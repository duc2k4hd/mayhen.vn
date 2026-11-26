# MÂY HẸN – THE ART OF TAROT
## Master Product & Engineering Specification (Đề án #163)

> **Tên chức năng chính thức (Title & H1):** Xem Tarot Online & Bói Bài Tarot Online Miễn Phí  
> **Concept phụ:** Mây Hẹn – The Art of Tarot  
> **URL chính:** `/tarot`  
> **Trạng thái:** Đã triển khai P0 + P1 + Core P2, kiểm thử Pest (8/8 PASS, 71 assertions) & Playwright E2E.

---

## 1. Báo cáo Benchmark Quốc tế (Khảo sát trực tiếp)
| Nền tảng | Chức năng quan sát | Điểm mạnh | Điểm yếu / Giới hạn | Điểm Mây Hẹn cải thiện |
|---|---|---|---|---|
| **Labyrinthos** (`labyrinthos.co`) | Chọn bài trực quan, nhật ký Tarot, thư viện 78 lá | Thiết kế bài nghệ thuật, trải nghiệm mobile mượt | Nhiều trải bài sâu khóa sau app/tài khoản trả phí | Đưa trọn bộ 3D bàn nhung & Sổ Bài Tarot lên web miễn phí, không cần cài app |
| **Biddy Tarot** (`biddytarot.com`) | Thư viện 78 lá RWS chuẩn, xuôi/ngược, hướng dẫn đặt câu hỏi | Cấu trúc kiến thức sâu, ngôn ngữ nhân văn | Thiên về nội dung đọc, thiếu bàn trải bài 3D tương tác | Kết hợp thư viện 78 lá RWS với bàn nhung 3D + AI 6 tầng + câu hỏi mở |
| **Tarot.com** (`tarot.com`) | Tarot miễn phí, Tarot tình yêu, Tarot hàng ngày | Phân loại rõ theo nhu cầu người dùng | Nhiều quảng cáo/upsell gây gián đoạn trải nghiệm | Giao diện Quiet Luxury sạch sẽ, miễn phí thực chất, không spam banner |
| **Facade** (`facade.com/tarot`) | Nhiều kiểu trải bài cổ điển (Celtic Cross, 3 lá) | Nhanh, rõ cấu trúc trải bài | Giao diện cũ, thiếu ngữ cảnh cá nhân hóa | Giữ cấu trúc chuẩn (Celtic Cross 10 lá, 3 lá, 5 lá) trên giao diện Fine Art hiện đại |
| **Trusted Tarot** (`trustedtarot.com`) | Rút bài trực tuyến miễn phí nhiều vị trí | Dễ tiếp cận với người mới | Văn phong đôi khi thiên về khuôn mẫu đại trà | Luận giải 6 tầng nhân văn, khóa chặt dữ liệu lá đã rút, chặn văn mẫu AI-slop |

## 2. Triết lý Sản phẩm & Định vị Hệ sinh thái Mây Hẹn
- Cân bằng 6 giá trị: **Chính xác · Minh bạch · Có chiều sâu · Nghệ thuật · Cá nhân hóa · Nhân văn**.
- Tarot là phân hệ soi chiếu nội tâm, hỗ trợ tình yêu – chuẩn bị kết hôn – gia đình trong hệ sinh thái Mây Hẹn, không làm loãng định vị nền tảng Thiệp Cưới & Hôn Nhân.
- Không dự đoán định mệnh cứng nhắc, không gán tỷ lệ % vô căn cứ, không thay thế tư vấn y tế/tâm lý/pháp lý/tài chính.

## 3. Hệ thống Tarot Chính thức (Rider–Waite–Smith)
- Mặc định duy nhất: **Rider–Waite–Smith (RWS)** gồm chính xác **78 lá** (`rws_major_00`..`rws_major_21`, `rws_wands_01`..`14`, `rws_cups_01`..`14`, `rws_swords_01`..`14`, `rws_pentacles_01`..`14`).
- Bảo toàn chuẩn đánh số kinh điển RWS: **Strength = VIII** (`rws_major_08`), **Justice = XI** (`rws_major_11`).
- Không trộn lẫn Marseille, Thoth, Oracle hay Lenormand.

## 4. Kiến trúc Kho Dữ liệu 78 Lá (`database/data/tarot/`)
- `manifest.json`: phiên bản bộ bài (`rws-v1.0`), nguồn tham khảo (*The Pictorial Key to the Tarot* — A.E. Waite, 1910).
- `major_arcana_22.json`, `minor_wands_14.json`, `minor_cups_14.json`, `minor_swords_14.json`, `minor_pentacles_14.json`: đầy đủ `card_id`, `slug`, `roman`, `number`, `name_en`, `name_vi`, `arcana`, `suit`, `element`, `symbols`, `keywords_upright`, `keywords_reversed`, `upright_meaning`, `reversed_meaning`, `domains` (8 lĩnh vực), `reflection_question`, `common_misconception`.
- `spreads.json`: 9 kiểu trải bài chuẩn hóa.

## 5. Hệ thống 9 Kiểu Trải Bài
1. `daily_one` — Tarot Hôm Nay (1 lá, giữ ổn định trong ngày cho thành viên).
2. `advice_one` — Tarot Một Lá · Lời Khuyên (1 lá).
3. `yes_no_one` — Tarot Có Hoặc Không Có Điều Kiện (1 lá, không dùng bảng cứng).
4. `three_time` — Trải Bài Ba Lá · Dòng Thời Gian (3 lá).
5. `three_problem` — Trải Bài Ba Lá · Giải Quyết Vấn Đề (3 lá).
6. `three_love` — Tarot Tình Yêu Ba Lá (3 lá).
7. `five_love` — Tarot Tình Yêu Năm Lá Chuyên Sâu (5 lá).
8. `mayhen_nhan_duyen_7` — **Mây Hẹn – Hành Trình Nhân Duyên** (7 lá độc quyền cho cặp đôi & chuẩn bị kết hôn).
9. `celtic_cross_10` — **Celtic Cross 10 Lá** (biến thể hiện đại công bố rõ, lá số 2 cắt ngang 90° trên lá số 1 nhưng vẫn giữ nguyên chiều Xuôi/Ngược khi luận giải).

## 6. Cơ chế Ngẫu nhiên CSPRNG & Minh bạch (`TarotRandomizationEngine`)
- Xáo toàn bộ 78 lá phía máy chủ bằng **Fisher–Yates CSPRNG (`random_int`)**.
- **Rút không hoàn lại 100%**: mỗi `slot_index` (0..77) chỉ được rút tối đa 1 lần (trùng trả `409 Conflict`).
- Chiều bài: `upright_reversed` (50/50 độc lập từng lá qua `random_int(0, 1)`) hoặc `upright_only`.
- Khóa thứ tự bộ bài trước khi người dùng chọn bằng **SHA-256 Commitment Hash + Nonce** hiển thị minh bạch trên bàn trải bài và trong mục Kiểm định thuật toán.

## 7. Vòng đời Phiên Trải Bài & Nhập Bài Vật Lý
- Vòng đời máy trạng thái: `SHUFFLED` → `SELECTING` → `SELECTED` → `COMPLETED`.
- Hỗ trợ 3 chế độ: **Tự tay chọn lá trên bàn 3D**, **Rút nhanh không hoàn lại**, và **Nhập lá từ bộ bài vật lý** (có kiểm tra không trùng lá trong cùng 1 bộ bài).

## 8. Nghệ thuật Bộ bài Fine Art & Tương tác 3D (`tarot.css`, `tarot.js`)
- Bàn nhung xanh ngọc lục bảo (`#10241D` → `#142D25`), viền vàng cổ (`#C8A055`), trang luận giải giấy ngà Editorial (`#FAF5EB`), hoàn toàn thuần Vanilla HTML/CSS/JS với tiền tố `mayhen_tarot_*`.
- **Mặt sau đối xứng 180°** (`buildTarotBackSvg`) đảm bảo không lộ chiều Xuôi/Ngược trước khi lật.
- **Mặt trước Fine Art SVG** cho đủ 78 lá; khi lá Ngược, chỉ phần hình tượng xoay 180° còn tiêu đề và nhãn chiều giữ thẳng để dễ đọc.
- Hiệu ứng 3D Riffle Shuffle, 3D Fan Arc (Desktop) / Vuốt ngang mượt (Mobile), 3D lật 180°, 3 mức hiệu năng (`high / balanced / lightweight` + `prefers-reduced-motion`) và âm thanh lá bài Web Audio (TẮT mặc định).

## 9. Kiến trúc AI 6 Tầng & An toàn Nhân văn (`TarotAiInterpreterService`, `TarotSafetyGuardService`)
- Tái sử dụng trực tiếp `LlmProviderInterface` & `AiGovernanceService` hiện hữu của Mây Hẹn.
- AI **chỉ luận giải** dựa trên danh sách lá bài cố định đã khóa từ Rule Engine; tuyệt đối không được chọn hay đổi lá.
- **Lớp chặn khủng hoảng (`422 SAFETY_CRISIS_BLOCK`)**: phát hiện từ khóa tự hại/bạo hành và cung cấp đường dây hỗ trợ 115/111/113 thay vì rút bài.
- **Lớp kiểm định đầu ra AI**: chặn cụm từ AI-slop/mê tín/cúng giải hạn/tiên đoán ngoại tình–ly hôn–y khoa–xổ số, chặn tỷ lệ % vô căn cứ, đối chiếu chính xác số lá và `card_id`. Tự động fallback sang kho tri thức chuẩn RWS khi AI không khả dụng.

## 10. Hỏi sâu sau khi rút, Sổ Bài Tarot & Xuất Thẻ PNG HiDPI
- **Hỏi sâu (`POST /tarot/ket-qua/{slug}/hoi-sau`)**: khóa chặt vào các lá đã rút (tối đa 8 lượt/trải bài), không rút thêm lá mới.
- **Sổ Bài Tarot (`/tarot/nhat-ky`)**: lưu lịch sử, ghi chú chiêm nghiệm (cảm xúc, nhận ra, hành động) và thống kê tần suất thực tế (không suy diễn mê tín).
- **Xuất Thẻ Nghệ Thuật PNG HiDPI (`1:1`, `4:5`, `9:16`)**: sử dụng bộ font an toàn tiếng Việt (`MayhenV3EditorialSerif`, `Times New Roman`, `Segoe UI`), mặc định ẩn câu hỏi riêng tư.

## 11. Cơ sở dữ liệu & Quyền riêng tư
- Migration `2026_11_26_101500_create_tarot_tables.php` (InnoDB, 3 bảng: `tarot_readings`, `tarot_reading_followups`, `tarot_reading_journals`).
- Trang kết quả `/tarot/ket-qua/{slug}` luôn gắn `noindex, nofollow` và kiểm tra quyền sở hữu chặt chẽ (`403` với người lạ).
- Chia sẻ ẩn danh `/tarot/chia-se/{shareToken}` có thể bật/thu hồi bất cứ lúc nào và luôn ẩn câu hỏi cá nhân.

## 12. SEO & Giới hạn đã công bố
- Các trang công khai được index & đưa vào XML Sitemap: `/tarot`, `/tarot/tinh-yeu`, `/tarot/hom-nay`, `/tarot/celtic-cross`, `/tarot/78-la-bai`, `/tarot/la-bai/{slug}`.
- **Giới hạn hiện tại**: Minh họa 78 lá hiện sử dụng bộ Fine Art SVG vector chuẩn hóa nội bộ (tải tức thì, 0 phụ thuộc ngoài); bộ tranh vẽ minh họa độ phân giải cao (illustrated raster deck) và nhiều bộ bài mở rộng sẽ được bổ sung ở giai đoạn P2 nâng cao khi có bộ artwork bản quyền riêng.
