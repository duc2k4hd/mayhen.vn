---
id: ADM-SYS-004
title: "Bảng điều khiển Nghiệm thu Thương mại (Admin Acceptance Audit Cockpit): Chấm điểm tuân thủ"
category: "Hệ thống, Sức khỏe, Sao lưu & Nghiệm thu"
summary: "Hướng dẫn vận hành Trạm chỉ huy kiểm định chất lượng toàn diện (Acceptance Audit Cockpit), cách thức chấm điểm tuân thủ 5 nhóm tiêu chí kỹ thuật và quy trình xuất báo cáo nghiệm thu phục vụ kiểm toán."
reader: "PER-ADM-03 (Quản trị viên Cấp cao)"
order: 4
updated_at: "2026-11-15"
tags: [acceptance, audit-cockpit, compliance, scoring, prd-v3, csv-export]
mapped_page: "ADM-SYS-ACCEPTANCE"
mapped_route: "admin.system.acceptance.index"
risk_level: "CRITICAL"
required_role: "admin.super"
---

# Bảng điều khiển Nghiệm thu Thương mại (Admin Acceptance Audit Cockpit): Chấm điểm tuân thủ

> [!WARNING]
> **Phân quyền thực hiện:** Chỉ dành riêng cho Quản trị viên Cấp cao (`admin.super`).  
> **Route quản trị:** `GET /admin/system/acceptance` (`admin.system.acceptance.index`).  
> **Căn cứ pháp lý & kỹ thuật:** Mục 62 & 63 PRD V3 và Bộ Tiêu Chuẩn Cổng Kiểm Định Thương Mại Mây Hẹn (Release Candidate Test Gates).


![Giao diện quản lý Bảng điều khiển Nghiệm thu Thương mại (Admin Acceptance Audit Cockpit): Chấm điểm tuân thủ](/assets/client/docs/images/admin/10-system-health/adm-sys-004-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích Nghiệp vụ & Ý nghĩa Trạm Chỉ Huy Kiểm Định

Trước khi bàn giao phiên bản mới hoặc công bố chiến dịch thương mại lớn, toàn bộ hệ sinh thái Mây Hẹn phải được đánh giá khách quan về chất lượng kỹ thuật, tính toàn vẹn tài chính và mức độ tuân thủ bảo mật. 

**Trạm Chỉ Huy Kiểm Định Chất Lượng Toàn Diện (Acceptance Audit Cockpit)** tại `/admin/system/acceptance` là công cụ độc lập giúp Quản trị viên Cấp cao:
1. Quét tự động toàn bộ 5 nhóm tiêu chí nghiệp vụ sống còn của nền tảng.
2. Chấm điểm tuân thủ tổng thể (**Compliance Score %**) và xếp hạng chất lượng phiên bản (**Grade: A+, A, B, C**).
3. Phát hiện sớm các lỗ hổng kỹ thuật (mẫu thiệp thiếu 10 section, webhook thiếu kiểm tra chữ ký số, tài nguyên gọi ra server CDN bên ngoài).
4. Xuất Báo cáo Nghiệm thu Kỹ thuật định dạng CSV UTF-8 BOM có chữ ký kiểm toán làm cơ sở ký kết biên bản nghiệm thu thương mại.

---

## 2. Thang Điểm Tuân Thủ & Xếp Hạng Chất Lượng

Hệ thống tính toán Điểm Tuân Thủ theo công thức toán học chuẩn:
$$\text{Compliance Score (\%)} = \left(\frac{\text{Số tiêu chí ĐẠT (Passed)}}{\text{Tổng số tiêu chí kiểm tra}}\right) \times 100$$

```
┌─────────────────┬──────────────────────┬─────────────────────────────────────────────────────────────┐
│  Điểm Tuân Thủ  │  Xếp Hạng (Grade)    │  Trạng Thái Đủ Điều Kiện Vận Hành (Release Readiness)       │
├─────────────────┼──────────────────────┼─────────────────────────────────────────────────────────────┤
│   95.0% - 100%  │  HẠNG XUẤT SẮC (A+)  │  Đủ điều kiện thương mại hóa và chịu tải cao điểm 24/7.    │
│   90.0% - 94.9% │  HẠNG TỐT (A)        │  Hoạt động ổn định, còn một số cảnh báo hiệu năng nhẹ.     │
│   80.0% - 89.9% │  HẠNG ĐẠT (B)        │  Chỉ phát hành thử nghiệm nội bộ, chưa được mở rộng.       │
│   Dưới 80.0%    │  KHÔNG ĐẠT (C)       │  CẤM PHÁT HÀNH. Bắt buộc kích hoạt quy trình khắc phục.    │
└─────────────────┴──────────────────────┴─────────────────────────────────────────────────────────────┘
```

---

## 3. Ma Trận 5 Nhóm Tiêu Chí Kiểm Tra Cốt Lõi

`AdminAcceptanceAuditService` tự động đánh giá qua 5 nhóm tiêu chuẩn:

### Nhóm 1: Bảo Mật & Phân Quyền (Auth & Security)
- **AUTH-01:** Cơ chế xác thực và mã hóa mật khẩu an toàn (Bcrypt / Argon2id, không lưu plain-text).
- **AUTH-02:** Ma trận phân quyền vai trò (Action-Level RBAC: Super Admin, Manager, Staff, Customer) được thực thi nghiêm ngặt tại Controller & Policy.
- **SEC-01:** Bảo vệ quyền riêng tư PII & Khử khóa nhạy cảm trong Audit Logs (tự động gán `***REDACTED***` cho 9 trường: password, secret, token, api_key, card_number...).

### Nhóm 2: Kho Mẫu Thiệp (Templates & Assets)
- **TMP-01:** Khung 10 Section Tiêu Chuẩn & Hệ thống class chuẩn toàn cục `mayhen_invitation_*` kèm hợp đồng live-sync `data-sync-target`.
- **TMP-02:** Tự chứa 100% tài nguyên cục bộ (Zero External CDN / Host). Cấm tuyệt đối tải font/ảnh từ máy chủ bên ngoài.
- **TMP-03:** Cơ chế phòng vệ nhúng Studio Editor (`$isEmbed = true`): Tự mở sẵn cánh thiệp, ẩn màn mở đầu, chống màn hình trắng trơn.

### Nhóm 3: Thanh Toán, SePay & Toàn Vẹn Tài Chính (Fintech & Orders)
- **FIN-01:** Xác minh chữ ký số Webhook SePay (HMAC-SHA256, dung sai thời gian 300s, phòng chống tấn công lặp lại Replay Attack).
- **FIN-02:** Quy trình xét duyệt hoàn tiền 3 cấp (Maker-Checker Approval Workflow) kèm đối soát ngân hàng.
- **FIN-03:** Tính toàn vẹn ví hoa hồng đối tác Studio, đảm bảo khóa dòng `lockForUpdate()` khi rút tiền hoa hồng.

### Nhóm 4: Hiệu Năng & Tối Ưu Hóa Giao Diện (Performance & UX)
- **PERF-01:** Tối ưu hóa định dạng hình ảnh chuẩn WebP thế hệ mới và nén giảm dung lượng tải trang.
- **PERF-02:** Tuân thủ tiêu chuẩn Core Web Vitals (LCP < 2.5s, CLS < 0.1, FID < 100ms) trên thiết bị di động.

### Nhóm 5: Khả Năng Khôi Phục & Độ Bền Vận Hành (Resilience & Operations)
- **OPS-01:** Tính sẵn sàng sao lưu và kiểm thử phục hồi độc lập (OPS-01 Verification Gate với SHA-256 Checksum trùng khớp 100%).
- **OPS-02:** Bảng đo kiểm sức khỏe hệ thống (System Health Check: DB latency, Cache latency, Disk space < 80%).
- **OPS-03:** Giám sát hàng đợi tác vụ nền (Job Center: Tự động che giấu dấu vết lỗi và hỗ trợ thử lại an toàn).

---

## 4. Hướng Dẫn Thao Tác Trạm Chỉ Huy Kiểm Định

### Kích hoạt Chạy Kiểm Tra Toàn Diện (`triggerAudit`)
1. Truy cập URL `/admin/system/acceptance`.
2. Quan sát bảng tóm tắt: Tổng số bài kiểm tra, Số bài ĐẠT (Xanh lá), Số CẢNH BÁO (Vàng), Số LỖI NGUY CẤP (Đỏ).
3. Nhấp nút **[Kích hoạt kiểm định toàn diện]** (`POST /admin/system/acceptance/run`).
4. Hệ thống thực thi tuần tự các truy vấn và phép thử độc lập trong 1-3 giây.
5. Khi hoàn tất, trang tải lại hiển thị điểm số mới nhất và hệ thống ghi nhận nhật ký kiểm toán `SYSTEM_ACCEPTANCE_AUDIT_RUN` lưu vết User ID, Điểm số và Xếp hạng.

### Xuất Báo Cáo Kiểm Định CSV UTF-8 BOM (`exportCsv`)
1. Nhấp nút **[Xuất báo cáo CSV]** (`GET /admin/system/acceptance/export`).
2. Trình duyệt tự động tải xuống tệp có tên dạng: `mayhen-bao-cao-kiem-dinh-nghiem-thu-YYYYMMDD-His.csv`.
3. Tệp CSV được nhúng sẵn tiền tố UTF-8 BOM (`\xEF\xBB\xBF`), cho phép mở trực tiếp bằng Microsoft Excel hiển thị trọn vẹn tiếng Việt không lỗi font.
4. Báo cáo bao gồm: Mã tiêu chí, Phân nhóm, Tên bài kiểm tra, Trạng thái (PASSED/WARNING/CRITICAL), Chỉ số đo lường thực tế và Diễn giải chi tiết.

---

## 5. Quy Trình Xử Lý Khi Có Tiêu Chí "CRITICAL"

Nếu xuất hiện bất kỳ tiêu chí nào rơi vào trạng thái `CRITICAL` (Màu đỏ):
1. **DỪNG NGAY** mọi kế hoạch phát hành hoặc mở bán tính năng mới.
2. Nhấp vào dòng tiêu chí bị lỗi để đọc phần *Chi tiết lỗi (Detail)*.
3. Tạo Ticket khẩn cấp trên hệ thống quản lý sự cố và gắn nhãn P0 (Blocker).
4. Phối hợp với bộ phận liên quan (Kỹ thuật / Sản phẩm) để khắc phục triệt để.
5. Sau khi sửa lỗi, quay lại trang `/admin/system/acceptance` và nhấp **[Kích hoạt kiểm định toàn diện]** để xác nhận chỉ số đã chuyển sang `PASSED`.

---

## 6. Những điều Tuyệt đối Bị cấm

1. **CẤM** ký biên bản nghiệm thu kỹ thuật bàn giao phiên bản khi Điểm Tuân Thủ dưới 90% hoặc còn tồn tại lỗi `CRITICAL`.
2. **CẤM** can thiệp chỉnh sửa logic tính điểm trong file dịch vụ `AdminAcceptanceAuditService` nhằm làm đẹp số liệu báo cáo ảo.
3. **CẤM** chia sẻ báo cáo kiểm định CSV ra bên ngoài khi chưa có sự chấp thuận của Ban Giám đốc Mây Hẹn.

---

## 7. Bài viết Liên quan

- [ADM-SYS-001: Kiểm tra bảng sức khỏe hệ thống (System Health Check)](/admin/docs/system-health/ADM-SYS-001)
- [ADM-SYS-003: Quy trình sao lưu toàn diện và diễn tập khôi phục thảm họa](/admin/docs/system-health/ADM-SYS-003)
- [ADM-SYS-005: Cấu hình nền tảng và quản lý Cờ tính năng (Feature Flags)](/admin/docs/system-health/ADM-SYS-005)
