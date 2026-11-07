# MÂY HẸN (MAYHEN.VN) — SỔ ĐĂNG KÝ LỖI (BUG REGISTER)

> **Mục tiêu:** Theo dõi chi tiết mọi lỗi logic, dữ liệu, concurrency và giao diện người dùng.  
> **Thời điểm xác lập:** 07/11/2026.

---

## 1. DANH SÁCH LỖI LOGIC & DỮ LIỆU ĐÃ XÁC NHẬN & ĐÃ KHẮC PHỤC TRIỆT ĐỂ

| Bug ID | Mức độ | Phân hệ | Mô tả lỗi | Điều kiện tái hiện | Nguyên nhân gốc rễ | Hướng xử lý | Trạng thái |
|---|---|---|---|---|---|---|---|
| **BUG-LOGIC-01** | **P0** | Public & APIs | Khách mời vẫn gửi được RSVP và Lời chúc vào thiệp cưới đã bị khóa hoặc chưa xuất bản | Gửi POST tới `/thiep/{slug}/rsvp` khi thiệp có `is_published=false` hoặc `is_locked=true` | Controller không kiểm tra trạng thái xuất bản và khóa ở tầng tiếp nhận dữ liệu | Thêm phương thức kiểm tra rào chắn quyền công khai và từ chối xử lý | **ĐÃ XỬ LÝ (100% Tests Passed)** |
| **BUG-LOGIC-02** | **P1** | Sổ Mừng Cưới | Cho phép gắn khách mời của đám cưới khác vào sổ mừng cưới của mình | Gửi form thêm mừng cưới với `guest_id` thuộc cặp đôi khác | Validation rule dùng `exists:guests,id` thiếu scope `where('wedding_id', ...)` | Dùng `Rule::exists('guests', 'id')->where('wedding_id', $invitation->wedding_id)` | **ĐÃ XỬ LÝ (100% Tests Passed)** |
| **BUG-LOGIC-03** | **P1** | Quản lý thiệp | Người dùng tự ý mở khóa thiệp vi phạm bằng nút "Xuất bản" trong Dashboard | Thiệp bị Admin khóa, vào trang danh sách thiệp bấm Bật xuất bản | `AccountController::togglePublish` không kiểm tra cờ `is_locked` | Thêm kiểm tra `is_locked` trước khi cho phép đảo trạng thái | **ĐÃ XỬ LÝ (100% Tests Passed)** |
| **BUG-LOGIC-04** | **P1** | Webhook Payment | Không lưu mã giao dịch SePay (`provider_transaction_id`) | Chuyển khoản thành công, webhook gửi về | Bảng `orders` chưa có trường lưu trữ định danh giao dịch cổng | Tạo migration thêm cột `provider_transaction_id` unique và lưu khi webhook xác nhận | **ĐÃ XỬ LÝ (100% Tests Passed)** |
| **BUG-LOGIC-05** | **P2** | Upload ảnh | Tên đuôi mở rộng lấy từ `getClientOriginalExtension` của client | Gửi file với header mime giả | Không dùng hàm đoán `guessExtension()` của server | Thay bằng `guessExtension()` và lọc whitelist an toàn | **ĐÃ XỬ LÝ (100% Tests Passed)** |
| **BUG-DOM-01** | **P2** | Template script | Script template `tron-kiep-nay-ben-nhau` nhận postMessage từ bất kỳ domain nào | Nhúng thiệp vào trang web lạ và gửi `postMessage` | Thiếu `if (evt.origin !== window.location.origin) return;` | Bổ sung kiểm tra origin ngay dòng đầu của handler | **ĐÃ XỬ LÝ (100% Tests Passed)** |
| **BUG-AUDIT-01** | **P1** | Check-in Tiếp Tân | Lệch sĩ số check-in cổng cưới khi khách RSVP nhiều người | Tiếp tân quét mã QR mà không nhập số người đè lên | `CheckInController::processScan` fallback về `expected_attendees` thay vì lấy RSVP thực tế | Thêm `actualAttendeesCount()` vào Model `Guest` ưu tiên sĩ số RSVP `attending` | **ĐÃ XỬ LÝ (100% Tests Passed)** |
| **BUG-AUDIT-02** | **P1** | Seating Planner | Bàn tiệc bị lệch ghế khi khách đổi RSVP | Khách RSVP `declined` vẫn chiếm 1 ghế; khách RSVP 3 người chỉ chiếm 1 ghế | `WeddingTable::assigned_attendees_count` đếm `expected_attendees` | Đổi sang tính theo `actualAttendeesCount()` (0 nếu declined, adults+children nếu attending) | **ĐÃ XỬ LÝ (100% Tests Passed)** |
| **BUG-AUDIT-03** | **P2** | Quản lý khách mời | Lệch hai nguồn sự thật `table_id` và `table_name` khi sửa khách | Sửa hoặc nhập khách có `table_name` nhưng `table_id = null` | `GuestController` chỉ lưu `table_name` mà không liên kết `table_id` | Tự động tra cứu `WeddingTable` cùng tên thuộc `wedding_id` để đồng bộ `table_id` | **ĐÃ XỬ LÝ (100% Tests Passed)** |

