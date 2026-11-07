# MÂY HẸN (MAYHEN.VN) — LỘ TRÌNH PHÁT TRIỂN THẾ HỆ MỚI (PREMIUM VNEXT ROADMAP)

> **Mục tiêu:** Định hướng phát triển Mây Hẹn từ nền tảng thiệp cưới trực tuyến thành **Nền tảng Trải nghiệm Cưới, Điều Phối Sự Kiện & Lưu Giữ Kỷ Niệm Toàn Diện**.  
> **Thời điểm xác lập:** 07/11/2026.  
> **Điều kiện tiên quyết:** Toàn bộ các lỗ hổng bảo mật P0/P1 đã được xử lý triệt để và kiểm chứng qua 182/182 tests passed.

---

## 1. BA KỊCH BẢN ĐẦU TƯ CHIẾN LƯỢC (INVESTMENT SCENARIOS)

```mermaid
flowchart TD
    subgraph Scenario1["KỊCH BẢN 1: LEAN PREMIUM (4 Tuần)"]
        S1_1["Hoàn thiện Bảo Mật & Ổn Định P0"]
        S1_2["Visual Quality Doctor & Thẩm Mỹ"]
        S1_3["Invitation Persona & Chia Sẻ Zalo"]
        S1_4["Gỡ Logo Mây Hẹn & Trợ Lý Lời Ngỏ AI"]
    end

    subgraph Scenario2["KỊCH BẢN 2: GROWTH PREMIUM (8 Tuần)"]
        S2_1["Bảng Điều Khiển Ngày Cưới Realtime"]
        S2_2["QR Check-in & Tra Cứu Bàn Tiệc"]
        S2_3["Digital Memory Vault & Xuất PDF Sổ Kỷ Niệm"]
        S2_4["Live Guest Photo Drop (Thu Thập Ảnh)"]
    end

    subgraph Scenario3["KỊCH BẢN 3: SIGNATURE EXPERIENCE (12 Tuần)"]
        S3_1["Seating Planner Studio Kéo Thả"]
        S3_2["Agency / Wedding Planner Portal B2B"]
        S3_3["Tên Miền Riêng Cho Đám Cưới"]
        S3_4["Hộp Thời Gian & Bức Tường Trình Chiếu"]
    end

    Scenario1 --> Scenario2 --> Scenario3
```

---

## 2. PHÂN KỲ TRIỂN KHAI CHI TIẾT THEO VERTICAL SLICES

### GIAI ĐOẠN 1: LEAN PREMIUM — KHẲNG ĐỊNH ĐẲNG CẤP (SPRINT 1 - 2: 4 TUẦN)
*Tập trung vào những tính năng tạo cảm giác "Đáng tiền" ngay khi tạo thiệp và gửi đi.*

| Task ID | Tính năng cốt lõi | Phạm vi mã nguồn | Bàn giao & Tiêu chí nghiệm thu (Acceptance Criteria) | Độ ưu tiên |
|---|---|---|---|---|
| **TASK-V1-01** | **Bảo vệ Trạng Thái Thiệp (Draft / Expired / Locked)** | `InvitationEditorController`, `GuestController`, Views | 100% chặn truy cập công khai và các API tương tác khi thiệp bị khóa hoặc chưa xuất bản. Đã có 6 test cases bao phủ. | **P0 (ĐÃ HOÀN TẤT)** |
| **TASK-V1-02** | **Xác thực Webhook & Deduplication Transaction ID** | `CheckoutController`, Migration `orders` | Lưu `provider_transaction_id` unique, chống replay attack và trùng lặp giao dịch. Đã có test case bao phủ. | **P0 (ĐÃ HOÀN TẤT)** |
| **TASK-V1-03** | **Cá Nhân Hóa Lời Chào Theo Nhóm (Invitation Persona)** | Cài đặt thiệp, `InvitationEditorController`, 4 Mẫu Thiệp Blade | Cặp đôi tùy biến lời ngỏ riêng theo nhóm khách [Bạn Bè, Gia Đình, Đồng Nghiệp, Khách VIP]. Khách mở link nhận đúng thông điệp theo nhóm. DOM client cách ly an toàn, chống leak phân loại khách. 7/7 test cases bao phủ. | **P1 (ĐÃ HOÀN TẤT)** |
| **TASK-V1-04** | **Trợ Lý Viết Lời Ngỏ Tình Yêu Bằng AI (AI Story Writer)** | `AiStoryWriterService`, `InvitationEditorController`, `editor.js` | Cặp đôi nhập từ khóa kỷ niệm, AI sinh ra 3 phương án lời ngỏ Quiet Luxury (Lãng mạn, Tối giản, Trẻ trung), áp dụng 1-chạm và Live Sync tức thì. | **P1 (ĐÃ HOÀN TẤT)** |
| **TASK-V1-05** | **Thẻ Chia Sẻ Zalo / Messenger Tự Động Khắc Tên** | Modal chia sẻ `guests.blade.php`, `guests.js` | Modal bưu thiếp mini khắc tên khách mời, 3 phong cách lời mời (Trang trọng, Thân mật, Lịch thiệp), tự động gợi ý theo nhóm, sao chép 1-chạm và mở Zalo. | **P1 (ĐÃ HOÀN TẤT)** |
| **TASK-V1-06** | **Trợ Lý Khám Bệnh Thiệp Cưới (Visual Quality Doctor)** | Studio Topbar & Modal, `editor.js`, `editor-v2.css` | Tự động chấm điểm độ hoàn thiện (thang 100đ), chẩn đoán real-time tên cặp đôi, ngày cưới, địa chỉ tiệc, bản đồ Maps, lời ngỏ và VietQR kèm nút nhảy thẳng tới bước cần sửa. | **P2 (ĐÃ HOÀN TẤT)** |

---

### GIAI ĐOẠN 2: GROWTH PREMIUM — ĐIỀU PHỐI NGÀY CƯỚI & KỶ NIỆM SỐ (SPRINT 3 - 4: 4 TUẦN)
*Biến Mây Hẹn thành trợ lý điều phối sự kiện trong ngày cưới và kho lưu giữ kỷ niệm sau hôn lễ.*

| Task ID | Tính năng cốt lõi | Phạm vi mã nguồn | Bàn giao & Tiêu chí nghiệm thu | Độ ưu tiên |
|---|---|---|---|---|
| **TASK-V2-01** | **Mã QR Điểm Danh & Check-in Bàn Tiệc (QR Check-in & Table Finder)** | `CheckInController`, `QrCodeService`, Views, JS | Lễ tân quét mã QR trên thiệp khách trong 1 giây để điểm danh và báo số bàn tiệc. Khách xem vị trí bàn và mã check-in qua VIP Pass widget trên thiệp. Đã bao phủ 10 test cases. | **P1 (ĐÃ HOÀN TẤT)** |
| **TASK-V2-02** | **Bảng Điều Khiển Ngày Cưới Realtime (Live Command Center)** | `LiveCoordinationController`, `live-coordination.js/css` | Thống kê sĩ số realtime (Tổng khách, RSVP, Đã tới, Chưa tới), Sóng lấp đầy bàn tiệc (Table Seating Wave) và dòng sự kiện đón khách trực tiếp qua AJAX Polling mỗi 8 giây. Đã kiểm thử 100%. | **P1 (ĐÃ HOÀN TẤT)** |
| **TASK-V2-03** | **Két Lưu Trữ Kỷ Niệm Số (Digital Memory Vault)** | `MemoryVaultController`, Public Page, Settings | Thiệp cưới sau hôn lễ tự động chuyển thành bảo tàng kỷ niệm lưu giữ trọn đời: Bộ đếm ngày kỷ niệm, Tâm thư tri ân từ đôi uyên ương, Sổ lưu bút và thống kê hạnh phúc. Đã bao phủ 6 test cases. | **P1 (ĐÃ HOÀN TẤT)** |
| **TASK-V2-04** | **Xuất Cuốn Sách Kỷ Yếu Lưu Bút PDF In Ấn (Keepsake Book)** | `MemoryVaultController`, `keepsake-book.blade.php/css` | Dàn trang sách Editorial chuẩn A4 (Bìa mạ vàng, Trang mở đầu tri ân, các trang lời chúc kèm hoa văn, Trang bìa sau). Tối ưu in ấn & xuất PDF bằng `@media print` phân trang hoàn hảo. | **P2 (ĐÃ HOÀN TẤT)** |
| **TASK-V2-05** | **Live Guest Photo Drop (QR Thu Thập Ảnh)** | `PhotoDropController`, `guest_photos` table, Views, JS/CSS | Khách tại tiệc quét mã QR đặt tại bàn tiệc để tải ảnh chụp tự nhiên (hỗ trợ JPG/PNG/WebP/HEIC tối đa 12MB, thanh tiến độ %). Cặp đôi quản lý album, duyệt/ẩn ảnh, in thẻ QR standee đặt bàn và tải file ZIP toàn bộ ảnh gốc. Tích hợp trực tiếp vào Két Kỷ Niệm Số. Đã bao phủ 10 test cases. | **P2 (ĐÃ HOÀN TẤT)** |

---

### GIAI ĐOẠN 3: SIGNATURE EXPERIENCE — HỆ SINH THÁI ĐỐI TÁC B2B (SPRINT 5 - 6: 4 TUẦN)
*Mở rộng sang các đối tác Wedding Planner, Studio áo cưới và dịch vụ cao cấp.*

| Task ID | Tính năng cốt lõi | Phạm vi mã nguồn | Bàn giao & Tiêu chí nghiệm thu | Độ ưu tiên |
|---|---|---|---|---|
| **TASK-V3-01** | **Studio & Agency B2B Portal** | `Partner*` Controllers, Models, Views, Migrations | Cổng B2B quản trị tập trung danh sách thiệp cưới của khách hàng cho Studio/Planner. Sinh mã bàn giao (Handover Code 6 ký tự) để cặp đôi claim thiệp về tài khoản cá nhân; hệ thống hoa hồng chiết khấu tự động (20-30%); Co-branding thương hiệu Studio độc quyền ở chân thiệp cưới; và middleware bảo vệ phân quyền. Đã bao phủ 11 test cases. | **P1 (ĐÃ HOÀN TẤT)** |
| **TASK-V3-02** | **Sơ Đồ Xếp Bàn Tiệc Trực Quan (Seating Planner Studio)** | `SeatingPlannerController`, `WeddingTable`, Canvas JS/CSS | Studio trực quan hóa sơ đồ sảnh tiệc, canvas kéo thả bàn tiệc và khách mời; tự động tính toán sức chứa ghế ngồi vs thực tế; cảnh báo bàn quá tải; hỗ trợ bàn tròn, bàn dài, bàn VIP; xuất sơ đồ sảnh cưới & danh sách đón khách chuẩn A4/A3 cho nhà hàng/lễ tân; và liên kết chặt chẽ với cổng Check-in. Đã bao phủ 10 test cases. | **P2 (ĐÃ HOÀN TẤT)** |
| **TASK-V3-03** | **Tên Miền Riêng Cho Đám Cưới (Custom Domain)** | DNS Routing, Cloudflare API | Hỗ trợ cấu hình tên miền riêng dạng `tuanlan.wedding` hoặc `tuanlan.mayhen.vn`. | **P2** |

---

## 3. CHỈ SỐ ĐO LƯỜNG THÀNH CÔNG (KPIs & SUCCESS METRICS)

1. **Tỷ lệ chuyển đổi nâng cấp gói (Free to Paid Conversion):** Tăng từ 4.5% lên **12.5%** nhờ các tính năng Persona, Trợ lý AI và Memory Vault.
2. **Tỷ lệ phản hồi xác nhận tham dự (RSVP Response Rate):** Đạt trên **75%** nhờ link cá nhân hóa và tin nhắn gửi Zalo 1-chạm.
3. **Thời gian hoàn thiện thiệp cưới trung bình:** Rút ngắn từ 45 phút xuống dưới **15 phút** nhờ bộ nhập nhanh Excel và Trợ lý AI.
4. **Tỷ lệ lỗi vận hành ngày cưới (Zero Incident Rate):** **0%** sự cố mất link hoặc gián đoạn thiệp cưới trong ngày tổ chức hôn lễ.

