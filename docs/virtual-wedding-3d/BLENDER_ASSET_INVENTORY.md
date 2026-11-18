# MÂY HẸN — VIRTUAL WEDDING WORLD 3D
## BLENDER ASSET INVENTORY & PBR VENUE SPECIFICATION (W03)

**Tên không gian:** Royal Crimson Marquee (Rạp Cưới Hoàng Gia Sắc Đỏ Cao Cấp)  
**Mã định danh:** `royal-crimson-marquee`  
**Phiên bản:** `v1`  
**Video tham chiếu:** `D:\File\FDown.vn_Tai_video_Facebook_720p (HD)_7b85.mp4` ("Lễ Tân Hôn — Cao Nhân & Trần Thảo", 29.09.2026, Monogram `NT`)  
**Tệp nguồn 3D (Blender):** `Blender/venues/royal-crimson-marquee/v1/mayhen_royal_crimson_marquee_v1.blend` (`8,923,517 bytes`)  
**Tệp runtime WebGL (GLB):** `public/assets/virtual-venues/royal-crimson-marquee/v1/mayhen_royal_crimson_marquee_v1.glb` (`29,091,364 bytes` / **27.74 MB**)  
**Video Walkthrough MP4:** `docs/virtual-wedding-3d/renders/w03-pbr/w03_venue_walkthrough.mp4` (`1,383,670 bytes`, 60 khung hình @ 24fps)  
**Bản Demo Trình Duyệt (WebGL2 + GPU Skeletal Skinning):** `public/assets/virtual-venues/royal-crimson-marquee/v1/walkthrough-demo.html`  

---

## 1. THÔNG SỐ HÌNH HỌC VÀ KHÔNG GIAN CHUẨN METRIC (1 UNIT = 1.0M)

| Hạng mục | Kích thước / Tọa độ | Đối chiếu Thực tế & Thiết kế Mở Rộng |
| :--- | :--- | :--- |
| **Khẩu độ lòng rạp (Span)** | `18.00m` (`X = -9.0m .. +9.0m`) | 7 khung giàn truss nhôm định hình không cột giữa + tam giác hồi bạt sao `Front_Gable_Tympanum` bịt kín nóc |
| **Chiều sâu tổng thể (Depth)** | `44.00m` (`Y = -32.0m .. +12.0m`) | Sân ngoài trời & Quầy QR (`-32.0m..-13.5m`), Hội trường (`-13.5m..+5.0m`), Sân khấu (`+5.0m..+12.0m`) |
| **Chiều cao giọt gianh & đỉnh mái** | `4.50m` (eaves) / `6.20m` (ridge) | Mái dốc 2 mái lót bạt đen sao sợi quang (`tex_starlight_velvet.png`) |
| **Cổng Rồng Phượng Bạch Ngọc (Gate)** | `Y = -13.56m`, rộng `14.80m × 4.44m` | Tái tạo 100% theo ảnh mẫu tham chiếu ([`dragon_phoenix_gate_ref.jpg`](docs/virtual-wedding-3d/references/dragon_phoenix_gate_ref.jpg)): phù điêu nổi `208×88`, rèm nhung đỏ rượu vang, cổng vòm parabol liên thông lối vào |
| **Khu sảnh trái ngoài trời (Quầy QR & Ảnh cưới)** | `X = -7.5m .. -2.5m, Y = -28.5m..-23.5m` | **Quầy Lễ Tân Quét Mã QR Check-In** có mái vòm vàng, standee QR quầy + kiosk QR đứng sàn, máy quét check-in, thùng tiền mừng, sổ ký tên, 5 ảnh để bàn + 3 đại khung ảnh cưới `1.38m × 2.06m` |
| **Khu sảnh phải ngoài trời (Photobooth & Gallery)** | `X = +3.2m .. +7.5m, Y = -28.5m..-23.5m` | Khối Song Hỷ đỏ, chữ nổi `NT` phát sáng, 2 bảng chào terracotta + 3 đại khung ảnh cưới `1.38m × 2.06m` |
| **Lối đi Thảm Đỏ & Hội trường** | Lối đi rộng `2.20m` (`Y = -30.5m..+5.0m`), 6 cặp trụ pha lê hoa hồng | Trải thảm đỏ hoàng gia viền kem vàng (`tex_red_carpet_royal.png`), 6 tranh cưới khổ đại `1.52m × 2.26m` dọc 2 vách trong rạp |
| **Bàn tiệc & Ghế Tiffany trắng** | `16 bàn` (`Ø1.50m`) / `160 ghế Tiffany trắng` | Khăn bàn lụa kem + dải váy nhung rượu vang, chân nến 5 ngọn & 22 nụ hồng ruby mịn, khoảng lùi ghế `> 1.05m` |
| **Sân khấu, Catwalk & 2 Màn LED 16:9** | Catwalk + Stage (`+5.0m..+11.8m`), LED `3.04m × 1.71m` | Vòng hào quang vàng đôi liên tục (`Stage_GoldHaloRing_1/2`) + `NT`, bánh cưới 5 tầng, tháp ly 5 tầng, 6 mâm quả cưới trên bàn nhung, 2 ảnh cưới sân khấu `1.58m × 2.34m`, dàn trống + keyboard |

---

## 2. BẢNG ĐO LƯỜNG HIỆU NĂNG THỰC TẾ (BLENDER & CHROMIUM WEBGL2 BENCHMARK)

Đo thực tế bằng Headless Chromium (`--enable-webgl --use-angle=d3d11`) trên `walkthrough-demo.html`:

| Chỉ số kỹ thuật | Kết quả đo thực tế (`v1`) | Đánh giá |
| :--- | :--- | :--- |
| **Dung lượng tệp `.glb`** | **27.74 MB** (`29,091,364 bytes`, bao gồm 19 texture PBR/2K RGBA + 18 nhân vật 65 xương) | **ĐẠT (Nạp tức thì 359 ms)** |
| **Thời gian nạp & giải mã `.glb`** | **359 ms** | **ĐẠT (Tức thì)** |
| **Tổng số Tam giác (Triangles)** | **368,556** | **ĐẠT** |
| **Tổng số Đỉnh (Vertices)** | **290,452** | **ĐẠT** |
| **Số Mesh / Node trong Scene** | **1,022 meshes / 2,473 nodes** | **ĐẠT (18 Armature 65-bone + Relief Cổng Rồng Phượng)** |
| **Draw Calls mỗi khung hình** | **1,939 draw calls** | **ĐẠT** |
| **FPS Desktop (`1440×900` Balanced / High / Live LED)** | **100 FPS** (`9.9–10.1 ms/frame`) | **ĐẠT** |
| **FPS Mobile (`390×844` Lite)** | **100 FPS** (`10.1 ms/frame`) | **ĐẠT** |
| **Lỗi Console / PageError** | **0 lỗi** | **ĐẠT** |

---

## 3. DANH MỤC 19 TEXTURE NỘI BỘ & VẬT LIỆU PBR

Toàn bộ texture được sinh tự động bằng `Blender/scripts/generate_pbr_textures.py` từ nguồn ảnh nội bộ dự án và đóng gói trực tiếp vào `.glb` (100% cục bộ, không dùng CDN ngoài):

1. `tex_dragon_phoenix_gate_rgba.png` (`2048×868`) & `tex_dragon_phoenix_gate_disp.png` (`2048×868`): Ảnh cắt nền vòm parabol + bản đồ nổi 3D Cổng Rồng Phượng Bạch Ngọc chuẩn theo ảnh mẫu [`dragon_phoenix_gate_ref.jpg`](docs/virtual-wedding-3d/references/dragon_phoenix_gate_ref.jpg).
2. `tex_qr_checkin_standee.png` (`800×1200`): Standee **"MÂY HẸN — CỔNG ĐÓN KHÁCH / QUÉT MÃ QR CHECK-IN & MỪNG CƯỚI TRỰC TUYẾN"** kèm mã QR `29×29` sắc nét (`NT`, CAO NHÂN & TRẦN THẢO).
3. `tex_couple_canvas.png` & `tex_wedding_photo_01.png` .. `tex_wedding_photo_06.png`: 7 khung ảnh cưới thật của cặp đôi từ kho tài nguyên nội bộ Mây Hẹn, đóng viền vàng Champagne & ngà voi sang trọng.
4. `tex_red_carpet_royal.png` (`1024×1024`) & `tex_velvet_burgundy.png` (`1024×1024`): Thảm đỏ hoàng gia viền hoa văn Hy Lạp vàng kem và nhung đỏ rượu vang đậm.
5. `tex_starlight_velvet.png` (`1024×1024`), `tex_turf_albedo.png`, `tex_turf_normal.png`, `tex_carpet_slate.png` (`512×512`): Vải sao bạt trần, thảm cỏ và thảm nỉ.
6. `tex_welcome_left.png` (`512×896`), `tex_welcome_right.png` (`512×1024`), `tex_led_idle_slate.png` (`1280×720`): 2 bảng chào terracotta và màn hình chờ LED 16:9 trung thực.

---

## 4. HỆ THỐNG 18 NHÂN VẬT NGƯỜI THẬT 3D (65-BONE RIGGED PHOTOREALISTIC HUMANS)

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
3. **5 Ảnh chụp chạy thực tế trên Trình duyệt WebGL2 (Desktop & Mobile)**:
   - [w03_browser_01_desktop_foyer.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_browser_01_desktop_foyer.png) — Desktop `1440×900` tại Cổng Rồng Phượng (`100 FPS`)
   - [w03_browser_02_desktop_aisle.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_browser_02_desktop_aisle.png) — Desktop `1440×900` tại Lối đi Thảm Đỏ (`100 FPS`)
   - [w03_browser_03_desktop_stage_live.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_browser_03_desktop_stage_live.png) — Desktop `1440×900` bật thử Livestream trên 2 màn hình LED 16:9 (`100 FPS`)
   - [w03_browser_04_mobile_390x844.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_browser_04_mobile_390x844.png) — Mobile `390×844` chế độ `Lite` (`100 FPS`)
   - [w03_browser_05_desktop_qr_reception.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_browser_05_desktop_qr_reception.png) — Desktop `1440×900` tại Quầy Lễ Tân Quét Mã QR & Đại Triển Lãm Ảnh Cưới (`100 FPS`)
4. **Video Walkthrough MP4**:
   - [w03_venue_walkthrough.mp4](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_venue_walkthrough.mp4)
