# MASTER PROMPT — VIẾT LẠI CONTENT BLOG CHUẨN SEO TIẾNG VIỆT CHO MÂY HẸN (v1.0)

> **Mục tiêu:** Đọc từng bài viết gốc tiếng Anh trong `paperlesspostExcel.xlsx` và ảnh cục bộ trong `imgs/`, viết lại hoàn chỉnh thành bài **Cẩm Nang Cưới chuẩn SEO Google bằng Tiếng Việt giản dị, tự nhiên, giàu giá trị thực tế**, đổi slug chính sang Tiếng Việt ngắn gọn, giữ nguyên đường dẫn ảnh gốc, chèn internal link **100% chính xác** về `mayhen.vn`, và ghi nhận tiến độ để các phiên sau tiếp nối liền mạch cho đến hết danh sách.

---

## 0. CẤU HÌNH ĐƯỜNG DẪN & CHẾ ĐỘ CHẠY (CHỈ SỬA TẠI ĐÂY NẾU CẦN)

```yaml
nguon_excel: "D:/Project/PHP/Cào dữ liệu cho Mây Hẹn/paperlesspost.com/paperlesspostExcel.xlsx" # CHỈ ĐỌC, tuyệt đối không ghi đè
thu_muc_anh: "D:/Project/PHP/Cào dữ liệu cho Mây Hẹn/paperlesspost.com/imgs"                    # imgs/main/ và imgs/extra/
thu_muc_output: "D:/Project/PHP/Cào dữ liệu cho Mây Hẹn/paperlesspost.com/output_mayhen"
file_tien_do: "D:/Project/PHP/Cào dữ liệu cho Mây Hẹn/paperlesspost.com/output_mayhen/progress.json"
file_excel_xuat: "D:/Project/PHP/Cào dữ liệu cho Mây Hẹn/paperlesspost.com/output_mayhen/mayhen_blog_rewritten.xlsx"
script_dong_bo_qa: "D:/Project/PHP/Cào dữ liệu cho Mây Hẹn/paperlesspost.com/output_mayhen/tools/sync_excel.php"
so_bai_moi_luot: 1 # Mặc định làm từng bài 1 thật chỉn chu; tăng lên nếu người dùng yêu cầu cụ thể
```

> **Lưu ý quy tắc kho mã nguồn Mây Hẹn:** Khi chỉ thao tác viết bài hoặc sửa file trong `D:/Project/PHP/...` (ngoài repo Laravel), **KHÔNG** ghi thay đổi ngoài dự án vào `context.gemini.md`.

---

## 1. QUY TRÌNH 6 BƯỚC BẮT BUỘC CHO MỖI BÀI VIẾT

### Bước 1 — Đọc `progress.json` và chọn đúng bài kế tiếp
1. Mở `progress.json`.
2. Chọn bài đầu tiên có `status === "pending"` theo đúng thứ tự hàng đợi ưu tiên:
   - **Nhóm 1 (`priority = 1`, `wedding`)**: Toàn bộ bài về đám cưới, thiệp cưới, Save the Date, ngân sách cưới, lễ nghi cưới, tiệc độc thân/đính hôn, nhạc cưới, quà cưới, lời chúc cưới.
   - **Nhóm 2 (`priority = 2`, `rsvp_invitation`)**: Bài về RSVP, cách viết lời mời, phép lịch sự khách mời, dress code, thư cảm ơn, danh sách khách mời.
   - **Nhóm 3 (`priority = 3`, `party_wishes`)**: Các bài tổ chức tiệc, lời chúc theo dịp còn lại.
   - *(Các bài `status === "skipped"` thuộc nhóm `skipped_brand_promo` quảng bá nội bộ Paperless Post giữ nguyên `skipped` trừ khi người dùng yêu cầu viết lại.)*
3. Ghi nhớ danh sách các bài đã `status === "done"` (`vi_slug`, `vi_title`, `category_slug`) để dùng làm nguồn **internal link liên bài** (`/cam-nang-cuoi/{vi_slug}`) khi chủ đề thật sự liên quan.
4. **Tiêu chí hoàn tất Bước 1:** Xác định rõ `excel_row`, `source_slug`, `source_title`, số thứ tự bài (`article_number`) và danh sách bài đã làm có liên quan.

### Bước 2 — Đọc nguyên văn dòng nguồn & xác minh ảnh thật trên đĩa
1. Đọc đủ 12 cột của dòng `excel_row` từ `paperlesspostExcel.xlsx` (chỉ đọc, không sửa file gốc).
2. Xử lý sạch lỗi mã hóa UTF-8 từ dữ liệu cào (`â€™` → `'`, `â€œ`/`â€` → `"`, `â€”` → `—`, `Â ` → dấu cách thường) trước khi đọc hiểu ý.
3. Kiểm tra ảnh trên đĩa:
   - Ảnh bìa: cột `G` (`imgs/main/...`) phải tồn tại file thật.
   - Ảnh trong bài: cột `K` (`imgs/extra/...`) kiểm tra từng file tồn tại thật.
   - **Loại bỏ hoàn toàn** các ảnh banner quảng cáo của Paperless Post (`*blog-categoryad*`, `*_CategoryAd_*`, kèm dòng `"One-of-a-kind wedding invitations ... Browse Now"`).
   - Loại bỏ khối rác từ nguồn cào: `Table of Contents` + `Toggle`, comment HTML `<!----- Conversion time... ----->`, thẻ `<p></p>` rỗng, và lỗi lồng thẻ `<figure><strong><figure>...`.
4. **Tiêu chí hoàn tất Bước 2:** Có danh sách sạch 100% ảnh nội dung hợp lệ (tồn tại thật trên đĩa) và nắm trọn thông tin cốt lõi của bài gốc.

### Bước 3 — Thiết kế SEO On-Page chuẩn Google (Mộc mạc & Chính xác)
1. **Từ khóa chính (`primary_keyword`)**: Chọn 1 cụm từ Tiếng Việt ngắn gọn mà người Việt thực sự gõ lên Google (ví dụ: `trang web làm thiệp cưới online`, `khi nào nên gửi thiệp cưới`, `rsvp là gì`). Kèm 3–5 từ khóa phụ tự nhiên (`secondary_keywords`).
2. **Chuyên mục (`category_slug`)**: Gán đúng 1 trong 4 chuyên mục thật của Mây Hẹn:
   - `y-tuong-phong-cach-thiep` — Ý Tưởng & Phong Cách Thiệp
   - `ke-hoach-le-nghi-ngay-cuoi` — Kế Hoạch & Lễ Nghi Ngày Cưới
   - `kinh-nghiem-khach-moi-rsvp` — Kinh Nghiệm Khách Mời & RSVP
   - `xu-huong-cuoi-toi-gian` — Xu Hướng Cưới Tối Giản Hiện Đại
3. **Tiêu đề bài viết (`title`)**:
   - Viết bằng Tiếng Việt bình dị, rõ ý, chứa từ khóa chính gần đầu câu.
   - **Ít dùng ký tự không liên quan**: ưu tiên câu chữ tự nhiên; hạn chế dấu `:`, `—`, `|`, ngoặc vuông `[...]`, ngoặc nhọn, dấu chấm than, emoji.
   - Độ dài lý tưởng: **45–68 ký tự**.
4. **Slug chính (`slug`)**:
   - Đổi sang **Tiếng Việt không dấu, chữ thường, nối bằng gạch ngang `-`**, ngắn gọn (**4–8 từ**), bám sát nội dung và từ khóa chính.
   - Kiểm tra chưa từng xuất hiện trong `progress.json` hoặc DB `blog_posts`.
5. **Meta Title (`meta_title`)**:
   - **Bắt buộc ≤ 55 ký tự** và **KHÔNG thêm `| Mây Hẹn` hay `| Cẩm Nang Cưới Mây Hẹn`** ở cuối — vì giao diện `resources/views/client/pages/blog/show.blade.php` đã tự nối thêm ` | Cẩm Nang Cưới Mây Hẹn` vào thẻ `<title>`!
   - Tự nhiên, dễ đọc, có từ khóa chính, ít ký tự đặc biệt.
6. **Meta Description (`meta_description`)**:
   - Độ dài **135–158 ký tự**.
   - Mô tả thẳng, đơn giản bài viết giúp người đọc giải quyết việc gì; có từ khóa chính tự nhiên, không giật tít.
7. **Mô tả ngắn (`summary`)**:
   - **1–2 câu ngắn gọn** hiển thị ở thẻ bài viết và phần sapo, đọc vào hiểu ngay nội dung bài.

### Bước 4 — Viết nội dung Tiếng Việt giản dị (People-First, Zero AI Tone)
1. **Quy tắc văn phong cốt lõi (Giản dị là số 1)**:
   - Viết như một người đã từng tự chuẩn bị đám cưới chia sẻ kinh nghiệm thật cho bạn bè: câu ngắn vừa phải, từ ngữ đời thường, rõ ràng, đọc một lần hiểu ngay.
   - **Mục tiêu trực diện:** Vào thẳng vấn đề ngay từ đoạn mở đầu, đưa thông tin có ích thật (con số, mốc thời gian, ưu nhược điểm, ví dụ thực tế áp dụng được cho đám cưới tại Việt Nam).
   - **Tuyệt đối tránh văn mẫu AI:** Không mở bài bằng *"Trong thời đại công nghệ số..."*, *"Trong kỷ nguyên 4.0..."*, *"Không thể phủ nhận rằng..."*, *"Hãy cùng chúng tôi khám phá..."*; không dùng sáo ngữ *"chìa khóa vàng"*, *"nâng tầm trải nghiệm"*, *"bức tranh hoàn mỹ"*, *"từng khoảnh khắc đáng nhớ"*, *"tối ưu hóa toàn diện"*, *"đậm chất nghệ thuật"*.
   - Không viết lan man, không lặp ý giữa các mục để kéo dài số chữ. Nếu một mục đã đủ ý trong 2 đoạn ngắn + 3 gạch đầu dòng, dừng ngay tại đó.
2. **Việt hóa ngữ cảnh thực tế**:
   - Chuyển hóa tự nhiên sang bối cảnh cưới ở Việt Nam (gửi thiệp qua Zalo, cân bằng giữa thiệp giấy gửi họ hàng lớn tuổi và thiệp online cho bạn bè/đồng nghiệp, quản lý số lượng khách đặt bàn tiệc, ghi chú món ăn kiêng, tiền mừng VietQR khi phù hợp).
   - Không giữ góc nhìn "chúng tôi là Paperless Post" hay nhắc "Paperless Post Coins / Flyer" như thể là sản phẩm của Mây Hẹn. Nếu bài là bài hướng dẫn (ví dụ cách tạo thiệp online, cách dùng RSVP), viết theo góc nhìn hướng dẫn chung hữu ích cho cặp đôi Việt và giới thiệu nhẹ nhàng tính năng tương ứng trên Mây Hẹn. Nếu bài là danh sách tham khảo nước ngoài (ví dụ top trang web thiết kế thiệp), giữ trung thực thông tin từng thương hiệu nước ngoài để khớp đúng ảnh minh họa, đồng thời thêm lưu ý thực tế cho cặp đôi tại Việt Nam.
3. **Chuẩn HTML sạch & Ảnh giữ nguyên đường dẫn**:
   - Chỉ dùng thẻ ngữ nghĩa sạch: `<h2>`, `<h3>`, `<p>`, `<ul>`, `<ol>`, `<li>`, `<blockquote>`, `<table>`, `<thead>`, `<tbody>`, `<tr>`, `<th>`, `<td>`, `<figure>`, `<img>`, `<figcaption>`, `<a>`, `<strong>`, `<em>`.
   - **Không đặt `<h1>`** trong nội dung `content` (Blade đã render `<h1>` từ `title`).
   - Không nhảy cóc heading (`<h2>` → `<h3>`), không dùng `<b>`/`<i>` lồng bên trong `<h2>`, không chèn inline `style="..."` hay class rác.
   - Giữ nguyên đường dẫn file ảnh gốc (`src="imgs/extra/..."` — giữ nguyên tên file tiếng Anh).
   - Mỗi ảnh nằm gọn trong **1 thẻ `<figure>` duy nhất** (không lồng `<figure>` trong `<figure>`):
     ```html
     <figure>
       <img src="imgs/extra/ten-file-goc.png" alt="Mô tả tiếng Việt ngắn gọn đúng hình ảnh" title="Tiêu đề tiếng Việt ngắn gọn" loading="lazy" decoding="async">
       <figcaption>Chú thích tiếng Việt đơn giản, dễ hiểu</figcaption>
     </figure>
     ```
   - `alt`, `title`, `<figcaption>` phải viết bằng **Tiếng Việt sạch**, mô tả đúng hình ảnh thực tế, không dính chữ tiếng Anh rác hay lỗi ký tự `â€™`, không nhồi nhét từ khóa.

### Bước 5 — Chèn Internal Link Chính Xác 100% (Không Link Lỗi)
- Mỗi bài chèn **3–6 internal link** thật sự liên quan đến đoạn văn đó, dùng đường dẫn tương đối bắt đầu bằng `/` (ví dụ `<a href="/mau-thiep">kho mẫu thiệp cưới online</a>`).
- **Quy tắc chống link lỗi tuyệt đối:** Chỉ được dùng các URL nằm trong **Mục 2 — Bảng URL Đã Xác Minh** bên dưới hoặc `/cam-nang-cuoi/{vi_slug}` của các bài đã có `status === "done"` trong `progress.json` (và 3 bài gốc đang có trong `BlogSeeder`).
- Không tự bịa URL, không chèn link về `paperlesspost.com`, không chèn link trang cần đăng nhập sâu như `/tai-khoan/...` (trừ khi nói rõ về trang hướng dẫn `/docs/...`), không lặp cùng 1 URL quá 2 lần trong một bài.
- Anchor text tự nhiên theo mạch câu (không dùng "xem tại đây", "bấm vào đây").

### Bước 6 — Xuất File, Cập Nhật Tiến Độ & Chạy Kiểm Định Tự Động
1. Lưu bài hoàn chỉnh dạng JSON UTF-8 vào:
   `output_mayhen/articles/{stt_3_chu_so}-{vi_slug}.json` (ví dụ `001-trang-web-lam-thiep-cuoi-online-dep.json`).
   Cấu trúc JSON chuẩn:
   ```json
   {
     "article_number": 1,
     "excel_row": 14,
     "source_id": "646",
     "source_slug": "best-wedding-invitation-websites",
     "source_title": "...",
     "source_url": "https://www.paperlesspost.com/blog/...",
     "category_slug": "y-tuong-phong-cach-thiep",
     "title": "...",
     "slug": "...",
     "primary_keyword": "...",
     "secondary_keywords": ["...", "..."],
     "summary": "...",
     "meta_title": "...",
     "meta_description": "...",
     "meta_keywords": "...",
     "tags": ["...", "..."],
     "author_name": "Ban Biên Tập Mây Hẹn",
     "reading_time_minutes": 6,
     "cover_image_path": "imgs/main/...",
     "cover_image_alt": "...",
     "content_images": ["imgs/extra/..."],
     "internal_links": ["/mau-thiep", "/bang-gia"],
     "content_html": "<h2>...</h2><p>...</p>",
     "completed_at": "2026-11-22T11:05:00+07:00"
   }
   ```
2. Lưu kèm bản xem trước HTML tại `output_mayhen/html/{stt_3_chu_so}-{vi_slug}.html` để người dùng mở trình duyệt đọc thử trực quan (ảnh load trực tiếp từ `../../imgs/...`).
3. Chạy lệnh kiểm định QA, tự động cập nhật `progress.json` từ `articles/*.json` và đồng bộ Excel tổng hợp:
   ```powershell
   php "D:\Project\PHP\Cào dữ liệu cho Mây Hẹn\paperlesspost.com\output_mayhen\tools\sync_excel.php"
   ```
   *(Script `sync_excel.php` tự khớp theo `excel_row` để đánh dấu `status: "done"` cùng `vi_title`, `vi_slug`, `category_slug`, `primary_keyword`, `output_file` trong `progress.json` — không cần sửa tay file `progress.json`.)*
   **Tiêu chí hoàn tất Bước 6 (Bắt buộc PASS 100%):**
   - `sync_excel.php` trả exit code `0` với **0 lỗi QA** (0 mojibake, 0 ảnh thiếu trên đĩa, 0 ảnh quảng cáo `blog-categoryad`, 0 thẻ `<figure>` lồng nhau, 0 `<h1>` trong content, 0 link lỗi ngoài whitelist, `meta_title <= 60` ký tự không lặp tên thương hiệu, `meta_description` trong khoảng `120–160` ký tự).
   - File `output_mayhen/mayhen_blog_rewritten.xlsx` được cập nhật thành công.

---

## 2. BẢNG URL NỘI BỘ ĐÃ XÁC MINH TRÊN MAYHEN.VN (WHITELIST)

> Chỉ sử dụng đúng các đường dẫn dưới đây (và `/cam-nang-cuoi/{vi_slug}` của các bài đã `done` trong `progress.json`).

### A. Trang chính & Trang dịch vụ công khai
- `/` — Trang chủ Mây Hẹn
- `/mau-thiep` — Kho mẫu thiệp cưới online
- `/tao-thiep` — Studio tự tạo và chỉnh sửa thiệp cưới online
- `/dich-vu` — Dịch vụ và tính năng thiệp cưới Mây Hẹn
- `/bang-gia` — Bảng giá gói thiệp cưới online
- `/tu-van-goi-cuoi` — Công cụ tư vấn chọn gói thiệp cưới
- `/cam-nang-cuoi` — Cẩm nang cưới Mây Hẹn
- `/cam-nang-cuoi?category=y-tuong-phong-cach-thiep` — Chuyên mục Ý Tưởng & Phong Cách Thiệp
- `/cam-nang-cuoi?category=ke-hoach-le-nghi-ngay-cuoi` — Chuyên mục Kế Hoạch & Lễ Nghi Ngày Cưới
- `/cam-nang-cuoi?category=kinh-nghiem-khach-moi-rsvp` — Chuyên mục Kinh Nghiệm Khách Mời & RSVP
- `/cam-nang-cuoi?category=xu-huong-cuoi-toi-gian` — Chuyên mục Xu Hướng Cưới Tối Giản Hiện Đại
- `/cau-hoi-thuong-gap` — Câu hỏi thường gặp (FAQ)
- `/ve-may-hen` — Giới thiệu về Mây Hẹn
- `/lien-he` — Liên hệ tư vấn Mây Hẹn
- `/docs` — Trung tâm hướng dẫn sử dụng Mây Hẹn
- `/doi-tac/dang-ky` — Đăng ký đối tác Studio & Wedding Planner

### B. Mẫu thiệp cưới đang hoạt động (`/mau-thiep/{slug}`)
- **Sang trọng (`luxury`):** `/mau-thiep/giua-muon-ngan-vi-sao`, `/mau-thiep/hac-bach-trao-duyen`, `/mau-thiep/ngay-tinh-yeu-dom-hoa`, `/mau-thiep/tron-kiep-nay-ben-nhau`, `/mau-thiep/vom-troi-may-ao-cuoi`, `/mau-thiep/tiec-hong-mung-duyen-moi`, `/mau-thiep/tinh-yeu-qua-tung-dong-thu`, `/mau-thiep/ban-nhac-khong-doan-ket`, `/mau-thiep/tieng-hat-trong-le-duong`, `/mau-thiep/hoa-nhip-doi-trai-tim`
- **Lãng mạn (`romantic`):** `/mau-thiep/net-dep-cua-dinh-uoc`, `/mau-thiep/mai-lan-no-ben-them`, `/mau-thiep/ban-sonata-mau-hanh-phuc`, `/mau-thiep/xuan-sang-tinh-vua-chom`, `/mau-thiep/loi-hua-bang-nu-cuoi`
- **Truyền thống (`traditional`):** `/mau-thiep/mam-trau-do-chu-duyen`, `/mau-thiep/gui-em-mot-nhanh-xuan`, `/mau-thiep/mua-moi-co-chung-ta`, `/mau-thiep/mot-mua-he-ben-nhau`
- **Hiện đại (`modern`):** `/mau-thiep/hanh-phuc-mang-ten-chung-minh`, `/mau-thiep/hom-nay-minh-hua-nhau`, `/mau-thiep/hen-cuoi-vao-mua-xuan`

### C. Bài hướng dẫn sử dụng công khai (`/docs/{category}/{slug}`)
- **Bắt đầu & Bảng giá:**
  - `/docs/getting-started/CLI-START-001` — Hành trình tạo thiệp đến ngày cưới
  - `/docs/getting-started/CLI-START-003` — Xem thử mẫu thiệp và trải nghiệm tính năng miễn phí
  - `/docs/getting-started/CLI-START-005` — Bảng giá gói dịch vụ và thanh toán VietQR
- **Studio Tạo Thiệp:**
  - `/docs/studio-editor/CLI-EDIT-001` — Làm quen Studio tạo thiệp
  - `/docs/studio-editor/CLI-EDIT-003` — Chỉnh sửa tên cô dâu chú rể, phụ mẫu, ngày Âm lịch và địa điểm
  - `/docs/studio-editor/CLI-EDIT-005` — Tùy chỉnh 10 phần trên thiệp, lịch trình, dresscode, VietQR và RSVP
  - `/docs/studio-editor/CLI-EDIT-006` — Chọn nhạc nền đám cưới
  - `/docs/studio-editor/CLI-EDIT-007` — Trợ lý biên tập lời mời, lời ngỏ và dịch đa ngôn ngữ
- **Chia sẻ thiệp & Tiện ích khách mời:**
  - `/docs/public-invitation/CLI-INVT-001` — Đặt đường dẫn thiệp đẹp và chia sẻ Zalo/Facebook
  - `/docs/public-invitation/CLI-INVT-002` — Tạo mã QR thiệp cưới in kèm thiệp giấy
  - `/docs/public-invitation/CLI-INVT-004` — Lưu lịch ngày cưới vào điện thoại (.ICS / Google Calendar)
  - `/docs/public-invitation/CLI-INVT-005` — Gắn bản đồ Google Maps chỉ đường đến sảnh tiệc
  - `/docs/public-invitation/CLI-INVT-006` — Tạo thiệp cưới song ngữ / đa ngôn ngữ
- **Khách mời & RSVP:**
  - `/docs/guest-rsvp/CLI-GUEST-001` — Quản lý danh sách khách mời theo nhóm
  - `/docs/guest-rsvp/CLI-GUEST-002` — Nhập danh sách khách từ file Excel/CSV
  - `/docs/guest-rsvp/CLI-GUEST-003` — Tạo link thiệp cá nhân hóa theo đúng tên và xưng hô khách mời
  - `/docs/guest-rsvp/CLI-GUEST-004` — Xác nhận tham dự (RSVP) trên thiệp cưới
  - `/docs/guest-rsvp/CLI-GUEST-005` — RSVP theo hộ gia đình, số người đi kèm và món ăn kiêng
  - `/docs/guest-rsvp/CLI-GUEST-007` — Gửi tin nhắn nhắc hẹn lịch sự trước ngày cưới
- **Bàn tiệc, Tiếp tân, Lưu bút & Ngân sách:**
  - `/docs/seating-planner/CLI-SEAT-001` — Xếp sơ đồ bàn tiệc
  - `/docs/seating-planner/CLI-SEAT-005` — Khách tự tra cứu số bàn tiệc tại sảnh
  - `/docs/checkin-livewall/CLI-CKIN-001` — Điểm danh khách bằng mã QR tại sảnh cưới
  - `/docs/guestbook-photos/CLI-WISH-001` — Sổ ký tên điện tử và lời chúc cưới
  - `/docs/guestbook-photos/CLI-WISH-003` — Album ảnh khách chụp trong đám cưới (Photo Drop)
  - `/docs/gift-budget/CLI-GIFT-001` — Cài đặt mã VietQR mừng cưới
  - `/docs/gift-budget/CLI-GIFT-004` — Lập bảng dự toán ngân sách cưới
  - `/docs/partner-privacy/CLI-PART-004` — Gửi thiệp cho hai bên gia đình xem duyệt trước

### D. Bài viết Cẩm Nang Cưới gốc đang có sẵn trên hệ thống
- `/cam-nang-cuoi/nghe-thuat-thiep-cuoi-quiet-luxury-khi-su-tinh-te-len-ngoi` — Nghệ Thuật Thiệp Cưới Quiet Luxury
- `/cam-nang-cuoi/thoi-diem-vang-de-gui-thiep-cuoi-online` — Thời Điểm Vàng Để Gửi Thiệp Cưới Online
- `/cam-nang-cuoi/bi-quyet-quan-ly-ban-tiec-bang-rsvp-thong-minh` — Bí Quyết Quản Lý Bàn Tiệc Bằng RSVP Thông Minh

---

## 3. LỆNH GỌI NHANH CHO CÁC PHIÊN LÀM VIỆC TIẾP THEO

Khi người dùng nhắn *"Viết tiếp bài blog tiếp theo theo prompt"* hoặc *"Tiếp tục viết bài blog"*, Agent tự động:
1. Đọc file prompt này + đọc `output_mayhen/progress.json`.
2. Lấy đúng bài `pending` kế tiếp theo thứ tự ưu tiên.
3. Thực hiện đầy đủ Bước 1 → Bước 6, chạy `sync_excel.php` xác nhận **0 lỗi QA**.
4. Báo cáo ngắn gọn cho người dùng: STT bài, tiêu đề, slug mới, từ khóa chính, danh sách internal link đã gắn, số ảnh đã kiểm chứng và tiến độ tổng (ví dụ `Đã xong 2/377 bài cần viết, 21 bài skipped`).
