---
id: ADM-DASH-003
title: "Giám sát trạm quan sát chi phí & chất lượng AI (AI Cost & Quality Observatory)"
category: "Trung tâm Điều hành & Giám sát"
summary: "Hướng dẫn Ban Giám Đốc và Quản trị viên vận hành Đài Quan Sát Chi Phí & Chất Lượng AI: đo lường ngân sách token VND, độ trễ phản hồi (ms), tỷ lệ kích hoạt dự phòng (Fallback Rate), bộ lọc an toàn thương hiệu (Brand Safety) và Sandbox mô phỏng prompt."
reader: "PER-ADM-03 (Ban Giám Đốc / Giám Đốc Công Nghệ)"
order: 3
updated_at: "2026-11-15"
tags: [admin, ai-observatory, token-cost, brand-safety, heuristic-fallback]
mapped_page: "ADM-DASH-PAGE"
mapped_route: "admin.dashboard"
risk_level: "HIGH"
required_role: "manager"
---

# Giám sát trạm quan sát chi phí & chất lượng AI (AI Cost & Quality Observatory)

> [!NOTE]
> **Phân quyền thực hiện:** Nhân viên có vai trò `Manager` hoặc `Super Admin`.  
> **Mức độ rủi ro:** `HIGH` | **Endpoint:** `GET /admin/ai-observatory` (`admin.ai-observatory.index`)  
> **Service lõi:** `App\Services\AiQualityAndCostObservatoryService`  
> **Mô hình tích hợp:** Google Gemini 1.5 Flash, Gemini 1.5 Pro & Động cơ viết văn Heuristic dự phòng cục bộ (Zero-cost Offline Fallback).

---

## 1. Mục đích Vận hành của Đài Quan sát AI (AI Observatory)

Trong nền tảng Mây Hẹn, trí tuệ nhân tạo (AI) được ứng dụng sâu rộng trong các tác vụ:
1. **AI Story Writer:** Soạn thảo câu chuyện tình yêu và lời tựa thiệp cưới lãng mạn.
2. **AI Wording Assistant:** Viết lời mời thiệp cưới trang trọng cho từng nhóm đối tượng (Họ Hàng, Bạn Bè, Đồng Nghiệp, Khách VIP).
3. **Paper Invitation Parser:** Tự động quét và đọc hiểu thiệp cưới giấy truyền thống để tạo bản sao online.
4. **Wedding Concierge QA:** Trả lời tự động câu hỏi thường gặp của khách mời về dresscode, bãi đỗ xe và lịch trình.

Để đảm bảo AI vận hành **tiết kiệm chi phí, phản hồi siêu tốc, chất lượng văn phong Quiet Luxury và không bao giờ gặp sự cố gián đoạn**, Mây Hẹn thiết lập **Đài Quan Sát Chi Phí & Chất Lượng AI** (`/admin/ai-observatory`).

---

## 2. Thống kê 8 Chỉ số Sức khỏe AI Toàn cảnh (Observatory Metrics)

Tại đầu trang Đài quan sát, hệ thống tổng hợp báo cáo trong chu kỳ **7 ngày, 30 ngày hoặc 90 ngày**:

```
+---------------------------------------------------------------------------------------------------+
|  ĐÀI QUAN SÁT CHI PHÍ & CHẤT LƯỢNG AI (CHỦ KỲ: 30 NGÀY QUA)                                       |
+---------------------------------------------------------------------------------------------------+
|  TỔNG YÊU CẦU AI   | TỔNG CHI PHÍ TOKEN  | TỔNG TOKEN TIÊU THỤ | ĐỘ TRỄ TRUNG BÌNH                |
|  14.280 lượt       | 285.600 VNĐ         | 1.845.200 tokens    | 620 ms (Rất nhanh)               |
+--------------------+---------------------+---------------------+----------------------------------+
|  TỶ LỆ THÀNH CÔNG  | TỶ LỆ DỰ PHÒNG      | TỶ LỆ CHẤP NHẬN NỘI DUNG | SỰ CỐ AN TOÀN NỘI DUNG      |
|  99.4%             | 0.6% (Heuristic)    | 88.2% (Accepted)    | 0 vụ vi phạm (An toàn 100%)      |
+---------------------------------------------------------------------------------------------------+
```

### Chi tiết ý nghĩa từng chỉ số:

| Chỉ số | Cách tính toán | Chuẩn mực Vận hành An toàn |
| :--- | :--- | :--- |
| **Tổng chi phí Token (VND)** | Tính dựa trên biểu phí API Google Gemini quy đổi ra VNĐ (`sum('cost_vnd')`). | Trung bình mỗi thiệp cưới chỉ tốn khoảng **20 - 50 VNĐ** chi phí AI, đảm bảo biên lợi nhuận ròng trên 95%. |
| **Độ trễ trung bình (`avg_latency_ms`)** | Thời gian phản hồi từ lúc gửi prompt đến khi nhận văn bản hoàn chỉnh. | Ngưỡng an toàn: **< 1.000 ms**. Nếu vượt quá 2.500 ms, hệ thống tự động gắn cờ cảnh báo mạng API. |
| **Tỷ lệ Dự phòng (`fallback_rate`)** | Tỷ lệ phải kích hoạt Động cơ Heuristic cục bộ khi API bên ngoài bị lỗi hoặc timeout. | Ngưỡng an toàn: **< 2%**. Đảm bảo người dùng không bao giờ thấy màn hình trắng trơn hay thông báo lỗi. |
| **Tỷ lệ Chấp nhận (`acceptance_rate`)** | Tỷ lệ cặp đôi bấm **[Áp dụng vào thiệp]** mà không bấm xóa hay viết lại. | Mục tiêu: **> 85%**. Phản ánh chất lượng văn phong của prompt có thực sự chạm vào cảm xúc người dùng hay không. |
| **Sự cố An toàn (`safety_incidents`)** | Số lần prompt hoặc câu trả lời bị hệ thống chặn lại vì vi phạm tiêu chuẩn thương hiệu. | Mục tiêu: **0 vụ**. Ngăn chặn tuyệt đối từ ngữ thù địch, mê tín dị đoan, hoặc xưng hô không phù hợp ngày cưới. |

---

## 3. Tường lửa An toàn Thương hiệu (Brand Safety Guardrails)

Trước khi gửi bất kỳ prompt nào đến mô hình ngôn ngữ lớn (LLM), lớp bảo vệ `checkSafetyGuardrails` sẽ kiểm tra văn bản:

```
[ BẢN ĐỒ KIỂM DUYỆT AN TOÀN THƯƠNG HIỆU MÂY HẸN ]

               +---> [ 1. Danh sách từ cấm ] ---> Từ ngữ tiêu cực, chia tay, tang tóc, ly hôn
               |
[ PROMPT VÀO ] +---> [ 2. Nhận diện nhạy cảm ] ---> Quảng cáo cờ bạc, đa cấp, số điện thoại lạ
               |
               +---> [ 3. Tinh chỉnh phong cách ] ---> Ép buộc giọng văn: Lãng mạn, thanh tao, ấm áp
                                  |
                                  v
                        [ DUYỆT / CHẶN AN TOÀN ]
```

* **Chặn tức thời (`blocked_safety`):** Nếu phát hiện nội dung độc hại hoặc quấy rối, hệ thống từ chối gọi API, lưu bản ghi vào `ai_usage_logs` với cờ `blocked_safety` và trả về thông báo văn minh cho người dùng.

---

## 4. Cơ chế Cứu hộ 3 Tầng: Chống Sập Tuyệt Đối (3-Tier Resilience)

Để ngày vui của khách hàng không bị phụ thuộc hoàn toàn vào đường truyền quốc tế:
1. **Tầng 1 (Primary):** Google Gemini 1.5 Flash (Phản hồi siêu tốc 500ms, chi phí tối ưu).
2. **Tầng 2 (Secondary):** Tự động chuyển vùng API Key hoặc nâng lên Gemini 1.5 Pro nếu prompt đòi hỏi độ sâu cảm xúc cao.
3. **Tầng 3 (Local Fallback - Zero Cost):** Nếu mất mạng quốc tế hoặc cổng API trả mã 500/503: Hệ thống lập tức kích hoạt thư viện **500 mẫu văn học cưới kinh điển viết sẵn bằng tiếng Việt** lưu sẵn trong mã nguồn Laravel. Khách hàng vẫn nhận được lời tựa thiệp tuyệt đẹp trong **10 mili-giây** mà không hề nhận ra mạng bên ngoài đang gặp sự cố.

---

## 5. Sandbox Thử nghiệm & Mô phỏng Prompt (`/admin/ai-observatory/simulate`)

Tại tab **[Phòng Thí Nghiệm AI Sandbox]**, các chuyên gia Prompt Engineer và Quản lý sản phẩm có thể thử nghiệm câu lệnh mới:

1. **Chọn Tính năng:** `story_generator` (Câu chuyện tình yêu), `rsvp_wording` (Lời mời cưới) hoặc `concierge_qa` (Hỏi đáp khách mời).
2. **Chọn Mô hình:** `gemini-1.5-flash`, `gemini-1.5-pro` hoặc cưỡng chế chạy thử nghiệm `heuristic_fallback`.
3. **Nhập Prompt thử nghiệm:** Điền các tình huống hóc búa (ví dụ: *Cô dâu chú rể quen nhau qua game online nhưng muốn viết lời mời gửi cho người lớn tuổi trong dòng họ thật trang trọng*).
4. **Nhấp [Chạy Thử Nghiệm]:** Hệ thống hiển thị kết quả văn phong, tổng số token tiêu thụ, chi phí quy đổi VNĐ chính xác đến từng đồng và kết quả kiểm duyệt Brand Safety.

---

## 6. Kiểm soát Ngân sách & Cảnh báo Chi phí

* **Hạn mức Ngân sách Tháng (Budget Cap):** Được cấu hình an toàn ở mức **2.000.000 VNĐ / tháng**.
* Nếu chi phí vượt quá **80% hạn mức**, hệ thống tự động gửi thông báo Telegram cho Ban Giám Đốc và ưu tiên điều phối các tác vụ đơn giản về động cơ Heuristic để đảm bảo an toàn tài chính.
