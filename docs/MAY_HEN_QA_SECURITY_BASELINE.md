# MÂY HẸN (MAYHEN.VN) — QA & SECURITY BASELINE REPORT

> **Tài liệu đối soát hiện trạng kỹ thuật, cấu hình kiểm thử và ranh giới bảo mật.**  
> **Thời điểm xác lập:** 07/11/2026.  
> **Phiên bản hệ thống:** Laravel 13.35.0 — PHP 8.5.10 CLI.  
> **Trạng thái Git:** Branch `main`, sạch sẽ, bảo toàn toàn bộ mã nguồn.

---

## 1. MÔI TRƯỜNG RUNTIME & BASELINE KỸ THUẬT

### 1.1. Hạ tầng kiểm thử cục bộ (Test Environment)
- **Hệ điều hành:** Windows 10/11 Enterprise x64.
- **PHP CLI:** PHP 8.5.10 (Zend Engine v4.5.10 with OPcache).
- **Laravel Framework:** 13.35.0.
- **Cơ sở dữ liệu kiểm thử:** SQLite `:memory:` (chạy thông qua `RefreshDatabase`).
- **Cơ sở dữ liệu mục tiêu (Production Target):** MySQL 8.0+ / MariaDB 10.11+ (Hỗ trợ Row Locking `SELECT ... FOR UPDATE`, Collation `utf8mb4_unicode_ci`).
- **Storage Driver:** `local` (`storage/app/public` symlinked vào `public/storage`).
- **Cache Driver:** `array` (Test) / `file` hoặc `redis` (Staging/Production).
- **Queue Driver:** `sync` (Test) / `database` (Staging/Production).
- **Session Driver:** `file` (Web Client & Admin).

> [!WARNING]
> **Giới hạn môi trường SQLite in-memory:**  
> SQLite không thực thi câu lệnh khóa dòng `lockForUpdate()` theo cơ chế đa luồng độc lập như MySQL/MariaDB. Mọi khẳng định về concurrency/race condition trên SQLite chỉ mang tính kiểm tra cú pháp và logic nghiệp vụ, bắt buộc phải có test đối soát trên MySQL khi đưa vào quy trình CI/CD production.

---

## 2. MA TRẬN INVENTORY ROUTE & CONTROLLER (123 ROUTES)

Hệ thống hiện tại gồm **123 routes** phân chia rõ ràng theo 4 ranh giới bảo mật:

| Ranh giới bảo mật | Tiền tố Route | Middleware cốt lõi | Controller chịu trách nhiệm | Trạng thái bảo mật |
|---|---|---|---|---|
| **Marketing & Khám phá** | `/`, `/ve-may-hen`, `/bang-gia`, `/xem-thu/*` | `web`, `SecurityHeaders` | `HomeController`, `PageController`, `TemplateController` | ĐÃ KIỂM CHỨNG |
| **Xác thực & Session** | `/dang-nhap`, `/dang-ky`, `/dang-xuat` | `web`, `throttle:auth_attempts` | `Client\AuthController` | ĐÃ KIỂM CHỨNG |
| **Studio & Phòng Thu Thiệp** | `/tao-thiep`, `/api/editor/*` | `web`, `throttle:editor_actions` | `Client\InvitationEditorController` | CẦN TÁCH DRAFT/PUBLIC |
| **Thiệp Công Khai & RSVP** | `/thiep/{slug}`, `/thiep/{slug}/*` | `web`, `throttle:guest_interactions` | `Client\InvitationEditorController`, `Client\GuestController` | CẦN CHẶN THIỆP CHƯA XUẤT BẢN/KHÓA/HẾT HẠN |
| **Quản lý Tài Khoản Cặp Đôi** | `/tai-khoan/*` | `web`, `auth` | `AccountController`, `GuestController`, `WishManagementController`, `GiftManagementController` | CẦN FIX IDOR GUEST_ID & LOCK BYPASS |
| **Thanh Toán & Webhook** | `/thanh-toan/*`, `/api/payments/sepay/*` | `api`, `preventRequestForgery` (exempt) | `Client\CheckoutController` | CẦN BỔ SUNG TRANSACTION ID UNIQUE |
| **Quản Trị Viên (Admin Panel)** | `/admin/*` | `web`, `admin.auth`, `admin.manager`, `admin.super` | `Admin\*` Controllers (8 controllers) | ĐÃ ĐỐI SOÁT & HOÀN THIỆN RBAC |

---

## 3. KẾT QUẢ KIỂM THỬ BASELINE TRƯỚC AUDIT

- **Tổng số ca kiểm thử:** 176 Feature & Unit Tests.
- **Tỷ lệ vượt qua:** 176/176 (100% Passed).
- **Tổng số assertions:** 962 assertions.
- **Thời gian chạy toàn bộ suite:** ~3.5 giây.
- **Kiểm định mã nguồn (Pint PSR-12):** Clean, 0 warning, 0 error.
- **Tình trạng mã nguồn:** Đã gỡ bỏ toàn bộ code mock/demo ở các luồng cốt lõi, không có file tạm rác trong kho mã nguồn.

