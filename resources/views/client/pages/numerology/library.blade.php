@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $currentYear = now('Asia/Ho_Chi_Minh')->year;

    $metaMap = [
        'so-chu-dao' => [
            'title' => 'Số Chủ Đạo & Đường Đời Thần Số Học (1–9, 11, 22, 33) | ' . $brandName,
            'h1' => 'Số Chủ Đạo & Chỉ Số Đường Đời (1–9, 11/2, 22/4, 33/6)',
            'desc' => 'Tra cứu ý nghĩa cốt lõi, tình yêu, hôn nhân, công việc và cách tính minh bạch cho 9 con số cơ bản cùng 3 Master Number theo Hans Decoz và David A. Phillips.',
            'route' => route('numerology.library.life_path'),
        ],
        'chi-so' => [
            'title' => 'Hệ Thống Chỉ Số Thần Số Học — Sứ Mệnh, Linh Hồn, Nhân Cách & Đỉnh Cao | ' . $brandName,
            'h1' => 'Hệ Thống Chỉ Số Thần Số Học Chuẩn Pythagorean',
            'desc' => 'Định nghĩa, công thức, ví dụ tính toán và phạm vi áp dụng của 5 chỉ số cốt lõi cùng các chỉ số mở rộng trong Thần Số Học hiện đại.',
            'route' => route('numerology.library.indicators'),
        ],
        'bieu-do-ngay-sinh' => [
            'title' => 'Biểu Đồ Ngày Sinh 3×3 & 8 Mũi Tên Thần Số Học (David A. Phillips) | ' . $brandName,
            'h1' => 'Biểu Đồ Ngày Sinh 3×3 & 8 Đường Mũi Tên',
            'desc' => 'Hướng dẫn lập Biểu đồ ngày sinh 3×3 và tra cứu 8 Đường Mũi Tên (đủ 3 số và trống cả 3 số) tham chiếu Dr. David A. Phillips.',
            'route' => route('numerology.library.birth_chart'),
        ],
        'nam-ca-nhan' => [
            'title' => 'Tính Năm Cá Nhân ' . $currentYear . ' & Chu Kỳ 9 Năm Thần Số Học | ' . $brandName,
            'h1' => 'Năm Cá Nhân ' . $currentYear . ' & Chu Kỳ 9 Năm (1–9)',
            'desc' => 'Công thức tính Năm cá nhân theo năm dương lịch 01/01–31/12 và ý nghĩa định hướng từng năm trong chu kỳ 9 năm.',
            'route' => route('numerology.library.personal_year'),
        ],
        'thu-vien' => [
            'title' => 'Bách Khoa Thần Số Học Minh Bạch Công Thức & Nguồn Tham Chiếu | ' . $brandName,
            'h1' => 'Bách Khoa Thần Số Học Mây Hẹn',
            'desc' => 'Thư viện tra cứu công khai các con số 1–9, Master 11/22/33, hệ thống chỉ số, Biểu đồ ngày sinh 3×3, chu kỳ cá nhân và 45 cặp số tình duyên.',
            'route' => route('numerology.library.index'),
        ],
    ];
    $active = $metaMap[$section] ?? $metaMap['thu-vien'];
@endphp

@section('title', $active['title'])
@section('meta_description', $active['desc'])
@section('canonical', $active['route'])

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/numerology.css') }}">
@endpush

@section('content')
<div class="mayhen_numerology_page">
    <section class="mayhen_numerology_hero" style="padding: 36px 0;">
        <div class="mayhen_numerology_container">
            <span class="mayhen_numerology_eyebrow">Bách Khoa Thần Số Học • Biên Soạn &amp; Kiểm Định Bởi Mây Hẹn (Cập nhật {{ $manifest['updated_at'] ?? '2026-11-25' }})</span>
            <h1 class="mayhen_numerology_h1">{{ $active['h1'] }}</h1>
            <p class="mayhen_numerology_lead">{{ $active['desc'] }}</p>

            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="{{ route('numerology.library.index') }}" class="{{ $section === 'thu-vien' ? 'mayhen_numerology_btn_primary' : 'mayhen_numerology_btn_outline' }} mayhen_numerology_btn_sm">Tổng Quan</a>
                <a href="{{ route('numerology.library.life_path') }}" class="{{ $section === 'so-chu-dao' ? 'mayhen_numerology_btn_primary' : 'mayhen_numerology_btn_outline' }} mayhen_numerology_btn_sm">Số 1–9 &amp; Master</a>
                <a href="{{ route('numerology.library.indicators') }}" class="{{ $section === 'chi-so' ? 'mayhen_numerology_btn_primary' : 'mayhen_numerology_btn_outline' }} mayhen_numerology_btn_sm">Các Chỉ Số</a>
                <a href="{{ route('numerology.library.birth_chart') }}" class="{{ $section === 'bieu-do-ngay-sinh' ? 'mayhen_numerology_btn_primary' : 'mayhen_numerology_btn_outline' }} mayhen_numerology_btn_sm">Biểu Đồ 3×3 &amp; Mũi Tên</a>
                <a href="{{ route('numerology.library.personal_year') }}" class="{{ $section === 'nam-ca-nhan' ? 'mayhen_numerology_btn_primary' : 'mayhen_numerology_btn_outline' }} mayhen_numerology_btn_sm">Năm Cá Nhân {{ $currentYear }}</a>
                <a href="{{ route('numerology.index') }}" class="mayhen_numerology_btn_crimson mayhen_numerology_btn_sm">Tra Cứu Của Bạn &rarr;</a>
            </div>
        </div>
    </section>

    <section class="mayhen_numerology_section mayhen_numerology_section--paper">
        <div class="mayhen_numerology_container">
            <div style="max-width: 500px; margin-bottom: 24px;">
                <input id="mayhenNumLibSearch" type="search" class="mayhen_numerology_input" placeholder="Tìm nhanh không dấu (vd: so 6, master 11, mui ten y chi, nam ca nhan 4...)">
            </div>

            @if($section === 'so-chu-dao' || $section === 'thu-vien')
                <h2 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 14px;">12 Con Số Cơ Bản &amp; Master Number (1–9, 11/2, 22/4, 33/6)</h2>
                <div class="mayhen_numerology_grid_3" style="margin-bottom: 32px;">
                    @foreach($archetypes as $numKey => $a)
                        <article class="mayhen_numerology_sheet" style="padding: 18px;" data-num-lib-item="so {{ $numKey }} {{ $a['title'] ?? '' }} {{ $a['core_meaning'] ?? '' }}">
                            <div style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 34px; color: #85182A; line-height: 1;">{{ $numKey }}</div>
                            <h3 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 4px 0 8px;">{{ $a['title'] ?? '' }}</h3>
                            <p style="font-size: 13.5px; line-height: 1.65; margin: 0 0 8px;">{{ $a['core_meaning'] ?? '' }}</p>
                            <p style="font-size: 12.5px; color: #1E4D3B; margin: 0;"><strong>Tình yêu:</strong> {{ $a['love_meaning'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            @endif

            @if($section === 'chi-so' || $section === 'thu-vien')
                <h2 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 14px;">Hệ Thống Chỉ Số &amp; Phạm Vi Ứng Dụng</h2>
                <div class="mayhen_numerology_grid_2" style="margin-bottom: 32px;">
                    @foreach($indicatorLenses as $code => $lens)
                        <article class="mayhen_numerology_note_box" style="background: #FAF5EB;" data-num-lib-item="{{ $lens['label'] ?? '' }} {{ $lens['scope'] ?? '' }}">
                            <strong style="color: #85182A;">{{ $lens['label'] ?? $code }}</strong>
                            <p style="font-size: 13.5px; margin: 6px 0 0;">{{ $lens['scope'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            @endif

            @if($section === 'bieu-do-ngay-sinh' || $section === 'thu-vien')
                <h2 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 14px;">8 Đường Mũi Tên Biểu Đồ Ngày Sinh (Tham Chiếu David A. Phillips)</h2>
                <div class="mayhen_numerology_grid_2" style="margin-bottom: 32px;">
                    @foreach($arrowsCatalog['arrows'] ?? [] as $ar)
                        <article class="mayhen_numerology_note_box" style="background: #FCFBF9;" data-num-lib-item="{{ $ar['code'] }} {{ $ar['full_name'] ?? '' }} {{ $ar['empty_name'] ?? '' }}">
                            <strong style="color: #1E4D3B;">{{ $ar['code'] }} ({{ $ar['axis'] ?? '' }}) — {{ $ar['full_name'] ?? '' }}</strong>
                            <p style="font-size: 13.5px; margin: 6px 0;">{{ $ar['full_meaning'] ?? '' }}</p>
                            @if(! empty($ar['empty_name']))
                                <p style="font-size: 12.5px; color: #5C5248; margin: 0;"><strong>Khi trống cả 3 số ({{ $ar['empty_name'] }}):</strong> {{ $ar['empty_meaning'] ?? '' }}</p>
                            @else
                                <p style="font-size: 12.5px; color: #5C5248; margin: 0;">{{ $ar['empty_meaning'] ?? '' }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif

            @if($section === 'nam-ca-nhan' || $section === 'thu-vien')
                <h2 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 14px;">Chu Kỳ 9 Năm Cá Nhân (Năm Dương Lịch 01/01–31/12)</h2>
                <div class="mayhen_numerology_grid_3">
                    @foreach($cyclesCatalog['personal_years'] ?? [] as $yKey => $py)
                        <article class="mayhen_numerology_sheet" style="padding: 16px;" data-num-lib-item="nam ca nhan {{ $yKey }} {{ $py['theme'] ?? '' }} {{ $py['summary'] ?? '' }}">
                            <div style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 28px; color: #1E4D3B;">Năm {{ $yKey }}</div>
                            <h3 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 2px 0 6px; font-size: 17px;">{{ $py['theme'] ?? '' }}</h3>
                            <p style="font-size: 13px; margin: 0 0 6px;">{{ $py['summary'] ?? '' }}</p>
                            <p style="font-size: 12px; color: #85182A; margin: 0;"><strong>Trọng tâm:</strong> {{ $py['focus'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/numerology.js') }}"></script>
@endpush
