@extends('client.layouts.master')

@section('title', 'Triển Lãm 16 Nhóm Tính Cách & Hình Tượng Nghệ Thuật')
@section('meta_description', 'Khám phá trọn bộ 16 nhóm tính cách và 16 hình tượng nghệ thuật độc quyền của Mây Hẹn (The Art of Personality) với chế độ tương tác 3D và 2D Editorial.')

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
            <span class="mayhen_personality_kicker">Contemporary Museum • 16 Portraits</span>
            <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap;">
                <div>
                    <h1 class="mayhen_personality_h1">Triển Lãm 16 Nhóm Tính Cách</h1>
                    <p class="mayhen_personality_lead" style="margin-bottom:0;">
                        Mỗi hình tượng biểu trưng bằng chất liệu nghệ thuật (đá cẩm thạch, đồng cổ, ngọc, hoa sen, lăng kính) — không gắn giới tính, không sao chép nhân vật bên thứ ba.
                    </p>
                </div>
                <button type="button" id="mayhen_personality_3d_toggle" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">
                    Chế độ 3D Tương tác
                </button>
            </div>
        </div>
    </section>

    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <div class="mayhen_personality_grid_4">
                @foreach($types as $code => $t)
                    <div class="mayhen_personality_sculpture_card" data-mode="3d">
                        @include('client.pages.personality.partials.sculpture-3d', [
                            'typeInfo' => $t,
                            'canvasHeight' => 190,
                            'compact' => true,
                            'fallbackSvg' => $artworks[$code] ?? '',
                        ])
                        <a href="{{ route('personality.type', ['typeCode' => strtolower($code)]) }}" style="text-decoration:none; color:inherit; display:block; margin-top:10px;">
                            <div class="mayhen_personality_type_code">{{ $code }}</div>
                            <div style="font-weight:700; color:var(--mayhen-personality-crimson);">{{ $t['art_title'] }}</div>
                            <div style="font-size:0.84rem; color:var(--mayhen-personality-muted); margin-top:4px;">{{ $t['vietnamese_name'] }}</div>
                            <div style="font-size:0.78rem; color:var(--mayhen-personality-slate); margin-top:6px;">{{ $t['symbol_name'] }} • Xem chi tiết →</div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @include('client.pages.personality.partials.seo-pillar', ['seoPillar' => $seoPillar ?? null])
</main>
@endsection
