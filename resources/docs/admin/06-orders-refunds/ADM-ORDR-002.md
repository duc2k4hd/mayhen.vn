---
id: ADM-ORDR-002
title: "Đối soát đơn hàng chuyển khoản thủ công"
category: "Đơn hàng, SePay & Hoàn tiền"
summary: "Tra cứu đơn và xem các bước đối soát thủ công, đề xuất hoặc phê duyệt theo quyền."
reader: "Nhân sự tài chính và quản trị được cấp quyền"
order: 2
updated_at: "2026-11-15"
tags: [doi-soat, don-hang, thanh-toan]
mapped_page: "ADM-ORDR-PAGE"
mapped_route: "admin.orders.index"
risk_level: "CRITICAL"
required_role: "finance"
---

# Đối soát đơn hàng chuyển khoản thủ công

**Cảnh báo:** Đây là thao tác tài chính có thể kích hoạt đơn hàng. Chỉ nhân sự được ủy quyền thực hiện theo quy trình tài chính của đơn vị, sau khi đối chiếu chứng từ độc lập. Không phê duyệt chỉ dựa trên ảnh chụp do khách gửi. Hướng dẫn này chưa được kiểm thử bằng giao dịch trên môi trường cô lập.

## Tra cứu và đối chiếu

1. Mở **Đơn Hàng & Doanh Thu** tại `/admin/orders`. Tìm theo mã đơn, tên, email hoặc số điện thoại khách hàng; mở đơn cần kiểm tra.
2. Đối chiếu số tiền, trạng thái, thông tin giao dịch và chứng từ với nguồn tài chính được đơn vị chấp thuận. Trang có mục **Chi Tiết Giao Dịch & Đối Soát** và liên kết **Nhật Ký Webhook Thanh Toán** ở danh sách.
3. Nếu chưa xác minh được tiền thực nhận, có dấu hiệu trùng giao dịch hoặc chưa rõ đơn phù hợp, dừng thao tác và chuyển người phụ trách tài chính kiểm tra. Không đưa mã giao dịch thật vào ảnh tài liệu hay kênh công khai.

## Đề xuất hoặc duyệt đối soát

Khi đơn ở trạng thái `pending` và chưa có đề xuất đối soát đang chờ, trang chi tiết hiển thị **Đối Soát & Duyệt Thanh Toán Thủ Công**:

- **Mã GD Ngân Hàng / Tham Chiếu Thật**: bắt buộc.
- **Ngân Hàng Nhận**: tùy chọn.
- **Ghi Chú Đối Soát Chứng Từ**: bắt buộc.

Sau khi kiểm tra chứng từ và quyền, điền thông tin chính xác. Giao diện hiển thị nút **Duyệt Thanh Toán Thủ Công (Super Admin)** cho Super Admin; với nhánh người dùng còn lại hiển thị **Đề Xuất Duyệt Thanh Toán (Maker-Checker)**. Làm theo phân quyền và quy trình phê duyệt thực tế của đơn vị; việc nút xuất hiện không thay thế việc kiểm tra quyền hoặc chứng từ.

Nếu có đề xuất đang chờ, trang hiển thị người đề xuất, mã giao dịch và lý do. Giao diện Super Admin có **Phê Duyệt Đối Soát & Kích Hoạt** hoặc **Từ Chối Đề Xuất**; từ chối cần nhập lý do. Kiểm tra thông tin trước khi xác nhận vì thao tác ảnh hưởng tài chính.

## Kiểm tra sau xử lý

Xem lại trạng thái đơn, thông báo và mục **Sổ Cái Đối Soát Tài Chính & Chứng Từ**. Nếu kết quả không như dự kiến, dừng và báo người phụ trách; không lặp thao tác phê duyệt một cách mù quáng. Tác động kích hoạt, kiểm toán, quyền từng vai trò và khả năng chống xử lý lặp vẫn cần kiểm thử với dữ liệu giả trên môi trường cô lập trước khi nghiệm thu tài liệu.
