@extends('client.layouts.master')

@php
    $groom = $wedding->groom_name ?? 'Chú Rể';
    $bride = $wedding->bride_name ?? 'Cô Dâu';
    $pageTitle = "Hộp Thời Gian Cưới: {$groom} & {$bride} | " . config('app.name');
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/time-capsule.css') }}?v={{ file_exists(public_path('assets/client/css/pages/time-capsule.css')) ? filemtime(public_path('assets/client/css/pages/time-capsule.css')) : '1.0' }}">
@endpush

@section('content')
<div class="mayhen_capsule_container">
    <!-- Public Hero Banner -->
    <div class="mayhen_capsule_public_hero">
        <div style="font-size: 12px; letter-spacing: 0.2em; text-transform: uppercase; color: #9C7A4A; font-weight: 600; margin-bottom: 6px;">
            WEDDING TIME CAPSULE
        </div>
        <h1 class="mayhen_capsule_public_title">
            Hộp Thời Gian: {{ $groom }} & {{ $bride }}
        </h1>
        <p class="mayhen_capsule_public_subtitle">
            Nơi lưu giữ những bức tâm thư và lời chúc phúc được niêm phong sáp bí mật. Những phong bì này sẽ chỉ được mở vào các dịp kỷ niệm 1 năm, 3 năm, 5 năm ngày cưới của đôi uyên ương.
        </p>

        @if($isOwner)
            <div style="margin-top: 18px;">
                <a href="{{ route('account.time_capsule.index', $invitation->id) }}" class="mayhen_client_button mayhen_client_button--primary" style="font-size: 13px; padding: 8px 18px; border-radius: 20px;">
                    Vào Không Gian Quản Trị Hộp Thời Gian
                </a>
            </div>
        @endif
    </div>

    @if(session('success'))
        <div class="mayhen_account_alert mayhen_account_alert_success" style="margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; background: #F2F8F5; border: 1px solid #D2E7DC; color: #1B5E3C; font-size: 14px;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Danh Sách Phong Bì Niêm Phong Hiện Tại -->
    <div style="margin-bottom: 40px;">
        <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 24px; font-weight: 600; color: #1F1D1A; margin-bottom: 18px; text-align: center;">
            Những Phong Bì Đang Được Niêm Phong Sáp
        </h2>

        @if(empty($capsules) || count($capsules) === 0)
            <div style="background: #FAF8F5; border: 1px dashed #D8CEBD; border-radius: 16px; padding: 40px 20px; text-align: center; color: #7A726A;">
                Chưa có phong bì nào trong Hộp Thời Gian. Hãy là người đầu tiên gửi lời chúc phúc niêm phong cho cặp đôi!
            </div>
        @else
            <div class="mayhen_capsule_grid">
                @foreach($capsules as $c)
                    <div class="mayhen_capsule_card">
                        <div>
                            <div class="mayhen_capsule_card_header">
                                <div>
                                    <h3 class="mayhen_capsule_title">{{ $c['title'] }}</h3>
                                    <div class="mayhen_capsule_meta">
                                        <span><strong>Người gửi:</strong> {{ $c['sender_name'] }}</span>
                                        <span><strong>Gửi tới:</strong> {{ $c['recipient_name'] }}</span>
                                        <span style="color: #9C7A4A; font-weight: 500;">{{ $c['milestone_label'] }}</span>
                                    </div>
                                </div>
                                <div class="mayhen_capsule_seal mayhen_capsule_seal--{{ $c['seal_color'] }}" title="Con dấu sáp niêm phong">
                                    {{ mb_substr($c['sender_name'], 0, 1, 'UTF-8') }}
                                </div>
                            </div>

                            @if($c['is_sealed'])
                                <div class="mayhen_capsule_countdown">
                                    <span>Mở ngày: <strong>{{ $c['unlock_date'] }}</strong></span>
                                    <span class="mayhen_capsule_countdown_days">Còn {{ $c['days_remaining'] }} ngày</span>
                                </div>

                                <div style="background: #FAF6F0; border-radius: 8px; padding: 12px; font-size: 12px; color: #7A726A; display: flex; align-items: center; gap: 8px;">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #9C7A4A; flex-shrink: 0;"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                    <span>Niêm phong sáp bí mật. Đôi uyên ương sẽ mở đọc vào ngày kỷ niệm!</span>
                                </div>
                            @else
                                <div class="mayhen_capsule_content_unsealed">
                                    “{{ $c['content'] }}”
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Form Quan Khách Gửi Tâm Thư Niêm Phong -->
    <div class="mayhen_capsule_form_card" style="max-width: 760px; margin: 0 auto;">
        <div style="border-bottom: 1px solid #EAE3D9; padding-bottom: 16px; margin-bottom: 20px; text-align: center;">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 600; color: #1F1D1A; margin: 0 0 6px 0;">
                Gửi Lời Chúc Niêm Phong Tới Tương Lai
            </h3>
            <p style="font-size: 13px; color: #7A726A; margin: 0;">
                Viết một bức thư đặc biệt cho đôi uyên ương mở ra sau 1 năm, 3 năm hoặc 5 năm nữa.
            </p>
        </div>

        <form action="{{ route('time_capsule.guest.store', $invitation->slug) }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="mayhen_account_form_label" for="guest_sender_name">Họ và tên của bạn *</label>
                    <input type="text" name="sender_name" id="guest_sender_name" required class="mayhen_account_form_input" placeholder="Ví dụ: Hoàng Nam (Bạn thân chú rể)">
                </div>

                <div>
                    <label class="mayhen_account_form_label" for="guest_recipient_name">Gửi tới ai? *</label>
                    <input type="text" name="recipient_name" id="guest_recipient_name" value="Gửi Đôi Uyên Ương ({{ $groom }} & {{ $bride }})" required class="mayhen_account_form_input">
                </div>

                <div>
                    <label class="mayhen_account_form_label" for="guest_milestone">Mốc năm kỷ niệm mở thư *</label>
                    <select name="milestone_years" id="guest_milestone" class="mayhen_account_form_input" required>
                        <option value="1">Kỷ niệm 1 năm ngày cưới (Đám cưới Giấy)</option>
                        <option value="3">Kỷ niệm 3 năm ngày cưới (Đám cưới Da)</option>
                        <option value="5">Kỷ niệm 5 năm ngày cưới (Đám cưới Gỗ)</option>
                        <option value="10">Kỷ niệm 10 năm ngày cưới (Đám cưới Thiếc)</option>
                    </select>
                </div>

                <div>
                    <label class="mayhen_account_form_label">Màu con dấu sáp niêm phong *</label>
                    <div class="mayhen_capsule_seal_picker">
                        <label class="mayhen_capsule_seal_radio">
                            <input type="radio" name="seal_color" value="ruby" checked>
                            <span class="mayhen_capsule_seal mayhen_capsule_seal--ruby" style="width: 26px; height: 26px; font-size: 11px;">M</span>
                            <span>Đỏ Ruby</span>
                        </label>
                        <label class="mayhen_capsule_seal_radio">
                            <input type="radio" name="seal_color" value="gold">
                            <span class="mayhen_capsule_seal mayhen_capsule_seal--gold" style="width: 26px; height: 26px; font-size: 11px;">M</span>
                            <span>Vàng Kim</span>
                        </label>
                        <label class="mayhen_capsule_seal_radio">
                            <input type="radio" name="seal_color" value="emerald">
                            <span class="mayhen_capsule_seal mayhen_capsule_seal--emerald" style="width: 26px; height: 26px; font-size: 11px;">M</span>
                            <span>Lục Bảo</span>
                        </label>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label class="mayhen_account_form_label" for="guest_title">Tiêu đề bức thư *</label>
                <input type="text" name="title" id="guest_title" required class="mayhen_account_form_input" placeholder="Ví dụ: Vài lời dặn dò cho hai bạn sau 1 năm chung sống">
            </div>

            <div style="margin-bottom: 20px;">
                <label class="mayhen_account_form_label" for="guest_content">Nội dung bức thư niêm phong *</label>
                <textarea name="content" id="guest_content" rows="5" required class="mayhen_account_form_input" style="line-height: 1.6;" placeholder="Gửi gắm những lời chúc, kỷ niệm đáng nhớ hoặc câu hỏi gửi tới tương lai của hai bạn..."></textarea>
            </div>

            <div style="text-align: center;">
                <button type="submit" class="mayhen_client_button mayhen_client_button--primary" style="padding: 12px 28px; border-radius: 30px; font-size: 14px;">
                    🔒 Đóng Dấu Sáp & Gửi Vào Hộp Thời Gian
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

