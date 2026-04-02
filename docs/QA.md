# QA.md — แผนงาน Quality Assurance

> ทดสอบด้วยตนเอง (Manual Testing) บน XAMPP / Chrome DevTools (Mobile Emulation)  
> Device target: iPhone 375px, Android 360px  
> เครื่องหมาย: ✅ ผ่าน | ❌ ไม่ผ่าน | ⏭️ ยังไม่ได้ทดสอบ

---

## QA Phase 0 — Project Setup

| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 0.1 | เปิด `http://localhost/money/` | แสดงหน้าเว็บได้ ไม่มี PHP error | ⏭️ |
| 0.2 | CDN โหลดครบ (Tailwind, DaisyUI, Alpine.js, Chart.js) | Network tab: 200 OK ทุก CDN | ⏭️ |
| 0.3 | Tailwind + DaisyUI ทำงาน | `btn btn-primary` มีสีแสดง | ⏭️ |
| 0.4 | Alpine.js ทำงาน | `x-data` / `x-show` ทำงาน | ⏭️ |
| 0.5 | `php -l` ทุกไฟล์ | ไม่มี syntax error | ⏭️ |

---

## QA Phase 1 — Database Layer

| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 1.1 | โหลดหน้าแรกครั้งแรก | ไฟล์ `database/money_manager.db` ถูกสร้าง | ✅ |
| 1.2 | ตรวจสอบตาราง | มีตาราง accounts, categories, transactions, budgets | ✅ |
| 1.3 | Seed data โหลด | มีหมวดหมู่ default อย่างน้อย 10 รายการ | ✅ |
| 1.4 | PDO error mode | query ผิด → แสดง PHP exception ชัดเจน | ✅ |
| 1.5 | `helpers.php` functions | เรียก `json_response()` ได้ไม่ error | ✅ |
| 1.6 | SQLite WAL mode | `PRAGMA journal_mode` = wal | ✅ |
| 1.7 | PHP 5.6 compat — db.php | `php -l includes/db.php` ไม่มี error | ✅ |
| 1.8 | PHP 5.6 compat — helpers.php | `php -l includes/helpers.php` ไม่มี error | ✅ |

---

## QA Phase 2 — API Layer

### 2.1 Accounts API

| # | Test Case | Method | Expected | Status |
|---|-----------|--------|----------|--------|
| 2.1.1 | ดึงบัญชีทั้งหมด | GET `/api/accounts.php` | `success: true`, array ของบัญชี | ✅ |
| 2.1.2 | เพิ่มบัญชีใหม่ (ข้อมูลครบ) | POST | `success: true`, id ใหม่ | ✅ |
| 2.1.3 | เพิ่มบัญชี (ไม่มี name) | POST | `success: false`, message | ✅ |
| 2.1.4 | แก้ไขบัญชี | PUT `?id=1` | `success: true`, ข้อมูลอัปเดต | ✅ |
| 2.1.5 | ลบบัญชี | DELETE `?id=1` | `success: true`, is_active = 0 | ✅ |
| 2.1.6 | PHP 5.6 compat | `php -l api/accounts.php` | ไม่มี syntax error | ✅ |

### 2.2 Categories API

| # | Test Case | Method | Expected | Status |
|---|-----------|--------|----------|--------|
| 2.2.1 | ดึงหมวดหมู่ทั้งหมด | GET | array ครบ | ✅ |
| 2.2.2 | ดึงเฉพาะ expense | GET `?type=expense` | เฉพาะ type=expense | ✅ |
| 2.2.3 | เพิ่มหมวดหมู่ | POST | success: true | ✅ |
| 2.2.4 | เพิ่มโดยไม่มี type | POST | success: false | ✅ |
| 2.2.5 | แก้ไข / ลบ | PUT / DELETE | success: true | ✅ |
| 2.2.6 | PHP 5.6 compat | `php -l api/categories.php` | ไม่มี syntax error | ✅ |

### 2.3 Transactions API

| # | Test Case | Method | Expected | Status |
|---|-----------|--------|----------|--------|
| 2.3.1 | ดึง transactions เดือนปัจจุบัน | GET `?month=YYYY-MM` | array พร้อม join ข้อมูล | ✅ |
| 2.3.2 | เพิ่ม income | POST | success: true, balance บัญชี **เพิ่ม** | ✅ |
| 2.3.3 | เพิ่ม expense | POST | success: true, balance บัญชี **ลด** | ✅ |
| 2.3.4 | เพิ่ม transfer | POST | success: true, ต้นทาง **ลด**, ปลายทาง **เพิ่ม** | ✅ |
| 2.3.5 | เพิ่ม (amount = 0) | POST | success: false | ✅ |
| 2.3.6 | เพิ่ม (amount ติดลบ) | POST | success: false | ✅ |
| 2.3.7 | แก้ไข transaction | PUT | balance rollback แล้วคำนวณใหม่ถูก | ✅ |
| 2.3.8 | ลบ transaction | DELETE | success: true, balance rollback ถูกต้อง | ✅ |
| 2.3.9 | Filter by account_id | GET `?account_id=1` | เฉพาะบัญชีนั้น | ✅ |
| 2.3.10 | Filter by type | GET `?type=expense` | เฉพาะ expense | ✅ |
| 2.3.11 | PHP 5.6 compat | `php -l api/transactions.php` | ไม่มี syntax error | ✅ |

### 2.4 Budgets API

| # | Test Case | Method | Expected | Status |
|---|-----------|--------|----------|--------|
| 2.4.1 | ดึงงบเดือนนี้ | GET `?month=YYYY-MM` | array พร้อม spent จริง | ✅ |
| 2.4.2 | ตั้งงบใหม่ | POST | success: true | ✅ |
| 2.4.3 | ตั้งงบซ้ำ (category+month ซ้ำ) | POST | UPSERT / อัปเดตได้ ไม่ error | ✅ |
| 2.4.4 | `spent` คำนวณจาก transactions จริง | GET | spent = SUM transactions หมวดนั้น | ✅ |
| 2.4.5 | แก้ไข / ลบงบ | PUT / DELETE | success: true | ✅ |
| 2.4.6 | PHP 5.6 compat | `php -l api/budgets.php` | ไม่มี syntax error | ✅ |

### 2.5 Stats API

| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 2.5.1 | ดึง stats เดือนปัจจุบัน | income, expense, net ถูกต้อง | ✅ |
| 2.5.2 | income + expense = net | net = income - expense | ✅ |
| 2.5.3 | Pie chart data | array หมวดหมู่พร้อม amount | ✅ |
| 2.5.4 | Line chart data | ทุกวันในเดือน | ✅ |
| 2.5.5 | เดือนที่ไม่มีข้อมูล | income=0, expense=0 ไม่ error | ✅ |
| 2.5.6 | PHP 5.6 compat | `php -l api/stats.php` | ไม่มี syntax error | ✅ |

---

## QA Phase 3 — Mobile Layout & Components

| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 3.1 | App แสดงบน iPhone 375px | ไม่มี overflow, ไม่มี horizontal scroll | ⏭️ |
| 3.2 | App แสดงบน Android 360px | ไม่มี overflow | ⏭️ |
| 3.3 | Bottom Nav แสดงครบ 5 ไอคอน | Dashboard, Transactions, FAB, Accounts, More | ⏭️ |
| 3.4 | FAB ตรงกลาง bottom nav | นูนขึ้นมา, tap ได้ง่าย | ⏭️ |
| 3.5 | Active tab highlight | หน้าปัจจุบันถูก highlight | ⏭️ |
| 3.6 | iPhone safe area | bottom nav ไม่ทับ home indicator | ⏭️ |
| 3.7 | Top app bar แสดงชื่อหน้า | ชื่อถูกต้อง ไม่ถูก clip | ⏭️ |
| 3.8 | `[x-cloak]` ไม่ flash | ไม่เห็น content กะพริบก่อน Alpine init | ⏭️ |
| 3.9 | Chart.js functions โหลดได้ | ไม่มี console error | ⏭️ |
| 3.10 | Bottom sheet slide up | animate smooth, overlay มืดด้านหลัง | ⏭️ |

---

## QA Phase 4 — Dashboard

| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 4.1 | Summary Cards โหลด | แสดงตัวเลขถูกต้อง | ✅ |
| 4.2 | Net = income - expense | ตัวเลขตรงกับ API | ✅ |
| 4.3 | Line Chart แสดง | ไม่มี JS error, กราฟ render | ⏭️ |
| 4.4 | Pie Chart แสดง | สัดส่วนหมวดหมู่ถูกต้อง | ⏭️ |
| 4.5 | รายการล่าสุด 10 รายการ | Card style ไม่ overflow | ⏭️ |
| 4.6 | เปลี่ยนเดือน (← →) | ข้อมูลทุก section อัปเดต ไม่ reload | ⏭️ |
| 4.7 | เดือนที่ไม่มีข้อมูล | empty state ไม่ error | ✅ |
| 4.8 | Loading skeleton | แสดงระหว่างโหลด | ⏭️ |
| 4.9 | FAB → bottom sheet เปิดได้ | sheet slide up | ⏭️ |
| 4.10 | บันทึกรายการจาก sheet | toast + dashboard reload | ⏭️ |

---

## QA Phase 5 — Transactions Page

| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 5.1 | แสดงรายการเดือนปัจจุบัน | Card layout สีถูก (เขียว/แดง) | ⏭️ |
| 5.2 | FAB tap → bottom sheet เปิด | Sheet slide up ได้ | ⏭️ |
| 5.3 | เพิ่ม income — ครบฟิลด์ | บันทึกสำเร็จ, Toast แสดง | ⏭️ |
| 5.4 | เพิ่ม expense | balance บัญชีลด | ⏭️ |
| 5.5 | เพิ่ม transfer | balance ต้นทางลด, ปลายทางเพิ่ม | ⏭️ |
| 5.6 | input amount บน mobile | keyboard เป็น numpad (type=number/decimal) | ⏭️ |
| 5.7 | เพิ่ม — ไม่กรอก amount | validation error แสดง ไม่ submit | ⏭️ |
| 5.8 | แก้ไขรายการ | ข้อมูลเดิมโหลดใน sheet | ⏭️ |
| 5.9 | ลบรายการ | Confirm dialog แสดง, balance rollback | ⏭️ |
| 5.10 | Filter chips scroll แนวนอน | scroll ได้ ไม่ wrap | ⏭️ |
| 5.11 | Filter by month / type / account | ผลลัพธ์ถูกต้อง | ⏭️ |
| 5.12 | Bottom sheet ปิดได้ | tap overlay หรือ swipe down | ⏭️ |

---

## QA Phase 6 — Accounts Page

| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 6.1 | Account cards แสดงครบ | icon, ชื่อ, ยอด ครบ | ⏭️ |
| 6.2 | Balance สีตามจำนวน | บวก = เขียว, ลบ = แดง (credit) | ⏭️ |
| 6.3 | เพิ่มบัญชี | บันทึกสำเร็จ, card ใหม่ปรากฏ | ⏭️ |
| 6.4 | เพิ่มโดยไม่มีชื่อ | validation error | ⏭️ |
| 6.5 | แก้ไขบัญชี | ข้อมูลอัปเดต | ⏭️ |
| 6.6 | ลบบัญชีที่ไม่มี transaction | ลบสำเร็จ | ⏭️ |
| 6.7 | ลบบัญชีที่มี transaction | error message | ⏭️ |
| 6.8 | ยอดรวมทั้งหมด | SUM balance ถูกต้อง | ⏭️ |

---

## QA Phase 7 — Categories Page

| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 7.1 | Tab รายรับ / รายจ่าย | สลับได้ แสดงเฉพาะ type ที่ตรง | ⏭️ |
| 7.2 | เพิ่มหมวดหมู่ | บันทึกสำเร็จ | ⏭️ |
| 7.3 | เพิ่มโดยไม่มีชื่อ | validation error | ⏭️ |
| 7.4 | แก้ไขหมวดหมู่ | ข้อมูลอัปเดต | ⏭️ |
| 7.5 | ลบหมวดที่ไม่มี transaction | ลบสำเร็จ | ⏭️ |
| 7.6 | ลบหมวดที่มี transaction | error message | ⏭️ |
| 7.7 | Default categories | ปุ่มลบซ่อน/ไม่มี | ⏭️ |

---

## QA Phase 8 — Budgets Page

| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 8.1 | Progress bar ทุกหมวด | render ถูกต้อง ไม่ overflow | ⏭️ |
| 8.2 | % ถูกต้อง | spent/budget × 100 | ⏭️ |
| 8.3 | Progress สีเหลือง > 80% | สีเปลี่ยน | ⏭️ |
| 8.4 | Progress สีแดง ≥ 100% | สีแดง + badge เกินงบ | ⏭️ |
| 8.5 | ตั้งงบใหม่ | บันทึกสำเร็จ | ⏭️ |
| 8.6 | แก้ไขงบ | ตัวเลขอัปเดต | ⏭️ |
| 8.7 | ลบงบ | progress หาย | ⏭️ |
| 8.8 | เปลี่ยนเดือน | ข้อมูลงบโหลดใหม่ | ⏭️ |
| 8.9 | หมวดที่ยังไม่ตั้งงบ | แสดงรายการ | ⏭️ |

---

## QA Phase 9 — Reports Page

| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 9.1 | Line Chart 12 เดือน | render ไม่มี JS error | ⏭️ |
| 9.2 | Bar Chart | render ไม่มี JS error | ⏭️ |
| 9.3 | Doughnut Chart | สัดส่วนถูกต้อง | ⏭️ |
| 9.4 | Top 5 หมวดหมู่ | เรียง descending | ⏭️ |
| 9.5 | ไม่มีข้อมูล | กราฟเป็น 0 ไม่ crash | ⏭️ |
| 9.6 | Charts fit mobile | ไม่ถูก clip ที่ขอบ | ⏭️ |

---

## QA Phase 10 — Polish, PHP 5.6 Audit & Mobile Final

### PHP 5.6 Compatibility
| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 10.1 | `php -l` ทุกไฟล์ PHP | ไม่มี syntax error | ✅ |
| 10.2 | ไม่มี `??` operator | grep ไม่พบ `??` | ✅ |
| 10.3 | ไม่มี `fn()` arrow function | grep ไม่พบ `fn(` | ✅ |
| 10.4 | ไม่มี `str_starts_with` | grep ไม่พบ | ✅ |
| 10.5 | ไม่มี return type declaration | grep ไม่พบ `): ` ใน PHP | ✅ |
| 10.6 | ทดสอบบน shared hosting | ไม่มี 500 error | ⏭️ |

### UX & Mobile
| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 10.7 | Toast หลัง save | แสดง 3 วินาที แล้วหาย | ⏭️ |
| 10.8 | Toast หลัง delete | สีแดง | ⏭️ |
| 10.9 | Input ตัวเลข | keyboard = numpad บน iOS/Android | ⏭️ |
| 10.10 | Add to Home Screen | icon แสดง, app ไม่มี browser chrome | ⏭️ |
| 10.11 | Confirm ก่อนลบทุกที่ | ไม่ลบทันที | ⏭️ |
| 10.12 | Empty state ทุกหน้า | illustration + ข้อความ | ⏭️ |

### Security
| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 10.13 | SQL Injection (`' OR '1'='1`) | ไม่ error, ไม่ดึงข้อมูล (prepared stmt) | ⏭️ |
| 10.14 | XSS (`<script>alert(1)</script>` ใน note) | แสดงเป็น text ไม่ execute | ⏭️ |
| 10.15 | API รับ amount ติดลบ | validate ปฏิเสธ | ⏭️ |

### Mobile Browser
| # | Browser | Device | Expected | Status |
|---|---------|--------|----------|--------|
| 10.16 | Chrome Mobile (Android) | 360px | ทำงานปกติ ไม่มี overflow | ⏭️ |
| 10.17 | Safari (iPhone) | 375px | ทำงานปกติ, safe area ถูกต้อง | ⏭️ |
| 10.18 | Chrome DevTools Mobile Emulate | iPhone 12 | ทำงานปกติ | ⏭️ |

---

## QA Phase 11 — Authentication (Password Login)

### First-Time Setup
| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 11.1 | เปิด app ครั้งแรก (ไม่มี password ใน DB) | redirect → `setup.php` | ✅ |
| 11.2 | `setup.php` — กรอก password + confirm ตรงกัน | บันทึก hash ลง DB, login อัตโนมัติ, redirect `index.php` | ✅ |
| 11.3 | `setup.php` — password ไม่ตรงกัน | error message, ไม่บันทึก | ✅ |
| 11.4 | `setup.php` — password ว่าง | validation error | ✅ |
| 11.5 | เข้า `setup.php` หลังตั้ง password แล้ว | redirect `login.php` (ป้องกัน reset) | ✅ |

### Login / Logout
| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 11.6 | เปิด `index.php` โดยไม่ login | redirect → `login.php` | ✅ |
| 11.7 | Login ด้วย password ถูกต้อง | redirect → `index.php` | ✅ |
| 11.8 | Login ด้วย password ผิด | error message, ไม่ redirect | ✅ |
| 11.9 | Login ด้วย password ว่าง | validation error | ✅ |
| 11.10 | กด Logout | session ถูกลบ, redirect → `login.php` | ✅ |
| 11.11 | หลัง logout กด Back browser | ไม่กลับไปหน้า protected ได้ | ✅ |

### API Protection
| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 11.12 | เรียก `api/transactions.php` โดยไม่มี session | HTTP 401, `{success:false}` | ✅ |
| 11.13 | เรียก `api/accounts.php` โดยไม่มี session | HTTP 401 | ✅ |
| 11.14 | เรียก `api/stats.php` โดยไม่มี session | HTTP 401 | ✅ |

### Change Password
| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 11.15 | `settings.php` — เปลี่ยน password (current ถูก) | บันทึก hash ใหม่สำเร็จ | ✅ |
| 11.16 | `settings.php` — current password ผิด | error message | ✅ |
| 11.17 | หลังเปลี่ยน password — login ด้วยรหัสใหม่ | ผ่าน | ✅ |

### PHP 5.6
| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 11.18 | `php -l includes/auth.php` | ไม่มี syntax error | ✅ |
| 11.19 | `php -l login.php` / `setup.php` / `logout.php` / `settings.php` | ไม่มี syntax error | ✅ |
| 11.20 | `php -l api/auth.php` | ไม่มี syntax error | ✅ |

---

## QA Phase 12 — Sample Data

| # | Test Case | Expected | Status |
|---|-----------|----------|--------|
| 12.1 | เพิ่มหมวดหมู่ใหม่ 4 รายการใน seed.sql | id 16-19 ปรากฏใน categories page | ✅ |
| 12.2 | รัน `seed_sample.php` ครั้งแรก | insert 10 transactions สำเร็จ | ✅ |
| 12.3 | รัน `seed_sample.php` ซ้ำ | ไม่ insert ซ้ำ (idempotent) | ✅ |
| 12.4 | Dashboard เดือนปัจจุบัน — รายรับ | แสดง 59,000 ✓ | ✅ |
| 12.5 | Dashboard เดือนปัจจุบัน — รายจ่าย | แสดง 39,102 ✓ | ✅ |
| 12.6 | Dashboard — ยอดสุทธิ | แสดง +19,898 ✓ | ✅ |
| 12.7 | Transactions page — แสดงครบ 10 รายการ | ไม่มีรายการหาย | ✅ |
| 12.8 | Budgets page — หมวดหมู่ใหม่ปรากฏในส่วน "ยังไม่ตั้งงบ" | ครอบครัว, ชำระหนี้, ไอที/โดเมน, ภาษี | ✅ |
| 12.9 | Reports page — Bar chart มีหมวดหมู่ใหม่ | render ถูกต้อง ไม่ crash | ✅ |
| 12.10 | Account balance (ธนาคาร กสิกร) หลัง seed | เพิ่มขึ้น 19,898 จากยอดเดิม | ✅ |

---

## 📊 QA Summary Tracker

| Phase | Total | ✅ Pass | ❌ Fail | ⏭️ Pending |
|-------|-------|---------|---------|------------|
| Phase 0 | 5 | 0 | 0 | 5 |
| Phase 1 | 8 | 8 | 0 | 0 |
| Phase 2 | 31 | 31 | 0 | 0 |
| Phase 3 | 10 | 0 | 0 | 10 |
| Phase 4 | 10 | 3 | 0 | 7 |
| Phase 5 | 12 | 0 | 0 | 12 |
| Phase 6 | 8 | 0 | 0 | 8 |
| Phase 7 | 7 | 0 | 0 | 7 |
| Phase 8 | 9 | 0 | 0 | 9 |
| Phase 9 | 6 | 0 | 0 | 6 |
| Phase 10 | 18 | 5 | 0 | 13 |
| Phase 11 | 20 | 20 | 0 | 0 |
| Phase 12 | 10 | 10 | 0 | 0 |
| **Total** | **154** | **77** | **0** | **77** |
