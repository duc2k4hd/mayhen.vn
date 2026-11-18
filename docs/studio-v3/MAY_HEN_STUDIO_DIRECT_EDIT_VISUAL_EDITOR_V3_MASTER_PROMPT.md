# MÂY HẸN — MASTER IMPLEMENTATION PROMPT
## STUDIO VISUAL EDITOR V3 · CHỈNH TRỰC TIẾP TRÊN THIỆP · CANVAS TRANG TRÍ AN TOÀN · TYPOGRAPHY & MOTION

> **Trạng thái tài liệu:** Đặc tả/đề bài triển khai dành cho coding agent. KHÔNG PHẢI tuyên bố tính năng đã tồn tại hoặc đã vượt qua kiểm thử.
>
> **Nguồn khảo sát:** 8 ảnh giao diện editor đối chiếu do chủ dự án cung cấp (đặt tại `docs/studio-v3/references/`), bản mã nguồn Google Drive có metadata đồng bộ khoảng 10/11/2026, và bộ quy tắc `AGENTS.md`, `.agents/skills/mayhen-visual-editor/SKILL.md`, `.agents/skills/mayhen-invitation-standards/SKILL.md`. Vì snapshot Drive có thể cũ hơn workspace ngày 18/11/2026, mọi quyết định cuối phải đối chiếu trực tiếp SOURCE MỚI NHẤT đang mở trên máy phát triển.
>
> **Mục tiêu sản phẩm:** Studio Mây Hẹn cho phép người dùng bấm trực tiếp vào **bất kỳ thành phần nào mà hệ thống đang cho phép khách sửa** trên bản xem trước; từ đó sửa chữ/ảnh/dữ liệu, thay kiểu chữ, màu sắc và hiệu ứng; thêm chữ, sticker, hình dạng, khung trang trí và di chuyển tự do trong phạm vi an toàn của thiệp. Tất cả hoạt động nhất quán trên bản nháp, bản xuất bản, desktop và mobile, không phá thiết kế gốc, dữ liệu cưới hay nghiệp vụ.

---

# PHẦN 00 — VAI TRÒ VÀ LUẬT BẤT BIẾN

Bạn là Principal Laravel Engineer, Staff Frontend Engineer chuyên về native DOM visual editors, Product Interaction Designer, Security/Privacy Engineer, Accessibility QA và Business Logic Auditor. Hãy **lập kế hoạch dựa trên code thật, sau đó thực thi từng giai đoạn, sửa lỗi và test hồi quy trong cùng phiên làm việc khi có thể**. Không dừng ở danh sách ý tưởng. Khi hết phiên, lưu checkpoint rõ ràng để tiếp tục.

Các điều kiện không được thương lượng:

1. **KHÔNG tạo editor độc lập mới.** Mở rộng `client.pages.editor` và các service/bridge/JS của Mây Hẹn, giữ nguyên API, source of truth, quyền sở hữu, kế hoạch thanh toán và luồng tạo–lưu–xuất bản.
2. **KHÔNG mô phỏng sai mẫu đối thủ.** 8 ảnh chỉ là tham chiếu về thao tác (chọn phần tử, inspector, thêm asset, animation). Thiết kế UI/UX thương hiệu Mây Hẹn phải nguyên bản, Quiet Luxury, cream/ivory/champagne, gọn gàng và không sao chép thương hiệu/giao diện của Miwedi.
3. **GIỮ MẪU THIỆP GỐC.** Mỗi mẫu tiếp tục render chính `template.blade.php`, `styles.css`, `script.js`, `schema.json`, assets gốc của mẫu trong `resources/views/client/invitations/{slug}`. Không viết một mẫu thay thế và không làm hỏng/đổi CSS nghệ thuật gốc để phục vụ editor.
4. **Giữ các profile khác nhau:** thiệp thường theo hợp đồng 10 section; thiệp AI Curated nếu còn dùng 7 section phải là profile riêng, không lén hiển thị thêm 3 section và không áp quy tắc 10 section sai chỗ. Kiểm tra cách profile hoạt động trên source MỚI NHẤT rồi mới quyết định.
5. **Phân tách DATA / PRESENTATION / FREE DECORATION / MOTION.** Họ tên, thời gian, địa điểm, ngân hàng, URL ảnh của đôi uyên ương, RSVP… giữ nguồn dữ liệu chuẩn. Kiểu chữ/màu/căn lề của phần tử là style overrides. Sticker, chữ thêm, hình trang trí là overlay objects. Animation là metadata trên object hoặc target, không tạo một source of truth khác.
6. **Bộ quy tắc nền:** `AGENTS.md`, `CLAUDE.md`, `.ai/rules/` nếu có, `context.gemini.md`, `mayhen-visual-editor`, `mayhen-invitation-standards`, `mayhen-animation`, `mayhen-javascript`, `mayhen-css`, `mayhen-ui-ux`, `mayhen-auth-security`, `mayhen-testing-quality`, `mayhen-database`, `mayhen-laravel-backend`. Đọc skill thật; không chỉ đọc tên.
7. **Thuần native:** Laravel/Blade, HTML/CSS/JS thuần ES Modules; không React, Vue, Fabric/Konva, GSAP, jQuery, Tailwind, CropperJS, Interact, animation kit, canvas framework, external CDN hay các gói phụ thuộc mới khi chưa được người dùng phê duyệt.
8. **Nguyên tắc thiết kế gốc Flow Layout:** không chuyển tất cả 10 section thành web canvas `position:absolute` tự do. Nếu thêm lớp kéo thả, nó phải là một **lớp trang trí riêng** được cắm vào từng section/host, không phá flow chính. Ưu tiên CSS Grid stacking + `transform:translate3d(...)` cho overlay; nếu kỹ thuật thực tế buộc phải có positioned layer, tài liệu hóa ngoại lệ hẹp, xin duyệt thay đổi quy chuẩn thay vì lách luật.
9. Mọi CSS mới namespace `mayhen_editor_*` (Studio), `mayhen_invitation_*` (thiệp); không dùng selector toàn cục làm ảnh hưởng template hoặc Admin.
10. Assets/font/sticker SVG **tự lưu nội bộ**, có giấy phép sử dụng thương mại phù hợp; không tự động nhúng URL ảnh/logo của trang tham chiếu.
11. Mọi action có dữ liệu phải thực sự persist; nút “Đã lưu” chỉ hiển thị sau ACK hợp lệ của phiên bản mới nhất. Không xây UI giả, tính năng giả, demo dùng dữ liệu giả trong production.
12. Giữ quyền riêng tư và trạng thái: draft, published, locked, expired, owner, guest, permission/plan entitlement. Editor không làm lộ data unpublished trong iframe hoặc API.
13. Không tự commit, push, deploy, chạy trên production, xoá DB hay làm thay đổi tài chính thật. Cập nhật `context.gemini.md` sau khi thay đổi source theo đúng quy tắc của dự án; viết sẵn commit tiếng Việt có dấu.
14. **“Bấm vào được” không có nghĩa tất cả chữ tĩnh đều được sửa.** Thành phần có hợp đồng editable thì sửa trực tiếp; thành phần tính toán, navigation, tên trường cố định, CTA tương tác, đồng hồ, bản đồ, RSVP, VietQR… phải mở đúng inspector và input nghiệp vụ tương ứng, hoặc hiển thị giải thích không sửa tự do. Không phá ý nghĩa nghiệp vụ.
15. Không tuyên bố “100%”, “không lỗi”, “giống hệt hình tham khảo” nếu chưa có bằng chứng thao tác thực tế và test cho từng trường hợp.

---

# PHẦN 01 — BẮT BUỘC ĐỌC MÃ NGUỒN TRƯỚC KHI CODE

## 01.1. Định vị repo MỚI NHẤT

- Chạy `git rev-parse --show-toplevel`, `git status --short`, `git log -5 --oneline` tại repo đang mở. Đường dẫn có thể là `D:\Project\Laravel\mayhen.vn` hoặc `E:\Project\Laravel\mayhen.vn`; không tự chọn sai ổ đĩa.
- Đọc `context.gemini.md` **đầu tiên**. Kiểm tra ngày sửa thực và nhánh hiện tại. KHÔNG tin snapshot Google Drive cũ hơn local.
- Kiểm tra `composer.json`, `package.json`, lock files, `config`, migrations, test framework. Không giả định version.
- Kiểm tra toàn bộ routes `routes/web.php`, `routes/admin.php` và `php artisan route:list --except-vendor` nếu môi trường cho phép.
- Đọc các tệp trọng điểm sau (theo path thực tế, không bỏ qua nếu có tên/đường dẫn mới):
  - `resources/views/client/pages/editor.blade.php`
  - `public/assets/client/js/pages/editor.js`
  - `public/assets/client/css/pages/editor.css`, `editor-v2.css`
  - `public/assets/client/js/editor/store.js`, `actions.js`, `autosaver.js`, `draft-recovery.js`, `history.js`, `template-switcher.js`, `dnd-section.js`, `theme-controller.js`
  - `resources/views/client/invitations/partials/editor-bridge.blade.php`
  - `resources/views/client/layouts/invitation.blade.php`
  - `app/Http/Controllers/Client/InvitationEditorController.php`
  - `app/Services/InvitationTemplateService.php`
  - `app/Models/Invitation.php`, `Wedding.php`, `InvitationTemplate.php`
  - `resources/views/client/invitations/{slug}/template.blade.php`, `schema.json`, `styles.css`, `script.js`, assets cho TẤT CẢ mẫu đang active.
  - `database/migrations/*invitations*`, files liên quan draft, media, template versions nếu có.
  - Các test hiện có như `StudioEditorAndAutosaveDeepTest.php`, `InvitationEditorDataSyncTest.php`, `Gate2TemplateStudioAndSyncInvariantsTest.php`, `TemplateRenderAndMediaSecurityDeepTest.php`, các test liên quan AI Curated và template-specific.

## 01.2. Kiểm toán trạng thái trước khi triển khai

Tạo bảng kiểm kê có đường dẫn và dòng code hoặc selector thật:

| ID | Khả năng | Đã có? | Nguồn data | UI hiện có | Storage | Lỗ hổng / cần mở rộng | Bằng chứng |
|---|---|---|---|---|---|---|---|
| ST-001 | Chọn trực tiếp chữ | PRESENT/PARTIAL/MISSING | wedding.*, custom_config.* | iframe | ... | ... | file:line |
| ST-002 | Chọn trực tiếp ảnh | ... | ... | ... | ... | ... | ... |
| ST-003 | Font riêng từng instance | ... | ... | ... | ... | ... | ... |
| ST-004 | Font toàn thiệp | ... | ... | ... | ... | ... | ... |
| ST-005 | Khối chữ tự do | ... | ... | ... | ... | ... | ... |
| ST-006 | Sticker & SVG an toàn | ... | ... | ... | ... | ... | ... |
| ST-007 | Hình cơ bản & khung | ... | ... | ... | ... | ... | ... |
| ST-008 | Drag/resize/rotate | ... | ... | ... | ... | ... | ... |
| ST-009 | Animation từng element | ... | ... | ... | ... | ... | ... |
| ST-010 | Save/reload/publish parity | ... | ... | ... | ... | ... | ... |

Nếu code đã có một phần, **tái sử dụng và sửa đúng chỗ**, không nhân đôi component hoặc store. Tất cả source được trích dẫn chính xác trong tài liệu agent tự tạo.

## 01.3. Dấu vết kiến trúc đã xác định trong snapshot Drive (PHẢI XÁC MINH LẠI)

- Studio có `MAYHEN_BRIDGE_READY`, `MAYHEN_SYNC_INPUT`, `MAYHEN_SCROLL_TO_TARGET`, `MAYHEN_SCROLL_TO_SECTION` và 2 iframe preview.
- `editor.js` quản lý form/input, uploader, cropper, section stepper và sync; chưa đủ bằng chứng có contract chọn/cập nhật trực tiếp phần tử từ iframe ngược về editor.
- `invitations.custom_config` và `draft_config`, `revision_id` đã tồn tại; service có `saveInvitation()` và `saveDraftWithLock()` với optimistic concurrency.
- `AGENTS.md` và `mayhen-visual-editor/SKILL.md` khẳng định native JS, theme template riêng, flow layout, giữ tiếng Việt IME.
- `editor.js` trong snapshot có gửi `postMessage(..., '*')`. Kiểm tra handshake có xác thực `origin`, `source`, requestId không; nếu chưa có cần khóa an toàn trước khi mở rộng nhiều message.
- Endpoint upload asset hiện có; phải kiểm tra lại việc xác thực, ownership, chống spam, mime thực, quota và khả năng dọn file rác trước khi triển khai sticker/ảnh nhiều hơn.
- Source trên Drive mà tôi xem cũ hơn 8 ảnh ngày 18/11; **mọi nhận định trên là baseline để kiểm tra, không phải kết luận tình trạng code hiện tại**.

---

# PHẦN 02 — ĐỌC TỪNG ẢNH THAM CHIẾU ĐÚNG CÁCH

Trong `docs/studio-v3/references/` có 8 ảnh màn hình editor bên ngoài. Chỉ phân tích **pattern tương tác**, không sao chép mã, HTML/CSS, icon, thương hiệu, hình người hoặc ấn phẩm.

| Ảnh | Thao tác cần học | Sản phẩm Mây Hẹn phải có |
|---|---|---|
| `01-text-inspector.png` | Chọn khối chữ ngay trong thiệp, inspector Nội dung/Chữ/Màu/Vị trí, thanh công cụ nổi | Click chữ → selected outline + inspector đúng field; sửa nội dung trực tiếp, đổi font/cỡ chữ/giãn dòng/căn lề/màu |
| `02-image-inspector.png` | Chọn ảnh trực tiếp, preview ảnh và thao tác crop/mở ảnh | Click ảnh → chọn/thay/crop/đổi focal point/opacity; không thay nhầm ảnh khác |
| `03-add-text.png` | Bổ sung khối văn bản vào vị trí trên mẫu | Thêm text overlay mới, chọn font, edit inline, kéo thả, z-index và undo/redo |
| `04-shapes.png` | Thư viện đường kẻ, hình cơ bản, biểu tượng | Tạo shape object, chỉnh màu viền/nền/opacity/kích thước/rotation, hỗ trợ snap |
| `05-frames.png` | Các khung/vòm/cửa sổ chứa ảnh | Thêm frame hoặc áp frame mask cho ảnh; luôn có thao tác chọn ảnh/crop; không để khung rỗng sai final |
| `06-music.png` | Thư viện nhạc, tìm kiếm, phát thử và trạng thái đã chọn | Giữ kho âm nhạc hiện có, không xây lại; kiểm tra lưu/playback, quyền autoplay và browser audio |
| `07-stickers.png` | Thư viện nhãn dán theo chủ đề, SVG đổi màu | Kho sticker cưới chọn lọc, SVG nội bộ an toàn, thêm object, đổi màu nếu cho phép |
| `08-animation.png` | Bảng hiệu ứng theo nhóm, preview trong thumbnail | Tab Animation theo phần tử chọn, preset có thật, xem thử đúng phần tử, cấu hình delay/duration/trigger |

**Tuyệt đối không sao chép:** logo “SQ”, màu đỏ đậm/bố cục thương hiệu Miwedi, tên bài nhạc/hình ảnh ngẫu nhiên, ảnh đô thị lặp lại cho mọi animation; thumbnails phải phản ánh hiệu ứng thật hoặc icon/minipreview Mây Hẹn.

---

# PHẦN 03 — BẢN ĐỒ TÍNH NĂNG, QUYỀN SỬA VÀ HỢP ĐỒNG EDITABLE

## 03.1. Danh mục loại phần tử bắt buộc phân loại

1. `canonical_text`: dữ liệu nội dung cưới (groom/bride name, lời ngỏ, cha mẹ, venue, address, từng timeline title/time, dresscode note, thank-you…)
2. `canonical_media`: ảnh bìa, story, chân dung, gallery 10 ảnh, ảnh cá nhân hóa, các ảnh được khách cho phép thay.
3. `canonical_style`: thuộc tính thị giác của một instance (font, cỡ, line-height, màu, align, opacity…); tuyệt đối không thay thế text data.
4. `derived_readonly`: đồng hồ đếm ngược, ngày âm lịch tự tính, lịch sinh động, QR được tạo từ account, RSVP count, map based on address.
5. `interactive_system`: nút mở thiệp, nút RSVP, link bản đồ, form lời chúc, gửi QR, thanh điều khiển nhạc, điều hướng, captcha/privacy controls.
6. `decorative_builtin`: các hình trang trí có trong template gốc; có thể chỉnh màu/animation nếu manifest cho phép, không tự ý cho xóa cấu trúc gốc.
7. `overlay_text`, `overlay_image`, `overlay_sticker`, `overlay_shape`, `overlay_frame`, `overlay_divider`, `overlay_icon`: vật thể do khách tạo thêm.
8. `global_design`: token typography/palette/font chính của *thiệp hiện tại* (không phải đổi toàn bộ site mayhen.vn).

## 03.2. Khóa hợp đồng nguồn dữ liệu

- Không dùng CSS selector mơ hồ để suy ra dữ liệu có thể sửa. Xây dựng **Editable Target Registry** kết hợp `data-sync-target` hiện tại + `schema.json` + metadata ổn định riêng cho từng instance.
- Ví dụ manifest (là ví dụ thiết kế, phải điều chỉnh theo code thật):

```json
{
  "profile": "classic_ten_v1",
  "template_slug": "giua-muon-ngan-vi-sao",
  "targets": [
    {
      "instance_id": "hero.groom.primary",
      "sync_key": "groom_name",
      "section_id": "hero",
      "type": "canonical_text",
      "editable": true,
      "capabilities": ["content", "font", "color", "size", "alignment", "animation"],
      "linked_scope": "all_canonical_instances"
    },
    {
      "instance_id": "album.photo.0",
      "sync_key": "gallery.0",
      "section_id": "album",
      "type": "canonical_media",
      "editable": true,
      "capabilities": ["replace", "crop", "focal_point", "opacity", "animation"]
    }
  ]
}
```

- `instance_id` phải **ổn định qua F5, re-render và save**, không dựa vào `nth-child` nếu thứ tự DOM thay đổi.
- Các field xuất hiện nhiều nơi: cùng một `sync_key` nhưng nhiều `instance_id`; **sửa nội dung** phải cập nhật tất cả vị trí cùng key, trong khi **style** có thể chỉ áp trên instance được chọn. Nếu user muốn style toàn bộ nơi dùng key, cần lựa chọn rõ và thao tác batch kiểm soát.
- Chỉ thay đổi dữ liệu tính toán qua canonical source: click vào countdown → inspector ngày cưới; click tên đám cưới → editor tên; click QR → inspector ngân hàng; click bản đồ → inspector địa chỉ; click RSVP → inspector cấu hình tham dự được phép, không cho sửa HTML action.
- Với text được tạo trong CSS `::before/::after`, pseudo-elements, JS inject, hoặc SVG: nếu thuộc tập khách cho sửa thì phải có registry adapter, không để click vô tác dụng.
- Với ảnh được tạo bởi `background-image`, `<picture>`, `srcset`, `canvas`, video frame: adapter phải nhận diện và sửa đúng source nếu manifest cho phép.
- Trạng thái selection không được đi vào public HTML, file export hoặc metadata SEO.

## 03.3. Ma trận từng loại target/permission

Lập FILE thật `docs/studio-v3/EDITABLE_TARGET_MATRIX.csv` sau khi quét từng template, cột bắt buộc:
`template_slug,profile,section_id,instance_id,canonical_key,type,css_selector,data_source,editable_content,editable_style,editable_position,editable_animation,editable_crop,read_only_reason,render_view,edit_side_effect,api_owner,tests,status,evidence`.

Không viết chung chung “10 sections đều sửa được”; phải điền target thật cho **từng mẫu đang active**, toàn bộ slot ảnh và các vị trí lặp tên/date. Nếu target không hợp lệ phải ghi lý do, không “coi như xong”.

---

# PHẦN 04 — TRẢI NGHIỆM CLICK-TO-EDIT (CORE P0)

## 04.1. Tương tác tiêu chuẩn

- **Single click** phần tử editable trên preview: chọn phần tử, vẽ selection outline tinh tế trong chế độ editor, hiện menu nổi ngay gần phần tử (không che text), mở đúng inspector theo loại.
- **Double click** hoặc Enter khi chọn văn bản: chuyển sang chế độ gõ trực tiếp (inline), đặt caret vào đúng vị trí, chọn/đổi nội dung không phá HTML gốc.
- **Một click vào ảnh**: chọn ảnh, mở inspector; nút “Thay ảnh”, “Cắt & căn”, “Đặt lại”, “Độ mờ”, “Hiệu ứng”. Không mở file picker khi người dùng chỉ muốn chọn.
- **Click shape/sticker/frame**: chọn đúng object, hiển thị handle góc, menu duplicate/lock/delete/layer và inspector chuyên biệt.
- **Click khoảng trống**: bỏ chọn; `Escape`: thoát inline hoặc bỏ chọn có thứ tự; không làm mất nội dung đang gõ.
- **Khi chọn phần tử ở iframe desktop**, parent nhận selection qua bridge và mở inspector; nếu chuyển preview mobile vẫn giữ selection nếu target còn tồn tại.
- **Hiển thị breadcrumb lựa chọn:** `Thiệp / Album / Ảnh 3`; tên thân thiện, không hiển thị id nội bộ với người dùng.
- **Không chiếm thao tác thật:** khi `editor-mode` đang bật, click vào nút RSVP/link mở bản đồ không tạo request thật; có chế độ “Xem như khách” để cho phép thao tác công khai mô phỏng đúng quyền.
- **Nested/overlapping elements:** ưu tiên hit-test object trên cùng, có nút “Chọn lớp bên dưới” hoặc danh sách Layers; không chọn nhầm phần tử cha.
- **Thao tác cảm ứng:** single tap để chọn, double tap/long press hoặc nút “Sửa chữ” để nhập; kéo bằng handle riêng nếu inline text đang focus; không chặn scroll vô tình.

## 04.2. Giao thức iframe → parent, parent → iframe

- Giữ thông điệp cũ; bổ sung tên message rõ ràng, ví dụ:

```json
{
  "type": "MAYHEN_EDITOR_TARGET_SELECTED",
  "protocol_version": 3,
  "request_id": "unique-id",
  "instance_id": "hero.groom.primary",
  "section_id": "hero",
  "data_type": "canonical_text",
  "capabilities": ["content", "font", "animation"],
  "bbox": { "x": 0, "y": 0, "width": 100, "height": 25 },
  "template_version": "v1"
}
```

- Các message dự kiến: `MAYHEN_EDITOR_READY`, `TARGET_SELECTED`, `TARGET_DESELECTED`, `TARGET_EDIT_STARTED`, `TARGET_TEXT_COMMITTED`, `TARGET_STYLE_PREVIEW`, `TARGET_STYLE_APPLIED`, `ASSET_SELECTED`, `OBJECT_CREATED`, `OBJECT_MOVED`, `OBJECT_TRANSFORMED`, `OBJECT_REMOVED`, `OBJECT_ANIMATION_APPLIED`, `SELECTION_ACK`, `ERROR`.
- Mỗi message phải có schema validation, định danh session/editor instance, `request_id`/sequence để bỏ phản hồi cũ, `instance_id` ổn định, kiểm tra quyền và nguồn dữ liệu.
- Khóa `event.origin` và `event.source` với danh sách iframe được tin cậy. `postMessage(payload, location.origin)` khi same-origin; không dùng `'*'` cho event chứa dữ liệu khách, không tự gọi hai kênh direct + postMessage dẫn đến double mutation. Cần **idempotency** cho message.
- Khi iframe có reload, rehydrate selections & style từ store, không chạy lại animation tạo thiệp hoặc focus/scroll bất ngờ.
- Cấm truyền HTML tùy ý từ iframe đến server, cấm cho iframe chạy script tùy biến. Plain text và danh mục thuộc tính allowlist.

## 04.3. Inline Text Editing & IME

- Với plain text an toàn, dùng `contenteditable="plaintext-only"` nơi browser hỗ trợ; fallback input/textarea overlay có cùng font nếu trình duyệt không hỗ trợ chuẩn đó.
- **KHÔNG reset `textContent` hoặc `innerHTML` mỗi phím** khi đang gõ; bảo vệ `compositionstart`, `compositionupdate`, `compositionend`, `beforeinput`, selection/range, paste text/plain, Enter/Escape, multiline allowed/forbidden.
- Tiếng Việt Unikey/EVKey phải được thử trực tiếp: “Huyền Trang”, “Thành Hôn”, “Đặng Thị Hồng”, chữ dấu kép, dán có dấu, IME dùng bộ gõ tiếng Việt và tiếng Nhật/Hàn nếu cần.
- Rich text: giai đoạn đầu **không cho paste HTML**; nếu có bold/italic/underline ở một phần text thì thiết kế schema run spans riêng với allowlist, không dùng HTML trôi nổi.
- Thay đổi nội dung phải đi qua store `EditorStore`/`ActionTypes` hiện có và canonical payload tương ứng. `Undo/Redo` phải khôi phục cả phía form lẫn preview, không chỉ DOM.
- Chỉ autosave sau debounce hợp lý và `compositionend`, không làm giật caret khi server trả ACK. Nếu lỗi mạng, giữ working copy và hiển thị “Chưa lưu lên máy chủ”.

---

# PHẦN 05 — TYPOGRAPHY: RIÊNG TỪNG CHỮ, TỪNG PHẦN, TOÀN THIỆP

## 05.1. Font registry

- Admin/server định nghĩa danh sách **font được phép và có giấy phép thương mại**, tên hiển thị thân thiện, file WOFF2/WOFF nội bộ, Unicode Vietnamese coverage, fallback stack. Không nhập URL font tùy ý từ client.
- Mỗi font có `font_id`, `display_name`, `family`, `weight_variants`, `supported_scripts`, `preview_sample`, `license`, `asset_path`, `fallback`.
- Mẫu thiệp gốc vẫn giữ font riêng nếu không có override. Font overrides **không xóa CSS mẫu**; ưu tiên custom properties hoặc style registry an toàn trên đúng target.

## 05.2. Phạm vi áp dụng

1. **Phần tử đang chọn:** chỉ `instance_id` hiện tại.
2. **Tất cả vị trí của cùng nội dung** (tùy chọn): ví dụ tất cả vị trí in tên chú rể.
3. **Toàn section** (tùy chọn): áp dụng token typography có kiểm soát cho text trong một section.
4. **Toàn bộ thiệp hiện tại:** 2 nhóm độc lập — `font_heading` và `font_body`; nếu muốn thêm `font_accent` cho chữ thư pháp thì explicit. Không áp dụng sang những thiệp khác, Admin hay giao diện mayhen.vn.
5. **Reset:** khôi phục font/size/màu/căn lề gốc của template; không xóa text người dùng.

## 05.3. Inspector chữ

- Nội dung, font, cỡ chữ, weight, italic, underline, strikethrough nếu phù hợp; letter spacing, line height, text align, màu chữ, opacity, nền label nếu allowed, word wrap, max lines, text shadow preset nhẹ (không CSS tùy ý), biến đổi chữ hoa/thường ở chế độ hiển thị nếu dữ liệu người dùng vẫn được bảo toàn.
- Hiện đơn vị chính xác `px`, `em` hoặc `rem`; UI có slider + input số + nút +/- với clamp min/max. Không cho giá trị âm phi lý, NaN, màu không hợp lệ.
- Trên thiết bị nhỏ phải có min readable (theo loại), lưu default template riêng; cảnh báo overflow và độ tương phản, không âm thầm phá typography nghệ thuật.
- Text tiếng Việt nhiều dấu, xuống dòng, đoạn dài, tên ghép dài, font thiếu glyph: có fallback thật và cảnh báo thân thiện.
- Chỉ thêm **Warp/curved text** nếu có render ổn định cho mọi font/viewport và không phá accessibility; không bắt buộc làm trong MVP vì hình tham chiếu có slider uốn cong.

---

# PHẦN 06 — ẢNH & FRAME: THAY THẲNG Ở PREVIEW

- Chạm vào đúng slot ảnh trong thiệp phải tự nhận ra key (`hero_photo_url`, `save_date_photo_url`, `groom_photo_url`, `bride_photo_url`, `gallery.0..9`, các slot hợp lệ khác).
- Mở inspector hình ảnh chứa ảnh đang dùng, `Thay ảnh`, `Cắt ảnh`, `Điều chỉnh vùng nhìn`, `Phóng to/thu nhỏ`, `Lật`, `Xoay` nếu template cho phép, `Đặt lại ảnh mẫu`, `Opacity`, `Animation`.
- **Không tự ép mọi ảnh về 3:4**: giữ tỷ lệ slot hiện có; crop theo aspect ratio của slot hoặc user-chosen frame. Bảo toàn ảnh gốc nếu người dùng muốn crop lại; lưu transform/focal point **non-destructive** khi có thể.
- Sử dụng trình uploader/cropper Mây Hẹn hiện có. Không tải một ảnh lên nhiều lần do preview iframe hoặc repeated target.
- Crop ảnh bằng Canvas cần xử lý EXIF orientation, điện thoại iOS/HEIC theo support thật, chất lượng ảnh, kích thước file/megapixel, hình bị trong suốt, lỗi máy chủ/cross-origin.
- `srcset`, `background-image`, `<picture>`, lazy image: adapter quyết định đúng nơi thay. Khi 1 key dùng nhiều vị trí, các ảnh phải cập nhật nhất quán; style/crop instance được phép khác nếu có metadata riêng.
- Object ảnh do khách thêm là overlay riêng, có giới hạn file, quota, bản quyền; không nhầm ảnh thêm vào album đã có nghiệp vụ riêng.
- **Frame library**: 6–12 khung sạch đẹp mang dấu ấn Mây Hẹn, viền oval, film, viền giấy, góc mềm, khung hoa tối giản; mọi khung hỗ trợ chọn ảnh, đổi mask, crop, replace/empty state. Không copy khung đối thủ.
- Nút xóa ảnh hoặc xóa overlay phải có semantics rõ: xóa ảnh khách upload khỏi vị trí ≠ xóa file đang được thiệp khác dùng. Cleanup asset có tham chiếu.
- Preview phải bằng ảnh của đối tượng thật khi demo animation, không để tất cả thumbnail dùng một ảnh skyline không liên quan như ảnh tham chiếu.

---

# PHẦN 07 — THÊM PHẦN TỬ TỰ DO (FREE DECORATION / OVERLAY LAYER)

## 07.1. Công cụ bên trái

- **Văn bản**: thêm tiêu đề, đoạn ngắn, chú thích, tên ghép trang trí, lời hẹn tùy chọn; không tự động đưa vào `wedding.*` nếu đó là văn bản trang trí.
- **Sticker**: thư viện nội bộ theo chủ đề (trái tim, hoa, ngôi sao, nơ, pháo giấy, chú chim, phong bì, tem sáp, góc trang trí, đường viền). Icon rõ nét SVG viewBox chuẩn.
- **Hình dạng**: hình tròn, elip, chữ nhật, góc bo, tam giác, đường thẳng, nét đứt, ngôi sao, trái tim, hình vòm, mũi tên, hình trám. Hạn chế các hình tạo cảm giác trẻ con; ưu tiên Quiet Luxury.
- **Khung**: khung chứa ảnh, khung vintage, khung film, oval/arch/rounded; có thể thay ảnh/crop.
- **Ảnh riêng**: tải ảnh và chèn vào section đang chọn.
- **Đường phân cách, hoa văn**: nhóm chi tiết mảnh phù hợp wedding editorial.
- **Bảng Layers**: xem danh sách đối tượng đang nằm trong section, chọn/ẩn/khóa/đổi tên/đưa trước/đưa sau.
- Không cần xây lại kho nhạc, thư viện mẫu, AI viết lời ngỏ, QR nếu các module hiện tại đã hoạt động.

## 07.2. Tọa độ, anchor và responsive

- Overlay object luôn thuộc một `section_id` hoặc anchor ổn định (ví dụ `hero`, `story`, `album`); không có hệ tọa độ toàn trang cuộn nhiều chục nghìn pixel rồi vỡ sau khi thêm nội dung.
- Xác định điểm neo theo section/element; lưu tọa độ **normalized x/y (0..1 hoặc %) trong không gian host**, kích thước theo host, constraints min/max; tùy trường hợp có `offset_px` bù tinh chỉnh, `rotation`, `scale`, `z_index`, `align`, `anchor`.
- Base template tiếp tục flow layout. Overlay đặt trong **isolated grid stacking layer** (`display:grid; grid-area` + transform) có giới hạn theo host và `pointer-events` phù hợp; layer không đẩy nội dung gốc vỡ. Nếu cần absolute-position trong overlay-only, tách biệt kỹ và ghi giải trình ngoại lệ quy chuẩn.
- Xây dựng conversion `viewport click coords → iframe CSS pixels → section local coords → normalized model`. Phải tính browser zoom, iframe scroll/scale, CSS transforms, DPR, scrolling, pinch zoom, hidden editor rails, sticky headers, font loading.
- **Không gán tọa độ desktop cho mobile một cách mù quáng.** Ưu tiên thiết kế mobile-first; dùng `mobile` normalized default và `desktop` override nếu người dùng thực sự chỉnh riêng. Có chế độ `áp dụng cả hai` vs `chỉnh riêng thiết bị` rõ ràng.
- Khi section thay chiều cao do text dài, ảnh tải chậm, hoặc đổi font, cần ResizeObserver và layout reflow để bám anchor đúng; không phủ lên RSVP và nút quan trọng.
- Snapping: căn trái/giữa/phải, baseline, lưới nhẹ 4/8px, safe area top/bottom, cảnh báo đè lên tên, ngày cưới, QR, nút RSVP, bản đồ. Cho tắt snap khi cần, nhưng không cho phá interactive click target quan trọng.
- Object ngoài vùng nhìn phải được thông báo và có lệnh “Đưa về giữa”. Không được rơi vào trạng thái không thể chọn và không thể xóa.

## 07.3. Transform & thao tác

- Drag bằng Pointer Events (mouse/pen/touch); không chỉ sử dụng HTML5 drag/drop (không nhất quán mobile).
- Resize handles trên các góc/cạnh; Shift giữ tỷ lệ (desktop), lock aspect ratio toggle; rotation handle, snap 0/15/45/90 độ; shortcut keyboard nếu focus đang ở canvas, không can thiệp khi người dùng đang gõ input.
- Duplicate, delete (với undo), lock/unlock, move front/back, hide/show, zoom to selected, group/ungroup nếu có kế hoạch cụ thể và test đủ.
- Có số lượng overlay hợp lý theo plan/tier và giới hạn tổng payload/assets. Không cho tạo vô hạn gây lag hoặc spam upload.
- “Sửa ảnh gốc theo slot” và “ảnh chèn trang trí” là hai luồng riêng; không nhầm khi người dùng chọn.
- Preview public tuyệt đối không hiển thị handles, target outlines, inspector, drag listeners, object IDs, editor tooltips, debug attributes nhạy cảm.

---

# PHẦN 08 — PANEL BÊN PHẢI ĐÚNG NGỮ CẢNH

Giao diện desktop tổ chức **3 vùng như UX pattern tham khảo** nhưng theo design system Mây Hẹn:

- Topbar: logo gọn, tên thiệp chỉnh được, Undo/Redo, trạng thái lưu thật, Xem như khách, Preview mobile/desktop, Xuất bản/Quản lý thiệp. Không nhồi quá nhiều CTA ngang; có overflow menu khi hẹp.
- Left rail (56–80px): Nội dung / Văn bản / Thư viện ảnh / Sticker / Hình dạng / Khung / Nhạc / Bố cục / Màu & Font / Mẫu / Hướng dẫn. Không đưa toolbar chỉ để đủ số lượng.
- Center Stage: thiệp thật trong iframe; pan/zoom (trong editor), lựa chọn phần tử/overlay; vùng click phải rõ, không đè lớp nền che chuột.
- Right inspector: thay đổi theo `canonical_text`, `canonical_media`, `overlay_text`, `overlay_sticker`, `overlay_shape`, `frame`, `theme`, `section`, `none`. Hai tab `Thiết kế` và `Chuyển động` chỉ xuất hiện khi có target hỗ trợ. Chỉ control có ý nghĩa đối với target mới xuất hiện.
- Floating toolbar: “Sửa chữ”, “Đổi ảnh”, “Nhân bản”, “Khóa”, “Đưa lên trước”, “Xóa” tùy capabilities; đặt an toàn khi gần viewport edge.
- Properties có accordion: Nội dung; Font & chữ; Màu; Vị trí & kích thước; Khung & cắt; Lớp; Hiệu ứng; Nâng cao; Tham chiếu tới nguồn dữ liệu nếu cần.
- Khi không có selection: inspector hiển thị thiết lập font toàn thiệp, bảng màu, gợi ý “Chạm vào một thành phần trên thiệp để chỉnh sửa”, không phải trang trống khó hiểu.
- Khi chưa có ảnh mới, trình bày trạng thái hữu ích và button thật, không hiện canvas checkerboard rỗng khổng lồ.
- Thiết kế Quiet Luxury: màu cream/ivory, muted gold, charcoal, dusty rose nhẹ; font UI dễ đọc, border 1px, shadow tinh tế, góc bo vừa phải; tránh accent đỏ cảnh báo làm màu chủ đạo.
- Desktop 1366/1440/1920, laptop 1024, tablet 768, mobile 390/375/320; drawer phải thu gọn/bottom sheet trên mobile. Canvas luôn còn không gian thao tác thực tế; không chỉ screenshot đẹp mà kéo thả được.

---

# PHẦN 09 — ANIMATION CHO MỌI PHẦN TỬ ĐƯỢC CHỌN

## 09.1. Motion registry dùng chung

- Bảo toàn hiệu ứng gốc trên các template và trình diễn AI Curated; animation được người dùng chọn là **override có chủ đích** cho instance.
- Preset groups đề xuất (chỉ làm các preset render ổn định, không nhất thiết copy đầy đủ):
  - `Không hiệu ứng / Theo mẫu gốc`
  - `Xuất hiện`: Fade in, Lift, Rise, Slide left/right/up/down, Gentle Zoom, Blur to clear (nhẹ).
  - `Nhấn nhẹ`: Float, Soft Pulse, Shimmer nhẹ, Wobble nhỏ, Glow.
  - `Nở/Reveal`: Paper unfold, Mask reveal, Wipe left-to-right, Scale in, Ribbon reveal.
  - `Lặp dịu`: Slow float, Tiny sway, Sparkle (có giới hạn).
  - `Cuộn trang`: Enter viewport once, replay on re-enter (nếu user chọn và không gây giật).
- Mỗi preset xác định `id`, `label_vi`, hỗ trợ loại object nào, preview thật, keyframes native CSS, `allowed_duration`, `allowed_delay`, `allowed_easing`, `trigger`, `repeat`, `fill_mode`, motion safety.
- Không đặt tên hiệu ứng một kiểu nhưng keyframe thực tế khác; ảnh thumbnail phải preview đúng trên object thật/placeholder thuộc loại đúng.
- Animation **chỉ chạy lúc thích hợp**, không tự tái tạo thiệp mỗi lần F5. Template opening vẫn theo flow công khai; user-selected element animation tuân trigger và `prefers-reduced-motion`.

## 09.2. Inspector animation

- Chọn preset → xem thử chỉ đối tượng đang chọn (không cuộn tất cả trang).
- Duration (mặc định tùy preset), delay, easing, once/loop, trigger: on load / on enter viewport / after opening invitation; option thứ tự xuất hiện theo section nếu tương thích.
- Preview / Replay / Reset to template animation / Remove animation; các nút hoạt động độc lập.
- Không cho loop vô hạn hiệu ứng nặng trên nhiều ảnh lớn; hạn chế đồng thời, respect CPU/power/network, pause tab hidden, stop offscreen, remove listeners khi unmount.
- Khi user thay font/resize/drag, animation preview không giữ transform làm sai tọa độ. **Tách `layoutTransform` và `motionTransform` vào các wrapper lồng nhau**, không ghi đè `transform` của nhau.
- Không autoplay âm thanh do chọn animation. Chức năng nhạc giữ nút chủ động và tuân quy tắc autoplay trình duyệt.
- Với `prefers-reduced-motion`, chuyển động tối thiểu/không; nội dung hiển thị ngay, không bị `opacity:0` vĩnh viễn.

---

# PHẦN 10 — SCHEMA/PERSISTENCE: KHÔNG MẤT DỮ LIỆU

Đề xuất namespace **cần kiểm chứng với schema code mới**, KHÔNG đè lên `wedding.*`:

```json
{
  "visual_editor": {
    "version": 1,
    "global_style": {
      "heading_font_id": null,
      "body_font_id": null,
      "accent_font_id": null
    },
    "instance_styles": {
      "hero.groom.primary": {
        "font_id": "mayhen-serif-01",
        "font_size_px": 30,
        "text_align": "center",
        "color": "#6D4A40"
      }
    },
    "overlays": [
      {
        "id": "ulid-or-uuid",
        "type": "overlay_text",
        "section_id": "story",
        "anchor": { "type": "section", "id": "story" },
        "position": {
          "mobile": { "x": 0.52, "y": 0.38, "width": 0.56 },
          "desktop": null
        },
        "rotation_deg": 0,
        "z_index": 5,
        "locked": false,
        "visible": true,
        "content": { "text": "Ngày chúng mình chung đôi" },
        "style": { "font_id": "mayhen-serif-01", "color": "#987B6B" },
        "motion": { "preset": "soft-rise", "duration_ms": 750, "delay_ms": 100, "trigger": "viewport", "repeat": 1 }
      }
    ],
    "instance_animations": {
      "hero.groom.primary": { "preset": "fade-up", "duration_ms": 800, "delay_ms": 0, "trigger": "viewport", "repeat": 1 }
    }
  }
}
```

**Đây chỉ là ví dụ cấu trúc**, không áp đặt nguyên xi nếu database/schema hiện tại có đối tượng tương đương. Agent phải khảo sát các key `custom_config`, `draft_config`, `schema_version`, `template_version`, `revision_id` và ghi migration plan ẩn/hiện backwards compatible.

Quy tắc:

- `wedding.*`/`wedding.extra_data.*`: canonical content; bất kỳ thay đổi text/ảnh gắn key thì save vào đây qua đường hiện có.
- `custom_config.visual_editor`/`draft_config.visual_editor`: styles, overlays, animations. Không đẩy `data:image` base64 lớn, `blob:` URL hay HTML/JS vào JSON.
- UUID/ULID ổn định cho overlay; version/schema upgrade idempotent; clamps server-side theo schema allowlist.
- JSON merge phải **deep patch theo danh mục có khóa** (hoặc cơ chế thay thế field có revision), tránh shallow `array_merge` làm mất object khác khi sửa một style; hỗ trợ xóa key/overlay bằng patch tombstone hoặc replace-set revisioned.
- Autosave optimistic lock có `revision_id`; client phải giữ các action đang chờ khi ACK về trễ/out of order; lỗi `409` phải hiện chọn “Giữ bản máy”, “Xem thay đổi”, “Tải bản dự phòng”, không tự overwrite.
- Undo/redo history của content, style, overlays, motion lưu action có inverse; tách local transient state (selection/hover/drag) khỏi dirty persisted state.
- Khi người dùng nhấn Reset mẫu: cần phân biệt `reset_styles`, `reset_decoration`, `reset_all_config` và tác động lên dữ liệu cưới; không xóa dữ liệu khách ngoài ý muốn.
- Khi đổi mẫu: canonical data **bất biến**; style overrides chỉ chuyển khi target compatible; overlay có section anchor không tồn tại phải được giữ trong `unplaced_tray` với cảnh báo và thao tác chuyển vị trí, không xóa thầm.
- Draft/public parity: render final từ đúng persisted config, không chỉ qua JS của Studio; preview iframe và `GET /thiep/{slug}` thống nhất; cache invalidation version/revision.
- Nếu user chỉnh 1 thiệp trong nhiều bản thiệp gắn cùng Wedding: **canonical wedding data shared**, visual styles chỉ thuộc một Invitation. Giải thích side effects trước khi đồng bộ data shared cho những thiệp khác.
- Giới hạn số layer, độ dài text, số màu/font, texture, media size và kích thước JSON để đảm bảo shared hosting/DB không bị quá tải.

---

# PHẦN 11 — BẢO MẬT, PHÂN QUYỀN, RÀ SOÁT NGHIỆP VỤ

1. Authorization theo **invitation_id + ownership + plan + locked state + editor mode** trên từng API. Không tin `instance_id`, `section_id`, asset path hoặc `template_slug` do client gửi.
2. `uploadAsset` phải kiểm tra upload owner/session, giới hạn file/bytes/dimension/quota/rate, MIME sniff thực, chuyển SVG qua pipeline sanitize hoặc **chỉ cho dùng SVG sticker do Admin quản lý**. Không nhận script hoặc external references trong SVG.
3. Font registry chỉ load file nội bộ, không cho user nhập arbitrary `@font-face`/font URL; không inject CSS/HTML tùy ý, không dùng `eval` hay `new Function`.
4. CSS style allowlist: màu HEX/rgba được kiểm tra, font ID, finite number trong min/max, opacity 0–100, position clamp, angle/delay/scale clamp. Không nhận free-form CSS và URL JavaScript.
5. Paste plain text, HTML-escape, chống XSS/attribute injection/content spoofing. Hạn chế text trang trí giả nút thanh toán hoặc nhập mật khẩu, chống overlay trên nút RSVP/QR, map links, form nhập dữ liệu.
6. `postMessage`: origin/source/session token/request id; iframe đúng origin. Không nhận message tuỳ tiện từ các iframe ngoài editor. Hạn chế double mutation do direct-call và postMessage fallback.
7. Public invitation không được mang JS editor code để lựa chọn/kéo; chỉ render overlay và motion đã được sanitize, tránh tải script nặng.
8. Tài nguyên upload lỗi/trùng/không dùng cần cleanup theo retention policy, không xóa asset tham chiếu bởi nhiều invitation. Người dùng chỉ thấy media thuộc quyền của mình.
9. Hành vi click-to-edit không làm trigger webhook, RSVP, Wish, Payment, QR/bank write hay cập nhật thống kê page view trong chế độ editor.
10. Cập nhật dữ liệu tài chính/địa chỉ/ngày cưới phải dùng validation business hiện tại. Người dùng không được thay số tài khoản bằng text overlay rồi tưởng VietQR đã thay.
11. Bản nháp chưa xuất bản không được truy cập trái phép qua iframe/thumbnail/static asset; các biên review/agency/tenant phải đúng ACL.
12. Template versions: không làm hỏng lời mời đang phát hành khi template update; snapshot/render compatibility theo version.

---

# PHẦN 12 — BẢO VỆ GIAO DIỆN THẬT VÀ RESPONSIVE

- Thiết kế trước ở **390px** vì thiệp cưới chủ yếu được mở bằng điện thoại, nhưng Studio desktop cần hiệu năng như ứng dụng chuyên nghiệp.
- Các kiểm thử màn hình: `320×568`, `360×800`, `375×812`, `390×844`, `430×932`, `768×1024`, `1024×768`, `1366×768`, `1440×900`, `1920×1080`.
- Zoom editor 50%, 75%, 100%, 125%; browser zoom 80/100/125/150%; artboard scroll sâu hàng chục section; chọn element tại mép trên/dưới/sát rìa.
- Không để handle nằm ngoài màn hình, không để overlay làm xuất hiện horizontal scrollbar, không cắt chữ, không che RSVP/QR/link bản đồ và không làm chậm FCP/LCP.
- Phân biệt viewport public (full width mobile) với preview mobile nằm trong desktop canvas; dùng **cùng breakpoint behavior dựa trên chiều rộng container/viewport thực**, nếu cần container queries thì test cross browser.
- Khi người dùng đặt overlay lên template ở desktop nhưng xem điện thoại, cảnh báo nếu layout có rủi ro; có nút “Điều chỉnh bản mobile”, không âm thầm di chuyển phá thiết kế gốc.
- `ResizeObserver`, `MutationObserver`, `IntersectionObserver` phải cleanup, không loop vô hạn sau mỗi re-render/animation.
- Kiểm tra màn mở thiệp/envelope không chắn preview iframe; audio trên Safari không autoplay sai; image aspect ratio không méo, shape giữ tỷ lệ, ảnh và SVG giữ viewBox.
- Accessibility: keyboard selection, focus outline visible, focus restore khi inspector đổi, ARIA cho sliders, visible label, contrast, `prefers-reduced-motion`, không animation làm khó đọc/nhấp nháy mạnh.

---

# PHẦN 13 — LUỒNG NGHIỆP VỤ BẮT BUỘC MÔ TẢ VÀ CODE

Viết sequence diagrams hoặc từng bước đầy đủ cho mỗi luồng:

**FLOW-V3-01** Người dùng mở `/tao-thiep` với mẫu A → iframe READY → đồng bộ dữ liệu cưới → click đúng tên cô dâu → inspector → sửa ngay trên text → form/store cập nhật → autosave ACK → F5 → tên đã sửa; không thay format tên ở đoạn khác bất ngờ.

**FLOW-V3-02** Click tên chú rể hiển thị ở Hero → đổi nội dung → tất cả vị trí cùng canonical key cập nhật; đổi font một instance → các vị trí còn lại giữ font gốc.

**FLOW-V3-03** Chọn `global heading font` → chỉ text heading đang có capability áp dụng; các asset typography nghệ thuật cố định vẫn đúng; reset khôi phục font template.

**FLOW-V3-04** Click ảnh số 4 trong Album → chọn media → crop đúng tỉ lệ slot → preview → save → public → ảnh số 4 mới, 9 ảnh còn lại không đổi.

**FLOW-V3-05** Thêm text mới trong Story → sửa inline tiếng Việt → kéo → resize → đổi màu → animation → autosave → xem mobile → không che nội dung → publish.

**FLOW-V3-06** Thêm sticker SVG → đổi màu → scale/rotate → duplicate → move backward → khóa → F5 → giữ nguyên, public không có handle.

**FLOW-V3-07** Thêm frame → choose image → crop → change frame shape → rotate → reset → redo/undo đúng cả ảnh và layout.

**FLOW-V3-08** Click countdown / calendar → inspector ngày cưới → sửa canonical date → đồng hồ và lịch tự đồng bộ; không biến đồng hồ thành text gõ tự do.

**FLOW-V3-09** Click thông tin địa điểm → sửa địa chỉ → bản đồ và link hướng dẫn đi cập nhật thật; không dùng overlay text để giả dữ liệu nghiệp vụ.

**FLOW-V3-10** Click VietQR → inspector tài khoản theo quyền → validation → QR mới; không cho overlay chặn tương tác của khách.

**FLOW-V3-11** Animation set `Fade Up + delay` trên ảnh → preview riêng → open public on phone → animation đúng → reduced-motion đọc được ngay → không tự chạy lại crafting.

**FLOW-V3-12** Đổi mẫu A→B→A có styles/overlays → canonical content bất biến; các layer không còn anchor không bị mất mà vào unplaced tray; không phá mẫu.

**FLOW-V3-13** Autosave offline → đang gõ vẫn giữ caret; reconnect → version ACK; 2 tab sửa cùng thiệp → conflict UI rõ, không silently overwrite.

**FLOW-V3-14** Thiệp bị Admin khóa trong khi editor đang mở → reject new save, preserve local backup, không tự mở public.

**FLOW-V3-15** Thiệp AI Curated 7 sections → edit trên đúng native template còn trong current design profile → overlay/animation chỉ dùng section có trong profile → không tạo Dresscode/Gift/RSVP bị loại.

**FLOW-V3-16** Khách đăng nhập/anonymous/agency support theo quyền thật → xem và sửa đúng thiệp; không truy cập thiệp của người khác qua ID, file URL, message hoặc asset API.

**FLOW-V3-17** User chọn một image nằm trong overlay và nhấn delete → xóa overlay, không xóa ảnh gốc; undo khôi phục layer.

**FLOW-V3-18** User sửa nhiều fields trong 30 giây, autosave liên tiếp, backend trả ACK ngược thứ tự → phiên bản mới nhất được giữ, status Saved đúng.

**FLOW-V3-19** Scroll thiệp rất dài, zoom 125%, bật animation và kéo overlay sang mobile preview → hit-test / coords đúng, không nhảy tọa độ.

**FLOW-V3-20** Public invitation với host/guest thật mở links RSVP, directions, music, gallery, wish, VietQR → tất cả vẫn hoạt động sau khi có new edit overlay.

---

# PHẦN 14 — LỘ TRÌNH THỰC THI, KHÔNG ĐÁNH DẤU DONE ẢO

## WAVE 00 — SOURCE TRUTH / DELTA AUDIT

- Chạy audit source mới nhất; xác minh 8 ảnh là **tham khảo chức năng**, không phải code được cung cấp.
- Sinh `MAY_HEN_STUDIO_V3_CODE_AUDIT.md` với code line, trạng thái hiện tại và risk; `EDITABLE_TARGET_MATRIX.csv` cho TẤT CẢ template active.
- Chụp screenshot baseline Studio + một thiệp gốc trên 390/1440. Không sửa UI trước khi có ảnh baseline.
- Lập risk register cho bridge, asset upload, versioning, no-overlap, multi-tab. Nếu phát hiện hổng security nghiêm trọng, P0 sửa trước.

## WAVE 01 — CONTRACT / ARCHITECTURE DESIGN

- Sơ đồ parent editor, iframe bridge, registry, event dispatcher, reducer, persistence, public renderer.
- Thỏa thuận editable capabilities cho từng type, schema_version, migration approach, rollback, test fixtures, compatibility.
- So sánh ít nhất 2 phương án overlay (grid transforms / positioned overlay exception) và chọn giải pháp bám skill. Ghi quyết định kỹ thuật (ADR).
- Tạo một example end-to-end trên **một mẫu gốc đang active**, không đại trà trước khi chứng minh.

## WAVE 02 — CLICK TEXT / CONTENT SYNC (P0)

- Select target; inline edit; route back into canonical store; update all linked instances; inspector contextual; keyboard/touch; IME; undo/redo; autosave; backend ACK.
- Test against one common template, including duplicate keys, mixed HTML markup and missing/hidden fields.

## WAVE 03 — CLICK IMAGE / REPLACEMENT / CROP (P0)

- Target selection for slots; image inspector; correct key mapping; crop non-destructive; upload; rate limits; persistence; nested images/background images. Test 10 gallery slots and repeated source images.

## WAVE 04 — FONT & GLOBAL STYLES (P0)

- Font registry local with license, font picker on target, per-key/all headings/all body; inspector values; reset; fallback; typography visual QA mobile.

## WAVE 05 — FREE TEXT / STICKER / SHAPE / FRAME (P1)

- Decoration object schema, host layer, object toolbox, create/duplicate/delete/layer, SVG curated assets, pointer drag/resize/rotation, section snap, responsive anchors, transform isolation.
- Thử “giữ nguyên bản gốc rồi chèn” để chắc chắn không làm sai flow layout.

## WAVE 06 — PER-ELEMENT ANIMATION (P1)

- Motion registry, inspector, real preview on target, apply/reset, cache/reduced motion, public renderer. Test object + built-in editable instance + long page + multiple animated objects.

## WAVE 07 — POLISH / VISUAL UX / A11Y / MOBILE (P1)

- Hoàn thiện topbar/left rail/inspector/floating menu. Có UX onboarding ngắn 3 bước cho lần đầu, không lạm dụng tooltip. Keyboard/mobile touch/focus, no text cut, responsive, loading/offline/save.

## WAVE 08 — MULTI-TEMPLATE / AI CURATED / RELEASE (P0)

- Mở rộng bằng adapter/manifest sang các template active, profile 10 và 7 nếu có; test public, version switch, locked/unpublished/password/expired; rollback flag riêng và release toggles.
- QA trước release phải có screenshots đối chiếu template gốc trước/sau, output public, log test và các case fail/blocked minh bạch.

### QUY TẮC VỚI MỖI WAVE

1. Xác minh code hiện tại để tránh làm trùng.
2. Chốt yêu cầu với test mô tả đủ case/nhánh.
3. Sửa mã thật bằng công cụ edit có diff người dùng xem được.
4. Chạy test hẹp; sửa lỗi; thêm regression tests bền vững trong `tests/Feature` hoặc `tests/Unit` (không xóa sau test).
5. Chạy test liên quan giữa Studio, template, guest/public, autosave, security.
6. Kiểm tra browser thật: thao tác screenshot trước/sau 390px + 1440px, ít nhất Chrome và một browser mobile nếu khả dụng. Không giả mạo browser test.
7. Ghi status **DONE / PARTIAL / BLOCKED / NOT_TESTED** + file paths + test names + screenshots + hành vi thật.
8. Cập nhật `context.gemini.md`, roadmap/task ID, và đề xuất commit message tiếng Việt có dấu.
9. Tự chuyển sang wave tiếp theo an toàn. Nếu cần đổi quy chuẩn/hệ thống thanh toán hoặc quyền người dùng thì báo impact và xin duyệt trước khi thực hiện thay đổi rủi ro.

---

# PHẦN 15 — MA TRẬN TEST TỐI THIỂU / QUALITY GATES

Lấy `MAY_HEN_STUDIO_V3_TEST_MATRIX.csv` trong cùng gói làm điểm khởi đầu; bổ sung theo source mới nhất. Tất cả bắt đầu `NOT_RUN` cho đến khi có bằng chứng.

### Gate A — Functionality
- 100% target có quyền sửa trong registry chọn được bằng click/tap; không có nút fake.
- Text edited within iframe sync về form, store, DB, re-render; repeated keys và linked instances đúng.
- Image edited/cropped/replaced đúng slot; album 0..9 không nhầm; reset logic đúng.
- Font per-instance + global heading/body + reset + fallback hoạt động.
- New overlay layer được drag, resize, rotate, duplicate, delete, hide/lock, z-layer theo capabilities.
- Animation preset render đúng, configurable, reset đúng, public parity.

### Gate B — Persistence / Business
- F5, reload, đổi preview, đổi mẫu, duplicate, publish/unpublish, rollback, multi-tab, xung đột revision, offline/reconnect.
- Guest interactions/RSVP, Wish, Check-in, Gift QR không ảnh hưởng hoặc bị giả mạo khi editor bật.
- Anh/chị phải chứng minh zero-loss với dữ liệu cưới và mẫu thiệp khác; bản nháp có quyền riêng tư.

### Gate C — Security / Resilience
- Cross-user IDOR, cross-template asset, upload MIME spoofing, SVG XSS, CSS injection, HTML paste XSS, event.origin spoof, postMessage injection.
- Giới hạn số overlay, kích cỡ text/assets, throttling, DB JSON limits, stale ACK, concurrent saves.

### Gate D — Visual / Accessibility / Performance
- So sánh desktop/mobile trước–sau: DOM template gốc, ảnh bìa, typography, bố cục, không mất section, không cắt chữ.
- Tất cả 10 viewport trên; dài ngắn khác nhau, fonts Vietnamese, 1–30 overlays, animation repeat, reduced-motion.
- Performance budget cần đo baseline rồi đặt mục tiêu số (LCP/INP/layout shift, JS size, memory). Không tự công bố số đo chưa test.
- Không đẩy UI editor vào public page, không làm preview/thiệp thực bị chậm rõ rệt.

**Không gọi 1.502 test cũ PASS là bằng chứng mới.** Muốn DONE phải có testcase trực tiếp cho thay đổi này và browser screenshots mới.

---

# PHẦN 16 — DELIVERABLE BẮT BUỘC

Các output do agent TỰ TẠO trong dự án (được chủ dự án cho phép tạo tài liệu ở nhiệm vụ này):

1. `docs/studio-v3/MAY_HEN_STUDIO_V3_CODE_AUDIT.md`: trạng thái hiện tại, file:line, những gì giữ nguyên, những gì bổ sung, phát hiện lỗi.
2. `docs/studio-v3/EDITABLE_TARGET_MATRIX.csv`: all active templates, all canonical editable slots, overlay capabilities, API mapping.
3. `docs/studio-v3/MAY_HEN_STUDIO_V3_PRODUCT_REQUIREMENTS.md`: UX behavior chính xác cho keyboard/mouse/touch.
4. `docs/studio-v3/MAY_HEN_STUDIO_V3_ARCHITECTURE_ADR.md`: phương án và tradeoff của overlay, renderer, event bridge, schema, backwards compatibility.
5. `docs/studio-v3/MAY_HEN_STUDIO_V3_IMPLEMENTATION_ROADMAP.md`: 9 Wave, task IDs, dependency, risks, test + evidence + status.
6. `docs/studio-v3/MAY_HEN_STUDIO_V3_QA_RESULTS.md`: tên test thật, kết quả thật, ảnh browser, lỗi, vấn đề chưa xác minh.
7. Mã nguồn Laravel/Blade/JS/CSS và test được chỉnh tại đúng vị trí quy định, không code trùng lặp.
8. Cập nhật `context.gemini.md` sau khi sửa code; thông báo những file đã chạm, ảnh hưởng trên các luồng khác, commit tiếng Việt có dấu.

Không tạo một bộ 8 file `.md` lặp nội dung; viết rõ ràng, mô tả đích xác thay đổi. Không tự push/deploy.

---

# PHẦN 17 — TIÊU CHÍ NGHIỆM THU CUỐI CÙNG, DÙNG ĐỂ TỪ CHỐI CODE SAI

Tính năng CHỈ được nghiệm thu nếu:

**A. Dữ liệu đúng:** Bấm sửa tên trong preview → tên thay đổi ngay → lưu thành công → mở lại → public đúng → những chỗ lặp tên đồng bộ. Đổi font riêng không làm đổi tên, đổi font toàn thiệp không làm đổi CSS template gốc.

**B. Ảnh đúng:** Click ảnh bất kỳ trong tập khách được quyền thay → mở đúng ảnh, crop đúng frame, tải ảnh có xác thực → lưu → public đúng, không làm mất ảnh còn lại hoặc kéo méo ảnh.

**C. Kéo thả đúng:** Chèn sticker/khung/chữ và thả vào bất cứ nơi **hợp lệ** trong section → reload không nhảy vị trí, mobile có thể điều chỉnh, không đè lên các tương tác cốt lõi. Không được đổi 10 section thành LadiPage clone.

**D. Animation đúng:** Mọi selectable element hỗ trợ motion đều có tab animation thật, preview đúng object, public đúng motion, không làm mất data/transform, không ảnh hưởng `prefers-reduced-motion`.

**E. UI đúng:** Studio Mây Hẹn vẫn là Mây Hẹn, không sao chép Miwedi; không rối, không icon vô nghĩa, nhanh và dùng được với máy tính lẫn điện thoại.

**F. Security đúng:** Một khách không sửa được thiệp người khác, không lách khóa, không inject script/CSS qua sticker, style hay text, không upload vô hạn, không tạo request RSVP thật trong editor.

**G. Regression đúng:** Tất cả mẫu đã được hỗ trợ phải render đúng native Blade/CSS/JS; thiệp 10 section và thiệp AI curated 7 section đúng profile; version/draft/publish/rollback thật; các form RSVP/Gift/Wish/Map không bị ảnh hưởng.

**H. Evidence đúng:** Mỗi capability có test, screenshot/video thao tác từ browser thật (nếu môi trường cho phép), file path và trạng thái; không tuyên bố hoàn thành 100% dựa trên test không bao phủ.

**Mệnh lệnh bắt đầu:** Đọc source hiện tại và 8 ảnh, tạo `WAVE 00` và `WAVE 01` để đối chiếu và thiết kế kỹ thuật, sau đó triển khai tuần tự WAVE 02–08 với kiểm thử sau từng wave. Ưu tiên đạt một luồng hoạt động end-to-end trên một native template thay vì làm 10 menu chưa có backend. Không tự ý đổi đường lối kiến trúc dự án.
