@extends('client.layouts.master')

@section('title', 'Bảng Điều Khiển Ngày Cưới Realtime | ' . $wedding->groom_name . ' & ' . $wedding->bride_name . ' | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/live-coordination.css') }}">
@endpush

@section('content')
<div class="mayhen_live_app" id="mayhen_live_app" data-invitation-id="{{ $invitation->id }}" data-stats-url="{{ route('coordination.stats', $invitation->id) }}">
    <div class="mayhen_live_container">
        {{-- Top Bar & Header --}}
        <header class="mayhen_live_header">
            <div class="mayhen_live_brand">
                <span class="mayhen_live_pulse"></span>
                <div>
                    <h1 class="mayhen_live_title">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</h1>
                    <p class="mayhen_live_subtitle">Trung Tâm Điều Phối & Sĩ Số Ngày Cưới Realtime</p>
                </div>
            </div>

            <div class="mayhen_live_top_actions">
                <div class="mayhen_live_pin_badge" title="Mã PIN cho ban lễ tân mở máy quét trên điện thoại">
                    <span class="pin_label">PIN Tiếp Tân:</span>
                    <strong class="pin_value">{{ $checkinPin }}</strong>
                </div>
                <a href="{{ route('checkin.portal', $invitation->slug) }}" target="_blank" class="mayhen_live_btn mayhen_live_btn--primary">
                    📷 Mở Cổng Tiếp Tân Quét QR
                </a>
                <a href="{{ route('guests.index', $invitation->id) }}" class="mayhen_live_btn mayhen_live_btn--outline">
                    ← Danh Sách Khách
                </a>
            </div>
        </header>

        {{-- 4 Big Metric Cards --}}
        <div class="mayhen_live_metrics_grid">
            <div class="mayhen_live_metric_card">
                <span class="metric_label">TỔNG KHÁCH MỜI DỰ KIẾN</span>
                <div class="metric_value" id="val_total_expected">{{ $stats['total_expected_attendees'] }}</div>
                <span class="metric_hint">Từ {{ $stats['total_invited_guests'] }} thiệp cưới gửi đi</span>
            </div>

            <div class="mayhen_live_metric_card">
                <span class="metric_label">XÁC NHẬN THAM DỰ (RSVP)</span>
                <div class="metric_value" id="val_rsvp_count">{{ $stats['rsvp_total_attendees'] }}</div>
                <span class="metric_hint">{{ $stats['rsvp_attending_count'] }} lượt phản hồi Sẽ tham gia</span>
            </div>

            <div class="mayhen_live_metric_card mayhen_live_metric_card--highlight">
                <span class="metric_label">ĐÃ CÓ MẶT TẠI SẢNH TIỆC</span>
                <div class="metric_value_row">
                    <span class="metric_value" id="val_checked_in">{{ $stats['checked_in_count'] }}</span>
                    <span class="metric_rate_badge" id="val_arrival_rate">{{ $stats['arrival_rate'] }}%</span>
                </div>
                <div class="metric_progress_track">
                    <div class="metric_progress_fill" id="val_arrival_progress" style="width: {{ $stats['arrival_rate'] }}%;"></div>
                </div>
            </div>

            <div class="mayhen_live_metric_card">
                <span class="metric_label">CHƯA CÓ MẶT (CHỜ ĐÓN)</span>
                <div class="metric_value" id="val_pending_count">{{ $stats['pending_attendees'] }}</div>
                <span class="metric_hint">Khách đang trên đường tới sảnh</span>
            </div>
        </div>

        {{-- Main 2-Column Split: Seating Waves & Live Activity Feed --}}
        <div class="mayhen_live_layout">
            {{-- Column Left: Table Seating Waves --}}
            <section class="mayhen_live_col_tables">
                <div class="mayhen_live_section_header">
                    <div>
                        <h2 class="mayhen_live_sec_title">Sóng Lấp Đầy Bàn Tiệc (Seating Wave)</h2>
                        <p class="mayhen_live_sec_desc">Theo dõi tiến độ lấp đầy từng bàn tiệc theo thời gian thực</p>
                    </div>
                    <span class="mayhen_live_table_count" id="table_summary_count">
                        {{ count($tableWaves) }} Bàn Tiệc
                    </span>
                </div>

                <div class="mayhen_live_tables_grid" id="tables_grid">
                    @forelse($tableWaves as $wave)
                        <div class="mayhen_live_table_card {{ $wave['is_unassigned'] ? 'is-unassigned' : '' }}" id="table_card_{{ md5($wave['table_name']) }}">
                            <div class="table_card_header">
                                <div class="table_card_title_wrap">
                                    <h3 class="table_card_name">{{ $wave['table_name'] }}</h3>
                                    @if($wave['is_unassigned'])
                                        <span class="table_unassigned_badge">Cần xếp bàn</span>
                                    @endif
                                </div>
                                <div class="table_card_headcount">
                                    <span class="table_current_count">{{ $wave['checked_in_count'] }}</span>
                                    <span class="table_total_count">/ {{ $wave['total_expected'] }} ghế</span>
                                </div>
                            </div>

                            <div class="table_progress_track">
                                <div class="table_progress_fill {{ $wave['fill_rate'] >= 100 ? 'is-full' : '' }}" style="width: {{ $wave['fill_rate'] }}%;"></div>
                            </div>

                            <div class="table_card_guests">
                                @foreach($wave['guests'] as $guest)
                                    <span class="table_guest_pill {{ $guest['is_checked_in'] ? 'is-present' : 'is-absent' }}" title="{{ $guest['name'] }} ({{ $guest['expected'] }} người) - {{ $guest['is_checked_in'] ? 'Đã vào bàn lúc ' . $guest['checked_in_time'] : 'Chưa tới' }}">
                                        {{ $guest['is_checked_in'] ? '✓' : '○' }} {{ $guest['name'] }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="mayhen_live_empty_tables">
                            Chưa có dữ liệu bàn tiệc. Hãy phân chia bàn tiệc trong danh sách khách mời để xem sơ đồ trực quan!
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Column Right: Live Check-in Activity Feed --}}
            <aside class="mayhen_live_col_feed">
                <div class="mayhen_live_section_header">
                    <div>
                        <h2 class="mayhen_live_sec_title">Dòng Sự Kiện Đón Khách</h2>
                        <p class="mayhen_live_sec_desc">Lượt khách vừa quét mã QR tại bàn tiếp tân</p>
                    </div>
                </div>

                <div class="mayhen_live_feed_box" id="feed_container">
                    <div class="mayhen_live_feed_list" id="feed_list">
                        @forelse($recentActivities as $act)
                            <div class="mayhen_live_feed_item">
                                <span class="feed_item_time">{{ $act['time'] }}</span>
                                <div class="feed_item_body">
                                    <div class="feed_item_guest">
                                        {{ $act['salutation'] ? $act['salutation'] . ' ' : '' }}<strong>{{ $act['name'] }}</strong>
                                        <span class="feed_item_attendees">({{ $act['attendees'] }} khách)</span>
                                    </div>
                                    <div class="feed_item_table">
                                        Đã vào bàn: <strong>{{ $act['table_name'] }}</strong>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="mayhen_live_feed_empty" id="feed_empty">
                                Chưa có lượt check-in nào. Khi khách tới quét mã, dữ liệu sẽ tự động xuất hiện tại đây.
                            </div>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/live-coordination.js') }}"></script>
@endpush
