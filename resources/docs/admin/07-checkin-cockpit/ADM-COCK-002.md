---
id: ADM-COCK-002
title: "Hỗ trợ xử lý sự cố nghẽn mạng sảnh tiệc và hướng dẫn lễ tân chuyển chế độ offline"
category: "Check-in Cockpit & Sơ đồ bàn"
summary: "Hướng dẫn chi tiết về Hỗ trợ xử lý sự cố nghẽn mạng sảnh tiệc và hướng dẫn lễ tân chuyển chế độ offline trên hệ thống Mây Hẹn."
reader: "PER-ADM-01 (Điều hành tiệc cưới)"
order: 6
updated_at: "2026-11-12"
tags: [mayhen, check-in-cockpit-&-sơ-đồ-bàn]
mapped_page: "ADM-COCK-PAGE"
mapped_route: "admin.checkin.cockpit"
risk_level: "CRITICAL"
required_role: "manager"
---

# ADM-SOP-COCK-002: Sổ tay Xử lý Sự cố Nghẽn mạng Sảnh tiệc & Cứu hộ Điểm danh Lễ tân Ngoại tuyến (Emergency Offline Pack)

> [!CRITICAL]
> **Phân quyền thực hiện:** Đội ngũ Kỹ thuật Trực tiệc (On-call Tech), Điều Hành Cưới (Wedding Ops Manager) hoặc Quản Trị Viên Tối Cao.  
> **Mức độ rủi ro:** `P0 - CRITICAL` (Sự cố ảnh hưởng trực tiếp đến giờ G đón khách của đám cưới).  
> **Vị trí chức năng:** `Admin Cockpit` -> `Wedding Ops Cockpit` (`/admin/checkin/cockpit`)  
> **Cam kết kiểm toán:** Mọi thao tác cấp cứu ngoại tuyến và tái đồng bộ dữ liệu bắt buộc lưu vết trong `audit_logs` với action `cockpit.emergency_offline_sync`.  

Vào giờ cao điểm đón khách (17h30 - 19h00), hàng trăm khách mời đồng loạt tập trung tại sảnh tiệc khách sạn hoặc trung tâm tiệc cưới. Tại nhiều địa điểm nằm ở tầng hầm hoặc góc khuất, sóng 4G/5G và mạng Wi-Fi nội bộ có thể bị nghẽn cục bộ (packet drop > 60%), khiến các lượt quét mã QR điểm danh bị quay vòng chờ (Spinner). Quy trình SOP này hướng dẫn đội ngũ kỹ thuật và điều hành kích hoạt **Gói Cứu Hộ Điểm Danh Ngoại Tuyến (Emergency Offline Pack)** để đảm bảo lễ tân đón khách thông suốt 100%, không để khách phải xếp hàng chờ đợi.

---

## 1. Dấu hiệu Nhận diện Sự cố Cần Can thiệp (P0 Alerts)
Nhân viên trực chiến cần kích hoạt quy trình cứu hộ ngay khi xuất hiện một trong các dấu hiệu sau:
- Cuộc gọi khẩn cấp từ Cô dâu/Chú rể hoặc Trưởng ban lễ tân báo *"Máy quét QR quay tròn, không báo tên khách và số bàn"*.
- Dashboard giám sát hệ thống ghi nhận tỷ lệ lỗi HTTP 504 Gateway Timeout hoặc request latency vượt quá 4,000ms tại endpoint `/api/diem-danh-tiec/{slug}/quet`.
- Trạng thái kết nối WebSocket của Cổng Check-in chuyển sang màu cam hoặc đỏ (Reconnecting...).

---

## 2. Quy trình 4 Bước Cứu hộ Ngoại tuyến Thần tốc

### Bước 1: Trấn an khách hàng & Hướng dẫn chuyển sang Chế độ Ngoại tuyến (Offline Mode)
1. Gọi điện thoại trực tiếp cho Trưởng ban lễ tân tiệc cưới, giữ bình tĩnh và hướng dẫn ngắn gọn:
   > *"Chào bạn, kỹ thuật Mây Hẹn đang đồng hành cùng bạn. Bạn hãy yên tâm, hệ thống đã chuẩn bị sẵn cơ chế ngoại tuyến hoàn toàn tự động. Hãy làm theo hướng dẫn sau trong 15 giây."*
2. Yêu cầu toàn bộ thiết bị lễ tân **GIỮ NGUYÊN TAB TRÌNH DUYỆT ĐANG MỞ**, tuyệt đối không đóng tab hay xóa lịch sử duyệt web.
3. Hướng dẫn lễ tân gạt công tắc **[Chế Độ Điểm Danh Ngoại Tuyến (Offline Mode)]** ở góc trên màn hình check-in:
   - Điểm báo trạng thái chuyển sang màu xanh ngọc: `🟢 NGOẠI TUYẾN SẴN SÀNG (IndexedDB Cached)`.
   - Toàn bộ danh sách 500+ khách mời và số bàn tiệc đã được nạp sẵn vào bộ nhớ cục bộ an toàn của máy.
   - Máy ảnh quét mã QR vẫn hoạt động bình thường, hiển thị tên khách và số bàn trong vòng 0.1 giây mà không cần mạng Internet!

### Bước 2: Kích hoạt Tải Gói Cứu Hộ Dự Phòng (Emergency Offline Pack) từ Admin
Trong trường hợp thiết bị của lễ tân bị tắt nguồn hoặc tải lại trang khi hoàn toàn mất mạng:
1. Nhân viên Admin truy cập `/admin/checkin/cockpit`, tìm đám cưới tương ứng theo tên hoặc mã thiệp.
2. Nhấp nút **[Tải Gói Khẩn Cấp (Emergency Pack)]**:
   - Hệ thống lập tức đóng gói tệp `mayhen_offline_manifest_{slug}.json` và bản in danh sách khách dạng PDF khổ A4 đã chia theo thứ tự bảng chữ cái A-Z và số bàn tiệc.
3. Gửi tệp này qua Zalo/Bluetooth hoặc in nhanh tại quầy lễ tân khách sạn để đội ngũ đối soát tức thì.

### Bước 3: Cơ chế Ghi nhận Điểm danh & Chống Trùng lặp Ngoại tuyến
- Khi quét QR ở chế độ ngoại tuyến, trình duyệt tự động lưu lượt check-in vào hàng đợi nội bộ `offline_checkin_queue` kèm dấu thời gian chính xác (ISO 8601 Timestamp) và mã định danh thiết bị (`device_fingerprint`).
- Khi 2 lễ tân cùng quét một khách mời đi kèm, thuật toán của Mây Hẹn sẽ tự động hợp nhất theo nguyên tắc **Bảo tồn Mốc Thời Gian Sớm Nhất (Earliest Timestamp Wins)**, không bao giờ ghi nhận sai số lượng khách tham dự thực tế.

### Bước 4: Tái Đồng bộ Dữ liệu Sau Giờ Đón Khách (Post-Event Deterministic Sync)
Khi tiệc cưới bắt đầu và mạng Wi-Fi/4G phục hồi ổn định (thường sau 19h15):
1. Nhắc nhở đội lễ tân mở lại kết nối mạng trên điện thoại.
2. Màn hình check-in sẽ tự động nhận diện kết nối `window.navigator.onLine = true` và hiển thị nút **[Đồng Bộ Hóa Dữ Liệu Ngoại Tuyến (Sync Now)]**.
3. Nhấp nút đồng bộ. Hệ thống sẽ gửi tuần tự toàn bộ hàng đợi check-in lên máy chủ qua giao thức xác thực an toàn `EmergencyOfflinePackService`.
4. Trên Admin Cockpit, nhân viên kiểm tra số liệu:
   - Tổng khách đã check-in thực tế.
   - Số khách phát sinh (Walk-in Guests) được gắn thẻ đặc biệt.
   - Thông báo *"Đồng bộ 100% dữ liệu ngoại tuyến thành công, không phát sinh xung đột"*.

---

## 3. Tiêu chí Nghiệm thu Hoàn thành Sự cố
- Đám cưới đón tiếp khách mượt mà, không xảy ra ùn ứ tại cửa sảnh tiệc.
- Toàn bộ dữ liệu điểm danh và bàn tiệc sau đồng bộ khớp 100% với thực tế khách ngồi trong phòng tiệc.
- Nhân viên lập biên bản sự cố ngắn (Incident Postmortem) ghi nhận: Thời điểm bắt đầu, thời điểm kết thúc, nguyên nhân nghẽn mạng từ nhà mạng/khách sạn và gửi báo cáo cho Quản Lý trong vòng 24 giờ.

---

## 4. Những điều Tuyệt đối Bị cấm
- **TUYỆT ĐỐI CẤM yêu cầu lễ tân xóa Cache / Xóa dữ liệu trình duyệt (Clear Cookies/Site Data)** khi đang xảy ra sự cố nghẽn mạng, vì hành động này sẽ làm mất hàng đợi check-in ngoại tuyến đang lưu tạm trong máy!
- **TUYỆT ĐỐI CẤM can thiệp thủ công bằng truy vấn cơ sở dữ liệu (Raw SQL UPDATE)** trong lúc lễ tân đang đón khách ngoại tuyến.
- **TUYỆT ĐỐI CẤM đổ lỗi cho khách hàng hoặc nhà hàng tiệc cưới.** Luôn giữ thái độ hòa nhã, chuyên nghiệp và đồng hành tận tâm cùng gia đình trong ngày trọng đại.
