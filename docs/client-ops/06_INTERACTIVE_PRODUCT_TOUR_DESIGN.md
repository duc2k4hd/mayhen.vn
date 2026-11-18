# 06 — Interactive Product Tour Design (Client Account & Guest CRM)

## 1. Phạm vi & Nguyên tắc tách biệt tuyệt đối
Hệ thống hướng dẫn tương tác khu vực Quản lý Khách hàng (Client Operations) được thiết kế **tách biệt 100%** khỏi Product Tour của Studio Tạo Thiệp (`/tao-thiep`):

| Tiêu chí | Studio Tour (`/tao-thiep`) | Client Ops Tour A (`/tai-khoan`) | Client Ops Tour B (`/tai-khoan/thiep/{id}/khach-moi`) |
| :--- | :--- | :--- | :--- |
| **Route áp dụng** | `invitation.create` (`/tao-thiep`) | `account.dashboard` (`/tai-khoan`) | `guests.index` (`/tai-khoan/thiep/{id}/khach-moi`) |
| **File JS** | `public/assets/client/js/editor/product-tour.js` | `public/assets/client/js/components/client-ops-tour.js` | `public/assets/client/js/components/client-ops-tour.js` |
| **File CSS** | `public/assets/client/css/components/editor-tour.css` | `public/assets/client/css/components/client-ops-tour.css` | `public/assets/client/css/components/client-ops-tour.css` |
| **CSS Prefix** | `.mayhen_editor_tour_*` | `.mayhen_client_tour_*` | `.mayhen_client_tour_*` |
| **Nút mở lại** | `#mayhen_editor_tour_trigger_btn` | `#mayhen_account_tour_trigger_btn` | `#mayhen_guest_tour_trigger_btn` |
| **Storage Key** | `mayhen:editor:product-tour:v1:u_{id}` | `mayhen:client-ops-tour:v1:account_overview:u_{id}` | `mayhen:client-ops-tour:v1:guest_crm:u_{id}` |

---

## 2. Cam kết An toàn Dữ liệu & Quyền riêng tư
1. **Không bao giờ ghi hay biến đổi dữ liệu nghiệp vụ**: Cả 2 tour chỉ đọc tọa độ DOM (`getBoundingClientRect`), tự cuộn mượt đến phần tử và vẽ lớp phủ spotlight. Tuyệt đối không gửi form, không thêm/sửa/xóa khách mời, không cập nhật RSVP, không điểm danh, không sao chép clipboard hay tự mở Zalo.
2. **Không lưu PII trong trình duyệt**: Dữ liệu lưu trong `localStorage` (có fallback sang `sessionStorage` và bộ nhớ RAM khi trình duyệt chặn lưu trữ) chỉ chứa `{ version: 1, status: 'in_progress' | 'skipped' | 'completed', last_step_id, updated_at }`.
3. **Không làm phiền lặp lại**:
   - Tự mở **duy nhất 1 lần** khi người dùng lần đầu vào `/tai-khoan` hoặc lần đầu vào `/tai-khoan/thiep/{id}/khach-moi`.
   - Khi nhấn **Bỏ qua hướng dẫn**, nút đóng `×`, phím `Escape` hoặc **Hoàn tất**, trạng thái được ghi nhớ ngay để lần sau không tự mở lại.
   - Hỗ trợ tham số `?tour=0` để tắt tự mở (phục vụ kiểm thử tự động / liên kết nhanh) và `?tour=1` để mở chủ động.
   - Không tự bật đè lên modal đang mở (`.mayhen_account_modal.is-open`, `.mayhen_guest_modal.is-open`).

---

## 3. Chi tiết các bước hướng dẫn

### 3.1. Tour A — Tổng quan Tài khoản (`account_overview`, 7 bước)
1. **Không gian quản lý của hai bạn** (`[data-ops-tour-anchor="account-header"]`): Giới thiệu hồ sơ cặp đôi và nút tạo thêm thiệp cưới mới (hữu ích khi tách thiệp Nhà Trai / Nhà Gái).
2. **5 phân khu quản lý rõ ràng** (`[data-ops-tour-anchor="account-tabs"]`): Tổng Quan, Thiệp Cưới Của Tôi, Đơn Hàng & Gói Cước, Hồ Sơ & Bảo Mật, Hỗ Trợ.
3. **4 chỉ số cốt lõi minh bạch** (`[data-ops-tour-anchor="account-kpis"]`): Giải thích sự khác biệt giữa **Suất Dự Kiến** (kèm số lời mời) và **Người Xác Nhận Đến** (`người lớn + trẻ em`).
4. **Thiệp cưới đang chuẩn bị** (`[data-ops-tour-anchor="account-active-wedding"]`): Sao chép link thiệp chung, vào Studio, xem thiệp trực tiếp, tạo mã QR hoặc xuất bản (tự thích ứng cả khi tài khoản mới chưa có thiệp).
5. **Lối tắt vận hành trước, trong và sau lễ cưới** (`[data-ops-tour-anchor="account-quick-actions"]`): Đi thẳng vào Khách mời & RSVP, Sơ đồ bàn tiệc, Màn chiếu LED & Điều phối, Két kỷ niệm số.
6. **Sổ lưu bút & lời chúc mới nhất** (`[data-ops-tour-anchor="account-wishes"]`): Theo dõi lời chúc mới và bảo mật tuyệt đối các lời chúc riêng tư.
7. **Xem lại hướng dẫn bất cứ khi nào cần** (`#mayhen_account_tour_trigger_btn`): Vị trí nút **Hướng dẫn** cố định ở góc phải trên.

### 3.2. Tour B — Quản lý Khách mời & RSVP (`guest_crm`, 8 bước)
1. **Sổ Quản lý Khách mời & Phản hồi RSVP** (`[data-ops-tour-anchor="guest-header"]`): Vai trò trung tâm kết nối danh sách mời, xếp bàn, báo bếp và đón khách QR.
2. **Phân biệt rõ 6 con số quan trọng** (`[data-ops-tour-anchor="guest-kpi-grid"]`): Giải thích rành mạch **Số Lời Mời** ≠ **Suất Dự Kiến** ≠ **Người Xác Nhận Đến** (`người lớn + trẻ em`) ≠ **Báo Bận / Chờ Trả Lời** ≠ **Đã Đánh Dấu Gửi** ≠ **Đã Điểm Danh Đến Tiệc**.
3. **3 cách thêm khách mời linh hoạt** (`[data-ops-tour-anchor="guest-add-box"]`): Thêm từng khách, dán nhiều dòng nhanh, hoặc nhập tệp CSV mẫu chuẩn tiếng Việt (Excel UTF-8 BOM).
4. **Tìm kiếm & lọc khách theo tình trạng thực tế** (`[data-ops-tour-anchor="guest-filter-toolbar"]`): Lọc Họ Nhà Trai / Nhà Gái, nhóm, bàn tiệc, khách chưa mở thiệp, chưa phản hồi hoặc đã điểm danh.
5. **Link mời cá nhân hóa & cập nhật RSVP hộ khách** (`[data-ops-tour-anchor="guest-table"]`): Chép lời mời kèm link riêng để gửi qua Zalo/Messenger (minh bạch cơ chế chủ tiệc nhấn gửi), đánh dấu đã gửi và nhập RSVP hộ người lớn tuổi.
6. **Soát lỗi, Xếp bàn tiệc & Đón khách QR** (`[data-ops-tour-anchor="guest-primary-actions"]`): Bàn soát lỗi trước khi gửi, Sơ đồ bàn tiệc, Cổng quét QR tiếp tân, Xuất CSV & In A4.
7. **Ngăn công cụ mở rộng theo từng giai đoạn** (`#mayhen_guest_more_tools`): Mở ngăn phụ khi cần Nhắc nhở tế nhị, Lời cảm ơn sau cưới, Lọc trùng khách, Cổng gia đình hai họ và Bảng kê Báo bếp.
8. **Luôn sẵn sàng khi bạn muốn xem lại** (`#mayhen_guest_tour_trigger_btn`): Nhấn nút **Hướng dẫn** để mở lại bất cứ lúc nào.

---

## 4. Trải nghiệm Chuyển động & Đa thiết bị (Quiet Luxury Motion & Responsive)
- **Spotlight Glide**: Khung viền vàng ánh kim (`#C5A880`) lướt mượt từ phần tử trước sang phần tử tiếp theo bằng `cubic-bezier(0.22, 1, 0.36, 1)`.
- **Định vị thông minh**: Tự động đo khoảng trống dưới / trên / phải / trái phần tử mục tiêu; trên màn hình di động (`320px`–`767px`), hộp thoại tự neo gọn ở vùng thoáng không che khuất phần tử đang giới thiệu và không bao giờ tràn ngang màn hình.
- **Hỗ trợ bàn phím & Trợ năng**: `role="dialog"`, `aria-modal="true"`, điều hướng bằng phím `ArrowRight`, `ArrowLeft`, `Escape`, và tôn trọng `@media (prefers-reduced-motion: reduce)`.
