@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $comp = is_array($report->comparison_payload_json) ? $report->comparison_payload_json : [];
    $interp = is_array($report->interpretation_json) ? $report->interpretation_json : [];
    $pA = $comp['person_a'] ?? ['display_name' => 'Người 1', 'life_path_display' => '—', 'life_path_title' => ''];
    $pB = $comp['person_b'] ?? ['display_name' => 'Người 2', 'life_path_display' => '—', 'life_path_title' => ''];
    $pair = $comp['base_pair'] ?? ['dynamic' => 'Đồng Hành', 'harmony' => '', 'difference' => '', 'nurture' => ''];
    $savedAnswers = $report->couple_answers_json['questions'] ?? [];
    $promises = is_array($report->promise_30_days_json) ? $report->promise_30_days_json : [];
    $messages = is_array($report->couple_messages_json) ? $report->couple_messages_json : [];
@endphp

@section('title', 'Thần Số Học Tình Duyên: ' . ($pA['display_name'] ?? '') . ' & ' . ($pB['display_name'] ?? ''))
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/numerology.css') }}">
@endpush

@section('content')
<div class="mayhen_numerology_page">
    <section class="mayhen_numerology_section" style="padding: 34px 0 60px;">
        <div class="mayhen_numerology_container">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
                <a href="{{ route('numerology.love.index') }}" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm">&larr; Tạo Đối Chiếu Khác</a>
                <a href="{{ route('numerology.profiles') }}" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm">Hồ Sơ Đã Lưu</a>
            </div>

            {{-- PHẦN ĐẦU CUỐN SÁCH CÂU CHUYỆN HAI NGƯỜI --}}
            <div class="mayhen_numerology_love_banner" style="margin-bottom: 28px;">
                <span class="mayhen_numerology_eyebrow">Báo Cáo Riêng Tư • {{ $stageLabel }} • Cặp Số Nền {{ $comp['pair_key'] ?? '' }}</span>
                <h1 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: clamp(24px, 3vw, 34px); margin: 4px 0 10px;">
                    {{ $interp['headline'] ?? ($pA['display_name'] . ' & ' . $pB['display_name']) }}
                </h1>
                <p style="max-width: 720px; margin: 0 auto; font-size: 14px; color: #5C5248;">{{ $comp['disclaimer'] ?? '' }}</p>

                <div class="mayhen_numerology_love_duet">
                    <div class="mayhen_numerology_love_emblem">
                        <div style="font-size: 11px; color: #1E4D3B; font-weight: 700;">{{ $pA['display_name'] }}</div>
                        <div style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 52px; color: #85182A; line-height: 1.05;">{{ $pA['life_path_display'] }}</div>
                        <div style="font-size: 12px; color: #5C5248;">{{ $pA['life_path_title'] ?? '' }}</div>
                    </div>
                    <div class="mayhen_numerology_love_bridge" aria-hidden="true"></div>
                    <div class="mayhen_numerology_love_emblem">
                        <div style="font-size: 11px; color: #1E4D3B; font-weight: 700;">{{ $pB['display_name'] }}</div>
                        <div style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; font-size: 52px; color: #1E4D3B; line-height: 1.05;">{{ $pB['life_path_display'] }}</div>
                        <div style="font-size: 12px; color: #5C5248;">{{ $pB['life_path_title'] ?? '' }}</div>
                    </div>
                </div>

                @if(! empty($comp['master_notes']))
                    <div class="mayhen_numerology_note_box" style="text-align: left; max-width: 760px; margin: 12px auto 0;">
                        @foreach($comp['master_notes'] as $mn)
                            <div>{{ $mn }}</div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- BẢNG SO SÁNH ĐA TẦNG HAI HỒ SƠ --}}
            <div class="mayhen_numerology_editorial_block">
                <span class="mayhen_numerology_chapter_tag">Đối Chiếu Đa Tầng Hai Hồ Sơ Độc Lập</span>
                <h2>Tổng Quan &amp; So Sánh Chỉ Số</h2>
                <p>{{ $interp['overview'] ?? '' }}</p>
                <p style="font-size: 13px; color: #6B5F52;"><em>{{ $comp['limitation_note'] ?? '' }}</em></p>

                <table class="mayhen_numerology_table" style="margin-top: 14px;">
                    <thead>
                        <tr><th>Chỉ số</th><th>{{ $pA['display_name'] }}</th><th>{{ $pB['display_name'] }}</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>Đường Đời (Life Path)</strong></td><td>{{ $pA['life_path_display'] }}</td><td>{{ $pB['life_path_display'] }}</td></tr>
                        <tr><td><strong>Sứ Mệnh (Expression)</strong></td><td>{{ $pA['expression_display'] ?? 'Chưa nhập họ tên' }}</td><td>{{ $pB['expression_display'] ?? 'Chưa nhập họ tên' }}</td></tr>
                        <tr><td><strong>Linh Hồn (Soul Urge)</strong></td><td>{{ $pA['soul_urge_display'] ?? 'Chưa nhập họ tên' }}</td><td>{{ $pB['soul_urge_display'] ?? 'Chưa nhập họ tên' }}</td></tr>
                        <tr><td><strong>Nhân Cách (Personality)</strong></td><td>{{ $pA['personality_display'] ?? 'Chưa nhập họ tên' }}</td><td>{{ $pB['personality_display'] ?? 'Chưa nhập họ tên' }}</td></tr>
                        <tr><td><strong>Ngày Sinh / Năm Cá Nhân</strong></td><td>Ngày {{ $pA['birthday_raw'] ?? '—' }} • Năm CN {{ $pA['personal_year'] ?? '—' }}</td><td>Ngày {{ $pB['birthday_raw'] ?? '—' }} • Năm CN {{ $pB['personal_year'] ?? '—' }}</td></tr>
                    </tbody>
                </table>
            </div>

            {{-- 3 ĐIỀU VUN ĐẮP & 3 ĐIỀU TRAO ĐỔI --}}
            <div class="mayhen_numerology_editorial_block">
                <div class="mayhen_numerology_grid_2">
                    <div class="mayhen_numerology_note_box" style="background: #FCFBF9; border-left-color: #1E4D3B;">
                        <strong>3 Điều Nên Vun Đắp</strong>
                        <ul style="margin: 8px 0 0; padding-left: 18px;">
                            @foreach($interp['things_to_nurture'] ?? [] as $item)
                                <li style="margin-bottom: 6px;">{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="mayhen_numerology_note_box" style="background: #FCFBF9; border-left-color: #85182A;">
                        <strong>3 Điều Nên Trao Đổi Sớm</strong>
                        <ul style="margin: 8px 0 0; padding-left: 18px;">
                            @foreach($interp['things_to_discuss'] ?? [] as $item)
                                <li style="margin-bottom: 6px;">{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            {{-- 10 CHỦ ĐỀ TÌNH DUYÊN --}}
            <div class="mayhen_numerology_editorial_block">
                <span class="mayhen_numerology_chapter_tag">Khung 10 Chủ Đề Ứng Dụng Mây Hẹn</span>
                <h2>Mười Khía Cạnh Đồng Hành Của Hai Bạn</h2>
                <div class="mayhen_numerology_grid_2">
                    @foreach($interp['topics'] ?? [] as $tKey => $topic)
                        <div class="mayhen_numerology_note_box" style="background: #FCFBF9;">
                            <strong style="color: #85182A;">{{ $topic['label'] ?? $tKey }}</strong>
                            <p style="font-size: 13.5px; margin: 6px 0 0;">{{ $topic['insight'] ?? '' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- KHÔNG GIAN TƯƠNG TÁC "CÂU CHUYỆN HAI CHÚNG TA" & LỜI HẸN 30 NGÀY --}}
            <div class="mayhen_numerology_editorial_block" style="border-bottom: none;">
                <span class="mayhen_numerology_chapter_tag">Không Gian Tương Tác Cặp Đôi (Ưu Tiên Trải Nghiệm Thực Tế)</span>
                <h2>Câu Chuyện Hai Chúng Ta &amp; Lời Hẹn 30 Ngày</h2>
                <p>Thông tin thực tế hai bạn cùng trao đổi tại đây luôn có giá trị cao hơn mọi suy đoán từ con số.</p>

                <form id="mayhenNumCoupleStoryForm" action="{{ route('numerology.love.story', $report->slug) }}" method="POST" class="mayhen_numerology_sheet" style="padding: 24px;">
                    @foreach($coupleQuestions as $qi => $qText)
                        <div class="mayhen_numerology_field">
                            <label for="coupleQ{{ $qi }}">{{ $qi + 1 }}. {{ $qText }}</label>
                            <input id="coupleQ{{ $qi }}" name="q_{{ $qi }}" type="text" class="mayhen_numerology_input" maxlength="320" value="{{ $savedAnswers['q_' . $qi] ?? '' }}" placeholder="Cùng ghi câu trả lời thực tế của hai bạn...">
                        </div>
                    @endforeach

                    <div class="mayhen_numerology_grid_2">
                        <div class="mayhen_numerology_field">
                            <label for="promiseA">Lời hẹn 30 ngày của {{ $pA['display_name'] }}</label>
                            <input id="promiseA" name="promise_a" type="text" class="mayhen_numerology_input" maxlength="320" value="{{ $promises['person_a'] ?? '' }}" placeholder="1 hành động cụ thể mình muốn làm cho đối phương...">
                        </div>
                        <div class="mayhen_numerology_field">
                            <label for="promiseB">Lời hẹn 30 ngày của {{ $pB['display_name'] }}</label>
                            <input id="promiseB" name="promise_b" type="text" class="mayhen_numerology_input" maxlength="320" value="{{ $promises['person_b'] ?? '' }}" placeholder="1 hành động cụ thể mình muốn làm cho đối phương...">
                        </div>
                    </div>

                    <div class="mayhen_numerology_field">
                        <label for="coupleMessageNote">Viết một lời nhắn gửi cho nhau (tùy chọn)</label>
                        <textarea id="coupleMessageNote" name="message_note" rows="2" class="mayhen_numerology_textarea" maxlength="400"></textarea>
                    </div>

                    <div id="mayhenNumStoryStatus" class="mayhen_numerology_note_box" style="display: none; border-left-color: #1E4D3B; margin-bottom: 12px;"></div>

                    <button type="submit" class="mayhen_numerology_btn_primary">Lưu Câu Chuyện &amp; Lời Hẹn 30 Ngày</button>
                </form>

                @if(! empty($messages))
                    <div style="margin-top: 16px;">
                        <h4 style="font-family: 'MayhenV3EditorialSerif', Georgia, serif; margin: 0 0 8px;">Lời Nhắn Hai Bạn Đã Lưu</h4>
                        @foreach($messages as $msg)
                            <div class="mayhen_numerology_note_box" style="margin-bottom: 8px;">
                                <strong>{{ $msg['created_at'] ?? '' }}:</strong> {{ $msg['text'] ?? '' }}
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mayhen_numerology_note_box" style="margin-top: 22px; background: #FCFBF9;">
                    <strong>Hệ sinh thái chuẩn bị ngày chung đôi — Mây Hẹn:</strong>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px;">
                        <a href="{{ route('templates.index') }}" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm">Khám Phá Thiệp Cưới Online</a>
                        <a href="{{ route('tuvi.synastry.index') }}" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm">Nhân Duyên Song Mệnh (Tử Vi)</a>
                        <a href="{{ route('blog.index') }}" class="mayhen_numerology_btn_outline mayhen_numerology_btn_sm">Cẩm Nang Chuẩn Bị Cưới</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/numerology.js') }}"></script>
@endpush
