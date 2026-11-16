---
id: ADM-INVT-005
title: "Khôi phục phiên bản thiệp cũ từ lịch sử bản sửa đổi (Invitation Revisions)"
category: "Thiệp cưới Công cộng & Tương tác"
summary: "Quy trình vận hành Admin để kiểm tra lịch sử chỉnh sửa thiệp cưới, so sánh sai khác (Change Diff), và phục hồi an toàn (Rollback) nội dung thiệp về phiên bản trước mà không làm gián đoạn hệ thống hay mất dữ liệu khách mời."
reader: "Admin / Operation Specialist / Technical Support"
order: 5
updated_at: "2026-11-15"
tags: [admin, lich-su-phien-ban, khoi-phuc-thiep, change-diff, audit-log, append-only]
mapped_page: "ADM-INVT-PAGE"
mapped_route: "admin.invitations.revisions.index"
risk_level: "HIGH"
required_role: "support"
---

# Khôi phục phiên bản thiệp cũ từ lịch sử bản sửa đổi (Invitation Revisions)

> [!IMPORTANT]
> **Vai trò thực hiện:** `Customer Support`, `Operation Specialist`, hoặc `Super Admin`.  
> **Route màn hình:** `GET /admin/invitations/{id}/revisions` (`AdminInvitationRevisionController@index`).  
> **Endpoint so sánh diff:** `GET /admin/invitations/{id}/revisions/{revisionId}/diff` (`@diff`).  
> **Endpoint phục hồi:** `POST /admin/invitations/{id}/revisions/{revisionId}/restore` (`@restore`).  
> **Dịch vụ backend:** `App\Services\InvitationRevisionService`.  
> **Nguyên tắc an toàn:** Cơ chế **Append-only**. Khi phục hồi, hệ thống sinh một phiên bản mới `v(N+1)` kiểu `restored`, tuyệt đối không ghi đè hay xóa các bản ghi lịch sử cũ.


![Giao diện quản lý Khôi phục phiên bản thiệp cũ từ lịch sử bản sửa đổi (Invitation Revisions)](/assets/client/docs/images/admin/03-public-invitation/adm-invt-005-step-1.png)
*Bảng điều khiển quản trị: Trực quan hóa quy trình vận hành tiêu chuẩn và quản lý dữ liệu an toàn.*

---

## 1. Mục đích & Bối cảnh nghiệp vụ

Trong quá trình chuẩn bị đám cưới, cô dâu chú rể hoặc người được ủy quyền thường xuyên truy cập Studio để tinh chỉnh câu từ, thay đổi timeline tiệc hoặc thay ảnh đại diện. Đôi khi xảy ra các tình huống sự cố:
* **Khách hàng vô tình xóa nhầm**: Lời ngỏ yêu thương, danh sách timeline hoặc thông tin gia đình hai họ dày công chuẩn bị bị xóa nhầm.
* **Xung đột khi nhiều người cùng chỉnh sửa**: Bố mẹ hoặc bạn bè cùng vào Studio một lúc dẫn đến việc ghi đè bản nháp sai lệch.
* **Lỗi định dạng khi đổi mẫu**: Sau khi thử đổi mẫu thiệp mới, giao diện bị xô lệch và khách hàng muốn quay lại đúng phiên bản hoàn hảo đã lưu trước đó.

Hệ thống **Invitation Revisions** của Mây Hẹn tự động ghi lại từng phiên bản thay đổi (snapshot) với mã băm SHA-256 chống trùng lặp, cho phép nhân viên vận hành hỗ trợ khách hàng so sánh và khôi phục chỉ với 1 nhấp chuột.

---

## 2. Các loại bản sửa đổi (Revision Types)

Mỗi lần thiệp cưới có biến động dữ liệu, hệ thống tự động gắn nhãn nguồn gốc thay đổi:

| Mã loại (`revision_type`) | Nhãn hiển thị | Mô tả kích hoạt |
| :--- | :--- | :--- |
| `manual_save` | **Lưu thủ công** | Cặp đôi chủ động bấm nút `[ 💾 Lưu bản nháp ]` trên Studio. |
| `autosave` | **Tự động lưu nháp** | Cơ chế ngầm tự động lưu sau 60 giây không hoạt động (giới hạn tối đa 30 bản gần nhất). |
| `published` | **Xuất bản chính thức** | Cặp đôi nhấn nút `[ 🚀 Xuất bản thiệp cưới ]` để chia sẻ công khai. |
| `template_switched` | **Chuyển đổi mẫu thiệp** | Cặp đôi hoặc Admin đổi mẫu thiệp sang giao diện thiết kế khác. |
| `restored` | **Phục hồi từ bản cũ** | Admin hoặc khách hàng thực hiện quay ngược dữ liệu từ một phiên bản trong quá khứ. |

---

## 3. Điều kiện tiên quyết & Thẩm quyền can thiệp

1. **Yêu cầu hỗ trợ từ chủ thiệp**: Khách hàng gọi hotline hoặc gửi ticket từ đúng số điện thoại/email đăng ký tài khoản thiệp cưới.
2. **Kiểm tra trạng thái thiệp**: Thiệp không nằm trong diện phong tỏa khẩn cấp vì tranh chấp pháp lý (`is_locked = false`).
3. **Phân quyền vai trò**:
   * Nhân viên `Customer Support` được quyền xem lịch sử, mở màn hình so sánh Diff và bấm nút Phục hồi.
   * `Operation Manager` phê duyệt khi phục hồi các bản sửa đổi trước ngày xuất bản chính thức có tác động lớn đến bố cục.

---

## 4. Hướng dẫn từng bước: So sánh & Phục hồi phiên bản

### Bước 1: Mở màn hình Lịch sử Phiên bản
1. Đăng nhập Admin Portal tại `/admin`.
2. Vào **Quản lý Thiệp cưới** (`/admin/invitations`).
3. Nhấp vào thiệp cần hỗ trợ để mở chi tiết, sau đó chọn tab **Lịch sử Phiên bản (Revisions)** hoặc truy cập trực tiếp URL:
   `/admin/invitations/{id}/revisions`
4. Màn hình hiển thị danh sách các phiên bản sắp xếp từ mới nhất xuống cũ nhất:
   * **Cột Phiên bản**: Ví dụ `v12`, `v11`, `v10`...
   * **Loại sửa đổi**: Huy hiệu màu (Xanh lá: Xuất bản, Xanh dương: Lưu thủ công, Xám: Tự động lưu).
   * **Người tạo**: Tên và email của người thao tác (Cô dâu, Chú rể hoặc tên Admin).
   * **Thời gian**: Ngày giờ chi tiết (ví dụ: `15/11/2026 14:32:15`).
   * **Tóm tắt thay đổi**: Các trường bị tác động (ví dụ: `timeline, love_story, gallery`).

### Bước 2: So sánh sai khác (Visual Change Diff)
1. Trên dòng phiên bản khách hàng muốn khôi phục (ví dụ `v8`), nhấp nút **[ 🔍 Xem Khác Biệt (Diff) ]**.
2. Hệ thống gọi API `GET /admin/invitations/{id}/revisions/8/diff` và mở Modal so sánh:
   * **Cột Trái (Bản hiện tại - v12)**: Hiển thị nội dung đang chạy ngoài web.
   * **Cột Phải (Bản mục tiêu - v8)**: Hiển thị nội dung cũ.
   * Các trường có sự thay đổi được tô màu trực quan (Đỏ: nội dung đã bị xóa/thay thế; Xanh lá: nội dung sẽ được phục hồi lại).
3. Đọc kỹ lại thông tin tên cô dâu chú rể, địa chỉ sảnh tiệc và ngày giờ để xác nhận đúng mốc thời gian khách hàng yêu cầu.

### Bước 3: Thực hiện phục hồi an toàn (Rollback)
1. Trong Modal Diff hoặc tại danh sách, nhấp nút **[ ↺ Phục Hồi Về Bản Này ]**.
2. Hộp thoại xác nhận hiển thị cảnh báo:
   > *"Hệ thống sẽ cập nhật nội dung thiệp về phiên bản v8 (ngày 12/11/2026). Dữ liệu hiện tại sẽ được lưu thành phiên bản mới v13 (restored). Bạn có chắc chắn muốn thực hiện?"*
3. Nhấp **[ Xác nhận Phục hồi ]**.

### Bước 4: Kiểm chứng kết quả
1. Hệ thống xử lý qua `InvitationRevisionService@restoreRevision`:
   * Nạp `config_data` từ bản `v8` vào `custom_config` và `draft_config` của thiệp.
   * Tự động sinh phiên bản mới `v13` với nhãn `restored`.
   * Xóa bộ nhớ đệm công khai `Cache::forget('invitation_public_' . $slug)`.
   * Lưu vết nhật ký `restore_invitation_revision` vào `audit_logs`.
2. Màn hình tự động tải lại với thông báo thành công màu xanh.
3. Nhấp vào đường link xem thử thiệp `/thiep/{slug}` để kiểm tra trực tiếp ngoài giao diện thực tế.

---

## 5. Dữ liệu nào được phục hồi và Dữ liệu nào KHÔNG bị ảnh hưởng?

Nhân viên hỗ trợ cần giải thích rõ ràng cho khách hàng:

* **NHỮNG DỮ LIỆU ĐƯỢC PHỤC HỒI (Theo bản cũ)**:
  * Toàn bộ 10 Section nội dung thiệp: Lời ngỏ, câu chuyện tình yêu, tên cô dâu chú rể, tên bố mẹ hai bên.
  * Lịch trình sự kiện (Timeline), bản đồ chỉ đường, dresscode.
  * Bộ sưu tập album ảnh cưới (Gallery), nhạc nền thiệp cưới (Audio).
  * Màu sắc chủ đạo, font chữ và phong cách hiển thị.

* **NHỮNG DỮ LIỆU TUYỆT ĐỐI KHÔNG BỊ MẤT HOẶC HOÀN NGUYÊN**:
  * **Danh sách khách mời & RSVP**: Khách đã xác nhận tham dự, số lượng người đi kèm và lời nhắn RSVP được lưu độc lập trong bảng `guests` và `rsvps`, không bị mất hay thay đổi.
  * **Sổ ký tên & Lời chúc**: Toàn bộ lời chúc của bạn bè trong bảng `wishes` được giữ nguyên vẹn.
  * **Mừng cưới VietQR & SePay**: Lịch sử giao dịch tiền mừng và biến động số dư ngân hàng không bị ảnh hưởng.
  * **Sơ đồ bàn tiệc & Chỗ ngồi**: Sơ đồ phân bàn tại nhà hàng hoàn toàn độc lập.

---

## 6. Xử lý sự cố & Tình huống đặc biệt

### Tình huống 1: Khách hàng phục hồi xong nhưng muốn quay lại bản trước đó
* **Xử lý:** Do hệ thống hoạt động theo nguyên tắc **Append-only**, bản trước khi phục hồi (`v12`) vẫn còn nguyên vẹn trong danh sách. Chỉ cần bấm phục hồi lại `v12` bất kỳ lúc nào mà không sợ mất mát.

### Tình huống 2: Ảnh trong bản cũ đã bị xóa khỏi máy chủ lưu trữ (Storage)
* **Xử lý:** Nếu khách hàng từng xóa vĩnh viễn tệp ảnh gốc khỏi thư viện Media:
  * Hệ thống tự động kích hoạt cơ chế ảnh dự phòng an toàn (`placeholder fallback`).
  * Giao diện thiệp vẫn hiển thị trang nhã, không bao giờ bị lỗi icon ảnh vỡ `404`.
  * Hướng dẫn khách hàng vào Studio tải lại ảnh mới vào vị trí đó.

---

## 7. Những điều Tuyệt đối Bị cấm

1. **CẤM THAO TÁC PHỤC HỒI KHI CHƯA XÁC MINH DANH TÍNH:** Bắt buộc đối chiếu số điện thoại hoặc email đăng ký để ngăn chặn kẻ xấu mạo danh yêu cầu sửa đổi nội dung thiệp của người khác.
2. **CẤM XÓA BẢNG GHI LỊCH SỬ THIỆP CƯỚI TRỰC TIẾP:** Toàn bộ bảng `invitation_revisions` là tài sản kiểm toán và căn cứ giải quyết khiếu nại, nghiêm cấm chạy lệnh SQL `DELETE` hoặc can thiệp thủ công vào cơ sở dữ liệu.

---

## 8. Bài viết liên quan
* [ADM-INVT-001: Tìm kiếm & Tra cứu thông tin thiệp cưới nâng cao](file:///resources/docs/admin/03-public-invitation/ADM-INVT-001.md)
* [ADM-INVT-002: Quy trình khóa thiệp cưới khẩn cấp](file:///resources/docs/admin/03-public-invitation/ADM-INVT-002.md)
* [ADM-INVT-004: Gia hạn thời gian lưu trữ thiệp cưới và quy trình chuyển đổi gói dịch vụ](file:///resources/docs/admin/03-public-invitation/ADM-INVT-004.md)
* [ADM-INVT-006: Can thiệp hỗ trợ thiệp khách hàng mà không làm rò rỉ hoặc mất dữ liệu khách mời](file:///resources/docs/admin/03-public-invitation/ADM-INVT-006.md)
