@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $primary = $analysis['primary'];
    $mutual = $analysis['mutual'];
    $changed = $analysis['changed'];
    $tiYong = $analysis['ti_yong'];
    $moving = $analysis['moving_lines'] ?? [];

    $renderSvg = function (string $code, array $movingPositions = []): string {
        $rows = [];
        for ($i = 5; $i >= 0; $i--) {
            $pos = $i + 1;
            $y = (5 - $i) * 16 + 4;
            $isYang = ($code[$i] ?? '1') === '1';
            $isMoving = in_array($pos, $movingPositions, true);
            $fill = $isMoving ? '#85182A' : '#2A211B';
            if ($isYang) {
                $rows[] = '<rect x="4" y="'.$y.'" width="92" height="9" rx="3" fill="'.$fill.'" />';
            } else {
                $rows[] = '<rect x="4" y="'.$y.'" width="40" height="9" rx="3" fill="'.$fill.'" />';
                $rows[] = '<rect x="56" y="'.$y.'" width="40" height="9" rx="3" fill="'.$fill.'" />';
            }
            if ($isMoving) {
                $rows[] = '<circle cx="104" cy="'.($y + 4.5).'" r="3.5" fill="#85182A" />';
            }
        }
        return '<svg viewBox="0 0 112 102" width="108" height="98" aria-hidden="true">'.implode('', $rows).'</svg>';
    };
@endphp

@section('title', 'Quẻ ' . $primary['name'] . ' — Luận Giải Kinh Dịch | ' . $brandName)
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/iching.css') }}">
@endpush

@section('content')
<div class="mayhen_iching_page" style="padding: 36px 0 64px;">
    <div class="mayhen_iching_container">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
            <a href="{{ route('iching.index') }}" class="mayhen_iching_btn_outline" style="padding:8px 14px; font-size:13px;">
                &larr; Gieo Quẻ Mới
            </a>
            @auth
                <a href="{{ route('iching.history') }}" class="mayhen_iching_btn_gold" style="padding:8px 14px; font-size:13px;">
                    Sổ Quẻ Của Tôi
                </a>
            @endauth
        </div>

        <div class="mayhen_iching_result_shell">
            <div class="mayhen_iching_result_banner">
                <div style="flex:1; min-width:260px;">
                    <div style="font-size:12px; color:#6A635B; margin-bottom:6px;">
                        Chủ đề: <strong>{{ $topicLabel }}</strong> • Gieo lúc {{ $analysis['cast_at'] }}
                        @if($reading->ai_powered)
                            • <strong style="color:#1E4D3B;">✦ AI Luận Giải 4 Tầng</strong>
                        @endif
                    </div>
                    <h1 class="mayhen_iching_result_headline">{{ $interpretation['headline'] ?? ('Quẻ ' . $primary['name']) }}</h1>
                    <p style="margin:0; font-size:14.5px; color:#3F3730;">
                        <strong>Câu hỏi khởi tâm:</strong> “{{ $reading->question }}”
                    </p>
                </div>
                <div class="mayhen_iching_score_ring">
                    <small>Cát Khí Thể–Dụng</small>
                    <strong>{{ $tiYong['auspicious_score'] ?? 75 }}/100</strong>
                    <small>{{ $tiYong['relation'] ?? '' }} ({{ $tiYong['verdict'] ?? '' }})</small>
                </div>
            </div>

            <div class="mayhen_iching_triad_grid">
                <div class="mayhen_iching_hex_card is-primary">
                    <span class="mayhen_iching_hex_role">1. Quẻ Chủ (Hiện Tại)</span>
                    <div class="mayhen_iching_hex_svg_wrap">{!! $renderSvg($primary['code'], $moving) !!}</div>
                    <h3>Quẻ {{ $primary['no'] }}: {{ $primary['name'] }} ({{ $primary['hanzi'] }})</h3>
                    <span class="mayhen_iching_level_pill">{{ $primary['level'] }}</span>
                    <p style="font-size:12px; color:#6A635B; margin:0 0 6px;">{{ $primary['trigram_pair_label'] }}</p>
                    <p style="font-size:12.5px; color:#3F3730; margin:0;"><em>“{{ $primary['judgment'] }}”</em></p>
                </div>

                <div class="mayhen_iching_hex_card">
                    <span class="mayhen_iching_hex_role">2. Quẻ Hỗ (Nội Tình)</span>
                    <div class="mayhen_iching_hex_svg_wrap">{!! $renderSvg($mutual['code'], []) !!}</div>
                    <h3>Quẻ {{ $mutual['no'] }}: {{ $mutual['name'] }} ({{ $mutual['hanzi'] }})</h3>
                    <span class="mayhen_iching_level_pill">{{ $mutual['level'] }}</span>
                    <p style="font-size:12.5px; color:#5C5349; margin:0;">{{ $mutual['meaning_short'] }}</p>
                </div>

                <div class="mayhen_iching_hex_card">
                    <span class="mayhen_iching_hex_role">3. Quẻ Biến (Tương Lai)</span>
                    @if($changed)
                        <div class="mayhen_iching_hex_svg_wrap">{!! $renderSvg($changed['code'], []) !!}</div>
                        <h3>Quẻ {{ $changed['no'] }}: {{ $changed['name'] }} ({{ $changed['hanzi'] }})</h3>
                        <span class="mayhen_iching_level_pill">{{ $changed['level'] }}</span>
                        <p style="font-size:12.5px; color:#5C5349; margin:0;">{{ $changed['meaning_short'] }}</p>
                    @else
                        <div style="padding:28px 10px; color:#7A6E62; font-size:13px;">
                            <strong>Quẻ Tĩnh (0 Hào Động)</strong><br>
                            Lấy Quái Từ Quẻ Chủ làm trục luận chính.
                        </div>
                    @endif
                </div>
            </div>

            <div class="mayhen_iching_layers_grid">
                <div class="mayhen_iching_layer_card">
                    <span class="mayhen_iching_layer_badge">Tầng 1 • Tượng Quẻ &amp; Thể – Dụng</span>
                    <h4>Bản Chất &amp; Ngũ Hành Sinh Khắc</h4>
                    <p>{{ $interpretation['layer_1_image'] ?? '' }}</p>
                </div>
                <div class="mayhen_iching_layer_card">
                    <span class="mayhen_iching_layer_badge">Tầng 2 • Hào Động</span>
                    <h4>Điểm Chuyển Biến Then Chốt</h4>
                    <p>{{ $interpretation['layer_2_moving_lines'] ?? '' }}</p>
                </div>
                <div class="mayhen_iching_layer_card">
                    <span class="mayhen_iching_layer_badge">Tầng 3 • Quẻ Hỗ &amp; Quẻ Biến</span>
                    <h4>Nội Tình &amp; Xu Hướng Tương Lai</h4>
                    <p>{{ $interpretation['layer_3_evolution'] ?? '' }}</p>
                </div>
                <div class="mayhen_iching_layer_card">
                    <span class="mayhen_iching_layer_badge">Tầng 4 • Ứng Kỳ &amp; Thời Điểm</span>
                    <h4>Nhịp Chuyển Biến Thuận Thời</h4>
                    <p>{{ $interpretation['layer_4_timing_actions'] ?? '' }}</p>
                </div>
            </div>

            @if(!empty($interpretation['action_steps']))
                <div class="mayhen_iching_actions_box">
                    <h4>✦ 3 Hành Động Gợi Ý Thực Tế</h4>
                    <ul>
                        @foreach($interpretation['action_steps'] as $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!empty($interpretation['caution_note']))
                <div class="mayhen_iching_caution_box">
                    <strong>Lưu ý thận trọng:</strong> {{ $interpretation['caution_note'] }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
