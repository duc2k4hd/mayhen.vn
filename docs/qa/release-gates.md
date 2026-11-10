# MÂY HẸN (MAYHEN.VN) — BẢNG MA TRẬN NGHIỆM THU PHÁT HÀNH (RELEASE GATES MATRIX)

- **Phiên bản đánh giá**: Mây Hẹn 2.0 Thương Mại
- **Ngày nghiệm thu**: 2026-11-09
- **Trạng thái phát hành**: **PRODUCTION READY (SẴN SÀNG KHỞI CHẠY)**

---

## 1. TỔNG HỢP TRẠNG THÁI 5 CỔNG CHẤT LƯỢNG (RELEASE GATES)

| Cổng kiểm định | Trọng tâm kiểm soát | Tiêu chí nghiệm thu | Trạng thái |
| :--- | :--- | :--- | :---: |
| **GATE 0 (P0)** | **Bảo mật, Tiền tệ & Quyền hạn** | - 100% route nhạy cảm có phân quyền Tenant Isolation.<br>- Webhook SePay chống replay, xác thực chữ ký HMAC-SHA256, xử lý idempotency.<br>- Thiệp nháp, thiệp khóa, thiệp hết hạn chặn toàn bộ tương tác khách.<br>- Không có lỗ hổng IDOR/BOLA trên dữ liệu khách hàng. | **ACCEPTED** |
| **GATE 1 (P1)** | **Khách mời, RSVP & Check-in** | - Đồng bộ 2 chiều tuyệt đối giữa `table_id` và `table_name`.<br>- Điểm danh QR chống race-condition, không double-count.<br>- Sĩ số bàn tiệc co giãn chính xác theo phản hồi RSVP.<br>- Xóa bàn tiệc không cascade làm mất khách mời. | **ACCEPTED** |
| **GATE 2 (P1/P2)** | **Studio Tạo Thiệp & Đồng Bộ** | - Chuyển đổi mẫu thiệp bảo toàn 100% dữ liệu cưới gốc.<br>- Khắc phục FOUC/màn hình trắng: iframe nhúng tự động mở cánh thiệp và gửi handshake `MAYHEN_BRIDGE_READY`.<br>- 100% template có đủ `data-sync-target`.<br>- Cấm tuyệt đối chèn ảnh người trong phần Ký tên & RSVP. | **ACCEPTED** |
| **GATE 3 (P2)** | **Kỷ Niệm & Trải Nghiệm Sau Cưới** | - Hộp Thời Gian (`Time Capsule`) khóa cứng trước ngày mở (422).<br>- 100% ảnh và lời chúc chưa duyệt bị ẩn khỏi Live Wall/Két Kỷ Niệm.<br>- Xuất danh sách khách chuẩn UTF-8 BOM và vô hiệu hóa CSV Formula Injection (CWE-1236).<br>- Xuất gói kỷ niệm ngoại tuyến ZIP độc lập (TASK-VAL-18). | **ACCEPTED** |
| **GATE 4 (P2)** | **Thương Mại & Trải Nghiệm Khách** | - Chế độ người lớn tuổi (Senior-Friendly Mode) chuẩn tiếp cận dễ đọc.<br>- Thanh tổng duyệt Rehearsal Bar an toàn, không sinh rác DB.<br>- Bãi đỗ xe thông minh (Parking Concierge) đầy đủ chỉ dẫn.<br>- Bảo vệ dữ liệu cá nhân theo Luật 91/2025/QH15 & Nghị định 13.<br>- Tuân thủ chính sách Autoplay âm thanh trình duyệt. | **ACCEPTED** |

---

## 2. NỢ KỸ THUẬT (TECHNICAL DEBT) & RỦI RO CÒN LẠI

1. **Khả năng mở rộng quét QR quy mô lớn (High-Concurrency Spike)**:
   - *Hiện trạng*: Đã xử lý khóa logic, kiểm thử quét dồn dập và xử lý lũy đẳng (Idempotency) thành công trong `ReceptionConcurrencyAndSpikeLoadTest.php`.
   - *Khuyến nghị tương lai*: Khi hệ thống đạt >500 lượt quét/giây đồng thời tại các trung tâm tiệc cưới lớn, có thể bổ sung Redis cache lock (`atomic lock`) để giảm tải cho database.
2. **Lưu trữ ảnh dung lượng lớn (Cloud Object Storage)**:
   - *Hiện trạng*: Đang phục vụ local storage an toàn với giới hạn tải lên WebP.
   - *Khuyến nghị tương lai*: Kích hoạt S3 / Cloudflare R2 khi dung lượng media vượt quá ngưỡng lưu trữ máy chủ nội bộ.

---

## 3. KẾT LUẬN NGHIỆM THU

Hệ thống Mây Hẹn (`mayhen.vn`) đã vượt qua toàn bộ **653 bài kiểm thử tự động hóa (3,364 assertions)** với **0 lỗi tồn đọng ở mức P0/P1** (27 lỗi đã được phát hiện, vá lỗi tận gốc và kiểm thử hồi quy thành công trong `bug-register.csv`). 100% trong số 782 hạng mục chức năng vi mô (`MAY_HEN_FUNCTION_CATALOG.md`), 5,090 kịch bản kiểm thử (`MAY_HEN_QA_TEST_CASES.csv`) và 116 tệp ảnh chụp trực quan thực tế (`MAY_HEN_VISUAL_SCREEN_MATRIX.csv`) đã được đối chiếu truy vết toàn diện trong `feature-traceability.csv`. Toàn bộ 20 kịch bản E2E chuyên sâu (`DEEP-01` đến `DEEP-20`) đã được tự động hóa hoàn toàn trong `tests/Feature/CrossExplicitE2EDeepScenariosTest.php`. Mã nguồn tuân thủ 100% các nguyên tắc cốt lõi: Pure Vanilla CSS/JS với tiền tố `mayhen_*`, Zero External CSS/JS Frameworks, Native Fast Architecture, và bảo mật dữ liệu cá nhân theo Luật 91/2025/QH15. Sản phẩm đạt chuẩn thương mại cao cấp và sẵn sàng đi vào vận hành thực tế.

