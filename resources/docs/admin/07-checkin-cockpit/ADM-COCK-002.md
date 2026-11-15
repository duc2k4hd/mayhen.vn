---
id: ADM-COCK-002
title: "Xử lý gián đoạn mạng khi điểm danh tại sảnh tiệc"
category: "Check-in Cockpit & Sơ đồ bàn"
summary: "Các bước thận trọng khi trạm điểm danh mất kết nối và cách kiểm tra lại sau khi có mạng."
reader: "Nhân sự vận hành được cấp quyền"
order: 6
updated_at: "2026-11-15"
tags: [check-in, su-co-mang, van-hanh]
mapped_page: "ADM-COCK-PAGE"
mapped_route: "admin.guests.checkin.index"
risk_level: "CRITICAL"
required_role: "manager"
---

# Xử lý gián đoạn mạng khi điểm danh tại sảnh tiệc

**Quan trọng:** Chưa xác minh được chức năng điểm danh ngoại tuyến hoặc tự đồng bộ. Không giả định thao tác đã được lưu khi màn hình báo lỗi mạng, và không hứa với lễ tân rằng có thể dùng phần mềm khi mất kết nối.

## Nhận biết sự cố

Trạm điểm danh có thể báo **“Lỗi mạng khi điểm danh quan khách.”** hoặc **“Lỗi mạng khi hủy điểm danh.”**. Kết quả tìm kiếm hoặc thống kê cũng có thể không cập nhật khi kết nối bị gián đoạn.

## Xử lý tại chỗ

1. Báo người phụ trách vận hành và kiểm tra kết nối thiết bị, Wi-Fi hoặc đường truyền theo quy trình của địa điểm. Không yêu cầu khách cung cấp thêm dữ liệu cá nhân không cần thiết.
2. Chuyển sang phương án tiếp đón dự phòng do đơn vị tổ chức phê duyệt. Nếu cần ghi nhận thủ công, chỉ thu thập thông tin tối thiểu, kiểm soát người được tiếp cận và bảo quản an toàn; thống nhất cách bàn giao và xóa bản ghi theo quy định của đơn vị.
3. Ghi lại thời điểm, tên tiệc, biểu hiện lỗi và người phụ trách để hỗ trợ đối chiếu. Không chụp hoặc gửi danh sách khách thật qua kênh không được phép.
4. Khi kết nối ổn định, nhân sự được cấp quyền mở **Trạm Điểm Danh Ngày Cưới** tại `/admin/guests/checkin`, chọn đúng tiệc và tìm khách cần kiểm tra. Xem trạng thái hiện tại trước khi thao tác lại: yêu cầu trước đó có thể đã tới máy chủ dù thiết bị không nhận được phản hồi.
5. Đối chiếu các ghi nhận dự phòng với trạng thái trên hệ thống theo quy trình của đơn vị, tránh điểm danh trùng hoặc hủy nhầm. Xử lý các trường hợp không chắc chắn cùng người phụ trách; không tự suy đoán kết quả.

## Điều cần kiểm chứng trước khi áp dụng

Việc lưu ngoại tuyến, hàng đợi đồng bộ, xử lý xung đột và quyền cụ thể chưa được kiểm thử trong môi trường cô lập. Không coi hướng dẫn này là bằng chứng các tính năng đó tồn tại. Chỉ xuất hoặc chia sẻ báo cáo khách mời khi được phép và thực sự cần thiết.
