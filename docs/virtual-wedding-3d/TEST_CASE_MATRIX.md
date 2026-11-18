# MÂY HẸN — VIRTUAL WEDDING WORLD 3D
## MA TRẬN KIỂM THỬ TOÀN DIỆN CÁC TÌNH HUỐNG NHỎ NHẤT (TEST CASE MATRIX)

---

## 1. KIỂM THỬ GIAI ĐOẠN BLENDER & WEBGL2 (`W00`–`W03` — ĐÃ THỰC THI & NGHIỆM THU)

| ID | Nhóm | Bước tái hiện | Kết quả mong đợi | Kết quả thực tế | Bằng chứng |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-3D-001** | Video Source | Kiểm tra video gốc `FDown.vn_Tai_video_Facebook_720p (HD)_7b85.mp4` bằng `ffprobe` & trích xuất 18 frame | Đọc đúng 1280×720, 19.20s, trích xuất đủ `f01`..`f18` | **PASS** (18/18 frames, 7 góc quay) | `VIDEO_REFERENCE_BREAKDOWN.md` |
| **TC-3D-002** | Metric Scale | Kiểm tra tỷ lệ `1 unit = 1.0m`, lối đi `2.20m`, 16 bàn Ø1.5m, 160 ghế Tiffany | Không có bàn ghế chồng lấn hay lơ lửng; lối đi thông thoáng `> 1.0m` | **PASS** (Zero collision, `graybox_04_top_orthographic_blueprint.png`) | `BLENDER_SCENE_SPECIFICATION.md` |
| **TC-3D-003** | Planar UV & Signage | Kiểm tra UV trên 2 bảng chào terracotta, tranh cưới và 2 màn hình LED 16:9 | Chữ hiển thị thẳng, sắc nét, không bị kéo sọc hay ngược chữ | **PASS** (`w03_02_foyer_entrance.png`, `w03_03_aisle_to_stage.png`) | `renders/w03-pbr/w03_02*.png` |
| **TC-3D-004** | Time-of-Day Lighting | Render 5 khung giờ `05:00`, `08:00`, `12:00`, `17:00`, `20:00` | Mỗi khung giờ có màu trời, nắng và độ sáng đèn chùm phân biệt rõ | **PASS** (5 ảnh `w03_lighting_01`..`05`) | `renders/w03-pbr/w03_lighting_*.png` |
| **TC-3D-005** | Animated NPCs | Kiểm tra 15 NPC (Cô dâu, Chú rể, Lễ tân, Usher, Ban nhạc, Khách) | Cử động tự nhiên theo chu kỳ 72 frames lệch pha, không chắn giữa cổng | **PASS** (72-frame keyframed actions exported into `.glb`) | `build_w03_venue.py` |
| **TC-3D-006** | WebGL2 Desktop & Mobile | Mở `walkthrough-demo.html` trên Chromium `1440×900` và `390×844` | Nạp `.glb` `< 1.5s`, đạt `>= 60 FPS`, `0` lỗi JS, bật thử Livestream LED hoạt động | **PASS** (`117ms` load, **100 FPS**, `892` draw calls, `0` console error) | `w03_browser_01`..`04.png` |

---

## 2. MA TRẬN TEST CASES CHO CÁC GIAI ĐOẠN TIẾP THEO (`W04`–`W11`)

### 2.1. Nhóm Thời Gian & Vòng Đời (`TC-TIME-*`)
- **TC-TIME-01**: Tạo đám cưới 3D đúng ngưỡng `starts_at = now_utc() + 5 hours` → **PASS (`201 Created`)**.
- **TC-TIME-02**: Tạo đám cưới 3D muộn (`now_utc() + 4h59m` hoặc thời điểm quá khứ) → **Chặn `422 Unprocessable Entity`**.
- **TC-TIME-03**: Đám cưới 2 ngày (48h world lifecycle): Phiên Ngày 1 khóa cổng sau `+5h`, kiểm tra phiên Ngày 2 sáng hôm sau vẫn mở cổng bình thường.
- **TC-TIME-04**: Máy khách cố tình chỉnh đồng hồ Windows/điện thoại lệch `-6 giờ` để vào cổng đã khóa → Server từ chối dựa trên `now_utc()`.
- **TC-TIME-05**: Khách vào lúc `12:30` hoặc bấm `F5` → Hoạt cảnh tiếp tục ở mốc `12:30`, không tua lại từ `05:00`.

### 2.2. Nhóm Thanh Toán 5.000đ & Quyền Vào Cổng (`TC-PAY-*`)
- **TC-PAY-01**: Chuyển khoản đúng `5.000đ` nội dung `MVP...` → SePay webhook HMAC hợp lệ → Cấp `VirtualAccessPass` `ACTIVE`.
- **TC-PAY-02**: Khách bấm nút giả hoặc sửa `localStorage` khi chưa có webhook → API chặn `403 Forbidden`.
- **TC-PAY-03**: Webhook SePay gửi lặp 2 lần cùng `gateway_transaction_id` → Idempotent, chỉ tạo 1 vé.
- **TC-PAY-04**: Chuyển thiếu (`3.000đ`) → Giữ trạng thái `UNDERPAID`, không mở cổng.
- **TC-PAY-05**: Tiền về muộn sau khi cổng đã khóa quá thời gian ân hạn → Ghi nhận `LATE_PAYMENT_REFUND_REQUIRED`.
- **TC-PAY-06**: Thanh toán vé 5.000đ tuyệt đối không làm thay đổi `orders` hay gói cước thiệp cưới của cặp đôi.

### 2.3. Nhóm Livestream, Chat & Bảo Mật (`TC-LIVE-*`, `TC-CHAT-*`, `TC-SEC-*`)
- **TC-LIVE-01**: Chủ tiệc mới mở trang quản lý → Trình duyệt **không** tự bật camera/mic cho tới khi bấm nút.
- **TC-LIVE-02**: Khi chưa có luồng phát → Màn hình LED 3D hiển thị đúng `"CHƯƠNG TRÌNH TRỰC TIẾP CHƯA BẮT ĐẦU"`.
- **TC-LIVE-03**: Chủ tiệc phát livestream đón dâu bằng điện thoại → 2 màn hình LED 3D trong rạp hiển thị video thật + nút toàn màn hình hoạt động.
- **TC-CHAT-01**: Gửi tin nhắn chứa `<script>alert(1)</script>` hoặc từ cấm → Bị lọc/escape an toàn 100%.
- **TC-CHAT-02**: Khách ở đám cưới A cố kết nối vào kênh WebSocket của đám cưới B (IDOR) → Bị từ chối `403`.
- **TC-AVT-01**: Khách mở 3 tab trình duyệt cùng lúc với 1 vé → Chỉ duy trì 1 avatar trong phòng, không nhân bản nhân vật.
