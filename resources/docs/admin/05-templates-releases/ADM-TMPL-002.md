---
id: ADM-TMPL-002
title: "Sử dụng công cụ Chẩn đoán Asset (Asset Diagnostics) quét ảnh và tài nguyên ngoài"
category: "Quản lý Mẫu thiệp & Release Control"
summary: "Hướng dẫn quản trị viên vận hành Trạm chẩn đoán tài nguyên hình ảnh (Asset Diagnostics Dashboard), phát hiện liên kết máy chủ ngoại vi vi phạm quy chuẩn và đo lường điểm sức khỏe tài nguyên của thiệp cưới."
reader: "PER-ADM-03 (Quản trị viên Hệ thống)"
order: 2
updated_at: "2026-11-15"
tags: [asset-diagnostics, media-scan, external-domains, local-assets, 404-detection, health-score]
mapped_page: "ADM-ASSET-DIAG"
mapped_route: "admin.assets.diagnostics.index"
risk_level: "HIGH"
required_role: "manager"
---

# Sử dụng công cụ Chẩn đoán Asset (Asset Diagnostics) quét ảnh và tài nguyên ngoài

> [!WARNING]
> **Phân quyền thực hiện:** Quản lý Vận hành (`manager`) hoặc Super Admin (`admin.super`).  
> **Route quản trị:** `GET /admin/assets/diagnostics` (`admin.assets.diagnostics.index`).  
> **Nguyên tắc kỹ thuật sống còn:** Tuân thủ Nguyên tắc số 6 (100% Local Assets) — Tuyệt đối không để thiệp cưới người dùng phụ thuộc vào máy chủ CDN bên ngoài.


![Giao diện quản lý Sử dụng công cụ Chẩn đoán Asset (Asset Diagnostics) quét ảnh và tài nguyên ngoài](/assets/client/docs/images/admin/05-templates-releases/adm-tmpl-002-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục Đích Của Trạm Chẩn Đoán Tài Nguyên (Asset Diagnostics Cockpit)

Khi người dùng sao chép nội dung hoặc các mẫu thiệp được nhập từ nhiều nguồn khác nhau, rất dễ xảy ra tình trạng:
- Dùng đường dẫn ảnh trực tiếp từ máy chủ thứ ba (`ladicdn.com`, `builder.27biggroup.com`, `unsplash.com`, `iili.io`). Khi các máy chủ này chặn hotlink hoặc bảo trì, thiệp cưới của khách hàng sẽ bị **vỡ ảnh, hiện biểu tượng lỗi hoặc load chậm chạp**.
- Người dùng xóa nhầm ảnh trong Két Kỷ Niệm khiến tệp vật lý trong `storage/app/public/` bị biến mất dẫn đến lỗi HTTP 404.
- Thiếu các ảnh chân dung bắt buộc (Ảnh bìa Hero, Ảnh Chú rể, Ảnh Cô dâu).

**Trạm Chẩn Đoán Asset** tại `/admin/assets/diagnostics` là công cụ rà soát tự động giúp quản trị viên quét, phân loại mức độ rủi ro và chấm điểm sức khỏe tài nguyên (**Health Score**) cho toàn bộ thiệp cưới trên hệ thống.

---

## 2. Các Chỉ Số KPI Giám Sát Tài Nguyên

Đầu trang Dashboard hiển thị 4 chỉ số tổng hợp:

```
┌─────────────────────────┬─────────────────────────┬─────────────────────────┬─────────────────────────┐
│   Tổng Thiệp Đã Quét    │    Lỗi Tài Nguyên Phát Hiện │     Lỗi Nguy Cấp (Critical) │  Tài Nguyên Ngoại Vi (CDN) │
│       1,248 Thiệp       │         34 Lỗi          │         5 Lỗi           │         18 Liên Kết     │
│   (Toàn bộ hệ thống)    │   (Trong trang hiện tại)│   (Thiếu ảnh bìa/chân dung)  │   (Cần Localize ngay)   │
└─────────────────────────┴─────────────────────────┴─────────────────────────┴─────────────────────────┘
```

- **Health Score (0% - 100%):** Thang điểm đo lường độ toàn vẹn của từng thiệp cưới:
  $$\text{Health Score} = \max\left(0, \left(1 - \frac{\text{Số lỗi phát hiện}}{\text{Tổng số tài nguyên kiểm tra}}\right) \times 100\right)$$
- Thiệp đạt **100% (Xanh lá):** Hoàn toàn tự chứa, 100% tệp tồn tại trên đĩa cứng nội bộ.
- Thiệp dưới **80% (Vàng cam/Đỏ):** Cần can thiệp sửa chữa khẩn cấp trước ngày cưới của khách hàng.

---

## 3. Phân Loại 3 Dạng Lỗi Tài Nguyên Phổ Biến

`AdminAssetDiagnosticsService` tự động phân loại lỗi theo mức độ nghiêm trọng:

### A. Lỗi Máy chủ Ngoại vi Bị cấm (`external_untrusted`) - Mức độ: WARNING / CRITICAL
- **Hiện tượng:** Trường ảnh chứa đường dẫn bắt đầu bằng các tên miền bên thứ ba nằm trong danh sách đen: `builder.27biggroup.com`, `ladicdn.com`, `images.unsplash.com`, `iili.io`, `cdn.jsdelivr.net`.
- **Hậu quả:** Nguy cơ bị chặn IP, rò rỉ dữ liệu quan khách sang máy chủ bên ngoài và vi phạm quy chuẩn xuất bản của Mây Hẹn.

### B. Lỗi Tệp Vật lý Không Tồn tại (`missing_local_file`) - Mức độ: CRITICAL
- **Hiện tượng:** Đường dẫn URL có dạng `/storage/invitations/...` hoặc `/assets/...` nhưng hàm kiểm tra đĩa cứng `Storage::disk('public')->exists()` hoặc `File::exists()` trả về `false`.
- **Hậu quả:** Trình duyệt của khách mời nhận mã lỗi HTTP 404, khung ảnh bị trống hoặc vỡ giao diện.

### C. Thiếu Trường Bắt buộc (`missing_required`) - Mức độ: CRITICAL
- **Hiện tượng:** Các trường cốt lõi như `hero_photo_url`, `groom_photo_url`, `bride_photo_url` bị bỏ trống rỗng mà không có ảnh mặc định thay thế.

---

## 4. Hướng Dẫn Thao Tác Chẩn Đoán Chi Tiết

### Bước 1: Lọc thiệp cần kiểm tra
- Truy cập `/admin/assets/diagnostics`.
- Dùng bộ lọc:
  - **Ô tìm kiếm (`q`):** Nhập mã slug thiệp, tiêu đề tiệc cưới hoặc tên cô dâu chú rể.
  - **Lọc theo Mẫu thiệp (`template_id`):** Chọn mẫu thiệp nghi vấn (Ví dụ: kiểm tra toàn bộ thiệp dùng mẫu `celestial-bloom`).

### Bước 2: Quét chi tiết từng thiệp (`scanSingle`)
- Trên dòng thiệp cần kiểm tra, nhấp nút **[Quét chi tiết]** (`GET /admin/assets/diagnostics/{id}/scan`).
- Bảng phân tích chi tiết mở ra, liệt kê toàn bộ các trường tài nguyên:
  - Ảnh bìa Hero
  - Ảnh chú rể & cô dâu
  - Nhạc nền (Audio MP3)
  - 10 vị trí ảnh Album cưới
  - Mã QR mừng cưới VietQR
- Các dòng bị lỗi sẽ được làm nổi bật màu đỏ (Critical) hoặc vàng (Warning) kèm theo **Đề xuất ảnh Fallback tương ứng** từ thư mục mẫu gốc.

---

## 5. Những điều Tuyệt đối Bị cấm

1. **CẤM** bỏ qua các cảnh báo `external_untrusted` khi chuẩn bị xuất bản thiệp cưới ra tên miền chính thức.
2. **CẤM** tự ý thêm các domain CDN lạ vào danh sách tin cậy mà không có sự thẩm định an ninh mạng của Lead Engineer.
3. **CẤM** xóa thư mục `public/assets/` hoặc xóa ổ đĩa `storage/app/public` khi chưa có bản sao lưu snapshot đầy đủ.

---

## 6. Bài viết Liên quan

- [ADM-TMPL-001: Thêm mẫu thiệp mới: schema.json, ảnh đại diện và 10 section](/admin/docs/templates-releases/ADM-TMPL-001)
- [ADM-TMPL-003: Thao tác Localize & Batch-repair đưa 100% tài nguyên về cục bộ](/admin/docs/templates-releases/ADM-TMPL-003)
- [ADM-SYS-001: Kiểm tra bảng sức khỏe hệ thống (System Health Check)](/admin/docs/system-health/ADM-SYS-001)
