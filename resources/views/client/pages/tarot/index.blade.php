@extends('client.layouts.master')

@section('title', $pageMeta['title'] ?? 'Xem Tarot Online & Bói Bài Tarot Online Miễn Phí')
@section('meta_description', $pageMeta['meta_description'] ?? 'Xem Tarot Online & Bói Bài Tarot Online Miễn Phí theo chuẩn 78 lá Rider–Waite–Smith tại Mây Hẹn – The Art of Tarot.')
@section('canonical', $pageMeta['canonical'] ?? route('tarot.index'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/tarot.css') }}?v={{ filemtime(public_path('assets/client/css/pages/tarot.css')) }}">
@endpush

@push('structured_data')
    @php
        $tarotSchemas = [
            [
                '@context' => 'https://schema.org',
                '@type' => 'WebApplication',
                'name' => 'Xem Tarot Online & Bói Bài Tarot Online Miễn Phí — Mây Hẹn – The Art of Tarot',
                'url' => route('tarot.index'),
                'applicationCategory' => 'LifestyleApplication',
                'operatingSystem' => 'All',
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'VND',
                ],
                'description' => 'Trải nghiệm xem Tarot online chuẩn 78 lá Rider–Waite–Smith (Strength VIII, Justice XI), xáo bài CSPRNG Fisher–Yates có SHA-256 Commitment Hash, tương tác 3D và luận giải 6 tầng.',
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Xem Tarot Online', 'item' => route('tarot.index')],
                ],
            ],
            [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => 'Xem Tarot Online tại Mây Hẹn dùng bộ bài nào và cơ chế rút bài có ngẫu nhiên thật không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Mây Hẹn sử dụng chuẩn 78 lá Rider–Waite–Smith gốc 1909 (Strength ở vị trí VIII, Justice ở vị trí XI). Mỗi phiên trải bài được xáo trên máy chủ bằng thuật toán Fisher–Yates CSPRNG (random_int), rút không hoàn lại và khóa thứ tự trước bằng mã băm SHA-256 Commitment Hash.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Xem Tarot Online tại Mây Hẹn có miễn phí không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Hoàn toàn miễn phí cho cả 3 cấp độ Xem 1 Lá, Xem 3 Lá và Xem Chuyên Sâu 5 Lá. Đăng nhập tài khoản Mây Hẹn miễn phí giúp bạn mở khóa phân tích AI 6 tầng, hỏi sâu sau khi rút, trải bài Hành Trình Nhân Duyên 7 lá, Celtic Cross 10 lá và lưu Sổ Bài Tarot riêng tư.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Nên chọn xem Tarot 1 lá, 3 lá hay chuyên sâu 5 lá?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Xem 1 lá phù hợp rút thông điệp hôm nay hoặc lời khuyên nhanh; Xem 3 lá phù hợp nhìn mạch quá khứ – hiện tại – hướng tới hoặc tình yêu 3 góc nhìn; Xem Chuyên Sâu 5 lá phù hợp khi bạn cần phân tích đa chiều cả thực tại, nguyên nhân ẩn sâu, rào cản, nguồn lực và kết quả có điều kiện.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Rút trúng lá bài Tarot Ngược (Reversed) có phải là điềm xấu không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Không. Trong trường phái Rider–Waite–Smith hiện đại, lá bài Ngược không mang nghĩa xui xẻo mà thể hiện năng lượng đang hướng vào bên trong, bị trì hoãn tạm thời, hoặc đang thừa/thiếu cân bằng để bạn chủ động điều chỉnh.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Có nên rút lại nhiều lần cho cùng một câu hỏi Tarot không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Không nên. Rút lặp liên tục cho cùng một câu hỏi trong thời gian ngắn dễ gây nhiễu thông tin và lo âu. Thay vào đó, hãy dùng tính năng Hỏi Sâu trên chính trải bài vừa rút hoặc chờ hoàn cảnh thay đổi rồi mới trải bài mới.',
                        ],
                    ],
                    [
                        '@type' => 'Question',
                        'name' => 'Tarot có dùng để dự đoán chắc chắn tương lai, chẩn đoán y khoa hay khẳng định ngoại tình không?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Không. Tarot tại Mây Hẹn là công cụ soi chiếu tâm trí, gợi mở góc nhìn và định hướng hành động tích cực; tuyệt đối không thay thế bác sĩ, chuyên gia tâm lý, luật sư hay chuyên gia tài chính.',
                        ],
                    ],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($tarotSchemas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
<div class="mayhen_tarot_page" id="mayhenTarotApp">
    <!-- 1. Velvet Emerald 3D Reading Table Hero -->
    <section class="mayhen_tarot_hero">
        <div class="mayhen_tarot_container">
            <div class="mayhen_tarot_hero_head">
                <div class="mayhen_tarot_eyebrow">
                    <span>{{ $pageMeta['eyebrow'] ?? '✦ MÂY HẸN – THE ART OF TAROT · RIDER–WAITE–SMITH 78 LÁ' }}</span>
                </div>
                <h1 class="mayhen_tarot_title">{{ $pageMeta['h1'] ?? 'Xem Tarot Online & Bói Bài Tarot Online Miễn Phí' }}</h1>
                <p class="mayhen_tarot_subtitle">
                    {{ $pageMeta['subtitle'] ?? '' }}
                </p>

                <div class="mayhen_tarot_toolbar">
                    <button type="button" id="tarotSoundToggle" class="mayhen_tarot_chip_btn" aria-pressed="false">
                        🔇 Âm thanh lá bài: TẮT
                    </button>
                    <select id="tarotPerfMode" class="mayhen_tarot_perf_select" aria-label="Chế độ hiệu ứng">
                        <option value="high">Hiệu ứng 3D Cao cấp</option>
                        <option value="balanced">Cân bằng</option>
                        <option value="lightweight">Nhẹ (2D Tối giản)</option>
                    </select>
                    <a href="{{ route('tarot.library') }}" class="mayhen_tarot_chip_btn">📖 Bách Khoa 78 Lá Bài</a>
                    @auth
                        <a href="{{ route('tarot.journal.index') }}" class="mayhen_tarot_chip_btn">📓 Sổ Bài Tarot Của Tôi</a>
                    @endauth
                </div>
            </div>

            <div style="text-align: center;">
                <div class="mayhen_tarot_mode_tabs" role="tablist" aria-label="Chế độ trải bài">
                    <button type="button" class="mayhen_tarot_mode_tab is-active" data-tarot-mode-tab="online" role="tab">
                        ✦ Trải Bài 3D Trực Tuyến
                    </button>
                    <button type="button" class="mayhen_tarot_mode_tab" data-tarot-mode-tab="manual" role="tab">
                        🃏 Nhập Lá Từ Bộ Bài Vật Lý
                    </button>
                </div>
            </div>

            <div class="mayhen_tarot_stage_card">
                <!-- Panel A: Interactive Online 3D Reading -->
                <div id="tarotOnlinePanel">
                    <div class="mayhen_tarot_step_bar" aria-label="Tiến trình trải bài">
                        <div class="mayhen_tarot_step_item is-active" data-tarot-step="1">
                            <span class="mayhen_tarot_step_num">1</span> Chọn trải bài &amp; câu hỏi
                        </div>
                        <div class="mayhen_tarot_step_item" data-tarot-step="2">
                            <span class="mayhen_tarot_step_num">2</span> Xáo 78 lá CSPRNG
                        </div>
                        <div class="mayhen_tarot_step_item" data-tarot-step="3">
                            <span class="mayhen_tarot_step_num">3</span> Tự tay chọn lá trên bàn
                        </div>
                        <div class="mayhen_tarot_step_item" data-tarot-step="4">
                            <span class="mayhen_tarot_step_num">4</span> Mở luận giải 6 tầng
                        </div>
                    </div>

                    <!-- Step 1: Setup -->
                    <div id="tarotSetupBox">
                        <!-- 3 Cấp Độ Xem Tarot Chuẩn: 1 Lá · 3 Lá · Chuyên Sâu 5 Lá (+ Mở rộng 7-10 lá) -->
                        <div class="mayhen_tarot_tier_bar" role="tablist" aria-label="Chọn cấp độ xem Tarot">
                            <button type="button" class="mayhen_tarot_tier_btn" data-tarot-tier="1" role="tab">
                                <span class="mayhen_tarot_tier_num">1 LÁ</span>
                                <span class="mayhen_tarot_tier_info">
                                    <strong>Xem 1 Lá</strong>
                                    <small>Nhanh · Trọng tâm &amp; Lời khuyên</small>
                                </span>
                            </button>
                            <button type="button" class="mayhen_tarot_tier_btn is-active" data-tarot-tier="3" role="tab">
                                <span class="mayhen_tarot_tier_num">3 LÁ</span>
                                <span class="mayhen_tarot_tier_info">
                                    <strong>Xem 3 Lá</strong>
                                    <small>Tiêu chuẩn · Toàn diện &amp; Tình yêu</small>
                                </span>
                            </button>
                            <button type="button" class="mayhen_tarot_tier_btn" data-tarot-tier="5" role="tab">
                                <span class="mayhen_tarot_tier_num">5 LÁ</span>
                                <span class="mayhen_tarot_tier_info">
                                    <strong>Xem Chuyên Sâu (5 Lá) <span style="font-size:10px; padding:1px 6px; border-radius:99px; background:rgba(217,180,100,0.22); color:#F2DFB3;">PLUS+</span></strong>
                                    <small>Đào sâu 5 tầng · Đa chiều</small>
                                </span>
                            </button>
                            <button type="button" class="mayhen_tarot_tier_btn mayhen_tarot_tier_btn--ext" data-tarot-tier="ext" role="tab">
                                <span class="mayhen_tarot_tier_num">7–10</span>
                                <span class="mayhen_tarot_tier_info">
                                    <strong>Mở Rộng Đặc Biệt <span style="font-size:10px; padding:1px 6px; border-radius:99px; background:rgba(133,24,42,0.55); color:#FFE4E8;">PRO</span></strong>
                                    <small>Nhân Duyên 7 lá · Celtic Cross 10 lá</small>
                                </span>
                            </button>
                        </div>

                        <div class="mayhen_tarot_spread_grid">
                            @foreach ($spreads as $sp)
                                @php
                                    $spCount = (int) ($sp['card_count'] ?? 3);
                                    $spTier = in_array($spCount, [1, 3, 5], true) ? (string) $spCount : 'ext';
                                @endphp
                                <button type="button"
                                        class="mayhen_tarot_spread_option {{ ($presetSpread ?? '') === $sp['spread_id'] ? 'is-selected' : '' }}"
                                        data-tarot-spread-id="{{ $sp['spread_id'] }}"
                                        data-tarot-spread-tier="{{ $spTier }}"
                                        data-tarot-card-count="{{ $spCount }}">
                                    @if (! empty($sp['is_signature']))
                                        <span class="mayhen_tarot_spread_tag">✦ Độc quyền Mây Hẹn</span>
                                    @endif
                                    <div class="mayhen_tarot_spread_top">
                                        <span class="mayhen_tarot_spread_name">{{ $sp['name'] }}</span>
                                        <span class="mayhen_tarot_spread_count">{{ $sp['card_count'] }} lá</span>
                                    </div>
                                    <p class="mayhen_tarot_spread_desc">{{ $sp['description'] ?? '' }}</p>
                                </button>
                            @endforeach
                        </div>

                        <div class="mayhen_tarot_form_grid">
                            <div class="mayhen_tarot_field">
                                <label class="mayhen_tarot_label" for="tarotCategorySelect">Chủ đề bạn muốn soi chiếu</label>
                                <select id="tarotCategorySelect" class="mayhen_tarot_select">
                                    @foreach ($categories as $catKey => $catLabel)
                                        <option value="{{ $catKey }}" @selected(($presetCategory ?? 'love') === $catKey)>{{ $catLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mayhen_tarot_field">
                                <label class="mayhen_tarot_label" for="tarotStageSelect">Giai đoạn hiện tại (tuỳ chọn)</label>
                                <select id="tarotStageSelect" class="mayhen_tarot_select">
                                    <option value="">-- Không bắt buộc --</option>
                                    @foreach ($relationshipStages as $stKey => $stLabel)
                                        <option value="{{ $stKey }}">{{ $stLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mayhen_tarot_field mayhen_tarot_field_full">
                                <label class="mayhen_tarot_label" for="tarotQuestionInput">Câu hỏi mở của bạn (tuỳ chọn, riêng tư tuyệt đối)</label>
                                <div class="mayhen_tarot_question_row">
                                    <input type="text"
                                           id="tarotQuestionInput"
                                           class="mayhen_tarot_input"
                                           maxlength="360"
                                           placeholder="Ví dụ: Điều gì đang giúp mối quan hệ của hai chúng mình gắn kết và thấu hiểu hơn trong 3 tháng tới?">
                                    <button type="button" id="tarotRefineQuestionBtn" class="mayhen_tarot_btn_secondary">
                                        Gợi ý câu hỏi mở
                                    </button>
                                </div>
                                <div id="tarotRefineBox" class="mayhen_tarot_refine_box"></div>
                            </div>

                            <div class="mayhen_tarot_field">
                                <label class="mayhen_tarot_label" for="tarotOrientationSelect">Chế độ chiều lá bài (minh bạch 50/50)</label>
                                <select id="tarotOrientationSelect" class="mayhen_tarot_select">
                                    <option value="upright_reversed" selected>Cả Lá Xuôi &amp; Lá Ngược (Chuẩn RWS · xác suất 50/50 độc lập)</option>
                                    <option value="upright_only">Chỉ Lá Xuôi (Phù hợp người mới làm quen Tarot)</option>
                                </select>
                            </div>

                            <div class="mayhen_tarot_field">
                                <label class="mayhen_tarot_label" for="tarotContextInput">Bối cảnh ngắn gọn (tuỳ chọn)</label>
                                <input type="text"
                                       id="tarotContextInput"
                                       class="mayhen_tarot_input"
                                       maxlength="600"
                                       placeholder="Ví dụ: Đang chuẩn bị bàn chuyện cưới hỏi hoặc vừa thay đổi công việc...">
                            </div>
                        </div>

                        <div class="mayhen_tarot_actions_bar">
                            <div style="font-size: 12.8px; color: rgba(248, 245, 238, 0.86); line-height: 1.5;">
                                @if ($canUseTarot)
                                    ✦ Gói <strong>{{ $currentPlanLabel }}</strong> · Còn <strong>{{ $tarotQuota['daily_remaining'] }}/{{ $tarotQuota['daily_limit'] }}</strong> lượt hôm nay ({{ $tarotQuota['cooldown_label'] }}) · Tối đa <strong>{{ $tarotQuota['max_card_count'] }} lá</strong>
                                    @if (($tarotQuota['plan_slug'] ?? 'premium') !== 'pro')
                                        · <a href="{{ route('pricing') }}" style="color: #F2DFB3; text-decoration: underline;">Nâng cấp Plus/Pro</a>
                                    @endif
                                @else
                                    ✦ Bắt buộc đăng nhập miễn phí (0đ) để rút bài &amp; luận giải AI 6 tầng (<strong>Premium 0đ:</strong> 1–3 lá · 5 lượt/ngày · <strong>Plus:</strong> 5 lá · 10 lượt/ngày · <strong>Pro:</strong> 7–10 lá · 30 lượt/ngày).
                                @endif
                            </div>
                            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                @if ($canUseTarot)
                                    <button type="button" id="tarotQuickDrawBtn" class="mayhen_tarot_btn_secondary">
                                        ⚡ Rút Nhanh Không Hoàn Lại
                                    </button>
                                    <button type="button" id="tarotStartSessionBtn" class="mayhen_tarot_btn_primary">
                                        ✦ Xáo 78 Lá &amp; Tự Tay Chọn Bài
                                    </button>
                                @else
                                    <a href="{{ route('login', ['redirect' => request()->fullUrl()]) }}" class="mayhen_tarot_btn_primary">
                                        ✦ Đăng Nhập Miễn Phí Để Rút Bài →
                                    </a>
                                    <a href="{{ route('pricing') }}" class="mayhen_tarot_btn_secondary">
                                        Xem Quyền Lợi Các Gói
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Step 2-4: Active 3D Shuffle & Fan Selection Table -->
                    <div id="tarotTableStage" class="mayhen_tarot_table_stage">
                        <div class="mayhen_tarot_hash_pill" id="tarotHashPill">SHA-256 Deck Lock</div>
                        <h2 class="mayhen_tarot_table_status" id="tarotTableStatus">Đang xáo bộ bài...</h2>
                        <p class="mayhen_tarot_table_hint" id="tarotTableHint">Giữ hơi thở đều và tập trung vào câu hỏi của bạn.</p>

                        <!-- 3D Riffle Shuffle Arena -->
                        <div class="mayhen_tarot_shuffle_arena" id="tarotShuffleArena">
                            <div class="mayhen_tarot_shuffle_packet" data-tarot-card-back></div>
                            <div class="mayhen_tarot_shuffle_packet" data-tarot-card-back></div>
                        </div>

                        <!-- 3D Fan / Mobile Swipe Deck -->
                        <div class="mayhen_tarot_fan_viewport" id="tarotFanViewport" style="display: none;">
                            <div class="mayhen_tarot_fan_arc" id="tarotFanArc"></div>
                        </div>

                        <!-- Revealed Cards Tray with 180° 3D Flip -->
                        <div class="mayhen_tarot_tray" id="tarotTray"></div>

                        <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; margin-top: 12px;">
                            <button type="button" id="tarotResetBtn" class="mayhen_tarot_btn_secondary">
                                ← Đổi kiểu trải bài
                            </button>
                            <button type="button" id="tarotCompleteBtn" class="mayhen_tarot_btn_primary" style="display: none;">
                                Mở Bản Luận Giải Chi Tiết →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Panel B: Manual Physical Deck Input -->
                <div id="tarotManualPanel" class="mayhen_tarot_manual_panel">
                    <p style="font-size: 13.5px; color: rgba(250, 245, 235, 0.82); margin: 0 0 16px;">
                        Bạn đã tự xáo và rút lá từ bộ bài Tarot giấy ngoài đời? Chọn kiểu trải bài, nhập chính xác từng lá bài (Xuôi/Ngược) để Mây Hẹn tổng hợp luận giải 6 tầng và lưu vào Sổ Bài.
                    </p>
                    <div class="mayhen_tarot_form_grid">
                        <div class="mayhen_tarot_field">
                            <label class="mayhen_tarot_label" for="tarotManualSpread">Kiểu trải bài</label>
                            <select id="tarotManualSpread" class="mayhen_tarot_select">
                                @foreach ($spreads as $sp)
                                    <option value="{{ $sp['spread_id'] }}">{{ $sp['name'] }} ({{ $sp['card_count'] }} lá)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mayhen_tarot_field">
                            <label class="mayhen_tarot_label" for="tarotManualCategory">Chủ đề</label>
                            <select id="tarotManualCategory" class="mayhen_tarot_select">
                                @foreach ($categories as $catKey => $catLabel)
                                    <option value="{{ $catKey }}">{{ $catLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mayhen_tarot_field mayhen_tarot_field_full">
                            <label class="mayhen_tarot_label" for="tarotManualQuestion">Câu hỏi của bạn (tuỳ chọn)</label>
                            <input type="text" id="tarotManualQuestion" class="mayhen_tarot_input" maxlength="360" placeholder="Nhập câu hỏi khi bạn rút bộ bài giấy...">
                        </div>
                    </div>

                    <div id="tarotManualRows" class="mayhen_tarot_manual_rows"></div>

                    <div style="text-align: right; margin-top: 14px;">
                        <button type="button" id="tarotManualSubmitBtn" class="mayhen_tarot_btn_primary">
                            Phân Tích Bộ Bài Vật Lý →
                        </button>
                    </div>
                </div>

                <div id="tarotAlertBox" class="mayhen_tarot_alert_box" role="alert"></div>
            </div>
        </div>
    </section>

    <!-- 2. Recent Readings (if logged in) -->
    @if ($recentReadings->isNotEmpty())
        <section class="mayhen_tarot_section">
            <div class="mayhen_tarot_container">
                <div class="mayhen_tarot_section_head" style="display: flex; justify-content: space-between; align-items: flex-end; max-width: none; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <div class="mayhen_tarot_kicker">NHẬT KÝ GẦN ĐÂY</div>
                        <h2 class="mayhen_tarot_h2" style="margin-bottom: 0;">Các trải bài vừa lưu của bạn</h2>
                    </div>
                    <a href="{{ route('tarot.journal.index') }}" class="mayhen_tarot_btn_outline_dark">Xem toàn bộ Sổ Bài →</a>
                </div>
                <div class="mayhen_tarot_pillar_grid">
                    @foreach ($recentReadings as $r)
                        <a href="{{ route('tarot.show', $r->slug) }}" class="mayhen_tarot_editorial_card" style="text-decoration: none;">
                            <div style="font-size: 12px; color: #85182A; font-weight: 600; margin-bottom: 6px;">
                                {{ $r->created_at?->format('H:i · d/m/Y') }} · {{ count($r->selected_cards_json ?? []) }} lá
                            </div>
                            <h3 style="margin-bottom: 6px;">{{ $r->question ? Str::limit($r->question, 62) : 'Trải bài không đặt câu hỏi cố định' }}</h3>
                            <p>{{ collect($r->selected_cards_json ?? [])->pluck('name_en')->take(3)->implode(' · ') }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- 3. Dedicated Editorial Section per Landing Variant (Tránh trùng lặp nội dung SEO) -->
    @if (($pageMeta['variant'] ?? 'main') === 'celtic')
        @php $celticSpread = $spreads['celtic_cross_10'] ?? null; @endphp
        <section class="mayhen_tarot_section">
            <div class="mayhen_tarot_container">
                <div class="mayhen_tarot_section_head">
                    <div class="mayhen_tarot_kicker">CẤU TRÚC CHUẨN CELTIC CROSS 10 LÁ</div>
                    <h2 class="mayhen_tarot_h2">Giải mã 10 vị trí trong trải bài Celtic Cross (Chữ Thập &amp; Trụ Đứng)</h2>
                    <p class="mayhen_tarot_lead">
                        Celtic Cross là kiểu trải bài chuyên sâu nổi tiếng nhất trong lịch sử Rider–Waite–Smith, gồm 6 lá tạo thành hình Chữ Thập ở bên trái (phân tích cốt lõi tình huống) và 4 lá tạo thành Trụ Đứng bên phải (phân tích nội tâm, môi trường và hướng kết quả).
                    </p>
                </div>
                @if ($celticSpread && ! empty($celticSpread['positions']))
                    <div class="mayhen_tarot_pillar_grid">
                        @foreach ($celticSpread['positions'] as $pos)
                            <article class="mayhen_tarot_editorial_card">
                                <h3>{{ $pos['label'] }}</h3>
                                <p>{{ $pos['meaning'] }}</p>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @elseif (($pageMeta['variant'] ?? 'main') === 'love')
        <section class="mayhen_tarot_section">
            <div class="mayhen_tarot_container">
                <div class="mayhen_tarot_section_head">
                    <div class="mayhen_tarot_kicker">TAROT TÌNH YÊU &amp; NHÂN DUYÊN</div>
                    <h2 class="mayhen_tarot_h2">Cách đặt câu hỏi Tarot Tình Yêu sâu sắc và lành mạnh</h2>
                    <p class="mayhen_tarot_lead">
                        Thay vì hỏi những câu đoán tâm ý người khác, Tarot Tình Yêu tại Mây Hẹn tập trung giúp bạn nhìn rõ nhu cầu cảm xúc, động lực giữa hai người và cách giao tiếp chân thành hơn.
                    </p>
                </div>
                <div class="mayhen_tarot_pillar_grid">
                    <article class="mayhen_tarot_editorial_card">
                        <h3>Dành cho người đang độc thân</h3>
                        <p>“Tôi cần mở lòng hoặc chữa lành điều gì để đón nhận một mối quan hệ lành mạnh và đồng điệu hơn trong thời gian tới?”</p>
                    </article>
                    <article class="mayhen_tarot_editorial_card">
                        <h3>Dành cho cặp đôi đang tìm hiểu / yêu nhau</h3>
                        <p>“Điều gì đang giúp hai chúng mình gắn kết tốt nhất và khúc mắc nào cả hai nên cùng trao đổi thẳng thắn?”</p>
                    </article>
                    <article class="mayhen_tarot_editorial_card">
                        <h3>Dành cho giai đoạn chuẩn bị kết hôn</h3>
                        <p>“Hai chúng tôi cần chuẩn bị nền tảng tinh thần, tài chính và sự đồng thuận gia đình như thế nào để bước vào hôn nhân vững vàng?”</p>
                    </article>
                </div>
            </div>
        </section>
    @elseif (($pageMeta['variant'] ?? 'main') === 'daily')
        <section class="mayhen_tarot_section">
            <div class="mayhen_tarot_container">
                <div class="mayhen_tarot_section_head">
                    <div class="mayhen_tarot_kicker">THÓI QUEN CHIÊM NGHIỆM 1 PHÚT</div>
                    <h2 class="mayhen_tarot_h2">Vì sao nên rút 1 lá Tarot Hôm Nay mỗi sáng?</h2>
                    <p class="mayhen_tarot_lead">
                        Một lá bài mỗi ngày đóng vai trò như một điểm neo tỉnh thức: nhắc bạn chú ý tới thái độ, cách ứng xử và cơ hội nhỏ ngay trong ngày mà không cần rút lặp nhiều lần.
                    </p>
                </div>
                <div class="mayhen_tarot_pillar_grid">
                    <article class="mayhen_tarot_editorial_card">
                        <h3>Giữ ổn định 1 lá mỗi ngày</h3>
                        <p>Khi đăng nhập Mây Hẹn, lá bài Tarot Hôm Nay của bạn được giữ cố định trong suốt ngày đó — không thay đổi khi tải lại trang.</p>
                    </article>
                    <article class="mayhen_tarot_editorial_card">
                        <h3>Đọc nhanh trong 60 giây</h3>
                        <p>Chế độ Đọc nhanh giúp bạn nắm ngay từ khóa cốt lõi và 1 việc nhỏ nên ưu tiên trước khi bắt đầu ngày làm việc.</p>
                    </article>
                    <article class="mayhen_tarot_editorial_card">
                        <h3>Đối chiếu lại vào cuối ngày</h3>
                        <p>Lưu một dòng ghi chú ngắn vào Sổ Bài Tarot vào buổi tối để quan sát cách bạn đã ứng dụng thông điệp trong thực tế.</p>
                    </article>
                </div>
            </div>
        </section>
    @else
        <section class="mayhen_tarot_section">
            <div class="mayhen_tarot_container">
                <div class="mayhen_tarot_section_head">
                    <div class="mayhen_tarot_kicker">CHUẨN MỰC &amp; MINH BẠCH</div>
                    <h2 class="mayhen_tarot_h2">Vì sao trải bài Tarot tại Mây Hẹn khác biệt?</h2>
                    <p class="mayhen_tarot_lead">
                        Tarot không phải trò bói toán định mệnh hay những lời phán gây bất an. Mây Hẹn xây dựng trải nghiệm Tarot dựa trên dữ liệu chuẩn học thuật Rider–Waite–Smith, cơ chế ngẫu nhiên minh bạch và ngôn ngữ soi chiếu nhân văn.
                    </p>
                </div>
                <div class="mayhen_tarot_pillar_grid">
                    <article class="mayhen_tarot_editorial_card">
                        <h3>Chuẩn 78 lá Rider–Waite–Smith</h3>
                        <p>Bảo toàn đúng trật tự kinh điển của Arthur Edward Waite &amp; Pamela Colman Smith: 22 Ẩn Chính (Strength VIII, Justice XI) và 56 Ẩn Phụ chia đủ 4 chất Gậy – Cốc – Kiếm – Xu, không trộn lẫn hệ Marseille hay Oracle.</p>
                    </article>
                    <article class="mayhen_tarot_editorial_card">
                        <h3>CSPRNG Fisher–Yates &amp; SHA-256</h3>
                        <p>Bộ bài được xáo toàn bộ 78 lá bằng `random_int` phía máy chủ, rút không hoàn lại và tạo mã băm SHA-256 Commitment Hash trước khi bạn chọn vị trí. AI không bao giờ can thiệp hay thay đổi lá bài đã rút.</p>
                    </article>
                    <article class="mayhen_tarot_editorial_card">
                        <h3>3 Cấp Độ 1 Lá · 3 Lá · Chuyên Sâu 5 Lá</h3>
                        <p>Tổ chức rõ ràng từ rút nhanh 1 lá, tiêu chuẩn 3 lá đến phân tích chuyên sâu 5 lá, kèm bộ mở rộng Hành Trình Nhân Duyên 7 lá và Celtic Cross 10 lá.</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- SEO Pillar Section A: Tarot Là Gì & Cấu Trúc 78 Lá Rider-Waite-Smith -->
        <section class="mayhen_tarot_section">
            <div class="mayhen_tarot_container">
                <div class="mayhen_tarot_seo_article_card">
                    <div class="mayhen_tarot_kicker">KIẾN THỨC NỀN TẢNG CHUẨN HỌC THUẬT</div>
                    <h2 class="mayhen_tarot_h2">Xem Tarot Online Là Gì? Cấu Trúc Bộ Bài 78 Lá Rider–Waite–Smith (1909)</h2>
                    <p class="mayhen_tarot_seo_p">
                        <strong>Tarot</strong> là hệ thống gồm <strong>78 lá bài biểu tượng</strong> xuất hiện tại châu Âu từ thế kỷ XV và đạt đến hình thái chuẩn mực phổ biến nhất thế giới vào năm <strong>1909</strong> qua bộ <strong>Rider–Waite–Smith (RWS)</strong> do học giả <em>Arthur Edward Waite</em> biên soạn và nữ họa sĩ <em>Pamela Colman Smith</em> vẽ minh họa (kèm tài liệu gốc <em>The Pictorial Key to the Tarot, 1910</em>). Ngày nay, xem bài Tarot online miễn phí được ứng dụng rộng rãi như một phương pháp <strong>soi chiếu nội tâm, nhận diện nút thắt cảm xúc và gợi mở góc nhìn hành động</strong> cho tình yêu, hôn nhân, gia đình và sự nghiệp.
                    </p>
                    <p class="mayhen_tarot_seo_p">
                        Khác với các hệ bài Marseille hay Thoth, bộ bài Rider–Waite–Smith đặt lá <strong>Strength ở vị trí VIII</strong> và <strong>Justice ở vị trí XI</strong>, đồng thời minh họa đầy đủ cảnh tượng sống động cho cả 56 lá Ẩn Phụ giúp người xem dễ dàng cảm nhận biểu tượng trực quan:
                    </p>

                    <div class="mayhen_tarot_table_wrap">
                        <table class="mayhen_tarot_seo_table">
                            <thead>
                                <tr>
                                    <th>Nhóm lá bài</th>
                                    <th>Số lượng</th>
                                    <th>Nguyên tố</th>
                                    <th>Phạm vi ý nghĩa cốt lõi trong trải bài Tarot</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>22 Ẩn Chính (Major Arcana)</strong></td>
                                    <td>22 lá (0 – XXI)</td>
                                    <td>Tổng hợp</td>
                                    <td>Các nguyên mẫu lớn, bài học trưởng thành, bước ngoặt quan trọng (The Fool, The Lovers, Strength VIII, Justice XI, The Sun, The World...).</td>
                                </tr>
                                <tr>
                                    <td><strong>Bộ Gậy (Wands)</strong></td>
                                    <td>14 lá (Ace – King)</td>
                                    <td>Lửa</td>
                                    <td>Ý chí, cảm hứng, hành động chủ động, đam mê, khởi nghiệp và động lực bứt phá.</td>
                                </tr>
                                <tr>
                                    <td><strong>Bộ Cốc (Cups)</strong></td>
                                    <td>14 lá (Ace – King)</td>
                                    <td>Nước</td>
                                    <td>Cảm xúc, tình yêu, nhân duyên, hôn nhân, gia đình, sự đồng cảm và trực giác nội tâm.</td>
                                </tr>
                                <tr>
                                    <td><strong>Bộ Kiếm (Swords)</strong></td>
                                    <td>14 lá (Ace – King)</td>
                                    <td>Khí</td>
                                    <td>Tư duy lý trí, sự minh bạch, giao tiếp, ra quyết định và các rào cản tâm lý cần tháo gỡ.</td>
                                </tr>
                                <tr>
                                    <td><strong>Bộ Tiền / Xu (Pentacles)</strong></td>
                                    <td>14 lá (Ace – King)</td>
                                    <td>Đất</td>
                                    <td>Công việc bền bỉ, quản trị tài chính, nền tảng vật chất, sức khỏe thể chất và xây dựng tổ ấm.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- SEO Pillar Section B: So sánh 3 cấp độ 1 lá - 3 lá - 5 lá & Cách đặt câu hỏi mở -->
        <section class="mayhen_tarot_section">
            <div class="mayhen_tarot_container">
                <div class="mayhen_tarot_seo_two_col">
                    <article class="mayhen_tarot_seo_article_card">
                        <div class="mayhen_tarot_kicker">CHỌN ĐÚNG KIỂU TRẢI BÀI</div>
                        <h2 class="mayhen_tarot_h2" style="font-size: 24px;">So sánh 3 cấp độ Xem Tarot: 1 Lá · 3 Lá · Chuyên Sâu 5 Lá</h2>
                        <ul class="mayhen_tarot_seo_list">
                            <li><strong>Xem 1 Lá (Tarot Hôm Nay / Lời Khuyên / Có–Không):</strong> Phù hợp khi bạn cần một điểm neo nhanh trong 60 giây, nắm từ khóa trọng tâm của ngày mới hoặc tham khảo xu hướng có điều kiện.</li>
                            <li><strong>Xem 3 Lá (Tình Yêu / Dòng Thời Gian / Giải Quyết Vấn Đề):</strong> Cấp độ cân bằng nhất để thấy mạch liên kết giữa nguyên nhân – hiện tại – điều cần vun đắp.</li>
                            <li><strong>Xem Chuyên Sâu 5 Lá (Toàn Cảnh &amp; Tình Yêu 5 Lá):</strong> Đào sâu 5 lớp dữ kiện (Thực tại, Nguyên nhân ẩn sâu, Rào cản, Nguồn lực và Hướng đi có điều kiện) cho các quyết định quan trọng về tình cảm, cưới hỏi hay sự nghiệp.</li>
                            <li><strong>Mở rộng đặc biệt (7 – 10 Lá):</strong> Dành cho cặp đôi chuẩn bị kết hôn (<a href="{{ route('tarot.love') }}">Hành Trình Nhân Duyên 7 lá</a>) hoặc bối cảnh phức tạp cần sơ đồ <a href="{{ route('tarot.celtic') }}">Celtic Cross 10 lá</a>.</li>
                        </ul>
                    </article>

                    <article class="mayhen_tarot_seo_article_card">
                        <div class="mayhen_tarot_kicker">BÍ QUYẾT RÚT BÀI CHÍNH XÁC</div>
                        <h2 class="mayhen_tarot_h2" style="font-size: 24px;">Cách đặt câu hỏi mở &amp; hiểu đúng Lá Ngược (Reversed)</h2>
                        <p class="mayhen_tarot_seo_p">
                            Tarot phát huy chiều sâu tốt nhất khi câu hỏi tập trung vào <strong>vai trò chủ động của chính bạn</strong> thay vì cố đoán suy nghĩ của người khác:
                        </p>
                        <ul class="mayhen_tarot_seo_list">
                            <li><strong>Thay vì hỏi:</strong> <em>“Người ấy có yêu tôi thật lòng không?”</em> → <strong>Hãy hỏi:</strong> <em>“Tôi nên quan sát và trao đổi điều gì để hiểu rõ hơn sự đồng điệu trong mối quan hệ này?”</em></li>
                            <li><strong>Thay vì hỏi:</strong> <em>“Tôi có giàu không?”</em> → <strong>Hãy hỏi:</strong> <em>“Điểm nghẽn nào trong cách quản lý công việc/tài chính tôi cần điều chỉnh trong 3 tháng tới?”</em></li>
                            <li><strong>Hiểu đúng Lá Ngược:</strong> Lá Ngược <strong>không phải điềm xui</strong> — nó chỉ ra 1 trong 4 sắc thái: năng lượng đang hướng vào nội tâm, bị trì hoãn tạm thời, đang thừa/thiếu cân bằng hoặc đang trong quá trình chuyển hóa.</li>
                        </ul>
                    </article>
                </div>
            </div>
        </section>

        <!-- SEO Pillar Section C: Hub 8 Lá Bài RWS Tiêu Biểu (Internal Links tới 78 bài SEO) -->
        <section class="mayhen_tarot_section">
            <div class="mayhen_tarot_container">
                <div class="mayhen_tarot_section_head" style="max-width: none; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <div class="mayhen_tarot_kicker">TRA CỨU Ý NGHĨA 78 LÁ BÀI TAROT</div>
                        <h2 class="mayhen_tarot_h2" style="margin-bottom: 4px;">Các lá bài Rider–Waite–Smith được tìm hiểu nhiều nhất</h2>
                    </div>
                    <a href="{{ route('tarot.library') }}" class="mayhen_tarot_btn_outline_dark">Xem toàn bộ 78 lá bài →</a>
                </div>
                @php
                    $featuredSlugs = ['the-fool', 'the-lovers', 'strength', 'justice', 'death', 'the-sun', 'two-of-cups', 'nine-of-cups'];
                    $featuredCards = collect($cardsLight)->whereIn('slug', $featuredSlugs)->values();
                @endphp
                <div class="mayhen_tarot_seo_featured_grid">
                    @foreach ($featuredCards as $fc)
                        <a href="{{ route('tarot.card_detail', $fc['slug']) }}" class="mayhen_tarot_related_card">
                            <img src="{{ asset('assets/client/images/tarot/rws/' . $fc['slug'] . '.jpg') }}"
                                 alt="Ý nghĩa lá bài Tarot {{ $fc['name_en'] }} ({{ $fc['name_vi'] }}) chuẩn Rider–Waite–Smith"
                                 width="96"
                                 height="165"
                                 loading="lazy"
                                 decoding="async"
                                 class="mayhen_tarot_rws_thumb">
                            <strong>{{ $fc['name_en'] }}</strong>
                            <span>{{ $fc['name_vi'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- SEO Pillar Section D: 6 Câu Hỏi Thường Gặp (Đồng bộ 100% Schema FAQPage) -->
        <section class="mayhen_tarot_section">
            <div class="mayhen_tarot_container">
                <div class="mayhen_tarot_seo_article_card">
                    <div class="mayhen_tarot_kicker">GIẢI ĐÁP THẮC MẮC (FAQ)</div>
                    <h2 class="mayhen_tarot_h2">Câu hỏi thường gặp khi Xem Tarot Online tại Mây Hẹn</h2>
                    <div class="mayhen_tarot_faq_accordion">
                        <details class="mayhen_tarot_faq_details" open>
                            <summary>Xem Tarot Online tại Mây Hẹn dùng bộ bài nào và có ngẫu nhiên thật không?</summary>
                            <p>Mây Hẹn sử dụng chuẩn 78 lá <strong>Rider–Waite–Smith gốc 1909</strong> (Strength VIII, Justice XI). Mỗi phiên trải bài được xáo đủ 78 lá trên máy chủ bằng thuật toán <strong>Fisher–Yates CSPRNG (<code>random_int</code>)</strong>, rút không hoàn lại 100% và khóa trước bằng mã băm <strong>SHA-256 Commitment Hash</strong>. AI không bao giờ chọn hay thay đổi lá bài bạn đã rút.</p>
                        </details>
                        <details class="mayhen_tarot_faq_details">
                            <summary>Xem Tarot Online tại Mây Hẹn có miễn phí không?</summary>
                            <p>Hoàn toàn miễn phí cho cả 3 cấp độ <strong>Xem 1 Lá, Xem 3 Lá và Xem Chuyên Sâu 5 Lá</strong>. Khi đăng nhập tài khoản Mây Hẹn miễn phí, bạn được mở khóa thêm phân tích AI 6 tầng cá nhân hóa, Hỏi Sâu sau khi rút, trải bài 7 lá Hành Trình Nhân Duyên, Celtic Cross 10 lá và lưu Sổ Bài Tarot riêng tư.</p>
                        </details>
                        <details class="mayhen_tarot_faq_details">
                            <summary>Nên chọn xem Tarot 1 lá, 3 lá hay chuyên sâu 5 lá?</summary>
                            <p>Hãy chọn <strong>1 Lá</strong> khi cần thông điệp nhanh mỗi ngày hoặc lời khuyên ngắn gọn; chọn <strong>3 Lá</strong> để thấy mạch diễn tiến Quá khứ – Hiện tại – Hướng tới hoặc 3 góc nhìn Tình yêu; chọn <strong>Chuyên Sâu 5 Lá</strong> khi đứng trước lựa chọn lớn cần phân tích cả nguyên nhân ẩn sâu, rào cản và nguồn lực.</p>
                        </details>
                        <details class="mayhen_tarot_faq_details">
                            <summary>Rút trúng lá bài Tarot Ngược (Reversed) có phải là điềm xấu không?</summary>
                            <p>Không. Lá bài Ngược không mang nghĩa xui xẻo hay tai họa. Nó đơn giản nhắc rằng năng lượng của lá bài đang diễn ra âm thầm bên trong, bị chững lại tạm thời hoặc cần bạn điều chỉnh lại mức độ cân bằng.</p>
                        </details>
                        <details class="mayhen_tarot_faq_details">
                            <summary>Có nên rút lại nhiều lần cho cùng một câu hỏi Tarot không?</summary>
                            <p>Không nên. Rút lặp nhiều lần liên tiếp cho cùng một câu hỏi trong vài phút chỉ làm rối góc nhìn. Bạn nên sử dụng khung <strong>Hỏi Sâu</strong> ngay trên trải bài vừa mở để đào kỹ ý nghĩa từng lá, và đợi khi hoàn cảnh thực tế có chuyển biến mới hãy trải bài lại.</p>
                        </details>
                        <details class="mayhen_tarot_faq_details">
                            <summary>Tarot có dùng để chẩn đoán bệnh, khẳng định ngoại tình hay dự đoán xổ số không?</summary>
                            <p>Tuyệt đối không. Tarot tại Mây Hẹn là công cụ chiêm nghiệm tâm lý và định hướng hành động tích cực; không thay thế bác sĩ, chuyên gia tâm lý, luật sư hay chuyên gia tài chính.</p>
                        </details>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <!-- 4. Quick Links to Public Tarot Ecosystem -->
    <section class="mayhen_tarot_section">
        <div class="mayhen_tarot_container">
            <div class="mayhen_tarot_editorial_card" style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 18px; background: #F3EBE0;">
                <div>
                    <div class="mayhen_tarot_kicker">THƯ VIỆN KIẾN THỨC MỞ</div>
                    <h2 class="mayhen_tarot_h2" style="margin-bottom: 6px;">Khám phá Bách Khoa 78 Lá Bài Rider–Waite–Smith</h2>
                    <p class="mayhen_tarot_lead">Tra cứu ý nghĩa Xuôi &amp; Ngược, biểu tượng hình ảnh, 8 lĩnh vực đời sống và những hiểu lầm thường gặp của từng lá bài.</p>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="{{ route('tarot.library') }}" class="mayhen_tarot_btn_editorial">Mở Bách Khoa 78 Lá →</a>
                    <a href="{{ route('tarot.celtic') }}" class="mayhen_tarot_btn_outline_dark">Trải bài Celtic Cross 10 lá</a>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="application/json" id="mayhenTarotConfig">{!! json_encode([
    'presetSpread' => $presetSpread,
    'presetCategory' => $presetCategory,
    'spreads' => $spreads,
    'cards' => $cardsLight,
    'endpoints' => [
        'createSession' => route('tarot.session.create'),
        'selectSlotTemplate' => route('tarot.session.select', ['sessionToken' => '__TOKEN__']),
        'completeSessionTemplate' => route('tarot.session.complete', ['sessionToken' => '__TOKEN__']),
        'manual' => route('tarot.manual'),
        'suggestQuestion' => route('tarot.suggest_question'),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endsection

@push('scripts')
    <script src="{{ asset('assets/client/js/pages/tarot.js') }}?v={{ filemtime(public_path('assets/client/js/pages/tarot.js')) }}" defer></script>
@endpush
