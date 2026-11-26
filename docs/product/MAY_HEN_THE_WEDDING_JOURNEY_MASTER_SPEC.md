# MÂY HẸN – THE WEDDING JOURNEY (HÀNH TRÌNH HÔN LỄ) — MASTER SPEC (ĐỀ ÁN #167)

## 1. Tổng quan kiến trúc
Mây Hẹn – The Wedding Journey tách bạch 2 không gian rõ ràng về SEO Intent & quyền riêng tư:
- **A. Công cụ Xem Ngày Cưới Công Khai (`/xem-ngay-cuoi` + 5 trang chuyên đề)**: `ngay-an-hoi`, `ngay-dam-ngo`, `gio-don-dau`, `thang-dai-loi`, `kim-lau`. Miễn phí 100%, không cần đăng nhập.
- **B. Không Gian Tổ Chức Đám Cưới (`/to-chuc-dam-cuoi`, `/to-chuc-dam-cuoi/nghi-le`, `/to-chuc-dam-cuoi/ho-so`, `/to-chuc-dam-cuoi/du-an/{slug}`)**: Công cụ tính ngân sách nhanh, kịch bản MC/phát biểu/vows miễn phí, và Personal Wedding Workspace riêng tư (`auth`, `403` isolation, `noindex`).

## 2. Bộ Engine xác định (`app/Services/Wedding/`)
1. **`WeddingDateEngine`**: Tái sử dụng `VietnameseCalendarEngine` (UTC+7), `CalendarAstronomyEngine`, `TraditionalAlmanacEngine`, `MarriageCompatibilityEngine`. Hỗ trợ 8 hồ sơ nghi lễ cưới hỏi, Hard Constraints (`weekends_only`, `weekdays_only`, `excluded_dates`, `min_prep_days`), Kim Lâu toggle, Hành Giá Đại Lợi Nguyệt (tùy chọn có ghi nguồn & dị bản), so sánh song song 2–5 ngày ứng viên, không chấm điểm % may mắn giả.
2. **`WeddingChecklistService`**: Sinh checklist 3 miền (Bắc/Trung/Nam) & 3 phong cách (Nhà hàng/Ngoài trời/Tối giản), co giãn deadline không bao giờ ở quá khứ, tích hợp DFS Dependency Cycle Detector.
3. **`WeddingBudgetEngine`**: 100% số nguyên VND (`int`), tách bạch `budgeted_vnd`, `estimated_vnd`, `committed_vnd`, `deposit_vnd`, `paid_vnd`, `outstanding_vnd`, `refunded_vnd`. Bất biến kế toán: tiền cọc nằm trong `paid_vnd`, KHÔNG BAO GIỜ cộng trùng vào `committed_vnd`, `outstanding_vnd = committed_vnd > 0 ? max(0, committed_vnd - paid_vnd) : 0`. Hỗ trợ xuất CSV UTF-8 BOM.
4. **`WeddingSeatingAndTimelineEngine`**: Kiểm tra sơ đồ bàn tiệc 2D (vượt biên, chồng lấn, vượt sức chứa, trùng khách ngồi 2 bàn) và kiểm tra xung đột lịch trình ngày cưới (thời lượng âm, trùng người phụ trách, thiếu đệm di chuyển < 15 phút).
5. **`WeddingAiConciergeService`**: Grounded trên dữ liệu thực, Proposal-with-Confirmation (không tự sửa DB), kiểm duyệt an toàn qua `BaziSafetyGuardService`, quản trị qua `AiGovernanceService` & `PlanAccessService` + Cache anti-bypass.

## 3. Cơ sở dữ liệu
Migration `2026_11_26_143000_create_wedding_journey_tables.php` (InnoDB, `unsignedBigInteger('user_id')->index()`, không FK sang `users`):
- `wedding_projects`, `wedding_tasks`, `wedding_expenses`, `wedding_timeline_items`, `wedding_journey_vendors`, `wedding_scripts`.

## 4. Kiểm thử xác minh
- `tests/Unit/WeddingJourneyEngineTest.php` & `tests/Feature/WeddingJourneyFeatureTest.php`: **7/7 PASS (95 assertions)**.
- Laravel Pint: **PASSED**.
- Playwright E2E (Desktop 1440×900 & Mobile 390×844): **200 OK, 0 `pageerror`, 0px horizontal overflow**.
