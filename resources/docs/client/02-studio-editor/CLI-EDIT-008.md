---
id: CLI-EDIT-008
title: "Cơ chế tự động lưu nháp, hoàn tác 50 bước (Undo/Redo) và bảo vệ dữ liệu khi mất mạng"
category: "Studio Tạo Thiệp Cưới V2"
summary: "Hướng dẫn theo dõi trạng thái tự động lưu trên thanh đầu trang Studio, sử dụng nút Hoàn tác / Làm lại (Ctrl+Z / Ctrl+Y) cho mọi thao tác chữ, ảnh, trang trí và cách khôi phục bản nháp khi gián đoạn kết nối."
reader: "Cô dâu & Chú rể"
order: 13
updated_at: "2026-11-18"
tags: [mayhen, studio-tạo-thiệp-cưới-v2, tu-dong-luu-nhap, undo-redo, mat-ket-noi, an-toan-du-lieu]
mapped_page: "CLI-EDIT-PAGE"
mapped_route: "studio.workspace"
risk_level: "CRITICAL"
required_role: "customer"
---

# Cơ chế tự động lưu nháp, hoàn tác 50 bước (Undo/Redo) và bảo vệ dữ liệu khi mất mạng

> **Dành cho:** Cô dâu & Chú rể yên tâm thử nghiệm mọi kiểu chữ, màu sắc, nhãn dán mà không sợ bấm nhầm hay mất công sức nhập liệu  
> **Thời gian làm quen:** Khoảng 1 – 2 phút  
> **Vị trí thao tác:** Cụm trạng thái lưu và nút **Hoàn tác / Làm lại (`↺` / `↻`)** trên thanh đầu trang `/tao-thiep`  

Khi chăm chút từng dòng kỷ niệm hay thử phối nhiều phông chữ, nhãn dán khác nhau trên thiệp, đôi lúc bạn sẽ muốn quay lại phương án trước đó hoặc lo lắng trình duyệt bị đóng bất ngờ. Studio Tạo Thiệp Mây Hẹn bảo vệ công sức của hai bạn hoàn toàn tự động qua **3 lớp an toàn liên hoàn**: lịch sử hoàn tác tức thì, bộ nhớ nháp trên trình duyệt và đồng bộ tự động lên máy chủ.

---

## 1. Theo dõi trạng thái Tự động lưu nháp ngay trên thanh đầu trang

Bạn **không cần phải bấm nút Lưu thủ công** sau mỗi lần gõ chữ hay đổi màu. Ngay bên phải nút `Đổi mẫu` trên thanh đầu trang (`Studio Topbar`) luôn có viên thuốc báo trạng thái lưu trực tiếp:

![Viên thuốc báo trạng thái tự động lưu và nút Hoàn tác mở sáng trên thanh đầu trang Studio](/assets/client/docs/images/client/02-studio-editor/cli-edit-008-step-1.png)
*Ngay khi bạn sửa câu chào hoặc thông tin trên thiệp, trạng thái lưu tự động cập nhật và nút Hoàn tác (↺) sáng lên sẵn sàng.*

- **`Đã sẵn sàng`**: Trạng thái khởi tạo ổn định khi bạn vừa mở Studio.
- **`Đã lưu trình duyệt`**: Khi bạn đang thiết kế thử nghiệm trên `/tao-thiep` (kể cả trước khi đăng nhập), mọi chữ viết, bảng màu, phông chữ và nhãn dán vừa chỉnh đều được ghi ngay vào bộ nhớ an toàn của trình duyệt — lỡ tay bấm tải lại trang (`F5`) cũng không bị mất.
- **`Đang lưu...` ➔ `Đã lưu nháp`**: Khi bạn chỉnh sửa thiệp trong tài khoản cá nhân, hệ thống tự động đồng bộ ngầm các thay đổi lên máy chủ Mây Hẹn chỉ sau nửa giây ngừng gõ.

---

## 2. Hoàn tác & Làm lại (`Undo / Redo`) tới 50 bước liên tiếp

Nếu bạn vừa thử đổi sang một phông chữ khác, kéo lệch một nhãn dán trang trí hoặc xóa nhầm một đoạn lời ngỏ và muốn lấy lại ngay lập tức:

![Bấm nút Hoàn tác trên thanh đầu trang để khôi phục ngay nội dung trước đó và bật sáng nút Làm lại](/assets/client/docs/images/client/02-studio-editor/cli-edit-008-step-2.png)
*Sau khi bấm Hoàn tác (↺), câu chào quay về nguyên trạng ban đầu và nút Làm lại (↻) bên cạnh sáng lên để bạn dễ dàng so sánh 2 phương án.*

### Cách 1: Bấm nút mũi tên trên thanh đầu trang
- Bấm nút mũi tên cong trái **`↺` (Hoàn tác)** để lùi lại thao tác vừa làm.
- Bấm nút mũi tên cong phải **`↻` (Làm lại)** nếu bạn đổi ý muốn giữ lại bước vừa hoàn tác.
- Khi nút hiển thị mờ nghĩa là bạn đang ở điểm đầu tiên hoặc mới nhất của chuỗi thao tác.

### Cách 2: Dùng phím tắt bàn phím siêu nhanh
- **Hoàn tác (`Undo`):** Nhấn **`Ctrl + Z`** (Windows) hoặc **`Cmd + Z`** (Mac).
- **Làm lại (`Redo`):** Nhấn **`Ctrl + Y`** (hoặc **`Ctrl + Shift + Z`** / **`Cmd + Shift + Z`**).
- Bộ nhớ lịch sử ghi nhận tới **50 bước chỉnh sửa liên tiếp** cho cả nội dung văn bản, màu sắc, bố cục 10 phần lẫn các thao tác kéo thả, đổi font và hiệu ứng trực tiếp trên thiệp!

---

## 3. Điều gì xảy ra khi rớt mạng Wi-Fi/4G hoặc tắt nhầm trình duyệt?

1. **Khi mất mạng tạm thời giữa chừng:**
   - Studio không khóa màn hình của bạn. Bạn vẫn tiếp tục gõ nội dung và căn chỉnh bình thường; dữ liệu được giữ nguyên trong bộ nhớ cục bộ trên thiết bị.
   - Ngay khi Wi-Fi hoặc 4G kết nối trở lại, hệ thống tự động gửi bản cập nhật mới nhất lên máy chủ.
2. **Khi có bản nháp trên máy mới hơn bản trên máy chủ:**
   - Nếu trước đó bạn đang sửa dở mà máy tính hết pin hoặc mất mạng rồi mở lại thiệp sau đó, đầu khung nhập liệu sẽ hiển thị thông báo rõ ràng: **"Có bản nháp chưa khớp với thiệp hiện tại"** kèm 2 lựa chọn an toàn: **`Tải bản nháp xuống`** hoặc **`Dùng bản trên máy chủ`**, đảm bảo bạn không bao giờ bị mất dữ liệu ngoài ý muốn.

> [!WARNING]
> **Lưu ý nhỏ khi chưa đăng nhập tài khoản:**  
> Nếu bạn đang thiết kế thử ở chế độ khách chưa đăng nhập và dùng cửa sổ Ẩn danh (Incognito), việc đóng hoàn toàn trình duyệt ẩn danh sẽ xóa bộ nhớ tạm của trình duyệt. Vì vậy, khi đã ưng ý bản thiết kế, hãy bấm **`Xuất Bản Thiệp →`** và đăng nhập để lưu vĩnh viễn tấm thiệp vào tài khoản Mây Hẹn của bạn nhé!

---

## 4. Xem tiếp bài viết liên quan

- [Bác Sĩ Thiệp Cưới (Quality Doctor): Kiểm tra thiếu sót trước khi xuất bản](/docs/studio-editor/CLI-EDIT-009)
- [Xuất bản thiệp cưới chính thức & Báo cáo tác động thay đổi](/docs/studio-editor/CLI-EDIT-010)
- [Đổi sang mẫu thiệp khác mà không bị mất dữ liệu đã nhập](/docs/studio-editor/CLI-EDIT-011)
