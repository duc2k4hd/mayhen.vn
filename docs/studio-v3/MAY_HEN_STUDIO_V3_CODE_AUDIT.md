# MÂY HẸN — BÁO CÁO KIỂM KÊ MÃ NGUỒN STUDIO VISUAL EDITOR V3 (WAVE 00)

> **Ngày kiểm kê**: 2026-11-18  
> **Phạm vi**: Kiểm kê trực tiếp 100% mã nguồn thực tế của Studio Tạo Thiệp (`/tao-thiep`), cầu nối iframe (`editor-bridge.blade.php`), 7 thư mục mẫu thiệp đang hoạt động, dịch vụ lưu nháp/xuất bản, bảo mật media, hệ thống font và bộ kiểm thử hiện có trước khi viết bất kỳ dòng mã nâng cấp nào.  
> **Ảnh chụp hiện trạng gốc (`Wave 00 Baseline`)**: Đã lưu tại `docs/studio-v3/qa/w00-baseline/` (`studio-desktop-1440.png`, `studio-mobile-390.png`, `studio-mobile-preview-390.png`, `template-vom-troi-desktop-1440.png`, `template-vom-troi-mobile-390.png`, `template-giua-muon-ngan-mobile-390.png`, `baseline-manifest.json`).

---

## 1. Ghi nhận minh bạch về thư mục ảnh tham chiếu (`docs/studio-v3/references/`)

- Kiểm tra lịch sử Git (`git show 4dd6d50 --stat`) và toàn bộ ổ đĩa dự án xác nhận commit `4dd6d50` chỉ đưa vào 2 tệp:
  - `docs/studio-v3/MAY_HEN_STUDIO_DIRECT_EDIT_VISUAL_EDITOR_V3_MASTER_PROMPT.md`
  - `docs/studio-v3/MAY_HEN_STUDIO_V3_TEST_MATRIX.csv`
- Thư mục `docs/studio-v3/references/` (`01-text-inspector.png` .. `08-animation.png`) chưa được commit vào kho mã nguồn.
- Toàn bộ hành vi tương tác chuẩn của 8 ảnh tham chiếu đã được mô tả đầy đủ tại **Phần 02 (`lines 89-105`)** của `MAY_HEN_STUDIO_DIRECT_EDIT_VISUAL_EDITOR_V3_MASTER_PROMPT.md`, đồng thời quy định rõ chỉ học mô hình tương tác, tuyệt đối không sao chép thương hiệu hay bảng màu đối thủ. Thiết kế UI Studio V3 tuân thủ 100% thẩm mỹ **Mây Hẹn Quiet Luxury** (`tokens.css` / `editor-v2.css`).

---

## 2. Bảng Trạng Thái Năng Lực Hiện Tại (`ST-001` .. `ST-010`)

| Mã kiểm kê | Thành phần | Tệp thực tế (`file:line`) | Trạng thái | Ghi chú kỹ thuật & Khoảng trống so với V3 |
|---|---|---|---|---|
| `ST-001` | Click chọn phần tử trong preview iframe | `resources/views/client/invitations/partials/editor-bridge.blade.php:620-795` | `PARTIAL` | Đã có bắt sự kiện click cơ bản trên `[data-sync-target]` để bật `contenteditable` hoặc gửi `MAYHEN_OPEN_MEDIA_PICKER`, nhưng **chưa có** định danh `instance_id` ổn định, chưa có khung chọn Quiet Luxury kèm thanh công cụ nổi (`Floating Quick Toolbar`), chưa chặn các hành vi khách mời (RSVP/Lời chúc/Map/QR) theo chế độ `editor` vs `guest_preview`, chưa điều hướng phần tử dẫn xuất (`countdown`, `calendar`, `lunar`, `couple_names`) về trường gốc. |
| `ST-002` | Sửa chữ inline trực tiếp trên thiệp | `resources/views/client/invitations/partials/editor-bridge.blade.php:620-735`, `public/assets/client/js/pages/editor.js:934-975` | `PARTIAL` | Có `contenteditable` sơ khai gửi `MAYHEN_INLINE_TEXT_CHANGE`, nhưng thiếu khóa bảo vệ bộ gõ tiếng Việt IME (`compositionstart`/`compositionend`), thiếu ép dán văn bản thuần (`text/plain`), thiếu phân biệt đơn dòng (`Enter` chốt) với đa dòng, và bị lỗi ghi đè `el.textContent` làm mất thẻ con `<span>` lồng nhau tại 6 mẫu thiệp. |
| `ST-003` | Đổi font riêng từng chữ + font toàn thiệp | `resources/views/client/layouts/invitation.blade.php:97-133`, `public/assets/client/js/editor/theme-controller.js:1-226` | `MISSING` | Chưa có Font Registry cục bộ chuẩn tiếng Việt cho V3; chưa có `instance_styles` theo từng phần tử chữ. `invitation.blade.php:98-131` đang gọi Google Fonts CDN bên ngoài và nội suy trực tiếp chuỗi font chưa qua allowlist, đồng thời 6/7 mẫu thiệp dùng biến CSS font nội bộ riêng nên không nhận `--font-heading`/`--font-body`. |
| `ST-004` | Click ảnh để thay / crop / căn điểm nhìn | `public/assets/client/js/pages/editor.js:1037-1360`, `app/Services/SmartFocalPointService.php:1-196`, `invitation.blade.php:139-225` | `PARTIAL` | Đã có `SmartFocalPointService` phía backend và modal cắt ảnh Canvas trên Studio, nhưng modal cắt ảnh đang ép cứng tỷ lệ `3:4` cho mọi ảnh, xuất chuỗi `data:image/jpeg` nhồi thẳng vào ô input thay vì tải lên qua `uploadAsset` an toàn, chưa có Image Inspector bên phải (zoom, focal X/Y trực quan, độ mờ, đặt lại ảnh mẫu). |
| `ST-005` | Thêm phần tử mới (text, sticker, shape, frame, ảnh trang trí) | — | `MISSING` | Chưa có kho Sticker SVG / Hình dạng / Khung ảnh nội bộ cho V3, chưa có cấu trúc `visual_editor.overlays` và lớp `.mayhen_invitation_overlay_layer` neo theo từng section. |
| `ST-006` | Kéo, thả, phóng to/thu nhỏ, xoay, căn hàng phần tử trang trí | `public/assets/client/js/editor/dnd-section.js:1-232` | `MISSING` | Hiện mới chỉ có kéo-thả sắp xếp thứ tự 10 mục lớn ở thanh bên trái (`dnd-section.js`), hoàn toàn chưa có Pointer Events drag/resize/rotate, đường gióng căn giữa (`snap guides`), vùng an toàn (`safe zone`) hay quản lý lớp (`z-index`, khóa, ẩn, nhân bản, xóa) cho phần tử trang trí trên thiệp. |
| `ST-007` | Chọn animation cho từng phần tử & xem thử tức thì | `resources/views/client/invitations/*/script.js` | `MISSING` | Mỗi mẫu thiệp đang tự chạy `IntersectionObserver` tĩnh của riêng mẫu đó; chưa có `MotionRegistry` dùng chung, chưa có bảng chọn hiệu ứng từng phần tử (`instance_animations` / `overlay.motion`), chưa tách lớp `transform` bố cục và `transform` chuyển động. |
| `ST-008` | Lưu nháp, Undo/Redo, đổi mẫu, xuất bản dữ liệu Visual Editor | `public/assets/client/js/editor/{store.js,history.js,autosaver.js,template-switcher.js}`, `app/Services/InvitationTemplateService.php:1060-1240` | `PARTIAL` | Đã có `EditorStore`, `HistoryManager` (tối đa 50 bước), `Autosaver` (khóa lạc quan `revision_id` tuần tự `+1`) và `PublishDiffInspectorService`. Tuy nhiên `saveDraftWithLock()` (`InvitationTemplateService.php:1219`) đang gộp mảng nông (`array_merge`) làm mất nhánh con hoặc không xóa được khóa đã reset, và `invitation.blade.php:6-8` chưa đọc `draft_config` khi nạp lại iframe trong Studio. |
| `ST-009` | Hiển thị đồng nhất trên thiệp công khai (`/thiep/{slug}`) | `app/Http/Controllers/Client/InvitationEditorController.php:412-729`, `resources/views/client/layouts/invitation.blade.php:1-523`, `public/assets/client/js/common/invitation-config.js:1-130` | `PARTIAL` | Đã đồng bộ dữ liệu cưới cốt lõi, `theme_color`, `section_order`, `hidden_sections` và `focal_points`. Chưa kết xuất `visual_editor` (`global_style`, `instance_styles`, `image_overrides`, `overlays`, `instance_animations`) trên bản công khai và đang cho phép tham số `?embed=1` không xác thực nạp `editor-bridge.blade.php` trên URL công khai. |
| `ST-010` | Tương thích đồng thời Classic 10-Section & AI Curated 7-Section | `app/Services/CuratedInvitationService.php:28-36, 439-471`, `resources/views/client/invitations/*/template.blade.php` | `PARTIAL` | 4 mẫu gốc đã có `@if(!$isCuratedSeven)` và có mẫu riêng `mayhen-curated` (24 biến thể), nhưng `TemplateController::preview()` chưa truyền `isCuratedSeven` khi mở lại thiệp Curated trong Studio, `InvitationEditorController::create()` chưa lọc danh sách mục theo 7 section Curated, `vom-troi-may-ao-cuoi` mới ẩn 2/6 khối cuối, và `hac-bach-trao-duyen` chưa có nhánh `$isCuratedSeven`. |

---

## 3. Kiểm Kê Kiến Trúc Hiện Có Cần Tái Sử Dụng Tuyệt Đối

1. **Routes & Controllers (`routes/web.php:118-170`)**:
   - `GET /tao-thiep` -> `InvitationEditorController@create` (`app/Http/Controllers/Client/InvitationEditorController.php:50-207`) — có cơ chế ETag + `304 Not Modified` + **0 truy vấn SQL** khi cache ấm cho khách vãng lai (`tests/Feature/InvitationEditorPerformanceTest.php:53-120`).
   - `POST /tao-thiep` -> `InvitationEditorController@store` (`:209-407`).
   - `POST /tao-thiep/upload-asset` -> `InvitationEditorController@uploadAsset` (`:734-795`).
   - `POST /api/editor/autosave` -> `InvitationEditorController@autosave` (`:797-855`) -> `InvitationTemplateService::saveDraftWithLock()` (`:1145-1235`).
   - `GET /xem-thu/{slug}` -> `TemplateController@preview` (`app/Http/Controllers/Client/TemplateController.php:185-451`).
   - `GET /thiep/{slug}` -> `InvitationEditorController@show` (`:412-729`) + middleware `DetectCustomDomain` (`app/Http/Middleware/DetectCustomDomain.php:25-123`).
   - `POST /tai-khoan/thiep/{id}/thanh-tra-xuat-ban/xuat-ban` -> `PublishDiffController@publish` (`app/Http/Controllers/Client/PublishDiffController.php:55-78`).
2. **State, Undo/Redo & Autosave trên trình duyệt**:
   - `public/assets/client/js/editor/store.js` (`EditorStore`), `history.js` (`HistoryManager`), `autosaver.js` (`Autosaver` kiểm tra nghiêm ngặt `new_revision_id === sentRevision + 1` tại `tests/Unit/editor-autosaver.test.mjs:472-491`), `draft-recovery.js`, `template-switcher.js`, `theme-controller.js`.
3. **Cơ sở dữ liệu**:
   - Bảng `weddings` (`groom_name`, `bride_name`, `wedding_date`, `venue_name`, `venue_address`, `cover_photo_url`, `music_url`, cột JSON `extra_data`).
   - Bảng `invitations` (`template_id`, `slug`, `custom_config` JSON, `draft_config` JSON, `revision_id` integer, `is_published`, `published_at`, `expires_at`).
   - **Kết luận Schema DB**: Cột JSON `invitations.custom_config` và `invitations.draft_config` đã đủ năng lực lưu nhánh `visual_editor` chuẩn hóa phiên bản `v1` mà **không cần tạo bảng mới phá vỡ kiến trúc hiện hữu**.

---

## 4. Các Rủi Ro Bảo Mật, Đồng Bộ & Giao Diện Phát Hiện Khi Kiểm Kê (Cần Khắc Phục Trong V3)

### 4.1 Rủi ro Bảo mật & Lưu trữ (Backend)
1. **`BUG-SEC-01` — Lỗ hổng IDOR & Vượt hạn mức tại `uploadAsset` (`InvitationEditorController.php:734-795`)**:
   - Khi truyền `invitation_id`, hàm `Invitation::with('wedding.user')->find($invitationId)` không kiểm tra `$invitation->isOwner()` và `$invitation->isLocked()`, cho phép tài khoản khác dò hạn mức dung lượng hoặc tải tệp gắn vào ngữ cảnh thiệp của người khác; đồng thời đoạn kiểm tra quota bị lặp kép (`:745-759` và `:762-776`).
2. **`BUG-SEC-02` — Tạo thiệp trên mẫu không hoạt động hoặc vượt gói (`InvitationEditorController.php:215`)**:
   - `InvitationTemplate::findOrFail($templateId)` chưa lọc `where('is_active', true)` và chưa kiểm tra quyền sử dụng mẫu `pro` đối với khách chưa đăng nhập.
3. **`BUG-SEC-03` — CDN Google Fonts ngoài luồng & Tiêm CSS qua tên Font (`invitation.blade.php:97-133`)**:
   - Nạp `https://fonts.googleapis.com/css2?...` (vi phạm quy tắc 100% tài nguyên cục bộ) và nội suy trực tiếp `$headingFont`, `$bodyFont`, `$scriptFont` từ `extra_data` vào thẻ `<style>` mà chưa qua danh sách trắng (`allowlist`), đồng thời dòng `98` ghi đè biến `$invConfig` của dòng `6`.
4. **`BUG-SEC-04` — Lộ cầu nối `editor-bridge` trên thiệp công khai (`InvitationEditorController.php:502`, `invitation.blade.php:11, 487`)**:
   - Khách vãng lai truy cập `/thiep/{slug}?embed=1` vẫn kích hoạt `$isEmbed = true` và nạp toàn bộ `editor-bridge.blade.php`. Cần khóa chặt: chỉ nạp công cụ chỉnh sửa khi đang ở chế độ Studio hợp lệ (chủ sở hữu thiệp, khách đang soạn trên `/xem-thu/{slug}`, hoặc admin).
5. **`BUG-SEC-05` — Bộ dọn file mồ côi bỏ sót ảnh Visual Editor (`MediaLibraryService.php:192-290`, `WeddingStorageService.php:128-217`)**:
   - `detectOrphanedFiles()` chưa quét `invitations.custom_config` và `invitations.draft_config`. Nếu người dùng thêm ảnh trang trí (`overlay_image` / `overlay_frame`), công cụ dọn rác Admin sẽ nhận nhầm là tệp mồ côi và xóa mất ảnh.
6. **`BUG-SEC-06` — Nhân bản thiệp trùng ID phần tử trang trí (`AccountController.php:365-386`)**:
   - `duplicate()` sao chép nguyên văn `custom_config`, cần tái sinh `id` mới cho mảng `visual_editor.overlays` để tránh đụng độ định danh.

### 4.2 Rủi ro Cầu nối Iframe, Dữ liệu & Mẫu thiệp (Frontend & Blade)
7. **`BUG-SYNC-01` — Gửi đúp lệnh vào iframe & thiếu kiểm tra `origin` (`editor.js:514-536, 934-940`, `editor-bridge.blade.php:1288`)**:
   - `dispatchToIframes()` vừa gọi hàm trực tiếp trên `contentWindow` vừa bắn `postMessage(..., '*')` khiến mỗi thao tác cập nhật DOM chạy 2 lần liên tiếp và dùng wildcard `'*'`.
8. **`BUG-SYNC-02` — Xung đột lồng nhau `data-sync-target` làm mất thẻ con (`editor-bridge.blade.php:1114`)**:
   - Khi gán `el.textContent = displayVal` lên thẻ cha mang `data-sync-target` (`couple_names`, `calendar_month_year`, `event_party_address`, `footer_thank_you_text`), các thẻ `<span data-sync-target="...">` bên trong bị xóa sổ khỏi DOM tại `giua-muon-ngan-vi-sao:559,775`, `ngay-tinh-yeu-dom-hoa:979`, `tron-kiep-nay-ben-nhau:347,395,599,1353`, `hac-bach-trao-duyen:985`, `vom-troi-may-ao-cuoi:569`.
9. **`BUG-SYNC-03` — Lệch khóa dữ liệu & thiếu `data-sync-target` ở một số mẫu**:
   - `giua-muon-ngan-vi-sao:364,1337,1340`: `slogan`, `thank_you_title`, `thank_you_quote` đang hardcode tĩnh, chưa gắn `data-sync-target` và biến PHP.
   - `hanh-phuc-mang-ten-chung-minh`: `script.js:536` tìm nhầm `.mayhen_invitation_ladi_wraper` khiến nút gửi RSVP công khai báo lỗi; `:121` đọc `love_quote` thay vì `couple_quote`; `:363,693,712` lệch alias `couple_photo_url`, `groom_qr_url`, `bride_qr_url`.
   - `vom-troi-may-ao-cuoi`: thiếu `data-mayhen-section` trên `opening:262`, `signature:766`, `rsvp:891`, `wishes:978`; thiếu `@if(!$isCuratedSeven)` quanh `signature`, `rsvp`, `wishes`, `ending`; `ceremony_photo_url:606` và ảnh Polaroid chuyện tình chưa gắn `data-sync-target`; `<audio>` dùng thẻ `<source>` con (`:252`).
   - `hac-bach-trao-duyen`: 3 ảnh chuyện tình (`:611,623,635`) và 3 mốc timeline (`:543-582`) chưa gắn `data-sync-target`; `script.js:55` ghi đè tên khách mời SSR; chưa có nhánh `$isCuratedSeven`.
   - `mayhen-curated`: `sections/parents/variant_c.blade.php:22,32` gắn nhầm `data-sync-target="groom_father"` / `"bride_father"` vào dòng tên chú rể/cô dâu; `countdown/variant_b.blade.php` thiếu `data-sync-target`; `script.js` thiếu hàm cập nhật đếm ngược/lịch khi đổi ngày cưới trực tiếp.
   - `InvitationTemplateService::normalizeSchema()` (`:746-759`) đang xóa cứng các trường chữ ngân hàng (`groom_bank_name`, `groom_bank_account`, `bride_bank_name`, `bride_bank_account`), khiến người dùng không sửa được tên ngân hàng/số tài khoản khi mẫu hiển thị các trường này.
10. **`BUG-UI-01` — Vỡ bố cục thanh trên cùng Studio ở màn hình điện thoại `390px` (Phát hiện từ ảnh chụp `w00-baseline/studio-mobile-390.png`)**:
    - Trên `390×844`, cụm chuyển chế độ `Nhập Liệu | Xem Thiệp` (`.mayhen_editor_mode_switcher`) đè lên nút quay lại và nhãn tên mẫu thiệp; nút `Xuất Bản Thiệp` bị cắt mất chữ ở mép phải; thanh gợi ý dưới đáy đè lên thanh nổi di động. Sẽ được xử lý dứt điểm trong bố cục Studio V3.

---

## 5. Tài Nguyên Font Cục Bộ Có Sẵn Trong Kho Mã Nguồn

Dự án đã có sẵn các tệp font `.woff2`/`.ttf`/`.otf` hỗ trợ đầy đủ dấu tiếng Việt trong các thư mục mẫu thiệp:
- `resources/views/client/invitations/ngay-tinh-yeu-dom-hoa/assets/fonts/` (`8` tệp `.woff2` gồm Serif, Display, Script, Sans, Condensed).
- `resources/views/client/invitations/tron-kiep-nay-ben-nhau/assets/fonts/` (`5` tệp `.woff2` gồm Heading Serif, Body Sans, Script).
- `resources/views/client/invitations/vom-troi-may-ao-cuoi/assets/fonts/` (`34` tệp `.woff2` subset tiếng Việt/Latin cho `Philosopher`, `Lustria`, `Jura`, `Pattaya` + `w17-indulge-script.otf` + `w17-fleur-de-leah.ttf`).

-> Đủ điều kiện đóng gói **100% Local Font Registry** tại `public/assets/client/fonts/studio-v3/` và `public/assets/client/css/common/invitation-fonts-v3.css`, loại bỏ hoàn toàn sự phụ thuộc vào Google Fonts CDN bên ngoài.
