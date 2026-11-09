# MÂY HẸN (MAYHEN.VN) — SPEC CONFLICTS & VERIFICATION REGISTER

Tài liệu này ghi nhận toàn bộ các mâu thuẫn giữa Đặc tả (Specs/Docs) và Mã nguồn thực tế (Codebase), kèm theo quyết định xử lý an toàn nhất.

---

### SPEC_CONFLICT-01: Quy Tắc So Khớp Số Tiền Webhook Thanh Toán (`>=` vs `===`)
- **Vị trí tài liệu**: `docs/MAY_HEN_DOMAIN_INVARIANTS_AND_STATE_MACHINES.md:L71` (`INV-01`)
  * *Tài liệu mô tả*: Webhook chấp nhận khi số tiền `>= order.amount`.
- **Vị trí mã nguồn**: `app/Http/Controllers/Client/CheckoutController.php:L225`
  * *Code thực tế*: `if ($amount !== $order->amount) { ... return 400; }`
- **Phân tích rủi ro kế toán**:
  * Nếu dùng `===`: Khách chuyển thừa dù chỉ 1 đồng hoặc làm tròn số tiền thì hệ thống từ chối kích hoạt, dẫn đến khiếu nại khách hàng.
  * Nếu dùng `>=`: Khách chuyển thừa vẫn được kích hoạt gói, số tiền thừa được ghi nhận trong audit log để kế toán xử lý.
- **Khuyến nghị & Xử lý**:
  * Giữ nguyên `===` theo code hiện hành nếu yêu cầu hạch toán khớp 100% hóa đơn.
  * Bổ sung log cảnh báo rõ ràng khi số tiền chuyển thừa và cho phép Admin phê duyệt thủ công (`OrderController@approve`).

