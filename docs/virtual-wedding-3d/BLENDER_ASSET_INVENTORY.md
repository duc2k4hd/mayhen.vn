# MÂY HẸN — VIRTUAL WEDDING WORLD 3D
## BLENDER ASSET INVENTORY & PBR VENUE SPECIFICATION (W03)

**Tên không gian:** Royal Crimson Marquee (Rạp Cưới Hoàng Gia Sắc Đỏ Cao Cấp)  
**Mã định danh:** `royal-crimson-marquee`  
**Phiên bản:** `v1`  
**Video tham chiếu:** `D:\File\FDown.vn_Tai_video_Facebook_720p (HD)_7b85.mp4` ("Lễ Tân Hôn — Cao Nhân & Trần Thảo", 29.09.2026, Monogram `NT`)  
**Tệp nguồn 3D (Blender):** `Blender/venues/royal-crimson-marquee/v1/mayhen_royal_crimson_marquee_v1.blend` (`652,398 bytes`)  
**Tệp runtime WebGL (GLB):** `public/assets/virtual-venues/royal-crimson-marquee/v1/mayhen_royal_crimson_marquee_v1.glb` (`6,191,752 bytes` / **5.90 MB**)  
**Video Walkthrough MP4:** `docs/virtual-wedding-3d/renders/w03-pbr/w03_venue_walkthrough.mp4` (`946,657 bytes`)  
**Bản Demo Trình Duyệt (WebGL2):** `public/assets/virtual-venues/royal-crimson-marquee/v1/walkthrough-demo.html`  

---

## 1. THÔNG SỐ HÌNH HỌC VÀ KHÔNG GIAN CHUẨN METRIC (1 UNIT = 1.0M)

| Hạng mục | Kích thước / Tọa độ | Đối chiếu Video gốc (`f01`–`f18`) |
| :--- | :--- | :--- |
| **Khẩu độ lòng rạp (Span)** | `18.00m` (`X = -9.0m .. +9.0m`) | 7 khung giàn truss nhôm định hình không cột giữa |
| **Chiều sâu tổng thể (Depth)** | `30.00m` (`Y = -18.0m .. +12.0m`) | Sảnh đón (`-18.0m..-13.5m`), Hội trường (`-13.5m..+5.0m`), Sân khấu (`+5.0m..+12.0m`) |
| **Chiều cao giọt gianh & đỉnh mái** | `4.50m` (eaves) / `6.20m` (ridge) | Mái dốc 2 mái lót bạt đen sao sợi quang (`tex_starlight_velvet.png`) |
| **Vách rèm ngăn sảnh & Cổng hoa** | `Y = -13.50m`, cổng rộng `3.20m x 3.15m` | Rèm nhung đỏ xếp ly sóng đứng, bảng tên trắng trên đỉnh cổng, 2 trụ hoa 3 tầng (`f01`) |
| **Khu vực sảnh trái (Reception)** | `X = -6.5m .. -2.2m, Y = -14.5m` | Bàn gallery lụa kem + dải nhung đỏ, thùng tiền mừng trắng, 5 khung ảnh, 2 bảng chào terracotta & giá vẽ (`f05`) |
| **Khu vực sảnh phải (Photobooth)** | `X = +2.0m .. +7.8m, Y = -14.3m` | Khối chữ Song Hỷ đỏ 3D, bục thảm đen, chữ nổi 3D `NT` giữa cụm hoa hồng đỏ/trắng (`f01`, `f09`) |
| **Lối đi trung tâm (Central Aisle)** | Rộng `2.20m` (`X = -1.10m .. +1.10m`) | Trải thảm nỉ xanh xám phiến đá (`tex_carpet_slate.png`), 2 cánh bên trải thảm cỏ xanh (`tex_turf_albedo.png`) |
| **Bàn tiệc & Ghế Tiffany trắng** | `16 bàn` (`Ø1.50m`) / `160 ghế Tiffany trắng` | Khăn bàn lụa trắng kem, bộ bát đĩa sứ 10 người + lon nước xanh (`f16`), khoảng lùi ghế `> 1.05m` (Zero Overlap) |
| **Cầu dẫn Catwalk & Sân khấu** | Catwalk `2.8m x 3.2m x 0.45m`, Stage `10.4m x 3.4m x 0.65m` | Thảm đen tuyền, viền hoa hồng trắng/đỏ thấp dọc catwalk, đĩa sáng gobo tròn trên sàn (`f15`, `f16`) |
| **Phông sân khấu & 2 Màn LED 16:9** | Phông nhung đỏ `9.6m x 4.2m` + 2 màn LED `3.20m x 1.80m` | Chữ `NT` phát sáng ở tâm, tháp ly sâm-panh, bánh cưới 2 tầng, dàn trống 5 món + đàn organ (`f07`, `f15`) |

---

## 2. BẢNG ĐO LƯỜNG HIỆU NĂNG THỰC TẾ (BLENDER & CHROMIUM WEBGL2 BENCHMARK)

Đo thực tế bằng Headless Chromium (`--enable-webgl --use-angle=d3d11`) trên `walkthrough-demo.html`:

| Chỉ số kỹ thuật | Ngân sách mục tiêu | Kết quả đo thực tế (`v1`) | Đánh giá |
| :--- | :--- | :--- | :--- |
| **Dung lượng tệp `.glb`** | `< 15.0 MB` | **5.90 MB** (`6,191,752 bytes`) | **ĐẠT (Giảm 52% nhờ Mesh Instancing)** |
| **Thời gian nạp & giải mã `.glb`** | `< 1,500 ms` | **117 ms** | **ĐẠT (Tức thì)** |
| **Tổng số Tam giác (Triangles)** | `< 150,000` | **57,116** | **ĐẠT (Rất nhẹ cho Mobile GPU)** |
| **Tổng số Đỉnh (Vertices)** | `< 150,000` | **113,870** | **ĐẠT** |
| **Số Mesh / Node trong Scene** | `< 1,000` | **518 meshes / 727 nodes** | **ĐẠT** |
| **Draw Calls mỗi khung hình** | `< 1,200` | **892 draw calls** | **ĐẠT** |
| **FPS Desktop (`1440×900` High)** | `>= 60 FPS` | **100 FPS** (`10.1 ms/frame`) | **ĐẠT** |
| **FPS Desktop (`1440×900` Live LED)** | `>= 60 FPS` | **100 FPS** (`10.1 ms/frame`) | **ĐẠT** |
| **FPS Mobile (`390×844` Lite)** | `>= 45 FPS` | **100 FPS** (`10.2 ms/frame`) | **ĐẠT** |
| **Lỗi Console / PageError** | `0` | **0 lỗi** | **ĐẠT** |

---

## 3. DANH MỤC 8 TEXTURE NỘI BỘ & 19 VẬT LIỆU PBR

Toàn bộ texture được sinh tự động bằng `Blender/scripts/generate_pbr_textures.py` và đóng gói trực tiếp vào `.glb` (100% nội bộ, không dùng CDN ngoài):

1. `tex_starlight_velvet.png` (`1024×1024`): Vải bạt đen trần & vách rạp điểm hạt sao sợi quang trắng ấm và xanh dương (`Mat_Starlight_Liner`).
2. `tex_turf_albedo.png` (`512×512`) & `tex_turf_normal.png` (`512×512`): Thảm cỏ nhân tạo hai bên cánh bàn tiệc (`Mat_Turf_Grass`).
3. `tex_carpet_slate.png` (`512×512`): Thảm nỉ xanh xám phiến đá trải sảnh đón và lối đi chính 2.20m (`Mat_Carpet_Slate`).
4. `tex_welcome_left.png` (`512×896`): Bảng chào terracotta trái *"LỄ TÂN HÔN — CAO NHÂN & TRẦN THẢO — 29.09.2026"* (`Mat_Welcome_Left`, UV phẳng chuẩn).
5. `tex_welcome_right.png` (`512×1024`): Bảng chào terracotta phải *"NHÀ CÓ HỶ"* kèm nút thắt đồng tâm trắng (`Mat_Welcome_Right`, UV phẳng chuẩn).
6. `tex_couple_canvas.png` (`512×768`): Tranh chân dung cô dâu chú rể trên giá vẽ gỗ 3 chân (`Mat_Couple_Canvas`, UV phẳng chuẩn).
7. `tex_led_idle_slate.png` (`1280×720`): Màn hình chờ 16:9 trung thực *"MÂY HẸN VIRTUAL STAGE — CHƯƠNG TRÌNH TRỰC TIẾP CHƯA BẮT ĐẦU"* (`Mat_LED_Screen`, hỗ trợ thay thế động bằng Video Texture khi chủ tiệc phát livestream).

---

## 4. HỆ THỐNG 15 NHÂN VẬT 3D CHUYỂN ĐỘNG (ARTICULATED ANIMATED NPCS)

Tất cả nhân vật có cấu trúc phân cấp khớp (Thân trên, Vai, Đầu, Tóc, Tay trái, Tay phải, Chân/Tà áo) và vòng lặp hoạt cảnh **72 frames (3.0 giây @ 24fps)** lệch pha nhau (`phase_offset`) để không bao giờ cử động rập khuôn cùng lúc:

- **Sân khấu & Ban nhạc (`STAFF`)**: `NPC_Bride`, `NPC_Groom`, `NPC_Band_Drummer` (ngồi đánh trống), `NPC_Band_Keyboardist` (chơi đàn organ), `NPC_Band_Guitarist` (chơi guitar).
- **Đón tiếp & Phục vụ (`STAFF`)**: `NPC_Receptionist_01` (áo dài đỏ đứng sau bàn gallery cúi chào), `NPC_Usher_Portal` (áo dài đỏ đứng bên phải cổng hoa mời khách vào sảnh — không chắn trục nhìn chính), `NPC_Waiter_Hall` (phục vụ bàn tiệc), `NPC_Staff_Prep` (nhân viên kiểm tra bàn).
- **Khách ảo tạo không khí (`GUEST_NPCS`)**: `NPC_Guest_01` .. `NPC_Guest_06` ngồi trò chuyện tự nhiên tại các bàn tiệc trung tâm (tách biệt hoàn toàn khỏi bộ đếm khách thật tham dự).

---

## 5. BỘ ẢNH RENDER CYCLES, VIDEO WALKTHROUGH & ẢNH CHỤP WEBGL2

1. **6 Góc nhìn kiến trúc PBR (Cycles GPU CUDA)**:
   - [w03_01_overview.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_01_overview.png) — Góc nhìn tổng thể cắt lớp mái (Isometric Cutaway)
   - [w03_02_foyer_entrance.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_02_foyer_entrance.png) — Cổng hoa sảnh đón, bảng chào & lối đi chính (khớp `f01`, `f05`)
   - [w03_03_aisle_to_stage.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_03_aisle_to_stage.png) — Trục lối đi chính nhìn lên vòm lụa đỏ/trắng, đèn chùm & sân khấu (khớp `f03`, `f18`)
   - [w03_04_stage_gala_night.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_04_stage_gala_night.png) — Cận cảnh sân khấu nghi lễ, chữ `NT`, bánh cưới, tháp rượu & ban nhạc (khớp `f07`, `f15`)
   - [w03_05_foyer_photobooth.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_05_foyer_photobooth.png) — Khu vực Song Hỷ & Photobooth chữ nổi `NT` (khớp `f09`)
   - [w03_06_dining_table_detail.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_06_dining_table_detail.png) — Chi tiết bàn tiệc 10 ghế Tiffany trắng, bộ đồ ăn sứ & quạt điều hòa (khớp `f16`)
2. **5 Khung giờ ánh sáng (Cycles GPU CUDA)**:
   - [w03_lighting_01_dawn_05h.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_lighting_01_dawn_05h.png) — `05:00` Sáng sớm chuẩn bị
   - [w03_lighting_02_morning_08h.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_lighting_02_morning_08h.png) — `08:00` Nắng sớm đón khách
   - [w03_lighting_03_noon_12h.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_lighting_03_noon_12h.png) — `12:00` Ánh sáng ban ngày rực rỡ
   - [w03_lighting_04_sunset_17h.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_lighting_04_sunset_17h.png) — `17:00` Hoàng hôn cam vàng ấm áp
   - [w03_lighting_05_night_20h.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_lighting_05_night_20h.png) — `20:00` Dạ tiệc đêm lung linh
3. **4 Ảnh chụp chạy thực tế trên Trình duyệt WebGL2 (Desktop & Mobile)**:
   - [w03_browser_01_desktop_foyer.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_browser_01_desktop_foyer.png) — Desktop `1440×900` tại Cổng vào (`100 FPS`)
   - [w03_browser_02_desktop_aisle.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_browser_02_desktop_aisle.png) — Desktop `1440×900` tại Lối đi chính (`100 FPS`)
   - [w03_browser_03_desktop_stage_live.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_browser_03_desktop_stage_live.png) — Desktop `1440×900` bật thử Livestream trên 2 màn hình LED 16:9 (`100 FPS`)
   - [w03_browser_04_mobile_390x844.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_browser_04_mobile_390x844.png) — Mobile `390×844` chế độ `Lite` (`100 FPS`)
4. **Video Walkthrough MP4**:
   - [w03_venue_walkthrough.mp4](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_venue_walkthrough.mp4)
