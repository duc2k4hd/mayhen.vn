@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $canonicalUrl = route('numerology.love.index');
@endphp

@section('title', 'Thần Số Học Tình Duyên Miễn Phí — 10 Chủ Đề Cặp Đôi & Hôn Nhân')
@section('meta_description', 'Thần Số Học Tình Duyên của Mây Hẹn tính độc lập hai hồ sơ, đối chiếu ma trận 45 cặp số nền và phân tích 10 chủ đề thực tế của tình yêu, hôn nhân, gia đình hai bên — tuyệt đối không chấm điểm % hợp nhau.')
@section('canonical', $canonicalUrl)

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/numerology.css') }}">
@endpush

@section('content')
<div class="mayhen_numerology_page">
    <section class="mayhen_numerology_hero">
        <div class="mayhen_numerology_container">
            <span class="mayhen_numerology_eyebrow">Mây Hẹn · Thần Số Học Tình Duyên &amp; Câu Chuyện Hai Chúng Ta</span>
            <h1 class="mayhen_numerology_h1">
                Thần Số Học <em>Tình Duyên</em> — Thấu Hiểu Nhau Qua 10 Chủ Đề Thực Tế
            </h1>
            <p class="mayhen_numerology_lead">
                Tính độc lập từng hồ sơ (Đường đời, Sứ mệnh, Linh hồn, Nhân cách, Ngày sinh, Năm cá nhân) rồi đối chiếu đa tầng — tuyệt đối không chấm % hợp tuổi hay phán định chia ly.
            </p>

            <div class="mayhen_numerology_sheet" style="max-width: 960px;">
                <div class="mayhen_numerology_sheet_body">
                    @if(! $canUseAi)
                        <div class="mayhen_numerology_note_box">
                            Tính năng tạo &amp; lưu Báo Cáo Thần Số Học Tình Duyên riêng tư hoàn toàn miễn phí cho thành viên Mây Hẹn (tự động cấp gói Thành Viên Premium 0đ).
                            <div style="margin-top: 12px;">
                                <a href="{{ route('login', ['redirect' => route('numerology.love.index')]) }}" class="mayhen_numerology_btn_primary">Đăng nhập miễn phí (0đ)</a>
                            </div>
                        </div>
                    @else
                        <form id="mayhenNumLoveForm" action="{{ route('numerology.love.store') }}" method="POST">
                            <div class="mayhen_numerology_grid_2" style="gap: 24px;">
                                <div>
                                    <span class="mayhen_numerology_chapter_tag">Người thứ nhất</span>
                                    <div class="mayhen_numerology_field">
                                        <label for="loveAName">Họ tên khai sinh (tùy chọn)</label>
                                        <input id="loveAName" name="person_a_name" type="text" class="mayhen_numerology_input" maxlength="160" placeholder="Ví dụ: Nguyễn Minh Quân">
                                    </div>
                                    <div class="mayhen_numerology_grid_3">
                                        <div class="mayhen_numerology_field"><label for="loveADay">Ngày *</label><input id="loveADay" name="person_a_day" type="number" class="mayhen_numerology_input" min="1" max="31" required placeholder="15"></div>
                                        <div class="mayhen_numerology_field"><label for="loveAMonth">Tháng *</label><input id="loveAMonth" name="person_a_month" type="number" class="mayhen_numerology_input" min="1" max="12" required placeholder="5"></div>
                                        <div class="mayhen_numerology_field"><label for="loveAYear">Năm *</label><input id="loveAYear" name="person_a_year" type="number" class="mayhen_numerology_input" min="1900" max="2100" required placeholder="1994"></div>
                                    </div>
                                </div>

                                <div>
                                    <span class="mayhen_numerology_chapter_tag">Người thứ hai</span>
                                    <div class="mayhen_numerology_field">
                                        <label for="loveBName">Họ tên khai sinh (tùy chọn)</label>
                                        <input id="loveBName" name="person_b_name" type="text" class="mayhen_numerology_input" maxlength="160" placeholder="Ví dụ: Trần Lan Phương">
                                    </div>
                                    <div class="mayhen_numerology_grid_3">
                                        <div class="mayhen_numerology_field"><label for="loveBDay">Ngày *</label><input id="loveBDay" name="person_b_day" type="number" class="mayhen_numerology_input" min="1" max="31" required placeholder="22"></div>
                                        <div class="mayhen_numerology_field"><label for="loveBMonth">Tháng *</label><input id="loveBMonth" name="person_b_month" type="number" class="mayhen_numerology_input" min="1" max="12" required placeholder="8"></div>
                                        <div class="mayhen_numerology_field"><label for="loveBYear">Năm *</label><input id="loveBYear" name="person_b_year" type="number" class="mayhen_numerology_input" min="1900" max="2100" required placeholder="1996"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="mayhen_numerology_grid_2">
                                <div class="mayhen_numerology_field">
                                    <label for="loveStage">Giai đoạn hiện tại của hai bạn</label>
                                    <select id="loveStage" name="relationship_stage" class="mayhen_numerology_select">
                                        @foreach($relationshipStages as $sKey => $sLabel)
                                            <option value="{{ $sKey }}" @selected($sKey === 'dang_yeu')>{{ $sLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mayhen_numerology_field">
                                    <label for="loveCoupleNote">Bối cảnh thực tế của hai bạn (tùy chọn)</label>
                                    <input id="loveCoupleNote" name="couple_note" type="text" class="mayhen_numerology_input" maxlength="500" placeholder="Ví dụ: Đang chuẩn bị lễ cưới và muốn thống nhất kế hoạch tài chính chung...">
                                </div>
                            </div>

                            <div class="mayhen_numerology_field">
                                <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; font-weight: 600;">
                                    <input type="checkbox" name="partner_consent_confirmed" value="1" required>
                                    <span>Tôi xác nhận đã có sự đồng thuận khi nhập thông tin của người đồng hành và hiểu rằng kết quả là gợi ý đối thoại, không phải điểm số hợp tuổi hay phán định tương lai.</span>
                                </label>
                            </div>

                            <div id="mayhenNumLoveErr" class="mayhen_numerology_alert_error" role="alert"></div>

                            <div class="mayhen_numerology_actions">
                                <a href="{{ route('numerology.index') }}" class="mayhen_numerology_btn_outline">&larr; Về trang Thần Số Học Cá Nhân</a>
                                <button type="submit" class="mayhen_numerology_btn_crimson">Phân Tích Thần Số Học Tình Duyên</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="mayhen_numerology_section mayhen_numerology_section--paper">
        <div class="mayhen_numerology_container">
            <div class="mayhen_numerology_section_head">
                <div>
                    <span class="mayhen_numerology_eyebrow">Khung Ứng Dụng Hiện Đại Mây Hẹn</span>
                    <h2>10 Khía Cạnh Phân Tích Tình Duyên</h2>
                </div>
            </div>
            <div class="mayhen_numerology_grid_2">
                @foreach($loveTopics as $i => $topic)
                    <div class="mayhen_numerology_note_box" style="background: #FAF5EB;">
                        <strong style="color: #85182A;">{{ $i + 1 }}. {{ $topic['label'] }}</strong><br>
                        <span>{{ $topic['focus'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/numerology.js') }}"></script>
@endpush
