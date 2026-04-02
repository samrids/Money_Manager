# AGENTS.md — Money Manager

## 🧠 Project Overview

**Money Manager** เป็น Web Application สำหรับจัดการการเงินส่วนตัว พัฒนาด้วย Vanilla PHP + SQLite  
รองรับการบันทึกรายรับ-รายจ่าย, หลายบัญชี, หมวดหมู่, งบประมาณ และกราฟสถิติ

**Target:** ใช้งานบน **Shared Hosting PHP 5.6** + เปิดใน **Mobile Browser เท่านั้น** (ไม่มี desktop layout)  
**ผู้ใช้:** คนเดียว (Single-user) — Login ด้วย Password เท่านั้น ไม่มี username

---

## 🏗️ Tech Stack

| Layer | Technology |
|-------|-----------|
| Runtime | **PHP 5.6** (Shared Hosting / XAMPP) |
| Database | SQLite 3 via PDO |
| CSS | Tailwind CSS v3 CDN + DaisyUI v4 CDN |
| JS Reactivity | Alpine.js v3 (CDN) |
| Charts | Chart.js v3 (CDN) — v3 ใช้ได้กับ browser เก่ากว่า v4 |
| Icons | Heroicons (SVG inline) |

> **หมายเหตุ Tailwind:** ใช้ Tailwind CSS v3 Play CDN (`https://cdn.tailwindcss.com`) เพราะ v4 ต้องการ build step  
> ใช้ DaisyUI v4 CDN แทน FlyonUI เพราะ FlyonUI ต้องการ build tool

---

## 📱 Mobile-First Design Principles

App นี้ออกแบบเป็น **Mobile-Only** — ไม่มี desktop sidebar

| หัวข้อ | แนวทาง |
|--------|--------|
| Layout | Max-width `480px`, centered, เหมือน native app |
| Navigation | **Bottom Navigation Bar** — 5 ไอคอน (Dashboard, Add, Accounts, Budgets, Reports) |
| Quick Add | **FAB (Floating Action Button)** ตรงกลาง bottom nav สำหรับบันทึกรายการด่วน |
| Forms | **Bottom Sheet** (slide up) แทน modal กลางจอ |
| Lists | Card layout ทุกที่ — ไม่ใช้ `<table>` แบบ horizontal scroll |
| Touch targets | ปุ่มและ input อย่างน้อย `48px` height |
| Font size | Base `16px` ขึ้นไป ป้องกัน auto-zoom บน iOS |
| Safe area | Padding bottom รองรับ iPhone home indicator (`pb-safe` / `env(safe-area-inset-bottom)`) |

---

## 📁 Project Structure

```
money/
├── AGENTS.md
├── PLAN.md
├── QA.md
│
├── index.php                  # Dashboard (ต้อง login)
├── login.php                  # หน้า Login (password only)
├── setup.php                  # ตั้งรหัสผ่านครั้งแรก
├── logout.php                 # Destroy session + redirect
├── transactions.php           # รายการธุรกรรม
├── accounts.php               # จัดการบัญชี
├── categories.php             # จัดการหมวดหมู่
├── budgets.php                # งบประมาณ
├── reports.php                # กราฟ/สถิติ
├── settings.php               # ตั้งค่า (เปลี่ยน password)
│
├── api/
│   ├── transactions.php       # CRUD + filter
│   ├── accounts.php           # CRUD accounts
│   ├── categories.php         # CRUD categories
│   ├── budgets.php            # CRUD + progress
│   ├── stats.php              # สถิติ/กราฟ
│   └── auth.php               # POST login / GET logout
│
├── includes/
│   ├── db.php                 # PDO SQLite connection + migrate
│   ├── auth.php               # session auth helpers
│   ├── header.php             # HTML head + bottom nav
│   ├── footer.php             # closing tags + scripts
│   └── helpers.php            # utility functions
│
├── database/
│   ├── schema.sql             # DDL ตารางทั้งหมด (incl. settings)
│   ├── seed.sql               # ข้อมูลหมวดหมู่ default + บัญชี
│   └── sample_data.sql        # Sample transactions สำหรับ demo
│
└── assets/
    ├── css/
    │   └── app.css            # Custom styles (safe-area, animation)
    └── js/
        └── charts.js          # Chart.js wrapper functions
```

---

## 🗄️ Database Schema

```sql
-- ตาราง settings: เก็บ password_hash และการตั้งค่าทั่วไป
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
```

---

## 🔌 API Contract

### Headers ทุก Response
```
Content-Type: application/json
```

### Response Format
```json
{ "success": true, "data": { ... } }
{ "success": false, "message": "error description" }
```

### Auth Flow

```
1. เปิด app → includes/auth.php → check $_SESSION['logged_in']
2. ถ้ายังไม่มี password ใน settings table → redirect setup.php
3. ถ้า session ไม่ valid → redirect login.php
4. login.php: POST password → verify hash → set session → redirect index.php
5. logout.php: session_destroy() → redirect login.php
```

- **`includes/auth.php`** มี 2 functions:
  - `require_auth()` — สำหรับ page (redirect ถ้าไม่ผ่าน)
  - `require_api_auth()` — สำหรับ API (return JSON 401 ถ้าไม่ผ่าน)
- ทุก page PHP เรียก `require_auth()` **ก่อน** `require_once header.php`
- ทุก api/*.php เรียก `require_api_auth()` เป็น line แรกหลัง header
- Password hash ด้วย `password_hash($plain, PASSWORD_DEFAULT)` (PHP 5.5+)
- Verify ด้วย `password_verify($plain, $hash)`

### Endpoints

| Method | Endpoint | Body / Params | คำอธิบาย |
|--------|----------|---------------|----------|
| POST | `/api/auth.php` | `{password}` | Login → set session |
| GET | `/api/auth.php?action=logout` | — | Logout → destroy session |
| GET | `/api/transactions.php` | `?month=YYYY-MM&account_id=&type=&category_id=` | ดึงรายการ |
| POST | `/api/transactions.php` | `{account_id, category_id, type, amount, note, date}` | เพิ่ม |
| PUT | `/api/transactions.php?id=` | same as POST | แก้ไข |
| DELETE | `/api/transactions.php?id=` | — | ลบ |
| GET | `/api/accounts.php` | — | ดึงบัญชีทั้งหมด |
| POST | `/api/accounts.php` | `{name, type, balance, color, icon}` | เพิ่มบัญชี |
| PUT | `/api/accounts.php?id=` | same as POST | แก้ไขบัญชี |
| DELETE | `/api/accounts.php?id=` | — | ลบบัญชี |
| GET | `/api/categories.php` | `?type=income\|expense` | ดึงหมวดหมู่ |
| POST | `/api/categories.php` | `{name, type, icon, color}` | เพิ่มหมวดหมู่ |
| PUT | `/api/categories.php?id=` | same as POST | แก้ไข |
| DELETE | `/api/categories.php?id=` | — | ลบ |
| GET | `/api/budgets.php` | `?month=YYYY-MM` | งบ + ยอดใช้จริง |
| POST | `/api/budgets.php` | `{category_id, amount, month}` | ตั้งงบ |
| PUT | `/api/budgets.php?id=` | `{amount}` | แก้งบ |
| DELETE | `/api/budgets.php?id=` | — | ลบงบ |
| GET | `/api/stats.php` | `?month=YYYY-MM` | สรุปสถิติ Dashboard |

---

## 🧩 Coding Conventions

### PHP 5.6 ข้อบังคับเข้มงวด

| ห้ามใช้ | ใช้แทน |
|---------|--------|
| `??` null coalescing (PHP 7.0+) | `isset($x) ? $x : $default` |
| `fn() =>` arrow function (PHP 7.4+) | `function() use (...) {}` |
| `str_starts_with()` (PHP 8.0+) | `strpos($s, $prefix) === 0` |
| `str_contains()` (PHP 8.0+) | `strpos($s, $needle) !== false` |
| Return type `: void` / `: string` (PHP 7.0+) | ไม่ประกาศ return type |
| `mixed` type hint (PHP 8.0+) | ไม่ประกาศ type |
| `?string` nullable type (PHP 7.1+) | ไม่ประกาศ type |
| `[...$arr]` spread in array (PHP 7.4+) | `array_merge()` |
| `match` expression (PHP 8.0+) | `switch` |
| `array_is_list()` (PHP 8.1+) | ไม่ใช้ |
| Named arguments `func(name: val)` (PHP 8.0+) | positional เท่านั้น |
| `list()` = destructuring short `[$a,$b]` (PHP 7.1+) | `list($a, $b) = ...` |

**เพิ่มเติม:**
- ใช้ PDO เท่านั้น (ห้าม `mysqli_*`)
- Prepared statements ทุก query ที่รับ input จากภายนอก
- ทุก API file เริ่มด้วย `header('Content-Type: application/json')`
- Error handling ด้วย `try/catch` และ return `success: false`
- ฟังก์ชันใน `helpers.php` ตั้งชื่อแบบ `snake_case`
- **`ON CONFLICT ... DO UPDATE`** (SQLite UPSERT) ต้องการ SQLite 3.24+ — ใช้ `INSERT OR REPLACE` หรือ SELECT ก่อนแล้ว INSERT/UPDATE แทน เพื่อความปลอดภัย

### JavaScript (Alpine.js)
- State ทั้งหมดอยู่ใน `x-data`
- เรียก API ด้วย `fetch()` + `async/await`
- ห้าม jQuery
- ใช้ `x-cloak` + CSS `[x-cloak]{display:none}` ป้องกัน flash

### CSS (Tailwind v3 + DaisyUI)
- ใช้ DaisyUI component classes เป็นหลัก (`btn`, `card`, `modal`, `input` ฯลฯ)
- Custom style เพิ่มใน `app.css` เท่านั้น
- **Mobile-first เสมอ** — ไม่ต้องมี breakpoint `md:` หรือ `lg:` เพราะ desktop ไม่ได้ใช้
- Bottom sheet ใช้ `fixed bottom-0 left-0 right-0` + `transition transform`
- Safe area: `padding-bottom: env(safe-area-inset-bottom)`

### ไฟล์ HTML/PHP
- `header.php` include ในทุก page — มี `<head>` + bottom nav
- `footer.php` ปิดท้ายทุกไฟล์ — ปิด `</body></html>` + include scripts
- ตัวแปร PHP ใน view ใช้ `htmlspecialchars()` เสมอ (ใช้ `h()` จาก helpers)

---

## 🌐 Environment

- Local: `http://localhost/money/`
- Shared Hosting: วาง app ไว้ใน root หรือ subfolder ของ public_html
- **PHP: 5.6** (ห้ามใช้ feature ที่ต้องการ PHP 7+)
- SQLite file: `database/money_manager.db`
- ไฟล์ DB **ต้องไม่ commit** ลง Git (เพิ่มใน `.gitignore`)
- **ไม่ต้องการ** Composer, npm, build step ใดๆ — pure PHP + CDN

---

## 🌱 Sample Data (Phase 12)

ข้อมูลตัวอย่างสำหรับ demo — เดือนปัจจุบัน

### หมวดหมู่เพิ่มเติม (id 16–19 — is_default=1)
| id | ชื่อ | ประเภท | icon |
|----|------|--------|------|
| 16 | ครอบครัว | expense | 👨‍👩‍👧 |
| 17 | ชำระหนี้ | expense | 💳 |
| 18 | ไอที/โดเมน | expense | 🖥️ |
| 19 | ภาษี | expense | 🏛️ |

### รายการ Sample (บัญชี: ธนาคาร กสิกร)
| รายการ | ประเภท | หมวดหมู่ | จำนวน |
|--------|--------|----------|-------|
| เงินเดือน | income | เงินเดือน | 100,000 |
| ค่าเช่าบ้าน | expense | ที่พักอาศัย | 15,000 |
| ให้ภรรยา | expense | ครอบครัว | 10,000 |
| จ่ายบัตรเครดิต | expense | ชำระหนี้ | 6,500 |
| ค่าเน็ต | expense | ค่าสาธารณูปโภค | 750 |
| ค่าเก็บขยะ | expense | ค่าสาธารณูปโภค | 700 |
| ค่าน้ำ | expense | ค่าสาธารณูปโภค | 300 |
| จ่ายค่ายืม | expense | ชำระหนี้ | 1,000 |
| ค่า domain gosoft.com | expense | ไอที/โดเมน | 907 |
| ค่าภาษีประจำปี | expense | ภาษี | 945 |

> รายรับรวม: **100,000** | รายจ่ายรวม: **39,102** | สุทธิ: **+19,898**

### วิธีโหลด Sample Data
- ไฟล์ `database/sample_data.sql` — INSERT ด้วย date เดือนปัจจุบัน
- สร้างหน้า `seed_sample.php` สำหรับ run ครั้งเดียว (ลบทิ้งหลังใช้)
- ป้องกัน duplicate ด้วย `INSERT OR IGNORE`

---

## 🚫 .gitignore

```
database/money_manager.db
.DS_Store
node_modules/
*.log
```
