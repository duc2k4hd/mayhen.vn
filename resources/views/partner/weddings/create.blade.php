@extends('partner.layouts.master')

@section('title', 'Khởi Tạo Thiệp Cưới Khách Hàng | ' . config('app.name'))

@section('content')
<div style="max-width: 880px; margin: 0 auto;">
    {{-- Breadcrumbs --}}
    <div class="mayhen_partner_breadcrumbs">
        <a href="{{ route('partner.dashboard') }}">Cổng Đối Tác</a>
        <span>/</span>
        <a href="{{ route('partner.weddings.index') }}">Thiệp Khách Hàng</a>
        <span>/</span>
        <span>Khởi Tạo Mới</span>
    </div>

    <div style="margin-bottom: 2rem;">
        <h1 class="dash_head_title" style="font-size: 2rem;">Khởi Tạo Thiệp Mới Cho Khách Hàng</h1>
        <p class="dash_head_desc">
            Chọn mẫu thiết kế và điền thông tin ban đầu. Sau khi tạo xong, hệ thống sẽ mở Studio để bạn hoàn thiện nội dung hoặc gửi mã bàn giao cho cô dâu chú rể.
        </p>
    </div>

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
        <form action="{{ route('partner.weddings.store') }}" method="POST">
            @csrf

            {{-- Template Selector --}}
            <div class="form_group">
                <label class="form_label">1. Chọn Mẫu Thiệp Cưới *</label>
                <div class="templates_select_grid">
                    @foreach($templates as $index => $t)
                        <label class="template_radio_card {{ $index === 0 ? 'is-selected' : '' }}">
                            <input 
                                type="radio" 
                                name="template_id" 
                                value="{{ $t->id }}" 
                                class="template_radio_input"
                                {{ $index === 0 ? 'checked' : '' }}
                            >
                            <div class="template_card_thumb">
                                <img src="{{ $t->thumbnail_url }}" alt="{{ $t->name }}" loading="lazy">
                            </div>
                            <div class="template_card_info">
                                <span class="template_card_title">{{ $t->name }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Couple Names --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-top: 1.5rem;">
                <div class="form_group">
                    <label class="form_label" for="groom_name">2. Tên Chú Rể *</label>
                    <input 
                        type="text" 
                        name="groom_name" 
                        id="groom_name" 
                        class="form_input" 
                        required 
                        placeholder="Ví dụ: Hoàng Long" 
                        value="{{ old('groom_name') }}"
                    >
                </div>

                <div class="form_group">
                    <label class="form_label" for="bride_name">Tên Cô Dâu *</label>
                    <input 
                        type="text" 
                        name="bride_name" 
                        id="bride_name" 
                        class="form_input" 
                        required 
                        placeholder="Ví dụ: Khánh Linh" 
                        value="{{ old('bride_name') }}"
                    >
                </div>
            </div>

            {{-- Date & Venue --}}
            <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 1.25rem;">
                <div class="form_group">
                    <label class="form_label" for="wedding_date">3. Ngày Tổ Chức Hôn Lễ *</label>
                    <input 
                        type="date" 
                        name="wedding_date" 
                        id="wedding_date" 
                        class="form_input" 
                        required 
                        value="{{ old('wedding_date', now()->addDays(30)->format('Y-m-d')) }}"
                    >
                </div>

                <div class="form_group">
                    <label class="form_label" for="venue_name">4. Sảnh Tiệc / Trung Tâm Tiệc Cưới</label>
                    <input 
                        type="text" 
                        name="venue_name" 
                        id="venue_name" 
                        class="form_input" 
                        placeholder="Ví dụ: GEM Center, Sảnh Grand Ballroom" 
                        value="{{ old('venue_name') }}"
                    >
                </div>
            </div>

            <div class="form_group">
                <label class="form_label" for="venue_address">Địa chỉ sảnh tiệc</label>
                <input 
                    type="text" 
                    name="venue_address" 
                    id="venue_address" 
                    class="form_input" 
                    placeholder="Ví dụ: Số 8 Nguyễn Bỉnh Khiêm, Phường Đa Kao, Quận 1, TP.HCM" 
                    value="{{ old('venue_address') }}"
                >
            </div>

            {{-- Studio Internal Notes --}}
            <div class="form_group">
                <label class="form_label" for="client_notes">5. Ghi Chú Nội Bộ Của Studio (Khách không nhìn thấy)</label>
                <textarea 
                    name="client_notes" 
                    id="client_notes" 
                    class="form_textarea" 
                    placeholder="Ví dụ: Gói chụp Pre-wedding Kim Cương, hợp đồng số HD-2026/08, cô dâu thích phong cách hoa linh lan trắng..."
                >{{ old('client_notes') }}</textarea>
                <span class="form_hint">Dùng để ghi nhớ hợp đồng, mã gói dịch vụ hoặc sở thích riêng của cặp đôi.</span>
            </div>

            {{-- Submit Actions --}}
            <div style="display: flex; gap: 1rem; align-items: center; margin-top: 2rem;">
                <button type="submit" class="mayhen_partner_btn mayhen_partner_btn--gold" style="padding: 0.85rem 1.75rem; font-size: 0.95rem;">
                    ✨ Tạo Thiệp & Chuyển Sang Studio
                </button>
                <a href="{{ route('partner.weddings.index') }}" class="mayhen_partner_btn mayhen_partner_btn--outline">
                    Hủy bỏ
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

