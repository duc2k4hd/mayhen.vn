# 📦 DANH MỤC KIỂM KÊ COMPONENT GIAO DIỆN (EDITOR V2 COMPONENT INVENTORY)

> **Dự án**: Mây Hẹn (`mayhen.vn`)  
> **Tài liệu**: Frontend Component Inventory & Design System Tokens  
> **Quy ước tiền tố bắt buộc**: `mayhen_editor_*`  
> **Nguyên tắc kỹ thuật**: Pure Vanilla CSS + Native Web Components / ES Modules, không sử dụng framework ngoài.

---

## 1. HỆ THỐNG TIỀN TỐ CSS CLASS CHUẨN MÂY HẸN (PREFIX NAMESPACE)

Mọi class CSS phục vụ phân hệ Editor V2 bắt buộc phải tuân thủ nghiêm ngặt bảng phân rã namespace dưới đây:

| Namespace Phân Khu | Phạm Vi Sử Dụng | Ví Dụ Class Chuẩn |
| :--- | :--- | :--- |
| `mayhen_editor_topbar_*` | Thanh tiêu đề trên cùng, trạng thái lưu, nút điều khiển | `.mayhen_editor_topbar_wrap`, `.mayhen_editor_topbar_btn` |
| `mayhen_editor_rail_*` | Thanh công cụ dọc bên trái (Desktop Left Rail) | `.mayhen_editor_rail_nav`, `.mayhen_editor_rail_item` |
| `mayhen_editor_canvas_*` | Vùng sân khấu trung tâm & khung điện thoại hiển thị thiệp | `.mayhen_editor_canvas_stage`, `.mayhen_editor_canvas_phone` |
| `mayhen_editor_section_*`| Các khối section của thiệp trên canvas & thanh công cụ ngữ cảnh | `.mayhen_editor_section_box`, `.mayhen_editor_section_handle` |
| `mayhen_editor_inspector_*`| Bảng thuộc tính bên phải hiển thị công cụ chỉnh sửa theo ngữ cảnh | `.mayhen_editor_inspector_panel`, `.mayhen_editor_inspector_field` |
| `mayhen_editor_media_*` | Thư viện ảnh, uploader kéo thả, khung cắt ảnh nghệ thuật | `.mayhen_editor_media_uploader`, `.mayhen_editor_media_cropper` |
| `mayhen_editor_palette_*` | Bộ chọn bảng màu sắc Quiet Luxury có sẵn | `.mayhen_editor_palette_card`, `.mayhen_editor_palette_swatch` |
| `mayhen_editor_sheet_*` | Bảng trượt ngữ cảnh từ đáy trên thiết bị di động (Mobile Sheet) | `.mayhen_editor_sheet_drawer`, `.mayhen_editor_sheet_grabber` |
| `mayhen_editor_modal_*` | Các hộp thoại: Đổi mẫu, Xem toàn thiệp, Xác nhận xuất bản | `.mayhen_editor_modal_dialog`, `.mayhen_editor_modal_backdrop` |

---

## 2. BẢNG CHI TIẾT CÁC COMPONENT GIAO DIỆN CỐT LÕI

### 2.1. Phân Hệ Điều Khiển Khung (Frame & Navigation Components)

| Tên Component | Tiền Tố Class | Trách Nhiệm Giao Diện | Các Trạng Thái (States) | File Định Nghĩa |
| :--- | :--- | :--- | :--- | :--- |
| **Studio Topbar** | `mayhen_editor_topbar_*` | Chứa logo, tên thiệp, trạng thái lưu, undo/redo, nút xuất bản | `is-sticky`, `is-saving`, `is-saved`, `is-offline` | `css/pages/editor-v2.css` |
| **Save Status Pill** | `mayhen_editor_status_pill` | Hiển thị chấm tròn xanh/vàng/đỏ báo tình trạng autosave | `status--saved`, `status--saving`, `status--error` | `css/pages/editor-v2.css` |
| **History Controls** | `mayhen_editor_history_btn` | Hai nút Undo / Redo kèm tooltip phím tắt (Ctrl+Z / Ctrl+Y) | `is-disabled` (hết lịch sử), `is-active`, `hover` | `css/pages/editor-v2.css` |
| **Device Switcher** | `mayhen_editor_device_tabs` | Chuyển đổi khung xem: Mobile (390px), Tablet (768px), Desktop | `tab--active`, `hover` | `css/pages/editor-v2.css` |
| **Template Chip** | `mayhen_editor_template_chip` | Thẻ bo tròn hiển thị ảnh thu nhỏ & tên template đang active | `hover`, `focus-visible`, `is-switching` | `css/pages/editor-v2.css` |
| **Publish Button** | `mayhen_editor_btn_publish` | Nút kêu gọi hành động chính mạ vàng cát dập nổi | `hover`, `active`, `is-loading` (quay spinner) | `css/pages/editor-v2.css` |

### 2.2. Phân Hệ Sân Khấu & Tương Tác Canvas (Canvas & In-Place Components)

| Tên Component | Tiền Tố Class | Trách Nhiệm Giao Diện | Các Trạng Thái (States) | File Định Nghĩa |
| :--- | :--- | :--- | :--- | :--- |
| **Phone Viewport** | `mayhen_editor_canvas_phone`| Khung vỏ kim loại iPhone với notch, viền bo tròn 44px và bóng đổ | `mode--mobile`, `mode--tablet`, `is-fullscreen` | `css/pages/editor-v2.css` |
| **Section Overlay Box** | `mayhen_editor_section_box` | Khung viền chỉ vàng bọc ngoài section khi rê chuột hoặc bấm chọn | `is-hovered` (viền đứt), `is-selected` (viền liền vàng) | `css/pages/editor-v2.css` |
| **Section Drag Handle** | `mayhen_editor_section_handle` | Tay cầm biểu tượng `⠿` để nhấn giữ kéo thả thay đổi vị trí | `hover`, `is-dragging`, `touch-active` | `css/pages/editor-v2.css` |
| **Drop Indicator Bar** | `mayhen_editor_drop_indicator`| Vạch nam châm màu vàng cát báo hiệu vị trí thả section | `is-visible`, `animate-pulse` | `css/pages/editor-v2.css` |
| **Section Action Float**| `mayhen_editor_section_actions`| Menu nổi nhỏ (Ẩn/Hiện, Nhân bản, Xóa, Cài đặt) ghim ở góc | `hover`, `active` | `css/pages/editor-v2.css` |

### 2.3. Phân Hệ Bảng Thuộc Tính & Nhập Liệu (Inspector & Form Controls)

| Tên Component | Tiền Tố Class | Trách Nhiệm Giao Diện | Các Trạng Thái (States) | File Định Nghĩa |
| :--- | :--- | :--- | :--- | :--- |
| **Inspector Header** | `mayhen_editor_inspector_head`| Tiêu đề section đang sửa kèm nút đóng hoặc nút xem hướng dẫn | `default` | `css/pages/editor-v2.css` |
| **Property Group** | `mayhen_editor_prop_group` | Cụm trường nhập liệu có tiêu đề nhóm (ví dụ: "Thông tin tiệc") | `is-collapsed`, `is-expanded` | `css/pages/editor-v2.css` |
| **Clean Input Field** | `mayhen_editor_input` | Ô gõ chữ viền mỏng hairline, nền giấy ngà, focus viền vàng | `default`, `focus`, `is-invalid`, `is-disabled` | `css/pages/editor-v2.css` |
| **Toggle Switch** | `mayhen_editor_toggle` | Công tắc gạt tròn bật/tắt tính năng (ví dụ: Bật RSVP, Bật QR) | `checked`, `unchecked`, `disabled` | `css/pages/editor-v2.css` |
| **Palette Card** | `mayhen_editor_palette_card` | Thẻ hình chữ nhật bo góc chứa 3 chấm màu hài hòa kèm tên gọi | `is-selected` (viền đôi vàng), `hover` | `css/pages/editor-v2.css` |
| **Font Style Pill** | `mayhen_editor_font_pill` | Nút chọn kiểu chữ Serif quý phái (Playfair / Cormorant / Cinzel) | `is-active`, `hover` | `css/pages/editor-v2.css` |

### 2.4. Phân Hệ Quản Lý Hình Ảnh & Media (Media & Cropper Components)

| Tên Component | Tiền Tố Class | Trách Nhiệm Giao Diện | Các Trạng Thái (States) | File Định Nghĩa |
| :--- | :--- | :--- | :--- | :--- |
| **Dropzone Uploader** | `mayhen_editor_media_dropzone`| Khung viền nét đứt kéo thả ảnh từ máy tính, có icon máy ảnh | `is-dragover` (phóng to viền), `is-uploading` | `css/pages/editor-v2.css` |
| **Gallery Thumbnail Grid**| `mayhen_editor_media_grid` | Lưới hiển thị các ảnh cưới đã tải lên, kèm nút xóa và nút sửa | `default`, `item--reordering` | `css/pages/editor-v2.css` |
| **Canvas Cropper Modal**| `mayhen_editor_cropper_modal` | Hộp thoại cắt ảnh 3:4 với thanh công cụ zoom, xoay, reset | `is-open`, `is-processing` | `css/pages/editor-v2.css` |
| **Crop Overlay Mask** | `mayhen_editor_cropper_mask` | Lớp phủ tối xung quanh, chừa ra khung nhìn tỷ lệ vàng 3:4 | `default` | `css/pages/editor-v2.css` |

### 2.5. Phân Hệ Trải Nghiệm Di Động (Mobile-Specific Components)

| Tên Component | Tiền Tố Class | Trách Nhiệm Giao Diện | Các Trạng Thái (States) | File Định Nghĩa |
| :--- | :--- | :--- | :--- | :--- |
| **Mobile Bottom Bar** | `mayhen_editor_mobile_bar` | Thanh 4 nút cố định ở đáy màn hình điện thoại | `is-sticky-bottom`, `safe-area-inset` | `css/pages/editor-v2.css` |
| **Context Bottom Sheet**| `mayhen_editor_sheet_panel` | Bảng điều khiển trượt từ đáy, có thanh kéo grabber ngón tay | `sheet--peek` (30%), `sheet--half` (50%), `sheet--full` | `css/pages/editor-v2.css` |
| **Sheet Grabber** | `mayhen_editor_sheet_grabber`| Thanh nhựa bo tròn dài 40px ở đỉnh sheet để vuốt lên/xuống | `hover`, `is-dragging` | `css/pages/editor-v2.css` |
