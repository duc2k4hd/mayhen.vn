# 📋 BẢNG PHÂN RÃ CÔNG VIỆC CHI TIẾT (EDITOR V2 DETAILED TASK BREAKDOWN)

> **Dự án**: Mây Hẹn (`mayhen.vn`)  
> **Tài liệu**: Technical Work Breakdown Structure (WBS)  
> **Nguyên tắc**: Task nhỏ, độc lập, có dependency rõ ràng, có tiêu chí nghiệm thu (DoD) và kịch bản kiểm thử cụ thể.

---

## DANH SÁCH CÁC TASK THEO THỨ TỰ THỰC THI

---

### 🔹 TASK-E2-001: Khắc Phục Lưu Trữ Media & Bổ Sung Schema Columns Cho Bảng `invitations`
- **Mục tiêu**: Đưa toàn bộ file ảnh tải lên vào đúng chuẩn `storage/app/public/weddings/` và bổ sung các cột `schema_version`, `revision_id`, `draft_config` vào database.
- **Vấn đề giải quyết**: Chấm dứt triệt để việc lưu file người dùng vào thư mục mã nguồn `resources/views/client/invitations/.../assets`.
- **Mức độ ưu tiên**: **P0 (Bắt buộc làm đầu tiên)**
- **Dependency**: Không có.
- **Files cần sửa/tạo**:
  - Tạo mới: `database/migrations/2026_10_20_000001_add_editor_v2_columns_to_invitations_table.php`
  - Sửa: `app/Http/Controllers/Client/InvitationEditorController.php` (phương thức `uploadAsset`)
  - Sửa: `app/Models/Invitation.php` (thêm `$fillable` và casts JSON)
- **Database impact**: Bổ sung 4 cột mới: `schema_version` (varchar), `revision_id` (bigint), `draft_config` (json), `last_autosaved_at` (timestamp). Không xóa hay sửa cột cũ.
- **Implementation approach**:
  1. Chạy migration tạo cột mới.
  2. Sửa `uploadAsset` chuyển sang dùng `Storage::disk('public')->putFileAs(...)`.
  3. Đảm bảo chạy lệnh `php artisan storage:link`.
- **Acceptance criteria**:
  - Upload thử 1 ảnh: File được lưu tại `storage/app/public/weddings/{wedding_id}/photos/{hash}.webp` và truy cập được qua URL `/storage/...`.
  - Không có bất kỳ file nào được ghi vào `resources/views/`.
- **Testing**: Gửi request POST upload ảnh qua Postman/curl và kiểm tra ổ đĩa.
- **Risk & Rollback**: Thấp. Rollback bằng `php artisan migrate:rollback --step=1`.
- **Skill cần đọc**: `mayhen-database`, `mayhen-laravel-backend`.
- **Definition of Done (DoD)**: Migration chạy thành công, ảnh lưu đúng thư mục storage, code format bằng Pint agent.

---

### 🔹 TASK-E2-002: Xây Dựng Động Cơ State Store Vanilla JS & LocalStorage Recovery
- **Mục tiêu**: Tạo module JavaScript quản lý trạng thái tập trung (Single State Store) với luồng dữ liệu 1 chiều, cơ chế Undo/Redo và phục hồi bản nháp offline.
- **Vấn đề giải quyết**: Loại bỏ hoàn toàn tình trạng state bị phân tán trong các ô input DOM và biến toàn cục, chấm dứt nguy cơ mất dữ liệu khi mất mạng hoặc reload trang.
- **Mức độ ưu tiên**: **P0**
- **Dependency**: TASK-E2-001
- **Files cần sửa/tạo**:
  - Tạo mới: `public/assets/client/js/editor/store.js`
  - Tạo mới: `public/assets/client/js/editor/actions.js`
  - Tạo mới: `public/assets/client/js/editor/history.js`
  - Tạo mới: `public/assets/client/js/editor/autosaver.js`
- **Implementation approach**:
  1. `store.js`: Quản lý object `state = { wedding, custom_config, revision_id, selectedSection, isDirty }`.
  2. `history.js`: Giữ mảng `pastStates` (tối đa 20 snapshot), xử lý hàm `undo()` và `redo()`.
  3. `autosaver.js`: Lắng nghe sự kiện `state-changed`, lưu ngay vào `localStorage.setItem('mayhen_draft_' + id)`, sau đó debounce 1500ms gửi request ngầm lên `/api/editor/autosave`.
- **Acceptance criteria**:
  - Thay đổi 1 giá trị: State cập nhật, Topbar hiển thị 🟡 *Đang lưu...* -> 🟢 *Đã lưu nháp*.
  - Bấm Ctrl+Z: Giá trị cũ được khôi phục chính xác.
  - Tắt mạng Wifi hoặc tắt tab rồi mở lại: Form tự động phục hồi lại đúng 100% dữ liệu đang gõ dở.
- **Testing**: Mở DevTools Console, ngắt mạng giả lập (Network Offline) và kiểm tra khôi phục từ LocalStorage.
- **Definition of Done (DoD)**: Các module JS hoạt động độc lập, không lỗi console, có unit logic test rõ ràng.

---

### 🔹 TASK-E2-003: Xây Dựng Endpoint Backend Phục Vụ Autosave & Concurrency Lock
- **Mục tiêu**: Cung cấp API nhẹ cho việc lưu nháp định kỳ và xử lý khóa lạc quan chống xung đột nhiều tab.
- **Vấn đề giải quyết**: Không bắt buộc phải validate các trường bắt buộc khi đang lưu nháp dở dang; phát hiện và ngăn chặn việc ghi đè dữ liệu cũ.
- **Mức độ ưu tiên**: **P0**
- **Dependency**: TASK-E2-001
- **Files cần sửa/tạo**:
  - Sửa: `routes/web.php` (thêm route `POST /api/editor/autosave`)
  - Sửa: `app/Http/Controllers/Client/InvitationEditorController.php` (thêm hàm `autosave`)
  - Sửa: `app/Services/InvitationTemplateService.php` (hàm `saveDraftWithLock`)
- **Implementation approach**:
  1. Nhận payload JSON `{ invitation_id, revision_id, custom_config, wedding_delta }`.
  2. Kiểm tra quyền sở hữu (`isOwner`).
  3. So sánh `revision_id`: Nếu nhỏ hơn trong DB, trả về `409 Conflict`.
  4. Cập nhật cột `draft_config` và `wedding_delta`, tăng `revision_id` lên 1, trả về `{ success: true, new_revision_id, saved_at }`.
- **Acceptance criteria**:
  - Request autosave trả kết quả trong vòng < 50ms.
  - Tab có revision cũ bị từ chối lưu và nhận thông báo cảnh báo xung đột.
- **Definition of Done (DoD)**: Endpoint bảo mật tuyệt đối, có transaction an toàn, kiểm thử Pint pass.

---

### 🔹 TASK-E2-004: Tái Cấu Trúc Khung Màn Hình Studio V2 (Desktop 4-Zone & Mobile Viewport)
- **Mục tiêu**: Xây dựng cấu trúc HTML & CSS chuẩn Quiet Luxury cho Editor V2: Topbar dính, Left Tool Rail, Center Stage Phone Mockup và Right Inspector Panel trên desktop; Contextual Bottom Sheet trên mobile.
- **Vấn đề giải quyết**: Xóa bỏ hoàn toàn giao diện form hành chính chật chội; tạo cảm giác một xưởng thiết kế cao cấp thực sự.
- **Mức độ ưu tiên**: **P0**
- **Dependency**: TASK-E2-002
- **Files cần sửa/tạo**:
  - Sửa: `resources/views/client/pages/editor.blade.php`
  - Sửa: `public/assets/client/css/pages/editor.css`
  - Tạo mới: `public/assets/client/css/pages/editor-v2.css`
- **UI Requirements**:
  - Desktop: Center Canvas hiển thị khung iPhone 390px viền bo tròn 44px sang trọng, đổ bóng mềm.
  - Mobile: Canvas thiệp tràn màn hình, Bottom Bar 4 nút, Bottom Sheet trượt êm ái khi chạm.
- **Acceptance criteria**:
  - Giao diện đạt chuẩn Quiet Luxury: màu kem ấm `#FAF8F5`, viền mạ vàng champagne hairline, typography sắc nét.
  - Trên iPhone/Android: Bàn phím ảo mở lên không làm che khuất nội dung đang nhập.
- **Definition of Done (DoD)**: 100% Pure CSS có prefix `mayhen_editor_*`, responsive hoàn hảo trên mọi kích thước màn hình từ 360px đến 4K.

---

### 🔹 TASK-E2-005: Xây Dựng Tính Năng Đổi Mẫu Thiệp Tức Thì Không Reload (Zero-Loss Switch)
- **Mục tiêu**: Cho phép người dùng chuyển đổi qua lại giữa các mẫu thiệp khác nhau ngay trong studio mà không làm reload trang web và không mất 1 chữ nào.
- **Vấn đề giải quyết**: Khắc phục trải nghiệm giật cục khi phải reload trang qua query string URL.
- **Mức độ ưu tiên**: **P0**
- **Dependency**: TASK-E2-004
- **Files cần sửa/tạo**:
  - Sửa: `public/assets/client/js/editor/store.js`
  - Tạo mới: `public/assets/client/js/editor/template-switcher.js`
  - Sửa: `resources/views/client/pages/editor.blade.php` (Template Drawer Modal)
- **Implementation approach**:
  1. Khi người dùng chọn mẫu mới: Đẩy action `SWITCH_TEMPLATE`.
  2. Adapter bóc tách dữ liệu cưới chung (tên, ngày, giờ, sảnh tiệc, ảnh...) giữ nguyên.
  3. Cập nhật `src` của Canvas iframe sang template mới kèm dữ liệu đã đồng bộ qua `postMessage`.
  4. Cập nhật chip hiển thị trên Topbar mượt mà.
- **Acceptance criteria**:
  - Đổi mẫu hoàn tất trong < 0.5 giây.
  - Toàn bộ tên cô dâu chú rể, ngày cưới, ảnh bìa hiển thị chuẩn xác trên mẫu mới ngay lập tức.
- **Definition of Done (DoD)**: Trải nghiệm liền mạch không chớp màn hình, có thông báo Toast chúc mừng và nút Hoàn tác mẫu cũ.

---

### 🔹 TASK-E2-006: Triển Khai Cơ Chế Chạm Chọn Trực Tiếp & Kéo Thả Section (Direct Visual DnD)
- **Mục tiêu**: Cho phép người dùng click chuột/chạm tay vào từng section trên thiệp để sửa trực tiếp, và nhấn giữ tay cầm kéo thả để sắp xếp lại thứ tự các phần.
- **Vấn đề giải quyết**: Biến Mây Hẹn từ một form nhập liệu bị động thành một công cụ thiết kế trực quan sống động.
- **Mức độ ưu tiên**: **P0**
- **Dependency**: TASK-E2-004, TASK-E2-005
- **Files cần sửa/tạo**:
  - Tạo mới: `public/assets/client/js/editor/dnd-section.js`
  - Tạo mới: `public/assets/client/js/editor/inspector-controller.js`
  - Sửa: `resources/views/client/invitations/thuoc-phim-tinh-yeu/template.blade.php` (bổ sung data attributes cho section wrapper)
  - Sửa: `resources/views/client/invitations/net-muc-tinh-yeu/template.blade.php`
- **Implementation approach**:
  1. Bọc mỗi section trong template bằng thẻ có thuộc tính `data-editor-section="id"`.
  2. Lắng nghe `click` trên section: Phát tin nhắn sang trang cha để mở đúng panel tương ứng trên Right Inspector.
  3. Lắng nghe `pointerdown` trên drag handle: Bắt đầu tính toán kéo thả, hiển thị Drop Indicator Bar, hoán đổi thứ tự trong mảng `state.sections`.
- **Acceptance criteria**:
  - Click vào khối Sự kiện cưới: Inspector bên phải mở ngay tab cài đặt Sự kiện cưới.
  - Kéo mục Sổ lưu bút lên trên mục Lịch trình: Thứ tự trên Canvas đổi ngay lập tức, tự động kích hoạt autosave.
  - Hỗ trợ thao tác mượt mà bằng cả chuột máy tính lẫn ngón tay trên màn hình cảm ứng điện thoại.
- **Definition of Done (DoD)**: Hoạt động mượt mà 60 FPS, không dùng thư viện ngoài, có phím tắt Undo quay lại vị trí cũ.

---

### 🔹 TASK-E2-007: Tích Hợp Bộ 6 Bảng Màu Thượng Lưu (Curated Palette Picker) & Tùy Biến Mở Phong Thư
- **Mục tiêu**: Cho phép cặp đôi đổi tông màu toàn thiệp chỉ bằng 1 chạm và tùy biến con dấu sáp hoàng gia.
- **Mức độ ưu tiên**: **P1**
- **Dependency**: TASK-E2-006
- **Files cần sửa/tạo**:
  - Tạo mới: `public/assets/client/js/editor/theme-controller.js`
  - Sửa: `public/assets/client/css/pages/editor-v2.css`
- **Acceptance criteria**:
  - Chọn bảng màu *Sage Botanic*: Màu chữ, nút bấm, viền và tem sáp trên toàn bộ thiệp tự động chuyển sang sắc xanh olive thanh lịch.
  - Tùy biến tem sáp: Cho phép chọn màu sáp (Đỏ Bordeaux, Vàng Champagne, Hồng Khói) và đổi chữ dập nổi (ví dụ: MH, ĐP, 2027).
- **Definition of Done (DoD)**: Phản chiếu thời gian thực trên Canvas, lưu cấu hình vào `custom_config.theme`.

---

### 🔹 TASK-E2-008: Kiểm Thử Toàn Diện, Tối Ưu Hiệu Năng & Regression Testing
- **Mục tiêu**: Kiểm thử toàn bộ 20 kịch bản test case bắt buộc (mạng chậm, mất mạng, tên dài, đổi mẫu liên tục, 2 tab xung đột, mobile keyboard), chạy Pint format và tài liệu hóa.
- **Mức độ ưu tiên**: **P0**
- **Dependency**: Hoàn thành toàn bộ từ TASK-E2-001 đến TASK-E2-007
- **Acceptance criteria**:
  - 100% test scenarios vượt qua (PASS).
  - Không có lỗi JavaScript Console hay cảnh báo PHP nào.
  - Điểm Google Lighthouse Performance của trang thiệp cưới đạt > 90.
- **Definition of Done (DoD)**: Bộ kiểm thử hoàn chỉnh, code đạt chuẩn PSR-12, cập nhật `context.gemini.md` đầy đủ.
