# Trạng thái Giai đoạn B — 2026-11-15

## Phạm vi và bằng chứng

Đã sửa sáu tệp Markdown thí điểm và các assertion Docs liên quan. Các bài là bản chỉnh theo mã nguồn, **chưa nghiệm thu**; không coi việc render trong test là bằng chứng ảnh, UX, bảo mật hay nghiệp vụ. `php artisan test tests/Feature/DocumentationCenterIntegrityTest.php --compact`: 16 test, 98 assertions, đạt. `phpunit.xml` cấu hình SQLite `:memory:`, mail array, cache array cho test. Chưa chạy bộ test nghiệp vụ riêng.

| ID | Tình trạng tổng thể | Bằng chứng mã / việc còn lại |
| --- | --- | --- |
| CLI-START-002 | BLOCKED | `AuthController`, `OnboardingController`, Blade register/onboarding; test render Docs đạt. Chưa chạy đăng ký, kiểm tra link toàn bộ, ảnh, giao diện desktop/mobile. |
| CLI-EDIT-002 | BLOCKED | `SmartFocalPointController`, `SmartFocalPointService`, `account/focal_point/index.blade.php`: căn tiêu điểm là màn riêng; chưa xác minh luồng tải ảnh, quyền, lưu thật, ảnh và responsive. |
| CLI-GUEST-004 | BLOCKED | `SubmitRsvpRequest`, `GuestController::submitRsvp`, script mẫu `ngay-tinh-yeu-dom-hoa`: không suy ra mọi mẫu hỗ trợ đa sự kiện/ẩm thực. Chưa chạy RSVP, kiểm tra token, ảnh và UI. |
| ADM-USER-001 | BLOCKED | `Admin/UserController::index/show`, views users: bỏ khẳng định mọi lần đọc có audit/che PII. Chưa kiểm thử RBAC, PII, audit, ảnh an toàn. |
| ADM-ORDR-002 | BLOCKED | `admin/pages/orders/show.blade.php`: các trường mã tham chiếu, ngân hàng tùy chọn, ghi chú; nút nhánh Super Admin và đề xuất. Chưa thử đơn giả, quyền, audit, idempotency hoặc ảnh. Không thao tác thanh toán thật. |
| ADM-COCK-002 | BLOCKED | `AdminCheckInController`, view `admin/pages/guests/checkin/index.blade.php`: fetch và thông báo lỗi mạng; chưa xác minh queue offline. Chưa mô phỏng mất mạng, xung đột, quyền hay chụp ảnh. |

Các trạng thái BLOCKED do thiếu kiểm chứng bắt buộc, không phải kết luận tính năng không tồn tại. Bộ test Docs là PASS riêng cho lệnh nêu trên; các test nghiệp vụ, ảnh, browser, bảo mật và nghiệm thu bài là NOT_TESTED/BLOCKED như bảng.

## Trở ngại và bước tiếp tục

1. Browser profile OpenClaw khởi chạy nhưng mở `http://127.0.0.1:8000/dang-ky` và `/docs` bị `browser navigation blocked by policy`. Không né chính sách. Người vận hành cần cấu hình đường truy cập local được browser cho phép bằng cơ chế OpenClaw hỗ trợ; sau đó xác nhận service cô lập, dữ liệu và tài khoản giả, mail/queue an toàn.
2. Chụp UI thật từng bước với dữ liệu giả; không đặt ảnh Admin nhạy cảm trong `public/`. Chèn và kiểm tra trên Docs, mobile/desktop, link, khả năng truy cập và console.
3. Chạy các test nghiệp vụ độc lập liên quan trên DB cô lập; xác minh riêng PII/RBAC/audit, đối soát giả, offline và token; lưu bằng chứng mới từng bài. Hoàn thiện hướng dẫn thay ảnh sau khi xác minh UI thực. Kiểm tra invalidation cache 3600 giây sau sửa Markdown.
4. Kiểm chứng các nguy cơ Docs ở bàn giao 09: metadata Admin index/search/sidebar, role chưa nhận diện fail-open, và scheme/quote URL Markdown. Xử lý chỉ trong Docs/test Docs sau khi có test chứng minh; không tự sửa phân quyền module khác.
5. Antigravity IDE 1.107.0 có `antigravity-ide.cmd chat --mode agent --add-file`, nhưng chưa có kênh đã chứng minh để nhận phản hồi hoặc xác nhận agent thực thi. Không tuyên bố đã giao việc. Chủ dự án có thể mở repo trong IDE, đưa prompt dưới đây cùng file 09 và file này vào chat agent, rồi chuyển diff và kết quả test lại để kiểm chứng.

## Prompt chuyển trực tiếp cho Antigravity

> Tiếp tục Giai đoạn B Docs Mây Hẹn tại `D:\Project\Laravel\mayhen.vn`; đọc `AGENTS.md`, `context.gemini.md`, `docs/docs-center/09_ANTIGRAVITY_HANDOFF_GIAI_DOAN_A.md` và `10_TRANG_THAI_GIAI_DOAN_B.md`. Không khảo sát lại; giữ các thay đổi chưa commit và hai tệp untracked có trước. Chỉ sửa Docs/test Docs; không commit, push, deploy, đổi module khác hoặc thao tác dữ liệu thật. Đối chiếu sáu bài thí điểm với mã và UI, hoàn thiện các bước còn thiếu, kiểm thử bằng môi trường tách biệt và dữ liệu giả. Ảnh phải là UI thật, không lộ PII, ảnh Admin không public. Kiểm thử các nguy cơ Docs bằng test an toàn; ghi PASS/FAIL/BLOCKED/NOT_TESTED theo bằng chứng, báo đầy đủ diff và kết quả. Nếu không đủ môi trường, ghi blocker cụ thể, tiếp tục phần độc lập.

## Lưu ý Git

Không commit/push/deploy. Hai tệp untracked có trước: `docs/docs-center/01_DOCS_SITEMAP_AND_READER_JOURNEYS.zip` và `docs/qa/03_QA_SCREENSHOT.md`; giữ nguyên. `09_ANTIGRAVITY_HANDOFF_GIAI_DOAN_A.md` và tệp này là bàn giao mới chưa theo dõi.
