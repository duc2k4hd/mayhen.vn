Hãy kiểm tra và tối ưu hóa toàn diện hiệu năng, tốc độ tải trang và khả năng chịu tải hàng triệu user cho trang /mau-thiep/ và /mau-thiep/slug (bao gồm cả các biến thể có query param).

Yêu cầu kỹ thuật bắt buộc phải tuân thủ:

Tốc độ phụ thuộc 100% vào code nhanh: Rà soát controller, service, model và các component Blade liên quan; tinh gọn thuật toán, loại bỏ toàn bộ logic thừa, ép thời gian thực thi mã PHP xuống mức tối thiểu trước khi tính đến cache.
Triệt tiêu nghẽn Database (Zero DB Bottleneck): Cache chỉ dùng để tránh gọi lại database nhiều lần. Đảm bảo khi 1 triệu user truy cập đồng thời, tuyệt đối không có thao tác nào bị đồn vào MySQL (kiểm tra kỹ middleware, view composers, layout components, header, footer, announcement bar).
Static RAM Memoization trong Worker PHP: Áp dụng biến tĩnh (static memoization) trong tiến trình PHP (FPM / Octane) để các lượt gọi cùng vòng đời worker trả về tức thì trong 0.00ms, không tốn I/O, không gọi lại cache driver lặp lại.
Edge CDN Caching & ETag / 304 Not Modified: Thiết lập HTTP Headers chuẩn (ETag, Last-Modified, Cache-Control) để CDN (Cloudflare) và trình duyệt có thể trả về 304 Not Modified ngay lập tức, tiết kiệm 99% tài nguyên server. Vẫn bảo toàn hiển thị cá nhân hóa chính xác khi user đã đăng nhập.
Kiểm thử tự động bằng Pest: Viết test đo lường thực tế, bật DB::enableQueryLog() để kiểm chứng đạt tuyệt đối 0 câu truy vấn DB khi phục vụ trang, kiểm tra phản hồi 304 và đảm bảo 100% test suites liên quan đều PASS.
Quy chuẩn bàn giao: Định dạng code chuẩn bằng vendor/bin/pint --dirty --format agent, cập nhật đầy đủ vào context.gemini.md và gợi ý thông điệp commit Git bằng tiếng Việt.