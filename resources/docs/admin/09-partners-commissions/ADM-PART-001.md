---
id: ADM-PART-001
title: "Xét duyệt hồ sơ đăng ký đối tác Studio cưới mới và kích hoạt tài khoản"
category: "Quản trị Đối tác Studio & Hoa hồng"
summary: "Quy trình thẩm định năng lực hồ sơ đối tác Studio ảnh viện và Wedding Planner mới, đối chiếu thông tin pháp lý doanh nghiệp, cấp mã định danh Partner Code và kích hoạt tài khoản quản trị mạng lưới trên Mây Hẹn."
reader: "PER-ADM-02 / PER-ADM-03 (Partnership Lead & Admin)"
order: 1
updated_at: "2026-11-15"
tags: [admin, doi-tac, studio-anh-vien, xet-duyet-ho-so, partner-code, kich-hoat-tai-khoan]
mapped_page: "ADM-PART-PAGE"
mapped_route: "admin.partners.approve"
risk_level: "HIGH"
required_role: "manager"
---

# Xét duyệt hồ sơ đăng ký đối tác Studio cưới mới và kích hoạt tài khoản

> [!IMPORTANT]
> **Vai trò thực hiện:** `Partnership Manager`, `Operation Lead`, hoặc `Super Admin`.  
> **Route màn hình đối tác:** `GET /admin/partners` (`PartnerController@index`) & `GET /admin/partners/{id}` (`@show`).  
> **Endpoint phê duyệt:** `POST /admin/partners/{id}/approve` (`@approve`).  
> **Dịch vụ backend:** `App\Services\PartnerManagementService`.  
> **Hạng đối tác mặc định khi kích hoạt:** `Silver` (Tỷ lệ hoa hồng khởi điểm: **15%**).


![Giao diện quản lý Xét duyệt hồ sơ đăng ký đối tác Studio cưới mới và kích hoạt tài khoản](/assets/client/docs/images/admin/09-partners-commissions/adm-part-001-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích & Bối cảnh nghiệp vụ

Mạng lưới Đối tác Mây Hẹn (May Hen Partner Network) quy tụ các **Studio ảnh cưới cao cấp, Wedding Planners, Trung tâm tiệc cưới và Freelancer nhiếp ảnh**. Khi trở thành đối tác chính thức, các đơn vị được cấp quyền:
1. Tạo và quản lý hàng loạt thiệp cưới cho các cặp đôi khách hàng của Studio qua **Partner Portal** (`/doi-tac`).
2. Gắn thương hiệu độc quyền của Studio (Co-branding Watermark) vào chân thiệp cưới.
3. Hưởng chiết khấu hoa hồng định kỳ (15% – 30%) trên mọi giao dịch nâng cấp gói dịch vụ phát sinh từ khách hàng.

Để bảo đảm uy tín thương hiệu Mây Hẹn và chất lượng dịch vụ đồng đều, mọi hồ sơ đăng ký đối tác gửi về từ trang `/dang-ky-doi-tac` đều phải trải qua quy trình thẩm định trước khi được cấp mã định danh `partner_code`.

---

## 2. Tiêu chuẩn Thẩm định Hồ sơ Đối tác

Nhân viên phụ trách đối tác (Partnership Specialist) cần đối chiếu các tiêu chí sau:

| Tiêu chí | Yêu cầu đối với Studio / Doanh nghiệp | Yêu cầu đối với Wedding Planner / Cá nhân |
| :--- | :--- | :--- |
| **Tên đơn vị (`business_name`)** | Tên thương hiệu Studio ảnh cưới hoặc công ty sự kiện rõ ràng. | Tên nghệ danh hoặc thương hiệu dịch vụ cưới cá nhân. |
| **Pháp lý & ĐKKD** | Mã số thuế doanh nghiệp hoặc hộ kinh doanh hợp lệ. | Căn cước công dân và tài khoản ngân hàng chính chủ. |
| **Kênh hoạt động trực tuyến** | Fanpage Facebook, Website, hoặc Instagram có trên 1.000 followers và có bài đăng thực tế về dịch vụ cưới trong 30 ngày gần nhất. | Portfolio hoặc trang cá nhân công khai các dự án cưới đã thực hiện. |
| **Thông tin liên hệ** | Số điện thoại Hotline, Email nghiệp vụ, Địa chỉ showroom/studio có thật. | Số điện thoại Zalo liên lạc thường trực và địa bàn hoạt động. |

---

## 3. Vòng đời Trạng thái Hồ sơ Đối tác (`partners.status`)

```
   [ Đăng ký mới ] ──> pending (Chờ thẩm định hồ sơ)
                             │
            ┌────────────────┴────────────────┐
            ▼                                 ▼
   approved / active (Kích hoạt)     rejected (Từ chối)
            │
            ▼
   suspended (Đình chỉ vi phạm)
```

* `pending`: Hồ sơ mới gửi, chưa được cấp mã kích hoạt.
* `active`: Đã được phê duyệt, được phép đăng nhập Partner Portal và tạo thiệp cho khách hàng.
* `suspended`: Bị tạm khóa do vi phạm chính sách hoặc phát sinh khiếu nại chưa giải quyết.
* `rejected`: Hồ sơ không đạt yêu cầu năng lực hoặc thông tin giả mạo.

---

## 4. Hướng dẫn từng bước: Thẩm định & Kích hoạt Đối tác

### Bước 1: Tiếp nhận danh sách đối tác chờ duyệt
1. Đăng nhập Admin Portal tại `/admin`.
2. Trên thanh menu, chọn **Mạng Lưới Đối Tác** (`/admin/partners`).
3. Tại bộ lọc Trạng thái (`status`), chọn **Chờ duyệt (`pending`)**.
4. Màn hình hiển thị danh sách các hồ sơ mới kèm theo ngày đăng ký và số điện thoại liên hệ.

### Bước 2: Thẩm định chi tiết hồ sơ
1. Nhấp vào tên đơn vị để mở trang **Hồ Sơ Đối Tác Chi Tiết** (`/admin/partners/{id}`).
2. Kiểm tra các trường thông tin:
   * **Tên thương hiệu (`business_name`)**: Đảm bảo không trùng lặp thương hiệu đã có.
   * **Người đại diện & Email đăng nhập (`user`)**: Tài khoản người dùng liên kết trên hệ thống.
   * **Số điện thoại & Địa chỉ**: Kiểm tra showroom trên Google Maps.
   * **Kênh truyền thông**: Bấm vào liên kết Fanpage/Website đối tác để đánh giá phong cách hình ảnh có phù hợp với thẩm mỹ Quiet Luxury của Mây Hẹn hay không.
3. Liên hệ trực tiếp qua điện thoại hoặc Zalo để chào mừng và xác minh nhu cầu hợp tác thực tế.

### Bước 3: Phê duyệt và Cấp mã Đối tác (`partner_code`)
1. Sau khi xác nhận hồ sơ đạt chuẩn, nhấp nút màu xanh **[ ✅ Phê Duyệt Hồ Sơ ]** ở thanh tác vụ.
2. Hệ thống gọi `PartnerManagementService@approvePartner`:
   * Chuyển trạng thái `partner.status = 'active'`.
   * Tự động sinh mã định danh đối tác duy nhất theo cú pháp `MH-PART-{ID}` hoặc tiền tố thương hiệu (ví dụ: `PART-LUXURY-01`).
   * Thiết lập hạng ban đầu: `tier = 'silver'`, tỷ lệ hoa hồng `commission_rate = 15.0%`.
   * Gán quyền `partner` cho tài khoản người dùng (`User`).
   * Gửi email kích hoạt tự động chứa đường link đăng nhập Partner Portal và hướng dẫn sử dụng.
   * Ghi log kiểm toán `PARTNER_APPROVED` vào `audit_logs`.

---

## 5. Xử lý khi Hồ sơ Không đạt yêu cầu

Nếu hồ sơ thiếu thông tin xác minh hoặc phong cách dịch vụ không phù hợp:
1. Nhấp nút **[ ❌ Đình Chỉ / Từ Chối ]**.
2. Nhập lý do từ chối rõ ràng (ví dụ: `Không liên lạc được qua số điện thoại cung cấp sau 3 lần gọi; Fanpage không hoạt động`).
3. Gửi thông báo qua email hướng dẫn đối tác bổ sung thông tin nếu muốn đăng ký lại trong tương lai.

---

## 6. Những điều Tuyệt đối Bị cấm trong Xét duyệt Đối tác

1. **CẤM DUYỆT HỒ SƠ ẢO / CHƯA LIÊN LẠC THỰC TẾ:** Mọi đối tác được kích hoạt đều có quyền gắn thương hiệu và hưởng hoa hồng tài chính, bắt buộc phải có cuộc gọi xác minh thực tế.
2. **CẤM TỰ Ý NÂNG TỶ LỆ HOA HỒNG VƯỢT QUY ĐỊNH KHI MỚI KÍCH HOẠT:** Mọi đối tác mới đều xuất phát từ hạng `Silver` (15%). Việc nâng lên `Gold` (20%) hoặc `Platinum` (25–30%) phải tuân theo quy chuẩn doanh số tại [ADM-PART-002](/admin/docs/partners-commissions/ADM-PART-002).
3. **CẤM TẠO TÀI KHOẢN ĐỐI TÁC TRÙNG LẶP CHO CÙNG MỘT PHÁP NHÂN:** Mỗi Studio chỉ được cấp 01 mã `partner_code` duy nhất để quản lý danh sách đám cưới tập trung.

---

## 7. Bài viết liên quan
* [ADM-PART-002: Quản lý cấu hình mức chiết khấu hoa hồng theo từng cấp độ đối tác (Tier)](/admin/docs/partners-commissions/ADM-PART-002)
* [ADM-PART-003: Kiểm tra và đối soát doanh thu tạo ra từ các thiệp của đối tác](/admin/docs/partners-commissions/ADM-PART-003)
* [ADM-PART-004: Phê duyệt lệnh rút tiền hoa hồng: Kiểm tra mã chuẩn chi ngân hàng](/admin/docs/partners-commissions/ADM-PART-004)
* [CLI-PART-001: Studio nhiếp ảnh liên kết và quản lý nhiều thiệp cưới khách hàng](/docs/partner-privacy/CLI-PART-001)
