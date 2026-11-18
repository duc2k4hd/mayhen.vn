# ĐẶC TẢ KỸ THUẬT CẢNH BLENDER & QUY CHUẨN TỶ LỆ THỰC (BLENDER SCENE SPECIFICATION)

## 1. Hệ Tọa Độ, Đơn Vị Đo & Tỷ Lệ Người Thật
- **Phần mềm dựng gốc:** Blender `5.2.3 LTS` (tương thích xuất chuẩn `glTF 2.0 Binary .glb`).
- **Hệ đơn vị:** `Metric` (`Unit Scale = 1.000`, `1 Blender Unit = 1,00 mét`).
- **Quy ước trục tọa độ:**
  - Trục **`X` (Đông – Tây):** Chiều rộng ngang của rạp (`-9,0 m` cánh trái → `0,0 m` tim lối đi chính → `+9,0 m` cánh phải).
  - Trục **`Y` (Nam – Bắc):** Chiều sâu tiến trình trải nghiệm khách tham dự:
    - `Y = -22,0 m .. -18,0 m`: Thảm cỏ đón khách ngoài trời & điểm xuất phát mặc định của khách (`Spawn_Entrance`).
    - `Y = -18,0 m .. -13,5 m`: Tiền sảnh có mái che (`Foyer`: Bàn lễ tân bên trái, Cổng hoa chính ở giữa, Chữ Song Hỷ `囍` & Bục chụp ảnh `NT` bên phải).
    - `Y = -13,5 m`: Vách rèm nhung đỏ ngăn cách Tiền sảnh và Đại sảnh.
    - `Y = -13,5 m .. +5,2 m`: Đại sảnh tiệc bầu trời sao (`16` bàn tròn 10 ghế chia 2 cánh trên thảm cỏ xanh, lối đi thảm xám rộng `2,20 m` ở giữa).
    - `Y = +5,2 m .. +8,5 m`: Sàn Catwalk nghi lễ (`rộng 3,0 m, cao 0,45 m`, có bậc bước lên từ lối đi chính).
    - `Y = +8,5 m .. +12,0 m`: Sân khấu chính (`rộng 10,4 m, cao 0,65 m`, phông nhung đỏ `NT`, 2 tháp hoa, bánh cưới, tháp rượu, ban nhạc và 2 màn hình LED 16:9 hai cánh).
  - Trục **`Z` (Độ cao):** Mặt sàn chính của rạp = `0,000 m`. Mọi chân bàn, chân ghế, bục sân khấu, bàn lễ tân và bàn chân NPC đều tiếp xúc chuẩn trên mặt sàn tương ứng (`không lơ lửng, không xuyên sàn`).

---

## 2. Cấu Trúc Phân Cấp Collection Bắt Buộc Trong File `.blend`

```text
MAYHEN_VIRTUAL_WEDDING
├── REFERENCES                  # Thước chuẩn nhân trắc 1.70m & khung quy chiếu góc máy gốc
├── ENVIRONMENT
│   ├── GROUND                  # Nền sân ngoài, thảm cỏ xanh 2 cánh, thảm xám lối đi & tiền sảnh
│   ├── EXTERIOR                # Lối dạo đón khách, hàng cây cảnh & đèn trụ sân ngoài
│   ├── ARCHITECTURE            # Vách rèm nhung đỏ ngăn sảnh, vách bạt sao đen bao che kín sáng
│   ├── ROOF_AND_STRUCTURE      # 7 khung cổng Truss thép hộp, xà gồ dọc & mái bạt PVC trắng
│   └── ENTRANCE                # Cổng hoa trung tâm, bàn lễ tân + thùng mừng + khung ảnh, bảng đón khách, giá vẽ, chữ 囍, bục Photobooth NT
├── CEREMONY
│   ├── STAGE                   # Bệ sân khấu chính 0.65m, bậc tam cấp, bục bánh cưới, bục tháp rượu, bộ nhạc cụ
│   ├── AISLE                   # Sàn Catwalk 0.45m, viền hoa dọc catwalk, thảm chiếu gobo sao
│   ├── BACKDROP                # Rèm nhung đỏ sân khấu, chữ NT phát sáng, 2 tháp hoa & mành tua trắng
│   └── LED_SCREENS             # LED_Screen_Left (3.2x1.8m), LED_Screen_Right (3.2x1.8m) & khung viền
├── DINING
│   ├── TABLES                  # 16 bàn tròn Ø1.50m cao 0.76m phủ khăn trắng rủ nếp tự nhiên
│   ├── CHAIRS                  # 160 ghế Tiffany trắng xếp quanh bàn + 2 cụm ghế dự phòng sát vách
│   ├── TABLEWARE               # 16 bộ bát đĩa sứ trắng, ly, lon nước xanh đồng bộ
│   └── DECORATIONS             # Bình hoa tâm bàn & phụ kiện bàn tiệc
├── DECOR
│   ├── FLOWERS                 # Hoa cổng, hoa chân sân khấu, hoa photobooth đa sắc độ
│   ├── FABRICS                 # 14 dải lụa kem uốn lượn tầng cánh sen, vòm nhung đỏ 12 múi giữa sảnh, 16 dải lụa cánh bên
│   └── ORNAMENTS               # Vòng kim loại vàng treo trần, dây hoa trắng rủ, tháp bánh, tháp ly, tráp ăn hỏi
├── LIGHTING                    # 8 đèn chùm pha lê, đèn cầu hổ phách, 12 đèn Par ấm, 8 đèn Beam + nón tia sáng, Sun/Sky & đèn sảnh
├── CHARACTERS
│   ├── STAFF                   # Lễ tân đón khách, nhân viên dẫn bàn, nhân viên kiểm bàn, 2 nhạc công
│   ├── GUEST_NPCS              # Cô dâu – Chú rể (chế độ demo lễ) & 3 khách mô phỏng mặc áo dài / vest
│   └── CHARACTER_ANIMATIONS    # Dữ liệu chuyển động lệch pha không trượt chân, không xuyên bàn
├── INTERACTION_ZONES           # Zone_Entrance_Gate, Zone_Reception_Gift, Zone_Photobooth, Zone_Stage_View, Zone_Livestream_Focus
├── COLLIDERS                   # Hộp va chạm vô hình (vách rạp, bàn lễ tân, 16 bàn tiệc, bệ sân khấu) xuất sang WebGL
├── NAVIGATION                  # Mạng mặt phẳng đi bộ hợp lệ (Walkable NavBounds: tiền sảnh, lối giữa 2.2m, hành lang biên)
├── CAMERAS                     # 7 Camera đối chiếu trực tiếp với góc quay video gốc + Camera Walkthrough
└── WEB_EXPORT                  # Siêu dữ liệu định danh phiên bản xuất GLB
```

---

## 3. Quy Chuẩn Thông Thủy & Nhân Trắc Học Đã Kiểm Định
- **Chiều cao mắt nhân vật người lớn:** `1,62 m – 1,70 m`.
- **Lối đi trung tâm (`Central Aisle`):** Rộng `2,20 m` hoàn toàn thông thoáng từ cổng vòm (`Y=-13,5m`) tới bậc Catwalk (`Y=+5,2m`), đủ cho 3 avatar đi song song không va chạm ghế.
- **Khoảng cách tâm-đến-tâm giữa các bàn tiệc:** `3,60 m` theo chiều ngang × `3,80 m` theo chiều dọc → sau khi trừ mặt bàn `1,50m` và vòng ghế `0,55m × 2`, khoảng lọt lòng giữa hai lưng ghế liền kề đạt **`1,00 m – 1,20 m`**, đảm bảo không có bất kỳ ghế nào xuyên nhau và NPC phục vụ đi lọt tự nhiên.
- **Chiều cao rủ thấp nhất của đèn chùm & dải lụa trần:** `3,45 m` trên mặt sàn (cao hơn đầu người đứng trên sân khấu tối thiểu `1,10 m`).

---

## 4. Năm Trạng Thái Ánh Sáng Theo Thời Gian Sự Kiện (Server-Driven Lighting Presets)

| Mã trạng thái | Giờ sự kiện mẫu | Bầu trời & Ánh sáng tự nhiên ngoài hiên | Ánh sáng bên trong Đại sảnh & Sân khấu | Bầu không khí cảm nhận |
| :--- | :---: | :--- | :--- | :--- |
| `TIME_0500_DAWN` | **05:00 sáng** | Trời tờ mờ xanh chàm lạnh (`11000K`, cường độ yếu `0.25`), sương sớm nhẹ | Đèn làm việc/đèn dây ấm bật `55%`, đèn chùm `40%`, đèn Beam sân khấu **TẮT** | Yên tĩnh tinh sương, thấy rõ nhân viên đang chuẩn bị bàn ghế & khăn trải |
| `TIME_0800_MORNING` | **08:00 sáng** | Nắng sớm vàng dịu chiếu xiên góc thấp (`4200K`, cường độ `1.4`) vào tiền sảnh | Đèn chùm pha lê `75%`, đèn khe lụa trần ấm `80%`, đèn Par sân khấu ấm nhẹ `50%` | Tươi mới, trang nhã, khu đón khách và bảng tên sáng rõ tự nhiên |
| `TIME_1200_NOON` | **12:00 trưa** | Nắng ban ngày rực rỡ ngoài sân cỏ (`5600K`, cường độ `2.6`) — khớp thời điểm quay thực tế của video gốc (`f11`) | Nhờ lớp bạt sao đen kín sáng, đại sảnh giữ nền sao đêm huyền ảo; 8 đèn chùm `100%`, 14 đèn Par + 8 luồng Beam trắng xanh bật **RỰC RỠ** phục vụ nghi lễ | Đúng 100% chất điện ảnh đối lập Ngoài nắng – Trong lung linh như video tham chiếu |
| `TIME_1700_SUNSET` | **17:00 chiều** | Hoàng hôn cam hổ phách ấm (`3000K`, góc nắng thấp `12°`) nhuộm vàng thảm cỏ và cổng hoa tiền sảnh | Đèn cầu thủy tinh tiền sảnh bật sáng rõ, đèn chùm & vòm nhung đỏ ấm áp `95%`, Beam dịu `60%` | Lãng mạn, trầm ấm lúc giao thoa chiều tà – đón khách tiệc tối |
| `TIME_2000_NIGHT` | **20:00 tối** | Bầu trời đêm xanh đen sâu (`#070B16`), đèn trụ lối dạo sân vườn bật sáng dẫn hướng vào cổng | Đèn cổng hoa & photobooth nổi bật giữa đêm, đèn chùm pha lê + trần sao + màn LED/sân khấu cân bằng phơi sáng chuẩn (`Filmic/AgX`, không cháy trắng, mặt NPC đủ sáng fill ấm) | Đêm tiệc cưới sang trọng, ấm cúng và có chiều sâu thị giác cao nhất |
