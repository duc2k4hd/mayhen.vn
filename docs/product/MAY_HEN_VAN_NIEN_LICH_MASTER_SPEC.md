# MÂY HẸN – VẠN NIÊN LỊCH: ĐẶC TẢ SẢN PHẨM & KIẾN TRÚC KỸ THUẬT

- **Đề án**: #164 — Xem Lịch Âm & Lịch Vạn Niên Online Miễn Phí Hôm Nay (`/lich-am`)
- **Phiên bản Engine**: `mayhen_vn_calendar_v1.0` | **Almanac Rules**: `ngoc_hap_mayhen_v1.0`
- **Ngày hoàn thiện**: 26/11/2026
- **Tiêu đề SEO & H1 chính thức**: `Xem Lịch Âm & Lịch Vạn Niên Online Miễn Phí Hôm Nay`

---

## 1. Benchmark Đối Thủ & Nguồn Tham Khảo (Khảo sát ngày 26/11/2026)

| Nguồn khảo sát | Chức năng quan sát | Điểm mạnh | Điểm yếu | Phương án cải tiến của Mây Hẹn |
| :--- | :--- | :--- | :--- | :--- |
| `xemamlich.uhm.vn` (Hồ Ngọc Đức) | Thuật toán âm lịch Việt Nam UTC+7, tài liệu lịch sử âm lịch 1960–1975 | Nguồn chuẩn mực nhất về quy luật Sóc, Trung Khí, tháng nhuận và khác biệt múi giờ UTC+7 vs UTC+8 | Giao diện học thuật thuần chữ, chưa có trải nghiệm di động & tiện ích hằng ngày | Tái sử dụng lõi Sóc/Trung Khí chuẩn Hồ Ngọc Đức / Jean Meeus, kết hợp giao diện Quiet Luxury + 3D Bloc + Thiên văn trực quan |
| `lichngaytot.com` | Lịch ngày, lịch tháng, xem ngày tốt xấu, tử vi | Phủ SEO rộng, nhiều mục tra cứu nhanh | Quảng cáo dày, giao diện rối mắt, nhiều nội dung giật gân/hù dọa mê tín, trộn lẫn thiên văn và dân gian | 0 quảng cáo rác, tách rạch ròi 3 lớp *Thiên văn — Lịch pháp — Truyền thống*, ngôn ngữ điềm đạm, không chấm `% may mắn` giả |
| `licham.net` / `amlich.org` | Đổi ngày âm dương, lịch tháng/năm, đếm ngược Tết | Nhẹ, tải nhanh, thao tác đổi ngày trực tiếp | Thiếu kiểm tra biên nghiêm ngặt cho tháng thiếu/nhuận, thiếu Pha Mặt Trăng thiên văn và đồng hồ 12 thời thần tương tác | Kiểm tra chặt ngày Dương (`29/02/1900`, `31/04`) & Âm (ngày 30 tháng thiếu, tháng nhuận), bổ sung Canvas Mặt Trăng 3D & Đồng hồ 12 Thời Thần SVG |

---

## 2. Kiến Trúc Vietnamese Calendar Engine Dùng Chung (`app/Services/Calendar/`)

Mây Hẹn xây dựng **một Engine lịch pháp duy nhất**, độc lập hoàn toàn với giao diện và AI, tái sử dụng lõi thiên văn `App\Services\Iching\VietnameseLunarCalendarService` để đảm bảo đồng nhất dữ liệu giữa **Vạn Niên Lịch**, **Gieo Quẻ Kinh Dịch** và **Tử Vi Đẩu Số**:

1. **`VietnameseCalendarEngine`**:
   - Phạm vi hỗ trợ: `1900–2100`.
   - Hai chế độ lịch minh bạch:
     - `vn_modern_utc7` (mặc định): chuẩn lịch Việt Nam hiện hành `Asia/Ho_Chi_Minh` (`UTC+7`), độc lập timezone server/trình duyệt.
     - `vn_historical`: minh bạch ghi chú múi giờ `UTC+8` áp dụng tại miền Bắc Việt Nam giai đoạn `1960–1975` và sự khác biệt lịch sử.
   - Quy đổi 2 chiều `convertSolarToLunar` & `convertLunarToSolar` (`O(13)` theo danh sách tháng âm của năm), kiểm tra biên chặt (từ chối `29/02/1900`, `29/02/2100`, `31/04`, ngày `30` của tháng thiếu `29` ngày và tháng nhuận không tồn tại).
   - Chuẩn hóa 10 Thiên Can, 12 Địa Chi (bản sắc Việt Nam: `Mão = Mèo`, `Sửu = Trâu`), 60 Hoa Giáp & 30 Nạp Âm từ `database/data/tuvi/sexagenary_60.json`.
2. **`CalendarAstronomyEngine`**:
   - **24 Tiết Khí**: Tính chính xác thời điểm giao tiết từng năm theo kinh độ Mặt Trời `15°` (Jean Meeus + lặp Newton–Raphson hội tụ `< 1 phút`, quy đổi `UTC+7`).
   - **Pha Mặt Trăng Thiên Văn**: Góc pha, tỷ lệ chiếu sáng `0–100%`, tuổi Trăng, xu hướng *Waxing/Waning*, thời điểm Trăng Tròn & Trăng Non kế tiếp — **phân biệt rõ với ngày 15 Âm lịch**.
   - **Mặt Trời Mọc / Chính Ngọ / Lặn**: Theo công thức NOAA cho 6 thành phố Việt Nam (`Hà Nội`, `TP.HCM`, `Đà Nẵng`, `Huế`, `Hải Phòng`, `Cần Thơ`).
3. **`TraditionalAlmanacEngine`**:
   - 12 Thần Hoàng Đạo / Hắc Đạo cho ngày và 12 khung giờ trong ngày (đúng 6 Hoàng Đạo + 6 Hắc Đạo).
   - 12 Trực (Thập Nhị Kiến Trừ neo theo tháng Tiết Khí), 28 Tú, Lục Diệu, Hỷ Thần / Tài Thần, Tam Nương / Nguyệt Kỵ (kèm ghi chú văn hóa ôn hòa).
   - **Activity Date Finder** (`evaluateDateForActivity`): Hỗ trợ lọc cuối tuần (`weekends_only`), tách rõ *Yếu tố thuận lợi*, *Yếu tố lưu tâm* và *Xung đột giữa các hệ quy tắc*, tuyệt đối không sinh `% may mắn` giả.
4. **`VietnameseHolidayEngine`**:
   - Phân biệt `is_public_holiday` (nghỉ lễ theo Bộ luật Lao động) và ngày lễ truyền thống / kỷ niệm.
   - Đếm ngược sự kiện lớn & **Tính Ngày Giỗ / Sinh Nhật Âm Lịch** nhiều năm hỗ trợ quy ước gia đình (`last_day_of_month` vs `skip` khi gặp tháng thiếu; `regular_only` / `leap_if_exists` / `both` khi gặp tháng nhuận).
5. **`CalendarAiInterpreterService`**:
   - AI tuyệt đối không tự tính ngày âm, Can Chi, Tiết Khí hay Pha Trăng; chỉ diễn giải dữ liệu JSON đã kiểm chứng từ Engine.
   - Bản giải thích công khai theo ngày được cache trong bảng `calendar_ai_daily_caches`; có fallback tri thức đầy đủ khi AI không khả dụng.

---

## 3. Lịch Gia Đình Riêng Tư & Xuất `.ics` Chuẩn Xác

- **CSDL**: Bảng `calendar_family_events` (InnoDB, `unsignedBigInteger('user_id')->index()`) bảo vệ riêng tư qua middleware `auth` và kiểm tra quyền sở hữu (`403` cho người khác).
- **Xuất `.ics` (`/lich-am/lich-cua-toi/xuat-ics`)**: Không dùng `RRULE:FREQ=YEARLY` Gregory cho sự kiện Âm lịch (vốn sẽ sai ngày mỗi năm), mà quy đổi chính xác sang từng `VEVENT` Dương lịch cho 5 năm kế tiếp.

---

## 4. Thiết Kế Giao Diện, SEO & Hiệu Năng

- **100% Pure Vanilla HTML + CSS + JS**: Tiền tố BEM bắt buộc `mayhen_calendar_*`, bảng màu Di sản Việt Nam & Quiet Luxury (`#85182A` Đỏ Son, `#1E4D3B` Xanh Ngọc Bích, `#FAF5EB` Giấy Dó Kem Ấm, `#CFA860` Vàng Đồng, `#101C2C` Trời Đêm Thiên Văn).
- **Tương tác trực quan**: Tờ Lịch Bloc 3D lật mượt không tải lại trang, Canvas Pha Mặt Trăng 3D theo góc pha thực, Đồng hồ 12 Thời Thần SVG, Lưới Lịch Tháng tương tác, 4 Tab tiện ích (Đổi ngày 2 chiều, Tìm ngày cưới hỏi/công việc, Tính ngày giỗ, Hỏi AI).
- **SEO chống Crawl Trap**: Trang `/lich-am`, `/lich-am/doi-ngay`, `/lich-am/tiet-khi`, `/lich-am/tinh-ngay-gio`, `/lich-am/nam/{year}` được đưa vào `SitemapGeneratorService`; trang `/lich-am/ngay/{YYYY-MM-DD}` chỉ `index` trong khoảng `±1` năm quanh năm hiện tại, ngoài khoảng đó tự động gắn `noindex, follow`.

---

## 5. Kết Quả Kiểm Chứng & Giới Hạn Trung Thực (Mục 91)

- **Pest Unit & Feature Tests** (`tests/Unit/VietnameseCalendarEngineTest.php` & `tests/Feature/CalendarFeatureTest.php`): **9/9 PASS — 40.303 assertions** trong `3.41s`, bao gồm:
  - Mốc chuẩn UTC+7: Tết 1985 (`21/01/1985`), Tết 2007 (`17/02/2007`), Tết 2030 (`02/02/2030`), tháng 2 nhuận năm 2004.
  - Bất biến & round-trip `Solar ↔ Lunar` toàn dải **1900–2099** (kiểm chứng đầy đủ tháng thường, tháng nhuận, 29/30 ngày).
  - Xác thực từ chối `29/02/1900`, `29/02/2100`, `31/04/2026`, ngày 30 trong tháng thiếu và tháng nhuận không tồn tại.
  - Kiểm thử API, xác thực phân quyền `403` và xuất `.ics` 5 năm.
- **Playwright E2E**: Đã kiểm tra trên `http://127.0.0.1:8000/lich-am` ở Desktop (`1440×900`) và Mobile (`390×844`): **0 `pageerror`**, **0px overflow ngang**, Canvas Mặt Trăng 3D và Lịch Bloc hiển thị sắc nét.
- **Giới hạn minh bạch**:
  - Thuật toán thiên văn sử dụng công thức đơn giản hóa chuẩn Jean Meeus / Hồ Ngọc Đức (chính xác tới dưới ~1 phút cho 1900–2100); với các mốc Sóc rơi sát 23:59:xx / 00:00:xx UTC+7 cách hàng thế kỷ, sai số ΔT cực nhỏ có thể cần đối chiếu lịch thiên văn đầy đủ (JPL DE440).
  - Các chỉ số *Hoàng Đạo, 12 Trực, 28 Tú, Tam Nương* mang giá trị tham khảo văn hóa truyền thống, không phải dự đoán khoa học hay căn cứ thay thế kế hoạch thực tế của gia đình.
