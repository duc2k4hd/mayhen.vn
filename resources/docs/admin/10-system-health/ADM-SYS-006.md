---
id: ADM-SYS-006
title: "Quản lý tên miền tùy chỉnh (Custom Domains) và kiểm tra bản ghi DNS SSL tự động"
category: "Hệ thống, Sức khỏe, Sao lưu & Nghiệm thu"
summary: "Hướng dẫn quản trị viên hỗ trợ khách hàng cấu hình Vanity Subdomain, tên miền riêng độc bản, xác minh bản ghi DNS CNAME tự động và cơ chế định tuyến qua middleware DetectCustomDomain."
reader: "PER-ADM-03 (Quản trị viên Cấp cao)"
order: 6
updated_at: "2026-11-15"
tags: [custom-domain, vanity-subdomain, dns, cname, ssl, tls, tenant-routing]
mapped_page: "ADM-SYS-DOMAIN"
mapped_route: "account.invitations.domain"
risk_level: "HIGH"
required_role: "admin.super"
---

# Quản lý tên miền tùy chỉnh (Custom Domains) và kiểm tra bản ghi DNS SSL tự động

> [!WARNING]
> **Phân quyền thực hiện:** Quản trị viên Kỹ thuật & Chuyên viên Hỗ trợ Cấp cao.  
> **Route quản lý:** `/thiep/{id}/ten-mien` (`invitations.domain`).  
> **Cơ chế định tuyến lõi:** Middleware toàn cục `DetectCustomDomain` phục vụ thiệp cưới trực tiếp từ tên miền riêng.


![Giao diện quản lý Quản lý tên miền tùy chỉnh (Custom Domains) và kiểm tra bản ghi DNS SSL tự động](/assets/client/docs/images/admin/10-system-health/adm-sys-006-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Tổng Quan Kiến Trúc Đa Tên Miền (Custom Domain Architecture)

Để mang lại trải nghiệm độc bản, sang trọng (Quiet Luxury) cho các cặp đôi, Mây Hẹn hỗ trợ 2 hình thức định danh trực tuyến cao cấp:
1. **Tên miền phụ cá nhân hóa (Vanity Subdomain):** Có dạng `tuanlan.mayhen.vn` — Kích hoạt tức thì trong 1 giây, hoàn toàn miễn phí đi kèm mọi gói dịch vụ.
2. **Tên miền riêng độc bản (Custom Domain):** Có dạng `damcuoituanlan.vn` hoặc `tuanlan.wedding` — Cặp đôi sở hữu tên miền riêng và cấu hình trỏ về hạ tầng Mây Hẹn.

Middleware `DetectCustomDomain` tại `app/Http/Middleware/DetectCustomDomain.php` liên tục kiểm tra Host header của mọi lượt truy cập. Khi phát hiện tên miền riêng đã được xác thực, hệ thống sẽ:
- Nhận diện chính xác thiệp cưới (`invitation_id`) và đám cưới (`wedding_id`) tương ứng.
- Phục vụ trực tiếp nội dung thiệp mời, Két Kỷ Niệm (Memory Vault), Album ảnh tương tác (Photo Drop) và Sổ ký tên mà **giữ nguyên 100% URL tên miền riêng** trên thanh địa chỉ trình duyệt của khách mời.

---

## 2. Danh Sách Từ Khóa Bảo Lưu (Blacklisted Subdomains)

Để bảo vệ an toàn thương hiệu và ngăn chặn hành vi giả mạo trang quản trị, `WeddingDomain::BLACKLISTED_SUBDOMAINS` tự động chặn tuyệt đối các từ khóa nhạy cảm khi người dùng đăng ký Vanity Subdomain:

```
[ admin, administrator, api, auth, blog, cdn, dashboard, dev, docs, ftp, 
  help, mail, mayhen, payment, root, sepay, secure, ssl, static, status, 
  support, system, test, vpn, webhook, www, zalo ]
```

Nếu người dùng cố tình nhập các tên này (ví dụ: `admin.mayhen.vn` hoặc `api.mayhen.vn`), hệ thống lập tức ném ngoại lệ `ValidationException` và từ chối lưu dữ liệu.

---

## 3. Quy Trình Cấu Hình Bản Ghi DNS CNAME

Khi khách hàng gắn tên miền riêng độc bản (ví dụ: `damcuoituanlan.vn` hoặc `thiep.damcuoituanlan.vn`), nhân viên hỗ trợ hướng dẫn khách thiết lập trên trang quản lý DNS (như Cloudflare, Mat Bao, PA Vietnam, GoDaddy):

```
┌───────────────┬────────────┬─────────────────────────────┬─────────────┐
│   Tên Host    │  Loại DNS  │      Giá trị Đích (Target)  │    TTL      │
├───────────────┼────────────┼─────────────────────────────┼─────────────┤
│  thiep (hoặc) │   CNAME    │      cname.mayhen.vn        │  Tự động    │
│  @ (Apex CNAME│   ALIAS    │      cname.mayhen.vn        │  (Auto)     │
└───────────────┴────────────┴─────────────────────────────┴─────────────┘
```

> [!NOTE]
> Khuyến nghị khách hàng sử dụng tên miền phụ dạng `thiep.damcuoituanlan.vn` hoặc `wedding.damcuoituanlan.com` vì chuẩn giao thức DNS CNAME hỗ trợ tối ưu trên mọi nhà đăng ký tên miền tại Việt Nam.

---

## 4. Xác Minh Tự Động DNS CNAME (`DomainDnsService`)

Sau khi khách hàng lưu bản ghi DNS, hệ thống cung cấp nút bấm kiểm tra thời gian thực:

1. **Bước 1:** Khách hàng hoặc quản trị viên nhấp **[Xác minh bản ghi DNS]** (`POST /thiep/{id}/ten-mien/xac-minh-dns`).
2. **Bước 2:** `DomainDnsService::verifyCname` thực hiện truy vấn hàm `dns_get_record($domain, DNS_CNAME)`.
3. **Bước 3:** Hệ thống phân tích kết quả trả về:
   - **Thành công:** Bản ghi trỏ chính xác về `cname.mayhen.vn` (hoặc đuôi `mayhen.vn`). Trường `status` và `ssl_status` chuyển sang `active`, ghi nhận `verified_at = now()`.
   - **Chưa nhận diện:** Trả về thông báo *"Chưa tìm thấy bản ghi CNAME nào cho tên miền này. Hãy kiểm tra lại sau 5-15 phút do độ trễ phân giải DNS toàn cầu"*.
   - **Trỏ sai đích:** Thông báo *"Tên miền đã có bản ghi CNAME nhưng chưa trỏ về cname.mayhen.vn"*.

---

## 5. Tự Động Cấp Phát Chứng Chỉ Bảo Mật SSL/TLS (HTTPS)

1. Máy chủ Edge Router của Mây Hẹn tích hợp cơ chế tự động cấp chứng chỉ bảo mật Let's Encrypt / ZeroSSL qua giao thức ACME HTTP-01 Challenge.
2. Ngay sau khi bản ghi DNS CNAME được xác minh thành công (`status = active`), hệ thống cấp phát chứng chỉ SSL hợp lệ trong vòng 60 giây.
3. Toàn bộ lưu lượng HTTP chưa mã hóa đều được tự động chuyển hướng (`301 Redirect`) sang HTTPS bảo mật cao cấp.

---

## 6. Hỗ Trợ Gỡ Bỏ Hoặc Chuyển Đổi Tên Miền (Domain Lifecycle)

Khi khách hàng muốn đổi tên miền mới hoặc tiệc cưới đã kết thúc:
1. Truy cập trang quản lý tên miền `/thiep/{id}/ten-mien`.
2. Khách hàng có 3 lựa chọn thao tác độc lập:
   - **Gỡ tên miền phụ (Remove Subdomain):** Đặt trường `subdomain` về `null`, giữ nguyên tên miền riêng.
   - **Gỡ tên miền riêng (Remove Custom Domain):** Đặt trường `custom_domain` về `null`, giữ nguyên Vanity Subdomain.
   - **Xóa trọn bộ (Full Reset):** Xóa hoàn toàn bản ghi trong bảng `wedding_domains`. Tên miền được giải phóng để các cặp đôi khác có thể đăng ký trong tương lai.

---

## 7. Bảng Xử Lý Sự Cố Tên Miền Thường Gặp

| Hiện tượng lỗi | Nguyên nhân gốc rễ | Hướng dẫn khắc phục |
| :--- | :--- | :--- |
| **Báo lỗi 404 khi truy cập tên miền riêng** | Bản ghi CNAME trỏ đúng nhưng khách chưa ấn nút **[Xác minh DNS]** trên Mây Hẹn. | Vào trang `/thiep/{id}/ten-mien`, nhấp nút **[Xác minh bản ghi DNS]** để kích hoạt bản ghi trong DB. |
| **Trình duyệt báo "Kết nối không an toàn" (SSL Error)** | DNS mới trỏ xong nhưng chứng chỉ SSL đang trong hàng đợi phát hành (cần 1-2 phút). | Hướng dẫn khách đợi 2 phút hoặc thử truy cập bằng cửa sổ ẩn danh (Ctrl + Shift + N). |
| **Cloudflare báo lỗi Error 521 / Error 522** | Khách bật chế độ đám mây màu cam (Proxy On) gây xung đột SSL lặp vòng. | Hướng dẫn khách chuyển đám mây Cloudflare sang **Xám (DNS Only)**. |
| **Không thể đăng ký Subdomain** | Subdomain đã bị cặp đôi khác đăng ký trước đó hoặc nằm trong danh sách cấm. | Chọn tên miền phụ khác thêm năm cưới (ví dụ: `tuanlan2026`). |

---

## 8. Những điều Tuyệt đối Bị cấm

1. **CẤM** can thiệp trỏ tên miền chính `mayhen.vn` về bất kỳ máy chủ nào ngoài hạ tầng chuẩn của dự án.
2. **CẤM** cấp quyền can thiệp tên miền cho người dùng lạ không phải chủ sở hữu thiệp cưới (ngăn chặn IDOR).
3. **CẤM** bypass danh sách từ khóa cấm `BLACKLISTED_SUBDOMAINS` cho bất kỳ khách hàng nào mà chưa có văn bản phê duyệt từ Giám đốc Kỹ thuật.

---

## 9. Bài viết Liên quan

- [ADM-SYS-001: Kiểm tra bảng sức khỏe hệ thống (System Health Check)](/admin/docs/system-health/ADM-SYS-001)
- [ADM-SYS-005: Cấu hình nền tảng và quản lý Cờ tính năng (Feature Flags)](/admin/docs/system-health/ADM-SYS-005)
- [CLI-PART-003: Hướng dẫn cài đặt tên miền riêng cho thiệp cưới (Dành cho khách hàng)](/docs/partner-privacy/CLI-PART-003)
