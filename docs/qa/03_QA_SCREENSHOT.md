# QA GATES & HÌNH ẢNH — MÂY HẸN DOCS

## Nguyên tắc
21 `PASS` trong tệp planning `08_DOCS_VERIFICATION_GATES.csv` phải coi là dữ liệu nhập, chưa phải kết quả kiểm thử hiện tại. Mỗi gate được thực hiện lại trên local/staging, ghi `git commit`, ngày, kết quả, bằng chứng.

## Checklist mỗi bài

- [ ] Đã đọc `AGENTS.md`, `context.gemini.md`, skill liên quan và tài liệu nguồn.
- [ ] Article_ID khớp backlog, không trùng ID/slug, có đúng audience và vị trí docs.
- [ ] Chức năng hiện có và quyền thực tế được kiểm tra trên local.
- [ ] Tên nút, menu, URL, thông báo và giới hạn nhập được xác minh với UI + code.
- [ ] Chuẩn bị, các bước, kết quả, troubleshooting, FAQ đều đầy đủ và không phóng đại.
- [ ] Từng bước quan trọng có screenshot thật, phù hợp version/role và caption.
- [ ] Ảnh rõ chữ, alt text diễn giải; load thành công; responsive; không ảnh trùng vô ích.
- [ ] Không có PII, session token, số tài khoản thật, QR nhạy cảm, API keys trong file ảnh và repository.
- [ ] Các thao tác click/submit đã thử trên dữ liệu giả, không giao dịch/sending thật.
- [ ] Giao diện bài viết đẹp và nhất quán với Docs hiện có.
- [ ] Ảnh mobile 390px, desktop 1440px (hoặc viewport phù hợp) không tràn/cắt chữ.
- [ ] Sidebar, breadcrumb, TOC, dark-mode, search, keyboard focus chạy đúng.
- [ ] URL bài, href liên quan, ảnh, meta/canonical public không broken.
- [ ] `/docs/search` không có dữ liệu nội bộ Admin; test cả query, cache và direct endpoint.
- [ ] `/admin/docs` có auth/RBAC backend; noindex bổ trợ, không thay auth.
- [ ] Admin asset nhạy cảm được bảo vệ bằng phương án truy cập có quyền.
- [ ] Test Laravel/Pest liên quan chạy và log lại output, không sửa test để hợp thức hóa.
- [ ] Test regression cho Docs khác và mã nguồn kinh doanh không lỗi.
- [ ] Cập nhật tracking + `context.gemini.md` sau thay đổi trong repo.

## Screenshot manifest đề xuất

| Trường | Ý nghĩa |
|---|---|
| Article_ID | Ví dụ CLI-EDIT-002 |
| Step_ID | step-03 |
| Space | Client/Admin |
| Role | Chủ thiệp / Khách mời / Finance / Support... |
| UI_Commit | `git rev-parse --short HEAD` lúc chụp |
| Page_URL | Path không có token/secret |
| Viewport | 1440x900 hoặc 390x844 |
| Raw_File | Ảnh gốc trong khu vực staging artifacts an toàn |
| Published_File | Ảnh đã cắt/ẩn dữ liệu và được phép xuất bản |
| Description/ALT | Mô tả đúng bước người đọc cần làm |
| Captured_Date | Ngày thực tế |
| Verification | EXISTS / MATCHES_STEP / SAFE / BROKEN |

## Tình huống bảo mật phải test

1. Chưa login vào `/admin/docs` và API search Admin: không được nhận nội dung bảo mật.
2. Đăng nhập Admin role thấp đọc SOP role cao: backend thực sự từ chối.
3. Public search với keyword SOP tài chính: không tiết lộ nội dung hoặc tiêu đề Admin.
4. Kiểm tra HTML/cache/search index/page source có nhúng data Admin không.
5. Admin screenshot URL: nếu nhạy cảm, không được truy cập qua URL public dù có noindex.
6. Nếu Markdown hỗ trợ HTML/raw link: sanitize XSS, kiểm tra ký tự và query search.

## Chặn sai lệch đặc biệt trong tài liệu nguồn

- Bài mẫu `CLI-EDIT-002` mô tả giới hạn ảnh 10 MB, Smart Focal Point, thông báo đã lưu; cần verify điều kiện thực tế trước khi dùng.
- Bài mẫu `CLI-GUEST-004` mô tả RSVP đa sự kiện/khẩu phần; cần verify từng trường.
- `ADM-ORDR-002` mô tả nút kích hoạt thủ công, audit log, gửi email; không được viết thành hướng dẫn live nếu workflow chưa tồn tại.
- `ADM-COCK-002` mô tả Emergency Offline Pack; không được xác nhận nếu chưa có UI và flow thật.
- Ảnh Admin dùng dữ liệu giả vẫn có thể nhạy cảm nếu tiết lộ cấu hình, token hoặc quyền; kiểm tra từng file.

## Chuẩn báo cáo sau từng bài

```
Article_ID: ...
Status: DONE_VERIFIED | QA_FAILED | BLOCKED_... | NEEDS_REVIEW
Source_commit: ...
Docs_URL: ...
UI_routes_verified: ...
UI_labels_verified: ...
Screenshots: N file, verified M file
Automated_tests: test command + passed/failed counts (actual)
Browser_tests: viewport + pass/fail + notes
Security_tests: ...
Changed_files: ...
Unresolved: ...
Next_task: ...
```

Không tính số lượng ảnh bằng `N` nếu file thực không tồn tại. Chỉ dùng DONE_VERIFIED khi tất cả gate thích hợp đều có bằng chứng.
