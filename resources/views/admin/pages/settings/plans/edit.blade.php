@extends('admin.layouts.master')

@section('title', 'Chỉnh Sửa Gói Dịch Vụ')
@section('page_title', 'Chỉnh Sửa Gói: ' . $plan->name)

@section('breadcrumb_parent')
    <a href="{{ route('admin.settings.plans') }}">Bảng Giá & Gói Dịch Vụ</a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/settings.css') }}">
@endpush

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.settings.plans') }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="display: inline-flex; align-items: center; gap: 6px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Quay lại Danh Sách Gói</span>
        </a>
    </div>

    <div class="mayhen_admin_card" style="max-width: 800px; padding: 28px;">
        <form action="{{ route('admin.settings.plans.update', $plan->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Plan Name -->
            <div style="margin-bottom: 20px;">
                <label class="mayhen_admin_label" for="plan_name">Tên Gói Dịch Vụ <span style="color: #C62828;">*</span></label>
                <input 
                    type="text" 
                    id="plan_name" 
                    name="name" 
                    value="{{ old('name', $plan->name) }}" 
                    class="mayhen_admin_input" 
                    required
                >
                @error('name')
                    <div style="color: #C62828; font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Price & Duration Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                <div>
                    <label class="mayhen_admin_label" for="plan_price">Giá Bán (VNĐ) <span style="color: #C62828;">*</span></label>
                    <input 
                        type="number" 
                        id="plan_price" 
                        name="price" 
                        value="{{ old('price', $plan->price) }}" 
                        class="mayhen_admin_input" 
                        min="0" 
                        step="1000" 
                        required
                    >
                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 4px;">Nhập 0 nếu là gói Miễn phí</div>
                    @error('price')
                        <div style="color: #C62828; font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="mayhen_admin_label" for="duration_days">Thời Hạn (Ngày) <span style="color: #C62828;">*</span></label>
                    <input 
                        type="number" 
                        id="duration_days" 
                        name="duration_days" 
                        value="{{ old('duration_days', $plan->duration_days) }}" 
                        class="mayhen_admin_input" 
                        min="1" 
                        required
                    >
                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 4px;">Ví dụ: 30, 90, 365 ngày</div>
                    @error('duration_days')
                        <div style="color: #C62828; font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="mayhen_admin_label" for="max_invitations">Số Thiệp Tối Đa <span style="color: #C62828;">*</span></label>
                    <input 
                        type="number" 
                        id="max_invitations" 
                        name="max_invitations" 
                        value="{{ old('max_invitations', $plan->max_invitations) }}" 
                        class="mayhen_admin_input" 
                        min="1" 
                        required
                    >
                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 4px;">Số thiệp được tạo trong gói</div>
                    @error('max_invitations')
                        <div style="color: #C62828; font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Features Checkboxes -->
            <div style="margin-bottom: 24px;">
                <label class="mayhen_admin_label">Danh Sách Tính Năng Của Gói</label>
                <div style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 12px;">
                    Chọn các tính năng được mở khóa cho khách hàng khi kích hoạt gói này:
                </div>

                @php
                    $currentFeatures = is_array($plan->features) ? $plan->features : (json_decode($plan->features, true) ?? []);
                @endphp

                <div class="mayhen_admin_features_checkbox_grid">
                    @foreach($availableFeatures as $feat)
                        <label class="mayhen_admin_feature_checkbox_label">
                            <input 
                                type="checkbox" 
                                name="features[]" 
                                value="{{ $feat }}" 
                                {{ in_array($feat, old('features', $currentFeatures)) ? 'checked' : '' }}
                                style="width: 16px; height: 16px; accent-color: var(--mh-admin-gold);"
                            >
                            <span>{{ $feat }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Active Status -->
            <div style="margin-bottom: 28px; padding-top: 16px; border-top: 1px solid var(--mh-admin-border-subtle);">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input 
                        type="checkbox" 
                        name="is_active" 
                        value="1" 
                        {{ old('is_active', $plan->is_active) ? 'checked' : '' }}
                        style="width: 18px; height: 18px; accent-color: var(--mh-admin-gold);"
                    >
                    <div>
                        <div style="font-weight: 600; color: var(--mh-admin-charcoal);">Kích hoạt mở bán gói này</div>
                        <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">Gói sẽ xuất hiện trên trang bảng giá và màn hình nâng cấp thiệp.</div>
                    </div>
                </label>
            </div>

            <!-- Submit -->
            <div style="display: flex; gap: 12px;">
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="padding: 10px 24px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Lưu Cập Nhật Gói Dịch Vụ</span>
                </button>
                <a href="{{ route('admin.settings.plans') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                    <span>Hủy</span>
                </a>
            </div>
        </form>
    </div>
@endsection

