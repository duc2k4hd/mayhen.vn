---
id: CLI-TIME-004
title: "Xuất gói lưu trữ đám cưới trọn đời (Portable Wedding Archive) tải về máy tính"
category: "Hộp thời gian & Kỷ niệm Ngày cưới"
summary: "Hướng dẫn xuất trọn gói lưu trữ đám cưới độc lập (Portable Wedding Archive): Tải file ZIP chứa trang thiệp tĩnh ngoại tuyến (index.html), toàn bộ danh bạ khách mời, sổ mừng cưới, lưu bút và ảnh kỷ niệm, tuân thủ quyền mang theo dữ liệu (Data Portability theo Luật 91/2025/QH15) có mã băm toàn vẹn SHA-256."
reader: "PER-CLI-02 / PER-CLI-03"
order: 5
updated_at: "2026-11-15"
tags: [mayhen, hộp-thời-gian, portable-archive, tai-du-lieu, zip-export, sha256-checksum, data-portability]
mapped_page: "CLI-TIME-PAGE"
mapped_route: "time_capsule.public_slug"
risk_level: "MEDIUM"
required_role: "customer"
---

# Xuất gói lưu trữ đám cưới trọn đời (Portable Wedding Archive) tải về máy tính

> **Dành cho:** Cô dâu & Chú rể muốn sao lưu và sở hữu vĩnh viễn 100% dữ liệu đám cưới trên máy tính cá nhân  
> **Thời gian tải về:** Khoảng 1 - 2 phút tùy theo dung lượng ảnh  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Tải Dữ Liệu Lưu Trữ (Download Archive)** (Route: `account.invitations.download_archive`)  
> **Dịch vụ trích xuất:** `App\Services\PortableWeddingArchiveService`

Nhiều cặp đôi lo lắng: *"Sau 10 năm hay 20 năm nữa, nếu nền tảng thay đổi hoặc đường link thiệp cưới hết hạn, liệu những hình ảnh, lời chúc và chữ ký kỷ niệm ngày cưới của mình có bị mất đi không?"*.

Tại Mây Hẹn, chúng tôi tôn trọng quyền làm chủ dữ liệu của khách hàng. Tuân thủ nghiêm ngặt **Quyền mang theo dữ liệu cá nhân (Right to Data Portability theo Luật 91/2025/QH15)**, Mây Hẹn cung cấp tính năng **Gói Lưu Trữ Đám Cưới Độc Lập (Portable Wedding Archive)**: Cho phép bạn tải về một file ZIP duy nhất chứa toàn bộ dữ liệu, có thể mở xem trực tiếp trên máy tính mà không cần Internet, lưu trữ an toàn vào ổ cứng gia đình suốt đời.

![Gói lưu trữ đám cưới độc lập Portable Wedding Archive](/assets/client/docs/images/client/09-time-capsule/cli-time-004-step-1.png)
*Tải Gói Lưu Trữ Độc Lập: Đóng gói toàn bộ thiệp cưới, ảnh gốc, lời chúc và chữ ký vào 1 tệp ZIP lưu giữ trọn đời.*

---

## 1. Cấu trúc Gói Lưu Trữ Độc Lập (Archive Structure)

Sau khi tải và giải nén file ZIP `mayhen-archive-{slug}.zip`, bạn sẽ sở hữu một thư mục hoàn chỉnh:

```
mayhen-archive-hoang-dung/
│
├── 🌐 index.html                       # Trang thiệp cưới ngoại tuyến độc lập (Nhấp đúp mở xem ngay)
├── 📜 HUONG_DAN_LUU_TRU_KY_NIEM.txt   # Thư dặn dò và hướng dẫn bảo tồn kỷ vật
├── 🔐 wedding_manifest.json            # Chứng thực số và mã băm toàn vẹn SHA-256
│
├── 📊 danh_sach_khach_moi_va_rsvp.csv  # Danh sách khách mời, bàn tiệc và phản hồi (Excel UTF-8)
├── 💰 so_mung_cuoi_va_tien_mung.csv    # Bảng kê chi tiết tiền mừng cưới và phong bì
├── 💌 loi_chuc_va_chu_ky.json          # Toàn bộ lời chúc phúc và dấu vết chữ ký số
├── 🗓️ lich_trinh_va_nha_cung_cap.json  # Timeline sự kiện và danh sách đối tác cưới
└── 💾 toan_bo_du_lieu_hon_le.json      # Bản sao lưu máy đọc chuẩn hóa (JSON Dump)
```

1. **Trang web ngoại tuyến độc lập (`index.html`):**  
   - Được biên tập bằng HTML5 và CSS nhúng trực tiếp (Zero External Dependency).  
   - Dù 50 năm nữa bạn không có mạng Internet hay máy chủ Mây Hẹn có nâng cấp thế nào, bạn chỉ cần nhấp đúp chuột vào file `index.html` trên máy Mac hoặc Windows là thiệp cưới của hai bạn sẽ hiện lên lung linh nguyên vẹn như ngày cưới thuở nào!
2. **Chứng thực toàn vẹn số (`SHA-256 Checksums`):**  
   - Mỗi file đều được đóng dấu vân tay kỹ thuật số SHA-256 trong `wedding_manifest.json` để bạn kiểm tra tính toàn vẹn, chống bị chỉnh sửa hay hỏng hóc tệp tin qua nhiều năm lưu trữ.

---

## 2. Hướng dẫn 3 bước tải gói lưu trữ về máy tính

### Bước 1: Mở mục Tải Dữ Liệu trong Quản Trị Thiệp
- Đăng nhập vào Mây Hẹn -> Chọn thiệp cưới của bạn.
- Nhấp nút **[📦 Tải Trọn Bộ Dữ Liệu Lưu Trữ (Portable Archive)]** nằm ở chân trang quản lý (Route: `account.invitations.download_archive`).

### Bước 2: Hệ thống tự động đóng gói
- Máy chủ Mây Hẹn sẽ tự động quét:
  - Toàn bộ danh bạ khách mời và nhật ký điểm danh lễ tân.
  - Sổ mừng cưới và đối soát tài chính.
  - Toàn bộ lời chúc, chữ ký tay và ảnh Photo Drop khách gửi.
  - Biên tập file `index.html` và tính toán mã băm SHA-256.
- Toàn bộ quá trình đóng gói diễn ra trong **chưa đầy 5 giây**.

### Bước 3: Tải file ZIP về máy
- Trình duyệt sẽ tự động tải file nén có tên chuẩn hóa:  
  `goi-luu-tru-cuoi-minh-hoang-thuy-dung-2026.zip`
- Bạn có thể giải nén ra để kiểm tra ngay lập tức.

---

## 3. Khuyến nghị Bảo Tồn Kỷ Vật cho Các Cặp Đôi

> [!TIP]
> **Quy tắc sao lưu 3-2-1 kinh điển:**  
> - **3 bản sao lưu:** Giữ 1 bản trên ổ cứng máy tính cá nhân, 1 bản trên ổ cứng di động cất trong tủ gia đình, và 1 bản tải lên Google Drive / iCloud của hai vợ chồng.  
> - **2 loại phương tiện:** Lưu trên cả ổ SSD và dịch vụ lưu trữ đám mây.  
> - **1 bản lưu ngoại tuyến:** Không phụ thuộc vào bất kỳ tài khoản hay mật khẩu trực tuyến nào.

> [!NOTE]
> **Xuất file sau khi đã kiểm duyệt xong ảnh và tiền mừng:**  
> Bạn nên đợi từ 3 đến 7 ngày sau đám cưới (khi đã kiểm đếm xong toàn bộ phong bì mừng cưới và duyệt hết ảnh khách gửi) rồi hãy thực hiện xuất gói lưu trữ này để có được bản chụp hoàn hảo và đầy đủ nhất.
