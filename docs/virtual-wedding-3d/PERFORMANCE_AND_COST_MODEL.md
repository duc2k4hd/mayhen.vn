# MÂY HẸN — VIRTUAL WEDDING WORLD 3D
## MÔ HÌNH HIỆU NĂNG & BÀI TOÁN CHI PHÍ KINH DOANH (PERFORMANCE & COST MODEL)

---

## 1. KẾT QUẢ BENCHMARK HIỆU NĂNG THỰC TẾ (`royal-crimson-marquee/v1`)

Đo thực tế trên tệp runtime `mayhen_royal_crimson_marquee_v1.glb` (`5.90 MB`) bằng Chromium WebGL2:

| Hạng mục đo lường | Desktop (`1440×900` High) | Desktop (`1440×900` Balanced) | Mobile (`390×844` Lite) | Ngưỡng nghiệm thu |
| :--- | :--- | :--- | :--- | :--- |
| **Kích thước tải `.glb`** | `5.90 MB` (Gzip/Brotli `~3.8 MB`) | `5.90 MB` | `5.90 MB` | `< 15.0 MB` |
| **Thời gian nạp & khởi tạo GPU** | **117 ms** | **117 ms** | **117 ms** | `< 1.500 ms` |
| **Tốc độ khung hình (FPS)** | **100 FPS** (`10.1 ms`) | **100 FPS** (`10.0 ms`) | **100 FPS** (`10.2 ms`) | `>= 60 / 45 FPS` |
| **Số lượng Draw Calls** | `892` | `892` | `892` | `< 1.200` |
| **Số lượng Tam giác / Đỉnh** | `57.116 tris / 113.870 verts` | `57.116 tris / 113.870 verts` | `57.116 tris / 113.870 verts` | `< 150.000` |

---

## 2. PHÂN TÍCH CHI PHÍ VẬN HÀNH & DOANH THU VÉ 5.000 ĐỒNG THEO QUY MÔ

### 2.1. Các cấu phần chi phí đơn vị (Ước tính theo giá hạ tầng Cloud/CDN tại Việt Nam)
1. **Chi phí sản xuất Venue Blender ban đầu (CapEx)**: Đã tự động hóa 100% bằng pipeline Python (`build_w03_venue.py` + `generate_pbr_textures.py`), chi phí biên tạo bản sao = **0đ**.
2. **Chi phí phân phối Asset `.glb` qua CDN**: `5.90 MB` (nén Brotli `~3.8 MB`) × `~800đ / GB` ≈ **3,2 đồng / lượt tải đầu tiên** (các lần mở lại đọc từ bộ nhớ đệm trình duyệt = **0đ**).
3. **Chi phí kết nối Realtime WebSocket (Presence + Chat)**: `~15 đồng / khách / giờ`.
4. **Chi phí băng thông Livestream (720p ABR trung bình 1.1 Mbps ≈ 0.48 GB / giờ)**:
   - Nếu xem 30 phút (`~0.24 GB`): `0.24 GB × 950đ/GB` ≈ **228 đồng / khách**.
   - Nếu xem trọn 1 giờ (`~0.48 GB`): `0.48 GB × 950đ/GB` ≈ **456 đồng / khách**.
5. **Chi phí cổng thanh toán / đối soát ngân hàng (SePay VietQR)**:
   - SePay tính theo gói thuê bao cố định hoặc phí giao dịch (`~0đ – 550đ / giao dịch`), cộng dự phòng hoàn tiền/hỗ trợ CSKH (`~150đ / vé`).

### 2.2. Bảng đối chiếu Chi phí & Lợi nhuận ở 4 quy mô khách tham dự (1 giờ lễ + Livestream)

| Quy mô khách tham dự | Tổng doanh thu vé (`5.000đ/khách`) | Chi phí CDN Asset + Realtime | Chi phí Livestream (`~456đ/khách`) | Chi phí Đối soát & CSKH (`~350đ/khách`) | Tổng chi phí vận hành | Lợi nhuận gộp ước tính (Biên lợi nhuận) |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **100 khách** | `500.000đ` | `1.820đ` | `45.600đ` | `35.000đ` | **82.420đ** | **+417.580đ** (`83.5%`) |
| **1.000 khách** | `5.000.000đ` | `18.200đ` | `456.000đ` | `350.000đ` | **824.200đ** | **+4.175.800đ** (`83.5%`) |
| **10.000 khách** | `50.000.000đ` | `182.000đ` | `4.560.000đ` | `3.500.000đ` | **8.242.000đ** | **+41.758.000đ** (`83.5%`) |
| **100.000 khách** | `500.000.000đ` | `1.820.000đ` | `45.600.000đ` | `35.000.000đ` | **82.420.000đ** | **+417.580.000đ** (`83.5%`) |

> **Cảnh báo rủi ro kinh doanh**: Nếu một cổng thanh toán trung gian thu phí cố định `>= 3.000đ + 1.5%/giao dịch` hoặc khách xem luồng 1080p không giới hạn trong suốt 5 giờ liên tục (`~4.5 GB/khách ≈ 4.275đ`), vé `5.000đ` sẽ **bị âm lãi**. Do đó bắt buộc:
> 1. Sử dụng **VietQR chuyển khoản trực tiếp đối soát tự động qua SePay** (không chịu phí cố định 3.000đ/lần của thẻ quốc tế).
> 2. Giới hạn độ phân giải livestream ở mức tối ưu **720p@30fps ABR** và tự động tạm dừng luồng video khi tab trình duyệt bị ẩn (`document.hidden`).

---

## 3. PHƯƠNG ÁN KHUYẾN NGHỊ: CHỦ TIỆC TÀI TRỢ VÉ (`HOST-SPONSORED MODE`) vs KHÁCH TỰ TRẢ 5.000Đ

Trong văn hóa đám cưới Việt Nam, việc yêu cầu khách mời trả 5.000đ ở cổng có thể tạo rào cản tâm lý khiến khách lớn tuổi ngại vào. Vì vậy hệ thống hỗ trợ **2 chế độ cấu hình linh hoạt cho Chủ tiệc**:
1. **Chế độ Chủ Tiệc Tài Trợ (`HOST_SPONSORED` — Khuyến nghị mặc định)**: Chủ tiệc mua gói cước bao trọn gói lượt tham dự (ví dụ gói 200 / 500 / 1.000 khách); khách mở thiệp bấm **"Bước vào Rạp Cưới 3D"** là vào thẳng không cần chuyển khoản 5.000đ.
2. **Chế độ Thu Vé Cổng (`GUEST_PAID_5000`)**: Khi chủ tiệc bật chế độ thu vé 5.000đ/lượt, hệ thống thực thi nghiêm ngặt quy trình thanh toán VietQR `MVP...` → đối soát Webhook → cấp `VirtualAccessPass` trước khi mở cổng.
