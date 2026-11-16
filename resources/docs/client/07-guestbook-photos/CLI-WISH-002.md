---
id: CLI-WISH-002
title: "Cách cặp đôi duyệt lời chúc và gửi lời cảm ơn phản hồi lại từng người"
category: "Sổ ký tên, Lời chúc & Photo Drop"
summary: "Hướng dẫn cặp đôi quản lý sổ lưu bút: Kiểm duyệt lời chúc công khai, ghim lời chúc ý nghĩa lên đầu, phản hồi lời cảm ơn riêng tư hoặc công khai tới từng khách mời và cơ chế bảo mật chống quấy rối (NoProfanity Firewall)."
reader: "PER-CLI-03 (Khách mời & Cặp đôi)"
order: 6
updated_at: "2026-11-15"
tags: [mayhen, sổ-ký-tên, duyệt-lời-chúc, phản-hồi-cảm-ơn, moderation, pin-wish]
mapped_page: "CLI-WISH-PAGE"
mapped_route: "invitation.wish.index"
risk_level: "MEDIUM"
required_role: "customer"
---

# Cách cặp đôi duyệt lời chúc và gửi lời cảm ơn phản hồi lại từng người

> **Dành cho:** Cô dâu & Chú rể quản lý lưu bút cưới và gửi lời tri ân tới quan khách  
> **Thời gian thao tác:** Khoảng 2 - 5 phút mỗi ngày  
> **Vị trí trong ứng dụng:** Trang Quản lý Thiệp -> Chọn thiệp -> **Sổ Ký Tên & Lời Chúc** (Route: `account.interactive_ending.index`) hoặc phản hồi trực tiếp tại link thiệp `/thiep/{slug}#so-ky-ten`  
> **API Xử lý:** `invitation.wish.reply` (`POST /thiep/{slug}/wishes/{wishId}/reply`)

Nhận được hàng chục, thậm chí hàng trăm lời chúc phúc từ người thân, bạn bè và đồng nghiệp là một trong những trải nghiệm ấm áp nhất sau ngày cưới. Nhưng niềm vui sẽ trọn vẹn hơn gấp bội khi chính cô dâu chú rể để lại những dòng **phản hồi cảm ơn (Couple Reply)** chân thành dưới từng lời chúc ấy.

Hệ thống quản lý Sổ Ký Tên của Mây Hẹn cung cấp bảng điều khiển trực quan giúp bạn dễ dàng đọc duyệt, ghim những lời chúc cảm động nhất lên đầu trang và gửi lời tri ân tới từng vị khách quý.

![Bảng duyệt lời chúc và gửi lời cảm ơn phản hồi](/assets/client/docs/images/client/07-guestbook-photos/cli-wish-002-step-1.png)
*Bảng quản lý lời chúc: Duyệt nội dung, ghim lời chúc ý nghĩa và phản hồi tri ân khách mời.*

---

## 1. Trải nghiệm Phản Hồi của Cặp Đôi (Interactive Reply Card)

Khi cô dâu chú rể đăng nhập và mở thiệp cưới của mình, dưới mỗi thẻ lời chúc của khách sẽ xuất hiện huy hiệu đặc quyền dành cho chủ tiệc:

```
┌────────────────────────────────────────────────────────────────────────┐
│  👤 Bác Nguyễn Văn Tuấn (Họ Nhà Gái)                    🕒 2 giờ trước │
│  💬 "Chúc hai cháu Hoàng & Dung một đời yêu thương, thuận hòa, sớm     │
│     có quý tử bồng bế cho ông bà nội ngoại vui lòng nhé!"              │
│  🖋️ [Chữ ký bút vàng: Tuấn Nguyen]                                     │
├────────────────────────────────────────────────────────────────────────┤
│  💍 CÔ DÂU & CHÚ RỂ ĐÃ PHẢN HỒI:                        🕒 Vừa xong   │
│  ❤️ "Cháu cảm ơn bác Tuấn rất nhiều ạ! Cháu chúc bác và gia đình      │
│     luôn dồi dào sức khỏe và bình an. Hẹn sớm gặp bác ở Hà Nội ạ!"    │
└────────────────────────────────────────────────────────────────────────┘
```

1. **Nhận diện chính chủ (Couple Verified Badge):**
   - Phản hồi của bạn sẽ mang huy hiệu nhẫn cưới màu vàng kim (`💍 Cô dâu & Chú rể`), phân biệt rõ ràng với bình luận của các khách mời khác.
2. **Thông báo đẩy thời gian thực:**
   - Nếu khách mời mở lại đường link thiệp cưới hoặc trang tra cứu chỗ ngồi, họ sẽ thấy ngay dòng cảm ơn ấm áp của hai bạn.

---

## 2. Hướng dẫn 3 bước gửi lời cảm ơn tới khách mời

### Bước 1: Mở danh sách lời chúc
- Bạn có thể thao tác ngay trên giao diện thiệp cưới công cộng (khi đã đăng nhập tài khoản tạo thiệp) hoặc vào mục **Quản Lý Thiệp** -> **Sổ Ký Tên**.
- Hệ thống hỗ trợ lọc:
  - `Tất cả lời chúc`
  - `Chưa phản hồi` (giúp bạn không bỏ sót bất kỳ ai)
  - `Lời chúc riêng tư` (chỉ cô dâu chú rể xem được)

### Bước 2: Viết nội dung phản hồi
- Nhấp vào nút **[💌 Trả lời / Gửi lời cảm ơn]** dưới lời chúc của khách.
- Khung soạn thảo hiện ra với độ dài tối đa `1000 ký tự`.
- Bạn có thể gõ nội dung tùy ý hoặc chọn mẫu trả lời nhanh:
  - *Mẫu trang trọng (Dành cho người lớn tuổi):* "Cháu cảm ơn lời chúc phúc quý báu của bác ạ. Cháu chúc bác luôn dồi dào sức khỏe!"
  - *Mẫu thân mật (Dành cho bạn bè):* "Cảm ơn mày nhiều nha! Hôm nào về hội bạn cấp 3 mình làm bữa liên hoan quẩy tiếp nhé!"

### Bước 3: Nhấp nút [Gửi phản hồi]
- Hệ thống gọi API `invitation.wish.reply`, cập nhật thời gian phản hồi (`replied_at`) và hiển thị ngay ngắn dưới lời chúc của khách.

---

## 3. Các công cụ kiểm duyệt & Sắp xếp nâng cao (Moderation Tools)

Để sổ lưu bút luôn trang nhã và lịch sự, Mây Hẹn trang bị các nút điều hướng nhanh cho từng lời chúc:

1. **Ghim lời chúc lên đầu trang (Pin to Top):**
   - Nhấp vào biểu tượng chiếc ghim **[📌 Ghim]**. Lời chúc xúc động nhất (ví dụ thư dặn dò của Bố Mẹ hay lời chúc của Bạn thân) sẽ luôn nổi bật ở vị trí đầu tiên của sổ ký tên.
2. **Ẩn / Hiện lời chúc (Toggle Approval):**
   - Nếu có lời chúc trêu đùa nhạy cảm hoặc bạn chỉ muốn giữ làm kỷ niệm riêng giữa hai người, nhấp **[Ẩn khỏi màn hình công cộng]**. Lời chúc sẽ chỉ hiển thị trong tài khoản quản trị của bạn mà không xuất hiện trên thiệp online hay màn LED sân khấu.
3. **Chế độ Lời chúc riêng tư (Private Wishes):**
   - Khách có thể tích chọn ô *"Chỉ gửi riêng cho cô dâu chú rể"* khi ký tên. Những lời tâm sự kín đáo này sẽ có biểu tượng ổ khóa bảo mật và được bảo vệ nghiêm ngặt.

---

## 4. Xử lý khiếu nại & Báo cáo nội dung xấu (Report Firewall)

> [!NOTE]
> **Bộ lọc tự động (NoProfanity Guard):**  
> 100% nội dung lời chúc và phản hồi đều được quét tự động qua từ điển an toàn. Bất kỳ từ ngữ nào mang tính chất spam quảng cáo, thô tục hay phản cảm đều bị chặn từ cửa vào.

> [!TIP]
> **Xuất file lưu niệm sau đám cưới:**  
> Đừng lo lắng về việc mất dữ liệu! Sau khi đám cưới kết thúc, bạn có thể xuất toàn bộ lời chúc kèm chữ ký và phản hồi ra file PDF hoặc Excel để in đóng cuốn thành kỷ yếu hôn lễ (xem hướng dẫn chi tiết tại bài `CLI-WISH-005`).
