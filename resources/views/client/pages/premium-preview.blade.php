@extends('client.layouts.master')

@section('title', 'Xem Thử Tính Năng Pro: ' . $meta['title'] . ' | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/premium-preview.css') }}">
@endpush

@section('content')
<div class="mayhen_preview_sandbox_wrap">
    <div class="mayhen_preview_sandbox_container">
        {{-- Floating Sandbox Banner --}}
        <div class="sandbox_notice_banner">
            <div class="sandbox_notice_info">
                <span class="sandbox_badge_icon">🧪</span>
                <div>
                    <h3 class="sandbox_notice_title">Chế Độ Xem Thử Tính Năng Cao Cấp (Sandbox Preview)</h3>
                    <p class="sandbox_notice_desc">
                        Hai bạn đang trải nghiệm thử giao diện thực tế bằng dữ liệu mô phỏng. Hãy nâng cấp gói để áp dụng chính thức cho đám cưới thật.
                    </p>
                </div>
            </div>
            <a href="{{ route('checkout.show', $proPlan->slug ?? 'pro') }}" class="sandbox_upgrade_btn">
                ✨ Nâng Cấp Gói Pro Ngay &rarr;
            </a>
        </div>

        {{-- Feature Header --}}
        <header class="sandbox_feature_header">
            <span class="sandbox_feature_badge">PREMIUM PRO FEATURE</span>
            <h1 class="sandbox_feature_title">{{ $meta['title'] }}</h1>
            <p class="sandbox_feature_desc">{{ $meta['subtitle'] }}</p>
        </header>

        {{-- Demo Sandbox Surface --}}
        <div class="sandbox_demo_surface">
            @if($feature === 'seating')
                <div class="sandbox_demo_head">
                    <h3 class="sandbox_demo_title">Sơ Đồ Bố Trí Bàn Tiệc &amp; Sức Chứa Sảnh Cưới</h3>
                    <span style="font-size: 0.82rem; color: #8C8479;">Mô phỏng 4 bàn tiệc tiệc cưới</span>
                </div>

                <div class="sandbox_tables_grid">
                    @foreach($sandboxData['tables'] as $tbl)
                        <div class="sandbox_table_card sandbox_table_card--{{ $tbl['status'] }}">
                            <strong class="sandbox_table_name">{{ $tbl['name'] }}</strong>
                            <span style="font-size: 0.78rem; color: #6E685F;">Kiểu bàn: {{ $tbl['shape'] === 'round' ? 'Bàn tròn' : 'Bàn dài' }}</span>
                            <div style="margin-top: 0.5rem; font-size: 1.15rem; font-weight: 700; color: #1F1D1A;">
                                {{ $tbl['assigned'] }}/{{ $tbl['capacity'] }} ghế
                            </div>
                            @if($tbl['status'] === 'overfilled')
                                <span class="sandbox_table_badge" style="background: #FFCDD2; color: #B71C1C;">⚠️ Quá tải +2 ghế</span>
                            @elseif($tbl['status'] === 'underfilled')
                                <span class="sandbox_table_badge" style="background: #FFE082; color: #E65100;">📉 Quá thưa (50%)</span>
                            @else
                                <span class="sandbox_table_badge" style="background: #C8E6C9; color: #1B5E20;">✓ Sức chứa chuẩn</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @elseif($feature === 'checkin')
                <div class="sandbox_demo_head">
                    <h3 class="sandbox_demo_title">Nhật Ký Quét QR Điểm Danh Lễ Tân Thời Gian Thực</h3>
                    <span style="font-size: 0.82rem; color: #2E7D32; font-weight: 700;">Đã đón: {{ $sandboxData['stats']['checked_in_count'] }}/{{ $sandboxData['stats']['total_expected'] }} khách</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach($sandboxData['recent_checkins'] as $rc)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1rem; background: #FAF8F5; border: 1px solid #EFEAE3; border-radius: 10px;">
                            <div>
                                <strong style="font-size: 0.95rem; color: #1F1D1A; display: block;">{{ $rc['name'] }}</strong>
                                <span style="font-size: 0.78rem; color: #6E685F;">Vào {{ $rc['table'] }} &bull; Đi kèm: {{ $rc['headcount'] }} người &bull; {{ $rc['note'] }}</span>
                            </div>
                            <span style="font-size: 0.8rem; font-weight: 700; color: #9C7A4A; background: #FFFDF9; padding: 0.25rem 0.65rem; border-radius: 999px; border: 1px solid #E5DCCD;">
                                {{ $rc['time'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @elseif($feature === 'live-wall')
                <div class="sandbox_demo_head">
                    <h3 class="sandbox_demo_title">Giao Diện Màn Hình Chiếu Trực Tiếp (Live Wall LED)</h3>
                    <span style="font-size: 0.82rem; color: #8C8479;">Khách quét mã tại bàn tải ảnh lên tức thời</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                    @foreach($sandboxData['photos'] as $p)
                        <div style="background: #1F1D1A; color: #FFF; border-radius: 14px; overflow: hidden; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <p style="font-size: 1.1rem; font-style: italic; font-family: var(--font-heading, serif); margin: 0 0 1rem; line-height: 1.45;">
                                "{{ $p['caption'] }}"
                            </p>
                            <span style="font-size: 0.82rem; color: #C5A880; font-weight: 600;">Gửi bởi: {{ $p['author'] }}</span>
                        </div>
                    @endforeach
                </div>
            @elseif($feature === 'memory-vault')
                <div class="sandbox_demo_head">
                    <h3 class="sandbox_demo_title">Gói Kỷ Niệm Ngoại Tuyến Độc Lập (Portable Wedding Archive)</h3>
                    <span style="font-size: 0.82rem; color: #2E7D32; font-weight: 700;">Thời hạn bảo quản: {{ $sandboxData['stats']['retention'] }}</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach($sandboxData['archive_files'] as $af)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1rem; background: #FAF8F5; border: 1px solid #EFEAE3; border-radius: 10px;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <span style="font-size: 1.5rem;">📄</span>
                                <div>
                                    <strong style="font-size: 0.92rem; color: #1F1D1A; display: block; font-family: monospace;">{{ $af['name'] }}</strong>
                                    <span style="font-size: 0.78rem; color: #6E685F;">{{ $af['desc'] }}</span>
                                </div>
                            </div>
                            <span style="font-size: 0.78rem; color: #8C8479; font-weight: 600;">{{ $af['size'] }}</span>
                        </div>
                    @endforeach
                </div>
            @elseif($feature === 'welcome-kiosk')
                <div class="sandbox_demo_head">
                    <h3 class="sandbox_demo_title">Màn Hình Chào Mừng Sảnh Cưới &amp; Kiosk Lễ Tân (Venue Kiosk)</h3>
                    <span style="font-size: 0.82rem; color: #2E7D32; font-weight: 700;">Âm thanh: {{ $sandboxData['stats']['audio_effects'] }} &bull; {{ $sandboxData['stats']['mode'] }}</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                    @foreach($sandboxData['recent_welcomes'] as $rw)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; background: #FFFDF9; border: 1px solid #E8DECB; border-radius: 12px; box-shadow: 0 4px 14px rgba(184, 147, 93, 0.08);">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <span style="font-size: 1.8rem;">🥂</span>
                                <div>
                                    <div style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: #9C7A4A; font-weight: 600;">{{ $rw['salutation'] }}</div>
                                    <strong style="font-size: 1.05rem; color: #1F1D1A; display: block;">{{ $rw['name'] }}</strong>
                                    <span style="font-size: 0.82rem; color: #6E685F;">Được sắp xếp tại: <strong style="color: #9C7A4A;">{{ $rw['table'] }}</strong></span>
                                </div>
                            </div>
                            <span style="font-size: 0.82rem; font-weight: 700; color: #9C7A4A; background: #FAF8F5; padding: 0.35rem 0.85rem; border-radius: 999px; border: 1px solid #E5DCCD;">
                                {{ $rw['time'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @elseif($feature === 'signature-mosaic')
                <div class="sandbox_demo_head">
                    <h3 class="sandbox_demo_title">Bức Tranh Ký Tên Vàng Quan Khách (Signature Mosaic Wall)</h3>
                    <span style="font-size: 0.82rem; color: #9C7A4A; font-weight: 700;">Chủ đề: {{ $sandboxData['stats']['theme'] }} &bull; Tổng: {{ $sandboxData['stats']['total_signatures'] }} chữ ký</span>
                </div>

                <div style="background: #FAF8F5; border: 1px solid #E8DECB; border-radius: 14px; padding: 1.25rem; margin-bottom: 1rem; text-align: center;">
                    <div style="font-size: 0.75rem; letter-spacing: 0.1em; text-transform: uppercase; color: #9C7A4A; font-weight: 600; margin-bottom: 0.35rem;">Bút Tích Mở Đầu Hạnh Phúc</div>
                    <div style="font-size: 1.3rem; font-weight: 700; color: #1F1D1A; font-family: var(--font-heading, serif);">
                        {{ $sandboxData['couple']['groom'] }} &amp; {{ $sandboxData['couple']['bride'] }}
                    </div>
                    <p style="font-size: 0.88rem; font-style: italic; color: #6E685F; margin: 0.5rem auto 0; max-width: 600px;">
                        "{{ $sandboxData['couple']['quote'] }}"
                    </p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                    @foreach($sandboxData['guest_signatures'] as $gs)
                        <div style="background: #FFFFFF; border: 1px solid {{ $gs['is_pinned'] ? '#C5A880' : '#EFEAE3' }}; border-radius: 12px; padding: 1rem; position: relative;">
                            @if($gs['is_pinned'])
                                <span style="position: absolute; top: 8px; right: 8px; color: #C5A880; font-size: 0.9rem;">★ Ghim</span>
                            @endif
                            <div style="font-size: 0.72rem; background: #FAF8F5; display: inline-block; padding: 2px 6px; border-radius: 4px; color: #8C8479; margin-bottom: 0.5rem;">
                                {{ $gs['group'] }}
                            </div>
                            <div style="height: 60px; display: flex; align-items: center; justify-content: center; background: #FCFBF9; border-radius: 6px; margin-bottom: 0.5rem;">
                                <span style="font-family: 'Brush Script MT', cursive, serif; font-size: 1.8rem; color: #1F1D1A; opacity: 0.85;">{{ $gs['name'] }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.78rem; color: #6E685F;">
                                <strong>{{ $gs['name'] }}</strong>
                                <span>{{ $gs['time'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Bottom Plan Entitlement CTA --}}
        <div class="sandbox_plan_cta_card">
            <div>
                <h3 class="plan_cta_title">Trải Nghiệm Đầy Đủ Với Gói Trọn Vẹn (Wedding Pro)</h3>
                <p class="plan_cta_desc">
                    Mở khóa không giới hạn Sơ đồ xếp bàn tiệc, QR Check-in cổng đón tiếp, Két Kỷ Niệm 2 năm và tên miền riêng.
                </p>
            </div>
            <div class="plan_cta_actions">
                <a href="{{ route('pricing') }}" style="color: #6E685F; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
                    Xem Bảng Giá
                </a>
                <a href="{{ route('checkout.show', $proPlan->slug ?? 'pro') }}" class="sandbox_upgrade_btn">
                    Kích Hoạt Ngay 299.000đ &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

