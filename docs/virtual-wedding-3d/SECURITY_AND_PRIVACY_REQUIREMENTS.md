# MÂY HẸN — VIRTUAL WEDDING WORLD 3D
## YÊU CẦU BẢO MẬT, PHÂN QUYỀN & BẢO VỆ DỮ LIỆU CÁ NHÂN (SECURITY & PRIVACY)

---

## 1. MA TRẬN PHÂN QUYỀN & CHỐNG TRUY CẬP TRÁI PHÉP (ZERO IDOR)

| Tác nhân | Quyền hạn được phép | Cơ chế kiểm soát tại Backend |
| :--- | :--- | :--- |
| **Chủ tiệc (`Owner`)** | Tạo/sửa cấu hình đám cưới 3D của mình, phát/dừng livestream, ghim/xóa tin nhắn, khóa cổng khẩn cấp, xem báo cáo vé. | Kiểm tra `WeddingPolicy` (`wedding.user_id === auth()->id()`); chặn tuyệt đối IDOR sang đám cưới của cặp đôi khác (`403 Forbidden`). |
| **Khách tham dự (`Attendee`)** | Vào cổng khi có `VirtualAccessPass` trạng thái `ACTIVE` và phiên tiệc chưa khóa (`<= +5h`), chọn avatar chuẩn hóa, di chuyển trong vùng cho phép, xem lễ, gửi lời chúc. | Token phiên ký HMAC-SHA256 gắn chặt với `(wedding_id, session_id, pass_id)`; không thể dùng vé của đám cưới A để mở scene/chat của đám cưới B. |
| **Khách chưa có vé (`Unverified`)** | Chỉ xem được trang giới thiệu sự kiện nhẹ và hướng dẫn mua vé 5.000đ. | Chặn truy cập API lấy token phòng 3D, URL luồng livestream và kênh WebSocket khi chưa có `VirtualAccessPass` hợp lệ. |
| **Quản trị viên (`Admin`)** | Quản lý danh mục mẫu Venue 3D (`VirtualVenueTemplate`), phát hành phiên bản `.glb` bất biến, đối soát hoàn tiền. | Kiểm tra RBAC `role === 'admin'`; mọi thao tác khóa sự kiện hoặc hoàn tiền đều bắt buộc ghi `AuditLogger`. |

---

## 2. 10 CHỐT CHẶN BẢO MẬT KỸ THUẬT BẮT BUỘC

1. **Chống giả mạo Webhook Thanh Toán**: Xác minh chữ ký HMAC-SHA256 từ SePay, kiểm tra khớp số tiền `>= 5.000đ`, mã tiền tố `MVP...` và khóa `UNIQUE(gateway_transaction_id)` trong giao dịch cơ sở dữ liệu.
2. **Chống vượt rào Thời Gian (`Clock Tampering Defense`)**: Mọi kiểm tra `T - 5h` khi tạo lễ và `+5h` khi khóa cổng đều dùng `CarbonImmutable::now('UTC')` trên máy chủ.
3. **Chống giả mạo Tọa độ & Xuyên Tường (`Server-Side Bounds Check`)**: Cổng Realtime kiểm tra tọa độ `(x, z)` nằm trong vùng đi bộ hợp lệ (`X ∈ [-8.2, +8.2], Y_scene ∈ [-18.5, +10.5]`) và giới hạn vận tốc tối đa `<= 6.0 m/s`.
4. **Chống XSS & Giả Mạo Danh Tính trong Chat/Avatar**:
   - Tên hiển thị (`display_name`) giới hạn `2–32 ký tự`, lọc ký tự điều khiển, escape HTML 100% và chặn các từ khóa mạo danh (`Admin`, `Quản trị viên`, `Hệ thống`, `Mây Hẹn Official`).
5. **Chống Upload Avatar Độc Hại**: Khách tham dự chỉ chọn từ bộ **Avatar & Trang phục chuẩn hóa** của Mây Hẹn (Nam, Nữ, Trung tính; Vest, Áo dài, Đầm dự tiệc), **không cho phép tải file 3D hay ảnh mặt người thật tự do** khi chưa có quy trình xác thực đồng ý.
6. **Bảo vệ Phiên bản Asset Bất Biến (`Immutable Versioned GLB`)**: Mỗi bản xuất `.glb` được lưu theo thư mục phiên bản (`v1`, `v2`). Khi Admin cập nhật bản mới `v2`, các đám cưới đang diễn ra trên `v1` tiếp tục chạy ổn định trên `v1`, tuyệt đối không bị ép tải lại giữa buổi lễ.

---

## 3. TUÂN THỦ LUẬT BẢO VỆ DỮ LIỆU CÁ NHÂN (LUẬT 91/2025/QH15)

- **Không thu thập sinh trắc học**: Hệ thống không thu thập dữ liệu khuôn mặt 3D hay giọng nói của khách mời.
- **Quyền kiểm soát của Chủ tiệc**: Camera và microphone khi phát livestream chỉ mở khi có thao tác bấm nút rõ ràng và hiển thị đèn báo `🔴 ĐANG PHÁT TRỰC TIẾP` nổi bật; dừng phát ngay lập tức khi chủ tiệc bấm tắt hoặc đóng trang.
- **Dọn dẹp dữ liệu sau khi Thế giới 3D kết thúc (`ENDED`)**:
  - Toàn bộ tọa độ hiện diện tạm thời (`presence state`) được xóa ngay lập tức.
  - Lời chúc đám cưới được đồng bộ về Sổ Lưu Bút (`wedding_wishes`) của cặp đôi theo đúng cài đặt quyền riêng tư (`wishes_visibility`).
