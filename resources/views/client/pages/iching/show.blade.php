@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $primary = $analysis['primary'];
    $mutual = $analysis['mutual'];
    $changed = $analysis['changed'];
    $tiYong = $analysis['ti_yong'] ?? [];
    $chuHy = $analysis['chu_hy'] ?? [];
    $guidance = $analysis['guidance_state'] ?? ['label' => 'Cân bằng', 'tone' => 'balanced', 'description' => ''];
    $moving = $analysis['moving_lines'] ?? [];
    $lineDetails = $analysis['line_details'] ?? [];
    $trace = $analysis['calculation_trace'] ?? [];
    $checklist = is_array($reading->action_checklist_json) && $reading->action_checklist_json !== []
        ? $reading->action_checklist_json
        : array_map(fn ($s) => ['text' => $s, 'done' => false], $interpretation['action_steps'] ?? []);
    $cautionNotes = $interpretation['caution_notes'] ?? (! empty($interpretation['caution_note']) ? [$interpretation['caution_note']] : []);
    $plan = $interpretation['time_horizon_plan'] ?? [];

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

@section('title', 'Quẻ #' . $primary['no'] . ' ' . $primary['name'] . ' — Sổ Quẻ Riêng Tư | ' . $brandName)
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/iching.css') }}">
@endpush

@section('content')
<div class="mayhen_iching_page mayhen_iching_page_padded" id="mayhenIchingShowApp">
    <div class="mayhen_iching_container">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
            <a href="{{ route('iching.index') }}" class="mayhen_iching_btn_outline mayhen_iching_btn_sm">&larr; Gieo Quẻ Mới</a>
            <div class="mayhen_iching_btn_row">
                <span class="mayhen_iching_pill_btn">🔒 Lá quẻ riêng tư của bạn</span>
                <a href="{{ route('iching.history') }}" class="mayhen_iching_btn_gold mayhen_iching_btn_sm">Sổ Quẻ Của Tôi</a>
            </div>
        </div>

        <div class="mayhen_iching_result_shell">
            <div class="mayhen_iching_result_banner">
                <div style="flex:1; min-width:260px;">
                    <div style="font-size:12px; color:#6A635B; margin-bottom:6px;">
                        {{ $analysis['method_label'] }} • Chủ đề: <strong>{{ $topicLabel }}</strong>
                        @if($reading->sub_topic)
                            ({{ $reading->sub_topic }})
                        @endif
                        • {{ $analysis['cast_at'] }}
                        @if($reading->ai_powered)
                            • <strong style="color:#1E4D3B;">✦ AI Cá Nhân Hóa</strong>
                        @endif
                    </div>
                    <h1 class="mayhen_iching_result_headline">{{ $interpretation['headline'] ?? ('Quẻ ' . $primary['name']) }}</h1>
                    <p style="margin:0; font-size:14.5px; color:#3F3730;">
                        <strong>Câu hỏi khởi tâm:</strong> “{{ $reading->question }}”
                    </p>
                </div>
                <div class="mayhen_iching_guidance_card tone-{{ $guidance['tone'] ?? 'balanced' }}">
                    <span class="mayhen_iching_guidance_kicker">Trạng thái hướng dẫn</span>
                    <strong class="mayhen_iching_guidance_label">{{ $guidance['label'] }}</strong>
                    <span class="mayhen_iching_guidance_desc">{{ $guidance['description'] }}</span>
                </div>
            </div>

            <div class="mayhen_iching_depth_tabs" role="tablist">
                <button type="button" class="mayhen_iching_depth_tab is-active" data-depth="full">Luận giải đầy đủ</button>
                <button type="button" class="mayhen_iching_depth_tab" data-depth="quick">Đọc nhanh (30 giây)</button>
                <button type="button" class="mayhen_iching_depth_tab" data-depth="deep">Nghiên cứu &amp; Minh bạch phép tính</button>
            </div>

            {{-- MỨC 1: ĐỌC NHANH --}}
            <div class="mayhen_iching_depth_panel" data-depth-panel="quick">
                <div class="mayhen_iching_direct_box">
                    <h3>Tóm tắt cốt lõi</h3>
                    <p>{{ $interpretation['plain_summary'] ?? $interpretation['direct_answer'] ?? $primary['summary'] }}</p>
                </div>
                @if(!empty($interpretation['reflection_question']))
                    <div class="mayhen_iching_reflection_box">
                        <strong>Câu hỏi tự quán chiếu dành cho bạn</strong>
                        <p>“{{ $interpretation['reflection_question'] }}”</p>
                    </div>
                @endif
            </div>

            {{-- MỨC 2: ĐẦY ĐỦ --}}
            <div class="mayhen_iching_depth_panel is-active" data-depth-panel="full">
                @if(!empty($interpretation['direct_answer']))
                    <div class="mayhen_iching_direct_box">
                        <h3>Trả lời trực tiếp câu hỏi của bạn</h3>
                        <p>{{ $interpretation['direct_answer'] }}</p>
                    </div>
                @endif

                <div class="mayhen_iching_triad_grid">
                    <div class="mayhen_iching_hex_card is-primary">
                        <span class="mayhen_iching_hex_role">1. Quẻ Chủ (Hiện Tại)</span>
                        <div class="mayhen_iching_hex_svg_wrap">{!! $renderSvg($primary['code'], $moving, $analysis['lines'] ?? null) !!}</div>
                        <h3>Quẻ #{{ $primary['no'] }}: {{ $primary['name'] }} ({{ $primary['hanzi'] }})</h3>
                        <span class="mayhen_iching_level_pill">{{ $primary['guidance_state_ref'] ?? $primary['level'] }}</span>
                        <p style="font-size:12px; color:#6A635B; margin:0 0 6px;">{{ $primary['trigram_pair_label'] }}</p>
                        <p style="font-size:12.5px; color:#3F3730; margin:0;"><em>“{{ $primary['judgment'] }}”</em></p>
                    </div>

                    <div class="mayhen_iching_hex_card">
                        <span class="mayhen_iching_hex_role">2. Quẻ Hỗ ({{ !empty($analysis['mutual_meta']['is_applicable_traditionally']) ? 'Nội Tình Mai Hoa' : 'Tham Khảo Nội Tình' }})</span>
                        <div class="mayhen_iching_hex_svg_wrap">{!! $renderSvg($mutual['code'], []) !!}</div>
                        <h3>Quẻ #{{ $mutual['no'] }}: {{ $mutual['name'] }} ({{ $mutual['hanzi'] }})</h3>
                        <span class="mayhen_iching_level_pill">{{ $mutual['guidance_state_ref'] ?? $mutual['level'] }}</span>
                        <p style="font-size:12.5px; color:#5C5349; margin:0;">{{ $mutual['meaning_short'] }}</p>
                    </div>

                    <div class="mayhen_iching_hex_card">
                        <span class="mayhen_iching_hex_role">3. Quẻ Biến (Hướng Chuyển Hóa)</span>
                        @if($changed)
                            <div class="mayhen_iching_hex_svg_wrap">{!! $renderSvg($changed['code'], []) !!}</div>
                            <h3>Quẻ #{{ $changed['no'] }}: {{ $changed['name'] }} ({{ $changed['hanzi'] }})</h3>
                            <span class="mayhen_iching_level_pill">{{ $changed['guidance_state_ref'] ?? $changed['level'] }}</span>
                            <p style="font-size:12.5px; color:#5C5349; margin:0;">{{ $changed['meaning_short'] }}</p>
                        @else
                            <div style="padding:28px 10px; color:#7A6E62; font-size:13px;">
                                <strong>Quẻ Tĩnh (0 Hào Động)</strong><br>
                                Lấy Quái Từ và Đại Tượng Quẻ Chủ làm trục luận chính.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mayhen_iching_layers_grid">
                    <div class="mayhen_iching_layer_card">
                        <span class="mayhen_iching_layer_badge">Tầng 1 • Tượng Quẻ {{ !empty($tiYong['applicable']) ? '& Thể – Dụng' : '& Quái Từ' }}</span>
                        <h4>Bản Chất Tình Thế Hiện Tại</h4>
                        <p>{{ $interpretation['layer_1_image'] ?? '' }}</p>
                    </div>
                    <div class="mayhen_iching_layer_card">
                        <span class="mayhen_iching_layer_badge">Tầng 2 • Hào Động ({{ count($moving) > 0 ? 'Hào ' . implode(', ', $moving) : 'Quẻ Tĩnh' }})</span>
                        <h4>Điểm Chuyển Biến Theo Chu Hy</h4>
                        <p>{{ $interpretation['layer_2_moving_lines'] ?? '' }}</p>
                    </div>
                    <div class="mayhen_iching_layer_card">
                        <span class="mayhen_iching_layer_badge">Tầng 3 • Quẻ Hỗ &amp; Quẻ Biến</span>
                        <h4>Tiến Trình Nội Tại &amp; Chuyển Hóa</h4>
                        <p>{{ $interpretation['layer_3_evolution'] ?? '' }}</p>
                    </div>
                    <div class="mayhen_iching_layer_card">
                        <span class="mayhen_iching_layer_badge">Tầng 4 • Nhịp Điệu &amp; Hành Động</span>
                        <h4>Cách Ứng Xử Thuận Thời</h4>
                        <p>{{ $interpretation['layer_4_timing_actions'] ?? '' }}</p>
                    </div>
                </div>

                @if(!empty($plan['today']))
                    <div class="mayhen_iching_horizon_grid">
                        <div class="mayhen_iching_horizon_card"><strong>Hôm nay</strong><p>{{ $plan['today'] }}</p></div>
                        <div class="mayhen_iching_horizon_card"><strong>7 ngày tới</strong><p>{{ $plan['next_7_days'] ?? '' }}</p></div>
                        <div class="mayhen_iching_horizon_card"><strong>Nhìn lại sau 30 ngày</strong><p>{{ $plan['review_30_days'] ?? '' }}</p></div>
                    </div>
                @endif

                @if(!empty($checklist))
                    <div class="mayhen_iching_actions_box">
                        <h4>✦ 3 Hành Động Gợi Ý Thực Tế (Tick để lưu tiến độ)</h4>
                        <ul class="mayhen_iching_checklist">
                            @foreach($checklist as $item)
                                <li class="{{ !empty($item['done']) ? 'is-done' : '' }}">
                                    <input type="checkbox"
                                           class="mayhen_iching_check_input"
                                           data-step-text="{{ $item['text'] ?? '' }}"
                                           {{ !empty($item['done']) ? 'checked' : '' }}>
                                    <span>{{ $item['text'] ?? '' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($cautionNotes))
                    <div class="mayhen_iching_caution_box">
                        <strong>Điều cần lưu ý:</strong>
                        <ul>
                            @foreach($cautionNotes as $note)
                                <li>{{ $note }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($interpretation['reflection_question']))
                    <div class="mayhen_iching_reflection_box">
                        <strong>Câu hỏi tự quán chiếu</strong>
                        <p>“{{ $interpretation['reflection_question'] }}”</p>
                    </div>
                @endif
            </div>

            {{-- MỨC 3: NGHIÊN CỨU CHUYÊN SÂU --}}
            <div class="mayhen_iching_depth_panel" data-depth-panel="deep">
                <div class="mayhen_iching_interactive_lines">
                    <h4 style="margin:0 0 10px; font-size:15px;">Bấm vào từng hào của Quẻ Chủ #{{ $primary['no'] }} {{ $primary['name'] }} để xem Hào Từ</h4>
                    <div class="mayhen_iching_interactive_lines_grid">
                        <div class="mayhen_iching_line_buttons">
                            @foreach($lineDetails as $l)
                                <button type="button"
                                        class="mayhen_iching_line_btn {{ !empty($l['is_moving']) ? 'is-moving' : '' }}"
                                        data-line-pos="{{ $l['position'] }}">
                                    <span><strong>{{ $l['label'] }}</strong> (Hào {{ $l['position'] }})</span>
                                    <span>{{ $l['type_label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                        <div class="mayhen_iching_line_inspector"></div>
                    </div>
                </div>

                <div class="mayhen_iching_trace_box">
                    <h4>Minh bạch thuật toán ({{ $analysis['rule_version'] }} • KB {{ $analysis['kb_version'] }})</h4>
                    <div><strong>Phương pháp:</strong> {{ $trace['school'] ?? $analysis['method_label'] }}</div>
                    @if(!empty($trace['rule_note']))
                        <div><strong>Quy tắc áp dụng:</strong> {{ $trace['rule_note'] }}</div>
                    @endif
                    @if(!empty($trace['lunar']['lunar_date_label']))
                        <div><strong>Âm lịch Việt Nam (UTC+7):</strong> {{ $trace['lunar']['lunar_date_label'] }} • Giờ {{ $trace['lunar']['hour_branch_name'] }}</div>
                    @endif
                    @if(!empty($trace['steps']))
                        <ul style="margin:6px 0 0; padding-left:18px;">
                            @foreach($trace['steps'] as $s)
                                <li>{{ $s }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <div style="margin-top:8px;"><strong>Quẻ Hỗ:</strong> {{ $analysis['mutual_meta']['note'] ?? '' }}</div>
                    <div style="margin-top:6px;"><strong>Quy tắc Chu Hy:</strong> {{ $chuHy['summary_text'] ?? $analysis['moving_rule'] }}</div>
                    @if(!empty($tiYong['applicable']))
                        <div style="margin-top:8px;">
                            <strong>Thể – Dụng Mai Hoa:</strong> {{ $tiYong['ti_position'] }} ({{ $tiYong['ti_trigram']['name'] }} – {{ $tiYong['ti_trigram']['element'] }}) vs {{ $tiYong['yong_position'] }} ({{ $tiYong['yong_trigram']['name'] }} – {{ $tiYong['yong_trigram']['element'] }}) &rarr; <strong>{{ $tiYong['relation'] }}</strong>. {{ $tiYong['mutual_note'] ?? '' }}
                        </div>
                    @else
                        <div style="margin-top:8px;"><strong>Trường phái:</strong> {{ $tiYong['school_note'] ?? '' }}</div>
                    @endif
                </div>
            </div>

            {{-- HỎI SÂU VỀ QUẺ & NHẬT KÝ NHÌN LẠI --}}
            <div class="mayhen_iching_companion_grid">
                <div class="mayhen_iching_companion_card">
                    <h3>Hỏi Sâu Về Lá Quẻ Này</h3>
                    <p style="font-size:12.5px; color:#5C5349; margin:0 0 12px;">Đặt câu hỏi đào sâu bám sát lá quẻ gốc (tối đa 8 lượt) thay vì gieo quẻ mới.</p>
                    <div class="mayhen_iching_followup_list" data-iching-followup-list>
                        @foreach($reading->followups as $fu)
                            <div class="mayhen_iching_followup_item">
                                <div class="mayhen_iching_followup_q">Hỏi sâu: {{ $fu->question }}</div>
                                <div>{{ $fu->answer }}</div>
                            </div>
                        @endforeach
                    </div>
                    <form data-iching-followup-form>
                        <textarea name="question" class="mayhen_iching_textarea" rows="2" maxlength="320" placeholder="VD: Tôi nên ưu tiên hành động nào trước trong tuần này?"></textarea>
                        <button type="submit" class="mayhen_iching_btn_gold mayhen_iching_btn_sm" style="margin-top:8px;">Hỏi Sâu Về Lá Quẻ Này</button>
                    </form>
                </div>

                <div class="mayhen_iching_companion_card">
                    <h3>Nhật Ký Nhìn Lại (7 / 30 / 90 ngày)</h3>
                    <p style="font-size:12.5px; color:#5C5349; margin:0 0 12px;">Ghi nhận chuyển biến thực tế sau khi thực hiện các gợi ý của lá quẻ.</p>
                    <div class="mayhen_iching_followup_list" data-iching-journal-list>
                        @foreach($reading->journals as $j)
                            <div class="mayhen_iching_followup_item">
                                <div class="mayhen_iching_followup_q">Mốc {{ $j->milestone_days }} ngày • {{ $j->created_at?->format('H:i • d/m/Y') }}</div>
                                @if($j->actions_taken)<div><strong>Đã làm:</strong> {{ $j->actions_taken }}</div>@endif
                                @if($j->progress_note)<div><strong>Chuyển biến:</strong> {{ $j->progress_note }}</div>@endif
                                @if($j->lessons_learned)<div><strong>Bài học:</strong> {{ $j->lessons_learned }}</div>@endif
                            </div>
                        @endforeach
                    </div>
                    <form data-iching-journal-form>
                        <select name="milestone_days" class="mayhen_iching_select" style="margin-bottom:8px; padding:8px 10px; font-size:13px;">
                            <option value="7">Mốc nhìn lại sau 7 ngày</option>
                            <option value="30">Mốc nhìn lại sau 30 ngày</option>
                            <option value="90">Mốc nhìn lại sau 90 ngày</option>
                            <option value="0">Ghi chú ngay hôm nay</option>
                        </select>
                        <input type="text" name="actions_taken" class="mayhen_iching_input" style="margin-bottom:8px; font-size:13px;" placeholder="Bạn đã làm điều gì cụ thể?">
                        <input type="text" name="progress_note" class="mayhen_iching_input" style="margin-bottom:8px; font-size:13px;" placeholder="Điều gì đã chuyển biến rõ hơn?">
                        <input type="text" name="lessons_learned" class="mayhen_iching_input" style="margin-bottom:8px; font-size:13px;" placeholder="Bài học bạn rút ra cho mình">
                        <button type="submit" class="mayhen_iching_btn_outline mayhen_iching_btn_sm">Lưu Nhật Ký Chiêm Nghiệm</button>
                    </form>
                </div>
            </div>

            {{-- PHẢN HỒI 4 MỨC, YÊU THÍCH, CHIA SẺ ẨN DANH & LUẬN LẠI AI --}}
            <div class="mayhen_iching_feedback_bar">
                <div style="display:flex; flex-wrap:wrap; align-items:center; gap:7px;">
                    <span style="font-size:12.5px; font-weight:700; color:#4A4038;">Mức độ hữu ích:</span>
                    @foreach(['very_helpful' => 'Rất hữu ích', 'helpful' => 'Hữu ích', 'neutral' => 'Bình thường', 'not_relevant' => 'Chưa sát'] as $rKey => $rLabel)
                        <button type="button"
                                class="mayhen_iching_pill_btn {{ $reading->feedback_rating === $rKey ? 'is-active' : '' }}"
                                data-iching-feedback="{{ $rKey }}">{{ $rLabel }}</button>
                    @endforeach
                </div>
                <div class="mayhen_iching_btn_row">
                    <button type="button"
                            class="mayhen_iching_pill_btn"
                            data-iching-favorite-btn
                            data-favorite="{{ $reading->is_favorite ? '1' : '0' }}">
                        {{ $reading->is_favorite ? '★ Đã lưu Yêu thích' : '☆ Đánh dấu Yêu thích' }}
                    </button>
                    <button type="button" class="mayhen_iching_pill_btn" data-iching-share-btn>🔗 Tạo Thẻ Chia Sẻ (Ẩn Câu Hỏi)</button>
                    <button type="button"
                            class="mayhen_iching_pill_btn"
                            data-iching-revoke-btn
                            style="{{ $shareUrl ? '' : 'display:none;' }} color:#B91C1C;">Thu hồi chia sẻ</button>
                    @if(! $reading->ai_powered)
                        <button type="button" class="mayhen_iching_pill_btn is-active" data-iching-reinterpret-btn>✦ Thử Luận Giải Lại Bằng AI</button>
                    @endif
                </div>
            </div>

            <div data-iching-share-output style="{{ $shareUrl ? '' : 'display:none;' }} margin-top:10px; font-size:12.5px; color:#1E4D3B; padding:10px 14px; background:#F1F7F4; border-radius:10px;">
                @if($shareUrl)
                    Link Thẻ Chia Sẻ đang bật (đã ẩn câu hỏi riêng tư): <a href="{{ $shareUrl }}" target="_blank" rel="noopener">{{ $shareUrl }}</a>
                @endif
            </div>

            @if(!empty($interpretation['ecosystem_suggestions']))
                <div class="mayhen_iching_ecosystem_grid">
                    @foreach($interpretation['ecosystem_suggestions'] as $eco)
                        <div class="mayhen_iching_ecosystem_card">
                            <h4>{{ $eco['title'] }}</h4>
                            <p>{{ $eco['description'] }}</p>
                            <a href="{{ $eco['url'] }}" class="mayhen_iching_status_link">{{ $eco['cta'] }} &rarr;</a>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mayhen_iching_closing_note">
                “{{ $interpretation['closing_note'] ?? 'Một quẻ Dịch không quyết định tương lai của bạn, nhưng có thể giúp bạn nhìn lại hiện tại và cân nhắc những lựa chọn phía trước.' }}”
            </div>
        </div>
    </div>
</div>

<script id="mayhenIchingShowLinesJson" type="application/json">@json($lineDetails, JSON_UNESCAPED_UNICODE)</script>
<script id="mayhenIchingShowEndpointsJson" type="application/json">@json($endpoints, JSON_UNESCAPED_UNICODE)</script>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/iching.js') }}"></script>
@endpush
