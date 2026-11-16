---
id: ADM-USER-001
title: "Tra cứu hồ sơ khách hàng và lịch sử liên quan"
category: "Quản lý Tài khoản Khách & RBAC"
summary: "Tra cứu tài khoản, thiệp, đơn hàng và yêu cầu hỗ trợ theo quyền được cấp."
reader: "Nhân sự hỗ trợ và quản lý được cấp quyền"
order: 8
updated_at: "2026-11-15"
tags: [khach-hang, tai-khoan, bao-mat]
mapped_page: "ADM-USER-PAGE"
mapped_route: "admin.users.index"
risk_level: "CRITICAL"
required_role: "manager"
---

# Tra cứu hồ sơ khách hàng và lịch sử liên quan

Hồ sơ có thể chứa thông tin cá nhân. Chỉ tra cứu khi có nhu cầu công việc hợp lệ, dùng tài khoản của chính bạn và chỉ chia sẻ thông tin cần thiết qua kênh được phép. Không yêu cầu khách cung cấp mật khẩu hiện tại.


![Giao diện quản lý Tra cứu hồ sơ khách hàng và lịch sử liên quan](/assets/client/docs/images/admin/03-users-rbac/adm-user-001-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

## Tìm và mở hồ sơ

1. Mở mục quản lý người dùng tại `/admin/users` bằng tài khoản được cấp quyền.
2. Tìm bằng tên, email hoặc số điện thoại; có thể lọc theo vai trò hoặc trạng thái tài khoản. Kiểm tra thông tin nhận diện phù hợp với yêu cầu hỗ trợ trước khi mở chi tiết.
3. Mở hồ sơ khách hàng. Theo mã hiện tại, màn hình chi tiết lấy các đám cưới và thiệp liên quan, lịch sử đơn hàng và yêu cầu hỗ trợ nếu bảng dữ liệu hỗ trợ có sẵn. Tổng chi tiêu được tính từ các đơn đã thanh toán.
4. Đối chiếu thông tin cần thiết để giải quyết yêu cầu; nếu không đủ quyền hoặc chưa xác minh được danh tính, chuyển cho người phụ trách thay vì chia sẻ thông tin nhạy cảm.

## Giới hạn an toàn

- Không mặc định email hay số điện thoại đã được che khi hiển thị; tránh chia sẻ màn hình, xuất dữ liệu hoặc chụp ảnh có thông tin thật nếu chưa được phép.
- Chưa có bằng chứng rằng **mọi lượt xem hồ sơ** được ghi nhật ký kiểm toán. Không dựa vào giả định đó để thay thế quy trình kiểm soát truy cập.
- Các thao tác đổi vai trò, đặt lại mật khẩu, khóa hoặc đăng nhập thay khách là thao tác riêng có rủi ro; không thực hiện chỉ vì đang tra cứu. Tuân thủ quy trình và quyền thực tế của đơn vị.
- Quyền theo từng vai trò, cách hiển thị dữ liệu và audit khi đọc cần được xác minh bằng tài khoản và dữ liệu giả trong môi trường cô lập trước khi nghiệm thu.
