# MÂY HẸN (MAYHEN.VN) — BÁO CÁO KIỂM TRA GIAO DIỆN & TRẢI NGHIỆM (UI/UX POLISH AUDIT)

> **Phạm vi kiểm tra:** Mọi viewport từ Mobile (320px, 375px, 390px, 430px), Tablet (768px, 1024px) đến Desktop (1366px, 1440px, 1920px).  
> **Các phân hệ:** Client Marketing, Auth, Studio Editor, Public Invitation, Account Dashboard, Admin Management.

---

## 1. ĐỐI SOÁT CÁC MẪU THIỆP NGUỒN (SOURCE-LOCKED TEMPLATES)
- **Quy tắc bất biến:** Toàn bộ 4 mẫu thiệp cưới chính thức (`hac-bach-trao-duyen` / `hanh-phuc-mang-ten-chung-minh`, `giua-muon-ngan-vi-sao`, `ngay-tinh-yeu-dom-hoa`, `tron-kiep-nay-ben-nhau`) là các mẫu tham chiếu cố định (source-locked).
- **Trạng thái:**
  - 100% tài nguyên ảnh, font, script nằm cục bộ trong thư mục `assets/` của từng mẫu, không tải từ CDN ngoài.
  - Không lạm dụng ID tĩnh gây xung đột, toàn bộ class tuân thủ chuẩn `mayhen_invitation_*`.
  - Không chứa ảnh người thật ở Sổ Ký Tên (Signature) và Form Xác Nhận (RSVP) theo đúng quy tắc tôn trọng sự riêng tư của khách hàng.

---

## 2. KẾT QUẢ ĐỐI SOÁT RESPONSIVE & LAYOUT
1. **Studio Editor V2 (`/tao-thiep`)**:
   - Khung Center Stage (iPhone Mockup 390x844) co giãn linh hoạt theo tỷ lệ màn hình desktop.
   - Drawer bên trái cuộn độc lập, stepper 10 bước cố định footer nút "Tiếp Tục / Quay Lại".
   - Trên Mobile (<768px): Có bộ chuyển đổi `Segmented Switcher` (`[ Nhập Liệu | Xem Thiệp ]`) tránh tình trạng kẹt cuộn và che khuất màn hình.
2. **Bảng Quản Trị Admin (`/admin/*`)**:
   - Các bảng danh sách (Users, Invitations, Templates, Orders, Wishes) đều có container bọc `mayhen_admin_table_wrapper` với `overflow-x: auto`, đảm bảo trên tablet/mobile không bị vỡ layout hoặc tràn màn hình ngang.
   - Breadcrumbs tự động hiển thị đa tầng theo chuẩn Quiet Luxury.
3. **Màn hình thông báo trạng thái thiệp**:
   - Cần bổ sung trang giao diện thống nhất cho trường hợp Thiệp chưa xuất bản (`invitation-unpublished.blade.php`) và Thiệp đã hết hạn (`invitation-expired.blade.php`) đồng bộ thẩm mỹ Quiet Luxury với trang `invitation-locked.blade.php`.

