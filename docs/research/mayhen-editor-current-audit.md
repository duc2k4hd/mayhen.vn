# 🛠️ BÁO CÁO KIỂM TOÁN TOÀN DIỆN HỆ THỐNG TẠO THIỆP MÂY HẸN HIỆN TẠI (CURRENT SYSTEM AUDIT)

> **Dự án**: Mây Hẹn (`mayhen.vn`)  
> **Tài liệu**: Codebase Audit & System Gap Analysis  
> **Đối tượng kiểm toán**: Toàn bộ luồng tạo thiệp từ UI, Frontend JavaScript, Blade View, Laravel Controller, Service, Database Models và Migration.  
> **Thời gian kiểm toán**: 19/10/2026

---

## 1. TỔNG QUAN HIỆN TRẠNG KIẾN TRÚC EDITOR HIỆN TẠI

Hệ thống tạo thiệp hiện tại của Mây Hẹn được xây dựng dựa trên mô hình:
- **Cấu trúc màn hình**: Chia 2 cột (Desktop Split Screen):
  - Cột trái: Form nhập liệu phân bước (Stepper) được render động từ `schema.json` của từng template qua `InvitationTemplateService`.
  - Cột phải: Khung giả lập điện thoại iPhone nhúng một `<iframe>` trỏ về route xem thử `/xem-thu/{slug}?embed=1&mode=editor`.
- **Cơ chế truyền dữ liệu**: Khi người dùng gõ vào input ở form, JavaScript lắng nghe sự kiện `input`/`change`, đọc thuộc tính `data-sync-key` và gửi tin nhắn `postMessage` sang `iframe` với payload `{ type: 'MAYHEN_SYNC_INPUT', key, value }`.
- **Cơ chế lưu trữ**: Form gửi POST truyền thống hoặc AJAX về `InvitationEditorController@store`, dữ liệu được bóc tách và lưu vào 2 bảng `weddings` và `invitations`, sau đó redirect sang trang thiệp công khai `/thiep/{slug}`.

---

## 2. KIỂM TOÁN CHI TIẾT TỪNG PHÂN HỆ SOURCE CODE

### 2.1. Phân Hệ Route & Controller (`routes/web.php` & `InvitationEditorController.php`)
- **Điểm tích cực**:
  - Đã có cơ chế bảo vệ phân tầng: Template `plus`/`pro` yêu cầu người dùng phải đăng nhập trước khi tạo thiệp (`$template->tier !== 'free' && !auth()->check()`).
  - Hỗ trợ lưu trữ Transaction an toàn giữa bảng `weddings` và `invitations`.
  - Khử toàn diện chuỗi blob URL trước khi lưu vào DB (`$sanitizeBlobs`).
- **Lỗ hổng & Nhược điểm phát hiện được**:
  1. **Upload Asset sai vị trí kiến trúc**: Trong phương thức `uploadAsset` (dòng 248 - 288), file ảnh tải lên của người dùng được di chuyển trực tiếp vào:
     `resource_path("views/client/invitations/{$templateSlug}/assets")`.
     *Hậu quả nghiêm trọng*: Ảnh cá nhân của khách hàng bị ghi thẳng vào thư mục mã nguồn Git repository (`resources/views/`), vi phạm nguyên tắc tách biệt giữa Code và User Generated Content (UGC), không thể scale khi triển khai nhiều web server hoặc containerize Docker!
  2. **Thiếu cơ chế Autosave và Draft Status**: Controller chỉ có 1 action `store` duy nhất, không có endpoint riêng cho `autosave` nhẹ (chỉ cập nhật JSON draft mà không phải validate toàn bộ các trường bắt buộc).
  3. **Không có Concurrency Control / Revision ID**: Nếu người dùng mở 2 tab cùng sửa 1 thiệp, request sau sẽ ghi đè toàn bộ dữ liệu của request trước mà không có cảnh báo xung đột (Conflict Detection).

### 2.2. Phân Hệ Service & Schema (`InvitationTemplateService.php`)
- **Điểm tích cực**:
  - Có tư duy Schema-driven: Đọc `schema.json` từ template, nếu không có thì fallback sang hàm `generateFallbackSchema`.
- **Lỗ hổng & Nhược điểm phát hiện được**:
  1. **Trộn lẫn giữa Wedding Profile và Invitation Design**:
     Bảng `weddings` chứa các trường nội dung cố định (tên, ngày, địa chỉ, story, bank...), nhưng một số cấu hình layout lại bị nhét vào `wedding.extra_data` (ví dụ `gallery`, `timeline`). Nếu một cặp đôi tạo 2 thiệp khác nhau (1 thiệp nhà trai, 1 thiệp nhà gái) thì dữ liệu `extra_data` của wedding bị ghi đè lẫn nhau!
  2. **Thiếu Schema Versioning chuẩn mực**:
     Template chỉ có `current_version` dạng chuỗi thô (`v1`), không có JSON Schema Specification để validate cấu trúc `custom_config`. Khi nâng cấp template lên v2, các thiệp cũ tạo từ v1 có nguy cơ lỗi render.

### 2.3. Phân Hệ Blade View (`resources/views/client/pages/editor.blade.php`)
- **Điểm tích cực**:
  - Giao diện có Stepper Pills rõ ràng, có notch iPhone sang trọng, có modal Fullscreen xem thử trên điện thoại.
  - Tích hợp sẵn modal Canvas Cropper tỷ lệ 3:4 cho ảnh cưới.
- **Lỗ hổng & Nhược điểm phát hiện được**:
  1. **Tư duy Form-Centric thụ động**:
     Giao diện hoàn toàn là một biểu mẫu nhập liệu web hành chính (text inputs, textareas, selects). Không có cảm giác của một công cụ sáng tạo (Creative Tool).
  2. **Trải nghiệm trên điện thoại (Mobile UX) bị chia cắt**:
     Trên màn hình điện thoại, cột Preview bên phải bị ẩn đi, chỉ còn lại form nhập liệu. Người dùng muốn xem kết quả phải bấm nút "Xem Toàn Thiệp" để mở một modal pop-up chiếm toàn màn hình. Sau khi nhìn xong lại phải bấm nút Đóng để quay về form sửa tiếp! Quá trình này lặp đi lặp lại hàng chục lần khiến người dùng mệt mỏi, mất cảm xúc.
  3. **Không hỗ trợ tương tác trực tiếp (In-place Visual Interaction)**:
     Người dùng không thể chạm vào khối Lời chúc hay khối Sảnh tiệc trên màn hình preview để di chuyển vị trí, ẩn đi hoặc đổi màu sắc.

### 2.4. Phân Hệ Frontend JavaScript (`public/assets/client/js/pages/editor.js`)
- **Điểm tích cực**:
  - 100% Pure Vanilla JS (ES Modules), không dùng jQuery hay framework nặng.
  - Thuật toán Canvas Cropper hỗ trợ zoom, xoay góc và kéo vị trí hoạt động mượt mà.
- **Lỗ hổng & Nhược điểm phát hiện được**:
  1. **Không có State Store tập trung**:
     File dài 743 dòng code, dữ liệu trạng thái nằm rải rác trong DOM inputs, các biến toàn cục `pendingUploadBlobs`, `activeCropperTarget`, `currentStep`. Không có luồng dữ liệu một chiều (Single Source of Truth), dẫn đến việc đồng bộ giữa Form, Iframe và Cropper rất dễ bị lệch pha (desync).
  2. **Không có Undo / Redo Stack**:
     Nếu người dùng lỡ tay xóa một đoạn văn hay đổi nhầm màu thiệp, không có phím tắt `Ctrl + Z` hay nút "Hoàn tác" để lấy lại dữ liệu.
  3. **Đổi mẫu thiệp (Switch Template) làm gián đoạn luồng**:
     Khi người dùng bấm "Đổi mẫu", hệ thống chuyển hướng toàn trang bằng `window.location.href = ...`. Điều này khiến trang bị reload lại từ đầu, mất toàn bộ trạng thái cuộn, và nếu có dữ liệu chưa lưu sẽ bị thất lạc.

---

## 3. BẢNG TỔNG HỢP GAPS & NGUY CƠ HỆ THỐNG

| Khía Cạnh | Hiện Trạng Mây Hẹn V1 | Hậu Quả Đối Với Người Dùng & Sản Phẩm | Mức Độ Nghiêm Trọng |
| :--- | :--- | :--- | :--- |
| **Mô hình trải nghiệm** | Nhập form văn bản -> Đồng bộ Iframe xem trước | Cảm giác khô cứng, giống điền khảo sát hơn là làm thiệp cưới nghệ thuật. | **CAO** |
| **Trực quan hóa (WYSIWYG)** | Không thể click chọn section trên thiệp | Người dùng không biết trường form nào tương ứng với vị trí nào trên thiệp. | **RẤT CAO** |
| **Tùy biến bố cục** | Thứ tự section bị cố định chết theo code template | Khách không thể kéo mục Sổ lưu bút lên trước hay ẩn bớt mục Đếm ngược ngày cưới. | **CAO** |
| **Trải nghiệm Mobile** | Form che kín màn hình, preview phải mở qua modal | Cắt vụn hành trình, tỷ lệ bỏ dở (drop-off) trên điện thoại rất cao. | **CỰC KỲ CAO** |
| **Lưu nháp & Autosave** | Chỉ lưu khi bấm nút submit cuối cùng | Mất mạng hoặc vô tình đóng tab là mất sạch dữ liệu vừa nhập. | **CỰC KỲ CAO** |
| **Đổi mẫu thiệp** | Reload trang web bằng query string | Làm gián đoạn trải nghiệm, tạo cảm giác giật cục, thiếu chuyên nghiệp. | **TRUNG BÌNH** |
| **Quản lý Media** | Lưu file vào `resources/views/client/invitations/.../assets` | Vi phạm kiến trúc bảo mật và lưu trữ của Laravel, không deploy được đa cụm server. | **CỰC KỲ CAO** |
| **State Management** | Đọc ghi DOM trực tiếp, không có State Controller | Dễ phát sinh bug tiềm ẩn khi thêm tính năng mới, khó mở rộng lên hàng trăm mẫu. | **CAO** |

---

## 4. KẾT LUẬN KIỂM TOÁN

Editor hiện tại của Mây Hẹn là một phiên bản thử nghiệm (Prototype) đã chứng minh được tính khả thi của việc đồng bộ `postMessage` sang Iframe và Canvas Cropper. Tuy nhiên, nó **chưa đủ tầm vóc để trở thành một sản phẩm thương mại cạnh tranh sòng phẳng với các đối thủ hàng đầu**.

Để đưa Mây Hẹn lên vị thế dẫn đầu trong phân khúc **Quiet Luxury Wedding Invitations**, việc refactor lên **Invitation Editor V2** là hoàn toàn cấp bách và bắt buộc, tập trung vào:
1. Kiến trúc lưu trữ chuẩn qua `storage/app/public/` và Media Library.
2. Bộ State Controller Vanilla JS quản lý trạng thái tập trung.
3. Trải nghiệm 2 tầng: Quick Editor (nhập siêu tốc) + Visual Section Editor (chạm trực tiếp, kéo thả section, tùy biến font/màu/hiệu ứng).
4. Cơ chế Autosave ngầm + Undo/Redo an toàn.
5. Mobile-first layout: Canvas luôn hiển thị, công cụ điều khiển dạng trượt ngữ cảnh (Contextual Bottom Sheet).
