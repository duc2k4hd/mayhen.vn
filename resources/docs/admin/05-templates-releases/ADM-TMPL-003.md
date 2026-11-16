---
id: ADM-TMPL-003
title: "Thao tác Localize & Batch-repair: Đưa 100% tài nguyên về thư mục cục bộ tự chứa"
category: "Quản lý Mẫu thiệp & Release Control"
summary: "Hướng dẫn thực hiện quy trình tự động tải ảnh từ máy chủ ngoại vi về đĩa cứng cục bộ (Localize Asset) và công cụ khắc phục hàng loạt (Batch-Repair) đưa toàn bộ thiệp cưới về trạng thái an toàn."
reader: "PER-ADM-03 (Quản trị viên Hệ thống)"
order: 3
updated_at: "2026-11-15"
tags: [localize-asset, batch-repair, fallback-assets, storage, local-assets, csv-export]
mapped_page: "ADM-ASSET-REPAIR"
mapped_route: "admin.assets.diagnostics.index"
risk_level: "HIGH"
required_role: "manager"
---

# Thao tác Localize & Batch-repair: Đưa 100% tài nguyên về thư mục cục bộ tự chứa

> [!WARNING]
> **Phân quyền thực hiện:** Quản lý Vận hành (`manager`) hoặc Super Admin (`admin.super`).  
> **Route thao tác:** `POST /admin/assets/diagnostics/{id}/localize`, `POST /admin/assets/diagnostics/{id}/batch-repair` và `GET /admin/assets/diagnostics/export`.  
> **Mục tiêu kỹ thuật:** Triệt tiêu hoàn toàn mã lỗi HTTP 404 và loại bỏ 100% liên kết CDN ngoại vi trôi nổi.


![Giao diện quản lý Thao tác Localize & Batch-repair: Đưa 100% tài nguyên về thư mục cục bộ tự chứa](/assets/client/docs/images/admin/05-templates-releases/adm-tmpl-003-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Hai Cơ Chế Phục Hồi Tài Nguyên Cốt Lõi

Khi Trạm Chẩn Đoán Asset phát hiện các lỗi về đường dẫn hình ảnh, Mây Hẹn cung cấp hai cơ chế xử lý chuyên sâu:

```
┌──────────────────────────────────────┐        ┌──────────────────────────────────────┐
│       1. NỘI ĐỊA HÓA (LOCALIZE)      │        │       2. SỬA HÀNG LOẠT (BATCH-REPAIR)│
├──────────────────────────────────────┤        ├──────────────────────────────────────┤
│ Tải tệp ảnh từ máy chủ ngoại vi về   │        │ Thay thế hàng loạt các ảnh bị mất    │
│ lưu trữ vĩnh viễn trên đĩa cứng      │   VÀ   │ (404) bằng bộ ảnh Fallback chuẩn mực │
│ máy chủ: storage/invitations/{id}/   │        │ trích xuất từ chính mẫu thiệp gốc.   │
│ => Bảo toàn đúng ảnh của khách hàng. │        │ => Xóa sổ nguy cơ vỡ layout thiệp.   │
└──────────────────────────────────────┘        └──────────────────────────────────────┘
```

---

## 2. Thao Tác Nội Địa Hóa Từng Ảnh (Localize Asset SOP)

Sử dụng khi ảnh trên máy chủ ngoại vi là ảnh cưới thật của cô dâu chú rể và bạn muốn kéo về lưu trữ an toàn trên máy chủ Mây Hẹn:

1. **Bước 1:** Truy cập `/admin/assets/diagnostics`, nhấp **[Quét chi tiết]** trên thiệp cưới cần xử lý.
2. **Bước 2:** Định vị dòng tài nguyên bị cảnh báo màu vàng hoặc đỏ `external_untrusted` (Ví dụ: `https://ladicdn.com/photo/prewedding-tuanlan.jpg`).
3. **Bước 3:** Nhấp nút **[Nội địa hóa (Localize)]** (`POST /admin/assets/diagnostics/{id}/localize`).
4. **Bước 4:** Tiến trình `AdminAssetDiagnosticsService::localizeExternalAsset` tự động thực thi:
   - Gửi yêu cầu HTTP GET có kiểm soát thời gian chờ (Timeout 10s) đến máy chủ nguồn.
   - Xác minh luồng dữ liệu nhị phân của tệp ảnh và nhận diện phần mở rộng chuẩn (`webp`, `jpg`, `png`).
   - Lưu trữ an toàn vào ổ đĩa: `storage/app/public/invitations/{invitation_id}/localized_{md5_hash}.{ext}`.
   - Cập nhật trường cấu hình trong bảng `invitations.custom_config` trỏ về đường dẫn nội bộ: `/storage/invitations/...`.
5. **Bước 5:** Màn hình hiển thị thông báo thành công màu xanh: *"Đã tải về và nội địa hóa tài nguyên thành công (100% Local Asset)"*. Biểu tượng cảnh báo chuyển thành tích xanh an toàn.

---

## 3. Thao Tác Sửa Nhanh Hàng Loạt (Batch-Repair SOP)

Sử dụng trong tình huống khẩn cấp khi thiệp cưới có quá nhiều ảnh bị lỗi 404, liên kết ngoại vi bị chết hàng loạt hoặc trước giờ cử hành tiệc cưới:

1. **Bước 1:** Tại trang chẩn đoán, kiểm tra cột *Chỉ số Lỗi* của thiệp cưới.
2. **Bước 2:** Nhấp nút màu tím **[Phục hồi nhanh hàng loạt (Batch Repair)]** (`POST /admin/assets/diagnostics/{id}/batch-repair`).
3. **Bước 3:** `AdminAssetDiagnosticsService::batchRepairInvitation` tự động duyệt qua toàn bộ các trường lỗi:
   - Ảnh bìa Hero bị lỗi -> Tự động thay bằng: `/mau-thiep/{slug}/assets/images/cover.webp`.
   - Ảnh chú rể bị lỗi -> Tự động thay bằng: `/mau-thiep/{slug}/assets/images/groom.webp`.
   - Ảnh cô dâu bị lỗi -> Tự động thay bằng: `/mau-thiep/{slug}/assets/images/bride.webp`.
   - Các ảnh Album cưới bị hỏng -> Tự động gán lại ảnh gallery fallback từ 1 đến 10 của mẫu thiệp.
4. **Bước 4:** Giao diện trả về thông báo: *"Đã phục hồi nhanh X tài nguyên cho thiệp [Tiêu đề]"*. Điểm sức khỏe `Health Score` lập tức nhảy vọt lên **100%**.

---

## 4. Áp Dụng Fallback Tùy Biến (Custom Fallback)

Nếu bạn muốn chỉ định một hình ảnh cụ thể làm ảnh thay thế thay vì ảnh mặc định của hệ thống:
1. Nhấp nút **[Áp dụng Fallback]** bên cạnh trường dữ liệu cần sửa.
2. Hộp thoại cho phép nhập URL ảnh thay thế hợp lệ trên máy chủ nội bộ.
3. Nhấp **[Xác nhận áp dụng]**. Hệ thống cập nhật trường dữ liệu ngay lập tức.

---

## 5. Xuất Báo Cáo Chẩn Đoán Toàn Hệ Thống (CSV UTF-8 BOM)

Phục vụ việc bàn giao ca trực hoặc gửi báo cáo chất lượng cho Ban Kỹ thuật:
1. Nhấp nút **[Xuất báo cáo lỗi (CSV)]** (`GET /admin/assets/diagnostics/export`).
2. Tệp CSV được tải về máy tính chứa đầy đủ: Mã thiệp cưới, Tên mẫu thiệp, Trường dữ liệu lỗi, URL nguồn ngoại vi, Loại lỗi và Điểm sức khỏe tài nguyên.
3. Tệp hỗ trợ hiển thị trọn vẹn dấu tiếng Việt trong Excel mà không bị lỗi bảng mã.

---

## 6. Những điều Tuyệt đối Bị cấm

1. **CẤM** bấm Batch-Repair trên các thiệp cưới đang diễn ra tiệc nếu chưa trao đổi với cô dâu chú rể, vì thao tác này có thể thay thế ảnh cưới thật của khách bằng ảnh mẫu minh họa.
2. **CẤM** tải về các tệp ảnh ngoại vi có dung lượng vượt quá 20MB hoặc tệp chứa mã độc thực thi.
3. **CẤM** sửa trực tiếp vào cơ sở dữ liệu chuỗi JSON `custom_config` bằng câu lệnh SQL thô mà không thông qua Service chẩn đoán.

---

## 7. Bài viết Liên quan

- [ADM-TMPL-001: Thêm mẫu thiệp mới: schema.json, ảnh đại diện và 10 section](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/05-templates-releases/ADM-TMPL-001.md)
- [ADM-TMPL-002: Sử dụng công cụ Chẩn đoán Asset quét ảnh và tài nguyên ngoài](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/05-templates-releases/ADM-TMPL-002.md)
- [ADM-TMPL-004: Kích hoạt, gắn nhãn Nổi bật và ẩn mẫu thiệp bảo trì](file:///d:/Project/Laravel/mayhen.vn/resources/docs/admin/05-templates-releases/ADM-TMPL-004.md)
