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
                            <div class="mayhen_tuvi_alert_info">
                                <strong>Giờ {{ $cand['branch_name'] }} ({{ $cand['hour_range'] }})</strong><br>
                                Cục: {{ $cand['cuc_name'] }} • Mệnh tại {{ $cand['menh_branch_name'] }}<br>
                                Chính tinh Mệnh: <em>{{ $cand['menh_major_stars'] ?: 'Vô Chính Diệu' }}</em><br>
                                Thân cư: {{ $cand['than_palace_name'] }}
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
                    <button type="button" class="mayhen_tuvi_view_tab is-active" data-view="full">1. Luận Giải 6 Tầng &amp; Đại Hạn</button>
                    <button type="button" class="mayhen_tuvi_view_tab" data-view="quick">2. Tóm Tắt 1 Phút</button>
                    @if(! $isPartial)
                        <button type="button" class="mayhen_tuvi_view_tab" data-view="board">3. Thiên Bàn 4×4, Tứ Hóa &amp; Cách Cục</button>
                    @endif
                    <button type="button" class="mayhen_tuvi_view_tab" data-view="ai">4. Hỏi Sâu AI &amp; Nhật Ký</button>
                </div>

                <div class="mayhen_tuvi_btn_row">
                    @if(! $chartModel->ai_powered && ! $isPartial)
                        <button type="button" class="mayhen_tuvi_btn_primary mayhen_tuvi_btn_sm" data-tuvi-reinterpret-btn>
                            ✦ Chạy Lại Luận Giải AI 6 Tầng
                        </button>
                    @endif
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

            {{-- TAB 1: LUẬN GIẢI 6 TẦNG ĐẦY ĐỦ --}}
            <div class="mayhen_tuvi_view_panel is-active" data-view-panel="full">
                <div class="mayhen_tuvi_card" style="margin-bottom: 18px; border-color: #CFA860; background: #FAF5EB;">
                    <span class="mayhen_tuvi_badge">
                        {{ $chartModel->ai_powered ? '✦ Luận giải AI 6 tầng bám sát lá số thật' : '✦ Luận giải chuẩn hóa từ kho dữ liệu đã thẩm định' }}
                    </span>
                    <h3 style="font-size: 22px; color: #85182A;">Thông Điệp Cốt Lõi</h3>
                    <p style="font-size: 15px;">{{ $interpretation['core_message'] ?? '' }}</p>
                </div>

                <div class="mayhen_tuvi_layers_grid">
                    <div class="mayhen_tuvi_card">
                        <span class="mayhen_tuvi_badge">Tầng 1 • Tổng Quan Bản Mệnh</span>
                        <h3>Bản Mệnh, Cục &amp; Trục Mệnh – Thân</h3>
                        <p>{{ $interpretation['layer_1_overview'] ?? '' }}</p>
                    </div>
                    <div class="mayhen_tuvi_card">
                        <span class="mayhen_tuvi_badge">Tầng 3 • Tổ Hợp &amp; Cách Cục</span>
                        <h3>Tam Phương Tứ Chính &amp; Tứ Hóa</h3>
                        <p>{{ $interpretation['layer_3_combinations'] ?? '' }}</p>
                    </div>
                    <div class="mayhen_tuvi_card">
                        <span class="mayhen_tuvi_badge">Tầng 4 • Tình Yêu &amp; Gia Đạo</span>
                        <h3>Nhân Duyên, Hôn Nhân &amp; Gia Đình Hai Bên</h3>
                        <p>{{ $interpretation['layer_4_love_family'] ?? '' }}</p>
                    </div>
                    <div class="mayhen_tuvi_card">
                        <span class="mayhen_tuvi_badge">Tầng 5 • Giai Đoạn Cuộc Đời</span>
                        <h3>Đại Hạn, Tiểu Hạn &amp; Lưu Niên {{ $chart['viewed_year'] ?? '' }}</h3>
                        <p>{{ $interpretation['layer_5_life_stages'] ?? '' }}</p>
                    </div>
                </div>

                @if(! empty($palaceHighlights))
                    <div class="mayhen_tuvi_card" style="margin-bottom: 20px;">
                        <span class="mayhen_tuvi_badge">Tầng 2 • 6 Cung Trọng Tâm</span>
                        <h3>Điểm Nhấn Các Cung Quan Trọng Nhất</h3>
                        <div class="mayhen_tuvi_wiki_grid" style="margin-top: 12px;">
                            @foreach($palaceHighlights as $pCode => $pText)
                                <div class="mayhen_tuvi_alert_info">
                                    <strong>{{ $palacesMeta[$pCode]['name'] ?? $pCode }}</strong>
                                    <p style="margin: 6px 0 0; font-size: 13.5px;">{{ $pText }}</p>
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
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="mayhen_tuvi_card">
                        <span class="mayhen_tuvi_badge">Điểm Cần Lưu Tâm</span>
                        <h3>Điều Nên Quan Sát &amp; Giữ Nhịp</h3>
                        <ul class="mayhen_tuvi_list">
                            @foreach($interpretation['watch_outs'] ?? [] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                {{-- TẦNG 6: KẾ HOẠCH HÀNH ĐỘNG --}}
                <div class="mayhen_tuvi_card" style="margin-bottom: 22px;">
                    <span class="mayhen_tuvi_badge">Tầng 6 • Gợi Ý Ứng Dụng Thực Tế</span>
                    <h3>Lộ Trình Hành Động &amp; Câu Hỏi Chiêm Nghiệm</h3>
                    <div class="mayhen_tuvi_form_grid" style="margin-top: 12px;">
                        <div class="mayhen_tuvi_alert_info"><strong>Hôm nay:</strong> {{ $plan['today'] ?? '' }}</div>
                        <div class="mayhen_tuvi_alert_info"><strong>Trong tuần này:</strong> {{ $plan['this_week'] ?? '' }}</div>
                        <div class="mayhen_tuvi_alert_info"><strong>30 ngày tới:</strong> {{ $plan['next_30_days'] ?? '' }}</div>
                        <div class="mayhen_tuvi_alert_info"><strong>Dài hạn:</strong> {{ $plan['long_term'] ?? '' }}</div>
                    </div>
                    @if(! empty($interpretation['reflection_question']))
                        <p style="margin-top: 14px; font-style: italic; color: #85182A;">
                            <strong>Câu hỏi tự chiêm nghiệm:</strong> “{{ $interpretation['reflection_question'] }}”
                        </p>
                    @endif
                </div>

                {{-- TIMELINE ĐẠI HẠN 120 NĂM --}}
                @if(! empty($daiHanTimeline))
                    <div class="mayhen_tuvi_card">
                        <span class="mayhen_tuvi_badge">Hành Trình Cuộc Đời (12 Đại Hạn × 10 Năm)</span>
                        <h3>Dòng Thời Gian Đại Hạn</h3>
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
                    <p style="margin-bottom: 14px;">{{ $interpretation['core_message'] ?? '' }}</p>
                    <h4>3 Điểm Nên Phát Huy Ngay</h4>
                    <ul class="mayhen_tuvi_list">
                        @foreach($interpretation['strengths'] ?? [] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <h4 style="margin-top: 16px;">4 Việc Cụ Thể Có Thể Bắt Đầu</h4>
                    <ul class="mayhen_tuvi_list">
                        <li><strong>Hôm nay:</strong> {{ $plan['today'] ?? '' }}</li>
                        <li><strong>Tuần này:</strong> {{ $plan['this_week'] ?? '' }}</li>
                        <li><strong>30 ngày:</strong> {{ $plan['next_30_days'] ?? '' }}</li>
                        <li><strong>Dài hạn:</strong> {{ $plan['long_term'] ?? '' }}</li>
                    </ul>
                </div>
            </div>

            @if(! $isPartial)
                {{-- TAB 3: THIÊN BÀN 4x4 TƯƠNG TÁC & XUẤT PNG --}}
                <div class="mayhen_tuvi_view_panel" data-view-panel="board">
                    <div class="mayhen_tuvi_btn_row" style="justify-content: space-between; margin-bottom: 14px;">
                        <p style="margin: 0; font-size: 13.5px; color: #544A42;">
                            Chạm vào bất kỳ cung nào để làm nổi bật <strong>Tam Hợp (xanh ngọc)</strong> và <strong>Xung Chiếu (nét đứt vàng)</strong>.
                        </p>
                        <div class="mayhen_tuvi_btn_row">
                            <label style="font-size: 13px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;">
                                <input type="checkbox" data-tuvi-export-hide-pii checked> Ẩn tên &amp; ngày giờ sinh khi xuất ảnh
                            </label>
                            <button type="button" class="mayhen_tuvi_btn_primary mayhen_tuvi_btn_sm" data-tuvi-export-png>
                                ↓ Xuất Ảnh Lá Số PNG (HiDPI)
                            </button>
                        </div>
                    </div>

                    <div class="mayhen_tuvi_chart_board" role="grid" aria-label="Thiên Bàn Tử Vi 12 Cung">
                        @foreach($palacesByBranch as $p)
                            <button type="button"
                                    class="mayhen_tuvi_palace_cell mayhen_tuvi_branch_{{ $p['branch_index'] }}"
                                    data-branch-idx="{{ $p['branch_index'] }}">
                                <div class="mayhen_tuvi_cell_top">
                                    <span class="mayhen_tuvi_cell_name">
                                        {{ $p['short_name'] }}@if($p['is_than'])<span class="mayhen_tuvi_than_tag">THÂN</span>@endif
                                    </span>
                                    <span class="mayhen_tuvi_cell_branch">{{ $p['can_chi'] }}</span>
                                </div>

                                <div class="mayhen_tuvi_cell_majors">
                                    @forelse($p['major_stars'] as $ms)
                                        <div class="mayhen_tuvi_major_line">
                                            {{ $ms['name'] }} <small>({{ $ms['brightness'] }})</small>
                                        </div>
                                    @empty
                                        <div class="mayhen_tuvi_major_line" style="color: #7D7165; font-style: italic;">Vô Chính Diệu</div>
                                    @endforelse
                                </div>

                                <div class="mayhen_tuvi_cell_aux">
                                    @foreach($p['tu_hoa'] as $th)
                                        <span class="mayhen_tuvi_tuhoa_pill">{{ $th['name'] }}</span>
                                    @endforeach
                                    @foreach($p['aux_stars'] as $aux)
                                        <span class="{{ ($aux['polarity'] ?? '') === 'cat' ? 'mayhen_tuvi_aux_cat' : (($aux['polarity'] ?? '') === 'sat' ? 'mayhen_tuvi_aux_sat' : '') }}">
                                            {{ $aux['name'] }}
                                        </span>
                                    @endforeach
                                </div>

                                <div class="mayhen_tuvi_cell_bottom">
                                    <span>ĐH {{ $p['dai_han_start'] }}–{{ $p['dai_han_end'] }} • {{ $p['ring_thai_tue'] }}</span>
                                    <span>
                                        @if($p['has_tuan'])<span class="mayhen_tuvi_tuan_triet_tag">TUẦN</span>@endif
                                        @if($p['has_triet'])<span class="mayhen_tuvi_tuan_triet_tag">TRIỆT</span>@endif
                                        {{ $p['ring_trang_sinh'] }}
                                    </span>
                                </div>
                            </button>
                        @endforeach

                        {{-- Ô TRUNG TÂM 2x2 --}}
                        <div class="mayhen_tuvi_center_plaque">
                            <span class="mayhen_tuvi_badge" style="margin: 0 auto 6px;">MÂY HẸN · THIÊN MỆNH</span>
                            <h2>{{ $nickname }}</h2>
                            <div style="font-size: 13px; color: #5B5046;">{{ $chart['school_name'] ?? 'Nam Phái Việt Nam' }}</div>
                            <div class="mayhen_tuvi_center_meta">
                                <div><strong>Dương lịch:</strong> {{ $birth['solar']['label'] ?? '' }}</div>
                                <div><strong>Âm lịch:</strong> {{ $birth['lunar']['label'] ?? '' }}</div>
                                <div><strong>Bản Mệnh:</strong> {{ $summary['nap_am']['name'] ?? '' }} ({{ $summary['nap_am']['element'] ?? '' }})</div>
                                <div><strong>Ngũ Hành Cục:</strong> {{ $summary['cuc']['name'] ?? '' }}</div>
                                <div><strong>Âm Dương:</strong> {{ $chart['yin_yang_gender_label'] ?? '' }}</div>
                                <div><strong>Thân cư:</strong> {{ $summary['than_palace_name'] ?? '' }}</div>
                                <div><strong>Lưu Niên:</strong> {{ $summary['current_luu_nien']['year'] ?? '' }} ({{ $summary['current_luu_nien']['can_chi'] ?? '' }})</div>
                                <div><strong>Tuần / Triệt:</strong> {{ implode('–', $summary['tuan_branches'] ?? []) }} / {{ implode('–', $summary['triet_branches'] ?? []) }}</div>
                            </div>
                            <div style="font-size: 12px; color: #7C6E60;">
                                “Hiểu mình, hiểu người, vun đắp nhân duyên.”
                            </div>
                        </div>
                    </div>

                    <div class="mayhen_tuvi_card" data-tuvi-board-inspector aria-live="polite" style="margin-bottom: 20px;"></div>

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

                    <div class="mayhen_tuvi_card">
                        <span class="mayhen_tuvi_badge">Cách Cục Đã Xác Minh Bằng Rule Engine</span>
                        <h3>Danh Sách Cách Cục Hiện Diện Trên Lá Số</h3>
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
                        <span class="mayhen_tuvi_badge">Trợ Lý Tử Vi AI Khóa Chặt Lá Số (Tối đa 8 lượt)</span>
                        <h3>Hỏi Sâu Trên Lá Số Này</h3>
                        <p style="margin-bottom: 12px;">
                            Đặt câu hỏi cụ thể về sự nghiệp, giao tiếp tình cảm hoặc giai đoạn năm {{ $chart['viewed_year'] ?? '' }}. AI chỉ đối chiếu đúng dữ liệu lá số này và không đưa lời tiên đoán y khoa hay mê tín.
                        </p>
                        <form data-tuvi-followup-form>
                            <div class="mayhen_tuvi_field">
                                <label for="tuviFollowupPalace">Cung muốn hỏi sâu (tùy chọn)</label>
                                <select id="tuviFollowupPalace" name="palace_code" class="mayhen_tuvi_select">
                                    <option value="">Tổng thể lá số</option>
                                    @foreach($palacesMeta as $pCode => $pMeta)
                                        <option value="{{ $pCode }}">{{ $pMeta['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mayhen_tuvi_field">
                                <label for="tuviFollowupQuestion">Câu hỏi của bạn *</label>
                                <textarea id="tuviFollowupQuestion" name="question" rows="3" class="mayhen_tuvi_textarea" minlength="4" maxlength="320" required placeholder="Ví dụ: Với Cung Phu Thê này, mình nên rèn thói quen giao tiếp nào trước khi kết hôn?"></textarea>
                            </div>
                            <button type="submit" class="mayhen_tuvi_btn_primary">Hỏi Sâu Trên Lá Số Này</button>
                        </form>

                        <div data-tuvi-followup-list style="display: flex; flex-direction: column; gap: 10px; margin-top: 16px;">
                            @foreach($chartModel->followups as $fu)
                                <div class="mayhen_tuvi_card" style="padding: 12px 14px;">
                                    <div style="font-weight: 700; color: #85182A; margin-bottom: 4px;">Hỏi: {{ $fu->question }}</div>
                                    <p style="font-size: 13.5px;">{{ $fu->answer }}</p>
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

    {{-- JSON DATA ISLANDS --}}
    <script id="mayhenTuViChartJson" type="application/json">@json($chart, JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenTuViRuleJson" type="application/json">@json($ruleAnalysis, JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenTuViPalacesJson" type="application/json">@json($palacesMeta, JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenTuViEndpointsJson" type="application/json">@json($endpoints, JSON_UNESCAPED_UNICODE)</script>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/tuvi.js') }}"></script>
@endpush
