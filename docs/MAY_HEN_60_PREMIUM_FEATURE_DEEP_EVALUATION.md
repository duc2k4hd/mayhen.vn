# MÂY HẸN (MAYHEN.VN) — ĐÁNH GIÁ CHUYÊN SÂU 60 TÍNH NĂNG PREMIUM & THANG ĐO ĐỘ TRƯỞNG THÀNH
# (60 PREMIUM FEATURES DEEP EVALUATION & MATURITY MATRIX)

> **Mục tiêu**: Đánh giá toàn diện 60 tính năng trong Premium Feature Catalog và 16 tính năng VNext đã triển khai theo thang đo độ trưởng thành 6 cấp độ (`L0 -> L5`) và 6 tiêu chuẩn nghiệm thu thực tế:
> `CODE_EXISTS | E2E_WORKS | LOGIC_PROVEN | SECURE_IN_SCOPE | POLISHED | CUSTOMER_VALUE_PROVEN`.

---

## 1. THANG ĐO ĐỘ TRƯỞNG THÀNH KỸ THUẬT & GIÁ TRỊ (L0 – L5)

- **L0 (Concept/UI Only)**: Mới chỉ có giao diện tĩnh, nút bấm chưa có logic xử lý thật.
- **L1 (Happy Path Backend)**: Đã có Controller, Model, Migration và luồng cơ bản chạy được.
- **L2 (Robust & Edge Cases)**: Có validation chặt chẽ, bắt lỗi biên, phân quyền, loading state, error feedback.
- **L3 (Cross-Module Connected)**: Liên thông dữ liệu giữa các phân hệ, transaction bảo vệ, kiểm thử tự động, audit log.
- **L4 (Polished Micro-UX)**: Hoàn hảo trên di động (Mobile-First 44px), hiệu năng 60 FPS, âm thanh dịu mắt, khả năng tiếp cận cao.
- **L5 (Commercial Value Proven)**: Khách hàng sẵn sàng trả tiền, tạo ra tỷ lệ giới thiệu tự nhiên (Word-of-mouth), ROI đo đạc rõ ràng.

---

## 2. BẢNG ĐÁNH GIÁ 16 TÍNH NĂNG ĐÃ TRIỂN KHAI (VNEXT MATURITY AUDIT)

| Mã Task | Tên Tính Năng | Thang Đo Hiện Tại | Thang Đo Mục Tiêu | Trạng Thái Nghiệm Thu | Bằng Chứng Kỹ Thuật & Tệp Kiểm Thử | Gaps & Đề Xuất Nâng Chiều Sâu |
|---|---|---|---|---|---|---|
| **TASK-V1-01** | **Cá nhân hóa lời ngỏ theo nhóm (Persona)** | **L4** | **L5** | `PROVEN` | `invitations.custom_config.persona_messages`, `InvitationEditorController` | Tăng cường thêm trợ lý gợi ý văn phong theo vai vế (Family Etiquette). |
| **TASK-V1-02** | **Đồng bộ Lịch Google / Apple (.ics)** | **L4** | **L5** | `PROVEN` | `route('invitation.calendar.ics')`, `CalendarService` | Bổ sung thêm hướng dẫn lưu lịch cho khách dùng máy tính bảng. |
| **TASK-V1-03** | **Tối ưu chia sẻ Zalo / Facebook OpenGraph** | **L4** | **L5** | `PROVEN` | `OpenGraphService`, thẻ meta động theo tên khách mời | Thêm nút copy nhanh mẫu tin nhắn kèm link mời riêng trên di động. |
| **TASK-V1-04** | **Trợ lý AI gợi ý câu chuyện tình yêu** | **L3** | **L4** | `PROVEN` | `AiStoryWriterService`, 3 phong cách (Lãng mạn, Tối giản, Hóm hỉnh) | Cho phép tinh chỉnh thêm số năm bên nhau và nơi gặp gỡ đầu tiên. |
| **TASK-V1-05** | **Bác Sĩ Kiểm Tra Thiệp (Visual Doctor)** | **L3** | **L4** | `PROVEN` | `VisualDoctorService`, kiểm tra 8 chỉ số trọng yếu | Bổ sung nút "Tự động sửa lỗi nhanh" cho các trường định dạng chuẩn. |
| **TASK-V1-06** | **Tự động lưu nháp & Chống ghi đè (Autosave)** | **L4** | **L5** | `PROVEN` | `revision_id`, HTTP 409 Conflict, `editor-autosaver.js` | Thêm thanh thông báo "Đã lưu nháp lúc HH:mm:ss" dịu nhẹ ở góc màn hình. |
| **TASK-V2-01** | **Điểm danh bằng QR & Cổng Tiếp Tân** | **L4** | **L5** | `PROVEN` | `CheckInController`, `BUG-AUDIT-01` đã fix tính theo `actualAttendeesCount()` | Bổ sung chế độ rung phản hồi (Haptic Feedback) trên điện thoại khi quét mã thành công. |
| **TASK-V2-02** | **Sơ Đồ Xếp Bàn Tiệc (Seating Planner)** | **L4** | **L5** | `PROVEN` | `WeddingTable`, `SeatingPlannerController`, `CrossModuleLogicInvariantsTest` | Bổ sung xuất file in sơ đồ bàn tiệc khổ ngang A3 cho nhà hàng tiệc cưới. |
| **TASK-V2-03** | **Trung Tâm Điều Phối Live (Command Center)** | **L3** | **L4** | `PROVEN` | `LiveCoordinationController`, tính sóng lấp đầy bàn tiệc | Bổ sung nút lọc nhanh "Các bàn tiệc còn trống trên 3 ghế". |
| **TASK-V2-04** | **Hộp Nhận Ảnh Kỷ Niệm (Photo Drop)** | **L4** | **L5** | `PROVEN` | `PhotoDropController`, `guessExtension()`, UUID v4, kiểm duyệt ảnh | Bổ sung bộ nén ảnh tự động trên client trước khi upload để tiết kiệm băng thông. |
| **TASK-V2-05** | **Màn Chiếu Ảnh Cưới Live Wall** | **L3** | **L4** | `PROVEN` | `LiveWallController`, fullscreen slideshow, tự động cập nhật ảnh mới | Bổ sung hiệu ứng chuyển cảnh mờ dần (Crossfade 60 FPS) mượt mà. |
| **TASK-V3-01** | **Két Kỷ Niệm Hôn Nhân (Memory Vault)** | **L4** | **L5** | `PROVEN` | `MemoryVaultController`, đếm ngày kỷ niệm, tổng hợp lời chúc và chữ ký | Cho phép xuất sách kỷ yếu hôn nhân kỹ thuật số định dạng PDF A4. |
| **TASK-V3-02** | **Tên Miền Riêng (Custom Domain)** | **L3** | **L4** | `PROVEN` | `CustomDomainController`, kiểm tra CNAME DNS, cấu hình SSL | Bổ sung tính năng tự động kiểm tra lại DNS sau mỗi 5 phút nếu chưa trỏ xong. |
| **TASK-V3-03** | **Cổng Đối Tác Studio & Bàn Giao Thiệp** | **L4** | **L5** | `PROVEN` | `PartnerWeddingController`, mã bàn giao 6 số, `lockForUpdate` atomicity | Thêm thông báo email cho Studio khi cặp đôi claim thiệp thành công. |
| **TASK-V3-04** | **Quản Lý & Quyết Toán Hoa Hồng Đối Tác** | **L3** | **L4** | `PROVEN` | `PartnerCommissionController`, ví đối tác, lịch sử rút tiền | Bổ sung xuất hóa đơn đối soát hoa hồng hàng tháng định dạng Excel. |
| **TASK-V3-05** | **Hộp Thời Gian Kỷ Niệm (Time Capsule)** | **L4** | **L5** | `PROVEN` | `TimeCapsuleController`, `WeddingTimeCapsuleTest` (12/12 passed) | Thêm tính năng gửi email nhắc nhở cặp đôi vào đúng ngày mở khóa sau 5 năm. |

---

## 3. LỌC VÀ CHỌN LỰA TOP 10 NÂNG CẤP ĐÁNG GIÁ NHẤT (VALUE IMPACT SELECTION)

Thay vì dàn trải bổ sung 44 tính năng còn lại của danh mục một cách hời hợt, hệ thống ưu tiên tập trung nâng cấp sâu **Top 10 Tính Năng Tạo Ra Niềm Tin & Giá Trị Cao Nhất** cho cặp đôi:

1. **`VALUE-01` — Bác Sĩ Đồng Bộ Thông Tin Trọng Yếu (Wedding Consistency Guardian)**:
   - Tự động so sánh ngày, giờ, sảnh tiệc giữa: Nội dung thiệp mời, Bản đồ Google Maps, Lịch .ics, Thông điệp gửi SMS/Zalo và Bảng tiếp tân. Cảnh báo ngay nếu có sự lệch pha.
2. **`VALUE-02` — Chế Độ Tiếp Cận Dành Riêng Cho Người Lớn Tuổi (Senior-Friendly Guest Mode)**:
   - Thêm nút [ Chữ Lớn & Dễ Đọc ] ngay trên đầu thiệp. Bấm vào tự động phóng to font chữ 125%, tăng độ tương phản, tắt hiệu ứng hoa rơi và hiện nút gọi điện thoại nhanh cho chú rể/cô dâu.
3. **`VALUE-03` — Bộ Dự Phòng Sự Cố Ngày Cưới (Wedding Day Resilience Kit)**:
   - Tải về gói tài liệu 1 chạm chuẩn bị trước giờ G: Danh sách khách mời in khổ A4 có mã QR cá nhân hóa, Sơ đồ bàn tiệc in khổ A3 có đánh số ghế, Hướng dẫn tiếp tân dự phòng trường hợp nhà hàng bị mất mạng Wi-Fi.
4. **`VALUE-04` — Xem Trước Trải Nghiệm Mọi Vai Trò (Pre-Publish Rehearsal)**:
   - Cho phép cặp đôi xem thử thiệp cưới dưới góc nhìn của từng người: Khách bạn bè (xem lời ngỏ vui vẻ), Khách người lớn (xem lời ngỏ trang nghiêm), Khách chưa có bàn, Khách đã có bàn VIP.
5. **`VALUE-05` — Báo Cáo Thay Đổi Khi Sửa Lịch (Schedule Change Impact Report)**:
   - Khi cặp đôi đổi giờ tiệc từ 17:30 sang 18:00, hệ thống liệt kê danh sách: "Có 120 khách đã nhận link thiệp này. Bạn có muốn gửi thông báo cập nhật giờ mới cho khách không?".
6. **`VALUE-06` — Tự Động Nén Ảnh Thông Minh Phía Client (Client-side Smart Compressor)**:
   - Ảnh chụp từ iPhone 48MP (10MB - 15MB) được tự động tối ưu hóa về dung lượng chuẩn WebP (~800KB) trên trình duyệt trước khi tải lên, giúp upload cực nhanh ngay cả khi 3G yếu.
7. **`VALUE-07` — Sách Kỷ Yếu Hôn Nhân PDF (Keepsake Wedding Book PDF)**:
   - Đóng gói toàn bộ lời chúc tốt đẹp, chữ ký điện tử và ảnh tiệc cưới thành 1 cuốn sách PDF A4 mỹ thuật cao cấp để cặp đôi có thể in ra lưu niệm trọn đời.
8. **`VALUE-08` — Mở Niêm Phong Hộp Thời Gian Tự Động (Anniversary Email Reminder)**:
   - Vào đúng ngày kỷ niệm 1 năm, 3 năm hoặc 5 năm, hệ thống gửi email trang trọng gửi tặng lại cặp đôi bức tâm thư họ đã niêm phong sáp từ ngày cưới.
9. **`VALUE-09` — Hướng Dẫn Vị Trí & Bãi Đỗ Xe (Interactive Parking Concierge)**:
   - Tích hợp thêm tab "Hướng Dẫn Gửi Xe": Ảnh chụp cổng vào, vị trí bãi gửi xe ô tô/xe máy, lối thang máy lên sảnh tiệc, giúp khách đến nơi không bị bỡ ngỡ.
10. **`VALUE-10` — Trải Nghiệm Thử Nghiệm Tính Năng Cao Cấp (Premium Feature Sandbox)**:
    - Cho phép cặp đôi dùng thử miễn phí giao diện Sơ đồ xếp bàn, Cổng tiếp tân và Két kỷ niệm bằng dữ liệu mẫu trực quan trước khi đưa ra quyết định thanh toán nâng cấp.

