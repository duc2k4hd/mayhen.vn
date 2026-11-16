---
id: CLI-CKIN-004
title: "Cơ chế điểm danh ngoại tuyến (Offline Check-in) khi sảnh tiệc mất sóng Wi-Fi/4G"
category: "Điểm danh Lễ tân & Màn chiếu"
summary: "Hướng dẫn kích hoạt và vận hành Gói Cứu Hộ Điểm Danh Ngoại Tuyến (Wedding Emergency Offline Pack): Tự động nạp trước toàn bộ danh bạ khách mời vào bộ nhớ máy, quét mã QR và tìm kiếm không cần Internet, cơ chế tự động đồng bộ (Auto-Sync) khi có mạng trở lại."
reader: "PER-CLI-02 (Đội lễ tân & MC)"
order: 14
updated_at: "2026-11-15"
tags: [mayhen, điểm-danh-lễ-tân, offline-pack, mất-mạng, auto-sync, emergency-resilience]
mapped_page: "CLI-CKIN-PAGE"
mapped_route: "checkin.portal"
risk_level: "CRITICAL"
required_role: "customer"
---

# Cơ chế điểm danh ngoại tuyến (Offline Check-in) khi sảnh tiệc mất sóng Wi-Fi/4G

> **Dành cho:** Đội ngũ lễ tân tiếp đón và Kỹ thuật viên sự kiện tại khách sạn / trung tâm tiệc cưới  
> **Khả năng hoạt động:** 100% không cần Internet trong suốt 4 tiếng đón khách  
> **Vị trí trong ứng dụng:** Cổng điểm danh `/diem-danh-tiec/{slug}` (Tự động kích hoạt)  
> **Dịch vụ cốt lõi:** `App\Services\EmergencyOfflinePackService` & API `checkin.sync_offline`

Nhiều trung tâm tiệc cưới lớn tại Việt Nam được xây dựng ở tầng hầm hoặc có kết cấu tường bê tông cách âm dày đặc, dẫn đến hiện tượng **nghẽn mạng 4G** hoặc **sóng Wi-Fi chập chờn** vào giờ cao điểm khi hàng trăm khách cùng tập trung. Nếu hệ thống điểm danh phụ thuộc 100% vào máy chủ trực tuyến, cổng tiếp tân sẽ bị tê liệt, khách mời phải xếp hàng dài chờ đợi.

Để đối phó với rủi ro P0 này, Mây Hẹn trang bị **Gói Cứu Hộ Ngoại Tuyến Khẩn Cấp (Emergency Offline Pack)** đạt chuẩn kiểm định sự kiện `TASK-VAL-03`. Cổng điểm danh có thể hoạt động hoàn toàn độc lập mà không cần một byte kết nối Internet nào.

![Cơ chế điểm danh ngoại tuyến Offline Check-in](/assets/client/docs/images/client/06-checkin-livewall/cli-ckin-004-step-1.png)
*Gói Cứu Hộ Ngoại Tuyến: Điểm danh không gián đoạn bằng IndexedDB cục bộ và tự động đồng bộ khi có mạng.*

---

## 1. Cơ chế Tải Trước Dữ Liệu (Pre-caching Snapshot)

Ngay khi lễ tân nhập mã PIN và mở Cổng điểm danh thành công lúc đầu giờ (khi còn mạng Wi-Fi hoặc 4G ngoài sảnh):

```
┌────────────────────────────────────────────────────────────────────────┐
│  BƯỚC 1: TẢI TRƯỚC SNAPSHOT (PRE-CACHE) KHI CÒN KẾT NỐI MẠNG          │
│                                                                        │
│  Máy chủ Mây Hẹn (Cloud) ───[Tải Snapshot JSON]───> Trình duyệt Lễ Tân │
│  - Danh sách 500 khách mời (Họ tên, SĐT, Mã QR Token, Suất ăn)       │
│  - Danh sách 50 bàn tiệc & Khu vực (Zones)                            │
│  - Lưu trữ an toàn trong IndexedDB của điện thoại / iPad               │
└────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼ Khi mất mạng
┌────────────────────────────────────────────────────────────────────────┐
│  BƯỚC 2: QUÉT QR & ĐIỂM DANH HOÀN TOÀN NGOẠI TUYẾN (OFFLINE MODE)      │
│                                                                        │
│  📷 Camera quét QR ➔ Tra cứu tức thời trong bộ nhớ máy (0.05 giây)     │
│  🏷️ Hiển thị số bàn cho khách bình thường                               │
│  💾 Lưu lượt điểm danh vào Hàng Đợi Ngoại Tuyến (Offline Queue)        │
└────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼ Khi có mạng lại
┌────────────────────────────────────────────────────────────────────────┐
│  BƯỚC 3: TỰ ĐỘNG ĐỒNG BỘ LÊN MÁY CHỦ (AUTO RE-SYNC)                    │
│                                                                        │
│  Gửi hàng loạt (Batch Request) lên máy chủ qua `checkin.sync_offline`  │
│  - Cập nhật số liệu sĩ số thời gian thực cho Bếp & MC                  │
│  - Bắn lời chúc dồn lên Màn LED sân khấu                               │
└────────────────────────────────────────────────────────────────────────┘
```

Hệ thống lưu trữ bản chụp dữ liệu đầy đủ bao gồm:
1. Danh sách khách mời, xưng hô, số suất ăn và mã định danh QR.
2. Vị trí bàn tiệc và phân vùng khu vực (Zone).
3. Các ghi chú quan trọng (Ăn chay, dị ứng, người lớn tuổi).

---

## 2. Giao diện Cảnh Báo Ngoại Tuyến (Offline Banner)

Khi sóng Wi-Fi hoặc 4G bị mất đột ngột:
- Cổng điểm danh lập tức chuyển sang chế độ **Offline Shield**.
- Trên đỉnh màn hình xuất hiện dải banner màu vàng hổ phách trang nhã:
  ```
  ⚡ CHẾ ĐỘ NGOẠI TUYẾN: Đang lưu vào bộ nhớ máy (Đã đón 14 khách ngoại tuyến)
  ```
- **Mọi thao tác vẫn diễn ra trơn tru:**
  - Camera vẫn quét mã QR bình thường (giải mã trực tiếp bằng Javascript trên trình duyệt).
  - Thanh tìm kiếm tên khách/SĐT vẫn lọc tức thì nhờ dữ liệu đã nạp sẵn trong bộ nhớ RAM.
  - Số bàn tiệc của khách vẫn hiển thị to rõ để lễ tân hướng dẫn khách vào bàn.

---

## 3. Quy trình Tự Động Đồng Bộ khi Sóng Mạng Phục Hồi (Auto-Sync)

Bạn không cần bấm bất kỳ nút nào để gửi dữ liệu thủ công:

1. Trình duyệt liên tục lắng nghe sự kiện `window.addEventListener('online')` và kiểm tra kết nối nền định kỳ mỗi `5 giây`.
2. Ngay khi điện thoại bắt lại được sóng Wi-Fi hoặc 4G:
   - Banner màu vàng chuyển thành màu xanh lá: *"Đang đồng bộ dữ liệu lên máy chủ..."*.
   - Hệ thống tự động gom toàn bộ các lượt điểm danh ngoại tuyến và gửi một gói dữ liệu an toàn đến endpoint:
     ```json
     POST /api/diem-danh-tiec/{slug}/dong-bo-ngoai-tuyen
     {
       "checkins": [
         {
           "guest_id": 105,
           "checked_in_at": "18:35:12",
           "attendees": 2,
           "table_id": 4
         },
         {
           "guest_id": 142,
           "checked_in_at": "18:36:04",
           "attendees": 1,
           "table_id": 8
         }
       ]
     }
     ```
3. Máy chủ Mây Hẹn sử dụng cơ chế xử lý giao dịch an toàn (Database Transaction & Idempotency Check), đảm bảo:
   - **Không bị trùng lặp:** Dù hai máy lễ tân cùng đồng bộ một khách, hệ thống chỉ ghi nhận 1 lần duy nhất theo mốc thời gian sớm nhất.
   - **Bảo toàn thời gian thực:** Thời gian điểm danh hiển thị trên báo cáo là thời gian thực tế lúc khách quét tại cửa, không phải thời gian lúc máy bắt lại được sóng.

---

## 4. Hướng dẫn Chủ Động Tải Gói Cứu Hộ trước ngày cưới (Emergency Pack Download)

Nếu biết trước hội trường tiệc cưới nằm ở vị trí sóng yếu (ví dụ phòng tiệc tầng hầm B1/B2):

1. Vào trang quản trị thiệp: **Quản Lý Thiệp** -> Chọn thiệp -> **Điểm Danh Lễ Tân**.
2. Nhấp nút **[📥 Tải Gói Cứu Hộ Ngoại Tuyến (Offline Snapshot)]**.
3. Hệ thống sẽ kết xuất một file dữ liệu mã hóa an toàn (`offline-pack-{slug}.json`) và một bản PDF danh bạ bàn tiệc dự phòng A4.
4. Gửi file này qua Zalo cho Lễ tân trưởng để lưu sẵn vào máy tính bảng đón khách. Khi đến hội trường, dù hoàn toàn không có sóng, lễ tân vẫn mở Cổng điểm danh và nạp dữ liệu hoạt động bình thường.

---

## 5. Danh mục kiểm tra phòng ngừa rủi ro mất mạng

> [!TIP]
> **Checklist 15 phút trước giờ mở cổng đón khách:**
> - [ ] Mở Cổng điểm danh khi đang có mạng 4G để hệ thống nạp sẵn danh bạ vào bộ nhớ.
> - [ ] Bật thử tính năng Diễn tập (Dry Run) quét thử 1 mã giả lập xem camera có hoạt động trơn tru không.
> - [ ] Chuẩn bị sẵn 1 cục sạc dự phòng cho điện thoại lễ tân (vì camera hoạt động liên tục sẽ tiêu hao pin nhanh hơn bình thường).
> - [ ] Giữ nguyên tab trình duyệt Cổng điểm danh, **tuyệt đối không xóa lịch sử duyệt web (Clear Cache)** trong lúc đang diễn ra tiệc cưới.
