<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

send_no_cache_headers();

$pdo = get_db();
if (!has_password($pdo)) {
    redirect_to('setup.php');
}
if (is_logged_in()) {
    redirect_to('index.php');
}

$error = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $hash = get_setting($pdo, 'password_hash');

    if ($password === '') {
        $error = 'กรุณากรอกรหัสผ่าน';
    } elseif (!$hash || !password_verify($password, $hash)) {
        $error = 'รหัสผ่านไม่ถูกต้อง';
    } else {
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
  <title>เข้าสู่ระบบ — Money</title>
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.12.14/dist/full.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="bg-base-200 min-h-screen">
  <div class="max-w-[480px] mx-auto min-h-screen flex items-center px-4 py-6">
    <div class="card bg-base-100 shadow-xl w-full border border-base-200">
      <div class="card-body p-5">
        <div class="text-center mb-2">
          <div class="w-14 h-14 rounded-2xl bg-primary/10 text-3xl flex items-center justify-center mx-auto mb-3">🔐</div>
          <h1 class="text-xl font-bold">Money Manager</h1>
          <p class="text-sm text-base-content/60 mt-1">เข้าสู่ระบบด้วยรหัสผ่านของคุณ</p>
        </div>

        <?php if ($error !== ''): ?>
        <div class="alert alert-error text-sm mb-3">
          <span><?php echo h($error); ?></span>
        </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
          <div>
            <label class="label pb-1"><span class="label-text font-medium">รหัสผ่าน</span></label>
            <input
              type="password"
              name="password"
              autocomplete="current-password"
              placeholder="กรอกรหัสผ่าน"
              class="input input-bordered w-full h-12 text-base"
              value="<?php echo h($password); ?>"
            >
          </div>

          <button type="submit" class="btn btn-primary w-full h-12 text-base">เข้าสู่ระบบ</button>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
