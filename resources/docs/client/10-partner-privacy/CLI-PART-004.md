---
id: CLI-PART-004
title: "Cổng duyệt thiệp dành cho hai bên gia đình phụ mẫu (Family Approval Portal)"
category: "Đối tác Studio & Bảo mật"
summary: "Hướng dẫn cặp đôi chia sẻ liên kết rà soát riêng biệt cho Bố Mẹ Nhà Trai và Nhà Gái, ghi nhận lời chúc phúc hoặc góp ý chỉnh sửa thông tin hôn lễ trước khi chính thức phát hành thiệp cưới."
reader: "PER-CLI-04 (Studio Cưới & Đối tác)"
order: 4
updated_at: "2026-11-15"
tags: [family-approval, nha-trai, nha-gai, review, parents, two-family, consent]
mapped_page: "CLI-PART-FAMILY"
mapped_route: "family_approval.review"
risk_level: "LOW"
required_role: "customer"
---

# Cổng duyệt thiệp dành cho hai bên gia đình phụ mẫu (Family Approval Portal)

> [!NOTE]
> **Dành cho:** Cô dâu chú rể và Phụ mẫu hai bên gia đình (Bố Mẹ Nhà Trai & Bố Mẹ Nhà Gái).  
> **Cổng rà soát của Phụ mẫu:** `/gia-dinh-duyet/{token}` (Không cần tạo tài khoản hay đăng nhập).  
> **Khu vực quản lý của Cặp đôi:** Mục *Gia đình duyệt thiệp* trong Studio Editor hoặc chi tiết thiệp cưới.

---

## 1. Ý Nghĩa Văn Hóa & Nghiệp Vụ Của Cổng Duyệt Thiệp Gia Đình

Trong văn hóa cưới hỏi truyền thống của người Việt, thiệp cưới không chỉ là lời mời của riêng đôi trẻ mà còn là đại diện thể diện cho cả hai dòng họ. Những thông tin như:
- **Tước vị & Vai vế:** Trưởng nam, Thứ nam, Ái nữ, Út nữ.
- **Họ tên phụ mẫu hai bên:** Thân phụ, Thân mẫu (hoặc Cố thân phụ/Cố thân mẫu nếu có người đã khuất).
- **Giờ hoàng đạo hôn lễ:** Giờ cử hành hôn lễ tại tư gia và giờ đón khách tại trung tâm tiệc cưới (rất quan trọng với thế hệ phụ mẫu).
- **Ngày âm lịch & dương lịch:** Bắt buộc chuẩn xác theo phong tục.

**Cổng Duyệt Thiệp Gia Đình (Two-Family Approval Portal)** của Mây Hẹn sinh ra 2 liên kết rà soát độc lập dành riêng cho Nhà Trai và Nhà Gái, giúp các bậc phụ huynh xem trước thiệp trên điện thoại dễ dàng và đóng góp ý kiến trước khi gửi thiệp tới các bậc cao niên trong họ.

---

## 2. Cách Lấy Liên Kết Duyệt Thiệp (Dành cho Cặp Đôi)

```
┌────────────────────────────────────────────────────────────────────────┐
│ TRẠNG THÁI DUYỆT THIỆP GIA ĐÌNH                                        │
├────────────────────────────────────────────────────────────────────────┤
│ 1. Nhà Trai (Bố Mẹ Chú Rể):                                            │
│    Liên kết: https://mayhen.vn/gia-dinh-duyet/gf_89a... [Sao chép]     │
│    Trạng thái: [Đã duyệt ●] - Bố Mẹ chúc hai con trăm năm hạnh phúc!   │
│                                                                        │
│ 2. Nhà Gái (Bố Mẹ Cô Dâu):                                            │
│    Liên kết: https://mayhen.vn/gia-dinh-duyet/bf_42c... [Sao chép]     │
│    Trạng thái: [Chờ duyệt ◌] - Đã gửi link qua Zalo                    │
└────────────────────────────────────────────────────────────────────────┘
```

1. Mở thiệp cưới của bạn trong Studio Editor (`/tao-thiep?edit={slug}`).
2. Tìm đến mục **Phê duyệt Gia đình (Family Approvals)**.
3. Hệ thống tạo sẵn 2 liên kết riêng biệt:
   - **Liên kết Nhà Trai:** Tiền tố mã hóa `gf_...` (Groom's Family).
   - **Liên kết Nhà Gái:** Tiền tố mã hóa `bf_...` (Bride's Family).
4. Nhấp nút **[Sao chép liên kết Nhà Trai]** và gửi vào nhóm Zalo gia đình Nhà Trai.
5. Nhấp nút **[Sao chép liên kết Nhà Gái]** và gửi vào nhóm Zalo gia đình Nhà Gái.

---

## 3. Trải Nghiệm Của Phụ Mẫu (Dành cho Bố Mẹ)

Giao diện `/gia-dinh-duyet/{token}` được thiết kế theo phong cách trang nhã, phông chữ lớn, độ tương phản cao, tối ưu tuyệt đối cho người lớn tuổi:
1. **Không cần đăng nhập:** Bố Mẹ chỉ cần nhấp trực tiếp vào đường link Zalo là mở được ngay trên điện thoại hoặc máy tính bảng.
2. **Xem bản thảo thiệp cưới:**
   - Đọc kỹ thông tin nhà trai / nhà gái.
   - Kiểm tra ngày giờ làm lễ gia tiên và tiệc mừng.
   - Xem ảnh cưới của các con và sơ đồ bản đồ chỉ đường.
3. **Thực hiện một trong hai lựa chọn:**
   - **Lựa chọn 1: [Phê duyệt & Gửi lời chúc phúc]** (`decision = approved`)  
     Bố Mẹ có thể gõ thêm vài lời chúc nhắn nhủ (Ví dụ: *"Bố Mẹ rất ưng ý với mẫu thiệp này, chúc hai con bước vào hôn nhân thuận hòa viên mãn"*).
   - **Lựa chọn 2: [Gửi góp ý chỉnh sửa]** (`decision = feedback`)  
     Nếu phát hiện sai sót (Ví dụ: *"Tên bà ngoại viết sai dấu, phần tiệc cưới nhà gái cần ghi rõ 11h trưa đón khách"*), Bố Mẹ gõ vào ô góp ý và ấn **[Gửi cho các con]**.

---

## 4. Theo Dõi & Xử Lý Góp Ý Trên Studio Editor

1. Khi Bố Mẹ gửi ý kiến, cặp đôi sẽ nhận được thông báo tức thời.
2. Tại Studio Editor của cặp đôi:
   - Nếu có góp ý: Hộp thoại màu vàng cam hiển thị nguyên văn lời nhắn của Bố hoặc Mẹ. Cặp đôi sửa lại thông tin tương ứng trong Studio và ấn **Lưu**.
   - Khi cả hai bên đều chọn Phê duyệt: Hệ thống gắn huy hiệu **ĐÃ ĐƯỢC HAI BÊN GIA ĐÌNH ĐỒNG THUẬN (100% Family Approved)**.
3. Cặp đôi có thể yên tâm 100% bấm xuất bản thiệp mà không sợ bị phàn nàn vì thiếu sót vai vế dòng tộc.

---

## 5. Tính Năng Bảo Mật & Phòng Chống Rò Rỉ

- **Token ngẫu nhiên 32 ký tự:** Không thể đoán mò hay duyệt chéo giữa các gia đình khác nhau.
- **Bộ lọc từ ngữ văn minh (NoProfanity Rule):** Hệ thống tự động ngăn chặn các ký tự hoặc từ ngữ không phù hợp trong ô góp ý và chúc phúc.
- **Khóa chỉnh sửa độc lập:** Bố Mẹ chỉ xem và góp ý, không thể vô tình bấm nhầm xóa mất thiệp hay danh sách khách mời của các con.

---

## 6. Xử Lý Các Sự Cố Thường Gặp

| Tình huống | Nguyên nhân | Hướng dẫn xử lý |
| :--- | :--- | :--- |
| **Bố Mẹ mở link báo lỗi không tìm thấy** | Đường link bị cắt cụt khi gửi qua tin nhắn (thiếu một vài ký tự ở đuôi). | Dùng nút **[Sao chép liên kết]** trên Mây Hẹn để gửi lại toàn bộ đường dẫn đầy đủ. |
| **Gửi nhầm link Nhà Gái cho Nhà Trai** | Nhầm lẫn giữa hai nút sao chép. | Gửi lại đúng link `gf_...` cho Nhà Trai. Mỗi bên gia đình có token riêng biệt để hệ thống ghi nhận đúng danh xưng. |
| **Bố Mẹ muốn đổi lại ý kiến sau khi đã duyệt** | Bố Mẹ phát hiện thêm một chi tiết cần sửa sau khi đã ấn duyệt. | Bố Mẹ có thể mở lại đúng link đó bất cứ lúc nào để gửi thêm góp ý mới. Cặp đôi luôn đọc được góp ý mới nhất. |

---

## 7. Bài viết Liên quan

- [CLI-INVT-002: Tùy biến thông tin lễ cưới, thời gian và địa điểm](file:///d:/Project/Laravel/mayhen.vn/resources/docs/client/01-getting-started/CLI-START-002.md)
- [CLI-PART-002: Tạo và bàn giao thiệp cưới cho các cặp đôi khách hàng của studio](file:///d:/Project/Laravel/mayhen.vn/resources/docs/client/10-partner-privacy/CLI-PART-002.md)
- [CLI-PART-005: Quyền riêng tư theo Luật 91/2025: Tải dữ liệu và xóa tài khoản](file:///d:/Project/Laravel/mayhen.vn/resources/docs/client/10-partner-privacy/CLI-PART-005.md)
