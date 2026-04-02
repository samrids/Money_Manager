<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

require_api_auth();

$pdo    = get_db();
$method = get_method();

try {
    switch ($method) {

        case 'GET':
            $rows = $pdo->query('SELECT * FROM accounts WHERE is_active = 1 ORDER BY id')->fetchAll();
            json_response($rows);
            break;

        case 'POST':
            $data    = get_json_body();
            $err     = validate_required($data, array('name'));
            if ($err) json_response($err, false, 400);

            $balance = isset($data['balance']) ? (float)$data['balance'] : 0;
            if ($balance < 0) json_response('ยอดเริ่มต้นต้องไม่ติดลบ', false, 400);

            $stmt = $pdo->prepare('INSERT INTO accounts (name, type, balance, color, icon) VALUES (:name, :type, :balance, :color, :icon)');
            $stmt->execute(array(
                'name'    => $data['name'],
                'type'    => isset($data['type'])  ? $data['type']  : 'cash',
                'balance' => $balance,
                'color'   => isset($data['color']) ? $data['color'] : '#6366f1',
                'icon'    => isset($data['icon'])  ? $data['icon']  : '💳',
            ));
            $id  = $pdo->lastInsertId();
            $row = $pdo->prepare('SELECT * FROM accounts WHERE id = ?');
            $row->execute(array($id));
            json_response($row->fetch());
            break;

        case 'PUT':
            $id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $data = get_json_body();
            if (!$id) json_response('ไม่ระบุ id', false, 400);

            $check = $pdo->prepare('SELECT id FROM accounts WHERE id = ? AND is_active = 1');
            $check->execute(array($id));
            if (!$check->fetch()) json_response('ไม่พบบัญชี', false, 404);

            $err = validate_required($data, array('name'));
            if ($err) json_response($err, false, 400);

            $stmt = $pdo->prepare('UPDATE accounts SET name=:name, type=:type, color=:color, icon=:icon WHERE id=:id');
            $stmt->execute(array(
                'name'  => $data['name'],
                'type'  => isset($data['type'])  ? $data['type']  : 'cash',
                'color' => isset($data['color']) ? $data['color'] : '#6366f1',
                'icon'  => isset($data['icon'])  ? $data['icon']  : '💳',
                'id'    => $id,
            ));
            $row = $pdo->prepare('SELECT * FROM accounts WHERE id = ?');
            $row->execute(array($id));
            json_response($row->fetch());
            break;

        case 'DELETE':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) json_response('ไม่ระบุ id', false, 400);

            $chk = $pdo->prepare('SELECT COUNT(*) FROM transactions WHERE account_id = ? OR to_account_id = ?');
            $chk->execute(array($id, $id));
            if ((int)$chk->fetchColumn() > 0) {
                json_response('ไม่สามารถลบบัญชีที่มีรายการธุรกรรม', false, 400);
            }

            $stmt = $pdo->prepare('UPDATE accounts SET is_active = 0 WHERE id = ?');
            $stmt->execute(array($id));
            json_response(array('deleted' => $id));
            break;

        default:
            json_response('Method not allowed', false, 405);
    }
} catch (PDOException $e) {
    json_response('Database error: ' . $e->getMessage(), false, 500);
}
