# MÂY HẸN — VIRTUAL WEDDING WORLD 3D
## LỘ TRÌNH TRIỂN KHAI TỪNG GIAI ĐOẠN (IMPLEMENTATION ROADMAP W00–W11)

---

## 1. TRẠNG THÁI CÁC GIAI ĐOẠN HIỆN TẠI (`W00`–`W03`: HOÀN TẤT 100%)

| Wave | Nội dung công việc | Trạng thái | Sản phẩm & Bằng chứng bàn giao |
| :--- | :--- | :--- | :--- |
| **W00** | Kiểm toán mã nguồn Mây Hẹn, xác định kiến trúc & ranh giới dữ liệu | **COMPLETED** | [`PROJECT_INTEGRATION_AUDIT.md`](PROJECT_INTEGRATION_AUDIT.md) |
| **W01** | Phân tích toàn bộ video gốc (18 frames, 7 góc quay) & kiểm kê đối tượng | **COMPLETED** | [`VIDEO_REFERENCE_BREAKDOWN.md`](VIDEO_REFERENCE_BREAKDOWN.md), [`REFERENCE_OBJECT_REGISTER.md`](REFERENCE_OBJECT_REGISTER.md) |
| **W02** | Dựng Graybox Blender tỷ lệ thực `1 unit = 1.0m` & kiểm chứng khoảng lùi | **COMPLETED** | `mayhen_royal_crimson_graybox_v1.blend` + 4 ảnh render Cycles tại `renders/w02-graybox/` |
| **W03** | Hoàn thiện Venue PBR chân thực, 5 khung giờ ánh sáng, 15 NPC chuyển động, 2 màn LED 16:9, xuất `.glb` (`5.90 MB`), video walkthrough & bản demo trình duyệt WebGL2 (`100 FPS`) | **COMPLETED** | `mayhen_royal_crimson_marquee_v1.blend`, `mayhen_royal_crimson_marquee_v1.glb`, `w03_venue_walkthrough.mp4`, `walkthrough-demo.html` + 15 ảnh nghiệm thu tại `renders/w03-pbr/` |

---

## 2. LỘ TRÌNH CÁC WAVE TIẾP THEO (`W04`–`W11` — CHỜ NGHIỆM THU W03 ĐỂ KHỞI ĐỘNG)

| Wave | Mục tiêu kỹ thuật | Điều kiện hoàn thành (Exit Criteria) |
| :--- | :--- | :--- |
| **W04** | Tích hợp trình xem 3D chính thức vào route Laravel, cơ chế nạp phân tầng (Progressive Loading), 3 mức đồ họa (`High / Balanced / Lite`), fallback 2D khi WebGL lỗi và Product Tour 3D độc lập | Chạy mượt trên cả Desktop & Mobile, có fallback xem lễ khi tắt WebGL, không ảnh hưởng Core Web Vitals của thiệp cưới 2D. |
| **W05** | Triển khai Backend Vòng đời 48 giờ, kiểm tra tạo trước `T - 5h` theo giờ UTC server, khóa cổng tự động sau `+5h` mỗi phiên tiệc, máy trạng thái 11 bước (`DRAFT`..`ENDED`) | Pest Feature Tests phủ 100% các trường hợp biên về múi giờ, lệch đồng hồ client, đám cưới 2 ngày và tự khóa cổng. |
| **W06** | Hệ thống chọn Avatar chuẩn hóa (Nam/Nữ/Trung tính, Vest/Áo dài), điều khiển di chuyển va chạm, đồng bộ hoạt cảnh NPC theo timeline sự kiện không bị reset khi F5 | Khách vào ở bất kỳ thời điểm nào đều thấy đúng tiến độ hoạt cảnh của giờ đó. |
| **W07** | Thanh toán Sandbox 5.000đ (`virtual_entry_payments`, mã `MVP...`), đối soát Webhook SePay HMAC-SHA256, cấp `VirtualAccessPass` và chế độ Chủ tiệc tài trợ vé | Tách biệt 100% khỏi bảng `orders`, xử lý đủ 12 trường hợp biên thanh toán. |
| **W08** | Hệ thống Livestream thật từ trình duyệt chủ tiệc (Camera/Mic) lên 2 màn hình LED 3D trong rạp, chế độ xem toàn màn hình và đo độ trễ thực tế | Đo và ghi nhận `first_frame_ms`, `median_latency_ms`, `p95_latency_ms`; trạng thái `IDLE` trung thực khi chưa phát. |
| **W09** | Cổng Realtime WebSocket phân phòng (`Room Shards`), đồng bộ tối đa 35 avatar gần nhất, chống nhân bản khi mở nhiều tab, Chat & gửi lời chúc có kiểm duyệt | Chịu tải mượt mà, chat mất kết nối không làm sập cảnh 3D, chống IDOR & XSS. |
| **W10** | Dashboard quản lý Đám cưới 3D cho Chủ tiệc (`/tai-khoan/...`) và trang Quản trị danh mục Venue 3D có phiên bản bất biến cho Admin (`/admin/...`) | Giao diện *Quiet Luxury* thân thiện, quản lý phiên bản `.glb` không làm gián đoạn lễ đang chạy. |
| **W11** | Kiểm thử chịu tải tăng dần (`10 → 50 → 100 → 500 → 1.000+`), diễn tập khôi phục sự cố và nghiệm thu tổng thể | Báo cáo benchmark thực tế và bộ Pest/Node test toàn trình đạt 100% PASS. |
