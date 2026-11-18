# MÂY HẸN — VIRTUAL WEDDING WORLD 3D
## BLENDER ASSET INVENTORY & PBR VENUE SPECIFICATION (W03)

**Tên không gian:** Royal Crimson Marquee (Rạp Cưới Hoàng Gia Sắc Đỏ Cao Cấp)  
**Mã định danh:** `royal-crimson-marquee`  
**Phiên bản:** `v1`  
**Video tham chiếu:** `D:\File\FDown.vn_Tai_video_Facebook_720p (HD)_7b85.mp4` ("Lễ Tân Hôn — Cao Nhân & Trần Thảo", 29.09.2026, Monogram `NT`)  
**Tệp nguồn 3D (Blender):** `Blender/venues/royal-crimson-marquee/v1/mayhen_royal_crimson_marquee_v1.blend`  
**Tệp runtime WebGL (GLB):** `public/assets/virtual-venues/royal-crimson-marquee/v1/mayhen_royal_crimson_marquee_v1.glb`  
**Trạng thái nghiệm thu:** ĐÃ HOÀN THÀNH (100% Pure Local Assets, Zero External CDN, Cycles GPU Verified)

---

## 1. THÔNG SỐ HÌNH HỌC VÀ KHÔNG GIAN

| Hạng mục | Kích thước / Thông số | Ghi chú thiết kế |
| :--- | :--- | :--- |
| **Chiều ngang lòng rạp (Span)** | 18.00m | Khẩu độ khung giàn không cột giữa |
| **Chiều sâu tổng thể (Depth)** | 30.00m | Trục Y từ `-18.00m` (cổng vào) đến `+12.00m` (cuối sân khấu) |
| **Chiều cao giọt gianh (Eaves)** | 4.20m | Chiều cao cột biên chịu lực |
| **Chiều cao nóc rạp (Ridge)** | 6.20m | Đỉnh mái nhọn dốc thoát nước |
| **Lối đi trung tâm (Central Aisle)** | **2.20m** | Trục X từ `-1.10m` đến `+1.10m`, trải thảm nỉ xám phiến đá |
| **Khoảng cách hàng ghế** | **> 1.05m** | Không bao giờ xảy ra va chạm (Zero Mesh Collision) |
| **Sân khấu chính (Main Stage)** | 10.40m x 4.20m x 0.65m | Sàn gỗ tối màu, váy quây nhung đỏ |
| **Cầu dẫn catwalk (Catwalk)** | 2.40m x 1.80m x 0.45m | Nối liền thảm lối đi lên sân khấu |
| **Bàn tiệc tròn (Banquet Tables)** | 16 bàn (Ø1.50m) | 2 cánh tả/hữu x 4 hàng x 2 cột |
| **Ghế Tiffany hoàng gia** | **160 ghế** (10 ghế/bàn) | Hợp kim mạ vàng, đệm lụa ngà, nơ satin đỏ |

---

## 2. CHỈ SỐ TỐI ƯU HÓA WEBGL (BUDGET vs ACTUAL)

| Chỉ số | Ngưỡng trần WebGL Mobile 60FPS | Kết quả thực tế (Blender / GLB) | Đánh giá |
| :--- | :--- | :--- | :--- |
| **Tổng số đỉnh (Vertices)** | < 150,000 | **90,260** | **ĐẠT (An toàn 60%)** |
| **Tổng số đa giác (Faces)** | < 100,000 | **49,772** | **ĐẠT (Rất tối ưu)** |
| **Tổng số tam giác (Triangles)** | < 300,000 | **170,400** | **ĐẠT (Mượt mà)** |
| **Kích thước file GLB** | < 25.0 MB | **12.37 MB** (`12,373,528 bytes`) | **ĐẠT (Tải nhanh)** |
| **Thời gian nạp ước tính (4G)** | < 2.5s | ~ 1.8s (kèm HTTP Range/Cache) | **Tối ưu** |
| **Vật liệu PBR** | Max 24 materials | **17 PBR Principled BSDF** | **Đạt chuẩn** |
| **Tài nguyên ảnh Texture** | 100% Local PNG | **8 textures (tổng 1.82 MB)** | **Zero CDN** |

---

## 3. DANH MỤC VẬT LIỆU PBR (PBR MATERIAL LIBRARY)

1. **`Mat_Crimson_Velvet`**: Vải nhung đỏ hoàng gia, Base `#7D1224`, Roughness 0.82, Sheen 1.0 (crimson tint), Subsurface 0.08. Dùng cho rèm trần, vách sân khấu, khăn bàn đón tiếp.
2. **`Mat_Ivory_Silk`**: Lụa tơ tằm ngà, Base `#F5EFEB`, Roughness 0.52, Sheen 0.75. Dùng cho dải lụa võng trần, đệm ghế Tiffany, khăn phủ bàn tiệc.
3. **`Mat_Starlight_Liner`**: Bạt đen trần cách âm phủ texture hạt sợi quang (`tex_starlight_velvet.png`), Base Color + Emission 2.4 tạo bầu trời sao lấp lánh như video f07.
4. **`Mat_Carpet_Slate`**: Thảm nỉ lối đi và sảnh đón màu xám phiến đá (`tex_carpet_slate.png`), Roughness 0.92.
5. **`Mat_Turf_Grass`**: Cỏ nhân tạo sân vườn bên ngoài rạp với Albedo (`tex_turf_albedo.png`) và Normal map (`tex_turf_normal.png`), Normal Strength 0.85.
6. **`Mat_Gold_Brass`**: Kim loại đồng thau mạ vàng bóng sang trọng, Metallic 0.95, Roughness 0.20, Base `#D4AF37`. Dùng cho khung ghế Tiffany, trụ hoa, khung tranh, monogram Song Hỷ.
7. **`Mat_Crystal_Glass`**: Pha lê quang học khúc xạ cao, Transmission 1.0, IOR 1.52, Roughness 0.04, Specular 0.95. Dùng cho 8 chùm đèn pha lê, tháp ly sâm-panh và bình hoa thủy tinh.
8. **`Mat_Chandelier_Bulb`**: Bóng nến sợi đốt ấm cúng bên trong đèn chùm, Emission Color (1.0, 0.88, 0.65), Emission Strength 5.0.
9. **`Mat_LED_Screen`**: Màn hình LED sân khấu 16:9 hiển thị màn hình chờ trung thực (`tex_led_idle_slate.png` - "CHƯƠNG TRÌNH TRỰC TIẾP CHƯA BẮT ĐẦU"), Emission Strength 3.2. Không giả mạo nhãn LIVE.
10. **`Mat_Welcome_Left`**: Bảng chào terracotta bên trái (`tex_welcome_left.png` - "LỄ TÂN HÔN - CAO NHÂN & TRẦN THẢO - 29.09.2026").
11. **`Mat_Welcome_Right`**: Bảng chào terracotta bên phải (`tex_welcome_right.png` - "NHÀ CÓ HỶ" kèm đồ họa nút thắt đồng tâm bất tận).
12. **`Mat_Couple_Canvas`**: Tranh chân dung cưới mỹ thuật (`tex_couple_canvas.png`) đặt trên giá vẽ nghệ thuật tại sảnh đón.
13. **`Mat_Truss_Silver`**: Giàn không gian nhôm định hình chịu lực, Metallic 0.88, Roughness 0.32, Base `#C0C4C8`.
14. **`Mat_Deck_Wood`**: Sàn gỗ chịu lực sự kiện tối màu, Roughness 0.38, Specular 0.55.
15. **`Mat_Rose_Cream` / `Mat_Rose_Crimson` / `Mat_Foliage_Eucalyptus`**: Bộ 3 vật liệu hoa hồng kem, hoa hồng đỏ nhung và lá khuynh diệp cho toàn bộ cụm hoa cổng, hoa bàn và đôn hoa.
16. **`Mat_NPC_*`**: Bộ vật liệu trang phục nhân vật: Áo dài lụa đỏ (`Mat_NPC_AoDai_Red`), Tuxedo đen (`Mat_NPC_Dark_Suit`), Váy cưới trắng tinh khôi (`Mat_NPC_Bridal_Gown`), Da người ấm áp (`Mat_NPC_Skin`).
17. **`Mat_Porcelain`**: Bộ đĩa chén sứ tiệc cưới trắng bóng, Roughness 0.12.

---

## 4. HỆ THỐNG ÁNH SÁNG 5 KHUNG GIỜ (5 TIME-OF-DAY LIGHTING PRESETS)

1. **`05:00 Bình Minh (Dawn)`**:
   - Bầu trời xanh tím dịu nhẹ (`Color: 0.24, 0.28, 0.44`).
   - Mặt trời góc thấp sát chân trời (82°), ánh hồng cam (`Energy: 1.2`).
   - Đèn chùm bên trong rạp bật nhẹ 35% (`Energy: 100W`) tạo không khí chuẩn bị đón khách sớm.
2. **`08:00 Buổi Sáng (Morning)`**:
   - Bầu trời trong xanh tươi sáng (`Color: 0.75, 0.85, 0.98`).
   - Ánh nắng vàng trong trẻo góc 65° (`Energy: 3.5`).
   - Không gian rạp đón ánh sáng tự nhiên tràn qua cửa đón tiếp.
3. **`12:00 Buổi Trưa (Noon)`**:
   - Mặt trời đứng bóng góc 20° (`Energy: 4.8`).
   - Bên ngoài bóng đổ gắt; bên trong rạp bạt đen cản nắng hoàn toàn, đèn chùm và đèn rọi tạo không gian mát mẻ cho tiệc trưa.
4. **`17:00 Hoàng Hôn (Sunset)`**:
   - Bầu trời cam đỏ rực rỡ (`Color: 0.55, 0.32, 0.28`).
   - Ánh nắng xiên góc thấp 80° (`Energy: 2.2`), bóng đổ dài lãng mạn qua cổng hoa sảnh đón.
   - Đèn chùm và đôn hoa lối đi bắt đầu tăng sáng 80%.
5. **`20:00 Dạ Tiệc Đêm (Night Gala - Chuẩn theo Video gốc)`**:
   - Bầu trời đêm sâu thẳm (`Color: 0.015, 0.018, 0.035`).
   - Mặt trời tắt hẳn (`Energy: 0.0`).
   - Toàn bộ 8 đèn chùm pha lê rực sáng (`Energy: 280W`), đèn rọi sân khấu (Stage Beam Spotlights 450W), trần sao lung linh (Starlight LEDs) và 2 màn hình LED tỏa sáng lộng lẫy đúng chuẩn dạ tiệc cưới xa hoa.

---

## 5. DANH SÁCH 8 NHÂN VẬT DEMO (CHARACTER NPCS)

1. **`NPC_Bride` (Cô dâu)**: Đứng tại trung tâm sân khấu, váy cưới xòe đuôi dài, khăn voan cài tóc, tay cầm bó hoa cưới hồng kem.
2. **`NPC_Groom` (Chú rể)**: Đứng cạnh cô dâu trên sân khấu, veston tuxedo đen lịch lãm, hoa cài áo đỏ nhung.
3. **`NPC_Receptionist` (Lễ tân đón khách)**: Đứng tại bàn đón tiếp sảnh vào (`Y = -14.5m`), áo dài truyền thống gấm đỏ, tư thế cúi chào đón khách.
4. **`NPC_Usher` (Nhân viên dẫn lối)**: Đứng đầu thảm lối đi (`Y = -16.5m`), hướng dẫn khách mời vào bàn tiệc.
5. **`NPC_Server` (Nhân viên phục vụ)**: Đang di chuyển giữa các hàng bàn tiệc (`Y = -4.5m`), bưng khay đồ uống.
6. **`NPC_Keyboardist` (Nhạc công organ)**: Cánh trái sân khấu phía sau (`X = -3.8m, Y = 8.3m`), bên đàn piano điện trên chân đế chuyên nghiệp.
7. **`NPC_Drummer` (Nhạc công trống)**: Cánh phải sân khấu phía sau (`X = 3.8m, Y = 8.5m`), bên dàn trống acoustic đầy đủ trống bass, snare, cymbals.
8. **`NPC_Guest_Demo` (Khách mời)**: Đang ngồi tại Bàn số 1 gần lối đi trung tâm.

---

## 6. HÌNH ẢNH NGHIỆM THU RENDER THỰC TẾ (CYCLES GPU)

Toàn bộ ảnh kết xuất được lưu trữ trong thư mục: `docs/virtual-wedding-3d/renders/w03-pbr/`

### 6.1. Bốn góc nhìn kiến trúc then chốt (Night Gala Lighting)
- **Góc 1: Tổng thể cắt lớp (Isometric Cutaway)**: [w03_01_overview.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_01_overview.png)
- **Góc 2: Cổng hoa & sảnh đón (Foyer Entrance)**: [w03_02_foyer_entrance.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_02_foyer_entrance.png)
- **Góc 3: Lối đi trung tâm nhìn lên sân khấu (Aisle To Stage)**: [w03_03_aisle_to_stage.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_03_aisle_to_stage.png)
- **Góc 4: Toàn cảnh dạ tiệc sân khấu (Stage Gala Night)**: [w03_04_stage_gala_night.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_04_stage_gala_night.png)

### 6.2. Năm khung giờ ánh sáng (Chụp từ trục lối đi trung tâm)
- **05:00 Bình Minh**: [w03_lighting_01_dawn_05h.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_lighting_01_dawn_05h.png)
- **08:00 Buổi Sáng**: [w03_lighting_02_morning_08h.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_lighting_02_morning_08h.png)
- **12:00 Buổi Trưa**: [w03_lighting_03_noon_12h.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_lighting_03_noon_12h.png)
- **17:00 Hoàng Hôn**: [w03_lighting_04_sunset_17h.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_lighting_04_sunset_17h.png)
- **20:00 Dạ Tiệc Đêm**: [w03_lighting_05_night_20h.png](file:///d:/Project/Laravel/mayhen.vn/docs/virtual-wedding-3d/renders/w03-pbr/w03_lighting_05_night_20h.png)
