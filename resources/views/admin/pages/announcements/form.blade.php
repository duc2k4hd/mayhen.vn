@extends('admin.layouts.master')

@section('title', $announcement->exists ? 'Chỉnh Sửa Thông Báo #' . $announcement->id : 'Thêm Mới Thông Báo Công Khai')
@section('page_title', $announcement->exists ? 'Chỉnh Sửa Thông Báo #' . $announcement->id : 'Tạo Mới Thông Báo Công Khai')

@section('content')
<div class="mayhen_admin_content_wrap" style="max-width: 800px; margin: 0 auto;">
    <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 12px; padding: 24px;">
        <form action="{{ $announcement->exists ? route('admin.announcements.update', $announcement->id) : route('admin.announcements.store') }}" method="POST">
            @csrf
            @if($announcement->exists)
                @method('PUT')
            @endif

            <!-- Tiêu đề -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                    Tiêu Đề Thông Báo <span style="color: #DC2626;">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title', $announcement->title) }}" required class="mayhen_admin_form_input" style="width: 100%; font-size: 14px;" placeholder="VD: Mừng Mùa Cưới 2026: Ưu đãi đặc biệt giảm 20% cho tất cả các gói thiệp...">
                @error('title')
                    <div style="color: #DC2626; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Nội dung chi tiết -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                    Nội Dung Rút Gọn <span style="color: #DC2626;">*</span>
                </label>
                <textarea name="content" rows="3" required class="mayhen_admin_form_input" style="width: 100%; font-size: 14px;" placeholder="VD: Nhập mã MAYHEN2026 khi thanh toán để nhận ngay quà tặng bộ thiệp lưu niệm số cao cấp...">{{ old('content', $announcement->content) }}</textarea>
                @error('content')
                    <div style="color: #DC2626; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Grid 2 cột: Vị trí & Phân loại -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Vị Trí Hiển Thị (Placement) <span style="color: #DC2626;">*</span>
                    </label>
                    <select name="placement" class="mayhen_admin_form_input" style="width: 100%; font-size: 14px;">
                        <option value="top_bar" {{ old('placement', $announcement->placement) === 'top_bar' ? 'selected' : '' }}>
                            Thanh thông báo đầu trang (Top Bar)
                        </option>
                        <option value="client_dashboard" {{ old('placement', $announcement->placement) === 'client_dashboard' ? 'selected' : '' }}>
                            Bảng điều khiển cặp đôi (Dashboard)
                        </option>
                        <option value="modal_popup" {{ old('placement', $announcement->placement) === 'modal_popup' ? 'selected' : '' }}>
                            Hộp thoại Popup chào mừng
                        </option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Phân Loại Tông Màu (Type) <span style="color: #DC2626;">*</span>
                    </label>
                    <select name="type" class="mayhen_admin_form_input" style="width: 100%; font-size: 14px;">
                        <option value="promotion" {{ old('type', $announcement->type) === 'promotion' ? 'selected' : '' }}>
                            Ưu Đãi & Khuyến Mãi (Vàng Champagne Quiet Luxury)
                        </option>
                        <option value="info" {{ old('type', $announcement->type) === 'info' ? 'selected' : '' }}>
                            Thông Tin Chung & Tính Năng Mới (Xanh dịu)
                        </option>
                        <option value="warning" {{ old('type', $announcement->type) === 'warning' ? 'selected' : '' }}>
                            Cảnh Báo & Lịch Bảo Trì (Vàng hổ phách)
                        </option>
                        <option value="danger" {{ old('type', $announcement->type) === 'danger' ? 'selected' : '' }}>
                            Khẩn Cấp / Sự Cố Kỹ Thuật (Đỏ)
                        </option>
                    </select>
                </div>
            </div>

            <!-- Grid 2 cột: Nút bấm CTA & Link -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Chữ Trên Nút Bấm CTA (Tùy chọn)
                    </label>
                    <input type="text" name="cta_text" value="{{ old('cta_text', $announcement->cta_text) }}" class="mayhen_admin_form_input" style="width: 100%; font-size: 14px;" placeholder="VD: Khám Phá Ngay, Xem Bảng Giá...">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Đường Dẫn Đích CTA (Tùy chọn)
                    </label>
                    <input type="text" name="cta_url" value="{{ old('cta_url', $announcement->cta_url) }}" class="mayhen_admin_form_input" style="width: 100%; font-size: 14px;" placeholder="VD: /bang-gia hoặc https://...">
                </div>
            </div>

            <!-- Grid 2 cột: Khung giờ hẹn lịch -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Thời Điểm Bắt Đầu (Để trống nếu hiển thị ngay)
                    </label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $announcement->starts_at ? $announcement->starts_at->format('Y-m-d\TH:i') : '') }}" class="mayhen_admin_form_input" style="width: 100%; font-size: 14px;">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Thời Điểm Kết Thúc (Để trống nếu không giới hạn)
                    </label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $announcement->ends_at ? $announcement->ends_at->format('Y-m-d\TH:i') : '') }}" class="mayhen_admin_form_input" style="width: 100%; font-size: 14px;">
                </div>
            </div>

            <!-- Checkbox tùy chọn -->
            <div style="margin-bottom: 24px; padding: 14px; background: #F9FAFB; border-radius: 8px; border: 1px solid #E5E7EB; display: flex; flex-direction: column; gap: 10px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #374151; cursor: pointer;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}>
                    <span><strong>Kích Hoạt Ngay:</strong> Bật hiển thị thông báo ra toàn hệ thống (khi đúng khung giờ).</span>
                </label>

                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #374151; cursor: pointer;">
                    <input type="checkbox" name="is_dismissible" value="1" {{ old('is_dismissible', $announcement->is_dismissible) ? 'checked' : '' }}>
                    <span><strong>Cho Phép Đóng:</strong> Hiển thị nút &times; cho phép khách bấm tắt và không hiện lại trên trình duyệt đó.</span>
                </label>
            </div>

            <!-- Nút hành động -->
            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('admin.announcements.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                    Hủy Bỏ
                </a>
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">
                    {{ $announcement->exists ? 'Lưu Thay Đổi' : 'Tạo Thông Báo' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

