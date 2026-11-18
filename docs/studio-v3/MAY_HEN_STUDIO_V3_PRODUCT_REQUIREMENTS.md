# MÂY HẸN — ĐẶC TẢ SẢN PHẨM & LUỒNG TƯƠNG TÁC STUDIO VISUAL EDITOR V3

> **Phiên bản**: 1.0 (Wave 01)  
> **Triết lý cốt lõi**: *Hybrid Flow + Overlay Visual Editor* — Giữ nguyên 100% bố cục tự co giãn (`Flow Layout`) và vẻ đẹp nghệ thuật của từng mẫu thiệp Mây Hẹn, đồng thời cho phép người dùng chạm trực tiếp vào chữ/ảnh trên bản xem trước để chỉnh sửa và thêm lớp trang trí tự do (`Overlay Layer`) có kiểm soát vùng an toàn.

---

## 1. Mô Hình Ba Lớp Phần Tử Trong Studio V3

1. **Lớp 1 — Phần tử chuẩn của mẫu thiệp (`Canonical Template Elements`)**:
   - Mọi phần tử gắn `data-sync-target` (hoặc widget nghiệp vụ chuẩn): tên cô dâu chú rể, ngày cưới, lời ngỏ, gia đình hai bên, thời gian/địa điểm sự kiện, lịch trình, ảnh bìa, ảnh chuyện tình, 10 ảnh album, thông tin mừng cưới, lời cảm ơn.
   - **Quyền thao tác**: Chọn trực tiếp bằng 1 chạm; sửa nội dung chữ inline (2 chạm / `Enter` / nút `Sửa chữ`); thay/cắt/căn tiêu cự ảnh; tùy biến kiểu chữ/màu/cỡ/căn lề theo phạm vi (`instance_id`, trường dữ liệu, hoặc toàn thiệp); chọn hiệu ứng chuyển động; đặt lại về mặc định mẫu. **Không kéo lệch khỏi dòng chảy bố cục** để bảo toàn responsive điện thoại.
2. **Lớp 2 — Phần tử trang trí tự do (`Free Decoration / Overlay Elements`)**:
   - Gồm 6 loại: `overlay_text`, `overlay_sticker` (SVG nội bộ đổi màu được), `overlay_shape`, `overlay_frame` (khung ảnh nghệ thuật kèm cắt ảnh), `overlay_divider`, `overlay_image`.
   - Neo theo từng section (`section_id`), định vị bằng tọa độ chuẩn hóa `x, y, width, height` (`0..1` theo khung section), hỗ trợ kéo, phóng to/thu nhỏ giữ tỷ lệ góc, xoay có bắt góc `0°/15°/45°/90°`, gióng hàng giữa/mép, sắp lớp `z_index`, nhân bản, khóa, ẩn/hiện, xóa và chọn hiệu ứng chuyển động.
3. **Lớp 3 — Phần tử hệ thống bảo vệ (`Protected System & Business Widgets`)**:
   - Gồm: Đếm ngược (`Countdown`), Lịch tháng (`Calendar`), Cụm tên ghép (`couple_names`), Mã `VietQR`, Bản đồ chỉ đường (`Google Maps`), Nút nhạc nền, Form `RSVP`, Sổ lưu bút `Wishes`, Sổ ký tên `Signature Canvas`.
   - **Ở chế độ Chỉnh sửa (`mode = editor`)**: Chạm vào không kích hoạt gửi form giả hay nhảy trang ra ngoài, mà mở đúng bảng điều khiển nghiệp vụ tương ứng ở thanh Inspector bên phải (ví dụ chạm lịch/đếm ngược -> mở chọn `Ngày cưới`; chạm QR -> mở `Ngân hàng & VietQR`; chạm bản đồ -> mở `Địa chỉ & Link Google Maps`).
   - **Ở chế độ Xem như khách (`mode = guest_preview`)**: Tắt toàn bộ khung chọn/handle để người dùng trải nghiệm y hệt khách mời thật.

---

## 2. Hợp Đồng Tương Tác Chuột, Cảm Ứng, Bàn Phím & Bộ Gõ Tiếng Việt (IME)

| Ngữ cảnh | Thao tác | Hành vi bắt buộc |
|---|---|---|
| **Canvas (Chế độ chọn)** | Click đơn / Chạm đơn vào phần tử | Chọn phần tử, hiển thị viền chọn mảnh màu vàng kim/than chì (`Quiet Luxury`), hiện `Floating Quick Toolbar` nằm gọn trong khung nhìn và đồng bộ `Right Contextual Inspector`. |
| **Canvas (Văn bản)** | Double-click / Chạm nút `Sửa chữ` / Nhấn `Enter` khi đang chọn chữ | Chuyển phần tử sang trạng thái sửa chữ trực tiếp (`inline editing`), đặt con trỏ cuối dòng hoặc tại vị trí chạm, khóa kéo-thả. |
| **Sửa chữ Tiếng Việt (IME)** | Gõ Telex / VNI (Unikey, EVKey, Gboard, iOS) | Theo dõi `compositionstart` -> `compositionupdate` -> `compositionend`. Trong lúc `_isComposing = true`, **tuyệt đối không** ghi đè `textContent` ngược từ store xuống phần tử đang gõ và không cắt chuỗi giữa chừng để không bao giờ mất dấu `ấ, ậ, ữ, đ` hay nhảy con trỏ về đầu dòng. |
| **Dán văn bản (`Paste`)** | `Ctrl/Cmd + V` trong lúc sửa chữ inline | Chặn HTML/style rác từ Word/Zalo/Web; chỉ lấy `clipboardData.getData('text/plain')`, chuẩn hóa xuống dòng theo loại trường (đơn dòng thay `\n` bằng khoảng trắng; đa dòng giữ `\n`). |
| **Phím tắt khi đang sửa chữ** | `Enter` / `Shift+Enter` / `Escape` | Trường đơn dòng (`groom_name`, `bride_name`, `party_time`...): `Enter` chốt nội dung và thoát sửa chữ. Trường đa dòng (`story_content`, `couple_quote`, `overlay_text`...): `Enter` xuống dòng, `Escape` hoặc click ra ngoài chốt nội dung. `Backspace`/`Delete` chỉ xóa ký tự chữ, **không bao giờ xóa phần tử**. |
| **Phím tắt khi đang chọn phần tử (không gõ chữ)** | `Ctrl/Cmd+Z`, `Ctrl/Cmd+Shift+Z`/`Y`, `Escape`, `Delete`/`Backspace`, Mũi tên | `Undo` / `Redo` chuẩn xác; `Escape` bỏ chọn; `Delete`/`Backspace`: với `overlay_*` thì xóa phần tử (có thể `Undo`), với phần tử gốc của mẫu thì hiện thông báo nhẹ *"Đây là thành phần gốc của mẫu thiệp — bạn có thể ẩn mục hoặc sửa nội dung"*; phím mũi tên dịch chuyển `overlay_*` `1px` (`Shift + Mũi tên` = `10px`). |
| **Vùng an toàn (`Safe Zone`)** | Kéo `overlay_*` đè lên nút RSVP, Gửi lời chúc, Canvas ký tên, QR, Nút bản đồ | Hiện cảnh báo viền hổ phách *"Phần tử trang trí đang che vùng tương tác của khách mời"* kèm nút `Đưa ra vùng an toàn` và trên bản công khai tự động hạ ưu tiên chạm nếu lấn vùng nút bấm chính. |

---

## 3. Ánh Xạ Luồng Đầu-Cuối (`Data Source -> Selection -> Store -> API -> DB -> Preview -> Public -> Test`)

| Mã luồng | Phần tử & Thao tác | Nguồn & Đường dẫn `EditorStore` | API Lưu & Cột Database | Bản Preview (`Studio`) | Bản Xuất Bản (`/thiep/{slug}`) | Mã Test Kiểm Chứng |
|---|---|---|---|---|---|---|
| `FLOW-V3-01` | Click & sửa inline tên Chú rể (`groom_name`) | `wedding.groom_name` | `POST /api/editor/autosave` (`wedding_delta.groom_name`) + `POST /tao-thiep` -> `weddings.groom_name` | Đồng bộ tức thì mọi vị trí `groom_name` & `couple_names` + ô input form trái | Hiển thị chữ đã lưu, thoát ký tự chống XSS | `STV3-TEXT-001..016`, `STV3-SYNC-001` |
| `FLOW-V3-02` | Đổi font/cỡ/màu riêng 1 tiêu đề (`instance_id`) | `config.visual_editor.instance_styles[instance_id]` | `autosave` -> `invitations.draft_config.visual_editor`; `publish` -> `invitations.custom_config.visual_editor` | Áp CSS biến/inline đã lọc trên đúng `[data-edit-id]` được chọn, các vị trí khác giữ nguyên | Áp từ `custom_config.visual_editor.instance_styles` qua `VisualEditorSanitizerService` | `STV3-FONT-001, 004, 007..017` |
| `FLOW-V3-03` | Đổi bộ Font toàn thiệp (`Tiêu đề / Nội dung / Chữ ký`) | `config.visual_editor.global_style.{heading_font_id,body_font_id,accent_font_id}` | `autosave` -> `draft_config`; `publish` -> `custom_config` | Cập nhật biến CSS font gốc trên `.mayhen_invitation_root` & `:root` bằng `@font-face` nội bộ | Nạp `invitation-fonts-v3.css` cục bộ (0 Google CDN), giữ nguyên override riêng nếu có | `STV3-FONT-002..006, 018..020` |
| `FLOW-V3-04` | Click ảnh bìa / ảnh album -> Thay ảnh, cắt theo khung & chỉnh tiêu cự/zoom | `wedding.cover_photo_url` / `wedding.extra_data.gallery[i]` + `config.visual_editor.image_overrides[instance_id]` | `POST /tao-thiep/upload-asset` -> `/storage/weddings/photos/*.webp` + `autosave` | Cập nhật `img[src]` hoặc `background-image`, áp `object-position`/`background-position` & `scale` không phá ảnh gốc | Hiển thị ảnh thật từ `/storage/...` (0 `blob:`) với đúng tiêu cự & tỷ lệ khung | `STV3-IMAGE-001..024` |
| `FLOW-V3-05` | Thêm Văn bản / Sticker SVG / Hình dạng / Khung ảnh trang trí (`overlay_*`) | `config.visual_editor.overlays[]` | `autosave` -> `draft_config.visual_editor.overlays`; `publish` -> `custom_config.visual_editor.overlays` | Render trong `.mayhen_invitation_overlay_layer` của `[data-mayhen-section]`, có khung kéo/co giãn/xoay | Render tĩnh sạch sẽ (không handle, không JS editor), đúng tọa độ `%` trên mobile & desktop | `STV3-ADD-001..014`, `STV3-TRANSFORM-001..022` |
| `FLOW-V3-06` | Chọn hiệu ứng chuyển động (`Motion`) cho chữ/ảnh/sticker & bấm `Xem thử` | `config.visual_editor.instance_animations[instance_id]` hoặc `overlay.motion` | `autosave` -> `draft_config`; `publish` -> `custom_config` | Phát lại hiệu ứng tức thì trên đúng 1 phần tử được chọn trong iframe | Chạy bằng `IntersectionObserver` / `on_load` / `after_open` nhẹ, tôn trọng `prefers-reduced-motion` | `STV3-MOTION-001..016` |
| `FLOW-V3-07` | Chuyển mẫu thiệp (10-section <-> 10-section hoặc sang 7-section Curated) | Giữ nguyên `wedding.*`, `global_style`, `overlays`; phần tử thuộc section không tồn tại chuyển vào khay `unplaced_overlays` | `autosave` / `POST /tao-thiep` | Nạp preview mẫu mới với đầy đủ dữ liệu cưới; hiện khay *"Phần tử chưa gắn mục"* nếu mẫu mới ẩn section | Xuất bản đúng mẫu mới, không rơi mất dữ liệu cưới hay làm vỡ giao diện | `STV3-TEMPLATES-004..005`, `FLOW-V3-14..15` |
