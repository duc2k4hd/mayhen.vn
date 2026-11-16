---
id: ADM-SOP-002
title: "Đăng nhập quản trị an toàn và bảo vệ dữ liệu"
category: "Quy chuẩn Tiếp nhận & Vận hành (SOP)"
summary: "Các bước đăng nhập quản trị và nguyên tắc bảo vệ dữ liệu khi hỗ trợ."
reader: "PER-ADM-01 / PER-ADM-03"
order: 12
updated_at: "2026-11-12"
tags: [mayhen, quy-chuẩn-tiếp-nhận-&-vận-hành-(sop)]
mapped_page: "ADM-SOP-PAGE"
mapped_route: "admin.login"
risk_level: "CRITICAL"
required_role: "manager"
---

# Đăng nhập quản trị an toàn và bảo vệ dữ liệu


![Giao diện quản lý Đăng nhập quản trị an toàn và bảo vệ dữ liệu](/assets/client/docs/images/admin/01-sop-onboarding/adm-sop-002-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

## Đăng nhập

1. Mở `/admin/login` và nhập **Email quản trị** cùng **Mật khẩu** của tài khoản được cấp quyền.
2. Đăng nhập và kiểm tra bạn được chuyển đến bảng điều khiển `/admin/dashboard`. Nếu tài khoản không có quyền quản trị hoặc đang bị khóa, hệ thống từ chối truy cập.
3. Khi kết thúc ca, dùng chức năng đăng xuất; không chia sẻ tài khoản hoặc mật khẩu với người khác.

Nếu nhập sai nhiều lần, trang có thể yêu cầu bạn đợi trước khi thử lại. Kiểm tra email và liên hệ người quản lý quyền nếu tài khoản bị khóa; không thử đoán mật khẩu liên tục.

## Bảo vệ dữ liệu khi hỗ trợ

- Chỉ mở hồ sơ và dữ liệu khách khi cần cho công việc được giao. Dùng dữ liệu giả, chẳng hạn `minh@example.com`, khi hướng dẫn hoặc kiểm thử trong môi trường cô lập.
- Không đưa mật khẩu, mã OTP hoặc dữ liệu khách thật vào ảnh chụp, tài liệu bàn giao hay kênh trao đổi không được phép.
- Mã đăng nhập hiện gọi ghi nhận sự kiện đăng nhập và đăng xuất. Không suy ra mọi lượt xem dữ liệu đều được ghi lại, hoặc mọi bản ghi audit được lưu vĩnh viễn.
- Luồng đăng nhập được đối chiếu dùng email và mật khẩu; chưa có bằng chứng ở luồng này để khẳng định 2FA bắt buộc. Hãy tuân thủ chính sách bảo mật nội bộ và báo quản trị viên nếu cần xác minh cấu hình.

Xem thêm [tra cứu hồ sơ khách hàng](/admin/docs/users-rbac/ADM-USER-001).
