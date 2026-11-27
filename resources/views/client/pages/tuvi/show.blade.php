@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $isPartial = ($chart['status'] ?? '') === 'partial_no_birth_hour';
    $isWindow = ($chart['status'] ?? '') === 'window_candidates';
    $nickname = $chart['nickname'] ?? ($chartModel->profile?->nickname ?: 'Hồ Sơ Thiên Mệnh');
    $summary = $chart['summary'] ?? [];
    $birth = $chart['birth'] ?? [];
    $palacesByBranch = $chart['palaces_by_branch'] ?? [];
    $daiHanTimeline = $chart['dai_han_timeline'] ?? [];
    $verifiedPatterns = $ruleAnalysis['verified_patterns'] ?? [];
    $palaceDeepReports = $ruleAnalysis['palace_deep_reports'] ?? [];
    $careerOrientation = $ruleAnalysis['career_orientation'] ?? null;
    $thienBanOverview = $ruleAnalysis['thien_ban_overview'] ?? [];
    $plan = $interpretation['layer_6_action_plan'] ?? [];
    $palaceHighlights = $interpretation['layer_2_palace_highlights'] ?? [];
    $ageMu = (int) ($chart['age_mu'] ?? 1);
@endphp

@section('title', 'Lá Số Thiên Mệnh: ' . $nickname)
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/tuvi.css') }}">
@endpush

@section('content')
<div class="mayhen_tuvi_page" id="mayhenTuViShowApp" data-auto-open-menh="1">
    {{-- 1. PHẦN ĐẦU KHÔNG GIAN THIÊN VĂN & THẬP NHỊ THIÊN MỆNH BÀI 3D XÒE QUẠT TRÊN TAY --}}
    <section class="mayhen_tuvi_deck_section">
        <canvas class="mayhen_tuvi_starfield_canvas" aria-hidden="true"></canvas>
        <div class="mayhen_tuvi_container">
            <div class="mayhen_tuvi_btn_row" style="justify-content: space-between; margin-bottom: 18px;">
                <div class="mayhen_tuvi_btn_row">
                    <a href="{{ route('tuvi.index') }}" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm">&larr; Lập lá số mới</a>
                    <a href="{{ route('tuvi.profiles') }}" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm">Hồ Sơ Thiên Mệnh</a>
                    <a href="{{ route('tuvi.synastry.index') }}" class="mayhen_tuvi_btn_gold mayhen_tuvi_btn_sm">✦ Soi Nhân Duyên Song Mệnh</a>
                </div>
                <div class="mayhen_tuvi_btn_row">
                    @if($versions->count() > 1)
                        @foreach($versions as $ver)
                            <a href="{{ route('tuvi.show', $ver->slug) }}"
                               class="mayhen_tuvi_btn_sm {{ $ver->id === $chartModel->id ? 'mayhen_tuvi_btn_gold' : 'mayhen_tuvi_btn_outline' }}">
                                v{{ $ver->version_number }} ({{ $ver->viewed_year }})
                            </a>
                        @endforeach
                    @endif
                    <span class="mayhen_tuvi_kicker" style="margin: 0;">🔒 Riêng tư • v{{ $chartModel->version_number }}</span>
                </div>
            </div>

            <div class="mayhen_tuvi_deck_head">
                <div>
                    <span class="mayhen_tuvi_kicker">
                        ✦ {{ $chart['school_name'] ?? 'Nam Phái Việt Nam' }} • Trọng tâm: {{ $focusTopicLabel }}
                    </span>
                    <h2>{{ $nickname }} — {{ $interpretation['headline'] ?? 'Lá Số Thiên Mệnh' }}</h2>
                    <p>
                        {{ $birth['solar']['label'] ?? '' }} (Dương lịch) • {{ $birth['lunar']['label'] ?? '' }} (Âm lịch)
                        • Bản Mệnh: <strong>{{ $birth['nap_am']['name'] ?? '' }}</strong>
                        @if(! $isPartial)
                            • <strong>{{ $summary['cuc']['name'] ?? '' }}</strong>
                            • {{ $chart['yin_yang_gender_label'] ?? '' }}
                            • Lưu niên {{ $chart['viewed_year'] ?? '' }} ({{ $ageMu }} tuổi mụ)
                        @endif
                    </p>
                </div>
                @if(! $isPartial)
                    <div class="mayhen_tuvi_btn_row">
                        <div class="mayhen_tuvi_quality_bar" role="group" aria-label="Chất lượng hiệu ứng">
                            <button type="button" class="mayhen_tuvi_quality_btn" data-tuvi-quality="high">Đầy đủ 3D</button>
                            <button type="button" class="mayhen_tuvi_quality_btn" data-tuvi-quality="balanced">Cân bằng</button>
                            <button type="button" class="mayhen_tuvi_quality_btn" data-tuvi-quality="lightweight">Nhẹ máy</button>
                        </div>
                        <button type="button" class="mayhen_tuvi_btn_gold mayhen_tuvi_btn_sm" data-tuvi-deck-reveal-all>Lật mở 12 lá</button>
                        <button type="button" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm" data-tuvi-deck-reset>Úp gọn quạt</button>
                    </div>
                @endif
            </div>

            @if($isPartial)
                <div class="mayhen_tuvi_card" style="background: rgba(252,251,249,0.97); color: #1F1915;">
                    <span class="mayhen_tuvi_badge">Chế độ minh bạch khi chưa rõ giờ sinh</span>
                    <h3>Chưa đủ giờ sinh để lập 12 Cung &amp; Thập Nhị Thiên Mệnh Bài</h3>
                    <p>
                        {{ $chart['unknown_hour_notice'] ?? 'Mây Hẹn tuyệt đối không tự gán giờ giả 12:00.' }}
                        Bên dưới là phần phân tích trung thực cho Bản Mệnh Ngũ Hành ({{ $birth['nap_am']['name'] ?? '' }}) và năm sinh Âm lịch ({{ $birth['pillars']['year']['label'] ?? '' }}). Khi nhớ lại khung giờ hoặc khoảng giờ sinh, bạn có thể lập phiên bản mới bất cứ lúc nào.
                    </p>
                </div>
            @else
                <div class="mayhen_tuvi_fan_stage" data-tuvi-fan-stage>
                    <div class="mayhen_tuvi_fan_hand" data-tuvi-deck-carousel></div>
                </div>
                <p class="mayhen_tuvi_fan_hint">
                    ✦ 12 Thiên Mệnh Bài thật của bạn đang xòe trọn trên màn hình • Đã khám phá: <strong data-tuvi-explored-count>0/12</strong> cung • Chạm lá bất kỳ để mở giải mã ✦
                </p>
                <div class="mayhen_tuvi_card_drawer" data-tuvi-card-drawer aria-live="polite"></div>
            @endif
        </div>
    </section>

    {{-- CẢNH BÁO ỨNG VIÊN KHOẢNG GIỜ (NẾU CHỌN TIME_WINDOW) --}}
    @if($isWindow && ! empty($chart['candidates']))
        <section class="mayhen_tuvi_section_ivory" style="padding: 28px 0;">
            <div class="mayhen_tuvi_container">
                <div class="mayhen_tuvi_card">
                    <span class="mayhen_tuvi_badge">Đối chiếu khoảng giờ sinh ({{ count($chart['candidates']) }} khung giờ ứng viên)</span>
                    <h3>Các lá số ứng viên trong khoảng giờ bạn chọn</h3>
                    <p style="margin-bottom: 12px;">
                        Hệ thống đang hiển thị chi tiết khung giờ đại diện đầu tiên. Khi bạn nhớ lại giờ sinh chính xác hơn, hãy so sánh sự thay đổi của Cung Mệnh &amp; Chính tinh dưới đây:
                    </p>
                    <div class="mayhen_tuvi_wiki_grid">
                        @foreach($chart['candidates'] as $cand)
                            @php
                                $candMenhBranch = $cand['menh_branch_name'] ?? ($cand['menh_branch'] ?? '');
                                $candStarsRaw = $cand['menh_major_stars_label'] ?? ($cand['menh_major_stars'] ?? '');
                                $candStarsText = is_array($candStarsRaw)
                                    ? (count($candStarsRaw) > 0 ? implode(', ', $candStarsRaw) : 'Vô Chính Diệu')
                                    : ($candStarsRaw !== '' ? $candStarsRaw : 'Vô Chính Diệu');
                                $candThanPalace = $cand['than_palace_name'] ?? ($cand['chart']['summary']['than_palace_name'] ?? '');
                            @endphp
                            <div class="mayhen_tuvi_alert_info">
                                <strong>Giờ {{ $cand['branch_name'] ?? '' }} ({{ $cand['hour_range'] ?? '' }})</strong><br>
                                Cục: {{ $cand['cuc_name'] ?? '' }} • Mệnh tại {{ $candMenhBranch }}<br>
                                Chính tinh Mệnh: <em>{{ $candStarsText }}</em><br>
                                @if($candThanPalace !== '')
                                    Thân cư: {{ $candThanPalace }}
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- 2. KHÔNG GIAN ĐỌC SÁNG NGÀ 4 CHẾ ĐỘ MẠCH LẠC (KHÔNG RỜI RẠC) --}}
    <section class="mayhen_tuvi_reading_section">
        <div class="mayhen_tuvi_container">
            <div class="mayhen_tuvi_btn_row" style="justify-content: space-between; margin-bottom: 14px;">
                <div class="mayhen_tuvi_view_tabs" role="tablist" aria-label="4 góc nhìn lá số Tử Vi">
                    @if(! $isPartial)
                        <button type="button" class="mayhen_tuvi_view_tab is-active" data-view="board">1. Bảng Lá Số 4×4 &amp; Tổng Quan 12 Cung</button>
                    @endif
                    <button type="button" class="mayhen_tuvi_view_tab {{ $isPartial ? 'is-active' : '' }}" data-view="full">{{ $isPartial ? '1.' : '2.' }} Luận Giải Chuyên Sâu 12 Cung</button>
                    <button type="button" class="mayhen_tuvi_view_tab" data-view="quick">{{ $isPartial ? '2.' : '3.' }} Tóm Tắt 1 Phút</button>
                    <button type="button" class="mayhen_tuvi_view_tab" data-view="ai">{{ $isPartial ? '3.' : '4.' }} Hỏi Sâu AI &amp; Nhật Ký</button>
                </div>

                <div class="mayhen_tuvi_btn_row">
                    <button type="button" class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm" data-tuvi-share-btn>
                        Tạo Thẻ Chia Sẻ Ẩn Danh
                    </button>
                    <button type="button"
                            class="mayhen_tuvi_btn_outline mayhen_tuvi_btn_sm"
                            data-tuvi-revoke-btn
                            style="{{ $shareUrl ? '' : 'display:none;' }}">
                        Thu Hồi Chia Sẻ
                    </button>
                </div>
            </div>

            <div class="mayhen_tuvi_alert_info" data-tuvi-share-output style="margin-bottom: 18px; {{ $shareUrl ? '' : 'display:none;' }}">
                @if($shareUrl)
                    Link Thẻ Chia Sẻ (đã ẩn tên &amp; ngày giờ sinh): <a href="{{ $shareUrl }}" target="_blank" rel="noopener">{{ $shareUrl }}</a>
                @endif
            </div>

            {{-- TAB 2 (HOẶC 1 KHI CHƯA RÕ GIỜ): LUẬN GIẢI CHUYÊN SÂU 6 TẦNG & 12 CUNG --}}
            <div class="mayhen_tuvi_view_panel {{ $isPartial ? 'is-active' : '' }}" data-view-panel="full">
                <div class="mayhen_tuvi_card mayhen_tuvi_scope_card" data-tuvi-scope-section="core_overview" style="margin-bottom: 18px; border-color: #CFA860; background: #FAF5EB;">
                    <div class="mayhen_tuvi_section_header_row">
                        <div>
                            <span class="mayhen_tuvi_badge">
                                {{ $chartModel->ai_powered ? '✦ Luận giải AI chuyên sâu bám sát lá số thật' : '✦ Luận giải chuẩn hóa từ kho dữ liệu đã thẩm định' }}
                            </span>
                            <h3 style="font-size: 22px; color: #85182A; margin-bottom: 4px;">Thông Điệp Cốt Lõi</h3>
                        </div>
                        <button type="button" class="mayhen_tuvi_ask_scope_btn" data-tuvi-ask-trigger data-scope-type="section" data-section-key="core_overview" data-scope-label="Thông Điệp &amp; Trục Mệnh – Thân">
                            ✦ Hỏi sâu AI phần này
                        </button>
                    </div>
                    <div class="mayhen_tuvi_long_prose mayhen_md_prose" style="font-size: 15px;">{!! safe_ai_markdown_html($interpretation['core_message'] ?? '') !!}</div>
                </div>

                <div class="mayhen_tuvi_layers_grid">
                    <div class="mayhen_tuvi_card mayhen_tuvi_scope_card" data-tuvi-scope-section="core_overview">
                        <div class="mayhen_tuvi_section_header_row">
                            <div>
                                <span class="mayhen_tuvi_badge">Tầng 1 • Tổng Quan Bản Mệnh</span>
                                <h3>Bản Mệnh, Cục &amp; Trục Mệnh – Thân</h3>
                            </div>
                            <button type="button" class="mayhen_tuvi_ask_scope_btn" data-tuvi-ask-trigger data-scope-type="section" data-section-key="core_overview" data-scope-label="Tầng 1: Bản Mệnh, Cục &amp; Trục Mệnh – Thân">
                                ✦ Hỏi sâu
                            </button>
                        </div>
                        <div class="mayhen_tuvi_long_prose mayhen_md_prose">{!! safe_ai_markdown_html($interpretation['layer_1_overview'] ?? '') !!}</div>
                    </div>
                    <div class="mayhen_tuvi_card mayhen_tuvi_scope_card" data-tuvi-scope-section="combinations_tu_hoa">
                        <div class="mayhen_tuvi_section_header_row">
                            <div>
                                <span class="mayhen_tuvi_badge">Tầng 3 • Tổ Hợp &amp; Cách Cục</span>
                                <h3>Tam Phương Tứ Chính &amp; Tứ Hóa</h3>
                            </div>
                            <button type="button" class="mayhen_tuvi_ask_scope_btn" data-tuvi-ask-trigger data-scope-type="section" data-section-key="combinations_tu_hoa" data-scope-label="Tầng 3: Tam Phương Tứ Chính &amp; Tứ Hóa">
                                ✦ Hỏi sâu
                            </button>
                        </div>
                        <div class="mayhen_tuvi_long_prose mayhen_md_prose">{!! safe_ai_markdown_html($interpretation['layer_3_combinations'] ?? '') !!}</div>
                    </div>
                    <div class="mayhen_tuvi_card mayhen_tuvi_scope_card" data-tuvi-scope-section="love_family">
                        <div class="mayhen_tuvi_section_header_row">
                            <div>
                                <span class="mayhen_tuvi_badge">Tầng 4 • Tình Yêu &amp; Gia Đạo</span>
                                <h3>Nhân Duyên, Hôn Nhân &amp; Gia Đình Hai Bên</h3>
                            </div>
                            <button type="button" class="mayhen_tuvi_ask_scope_btn" data-tuvi-ask-trigger data-scope-type="section" data-section-key="love_family" data-scope-label="Tầng 4: Tình Yêu, Hôn Nhân &amp; Gia Đạo">
                                ✦ Hỏi sâu
                            </button>
                        </div>
                        <div class="mayhen_tuvi_long_prose mayhen_md_prose">{!! safe_ai_markdown_html($interpretation['layer_4_love_family'] ?? '') !!}</div>
                    </div>
                    <div class="mayhen_tuvi_card mayhen_tuvi_scope_card" data-tuvi-scope-section="life_stages">
                        <div class="mayhen_tuvi_section_header_row">
                            <div>
                                <span class="mayhen_tuvi_badge">Tầng 5 • Giai Đoạn Cuộc Đời</span>
                                <h3>Đại Hạn, Tiểu Hạn &amp; Lưu Niên {{ $chart['viewed_year'] ?? '' }}</h3>
                            </div>
                            <button type="button" class="mayhen_tuvi_ask_scope_btn" data-tuvi-ask-trigger data-scope-type="section" data-section-key="life_stages" data-scope-label="Tầng 5: Đại Hạn, Tiểu Hạn &amp; Lưu Niên">
                                ✦ Hỏi sâu
                            </button>
                        </div>
                        <div class="mayhen_tuvi_long_prose mayhen_md_prose">{!! safe_ai_markdown_html($interpretation['layer_5_life_stages'] ?? '') !!}</div>
                    </div>
                </div>

                {{-- THIÊN HƯỚNG NGHỀ NGHIỆP TỪ TRỤC MỆNH – TÀI – QUAN --}}
                @if(! empty($careerOrientation) || ! empty($interpretation['career_orientation']))
                    <div class="mayhen_tuvi_card mayhen_tuvi_career_card mayhen_tuvi_scope_card" data-tuvi-scope-section="career_orientation" data-tuvi-reveal style="margin-bottom: 20px;">
                        <div class="mayhen_tuvi_section_header_row">
                            <div>
                                <span class="mayhen_tuvi_badge">Định Hướng Nghề Nghiệp • Trục Mệnh – Tài – Quan</span>
                                <h3>{{ $careerOrientation['headline'] ?? 'Thiên Hướng Nghề Nghiệp & Tài Lộc Phù Hợp' }}</h3>
                            </div>
                            <button type="button" class="mayhen_tuvi_ask_scope_btn" data-tuvi-ask-trigger data-scope-type="section" data-section-key="career_orientation" data-scope-label="Thiên Hướng Nghề Nghiệp &amp; Tài Lộc">
                                ✦ Hỏi sâu AI phần này
                            </button>
                        </div>
                        <div class="mayhen_tuvi_long_prose mayhen_md_prose" style="margin-bottom: 12px;">{!! safe_ai_markdown_html($interpretation['career_orientation'] ?? ($careerOrientation['summary'] ?? '')) !!}</div>
                        @if(! empty($careerOrientation['roles']))
                            <div class="mayhen_tuvi_career_chips">
                                @foreach($careerOrientation['roles'] as $role)
                                    <span class="mayhen_tuvi_career_chip">{{ $role }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                {{-- THỂ TRẠNG TINH THẦN & MẠNG LƯỚI QUAN HỆ --}}
                @if(! empty($interpretation['health_and_network']))
                    <div class="mayhen_tuvi_card mayhen_tuvi_scope_card" data-tuvi-scope-section="health_network" data-tuvi-reveal style="margin-bottom: 20px;">
                        <div class="mayhen_tuvi_section_header_row">
                            <div>
                                <span class="mayhen_tuvi_badge">Cân Bằng Nội Lực &amp; Quan Hệ • Tật · Nô · Phụ · Huynh</span>
                                <h3>Nhịp Tinh Thần, Cộng Sự &amp; Hậu Phương Gia Phong</h3>
                            </div>
                            <button type="button" class="mayhen_tuvi_ask_scope_btn" data-tuvi-ask-trigger data-scope-type="section" data-section-key="health_network" data-scope-label="Thể Trạng Tinh Thần &amp; Quan Hệ Xã Hội">
                                ✦ Hỏi sâu AI phần này
                            </button>
                        </div>
                        <div class="mayhen_tuvi_long_prose mayhen_md_prose">{!! safe_ai_markdown_html($interpretation['health_and_network']) !!}</div>
                    </div>
                @endif

                {{-- HỒ SƠ LUẬN GIẢI CHI TIẾT ĐỦ 12 CUNG --}}
                @if(! empty($palaceDeepReports))
                    <div class="mayhen_tuvi_deep_head" data-tuvi-reveal>
                        <div>
                            <span class="mayhen_tuvi_badge">Luận Giải Từng Cung • Minh Bạch Căn Cứ Sao</span>
                            <h3>Hồ Sơ Chi Tiết 12 Cung Tử Vi</h3>
                            <p>Mỗi cung phân tách rõ <strong>Điểm Sáng Cát Tinh</strong> và <strong>Điểm Cần Lưu Tâm</strong>. Bấm <strong>✦ Hỏi sâu AI cung này</strong> trên bất kỳ cung nào để hỏi riêng về cung đó dựa trên sao thật.</p>
                        </div>
                        <div class="mayhen_tuvi_palace_filters" role="tablist" aria-label="Lọc nhóm cung">
                            <button type="button" class="mayhen_tuvi_pfilter_btn is-active" data-tuvi-pfilter="all">Tất cả 12 cung</button>
                            <button type="button" class="mayhen_tuvi_pfilter_btn" data-tuvi-pfilter="core">Mệnh · Tài · Quan · Di</button>
                            <button type="button" class="mayhen_tuvi_pfilter_btn" data-tuvi-pfilter="family">Nhân Duyên &amp; Gia Đạo</button>
                            <button type="button" class="mayhen_tuvi_pfilter_btn" data-tuvi-pfilter="foundation">Phúc · Điền · Tật · Nô</button>
                        </div>
                    </div>

                    <div class="mayhen_tuvi_deep_grid" data-tuvi-deep-grid>
                        @foreach($palaceDeepReports as $rep)
                            <article class="mayhen_tuvi_deep_card" id="tuvi-palace-{{ $rep['code'] }}" data-tuvi-pgroup="{{ $rep['group'] }}" data-tuvi-reveal>
                                <div class="mayhen_tuvi_deep_top">
                                    <div>
                                        <div class="mayhen_tuvi_deep_badges">
                                            <span class="mayhen_tuvi_deep_can_chi">{{ $rep['can_chi'] }} • ĐH {{ $rep['dai_han'] }}</span>
                                            @if($rep['is_than'])<span class="mayhen_tuvi_than_tag">CƯ THÂN</span>@endif
                                            @if($rep['is_lai_nhan'])<span class="mayhen_tuvi_deep_tag_gold">LAI NHÂN</span>@endif
                                            @if($rep['is_current_tieu_han'])<span class="mayhen_tuvi_han_pill mayhen_tuvi_han_pill--th">TIỂU HẠN {{ $chart['viewed_year'] ?? '' }}</span>@endif
                                            @if($rep['is_current_luu_nien'])<span class="mayhen_tuvi_han_pill mayhen_tuvi_han_pill--ln">LƯU NIÊN</span>@endif
                                        </div>
                                        <h4>{{ $rep['name'] }} <small>({{ $rep['hanzi'] }})</small></h4>
                                    </div>
                                    <div class="mayhen_tuvi_deep_score mayhen_tuvi_tier--{{ $rep['tier_code'] }}">
                                        <strong>{{ $rep['score'] }}</strong>
                                        <span>{{ $rep['tier_label'] }}</span>
                                    </div>
                                </div>

                                <div class="mayhen_tuvi_deep_stars_line">
                                    <strong>Chính tinh:</strong> {{ $rep['stars_headline'] }}
                                </div>
                                <div class="mayhen_tuvi_deep_modern mayhen_tuvi_long_prose mayhen_md_prose">{!! safe_ai_markdown_html($palaceHighlights[$rep['code']] ?? $rep['modern_meaning']) !!}</div>

                                <div class="mayhen_tuvi_deep_split">
                                    <div class="mayhen_tuvi_deep_col mayhen_tuvi_deep_col--good">
                                        <div class="mayhen_tuvi_deep_col_title">Điểm Sáng &amp; Cát Tinh Trợ Lực</div>
                                        <ul>
                                            @foreach($rep['positive_points'] as $pt)
                                                <li><strong>{{ $pt['star'] }}:</strong> {!! safe_ai_markdown_inline($pt['text']) !!}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="mayhen_tuvi_deep_col mayhen_tuvi_deep_col--warn">
                                        <div class="mayhen_tuvi_deep_col_title">Điểm Cần Lưu Tâm &amp; Điều Tiết</div>
                                        <ul>
                                            @foreach($rep['caution_points'] as $ct)
                                                <li><strong>{{ $ct['star'] }}:</strong> {!! safe_ai_markdown_inline($ct['text']) !!}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>

                                <div class="mayhen_tuvi_deep_foot">
                                    <span>{{ $rep['tptc_summary'] }}</span>
                                    <button type="button"
                                            class="mayhen_tuvi_ask_scope_btn"
                                            data-tuvi-ask-trigger
                                            data-scope-type="palace"
                                            data-palace-code="{{ $rep['code'] }}"
                                            data-scope-label="{{ $rep['name'] }} ({{ $rep['can_chi'] }})">
                                        ✦ Hỏi sâu AI cung này
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @elseif(! empty($palaceHighlights))
                    <div class="mayhen_tuvi_card" style="margin-bottom: 20px;">
                        <span class="mayhen_tuvi_badge">Tầng 2 • 6 Cung Trọng Tâm</span>
                        <h3>Điểm Nhấn Các Cung Quan Trọng Nhất</h3>
                        <div class="mayhen_tuvi_wiki_grid" style="margin-top: 12px;">
                            @foreach($palaceHighlights as $pCode => $pText)
                                <div class="mayhen_tuvi_alert_info">
                                    <strong>{{ $palacesMeta[$pCode]['name'] ?? $pCode }}</strong>
                                    <div class="mayhen_tuvi_long_prose mayhen_md_prose" style="margin: 6px 0 0; font-size: 13.5px;">{!! safe_ai_markdown_html($pText) !!}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mayhen_tuvi_layers_grid">
                    <div class="mayhen_tuvi_card">
                        <span class="mayhen_tuvi_badge">Điểm Nên Phát Huy</span>
                        <h3>Thế Mạnh Tự Nhiên</h3>
                        <ul class="mayhen_tuvi_list">
                            @foreach($interpretation['strengths'] ?? [] as $item)
                                <li>{!! safe_ai_markdown_inline($item) !!}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="mayhen_tuvi_card">
                        <span class="mayhen_tuvi_badge">Điểm Cần Lưu Tâm</span>
                        <h3>Điều Nên Quan Sát &amp; Giữ Nhịp</h3>
                        <ul class="mayhen_tuvi_list">
                            @foreach($interpretation['watch_outs'] ?? [] as $item)
                                <li>{!! safe_ai_markdown_inline($item) !!}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- TẦNG 6: KẾ HOẠCH HÀNH ĐỘNG --}}
                <div class="mayhen_tuvi_card mayhen_tuvi_scope_card" data-tuvi-scope-section="action_plan" style="margin-bottom: 22px;">
                    <div class="mayhen_tuvi_section_header_row">
                        <div>
                            <span class="mayhen_tuvi_badge">Tầng 6 • Gợi Ý Ứng Dụng Thực Tế</span>
                            <h3>Lộ Trình Hành Động &amp; Câu Hỏi Chiêm Nghiệm</h3>
                        </div>
                        <button type="button" class="mayhen_tuvi_ask_scope_btn" data-tuvi-ask-trigger data-scope-type="section" data-section-key="action_plan" data-scope-label="Tầng 6: Lộ Trình Hành Động">
                            ✦ Hỏi sâu AI phần này
                        </button>
                    </div>
                    <div class="mayhen_tuvi_form_grid" style="margin-top: 12px;">
                        <div class="mayhen_tuvi_alert_info"><strong>Hôm nay:</strong> {!! safe_ai_markdown_inline($plan['today'] ?? '') !!}</div>
                        <div class="mayhen_tuvi_alert_info"><strong>Trong tuần này:</strong> {!! safe_ai_markdown_inline($plan['this_week'] ?? '') !!}</div>
                        <div class="mayhen_tuvi_alert_info"><strong>30 ngày tới:</strong> {!! safe_ai_markdown_inline($plan['next_30_days'] ?? '') !!}</div>
                        <div class="mayhen_tuvi_alert_info"><strong>Dài hạn:</strong> {!! safe_ai_markdown_inline($plan['long_term'] ?? '') !!}</div>
                    </div>
                    @if(! empty($interpretation['reflection_question']))
                        <p style="margin-top: 14px; font-style: italic; color: #85182A;">
                            <strong>Câu hỏi tự chiêm nghiệm:</strong> “{!! safe_ai_markdown_inline($interpretation['reflection_question']) !!}”
                        </p>
                    @endif
                </div>

                {{-- TIMELINE ĐẠI HẠN 120 NĂM --}}
                @if(! empty($daiHanTimeline))
                    <div class="mayhen_tuvi_card mayhen_tuvi_scope_card" data-tuvi-scope-section="dai_han_timeline">
                        <div class="mayhen_tuvi_section_header_row">
                            <div>
                                <span class="mayhen_tuvi_badge">Hành Trình Cuộc Đời (12 Đại Hạn × 10 Năm)</span>
                                <h3>Dòng Thời Gian Đại Hạn</h3>
                            </div>
                            <button type="button" class="mayhen_tuvi_ask_scope_btn" data-tuvi-ask-trigger data-scope-type="section" data-section-key="dai_han_timeline" data-scope-label="Dòng Thời Gian 12 Đại Hạn">
                                ✦ Hỏi sâu AI phần này
                            </button>
                        </div>
                        <div class="mayhen_tuvi_timeline_track">
                            @foreach($daiHanTimeline as $dh)
                                @php $isCurrentDh = $ageMu >= ($dh['age_start'] ?? 0) && $ageMu <= ($dh['age_end'] ?? 0); @endphp
                                <div class="mayhen_tuvi_timeline_item {{ $isCurrentDh ? 'is-current' : '' }}">
                                    <div style="font-size: 11px; font-weight: 700; color: #85182A;">
                                        {{ $dh['age_start'] }}–{{ $dh['age_end'] }} tuổi {{ $isCurrentDh ? '• HIỆN TẠI' : '' }}
                                    </div>
                                    <div style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 16px; font-weight: 700; margin: 4px 0;">
                                        {{ $dh['palace_name'] }}
                                    </div>
                                    <div style="font-size: 12px; color: #5C5044;">
                                        {{ $dh['can_chi'] ?? '' }} • {{ ! empty($dh['major_stars']) ? implode(', ', $dh['major_stars']) : 'Vô Chính Diệu' }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- TAB 2: ĐỌC NHANH 1 PHÚT --}}
            <div class="mayhen_tuvi_view_panel" data-view-panel="quick">
                <div class="mayhen_tuvi_card" style="margin-bottom: 18px;">
                    <span class="mayhen_tuvi_badge">Tóm Lược Trong 60 Giây</span>
                    <h3 style="color: #85182A;">{{ $interpretation['headline'] ?? '' }}</h3>
                    <div class="mayhen_tuvi_long_prose mayhen_md_prose" style="margin-bottom: 14px;">{!! safe_ai_markdown_html($interpretation['core_message'] ?? '') !!}</div>
                    <h4>3 Điểm Nên Phát Huy Ngay</h4>
                    <ul class="mayhen_tuvi_list">
                        @foreach($interpretation['strengths'] ?? [] as $item)
                            <li>{!! safe_ai_markdown_inline($item) !!}</li>
                        @endforeach
                    </ul>
                    <h4 style="margin-top: 16px;">4 Việc Cụ Thể Có Thể Bắt Đầu</h4>
                    <ul class="mayhen_tuvi_list">
                        <li><strong>Hôm nay:</strong> {!! safe_ai_markdown_inline($plan['today'] ?? '') !!}</li>
                        <li><strong>Tuần này:</strong> {!! safe_ai_markdown_inline($plan['this_week'] ?? '') !!}</li>
                        <li><strong>30 ngày:</strong> {!! safe_ai_markdown_inline($plan['next_30_days'] ?? '') !!}</li>
                        <li><strong>Dài hạn:</strong> {!! safe_ai_markdown_inline($plan['long_term'] ?? '') !!}</li>
                    </ul>
                </div>
            </div>

            @if(! $isPartial)
                {{-- TAB 1 (MẶC ĐỊNH): BẢNG LÁ SỐ TỬ VI 4x4 TƯƠNG TÁC, TỨ HÓA & CÁCH CỤC --}}
                <div class="mayhen_tuvi_view_panel is-active" data-view-panel="board">
                    @include('client.pages.tuvi.partials.laso-board', [
                        'chart' => $chart,
                        'nickname' => $nickname,
                        'showExportControls' => true,
                        'isDemo' => false,
                    ])

                    {{-- TỔNG QUAN 6 TRỤ THIÊN BÀN & CÂN LƯỢNG / LAI NHÂN / TUỔI HỢP --}}
                    @if(! empty($thienBanOverview))
                        <div class="mayhen_tuvi_section_header_row" style="margin-top: 8px;">
                            <div>
                                <span class="mayhen_tuvi_badge">Tổng Quan Thiên Bàn</span>
                                <h3 style="margin: 0;">6 Trụ Cốt Lõi &amp; Lai Nhân Cung</h3>
                            </div>
                            <button type="button" class="mayhen_tuvi_ask_scope_btn" data-tuvi-ask-trigger data-scope-type="section" data-section-key="thien_ban_overview" data-scope-label="Thiên Bàn, Lai Nhân Cung &amp; Cân Lượng">
                                ✦ Hỏi sâu AI Thiên Bàn
                            </button>
                        </div>
                        <div class="mayhen_tuvi_tb_grid" data-tuvi-reveal>
                            <div class="mayhen_tuvi_tb_card">
                                <span class="mayhen_tuvi_tb_kicker">Bản Mệnh &amp; Cục</span>
                                <strong>{{ $thienBanOverview['nap_am'] ?? '' }}</strong>
                                <p>{{ $thienBanOverview['cuc'] ?? '' }} — {{ $thienBanOverview['cuc_menh_relation'] ?? '' }}</p>
                            </div>
                            <div class="mayhen_tuvi_tb_card">
                                <span class="mayhen_tuvi_tb_kicker">Chủ Mệnh &amp; Chủ Thân</span>
                                <strong>Chủ Mệnh: {{ $thienBanOverview['chu_menh'] ?: '—' }} • Chủ Thân: {{ $thienBanOverview['chu_than'] ?: '—' }}</strong>
                                <p>Thân cư {{ $thienBanOverview['than_palace_name'] ?? '' }} • {{ $thienBanOverview['am_duong_gender'] ?? '' }} ({{ $thienBanOverview['am_duong_ly'] ?? '' }})</p>
                            </div>
                            <div class="mayhen_tuvi_tb_card">
                                <span class="mayhen_tuvi_tb_kicker">Lai Nhân Cung</span>
                                <strong>{{ $thienBanOverview['lai_nhan_palace_name'] ?? '' }}</strong>
                                <p>Cung đồng Thiên Can với năm sinh — nơi khơi nguồn nhân duyên và trọng tâm ảnh hưởng dài hạn của lá số.</p>
                            </div>
                            @if(! empty($thienBanOverview['can_luong']))
                                <div class="mayhen_tuvi_tb_card">
                                    <span class="mayhen_tuvi_tb_kicker">Cân Lượng Tử Vi (Viên Thiên Cương)</span>
                                    <strong>{{ $thienBanOverview['can_luong']['label'] }} — {{ $thienBanOverview['can_luong']['tier'] }}</strong>
                                    <p>{{ $thienBanOverview['can_luong']['note'] }}</p>
                                </div>
                            @endif
                            @if(! empty($thienBanOverview['zodiac_harmony']))
                                <div class="mayhen_tuvi_tb_card mayhen_tuvi_tb_card--wide">
                                    <span class="mayhen_tuvi_tb_kicker">Địa Chi Tam Hợp · Lục Hợp · Tứ Hành Xung</span>
                                    <strong>
                                        Tam hợp: {{ implode(', ', $thienBanOverview['zodiac_harmony']['tam_hop'] ?? []) }}
                                        • Lục hợp: {{ $thienBanOverview['zodiac_harmony']['luc_hop'] ?? '' }}
                                    </strong>
                                    <p>Nhóm xung nhịp cần giao tiếp khéo léo: {{ implode(', ', $thienBanOverview['zodiac_harmony']['tu_hanh_xung'] ?? []) }} (tham chiếu tính khí, không phải kiêng kỵ tuyệt đối).</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- BỨC TRANH TỔNG QUAN 12 CUNG (BIỂU ĐỒ THANH ANIMATION — BẤM XEM CHI TIẾT) --}}
                    @if(! empty($palaceDeepReports))
                        <div class="mayhen_tuvi_card mayhen_tuvi_overview_bars_card" data-tuvi-score-bars style="margin-bottom: 22px;">
                            <div class="mayhen_tuvi_deep_head" style="margin: 0 0 14px;">
                                <div>
                                    <span class="mayhen_tuvi_badge">Bức Tranh Tổng Quan 12 Cung</span>
                                    <h3 style="margin: 0 0 4px;">Độ Vượng &amp; Cân Bằng Năng Lượng 12 Cung</h3>
                                    <p style="margin: 0;">Tính minh bạch theo độ sáng Chính tinh, Lục Cát/Lục Sát, Tứ Hóa và Tuần/Triệt — bấm vào bất kỳ cung nào để mở bài luận chi tiết cung đó.</p>
                                </div>
                            </div>
                            <div class="mayhen_tuvi_score_grid">
                                @foreach($palaceDeepReports as $idx => $rep)
                                    <button type="button"
                                            class="mayhen_tuvi_score_row mayhen_tuvi_tier--{{ $rep['tier_code'] }}"
                                            data-jump-palace="{{ $rep['code'] }}"
                                            style="--tuvi-bar-pct: {{ $rep['score'] }}%; --tuvi-bar-delay: {{ $idx * 55 }}ms;">
                                        <div class="mayhen_tuvi_score_meta">
                                            <span class="mayhen_tuvi_score_name">{{ $rep['name'] }} <small>({{ $rep['can_chi'] }})</small></span>
                                            <span class="mayhen_tuvi_score_val">{{ $rep['score'] }} · {{ $rep['tier_label'] }}</span>
                                        </div>
                                        <div class="mayhen_tuvi_score_track">
                                            <div class="mayhen_tuvi_score_fill"></div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- CĂN CỨ AN SAO, BẢNG TỨ HÓA & CÁCH CỤC GỘP LIỀN MẠCH DƯỚI THIÊN BÀN --}}
                    <div class="mayhen_tuvi_layers_grid">
                        <div class="mayhen_tuvi_card">
                            <span class="mayhen_tuvi_badge">Minh Bạch Thuật Toán</span>
                            <h3>Căn Cứ Quy Đổi &amp; An Sao</h3>
                            <table class="mayhen_tuvi_table" style="margin-top: 10px;">
                                <tbody>
                                    <tr><th>Trường phái</th><td>{{ $chart['school_name'] ?? '' }} ({{ $chart['rule_version'] ?? '' }})</td></tr>
                                    <tr><th>Dương lịch</th><td>{{ $birth['solar']['label'] ?? '' }} ({{ $birth['timezone'] ?? 'Asia/Ho_Chi_Minh' }})</td></tr>
                                    <tr><th>Âm lịch</th><td>{{ $birth['lunar']['label'] ?? '' }}</td></tr>
                                    <tr><th>Năm Can Chi</th><td>{{ $birth['pillars']['year']['label'] ?? '' }} — {{ $summary['nap_am']['name'] ?? '' }}</td></tr>
                                    <tr><th>Giờ Địa Chi</th><td>{{ $birth['pillars']['hour']['label'] ?? '' }} ({{ $birth['pillars']['hour']['range'] ?? '' }})</td></tr>
                                    <tr><th>Ngũ Hành Cục</th><td>{{ $summary['cuc']['name'] ?? '' }}</td></tr>
                                    <tr><th>Tuần / Triệt</th><td>Tuần: {{ implode(', ', $summary['tuan_branches'] ?? []) }} • Triệt: {{ implode(', ', $summary['triet_branches'] ?? []) }}</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mayhen_tuvi_card">
                            <span class="mayhen_tuvi_badge">Tứ Hóa Năm Sinh (Nam Phái)</span>
                            <h3>Bảng Tứ Hóa &amp; Vận Hạn Hiện Tại</h3>
                            <table class="mayhen_tuvi_table" style="margin-top: 10px;">
                                <thead>
                                    <tr><th>Hóa Tinh</th><th>Đi kèm sao</th><th>Tại cung</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($summary['tu_hoa'] ?? [] as $th)
                                        <tr>
                                            <td><strong>{{ $th['hoa_name'] ?? '' }}</strong></td>
                                            <td>{{ $th['star_name'] ?? '' }}</td>
                                            <td>{{ $th['palace_name'] ?? '' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mayhen_tuvi_card mayhen_tuvi_scope_card" data-tuvi-scope-section="verified_patterns">
                        <div class="mayhen_tuvi_section_header_row">
                            <div>
                                <span class="mayhen_tuvi_badge">Cách Cục Đã Xác Minh Bằng Rule Engine</span>
                                <h3>Danh Sách Cách Cục Hiện Diện Trên Lá Số</h3>
                            </div>
                            <button type="button" class="mayhen_tuvi_ask_scope_btn" data-tuvi-ask-trigger data-scope-type="section" data-section-key="verified_patterns" data-scope-label="Cách Cục &amp; Cấu Trúc Sao">
                                ✦ Hỏi sâu AI phần này
                            </button>
                        </div>
                        @forelse($verifiedPatterns as $pat)
                            <div class="mayhen_tuvi_alert_info" style="margin-top: 10px;">
                                <strong>{{ $pat['name'] }} ({{ $pat['hanzi'] ?? '' }})</strong> — <em>{{ $pat['source'] ?? 'Cổ pháp Nam Phái' }}</em><br>
                                <span>Căn cứ kiểm chứng: {{ $pat['evidence'] ?? '' }}</span><br>
                                <span>{{ $pat['plain_explanation'] ?? '' }}</span>
                                @if(! empty($pat['expert_explanation']))
                                    <div style="margin-top: 4px; font-size: 12.5px; color: #5C5044;"><strong>Góc nhìn chuyên sâu:</strong> {{ $pat['expert_explanation'] }}</div>
                                @endif
                            </div>
                        @empty
                            <p>Lá số không rơi vào các cách cục đặc thù có tên gọi riêng trong danh mục chuẩn — điều này hoàn toàn bình thường; trọng tâm đọc vẫn nằm ở bộ chính tinh, Tam Phương Tứ Chính và Tứ Hóa từng cung.</p>
                        @endforelse
                    </div>
                </div>
            @endif

            {{-- TAB 5: HỎI SÂU AI & NHẬT KÝ 30/90 NGÀY --}}
            <div class="mayhen_tuvi_view_panel" data-view-panel="ai">
                <div class="mayhen_tuvi_layers_grid">
                    <div class="mayhen_tuvi_card">
                        <span class="mayhen_tuvi_badge">Trợ Lý Tử Vi AI Khóa Chặt Lá Số (Tối đa 16 lượt)</span>
                        <h3>Hỏi Sâu Theo Từng Cung Hoặc Từng Phần Đã Luận</h3>
                        <p style="margin-bottom: 12px;">
                            Chọn đúng Cung hoặc Phần luận giải cần làm rõ. AI chỉ đối chiếu dữ liệu đã xác minh trên lá số này và từ chối mọi câu hỏi ngoài luồng.
                        </p>
                        <form data-tuvi-followup-form>
                            <div class="mayhen_tuvi_field">
                                <label for="tuviFollowupPalace">Phạm vi hỏi sâu (khóa chặt dữ kiện)</label>
                                <select id="tuviFollowupPalace" name="palace_code" class="mayhen_tuvi_select">
                                    <option value="">Tổng thể lá số</option>
                                    <optgroup label="12 Cung Tử Vi">
                                        @foreach($palacesMeta as $pCode => $pMeta)
                                            <option value="{{ $pCode }}">{{ $pMeta['name'] }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Các Phần Luận Giải Chuyên Sâu">
                                        @foreach($sectionDefinitions as $sKey => $sDef)
                                            <option value="{{ $sDef['scope_code'] }}">{{ $sDef['label'] }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="mayhen_tuvi_field">
                                <label for="tuviFollowupQuestion">Câu hỏi của bạn *</label>
                                <textarea id="tuviFollowupQuestion" name="question" rows="3" class="mayhen_tuvi_textarea" minlength="4" maxlength="600" required placeholder="Ví dụ: Với Cung Phu Thê này, mình nên rèn thói quen giao tiếp nào trước khi kết hôn?"></textarea>
                            </div>
                            <div class="mayhen_tuvi_scoped_error" data-tuvi-followup-error style="display:none;"></div>
                            <button type="submit" class="mayhen_tuvi_btn_primary">Hỏi Sâu Trên Lá Số Này</button>
                        </form>

                        <div data-tuvi-followup-list style="display: flex; flex-direction: column; gap: 10px; margin-top: 16px;">
                            @foreach($followupsPayload as $fu)
                                <div class="mayhen_tuvi_card" style="padding: 12px 14px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                                        <span class="mayhen_tuvi_scope_pill">{{ $fu['scope_label'] }}</span>
                                        <small style="color: #6B5F52;">{{ $fu['created_at'] ?? '' }}</small>
                                    </div>
                                    <div style="font-weight: 700; color: #85182A; margin-bottom: 4px;">Hỏi: {{ $fu['question'] }}</div>
                                    <div class="mayhen_tuvi_long_prose mayhen_md_prose" style="font-size: 13.5px;">{!! safe_ai_markdown_html($fu['answer']) !!}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mayhen_tuvi_card">
                        <span class="mayhen_tuvi_badge">Đồng Hành Sau Khi Đọc</span>
                        <h3>Nhật Ký Chiêm Nghiệm 30 / 90 Ngày</h3>
                        <p style="margin-bottom: 12px;">Ghi lại điều bạn quyết định thực hành và bài học rút ra để nhìn lại hành trình trưởng thành của chính mình.</p>
                        <form data-tuvi-journal-form>
                            <div class="mayhen_tuvi_field">
                                <label for="tuviJournalMilestone">Mốc chiêm nghiệm</label>
                                <select id="tuviJournalMilestone" name="milestone_days" class="mayhen_tuvi_select">
                                    <option value="0">Ghi chú ngay hôm nay</option>
                                    <option value="30" selected>Nhìn lại sau 30 ngày</option>
                                    <option value="90">Nhìn lại sau 90 ngày</option>
                                </select>
                            </div>
                            <div class="mayhen_tuvi_field">
                                <label for="tuviJournalFocus">Điều tâm đắc nhất</label>
                                <input id="tuviJournalFocus" type="text" name="focus_note" class="mayhen_tuvi_input" maxlength="500" placeholder="Ví dụ: Học cách lắng nghe trước khi phản ứng nhanh...">
                            </div>
                            <div class="mayhen_tuvi_field">
                                <label for="tuviJournalActions">Việc mình sẽ làm thực tế</label>
                                <textarea id="tuviJournalActions" name="actions_taken" rows="2" class="mayhen_tuvi_textarea" maxlength="500"></textarea>
                            </div>
                            <button type="submit" class="mayhen_tuvi_btn_outline">Lưu Vào Nhật Ký Riêng Tư</button>
                        </form>

                        <div data-tuvi-journal-list style="display: flex; flex-direction: column; gap: 10px; margin-top: 16px;">
                            @foreach($chartModel->journals->sortByDesc('id') as $jn)
                                <div class="mayhen_tuvi_card" style="padding: 12px 14px;">
                                    <strong>Mốc {{ $jn->milestone_days }} ngày • {{ $jn->created_at?->format('H:i • d/m/Y') }}</strong>
                                    <p style="font-size: 13px;">{{ $jn->actions_taken ?: $jn->focus_note }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- PHẢN HỒI HỮU ÍCH & GỢI Ý HỆ SINH THÁI --}}
            <div class="mayhen_tuvi_card" style="margin-top: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                    <strong>Bản luận giải này có giúp bạn nhìn rõ hướng đi hơn không?</strong>
                    <span data-tuvi-feedback-status style="margin-left: 8px; font-size: 13px; color: #1E4D3B;"></span>
                </div>
                <div class="mayhen_tuvi_pill_group">
                    <button type="button" class="mayhen_tuvi_choice_pill {{ $chartModel->feedback_rating === 'very_helpful' ? 'is-active' : '' }}" data-tuvi-feedback-rating="very_helpful">Rất sâu sắc</button>
                    <button type="button" class="mayhen_tuvi_choice_pill {{ $chartModel->feedback_rating === 'helpful' ? 'is-active' : '' }}" data-tuvi-feedback-rating="helpful">Hữu ích</button>
                    <button type="button" class="mayhen_tuvi_choice_pill {{ $chartModel->feedback_rating === 'neutral' ? 'is-active' : '' }}" data-tuvi-feedback-rating="neutral">Bình thường</button>
                </div>
            </div>
        </div>
    </section>

    {{-- NGĂN KÉO CHAT HỎI SÂU AI THEO TỪNG PHẦN / CUNG (BẤM PHẦN NÀO HỎI NGAY PHẦN ĐÓ) --}}
    <div class="mayhen_tuvi_scoped_backdrop" data-tuvi-scoped-backdrop aria-hidden="true">
        <aside class="mayhen_tuvi_scoped_modal" data-tuvi-scoped-modal role="dialog" aria-modal="true" aria-labelledby="tuviScopedTitle">
            <header class="mayhen_tuvi_scoped_head">
                <div class="mayhen_tuvi_scoped_head_info">
                    <div class="mayhen_tuvi_scoped_title_row">
                        <h3 id="tuviScopedTitle" data-tuvi-scoped-title>Hỏi Sâu AI</h3>
                        <span class="mayhen_tuvi_scope_pill mayhen_tuvi_scope_pill--compact" data-tuvi-scoped-badge>Khóa dữ kiện</span>
                    </div>
                    <p class="mayhen_tuvi_scoped_desc" data-tuvi-scoped-desc></p>
                </div>
                <button type="button" class="mayhen_tuvi_scoped_close" data-tuvi-scoped-close aria-label="Đóng khung chat AI">&times;</button>
            </header>

            <div class="mayhen_tuvi_scoped_chat_stage">
                <div class="mayhen_tuvi_scoped_history" data-tuvi-scoped-history aria-live="polite"></div>
                <button
                    type="button"
                    class="mayhen_tuvi_chat_scroll_btn"
                    data-tuvi-scroll-bottom
                    aria-label="Cuộn xuống tin nhắn mới nhất"
                    title="Cuộn xuống cuối đoạn chat"
                >
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 5v14"/>
                        <path d="m19 12-7 7-7-7"/>
                    </svg>
                </button>
            </div>

            <div class="mayhen_tuvi_scoped_suggest_wrap" data-tuvi-scoped-suggest-wrap>
                <div class="mayhen_tuvi_scoped_suggest_bar">
                    <button type="button" class="mayhen_tuvi_scoped_suggest_toggle" data-tuvi-scoped-suggest-toggle aria-expanded="true">
                        <span>✦ Gợi ý câu hỏi</span>
                        <span class="mayhen_tuvi_scoped_suggest_caret" aria-hidden="true">▾</span>
                    </button>
                    <span class="mayhen_tuvi_scoped_guard_hint">Chỉ trả lời theo dữ kiện phần này</span>
                </div>
                <div class="mayhen_tuvi_scoped_suggestions" data-tuvi-scoped-suggestions></div>
            </div>

            <form class="mayhen_tuvi_scoped_form" data-tuvi-scoped-form>
                <div class="mayhen_tuvi_scoped_error" data-tuvi-scoped-error></div>
                <div class="mayhen_tuvi_chat_composer">
                    <textarea
                        id="tuviScopedInput"
                        name="question"
                        rows="1"
                        class="mayhen_tuvi_chat_input"
                        minlength="4"
                        maxlength="600"
                        required
                        aria-label="Nhập câu hỏi đào sâu cho phần đang xem"
                        placeholder="Hỏi về sao, hạn, ý nghĩa trong phần này..."
                    ></textarea>
                    <div class="mayhen_tuvi_chat_composer_bar">
                        <div class="mayhen_tuvi_chat_composer_meta">
                            <span class="mayhen_tuvi_chat_counter" data-tuvi-char-count>0/600</span>
                            <span class="mayhen_tuvi_chat_keyhint">Enter gửi • Shift+Enter xuống dòng</span>
                        </div>
                        <button
                            type="submit"
                            class="mayhen_tuvi_chat_send_btn"
                            data-tuvi-scoped-submit
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

    {{-- JSON DATA ISLANDS --}}
    <script id="mayhenTuViChartJson" type="application/json">@json($chart, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenTuViRuleJson" type="application/json">@json($ruleAnalysis, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenTuViPalacesJson" type="application/json">@json($palacesMeta, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenTuViEndpointsJson" type="application/json">@json($endpoints, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenTuViSectionDefsJson" type="application/json">@json($sectionDefinitions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenTuViFollowupsJson" type="application/json">@json($followupsPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE)</script>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/tuvi.js') }}"></script>
@endpush
