<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/auth.php';

require_api_auth();

$pdo    = get_db();
$method = get_method();

function update_balance(PDO $pdo, $account_id, $delta)
{
    $stmt = $pdo->prepare('UPDATE accounts SET balance = balance + :delta WHERE id = :id');
    $stmt->execute(array('delta' => $delta, 'id' => $account_id));
}

function apply_transaction(PDO $pdo, array $tx, $sign = 1)
{
    $amount = (float)$tx['amount'] * $sign;

    if ($tx['type'] === 'income') {
        update_balance($pdo, (int)$tx['account_id'], $amount);
    } elseif ($tx['type'] === 'expense') {
        update_balance($pdo, (int)$tx['account_id'], -$amount);
    } elseif ($tx['type'] === 'transfer') {
        update_balance($pdo, (int)$tx['account_id'],    -$amount);
        update_balance($pdo, (int)$tx['to_account_id'],  $amount);
    }
}

try {
    switch ($method) {

        case 'GET':
            $where  = array('1=1');
            $params = array();

            if (!empty($_GET['month'])) {
                $where[]          = "strftime('%Y-%m', t.date) = :month";
                $params['month']  = $_GET['month'];
            }
            if (!empty($_GET['account_id'])) {
                // ใช้ชื่อ param ต่างกันเพราะ PDO ไม่อนุญาตให้ซ้ำ
                $where[]              = '(t.account_id = :acct_id OR t.to_account_id = :acct_id2)';
                $params['acct_id']    = (int)$_GET['account_id'];
                $params['acct_id2']   = (int)$_GET['account_id'];
            }
            if (!empty($_GET['type'])) {
                $where[]        = 't.type = :type';
                $params['type'] = $_GET['type'];
            }
            if (!empty($_GET['category_id'])) {
                $where[]               = 't.category_id = :category_id';
                $params['category_id'] = (int)$_GET['category_id'];
            }

            $sql = 'SELECT t.*,
                           a.name  AS account_name,
                           a.color AS account_color,
                           b.name  AS to_account_name,
                           c.name  AS category_name,
                           c.icon  AS category_icon,
                           c.color AS category_color
                    FROM transactions t
                    LEFT JOIN accounts   a ON t.account_id    = a.id
                    LEFT JOIN accounts   b ON t.to_account_id = b.id
                    LEFT JOIN categories c ON t.category_id   = c.id
                    WHERE ' . implode(' AND ', $where) . '
                    ORDER BY t.date DESC, t.created_at DESC';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            json_response($stmt->fetchAll());
            break;

        case 'POST':
            $data = get_json_body();
            $err  = validate_required($data, array('account_id', 'type', 'amount', 'date'));
            if ($err) json_response($err, false, 400);

            if (!in_array($data['type'], array('income', 'expense', 'transfer'))) {
                json_response('type ต้องเป็น income, expense, หรือ transfer', false, 400);
            }
            if ((float)$data['amount'] <= 0) {
                json_response('amount ต้องมากกว่า 0', false, 400);
            }
            if ($data['type'] === 'transfer') {
                $err2 = validate_required($data, array('to_account_id'));
                if ($err2) json_response($err2, false, 400);
                if ((int)$data['account_id'] === (int)$data['to_account_id']) {
                    json_response('บัญชีต้นทางและปลายทางต้องไม่ซ้ำกัน', false, 400);
                }
            }

            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('INSERT INTO transactions (account_id, to_account_id, category_id, type, amount, note, date) VALUES (:account_id, :to_account_id, :category_id, :type, :amount, :note, :date)');
                $stmt->execute(array(
                    'account_id'    => (int)$data['account_id'],
                    'to_account_id' => ($data['type'] === 'transfer') ? (int)$data['to_account_id'] : null,
                    'category_id'   => !empty($data['category_id']) ? (int)$data['category_id'] : null,
                    'type'          => $data['type'],
                    'amount'        => (float)$data['amount'],
                    'note'          => isset($data['note']) ? $data['note'] : null,
                    'date'          => $data['date'],
                ));
                $id = $pdo->lastInsertId();
                apply_transaction($pdo, array_merge($data, array('amount' => (float)$data['amount'])));
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }

            $row = $pdo->prepare('SELECT * FROM transactions WHERE id = ?');
            $row->execute(array($id));
            json_response($row->fetch());
            break;

        case 'PUT':
            $id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            $data = get_json_body();
            if (!$id) json_response('ไม่ระบุ id', false, 400);

            $old = $pdo->prepare('SELECT * FROM transactions WHERE id = ?');
            $old->execute(array($id));
            $old_tx = $old->fetch();
            if (!$old_tx) json_response('ไม่พบรายการ', false, 404);

            $err = validate_required($data, array('account_id', 'type', 'amount', 'date'));
            if ($err) json_response($err, false, 400);
            if ((float)$data['amount'] <= 0) json_response('amount ต้องมากกว่า 0', false, 400);

            $pdo->beginTransaction();
            try {
                apply_transaction($pdo, $old_tx, -1);
                $stmt = $pdo->prepare('UPDATE transactions SET account_id=:account_id, to_account_id=:to_account_id, category_id=:category_id, type=:type, amount=:amount, note=:note, date=:date WHERE id=:id');
                $stmt->execute(array(
                    'account_id'    => (int)$data['account_id'],
                    'to_account_id' => ($data['type'] === 'transfer' && !empty($data['to_account_id'])) ? (int)$data['to_account_id'] : null,
                    'category_id'   => !empty($data['category_id']) ? (int)$data['category_id'] : null,
                    'type'          => $data['type'],
                    'amount'        => (float)$data['amount'],
                    'note'          => isset($data['note']) ? $data['note'] : null,
                    'date'          => $data['date'],
                    'id'            => $id,
                ));
                apply_transaction($pdo, array_merge($data, array('amount' => (float)$data['amount'])));
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }

            $row = $pdo->prepare('SELECT * FROM transactions WHERE id = ?');
            $row->execute(array($id));
            json_response($row->fetch());
            break;

        case 'DELETE':
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) json_response('ไม่ระบุ id', false, 400);

            $old = $pdo->prepare('SELECT * FROM transactions WHERE id = ?');
            $old->execute(array($id));
            $old_tx = $old->fetch();
            if (!$old_tx) json_response('ไม่พบรายการ', false, 404);

            $pdo->beginTransaction();
            try {
                apply_transaction($pdo, $old_tx, -1);
                $pdo->prepare('DELETE FROM transactions WHERE id = ?')->execute(array($id));
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
            json_response(array('deleted' => $id));
            break;

        default:
            json_response('Method not allowed', false, 405);
    }
} catch (PDOException $e) {
    json_response('Database error: ' . $e->getMessage(), false, 500);
}
