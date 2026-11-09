# MÂY HẸN — MASTER PROMPT V3: NATIVE TEMPLATE REUSE, ZERO NEW DESIGN, AUTHENTIC CURATED PRESENTATION

**NHIỆM VỤ KHẨN CẤP:** SỬA SAI KIẾN TRÚC HỆ THỐNG TẠO THIỆP TỰ ĐỘNG ĐÃ TRIỂN KHAI. KHÔNG PHẢI LÀM LẠI MỘT BỘ THIỆP MỚI.

**Thư mục dự án đang sử dụng (ưu tiên đường dẫn này, không lấy đường dẫn D: cũ từ tài liệu):**
`E:\Project\Laravel\mayhen.vn`

**Thư viện mẫu gốc bắt buộc phải khảo sát và sử dụng:**
`E:\Project\Laravel\mayhen.vn\resources\views\client\invitations`

**4 mẫu thiệp gốc nhìn thấy trong thư viện hiện tại:**
- `giua-muon-ngan-vi-sao`
- `hanh-phuc-mang-ten-chung-minh`
- `ngay-tinh-yeu-dom-hoa`
- `tron-kiep-nay-ben-nhau`

`mayhen-curated/` hiện là sản phẩm AI tự viết thêm, **không phải mẫu nguồn được phép dùng để hiển thị thiệp cuối cùng**. Các thư mục `partials/`, `layouts/` và mọi mẫu khác nếu xuất hiện phải được kiểm tra vai trò, không giả định là mẫu được phép chọn.

## A. LỜI TUYÊN BỐ Ý ĐỊNH — ĐỌC LẠI NẾU BẠN ĐỊNH TẠO HTML/CSS

Tôi đã có sẵn những mẫu thiệp cưới được thiết kế công phu, nghệ thuật và cao cấp. Tôi KHÔNG muốn bạn tự sáng tạo thêm bộ mẫu, thêm 21 variant, thêm một layout nhìn như đồ án sinh viên, hoặc tái thiết kế các section theo cảm nhận của bạn.

Tôi muốn **người dùng nhập thông tin → chọn phong cách và màu → hệ thống lựa chọn có kiểm soát 1 mẫu CÓ SẴN thực sự phù hợp → AI chỉ viết content → website render CHÍNH XÁC mẫu thật đó với dữ liệu cá nhân hóa → hiệu ứng làm cho các phần tử của mẫu thật xuất hiện dần từ trên xuống, trái sang phải.**

**Kết quả cuối cùng phải chính là chiếc thiệp nguyên bản đang có trong thư mục nguồn, chỉ khác nội dung, màu được hỗ trợ, số section hiển thị và hiệu ứng tiết lộ trong lần trình diễn đầu tiên.** Khi đã hoàn thành, mọi người mở thiệp đều thấy website mẫu thật, không có dấu vết của mẫu tự vẽ `mayhen-curated`.

Phải ưu tiên **sự trung thành với bản thiết kế gốc (visual fidelity)** hơn khả năng trộn ngẫu nhiên section. Nếu trộn section làm phá mẫu, **dừng trộn**. Ngẫu nhiên đúng nghĩa ở giai đoạn này là **chọn giữa các mẫu gốc phù hợp phong cách**, không phải lấy Hero của mẫu A ghép với Album của mẫu B.

## B. NGHIÊM CẤM

1. CẤM dùng HTML/Blade ở `resources/views/client/invitations/mayhen-curated/sections/*` làm nền thiết kế thiệp. Không tái chế chỉ bằng cách đổi màu/font/ảnh.
2. CẤM tạo template Blade mới chứa cấu trúc thiệp tự nghĩ ra; cấm `variant_a`, `variant_b`, `variant_c` dạng thiệp tự viết.
3. CẤM tạo thư viện 7 section mới để thay thế source. Mọi section phải được lấy từ mẫu thật tương ứng.
4. CẤM viết lại style chính của mẫu gốc, thay bố cục gốc bằng Grid chung, đặt ảnh polaroid tùy hứng, hoặc tạo hero/love story mới.
5. CẤM copy 7 section của 4 mẫu rồi paste vào một template 'mayhen-curated' khổng lồ nếu có thể render trực tiếp template nguồn; tránh bản sao CSS/asset dễ lệch.
6. CẤM sử dụng ảnh demo ở `mayhen-curated/assets` để thay asset của mẫu nguồn; ảnh cá nhân do người dùng upload có quyền ưu tiên.
7. CẤM lưu dữ liệu riêng của khách vào assets dùng chung trong `resources/views/client/invitations/{slug}/assets`.
8. CẤM 'fake AI generated design'. AI thật sự chỉ tạo chữ nếu API AI hoạt động; dựng giao diện là animation client của hệ thống trên mẫu đã thiết kế. Không tuyên bố AI tự thiết kế HTML/CSS.
9. CẤM tự phát animation mỗi lần refresh, mở tab khác, back/forward, vào Studio, xem thiệp public.
10. CẤM phát hành thiệp có 7 section nhưng lại là Dresscode ở vị trí số 7. Section thứ 7 trên trang curated là ALBUM 10 ẢNH (Section 8 theo skill).
11. CẤM xóa 4 mẫu nguồn, viết đè `template.blade.php`/`styles.css`/`script.js` của chúng bằng một layout chung, hoặc làm hỏng mẫu 10 section hiện có.
12. CẤM thay đổi bảng màu người dùng đã chọn sau khi phối lại nếu template hỗ trợ bảng màu đó; không áp CSS lên template theo cách phá texture, gradient, contrast/ornaments.
13. CẤM kết luận đạt chỉ vì Pest/HTTP 200 thành công. Phải chụp ảnh so sánh giao diện thật ở browser.
14. CẤM tự động downgrade sang theme `mayhen-curated` khi mẫu gốc không phù hợp. Trường hợp không tương thích phải xử lý rõ ràng và chọn mẫu gốc hợp lệ khác.
15. CẤM sửa tất cả mẫu một lượt nếu chưa chứng minh pipeline hoạt động trên **một mẫu gốc đầu tiên**; triển khai theo mức an toàn tăng dần.

## C. ĐỌC MÃ THỰC TẾ TRƯỚC KHI SỬA — KHÔNG TIN TUYÊN BỐ DONE

Đọc theo thứ tự:

1. `context.gemini.md` — nhất là nhật ký mục 223–227, các nội dung tương ứng Curated V1/V2. Cập nhật tài liệu sau khi code theo quy định.
2. `AGENTS.md`.
3. `.agents/skills/mayhen-invitation-standards/SKILL.md`.
4. `docs/MAY_HEN_AI_CURATED_7_SECTION_INVITATION_MASTER_PROMPT.md` nếu file có tồn tại.
5. `docs/MAY_HEN_CURATED_CRAFTING_V2_VISUAL_UX_FIX_MASTER_PROMPT.md` nếu có.
6. 4 thư mục mẫu gốc, **đọc từng `template.blade.php`, `styles.css`, `script.js`, `schema.json`, `README.md` nếu có, và cấu trúc assets**.
7. Toàn bộ code đã triển khai: `CuratedInvitationService.php`, `CuratedInvitationController.php`, `wizard.blade.php`, `preview.blade.php`, JS/CSS wizard và preview, seeder, routes và tests.
8. `InvitationEditorController`, `TemplateController`, `InvitationTemplateService`, `resources/views/client/layouts/invitation.blade.php`, `editor-bridge.blade.php` và nguồn hình thành `custom_config`.
9. Kiểm tra `git status` và diff trước khi chạm đến code; giữ nguyên các thay đổi không thuộc phần việc của bạn.

**Không giả định máy vẫn ở ổ D.** Đường dẫn do tôi đưa mới nhất là ổ E; hãy lấy repository hiện tại làm source of truth.

### Báo cáo khảo sát bắt buộc

Tạo tài liệu `docs/MAY_HEN_CURATED_NATIVE_TEMPLATE_AUDIT.md`, có bảng ít nhất:

| Mẫu gốc | View thật | CSS/JS thật | 7 section gắn selector nào | Section nào dùng chung wrapper | Asset nội bộ | Cách inject dữ liệu | Khả năng ẩn các section dư | Tương thích màu | Kết quả screenshot |
|---|---|---|---|---|---|---|---|---|---|

Không suy đoán selectors từ tên class chuẩn nếu code thực tế khác. Ghi file/đoạn code thực tế cho mỗi mapping. Xem xét các section gộp nhiều phần, overlay/opening, footer, lightbox, popup, âm nhạc và JS phụ thuộc section bị ẩn. Không được bỏ qua vì một mẫu có số lượng sections DOM khác nhau hoặc nhiều wrapper phụ.

## D. KIẾN TRÚC BẮT BUỘC: NATIVE TEMPLATE FIRST

### D1. Phân tách trách nhiệm

- `CuratedInvitationController`: quản lý wizard, tạo thiệp, quyền, kết quả và thao tác người dùng.
- `CuratedInvitationService`: chọn đúng template nguồn hợp lệ và phối thông tin, lưu dữ liệu, kiểm tra entitlements, không render HTML thủ công.
- `NativeTemplateRegistry` (tên có thể thay đổi): danh sách allowlist các template gốc, style mapping, metadata, khả năng hỗ trợ 7 section, palette support.
- `NativeCuratedProjection`/adapter (tên tùy chọn): chuyển chế độ render cùng một mẫu nguồn sang chế độ 7 section chỉ bằng scope hiển thị và dữ liệu; không dựng bố cục mới.
- `NativeCraftingPresenter`: một script hiệu ứng trình diễn dùng chung, **không sở hữu nội dung và không đổi layout**.
- Template gốc `template.blade.php` + `styles.css` + `script.js` + assets: **đây là nguồn duy nhất quyết định hình thức thiệp**.

Không buộc phải tạo đúng các tên class kể trên; ưu tiên tích hợp vào cấu trúc hiện có, code ngắn và dễ bảo trì.

### D2. Xác định template thực bằng ID/slug thực

- Đối với mỗi invitation mới, lưu `source_template_id` hoặc sử dụng trực tiếp `template_id` trỏ đến bản ghi `InvitationTemplate` của một trong 4 mẫu có thật.
- Nếu còn giữ `mayhen-curated` như một loại chế độ/sản phẩm, dùng `creation_mode = curated_native_v1` hoặc metadata riêng, **không dùng template_id `mayhen-curated` làm renderer cuối cùng**.
- Nếu schema cũ chỉ có `template_id`, cần thiết kế migration dữ liệu tương thích trước khi sửa, không phá link thiệp cũ; bảo đảm có thể phân biệt kiểu curated 7 section với template mặc định 10 section.
- Với cùng một invitation, `template_id`, `source_template_slug`/metadata, Studio edit, preview, public URL và API phải thống nhất cùng một mẫu.
- URL render public và preview phải đi qua cơ chế hiện hành đã sử dụng source template, không cần render biến thể lạ theo route riêng nếu có thể tái sử dụng.
- Với `mayhen-curated` legacy đã tạo, không xóa dữ liệu. Xây migration/adapter tương thích hoặc cơ chế người dùng chọn đổi sang mẫu native có xác nhận, không tự ý biến đổi ảnh/nội dung khi không mapping được.

### D3. Render đúng nguyên bản, không sao chép thiết kế

Ưu tiên **render `client.invitations.{sourceSlug}.template`**, truyền `wedding`, `invitation`, `isEmbed`, `isPreview`, `isCuratedSeven`, config thật và đúng những biến template cần. Kiểm chứng controller/layout đã làm thế nào ở `/xem-thu/{slug}`, `/tao-thiep`, `/thiep/{slug}` và sử dụng cùng pipeline.

Nếu cần điều kiện để chỉ render các section đã chọn, triển khai adapter rất mỏng ở mỗi mẫu nguồn hoặc ở layer chung, ví dụ điều kiện `$isCuratedSeven` tác động đến **việc có hiển thị section**, không tác động đến cấu trúc của 7 section còn lại. Một số mẫu có phần mở đầu/full-screen overlay, intro và footer riêng phải xem cụ thể: không phá nguyên bản bằng ẩn cả wrapper cha.

Nếu một section gốc bị gộp cứng với section không được chọn, cân nhắc chuyển đúng đoạn markup nguyên gốc thành partial dùng lại trong template nguồn để bật/tắt theo mode; **không viết thay thiết kế**. Việc tách partial phải được chụp ảnh before/after chứng minh chế độ 10 section không bị thay đổi.

Giữ:
- Font gốc, hình nền gốc, họa tiết, geometry, negative space, animation signature.
- Kích thước chữ, breakpoint, responsive, nét nhận diện của từng mẫu.
- Cách hiển thị ảnh, lightbox, âm nhạc, lịch và bản đồ khi đủ dữ liệu.
- Asset resolution qua hệ thống nội bộ của mẫu gốc.
- `data-sync-target` và `data-mayhen-section` contract khi Studio yêu cầu.
- Không bật interactive UI thừa, route hoặc request đến 3 section bị ẩn.

**Visual fidelity test**: cùng `sourceSlug` và cùng dữ liệu/cùng viewport, ảnh chụp các phần 1–6/8 của curated phải khớp thiết kế gốc ở phần tương ứng (trừ text/ảnh cá nhân/màu có chủ ý, việc loại bỏ section và giới hạn nội dung). Không đo tương đồng bằng ước lượng cảm tính: chụp hình so sánh side-by-side và kiểm tra phần lớn layout/typography/asset giữ nguyên, không vỡ trang vì section bị ẩn.

## E. EXACTLY 7 SECTIONS — KHÔNG NHẦM SỐ 7 VÀ SECTION 7 CỦA SKILL

**Thứ tự hiển thị trên trang curated** vs **số section gốc trong skill**:

1. Curated #1 = Skill SECTION 1 — **HERO COVER**.
2. Curated #2 = Skill SECTION 2 — **LOVE STORY & QUOTE**.
3. Curated #3 = Skill SECTION 3 — **FAMILY & COUPLE**.
4. Curated #4 = Skill SECTION 4 — **COUNTDOWN & CALENDAR**.
5. Curated #5 = Skill SECTION 5 — **EVENTS & MAPS**.
6. Curated #6 = Skill SECTION 6 — **WEDDING TIMELINE**.
7. Curated #7 = **Skill SECTION 8 — ALBUM 10 PHOTOS**.

**Không được đưa Skill SECTION 7 Dresscode, SECTION 9 Gift/VietQR, SECTION 10 RSVP/Guestbook/Signature/Ending vào phiên bản curated 7 section.** Không giữ chúng trong DOM dưới dạng `display:none` nếu vẫn tạo hành vi phụ không mong muốn: chủ động loại ở render branch khi an toàn, hoặc bảo đảm không khởi tạo scripts/handlers. Template chuẩn 10 section phải giữ hợp đồng riêng, không thay đổi global rule trong skill. Sửa tài liệu/validation để nhận rõ `curated_native_v1` như ngoại lệ trình bày 7 section, **không hạ số section tiêu chuẩn của 4 mẫu gốc**.

Đừng nhầm `section 7 của trang curated` với `section 7 của skill` một lần nữa.

Các template gốc có thể dùng `data-mayhen-section="couple"` cho hai phần; phải map đúng các khối DOM thực, không chỉ gọi một selector chung rồi làm biến mất phần Hero hoặc Story. Nếu một mẫu không hỗ trợ chính xác bảy phần mà không phá hình thức, đánh dấu `not_eligible` kèm bằng chứng; không tự chế section thay thế. Tối thiểu một mẫu thực sự hợp lệ phải chạy end-to-end trước khi mở rộng dần.

## F. CHỌN PHONG CÁCH VÀ MÀU SẮC ĐÚNG — KHÔNG RANDOM BỪA

### F1. Phong cách là danh mục do Mây Hẹn định nghĩa

Các style hiện hành trong context: `romantic`, `minimal`, `editorial`, `traditional`. Đây là ví dụ hiện trạng, không khẳng định mọi mẫu đều phù hợp hoặc đủ 4 style; hãy rà metadata/thẩm mỹ mẫu thật và xác định rõ cái nào tương ứng cái nào.

- Chỉ allowlist từ Admin/registry do Mây Hẹn kiểm soát; không có free-text style.
- Mỗi style có tập mẫu nguồn phù hợp sau khi art review, không trộn tùy tiện.
- Nếu style chưa có mẫu đạt, hiển thị thông báo và không cho chọn style đó trước khi xây thêm mẫu nguyên bản đủ tốt (ngoài phạm vi task hiện tại).
- Không dùng random trực tiếp trên tất cả bốn mẫu rồi gọi đó là matching style.

### F2. Palette do người dùng chủ động chọn

Mẫu gốc có thể có palette/token khác nhau; audit khả năng áp màu **không phá giao diện**. Dùng adapter token có kiểm soát ở cấp template (`--...` được whitelist), hoặc ảnh hưởng một số phần tử được thiết kế cho tùy biến, **không dùng generic blanket CSS** khiến texture/phông nền, ảnh, họa tiết mất nguyên bản. Nếu palette không tương thích với mẫu đó, loại mẫu khỏi tập chọn, hoặc báo lựa chọn không được hỗ trợ; không silently đổi màu và không hứa mọi màu sẽ hợp mọi mẫu.

Khi chọn/đổi màu, phải giữ nguyên source template và lưu màu bền vững. Khi bấm "Phối lại", chỉ chọn **một source template khác trong cùng style và tương thích palette**, render nguyên mẫu ấy; không shuffle 7 section độc lập. Nếu cùng style/palette chỉ có một mẫu hợp lệ, không trình bày nút random gây hiểu nhầm; đổi thành "Xem mẫu khác" (nếu có) hoặc thông báo rõ chưa có phương án khác.

## G. AI CHỈ VIẾT CONTENT — KHÔNG ĐƯỢC THIẾT KẾ

AI chỉ sinh dữ liệu có kiểm soát: `story_title`, `story_content`, `couple_quote`, có thể thêm `slogan`/`story_subtitle` nếu schema template hỗ trợ. JSON schema validation chặt chẽ; escape output; kiểm duyệt, giới hạn dài, không HTML/Markdown/raw CSS/JS.

- AI không được đổi mẫu, layout, palette, thứ tự section, font, animation, ảnh; không được xuất CSS class/HTML/JS/Blade.
- Không được bịa địa điểm, ngày giờ, cách hai người gặp nhau, năm yêu nhau, tên phụ huynh hoặc ảnh kỷ niệm.
- Ngân sách text mặc định: slogan khoảng 5–10 từ; quote 12–22 từ; lời ngỏ 35–55 từ; nếu nguồn mẫu gốc yêu cầu ngắn hơn thì ưu tiên text budget theo slot thực tế. Giới hạn này là ngân sách mỹ thuật, không ép văn bản quá dài vào khung.
- Có thể tái tạo chỉ content mà giữ toàn bộ template/style/palette/ảnh/event.
- Khi provider fail hoặc không cấu hình, dùng nội dung dự phòng đã biên tập, ghi đúng `content_source='editorial_fallback'`. Không giả API AI đang hoạt động.
- UI nói trung thực "AI gợi ý lời ngỏ · Mây Hẹn phối từ thiết kế tuyển chọn". Có thể trình diễn reveal như một thủ pháp nghệ thuật, nhưng không khẳng định AI tự vẽ bố cục khi thực tế không làm vậy.

## H. TRÌNH DIỄN 'DỰNG THIỆP' LÊN MẪU GỐC — KHÔNG THIẾT KẾ MỚI

**Chỉ là presentation layer** trên DOM đã render đúng từ `template.blade.php` nguồn.

### H1. Nguyên tắc

- DOM/CSS chính phải là của mẫu gốc. Trình diễn chỉ thêm data attributes/classes của một coordinator chung để lần lượt reveal các khối có sẵn. Không thay markup bằng "các dòng do JS dựng lại", không sao chép text vào overlay giả, không có video canvas tạo bản sao, không dùng fake loader nhiều giây.
- Thứ tự **trên xuống dưới** và trong cùng hàng **trái sang phải**, dựa vào layout thực tế sau khi fonts load; phần đã xuất hiện giữ nguyên; text reveal theo dòng thị giác khi khả thi an toàn, phần tử phức tạp theo block/hàng.
- Dùng native JS/CSS, Web Animations API hoặc IntersectionObserver nếu hữu ích. Thời gian phản ánh **progress presentation**, không giả tiến độ model.
- Lần đầu tạo thật mới có presentation token/permission do server quản lý; F5, revisit, open public, Studio edit = render ngay 100%; nút 'Xem lại quá trình' là client-only, không gọi AI/migrate/re-save.
- Cho 'Bỏ qua' và `prefers-reduced-motion: reduce`. Không khóa người dùng vào animation, không làm hỏng tương tác trong mẫu. Không làm biến dạng UI gốc vì animation.
- Animation không thể thêm opacity 0 mặc định khiến khi JS crash/disabled thì thiệp trắng. Progressive enhancement: nguồn SSR hiển thị bình thường mặc định, chỉ ẩn đơn vị sau khi coordinator sẵn sàng và one-time permission hợp lệ.
- Không để các template scripts tự kích hoạt opening/scroll lock khi `isEmbed` hoặc mode `curated_presentation` đã được xác thực. Trong curated playback có thể bypass overlay mở phong bì để reveal thiệp bên trong nhưng **không gỡ thiết kế/animation opening ở thiệp công khai** nếu người dùng đang xem bình thường.
- Khi chuyển thiết bị, không phát lại tự động. Khi kết thúc, dừng tại chỗ, không tự giật về đỉnh.
- Tốc độ đề xuất 15–25 giây tùy số đơn vị; không kéo dài giả tạo nếu ít content; các phần quá dài phải được rút trước. Khi người dùng tự scroll/touch, ngừng auto-scroll và không tranh quyền điều khiển.

### H2. Đầu tư vào trải nghiệm, không 'trẻ con'

- Không emoji lấp lánh, icon hoạt hình, đường scan neon hoặc 'AI vẽ từng nét' giả.
- Ưu tiên hiệu ứng giấy mỹ thuật: text mask nhẹ, ảnh reveal như mở tấm ảnh, viền tan từ trái sang phải, chuyển vùng có nhịp điệu, easing tinh tế, chừa thời gian để khách nhìn thành phẩm.
- Không thay đổi font/chữ/ảnh nền, nhịp khoảng trắng, lưới bố cục của template gốc.
- Khung preview nên đúng tỷ lệ thiết bị, không có giả Dynamic Island che nội dung, nút bên ngoài khung đủ gọn. Thu gọn panel chỉ giữ thông tin cần thiết.

## I. GIAO DIỆN WIZARD / PREVIEW

Wizard là **giao diện công cụ**, có thể tinh chỉnh để trang nhã, nhanh và dễ dùng; nhưng **đừng nhầm** với giao diện mẫu thiệp — mẫu thiệp cuối phải từ thư viện source.

- Bước 1 nhập tên chú rể/cô dâu; 4 trường phụ mẫu; có thể thêm ngày/giờ/địa điểm tùy chọn; nếu chưa có thì không bịa.
- Bước 2 chọn phong cách từ các style được phép với ảnh thumbnail thật trích xuất từ mẫu có thật; không dùng mockup do AI tự vẽ.
- Bước 3 chọn palette tương thích và xem sơ bộ thay đổi ngay trên **mẫu nguồn thực**.
- Bước 4 xác nhận trước khi tạo, giải thích AI chỉ gợi ý lời ngỏ và thiết kế dựa trên bộ sưu tập có sẵn.
- Không bắt người dùng nhập lại dữ liệu khi chọn mẫu khác. Lưu draft an toàn.
- Preview chính dùng iframe/native render đã load đúng source slug. Cạnh đó hiển thị "Mẫu nguồn: [tên mẫu]" cho người dùng và cả QA/dev kiểm chứng, không hiển thị `mayhen-curated` như tên mẫu nghệ thuật.
- CTA sau tạo: `Chỉnh sửa thiệp`, `Lưu/Xuất bản` (theo quyền hiện có), `Xem mẫu khác` nếu có, `Xem lại hiệu ứng`; tránh nút giả.

## J. GIỮ NGUYÊN HỢP ĐỒNG 10 SECTION CỦA MẪU GỐC VÀ TƯƠNG THÍCH STUDIO

- `.agents/skills/mayhen-invitation-standards/SKILL.md` quy định mẫu gốc 10 section và ký tên/RSVP. Đây vẫn là contract cho **mẫu chuẩn**.
- `curated_native_v1` là **chế độ hiển thị riêng** có projection 7 section; không sửa skill để khẳng định mọi mẫu chỉ cần 7.
- Bảo đảm nguồn 10 section vẫn đủ nội dung và interactive trên đường `/xem-thu/{slug}` thông thường và các thiệp đã xuất bản.
- `curated_native_v1` sau khi lưu, edit qua Studio phải giữ `source_template`, `curated_mode`, 7 section, ảnh, màu, dữ liệu, revision. Nếu Studio V2 mặc định dựng lại đủ 10 section, phải sửa tích hợp để đọc đúng curated projection, không tự bật các phần bị loại.
- Nếu người dùng chọn "Chuyển sang thiệp đầy đủ 10 section" trong Studio ở tương lai, đây là thao tác chủ động có xác nhận, không tự xảy ra khi edit/publish.
- `data-sync-target` cập nhật mọi nơi, `editor-bridge` handshake, không gửi postMessage sang origin không hợp lệ.
- Album bắt buộc 10 slots trong curated; nếu chưa upload, sử dụng đúng fallback hợp lệ của source template khi đã kiểm tra license/privacy, hoặc placeholder phi cá nhân không phải cặp đôi lạ. Không lộ ảnh khách A sang khách B.
- Khi lưu, dữ liệu cưới thuộc wedding; media upload per wedding; không ghi vào template assets chung.

## K. TIN CẬY, BẢO MẬT, QUYỀN VÀ TÍNH ỔN ĐỊNH

- Kiểm tra route preview có thể bị tham số `sourceSlug` giả mạo để truy cập mẫu Pro mà chưa mua không; backend validate registry/tier/ownership.
- Không cho người dùng sửa `template_id`/`source_template_slug` tùy ý để vượt gói; không cho user A lấy metadata, ảnh hoặc draft của B.
- Check CSRF, authorization, XSS từ generated content, asset path traversal, URL ảnh và data sync.
- Việc tạo lại/phối lại phải idempotent, không tạo double invitation khi click đúp.
- Lock/revision cho autosave/Studio, không mất nội dung khi back/refresh/2 tabs.
- Nếu 1 mẫu source bị xóa/khóa sau khi khách đã tạo thiệp, nêu chiến lược version/pin hoặc bảo vệ availability; không tự redirect sang mẫu khác.
- Không sửa production hay xóa dữ liệu thật; dùng fixture/storage giả và migration có rollback, backup an toàn.

## L. CHUYỂN ĐỔI LEGACY `mayhen-curated` KHÔNG LÀM MẤT THIỆP

1. Kiểm tra thiệp nào đang tham chiếu `mayhen-curated`, dữ liệu content, album, configuration, trạng thái thanh toán và quyền sở hữu.
2. Không delete thư mục/theme `mayhen-curated` ngay khi vẫn còn bản ghi phụ thuộc; cần có strategy chuyển đổi chủ động/rollback hoặc adapter legacy riêng.
3. Với **thiệp tạo mới**, tuyệt đối không trỏ renderer về `mayhen-curated` — đây là yêu cầu hoàn thành quan trọng nhất.
4. Với **thiệp cũ**, cho đọc/xem và chỉnh sửa an toàn hoặc hỗ trợ quy trình nâng cấp có xác nhận; giữ bản gốc đến khi người dùng đã duyệt mẫu native. Không âm thầm đổi diện mạo thiệp đã xuất bản.
5. Sau khi không còn dependency, có thể chuyển `sections/*/variant_*.blade.php` vào chế độ deprecated/không route. Chỉ xóa khi có bằng chứng không còn thiệp nào cần và không làm mất history.

## M. TEST & VISUAL PROOF — KHÔNG ĐƯỢC NÓI 100% NẾU CHƯA XEM ẢNH THẬT

### M1. Unit/Feature/Integration

Viết test tại `tests/Feature/` và `tests/Unit/` theo chuẩn dự án, tối thiểu:

1. Tạo thiệp curated `template_id` trỏ về một trong bốn source template thật, **không trỏ `mayhen-curated`**.
2. HTML phản hồi có asset paths/class đặc thù source template; không có lớp section `variant_a|b|c` từ renderer tự viết.
3. 7 section được hiển thị đúng 1,2,3,4,5,6,8; không xuất hiện 7,9,10 trong curated.
4. Không làm thay đổi 10-section pages thông thường.
5. Style whitelist chỉ chọn mẫu hợp lệ, palette compatibility đúng, không đổi palette đã chọn.
6. Phối lại nếu có nhiều source cùng style đổi **cả source template** chứ không trộn module.
7. Cùng seed/config và cùng invitation -> cùng template khi F5, không random lại.
8. AI provider chỉ trả content; đầu ra có HTML/style tags bị reject/sanitize.
9. Fallback không được báo "AI vừa thiết kế".
10. Sau edit, Studio/public/preview dùng cùng source + content + palette + dữ liệu 7 section.
11. Album 10 ảnh đúng slot, thứ tự giữ nguyên, không lộ ảnh của user khác.
12. Không có date/address, không sinh countdown/map giả.
13. Pro template không được bypass bằng sửa request `source_template_id`.
14. Legacy curated vẫn xem được nếu còn dữ liệu.
15. Double submit, token presentation replay, F5 giữa chừng, 2 tab, revoke ownership được xử lý.

### M2. Browser real-world visual acceptance

- Chạy desktop 1440/1366, tablet 768, mobile 430/390/375/320 và nếu có Safari/Firefox test thêm.
- Chụp ảnh **trực tiếp** `/xem-thu/{sourceSlug}` (chế độ thông thường) và curated preview sử dụng cùng source, cùng dữ liệu.
- Duyệt từng section bằng mắt, đánh dấu pass/fail: visual hierarchy, font, ảnh, decorative assets, cropping, spacing, responsiveness.
- Kiểm tra opening overlay, nhạc, lightbox, countdown, map, event timeline, không gây JS errors khi bỏ 3 sections.
- Chạy video hoặc recording màn hình **thật** lần tạo mới, xác minh hiệu ứng xuất hiện từng dòng/từng hàng trên DOM mẫu gốc, không phải một bản sao.
- F5 khi animation 30%, F5 sau animation xong, public URL, Studio editing: render hoàn chỉnh tức thì.
- Visual compare không thể dùng pixel-perfect một cách máy móc với dynamic dates/animation, nhưng layout motif, styling và geometry phải có bằng chứng không bị thiết kế lại.

### M3. Bằng chứng runtime không thể giả

Trên chế độ dev/test được phép, expose an toàn `data-curated-source-template="{slug}"` hoặc log debug server không lộ PII, và trong báo cáo ghi:
- `invitation_id` test/fixture;
- `template_id` thực;
- `source_template_slug`;
- view Laravel thực được render;
- đường dẫn CSS/JS/asset đã nạp;
- số section hiển thị;
- ảnh screenshot source và curated;
- kết quả browser network (không 404), console (không error);
- test command + pass/fail;
- mọi hạng mục chưa xác minh.

**Nếu source được render không phải 1 trong 4 template gốc, task FAIL ngay, bất chấp 599/599 test xanh.**

## N. THỨ TỰ THỰC HIỆN — PHẢI CODE THẬT, KHÔNG CHỈ BÁO CÁO

**P0.** Audit code, xác định renderer gốc vs renderer sai. Kiểm chứng hiện tại thực sự dùng `mayhen-curated/sections/` từ service/view ở đâu. Ghi bằng chứng file + code path.

**P1.** Dựng end-to-end trên **một template thật** trước (chọn mẫu có đủ 7 section và mapping rõ nhất). Sử dụng HTML/CSS/JS nguyên bản; sửa liên kết data; lọc đủ đúng 7 section; test screenshot fidelity.

**P2.** Xây registry/adapter những mẫu còn lại sau khi đã audit. Mẫu nào chưa đủ điều kiện thì không đưa vào random.

**P3.** Chuyển giao diện wizard cho chọn style/thumbnail thật, màu hỗ trợ, log tên source; random chỉ giữa các template nguồn phù hợp.

**P4.** Gắn fine-grained crafting presenter lên DOM nguồn, one-time presentation, skip/replay, reduced motion; không thay đổi logic render.

**P5.** Cập nhật Studio/public/preview/persistence/legacy; test quyền, tier, dữ liệu, mobile và cross-browser.

**P6.** Dọn code không còn dùng sau khi chắc chắn không còn dữ liệu phụ thuộc; test regression, cập nhật tài liệu, chuẩn bị commit message.

Nếu gặp blockers nghiêm trọng, ghi đúng chi tiết và tiếp tục task độc lập an toàn. Không mặc định thêm API, migration, thư viện nếu không cần. Không tự động commit hoặc push.

## O. DEFINITION OF DONE — 16 CỬA KIỂM ĐỊNH

Task chỉ DONE khi đầy đủ tất cả điểm sau:

- [ ] Tôi có bảng audit đầy đủ 4 mẫu gốc, selector/asset và ảnh thực của từng mẫu.
- [ ] Tạo mới dùng source thực thay vì `mayhen-curated`.
- [ ] Có bằng chứng view source thực là Laravel view render.
- [ ] Có 7 section chính xác 1,2,3,4,5,6,8.
- [ ] Đã đối chiếu visual source-vs-curated, không thay thiết kế source.
- [ ] Các mẫu gốc ở chế độ thường vẫn giữ 10 section đầy đủ.
- [ ] Phong cách do Mây Hẹn định nghĩa, không nhập tùy tiện.
- [ ] Lựa chọn source phù hợp style thật; không trộn section A/B/C.
- [ ] Màu do khách chọn và được áp đúng trên mẫu hỗ trợ.
- [ ] AI chỉ sinh chữ, giới hạn độ dài, có fallback trung thực.
- [ ] Trình diễn lần đầu trên DOM thật theo từng dòng/hàng, không tự chạy sau F5.
- [ ] Link public/Studio/preview cho cùng một bản thiệp có nội dung/hình thức nhất quán.
- [ ] Album 10 ảnh đủ slot; ảnh khách không lưu vào shared template assets.
- [ ] Authorization/tier/data isolation và legacy data được bảo vệ.
- [ ] Test, screenshots và browser recording đã chạy thật, không dùng log cũ làm bằng chứng mới.
- [ ] `context.gemini.md` và docs được cập nhật, có commit tiếng Việt có dấu và không tự push.

## P. BÁO CÁO CUỐI PHIÊN — FORMAT BẮT BUỘC

1. **Kiến trúc sai cũ là gì?** Ghi code path chính xác (`mayhen-curated` render ở đâu).
2. **Mẫu nguồn đã chọn để test** + vì sao.
3. **View thật được render** + `template_id`/slug thật.
4. **Cách giữ nguyên CSS/JS/asset** của template nguồn.
5. **Bản đồ 7 section** + mapping chính xác vào source markup.
6. **UI trước/sau** (screenshot path, viewport).
7. **AI sinh content như thế nào**, fallback khi fail.
8. **Bảo toàn Studio/public/legacy/10-section**.
9. **Test đã chạy thật**, pass/fail, các giới hạn.
10. **Danh sách file đã sửa** và nội dung commit tiếng Việt có dấu.

### LỆNH BẮT ĐẦU

**BẮT ĐẦU NGAY TỪ VIỆC MỞ THƯ MỤC `E:\Project\Laravel\mayhen.vn\resources\views\client\invitations` VÀ ĐỌC ĐÚNG CODE CỦA TỪNG MẪU GỐC. KHÔNG VIẾT TIẾP MỘT COMPONENT THIỆP NÀO TRONG `mayhen-curated/sections/`. HÃY CHỨNG MINH TRÊN TRÌNH DUYỆT RẰNG THIỆP AUTO-GENERATED ĐƯỢC RENDER BẰNG NGUYÊN MẪU THẬT CỦA MÂY HẸN.**
