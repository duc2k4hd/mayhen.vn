---
id: ADM-TMPL-005
title: "Quy trình phát hành bản phát hành mới (Release Control) và cơ chế Rollback an toàn"
category: "Quản lý Mẫu thiệp & Release Control"
summary: "Hướng dẫn vận hành Phòng thí nghiệm phát hành mẫu thiệp (Release Lab), quản lý phiên bản Semantic Versioning, vượt qua Cổng kiểm định chất lượng (Preflight Quality Gate) và cơ chế hoàn nguyên 1-Click Rollback."
reader: "PER-ADM-03 (Quản trị viên Hệ thống)"
order: 5
updated_at: "2026-11-15"
tags: [release-control, template-releases, semantic-versioning, preflight-audit, rollback, release-lab]
mapped_page: "ADM-TMPL-RELEASE"
mapped_route: "admin.templates.release_lab"
risk_level: "CRITICAL"
required_role: "designer"
---

# Quy trình phát hành bản phát hành mới (Release Control) và cơ chế Rollback an toàn

> [!WARNING]
> **Phân quyền thực hiện:** Chuyên viên Mẫu thiệp (`designer`), Quản lý Vận hành (`manager`) hoặc Super Admin (`admin.super`).  
> **Route quản trị:** `GET /admin/templates/{id}/releases` (`admin.templates.release_lab`).  
> **Cổng an toàn kỹ thuật:** Bắt buộc vượt qua 100% bài kiểm tra tiền phát hành (Preflight Quality Gate) trước khi xuất bản ra toàn bộ người dùng website.


![Giao diện quản lý Quy trình phát hành bản phát hành mới (Release Control) và cơ chế Rollback an toàn](/assets/client/docs/images/admin/05-templates-releases/adm-tmpl-005-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Ý Nghĩa Quản Lý Phiên Bản Mẫu Thiệp (Release Lab)

Mẫu thiệp cưới không phải là một tệp HTML tĩnh mà là một sản phẩm phần mềm liên tục được cải tiến: thêm hiệu ứng rơi cánh hoa tuyết, đổi màu nút bấm, tối ưu tốc độ cuộn trên iPhone đời cũ hoặc bổ sung các trường thông tin mới.

Nếu chỉnh sửa trực tiếp vào mã nguồn mà không qua kiểm soát phiên bản:
- Thiệp cưới của các cặp đôi sắp cưới vào ngày mai có thể bị vỡ bố cục đột ngột.
- Khách hàng không lưu lại được dữ liệu do lệch schema giữa bản cũ và bản mới.

**Phòng Thí Nghiệm Phát Hành Mẫu Thiệp (Template Release Lab)** tại `/admin/templates/{id}/releases` cung cấp quy trình quản lý phiên bản chuẩn công nghiệp:
1. Đánh số phiên bản theo chuẩn Semantic Versioning (`v1.0.0` -> `v1.1.0` -> `v2.0.0`).
2. Phát hành thử nghiệm theo tỷ lệ bậc thang (Canary Rollout: 10% -> 50% -> 100%).
3. Chặn lỗi tự động bằng Cổng kiểm định chất lượng (Preflight Quality Gate).
4. Khôi phục thảm họa trong 1 giây bằng nút **Hoàn nguyên khẩn cấp (1-Click Instant Rollback)**.

---

## 2. Vòng Đời Trạng Thái Của Một Bản Phát Hành (Release Lifecycle)

```
┌──────────────┐      ┌──────────────┐      ┌──────────────┐      ┌──────────────┐
│    STAGED    │ ──>  │  PREFLIGHT   │ ──>  │  PUBLISHED   │ ──>  │  DEPRECATED  │
│ (Thử nghiệm) │      │ (Cổng duyệt) │      │ (Công bố 24/7│      │ (Bản lưu cũ) │
└──────────────┘      └──────────────┘      └──────────────┘      └──────────────┘
                                                   │
                                                   ▼
                                            ┌──────────────┐
                                            │ ROLLED_BACK  │
                                            │(Hoàn nguyên) │
                                            └──────────────┘
```

- **`staged` (Đang thử nghiệm):** Bản phát hành mới được tạo, chỉ hiển thị cho tỷ lệ người dùng thử nghiệm được chỉ định (`rollout_percentage`).
- **`published` (Chính thức):** Bản phát hành đạt chuẩn đã được quản lý phê duyệt phục vụ toàn bộ khách hàng.
- **`deprecated` (Lưu trữ):** Các bản phát hành cũ đã được thay thế bởi phiên bản mới hơn.
- **`rolled_back` (Đã hoàn nguyên):** Bản phát hành bị thu hồi khẩn cấp do phát hiện lỗi phát sinh trên môi trường thực tế.

---

## 3. Quy Trình Tạo Bản Phát Hành Mới (Create Release SOP)

1. **Bước 1:** Truy cập `/admin/templates`, nhấp nút **[Bản phát hành]** của mẫu thiệp cần nâng cấp để mở trang `/admin/templates/{id}/releases`.
2. **Bước 2:** Nhấp nút **[+ Khởi tạo bản phát hành mới]**.
3. **Bước 3:** Nhập thông tin cấu hình:
   - **Phiên bản mới (Version):** Tuân thủ định dạng chuẩn `vX.Y.Z` (Ví dụ: `v1.2.0`). Hệ thống tự động kiểm tra chống trùng lặp phiên bản.
   - **Tỷ lệ triển khai (Rollout Percentage):** Nhập tỷ lệ từ 0% đến 100% (Khuyến nghị: 10% cho đợt thử nghiệm đầu tiên).
   - **Ghi chú phát hành (Release Notes / Changelog):** Mô tả chi tiết những điểm mới (Ví dụ: *"Bổ sung hiệu ứng tuyết rơi lấp lánh cho mùa cưới Giáng Sinh; tối ưu dung lượng ảnh bìa WebP giảm 40%"*).
4. **Bước 4:** Nhấp **[Lưu bản phát hành thử nghiệm]** (`POST /admin/templates/{id}/releases`).
5. **Hệ quả:** Hệ thống chạy kiểm tra `InvitationTemplateService::runPreflightAudit`, chụp lại ảnh snapshot chất lượng (`preflight_snapshot`) và lưu bản phát hành ở trạng thái `staged`.

---

## 5. Vượt Cổng Kiểm Định Chất Lượng & Công Bố (Preflight Quality Gate)

Trước khi một bản phát hành thử nghiệm được phép nâng cấp lên trạng thái `published`:

1. Quản trị viên nhấp nút **[Công bố chính thức (Publish)]** (`POST /admin/templates/{id}/releases/{release_id}/publish`).
2. `TemplateController@publishRelease` tự động kích hoạt Cổng kiểm định Preflight:
   - Kiểm tra đủ 10 Section tiêu chuẩn: Không thiếu bất kỳ phần nào từ Màn mở đầu đến Sổ ký tên.
   - Kiểm tra mã nguồn Blade: Đảm bảo kế thừa layout chung `@extends('client.layouts.invitation')`.
   - Kiểm tra hợp đồng Live Sync: Đảm bảo các trường động có thuộc tính `data-sync-target`.
   - Kiểm tra tài nguyên hình ảnh: Không có liên kết máy chủ bên ngoài bị cấm.
3. **Nếu có bất kỳ vi phạm nào (Blockers):** Lệnh công bố bị **CHẶN ĐỨNG**. Hệ thống báo lỗi chi tiết trên màn hình (Ví dụ: *"Chưa vượt qua Preflight Gate: Thiếu Section 8 Dresscode"*).
4. **Nếu 100% đạt chuẩn:** Hệ thống chuyển bản cũ sang `deprecated`, kích hoạt bản mới thành `published`, xóa sạch cache template và ghi nhận Audit Log `publish_template_release`.

---

## 6. Cơ Chế Hoàn Nguyên Khẩn Cấp (1-Click Instant Rollback SOP)

Nếu sau khi công bố phiên bản mới, tổng đài hỗ trợ nhận được phản ánh của người dùng về việc thiệp bị lỗi hiển thị trên một số dòng máy:

```
┌────────────────────────────────────────────────────────────────────────┐
│ XÁC NHẬN HOÀN NGUYÊN KHẨN CẤP (1-CLICK INSTANT ROLLBACK)               │
├────────────────────────────────────────────────────────────────────────┤
│ Mẫu thiệp: Dạ Yến Ánh Sao (Celestial Bloom)                            │
│ Phiên bản lỗi hiện tại: v1.2.0  ===>  Phiên bản an toàn đích: v1.1.0   │
│                                                                        │
│ Lý do hoàn nguyên (Bắt buộc nhập tối thiểu 6 ký tự):                   │
│ [ Hiệu ứng tuyết rơi gây giật lag trên trình duyệt Safari iOS 16 ]    │
│                                                                        │
│ [ Hủy Bỏ ]              [ THỰC THI HOÀN NGUYÊN NGAY LẬP TỨC ]          │
└────────────────────────────────────────────────────────────────────────┘
```

1. **Bước 1:** Tại trang Lịch sử phát hành (`/admin/templates/{id}/releases`), tìm lại phiên bản ổn định trước đó (Ví dụ: bản `v1.1.0` có trạng thái `deprecated`).
2. **Bước 2:** Nhấp nút màu đỏ **[Hoàn nguyên về bản này (Rollback)]** (`POST /admin/templates/{id}/releases/{release_id}/rollback`).
3. **Bước 3:** Nhập bắt buộc **Lý do hoàn nguyên** (Tối thiểu 6 ký tự để phục vụ hậu kiểm an toàn thông tin).
4. **Bước 4:** Nhấp **[Thực thi hoàn nguyên ngay lập tức]**.
5. **Hiệu lực trong 0.5 giây:**
   - Phiên bản lỗi hiện tại bị đánh dấu thành `rolled_back` kèm lý do và thời điểm thu hồi.
   - Phiên bản ổn định đích được phục hồi ngay lập tức thành `published` với tỷ lệ triển khai 100%.
   - Toàn bộ bộ nhớ đệm template trên Redis / File Cache bị xóa sạch.
   - Hàng ngàn thiệp cưới của khách hàng lập tức quay trở lại giao diện ổn định bình thường mà không cần kỹ sư phải vào can thiệp mã nguồn máy chủ.
   - Ghi nhận Audit Log `rollback_template_release` lưu vết toàn bộ diễn biến sự cố.

---

## 7. Những điều Tuyệt đối Bị cấm

1. **CẤM** bỏ qua bước phát hành thử nghiệm (Canary Rollout) để công bố trực tiếp 100% đối với các thay đổi lớn về cấu trúc layout hoặc CSS cốt lõi.
2. **CẤM** thực hiện Rollback mà không ghi rõ nguyên nhân lỗi cụ thể vào ô lý do kiểm toán.
3. **CẤM** xóa bản ghi lịch sử phát hành của một phiên bản đã từng được công bố trên hệ thống.

---

## 8. Bài viết Liên quan

- [ADM-TMPL-001: Thêm mẫu thiệp mới: schema.json, ảnh đại diện và 10 section](/admin/docs/templates-releases/ADM-TMPL-001)
- [ADM-TMPL-004: Kích hoạt, gắn nhãn Nổi bật và ẩn mẫu thiệp bảo trì](/admin/docs/templates-releases/ADM-TMPL-004)
- [ADM-SYS-005: Cấu hình nền tảng và quản lý Cờ tính năng (Feature Flags)](/admin/docs/system-health/ADM-SYS-005)
