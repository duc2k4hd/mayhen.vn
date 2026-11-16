---
id: ADM-SOP-004
title: "Checklist bàn giao ca trực hàng ngày và sổ tay xử lý sự cố khẩn cấp (P0/P1 Runbook)"
category: "Quy chuẩn Tiếp nhận & Vận hành (SOP)"
summary: "Hướng dẫn chi tiết về Checklist bàn giao ca trực hàng ngày và sổ tay xử lý sự cố khẩn cấp (P0/P1 Runbook) trên hệ thống Mây Hẹn."
reader: "PER-ADM-01 / PER-ADM-03"
order: 2
updated_at: "2026-11-12"
tags: [mayhen, quy-chuẩn-tiếp-nhận-&-vận-hành-(sop)]
mapped_page: "ADM-SOP-PAGE"
mapped_route: "admin.dashboard"
risk_level: "CRITICAL"
required_role: "manager"
---

# Checklist bàn giao ca trực hàng ngày và sổ tay xử lý sự cố khẩn cấp (P0/P1 Runbook)


![Giao diện quản lý Checklist bàn giao ca trực hàng ngày và sổ tay xử lý sự cố khẩn cấp (P0/P1 Runbook)](/assets/client/docs/images/admin/01-sop-onboarding/adm-sop-004-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

## Khi nhận ca

1. Đăng nhập bằng tài khoản của bạn và mở `/admin/dashboard`.
2. Tiếp nhận các yêu cầu còn mở, người phụ trách và thời điểm cần phản hồi từ người bàn giao. Chỉ ghi thông tin cá nhân tối thiểu cần thiết trong kênh nội bộ được phép.
3. Với sự cố kỹ thuật, người có quyền phù hợp có thể kiểm tra `/admin/system/health`; khu vực này được bảo vệ bởi `admin.super`.
4. Ghi thời gian, phạm vi ảnh hưởng, bước đã kiểm tra và người đang xử lý. Ví dụ kiểm thử: ghi mã thiệp giả `demo-001`, không dùng dữ liệu khách thật.

## Khi gặp sự cố và bàn giao

- Chuyển sự cố cho người phụ trách theo quy trình của đơn vị. Nếu ảnh hưởng thanh toán hoặc dữ liệu cá nhân, tránh tự thao tác khôi phục, hoàn tiền hay xóa dữ liệu khi chưa được phân quyền và xác minh.
- Bàn giao tình trạng mới nhất, các bước đang chờ và đầu mối liên hệ; không chia sẻ mật khẩu, OTP hoặc ảnh chứa dữ liệu nhạy cảm.
- Đăng xuất khi kết thúc ca. Việc kiểm tra giao diện, mức độ ưu tiên và kênh ứng cứu thực tế cần được xác nhận theo quy trình vận hành hiện hành; bài này không chứng minh hệ thống tự ghi audit cho mọi bước bàn giao.
