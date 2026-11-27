@extends('client.layouts.master')

@section('title', $typeInfo['code'].' ('.$typeInfo['art_title'].') — '.$typeInfo['vietnamese_name'])
@section('meta_description', 'Tìm hiểu nhóm tính cách '.$typeInfo['code'].' ('.$typeInfo['vietnamese_name'].'): 4 chiều xu hướng, Function Stack lý thuyết, điểm mạnh, giao tiếp, tình yêu, gia đình và những hiểu lầm phổ biến.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/personality.css') }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/personality.js') }}"></script>
@endpush

@section('content')
<main class="mayhen_personality_page">
    <section class="mayhen_personality_hero">
        <div class="mayhen_personality_container">
            <div class="mayhen_personality_hero_grid">
                <div>
                    <span class="mayhen_personality_kicker">{{ $typeInfo['group'] }} • Hình tượng {{ $typeInfo['art_title'] }}</span>
                    <h1 class="mayhen_personality_h1">{{ $typeInfo['code'] }} — {{ $typeInfo['vietnamese_name'] }}</h1>
                    <p class="mayhen_personality_lead">{{ $typeInfo['theory_summary'] }}</p>
                    <div class="mayhen_personality_actions">
                        <a href="{{ route('personality.start') }}" class="mayhen_personality_btn mayhen_personality_btn--primary">
                            Làm bài trắc nghiệm khám phá nhóm của bạn
                        </a>
                        <a href="{{ route('personality.gallery') }}" class="mayhen_personality_btn mayhen_personality_btn--outline">
                            Xem cả 16 nhóm
                        </a>
                    </div>
                </div>
                <div class="mayhen_personality_sculpture_stage">
                    <div class="mayhen_personality_sculpture_card" data-mode="3d" style="max-width:290px; width:100%;">
                        {!! $artworkSvg !!}
                        <p style="font-size:0.84rem; color:var(--mayhen-personality-muted); margin:10px 0 0;">
                            <strong>{{ $typeInfo['symbol_name'] }}</strong> — {{ $typeInfo['art_story'] }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <div class="mayhen_personality_grid_2">
                <div class="mayhen_personality_card">
                    <h2 class="mayhen_personality_h3">Điểm mạnh tiềm năng</h2>
                    <ul style="margin:8px 0 0 18px; line-height:1.65;">
                        @foreach($typeInfo['strengths'] as $st)<li>{{ $st }}</li>@endforeach
                    </ul>
                    <h2 class="mayhen_personality_h3" style="margin-top:20px;">Tình huống cần lưu ý</h2>
                    <ul style="margin:8px 0 0 18px; line-height:1.65;">
                        @foreach($typeInfo['watch_outs'] as $wo)<li>{{ $wo }}</li>@endforeach
                    </ul>
                    <h2 class="mayhen_personality_h3" style="margin-top:20px;">Những hiểu lầm phổ biến</h2>
                    <ul style="margin:8px 0 0 18px; line-height:1.65;">
                        @foreach($typeInfo['common_myths'] as $m)<li>{{ $m }}</li>@endforeach
                    </ul>
                </div>

                <div class="mayhen_personality_card">
                    <h2 class="mayhen_personality_h3">Phong cách giao tiếp</h2>
                    <p style="line-height:1.65;">{{ $typeInfo['communication_style'] }}</p>
                    <h2 class="mayhen_personality_h3" style="margin-top:16px;">Tình yêu & Hôn nhân</h2>
                    <p style="line-height:1.65;">{{ $typeInfo['love_theme'] }}</p>
                    <h2 class="mayhen_personality_h3" style="margin-top:16px;">Đời sống gia đình</h2>
                    <p style="line-height:1.65;">{{ $typeInfo['family_theme'] }}</p>
                    <h2 class="mayhen_personality_h3" style="margin-top:16px;">Công việc & Học tập</h2>
                    <p style="line-height:1.65;">{{ $typeInfo['work_learning_style'] }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <h2 class="mayhen_personality_h2">Chuỗi Chức Năng Nhận Thức Lý Thuyết ({{ implode(' → ', $typeInfo['function_stack']) }})</h2>
            <p class="mayhen_personality_sub">
                Lưu ý: Đây là mô hình lý thuyết tham chiếu (Jung–Myers), không phải điểm đo trực tiếp vùng não hay tỷ lệ phần trăm từng chức năng.
            </p>
            <div class="mayhen_personality_grid_4">
                @foreach(['Dominant', 'Auxiliary', 'Tertiary', 'Inferior'] as $idx => $role)
                    @php $codeFn = $typeInfo['function_stack'][$idx] ?? ''; $fn = $cognitiveFunctions[$codeFn] ?? null; @endphp
                    <div class="mayhen_personality_card">
                        <div style="font-size:0.78rem; color:var(--mayhen-personality-muted);">{{ $role }}</div>
                        <div class="mayhen_personality_type_code">{{ $codeFn }}</div>
                        <p style="font-size:0.86rem; line-height:1.55; margin:6px 0 0;">{{ $fn['definition'] ?? '' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <h2 class="mayhen_personality_h2">Các nhóm gần kề (Best-Fit)</h2>
            <div class="mayhen_personality_grid_4">
                @foreach($adjacentInfos as $adjCode => $adj)
                    <a href="{{ route('personality.type', ['typeCode' => strtolower($adjCode)]) }}" class="mayhen_personality_type_tile">
                        <div class="mayhen_personality_type_code">{{ $adjCode }}</div>
                        <div style="font-weight:700; color:var(--mayhen-personality-crimson);">{{ $adj['art_title'] }}</div>
                        <div style="font-size:0.84rem; color:var(--mayhen-personality-muted); margin-top:4px;">{{ $adj['vietnamese_name'] }}</div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
</main>
@endsection
