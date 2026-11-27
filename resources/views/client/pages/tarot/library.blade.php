@extends('client.layouts.master')

@section('title', 'Bách Khoa 78 Lá Bài Tarot Rider–Waite–Smith')
@section('meta_description', 'Tra cứu trọn bộ 78 lá bài Tarot Rider–Waite–Smith (22 Ẩn Chính Strength VIII, Justice XI và 56 Ẩn Phụ): ý nghĩa Xuôi, Ngược, biểu tượng và 8 lĩnh vực đời sống tại Mây Hẹn.')
@section('canonical', route('tarot.library'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/tarot.css') }}?v={{ filemtime(public_path('assets/client/css/pages/tarot.css')) }}">
@endpush

@section('content')
<div class="mayhen_tarot_page">
    <section class="mayhen_tarot_result_hero">
        <div class="mayhen_tarot_container">
            <div class="mayhen_tarot_eyebrow">✦ RIDER–WAITE–SMITH · 78 LÁ CHUẨN HỆ THỐNG</div>
            <h1 class="mayhen_tarot_title">Bách Khoa 78 Lá Bài Tarot</h1>
            <p class="mayhen_tarot_subtitle" style="margin: 0; max-width: 720px;">
                Tra cứu toàn bộ 22 Ẩn Chính (Major Arcana — Strength VIII, Justice XI) và 56 Ẩn Phụ (Wands, Cups, Swords, Pentacles). Nhấn từng lá để xem biểu tượng, chiều Xuôi/Ngược và ứng dụng tình yêu – cuộc sống.
            </p>
        </div>
    </section>

    <div class="mayhen_tarot_container">
        <div class="mayhen_tarot_filter_bar">
            <div class="mayhen_tarot_filter_pills">
                <button type="button" class="mayhen_tarot_filter_pill is-active" data-tarot-suit-filter="all">Tất cả (78)</button>
                <button type="button" class="mayhen_tarot_filter_pill" data-tarot-suit-filter="major">Ẩn Chính (22)</button>
                <button type="button" class="mayhen_tarot_filter_pill" data-tarot-suit-filter="wands">Bộ Gậy · Wands (14)</button>
                <button type="button" class="mayhen_tarot_filter_pill" data-tarot-suit-filter="cups">Bộ Cốc · Cups (14)</button>
                <button type="button" class="mayhen_tarot_filter_pill" data-tarot-suit-filter="swords">Bộ Kiếm · Swords (14)</button>
                <button type="button" class="mayhen_tarot_filter_pill" data-tarot-suit-filter="pentacles">Bộ Xu · Pentacles (14)</button>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <input type="search" id="tarotLibrarySearch" class="mayhen_tarot_light_input" style="width: 240px;" placeholder="Tìm tên lá bài (VN/EN)...">
                <span id="tarotLibraryCount" style="font-size: 13px; color: #57534E; white-space: nowrap;">78 / 78 lá bài</span>
            </div>
        </div>

        <div class="mayhen_tarot_library_grid">
            @foreach ($cards as $c)
                <a href="{{ route('tarot.card_detail', $c['slug']) }}"
                   class="mayhen_tarot_library_card"
                   data-tarot-library-item
                   data-suit="{{ $c['suit'] }}"
                   data-search="{{ $c['roman'] }} {{ $c['name_en'] }} {{ $c['name_vi'] }} {{ implode(' ', $c['keywords_upright'] ?? []) }}">
                    @if (! empty($c['has_local_image']))
                        <div class="mayhen_tarot_library_img_wrap">
                            <img src="{{ asset($c['image_rel']) }}"
                                 alt="Lá bài Tarot {{ $c['name_en'] }} ({{ $c['name_vi'] }}) chuẩn Rider–Waite–Smith"
                                 width="136"
                                 height="233"
                                 loading="lazy"
                                 decoding="async"
                                 class="mayhen_tarot_library_rws_img">
                        </div>
                    @else
                        <div class="mayhen_tarot_card_frame" data-tarot-card-art='@json($c)'></div>
                    @endif
                    <strong style="font-family: var(--tarot-serif); font-size: 15.5px;">{{ $c['roman'] ? $c['roman'] . ' · ' : '' }}{{ $c['name_en'] }}</strong>
                    <span style="font-size: 12.5px; color: #57534E; margin-top: 2px;">{{ $c['name_vi'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/tarot.js') }}"></script>
@endpush
