# MÂY HẸN — MASTER PROMPT NÂNG CẤP CURATED INVITATION V2
## Authentic Progressive Crafting • Editorial UI Redesign • First-Visit-Only Experience • Logic Integrity

> **ĐÂY LÀ LỆNH REFACTOR CHỨC NĂNG ĐÃ TRIỂN KHAI, KHÔNG PHẢI TẠO CHỨC NĂNG TỪ ĐẦU.**
>
> Workspace: `D:\Project\Laravel\mayhen.vn`
>
> Tư liệu nghiệm thu của chủ dự án: video quay màn hình ngày 08/11/2026 (~9,83 giây), ảnh chụp màn hình phần Love Story, file `context.gemini.md` và `.agents/skills/mayhen-invitation-standards/SKILL.md`.
>
> **Tầm nhìn:** Một chiếc thiệp được Mây Hẹn biên tập nghệ thuật thật sự, với nội dung do AI hỗ trợ viết; người dùng nhìn thấy trải nghiệm công bố bản thiết kế thành phẩm theo từng hàng, từng dòng từ trên xuống dưới, mượt, có chiều sâu, **chỉ ở lần tạo mới thực sự**. F5/mở lại phải thấy ngay thành phẩm. Toàn bộ giao diện và thiệp phải tinh tế, không dài dòng, không giả vờ rằng AI đang vẽ HTML.

---

## 0. VAI TRÒ, NHIỆM VỤ VÀ CÁCH LÀM

Bạn là **Principal Frontend Motion Engineer + Art Director + Senior Laravel Architect + UX Researcher + QA Lead**, chịu trách nhiệm chỉnh sửa code có thật.

Nhiệm vụ:

1. Đọc trạng thái triển khai thật trong repository, xác định các bug và hành vi hiện hành, không tin tuyệt đối ghi chú DONE.
2. Xem **video gốc và ảnh tham khảo được chủ dự án cung cấp** nếu có trong workspace. Nếu không truy cập được media, không nhận là đã xem; sử dụng mô tả vấn đề kiểm chứng được dưới đây.
3. Sửa hiệu ứng *Crafting Preview* từ kiểu “lướt/nhảy từng section” thành **thiết kế xuất hiện dần ở mức từng dòng chữ, hàng, chi tiết hình ảnh**, theo trật tự thị giác từ trên xuống và trái sang phải.
4. Xử lý đúng một lần ở lần tạo mới; không replay khi F5, Back/Forward, mở link hoặc vào Studio. Nút xem lại mới chủ động phát lại, **không gọi AI hay thay đổi dữ liệu**.
5. Thiết kế lại giao diện tổng của trang wizard + preview và chỉnh bố cục 7 section, đặc biệt lỗi Love Story bị bóp cột và nhiều chữ.
6. Sửa lỗi nghiệp vụ nghiêm trọng: **section thứ 7 phải là ALBUM 10 ẢNH (SECTION 8 trong SKILL), tuyệt đối không phải Dresscode (SECTION 7)**.
7. Test browser thật, chụp/so sánh video trước-sau, không chỉ test HTTP 200.
8. Bảo toàn dữ liệu, sản phẩm khác và các quy tắc trong `context.gemini.md`, `AGENTS.md`, skill; cập nhật context sau chỉnh sửa; viết commit message tiếng Việt, không tự commit/push.

**Tuyệt đối không** làm lại hệ thống curated, xóa dữ liệu cũ, thay đổi production không được phép, tự thêm thư viện frontend/animation. Sửa trực tiếp file theo cơ chế diff nhìn thấy được. Mọi file test chỉ đặt trong `tests/`.

---

## 1. HIỆN TRẠNG ĐÃ BIẾT — KIỂM TRA CHÍNH XÁC

### 1.1. Từ nhật ký triển khai `context.gemini.md`, mục 223

Các thành phần đã được báo cáo tạo ra:

- Service `app/Services/CuratedInvitationService.php`.
- Controller `app/Http/Controllers/Client/CuratedInvitationController.php`.
- Routes `curated.create`, `curated.generate`, `curated.preview`, `curated.remix`, `curated.switch_variant` trong `routes/web.php`.
- Wizard `resources/views/client/pages/curated/wizard.blade.php`.
- Preview `resources/views/client/pages/curated/preview.blade.php`.
- `resources/views/client/invitations/mayhen-curated/template.blade.php`, `schema.json`, `styles.css`, `script.js`, `sections/` gồm 7 nhóm x 3 biến thể A/B/C được ghi nhận.
- CSS `public/assets/client/css/pages/curated-wizard.css`, `curated-preview.css`.
- JS `public/assets/client/js/pages/curated-wizard.js`, `curated-preview.js`.
- `custom_config['curated_layout']` lưu bố cục.
- `initLiveCraftingScroll` và các keyframe được ghi nhận: `mayhenCraftSectionReveal`, `mayhenCraftPencilScan`.
- URL video preview có `?crafting=1`, nút `Xem lại dựng thiệp`.
- Nhật ký ghi toàn bộ hiệu ứng dựng hiện có diễn ra khoảng **0,2–4,9 giây**; suite ghi **471/471** ở thời điểm đó. **Đó chỉ là báo cáo lịch sử, phải chạy lại.**

### 1.2. Quan sát trực tiếp được mô tả từ video và ảnh

- Clip khoảng 9,83 giây; camera tại trang `/tao-thiep-tu-dong/xem-thu/{slug}?crafting=1`; preview nằm trong khung điện thoại, bên trái là danh sách biến thể.
- Thiệp **đã có nguyên một section từ đầu**, rồi camera/iframe cuộn sang section khác rất nhanh; cảm giác “tự động lướt web”, chưa phải hình ảnh/văn bản được đặt dần lên mặt giấy theo từng hàng.
- Trong vài giây đầu preview chuyển qua bìa, Story, Family, Events, Timeline và Dresscode; cuối hành trình đột ngột trở về đầu, gây cảm giác máy trình chiếu.
- Giao diện ngoài phone đen gần như toàn bộ, sidebar dày đặc text, thanh header có nhiều pill/nút chen nhau. Khoảng trống sân khấu quá lớn so với phone; thiếu phân cấp rõ cho việc người dùng cần làm.
- Ảnh chụp Love Story: khoảng trắng lớn phía đầu, câu quote nhiều dòng; văn bản story chiếm cột hẹp và quá dài, ảnh nhỏ bị dồn sang phải; font body khó tạo cảm xúc. Trên viewport điện thoại 320–390px, bố cục hai cột như vậy làm các dòng chữ cụt và kéo dài quá mức.
- Danh sách bên trái đang hiển thị `#07 Quy Định Trang Phục (Dresscode)` — **sai yêu cầu đầu bài**. Mục 223 của context cũng ghi section #7 là Dresscode thay vì Album.

Đây là **bằng chứng UI**, chưa chứng minh nguyên nhân code cụ thể. Bạn phải mở code thực tế để tìm chính xác điểm gây lỗi trước khi sửa.

### 1.3. Kiểm tra xung đột source-of-truth

- Skill gốc yêu cầu các mẫu thiệp chuẩn có 10 section và RSVP/signature.
- Chế độ curated của chủ dự án là **loại thiệp riêng, cố định 7 section**, không làm thay đổi các mẫu chuẩn 10 section.
- Chủ dự án muốn đúng **section số 1, 2, 3, 4, 5, 6 và 8** theo skill. Không suy diễn “7 section liên tiếp đầu tiên”.
- Trong `context.gemini.md` có thể có ghi chú cũ trái với repo mới nhất; đối chiếu code, migration, test và các bản ghi trước khi quyết định cách vá.

---

## 2. ĐỊNH NGHĨA LẠI TRẢI NGHIỆM (KHÔNG LÀM MÀU, KHÔNG LỪA NGƯỜI DÙNG)

**Phân biệt ba hành động thực tế và trình bày đúng sự thật:**

1. **AI viết nội dung**: có lời gọi tới AI provider thật (nếu được cấu hình), sinh các field lời ngỏ/quote hợp lệ, qua validation và lưu. Chỉ hiển thị trạng thái “Đang biên soạn lời ngỏ...” trong giai đoạn đó. Nếu content có sẵn, dùng cache hoặc nội dung biên tập dự phòng, hiển thị nhãn đúng nguồn; tuyệt đối không gọi đó là “AI đang viết” khi không có request.
2. **Mây Hẹn phối bố cục**: thuật toán chọn các layout component **đã duyệt**, palette do user chọn, chốt `curated_layout` và lưu vào DB. Không có AI sinh HTML/CSS/JS.
3. **Trình bày bản thiết kế lần đầu**: trình duyệt lần lượt tiết lộ nội dung/bố cục thành phẩm đã được server lưu, tạo cảm giác một artwork đang hình thành theo đúng cấu trúc thiết kế. Đây là **animation thực trên DOM** chứ không phải một request AI dài hay video giả.

Ngôn ngữ UI minh bạch: `AI đang biên soạn lời ngỏ` → `Mây Hẹn đang phối bố cục` → `Đang trình bày chiếc thiệp của bạn` → `Thiệp đã sẵn sàng`. **Không nói AI đang vẽ từng nét, đang tự thiết kế giao diện, hay đang suy nghĩ khi backend đã xong.** Niềm tin đến từ nội dung thực, lựa chọn hợp lý, kết quả nhất quán, khả năng sửa và tốc độ thực tế — không phải cố tình chờ hoặc giả phần trăm.

Nếu backend hoàn tất rất nhanh, có thể **trình diễn một màn unveil nghệ thuật có kiểm soát** cho lần tạo đầu tiên, nhưng phải đặt tên chính xác, cho phép người dùng **Bỏ qua** bất kỳ lúc nào. Không cố tình làm chậm lời gọi server.

---

## 3. SỬA LỖI DANH SÁCH SECTION TRƯỚC KHI POLISH

### 3.1. Hợp đồng section curated chính xác

| Position trong thiệp | Section number của skill | Canonical ID dự kiến | Tên |
|---|---:|---|---|
| 01 | 1 | `hero` | Trang bìa chính (Hero Cover) |
| 02 | 2 | `story` | Lời ngỏ & Chuyện tình yêu |
| 03 | 3 | `parents` | Song thân & Đôi uyên ương |
| 04 | 4 | `countdown_calendar` | Đếm ngược & Lịch tháng |
| 05 | 5 | `events` | Sự kiện & Bản đồ |
| 06 | 6 | `timeline` | Lịch trình hôn lễ |
| 07 | 8 | `album` | Khoảnh khắc hạnh phúc — Album 10 ảnh |

Canonical IDs trong bảng là định hướng; hãy ánh xạ sang ID data contract thực tế trong `InvitationTemplateService`, skill và Blade. Các `data-sync-target` phải dùng đúng convention hiện hữu. **Không đưa `dresscode`, `gift`, `rsvp`, `wishes`, `signature`, `ending` vào curated** dưới dạng visible hay hidden section.

### 3.2. Sửa trên toàn bộ đường đi

- Cập nhật danh sách section trong service, controller, allowed variants, layout selector, `schema.json`, Blade, JS, SSR, API remix/switch, sidebar labels, test fixtures, seeder nếu có cấu hình liên quan.
- Bổ sung **3 biến thể đã thiết kế thật** cho Album (ví dụ Gallery Editorial Grid / Film Strip / Elegant Collage), không đặt tên A/B/C nhưng cùng markup copy-paste. Album có đúng **10 slot** chuẩn `gallery.0`...`gallery.9`, nhận ảnh thật theo `data-sync-target` đã khai báo.
- Với thiệp curated đã tạo trước đây lưu `dresscode` ở vị trí cuối: tạo cơ chế normalize/render mapping ngược an toàn và migration nhẹ **chỉ khi cần**; không làm lỗi view cũ; không xóa nội dung dresscode cũ để tránh mất dữ liệu; chỉ ngừng render nó trong curated.
- Trước khi có ảnh thật, dùng placeholder đồ họa nội bộ **không phải ảnh đôi vợ chồng xa lạ mà người dùng hiểu nhầm là ảnh của họ**; ở Studio có CTA “Thêm ảnh của chúng mình”. Không tự nhận có đủ 10 ảnh thật khi người dùng chưa upload. Xác lập quy tắc publish nhất quán cho album thiếu ảnh, bảo vệ public khỏi ảnh người lạ.
- Kiểm tra đủ 10 slots trên mọi variant, switch variant không mất vị trí/ảnh; remix không đổi palette; sửa Studio vẫn đọc lại đúng các key.
- Chuẩn hóa `SKILL.md` **bằng ngoại lệ có phạm vi cho riêng curated**, không phá hợp đồng 10-section của mẫu khác. Nếu nguyên tắc không cho ngoại lệ tự động, tạo adapter/schema riêng và ghi rõ trong tài liệu.

**Quality gate:** Không được gọi task hoàn tất khi sidebar vẫn có Dresscode hoặc album không tồn tại / không tải được 10 slot trên preview và Studio.

---

## 4. TẠO HIỆU ỨNG DỰNG THIỆP THEO TỪNG DÒNG/TỪNG HÀNG (YÊU CẦU ƯU TIÊN #1)

### 4.1. Chuyển từ section reveal sang *fine-grained crafting choreography*

Cơ chế cũ theo tài liệu: animation section + cuộn iframe `initLiveCraftingScroll` khoảng 0,2–4,9s. **Không được chỉ tăng duration từ 5s lên 20s** nếu vẫn chỉ nhảy một section mỗi nhịp! Phải thay đổi **đơn vị trình diễn** từ toàn section thành từng **element / visual line / row / image accent**.

Phải thiết lập cấu trúc **reveal units** theo đúng trật tự đọc tự nhiên của từng variant:

`Section → Block → Row → Visual Line / Decorative Detail`

Ví dụ cho hero:

1. Nền giấy, khung viền nghệ thuật được lộ từ mép trên; không nhấp nháy.
2. Monogram nổi lên trong 300–450ms.
3. Dòng nhãn lễ cưới xuất hiện từ trái qua phải.
4. Tên chú rể được tiết lộ như một dòng dàn trang hoàn chỉnh.
5. Ký hiệu nối hiện nhẹ.
6. Tên cô dâu xuất hiện, cân bằng với dòng trước.
7. Khung ảnh hiển lộ qua một mặt nạ lụa di chuyển ngang chậm.
8. Ngày cưới xuất hiện theo từng hàng thông tin.
9. Đường phân cách kẻ ra từ trái sang phải, kết thúc có nhịp nghỉ nhỏ.

Ví dụ Love Story:

1. `CHAPTER ONE / THE BEGINNING` xuất hiện lần lượt từng dòng.
2. Tiêu đề tiếng Việt hiện đủ một dòng trước, không bị chia đôi khó đọc.
3. Trích dẫn được tiết lộ **từng dòng đã dàn trang tự nhiên** từ trên xuống, với mask mềm quét từ trái qua phải.
4. Ảnh được hé lộ bằng một khung ảnh giấy đi vào nhẹ, không che text.
5. Đoạn chuyện tình hiện **từng dòng đọc được**, với nhịp ngắn hơn trích dẫn.
6. Signature/ornament cuối mục khép lại; chuyển section tiếp theo khi dòng cuối đã rõ.

Với các mục còn lại:

- Parents: “NHÀ TRAI” và các tên bố mẹ lần lượt, sau đó “NHÀ GÁI”, phân cấp rõ, mỗi hàng vừa hiện vừa cân layout.
- Countdown: số và nhãn theo từng **hàng**, calendar xuất hiện theo tuần/dòng, ngày cưới được nhấn sau cùng, không random đếm số vô nghĩa.
- Events: tên sự kiện → ngày/giờ → địa điểm → đường kẻ → CTA bản đồ, mỗi hàng nối logic với thông tin thật.
- Timeline: đường trục kéo xuống dần, từng nút/mốc được đặt theo thứ tự thời gian; tránh animate 10 mốc cùng lúc.
- Album 10 ảnh: hàng ảnh thứ nhất xuất hiện tuần tự trái → phải, hàng tiếp theo từ trên → dưới, mỗi ảnh có mask reveal tinh tế, không tạo cảm giác slot nhảy vị trí.

### 4.2. Yêu cầu kỹ thuật chi tiết

- Tạo bộ điều phối animation **một lần**, không để template script, preview script và iframe cùng tự chạy timeline độc lập.
- Mỗi reveal unit có `data-mayhen-craft-step`, `data-mayhen-craft-group`, `data-mayhen-craft-order` (hoặc tương đương có giải thích). Metadata phải được derive từ **DOM thực**, không yêu cầu AI tạo selector.
- Tách **render phase** (SSR thành phẩm) khỏi **presentation phase** (tiết lộ); presentation không được thay đổi business data hay gọi API save.
- Không dùng `setTimeout` rải rác hàng trăm lần, không `innerHTML` nạp lại từng lần, không tự `scrollIntoView` sau mỗi section. Sử dụng coordinator duy nhất (`requestAnimationFrame`, Web Animations API native, CSS transitions, `IntersectionObserver` khi thực sự phù hợp).
- Tính **thứ tự bằng vị trí layout**: trên → dưới, cùng hàng trái → phải. Có sự ưu tiên ngữ nghĩa để tên, ảnh, ngày cưới không bị đảo; không sort theo DOM tùy tiện nếu variant đặt ảnh khác.
- Đối với text dài: không tách từng ký tự gây giật và tăng 1000 node DOM. Dùng reveal theo dòng trực quan sau khi webfont ổn định (`document.fonts.ready` nếu có) hoặc tổ chức nội dung thành các block/hàng rõ ràng. Có thể dùng `Range.getClientRects()` / đo hình học / mask để phân nhóm dòng **nếu triển khai chắc chắn trên các trình duyệt**; không làm hỏng wrapping, selection, copy, screen readers.
- **Một dòng viết xong mới chuyển sang dòng tiếp theo**. Trong một dòng, hiệu ứng mask/quét từ trái sang phải nhẹ nhàng, giữ phần đã hiện; phần tử chưa đến lượt không nhảy làm lại bố cục.
- Text có thể hiện bằng `clip-path`, `mask` hoặc lớp overlay trang trí; không tạo “con trỏ gõ máy” lòe loẹt. Không giả hand-writing hoặc token AI stream khi text đã có sẵn.
- Đường viền, ornament, ảnh: thời lượng/nhip riêng, nhấn nhẹ, không biến thành hiệu ứng pháo hoa. Chữ phải đạt độ tương phản để đọc ngay khi xuất hiện.
- Dùng `transform`/`opacity`/`clip-path` hợp lý; tránh animation `height:auto` gây layout shift. Giữ **final geometry** của các section từ đầu và chỉ reveal content, hoặc dùng placeholder giữ chiều cao chính xác.
- Nếu fonts/images tải trễ, đợi layout ổn định ở phạm vi cần thiết, có fallback; khi ảnh lỗi lộ placeholder đẹp và ghi nhận asset problem, không để pipeline treo.
- Không áp dụng hiệu ứng cho `prefers-reduced-motion: reduce` — nội dung hiện đầy đủ ngay, không bắt buộc trải qua 20 giây.
- Mọi hiệu ứng `aria-hidden`/inert/focus phải hợp lý: không làm screen reader mất nội dung hay focus vào nút vô hình; trong chế độ trình diễn cho phép skip và điều khiển.

### 4.3. Nhịp thời gian đề xuất (chỉ áp dụng giai đoạn presentation)

Chủ dự án không muốn 4,9s cho cả tấm thiệp. Đề xuất **~18–26 giây** ở chế độ “trình bày thành phẩm”, tốc độ linh hoạt theo số dòng và số ảnh; **không đặt delay tĩnh máy móc**. Có nút bỏ qua ngay.

- Mỗi dòng tiêu đề/quote: ~350–650ms tùy độ dài.
- Mỗi dòng story: ~140–260ms, có thể nhanh hơn headline.
- Mỗi ảnh/khung: ~350–600ms, theo độ lớn và loại ảnh.
- Mỗi phần chuyển section: nghỉ ~200–350ms, không blank screen dài.
- Không cần chờ đủ 26 giây nếu nội dung ít; tính progression từ số reveal units thực, chiều cao, thời lượng thống nhất.
- Khi user bật `Tốc độ nhanh` hoặc `Bỏ qua`, chuyển tức thì hoặc co ngắn; không khóa người dùng.

**Không show progress 0–100% giả lập theo clock.** Thanh tiến độ presentation, nếu cần, tính từ `visibleSteps / totalSteps` thực và tách hẳn khỏi tiến độ AI/provider.

### 4.4. Auto-scroll nhẹ và thông minh

- Không cuộn liên tục kiểu camera bay qua cả section (như video).
- Chỉ scroll thêm khi **dòng tiếp theo đã đến gần đáy vùng nhìn thấy**, để người xem luôn nhìn thấy dòng đang hiện.
- Ưu tiên giữ 65–75% khung preview là vùng có nội dung đang dựng; scroll theo tốc độ giới hạn (ví dụ 70–140px/s tùy viewport), ease-in-out, không giật mốc lớn.
- Khi người dùng tự wheel/touch/drag scrollbar trong preview, **tạm dừng auto-scroll ngay**; không giành quyền cuộn; có nút `Tiếp tục xem quá trình` nếu còn trong phiên trình diễn.
- Khi trình diễn xong, **KHÔNG tự nhảy về đầu một cách đột ngột**. Kết thúc ở vị trí cuối đang xem; hiển thị CTA `Xem từ đầu` để người dùng chủ động. Nếu muốn giới thiệu bìa, cung cấp nút điều hướng riêng, không tự reset.
- Device switch giữa Mobile/Tablet/Desktop không phát lại toàn bộ; giữ trạng thái `ready` hoặc tiếp tục đúng đơn vị phù hợp mà không tự reload vĩnh viễn.

### 4.5. Trạng thái người dùng nhìn thấy

- Trong presentation: một câu trạng thái ngắn `Đang hoàn thiện phần “Lời ngỏ” · 2/7`, không hiện technical logs hoặc tiếng Anh lẫn lộn.
- Thanh trạng thái compact, không thay thế cho toàn bộ topbar. Có `Bỏ qua`, `Tạm dừng` nếu cần; kiểm soát bằng bàn phím.
- Hoàn thành: trạng thái tĩnh **`Thiệp của bạn đã sẵn sàng`**, biến mất animation trạng thái sau khi xác nhận; nút **`Chỉnh sửa`** là CTA chính, `Xuất bản` khi đáp ứng điều kiện và `Phối lại` là secondary.
- Không dùng thông báo giả `AI đã vẽ xong giao diện`. Nếu sinh nội dung AI thật, có thể ghi `Lời ngỏ có hỗ trợ AI · Bạn luôn có thể chỉnh sửa`.

---

## 5. CHỈ TRÌNH DIỄN Ở LẦN TẠO THẬT ĐẦU TIÊN (YÊU CẦU ƯU TIÊN #2)

### 5.1. State machine bắt buộc

Cần có trạng thái có thể xác minh từ server và phía client, tách khỏi trạng thái publish:

`new → generating_content → composing_layout → persisted → presenting_first_time → ready`

Nhánh thất bại: `generation_failed`, `presentation_interrupted`, `recoverable`.

**Không dùng `?crafting=1` làm nguồn thẩm quyền để quyết định replay**. Nó có thể do người dùng copy link, Back/Forward hoặc F5 còn nguyên query. URL không được khiến animated generation chạy lại.

### 5.2. Cơ chế *one-time first presentation* bền vững

1. Khi người dùng bấm **Tạo thiệp** thực sự, backend sinh một `generation_id`/`presentation_id` duy nhất cho lần tạo đó; lưu nội dung và `curated_layout` trước khi báo thành công. Đừng tạo invitation trùng vì double-submit.
2. Lần đầu mở preview từ response thành công: server cấp **quyền trình bày một lần**, dựa trên state được kiểm tra và xác thực (chủ sở hữu/session), ví dụ one-time presentation token hoặc cờ phát lần đầu được tiêu thụ atomically. Không dùng duy nhất localStorage để chống replay.
3. **Ngay khi presentation bắt đầu**, đánh dấu quyền này đã tiêu thụ để nếu F5 giữa chừng thì trang sau hiện ngay thành phẩm. Có thể ghi `first_presented_at`/`presentation_consumed_at` nếu cần, hoặc cơ chế nhẹ tương đương; schema thay đổi phải có migration tương thích và rollback.
4. `history.replaceState()` gỡ `crafting=1` nếu còn trong URL sau khi đã nhận quyền hiển thị. Đừng phụ thuộc duy nhất vào bước này.
5. Truy cập preview bằng link trực tiếp, nhấp Back/Forward, Ctrl+R/F5, mở tab mới, khôi phục session, trở lại từ Studio, xem public `/thiep/{slug}`: **render full content ngay; không flashing/skeleton/delay và không auto-scroll**.
6. Nếu presentation mới chỉ chạy được 30%, người dùng reload phải thấy **100% thiết kế đã lưu**. Không để các section mãi `opacity:0` nếu JS không hoạt động.
7. `Xem lại dựng thiệp` là một **nút tùy chọn** không kích hoạt backend AI, không tạo invitation mới, không random layout, không ghi dữ liệu, không đổi thời hạn gói. Có thể replay client-side bằng reset reveal states của cùng DOM; không reload iframe nếu có thể, tránh flicker và tốn mạng.
8. `Phối lại bố cục` là một **hành động khác**, chỉ hoạt động khi user bấm rõ ràng; backend lưu bố cục mới, giữ màu và nội dung; hỗ trợ undo/khôi phục nếu đã lưu bản trước. Hành động này không phải replay AI-content. Sau phối lại có thể chỉ animate phần layout đã thay đổi theo ý người dùng; không tự kích hoạt full first-time ceremony.
9. `Đổi biến thể từng section` chỉ render lại section đó, giữ ảnh/data; không làm reset toàn trang, không phát lại AI.
10. Nếu UI mở lại trong chế độ editor/embed, luôn bỏ motion và show full nội dung. Các template 10 section khác phải giữ behavior hiện hữu.

### 5.3. Không gọi API/chi phí vô ích

- Reload/mở preview chỉ **GET persisted record**; không gửi `generate` hoặc `remix` tự động.
- Không gọi lại AI khi playback, scroll, đổi thiết bị, sửa CSS, hover, F5.
- Khi backend timeout, báo rõ `Không thể hoàn tất nội dung` và hỗ trợ thử lại idempotent; không hiển thị nội dung cũ dưới nhãn mới mà không nói rõ.
- Xử lý tab đôi và hai requests cùng `generation_id`, CSRF, ACL; không tạo hai thiệp hay phát sự kiện hai lần.

---

## 6. REDESIGN UI TOÀN BỘ WIZARD + PREVIEW — QUIET LUXURY, CÂN ĐỐI, ÍT CHỮ

### 6.1. Định hướng art direction

Giao diện hiện tại: nền đen gần toàn bộ và các chip/danh sách nhồi nhiều chữ. Mục tiêu mới là một **Digital Wedding Design Atelier**, không phải dashboard kỹ thuật và không phải AI chat app.

- Nền chính ưu tiên **warm ivory / soft neutral** (ví dụ `#F8F5F0`) hoặc phối nền lụa trầm ở một vùng sân khấu nhỏ; giữ dark mode như lựa chọn nếu cần. Tránh toàn màn hình đen khiến phone trông lọt thỏm.
- Phân cấp rõ 3 khu: **Toolbar cô đọng**, **Design tools bên trái**, **Live Invitation Stage nổi bật bên phải**. Thiệp là nhân vật chính, không phải sidebar.
- Ẩn metadata thừa khỏi chế độ mặc định; ngôn từ tiếng Việt nhất quán. Không dùng dấu emoji tràn lan trên 6 nút header.
- Phong cách: typography serif cho heading, sans đọc rõ cho UI, viền mỏng, shadow nhẹ, màu nhấn theo nhận diện Mây Hẹn **khác với palette thiệp của khách**.
- Kích thước control tối thiểu đủ chạm, focus rõ, microcopy súc tích, không có nút vô hiệu.
- Tránh bóng kính/gradient quá mức, thẻ viền dày và màu vàng nâu phủ tất cả.

### 6.2. Desktop / laptop đề xuất

- Trên màn hình 1366–1920px, tổng layout max-width hợp lý hoặc full-bleed có phân khu rõ, **không tạo lỗ trống lớn**.
- Toolbar chiều cao ~64–76px, chỉ còn: quay lại, nhận diện Mây Hẹn, tên cặp đôi/palette compact, trạng thái, preview device selector và 1–2 CTA chính. Các nút phụ gom vào popover/menu `Tùy chọn`.
- Panel trái rộng khoảng 280–340px, tiêu đề `Tùy chỉnh thiết kế`, danh sách đúng 7 section. Mỗi item hiện **tên ngắn + tên variant + nút trước/sau**. Mô tả dài để tooltip/collapsible, không cần `SECTION 1: ... (English)` lặp ở mọi card.
- Live Stage chiếm phần lớn vùng còn lại; phone preview cân tâm, rộng hơn hợp lý trên màn hình lớn, có nền sân khấu ánh giấy nghệ thuật hoặc shadow thoáng. Nút liên quan đến preview nằm gần preview.
- Sidebar có tab `Bố cục` / `Nội dung` / `Màu sắc` nếu thực sự hữu dụng; không tạo tabs chỉ để trang trí. Đừng làm mất khả năng chỉnh nội dung AI sinh.
- Gợi ý mini thumbnail cho các variant thay vì A/B/C vô nghĩa; tên variant diễn tả bố cục, khách cảm nhận khác biệt trước khi chọn.
- Sticky bottom action area hợp lý nếu viewport thấp; không che preview và CTA.

### 6.3. Mobile / tablet

- Mobile không giữ sidebar cố định khiến preview chỉ còn 150px. Ưu tiên **preview gần full-width**, danh sách biến thể mở trong bottom sheet/drawer khi bấm `Tùy chỉnh`.
- Toolbar chỉ giữ trạng thái ngắn và CTA chính; menu phụ trong một nút.
- Thanh thao tác dưới (nếu có) gồm `Bố cục` | `Nội dung` | `Xem thiệp` | `Tiếp tục`, tối đa đủ dùng, không tranh chấp với bottom nav hệ thống (preview độc lập có thể không render chrome marketing).
- Kiểm `100dvh`, safe area, soft keyboard, orientation, touch, scroll containment và overscroll. Không để dynamic island trang trí chồng lên nút nhạc hoặc nút Chữ Lớn.
- Chế độ desktop preview không giả lập điện thoại bé xíu; layout tự thích ứng theo device selector.

### 6.4. Redesign wizard nhập liệu

- Bước 1: `Hai bạn là ai?` gồm 2 ô rõ ràng Chú rể/Cô dâu, ưu tiên nội dung quan trọng.
- Bước 2: `Hai bên gia đình` chia cặp cha/mẹ tương ứng và ghi rõ có thể bổ sung sau nếu nghiệp vụ cho phép; không hỏi thông tin quá nhiều chỉ để đẹp demo.
- Bước 3: `Phong cách của bạn` lựa chọn bằng **thẻ có preview đồ họa thực**, chỉ 4 phong cách backend cho phép. Không free-text style. Định nghĩa rõ Lãng mạn / Tối giản / Editorial / Truyền thống.
- Bước 4: `Bảng màu yêu thích` swatches lớn rõ nhãn và contrast + thông tin ngày/giờ/sảnh khi cần; quyết định trải nghiệm nhập dần dần thay vì ép quá nhiều trường.
- Summary trước khi tạo: hiển thị tên, phong cách, palette, trạng thái field chưa có; CTA `Tạo thiệp của chúng mình`.
- Nút có loading thật, chặn submit trùng, giữ draft form sau refresh/error; không hiển thị nội dung marketing dài. Khi chưa upload ảnh, chỉ rõ sẽ có khung chờ ảnh cưới thay vì giả ảnh có sẵn của người khác.

---

## 7. SỬA CHI TIẾT BỐ CỤC 7 SECTION — ĐẶC BIỆT STORY

### 7.1. Thiết kế nhất quán ở chiều rộng phone thực tế

Không chỉ đẹp ở CSS viewport desktop. **Bản thiệp phải được art-direct cho khoảng 320–430 CSS pixels bên trong iframe**; chrome ngoài phone làm vùng nội dung thực nhỏ hơn nữa. Mọi variant có min-width an toàn.

- Mỗi section có composition rõ: eyebrow (nếu cần), heading, nội dung chính (ảnh hoặc typography), footer/ornament nhỏ.
- Dùng tỷ lệ chữ và nhịp dọc: heading 23–34px tùy layout; body khoảng 14–16px, line-height ~1.55–1.75; text màu đậm đủ tương phản; quote khác biệt nhưng không quá dài.
- Tránh `min-height: 100vh` vô ích gây khoảng trắng lớn, phần ornamental spacer quá cao, `position:absolute` cho nội dung dài, ảnh bị bóp.
- Giữ mỗi trang/section đủ thở nhưng không biến thành hàng nghìn pixel chữ.
- Ảnh `object-fit: cover` khi hợp lý, giữ chủ thể; bố cục không bị phá vì ảnh chưa tải.
- Các variant thực sự khác nhau theo composition, chứ không chỉ đổi label, viền hay màu.

### 7.2. Riêng section 2: Love Story bắt buộc sửa nguyên nhân

Ảnh tham khảo cho thấy text bị bóp thành cột hẹp dưới ~150px và ảnh chèn bên phải. **Đây là anti-pattern trên mobile**.

- Với viewport thiệp <= ~480px: dùng **một cột**, có thứ tự thị giác chủ động: eyebrow → quote ngắn → ảnh/khung ảnh hợp lý → story ngắn → điểm nhấn. Có thể dùng split editorial *chỉ khi* chiều rộng container đủ cho cột chữ tối thiểu ~240px và cột ảnh ~180px. Mốc breakpoint phải dựa trên width **container/iframe**, không chỉ outer `window`.
- Nếu muốn layout bất đối xứng trên mobile, chỉ cho phép một ảnh floating nhẹ hoặc full-width, không ép text dài chạy vòng qua ảnh theo 90px.
- **Không bắt quote dài 7–10 dòng rồi tiếp tục story 18–25 dòng**. Story cần được AI viết **ngắn, xúc động, đúng chất một chiếc thiệp**, không phải bài blog.
- Vùng trên của section không có khoảng trống trắng khổng lồ; khoảng cách phù hợp để giữ nhịp với Hero.
- Ảnh Story nên đủ lớn để tạo cảm xúc; nếu chỉ có ảnh mẫu, dùng placeholder trung tính và nhãn rõ trên preview chủ thiệp.
- Tránh text bị orphan một từ ở cuối dòng, tiêu đề bị xuống 4–5 dòng; dùng `text-wrap: balance` cho tiêu đề khi browser hỗ trợ, fallback hợp lý; đừng CSS ép `white-space: nowrap` khiến tràn màn hình.
- Không thu nhỏ body text xuống 11px để cứu layout. Thay bố cục, không ép chữ.

### 7.3. Content budget bằng AI, phân biệt content và sự kiện thật

AI provider **chỉ viết** các key đúng hợp đồng: `story_title`, `story_subtitle` nếu có, `couple_quote`, `story_content`, `hero_slogan` hoặc lời dẫn ngắn khi template hỗ trợ. Không cung cấp quyền tạo section/layout/CSS/HTML.

**Giới hạn gợi ý (có validator ở backend và fallback biên tập):**

| Nội dung | Ngân sách từ đề nghị | Yêu cầu |
|---|---:|---|
| Hero slogan | 6–12 từ | 1–2 dòng giàu cảm xúc, tránh sáo rỗng |
| Story title | 3–7 từ | Có nét riêng, không generic |
| Story subtitle | 5–12 từ | Có thể ẩn nếu lặp quote |
| Couple quote | 12–25 từ | ~2–4 dòng ở phone |
| Story content | **45–80 từ**, ưu tiên ~55–65 | 2–3 đoạn ngắn hoặc 1 đoạn ngắn; không tự bịa các sự kiện chưa được cung cấp |
| Timeline label | Mỗi mốc 2–7 từ | Chỉ tạo từ lịch trình thực hoặc dùng mốc mẫu được gắn nhãn minh họa |

- Không lặp tên đôi uyên ương ở mọi đoạn. Không dài dòng kiểu “giữa muôn kiếp trước, dưới ánh hoàng hôn rạng rỡ...”, tránh sáo rỗng, không lạm dụng `hạnh phúc`, `vĩnh cửu`, `trọn đời`.
- Nếu khách chỉ nhập 6 cái tên và chọn phong cách, AI không được khẳng định họ gặp nhau ngày nào, đi du lịch ở đâu hay cầu hôn như thế nào. Viết bằng cảm xúc **không cần bịa sự kiện có thật**.
- Nếu có dữ liệu tình yêu tự nguyện nhập, mới cá nhân hóa dựa trên dữ kiện đó.
- Content trả về phải theo JSON schema, validate loại, độ dài, ký tự, escape XSS; chỉ lưu khi hợp lệ. Nếu quá dài, yêu cầu rewrite/rút ngắn có giới hạn; không đơn thuần CSS `line-clamp` để giấu nội dung khách trả tiền.
- Có nút `Viết lại lời ngỏ` và `Sửa bằng tay` trong UI; viết lại chỉ cập nhật field nội dung, **không đổi layout hoặc palette**.
- Với AI provider lỗi/hết quota: fallback có nguồn ghi rõ `Lời ngỏ mẫu từ Mây Hẹn`; không nói “AI tạo xong” khi dùng mẫu tĩnh.
- Không hiển thị nguyên cả khối văn bản 150–250 từ trên một màn hình phone.

### 7.4. Các section còn lại

**Hero:** tên hai người là tiêu điểm; ảnh lớn hơn khi có ảnh thật; ngày cưới rõ; sắc màu do user chọn; tránh font quá nhỏ và các badge/ornament vô nghĩa.

**Parents:** nhà trai và nhà gái cân đối, tên bố mẹ 4 người hiển thị đúng, không đảo vai; nếu thiếu tên thì placeholder biên tập, không bịa. Cỡ chữ dễ đọc cho phụ huynh.

**Countdown & Calendar:** chỉ chạy countdown khi ngày hợp lệ; nếu chưa nhập, hiển thị “Bổ sung ngày cưới để xem đếm ngược”, không chạy đến ngày giả; timezone Việt Nam; lịch không lệch thứ/ngày.

**Events & Maps:** venue/time/address đúng các trường đã nhập; nếu chưa nhập, không hiển thị bản đồ chỉ sai địa điểm; CTA hướng dẫn thêm địa điểm khi chủ thiệp đang edit.

**Timeline:** mốc thật theo data; khi chưa có, hiển thị placeholder gọn gàng, tránh lịch trình bịa một cách khẳng định; timeline chỉ một dòng mỗi mốc.

**Album:** đúng **10 slot** có trạng thái ảnh đã upload/chưa có, variant gallery tối thiểu 3 phương án thực; bố cục 2–3 cột mobile tùy kích thước; ảnh đủ lớn, không nhồi 10 khung bé xíu không nhìn thấy.

---

## 8. AI CONTENT ENGINE — ĐẢM BẢO ĐÁNG TIN, KHÔNG KHOÁC ÁO AI CHO DỮ LIỆU GIẢ

- Kiểm tra `CuratedInvitationService` có thực gọi model/connector không, hay đang trả mảng câu cố định. Phải chứng minh đường đi thật bằng tests mock provider, request ID đã che secret, và output validation; không lộ token/API keys.
- Lưu metadata **nguồn nội dung** (`ai_generated`, `editorial_fallback`, `user_edited` hoặc tương đương) ở đúng scope, không lưu thông tin nhạy cảm không cần.
- Có prompt nội bộ giới hạn schema/word budget/nội dung cấm bịa; model không được chỉ đạo render/URL/code.
- Không sử dụng “fake typing” để gợi ý AI đang suy luận từng từ khi thực tế chỉ reveal text đã hoàn thành.
- Giới hạn timeout/cost, retries có backoff phù hợp, quota và rate limiter; lỗi AI không được tạo thiệp trùng, lưu nửa chừng mà báo success.
- Xác minh content AI không chứa tên người không liên quan, nhãn mẫu không tương ứng palette, thông tin ngày âm/bản đồ tự bịa, link lạ, văn bản quá dài, markdown hiển thị như literal.
- Khi `remix` chỉ thay các variant, **content_hash phải không đổi**; khi regenerate content chỉ thay content fields, `layout_hash`/palette không đổi.

---

## 9. HỢP ĐỒNG FRONTEND VÀ BE, TỐI ƯU HIỆU NĂNG

### 9.1. FE modules đề xuất (thích nghi cấu trúc hiện tại)

Tại `public/assets/client/js/pages/` hoặc thư mục curated riêng nếu repo có convention:

- `curated-preview.js`: bootstrapping + UI actions; tránh script toàn năng trộn mọi logic.
- `curated-crafting-coordinator.js`: reveal timeline state machine, step discovery, timing, pause/resume/skip/replay, scroll tracking, cleanup.
- `curated-presentation-state.js`: kiểm tra/consume one-time state từ server, page navigation state, set ready; không dùng URL query đơn thuần.
- `curated-layout-variants.js` (nếu cần): mapping cho bảy section và renderer metadata; không duplicate source schema.

Cụ thể tên file tùy dự án: ưu tiên ít file, module hóa vừa đủ, **không thêm dependency**. Dọn logic cũ `initLiveCraftingScroll` nếu bị thay, tránh hai animation controllers cùng thao tác iframe.

### 9.2. CSS

- Tách motion tokens `--mayhen-curated-craft-*` và UI tokens `--mayhen-curated-ui-*` khỏi palette thiết kế thiệp (user choice).
- Tuân thủ prefix `mayhen_curated_*` cho UI, `mayhen_invitation_*` + modifier cho thiệp, không dùng selector global phá Admin hoặc template khác.
- Không lạm dụng `!important`, không inline `<style>`/`<script>` trong Blade.
- Đảm bảo **JS disable/no animation state là hiển thị đầy đủ**; CSS không đặt mặc định `opacity:0` cho tất cả nội dung khi không có data-state đang trình diễn.
- Reduced motion và print/export phải show full content 100%.
- Sử dụng local fonts/assets, không thêm CDN. Nếu lựa chọn font đã có sẵn, dùng đúng local file và kiểm bản quyền.

### 9.3. Backend/DB

- Chỉ thêm bảng/cột mới nếu one-time state không thể biểu đạt an toàn qua trường hiện có; nếu thêm migration phải có rollback không phá dữ liệu và kiểm tra SQLite + MySQL ở môi trường test khi có.
- `curated_layout` và palette hiện tại không đổi bất ngờ khi preview GET hoặc iframe replay.
- Chủ invitation mới được remix/modify; guest token/public không có quyền đổi layout.
- Chống CSRF, mass assignment, IDOR, race, double-click, stale revision. Sử dụng DB transaction khi generation tạo dữ liệu liên quan.
- Response format rõ: `generation_status`, `content_source`, `layout_version`, `presentation_token?`, `invitation_id`, `revision_id`. Không bắt buộc áp dụng đúng field names nếu có model tương đương.
- Phục hồi thiệp cũ có `dresscode` thay album: không phá public URL hoặc ảnh đã được lưu từ mẫu 10-section.

---

## 10. THỨ TỰ TRIỂN KHAI (BẮT BUỘC, TRÁNH SỬA LAN MAN)

**P0-A — Khảo sát và xác nhận**

- Đọc `context.gemini.md`, `AGENTS.md`, skill, master prompt đã triển khai curated, và các file có thật ở mục 1.
- Chạy `git status`; bảo toàn uncommitted changes.
- Map khi GET preview có `?crafting=1`: ai gắn cờ, ai scroll, ai animate, ai reset ở cuối.
- Xác định đúng 7 section đã tạo (đặc biệt nhầm `dresscode` với `album`).
- Lập danh sách hành vi xác minh từ video vs code.

**P0-B — Sửa 7-section contract và dữ liệu an toàn**

- Loại Dresscode khỏi curated; thêm Album 10 ảnh với 3 variant thực.
- Đồng bộ schema, backend, frontend, SSR, editor, seeder, tests và thiệp cũ.

**P0-C — One-time animation logic**

- Thiết lập one-time presentation state, tiêu thụ atomically; chặn replay do reload/query.
- Replay explicit không gọi server/AI; public luôn render instant.

**P1-A — Fine-grained progressive rendering**

- Cơ chế reveal theo dòng/hàng, không fade section nguyên khối.
- Điều phối scroll nhẹ, skip/pause/resume/reduced motion, dừng đúng chỗ không tự quay đầu.

**P1-B — Editorial typography & copy budget**

- Sửa Love Story dồn chữ và ảnh.
- Giảm độ dài AI copy và mọi text hiển thị; giữ ngữ nghĩa và người dùng vẫn chỉnh sửa được.
- Nâng composition thực 7 section và 21 variant, không xóa toàn bộ hoặc viết lại 21 variant nếu không cần.

**P1-C — Redesign tổng Preview và Wizard**

- Tái tổ chức header, sidebar, phone stage, action hierarchy, mobile drawer.
- Có screenshot before/after và người dùng test được ngay.

**P1-D — Hoàn thiện test, cập nhật context**

- Chạy tests, kiểm tra 4 mẫu 10-section cũ không bị ảnh hưởng, write changelog/commit message tiếng Việt.

---

## 11. MA TRẬN TEST NGHIỆM THU BẮT BUỘC — CÓ BẰNG CHỨNG

### 11.1. Animation / persistence

| Test ID | Tình huống | Expected |
|---|---|---|
| CRAFT-01 | POST tạo một thiệp mới thành công | Một invitation được lưu, one-time presentation được cấp |
| CRAFT-02 | Lần đầu vào preview | Các hàng/dòng xuất hiện theo thứ tự, không nhảy cả section |
| CRAFT-03 | Đang dựng ở 30% bấm F5 | Full finished invitation ngay, không tự chạy lại |
| CRAFT-04 | Dựng xong bấm Ctrl+R | Full finished invitation ngay |
| CRAFT-05 | Mở cùng preview ở tab mới | Full finished invitation, không tái tạo |
| CRAFT-06 | Back/Forward/BFCache | Không replay không chủ ý, không mất state |
| CRAFT-07 | URL trực tiếp có `?crafting=1` sau khi đã xem | Không replay nếu server one-time đã consumed |
| CRAFT-08 | Bấm `Xem lại dựng thiệp` | Replay client-side đúng layout và nội dung cũ, zero AI call |
| CRAFT-09 | Bấm `Bỏ qua` ở bước 2 | Toàn bộ nội dung hiện tức thì, không vị trí scroll kỳ quặc |
| CRAFT-10 | Người dùng wheel/touch scroll khi diễn | Auto scroll tạm dừng ngay, không kéo giành điều khiển |
| CRAFT-11 | `prefers-reduced-motion: reduce` | Full content hiện ngay hoặc không motion gây khó chịu |
| CRAFT-12 | JS lỗi/tắt khi mở preview đã lưu | Nội dung vẫn hiện đầy đủ theo SSR |
| CRAFT-13 | Bật device selector trong khi/hoàn tất | Không làm replay hoặc nhảy UI bất ngờ |
| CRAFT-14 | Bấm Remix | Chỉ đổi layout, giữ palette, content, album; không gọi AI |
| CRAFT-15 | Switch section variant | Chỉ thay đúng section, không mất dữ liệu, không reset toàn preview |
| CRAFT-16 | Hai tab đồng thời tạo/tiêu thụ một presentation | Không tạo invitation trùng, không có hai first-time plays do race |
| CRAFT-17 | Provider AI timeout/failure | Không báo AI thành công giả; retry và fallback minh bạch |
| CRAFT-18 | Load ảnh/font trễ | Không đứng ở trạng thái ẩn, không vỡ tỷ lệ/geometry |
| CRAFT-19 | Hết animation | Không tự jump đầu trang; trạng thái ready chính xác |
| CRAFT-20 | Mở public invitation | Render ngay không play first-time crafting |

### 11.2. Seven-section, content, visual quality

| Test ID | Tình huống | Expected |
|---|---|---|
| UI-01 | Render DOM curated mới | Chính xác 7 section 1/2/3/4/5/6/8 |
| UI-02 | Sidebar | `#07 Album 10 ảnh`, tuyệt đối không Dresscode |
| UI-03 | Album 0 ảnh | 10 placeholder an toàn/labeled, không stock người lạ gây hiểu nhầm |
| UI-04 | Album 10 ảnh | 10 slot hiển thị đúng, không sai thứ tự |
| UI-05 | Album 3 variants | Thực sự khác composition, giữ 10 ảnh sau switch |
| UI-06 | Love Story ở phone 320px | Không text column quá hẹp, không line nhiều chữ cụt |
| UI-07 | Love Story ở phone 390px | Dàn trang gọn, ảnh có vai trò lớn, không khoảng trắng quá mức |
| UI-08 | Story AI dài bất thường | Validation/rewrite có giới hạn, không nhồi chữ |
| UI-09 | Chỉ nhập tên, chưa ngày/địa điểm | Không tự bịa ngày, countdown, map, timeline |
| UI-10 | Reload preview/Studio | Palette, bố cục, text, album giữ nguyên |
| UI-11 | 320, 375, 390, 430, 768, 1024, 1366, 1440, 1920 | Không overflow, button/tooltip/nav đúng, tâm preview cân đối |
| UI-12 | Màn 1366×768 | Toolbar không quá dày, không che CTA; phone không bị cắt |
| UI-13 | Mobile dưới 768px | Không sidebar chiếm chỗ; có bottom sheet chỉnh layout |
| UI-14 | Text Vietnamese/emoji/name dài | Không vỡ chữ hoặc CSS, XSS được escape |
| UI-15 | Print/export / embed Studio | Show full content, không animation/hidden state |
| UI-16 | Bốn mẫu thiệp 10 section cũ | Giữ nguyên data contract và behavior |
| UI-17 | AI provider thực vs fallback | Nhãn nguồn nội dung trung thực, không fake status |
| UI-18 | Button và state UX | Chỉnh sửa/Phối lại/Replay/Xuất bản khác chức năng rõ |

**Yêu cầu về browser evidence:**

- Chụp screenshot ở các mốc `t=0`, `t≈3s`, `t≈7s`, `t≈12s`, và cuối presentation thực (mốc phụ thuộc số steps), kèm video screen-record **với thao tác thực**.
- Test F5 ngay giữa dòng Story và sau khi hoàn thành.
- So sánh screenshot Love Story trước-sau cùng kích thước phone frame; nếu còn cột text 90–140px và ảnh bé chiếm không gian vô nghĩa, coi là **FAIL**.
- Ghi screenshot cảnh sidebar #07 Album, 10 ảnh theo 3 variants và mobile editor.
- Test console errors, HTTP failed assets, memory leaks, zombie timers, duplicate event listeners, iframe unload cleanup, FPS/LCP/CLS phù hợp; không tự tuyên bố 60fps hoặc 100%.
- Tests backend tại `tests/Feature/CuratedInvitationFlowTest.php` và tests JS/browser đặt dưới `tests/` (tên mới hợp lý). Không sửa test để giấu bug; không dùng SQLite-only để kết luận MySQL concurrency đã an toàn.

---

## 12. TIÊU CHÍ THẨM MỸ NGHIỆM THU CÓ THỂ QUAN SÁT

Khi người dùng nhìn màn hình tạo thiệp:

- Trong 3 giây đầu **thấy tên cô dâu chú rể/nhận diện thiết kế** rõ; không phải chờ máy chạy progress giả.
- Từng hàng và phần tử “được đặt lên giấy” một cách có chủ đích, không phải camera nhảy loạn giữa sections. Có nhịp nhanh-chậm như art director chứ không đồng hồ đều đều.
- Mọi thứ sang trọng, tinh tế, ấm áp, ít chữ; focus vào thiết kế và ảnh, không vào chip kỹ thuật.
- Người dùng tin vào quy trình vì **có nội dung mới hợp tên/phong cách, layout được phối thật và lưu thật**, không phải vì bị giữ ở loading lâu.
- Khi khách reload, mở lại hoặc chia sẻ link, thiệp xuất hiện **ngay, hoàn chỉnh và thống nhất**. Không bao giờ khiến khách nghĩ phần mềm “múa máy” mỗi lần mở.
- Khi chỉnh sửa, kết quả vẫn đồng bộ Studio, full SSR và public invitation; không mất ảnh, text, màu, section variants.
- Nút chính có giá trị thật, không có CTA giả hoặc fake typing.

---

## 13. NHỮNG THỨ TUYỆT ĐỐI KHÔNG ĐƯỢC LÀM

- Không sửa bằng cách tăng CSS delay, giữ nguyên jump-scroll theo section; đó không đáp ứng yêu cầu.
- Không để animation chạy mỗi `load`, `DOMContentLoaded`, iframe `load`, tab switch hay chỉ vì `?crafting=1`.
- Không gán `opacity:0` cho SSR full content rồi dựa vào JS luôn chạy; sẽ gây thiệp trắng khi JS lỗi.
- Không bịa tiến độ AI, không gọi AI lại khi replay, không tự ý tạo lời kể “AI đang vẽ” khi thực ra layout fixed.
- Không biến text thành một chuỗi `span` mỗi ký tự với 1000+ DOM nodes gây lag.
- Không thêm thư viện motion/framework/CDN/Google Fonts ngoài, không tạo inline CSS/JS tùy tiện.
- Không phá mẫu 10 section hoặc làm mất các quyền thanh toán/giới hạn gói/owner.
- Không dùng stock ảnh người khác như ảnh cưới thật của user, không đẩy thông tin chưa có thành dữ liệu đã xác nhận.
- Không xóa nội dung cũ của người dùng chỉ để khớp layout mới; chỉ thay phạm vi render với migration/backward compatibility rõ.
- Không tự ghi `PASS 100%` chỉ vì các feature tests chạy xanh; visual browser QA là điều kiện bắt buộc của yêu cầu này.
- Không đổi style/colour tự chọn của khách khi remix hoặc AI rewrite.
- Không commit/push thay người dùng; không chạy migrations phá dữ liệu production.

---

## 14. BÀN GIAO CỤ THỂ

Sau khi thực hiện, cung cấp:

1. **Audit hiện trạng**: root cause của scroll nhanh, animation chạy lại, UI dài chữ và nhầm Dresscode/Album. Nêu file/hàm/đoạn code chính xác.
2. **Danh sách file thực tế đã thay đổi**, kèm giải thích theo module; không cần in các file không sửa.
3. **Screenshot/video before-after** (nếu có browser tool) của lần tạo đầu, F5, Love Story, Album, mobile wizard.
4. **Tổng hợp test**: lệnh chạy, pass/fail/skip, công cụ browser, những phần chưa thể xác minh.
5. **Migration/backward-compatibility note** cho thiệp curated cũ, không xóa dữ liệu khách.
6. **Cập nhật `context.gemini.md` sau mỗi lần sửa** theo đúng nội quy; nếu thêm docs, cập nhật đúng file liên quan mà không làm phình to changelog vô ích.
7. **Commit message tiếng Việt có dấu** kiểu `fix(curated): Dựng thiệp theo từng hàng, chỉ phát lần đầu và thay Dresscode bằng Album 10 ảnh` — chỉ soạn, không tự commit.
8. **Báo cáo ngắn sau từng phase**, sau đó chủ động tiếp tục phase kế tiếp trong cùng phiên nếu an toàn, không dừng chỉ để hỏi `có tiếp tục không?`.

### Definition of Done (điều kiện KHÔNG THỂ BỎ QUA)

- [ ] 7 section chính xác và cuối là **Album 10 ảnh**, không phải Dresscode.
- [ ] Presentation thật theo dòng/hàng nhỏ, không jump section/camera nhanh.
- [ ] Nhịp hoàn thiện có tính nghệ thuật, thấy rõ từng hàng nhưng có skip.
- [ ] Lần đầu tạo mới mới tự trình diễn; F5/back/tab mới/public xem ngay thành phẩm.
- [ ] Nút Replay chỉ trình diễn lại trên client, không gọi lại AI/re-save.
- [ ] Kết thúc không tự nhảy về đầu.
- [ ] Love Story gọn, cân đối, đọc tốt ở 320–390px; AI copy không dài dòng.
- [ ] Wizard/Preview được nâng chất lượng tổng thể desktop/mobile, không sidebar dày chữ/topbar quá nhiều CTA.
- [ ] AI source status trung thực, không giả vờ tạo content hoặc tiến độ.
- [ ] Giữ layout/palette/ảnh khi sửa, remix, reload; 4 mẫu 10-section không bị regression.
- [ ] Kiểm thử feature + browser evidence thực tế, ghi hạn chế.
- [ ] Context và commit message cập nhật đúng.

---

## 15. LỆNH KHỞI ĐỘNG NGAY

**ĐỪNG CHỈ VIẾT PLAN.**

1. Mở `context.gemini.md` và `.agents/skills/mayhen-invitation-standards/SKILL.md`, đọc phần curated implementation mới nhất.
2. Đọc tất cả file module Curated hiện có, kiểm `git status`, xác minh môi trường test.
3. Tái hiện video lỗi với preview hiện tại; phát hiện vì sao animation bị 4,9s, auto scroll nhảy và replay sai.
4. Sửa **bug section 7 Dresscode → Album 10 ảnh** ngay, bao gồm toàn bộ data/render/tests.
5. Triển khai state machine one-time và hiệu ứng visual-line crafting.
6. Redesign UI wizard/preview và editorial layout, cắt ngắn AI content.
7. Test trực tiếp browser, sửa bugs còn lại, cập nhật context, báo cáo chứng cứ và commit message.

**ƯU TIÊN CHÍNH XÁC, TIN CẬY VÀ GIÁ TRỊ THẨM MỸ THẬT — KHÔNG MÀU MÈ, KHÔNG GIẢ AI, KHÔNG LÀM LẠI TỪ ĐẦU.**
