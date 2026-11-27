@extends('client.layouts.master')

@section('title', 'Kết Quả Trắc Nghiệm Tính Cách — '.($session->effectiveType() ?? 'Hồ Sơ Cá Nhân'))
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
        <span class="mayhen_personality_kicker">Mây Hẹn • The Art of Personality</span>
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
            Bỏ qua hiệu ứng
        </button>
    </div>
@endif

<main class="mayhen_personality_page">
    {{-- CHƯƠNG 1 & 2: BỨC CHÂN DUNG & MÃ NHÓM --}}
    <section class="mayhen_personality_hero">
        <div class="mayhen_personality_container">
            @if(session('success'))
                <div class="mayhen_personality_notice" style="margin-bottom:20px; border-left-color:var(--mayhen-personality-jade);">{{ session('success') }}</div>
            @endif

            <div class="mayhen_personality_hero_grid">
                <div>
                    <span class="mayhen_personality_kicker">Chương 1 & 2 • Hồ sơ riêng tư • {{ $method['name'] }}</span>
                    @if($isBigFive)
                        <h1 class="mayhen_personality_h1">Hồ Sơ Big Five (OCEAN)</h1>
                        <p class="mayhen_personality_lead">
                            Kết quả đo 5 đặc điểm tính cách độc lập theo thang IPIP-50 (Public Domain). Không hiển thị Percentile dân số vì chưa có mẫu chuẩn hóa Việt Nam.
                        </p>
                    @elseif($typeInfo)
                        <h1 class="mayhen_personality_h1">{{ $effectiveCode }} — {{ $typeInfo['art_title'] }}</h1>
                        <p style="font-size:1.12rem; font-weight:600; color:var(--mayhen-personality-crimson); margin:0 0 12px;">
                            {{ $typeInfo['vietnamese_name'] }} ({{ $typeInfo['group'] }})
                        </p>
                        <p class="mayhen_personality_lead">{{ $typeInfo['theory_summary'] }}</p>
                        @if($session->best_fit_type && $session->best_fit_type !== $session->suggested_type)
                            <div class="mayhen_personality_notice" style="margin-bottom:16px;">
                                <strong>Phân biệt minh bạch:</strong> Nhóm đề xuất từ thuật toán là <strong>{{ $session->suggested_type }}</strong> • Nhóm bạn tự đối chiếu phù hợp (Best-Fit) là <strong>{{ $session->best_fit_type }}</strong>. Điểm gốc được bảo toàn 100%.
                            </div>
                        @endif
                    @else
                        <h1 class="mayhen_personality_h1">Chưa nghiêng rõ về 1 mã duy nhất</h1>
                        <p class="mayhen_personality_lead">
                            Một hoặc nhiều chiều đạt đúng điểm giữa (50/50) hoặc chưa đủ câu trả lời hợp lệ. Mây Hẹn không chọn ngẫu nhiên — các nhóm khả dĩ của bạn là:
                            <strong>{{ implode(', ', (array) ($session->possible_types_json ?? [])) }}</strong>.
                        </p>
                    @endif

                    <div class="mayhen_personality_actions">
                        <a href="{{ route('personality.love') }}" class="mayhen_personality_btn mayhen_personality_btn--crimson mayhen_personality_btn--sm">
                            Mời người yêu đối chiếu cặp đôi
                        </a>
                        <button type="button" id="mayhen_personality_3d_toggle" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">
                            Chế độ 3D Tương tác
                        </button>
                        <a href="{{ route('personality.start') }}" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">
                            Làm bài mới
                        </a>
                    </div>
                </div>

                <div class="mayhen_personality_sculpture_stage">
                    <div class="mayhen_personality_sculpture_card" data-mode="3d" style="max-width:290px; width:100%;">
                        {!! $artworkSvg !!}
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
                    <strong>Ghi nhận chất lượng phiên làm bài (không tự sửa điểm gốc):</strong>
                    <ul style="margin:6px 0 0 18px;">
                        @foreach($qualityFlags['notes'] as $note)
                            <li>{{ $note }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    {{-- CHƯƠNG 3 & 4: BIỂU ĐỒ SPECTRUM + NÚT XEM CĂN CỨ --}}
    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <span class="mayhen_personality_kicker">Chương 3 & 4 • Điểm số xác định & Nút Xem Căn Cứ</span>
            <h2 class="mayhen_personality_h2">{{ $isBigFive ? 'Năm Yếu Tố Big Five (Thang chuẩn hóa 0–100)' : 'Bốn Chiều Xu Hướng (Biểu đồ hai phía)' }}</h2>
            <p class="mayhen_personality_sub">
                Mọi chỉ số đều thể hiện vị trí trên thang điểm nội bộ chuẩn hóa — không phải phần trăm xác suất hay thứ hạng dân số. Bấm <strong>Xem căn cứ</strong> để đối chiếu trực tiếp câu trả lời thật của bạn.
            </p>

            @if($isBigFive)
                @foreach($dimensions as $dimCode => $d)
                    <div class="mayhen_personality_spectrum_item">
                        <div class="mayhen_personality_spectrum_head">
                            <strong>{{ $d['name'] }}</strong>
                            <span>{{ $d['index'] !== null ? $d['index'].' / 100 ('.$d['band_label'].')' : 'Chưa đủ dữ liệu' }}</span>
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
                                <br><strong>Ổn định cảm xúc (Emotional Stability): {{ $d['emotional_stability_index'] }} / 100.</strong>
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
                            <div class="mayhen_personality_spectrum_band" title="Vùng ranh giới UX 45–55"></div>
                            <div class="mayhen_personality_spectrum_center"></div>
                            <div class="mayhen_personality_spectrum_marker" style="left: {{ max(3, min(97, (float) $posIdx)) }}%;"></div>
                        </div>

                        <div class="mayhen_personality_spectrum_poles">
                            <span><strong>{{ $d['negative_code'] }}</strong> ({{ $oppIdx }}) — {{ $d['negative_name'] }}</span>
                            <span><strong>{{ $d['positive_code'] }}</strong> ({{ $posIdx }}) — {{ $d['positive_name'] }}</span>
                        </div>

                        <div id="ev_{{ $dimCode }}" class="mayhen_personality_evidence_panel" hidden>
                            <div style="margin-bottom:8px; color:var(--mayhen-personality-muted);">
                                Dimension: <code>{{ $dimCode }}</code> • Số câu hợp lệ: {{ $d['valid_count'] }}/{{ $d['total_questions'] }} • Mean gốc: <code>{{ $d['mean'] ?? 'N/A' }}</code> • Phương pháp: <code>{{ $session->assessment_version }}</code>
                            </div>
                            <ul style="margin:0 0 0 18px; line-height:1.6;">
                                @foreach((array) ($d['evidence'] ?? []) as $ev)
                                    <li>
                                        <code>{{ $ev['question_id'] }}</code>: "{{ $ev['text'] }}" — Bạn chọn mức <strong>{{ $ev['raw_answer'] }}/7</strong> (điểm hiệu chỉnh {{ $ev['adjusted_score'] }}/7, {{ $ev['direction'] === 1 ? 'câu thuận' : 'câu đảo chiều' }}).
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
        {{-- CHƯƠNG 5–10: FUNCTION STACK & KHÁM PHÁ CHUYÊN SÂU --}}
        @if($typeInfo)
            <section class="mayhen_personality_section">
                <div class="mayhen_personality_container">
                    <span class="mayhen_personality_kicker">Chương 5–10 • Lý thuyết Chức năng Nhận thức & Đời sống</span>
                    <h2 class="mayhen_personality_h2">Bức tranh lý thuyết cho {{ $typeInfo['code'] }}</h2>
                    <div class="mayhen_personality_grid_4" style="margin-bottom:20px;">
                        @foreach(['Dominant (Chủ đạo)', 'Auxiliary (Phụ trợ)', 'Tertiary (Thứ ba)', 'Inferior (Kém phát triển)'] as $idx => $roleLabel)
                            @php $fnCode = $typeInfo['function_stack'][$idx] ?? ''; $fn = $cognitiveFunctions[$fnCode] ?? null; @endphp
                            <div class="mayhen_personality_card">
                                <div style="font-size:0.76rem; color:var(--mayhen-personality-muted);">{{ $roleLabel }}</div>
                                <div class="mayhen_personality_type_code" style="margin:4px 0;">{{ $fnCode }}</div>
                                <div style="font-size:0.86rem; line-height:1.55;">{{ $fn['definition'] ?? '' }}</div>
                            </div>
                        @endforeach
                    </div>
                    <p style="font-size:0.84rem; color:var(--mayhen-personality-muted); margin-bottom:24px;">
                        <em>Lưu ý khoa học: Function Stack được suy ra theo quy ước lý thuyết từ mã nhóm, KHÔNG phải điểm đo trực tiếp 8 chức năng nhận thức hay vùng não.</em>
                    </p>

                    <div class="mayhen_personality_grid_2">
                        <div class="mayhen_personality_card">
                            <h3 class="mayhen_personality_h3">Điểm mạnh tiềm năng</h3>
                            <ul style="margin:8px 0 0 18px; line-height:1.65;">
                                @foreach($typeInfo['strengths'] as $st)<li>{{ $st }}</li>@endforeach
                            </ul>
                            <h3 class="mayhen_personality_h3" style="margin-top:18px;">Tình huống cần lưu ý</h3>
                            <ul style="margin:8px 0 0 18px; line-height:1.65;">
                                @foreach($typeInfo['watch_outs'] as $wo)<li>{{ $wo }}</li>@endforeach
                            </ul>
                        </div>
                        <div class="mayhen_personality_card">
                            <h3 class="mayhen_personality_h3">Giao tiếp & Tình yêu</h3>
                            <p style="line-height:1.65; margin:6px 0 14px;">{{ $typeInfo['communication_style'] }} {{ $typeInfo['love_theme'] }}</p>
                            <h3 class="mayhen_personality_h3">Công việc, Học tập & Gia đình</h3>
                            <p style="line-height:1.65; margin:6px 0 0;">{{ $typeInfo['work_learning_style'] }} {{ $typeInfo['family_theme'] }}</p>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        {{-- CHƯƠNG 11: BEST-FIT EXPLORATION --}}
        <section class="mayhen_personality_section">
            <div class="mayhen_personality_container">
                <span class="mayhen_personality_kicker">Chương 11 • Best-Fit Exploration</span>
                <h2 class="mayhen_personality_h2">Tự đối chiếu nhóm phù hợp nhất với bạn</h2>
                <p class="mayhen_personality_sub">
                    Nếu có chiều gần ranh giới (45–55) hoặc bạn thấy mô tả nhóm gần kề sát với mình hơn, bạn có thể chọn lưu nhóm <strong>Best-Fit</strong>. Hệ thống luôn giữ nguyên điểm trắc nghiệm gốc.
                </p>

                @if($alternativeInfos !== [])
                    <div class="mayhen_personality_grid_3" style="margin-bottom:20px;">
                        @foreach($alternativeInfos as $altCode => $alt)
                            <div class="mayhen_personality_card">
                                <div class="mayhen_personality_type_code">{{ $altCode }} — {{ $alt['art_title'] }}</div>
                                <div style="font-size:0.86rem; color:var(--mayhen-personality-muted); margin:6px 0 10px;">{{ $alt['theory_summary'] }}</div>
                                <a href="{{ route('personality.type', ['typeCode' => strtolower($altCode)]) }}" style="font-size:0.85rem; font-weight:600; color:var(--mayhen-personality-jade);">Xem chi tiết {{ $altCode }} →</a>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('personality.result.best_fit', ['slug' => $session->slug]) }}" class="mayhen_personality_card">
                    @csrf
                    <div class="mayhen_personality_grid_2">
                        <div>
                            <label for="best_fit_type" style="display:block; font-weight:600; margin-bottom:6px;">Chọn nhóm bạn tự đối chiếu thấy phù hợp nhất</label>
                            <select id="best_fit_type" name="best_fit_type" class="mayhen_personality_select">
                                @foreach(['INTJ','INTP','ENTJ','ENTP','INFJ','INFP','ENFJ','ENFP','ISTJ','ISFJ','ESTJ','ESFJ','ISTP','ISFP','ESTP','ESFP'] as $codeOpt)
                                    <option value="{{ $codeOpt }}" {{ $effectiveCode === $codeOpt ? 'selected' : '' }}>{{ $codeOpt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="user_notes" style="display:block; font-weight:600; margin-bottom:6px;">Ghi chú hoàn cảnh hiện tại của bạn</label>
                            <input type="text" id="user_notes" name="user_notes" value="{{ $session->user_notes }}" class="mayhen_personality_input" placeholder="Ví dụ: Đang trong giai đoạn chuẩn bị đám cưới...">
                        </div>
                    </div>
                    <button type="submit" class="mayhen_personality_btn mayhen_personality_btn--primary mayhen_personality_btn--sm" style="margin-top:14px;">
                        Lưu nhóm Best-Fit & Ghi chú
                    </button>
                </form>
            </div>
        </section>

        {{-- CHƯƠNG 12: AI PHÂN TÍCH 12 TẦNG + COACH + GROWTH PLAN --}}
        <section class="mayhen_personality_section">
            <div class="mayhen_personality_container">
                <span class="mayhen_personality_kicker">Chương 12 • AI Luận Giải 12 Tầng Có Căn Cứ & Personality Coach</span>
                <h2 class="mayhen_personality_h2">Phân tích chuyên sâu dựa trên câu trả lời thực tế</h2>

                @if(! empty($aiReport['layers']))
                    <div class="mayhen_personality_grid_2" style="margin-bottom:28px;">
                        @foreach($aiReport['layers'] as $layer)
                            <div class="mayhen_personality_card">
                                <h3 class="mayhen_personality_h3">{{ $layer['title'] ?? 'Tầng phân tích' }}</h3>
                                <p style="font-size:0.92rem; line-height:1.65; margin:6px 0 8px;">{{ $layer['content'] ?? '' }}</p>
                                @if(! empty($layer['evidence_note']))
                                    <div style="font-size:0.8rem; color:var(--mayhen-personality-muted);">{{ $layer['evidence_note'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mayhen_personality_grid_2">
                    <div class="mayhen_personality_card">
                        <h3 class="mayhen_personality_h3">AI Personality Coach (Hỏi sâu bám sát hồ sơ)</h3>
                        <form id="mayhen_personality_coach_form" method="POST" action="{{ route('personality.result.coach', ['slug' => $session->slug]) }}">
                            @csrf
                            <div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:10px;">
                                @foreach(['Vì sao tôi được đề xuất nhóm này?', 'Vì sao chiều gần ranh giới của tôi lại cân bằng?', 'Tôi nên giao tiếp với người yêu thế nào?'] as $chip)
                                    <button type="button" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm" data-coach-prompt="{{ $chip }}">{{ $chip }}</button>
                                @endforeach
                            </div>
                            <textarea name="question" rows="3" class="mayhen_personality_textarea" placeholder="Đặt câu hỏi về hồ sơ tính cách của bạn..." required></textarea>
                            <button type="submit" class="mayhen_personality_btn mayhen_personality_btn--primary mayhen_personality_btn--sm" style="margin-top:10px;">
                                Hỏi AI Coach
                            </button>
                        </form>
                        <div id="mayhen_personality_coach_list">
                            @foreach($session->followups as $f)
                                <div class="mayhen_personality_card" style="margin-top:12px;">
                                    <p style="font-weight:700; margin:0;">Bạn hỏi: {{ $f->question }}</p>
                                    <p style="margin:8px 0 0; line-height:1.65;">{{ $f->answer }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mayhen_personality_card">
                        <h3 class="mayhen_personality_h3">Personality Growth Plan (7 / 14 / 30 ngày)</h3>
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
                                    Tạo kế hoạch linh hoạt
                                </button>
                            </div>
                        </form>

                        @if(! empty($session->growth_plan_json['milestones']))
                            <div style="margin-top:16px; border-top:1px solid var(--mayhen-personality-border); padding-top:14px;">
                                <strong>{{ $session->growth_plan_json['goal_label'] }} ({{ $session->growth_plan_json['days'] }} ngày)</strong>
                                @foreach($session->growth_plan_json['milestones'] as $ms)
                                    <div style="margin-top:10px;">
                                        <div style="font-weight:600; font-size:0.9rem; color:var(--mayhen-personality-jade);">{{ $ms['phase'] }} — {{ $ms['title'] }}</div>
                                        <ul style="margin:4px 0 0 18px; font-size:0.86rem; line-height:1.55;">
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

    {{-- CHƯƠNG 13: SHARE CARD PNG + QUYỀN RIÊNG TƯ + PHƯƠNG PHÁP & GIỚI HẠN --}}
    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <div class="mayhen_personality_grid_2">
                <div class="mayhen_personality_card" id="mayhen_personality_share_meta"
                     data-code="{{ $effectiveCode }}"
                     data-art-title="{{ $typeInfo['art_title'] ?? 'Bản Sắc Tâm Hồn' }}"
                     data-vn-name="{{ $typeInfo['vietnamese_name'] ?? 'Hồ sơ Big Five OCEAN' }}"
                     data-summary="{{ $typeInfo['theory_summary'] ?? 'Khám phá bản sắc tâm hồn tại Mây Hẹn' }}">
                    <h3 class="mayhen_personality_h3">Tải Share Card Nghệ Thuật (PNG) & Chia sẻ an toàn</h3>
                    <p style="font-size:0.88rem; color:var(--mayhen-personality-muted); line-height:1.6;">
                        Thẻ chia sẻ chỉ hiển thị mã nhóm & hình tượng nghệ thuật — tuyệt đối không lộ câu trả lời chi tiết của bạn.
                    </p>
                    <div class="mayhen_personality_actions">
                        <button type="button" data-export-ratio="1:1" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">Tải PNG 1:1</button>
                        <button type="button" data-export-ratio="4:5" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">Tải PNG 4:5</button>
                        <button type="button" data-export-ratio="9:16" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">Tải Story 9:16</button>
                    </div>

                    <form method="POST" action="{{ route('personality.result.share', ['slug' => $session->slug]) }}" style="margin-top:14px;">
                        @csrf
                        <button type="submit" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">
                            {{ $session->is_shared ? 'Thu hồi liên kết chia sẻ công khai' : 'Tạo liên kết thẻ chia sẻ công khai' }}
                        </button>
                    </form>
                    @if($session->is_shared && $session->share_token)
                        <div style="margin-top:10px; font-size:0.84rem;">
                            Liên kết chia sẻ: <a href="{{ route('personality.shared_card', ['shareToken' => $session->share_token]) }}" target="_blank">{{ route('personality.shared_card', ['shareToken' => $session->share_token]) }}</a>
                        </div>
                    @endif
                </div>

                <div class="mayhen_personality_card">
                    <h3 class="mayhen_personality_h3">Chương 13 • Minh bạch Phương pháp & Quản lý dữ liệu</h3>
                    <p style="font-size:0.86rem; color:var(--mayhen-personality-muted); line-height:1.65;">
                        Phiên bản: <code>{{ $session->assessment_version }}</code> • Calculation Hash: <code>{{ substr((string) $session->calculation_hash, 0, 16) }}</code>.<br>
                        {{ $method['trademark_disclaimer'] }}<br>
                        {{ $method['validation_note'] }}
                    </p>
                    <form method="POST" action="{{ route('personality.result.destroy', ['slug' => $session->slug]) }}" onsubmit="return confirm('Xóa vĩnh viễn hồ sơ trắc nghiệm này?');" style="margin-top:12px;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm" style="color:var(--mayhen-personality-crimson); border-color:var(--mayhen-personality-crimson);">
                            Xóa hồ sơ này ngay
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>
@endsection
