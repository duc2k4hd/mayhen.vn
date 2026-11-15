---
id: CLI-EDIT-003
title: "Chỉnh sửa thông tin cô dâu chú rể, phụ mẫu hai bên và ngày giờ hôn lễ"
category: "Studio Tạo Thiệp Cưới V2"
summary: "Hướng dẫn chi tiết từng bước thiết lập họ tên cô dâu chú rể, thứ bậc gia đình, phụ mẫu hai bên (kèm quy chuẩn tôn kính cố nhân) và đối soát ngày giờ hôn lễ Dương - Âm lịch chuẩn xác trên Mây Hẹn."
reader: "PER-CLI-01 / PER-CLI-02"
order: 8
updated_at: "2026-11-15"
tags: [mayhen, studio-tạo-thiệp-cưới-v2, thong-tin-cap-doi, ngay-cuoi-am-lich]
mapped_page: "CLI-EDIT-PAGE"
mapped_route: "studio.workspace"
risk_level: "HIGH"
required_role: "customer"
---

# Chỉnh sửa thông tin cô dâu chú rể, phụ mẫu hai bên và ngày giờ hôn lễ

> **Dành cho:** Cô dâu & Chú rể hoàn thiện thông tin pháp lý và văn hóa truyền thống của ngày vui  
> **Thời gian thực hiện:** Khoảng 3 - 5 phút  
> **Vị trí trong ứng dụng:** Studio Tạo Thiệp V2 → Tab **[Nội Dung]** (Content)  

Trong phong tục cưới hỏi của người Việt, thông tin về họ tên hai bên gia đình, thứ bậc con cái và ngày giờ hoàng đạo mang ý nghĩa văn hóa vô cùng trang nghiêm. Mây Hẹn thiết kế riêng bộ công cụ nhập liệu văn hóa chuẩn mực, giúp hai bạn ghi nhận đầy đủ, tinh tế và không bao giờ phạm phải các điều cấm kỵ truyền thống.

---

## 1. Điều kiện chuẩn bị

Trước khi bắt đầu nhập liệu, hai bạn nên chuẩn bị sẵn:
- Chính xác họ và tên khai sinh của Cô dâu và Chú rể.
- Họ tên thân sinh phụ mẫu hai bên (Cha và Mẹ).
- Thứ bậc chính xác trong gia đình (Trưởng nam / Thứ nam / Út nam / Giai út; Trưởng nữ / Thứ nữ / Út nữ / Gái út).
- Thời gian tổ chức hôn lễ (giờ, ngày, tháng, năm Dương lịch).
- Nếu gia đình có phụ mẫu đã qua đời, hai bạn thống nhất cách gọi trang trọng (ví dụ: tiền tố *Cố*, *Hương linh*, hoặc ký hiệu hoa sen trang nghiêm).

---

## 2. Hướng dẫn từng bước thao tác

```
┌────────────────────────────────────────────────────────┐
│ STUDIO V2 → TAB [NỘI DUNG] → KHUNG THÔNG TIN GIA ĐÌNH  │
├────────────────────────────────────────────────────────┤
│  [1] Nhà Trai: Chú Rể • Thứ Bậc • Họ Tên Phụ Mẫu      │
│  [2] Nhà Gái:  Cô Dâu • Thứ Bậc • Họ Tên Phụ Mẫu      │
│  [3] Tôn Kính: Tích chọn "Đã mất" để gắn tiền tố "Cố"  │
│  [4] Thời Gian: Chọn ngày Dương → Tự động tính Âm lịch │
└────────────────────────────────────────────────────────┘
```

### Bước 1: Mở mục Thông tin Cặp đôi trong Studio
1. Tại giao diện Studio Tạo Thiệp (`/tao-thiep` hoặc `/studio/{invitation}`), nhìn sang thanh công cụ bên trái (Left Rail).
2. Nhấp vào tab đầu tiên: **[Nội Dung]** (biểu tượng trang giấy bút ✎).
3. Khu vực soạn thảo mở ra mục **"Thông Tin Cặp Đôi & Gia Đình"**.

### Bước 2: Thiết lập thông tin Chú Rể và Nhà Trai
1. **Họ và tên Chú Rể (`groom_name`):** Nhập đầy đủ họ tên có dấu (Ví dụ: `Đỗ Tuấn Minh`).
2. **Tên gọi thân mật / Biệt danh (Tùy chọn):** Nếu thiệp cưới mang phong cách hiện đại cho bạn bè bè bạn, bạn có thể điền thêm (Ví dụ: `Minh Đỗ`).
3. **Thứ bậc trong gia đình (`groom_order`):** Nhấp chọn trong menu thả xuống:
   - *Trưởng nam* (Con trai cả)
   - *Thứ nam* (Con trai thứ)
   - *Út nam* (Con trai út)
   - *Quý nam* (Con trai một)
4. **Họ tên Phụ mẫu Nhà Trai:**
   - **Thân phụ (`groom_father_name`):** Điền họ tên bố chú rể (Ví dụ: `Đỗ Quốc Tuấn`).
   - **Thân mẫu (`groom_mother_name`):** Điền họ tên mẹ chú rể (Ví dụ: `Lê Thị Mai`).
5. **Quy chuẩn tôn kính phụ mẫu đã khuất:**
   - Nếu thân phụ hoặc thân mẫu đã qua đời, hãy gạt công tắc **[Đã mất]** bên cạnh tên.
   - Hệ thống Mây Hẹn sẽ tự động thêm tiền tố trang trọng (ví dụ: *Cố Phụ Đỗ Quốc Tuấn*) và định dạng font chữ nghiêm cẩn, tránh việc in ấn thiếu trang trọng.

### Bước 3: Thiết lập thông tin Cô Dâu và Nhà Gái
1. **Họ và tên Cô Dâu (`bride_name`):** Nhập họ tên đầy đủ (Ví dụ: `Nguyễn Lan Anh`).
2. **Thứ bậc trong gia đình (`bride_order`):** Nhấp chọn:
   - *Trưởng nữ* (Con gái cả)
   - *Thứ nữ* (Con gái thứ)
   - *Út nữ* (Con gái út)
   - *Ái nữ* (Con gái một)
3. **Họ tên Phụ mẫu Nhà Gái:**
   - **Thân phụ (`bride_father_name`):** Điền họ tên bố cô dâu (Ví dụ: `Nguyễn Văn Thành`).
   - **Thân mẫu (`bride_mother_name`):** Điền họ tên mẹ cô dâu (Ví dụ: `Trần Thu Hà`).
   - Tương tự, sử dụng công tắc **[Đã mất]** nếu cần thể hiện lòng hiếu kính người đã khuất.

### Bước 4: Thiết lập Ngày giờ & Tính toán Âm Lịch tự động
1. Tại mục **"Ngày Giờ Hôn Lễ"**, nhấp vào ô chọn ngày (`wedding_date`).
2. Chọn ngày Dương lịch trong lịch tương tác (Ví dụ: `28/12/2026`).
3. Chọn giờ tổ chức lễ chính (Ví dụ: `11:00`).
4. **Công nghệ Đối soát Âm Lịch Thông Minh:**
   - Mây Hẹn tự động tra cứu thuật toán thiên văn Việt Nam và hiển thị ngay dòng đối chiếu:  
     `Nhằm ngày 20 tháng 11 năm Bính Ngọ (Giờ Hoàng Đạo: Giáp Thìn)`
   - Bạn không cần phải mở lịch vạn niên hay lo lắng tính sai ngày Âm lịch.
   - Nếu bạn muốn tùy chỉnh cách ghi (ví dụ: *Nhằm ngày hai mươi tháng một năm Ất Tỵ* bằng chữ), bạn có thể bật công tắc **[Ghi ngày Âm bằng chữ]**.

---

## 3. Dữ liệu thực tế kiểm chứng (Mock Data Mẫu)

| Trường thông tin | Dữ liệu mẫu chuẩn Mây Hẹn | Vị trí hiển thị trên thiệp |
| :--- | :--- | :--- |
| **Chú Rể** | Đỗ Tuấn Minh (Trưởng nam) | Hero Cover, Section 4 (Family & Couple) |
| **Phụ mẫu Nhà Trai** | Ông: Đỗ Quốc Tuấn • Bà: Lê Thị Mai | Section 4 (Family & Couple) |
| **Cô Dâu** | Nguyễn Lan Anh (Út nữ) | Hero Cover, Section 4 (Family & Couple) |
| **Phụ mẫu Nhà Gái** | Ông: Nguyễn Văn Thành • Bà: Trần Thu Hà | Section 4 (Family & Couple) |
| **Ngày Dương lịch** | 11:00 Chủ Nhật, 28/12/2026 | Hero Cover, Section 5 (Countdown & Calendar) |
| **Ngày Âm lịch** | Ngày 20 tháng 11 năm Bính Ngọ | Section 5 (Lịch cưới truyền thống) |

---

## 4. Dấu hiệu nhận biết thành công

1. **Live Preview phản hồi tức thì:** Ngay khi nhập xong họ tên, quan sát sang màn hình điện thoại bên phải:
   - Phần bìa thiệp (Cover) hiển thị tên lồng ghép uyển chuyển: `Tuấn Minh & Lan Anh`.
   - Section 4 (Gia đình hai bên) cập nhật đầy đủ danh vị phụ mẫu và thứ bậc.
2. **Topbar báo đã lưu:** Pill trạng thái trên cùng chuyển sang màu xanh ngọc với chữ **"Đã lưu nháp"**.
3. **Bác Sĩ Thiệp Cưới ghi điểm:** Tiêu chí *"Thông tin gia đình và ngày cưới"* được đánh dấu tích xanh `✓ Hoàn thành (+20 điểm)`.

---

## 5. Lỗi thường gặp và cách khắc phục

- **Lỗi 1: Tên bị dài tràn khung điện thoại nhỏ (màn hình < 375px)**
  - *Nguyên nhân:* Họ tên quá dài hoặc font chữ viết hoa toàn bộ (All-caps).
  - *Khắc phục:* Trong tab [Kiểu Chữ], chuyển font tên chính sang phong cách Calligraphy thanh thoát hoặc bật chế độ tự động co giãn thông minh (Responsive Text Clamp).
- **Lỗi 2: Nhầm lẫn thứ bậc con trai / con gái một**
  - *Khắc phục:* Con trai duy nhất trong nhà chọn *Quý nam*, con gái duy nhất chọn *Ái nữ*. Không nên chọn *Trưởng nam/Trưởng nữ* nếu gia đình không có người con thứ hai.
- **Lỗi 3: Ngày Âm lịch hiển thị lệch 1 ngày**
  - *Nguyên nhân:* Do lệch múi giờ trên thiết bị cá nhân (chưa đặt múi giờ GMT+7 Việt Nam).
  - *Khắc phục:* Hệ thống Mây Hẹn chuẩn hóa thời gian theo múi giờ `Asia/Ho_Chi_Minh`. Bạn chỉ cần lưu lại ngày Dương lịch, hệ thống sẽ chốt cố định ngày Âm lịch chính xác theo lịch pháp quốc gia.

---

## 6. Bài viết liên quan tiếp theo

- [Chỉnh sửa sự kiện cưới, bản đồ Google Maps & chỉ đường đón khách](/docs/client/02-studio-editor/CLI-EDIT-006)
- [Căn chỉnh tiêu điểm ảnh cưới Smart Focal Point trong Studio](/docs/client/02-studio-editor/CLI-EDIT-002)
- [Cài đặt sổ mừng cưới VietQR tự động qua SePay](/docs/client/08-gifts-vietqr/CLI-GIFT-001)
