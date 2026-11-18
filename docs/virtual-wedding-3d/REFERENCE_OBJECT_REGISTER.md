# HỒ SƠ KIỂM KÊ VẬT THỂ THAM CHIẾU & QUY CHUẨN TÁI DỰNG 3D (REFERENCE OBJECT REGISTER)

> Mọi vật thể dưới đây đều được kiểm kê từ 18 khung hình chuẩn của video gốc (`f01`–`f18`), định danh rõ nguồn gốc (**`[SRC]` Có trong video gốc** vs **`[SUP]` Thiết kế bổ sung có chủ đích**), kích thước mét thực tế (`W × D × H`), vật liệu PBR, chiến lược tối ưu (`Instancing` / `LOD`) và trạng thái hoàn thiện trong `.blend` / `.glb`.

---

## 1. Nhóm Kiến Trúc Rạp, Mái, Khung Truss & Sàn (`ENVIRONMENT`)

| Mã vật thể | Tên vật thể | Nguồn | Khu vực (`Zone`) | Kích thước thực (`m`) | Vật liệu PBR (`Material`) | LOD & Tối ưu WebGL | Ảnh đối chiếu | Trạng thái |
| :--- | :--- | :---: | :--- | :--- | :--- | :--- | :---: | :---: |
| `ENV-001` | Khung giàn Truss thép hộp chịu lực (7 cổng vòm chữ A + giằng dọc) | `[SRC]` | Toàn rạp (`Y=-18..+12`) | Khẩu độ `18,0 × 30,0 × 6,2` (tiết diện truss `0,35 × 0,35`) | `MAT_Steel_Truss_Galvanized` (`Metallic=0.88, Roughness=0.32`) | Dùng chung mesh cột/kèo lặp theo bước cột `5,0m` | `f11`, `f12` | Hoàn thiện W02–W03 |
| `ENV-002` | Mái bạt PVC trắng chống nắng mưa ngoài cùng | `[SRC]` | Mái trên khung truss | `18,8 × 30,6`, dốc hai mái `14°` | `MAT_PVC_Canopy_White` (`Roughness=0.55`) | Mesh 1 lớp nhẹ (`< 60 tris`) | `f11` | Hoàn thiện W02–W03 |
| `ENV-003` | Trần & Vách bạt nhung đen bầu trời sao (`Starlight Blackout Liner`) | `[SRC]` | Đại sảnh & hông tiền sảnh | Bao phủ `18,0 × 25,5 × 5,8` | `MAT_Starlight_Velvet_Black` (Đen nhung `Roughness=0.92` + điểm sao phát sáng nhẹ) | Tích hợp texture/puntos sao nhẹ không tốn draw call | `f02`, `f05`, `f18` | Hoàn thiện W03 |
| `ENV-004` | Vách rèm nhung đỏ ngăn Tiền sảnh – Đại sảnh (có cổng vòm giữa) | `[SRC]` | `Y = -13,5 m` | Rộng `18,0 × 0,25 × 4,5`, cổng giữa `3,2 × 3,15` | `MAT_Crimson_Velvet_Pleated` (`#8C0F26`, sóng xếp ly hình học thật + Roughness `0.78`) | Dựng sóng hình học tần số vừa kết hợp normal mềm | `f01`, `f11`–`f14` | Hoàn thiện W03 |
| `ENV-005` | Thảm cỏ nhân tạo xanh đón khách & hai cánh bàn tiệc | `[SRC]` | Sân trước & 2 cánh sảnh | `22,0 × 34,0` | `MAT_Artificial_Turf_Green` (`#2B572C`, `Roughness=0.88`, micro-bump cỏ) | 1 mặt phẳng chia lưới chuẩn | `f11`, `f16`, `f17` | Hoàn thiện W03 |
| `ENV-006` | Thảm nỉ xanh xám Tiền sảnh & Lối đi chính trung tâm | `[SRC]` | Tiền sảnh + Trục `X=0` | Tiền sảnh `18,0 × 4,5`; Lối đi `2,2 × 19,0` | `MAT_Event_Carpet_SlateBlue` (`#5E6C80`, `Roughness=0.86`) | Tách cao độ `+0,008m` chống z-fighting tuyệt đối | `f01`, `f05`, `f18` | Hoàn thiện W03 |
| `ENV-007` | Sân nền ngoại cảnh, lối dạo đá & hàng cây cảnh hai bên mặt tiền | `[SUP]` | Ngoại cảnh `Y < -18,0m` | `36,0 × 46,0` | `MAT_Exterior_Paving_Warm`, `MAT_Foliage_Boxwood` | Low-poly cây cảnh tạo khung chân trời tự nhiên | `f11` (mở rộng từ góc sân thật) | Hoàn thiện W03 |

---

## 2. Nhóm Cổng Chào, Khu Tiếp Tân & Bục Chụp Ảnh (`ENTRANCE` / `FOYER`)

| Mã vật thể | Tên vật thể | Nguồn | Khu vực (`Zone`) | Kích thước thực (`m`) | Vật liệu PBR (`Material`) | LOD & Tối ưu WebGL | Ảnh đối chiếu | Trạng thái |
| :--- | :--- | :---: | :--- | :--- | :--- | :--- | :---: | :---: |
| `ENT-001` | Cổng vòm hoa trung tâm bất đối xứng + bảng tên trắng đỉnh vòm | `[SRC]` | `X=0, Y=-13,65m` | Khung `4,2 × 0,9 × 3,65` (thông thủy `3,2 × 3,15`) | Hoa 3 sắc (`MAT_Floral_Crimson`, `MAT_Floral_Ivory`, `MAT_Floral_Blush`) + `MAT_Sign_Plaque_Ivory` | Gom cụm cầu hoa hữu cơ đa kích cỡ, tránh lặp đều | `f01`, `f13`, `f14` | Hoàn thiện W03 |
| `ENT-002` | Bàn lễ tân trưng khung ảnh & thùng tiền mừng cưới | `[SRC]` | Tiền sảnh trái (`X=-5,6m, Y=-14,4m`) | `2,40 × 0,75 × 0,76` | Khăn satin kem hồng `MAT_Tablecloth_ChampagneBlush` + dải nhung đỏ + thùng trắng + 6 khung ảnh kính | Gộp khung ảnh tĩnh giảm draw call | `f05`, `f11` | Hoàn thiện W03 |
| `ENT-003` | Bộ đôi bảng đón khách lệch tầng (*Lễ Tân Hôn* & *Nhà Có Hỷ* + nút đồng tâm) | `[SRC]` | Tiền sảnh trái (`X=-3,1m, Y=-14,2m`) | Bảng thấp `0,85 × 1,55`; Bảng cao `0,85 × 1,90` | `MAT_WelcomeBoard_Terracotta` (`#C85A48`, `Roughness=0.42`) + nút đồng tâm nổi trắng | Geometry vát cạnh + họa tiết nút thắt nổi 3D | `f01`, `f05`, `f11` | Hoàn thiện W03 |
| `ENT-004` | Giá vẽ gỗ chữ A & Khung ảnh cưới đứng đón khách | `[SRC]` | Tiền sảnh trái (`X=-2,4m, Y=-14,8m`) | Giá gỗ cao `1,48`; Khung ảnh `0,65 × 0,95` | `MAT_Wood_Pine_Natural` + `MAT_Photo_Canvas_Gloss` | Low-poly sạch `< 180 tris` | `f05`, `f11`, `f12` | Hoàn thiện W03 |
| `ENT-005` | Phù điêu chữ Song Hỷ `囍` đỏ nhung khổ lớn | `[SRC]` | Tiền sảnh phải (`X=+2,7m, Y=-13,6m`) | `1,25 × 0,12 × 1,35` | `MAT_Crimson_Velvet_Bright` (`#B1122A`) | Extrude khối chữ `囍` chuẩn tỷ lệ | `f01`, `f12`, `f13` | Hoàn thiện W03 |
| `ENT-006` | Bục Photobooth thảm đen, phông yếm lụa đỏ & chữ nổi `NT` kèm thảm hoa | `[SRC]` | Tiền sảnh phải (`X=+5,4m, Y=-14,3m`) | Bục `4,20 × 1,40 × 0,12`; Chữ `NT` cao `1,05` | `MAT_Carpet_Stage_Black`, `MAT_MagentaRed_Silk`, `MAT_Monogram_IvoryGold` | Bục có vát mép an toàn, cụm hoa chân chữ `NT` | `f09`, `f11`, `f13` | Hoàn thiện W03 |

---

## 3. Nhóm Sân Khấu, Sàn Catwalk, Nhạc Cụ & Màn Hình LED (`CEREMONY`)

| Mã vật thể | Tên vật thể | Nguồn | Khu vực (`Zone`) | Kích thước thực (`m`) | Vật liệu PBR (`Material`) | LOD & Tối ưu WebGL | Ảnh đối chiếu | Trạng thái |
| :--- | :--- | :---: | :--- | :--- | :--- | :--- | :---: | :---: |
| `CER-001` | Sân khấu chính + Sàn Catwalk chữ T 2 bậc thảm đen | `[SRC]` | `Y=+5,2..+11,8m` | Sân khấu `10,4 × 3,4 × 0,65`; Catwalk `3,0 × 3,3 × 0,45` | `MAT_Carpet_Stage_Black` (`Roughness=0.90`) + gờ viền kim loại tối | Có bậc lên hợp chuẩn `0,22m`/bậc | `f07`, `f15`, `f16` | Hoàn thiện W02–W03 |
| `CER-002` | Phông nhung đỏ trung tâm sân khấu + Monogram `NT` phát sáng | `[SRC]` | `X=0, Y=+11,6m` | Rèm `9,6 × 4,4`; Chữ `NT` `1,15 × 0,95` | `MAT_Crimson_Velvet_Pleated` + `MAT_Emissive_WarmGold` (`Strength=2.4`) | Chữ `NT` nổi khối 3D thật | `f07`, `f08`, `f15` | Hoàn thiện W03 |
| `CER-003` | Đôi tháp hoa nghệ thuật bất đối xứng & mành tua trắng trên sân khấu | `[SRC]` | `X=±2,1m, Y=+10,8m` | Cao `2,45` & `2,80`, rộng `1,10` | Hoa đỏ-trắng-hồng phấn + mành lụa trắng `MAT_Silk_Tassel_Ivory` | Đa dạng chiều cao đúng ảnh `f07` | `f07`, `f08`, `f15` | Hoàn thiện W03 |
| `CER-004` | Bục & Tháp bánh cưới 3 tầng khung sắt uốn (cánh trái) | `[SRC]` | `X=-3,7m, Y=+9,4m` | Bục `0,90 × 0,90 × 0,85`; Kệ bánh cao `0,75` | Khăn đỏ + khung sắt trắng `MAT_WroughtIron_White` + kem bánh hồng trắng | Chi tiết vừa phải, nổi bật silhoutte | `f07`, `f08` | Hoàn thiện W03 |
| `CER-005` | Bục & Tháp ly Champagne 5 tầng đế phát sáng (cánh phải) + Đôn tráp hỏi | `[SRC]` | `X=+3,7m, Y=+9,4m` | Bục `1,00 × 0,90 × 0,85`; 30 ly thủy tinh xếp tháp | `MAT_Glass_Champagne` (`Transmission/Alpha + IOR=1.5, Roughness=0.06`) + đế LED | Ly gom chung 1 mesh giảm draw call | `f07`, `f08`, `f15` | Hoàn thiện W03 |
| `CER-006` | Bộ nhạc cụ Ban nhạc sống (Trống Jazz, Đàn Organ/Keyboard, Loa Monitor) | `[SRC]` | Trái & Phải sân khấu | Bộ trống `1,6 × 1,2 × 1,25`; Đàn phím `1,3 × 0,45 × 0,85` | `MAT_DrumShell_Black`, `MAT_Brass_Cymbal` (`Metallic=0.92`), `MAT_Metal_Chrome` | Low-poly chuẩn dáng nhạc cụ | `f07`, `f15` | Hoàn thiện W03 |
| `CER-007` | **Cặp Màn hình LED Livestream 16:9 hai cánh sân khấu** (`LED_Screen_Left` / `Right`) | `[SUP]` | `X=±4,9m, Y=+10,6m, Z=2,35m` | Mỗi màn **`3,20 × 1,80`** (tỷ lệ chuẩn `16:9`, khung viền `0,08m`) | Khung `MAT_Frame_BrushedGoldBlack` + Mặt chiếu `MAT_LED_Screen_Surface` (UV `[0..1]` nguyên vẹn để gán `VideoTexture`) | Tách object riêng trong collection `LED_SCREENS` để Three.js truy cập tức thì | Bổ sung theo Phần VI & XVII.8 (giữ nguyên rèm `NT` giữa!) | Hoàn thiện W03 |

---

## 4. Nhóm Trần Lụa Nghệ Thuật, Đèn Chùm, Đèn Sân Khấu & Âm Thanh (`DECOR` & `LIGHTING`)

| Mã vật thể | Tên vật thể | Nguồn | Khu vực (`Zone`) | Kích thước thực (`m`) | Vật liệu PBR (`Material`) | LOD & Tối ưu WebGL | Ảnh đối chiếu | Trạng thái |
| :--- | :--- | :---: | :--- | :--- | :--- | :--- | :--- | :---: |
| `DEC-001` | Hệ dải lụa kem uốn lượn tầng cánh sen dọc trục giữa & tiền sảnh | `[SRC]` | Trần `Y=-17..+4m`, `Z=3,9..5,6m` | 14 dải uốn cong đối xứng dài `6m–14m`, rủ sâu `0,9m–1,5m` | `MAT_Ceiling_Silk_Cream` (`#F5EDE0`, `Roughness=0.68`, truyền sáng ấm nhẹ) | Dựng bằng ribbon có độ dày/nếp uốn cong mượt, không phẳng cứng | `f01`, `f02`, `f10`, `f18` | Hoàn thiện W03 |
| `DEC-002` | Vòm lụa nhung đỏ 12 múi tròn trung tâm (`Crimson Lotus Dome`) | `[SRC]` | Trần trung tâm `X=0, Y=+1,5m, Z=4,3..5,6m` | Đường kính vành ngoài `4,80 m`, tụ tâm tại đèn chùm giữa | `MAT_Crimson_Satin_Canopy` (`#8E0B22`, `Roughness=0.48`, sheen lụa) | 12 múi lụa catenary võng tự nhiên theo trọng lực | `f03`, `f04`, `f16` | Hoàn thiện W03 |
| `DEC-003` | Hệ 16 dải lụa kem võng hai cánh bàn tiệc (`Side Wing Swags`) | `[SRC]` | Trần hai cánh `X=±2,5..±8,5m` | Mỗi dải dài `5,8m`, độ võng `0,85m` | `MAT_Ceiling_Silk_Cream` | Instancing đối xứng trái/phải | `f02`, `f17`, `f18` | Hoàn thiện W03 |
| `LGT-001` | Hệ 8 Đèn chùm pha lê 8 tay nến (1 trung tâm + 1 đầu sảnh + 6 hai cánh) | `[SRC]` | Treo trần `Z=3,55..3,95m` | Đường kính `0,95 m`, cao `1,05 m` | `MAT_Crystal_Clear` + `MAT_Gold_Champagne` + đầu nến `MAT_Emissive_CandleWarm` | Instancing cùng 1 prototype đèn chùm | `f02`–`f04`, `f16`–`f18` | Hoàn thiện W03 |
| `LGT-002` | Cụm vòng kim loại vàng phát sáng, dây hoa trắng & cầu thủy tinh hổ phách | `[SRC]` | Dọc trục giữa & tiền sảnh | Vòng `Ø0,26m`, cầu `Ø0,18m`, dây hoa dài `0,9..1,6m` | `MAT_Gold_Ring_Glow`, `MAT_Amber_Glass_Glow`, `MAT_Floral_White_Strand` | Gom theo cụm treo mô-đun | `f03`, `f07`, `f14` | Hoàn thiện W03 |
| `LGT-003` | Giàn 12 Đèn Par LED ấm + 6 Đèn Moving-Head Beam trần + 2 Đèn Beam sàn `DTE` + nón tia sáng thể tích mềm | `[SRC]` | Sân khấu `Y=+8,4..+11,2m` | Đèn Beam `0,32 × 0,28 × 0,48`; nón tia sáng dài `5,5m` | Vỏ `MAT_Stage_Plastic_Black` + Nón tia sáng `MAT_Volumetric_Beam_Cone` (Alpha Blend nhẹ không chói) | Bật/tắt cường độ theo trạng thái thời gian trên Web | `f07`, `f08`, `f15` | Hoàn thiện W03 |
| `AUD-001` | Cặp loa thùng sân khấu & 4 Quạt điều hòa hơi nước công nghiệp hai hông | `[SRC]` | `X=±6,8m` (loa) & `X=±8,2m` (quạt) | Loa `0,65 × 0,60 × 1,55`; Quạt hơi nước `0,85 × 0,65 × 1,75` | `MAT_Speaker_Mesh_Black`, `MAT_Cooler_Body_Grey` + nắp `MAT_Cooler_Top_Coral` | Đúng chi tiết thực tế sảnh cưới Việt Nam trong `f03`, `f17` | `f03`, `f04`, `f17` | Hoàn thiện W03 |

---

## 5. Nhóm Bàn Ghế Tiệc, Bát Đĩa & Hoa Bàn (`DINING`)

| Mã vật thể | Tên vật thể | Nguồn | Khu vực (`Zone`) | Kích thước thực (`m`) | Vật liệu PBR (`Material`) | LOD & Tối ưu WebGL | Ảnh đối chiếu | Trạng thái |
| :--- | :--- | :---: | :--- | :--- | :--- | :--- | :--- | :---: |
| `DIN-001` | Bàn tiệc tròn 10 khách phủ khăn trắng kem rủ tự nhiên (16 bàn chính = 160 ghế) | `[SRC]` | 2 cánh đại sảnh (`4 hàng × 4 cột`) | Mặt bàn `Ø1,50 m`, cao `0,76 m`, váy khăn rủ xuống `0,12 m` cách sàn | `MAT_Tablecloth_IvoryWhite` (`#F7F5F0`, `Roughness=0.82`, có sóng rủ biên khăn) | Chia sẻ chung mesh data (`InstancedMesh` thân thiện GLB) | `f16`, `f17`, `f18` | Hoàn thiện W02–W03 |
| `DIN-002` | Ghế Tiffany / Chiavari sơn trắng lưng nan dọc + đệm ngồi kem (160 ghế bàn + 2 cụm ghế xếp chồng dự phòng) | `[SRC]` | Quanh 16 bàn tiệc & sát vách biên | `0,41 × 0,43 × 0,90` (cao mặt ghế `0,45 m`) | `MAT_Chair_Tiffany_White` (`Roughness=0.36`) + đệm `MAT_Cushion_Cream` | Toàn bộ 160 ghế chia sẻ 1 mesh gốc duy nhất (< 260 tris/ghế) | `f01`, `f16`, `f17` | Hoàn thiện W02–W03 |
| `DIN-003` | Bộ đồ ăn trên bàn tiệc (10 bộ bát đĩa sứ trắng, lon nước xanh, bình hoa tâm bàn) | `[SRC]` | Trên mặt 16 bàn tiệc | Bát đĩa `Ø0,18m`, lon nước cao `0,12m`, lọ hoa giữa bàn `0,26m` | `MAT_Porcelain_White` (`Roughness=0.18`), `MAT_Can_CobaltBlue` (`Metallic=0.75`) | Gộp thành 1 cụm `Tabletop_Set` dùng chung cho 16 bàn (chỉ 1 mesh data!) | `f16`, `f17`, `f18` | Hoàn thiện W03 |

---

## 6. Nhóm Nhân Vật Mô Phỏng (`CHARACTERS` — Tuyệt đối tách biệt khỏi khách thật)

| Mã nhân vật | Vai trò NPC mô phỏng | Nguồn cảm hứng từ video | Vị trí hoạt động | Chiều cao (`m`) | Chu kỳ chuyển động (`Animation Action`) | Ghi chú phân tách dữ liệu |
| :--- | :--- | :--- | :--- | :---: | :--- | :--- |
| `NPC-STF-01` | Nhân viên lễ tân đón khách (Nữ, đầm/áo công sở lịch sự) | `f05`, `f11` (người trực sau bàn lễ tân) | Sau bàn lễ tân `X=-5,6m, Y=-14,9m` | `1,62` | Nhịp thở tự nhiên, xoay người chào & cúi đầu lịch sự (`Reception_Greet_Loop`, pha lệch `0.0s`) | Gắn cờ `userData.entityType = "npc"` |
| `NPC-STF-02` | Nhân viên điều phối sảnh & dẫn bàn (Nam, sơ mi trắng – quần âu đen) | `f03`, `f04`, `f13` (nhân viên sơ mi trắng dọc lối đi) | Đi tuần dọc hành lang đón khách & kiểm tra dãy bàn trái (`Y=-11m ↔ +1m`) | `1,72` | Bước đi đúng vận tốc chân không trượt (`Staff_Walk_Guide_Loop`, pha lệch `1.7s`) | `userData.entityType = "npc"` |
| `NPC-STF-03` | Nhân viên phục vụ / kiểm tra bàn tiệc (Nam, áo polo đen – mũ đen) | `f06` (nhân viên áo đen kiểm tra cạnh bàn phải) | Khu bàn tiệc cánh phải (`X=+3,5m, Y=-2,5m`) | `1,70` | Căn chỉnh khăn/bát đĩa & quan sát sảnh (`Staff_TableCheck_Loop`, pha lệch `3.1s`) | `userData.entityType = "npc"` |
| `NPC-MUS-01..02` | Nhạc công ban nhạc sống (Tay trống & Nhạc công đàn phím) | `f07`, `f08`, `f15` (ban nhạc biểu diễn trên sân khấu) | Trên sân khấu (`X=+2,6m` & `X=-2,6m, Y=+10,0m`) | `1,69` | Đánh nhịp trống & chơi phím tự nhiên không đồng pha (`Musician_Play_Loop`) | `userData.entityType = "npc"` |
| `NPC-CPL-01..02` | Cô dâu váy cưới trắng & Chú rể vest đen (chế độ demo nghi lễ theo khung giờ) | `f01`, `f02`, `f10` (cặp đôi trên sân khấu lúc cử hành lễ) | Giữa sân khấu chính (`X=±0,45m, Y=+9,6m`) | `1,64` & `1,75` | Đứng nghi lễ sang trọng, vẫy tay nhẹ & hướng về quan khách (`Couple_Ceremony_Loop`) | Có thể ẩn/thay theo giai đoạn timeline (`PREPARATION` vs `CEREMONY`) |
| `NPC-GST-01..04` | Khách mô phỏng tạo không khí (Áo dài đỏ truyền thống, vest lịch sự) | `f06`, `f18` (khách mặc áo dài đỏ & vest trò chuyện, chụp ảnh) | Tiền sảnh chụp ảnh & cạnh bàn tiệc (không chắn lối đi giữa) | `1,60–1,73` | Trò chuyện gật đầu nhẹ, giơ điện thoại chụp ảnh kỷ niệm (`Guest_Social_Loop` lệch pha nhau) | **Tuyệt đối không cộng vào bộ đếm khách thật (`online_real_guests`)** |
