---
id: ADM-SOP-005
title: "Quy trình tiếp nhận và xử lý yêu cầu quyền riêng tư dữ liệu cá nhân (Luật 91/2025/QH15)"
category: "Quy chuẩn Tiếp nhận & Vận hành (SOP)"
summary: "Hướng dẫn chi tiết về Quy trình tiếp nhận và xử lý yêu cầu quyền riêng tư dữ liệu cá nhân (Luật 91/2025/QH15) trên hệ thống Mây Hẹn."
reader: "PER-ADM-01 / PER-ADM-03"
order: 3
updated_at: "2026-11-12"
tags: [mayhen, quy-chuẩn-tiếp-nhận-&-vận-hành-(sop)]
mapped_page: "ADM-SOP-PAGE"
mapped_route: "admin.privacy.index"
risk_level: "CRITICAL"
required_role: "manager"
---

# Quy trình tiếp nhận và xử lý yêu cầu quyền riêng tư dữ liệu cá nhân (Luật 91/2025/QH15)

## Trước khi xử lý

Yêu cầu xóa hoặc ẩn danh có thể ảnh hưởng dữ liệu khách hàng. Xác minh danh tính và thẩm quyền theo quy trình của đơn vị, kiểm tra phạm vi yêu cầu và nghĩa vụ lưu giữ liên quan trước khi thao tác. Chỉ kiểm thử bằng tài khoản và dữ liệu giả trên môi trường cô lập.

## Theo dõi yêu cầu

1. Người được phân quyền mở `/admin/privacy` và xem danh sách yêu cầu. Bộ lọc có các trạng thái như **Chờ duyệt**, đã duyệt và đã thực thi.
2. Đối chiếu mã yêu cầu và đối tượng với thông tin đã được xác minh. Ví dụ trong môi trường thử, dùng một yêu cầu giả thay vì hồ sơ khách thật.
3. Nếu có thẩm quyền và đủ căn cứ, xem xét phê duyệt hoặc từ chối. Route của hai thao tác này yêu cầu `admin.manager`; khi từ chối, mã yêu cầu nhập lý do.
4. Phê duyệt chưa phải là thực thi. Thao tác thực thi là bước riêng, có cảnh báo ẩn danh hóa; chỉ thực hiện sau khi xác nhận lại phạm vi và được phép. Không chạy dọn dẹp hàng loạt để thử.
5. Kiểm tra trạng thái và lưu bằng chứng xử lý qua kênh nội bộ được phép. Mã hiện gọi ghi audit cho một số hành động, nhưng cần kiểm thử quyền và nhật ký từng trường hợp trước khi nghiệm thu.

Nếu thiếu thông tin hoặc bị từ chối quyền, dừng thao tác và chuyển cho người phụ trách. Không cam kết rằng mọi dữ liệu đều có thể xóa hoặc rằng hành động đã hoàn tất chỉ vì yêu cầu được duyệt.
