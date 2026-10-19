# 🎨 ĐẶC TẢ THIẾT KẾ GIAO DIỆN & TƯƠNG TÁC (EDITOR V2 UX/UI SPECIFICATION)

> **Dự án**: Mây Hẹn (`mayhen.vn`)  
> **Tài liệu**: UI/UX Design System & Layout Architecture  
> **Phong cách chủ đạo**: Quiet Luxury Studio, Minimalist Editorial, Mobile-First Native Excellence.  
> **Nguyên tắc kỹ thuật**: 100% Pure HTML + Pure CSS + Native Vanilla JavaScript (Zero External Libraries).

---

## 1. NGUYÊN TẮC THIẾT KẾ KHÔNG GIAN STUDIO (DESIGN PHILOSOPHY)

Trình tạo thiệp cưới không được biến thành một ứng dụng văn phòng khô cứng, cũng không được sến súa màu mè. Mây Hẹn Studio V2 được định hình như **một xưởng may thiệp cưới thủ công cao cấp kỹ thuật số**:
- **Tone & Mood**: Tinh khôi, ấm áp, yên bình, quý phái. Nền giấy mỹ thuật màu kem nhạt (`#FAF8F5`), viền chỉ vàng champagne hairline (`rgba(197, 168, 128, 0.3)`), khoảng thở phóng khoáng.
- **Trọng tâm thị giác**: Mọi công cụ phải lùi về làm nền, tôn vinh trung tâm sân khấu là **chiếc thiệp cưới đang tỏa sáng**.

---

## 2. BỐ CỤC KHÔNG GIAN TRÊN MÁY TÍNH (DESKTOP STUDIO LAYOUT)

Giao diện Desktop được chia thành 4 phân khu chuyên biệt (4-Zone Studio Architecture):

```
+--------------------------------------------------------------------------------------------------+
| ZONE 1: STUDIO TOPBAR (Logo · Tên Thiệp · Save Status · Undo/Redo · Đổi Mẫu · Xem Thử · Xuất Bản)|
+-------------------+----------------------------------------------------+-------------------------+
| ZONE 2:           | ZONE 3: CENTER STAGE CANVAS                        | ZONE 4:                 |
| LEFT TOOL RAIL    |                                                    | INSPECTOR PANEL         |
|                   | [Phone Viewport Frame - 390x844 CSS px]            |                         |
| [1. Nội dung]     | +------------------------------------------------+ | Thuộc tính của          |
| [2. Bố cục & Thứ tự]| |  [Header & Phong Thư Tem Sáp]                 | | Section đang được chọn: |
| [3. Hình ảnh cưới]| |  -------------------------------------------- | |                         |
| [4. Màu sắc & Font] | |  [Lễ Vu Quy & Tiệc Cưới]  <-- Selected State   | | - Tiêu đề section     |
| [5. Nhạc nền]     | |  -------------------------------------------- | | - Tên sảnh tiệc       |
| [6. Quà mừng QR]  | |  [Album Ảnh Kỷ Niệm 10 Bức]                  | | - Địa chỉ & Map URL   |
| [7. Cài đặt thiệp]| |  -------------------------------------------- | | - Giờ đón khách       |
|                   | |  [Sổ Lưu Bút & RSVP Online]                  | | - Nút Chỉ Đường       |
|                   | +------------------------------------------------+ | - Ẩn/Hiện Section       |
+-------------------+----------------------------------------------------+-------------------------+
```

### 2.1. Zone 1: Studio Topbar (`.mayhen_editor_topbar_*`)
- **Chiều cao cố định**: `64px`, dính chặt đỉnh màn hình (`position: sticky; top: 0; z-index: 100;`).
- **Nền & Viền**: Nền trắng ngà kính mờ (`rgba(255, 255, 255, 0.92); backdrop-filter: blur(12px); border-bottom: 1px solid var(--mayhen-color-border);`).
- **Thành phần**:
  - *Cánh trái*: Nút Back quay về Dashboard + Tên thiệp cưới (click để đổi tên trực tiếp) + Badge trạng thái lưu (🟢 *Đã lưu nháp* / 🟡 *Đang lưu...*).
  - *Chính giữa*: Nút hoàn tác (Undo `Ctrl+Z` / Redo `Ctrl+Y`) + Bộ chuyển đổi chế độ xem thiết bị (📱 Mobile 390px / 💻 Tablet 768px / 🖥️ Desktop Full).
  - *Cánh phải*: Nút **"Đổi Mẫu"** (Chip ảnh đại diện mẫu hiện tại) + Nút **"Xem Thử Toàn Thiệp"** + Nút chính **"Xuất Bản / Chia Sẻ Thiệp"** mạ vàng ánh kim sang trọng.

### 2.2. Zone 2: Left Tool Rail (`.mayhen_editor_nav_*`)
- **Chiều rộng**: `80px` (thu gọn) hoặc `280px` (khi mở rộng chi tiết).
- Chứa danh sách các tab điều hướng trực quan với icon SVG tinh xảo:
  1. 📝 *Nội dung hôn lễ*
  2. 🔀 *Bố cục & Thứ tự các phần (Section Reorder)*
  3. 🖼️ *Hình ảnh & Album cưới*
  4. 🎨 *Màu sắc, Font chữ & Nền giấy*
  5. 💌 *Hiệu ứng mở phong thư tem sáp*
  6. 🎵 *Nhạc nền cưới lãng mạn*
  7. 💳 *Mã QR mừng cưới & Ngân hàng*

### 2.3. Zone 3: Center Stage Canvas (`.mayhen_editor_canvas_*`)
- **Nền sân khấu**: Nền canvas màu xám cát ấm mềm (`#F0EBE1`), có họa tiết lưới chấm mờ (subtle dot grid) tạo cảm giác không gian kiến trúc.
- **Khung điện thoại chuẩn (Phone Mockup Frame)**:
  - Tỉ lệ: `390px × 844px` (tương đương chuẩn iPhone 14/15/16).
  - Bo góc viền `44px`, viền kim loại champagne `border: 10px solid #2C2824; box-shadow: 0 30px 70px -15px rgba(44, 38, 30, 0.25);`.
  - Có thanh điều hướng ảo trên đỉnh và vạch home bar ở đáy.
  - Hỗ trợ cuộn mượt mà bên trong khung thiệp.
- **Tương tác trực tiếp trên Canvas**:
  - Khi hover vào một section: Xuất hiện viền chỉ vàng đứt nét và nhãn tên section (ví dụ: `[Khối Sự Kiện Cưới]`).
  - Khi click vào section: Khung viền chuyển sang nét liền vàng gold, hiển thị tay cầm kéo thả di chuyển (`Drag Handle`) và tự động đồng bộ mở Inspector bên phải.

### 2.4. Zone 4: Right Inspector Panel (`.mayhen_editor_inspector_*`)
- **Chiều rộng**: `360px`, nằm cố định bên phải.
- **Nguyên tắc hiển thị theo ngữ cảnh (Contextual Property Inspector)**:
  - Chọn Section nào chỉ hiện công cụ của Section đó (không nhồi nhét tất cả cài đặt vào một màn hình dài ngoằng).
  - Ví dụ:
    - Chọn *Section Lễ Vu Quy*: Hiện form nhập Giờ lễ, Tên nhà hàng, Địa chỉ, Nút định vị Google Maps, Công tắc hiển thị Nhà Trai / Nhà Gái.
    - Chọn *Section Album Ảnh*: Hiện lưới thumbnail ảnh, nút Tải thêm ảnh, nút Cắt ảnh 3:4, chọn kiểu hiển thị lưới 2 cột hay dải phim cuộn ngang.
    - Chọn *Section Nhạc Nền*: Hiện trình phát nhạc thử, ô tìm kiếm bài hát theo tên ca sĩ, công tắc Tự động phát khi mở thiệp.

---

## 3. BỐ CỤC TRÊN ĐIỆN THOẠI (MOBILE-FIRST WORKSPACE)

> ⚠️ **Quy tắc vàng**: Tuyệt đối **KHÔNG** thu nhỏ giao diện desktop xuống mobile. Trên điện thoại, màn hình thiệp cưới phải là trung tâm, mọi thao tác chỉnh sửa diễn ra qua **Bottom Sheet (Bảng trượt từ đáy)**.

```
+---------------------------------------------------+
| MOBILE TOPBAR: [<] Tên Thiệp · [Lưu] · [Xem Thử]   |
+---------------------------------------------------+
|                                                   |
|             CANVAS THIỆP CƯỚI TRỰC TIẾP           |
|         (Khách vuốt xem thiệp mượt mà 100%)       |
|                                                   |
|   +-------------------------------------------+   |
|   | Section Đang Được Chọn (Active Focus)      |   |
|   | Viền vàng mỏng + Nút [Chỉnh sửa] nổi      |   |
|   +-------------------------------------------+   |
|                                                   |
+---------------------------------------------------+
| CONTEXTUAL BOTTOM SHEET (Trượt lên khi cần sửa):  |
| ── (Thanh gạt trượt ngón tay) ─────────────────── |
| [Tên Chú Rể: Nguyễn Minh Anh]                     |
| [Tên Cô Dâu: Trần Ngọc Hà   ]                     |
| [Ngày Hôn Lễ: 2027-03-07    ]                     |
+---------------------------------------------------+
| BOTTOM TOOLBAR: [Nội Dung] [Bố Cục] [Ảnh] [Màu]   |
+---------------------------------------------------+
```

### 3.1. Hành Trình Trải Nghiệm Mobile Liền Mạch
1. **Màn hình Canvas luôn hiện hữu**: Người dùng luôn nhìn thấy chiếc thiệp cưới của mình chiếm trọn khung nhìn (Viewport).
2. **Bottom Toolbar (Thanh công cụ đáy)**: 4 nút biểu tượng lớn vừa ngón tay cái:
   - 📋 *Thông Tin*
   - 🔀 *Bố Cục*
   - 🖼️ *Ảnh Cưới*
   - 🎨 *Phong Cách*
3. **Contextual Bottom Sheet**:
   - Khi bấm vào 1 nút trên toolbar hoặc chạm vào 1 section trên thiệp, bảng điều khiển trượt nhẹ từ dưới lên (chiếm khoảng 45% chiều cao màn hình).
   - Người dùng vừa gõ chữ ở bảng dưới, mắt vừa nhìn thấy chữ trên thiệp ở nửa trên màn hình thay đổi ngay lập tức!
   - Khi bàn phím ảo xuất hiện: Sheet tự động co giãn thông minh, cuộn vùng input lên vị trí an toàn, không che khuất mắt nhìn.

---

## 4. CHI TIẾT TƯƠNG TÁC (MICRO-INTERACTIONS & MOTION GUIDELINES)

### 4.1. Tương Tác Kéo Thả Section (Section Drag & Drop)
- **Thao tác**: Nhấp giữ vào biểu tượng tay cầm `⠿` (Drag Handle) ở mép section trong khoảng `150ms`.
- **Phản hồi thị giác**:
  - Section được nhấc bổng nhẹ (`transform: scale(1.02); box-shadow: 0 15px 30px rgba(0,0,0,0.15); opacity: 0.95; z-index: 50;`).
  - Xuất hiện một đường chỉ vàng nam châm (Drop Indicator Bar) tại các khe giữa những section khác báo hiệu vị trí sẽ thả.
  - Các section xung quanh tự động trượt dạt sang một bên bằng hiệu ứng CSS Spring êm dịu (`transition: transform 0.25s cubic-bezier(0.2, 0, 0, 1);`).
  - Khi thả ngón tay/chuột: Section rơi vào vị trí mới, rung nhẹ một nhịp haptic feedback (nếu thiết bị hỗ trợ).

### 4.2. Trình Cắt Ảnh Nghệ Thuật (Fine Art Image Cropper)
- Tỉ lệ cố định: `3:4` (chuẩn ảnh chân dung nghệ thuật) và `16:9` (chuẩn ảnh panorama phong cảnh).
- Khung cắt có đường lưới 1/3 (Rule of Thirds) màu trắng mờ, có vùng an toàn (Safe Zone) hình oval để người dùng canh khuôn mặt cô dâu chú rể không bị viền thiệp cắt phạm vào mắt hay trán.
- Hỗ trợ cử chỉ 2 ngón tay chụm/zoom (Pinch-to-zoom) trên điện thoại và con lăn chuột (Wheel Zoom) trên máy tính.

### 4.3. Bảng Màu Phong Cách Quiet Luxury (Curated Palette Picker)
- Thay vì một hộp màu RGB/HEX phức tạp khiến người dùng phối màu lem nhem, Mây Hẹn cung cấp sẵn **6 Bảng Màu Hôn Lễ Thượng Lưu**:
  1. 🍂 **Champagne Ivory**: Vàng cát, kem ấm, nâu hạt dẻ (`#FAF8F5`, `#C5A880`, `#4A3B32`).
  2. 🌿 **Sage Botanic**: Xanh lá xô thơm, trắng ngọc trai (`#F1F5F0`, `#9FAF90`, `#2D3B2A`).
  3. 🌸 **Dusty Romantic**: Hồng phấn khói, be sáp nến (`#FAF0F0`, `#D4A5A5`, `#4E3838`).
  4. 🍷 **Royal Bordeaux**: Đỏ rượu vang, chữ vàng dập nổi (`#FDF8F7`, `#8B2635`, `#C5A880`).
  5. 🖤 **Modern Monochrome**: Trắng đen tạp chí Vogue (`#FFFFFF`, `#1C1917`, `#78716C`).
  6. 🌊 **Ocean Breeze**: Xanh lam pastel sương mù (`#F0F4F8`, `#8EA8C3`, `#23395B`).
- Khách hàng bấm 1 chạm là toàn bộ màu chữ, màu viền, màu nút bấm và tem sáp trên thiệp tự động chuyển đổi đồng bộ hoàn hảo!
