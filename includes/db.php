<?php

/**
 * PDO SQLite connection with auto-migrate
 * PHP 5.6 compatible
 */
function get_db()
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $db_dir  = dirname(__DIR__) . '/database';
    $db_file = $db_dir . '/money_manager.db';

    if (!is_dir($db_dir)) {
        mkdir($db_dir, 0755, true);
    }

    try {
        $pdo = new PDO('sqlite:' . $db_file);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA foreign_keys = ON');

        migrate($pdo);

    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()), JSON_UNESCAPED_UNICODE);
        exit;
    }

    return $pdo;
}

/**
 * สร้างตารางถ้ายังไม่มี และ seed default data
 * รองรับ shared hosting แม้ไฟล์ .sql ไม่ถูกอัปโหลดครบ
 */
function migrate(PDO $pdo)
{
    $schema = load_sql_from_file_or_default('schema.sql', get_default_schema_sql());
    execute_sql_batch($pdo, $schema, false);

    $seed = load_sql_from_file_or_default('seed.sql', get_default_seed_sql());
    execute_sql_batch($pdo, $seed, true);
}

function load_sql_from_file_or_default($file_name, $default_sql)
{
    $path = dirname(__DIR__) . '/database/' . $file_name;

    if (is_file($path) && is_readable($path)) {
        $sql = file_get_contents($path);
        if ($sql !== false && trim($sql) !== '') {
            return $sql;
        }
    }

    return $default_sql;
}

function execute_sql_batch(PDO $pdo, $sql, $ignore_errors)
{
    if (!is_string($sql) || trim($sql) === '') {
        return;
    }

    $lines = explode("\n", $sql);
    $clean_lines = array();

    foreach ($lines as $line) {
        if (strpos(trim($line), '--') !== 0) {
            $clean_lines[] = $line;
        }
    }

    $parts = explode(';', implode("\n", $clean_lines));
    foreach ($parts as $stmt) {
        $stmt = trim($stmt);
        if ($stmt === '') {
            continue;
        }

        if ($ignore_errors) {
            try {
                $pdo->exec($stmt);
            } catch (PDOException $e) {
                // duplicate / already exists → ignore
            }
        } else {
            $pdo->exec($stmt);
        }
    }
}

function get_default_schema_sql()
{
    return <<<'SQL'
PRAGMA journal_mode = WAL;
PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS settings (
    key   TEXT PRIMARY KEY,
    value TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS accounts (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    type       TEXT DEFAULT 'cash',
    balance    REAL DEFAULT 0,
    color      TEXT DEFAULT '#6366f1',
    icon       TEXT DEFAULT '💳',
    is_active  INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    type       TEXT NOT NULL,
    icon       TEXT DEFAULT '📦',
    color      TEXT DEFAULT '#6366f1',
    is_active  INTEGER DEFAULT 1,
    is_default INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS transactions (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    account_id    INTEGER NOT NULL,
    to_account_id INTEGER,
    category_id   INTEGER,
    type          TEXT NOT NULL,
    amount        REAL NOT NULL CHECK(amount > 0),
    note          TEXT,
    date          DATE NOT NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES accounts(id),
    FOREIGN KEY (to_account_id) REFERENCES accounts(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS budgets (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    amount      REAL NOT NULL CHECK(amount > 0),
    month       TEXT NOT NULL,
    UNIQUE(category_id, month),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);
SQL;
}

function get_default_seed_sql()
{
    return <<<'SQL'
INSERT OR IGNORE INTO categories (id, name, type, icon, color, is_active, is_default) VALUES
(1,  'เงินเดือน',        'income',  '💰', '#22c55e', 1, 1),
(2,  'รายได้พิเศษ',      'income',  '🎁', '#10b981', 1, 1),
(3,  'ดอกเบี้ย',         'income',  '🏦', '#06b6d4', 1, 1),
(4,  'เงินปันผล',        'income',  '📈', '#8b5cf6', 1, 1),
(5,  'รายได้อื่นๆ',      'income',  '💵', '#84cc16', 1, 1);

INSERT OR IGNORE INTO categories (id, name, type, icon, color, is_active, is_default) VALUES
(6,  'อาหาร',           'expense', '🍜', '#f97316', 1, 1),
(7,  'เดินทาง',         'expense', '🚗', '#f59e0b', 1, 1),
(8,  'ที่พักอาศัย',     'expense', '🏠', '#ef4444', 1, 1),
(9,  'สุขภาพ',          'expense', '🏥', '#ec4899', 1, 1),
(10, 'ช้อปปิ้ง',        'expense', '🛍️', '#a855f7', 1, 1),
(11, 'บันเทิง',         'expense', '🎬', '#6366f1', 1, 1),
(12, 'ค่าสาธารณูปโภค', 'expense', '💡', '#14b8a6', 1, 1),
(13, 'การศึกษา',        'expense', '📚', '#0ea5e9', 1, 1),
(14, 'ประกัน',          'expense', '🛡️', '#78716c', 1, 1),
(15, 'อื่นๆ',           'expense', '📋', '#94a3b8', 1, 1),
(16, 'ครอบครัว',       'expense', '👨‍👩‍👧', '#f43f5e', 1, 1),
(17, 'ชำระหนี้',       'expense', '💳', '#dc2626', 1, 1),
(18, 'ไอที/โดเมน',     'expense', '🖥️', '#0284c7', 1, 1),
(19, 'ภาษี',           'expense', '🏛️', '#92400e', 1, 1);

INSERT OR IGNORE INTO accounts (id, name, type, balance, color, icon, is_active) VALUES
(1, 'กระเป๋าสตางค์', 'cash', 5000.00, '#6366f1', '👛', 1),
(2, 'ธนาคาร กสิกร',  'bank', 25000.00, '#22c55e', '🏦', 1);
SQL;
}
