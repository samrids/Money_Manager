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
            $type = isset($_GET['type']) ? $_GET['type'] : null;
            if ($type) {
                $stmt = $pdo->prepare('SELECT * FROM categories WHERE is_active = 1 AND type = ? ORDER BY id');
                $stmt->execute(array($type));
            } else {
                $stmt = $pdo->query('SELECT * FROM categories WHERE is_active = 1 ORDER BY type, id');
            }
            json_response($stmt->fetchAll());
            break;

        case 'POST':
            $data = get_json_body();
            $err  = validate_required($data, array('name', 'type'));
            if ($err) json_response($err, false, 400);

            if (!in_array($data['type'], array('income', 'expense'))) {
                json_response("type ต้องเป็น 'income' หรือ 'expense'", false, 400);
            }

            $stmt = $pdo->prepare('INSERT INTO categories (name, type, icon, color) VALUES (:name, :type, :icon, :color)');
            $stmt->execute(array(
                'name'  => $data['name'],
                'type'  => $data['type'],
                'icon'  => isset($data['icon'])  ? $data['icon']  : '📦',
                'color' => isset($data['color']) ? $data['color'] : '#6366f1',
            ));
            $id  = $pdo->lastInsertId();
            $row = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
            $row->execute(array($id));
            json_response($row->fetch());
            break;

        case 'PUT':
            $id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $data = get_json_body();
            if (!$id) json_response('ไม่ระบุ id', false, 400);

            $check = $pdo->prepare('SELECT id FROM categories WHERE id = ? AND is_active = 1');
            $check->execute(array($id));
            if (!$check->fetch()) json_response('ไม่พบหมวดหมู่', false, 404);

            $err = validate_required($data, array('name', 'type'));
            if ($err) json_response($err, false, 400);

            $stmt = $pdo->prepare('UPDATE categories SET name=:name, type=:type, icon=:icon, color=:color WHERE id=:id');
            $stmt->execute(array(
                'name'  => $data['name'],
                'type'  => $data['type'],
                'icon'  => isset($data['icon'])  ? $data['icon']  : '📦',
                'color' => isset($data['color']) ? $data['color'] : '#6366f1',
                'id'    => $id,
            ));
            $row = $pdo->prepare('SELECT * FROM categories WHERE id = ?');
            $row->execute(array($id));
            json_response($row->fetch());
            break;

        case 'DELETE':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) json_response('ไม่ระบุ id', false, 400);

            $def = $pdo->prepare('SELECT is_default FROM categories WHERE id = ?');
            $def->execute(array($id));
            $cat = $def->fetch();
            if (!$cat) json_response('ไม่พบหมวดหมู่', false, 404);
            if ((int)$cat['is_default'] === 1) {
                json_response('ไม่สามารถลบหมวดหมู่เริ่มต้น', false, 400);
            }

            $chk = $pdo->prepare('SELECT COUNT(*) FROM transactions WHERE category_id = ?');
            $chk->execute(array($id));
            if ((int)$chk->fetchColumn() > 0) {
                json_response('ไม่สามารถลบหมวดหมู่ที่มีรายการธุรกรรม', false, 400);
            }

            $stmt = $pdo->prepare('UPDATE categories SET is_active = 0 WHERE id = ?');
            $stmt->execute(array($id));
            json_response(array('deleted' => $id));
            break;

        default:
            json_response('Method not allowed', false, 405);
    }
} catch (PDOException $e) {
    json_response('Database error: ' . $e->getMessage(), false, 500);
}
