# MÂY HẸN — VIRTUAL WEDDING WORLD 3D
## KIẾN TRÚC TỔNG THỂ HỆ THỐNG (VIRTUAL WEDDING ARCHITECTURE)

---

## 1. SƠ ĐỒ KIẾN TRÚC PHÂN TẦNG ĐỘC LẬP

```text
                             NGƯỜI THAM DỰ (KHÁCH MỜI / CHỦ TIỆC)
                                              │
                         ┌────────────────────┼────────────────────┐
                         ▼                    ▼                    ▼
                  [1. WEB / BLADE]    [2. REALTIME GATEWAY]  [3. VIDEO STREAMING]
                  Laravel 13 + ES6      WebSocket Shards       WebRTC Ingest /
                  Pure WebGL2/Three     (Presence & Chat)      LL-HLS Broadcast
                         │                    │                    │
                         ▼                    ▼                    ▼
                  [MySQL / SQLite]      [Redis Pub/Sub]     [3D LED Screen Surface]
                  - virtual_weddings    - Room Spatial Grid  - LED_STAGE_LEFT (16:9)
                  - virtual_sessions    - Max 35 Visible     - LED_STAGE_RIGHT (16:9)
                  - virtual_passes        Avatars / Zone     - Fullscreen Fallback
```

---

## 2. NGUYÊN TẮC BẤT BIẾN: KHÔNG BIẾN LARAVEL THÀNH GAME SERVER

1. **Tách biệt hoàn toàn luồng nghiệp vụ và luồng 3D/Realtime**:
   - **Laravel Backend**: Quản lý cấu hình đám cưới 3D, vòng đời 48 giờ, xác thực quyền sở hữu (`Wedding` / `WeddingEvent` / `Invitation`), đối soát webhook SePay 5.000đ, cấp vé vào cổng (`VirtualAccessPass`), quản lý phiên phát livestream và lưu trữ lời chúc/nhật ký kiểm duyệt.
   - **Tuyệt đối không ghi tọa độ di chuyển `(x, y, z, yaw)` vào MySQL/SQLite**: Mọi gói tin di chuyển nhân vật chỉ truyền qua bộ nhớ tạm của cổng Realtime theo từng phòng (`room_shard_id`) với tần suất 5–10Hz và nội suy mượt 60FPS tại trình duyệt khách.
   - **Không để PHP Worker xử lý luồng video**: Tín hiệu camera của chủ tiệc đi qua WebRTC/WHIP Ingest sang tầng phân phối WHEP/LL-HLS; Laravel chỉ xác thực khóa phát (`stream_key`) và phát tín hiệu trạng thái `LIVE / IDLE`.

---

## 3. KIẾN TRÚC HIỂN THỊ 3D & TỐI ƯU TỐC ĐỘ TẢI (8 BƯỚC PROGRESSIVE)

1. **Bước 1 — Trang Đón Khách Nhẹ (Zero 3D Payload)**: Hiển thị ngay tên Cô Dâu – Chú Rể, lịch sự kiện, ảnh đại diện rạp cưới và trạng thái cổng trong `< 300ms` (HTML + CSS thuần).
2. **Bước 2 — Kiểm tra năng lực WebGL2**: Tự động kiểm tra `canvas.getContext('webgl2')` và `MAX_TEXTURE_SIZE`. Nếu thiết bị không hỗ trợ WebGL2 hoặc ngữ cảnh GPU bị mất (`webglcontextlost`), tự động chuyển sang **Chế độ Xem Lễ 2D & Livestream Dự Phòng** (không bao giờ kẹt ở màn hình tải).
3. **Bước 3 — Xác thực quyền vào cổng & Access Pass**: Kiểm tra phiên vé hợp lệ (`VirtualAccessPass`) và cửa vào tiệc 5 giờ trước khi tải mô hình `.glb`.
4. **Bước 4 — Nạp mô hình `.glb` bất biến theo phiên bản**: Tải `public/assets/virtual-venues/royal-crimson-marquee/v1/mayhen_royal_crimson_marquee_v1.glb` (`5.90 MB`, có `Cache-Control: public, max-age=31536000, immutable`).
5. **Bước 5 — Hiển thị Cổng Vào & Sảnh Đón (`Foyer`)**: Khởi tạo camera tại `Y = -18.2m` ngay khi giải mã xong khung cảnh.
6. **Bước 6 — Kích hoạt Ánh sáng theo Giờ Sự Kiện**: Đồng bộ múi giờ sự kiện từ server để áp dụng đúng trạng thái `05:00`, `08:00`, `12:00`, `17:00` hoặc `20:00`.
7. **Bước 7 — Kích hoạt Hoạt cảnh NPC & Băng chuyền Timeline**: Nội suy chuyển động của 15 NPC theo mốc giờ thực tế của sự kiện.
8. **Bước 8 — Tự động thích ứng 3 Mức Chất Lượng (`Quality Tiers`)**:
   - **High**: Độ phân giải `min(devicePixelRatio, 2.0)`, đầy đủ bóng sáng điểm và phản xạ pha lê.
   - **Balanced** (Mặc định): Độ phân giải `min(devicePixelRatio, 1.25)`, cân bằng độ nét và tốc độ khung hình.
   - **Lite** (Thiết bị yếu / Điện thoại): Độ phân giải `0.85x`, tự động hạ cấp nếu FPS trung bình dưới `32 FPS` trong 3 giây liên tiếp.
