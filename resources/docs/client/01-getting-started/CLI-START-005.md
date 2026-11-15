---
id: CLI-START-005
title: "Quy trình thanh toán chuyển khoản VietQR tự động qua SePay an toàn"
category: "Bắt đầu với Mây Hẹn"
summary: "Tạo mã VietQR, đối chiếu thông tin đơn và xử lý khi giao dịch chưa được ghi nhận."
reader: "PER-CLI-01 (Cô dâu chú rể mới)"
order: 5
updated_at: "2026-11-12"
tags: [mayhen, bắt-đầu-với-mây-hẹn]
mapped_page: "CLI-START-PAGE"
mapped_route: "checkout.show"
risk_level: "HIGH"
required_role: "customer"
---

# Quy trình thanh toán chuyển khoản VietQR tự động qua SePay an toàn

Sau khi chọn gói trả phí tại [Bảng giá](/bang-gia), bạn có thể tạo mã VietQR để chuyển khoản. Chỉ thanh toán khi bạn đã kiểm tra đúng gói và sẵn sàng đặt đơn.

## Tạo mã thanh toán

1. Trên trang **Xác Nhận & Thanh Toán**, kiểm tra phần **Tóm Tắt Đơn Hàng**. Nếu trang nêu thiệp được áp dụng, kiểm tra đúng thiệp của bạn.
2. Nhập **Họ và tên của bạn**, **Email nhận thông báo** và **Số điện thoại / Zalo**. Ví dụ khi tìm hiểu biểu mẫu, bạn có thể hình dung tên Minh và email `minh@example.com`; khi đặt đơn thật cần dùng thông tin liên hệ của mình.
3. Chọn **Tạo Mã VietQR Thanh Toán**. Màn hình sẽ hiển thị mã QR cùng ngân hàng, số tài khoản, chủ tài khoản, số tiền và nội dung chuyển khoản của đơn.
4. Mở ứng dụng ngân hàng, quét mã và đối chiếu toàn bộ thông tin với màn hình thanh toán trước khi xác nhận. Nếu chuyển thủ công, nhập đúng số tiền và nội dung được hiển thị cho đơn đó.
5. Sau khi chuyển, giữ lại xác nhận giao dịch và chờ màn hình cập nhật. Khi hệ thống ghi nhận đã thanh toán, trang hiển thị **Thanh Toán Thành Công!** và chuyển đến trang thông báo tiếp theo.

## Nếu trạng thái chưa cập nhật

- Kiểm tra lại giao dịch và kết nối mạng. Không chuyển tiền lần nữa chỉ vì màn hình còn đang chờ.
- Nếu cần hỗ trợ, dùng trang [Liên hệ](/lien-he), cung cấp thông tin đơn và bằng chứng chuyển khoản qua kênh hỗ trợ phù hợp. Không gửi mật khẩu ngân hàng hoặc mã OTP.
- QR thanh toán dịch vụ khác với QR mừng cưới của cô dâu chú rể; hãy kiểm tra người nhận trước khi chuyển.

Việc ghi nhận phụ thuộc vào giao dịch và thông báo ngân hàng; không thể bảo đảm thời gian xác nhận trong mọi trường hợp. Xem thêm [cách chọn gói](/docs/getting-started/CLI-START-004).
