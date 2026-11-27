@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $displayName = $calc['display_name'] ?? ($reading->profile?->display_name ?: 'Hồ Sơ Con Số');
    $core = $calc['core_numbers'] ?? [];
    $ext = $calc['extended_numbers'] ?? [];
    $cycles = $calc['personal_cycles'] ?? [];
    $lp = $core['life_path'] ?? ['display' => '—', 'value' => 1];
    $nb = $calc['name_breakdown'] ?? null;
    $plan = $interpretation['layer_6_plan'] ?? [];
    $detailedIndicators = $enriched['detailed_indicators'] ?? [];
    $bridgeInsights = $enriched['bridge_insights'] ?? [];
    $careerDomains = $enriched['career_domains'] ?? ['domains' => [], 'top_roles' => []];
    $periodCyclesEnriched = $enriched['period_cycles_enriched'] ?? [];
    $twelveMonthsEnriched = $enriched['twelve_months_enriched'] ?? [];
    $strengthsList = $interpretation['strengths_list'] ?? array_values(array_filter([
        $enriched['indicator_meanings']['life_path']['strengths'] ?? null,
        $enriched['indicator_meanings']['expression']['strengths'] ?? null,
        $enriched['indicator_meanings']['soul_urge']['strengths'] ?? null,
    ]));
    $watchOutsList = $interpretation['watch_outs'] ?? array_values(array_filter([
        $enriched['indicator_meanings']['life_path']['watch_outs'] ?? null,
        $enriched['indicator_meanings']['expression']['watch_outs'] ?? null,
    ]));
    $careerDirections = $interpretation['career_directions'] ?? ($careerDomains['top_roles'] ?? []);
    $yearAspects = $interpretation['year_aspects'] ?? ($enriched['year_aspects_default'] ?? []);
    $deepInsights = $interpretation['indicator_deep_insights'] ?? [];
    $karmicMaster = $enriched['karmic_master_insights'] ?? ['masters' => [], 'karmic_debts' => []];
@endphp

@section('title', 'Hồ Sơ Con Số: ' . $displayName . ' (Đường Đời ' . ($lp['display'] ?? '') . ')')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/numerology.css') }}">
@endpush

@section('content')
<div class="mayhen_numerology_page" id="mayhenNumerologyShowApp">
    <section class="mayhen_numerology_section" style="padding: 32px 0 60px;">
        <div class="mayhen_numerology_container">
            {{-- THANH ĐIỀU HƯỚNG HỒ SƠ & PHIÊN BẢN --}}
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <a href="{{ route('numerology.index') }}" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm">&larr; Tra cứu mới</a>
                    <a href="{{ route('numerology.profiles') }}" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm">Hồ Sơ Đã Lưu</a>
                    <a href="{{ route('numerology.love.index') }}" class="mayhen_numerology_btn_primary mayhen_numerology_btn_sm">Soi Thần Số Học Tình Duyên</a>
                </div>
                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                    @if($versions->count() > 1)
                        @foreach($versions as $ver)
                            <a href="{{ route('numerology.show', $ver->slug) }}"
                               class="mayhen_numerology_btn_sm {{ $ver->id === $reading->id ? 'mayhen_numerology_btn_crimson' : 'mayhen_numerology_btn_outline' }}">
                                v{{ $ver->version_number }} ({{ $ver->viewed_year }})
                            </a>
                        @endforeach
                    @endif
                    <span style="font-size: 12px; color: #5C5248; font-weight: 700;">Hồ sơ riêng tư • v{{ $reading->version_number }}</span>
                </div>
            </div>

            {{-- MỤC LỤC 10 CHƯƠNG CUỐN HỒ SƠ --}}
            <nav class="mayhen_numerology_chapter_nav" aria-label="Mục lục 10 chương hồ sơ">
                <a href="#chuong-1" class="mayhen_numerology_chapter_link">1. Dấu ấn</a>
                <a href="#chuong-2" class="mayhen_numerology_chapter_link">2. Bộ số &amp; Cách tính</a>
                <a href="#chuong-3" class="mayhen_numerology_chapter_link">3. Biểu đồ 3×3</a>
                <a href="#chuong-4" class="mayhen_numerology_chapter_link">4. Hiểu bản thân</a>
                <a href="#chuong-5" class="mayhen_numerology_chapter_link">5. Tình yêu &amp; Hôn nhân</a>
                <a href="#chuong-6" class="mayhen_numerology_chapter_link">6. Kim tự tháp</a>
                <a href="#chuong-7" class="mayhen_numerology_chapter_link">7. Năm cá nhân</a>
                <a href="#chuong-8" class="mayhen_numerology_chapter_link">8. Kế hoạch 30 ngày</a>
                <a href="#chuong-9" class="mayhen_numerology_chapter_link">9. Hỏi Sâu AI &amp; Nhật ký</a>
                <a href="#chuong-10" class="mayhen_numerology_chapter_link">10. Xuất thẻ &amp; Chia sẻ</a>
            </nav>

            {{-- CHƯƠNG 1 — DẤU ẤN CỦA BẠN --}}
            <article id="chuong-1" class="mayhen_numerology_signature_hero">
                <div class="mayhen_numerology_giant_badge">
                    <span class="mayhen_numerology_chapter_tag">Chương 1 · Đường Đời</span>
                    <div class="mayhen_numerology_giant_number">{{ $lp['display'] ?? '—' }}</div>
                    <div class="mayhen_numerology_giant_sub">{{ $enriched['indicator_meanings']['life_path']['title'] ?? '' }}</div>
                </div>
                <div>
                    <div class="mayhen_numerology_chapter_head mayhen_numerology_chapter_head--compact">
                        <span class="mayhen_numerology_eyebrow">{{ $calc['method_name'] ?? 'Pythagorean Hans Decoz' }} (v{{ $calc['method_version'] ?? '1.0.0' }})</span>
                        <div class="mayhen_numerology_head_tools">
                            <button type="button" class="mayhen_numerology_mini_btn" data-num-concept-lookup="life_path">? Đường Đời là gì</button>
                            <button type="button"
                                    class="mayhen_numerology_ask_btn"
                                    data-num-ask-trigger
                                    data-scope-type="section"
                                    data-section-key="overview"
                                    data-scope-label="Tổng Quan Dấu Ấn Con Số">
                                ✦ Hỏi sâu AI phần này
                            </button>
                        </div>
                    </div>
                    <h1 class="mayhen_numerology_show_h1">
                        {{ $displayName }} — {{ $interpretation['headline'] ?? 'Hồ Sơ Dấu Ấn Con Số' }}
                    </h1>
                    <p class="mayhen_numerology_show_meta">
                        Ngày sinh dương lịch: <strong>{{ $calc['birth']['label'] ?? '' }}</strong> • {{ $calc['completeness_label'] ?? '' }} • Trọng tâm: <strong>{{ $focusTopicLabel }}</strong>
                    </p>
                    <p class="mayhen_numerology_long_prose mayhen_numerology_show_summary">{!! nl2br(e($interpretation['summary'] ?? '')) !!}</p>
                    <div class="mayhen_numerology_note_box">
                        {{ $core['life_path_comparison']['explanation'] ?? '' }}
                    </div>

                    @if(! empty($karmicMaster['masters']) || ! empty($karmicMaster['karmic_debts']))
                        <div class="mayhen_numerology_special_grid">
                            @foreach($karmicMaster['masters'] as $mst)
                                <div class="mayhen_numerology_special_card mayhen_numerology_special_card--master">
                                    <div class="mayhen_numerology_special_top">
                                        <strong>Master Number {{ $mst['display'] }} · {{ $mst['location'] }}</strong>
                                        <button type="button" class="mayhen_numerology_mini_btn" data-num-concept-lookup="master_numbers">? Master là gì</button>
                                    </div>
                                    <p>{{ $mst['note'] }}</p>
                                </div>
                            @endforeach
                            @foreach($karmicMaster['karmic_debts'] as $kd)
                                <div class="mayhen_numerology_special_card mayhen_numerology_special_card--debt">
                                    <div class="mayhen_numerology_special_top">
                                        <strong>Bài Học Nợ Nghiệp {{ $kd['code'] }} · {{ $kd['location'] }}</strong>
                                        <button type="button" class="mayhen_numerology_mini_btn" data-num-concept-lookup="karmic_debts">? Nợ Nghiệp là gì</button>
                                    </div>
                                    <p>{{ $kd['note'] }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </article>

            {{-- CHƯƠNG 2 — BỘ SỐ CÁ NHÂN (THẺ XÒE QUẠT + CÁCH TÍNH CỦA TÔI) --}}
            <section id="chuong-2" class="mayhen_numerology_editorial_block">
                <div class="mayhen_numerology_chapter_head">
                    <div>
                        <span class="mayhen_numerology_chapter_tag">Chương 2 · Bộ Chỉ Số Cá Nhân &amp; Minh Bạch Phép Tính</span>
                        <h2>Bộ Thẻ Con Số Của Riêng Bạn</h2>
                    </div>
                    <div class="mayhen_numerology_head_tools">
                        <button type="button"
                                class="mayhen_numerology_ask_btn"
                                data-num-ask-trigger
                                data-scope-type="section"
                                data-section-key="core_numbers"
                                data-scope-label="Bộ Chỉ Số Cốt Lõi & Mở Rộng">
                            ✦ Hỏi sâu AI phần này
                        </button>
                    </div>
                </div>
                <p>Chạm vào từng thẻ dưới đây để lật xem ý nghĩa đúng theo vị trí chỉ số (cùng một con số ở Đường Đời, Sứ Mệnh, Linh Hồn và Nhân Cách mang chiều sâu khác nhau).</p>

                <div class="mayhen_numerology_fan_shell">
                    <div class="mayhen_numerology_fan_stage" data-num-fan-stage>
                        <div class="mayhen_numerology_fan_hand" data-num-fan-hand></div>
                    </div>
                    <div class="mayhen_numerology_fan_pills" data-num-fan-pills role="tablist" aria-label="Chọn nhanh chỉ số cá nhân"></div>
                </div>
                <div class="mayhen_numerology_drawer" data-num-card-drawer aria-live="polite"></div>

                <div class="mayhen_numerology_stat_strip" style="margin: 22px 0;">
                    <div class="mayhen_numerology_stat_cell">
                        <div class="mayhen_numerology_stat_lbl">Sứ Mệnh (Expression)</div>
                        <div class="mayhen_numerology_stat_val">{{ $core['expression']['display'] ?? '—' }}</div>
                    </div>
                    <div class="mayhen_numerology_stat_cell">
                        <div class="mayhen_numerology_stat_lbl">Linh Hồn (Soul Urge)</div>
                        <div class="mayhen_numerology_stat_val">{{ $core['soul_urge']['display'] ?? '—' }}</div>
                    </div>
                    <div class="mayhen_numerology_stat_cell">
                        <div class="mayhen_numerology_stat_lbl">Nhân Cách (Personality)</div>
                        <div class="mayhen_numerology_stat_val">{{ $core['personality']['display'] ?? '—' }}</div>
                    </div>
                    <div class="mayhen_numerology_stat_cell">
                        <div class="mayhen_numerology_stat_lbl">Ngày Sinh / Trưởng Thành</div>
                        <div class="mayhen_numerology_stat_val">{{ $core['birthday']['display'] ?? '—' }} · {{ $ext['maturity']['display'] ?? '—' }}</div>
                    </div>
                </div>

                @if(! empty($ext['hidden_passion']))
                    <div class="mayhen_numerology_grid_3" style="margin-bottom: 18px;">
                        <div class="mayhen_numerology_note_box">
                            <div class="mayhen_numerology_note_inline_head">
                                <strong>Thái Độ / Cân Bằng:</strong>
                                <button type="button" class="mayhen_numerology_mini_pill" data-num-concept-lookup="attitude">?</button>
                            </div>
                            {{ $ext['attitude']['display'] ?? '—' }} / {{ $ext['balance']['display'] ?? '—' }}
                        </div>
                        <div class="mayhen_numerology_note_box">
                            <div class="mayhen_numerology_note_inline_head">
                                <strong>Đam Mê Tiềm Ẩn &amp; Tiềm Thức:</strong>
                                <button type="button" class="mayhen_numerology_mini_pill" data-num-concept-lookup="hidden_passion">?</button>
                            </div>
                            {{ implode(', ', $ext['hidden_passion']['numbers'] ?? []) ?: '—' }} • Tiềm thức: {{ $ext['subconscious_self']['value'] ?? '—' }}/9
                        </div>
                        <div class="mayhen_numerology_note_box">
                            <div class="mayhen_numerology_note_inline_head">
                                <strong>Bài Học (Số thiếu) &amp; Cầu Nối:</strong>
                                <button type="button" class="mayhen_numerology_mini_pill" data-num-concept-lookup="karmic_lessons">?</button>
                            </div>
                            {{ implode(', ', $ext['karmic_lessons']['missing_numbers'] ?? []) ?: 'Đủ 1–9' }} • Cầu nối: {{ $ext['bridges']['life_path_expression'] ?? '—' }}
                        </div>
                    </div>
                @endif

                <details class="mayhen_numerology_note_box" style="background: #FCFBF9; margin-bottom: 24px;">
                    <summary style="cursor: pointer; font-weight: 700; color: #1E4D3B;">Mở bảng “Cách tính của tôi” — Kiểm tra từng bước quy đổi</summary>
                    <div style="margin-top: 12px;">
                        <p style="font-size: 13.5px; margin-bottom: 8px;"><strong>Các bước tính Đường Đời:</strong> {{ implode(' → ', $lp['formula_steps'] ?? []) }}</p>
                        @if($nb)
                            <p style="font-size: 13.5px; margin-bottom: 6px;">
                                <strong>Họ tên chuẩn hóa:</strong> {{ $nb['original_name'] }} → <code>{{ $nb['normalized_name'] }}</code>
                                • Quy ước Y: <em>{{ $nb['y_mode_label'] ?? '' }}</em>
                                • Toàn vẹn: Nguyên âm ({{ $nb['raw_vowel_sum'] }}) + Phụ âm ({{ $nb['raw_consonant_sum'] }}) = Tổng ({{ $nb['raw_all_sum'] }})
                            </p>
                            <div class="mayhen_numerology_letter_row">
                                @foreach($nb['letters'] as $l)
                                    <div class="mayhen_numerology_letter_tile {{ $l['type'] === 'vowel' ? 'mayhen_numerology_letter_tile--vowel' : 'mayhen_numerology_letter_tile--consonant' }}">
                                        <div style="font-weight: 800;">{{ $l['char'] }}</div>
                                        <div style="font-size: 11px; color: #5C5248;">{{ $l['value'] }} · {{ $l['type'] === 'vowel' ? 'NA' : 'PA' }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </details>

                {{-- HỒ SƠ LUẬN GIẢI CHI TIẾT TỪNG CHỈ SỐ --}}
                @if(! empty($detailedIndicators))
                    <div class="mayhen_numerology_ind_head" data-num-reveal>
                        <div>
                            <h3 class="mayhen_numerology_sub_h3">Luận Giải Chi Tiết Từng Chỉ Số ({{ count($detailedIndicators) }} Chỉ Số)</h3>
                            <p style="margin: 0; font-size: 13.5px; color: #6B5F52;">Mỗi chỉ số có phần giải mã công thức, ý nghĩa ứng dụng và phân tích cá nhân hóa theo tổ hợp bộ số riêng của bạn.</p>
                        </div>
                        <div class="mayhen_numerology_ind_filters" role="tablist" aria-label="Lọc nhóm chỉ số">
                            <button type="button" class="mayhen_numerology_ind_filter_btn is-active" data-ind-filter="all">Tất cả ({{ count($detailedIndicators) }})</button>
                            <button type="button" class="mayhen_numerology_ind_filter_btn" data-ind-filter="core">Cốt lõi</button>
                            <button type="button" class="mayhen_numerology_ind_filter_btn" data-ind-filter="extended">Mở rộng</button>
                        </div>
                    </div>

                    <div class="mayhen_numerology_ind_grid" data-num-ind-grid>
                        @foreach($detailedIndicators as $ind)
                            @php
                                $indCode = (string) ($ind['code'] ?? '');
                                $indDeepText = (string) ($deepInsights[$indCode] ?? '');
                            @endphp
                            <article class="mayhen_numerology_ind_card" data-ind-group="{{ $ind['group'] }}" data-num-reveal>
                                <div class="mayhen_numerology_ind_top">
                                    <div class="mayhen_numerology_ind_badge">{{ $ind['display'] }}</div>
                                    <div class="mayhen_numerology_ind_title_wrap">
                                        <span class="mayhen_numerology_ind_kicker">{{ $ind['group'] === 'core' ? 'Chỉ Số Cốt Lõi' : 'Chỉ Số Mở Rộng' }}</span>
                                        <h4>{{ $ind['label'] }}</h4>
                                        @if($ind['title'] !== '')
                                            <div class="mayhen_numerology_ind_subtitle">{{ $ind['title'] }}</div>
                                        @endif
                                    </div>
                                </div>
                                @if($ind['scope'] !== '')
                                    <p class="mayhen_numerology_ind_scope">{{ $ind['scope'] }}</p>
                                @endif
                                <p class="mayhen_numerology_ind_body">{{ $ind['contextual_meaning'] }}</p>
                                @if($indDeepText !== '')
                                    <div class="mayhen_numerology_ind_deep">
                                        <span class="mayhen_numerology_ind_deep_tag">✦ Luận Chuyên Sâu Theo Hồ Sơ Của Bạn</span>
                                        <p class="mayhen_numerology_long_prose">{!! nl2br(e($indDeepText)) !!}</p>
                                    </div>
                                @endif
                                <div class="mayhen_numerology_ind_split">
                                    @if($ind['strengths'] !== '')
                                        <div class="mayhen_numerology_ind_box mayhen_numerology_ind_box--good">
                                            <strong>Điểm mạnh nổi bật</strong>
                                            <span>{{ $ind['strengths'] }}</span>
                                        </div>
                                    @endif
                                    @if($ind['watch_outs'] !== '')
                                        <div class="mayhen_numerology_ind_box mayhen_numerology_ind_box--warn">
                                            <strong>Điều cần cân bằng</strong>
                                            <span>{{ $ind['watch_outs'] }}</span>
                                        </div>
                                    @endif
                                </div>
                                @if($ind['work_meaning'] !== '')
                                    <div class="mayhen_numerology_ind_extra">
                                        <strong>Ứng dụng công việc:</strong> {{ $ind['work_meaning'] }}
                                    </div>
                                @endif
                                <div class="mayhen_numerology_ind_actions">
                                    <button type="button" class="mayhen_numerology_mini_btn" data-num-concept-lookup="{{ $indCode }}">? Giải mã chỉ số</button>
                                    <button type="button"
                                            class="mayhen_numerology_mini_btn mayhen_numerology_mini_btn--ai"
                                            data-num-ask-trigger
                                            data-scope-type="indicator"
                                            data-topic-code="ind:{{ $indCode }}"
                                            data-scope-label="{{ $ind['label'] }} ({{ $ind['display'] }})">
                                        ✦ Hỏi sâu AI chỉ số này
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif

                {{-- CẦU NỐI TƯƠNG QUAN CỐT LÕI --}}
                @if(! empty($bridgeInsights))
                    <div class="mayhen_numerology_bridges_grid" data-num-reveal>
                        @foreach($bridgeInsights as $br)
                            <div class="mayhen_numerology_bridge_card">
                                <div class="mayhen_numerology_bridge_top">
                                    <strong>{{ $br['title'] }}</strong>
                                    <span class="mayhen_numerology_bridge_pill">{{ $br['pair'] }} • Cầu nối {{ $br['bridge_value'] }}</span>
                                </div>
                                <p>{{ $br['summary'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- CHƯƠNG 3 — BIỂU ĐỒ NGÀY SINH 3×3 (DAVID A. PHILLIPS) --}}
            <section id="chuong-3" class="mayhen_numerology_editorial_block">
                <div class="mayhen_numerology_chapter_head">
                    <div>
                        <span class="mayhen_numerology_chapter_tag">Chương 3 · Lớp Phân Tích Độc Lập Theo David A. Phillips</span>
                        <h2>Biểu Đồ Ngày Sinh 3×3 &amp; 8 Đường Mũi Tên</h2>
                    </div>
                    <div class="mayhen_numerology_head_tools">
                        <button type="button" class="mayhen_numerology_mini_btn" data-num-concept-lookup="birth_chart">? Biểu đồ 3×3 là gì</button>
                        <button type="button" class="mayhen_numerology_mini_btn" data-num-concept-lookup="arrows">? 8 Đường Mũi Tên</button>
                        <button type="button"
                                class="mayhen_numerology_ask_btn"
                                data-num-ask-trigger
                                data-scope-type="section"
                                data-section-key="birth_chart"
                                data-scope-label="Biểu Đồ Ngày Sinh 3×3 & Mũi Tên">
                            ✦ Hỏi sâu AI phần này
                        </button>
                    </div>
                </div>
                <div data-num-birthchart></div>
                @if(! empty($deepInsights['birth_chart']))
                    <div class="mayhen_numerology_ai_callout">
                        <span class="mayhen_numerology_ai_callout_tag">✦ Luận Giải Chuyên Sâu Biểu Đồ Ngày Sinh 3×3</span>
                        <p class="mayhen_numerology_long_prose">{!! nl2br(e($deepInsights['birth_chart'])) !!}</p>
                    </div>
                @endif
            </section>

            {{-- CHƯƠNG 4 — HIỂU BẢN THÂN, ĐIỂM MẠNH/YẾU & ĐỊNH HƯỚNG NGHỀ NGHIỆP --}}
            <section id="chuong-4" class="mayhen_numerology_editorial_block" data-num-reveal>
                <div class="mayhen_numerology_chapter_head">
                    <div>
                        <span class="mayhen_numerology_chapter_tag">Chương 4 · Luận Giải Tầng 1, 2 &amp; 3 ({{ $reading->ai_powered ? 'AI Cá Nhân Hóa Siêu Chi Tiết' : 'Kho Tri Thức Kiểm Định' }})</span>
                        <h2>Hiểu Bản Thân, Tương Tác Chỉ Số &amp; Định Hướng Nghề Nghiệp</h2>
                    </div>
                    <div class="mayhen_numerology_head_tools">
                        <button type="button"
                                class="mayhen_numerology_ask_btn"
                                data-num-ask-trigger
                                data-scope-type="section"
                                data-section-key="self_understanding"
                                data-scope-label="Hiểu Bản Thân & Cân Bằng">
                            ✦ Hỏi sâu AI phần này
                        </button>
                    </div>
                </div>
                <div class="mayhen_numerology_editorial_prose">
                    <div class="mayhen_numerology_layer_block">
                        <h3>Tầng 1 · Dấu Ấn Bản Sắc &amp; Nền Tảng Đường Đời</h3>
                        <p class="mayhen_numerology_long_prose">{!! nl2br(e($interpretation['layer_1_signature'] ?? '')) !!}</p>
                    </div>
                    <div class="mayhen_numerology_layer_block">
                        <h3>Tầng 2 · Nội Tâm, Biểu Hiện &amp; Các Điểm Giao Thoa</h3>
                        <p class="mayhen_numerology_long_prose">{!! nl2br(e($interpretation['layer_2_self_understanding'] ?? '')) !!}</p>
                    </div>
                    <div class="mayhen_numerology_layer_block">
                        <div class="mayhen_numerology_chapter_head mayhen_numerology_chapter_head--sub">
                            <h3>Tầng 3 · Tiềm Năng Thực Thi, Nghề Nghiệp &amp; Tài Chính</h3>
                            <button type="button"
                                    class="mayhen_numerology_mini_btn mayhen_numerology_mini_btn--ai"
                                    data-num-ask-trigger
                                    data-scope-type="section"
                                    data-section-key="career_potential"
                                    data-scope-label="Nghề Nghiệp & Năng Lực Thực Thi">
                                ✦ Hỏi sâu AI sự nghiệp
                            </button>
                        </div>
                        <p class="mayhen_numerology_long_prose">{!! nl2br(e($interpretation['layer_3_potential'] ?? '')) !!}</p>
                    </div>
                    @if(! empty($interpretation['inner_balance_and_environment']))
                        <div class="mayhen_numerology_ai_callout">
                            <div class="mayhen_numerology_chapter_head mayhen_numerology_chapter_head--sub">
                                <span class="mayhen_numerology_ai_callout_tag">Nhịp Cân Bằng Tâm – Thân &amp; Môi Trường Cộng Hưởng</span>
                                <button type="button" class="mayhen_numerology_mini_btn" data-num-concept-lookup="balance">? Chỉ số Cân Bằng là gì</button>
                            </div>
                            <p class="mayhen_numerology_long_prose">{!! nl2br(e($interpretation['inner_balance_and_environment'])) !!}</p>
                        </div>
                    @endif
                </div>

                {{-- ĐIỂM MẠNH & ĐIỂM CẦN RÈN LUYỆN --}}
                <div class="mayhen_numerology_sw_grid">
                    <div class="mayhen_numerology_sw_card mayhen_numerology_sw_card--good">
                        <div class="mayhen_numerology_sw_head">
                            <span class="mayhen_numerology_sw_tag mayhen_numerology_sw_tag--good">Thế Mạnh Nổi Bật</span>
                            <h3>Nguồn Lực Tự Nhiên Của Bạn</h3>
                        </div>
                        <ul class="mayhen_numerology_sw_list">
                            @foreach($strengthsList as $stItem)
                                <li>{{ $stItem }}</li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mayhen_numerology_sw_card mayhen_numerology_sw_card--warn">
                        <div class="mayhen_numerology_sw_head">
                            <span class="mayhen_numerology_sw_tag mayhen_numerology_sw_tag--warn">Điểm Cần Cân Bằng</span>
                            <h3>Vùng Rèn Luyện Để Bứt Phá</h3>
                        </div>
                        <ul class="mayhen_numerology_sw_list">
                            @foreach($watchOutsList as $woItem)
                                <li>{{ $woItem }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- HỒ SƠ 6 NHÓM NĂNG LỰC NGHỀ NGHIỆP (TÍNH TỪ BỘ SỐ THẬT) --}}
                @if(! empty($careerDomains['domains']))
                    <div class="mayhen_numerology_career_panel" data-num-career-bars>
                        <div class="mayhen_numerology_career_head">
                            <div>
                                <span class="mayhen_numerology_chapter_tag" style="margin-bottom: 4px;">Phân Tích Từ Tổ Hợp Bộ Số Cá Nhân</span>
                                <h3 class="mayhen_numerology_sub_h3" style="margin: 0;">Thiên Hướng 6 Nhóm Năng Lực Nghề Nghiệp</h3>
                            </div>
                            <span class="mayhen_numerology_career_note">Tính theo trọng số Đường Đời, Sứ Mệnh, Linh Hồn, Nhân Cách &amp; Ngày Sinh</span>
                        </div>

                        <div class="mayhen_numerology_career_bars">
                            @foreach($careerDomains['domains'] as $cIdx => $dom)
                                <div class="mayhen_numerology_career_row {{ $cIdx < 2 ? 'is-top' : '' }}" style="--bar-pct: {{ $dom['score'] }}%; --bar-delay: {{ $cIdx * 90 }}ms;">
                                    <div class="mayhen_numerology_career_meta">
                                        <span class="mayhen_numerology_career_label">
                                            {{ $dom['label'] }}
                                            @if($cIdx < 2)
                                                <small class="mayhen_numerology_career_top_pill">Nổi trội</small>
                                            @endif
                                        </span>
                                        <span class="mayhen_numerology_career_score">{{ $dom['score'] }}/100</span>
                                    </div>
                                    <div class="mayhen_numerology_career_track">
                                        <div class="mayhen_numerology_career_fill"></div>
                                    </div>
                                    <div class="mayhen_numerology_career_desc">{{ $dom['desc'] }}</div>
                                </div>
                            @endforeach
                        </div>

                        @if(! empty($careerDirections))
                            <div class="mayhen_numerology_career_roles">
                                <strong>Gợi ý môi trường &amp; vai trò phát huy tốt nhất:</strong>
                                <div class="mayhen_numerology_career_chips">
                                    @foreach($careerDirections as $role)
                                        <span class="mayhen_numerology_career_chip">{{ $role }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </section>

            {{-- CHƯƠNG 5 — TÌNH YÊU, HÔN NHÂN & GIA ĐÌNH --}}
            <section id="chuong-5" class="mayhen_numerology_editorial_block">
                <div class="mayhen_numerology_chapter_head">
                    <div>
                        <span class="mayhen_numerology_chapter_tag">Chương 5 · Luận Giải Tầng 4</span>
                        <h2>Tình Yêu, Giao Tiếp, Hôn Nhân &amp; Gia Đình</h2>
                    </div>
                    <div class="mayhen_numerology_head_tools">
                        <button type="button"
                                class="mayhen_numerology_ask_btn"
                                data-num-ask-trigger
                                data-scope-type="section"
                                data-section-key="love_family"
                                data-scope-label="Tình Yêu, Hôn Nhân & Gia Đình">
                            ✦ Hỏi sâu AI phần này
                        </button>
                    </div>
                </div>
                <p class="mayhen_numerology_long_prose">{!! nl2br(e($interpretation['layer_4_love_family'] ?? '')) !!}</p>
                <div class="mayhen_numerology_note_box" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-top: 14px;">
                    <span>Muốn đối chiếu sâu hơn nhịp giao tiếp và hôn nhân cùng người thương (10 chủ đề, không chấm % hợp tuổi)?</span>
                    <a href="{{ route('numerology.love.index') }}" class="mayhen_numerology_btn_crimson mayhen_numerology_btn_sm">Mở Thần Số Học Tình Duyên &rarr;</a>
                </div>
            </section>

            {{-- CHƯƠNG 6 — HÀNH TRÌNH CUỘC ĐỜI (3 GIAI ĐOẠN + KIM TỰ THÁP 4 ĐỈNH CAO & 4 THỬ THÁCH) --}}
            <section id="chuong-6" class="mayhen_numerology_editorial_block" data-num-reveal>
                <div class="mayhen_numerology_chapter_head">
                    <div>
                        <span class="mayhen_numerology_chapter_tag">Chương 6 · Luận Giải Tầng 5</span>
                        <h2>Ba Giai Đoạn Cuộc Đời, Kim Tự Tháp 4 Đỉnh Cao &amp; 4 Thử Thách</h2>
                    </div>
                    <div class="mayhen_numerology_head_tools">
                        <button type="button" class="mayhen_numerology_mini_btn" data-num-concept-lookup="pinnacles">? 4 Đỉnh Cao là gì</button>
                        <button type="button" class="mayhen_numerology_mini_btn" data-num-concept-lookup="challenges">? 4 Thử Thách là gì</button>
                        <button type="button"
                                class="mayhen_numerology_ask_btn"
                                data-num-ask-trigger
                                data-scope-type="section"
                                data-section-key="life_journey"
                                data-scope-label="3 Giai Đoạn & Kim Tự Tháp 4 Đỉnh Cao">
                            ✦ Hỏi sâu AI phần này
                        </button>
                    </div>
                </div>
                <p class="mayhen_numerology_long_prose">{!! nl2br(e($interpretation['layer_5_life_journey'] ?? '')) !!}</p>
                @if(! empty($deepInsights['pinnacles_challenges']))
                    <div class="mayhen_numerology_ai_callout" style="margin: 12px 0 18px;">
                        <span class="mayhen_numerology_ai_callout_tag">✦ Phân Tích Chuyên Sâu Đỉnh Cao &amp; Thử Thách Trên Hồ Sơ</span>
                        <p class="mayhen_numerology_long_prose">{!! nl2br(e($deepInsights['pinnacles_challenges'])) !!}</p>
                    </div>
                @endif

                {{-- 3 GIAI ĐOẠN CUỘC ĐỜI (GIEO HẠT -> KIẾN TẠO -> THU HOẠCH) --}}
                @if(! empty($periodCyclesEnriched))
                    <div class="mayhen_numerology_periods_grid">
                        @foreach($periodCyclesEnriched as $pc)
                            <div class="mayhen_numerology_period_card {{ ! empty($pc['is_current']) ? 'is-current' : '' }}">
                                <div class="mayhen_numerology_period_top">
                                    <span class="mayhen_numerology_period_stage">{{ $pc['stage_name'] }}</span>
                                    @if(! empty($pc['is_current']))
                                        <span class="mayhen_numerology_period_now">Đang đi qua</span>
                                    @endif
                                </div>
                                <div class="mayhen_numerology_period_num_row">
                                    <span class="mayhen_numerology_period_num">{{ $pc['value'] }}</span>
                                    <div>
                                        <div class="mayhen_numerology_period_title">{{ $pc['title'] }}</div>
                                        <div class="mayhen_numerology_period_age">{{ $pc['age_range'] }}</div>
                                    </div>
                                </div>
                                <p class="mayhen_numerology_period_desc">{{ $pc['meaning'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- ĐỒ HỌA KIM TỰ THÁP 4 ĐỈNH CAO SVG (ANIMATION VẼ NÉT KHI CUỘN TỚI) --}}
                <div class="mayhen_numerology_pyramid_card" data-num-pyramid-svg></div>

                <div class="mayhen_numerology_grid_2" style="margin-top: 18px;">
                    <div>
                        <h3 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 10px;">4 Đỉnh Cao Cuộc Đời (Pinnacles)</h3>
                        <table class="mayhen_numerology_table">
                            <thead><tr><th>Đỉnh cao</th><th>Giai đoạn tuổi</th><th>Con số</th></tr></thead>
                            <tbody>
                                @foreach($enriched['pinnacles_enriched'] ?? [] as $p)
                                    <tr>
                                        <td><strong>{{ $p['label'] }}</strong><br><small style="color:#6B5F52;">{{ $p['formula'] }}</small></td>
                                        <td>{{ $p['age_range'] }}</td>
                                        <td><strong style="color:#85182A; font-size:18px;">{{ $p['display'] }}</strong><br><small>{{ $p['meaning'] }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <h3 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 10px;">4 Thử Thách Trưởng Thành (Challenges)</h3>
                        <table class="mayhen_numerology_table">
                            <thead><tr><th>Thử thách</th><th>Công thức</th><th>Con số</th></tr></thead>
                            <tbody>
                                @foreach($enriched['challenges_enriched'] ?? [] as $c)
                                    <tr>
                                        <td><strong>{{ $c['label'] }}</strong></td>
                                        <td>{{ $c['formula'] }}</td>
                                        <td><strong style="color:#1E4D3B; font-size:18px;">{{ $c['value'] }}</strong><br><small>{{ $c['meaning'] }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            {{-- CHƯƠNG 7 — NĂM CÁ NHÂN, 12 THÁNG & CHU KỲ 9 NĂM --}}
            <section id="chuong-7" class="mayhen_numerology_editorial_block">
                <div class="mayhen_numerology_chapter_head">
                    <div>
                        <span class="mayhen_numerology_chapter_tag">Chương 7 · Chu Kỳ Thời Gian (Dương Lịch 01/01–31/12)</span>
                        <h2>Năm Cá Nhân {{ $cycles['personal_year']['year'] ?? '' }}: Số {{ $cycles['personal_year']['value'] ?? '' }} — {{ $enriched['personal_year_meaning']['theme'] ?? '' }}</h2>
                    </div>
                    <div class="mayhen_numerology_head_tools">
                        <button type="button" class="mayhen_numerology_mini_btn" data-num-concept-lookup="personal_year">? Năm Cá Nhân là gì</button>
                        <button type="button"
                                class="mayhen_numerology_ask_btn"
                                data-num-ask-trigger
                                data-scope-type="section"
                                data-section-key="personal_cycles"
                                data-scope-label="Chu Kỳ 9 Năm & Năm Cá Nhân {{ $cycles['personal_year']['year'] ?? '' }}">
                            ✦ Hỏi sâu AI phần này
                        </button>
                    </div>
                </div>
                <p>{{ $enriched['personal_year_meaning']['summary'] ?? '' }}</p>
                @if(! empty($deepInsights['personal_year']))
                    <div class="mayhen_numerology_ai_callout" style="margin: 12px 0 16px;">
                        <span class="mayhen_numerology_ai_callout_tag">✦ Luận Chuyên Sâu Năm Cá Nhân {{ $cycles['personal_year']['year'] ?? '' }}</span>
                        <p class="mayhen_numerology_long_prose">{!! nl2br(e($deepInsights['personal_year'])) !!}</p>
                    </div>
                @endif
                <div class="mayhen_numerology_grid_3" style="margin-bottom: 16px;">
                    <div class="mayhen_numerology_note_box"><strong>Năm cá nhân {{ $cycles['personal_year']['year'] ?? '' }}:</strong> Số {{ $cycles['personal_year']['value'] ?? '' }} ({{ $cycles['personal_year']['formula'] ?? '' }})</div>
                    <div class="mayhen_numerology_note_box"><strong>Tháng cá nhân {{ $cycles['personal_month']['month'] ?? '' }}:</strong> Số {{ $cycles['personal_month']['value'] ?? '' }} — {{ $enriched['personal_month_meaning'] ?? '' }}</div>
                    <div class="mayhen_numerology_note_box"><strong>Ngày cá nhân {{ $cycles['personal_day']['day'] ?? '' }}:</strong> Số {{ $cycles['personal_day']['value'] ?? '' }} — {{ $enriched['personal_day_meaning'] ?? '' }}</div>
                </div>

                {{-- 4 KHÍA CẠNH THỰC TẾ TRONG NĂM CÁ NHÂN HIỆN TẠI --}}
                @if(! empty($yearAspects))
                    <div class="mayhen_numerology_aspects_grid" data-num-reveal>
                        <div class="mayhen_numerology_aspect_card">
                            <span class="mayhen_numerology_aspect_tag">Sự Nghiệp &amp; Công Việc</span>
                            <p>{{ $yearAspects['career'] ?? '' }}</p>
                        </div>
                        <div class="mayhen_numerology_aspect_card">
                            <span class="mayhen_numerology_aspect_tag">Tài Chính &amp; Tích Lũy</span>
                            <p>{{ $yearAspects['finance'] ?? '' }}</p>
                        </div>
                        <div class="mayhen_numerology_aspect_card">
                            <span class="mayhen_numerology_aspect_tag">Tình Cảm &amp; Gia Đạo</span>
                            <p>{{ $yearAspects['love'] ?? '' }}</p>
                        </div>
                        <div class="mayhen_numerology_aspect_card">
                            <span class="mayhen_numerology_aspect_tag">Học Hỏi &amp; Nội Lực</span>
                            <p>{{ $yearAspects['growth'] ?? '' }}</p>
                        </div>
                    </div>
                @endif

                <div class="mayhen_numerology_wave_head">
                    <div>
                        <h4 class="mayhen_numerology_wave_title">Biểu Đồ Sóng Vận Số Chu Kỳ 9 Năm</h4>
                        <p class="mayhen_numerology_wave_sub">Nhịp lên xuống tự nhiên của 9 năm liên tiếp — chạm vào từng đỉnh sóng để xem chủ đề và trọng tâm từng năm.</p>
                    </div>
                    <div class="mayhen_numerology_wave_legend" aria-hidden="true">
                        <span class="mayhen_numerology_wave_legend_item mayhen_numerology_wave_legend_item--current">● Năm hiện tại</span>
                        <span class="mayhen_numerology_wave_legend_item mayhen_numerology_wave_legend_item--peak">● Nhịp bứt phá / Đỉnh cao</span>
                    </div>
                </div>
                <div class="mayhen_numerology_wave_card" data-num-cycle-wave></div>

                {{-- LỘ TRÌNH 12 THÁNG CÁ NHÂN TRONG NĂM ĐANG XEM --}}
                @if(! empty($twelveMonthsEnriched))
                    <div class="mayhen_numerology_months_head" data-num-reveal>
                        <h4 class="mayhen_numerology_wave_title">Lộ Trình 12 Tháng Cá Nhân Năm {{ $cycles['personal_year']['year'] ?? '' }}</h4>
                        <p class="mayhen_numerology_wave_sub">Nhịp năng lượng từng tháng giúp bạn chọn đúng thời điểm khởi động, củng cố hay nghỉ ngơi.</p>
                    </div>
                    <div class="mayhen_numerology_months_grid" data-num-reveal>
                        @foreach($twelveMonthsEnriched as $tm)
                            <div class="mayhen_numerology_month_card {{ ! empty($tm['is_current']) ? 'is-current' : '' }}">
                                <div class="mayhen_numerology_month_top">
                                    <span class="mayhen_numerology_month_lbl">Tháng {{ $tm['month'] }}</span>
                                    <span class="mayhen_numerology_month_num">{{ $tm['value'] }}</span>
                                </div>
                                <p class="mayhen_numerology_month_desc">{{ $tm['meaning'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- CHƯƠNG 8 — KẾ HOẠCH PHÁT TRIỂN CÁ NHÂN --}}
            <section id="chuong-8" class="mayhen_numerology_editorial_block">
                <div class="mayhen_numerology_chapter_head">
                    <div>
                        <span class="mayhen_numerology_chapter_tag">Chương 8 · Luận Giải Tầng 6</span>
                        <h2>Kế Hoạch Phát Triển Thực Tế &amp; Điểm Lưu Tâm</h2>
                    </div>
                    <div class="mayhen_numerology_head_tools">
                        <button type="button"
                                class="mayhen_numerology_ask_btn"
                                data-num-ask-trigger
                                data-scope-type="section"
                                data-section-key="action_plan"
                                data-scope-label="Kế Hoạch 7–30 Ngày & Hành Động">
                            ✦ Hỏi sâu AI phần này
                        </button>
                    </div>
                </div>
                <div class="mayhen_numerology_grid_2">
                    <div class="mayhen_numerology_note_box" style="background: #FCFBF9;">
                        <strong>3 Hành Động Có Thể Bắt Đầu</strong>
                        <ul style="margin: 8px 0 0; padding-left: 18px;">
                            @foreach($plan['actions'] ?? [] as $act)
                                <li style="margin-bottom: 6px;">{{ $act }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="mayhen_numerology_note_box" style="background: #FCFBF9;">
                        <strong>Mục tiêu 7 ngày:</strong> {{ $plan['goal_7_days'] ?? '' }}<br><br>
                        <strong>Mục tiêu 30 ngày:</strong> {{ $plan['goal_30_days'] ?? '' }}<br><br>
                        <strong>Gợi ý trao đổi cùng người thân:</strong> {{ $plan['couple_family_prompt'] ?? '' }}
                    </div>
                </div>
                @if(! empty($interpretation['reflection_question']))
                    <p style="margin-top: 14px; font-style: italic; color: #85182A;">
                        <strong>Câu hỏi tự nhìn lại:</strong> “{{ $interpretation['reflection_question'] }}”
                    </p>
                @endif
            </section>

            {{-- CHƯƠNG 9 — TRỢ LÝ AI & NHẬT KÝ PHÁT TRIỂN --}}
            <section id="chuong-9" class="mayhen_numerology_editorial_block">
                <span class="mayhen_numerology_chapter_tag">Chương 9 · Đồng Hành Lâu Dài</span>
                <h2>Trợ Lý Thần Số Học AI &amp; Nhật Ký Phát Triển</h2>
                <div class="mayhen_numerology_grid_2">
                    <div>
                        <h3 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 8px;">Hỏi Sâu Trên Hồ Sơ Này (Tối đa 16 lượt)</h3>
                        <p style="font-size: 13px; color: #5C5248; margin: 0 0 12px;">Mọi câu hỏi chỉ bám sát các con số, biểu đồ và nội dung đã luận trong hồ sơ này (tối đa 600 ký tự). Bấm nút “✦ Hỏi sâu AI” ở từng phần bên trên để mở khung chat nhanh.</p>
                        <form data-num-followup-form>
                            <div class="mayhen_numerology_field">
                                <label for="numFuScope">Phạm vi hỏi đáp *</label>
                                <select id="numFuScope" name="topic_code" class="mayhen_numerology_select">
                                    <option value="">Tổng thể toàn bộ hồ sơ</option>
                                    <optgroup label="Các Chương Luận Giải">
                                        @foreach($sectionDefinitions as $sKey => $sDef)
                                            <option value="{{ $sDef['scope_code'] }}">{{ $sDef['label'] }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Các Chỉ Số Cụ Thể">
                                        @foreach($indicatorLabels as $indCode => $indLabel)
                                            <option value="ind:{{ $indCode }}">{{ $indLabel }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="mayhen_numerology_field">
                                <label for="numFuQuestion">Đặt câu hỏi cụ thể về hồ sơ của bạn *</label>
                                <textarea id="numFuQuestion" name="question" rows="3" class="mayhen_numerology_textarea" minlength="4" maxlength="600" required placeholder="Ví dụ: Vì sao chỉ số Sứ mệnh của mình khác Đường đời và nên hài hòa hai số này thế nào?"></textarea>
                            </div>
                            <div class="mayhen_numerology_scoped_error" data-num-followup-error></div>
                            <button type="submit" class="mayhen_numerology_btn_primary">Gửi Câu Hỏi Trên Hồ Sơ Này</button>
                        </form>
                        <div data-num-followup-list style="margin-top: 14px;">
                            @foreach($followupsPayload as $fu)
                                <div class="mayhen_numerology_note_box" style="margin-bottom: 10px;">
                                    <div class="mayhen_numerology_fu_meta">
                                        <span class="mayhen_numerology_scope_pill">{{ $fu['scope_label'] }}</span>
                                        <small>{{ $fu['created_at'] ?? '' }}</small>
                                    </div>
                                    <div style="font-weight: 700; color: #85182A; margin-bottom: 4px;">Hỏi: {{ $fu['question'] }}</div>
                                    <div class="mayhen_numerology_long_prose">{!! nl2br(e($fu['answer'])) !!}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <h3 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 8px;">Nhật Ký Phát Triển 7 / 30 / 90 Ngày</h3>
                        <form data-num-journal-form>
                            <div class="mayhen_numerology_field">
                                <label for="numJnMilestone">Mốc nhìn lại</label>
                                <select id="numJnMilestone" name="milestone_days" class="mayhen_numerology_select">
                                    <option value="7">7 ngày</option>
                                    <option value="30" selected>30 ngày</option>
                                    <option value="90">90 ngày</option>
                                </select>
                            </div>
                            <div class="mayhen_numerology_field">
                                <label for="numJnGoal">Mục tiêu &amp; việc đã thực hiện</label>
                                <input id="numJnGoal" type="text" name="current_goal" class="mayhen_numerology_input" maxlength="500" placeholder="Ghi lại điều bạn đang rèn luyện thực tế...">
                            </div>
                            <button type="submit" class="mayhen_numerology_btn_outline">Lưu Nhật Ký Riêng Tư</button>
                        </form>
                        <div data-num-journal-list style="margin-top: 14px;">
                            @foreach($reading->journals->sortByDesc('id') as $jn)
                                <div class="mayhen_numerology_note_box" style="margin-bottom: 10px;">
                                    <strong>Mốc {{ $jn->milestone_days }} ngày • {{ $jn->created_at?->format('H:i • d/m/Y') }}</strong><br>
                                    {{ $jn->current_goal ?: $jn->actions_taken }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            {{-- CHƯƠNG 10 — XUẤT THẺ NGHỆ THUẬT PNG & CHIA SẺ ẨN NGÀY SINH --}}
            <section id="chuong-10" class="mayhen_numerology_editorial_block" style="border-bottom: none;">
                <span class="mayhen_numerology_chapter_tag">Chương 10 · Lưu Trữ &amp; Chia Sẻ Tinh Tế</span>
                <h2>Xuất Thẻ Nghệ Thuật PNG &amp; Liên Kết Chia Sẻ Ẩn Ngày Sinh</h2>
                <div class="mayhen_numerology_grid_2">
                    <div class="mayhen_numerology_note_box" style="background: #FCFBF9;">
                        <strong>Tải Thẻ Nghệ Thuật HiDPI (3 Tỉ Lệ Chuẩn)</strong>
                        <p style="font-size: 13px; margin: 6px 0 10px;">Mặc định ẩn ngày sinh đầy đủ để bảo vệ quyền riêng tư.</p>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; margin-bottom: 12px; cursor: pointer;">
                            <input type="checkbox" data-num-export-hide-birth checked> Ẩn ngày sinh đầy đủ trên thẻ ảnh
                        </label>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <button type="button" class="mayhen_numerology_btn_primary mayhen_numerology_btn_sm" data-num-export-ratio="1:1">Tải PNG 1:1 (Vuông)</button>
                            <button type="button" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm" data-num-export-ratio="9:16">Tải PNG 9:16 (Story)</button>
                            <button type="button" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm" data-num-export-ratio="4:5">Tải PNG 4:5 (Post)</button>
                        </div>
                    </div>

                    <div class="mayhen_numerology_note_box" style="background: #FCFBF9;">
                        <strong>Thẻ Chia Sẻ Online &amp; Kiểm Soát Quyền Riêng Tư</strong>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin: 10px 0;">
                            <button type="button" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm" data-num-share-btn>Tạo Link Thẻ Chia Sẻ Ẩn Ngày Sinh</button>
                            <button type="button" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm" data-num-revoke-btn style="{{ $shareUrl ? '' : 'display:none;' }}">Thu Hồi Link</button>
                        </div>
                        <div data-num-share-output style="font-size: 13px; {{ $shareUrl ? '' : 'display:none;' }}">
                            @if($shareUrl)
                                Link Thẻ Chia Sẻ: <a href="{{ $shareUrl }}" target="_blank" rel="noopener">{{ $shareUrl }}</a>
                            @endif
                        </div>
                        <div style="margin-top: 12px; display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                            <span style="font-size: 12.5px;">Đánh giá hồ sơ:</span>
                            <button type="button" class="mayhen_numerology_topic_btn {{ $reading->feedback_rating === 'very_helpful' ? 'is-active' : '' }}" style="padding: 5px 10px;" data-num-feedback="very_helpful">Rất hữu ích</button>
                            <button type="button" class="mayhen_numerology_topic_btn {{ $reading->feedback_rating === 'helpful' ? 'is-active' : '' }}" style="padding: 5px 10px;" data-num-feedback="helpful">Dễ hiểu</button>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </section>

    {{-- POPOVER GIẢI MÃ KHÁI NIỆM TỨC THÌ CHO NGƯỜI MỚI --}}
    <div class="mayhen_numerology_concept_popover" data-num-concept-popover role="dialog" aria-modal="false" aria-hidden="true">
        <div class="mayhen_numerology_concept_head">
            <div>
                <span class="mayhen_numerology_scope_pill" data-num-pop-group>Khái niệm</span>
                <h4 class="mayhen_numerology_concept_title" data-num-pop-title>Giải mã con số</h4>
            </div>
            <button type="button" class="mayhen_numerology_concept_close" data-num-pop-close aria-label="Đóng giải mã">&times;</button>
        </div>
        <div class="mayhen_numerology_concept_formula" data-num-pop-formula></div>
        <p class="mayhen_numerology_concept_summary" data-num-pop-summary></p>
        <p class="mayhen_numerology_concept_apply" data-num-pop-apply></p>
        <div class="mayhen_numerology_concept_foot">
            <button type="button" class="mayhen_numerology_ask_btn" data-num-pop-ask>
                ✦ Hỏi sâu AI phần này
            </button>
        </div>
    </div>

    {{-- NGĂN KÉO CHAT HỎI SÂU AI THEO TỪNG CHƯƠNG / CHỈ SỐ (CHUẨN TỬ VI) --}}
    <div class="mayhen_numerology_scoped_backdrop" data-num-scoped-backdrop aria-hidden="true">
        <aside class="mayhen_numerology_scoped_modal" data-num-scoped-modal role="dialog" aria-modal="true" aria-labelledby="numScopedTitle">
            <header class="mayhen_numerology_scoped_head">
                <div class="mayhen_numerology_scoped_head_info">
                    <div class="mayhen_numerology_scoped_title_row">
                        <h3 id="numScopedTitle" data-num-scoped-title>Hỏi Sâu AI</h3>
                        <span class="mayhen_numerology_scope_pill mayhen_numerology_scope_pill--compact" data-num-scoped-badge>Khóa dữ kiện</span>
                    </div>
                    <p class="mayhen_numerology_scoped_desc" data-num-scoped-desc></p>
                </div>
                <button type="button" class="mayhen_numerology_scoped_close" data-num-scoped-close aria-label="Đóng khung chat AI">&times;</button>
            </header>

            <div class="mayhen_numerology_scoped_chat_stage">
                <div class="mayhen_numerology_scoped_history" data-num-scoped-history aria-live="polite"></div>
                <button
                    type="button"
                    class="mayhen_numerology_chat_scroll_btn"
                    data-num-scroll-bottom
                    aria-label="Cuộn xuống tin nhắn mới nhất"
                    title="Cuộn xuống cuối đoạn chat"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 5v14"/>
                        <path d="m19 12-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            <div class="mayhen_numerology_scoped_suggest_wrap" data-num-scoped-suggest-wrap>
                <div class="mayhen_numerology_scoped_suggest_bar">
                    <button type="button" class="mayhen_numerology_scoped_suggest_toggle" data-num-scoped-suggest-toggle aria-expanded="true">
                        <span>✦ Gợi ý câu hỏi</span>
                        <span class="mayhen_numerology_scoped_suggest_caret" aria-hidden="true">▾</span>
                    </button>
                    <span class="mayhen_numerology_scoped_guard_hint">Khóa chặt dữ kiện hồ sơ</span>
                </div>
                <div class="mayhen_numerology_scoped_suggestions" data-num-scoped-suggestions></div>
            </div>

            <form class="mayhen_numerology_scoped_form" data-num-scoped-form>
                <div class="mayhen_numerology_scoped_error" data-num-scoped-error></div>
                <div class="mayhen_numerology_chat_composer">
                    <textarea
                        id="numScopedInput"
                        name="question"
                        rows="1"
                        class="mayhen_numerology_chat_input"
                        minlength="4"
                        maxlength="600"
                        required
                        aria-label="Nhập câu hỏi đào sâu cho hồ sơ Thần Số Học"
                        placeholder="Hỏi về chỉ số, biểu đồ 3×3, đỉnh cao trong phần này..."
                    ></textarea>
                    <div class="mayhen_numerology_chat_composer_bar">
                        <div class="mayhen_numerology_chat_composer_meta">
                            <span class="mayhen_numerology_chat_counter" data-num-char-count>0/600</span>
                            <span class="mayhen_numerology_chat_keyhint">Enter gửi • Shift+Enter xuống dòng</span>
                        </div>
                        <button
                            type="submit"
                            class="mayhen_numerology_chat_send_btn"
                            data-num-scoped-submit
                            aria-label="Gửi câu hỏi"
                            title="Gửi câu hỏi (Enter)"
                        >
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 19V5"/>
                                <path d="m5 12 7-7 7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </aside>
    </div>

    <script id="mayhenNumCalcJson" type="application/json">@json($calc, JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenNumEnrichedJson" type="application/json">@json($enriched, JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenNumInterpJson" type="application/json">@json($interpretation, JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenNumEndpointsJson" type="application/json">@json($endpoints, JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenNumSectionDefsJson" type="application/json">@json($sectionDefinitions, JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenNumFollowupsJson" type="application/json">@json($followupsPayload, JSON_UNESCAPED_UNICODE)</script>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/numerology.js') }}"></script>
@endpush
