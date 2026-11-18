# Mây Hẹn — Những điểm đã đối chiếu để giao AI kiểm toán Client Management

**Nguồn khảo sát:** bản repository Google Drive `/drive/folders/1CAFtsLAuI7AbiJrHzbH8FRlxPPVLNmUj`, đọc ngày 18/11/2026. **Giới hạn:** thời gian đồng bộ các file được liệt kê chủ yếu 10/11/2026; đây không phải xác nhận source trên máy hiện tại. Những điểm sau là các tình huống cần xác minh lại, không đồng nghĩa code mới nhất còn lỗi.

## A. Các chức năng đã thấy trong source

- `routes/web.php`: `/tai-khoan`, `/tai-khoan/thiep`, cài đặt, đơn hàng, tài khoản, phát hành, nhân bản, khôi phục, miền riêng; `/tai-khoan/thiep/{id}/khach-moi` cùng thêm khách, nhập nhanh, xuất CSV/in, kế hoạch nhắc, pre-send, thank-you; sơ đồ bàn, ca lễ tân, check-in, Live Wall, Photo Drop, sổ lưu bút, sổ mừng, Time Capsule.
- `GuestController.php`: `index`, `store`, `batchStore`, `exportCsv`, `print`, `update`, `manualRsvp`, `preSendValidation`, `reminderPlanner`, `thankYouBuilder`, `destroy`, `submitRsvp`, các API lời chúc.
- `guests.blade.php`: chỉ số RSVP, tìm kiếm, lọc nhóm/bàn/trạng thái, thêm đơn lẻ, nhập hàng loạt, link mời Zalo, xem như khách, sửa khách, RSVP thủ công, xóa khách.
- `guests.js`: preview lời mời Zalo theo phong cách trang trọng/thân thiết/ấm áp, sao chép tin/link, form sửa, modal RSVP, kiểm tra trước gửi, nhắc khéo, cảm ơn.
- `AccountController.php` và `account/*`: dashboard, thiệp, cài đặt, hồ sơ, đơn hàng, chủ đề ngày cưới và các phân hệ sau cưới.

## B. 7 điểm rất đáng ưu tiên kiểm chứng

1. **Tên KPI và ý nghĩa số liệu:** trong `AccountController::index`, `totalGuests` là tổng `expected_attendees`, trong `account/dashboard.blade.php` có chú thích “Tổng số thiệp mời gửi đi”. Cần xác định có gây hiểu lầm không: số suất dự kiến không chứng minh số link đã gửi.
2. **Hiệu năng danh sách khách lớn:** `GuestController::index` tính `$allGuests = ...->get()` và `$guests = ...->latest()->get()`; với hàng nghìn khách có thể nặng. Phải test với fixture lớn, thiết kế phân trang không làm sai KPI.
3. **“Đã gửi” qua Zalo:** `guests.js` thực hiện `navigator.clipboard.writeText` và mở Zalo; không đủ bằng chứng tin đã được đối tác giao nhận. Giao diện chỉ ghi đúng sự thật.
4. **RSVP lần thứ hai:** `GuestController::submitRsvp` hiện chặn phản hồi trùng bằng thông báo “đã gửi ... rồi và không thể thay đổi”. Cần kiểm tra đây là quyết định nghiệp vụ còn phù hợp không, đặc biệt khách đổi ý, nhiều sự kiện, trước hạn chốt.
5. **Guest management authorization:** một số route Guest được khai báo ngoài `middleware('auth')` và controller có `authorizeGuestManagement` cho session sở hữu thiệp. Đây có thể là cơ chế hỗ trợ chủ thiệp không cần tài khoản, **không tự gọi là lỗ hổng**; phải audit session ownership, IDOR, logout/transfer và phạm vi quyền.
6. **Đồng bộ bàn và sĩ số:** `GuestController::store`, `update`, `manualRsvp` có `table_id`, `table_name`, `expected_attendees`; yêu cầu khớp với `WeddingTable` và live count, kể cả bàn hết sức chứa, hủy RSVP, check-in trước sửa.
7. **Tour quản lý tách tour tạo thiệp:** ở snapshot khảo sát chưa thấy riêng Interactive Product Tour cho account/guests; AI cần xác minh source mới nhất trước khi thêm để không triển khai hai tour chồng nhau.

## C. Sản phẩm mong muốn

- Một hành trình quản lý thiệp đơn giản từ sau khi tạo: hoàn thiện → xuất bản → thêm khách → gửi → theo dõi → xếp bàn → đón tiếp → cảm ơn.
- Mọi chức năng đã có tìm được trong vài thao tác, không tạo menu dư.
- Tour 2 lớp: account toàn cảnh và Guest CRM chi tiết, tự xuất hiện lần đầu đúng ngữ cảnh, có xem lại và bỏ qua.
- Tất cả con số đúng bản chất; tuyệt đối không “PASS ảo” hoặc “đã gửi” khi chỉ sao chép.

## D. Cách dùng

Đặt file này cạnh `MAY_HEN_CLIENT_MANAGEMENT_GUEST_CRM_TOTAL_AUDIT_TOUR_MASTER_PROMPT.md` trong `docs/client-ops/` và yêu cầu coding agent làm W00–W09 theo mã nguồn local mới nhất.
