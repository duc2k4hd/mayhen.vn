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
| **TASK-V1-03** | **Cá Nhân Hóa Lời Chào Theo Nhóm (Invitation Persona)** | Studio Editor, `template.blade.php`, `GuestController` | Cho phép soạn lời ngỏ riêng cho [Bạn Bè / Gia Đình / Đồng Nghiệp]. Khách mở link nhận đúng thông điệp theo nhóm. | **P1 (Ưu tiên số 1)** |
| **TASK-V1-04** | **Trợ Lý Viết Lời Ngỏ Tình Yêu Bằng AI (AI Story Writer)** | Studio V2, Service AI, Controller | Cặp đôi nhập từ khóa kỷ niệm, AI sinh ra 3 phương án lời ngỏ Quiet Luxury để chọn lựa. | **P1 (Ưu tiên số 2)** |
| **TASK-V1-05** | **Thẻ Chia Sẻ Zalo / Messenger Tự Động Khắc Tên** | Modal chia sẻ, Blade templates | Tự động sinh đoạn tin nhắn mời cưới trang trọng và lịch thiệp có khắc tên riêng khách mời. | **P1 (Ưu tiên số 3)** |
| **TASK-V1-06** | **Trợ Lý Khám Bệnh Thiệp Cưới (Visual Quality Doctor)** | Studio Editor JS, Canvas | Cảnh báo trước khi xuất bản nếu ảnh quá mờ, thiếu địa chỉ Google Maps hoặc chưa điền giờ đón khách. | **P2** |

---

### GIAI ĐOẠN 2: GROWTH PREMIUM — ĐIỀU PHỐI NGÀY CƯỚI & KỶ NIỆM SỐ (SPRINT 3 - 4: 4 TUẦN)
*Biến Mây Hẹn thành trợ lý điều phối sự kiện trong ngày cưới và kho lưu giữ kỷ niệm sau hôn lễ.*

| Task ID | Tính năng cốt lõi | Phạm vi mã nguồn | Bàn giao & Tiêu chí nghiệm thu | Độ ưu tiên |
|---|---|---|---|---|
| **TASK-V2-01** | **Mã QR Điểm Danh & Check-in Bàn Tiệc (QR Check-in)** | `CheckInController`, Camera API, Views | Lễ tân quét mã QR trên thiệp khách trong 1 giây để điểm danh và báo số bàn tiệc. | **P1** |
| **TASK-V2-02** | **Bảng Điều Khiển Ngày Cưới (Live Command Center)** | `WeddingLiveController`, Dashboard | Thống kê thời gian thực: số khách đã tới, số bàn đã đầy, khách cần hỗ trợ đặc biệt. | **P1** |
| **TASK-V2-03** | **Két Lưu Trữ Kỷ Niệm Số (Digital Memory Vault)** | Account Controller, Public Page | Thiệp cưới sau hôn lễ tự động chuyển thành trang kỷ niệm lưu trữ lời chúc, chữ ký và album ảnh trọn đời. | **P1** |
| **TASK-V2-04** | **Xuất Cuốn Sách Kỷ Yếu Lưu Bút PDF In Ấn** | Export Service, Headless PDF | Xuất toàn bộ lời chúc và nét chữ ký tay thành file PDF dàn trang tuyệt đẹp để in kỷ niệm. | **P2** |
| **TASK-V2-05** | **Live Guest Photo Drop (QR Thu Thập Ảnh)** | Upload Service, S3/R2 Storage | Khách tại tiệc quét mã QR để tải ảnh chụp tự nhiên lên album chung của cặp đôi. | **P2** |

---

### GIAI ĐOẠN 3: SIGNATURE EXPERIENCE — HỆ SINH THÁI ĐỐI TÁC B2B (SPRINT 5 - 6: 4 TUẦN)
*Mở rộng sang các đối tác Wedding Planner, Studio áo cưới và dịch vụ cao cấp.*

| Task ID | Tính năng cốt lõi | Phạm vi mã nguồn | Bàn giao & Tiêu chí nghiệm thu | Độ ưu tiên |
|---|---|---|---|---|
| **TASK-V3-01** | **Studio & Agency B2B Portal** | Multi-workspace, Role Agency | Các Studio áo cưới và Planner quản lý tập trung hàng chục đám cưới của khách hàng. | **P1** |
| **TASK-V3-02** | **Sơ Đồ Xếp Bàn Tiệc Trực Quan (Seating Planner Studio)** | Canvas Drag & Drop, Models | Kéo thả khách mời vào các bàn tiệc 10 người, xuất sơ đồ đón khách thông minh. | **P2** |
| **TASK-V3-03** | **Tên Miền Riêng Cho Đám Cưới (Custom Domain)** | DNS Routing, Cloudflare API | Hỗ trợ cấu hình tên miền riêng dạng `tuanlan.wedding` hoặc `tuanlan.mayhen.vn`. | **P2** |

---

## 3. CHỈ SỐ ĐO LƯỜNG THÀNH CÔNG (KPIs & SUCCESS METRICS)

1. **Tỷ lệ chuyển đổi nâng cấp gói (Free to Paid Conversion):** Tăng từ 4.5% lên **12.5%** nhờ các tính năng Persona, Trợ lý AI và Memory Vault.
2. **Tỷ lệ phản hồi xác nhận tham dự (RSVP Response Rate):** Đạt trên **75%** nhờ link cá nhân hóa và tin nhắn gửi Zalo 1-chạm.
3. **Thời gian hoàn thiện thiệp cưới trung bình:** Rút ngắn từ 45 phút xuống dưới **15 phút** nhờ bộ nhập nhanh Excel và Trợ lý AI.
4. **Tỷ lệ lỗi vận hành ngày cưới (Zero Incident Rate):** **0%** sự cố mất link hoặc gián đoạn thiệp cưới trong ngày tổ chức hôn lễ.

