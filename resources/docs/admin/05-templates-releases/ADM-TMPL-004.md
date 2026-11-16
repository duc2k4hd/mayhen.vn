---
id: ADM-TMPL-004
title: "Kích hoạt, gắn nhãn Nổi bật (Featured) và ẩn mẫu thiệp bảo trì"
category: "Quản lý Mẫu thiệp & Release Control"
summary: "Hướng dẫn quản trị viên kích hoạt xuất bản mẫu thiệp lên Kho giao diện, gắn nhãn Nổi bật (Featured Badge), sắp xếp thứ tự ưu tiên và tạm ẩn mẫu thiệp khi bảo trì mà không làm gián đoạn thiệp của khách hàng cũ."
reader: "PER-ADM-03 (Quản trị viên Hệ thống)"
order: 4
updated_at: "2026-11-15"
tags: [templates, toggle-active, featured-badge, maintenance, display-order, store-curation]
mapped_page: "ADM-TMPL-CURATION"
mapped_route: "admin.templates.index"
risk_level: "MEDIUM"
required_role: "manager"
---

# Kích hoạt, gắn nhãn Nổi bật (Featured) và ẩn mẫu thiệp bảo trì

> [!WARNING]
> **Phân quyền thực hiện:** Chuyên viên Mẫu thiệp (`designer`), Quản lý Vận hành (`manager`) hoặc Super Admin (`admin.super`).  
> **Route quản trị:** `GET /admin/templates` (`admin.templates.index`), `POST /admin/templates/{id}/toggle-active` và `POST /admin/templates/{id}/toggle-featured`.  
> **Nguyên tắc tương thích ngược:** Việc tạm ẩn một mẫu thiệp không bao giờ làm ảnh hưởng đến các thiệp cưới của khách hàng đã tạo trước đó.


![Giao diện quản lý Kích hoạt, gắn nhãn Nổi bật (Featured) và ẩn mẫu thiệp bảo trì](/assets/client/docs/images/admin/05-templates-releases/adm-tmpl-004-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục Đích Nghiệp Vụ Quản Trị Kho Mẫu (Store Curation)

Kho mẫu thiệp cưới Mây Hẹn (`/mau-thiep`) là "bộ mặt thương hiệu" quyết định ấn tượng đầu tiên của các cặp đôi. Quản trị viên cần thường xuyên điều phối:
1. **Kích hoạt mẫu mới:** Đưa mẫu thiệp đã qua kiểm thử chất lượng lên kệ bán hàng công khai.
2. **Gắn nhãn Nổi bật (Featured Badge):** Đưa các mẫu thiệp có gu thẩm mỹ cao cấp, xu hướng thiết kế mùa cưới mới nhất lên vị trí danh dự ở đầu trang chủ và trang kho mẫu.
3. **Tạm ẩn mẫu để bảo trì / nâng cấp:** Ngừng cho phép người dùng mới chọn mẫu này khi mẫu đang được lập trình viên sửa mã nguồn hoặc thay đổi cấu trúc asset.
4. **Sắp xếp thứ tự hiển thị (Display Order):** Điều hướng thị hiếu khách hàng vào các mẫu thiệp mang lại giá trị cảm xúc cao hoặc gói dịch vụ chiến lược.

---

## 2. Bảng Điều Khiển Danh Sách Mẫu Thiệp (`/admin/templates`)

```
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│ KHO MẪU THIỆP CƯỚI (Tổng: 24 mẫu | Đang mở bán: 20 | Nổi bật: 6 | Đã tạo: 1,420 thiệp)          │
├──────────────────────────────────────────────────────────────────────────────────────────────────┤
│ Tên Mẫu Thiệp           │ Phân Loại   │ Trạng Thái Mở Bán  │ Huy Hiệu Nổi Bật   │ Thao Tác       │
├─────────────────────────┼─────────────┼────────────────────┼────────────────────┼────────────────┤
│ Celestial Bloom         │ Cổ Điển     │ [● Đang Mở Bán]    │ [★ Đang Ghim]      │ [Sửa] [Bản phát│
│ Pure Romance            │ Hiện Đại    │ [● Đang Mở Bán]    │ [☆ Ghim Nổi Bật]   │  hành] [Quét]  │
│ Vintage Melody (Bảo trì)│ Tối Giản    │ [○ Tạm Ẩn]         │ [☆ Không ghim]     │                │
└─────────────────────────┴─────────────┴────────────────────┴────────────────────┴────────────────┘
```

---

## 3. Quy Trình Bật / Tắt Trạng Thái Hoạt Động (`toggleActive`)

Khi cần tạm ngưng cho phép tạo mới mẫu thiệp (để nâng cấp giao diện hoặc khắc phục lỗi hiển thị):

1. **Bước 1:** Truy cập `/admin/templates`.
2. **Bước 2:** Định vị mẫu thiệp cần can thiệp.
3. **Bước 3:** Gạt thanh trượt hoặc nhấp vào nút trạng thái **[Đang Mở Bán / Tạm Ẩn]** (`POST /admin/templates/{id}/toggle-active`).
4. **Hệ quả tức thời:**
   - Trường `is_active` chuyển đổi giữa `true` và `false`.
   - Hệ thống tự động xóa sạch bộ nhớ đệm: `InvitationTemplate::clearTemplateCaches($template)`.
   - **Đối với khách hàng mới:** Mẫu thiệp biến mất khỏi trang `/mau-thiep` và danh sách chọn mẫu trong Studio Editor. Khách mới không thể chọn tạo thiệp từ mẫu này.
   - **Đối với khách hàng cũ:** **KHÔNG BỊ ẢNH HƯỞNG**. Hàng ngàn khách hàng đã tạo thiệp từ mẫu này trước đó vẫn mở thiệp, phát nhạc, nhận lời chúc và quét QR bình thường.
   - Ghi nhận Audit Log `toggle_template_active` lưu vết quản trị viên thực hiện.

---

## 4. Quy Trình Gắn Nhãn Nổi Bật (`toggleFeatured`)

Mỗi mùa cưới (Mùa Thu - Đông hoặc Mùa Xuân), ban biên tập Mây Hẹn sẽ tuyển chọn 4 đến 6 mẫu thiệp xuất sắc nhất để ghim nổi bật:

1. **Bước 1:** Tại danh sách mẫu thiệp, tìm mẫu muốn quảng bá.
2. **Bước 2:** Nhấp vào biểu tượng ngôi sao **[Ghim Nổi Bật]** (`POST /admin/templates/{id}/toggle-featured`).
3. **Hệ quả tức thời:**
   - Trường `is_featured` chuyển sang `true`.
   - Trên website công khai: Mẫu thiệp được gắn huy hiệu vàng ánh kim **[Được Yêu Thích Nhất]** hoặc **[Biên Tập Viên Tuyển Chọn]**.
   - Mẫu được ưu tiên xuất hiện tại slider trang chủ và nhóm đầu tiên của bộ lọc danh mục.
   - Khi muốn bỏ ghim: Nhấp lại vào biểu tượng ngôi sao để đưa mẫu về thứ tự sắp xếp thông thường.

---

## 5. Cấu Hình Thứ Tự Hiển Thị & Thông Tin Thương Mại

Để sắp xếp vị trí hiển thị chính xác từng mẫu:
1. Nhấp nút **[Chỉnh sửa]** trên dòng mẫu thiệp để mở trang `/admin/templates/{id}/edit`.
2. Cập nhật các trường thông tin:
   - **Tên hiển thị thương mại:** Tên tiếng Việt thanh lịch (Ví dụ: *Dạ Yến Ánh Sao - Celestial Bloom*).
   - **Phân loại phong cách (`category`):** `classic` (Cổ điển), `modern` (Hiện đại), `minimal` (Tối giản), `luxury` (Hoàng gia sang trọng), `vintage` (Hoài niệm), `oriental` (Truyền thống Á Đông).
   - **Thứ tự hiển thị (`display_order`):** Nhập số nguyên (Số nhỏ hơn hiển thị trước, ví dụ: 1, 2, 3...).
   - **Gói cước mặc định (`tier`):** Gán mẫu vào gói Tiêu chuẩn (Standard), Nâng cao (Plus) hoặc Không giới hạn (Premium).
3. Nhấp **[Lưu thay đổi]**. Bộ nhớ đệm danh mục được làm mới ngay lập tức.

---

## 6. Những điều Tuyệt đối Bị cấm

1. **CẤM** xóa cứng bản ghi mẫu thiệp trong CSDL nếu đã có dù chỉ 01 thiệp cưới của khách hàng đang liên kết với mẫu đó (sẽ gây lỗi vỡ quan hệ khóa ngoại). Thay vào đó, bắt buộc sử dụng thao tác **Tạm Ẩn (`is_active = false`)**.
2. **CẤM** ghim nổi bật quá 8 mẫu thiệp cùng lúc, gây loãng trải nghiệm thị giác của người dùng trên trang chủ.
3. **CẤM** kích hoạt mở bán mẫu thiệp khi chưa vượt qua bài kiểm định toàn vẹn của Schema Validator Inspector.

---

## 7. Bài viết Liên quan

- [ADM-TMPL-001: Thêm mẫu thiệp mới: schema.json, ảnh đại diện và 10 section](/admin/docs/templates-releases/ADM-TMPL-001)
- [ADM-TMPL-002: Sử dụng công cụ Chẩn đoán Asset quét ảnh và tài nguyên ngoài](/admin/docs/templates-releases/ADM-TMPL-002)
- [ADM-TMPL-005: Quy trình phát hành bản phát hành mới và cơ chế Rollback an toàn](/admin/docs/templates-releases/ADM-TMPL-005)
