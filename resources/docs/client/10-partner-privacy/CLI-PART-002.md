---
id: CLI-PART-002
title: "Tạo và bàn giao thiệp cưới cho các cặp đôi khách hàng của studio"
category: "Đối tác Studio & Bảo mật"
summary: "Quy trình trọn vẹn dành cho Studio: từ khởi tạo thiệp cưới, tùy biến ảnh cưới trong Studio Editor, sinh mã bàn giao (Handover Code) an toàn đến khi cặp đôi tiếp nhận quyền sở hữu thiệp."
reader: "PER-CLI-04 (Studio Cưới & Đối tác)"
order: 2
updated_at: "2026-11-15"
tags: [partner, handover, studio, wedding-transfer, handover-code, claim]
mapped_page: "CLI-PART-HANDOVER"
mapped_route: "partner.weddings.handover"
risk_level: "MEDIUM"
required_role: "partner"
---

# Tạo và bàn giao thiệp cưới cho các cặp đôi khách hàng của studio

> [!NOTE]
> **Dành cho:** Studio ảnh cưới, Wedding Planner và Cặp đôi nhận thiệp.  
> **Khu vực quản lý của Studio:** `/doi-tac/danh-sach-thiep` và `/doi-tac/thiep/{id}/ban-giao`.  
> **Cổng nhận thiệp của Cặp đôi:** `/nhan-thiep-doi-tac` (Hoặc nhấp nút *Nhận thiệp từ Studio* trên Dashboard tài khoản).

---

## 1. Tổng Quan Quy Trình Bàn Giao Độc Quyền (Ownership Handover)

Thông thường, studio chụp ảnh cưới sẽ hỗ trợ cô dâu chú rể thiết kế, chèn ảnh phóng, ảnh pre-wedding và chọn nhạc nền ưng ý trước. Sau khi thiệp đã hoàn thiện, studio sẽ **chuyển giao toàn quyền sở hữu** cho cặp đôi để cô dâu chú rể tự tay quản lý danh sách khách mời, gửi thiệp qua Zalo và nhận tiền mừng cưới VietQR về tài khoản ngân hàng của riêng họ.

Kiến trúc bàn giao của Mây Hẹn đảm bảo:
- **Tuyệt đối an toàn:** Sử dụng mã bàn giao ngẫu nhiên 6 ký tự độc bản (Handover Code), hiệu lực trong 30 ngày.
- **Không mất dữ liệu:** Toàn bộ ảnh cưới, lời tự sự, timeline và nhạc nền studio đã dựng đều được bảo toàn 100%.
- **Giữ nguyên quyền lợi Đối tác:** Thiệp sau khi chuyển sang tài khoản cô dâu chú rể vẫn giữ liên kết `partner_id` để ghi nhận hoa hồng khi cặp đôi nâng cấp gói dịch vụ và giữ logo Co-branding của studio ở chân thiệp.

---

## 2. Các Bước Tạo Thiệp Cho Khách Hàng (Dành cho Studio)

### Bước 1: Khởi tạo đám cưới mới
1. Đăng nhập tài khoản đối tác, truy cập menu **Quản lý thiệp cưới** (`/doi-tac/danh-sach-thiep`).
2. Nhấp nút **[+ Tạo thiệp mới cho khách]** (`/doi-tac/tao-thiep`).
3. Điền các thông tin ban đầu:
   - **Tên chú rể & Tên cô dâu:** (Ví dụ: *Quốc Tuấn & Lan Anh*).
   - **Ngày tổ chức hôn lễ:** Chọn ngày cưới dự kiến.
   - **Địa điểm tổ chức & Địa chỉ:** (Ví dụ: *Trung tâm Tiệc cưới Trống Đồng Palace, 72 Quán Sứ, Hà Nội*).
   - **Ghi chú nội bộ Studio:** (Ví dụ: *Gói chụp Diamond - Đã gửi ảnh demo đợt 1*).
4. Chọn một mẫu thiệp cưới phù hợp từ kho giao diện Mây Hẹn và nhấp **[Khởi tạo thiệp & Vào Studio]**.

### Bước 2: Tùy biến thiết kế trong Studio Editor
Hệ thống tự động chuyển bạn vào Studio trực quan:
- Tải lên bộ ảnh cưới chất lượng cao của cặp đôi vào mục **Album ảnh**.
- Chọn bản nhạc nền lãng mạn yêu thích.
- Điền câu chuyện tình yêu (Love Story) và lịch trình tiệc (Timeline).
- Nhấp **[Lưu thay đổi]** khi hoàn thành.

---

## 3. Sinh Mã Bàn Giao & Gửi Cho Cặp Đôi

Khi sản phẩm thiệp cưới đã hoàn thiện và sẵn sàng trao cho khách hàng:

```
┌────────────────────────────────────────────────────────────────────────┐
│ MÃ BÀN GIAO THIỆP CƯỚI: [   M H 8 9 A B   ]   [Sao chép mã]           │
│ Trạng thái: Chưa nhận | Hiệu lực đến: 15/12/2026 (Còn 29 ngày)          │
│ Đường dẫn nhận thiệp nhanh:                                            │
│ https://mayhen.vn/nhan-thiep-doi-tac?code=MH89AB  [Sao chép liên kết]   │
└────────────────────────────────────────────────────────────────────────┘
```

1. Tại danh sách thiệp cưới Studio, nhấp nút **[Bàn giao cho khách]** tại hàng tương ứng (`/doi-tac/thiep/{id}/ban-giao`).
2. Hệ thống tự động sinh một mã bàn giao 6 ký tự (Ví dụ: `MH89AB`).
3. Studio có 2 cách gửi cho khách:
   - **Cách 1 (Nhanh nhất):** Nhấp **[Sao chép liên kết]** và gửi qua Zalo/Messenger cho cặp đôi.
   - **Cách 2:** Gửi mã 6 ký tự `MH89AB` để cặp đôi tự nhập trên website.
4. **Cấp lại mã mới (`regenerate`):** Nếu lỡ gửi nhầm cho khách hàng khác hoặc mã đã hết hạn 30 ngày, nhấp nút **[Tạo mã mới]**. Mã cũ sẽ bị hủy tức thì.
5. **Thu hồi quyền bàn giao (`revoke`):** Nếu khách hàng hủy hợp đồng dịch vụ chụp ảnh, nhấp **[Thu hồi mã]** để khóa quyền tiếp nhận thiệp.

---

## 4. Hướng Dẫn Cặp Đôi Tiếp Nhận Thiệp Về Tài Khoản

Khi nhận được liên kết hoặc mã từ Studio, cô dâu chú rể thực hiện theo 3 bước đơn giản:

1. **Bước 1:** Đăng nhập (hoặc đăng ký miễn phí) tài khoản cá nhân trên `https://mayhen.vn`.
2. **Bước 2:** Truy cập trang nhận thiệp tại `/nhan-thiep-doi-tac` hoặc dán đường link Studio gửi vào trình duyệt.
3. **Bước 3:** Nhập mã bàn giao (Ví dụ: `MH89AB`) và nhấp nút **[Xác nhận nhận thiệp]**.
4. **Kết quả:**
   - Hệ thống chuyển quyền sở hữu đám cưới từ Studio sang tài khoản cá nhân của cặp đôi.
   - Thiệp cưới xuất hiện ngay lập tức trong bảng điều khiển của cặp đôi tại mục *Thiệp của tôi*.
   - Cặp đôi có toàn quyền cập nhật số tài khoản ngân hàng nhận tiền mừng VietQR, thêm danh sách khách mời và gửi thiệp mời đi.

---

## 5. Bảng Trạng Thái Vòng Đời Bàn Giao (Handover Lifecycle)

| Trạng thái hiển thị tại Studio | Ý nghĩa nghiệp vụ | Hành động tiếp theo |
| :--- | :--- | :--- |
| **Đang dựng (Draft)** | Thiệp do Studio quản lý, chưa phát hành mã bàn giao. | Tiếp tục thiết kế và tải ảnh cưới lên. |
| **Chờ khách nhận (Pending Claim)** | Đã sinh mã bàn giao, cặp đôi chưa kích hoạt. | Nhắc khách hàng mở link để nhận thiệp trước khi hết hạn 30 ngày. |
| **Đã bàn giao (Claimed)** | Cặp đôi đã nhập mã thành công và đang quản lý thiệp. | Studio vẫn có thể xem được thiệp nhưng không thể can thiệp danh sách khách mời của cặp đôi. |
| **Mã hết hạn (Expired)** | Quá 30 ngày kể từ ngày sinh mã mà khách chưa kích hoạt. | Nhấp nút **[Tạo mã mới]** để gia hạn thêm 30 ngày cho khách. |

---

## 6. Xử Lý Các Sự Cố Thường Gặp

| Sự cố | Nguyên nhân | Hướng giải quyết |
| :--- | :--- | :--- |
| **Khách báo mã không hợp lệ** | Khách gõ nhầm số 0 với chữ O, hoặc chữ I với số 1. | Gửi trực tiếp đường link nhận nhanh có gắn sẵn mã `?code=...` để khách chỉ cần ấn nút. |
| **Khách báo mã đã được sử dụng** | Một trong hai người (chú rể hoặc cô dâu) đã đăng nhập và ấn nhận trước đó. | Kiểm tra xem thiệp đã nằm trong tài khoản của vợ hoặc chồng chưa. Một thiệp chỉ thuộc về 1 tài khoản duy nhất. |
| **Studio muốn sửa lại ảnh sau khi đã bàn giao** | Quyền sở hữu chính đã chuyển sang cho khách hàng. | Cặp đôi có thể tự thay ảnh trong tài khoản của họ, hoặc liên hệ bộ phận hỗ trợ kỹ thuật Mây Hẹn để được trợ giúp. |

---

## 7. Bài viết Liên quan

- [CLI-PART-001: Hướng dẫn đăng ký trở thành Đối tác Studio Cưới Mây Hẹn](file:///d:/Project/Laravel/mayhen.vn/resources/docs/client/10-partner-privacy/CLI-PART-001.md)
- [CLI-PART-003: Theo dõi ví hoa hồng, lịch sử thanh toán và tạo lệnh rút tiền](file:///d:/Project/Laravel/mayhen.vn/resources/docs/client/10-partner-privacy/CLI-PART-003.md)
- [CLI-INVT-001: Hướng dẫn tạo thiệp cưới mới từ kho mẫu](file:///d:/Project/Laravel/mayhen.vn/resources/docs/client/01-getting-started/CLI-START-001.md)
