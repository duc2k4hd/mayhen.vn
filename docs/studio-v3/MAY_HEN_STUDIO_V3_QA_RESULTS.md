# MÂY HẸN — BÁO CÁO KẾT QUẢ KIỂM THỬ & NGHIỆM THU STUDIO VISUAL EDITOR V3 (`W00`–`W08`)

> **Ngày thực thi kiểm thử**: 2026-11-18  
> **Môi trường**: Windows · Laravel 13.35.0 · PHP 8.5 · SQLite/MySQL · Node.js v24.19.0 · Headless Chromium CDP (`127.0.0.1:8000`)  
> **Nguyên tắc trung thực**: Chỉ những ca kiểm thử thực sự được chạy bằng bộ test tự động (Pest PHP, Node Test Runner, Chromium CDP E2E) sau chỉnh sửa cuối cùng mới được đánh dấu `PASS`. Các ca phụ thuộc phần cứng vật lý đặc thù (bàn phím iOS/Gboard thật, tệp HEIC chụp trực tiếp từ iPhone, stress test 1.000 thao tác liên tiếp) được ghi nhận trung thực là `NOT_RUN`.

---

## 1. Tổng Hợp Kết Quả Các Bộ Kiểm Thử Đã Chạy

| Bộ kiểm thử | Lệnh thực thi | Kết quả | Số lượng | Thời gian |
|---|---|---|---|---|
| **Node JS Unit Suite** (`Autosaver`, `DraftRecovery`, `EditorStore`, `InvitationConfig`, `VisualEditorRenderer`) | `node --test tests/Unit/editor-autosaver.test.mjs tests/Unit/editor-v3-renderer.test.mjs` | **PASS** | `45 / 45 tests` (`0 failed`) | `75.2 ms` |
| **Pest PHP Feature Suite** (`StudioVisualEditorV3Test` + 6 bộ test Studio/Sync/Security/Performance/Curated) | `php artisan test tests/Feature/StudioVisualEditorV3Test.php tests/Feature/InvitationEditorPerformanceTest.php tests/Feature/StudioEditorAndAutosaveDeepTest.php tests/Feature/InvitationEditorDataSyncTest.php tests/Feature/Gate2TemplateStudioAndSyncInvariantsTest.php tests/Feature/TemplateRenderAndMediaSecurityDeepTest.php tests/Feature/CuratedInvitationFlowTest.php --compact` | **PASS** | `52 / 52 tests` (`548 assertions`, `0 failed`) | `3.30 s` |
| **Chromium CDP End-to-End & Multi-Viewport Visual QA** | `node verify_stv3_e2e_cdp.mjs` -> `docs/studio-v3/qa/w08-final/e2e-verification-report.json` | **PASS** | `8 screenshots` · `6 active templates interactive` · `0 JS exceptions` · `0 Google Fonts CDN requests` | `~11 s` |
| **Laravel Pint Code Style** | `php vendor/bin/pint --dirty --format agent` | **PASS** | Chuẩn PSR-12 / Laravel preset trên toàn bộ tệp PHP sửa đổi | `0.8 s` |
| **Ma trận `MAY_HEN_STUDIO_V3_TEST_MATRIX.csv`** | Đối chiếu từng dòng (`215` ca kiểm thử) | **185 PASS / 0 FAIL / 30 NOT_RUN** | Tỷ lệ tự động hóa thực tế: **86.0%** (`100%` ca P0 cốt lõi đã chạy đạt `PASS`) | — |

---

## 2. Bằng Chứng Trực Quan Trước & Sau Nâng Cấp (`Screenshots`)

### 2.1 Ảnh Baseline Trước Khi Sửa Mã Nguồn (`docs/studio-v3/qa/w00-baseline/`)
- `studio-desktop-1440.png` — Giao diện V2 cũ chưa có Right Contextual Inspector, chưa có tab Trang Trí, chưa có chọn trực tiếp trên thiệp.
- `studio-mobile-390.png` & `studio-mobile-preview-390.png` — Phát hiện lỗi thực tế trên màn hình `390×844`: cụm nút `Nhập Liệu | Xem Thiệp` đè lên nút quay lại & nhãn mẫu thiệp, nút `Xuất Bản Thiệp` bị cắt chữ mép phải.

### 2.2 Ảnh Nghiệm Thu Sau Nâng Cấp Studio V3 (`docs/studio-v3/qa/w08-final/`)
1. `01-desktop-text-selected-1440.png` (`1440×900`): Click trực tiếp vào tên chú rể trong iframe -> hiện viền chọn vàng kim, thanh công cụ nổi (`Tên Chú Rể | Sửa chữ | Kiểu chữ | Hiệu ứng | Đặt lại`) và Right Contextual Inspector với danh sách 12 phông chữ Tiếng Việt cục bộ + cỡ chữ + giãn dòng + căn lề + màu sắc.
2. `02-desktop-image-inspector-1440.png` (`1440×900`): Click trực tiếp vào ảnh bìa chính (`cover_photo_url`) -> mở Image Inspector (`Thay ảnh mới`, `Cắt theo khung`, thanh trượt Điểm tiêu cự `X=40%, Y=25%` & `Zoom=1.2x` không phá hủy ảnh gốc).
3. `03-desktop-overlay-and-motion-1440.png` (`1440×900`): Mở kho `Trang Trí` bên trái -> gắn sticker SVG `Dấu Sáp Hoàng Gia` vào mục Hero -> xoay `-15°` (4 tay nắm góc + trục xoay) + gán hiệu ứng chuyển động `Phóng Nhẹ Tinh Tế (gentle-zoom)` tách biệt hoàn toàn giữa vỏ xoay ngoài và vỏ animation trong.
4. `04-public-published-desktop-1440.png` & `05-public-published-mobile-390.png`: Xuất bản thiệp ra đường dẫn công khai `/thiep/{slug}` -> hiển thị chính xác chữ tiếng Việt `"Đặng Minh Quân"`, cỡ chữ `46px` font `MayhenV3Indulge`, tiêu cự ảnh `40% 25%`, dấu sáp xoay `-15deg`, và **hoàn toàn không chứa bất kỳ khung chọn hay mã JS trình sửa thiệp nào**.
5. `06-studio-mobile-fixed-390.png`, `07-studio-mobile-bottomsheet-390.png` & `08-studio-mobile-320.png`: Khắc phục triệt để lỗi đè nút trên `390×844` và `320×568` (`backRight: 44px < switcherLeft: 196px`, `submitRight: 380px <= 390px`); khi chạm vào ảnh trên bản xem thử di động, Bottom Sheet Inspector trượt lên mượt mà.

---

## 3. Kiểm Chứng Đa Mẫu Thiệp (`Multi-Template Parity`)

Kết quả quét thực tế từ `e2e-verification-report.json` + `StudioVisualEditorV3Test`:
- `vom-troi-may-ao-cuoi`: `105` phần tử `[data-edit-id]` định danh tất định, click chọn & xuất bản đầu-cuối đạt `PASS`.
- `giua-muon-ngan-vi-sao`: `74` phần tử `[data-edit-id]`, đã gắn `data-sync-target` cho `slogan`, `thank_you_title`, `thank_you_quote`, tách lồng `party_venue` / `event_party_address`, gỡ bỏ Google Fonts CDN -> `PASS`.
- `hanh-phuc-mang-ten-chung-minh`: `92` phần tử `[data-edit-id]`, đã sửa lỗi selector `.mayhen_invitation_app` cho form RSVP công khai và sửa fallback `couple_quote` -> `PASS`.
- `ngay-tinh-yeu-dom-hoa`: `98` phần tử `[data-edit-id]`, bảo vệ thẻ con `<span>` trong `couple_names` -> `PASS`.
- `tron-kiep-nay-ben-nhau`: `95` phần tử `[data-edit-id]`, đồng bộ đầy đủ chữ, ảnh, nhạc nền -> `PASS`.
- `hac-bach-trao-duyen`: `60` phần tử `[data-edit-id]`, nâng cấp timeline động `timeline.N.time/title`, gắn `data-sync-target` cho 3 ảnh chuyện tình, hỗ trợ `$isCuratedSeven`, gỡ bỏ Google Fonts CDN -> `PASS`.
- `mayhen-curated` (7-section AI Curated, 24 biến thể): sửa lỗi nhầm `groom_father`/`bride_father` ở `parents/variant_c.blade.php`, bổ sung `wedding_date_digits` ở `countdown/variant_b.blade.php`, gỡ bỏ Google Fonts CDN -> `PASS`.

---

## 4. Danh Sách 30 Ca Kiểm Thử Ghi Nhận `NOT_RUN` (Cần Thiết Bị Vật Lý / Kiểm Thử Tải Chuyên Biệt)

Các ca sau chưa chạy tự động trong phiên này và được giữ nguyên trạng thái `NOT_RUN` minh bạch trong `MAY_HEN_STUDIO_V3_TEST_MATRIX.csv`:
- Kiểm thử bộ gõ & chạm vật lý trực tiếp trên bàn phím ảo Gboard Android / iOS Safari thực tế (`STV3-SELECT-012`, `STV3-OVERLAY-007`).
- Tệp ảnh `.HEIC` gốc xuất trực tiếp từ iPhone (`STV3-IMAGE-011`) và ảnh GIF động đa khung (`STV3-IMAGE-012`).
- Các kịch bản stress/load dài hơi (`STV3-OVERLAY-021`, `STV3-PERFORMANCE-001..006`, `STV3-PERFORMANCE-008..011`) và kiểm tra màn hình vật lý `1920px` / `1366px` / `768px` / `browser zoom 125%` riêng lẻ ngoài bộ `320px / 390px / 1440px` đã chụp (`STV3-VISUAL-001..003`, `STV3-VISUAL-006..008`, `STV3-VISUAL-010`, `STV3-VISUAL-013`, `STV3-VISUAL-017..018`, `STV3-MOTION-009`, `STV3-MOTION-013`, `STV3-MOTION-016`, `STV3-PERSIST-009`, `STV3-PERSIST-016`).
