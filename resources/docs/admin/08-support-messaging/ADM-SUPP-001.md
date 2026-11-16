---
id: ADM-SUPP-001
title: "Tiếp nhận và phân loại Ticket hỗ trợ theo độ ưu tiên (SLA Response Time)"
category: "Hỗ trợ Khách hàng & Cổng Tin nhắn"
summary: "Quy trình tiếp nhận yêu cầu trợ giúp, phân loại mức độ ưu tiên theo SLA phản hồi (15 phút - 24 giờ), kích hoạt hàng đợi khẩn cấp VIP Concierge 48h trước giờ G và phân công nhân viên phụ trách."
reader: "PER-ADM-01 (Nhân viên CSKH & Vận hành Hỗ trợ)"
order: 1
updated_at: "2026-11-15"
tags: [admin, ticket-ho-tro, cskh, sla-response-time, vip-concierge-48h, phan-loai-su-co]
mapped_page: "ADM-SUPP-PAGE"
mapped_route: "admin.support.index"
risk_level: "HIGH"
required_role: "support"
---

# Tiếp nhận và phân loại Ticket hỗ trợ theo độ ưu tiên (SLA Response Time)

> [!IMPORTANT]
> **Vai trò thực hiện:** `Customer Care Specialist (CSKH)`, `Support Lead`, hoặc `Operation Manager`.  
> **Route Trung tâm Hỗ trợ:** `GET /admin/support` (`Admin\SupportController@index`).  
> **Route phân công nhân viên:** `POST /admin/support/{id}/assign` (`@assign`).  
> **Route leo thang sự cố:** `POST /admin/support/{id}/escalate` (`@escalate`).  
> **Dịch vụ backend:** `App\Services\IncidentResponseAndEscalationService`.  
> **Hàng đợi ưu tiên đặc biệt:** **VIP Concierge 48h** dành riêng cho các cặp đôi có ngày cưới diễn ra trong vòng 48 giờ tới.


![Giao diện quản lý Tiếp nhận và phân loại Ticket hỗ trợ theo độ ưu tiên (SLA Response Time)](/assets/client/docs/images/admin/08-support-messaging/adm-supp-001-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích & Bối cảnh nghiệp vụ

Đối với dịch vụ cưới, thời gian là yếu tố sinh tử. Một lỗi kỹ thuật nhỏ xảy ra trước giờ cử hành hôn lễ 2 giờ (ví dụ: màn chiếu Live Wall không nhận ảnh, khách không mở được bản đồ tiệc cưới) có thể biến thành thảm họa đối với cô dâu chú rể.

Do đó, Mây Hẹn thiết lập **Quy chuẩn Cam kết Thời gian Phản hồi (SLA Response Time)** nghiêm ngặt, kết hợp hàng đợi tự động nhận diện ngày cưới để đảm bảo:
1. Mọi Ticket gửi về từ `/lien-he`, Zalo OA hoặc Studio đều được tiếp nhận và phân loại ngay lập tức.
2. Các tình huống sát giờ cưới được đẩy lên đỉnh hàng đợi và xử lý dưới 15 phút.
3. Phân bổ công việc đúng chuyên môn (Kỹ thuật, Kế toán, Chăm sóc khách hàng).

---

## 2. Ma trận Phân Loại & Cam Kết Thời Gian Phản Hồi (SLA Matrix)

| Mức độ ưu tiên (`priority`) | Tiêu chí nhận diện | SLA phản hồi lần đầu (First Response) | SLA xử lý dứt điểm (Resolution) | Kênh thông báo cảnh báo |
| :--- | :--- | :--- | :--- | :--- |
| **Khẩn cấp (`urgent`) / VIP Concierge** | Cặp đôi có ngày cưới diễn ra trong **48 giờ tới** (`queue = vip_48h`), lỗi sập link thiệp công khai, lỗi Màn chiếu trực tiếp tại tiệc. | **< 15 phút** | **< 45 phút** | Bật còi chuông Admin, gửi thông báo khẩn qua Telegram Bot/ZNS tới Trưởng bộ phận. |
| **Cao (`high`)** | Lỗi không thanh toán được VietQR, lỗi không lưu được bản sửa đổi Studio trước ngày cưới 3–7 ngày. | **< 60 phút** | **< 4 giờ** | Thông báo đẩy trên Admin Dashboard. |
| **Trung bình (`medium`)** | Khách hàng thắc mắc về cách nhập danh sách khách mời Excel, căn chỉnh font chữ, phối màu thiệp. | **< 4 giờ** | **< 12 giờ** | Xếp vào hàng đợi làm việc ca trực. |
| **Thấp (`low`)** | Góp ý tính năng mới, hỏi thông tin các gói dịch vụ tương lai, câu hỏi chung ngoài giờ cao điểm. | **< 24 giờ** | **< 48 giờ** | Phản hồi trong ngày làm việc tiếp theo. |

---

## 3. Các Phân Loại Chuyên Môn (Ticket Categories)

Khi tiếp nhận Ticket, nhân viên CSKH phân loại vào đúng nhóm:
* `technical` (Kỹ thuật): Lỗi tải ảnh, lỗi âm thanh nền, lỗi trình duyệt không tương thích, lỗi Studio không hiển thị preview.
* `billing` (Thanh toán & Hóa đơn): Quét VietQR chưa kích hoạt gói, xuất hóa đơn VAT công ty, yêu cầu hoàn tiền.
* `template` (Mẫu thiệp): Yêu cầu đổi mẫu thiệp, thắc mắc về phong cách thiết kế Quiet Luxury.
* `rsvp` (Khách mời & Sơ đồ bàn): Lỗi link cá nhân hóa Zalo của khách mời, lỗi sơ đồ bàn tiệc.
* `general` (Chung): Hướng dẫn sử dụng ban đầu cho người mới bắt đầu.

---

## 4. Hướng dẫn từng bước: Tiếp nhận & Phân công Ticket

### Bước 1: Theo dõi Dashboard Hàng Đợi Hỗ Trợ
1. Đăng nhập Admin Portal tại `/admin`.
2. Trên thanh điều hướng, chọn **Hỗ Trợ Khách Hàng (Support)** (`/admin/support`).
3. Kiểm tra các chỉ số KPI trên đầu trang:
   * **Ticket đang mở (`open`)**: Số lượng yêu cầu chưa được phản hồi.
   * **Đang xử lý (`in_progress`)**: Ticket đã có nhân viên phụ trách.
   * **Hàng đợi VIP 48h**: Hiển thị số lượng cặp đôi sắp cưới cần ưu tiên phục vụ.

### Bước 2: Kiểm tra Hàng Đợi VIP Concierge 48h (`queue=vip_48h`)
1. Nhấp vào tab lọc nhanh **[ 🚨 VIP 48 Giờ Tới ]**.
2. Hệ thống tự động truy vấn các tài khoản có ngày cưới `$wedding->wedding_date` rơi vào hôm nay hoặc ngày mai:
   * Các Ticket này có huy hiệu nhấp nháy màu đỏ **VIP 48H**.
   * Nhân viên trực ca bắt buộc phải mở xem và phản hồi ngay lập tức trong vòng 15 phút, không được để tồn đọng.

### Bước 3: Phân công nhân viên phụ trách (Assignee)
1. Nhấp vào tiêu đề Ticket để mở trang xử lý chi tiết `/admin/support/{id}`.
2. Tại khối **Phân Công Nhân Viên Phụ Trách**:
   * Nếu tự xử lý: Bấm **[ Nhận Xử Lý Ticket Này ]** (Gán cho chính mình).
   * Nếu cần chuyển chuyên môn: Chọn tên nhân viên Kỹ thuật hoặc Kế toán trong danh sách thả xuống.
3. Nhấp **[ Cập Nhật Phân Công ]**. Hệ thống chuyển trạng thái Ticket từ `open` sang `in_progress` và gửi thông báo tới nhân viên được gán.

### Bước 4: Phản hồi khách hàng & Khép Ticket
1. Đọc kỹ câu hỏi và xem các tệp đính kèm (ảnh chụp màn hình lỗi) của khách hàng.
2. Sử dụng thư viện câu trả lời mẫu (chi tiết tại [ADM-SUPP-002](/admin/docs/support-messaging/ADM-SUPP-002)) để trả lời nhanh và chuẩn văn phong Quiet Luxury.
3. Nếu cần xem trực tiếp giao diện của khách: Kích hoạt chế độ hỗ trợ an toàn Impersonation (chi tiết tại [ADM-SUPP-003](/admin/docs/support-messaging/ADM-SUPP-003)).
4. Sau khi sự cố được khắc phục:
   * Gửi phản hồi cảm ơn và hướng dẫn kiểm tra lại.
   * Chuyển trạng thái Ticket sang `resolved` (Đã giải quyết).
   * Sau 24 giờ khách không phản hồi thêm, hệ thống tự động chuyển sang `closed`.

---

## 5. Quy trình Leo thang Sự cố Nghiêm trọng (Incident Escalation)

Khi phát hiện sự cố ảnh hưởng diện rộng (ví dụ: máy chủ hình ảnh bị chậm, toàn bộ link thiệp không phát được nhạc nền):
1. Tại trang chi tiết Ticket, nhấp nút màu đỏ **[ ⚠️ Báo Cáo Leo Thang Sự Cố (Escalate) ]**.
2. Chọn mức độ:
   * `Level 2 (Technical Lead)`: Cần can thiệp sửa code hoặc cấu hình máy chủ.
   * `Level 3 (Management/Crisis)`: Cần phát đi thông báo bảo trì toàn hệ thống.
3. Nhập diễn giải ngắn gọn về phạm vi ảnh hưởng.
4. Bấm **[ Kích Hoạt Leo Thang ]**. Hệ thống tự động gắn cờ `is_incident = true`, ghi nhận mốc `escalated_at` và kích hoạt luồng cảnh báo khẩn cấp tới Ban Kỹ thuật qua `IncidentResponseAndEscalationService`.

---

## 6. Những điều Tuyệt đối Bị cấm trong Vận hành Hỗ trợ

1. **CẤM ĐÓNG TICKET KHI CHƯA CÓ PHẢN HỒI THỰC SỰ ĐƯỢC GIẢI QUYẾT:** Tuyệt đối không bấm `resolved` chỉ để làm đẹp chỉ số SLA cá nhân khi vấn đề của cặp đôi chưa xong.
2. **CẤM BỎ QUA HÀNG ĐỢI VIP CONCIERGE 48H:** Nhân viên trực ca bỏ sót Ticket VIP 48h dẫn đến sự cố trong đám cưới của khách sẽ bị xử lý kỷ luật nghiêm khắc theo quy chế vận hành.
3. **CẤM DÙNG NGÔN TỪ SUỒNG SÃ HOẶC TRÁCH MÓC KHÁCH HÀNG:** Luôn giữ văn phong trang nhã, đồng cảm, lắng nghe và chia sẻ niềm vui chuẩn bị ngày cưới cùng khách hàng.

---

## 7. Bài viết liên quan
* [ADM-SUPP-002: Quản lý và sử dụng thư viện câu trả lời mẫu chuẩn mực (Canned Responses)](/admin/docs/support-messaging/ADM-SUPP-002)
* [ADM-SUPP-003: Tính năng Đóng vai hỗ trợ (Impersonation): Đăng nhập hỗ trợ khách an toàn](/admin/docs/support-messaging/ADM-SUPP-003)
* [ADM-SUPP-004: Tạo và phát thông báo hệ thống (Site Announcements) trên giao diện Client](/admin/docs/support-messaging/ADM-SUPP-004)
* [ADM-SUPP-005: Điều phối tin nhắn thông báo tập trung (Reliable Messaging Gateway Dispatcher)](/admin/docs/support-messaging/ADM-SUPP-005)
