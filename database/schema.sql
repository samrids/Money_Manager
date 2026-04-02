-- Money Manager Database Schema
-- SQLite 3

PRAGMA journal_mode = WAL;
PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS settings (
    key   TEXT PRIMARY KEY,
    value TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS accounts (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    type       TEXT DEFAULT 'cash',   -- cash | bank | credit | saving
    balance    REAL DEFAULT 0,
    color      TEXT DEFAULT '#6366f1',
    icon       TEXT DEFAULT '💳',
    is_active  INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    type       TEXT NOT NULL,         -- income | expense
    icon       TEXT DEFAULT '📦',
    color      TEXT DEFAULT '#6366f1',
    is_active  INTEGER DEFAULT 1,
    is_default INTEGER DEFAULT 0      -- 1 = ลบไม่ได้
);

CREATE TABLE IF NOT EXISTS transactions (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    account_id    INTEGER NOT NULL,
    to_account_id INTEGER,             -- ใช้เมื่อ type = transfer
    category_id   INTEGER,
    type          TEXT NOT NULL,       -- income | expense | transfer
    amount        REAL NOT NULL CHECK(amount > 0),
    note          TEXT,
    date          DATE NOT NULL,
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id)    REFERENCES accounts(id),
    FOREIGN KEY (to_account_id) REFERENCES accounts(id),
    FOREIGN KEY (category_id)   REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS budgets (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL,
    amount      REAL NOT NULL CHECK(amount > 0),
    month       TEXT NOT NULL,         -- YYYY-MM
    UNIQUE(category_id, month),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);
