@extends('client.layouts.master')

@section('title', 'Lịch Gia Đình Âm Lịch — Ngày Giỗ, Sinh Nhật & Kỷ Niệm')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/calendar.css') }}?v={{ filemtime(public_path('assets/client/css/pages/calendar.css')) }}">
@endpush

@section('content')
<div class="mayhen_calendar_page">
    <section class="mayhen_calendar_hero">
        <div class="mayhen_calendar_container">
            <div class="mayhen_calendar_hero_top">
                <div>
                    <div class="mayhen_calendar_eyebrow">✦ KHÔNG GIAN RIÊNG TƯ GIA ĐÌNH</div>
                    <h1 class="mayhen_calendar_h1">Lịch Gia Đình — Ngày Giỗ, Sinh Nhật &amp; Kỷ Niệm</h1>
                    <p style="font-size: 14px; color: #57534E; margin: 6px 0 0;">
                        Các sự kiện Âm lịch tự động quy đổi chính xác sang Dương lịch từng năm theo quy ước gia đình bạn chọn (không cộng cố định 365 ngày).
                    </p>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <a href="{{ route('calendar.index') }}" class="mayhen_calendar_btn_outline">← Về Lịch Hôm Nay</a>
                    <a href="{{ route('calendar.events.export_ics') }}" class="mayhen_calendar_btn_primary">⬇ Xuất File Lịch (.ICS · 5 Năm)</a>
                </div>
            </div>
        </div>
    </section>

    <div class="mayhen_calendar_container">
        <section class="mayhen_calendar_section">
            <div class="mayhen_calendar_two_col">
                <!-- Danh sách sự kiện sắp tới -->
                <div class="mayhen_calendar_card">
                    <h2>Sự kiện gia đình sắp tới ({{ count($upcoming) }})</h2>
                    @if (empty($upcoming))
                        <p style="color: #57534E;">Bạn chưa lưu sự kiện gia đình nào. Hãy thêm ngày giỗ, sinh nhật âm lịch hoặc kỷ niệm ngày cưới ở khung bên phải.</p>
                    @else
                        @foreach ($upcoming as $row)
                            @php $ev = $row['event']; $nx = $row['next']; @endphp
                            <div style="padding: 14px 0; border-bottom: 1px solid rgba(41,37,36,0.1); display: flex; justify-content: space-between; gap: 12px; align-items: flex-start;">
                                <div>
                                    <div style="font-size: 11.5px; font-weight: 700; color: #85182A; text-transform: uppercase;">
                                        {{ $ev->calendar_type === 'lunar' ? 'Âm lịch' : 'Dương lịch' }} · {{ sprintf('%02d/%02d', $ev->event_day, $ev->event_month) }}
                                    </div>
                                    <strong style="font-size: 16px;">{{ $ev->title }}</strong>
                                    @if ($nx)
                                        <div style="font-size: 13.5px; color: #1E4D3B; margin-top: 3px;">
                                            Lần tới: <strong>{{ $nx['weekday'] }}, {{ $nx['solar_date'] }}</strong> (còn {{ $row['days_left'] }} ngày)
                                        </div>
                                        @if (! empty($nx['note']))
                                            <div style="font-size: 12px; color: #85182A;">{{ $nx['note'] }}</div>
                                        @endif
                                    @endif
                                    @if ($ev->notes)
                                        <div style="font-size: 12.5px; color: #57534E; margin-top: 4px;">{{ $ev->notes }}</div>
                                    @endif
                                </div>
                                <button type="button" class="mayhen_calendar_btn_outline" data-delete-event-url="{{ route('calendar.events.destroy', $ev->id) }}">Xóa</button>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Form thêm sự kiện mới -->
                <div class="mayhen_calendar_card">
                    <h2>Thêm sự kiện gia đình</h2>
                    <form id="calNewEventForm" action="{{ route('calendar.events.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 12px;">
                        <div class="mayhen_calendar_field">
                            <label>Tên sự kiện</label>
                            <input type="text" name="title" class="mayhen_calendar_input" placeholder="Ví dụ: Ngày giỗ Ông Nội / Kỷ niệm ngày cưới" required>
                        </div>
                        <div class="mayhen_calendar_form_row" style="margin: 0;">
                            <div class="mayhen_calendar_field">
                                <label>Loại sự kiện</label>
                                <select name="event_type" class="mayhen_calendar_select">
                                    <option value="death_anniversary">Ngày giỗ</option>
                                    <option value="birthday">Sinh nhật</option>
                                    <option value="wedding">Ngày cưới</option>
                                    <option value="anniversary">Kỷ niệm</option>
                                    <option value="family">Họp mặt gia đình</option>
                                </select>
                            </div>
                            <div class="mayhen_calendar_field">
                                <label>Loại lịch</label>
                                <select name="calendar_type" class="mayhen_calendar_select">
                                    <option value="lunar">Âm lịch (tự quy đổi hàng năm)</option>
                                    <option value="solar">Dương lịch</option>
                                </select>
                            </div>
                        </div>
                        <div class="mayhen_calendar_form_row" style="margin: 0;">
                            <div class="mayhen_calendar_field">
                                <label>Ngày</label>
                                <input type="number" name="event_day" min="1" max="31" value="15" class="mayhen_calendar_input" required>
                            </div>
                            <div class="mayhen_calendar_field">
                                <label>Tháng</label>
                                <input type="number" name="event_month" min="1" max="12" value="7" class="mayhen_calendar_input" required>
                            </div>
                        </div>
                        <div class="mayhen_calendar_field">
                            <label>Quy ước khi gặp tháng thiếu (29 ngày)</label>
                            <select name="short_month_rule" class="mayhen_calendar_select">
                                <option value="last_day_of_month">Chuyển về ngày cuối tháng (29 Âm)</option>
                                <option value="skip">Bỏ qua năm tháng thiếu</option>
                            </select>
                        </div>
                        <div class="mayhen_calendar_field">
                            <label>Quy ước khi gặp tháng nhuận</label>
                            <select name="leap_month_rule" class="mayhen_calendar_select">
                                <option value="regular_only">Theo tháng thường</option>
                                <option value="leap_if_exists">Theo tháng nhuận nếu có</option>
                                <option value="both">Nhắc cả hai tháng</option>
                            </select>
                        </div>
                        <div class="mayhen_calendar_field">
                            <label>Ghi chú (tuỳ chọn)</label>
                            <input type="text" name="notes" class="mayhen_calendar_input" placeholder="Ghi chú riêng tư của gia đình...">
                        </div>
                        <button type="submit" class="mayhen_calendar_btn_primary" style="justify-content: center;">+ Lưu Vào Lịch Gia Đình</button>
                        <div id="calNewEventStatus" style="font-size: 12.5px; color: #85182A;"></div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/calendar.js') }}"></script>
@endpush
