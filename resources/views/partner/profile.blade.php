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
            Cấu hình thông tin Studio để xuất hiện trang trọng ở phần chữ ký bảo chứng dưới chân thiệp cưới của khách hàng và trên trang giới thiệu riêng của Studio.
        </p>
    </div>

    {{-- Showcase Public Link Banner --}}
    <div style="background: linear-gradient(135deg, #FAF8F5 0%, #F5EFEB 100%); border: 1px solid #E5DCCD; border-radius: 12px; padding: 1.25rem 1.5rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <span style="font-size: 0.75rem; letter-spacing: 1px; color: #9C7A4A; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 0.25rem;">Trang Giới Thiệu Riêng Của Studio (Showcase)</span>
            <div style="font-family: monospace; font-size: 0.95rem; color: #1F1D1A; word-break: break-all;">
                {{ route('studio.showcase', $partner->slug) }}
            </div>
            <span style="font-size: 0.8rem; color: #7A726A;">Gửi link này cho khách hàng chụp ảnh cưới để tự động kích hoạt mã hoa hồng của bạn.</span>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('studio.showcase', $partner->slug) }}" target="_blank" class="mayhen_partner_btn" style="background: #FFF; border: 1px solid #D8CEBD; color: #1F1D1A; padding: 0.5rem 1rem; font-size: 0.82rem; text-decoration: none;">
                👁️ Xem Trang
            </a>
            <button type="button" class="mayhen_partner_btn" onclick="navigator.clipboard.writeText('{{ route('studio.showcase', $partner->slug) }}'); alert('Đã sao chép link giới thiệu Studio!');" style="background: #1F1D1A; color: #FFF; border: none; padding: 0.5rem 1rem; font-size: 0.82rem; cursor: pointer;">
                📋 Sao Chép Link
            </button>
        </div>
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

            {{-- 3. Cấu hình Trang Giới Thiệu Riêng (Showcase) --}}
            @php
                $showcaseConfig = is_array($partner->custom_config) && isset($partner->custom_config['showcase'])
                    ? $partner->custom_config['showcase']
                    : [];
                $curatedIds = $showcaseConfig['curated_template_ids'] ?? [];
                $socialLinks = $showcaseConfig['social_links'] ?? [];
            @endphp

            <div style="border-top: 1px solid #EBE5DC; padding-top: 1.5rem; margin-top: 1.5rem;">
                <h3 style="font-size: 1.15rem; font-family: 'Playfair Display', serif; font-weight: 700; color: #1F1D1A; margin-bottom: 0.5rem;">
                    🌐 Cấu Hình Trang Giới Thiệu Riêng (Showcase Công Khai)
                </h3>
                <p style="font-size: 0.85rem; color: #7A726A; margin-bottom: 1.25rem;">
                    Trang web riêng của Studio tại <code>{{ url('/studio/' . $partner->slug) }}</code> giúp bạn tự tin gửi tới các cặp đôi để chọn mẫu thiệp cưới phù hợp.
                </p>

                <div class="form_group">
                    <label class="form_label" for="bio">Đoạn Giới Thiệu Về Studio / Phong Cách Cưới</label>
                    <textarea 
                        name="bio" 
                        id="bio" 
                        class="form_input" 
                        rows="3" 
                        maxlength="500" 
                        placeholder="Ví dụ: Studio chuyên phong cách ảnh cưới tối giản, thanh lịch, đồng hành cùng hơn 500+ cặp đôi tại Hà Nội..."
                    >{{ old('bio', $showcaseConfig['bio'] ?? '') }}</textarea>
                    <span class="form_hint">Tối đa 500 ký tự. Sẽ hiển thị ở phần đầu trang Showcase của bạn.</span>
                </div>

                <div class="form_group">
                    <label class="form_label" for="exclusive_perk">Đặc Quyền Dành Cho Khách Hàng Của Bạn</label>
                    <input 
                        type="text" 
                        name="exclusive_perk" 
                        id="exclusive_perk" 
                        class="form_input" 
                        maxlength="255" 
                        placeholder="Ví dụ: Tặng trọn bộ thiệp lưu niệm số cao cấp khi ký hợp đồng trọn gói..." 
                        value="{{ old('exclusive_perk', $showcaseConfig['exclusive_perk'] ?? '') }}"
                    >
                </div>

                @if(isset($allTemplates) && $allTemplates->count() > 0)
                    <div class="form_group">
                        <label class="form_label">Chọn Các Mẫu Thiệp Đưa Lên Trang Showcase Của Bạn</label>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.75rem; margin-top: 0.5rem;">
                            @foreach($allTemplates as $tpl)
                                <label style="display: flex; align-items: center; gap: 0.65rem; background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 8px; padding: 0.65rem 0.85rem; cursor: pointer; font-size: 0.85rem; color: #1F1D1A;">
                                    <input 
                                        type="checkbox" 
                                        name="curated_template_ids[]" 
                                        value="{{ $tpl->id }}" 
                                        {{ in_array($tpl->id, $curatedIds) ? 'checked' : '' }}
                                        style="width: 16px; height: 16px; accent-color: #C5A880; cursor: pointer;"
                                    >
                                    <span>{{ $tpl->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <span class="form_hint">Nếu không chọn mẫu nào, hệ thống sẽ tự động hiển thị các mẫu thiệp cưới nổi bật nhất.</span>
                    </div>
                @endif
            </div>

            <div style="margin-top: 2rem;">
                <button type="submit" class="mayhen_partner_btn mayhen_partner_btn--gold" style="padding: 0.85rem 1.75rem;">
                    💾 Lưu Cập Nhật Thương Hiệu & Showcase
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

