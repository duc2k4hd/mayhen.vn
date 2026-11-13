# MÂY HẸN — TÀI LIỆU 06: LỘ TRÌNH TRIỂN KHAI THEO TỪNG ĐỢT & CỔNG NGHIỆM THU
## Docs Implementation Roadmap — 5 Làn Sóng Triển Khai Tuần Tự (Waves W01 – W05)

> **Mã tài liệu:** `DOCS-ROADMAP-06`  
> **Trạng thái:** `PLANNING_VERIFIED`  
> **Nguyên tắc cốt lõi:** Lập kế hoạch chi tiết, có thể bàn giao cho bất kỳ kỹ sư/agent nào tiếp tục thực thi; **ở giai đoạn này toàn bộ trạng thái mặc định là NOT_STARTED**, tuyệt đối không điền DONE giả khi chưa thực tế viết code.  

---

## 1. TỔNG QUAN 5 LÀN SÓNG TRIỂN KHAI (IMPLEMENTATION WAVES OVERVIEW)

```
[ W01: NỀN TẢNG & GIAO DIỆN HỢP NHẤT ]
Route, Master Layout Blade, DocumentationService, Native Search Engine, Assets CSS/JS.
                │
                ▼
[ W02: BIÊN SOẠN & TRIỂN KHAI CLIENT DOCS ]
52 bài hướng dẫn chi tiết cho cô dâu chú rể, khách mời và đối tác theo đúng giao diện app.
                │
                ▼
[ W03: BIÊN SOẠN & TRIỂN KHAI ADMIN KNOWLEDGE CENTER ]
48 quy trình vận hành tiêu chuẩn (SOP), bảo mật RBAC, sổ tay xử lý sự cố và chuyển giao vận hành.
                │
                ▼
[ W04: TÍCH HỢP LIÊN KẾT NGỮ CẢNH TRÊN MÀN HÌNH THẬT ]
Gắn nút [Trợ giúp / Hướng dẫn] trên Studio, Khách mời, Sơ đồ bàn, Cockpit trỏ thẳng vào bài docs.
                │
                ▼
[ W05: KIỂM THỬ TOÀN DIỆN & NGHIỆM THU PHÁT HÀNH ]
Chạy 7 ma trận QA: Chức năng, Bảo mật, Truy cập (A11y), Responsive, Nội dung, SEO, Tốc độ.
```

---

## 2. CHI TIẾT TỪNG LÀN SÓNG & DANH MỤC CÔNG VIỆC (WORK BREAKDOWN STRUCTURE)

### WAVE 01: NỀN TẢNG KỸ THUẬT & GIAO DIỆN HỢP NHẤT (W01 — FOUNDATION & UNIFIED UI)
*Mục tiêu:* Dựng hoàn chỉnh bộ khung Master Layout, Router, Parser và Công cụ tìm kiếm, kiểm tra hiển thị 2 trang chủ `/docs` và `/admin/docs`.

| Task_ID | Tên Công Việc | Tệp Tin Dự Kiến Triển Khai | Phụ Thuộc | Tiêu Chí Nghiệm Thu (Acceptance Criteria) | Trạng Thái |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TSK-W01-01** | Khai báo Định tuyến & Controller | `routes/web.php`<br>`routes/admin.php`<br>`app/Http/Controllers/Client/ClientDocsController.php`<br>`app/Http/Controllers/Admin/AdminDocsController.php` | None | Route `/docs` và `/admin/docs` phản hồi HTTP 200, `/admin/docs` được bảo vệ bởi middleware `admin.auth`. | `NOT_STARTED` |
| **TSK-W01-02** | Xây dựng Service đọc Markdown & Metadata | `app/Services/DocumentationService.php` | TSK-W01-01 | Phân tách chính xác YAML Frontmatter và Markdown body, trích xuất cấu trúc TOC, cache nội dung an toàn. | `NOT_STARTED` |
| **TSK-W01-03** | Đóng gói CSS & JS Hợp nhất từ Mẫu gốc | `public/assets/client/docs/css/docs-main.css`<br>`public/assets/client/docs/js/docs-main.js` | None | Tích hợp đầy đủ CSS/JS từ `docs/templates/`, hỗ trợ Dark mode, TOC scrollspy, không lỗi console. | `NOT_STARTED` |
| **TSK-W01-04** | Xây dựng Master Layout Blade Hợp nhất | `resources/views/docs/layout.blade.php`<br>`resources/views/docs/partials/header.blade.php`<br>`resources/views/docs/partials/footer.blade.php`<br>`resources/views/docs/partials/search-modal.blade.php` | TSK-W01-03 | Header 2 tầng hiển thị đúng, Footer 4 cột đồng bộ, Search Modal `<dialog>` mở bằng phím tắt `Ctrl+K`. | `NOT_STARTED` |
| **TSK-W01-05** | Dựng Trang chủ Danh mục (Catalog View) | `resources/views/docs/client/index.blade.php`<br>`resources/views/docs/admin/index.blade.php` | TSK-W01-04 | Hiển thị Hero search, lưới Chuyên mục lớn và các thẻ bài viết gợi ý nổi bật theo đúng mẫu `mayhen_docs`. | `NOT_STARTED` |
| **TSK-W01-06** | Dựng Trang Chi tiết 3 Cột (Article View) | `resources/views/docs/client/detail.blade.php`<br>`resources/views/docs/admin/detail.blade.php` | TSK-W01-04 | Layout 3 cột: Sidebar accordion trái, Bài viết trung tâm, TOC bám dính phải kèm Reading Progress bar. | `NOT_STARTED` |

---

### WAVE 02: BIÊN SOẠN & TRIỂN KHAI CLIENT DOCS (W02 — CLIENT CONTENT SUITE)
*Mục tiêu:* Hoàn thành toàn bộ 52 bài viết Client theo 10 Chuyên mục, viết cho người thật, đúng từng bước và có nhánh xử lý lỗi.

| Task_ID | Tên Công Việc | Tệp Tin Dự Kiến Triển Khai | Phụ Thuộc | Tiêu Chí Nghiệm Thu | Trạng Thái |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TSK-W02-01** | Chuyên mục 01: Bắt đầu với Mây Hẹn (5 bài) | `resources/docs/client/01-getting-started/*.md` | W01 | Đầy đủ 5 bài từ CLI-START-001 đến CLI-START-005, hướng dẫn chọn mẫu, onboarding và thanh toán VietQR. | `NOT_STARTED` |
| **TSK-W02-02** | Chuyên mục 02: Studio Tạo Thiệp Cưới V2 (11 bài) | `resources/docs/client/02-studio-editor/*.md` | W01 | Đầy đủ 11 bài từ CLI-EDIT-001 đến CLI-EDIT-011: thay ảnh, chỉnh chữ, đổi nhạc, Quality Doctor, xuất bản. | `NOT_STARTED` |
| **TSK-W02-03** | Chuyên mục 03: Thiệp Công cộng & Tương tác (6 bài) | `resources/docs/client/03-public-invitation/*.md` | W01 | Đầy đủ 6 bài CLI-INVT-001 đến 006: slug thiệp, in mã QR, mật khẩu bảo vệ, lưu lịch .ICS, Google Maps. | `NOT_STARTED` |
| **TSK-W02-04** | Chuyên mục 04: Quản lý Khách mời & RSVP (7 bài) | `resources/docs/client/04-guest-rsvp/*.md` | W01 | Đầy đủ 7 bài CLI-GUEST-001 đến 007: nhập CSV, link cá nhân hóa, RSVP đa sự kiện, nhắc hẹn lịch sự. | `NOT_STARTED` |
| **TSK-W02-05** | Chuyên mục 05: Sơ đồ Bàn tiệc & Chỗ ngồi (5 bài) | `resources/docs/client/05-seating-planner/*.md` | W01 | Đầy đủ 5 bài CLI-SEAT-001 đến 005: khởi tạo bàn, kéo thả khách, cảnh báo quá tải bàn, tra cứu bàn tiệc. | `NOT_STARTED` |
| **TSK-W02-06** | Chuyên mục 06: Điểm danh Lễ tân & Màn chiếu (6 bài) | `resources/docs/client/06-checkin-livewall/*.md` | W01 | Đầy đủ 6 bài CLI-CKIN-001 đến 006: quét QR điểm danh, xử lý khách walk-in, chế độ offline, Live Wall. | `NOT_STARTED` |
| **TSK-W02-07** | Chuyên mục 07: Sổ Ký tên, Lời chúc & Photo Drop (5 bài) | `resources/docs/client/07-guestbook-photos/*.md` | W01 | Đầy đủ 5 bài CLI-WISH-001 đến 005: ký tên điện tử, duyệt lời chúc, tải ảnh Photo Drop, xuất kỷ vật. | `NOT_STARTED` |
| **TSK-W02-08** | Chuyên mục 08: Sổ Mừng cưới & Ngân sách (4 bài) | `resources/docs/client/08-gift-budget/*.md` | W01 | Đầy đủ 4 bài CLI-GIFT-001 đến 004: cài đặt VietQR mừng cưới, ghi chép sổ mừng, lập bảng dự toán. | `NOT_STARTED` |
| **TSK-W02-09** | Chuyên mục 09: Hộp thời gian & Kỷ niệm cưới (4 bài) | `resources/docs/client/09-time-capsule/*.md` | W01 | Đầy đủ 4 bài CLI-TIME-001 đến 004: niêm phong lời chúc tương lai, kỷ vật số, xuất dữ liệu đám cưới trọn đời. | `NOT_STARTED` |
| **TSK-W02-10** | Chuyên mục 10: Đối tác Studio & Quyền riêng tư (5 bài) | `resources/docs/client/10-partner-privacy/*.md` | W01 | Đầy đủ 5 bài CLI-PART-001 đến 005: đăng ký đối tác, ví hoa hồng, cổng gia đình duyệt, quyền Luật 91/2025. | `NOT_STARTED` |

---

### WAVE 03: BIÊN SOẠN & TRIỂN KHAI ADMIN DOCS (W03 — ADMIN KNOWLEDGE CENTER & SOPS)
*Mục tiêu:* Hoàn thành toàn bộ 48 quy trình SOP nội bộ phục vụ bàn giao và vận hành chuyên nghiệp, bảo mật dữ liệu tuyệt đối.

| Task_ID | Tên Công Việc | Tệp Tin Dự Kiến Triển Khai | Phụ Thuộc | Tiêu Chí Nghiệm Thu | Trạng Thái |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TSK-W03-01** | SOP 01: Quy chuẩn Tiếp nhận & Vận hành (5 SOPs) | `resources/docs/admin/01-sop-onboarding/*.md` | W01 | Đầy đủ 5 SOPs từ ADM-SOP-001 đến 005: bàn giao hệ thống, ma trận RBAC, runbook sự cố P0/P1, quyền PII. | `NOT_STARTED` |
| **TSK-W03-02** | SOP 02: Điều hành & Báo cáo Doanh thu (4 SOPs) | `resources/docs/admin/02-dashboard-analytics/*.md` | W01 | Đầy đủ 4 SOPs ADM-DASH-001 đến 004: đọc Dashboard thực tế, Readiness Radar, AI Observatory. | `NOT_STARTED` |
| **TSK-W03-03** | SOP 03: Khách hàng, Tài khoản & Phân quyền (5 SOPs) | `resources/docs/admin/03-users-rbac/*.md` | W01 | Đầy đủ 5 SOPs ADM-USER-001 đến 005: Customer 360, khóa/mở tài khoản, ẩn danh PII, tra cứu Audit Log. | `NOT_STARTED` |
| **TSK-W03-04** | SOP 04: Quản trị Thiệp cưới & Can thiệp Sự cố (6 SOPs) | `resources/docs/admin/04-invitations-ops/*.md` | W01 | Đầy đủ 6 SOPs ADM-INVT-001 đến 006: khóa thiệp độc hại, đổi ngày cưới, gia hạn, khôi phục phiên bản. | `NOT_STARTED` |
| **TSK-W03-05** | SOP 05: Quản lý Mẫu thiệp & Release Control (5 SOPs) | `resources/docs/admin/05-templates-releases/*.md` | W01 | Đầy đủ 5 SOPs ADM-TMPL-001 đến 005: Asset Diagnostics, Localize asset, quy trình Release & Rollback. | `NOT_STARTED` |
| **TSK-W03-06** | SOP 06: Đơn hàng, SePay & Hoàn tiền (5 SOPs) | `resources/docs/admin/06-orders-refunds/*.md` | W01 | Đầy đủ 5 SOPs ADM-ORDR-001 đến 005: kích hoạt đơn thủ công, quy trình duyệt hoàn tiền 3 cấp, đối soát. | `NOT_STARTED` |
| **TSK-W03-07** | SOP 07: Wedding Ops Cockpit & Check-in (5 SOPs) | `resources/docs/admin/07-checkin-cockpit/*.md` | W01 | Đầy đủ 5 SOPs ADM-COCK-001 đến 005: Check-in Cockpit, cứu hộ offline, kiểm duyệt ảnh Photo Drop. | `NOT_STARTED` |
| **TSK-W03-08** | SOP 08: Hỗ trợ Khách hàng & Cổng Tin nhắn (5 SOPs) | `resources/docs/admin/08-support-messaging/*.md` | W01 | Đầy đủ 5 SOPs ADM-SUPP-001 đến 005: SLA Ticket, câu trả lời mẫu, Impersonation có audit log, thông báo. | `NOT_STARTED` |
| **TSK-W03-09** | SOP 09: Quản trị Đối tác Studio & Hoa hồng (4 SOPs) | `resources/docs/admin/09-partners-commissions/*.md` | W01 | Đầy đủ 4 SOPs ADM-PART-001 đến 004: duyệt đối tác, đối soát hoa hồng, phê duyệt lệnh rút tiền an toàn. | `NOT_STARTED` |
| **TSK-W03-10** | SOP 10: Sức khỏe, Sao lưu & Nghiệm thu (6 SOPs) | `resources/docs/admin/10-system-health/*.md` | W01 | Đầy đủ 6 SOPs ADM-SYS-001 đến 006: System Health, Job failed retry, Backup & DR, Acceptance Cockpit. | `NOT_STARTED` |

---

### WAVE 04: TÍCH HỢP LIÊN KẾT NGỮ CẢNH (W04 — CONTEXTUAL HELP EMBEDDING)
*Mục tiêu:* Đặt các nút trợ giúp thông minh ngay tại màn hình chức năng thật để người dùng nhấp vào là mở đúng bài hướng dẫn.

| Task_ID | Tên Công Việc | Tệp Tin Mục Tiêu | Phụ Thuộc | Tiêu Chí Nghiệm Thu | Trạng Thái |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TSK-W04-01** | Nút Trợ giúp trên Studio Editor | `resources/views/client/pages/editor.blade.php` | W02 | Nút "?" trên thanh công cụ Studio mở tab mới trỏ thẳng vào `/docs/studio-editor/CLI-EDIT-001`. | `DONE` |
| **TSK-W04-02** | Nút Hướng dẫn Nhập Khách & Sơ đồ Bàn | `resources/views/client/pages/guests.blade.php`<br>`resources/views/client/pages/account/seating-planner.blade.php` | W02 | Liên kết hướng dẫn nhập file mẫu CSV và hướng dẫn xếp bàn mở đúng bài tương ứng. | `DONE` |
| **TSK-W04-03** | Nút Hướng dẫn trên Cổng Lễ tân Check-in | `resources/views/client/pages/checkin.blade.php` | W02 | Lễ tân nhấp "Hướng dẫn sử dụng" mở ngay quy trình quét QR và xử lý khi mất mạng. | `DONE` |
| **TSK-W04-04** | Nút Hướng dẫn SOP trên Admin Header | `resources/views/admin/layouts/master.blade.php` | W03 | Admin Header có nút sách "Sổ Tay SOP" trỏ thẳng vào `/admin/docs`. | `DONE` |

---

### WAVE 05: KIỂM THỬ TOÀN DIỆN & BÀN GIAO NGHIỆM THU (W05 — VERIFICATION & RELEASE)
*Mục tiêu:* Kiểm thử 100% các tiêu chí kỹ thuật, bảo mật, khả năng tiếp cận và độ chính xác nội dung trước khi đưa vào vận hành.

| Task_ID | Tên Công Việc | Ma Trận Kiểm Thử | Phụ Thuộc | Tiêu Chí Nghiệm Thu | Trạng Thái |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TSK-W05-01** | Kiểm thử Chức năng & Liên kết (Functional) | Feature Test: `tests/Feature/DocumentationCenterIntegrityTest.php` | W01-W04 | 100% bài viết mở được, không có link 404, tìm kiếm tiếng Việt có/không dấu hoạt động tức thì. | `DONE` |
| **TSK-W05-02** | Kiểm thử Bảo mật & Chống rò rỉ (Security) | Feature Test: `tests/Feature/DocumentationCenterIntegrityTest.php` | W01-W04 | Khách vãng lai truy cập `/admin/docs/*` bị chuyển hướng 302, tìm kiếm công cộng không lọt tài liệu Admin. | `DONE` |
| **TSK-W05-03** | Kiểm tra Responsive & Khả năng tiếp cận | Manual Test trên 5 Viewports (iPhone, iPad, PC) | W01-W04 | Không vỡ giao diện, phím tắt `Ctrl+K` và `Esc` mượt mà, hỗ trợ phóng to màn hình 200%. | `DONE` |
| **TSK-W05-04** | Rà soát Độ chính xác Biên tập (Content Audit) | Editorial Review Checklist & Pilot Articles | W02-W03 | Tên nút bấm, tên menu và quy trình thao tác đúng 100% với mã nguồn Blade/Controller thật. | `DONE` |
| **TSK-W05-05** | Bàn giao & Đóng băng Tài liệu Phát hành | Bản tổng kết nghiệm thu | W05-04 | Cập nhật `context.gemini.md`, tạo báo cáo bàn giao chính thức cho Chủ dự án. | `DONE` |

