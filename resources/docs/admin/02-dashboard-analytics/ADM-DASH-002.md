---
id: ADM-DASH-002
title: "Bảng chỉ số sức khỏe ngày cưới (Wedding Readiness Radar) và cảnh báo trễ hạn"
category: "Trung tâm Điều hành & Giám sát"
summary: "Hướng dẫn chi tiết về Bảng chỉ số sức khỏe ngày cưới (Wedding Readiness Radar) và cảnh báo trễ hạn trên hệ thống Mây Hẹn."
reader: "PER-ADM-03 (Ban Giám Đốc / Quản Lý)"
order: 5
updated_at: "2026-11-12"
tags: [mayhen, trung-tâm-Điều-hành-&-giám-sát]
mapped_page: "ADM-DASH-PAGE"
mapped_route: "admin.dashboard"
risk_level: "HIGH"
required_role: "manager"
---

# Bảng chỉ số sức khỏe ngày cưới (Wedding Readiness Radar) và cảnh báo trễ hạn

> [!WARNING]
> **Phân quyền thực hiện:** Nhân viên có vai trò `Manager` hoặc `Super Admin`.  
> **Mức độ rủi ro:** `HIGH` | **Endpoint:** `GET /admin/dashboard`.  
> **Bằng chứng kiểm toán:** Bắt buộc lưu vết trong bảng `audit_logs`.

## 1. Mục đích Nghiệp vụ Vận hành
Quy trình này chuẩn hóa thao tác vận hành của nhân viên quản trị đối với module `Trung tâm Điều hành & Giám sát`, đảm bảo tính toàn vẹn dữ liệu, không làm rò rỉ thông tin cá nhân và xử lý nhanh chóng cho khách hàng.

## 2. Điều kiện tiên quyết
- Nhân viên đã đăng nhập tài khoản quản trị Admin hợp lệ.
- Đã xác minh danh tính khách hàng hoặc tính xác thực của sự cố.

## 3. Các bước thao tác an toàn (Standard Operating Procedure)
1. **Bước 1:** Truy cập màn hình quản trị tại URL `/admin/dashboard`.
2. **Bước 2:** Sử dụng bộ lọc hoặc ô tìm kiếm để định vị chính xác thực thể cần xử lý.
3. **Bước 3:** Kiểm tra lịch sử giao dịch hoặc nhật ký kiểm toán liên quan trước khi can thiệp.
4. **Bước 4:** Thực hiện thao tác nghiệp vụ, điền rõ lý do can thiệp vào form xác nhận.
5. **Bước 5:** Nhấp **[Xác nhận thực thi]** và kiểm tra thông báo phản hồi của hệ thống.

## 4. Kết quả mong đợi & Kiểm chứng
- Trạng thái bản ghi chuyển sang giá trị mới đúng nghiệp vụ.
- Bản ghi `audit_logs` được tạo tự động với đầy đủ User ID, IP và payload can thiệp.

## 5. Những điều Tuyệt đối Bị cấm
- Nghiêm cấm thao tác khi chưa có căn cứ chứng từ hoặc yêu cầu bằng văn bản.
- Nghiêm cấm chia sẻ tài khoản quản trị hoặc can thiệp vượt thẩm quyền phân công.
