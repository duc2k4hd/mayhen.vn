# MÂY HẸN — MASTER PRODUCT REQUIREMENTS & IMPLEMENTATION PROMPT
## Nền tảng tạo thiệp cưới trực tuyến, Studio cá nhân hóa, quản lý khách mời và vận hành SaaS

**Phiên bản đề xuất:** 3.0 — Full Platform / Admin-First / Wedding Experience / Reference-Locked Templates  
**Website:** `mayhen.vn`  
**Tech baseline:** Laravel + Blade, HTML5, CSS3, JavaScript ES Modules thuần cho trang thiệp.  
**Trạng thái tài liệu:** ĐẶC TẢ ĐỀ XUẤT để thiết kế và phát triển; **không phải tuyên bố các chức năng đã triển khai**.  
**Đối tượng đọc:** Chủ sản phẩm, thiết kế UI/UX, backend, frontend, QA, DevOps và AI agent triển khai.

### Đối chiếu với repository — 06/11/2026 (đang thực hiện)

Phần này ghi nhận bằng chứng ban đầu, không thay thế kiểm toán đầy đủ hoặc xác nhận một tính năng đã sẵn sàng kinh doanh. Xem [kiểm toán bước đầu](MAY_HEN_PROJECT_AUDIT.md) và [roadmap triển khai](MAY_HEN_IMPLEMENTATION_ROADMAP.md) để biết phạm vi kiểm tra, công việc còn lại và tiêu chí nghiệm thu.

- **Công nghệ thực tế:** `composer.json` yêu cầu PHP `^8.3`, Laravel `^13.17`; phiên bản được kiểm tra trong workspace là PHP CLI 8.5.10 và Laravel 13.35.0. Blade, CSS thuần và JavaScript thuần là quy ước sản phẩm; việc khai báo một dependency frontend không chứng minh nó đang được sử dụng.
- **Mô hình đang có:** `users` → `weddings` → `invitations`; mẫu ở `invitation_templates`; khách và RSVP gắn với `weddings`. Các bảng workspace, membership, wedding event và template version ở mục 38 là phương án mục tiêu, chưa được xác nhận đã tồn tại. Không tạo migration hoặc đổi tên bảng chỉ để khớp sơ đồ đề xuất.
- **Studio:** Schema được chuẩn hóa qua `InvitationTemplateService`; mười section canonical hiện tại là `hero`, `story`, `parents`, `countdown_calendar`, `events`, `timeline`, `dresscode`, `album`, `gift`, `ending`. Opening là phần bao ngoài theo skill mẫu thiệp. Có endpoint autosave và revision; bảo toàn dữ liệu khi đồng bộ, cô lập nháp và công khai vẫn cần kiểm thử trước khi coi là hoàn tất.
- **Bảo vệ P0:** Cần xử lý và xác minh quyền quản lý khách, xác thực webhook thanh toán, quyền gắn đơn hàng với thiệp và phạm vi guest token trước khi coi các luồng này đủ an toàn để mở bán. Danh sách chi tiết và điều kiện xác minh nằm trong báo cáo kiểm toán.
- **Thứ tự thực hiện:** Ưu tiên bảo vệ dữ liệu khách và giao dịch, sau đó ổn định Studio, xuất bản và RSVP. Không mặc định triển khai ngay đa tenant, role matrix, snapshot, hàng loạt bảng mới hoặc các tính năng P2; thiết kế chi tiết theo nhu cầu và tương thích dữ liệu hiện hữu.
- **Thứ bậc áp dụng:** Yêu cầu người dùng và `AGENTS.md` hiện hành áp dụng cho công việc trong repository. Phụ lục clone ở cuối là tài liệu nguồn tham khảo, không tự kích hoạt yêu cầu clone một mẫu hay ghi đè các quy tắc hiện hành khi làm tác vụ khác.

> **Mục tiêu của bản viết lại:** File gốc chỉ tập trung clone trung thực **một** mẫu thiệp `Hương Hoa Ngày Đính Ước` từ URL tham chiếu. Bản này mở rộng thành kiến trúc và danh mục chức năng cho toàn bộ nền tảng Mây Hẹn, đặc biệt giao diện **Super Admin** và **Admin của khách hàng**. Đồng thời giữ nguyên những điều khoản không thể thỏa hiệp của file gốc về clone, asset, Studio, RSVP, chữ ký, responsive, QA và quyền sử dụng nội dung. **Toàn văn prompt gốc được đính ở cuối tài liệu làm phụ lục tham chiếu.**

---

# PHẦN I — TẦM NHÌN SẢN PHẨM, RANH GIỚI VÀ NHÓM NGƯỜI DÙNG

## 1. Mây Hẹn là gì?

Mây Hẹn không chỉ là trang chọn mẫu thiệp và thay tên cô dâu/chú rể. Mây Hẹn phải là nền tảng SaaS phục vụ toàn bộ hành trình chuẩn bị và gửi thiệp: chọn mẫu → nhập thông tin → chỉnh sửa bằng Studio → xuất bản → tạo liên kết riêng cho khách → phân phối thiệp → theo dõi lượt xem/RSVP → phân bàn → check-in → tiếp nhận lời chúc/chữ ký → thống kê → lưu giữ sau lễ cưới. Song song là hệ quản trị vận hành nội bộ: quản lý mẫu, khách hàng, gói dịch vụ, doanh thu, đối tác, nội dung, hỗ trợ, bảo mật, hiệu năng, SEO và nhật ký hoạt động.

### 1.1. Mục tiêu trải nghiệm có thể nghiệm thu

- Khách mới nhìn được mẫu và xem trước **trước khi đăng ký**; có lộ trình tạo thiệp rõ 5–7 bước, không bắt người dùng hiểu kỹ thuật.
- Người tạo thiệp hiểu ngay: **Bước tiếp theo là gì?**, **Thiệp đã lưu chưa?**, **Đã xuất bản chưa?**, **Ai đã xác nhận?**, **Link của mình là gì?**.
- Admin điều hành xem được tình hình toàn nền tảng ngay trên một màn hình, nhưng không bị chìm trong bảng số liệu.
- Các trang quản trị đều có bộ lọc, thao tác nhanh, lịch sử, phân quyền, trạng thái rỗng, xử lý lỗi, xác nhận thao tác nguy hiểm.
- Tất cả dữ liệu quản lý theo *wedding/workspace/event scope*, không có rò rỉ dữ liệu giữa các khách hàng.
- Mỗi mẫu thiệp có tính cách riêng; hệ quản trị Mây Hẹn có design system riêng. **Không ép giao diện thiệp clone phải giống giao diện admin**.
- Không giả lập thành công cho thanh toán, gửi tin, upload, RSVP, chữ ký, đồng bộ dữ liệu hoặc xuất ZIP.

### 1.2. Phân biệt các lớp sản phẩm

| Lớp | Người dùng | Mục đích | Nguyên tắc UI |
|---|---|---|---|
| Website marketing | Khách chưa đăng nhập | Khám phá mẫu, bảng giá, hướng dẫn, SEO | Đẹp, nhanh, CTA dễ hiểu |
| Client account | Cô dâu/chú rể, gia đình, wedding planner | Tạo thiệp, tổ chức khách mời và sự kiện | Wizard, ngôn ngữ đơn giản, mobile-friendly |
| Invitation Studio | Người sở hữu thiệp/biên tập viên | Sửa nội dung, hình ảnh, bố cục, dữ liệu động | Preview thật, autosave, undo/redo |
| Public invitation | Khách được mời | Mở thiệp, xem thông tin, RSVP, gửi lời chúc | Giữ nguyên thiết kế mẫu; nhanh, dễ thao tác |
| Super Admin | Đội vận hành Mây Hẹn | Khách hàng, mẫu, giao dịch, hỗ trợ, nội dung, hệ thống | Tập trung thao tác, audit, báo cáo, phân quyền |
| Agency/Partner portal | Đối tác tổ chức cưới, người bán mẫu | Quản lý nhiều khách/thiệp theo quyền | Multi-workspace, chia sẻ quyền, doanh thu liên quan |

## 2. Vai trò, phân quyền và ranh giới dữ liệu

### 2.1. Vai trò đề xuất

1. **Super Admin:** Toàn quyền cấu hình nền tảng, phân quyền và can thiệp có audit.
2. **Platform Operator:** Theo dõi tài khoản, mẫu, thiệp, đơn hàng và xử lý tác vụ được giao; không tự động có quyền xem dữ liệu nhạy cảm.
3. **Template Designer:** Tạo/sửa bản nháp, asset và kiểm thử mẫu; chỉ được publish mẫu khi đủ quyền/đã duyệt.
4. **Content/SEO Editor:** Trang marketing, bài viết, danh mục, sitemap, nội dung công khai.
5. **Finance:** Đơn hàng, đối soát, hoàn tiền, hóa đơn; quyền hạn phù hợp, không đọc chữ ký/lời chúc riêng tư mặc định.
6. **Customer Support:** Ticket, trạng thái khách, xem sự cố tối thiểu cần thiết; quyền hỗ trợ có điều kiện, có log.
7. **Workspace Owner:** Chủ tài khoản/cặp đôi/đại diện, toàn quyền với workspace của mình.
8. **Workspace Editor:** Sửa thiệp, nội dung, media trong workspace được chia sẻ.
9. **Guest Manager:** Danh sách khách, RSVP, gửi lời mời, phân bàn, check-in; không chỉnh gói cước.
10. **Check-in Staff:** Chỉ xem danh sách/check-in theo sự kiện và khoảng thời gian được cấp.
11. **Agency Owner/Member:** Nhiều workspace khách hàng theo hợp đồng/quyền được cấp.
12. **Invited Guest:** Chỉ truy cập phạm vi được mời, theo token/thiệp công khai; không có quyền vào dashboard.

### 2.2. RBAC phải ở mức hành động

Không gói toàn bộ thành `is_admin`. Thiết kế permission như `templates.view/create/edit/publish/archive`, `weddings.view/edit/publish`, `guests.import/export/delete`, `rsvp.view/moderate`, `finance.refund`, `settings.billing`, `support.access_sensitive`, `users.impersonate`, `audit.view`. Kiểm tra ở cả route, policy, query scope và API; nút bị ẩn trên UI **không phải** bảo mật. Mọi thao tác quyền cao phải ghi actor, target, thời gian, IP tương đối, lý do và old/new value đã lược bỏ bí mật. Có xác thực bổ sung cho hành động nhạy cảm nếu triển khai.

### 2.3. Tenant/workspace/event hierarchy

- `User` có thể sở hữu nhiều `Workspace` hoặc được mời vào workspace khác.
- `Workspace` chứa một hay nhiều `WeddingProject` (hoặc nhiều sự kiện nếu gói hỗ trợ).
- Một `WeddingProject` có thể có nhiều `WeddingEvent`: lễ ăn hỏi, lễ cưới, tiệc nhà trai, tiệc nhà gái, tiệc bạn bè, after-party.
- Một `WeddingProject` có thể có nhiều `Invitation`/biến thể nội dung gửi riêng cho nhóm khách (ví dụ thiệp bạn bè vs gia đình) **nhưng cùng nguồn dữ liệu được chọn**.
- `Guest`, `GuestGroup`, `RSVP`, `SeatAssignment`, `InvitationDelivery`, `CheckIn` có liên hệ dự án/sự kiện cụ thể, không nhầm lẫn giữa các tiệc.
- Nếu cho phép agency dùng chung dự án khách hàng, mỗi lần truy cập phải có membership hợp lệ; không “đoán ID” để xem dữ liệu.

## 3. Phân kỳ sản phẩm bắt buộc

| Nhóm | P0: Có thể mở bán an toàn | P1: Vận hành tốt | P2: Nâng cao |
|---|---|---|---|
| Tài khoản | Đăng ký/đăng nhập, quên mật khẩu, email verify, RBAC | 2FA, phiên hoạt động, mời cộng tác | SSO agency |
| Thiệp | Thư viện mẫu, tạo, Studio, autosave, preview, publish | Versioning, mẫu biến thể, lịch publish | Chỉnh sửa bố cục nâng cao |
| Khách mời | Nhập/tạo/sửa, nhóm, link riêng | CSV/Excel import, bulk, lịch sử gửi | Gợi ý/loại trùng nâng cao |
| RSVP | Trả lời, giới hạn số khách, admin dashboard | Nhắc RSVP, thay đổi xác nhận | Luồng đăng ký chọn menu/tùy chỉnh |
| Lời chúc/ký tên | Gửi, duyệt/xóa, canvas thực | Media guestbook, xuất lưu niệm | Guestbook layout nhiều loại |
| Quản trị | KPI, mẫu, tài khoản, thiệp, hỗ trợ, cấu hình | Billing, báo cáo, automation, audit | Agency marketplace, nâng cao |
| Thanh toán | Gói, order, trạng thái xác thực, webhook | Coupon, refund, invoice | Đối tác/hoa hồng |
| Vận hành | Backup, log lỗi, monitoring cơ bản | Job center, queue dashboard, kiểm tra asset | Capacity planning |

**Quy tắc phát triển:** P0 phải hoàn thành end-to-end trước khi làm P2. Chức năng chưa có backend không được đưa vào UI như đã hoàn chỉnh.

---

# PHẦN II — SƠ ĐỒ THÔNG TIN VÀ CHUẨN GIAO DIỆN QUẢN TRỊ

## 4. Sitemap Super Admin — đầy đủ nhóm menu

```text
TỔNG QUAN
  Dashboard tổng quan
  Hoạt động thời gian gần đây
  Công việc/cảnh báo cần xử lý

KHÁCH HÀNG & ĐỐI TÁC
  Danh sách tài khoản
  Workspace / Dự án cưới
  Tổ chức / Agency
  Phân nhóm khách hàng
  Tài khoản khóa / chờ xác minh
  Vai trò & phân quyền

KHO MẪU THIỆP
  Tất cả mẫu
  Tạo mẫu / Import ZIP
  Mẫu nháp / Chờ duyệt / Đang bán / Ẩn
  Bộ sưu tập / Danh mục / Tag
  Tác giả / Designer
  Phiên bản mẫu / Changelog
  Kho tài nguyên mẫu
  Visual QA / Audit mẫu

THIỆP & SỰ KIỆN
  Tất cả thiệp đã tạo
  Thiệp nháp / Đã xuất bản / Tạm dừng
  Dự án cưới & lịch sự kiện
  Số liệu khách mời / RSVP / Check-in
  Thiệp báo lỗi / Thiếu asset

DOANH THU
  Gói dịch vụ & quyền lợi
  Đơn hàng / Thanh toán
  Giao dịch / Webhook / Đối soát
  Mã giảm giá / Chiến dịch
  Gia hạn / Hết hạn
  Hoàn tiền / Khiếu nại
  Hóa đơn (nếu triển khai)

TRUYỀN THÔNG & NỘI DUNG
  Trang giới thiệu / Landing pages
  Blog / FAQ / Hướng dẫn
  SEO metadata / Redirects
  Banner / Popup / Thông báo công khai
  Email template / Message template
  Campaign & Automation
  Form liên hệ / Leads

VẬN HÀNH
  Ticket & yêu cầu hỗ trợ
  Kiểm duyệt lời chúc/báo cáo lạm dụng
  Công việc nền / Job Center
  Upload/media health
  Hệ thống gửi email/tin nhắn
  Trạng thái dịch vụ / Error logs
  Audit log / Lịch sử thay đổi

PHÂN TÍCH
  Lượt truy cập & chuyển đổi
  Mẫu được chọn nhiều
  Khách hàng & tỷ lệ giữ chân
  Doanh thu / MRR (nếu thuê bao)
  Funnel tạo thiệp
  Hiệu quả gửi thiệp & RSVP

CÀI ĐẶT
  Thương hiệu Mây Hẹn
  Cấu hình website / SEO
  Domain & liên kết
  Storage & CDN
  Email / SMS / Zalo provider
  Payment gateways
  Privacy / Retention
  Security / Rate limits
  Feature flags
  API keys / Webhooks
  Sao lưu / Khôi phục
```

## 5. Sitemap Client Dashboard — thao tác theo ngữ cảnh cưới

```text
TRANG CHỦ CỦA TÔI
  Tóm tắt dự án / Trạng thái thiệp / Nhiệm vụ tiếp theo

THIỆP CƯỚI
  Thiệp của tôi
  Chọn / Đổi mẫu
  Studio chỉnh sửa
  Xem trước / Lịch sử phiên bản
  Xuất bản / Link / QR thiệp

THÔNG TIN CƯỚI
  Cô dâu & chú rể
  Hai bên gia đình
  Lễ & tiệc / Lịch trình
  Địa điểm / Bản đồ
  Album / Video / Nhạc
  Lời nhắn / Chuyện tình yêu
  Thông tin nhận quà (tùy chọn)

KHÁCH MỜI
  Danh sách khách
  Nhóm & nhãn
  Import / Export
  Tạo link cá nhân hóa
  Gửi lời mời / Theo dõi gửi
  RSVP / Câu trả lời
  Bàn tiệc / Sơ đồ chỗ ngồi
  Check-in ngày cưới

TƯƠNG TÁC
  Lời chúc / Sổ ký tên
  Thư viện ảnh khách gửi (tùy chọn)
  Kiểm duyệt

BÁO CÁO
  Lượt xem thiệp / Khách mở
  RSVP & Số lượng tham dự
  Hiệu quả từng kênh

TÀI KHOẢN
  Gói đang sử dụng / Gia hạn
  Thanh toán / Hóa đơn
  Thành viên & quyền
  Domain / Riêng tư
  Hỗ trợ / Trung tâm hướng dẫn
```

## 6. Chuẩn giao diện Admin: dễ dùng trước, đẹp sau nhưng phải chỉn chu

### 6.1. Layout màn hình desktop

- **Sidebar trái** có logo, ô tìm nhanh menu, nhóm chức năng rõ, icon đồng bộ, badge chỉ khi thực sự cần, trạng thái thu gọn, ghi nhớ nhóm menu đang mở.
- **Topbar** có breadcrumb, tìm kiếm toàn hệ thống, danh sách thông báo, công việc chờ xử lý, chuyển workspace nếu thuộc client/agency, avatar và menu tài khoản.
- **Content area** có tiêu đề trang + mô tả ngắn + hành động chính nổi bật (ví dụ **Tạo mẫu mới**), không xếp 5–7 nút CTA ngang hàng.
- Trên danh sách phải có: thẻ KPI vừa đủ; search; filter nâng cao; chọn cột; preset filter; nút xuất; bảng dữ liệu; phân trang; row action.
- **Right drawer** để xem/sửa nhanh mà không mất filter, vị trí cuộn, trang hiện tại; modal chỉ dành cho xác nhận/quy trình ngắn.
- **Bottom system feedback:** snackbar/toast có nội dung rõ, không che CTA, hỗ trợ undo khi hành động có thể hoàn tác.
- Hỗ trợ màn hình 1366×768 trở lên không lãng phí khoảng trắng; laptop thấp có sticky toolbar trong vùng hợp lý.

### 6.2. Layout tablet/mobile

- Sidebar chuyển thành drawer; bộ lọc thành sheet; table thành danh sách card hoặc cuộn ngang có chủ đích và sticky 1–2 cột quan trọng.
- Nút thao tác đủ lớn để bấm bằng tay, tuyệt đối không phụ thuộc hover; không có nút chỉ hiển thị khi rê chuột.
- Trên mobile luôn ưu tiên 3 việc: xem trạng thái, thực hiện thao tác chính, tìm đối tượng; giảm biểu đồ dày đặc.
- Các biểu mẫu nhiều trường chia step/accordion, autosave khi phù hợp, tránh mất dữ liệu khi rời màn hình.

### 6.3. Mọi trang danh sách bắt buộc cùng chuẩn

| Thành phần | Yêu cầu hành vi cụ thể |
|---|---|
| Tìm kiếm | Debounce; tìm theo tên, mã, slug/email tùy đối tượng; thông báo từ khóa đang áp dụng |
| Bộ lọc | Status, ngày tạo/cập nhật, owner, gói, nguồn; multi-select; có nút xóa toàn bộ |
| Saved views | Mặc định/Tôi tạo/Gần hết hạn/Chờ duyệt; lưu theo user; hiển thị filter đang hoạt động |
| Sắp xếp | Click tiêu đề cột; chỉ sắp xếp field có hỗ trợ backend; giữ thứ tự khi phân trang |
| Cột | Ẩn/hiện, pin trái/phải, độ rộng, thứ tự; lưu preset cá nhân |
| Chọn nhiều dòng | Checkbox; toolbar xuất/chuyển trạng thái/gán người xử lý; xác nhận trước khi thực hiện |
| Phân trang | 10/20/50/100; tổng số chính xác hoặc giải thích nếu approximate; nhớ lựa chọn |
| Row action | Menu 3 chấm theo quyền; thao tác không được xuất hiện nếu vô nghĩa theo trạng thái |
| Export | CSV/XLSX theo quyền; phân biệt "trang hiện tại" và "toàn bộ kết quả lọc"; tác vụ lớn chạy job |
| Trạng thái | Text + biểu tượng/màu có ngữ nghĩa; không chỉ dựa vào màu; timestamp rõ múi giờ |
| Rỗng | Nêu nguyên nhân (chưa có dữ liệu vs filter quá chặt) và CTA thích hợp |
| Loading | Skeleton/placeholder, ngăn double-submit; giữ kích thước bảng để không nhảy layout |
| Lỗi | Nêu điều gì hỏng, retry, mã tham chiếu; không mất thông tin form |
| Xóa | Soft delete nơi phù hợp, lý do, hoàn tác trong thời gian chính sách; hard-delete phải có xác nhận riêng |
| Audit | Những thay đổi quan trọng hiển thị ai sửa và sửa khi nào |

### 6.4. Nguyên tắc microcopy và accessibility

- Dùng tiếng Việt rõ: **"Xuất bản thiệp"** thay vì **"Publish"**, **"Khách đã xác nhận tham dự"** thay vì `RSVP completed` (giữ thuật ngữ kỹ thuật ở tooltip/chú giải).
- Label có ví dụ dữ liệu, helper text và validation đặt cạnh field. Đánh dấu bắt buộc; không dùng placeholder làm label duy nhất.
- Input date/time theo lịch Việt, hiển thị timezone `Asia/Ho_Chi_Minh`. Không nhầm giờ của lễ và giờ mở cổng check-in.
- Hỗ trợ tab, focus-visible, Esc cho modal, ARIA cho drawer, thông báo đọc được bằng screen reader.
- Không hiển thị số liệu giả. Trường hợp chưa có số liệu phải ghi "Chưa có dữ liệu" và nguyên nhân.
- Không hiển thị thông tin ngân hàng/điện thoại đầy đủ trong bảng Super Admin nếu không cần.

### 6.5. Design token của Admin — độc lập với thiệp clone

Token đề xuất cho giao diện quản trị: `--mayhen-admin-bg`, `--mayhen-admin-surface`, `--mayhen-admin-text`, `--mayhen-admin-muted`, `--mayhen-admin-primary`, `--mayhen-admin-border`, `--mayhen-admin-radius`, `--mayhen-admin-shadow`... Màu thực tế phải được chốt từ bộ nhận diện Mây Hẹn; không suy diễn bảng màu từ mẫu thiệp gốc. Class của trang admin login tiếp tục prefix `mayhen_admin_login_`; client login `mayhen_login_`; register `mayhen_register_`; 404 `mayhen_error_404_`; template thiệp prefix `mayhen_invitation_` theo quy tắc file gốc. Các trang admin khác dùng `mayhen_admin_` để chống xung đột.

---

# PHẦN III — CHỨC NĂNG CHI TIẾT SUPER ADMIN

## 7. Dashboard điều hành

### 7.1. Hàng chỉ số đầu tiên

- Tổng tài khoản; tài khoản mới hôm nay/7/30 ngày; tỷ lệ đã xác thực email.
- Tổng workspace/dự án cưới; thiệp đang soạn; đã xuất bản; sắp diễn ra trong 7/30 ngày; hết hạn gói.
- Doanh thu đơn thanh toán thành công (không cộng đơn pending); doanh thu ròng sau hoàn tiền; đơn hàng chờ xử lý.
- Số mẫu công khai/nháp/chờ duyệt/bị lỗi QA; mẫu được dùng nhiều nhất.
- RSVP toàn hệ thống dạng tổng hợp được phép, không lộ thông tin khách cá nhân ngoài nhiệm vụ vận hành.
- Ticket mới/đang xử lý/quá SLA; job failed; webhook lỗi; storage sắp đầy.

### 7.2. Biểu đồ và danh sách tác vụ

- Đồ thị tăng trưởng tài khoản/thiệp theo ngày/tuần/tháng, bộ lọc thời gian chung và ghi rõ đơn vị.
- Funnel: khách ghé thư viện → chọn mẫu → đăng ký → hoàn tất thông tin → xuất bản → mua gói.
- Top 10 mẫu theo số thiệp **đã xuất bản** và theo conversion; tránh nhầm lượt click với lượt dùng thật.
- Danh sách 10 ticket gấp, mẫu chờ duyệt, khách có thanh toán lỗi, job lỗi gần nhất.
- Widget cảnh báo kèm CTA đến màn hình xử lý, ví dụ "3 webhook thanh toán cần đối soát".

### 7.3. Tương tác

- Click KPI mở danh sách có filter sẵn, không phải dashboard tĩnh.
- Bộ lọc ngày thống nhất tất cả widget; có nút so sánh với kỳ trước, ghi công thức tính.
- Export báo cáo theo quyền; các widget cá nhân có thể sắp xếp P1.
- Data tạm thời/lỗi tải widget không làm sập toàn dashboard; hiển thị thời điểm cập nhật cuối.

## 8. Quản lý tài khoản người dùng

**Danh sách:** ID công khai, avatar, họ tên, email đã che một phần theo quyền, số điện thoại nếu có, email verified, trạng thái, ngày tham gia, số dự án, gói hiện tại, lần hoạt động gần nhất. Search ID/email/phone; filter status/verified/plan/date/source.

**Trang chi tiết có tab:** Hồ sơ; Workspace/Thiệp; Đơn hàng; Gói & quyền; Hoạt động/Đăng nhập; Ticket; Nhật ký admin. Nút thao tác: xác minh thủ công có quyền và audit, gửi lại email, khóa/mở tài khoản kèm lý do, đặt lại quyền/role, thu hồi phiên; **không bao giờ xem mật khẩu**. Không gửi mật khẩu plaintext. Khi khóa user, xác định chính sách với thiệp public đã xuất bản để không tự ý làm mất trang khách mời.

**Chống thao tác sai:** Không cho Super Admin tự xóa/quyền cuối cùng của mình nếu đó là tài khoản duy nhất; xóa user phải xét nghĩa vụ dữ liệu, hóa đơn, đơn hàng, project và yêu cầu xóa dữ liệu.

## 9. Workspace, dự án cưới và Agency

**Workspace:** tên, slug nội bộ, owner, thành viên, gói, quota, số dự án, storage, ngày tạo, status. Chi tiết có tab Dự án, Thành viên, Phân quyền, Gói, Tên miền, Hoạt động.

**WeddingProject:** tiêu đề nội bộ, tên cặp đôi hiển thị, ngày cưới, timezone, loại sự kiện, trạng thái chuẩn bị, danh sách invitation và event, chủ sở hữu, người cộng tác. Cho phép chuyển owner qua quy trình xác thực và có audit; chuyển quyền không xóa dữ liệu.

**Agency:** thông tin pháp lý ở mức cần thiết, thành viên, gói dành đối tác, danh sách khách hàng được quản lý, quyền truy cập theo từng khách, hạn mức tạo thiệp, lịch sử phân phối mẫu, hợp đồng/hóa đơn nếu có. Không cấp quyền truy cập mọi dữ liệu khách hàng chỉ vì thuộc agency.

## 10. Quản lý thư viện mẫu thiệp — trung tâm của Mây Hẹn

### 10.1. Danh sách mẫu

Các cột: ảnh thumbnail, mã mẫu, tên hiển thị, slug, danh mục, phong cách, tác giả, giá/điều kiện gói, phiên bản, trạng thái, số lần được tạo, số thiệp đã publish, cập nhật gần nhất, QA. Filter theo danh mục, màu sắc/tag, ngôn ngữ, miễn phí/trả phí, tài nguyên đầy đủ/thiếu, bản nháp/công khai/ngừng bán.

### 10.2. Tạo mẫu qua wizard

1. **Thông tin chung:** Tên, slug duy nhất, mô tả ngắn/dài, ảnh thumbnail, tags, loại sự kiện, ngôn ngữ, author, quy định bản quyền.
2. **Nguồn thiết kế:** Tạo mới/clone từ URL tham chiếu (khi hợp lệ)/import ZIP do chủ sở hữu cung cấp. Ghi link và trạng thái quyền tài nguyên.
3. **Upload package:** Kéo thả ZIP; kiểm ZIP slip, path traversal, extension, size/quota; giải nén vào vùng cách ly; chặn executable và code không được phép; không tự chạy JS khi phân tích package.
4. **Validation cấu trúc:** `template.blade.php`, `styles.css`, `script.js`, `schema.json`, manifest, assets; kiểm đường dẫn tương đối, hash, asset thiếu.
5. **Schema Studio:** Nhận dạng field, nhóm field, type, required, default, repeatable, validation, map từng `data-sync-target`.
6. **Preview:** Render trong sandbox/iframe an toàn ở nhiều viewport và `isEmbed`; có demo data trung tính.
7. **QA:** Responsive, accessibility, lỗi console, asset 404, video/audio, calendar, countdown, RSVP, signature, có so sánh hình nếu clone.
8. **Pricing/Visibility:** Free/paid/premium, hiển thị hay ẩn, gói được dùng, quyền sao chép/đổi mẫu, ảnh preview dùng cho listing.
9. **Duyệt & Publish:** Reviewer độc lập (khi cấu hình), ghi lý do reject; publish phiên bản bất biến; rollback nếu bản lỗi.

### 10.3. Trạng thái template

`draft → validating → ready_for_review → approved → published → archived`; có nhánh `validation_failed` và `rejected`. Chỉ `published` xuất hiện ở gallery. Bản publish không sửa trực tiếp source đang được thiệp khách dùng: tạo bản version mới, test và publish; chính sách migrate thiệp phải explicit. Mẫu đã có thiệp sử dụng phải có quy tắc xóa/ẩn, không hard delete phá dữ liệu người dùng.

### 10.4. Trang chi tiết mẫu

Tab **Thông tin | Giao diện | Schema | Assets | Phiên bản | QA | Lượt sử dụng | Lịch sử**. Một admin phải trả lời được: "Mẫu này có tốt không?", "Có đang dùng không?", "Thiếu gì?", "Phiên bản nào đang chạy?", "Ai đã xuất bản?".

### 10.5. Tìm kiếm/lọc dành cho khách

Danh mục: truyền thống/hiện đại/tối giản/hoa lá/ảnh cưới/cổ điển... là **taxonomy do admin quản lý**, không hard-code cố định. Bộ lọc: màu chủ đạo, kiểu bố cục, số lượng section, có sổ ký tên, RSVP, nhạc, hỗ trợ hai tiệc, miễn phí/trả phí; sort mới nhất, phổ biến, giá. Mọi bộ lọc phải đọc metadata thật.

## 11. Mẫu clone "Hương Hoa Ngày Đính Ước" — không làm mất chuẩn gốc

**Thông số gốc cần được kế thừa:** Website `mayhen.vn`; nguồn tham chiếu `https://songhy.online/invite/hoa-tulip-xanh-reu`; template tên **Hương Hoa Ngày Đính Ước**, slug `huong-hoa-ngay-dinh-uoc`, xuất ZIP `Hương Hoa Ngày Đính Ước.zip`, thư mục `huong-hoa-ngay-dinh-uoc/`.

**Hợp đồng bất biến:** Clone theo DOM/style/asset **thực sự quan sát được**, không tự bịa palette hoặc section; lưu asset đúng quyền sử dụng; không hotlink; không screenshot giả giao diện; prefix toàn bộ CSS class thiệp `mayhen_invitation_`; Blade + HTML/CSS/Vanilla ES Modules; đầy đủ `reference-audit.md`, `assets-manifest.json`, `visual-audit.md`, screenshot QA khi có thể; RSVP và Signature thật; live sync Studio; countdown/calendar timezone Việt Nam; chế độ `$isEmbed` không khóa cuộn/overlay Studio.

**Giải quyết xung đột kiến trúc:** Những tính năng phong phú của Admin **thuộc dashboard và backend**, không phải cái cớ để tùy tiện thêm section làm lệch template clone. Nếu chủ thiệp chọn bật tính năng Mây Hẹn-only chưa có trong mẫu gốc, cung cấp một section tương thích phong cách nguồn hoặc một module điều khiển bên ngoài khung thiệp; ghi rõ sai khác trong visual audit. Xem **PHỤ LỤC A** cho toàn bộ điều khoản chi tiết ban đầu.

## 12. Quản lý tất cả thiệp của khách hàng

**List:** Mã thiệp, ảnh mẫu, tên/slug, owner, workspace, template + version, ngày sự kiện, thời điểm publish, public URL, plan, lượt xem, trạng thái, cảnh báo. Filter unpublished/published/expired/suspended, template, date, owner, quota. Search tên/ID/URL.

**Actions:** View demo/public có quyền, kiểm ảnh bị lỗi, gửi lời nhắc cập nhật, bảo trì/ẩn riêng thiệp vi phạm theo quy trình có lý do và thông báo, chuyển trạng thái khi có quyền, xem log phát hành. Tuyệt đối không để support tự ý sửa nội dung riêng tư mà không được chủ thiệp ủy quyền.

**Health panel:** Link public 200/404, asset missing, storage errors, schema mismatch, lỗi RSVP, lỗi JS gần đây; "Sửa" dẫn tới trang nhiệm vụ thật, không phải nút tượng trưng.

## 13. Quản lý gói dịch vụ và quyền lợi

**Package fields:** ID, tên, slug, mô tả, đơn vị thời hạn (theo tháng/theo dự án/trọn đời tùy chính sách), giá niêm yết, tiền tệ, thuế nếu áp dụng, trial, status, sort, highlight, policies. Entitlements không chỉ là chuỗi mô tả marketing: `max_projects`, `max_published_invitations`, `max_guests`, `max_storage_mb`, `custom_domain`, `remove_branding`, `advanced_analytics`, `guest_import`, `personalized_links`, `team_members`, `rsvp_reminders`, `checkin_enabled`, `priority_support`, ...

**Luồng:** tạo nháp → cấu hình quyền → xem trước so sánh gói → publish; sửa giá nên tạo phiên bản giá mới, không hồi tố đơn đã thanh toán. Có mapping mua gói → entitlement snapshot → lifecycle; downgrade/expiry phải có policy bảo vệ dữ liệu và lịch thông báo, tránh làm biến mất thiệp ngay trong ngày cưới.

## 14. Đơn hàng, giao dịch, thanh toán và đối soát

**Order:** order_no, user/workspace, plan/sku, gross/discount/tax/net, currency, payment provider, payment method, status `pending/paid/failed/cancelled/refunded/partially_refunded`, created/paid timestamps, invoice status. Phân biệt order, payment attempt, transaction và webhook event.

**Chi tiết đơn:** timeline thời gian, phương thức, mã giao dịch, request-id, callback đã xác minh hay chưa, quyền gói được cấp, coupon, lịch sử hoàn tiền, thông báo đã gửi. Admin tài chính có thể bấm "Đối soát lại" (idempotent), "Gửi lại biên nhận" có log, "Hoàn tiền" theo vai trò/chính sách và đối soát provider.

**Chống lỗi tiền:** Không tin redirect trình duyệt là đã thanh toán. Chỉ cấp quyền qua server verification/webhook có xác thực, idempotency key, unique transaction reference, kiểm số tiền/currency/order, xử lý callback đến trễ/trùng/thứ tự đảo. Không giữ thông tin thẻ nhạy cảm.

## 15. Coupon, chiến dịch giá và hoa hồng

- Coupon code unique, loại `%`/số tiền, mức giảm tối đa, chi tiêu tối thiểu, áp dụng gói nào, số lượt tổng/mỗi tài khoản, ngày hiệu lực, được cộng dồn hay không.
- Theo dõi tạo/dùng/huỷ/đơn hoàn và lạm dụng coupon; chỉ cộng số lượt khi điều kiện đơn thỏa theo quy tắc.
- Agency/affiliate (P2): referrer, attribution window, order liên quan, commission state (pending/approved/paid/reversed), xuất đối soát; không tự nhận tỷ lệ hoa hồng nếu chưa được cấu hình.

## 16. CMS, nội dung SEO và marketing

- Quản lý trang tĩnh (`Giới thiệu`, `Bảng giá`, `Hướng dẫn`, `Liên hệ`, `Chính sách`); slug, title, description, OG image, canonical, schema type và trạng thái publish.
- Blog có category/tag, tác giả, cover, rich content được sanitize, draft/scheduled/published, mục lục, liên kết nội bộ, preview, lịch sử chỉnh sửa.
- Trang danh mục mẫu và trang chi tiết mẫu có metadata riêng, hình preview, breadcrumbs, sitemap, robots và canonical đúng. Chống index các trang studio/dashboard, link preview riêng tư, query filter vô hạn.
- Redirect 301/302 quản lý có vòng lặp detection; liên kết gãy, sitemap health, media alt, ảnh tối ưu.
- Banner/campaign có vị trí/đối tượng/thời gian, preview và thống kê click; hỗ trợ tắt khẩn.
- Form leads: tên/email/điện thoại/nhu cầu/consent, status, người phụ trách, ghi chú, nguồn; chống spam, export theo quyền.

## 17. Support, ticket và kiểm duyệt

### 17.1. Ticket Center

Trường: ticket_no, source (web/email/in-app), user/workspace/project, type, severity, title, content, attachments, assignee, status, SLA_due_at, first_response_at, resolved_at, tags. Luồng `new → triaged → in_progress → waiting_customer → resolved → closed`, có reopen. Cung cấp canned response có kiểm soát, nội bộ note khác phản hồi gửi khách, mention đồng nghiệp, lịch sử xử lý và liên kết bug/code release.

### 17.2. Quyền hỗ trợ an toàn

Chế độ "Xem dưới góc nhìn khách" (nếu cần) phải có explicit approval, phiên giới hạn, banner rõ, log mọi thao tác và hạn chế dữ liệu tài chính/riêng tư; không giả danh âm thầm. Có công cụ yêu cầu khách tự chia sẻ screenshot/log chẩn đoán đã ẩn thông tin nhạy cảm.

### 17.3. Kiểm duyệt

Quản lý các lời chúc/ảnh/chữ ký bị báo cáo theo case, cờ lý do, xử lý ẩn/hiển thị/xóa theo chính sách, xem trước an toàn, khiếu nại. Không tự công khai lời chúc riêng tư trên website marketing. Quyền chủ thiệp duyệt lời chúc của họ trước khi public được ưu tiên.

## 18. Job Center, Email/SMS/Zalo và tình trạng hệ thống

- Job queue: loại job (resize ảnh, import khách, gửi email, tạo QR, build template, report), batch ID, người tạo, số phần tử, hoàn thành/thất bại/đang chạy, retry count, started/completed, lỗi rút gọn.
- Có filter failed/stuck/scheduled, action retry/dừng theo permission và idempotency; không restart mù gây gửi trùng hàng loạt.
- Email center: template, lịch gửi, provider status, delivered/bounced/failed (khi provider hỗ trợ), throttling, unsubscribe/consent, log đã che PII.
- SMS/Zalo: chỉ tích hợp qua nhà cung cấp hợp lệ và quyền gửi thực tế; trạng thái gửi theo callback, hạn mức/cost, template đã duyệt, không khẳng định gửi thành công khi mới enqueue.
- Cron health, worker queue delay, storage, DB, CDN, lỗi 5xx, cache, backup gần nhất; cảnh báo rõ mức độ. Không công khai stack trace hoặc key cho support không có quyền.

## 19. Báo cáo, analytics và xuất dữ liệu

- Báo cáo acquisition: nguồn traffic → view template → create → preview → publish → paid.
- Báo cáo content: mẫu có nhiều view nhưng ít lượt dùng, retention theo cohort, tỷ lệ drop-off Studio.
- Báo cáo revenue: gross, discount, refund, net, số đơn paid, AOV; nếu có subscription mới đo MRR/churn.
- Báo cáo RSVP: tổng số mời, link đã gửi, khách mở, xác nhận có/không/chưa phản hồi, headcount, theo từng sự kiện.
- Date filter và timezone nhất quán; ghi định nghĩa KPI ngay bên cạnh icon giải thích. Export theo quyền và audit dữ liệu truy xuất.

## 20. Cấu hình platform và tính an toàn khi thay đổi

Màn cài đặt tách tab: Thương hiệu & SEO; Mẫu mặc định; Gói & Billing; Payment; Email; SMS/Zalo; Storage/CDN; Security; Content moderation; Consent/Privacy; Feature flags; API/Webhook; Sao lưu. Mỗi nhóm có help text, validation, nút **Kiểm tra kết nối** thật và **Lưu thay đổi** hiển thị diff.

- Secret chỉ hiển thị masked, có quyền rotate, không đưa xuống HTML/JS.
- Thay payment/notification provider phải test sandbox và có audit.
- Feature flag có phạm vi toàn hệ thống, gói, workspace; ưu tiên default-off cho thử nghiệm.
- Có rollback cho config quan trọng, version và backup chính sách, tránh một lần Save gây downtime toàn nền tảng.

---

# PHẦN IV — CLIENT, STUDIO VÀ HÀNH TRÌNH TẠO THIỆP

## 21. Đăng ký, đăng nhập và onboarding

### 21.1. Client login/register

- Login email/điện thoại nếu backend hỗ trợ, password, show/hide, nhớ thiết bị theo policy, quên mật khẩu, xác minh email.
- Register tên, email, password + confirm, đồng ý điều khoản/chính sách cần thiết; không mặc định tick marketing consent.
- Validation inline, trường hợp email tồn tại, password yếu, rate limit, liên kết xác thực hết hạn; thông báo rõ mà không làm lộ trạng thái tài khoản trong các luồng nhạy cảm.
- Mẫu form có phong cách thiệp cưới tiết chế; prefix login `mayhen_login_`, register `mayhen_register_`; responsive desktop/tablet/mobile.
- Trang admin login riêng prefix `mayhen_admin_login_`; không dùng chung role redirect tuỳ tiện; có 2FA cho role nhạy cảm.

### 21.2. Onboarding sau đăng ký — wizard từng bước

1. **Bạn muốn làm gì?** Tạo thiệp cưới của mình / tạo cho người thân / quản lý nhiều khách (wedding planner).
2. **Chọn mẫu:** Filter, preview thật mobile/desktop, xem các tính năng template.
3. **Thông tin cặp đôi:** cô dâu/chú rể, cách hiển thị, có thể bỏ qua thông tin chưa biết.
4. **Thời gian và địa điểm:** nhiều lễ/tiệc, giờ, địa chỉ, bản đồ, timezone, lịch.
5. **Ảnh & nhạc:** có thể upload sau, dùng ảnh demo không người nếu cần và được quyền.
6. **Chỉnh sửa Studio:** nhập nội dung, xem preview, kiểm tra thiếu dữ liệu.
7. **Xuất bản:** review checklist, chọn URL/visibility, link chia sẻ và hướng dẫn bước tiếp theo.

Lưu draft mỗi bước, tiến trình hoàn thành %, resume từ bước cuối; không bắt mua gói trước khi người dùng biết họ đang mua cái gì trừ tính năng premium.

## 22. Client Dashboard — màn hình quan trọng nhất sau đăng nhập

- Header cặp đôi, ngày cưới, countdown **ở dashboard** (không ép vào template khác), trạng thái thiệp.
- Thẻ hành động đầu tiên: `Tiếp tục chỉnh thiệp`, `Xem thiệp`, `Sao chép link`, `Mời khách`, `Xem RSVP`.
- Checklist tiến độ: thông tin cặp đôi, giờ/địa điểm, ảnh, RSVP, kiểm tra link, publish, gửi 10 khách đầu tiên. Mỗi item click đúng phần cần sửa.
- Tóm tắt khách: đã mời, chưa mở, đã xem, có mặt, từ chối, chưa trả lời; hiển thị số lượng từ dữ liệu thật.
- Cảnh báo có ý nghĩa: "Thiệp đang nháp — khách chưa thể xem", "Địa điểm tiệc chưa có bản đồ", "Gói hết hạn trước ngày cưới".
- Nhiều dự án: switcher rõ, không lẫn dữ liệu; có quyền archive hoặc duplicate project.

## 23. Studio — trình chỉnh sửa thiệp phải thực sự mạnh

### 23.1. Bố cục Studio

```text
┌──────────────────────────────────────────────────────────────────────────────┐
│ Quay lại | Tên thiệp • Đã lưu | Undo Redo | Mobile Tablet Desktop | Preview │
│                            [Lưu bản nháp] [Xuất bản]                       │
├────────────────────┬───────────────────────────────────┬─────────────────────┤
│ Panel trái         │       VÙNG XEM TRƯỚC THẬT        │ Inspector / Tùy chọn│
│ 1. Tổng quan       │    thiệp khớp template công khai  │ Thuộc tính mục chọn │
│ 2. Cặp đôi         │    responsive, tương tác thật      │ màu/font/ảnh nếu có │
│ 3. Gia đình        │    hoặc iframe embed             │ schema cho phép    │
│ 4. Lễ & tiệc       │                                   │                     │
│ 5. Nội dung        │                                   │                     │
│ 6. Ảnh / Album     │                                   │                     │
│ 7. Nhạc / Video    │                                   │                     │
│ 8. RSVP            │                                   │                     │
│ 9. Lời chúc / Ký tên│                                  │                     │
│10. QR / Quà mừng   │                                   │                     │
│11. Cài đặt thiệp   │                                   │                     │
├────────────────────┴───────────────────────────────────┴─────────────────────┤
│ Trạng thái: Đã lưu lúc 15:42 | Lỗi validation | Hướng dẫn / Bàn phím tắt   │
└──────────────────────────────────────────────────────────────────────────────┘
```

Mobile Studio dùng panel dạng bước và preview riêng, không cố nhồi 3 cột. Trên desktop, panel có thể co giãn, vị trí preview không bị zoom sai.

**Phân biệt thiết kế và hiện trạng:** Sơ đồ trên là các nhóm chức năng sản phẩm, không thay thế thứ tự 10 section canonical của schema hiện tại. Khi thiết kế panel thực tế, phải lập bảng ánh xạ từng nhóm tới section/field tương ứng và tuân thủ hợp đồng template; không ép đổi dữ liệu hoặc ID section chỉ theo nhãn trong sơ đồ. Mã hiện tại chuyển mẫu bằng reload sau khi lưu dữ liệu tạm và tạo thiệp mới với `is_published = true`; luồng nháp/xuất bản riêng, lịch sử phiên bản server và chuyển mẫu không reload là yêu cầu mục tiêu cần triển khai và kiểm thử, chưa phải chức năng đã nghiệm thu.

### 23.2. Tính năng bắt buộc

- **Live sync:** sửa input tới đâu phản ánh mọi occurrence của field trên thiệp tới đó, kể cả hai vị trí tên hoặc ngày.
- **Autosave:** debounce, chỉ gửi changed fields, hiển thị Saving/Saved/Failed; không đánh dấu saved trước khi backend xác nhận. Khi mất mạng, dữ liệu chưa sync được thông báo; khôi phục khi quay lại, chống ghi đè im lặng.
- **Undo/Redo:** lịch sử thao tác phía client, có giới hạn; sau reload sử dụng version history server nếu có. Nêu rõ thay đổi nào có thể undo.
- **Version history:** autosave revision/bản xuất bản, ai sửa, timestamp, diff, restore tạo revision mới (không xóa lịch sử).
- **Preview:** đóng/mở, mobile 320/375/390/430/480, desktop, giả lập dữ liệu khách, nhạc mặc định tắt trong Studio, chống click RSVP thật khi preview.
- **Quản lý section:** show/hide, reorder chỉ khi template schema cấp quyền; với clone source-locked giữ vị trí và style gốc, không ép reorder tự do.
- **Chỉnh chữ:** text plain / multiline / rich limited nếu schema cho phép; không cho chèn script/HTML độc hại.
- **Chỉnh ảnh:** upload, crop focal point, preview before save, replace, alt text, restore default; các slot phải được định nghĩa trong schema.
- **Chỉnh nhạc:** upload file được phép, giới hạn kích thước/định dạng, nghe thử, bật tắt mặc định sau tương tác khách, không giả autoplay.
- **Chỉnh QR:** tạo QR từ dữ liệu tài khoản đã được khách chủ động nhập, xem trước, che thông tin nhạy cảm với admin không có quyền.
- **Thiết lập quyền riêng tư:** thiệp công khai, chỉ link, mật khẩu/token theo gói; ẩn thiệp khỏi index theo quy tắc; kiểm tra preview private.
- **Publish workflow:** validation cần thiết → preview cuối → chọn URL → xác nhận → xuất bản → kiểm link public 200 → hiển thị URL/QR/share.

### 23.3. Quy tắc chống mất dữ liệu

- Không gắn listener lặp khi render/hot reload.
- Bật cảnh báo rời trang nếu dirty state chưa lưu; dùng concurrency version/ETag tránh người thứ hai ghi đè.
- Nếu 2 editor cùng sửa, hiển thị người sửa gần nhất, conflict dialog với diff hoặc khóa phiên tùy chính sách.
- Mọi field đều có trạng thái `idle/dirty/saving/saved/error`; save thất bại phải có retry.

## 24. Schema dữ liệu thiệp — chi tiết theo nhóm

| Nhóm | Trường điển hình | Loại / Validation / Hành vi |
|---|---|---|
| Cặp đôi | `groom_name`, `bride_name`, `couple_names` | Chuỗi, giới hạn độ dài, tự đồng bộ mọi occurrence |
| Gia đình | `groom_father`, `groom_mother`, `bride_father`, `bride_mother` | Tùy chọn; có cách xử lý khi thiếu thông tin |
| Ngày | `wedding_date`, `wedding_date_digits`, `wedding_day/month/year`, `wedding_lunar_date` | ISO date chuẩn; âm lịch xác thực; timezone rõ |
| Sự kiện | `events[]: type,title,date,time,timezone,venue,address,mapUrl,notes` | Repeatable; validate giờ/địa chỉ; ưu tiên event ID thay vì ghép chuỗi |
| Lịch | `calendar_month_year`, `wedding_calendar`, `countdown_target` | Sinh từ date thật, không hard-code |
| Media | `hero_photo_url`, `gallery[]`, `backgrounds[]`, `music_url`, `video_url` | Media ID / quyền sử dụng / crop / alt / thứ tự |
| Story | `story_title`, `story_items[]`, `thank_you_note` | Có tùy chọn ẩn mục |
| RSVP | `rsvp_enabled`, `rsvp_deadline`, `max_plus_ones`, `questions[]` | Deadline và giới hạn hợp lệ; điều khiển bật/tắt |
| Guestbook | `guestbook_enabled`, `signature_enabled`, `moderation_mode` | Hỗ trợ pre/post moderation |
| Gift | `gift_enabled`, `gift_accounts[]`, `qr_images[]` | Tách bên cô dâu/chú rể; không tự nhận thanh toán |
| Appearance | Các token **được template cho phép** | Source-locked clone không mở palette tùy tiện |
| SEO/Privacy | `slug`, `title`, `description`, `og_image`, `visibility` | Chặn slug trùng, ràng buộc quyền |

**Quan trọng:** File gốc quy định những key `data-sync-target` cụ thể và hai hàm `window.updateMayhenCountdown(targetDate)`, `window.renderMayhenWeddingCalendar(targetDate)`. Vẫn giữ contract này để tương thích; phần backend mới nên map vào field registry có type/version thay vì hard-code các ô HTML rời rạc.

## 25. Quản lý nhiều lễ/tiệc, địa điểm và lịch trình

- Tạo nhiều event với tên và loại khác nhau, ngày giờ, địa chỉ, tọa độ chỉ khi nhập/được xác minh, link mở Google Maps, ảnh địa điểm hợp lệ, đầu mối liên lạc theo consent.
- Event có thứ tự hiển thị, riêng tư theo nhóm khách, RSVP deadline riêng, sức chứa, check-in window.
- Trên Studio, mỗi event có card gồm ngày/giờ/địa điểm, nút duplicate, hide/show; trường hợp nhà trai/nhà gái dùng nhãn dễ hiểu.
- Khi thay đổi lịch cưới sau khi đã gửi thiệp, hiển thị cảnh báo "Khách đã được mời — có muốn thông báo lịch mới?"; nếu gửi, phải có xác nhận và log phân phối.
- Thời gian sử dụng cùng timezone `Asia/Ho_Chi_Minh` theo mặc định Mây Hẹn nhưng cấu hình được nếu sản phẩm mở rộng quốc tế.

## 26. Quản lý ảnh, album, video và file

- Media library có folder theo workspace, tìm theo tên/loại/ngày, upload drag-drop, multi-upload, tiến trình, tệp lỗi, retry; hỗ trợ thumbnail/preview.
- Validation MIME thực, magic bytes, dimensions, dung lượng theo gói; sanitize tên file và strip metadata nhạy cảm như GPS nếu phù hợp.
- Tạo các derivative ảnh phù hợp (WebP/AVIF khi hỗ trợ), giữ file gốc theo chính sách, focal crop, không tự ép 1:1 ảnh album/hero.
- Dedupe nội bộ theo hash khi phù hợp nhưng không tiết lộ blob của tenant khác; thao tác thay asset không phá URL thiệp.
- Trash/recovery có thời gian; dùng reference-count để không xóa ảnh đang được các phiên bản thiệp sử dụng.
- Video nhúng chỉ từ nguồn cho phép/được chủ thiệp cung cấp và được xử lý CSP phù hợp; không hotlink asset clone trái điều khoản.

## 27. Link thiệp, chia sẻ và QR

- Link public chuẩn slug dễ đọc; check unique, reserved words, thay slug có redirect nếu an toàn.
- Link cá nhân theo khách (`guest_invitation`) sử dụng token ngẫu nhiên đủ mạnh, có thể thu hồi/rotate, không dùng số điện thoại/email/ID tăng dần làm secret.
- Hỗ trợ QR cho link public và từng khách; download PNG/SVG khi có quyền, file in ấn với kích thước đủ dùng.
- Phân biệt nút `Copy link`, `Xem như khách`, `Chia sẻ`, `Tạo QR`; link riêng tư không hiển thị đầy đủ trong analytics cho người không có quyền.
- Tùy chọn "Ẩn khỏi Google" không đồng nghĩa bảo mật: nội dung nhạy cảm cần authorization/token; `noindex` chỉ là tín hiệu cho crawler.

---

# PHẦN V — KHÁCH MỜI, RSVP, CHECK-IN, SỔ KÝ TÊN VÀ HẬU SỰ KIỆN

## 28. Guest CRM — sổ khách mời chuyên nghiệp nhưng không rối

### 28.1. Bảng khách mời

Cột: tên hiển thị, nhóm, danh xưng, bên nhà trai/gái/khác (nếu có), số điện thoại/email được che tùy quyền, sự kiện được mời, số suất mời, link riêng, trạng thái gửi, trạng thái xem, RSVP, số người đến dự kiến, bàn, check-in, ghi chú, người phụ trách.

### 28.2. Thêm khách đơn lẻ

Form gồm tên, danh xưng, phone/email (tùy chọn), số người được mời, nhãn/nhóm, các sự kiện được mời, người đi cùng, điều kiện ăn uống (chỉ thu khi cần thiết), ghi chú nội bộ, chế độ cho phép khách chỉnh RSVP. Sau Save có CTA **Thêm tiếp**, **Sao chép link**, **Gửi thiệp**. Validate trùng tên + contact theo scope, không tự gộp hai người trùng tên.

### 28.3. Import CSV/XLSX theo 4 bước

1. Upload file, kiểm dung lượng/số dòng, preview 10–20 dòng, phát hiện header, kiểm sheet.
2. Mapping cột nguồn → tên/phone/email/group/events/guest_count/note; chọn bỏ qua cột; kiểm date/phone dạng text, tránh mất số 0.
3. Preview lỗi theo dòng (thiếu tên, dữ liệu sai, trùng theo quy tắc), chọn mode create/update/skip duplicates, xác nhận các thay đổi.
4. Submit → batchId, tiến trình queued/processing/completed/partial_failed/failed, thống kê tạo/sửa/bỏ qua/lỗi, tải file lỗi để sửa.

Không cần import lại toàn bộ nếu chỉ có vài lỗi. Import phải atomic theo row hoặc batch có kết quả rõ; có idempotency để retry không tạo trùng.

### 28.4. Nhóm và phân đoạn

Nhóm gợi ý: gia đình, họ hàng, bạn bè, đồng nghiệp, đối tác; chủ thiệp tự thêm nhãn; nhóm động như "Chưa xác nhận", "Đã mở nhưng chưa RSVP", "Cần nhắc", "Nhà trai", "Nhà gái", "Đi cùng trẻ nhỏ" khi có dữ liệu và được phép. Bulk assign event, tag, generate link, xuất danh sách theo quyền.

## 29. Tạo thiệp cá nhân hóa cho từng khách

- Mỗi guest có invitation token, tên lời chào, event scope, số suất được phép, deadline, ngôn ngữ thiệp nếu hỗ trợ.
- Khi mở link riêng: hiển thị lời mời đúng tên; không để token người A cho phép xem/chỉnh RSVP người B; có khả năng revoke token.
- Thiệp công khai vẫn hoạt động khi chủ thiệp không bật cá nhân hóa; không buộc nhập số điện thoại để xem thiệp.
- Có nút **Sao chép lời mời** với template được chủ thiệp chỉnh, ví dụ `Thân mời {guest_name} ... {invitation_url}`. Placeholder được validate, không vô ý công khai thông tin cả danh sách.

## 30. Gửi lời mời và quản lý phân phối

- Từ danh sách chọn một/nhiều khách → chọn kênh (copy thủ công, email, SMS/Zalo nếu provider được cấu hình và đã có consent) → preview từng tin nhắn merge field → xác nhận → gửi theo job.
- Trạng thái theo *delivery attempt*: `draft/queued/sent/delivered/failed/unknown`, riêng `opened` là hành vi link thiệp, không coi như provider delivered. Nhiều attempt cho một khách nhưng không tăng số khách mời.
- Job hàng loạt có rate limit, retry có kiểm soát, opt-out, suppress list, lỗi số điện thoại/email; dashboard hiển thị tổng thực sự gửi và danh sách lỗi.
- Nút gửi lại chỉ cho phép đối tượng thích hợp; tự động nhắc phải có consent/cadence, giới hạn số lần và khả năng tắt.

## 31. RSVP — xác nhận tham dự có dữ liệu đúng

### 31.1. Form phía khách

- Tên khách (tự điền nếu link cá nhân và cho phép sửa theo rule).
- Đến tham dự / Không thể tham dự / Chưa chắc (nếu cấu hình cho phép).
- Event được mời, số người dự kiến, người đi cùng, lời nhắn, câu hỏi bổ sung (ví dụ ăn chay) khi chủ thiệp bật.
- Ràng buộc số lượng không vượt `max_invited_count` hoặc chính sách +1; deadline; input validation, double-submit, mạng yếu.
- Khách chỉnh lại RSVP bằng cùng link khi chủ thiệp cho phép; lưu revision để biết thay đổi, hiển thị xác nhận sau API success thật.
- Nếu RSVP công khai không có token, cần chống spam, moderation và chính sách chống giả mạo; không giả định danh tính khách.

### 31.2. Dashboard chủ thiệp

KPI: số khách được mời, đã xem, đã phản hồi, có/không/chưa chắc/chưa trả lời, tổng người dự kiến, số người từ chối, tỷ lệ phản hồi. Filter theo event/group/channel/status; mở từng khách thấy timeline. Hỗ trợ chỉnh trạng thái thủ công có ghi actor/lý do và cờ nguồn `manual` để phân biệt khách tự trả lời.

### 31.3. Quy tắc xử lý

- Một lời xác nhận có thể chứa nhiều guest event responses: không đặt một status chung khiến tiệc nhà trai và tiệc nhà gái ghi đè nhau.
- Đếm headcount khác số người trả lời. Nếu 1 khách xác nhận mang 3 người thì `response_count=1`, `attendance_headcount=3`.
- Không xóa lịch sử khi khách đổi "Có" → "Không"; báo cáo hiện hành đọc latest effective response.
- RSVP đã trễ hạn hiện thông báo đúng deadline, tránh gửi data vô hạn nếu không có exception.

## 32. Sơ đồ bàn tiệc / Seating Plan

- Tạo khu vực (sảnh A, phòng VIP), bàn số/tên, loại bàn, sức chứa, vị trí x/y tương đối, nhóm ưu tiên.
- Danh sách khách bên trái, sơ đồ bàn bên phải, kéo thả bằng Pointer Events **hoặc** chọn bàn bằng dropdown để dùng trên mobile/accessibility.
- Tự động kiểm quá sức chứa, khách đã xếp bàn, người đi cùng cần ngồi cùng; cảnh báo không đủ suất. Không tự xếp người "đã từ chối" trừ khi chủ thiệp xác nhận.
- Tính `assigned/unassigned/overflow` theo headcount, không chỉ số dòng guest; seat assignment theo event.
- Export sơ đồ in A4/PDF nếu triển khai, danh sách theo bàn, theo tên; có phiên bản trước ngày cưới để khôi phục.
- Thay đổi phút chót có log, check-in staff nhận cập nhật; offline sync nếu có phải có conflict policy.

## 33. Check-in ngày cưới

- Cho phép nhân viên được cấp quyền dùng camera điện thoại quét QR **nếu thiết bị/trình duyệt hỗ trợ**, hoặc nhập mã/tìm tên. Không bắt buộc dùng thư viện JS với template public; check-in dashboard có thể dùng API và camera/browser hỗ trợ thực tế.
- Màn hình tối giản: ô tìm/khung quét, tên khách, event, bàn, số người, nút **Xác nhận đã đến**, thông báo rõ; chống quét trùng với nút cho phép điều chỉnh có log.
- QR mã hóa token/mã tham chiếu, server kiểm thuộc đúng event và quyền staff; không chứa PII plaintext trong QR.
- Có trạng thái `not_checked_in/checked_in/partially_checked_in`, timestamp, staff, thiết bị, số người thực tế; ghi event id để không nhầm tiệc.
- Hỗ trợ tìm nhanh khi camera không được cấp quyền; lỗi mạng phải báo rõ, không đánh dấu check-in thành công giả. Offline mode chỉ P2 và phải có queue/conflict reconciliation thực.

## 34. Lời chúc, sổ lưu bút và chữ ký điện tử

### 34.1. Lời chúc

- Khách nhập tên + lời chúc, có thể gắn guest link; giới hạn độ dài, chống spam, rate limit, CSRF/anti-bot phù hợp.
- Chủ thiệp chọn publish tự động hoặc duyệt trước. Admin client xem danh sách `pending/visible/hidden/rejected`, filter theo ngày, từ khóa, người gửi; preview text an toàn, không `innerHTML` dữ liệu thô.
- Có tính năng ghim, ẩn, trả lời riêng nếu sản phẩm hỗ trợ; export lời chúc để lưu niệm theo quyền.

### 34.2. Signature Canvas

- Có Pointer Events cho mouse/touch/pen, hỗ trợ DPR, clear/undo khi có thể, preview và xuất PNG/WebP; không mất nét khi resize.
- Không có ảnh người/ảnh cặp đôi/stock có người ở section RSVP và chữ ký; phải theo chuẩn file clone gốc.
- Chữ ký lưu theo session/guest (nếu có), consent, moderation status, media metadata; backend verify upload và virus/type.
- Không tuyên bố lưu thành công trước khi server trả về result; có trạng thái lỗi/retry.

### 34.3. Guestbook nâng cao

- Lọc theo lời chúc/chữ ký/ảnh; export bản lưu niệm (P1/P2) có template riêng không mạo danh screenshot clone.
- Kiểm soát quyền công khai: người gửi đồng ý hiển thị tên/lời chúc hay chỉ gửi riêng chủ thiệp; quyền yêu cầu xóa.

## 35. Quà mừng / QR ngân hàng — tính năng nhạy cảm

- Chủ thiệp có thể bật/tắt QR nhận quà; lưu nhiều tài khoản với nhãn nhà trai/nhà gái, chủ tài khoản, ngân hàng, số tài khoản, QR. Hiển thị công khai **chỉ khi chủ thiệp chủ động bật**.
- Khi sửa tài khoản ngân hàng, yêu cầu xác nhận mạnh/notification tùy policy; lưu audit thay đổi, bảo vệ chống account takeover và hành vi thay QR.
- Mây Hẹn **không** được kết luận khách đã chuyển khoản chỉ vì đã bấm QR; nếu có payment integration cho quà, phải tách khỏi chuyển khoản trực tiếp và chứng minh qua provider.
- Admin platform không có quyền mặc định đọc thông tin tài khoản ngân hàng đầy đủ; cần masking và role theo nguyên tắc least privilege.

## 36. Kế hoạch cưới và hậu sự kiện (P1/P2, tách khỏi thiệp clone)

- Checklist chuẩn bị theo ngày: đặt địa điểm, chụp ảnh, gửi thiệp, chốt khách, chốt bàn, diễn tập, thanh toán nhà cung cấp.
- Mỗi task có người phụ trách, due date, priority, status, file đính kèm, reminder.
- Budget planner: ngân sách dự kiến, khoản chi, thực chi, hóa đơn/ghi chú, category; là tính năng nội bộ client, không mặc định công khai.
- Vendor contacts: đơn vị cung cấp, hợp đồng/timeline, thanh toán, ghi chú; không tự nhận có marketplace nếu chưa triển khai.
- Sau sự kiện: lưu thiệp thành trang kỷ niệm theo lựa chọn, ẩn RSVP, lưu/export guestbook, download danh sách và xóa dữ liệu theo chính sách.

---
# PHẦN VI — KIẾN TRÚC, DỮ LIỆU, API VÀ BẢO MẬT

## 37. Kiến trúc tổng quan đề xuất

```text
Browser / Mobile Browser
  ├─ Marketing website (Blade, SSR ưu tiên SEO)
  ├─ Client dashboard + Studio (Laravel Blade + Vanilla JS theo module)
  ├─ Super Admin (Laravel Blade + Vanilla JS theo module)
  └─ Public Invitation (Blade + Native HTML/CSS/JS, source-locked)
                  │
             HTTPS / WAF
                  │
             Laravel App
  ├─ Authentication / Authorization / Policies
  ├─ Workspaces / Memberships / Weddings
  ├─ Templates / Versions / Schema Renderer
  ├─ Studio Autosave / Revisions / Publish
  ├─ Guests / RSVP / Guestbook / Check-in
  ├─ Orders / Payments / Entitlements
  ├─ Media / Storage / Assets / CDN
  ├─ CMS / SEO / Reports / Support
  └─ API + Signed Webhook Handlers
        │              │               │
    SQL Database    Redis Cache      Object Storage
        │           + Queue/Lock      + Image workers
        └── Background Workers / Scheduler / Notifications
```

Đây là **kiến trúc đề xuất**, không mặc định cho rằng dự án hiện có các queue/provider/storage tương ứng. Các driver phải được cấu hình qua environment và kiểm thử trong môi trường thực tế. Nếu hệ thống đang có backend khác, tạo compatibility adapter thay vì tự đổi toàn bộ route/model hoặc đập bỏ logic hoạt động.

### 37.1. Quy tắc code / module boundaries

- Tách `Admin`, `Client`, `PublicInvitation`, `Studio`, `Payment`, `Media`, `Support` thành module logic rõ ràng (theo conventions Laravel thực tế).
- Controller nhẹ, request validation tách riêng, policy và service xử lý nghiệp vụ, repository/query layer khi thực sự cần, jobs cho tác vụ lâu.
- Không truy cập database trực tiếp từ view. Không đặt logic thanh toán hoặc quyền trong JS.
- Mọi query dữ liệu client có `workspace_id/project_id` scope và policy server-side; tránh IDOR.
- Event handlers và webhook dùng idempotency, transaction, unique constraints; tác vụ dài không giữ HTTP request.
- Dữ liệu được cấp phép cho preview và trang khách phải qua DTO/serializer đã giới hạn field, không dump toàn bộ model.

## 38. Mô hình dữ liệu — từ tên bảng đến trách nhiệm

### 38.1. Các bảng lõi

| Table | Trường cốt lõi | Ghi chú |
|---|---|---|
| `users` | id, name, email, phone?, password_hash, email_verified_at, status, last_seen_at | Không lưu plaintext password |
| `roles`, `permissions`, `role_permissions` | id, code, name, scope, ... | Role platform và workspace tách scope |
| `workspaces` | id, owner_id, name, status, plan_snapshot?, created_at | Multi-tenant |
| `workspace_members` | workspace_id, user_id, role_id, status, invited_by, accepted_at | Unique theo workspace/user |
| `wedding_projects` | id, workspace_id, title, timezone, status, wedding_date | Container dự án |
| `wedding_people` | project_id, role, full_name, display_name, family fields | Hoặc value objects nếu schema thực hỗ trợ |
| `wedding_events` | id, project_id, type, title, starts_at, ends_at?, venue, address, map_url, capacity?, rsvp_deadline_at | Nhiều sự kiện |
| `templates` | id, name, slug, category_id, designer_id, status, price_rule, published_version_id | Catalog |
| `template_versions` | id, template_id, semver, storage_path, manifest_hash, schema_json, qa_status, published_at | Immutable publish |
| `template_assets` | id, version_id, path, sha256, mime, rights_status, size | Theo version |
| `invitations` | id, project_id, template_version_id, slug, visibility, status, published_revision_id | Link public |
| `invitation_revisions` | id, invitation_id, revision_no, data_json, actor_id, published_at?, created_at | Snapshot + audit |
| `invitation_sections` | invitation_id, section_key, enabled, sort_order, settings_json | Chỉ khi template cho phép |
| `media_assets` | id, workspace_id, storage_key, mime, width, height, byte_size, sha256, deleted_at | File workspace |
| `guests` | id, project_id, display_name, phone?, email?, side?, notes?, status | PII: hạn chế quyền |
| `guest_groups`, `guest_group_members` | id, project_id, name; guest_id, group_id | Labels/groups |
| `guest_event_invitations` | guest_id, event_id, invited_count, access_token_hash, status | Số suất theo event |
| `invitation_deliveries` | id, guest_id, event_id?, channel, attempt, status, provider_ref, sent_at | Không trộn với opened |
| `rsvp_responses` | id, guest_id?, event_id, invitation_id, response, attendance_count, answers_json, source, submitted_at | Lịch sử thay đổi |
| `guestbook_entries` | id, project_id, guest_id?, name, message, signature_asset_id?, visibility, moderation_status | XSS-safe |
| `seat_areas`, `seat_tables`, `seat_assignments` | event_id, area, table_code, capacity, guest_id, headcount | Theo sự kiện |
| `checkins` | id, event_id, guest_id, count, checked_in_at, staff_id, source | Unique policy chống trùng |
| `plans`, `plan_prices`, `entitlements` | id, code, price, currency, billing_cycle, key, value | Giá có version |
| `orders`, `payments`, `payment_events`, `refunds` | order_no, amount, state, provider_ref, payload_hash,... | Tách order / attempt / event |
| `subscriptions`/`workspace_entitlements` | workspace_id, plan_id, effective_at, expires_at, snapshot_json | Không quyết định chỉ từ UI |
| `coupons`, `coupon_redemptions` | code, type, value, usage_limit, order_id | Race-safe |
| `support_tickets`, `ticket_messages` | requester, assignee, severity, status, body, visibility | Internal vs public notes |
| `cms_pages`, `blog_posts`, `seo_redirects` | slug, locale, status, metadata | SEO |
| `notification_templates`, `notification_jobs`, `notification_attempts` | channel, consent, template_version, state | Có audit gửi |
| `audit_logs` | actor_type/id, target_type/id, action, before/after redacted, ip?, created_at | Append-only theo policy |
| `settings`, `feature_flags`, `webhook_events` | key, scope, encrypted_value?, status, event_id | Secrets không lộ |
| `import_batches`, `import_batch_rows` | batch_id, created_by, status, counts, error | Import chạy nền |
| `analytics_events`/aggregate tables | scope, event_code, anonymous_token?, timestamp, metadata allowed | Chỉ track dữ liệu cần thiết |

### 38.2. Ràng buộc bắt buộc

- Unique `(workspace_id, slug)` hoặc `(domain, slug)` theo chính sách route, đảm bảo không trùng URL.
- Unique `(template_id, semver)`; `template_versions.published` bất biến.
- `guest_event_invitations` unique `(guest_id, event_id)`; `rsvp` là history với latest được xác định theo effective timestamp/version.
- `payment_events` unique `(provider, provider_event_id)`; `payments` unique provider transaction ID khi có.
- `access_token_hash` lưu hash token dùng tra cứu/verify, không lưu link token plaintext trong log/analytics nếu không cần.
- Soft delete và foreign key được thiết kế trước; không cho cascade từ user xóa toàn bộ giao dịch tài chính trái chính sách.
- Monetary values dùng decimal/đơn vị nguyên nhỏ nhất phù hợp provider, không dùng floating-point để cộng tiền.
- Tất cả timestamps trong DB nên lưu UTC, format khi hiển thị sang `Asia/Ho_Chi_Minh`; tránh dùng `date` làm `datetime` không chủ đích.

### 38.3. Hướng dẫn model mở rộng dữ liệu Studio

`InvitationRevision.data_json` chỉ chứa dữ liệu đã validate theo `TemplateVersion.schema_json`, không nhét toàn bộ model user vào JSON. Nếu một template có field lặp như `events[]` và `gallery[]`, mỗi item cần stable ID để tránh Studio reorder ghi đè nhầm. Thông tin được dùng làm nghiệp vụ quan trọng (ngày cưới/event/guest) nên thuộc bảng có cấu trúc, Studio field tham chiếu các entity này bằng mapper một chiều hoặc hai chiều có quy tắc rõ; không tạo hai nguồn sự thật không đồng bộ.

## 39. API contract mẫu — phải điều chỉnh theo route thật

> Tất cả endpoint dưới đây là **đề xuất route**, không khẳng định đã tồn tại. Dự án thực tế phải xác minh route, DTO, CSRF, auth, policy, response shape và lỗi trước khi viết client.

| Method | Endpoint đề xuất | Tác dụng | Phân quyền |
|---|---|---|---|
| GET | `/api/client/projects` | Danh sách dự án được phép truy cập | workspace member |
| POST | `/api/client/projects` | Tạo project | owner/editor có quyền |
| GET | `/api/client/projects/{id}` | Chi tiết project | member scoped |
| GET | `/api/client/projects/{id}/events` | Danh sách event | member scoped |
| POST | `/api/client/projects/{id}/events` | Tạo event | project edit |
| GET | `/api/client/invitations/{id}/studio` | Schema + values + revision | invitation edit |
| PATCH | `/api/client/invitations/{id}/draft` | Autosave từng patch với `expectedRevision` | edit |
| POST | `/api/client/invitations/{id}/publish` | Publish sau validation | publish |
| POST | `/api/client/invitations/{id}/restore/{revision}` | Restore thành revision mới | edit |
| GET | `/api/client/projects/{id}/guests` | Filter/sort/pagination | guest view |
| POST | `/api/client/projects/{id}/guests/import` | Tạo import job | guest import |
| GET | `/api/client/import-batches/{batchId}` | Progress + result | batch owner |
| POST | `/api/client/guests/{id}/invitation-link` | Sinh/rotate guest link | guest manage |
| GET | `/api/client/projects/{id}/rsvp-summary` | KPI RSVP từng event | rsvp view |
| GET | `/api/client/projects/{id}/seating` | Sơ đồ bàn | seating view |
| PATCH | `/api/client/projects/{id}/seating` | Bulk seat operations có version | seating edit |
| POST | `/api/client/events/{id}/checkins` | Check-in | checkin create |
| POST | `/api/public/invitations/{slug}/rsvp` | RSVP khách | public + anti-abuse |
| POST | `/api/public/invitations/{slug}/guestbook` | Lời chúc/chữ ký | public + anti-abuse |
| GET | `/api/admin/dashboard` | KPI tổng hợp | admin analytics |
| GET | `/api/admin/users` | Query tài khoản | user view |
| GET | `/api/admin/templates` | Filter template | template view |
| POST | `/api/admin/templates/import` | Kiểm ZIP và tạo draft | template create |
| POST | `/api/admin/template-versions/{id}/publish` | Publish version | template publish |
| GET | `/api/admin/orders` | Danh sách đơn | finance view |
| POST | `/api/admin/payments/{id}/reconcile` | Đối soát server | finance reconcile |
| POST | `/api/webhooks/payments/{provider}` | Callback đã xác minh | signature auth |
| GET | `/api/admin/jobs` | Jobs/progress | operations view |
| GET | `/api/admin/audit-logs` | Audit timeline | audit view |

### 39.1. Response và lỗi thống nhất

```json
{
  "success": true,
  "data": { "id": "example-id", "revision": 7 },
  "meta": { "requestId": "example-request-id" }
}
```

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_FAILED",
    "message": "Vui lòng kiểm tra lại thông tin.",
    "fields": { "wedding_date": ["Ngày cưới chưa hợp lệ."] },
    "requestId": "example-request-id"
  }
}
```

Không phản hồi exception trace hoặc cấu hình nhạy cảm. HTTP status chính xác: `401` chưa đăng nhập, `403` không có quyền, `404` không thuộc scope/không tồn tại, `409` revision conflict, `422` validation, `429` rate-limit, `5xx` lỗi server; lỗi không làm mất draft trên client.

## 40. Event, queue, automation và idempotency

**Domain events đề xuất:** `UserRegistered`, `WorkspaceCreated`, `WeddingCreated`, `InvitationDraftSaved`, `InvitationPublished`, `GuestImported`, `InvitationDeliveryQueued`, `InvitationDeliveryUpdated`, `RSVPSubmitted`, `RSVPChanged`, `GuestbookEntrySubmitted`, `GuestCheckedIn`, `OrderPaidVerified`, `SubscriptionExpiring`, `TemplatePublished`, `SystemJobFailed`.

- Sau publish thiệp: invalidate cache URL/manifest, warm asset nếu có, ghi audit và thông báo chủ thiệp; nếu publish rollback được phải có revision.
- Sau RSVP: cập nhật aggregate/reports, gửi notification có opt-in, tránh double count khi retry.
- Sau thanh toán xác minh: cấp quyền **một lần**, tạo receipt/notification qua outbox nếu cần; job gửi email lỗi không rollback giao dịch paid.
- Nhắc RSVP: scheduler chọn khách chưa phản hồi theo deadline/timezone và consent; tạo dedupe key `(project,event,guest,campaign_step)`.
- Chia batch lớn: giới hạn throughput, retry exponential backoff, dead-letter và dashboard xử lý thất bại.
- Mỗi job có `idempotency_key`, `attempts`, `last_error_code`, `trace_id`; retry không tạo 2 QR/2 charge/2 tin gửi nếu provider hỗ trợ xác nhận idempotency.

## 41. Privacy, an ninh và tuân thủ

### 41.1. Mô hình dữ liệu nhạy cảm

- PII: tên khách, số điện thoại, email, lời chúc, danh sách bàn, lịch trình riêng, ảnh cá nhân, chữ ký, số tài khoản nhận quà.
- Phân quyền cụ thể, encrypt-at-rest cho trường cần thiết theo threat model; TLS trên đường truyền; secrets quản lý ngoài source.
- Không log toàn bộ RSVP, query token, tài khoản ngân hàng, upload raw hoặc OTP vào hệ thống log. Analytics không thu định danh quá mức cần thiết.
- Chỉ dùng ảnh/font/nhạc khi có quyền; ghi nguồn và giấy phép, tránh nhập trái phép tài nguyên có cơ chế bảo vệ.

### 41.2. An ninh ứng dụng

- CSRF cho form/session, chống XSS bằng escaping/sanitize, CSP phù hợp, chống SSRF nếu import URL nguồn, malware/file checks, ZIP bomb/Zip Slip limits.
- Rate limit đăng nhập, gửi lời chúc, RSVP, tạo link, tạo QR, thanh toán; có bot protection nhưng không làm khó khách mời thực.
- Reset password với token ngắn hạn single-use; email verify; session invalidation; 2FA nhân sự quan trọng.
- Restrict file path khi template import; template code chưa duyệt không chạy cùng quyền server production.
- Hạn chế quyền Laravel file storage. Các file riêng dùng signed access hoặc gateway authorized; CDN private không được public-bucket nhầm.
- Đảm bảo không có IDOR: user A truy cập `/projects/{id}` của B nhận 404/403 theo chính sách, kể cả export, media, audit, guest check-in.

### 41.3. Data lifecycle & privacy

- Có màn xem/quản lý quyền chia sẻ; privacy notice và consent cho việc gửi lời mời qua nhà cung cấp.
- Người dùng được export/xóa dữ liệu trong phạm vi chính sách; giải thích dữ liệu nào cần giữ vì nghĩa vụ giao dịch và retention.
- Gói hết hạn: quy tắc grace period, archive, read-only, data retention và khôi phục phải minh bạch trước khi thanh toán.
- Không đặt thời hạn retention chung nếu pháp lý/chính sách kinh doanh chưa quyết; phải có trường cấu hình và tư vấn pháp lý phù hợp thị trường triển khai.

## 42. Hiệu năng, SEO, accessibility và quality gates

### 42.1. Mục tiêu kỹ thuật đề xuất (cần benchmark thực)

- TTFB/INP/LCP/CLS được đo bằng Lighthouse/Web Vitals trên trang khách thiệp công khai; mục tiêu định lượng chỉ chốt sau baseline, tránh cam kết "100% SEO" hoặc "luôn 60 FPS".
- Ảnh media lazy-load, responsive sizes, asset cache có fingerprint, tải font nội bộ với `font-display` phù hợp; nhạc không preload toàn bộ khi chưa cần.
- Frontend template native không React/Vue/Angular/Tailwind/Bootstrap/jQuery/GSAP/CDN theo file gốc.
- Dashboard nhiều dữ liệu dùng server pagination, query index, aggregate snapshots; export lớn chạy queue không block request.
- `prefers-reduced-motion`, keyboard navigation, focus management, semantic markup, alt, contrast và touch targets.

### 42.2. SEO rõ ràng theo từng vùng

- Landing pages, blog, danh mục mẫu và chi tiết mẫu: crawlable, canonical đúng, structured data phù hợp và có nội dung thực.
- Login/Register/Admin/Studio/Preview private/RSVP private: `noindex` và kiểm quyền cho trang cần private. **Không dựa vào robots.txt/noindex để bảo vệ dữ liệu nhạy cảm**.
- Thiệp công khai theo cấu hình chủ thiệp: indexed hoặc noindex; ảnh preview OG chủ thiệp kiểm soát và không lộ asset riêng khi thiệp private.
- 301 khi đổi slug public có kiểm soát; sitemap không đưa draft/private URL.

### 42.3. Khả năng phục hồi

- DB backup có lịch, test restore trên môi trường riêng, theo dõi thành công/thất bại.
- Deploy có migration strategy, health check, queue drain, rollback app/release; không rollback schema phá data.
- Storage backup theo rủi ro thực tế, kiểm file corruption và asset references.
- Trước ngày cưới nên có phương án status page và graceful degraded mode: thiệp vẫn xem được khi service gửi email đang lỗi, RSVP báo chính xác khi backend unavailable.

---

# PHẦN VII — HÀNH VI THEO MÀN HÌNH: CHÍNH XÁC ADMIN PHẢI BẤM ĐƯỢC GÌ

## 43. Screen contract 01 — Trang quản lý mẫu thiệp

**Header:** `Kho mẫu thiệp` / phụ đề `Tạo, kiểm thử, phân loại và xuất bản các mẫu thiệp.` / CTA `+ Tạo mẫu` và `Nhập ZIP`.  
**KPI:** Tổng mẫu | Đang công khai | Chờ duyệt | Lỗi QA.  
**Toolbar:** search tên/slug/ID, filter `Trạng thái`, `Danh mục`, `Gói`, `Người thiết kế`, `QA`; sort `Mới cập nhật`; `Cột hiển thị`; `Xuất`.  
**Bảng:** thumbnail / tên + ID / danh mục / giá/gói / sử dụng / phiên bản / QA / trạng thái / cập nhật / actions.  
**Row click:** detail có 8 tab như mục 10.4.  
**Menu row:** Xem trước, Chỉnh sửa nháp, Tạo phiên bản, Kiểm QA, Publish/Unpublish theo permission, Duplicate, Archive.  
**Modal publish:** checklist asset/schema/QA/rights, thay đổi version, người duyệt, tác động tới thiệp đang dùng, nút confirm.  
**Empty state:** `Chưa có mẫu thiệp nào` + `Tạo mẫu đầu tiên`; filter không kết quả `Không tìm thấy mẫu` + `Xóa bộ lọc`.  
**Acceptance:** Không publish nếu manifest quan trọng thiếu asset hoặc quyền chưa được xác minh theo policy; publish thành công có URL preview và audit; rollback không sửa file version cũ.

## 44. Screen contract 02 — Trang quản lý tài khoản

**Header/CTA:** `Khách hàng` / `+ Tạo tài khoản` nếu admin được phép; mặc định khách tự đăng ký.  
**KPI:** Tổng, mới 30 ngày, chưa xác thực, đang khóa.  
**Filter:** status/verified/plan/registration channel; search email/name/phone/user ID.  
**Bảng:** user, contact masked, gói, số thiệp, trạng thái, verified, last login, created, actions.  
**Detail drawer:** Hồ sơ, workspace, thiệp, payment, tickets, history.  
**Actions:** Khóa tài khoản, mở lại, gửi email xác minh, revoke sessions, đổi role có kiểm soát.  
**Acceptance:** Nếu khóa vì rủi ro thì ghi reason và notification; không lộ hashed password/token; admin thường không dùng `impersonate` nếu chưa có approval.

## 45. Screen contract 03 — Dự án cưới và thiệp

**Header:** `Thiệp & sự kiện`.  
**Tabs:** Toàn bộ | Bản nháp | Đã xuất bản | Hết hạn | Cần xử lý.  
**Bảng:** mã thiệp, chủ sở hữu, mẫu + version, ngày cưới, lượt mở, RSVP, trạng thái, ngày xuất bản, domain.  
**Detail:** Preview (public/test), data health, user/workspace, logs, reports, support case links.  
**Actions:** View, check broken links, gửi hướng dẫn, flag/restrict có lý do, export diagnostic.  
**Acceptance:** Không sửa nội dung khách khi chỉ đang kiểm tra sự cố; status `suspended` và `archived` khác nhau; audit đầy đủ.

## 46. Screen contract 04 — Gói và đơn hàng

**Trang gói:** Bảng Free/Basic/Pro/Agency do admin cấu hình tùy thời điểm (tên chỉ ví dụ), so sánh quyền lợi dynamic, duplicate plan/version; xem số customer mỗi gói.  
**Trang đơn:** Search order_no/user/transaction, date range, amount, status, payment provider; export.  
**Chi tiết:** Order items, pricing snapshot, payment attempts, webhook events, entitlement, invoice, refund timeline.  
**Actions:** Verify/reconcile, resend receipt, refund theo policy, ghi chú nội bộ.  
**Acceptance:** Không có thao tác nút "Đã thanh toán" thủ công chỉ sửa status để cấp quyền nếu chưa qua quy trình tài chính có kiểm tra; không sinh giao dịch duplicate.

## 47. Screen contract 05 — Ticket và hỗ trợ

**Header:** `Trung tâm hỗ trợ` + badge việc cần xử lý; các tab `Của tôi`, `Chưa nhận`, `Quá hạn`, `Đã đóng`.  
**Danh sách:** ticket no, subject, customer, project, severity, status, assignee, last response, SLA.  
**Detail 2 cột:** thread khách hàng và internal notes; context user/plan/thiệp, linked errors; upload attachment có scan, reply templates.  
**Actions:** Assign, priority, reply, internal note, request more info, resolve, reopen.  
**Acceptance:** Internal notes không bị gửi cho khách; nhắc SLA; lịch sử không bị xóa khi đóng ticket.

## 48. Screen contract 06 — Job Center

**Header:** `Công việc nền` / filter trạng thái, loại, thời gian, workspace, batch ID.  
**Danh sách:** task, owner, progress bar `done/total`, queued/start/end, status, retry, error snippet.  
**Chi tiết:** timeline, sanitized error, rows success/fail, download error report, retry selective; đối với task gửi tin phải cảnh báo trùng.  
**Acceptance:** Refresh không mất batch name/progress; job hoàn thành không còn spinner; permission đúng với tenant và platform.

## 49. Screen contract 07 — Analytics

**Header:** `Báo cáo` / date range + compare / `Xuất báo cáo`.  
**Subtabs:** Tăng trưởng, Hành trình tạo thiệp, Mẫu, Giao dịch, RSVP, Vận hành.  
**Mỗi chart:** tiêu đề, chú giải định nghĩa, đơn vị và khoảng thời gian, loading/no-data/error.  
**Drill-down:** click các cohort/series khi có dữ liệu để mở list filter, không hiển thị dữ liệu thống kê hoàn toàn giả.  
**Acceptance:** Gross/revenue/refund nhất quán giữa dashboard và report, cùng timezone/date filter và điều kiện payment status.

## 50. Screen contract 08 — Thiết lập hệ thống

**Cột trái:** danh mục thiết lập, trạng thái cấu hình, icon cảnh báo.  
**Panel phải:** giải thích từng input, masked secret, `Test connection`, hiển thị diff trước Save, audit và rollback config.  
**Trường nhạy cảm:** gateway keys, email credentials, object storage, signing keys chỉ được nhập/rotate theo role; không trả secret value qua GET.  
**Acceptance:** Test connection thất bại không làm gián đoạn provider đang hoạt động; Save không xóa secret nếu form để trống.

## 51. Screen contract 09 — Client tạo thiệp

**Trang chọn mẫu:** thumbnail thực, xem thử nhanh, các filter, free/paid, tính năng rõ, nút `Dùng mẫu này`.  
**Wizard:** Bước 1 cặp đôi; 2 gia đình; 3 event; 4 ảnh; 5 lời nhắn; 6 review; 7 publish. Có `Bỏ qua bước này` cho trường không bắt buộc.  
**Khi publish:** phải có đủ date/venue cần thiết tùy template, asset xác minh, URL không trùng, mọi field required hợp lệ.  
**Sau publish:** dẫn tới screen chia sẻ với copy URL, QR, xem như khách, tạo danh sách khách đầu tiên.  
**Acceptance:** Reload wizard vẫn giữ draft; import mẫu cũ không làm mất thông tin đã nhập; validation dẫn đúng field lỗi.

## 52. Screen contract 10 — Client quản lý khách

**Header:** `Khách mời` / `+ Thêm khách` / `Nhập Excel`.  
**KPI:** tổng, chưa gửi, đã mở, đã RSVP, headcount dự kiến, chưa xếp bàn.  
**Toolbar:** search tên/phone, filter event/group/RSVP/send/checkin, bulk actions; import results.  
**Bảng:** guest name, group, events, invite quota, RSVP, opened, table, checkin, actions.  
**Detail drawer:** hồ sơ, link riêng, timeline gửi, câu trả lời, lời nhắn, bàn; action gửi lại/chỉnh RSVP thủ công (có log).  
**Acceptance:** Import 1000 dòng không khóa tab, xuất file lỗi có số dòng gốc, retry không nhân bản khách.

## 53. Screen contract 11 — Client RSVP và Seating

**RSVP dashboard:** cards KPI, donut/bar status nếu thật, filter event, list đã thay đổi gần đây; export danh sách tham dự chốt.  
**Seating:** danh sách chưa phân bàn + sơ đồ bàn có capacity, drag/drop và select fallback; hiển thị overflow; nút `Lưu sơ đồ` và lịch sử thay đổi.  
**Acceptance:** Khách tăng số người đi cùng làm bàn vượt capacity có cảnh báo; di chuyển khách không làm check-in nhầm event.

## 54. Screen contract 12 — Client Guestbook và QR

**Guestbook:** tab Pending/Published/Hidden; preview lời chúc + chữ ký, approve/hide, export theo quyền; có toggle duyệt trước, privacy notice.  
**QR:** danh sách tài khoản nhận quà bên cô dâu/chú rể, add/edit/delete, upload QR hoặc generate khi được hỗ trợ; xem trước hiển thị khách; xác nhận khi đổi số tài khoản.  
**Acceptance:** Chữ ký PNG lưu được từ điện thoại; bản QR public chỉ xuất hiện khi owner bật; cảnh báo không xác nhận chuyển tiền chỉ nhờ scan QR.

---

# PHẦN VIII — CÁC LUỒNG NGHIỆP VỤ END-TO-END

## 55. Luồng A — Khách từ landing page tới thiệp xuất bản

1. Khách mở mayhen.vn → danh mục mẫu → xem mẫu demo, chuyển mobile/desktop.
2. Nhấn `Dùng mẫu này` → nếu chưa đăng nhập thì register/login; lưu template intent để sau đăng nhập không mất lựa chọn.
3. Tạo wedding project hoặc chọn dự án hiện có → kiểm tra entitlement trước khi tạo nếu đã chạm hạn mức.
4. Studio khởi tạo từ version mẫu đang publish và demo values trung tính, upload media thật nếu có.
5. Nhập ít nhất thông tin cần thiết, autosave có revision. Khách xem preview và chỉnh sửa.
6. Nhấn Publish → validator trả danh sách field thiếu, lỗi URL/quota/assets; lỗi dẫn tới đúng panel cần sửa.
7. Publish tạo public revision, resolve URL, test access → phản hồi thật. Không nói publish thành công nếu URL hỏng.
8. Screen chia sẻ → copy/QR/tạo khách; project status và dashboard tự cập nhật.
9. Người xem public chỉ thấy các thông tin owner đã publish, không thấy dữ liệu draft/chưa được chủ thiệp duyệt.

## 56. Luồng B — Import khách và gửi thiệp cá nhân hóa

1. Chủ thiệp upload XLSX → preview/mapping → xem lỗi/dedupe → confirm.
2. Job import chạy và hiển thị batch progress; có danh sách lỗi và tải lại file chỉ chứa dòng lỗi.
3. Chủ thiệp filter `Chưa có link` → tạo guest token theo scope, không duplicate.
4. Chọn nhóm `Đồng nghiệp` → Preview lời mời chứa `{guest_name}` và `{invitation_url}` → gửi kênh hợp lệ.
5. Delivery attempts cập nhật trạng thái; callback/provider error hiển thị riêng.
6. Khách mở link: cá nhân hóa tên; click xác nhận; trả lời cho event được mời.
7. RSVP trả về API success; báo cáo headcount thay đổi, chủ thiệp nhận notification theo cài đặt.
8. Nếu khách đổi ý, lịch sử retained; seating cập nhật cảnh báo theo số suất mới.

## 57. Luồng C — Ngày cưới: chốt khách, chia bàn và check-in

1. Guest Manager mở RSVP tab, filter `Đã xác nhận Có` theo event và xuất danh sách.
2. Tạo bàn và giới hạn sức chứa, gán khách/nhóm hoặc người đi cùng; kiểm overflow.
3. Staff được cấp check-in role sự kiện; link công cụ có thời hạn khi thích hợp, không chia mật khẩu owner.
4. Khi quét QR hoặc tìm tên, server kiểm guest/event. Nhấn `Đã đến` → ghi count, staff, timestamp.
5. Quét lại cùng guest nhận cảnh báo đã check-in; có nút điều chỉnh với lý do/permission.
6. Dashboard ngày cưới phản ánh số người thực đến theo event, không thay RSVP ban đầu như một chuyện đã xảy ra.
7. Sau tiệc, khóa check-in, export và archive theo chính sách.

## 58. Luồng D — Admin thêm mẫu clone từ website tham chiếu

1. Admin nhập URL, tên/slug và chứng cứ quyền tài nguyên; thao tác khảo sát chỉ dùng nội dung truy cập hợp lệ.
2. Kiểm DOM/CSS/screenshot/network nhiều viewport; tạo `reference-audit.md` có mức tin cậy observed/measured/estimated.
3. Thu thập asset được phép, ghi manifest, không hotlink/không đóng gói asset thiếu quyền.
4. Tạo Blade/template CSS/JS theo chuẩn `mayhen_invitation_`, không dùng thư viện frontend ngoài.
5. Tích hợp dữ liệu Studio, preview embed `$isEmbed`, RSVP, signature, calendar/countdown; không phá visual của template.
6. Chạy QA source/local side-by-side; báo thiếu permission/asset và sai khác; không tuyên bố 100% nếu không xác minh.
7. ZIP source đã test extraction → import thành `template_version` nháp → validation/security → publish.
8. Mẫu mới lên thư viện theo danh mục và quyền gói; khi khách chọn phải biết version nào được gắn.

## 59. Luồng E — Mua gói và cấp quyền

1. Client xem trang so sánh gói có điều kiện/giới hạn rõ → chọn plan/price version.
2. Backend tạo pending order và payment attempt, amount ở server.
3. Điều hướng payment provider theo cấu hình thực. Trình duyệt quay về trang chờ xác minh (không set `paid` từ URL callback frontend).
4. Backend verify webhook/signature, số tiền, status, transaction unique; update payment/order bằng transaction/idempotency.
5. Tạo entitlement snapshot và thời hạn; client dashboard cập nhật quyền.
6. Thanh toán pending lâu/failed hiện retry/contact support, không cấp quyền sai; thanh toán trùng xử lý đúng.
7. Refund/revoke sau này theo policy, không xóa dữ liệu khách bất ngờ.

## 60. Luồng F — Admin xử lý ticket mất ảnh thiệp

1. Chủ thiệp gửi ticket kèm URL/ảnh minh họa → ticket linked invitation.
2. Support nhìn thấy mẫu/version, ảnh asset missing, trace error đã sanitize, quyền sở hữu.
3. Nếu asset thật bị thiếu, chuyển ticket cho template designer/operations; không truy cập trái phép ảnh riêng tư.
4. Tạo version hotfix hoặc sửa manifest theo quy trình; review và deploy; test thiệp của user có bị ảnh hưởng.
5. Support đóng ticket khi đã verify public URL và gửi phản hồi có thông tin rõ; ticket lưu timeline và release liên quan.

## 61. Luồng G — Chủ thiệp đổi ngày cưới sau khi đã gửi thiệp

1. Editor sửa ngày event trong Studio; hệ thống hiển thị "Bạn đã gửi lời mời đến N khách, thay đổi ngày sẽ ảnh hưởng đến nội dung".
2. Save draft thay vì đổi ngay public nếu sản phẩm áp dụng publish revisions; preview calendar/countdown dùng ngày mới.
3. Publish bản sửa → URL cũ vẫn dùng, dữ liệu mới được đồng bộ; nếu có cache cần invalidate.
4. Hiển thị tùy chọn gửi thông báo cập nhật tới những khách từng nhận lời mời, không tự gửi mặc định.
5. Gửi theo consent/notification logs; status delivered/failed là theo provider. RSVP cũ được giữ hoặc yêu cầu re-confirm theo cấu hình được người dùng phê duyệt.

---

# PHẦN IX — KIỂM THỬ, NGHIỆM THU, ROADMAP VÀ BÀN GIAO

## 62. Ma trận kiểm thử tối thiểu

| Case ID | Tình huống | Điều kiện pass |
|---|---|---|
| AUTH-01 | Đăng ký + email verify | Account tạo hợp lệ; không access protected trước verify nếu policy yêu cầu |
| AUTH-02 | Login sai mật khẩu nhiều lần | Rate limit/feedback an toàn, không lộ tài khoản |
| AUTH-03 | Role guest manager xem payment | 403; UI không hiện CTA; API cũng chặn |
| AUTH-04 | User A đoán ID project của B | Không đọc/sửa/export được, kể cả API/media |
| TMP-01 | Import ZIP thiếu file | Validation chặn publish, báo chính xác file thiếu |
| TMP-02 | ZIP chứa `../` path | Reject; không giải nén ra ngoài vùng an toàn |
| TMP-03 | Asset từ nguồn chưa được cấp quyền | Manifest `requires-permission`, không gắn quyền giả |
| TMP-04 | Template clone đối chiếu 375 và 1440 | QA report thật; không tự nhận pixel perfect nếu thiếu screenshot |
| TMP-05 | Editor preview `$isEmbed=true` | Không opening, không khóa scroll, không autoplay, không blank |
| TMP-06 | Source-locked template publish bản mới | Không ngầm sửa tất cả thiệp đang dùng bản cũ |
| STU-01 | Thay tên trong Studio | Mọi occurrence hiển thị đúng; reload vẫn giữ |
| STU-02 | Save mất mạng | Có `Save failed`, retry; không báo Saved sai |
| STU-03 | Hai editor sửa cùng revision | Trả `409`, hiện resolve conflict; không ghi đè im lặng |
| STU-04 | Thay ngày cưới | Calendar + countdown cập nhật, đúng leap year/timezone |
| STU-05 | Preview RSVP | Không gửi vào dữ liệu production ngoài chủ đích |
| GUE-01 | Import Excel số phone có 0 đầu | Không mất số 0; mapping/preview đúng |
| GUE-02 | Retry import batch | Không tạo khách trùng không kiểm soát |
| GUE-03 | Token khách A truy cập data B | Access denied; không lộ thông tin B |
| RSVP-01 | Khách 1 người mời 3 suất trả lời 3 | Response count 1, headcount 3 |
| RSVP-02 | Khách đổi RSVP 2 lần | Lịch sử đủ; report dùng response mới nhất |
| RSVP-03 | RSVP sau deadline | Chặn/cho phép theo policy, thông báo đúng |
| SIGN-01 | Ký trên iPhone/Android/mouse | Canvas không nhảy tọa độ, export đúng |
| SEAT-01 | Xếp quá capacity bàn | Cảnh báo rõ và không âm thầm lưu sai |
| CHECK-01 | Quét QR hai lần | Không tăng double check-in; có log |
| PAY-01 | Redirect thành công nhưng webhook chưa verify | Order vẫn pending; không cấp plan |
| PAY-02 | Webhook payment gửi lại | Entitlement chỉ cấp 1 lần |
| PAY-03 | Callback amount mismatch | Không đánh dấu paid, lưu case đối soát |
| JOB-01 | Worker fail batch gửi thiệp | Show failed rows, retry selective; không double-send |
| CMS-01 | Draft/private invitation SEO | Không đưa vào sitemap, không index khi cấu hình |
| SEC-01 | Input lời chúc chứa script | Không thực thi script; nội dung sanitize/escape |
| SEC-02 | Thay QR ngân hàng | Confirmation/audit/permission theo policy |
| MEDIA-01 | Xóa ảnh đang dùng trong thiệp publish | Không làm gãy ảnh hoặc bị chặn theo policy |
| OPS-01 | DB backup + restore thử | Có log chứng minh khôi phục được trên staging |
| UI-01 | 320×720, 390×844, 1440×900 | Không tràn ngang, CTA dùng được, form rõ |
| UI-02 | Keyboard-only Studio/admin | Focus hợp lý, modal đóng được, form submit được |

## 63. Acceptance Definition of Done — theo từng tính năng

Một module chỉ được đánh dấu **Done** khi đồng thời:

1. Đã có màn danh sách/chi tiết/form phù hợp, responsive và có microcopy rõ.
2. API/backend thực sự hoạt động, validate cả client lẫn server, có permission và tenant scope.
3. Trạng thái loading/empty/error/success xử lý đúng; hành động không bị bấm hai lần ngoài chủ đích.
4. Có test happy path, permission, failure, concurrency/idempotency nếu liên quan.
5. Có logs/audit cho tác vụ quan trọng; không lộ bí mật/PII.
6. Docs chỉ rõ endpoint/DB migration/config cần thiết, cùng cách kiểm tra.
7. Đã QA trên mobile/tablet/desktop; không console error.
8. Có đường truy cập từ menu/CTA thực; không phải màn hình mockup đứng độc lập.
9. Một người dùng mới có thể tự thao tác theo hướng dẫn trực quan, không cần đọc tài liệu kỹ thuật.
10. Không làm hỏng mẫu thiệp gốc hoặc chức năng đã hoạt động.

## 64. Các giai đoạn triển khai — tránh xây tràn lan và nửa vời

### Sprint Group 0 — Discovery & Foundation

- Khảo sát codebase, route và DB hiện tại. Kiểm tra chuẩn giao diện đã có; không giả định framework/contract chưa được cung cấp.
- Chốt mô hình tenant, roles, template version, asset, invitation, RSVP; viết migration/ERD và skeleton route.
- Xây design system admin, auth, policies, errors, UI components (table, modal, drawer, form, date, toast, upload, pagination) native hoặc stack đã phê duyệt.
- Vẽ clickable user flow từ chọn mẫu đến publish và RSVP trước khi chia ticket.

### Sprint Group 1 — MVP có thể phát hành thiệp

- Marketing/gallery mẫu/preview, client onboarding, user/workspace.
- Template manager và renderer, Studio cơ bản với autosave + preview + publish.
- Media upload, project/event, URL public, calendar/countdown, RSVP, guestbook/signature cơ bản.
- Admin dashboard, tài khoản, template, thiệp và lỗi; QA end-to-end.

### Sprint Group 2 — Nghiệp vụ vận hành sản phẩm

- Guest CRM + import/export, personalized links, RSVP reporting, message templates.
- Plan + order/payment verification + entitlements, support ticket, audit logs, email notifications.
- Template versioning/QA pipeline, job center, CMS/SEO, monitoring/backup.

### Sprint Group 3 — Wedding Operations & Scale

- Seating/check-in, analytics nâng cao, automation nhắc RSVP, team collaboration, bản lưu niệm.
- Agency/partner portal, campaign/affiliate/coupon, tuỳ chọn tùy biến Studio theo schema.
- Tối ưu scale/media/CDN/queue, usability testing và xử lý bottleneck.

**Không triển khai đồng thời tất cả module nếu đội ngũ nhỏ.** Mỗi nhóm phải tạo ra luồng hoàn chỉnh, có thể demo bằng dữ liệu thật và đủ test.

## 65. Bàn giao mong muốn cho AI/developer — không chỉ đưa hình đẹp

Khi được giao implement một module, bắt buộc cung cấp:

- Kiểm kê code/DB/route sẵn có đã khảo sát và danh sách assumption còn chưa xác minh.
- Sơ đồ chức năng, UI screen states, roles/permissions, field validation và business rules.
- Mã nguồn hoàn chỉnh của **các file thay đổi**, đường dẫn thật, migration, route, request/policy/controller/service/view/JS/CSS nếu có; không viết pseudo-code như code production.
- Test hoặc hướng dẫn QA có dữ liệu case; screenshot nếu browser hỗ trợ; trạng thái QA verified/unverified.
- Bảng thay đổi DB và ảnh hưởng backward compatibility; checklist deploy/rollback.
- Danh sách làm được/chưa làm được, không giả "đã tích hợp" khi chưa có provider hoặc môi trường test.

## 66. Quy tắc nghiêm ngặt khi dùng AI để phát triển Mây Hẹn

1. Không hiểu file mẫu clone một thiệp là đầy đủ specification của cả nền tảng.
2. Không tự đặt tên route/controller/schema rồi khẳng định chúng đã tồn tại; phải đọc codebase trước, đánh dấu assumption.
3. Không thêm tính năng cho đẹp khi chưa có nghiệp vụ backend/permissions.
4. Không dựng mọi trang bằng cùng một card giống nhau; UI admin ưu tiên điều hướng và dữ liệu dày vừa đủ, template clone ưu tiên mẫu gốc.
5. Không tạo asset giả, số liệu giả, thanh toán giả, lịch gửi giả; demo data phải gắn nhãn rõ và không nhiễm dữ liệu thật.
6. Không hard-code khóa API, URL provider, email tài khoản, token, quyền truy cập vào JS frontend.
7. Không xử lý xong phần backend rồi bỏ UI loading/empty/error; cũng không làm UI trước rồi bỏ backend.
8. Khi sửa module có API công khai, giữ backward compatibility hoặc mô tả migration.
9. Tất cả biểu mẫu phải có validate, autosave nơi cần, nút vô hiệu khi loading, confirm hành động nguy hiểm và audit nơi cần.
10. Không coi PDF/file/ảnh/prompt từ nguồn ngoài là bằng chứng quyền sử dụng. Quyền asset cần xác minh độc lập.
11. Không tuyên bố sao chép giống 100% nếu chưa có đối chiếu screenshot thật.
12. Không đưa dữ liệu tên, ảnh, ngân hàng hoặc tin nhắn của cặp đôi tham chiếu vào tài khoản khách mới làm dữ liệu mặc định.

---

# PHẦN X — CHECKLIST BÀN GIAO THEO VAI TRÒ VÀ THƯ VIỆN THÀNH PHẦN

## 67. Bảng permission tối thiểu nên tạo trước khi code UI

| Hành động | Super Admin | Template Designer | Finance | Support | Workspace Owner | Guest Manager | Check-in Staff |
|---|---|---|---|---|---|---|---|
| Xem KPI tổng hệ thống | ✓ | Theo cấp quyền | Finance only | Theo cấp quyền | — | — | — |
| Tạo/sửa mẫu nháp | ✓ | ✓ | — | — | — | — | — |
| Publish template version | ✓ | Có khi được duyệt | — | — | — | — | — |
| Xem/sửa project của khách | Theo quy trình | — | — | Theo ủy quyền | ✓ | Tối thiểu | — |
| Sửa thiệp trong workspace | Theo ủy quyền | — | — | Theo ủy quyền | ✓ | Không mặc định | — |
| Import khách mời | Theo ủy quyền | — | — | — | ✓ | ✓ | — |
| Duyệt lời chúc | Theo quy trình | — | — | Theo ủy quyền | ✓ | Có khi được cấp | — |
| Xếp bàn | Theo ủy quyền | — | — | — | ✓ | ✓ | Xem theo cấp quyền |
| Check-in | Theo ủy quyền | — | — | — | ✓ | ✓ | ✓ |
| Xem giao dịch | ✓ theo role | — | ✓ | Giới hạn | Của workspace | — | — |
| Hoàn tiền | Role riêng | — | Có quyền | — | — | — | — |
| Xem audit hệ thống | ✓ | Chỉ phần mình | Finance scope | Ticket scope | Workspace scope | — | — |
| Rotate secret/provider | Role hệ thống riêng | — | — | — | — | — | — |

`✓` thể hiện **quyền đề xuất**, không đồng nghĩa luôn bật sẵn cho mọi nhân viên. Khi triển khai dùng policy và permission code, không chỉ lấy bảng tĩnh làm bảo mật.

## 68. Component/UI toolkit Admin cần có

- `AdminShell`, `SidebarNav`, `Topbar`, `Breadcrumb`, `PageHeader`, `WorkspaceSwitcher`.
- `KpiCard`, `ChartPanel`, `DateRangePicker`, `FilterBar`, `SavedFilterViews`, `DataTable`, `ColumnSettings`, `BulkActionBar`, `Pagination`.
- `SearchInput`, `DebouncedSelect`, `TagInput`, `DateTimeField`, `CurrencyField`, `MediaPicker`, `MapLinkInput`, `RichTextLimited`.
- `FormWizard`, `FormField`, `ValidationSummary`, `ModalConfirm`, `DetailDrawer`, `Toast`, `EmptyState`, `ErrorState`, `Skeleton`.
- `FileUploader`, `ImportColumnMapper`, `ImportProgress`, `RetryFailedRows`, `ExportJobProgress`.
- `PermissionGate` chỉ điều khiển UI (backend vẫn kiểm tra), `AuditTimeline`, `StatusTimeline`, `ChangeDiff`, `RevisionHistory`.
- `InvitationPreviewFrame`, `SchemaFieldEditor`, `MediaCropper`, `RsvpStats`, `GuestStatusBadge`, `SeatingCanvas`, `CheckinSearchScanner`.

### 68.1. Contract mẫu cho một component bảng

- Nhận `columns`, `rows`, `rowKey`, `loading`, `error`, `pagination`, `sort`, `filters`, `permissions`, `selection`, callbacks.
- Cột có `id`, `title`, `dataType`, `sortable`, `filterable`, `width`, `pin`, `hidden`, `formatter`; tránh truyền HTML string không tin cậy.
- Thao tác bulk phải dùng stable IDs, không dựa index hiện tại; khi filter/page đổi phải reset/giải thích selection.
- Khi backend trả 409 hoặc 422 phải chỉ ra record hoặc field thất bại, không toast "Lỗi không xác định" nếu đã có message.

## 69. Ngôn ngữ trạng thái nhất quán

| Đối tượng | Trạng thái nội bộ | Nhãn tiếng Việt |
|---|---|---|
| Invitation | draft / published / paused / archived | Bản nháp / Đang hiển thị / Tạm dừng / Lưu trữ |
| Template | draft / review / published / archived | Nháp / Chờ duyệt / Công khai / Ngừng sử dụng |
| RSVP | attending / declined / maybe / pending | Sẽ tham dự / Không tham dự / Chưa chắc / Chưa phản hồi |
| Delivery | queued / sent / delivered / failed / unknown | Chờ gửi / Đã gửi / Đã nhận / Thất bại / Chưa rõ |
| Payment | pending / paid / failed / refunded | Chờ xác minh / Thành công / Thất bại / Đã hoàn tiền |
| Import | queued / processing / completed / partial_failed / failed | Trong hàng đợi / Đang xử lý / Hoàn tất / Hoàn tất một phần / Thất bại |
| Guestbook | pending / visible / hidden / rejected | Chờ duyệt / Đang hiển thị / Đã ẩn / Từ chối |
| Support | new / in_progress / waiting_customer / resolved / closed | Mới / Đang xử lý / Chờ khách / Đã giải quyết / Đã đóng |

Các label này thuộc UI tiếng Việt; backend có thể dùng tên khác nếu đã có schema thật. Phải có mapping tường minh, không bịa trạng thái không tồn tại.

## 70. Một ngày làm việc mẫu của Super Admin — bài thử usability

**08:30:** Mở dashboard → thấy 3 mẫu chờ duyệt, 2 ticket quá SLA, 1 webhook lỗi.  
**08:32:** Click KPI "Mẫu chờ duyệt" → danh sách được filter sẵn → mở mẫu → kiểm demo mobile/desktop + asset + QA → approve → publish; quay lại danh sách vẫn giữ filter.  
**08:40:** Click ticket quá SLA → chi tiết có project/mẫu/version/link lỗi; assign operations, note nội bộ, phản hồi cho khách mà không lộ note.  
**08:50:** Vào giao dịch lỗi → xem payment attempt và callback signature status → đối soát lại an toàn; không bấm chỉnh `paid` thủ công.  
**09:00:** Mở Analytics → xem số thiệp xuất bản 7 ngày, top template, funnel drop-off.  
**09:10:** Xuất báo cáo để họp, job gửi báo cáo có trạng thái thực.  

Nếu admin cần vào 5–6 màn để tìm một thao tác trên, hoặc không biết trạng thái của công việc, UX chưa đạt.

## 71. Một ngày của cô dâu/chú rể — bài thử usability

**Buổi sáng:** Vào dashboard, thấy "Thiệp đã xuất bản", nút Copy link và "20 khách chưa trả lời".  
**Trưa:** Thêm 10 khách từ Excel, tự biết dòng nào trùng, tạo link riêng và chia sẻ.  
**Chiều:** Thay giờ tiệc trên Studio, preview lập tức đổi lịch/countdown, Save, Publish bản sửa; hệ thống gợi ý thông báo tới khách cũ.  
**Tối:** Xem 12 RSVP mới, số người dự kiến và số bàn còn thiếu; gán bàn nhanh.  
**Ngày cưới:** Staff check-in bằng QR hoặc tìm tên; chủ thiệp nhìn được số người đến thực tế.  

Nếu các thao tác này buộc phải nhờ nhân viên hỗ trợ, sản phẩm chưa đủ dễ dùng.

## 72. Những thông tin PHẢI chốt trước khi code sản xuất

- Logo, font, palette chính thức **của giao diện Mây Hẹn** (không đồng nhất với mẫu clone).
- Hạ tầng đang có: Laravel version, auth, DB, Redis, queue, object storage, hosting/CDN.
- Contract Blade `client.layouts.invitation`, endpoint RSVP/Signature, cấu trúc schema Studio hiện hữu (nếu có).
- Danh sách gói thật, quyền lợi thật, chính sách hết hạn, provider thanh toán và chính sách hoàn tiền.
- Loại event bắt buộc, quy tắc mời thêm người và cách quản lý nhà trai/nhà gái.
- Chính sách quyền riêng tư, đồng ý gửi tin, retention và quyền asset/media/music.
- Ai có quyền publish template, duyệt lời chúc, sửa thiệp khách, xử lý refund.

**Không cần đợi chốt tất cả để bắt đầu phác thảo UI/DB**, nhưng không được đưa giả định chưa xác minh vào production như yêu cầu đã được phê duyệt.

## 73. YÊU CẦU CUỐI CÙNG CHO ĐƠN VỊ TRIỂN KHAI / AI AGENT

> Hãy dùng **PHẦN I–X** của tài liệu này như một **Product Requirement Specification** cho toàn nền tảng Mây Hẹn. Khi triển khai, ưu tiên luồng end-to-end và trang quản trị **dễ dùng, dữ liệu thật, thao tác thật**. Đối với **mẫu thiệp Hương Hoa Ngày Đính Ước**, dùng **PHỤ LỤC A** làm technical/visual acceptance contract gốc: quan sát đúng mẫu thực, giữ thiết kế/asset hợp pháp, triển khai native frontend, tích hợp Studio/RSVP/Signature mà không phá visual fidelity. Không tuyên bố module hoàn thành chỉ vì có form và nút bấm; mọi nút phải có nghiệp vụ, API, validation, permission, feedback và test thật.

---

# PHỤ LỤC A — MASTER PROMPT CLONE THIỆP CƯỚI (BẢN GỐC GIỮ NGUYÊN)

**Lưu ý phân định phạm vi:** Phần dưới đây là nội dung gốc của tài liệu đã cung cấp, được giữ nguyên để không mất hợp đồng clone nguồn. Các lệnh “không thêm section”, “không tự đổi màu” chỉ ràng buộc **bản clone source-locked**; chúng không cấm bổ sung màn hình quản trị/khả năng vận hành bên ngoài thiệp.

---
# MASTER PROMPT — CLONE THIỆP CƯỚI MÂY HẸN CHUẨN MẪU GỐC

> **Phiên bản:** 2.0 — Visual Fidelity First / Reference-Locked Implementation  
> **Mục tiêu:** Tái dựng giao diện, hình ảnh, màu sắc, font, viền, kích thước, khoảng cách, chuyển động và hành vi của trang tham chiếu với độ trung thực cao nhất có thể; sau đó tích hợp vào nền tảng **Mây Hẹn (mayhen.vn)** bằng Laravel Blade + HTML/CSS/JavaScript thuần.  
> **Cách dùng:** Gửi **toàn bộ tài liệu này** cho AI có khả năng mở trình duyệt, kiểm tra DOM/CSS, tải tài nguyên, lập trình, chạy thử, chụp ảnh màn hình và tạo tệp ZIP. Nếu môi trường thiếu một khả năng, phải công khai phần không thể thực hiện, tuyệt đối không tự nhận đã hoàn tất.

---

## 0. VAI TRÒ VÀ ĐẦU BÀI

Bạn là **Senior Frontend Reverse-Engineering Engineer, Visual QA Engineer và Laravel Blade Architect**. Nhiệm vụ là **tái dựng trung thực một mẫu thiệp cưới có sẵn**, không phải thiết kế một mẫu mới lấy cảm hứng từ nó.

**Thông số dự án:**

| Thuộc tính | Giá trị |
|---|---|
| Nền tảng nhận mẫu | **Mây Hẹn — mayhen.vn** |
| URL tham chiếu | `https://songhy.online/invite/hoa-tulip-xanh-reu` |
| Tên mẫu trên Mây Hẹn | **Hương Hoa Ngày Đính Ước** |
| Slug | `huong-hoa-ngay-dinh-uoc` |
| Bộ công nghệ | Laravel Blade, HTML5, CSS3, JavaScript ES Modules thuần |
| Tệp ZIP đầu ra | **`Hương Hoa Ngày Đính Ước.zip`** |
| Thư mục gốc trong ZIP | `huong-hoa-ngay-dinh-uoc/` |

### MỆNH LỆNH TỐI CAO: SOURCE OF TRUTH = MẪU GỐC

1. **Mẫu gốc quyết định toàn bộ thẩm mỹ.** Không tự áp dụng Sage Green, Deep Forest, Antique Gold, Quiet Luxury, glassmorphism, gradient, bo góc, bóng đổ, khung vàng, đường viền, họa tiết, hiệu ứng, font hoặc màu mới chỉ vì chúng có vẻ đẹp hơn.
2. **Sao chép đúng thứ tự, cách chia section và hệ phân cấp thị giác của mẫu gốc.** Không tự thêm section, không đổi layout sang thiết kế kiểu AI, không chuẩn hóa tất cả thành card tròn hoặc khối có viền.
3. **Giữ đúng ảnh nguồn khi được phép sử dụng**, tỷ lệ khung, vị trí crop, lớp phủ, nền, họa tiết, ký tự trang trí, biểu tượng và cách kết hợp ảnh/chữ.
4. **Tính năng bắt buộc của Mây Hẹn được tích hợp với mức thay đổi giao diện nhỏ nhất.** Nếu nguồn chưa có phần Ký tên điện tử thì bổ sung phần này theo chính ngôn ngữ thiết kế đã đo đạc từ nguồn, **không** sáng tạo một phong cách khác.
5. **Độ trung thực giao diện cao hơn sở thích cá nhân và phong cách mặc định của AI.** Chỉ thay đổi khi có yêu cầu riêng, hạn chế kỹ thuật có bằng chứng, hoặc nghĩa vụ liên quan đến quyền sử dụng tài nguyên.
6. **Không dùng ảnh chụp toàn trang làm giao diện.** Phải xây dựng HTML có ngữ nghĩa và tương tác thật; screenshot chỉ dùng để phân tích/đối chiếu.
7. **Không tuyên bố giống 100% nếu chưa đối chiếu ảnh thực tế.** Nếu không truy cập được một phần trang, hãy ghi rõ phần đó chưa được xác minh; không tự đoán.

### PHÂN BIỆT HAI YÊU CẦU DỄ XUNG ĐỘT

- **Clone nguyên bản (bắt buộc):** Giữ **đúng bảng màu và mọi chi tiết thị giác của URL tham chiếu**; không áp bảng màu xanh lá/kem/vàng từ prompt cũ.
- **Đổi màu thương hiệu (không nằm trong đầu bài hiện tại):** Chỉ được làm thành **biến thể riêng** khi người dùng yêu cầu rõ. Không pha trộn màu mới vào bản clone chuẩn.

---

## 1. GIAI ĐOẠN A — KHẢO SÁT MẪU THỰC TẾ TRƯỚC KHI CODE

**Không được bắt đầu viết CSS trước khi có dữ liệu khảo sát.**

### A1. Khảo sát bằng trình duyệt thực

1. Mở URL tham chiếu trong Chromium/Chrome với trạng thái sạch.
2. Quan sát **màn hình mở thiệp**, sau đó thực hiện thao tác **chạm mở thiệp**, thử bật/tắt nhạc, cuộn trang từ đầu đến cuối.
3. Thu thập ảnh chụp **full page** và ảnh chụp từng section; chụp riêng các trạng thái mở/đóng, hover, active, gallery, popup, form, mobile menu nếu có.
4. Lặp lại ở các viewport tối thiểu: **320×720, 375×812, 390×844, 430×932, 480×900, 1440×900**. Mẫu ưu tiên mobile thì mobile là chuẩn đối chiếu chính.
5. Đọc DOM, computed styles, network resources và hành vi tương tác để ghi nhận thông số thật. Nếu chưa thể đọc CSS gốc, đo trực tiếp trên screenshot và đánh dấu thông số là *ước lượng*.
6. Kiểm tra lazy-load bằng cách cuộn toàn bộ trang; xác nhận ảnh nền nằm trong CSS, ảnh responsive (`srcset`, `<picture>`), SVG, font và âm thanh.
7. Phân biệt nội dung thiệp với widget bên thứ ba, watermark và nút quảng cáo. Chỉ loại bỏ phần không thuộc thiết kế thiệp hoặc không được phép sử dụng; ghi nhận ngoại lệ.
8. Nếu trang có dữ liệu cá nhân, họ tên, địa chỉ, số tài khoản hoặc ảnh của cặp đôi cụ thể, **không chép mặc định vào dữ liệu khách hàng**. Thay bằng dữ liệu demo trung tính và cho phép Studio thay đổi. Giữ nguyên *kiểu chữ/cách trình bày*, không nhất thiết giữ nguyên thông tin riêng tư.

### A2. Tạo `reference-audit.md` trước khi triển khai

Ghi **từng section theo đúng thứ tự** nguồn, không tự sắp xếp lại. Mỗi section có:

- Tên/điểm bắt đầu-kết thúc, mục đích, thứ tự xuất hiện và ảnh chụp tương ứng.
- Nền: ảnh hay màu thuần; nếu là màu ghi mã HEX/RGBA; nếu có gradient thì ghi chính xác góc, stop và alpha.
- Typography: font family thực tế, font weight, cỡ, line-height, letter-spacing, chữ hoa/thường, căn lề và shadow chữ.
- Box model: width, min/max-width, padding, margin, gap, border width/style/color, radius, shadow, opacity, blend mode.
- Ảnh: file nguồn, kích thước gốc, tỷ lệ, `object-fit`, `object-position`, crop, overlay, mask và vị trí trên màn hình.
- Bố cục: grid/flex/flow, phân lớp, tỷ lệ các phần, thứ tự DOM, khoảng trắng thực tế.
- Chuyển động: trigger, duration, easing, direction, offset; nếu không thấy hiệu ứng thì **không thêm**.
- Hành vi: nút mở thiệp, music, auto-scroll, lịch, album, bản đồ, RSVP, lời chúc, QR và các control thực tế.
- Mức tin cậy: `observed`, `measured` hoặc `estimated`.

**Chốt yêu cầu:** Không được thay các kết quả quan sát bằng tính từ chung chung như “sang trọng”, “hiện đại”, “tinh tế”; phải dùng giá trị và đặc điểm cụ thể từ nguồn.

---

## 2. GIAI ĐOẠN B — TẢI VÀ ĐÓNG GÓI ẢNH/TÀI NGUYÊN GỐC

### B1. Quy tắc tải tài nguyên

1. Liệt kê toàn bộ tài nguyên **thực sự được trang thiệp dùng để hiển thị**: ảnh (`img`, `picture`, CSS `background-image`), texture, họa tiết, sticker, SVG, icon, font (`woff2`, `woff`, `ttf` nếu được phép), audio và những file liên quan.
2. Với tài nguyên **được quyền sử dụng/tái phân phối**, tải **file gốc đúng URL thực**, ưu tiên độ phân giải cao nhất phù hợp; không lấy ảnh screenshot để thay thế nếu file nguồn còn truy cập được.
3. Tài nguyên có thể đến từ nhiều host/CDN khác nhau. Thu thập đúng file theo network trace, nhưng **mã nguồn xuất ra chỉ tham chiếu file nội bộ**.
4. Lưu trong `assets/` và đổi tên mô tả, tránh tên vô nghĩa hay trùng tên. Ví dụ: `hero-background.webp`, `section-family-decoration.webp`, `gallery-01.webp`, `font-heading.woff2`, `icon-music.svg`.
5. Có thể chuyển raster hợp lệ sang **WebP** với chất lượng đủ để không làm khác màu/độ nét; giữ alpha, orientation và tỷ lệ ảnh. Không kéo giãn, không cắt sai, không ép mọi ảnh thành 1:1.
6. **Không thay ảnh gốc bằng ảnh AI tự sinh, ảnh stock ngẫu nhiên, SVG vẽ lại hoặc gradient** nếu file nguồn có thể lấy hợp lệ và mục tiêu là clone chính xác.
7. Nếu ảnh bị chặn, không đủ quyền sử dụng hoặc không truy cập được: không vượt cơ chế bảo vệ, không tải lén; ghi `missing`/`requires-permission` vào manifest và dùng placeholder **chỉ cho phần chưa được cấp phép**, đánh dấu rõ trong báo cáo. Không tự nhận ZIP có đầy đủ ảnh gốc khi còn thiếu.
8. Với ảnh minh họa của cặp đôi, chỉ đóng gói khi có quyền sử dụng; **mọi vị trí ảnh cặp đôi trong thiệp phải chỉnh sửa được bằng Studio**. Không nhúng cố định ảnh cá nhân không thể thay.
9. Nếu nguồn không có nhạc hoặc không được phép phân phối file nhạc, không tạo MP3 rỗng/giả; nêu thiếu tài nguyên và cung cấp cơ chế thay file nhạc hợp lệ sau này.
10. Không nhúng hotlink từ website nguồn hoặc CDN vào bất kỳ HTML, CSS, JS hay SVG nào của bản xuất.

### B2. Manifest bắt buộc

Tạo `assets-manifest.json` với tối thiểu:

- `id`, `type`, `sourceUrl`, `localPath`, `usedInSections`.
- `originalWidth`, `originalHeight`, `outputWidth`, `outputHeight` nếu áp dụng.
- `sourceFormat`, `outputFormat`, `sha256`, `downloadStatus`.
- `rightsStatus`: `authorized`, `requires-permission`, `unknown`.
- `notes`: xử lý/chuyển đổi/thất bại và lý do.

**Tuyệt đối không đánh dấu `authorized` nếu chưa có cơ sở.** Nếu không có quyền với một tài nguyên thì chỉ lưu thông tin tham chiếu và dùng tài nguyên thay thế có quyền hợp lệ; không đóng gói trái phép.

### B3. Kiểm tra sau tải

- Đảm bảo 100% asset đã kê khai `downloadStatus=downloaded` thực sự tồn tại và mở được.
- Dò toàn bộ chuỗi `http://`, `https://`, `url(...)`, `@import`, `src`, `srcset`, `poster`, `href` trong mã nguồn để không còn asset ngoại.
- Các **liên kết hành động** (Google Maps, chia sẻ, thanh toán bên ngoài) có thể hướng ra website khác nếu thật sự là chức năng, nhưng **không được dùng link ngoài để tải resource giao diện**.
- ZIP phải chứa chính xác các tệp mà code tham chiếu, kể cả font, icon và ảnh nền ẩn trong CSS.

---

## 3. GIAI ĐOẠN C — DỰNG GIAO DIỆN TRUNG THỰC, KHÔNG “AI STYLE”

### C1. Bảo toàn 100% ngôn ngữ thị giác gốc

**CẤM thực hiện những thay đổi sau khi không có bằng chứng từ ảnh/DOM gốc:**

- Đổi màu nền, màu chữ, màu viền, màu nút hoặc áp một palette khác.
- Thêm/giảm border, bo góc, box-shadow, glow, glass, blur, gradient, texture hoặc overlay.
- Đổi font sang serif/cursive/sans khác; thay font vì “trông thanh lịch hơn”.
- Tự tạo card, container, đường phân cách, icon, tem sáp, hoa lá hoặc pattern.
- Tự sửa khoảng cách, tăng/giảm padding theo cảm tính; căn giữa khi nguồn căn trái hoặc ngược lại.
- Đổi tỷ lệ khung ảnh, vị trí crop, trật tự ảnh/chữ, chiều cao section hoặc cách phân lớp.
- Tự thêm hiệu ứng lá rơi, tuyết, đom đóm, parallax, tim bay nếu nguồn không có.
- Xóa họa tiết của bản gốc chỉ vì khó code; thay bằng emoji hoặc CSS đơn giản hơn.
- Tự đổi nội dung trang thành thiết kế landing page/card thông thường.

**Nếu nguồn có chi tiết phức tạp:** Ưu tiên tái sử dụng asset đúng quyền sử dụng hoặc tái dựng bằng CSS/SVG trung thực. Không dùng SVG “trang trí đại khái”.

### C2. CSS theo hệ token **trích từ mẫu**, không phải token AI tự nghĩ

Tạo `:root` với các biến `--mayhen-invitation-*` lấy từ giá trị đo trên nguồn. Ví dụ **chỉ minh họa cách đặt tên, không phải bảng màu mặc định**:

```css
:root {
  --mayhen-invitation-bg: /* HEX đo từ bản gốc */;
  --mayhen-invitation-text: /* HEX đo từ bản gốc */;
  --mayhen-invitation-accent: /* HEX đo từ bản gốc */;
  --mayhen-invitation-heading-font: /* font gốc, self-host */;
  --mayhen-invitation-body-font: /* font gốc, self-host */;
}
```

Đo và thực thi đúng border width, radius, shadow, typography và spacing theo section; **không** ép toàn bộ mẫu vào một bộ radius/shadow thống nhất nếu bản gốc không như vậy.

### C3. Triển khai theo thứ tự bắt buộc

1. Dựng khung section đúng nguồn.
2. Đặt đúng asset và crop.
3. Khớp màu, kiểu nền, border, trang trí và typography.
4. Khớp spacing, kích thước, tỉ lệ, vị trí và responsive.
5. Khớp animation và hành vi sẵn có.
6. Sau cùng mới tích hợp dữ liệu động, RSVP, Signature và Studio.

**Lưu ý:** Không được ưu tiên làm chức năng trước rồi phỏng đoán giao diện từ cảm nhận.

---

## 4. QUY ĐỊNH KỸ THUẬT MÂY HẸN

### D1. 100% Native Frontend

- Dùng Laravel Blade cho server template, **HTML5 + CSS3 + Vanilla JavaScript ES Modules** cho frontend.
- **Không dùng** React, Vue, Angular, Tailwind, Bootstrap, jQuery, GSAP, Anime.js, AOS, Swiper, Slick, UI kit hoặc thư viện animation/form/carousel ngoài.
- Không gọi CDN/font server/JS ngoài; không `@import` font URL ngoài.
- Có thể dùng `fetch`, `IntersectionObserver`, Pointer Events, Canvas API, Web Animations API khi cần, miễn là trình duyệt hỗ trợ và có fallback hợp lý.

### D2. Class prefix, khả năng tái sử dụng

- Tất cả **class tự tạo** trong template, CSS và JS phải bắt đầu bằng `mayhen_invitation_`.
- Đặt tên kiểu BEM có namespace: `mayhen_invitation_hero`, `mayhen_invitation_hero__title`, `mayhen_invitation_hero--opened`.
- Không dùng class chung như `.container`, `.button`, `.active`, `.hidden`, `.card`, `.title` khiến xung đột giao diện của website Mây Hẹn.
- Dùng ID chỉ khi cần để kết nối `label/for`, `aria-*` hoặc `canvas`; tuyệt đối không dùng ID để style hàng loạt.
- Selector phải scope trong root `.mayhen_invitation_root`. Không thay đổi CSS toàn cục của layout Mây Hẹn.

### D3. Responsive Flow Layout

- Khung thiệp chính mặc định: `width: 100%; max-width: 480px; margin-inline: auto; position: relative;` **nếu tương thích với bố cục thực của bản tham chiếu**.
- Dùng Grid/Flexbox/normal flow; `rem`, `%`, `min()`, `max()`, `clamp()`, `aspect-ratio` và breakpoint dựa trên hành vi nguồn.
- Không dùng absolute positioning để “xếp cả trang” với tọa độ pixel cứng. **Cho phép** `position: absolute` giới hạn ở các lớp trang trí/overlay thực sự chồng lớp theo mẫu, neo tương đối với container và không phá flow.
- Không làm méo bố cục của bản gốc chỉ để giữ `max-width: 480px`; nếu phát hiện xung đột, nêu rõ điểm khác và dùng phương án ít sai lệch nhất.
- Không horizontal overflow ngoài chủ đích. Test ít nhất các viewport ở mục A1 và cả màn hình dọc/ngang nếu nguồn có hỗ trợ.
- Lưu ý safe-area và nút điều khiển khi hiển thị trên điện thoại.

### D4. Blade contract

- `template.blade.php` phải chứa `@extends('client.layouts.invitation')` và khai báo `@section`/`@push` theo cấu trúc layout mà dự án **thực sự hỗ trợ**.
- Không tự giả định biến Blade, route, controller, API, `@stack`, helper `$resolveAsset(...)` hoặc cấu trúc `schema.json` đã có sẵn nếu chưa được cung cấp.
- Nếu contract dự án chưa có, triển khai adapter rõ ràng, ghi `ASSUMPTION` trong README và cung cấp fallback cục bộ. Không nói “đã tích hợp production” nếu chưa test được trong Mây Hẹn.
- Escape dữ liệu đầu vào bằng Blade đúng chuẩn; không đưa dữ liệu khách hàng chưa kiểm duyệt vào `innerHTML`.
- Nếu có nhiều instance thiệp/trang Studio, hạn chế biến toàn cục và tránh trùng DOM selector.

---

## 5. MÀN MỞ THIỆP, ÂM NHẠC VÀ STUDIO EMBED

### E1. Opening Screen

- **Bắt chước màn mở đầu và thao tác mở thiệp từ nguồn**: cấu trúc, ảnh, màu, viền, chữ, độ mờ, hình động, chiều cao và trigger.
- Không tự thêm phong bì 3D/cánh cửa song hỷ nếu bản gốc không có; chỉ sử dụng giải pháp khác khi người dùng yêu cầu riêng.
- Dùng class tối thiểu: `mayhen_invitation_opening`, `mayhen_invitation_open_btn` và các class con đúng namespace.

### E2. Nhạc

- Khi khách **tự chạm mở thiệp**, có thể gọi `audio.play()` để bật nhạc; xử lý Promise bị từ chối và cung cấp nút play/pause đúng thiết kế nguồn.
- Không hứa autoplay trước tương tác người dùng (trình duyệt có thể chặn).
- File nhạc được cấp phép nằm nội bộ tại `assets/music.mp3`, hoặc dùng một tệp âm thanh hợp lệ khác được ghi trong manifest.
- Nếu không có quyền với nhạc gốc, không tải/đóng gói và không thay bằng nhạc ngẫu nhiên mà không báo.

### E3. Studio Editor / Embedded Preview

- Hỗ trợ cờ `$isEmbed` (mặc định `false` nếu dự án chưa truyền).
- Khi `true`: **ẩn hoàn toàn opening**, hiển thị thiệp ở trạng thái đã mở, không chặn `html`/`body`, không tự autoplay, không tạo overlay ăn pointer events.
- Không thêm `no-scroll` hoặc `overflow: hidden` lên `html`/`body` của Studio.
- Khi `false`: mở thiệp theo đúng nguồn; nếu khóa cuộn trong lúc opening thì chỉ khóa đúng lúc, tự giải phóng khi mở, khi lỗi và khi destroy.
- Thay đổi trạng thái phải an toàn cả khi Studio hot-reload/preview nhiều lần; không bind listener hoặc timer lặp vô hạn.

---

## 6. RSVP VÀ SỔ KÝ TÊN — TÍNH NĂNG MÂY HẸN BẮT BUỘC

Cần đủ **RSVP Form** và **Signature Canvas**, dù bản tham khảo có thể thiếu một tính năng.

### F1. Nguyên tắc hình ảnh tuyệt đối

- **KHÔNG có ảnh cặp đôi, ảnh người hoặc ảnh stock có người ở bất kỳ phần nào của RSVP/Signature**, kể cả `background-image`, pseudo-element, sticker hoặc nền canvas.
- Chỉ dùng nền giấy, màu, đường nét, họa tiết hình học/hoa văn, icon SVG tự tạo hoặc tài nguyên trang trí hợp lệ.
- Nếu bản gốc có ảnh người bên trong RSVP thì **ngoại lệ này được loại bỏ** và phải nêu trong `visual-audit.md`; mọi chi tiết còn lại vẫn bám sát mẫu.
- Ưu tiên reuse token/viền/font/background/padding/cách sắp chữ **đo từ các section liền kề**, không tạo một phong cách “AI card” mới cho form.

### F2. RSVP thực sự hoạt động

- Các trường tối thiểu: tên khách, trạng thái tham dự, số người (nếu có), lời nhắn (nếu có), thông tin sự kiện/địa điểm tương ứng.
- Validation, trạng thái loading/success/error, hỗ trợ bàn phím và nhãn accessible.
- Dùng API endpoint **được cấu hình từ dự án**, CSRF token cho form Laravel, xử lý lỗi đúng thực tế; không hard-code URL backend bịa ra.
- Nếu chưa có backend thì ghi rõ `adapter required`, cung cấp sự kiện/hook để Studio kết nối; **không hiển thị “gửi thành công” giả**.

### F3. Canvas ký tên

- Hỗ trợ chuột, bút và cảm ứng bằng Pointer Events, chuẩn hóa tọa độ theo canvas bounds và `devicePixelRatio`.
- Hỗ trợ xóa, viết lại, xem trước và xuất chữ ký PNG/WebP (hoặc blob/data URL theo contract backend).
- Tránh mất nét khi resize; không tự vẽ chữ ký mẫu hoặc sử dụng ảnh cặp đôi làm nền.
- Nếu chưa có endpoint lưu, cung cấp adapter/event rõ ràng và thông báo trạng thái thực.

### F4. Vị trí phần bắt buộc chưa có ở nguồn

- Giữ nguyên trật tự và bố cục **tất cả** section có sẵn.
- Chỉ chèn section thiếu ở điểm hợp lý ít ảnh hưởng nhất đến bố cục tổng thể, nêu rõ đó là *Mây Hẹn-only section*.
- Không đẩy/viết lại các section gốc một cách tùy tiện để “làm cân đối”.

---

## 7. LIVE SYNC VỚI STUDIO TẠO THIỆP

### G1. `data-sync-target` bắt buộc

Gắn vào **mọi lần xuất hiện tương ứng trong HTML**, bao gồm text, hình ảnh, QR, background ảnh chỉnh sửa được. Dùng chính xác các key có sẵn của Studio, tối thiểu:

```text
groom_name
bride_name
couple_names
groom_father
groom_mother
bride_father
bride_mother
wedding_date_digits
wedding_lunar_date
wedding_day
wedding_month
wedding_year
calendar_month_year
wedding_calendar
party_time
party_venue
event_party_address
groom_bank_account
groom_bank_name
qr_groom_image
hero_photo_url
```

Với các thông tin xuất hiện nhiều nơi, **mọi occurrence phải được đồng bộ**, không chỉ ô hiển thị đầu tiên. Nếu trang nguồn có hai gia đình/hai tiệc/hai QR, mở rộng schema thành các field tách biệt (`*_groom`, `*_bride` hoặc theo contract Studio thực có), tránh ghi đè cùng một value vào hai vị trí khác nghĩa.

### G2. Đồng bộ ngày — lịch — đếm ngược

Cung cấp 2 API toàn cục chính xác:

```js
window.updateMayhenCountdown = function (targetDate) {
  // Cập nhật ngay, quản lý timer an toàn, cleanup timer cũ.
};

window.renderMayhenWeddingCalendar = function (targetDate) {
  // Render đúng tháng/năm, số ngày, thứ và ngày cưới đang chọn.
};
```

**Yêu cầu hành vi:**

- Thay đổi ngày trong Studio cập nhật ngay: text ngày, thứ, tháng, năm, điểm đánh dấu trên lịch và countdown.
- Xử lý đúng năm nhuận, ngày đầu tháng, số ngày trong tháng và thứ Hai–Chủ nhật (nếu nguồn sử dụng định dạng đó).
- Xử lý timezone **Asia/Ho_Chi_Minh**, không parse chuỗi ngày mơ hồ theo trình duyệt.
- Khi ngày qua đi, countdown về 0 hoặc trạng thái quá hạn theo quy tắc được chọn; không hiển thị số âm.
- Các ô ngày/lịch không được hard-code như HTML tĩnh từ mẫu nguồn.
- Nội dung âm lịch phải đến từ dữ liệu hoặc logic xác thực; không tự tính sai hoặc bịa ngày âm.

### G3. Ảnh và gallery có thể chỉnh sửa

- `hero_photo_url`, các ảnh album và ảnh riêng của hai người phải được đưa vào schema để người dùng thay từ Studio.
- QR ngân hàng là dữ liệu động; hỗ trợ cả hai bên nếu mẫu có hai khối.
- Không nhúng ảnh khách hàng cụ thể thành background cố định không thay được.

---

## 8. HIỆU ỨNG — CHỈ TÁI TẠO NHỮNG GÌ NGUỒN CÓ

- Xác định hiệu ứng thực tế trước khi viết keyframe.
- Ưu tiên CSS transform/opacity để animation nhẹ và không gây reflow; dùng JS điều khiển khi cần hành vi thật.
- Nếu nguồn có reveal-on-scroll, xác định offset và thứ tự xuất hiện; dùng `IntersectionObserver` với fallback.
- Nếu nguồn không có animation tại một section: để **tĩnh**, không tự thêm bobbing, glow, parallax, tuyết/lá rơi hoặc hiệu ứng lấp lánh.
- Hỗ trợ `prefers-reduced-motion: reduce` một cách không làm ẩn nội dung.
- Không tuyên bố “60 FPS bảo đảm” khi chưa đo thực tế; ghi lại điểm nghẽn performance nếu có.

---

## 9. KIỂM THỬ VISUAL REGRESSION — BẮT BUỘC TRƯỚC KHI XUẤT ZIP

### I1. Ảnh tham chiếu và ảnh local

Tạo thư mục `qa/` chứa ít nhất:

```text
qa/
  reference/
    mobile-opening.png
    mobile-fullpage.png
    desktop-fullpage.png
  local/
    mobile-opening.png
    mobile-fullpage.png
    desktop-fullpage.png
  comparison/
    mobile-side-by-side.png
    mobile-diff.png
    desktop-side-by-side.png
    desktop-diff.png
```

Có thể thêm ảnh section/breakpoint. Nếu không chụp được ảnh nguồn, ghi rõ **không thể xác minh pixel** và tuyệt đối không dựng ảnh “reference” giả.

### I2. Quy trình đối chiếu

1. Chụp bản gốc và bản local ở **cùng viewport, cùng trạng thái thiệp, cùng mức scroll, cùng mức zoom và cùng dữ liệu demo hợp lệ nếu có thể**.
2. Chờ font, ảnh, lazy-load ổn định; giảm/đồng bộ animation khi chụp để tránh sai khác do thời điểm.
3. So sánh side-by-side và ảnh diff; dùng công cụ SSIM, MAE hoặc pixel diff **nếu môi trường hỗ trợ**. Loại trừ rõ ràng các vùng dữ liệu động/ảnh chưa được cấp quyền.
4. Rà bằng mắt theo thứ tự: **bố cục tổng thể → ảnh/crop → màu nền → font → kích thước chữ → border/shadow/radius → spacing → icon → hiệu ứng**.
5. Sửa từ lỗi thị giác lớn nhất, chụp lại, lặp cho đến khi không còn sai khác đáng kể có thể khắc phục.
6. Kiểm tra opening, ảnh, lịch, RSVP, signature, music, album/gallery, QR, phản hồi lỗi API và Studio embed.

### I3. Tiêu chí nghiệm thu

- Không có section gốc bị thiếu, đổi thứ tự hoặc bị thay bằng thiết kế tùy ý.
- Không có màu/viền/bo góc/shadow/gradient “tự sáng tác” không tìm thấy trong nguồn.
- Các ảnh được phép sử dụng nằm local, đúng tỷ lệ và crop.
- Chữ/họa tiết/nút được đặt đúng nhóm vị trí với bản gốc ở các breakpoint chính.
- Không horizontal overflow, nút không bị che, không section trắng do lỗi animation/embed.
- Không console error, JS error, 404 asset hoặc lỗi syntax Blade/JSON.
- RSVP/Signature đúng hành vi, có phản hồi thành công/thất bại thật; Studio đồng bộ các field.
- Ghi rõ số lượng ảnh nguồn tìm thấy, số lượng tải thành công, số lượng thiếu, số lượng file chuyển WebP.
- `visual-audit.md` liệt kê **mọi sai khác còn lại**, nguyên nhân, mức ảnh hưởng và trạng thái (`fixed`, `acceptable-exception`, `unresolved`).

**Nguyên tắc xuất bản:** Nếu không có công cụ chạy browser hoặc đo diff, vẫn được cung cấp phần mã đã hoàn thành nhưng phải ghi rõ `Visual QA: NOT VERIFIED`; không được viết “pixel perfect 100%”.

---

## 10. CẤU TRÚC ZIP BẮT BUỘC

```text
Hương Hoa Ngày Đính Ước.zip
└── huong-hoa-ngay-dinh-uoc/
    ├── template.blade.php
    ├── styles.css
    ├── script.js
    ├── schema.json
    ├── assets-manifest.json
    ├── reference-audit.md
    ├── visual-audit.md
    ├── README.md
    ├── assets/
    │   ├── images/
    │   │   ├── ...webp
    │   ├── fonts/
    │   │   ├── ...woff2
    │   ├── icons/
    │   │   ├── ...svg
    │   └── music.mp3                # Chỉ khi có nguồn hợp lệ/được cấp phép
    └── qa/
        ├── reference/
        ├── local/
        └── comparison/
```

**Quy định riêng cho từng file:**

| File | Nội dung |
|---|---|
| `template.blade.php` | Trang Blade hoàn chỉnh, đúng các section nguồn và đúng contract Mây Hẹn, escape dữ liệu, có `data-sync-target` đầy đủ. |
| `styles.css` | CSS thuần, prefix `mayhen_invitation_`, bố cục/mobile chính xác, các font `@font-face` nội bộ và hiệu ứng gốc. |
| `script.js` | ES Modules thuần: mở thiệp, nhạc, gallery nếu có, RSVP, ký tên, countdown, calendar, cleanup và embed. |
| `schema.json` | Các nhóm/trường Studio (tên cô dâu/chú rể, gia đình, sự kiện, ảnh, gallery, QR, nhạc, v.v.). Nếu schema thật chưa được cung cấp, đánh dấu **proposed schema** và mô tả adapter cần thiết. |
| `assets-manifest.json` | Ánh xạ URL nguồn ↔ file local, hash, tình trạng tải, quyền sử dụng và lỗi thiếu asset. |
| `reference-audit.md` | Phân tích từng section, bảng màu/font/ảnh, thông số layout và hành vi mẫu. |
| `visual-audit.md` | Kết quả so sánh screenshot, danh sách lỗi đã sửa/chưa sửa và ngoại lệ. |
| `README.md` | Cách tích hợp Mây Hẹn, đường dẫn asset, biến Blade, endpoint cần nối, cách chạy thử, giới hạn và quyền sử dụng. |
| `assets/` | Toàn bộ tài nguyên hợp lệ được sử dụng bởi bản clone, không có link nóng/CDN. |
| `qa/` | Screenshot nguồn, bản chạy local và ảnh đối chiếu nếu công cụ thực hiện được. |

**Lưu ý:** Không được tạo file giả/empty placeholder để trông như đủ cấu trúc. Nếu thiếu một asset/file QA, ghi rõ tình trạng trong README và báo cáo.

---

## 11. QUY TRÌNH THỰC HIỆN THEO THỨ TỰ, KHÔNG BỎ BƯỚC

1. **Inspect:** Truy cập URL, ghi lại toàn bộ section/state và thông số thiết kế.
2. **Capture:** Lưu ảnh screenshot nguồn ở nhiều viewport.
3. **Inventory:** Lập danh sách các ảnh/asset/font/music từ DOM, CSS và network.
4. **Download:** Tải nguồn được phép sử dụng, chuyển WebP khi phù hợp, kiểm tra file, ghi manifest.
5. **Implement:** Viết Blade + CSS + JS native theo **source lock**, không tự sáng tạo.
6. **Integrate:** Ánh xạ Studio schema, Live Sync, embed, RSVP và Signature.
7. **Test:** Chạy local, test chức năng, test responsive, kiểm tra console, asset và accessibility cơ bản.
8. **Compare:** Chụp local screenshot, đối chiếu side-by-side/diff, sửa lỗi rồi so sánh lại.
9. **Audit:** Ghi chênh lệch thị giác, ngoại lệ về quyền tài nguyên và điểm chưa được xác thực.
10. **Package:** Đóng ZIP, kiểm tra cấu trúc, kiểm tra integrity, giải nén thử và xác minh toàn bộ asset được tham chiếu đều tồn tại.

Không bỏ qua bước 8 để chuyển sang bước 10 chỉ vì giao diện “nhìn có vẻ ổn”.

---

## 12. ĐẦU RA PHẢI TRẢ CHO NGƯỜI DÙNG

**Không chỉ in code vào chat.** Hãy trực tiếp tạo file trong môi trường có quyền ghi file và xuất:

1. Link tải ZIP **`Hương Hoa Ngày Đính Ước.zip`** chứa toàn bộ bộ mã nguồn, asset hợp lệ và tài liệu audit.
2. Bản preview/screenshot local tiêu biểu, nếu có thể thực thi browser.
3. Tóm tắt: số section clone, số asset tải được/tổng số, số tài nguyên thiếu, tính năng đã chạy, tình trạng Studio integration và visual QA.
4. Danh sách ngắn các điểm chưa giống/không hoàn thành, với lý do cụ thể và không che giấu hạn chế.

**Điều kiện kết thúc:** Chỉ nói **“Hoàn thành”** khi ZIP thực sự đã được tạo, có thể giải nén, không thiếu mã nguồn quan trọng và các kiểm tra được ghi rõ. Nếu công cụ không hỗ trợ tải nguồn, render, kiểm thử hoặc tạo ZIP: cung cấp sản phẩm thật đã làm được và ghi rõ phần còn lại; **không bịa đường dẫn tải, ảnh, thành tích QA hoặc asset**.

---

## 13. CHECKLIST CHỐT TRƯỚC KHI GỬI

- [ ] Đã quan sát bản gốc ở trạng thái mở/đóng và cuộn hết trang.
- [ ] Đã đối chiếu chính xác màu sắc, font, viền, radius, shadow, background, ảnh và crop.
- [ ] Không tự đưa phong cách Quiet Luxury/Sage Green/AI-style vào bản clone gốc.
- [ ] Không tự thêm/bớt/đổi trật tự section của nguồn, ngoại trừ tính năng Mây Hẹn bắt buộc được ghi chú.
- [ ] Đã tải và đóng gói **toàn bộ asset có quyền sử dụng**; asset thiếu được ghi rõ.
- [ ] Không còn URL asset ngoại/CDN trong bản local.
- [ ] Blade, CSS, JS và schema đúng namespace và contract đã xác minh.
- [ ] Opening hoạt động; Studio embed không trắng/tràn/khóa cuộn.
- [ ] Countdown và Calendar cập nhật live; múi giờ đúng.
- [ ] RSVP và Signature có chức năng thực, không có ảnh cặp đôi trong hai phần đó.
- [ ] Studio có thể thay ảnh cặp đôi, QR và nội dung khách hàng.
- [ ] Đã so sánh screenshot nếu môi trường hỗ trợ, có báo cáo sai khác minh bạch.
- [ ] Đã đóng và giải nén kiểm tra `Hương Hoa Ngày Đính Ước.zip`.

> **NHẮC LẠI CHO AI THỰC THI:** Người dùng **không cần một giao diện mới trông đẹp theo kiểu AI**. Người dùng cần **đúng thiết kế đã gửi từ A–Z**, chỉ thay phần cấu trúc mã nguồn để tương thích Mây Hẹn và bổ sung các chức năng bắt buộc theo phong cách gốc. **Không tự sáng tạo màu sắc, viền, font, shadow, animation, khung hoặc ảnh.**

