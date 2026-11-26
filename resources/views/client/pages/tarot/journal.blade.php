@extends('client.layouts.master')

@section('title', 'Mây Hẹn – Sổ Bài Tarot Của Tôi')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/tarot.css') }}?v={{ filemtime(public_path('assets/client/css/pages/tarot.css')) }}">
@endpush

@section('content')
<div class="mayhen_tarot_page">
    <section class="mayhen_tarot_result_hero">
        <div class="mayhen_tarot_container">
            <div class="mayhen_tarot_eyebrow">✦ KHÔNG GIAN RIÊNG TƯ</div>
            <h1 class="mayhen_tarot_title">Sổ Bài Tarot Của Tôi</h1>
            <p class="mayhen_tarot_subtitle" style="margin: 0; max-width: 720px;">
                Lưu giữ toàn bộ trải bài, ghi chú chiêm nghiệm và thống kê thực tế các lá bài bạn đã rút qua thời gian.
            </p>
        </div>
    </section>

    <div class="mayhen_tarot_container" style="margin-top: 28px;">
        <div class="mayhen_tarot_report_grid">
            <div>
                @if ($readings->isEmpty())
                    <div class="mayhen_tarot_book_card" style="text-align: center; padding: 40px 24px;">
                        <h3>Bạn chưa có trải bài Tarot nào được lưu</h3>
                        <p style="color: #57534E; margin-bottom: 18px;">Hãy bắt đầu trải bài đầu tiên với bộ 78 lá Rider–Waite–Smith trên bàn nhung 3D.</p>
                        <a href="{{ route('tarot.index') }}" class="mayhen_tarot_btn_editorial">✦ Bắt đầu trải bài ngay</a>
                    </div>
                @else
                    @foreach ($readings as $r)
                        @php
                            $spreadMeta = collect($spreads)->firstWhere('spread_id', $r->spread_id);
                        @endphp
                        <article class="mayhen_tarot_book_card" style="margin-bottom: 16px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 8px;">
                                <span class="mayhen_tarot_badge mayhen_tarot_badge_dark">{{ $spreadMeta['name'] ?? $r->spread_id }}</span>
                                <span style="font-size: 12.5px; color: #57534E;">{{ $r->created_at?->format('H:i · d/m/Y') }}</span>
                            </div>
                            <h3 style="margin-bottom: 8px;">
                                <a href="{{ route('tarot.show', $r->slug) }}" style="color: inherit; text-decoration: none;">
                                    {{ $r->question ?: 'Trải bài không đặt câu hỏi cố định' }}
                                </a>
                            </h3>
                            <p style="font-size: 13.5px; color: #57534E; margin: 0 0 12px;">
                                Lá đã rút: <strong>{{ collect($r->selected_cards_json ?? [])->map(fn ($c) => ($c['name_en'] ?? '') . (($c['orientation'] ?? '') === 'reversed' ? ' (Ngược)' : ''))->implode(' · ') }}</strong>
                            </p>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 12px; color: #57534E;">{{ $r->journals_count }} ghi chú nhật ký</span>
                                <a href="{{ route('tarot.show', $r->slug) }}" class="mayhen_tarot_btn_outline_dark">Xem lại luận giải →</a>
                            </div>
                        </article>
                    @endforeach
                @endif
            </div>

            <aside>
                <div class="mayhen_tarot_book_card">
                    <div class="mayhen_tarot_kicker">THỐNG KÊ THỰC TẾ</div>
                    <h3>Lá bài xuất hiện nhiều nhất</h3>
                    @if (empty($topCards))
                        <p style="font-size: 13.5px; color: #57534E;">Chưa có dữ liệu thống kê.</p>
                    @else
                        <ul class="mayhen_tarot_action_list">
                            @foreach ($topCards as $cName => $cnt)
                                <li><strong>{{ $cName }}</strong> — {{ $cnt }} lần</li>
                            @endforeach
                        </ul>
                        <p style="font-size: 12px; color: #57534E; margin: 12px 0 0;">
                            Thống kê chỉ phản ánh tần suất rút thực tế trong lịch sử lưu của bạn, không mang hàm ý suy diễn định mệnh.
                        </p>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
