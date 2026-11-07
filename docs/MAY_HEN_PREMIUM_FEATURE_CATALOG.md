# MÂY HẸN (MAYHEN.VN) — DANH MỤC 60 TÍNH NĂNG CAO CẤP THẾ HỆ MỚI (PREMIUM VNEXT FEATURE CATALOG)

> **Chiến lược:** Chuyển đổi định vị từ "Website tạo thiệp cưới trực tuyến" thành **"Nền Tảng Trải Nghiệm Cưới Toàn Diện, Điều Phối Sự Kiện & Lưu Giữ Kỷ Niệm Cao Cấp"**.  
> **Thẩm mỹ chủ đạo:** Quiet Luxury, Tối giản, Chân thành, Riêng tư tuyệt đối.  
> **Khách hàng mục tiêu:** Các cặp đôi hiện đại có gu thẩm mỹ cao, sẵn sàng chi trả từ 199.000đ – 990.000đ để có một ngày cưới chỉn chu, không lỗi lầm và đáng nhớ.

---

## 1. PHÂN TÍCH ĐỐI THỦ CẠNH TRANH THỰC TẾ

| Tiêu chí | Biihappy / Thiệp Cưới Online (VN) | Paperless Post / Greenvelope (Global) | Joy / WithJoy / Zola (Global) | **Mây Hẹn Premium (VNext)** |
|---|---|---|---|---|
| **Phong cách thiết kế** | Đại trà, nhiều mẫu sặc sỡ, bố cục LadiPage kéo thả | Thanh lịch, typography phong bì số | Đa năng, hơi nặng nề phong cách phương Tây | **Quiet Luxury, Editorial, Thư Pháp thuần Việt, Motion tinh tế** |
| **Công nghệ thiệp** | LadiPage/Vue nhúng CDN nặng | Web Canvas, Vector SVG | React App SPA cồng kềnh | **Native HTML5/CSS thuần/ES Modules, Zero Framework, 60 FPS** |
| **Bảo mật & Riêng tư** | Yếu, không mã hóa link cá nhân, lộ danh sách khách | Cao, link token riêng | Cao, quản lý theo tài khoản | **Luật 91/2025/QH15, Mã hóa Token 32 bytes, Chống rò rỉ danh sách** |
| **Trải nghiệm sau cưới** | Hầu như không có | Lưu niệm đơn giản | Có album chia sẻ | **Digital Memory Vault, Sổ lưu bút in được, Video Wishes** |
| **Điều phối sự kiện** | Không có | Không có | Có Seating chart phức tạp | **Wedding Day Command Center, Check-in QR bàn tiệc, Trực quan** |

---

## 2. DANH MỤC 60 TÍNH NĂNG CAO CẤP CHI TIẾT

### NHÓM A — TRẢI NGHIỆM THIỆP CÓ CẢM XÚC & CÁ NHÂN HÓA SÂU (10 TÍNH NĂNG)

#### `IDEA-A01`: Cá Nhân Hóa Lời Chào & Thông Điệp Theo Nhóm Khách (Invitation Persona)
- **User Story:** Là cô dâu chú rể, tôi muốn gửi thiệp cho bạn bè thân thiết với giọng văn vui vẻ gần gũi, nhưng gửi cho đối tác/người lớn tuổi với giọng văn trang trọng, trên cùng một mẫu thiệp.
- **Lý do khách trả tiền:** Cực kỳ tinh tế, không làm mất lòng người lớn và không gượng gạo với bạn thân.
- **Luồng UX/UI:** Trong Studio, mục "Lời Ngỏ" cho phép bật "Thông điệp theo nhóm". Cặp đôi soạn 3 tab: [Bạn Thân | Gia Đình | Đồng Nghiệp]. Khi tạo link cho khách thuộc nhóm nào, thiệp tự động hiển thị tab đó.
- **Backend & Data:** Lưu trong `invitations.custom_config.persona_messages` (dạng key-value theo `group_name`).
- **Bảo mật:** Khách nhóm A không xem được nội dung nhóm B; không rò rỉ tên các nhóm khác trong DOM.
- **Effort / Chi phí:** `M` / Chi phí định kỳ 0đ.
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 5 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-A02`: Chế Độ Tiếp Cận Dành Riêng Cho Ông Bà & Người Lớn Tuổi (Grandparents Accessibility Mode)
- **User Story:** Khách mời lớn tuổi mắt kém hoặc dùng điện thoại chữ to có thể bấm 1 nút để xem thiệp với font chữ to rõ, độ tương phản cao, không bị rối mắt bởi hiệu ứng.
- **Lý do khách trả tiền:** Giải quyết nỗi lo lớn nhất của gia đình: "Sợ gửi thiệp online các cụ không đọc được".
- **Luồng UX/UI:** Ở góc trên thiệp có icon [ Chữ Lớn / Dễ Đọc ]. Bấm vào tự động phóng to font chữ lên 125%, chuyển nền tương phản cao, dừng các hiệu ứng hoa rơi.
- **Backend & Data:** CSS Tokens toggle class `.mayhen_accessibility_mode` thuần Vanilla JS.
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 5 | WTP: 4 | Diff: 5 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-A03`: Hiệu Ứng Mở Màn Điện Ảnh Cao Cấp (Cinema Opening & Wax Seal)
- **User Story:** Khách chạm nhẹ vào con dấu sáp vàng kim monogram tên cặp đôi, phong bì lụa mở ra với âm thanh giấy tự nhiên và cánh thiệp trượt ra như thư mời hoàng gia.
- **Lý do khách trả tiền:** Tạo ấn tượng "Choáng ngợp" ngay từ 3 giây đầu tiên mở thiệp.
- **Luồng UX/UI:** Tùy chọn 6 mẫu tem sáp độc quyền: Cẩm Tú Cầu, Đôi Uyên Ương, Chữ Hỷ Hoàn Gia, Monogram Khắc Nổi. Âm thanh bóc thư êm dịu (Web Audio API).
- **Backend & Data:** Cấu hình trong `invitations.custom_config.seal_type`.
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 4 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-A04`: Lời Mời Song Ngữ Tự Động Theo Thiết Bị (Bilingual Multi-Language Switcher)
- **User Story:** Khi gửi thiệp cho bạn bè hoặc người thân ở nước ngoài, thiệp tự động hiển thị Tiếng Anh hoặc Tiếng Việt dựa theo ngôn ngữ trình duyệt của khách.
- **Lý do khách trả tiền:** Cần thiết cho các đám cưới đa quốc gia hoặc có bạn bè quốc tế.
- **Luồng UX/UI:** Cho phép nhập song ngữ (VI / EN / ZH) ở các trường quan trọng (Tên lễ, Địa chỉ, Dresscode, Lời ngỏ). Nút đổi ngôn ngữ nổi nhẹ ở góc màn hình.
- **Backend & Data:** `schema.json` mở rộng trường `_en` song song.
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 4 | WTP: 5 | Diff: 4 | Feas: 4 | Maint: 5 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-A05`: Chỉ Đường Thông Minh Đa Phương Tiện (Interactive Travel & Parking Kit)
- **User Story:** Khách đi ô tô cần biết chỗ gửi xe, khách ở xa cần biết khoảng cách và hướng đi thuận tiện nhất.
- **Lý do khách trả tiền:** Tránh tình trạng khách đến nơi loay hoay tìm bãi đỗ xe hoặc đi nhầm cổng trung tâm hội nghị.
- **Luồng UX/UI:** Khối sự kiện có tab: [ Google Maps | Chỉ Dẫn Chỗ Đậu Ô Tô/Xe Máy | Lưu Ý Lối Vào Sảnh ].
- **Backend & Data:** Cặp trường `parking_guide`, `venue_gate_note` trong `extra_data`.
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 5 | WTP: 4 | Diff: 4 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-A06`: Bộ Thẻ In Đồng Bộ Thiệp Mời (Printable Companion & QR Card Builder)
- **User Story:** Cặp đôi in thiệp giấy truyền thống muốn kèm một thẻ nhỏ xinh có mã QR dẫn thẳng vào thiệp online.
- **Lý do khách trả tiền:** Kết hợp hoàn hảo giữa thiệp giấy gửi phụ huynh và thiệp online cho bạn bè.
- **Luồng UX/UI:** Studio xuất ra file PDF vector kích thước thẻ cảm ơn (9x5.4cm hoặc 10x15cm) có mã QR và typography đồng bộ mẫu thiệp.
- **Backend & Data:** Server-side PDF generation qua canvas / headless SVG.
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 4 | WTP: 5 | Diff: 4 | Feas: 4 | Maint: 4 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-A07`: Thông Báo Thay Đổi Sự Kiện Khẩn Cấp (Wedding Update Broadcast)
- **User Story:** Đám cưới đổi sảnh tiệc hoặc dời giờ đón khách 30 phút vì thời tiết, cặp đôi cần báo ngay cho khách đã nhận thiệp.
- **Lý do khách trả tiền:** Cứu cánh trong tình huống phát sinh khẩn cấp ngày cưới.
- **Luồng UX/UI:** Banner thông báo trang trọng nổi ở đầu thiệp: "Thông báo cập nhật: Tiệc cưới được chuyển sang Sảnh Ballroom B".
- **Backend & Data:** Cột `broadcast_notice` trong `invitations.custom_config`.
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 4 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-A08`: Dòng Thời Gian Tương Tác Hành Trình Yêu (Interactive Story Timeline)
- **User Story:** Hiển thị các cột mốc: Ngày đầu gặp gỡ -> Lời tỏ tình -> Chuyến đi đáng nhớ -> Ngày cầu hôn -> Ngày về chung đôi.
- **Lý do khách trả tiền:** Tạo chiều sâu cảm xúc, khách mời xúc động khi đọc câu chuyện tình yêu.
- **Luồng UX/UI:** Dòng thời gian dạng lật ảnh kèm mốc năm tháng lãng mạn.
- **Backend & Data:** Repeater `story_milestones` (Year, Title, Description, Photo).
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 3 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-A09`: Phân Quyền Sự Kiện Theo Nhóm Khách (Guest-Specific Event View)
- **User Story:** Khách chỉ dự tiệc Nhà Trai không nhìn thấy giờ Lễ Vu Quy ở Nhà Gái, tránh đi nhầm địa điểm.
- **Lý do khách trả tiền:** Rất quan trọng cho đám cưới tổ chức ở 2 quê xa nhau.
- **Luồng UX/UI:** Gắn tag sự kiện cho từng nhóm khách trong danh bạ. Link khách mời chỉ render các sự kiện họ được mời.
- **Backend & Data:** Logic render điều kiện trong `events` section theo `guest.event_filter`.
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 5 | Feas: 4 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-A10`: Hộp Thời Gian Tình Yêu (Story Capsule & Milestone Unlock)
- **User Story:** Một số bức ảnh hoặc video đặc biệt sẽ tự động mở sau giờ cử hành hôn lễ.
- **Lý do khách trả tiền:** Khách tò mò quay lại mở thiệp nhiều lần sau ngày cưới.
- **Effort / Chi phí:** `L` / Chi phí lưu trữ nhỏ.
- **Điểm số:** Value: 3 | WTP: 3 | Diff: 4 | Feas: 4 | Maint: 4 | Risk: 1 → **Xếp hạng: LATER**.

---

### NHÓM B — GUEST CRM & RSVP ĐẲNG CẤP (10 TÍNH NĂNG)

#### `IDEA-B01`: Nhập Nhanh Danh Sách Khách Thông Minh Từ Excel / Copy-Paste (Smart Guest Importer)
- **User Story:** Cặp đôi copy 200 dòng từ Excel hoặc dán danh sách từ Zalo, hệ thống tự động phân tích Tên, Xưng hô, Số điện thoại và Nhóm.
- **Lý do khách trả tiền:** Tiết kiệm 3-4 tiếng gõ tay từng khách mời.
- **Luồng UX/UI:** Drawer Import với bản xem trước (Live Preview Mapping) trước khi bấm Lưu.
- **Backend & Data:** Bộ phân tích Regex + heuristic tên Việt, lưu hàng loạt trong 1 transaction an toàn.
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 4 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW** (Đã có MVP, nâng cấp mapping).

#### `IDEA-B02`: Cá Nhân Hóa Link Mời 1-Chạm Kèm Thẻ Chia Sẻ Zalo / Messenger (Social Share Card)
- **User Story:** Bấm "Gửi qua Zalo", hệ thống sinh sẵn đoạn tin nhắn trang trọng kèm ảnh thiệp có khắc tên riêng khách mời.
- **Lý do khách trả tiền:** Gửi thiệp lịch sự, chu đáo, không bị coi là spam link đại trà.
- **Luồng UX/UI:** Modal sao chép: Lời nhắn gợi ý ("Thân gửi anh Nam...") + Link riêng + Nút mở Zalo.
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 5 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-B03`: Khảo Sát Nhu Cầu Ăn Uống & Đưa Đón (Dietary & Shuttle Bus Survey)
- **User Story:** Hỏi khách có ăn chay / dị ứng hải sản không, có cần xe đưa đón từ điểm tập trung không.
- **Lý do khách trả tiền:** Giúp đặt bàn tiệc chính xác 100%, không lãng phí cỗ cưới.
- **Backend & Data:** Trường `dietary_requirements`, `shuttle_bus_needed` trong `guest_responses`.
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 5 | WTP: 4 | Diff: 4 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-B04`: Quản Lý Hạn Chót Xác Nhận Tham Dự (RSVP Deadline Enforcement)
- **User Story:** Sau ngày 15/11, form RSVP tự động đóng và hiển thị lời dặn: "Hạn chót xác nhận đã kết thúc. Vui lòng liên hệ trực tiếp cô dâu chú rể".
- **Lý do khách trả tiền:** Chốt số lượng mâm cỗ với nhà hàng trước ngày cưới.
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 3 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-B05`: Lịch Sử Thay Đổi Xác Nhận (RSVP Change History & Audit)
- **User Story:** Khách ban đầu báo "Có tham dự (2 người)", sau đó đổi thành "Không thể tham dự", hệ thống lưu vết lịch sử và thông báo cho cặp đôi.
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 4 | Feas: 4 | Maint: 5 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-B06`: Phân Quyền Gia Đình Hai Bên Quản Lý Khách (Shared Family Guest List)
- **User Story:** Bố mẹ nhà trai có tài khoản phụ chỉ xem và nhập danh sách khách nhà trai, không chỉnh sửa thiệp hay khách nhà gái.
- **Lý do khách trả tiền:** Cực kỳ cần thiết khi bố mẹ muốn chủ động quản lý khách mời riêng.
- **Effort / Chi phí:** `L` / 0đ.
- **Điểm số:** Value: 4 | WTP: 5 | Diff: 5 | Feas: 4 | Maint: 4 | Risk: 2 → **Xếp hạng: NEXT**.

#### `IDEA-B07`: Nhật Ký Phân Phối Thiệp Mời (Invitation Delivery Ledger)
- **User Story:** Theo dõi trạng thái: [ Chưa Gửi | Đã Gửi | Đã Mở Xem | Đã RSVP ].
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 4 | Feas: 4 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-B08`: Nhắc Hẹn RSVP Nhẹ Nhàng Tự Động (Gentle RSVP Reminder Assistant)
- **User Story:** Hệ thống lọc ra 35 khách chưa trả lời và tạo sẵn danh sách kèm lời nhắn nhắc hẹn lịch thiệp để cặp đôi gửi 1-chạm.
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 3 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-B09`: Nhận Diện Khách Trùng Lặp Thông Minh (Duplicate Guest Detector)
- **User Story:** Cảnh báo khi nhập 2 khách trùng số điện thoại hoặc trùng họ tên trong cùng một nhóm.
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 4 | WTP: 3 | Diff: 3 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-B10`: Hỗ Trợ Đón Tiếp Khách Mời Không Dùng Smartphone (Manual Concierge Entry)
- **User Story:** Người thân gọi điện báo có đi, cô dâu chú rể cập nhật trực tiếp trong dashboard trong 2 giây.
- **Effort / Chi phí:** `S` / 0đ (Đã triển khai trong `GuestController@manualRsvp`).
- **Điểm số:** Value: 5 | WTP: 4 | Diff: 3 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW (ĐÃ CÓ)**.

---

### NHÓM C — ĐIỀU PHỐI NGÀY CƯỚI “WEDDING DAY CONTROL ROOM” (10 TÍNH NĂNG)

#### `IDEA-C01`: Bảng Điều Khiển Ngày Cưới Thời Gian Thực (Live Wedding Command Center)
- **User Story:** Ngày cưới, cặp đôi mở điện thoại thấy ngay: 142/180 khách đã đến, 4 bàn tiệc đã kín, 2 bàn còn trống 3 ghế.
- **Lý do khách trả tiền:** Biến Mây Hẹn thành trợ lý điều phối đắc lực nhất trong ngày cưới.
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 5 | Feas: 4 | Maint: 5 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-C02`: Quét QR Check-in Điểm Danh Đón Khách (Receptionist QR Check-in)
- **User Story:** Khách đến sảnh đưa thiệp trên điện thoại, lễ tân quét mã QR mất 1 giây để chào tên khách và báo số bàn.
- **Lý do khách trả tiền:** Cảm giác VIP, chuyên nghiệp như sự kiện thảm đỏ.
- **Effort / Chi phí:** `M` / 0đ (Webcam BarcodeDetector API).
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 5 | Feas: 4 | Maint: 4 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-C03`: Xếp Bàn Tiệc Kéo Thả Trực Quan (Seating Planner Studio)
- **User Story:** Tạo các bàn 10 người, kéo thả tên khách vào từng bàn (Bàn Gia Đình, Bàn Đại Học, Bàn Đồng Nghiệp).
- **Lý do khách trả tiền:** Tránh tình trạng khách đến không biết ngồi đâu, người quen bị ngồi cách xa nhau.
- **Effort / Chi phí:** `L` / 0đ.
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 4 | Feas: 3 | Maint: 4 | Risk: 2 → **Xếp hạng: LATER**.

#### `IDEA-C04`: Tra Cứu Số Bàn 1-Chạm Cho Khách (Guest Table Finder)
- **User Story:** Khách mở thiệp có ngay dòng: "Vị trí bàn tiệc của bạn: Bàn số 08 (Khu Vực Bạn Cô Dâu)".
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 5 | WTP: 4 | Diff: 4 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-C05`: Bảng In Sơ Đồ Bàn Tiệc & Đón Tiếp (Reception Printable Sheet)
- **User Story:** Xuất file in khổ A4/A3 sắp xếp theo Alpha B hoặc theo Bàn để lễ tân cầm tay lúc đón khách.
- **Effort / Chi phí:** `S` / 0đ (Đã triển khai MVP `guests.print`).
- **Điểm số:** Value: 5 | WTP: 4 | Diff: 4 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW (ĐÃ CÓ MVP)**.

#### `IDEA-C06`: Phân Quyền Nhân Sự Ngày Cưới (Crew Staff Roles)
- **User Story:** Tạo mã PIN truy cập cho bạn thân phụ trách đón khách, chỉ được xem danh sách và check-in, không sửa thiệp.
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 4 | Feas: 4 | Maint: 5 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-C07`: Gói Dự Phòng Mất Mạng Ngày Cưới (Emergency Offline Pack)
- **User Story:** Nếu trung tâm tiệc cưới mất sóng 4G/WiFi, danh sách khách và số bàn đã được tải sẵn vào máy để tra cứu offline.
- **Effort / Chi phí:** `M` / 0đ (PWA Service Worker / CacheStorage).
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 5 | Feas: 4 | Maint: 4 | Risk: 2 → **Xếp hạng: NEXT**.

#### `IDEA-C08`: Màn Hình Chào Mừng Tại Sảnh Lễ Tân (Venue Welcome Display)
- **User Story:** Một tablet đặt ở bàn lễ tân hiển thị hình ảnh cặp đôi và chạy lời chào mừng từng khách vừa check-in.
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 4 | Feas: 4 | Maint: 5 | Risk: 1 → **Xếp hạng: LATER**.

#### `IDEA-C09`: Báo Cáo Sĩ Số Bàn Tiệc Thời Gian Thực (Table Capacity Wave)
- **User Story:** Báo động sảnh tiệc: Bàn 5 đã đủ 10 người, hướng dẫn khách tiếp theo sang Bàn 6.
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 3 | WTP: 3 | Diff: 4 | Feas: 4 | Maint: 4 | Risk: 1 → **Xếp hạng: LATER**.

#### `IDEA-C10`: Nhật Ký Thay Đổi Giờ Chót (Last-Minute Wedding Log)
- **User Story:** Ghi lại mọi phát sinh: Bổ sung 1 ghế phụ Bàn 3, đổi món chay cho khách VIP.
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 3 | WTP: 3 | Diff: 3 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: LATER**.

---

### NHÓM D — LƯU NIỆM & TƯƠNG TÁC SAU CƯỚI (10 TÍNH NĂNG)

#### `IDEA-D01`: Két Lưu Trữ Kỷ Niệm Số Trọn Đời (Digital Memory Vault)
- **User Story:** Sau ngày cưới, thiệp cưới chuyển hóa thành Trang Kỷ Niệm lưu trữ vĩnh viễn toàn bộ lời chúc, chữ ký và ảnh ngày cưới.
- **Lý do khách trả tiền:** Biến khoản tiền mua thiệp thành tài sản kỷ niệm gia đình vô giá.
- **Effort / Chi phí:** `M` / Chi phí lưu trữ nhỏ.
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 5 | Feas: 4 | Maint: 4 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-D02`: Xuất Sách Lưu Bút Kỷ Niệm PDF Đẹp Mắt (Printable Memory Guestbook)
- **User Story:** Một nút bấm xuất toàn bộ lời chúc và chữ ký thành cuốn sách PDF dàn trang thanh lịch để in kỷ yếu đóng bìa cứng.
- **Lý do khách trả tiền:** Cặp đôi có thể in ra đặt ở phòng khách gia đình.
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 5 | Feas: 4 | Maint: 4 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-D03`: Mã QR Thu Thập Ảnh Ngày Cưới Từ Khách Mời (Live Guest Photo Drop)
- **User Story:** Đặt mã QR trên bàn tiệc, khách chụp ảnh bằng điện thoại quét mã để tải thẳng ảnh lên album chung của cô dâu chú rể.
- **Lý do khách trả tiền:** Gom được hàng trăm bức ảnh khoảnh khắc tự nhiên mà thợ ảnh không chụp kịp.
- **Effort / Chi phí:** `L` / Chi phí lưu trữ ảnh (S3/Cloudflare R2).
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 5 | Feas: 3 | Maint: 4 | Risk: 2 → **Xếp hạng: NEXT**.

#### `IDEA-D04`: Lời Chúc Kèm Ảnh & Video Ngắn (Photo & Voice Wishes)
- **User Story:** Khách gửi lời chúc kèm bức ảnh kỷ niệm chụp cùng cô dâu chú rể từ thời đại học.
- **Effort / Chi phí:** `M` / Chi phí lưu trữ.
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 4 | Feas: 4 | Maint: 4 | Risk: 1 → **Xếp hạng: LATER**.

#### `IDEA-D05`: Luồng Gửi Thư Cảm Ơn Sau Cưới (Post-Wedding Thank You Flow)
- **User Story:** 2 ngày sau lễ cưới, gửi tin nhắn cảm ơn trang trọng kèm ảnh cưới chính thức tới danh sách khách đã tham dự.
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 4 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-D06`: Bức Tường Lời Chúc Trình Chiếu Sảnh Tiệc (Guest Wishes Projection Wall)
- **User Story:** Chiếu trực tiếp các lời chúc và ảnh của khách lên màn hình LED lớn ở sảnh tiệc cưới (có bộ lọc kiểm duyệt).
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 4 | Feas: 4 | Maint: 4 | Risk: 2 → **Xếp hạng: LATER**.

#### `IDEA-D07`: Nhắc Hẹn Kỷ Niệm Ngày Cưới 1 Năm (1st Anniversary Keepsake Reminder)
- **User Story:** Đúng 1 năm sau ngày cưới, gửi email chúc mừng kèm liên kết mở lại chiếc thiệp cưới thiêng liêng năm nào.
- **Effort / Chi phí:** `S` / Chi phí email tối thiểu.
- **Điểm số:** Value: 4 | WTP: 3 | Diff: 4 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-D08`: Xuất Báo Cáo Tài Chính Mừng Cưới Excel Chi Tiết (Financial Gift Ledger Export)
- **User Story:** Xuất file Excel phân loại: Ai mừng tiền mặt, ai chuyển khoản, số tiền từng người, ghi chú.
- **Effort / Chi phí:** `S` / 0đ (Đã triển khai MVP `gifts.export`).
- **Điểm số:** Value: 5 | WTP: 4 | Diff: 3 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW (ĐÃ CÓ)**.

#### `IDEA-D09`: Lưu Giữ Bút Tích Chữ Ký Tay (Handwritten Signature Canvas Archive)
- **User Story:** Lưu vết nét vẽ chữ ký tay của từng khách trên màn hình cảm ứng thành ảnh PNG trong suốt.
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 4 | Feas: 4 | Maint: 5 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-D10`: Lưu Trữ Ngoại Tuyến Đóng Gói (One-Click Offline Archive ZIP)
- **User Story:** Tải trọn bộ website thiệp cưới, ảnh, nhạc, lời chúc thành 1 file ZIP mở xem offline không cần mạng.
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 3 | WTP: 3 | Diff: 4 | Feas: 4 | Maint: 4 | Risk: 1 → **Xếp hạng: LATER**.

---

### NHÓM E — STUDIO V2 & NĂNG LỰC TẠO THIỆP VƯỢT TRỘI (10 TÍNH NĂNG)

#### `IDEA-E01`: Trợ Lý Cảnh Báo Chất Lượng Thiệp Tự Động (Visual Quality Doctor)
- **User Story:** Hệ thống tự quét thiệp và nhắc: "Ảnh bìa của bạn hơi mờ (độ phân giải thấp)", "Bạn chưa gắn liên kết Google Maps", "Địa chỉ sảnh tiệc đang để trống".
- **Lý do khách trả tiền:** Khách yên tâm 100% thiệp hoàn hảo trước khi gửi cho hàng trăm người.
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 5 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-E02`: Đổi Mẫu Thiệp Không Bao Giờ Mất Dữ Liệu (Safe Smart Template Switcher)
- **User Story:** Khách đang làm mẫu A muốn chuyển thử sang mẫu B: toàn bộ tên, ngày cưới, ảnh, bố mẹ tự động map sang mẫu mới mượt mà.
- **Effort / Chi phí:** `M` / 0đ (Đã đạt chuẩn hóa Schema 10 Section).
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 4 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW (ĐÃ CÓ NỀN TẢNG)**.

#### `IDEA-E03`: Tự Động Tính Ngày Âm Lịch & Lịch Hoàng Đạo (Auto Lunar Date & Calendar Sync)
- **User Story:** Chọn ngày dương 22/11/2026, thiệp tự điền "(Tức ngày 14 tháng 10 năm Bính Ngọ)" và vẽ tờ lịch chuẩn xác.
- **Effort / Chi phí:** `S` / 0đ (Đã tích hợp thư viện âm lịch thuần Việt).
- **Điểm số:** Value: 5 | WTP: 4 | Diff: 4 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW (ĐÃ CÓ)**.

#### `IDEA-E04`: Cắt Ảnh Tỷ Lệ Vàng Thông Minh Trực Quan (Smart Aspect-Ratio Photo Cropper)
- **User Story:** Upload ảnh dọc hay ảnh ngang, công cụ cắt ảnh cho phép zoom, xoay và chọn đúng tâm điểm khuôn mặt cô dâu chú rể.
- **Effort / Chi phí:** `M` / 0đ (Cropper.js thuần hoặc HTML5 Canvas).
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 4 | Feas: 4 | Maint: 5 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-E05`: So Sánh Bản Nháp & Bản Đang Xuất Bản (Publish Diff Inspector)
- **User Story:** Xem lại những thay đổi (ví dụ: đã sửa giờ tiệc từ 17h30 thành 18h00) trước khi bấm Xuất bản chính thức.
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 4 | Feas: 4 | Maint: 5 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-E06`: Kho Nhạc Cưới Bản Quyền Chọn Lọc (Curated Quiet Luxury Wedding Musics)
- **User Story:** Hơn 50 bản nhạc không lời acoustic, piano, violin lãng mạn nhẹ nhàng, nghe thử 1-chạm và tự động phát êm dịu.
- **Effort / Chi phí:** `S` / Chi phí lưu trữ nhỏ (Đã có `WeddingMusicService`).
- **Điểm số:** Value: 5 | WTP: 4 | Diff: 4 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW (ĐÃ CÓ)**.

#### `IDEA-E07`: Bảng Màu Tùy Biến Có Rào Chắn Thẩm Mỹ (Curated Palette Customizer)
- **User Story:** Cho phép đổi tone màu thiệp theo 5 bảng màu được nhà thiết kế phối sẵn (Đỏ Son, Xanh Sage, Vàng Gold, Kem Be, Hồng Tro), không cho chọn màu bừa bãi làm xấu thiệp.
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 4 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-E08`: Quản Lý Nhiều Thiệp Cưới Trên Một Tài Khoản (Multi-Invitation Dashboard)
- **User Story:** Tạo 1 thiệp riêng cho Tiệc Nhà Trai và 1 thiệp riêng cho Tiệc Nhà Gái với 2 địa điểm và ngày giờ khác nhau.
- **Effort / Chi phí:** `S` / 0đ (Đã hỗ trợ qua chức năng Nhân Bản Thiệp `duplicate`).
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 4 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW (ĐÃ CÓ)**.

#### `IDEA-E09`: Khôi Phục Lịch Sử Thao Tác Đa Tầng (Undo / Redo Multi-Level History)
- **User Story:** Lỡ tay xóa mất đoạn văn hay ảnh, bấm Undo (Ctrl+Z) khôi phục lại tức thì.
- **Effort / Chi phí:** `M` / 0đ (Đã triển khai trong `editor/history.js`).
- **Điểm số:** Value: 5 | WTP: 4 | Diff: 3 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW (ĐÃ CÓ)**.

#### `IDEA-E10`: Xem Thử Đa Thiết Bị Chuẩn Xác 100% (True Multi-Device Simulator)
- **User Story:** Xem trước thiệp hiển thị như thế nào trên iPhone 15 Pro, Samsung Galaxy, iPad và Màn hình Laptop.
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 4 | WTP: 3 | Diff: 3 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

---

### NHÓM F — DỊCH VỤ THÔNG MINH, SAAS & KINH DOANH (10 TÍNH NĂNG)

#### `IDEA-F01`: Trợ Lý AI Gợi Ý Lời Ngỏ & Câu Chuyện Tình Yêu (AI Wedding Story Writer)
- **User Story:** Cặp đôi nhập 3 từ khóa: "Gặp nhau ở quán cafe", "Yêu xa 3 năm", "Cùng thích Đà Lạt", AI viết thành câu chuyện tình yêu mượt mà, sâu lắng.
- **Lý do khách trả tiền:** Giải quyết nỗi sợ "Không biết viết gì cho hay" của hầu hết các cặp đôi.
- **Effort / Chi phí:** `M` / Chi phí OpenAI / Gemini API (~100đ/lần gọi).
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 4 | Feas: 5 | Maint: 4 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-F02`: Tên Miền Riêng Cho Đám Cưới (Custom Wedding Domain: `tuanlan.mayhen.vn` hoặc `tuanlan.wedding`)
- **User Story:** Thiệp cưới có tên miền độc nhất vô nhị mang tên hai bạn.
- **Lý do khách trả tiền:** Cực kỳ sang trọng và khẳng định dấu ấn cá nhân.
- **Effort / Chi phí:** `M` / Chi phí DNS Cloudflare / CNAME.
- **Điểm số:** Value: 4 | WTP: 5 | Diff: 4 | Feas: 4 | Maint: 4 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-F03`: Cổng Dành Riêng Cho Studio & Wedding Planner (Studio & Agency Portal)
- **User Story:** Studio áo cưới mua gói sỉ (10-50 thiệp) để tặng hoặc bán kèm cho khách hàng chụp ảnh cưới, có dashboard quản lý riêng.
- **Lý do khách trả tiền:** Kênh B2B tạo doanh thu định kỳ lớn và bền vững cho Mây Hẹn.
- **Effort / Chi phí:** `L` / 0đ.
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 5 | Feas: 4 | Maint: 4 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-F04`: Gỡ Hoàn Toàn Logo Mây Hẹn (White-Label Premium Branding)
- **User Story:** Khách trả phí gói cao cấp nhất để ẩn thương hiệu "Tạo bởi mayhen.vn", thiệp chỉ mang thương hiệu cá nhân của cặp đôi.
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 4 | WTP: 5 | Diff: 3 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-F05`: Hỗ Trợ Kỹ Thuật Ưu Tiên 24/7 Trong Tuần Lễ Cưới (VIP Wedding Week Concierge)
- **User Story:** Hotline / Zalo VIP hỗ trợ trực tiếp trong vòng 15 phút nếu có bất kỳ sự cố nào xảy ra trong tuần lễ đám cưới.
- **Effort / Chi phí:** `S` (Quy trình vận hành).
- **Điểm số:** Value: 4 | WTP: 5 | Diff: 4 | Feas: 5 | Maint: 4 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-F06`: Trợ Lý AI Tối Ưu Bố Cục Ảnh Cưới (Smart Focal Point Photo Optimizer)
- **User Story:** AI tự động nhận diện vị trí cô dâu chú rể để không bị cắt mất đầu hoặc mất váy cưới khi hiển thị trên các màn hình khác nhau.
- **Effort / Chi phí:** `M` / Chi phí tính toán nhỏ.
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 4 | Feas: 4 | Maint: 4 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-F07`: Danh Sách Mừng Cưới & Gợi Ý Món Quà (Digital Wedding Gift Registry)
- **User Story:** Cặp đôi liệt kê danh sách quà mong muốn (Máy pha cà phê, Lò nướng, Bộ ga gối...), bạn bè bấm chọn mua để không bị trùng quà.
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 3 | WTP: 3 | Diff: 4 | Feas: 4 | Maint: 4 | Risk: 1 → **Xếp hạng: LATER**.

#### `IDEA-F08`: Báo Cáo Phễu Tương Tác Thiệp (Invitation Analytics Funnel)
- **User Story:** Xem biểu đồ: Có bao nhiêu lượt mở thiệp, thời gian xem trung bình, phần nào được xem nhiều nhất (Ảnh cưới hay Lịch trình).
- **Effort / Chi phí:** `M` / 0đ.
- **Điểm số:** Value: 4 | WTP: 4 | Diff: 4 | Feas: 4 | Maint: 5 | Risk: 1 → **Xếp hạng: NEXT**.

#### `IDEA-F09`: Trung Tâm Quyền Riêng Tư & Bảo Vệ Dữ Liệu Khách Hàng (Privacy Center)
- **User Story:** Cho phép cặp đôi chủ động bật mật khẩu thiệp, khóa tải ảnh, bật/tắt hiển thị số tài khoản ngân hàng và xóa toàn bộ dữ liệu khi cần.
- **Effort / Chi phí:** `S` / 0đ.
- **Điểm số:** Value: 5 | WTP: 4 | Diff: 5 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW**.

#### `IDEA-F10`: Đóng Gói Gói Cước Minh Bạch & Tự Động Kích Hoạt Không Cần Duyệt Tay (Zero-Touch Activation)
- **User Story:** Chuyển khoản VietQR thành công, hệ thống tự động nâng cấp gói trong 3 giây mà không cần chụp màn hình gửi hóa đơn cho admin.
- **Effort / Chi phí:** `S` / 0đ (Đã hoàn thiện với webhook SePay + Transaction ID).
- **Điểm số:** Value: 5 | WTP: 5 | Diff: 4 | Feas: 5 | Maint: 5 | Risk: 1 → **Xếp hạng: NOW (ĐÃ CÓ)**.

