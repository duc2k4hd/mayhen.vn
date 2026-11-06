# MASTER PROMPT — CLONE THIỆP CƯỚI MÂY HẸN CHUẨN MẪU GỐC

> **Phiên bản:** 2.0 — Visual Fidelity First / Reference-Locked Implementation  
> **Mục tiêu:** Tái dựng giao diện, hình ảnh, màu sắc, font, viền, kích thước, khoảng cách, chuyển động và hành vi của trang tham chiếu với độ trung thực cao nhất có thể; sau đó tích hợp vào nền tảng **Mây Hẹn (mayhen.vn)** bằng Laravel Blade + HTML/CSS/JavaScript thuần.  
> **Cách dùng:** Gửi **toàn bộ tài liệu này** cho AI có khả năng mở trình duyệt, kiểm tra DOM/CSS, tải tài nguyên, lập trình, chạy thử, chụp ảnh màn hình và tạo tệp ZIP. Nếu môi trường thiếu một khả năng, phải công khai phần không thể thực hiện, tuyệt đối không tự nhận đã hoàn tất.

---

## 0. VAI TRÒ VÀ ĐẦU BÀI

Bạn là **Senior Frontend Reverse-Engineering Engineer, Visual QA Engineer và Laravel Blade Architect**. Nhiệm vụ là **tái dựng trung thực một mẫu thiệp cưới có sẵn**, không phải thiết kế một mẫu mới lấy cảm hứng từ nó.

**Thông số dự án:**

| Thuộc tính | Giá trị |
|---|---|
| Nền tảng nhận mẫu | **Mây Hẹn — mayhen.vn** |
| URL tham chiếu | `https://hwd.happyweddingday.vn/red-vip` |
| Tên mẫu trên Mây Hẹn | **Hạnh Phúc Mang Tên Chúng Mình** |
| Slug | `hanh-phuc-mang-ten-chung-minh` |
| Bộ công nghệ | Laravel Blade, HTML5, CSS3, JavaScript ES Modules thuần |
| Tệp ZIP đầu ra | **`Hạnh Phúc Mang Tên Chúng Mình.zip`** |
| Thư mục gốc trong ZIP | `hanh-phuc-mang-ten-chung-minh/` |

### MỆNH LỆNH TỐI CAO: SOURCE OF TRUTH = MẪU GỐC

1. **Mẫu gốc quyết định toàn bộ thẩm mỹ.** Không tự áp dụng Sage Green, Deep Forest, Antique Gold, Quiet Luxury, glassmorphism, gradient, bo góc, bóng đổ, khung vàng, đường viền, họa tiết, hiệu ứng, font hoặc màu mới chỉ vì chúng có vẻ đẹp hơn.
2. **Sao chép đúng thứ tự, cách chia section và hệ phân cấp thị giác của mẫu gốc.** Không tự thêm section, không đổi layout sang thiết kế kiểu AI, không chuẩn hóa tất cả thành card tròn hoặc khối có viền.
3. **Giữ đúng ảnh nguồn khi được phép sử dụng**, tỷ lệ khung, vị trí crop, lớp phủ, nền, họa tiết, ký tự trang trí, biểu tượng và cách kết hợp ảnh/chữ.
4. **Tính năng bắt buộc của Mây Hẹn được tích hợp với mức thay đổi giao diện nhỏ nhất.** Nếu nguồn chưa có phần Ký tên điện tử thì bổ sung phần này theo chính ngôn ngữ thiết kế đã đo đạc từ nguồn, **không** sáng tạo một phong cách khác.
5. **Độ trung thực giao diện cao hơn sở thích cá nhân và phong cách mặc định của AI.** Chỉ thay đổi khi có yêu cầu riêng, hạn chế kỹ thuật có bằng chứng, hoặc nghĩa vụ liên quan đến quyền sử dụng tài nguyên.
6. **Không dùng ảnh chụp toàn trang làm giao diện.** Phải xây dựng HTML có ngữ nghĩa và tương tác thật; screenshot chỉ dùng để phân tích/đối chiếu.
7. **Không tuyên bố giống 100% nếu chưa đối chiếu ảnh thực tế.** Nếu không truy cập được một phần trang, hãy ghi rõ phần đó chưa được xác minh; không tự đoán.

### PHÂN BIỆT HAI YÊU CẦU DỄ XUNG ĐỘT

- **Clone nguyên bản (bắt buộc):** Giữ **đúng bảng màu và mọi chi tiết thị giác của URL tham chiếu**; không áp bảng màu xanh lá/kem/vàng từ prompt cũ.
- **Đổi màu thương hiệu (không nằm trong đầu bài hiện tại):** Chỉ được làm thành **biến thể riêng** khi người dùng yêu cầu rõ. Không pha trộn màu mới vào bản clone chuẩn.

---

## 1. GIAI ĐOẠN A — KHẢO SÁT MẪU THỰC TẾ TRƯỚC KHI CODE

**Không được bắt đầu viết CSS trước khi có dữ liệu khảo sát.**

### A1. Khảo sát bằng trình duyệt thực

1. Mở URL tham chiếu trong Chromium/Chrome với trạng thái sạch.
2. Quan sát **màn hình mở thiệp**, sau đó thực hiện thao tác **chạm mở thiệp**, thử bật/tắt nhạc, cuộn trang từ đầu đến cuối.
3. Thu thập ảnh chụp **full page** và ảnh chụp từng section; chụp riêng các trạng thái mở/đóng, hover, active, gallery, popup, form, mobile menu nếu có.
4. Lặp lại ở các viewport tối thiểu: **320×720, 375×812, 390×844, 430×932, 480×900, 1440×900**. Mẫu ưu tiên mobile thì mobile là chuẩn đối chiếu chính.
5. Đọc DOM, computed styles, network resources và hành vi tương tác để ghi nhận thông số thật. Nếu chưa thể đọc CSS gốc, đo trực tiếp trên screenshot và đánh dấu thông số là *ước lượng*.
6. Kiểm tra lazy-load bằng cách cuộn toàn bộ trang; xác nhận ảnh nền nằm trong CSS, ảnh responsive (`srcset`, `<picture>`), SVG, font và âm thanh.
7. Phân biệt nội dung thiệp với widget bên thứ ba, watermark và nút quảng cáo. Chỉ loại bỏ phần không thuộc thiết kế thiệp hoặc không được phép sử dụng; ghi nhận ngoại lệ.
8. Nếu trang có dữ liệu cá nhân, họ tên, địa chỉ, số tài khoản hoặc ảnh của cặp đôi cụ thể, **không chép mặc định vào dữ liệu khách hàng**. Thay bằng dữ liệu demo trung tính và cho phép Studio thay đổi. Giữ nguyên *kiểu chữ/cách trình bày*, không nhất thiết giữ nguyên thông tin riêng tư.

### A2. Tạo `reference-audit.md` trước khi triển khai

Ghi **từng section theo đúng thứ tự** nguồn, không tự sắp xếp lại. Mỗi section có:

- Tên/điểm bắt đầu-kết thúc, mục đích, thứ tự xuất hiện và ảnh chụp tương ứng.
- Nền: ảnh hay màu thuần; nếu là màu ghi mã HEX/RGBA; nếu có gradient thì ghi chính xác góc, stop và alpha.
- Typography: font family thực tế, font weight, cỡ, line-height, letter-spacing, chữ hoa/thường, căn lề và shadow chữ.
- Box model: width, min/max-width, padding, margin, gap, border width/style/color, radius, shadow, opacity, blend mode.
- Ảnh: file nguồn, kích thước gốc, tỷ lệ, `object-fit`, `object-position`, crop, overlay, mask và vị trí trên màn hình.
- Bố cục: grid/flex/flow, phân lớp, tỷ lệ các phần, thứ tự DOM, khoảng trắng thực tế.
- Chuyển động: trigger, duration, easing, direction, offset; nếu không thấy hiệu ứng thì **không thêm**.
- Hành vi: nút mở thiệp, music, auto-scroll, lịch, album, bản đồ, RSVP, lời chúc, QR và các control thực tế.
- Mức tin cậy: `observed`, `measured` hoặc `estimated`.

**Chốt yêu cầu:** Không được thay các kết quả quan sát bằng tính từ chung chung như “sang trọng”, “hiện đại”, “tinh tế”; phải dùng giá trị và đặc điểm cụ thể từ nguồn.

---

## 2. GIAI ĐOẠN B — TẢI VÀ ĐÓNG GÓI ẢNH/TÀI NGUYÊN GỐC

### B1. Quy tắc tải tài nguyên

1. Liệt kê toàn bộ tài nguyên **thực sự được trang thiệp dùng để hiển thị**: ảnh (`img`, `picture`, CSS `background-image`), texture, họa tiết, sticker, SVG, icon, font (`woff2`, `woff`, `ttf` nếu được phép), audio và những file liên quan.
2. Với tài nguyên **được quyền sử dụng/tái phân phối**, tải **file gốc đúng URL thực**, ưu tiên độ phân giải cao nhất phù hợp; không lấy ảnh screenshot để thay thế nếu file nguồn còn truy cập được.
3. Tài nguyên có thể đến từ nhiều host/CDN khác nhau. Thu thập đúng file theo network trace, nhưng **mã nguồn xuất ra chỉ tham chiếu file nội bộ**.
4. Lưu trong `assets/` và đổi tên mô tả, tránh tên vô nghĩa hay trùng tên. Ví dụ: `hero-background.webp`, `section-family-decoration.webp`, `gallery-01.webp`, `font-heading.woff2`, `icon-music.svg`.
5. Có thể chuyển raster hợp lệ sang **WebP** với chất lượng đủ để không làm khác màu/độ nét; giữ alpha, orientation và tỷ lệ ảnh. Không kéo giãn, không cắt sai, không ép mọi ảnh thành 1:1.
6. **Không thay ảnh gốc bằng ảnh AI tự sinh, ảnh stock ngẫu nhiên, SVG vẽ lại hoặc gradient** nếu file nguồn có thể lấy hợp lệ và mục tiêu là clone chính xác.
7. Nếu ảnh bị chặn, không đủ quyền sử dụng hoặc không truy cập được: không vượt cơ chế bảo vệ, không tải lén; ghi `missing`/`requires-permission` vào manifest và dùng placeholder **chỉ cho phần chưa được cấp phép**, đánh dấu rõ trong báo cáo. Không tự nhận ZIP có đầy đủ ảnh gốc khi còn thiếu.
8. Với ảnh minh họa của cặp đôi, chỉ đóng gói khi có quyền sử dụng; **mọi vị trí ảnh cặp đôi trong thiệp phải chỉnh sửa được bằng Studio**. Không nhúng cố định ảnh cá nhân không thể thay.
9. Nếu nguồn không có nhạc hoặc không được phép phân phối file nhạc, không tạo MP3 rỗng/giả; nêu thiếu tài nguyên và cung cấp cơ chế thay file nhạc hợp lệ sau này.
10. Không nhúng hotlink từ website nguồn hoặc CDN vào bất kỳ HTML, CSS, JS hay SVG nào của bản xuất.

### B2. Manifest bắt buộc

Tạo `assets-manifest.json` với tối thiểu:

- `id`, `type`, `sourceUrl`, `localPath`, `usedInSections`.
- `originalWidth`, `originalHeight`, `outputWidth`, `outputHeight` nếu áp dụng.
- `sourceFormat`, `outputFormat`, `sha256`, `downloadStatus`.
- `rightsStatus`: `authorized`, `requires-permission`, `unknown`.
- `notes`: xử lý/chuyển đổi/thất bại và lý do.

**Tuyệt đối không đánh dấu `authorized` nếu chưa có cơ sở.** Nếu không có quyền với một tài nguyên thì chỉ lưu thông tin tham chiếu và dùng tài nguyên thay thế có quyền hợp lệ; không đóng gói trái phép.

### B3. Kiểm tra sau tải

- Đảm bảo 100% asset đã kê khai `downloadStatus=downloaded` thực sự tồn tại và mở được.
- Dò toàn bộ chuỗi `http://`, `https://`, `url(...)`, `@import`, `src`, `srcset`, `poster`, `href` trong mã nguồn để không còn asset ngoại.
- Các **liên kết hành động** (Google Maps, chia sẻ, thanh toán bên ngoài) có thể hướng ra website khác nếu thật sự là chức năng, nhưng **không được dùng link ngoài để tải resource giao diện**.
- ZIP phải chứa chính xác các tệp mà code tham chiếu, kể cả font, icon và ảnh nền ẩn trong CSS.

---

## 3. GIAI ĐOẠN C — DỰNG GIAO DIỆN TRUNG THỰC, KHÔNG “AI STYLE”

### C1. Bảo toàn 100% ngôn ngữ thị giác gốc

**CẤM thực hiện những thay đổi sau khi không có bằng chứng từ ảnh/DOM gốc:**

- Đổi màu nền, màu chữ, màu viền, màu nút hoặc áp một palette khác.
- Thêm/giảm border, bo góc, box-shadow, glow, glass, blur, gradient, texture hoặc overlay.
- Đổi font sang serif/cursive/sans khác; thay font vì “trông thanh lịch hơn”.
- Tự tạo card, container, đường phân cách, icon, tem sáp, hoa lá hoặc pattern.
- Tự sửa khoảng cách, tăng/giảm padding theo cảm tính; căn giữa khi nguồn căn trái hoặc ngược lại.
- Đổi tỷ lệ khung ảnh, vị trí crop, trật tự ảnh/chữ, chiều cao section hoặc cách phân lớp.
- Tự thêm hiệu ứng lá rơi, tuyết, đom đóm, parallax, tim bay nếu nguồn không có.
- Xóa họa tiết của bản gốc chỉ vì khó code; thay bằng emoji hoặc CSS đơn giản hơn.
- Tự đổi nội dung trang thành thiết kế landing page/card thông thường.

**Nếu nguồn có chi tiết phức tạp:** Ưu tiên tái sử dụng asset đúng quyền sử dụng hoặc tái dựng bằng CSS/SVG trung thực. Không dùng SVG “trang trí đại khái”.

### C2. CSS theo hệ token **trích từ mẫu**, không phải token AI tự nghĩ

Tạo `:root` với các biến `--mayhen-invitation-*` lấy từ giá trị đo trên nguồn. Ví dụ **chỉ minh họa cách đặt tên, không phải bảng màu mặc định**:

```css
:root {
  --mayhen-invitation-bg: /* HEX đo từ bản gốc */;
  --mayhen-invitation-text: /* HEX đo từ bản gốc */;
  --mayhen-invitation-accent: /* HEX đo từ bản gốc */;
  --mayhen-invitation-heading-font: /* font gốc, self-host */;
  --mayhen-invitation-body-font: /* font gốc, self-host */;
}
```

Đo và thực thi đúng border width, radius, shadow, typography và spacing theo section; **không** ép toàn bộ mẫu vào một bộ radius/shadow thống nhất nếu bản gốc không như vậy.

### C3. Triển khai theo thứ tự bắt buộc

1. Dựng khung section đúng nguồn.
2. Đặt đúng asset và crop.
3. Khớp màu, kiểu nền, border, trang trí và typography.
4. Khớp spacing, kích thước, tỉ lệ, vị trí và responsive.
5. Khớp animation và hành vi sẵn có.
6. Sau cùng mới tích hợp dữ liệu động, RSVP, Signature và Studio.

**Lưu ý:** Không được ưu tiên làm chức năng trước rồi phỏng đoán giao diện từ cảm nhận.

---

## 4. QUY ĐỊNH KỸ THUẬT MÂY HẸN

### D1. 100% Native Frontend

- Dùng Laravel Blade cho server template, **HTML5 + CSS3 + Vanilla JavaScript ES Modules** cho frontend.
- **Không dùng** React, Vue, Angular, Tailwind, Bootstrap, jQuery, GSAP, Anime.js, AOS, Swiper, Slick, UI kit hoặc thư viện animation/form/carousel ngoài.
- Không gọi CDN/font server/JS ngoài; không `@import` font URL ngoài.
- Có thể dùng `fetch`, `IntersectionObserver`, Pointer Events, Canvas API, Web Animations API khi cần, miễn là trình duyệt hỗ trợ và có fallback hợp lý.

### D2. Class prefix, khả năng tái sử dụng

- Tất cả **class tự tạo** trong template, CSS và JS phải bắt đầu bằng `mayhen_invitation_`.
- Đặt tên kiểu BEM có namespace: `mayhen_invitation_hero`, `mayhen_invitation_hero__title`, `mayhen_invitation_hero--opened`.
- Không dùng class chung như `.container`, `.button`, `.active`, `.hidden`, `.card`, `.title` khiến xung đột giao diện của website Mây Hẹn.
- Dùng ID chỉ khi cần để kết nối `label/for`, `aria-*` hoặc `canvas`; tuyệt đối không dùng ID để style hàng loạt.
- Selector phải scope trong root `.mayhen_invitation_root`. Không thay đổi CSS toàn cục của layout Mây Hẹn.

### D3. Responsive Flow Layout

- Khung thiệp chính mặc định: `width: 100%; max-width: 480px; margin-inline: auto; position: relative;` **nếu tương thích với bố cục thực của bản tham chiếu**.
- Dùng Grid/Flexbox/normal flow; `rem`, `%`, `min()`, `max()`, `clamp()`, `aspect-ratio` và breakpoint dựa trên hành vi nguồn.
- Không dùng absolute positioning để “xếp cả trang” với tọa độ pixel cứng. **Cho phép** `position: absolute` giới hạn ở các lớp trang trí/overlay thực sự chồng lớp theo mẫu, neo tương đối với container và không phá flow.
- Không làm méo bố cục của bản gốc chỉ để giữ `max-width: 480px`; nếu phát hiện xung đột, nêu rõ điểm khác và dùng phương án ít sai lệch nhất.
- Không horizontal overflow ngoài chủ đích. Test ít nhất các viewport ở mục A1 và cả màn hình dọc/ngang nếu nguồn có hỗ trợ.
- Lưu ý safe-area và nút điều khiển khi hiển thị trên điện thoại.

### D4. Blade contract

- `template.blade.php` phải chứa `@extends('client.layouts.invitation')` và khai báo `@section`/`@push` theo cấu trúc layout mà dự án **thực sự hỗ trợ**.
- Không tự giả định biến Blade, route, controller, API, `@stack`, helper `$resolveAsset(...)` hoặc cấu trúc `schema.json` đã có sẵn nếu chưa được cung cấp.
- Nếu contract dự án chưa có, triển khai adapter rõ ràng, ghi `ASSUMPTION` trong README và cung cấp fallback cục bộ. Không nói “đã tích hợp production” nếu chưa test được trong Mây Hẹn.
- Escape dữ liệu đầu vào bằng Blade đúng chuẩn; không đưa dữ liệu khách hàng chưa kiểm duyệt vào `innerHTML`.
- Nếu có nhiều instance thiệp/trang Studio, hạn chế biến toàn cục và tránh trùng DOM selector.

---

## 5. MÀN MỞ THIỆP, ÂM NHẠC VÀ STUDIO EMBED

### E1. Opening Screen

- **Bắt chước màn mở đầu và thao tác mở thiệp từ nguồn**: cấu trúc, ảnh, màu, viền, chữ, độ mờ, hình động, chiều cao và trigger.
- Không tự thêm phong bì 3D/cánh cửa song hỷ nếu bản gốc không có; chỉ sử dụng giải pháp khác khi người dùng yêu cầu riêng.
- Dùng class tối thiểu: `mayhen_invitation_opening`, `mayhen_invitation_open_btn` và các class con đúng namespace.

### E2. Nhạc

- Khi khách **tự chạm mở thiệp**, có thể gọi `audio.play()` để bật nhạc; xử lý Promise bị từ chối và cung cấp nút play/pause đúng thiết kế nguồn.
- Không hứa autoplay trước tương tác người dùng (trình duyệt có thể chặn).
- File nhạc được cấp phép nằm nội bộ tại `assets/music.mp3`, hoặc dùng một tệp âm thanh hợp lệ khác được ghi trong manifest.
- Nếu không có quyền với nhạc gốc, không tải/đóng gói và không thay bằng nhạc ngẫu nhiên mà không báo.

### E3. Studio Editor / Embedded Preview

- Hỗ trợ cờ `$isEmbed` (mặc định `false` nếu dự án chưa truyền).
- Khi `true`: **ẩn hoàn toàn opening**, hiển thị thiệp ở trạng thái đã mở, không chặn `html`/`body`, không tự autoplay, không tạo overlay ăn pointer events.
- Không thêm `no-scroll` hoặc `overflow: hidden` lên `html`/`body` của Studio.
- Khi `false`: mở thiệp theo đúng nguồn; nếu khóa cuộn trong lúc opening thì chỉ khóa đúng lúc, tự giải phóng khi mở, khi lỗi và khi destroy.
- Thay đổi trạng thái phải an toàn cả khi Studio hot-reload/preview nhiều lần; không bind listener hoặc timer lặp vô hạn.

---

## 6. RSVP VÀ SỔ KÝ TÊN — TÍNH NĂNG MÂY HẸN BẮT BUỘC

Cần đủ **RSVP Form** và **Signature Canvas**, dù bản tham khảo có thể thiếu một tính năng.

### F1. Nguyên tắc hình ảnh tuyệt đối

- **KHÔNG có ảnh cặp đôi, ảnh người hoặc ảnh stock có người ở bất kỳ phần nào của RSVP/Signature**, kể cả `background-image`, pseudo-element, sticker hoặc nền canvas.
- Chỉ dùng nền giấy, màu, đường nét, họa tiết hình học/hoa văn, icon SVG tự tạo hoặc tài nguyên trang trí hợp lệ.
- Nếu bản gốc có ảnh người bên trong RSVP thì **ngoại lệ này được loại bỏ** và phải nêu trong `visual-audit.md`; mọi chi tiết còn lại vẫn bám sát mẫu.
- Ưu tiên reuse token/viền/font/background/padding/cách sắp chữ **đo từ các section liền kề**, không tạo một phong cách “AI card” mới cho form.

### F2. RSVP thực sự hoạt động

- Các trường tối thiểu: tên khách, trạng thái tham dự, số người (nếu có), lời nhắn (nếu có), thông tin sự kiện/địa điểm tương ứng.
- Validation, trạng thái loading/success/error, hỗ trợ bàn phím và nhãn accessible.
- Dùng API endpoint **được cấu hình từ dự án**, CSRF token cho form Laravel, xử lý lỗi đúng thực tế; không hard-code URL backend bịa ra.
- Nếu chưa có backend thì ghi rõ `adapter required`, cung cấp sự kiện/hook để Studio kết nối; **không hiển thị “gửi thành công” giả**.

### F3. Canvas ký tên

- Hỗ trợ chuột, bút và cảm ứng bằng Pointer Events, chuẩn hóa tọa độ theo canvas bounds và `devicePixelRatio`.
- Hỗ trợ xóa, viết lại, xem trước và xuất chữ ký PNG/WebP (hoặc blob/data URL theo contract backend).
- Tránh mất nét khi resize; không tự vẽ chữ ký mẫu hoặc sử dụng ảnh cặp đôi làm nền.
- Nếu chưa có endpoint lưu, cung cấp adapter/event rõ ràng và thông báo trạng thái thực.

### F4. Vị trí phần bắt buộc chưa có ở nguồn

- Giữ nguyên trật tự và bố cục **tất cả** section có sẵn.
- Chỉ chèn section thiếu ở điểm hợp lý ít ảnh hưởng nhất đến bố cục tổng thể, nêu rõ đó là *Mây Hẹn-only section*.
- Không đẩy/viết lại các section gốc một cách tùy tiện để “làm cân đối”.

---

## 7. LIVE SYNC VỚI STUDIO TẠO THIỆP

### G1. `data-sync-target` bắt buộc

Gắn vào **mọi lần xuất hiện tương ứng trong HTML**, bao gồm text, hình ảnh, QR, background ảnh chỉnh sửa được. Dùng chính xác các key có sẵn của Studio, tối thiểu:

```text
groom_name
bride_name
couple_names
groom_father
groom_mother
bride_father
bride_mother
wedding_date_digits
wedding_lunar_date
wedding_day
wedding_month
wedding_year
calendar_month_year
wedding_calendar
party_time
party_venue
event_party_address
groom_bank_account
groom_bank_name
qr_groom_image
hero_photo_url
```

Với các thông tin xuất hiện nhiều nơi, **mọi occurrence phải được đồng bộ**, không chỉ ô hiển thị đầu tiên. Nếu trang nguồn có hai gia đình/hai tiệc/hai QR, mở rộng schema thành các field tách biệt (`*_groom`, `*_bride` hoặc theo contract Studio thực có), tránh ghi đè cùng một value vào hai vị trí khác nghĩa.

### G2. Đồng bộ ngày — lịch — đếm ngược

Cung cấp 2 API toàn cục chính xác:

```js
window.updateMayhenCountdown = function (targetDate) {
  // Cập nhật ngay, quản lý timer an toàn, cleanup timer cũ.
};

window.renderMayhenWeddingCalendar = function (targetDate) {
  // Render đúng tháng/năm, số ngày, thứ và ngày cưới đang chọn.
};
```

**Yêu cầu hành vi:**

- Thay đổi ngày trong Studio cập nhật ngay: text ngày, thứ, tháng, năm, điểm đánh dấu trên lịch và countdown.
- Xử lý đúng năm nhuận, ngày đầu tháng, số ngày trong tháng và thứ Hai–Chủ nhật (nếu nguồn sử dụng định dạng đó).
- Xử lý timezone **Asia/Ho_Chi_Minh**, không parse chuỗi ngày mơ hồ theo trình duyệt.
- Khi ngày qua đi, countdown về 0 hoặc trạng thái quá hạn theo quy tắc được chọn; không hiển thị số âm.
- Các ô ngày/lịch không được hard-code như HTML tĩnh từ mẫu nguồn.
- Nội dung âm lịch phải đến từ dữ liệu hoặc logic xác thực; không tự tính sai hoặc bịa ngày âm.

### G3. Ảnh và gallery có thể chỉnh sửa

- `hero_photo_url`, các ảnh album và ảnh riêng của hai người phải được đưa vào schema để người dùng thay từ Studio.
- QR ngân hàng là dữ liệu động; hỗ trợ cả hai bên nếu mẫu có hai khối.
- Không nhúng ảnh khách hàng cụ thể thành background cố định không thay được.

---

## 8. HIỆU ỨNG — CHỈ TÁI TẠO NHỮNG GÌ NGUỒN CÓ

- Xác định hiệu ứng thực tế trước khi viết keyframe.
- Ưu tiên CSS transform/opacity để animation nhẹ và không gây reflow; dùng JS điều khiển khi cần hành vi thật.
- Nếu nguồn có reveal-on-scroll, xác định offset và thứ tự xuất hiện; dùng `IntersectionObserver` với fallback.
- Nếu nguồn không có animation tại một section: để **tĩnh**, không tự thêm bobbing, glow, parallax, tuyết/lá rơi hoặc hiệu ứng lấp lánh.
- Hỗ trợ `prefers-reduced-motion: reduce` một cách không làm ẩn nội dung.
- Không tuyên bố “60 FPS bảo đảm” khi chưa đo thực tế; ghi lại điểm nghẽn performance nếu có.

---

## 9. KIỂM THỬ VISUAL REGRESSION — BẮT BUỘC TRƯỚC KHI XUẤT ZIP

### I1. Ảnh tham chiếu và ảnh local

Tạo thư mục `qa/` chứa ít nhất:

```text
qa/
  reference/
    mobile-opening.png
    mobile-fullpage.png
    desktop-fullpage.png
  local/
    mobile-opening.png
    mobile-fullpage.png
    desktop-fullpage.png
  comparison/
    mobile-side-by-side.png
    mobile-diff.png
    desktop-side-by-side.png
    desktop-diff.png
```

Có thể thêm ảnh section/breakpoint. Nếu không chụp được ảnh nguồn, ghi rõ **không thể xác minh pixel** và tuyệt đối không dựng ảnh “reference” giả.

### I2. Quy trình đối chiếu

1. Chụp bản gốc và bản local ở **cùng viewport, cùng trạng thái thiệp, cùng mức scroll, cùng mức zoom và cùng dữ liệu demo hợp lệ nếu có thể**.
2. Chờ font, ảnh, lazy-load ổn định; giảm/đồng bộ animation khi chụp để tránh sai khác do thời điểm.
3. So sánh side-by-side và ảnh diff; dùng công cụ SSIM, MAE hoặc pixel diff **nếu môi trường hỗ trợ**. Loại trừ rõ ràng các vùng dữ liệu động/ảnh chưa được cấp quyền.
4. Rà bằng mắt theo thứ tự: **bố cục tổng thể → ảnh/crop → màu nền → font → kích thước chữ → border/shadow/radius → spacing → icon → hiệu ứng**.
5. Sửa từ lỗi thị giác lớn nhất, chụp lại, lặp cho đến khi không còn sai khác đáng kể có thể khắc phục.
6. Kiểm tra opening, ảnh, lịch, RSVP, signature, music, album/gallery, QR, phản hồi lỗi API và Studio embed.

### I3. Tiêu chí nghiệm thu

- Không có section gốc bị thiếu, đổi thứ tự hoặc bị thay bằng thiết kế tùy ý.
- Không có màu/viền/bo góc/shadow/gradient “tự sáng tác” không tìm thấy trong nguồn.
- Các ảnh được phép sử dụng nằm local, đúng tỷ lệ và crop.
- Chữ/họa tiết/nút được đặt đúng nhóm vị trí với bản gốc ở các breakpoint chính.
- Không horizontal overflow, nút không bị che, không section trắng do lỗi animation/embed.
- Không console error, JS error, 404 asset hoặc lỗi syntax Blade/JSON.
- RSVP/Signature đúng hành vi, có phản hồi thành công/thất bại thật; Studio đồng bộ các field.
- Ghi rõ số lượng ảnh nguồn tìm thấy, số lượng tải thành công, số lượng thiếu, số lượng file chuyển WebP.
- `visual-audit.md` liệt kê **mọi sai khác còn lại**, nguyên nhân, mức ảnh hưởng và trạng thái (`fixed`, `acceptable-exception`, `unresolved`).

**Nguyên tắc xuất bản:** Nếu không có công cụ chạy browser hoặc đo diff, vẫn được cung cấp phần mã đã hoàn thành nhưng phải ghi rõ `Visual QA: NOT VERIFIED`; không được viết “pixel perfect 100%”.

---

## 10. CẤU TRÚC ZIP BẮT BUỘC

```text
Hạnh Phúc Mang Tên Chúng Mình.zip
└── hanh-phuc-mang-ten-chung-minh/
    ├── template.blade.php
    ├── styles.css
    ├── script.js
    ├── schema.json
    ├── assets-manifest.json
    ├── reference-audit.md
    ├── visual-audit.md
    ├── README.md
    ├── assets/
    │   ├── images/
    │   │   ├── ...webp
    │   ├── fonts/
    │   │   ├── ...woff2
    │   ├── icons/
    │   │   ├── ...svg
    │   └── music.mp3                # Chỉ khi có nguồn hợp lệ/được cấp phép
    └── qa/
        ├── reference/
        ├── local/
        └── comparison/
```

**Quy định riêng cho từng file:**

| File | Nội dung |
|---|---|
| `template.blade.php` | Trang Blade hoàn chỉnh, đúng các section nguồn và đúng contract Mây Hẹn, escape dữ liệu, có `data-sync-target` đầy đủ. |
| `styles.css` | CSS thuần, prefix `mayhen_invitation_`, bố cục/mobile chính xác, các font `@font-face` nội bộ và hiệu ứng gốc. |
| `script.js` | ES Modules thuần: mở thiệp, nhạc, gallery nếu có, RSVP, ký tên, countdown, calendar, cleanup và embed. |
| `schema.json` | Các nhóm/trường Studio (tên cô dâu/chú rể, gia đình, sự kiện, ảnh, gallery, QR, nhạc, v.v.). Nếu schema thật chưa được cung cấp, đánh dấu **proposed schema** và mô tả adapter cần thiết. |
| `assets-manifest.json` | Ánh xạ URL nguồn ↔ file local, hash, tình trạng tải, quyền sử dụng và lỗi thiếu asset. |
| `reference-audit.md` | Phân tích từng section, bảng màu/font/ảnh, thông số layout và hành vi mẫu. |
| `visual-audit.md` | Kết quả so sánh screenshot, danh sách lỗi đã sửa/chưa sửa và ngoại lệ. |
| `README.md` | Cách tích hợp Mây Hẹn, đường dẫn asset, biến Blade, endpoint cần nối, cách chạy thử, giới hạn và quyền sử dụng. |
| `assets/` | Toàn bộ tài nguyên hợp lệ được sử dụng bởi bản clone, không có link nóng/CDN. |
| `qa/` | Screenshot nguồn, bản chạy local và ảnh đối chiếu nếu công cụ thực hiện được. |

**Lưu ý:** Không được tạo file giả/empty placeholder để trông như đủ cấu trúc. Nếu thiếu một asset/file QA, ghi rõ tình trạng trong README và báo cáo.

---

## 11. QUY TRÌNH THỰC HIỆN THEO THỨ TỰ, KHÔNG BỎ BƯỚC

1. **Inspect:** Truy cập URL, ghi lại toàn bộ section/state và thông số thiết kế.
2. **Capture:** Lưu ảnh screenshot nguồn ở nhiều viewport.
3. **Inventory:** Lập danh sách các ảnh/asset/font/music từ DOM, CSS và network.
4. **Download:** Tải nguồn được phép sử dụng, chuyển WebP khi phù hợp, kiểm tra file, ghi manifest.
5. **Implement:** Viết Blade + CSS + JS native theo **source lock**, không tự sáng tạo.
6. **Integrate:** Ánh xạ Studio schema, Live Sync, embed, RSVP và Signature.
7. **Test:** Chạy local, test chức năng, test responsive, kiểm tra console, asset và accessibility cơ bản.
8. **Compare:** Chụp local screenshot, đối chiếu side-by-side/diff, sửa lỗi rồi so sánh lại.
9. **Audit:** Ghi chênh lệch thị giác, ngoại lệ về quyền tài nguyên và điểm chưa được xác thực.
10. **Package:** Đóng ZIP, kiểm tra cấu trúc, kiểm tra integrity, giải nén thử và xác minh toàn bộ asset được tham chiếu đều tồn tại.

Không bỏ qua bước 8 để chuyển sang bước 10 chỉ vì giao diện “nhìn có vẻ ổn”.

---

## 12. ĐẦU RA PHẢI TRẢ CHO NGƯỜI DÙNG

**Không chỉ in code vào chat.** Hãy trực tiếp tạo file trong môi trường có quyền ghi file và xuất:

1. Link tải ZIP **`Hạnh Phúc Mang Tên Chúng Mình.zip`** chứa toàn bộ bộ mã nguồn, asset hợp lệ và tài liệu audit.
2. Bản preview/screenshot local tiêu biểu, nếu có thể thực thi browser.
3. Tóm tắt: số section clone, số asset tải được/tổng số, số tài nguyên thiếu, tính năng đã chạy, tình trạng Studio integration và visual QA.
4. Danh sách ngắn các điểm chưa giống/không hoàn thành, với lý do cụ thể và không che giấu hạn chế.

**Điều kiện kết thúc:** Chỉ nói **“Hoàn thành”** khi ZIP thực sự đã được tạo, có thể giải nén, không thiếu mã nguồn quan trọng và các kiểm tra được ghi rõ. Nếu công cụ không hỗ trợ tải nguồn, render, kiểm thử hoặc tạo ZIP: cung cấp sản phẩm thật đã làm được và ghi rõ phần còn lại; **không bịa đường dẫn tải, ảnh, thành tích QA hoặc asset**.

---

## 13. CHECKLIST CHỐT TRƯỚC KHI GỬI

- [ ] Đã quan sát bản gốc ở trạng thái mở/đóng và cuộn hết trang.
- [ ] Đã đối chiếu chính xác màu sắc, font, viền, radius, shadow, background, ảnh và crop.
- [ ] Không tự đưa phong cách Quiet Luxury/Sage Green/AI-style vào bản clone gốc.
- [ ] Không tự thêm/bớt/đổi trật tự section của nguồn, ngoại trừ tính năng Mây Hẹn bắt buộc được ghi chú.
- [ ] Đã tải và đóng gói **toàn bộ asset có quyền sử dụng**; asset thiếu được ghi rõ.
- [ ] Không còn URL asset ngoại/CDN trong bản local.
- [ ] Blade, CSS, JS và schema đúng namespace và contract đã xác minh.
- [ ] Opening hoạt động; Studio embed không trắng/tràn/khóa cuộn.
- [ ] Countdown và Calendar cập nhật live; múi giờ đúng.
- [ ] RSVP và Signature có chức năng thực, không có ảnh cặp đôi trong hai phần đó.
- [ ] Studio có thể thay ảnh cặp đôi, QR và nội dung khách hàng.
- [ ] Đã so sánh screenshot nếu môi trường hỗ trợ, có báo cáo sai khác minh bạch.
- [ ] Đã đóng và giải nén kiểm tra `Hạnh Phúc Mang Tên Chúng Mình.zip`.

> **NHẮC LẠI CHO AI THỰC THI:** Người dùng **không cần một giao diện mới trông đẹp theo kiểu AI**. Người dùng cần **đúng thiết kế đã gửi từ A–Z**, chỉ thay phần cấu trúc mã nguồn để tương thích Mây Hẹn và bổ sung các chức năng bắt buộc theo phong cách gốc. **Không tự sáng tạo màu sắc, viền, font, shadow, animation, khung hoặc ảnh.**
