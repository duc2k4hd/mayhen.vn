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

        {{-- Post-Wedding Lifecycle & RSVP Freezing Form Card (Section 36 PRD V3) --}}
        <div class="mayhen_vault_form_card" style="margin-top: 28px;">
            <div style="border-bottom: 1px solid #EAE3D9; padding-bottom: 16px; margin-bottom: 20px;">
                <span class="settings_badge" style="background: #EFE8DD; color: #7A726A;">VÒNG ĐỜI HẬU SỰ KIỆN</span>
                <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 600; color: #1F1D1A; margin: 6px 0 2px 0;">
                    Chuyển Đổi Hậu Sự Kiện & Đóng Băng Khóa RSVP
                </h2>
                <p style="font-size: 13px; color: #7A726A; margin: 0;">
                    Khi ngày cưới đã diễn ra, hãy kích hoạt các chế độ này để khép lại việc nhận khách và chuyển hướng người xem sang lưu giữ kỷ niệm.
                </p>
            </div>

            <form action="{{ route('memory.post_wedding_lifecycle', $invitation->id) }}" method="POST">
                @csrf

                {{-- Freeze RSVP Toggle --}}
                <div class="form_toggle_row" style="margin-bottom: 20px;">
                    <div>
                        <strong class="toggle_title">Đóng Băng Xác Nhận Tham Dự (Khóa RSVP)</strong>
                        <p class="toggle_desc">
                            Khóa toàn bộ form RSVP trên thiệp cưới. Quan khách khi truy cập sẽ thấy thông báo lịch sự rằng hôn lễ đã diễn ra thành công tốt đẹp và không nhận thêm phản hồi.
                        </p>
                    </div>
                    <label class="mayhen_switch">
                        <input type="checkbox" name="freeze_rsvp" value="1" {{ !empty($postWeddingConfig['freeze_rsvp']) ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </div>

                {{-- Redirect to Memory Vault Toggle --}}
                <div class="form_toggle_row" style="margin-bottom: 24px;">
                    <div>
                        <strong class="toggle_title">Tự Động Chuyển Hướng Sang Bảo Tàng Kỷ Niệm</strong>
                        <p class="toggle_desc">
                            Khi quan khách truy cập link thiệp cưới <code>/thiep/{{ $invitation->slug }}</code>, hệ thống sẽ tự động điều hướng sang trang Bảo Tàng Kỷ Niệm Số để thưởng thức ảnh cưới và lời chúc mừng thay vì thiệp mời sự kiện cũ.
                        </p>
                    </div>
                    <label class="mayhen_switch">
                        <input type="checkbox" name="redirect_to_vault" value="1" {{ !empty($postWeddingConfig['redirect_to_vault']) ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </div>

                <div class="form_submit_row">
                    <button type="submit" class="mayhen_vault_btn mayhen_vault_btn--gold">
                        Lưu Cài Đặt Hậu Sự Kiện
                    </button>
                </div>
            </form>
        </div>

        {{-- Data Portability & Privacy Data Purge Card (Section 41.3 PRD V3 & Law 91/2025/QH15) --}}
        <div class="mayhen_vault_form_card" style="margin-top: 28px; background: #FAF8F5; border: 1px solid #EAE3D9;">
            <div style="border-bottom: 1px solid #EAE3D9; padding-bottom: 16px; margin-bottom: 20px;">
                <span class="settings_badge" style="background: #E8F5E9; color: #2E7D32;">AN TOÀN DỮ LIỆU & QUYỀN RIÊNG TƯ</span>
                <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 600; color: #1F1D1A; margin: 6px 0 2px 0;">
                    Xuất Trọn Bộ Dữ Liệu & Quyền Được Lãng Quên (Privacy Retention)
                </h2>
                <p style="font-size: 13px; color: #7A726A; margin: 0;">
                    Tải về toàn bộ kỷ niệm hôn lễ để lưu trữ độc lập trọn đời hoặc làm sạch dữ liệu cá nhân theo Luật Bảo Vệ Dữ Liệu 91/2025/QH15.
                </p>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap; margin-bottom: 24px;">
                <div style="max-width: 520px;">
                    <strong style="font-size: 15px; color: #1F1D1A; display: block; margin-bottom: 4px;">
                        📦 Gói Lưu Trữ Kỷ Niệm Ngoại Tuyến Toàn Diện (ZIP Archive)
                    </strong>
                    <p style="font-size: 13px; color: #7A726A; margin: 0; line-height: 1.5;">
                        Bao gồm trang thiệp offline không cần mạng, file CSV khách mời & RSVP, sổ mừng cưới & tiền mừng, danh bạ nhà cung cấp cưới, và file JSON chứng thực toàn vẹn số.
                    </p>
                </div>
                <a href="{{ route('memory.offline_archive', $invitation->id) }}" class="mayhen_vault_btn mayhen_vault_btn--primary" style="white-space: nowrap;">
                    Tải Về Trọn Bộ Dữ Liệu (ZIP)
                </a>
            </div>

            <div style="background: #FFF8E1; border: 1px solid #FFE082; border-radius: 12px; padding: 18px 20px;">
                <div style="display: flex; align-items: flex-start; gap: 12px; margin-bottom: 14px;">
                    <span style="font-size: 20px;">🛡️</span>
                    <div>
                        <strong style="font-size: 14px; color: #B78103; display: block;">
                            Quy Chuẩn Bảo Vệ Quyền Riêng Tư (Right to be Forgotten)
                        </strong>
                        <p style="font-size: 12px; color: #6D4C41; margin: 4px 0 0 0; line-height: 1.5;">
                            Sau khi hôn lễ kết thúc tốt đẹp, bạn có thể chủ động xóa vĩnh viễn số điện thoại, mã token liên kết và nhật ký thiết bị của <strong>{{ $guestsCount }} khách mời</strong>. Hệ thống vẫn lưu giữ tên quan khách và tổng số tiền mừng phục vụ kỷ niệm.
                        </p>
                    </div>
                </div>

                <form action="{{ route('memory.purge_guest_pii', $invitation->id) }}" method="POST" onsubmit="return confirm('CẢNH BÁO AN TOÀN:\nBạn có chắc chắn muốn làm sạch toàn bộ số điện thoại và token định danh của khách mời?\nThao tác này không thể hoàn tác.');">
                    @csrf
                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <input type="password" name="current_password" required placeholder="Nhập mật khẩu tài khoản để xác nhận..." class="form_input" style="height: 38px; font-size: 13px; max-width: 300px;">
                        <button type="submit" class="mayhen_vault_btn" style="background: #C62828; color: #FFFFFF; font-size: 13px; height: 38px; padding: 0 16px; border: none; white-space: nowrap;">
                            Xóa Dữ Liệu Nhạy Cảm Khách Mời
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

