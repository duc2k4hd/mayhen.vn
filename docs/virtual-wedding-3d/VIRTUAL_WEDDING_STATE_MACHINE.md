# MÂY HẸN — VIRTUAL WEDDING WORLD 3D
## MÁY TRẠNG THÁI & VÒNG ĐỜI 48 GIỜ (VIRTUAL WEDDING STATE MACHINE)

---

## 1. QUY TẮC TẠO SỰ KIỆN & ĐỒNG HỒ SERVER (SERVER-AUTHORITATIVE TIME)

1. **Điều kiện tạo trước tối thiểu 5 giờ (`T - 5h`)**:
   - Thời điểm bắt đầu chính thức của sự kiện (`starts_at_utc`) phải lớn hơn hoặc bằng `now_utc() + 5 hours`.
   - Mọi yêu cầu tạo hoặc dời lịch có `starts_at_utc < now_utc() + 5 hours` hoặc nằm trong quá khứ đều bị từ chối với mã lỗi `422 Unprocessable Entity`.
2. **Không bao giờ tin đồng hồ trình duyệt (`Date.now()`)**:
   - Server trả về `server_now_utc` và `event_timezone` (mặc định `Asia/Ho_Chi_Minh`, `UTC+7`) trong mọi bản tin cấu hình.
   - Client tính độ lệch `clockOffsetMs = serverNowMs - performance.timeOrigin - performance.now()` chỉ để hiển thị đồng hồ đếm ngược, còn quyền mở cổng/khóa cổng do Backend quyết định 100%.

---

## 2. TÁCH BIỆT VÒNG ĐỜI THẾ GIỚI (48 GIỜ) VÀ CỬA VÀO TIỆC (5 GIỜ / PHIÊN)

| Khái niệm | Thời lượng | Quy tắc vận hành |
| :--- | :--- | :--- |
| **Vòng đời Thế Giới (`World Lifecycle`)** | Tối đa **48 giờ** (`world_opens_at_utc` → `world_ends_at_utc`) | Cho phép đám cưới tổ chức trải dài 2 ngày (ví dụ: Ngày 1 Lễ Vu Quy / Đón khách sớm từ `05:00`, Ngày 2 Lễ Tân Hôn & Đại Tiệc). |
| **Cửa Vào Phiên Tiệc (`Session Entry Gate`)** | Mở trước giờ lễ và **tự động khóa sau đúng 5 giờ** tính từ `session_starts_at_utc` | Mỗi phiên tiệc (`VirtualWeddingSession`) có `entry_opens_at_utc` và `entry_locks_at_utc = session_starts_at_utc + 5 hours` độc lập. Khóa cổng phiên Ngày 1 **tuyệt đối không** khóa nhầm phiên Ngày 2. |

---

## 3. SƠ ĐỒ MÁY TRẠNG THÁI 11 BƯỚC (11-STATE MACHINE)

```text
DRAFT ──► CONFIGURED ──► PUBLISHED ──► WAITING ──► PREPARATION (05:00)
                                                        │
ENDED ◄── CLOSED ◄── ENTRY_LOCKED (+5h) ◄── BANQUET ◄── CEREMONY ◄── GUEST_ARRIVAL
```

### Quy tắc chuyển trạng thái tự động (Lazy Evaluation + Scheduled Command):
- Trạng thái được tính toán **kép**: vừa có tác vụ định kỳ mỗi phút, vừa được hàm `VirtualWeddingStateResolver::resolve($virtualWedding, CarbonImmutable $nowUtc)` xác minh tức thời ngay trong mỗi HTTP Request / WebSocket Handshake (đảm bảo chuyển trạng thái chính xác ngay cả khi không có cron hoặc không có người mở web trước đó).

---

## 4. TIMELINE HOẠT CẢNH ĐỒNG BỘ THEO GIỜ SỰ KIỆN (KHÔNG RESET KHI F5)

| Khung giờ mẫu (Cấu hình linh hoạt theo từng đám cưới) | Trạng thái ánh sáng | Hoạt động của NPC & Không gian 3D |
| :--- | :--- | :--- |
| **05:00 – 07:00 (`PREPARATION`)** | `DAWN_05H` | Ánh sáng bình minh dịu; nhân viên kiểm tra bàn ghế, trải khăn bàn, chỉnh hoa sân khấu. |
| **07:00 – 10:30 (`PREPARATION`)** | `MORNING_08H` | Ánh nắng buổi sáng; hoàn thiện cổng hoa, bàn gallery, kiểm tra âm thanh & màn hình LED. |
| **10:30 – 12:00 (`GUEST_ARRIVAL`)** | `NOON_12H` / `NIGHT_20H` | Lễ tân áo dài đón khách tại bàn gallery, usher mời khách qua cổng hoa vào bàn tiệc. |
| **12:00 – 13:00 (`CEREMONY`)** | `NIGHT_20H` (Gala Mode) | Đèn chùm và đèn beam sân khấu bật sáng; Cô dâu – Chú rể trên sân khấu, ban nhạc biểu diễn. |
| **13:00 – 17:00 (`BANQUET`)** | `NIGHT_20H` / `SUNSET_17H` | Phục vụ tiệc tại 16 bàn, khách tham quan chụp ảnh photobooth `NT`, gửi lời chúc & xem livestream. |
| **Sau `session_starts_at + 5h` (`ENTRY_LOCKED`)** | `SUNSET_17H` / `NIGHT_20H` | Cổng bán vé và nhận khách mới tự động khóa; khách đang ở trong rạp hoàn tất buổi lễ cho tới khi `CLOSED`. |

- **Bảo toàn tiến độ khi F5 hoặc vào muộn**: Vị trí và trạng thái hoạt cảnh được tính là hàm thuần toán học `f(now_utc - session_starts_at_utc)`. Khách vào lúc `12:30` hoặc bấm `F5` sẽ thấy ngay nghi lễ đang diễn ra ở phút thứ 30, tuyệt đối không chạy lại hoạt cảnh từ `05:00`.
