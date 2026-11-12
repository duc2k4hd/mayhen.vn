---
id: ADM-ORDR-002
title: "Quy trình Đối soát SePay & Kích hoạt Đơn hàng Chuyển khoản Thủ công"
category: "Đơn hàng, SePay & Hoàn tiền"
summary: "Quy trình chuẩn xử lý đơn hàng chuyển khoản sai cú pháp, đối chiếu tiền nổi thực tế tại ngân hàng và kích hoạt gói dịch vụ an toàn."
reader: "Kế toán, Quản lý"
order: 2
updated_at: "2026-11-12"
tags: [sepay, kich-hoat-thu-cong, doi-soat, audit-log]
mapped_page: "ADM-ORDR-PAGE"
mapped_route: "admin.orders.index"
risk_level: "CRITICAL"
required_role: "finance"
---

# ADM-SOP-ORDR-002: Quy trình Đối soát SePay & Kích hoạt Đơn hàng Chuyển khoản Thủ công

> **Mã quy trình:** `ADM-SOP-ORDR-002`  
> **Đối tượng thực hiện:** Nhân viên Kế toán (Finance) & Quản lý vận hành (Manager)  
> **Mức độ rủi ro:** CAO (Ảnh hưởng đến doanh thu thực và trạng thái kích hoạt gói dịch vụ)  
> **Bằng chứng bắt buộc:** Mã giao dịch ngân hàng thực tế (FT ID / Ref Code) trong sao kê  
> **Điều kiện tiên quyết:** Khách đã chuyển khoản thực tế nhưng đơn hàng ở trạng thái "pending" quá 10 phút  

## 1. Mục đích nghiệp vụ
Xử lý các tình huống hy hữu khi khách hàng chuyển khoản qua VietQR nhưng hệ thống chưa tự động kích hoạt gói do khách sửa nội dung chuyển khoản hoặc gián đoạn mạng IPN. Quy trình này hướng dẫn đối soát minh bạch, kích hoạt gói kịp thời mà không gây thất thoát tài chính.

## 2. Các bước thao tác an toàn (Standard Operating Procedure)
1. **Bước 1: Tiếp nhận yêu cầu & Xác minh đơn hàng:** Truy cập `/admin/orders`, nhập Số điện thoại hoặc Mã đơn của khách. Mở chi tiết đơn hàng kiểm tra số tiền và lịch sử Webhook Logs.
2. **Bước 2: Kiểm tra dòng tiền thực tế tại Tài khoản Ngân hàng Doanh nghiệp:** Đăng nhập App Ngân hàng Doanh nghiệp của Mây Hẹn (Vietcombank / MBBank) hoặc Dashboard SePay. Tìm kiếm biến động số dư: số tiền khớp đúng 100%, thời gian chuyển hợp lệ. Ghi lại **Mã chuẩn chi ngân hàng (Reference Code / FT Number)** (ví dụ: `FT263158912401`).
> [!CAUTION]
> **QUY TẮC SỐ 1:** TUYỆT ĐỐI KHÔNG kích hoạt đơn hàng chỉ dựa trên ảnh chụp màn hình ủy nhiệm chi do khách gửi. Chỉ thao tác khi tiền ĐÃ NỔI THỰC TẾ trên tài khoản công ty.

3. **Bước 3: Thao tác Kích hoạt Thủ công trên Giao diện Admin:** Tại trang chi tiết đơn hàng `/admin/orders/{id}`, nhấp vào nút **[Kích hoạt thủ công]**. Form bảo mật hiện lên, nhập đủ 3 thông tin: Mã tham chiếu ngân hàng, Số tiền thực nhận, và Lý do can thiệp (ví dụ: 'Khách chuyển nhầm nội dung thành tên chú rể, đã đối soát sao kê 14:30'). Nhấp **[Xác nhận kích hoạt]**.

## 3. Kết quả mong đợi & Tác động hệ thống
- Trạng thái đơn hàng tự động chuyển từ `pending` sang `paid`.
- Gói dịch vụ của thiệp cưới tương ứng lập tức được mở khóa (khách có thể xuất bản thiệp ngay).
- Hệ thống ghi nhận 1 bản ghi kiểm toán bất biến trong bảng `audit_logs`.
- Email xác nhận thanh toán tự động gửi đến hòm thư của cô dâu chú rể.

## 4. Những điều TUYỆT ĐỐI BỊ CẤM đối với Nhân viên
- **CẤM:** Không được kích hoạt đơn hàng khi số tiền thực nhận nhỏ hơn giá trị đơn hàng.
- **CẤM:** Không được nhập mã tham chiếu giả mạo hoặc ghi lý do qua loa.
- **CẤM:** Không được chia sẻ mật khẩu tài khoản Admin hoặc nhờ đồng nghiệp thao tác hộ.

## 5. Quy trình Chuyển cấp Khẩn cấp (Escalation)
Nếu phát hiện 1 mã giao dịch ngân hàng bị dùng trùng lặp cho 2 đơn hàng khác nhau: Giữ nguyên trạng thái đơn hàng ở mức `pending`, khóa tạm thời quyền chỉnh sửa của thiệp liên quan và báo cáo ngay cho **Kế toán trưởng** trong vòng **15 phút**.
