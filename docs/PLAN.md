# PLAN.md — แผนงานพัฒนา Money Manager

> **Target:** PHP 5.6 Shared Hosting | Mobile-Only App | ไม่มี build step

---

## Phase 0 — Project Setup ✅
**เป้าหมาย:** ตั้งโครงสร้างโปรเจกต์ให้พร้อมพัฒนา

### Tasks
- [x] สร้างโครงสร้างไฟล์และโฟลเดอร์ตาม AGENTS.md
- [x] สร้าง `assets/css/app.css`
- [x] สร้าง `.gitignore`
- [x] สร้าง `index.php` เบื้องต้น

### Deliverables
- โครงสร้างโฟลเดอร์ครบ ✅

---

## Phase 1 — Database Layer ✅
**เป้าหมาย:** สร้างฐานข้อมูลและระบบเชื่อมต่อ (PHP 5.6 compatible)

### Tasks
- [x] เขียน `database/schema.sql` — DDL ตารางทั้งหมด
- [x] เขียน `database/seed.sql` — หมวดหมู่ default 15 รายการ + 2 บัญชี
- [x] เขียน `includes/db.php`
  - [x] เชื่อมต่อ SQLite ด้วย PDO (PHP 5.6 compat)
  - [x] Auto-migrate: สร้างตารางถ้ายังไม่มี
  - [x] Set PDO error mode = ERRMODE_EXCEPTION
  - [x] WAL mode
- [x] เขียน `includes/helpers.php`
  - [x] `json_response($data, $success, $http_code)`
  - [x] `get_method()`
  - [x] `get_json_body()`
  - [x] `validate_required($data, $fields)`
  - [x] `format_money($amount)`
  - [x] `h($str)` — htmlspecialchars wrapper

### ⚠️ PHP 5.6 Fix ✅ (เสร็จก่อน Phase 3)
- [x] แก้ `includes/db.php` — ลบ `fn() =>`, `str_starts_with()`, `: void`, type hints
- [x] แก้ `includes/helpers.php` — ลบ `mixed`, `: void`, `?string` type hints
- [x] แก้ `api/transactions.php` — ลบ `: void` return type, แก้ named param ซ้ำ
- [x] แก้ `api/stats.php` — แทน `cal_days_in_month()` ด้วย `date('t', mktime(...))`
- [x] แก้ `api/budgets.php` — แทน `ON CONFLICT DO UPDATE` ด้วย SELECT+INSERT/UPDATE 2-step
- [x] ทดสอบ PHP 5.6 syntax ด้วย `php -l` ทุกไฟล์ — ผ่านทั้งหมด

### Deliverables
- ไฟล์ `database/money_manager.db` ถูกสร้างอัตโนมัติ ✅
- ตาราง 4 ตาราง ✅
- Seed data 15 หมวดหมู่ ✅

---

## Phase 2 — API Layer ✅
**เป้าหมาย:** สร้าง REST API ครบทุก endpoint

### 2.1 Accounts API ✅
### 2.2 Categories API ✅
### 2.3 Transactions API ✅
### 2.4 Budgets API ✅
### 2.5 Stats API ✅

### Deliverables
- ทุก endpoint ตอบ JSON ✅
- Balance อัปเดตอัตโนมัติ ✅

---

## Phase 3 — Mobile Layout & Shared Components
**เป้าหมาย:** สร้าง layout มือถือที่ทุกหน้าใช้ร่วมกัน

### Tasks

#### `includes/header.php` ✅
- [x] `<head>` — meta viewport, CDN: Tailwind v3, DaisyUI v4, Alpine.js v3, Chart.js v3
- [x] `<meta name="theme-color">` และ `apple-mobile-web-app-capable`
- [x] App shell: `max-w-[480px] mx-auto` wrapping container
- [x] **Top App Bar** — ชื่อหน้า + optional back button

#### `includes/footer.php` ✅
- [x] **Bottom Navigation Bar** — 5 tabs: หน้าหลัก, รายการ, FAB, บัญชี, เพิ่มเติม
- [x] FAB ตรงกลางนูนขึ้นมา
- [x] More Menu (modal) — Budgets, Categories, Reports
- [x] Global Toast system (`showToast()`)
- [x] Safe area padding

#### `assets/css/app.css` ✅
- [x] `[x-cloak]`, bottom sheet, safe area, toast animation
- [x] filter chips, type chips, amount input, progress bars
- [x] iOS font-size fix (ป้องกัน auto-zoom)

#### `assets/js/charts.js` ✅
- [x] `renderLineChart()` — income vs expense รายวัน
- [x] `renderPieChart()` — doughnut chart หมวดหมู่
- [x] `renderBarChart()` — horizontal bar (mobile-friendly)

### Deliverables
- Bottom nav ทำงานได้บนทุกหน้า
- Safe area รองรับ iPhone notch/home indicator
- FAB เปิด bottom sheet เพิ่มรายการได้

---

## Phase 4 — Dashboard (index.php)
**เป้าหมาย:** หน้าแรกสรุปภาพรวมการเงินแบบ mobile

### Tasks
- [ ] Month picker (scroll/select) — ด้านบน
- [ ] Summary Cards (2×2 grid)
  - รายรับ / รายจ่าย / ยอดสุทธิ / ยอดรวมบัญชี
- [ ] Swipeable account balance cards (horizontal scroll)
- [ ] Line Chart (income vs expense) — compact height
- [ ] Pie/Doughnut Chart — รายจ่ายแยกหมวดหมู่
- [ ] Recent transactions list (10 รายการล่าสุด) — card style
- [ ] Loading skeleton ระหว่างโหลด
- [ ] Empty state เมื่อไม่มีข้อมูล

### Deliverables
- Dashboard โหลดจาก `/api/stats.php` ด้วย Alpine.js
- เปลี่ยนเดือนได้โดยไม่ reload

---

## Phase 5 — Transactions Page (transactions.php)
**เป้าหมาย:** รายการธุรกรรมแบบ mobile list

### Tasks
- [ ] Filter chips (เดือน, ประเภท, บัญชี) — horizontal scroll
- [ ] Transaction list — Card ทุกรายการ
  - วันที่, icon หมวดหมู่, ชื่อ, หมายเหตุ, จำนวน (เขียว/แดง)
  - Swipe to delete (optional)
- [ ] **Bottom Sheet** เพิ่ม/แก้ไขรายการ
  - Slide up จากด้านล่าง
  - ประเภท: income / expense / transfer (toggle button group)
  - จำนวนเงิน (numpad หรือ input ใหญ่)
  - บัญชี, หมวดหมู่, วันที่, หมายเหตุ
- [ ] Confirm dialog ก่อนลบ
- [ ] Toast notification หลัง save/delete

### Deliverables
- CRUD transaction ครบผ่าน Alpine.js fetch
- Balance อัปเดตทันที

---

## Phase 6 — Accounts Page (accounts.php)
**เป้าหมาย:** จัดการบัญชีหลายบัญชีแบบ mobile

### Tasks
- [ ] Account Cards — icon, ชื่อ, ประเภท, ยอดคงเหลือ (สีตาม balance)
- [ ] ยอดรวมทั้งหมดด้านบน
- [ ] Bottom Sheet เพิ่ม/แก้ไขบัญชี
  - ชื่อ, ประเภท (cash/bank/credit/saving)
  - ยอดตั้งต้น, สี (color picker), icon (emoji)
- [ ] Confirm + ป้องกันลบถ้ามี transaction

### Deliverables
- CRUD บัญชีครบ, ยอดสอดคล้องกับ transactions

---

## Phase 7 — Categories Page (categories.php)
**เป้าหมาย:** จัดการหมวดหมู่

### Tasks
- [ ] Toggle tabs: รายรับ / รายจ่าย
- [ ] Category list — icon + ชื่อ + สี
- [ ] Bottom Sheet เพิ่ม/แก้ไข (ชื่อ, type, icon emoji, สี)
- [ ] Default categories — ปุ่มลบซ่อน

### Deliverables
- CRUD หมวดหมู่ครบ

---

## Phase 8 — Budgets Page (budgets.php)
**เป้าหมาย:** ตั้งและติดตามงบประมาณ

### Tasks
- [ ] Month picker
- [ ] Summary chips: งบรวม / ใช้ไป / คงเหลือ
- [ ] Progress card แต่ละหมวดหมู่
  - Progress bar (เหลือง >80%, แดง ≥100%)
  - ยอดงบ vs ใช้จริง
- [ ] Bottom Sheet ตั้ง/แก้ไขงบ
- [ ] Section "ยังไม่ตั้งงบ" ด้านล่าง

### Deliverables
- ตั้งงบได้, progress bar ถูกต้อง real-time

---

## Phase 9 — Reports Page (reports.php)
**เป้าหมาย:** กราฟสถิติเชิงลึก

### Tasks
- [ ] Line Chart: income vs expense 12 เดือน
- [ ] Bar Chart: รายจ่ายแยกหมวดหมู่ (เดือนปัจจุบัน)
- [ ] Doughnut Chart: สัดส่วนรายจ่าย
- [ ] Top 5 รายการ — list style
- [ ] Year picker

### Deliverables
- กราฟทั้งหมดโหลดจาก API, ไม่ crash บน mobile

---

## Phase 11 — Authentication (Password Login) ✅
**เป้าหมาย:** เพิ่มระบบ login ด้วย password เพียงอย่างเดียว (single-user)

### Tasks

#### Database
- [x] เพิ่มตาราง `settings` ใน `database/schema.sql`
  ```sql
  CREATE TABLE IF NOT EXISTS settings (key TEXT PRIMARY KEY, value TEXT NOT NULL);
  ```
- [x] อัปเดต `includes/db.php` → `migrate()` สร้างตาราง settings ด้วย

#### Auth Core (`includes/auth.php`)
- [x] `require_auth()` — ตรวจ `$_SESSION['logged_in']`; redirect `login.php` ถ้าไม่ผ่าน
- [x] `require_api_auth()` — เช่นเดียวกัน แต่ return JSON `{success:false, message:'Unauthorized'}` + HTTP 401
- [x] `has_password(PDO $pdo)` — ตรวจว่า `settings` มี key `password_hash` หรือยัง
- [x] ไม่มี type hints ทุกฟังก์ชัน (PHP 5.6)

#### Login Pages
- [x] `login.php` — หน้า login (mobile-styled, ไม่ include header/footer)
  - Form: input password, ปุ่ม Login
  - POST ตรวจ password ด้วย `password_verify()` → set `$_SESSION['logged_in'] = true`
  - ถ้า `!has_password()` → redirect `setup.php`
  - ถ้า login สำเร็จ → redirect `index.php`
- [x] `setup.php` — ตั้งรหัสผ่านครั้งแรก (เข้าได้เฉพาะตอนยังไม่มี password)
  - Form: new password + confirm password
  - บันทึก `password_hash($plain, PASSWORD_DEFAULT)` ลง settings
  - Set session → redirect `index.php`
- [x] `logout.php` — `session_destroy()` → redirect `login.php`

#### Protect Existing Pages
- [x] เพิ่ม `require_once includes/auth.php; require_auth();` ใน **ทุก page** ก่อน `header.php`
  - `index.php`, `transactions.php`, `accounts.php`, `categories.php`, `budgets.php`, `reports.php`, `settings.php`
- [x] เพิ่ม `require_api_auth();` ใน **ทุก api/*.php** ก่อน logic หลัก

#### Settings Page (`settings.php`)
- [x] หน้าเปลี่ยน password — form: current + new + confirm
- [x] เพิ่ม link "ตั้งค่า" ใน More Menu (`footer.php`)
- [x] ปุ่ม Logout ใน settings page (หรือ More Menu)

#### PHP 5.6 compat
- [x] ใช้ `password_hash()` + `password_verify()` (พร้อมใช้ PHP 5.5+)
- [x] ใช้ `session_start()` ใน `auth.php` (เรียกก่อน output ทุกครั้ง)
- [x] `php -l` ทุกไฟล์ใหม่ผ่าน

### Deliverables
- เปิด app → login page (ถ้ายังไม่ login)
- ตั้ง password ครั้งแรกได้
- Login/Logout ทำงาน
- API ทุกตัวคืน 401 ถ้าไม่ได้ login

---

## Phase 12 — Sample Data (Demo Transactions) ✅
**เป้าหมาย:** โหลดข้อมูลตัวอย่างจริงเพื่อดูหน้าตาแอปได้ทันที

### Tasks

#### เพิ่มหมวดหมู่ใหม่ใน `database/seed.sql`
- [x] id 16: ครอบครัว (expense, 👨‍👩‍👧, #f43f5e, is_default=1)
- [x] id 17: ชำระหนี้ (expense, 💳, #dc2626, is_default=1)
- [x] id 18: ไอที/โดเมน (expense, 🖥️, #0284c7, is_default=1)
- [x] id 19: ภาษี (expense, 🏛️, #92400e, is_default=1)

#### `database/sample_data.sql`
- [x] INSERT transactions เดือนปัจจุบัน (account_id=2 ธนาคาร กสิกร):
  | note | type | cat_id | amount |
  |------|------|--------|--------|
  | เงินเดือน | income | 1 | 59000 |
  | ค่าเช่าบ้าน | expense | 8 | 18000 |
  | ให้ภรรยา | expense | 16 | 10000 |
  | จ่ายบัตรเครดิต | expense | 17 | 6500 |
  | ค่าเน็ต | expense | 12 | 750 |
  | ค่าเก็บขยะ | expense | 12 | 700 |
  | ค่าน้ำ | expense | 12 | 300 |
  | จ่ายค่ายืม | expense | 17 | 1000 |
  | ค่า domain awarasoft.com | expense | 18 | 907 |
  | ค่าภาษีประจำปี | expense | 19 | 945 |
- [x] UPDATE account balance หลัง insert (account_id=2: +59000-39102 = เพิ่ม 19898)
- [x] ใช้ `INSERT OR IGNORE` กัน duplicate

#### `seed_sample.php` (run-once helper)
- [x] หน้า PHP เรียกใช้งานครั้งเดียวที่ `/seed_sample.php`
- [x] ตรวจว่า sample data ยังไม่มี (SELECT COUNT ก่อน) — ไม่ insert ซ้ำ
- [x] แสดงสรุปว่า insert สำเร็จกี่รายการ
- [x] ต้อง Login ก่อน (เรียก `require_auth()`)
- [x] **ลบทิ้งหลังใช้งาน** (หรือ disable บน production)

### Deliverables
- โหลด `seed_sample.php` ครั้งเดียว → Dashboard มีข้อมูลจริงให้ดู
- รายรับ 59,000 | รายจ่าย 39,102 | สุทธิ +19,898

---

## Phase 10 — Polish & PHP 5.6 Final Audit
**เป้าหมาย:** เก็บรายละเอียด + ตรวจ compatibility สุดท้าย

### Tasks
- [x] รัน `php -l` ทุกไฟล์ (PHP 5.6 syntax check)
- [x] Toast notification ทุก action
- [x] Loading skeleton / spinner
- [x] Empty states ทุกหน้า
- [x] Keyboard: input type=number ขึ้น numpad บน mobile
- [x] `<meta name="apple-mobile-web-app-capable">` — Add to Home Screen
- [x] Favicon + `<title>` แต่ละหน้า
- [x] ตรวจ `htmlspecialchars()` ทุกจุดที่ output ข้อมูลจาก DB
- [ ] ทดสอบบน shared hosting จริง (manual)

### Deliverables
- PHP syntax ผ่าน `php -l` บน PHP 5.6
- ทดสอบใช้งานจริงบน mobile browser ได้ครบ
