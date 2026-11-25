@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $canonicalUrl = route('tuvi.synastry.index');
@endphp

@section('title', 'Nhân Duyên Song Mệnh — Xem Tử Vi Cặp Đôi & Hôn Nhân | ' . $brandName)
@section('meta_description', 'Nhân Duyên Song Mệnh của Mây Hẹn đối chiếu hai lá số Tử Vi độc lập trên 10 chủ đề tình yêu, giao tiếp, tài chính chung và hòa hợp hai bên gia đình. Tuyệt đối không chấm % hợp tuổi hay phán định chia ly.')
@section('canonical', $canonicalUrl)

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/tuvi.css') }}">
@endpush

@section('content')
<div class="mayhen_tuvi_page">
    <section class="mayhen_tuvi_celestial_hero">
        <canvas class="mayhen_tuvi_starfield_canvas" aria-hidden="true"></canvas>
        <div class="mayhen_tuvi_container">
            <div class="mayhen_tuvi_hero_head">
                <span class="mayhen_tuvi_kicker">✦ Nhật · Nguyệt · Duyên — Không Chấm % Hợp Tuổi Hay Ly Hôn</span>
                <h1 class="mayhen_tuvi_hero_h1">
                    Nhân Duyên <span>Song Mệnh</span>
                </h1>
                <p class="mayhen_tuvi_hero_lead">
                    Khung đối chiếu nhân văn giữa hai lá số Tử Vi độc lập để cặp đôi hiểu ngôn ngữ yêu thương, phong cách giải quyết bất đồng và chuẩn bị hành trang trước ngày về chung một nhà.
                </p>
            </div>

            <div class="mayhen_tuvi_synastry_duet">
                <div class="mayhen_tuvi_duet_card mayhen_tuvi_duet_card--sun">
                    <span style="font-size: 11px; letter-spacing: 0.2em; color: #EBC984;">NHẬT MỆNH</span>
                    <div style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 54px; color: #F4DEAE; line-height: 1;">日</div>
                    <div style="font-size: 13px; color: #EADBC0;">Lá số thứ nhất</div>
                </div>
                <div class="mayhen_tuvi_duet_medallion" aria-hidden="true">緣</div>
                <div class="mayhen_tuvi_duet_card mayhen_tuvi_duet_card--moon">
                    <span style="font-size: 11px; letter-spacing: 0.2em; color: #EBC984;">NGUYỆT MỆNH</span>
                    <div style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 54px; color: #F4DEAE; line-height: 1;">月</div>
                    <div style="font-size: 13px; color: #EADBC0;">Lá số thứ hai</div>
                </div>
            </div>
        </div>
    </section>

    <section class="mayhen_tuvi_section_light">
        <div class="mayhen_tuvi_container" style="max-width: 920px;">
            <div class="mayhen_tuvi_card" style="margin-bottom: 28px;">
                <span class="mayhen_tuvi_badge">Thiết Lập Đối Chiếu Riêng Tư</span>
                <h2 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 10px;">Chọn 2 Lá Số Độc Lập Trong Hồ Sơ Của Bạn</h2>

                @if(! $canUseFree)
                    <p style="margin-bottom: 14px;">Vui lòng đăng nhập tài khoản Mây Hẹn (miễn phí gói Thành Viên Premium 0đ) và lập ít nhất 2 lá số để kích hoạt Nhân Duyên Song Mệnh.</p>
                    <a href="{{ route('login', ['redirect' => route('tuvi.synastry.index')]) }}" class="mayhen_tuvi_btn_primary">Đăng Nhập Miễn Phí</a>
                @elseif($charts->count() < 2)
                    <p style="margin-bottom: 14px;">
                        Hiện Hồ Sơ Thiên Mệnh của bạn mới có <strong>{{ $charts->count() }}</strong> lá số. Hãy lập thêm lá số thứ hai (của bạn đời / người thương) rồi quay lại đây nhé.
                    </p>
                    <a href="{{ route('tuvi.index') }}" class="mayhen_tuvi_btn_primary">+ Lập Thêm Lá Số Tại /tu-vi</a>
                @else
                    <form id="mayhenTuViSynastryForm" action="{{ route('tuvi.synastry.create') }}" method="POST">
                        <div class="mayhen_tuvi_form_grid">
                            <div class="mayhen_tuvi_field">
                                <label for="synChartA">Lá số Nhật Mệnh (Người thứ nhất) *</label>
                                <select id="synChartA" name="chart_a_id" class="mayhen_tuvi_select" required>
                                    @foreach($charts as $idx => $c)
                                        <option value="{{ $c->id }}" @selected($idx === 0)>
                                            {{ $c->profile?->nickname ?: 'Hồ sơ #'.$c->id }} (v{{ $c->version_number }} • {{ $c->viewed_year }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mayhen_tuvi_field">
                                <label for="synChartB">Lá số Nguyệt Mệnh (Người đồng hành) *</label>
                                <select id="synChartB" name="chart_b_id" class="mayhen_tuvi_select" required>
                                    @foreach($charts as $idx => $c)
                                        <option value="{{ $c->id }}" @selected($idx === 1)>
                                            {{ $c->profile?->nickname ?: 'Hồ sơ #'.$c->id }} (v{{ $c->version_number }} • {{ $c->viewed_year }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mayhen_tuvi_field">
                            <label for="synCoupleNote">Bối cảnh thực tế của hai bạn (tùy chọn — giúp bài luận sát đời sống hơn)</label>
                            <textarea id="synCoupleNote" name="couple_note" rows="3" class="mayhen_tuvi_textarea" maxlength="500" placeholder="Ví dụ: Tụi mình yêu nhau 2 năm, dự định cưới vào cuối năm nay và muốn chuẩn bị tốt chuyện tài chính chung, ra mắt hai bên gia đình..."></textarea>
                        </div>

                        <div class="mayhen_tuvi_field">
                            <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; font-weight: 600;">
                                <input type="checkbox" name="partner_consent_confirmed" value="1" required>
                                <span>Tôi xác nhận đã có sự đồng thuận khi nhập thông tin sinh của người đồng hành và hiểu rằng đây là góc nhìn tham khảo nhân văn, không thay thế sự đối thoại thực tế giữa hai người.</span>
                            </label>
                        </div>

                        <div id="mayhenTuViSynastryErr" class="mayhen_tuvi_alert_error" role="alert"></div>

                        <button type="submit" class="mayhen_tuvi_btn_primary" style="margin-top: 8px;">
                            ✦ Khám Phá Nhân Duyên Song Mệnh
                        </button>
                    </form>
                @endif
            </div>

            <div class="mayhen_tuvi_section_head">
                <h2>10 Chủ Đề Cốt Lõi Của Nhân Duyên Song Mệnh</h2>
                <p>Không dừng ở chuyện “hợp – khắc” đơn giản, Mây Hẹn đồng hành vào đúng những câu chuyện thực tế nhất của tình yêu và hôn nhân.</p>
            </div>
            <div class="mayhen_tuvi_layers_grid">
                @foreach($synastryTopics as $i => $topic)
                    <div class="mayhen_tuvi_card">
                        <span class="mayhen_tuvi_badge">Chủ đề {{ $i + 1 }}</span>
                        <h3>{{ $topic['label'] }}</h3>
                        <p>{{ $topic['focus'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/tuvi.js') }}"></script>
@endpush
