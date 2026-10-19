# 🗄️ ĐÁNH GIÁ TÁC ĐỘNG CƠ SỞ DỮ LIỆU & KẾ HOẠCH MIGRATION (EDITOR V2 DATA IMPACT)

> **Dự án**: Mây Hẹn (`mayhen.vn`)  
> **Tài liệu**: Database Schema Audit, Impact Assessment & Safe Migration Strategy  
> **Nguyên tắc an toàn**: Không phá hủy dữ liệu, không drop bảng, 100% Backward Compatible với các thiệp cưới đang hoạt động.

---

## 1. KIỂM TOÁN CẤU TRÚC DATABASE HIỆN TẠI LIÊN QUAN ĐẾN EDITOR

### 1.1. Bảng `weddings` (Hồ Sơ Đám Cưới)
- **Các cột hiện có**: `id`, `user_id`, `groom_name`, `bride_name`, `groom_parents`, `bride_parents`, `wedding_date`, `venue_name`, `venue_address`, `story`, `cover_photo_url`, `music_url`, `bank_name`, `bank_account_number`, `bank_account_name`, `extra_data` (json), `timestamps`.
- **Đánh giá**: 
  - Cấu trúc cơ bản rất tốt, lưu trữ đầy đủ thông tin thực thể của một đám cưới.
  - *Vấn đề tồn tại*: Cột `extra_data` hiện tại đang chứa lẫn lộn cả album ảnh (`gallery`) lẫn các mốc sự kiện (`timeline`). Cần chuẩn hóa cấu trúc JSON này để tránh ghi đè dữ liệu khi người dùng tạo nhiều thiệp khác nhau cho cùng một đám cưới.

### 1.2. Bảng `invitations` (Thiệp Mời Khách Hàng)
- **Các cột hiện có**: `id`, `wedding_id`, `template_id`, `slug`, `title`, `version_type`, `template_version`, `custom_config` (json), `is_published`, `published_at`, `expires_at`, `timestamps`.
- **Đánh giá**:
  - *Vấn đề tồn tại*:
    1. Cột `custom_config` đang chứa JSON tự do không có phiên bản schema định danh, dẫn đến nguy cơ lỗi giao diện khi template nâng cấp code.
    2. Thiếu cột `revision_id` dẫn đến không thể kiểm soát việc 2 tab cùng chỉnh sửa 1 thiệp gây mất dữ liệu (Concurrency Collision).
    3. Thiếu cột `draft_config` để phân tách giữa **bản đang chỉnh sửa dở dang** và **bản đang công khai cho khách xem**. Khi khách hàng đang sửa thiệp dở (chưa muốn người ngoài thấy), bản công khai `/thiep/{slug}` không nên bị đổi ngay lập tức!

### 1.3. Bảng `invitation_templates` (Kho Mẫu Thiệp)
- **Các cột hiện có**: `id`, `slug`, `name`, `category`, `thumbnail_url`, `demo_url`, `default_config` (json), `current_version`, `is_active`, `is_featured`, `timestamps`.
- **Đánh giá**: Hoàn toàn đáp ứng tốt cho Editor V2, chỉ cần chuẩn hóa cấu trúc `default_config` theo đúng chuẩn Schema V2.

---

## 2. ĐỀ XUẤT THAY ĐỔI SCHEMA AN TOÀN (SAFE INCREMENTAL SCHEMA CHANGES)

Tuyệt đối **KHÔNG** xóa bảng hay chạy lệnh phá hủy (`migrate:fresh`). Mọi thay đổi đều được thực hiện thông qua các Migration bổ sung trường (Additive Migrations):

### 2.1. Migration 1: Bổ Sung Trường Quản Lý Bản Nháp & Concurrency Cho Bảng `invitations`
File migration: `database/migrations/2026_10_20_000001_add_editor_v2_columns_to_invitations_table.php`

```php
Schema::table('invitations', function (Blueprint $table) {
    // 1. Quản lý phiên bản Document Schema
    $table->string('schema_version', 20)->default('2.0.0')->after('template_version');
    
    // 2. Khóa lạc quan chống ghi đè đồng thời (Optimistic Locking)
    $table->unsignedBigInteger('revision_id')->default(1)->after('schema_version');
    
    // 3. Tách biệt bản nháp đang sửa (Draft) và bản xuất bản (Published)
    $table->json('draft_config')->nullable()->after('custom_config');
    
    // 4. Thời điểm lưu nháp gần nhất (phục vụ hiển thị Autosave status)
    $table->timestamp('last_autosaved_at')->nullable()->after('draft_config');
});
```

### 2.2. Migration 2: Bảng Quản Lý Thư Viện Ảnh Cưới Tập Trung (Tùy Chọn Mở Rộng - Phase G)
File migration: `database/migrations/2026_10_20_000002_create_wedding_media_table.php`

```php
Schema::create('wedding_media', function (Blueprint $table) {
    $table->id();
    $table->foreignId('wedding_id')->constrained('weddings')->cascadeOnDelete();
    $table->string('file_path', 500); // Lưu trữ trong storage/app/public/weddings/{id}/
    $table->string('file_name', 255);
    $table->string('mime_type', 50)->default('image/webp');
    $table->unsignedInteger('file_size')->default(0); // bytes
    $table->unsignedSmallInteger('width')->nullable();
    $table->unsignedSmallInteger('height')->nullable();
    $table->json('crop_settings')->nullable(); // Lưu tọa độ zoom, offset 3:4
    $table->timestamps();
    
    $table->index(['wedding_id', 'created_at']);
});
```

---

## 3. CHIẾN LƯỢC TƯƠNG THÍCH NGƯỢC & DATA MIGRATION PLAN

Nhằm bảo vệ 100% các thiệp cưới cũ của khách hàng đang lưu hành trên internet không bị hỏng bố cục hay mất liên kết:

### 3.1. Kế Hoạch Chuyển Đổi Dữ Liệu Tự Động (Data Migration Command)
Xây dựng một Artisan Command: `php artisan mayhen:migrate-invitations-v1-to-v2`
- **Nguyên lý hoạt động**:
  1. Quét toàn bộ các bản ghi trong bảng `invitations` có `schema_version` là `NULL` hoặc `v1`.
  2. Bóc tách dữ liệu từ `custom_config` cũ.
  3. Tự động ánh xạ vào cấu trúc Document Schema V2:
     - Biến đổi mảng `sections` cũ thành danh sách section có trường `order` và `visible: true`.
     - Chuyển `accent_color` vào `theme.palette.accent`.
     - Sao chép nội dung sang `draft_config` để đồng bộ.
  4. Đánh dấu `schema_version = '2.0.0'` và `revision_id = 1`.
  5. Đảm bảo chạy trong Transaction, có log chi tiết từng bản ghi và kiểm thử dry-run (`--dry-run`) trước khi cập nhật thật.

### 3.2. Fallback Adapter trong Blade Template
Trong file Blade của template, bổ sung Adapter tương thích ngược:
```blade
@php
    // Tự động nhận diện nếu thiệp vẫn là format V1
    $isV2 = ($invitation->schema_version ?? 'v1') === '2.0.0';
    $config = $isV2 ? ($invitation->custom_config ?? []) : \App\Services\InvitationTemplateService::adaptV1ToV2($invitation->custom_config);
@endphp
```
*Kết quả*: Kể cả khi chưa chạy lệnh migrate database, mọi thiệp cũ vẫn hiển thị hoàn hảo 100% mà không bị lỗi giao diện!

---

## 4. KẾ HOẠCH ROLLBACK DỰ PHÒNG (CONTINGENCY & ROLLBACK PLAN)

Nếu có sự cố phát sinh trong quá trình triển khai:
1. **Rollback Migration**:
   ```bash
   php artisan migrate:rollback --step=2
   ```
   Hệ thống xóa bỏ các cột mới `schema_version`, `revision_id`, `draft_config`, đưa database về đúng trạng thái ban đầu mà không làm mất cột `custom_config` cũ.
2. **Bảo toàn dữ liệu hình ảnh**:
   Toàn bộ file ảnh tải lên mới trong `storage/app/public/` hoàn toàn độc lập, không bị xóa khi rollback code.
