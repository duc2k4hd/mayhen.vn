@extends('client.layouts.master')

@section('title', 'Cấu Hình Két Kỷ Niệm Số | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/memory-vault.css') }}">
@endpush

@section('content')
<div class="mayhen_vault_settings_wrap">
    <div class="mayhen_vault_settings_container">
        {{-- Breadcrumbs --}}
        <div class="mayhen_vault_breadcrumbs">
            <a href="{{ route('account.dashboard') }}">Tài khoản</a>
            <span>/</span>
            <a href="{{ route('account.invitations') }}">Thiệp cưới</a>
            <span>/</span>
            <span>Két Kỷ Niệm Số</span>
        </div>

        @if(session('success'))
            <div class="mayhen_vault_alert mayhen_vault_alert--success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Header Card --}}
        <div class="mayhen_vault_settings_header">
            <div>
                <span class="settings_badge">PREMIUM KEEPSAKE</span>
                <h1 class="settings_title">Két Lưu Trữ Kỷ Niệm Số (Digital Memory Vault)</h1>
                <p class="settings_subtitle">
                    Hôn lễ: <strong>{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</strong> &bull;
                    Biến thiệp cưới thành bảo tàng kỷ niệm vĩnh cửu lưu giữ lời chúc và khoảnh khắc đẹp nhất.
                </p>
            </div>
            <div class="settings_actions">
                <a href="{{ route('memory.vault', $invitation->slug) }}" target="_blank" class="mayhen_vault_btn mayhen_vault_btn--outline">
                    👁️ Mở Trang Kỷ Niệm
                </a>
                <a href="{{ route('memory.keepsake_book', $invitation->id) }}" target="_blank" class="mayhen_vault_btn mayhen_vault_btn--gold">
                    📖 In Sách Kỷ Yếu
                </a>
            </div>
        </div>

        {{-- Configuration Form --}}
        <div class="mayhen_vault_form_card">
            <form action="{{ route('memory.updateSettings', $invitation->id) }}" method="POST">
                @csrf

                {{-- Toggle Enable Vault --}}
                <div class="form_toggle_row">
                    <div>
                        <strong class="toggle_title">Kích hoạt Chế Độ Bảo Tàng Kỷ Niệm</strong>
                        <p class="toggle_desc">
                            Khi bật, khách truy cập link <code>/ky-niem/{{ $invitation->slug }}</code> sẽ thấy trang kỷ niệm lãng mạn kèm bộ đếm ngày hạnh phúc và thư tri ân.
                        </p>
                    </div>
                    <label class="mayhen_switch">
                        <input type="checkbox" name="enabled" value="1" {{ !empty($vaultConfig['enabled']) ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </div>

                {{-- Thank-You Letter Editor --}}
                <div class="form_group">
                    <label class="form_label" for="thank_you_letter">
                        Tâm Thư Tri Ân & Lời Cảm Ơn Của Đôi Uyên Ương
                    </label>
                    <p class="form_hint">
                        Bức thư này sẽ xuất hiện trang trọng ở phần đầu Trang Kỷ Niệm và trang mở đầu của Cuốn Sách Kỷ Yếu Lưu Bút in ấn.
                    </p>
                    <textarea 
                        name="thank_you_letter" 
                        id="thank_you_letter" 
                        rows="6" 
                        class="form_textarea"
                        placeholder="Nhập bức thư cảm ơn gửi tới gia đình, bạn bè..."
                    >{{ old('thank_you_letter', $vaultConfig['thank_you_letter'] ?? '') }}</textarea>
                </div>

                {{-- Custom Cover Image --}}
                <div class="form_group">
                    <label class="form_label" for="cover_image">
                        Ảnh Bìa Kỷ Niệm (Tùy chọn)
                    </label>
                    <p class="form_hint">
                        Đường dẫn hình ảnh khoảnh khắc đẹp nhất trong ngày cưới để đặt làm ảnh bìa kỷ niệm.
                    </p>
                    <input 
                        type="text" 
                        name="cover_image" 
                        id="cover_image" 
                        class="form_input" 
                        placeholder="https://..."
                        value="{{ old('cover_image', $vaultConfig['cover_image'] ?? '') }}"
                    >
                </div>

                <div class="form_submit_row">
                    <button type="submit" class="mayhen_vault_btn mayhen_vault_btn--primary">
                        Lưu Cấu Hình Kỷ Niệm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

