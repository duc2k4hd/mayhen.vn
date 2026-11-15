---
id: ADM-SYS-005
title: "Cấu hình nền tảng (Platform Settings), Bật/Tắt Cờ tính năng (Feature Flags) cô lập"
category: "Hệ thống, Sức khỏe, Sao lưu & Nghiệm thu"
summary: "Hướng dẫn quản trị viên cấp cao cấu hình tham số nền tảng, quản lý vòng đời Cờ tính năng (Feature Flags), phát hành theo tỷ lệ (Canary Rollout) và kích hoạt Cầu dao ngắt khẩn cấp (Emergency Kill Switch)."
reader: "PER-ADM-03 (Quản trị viên Cấp cao)"
order: 5
updated_at: "2026-11-15"
tags: [feature-flags, platform-settings, canary-rollout, kill-switch, release-control]
mapped_page: "ADM-SYS-FLAGS"
mapped_route: "admin.settings.flags.index"
risk_level: "CRITICAL"
required_role: "admin.super"
---

# Cấu hình nền tảng (Platform Settings), Bật/Tắt Cờ tính năng (Feature Flags) cô lập

> [!WARNING]
> **Phân quyền thực hiện:** Chỉ dành riêng cho Quản trị viên Cấp cao (`admin.super`).  
> **Route quản trị:** `GET /admin/settings/flags` (`admin.settings.flags.index`).  
> **Tác động:** Thay đổi trạng thái Cờ tính năng có hiệu lực ngay lập tức (Real-time) trên toàn bộ người dùng website mà không cần khởi động lại máy chủ.

---

## 1. Mục đích Nghiệp vụ & Triết lý Phát hành An toàn

Mây Hẹn áp dụng kiến trúc **Tách rời việc Triển khai mã nguồn (Deploy) khỏi việc Bật tính năng cho người dùng (Release)**. Toàn bộ các chức năng mới (tạo thiệp tự động bằng AI, sơ đồ bàn tiệc nâng cao, mã VietQR động, cổng Zalo ZNS) đều được bao bọc bởi một **Cờ Tính Năng (Feature Flag)** độc lập.

Lợi ích vận hành:
1. **Phát hành theo tỷ lệ (Canary Rollout):** Cho phép thử nghiệm 10% khách hàng hoặc giới hạn cho tài khoản Studio thân thiết trước khi mở rộng 100%.
2. **Cầu dao ngắt khẩn cấp (Emergency Kill Switch):** Nếu dịch vụ AI bên ngoài bị lỗi hoặc tăng chi phí đột biến, quản trị viên chỉ mất đúng 1 click chuột để ngắt tính năng ngay lập tức, bảo vệ hệ thống không bị sập.
3. **Không cần Rollback Code:** Giảm thiểu tối đa rủi ro phải revert commit và deploy lại toàn bộ ứng dụng trong lúc cao điểm mùa cưới.

---

## 2. Các Chỉ Số Giám Sát Cờ Tính Năng (Flags Metrics)

Bảng điều khiển `/admin/settings/flags` tổng hợp 5 chỉ số thời gian thực:
- **Tổng số cờ (Total Flags):** Tổng số cờ tính năng đã đăng ký trong bảng `system_feature_flags`.
- **Đang hoạt động (Active 100%):** Các tính năng đã phát hành rộng rãi cho toàn bộ người dùng.
- **Phát hành thử nghiệm (Beta / Rollout):** Các cờ đang bật ở mức tỷ lệ `< 100%` hoặc giới hạn theo nhóm vai trò cụ thể.
- **Đang tắt (Disabled):** Tính năng đang tạm ngưng phục vụ hoặc trong giai đoạn phát triển nội bộ.
- **Cầu dao ngắt khẩn cấp (Kill Switched):** Các tính năng bị cưỡng chế ngắt khẩn cấp do sự cố kỹ thuật.

---

## 3. Phân Loại 5 Nhóm Danh Mục Cờ (Categories)

Mỗi cờ tính năng bắt buộc thuộc về một trong 5 nhóm phân loại:
1. `core` (Cốt lõi): Bộ công cụ tạo thiệp, album ảnh cưới, timeline tiệc cưới, sổ ký tên online.
2. `ai` (Trí tuệ nhân tạo): Tạo văn thơ thiệp cưới tự động, gợi ý câu chúc theo ngữ cảnh (`ai_story_generator`).
3. `commerce` (Thương mại & Thanh toán): Mã VietQR động theo đơn hàng (`vietqr_dynamic`), nâng cấp gói dịch vụ tức thì.
4. `collaboration` (Hợp tác & Đối tác): Quản lý nhiều tài khoản phụ cho Studio ảnh viện, bảng phân quyền cộng tác viên.
5. `safety` (An toàn & Phòng hộ): Chế độ bảo vệ khi lưu lượng truy cập cao đột biến (`high_concurrency_safe_mode`).

---

## 4. Hướng Dẫn Vận Hành Cờ Tính Năng

### A. Bật / Tắt Nhanh Trạng Thái Cờ (`toggle`)
- Trên bảng danh sách cờ, tìm tính năng tương ứng.
- Gạt thanh trượt **[Bật / Tắt]** (`POST /admin/settings/flags/{key}/toggle`).
- Hệ thống xóa sạch cache đệm `system_feature_flags` và cập nhật tức thì. Ghi nhận nhật ký kiểm toán `FEATURE_FLAG_TOGGLED` lưu vết người thay đổi.

### B. Cấu Hình Tỷ Lệ Phát Hành Dần (Canary Rollout)
Trước khi phát hành 100%, quản trị viên thực hiện lộ trình bậc thang an toàn:
1. Nhấp nút **[Cấu hình Rollout]** (`PUT /admin/settings/flags/{key}/rollout`).
2. Thiết lập tỷ lệ phần trăm người dùng tiếp cận:
   - **Giai đoạn 1 (Nội bộ):** 0% kèm nhóm vai trò `admin`.
   - **Giai đoạn 2 (Studio đối tác):** 10% kèm nhóm vai trò `partner_studio`.
   - **Giai đoạn 3 (Công chúng mở rộng):** Nâng dần lên 25% -> 50% -> 100%.
3. Nhấp **[Lưu cấu hình]**. Hệ thống sử dụng thuật toán băm xác định nhất quán (Deterministic Hashing) theo `user_id` để đảm bảo một khách hàng khi đã thấy tính năng mới thì sẽ thấy liên tục, không bị chập chờn biến mất giữa chừng.

### C. Kích Hoạt Cầu Dao Ngắt Khẩn Cấp (Emergency Kill Switch)
Khi phát hiện tính năng gây lỗi nghiêm trọng trên Production:
1. Nhấp nút màu đỏ **[Kích hoạt Cầu dao Khẩn cấp (Kill Switch)]** (`POST /admin/settings/flags/{key}/kill-switch`).
2. Điền bắt buộc **Lý do ngắt khẩn cấp** vào hộp thoại (Ví dụ: *"API nhà cung cấp AI trả về HTTP 500 hàng loạt gây treo trang tạo thiệp"*).
3. Nhấp **[Xác nhận Ngắt ngay]**.
4. Trạng thái cờ chuyển sang màu đỏ sẫm `KILL SWITCH ACTIVE`. Tính năng bị vô hiệu hóa 100% đối với mọi người dùng (kể cả khi cờ đang bật `is_enabled = true`). Giao diện người dùng tự động ẩn module này hoặc chuyển sang phương án dự phòng (Fallback Graceful UI).

---

## 5. Tích Hợp Cờ Tính Năng Trong Mã Nguồn

Lập trình viên và quản trị viên có thể kiểm tra cờ tính năng thông qua Service hoặc Blade Directive:

```php
// Kiểm tra trong Controller hoặc Service:
if (app(FeatureFlagService::class)->isEnabled('ai_story_generator', auth()->user())) {
    // Thực thi logic AI
}
```

```html
{{-- Kiểm tra trong giao diện Blade: --}}
@feature('vietqr_dynamic')
    <div class="vietqr-dynamic-payment-card">...</div>
@else
    <div class="vietqr-static-bank-transfer">...</div>
@endfeature
```

---

## 6. Những điều Tuyệt đối Bị cấm

1. **CẤM** bật 100% các tính năng thuộc nhóm `ai` hoặc `commerce` trên môi trường Production khi chưa hoàn tất kiểm thử tải (Load Test) tại Staging.
2. **CẤM** kích hoạt Cầu dao Ngắt khẩn cấp (Kill Switch) mà không ghi rõ lý do chi tiết và không thông báo cho Trưởng bộ phận Kỹ thuật.
3. **CẤM** xóa bỏ một cờ tính năng (`destroy`) khi mã nguồn Blade hoặc Controller vẫn còn đang gọi hàm kiểm tra cờ đó. Phải dọn sạch mã nguồn (Clean Code Debt) trước khi xóa cờ.

---

## 7. Bài viết Liên quan

- [ADM-SYS-001: Kiểm tra bảng sức khỏe hệ thống (System Health Check)](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/10-system-health/ADM-SYS-001.md)
- [ADM-SYS-004: Bảng điều khiển Nghiệm thu Thương mại (Acceptance Audit Cockpit)](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/10-system-health/ADM-SYS-004.md)
- [ADM-SYS-006: Quản lý tên miền tùy chỉnh (Custom Domains) và bản ghi DNS SSL](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/10-system-health/ADM-SYS-006.md)
