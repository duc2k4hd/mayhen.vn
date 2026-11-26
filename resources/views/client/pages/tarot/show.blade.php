@extends('client.layouts.master')

@section('title', 'Kết Quả Trải Bài Tarot — ' . ($spread['name'] ?? 'Mây Hẹn'))
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/tarot.css') }}?v={{ filemtime(public_path('assets/client/css/pages/tarot.css')) }}">
@endpush

@section('content')
@php
    $cards = $analysis['cards'] ?? [];
    $layout = $spread['layout'] ?? 'row_3';
    $cardInterpById = collect($interpretation['card_interpretations'] ?? [])->keyBy('card_id');
    $elementCounts = $analysis['stats']['elements'] ?? ['Lửa' => 0, 'Nước' => 0, 'Khí' => 0, 'Đất' => 0];
    $totalCards = max(1, count($cards));
    $summaryText = (string) ($interpretation['summary'] ?? $interpretation['core_answer'] ?? '');
    $actionsList = is_array($interpretation['practical_actions'] ?? null) ? $interpretation['practical_actions'] : [];
@endphp

<div class="mayhen_tarot_page">
    <!-- 1. Editorial Result Header -->
    <section class="mayhen_tarot_result_hero">
        <div class="mayhen_tarot_container">
            <div class="mayhen_tarot_meta_row">
                <span class="mayhen_tarot_badge">✦ {{ $spread['name'] ?? 'Trải Bài Tarot' }} ({{ count($cards) }} lá)</span>
                <span class="mayhen_tarot_badge">{{ $categoryLabel }}</span>
                <span class="mayhen_tarot_badge">
                    {{ $reading->orientation_mode === 'upright_only' ? 'Chế độ: Chỉ Lá Xuôi' : 'Chế độ: Xuôi & Ngược (50/50)' }}
                </span>
                <span class="mayhen_tarot_badge">
                    {{ $reading->ai_powered ? '✦ AI 6 Tầng · Chuẩn RWS' : '✦ Luận giải chuẩn hóa RWS' }}
                </span>
                <span class="mayhen_tarot_badge">{{ $reading->created_at?->format('H:i · d/m/Y') }}</span>
            </div>

            <h1 class="mayhen_tarot_title" style="font-size: clamp(24px, 3.2vw, 36px); margin-bottom: 10px;">
                {{ $interpretation['headline'] ?? ($spread['name'] ?? 'Bản Luận Giải Tarot') }}
            </h1>

            @if ($reading->question)
                <p class="mayhen_tarot_subtitle" style="margin: 0 0 10px; max-width: 820px;">
                    <strong>Câu hỏi của bạn:</strong> “{{ $reading->question }}”
                </p>
            @endif
        </div>
    </section>

    <div class="mayhen_tarot_container">
        <!-- 2. Interactive Spread Visual Board -->
        <div class="mayhen_tarot_board_wrap">
            @if ($layout === 'celtic_cross_10' && count($cards) === 10)
                <div class="mayhen_tarot_celtic_board">
                    <div class="mayhen_tarot_celtic_cross_zone">
                        <div></div>
                        <button type="button" class="mayhen_tarot_board_card mayhen_tarot_celtic_mini" data-tarot-focus-card="5">
                            <div class="mayhen_tarot_card_frame" data-tarot-card-art='@json($cards[4])'></div>
                            <div class="mayhen_tarot_slot_caption"><strong>5. {{ $cards[4]['position_label'] ?? '' }}</strong><br>{{ $cards[4]['name_en'] ?? '' }}</div>
                        </button>
                        <div></div>

                        <button type="button" class="mayhen_tarot_board_card mayhen_tarot_celtic_mini" data-tarot-focus-card="4">
                            <div class="mayhen_tarot_card_frame" data-tarot-card-art='@json($cards[3])'></div>
                            <div class="mayhen_tarot_slot_caption"><strong>4. {{ $cards[3]['position_label'] ?? '' }}</strong><br>{{ $cards[3]['name_en'] ?? '' }}</div>
                        </button>

                        <div class="mayhen_tarot_celtic_center_stack">
                            <button type="button" class="mayhen_tarot_board_card mayhen_tarot_celtic_mini mayhen_tarot_celtic_card1" data-tarot-focus-card="1" title="1. {{ $cards[0]['name_en'] ?? '' }}">
                                <div class="mayhen_tarot_card_frame" data-tarot-card-art='@json($cards[0])'></div>
                            </button>
                            <button type="button" class="mayhen_tarot_board_card mayhen_tarot_celtic_mini mayhen_tarot_celtic_card2" data-tarot-focus-card="2" title="2. {{ $cards[1]['name_en'] ?? '' }} (Cắt ngang 90°)">
                                <div class="mayhen_tarot_card_frame" data-tarot-card-art='@json($cards[1])'></div>
                            </button>
                        </div>

                        <button type="button" class="mayhen_tarot_board_card mayhen_tarot_celtic_mini" data-tarot-focus-card="6">
                            <div class="mayhen_tarot_card_frame" data-tarot-card-art='@json($cards[5])'></div>
                            <div class="mayhen_tarot_slot_caption"><strong>6. {{ $cards[5]['position_label'] ?? '' }}</strong><br>{{ $cards[5]['name_en'] ?? '' }}</div>
                        </button>

                        <div></div>
                        <button type="button" class="mayhen_tarot_board_card mayhen_tarot_celtic_mini" data-tarot-focus-card="3">
                            <div class="mayhen_tarot_card_frame" data-tarot-card-art='@json($cards[2])'></div>
                            <div class="mayhen_tarot_slot_caption"><strong>3. {{ $cards[2]['position_label'] ?? '' }}</strong><br>{{ $cards[2]['name_en'] ?? '' }}</div>
                        </button>
                        <div></div>
                    </div>

                    <div class="mayhen_tarot_celtic_staff_zone">
                        @foreach ([6, 7, 8, 9] as $staffIdx)
                            <button type="button" class="mayhen_tarot_board_card mayhen_tarot_celtic_mini" data-tarot-focus-card="{{ $staffIdx + 1 }}">
                                <div class="mayhen_tarot_card_frame" data-tarot-card-art='@json($cards[$staffIdx])'></div>
                                <div class="mayhen_tarot_slot_caption"><strong>{{ $staffIdx + 1 }}. {{ $cards[$staffIdx]['position_label'] ?? '' }}</strong><br>{{ $cards[$staffIdx]['name_en'] ?? '' }}</div>
                            </button>
                        @endforeach
                    </div>
                </div>
                <p style="text-align: center; font-size: 12px; color: rgba(231, 210, 163, 0.85); margin: 16px 0 0;">
                    ✦ Celtic Cross biến thể hiện đại (Rider–Waite–Smith): Lá số 2 đặt cắt ngang 90° trên lá số 1 nhưng vẫn giữ nguyên chiều {{ ! empty($cards[1]['is_reversed']) ? 'Ngược' : 'Xuôi' }} khi luận giải. Nhấn từng lá để xem chi tiết.
                </p>
            @else
                <div class="mayhen_tarot_board_row">
                    @foreach ($cards as $c)
                        @php $posNum = (int) ($c['position_index'] ?? $loop->iteration); @endphp
                        <button type="button" class="mayhen_tarot_board_card" data-tarot-focus-card="{{ $posNum }}">
                            <div class="mayhen_tarot_card_frame" data-tarot-card-art='@json($c)'></div>
                            <div class="mayhen_tarot_slot_caption">
                                <strong>{{ $posNum }}. {{ $c['position_label'] ?? '' }}</strong><br>
                                <span style="color: #E7D2A3;">{{ $c['name_en'] ?? '' }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- 3. Reading Depth Bar -->
        <div class="mayhen_tarot_depth_bar">
            <div class="mayhen_tarot_depth_tabs" role="tablist">
                <button type="button" class="mayhen_tarot_depth_tab" data-tarot-depth-tab="quick">Đọc nhanh (1 phút)</button>
                <button type="button" class="mayhen_tarot_depth_tab is-active" data-tarot-depth-tab="standard">Đầy đủ (6 tầng)</button>
                <button type="button" class="mayhen_tarot_depth_tab" data-tarot-depth-tab="deep">Chuyên sâu &amp; Biểu tượng</button>
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="{{ route('tarot.index') }}" class="mayhen_tarot_btn_outline_dark">+ Trải bài mới</a>
                @auth
                    <button type="button" id="tarotReinterpretBtn" class="mayhen_tarot_btn_outline_dark">↻ Luận giải lại bằng AI</button>
                @endauth
            </div>
        </div>

        <!-- 4. Editorial Report Body -->
        <div class="mayhen_tarot_report_grid">
            <div>
                <!-- Depth: Quick -->
                <div class="mayhen_tarot_depth_panel" data-tarot-depth-panel="quick">
                    <article class="mayhen_tarot_book_card">
                        <div class="mayhen_tarot_kicker">TÓM TẮT 1 PHÚT</div>
                        <h2>Thông điệp cốt lõi</h2>
                        <p style="font-size: 15.5px; line-height: 1.75; margin-bottom: 16px;">{{ $summaryText }}</p>
                        @if (! empty($actionsList))
                            <h3 style="font-size: 18px;">3 bước hành động ưu tiên</h3>
                            <ul class="mayhen_tarot_action_list">
                                @foreach ($actionsList as $act)
                                    <li>{{ $act }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </article>
                </div>

                <!-- Depth: Standard (Default) -->
                <div class="mayhen_tarot_depth_panel is-active" data-tarot-depth-panel="standard">
                    <article class="mayhen_tarot_book_card">
                        <div class="mayhen_tarot_kicker">TẦNG 1 · TỔNG QUAN TRẢI BÀI</div>
                        <h2>Bức tranh tổng thể &amp; Trả lời trọng tâm</h2>
                        <p style="font-size: 15.5px; line-height: 1.78; white-space: pre-line; margin: 0;">{{ $summaryText }}</p>
                    </article>

                    <article class="mayhen_tarot_book_card">
                        <div class="mayhen_tarot_kicker">TẦNG 2 · PHÂN TÍCH TỪNG VỊ TRÍ</div>
                        <h2>Ý nghĩa chi tiết từng lá bài</h2>
                        @foreach ($cards as $c)
                            @php
                                $posNum = (int) ($c['position_index'] ?? $loop->iteration);
                                $isRev = ! empty($c['is_reversed']);
                                $ci = $cardInterpById->get($c['card_id'] ?? '');
                            @endphp
                            <div class="mayhen_tarot_card_detail_row" data-card-pos="{{ $posNum }}">
                                <div>
                                    <div class="mayhen_tarot_card_frame" style="width: 112px; height: 186px;" data-tarot-card-art='@json($c)'></div>
                                </div>
                                <div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 6px;">
                                        <span class="mayhen_tarot_badge mayhen_tarot_badge_dark">Vị trí {{ $posNum }} · {{ $c['position_label'] ?? '' }}</span>
                                        <span class="mayhen_tarot_badge {{ $isRev ? 'mayhen_tarot_badge_reversed' : 'mayhen_tarot_badge_dark' }}">
                                            {{ $isRev ? 'Lá Ngược (Reversed)' : 'Lá Xuôi (Upright)' }}
                                        </span>
                                    </div>
                                    <h3 style="margin-bottom: 6px;">
                                        @if (! empty($c['slug']))
                                            <a href="{{ route('tarot.card_detail', $c['slug']) }}" style="color: inherit; text-decoration: none;">
                                                {{ ! empty($c['roman']) ? $c['roman'] . ' · ' : '' }}{{ $c['name_en'] ?? '' }} ({{ $c['name_vi'] ?? '' }})
                                            </a>
                                        @else
                                            {{ $c['name_en'] ?? '' }} ({{ $c['name_vi'] ?? '' }})
                                        @endif
                                    </h3>
                                    <p style="font-size: 14.5px; line-height: 1.72; color: #292524; margin: 0 0 10px;">
                                        {{ $ci['insight'] ?? ($c['position_contextual_meaning'] ?? ($c['core_meaning'] ?? '')) }}
                                    </p>
                                    @if (! empty($c['keywords']))
                                        <p style="font-size: 12.8px; color: #57534E; margin: 0;">
                                            <strong>Từ khóa:</strong> {{ implode(' · ', $c['keywords']) }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </article>

                    <article class="mayhen_tarot_book_card">
                        <div class="mayhen_tarot_kicker">TẦNG 3 &amp; 4 · LIÊN KẾT LÁ BÀI &amp; ỨNG DỤNG</div>
                        <h2>Tương tác giữa các lá bài</h2>
                        <p style="font-size: 14.8px; line-height: 1.75; white-space: pre-line; margin-bottom: 16px;">{{ $interpretation['cross_card_synthesis'] ?? ($analysis['rule_synthesis'] ?? '') }}</p>
                        @if (! empty($interpretation['contextual_insights']))
                            <div style="background: #F3EBE0; border-left: 3px solid #85182A; padding: 14px 16px; border-radius: 0 10px 10px 0;">
                                <strong style="color: #85182A;">Góc nhìn bối cảnh &amp; nhân duyên:</strong>
                                <p style="margin: 6px 0 0; font-size: 14.5px; line-height: 1.68;">{{ $interpretation['contextual_insights'] }}</p>
                            </div>
                        @endif
                    </article>

                    <article class="mayhen_tarot_book_card">
                        <div class="mayhen_tarot_kicker">TẦNG 5 &amp; 6 · HÀNH ĐỘNG &amp; CHIÊM NGHIỆM</div>
                        <h2>Gợi ý thực hành cụ thể</h2>
                        <ul class="mayhen_tarot_action_list" style="margin-bottom: 16px;">
                            @foreach ($actionsList as $act)
                                <li>{{ $act }}</li>
                            @endforeach
                        </ul>
                        @if (! empty($interpretation['avoid_action']))
                            <p style="font-size: 14px; color: #85182A; margin: 0 0 12px;">
                                <strong>Điều nên tránh:</strong> {{ $interpretation['avoid_action'] }}
                            </p>
                        @endif
                        @if (! empty($interpretation['dialogue_prompt']))
                            <p style="font-size: 14px; color: #142D25; margin: 0 0 12px;">
                                <strong>Gợi ý đối thoại:</strong> {{ $interpretation['dialogue_prompt'] }}
                            </p>
                        @endif
                        @if (! empty($interpretation['reflection_question']))
                            <div style="background: #FAF5EB; border: 1px solid rgba(200,160,85,0.4); border-radius: 10px; padding: 14px 16px;">
                                <strong>Câu hỏi tự vấn cho bạn:</strong> “{{ $interpretation['reflection_question'] }}”
                            </div>
                        @endif
                    </article>
                </div>

                <!-- Depth: Deep Symbols & Technical Audit -->
                <div class="mayhen_tarot_depth_panel" data-tarot-depth-panel="deep">
                    <article class="mayhen_tarot_book_card">
                        <div class="mayhen_tarot_kicker">CHUYÊN SÂU · BIỂU TƯỢNG RIDER–WAITE–SMITH</div>
                        <h2>Giải mã biểu tượng &amp; Hiểu lầm cần tránh</h2>
                        @foreach ($cards as $c)
                            @php $posNum = (int) ($c['position_index'] ?? $loop->iteration); @endphp
                            <div style="padding: 14px 0; border-bottom: 1px solid rgba(41,37,36,0.1);">
                                <h3 style="font-size: 17px; margin-bottom: 6px;">{{ $posNum }}. {{ $c['name_en'] ?? '' }} ({{ ! empty($c['is_reversed']) ? 'Ngược' : 'Xuôi' }})</h3>
                                <p style="font-size: 14px; line-height: 1.68; margin: 0 0 6px;">
                                    <strong>Biểu tượng chuẩn RWS:</strong> {{ implode(', ', $c['symbols'] ?? []) }}
                                </p>
                                @if (! empty($c['common_misconception']))
                                    <p style="font-size: 13.5px; color: #57534E; margin: 0;">
                                        <strong>Hiểu lầm cần tránh:</strong> {{ $c['common_misconception'] }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </article>

                    <article class="mayhen_tarot_book_card">
                        <div class="mayhen_tarot_kicker">MINH BẠCH THUẬT TOÁN</div>
                        <h2>Chứng thực tính ngẫu nhiên của trải bài</h2>
                        <p style="font-size: 13.5px; line-height: 1.68; color: #57534E;">
                            Trải bài được khởi tạo bằng thuật toán <strong>{{ $reading->randomization_version }}</strong> trên bộ <strong>{{ $manifest['deck_name'] ?? 'Rider–Waite–Smith' }}</strong> ({{ $reading->deck_version }}), rút không hoàn lại.
                        </p>
                        @if ($reading->commitment_hash)
                            <div style="font-family: ui-monospace, Consolas, monospace; font-size: 12px; background: #F3EBE0; padding: 12px; border-radius: 8px; word-break: break-all;">
                                <div><strong>Commitment Hash (SHA-256):</strong> {{ $reading->commitment_hash }}</div>
                                <div><strong>Nonce:</strong> {{ $reading->commitment_nonce }}</div>
                                <div><strong>Trạng thái AI:</strong> {{ $reading->ai_status }} {{ $reading->ai_model ? '(' . $reading->ai_model . ')' : '' }}</div>
                            </div>
                        @endif
                    </article>
                </div>

                <!-- Follow-up AI Q&A -->
                <article class="mayhen_tarot_book_card">
                    <div class="mayhen_tarot_kicker">HỎI SÂU SAU KHI RÚT</div>
                    <h3>Hỏi thêm về trải bài này (không rút thêm lá)</h3>
                    <div id="tarotFollowupList">
                        @foreach ($reading->followups as $fu)
                            <div class="mayhen_tarot_qa_item">
                                <div class="mayhen_tarot_qa_q">Bạn hỏi: {{ $fu->question }}</div>
                                <div class="mayhen_tarot_qa_a">{{ $fu->answer }}</div>
                            </div>
                        @endforeach
                    </div>
                    <form id="tarotFollowupForm" style="display: flex; gap: 10px; margin-top: 12px; flex-wrap: wrap;">
                        <input type="text" id="tarotFollowupInput" class="mayhen_tarot_light_input" style="flex: 1; min-width: 240px;" maxlength="360" placeholder="Ví dụ: Lá ở vị trí thử thách nhắc mình điều chỉnh cách giao tiếp thế nào?" required>
                        <button type="submit" class="mayhen_tarot_btn_editorial">Hỏi sâu</button>
                    </form>
                    <div id="tarotFollowupStatus" style="font-size: 12.5px; color: #57534E; margin-top: 8px;"></div>
                </article>
            </div>

            <!-- Right Sidebar -->
            <aside>
                <div class="mayhen_tarot_book_card">
                    <div class="mayhen_tarot_kicker">CẤU TRÚC NĂNG LƯỢNG</div>
                    <h3>Cân bằng 4 Nguyên tố</h3>
                    <div class="mayhen_tarot_element_bars">
                        @foreach (['Lửa', 'Nước', 'Khí', 'Đất'] as $elKey)
                            @php
                                $cnt = (int) ($elementCounts[$elKey] ?? 0);
                                $pct = (int) round(($cnt / $totalCards) * 100);
                            @endphp
                            <div class="mayhen_tarot_element_bar_row">
                                <span>{{ $elKey }}</span>
                                <div class="mayhen_tarot_element_track">
                                    <div class="mayhen_tarot_element_fill" style="width: {{ $pct }}%;"></div>
                                </div>
                                <strong>{{ $cnt }}</strong>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mayhen_tarot_book_card">
                    <div class="mayhen_tarot_kicker">LƯU GIỮ &amp; CHIA SẺ</div>
                    <h3>Xuất Thẻ Nghệ Thuật PNG</h3>
                    <p style="font-size: 13px; color: #57534E; margin: 0 0 12px;">
                        Thẻ ảnh ẩn danh tự động ẩn câu hỏi riêng tư của bạn, tối ưu cho Zalo, Instagram Story và lưu kỷ niệm.
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px;">
                        <button type="button" class="mayhen_tarot_btn_editorial" data-tarot-export-ratio="1:1">Tải 1:1</button>
                        <button type="button" class="mayhen_tarot_btn_outline_dark" data-tarot-export-ratio="4:5">Tải 4:5</button>
                        <button type="button" class="mayhen_tarot_btn_outline_dark" data-tarot-export-ratio="9:16">Tải 9:16</button>
                    </div>

                    @auth
                        <div style="border-top: 1px solid rgba(41,37,36,0.1); padding-top: 12px;">
                            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                <button type="button" id="tarotShareToggleBtn" class="mayhen_tarot_btn_outline_dark">🔗 Tạo link chia sẻ ẩn danh</button>
                                <button type="button" id="tarotShareRevokeBtn" class="mayhen_tarot_btn_outline_dark" style="{{ $shareUrl ? '' : 'display:none;' }}">Thu hồi link</button>
                            </div>
                            <div id="tarotShareUrlBox" style="font-size: 12.5px; color: #57534E; margin-top: 8px; word-break: break-all;">
                                @if ($shareUrl)
                                    Liên kết ẩn danh: <a href="{{ $shareUrl }}" target="_blank" rel="noopener">{{ $shareUrl }}</a>
                                @else
                                    Đang ở chế độ riêng tư tuyệt đối.
                                @endif
                            </div>
                        </div>
                    @endauth
                </div>

                @auth
                    <div class="mayhen_tarot_book_card">
                        <div class="mayhen_tarot_kicker">SỔ BÀI TAROT</div>
                        <h3>Ghi chú chiêm nghiệm</h3>
                        <form id="tarotJournalForm" style="display: flex; flex-direction: column; gap: 8px;">
                            <input type="text" name="feeling_note" class="mayhen_tarot_light_input" placeholder="Cảm xúc lúc mở bài...">
                            <input type="text" name="realization_note" class="mayhen_tarot_light_input" placeholder="Điều bạn nhận ra rõ nhất...">
                            <input type="text" name="action_note" class="mayhen_tarot_light_input" placeholder="Một việc nhỏ bạn sẽ làm trong tuần này...">
                            <button type="submit" class="mayhen_tarot_btn_editorial" style="justify-content: center;">Lưu vào Sổ Bài</button>
                        </form>
                        <div id="tarotJournalStatus" style="font-size: 12.5px; color: #142D25; margin-top: 6px;"></div>
                        <div id="tarotJournalList" style="margin-top: 12px;">
                            @foreach ($reading->journals->sortByDesc('id') as $jn)
                                <div class="mayhen_tarot_qa_item">
                                    <div class="mayhen_tarot_qa_q">{{ $jn->created_at?->format('H:i · d/m/Y') }}</div>
                                    <div class="mayhen_tarot_qa_a">{{ collect([$jn->feeling_note, $jn->realization_note, $jn->action_note, $jn->outcome_note])->filter()->implode(' · ') }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endauth

                <div class="mayhen_tarot_book_card">
                    <div class="mayhen_tarot_kicker">PHẢN HỒI &amp; TRÁCH NHIỆM</div>
                    <h3>Trải bài này có hữu ích với bạn?</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px;">
                        <button type="button" class="mayhen_tarot_filter_pill {{ $reading->feedback_rating === 'very_helpful' ? 'is-active' : '' }}" data-tarot-feedback="very_helpful">Rất sâu sắc</button>
                        <button type="button" class="mayhen_tarot_filter_pill {{ $reading->feedback_rating === 'helpful' ? 'is-active' : '' }}" data-tarot-feedback="helpful">Hữu ích</button>
                        <button type="button" class="mayhen_tarot_filter_pill {{ $reading->feedback_rating === 'unclear' ? 'is-active' : '' }}" data-tarot-feedback="unclear">Chưa rõ ý</button>
                    </div>
                    <p style="font-size: 12.5px; line-height: 1.6; color: #57534E; margin: 0;">
                        Tarot tại Mây Hẹn là công cụ soi chiếu nội tâm và gợi mở góc nhìn, không thay thế tư vấn y tế, tâm lý, pháp lý hay tài chính.
                    </p>
                </div>
            </aside>
        </div>
    </div>
</div>

<script type="application/json" id="mayhenTarotResultConfig">{!! json_encode([
    'endpoints' => $endpoints,
    'exportData' => [
        'spreadName' => $spread['name'] ?? 'Trải Bài Tarot',
        'dateLabel' => $reading->created_at?->format('d/m/Y'),
        'cards' => $cards,
        'summary' => $summaryText,
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/client/js/pages/tarot.js') }}?v={{ filemtime(public_path('assets/client/js/pages/tarot.js')) }}" defer></script>
@endpush
