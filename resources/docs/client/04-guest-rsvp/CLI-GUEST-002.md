---
id: CLI-GUEST-002
title: "Hướng dẫn tải tệp mẫu Excel/CSV và nhập hàng loạt 500+ khách mời trong 1 phút"
category: "Quản lý Khách mời & RSVP"
summary: "Cẩm nang hướng dẫn chi tiết cách tải tệp mẫu CSV chuẩn UTF-8 BOM, lập danh sách khách mời trong Excel/Google Sheets, xử lý trùng lặp và nhập hàng loạt hơn 500 khách mời kèm phòng vệ an toàn dữ liệu."
reader: "PER-CLI-02 / PER-CLI-03"
order: 12
updated_at: "2026-11-13"
tags: [mayhen, quản-lý-khách-mời-&-rsvp, csv-import, excel, utf-8-bom]
mapped_page: "CLI-GUEST-PAGE"
mapped_route: "guests.index"
risk_level: "HIGH"
required_role: "customer"
---

# Hướng dẫn tải tệp mẫu Excel/CSV và nhập hàng loạt 500+ khách mời trong 1 phút

> **Dành cho:** Cô dâu, Chú rể và người phụ trách danh sách khách mời  
> **Thời gian thực hiện:** Khoảng 1 - 3 phút  
> **Vị trí trong ứng dụng:** Trang Quản lý Khách mời (`/khach-moi/{id}`) -> Tab **Nhập Từ Tệp CSV (Excel)**  

Việc chuẩn bị danh sách khách mời cho một đám cưới từ 200 đến hơn 500 khách thường tốn nhiều công sức nếu phải nhập thủ công từng người một. Mây Hẹn cung cấp công cụ **Smart CSV Import** chuẩn hóa cao độ, giúp bạn đưa toàn bộ danh sách khách từ Microsoft Excel, Apple Numbers hoặc Google Sheets vào hệ thống chỉ trong một lần tải lên, bảo đảm không lỗi font tiếng Việt và tự động sinh link thiệp cá nhân hóa cho từng khách mời.

---

## 1. Cấu trúc tệp mẫu CSV chuẩn hóa của Mây Hẹn

Để tránh lỗi vỡ font tiếng Việt có dấu khi mở bằng Microsoft Excel trên Windows, tệp mẫu của Mây Hẹn được mã hóa chuẩn **UTF-8 with BOM (Byte Order Mark)**. Bạn hãy bấm vào nút **[Tải Tệp CSV Mẫu]** ngay trên màn hình để nhận tệp chuẩn.

Bảng dưới đây giải thích chi tiết ý nghĩa từng cột trong tệp mẫu:

| Tên cột (Header) | Bắt buộc | Ví dụ mẫu | Ý nghĩa & Hướng dẫn nhập |
| :--- | :---: | :--- | :--- |
| `name` | **Có** | `Anh Hoàng & Bạn gái` | Họ tên khách mời hiển thị trang trọng trên thiệp mời |
| `salutation` | Không | `Thân mời` | Cách xưng hô mở đầu thiệp (`Kính mời`, `Thân mời`, `Thương mời`) |
| `group_name` | Không | `Bạn Đại Học` | Phân nhóm khách (`Họ Nhà Trai`, `Họ Nhà Gái`, `Bạn Bè`, `Đồng Nghiệp`, `Khách VIP`) |
| `expected_attendees` | Không | `2` | Số lượng người lớn dự kiến tham dự (mặc định là 1 hoặc 2) |
| `max_attendees` | Không | `2` | Giới hạn suất tối đa cho phép khách đăng ký thêm khi RSVP (+1 Policy) |
| `phone` | Không | `0912345678` | Số điện thoại nhận mã check-in hoặc gửi tin nhắn Zalo |
| `table_name` | Không | `Bàn 06` | Tên hoặc số bàn tiệc dự kiến xếp cho khách |
| `notes` | Không | `Ăn chay trường` | Ghi chú riêng của cặp đôi (dị ứng, chỗ ngồi gần gia đình...) |

---

## 2. Quy trình 4 bước nhập danh sách khách hàng loạt

### Bước 1: Tải tệp CSV mẫu từ hệ thống
1. Truy cập vào trang Quản lý Khách mời của thiệp cưới.
2. Bấm chọn tab **[Nhập Từ Tệp CSV (Excel)]**.
3. Nhấp nút **[📥 Tải Tệp CSV Mẫu]**. Tệp `danh-sach-khach-mau-mayhen.csv` sẽ được tải về máy tính của bạn.

### Bước 2: Điền danh sách khách trong Excel hoặc Google Sheets
1. Mở tệp vừa tải về bằng Microsoft Excel hoặc Google Sheets.
2. Sao chép danh sách bạn bè, người thân từ sổ tay hoặc file có sẵn vào đúng các cột tương ứng.
3. **Lưu ý quan trọng:** Giữ nguyên hàng tiêu đề đầu tiên (`name`, `salutation`, `group_name`...).
4. Sau khi điền xong, chọn **File -> Save As -> Định dạng CSV (Comma delimited) (*.csv)**. Nếu dùng Google Sheets, chọn **Tệp -> Tải xuống -> Giá trị phân tách bằng dấu phẩy (.csv)**.

### Bước 3: Cấu hình chế độ xử lý trên Mây Hẹn
Quay lại màn hình Mây Hẹn, chọn các tùy chọn phù hợp trước khi tải lên:
- **Chọn tệp CSV từ máy tính:** Chọn tệp `.csv` bạn vừa lưu. Hệ thống chấp nhận tệp có dung lượng tối đa 5MB.
- **Chế độ xử lý trùng lặp (Duplicate Mode):**
  - `Bỏ qua nếu đã tồn tại (Khuyên dùng):` Nếu một khách mời đã có trong hệ thống (trùng số điện thoại hoặc trùng cả Họ tên và Phân nhóm), hệ thống sẽ giữ nguyên bản ghi cũ và bỏ qua dòng này để tránh làm mất mã link thiệp đã gửi cho khách trước đó.
  - `Cập nhật thông tin khách trùng:` Cập nhật lại số người dự kiến, bàn tiệc hoặc ghi chú mới đè lên bản ghi cũ.
- **Xưng hô mặc định:** Chọn từ mặc định (ví dụ: *Thân mời*) áp dụng cho những dòng mà bạn để trống cột `salutation`.
- **Nhóm khách mặc định:** Chọn nhóm mặc định (ví dụ: *Bạn Bè*) áp dụng cho những dòng chưa phân nhóm.

### Bước 4: Tải lên và nhận kết quả
1. Nhấp nút **[+ Tải Lên & Nhập Danh Sách]**.
2. Quá trình xử lý chỉ mất từ 1 đến 3 giây cho tệp 500 khách.
3. Khi hoàn tất, hệ thống sẽ hiển thị bảng thông báo kết quả chi tiết:
   - Số lượng khách thêm mới thành công.
   - Số dòng bị bỏ qua do trùng lặp.
   - Số dòng gặp lỗi định dạng (nếu có) kèm hướng dẫn khắc phục.

---

## 3. Cơ chế an toàn và phòng vệ bảo mật (Security Defense)

> [!NOTE]
> **Phòng vệ mã độc bảng tính (CSV Formula Injection Defense - CWE-1236):**  
> Khi bạn nhập tên hoặc ghi chú của khách bắt đầu bằng các ký tự công thức toán học (`=`, `+`, `-`, `@`), dịch vụ bảo vệ `CsvSanitizerService` của Mây Hẹn sẽ tự động chèn thêm một dấu nháy đơn `'` ở phía trước. Điều này ngăn chặn triệt để nguy cơ tệp bị phần mềm Excel thực thi mã lệnh nguy hiểm khi xuất ngược trở lại.

> [!TIP]
> **Tự động nhận diện dấu phân cách linh hoạt:**  
> Bạn không cần lo lắng về việc máy tính dùng dấu phẩy (`,`), dấu chấm phẩy (`;`) hay phím Tab (`\t`) để ngăn cách cột. Bộ đọc CSV thông minh của Mây Hẹn sẽ tự động quét 5 dòng đầu tiên để nhận diện chuẩn xác định dạng của máy tính bạn đang dùng.

---

## 4. Xử lý các sự cố thường gặp khi nhập CSV

| Hiện tượng | Nguyên nhân | Cách khắc phục triệt để |
| :--- | :--- | :--- |
| **Chữ tiếng Việt bị biến thành ký tự lạ (vỡ font)** | Khi lưu từ Excel cũ, tệp bị lưu theo mã hóa ANSI thay vì UTF-8 | Hãy dùng tệp mẫu có sẵn của Mây Hẹn (đã có UTF-8 BOM), hoặc mở tệp bằng Notepad -> chọn **Save As** -> mục **Encoding** chọn **UTF-8**. |
| **Báo lỗi thiếu cột bắt buộc `name`** | Hàng tiêu đề bị chỉnh sửa hoặc bị xóa mất cột `name` | Đảm bảo ô đầu tiên của hàng 1 luôn là chữ `name` viết thường. |
| **Khách bị gộp chung vào 1 cột duy nhất** | Excel lưu dấu phân cách là dấu chấm phẩy trong khi cài đặt là dấu phẩy | Hệ thống Mây Hẹn đã tự động xử lý. Nếu vẫn gặp lỗi, bạn hãy mở tệp trong Google Sheets rồi tải xuống lại dưới dạng CSV chuẩn. |
| **Số điện thoại bị mất số 0 ở đầu** | Excel tự động hiểu số điện thoại là con số và cắt bỏ số 0 đầu tiên | Thêm dấu nháy đơn `'` trước số điện thoại trong Excel (ví dụ: `'0912345678`) để Excel hiểu đây là dạng chuỗi văn bản. |

