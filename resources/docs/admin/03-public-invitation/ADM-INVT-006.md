---
id: ADM-INVT-006
title: "Can thiệp hỗ trợ thiệp khách hàng mà không làm rò rỉ hoặc mất dữ liệu khách mời"
category: "Thiệp cưới Công cộng & Tương tác"
summary: "Quy trình kích hoạt chế độ hỗ trợ an toàn dưới góc nhìn khách hàng (Safe Support Impersonation), giới hạn thời lượng 30 phút, phòng ngừa rò rỉ dữ liệu khách mời và tuân thủ Luật Bảo vệ dữ liệu cá nhân 91/2025/QH15."
reader: "Admin / Customer Support Lead / Data Privacy Officer"
order: 6
updated_at: "2026-11-15"
tags: [admin, che-do-ho-tro, impersonation, bao-mat-du-lieu, audit-log, luat-91-2025]
mapped_page: "ADM-INVT-PAGE"
mapped_route: "admin.support.impersonate"
risk_level: "CRITICAL"
required_role: "support"
---

# Can thiệp hỗ trợ thiệp khách hàng mà không làm rò rỉ hoặc mất dữ liệu khách mời

> [!CAUTION]
> **Vai trò thực hiện:** `Customer Support`, `Operation Lead`, hoặc `Super Admin`.  
> **Route kích hoạt:** `POST /admin/support/impersonate/{userId}` (`SupportImpersonationController@impersonate`).  
> **Route thoát phiên:** `POST /admin/support/leave` (`SupportImpersonationController@leave`).  
> **Dịch vụ backend:** `App\Services\SupportImpersonationService` & `App\Http\Middleware\SupportImpersonationMiddleware`.  
> **Thời lượng phiên tối đa:** Cố định **30 phút** (`DEFAULT_TTL_MINUTES = 30`), tự động hủy và đưa Admin về màn hình đăng nhập nếu hết hạn.


![Giao diện quản lý Can thiệp hỗ trợ thiệp khách hàng mà không làm rò rỉ hoặc mất dữ liệu khách mời](/assets/client/docs/images/admin/03-public-invitation/adm-invt-006-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích & Bối cảnh nghiệp vụ

Khi cô dâu chú rể gặp khó khăn trong quá trình tinh chỉnh thiệp cưới (ví dụ: lỗi dàn trang trên màn hình di động lạ, không biết chèn link Google Maps sảnh tiệc, hoặc cần hỗ trợ căn chỉnh album ảnh cưới), nhân viên hỗ trợ Mây Hẹn cần **nhìn thấy chính xác những gì khách hàng đang thấy trên màn hình của họ**.

Tuy nhiên, việc can thiệp trực tiếp vào tài khoản khách hàng tiềm ẩn các rủi ro bảo mật nghiêm trọng:
1. **Rò rỉ thông tin cá nhân khách mời (PII)**: Danh sách khách mời chứa số điện thoại, mối quan hệ thân thiết và số tiền mừng cưới (VietQR).
2. **Chiếm đoạt tài khoản ngoài ý muốn**: Nguy cơ vô tình đổi mật khẩu, đổi email hoặc xóa tài khoản.
3. **Vi phạm pháp lý**: Vi phạm quy định về bảo vệ dữ liệu cá nhân theo **Luật 91/2025/QH15**.

Mây Hẹn giải quyết bài toán này bằng cơ chế **Safe Support Impersonation** (Chế độ hỗ trợ an toàn có kiểm soát), bảo đảm nhân viên can thiệp hiệu quả mà không xâm phạm quyền riêng tư.

---

## 2. Nguyên tắc an toàn & Ranh giới bảo vệ (Security Guardrails)

Trong suốt thời gian nhân viên vận hành đăng nhập hỗ trợ tài khoản khách hàng:

```
[ QUẢN TRỊ VIÊN ] ──( Nhập Ticket + Lý do )──> [ MIDDLEWARE PHÒNG VỆ ]
                                                         │
           ┌─────────────────────────────────────────────┴─────────────────────────────────────────────┐
           ▼                                                                                           ▼
  [ CÁC THAO TÁC ĐƯỢC PHÉP ]                                                                [ CÁC HÀNH VI BỊ CHẶN TUYỆT ĐỐI ]
  - Vào Studio sửa nội dung thiệp                                                           - Chặn cập nhật mật khẩu (account.password.update)
  - Căn chỉnh ảnh cưới, timeline, thiệp mời                                                 - Chặn đổi email tài khoản (account.profile.update)
  - Xem trước giao diện Mobile / Desktop                                                    - Chặn yêu cầu xóa vĩnh viễn (account.privacy.erase)
  - Hỗ trợ nhập danh sách khách mời mẫu                                                     - Chặn chuyển tiền mừng cưới / rút tiền
```

* **TTL 30 phút tự hủy**: Hết thời hạn, phiên tự động kết thúc; hệ thống thu hồi quyền và chuyển hướng về `/admin/dashboard`.
* **Banner cảnh báo màu hổ phách toàn trang**: Trên đỉnh mọi màn hình hiển thị thanh thông báo:  
  *`"🛡️ Bạn đang ở chế độ hỗ trợ khách hàng: [Tên khách] • Còn lại: XX phút • [Rời Chế Độ Hỗ Trợ]"`*.
* **Nhật ký kiểm toán kép (Audit Trail)**: Ghi lại địa chỉ IP, User-Agent, mã Ticket hỗ trợ và chi tiết từng thao tác can thiệp.

---

## 3. Điều kiện tiên quyết & Thẩm quyền kích hoạt

Chỉ được phép kích hoạt chế độ hỗ trợ khi thỏa mãn đầy đủ **3 điều kiện**:
1. **Có yêu cầu trợ giúp hợp lệ từ khách hàng**: Thông qua Ticket hỗ trợ trên hệ thống, tin nhắn từ Zalo OA chính thức của Mây Hẹn, hoặc cuộc gọi từ số điện thoại đăng ký tài khoản.
2. **Có mã Ticket hoặc lý do hỗ trợ rõ ràng**: Hệ thống bắt buộc nhập tối thiểu 5 ký tự lý do (ví dụ: `Hỗ trợ căn chỉnh tiêu điểm ảnh cưới mẫu Celestial theo Ticket #TK-8821`).
3. **Không được hỗ trợ trên tài khoản chính mình**: Hệ thống từ chối nếu Admin cố tình impersonate tài khoản của chính bản thân.

---

## 4. Hướng dẫn từng bước: Kích hoạt phiên hỗ trợ an toàn

### Bước 1: Tra cứu tài khoản khách hàng
1. Đăng nhập Admin Portal tại `/admin`.
2. Trên menu bên trái, vào **Quản lý Tài khoản (Users)** (`/admin/users`).
3. Nhập email, số điện thoại hoặc tên khách hàng vào ô tìm kiếm.
4. Nhấp vào hồ sơ khách hàng cần hỗ trợ.

### Bước 2: Kích hoạt chế độ Hỗ trợ Khách hàng
1. Nhấp nút **[ 🛡️ Đăng Nhập Hỗ Trợ ]** trên thanh công cụ hồ sơ người dùng.
2. Hộp thoại xác nhận hiện ra yêu cầu điền:
   * **Mã Ticket hỗ trợ (Ticket Code)**: Nhập mã phiếu ghi nhận (ví dụ: `TK-2026-9912`).
   * **Lý do can thiệp (Reason)**: Ghi rõ nội dung sự cố cần xử lý (ví dụ: `Khách gặp lỗi font chữ tiêu đề trên iPhone 15 Pro Max`).
3. Nhấp **[ Xác nhận Bắt đầu Hỗ trợ ]**.

### Bước 3: Thao tác khắc phục sự cố trên Studio
1. Hệ thống tự động chuyển bạn sang **Dashboard Khách hàng** (`/tai-khoan`).
2. Trên đỉnh trang xuất hiện banner cảnh báo màu cam nổi bật.
3. Mở **Studio Tạo Thiệp** của khách hàng:
   * Thực hiện chỉnh sửa câu chữ, điều chỉnh tiêu điểm ảnh hoặc sắp xếp lại thứ tự sự kiện theo đúng yêu cầu của cặp đôi.
   * Bấm `[ Lưu bản nháp ]` hoặc `[ Xuất bản ]` nếu khách nhờ công bố thiệp.
4. **Tuyệt đối không nhấp vào tab Cài đặt bảo mật hoặc thông tin ngân hàng**.

### Bước 4: Kết thúc phiên hỗ trợ an toàn
1. Sau khi xử lý xong yêu cầu của khách hàng, nhấp nút màu đỏ **[ 🚪 Rời Chế Độ Hỗ Trợ ]** trực tiếp trên banner đầu trang (hoặc nút Logout).
2. Hệ thống thực thi `SupportImpersonationService@stopImpersonation`:
   * Xóa sạch các biến phiên hỗ trợ khỏi Session.
   * Khôi phục phiên đăng nhập Quản trị viên ban đầu.
   * Chuyển hướng bạn quay trở lại danh sách `/admin/users` với thông báo hoàn tất.

---

## 5. Quy chuẩn bảo vệ dữ liệu khách mời theo Luật 91/2025/QH15

Trong suốt quá trình hỗ trợ, nhân viên Mây Hẹn bắt buộc tuân thủ **5 nguyên tắc bảo mật thông tin khách mời**:

1. **Che giấu số điện thoại khách mời (PII Masking)**: Khi nhìn vào bảng khách mời trong màn hình hỗ trợ, số điện thoại luôn được che mờ các chữ số ở giữa (ví dụ: `090****889`). Không được chụp ảnh màn hình lộ số điện thoại gửi qua chat nội bộ.
2. **Cấm xuất danh sách khách mời (Zero Export)**: Nút xuất file Excel/CSV danh sách khách mời bị khóa quyền tải về trong phiên hỗ trợ. Chỉ tài khoản chính chủ của cặp đôi mới được tải danh sách này.
3. **Cấm xem biến động tiền mừng cưới VietQR cá nhân**: Số tiền mừng cưới, lời chúc riêng tư của khách gửi cho cô dâu chú rể thuộc danh mục dữ liệu đời tư nhạy cảm, nhân viên tuyệt đối không mở sổ theo dõi tiền mừng khi không có sự đồng ý bằng văn bản của cặp đôi.
4. **Không lưu cache dữ liệu khách hàng trên máy cá nhân**: Sử dụng trình duyệt ở chế độ ẩn danh (Incognito) nếu truy cập trên máy trạm chung tại văn phòng.

---

## 6. Xử lý các tình huống ngoại lệ

### Tình huống 1: Đang hỗ trợ thì bị văng ra trang đăng nhập
* **Nguyên nhân:** Đã vượt quá thời hạn an toàn 30 phút (`TTL`).
* **Xử lý:** Đây là tính năng bảo vệ tự động của hệ thống. Đăng nhập lại Admin và kích hoạt lại phiên mới nếu công việc chưa hoàn tất.

### Tình huống 2: Khách hàng cố tình đăng nhập trong lúc Admin đang hỗ trợ
* **Xử lý:** Phiên làm việc của khách hàng sẽ nhận diện xung đột session. Hệ thống ưu tiên cho chủ tài khoản và thông báo: *"Tài khoản của bạn đang được nhân viên kỹ thuật Mây Hẹn hỗ trợ theo yêu cầu"*. Khi Admin thoát phiên, quyền kiểm soát trở lại bình thường ngay lập tức.

---

## 7. Những điều Tuyệt đối Bị cấm trong Vận hành

1. **NGHIÊM CẤM TỰ Ý KÍCH HOẠT HỖ TRỢ KHI KHÔNG CÓ YÊU CẦU:** Mọi hành vi tự ý vào tài khoản khách hàng để tò mò thông tin cá nhân, ảnh cưới riêng tư đều là vi phạm kỷ luật cấp độ 3 và có thể bị truy cứu trách nhiệm theo luật an ninh mạng.
2. **NGHIÊM CẤM CHIA SẺ LIÊN KẾT PHIÊN LÀM VIỆC:** Tuyệt đối không copy cookie, session token hoặc đường link mang phiên hỗ trợ gửi cho người thứ ba.
3. **NGHIÊM CẤM SỬA ĐỔI THÔNG TIN BẢN QUYỀN HOẶC GỠ WATERMARK TRÁI PHÉP:** Không dùng quyền hỗ trợ để can thiệp miễn phí các tính năng trả phí của khách hàng.

---

## 8. Bài viết liên quan
* [ADM-INVT-001: Tìm kiếm & Tra cứu thông tin thiệp cưới nâng cao](file:///resources/docs/admin/03-public-invitation/ADM-INVT-001.md)
* [ADM-INVT-002: Quy trình khóa thiệp cưới khẩn cấp](file:///resources/docs/admin/03-public-invitation/ADM-INVT-002.md)
* [ADM-INVT-004: Gia hạn thời gian lưu trữ thiệp cưới và quy trình chuyển đổi gói dịch vụ](file:///resources/docs/admin/03-public-invitation/ADM-INVT-004.md)
* [ADM-INVT-005: Khôi phục phiên bản thiệp cũ từ lịch sử bản sửa đổi](file:///resources/docs/admin/03-public-invitation/ADM-INVT-005.md)
* [CLI-PART-002: Quyền riêng tư dữ liệu cá nhân (Chính sách tuân thủ Luật 91/2025/QH15)](file:///resources/docs/client/10-partner-privacy/CLI-PART-002.md)
