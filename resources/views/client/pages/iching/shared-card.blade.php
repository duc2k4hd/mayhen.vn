@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $primary = $analysis['primary'];
    $changed = $analysis['changed'];
    $guidance = $analysis['guidance_state'] ?? ['label' => 'Cân bằng', 'tone' => 'balanced', 'description' => ''];
    $moving = $analysis['moving_lines'] ?? [];

    $renderSvg = function (string $code, array $movingPositions = [], ?array $lineValues = null): string {
        $rows = [];
        for ($i = 5; $i >= 0; $i--) {
            $pos = $i + 1;
            $y = (5 - $i) * 16 + 4;
            $isYang = ($code[$i] ?? '1') === '1';
            $isMoving = in_array($pos, $movingPositions, true);
            $fill = $isMoving ? '#85182A' : '#2A211B';
            $val = is_array($lineValues) ? (int) ($lineValues[$i] ?? 7) : ($isYang ? 7 : 8);
            if ($isYang) {
                $rows[] = '<rect x="4" y="'.$y.'" width="88" height="9" rx="3" fill="'.$fill.'" />';
            } else {
                $rows[] = '<rect x="4" y="'.$y.'" width="38" height="9" rx="3" fill="'.$fill.'" />';
                $rows[] = '<rect x="54" y="'.$y.'" width="38" height="9" rx="3" fill="'.$fill.'" />';
            }
            if ($isMoving) {
                $sym = $val === 9 ? '○' : '×';
                $rows[] = '<text x="103" y="'.($y + 8.5).'" font-size="11" font-weight="700" fill="#85182A" text-anchor="middle">'.$sym.'</text>';
            }
        }
        return '<svg viewBox="0 0 116 102" width="114" height="100" aria-hidden="true">'.implode('', $rows).'</svg>';
    };
@endphp

@section('title', 'Thẻ Chiêm Nghiệm Quẻ ' . $primary['name'])
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/iching.css') }}">
@endpush

@section('content')
<div class="mayhen_iching_page mayhen_iching_page_padded">
    <div class="mayhen_iching_container">
        <div class="mayhen_iching_shared_shell">
            <div style="text-align:center; margin-bottom:20px;">
                <span class="mayhen_iching_kicker">☯ Thẻ Chiêm Nghiệm Mây Hẹn Dịch Tâm • Đã Ẩn Thông Tin Riêng Tư</span>
                <h1 class="mayhen_iching_result_headline" style="margin-top:8px;">
                    Quẻ #{{ $primary['no'] }}: {{ $primary['name'] }} ({{ $primary['hanzi'] }})
                    @if($changed)
                        &rarr; {{ $changed['name'] }}
                    @endif
                </h1>
                <p style="font-size:13px; color:#6A635B; margin:0;">
                    Chủ đề: <strong>{{ $topicLabel }}</strong> • Trạng thái hướng dẫn: <strong>{{ $guidance['label'] }}</strong>
                </p>
            </div>

            <div class="mayhen_iching_triad_grid" style="grid-template-columns: repeat({{ $changed ? 2 : 1 }}, 1fr); max-width:560px; margin:0 auto 22px;">
                <div class="mayhen_iching_hex_card is-primary">
                    <span class="mayhen_iching_hex_role">Quẻ Chủ</span>
                    <div class="mayhen_iching_hex_svg_wrap">{!! $renderSvg($primary['code'], $moving, $analysis['lines'] ?? null) !!}</div>
                    <h3>{{ $primary['name'] }} ({{ $primary['hanzi'] }})</h3>
                    <p style="font-size:12.5px; color:#4A4038; margin:0;"><em>“{{ $primary['judgment'] }}”</em></p>
                </div>

                @if($changed)
                    <div class="mayhen_iching_hex_card">
                        <span class="mayhen_iching_hex_role">Quẻ Biến</span>
                        <div class="mayhen_iching_hex_svg_wrap">{!! $renderSvg($changed['code'], []) !!}</div>
                        <h3>{{ $changed['name'] }} ({{ $changed['hanzi'] }})</h3>
                        <p style="font-size:12.5px; color:#4A4038; margin:0;">{{ $changed['meaning_short'] }}</p>
                    </div>
                @endif
            </div>

            <div class="mayhen_iching_direct_box">
                <h3>Thông điệp chiêm nghiệm</h3>
                <p>{{ $interpretation['plain_summary'] ?? $primary['summary'] }}</p>
            </div>

            @if(!empty($interpretation['reflection_question']))
                <div class="mayhen_iching_reflection_box">
                    <strong>Câu hỏi tự quán chiếu</strong>
                    <p>“{{ $interpretation['reflection_question'] }}”</p>
                </div>
            @endif

            <div class="mayhen_iching_closing_note">
                “Một quẻ Dịch không quyết định tương lai của bạn, nhưng có thể giúp bạn nhìn lại hiện tại và cân nhắc những lựa chọn phía trước.”
            </div>

            <div class="mayhen_iching_btn_row mayhen_iching_btn_row_center" style="margin-top:20px;">
                <a href="{{ route('iching.index') }}" class="mayhen_iching_btn_primary">
                    ☯ Tự Gieo Một Quẻ Cho Riêng Bạn (Miễn Phí)
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
