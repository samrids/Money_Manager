<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_auth();

$pdo = get_db();
$page_title = 'ตั้งค่า';
$show_back = true;
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
    $new = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    $hash = get_setting($pdo, 'password_hash');

    if ($current === '' || $new === '' || $confirm === '') {
        $error = 'กรุณากรอกข้อมูลให้ครบทุกช่อง';
    } elseif (!$hash || !password_verify($current, $hash)) {
        $error = 'รหัสผ่านปัจจุบันไม่ถูกต้อง';
    } elseif ($new !== $confirm) {
        $error = 'รหัสผ่านใหม่และยืนยันไม่ตรงกัน';
    } else {
        set_setting($pdo, 'password_hash', password_hash($new, PASSWORD_DEFAULT));
        $success = 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว';
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="space-y-4">
  <?php if ($success !== ''): ?>
  <div class="alert alert-success text-sm">
    <span><?php echo h($success); ?></span>
  </div>
  <?php endif; ?>

  <?php if ($error !== ''): ?>
  <div class="alert alert-error text-sm">
    <span><?php echo h($error); ?></span>
  </div>
  <?php endif; ?>

  <div class="card bg-base-100 border border-base-200">
    <div class="card-body p-4">
      <h2 class="card-title text-base">เปลี่ยนรหัสผ่าน</h2>
      <p class="text-sm text-base-content/60 mb-2">อัปเดตรหัสผ่านเพื่อป้องกันการเข้าถึงแอป</p>

      <form method="post" class="space-y-3">
        <div>
          <label class="text-xs text-base-content/50 font-medium">รหัสผ่านปัจจุบัน</label>
          <input type="password" name="current_password" autocomplete="current-password" class="input input-bordered w-full mt-1 h-12 text-base">
        </div>

        <div>
          <label class="text-xs text-base-content/50 font-medium">รหัสผ่านใหม่</label>
          <input type="password" name="new_password" autocomplete="new-password" class="input input-bordered w-full mt-1 h-12 text-base">
        </div>

        <div>
          <label class="text-xs text-base-content/50 font-medium">ยืนยันรหัสผ่านใหม่</label>
          <input type="password" name="confirm_password" autocomplete="new-password" class="input input-bordered w-full mt-1 h-12 text-base">
        </div>

        <button type="submit" class="btn btn-primary w-full h-12 text-base">บันทึกการเปลี่ยนแปลง</button>
      </form>
    </div>
  </div>

  <div class="card bg-base-100 border border-base-200">
    <div class="card-body p-4">
      <h2 class="card-title text-base">ออกจากระบบ</h2>
      <p class="text-sm text-base-content/60 mb-3">ลบ session ปัจจุบันและกลับไปหน้าเข้าสู่ระบบ</p>
      <a href="logout.php" class="btn btn-outline btn-error w-full h-12 text-base">Logout</a>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
