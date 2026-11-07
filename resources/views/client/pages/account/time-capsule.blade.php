@extends('client.pages.account.layout')

@php
    $groom = $wedding->groom_name ?? 'Chú Rể';
    $bride = $wedding->bride_name ?? 'Cô Dâu';
    $pageTitle = "Hộp Thời Gian Kỷ Niệm: {$groom} & {$bride}";
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/time-capsule.css') }}?v={{ file_exists(public_path('assets/client/css/pages/time-capsule.css')) ? filemtime(public_path('assets/client/css/pages/time-capsule.css')) : '1.0' }}">
@endpush

@section('account_breadcrumb')
    <a href="{{ route('account.invitations') }}" style="color: inherit; text-decoration: none;">Thiệp cưới</a>
    <span class="mayhen_account_breadcrumbs_sep">/</span>
    <span>Hộp thời gian</span>
@endsection

@section('account_content')
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 600; color: #1F1D1A; margin: 0;">
                Hộp Thời Gian Kỷ Niệm (Wedding Time Capsule)
            </h2>
            <p style="font-size: 13px; color: #7A726A; margin: 4px 0 0 0;">
                Niêm phong sáp những bức tâm thư thiêng liêng gửi tới tương lai (1 năm, 5 năm, 10 năm ngày cưới).
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <a href="{{ route('time_capsule.public', $invitation->slug) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                <span>Xem Trang Khách Gửi Thư</span>
            </a>
            <a href="#viet-tam-thu" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Viết Tâm Thư Mới</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mayhen_account_alert mayhen_account_alert_success" style="margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; background: #F2F8F5; border: 1px solid #D2E7DC; color: #1B5E3C; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mayhen_account_alert mayhen_account_alert_danger" style="margin-bottom: 24px; padding: 14px 18px; border-radius: 12px; background: #FDF4F4; border: 1px solid #F3D2D2; color: #9E2A2B; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- 3 Metric Cards -->
    <div class="mayhen_capsule_metrics">
        <div class="mayhen_capsule_metric_card">
            <div class="mayhen_capsule_metric_label">Tổng số bức thư</div>
            <div class="mayhen_capsule_metric_value">{{ $totalCount }}</div>
        </div>

        <div class="mayhen_capsule_metric_card">
            <div class="mayhen_capsule_metric_label">Đang niêm phong sáp</div>
            <div class="mayhen_capsule_metric_value" style="color: #9C7A4A;">{{ $sealedCount }}</div>
        </div>

        <div class="mayhen_capsule_metric_card">
            <div class="mayhen_capsule_metric_label">Đã mở khóa kỷ niệm</div>
            <div class="mayhen_capsule_metric_value" style="color: #2E7D32;">{{ $unlockedCount }}</div>
        </div>
    </div>

    <!-- Danh sách phong bì niêm phong -->
    <div style="margin-bottom: 36px;">
        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 600; color: #1F1D1A; margin: 0 0 16px 0;">
            Những Phong Bì Niêm Phong Trong Hộp Thời Gian
        </h3>

        @if($capsules->isEmpty())
            <div style="background: #FAF8F5; border: 1px dashed #D8CEBD; border-radius: 16px; padding: 48px 24px; text-align: center;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: #EFE8DD; color: #9C7A4A; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px;">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="8" width="18" height="13" rx="2"></rect>
                        <path d="M16 8V5a4 4 0 0 0-8 0v3"></path>
                    </svg>
                </div>
                <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 20px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                    Hộp Thời Gian Đang Chờ Bức Thư Đầu Tiên
                </h4>
                <p style="font-size: 13px; color: #6E665E; max-width: 460px; margin: 0 auto 20px;">
                    Hãy viết cho người bạn đời một bức tâm thư kỷ niệm 1 năm hoặc 5 năm ngày cưới, hệ thống sẽ niêm phong sáp và giữ bí mật tuyệt đối!
                </p>
                <a href="#viet-tam-thu" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                    Viết Bức Thư Đầu Tiên Ngay
                </a>
            </div>
        @else
            <div class="mayhen_capsule_grid">
                @foreach($capsules as $capsule)
                    <div class="mayhen_capsule_card">
                        <div>
                            <div class="mayhen_capsule_card_header">
                                <div>
                                    <h4 class="mayhen_capsule_title">{{ $capsule->title }}</h4>
                                    <div class="mayhen_capsule_meta">
                                        <span><strong>Người gửi:</strong> {{ $capsule->sender_name }}</span>
                                        <span><strong>Gửi tới:</strong> {{ $capsule->recipient_name }}</span>
                                        <span style="color: #9C7A4A; font-weight: 500;">{{ $capsule->milestoneLabel() }}</span>
                                    </div>
                                </div>
                                <div class="mayhen_capsule_seal mayhen_capsule_seal--{{ $capsule->seal_color }}" title="Con dấu sáp niêm phong">
                                    {{ mb_substr($capsule->sender_name, 0, 1, 'UTF-8') }}
                                </div>
                            </div>

                            @if($capsule->is_sealed)
                                <div class="mayhen_capsule_countdown">
                                    <span>Mở ngày: <strong>{{ $capsule->unlock_date->format('d/m/Y') }}</strong></span>
                                    @if($capsule->canBeUnlocked())
                                        <span style="color: #2E7D32; font-weight: 600;">Đã sẵn sàng mở!</span>
                                    @else
                                        <span class="mayhen_capsule_countdown_days">Còn {{ $capsule->daysRemaining() }} ngày</span>
                                    @endif
                                </div>

                                <div style="background: #FAF6F0; border-radius: 8px; padding: 12px 14px; font-size: 12px; color: #7A726A; display: flex; align-items: center; gap: 8px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #9C7A4A; flex-shrink: 0;"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                    <span>Nội dung được mã hóa niêm phong sáp. Chỉ được mở khi đến ngày kỷ niệm.</span>
                                </div>
                            @else
                                <div style="margin-top: 12px;">
                                    <span style="background: #E8F5E9; color: #2E7D32; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 12px; text-transform: uppercase;">
                                        ✓ Đã Mở Niêm Phong
                                    </span>
                                    <div class="mayhen_capsule_content_unsealed">
                                        “{{ $capsule->content }}”
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="mayhen_capsule_actions">
                            @if($capsule->is_sealed)
                                @if($capsule->canBeUnlocked())
                                    <form action="{{ route('account.time_capsule.unseal', [$invitation->id, $capsule->id]) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm">
                                            Bẻ Niêm Phong Sáp
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" disabled style="opacity: 0.6; cursor: not-allowed;" title="Chưa đến ngày mở khóa">
                                        Đang Niêm Phong
                                    </button>
                                @endif
                            @else
                                <span style="font-size: 12px; color: #7A726A;">Mở ngày: {{ $capsule->unlocked_at ? $capsule->unlocked_at->format('d/m/Y') : '' }}</span>
                            @endif

                            <form action="{{ route('account.time_capsule.destroy', [$invitation->id, $capsule->id]) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bức thư này khỏi Hộp Thời Gian?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" style="color: #9E2A2B; border-color: #F3D2D2;">
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Form Khởi Tạo Tâm Thư Mới -->
    <div id="viet-tam-thu" class="mayhen_capsule_form_card">
        <div style="border-bottom: 1px solid #EAE3D9; padding-bottom: 18px; margin-bottom: 24px;">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 600; color: #1F1D1A; margin: 0 0 6px 0;">
                Viết Tâm Thư Gửi Tương Lai
            </h3>
            <p style="font-size: 13px; color: #7A726A; margin: 0;">
                Gửi gắm những lời yêu thương, cảm xúc thiêng liêng lúc này cho người bạn đời mở ra trong những năm tháng tương lai.
            </p>
        </div>

        <form action="{{ route('account.time_capsule.store', $invitation->id) }}" method="POST">
            @csrf

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; margin-bottom: 18px;">
                <div>
                    <label class="mayhen_account_form_label">Người viết tâm thư *</label>
                    <select name="sender_type" class="mayhen_account_form_input" required onchange="document.getElementById('sender_name_input').value = this.options[this.selectedIndex].text;">
                        <option value="groom" {{ old('sender_type') === 'groom' ? 'selected' : '' }}>Chú Rể ({{ $groom }})</option>
                        <option value="bride" {{ old('sender_type') === 'bride' ? 'selected' : '' }}>Cô Dâu ({{ $bride }})</option>
                        <option value="couple" {{ old('sender_type') === 'couple' ? 'selected' : '' }}>Cả Hai Chúng Ta ({{ $groom }} & {{ $bride }})</option>
                    </select>
                </div>

                <div>
                    <label class="mayhen_account_form_label" for="sender_name_input">Tên hiển thị người gửi *</label>
                    <input type="text" name="sender_name" id="sender_name_input" value="{{ old('sender_name', $groom) }}" required class="mayhen_account_form_input">
                </div>

                <div>
                    <label class="mayhen_account_form_label" for="recipient_name">Gửi tới ai? *</label>
                    <input type="text" name="recipient_name" id="recipient_name" value="{{ old('recipient_name', 'Gửi Em Yêu (Vợ Của Anh)') }}" required class="mayhen_account_form_input" placeholder="vd: Gửi Vợ Yêu, Gửi Chồng Yêu, Gửi Chúng Ta...">
                </div>

                <div>
                    <label class="mayhen_account_form_label" for="milestone_years">Cột mốc kỷ niệm ngày mở *</label>
                    <select name="milestone_years" id="milestone_years" class="mayhen_account_form_input" required onchange="const dates = @json($suggestedDates); if(dates[this.value]) document.getElementById('unlock_date_input').value = dates[this.value];">
                        <option value="1">Kỷ niệm 1 năm (Đám cưới Giấy)</option>
                        <option value="3">Kỷ niệm 3 năm (Đám cưới Da)</option>
                        <option value="5">Kỷ niệm 5 năm (Đám cưới Gỗ)</option>
                        <option value="10">Kỷ niệm 10 năm (Đám cưới Thiếc)</option>
                        <option value="25">Kỷ niệm 25 năm (Đám cưới Bạc)</option>
                    </select>
                </div>

                <div>
                    <label class="mayhen_account_form_label" for="unlock_date_input">Ngày mở niêm phong khóa *</label>
                    <input type="date" name="unlock_date" id="unlock_date_input" value="{{ old('unlock_date', $suggestedDates[1] ?? date('Y-m-d', strtotime('+1 year'))) }}" required class="mayhen_account_form_input">
                </div>

                <div>
                    <label class="mayhen_account_form_label">Màu con dấu sáp niêm phong (Wax Seal) *</label>
                    <div class="mayhen_capsule_seal_picker">
                        <label class="mayhen_capsule_seal_radio">
                            <input type="radio" name="seal_color" value="ruby" checked>
                            <span class="mayhen_capsule_seal mayhen_capsule_seal--ruby" style="width: 28px; height: 28px; font-size: 12px;">M</span>
                            <span>Đỏ Ruby</span>
                        </label>
                        <label class="mayhen_capsule_seal_radio">
                            <input type="radio" name="seal_color" value="gold">
                            <span class="mayhen_capsule_seal mayhen_capsule_seal--gold" style="width: 28px; height: 28px; font-size: 12px;">M</span>
                            <span>Vàng Kim</span>
                        </label>
                        <label class="mayhen_capsule_seal_radio">
                            <input type="radio" name="seal_color" value="emerald">
                            <span class="mayhen_capsule_seal mayhen_capsule_seal--emerald" style="width: 28px; height: 28px; font-size: 12px;">M</span>
                            <span>Lục Bảo</span>
                        </label>
                        <label class="mayhen_capsule_seal_radio">
                            <input type="radio" name="seal_color" value="sapphire">
                            <span class="mayhen_capsule_seal mayhen_capsule_seal--sapphire" style="width: 28px; height: 28px; font-size: 12px;">M</span>
                            <span>Lam Ngọc</span>
                        </label>
                    </div>
                </div>
            </div>

            <div style="margin-bottom: 18px;">
                <label class="mayhen_account_form_label" for="capsule_title">Tiêu đề bức thư *</label>
                <input type="text" name="title" id="capsule_title" value="{{ old('title', 'Bức thư ngày chung đôi gửi em 1 năm sau') }}" required class="mayhen_account_form_input">
            </div>

            <div style="margin-bottom: 24px;">
                <label class="mayhen_account_form_label" for="capsule_content">Nội dung tâm thư niêm phong *</label>
                <textarea name="content" id="capsule_content" rows="6" required class="mayhen_account_form_input" style="line-height: 1.6;" placeholder="Viết những suy nghĩ chân thành nhất của bạn lúc này. Những lời dặn dò, lời hứa hẹn và niềm tin trao gửi cho tương lai...">{{ old('content') }}</textarea>
                <span style="font-size: 12px; color: #7A726A; margin-top: 4px; display: block;">
                    🔒 Nội dung này sẽ được bảo vệ tuyệt đối và không thể mở ra cho đến đúng ngày kỷ niệm đã chọn.
                </span>
            </div>

            <div>
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <span>Đóng Dấu Sáp & Niêm Phong Hộp Thời Gian</span>
                </button>
            </div>
        </form>
    </div>
@endsection

