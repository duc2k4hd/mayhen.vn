# MÂY HẸN – DUYÊN HỢP: ĐẶC TẢ SẢN PHẨM & KIẾN TRÚC KỸ THUẬT

- **Đề án**: #166 — Xem Cao Ly Đầu Hình & Xem Tuổi Vợ Chồng Online Miễn Phí (`/cao-ly-dau-hinh` & `/xem-tuoi-vo-chong`)
- **Phiên bản Engine**: `mayhen_duyen_hop_v1.0` | **Cao Ly**: `caoly_mayhen_v1.0` | **Bát Trạch**: `bat_trach_mayhen_v1.0`
- **Ngày hoàn thiện**: 26/11/2026

---

## 1. Phân Chia Hai Công Cụ Chính (Không Trùng Lặp Nội Dung SEO)

1. **Công cụ A — Cao Ly Đầu Hình (`/cao-ly-dau-hinh`)**:
   - Title & H1 chính thức: `Xem Cao Ly Đầu Hình & Xem Tuổi Vợ Chồng Online Miễn Phí`
   - Tập trung phép tra có hướng **Nam dụng Thiên Can × Nữ dụng Địa Chi** (10 × 12 = 120 khóa duy nhất `CLDH-{STEM}-{BRANCH}`), **Vòng Thiên Văn Đồng & Ngọc SVG tương tác**, **6 Huy Chương Lục Cảnh** (ngôn ngữ trung lập, nhân văn, không hình ảnh đáng sợ), **Ma Trận Bảng Tra 120 Tổ Hợp** và 120 trang chi tiết `/cao-ly-dau-hinh/{slug}`.
2. **Công cụ B — Xem Tuổi Vợ Chồng Tổng Hợp (`/xem-tuoi-vo-chong`)**:
   - Title & H1 chính thức: `Xem Tuổi Vợ Chồng & Tra Cứu Tuổi Kết Hôn Online Miễn Phí`
   - Tập trung **Hai Vòng Kết Duyên SVG**, phân tích độc lập 6 lớp (*Can Chi*, *Thiên Can*, *Địa Chi*, *Ngũ Hành Nạp Âm*, *Cung Phi & Vòng Bát Quái Du Niên 8×8*, *Cao Ly Đầu Hình*, *Kim Lâu năm cưới*), 12 Chủ Đề Tình Duyên, 6 Câu Hỏi Trước Hôn Nhân, Sổ Nhân Duyên riêng tư và kết nối tự nhiên sang Lịch Vạn Niên (`/lich-am`) & Thiệp Cưới Online (`/mau-thiep`).

---

## 2. Kiến Trúc Engine Xác Định (`app/Services/Marriage/`)

- **`CaoLyRegistry`**: Đủ 120 khóa `CLDH-{STEM}-{BRANCH}` (phép tra có hướng `A→B ≠ B→A`), 6 Lục Cảnh (`Nhất Phú Quý · 富貴`, `Nhì Bần Tiện · 勤儉`, `Tam Vinh Hiển · 榮顯`, `Tứ Đạt Đạo · 達道`, `Ngũ Biệt Ly · 相思`, `Lục Tuyệt Mạng · 珍重`), nguồn tham chiếu *Cao Ly Đầu Hình (Đoàn Văn Đẩu & bản biên tập hiện đại Mây Hẹn)*, ghi chú dị bản minh bạch.
- **`KuaDuNienEngine`**: Công thức Cung Phi 4 chữ số năm sinh chuẩn mọi thế kỷ 1900–2100 (`r = digitalRoot(year)`, Nam `11 - r` `5→Khôn`, Nữ `4 + r` `5→Cấn`), 8 Quẻ vẽ đúng 3 hào Âm/Dương, và **Ma trận Du Niên 8×8 = 64 tổ hợp** đối xứng chuẩn Bát Trạch Minh Cảnh.
- **`MarriageCompatibilityEngine`**: Tái sử dụng `VietnameseCalendarEngine` (xử lý chính xác ngày sinh trước/sau Tết Nguyên Đán, cảnh báo khi chỉ nhập năm Dương lịch) & `BaziRegistry` (10 Can, 12 Chi `Mão = Mèo`, `Sửu = Trâu`, 60 Nạp Âm, phân biệt rạch ròi Dần–Hợi là Lục Hợp chứ không phải Lục Xung), tính Kim Lâu tuổi mụ cô dâu (`mod 9 ∈ {1, 3, 6, 8}`), **0% điểm hợp nhau giả**.
- **`MarriageAiInterpreterService`**: AI luận giải đa phương pháp, Hỏi Đáp Cặp Đôi & Trợ Lý Trao Đổi Với Gia Đình căn cứ 100% JSON từ Engine, hậu kiểm an toàn chặn `% hợp nhau`, ly hôn, tử vong, mê tín.

---

## 3. Phân Quyền Công Bằng, Sổ Nhân Duyên & Kiểm Chứng

- **Phân quyền gói cước (`PlanAccessService` + Cache chống lách quota)**:
  - Khách chưa đăng nhập: Tra cứu miễn phí cả Cao Ly 120 tổ hợp lẫn Xem Tuổi Vợ Chồng đa phương pháp.
  - Thành viên đăng nhập (`Premium 0đ`: 5 lượt lưu Sổ Nhân Duyên & AI/ngày, 5p/lượt, 3 câu hỏi sâu · `Plus`: 10 lượt/ngày, 1p/lượt, 6 câu hỏi sâu · `Pro/Admin`: 30 lượt/ngày, liên tục, 8 câu hỏi sâu), chống reset quota khi xóa sổ, tự động chuyển thẳng sang trang Sổ Nhân Duyên `/xem-tuoi-vo-chong/so-nhan-duyen/{slug}`, bảo vệ `403` isolation & `noindex`.
- **Kiểm thử tự động**:
  - `MarriageCompatibilityEngineTest.php` & `MarriageCompatibilityFeatureTest.php`: **4/4 PASS (132 assertions)** — kiểm chứng đủ 120 khóa Cao Ly, phép tra có hướng, trước/sau Tết, 64 ô Du Niên, **40.401 cặp năm sinh có thứ tự (1900–2100)**, `403` isolation, chia sẻ ẩn danh thu hồi `404`, `422 SAFETY_CRISIS_BLOCK`, và chống lách cooldown `429`.
  - Playwright E2E trên `/cao-ly-dau-hinh` và `/xem-tuoi-vo-chong`: **0 `pageerror`**, **0px overflow ngang**, đủ 5 mô hình nghệ thuật SVG tương tác.
