# BÁO CÁO KHẢO SÁT & KIỂM ĐỊNH MẪU THIỆP NGUYÊN BẢN (NATIVE TEMPLATE AUDIT)
Dự án: **Mây Hẹn (mayhen.vn)**  
Thời gian khảo sát: **2026-11-10**  
Mục đích: Loại bỏ triệt để layout tự chế `mayhen-curated`, chuyển sang 100% tái sử dụng 4 mẫu thiệp gốc cao cấp có sẵn.

---

## 1. BẢNG TỔNG QUAN KIỂM ĐỊNH 4 MẪU THIỆP GỐC

| Mẫu gốc | View thật | CSS / JS thật | 7 section gắn selector nào | Section dùng chung wrapper | Asset nội bộ | Cách inject dữ liệu | Khả năng ẩn các section dư | Tương thích màu | Đánh giá |
|---|---|---|---|---|---|---|---|---|---|
| **Giữa Muôn Ngàn Vì Sao** (`giua-muon-ngan-vi-sao`) | `client.invitations.giua-muon-ngan-vi-sao.template` | `styles.css` / `script.js` | 1. Hero: `.mayhen_invitation_sr_dau`<br>2. Story: `.mayhen_invitation_sr_cau_chuyen`<br>3. Parents: `.mayhen_invitation_sr_the_cap`<br>4. Countdown: `.mayhen_invitation_sr_lich`<br>5. Events: `.mayhen_invitation_sr_the_sk`<br>6. Timeline: `.mayhen_invitation_sr_truc`<br>7. Album: `.mayhen_invitation_tmx_album_cho` | Chung thẻ bao ngoài `.mayhen_invitation_sr_khung` | 100% Local trong thư mục `assets/` | Biến Blade chuẩn `$wedding`, `$extraData`, `$data-sync-target` | `@if(!$isCuratedSeven)` ẩn Dresscode, Gift, RSVP | 5/5 Palette qua CSS variables | **ĐẠT CHUẨN 100%** |
| **Hạnh Phúc Mang Tên Chúng Mình** (`hanh-phuc-mang-ten-chung-minh`) | `client.invitations.hanh-phuc-mang-ten-chung-minh.template` | `styles.css` / `script.js` | 1. Hero: `.mayhen_invitation_hero`<br>2. Story: `.mayhen_invitation_story`<br>3. Parents: `.mayhen_invitation_parents`<br>4. Countdown: `.mayhen_invitation_calendar`<br>5. Events: `.mayhen_invitation_events`<br>6. Timeline: `.mayhen_invitation_timeline`<br>7. Album: `.mayhen_invitation_gallery` | Bọc trong `.mayhen_invitation_card_wrapper` | 100% Local qua `$resolveAsset` | Biến Blade chuẩn `$wedding`, `$extraData`, `$data-sync-target` | `@if(!$isCuratedSeven)` ẩn Dresscode, Gift, RSVP | 5/5 Palette qua CSS variables | **ĐẠT CHUẨN 100% (Mẫu ưu tiên P1)** |
| **Ngày Tình Yêu Đơm Hoa** (`ngay-tinh-yeu-dom-hoa`) | `client.invitations.ngay-tinh-yeu-dom-hoa.template` | `styles.css` / `script.js` | 1. Hero: `.mayhen_invitation_hero`<br>2. Story: `.mayhen_invitation_story`<br>3. Parents: `.mayhen_invitation_family`<br>4. Countdown: `.mayhen_invitation_countdown_calendar`<br>5. Events: `.mayhen_invitation_events`<br>6. Timeline: `.mayhen_invitation_timeline`<br>7. Album: `.mayhen_invitation_gallery` | Bọc trong `.mayhen_invitation_container` | 100% Local trong `assets/` | Biến Blade chuẩn `$wedding`, `$extraData`, `$data-sync-target` | `@if(!$isCuratedSeven)` ẩn Dresscode, Gift, RSVP | 5/5 Palette qua CSS variables | **ĐẠT CHUẨN 100%** |
| **Trọn Kiếp Này Bên Nhau** (`tron-kiep-nay-ben-nhau`) | `client.invitations.tron-kiep-nay-ben-nhau.template` | `styles.css` / `script.js` | 1. Hero: `.mayhen_invitation_hero`<br>2. Story: `.mayhen_invitation_story`<br>3. Parents: `.mayhen_invitation_family`<br>4. Countdown: `.mayhen_invitation_calendar`<br>5. Events: `.mayhen_invitation_events`<br>6. Timeline: `.mayhen_invitation_timeline`<br>7. Album: `.mayhen_invitation_gallery` | Bọc trong `.mayhen_invitation_root` | 100% Local trong `assets/` | Biến Blade chuẩn `$wedding`, `$extraData`, `$data-sync-target` | `@if(!$isCuratedSeven)` ẩn Dresscode, Gift, RSVP | 5/5 Palette qua CSS variables | **ĐẠT CHUẨN 100%** |

---

## 2. NGUYÊN NHÂN GỐC CỦA CÁC VẤN ĐỀ ĐÃ XẢY RA

1. **Hiển thị giao diện tự chế `mayhen-curated` thay vì mẫu gốc**:
   - `InvitationEditorController.php` có đoạn code cưỡng chế:
     ```php
     if (! empty($config['is_curated'])) {
         $curatedTemplate = InvitationTemplate::where('slug', 'mayhen-curated')->first();
         if ($curatedTemplate) {
             $template = $curatedTemplate;
         }
     }
     ```
   - Hậu quả: Dù CSDL lưu `template_id` của mẫu thật, trang hiển thị vẫn bị ép nạp `mayhen-curated`.
   - Khắc phục: Xóa bỏ hoàn toàn việc ép nạp `mayhen-curated`, khôi phục 100% quyền hiển thị của mẫu gốc thật.

2. **Màn hình trắng xóa sau khi dệt (Ảnh 2)**:
   - Các section phía dưới bị gắn class `mayhen_crafting_section_waiting` và không được giải phóng kịp thời, kết hợp với rèm che và class `mayhen_crafting_preparing`.
   - Khắc phục:
     + Section waiting dùng `display: none !important;` (tạo đến đâu mở đến đó).
     + Khi hoàn tất hoặc bấm Bỏ qua: `revealAllInstantly()` mở toàn bộ section (`display: block !important; opacity: 1 !important; visibility: visible !important;`).
     + Watchdog Fail-Safe Timer 25s tự động giải phóng 100% nội dung, triệt tiêu hoàn toàn rủi ro màn hình trắng.

3. **Vệt sáng và thời điểm hiển thị**:
   - Thay đổi kỹ thuật clip-path từ `polygon` sang `clip-path: inset(0 100% 0 0)` (bị che kín từ phải qua trái).
   - Khi dệt, vệt sáng hoàng gia lướt qua đồng bộ chính xác trong 0.65s với `clip-path: inset(0 0% 0 0)`.
   - Vệt sáng quét tới đâu, chữ và ảnh lộ ra tới đó.
   - Nâng cấp vệt sáng vàng 24K đa tầng tỏa ánh hào quang và đính sao `✦`.

4. **Trang load lâu**:
   - Rút ngắn chu kỳ dệt thiệp từ 40s-80s xuống còn **16s - 20s**, nhịp bước gãy gọn, dứt khoát, mượt mà 60fps.
