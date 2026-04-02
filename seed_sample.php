<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_auth();

$pdo = get_db();
$page_title = 'โหลดข้อมูลตัวอย่าง';
$show_back = true;
$month = date('Y-m');
$message = '';
$message_type = '';
$inserted_count = 0;

function sample_note_list()
{
    return array(
        'เงินเดือน',
        'ค่าเช่าบ้าน',
        'ให้ภรรยา',
        'จ่ายบัตรเครดิต',
        'ค่าเน็ต',
        'ค่าเก็บขยะ',
        'ค่าน้ำ',
        'จ่ายค่ายืม',
        'ค่า domain awarasoft.com',
        'ค่าภาษีประจำปี'
    );
}

function get_sample_count($pdo, $month)
{
    $notes = sample_note_list();
    $placeholders = implode(',', array_fill(0, count($notes), '?'));
    $params = array_merge(array(2, $month), $notes);

    $stmt = $pdo->prepare(
        "SELECT COUNT(*)
         FROM transactions
         WHERE account_id = ?
           AND strftime('%Y-%m', date) = ?
           AND note IN ($placeholders)"
    );
    $stmt->execute($params);

    return (int)$stmt->fetchColumn();
}

function get_sample_summary($pdo, $month)
{
    $stmt = $pdo->prepare(
        "SELECT
            COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expense
         FROM transactions
         WHERE account_id = ?
           AND strftime('%Y-%m', date) = ?"
    );
    $stmt->execute(array(2, $month));
    $row = $stmt->fetch();

    $balance_stmt = $pdo->prepare('SELECT balance, name FROM accounts WHERE id = ? LIMIT 1');
    $balance_stmt->execute(array(2));
    $account = $balance_stmt->fetch();

    return array(
        'income' => $row ? (float)$row['income'] : 0,
        'expense' => $row ? (float)$row['expense'] : 0,
        'net' => $row ? ((float)$row['income'] - (float)$row['expense']) : 0,
        'balance' => $account ? (float)$account['balance'] : 0,
        'account_name' => $account ? $account['name'] : 'บัญชี #2'
    );
}

function run_sql_file($pdo, $file)
{
    $sql = file_get_contents($file);
    $lines = explode("\n", $sql);
    $clean_lines = array();

    foreach ($lines as $line) {
        if (strpos(trim($line), '--') !== 0) {
            $clean_lines[] = $line;
        }
    }

    $parts = explode(';', implode("\n", $clean_lines));
    foreach ($parts as $stmt) {
        $stmt = trim($stmt);
        if ($stmt === '') {
            continue;
        }
        $pdo->exec($stmt);
    }
}

$before_count = get_sample_count($pdo, $month);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($before_count > 0) {
        $message = 'มีข้อมูลตัวอย่างของเดือนนี้อยู่แล้ว — ไม่ได้เพิ่มซ้ำ';
        $message_type = 'warning';
    } else {
        try {
            $pdo->beginTransaction();
            run_sql_file($pdo, __DIR__ . '/database/sample_data.sql');
            $pdo->commit();

            $after_count = get_sample_count($pdo, $month);
            $inserted_count = $after_count - $before_count;
            $message = 'โหลดข้อมูลตัวอย่างสำเร็จ ' . $inserted_count . ' รายการ';
            $message_type = 'success';
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

$sample_count = get_sample_count($pdo, $month);
$summary = get_sample_summary($pdo, $month);

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-4">
  <div class="alert alert-info text-sm">
    <span>หน้านี้ใช้สำหรับโหลดข้อมูล demo ครั้งเดียว หากใช้งานจริงบน production ควรลบทิ้งหรือปิดการเข้าถึงภายหลัง</span>
  </div>

  <?php if ($message !== ''): ?>
  <div class="alert <?php echo $message_type === 'success' ? 'alert-success' : ($message_type === 'error' ? 'alert-error' : 'alert-warning'); ?> text-sm">
    <span><?php echo h($message); ?></span>
  </div>
  <?php endif; ?>

  <div class="card bg-base-100 border border-base-200">
    <div class="card-body p-4">
      <h2 class="card-title text-base">ข้อมูลตัวอย่างเดือน <?php echo h($month); ?></h2>
      <p class="text-sm text-base-content/60">บัญชีเป้าหมาย: <?php echo h($summary['account_name']); ?> (`account_id = 2`)</p>

      <div class="grid grid-cols-2 gap-3 mt-2 text-sm">
        <div class="rounded-xl bg-success/10 p-3">
          <p class="text-success/70">รายรับ</p>
          <p class="font-bold text-success"><?php echo h(format_money($summary['income'])); ?></p>
        </div>
        <div class="rounded-xl bg-error/10 p-3">
          <p class="text-error/70">รายจ่าย</p>
          <p class="font-bold text-error"><?php echo h(format_money($summary['expense'])); ?></p>
        </div>
        <div class="rounded-xl bg-primary/10 p-3">
          <p class="text-primary/70">ยอดสุทธิ</p>
          <p class="font-bold text-primary"><?php echo h(format_money($summary['net'])); ?></p>
        </div>
        <div class="rounded-xl bg-base-200 p-3">
          <p class="text-base-content/60">จำนวนรายการ</p>
          <p class="font-bold"><?php echo (int)$sample_count; ?> / 10</p>
        </div>
      </div>

      <div class="mt-4 rounded-xl bg-base-200 p-3 text-sm">
        <p class="text-base-content/60">ยอดคงเหลือบัญชีหลัง seed</p>
        <p class="font-bold text-lg"><?php echo h(format_money($summary['balance'])); ?></p>
      </div>

      <form method="post" class="mt-4">
        <button type="submit" class="btn btn-primary w-full h-12 text-base" <?php echo $sample_count > 0 ? 'disabled' : ''; ?>>
          <?php echo $sample_count > 0 ? 'โหลดแล้วเรียบร้อย' : 'โหลดข้อมูลตัวอย่าง'; ?>
        </button>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
