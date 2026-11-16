---
id: ADM-COCK-004
title: "Kiểm duyệt ảnh Photo Drop và xử lý báo cáo vi phạm nội dung (Content Moderation)"
category: "Check-in Cockpit & Sơ đồ bàn"
summary: "Hướng dẫn quy trình kiểm duyệt ảnh trực tiếp từ quan khách (Photo Drop), thao tác duyệt hàng loạt, ẩn khẩn cấp trên màn chiếu Stage Live Wall và giải quyết báo cáo vi phạm nội dung theo Mục 17.3 PRD V3."
reader: "PER-ADM-01 (Điều hành tiệc cưới & Kiểm duyệt viên)"
order: 4
updated_at: "2026-11-15"
tags: [admin, content-moderation, photo-drop, abuse-report, live-wall-firewall]
mapped_page: "ADM-COCK-PAGE"
mapped_route: "admin.checkin.cockpit"
risk_level: "CRITICAL"
required_role: "manager"
---

# Kiểm duyệt ảnh Photo Drop và xử lý báo cáo vi phạm nội dung (Content Moderation)

> [!WARNING]
> **Phân quyền thực hiện:** Nhân viên có vai trò `Manager` hoặc `Super Admin`.  
> **Mức độ rủi ro:** `CRITICAL` | **Endpoints:** `/admin/guest-photos` & `/admin/moderation/reports`  
> **SLA Phản ứng Khẩn cấp:** Dưới **120 giây** kể từ khi nhận cảnh báo hoặc phát hiện nội dung nhạy cảm, nhằm ngăn chặn hình ảnh phản cảm bị phát tán lên Màn chiếu Sân khấu (Stage Live Wall).


![Giao diện quản lý Kiểm duyệt ảnh Photo Drop và xử lý báo cáo vi phạm nội dung (Content Moderation)](/assets/client/docs/images/admin/07-checkin-cockpit/adm-cock-004-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Vai trò của Tường lửa Kiểm duyệt trong Ngày cưới

Tính năng **Photo Drop** và **Sổ ký tên điện tử** của Mây Hẹn cho phép hàng trăm quan khách tại sảnh tiệc quét mã QR để gửi ảnh chụp khoảnh khắc và lời chúc chúc phúc tức thời. Tuy nhiên, trong không khí trang trọng và linh thiêng của đám cưới, rủi ro xuất hiện hình ảnh nhạy cảm, ảnh dìm hàng quá trớn, nội dung phản cảm hoặc quảng cáo rác (spam) luôn có thể xảy ra.

Hệ thống Mây Hẹn thiết lập **Tường lửa 2 lớp phòng vệ (2-Tier Moderation Firewall)**:
1. **Lớp 1: Bảng Kiểm duyệt Tường ảnh Quan khách (`/admin/guest-photos`):** Nơi kiểm tra, duyệt hiển thị từng ảnh hoặc duyệt hàng loạt trước khi ảnh được đồng bộ lên Stage Live Wall.
2. **Lớp 2: Trung tâm Xử lý Báo cáo Vi phạm Nội dung (`/admin/moderation/reports`):** Tiếp nhận khiếu nại, báo cáo vi phạm từ người dùng và xử lý dứt điểm theo quy trình chuẩn của Mục 17.3 PRD V3.

---

## 2. Quy trình Kiểm duyệt Ảnh Photo Drop (`/admin/guest-photos`)

### 2.1. Hai chế độ duyệt ảnh của Tiệc cưới
Tùy vào cấu hình tiệc cưới (`photo_drop.auto_approve`), hệ thống vận hành theo 2 cơ chế:
* **Chế độ Duyệt trước (Pre-moderation - Khuyến nghị cho tiệc lớn):** Mọi ảnh khách tải lên đều ở trạng thái `is_approved = false`. Ảnh chỉ xuất hiện trên Live Wall sau khi Điều phối viên bấm **[Duyệt]**.
* **Chế độ Duyệt sau (Auto-approve with Emergency Killswitch):** Ảnh tự động hiển thị ngay để tạo không khí sôi động, nhưng Điều phối viên luôn sẵn sàng bấm **[Ẩn khẩn cấp]** nếu phát hiện ảnh không phù hợp.

### 2.2. Thao tác Duyệt 1-chạm & Duyệt hàng loạt
1. Truy cập **Admin Portal** -> Chọn chuyên mục **[Ảnh quan khách (Photo Drop)]** (`/admin/guest-photos`).
2. Sử dụng bộ lọc tiệc cưới để xem đúng tiệc cưới đang diễn ra.
3. **Duyệt 1-chạm (Single Toggle):** Nhấp vào biểu tượng con mắt màu xanh lá hoặc nút **[Duyệt]** ngay trên thẻ ảnh. Trạng thái lập tức chuyển đổi qua AJAX mà không tải lại trang.
4. **Duyệt hàng loạt (Batch Approve):**
   - Tích chọn ô vuông trên các ảnh đạt tiêu chuẩn (ảnh cười tươi, ảnh chụp cặp đôi, ảnh không gian bàn tiệc).
   - Nhấp nút **[Duyệt các ảnh đã chọn]** ở thanh tác vụ trên cùng.
   - Endpoint `POST /admin/guest-photos/batch-approve` cập nhật tức thời hàng loạt ảnh vào luồng phát của tiệc cưới.

### 2.3. Xóa vĩnh viễn & Hủy file vật lý (Physical Destruction)
Đối với các ảnh có nội dung độc hại, xúc phạm hoặc vi phạm pháp luật:
1. Nhấp nút **[Xóa vĩnh viễn]** (biểu tượng thùng rác màu đỏ).
2. Hệ thống thực hiện 2 thao tác nguyên tử (Atomic Operations):
   - Xóa bản ghi trong bảng `guest_photos`.
   - Sử dụng `WeddingStorageService` xóa triệt để file ảnh gốc và file thumbnail trên đĩa cứng máy chủ (`Storage::disk('public')->delete(...)`).

---

## 3. Trung tâm Xử lý Báo cáo Vi phạm Nội dung (`/admin/moderation/reports`)

Khi quan khách hoặc người nhà cô dâu chú rể phát hiện nội dung có vấn đề và bấm nút **[Báo cáo vi phạm]** trên thiệp mời, hồ sơ sẽ được chuyển thẳng về Trung tâm Báo cáo Vi phạm (`/admin/moderation/reports`).

```
+---------------------------------------------------------------------------------------------------+
|  TRUNG TÂM XỬ LÝ BÁO CÁO VI PHẠM (ABUSE REPORTS CENTER)                                           |
+---------------------------------------------------------------------------------------------------+
|  Tổng tiếp nhận: 12  |  Chờ xử lý: 2 (CẦN XỬ LÝ GẤP)  |  Đã ẩn: 6  |  Đã xóa: 3  |  Bác bỏ: 1       |
+---------------------------------------------------------------------------------------------------+
|  MÃ # | THỜI GIAN | TIỆC CƯỚI             | PHÂN LOẠI      | LÝ DO BÁO CÁO       | THAO TÁC       |
|  #104 | 18:22     | Vũ Phong & Thảo Linh  | Lời chúc (#58) | Ngôn từ xúc phạm    | [ẨN] [XÓA] [BÁC]|
|  #105 | 18:25     | Hoàng Nam & Ngọc Trâm | Ảnh PhotoDrop  | Hình ảnh phản cảm   | [ẨN] [XÓA] [BÁC]|
+---------------------------------------------------------------------------------------------------+
```

### 3.1. Phân loại Lý do Báo cáo Vi phạm
Hệ thống phân loại tự động thành 5 nhóm lý do theo hằng số `ContentReport`:
1. `inappropriate`: Hình ảnh phản cảm, trang phục không phù hợp, cử chỉ khiếm nhã.
2. `spam`: Tin nhắn rác, link mời chào cờ bạc, đa cấp, số điện thoại lạ.
3. `harassment`: Lời lẽ bôi nhọ, châm biếm người yêu cũ, xúc phạm gia đình 2 bên.
4. `copyright`: Sử dụng ảnh cá nhân của người khác mà chưa có sự đồng ý.
5. `other`: Các nguyên nhân tế nhị khác do người báo cáo ghi rõ.

### 3.2. Ba Hành động Quyết định Chuẩn SOP
Tại mỗi hồ sơ báo cáo, Điều phối viên nhấp nút xử lý tương ứng:

| Hành động | Mã tác vụ (`action`) | Hệ quả Kỹ thuật & Nghiệp vụ |
| :--- | :--- | :--- |
| **Ẩn nội dung** | `hide` | Đưa nội dung về trạng thái `is_approved = false` hoặc `status = hidden`. Nội dung biến mất tức khắc khỏi thiệp cưới và Live Wall nhưng dữ liệu vẫn được lưu trữ phục vụ đối soát, giải quyết khiếu nại. |
| **Xóa vĩnh viễn** | `delete` | Xóa hoàn toàn bản ghi khỏi database và tiêu hủy file ảnh vật lý khỏi storage. Áp dụng cho nội dung đồi trụy hoặc vi phạm pháp luật nghiêm trọng. |
| **Bác bỏ báo cáo** | `dismiss` | Xác nhận báo cáo là nhầm lẫn hoặc ác ý (False Positive). Nội dung được khôi phục hiển thị bình thường, đóng hồ sơ báo cáo. |

---

## 4. Kịch bản Phản ứng Khẩn cấp: Tắt hiển thị trên Live Wall dưới 10 giây

Nếu trên Màn chiếu sân khấu (Stage Live Wall) bất ngờ xuất hiện hình ảnh hoặc lời chúc có vấn đề:

1. **Bước 1 (Tại bàn Điều phối):** Mở tab **Check-in Cockpit** hoặc **Thư viện Ảnh**, tìm nhanh bức ảnh vừa xuất hiện (nằm ở đầu danh sách vừa tải lên).
2. **Bước 2 (Ẩn tức thời):** Nhấp nút **[Ẩn ngay]**. Tín hiệu WebSocket / SSE hoặc vòng lặp Polling 2 giây của Live Wall sẽ lập tức kích hoạt hiệu ứng mờ dần (`Fade Out`) và rút bức ảnh khỏi luồng phát màn chiếu trong vòng **2 giây**.
3. **Bước 3 (Khóa tính năng tạm thời nếu bị spam dồn dập):** Nếu phát hiện dấu hiệu phá hoại (tải ảnh rác liên tục từ cùng một IP/thiết bị), Điều phối viên nhấp **[Tạm dừng Photo Drop]** trong cấu hình tiệc cưới để ngừng tiếp nhận ảnh mới, bảo vệ toàn bộ không gian buổi tiệc.

---

## 5. Tuân thủ Pháp lý & Bảo mật Cá nhân (Luật 91/2025/QH15)

Căn cứ theo Luật Bảo vệ Dữ liệu Cá nhân số 91/2025/QH15:
* Không công khai danh tính của người đã gửi báo cáo vi phạm cho bất kỳ bên thứ ba nào.
* Toàn bộ thao tác xử lý vi phạm của Admin được lưu vết vĩnh viễn vào bảng `content_reports` với các trường:
  - `resolver_id`: ID của Quản trị viên xử lý.
  - `action`: Hành động đã chọn (`hide`, `delete`, `dismiss`).
  - `notes`: Lý do và căn cứ quyết định của nhân viên.
  - `resolved_at`: Thời điểm giải quyết chính xác đến từng giây.
