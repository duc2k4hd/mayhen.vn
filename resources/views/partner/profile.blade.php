@extends('partner.layouts.master')

@section('title', 'Hồ Sơ & Thương Hiệu Studio | ' . config('app.name'))

@section('content')
<div style="max-width: 760px; margin: 0 auto;">
    {{-- Breadcrumbs --}}
    <div class="mayhen_partner_breadcrumbs">
        <a href="{{ route('partner.dashboard') }}">Cổng Đối Tác</a>
        <span>/</span>
        <span>Hồ Sơ & Thương Hiệu Studio</span>
    </div>

    <div style="margin-bottom: 2rem;">
        <h1 class="dash_head_title" style="font-size: 1.85rem;">Hồ Sơ Thương Hiệu & Co-Branding</h1>
        <p class="dash_head_desc">
            Cấu hình thông tin Studio để xuất hiện trang trọng ở phần chữ ký bảo chứng dưới chân thiệp cưới của khách hàng.
        </p>
    </div>

    @if(session('success'))
        <div class="mayhen_partner_alert mayhen_partner_alert--success">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mayhen_partner_alert mayhen_partner_alert--error">
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mayhen_partner_card">
        <form action="{{ route('partner.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form_group">
                <label class="form_label" for="business_name">Tên Studio / Thương Hiệu *</label>
                <input 
                    type="text" 
                    name="business_name" 
                    id="business_name" 
                    class="form_input" 
                    required 
                    value="{{ old('business_name', $partner->business_name) }}"
                >
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form_group">
                    <label class="form_label" for="phone">Số điện thoại *</label>
                    <input 
                        type="tel" 
                        name="phone" 
                        id="phone" 
                        class="form_input" 
                        required 
                        value="{{ old('phone', $partner->phone) }}"
                    >
                </div>

                <div class="form_group">
                    <label class="form_label" for="email">Email liên hệ *</label>
                    <input 
                        type="email" 
                        name="email" 
                        id="email" 
                        class="form_input" 
                        required 
                        value="{{ old('email', $partner->email) }}"
                    >
                </div>
            </div>

            <div class="form_group">
                <label class="form_label" for="website">Website / Fanpage Studio</label>
                <input 
                    type="url" 
                    name="website" 
                    id="website" 
                    class="form_input" 
                    placeholder="https://..." 
                    value="{{ old('website', $partner->website) }}"
                >
            </div>

            <div class="form_group">
                <label class="form_label" for="address">Địa chỉ cơ sở Studio</label>
                <input 
                    type="text" 
                    name="address" 
                    id="address" 
                    class="form_input" 
                    value="{{ old('address', $partner->address) }}"
                >
            </div>

            {{-- Logo Upload --}}
            <div class="form_group" style="border-top: 1px solid #EBE5DC; padding-top: 1.5rem; margin-top: 1.5rem;">
                <label class="form_label" for="logo">Logo Studio (PNG trong suốt, SVG hoặc JPG)</label>
                @if($partner->logo_url)
                    <div style="margin-bottom: 0.75rem; display: flex; align-items: center; gap: 1rem;">
                        <img src="{{ $partner->logo_url }}" alt="Logo {{ $partner->business_name }}" style="height: 48px; width: auto; object-fit: contain; background: #FAF8F5; padding: 6px; border: 1px solid #EBE5DC; border-radius: 8px;">
                        <span style="font-size: 0.8rem; color: #8C8479;">Logo hiện tại đang dùng</span>
                    </div>
                @endif
                <input type="file" name="logo" id="logo" class="form_input" accept="image/png,image/jpeg,image/webp,image/svg+xml">
                <span class="form_hint">Khuyến khích dùng logo nền trong suốt (PNG), kích thước tối đa 4MB.</span>
            </div>

            {{-- Co-branding Footer Text --}}
            @php
                $brandingConfig = is_array($partner->custom_config) && isset($partner->custom_config['branding'])
                    ? $partner->custom_config['branding']
                    : [];
                $defaultFooterText = 'Đồng hành cùng ' . $partner->business_name;
                $currentFooterText = $brandingConfig['footer_text'] ?? $defaultFooterText;
                $showBadge = $brandingConfig['show_badge'] ?? true;
            @endphp

            <div class="form_group">
                <label class="form_label" for="footer_text">Dòng chữ bảo chứng Co-Branding dưới chân thiệp cưới</label>
                <input 
                    type="text" 
                    name="footer_text" 
                    id="footer_text" 
                    class="form_input" 
                    maxlength="150"
                    placeholder="Ví dụ: Thiết kế & Đồng hành bởi Élysee Wedding Studio" 
                    value="{{ old('footer_text', $currentFooterText) }}"
                >
                <span class="form_hint">Dòng chữ này sẽ xuất hiện trang trọng ở phần Ending thiệp cưới của khách hàng.</span>
            </div>

            <div class="form_group" style="display: flex; align-items: center; gap: 0.65rem;">
                <input 
                    type="checkbox" 
                    name="show_badge" 
                    id="show_badge" 
                    value="1" 
                    {{ $showBadge ? 'checked' : '' }}
                    style="width: 18px; height: 18px; accent-color: #C5A880; cursor: pointer;"
                >
                <label for="show_badge" style="font-size: 0.88rem; color: #38332D; cursor: pointer; user-select: none;">
                    Bật huy hiệu bảo chứng Studio ở chân thiệp cưới của khách hàng
                </label>
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="mayhen_partner_btn mayhen_partner_btn--gold" style="padding: 0.85rem 1.75rem;">
                    💾 Lưu Cập Nhật Thương Hiệu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

