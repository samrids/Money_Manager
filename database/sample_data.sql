-- Sample demo transactions for current month (account_id = 2 : ธนาคาร กสิกร)
-- ควรรันผ่าน /seed_sample.php เพื่อป้องกัน duplicate และให้ transaction ครบชุด

UPDATE accounts
SET balance = balance + 19898
WHERE id = 2
  AND NOT EXISTS (
      SELECT 1
      FROM transactions
      WHERE account_id = 2
        AND type = 'income'
        AND note = 'เงินเดือน'
        AND amount = 59000
        AND strftime('%Y-%m', date) = strftime('%Y-%m', 'now', 'localtime')
  );

INSERT OR IGNORE INTO transactions (account_id, category_id, type, amount, note, date)
SELECT 2, 1, 'income', 59000, 'เงินเดือน', date('now', 'localtime', 'start of month', '+0 day')
WHERE NOT EXISTS (
    SELECT 1 FROM transactions
    WHERE account_id = 2 AND category_id = 1 AND type = 'income' AND amount = 59000
      AND note = 'เงินเดือน'
      AND date = date('now', 'localtime', 'start of month', '+0 day')
);

INSERT OR IGNORE INTO transactions (account_id, category_id, type, amount, note, date)
SELECT 2, 8, 'expense', 18000, 'ค่าเช่าบ้าน', date('now', 'localtime', 'start of month', '+1 day')
WHERE NOT EXISTS (
    SELECT 1 FROM transactions
    WHERE account_id = 2 AND category_id = 8 AND type = 'expense' AND amount = 18000
      AND note = 'ค่าเช่าบ้าน'
      AND date = date('now', 'localtime', 'start of month', '+1 day')
);

INSERT OR IGNORE INTO transactions (account_id, category_id, type, amount, note, date)
SELECT 2, 16, 'expense', 10000, 'ให้ภรรยา', date('now', 'localtime', 'start of month', '+2 day')
WHERE NOT EXISTS (
    SELECT 1 FROM transactions
    WHERE account_id = 2 AND category_id = 16 AND type = 'expense' AND amount = 10000
      AND note = 'ให้ภรรยา'
      AND date = date('now', 'localtime', 'start of month', '+2 day')
);

INSERT OR IGNORE INTO transactions (account_id, category_id, type, amount, note, date)
SELECT 2, 17, 'expense', 6500, 'จ่ายบัตรเครดิต', date('now', 'localtime', 'start of month', '+3 day')
WHERE NOT EXISTS (
    SELECT 1 FROM transactions
    WHERE account_id = 2 AND category_id = 17 AND type = 'expense' AND amount = 6500
      AND note = 'จ่ายบัตรเครดิต'
      AND date = date('now', 'localtime', 'start of month', '+3 day')
);

INSERT OR IGNORE INTO transactions (account_id, category_id, type, amount, note, date)
SELECT 2, 12, 'expense', 750, 'ค่าเน็ต', date('now', 'localtime', 'start of month', '+4 day')
WHERE NOT EXISTS (
    SELECT 1 FROM transactions
    WHERE account_id = 2 AND category_id = 12 AND type = 'expense' AND amount = 750
      AND note = 'ค่าเน็ต'
      AND date = date('now', 'localtime', 'start of month', '+4 day')
);

INSERT OR IGNORE INTO transactions (account_id, category_id, type, amount, note, date)
SELECT 2, 12, 'expense', 700, 'ค่าเก็บขยะ', date('now', 'localtime', 'start of month', '+5 day')
WHERE NOT EXISTS (
    SELECT 1 FROM transactions
    WHERE account_id = 2 AND category_id = 12 AND type = 'expense' AND amount = 700
      AND note = 'ค่าเก็บขยะ'
      AND date = date('now', 'localtime', 'start of month', '+5 day')
);

INSERT OR IGNORE INTO transactions (account_id, category_id, type, amount, note, date)
SELECT 2, 12, 'expense', 300, 'ค่าน้ำ', date('now', 'localtime', 'start of month', '+6 day')
WHERE NOT EXISTS (
    SELECT 1 FROM transactions
    WHERE account_id = 2 AND category_id = 12 AND type = 'expense' AND amount = 300
      AND note = 'ค่าน้ำ'
      AND date = date('now', 'localtime', 'start of month', '+6 day')
);

INSERT OR IGNORE INTO transactions (account_id, category_id, type, amount, note, date)
SELECT 2, 17, 'expense', 1000, 'จ่ายค่ายืม', date('now', 'localtime', 'start of month', '+7 day')
WHERE NOT EXISTS (
    SELECT 1 FROM transactions
    WHERE account_id = 2 AND category_id = 17 AND type = 'expense' AND amount = 1000
      AND note = 'จ่ายค่ายืม'
      AND date = date('now', 'localtime', 'start of month', '+7 day')
);

INSERT OR IGNORE INTO transactions (account_id, category_id, type, amount, note, date)
SELECT 2, 18, 'expense', 907, 'ค่า domain awarasoft.com', date('now', 'localtime', 'start of month', '+8 day')
WHERE NOT EXISTS (
    SELECT 1 FROM transactions
    WHERE account_id = 2 AND category_id = 18 AND type = 'expense' AND amount = 907
      AND note = 'ค่า domain awarasoft.com'
      AND date = date('now', 'localtime', 'start of month', '+8 day')
);

INSERT OR IGNORE INTO transactions (account_id, category_id, type, amount, note, date)
SELECT 2, 19, 'expense', 945, 'ค่าภาษีประจำปี', date('now', 'localtime', 'start of month', '+9 day')
WHERE NOT EXISTS (
    SELECT 1 FROM transactions
    WHERE account_id = 2 AND category_id = 19 AND type = 'expense' AND amount = 945
      AND note = 'ค่าภาษีประจำปี'
      AND date = date('now', 'localtime', 'start of month', '+9 day')
);
