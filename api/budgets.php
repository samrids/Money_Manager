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
            $month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
            $stmt  = $pdo->prepare('
                SELECT b.*,
                       c.name  AS category_name,
                       c.icon  AS category_icon,
                       c.color AS category_color,
                       COALESCE((
                           SELECT SUM(t.amount)
                           FROM transactions t
                           WHERE t.category_id = b.category_id
                             AND t.type = \'expense\'
                             AND strftime(\'%Y-%m\', t.date) = :month2
                       ), 0) AS spent
                FROM budgets b
                JOIN categories c ON b.category_id = c.id
                WHERE b.month = :month
                ORDER BY c.name
            ');
            $stmt->execute(array('month' => $month, 'month2' => $month));
            json_response($stmt->fetchAll());
            break;

        case 'POST':
            $data = get_json_body();
            $err  = validate_required($data, array('category_id', 'amount', 'month'));
            if ($err) json_response($err, false, 400);
            if ((float)$data['amount'] <= 0) json_response('amount ต้องมากกว่า 0', false, 400);

            $cat_id = (int)$data['category_id'];
            $mon    = $data['month'];
            $amt    = (float)$data['amount'];

            // UPSERT แบบ 2-step (รองรับ SQLite เก่า)
            $chk = $pdo->prepare('SELECT id FROM budgets WHERE category_id = ? AND month = ?');
            $chk->execute(array($cat_id, $mon));
            $existing = $chk->fetch();

            if ($existing) {
                $upd = $pdo->prepare('UPDATE budgets SET amount = ? WHERE id = ?');
                $upd->execute(array($amt, $existing['id']));
                $id = $existing['id'];
            } else {
                $ins = $pdo->prepare('INSERT INTO budgets (category_id, amount, month) VALUES (?, ?, ?)');
                $ins->execute(array($cat_id, $amt, $mon));
                $id = $pdo->lastInsertId();
            }

            $row = $pdo->prepare('SELECT * FROM budgets WHERE id = ?');
            $row->execute(array($id));
            json_response($row->fetch());
            break;

        case 'PUT':
            $id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $data = get_json_body();
            if (!$id) json_response('ไม่ระบุ id', false, 400);
            if (!isset($data['amount']) || (float)$data['amount'] <= 0) {
                json_response('amount ต้องมากกว่า 0', false, 400);
            }

            $check = $pdo->prepare('SELECT id FROM budgets WHERE id = ?');
            $check->execute(array($id));
            if (!$check->fetch()) json_response('ไม่พบงบประมาณ', false, 404);

            $stmt = $pdo->prepare('UPDATE budgets SET amount = :amount WHERE id = :id');
            $stmt->execute(array('amount' => (float)$data['amount'], 'id' => $id));
            $row = $pdo->prepare('SELECT * FROM budgets WHERE id = ?');
            $row->execute(array($id));
            json_response($row->fetch());
            break;

        case 'DELETE':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) json_response('ไม่ระบุ id', false, 400);

            $check = $pdo->prepare('SELECT id FROM budgets WHERE id = ?');
            $check->execute(array($id));
            if (!$check->fetch()) json_response('ไม่พบงบประมาณ', false, 404);

            $pdo->prepare('DELETE FROM budgets WHERE id = ?')->execute(array($id));
            json_response(array('deleted' => $id));
            break;

        default:
            json_response('Method not allowed', false, 405);
    }
} catch (PDOException $e) {
    json_response('Database error: ' . $e->getMessage(), false, 500);
}
