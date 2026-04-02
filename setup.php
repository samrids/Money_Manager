<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

send_no_cache_headers();

$pdo = get_db();
if (has_password($pdo)) {
    if (is_logged_in()) {
        redirect_to('index.php');
    }
    redirect_to('login.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm  = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    if ($password === '' || $confirm === '') {
        $error = 'กรุณากรอกรหัสผ่านและยืนยันรหัสผ่าน';
    } elseif ($password !== $confirm) {
        $error = 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        set_setting($pdo, 'password_hash', $hash);
        login_user();
        redirect_to('index.php');
    }
}
?>
<!DOCTYPE html>
<html lang="th" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="theme-color" content="#6366f1">
  <title>ตั้งรหัสผ่านครั้งแรก — Money</title>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="bg-base-200 min-h-screen">
  <div class="max-w-[480px] mx-auto min-h-screen flex items-center px-4 py-6">
    <div class="card bg-base-100 shadow-xl w-full border border-base-200">
      <div class="card-body p-5">
        <div class="text-center mb-2">
          <div class="w-14 h-14 rounded-2xl bg-primary/10 text-3xl flex items-center justify-center mx-auto mb-3">🛡️</div>
          <h1 class="text-xl font-bold">ตั้งรหัสผ่านครั้งแรก</h1>
          <p class="text-sm text-base-content/60 mt-1">สร้างรหัสผ่านสำหรับใช้งานแอปนี้แบบผู้ใช้คนเดียว</p>
        </div>

        <?php if ($error !== ''): ?>
        <div class="alert alert-error text-sm mb-3">
          <span><?php echo h($error); ?></span>
        </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
          <div>
            <label class="label pb-1"><span class="label-text font-medium">รหัสผ่านใหม่</span></label>
            <input
              type="password"
              name="password"
              autocomplete="new-password"
              placeholder="อย่างน้อย 1 ตัวอักษร"
              class="input input-bordered w-full h-12 text-base"
            >
          </div>

          <div>
            <label class="label pb-1"><span class="label-text font-medium">ยืนยันรหัสผ่าน</span></label>
            <input
              type="password"
              name="confirm_password"
              autocomplete="new-password"
              placeholder="กรอกรหัสผ่านอีกครั้ง"
              class="input input-bordered w-full h-12 text-base"
            >
          </div>

          <button type="submit" class="btn btn-primary w-full h-12 text-base">บันทึกและเริ่มใช้งาน</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
