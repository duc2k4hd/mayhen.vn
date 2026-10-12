# MÂY HẸN (MAYHEN.VN) — THIẾT KẾ CƠ SỞ DỮ LIỆU (DATABASE DESIGN)

## 1. Nguyên Tắc Thiết Kế
- **Không tạo bảng/cột thừa**: Chỉ tạo những thực thể thiết yếu phục vụ nghiệp vụ.
- **Tách bạch dữ liệu**:
  - Dữ liệu Đám Cưới (`weddings`)
  - Mẫu Thiệp Template (`invitation_templates`)
  - Thiệp Cá Nhân Của Khách (`invitations`)
  - Danh Sách Khách Mời & Phản Hồi (`guests`, `guest_responses`)
  - Gói Dịch Vụ & Đơn Hàng (`plans`, `orders`, `payments`)

---

## 2. Danh Sách Các Bảng Cốt Lõi

### A. Users
- `id` (bigint unsigned, PK)
- `name` (varchar 255)
- `email` (varchar 255, unique)
- `phone` (varchar 20, nullable)
- `password` (varchar 255)
- `role` (enum: 'customer', 'admin', 'partner') - default: 'customer'
- `created_at`, `updated_at`

### B. Weddings (Hồ sơ đám cưới)
- `id` (bigint unsigned, PK)
- `user_id` (bigint unsigned, FK -> users.id)
- `groom_name` (varchar 255)
- `bride_name` (varchar 255)
- `groom_parents` (text, nullable)
- `bride_parents` (text, nullable)
- `wedding_date` (date)
- `venue_name` (varchar 255, nullable)
- `venue_address` (text, nullable)
- `story` (text, nullable)
- `cover_photo_url` (varchar 500, nullable)
- `music_url` (varchar 500, nullable)
- `bank_name` (varchar 100, nullable) - Cho QR mừng cưới
- `bank_account_number` (varchar 50, nullable)
- `bank_account_name` (varchar 100, nullable)
- `created_at`, `updated_at`

### C. Invitation Templates (Kho Mẫu Thiệp)
- `id` (bigint unsigned, PK)
- `slug` (varchar 100, unique) - e.g. 'envelope-reveal-luxury', 'editorial-minimal'
- `name` (varchar 255)
- `category` (varchar 100) - e.g. 'luxury', 'editorial', 'traditional', 'minimal'
- `thumbnail_url` (varchar 500)
- `demo_url` (varchar 500, nullable)
- `default_config` (json, nullable) - Chứa tokens màu sắc, font, hiệu ứng mặc định
- `is_active` (boolean, default true)
- `is_featured` (boolean, default false)
- `created_at`, `updated_at`

### D. Invitations (Thiệp Đã Tạo Của Khách)
- `id` (bigint unsigned, PK)
- `wedding_id` (bigint unsigned, FK -> weddings.id)
- `template_id` (bigint unsigned, FK -> invitation_templates.id)
- `slug` (varchar 150, unique) - Đường dẫn công khai thiệp e.g. /thiep/nam-chi-2026
- `title` (varchar 255) - Tên thiệp e.g. 'Thiệp Nhà Trai', 'Thiệp Bạn Bè'
- `version_type` (varchar 50, default 'general') - 'groom_side', 'bride_side', 'friends'
- `custom_config` (json, nullable) - Palette màu đã chọn, thứ tự section bật/tắt
- `is_published` (boolean, default false)
- `published_at` (timestamp, nullable)
- `expires_at` (timestamp, nullable)
- `created_at`, `updated_at`

### E. Guests (Khách Mời)
- `id` (bigint unsigned, PK)
- `wedding_id` (bigint unsigned, FK -> weddings.id)
- `name` (varchar 255) - Tên khách mời e.g. 'Anh Hoàng & Gia đình'
- `salutation` (varchar 100, nullable) - Cách xưng hô e.g. 'Kính mời', 'Thân mời'
- `group_name` (varchar 100, nullable) - e.g. 'Đồng nghiệp', 'Bạn Đại học', 'Họ Hàng'
- `guest_token` (varchar 64, unique) - Token định danh link cá nhân hoá bí mật
- `phone` (varchar 20, nullable)
- `expected_attendees` (int, default 1)
- `is_sent` (boolean, default false)
- `created_at`, `updated_at`

### F. Guest Responses (RSVP & Lời Chúc)
- `id` (bigint unsigned, PK)
- `guest_id` (bigint unsigned, FK -> guests.id, nullable)
- `wedding_id` (bigint unsigned, FK -> weddings.id)
- `guest_name` (varchar 255)
- `attending_status` (enum: 'attending', 'declined', 'uncertain')
- `adults_count` (int, default 1)
- `children_count` (int, default 0)
- `dietary_requirements` (text, nullable)
- `wishes` (text, nullable)
- `is_wishes_public` (boolean, default true)
- `ip_address` (varchar 45, nullable)
- `created_at`, `updated_at`

### G. Plans & Orders
- `plans`: id, slug, name, price, duration_days, max_invitations, features (json).
- `orders`: id, order_code, user_id, plan_id, amount, status ('pending', 'paid', 'failed', 'refunded'), payment_method ('vietqr', 'sepay', 'bank_transfer'), paid_at.
