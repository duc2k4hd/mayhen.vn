---
name: mayhen-visual-editor
description: Tiêu chuẩn thiết kế, kiến trúc và phát triển bộ công cụ tạo thiệp cưới trực quan (Invitation Editor V2) cho dự án Mây Hẹn (mayhen.vn).
---

# 🎨 MAYHEN VISUAL EDITOR V2 — AGENT SPECIFICATION & DEVELOPMENT SKILL

> **Phạm vi áp dụng**: Mọi tác vụ liên quan đến phân hệ tạo và chỉnh sửa thiệp cưới (`/tao-thiep`, `/tai-khoan/thiep/*`, canvas, live preview iframe, stepper navigation, smart scroll-on-action, drag & drop, autosave, media uploader, vietqr).

---

## 1. NGUYÊN TẮC CỐT LÕI (CORE PRINCIPLES)

1. **Strict Vanilla Only**:
   - Tuyệt đối **KHÔNG** sử dụng bất kỳ thư viện ngoài nào (CẤM: React, Vue, Alpine, jQuery, Tailwind, Bootstrap, GSAP, Fabric.js, Konva.js, SortableJS, Interact.js).
   - 100% mã nguồn sử dụng: **Pure HTML5 + Pure Vanilla CSS + Pure Native JavaScript (ES Modules)**.
2. **Khung 10 Section Tiêu Chuẩn Bất Biến**:
   - Toàn bộ hệ thống Studio Editor và các Mẫu Thiệp bắt buộc phải đồng bộ chuẩn xác theo đúng thứ tự 10 Section tiêu chuẩn:
     1. `hero` (Trang Bìa / Mở Đầu)
     2. `story` (Lời Ngỏ & Chuyện Tình Yêu)
     3. `parents` (Gia Đình Song Thân & Đôi Uyên Ương)
     4. `countdown_calendar` (Đếm Ngược & Lịch Cưới)
     5. `events` (Sự Kiện Hôn Lễ & Bản Đồ)
     6. `timeline` (Lịch Trình Hôn Lễ)
     7. `dresscode` (Quy Định Trang Phục)
     8. `album` (Khoảnh Khắc Hạnh Phúc - 10 Ảnh)
     9. `gift` (Hộp Mừng Cưới & VietQR)
     10. `ending` (Lời Cảm Ơn, Lời Chúc & RSVP)
3. **Giao Thức Tự Động Cuộn Thông Minh (Smart Scroll-on-Action Protocol)**:
   - Khi người dùng chuyển bước (Stepper 1..10) hoặc thao tác trên bất kỳ trường dữ liệu/ảnh nào trong form bên trái, màn hình Preview bên phải **BẮT BUỘC TỰ ĐỘNG CUỘN ĐẾN ĐÚNG VỊ TRÍ ĐÓ** và kích hoạt hiệu ứng Highlight vàng kim Quiet Luxury để mang lại trải nghiệm xem trước mượt mà nhất.
4. **Phân Tách Dữ Liệu Tuyệt Đối (Data Decoupling)**:
   - Dữ liệu đám cưới cốt lõi lưu trong bảng `weddings`.
   - Cấu hình visual/layout lưu trong `invitations.custom_config` có version định danh (`schema_version: "2.0.0"`).
   - Đổi mẫu thiệp không bao giờ làm mất 1 ký tự nào của thông tin cưới (Zero Data Loss).
5. **Bảo Toàn Bộ Gõ Tiếng Việt & IME (Unikey/EVKey)**:
   - TUYỆT ĐỐI KHÔNG can thiệp hoặc gán đè `target.value` trong sự kiện `input` để tránh nuốt chữ hoặc nhảy con trỏ chuột khi người dùng đang gõ tiếng Việt. Chỉ chuẩn hóa khi `blur`.

---

## 2. QUY ƯỚC ĐẶT TÊN CSS & NAMESPACE

Mọi class CSS của phân hệ Editor bắt buộc phải có tiền tố `mayhen_editor_*`:
- Container & Wrapper: `mayhen_editor_wrapper`, `mayhen_editor_v2_container`
- Topbar: `mayhen_editor_topbar_*`
- Left Tool Rail: `mayhen_editor_rail_*`
- Drawer / Inspector Panel: `mayhen_editor_drawer_*`, `mayhen_editor_step_*`
- Stepper Nav Pills: `mayhen_editor_stepper_*`
- Center Stage & Preview: `mayhen_editor_center_stage`, `mayhen_editor_preview_*`
- Media & Cropper: `mayhen_editor_upload_*`, `mayhen_cropper_*`
- Palette Picker: `mayhen_editor_palette_*`
- Target Highlight Class: `.mayhen_editor_target_highlight`

---

## 3. GIAO THỨC TRUYỀN THÔNG 2 CHIỀU (EDITOR ⇄ IFRAME BRIDGE)

Studio Editor và Template trong Iframe giao tiếp với nhau thông qua native `window.postMessage` với cấu trúc JSON chuẩn hóa:

### 3.1. Handshake Khởi Đầu (`MAYHEN_BRIDGE_READY`)
- **Người gửi**: Iframe (`editor-bridge.blade.php`).
- **Người nhận**: Studio Editor (`editor.js`).
- **Ý nghĩa**: Báo hiệu Iframe đã nạp xong DOM và sẵn sàng nhận dữ liệu form, bảng màu và cấu hình ban đầu.

### 3.2. Đồng Bộ Dữ Liệu Thời Gian Thực (`MAYHEN_SYNC_INPUT`)
- **Payload**: `{ type: 'MAYHEN_SYNC_INPUT', key: string, value: string }`
- **Ý nghĩa**: Khi người dùng gõ vào ô nhập liệu hoặc tải ảnh, Iframe tự động cập nhật textContent, ảnh nền, định dạng ngày tháng, âm lịch, monogram, cha mẹ hai bên gia đình.

### 3.3. Cuộn Tới Section Theo Stepper (`MAYHEN_SCROLL_TO_SECTION`)
- **Payload**: `{ type: 'MAYHEN_SCROLL_TO_SECTION', section_id: string, step: number }`
- **Ý nghĩa**: Khi người dùng click chọn 1 trong 10 tab trên Stepper:
  - `step === 1` hoặc `section_id === 'hero'`: Cuộn mượt về đầu trang (`window.scrollTo({ top: 0, behavior: 'smooth' })`).
  - `section_id` tương ứng với 10 Section tiêu chuẩn: Tìm theo `[data-mayhen-section="{id}"]` hoặc fallback theo số thứ tự `step` (1..10).
  - Tự động kích hoạt mở hộp quà nếu bước được chọn là Section 9 (`gift`).

### 3.4. Cuộn Tới Phần Tử Mục Tiêu & Highlight (`MAYHEN_SCROLL_TO_TARGET`)
- **Payload**: `{ type: 'MAYHEN_SCROLL_TO_TARGET', target: string, action: 'focus' | 'type' | 'upload' | 'uploaded' | 'bank' }`
- **Ý nghĩa**: Kích hoạt khi:
  1. Người dùng `focusin` vào bất kỳ ô nhập liệu nào (Tên chú rể, cô dâu, ngày cưới, địa điểm, STK, v.v.).
  2. Người dùng đang gõ (`input` debounced).
  3. Người dùng click vào nút/slot chọn ảnh (`label[for^="upload_"]`, `.mayhen_editor_upload_btn`).
  4. Người dùng hoàn tất cắt ảnh hoặc nén ảnh (`uploaded`).
  5. Người dùng đổi ngân hàng/STK VietQR (`bank`).
- **Hành vi trong Iframe**:
  - Tìm phần tử hiển thị tương ứng trong thiệp cưới qua `data-sync-target` hoặc class BEM.
  - Tự động mở vùng chứa bị ẩn nếu cần (ví dụ hộp quà cưới).
  - Cuộn mượt phần tử ra giữa màn hình (`scrollIntoView({ behavior: 'smooth', block: 'center' })`).
  - Gắn class `.mayhen_editor_target_highlight` tạo hiệu ứng vàng kim Quiet Luxury (`#C5A880`) phát sáng tinh tế trong ~1.8s.

---

## 4. CHI TIẾT ÁNH XẠ MỤC TIÊU (SMART TARGET MAPPING MATRIX)

| Trường Trong Form Editor | Key Dữ Liệu | Phần Tử Đích Trong Iframe Thiệp Cưới |
| :--- | :--- | :--- |
| **Họ tên Chú Rể** | `wedding.groom_name` | `[data-sync-target="groom_name"]`, `.mayhen_invitation_hero_groom`, `[data-sync-target="couple_names"]` |
| **Họ tên Cô Dâu** | `wedding.bride_name` | `[data-sync-target="bride_name"]`, `.mayhen_invitation_hero_bride`, `[data-sync-target="couple_names"]` |
| **Ảnh Bìa / Đại Diện** | `wedding.cover_photo_url` | `[data-sync-target="hero_photo_url"]`, `[data-sync-target="cover_photo_url"]`, `.mayhen_invitation_hero_img` |
| **Ảnh Kỷ Niệm Lời Ngỏ** | `wedding.extra_data.save_date_photo_url` | `[data-sync-target="save_date_photo_url"]`, `.mayhen_invitation_story_img` |
| **Ảnh Chân Dung Chú Rể** | `wedding.extra_data.groom_photo_url` | `[data-sync-target="groom_photo_url"]`, `[data-ben="trai"] img`, `.mayhen_invitation_chan_dung` |
| **Ảnh Chân Dung Cô Dâu** | `wedding.extra_data.bride_photo_url` | `[data-sync-target="bride_photo_url"]`, `[data-ben="phai"] img`, `.mayhen_invitation_chan_dung` |
| **Song Thân Nhà Trai** | `wedding.extra_data.groom_father/mother` | `[data-sync-target="groom_father"]`, `[data-sync-target="groom_parents"]`, `[data-ben="trai"]` |
| **Song Thân Nhà Gái** | `wedding.extra_data.bride_father/mother` | `[data-sync-target="bride_father"]`, `[data-sync-target="bride_parents"]`, `[data-ben="phai"]` |
| **Ngày Cưới / Lịch Cưới** | `wedding.wedding_date` | `[data-sync-target="wedding_calendar"]`, `[data-sync-target="wedding_date"]`, `[data-mayhen-section="countdown_calendar"]` |
| **Thời Gian & Tiệc Cưới** | `wedding.extra_data.party_venue/address/time` | `[data-sync-target="party_venue"]`, `[data-sync-target="party_address"]`, `[data-mayhen-section="events"]` |
| **Mốc Lịch Trình (0..N)** | `wedding.extra_data.timeline.{i}.*` | `[data-sync-target="timeline.{i}.time"]`, `[data-sync-target="timeline.{i}.title"]`, `.mayhen_invitation_timeline_item:nth-child({i+1})` |
| **Quy Định Trang Phục** | `wedding.extra_data.dresscode_*` | `[data-sync-target*="dresscode"]`, `[data-mayhen-section="dresscode"]` |
| **Album 10 Ảnh Cưới (0..9)** | `wedding.extra_data.gallery.{0..9}` | `[data-sync-target="gallery.{i}"]`, `.mayhen_invitation_gallery_col:nth-child({i+1})` |
| **VietQR Chú Rể / STK** | `wedding.extra_data.qr_groom_image` | `[data-sync-target="qr_groom_image"]`, `[data-sync-target*="groom_bank"]` (Tự động mở hộp quà) |
| **VietQR Cô Dâu / STK** | `wedding.extra_data.qr_bride_image` | `[data-sync-target="qr_bride_image"]`, `[data-sync-target*="bride_bank"]` (Tự động mở hộp quà) |
| **Lời Cảm Ơn & RSVP** | `wedding.extra_data.thank_you_title/msg` | `[data-sync-target="thank_you_title"]`, `[data-mayhen-section="ending"]` |

---

## 5. HIỆU ỨNG TARGET HIGHLIGHT (QUIET LUXURY GOLD PULSE)

Khi phần tử được cuộn tới, hiệu ứng phát sáng nhẹ nhàng được thực thi qua keyframes CSS:

```css
@keyframes mayhenTargetPulse {
    0% {
        outline: 3px solid rgba(197, 168, 128, 0.95);
        outline-offset: 4px;
        box-shadow: 0 0 0 6px rgba(197, 168, 128, 0.35), 0 10px 30px rgba(197, 168, 128, 0.3);
        transform: scale(1.02);
    }
    40% {
        outline: 3px solid rgba(197, 168, 128, 0.8);
        outline-offset: 3px;
        box-shadow: 0 0 0 10px rgba(197, 168, 128, 0.15), 0 8px 24px rgba(197, 168, 128, 0.2);
    }
    100% {
        outline: 2px solid transparent;
        outline-offset: 0;
        box-shadow: none;
        transform: scale(1);
    }
}

.mayhen_editor_target_highlight {
    animation: mayhenTargetPulse 1.8s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
    position: relative;
    z-index: 10;
    transition: transform 0.3s ease, outline 0.3s ease, box-shadow 0.3s ease;
}
```

---

## 6. QUẢN LÝ ẢNH & CANVASCROPPER (CLIENT-SIDE ONLY)

1. **Không Tải Lên Máy Chủ Khi Chưa Xuất Bản**:
   - Khi chỉnh sửa trong Studio Editor, ảnh được nén hoặc cắt trực tiếp trên trình duyệt thành chuỗi `data:image/jpeg` hoặc `data:image/png` (WebP fallback).
   - Tốc độ xem trước tức thì 0ms, không tốn băng thông máy chủ.
2. **Chuẩn Khung Hình 3:4 Cho Ảnh Bìa**:
   - Ảnh bìa bắt buộc kiểm tra tỷ lệ 3/4. Nếu sai tỷ lệ, tự động kích hoạt Canvas Cropper Modal để người dùng căn chỉnh góc đẹp nhất.
3. **Mã VietQR Tự Động Tạo Cục Bộ**:
   - Tải SVG từ VietQR API, vẽ lên HTML Canvas và xuất ra `data:image/png` để nhúng thẳng vào thiệp, hoàn toàn không phụ thuộc CDN bên ngoài khi lưu trữ.

---

## 7. DEFINITION OF DONE (DOD) CHO TÁC VỤ EDITOR

Một tính năng hoặc sửa lỗi trên Editor chỉ được duyệt khi thỏa mãn:
1. **10 Section Navigation**: Chuyển 10 tab trên Stepper cuộn chính xác 100% đến 10 section tương ứng trong preview iframe.
2. **Smart Scroll-on-Action**: Bất kỳ hành động focus, gõ chữ, chọn ảnh, tải ảnh hay chọn ngân hàng đều tự động cuộn đến phần tử đích và phát sáng highlight vàng kim.
3. **Vietnamese Typing Intact**: Gõ tiếng Việt bằng Unikey/EVKey trên mọi trường không bị nhảy con trỏ, không mất dấu.
4. **Zero External Libs**: 100% Pure Vanilla JS ES Modules & Native CSS.
5. **Pest Tests Passed**: `vendor/bin/pest tests/Feature` pass 100%.
6. **Pint Formatted**: `vendor/bin/pint --dirty --format agent` chuẩn PSR-12.
7. **Context Documented**: Cập nhật chi tiết vào `context.gemini.md`.
