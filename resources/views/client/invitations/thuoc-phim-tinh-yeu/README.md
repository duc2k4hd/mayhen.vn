# MAYHEN — Thiệp cưới Duyên dáng 04 (Graceful 04)

Bản dựng độc lập **HTML + CSS + JavaScript thuần**, dựa trên cấu trúc HTML trang mẫu được cung cấp. Không sử dụng React/Next.js, jQuery, Bootstrap hoặc framework JavaScript/CSS.

## Khởi chạy

- Giải nén ZIP và mở `index.html` bằng Chrome, Edge, Safari hoặc Firefox.
- Có thể đưa nguyên thư mục lên hosting tĩnh / `public/` của Laravel và truy cập `index.html`.
- Ảnh và font hiện được tham chiếu từ máy chủ bên ngoài. Cần kết nối Internet để hiển thị đủ tài nguyên. Nếu máy chủ ảnh gốc thay đổi hoặc hạn chế hotlink, một số ảnh có thể không tải được.

## Các thành phần

1. Màn hình mở đầu (hero ảnh toàn màn hình, chữ bay vào).
2. Monogram + đếm ngược theo ngày cưới **07/03/2027 07:00 GMT+7** + phong thư/ảnh chồng lớp.
3. Khung ảnh **Save the Date**.
4. Thông tin hai bên gia đình, nút **Nhà gái / Nhà trai**, lời mời, thời gian và địa điểm, điều hướng Google Maps, dress code.
5. The Story of Love: ảnh cô dâu, chú rể và typography.
6. Film strip chạy vòng, album 10 ảnh và lightbox (điều hướng bằng mũi tên, vuốt, ESC).
7. Lịch tháng 3/2027, làm nổi bật ngày cưới.
8. Timeline 6 thời điểm, biểu tượng tương ứng.
9. Sổ lưu bút, canvas ký tay/chạm, lời chúc và các phản hồi mẫu.
10. RSVP, chọn đi hoặc không tham dự, điều chỉnh số người.
11. Hộp mừng cưới, hộp thoại cô dâu/chú rể.
12. Lời cảm ơn và ảnh cuối.

## Nơi tùy biến

- `index.html`: tên, câu chữ, ảnh, lịch, địa điểm, màu dress code, đường dẫn Google Maps.
- `styles.css`: màu sắc, kiểu chữ, bố cục, breakpoint, các hiệu ứng và kích thước.
- `script.js`: `WEDDING_DATE`, các hành vi của trang. Để hiển thị số tài khoản thật cho hộp mừng cưới, cập nhật `accounts.bride` và `accounts.groom` trong đầu file `script.js`.
- Mở link `index.html?to=Nguy%E1%BB%85n%20V%C4%83n%20A` để cá nhân hóa tên khách mời.

**Mọi CSS class đều bắt đầu bằng** `mayhen_invitation_djcvhwiuo9w9_` (kể cả class dùng cho JavaScript và modal).

## Giới hạn quan trọng

- Đây là bản tái tạo độc lập theo **HTML do người dùng cung cấp** và nội dung trang mẫu, không phải bản sao mã nguồn CSS/JS private hoặc xuất trực tiếp từ Next.js. Không có cơ sở xác nhận độ khớp pixel-perfect 100% nếu chưa so sánh đầy đủ từng trạng thái trên trình duyệt đang có quyền truy cập tài nguyên gốc.
- **Sổ lưu bút và RSVP là demo tĩnh:** dữ liệu chỉ lưu `localStorage` tại **trình duyệt/thiết bị hiện tại**, không tự gửi đến cô dâu/chú rể và không đồng bộ nhiều thiết bị. Cần có backend/API để ghi dữ liệu thực và quản lý khách mời.
- **Hộp mừng cưới:** không có thông tin ngân hàng/QR thực trong HTML nguồn, vì vậy bản demo để trống, tuyệt đối không tạo số tài khoản giả.
- Ngày **Tiệc Cưới 06.03.2026** xuất hiện trong HTML gốc, khác năm với ngày cưới **07.03.2027**; mẫu dựng giữ nguyên dữ liệu nguồn, bạn nên xác nhận lại trước khi đưa vào sử dụng.
- Các ảnh đang sử dụng URL từ máy chủ mẫu. Khi đưa vào website thương mại, cần thay bằng ảnh và tài nguyên mà bạn có quyền sử dụng.

## Dùng ảnh cục bộ (tùy chọn)

File `assets/manifest.json` liệt kê các URL hình nền và ảnh trong thiệp. Nếu có quyền sử dụng và muốn lưu trữ ảnh trên máy chủ riêng, chạy:

```bash
python download_assets.py
```

Lệnh này thử tải các ảnh về `assets/downloaded/` và tạo `index-offline.html` / `styles-offline.css`. Với ảnh nào tải thất bại, file offline vẫn sử dụng URL từ trang nguồn. Script Python chỉ phục vụ tải ảnh một lần, **website khi chạy không cần Python**.

Font chữ dùng Google Fonts (Cormorant Garamond, Great Vibes, Manrope) có fallback font hệ thống. Nếu muốn offline hoàn toàn, bạn cũng cần cung cấp font có giấy phép phù hợp.

## Kiểm thử

Đã kiểm tra cấu trúc và tương tác trong Chromium ở viewport 390×844, 768×1024, 1440×900: 11 section, không tràn ngang, bộ đếm, album, đóng/mở modal, RSVP. Do môi trường kiểm thử không truy cập được ảnh/font máy chủ gốc, kiểm tra hình ảnh ở trình duyệt có Internet vẫn cần thực hiện trước khi triển khai sản phẩm.
