---
id: ADM-SUPP-004
title: "Tạo và phát thông báo hệ thống (Site Announcements) trên giao diện Client"
category: "Hỗ trợ Khách hàng & Cổng Tin nhắn"
summary: "Quy trình thiết lập và phát sóng thông báo toàn website (Site Announcements), cấu hình vị trí hiển thị (Top Bar, Client Dashboard, Modal Popup), lên lịch hiển thị và theo dõi lượt xem/click (CTR) trên Mây Hẹn."
reader: "PER-ADM-01 / PER-ADM-02 (Vận hành & Hỗ trợ Khách hàng)"
order: 4
updated_at: "2026-11-15"
tags: [admin, thong-bao-he-thong, site-announcements, top-bar, modal-popup, bao-tri, uu-dai]
mapped_page: "ADM-SUPP-PAGE"
mapped_route: "admin.announcements.index"
risk_level: "MEDIUM"
required_role: "manager"
---

# Tạo và phát thông báo hệ thống (Site Announcements) trên giao diện Client

> [!IMPORTANT]
> **Vai trò thực hiện:** `Operation Manager`, `Support Lead`, hoặc `Super Admin`.  
> **Route Quản lý Thông báo:** `GET /admin/announcements` (`AdminAnnouncementController@index`).  
> **Route Tạo mới / Cập nhật:** `GET /admin/announcements/create` & `POST /admin/announcements` (`@store`).  
> **Dịch vụ backend:** `App\Services\SiteAnnouncementService`.  
> **Vị trí hiển thị hỗ trợ:** **Top Bar** (Thanh tiêu đề toàn site), **Client Dashboard** (Bảng điều khiển cặp đôi), và **Modal Popup** (Hộp thoại nổi).


![Giao diện quản lý Tạo và phát thông báo hệ thống (Site Announcements) trên giao diện Client](/assets/client/docs/images/admin/08-support-messaging/adm-supp-004-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích & Bối cảnh nghiệp vụ

Trong quá trình vận hành nền tảng Mây Hẹn, có những sự kiện quan trọng cần truyền đạt tức thời tới hàng nghìn cặp đôi và khách mời:
* **Thông báo bảo trì hệ thống định kỳ**: Nâng cấp máy chủ thanh toán hoặc bảo trì cổng kết nối ngân hàng vào khung giờ thấp điểm (02:00 – 04:00 sáng).
* **Cảnh báo lỗi diện rộng**: Thông báo các sự cố cáp quang biển ảnh hưởng đến tốc độ tải video/ảnh cưới.
* **Chiến dịch ưu đãi mùa cưới**: Chương trình tặng tính năng Màn chiếu Live Wall hoặc giảm giá gói dịch vụ dịp lễ cưới cao điểm (Mùa cưới thu đông).
* **Cập nhật tính năng mới**: Ra mắt bộ sưu tập mẫu thiệp cưới mới hoặc tính năng Két Kỷ Niệm (Memory Vault).

Hệ thống **Site Announcements** cho phép đội ngũ Vận hành phát đi các thông điệp có chủ đích, nhắm đúng vị trí và có thể hẹn giờ tự động tắt mà không cần can thiệp vào mã nguồn.

---

## 2. Các Loại Thông Báo & Vị Trí Hiển Thị

### 4 Phân loại thông báo (`type`):
1. **Thông tin (`info` - Xanh ngọc / Xanh dương)**: Cập nhật tính năng mới, hướng dẫn mẹo hay khi chuẩn bị đám cưới.
2. **Khuyến mãi (`promotion` - Vàng đồng / Rose Gold)**: Ưu đãi nâng cấp gói dịch vụ, tặng voucher Studio.
3. **Cảnh báo (`warning` - Vàng cam)**: Thông báo bảo trì kỹ thuật sắp diễn ra, nhắc nhở sao lưu dữ liệu.
4. **Khẩn cấp (`danger` - Đỏ bầm)**: Sự cố gián đoạn dịch vụ diện rộng cần người dùng chú ý ngay.

### 3 Vị trí hiển thị (`placement`):
* `top_bar` (**Thanh tiêu đề đầu trang**): Xuất hiện ghim chặt ở mép trên cùng của toàn bộ website (Trang chủ, Kho mẫu, Hướng dẫn Docs). Phù hợp cho thông báo bảo trì ngắn gọn hoặc khuyến mãi 1 dòng.
* `client_dashboard` (**Bảng điều khiển cặp đôi**): Banner trang nhã xuất hiện ngay trong trang `/tai-khoan` của cặp đôi. Thích hợp cho các hướng dẫn chuẩn bị ngày cưới hoặc gợi ý hoàn tất hồ sơ thiệp.
* `modal_popup` (**Hộp thoại nổi giữa màn hình**): Hiển thị cửa sổ pop-up ngay khi người dùng vào trang. Chỉ dùng cho các trường hợp khẩn cấp cấp độ 1 hoặc sự kiện đặc biệt quan trọng có nút xác nhận.

---

## 3. Cấu Trúc Bản Ghi Thông Báo (`site_announcements`)

Mỗi thông báo được cấu hình với các thuộc tính linh hoạt:
* **Tiêu đề (`title`)**: Ngắn gọn, súc tích (dưới 80 ký tự).
* **Nội dung (`content`)**: Diễn giải chi tiết bằng văn phong nhã nhặn.
* **Nút hành động CTA (`cta_text` & `cta_url`)**: Ví dụ: `[ Xem Chi Tiết ]` dẫn về bài hướng dẫn, hoặc `[ Nhận Ưu Đãi ]` dẫn về trang bảng giá.
* **Cho phép đóng (`is_dismissible`)**: Người dùng bấm nút `×` để ẩn thông báo trên trình duyệt của họ (tránh làm phiền khi đọc lại nhiều lần).
* **Lên lịch phát sóng (`starts_at` & `ends_at`)**: Tự động hiển thị và tự động hạ xuống đúng giờ định trước.
* **Thống kê hiệu quả (`views_count` & `clicks_count`)**: Hệ thống tự động đếm số lượt xem và số lượt bấm vào nút CTA để đo lường tỷ lệ chuyển đổi (CTR).

---

## 4. Hướng dẫn từng bước: Tạo và Phát Thông Báo Mới

### Bước 1: Mở màn hình Tạo Thông Báo
1. Đăng nhập Admin Portal tại `/admin`.
2. Trên menu bên trái, vào **Vận Hành & CSKH** -> chọn **Thông Báo Hệ Thống (Announcements)** (`/admin/announcements`).
3. Nhấp nút **[ ➕ Tạo Thông Báo Mới ]** (`/admin/announcements/create`).

### Bước 2: Thiết lập nội dung và vị trí hiển thị
1. Điền các trường thông tin:
   * **Tiêu đề (`title`)**: Ví dụ: `Bảo trì hệ thống thanh toán VietQR từ 02:00 đến 03:00 sáng ngày 18/11`.
   * **Nội dung (`content`)**: `Nhằm nâng cấp cổng kết nối ngân hàng VietinBank, Mây Hẹn sẽ tạm dừng xử lý giao dịch tự động trong 60 phút. Các dịch vụ xem thiệp của khách mời vẫn hoạt động bình thường.`
   * **Phân loại (`type`)**: Chọn `warning` (Cảnh báo).
   * **Vị trí hiển thị (`placement`)**: Chọn `top_bar`.
2. Cấu hình nút kêu gọi hành động (CTA):
   * **Nút bấm (`cta_text`)**: `Xem lịch bảo trì`.
   * **Đường dẫn (`cta_url`)**: `/docs/faq/he-thong-bao-tri`.
3. Tùy chọn đóng thông báo: Tích chọn **Cho phép người dùng đóng thông báo (`is_dismissible = true`)**.

### Bước 3: Lên lịch phát sóng (Scheduling)
* **Thời gian bắt đầu (`starts_at`)**: Chọn ngày giờ bắt đầu hiển thị (ví dụ: `17/11/2026 12:00`). Nếu để trống, thông báo sẽ hiển thị ngay khi kích hoạt.
* **Thời gian kết thúc (`ends_at`)**: Chọn ngày giờ tự động hạ thông báo xuống (ví dụ: `18/11/2026 04:00`).
* **Trạng thái kích hoạt**: Tích chọn `is_active = true`.

### Bước 4: Lưu và Kiểm tra hiển thị thực tế
1. Nhấp **[ Xuất Bản Thông Báo ]**.
2. Hệ thống gọi `SiteAnnouncementService@createAnnouncement`:
   * Lưu bản ghi mới vào cơ sở dữ liệu.
   * Xóa bộ nhớ đệm hiển thị thông báo `Cache::forget('active_announcement_top_bar')`.
   * Lưu vết kiểm toán `ANNOUNCEMENT_CREATED` vào `audit_logs`.
3. Mở tab trình duyệt ẩn danh truy cập trang chủ `https://mayhen.vn`:
   * Xác nhận thanh Top Bar hiển thị màu sắc đúng chuẩn, chữ đọc rõ ràng trên cả máy tính lẫn điện thoại.
   * Thử bấm nút đóng `×` để kiểm tra lưu trạng thái trên `localStorage`.

---

## 5. Quản lý, Tạm dừng và Xóa Thông Báo

Tại trang danh sách `/admin/announcements`:
* **Bật / Tắt nhanh 1 chạm (`toggleStatus`)**: Nhấp công tắc cạnh thông báo để tạm dừng phát sóng khẩn cấp mà không cần xóa dữ liệu.
* **Chỉnh sửa nội dung**: Nhấp nút **[ Sửa ]** để thay đổi câu chữ hoặc kéo dài thời gian hiển thị `ends_at`.
* **Đo lường hiệu quả**: Cột **Lượt Xem / Lượt Click** hiển thị số lượng tương tác thực tế giúp đánh giá mức độ tiếp cận của cặp đôi.

---

## 6. Những điều Tuyệt đối Bị cấm trong Vận hành Thông Báo

1. **CẤM LẠM DỤNG MODAL POPUP:** Không phát các thông báo Modal Popup che kín màn hình cho những nội dung tiếp thị thông thường. Modal Popup chỉ dành cho cảnh báo gián đoạn dịch vụ nghiêm trọng để tránh làm giảm trải nghiệm Quiet Luxury của người xem thiệp.
2. **CẤM PHÁT THÔNG BÁO BẢO TRÌ SÁT GIỜ CƯỚI CAO ĐIỂM (11:00 - 13:00 và 17:00 - 20:00):** Mọi hoạt động bảo trì kỹ thuật bắt buộc phải lên lịch vào ban đêm (sau 01:00 sáng).
3. **CẤM DÙNG TỪ NGỮ GÂY HOANG MANG:** Tránh dùng các từ như *"Sập hệ thống"*, *"Lỗi nguy hiểm"*. Luôn dùng văn phong trấn an, chuyên nghiệp: *"Mây Hẹn đang tối ưu hóa trải nghiệm kết nối"*.

---

## 7. Bài viết liên quan
* [ADM-SUPP-001: Tiếp nhận và phân loại Ticket hỗ trợ theo độ ưu tiên (SLA Response Time)](file:///resources/docs/admin/08-support-messaging/ADM-SUPP-001.md)
* [ADM-SUPP-002: Quản lý và sử dụng thư viện câu trả lời mẫu chuẩn mực (Canned Responses)](file:///resources/docs/admin/08-support-messaging/ADM-SUPP-002.md)
* [ADM-SUPP-003: Tính năng Đóng vai hỗ trợ (Impersonation): Đăng nhập hỗ trợ khách an toàn](file:///resources/docs/admin/08-support-messaging/ADM-SUPP-003.md)
* [ADM-SUPP-005: Điều phối tin nhắn thông báo tập trung (Reliable Messaging Gateway Dispatcher)](file:///resources/docs/admin/08-support-messaging/ADM-SUPP-005.md)
