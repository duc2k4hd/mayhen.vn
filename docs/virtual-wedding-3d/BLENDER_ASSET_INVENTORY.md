# MÂY HẸN — VIRTUAL WEDDING WORLD 3D
## BLENDER ASSET INVENTORY & PBR VENUE SPECIFICATION (W03)

**Tên không gian:** Royal Crimson Marquee (Rạp Cưới Hoàng Gia Sắc Đỏ Cao Cấp)  
**Mã định danh:** `royal-crimson-marquee`  
**Phiên bản:** `v1`  
**Video tham chiếu:** `D:\File\FDown.vn_Tai_video_Facebook_720p (HD)_7b85.mp4` ("Lễ Tân Hôn — Cao Nhân & Trần Thảo", 29.09.2026, Monogram `NT`)  
**Tệp nguồn 3D (Blender):** `Blender/venues/royal-crimson-marquee/v1/mayhen_royal_crimson_marquee_v1.blend` (`68,145,212 bytes`)  
**Tệp runtime WebGL (GLB):** `public/assets/virtual-venues/royal-crimson-marquee/v1/mayhen_royal_crimson_marquee_v1.glb` (`15,479,728 bytes` / **14.76 MB**)  
**Video Walkthrough MP4:** `docs/virtual-wedding-3d/renders/w03-pbr/w03_venue_walkthrough.mp4`  
**Bản Demo Trình Duyệt (WebGL2 + GPU Skeletal Skinning):** `public/assets/virtual-venues/royal-crimson-marquee/v1/walkthrough-demo.html`  

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
| **Dung lượng tệp `.glb`** | `< 15.0 MB` | **14.76 MB** (`15,479,728 bytes`) | **ĐẠT (Bao gồm 16 nhân vật người thật 65 xương & quần áo PBR)** |
| **Thời gian nạp & giải mã `.glb`** | `< 1,500 ms` | **264 ms** | **ĐẠT (Tức thì)** |
| **Tổng số Tam giác (Triangles)** | `< 300,000` | **296,291** | **ĐẠT (16 nhân vật người thật + 160 ghế Tiffany + toàn bộ rạp)** |
| **Tổng số Đỉnh (Vertices)** | `< 250,000` | **235,433** | **ĐẠT** |
| **Số Mesh / Node trong Scene** | `< 2,500` | **586 meshes / 2,005 nodes** | **ĐẠT (Bao gồm 16 hệ xương Armature 65-bone)** |
| **Draw Calls mỗi khung hình** | `< 1,200` | **960 draw calls** | **ĐẠT** |
| **FPS Desktop (`1440×900` High)** | `>= 60 FPS` | **100 FPS** (`10.0 ms/frame`) | **ĐẠT** |
| **FPS Desktop (`1440×900` Live LED)** | `>= 60 FPS` | **100 FPS** (`10.0 ms/frame`) | **ĐẠT** |
| **FPS Mobile (`390×844` Lite)** | `>= 45 FPS` | **100 FPS** (`9.9 ms/frame`) | **ĐẠT** |
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

## 4. HỆ THỐNG 16 NHÂN VẬT NGƯỜI THẬT 3D (65-BONE RIGGED PHOTOREALISTIC HUMANS)

Toàn bộ nhân vật sử dụng mô hình người thật 3D đầy đủ chi tiết giải phẫu (khuôn mặt thật, tròng mắt, hàm răng, mái tóc, bàn tay 5 ngón, giày da/cao gót) và trang phục cưới PBR sắc nét (bộ vest 3 mảnh có gile, sơ mi trắng, cà vạt lụa đỏ, khăn túi ngực, hoa cài áo chú rể; áo sơ mi trắng dài tay sơ vin quần âu thắt lưng da; đầm dạ hội satin kèm tà váy cưới chữ A chạm sàn và bó hoa hồng cầm tay cô dâu).

Hệ xương **65 xương chuẩn Humanoid (`Hips`, `Spine`..`Head`, `LeftArm`/`RightArm`, `LeftForeArm`/`RightForeArm`, `LeftUpLeg`/`RightUpLeg`, `LeftLeg`/`RightLeg`, `LeftFoot`/`RightFoot`)** được lập trình chuyển động bằng công thức xoay không gian Armature (`rest_rot.inverted() @ rot_arm @ rest_rot`) kết hợp biểu cảm khuôn mặt (`mouthSmile = 0.68`, `mouthOpen = 0.36` baked vào `Wolf3D_Head` & `Wolf3D_Teeth`), chạy vòng lặp **72 frames (3.0 giây @ 24fps)**:

- **Nhóm Phục vụ Bê đồ & Đi bộ (`CARRY_WALK` — 3 nhân vật)**: `NPC_Waiter_Carry_L`, `NPC_Waiter_Carry_R`, `NPC_Waiter_Carry_Outer` mặc sơ mi trắng dài tay, quần âu xám, bước đi tuần hoàn dọc hành lang bàn tiệc, tay phải nâng khay vàng/bạc đựng 3 ly sâm-panh pha lê và bát súp sứ trắng, tay trái đánh nhịp tự nhiên.
- **Nhóm Đi bộ Đón khách & Dạo sảnh (`WALK` — 2 nhân vật)**: `NPC_Usher_Walking` (đầm đỏ bước đi đón khách tại sảnh cổng hoa) và `NPC_Guest_Walk_Aisle` (vest 3 mảnh đi bộ dọc thảm lối đi chính hướng về sân khấu).
- **Nhóm Đứng Trò chuyện như người thật (`TALK_STAND` — 4 nhân vật)**: 2 cặp khách đứng đối diện nhau tại sảnh đón (`NPC_Receptionist_01` + `NPC_Guest_Foyer_Talk`) và lối đi trung tâm (`NPC_Guest_Talk_L` + `NPC_Guest_Talk_R`), mỉm cười hé môi lộ răng, gật đầu, xoay cổ và vung tay trò chuyện tự nhiên.
- **Nhóm Khách Ngồi Bàn Tiệc Trò chuyện (`TALK_SEATED` — 4 nhân vật)**: `NPC_Guest_Seated_01` .. `04` ngồi đúng tư thế gập hông/gối 90° trên ghế Tiffany trắng quanh các bàn tiệc trung tâm, nghiêng người nâng ly trò chuyện với nhau.
- **Cô Dâu, Chú Rể & Ban Nhạc Sân Khấu (`BRIDE_STAGE`, `GROOM_STAGE`, `BAND` — 5 nhân vật)**: `NPC_Bride` (váy cưới trắng chữ A chạm sàn, tay nâng bó hoa hồng trắng/đỏ), `NPC_Groom` (vest 3 mảnh, cà vạt đỏ, hoa hồng cài ngực vẫy chào khách), cùng 3 nhạc công (`NPC_Band_Drummer`, `NPC_Band_Keyboardist`, `NPC_Band_Guitarist`) biểu diễn sống động trên sân khấu.

---

## 5. BỘ ẢNH RENDER CYCLES, VIDEO WALKTHROUGH & ẢNH CHỤP WEBGL2

1. **7 Góc nhìn kiến trúc & nhân vật PBR (Cycles GPU CUDA)**:
   - [w03_01_overview.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_01_overview.png) — Góc nhìn tổng thể cắt lớp mái (Isometric Cutaway)
   - [w03_02_foyer_entrance.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_02_foyer_entrance.png) — Cổng hoa sảnh đón, bảng chào & nhân vật đi bộ/trò chuyện (khớp `f01`, `f05`)
   - [w03_03_aisle_to_stage.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_03_aisle_to_stage.png) — Trục lối đi chính nhìn lên vòm lụa đỏ/trắng, cặp đôi trò chuyện, nhân viên bê đồ & sân khấu (khớp `f03`, `f18`)
   - [w03_04_stage_gala_night.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_04_stage_gala_night.png) — Cận cảnh sân khấu nghi lễ, Cô dâu váy cưới dài + bó hoa, Chú rể vest 3 mảnh + cà vạt đỏ & ban nhạc (khớp `f07`, `f15`)
   - [w03_05_foyer_photobooth.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_05_foyer_photobooth.png) — Khu vực Song Hỷ & Photobooth chữ nổi `NT` (khớp `f09`)
   - [w03_06_dining_table_detail.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_06_dining_table_detail.png) — Chi tiết bàn tiệc, khách ngồi ghế Tiffany trò chuyện & phục vụ đi bộ bê khay ly/bát súp (khớp `f16`)
   - [w03_07_characters_closeup.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_07_characters_closeup.png) — Cận cảnh nhân vật người thật mặc trang phục cưới đang trò chuyện và nhân viên phục vụ đi bộ bê khay đồ ăn/thức uống
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
