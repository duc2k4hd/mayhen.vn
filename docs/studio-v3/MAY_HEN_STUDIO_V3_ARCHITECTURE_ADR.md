# MÂY HẸN — TÀI LIỆU QUYẾT ĐỊNH KIẾN TRÚC STUDIO VISUAL EDITOR V3 (ADR)

> **Phiên bản**: 1.0 (Wave 01)  
> **Trạng thái**: Đã phê duyệt triển khai  
> **Ràng buộc bất biến**: Laravel 13.35.0 + PHP 8.5 + 100% Pure HTML5 / Vanilla CSS (`mayhen_editor_*`, `mayhen_invitation_*`) / Native ES Modules; **0 thư viện ngoài**, **0 CDN bên ngoài**, **0 truy vấn SQL** trên `GET /tao-thiep` khi cache ấm cho khách vãng lai.

---

## ADR-01: Cơ Chế Lớp Trang Trí (`Overlay Layer`) Không Dùng Layout Kéo-Thả Tuyệt Đối Kiểu LadiPage

### Bối cảnh
Quy chuẩn dự án (`AGENTS.md` Nguyên tắc #3 & `.agents/skills/mayhen-invitation-standards/SKILL.md`) nghiêm cấm biến 10 Section chuẩn của mẫu thiệp thành canvas `position: absolute` cố định tọa độ pixel kiểu LadiPage vì sẽ phá hỏng khả năng tự giãn dòng (`Flow Layout`) trên hàng chục kích thước điện thoại. Tuy nhiên, người dùng vẫn cần đặt thêm chữ trang trí, sticker sáp niêm phong, khung ảnh và xoay/kéo tự do trên từng mục.

### So sánh phương án
1. **Phương án A — Chuyển toàn bộ section sang canvas tọa độ tuyệt đối (`Bị loại`)**: Phá vỡ 100% CSS gốc của 7 mẫu thiệp và gây tràn chữ trên màn hình nhỏ (`320px–390px`).
2. **Phương án B — CSS Grid Stacking (`grid-area: 1 / 1`) kết hợp khung neo phần trăm theo từng Section (`Được chọn`)**:
   - Giữ nguyên 100% dòng chảy nội dung gốc của `<section data-mayhen-section="...">`.
   - Khi một section có phần tử `overlay_*` (hoặc đang ở chế độ Studio Editor), chèn một lớp phủ `.mayhen_invitation_overlay_layer` nằm chồng lên section mà không chiếm chiều cao dòng chảy (`pointer-events: none` trên lớp chứa, `pointer-events: auto` trên từng vật thể `.mayhen_invitation_overlay_item`).
   - Vị trí và kích thước vật thể lưu dưới dạng **tọa độ chuẩn hóa `0..1` theo chiều rộng và chiều cao thực của section** (`x`, `y`, `w`, `h`), hiển thị bằng biến CSS `--mh-ov-x`, `--mh-ov-y`, `--mh-ov-w` kết hợp `transform: translate3d(...) rotate(...)` trên thẻ bao ngoài.

---

## ADR-02: Tách Biệt Vỏ Bố Cục (`Outer Transform Wrapper`) Và Vỏ Chuyển Động (`Inner Motion Wrapper`)

### Vấn đề
Nếu gắn cả `transform: translate3d(...) rotate(15deg)` của thao tác kéo/xoay và `@keyframes` hiệu ứng chuyển động (`transform: translateY(20px) scale(0.96)`) lên cùng một thẻ DOM, thuộc tính `transform` trong keyframe sẽ ghi đè góc xoay/tọa độ của người dùng (`STV3-MOTION-003`, `STV3-MOTION-014`).

### Quyết định
- **Đối với `overlay_*`**:
  - Thẻ ngoài `.mayhen_invitation_overlay_item`: chịu trách nhiệm vị trí, kích thước, góc xoay, `z-index` và khung chọn (`transform: rotate(var(--mh-ov-rot, 0deg))`).
  - Thẻ trong `.mayhen_invitation_overlay_inner`: chịu trách nhiệm hiệu ứng chuyển động (`data-v3-motion="..."`, `opacity` + `transform` GPU-only).
- **Đối với phần tử chuẩn của mẫu (`[data-sync-target]`)**:
  - Giữ nguyên cấu trúc thẻ gốc để không phá selector CSS của mẫu; gắn thuộc tính `data-v3-motion` sử dụng các keyframe GPU-only (`opacity`, `translate3d`, `scale`, `clip-path`) chỉ kích hoạt khi có cấu hình trong `instance_animations`, đồng thời vô hiệu hóa xung đột với reveal mặc định của mẫu trên riêng phần tử đó.

---

## ADR-03: Cấu Trúc Dữ Liệu `visual_editor` Phiên Bản 1 & Cơ Chế Gộp Sâu (`Deep Patch Merge`)

### Cấu trúc lưu trong `invitations.draft_config['visual_editor']` và `invitations.custom_config['visual_editor']`

```json
{
  "version": 1,
  "global_style": {
    "heading_font_id": "cormorant-vn",
    "body_font_id": "be-vietnam-sans",
    "accent_font_id": "indulge-script"
  },
  "instance_styles": {
    "hero__groom_name__0": {
      "scope": "instance",
      "font_id": "indulge-script",
      "font_size_px": 44,
      "font_weight": 400,
      "font_style": "normal",
      "line_height": 1.2,
      "letter_spacing_em": 0.02,
      "text_align": "center",
      "color": "#5A1822",
      "opacity": 100
    }
  },
  "image_overrides": {
    "hero__cover_photo_url__0": {
      "focal_x": 50,
      "focal_y": 32,
      "zoom": 1.12,
      "aspect_ratio": "3:4",
      "opacity": 100
    }
  },
  "instance_animations": {
    "hero__groom_name__0": {
      "preset": "soft-rise",
      "duration_ms": 800,
      "delay_ms": 120,
      "easing": "cubic-bezier(0.22, 1, 0.36, 1)",
      "trigger": "on_load",
      "repeat": "once"
    }
  },
  "overlays": [
    {
      "id": "ov_9f4b2c1a",
      "type": "overlay_sticker",
      "section_id": "hero",
      "asset_id": "wax-seal-champagne",
      "text": "",
      "image_url": "",
      "x": 0.76,
      "y": 0.12,
      "w": 0.16,
      "h": 0.12,
      "desktop_override": null,
      "rotation_deg": -8,
      "z_index": 12,
      "opacity": 95,
      "locked": false,
      "hidden": false,
      "style": {
        "color": "#C5A059",
        "fill": "#8B1E2D"
      },
      "crop": null,
      "motion": {
        "preset": "gentle-zoom",
        "duration_ms": 700,
        "delay_ms": 200,
        "easing": "cubic-bezier(0.22, 1, 0.36, 1)",
        "trigger": "viewport",
        "repeat": "once"
      }
    }
  ]
}
```

### Quy tắc cách ly Nháp (`draft_config`) và Công khai (`custom_config`)
1. **Khi tự động lưu (`POST /api/editor/autosave`)**:
   - `VisualEditorSanitizerService::sanitizeAndMerge()` làm sạch toàn bộ đầu vào (loại bỏ thẻ HTML/script, chặn `blob:`/`javascript:`, kẹp biên số học, đối chiếu `font_id`/`asset_id`/`preset` với allowlist) và ghi vào `invitations.draft_config['visual_editor']` (hỗ trợ giá trị `null` hoặc cờ xóa để khi người dùng bấm *"Đặt lại mặc định"* hoặc xóa sticker, khóa cũ thực sự biến mất thay vì bị `array_merge` nông phục hồi lại).
   - Giữ nguyên tăng tuần tự `revision_id = current + 1` để tương thích 100% với `Autosaver` hiện hữu.
2. **Khi tải lại Studio (`F5` trên `/tao-thiep?invitation_id=...` và iframe `/xem-thu/{slug}?invitation_id=...&mode=editor`)**:
   - Chỉ khi người dùng là chủ sở hữu hợp lệ (`$invitation->isOwner()` hoặc Admin), bản xem trước gộp `draft_config` đè lên `custom_config` để khôi phục 100% trạng thái đang thiết kế dở.
3. **Khi xem thiệp công khai (`GET /thiep/{slug}`)**:
   - Chỉ đọc `invitations.custom_config` đã xuất bản; tuyệt đối không đọc `draft_config` chưa xuất bản và **không bao giờ nạp `editor-bridge.blade.php`** trên route công khai dù có thêm `?embed=1` trên URL.
4. **Khi bấm Xuất bản (`POST /tao-thiep` hoặc `PublishDiffController@publish`)**:
   - Thăng cấp `draft_config['visual_editor']` đã làm sạch sang `custom_config['visual_editor']`, tạo bản ghi lịch sử phiên bản (`InvitationRevision`) và xóa cache thiệp công khai.

---

## ADR-04: Giao Thức Cầu Nối Iframe V3 (`postMessage` Bảo Mật Một Kênh Duy Nhất)

1. **Loại bỏ gọi kép**: Sửa `dispatchToIframes()` trong `editor.js` để chỉ gửi qua **một kênh `postMessage` chuẩn hóa duy nhất** với `targetOrigin = window.location.origin` (không dùng `'*'` và không gọi chồng chéo hàm trực tiếp gây cập nhật đúp).
2. **Xác thực hai chiều**:
   - Parent (`editor.js`) kiểm tra `event.origin === window.location.origin` VÀ `event.source` thuộc một trong hai iframe preview hợp lệ (`#previewIframe`, `#mobilePreviewIframe`).
   - Child (`editor-bridge.blade.php`) kiểm tra `event.origin === window.location.origin` VÀ `event.source === window.parent`.
   - Mọi thông điệp V3 đều mang `protocol: 'MAYHEN_STUDIO_V3'`, `version: 3`, `request_id` chống xử lý trùng.
3. **Định danh phần tử ổn định (`data-edit-id` / `instance_id`)**:
   - Cầu nối gán tự động định danh tất định `{canonical_section}__{sync_key}__{occurrence_index}` (ví dụ: `hero__groom_name__0`, `parents__groom_name__0`, `parents__groom_name__1`, `album__gallery__3`). Thuật toán hoàn toàn tất định nên cả phía trình duyệt trong Studio lẫn bộ dựng công khai (`invitation-config.js`) đều ánh xạ trúng đích 100% mà không phải sửa tay hàng trăm dòng HTML trong từng mẫu thiệp.

---

## ADR-05: Kho Font Tiếng Việt Cục Bộ (`InvitationFontRegistryService`) & Bảo Toàn 0 SQL Query

- Đóng gói bộ font `.woff2` tiếng Việt có sẵn trong kho nguồn vào `public/assets/client/fonts/studio-v3/` và tệp CSS tĩnh `public/assets/client/css/common/invitation-fonts-v3.css` (`font-display: swap`).
- Toàn bộ danh mục Font (`InvitationFontRegistryService`), danh mục Sticker/Shape/Frame (`StudioDecorationLibraryService`) và danh mục Hiệu ứng (`MotionPresets`) là **hằng số cấu hình PHP/JS tĩnh trong bộ nhớ**, đảm bảo `GET /tao-thiep` của khách vãng lai tiếp tục giữ vững **0 truy vấn SQL** khi cache ấm (`InvitationEditorPerformanceTest`).
- Khi áp `global_style` trên thiệp, đồng bộ đồng thời cả biến chung (`--font-heading`, `--font-serif`, `--font-body`, `--font-sans`, `--font-script`) lẫn biến nội bộ của từng mẫu (`--f-script`, `--f-than`, `--mayhen-font-serif`, `--mayhen-invitation-heading-font`, `--mh-f-script`, `--mh-f-phi`...) để **cả 7 mẫu thiệp** đều đổi font toàn cục mượt mà.
