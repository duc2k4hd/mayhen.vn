# MÂY HẸN — ADMIN ATOMIC FUNCTION CATALOG

> Catalog phân rã từ tài liệu Admin hiện có **cộng các đề xuất bổ sung**. `SPEC_OR_HISTORY_NEEDS_RUNTIME_VERIFICATION` không có nghĩa code đã tồn tại; mọi case chưa chạy.
> Những chức năng nằm trong tài liệu cũ nhưng có thể đã bị loại bỏ/tái cấu trúc chỉ là nguồn kiểm tra, **không tự động phục hồi hành vi nguy hiểm**.

- Tổng: **534** hạng mục; **461** từ yêu cầu/lịch sử cần xác minh; **73** đề xuất VNext.
- Test planning: **1407** case `NOT_RUN` (bao gồm 24 luồng xuyên module). Không phải kết quả test đã thực thi.

## A01 — Xác thực & phiên Admin
**Nguồn tài liệu:** `ADMIN_ARCHITECTURE_ROADMAP.md GĐ1; MASTER_PRD §2,§67`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A01-001` | Cần xác minh trong code | Trang đăng nhập Admin hiển thị đúng thương hiệu và mobile | P0 |
| `ADM-A01-002` | Cần xác minh trong code | Đăng nhập thành công bằng tài khoản đủ quyền | P0 |
| `ADM-A01-003` | Cần xác minh trong code | Đăng nhập sai thông tin không tiết lộ tài khoản tồn tại | P0 |
| `ADM-A01-004` | Cần xác minh trong code | Chặn truy cập admin đối với khách chưa đăng nhập | P0 |
| `ADM-A01-005` | Cần xác minh trong code | Chặn user customer vào route admin sau đăng nhập | P0 |
| `ADM-A01-006` | Cần xác minh trong code | Chặn tài khoản bị khóa hoặc vô hiệu hóa | P0 |
| `ADM-A01-007` | Cần xác minh trong code | Đăng xuất hủy session và CSRF/session token thích hợp | P0 |
| `ADM-A01-008` | Cần xác minh trong code | Redirect deep link đúng sau đăng nhập Admin | P0 |
| `ADM-A01-009` | Cần xác minh trong code | Rate limit thử mật khẩu liên tục | P0 |
| `ADM-A01-010` | Cần xác minh trong code | Thông báo lỗi validation ngay bên dưới input | P0 |
| `ADM-A01-011` | Cần xác minh trong code | Remember-me nếu chính sách cho phép | P0 |
| `ADM-A01-012` | Cần xác minh trong code | Đổi mật khẩu của chính tài khoản Admin | P0 |
| `ADM-A01-013` | Cần xác minh trong code | Đổi mật khẩu hủy phiên cũ theo chính sách | P0 |
| `ADM-A01-014` | Cần xác minh trong code | Không cho Manager đặt lại mật khẩu Super Admin | P0 |
| `ADM-A01-015` | Cần xác minh trong code | Ngăn Open Redirect qua tham số điều hướng | P0 |
| `ADM-A01-016` | Cần xác minh trong code | Quản lý trạng thái session hết hạn và redirect về login | P0 |
| `ADM-A01-017` | Cần xác minh trong code | Chặn session fixation và xoay session sau login | P0 |
| `ADM-A01-018` | Cần xác minh trong code | Ẩn mật khẩu và thông tin bí mật trong log | P0 |
| `ADM-A01-019` | Cần xác minh trong code | Nút show/hide mật khẩu hoạt động bằng bàn phím | P0 |
| `ADM-A01-020` | Cần xác minh trong code | Trang đăng nhập hỗ trợ 320px và zoom 200% | P0 |
| `ADM-A01-021` | Đề xuất VNext | Bổ sung xác thực mạnh MFA cho admin quyền cao | P2 |
| `ADM-A01-022` | Đề xuất VNext | Phiên quản trị có step-up cho hoàn tiền và xoay secret | P2 |
| `ADM-A01-023` | Đề xuất VNext | Trung tâm phiên hoạt động và thu hồi thiết bị | P2 |
| `ADM-A01-024` | Đề xuất VNext | Cảnh báo đăng nhập bất thường nhưng không tự khóa bừa | P2 |

## A02 — Admin Shell & điều hướng
**Nguồn tài liệu:** `MASTER_PRD §6,§68; ADMIN_ROADMAP GĐ1`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A02-001` | Cần xác minh trong code | Sidebar hiển thị menu theo quyền thực tế | P1 |
| `ADM-A02-002` | Cần xác minh trong code | Sidebar thu gọn và mở rộng không làm vỡ nội dung | P1 |
| `ADM-A02-003` | Cần xác minh trong code | Sidebar nhớ nhóm menu đang mở | P1 |
| `ADM-A02-004` | Cần xác minh trong code | Breadcrumb nhiều cấp và liên kết quay lại | P1 |
| `ADM-A02-005` | Cần xác minh trong code | Topbar hiển thị tài khoản và vai trò thực | P1 |
| `ADM-A02-006` | Cần xác minh trong code | Dropdown tài khoản mở và đóng đúng | P1 |
| `ADM-A02-007` | Cần xác minh trong code | Đăng xuất có xác nhận khi cần | P1 |
| `ADM-A02-008` | Cần xác minh trong code | Link xem website khách ở tab mới an toàn | P1 |
| `ADM-A02-009` | Cần xác minh trong code | Thông báo lỗi/toast không che nút điều khiển | P1 |
| `ADM-A02-010` | Cần xác minh trong code | Menu mobile mở/đóng bằng chạm | P1 |
| `ADM-A02-011` | Cần xác minh trong code | Menu mobile đóng bằng Escape và click ngoài | P1 |
| `ADM-A02-012` | Cần xác minh trong code | Tên trang, tiêu đề và CTA nhất quán | P1 |
| `ADM-A02-013` | Cần xác minh trong code | Menu active chính xác kể cả trang chi tiết | P1 |
| `ADM-A02-014` | Cần xác minh trong code | Giao diện không tràn ngang trên laptop | P1 |
| `ADM-A02-015` | Cần xác minh trong code | Chuẩn token màu kiểu chữ nút chung Admin | P1 |
| `ADM-A02-016` | Cần xác minh trong code | Biểu tượng trạng thái không chỉ dựa vào màu | P1 |
| `ADM-A02-017` | Cần xác minh trong code | Empty/loading/error state của trang shell | P1 |
| `ADM-A02-018` | Cần xác minh trong code | Tải CSS/JS Admin độc lập khỏi Client | P1 |
| `ADM-A02-019` | Đề xuất VNext | Command palette tìm menu và thao tác theo quyền | P3 |
| `ADM-A02-020` | Đề xuất VNext | Recent records theo vai trò và phạm vi được phép | P3 |
| `ADM-A02-021` | Đề xuất VNext | Saved workspace layout và bộ lọc toàn cục | P3 |
| `ADM-A02-022` | Đề xuất VNext | Thông báo tác vụ gắn owner và deep-link xử lý | P3 |
| `ADM-A02-023` | Đề xuất VNext | Cài đặt density chế độ gọn/thoáng không vỡ layout | P3 |

## A03 — Dashboard điều hành
**Nguồn tài liệu:** `MASTER_PRD §7,§70; ADMIN_ROADMAP GĐ1`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A03-001` | Cần xác minh trong code | KPI tổng user mới theo khoảng ngày | P1 |
| `ADM-A03-002` | Cần xác minh trong code | KPI tổng dự án và thiệp đang nháp | P1 |
| `ADM-A03-003` | Cần xác minh trong code | KPI thiệp đã xuất bản và sắp cưới | P1 |
| `ADM-A03-004` | Cần xác minh trong code | KPI đơn paid tách khỏi pending | P1 |
| `ADM-A03-005` | Cần xác minh trong code | KPI doanh thu gross theo VND | P1 |
| `ADM-A03-006` | Cần xác minh trong code | KPI doanh thu net sau refund | P1 |
| `ADM-A03-007` | Cần xác minh trong code | KPI lời chúc cần kiểm duyệt | P1 |
| `ADM-A03-008` | Cần xác minh trong code | KPI cảnh báo thiệp khóa/hết hạn | P1 |
| `ADM-A03-009` | Cần xác minh trong code | KPI ticket cần xử lý theo SLA | P1 |
| `ADM-A03-010` | Cần xác minh trong code | KPI jobs thất bại và webhook lỗi | P1 |
| `ADM-A03-011` | Cần xác minh trong code | Biểu đồ doanh thu theo ngày có trục, đơn vị | P1 |
| `ADM-A03-012` | Cần xác minh trong code | Biểu đồ đăng ký và tạo thiệp có ngày giờ chính xác | P1 |
| `ADM-A03-013` | Cần xác minh trong code | Top mẫu thiệp được sử dụng theo nguồn dữ liệu thật | P1 |
| `ADM-A03-014` | Cần xác minh trong code | Click KPI mở danh sách được lọc đúng | P1 |
| `ADM-A03-015` | Cần xác minh trong code | Chọn khoảng thời gian áp dụng đồng đều widget | P1 |
| `ADM-A03-016` | Cần xác minh trong code | So sánh kỳ trước không chia cho zero | P1 |
| `ADM-A03-017` | Cần xác minh trong code | Widget lỗi riêng không làm sập toàn dashboard | P1 |
| `ADM-A03-018` | Cần xác minh trong code | Thời điểm làm mới dữ liệu hiển thị rõ | P1 |
| `ADM-A03-019` | Cần xác minh trong code | Dashboard có trạng thái không dữ liệu và loading | P1 |
| `ADM-A03-020` | Cần xác minh trong code | Audit snapshot thao tác Admin gần nhất | P1 |
| `ADM-A03-021` | Đề xuất VNext | Action Inbox tự gom việc P0/P1 theo người xử lý | P3 |
| `ADM-A03-022` | Đề xuất VNext | Daily Brief theo role và thời hạn cưới sắp tới | P3 |
| `ADM-A03-023` | Đề xuất VNext | Cảnh báo anomaly dựa trên rule minh bạch | P3 |
| `ADM-A03-024` | Đề xuất VNext | Drill-down KPI đến ticket/record gốc và lý do | P3 |

## A04 — Tài khoản khách hàng
**Nguồn tài liệu:** `MASTER_PRD §8,§44; ADMIN_ROADMAP GĐ5`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A04-001` | Cần xác minh trong code | Tìm user theo tên email ID | P1 |
| `ADM-A04-002` | Cần xác minh trong code | Tìm theo số điện thoại nếu có quyền | P1 |
| `ADM-A04-003` | Cần xác minh trong code | Lọc user theo vai trò | P1 |
| `ADM-A04-004` | Cần xác minh trong code | Lọc user theo verified/locked | P1 |
| `ADM-A04-005` | Cần xác minh trong code | Lọc user theo thời gian đăng ký | P1 |
| `ADM-A04-006` | Cần xác minh trong code | Lọc user theo gói đang dùng | P1 |
| `ADM-A04-007` | Cần xác minh trong code | Sắp xếp user theo ngày tạo hoặc hoạt động | P1 |
| `ADM-A04-008` | Cần xác minh trong code | Trang user có phân trang server | P1 |
| `ADM-A04-009` | Cần xác minh trong code | Thông tin liên hệ masked trong bảng mặc định | P1 |
| `ADM-A04-010` | Cần xác minh trong code | Chi tiết hồ sơ user hiển thị đúng nguồn | P1 |
| `ADM-A04-011` | Cần xác minh trong code | Danh sách thiệp thuộc user chính xác | P1 |
| `ADM-A04-012` | Cần xác minh trong code | Danh sách đơn và trạng thái thanh toán của user | P1 |
| `ADM-A04-013` | Cần xác minh trong code | Lịch sử thao tác và hoạt động hỗ trợ | P1 |
| `ADM-A04-014` | Cần xác minh trong code | Khóa user kèm lý do và audit | P1 |
| `ADM-A04-015` | Cần xác minh trong code | Mở khóa user có quyền và audit | P1 |
| `ADM-A04-016` | Cần xác minh trong code | Đổi role user theo permission server | P1 |
| `ADM-A04-017` | Cần xác minh trong code | Gửi lại xác minh email không gửi trùng | P1 |
| `ADM-A04-018` | Cần xác minh trong code | Đặt lại mật khẩu bằng quy trình an toàn | P1 |
| `ADM-A04-019` | Cần xác minh trong code | Thu hồi session user theo chính sách | P1 |
| `ADM-A04-020` | Cần xác minh trong code | Xuất danh sách user theo quyền và filter | P1 |
| `ADM-A04-021` | Cần xác minh trong code | Không xóa user làm mất chứng từ tài chính | P1 |
| `ADM-A04-022` | Đề xuất VNext | Trang customer 360 liên kết ticket và thông báo | P3 |
| `ADM-A04-023` | Đề xuất VNext | Khả năng xuất dữ liệu cá nhân theo yêu cầu có kiểm soát | P3 |
| `ADM-A04-024` | Đề xuất VNext | Quy trình yêu cầu xóa tài khoản theo retention | P3 |

## A05 — RBAC & nhân viên nội bộ
**Nguồn tài liệu:** `MASTER_PRD §2,§67; context.gemini Cụm D`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A05-001` | Cần xác minh trong code | Role admin được map đúng permission hiện hành | P0 |
| `ADM-A05-002` | Cần xác minh trong code | Role manager được map đúng permission hiện hành | P0 |
| `ADM-A05-003` | Cần xác minh trong code | Role staff được map đúng permission hiện hành | P0 |
| `ADM-A05-004` | Cần xác minh trong code | Role partner chỉ xem dữ liệu tenant cho phép | P0 |
| `ADM-A05-005` | Cần xác minh trong code | Role customer không truy cập Admin nội bộ | P0 |
| `ADM-A05-006` | Cần xác minh trong code | Ẩn hành động trái quyền trên UI | P0 |
| `ADM-A05-007` | Cần xác minh trong code | Backend chặn thao tác trái quyền dù gọi API trực tiếp | P0 |
| `ADM-A05-008` | Cần xác minh trong code | Dữ liệu user của tenant khác bị chặn | P0 |
| `ADM-A05-009` | Cần xác minh trong code | Ngăn tự hạ quyền tài khoản Super Admin cuối | P0 |
| `ADM-A05-010` | Cần xác minh trong code | Ngăn Manager reset password Super Admin | P0 |
| `ADM-A05-011` | Cần xác minh trong code | Log thay đổi vai trò và quyền | P0 |
| `ADM-A05-012` | Cần xác minh trong code | Revoke quyền có hiệu lực với phiên đang hoạt động | P0 |
| `ADM-A05-013` | Cần xác minh trong code | Chặn bulk action vượt quá quyền từng record | P0 |
| `ADM-A05-014` | Cần xác minh trong code | Export dữ liệu kiểm tra lại quyền ở server | P0 |
| `ADM-A05-015` | Cần xác minh trong code | Menu theo vai trò không để route orphan | P0 |
| `ADM-A05-016` | Cần xác minh trong code | Không coi dấu Admin tại frontend là guard server | P0 |
| `ADM-A05-017` | Đề xuất VNext | Permission matrix tự kiểm tra chéo route/action | P2 |
| `ADM-A05-018` | Đề xuất VNext | Phê duyệt hai người cho thao tác rủi ro cực cao | P2 |
| `ADM-A05-019` | Đề xuất VNext | Giới hạn quyền theo thời gian và case hỗ trợ | P2 |
| `ADM-A05-020` | Đề xuất VNext | Kiểm tra drift giữa role đã cấu hình và middleware | P2 |

## A06 — Thiệp cưới và dự án
**Nguồn tài liệu:** `MASTER_PRD §9,§12,§45; ADMIN_ROADMAP GĐ2`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A06-001` | Cần xác minh trong code | Danh sách tất cả thiệp với quyền đúng | P1 |
| `ADM-A06-002` | Cần xác minh trong code | Tìm thiệp theo tên cô dâu chú rể | P1 |
| `ADM-A06-003` | Cần xác minh trong code | Tìm theo slug và ID ổn định | P1 |
| `ADM-A06-004` | Cần xác minh trong code | Lọc theo mẫu thiệp và phiên bản | P1 |
| `ADM-A06-005` | Cần xác minh trong code | Lọc theo trạng thái nháp/xuất bản/khóa/hết hạn | P1 |
| `ADM-A06-006` | Cần xác minh trong code | Lọc theo ngày cưới và ngày tạo | P1 |
| `ADM-A06-007` | Cần xác minh trong code | Danh sách tổng lượt xem không nhân đôi sai | P1 |
| `ADM-A06-008` | Cần xác minh trong code | Trang detail có chủ sở hữu thật | P1 |
| `ADM-A06-009` | Cần xác minh trong code | Trang detail có sự kiện thời gian địa điểm | P1 |
| `ADM-A06-010` | Cần xác minh trong code | Preview nhúng render đúng mẫu native | P1 |
| `ADM-A06-011` | Cần xác minh trong code | Preview không lộ dữ liệu của wedding khác | P1 |
| `ADM-A06-012` | Cần xác minh trong code | Xem sức khỏe ảnh và asset bị lỗi | P1 |
| `ADM-A06-013` | Cần xác minh trong code | Copy URL thiệp đúng slug và domain | P1 |
| `ADM-A06-014` | Cần xác minh trong code | Khóa thiệp có reason và audit | P1 |
| `ADM-A06-015` | Cần xác minh trong code | Mở khóa thiệp theo quyền và audit | P1 |
| `ADM-A06-016` | Cần xác minh trong code | Mọi API phụ của thiệp khóa đều bị chặn theo policy | P1 |
| `ADM-A06-017` | Cần xác minh trong code | Gia hạn thiệp có điều kiện gói và chính sách | P1 |
| `ADM-A06-018` | Cần xác minh trong code | Đổi mẫu giữ dữ liệu và phiên bản phù hợp | P1 |
| `ADM-A06-019` | Cần xác minh trong code | Ẩn/hiện xuất bản không mất bản nháp | P1 |
| `ADM-A06-020` | Cần xác minh trong code | Xuất báo cáo khách theo quyền chủ sở hữu | P1 |
| `ADM-A06-021` | Cần xác minh trong code | Xem lịch sử chỉnh sửa thiệp và hỗ trợ rollback | P1 |
| `ADM-A06-022` | Cần xác minh trong code | Link hỗ trợ tới ticket và case lỗi | P1 |
| `ADM-A06-023` | Đề xuất VNext | Health score từng thiệp kèm bằng chứng rõ | P3 |
| `ADM-A06-024` | Đề xuất VNext | Đánh giá ảnh hưởng trước đổi ngày tiệc hàng loạt | P3 |
| `ADM-A06-025` | Đề xuất VNext | Xem thiệp theo góc khách đọc-only có ủy quyền | P3 |

## A07 — Kho mẫu & phiên bản
**Nguồn tài liệu:** `MASTER_PRD §10,§43; ADMIN_ROADMAP GĐ3`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A07-001` | Cần xác minh trong code | Danh sách mẫu theo tên slug ID | P1 |
| `ADM-A07-002` | Cần xác minh trong code | Lọc theo tier free plus pro | P1 |
| `ADM-A07-003` | Cần xác minh trong code | Lọc theo style category tag | P1 |
| `ADM-A07-004` | Cần xác minh trong code | Lọc mẫu active inactive featured | P1 |
| `ADM-A07-005` | Cần xác minh trong code | Thống kê số thiệp sử dụng mẫu | P1 |
| `ADM-A07-006` | Cần xác minh trong code | Thumbnail và demo mẫu đúng ảnh gốc | P1 |
| `ADM-A07-007` | Cần xác minh trong code | Xem chi tiết schema và field metadata | P1 |
| `ADM-A07-008` | Cần xác minh trong code | Chặn mẫu inactive xuất hiện kho public | P1 |
| `ADM-A07-009` | Cần xác minh trong code | Bật tắt mẫu có permission và audit | P1 |
| `ADM-A07-010` | Cần xác minh trong code | Ghim bỏ ghim mẫu nổi bật có permission | P1 |
| `ADM-A07-011` | Cần xác minh trong code | Sửa thông tin tên mô tả SEO không phá schema | P1 |
| `ADM-A07-012` | Cần xác minh trong code | Kiểm tra schema.json hợp lệ trước công khai | P1 |
| `ADM-A07-013` | Cần xác minh trong code | Kiểm tra đủ field data-sync-target | P1 |
| `ADM-A07-014` | Cần xác minh trong code | Kiểm tra mẫu dùng assets cục bộ tồn tại | P1 |
| `ADM-A07-015` | Cần xác minh trong code | Kiểm tra CSS prefix và extends layout chung | P1 |
| `ADM-A07-016` | Cần xác minh trong code | Kiểm tra embed không khóa cuộn hoặc trắng iframe | P1 |
| `ADM-A07-017` | Cần xác minh trong code | Kiểm tra RSVP và Signature theo skill 10 section | P1 |
| `ADM-A07-018` | Cần xác minh trong code | Import ZIP an toàn nếu chức năng tồn tại | P1 |
| `ADM-A07-019` | Cần xác minh trong code | Thay phiên bản mẫu không sửa mẫu cũ trong khách | P1 |
| `ADM-A07-020` | Cần xác minh trong code | Preview trên mobile desktop trong giao diện Admin | P1 |
| `ADM-A07-021` | Cần xác minh trong code | Rollback version và audit theo policy | P1 |
| `ADM-A07-022` | Cần xác minh trong code | Export danh sách mẫu CSV an toàn | P1 |
| `ADM-A07-023` | Đề xuất VNext | Visual DNA so ảnh trước sau phiên bản mẫu | P3 |
| `ADM-A07-024` | Đề xuất VNext | Quality Passport cho mẫu gồm test thật và screenshot | P3 |
| `ADM-A07-025` | Đề xuất VNext | Canary publish mẫu cho nhóm nội bộ trước khi mở bán | P3 |

## A08 — AI Curated 7 phần native
**Nguồn tài liệu:** `mayhen-invitation-standards; yêu cầu người dùng trong hội thoại`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A08-001` | Cần xác minh trong code | Admin đăng ký danh sách template gốc được cho phép | P1 |
| `ADM-A08-002` | Cần xác minh trong code | Danh sách phong cách lựa chọn cố định của Mây Hẹn | P1 |
| `ADM-A08-003` | Cần xác minh trong code | Danh sách màu người dùng chọn không bị AI sửa | P1 |
| `ADM-A08-004` | Cần xác minh trong code | AI chỉ tạo content và không tạo CSS HTML JS | P1 |
| `ADM-A08-005` | Cần xác minh trong code | Chọn một mẫu gốc duy nhất không trộn các mẫu | P1 |
| `ADM-A08-006` | Cần xác minh trong code | Render đúng template.blade.php mẫu nguồn | P1 |
| `ADM-A08-007` | Cần xác minh trong code | Tải styles.css script.js và assets mẫu nguồn | P1 |
| `ADM-A08-008` | Cần xác minh trong code | Chế độ curated chỉ hiển thị 7 phần bắt buộc | P1 |
| `ADM-A08-009` | Cần xác minh trong code | Hero Cover dùng đúng dữ liệu người nhập | P1 |
| `ADM-A08-010` | Cần xác minh trong code | Love Story ngắn gọn và không bịa dữ liệu | P1 |
| `ADM-A08-011` | Cần xác minh trong code | Family hiển thị đủ tên bố mẹ hai bên | P1 |
| `ADM-A08-012` | Cần xác minh trong code | Countdown và Calendar không bịa ngày | P1 |
| `ADM-A08-013` | Cần xác minh trong code | Events & Maps yêu cầu dữ liệu thật | P1 |
| `ADM-A08-014` | Cần xác minh trong code | Wedding Timeline đúng thứ tự ngày giờ | P1 |
| `ADM-A08-015` | Cần xác minh trong code | Album đúng 10 slot với placeholder trung tính | P1 |
| `ADM-A08-016` | Cần xác minh trong code | Không thêm Dresscode thay cho Album | P1 |
| `ADM-A08-017` | Cần xác minh trong code | Một lần tạo thật có hiệu ứng reveal theo dòng | P1 |
| `ADM-A08-018` | Cần xác minh trong code | F5 và vào lại hiển thị thành phẩm ngay | P1 |
| `ADM-A08-019` | Cần xác minh trong code | Rewatch không gọi API AI hoặc random lại | P1 |
| `ADM-A08-020` | Cần xác minh trong code | Random seed/layout được lưu bền vững | P1 |
| `ADM-A08-021` | Cần xác minh trong code | Giao diện không dựng lại bằng mayhen-curated variant | P1 |
| `ADM-A08-022` | Cần xác minh trong code | Preview same native template as public wedding | P1 |
| `ADM-A08-023` | Cần xác minh trong code | Đổi màu không làm thay nội dung/ảnh | P1 |
| `ADM-A08-024` | Đề xuất VNext | Admin kiểm từng sample batch theo style/màu độ dài | P3 |
| `ADM-A08-025` | Đề xuất VNext | Chẩn đoán lỗi hallucination nội dung và hướng xử lý | P3 |
| `ADM-A08-026` | Đề xuất VNext | AI cost dashboard theo mỗi lượt viết nội dung | P3 |

## A09 — Đơn hàng & đối soát thanh toán
**Nguồn tài liệu:** `MASTER_PRD §14,§46; ADMIN_ROADMAP GĐ4`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A09-001` | Cần xác minh trong code | Danh sách đơn tìm theo orderCode | P0 |
| `ADM-A09-002` | Cần xác minh trong code | Tìm đơn theo user hoặc payment reference | P0 |
| `ADM-A09-003` | Cần xác minh trong code | Lọc đơn theo trạng thái đúng DB | P0 |
| `ADM-A09-004` | Cần xác minh trong code | Lọc ngày đặt đơn và ngày nhận tiền | P0 |
| `ADM-A09-005` | Cần xác minh trong code | Lọc provider VietQR SePay | P0 |
| `ADM-A09-006` | Cần xác minh trong code | Lọc và đối chiếu theo gói cước | P0 |
| `ADM-A09-007` | Cần xác minh trong code | Hiển thị giá và discount snapshot | P0 |
| `ADM-A09-008` | Cần xác minh trong code | Hiển thị amount VND đúng format | P0 |
| `ADM-A09-009` | Cần xác minh trong code | Tạo link chi tiết đơn và copy orderCode | P0 |
| `ADM-A09-010` | Cần xác minh trong code | Chi tiết nhận payment attempts | P0 |
| `ADM-A09-011` | Cần xác minh trong code | Chi tiết log webhook redacted | P0 |
| `ADM-A09-012` | Cần xác minh trong code | Xác thực chữ ký webhook trước cấp gói | P0 |
| `ADM-A09-013` | Cần xác minh trong code | Chống replay timestamp webhook | P0 |
| `ADM-A09-014` | Cần xác minh trong code | Đối chiếu tài khoản nhận và số tiền | P0 |
| `ADM-A09-015` | Cần xác minh trong code | Chặn giao dịch tiền ra khỏi ghi nhận paid | P0 |
| `ADM-A09-016` | Cần xác minh trong code | Chặn double webhook cấp gói hai lần | P0 |
| `ADM-A09-017` | Cần xác minh trong code | Chặn provider ref dùng cho hai đơn | P0 |
| `ADM-A09-018` | Cần xác minh trong code | Cập nhật đơn và entitlement trong transaction | P0 |
| `ADM-A09-019` | Cần xác minh trong code | Đơn cancel nhưng tiền đến xử lý có policy | P0 |
| `ADM-A09-020` | Cần xác minh trong code | Manual reconciliation cần chứng từ và audit | P0 |
| `ADM-A09-021` | Cần xác minh trong code | Manual action không đổi paid bằng checkbox đơn giản | P0 |
| `ADM-A09-022` | Cần xác minh trong code | Lập yêu cầu hoàn tiền đúng state riêng | P0 |
| `ADM-A09-023` | Cần xác minh trong code | Hoàn tiền hiển thị pending vs completed rõ ràng | P0 |
| `ADM-A09-024` | Cần xác minh trong code | Xuất báo cáo đơn đúng filter quyền và CSV safe | P0 |
| `ADM-A09-025` | Cần xác minh trong code | Dashboard doanh thu loại pending và double count | P0 |
| `ADM-A09-026` | Đề xuất VNext | Finance reconciliation detective gợi ý chứng cứ | P2 |
| `ADM-A09-027` | Đề xuất VNext | Hồ sơ review độc lập thao tác tài chính rủi ro cao | P2 |
| `ADM-A09-028` | Đề xuất VNext | Tự phát hiện lệch số liệu báo cáo và ledger | P2 |

## A10 — Gói dịch vụ & quyền lợi
**Nguồn tài liệu:** `MASTER_PRD §13; roadmap gói`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A10-001` | Cần xác minh trong code | Danh sách gói đang hoạt động | P1 |
| `ADM-A10-002` | Cần xác minh trong code | Tạo gói nếu nghiệp vụ cho phép | P1 |
| `ADM-A10-003` | Cần xác minh trong code | Sửa tên mô tả gói và mức giá | P1 |
| `ADM-A10-004` | Cần xác minh trong code | Bật tắt gói không làm mất entitlement cũ | P1 |
| `ADM-A10-005` | Cần xác minh trong code | Phân quyền Free Plus Pro theo cấu hình thật | P1 |
| `ADM-A10-006` | Cần xác minh trong code | Giới hạn số thiệp và thời hạn gói | P1 |
| `ADM-A10-007` | Cần xác minh trong code | Giới hạn album storage theme domain | P1 |
| `ADM-A10-008` | Cần xác minh trong code | Giới hạn tính năng quản lý khách và checkin | P1 |
| `ADM-A10-009` | Cần xác minh trong code | Gia hạn gói cộng ngày đúng khi còn hạn | P1 |
| `ADM-A10-010` | Cần xác minh trong code | Gia hạn gói khi hết hạn tính mốc đúng | P1 |
| `ADM-A10-011` | Cần xác minh trong code | Upgrade không làm mất dữ liệu khách | P1 |
| `ADM-A10-012` | Cần xác minh trong code | Downgrade tuân thủ policy công bố | P1 |
| `ADM-A10-013` | Cần xác minh trong code | Snapshot giá gói tại thời điểm order | P1 |
| `ADM-A10-014` | Cần xác minh trong code | Trang pricing client phản ánh cấu hình gói | P1 |
| `ADM-A10-015` | Cần xác minh trong code | Hiển thị số user/thiệp đang hưởng gói | P1 |
| `ADM-A10-016` | Cần xác minh trong code | Thay quyền lợi gói có impact preview | P1 |
| `ADM-A10-017` | Đề xuất VNext | Promise-to-proof đối chiếu lời quảng cáo và gói thật | P3 |
| `ADM-A10-018` | Đề xuất VNext | Phiên bản hóa policy entitlement và rollout | P3 |
| `ADM-A10-019` | Đề xuất VNext | Công cụ cảnh báo quota gần hết trước ngày cưới | P3 |

## A11 — Coupon & khuyến mại
**Nguồn tài liệu:** `MASTER_PRD §15`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A11-001` | Cần xác minh trong code | Tạo coupon mã duy nhất | P1 |
| `ADM-A11-002` | Cần xác minh trong code | Cấu hình % và số tiền giảm | P1 |
| `ADM-A11-003` | Cần xác minh trong code | Ngày bắt đầu kết thúc theo giờ Việt Nam | P1 |
| `ADM-A11-004` | Cần xác minh trong code | Giới hạn lượt dùng toàn hệ thống | P1 |
| `ADM-A11-005` | Cần xác minh trong code | Giới hạn lượt dùng mỗi user | P1 |
| `ADM-A11-006` | Cần xác minh trong code | Giá trị đơn tối thiểu | P1 |
| `ADM-A11-007` | Cần xác minh trong code | Giảm tối đa và quy tắc làm tròn | P1 |
| `ADM-A11-008` | Cần xác minh trong code | Áp dụng theo gói hoặc phân nhóm | P1 |
| `ADM-A11-009` | Cần xác minh trong code | Chặn mã hết hạn bị dùng | P1 |
| `ADM-A11-010` | Cần xác minh trong code | Chặn mã đã hết lượt bị dùng | P1 |
| `ADM-A11-011` | Cần xác minh trong code | Chống cộng dồn không theo chính sách | P1 |
| `ADM-A11-012` | Cần xác minh trong code | Theo dõi số lượt dùng thật | P1 |
| `ADM-A11-013` | Cần xác minh trong code | Xem doanh thu và ảnh hưởng của coupon | P1 |
| `ADM-A11-014` | Cần xác minh trong code | Tạm khóa campaign và audit | P1 |
| `ADM-A11-015` | Cần xác minh trong code | Export kết quả không lộ dữ liệu riêng | P1 |
| `ADM-A11-016` | Đề xuất VNext | Preview cart và giá cuối cùng trước phát campaign | P3 |
| `ADM-A11-017` | Đề xuất VNext | A/B test ưu đãi không gây phân biệt sai giá | P3 |

## A12 — Agency & đối tác
**Nguồn tài liệu:** `MASTER_PRD §9; VNext Partner`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A12-001` | Cần xác minh trong code | Danh sách đối tác theo trạng thái | P1 |
| `ADM-A12-002` | Cần xác minh trong code | Hồ sơ đối tác và người chịu trách nhiệm | P1 |
| `ADM-A12-003` | Cần xác minh trong code | Onboarding yêu cầu thông tin hợp lệ | P1 |
| `ADM-A12-004` | Cần xác minh trong code | Tài khoản partner chỉ thấy dự án của họ | P1 |
| `ADM-A12-005` | Cần xác minh trong code | Danh sách project thuộc studio đúng tenant | P1 |
| `ADM-A12-006` | Cần xác minh trong code | Phân quyền thành viên trong partner | P1 |
| `ADM-A12-007` | Cần xác minh trong code | Tạo mã bàn giao cho customer đúng owner | P1 |
| `ADM-A12-008` | Cần xác minh trong code | Hạn mã bàn giao và hiển thị countdown | P1 |
| `ADM-A12-009` | Cần xác minh trong code | Thu hồi mã bàn giao có audit | P1 |
| `ADM-A12-010` | Cần xác minh trong code | Claim mã bàn giao chuyển ownership atomic | P1 |
| `ADM-A12-011` | Cần xác minh trong code | Mã đã claim không thể claim lại | P1 |
| `ADM-A12-012` | Cần xác minh trong code | Partner cũ không truy cập sau bàn giao | P1 |
| `ADM-A12-013` | Cần xác minh trong code | Lịch sử handover không bị mất | P1 |
| `ADM-A12-014` | Cần xác minh trong code | Chặn studio A truy cập studio B | P1 |
| `ADM-A12-015` | Cần xác minh trong code | Domain riêng xác minh CNAME đúng | P1 |
| `ADM-A12-016` | Cần xác minh trong code | Chặn reserved domain và slugs nội bộ | P1 |
| `ADM-A12-017` | Cần xác minh trong code | Domain status lỗi DNS có thông báo rõ | P1 |
| `ADM-A12-018` | Cần xác minh trong code | Commission theo thực tế nếu được cấu hình | P1 |
| `ADM-A12-019` | Đề xuất VNext | Agency health dashboard dự án sắp cưới | P3 |
| `ADM-A12-020` | Đề xuất VNext | Client sign-off trước handover với preview | P3 |
| `ADM-A12-021` | Đề xuất VNext | Checklist QA bàn giao khách hàng tự động có bằng chứng | P3 |

## A13 — Guest CRM & RSVP
**Nguồn tài liệu:** `MASTER_PRD §28-31; Cụm A`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A13-001` | Cần xác minh trong code | Danh sách khách mời từng wedding đúng quyền | P1 |
| `ADM-A13-002` | Cần xác minh trong code | Tìm khách theo tên hoặc mã riêng | P1 |
| `ADM-A13-003` | Cần xác minh trong code | Phân nhóm nhà trai nhà gái gia đình bạn bè | P1 |
| `ADM-A13-004` | Cần xác minh trong code | Thêm khách mới có kiểm trùng hợp lý | P1 |
| `ADM-A13-005` | Cần xác minh trong code | Import CSV kiểm định cột và mã hóa UTF8 | P1 |
| `ADM-A13-006` | Cần xác minh trong code | Import bỏ qua hoặc cập nhật người trùng | P1 |
| `ADM-A13-007` | Cần xác minh trong code | Import có progress và error report | P1 |
| `ADM-A13-008` | Cần xác minh trong code | Xuất CSV guest đúng tenant và filter | P1 |
| `ADM-A13-009` | Cần xác minh trong code | Tạo link mời cá nhân hóa với token an toàn | P1 |
| `ADM-A13-010` | Cần xác minh trong code | Theo dõi trạng thái gửi lời mời nếu provider hỗ trợ | P1 |
| `ADM-A13-011` | Cần xác minh trong code | RSVP attending hiển thị người lớn và trẻ em | P1 |
| `ADM-A13-012` | Cần xác minh trong code | RSVP declined không chiếm bàn | P1 |
| `ADM-A13-013` | Cần xác minh trong code | RSVP uncertain không tính đã đến | P1 |
| `ADM-A13-014` | Cần xác minh trong code | Nhiều khách cùng NAT không bị chặn nhầm | P1 |
| `ADM-A13-015` | Cần xác minh trong code | Khách cập nhật RSVP không tạo bản duplicate | P1 |
| `ADM-A13-016` | Cần xác minh trong code | Xóa guest không để phản hồi mồ côi | P1 |
| `ADM-A13-017` | Cần xác minh trong code | Tăng headcount phản ánh seating capacity | P1 |
| `ADM-A13-018` | Cần xác minh trong code | Giảm headcount giải phóng ghế đúng | P1 |
| `ADM-A13-019` | Cần xác minh trong code | Tên và số điện thoại guest được mask theo quyền | P1 |
| `ADM-A13-020` | Cần xác minh trong code | Thống kê đã xác nhận theo wedding và event | P1 |
| `ADM-A13-021` | Đề xuất VNext | Bộ phát hiện guest invitation không được mở | P3 |
| `ADM-A13-022` | Đề xuất VNext | Sơ đồ hành trình khách giả lập không thu thập PII | P3 |

## A14 — Seating & check-in
**Nguồn tài liệu:** `MASTER_PRD §32-33; Cụm B`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A14-001` | Cần xác minh trong code | Tạo sửa xóa bàn tiệc trong phạm vi wedding | P1 |
| `ADM-A14-002` | Cần xác minh trong code | Thiết lập sức chứa từng bàn | P1 |
| `ADM-A14-003` | Cần xác minh trong code | Gán khách vào bàn không quá sức chứa | P1 |
| `ADM-A14-004` | Cần xác minh trong code | Di chuyển khách giữa bàn cập nhật tổng | P1 |
| `ADM-A14-005` | Cần xác minh trong code | Chuyển RSVP declined giải phóng chỗ | P1 |
| `ADM-A14-006` | Cần xác minh trong code | Chuyển RSVP attending cảnh báo hết chỗ | P1 |
| `ADM-A14-007` | Cần xác minh trong code | Tìm khách nhanh ở bàn tiệc | P1 |
| `ADM-A14-008` | Cần xác minh trong code | Phân bàn theo nhóm gia đình đúng yêu cầu | P1 |
| `ADM-A14-009` | Cần xác minh trong code | Quét QR thành công cập nhật check-in thật | P1 |
| `ADM-A14-010` | Cần xác minh trong code | Quét lại QR không tăng người đến | P1 |
| `ADM-A14-011` | Cần xác minh trong code | Token QR sai hoặc expired bị từ chối | P1 |
| `ADM-A14-012` | Cần xác minh trong code | Walk-in guest được tạo có phân quyền | P1 |
| `ADM-A14-013` | Cần xác minh trong code | Tổng số đã đến không vượt quá dữ liệu hợp lệ | P1 |
| `ADM-A14-014` | Cần xác minh trong code | Các quầy check-in đồng thời không double count | P1 |
| `ADM-A14-015` | Cần xác minh trong code | Không lộ danh sách guest trên màn lễ tân công khai | P1 |
| `ADM-A14-016` | Cần xác minh trong code | Reception desk UI mobile và camera permission | P1 |
| `ADM-A14-017` | Cần xác minh trong code | Các thay đổi check-in có lịch sử ai quét | P1 |
| `ADM-A14-018` | Cần xác minh trong code | Lỗi offline không giả báo đã check-in thành công | P1 |
| `ADM-A14-019` | Đề xuất VNext | Rescue Mode khôi phục khi lễ tân gặp sự cố | P3 |
| `ADM-A14-020` | Đề xuất VNext | Bàn điều phối theo sự kiện và cổng checkin | P3 |
| `ADM-A14-021` | Đề xuất VNext | Rehearsal synthetic QR không ghi dữ liệu thật | P3 |

## A15 — Guestbook & kiểm duyệt
**Nguồn tài liệu:** `MASTER_PRD §17,§34; ADMIN_ROADMAP GĐ6`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A15-001` | Cần xác minh trong code | Liệt kê lời chúc theo thiệp và status | P1 |
| `ADM-A15-002` | Cần xác minh trong code | Lọc lời chúc chờ duyệt và bị báo cáo | P1 |
| `ADM-A15-003` | Cần xác minh trong code | Kiểm duyệt tiếng Việt có dấu không dấu hợp lý | P1 |
| `ADM-A15-004` | Cần xác minh trong code | Từ ngữ lành tính không bị chặn false positive | P1 |
| `ADM-A15-005` | Cần xác minh trong code | Chặn nội dung XSS và URL độc hại | P1 |
| `ADM-A15-006` | Cần xác minh trong code | Duyệt lời chúc có permission | P1 |
| `ADM-A15-007` | Cần xác minh trong code | Ẩn lời chúc không xóa vĩnh viễn không lý do | P1 |
| `ADM-A15-008` | Cần xác minh trong code | Xóa lời chúc theo policy và audit | P1 |
| `ADM-A15-009` | Cần xác minh trong code | Đồng bộ trạng thái guestbook public | P1 |
| `ADM-A15-010` | Cần xác minh trong code | Chủ thiệp trả lời lời chúc đúng quyền | P1 |
| `ADM-A15-011` | Cần xác minh trong code | Chặn reply trên lời chúc wedding khác | P1 |
| `ADM-A15-012` | Cần xác minh trong code | Quản lý chữ ký điện tử theo wedding | P1 |
| `ADM-A15-013` | Cần xác minh trong code | Signature content không hiển thị ảnh người lạ | P1 |
| `ADM-A15-014` | Cần xác minh trong code | Khiếu nại lời chúc lưu lịch sử | P1 |
| `ADM-A15-015` | Cần xác minh trong code | Kiểm duyệt không lộ lời chúc riêng tư sang website | P1 |
| `ADM-A15-016` | Đề xuất VNext | Queue kiểm duyệt ưu tiên thiệp sắp diễn ra | P3 |
| `ADM-A15-017` | Đề xuất VNext | Giải thích nguyên nhân flag và khôi phục nhầm | P3 |

## A16 — Photo Drop Live Wall & quà mừng
**Nguồn tài liệu:** `MASTER_PRD §34-36; Premium`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A16-001` | Cần xác minh trong code | Album khách gửi phân quyền theo wedding | P1 |
| `ADM-A16-002` | Cần xác minh trong code | Upload media đúng MIME và kích thước | P1 |
| `ADM-A16-003` | Cần xác minh trong code | Retry upload không tự nhân đôi ảnh | P1 |
| `ADM-A16-004` | Cần xác minh trong code | Ẩn ảnh không còn xuất hiện trong public gallery | P1 |
| `ADM-A16-005` | Cần xác minh trong code | Xóa ảnh theo retention không mất audit | P1 |
| `ADM-A16-006` | Cần xác minh trong code | Live Wall PIN có hạn và rate limit | P1 |
| `ADM-A16-007` | Cần xác minh trong code | Live Wall không hiện lời chúc chưa duyệt | P1 |
| `ADM-A16-008` | Cần xác minh trong code | Thiệp khóa không truy cập API Live Wall | P1 |
| `ADM-A16-009` | Cần xác minh trong code | Lọc hình ảnh lỗi và đường dẫn asset mất | P1 |
| `ADM-A16-010` | Cần xác minh trong code | Sổ tiền mừng cưới chủ thiệp phân quyền | P1 |
| `ADM-A16-011` | Cần xác minh trong code | Sổ quà mừng không trộn doanh thu nền tảng | P1 |
| `ADM-A16-012` | Cần xác minh trong code | Xuất sổ quà mừng CSV chống formula injection | P1 |
| `ADM-A16-013` | Cần xác minh trong code | Bản in A4 tổng hợp tiền mừng đúng số | P1 |
| `ADM-A16-014` | Cần xác minh trong code | Trạng thái đã cảm ơn từng khách được cập nhật | P1 |
| `ADM-A16-015` | Cần xác minh trong code | Banking QR cô dâu chú rể không lộ trên log | P1 |
| `ADM-A16-016` | Đề xuất VNext | Ảnh gửi chất lượng kém cảnh báo trước phát màn hình | P3 |
| `ADM-A16-017` | Đề xuất VNext | Báo cáo kỷ niệm dành cho chủ thiệp được xuất riêng | P3 |

## A17 — Sau cưới & kỷ niệm
**Nguồn tài liệu:** `MASTER_PRD §36; Premium VNext`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A17-001` | Cần xác minh trong code | Memory Vault đúng chủ thiệp và tenant | P1 |
| `ADM-A17-002` | Cần xác minh trong code | Time Capsule niêm phong không lộ content | P1 |
| `ADM-A17-003` | Cần xác minh trong code | Time Capsule chỉ mở từ unlock date | P1 |
| `ADM-A17-004` | Cần xác minh trong code | Calendar timezone thống nhất khi mở time capsule | P1 |
| `ADM-A17-005` | Cần xác minh trong code | Không mở sớm qua API hoặc preview | P1 |
| `ADM-A17-006` | Cần xác minh trong code | Album ảnh sau cưới giữ quyền riêng tư | P1 |
| `ADM-A17-007` | Cần xác minh trong code | Gộp lời chúc đã duyệt vào keepsake book | P1 |
| `ADM-A17-008` | Cần xác minh trong code | Xuất kỷ yếu có bản quyền ảnh rõ ràng | P1 |
| `ADM-A17-009` | Cần xác minh trong code | Gỡ consent ảnh phải ẩn ở mọi nơi | P1 |
| `ADM-A17-010` | Cần xác minh trong code | Lịch sử thay đổi retention data | P1 |
| `ADM-A17-011` | Cần xác minh trong code | Hết hạn gói không âm thầm xóa kỷ niệm | P1 |
| `ADM-A17-012` | Cần xác minh trong code | Tài khoản chuyển owner không leak Time Capsule | P1 |
| `ADM-A17-013` | Đề xuất VNext | Sau cưới theo dõi lời hứa retention có bằng chứng | P3 |
| `ADM-A17-014` | Đề xuất VNext | Hộp bàn giao dữ liệu gia đình sau sự kiện an toàn | P3 |

## A18 — Trung tâm hỗ trợ Ticket & SLA
**Nguồn tài liệu:** `MASTER_PRD §17,§47`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A18-001` | Cần xác minh trong code | Nhận ticket từ form hỗ trợ | P1 |
| `ADM-A18-002` | Cần xác minh trong code | Sinh mã ticket duy nhất | P1 |
| `ADM-A18-003` | Cần xác minh trong code | Lọc ticket theo trạng thái và priority | P1 |
| `ADM-A18-004` | Cần xác minh trong code | Lọc ticket theo assignee và thời hạn SLA | P1 |
| `ADM-A18-005` | Cần xác minh trong code | Tìm ticket theo mã và khách | P1 |
| `ADM-A18-006` | Cần xác minh trong code | Gán ticket cho nhân viên có quyền | P1 |
| `ADM-A18-007` | Cần xác minh trong code | Chuyển trạng thái triage in-progress waiting | P1 |
| `ADM-A18-008` | Cần xác minh trong code | Gửi phản hồi khách từ template đúng | P1 |
| `ADM-A18-009` | Cần xác minh trong code | Internal note tuyệt đối không lộ phía customer | P1 |
| `ADM-A18-010` | Cần xác minh trong code | Đính kèm file scan và kiểm tra quyền | P1 |
| `ADM-A18-011` | Cần xác minh trong code | Link ticket tới account và thiệp | P1 |
| `ADM-A18-012` | Cần xác minh trong code | Link ticket tới order và payment case | P1 |
| `ADM-A18-013` | Cần xác minh trong code | Nhắc SLA theo thời gian làm việc thật | P1 |
| `ADM-A18-014` | Cần xác minh trong code | Đóng ticket lưu lịch sử | P1 |
| `ADM-A18-015` | Cần xác minh trong code | Reopen ticket có audit | P1 |
| `ADM-A18-016` | Cần xác minh trong code | Trạng thái gửi tin không giả thành công | P1 |
| `ADM-A18-017` | Cần xác minh trong code | Hỗ trợ xem customer read-only cần scope và approval | P1 |
| `ADM-A18-018` | Cần xác minh trong code | Phân biệt escalation kỹ thuật và tài chính | P1 |
| `ADM-A18-019` | Đề xuất VNext | One-Case Timeline tổng hợp dấu vết có redaction | P3 |
| `ADM-A18-020` | Đề xuất VNext | Từ ticket sang QA bug/regression cùng ID | P3 |
| `ADM-A18-021` | Đề xuất VNext | Cảnh báo hỗ trợ VIP gần ngày cưới | P3 |

## A19 — CMS & SEO
**Nguồn tài liệu:** `MASTER_PRD §16`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A19-001` | Cần xác minh trong code | Danh sách trang tĩnh Mây Hẹn | P1 |
| `ADM-A19-002` | Cần xác minh trong code | Sửa nội dung trang tĩnh có preview | P1 |
| `ADM-A19-003` | Cần xác minh trong code | Blog draft và publish theo quyền | P1 |
| `ADM-A19-004` | Cần xác minh trong code | Category tag author của bài viết | P1 |
| `ADM-A19-005` | Cần xác minh trong code | SEO meta title/description được validate | P1 |
| `ADM-A19-006` | Cần xác minh trong code | Canonical link đúng origin và slug | P1 |
| `ADM-A19-007` | Cần xác minh trong code | OG image theo bài viết và page | P1 |
| `ADM-A19-008` | Cần xác minh trong code | Schema JSONLD hợp lệ không bịa rating | P1 |
| `ADM-A19-009` | Cần xác minh trong code | Sitemap chỉ gồm URL public đủ điều kiện | P1 |
| `ADM-A19-010` | Cần xác minh trong code | Robots chặn admin studio checkout/private thiệp | P1 |
| `ADM-A19-011` | Cần xác minh trong code | Redirect 301 302 phát hiện vòng lặp | P1 |
| `ADM-A19-012` | Cần xác minh trong code | Link checker phát hiện URL gãy | P1 |
| `ADM-A19-013` | Cần xác minh trong code | Quản lý FAQ có thứ tự | P1 |
| `ADM-A19-014` | Cần xác minh trong code | Bật tắt banner theo thời gian | P1 |
| `ADM-A19-015` | Cần xác minh trong code | Preview campaign trên mobile/desktop | P1 |
| `ADM-A19-016` | Cần xác minh trong code | Form lead có consent và chống spam | P1 |
| `ADM-A19-017` | Cần xác minh trong code | Gán owner và status cho lead | P1 |
| `ADM-A19-018` | Cần xác minh trong code | Export lead theo permission | P1 |
| `ADM-A19-019` | Đề xuất VNext | Promise-to-proof scanner kiểm câu quảng cáo gói | P3 |
| `ADM-A19-020` | Đề xuất VNext | Quality score SEO page có bằng chứng rõ | P3 |

## A20 — Thông báo & Campaign
**Nguồn tài liệu:** `MASTER_PRD §18`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A20-001` | Cần xác minh trong code | Quản lý template email và biến template | P1 |
| `ADM-A20-002` | Cần xác minh trong code | Chạy preview tin với dữ liệu fake an toàn | P1 |
| `ADM-A20-003` | Cần xác minh trong code | Cấu hình sender theo quyền admin | P1 |
| `ADM-A20-004` | Cần xác minh trong code | Chặn gửi khi chưa có consent phù hợp | P1 |
| `ADM-A20-005` | Cần xác minh trong code | Quản lý unsubscribe nếu thông điệp áp dụng | P1 |
| `ADM-A20-006` | Cần xác minh trong code | Tạo chiến dịch theo nhóm khách có quyền | P1 |
| `ADM-A20-007` | Cần xác minh trong code | Thiết lập scheduled send timezone Việt Nam | P1 |
| `ADM-A20-008` | Cần xác minh trong code | Xem queued sent delivered failed độc lập | P1 |
| `ADM-A20-009` | Cần xác minh trong code | Retry failed chỉ gửi lại item lỗi | P1 |
| `ADM-A20-010` | Cần xác minh trong code | Ngăn double send do thao tác nhiều lần | P1 |
| `ADM-A20-011` | Cần xác minh trong code | Hiển thị giá/chi phí provider khi biết được | P1 |
| `ADM-A20-012` | Cần xác minh trong code | Nhật ký gửi tin redacted không lộ PII | P1 |
| `ADM-A20-013` | Cần xác minh trong code | Cảnh báo quota và rate limit | P1 |
| `ADM-A20-014` | Cần xác minh trong code | Test send chỉ gửi tới đích thử được phép | P1 |
| `ADM-A20-015` | Cần xác minh trong code | Chặn thông báo sai từ biến template thiếu | P1 |
| `ADM-A20-016` | Cần xác minh trong code | Không báo delivered nếu provider chỉ trả accepted | P1 |
| `ADM-A20-017` | Đề xuất VNext | Zero-surprise campaign preview impact và cost | P3 |
| `ADM-A20-018` | Đề xuất VNext | Mô phỏng chiến dịch trên danh sách test cô lập | P3 |
| `ADM-A20-019` | Đề xuất VNext | Actionable notification rules per role | P3 |

## A21 — Job Center & hệ thống
**Nguồn tài liệu:** `MASTER_PRD §18,§48`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A21-001` | Cần xác minh trong code | Danh sách jobs và batches | P1 |
| `ADM-A21-002` | Cần xác minh trong code | Lọc queued processing completed failed | P1 |
| `ADM-A21-003` | Cần xác minh trong code | Báo tiến độ done total thực | P1 |
| `ADM-A21-004` | Cần xác minh trong code | Gắn tên batch giữ sau F5 | P1 |
| `ADM-A21-005` | Cần xác minh trong code | Xem lỗi job được redacted | P1 |
| `ADM-A21-006` | Cần xác minh trong code | Retry job failed theo permission | P1 |
| `ADM-A21-007` | Cần xác minh trong code | Không retry item đã gửi thành công | P1 |
| `ADM-A21-008` | Cần xác minh trong code | Hủy job chưa chạy khi an toàn | P1 |
| `ADM-A21-009` | Cần xác minh trong code | Thời gian enqueue start end chính xác | P1 |
| `ADM-A21-010` | Cần xác minh trong code | Nhận biết job stuck và queue delay | P1 |
| `ADM-A21-011` | Cần xác minh trong code | Hiển thị trạng thái worker/cron nếu có telemetry | P1 |
| `ADM-A21-012` | Cần xác minh trong code | Kiểm tra webhook failure không lộ signature | P1 |
| `ADM-A21-013` | Cần xác minh trong code | Cảnh báo file broken asset tổng quan | P1 |
| `ADM-A21-014` | Cần xác minh trong code | Cảnh báo storage gần ngưỡng | P1 |
| `ADM-A21-015` | Cần xác minh trong code | Incident log có owner và severity | P1 |
| `ADM-A21-016` | Cần xác minh trong code | Xuất report lỗi không lộ token | P1 |
| `ADM-A21-017` | Cần xác minh trong code | Link job tới user/template/wedding liên quan | P1 |
| `ADM-A21-018` | Đề xuất VNext | Incident-to-action cockpit liên kết ticket fix evidence | P3 |
| `ADM-A21-019` | Đề xuất VNext | Alert dedupe và re-notify theo SLA | P3 |
| `ADM-A21-020` | Đề xuất VNext | Playbook phục hồi khi background workers chết | P3 |

## A22 — Media & Storage
**Nguồn tài liệu:** `MASTER_PRD §18,§20`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A22-001` | Cần xác minh trong code | Danh sách media theo tenant owner | P1 |
| `ADM-A22-002` | Cần xác minh trong code | Lọc media theo loại và ngày upload | P1 |
| `ADM-A22-003` | Cần xác minh trong code | Hiển thị dung lượng file và quota | P1 |
| `ADM-A22-004` | Cần xác minh trong code | Thống kê tổng storage theo thực tế | P1 |
| `ADM-A22-005` | Cần xác minh trong code | Thumbnail render không ảnh gãy | P1 |
| `ADM-A22-006` | Cần xác minh trong code | Kiểm tra MIME type cả file content | P1 |
| `ADM-A22-007` | Cần xác minh trong code | Chặn upload file executable masquerading image | P1 |
| `ADM-A22-008` | Cần xác minh trong code | Chặn truy cập private media không có quyền | P1 |
| `ADM-A22-009` | Cần xác minh trong code | Tạo derivative ảnh có kiểm quality | P1 |
| `ADM-A22-010` | Cần xác minh trong code | Tìm orphan asset theo reference thật | P1 |
| `ADM-A22-011` | Cần xác minh trong code | Dry run dọn media trước khi delete | P1 |
| `ADM-A22-012` | Cần xác minh trong code | Không xóa file còn được wedding dùng | P1 |
| `ADM-A22-013` | Cần xác minh trong code | Restore asset từ quarantine khi có chính sách | P1 |
| `ADM-A22-014` | Cần xác minh trong code | Checksum chống trùng theo phạm vi phù hợp | P1 |
| `ADM-A22-015` | Cần xác minh trong code | Export báo cáo media health | P1 |
| `ADM-A22-016` | Cần xác minh trong code | Không cho support xem media riêng tư tùy tiện | P1 |
| `ADM-A22-017` | Đề xuất VNext | Media impact graph chỉ asset dùng ở đâu | P3 |
| `ADM-A22-018` | Đề xuất VNext | Dự báo dung lượng theo số đám cưới sắp tới | P3 |

## A23 — Analytics & báo cáo
**Nguồn tài liệu:** `MASTER_PRD §19,§49`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A23-001` | Cần xác minh trong code | Báo cáo người dùng theo ngày tuần tháng | P1 |
| `ADM-A23-002` | Cần xác minh trong code | Báo cáo bắt đầu tạo thiệp và publish | P1 |
| `ADM-A23-003` | Cần xác minh trong code | Báo cáo conversion browse template to paid | P1 |
| `ADM-A23-004` | Cần xác minh trong code | Báo cáo nguồn signup nếu có attribution | P1 |
| `ADM-A23-005` | Cần xác minh trong code | Báo cáo mẫu có view nhưng ít mua | P1 |
| `ADM-A23-006` | Cần xác minh trong code | Báo cáo gross revenue | P1 |
| `ADM-A23-007` | Cần xác minh trong code | Báo cáo discount và refunds | P1 |
| `ADM-A23-008` | Cần xác minh trong code | Báo cáo net revenue | P1 |
| `ADM-A23-009` | Cần xác minh trong code | Báo cáo số đơn paid pending | P1 |
| `ADM-A23-010` | Cần xác minh trong code | Báo cáo RSVP và guest headcount tổng | P1 |
| `ADM-A23-011` | Cần xác minh trong code | Báo cáo checkin theo event | P1 |
| `ADM-A23-012` | Cần xác minh trong code | Báo cáo ticket SLA và thời gian giải quyết | P1 |
| `ADM-A23-013` | Cần xác minh trong code | Báo cáo queue errors và background job health | P1 |
| `ADM-A23-014` | Cần xác minh trong code | Tất cả report có filter date theo giờ Việt Nam | P1 |
| `ADM-A23-015` | Cần xác minh trong code | So kỳ trước không chia cho zero | P1 |
| `ADM-A23-016` | Cần xác minh trong code | Click biểu đồ drilldown đúng dữ liệu | P1 |
| `ADM-A23-017` | Cần xác minh trong code | Export filtered report theo quyền | P1 |
| `ADM-A23-018` | Cần xác minh trong code | Không hiển thị dữ liệu giả ở empty state | P1 |
| `ADM-A23-019` | Cần xác minh trong code | Định nghĩa KPI và nguồn dữ liệu bên cạnh biểu đồ | P1 |
| `ADM-A23-020` | Đề xuất VNext | Funnel friction analysis theo dữ liệu đã consent | P3 |
| `ADM-A23-021` | Đề xuất VNext | Đối chiếu báo cáo với ledger có cảnh báo drift | P3 |
| `ADM-A23-022` | Đề xuất VNext | Bản brief ngày theo vai trò cho admin | P3 |

## A24 — Settings Secrets Feature Flags
**Nguồn tài liệu:** `MASTER_PRD §20,§50`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A24-001` | Cần xác minh trong code | Cấu hình thương hiệu website | P0 |
| `ADM-A24-002` | Cần xác minh trong code | Cấu hình logo favicon và SEO mặc định | P0 |
| `ADM-A24-003` | Cần xác minh trong code | Cấu hình gói cước trong Settings | P0 |
| `ADM-A24-004` | Cần xác minh trong code | Cấu hình tài khoản nhận thanh toán riêng platform | P0 |
| `ADM-A24-005` | Cần xác minh trong code | Cấu hình provider SePay có secrets masked | P0 |
| `ADM-A24-006` | Cần xác minh trong code | Cấu hình email provider có test connection | P0 |
| `ADM-A24-007` | Cần xác minh trong code | Cấu hình storage và media quota | P0 |
| `ADM-A24-008` | Cần xác minh trong code | Cấu hình chính sách moderation | P0 |
| `ADM-A24-009` | Cần xác minh trong code | Cấu hình privacy retention policy | P0 |
| `ADM-A24-010` | Cần xác minh trong code | Cấu hình rate limits hợp lý | P0 |
| `ADM-A24-011` | Cần xác minh trong code | Cấu hình API/webhook keys không xuất secrets | P0 |
| `ADM-A24-012` | Cần xác minh trong code | Form secret để trống không xóa giá trị cũ | P0 |
| `ADM-A24-013` | Cần xác minh trong code | Preview diff trước khi save setting nguy hiểm | P0 |
| `ADM-A24-014` | Cần xác minh trong code | Validation setting sai không ảnh hưởng live | P0 |
| `ADM-A24-015` | Cần xác minh trong code | Ghi audit trước sau thay đổi đã mask | P0 |
| `ADM-A24-016` | Cần xác minh trong code | Rollback config có version và reason | P0 |
| `ADM-A24-017` | Cần xác minh trong code | Feature flag có scope user workspace tier nếu có | P0 |
| `ADM-A24-018` | Cần xác minh trong code | Kill switch tắt tính năng rủi ro nhanh | P0 |
| `ADM-A24-019` | Cần xác minh trong code | Cấu hình backup và xem lần backup gần nhất | P0 |
| `ADM-A24-020` | Đề xuất VNext | Impact simulator khi cập nhật cấu hình toàn nền tảng | P2 |
| `ADM-A24-021` | Đề xuất VNext | Phiên duyệt critical config trước publish | P2 |

## A25 — Audit Security Privacy Release
**Nguồn tài liệu:** `MASTER_PRD §41,§62-70; QA findings 10/11`

| ID | Phân loại | Yêu cầu/thao tác vi mô | P |
|---|---|---|---|
| `ADM-A25-001` | Cần xác minh trong code | Audit trail actor/action/target/time | P0 |
| `ADM-A25-002` | Cần xác minh trong code | Audit log có lý do cho thao tác nhạy cảm | P0 |
| `ADM-A25-003` | Cần xác minh trong code | Audit log lọc user action target ngày | P0 |
| `ADM-A25-004` | Cần xác minh trong code | Audit log được redacted không chứa secrets | P0 |
| `ADM-A25-005` | Cần xác minh trong code | Export audit log có permission | P0 |
| `ADM-A25-006` | Cần xác minh trong code | Chặn sửa/xóa audit log tùy tiện | P0 |
| `ADM-A25-007` | Cần xác minh trong code | Chống XSS trong tên khách và ticket note | P0 |
| `ADM-A25-008` | Cần xác minh trong code | Chống CSV formula injection khi xuất dữ liệu | P0 |
| `ADM-A25-009` | Cần xác minh trong code | Chống IDOR user wedding agency payment | P0 |
| `ADM-A25-010` | Cần xác minh trong code | Chống CSRF với mutation Admin | P0 |
| `ADM-A25-011` | Cần xác minh trong code | Chống SQL injection filter sort search | P0 |
| `ADM-A25-012` | Cần xác minh trong code | Chống mass assignment trên model nhạy cảm | P0 |
| `ADM-A25-013` | Cần xác minh trong code | Chống path traversal qua import asset | P0 |
| `ADM-A25-014` | Cần xác minh trong code | Chặn lộ environment hoặc private config | P0 |
| `ADM-A25-015` | Cần xác minh trong code | Quy trình data export user có audit | P0 |
| `ADM-A25-016` | Cần xác minh trong code | Quy trình data deletion có kiểm pháp lý retention | P0 |
| `ADM-A25-017` | Cần xác minh trong code | Backup restore đã có proof khi nghiệm thu | P0 |
| `ADM-A25-018` | Cần xác minh trong code | Migrations bảo vệ dữ liệu có rollback plan | P0 |
| `ADM-A25-019` | Cần xác minh trong code | Check quyền từ browser và API cross tenant | P0 |
| `ADM-A25-020` | Cần xác minh trong code | Release checklist và smoke test các route admin | P0 |
| `ADM-A25-021` | Cần xác minh trong code | Visual screenshot đúng role và trạng thái dữ liệu | P0 |
| `ADM-A25-022` | Cần xác minh trong code | No generic PASS từ screenshot redirect/login | P0 |
| `ADM-A25-023` | Đề xuất VNext | Quality Passport toàn hệ thống có evidence từng gate | P2 |
| `ADM-A25-024` | Đề xuất VNext | Privacy data flow map theo chủ thiệp và tenants | P2 |
| `ADM-A25-025` | Đề xuất VNext | Audit integrity verification và alert hành vi lạ | P2 |
| `ADM-A25-026` | Đề xuất VNext | Post-release smoke monitor với rollback runbook | P2 |
