@extends('client.layouts.master')

@section('title', 'Kết Quả Tính Cách Của Bạn — '.($session->effectiveType() ?? 'Bản Sắc Tâm Hồn'))
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/personality.css') }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/personality.js') }}"></script>
@endpush

@section('content')
@php
    $scores = (array) ($session->scores_json ?? []);
    $dimensions = (array) ($scores['dimensions'] ?? []);
    $isBigFive = $session->model_code === 'big_five';
    $effectiveCode = $session->effectiveType() ?? 'UNDECIDED';
    $qualityFlags = (array) ($session->quality_flags_json ?? []);
    $aiReport = (array) ($session->ai_report_json ?? []);
    $revealLetters = $isBigFive
        ? ['O', 'C', 'E', 'A', 'N']
        : ($effectiveCode === 'UNDECIDED' ? ['?', '?', '?', '?'] : str_split($effectiveCode));
@endphp

@if(request()->query('skip_reveal') !== '1')
    <div id="mayhen_personality_reveal_overlay" class="mayhen_personality_reveal" role="dialog" aria-label="Mở kết quả tính cách">
        <span class="mayhen_personality_kicker">Mây Hẹn • Bản Sắc Tâm Hồn</span>
        <h2 class="mayhen_personality_h2" style="margin-bottom:4px;">Bức chân dung tính cách của bạn</h2>
        <div class="mayhen_personality_reveal_letters">
            @foreach($revealLetters as $ch)
                <div class="mayhen_personality_reveal_letter">{{ $ch }}</div>
            @endforeach
        </div>
        @if($typeInfo)
            <div style="font-size:1.1rem; font-weight:700; color:var(--mayhen-personality-crimson);">{{ $typeInfo['art_title'] }} — {{ $typeInfo['vietnamese_name'] }}</div>
        @endif
        <button type="button" id="mayhen_personality_reveal_skip" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm" style="margin-top:20px;">
            Xem kết quả ngay
        </button>
    </div>
@endif

<main class="mayhen_personality_page" style="padding-bottom: 120px;">
    {{-- PHẦN 1: TỔNG QUAN CHÂN DUNG TÍNH CÁCH --}}
    <section class="mayhen_personality_hero">
        <div class="mayhen_personality_container">
            @if(session('success'))
                <div class="mayhen_personality_notice" style="margin-bottom:20px; border-left-color:var(--mayhen-personality-jade);">{{ session('success') }}</div>
            @endif

            <div class="mayhen_personality_hero_grid">
                <div>
                    <span class="mayhen_personality_kicker">Hồ sơ tính cách riêng của bạn • {{ $method['name'] }}</span>
                    @if($isBigFive)
                        <h1 class="mayhen_personality_h1">Bức Chân Dung 5 Yếu Tố Tính Cách (Big Five)</h1>
                        <p class="mayhen_personality_lead">
                            Đây là bản phác họa 5 nét tính cách nổi bật của bạn trên thang điểm từ 0 đến 100, giúp bạn hiểu rõ cách mình đón nhận điều mới, làm việc, giao tiếp và giữ cân bằng cảm xúc.
                        </p>
                    @elseif($typeInfo)
                        <h1 class="mayhen_personality_h1">{{ $effectiveCode }} — {{ $typeInfo['art_title'] }}</h1>
                        <p style="font-size:1.12rem; font-weight:600; color:var(--mayhen-personality-crimson); margin:0 0 12px;">
                            {{ $typeInfo['vietnamese_name'] }} ({{ $typeInfo['group'] }})
                        </p>
                        <p class="mayhen_personality_lead">{{ $typeInfo['theory_summary'] }}</p>
                        @if($session->best_fit_type && $session->best_fit_type !== $session->suggested_type)
                            <div class="mayhen_personality_notice" style="margin-bottom:16px;">
                                Kết quả từ bài làm là <strong>{{ $session->suggested_type }}</strong> • Nhóm bạn tự chọn thấy hợp nhất với mình là <strong>{{ $session->best_fit_type }}</strong>.
                            </div>
                        @endif
                    @else
                        <h1 class="mayhen_personality_h1">Tính cách của bạn đang cân bằng giữa nhiều xu hướng</h1>
                        <p class="mayhen_personality_lead">
                            Một hoặc nhiều nét tính cách của bạn đang nằm ở điểm giữa rất linh hoạt (hoặc còn thiếu câu trả lời). Bạn có thể làm lại bài để có kết quả rõ nét hơn, hoặc chọn nhóm bạn thấy giống mình nhất bên dưới.
                        </p>
                    @endif

                    <div class="mayhen_personality_actions">
                        <a href="{{ route('personality.love') }}" class="mayhen_personality_btn mayhen_personality_btn--crimson mayhen_personality_btn--sm">
                            So sánh với người yêu
                        </a>
                        <button type="button" id="mayhen_personality_3d_toggle" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">
                            Chuyển chế độ 3D / 2D
                        </button>
                        <a href="{{ route('personality.start') }}" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">
                            Làm lại bài mới
                        </a>
                    </div>
                </div>

                <div class="mayhen_personality_sculpture_stage">
                    <div class="mayhen_personality_sculpture_card" data-mode="3d" style="max-width:310px; width:100%;">
                        @include('client.pages.personality.partials.sculpture-3d', [
                            'typeInfo' => $typeInfo,
                            'canvasHeight' => 230,
                            'fallbackSvg' => $artworkSvg,
                        ])
                        @if($typeInfo)
                            <div style="margin-top:10px; font-size:0.84rem; color:var(--mayhen-personality-muted);">
                                <strong>{{ $typeInfo['symbol_name'] }}</strong> — {{ $typeInfo['art_story'] }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if(! empty($qualityFlags['notes']))
                <div class="mayhen_personality_notice" style="margin-top:24px;">
                    <strong>Lưu ý nhỏ về bài làm vừa rồi:</strong>
                    <ul style="margin:6px 0 0 18px;">
                        @foreach($qualityFlags['notes'] as $note)
                            <li>{{ $note }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    {{-- PHẦN 2: BỐN NÉT TÍNH CÁCH & XEM CÂU TRẢ LỜI --}}
    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <span class="mayhen_personality_kicker">Chi tiết từng khía cạnh</span>
            <h2 class="mayhen_personality_h2">{{ $isBigFive ? '5 Yếu Tố Tính Cách Của Bạn' : '4 Nét Tính Cách Nổi Bật Của Bạn' }}</h2>
            <p class="mayhen_personality_sub">
                Điểm số cho thấy xu hướng tự nhiên của bạn nghiêng về phía nào hơn. Bấm <strong>Xem căn cứ</strong> ở từng mục để xem lại chính những câu bạn vừa trả lời.
            </p>

            @if($isBigFive)
                @foreach($dimensions as $dimCode => $d)
                    <div class="mayhen_personality_spectrum_item">
                        <div class="mayhen_personality_spectrum_head">
                            <strong>{{ $d['name'] }}</strong>
                            <span>{{ $d['index'] !== null ? $d['index'].' / 100 ('.$d['band_label'].')' : 'Chưa đủ câu trả lời' }}</span>
                        </div>
                        <div class="mayhen_big_five_bar_track">
                            <div class="mayhen_big_five_bar_fill" style="width: {{ max(0, min(100, (float) ($d['index'] ?? 0))) }}%;"></div>
                        </div>
                        <div class="mayhen_personality_spectrum_poles">
                            <span>{{ $d['low_label'] }}</span>
                            <span>{{ $d['high_label'] }}</span>
                        </div>
                        <p style="font-size:0.88rem; color:var(--mayhen-personality-muted); margin:10px 0 0;">
                            {{ $d['summary'] }}
                            @if($dimCode === 'N' && isset($d['emotional_stability_index']))
                                <br><strong>Độ vững vàng cảm xúc: {{ $d['emotional_stability_index'] }} / 100.</strong>
                            @endif
                        </p>
                    </div>
                @endforeach
            @else
                @foreach($dimensions as $dimCode => $d)
                    @php
                        $posIdx = $d['positive_index'] ?? 50;
                        $oppIdx = $d['opposite_index'] ?? 50;
                    @endphp
                    <div class="mayhen_personality_spectrum_item">
                        <div class="mayhen_personality_spectrum_head">
                            <div>
                                <strong>{{ $d['name'] }} ({{ $d['positive_code'] }} / {{ $d['negative_code'] }})</strong>
                                <div style="font-size:0.86rem; color:var(--mayhen-personality-muted); margin-top:2px;">
                                    {{ $d['tendency_label'] }}
                                </div>
                            </div>
                            <button type="button" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm" data-evidence-toggle="ev_{{ $dimCode }}" aria-expanded="false">
                                Xem căn cứ ({{ count((array) ($d['evidence'] ?? [])) }})
                            </button>
                        </div>

                        <div class="mayhen_personality_spectrum_bar" aria-label="{{ $d['name'] }}">
                            <div class="mayhen_personality_spectrum_band" title="Vùng cân bằng giữa hai phía"></div>
                            <div class="mayhen_personality_spectrum_center"></div>
                            <div class="mayhen_personality_spectrum_marker" style="left: {{ max(3, min(97, (float) $posIdx)) }}%;"></div>
                        </div>

                        <div class="mayhen_personality_spectrum_poles">
                            <span><strong>{{ $d['negative_code'] }}</strong> ({{ $oppIdx }}) — {{ $d['negative_name'] }}</span>
                            <span><strong>{{ $d['positive_code'] }}</strong> ({{ $posIdx }}) — {{ $d['positive_name'] }}</span>
                        </div>

                        <div id="ev_{{ $dimCode }}" class="mayhen_personality_evidence_panel" hidden>
                            <div style="margin-bottom:8px; color:var(--mayhen-personality-muted);">
                                Những câu trả lời thể hiện rõ nhất nét tính cách này của bạn ({{ $d['valid_count'] }}/{{ $d['total_questions'] }} câu đã trả lời):
                            </div>
                            <ul style="margin:0 0 0 18px; line-height:1.65;">
                                @foreach((array) ($d['evidence'] ?? []) as $ev)
                                    <li>
                                        "{{ $ev['text'] }}" — Bạn chọn mức <strong>{{ $ev['raw_answer'] }}/7</strong>.
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </section>

    @if(! $isBigFive)
        @if($typeInfo)
            <section class="mayhen_personality_section">
                <div class="mayhen_personality_container">
                    <span class="mayhen_personality_kicker">Cách bạn suy nghĩ, yêu thương & làm việc</span>
                    <h2 class="mayhen_personality_h2">Hiểu sâu hơn về nhóm {{ $typeInfo['code'] }} ({{ $typeInfo['art_title'] }})</h2>

                    <div class="mayhen_personality_grid_2" style="margin-bottom:20px;">
                        <div class="mayhen_personality_card">
                            <h3 class="mayhen_personality_h3">Điểm mạnh nổi bật</h3>
                            <ul style="margin:8px 0 0 18px; line-height:1.7;">
                                @foreach($typeInfo['strengths'] as $st)<li>{{ $st }}</li>@endforeach
                            </ul>
                            <h3 class="mayhen_personality_h3" style="margin-top:18px;">Điều bạn nên lưu tâm</h3>
                            <ul style="margin:8px 0 0 18px; line-height:1.7;">
                                @foreach($typeInfo['watch_outs'] as $wo)<li>{{ $wo }}</li>@endforeach
                            </ul>
                        </div>
                        <div class="mayhen_personality_card">
                            <h3 class="mayhen_personality_h3">Trong giao tiếp & tình yêu</h3>
                            <p style="line-height:1.7; margin:6px 0 14px;">{{ $typeInfo['communication_style'] }} {{ $typeInfo['love_theme'] }}</p>
                            <h3 class="mayhen_personality_h3">Trong công việc & gia đình</h3>
                            <p style="line-height:1.7; margin:6px 0 0;">{{ $typeInfo['work_learning_style'] }} {{ $typeInfo['family_theme'] }}</p>
                        </div>
                    </div>

                    <div class="mayhen_personality_grid_4">
                        @foreach(['Nét dẫn dắt chính', 'Nét bổ trợ', 'Nét phát triển thêm', 'Nét dễ mệt khi căng thẳng'] as $idx => $roleLabel)
                            @php $fnCode = $typeInfo['function_stack'][$idx] ?? ''; $fn = $cognitiveFunctions[$fnCode] ?? null; @endphp
                            <div class="mayhen_personality_card">
                                <div style="font-size:0.78rem; color:var(--mayhen-personality-muted);">{{ $roleLabel }}</div>
                                <div class="mayhen_personality_type_code" style="margin:4px 0;">{{ $fnCode }}</div>
                                <div style="font-size:0.86rem; line-height:1.6;">{{ $fn['definition'] ?? '' }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- PHẦN 3: SO SÁNH NHÓM GẦN GIỐNG BẠN --}}
        <section class="mayhen_personality_section">
            <div class="mayhen_personality_container">
                <span class="mayhen_personality_kicker">Tự đối chiếu bản thân</span>
                <h2 class="mayhen_personality_h2">So sánh với các nhóm gần giống bạn</h2>
                <p class="mayhen_personality_sub">
                    Nếu có điểm nằm gần ở giữa, bạn có thể đọc thêm các nhóm gần kề và chọn lưu nhóm mà bạn thấy đúng với mình nhất ngoài đời thực.
                </p>

                @if($alternativeInfos !== [] && count($alternativeInfos) <= 4)
                    <div class="mayhen_personality_grid_3" style="margin-bottom:20px;">
                        @foreach($alternativeInfos as $altCode => $alt)
                            <div class="mayhen_personality_card">
                                <div class="mayhen_personality_type_code">{{ $altCode }} — {{ $alt['art_title'] }}</div>
                                <div style="font-size:0.88rem; color:var(--mayhen-personality-muted); margin:6px 0 10px; line-height:1.6;">{{ $alt['theory_summary'] }}</div>
                                <a href="{{ route('personality.type', ['typeCode' => strtolower($altCode)]) }}" style="font-size:0.85rem; font-weight:600; color:var(--mayhen-personality-jade);">Xem chi tiết {{ $altCode }} →</a>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('personality.result.best_fit', ['slug' => $session->slug]) }}" class="mayhen_personality_card">
                    @csrf
                    <div class="mayhen_personality_grid_2">
                        <div>
                            <label for="best_fit_type" style="display:block; font-weight:600; margin-bottom:6px;">Nhóm tính cách bạn thấy đúng với mình nhất</label>
                            <select id="best_fit_type" name="best_fit_type" class="mayhen_personality_select">
                                @foreach(['INTJ','INTP','ENTJ','ENTP','INFJ','INFP','ENFJ','ENFP','ISTJ','ISFJ','ESTJ','ESFJ','ISTP','ISFP','ESTP','ESFP'] as $codeOpt)
                                    <option value="{{ $codeOpt }}" {{ $effectiveCode === $codeOpt ? 'selected' : '' }}>{{ $codeOpt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="user_notes" style="display:block; font-weight:600; margin-bottom:6px;">Ghi chú nhanh cho bản thân (tùy chọn)</label>
                            <input type="text" id="user_notes" name="user_notes" value="{{ $session->user_notes }}" class="mayhen_personality_input" placeholder="Ví dụ: Giai đoạn chuẩn bị cưới, muốn hiểu rõ cách giao tiếp...">
                        </div>
                    </div>
                    <button type="submit" class="mayhen_personality_btn mayhen_personality_btn--primary mayhen_personality_btn--sm" style="margin-top:14px;">
                        Lưu lựa chọn của bạn
                    </button>
                </form>
            </div>
        </section>

        {{-- PHẦN 4: TRỢ LÝ AI TỰ ĐỘNG PHÂN TÍCH NỀN + HỎI ĐÁP AI PHẦN CHƯA HIỂU + KẾ HOẠCH RÈN LUYỆN --}}
        <section class="mayhen_personality_section">
            <div class="mayhen_personality_container">
                <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:14px; flex-wrap:wrap; margin-bottom:22px;">
                    <div>
                        <span class="mayhen_personality_kicker">Phân tích cá nhân hóa bởi AI Mây Hẹn</span>
                        <h2 class="mayhen_personality_h2" style="margin-bottom:4px;">Bản phân tích 12 khía cạnh dành riêng cho bạn</h2>
                    </div>
                    <div id="mayhen_personality_ai_auto_status"
                         class="mayhen_personality_badge_pilot"
                         data-auto-analyze="{{ $session->ai_status === 'pending' && ! empty($quota['allowed']) ? '1' : '0' }}"
                         data-analyze-url="{{ route('personality.result.ai_analyze', ['slug' => $session->slug]) }}">
                        @if(! empty($quota['tier_label']))
                            {{ $session->ai_status === 'pending' && ! empty($quota['allowed']) ? 'Trợ lý AI đang phân tích riêng cho bạn...' : 'Gói '.$quota['tier_label'].' • Còn '.($quota['remaining'] ?? 0).'/'.($quota['daily_limit'] ?? 5).' lượt AI hôm nay' }}
                        @else
                            {{ $session->ai_status === 'pending' ? 'Trợ lý AI đang hoàn thiện bài phân tích riêng cho bạn...' : 'Đã tổng hợp dựa trên câu trả lời của bạn' }}
                        @endif
                    </div>
                </div>

                @if(! empty($aiReport['layers']))
                    <div id="mayhen_personality_ai_layers_grid" class="mayhen_personality_grid_2" style="margin-bottom:28px;">
                        @foreach($aiReport['layers'] as $layer)
                            <div class="mayhen_personality_card">
                                <h3 class="mayhen_personality_h3">{{ $layer['title'] ?? '' }}</h3>
                                <p style="font-size:0.93rem; line-height:1.72; margin:6px 0 0;">{{ $layer['content'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mayhen_personality_grid_2">
                    <div class="mayhen_personality_card">
                        <h3 class="mayhen_personality_h3">Hỏi đáp riêng cùng Trợ lý AI</h3>
                        <p style="font-size:0.86rem; color:var(--mayhen-personality-muted); margin:4px 0 12px;">
                            Đặt bất kỳ câu hỏi nào về tính cách, giao tiếp, tình yêu hay công việc dựa trên đúng hồ sơ của bạn.
                        </p>
                        <form id="mayhen_personality_coach_form" method="POST" action="{{ route('personality.result.coach', ['slug' => $session->slug]) }}">
                            @csrf
                            <div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:10px;">
                                @foreach(['Vì sao kết quả của tôi lại ra nhóm này?', 'Tôi nên giao tiếp với người yêu thế nào để ít hiểu lầm?', 'Điểm mạnh lớn nhất của tôi trong công việc là gì?'] as $chip)
                                    <button type="button" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm" data-coach-prompt="{{ $chip }}">{{ $chip }}</button>
                                @endforeach
                            </div>
                            <textarea name="question" rows="3" class="mayhen_personality_textarea" placeholder="Nhập câu hỏi bạn muốn hỏi Trợ lý AI..." required></textarea>
                            <button type="submit" class="mayhen_personality_btn mayhen_personality_btn--primary mayhen_personality_btn--sm" style="margin-top:10px;">
                                Gửi câu hỏi cho AI
                            </button>
                        </form>
                        <div id="mayhen_personality_coach_list">
                            @foreach($session->followups as $f)
                                <div class="mayhen_personality_card" style="margin-top:12px;">
                                    <p style="font-weight:700; margin:0;">Bạn hỏi: {{ $f->question }}</p>
                                    <p style="margin:8px 0 0; line-height:1.7;">{{ $f->answer }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mayhen_personality_card">
                        <h3 class="mayhen_personality_h3">Gợi ý rèn luyện nhẹ nhàng (7 / 14 / 30 ngày)</h3>
                        <p style="font-size:0.86rem; color:var(--mayhen-personality-muted); margin:4px 0 12px;">
                            Chọn 1 điều bạn muốn cải thiện gần đây để nhận lộ trình thực hành nhỏ gọn, không áp lực.
                        </p>
                        <form method="POST" action="{{ route('personality.result.growth_plan', ['slug' => $session->slug]) }}">
                            @csrf
                            <div style="display:grid; gap:10px;">
                                <select name="goal_key" class="mayhen_personality_select">
                                    <option value="communication">Giao tiếp rõ ràng & thấu cảm hơn</option>
                                    <option value="listening">Lắng nghe sâu & giảm hiểu lầm</option>
                                    <option value="relationship">Thấu hiểu người yêu & chuẩn bị hôn nhân</option>
                                    <option value="time_management">Quản lý thời gian & giữ nhịp ổn định</option>
                                    <option value="work_collab">Phối hợp hiệu quả trong công việc</option>
                                    <option value="self_strengths">Phát huy điểm mạnh cá nhân</option>
                                </select>
                                <select name="days" class="mayhen_personality_select">
                                    <option value="7">Lộ trình nhẹ 7 ngày</option>
                                    <option value="14" selected>Lộ trình tiêu chuẩn 14 ngày</option>
                                    <option value="30">Lộ trình bền bỉ 30 ngày</option>
                                </select>
                                <button type="submit" class="mayhen_personality_btn mayhen_personality_btn--primary mayhen_personality_btn--sm">
                                    Tạo gợi ý rèn luyện
                                </button>
                            </div>
                        </form>

                        @if(! empty($session->growth_plan_json['milestones']))
                            <div style="margin-top:16px; border-top:1px solid var(--mayhen-personality-border); padding-top:14px;">
                                <strong>{{ $session->growth_plan_json['goal_label'] }} ({{ $session->growth_plan_json['days'] }} ngày)</strong>
                                @foreach($session->growth_plan_json['milestones'] as $ms)
                                    <div style="margin-top:10px;">
                                        <div style="font-weight:600; font-size:0.9rem; color:var(--mayhen-personality-jade);">{{ $ms['phase'] }} — {{ $ms['title'] }}</div>
                                        <ul style="margin:4px 0 0 18px; font-size:0.86rem; line-height:1.6;">
                                            @foreach($ms['actions'] as $act)<li>{{ $act }}</li>@endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- PHẦN 5: TẢI ẢNH CHIA SẺ & QUYỀN RIÊNG TƯ --}}
    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <div class="mayhen_personality_grid_2">
                <div class="mayhen_personality_card" id="mayhen_personality_share_meta"
                     data-code="{{ $effectiveCode }}"
                     data-art-title="{{ $typeInfo['art_title'] ?? 'Bản Sắc Tâm Hồn' }}"
                     data-vn-name="{{ $typeInfo['vietnamese_name'] ?? 'Hồ sơ 5 yếu tố Big Five' }}"
                     data-summary="{{ $typeInfo['theory_summary'] ?? 'Khám phá bản sắc tâm hồn tại Mây Hẹn' }}">
                    <h3 class="mayhen_personality_h3">Tải ảnh kết quả đẹp để chia sẻ</h3>
                    <p style="font-size:0.88rem; color:var(--mayhen-personality-muted); line-height:1.6;">
                        Ảnh chia sẻ chỉ gồm mã nhóm và hình tượng nghệ thuật — hoàn toàn giữ kín các câu trả lời riêng tư của bạn.
                    </p>
                    <div class="mayhen_personality_actions">
                        <button type="button" data-export-ratio="1:1" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">Ảnh vuông 1:1</button>
                        <button type="button" data-export-ratio="4:5" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">Ảnh dọc 4:5</button>
                        <button type="button" data-export-ratio="9:16" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">Ảnh Story 9:16</button>
                    </div>

                    <form method="POST" action="{{ route('personality.result.share', ['slug' => $session->slug]) }}" style="margin-top:14px;">
                        @csrf
                        <button type="submit" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">
                            {{ $session->is_shared ? 'Tắt link chia sẻ công khai' : 'Tạo link chia sẻ cho bạn bè' }}
                        </button>
                    </form>
                    @if($session->is_shared && $session->share_token)
                        <div style="margin-top:10px; font-size:0.84rem;">
                            Link chia sẻ: <a href="{{ route('personality.shared_card', ['shareToken' => $session->share_token]) }}" target="_blank">{{ route('personality.shared_card', ['shareToken' => $session->share_token]) }}</a>
                        </div>
                    @endif
                </div>

                <div class="mayhen_personality_card">
                    <h3 class="mayhen_personality_h3">Lưu ý nhỏ & Quyền riêng tư</h3>
                    <p style="font-size:0.88rem; color:var(--mayhen-personality-muted); line-height:1.68;">
                        Bài trắc nghiệm tính cách trên Mây Hẹn là công cụ tự khám phá độc lập giúp bạn hiểu mình và giao tiếp tốt hơn (không phải bài MBTI® chính thức có cấp phép và không thay thế tư vấn y tế/tâm lý). Hồ sơ này hoàn toàn riêng tư — bạn có thể xóa bất cứ lúc nào.
                    </p>
                    <form method="POST" action="{{ route('personality.result.destroy', ['slug' => $session->slug]) }}" onsubmit="return confirm('Xóa vĩnh viễn hồ sơ trắc nghiệm này?');" style="margin-top:12px;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm" style="color:var(--mayhen-personality-crimson); border-color:var(--mayhen-personality-crimson);">
                            Xóa hồ sơ này
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
