@extends('client.layouts.master')

@section('title', 'Lịch Sử Hồ Sơ Tính Cách Của Tôi')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/personality.css') }}">
@endpush

@section('content')
<main class="mayhen_personality_page">
    <section class="mayhen_personality_hero">
        <div class="mayhen_personality_container">
            <span class="mayhen_personality_kicker">Personality History • Hồ Sơ Riêng Tư</span>
            <h1 class="mayhen_personality_h1">Lịch Sử Trắc Nghiệm Tính Cách</h1>
            <p class="mayhen_personality_lead">
                Sự thay đổi điểm số hoặc nhóm đề xuất giữa hai thời điểm hoàn toàn bình thường (nhất là khi có chiều gần ranh giới 45–55). Chỉ nên so sánh trực tiếp hai lần làm cùng một phiên bản phương pháp.
            </p>
        </div>
    </section>

    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            @if($sessions->isEmpty())
                <div class="mayhen_personality_card" style="text-align:center; padding:36px;">
                    <p>Bạn chưa có hồ sơ trắc nghiệm nào được lưu.</p>
                    <a href="{{ route('personality.start') }}" class="mayhen_personality_btn mayhen_personality_btn--primary">Bắt đầu bài test đầu tiên</a>
                </div>
            @else
                <div class="mayhen_personality_grid_2">
                    @foreach($sessions as $s)
                        <div class="mayhen_personality_card">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span class="mayhen_personality_type_code">{{ $s->effectiveType() ?? 'UNDECIDED' }}</span>
                                <span style="font-size:0.82rem; color:var(--mayhen-personality-muted);">{{ $s->submitted_at?->format('d/m/Y H:i') }}</span>
                            </div>
                            <div style="font-size:0.86rem; color:var(--mayhen-personality-muted); margin:6px 0;">
                                Phiên bản: <code>{{ $s->assessment_version }}</code> • Đã trả lời: {{ $s->answered_count }}/{{ $s->question_count }}
                            </div>
                            @if($s->user_notes)
                                <p style="font-size:0.88rem; margin:6px 0;">Ghi chú: {{ $s->user_notes }}</p>
                            @endif
                            <a href="{{ route('personality.result', ['slug' => $s->slug, 'skip_reveal' => 1]) }}" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm" style="margin-top:8px;">
                                Mở báo cáo chi tiết
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</main>
@endsection
