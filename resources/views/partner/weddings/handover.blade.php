@extends('partner.layouts.master')

@section('title', 'Bàn Giao Thiệp Cưới: ' . $wedding->groom_name . ' & ' . $wedding->bride_name . ' | ' . config('app.name'))

@section('content')
<div class="handover_container">
    {{-- Breadcrumbs --}}
    <div class="mayhen_partner_breadcrumbs" style="justify-content: center;">
        <a href="{{ route('partner.dashboard') }}">Cổng Đối Tác</a>
        <span>/</span>
        <a href="{{ route('partner.weddings.index') }}">Thiệp Khách Hàng</a>
        <span>/</span>
        <span>Bàn Giao Thiệp</span>
    </div>

    @if(session('success'))
        <div class="mayhen_partner_alert mayhen_partner_alert--success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mayhen_partner_alert mayhen_partner_alert--danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="handover_card">
        <div class="handover_icon">🔑</div>
        <h1 class="handover_title">Bàn Giao Quyền Quản Lý Thiệp Cưới</h1>
        <p class="handover_desc">
            Cặp đôi: <strong>{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</strong> &bull;
            Gửi mã này cho cô dâu chú rể để họ liên kết thiệp vào tài khoản cá nhân, tự kiểm tra danh sách khách mời và RSVP.
        </p>

        @if($isAlreadyClaimed && ! $wedding->handover_code)
            <div class="mayhen_partner_alert mayhen_partner_alert--info" style="margin-bottom: 1.5rem; text-align: left;">
                <strong style="display: block; font-size: 1rem; color: #1B5E20; margin-bottom: 0.25rem;">
                    ✓ Thiệp cưới đã được bàn giao thành công!
                </strong>
                <span style="font-size: 0.85rem; color: #2E7D32;">
                    Cặp đôi đã liên kết thiệp vào tài khoản cá nhân của họ. Mã bàn giao đã tự động đóng lại để đảm bảo tính an toàn và bảo mật tài khoản.
                </span>
            </div>
        @elseif($wedding->handover_code)
            @if($isExpired)
                <div class="mayhen_partner_alert mayhen_partner_alert--warning" style="margin-bottom: 1.5rem; background: #FFF3E0; border: 1px solid #FFE082; color: #E65100; text-align: left;">
                    <strong>⚠️ Mã bàn giao này đã hết hạn hiệu lực (30 ngày).</strong>
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem;">Cô dâu chú rể sẽ không thể dùng mã này để nhận thiệp. Vui lòng bấm <em>"🔄 Đổi Mã Khác"</em> phía dưới để cấp mã mới có hiệu lực 30 ngày tiếp theo.</p>
                </div>
            @endif

            {{-- Big Handover Code Box --}}
            <div class="handover_code_wrap" style="{{ $isExpired ? 'opacity: 0.6; filter: grayscale(0.5);' : '' }}">
                <span style="font-size: 0.75rem; font-weight: 700; color: #8C6D3F; letter-spacing: 0.1em; text-transform: uppercase;">
                    MÃ BÀN GIAO 6 KÝ TỰ @if($expiresAt) &bull; HẠN DÙNG: {{ $expiresAt->format('d/m/Y') }} @endif
                </span>
                <span class="handover_big_code" id="handover_val">{{ $wedding->handover_code }}</span>
                @if(! $isExpired)
                    <button type="button" class="mayhen_partner_btn mayhen_partner_btn--primary" data-copy-target="#handover_val">
                        📋 Sao Chép Mã
                    </button>
                @endif
            </div>

            {{-- Step Instructions --}}
            <div class="handover_steps">
                <strong style="display: block; color: #1F1D1A; margin-bottom: 0.5rem; font-size: 0.95rem;">
                    📌 Hướng dẫn dành cho cô dâu & chú rể:
                </strong>
                <ol>
                    <li>Truy cập website Mây Hẹn: <a href="{{ route('home') }}" target="_blank" style="color: #8C6D3F;">{{ config('app.url') }}</a></li>
                    <li>Đăng nhập (hoặc đăng ký) tài khoản cá nhân của cặp đôi.</li>
                    <li>Vào mục <strong>Quản Lý Thiệp Cưới</strong>, bấm nút <em>"Nhập Mã Bàn Giao Từ Studio"</em>.</li>
                    <li>Điền mã <strong>{{ $wedding->handover_code }}</strong> để nhận toàn quyền quản lý thiệp!</li>
                </ol>
            </div>

            {{-- Pre-formatted Message Template for Zalo / Messenger --}}
            <div style="text-align: left; margin-bottom: 1.5rem;">
                <label class="form_label" for="zalo_msg_box">Mẫu tin nhắn gửi Zalo / Messenger:</label>
                <textarea id="zalo_msg_box" class="form_textarea" readonly style="font-size: 0.85rem; line-height: 1.5; background: #FAF8F5;">Chào hai bạn {{ $wedding->groom_name }} & {{ $wedding->bride_name }},
Studio {{ $partner->business_name }} đã hoàn thiện mẫu thiệp cưới điện tử của hai bạn trên nền tảng Mây Hẹn!
Để nhận quyền quản trị thiệp, mời bạn đăng nhập vào mayhen.vn và nhập Mã Bàn Giao sau:
👉 Mã nhận thiệp: {{ $wedding->handover_code }}
Link xem trước thiệp cưới: {{ $invitation ? route('invitation.show', $invitation->slug) : url('/') }}
Chúc hai bạn có một mùa cưới ngập tràn hạnh phúc!</textarea>
                <div style="margin-top: 0.5rem; text-align: right;">
                    <button type="button" class="mayhen_partner_btn mayhen_partner_btn--sm mayhen_partner_btn--outline" data-copy-target="#zalo_msg_box">
                        📋 Sao Chép Tin Nhắn
                    </button>
                </div>
            </div>
        @else
            <div class="mayhen_partner_alert mayhen_partner_alert--info" style="margin-bottom: 1.5rem; text-align: left;">
                <p style="margin: 0;">Hiện tại chưa có mã bàn giao nào đang mở cho đám cưới này. Bấm nút <em>"🔄 Cấp Mã Bàn Giao Mới"</em> phía dưới để gửi quyền quản lý cho khách hàng.</p>
            </div>
        @endif

        {{-- Actions --}}
        <div style="border-top: 1px solid #EBE5DC; padding-top: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
            <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <form action="{{ route('partner.weddings.regenerate', $wedding->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn cấp mã bàn giao mới? Mã cũ (nếu có) sẽ mất hiệu lực.');">
                    @csrf
                    <button type="submit" class="mayhen_partner_btn mayhen_partner_btn--sm mayhen_partner_btn--outline" style="color: #8C6D3F;">
                        🔄 {{ $wedding->handover_code ? 'Đổi Mã Khác' : 'Cấp Mã Bàn Giao Mới' }}
                    </button>
                </form>

                @if($wedding->handover_code)
                    <form action="{{ route('partner.weddings.revoke', $wedding->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn thu hồi mã bàn giao này? Khách hàng sẽ không thể dùng mã này để nhận thiệp.');">
                        @csrf
                        <button type="submit" class="mayhen_partner_btn mayhen_partner_btn--sm mayhen_partner_btn--outline" style="color: #C62828;">
                            🚫 Thu Hồi Mã
                        </button>
                    </form>
                @endif
            </div>

            <a href="{{ route('partner.weddings.index') }}" class="mayhen_partner_btn mayhen_partner_btn--sm mayhen_partner_btn--primary">
                ← Về Danh Sách Thiệp
            </a>
        </div>
    </div>
</div>
@endsection

