# MASTER PROMPT — MÂY HẸN STUDIO INTERACTIVE PRODUCT TOUR

## 0. Nhiệm vụ và phạm vi tuyệt đối

Bạn là Principal UX Engineer, Senior Laravel/Blade Developer, Vanilla JavaScript Architect, Accessibility QA và Product Onboarding Designer của **Mây Hẹn — mayhen.vn**. Hãy **khảo sát, lập kế hoạch ngắn gọn, triển khai thật, kiểm thử, sửa lỗi và bàn giao** chức năng **Hướng dẫn tương tác lần đầu (Interactive Product Tour / Guided Walkthrough)** chỉ dành riêng cho trang **`/tao-thiep`** và các trạng thái con thực sự thuộc cùng Studio của route đó.

Tôi muốn người lần đầu mở công cụ tạo thiệp được chỉ dẫn nhẹ nhàng ngay trên giao diện thực: vùng đang được giới thiệu nổi bật trong spotlight, xung quanh tối nhẹ, một popover xinh xắn giải thích ngắn gọn bằng tiếng Việt, có **Tiếp**, **Quay lại**, **Bỏ qua**, tiến độ; sau khi đã xem xong hoặc bỏ qua thì **không tự bật lại**, nhưng có nút **Hướng dẫn** cố định trong UI để khách chủ động xem lại bất cứ lúc nào.

**KHÔNG áp dụng cho `/admin`, `/admin/docs`, `/docs`, tài khoản, quản lý khách mời, trang xem thiệp công khai hay bất cứ route nào khác. KHÔNG xây dựng tour chung toàn website.** Không thay đổi logic nghiệp vụ, nội dung cưới, mẫu thiệp hay kiến trúc Studio để phục vụ tour.

Đối với source đang chạy khác bản trước, đọc source hiện hành và điều chỉnh selector, số bước, tên nút cho đúng. Không suy diễn giao diện từ ảnh tham khảo là giao diện hiện tại của Mây Hẹn.

## 1. Đọc source và quy chuẩn trước khi sửa

Đầu tiên phải đọc thật kỹ, theo thứ tự:

- `context.gemini.md`, `AGENTS.md`, `CLAUDE.md`, các rules và skill liên quan.
- `.agents/skills/mayhen-visual-editor/SKILL.md`.
- `.agents/skills/mayhen-invitation-standards/SKILL.md`.
- `.agents/skills/mayhen-javascript/SKILL.md`, `.agents/skills/mayhen-css/SKILL.md`, `.agents/skills/mayhen-ui-ux/SKILL.md`, `.agents/skills/mayhen-testing-quality/SKILL.md` và animation/accessibility nếu có.
- Routes thực tế liên quan đến `/tao-thiep`; Blade của Studio, layout, CSS/JS liên quan; toàn bộ rail, stepper, panel, form, tooltip, iframe preview, modal, notification, actions.
- `public/assets/client/js/pages/editor.js`, `public/assets/client/js/editor/store.js`, `autosaver.js`, `history.js`, `actions.js`, các module thực tế tương ứng và `resources/views/client/invitations/partials/editor-bridge.blade.php` nếu đang được dùng.
- Các template/schema hiện có để hiểu 10 section, vùng preview, `data-sync-key`, `data-sync-target`, những trường phụ thuộc nhau và chế độ AI Curated nếu Studio dùng chung.
- Các test Studio hiện có. Lập **inventory** toàn bộ controls thật trước khi viết bước hướng dẫn.

Studio gốc đã dùng một `EditorStore`, autosave, undo/redo, iframe và sự kiện `MAYHEN_BRIDGE_READY`, `MAYHEN_SYNC_INPUT`, `MAYHEN_SCROLL_TO_SECTION`, `MAYHEN_SCROLL_TO_TARGET`. **Tour chỉ được điều khiển UI và spotlight; không dispatch action thay đổi wedding/custom_config để giả lập thao tác.** Tận dụng module/DOM thật, không nhân bản store hoặc cơ chế autosave.

Cấm thư viện mới: React, Vue, Alpine, jQuery, Tailwind, Bootstrap, Intro.js, Driver.js, Shepherd.js, Tippy, GSAP, Fabric, Konva, v.v. Chỉ Laravel Blade + CSS thuần + Vanilla JS ES Modules; class mới prefix `mayhen_editor_tour_*`.

## 2. Trải nghiệm chính xác mà khách phải nhìn thấy

### 2.1. Tự mở lần đầu

- Chỉ khi trang `/tao-thiep` render và Studio **sẵn sàng thật** (DOM, store, các toolbar/stepper cần thiết đã mount, và iframe ready khi tour cần preview).
- Chỉ tự mở với người **chưa hoàn tất cũng chưa bỏ qua** tour phiên bản hiện tại. Không chớp flash overlay khi page đang load, không block trường nhập liệu trước khi sẵn sàng.
- Khi load có draft recovery, conflict modal, thông báo lỗi nghiêm trọng, cropper modal hay hộp thoại bắt buộc, **ưu tiên xử lý hộp thoại ấy**, tour tự dời sau và không đè lên.
- Không tự mở khi link có anchor hoặc context đặc biệt cần ưu tiên tác vụ đang làm (quy tắc cụ thể phải xác minh từ source). Không tự bật ở trang preview công khai.
- Nếu bị gián đoạn (reload/chuyển trang) trước khi kết thúc, không đánh dấu hoàn thành. Có thể lưu bước đang dở để tiếp tục lần sau; phải ghi rõ hành vi và không tạo vòng lặp gây phiền.

### 2.2. Bố cục tương tác

- Overlay đen than/nâu trầm mờ vừa phải; **lỗ spotlight thật, trong suốt** quanh mục tiêu với viền champagne mảnh và glow rất nhẹ, bo góc theo phần tử; không tạo vệt màu đỏ, animation lòe loẹt hay shadow quá nặng.
- Spotlight chính xác quanh phần tử **thực sự có trên trang**, cập nhật khi scroll, resize, zoom, thay đổi sidebar, font tải xong, orientation, `visualViewport` và DOM layout shift.
- Popover màu giấy trắng ngà, typography rõ, desktop tối đa khoảng 320–380px, mobile vừa viewport, có khoảng cách đủ để không che phần tử, tự lật vị trí theo không gian còn lại; ưu tiên cạnh bên rồi trên/dưới, không overflow, không bị cắt bởi `overflow: hidden`, không nằm dưới header/modal.
- Chỉ tối đa 1–3 câu ngắn cho mỗi bước: **tên chức năng → người dùng làm được gì → thao tác ở đâu**. Tránh từ kỹ thuật, marketing màu mè hoặc nói như robot. Văn phong tự nhiên, thân thiện, dễ hiểu với người không biết công nghệ.
- Hiển thị `Bước x / N` đúng số bước đang hợp lệ (không hardcode “21” khi số bước thay đổi), thanh tiến độ, nút `Quay lại`, `Tiếp`/`Hoàn tất`, `Bỏ qua`. Bước đầu `Quay lại` ẩn/disabled có chủ đích. Có icon đóng X nhưng hành vi giống Bỏ qua và phải lưu.
- Tiến tới bước cuối có màn kết rất gọn: “Bạn đã sẵn sàng tạo thiệp của riêng mình”, nút `Bắt đầu tạo thiệp`. Kết thúc thì focus được trả về vị trí hợp lý.
- Khi tour đang chạy, có thể cuộn để tìm mục tiêu. **Không bắt người dùng bấm thật vào tính năng để qua bước.** Nếu người dùng click ngoài, không tự thực thi hành động nguy hiểm, không đóng vô cớ.
- Scroll mượt nhưng có tính toán sticky header; hỗ trợ nested scroll container và iframe khi cần. Không giật khung khi bấm Quay lại/Tiếp, không vô tình thay đổi ngày, tên, ảnh, màu, nhạc, mẫu thiệp hoặc xuất bản.
- Mục tiêu ẩn do rail/accordion/tab: dùng cơ chế UI hiện có để **mở đúng panel khi giải thích**, không tự set nội dung nghiệp vụ; lưu trạng thái rail/panel/scroll trước tour và khôi phục có cân nhắc khi đóng.
- Nếu phần tử biến mất/không tồn tại, không tạo `null` crash, không spotlight ra ngoài màn hình; bỏ qua bước đó có log debug và cập nhật tổng số bước chính xác. Không gắn tên/selector giả.

### 2.3. Trở lại xem hướng dẫn

- Thêm một nút rõ ràng tên **“Hướng dẫn”** (icon `?` hoặc quy ước hiện hữu) vào vị trí dễ tìm của Studio, không phá thanh công cụ và responsive. Nút này **luôn hiển thị**, kể cả người chưa/từng xem; click mở tour từ bước đầu theo chủ đích của họ.
- Có tooltip `Xem hướng dẫn sử dụng Studio` và trạng thái keyboard focus; không đổi tên thành “Trợ lý AI”, vì đây là tutorial UI có sẵn chứ không phải AI.
- Khi tự mở lần đầu hoặc người dùng mở thủ công, UI xử lý nhất quán. Xem lại không xóa dữ liệu thiệp, không reset trạng thái đã xem.

## 3. Phạm vi hướng dẫn phải được kiểm kê theo source thật, không bỏ sót nút quan trọng

**Đầu tiên lập bản đồ `TourStep -> DOM selector -> module/feature -> lời hướng dẫn -> điều kiện hiển thị -> trạng thái UI cần mở -> cách test`.** Sau đó xây dựng một tour chính đủ bao quát nhưng không ngột ngạt; có thể chia các cụm nhỏ với câu chuyển tiếp, song vẫn một chuỗi Tiếp/Quay lại/Bỏ qua. Mỗi chức năng thực sự có trong Studio phải được đề cập ít nhất trong một bước trực tiếp hoặc trong hướng dẫn cho nhóm chức năng tương ứng. Không tạo 50 popover chỉ vì trang có 50 input tương tự nhau.

Các nhóm bắt buộc **nếu code hiện tại có**:

1. Giới thiệu vùng tạo thiệp: trung tâm chỉnh sửa và khu xem trước; đây là hướng dẫn, không phải thay đổi thiết kế.
2. Thanh trên cùng: tên thiệp / tên dự án / về trang quản lý / tình trạng lưu / undo-redo / đổi mẫu / xem trước / nút xuất bản nếu có.
3. Thanh điều hướng/stepper và cách qua lại các phần của thiệp; giải thích có 10 section theo quy chuẩn nếu là mẫu tiêu chuẩn, không tự gán 10 section cho profile 7 section.
4. Section trang bìa: tên cô dâu/chú rể, loại lễ, ảnh bìa, câu mở đầu. Có thể dừng ở cả input và phần hiển thị tương ứng trên preview.
5. Section câu chuyện: ảnh kỷ niệm, lời ngỏ, tiêu đề, AI viết nội dung nếu có.
6. Section gia đình: tên cha mẹ hai bên, địa chỉ tư gia, ảnh chân dung và giới thiệu.
7. Section đếm ngược/ngày cưới: ngày dương, âm lịch tự tính, giờ; nói rõ dữ liệu liên quan được đồng bộ.
8. Section sự kiện và địa điểm: giờ, địa chỉ, bản đồ, các buổi lễ theo dữ liệu thật.
9. Section timeline: thêm/sửa mốc, thứ tự, tác động hiển thị.
10. Section dresscode: bảng màu và ghi chú trang phục, nếu mẫu cho phép.
11. Section album: thêm/thay và sắp xếp ảnh, nếu có.
12. Section quà cưới/VietQR: ngân hàng, số tài khoản, QR; lưu ý kiểm tra thông tin trước khi gửi, không để lộ thông tin nhạy cảm trong text tour.
13. Section RSVP, lời chúc, lời cảm ơn: xác định đúng phần chỉnh được và phần do hệ thống xử lý; không hướng dẫn click vào form khách mời trong iframe như thể đang điền hộ khách.
14. Các nút chọn/cắt ảnh, thư viện media, quy định ảnh; chỉ mở modal thật trong tour khi hỗ trợ an toàn, không tự upload file.
15. Tab bố cục/sắp xếp section, ẩn/hiện section nếu có; bảo vệ phần bất biến.
16. Tab màu sắc/theme, typography, font nếu có và cấu hình hợp lệ; không hướng dẫn tính năng chưa triển khai.
17. Tab nhạc nền: nghe thử/chọn bài, hành vi âm thanh cần gesture của người dùng; **không tự phát nhạc** trong tour.
18. Preview mobile/desktop, nút mở preview toàn màn hình; giải thích đúng chỗ sẽ xuất hiện trên thiệp đã xuất bản.
19. Trạng thái tự lưu, ngoại tuyến, xung đột, hoàn tác/làm lại, khôi phục bản nháp; không giả lập lỗi để minh họa.
20. Nút kiểm tra chất lượng trước xuất bản nếu có; cuối tour dẫn khách về hành động bắt đầu chỉnh sửa.
21. Nếu Studio Visual Editor V3 đã có chức năng click-to-edit, overlay, sticker, hình dạng, animation, layer, font từng phần tử thì kiểm kê và hướng dẫn **tính năng thực sự có**; nếu chưa có thì tuyệt đối không tạo nút giả hay hướng dẫn tính năng tương lai.

Không đọc toàn bộ chức năng bằng liệt kê máy móc trong một popup. Nhóm các công cụ cùng loại vào bước có thể hiểu và dùng ngay. Ở mobile, giảm bước không thể hiển thị và cho người dùng tiếp cận hướng dẫn qua drawer thật.

## 4. Kiến trúc kỹ thuật gợi ý — tùy chỉnh theo repo thật

Dùng một module nhỏ độc lập, ví dụ `public/assets/client/js/editor/product-tour.js`, CSS `public/assets/client/css/pages/editor-tour.css` hoặc theo cấu trúc assets thật, có thể chia thêm config data theo quy ước hiện hành. Không nhét toàn bộ logic vào Blade và không sửa `editor.js` quá nhiều chỉ để chạy tour.

Các khối trách nhiệm:

- `TourRegistry`: `id`, `title`, `description`, `target` (selector hay hàm tìm DOM an toàn), `section`, `prepare()`, `isAvailable()`, `cleanup()`; dùng mô tả đúng ngữ cảnh thật.
- `TourController`: state `idle | preparing | active | completed | skipped | paused`, current step id; `start({ manual })`, `next()`, `previous()`, `skip()`, `finish()`, `destroy()`; ngăn start trùng và click quá nhanh.
- `SpotlightPositioner`: đo `getBoundingClientRect()`, clip/mask cutout, kiểm tra vùng hiển thị, tính vị trí popover, fallback mobile và tự cập nhật có throttle `requestAnimationFrame`.
- `TourPersistence`: chỉ lưu trạng thái tour (không bao giờ lưu tên/ảnh/nội dung cưới vào key tour); phiên bản, `completed`/`skipped`, thời điểm; an toàn nếu LocalStorage bị chặn.
- `TourA11y`: focus trap hợp lý trong popover, `role="dialog"`, `aria-modal`, aria-labelledby/describedby, keyboard `Enter`, `Shift+Tab`, `Tab`, `Escape`, focus restore, trợ năng đọc màn hình; không khóa tương tác quá mức. Nếu dùng các vai trò ARIA, phải kiểm tra hành vi thực với screen reader.

Bắt đầu tour từ DOM đã sẵn sàng; có thể dùng event sẵn có của editor; không để CSS/JS tour load tại route khác. Tuyệt đối tránh `innerHTML` đưa vào các chuỗi không được tin cậy và selectors do user điều khiển.

### 4.1. Lưu trạng thái lần đầu

- Dùng khóa rõ namespace/phiên bản như `mayhen:editor:product-tour:v1`; nếu có định danh account đã có sẵn và đúng privacy rules, thiết kế per-account hoặc per-browser có lý giải, tránh trải nghiệm “người dùng khác cùng máy bị bỏ mất hướng dẫn”. Không bắt buộc thêm bảng database hay API chỉ để ghi một boolean.
- Chỉ ghi `completed` sau nút cuối, `skipped` sau X/Bỏ qua. Cả hai trạng thái đều chặn tự mở ở lần sau nhưng không chặn nút mở lại thủ công.
- Thay đổi văn bản/căn lề nhỏ không được tự reset tour; thay đổi lớn cấu trúc có thể tăng major version có kiểm soát.
- Nếu lưu thất bại, tour vẫn đóng được; session fallback hợp lý; không loop tự mở trong cùng một phiên, không crash.

### 4.2. Iframe và preview

- Preview hiện dùng iframe + `postMessage` cho Studio. Tour không được tạo iframe giả hoặc chỉnh trực tiếp nội dung thiệp qua uncontrolled DOM mutation.
- Khi mục tiêu thuộc iframe, ưu tiên hướng dẫn **khung preview** ở cấp parent; khi thật sự cần spotlight phần tử trong iframe, triển khai cầu nối chỉ đọc để iframe trả về tọa độ mục tiêu quy chiếu với viewport parent và báo thay đổi khi scroll. Phải kiểm tra `event.origin`, `event.source`, type allowlist và tránh `postMessage('*')` cho message mới chứa thông tin nhạy cảm.
- Spotlight xuyên iframe cần xử lý scrollTop bên iframe, rect iframe, CSS zoom/scale, và khi parent/iframe scroll hoặc bị resize. Nếu không thể định vị đáng tin cậy thì hướng dẫn vùng preview thay vì vẽ một spotlight sai.
- Không truyền hoặc hiển thị dữ liệu riêng tư vào tour payload/log. Không ghi `MAYHEN_SYNC_INPUT` để minh họa.

### 4.3. Scroll/DOM lifecycle

- Chỉ dựa vào selector có thật; ưu tiên `data-mayhen-tour="..."` gắn vào phần tử hiện hữu khi selector không ổn định, không phụ thuộc CSS class chỉ để trang trí.
- Dùng `scrollIntoView` có kiểm soát với `scroll-margin-top` và scroll container; không can thiệp body khóa cuộn gây xung đột iframe/modal.
- Mọi `ResizeObserver`, `MutationObserver`, global listeners, `requestAnimationFrame`, timeouts phải được cleanup khi dừng hoặc rời trang.
- Trạng thái đang gõ tiếng Việt bằng IME, focus input, selection/caret không bị thay đổi; không tự gán `input.value`, không trigger `input`/`change` trong quá trình tour.
- `prefers-reduced-motion: reduce` tắt chuyển động không thiết yếu; không flash màn hình; tôn trọng phóng to trình duyệt 200%.

## 5. UI/UX phải trông như một tính năng thương mại chỉn chu

- Cùng design tokens Mây Hẹn hiện hành: Quiet Luxury, trắng ngà/kem, đỏ rượu hoặc nâu trầm theo UI gốc, champagne gold làm nhấn, border mảnh, radius vừa phải, shadow nhẹ; tuyệt đối không sao chép thiết kế/trademark đối thủ.
- Tiếng Việt thuần dễ hiểu; ví dụ tốt: `Đây là nơi bạn đổi ảnh bìa thiệp. Bấm chọn ảnh rồi căn lại để ảnh hiển thị vừa khung.`; ví dụ xấu: `Kích hoạt mô-đun đồng bộ hình ảnh thông minh đa nền tảng.`
- Trên desktop: popover gọn sát mục tiêu nhưng không che; trên mobile 320/360/375/390/414/768 px: popover có thể ghim bottom sheet nhỏ, không che nút điều hướng, hỗ trợ safe-area và bàn phím ảo, không bị panel trái/phải đè.
- Phím điều hướng: `Escape` = Bỏ qua (có thể cần xác nhận nhẹ nếu đang nhập liệu; không làm phiền không cần thiết); Enter/Space trên nút, trái/phải nếu không tập trung input; không cướp phím đang dùng trong editor.
- Nút `Hướng dẫn` phải có focus visible, aria-label và hiển thị ở desktop/mobile; không bị lẫn với `/docs` hoặc AI assistant.

## 6. Các trường hợp bắt buộc kiểm thử và chống lỗi

Tự viết tests thích hợp bằng các công cụ hiện có; không thêm dependency mới. Tối thiểu phải bao phủ:

1. Lần đầu vào `/tao-thiep`: tour tự hiện đúng một lần, khi UI sẵn sàng.
2. F5 khi chưa hoàn tất, khi đã hoàn tất, khi đã Bỏ qua.
3. Nút `Hướng dẫn` luôn mở lại từ đầu, bao gồm sau hoàn tất/bỏ qua.
4. Quay lại bước trước giữ spotlight đúng, tiến độ đúng; tiếp liên tục không tạo state race.
5. Chọn 10 section (hoặc số thật theo profile) không tự sửa nội dung; tour có thể điều hướng tới từng phần.
6. Panel/accordion ẩn được mở bằng UI thật và không để lại trang ở trạng thái lỗi sau khi tour đóng.
7. Mục tiêu bị thiếu, đổi selector, lỗi iframe, iframe load chậm, ảnh/font load muộn: degrade gracefully.
8. Auto-scroll đúng trong nested scroller, sticky header, zoom 80/100/125/200%, resize, orientation, browser mobile.
9. Modal crop/khôi phục bản nháp/cảnh báo xung đột/autosave error đang mở thì tour không chồng lên.
10. Tour không dispatch mutation action, không gọi autosave, không tạo invitation, không upload media, không phát nhạc, không chuyển mẫu, không xuất bản.
11. Không tự xuất hiện trên trang bất kỳ ngoài `/tao-thiep`.
12. Người chưa đăng nhập, đã đăng nhập, nhiều account chung trình duyệt, mở nhiều tab, localStorage disabled.
13. Chuột, touch, bàn phím, focus trap, screen reader, prefers-reduced-motion, text tiếng Việt IME.
14. Overlay không che/cắt tooltip, popover không tràn viewport; chỉ một instance/layer đang active, destroy cleanup không memory leak.
15. Test trình duyệt thực bằng screenshot ở các bước đại diện: desktop, iPad, mobile, preview mode. Không đánh dấu visual PASS nếu chưa chụp và so sánh.
16. Regression cho EditorStore, autosaver, history, saved draft, iframe sync, crop, theme, switch template, publish; bộ test trước phải tiếp tục đạt.
17. URL có query/hash, back/forward, fast navigation, tab hidden/visible; tour không treo.
18. Nội dung text chỉ nói đến nút/tác vụ tồn tại, không hướng dẫn chức năng đã bị ẩn theo plan/role.

Đặt test cases vào repo, không xóa sau khi chạy. Thực thi test tương ứng và cả bộ regression khả thi trong môi trường; báo cáo rõ test nào không thể thực hiện do thiếu browser hoặc DB.

## 7. Quy trình triển khai và nghiệm thu

**W00 — Audit:** Lập file inventory controls/sections/viewport, kiểm tra các skill và source, ghi phần đã có/thiếu, chốt số bước tour tự động có điều kiện. Không dựa vào danh sách gợi ý rồi gán selector giả.

**W01 — UX:** Viết lời hướng dẫn cho từng bước (title <= 55 ký tự, description <= 180 ký tự nếu phù hợp) và mock bằng CSS/Blade theo design tokens thật, không bịa nút; kiểm tra mobile trước khi code logic phức tạp.

**W02 — Core:** Registry, state controller, lần đầu, skip/finish/restart, keyboard/a11y, component popover và spotlight.

**W03 — Deep UI:** Navigation các step thực, panel/accordion, container scroll, iframe bridge read-only khi cần, dynamic skipped steps, all viewport.

**W04 — QA:** Unit/feature/browser tests, regression, screenshots và xử lý lỗi UI; không đánh dấu 100% khi thiếu thử nghiệm thực tế.

**W05 — Deliver:** cập nhật `context.gemini.md`; bổ sung hướng dẫn người dùng rất ngắn trong docs nếu hệ thống docs hiện có; báo cáo file changed, behavior, selector registry, test logs, missing coverage, screenshots, commit message tiếng Việt có dấu. **Không tự commit, push, deploy**, không thao tác production.

### Điều kiện nghiệm thu cuối

- Người lần đầu vào `/tao-thiep` được giới thiệu các chức năng cốt lõi theo đúng giao diện thật, ngắn gọn và đẹp.
- Có `Tiếp` / `Quay lại` / `Bỏ qua`, progress và hướng dẫn lại thủ công.
- Sau hoàn thành/bỏ qua, F5 và lần vào sau không bị tự mở; vẫn có thể bấm `Hướng dẫn`.
- Không chạm vào dữ liệu khách hàng; không phá bất kỳ nghiệp vụ tạo/xuất bản thiệp nào.
- Không có đối tượng được giới thiệu sai vị trí, popover tràn màn hình, trạng thái focus lỗi hoặc tour đè modal.
- Không tạo framework hay hệ thống thứ hai, tuân thủ thuần Laravel + Blade + CSS + Vanilla JS.
- Có bằng chứng test và visual QA thật, không chỉ khẳng định đã hoàn thành.

**BẮT ĐẦU:** Mở source hiện hành, kiểm kê và viết bản đồ từng mục tiêu, sau đó triển khai ngay bản tour hoạt động trên `/tao-thiep`, kiểm thử và sửa mọi lỗi cho đến khi đạt tiêu chí.
