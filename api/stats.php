<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

require_api_auth();

$pdo   = get_db();
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

try {
    // ─── 1. รวม income / expense / net ───────────────────────────────
    $sum = $pdo->prepare("
        SELECT
            COALESCE(SUM(CASE WHEN type = 'income'  THEN amount ELSE 0 END), 0) AS income,
            COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expense
        FROM transactions
        WHERE strftime('%Y-%m', date) = ?
    ");
    $sum->execute(array($month));
    $totals  = $sum->fetch();
    $income  = (float)$totals['income'];
    $expense = (float)$totals['expense'];
    $net     = $income - $expense;

    // ─── 2. ยอดรวมทุกบัญชี ───────────────────────────────────────────
    $total_balance = (float)$pdo->query(
        "SELECT COALESCE(SUM(balance), 0) FROM accounts WHERE is_active = 1"
    )->fetchColumn();

    // ─── 3. รายจ่ายแยกหมวดหมู่ (Pie chart) ──────────────────────────
    $pie_stmt = $pdo->prepare("
        SELECT c.name, c.icon, c.color,
               COALESCE(SUM(t.amount), 0) AS amount
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.type = 'expense'
          AND strftime('%Y-%m', t.date) = ?
        GROUP BY c.id
        ORDER BY amount DESC
    ");
    $pie_stmt->execute(array($month));
    $category_pie = $pie_stmt->fetchAll();

    // ─── 4. รายรับ-จ่ายรายวัน (Line chart) ──────────────────────────
    // หาจำนวนวันในเดือน — ไม่ใช้ cal_days_in_month (ต้องการ extension)
    $parts         = explode('-', $month);
    $year          = (int)$parts[0];
    $mon           = (int)$parts[1];
    $days_in_month = (int)date('t', mktime(0, 0, 0, $mon, 1, $year));

    $daily_stmt = $pdo->prepare("
        SELECT strftime('%d', date) AS day,
               type,
               COALESCE(SUM(amount), 0) AS total
        FROM transactions
        WHERE strftime('%Y-%m', date) = ?
          AND type IN ('income','expense')
        GROUP BY day, type
    ");
    $daily_stmt->execute(array($month));
    $daily_rows = $daily_stmt->fetchAll();

    $daily_map = array();
    foreach ($daily_rows as $r) {
        $daily_map[$r['day']][$r['type']] = (float)$r['total'];
    }

    $daily_labels  = array();
    $daily_income  = array();
    $daily_expense = array();
    for ($d = 1; $d <= $days_in_month; $d++) {
        $key            = str_pad($d, 2, '0', STR_PAD_LEFT);
        $daily_labels[] = $d;
        $daily_income[] = isset($daily_map[$key]['income'])  ? $daily_map[$key]['income']  : 0;
        $daily_expense[]= isset($daily_map[$key]['expense']) ? $daily_map[$key]['expense'] : 0;
    }

    // ─── 5. Top 5 หมวดหมู่ ───────────────────────────────────────────
    $top5_stmt = $pdo->prepare("
        SELECT c.name, c.icon, c.color,
               COALESCE(SUM(t.amount), 0) AS amount
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.type = 'expense'
          AND strftime('%Y-%m', t.date) = ?
        GROUP BY c.id
        ORDER BY amount DESC
        LIMIT 5
    ");
    $top5_stmt->execute(array($month));
    $top5 = $top5_stmt->fetchAll();

    // ─── 6. รายการล่าสุด 10 รายการ ───────────────────────────────────
    $recent_stmt = $pdo->prepare("
        SELECT t.id, t.type, t.amount, t.note, t.date,
               a.name AS account_name,
               c.name AS category_name,
               c.icon AS category_icon
        FROM transactions t
        LEFT JOIN accounts   a ON t.account_id  = a.id
        LEFT JOIN categories c ON t.category_id = c.id
        WHERE strftime('%Y-%m', t.date) = ?
        ORDER BY t.date DESC, t.created_at DESC
        LIMIT 10
    ");
    $recent_stmt->execute(array($month));
    $recent = $recent_stmt->fetchAll();

    json_response(array(
        'income'        => $income,
        'expense'       => $expense,
        'net'           => $net,
        'total_balance' => $total_balance,
        'category_pie'  => $category_pie,
        'daily_labels'  => $daily_labels,
        'daily_income'  => $daily_income,
        'daily_expense' => $daily_expense,
        'top5'          => $top5,
        'recent'        => $recent,
    ));

} catch (PDOException $e) {
    json_response('Database error: ' . $e->getMessage(), false, 500);
}
