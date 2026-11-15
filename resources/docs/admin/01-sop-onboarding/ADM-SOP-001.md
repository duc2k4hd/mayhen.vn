---
id: ADM-SOP-001
title: "Tiếp nhận vận hành hệ thống Mây Hẹn"
category: "Quy chuẩn Tiếp nhận & Vận hành (SOP)"
summary: "Kiểm tra môi trường, quyền truy cập và bàn giao an toàn trước khi vận hành."
reader: "Nhân sự vận hành được cấp quyền"
order: 11
updated_at: "2026-11-15"
tags: [van-hanh, ban-giao, bao-mat]
mapped_page: "ADM-SOP-PAGE"
mapped_route: "admin.dashboard"
risk_level: "CRITICAL"
required_role: "manager"
---

# Tiếp nhận vận hành hệ thống Mây Hẹn

Bài này giúp người nhận ca xác định đúng môi trường và quyền của mình trước khi làm việc. Không dùng tài khoản thật hoặc thao tác thay đổi dữ liệu thật để thực hành.

## 1. Xác định môi trường và tài khoản

1. Xác nhận với người bàn giao bạn đang làm việc trên môi trường thử hay môi trường vận hành thật. Không sao chép dữ liệu khách từ môi trường thật sang môi trường thử khi chưa được phép.
2. Dùng tài khoản được cấp riêng để đăng nhập tại `/admin/login`. Nếu có quyền, hệ thống chuyển đến `/admin/dashboard`.
3. Đối chiếu vai trò và phạm vi công việc với người phụ trách. Mã tài khoản nhận diện các vai trò quản trị gồm `admin`, `manager`, `staff`, `designer`, `finance` và `support`; quyền cụ thể còn phụ thuộc từng chức năng.
4. Trước khi thao tác nhạy cảm, xác minh quyền và quy trình phê duyệt tương ứng. Nếu bị từ chối, chuyển yêu cầu cho người có thẩm quyền thay vì dùng tài khoản người khác.

## 2. Bàn giao công việc

- Ghi lại sự cố đang xử lý, bước tiếp theo, người phụ trách và thời điểm cần phản hồi trong kênh nội bộ được phép.
- Chỉ ghi thông tin cá nhân tối thiểu cần thiết; không chia sẻ mật khẩu, mã OTP, khóa API hoặc ảnh có dữ liệu khách thật.
- Với sự cố hạ tầng, người có quyền tương ứng có thể xem `/admin/system/health`. Không tự xóa nhật ký, khôi phục dữ liệu hoặc thay đổi cấu hình khi chưa có quy trình và quyền phù hợp.
- Khi kết thúc ca, đăng xuất và xác nhận người nhận đã tiếp nhận những việc còn tồn đọng.

Ví dụ bàn giao thử: “Mã yêu cầu giả DEMO-001 đang chờ kiểm tra; người phụ trách sẽ cập nhật trong ca sau.” Không dùng ví dụ này để xử lý khách hàng thật.

## 3. Những điều cần xác minh riêng

Mã nguồn hiện tại cho thấy các route quản trị có nhiều lớp kiểm tra quyền, nhưng không đủ để kết luận mọi hành động đều được ghi audit hoặc log được lưu vĩnh viễn. Khả năng 2FA, khôi phục và cấu hình môi trường thực tế cần được xác minh riêng trước khi bàn giao.

Xem thêm [đăng nhập an toàn](/admin/docs/sop-onboarding/ADM-SOP-002), [kiểm tra quyền](/admin/docs/sop-onboarding/ADM-SOP-003) và [bàn giao ca trực](/admin/docs/sop-onboarding/ADM-SOP-004).

