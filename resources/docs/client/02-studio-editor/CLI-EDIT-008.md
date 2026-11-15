---
id: CLI-EDIT-008
title: "Cơ chế tự động lưu nháp, hoàn tác (Undo/Redo) và khôi phục khi mất mạng"
category: "Studio Tạo Thiệp Cưới V2"
summary: "Cẩm nang hướng dẫn cơ chế bảo vệ dữ liệu 3 tầng trong Studio Mây Hẹn: Tự động lưu nháp (Autosave), lịch sử hoàn tác 50 bước (Undo/Redo), chống mất dữ liệu khi rớt mạng và khôi phục sau sự cố đóng trình duyệt đột ngột."
reader: "PER-CLI-01 / PER-CLI-02"
order: 13
updated_at: "2026-11-15"
tags: [mayhen, studio-tạo-thiệp-cưới-v2, tu-dong-luu-nhap, undo-redo, mat-ket-noi, an-toan-du-lieu]
mapped_page: "CLI-EDIT-PAGE"
mapped_route: "studio.workspace"
risk_level: "CRITICAL"
required_role: "customer"
---

# Cơ chế tự động lưu nháp, hoàn tác (Undo/Redo) và khôi phục khi mất mạng

> **Dành cho:** Cô dâu & Chú rể an tâm sáng tạo thiệp cưới mà không lo rủi ro mất dữ liệu  
> **Thời gian làm quen:** Khoảng 2 - 3 phút  
> **Vị trí trong ứng dụng:** Thanh công cụ đỉnh cao (Studio Topbar) & Phím tắt bàn phím toàn cục  

Khi chuẩn bị cưới, có vô vàn chi tiết cần chăm chút: từ câu chuyện tình yêu dài hàng trang đến danh sách lịch trình tiệc cưới phức tạp. Mối lo ngại lớn nhất của các cặp đôi là lỡ tay bấm nhầm làm mất nội dung hoặc mất kết nối mạng giữa chừng. Studio Tạo Thiệp V2 của Mây Hẹn được trang bị **Kiến Trúc Phòng Vệ Dữ Liệu 3 Tầng (3-Tier Resilience Architecture)**, bảo đảm từng ký tự, từng bức ảnh của hai bạn luôn được bảo vệ an toàn tuyệt đối.

---

## 1. Kiến trúc 3 tầng phòng vệ dữ liệu của Mây Hẹn

```
┌────────────────────────────────────────────────────────────────────────┐
│             KIẾN TRÚC PHÒNG VỆ DỮ LIỆU 3 TẦNG (RESILIENCE GRID)        │
├─────────────────────┬────────────────────┬─────────────────────────────┤
│  TẦNG 1: HOÀN TÁC   │  TẦNG 2: CỤC BỘ    │  TẦNG 3: MÁY CHỦ CLOUD      │
│     (UNDO / REDO)   │ (OFFLINE RESCUE)   │     (SERVER REVISIONS)      │
│                     │                    │                             │
│ • Ngăn xếp 50 bước  │ • Bộ nhớ IndexedDB │ • Tự động gửi nháp định kỳ  │
│ • Phím tắt Ctrl + Z │ • Không sợ mất mạng│ • Lưu lịch sử phiên bản     │
│ • Phục hồi tức thì  │ • Khôi phục khi tắt│ • Đồng bộ an toàn máy chủ   │
│   khi xóa nhầm      │   trình duyệt      │   khi trực tuyến trở lại    │
└─────────────────────┴────────────────────┴─────────────────────────────┘
```

---

## 2. Hướng dẫn sử dụng tính năng Hoàn tác (Undo / Redo)

Trong lúc chỉnh sửa thiệp, nếu bạn vô tình xóa nhầm một đoạn thơ tình hoặc kéo nhầm thứ tự các phần:

### Cách 1: Sử dụng phím tắt bàn phím (Nhanh nhất)
- **Hoàn tác bước trước (Undo):** Nhấn tổ hợp phím **`Ctrl + Z`** (trên máy Windows) hoặc **`Cmd + Z`** (trên máy Mac).
- **Làm lại bước vừa hoàn tác (Redo):** Nhấn **`Ctrl + Y`** (hoặc **`Ctrl + Shift + Z`** / **`Cmd + Shift + Z`**).
- *Dung lượng bộ nhớ:* Studio lưu trữ tối đa **50 bước thao tác liên tiếp**, cho phép bạn thoải mái thử nghiệm các phong cách khác nhau mà luôn có thể quay lại điểm xuất phát.

### Cách 2: Sử dụng nút bấm trực quan trên Topbar
- Nhìn lên góc trái của thanh công cụ Studio Topbar.
- Nhấp vào biểu tượng mũi tên cong sang trái **(↺ Hoàn tác)** hoặc mũi tên sang phải **(↻ Làm lại)**.
- Khi một nút bị mờ đi (Disable), nghĩa là bạn đã ở bước đầu tiên hoặc bước mới nhất của lịch sử chỉnh sửa.

---

## 3. Cơ chế tự động lưu nháp (Autosave) và Trạng thái Topbar

Bạn **không bao giờ phải tìm nút "Lưu" thủ công** sau mỗi lần gõ chữ. Studio Mây Hẹn kích hoạt bộ đếm thời gian thông minh (Debounce Timer 500ms):
1. Mỗi khi bạn dừng gõ chữ hoặc chọn xong một bức ảnh trong 0.5 giây, hệ thống sẽ tự động đóng gói dữ liệu và gửi lên máy chủ an toàn.
2. **Quan sát Pill Trạng thái trên Topbar:**
   - **Đang lưu... (Biểu tượng xoay nhẹ màu be):** Dữ liệu đang được mã hóa và gửi đi.
   - **Đã lưu nháp lúc HH:mm (Chấm xanh ngọc):** Mọi thay đổi của bạn đã nằm an toàn trong cơ sở dữ liệu Mây Hẹn.
   - **Ngoại tuyến (Chấm màu hổ phách):** Thiết bị đang mất mạng, dữ liệu đang được giữ an toàn trên máy của bạn.

---

## 4. Cách hệ thống bảo vệ bạn khi mất mạng hoặc tắt máy đột ngột

### Tình huống 1: Mất mạng Internet giữa chừng khi đang làm thiệp
- Nếu Wi-Fi hoặc 4G bị gián đoạn, Studio sẽ **không bao giờ khóa màn hình hay xóa nội dung** của bạn.
- Hệ thống lập tức chuyển sang chế độ **Offline Rescue**: toàn bộ thay đổi tiếp theo được ghi thẳng vào bộ nhớ đệm an toàn `IndexedDB` trên trình duyệt máy tính/điện thoại.
- Một thanh thông báo màu hổ phách xuất hiện nhẹ nhàng ở góc màn hình:  
  *"Bạn đang ngoại tuyến. Dữ liệu vẫn được bảo vệ an toàn trên thiết bị này."*
- Ngay khi có mạng trở lại, Studio sẽ tự động đồng bộ hóa ngầm toàn bộ dữ liệu lên máy chủ và thông báo: *"Đã kết nối lại và đồng bộ hóa thành công!"*.

### Tình huống 2: Hết pin, sập nguồn hoặc vô tình tắt tab trình duyệt
1. Đừng hoảng sợ! Hãy cắm sạc, mở lại trình duyệt và truy cập lại đường dẫn chỉnh sửa thiệp của bạn: `/studio/{invitation}`.
2. Studio sẽ tự động phát hiện phiên làm việc chưa hoàn tất và hiển thị hộp thoại:  
   **"Khôi phục bản nháp chưa lưu?"**  
   *"Hệ thống tìm thấy một bản nháp được lưu cục bộ lúc 15:42 (mới hơn bản trên máy chủ 5 phút)."*
3. Bạn chỉ cần nhấp nút **[Khôi Phục Bản Nháp Này]** màu vàng kim. Toàn bộ câu từ và ảnh bạn vừa làm sẽ lập tức hiện lại nguyên vẹn 100%!

---

## 5. Lưu ý quan trọng để bảo vệ dữ liệu tối đa

> [!WARNING]
> **Không xóa lịch sử duyệt web (Clear Cache/Cookie) khi đang ngoại tuyến:**  
> Nếu bạn đang trong trạng thái mất mạng và có dữ liệu chưa kịp đồng bộ lên máy chủ, vui lòng không dùng tính năng "Xóa dữ liệu duyệt web" của trình duyệt, vì hành động này có thể xóa luôn bộ nhớ đệm `IndexedDB` cục bộ. Hãy đợi khi có mạng và thanh trạng thái báo *"Đã lưu nháp"* rồi mới thao tác.

> [!TIP]
> **Làm việc cùng lúc trên 2 thiết bị:**  
> Nếu hai bạn muốn cùng chỉnh sửa thiệp (ví dụ: Chú rể xem trên máy tính văn phòng, Cô dâu xem trên điện thoại), hệ thống Mây Hẹn sẽ nhận diện xung đột phiên làm việc và nhắc nhở: *"Thiệp vừa được cập nhật từ một thiết bị khác. Bạn có muốn tải bản mới nhất không?"*, giúp hai bạn không bao giờ ghi đè làm mất công sức của nhau.

---

## 6. Bài viết liên quan tiếp theo

- [Bác sĩ Kiểm tra Chất lượng (Quality Doctor): Cách khắc phục ảnh mờ, thiếu thông tin trước khi xuất bản](/docs/client/02-studio-editor/CLI-EDIT-009)
- [So sánh các thay đổi (Publish Diff) và xuất bản thiệp cưới chính thức](/docs/client/02-studio-editor/CLI-EDIT-010)
- [Chuyển đổi sang mẫu thiệp khác mà không bị mất dữ liệu đã nhập](/docs/client/02-studio-editor/CLI-EDIT-011)
