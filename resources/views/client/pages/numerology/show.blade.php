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
                    <div style="font-size: 13px; color: #1E4D3B; font-weight: 700;">{{ $enriched['indicator_meanings']['life_path']['title'] ?? '' }}</div>
                </div>
                <div>
                    <span class="mayhen_numerology_eyebrow">{{ $calc['method_name'] ?? 'Pythagorean Hans Decoz' }} (v{{ $calc['method_version'] ?? '1.0.0' }})</span>
                    <h1 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: clamp(24px, 3vw, 34px); margin: 0 0 8px;">
                        {{ $displayName }} — {{ $interpretation['headline'] ?? 'Hồ Sơ Dấu Ấn Con Số' }}
                    </h1>
                    <p style="font-size: 13.5px; color: #5C5248; margin: 0 0 12px;">
                        Ngày sinh dương lịch: <strong>{{ $calc['birth']['label'] ?? '' }}</strong> • {{ $calc['completeness_label'] ?? '' }} • Trọng tâm: <strong>{{ $focusTopicLabel }}</strong>
                    </p>
                    <p style="font-size: 15.5px; line-height: 1.75; margin: 0 0 14px;">{{ $interpretation['summary'] ?? '' }}</p>
                    <div class="mayhen_numerology_note_box">
                        {{ $core['life_path_comparison']['explanation'] ?? '' }}
                    </div>
                </div>
            </article>

            {{-- CHƯƠNG 2 — BỘ SỐ CÁ NHÂN (THẺ XÒE QUẠT + CÁCH TÍNH CỦA TÔI) --}}
            <section id="chuong-2" class="mayhen_numerology_editorial_block">
                <span class="mayhen_numerology_chapter_tag">Chương 2 · Bộ Chỉ Số Cá Nhân &amp; Minh Bạch Phép Tính</span>
                <h2>Bộ Thẻ Con Số Của Riêng Bạn</h2>
                <p>Chạm vào từng thẻ dưới đây để lật xem ý nghĩa đúng theo vị trí chỉ số (cùng một con số ở Đường Đời, Sứ Mệnh, Linh Hồn và Nhân Cách mang chiều sâu khác nhau).</p>

                <div class="mayhen_numerology_fan_stage" data-num-fan-stage>
                    <div class="mayhen_numerology_fan_hand" data-num-fan-hand></div>
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
                        <div class="mayhen_numerology_note_box"><strong>Thái Độ / Cân Bằng:</strong> {{ $ext['attitude']['display'] ?? '—' }} / {{ $ext['balance']['display'] ?? '—' }}</div>
                        <div class="mayhen_numerology_note_box"><strong>Đam Mê Tiềm Ẩn:</strong> {{ implode(', ', $ext['hidden_passion']['numbers'] ?? []) ?: '—' }} • <strong>Tiềm Thức:</strong> {{ $ext['subconscious_self']['value'] ?? '—' }}</div>
                        <div class="mayhen_numerology_note_box"><strong>Bài Học (Số thiếu tên):</strong> {{ implode(', ', $ext['karmic_lessons']['missing_numbers'] ?? []) ?: 'Đủ 1–9' }} • <strong>Cầu Nối:</strong> {{ $ext['bridges']['life_path_expression'] ?? '—' }}</div>
                    </div>
                @endif

                <details class="mayhen_numerology_note_box" style="background: #FCFBF9;">
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
            </section>

            {{-- CHƯƠNG 3 — BIỂU ĐỒ NGÀY SINH 3×3 (DAVID A. PHILLIPS) --}}
            <section id="chuong-3" class="mayhen_numerology_editorial_block">
                <span class="mayhen_numerology_chapter_tag">Chương 3 · Lớp Phân Tích Độc Lập Theo David A. Phillips</span>
                <h2>Biểu Đồ Ngày Sinh 3×3 &amp; 8 Đường Mũi Tên</h2>
                <div data-num-birthchart></div>
            </section>

            {{-- CHƯƠNG 4 — HIỂU BẢN THÂN & TIỀM NĂNG --}}
            <section id="chuong-4" class="mayhen_numerology_editorial_block">
                <span class="mayhen_numerology_chapter_tag">Chương 4 · Luận Giải Tầng 1, 2 &amp; 3 ({{ $reading->ai_powered ? 'AI Cá Nhân Hóa' : 'Kho Tri Thức Kiểm Định' }})</span>
                <h2>Hiểu Bản Thân, Tương Tác Chỉ Số &amp; Tiềm Năng</h2>
                <p>{{ $interpretation['layer_1_signature'] ?? '' }}</p>
                <p>{{ $interpretation['layer_2_self_understanding'] ?? '' }}</p>
                <p>{{ $interpretation['layer_3_potential'] ?? '' }}</p>
            </section>

            {{-- CHƯƠNG 5 — TÌNH YÊU, HÔN NHÂN & GIA ĐÌNH --}}
            <section id="chuong-5" class="mayhen_numerology_editorial_block">
                <span class="mayhen_numerology_chapter_tag">Chương 5 · Luận Giải Tầng 4</span>
                <h2>Tình Yêu, Giao Tiếp, Hôn Nhân &amp; Gia Đình</h2>
                <p>{{ $interpretation['layer_4_love_family'] ?? '' }}</p>
                <div class="mayhen_numerology_note_box" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <span>Muốn đối chiếu sâu hơn nhịp giao tiếp và hôn nhân cùng người thương (10 chủ đề, không chấm % hợp tuổi)?</span>
                    <a href="{{ route('numerology.love.index') }}" class="mayhen_numerology_btn_crimson mayhen_numerology_btn_sm">Mở Thần Số Học Tình Duyên &rarr;</a>
                </div>
            </section>

            {{-- CHƯƠNG 6 — HÀNH TRÌNH CUỘC ĐỜI (KIM TỰ THÁP 4 ĐỈNH CAO & 4 THỬ THÁCH) --}}
            <section id="chuong-6" class="mayhen_numerology_editorial_block">
                <span class="mayhen_numerology_chapter_tag">Chương 6 · Luận Giải Tầng 5</span>
                <h2>Bốn Đỉnh Cao (Kim Tự Tháp) &amp; Bốn Thử Thách</h2>
                <p>{{ $interpretation['layer_5_life_journey'] ?? '' }}</p>

                <div class="mayhen_numerology_grid_2" style="margin-top: 16px;">
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
                <span class="mayhen_numerology_chapter_tag">Chương 7 · Chu Kỳ Thời Gian (Dương Lịch 01/01–31/12)</span>
                <h2>Năm Cá Nhân {{ $cycles['personal_year']['year'] ?? '' }}: Số {{ $cycles['personal_year']['value'] ?? '' }} — {{ $enriched['personal_year_meaning']['theme'] ?? '' }}</h2>
                <p>{{ $enriched['personal_year_meaning']['summary'] ?? '' }}</p>
                <div class="mayhen_numerology_grid_3" style="margin-bottom: 14px;">
                    <div class="mayhen_numerology_note_box"><strong>Năm cá nhân {{ $cycles['personal_year']['year'] ?? '' }}:</strong> Số {{ $cycles['personal_year']['value'] ?? '' }} ({{ $cycles['personal_year']['formula'] ?? '' }})</div>
                    <div class="mayhen_numerology_note_box"><strong>Tháng cá nhân {{ $cycles['personal_month']['month'] ?? '' }}:</strong> Số {{ $cycles['personal_month']['value'] ?? '' }} — {{ $enriched['personal_month_meaning'] ?? '' }}</div>
                    <div class="mayhen_numerology_note_box"><strong>Ngày cá nhân {{ $cycles['personal_day']['day'] ?? '' }}:</strong> Số {{ $cycles['personal_day']['value'] ?? '' }} — {{ $enriched['personal_day_meaning'] ?? '' }}</div>
                </div>

                <h4 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 12px 0 6px;">Dòng Thời Gian Chu Kỳ 9 Năm</h4>
                <div class="mayhen_numerology_cycle_strip">
                    @foreach($cycles['nine_year_timeline'] ?? [] as $yr)
                        <div class="mayhen_numerology_cycle_cell {{ ! empty($yr['is_current']) ? 'is-current' : '' }}">
                            <div style="font-size: 11px; opacity: 0.8;">{{ $yr['year'] }}</div>
                            <div style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 22px; font-weight: 700;">{{ $yr['value'] }}</div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- CHƯƠNG 8 — KẾ HOẠCH PHÁT TRIỂN CÁ NHÂN --}}
            <section id="chuong-8" class="mayhen_numerology_editorial_block">
                <span class="mayhen_numerology_chapter_tag">Chương 8 · Luận Giải Tầng 6</span>
                <h2>Kế Hoạch Phát Triển Thực Tế &amp; Điểm Lưu Tâm</h2>
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
                        <h3 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 8px;">Hỏi Sâu Trên Hồ Sơ Này (Tối đa 8 lượt)</h3>
                        <form data-num-followup-form>
                            <div class="mayhen_numerology_field">
                                <label for="numFuQuestion">Đặt câu hỏi cụ thể về các chỉ số của bạn</label>
                                <textarea id="numFuQuestion" name="question" rows="3" class="mayhen_numerology_textarea" minlength="4" maxlength="320" required placeholder="Ví dụ: Vì sao chỉ số Sứ mệnh của mình khác Đường đời và nên hài hòa hai số này thế nào?"></textarea>
                            </div>
                            <button type="submit" class="mayhen_numerology_btn_primary">Gửi Câu Hỏi Trên Hồ Sơ Này</button>
                        </form>
                        <div data-num-followup-list style="margin-top: 14px;">
                            @foreach($reading->followups as $fu)
                                <div class="mayhen_numerology_note_box" style="margin-bottom: 10px;">
                                    <div style="font-weight: 700; color: #85182A; margin-bottom: 4px;">Hỏi: {{ $fu->question }}</div>
                                    <div>{{ $fu->answer }}</div>
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
                            @if(! $reading->ai_powered)
                                <button type="button" class="mayhen_numerology_btn_crimson mayhen_numerology_btn_sm" data-num-reinterpret-btn>Chạy Lại AI 6 Tầng</button>
                            @endif
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

    <script id="mayhenNumCalcJson" type="application/json">@json($calc, JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenNumEnrichedJson" type="application/json">@json($enriched, JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenNumInterpJson" type="application/json">@json($interpretation, JSON_UNESCAPED_UNICODE)</script>
    <script id="mayhenNumEndpointsJson" type="application/json">@json($endpoints, JSON_UNESCAPED_UNICODE)</script>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/numerology.js') }}"></script>
@endpush
