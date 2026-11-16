---
id: ADM-SOP-003
title: "Vai trò quản trị và cách kiểm tra quyền"
category: "Quy chuẩn Tiếp nhận & Vận hành (SOP)"
summary: "Các vai trò quản trị được nhận diện và cách xác minh quyền theo từng thao tác."
reader: "PER-ADM-01 / PER-ADM-03"
order: 1
updated_at: "2026-11-12"
tags: [mayhen, quy-chuẩn-tiếp-nhận-&-vận-hành-(sop)]
mapped_page: "ADM-SOP-PAGE"
mapped_route: "admin.dashboard"
risk_level: "CRITICAL"
required_role: "manager"
---

# Vai trò quản trị và cách kiểm tra quyền


![Giao diện quản lý Vai trò quản trị và cách kiểm tra quyền](/assets/client/docs/images/admin/01-sop-onboarding/adm-sop-003-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

## Các vai trò được nhận diện

Mã tài khoản hiện cho phép các vai trò `admin`, `manager`, `staff`, `designer`, `finance` và `support` vào không gian quản trị. Quyền vào bảng điều khiển không đồng nghĩa được phép thực hiện mọi thao tác.

Ví dụ, route thay đổi vai trò người dùng và các thao tác cập nhật ma trận RBAC yêu cầu lớp kiểm tra `admin.super`; nhiều thao tác khác yêu cầu `admin.manager`. Tên hiển thị trong giao diện có thể khác mã vai trò lưu trong hệ thống.

## Kiểm tra trước khi thao tác

1. Đăng nhập tại `/admin/login` bằng tài khoản của chính bạn, sau đó mở mục được phân công.
2. Kiểm tra vai trò và quyền cụ thể với quản trị viên phụ trách. Khi kiểm thử, dùng tài khoản và dữ liệu giả trên môi trường cô lập.
3. Nếu bị từ chối truy cập, ghi lại màn hình và thao tác đang thực hiện, sau đó chuyển yêu cầu cho người có thẩm quyền. Không dùng tài khoản của người khác để vượt quyền.
4. Việc cấp, đổi hoặc thu hồi quyền phải theo quy trình phê duyệt của đơn vị; không mặc định một nhãn vai trò cho phép toàn bộ thao tác tài chính hoặc dữ liệu cá nhân.

## Giới hạn xác minh

Thông tin trên dựa trên mã vai trò và middleware của các route được kiểm tra, chưa phải ma trận đầy đủ cho mọi màn hình. Kiểm thử allow/deny và xác nhận audit từng hành động trước khi sử dụng như căn cứ nghiệm thu quyền. Không sử dụng dữ liệu khách hàng thật để thử.
