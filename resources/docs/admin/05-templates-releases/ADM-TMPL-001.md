---
id: ADM-TMPL-001
title: "Thêm mẫu thiệp mới vào hệ thống: Cấu hình schema.json, ảnh đại diện và 10 section"
category: "Quản lý Mẫu thiệp & Release Control"
summary: "Hướng dẫn toàn diện quy trình đóng gói mẫu thiệp mới, cấu hình tệp schema.json, ảnh thumbnail WebP, đáp ứng chuẩn khung 10 section bất biến và chạy kiểm định Schema Validator Inspector."
reader: "PER-ADM-03 (Quản trị viên Hệ thống)"
order: 1
updated_at: "2026-11-15"
tags: [templates, schema-json, 10-sections, bem-class, live-sync, studio-editor]
mapped_page: "ADM-TMPL-PAGE"
mapped_route: "admin.templates.index"
risk_level: "HIGH"
required_role: "designer"
---

# Thêm mẫu thiệp mới vào hệ thống: Cấu hình schema.json, ảnh đại diện và 10 section

> [!WARNING]
> **Phân quyền thực hiện:** Chuyên viên Mẫu thiệp (`designer`), Quản lý (`manager`) hoặc Super Admin (`admin.super`).  
> **Route quản lý:** `GET /admin/templates` (`admin.templates.index`) và `/admin/templates/{id}/edit`.  
> **Bộ quy chuẩn bắt buộc:** Tuân thủ 100% tài liệu `.agents/skills/mayhen-invitation-standards/SKILL.md` và `.agents/skills/mayhen-invitation-templates/SKILL.md`.


![Giao diện quản lý Thêm mẫu thiệp mới vào hệ thống: Cấu hình schema.json, ảnh đại diện và 10 section](/assets/client/docs/images/admin/05-templates-releases/adm-tmpl-001-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Kiến Trúc Thư Mục Của Một Mẫu Thiệp Chuẩn

Mỗi mẫu thiệp cưới trong hệ thống Mây Hẹn là một phân hệ độc lập, tự chứa (Self-contained) nằm trong thư mục `resources/views/invitation-templates/{slug}/`:

```
resources/views/invitation-templates/celestial-bloom/
├── template.blade.php       # Giao diện Blade chính chứa đủ 10 Section tiêu chuẩn
├── schema.json              # Định nghĩa schema cấu hình cho Studio Editor
├── thumbnail.webp           # Ảnh đại diện mẫu hiển thị trên Kho mẫu thiệp (Store)
└── assets/                  # 100% tài nguyên cục bộ của mẫu (CẤM link CDN ngoài)
    ├── css/style.css        # CSS thuần có prefix mayhen_invitation_*
    ├── js/script.js         # JavaScript thuần ES Modules
    ├── images/              # Ảnh minh họa mẫu (fallback ảnh cô dâu chú rể)
    └── audio/theme.mp3      # Nhạc nền mặc định chất lượng cao
```

---

## 2. Quy Tắc Bắt Buộc: Khung 10 Section Tiêu Chuẩn

Mọi mẫu thiệp cưới khi tải lên hệ thống bắt buộc phải có đủ **10 Section theo đúng thứ tự logic** và sử dụng hệ thống class BEM chuẩn toàn cục `mayhen_invitation_*`:

1. **Section 1: Màn Mở Đầu (Opening Envelope):** Cánh thiệp mở, con dấu sáp (Wax Seal) hoặc hoa văn mở màn. *Phòng vệ nhúng Studio: Khi `$isEmbed = true` tự động mở sẵn và ẩn màn mở đầu để không làm trắng màn hình.*
2. **Section 2: Bìa Chính (Hero Cover):** Tên cô dâu chú rể, ngày cưới, ảnh bìa chính và câu định danh tiệc cưới.
3. **Section 3: Chuyện Tình & Câu Trích Dẫn (Love Story & Quote):** Dòng thời gian tình yêu, trích dẫn văn học lãng mạn.
4. **Section 4: Gia Đình & Cặp Đôi (Family & Couple):** Thông tin Thân phụ, Thân mẫu hai bên và chân dung cặp đôi.
5. **Section 5: Đếm Ngược & Lịch Cưới (Countdown & Calendar):** Đồng hồ đếm ngược từng giây và lịch tháng phong cách Editorial.
6. **Section 6: Sự Kiện & Chỉ Đường (Events & Maps):** Giờ lễ gia tiên, giờ tiệc cưới, bản đồ Google Maps và nút dẫn đường.
7. **Section 7: Lịch Trình Tiệc (Timeline):** Các mốc đón khách, khai tiệc, cắt bánh, nâng ly và khiêu vũ.
8. **Section 8: Quy Định Trang Phục (Dresscode):** Bảng màu trang phục gợi ý (Color Swatches) và lưu ý tiệc cưới.
9. **Section 9: Album 10 Ảnh Cưới (Album 10 Photos):** Bố cục lưới Flex/Grid hiển thị tối thiểu 10 vị trí ảnh kỷ niệm.
10. **Section 10: Quà Mừng, Tương Tác & Lời Kết (Gift, Interactive & Ending):** Mã VietQR mừng cưới, Sổ ký tên (Signature), Xác nhận tham dự (RSVP) và lời cảm ơn.

---

## 3. Cấu Trúc Tệp `schema.json` Chuẩn Hóa

Tệp `schema.json` đóng vai trò là "hợp đồng giao tiếp" giữa mẫu thiệp và Studio Editor. Nếu không có tệp này, cặp đôi sẽ không thể tùy biến chữ, đổi màu hay đổi ảnh:

```json
{
  "name": "Celestial Bloom",
  "version": "1.0.0",
  "author": "Mây Hẹn Creative Studio",
  "description": "Mẫu thiệp cưới phong cách hoàng gia cổ điển với tông màu Dusty Rose và Muted Gold.",
  "category": "classic",
  "theme_color": "#8B4D53",
  "font_family": "Playfair Display, serif",
  "sections": [
    {
      "key": "hero",
      "title": "Bìa Thiệp Chính",
      "fields": [
        { "name": "groom_name", "label": "Tên Chú Rể", "type": "text", "default": "Quốc Tuấn" },
        { "name": "bride_name", "label": "Tên Cô Dâu", "type": "text", "default": "Lan Anh" },
        { "name": "cover_photo", "label": "Ảnh Bìa Chính", "type": "image", "default": "cover.webp" }
      ]
    },
    {
      "key": "events",
      "title": "Thời Gian & Địa Điểm",
      "fields": [
        { "name": "wedding_date", "label": "Ngày Cưới", "type": "date", "default": "2026-12-25" },
        { "name": "venue_name", "label": "Tên Trung Tâm Tiệc Cưới", "type": "text", "default": "Trống Đồng Palace" },
        { "name": "venue_address", "label": "Địa Chỉ Tiệc Cưới", "type": "text", "default": "72 Quán Sứ, Hoàn Kiếm, Hà Nội" }
      ]
    }
  ]
}
```

---

## 4. Hợp Đồng Live Sync (`data-sync-target`)

Trong `template.blade.php`, mọi thẻ hiển thị nội dung động bắt buộc phải gắn thuộc tính `data-sync-target`:
- `<h1 class="mayhen_invitation_groom" data-sync-target="groom_name">{{ $groomName }}</h1>`
- `<p class="mayhen_invitation_venue" data-sync-target="venue_name">{{ $venueName }}</p>`
- `<img class="mayhen_invitation_cover_img" data-sync-target="cover_photo" src="{{ $resolveAsset('cover_photo') }}"/>`

Khi người dùng gõ phím trong Studio Editor, giao diện thiệp sẽ cập nhật tức thời theo thời gian thực (Real-time Mirroring) mà không bị giật trang.

---

## 5. Quy Trình Quét Mẫu & Kiểm Tra Toàn Vẹn (Schema Inspector)

1. **Bước 1: Quét nhận diện mẫu mới**
   - Đặt thư mục mẫu vào `resources/views/invitation-templates/{slug}`.
   - Truy cập `/admin/templates`, nhấp nút **[Quét mẫu mới từ thư mục]** (`POST /admin/templates/scan`).
   - Hệ thống tự động tạo bản ghi trong bảng `invitation_templates`.
2. **Bước 2: Kiểm tra toàn vẹn bằng Schema Validator Inspector**
   - Nhấp vào mẫu vừa quét để mở màn hình chỉnh sửa `/admin/templates/{id}/edit`.
   - Cột bên phải hiển thị công cụ **Schema & Code Integrity Inspector** do `InvitationTemplateService::inspectTemplateIntegrity` cung cấp:
     - Trạng thái tệp `schema.json`: Đã tồn tại & Hợp lệ.
     - Trạng thái `template.blade.php`: Đạt chuẩn 10 Section.
     - Báo cáo ảnh Album: Đủ 10 vị trí ảnh.
     - Báo cáo Visual DNA: Phân tích bảng màu và typography.
3. **Bước 3: Xem thử nghiệm thực tế (Live Preview)**
   - Nhấp **[Xem bản xem trước]** để kiểm tra chuyển động mở thiệp, nhạc nền và độ tương thích mượt mà trên khung hình điện thoại.

---

## 6. Những điều Tuyệt đối Bị cấm

1. **CẤM TUYỆT ĐỐI** sử dụng đường dẫn ảnh từ máy chủ bên ngoài (như `ladicdn.com`, `builder.27biggroup.com`, `unsplash.com`, `iili.io`). Toàn bộ tài nguyên phải nằm cục bộ trong thư mục `assets/` của mẫu thiệp.
2. **CẤM** bố cục layout dạng kéo-thả tuyệt đối (`position: absolute` kiểu LadiPage). 100% mẫu thiệp bắt buộc dùng Flow Layout tự co giãn (Flexbox & CSS Grid).
3. **CẤM** sử dụng các thư viện JavaScript bên ngoài nặng nề (Bootstrap, Tailwind, Swiper, jQuery, AOS). 100% JavaScript phải là Native ES Modules.

---

## 7. Bài viết Liên quan

- [ADM-TMPL-002: Sử dụng công cụ Chẩn đoán Asset quét ảnh và tài nguyên ngoài](/admin/docs/templates-releases/ADM-TMPL-002)
- [ADM-TMPL-003: Thao tác Localize & Batch-repair đưa 100% tài nguyên về cục bộ](/admin/docs/templates-releases/ADM-TMPL-003)
- [ADM-TMPL-004: Kích hoạt, gắn nhãn Nổi bật và ẩn mẫu thiệp bảo trì](/admin/docs/templates-releases/ADM-TMPL-004)
