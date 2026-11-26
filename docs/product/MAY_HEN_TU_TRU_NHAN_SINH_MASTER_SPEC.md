# MÂY HẸN – TỨ TRỤ NHÂN SINH: ĐẶC TẢ SẢN PHẨM & KIẾN TRÚC KỸ THUẬT

- **Đề án**: #165 — Tra Cứu Bát Tự & Lập Lá Số Tứ Trụ Online Miễn Phí (`/bat-tu`)
- **Phiên bản Engine**: `mayhen_bazi_v1.0` | **Rule Version**: `tu_binh_mayhen_v1.0` | **Calendar Version**: `mayhen_vn_calendar_v1.0`
- **Ngày hoàn thiện**: 26/11/2026
- **Tiêu đề SEO & H1 chính thức**: `Tra Cứu Bát Tự & Lập Lá Số Tứ Trụ Online Miễn Phí`

---

## 1. Benchmark Đối Thủ & Đánh Giá Thư Viện (Khảo sát ngày 26/11/2026)

| Nguồn khảo sát | Điểm mạnh quan sát | Điểm yếu / Lưu ý | Phương án triển khai của Mây Hẹn |
| :--- | :--- | :--- | :--- |
| `fourpillarslab.com` / `bazilabs.com` | Hỗ trợ True Solar Time, Thập Thần, Đại Vận, trình bày 4 trụ rõ ràng | Thiên về tiếng Anh / lịch Trung Quốc UTC+8, thiếu bảng so sánh trường phái trực diện và thiếu ngữ cảnh lịch âm Việt Nam | Tái sử dụng trực tiếp `VietnameseCalendarEngine` (UTC+7) & `CalendarAstronomyEngine` (24 Tiết Khí Jean Meeus), hỗ trợ cả Civil / LMST / True Solar Time và So Sánh 3 Trường Phái |
| `6tail/lunar-php` (MIT) | Thư viện PHP mã nguồn mở đầy đủ `EightChar`, Tiết Khí, Thập Thần | Mặc định sử dụng quy ước lịch âm Trung Quốc UTC+8; cài thêm phụ thuộc bên ngoài làm phân mảnh thuật toán Can Chi trong dự án | Không cài thêm thư viện trùng lặp; xây dựng `App\Services\Bazi\*` thuần PHP 8.5 dựa trên lõi thiên văn & lịch pháp Việt Nam dùng chung đã qua 40.303 assertions |

---

## 2. Nguồn Cổ Học & Quy Ước Minh Bạch

- **Uyên Hải Tử Bình (淵海子平)** & **Tam Mệnh Thông Hội (三命通會)**: Hệ thống 10 Thiên Can, 12 Địa Chi (bản sắc Việt Nam: `Mão = Mèo`, `Sửu = Trâu`), 60 Hoa Giáp, 30 Nạp Âm, Tuần Không Vong, Tàng Can, Vòng Trường Sinh, Thần Sát Bát Tự (tách bạch hoàn toàn với sao Tử Vi), Thai Nguyên, Mệnh Cung & Thân Cung Bát Tự.
- **Tử Bình Chân Thuyên (子平真詮 — Thẩm Hiếu Chiêm)**: Xác định Cách Cục theo Nguyệt Lệnh và Thấu Can, phân biệt Dụng Thần Cách Cục với Phù Ức.
- **Trích Thiên Tủy (滴天髓)**: Phân tích Thông Căn, Thấu Can, khí thế toàn cục và mối quan hệ sinh trợ – tiết hao.
- **Cùng Thông Bảo Giám (窮通寶鑑)**: Phương pháp Dụng Thần Điều Hậu theo mùa sinh (Hàn – Nhiệt – Táo – Thấp).

---

## 3. Kiến Trúc Engine Xác Định (`app/Services/Bazi/`)

1. **`BaziRegistry`**: Single Source of Truth cho 10 Thiên Can, 12 Địa Chi, 60 Hoa Giáp, 30 Nạp Âm, Tàng Can (Bản/Trung/Dư Khí), Ma trận 10×10 Thập Thần, Vòng Trường Sinh 10×12, Ngũ Hợp / Lục Hợp / Tam Hợp / Tam Hội / Lục Xung / Lục Hại / Lục Phá / Tự Hình, 4 Method Profile (`tu_binh_default_v1`, `tu_binh_midnight_v1`, `tu_binh_true_solar_v1`, `tu_binh_lmst_v1`) và 22 địa điểm sinh (kinh độ + IANA timezone).
2. **`BaziCalculationEngine`**:
   - Chuyển ngày Âm Việt Nam sang Dương lịch qua `VietnameseCalendarEngine` trước khi lập trụ.
   - Tính **True Solar Time** (`Civil + 4 × Longitude − UTC_offset_min + Equation_of_Time` theo công thức Spencer/NOAA); so sánh Lập Xuân & 12 mốc Tiết trên cùng thang thời gian tuyệt đối UTC.
   - Hỗ trợ 5 mức độ chính xác giờ sinh (`exact`, `approx`, `window`, `session`, `unknown` — **tuyệt đối không mặc định 12:00**, giữ trống Trụ Giờ và xuất 12 phương án khả dĩ).
   - Tính Ngũ Hành theo **3 chế độ độc lập** (*8 chữ*, *Mở rộng Tàng Can*, *Khí thế Nguyệt Lệnh*), Vượng Tướng Hưu Tù Tử, Thân Vượng / Nhược minh bạch công thức & trọng số, Dụng Thần đa phương pháp (*Phù Ức*, *Điều Hậu*, *Cách Cục*), **Cây Lập Luận (Evidence Graph)**, **Đại Vận** (Dương Nam/Âm Nữ thuận, Âm Nam/Dương Nữ nghịch theo Can Năm; khởi vận 3 ngày = 1 năm, 1 ngày = 4 tháng; ẩn giới tính hiển thị cả 2 chuỗi), **Lưu Niên** & **12 Lưu Nguyệt**.
3. **`BaziRelationshipEngine`**: Phân tích 2 lá số độc lập (**Mây Hẹn – Tứ Trụ Kết Duyên**), 12 chủ đề tình duyên ứng dụng, tương tác Nhật Chủ 2 chiều & Cung Phối Ngẫu, bổ trợ Ngũ Hành, 4 câu hỏi đối thoại trước hôn nhân — **0% hợp nhau giả**.
4. **`BaziReverseLookupService`**: Tra cứu ngược Tứ Trụ tìm tất cả mốc ngày giờ sinh hợp lệ khớp 4 trụ Can Chi trong phạm vi năm nghiên cứu.
5. **`BaziSafetyGuardService` & `BaziAiInterpreterService`**: Chặn khủng hoảng tâm lý (`422 SAFETY_CRISIS_BLOCK`), lọc mê tín / tiên đoán y khoa / ngoại tình / ly hôn / `% hợp nhau`, sinh luận giải 10 tầng & Hỏi Đáp Bát Tự AI căn cứ 100% Evidence Graph kèm fallback biên tập hoàn chỉnh.

---

## 4. CSDL, Bảo Mật, SEO & Giao Diện 3D

- **CSDL**: Migration `2026_11_26_121500_create_bazi_tables.php` (InnoDB, 3 bảng `bazi_charts`, `bazi_followups`, `bazi_relationship_reports`, `user_id` indexed không FK sang `users`) lưu đầy đủ versioning (`method_id`, `engine_version`, `rule_version`, `calendar_version`, `calculation_hash`).
- **Quyền riêng tư**: Tra cứu cơ bản miễn phí không cần đăng nhập; hồ sơ cá nhân (`/bat-tu/ho-so`, `/bat-tu/ket-qua/{slug}`) bắt buộc `auth`, kiểm tra chủ sở hữu (`403` isolation), gắn `noindex, nofollow`; thẻ chia sẻ ẩn danh & xuất ảnh PNG HiDPI (`1:1`, `4:5`, `9:16`) mặc định ẩn ngày giờ sinh; thu hồi chia sẻ trả `404`.
- **Giao diện (`mayhen_bazi_*`)**: 100% Pure Vanilla HTML/CSS/JS, bảng màu Thanh Ngọc `#1E4D3B` · Đỏ Son `#85182A` · Hoàng Kim `#CFA860` · Giấy Dó `#FAF5EB`, Không gian Tứ Trụ 3D xoay góc + Bảng 2D chuyên nghiệp, Ngũ Hành SVG, Thập Thần Orbit SVG, Timeline Đại Vận & Time Travel.

---

## 5. Kết Quả Kiểm Chứng & Giới Hạn Trung Thực

- **Pest Unit & Feature Tests** (`tests/Unit/BaziEngineTest.php`, `tests/Feature/BaziFeatureTest.php`): **6/6 PASS — 30.354 assertions** trong `5.09s`, bao gồm đủ 120 tổ hợp Ngũ Hổ Độn, 120 tổ hợp Ngũ Thử Độn, 100 tổ hợp Thập Thần, ranh giới Lập Xuân, tương đương Âm–Dương lịch Việt Nam (Tết 1985), ranh giới Giờ Tý `23:00` vs `00:00`, không biết giờ sinh, Đại Vận thuận/nghịch, Bát Tự tình duyên, Safety Guard, phân quyền `403` và **10.000 ca phân tầng**.
- **Playwright E2E** (`http://127.0.0.1:8000/bat-tu`, Desktop `1440×900` & Mobile `390×844`): **0 `pageerror`**, **0px overflow ngang**, đủ 4 trụ 3D, Ngũ Hành SVG & Thập Thần Orbit SVG.
- **Giới hạn minh bạch**:
  - Thời điểm giao Tiết dùng công thức Jean Meeus + Newton–Raphson (chính xác `< 1 phút` trong 1900–2100); người sinh cách mốc Lập Xuân hoặc giao Tiết dưới vài phút nên đối chiếu thêm lịch thiên văn JPL DE440.
  - Bát Tự là tri thức văn hóa cổ học dùng để tự chiêm nghiệm và đối thoại thấu hiểu trong gia đình; không thay thế quyết định hôn nhân, y tế, tâm lý, pháp lý hay tài chính.
