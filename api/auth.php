<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = get_db();
$method = get_method();

try {
    if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
        logout_user();
        json_response(array('logged_out' => true));
    }

    if ($method === 'POST') {
        if (!has_password($pdo)) {
            json_response('กรุณาตั้งรหัสผ่านก่อนเข้าสู่ระบบ', false, 400);
        }

        $data = get_json_body();
        $password = '';

        if (isset($data['password'])) {
            $password = trim($data['password']);
        } elseif (isset($_POST['password'])) {
            $password = trim($_POST['password']);
        }

        if ($password === '') {
            json_response('กรุณากรอกรหัสผ่าน', false, 400);
        }

        $hash = get_setting($pdo, 'password_hash');
        if (!$hash || !password_verify($password, $hash)) {
            json_response('รหัสผ่านไม่ถูกต้อง', false, 401);
        }

        login_user();
        json_response(array('logged_in' => true));
    }

    json_response('Method not allowed', false, 405);
} catch (PDOException $e) {
    json_response('Database error: ' . $e->getMessage(), false, 500);
}
