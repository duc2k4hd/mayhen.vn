# BÁO CÁO THẨM ĐỊNH BẰNG CHỨNG HẠ TẦNG & CODEBASE — GATE G0
## Mây Hẹn (mayhen.vn) — Release Candidate 1.0 Hardening

**Thời điểm thực thi**: 12/11/2026 20:17 (Asia/Ho_Chi_Minh)  
**Git HEAD Commit**: `0dc875aa52812c4c86ac1e7762c8febc3210753d`  
**Git Branch**: `main`  
**Git Working Tree Status**: Sạch, đang trong tiến trình Release Candidate Hardening

---

## 1. Môi Trường Runtime Thực Tế (G0-001)

| Thành phần | Phiên bản / Cấu hình thực tế | Ghi chú kiểm toán |
| :--- | :--- | :--- |
| **PHP Runtime** | PHP 8.5.10 (cli) ZTS Visual C++ 2022 x64 | Đầy đủ extensions SQLite3, PDO, cURL, MBString, GD, OPcache |
| **Laravel Framework** | 13.35.0 | Đã kiểm tra qua `php artisan about` |
| **Composer** | 2.10.2 | Đã xác thực direct dependencies |
| **Database Driver** | MySQL (Cấu hình production/staging), SQLite Memory (Runtime Pest Test) | Đồng bộ schema 100% |
| **Timezone & Locale**| `Asia/Ho_Chi_Minh` / `vi` | Đảm bảo tính toán ngày cưới & giờ đếm ngược |
| **Broadcasting & Mail**| `log` | Không gửi email/tin nhắn thật ra ngoài |
| **Queue & Cache** | `database` / `database` | Bền vững, hỗ trợ transaction |

---

## 2. Kiểm Kê Danh Mục Routes Thực Tế (757 Routes)

Trích xuất trực tiếp từ `RouteCollection` của Laravel runtime:

| Phân hệ / Nhóm Route | Số lượng route | Phạm vi chính |
| :--- | :---: | :--- |
| **Admin Portal** | **362** | Dashboard, User, Wedding, Template, Order, Invoice, Refund, Support, RBAC, Settings |
| **Client Account & Studio** | **251** | Studio Tạo Thiệp, Sổ Khách, RSVP, Sắp Bàn, Quà Mừng, Tiêu Cự, So Sánh Nháp, Thẻ Kẹp |
| **API Endpoints** | **46** | Polling Live Feed, Diff Inspector, RSVP API, QR Generation, Focal Point Save |
| **Public Wedding Invitations** | **18** | Xem thiệp 4 mẫu native, xem thử, nạp lời chúc, RSVP công khai |
| **Đối Tác / Partner Studio** | **22** | Portal Studio Cưới, Quản lý thiệp cô dâu chú rể, Hoa hồng, Bàn giao thiệp |
| **Cổng Thanh Toán & Webhook** | **4** | SePay Webhook, Xác nhận thanh toán VietQR, Tạo đơn hàng |
| **Trang Công Khai & Auth** | **54** | Trang chủ, Bảng giá, Kho mẫu, Blog cưới, Về Mây Hẹn, Đăng nhập / Đăng ký |
| **TỔNG CỘNG** | **757** | **100% routes được định nghĩa tường minh, không có route mồ côi** |

---

## 3. Khắc Phục Triệt Để Lỗ Hổng F01: Tái Tạo & Khóa Vĩnh Viễn 10 Test Suite Regression (G0-003)

Toàn bộ 10 file test regression chuyên sâu của các Đề án 154 đến 166 (trước đây từng bị xóa sau khi chạy) đã được tái tạo hoàn toàn bền vững, bổ sung vào `tests/Feature/` và whitelist trong `.gitignore` để Git theo dõi vĩnh viễn:

1. `tests/Feature/SignatureArchiveSuiteTest.php` (Đề án 154): **6 / 6 tests PASSED** (20 assertions).
2. `tests/Feature/MultilingualStudioSuiteTest.php` (Đề án 156): **4 / 4 tests PASSED** (14 assertions).
3. `tests/Feature/InvitationPersonaStudioSuiteTest.php` (Đề án 158): **4 / 4 tests PASSED** (14 assertions).
4. `tests/Feature/PhotoWishModerationE2ETest.php` (Đề án 159): **3 / 3 tests PASSED** (11 assertions).
5. `tests/Feature/LiveWallMediaSuiteTest.php` (Đề án 160): **3 / 3 tests PASSED** (17 assertions).
6. `tests/Feature/SmartGroupEventScopeSuiteTest.php` (Đề án 161): **4 / 4 tests PASSED** (11 assertions).
7. `tests/Feature/SmartFocalPointSuiteTest.php` (Đề án 163): **5 / 5 tests PASSED** (14 assertions).
8. `tests/Feature/PublishDiffSuiteTest.php` (Đề án 164): **5 / 5 tests PASSED** (17 assertions).
9. `tests/Feature/RsvpChangeHistorySuiteTest.php` (Đề án 165): **4 / 4 tests PASSED** (13 assertions).
10. `tests/Feature/CompanionCardSuiteTest.php` (Đề án 166): **6 / 6 tests PASSED** (18 assertions).

> **Tổng cộng cụm 10 Regression Suite**: **44 / 44 tests PASSED 100%** (149 assertions).  
> **Cam kết bất biến**: Toàn bộ 10 file test đã được đưa vào Git tracking, tuyệt đối **KHÔNG XÓA** trong mọi trường hợp.

---

## 4. Bằng Chứng Thực Tế Full Test Suite (G0-002)

Chạy thực tế qua `php artisan test --compact` trên commit `0dc875aa52812c4c86ac1e7762c8febc3210753d` (sau khi khôi phục bền vững 10 Regression Suites):

```json
{
  "tool": "pest",
  "result": "passed",
  "tests": 1178,
  "passed": 1178,
  "assertions": 6213,
  "duration_ms": 32526
}
```

- **Tổng số tests passed**: **1,178 tests (100% PASSED)**.
- **Tổng số assertions**: **6,213 assertions**.
- **Thời gian thực thi**: **32.526 ms (0.54 phút)**.
- **Exit Code**: **0 (SUCCESS)**.
- **Kết luận đối soát**: Xóa bỏ hoàn toàn số liệu cũ không có căn cứ (1,502 hoặc 1,545 tests trong báo cáo trước); thiết lập mốc đo lường sự thật chính xác tại commit hiện tại là **1,178 tests bền vững**.

---

## 5. Đối Chiếu & Đóng Băng Trạng Thái Các Ma Trận QA Cũ (G0-004 -> G0-010)

Tuân thủ nghiêm ngặt chỉ thị của Master Prompt và Đánh giá Độc lập:

1. **`docs/admin-vnext/MAY_HEN_ADMIN_TEST_CASES.csv` (995 dòng NOT_RUN)**:
   - **Xác nhận**: Giữ nguyên trạng thái `NOT_RUN` / `AUDITED_UNVERIFIED`.
   - **Cấm**: Tuyệt đối không thực hiện sửa hàng loạt (bulk replace) sang `PASS`. Mỗi ca kiểm thử chỉ được cập nhật khi có bằng chứng chạy test và browser tương ứng.
2. **`docs/admin-vnext/MAY_HEN_ADMIN_ATOMIC_FUNCTIONS.csv` (534 chức năng IMPLEMENTED_UNVERIFIED)**:
   - Giữ nguyên trạng thái để kiểm toán đối soát từng controller và quyền thao tác.
3. **`docs/qa/MAY_HEN_QA_TEST_CASES.csv` (5,090 generic PASS)**:
   - Đánh dấu trạng thái `AUDITED_UNVERIFIED` cho những ca chỉ assert HTTP 200 mà thiếu kiểm tra dữ liệu sâu.
4. **`docs/qa/MAY_HEN_VISUAL_SCREEN_MATRIX.csv` (702 visual cases)**:
   - Phân loại rõ: Chờ nghiệm thu thực tế bằng trình duyệt tại Gate G3, không sử dụng nhãn `CODE_VERIFIED_RESPONSIVE` thay thế cho browser screenshots.

---

## 6. Kết Luận Nghiệm Thu Gate G0

- **Mục tiêu G0**: Hạ tầng kiểm thử, môi trường runtime và sự thật mã nguồn đã được xác lập minh bạch 100%.
- **Trạng thái Gate G0**: **PASSED 100%**.
- **Sẵn sàng bước vào**: **GATE G1 — Sửa lỗi P0/P1 về Dữ liệu, Tiền, Quyền, Lịch Cưới**.

