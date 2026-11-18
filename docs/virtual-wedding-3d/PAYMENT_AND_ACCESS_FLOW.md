# MÂY HẸN — VIRTUAL WEDDING WORLD 3D
## LUỒNG THANH TOÁN 5.000 ĐỒNG & CẤP QUYỀN VÀO CỔNG (PAYMENT & ACCESS FLOW)

---

## 1. PHÂN TÁCH TUYỆT ĐỐI 4 KHOẢN TIỀN (ZERO ACCOUNTING COLLISION)

Để bảo vệ tuyệt đối bảng `orders` và `EntitlementStateMachineService` hiện có của Mây Hẹn, hệ thống tách bạch 4 luồng tài chính độc lập:

| Khoản tiền | Người trả → Người nhận | Bảng dữ liệu / Dịch vụ | Tiền tố mã giao dịch |
| :--- | :--- | :--- | :--- |
| **1. Gói dịch vụ thiệp / thuê Venue 3D** | Chủ tiệc → Mây Hẹn | `orders` (`plan_id`) | `MH{order_id}...` |
| **2. Vé vào cổng đám cưới 3D (5.000đ)** | Khách tham dự (hoặc Chủ tiệc tài trợ) → Mây Hẹn | `virtual_entry_payments` & `virtual_access_passes` | `MVP{pass_code}` |
| **3. Tiền mừng cưới (`Wedding Gifts`)** | Khách mời → Trực tiếp tài khoản Cô Dâu / Chú Rể | `wedding_gifts` (`VietQrService` P2P) | Chuyển thẳng P2P (Mây Hẹn không giữ hộ) |
| **4. Phí hạ tầng Livestream / Mở rộng** | Chủ tiệc → Mây Hẹn | `virtual_service_addons` | `MVS{addon_id}...` |

---

## 2. LUỒNG XỬ LÝ VÉ VÀO CỔNG 5.000 ĐỒNG AN TOÀN

```text
Khách bấm "Vào Cổng Đám Cưới 3D"
  │
  ├─► [1] Kiểm tra Server Time & Trạng thái Phiên tiệc (Cổng đang mở, chưa quá +5h)
  ├─► [2] Kiểm tra Cookie ký số HMAC / guest_token / Pass Token hiện có
  │       └── Nếu đã có VirtualAccessPass ACTIVE (hoặc sự kiện do Chủ tiệc tài trợ 100%) ──► Cấp Session Ticket vào 3D ngay
  │
  └─► [3] Chưa có vé & Sự kiện bật thu phí 5.000đ:
          ├── Tạo bản ghi `virtual_entry_payments` (status = PENDING, amount = 5000, code = MVP...)
          ├── Hiển thị mã VietQR 5.000đ chính xác số tiền & nội dung chuyển khoản `MVP...`
          ├── Ngân hàng / SePay gửi Webhook có chữ ký HMAC-SHA256
          ├── Backend kiểm tra Idempotency (`gateway_transaction_id` duy nhất) & khóa `SELECT ... FOR UPDATE`
          └── Kích hoạt `VirtualAccessPass` (status = ACTIVE) ──► Mở khóa cổng 3D tức thời
```

---

## 3. XỬ LÝ 12 TRƯỜNG HỢP BIÊN VỀ THANH TOÁN (EDGE CASES)

1. **Khách bấm "Tôi đã thanh toán" / sửa `localStorage` / sửa JS response**: Bị chặn 100% vì API tải cấu hình scene và cấp WebSocket token kiểm tra trực tiếp bản ghi `virtual_access_passes.status = 'ACTIVE'` trên Server.
2. **Webhook SePay gửi lặp 2 lần**: Khóa duy nhất `UNIQUE(gateway_transaction_id)` và kiểm tra trạng thái trong DB transaction đảm bảo chỉ cấp 1 vé, trả `200 OK` an toàn cho lần gọi lặp.
3. **Chuyển khoản thiếu tiền (`< 5.000đ`)**: Ghi trạng thái `UNDERPAID`, lưu số tiền thực nhận và thông báo khách bổ sung hoặc liên hệ hỗ trợ, không tự mở cổng.
4. **Chuyển khoản dư tiền (`> 5.000đ`)**: Vẫn kích hoạt vé `ACTIVE` để không chặn khách dự đám cưới, đồng thời gắn cờ `OVERPAID_FLAG` trong bảng đối soát cho Admin.
5. **Thanh toán khi cổng sắp khóa hoặc tiền về muộn sau khi cổng đã khóa (`> +5h`)**:
   - Nếu `paid_at_utc <= entry_locks_at_utc + 5 minutes (grace window)`: Cho phép vào cổng.
   - Nếu tiền về sau thời gian ân hạn khi phiên đã khóa: Chuyển sang trạng thái `LATE_PAYMENT_REFUND_REQUIRED` (nếu còn phiên Ngày 2 thì cho phép áp dụng sang phiên Ngày 2 theo cấu hình vé).
6. **Khách F5 hoặc đổi điện thoại**: Mỗi `VirtualAccessPass` có mã khôi phục ngắn (`pass_recovery_code`) gắn với số điện thoại/token khách mời; đăng nhập sang thiết bị mới sẽ thu hồi kết nối WebSocket cũ (chống nhân bản 2 avatar cùng lúc).
7. **Chủ tiệc hủy sự kiện hoặc lỗi WebGL trên thiết bị khách**: Vé vẫn cho phép xem luồng Livestream 2D dự phòng; nếu sự kiện bị hủy trước giờ mở cổng, toàn bộ giao dịch `ACTIVE` được chuyển sang hàng đợi `PENDING_REFUND` (chỉ đánh dấu `REFUNDED` sau khi đối soát hoàn tiền thật).
