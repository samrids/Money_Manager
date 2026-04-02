<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function app_base_url()
{
    $script = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    $dir = str_replace('\\', '/', dirname($script));

    if ($dir === '/' || $dir === '\\' || $dir === '.') {
        $dir = '';
    }

    if (substr($dir, -4) === '/api') {
        $dir = substr($dir, 0, -4);
    }

    return rtrim($dir, '/');
}

function app_url($path)
{
    $base = app_base_url();
    return ($base !== '' ? $base : '') . '/' . ltrim($path, '/');
}

function redirect_to($path)
{
    header('Location: ' . app_url($path));
    exit;
}

function send_no_cache_headers()
{
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
    header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');
}

function get_setting($pdo, $key)
{
    $stmt = $pdo->prepare('SELECT value FROM settings WHERE key = ? LIMIT 1');
    $stmt->execute(array($key));
    $value = $stmt->fetchColumn();

    return $value !== false ? $value : null;
}

function set_setting($pdo, $key, $value)
{
    $stmt = $pdo->prepare('INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)');
    $stmt->execute(array($key, $value));
}

function has_password($pdo)
{
    $hash = get_setting($pdo, 'password_hash');
    return !empty($hash);
}

function is_logged_in()
{
    return !empty($_SESSION['logged_in']);
}

function login_user()
{
    if (function_exists('session_regenerate_id')) {
        session_regenerate_id(true);
    }

    $_SESSION['logged_in'] = true;
}

function logout_user()
{
    $_SESSION = array();

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            isset($params['path']) ? $params['path'] : '/',
            isset($params['domain']) ? $params['domain'] : '',
            !empty($params['secure']),
            !empty($params['httponly'])
        );
    }

    session_destroy();
}

function require_auth()
{
    send_no_cache_headers();

    $pdo = get_db();
    if (!has_password($pdo)) {
        redirect_to('setup.php');
    }

    if (!is_logged_in()) {
        redirect_to('login.php');
    }
}

function require_api_auth()
{
    if (!is_logged_in()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('success' => false, 'message' => 'Unauthorized'), JSON_UNESCAPED_UNICODE);
        exit;
    }
}
