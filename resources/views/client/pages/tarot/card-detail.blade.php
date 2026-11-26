@extends('client.layouts.master')

@section('title', 'Ý Nghĩa Lá Bài ' . $card['name_en'] . ' (' . $card['name_vi'] . ') — Tarot RWS')
@section('meta_description', 'Giải mã chi tiết lá bài ' . $card['name_en'] . ' (' . $card['name_vi'] . ') chuẩn Rider–Waite–Smith: chiều Xuôi, chiều Ngược, biểu tượng hình ảnh, tình yêu, hôn nhân, công việc và lời khuyên thực tế.')
@section('canonical', route('tarot.card_detail', $card['slug']))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/tarot.css') }}?v={{ filemtime(public_path('assets/client/css/pages/tarot.css')) }}">
@endpush

@section('content')
<div class="mayhen_tarot_page">
    <section class="mayhen_tarot_result_hero">
        <div class="mayhen_tarot_container">
            <div style="margin-bottom: 12px;">
                <a href="{{ route('tarot.library') }}" style="color: #E7D2A3; font-size: 13px; text-decoration: none;">← Quay lại Bách Khoa 78 Lá Bài</a>
            </div>
            <div class="mayhen_tarot_meta_row">
                <span class="mayhen_tarot_badge">{{ $card['arcana'] === 'major' ? 'Ẩn Chính (Major Arcana)' : 'Ẩn Phụ (Minor Arcana)' }}</span>
                <span class="mayhen_tarot_badge">Nguyên tố: {{ $card['element'] }}</span>
                @if (! empty($card['astrology']))
                    <span class="mayhen_tarot_badge">{{ $card['astrology'] }}</span>
                @endif
            </div>
            <h1 class="mayhen_tarot_title">{{ $card['roman'] ? $card['roman'] . ' · ' : '' }}{{ $card['name_en'] }} — {{ $card['name_vi'] }}</h1>
        </div>
    </section>

    <div class="mayhen_tarot_container" style="margin-top: 28px;">
        <div class="mayhen_tarot_report_grid">
            <div>
                <article class="mayhen_tarot_book_card">
                    <div class="mayhen_tarot_kicker">CHIỀU XUÔI (UPRIGHT)</div>
                    <h2>Thông điệp khi lá bài xuất hiện xuôi</h2>
                    <p style="font-size: 13px; color: #142D25; font-weight: 600; margin-bottom: 10px;">
                        Từ khóa: {{ implode(' · ', $card['keywords_upright'] ?? []) }}
                    </p>
                    <p style="font-size: 15px; line-height: 1.75; margin: 0;">{{ $card['upright_meaning'] ?? '' }}</p>
                </article>

                <article class="mayhen_tarot_book_card">
                    <div class="mayhen_tarot_kicker">CHIỀU NGƯỢC (REVERSED)</div>
                    <h2>Thông điệp khi lá bài xuất hiện ngược</h2>
                    <p style="font-size: 13px; color: #85182A; font-weight: 600; margin-bottom: 10px;">
                        Từ khóa: {{ implode(' · ', $card['keywords_reversed'] ?? []) }}
                    </p>
                    <p style="font-size: 15px; line-height: 1.75; margin: 0;">{{ $card['reversed_meaning'] ?? '' }}</p>
                </article>

                <article class="mayhen_tarot_book_card">
                    <div class="mayhen_tarot_kicker">ỨNG DỤNG THEO 8 LĨNH VỰC</div>
                    <h2>Giải mã trong Tình yêu, Hôn nhân &amp; Đời sống</h2>
                    @php
                        $domainLabels = [
                            'love' => 'Tình yêu & Hẹn hò',
                            'marriage' => 'Hôn nhân & Chuẩn bị kết hôn',
                            'family' => 'Gia đình & Tổ ấm',
                            'work' => 'Công việc & Sự nghiệp',
                            'finance' => 'Tài chính & Quản trị',
                            'growth' => 'Phát triển bản thân',
                            'advice' => 'Lời khuyên cốt lõi',
                            'warning' => 'Điểm cần lưu ý',
                        ];
                    @endphp
                    @foreach ($domainLabels as $dKey => $dLabel)
                        @if (! empty($card['domains'][$dKey]))
                            <div style="padding: 10px 0; border-bottom: 1px solid rgba(41,37,36,0.08);">
                                <strong>{{ $dLabel }}:</strong>
                                <span style="color: #57534E;">{{ $card['domains'][$dKey] }}</span>
                            </div>
                        @endif
                    @endforeach
                </article>
            </div>

            <aside>
                <div class="mayhen_tarot_book_card" style="text-align: center;">
                    <div class="mayhen_tarot_card_frame" style="width: 190px; height: 316px; margin-bottom: 16px;" data-tarot-card-art='@json($card)'></div>
                    <a href="{{ route('tarot.index') }}" class="mayhen_tarot_btn_editorial" style="width: 100%; justify-content: center; box-sizing: border-box;">
                        ✦ Trải bài với bộ 78 lá RWS
                    </a>
                </div>

                <div class="mayhen_tarot_book_card">
                    <div class="mayhen_tarot_kicker">BIỂU TƯỢNG HÌNH ẢNH</div>
                    <h3>Biểu tượng chuẩn RWS</h3>
                    <ul class="mayhen_tarot_action_list">
                        @foreach (($card['symbols'] ?? []) as $sym)
                            <li>{{ $sym }}</li>
                        @endforeach
                    </ul>
                    @if (! empty($card['common_misconception']))
                        <div style="margin-top: 14px; background: #F3EBE0; padding: 12px; border-radius: 8px; font-size: 13.5px;">
                            <strong>Hiểu lầm thường gặp:</strong> {{ $card['common_misconception'] }}
                        </div>
                    @endif
                </div>

                <div style="display: flex; justify-content: space-between; gap: 10px;">
                    @if (! empty($adjacent['prev']))
                        <a href="{{ route('tarot.card_detail', $adjacent['prev']['slug']) }}" class="mayhen_tarot_btn_outline_dark" style="flex: 1;">← {{ $adjacent['prev']['name_en'] }}</a>
                    @endif
                    @if (! empty($adjacent['next']))
                        <a href="{{ route('tarot.card_detail', $adjacent['next']['slug']) }}" class="mayhen_tarot_btn_outline_dark" style="flex: 1; justify-content: flex-end;">{{ $adjacent['next']['name_en'] }} →</a>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/client/js/pages/tarot.js') }}?v={{ filemtime(public_path('assets/client/js/pages/tarot.js')) }}" defer></script>
@endpush
