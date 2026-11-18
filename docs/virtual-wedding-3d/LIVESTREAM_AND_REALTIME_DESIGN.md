# MÂY HẸN — VIRTUAL WEDDING WORLD 3D
## THIẾT KẾ LIVESTREAM SÂN KHẤU 3D & HỆ THỐNG REALTIME (LIVESTREAM & REALTIME DESIGN)

---

## 1. KIẾN TRÚC PHÁT LIVESTREAM LÊN MÀN HÌNH LED 3D (`16:9`)

### 1.1. Hai bề mặt màn hình LED chuẩn trong mô hình Blender (`royal-crimson-marquee/v1`)
- **`LED_Screen_Left_16x9_Surface`** (`3.20m × 1.80m`, tọa độ `X = -6.35m, Y = 10.35m, Z = 2.45m`, `mayhen_screen_id = "LED_STAGE_LEFT"`, UV phẳng chuẩn `0..1`).
- **`LED_Screen_Right_16x9_Surface`** (`3.20m × 1.80m`, tọa độ `X = +6.35m, Y = 10.35m, Z = 2.45m`, `mayhen_screen_id = "LED_STAGE_RIGHT"`, UV phẳng chuẩn `0..1`).

### 1.2. Trạng thái Chờ Trung Thực (`IDLE`) vs Trạng thái Phát Thật (`LIVE`)
- **Khi chưa phát (`IDLE`)**: Màn hình hiển thị texture nội bộ `tex_led_idle_slate.png` với dòng chữ rõ nét: **"MÂY HẸN VIRTUAL STAGE — CHƯƠNG TRÌNH TRỰC TIẾP CHƯA BẮT ĐẦU — SẴN SÀNG KẾT NỐI TÍN HIỆU TỪ CHỦ TIỆC"**. Tuyệt đối không dùng video dựng sẵn gắn nhãn `LIVE` giả.
- **Khi chủ tiệc bấm phát (`LIVE`)**:
  1. Chủ tiệc mở trang quản trị đám cưới 3D, chủ động bấm **"Mở Camera Xem Trước"** (trình duyệt chỉ xin quyền `navigator.mediaDevices.getUserMedia` khi chủ tiệc bấm nút, tuyệt đối không tự mở camera/mic khi mới vào trang).
  2. Chủ tiệc chọn Camera trước/sau, bật/tắt Mic, chọn nghi lễ đang phát (`Đón dâu`, `Lễ thành hôn`, `Khai tiệc`) và bấm **"Bắt đầu Phát Trực Tiếp"**.
  3. Server xác thực quyền chủ tiệc, chuyển trạng thái phiên sang `LIVE` và phát sự kiện tới các phòng trong rạp.
  4. Trình duyệt khách nhận luồng video `<video playsinline>` và cập nhật trực tiếp lên `WebGLTexture` của 2 màn hình LED 3D ở tốc độ 30fps, kèm nút **"Xem Toàn Màn Hình"** (đặc biệt hữu ích cho khách lớn tuổi dùng điện thoại).

---

## 2. ĐỘ TRỄ & HAI CHẾ ĐỘ TRUYỀN TẢI MEDIA (WEBRTC vs LL-HLS)

| Chế độ | Giao thức | Quy mô phù hợp | Độ trễ thực tế (Median / P95) | Cơ chế thích ứng mạng yếu |
| :--- | :--- | :--- | :--- | :--- |
| **Chế độ Tương tác Gần Thời Gian Thực (`Realtime`)** | WebRTC (WHIP Ingest → SFU / WHEP Egress) | `< 300` người xem đồng thời | **0.6s – 1.8s** (P95 `< 2.8s`) | Tự động hạ bitrate từ `720p@30fps (1.8 Mbps)` xuống `480p (750 kbps)` hoặc `360p (380 kbps)` khi mất gói `> 3%`. |
| **Chế độ Phát Quảng Bá Lớn (`Broadcast CDN`)** | Low-Latency HLS (`LL-HLS` / DASH qua CDN) | `300 – 100.000+` người xem | **2.5s – 5.5s** (P95 `< 8.0s`) | Adaptive Bitrate (ABR) tự chuyển bậc chất lượng, giữ nguyên âm thanh thông suốt khi hình ảnh giảm độ nét. |

*Cam kết kỹ thuật trung thực*: Không tuyên bố độ trễ 0,1 giây phi thực tế trên mạng di động công cộng; mọi phiên phát đều đo và ghi nhận `first_frame_ms`, `median_latency_ms`, `p95_latency_ms` và `rebuffer_ratio`.

---

## 3. ĐỒNG BỘ NHIỀU NGƯỜI, PHÂN VÙNG KHÔNG GIAN & CHAT REALTIME

1. **Phân vùng phòng (`Room Sharding`) & Giới hạn Avatar hiển thị (`Max 35 Avatars`)**:
   - Mỗi đám cưới được chia thành các shard tối đa **80 kết nối/phòng** (`wedding_{id}_session_{sid}_shard_{n}`).
   - Trên màn hình 3D của mỗi khách, chỉ hiển thị tối đa **35 avatar người thật gần nhất** theo bán kính ưu tiên (`Area of Interest <= 14m`) cộng với các NPC cố định của rạp — đảm bảo GPU điện thoại luôn duy trì `>= 45–60 FPS` dù sự kiện có hàng nghìn khách tham dự.
2. **Chống nhân bản Avatar & Dọn trạng thái**:
   - Mỗi `access_pass_id` chỉ được giữ **1 phiên hiện diện 3D active**; mở tab thứ hai hoặc đổi thiết bị sẽ ngắt kết nối hiện diện của tab cũ ngay lập tức.
   - Heartbeat mỗi `15 giây`; quá `35 giây` mất tín hiệu sẽ tự thu hồi slot nhân vật trên sàn.
3. **Chat & Gửi Lời Chúc theo Đám Cưới**:
   - Phân lập hoàn toàn theo `wedding_id`; xác thực chữ ký kênh WebSocket (không tin `wedding_id` hay `guest_id` do client tự truyền).
   - Tích hợp bộ lọc `NoProfanity`, chống XSS (`textContent` / HTML escape), giới hạn tốc độ `5 tin nhắn / 10 giây`, cấm mạo danh tên `[Chủ Tiệc]` / `[Admin]`, và hỗ trợ Chủ tiệc ghim thông báo hoặc khóa chat tức thời.
