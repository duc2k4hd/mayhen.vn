# MÂY HẸN — TÀI LIỆU 01: SITEMAP TOÀN DIỆN & HÀNH TRÌNH ĐỘC GIẢ
## Docs Sitemap & Reader Journeys — Cấu trúc Cây Hai Trung tâm Hướng dẫn

> **Mã tài liệu:** `DOCS-PLAN-01`  
> **Trạng thái:** `PLANNING_VERIFIED`  
> **Ánh xạ hệ thống:** Kế thừa trực tiếp từ `MAY_HEN_COMPLETE_SYSTEM_MAP.xlsx` (Sheet Admin & Sheet Client)  

---

## 1. MÔ HÌNH NHÂN VẬT & ĐỘC GIẢ MỤC TIÊU (PERSONAS & AUDIENCE MATRIX)

Để tài liệu thực sự "viết cho người thật đọc hiểu và tự làm được", hệ thống phân định rõ 7 nhóm độc giả cốt lõi:

| Persona ID | Tên Nhóm Độc Giả | Không gian Truy cập | Nhu cầu Thực tế & Mục tiêu Hành động |
| :--- | :--- | :--- | :--- |
| **PER-CLI-01** | **Cô dâu & Chú rể mới** | `/docs/` | Chưa từng làm thiệp cưới online; muốn tự tay chọn mẫu, thay ảnh, chỉnh sửa lời mời, chọn nhạc, thử nghiệm trước khi mua gói. |
| **PER-CLI-02** | **Cặp đôi đang quản lý tiệc** | `/docs/` | Đã xuất bản thiệp; cần nhập danh sách khách mời, gửi link thiệp cá nhân qua Zalo/Facebook, theo dõi ai đi ai vắng, chia bàn tiệc, quản lý mừng cưới VietQR. |
| **PER-CLI-03** | **Khách mời nhận thiệp** | `/docs/` | Người thân, bạn bè nhận link thiệp; cần mở thiệp, xác nhận tham dự (RSVP), gửi lời chúc, xem bản đồ đường đi, tải lịch cưới, tìm vị trí bàn tiệc ngày cưới. |
| **PER-CLI-04** | **Studio cưới & Đối tác** | `/docs/` | Đơn vị chụp ảnh cưới, wedding planner; muốn đăng ký đối tác, tạo thiệp cho khách của studio, quản lý chiết khấu hoa hồng và rút tiền. |
| **PER-ADM-01** | **Nhân viên CSKH & Tiếp nhận** | `/admin/docs/` | Nhân viên mới vào làm; cần tra cứu thông tin khách hàng, hỗ trợ đổi ảnh lỗi, xử lý khiếu nại, trả lời ticket, hướng dẫn khách dùng tính năng. |
| **PER-ADM-02** | **Kế toán & Vận hành Tài chính** | `/admin/docs/` | Phụ trách đối soát giao dịch SePay/VietQR, xử lý yêu cầu hoàn tiền (Refund), xuất hóa đơn VAT, đối soát hoa hồng đối tác. |
| **PER-ADM-03** | **Quản lý & Giám đốc Hệ thống** | `/admin/docs/` | Quản lý kiểm soát mẫu thiệp, phát hành bản cập nhật (Release & Rollback), phân quyền nhân sự (RBAC), sao lưu dữ liệu, điều tra sự cố bảo mật (P0/P1 SOP). |

---

## 2. SITEMAP TRUNG TÂM HƯỚNG DẪN KHÁCH HÀNG (`/docs/`)

Trung tâm `/docs/` được cấu trúc thành **10 Chuyên mục Lớn** với **52 bài hướng dẫn chi tiết**, bao phủ toàn diện 100% chức năng Client:

```
/docs/
├── 01. BẮT ĐẦU VỚI MÂY HẸN (GETTING STARTED)
│   ├── CLI-START-001: Mây Hẹn là gì? Hành trình từ tạo thiệp đến ngày cưới trọn vẹn
│   ├── CLI-START-002: Hướng dẫn đăng ký tài khoản và hoàn tất khảo sát phong cách cưới (Onboarding)
│   ├── CLI-START-003: Cách xem thử mẫu thiệp và trải nghiệm các tính năng tương tác miễn phí
│   ├── CLI-START-004: So sánh chi tiết các gói dịch vụ (Free, Silver, Gold, Diamond) và cách chọn gói phù hợp
│   └── CLI-START-005: Quy trình thanh toán chuyển khoản VietQR tự động qua SePay an toàn
│
├── 02. STUDIO TẠO THIỆP CƯỚI TRỰC QUAN V2 (STUDIO EDITOR)
│   ├── CLI-EDIT-001: Làm quen giao diện Studio: Khung soạn thảo, thanh công cụ và màn hình xem trước
│   ├── CLI-EDIT-002: Cách thay đổi ảnh bìa, ảnh cưới chính và chọn tiêu điểm khuôn mặt (Smart Focal Point)
│   ├── CLI-EDIT-003: Chỉnh sửa thông tin cô dâu chú rể, phụ mẫu hai bên và ngày giờ hôn lễ
│   ├── CLI-EDIT-004: Tùy chỉnh màu sắc, phông chữ và phong cách chữ ký lãng mạn
│   ├── CLI-EDIT-005: Sắp xếp, ẩn/hiện và tùy biến thứ tự 10 Section tiêu chuẩn của thiệp cưới
│   ├── CLI-EDIT-006: Thêm nhạc nền lãng mạn và cấu hình tự động phát nhạc chuẩn quy định trình duyệt
│   ├── CLI-EDIT-007: Trợ lý AI Copywriter: Gợi ý lời mời cưới và câu chuyện tình yêu xúc động
│   ├── CLI-EDIT-008: Cơ chế tự động lưu nháp, hoàn tác (Undo/Redo) và khôi phục khi mất mạng
│   ├── CLI-EDIT-009: Bác sĩ Kiểm tra Chất lượng (Quality Doctor): Cách khắc phục ảnh mờ, thiếu thông tin trước khi xuất bản
│   ├── CLI-EDIT-010: So sánh các thay đổi (Publish Diff) và xuất bản thiệp cưới chính thức
│   └── CLI-EDIT-011: Chuyển đổi sang mẫu thiệp khác mà không bị mất dữ liệu đã nhập
│
├── 03. THIỆP CƯỚI CÔNG CỘNG & TƯƠNG TÁC (PUBLIC INVITATION)
│   ├── CLI-INVT-001: Cách đặt tên đường dẫn thiệp đẹp (Custom Slug) và chia sẻ lên Zalo/Facebook
│   ├── CLI-INVT-002: Tạo mã QR thiệp cưới để in lên thiệp giấy hoặc thiệp kèm (Companion Card)
│   ├── CLI-INVT-003: Cài đặt mật khẩu bảo vệ thiệp cưới riêng tư và cách người thân mở khóa
│   ├── CLI-INVT-004: Hướng dẫn khách lưu lịch ngày cưới vào điện thoại (.ICS / Google Calendar)
│   ├── CLI-INVT-005: Cài đặt chỉ đường Google Maps chính xác đến sảnh tiệc nhà trai, nhà gái
│   └── CLI-INVT-006: Hướng dẫn tạo thiệp đa ngôn ngữ (Tiếng Việt, Tiếng Anh, Song ngữ)
│
├── 04. QUẢN LÝ KHÁCH MỜI & XÁC NHẬN THAM DỰ (GUEST & RSVP)
│   ├── CLI-GUEST-001: Tạo và quản lý danh sách khách mời theo nhóm (Bạn cô dâu, Bạn chú rể, Họ hàng, Đồng nghiệp)
│   ├── CLI-GUEST-002: Hướng dẫn tải tệp mẫu Excel/CSV và nhập hàng loạt 500+ khách mời trong 1 phút
│   ├── CLI-GUEST-003: Cách tạo link thiệp cá nhân hóa hiển thị đúng danh xưng từng vị khách
│   ├── CLI-GUEST-004: Hướng dẫn khách xác nhận tham dự (RSVP) đa sự kiện (Lễ thành hôn & Tiệc mừng)
│   ├── CLI-GUEST-005: RSVP theo hộ gia đình: Đăng ký số lượng người đi kèm và món ăn kiêng/dị ứng
│   ├── CLI-GUEST-006: Theo dõi sĩ số khách phản hồi thời gian thực và quản lý lịch sử thay đổi RSVP
│   └── CLI-GUEST-007: Thiết lập kế hoạch nhắc hẹn lịch sự (Gentle Reminder) gửi trước ngày cưới
│
├── 05. SƠ ĐỒ BÀN TIỆC & ĐIỀU PHỐI CHỖ NGỒI (SEATING PLANNER)
│   ├── CLI-SEAT-001: Khởi tạo sơ đồ bàn tiệc: Đặt tên bàn, chọn sức chứa (10 hoặc 12 người)
│   ├── CLI-SEAT-002: Thao tác kéo thả khách vào bàn tiệc trực quan trên máy tính và iPad
│   ├── CLI-SEAT-003: Phân chia khu vực bàn tiệc theo sự kiện (Lễ Vu Quy nhà gái riêng, Lễ Thành Hôn nhà trai riêng)
│   ├── CLI-SEAT-004: Cảnh báo quá tải bàn tiệc (Capacity Guardian) và cách xếp bàn dự phòng
│   └── CLI-SEAT-005: Tính năng Tra cứu bàn tiệc thông minh (Table Finder) dành cho khách khi đến sảnh
│
├── 06. ĐIỂM DANH LỄ TÂN & MÀN CHIẾU SÂN KHẤU (CHECK-IN & LIVE WALL)
│   ├── CLI-CKIN-001: Hướng dẫn đội lễ tân mở cổng điểm danh (/diem-danh-tiec/{slug}) trên điện thoại
│   ├── CLI-CKIN-002: Quét mã QR trên thiệp khách để điểm danh trong 1 giây và xem số bàn tiệc
│   ├── CLI-CKIN-003: Xử lý khách phát sinh (Walk-in) và cập nhật số người đi kèm tại sảnh tiệc
│   ├── CLI-CKIN-004: Cơ chế điểm danh ngoại tuyến (Offline Check-in) khi sảnh tiệc mất sóng Wi-Fi/4G
│   ├── CLI-CKIN-005: Cấu hình Màn hình Chào sảnh tiệc (Venue Welcome Kiosk) chạy tự động
│   └── CLI-CKIN-006: Thiết lập Màn hình Trình chiếu Sân khấu (Live Wall) hiển thị lời chúc và mã PIN bảo vệ
│
├── 07. SỔ KÝ TÊN, LỜI CHÚC & ALBUM KHÁCH MỜI (GUESTBOOK & MEMORIES)
│   ├── CLI-WISH-001: Sổ ký tên điện tử: Khách vẽ chữ ký chúc phúc và gửi lời chúc tốt đẹp
│   ├── CLI-WISH-002: Cách cặp đôi duyệt lời chúc và gửi lời cảm ơn phản hồi lại từng người
│   ├── CLI-WISH-003: Tính năng Photo Drop: Khách mời quét QR tải ảnh chụp tại đám cưới lên album chung
│   ├── CLI-WISH-004: Kiểm duyệt ảnh khách gửi trước khi cho phép chiếu lên màn hình sân khấu
│   └── CLI-WISH-005: Xuất toàn bộ sổ ký tên và lời chúc thành kỷ vật lưu niệm số (Signature Archive)
│
├── 08. SỔ MỪNG CƯỚI VIETQR & TÀI CHÍNH ĐÁM CƯỚI (GIFT REGISTRY & BUDGET)
│   ├── CLI-GIFT-001: Cài đặt thông tin tài khoản ngân hàng chú rể và cô dâu hiển thị mã VietQR chuẩn
│   ├── CLI-GIFT-002: Hướng dẫn khách mừng cưới quét mã QR tự động điền đúng số tài khoản và lời chúc
│   ├── CLI-GIFT-003: Quản lý sổ mừng cưới: Ghi chép tiền mừng thực tế và đối soát minh bạch
│   └── CLI-GIFT-004: Lập bảng dự toán ngân sách cưới (Wedding Budget) và theo dõi chi phí thực tế
│
├── 09. HỘP THỜI GIAN & KỶ NIỆM NGÀY CƯỚI (TIME CAPSULE & ANNIVERSARY)
│   ├── CLI-TIME-001: Hộp thời gian là gì? Khách gửi lời chúc tương lai hẹn ngày mở khóa
│   ├── CLI-TIME-002: Niêm phong hộp thời gian và cách hệ thống tự động gửi email mở khóa vào kỷ niệm 1 năm
│   ├── CLI-TIME-003: Cấp Chứng nhận Ngày cưới số hóa (Digital Keepsake Certificate) lưu giữ trọn đời
│   └── CLI-TIME-004: Xuất gói lưu trữ đám cưới trọn đời (Portable Wedding Archive) tải về máy tính
│
└── 10. DÀNH CHO ĐỐI TÁC STUDIO & BẢO MẬT TÀI KHOẢN (PARTNER & PRIVACY)
    ├── CLI-PART-001: Hướng dẫn đăng ký trở thành Đối tác Studio Cưới Mây Hẹn
    ├── CLI-PART-002: Tạo và bàn giao thiệp cưới cho các cặp đôi khách hàng của studio
    ├── CLI-PART-003: Theo dõi ví hoa hồng, lịch sử thanh toán và tạo lệnh rút tiền
    ├── CLI-PART-004: Cổng duyệt thiệp dành cho hai bên gia đình phụ mẫu (Family Approval Portal)
    └── CLI-PART-005: Quyền riêng tư theo Luật 91/2025: Cách tải toàn bộ dữ liệu hoặc yêu cầu xóa tài khoản
```

---

## 3. SITEMAP TRUNG TÂM HƯỚNG DẪN QUẢN TRỊ VIÊN (`/admin/docs/`)

Trung tâm `/admin/docs/` được cấu trúc thành **10 Chuyên mục Nghiệp vụ Quản trị** với **48 quy trình chuẩn (SOP)**, hỗ trợ đào tạo nhân sự mới và vận hành bàn giao hệ thống:

```
/admin/docs/
├── 01. QUY CHUẨN TIẾP NHẬN & VẬN HÀNH DÀNH CHO NHÂN SỰ MỚI (ONBOARDING & SOP)
│   ├── ADM-SOP-001: Bàn giao hệ thống: Tổng quan kiến trúc Mây Hẹn, môi trường Staging/Production
│   ├── ADM-SOP-002: Quy tắc bảo mật nhân viên: Đăng nhập an toàn, 2FA, nguyên tắc Zero Data Leakage
│   ├── ADM-SOP-003: Danh mục vai trò và ma trận phân quyền RBAC (Super Admin, Manager, Moderator, Support, Finance)
│   ├── ADM-SOP-004: Checklist bàn giao ca trực hàng ngày và sổ tay xử lý sự cố khẩn cấp (P0/P1 Runbook)
│   └── ADM-SOP-005: Quy trình tiếp nhận và xử lý yêu cầu quyền riêng tư dữ liệu cá nhân (Luật 91/2025/QH15)
│
├── 02. TRUNG TÂM ĐIỀU HÀNH & GIÁM SÁT KINH DOANH (DASHBOARD & ANALYTICS)
│   ├── ADM-DASH-001: Hướng dẫn đọc Dashboard doanh thu thực tế, biểu đồ tăng trưởng và tỷ lệ chuyển đổi
│   ├── ADM-DASH-002: Bảng chỉ số sức khỏe ngày cưới (Wedding Readiness Radar) và cảnh báo trễ hạn
│   ├── ADM-DASH-003: Giám sát trạm quan sát chi phí & chất lượng AI (AI Cost & Quality Observatory)
│   └── ADM-DASH-004: Xuất báo cáo kinh doanh tổng hợp định dạng CSV UTF-8 phục vụ ban giám đốc
│
├── 03. QUẢN LÝ TÀI KHOẢN KHÁCH HÀNG & PHÂN QUYỀN (USERS & RBAC)
│   ├── ADM-USER-001: Tra cứu hồ sơ khách hàng toàn diện (Customer 360 View) và lịch sử giao dịch
│   ├── ADM-USER-002: Quy trình khóa tài khoản vi phạm, mở khóa và đặt lại mật khẩu an toàn
│   ├── ADM-USER-003: Phân quyền vai trò nhân viên và phòng ngừa lỗi tự tước quyền Super Admin
│   ├── ADM-USER-004: Thực thi quyền lãng quên (PII Anonymization): Ẩn danh hóa thông tin nhưng bảo toàn hóa đơn kế toán
│   └── ADM-USER-005: Kiểm tra và trích xuất nhật ký hoạt động kiểm toán (Audit Log) truy vết thao tác
│
├── 04. QUẢN LÝ THIỆP CƯỚI & CAN THIỆP SỰ CỐ KHÁCH HÀNG (INVITATIONS OPS)
│   ├── ADM-INVT-001: Tra cứu thiệp theo mã, số điện thoại, tên cặp đôi hoặc tên miền phụ
│   ├── ADM-INVT-002: Xử lý khóa thiệp khẩn cấp khi phát hiện nội dung độc hại hoặc tranh chấp bản quyền
│   ├── ADM-INVT-003: Quy trình hỗ trợ khách đổi ngày cưới (Reschedule) và cập nhật thiệp đồng loạt
│   ├── ADM-INVT-004: Gia hạn thời gian lưu trữ thiệp cưới và quy trình chuyển đổi gói dịch vụ
│   ├── ADM-INVT-005: Khôi phục phiên bản thiệp cũ từ lịch sử bản sửa đổi (Invitation Revisions)
│   └── ADM-INVT-006: Can thiệp hỗ trợ thiệp khách hàng mà không làm rò rỉ hoặc mất dữ liệu khách mời
│
├── 05. QUẢN LÝ MẪU THIỆP & PHÁT HÀNH RELEASE (TEMPLATES & RELEASES)
│   ├── ADM-TMPL-001: Thêm mẫu thiệp mới vào hệ thống: Cấu hình schema.json, ảnh đại diện và 10 section
│   ├── ADM-TMPL-002: Sử dụng công cụ Chẩn đoán Asset (Asset Diagnostics) quét ảnh và tài nguyên ngoài
│   ├── ADM-TMPL-003: Thao tác Localize & Batch-repair: Đưa 100% tài nguyên về thư mục cục bộ tự chứa
│   ├── ADM-TMPL-004: Kích hoạt, gắn nhãn Nổi bật (Featured) và ẩn mẫu thiệp bảo trì
│   └── ADM-TMPL-005: Quy trình phát hành bản phát hành mới (Release Control) và cơ chế Rollback an toàn
│
├── 06. ĐƠN HÀNG, ĐỐI SOÁT SEPAY & HOÀN TIỀN (ORDERS, SEPAY & REFUNDS)
│   ├── ADM-ORDR-001: Tra cứu đơn hàng, trạng thái thanh toán và kiểm tra chữ ký số Webhook SePay
│   ├── ADM-ORDR-002: Quy trình kích hoạt gói dịch vụ thủ công khi khách chuyển khoản sai nội dung
│   ├── ADM-ORDR-003: Quy trình xét duyệt yêu cầu hoàn tiền (Refund Workflow) theo 3 cấp phê duyệt
│   ├── ADM-ORDR-004: Xuất hóa đơn VAT điện tử và lưu trữ chứng từ thanh toán kế toán
│   └── ADM-ORDR-005: Đối soát số dư ngân hàng định kỳ và xử lý giao dịch treo/bất thường
│
├── 07. QUẢN TRỊ KHÁCH MỜI, CHECK-IN COCKPIT & SƠ ĐỒ BÀN (WEDDING OPS COCKPIT)
│   ├── ADM-COCK-001: Bảng điều khiển Giám sát Lễ tân (Admin Check-in Cockpit) trực tiếp ngày cưới
│   ├── ADM-COCK-002: Hỗ trợ xử lý sự cố nghẽn mạng sảnh tiệc và hướng dẫn lễ tân chuyển chế độ offline
│   ├── ADM-COCK-003: Hỗ trợ cô dâu chú rể giải quyết xung đột sức chứa bàn tiệc trong giờ cao điểm
│   ├── ADM-COCK-004: Kiểm duyệt ảnh Photo Drop và xử lý báo cáo vi phạm nội dung (Content Moderation)
│   └── ADM-COCK-005: Kiểm tra và hỗ trợ giải phóng hàng đợi gửi tin Zalo/SMS cho khách mời
│
├── 08. HỆ THỐNG HỖ TRỢ KHÁCH HÀNG & THÔNG BÁO (SUPPORT & MESSAGING)
│   ├── ADM-SUPP-001: Tiếp nhận và phân loại Ticket hỗ trợ theo độ ưu tiên (SLA Response Time)
│   ├── ADM-SUPP-002: Quản lý và sử dụng thư viện câu trả lời mẫu chuẩn mực (Canned Responses)
│   ├── ADM-SUPP-003: Tính năng Đóng vai hỗ trợ (Impersonation): Đăng nhập hỗ trợ khách an toàn, có ghi log
│   ├── ADM-SUPP-004: Tạo và phát thông báo hệ thống (Site Announcements) trên giao diện Client
│   └── ADM-SUPP-005: Điều phối tin nhắn thông báo tập trung (Reliable Messaging Gateway Dispatcher)
│
├── 09. QUẢN TRỊ ĐỐI TÁC STUDIO & ĐỐI SOÁT HOA HỒNG (PARTNER MANAGEMENT)
│   ├── ADM-PART-001: Xét duyệt hồ sơ đăng ký đối tác Studio cưới mới và kích hoạt tài khoản
│   ├── ADM-PART-002: Quản lý cấu hình mức chiết khấu hoa hồng theo từng cấp độ đối tác (Tier)
│   ├── ADM-PART-003: Kiểm tra và đối soát doanh thu tạo ra từ các thiệp của đối tác
│   └── ADM-PART-004: Phê duyệt lệnh rút tiền hoa hồng: Kiểm tra mã chuẩn chi ngân hàng trước khi chuyển trạng thái Settled
│
└── 10. HỆ THỐNG, SỨC KHỎE, SAO LƯU & COCKPIT NGHIỆM THU (SYSTEM & HEALTH)
    ├── ADM-SYS-001: Kiểm tra bảng sức khỏe hệ thống (System Health Check): CPU, RAM, Disk, DB Connection
    ├── ADM-SYS-002: Giám sát hàng đợi tác vụ (Job Center): Xử lý failed jobs, thử lại tác vụ an toàn
    ├── ADM-SYS-003: Quy trình sao lưu toàn diện (System Backup) và diễn tập khôi phục thảm họa (Disaster Recovery)
    ├── ADM-SYS-004: Bảng điều khiển Nghiệm thu Thương mại (Admin Acceptance Audit Cockpit): Chấm điểm tuân thủ
    ├── ADM-SYS-005: Cấu hình nền tảng (Platform Settings), Bật/Tắt Cờ tính năng (Feature Flags) cô lập
    └── ADM-SYS-006: Quản lý tên miền tùy chỉnh (Custom Domains) và kiểm tra bản ghi DNS SSL tự động
```

---

## 4. TỔNG HỢP CHỈ SỐ BAO PHỦ CỦA SITEMAP

| Chỉ số Quy hoạch | Phân hệ Client (`/docs/`) | Phân hệ Admin (`/admin/docs/`) | Toàn Hệ thống |
| :--- | :--- | :--- | :--- |
| **Số Chuyên mục Lớn (Categories)** | 10 Chuyên mục | 10 Chuyên mục | **20 Chuyên mục** |
| **Số Bài hướng dẫn Chi tiết (Articles)** | 52 Bài hướng dẫn | 48 Bài hướng dẫn & SOP | **100 Bài hướng dẫn** |
| **Tỷ lệ bao phủ Chức năng (Coverage)** | 100% (393 routes, 81 controllers) | 100% (362 routes, 59 controllers) | **100% Hệ thống** |
| **Bao phủ Tác vụ Ngầm (Cron/Jobs)** | 5 Scheduled Commands | 12 Queue/Event Listeners | **100% Tác vụ ngầm** |
| **Mức độ sẵn sàng triển khai** | Hoàn tất cấu trúc cây & mã bài | Hoàn tất cấu trúc cây & mã bài | **Sẵn sàng bước vào W02** |

