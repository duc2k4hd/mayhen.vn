@extends('client.layouts.master')

@section('title', $pageMeta['title'])
@section('meta_description', $pageMeta['meta_description'])
@section('canonical', $pageMeta['canonical'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/marriage.css') }}?v={{ filemtime(public_path('assets/client/css/pages/marriage.css')) }}">
@endpush

@section('content')
<div class="mayhen_marriage_page">
    <section class="mayhen_marriage_hero">
        <div class="mayhen_marriage_container">
            <div class="mayhen_marriage_eyebrow">✦ MÂY HẸN – DUYÊN HỢP · PHÉP TRA CÓ HƯỚNG CAN CHỒNG × CHI VỢ</div>
            <h1 class="mayhen_marriage_h1">{{ $pageMeta['h1'] }}</h1>
            <p class="mayhen_marriage_lead">
                Thấu hiểu nhân duyên, cùng nhau vun đắp trăm năm. Tra cứu trọn bộ 120 tổ hợp Cao Ly Đầu Hình theo quy tắc cổ truyền <strong>Nam dụng Thiên Can – Nữ dụng Địa Chi</strong> với Vòng Thiên Văn Đồng Ngọc, 6 Huy Chương Lục Cảnh và Bảng Tra 120 quẻ tương tác miễn phí.
            </p>

            <nav class="mayhen_marriage_nav" aria-label="Điều hướng Duyên Hợp">
                <a href="{{ route('marriage.caoly.index') }}" class="mayhen_marriage_pill {{ $subView === null ? 'is-active' : '' }}">Vòng Tra Cao Ly</a>
                <a href="{{ route('marriage.caoly.matrix') }}" class="mayhen_marriage_pill {{ $subView === 'bang-tra' ? 'is-active' : '' }}">Bảng Tra 120 Tổ Hợp</a>
                <a href="{{ route('marriage.caoly.scenes') }}" class="mayhen_marriage_pill {{ $subView === 'luc-canh' ? 'is-active' : '' }}">6 Huy Chương Lục Cảnh</a>
                <a href="{{ route('marriage.compatibility.index') }}" class="mayhen_marriage_pill">Xem Tuổi Vợ Chồng Tổng Hợp &rarr;</a>
                @auth
                    <a href="{{ route('marriage.book.index') }}" class="mayhen_marriage_pill">Sổ Nhân Duyên Của Tôi</a>
                @endauth
            </nav>

            {{-- 1. VÒNG THIÊN VĂN ĐỒNG & NGỌC 10 THIÊN CAN × 12 ĐỊA CHI --}}
            <div class="mayhen_marriage_two_col">
                <div class="mayhen_marriage_card mayhen_cao_ly_wheel_wrap">
                    <div style="font-size:11px;letter-spacing:0.14em;color:#9A7228;font-weight:600;">MÂY HẸN · DUYÊN HỢP</div>
                    <h2 class="mayhen_marriage_card_title" style="color:#85182A;">Cao Ly Đầu Hình</h2>
                    <p class="mayhen_marriage_card_sub" style="margin-bottom:10px;"><em>Thiên Can chồng kết nối Địa Chi vợ (bấm trực tiếp trên vòng tròn)</em></p>

                    <div id="mayhenCaoLyWheelSvgWrap"></div>

                    <div class="mayhen_marriage_form_row" style="width:100%;margin-top:14px;text-align:left;">
                        <div class="mayhen_marriage_field">
                            <label for="mayhenCaoLyStemSelect" style="color:#1E4D3B;">Thiên Can chồng</label>
                            <select id="mayhenCaoLyStemSelect" class="mayhen_marriage_select">
                                @foreach ($stems as $idx => $s)
                                    <option value="{{ $idx }}" @selected($idx === 6)>{{ $s['hanzi'] }} – {{ $s['name'] }} ({{ $s['yin_yang'] }} {{ $s['element'] }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mayhen_marriage_field">
                            <label for="mayhenCaoLyBranchSelect" style="color:#85182A;">Địa Chi vợ</label>
                            <select id="mayhenCaoLyBranchSelect" class="mayhen_marriage_select">
                                @foreach ($branches as $idx => $b)
                                    <option value="{{ $idx }}" @selected($idx === 7)>{{ $b['hanzi'] }} – {{ $b['name'] }} ({{ $b['zodiac'] }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mayhen_marriage_card">
                    <div id="mayhenCaoLyResultBox"></div>
                    <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--mayhen-cao-ly-border);font-size:12.5px;color:#6E6259;line-height:1.6;">
                        <strong>Lưu ý minh bạch nguồn dữ liệu:</strong> Cao Ly Đầu Hình là phép tra có hướng (Can chồng × Chi vợ); đảo vai trò hai người sẽ cho tổ hợp khác. Các bản in cổ truyền có thể tồn tại dị bản phân loại — Mây Hẹn giữ thuật ngữ lịch sử và diễn giải bằng ngôn ngữ nhân văn, không dự đoán chia ly hay tuổi thọ.
                    </div>
                    <div style="margin-top:14px;display:flex;flex-wrap:wrap;gap:10px;">
                        <a href="{{ route('marriage.compatibility.index') }}" class="mayhen_marriage_btn">Xem Tuổi Vợ Chồng Đa Phương Pháp &rarr;</a>
                        <a href="{{ route('bazi.love') }}" class="mayhen_marriage_btn mayhen_marriage_btn--outline">Bát Tự Tình Duyên</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="mayhen_marriage_container">
        {{-- 2. SÁU HUY CHƯƠNG LỤC CẢNH CAO LY ĐẦU HÌNH --}}
        <section class="mayhen_marriage_section" id="luc-canh">
            <div class="mayhen_marriage_card">
                <h2 class="mayhen_marriage_card_title">Sáu Huy Chương Lục Cảnh Cao Ly Đầu Hình</h2>
                <p class="mayhen_marriage_card_sub">Mỗi cảnh mang một hình tượng văn hóa trung lập, không sử dụng hình ảnh gây sợ hãi. Bấm vào từng huy chương để xem ý nghĩa lịch sử và diễn giải hiện đại.</p>

                <div class="mayhen_cao_ly_medals_grid">
                    @foreach ($lucCanh as $key => $sc)
                        <div class="mayhen_cao_ly_medal_card {{ $loop->first ? 'is-active' : '' }}"
                             data-name="{{ $sc['name'] }}"
                             data-hanzi="{{ $sc['hanzi'] }}"
                             data-hist="{{ $sc['historical_note'] }}"
                             data-modern="{{ $sc['modern_insight'] }}"
                             data-tip="{{ $sc['couple_tip'] }}">
                            <svg width="118" height="118" viewBox="0 0 120 120" aria-hidden="true" style="margin-bottom:8px;">
                                <circle cx="60" cy="60" r="56" fill="{{ $sc['color'] }}" stroke="#CFA860" stroke-width="3.5" />
                                <circle cx="60" cy="60" r="42" fill="none" stroke="rgba(250,245,235,0.45)" stroke-width="1.2" />
                                @for ($a = 0; $a < 12; $a++)
                                    <ellipse cx="60" cy="11" rx="2.4" ry="5.5" fill="none" stroke="rgba(244,220,170,0.75)" stroke-width="1" transform="rotate({{ $a * 30 }} 60 60)" />
                                @endfor
                                <text x="60" y="69" text-anchor="middle" fill="#FAF5EB" font-size="27" font-weight="700" font-family="serif">{{ $sc['hanzi'] }}</text>
                            </svg>
                            <div style="font-weight:700;font-size:15px;color:#292524;">{{ $sc['name'] }}</div>
                            <div style="font-size:12.5px;color:#6E6259;">{{ $sc['keyword'] }}</div>
                        </div>
                    @endforeach
                </div>

                @php $firstScene = reset($lucCanh); @endphp
                <div id="mayhenLucCanhActiveDetail" style="margin-top:16px;padding:16px 18px;background:#FAF5EB;border:1px solid var(--mayhen-cao-ly-border);border-radius:12px;font-size:13.5px;line-height:1.6;">
                    <strong style="color:#85182A;font-size:17px;">{{ $firstScene['name'] }} ({{ $firstScene['hanzi'] }})</strong>
                    <p style="margin:6px 0;"><strong>Ngữ cảnh cổ truyền:</strong> {{ $firstScene['historical_note'] }}</p>
                    <p style="margin:6px 0;color:#1E4D3B;"><strong>Diễn giải hiện đại Mây Hẹn:</strong> {{ $firstScene['modern_insight'] }}</p>
                    <p style="margin:6px 0 0;"><strong>Gợi ý cặp đôi:</strong> {{ $firstScene['couple_tip'] }}</p>
                </div>
            </div>
        </section>

        {{-- 3. BẢNG TRA 120 TỔ HỢP TƯƠNG TÁC (10 THIÊN CAN × 12 ĐỊA CHI) --}}
        <section class="mayhen_marriage_section" id="bang-tra-120">
            <div class="mayhen_marriage_card">
                <h2 class="mayhen_marriage_card_title">Bảng Tra 120 Tổ Hợp Cao Ly Đầu Hình (10 Thiên Can × 12 Địa Chi)</h2>
                <p class="mayhen_marriage_card_sub">Chọn bất kỳ ô nào trên ma trận để xác định mã khóa `CLDH-{CAN}-{CHI}` và xem ngay đồ hình tương ứng.</p>

                <div class="mayhen_cao_ly_matrix_wrap">
                    <table class="mayhen_cao_ly_matrix">
                        <thead>
                            <tr>
                                <th>Can / Chi</th>
                                @foreach ($branches as $b)
                                    <th>{{ $b['name'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stems as $sIdx => $s)
                                <tr>
                                    <th>{{ $s['name'] }}</th>
                                    @foreach ($branches as $bIdx => $b)
                                        <td>
                                            <button type="button" class="mayhen_cao_ly_matrix_cell" data-s="{{ $sIdx }}" data-b="{{ $bIdx }}" aria-label="{{ $s['name'] }} – {{ $b['name'] }}">·</button>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div id="mayhenCaoLyMatrixDetail" style="margin-top:16px;padding-top:14px;border-top:1px solid var(--mayhen-cao-ly-border);"></div>
            </div>
        </section>
    </div>
</div>

<script id="mayhenCaoLyDataset" type="application/json">{!! json_encode(['stems' => array_values($stems), 'branches' => array_values($branches), 'records' => $all120], JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/client/js/pages/marriage.js') }}?v={{ filemtime(public_path('assets/client/js/pages/marriage.js')) }}" defer></script>
@endpush
