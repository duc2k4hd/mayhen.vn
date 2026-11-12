@extends('client.pages.account.layout')

@section('page_title', 'Báo Cáo Sĩ Số Bàn Tiệc & Điều Phối Chỗ Ngồi | ' . config('app.name'))
@section('account_breadcrumb', 'Sĩ Số Bàn Tiệc')

@section('account_content')
<div class="mayhen_table_capacity_studio" style="max-width: 1180px; margin: 0 auto;">
    {{-- Topbar --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <div style="margin-bottom: 8px;">
                <a href="{{ route('account.invitations') }}" style="color: #8C827A; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                    &larr; <span>Quay lại danh sách thiệp cưới</span>
                </a>
            </div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #1F1D1A; margin: 0; font-weight: 600;">
                Báo Cáo Sĩ Số Bàn Tiệc & Điều Phối Chỗ Ngồi (Table Capacity Wave)
            </h2>
            <p style="font-size: 14px; color: #8C827A; margin: 4px 0 0 0;">
                Theo dõi sóng đón khách và mức độ lấp đầy từng bàn tiệc theo thời gian thực • Báo bếp lên mâm đúng lúc & điều phối ghế trống linh hoạt.
            </p>
        </div>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('seating.index', $invitation->id) }}" 
               class="mayhen_account_btn" 
               style="background: #FFFFFF; border: 1px solid #D8CEBD; color: #4A453E; padding: 10px 16px; border-radius: 8px; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="4"></circle></svg>
                <span>Sơ Đồ Xếp Bàn 2D</span>
            </a>

            <a href="{{ route('table_capacity.usher', $invitation->slug) }}" target="_blank" 
               class="mayhen_account_btn" 
               style="background: #1F1D1A; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 13.5px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 600;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="12" y1="18" x2="12.01" y2="18"></line></svg>
                <span>Mở Tablet Điều Phối (Usher View) &rarr;</span>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div style="background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 13.5px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- 4 Card KPI Sóng Đón Khách Thời Gian Thực --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; margin-bottom: 28px;">
        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #8C827A; margin-bottom: 6px;">Tổng Sĩ Số Hiện Diện</div>
            <div style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: #1F1D1A; line-height: 1.1;">
                {{ $overview['total_checked_in'] }} <span style="font-size: 16px; font-weight: 500; color: #8C827A;">/ {{ $overview['total_capacity'] }} ghế</span>
            </div>
            <div style="font-size: 12.5px; color: #555; margin-top: 4px;">
                Dự kiến: {{ $overview['total_expected'] }} khách ({{ $overview['total_tables'] }} bàn)
            </div>
        </div>

        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #8C827A; margin-bottom: 6px;">Tỷ Lệ Lấp Đầy Sảnh Tiệc</div>
            <div style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: #047857; line-height: 1.1;">
                {{ $overview['venue_occupancy_percent'] }}%
            </div>
            <div style="margin-top: 8px; background: #E5E7EB; border-radius: 99px; height: 6px; overflow: hidden;">
                <div style="background: #10B981; height: 100%; width: {{ min(100, $overview['venue_occupancy_percent']) }}%;"></div>
            </div>
        </div>

        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #8C827A; margin-bottom: 6px;">Bàn Đã Đủ Mâm (Lên Món)</div>
            <div style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: #1F1D1A; line-height: 1.1;">
                {{ $overview['ready_to_serve_tables'] }} <span style="font-size: 16px; font-weight: 500; color: #8C827A;">bàn tròn 100%</span>
            </div>
            <div style="font-size: 12.5px; color: #046C4E; margin-top: 4px; font-weight: 500;">
                Bếp trưởng có thể bắt đầu lên món nóng
            </div>
        </div>

        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; padding: 18px 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #8C827A; margin-bottom: 6px;">Bàn Đang Có Khách / Quá Tải</div>
            <div style="display: flex; align-items: baseline; gap: 10px;">
                <span style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: #D97706; line-height: 1.1;">
                    {{ $overview['active_tables'] }}
                </span>
                <span style="font-size: 13.5px; color: #8C827A;">đang ngồi</span>
                @if($overview['overflow_tables'] > 0)
                    <span style="background: #FEE2E2; color: #991B1B; font-size: 11.5px; font-weight: 700; padding: 3px 8px; border-radius: 99px;">
                        {{ $overview['overflow_tables'] }} bàn quá tải
                    </span>
                @endif
            </div>
            <div style="font-size: 12.5px; color: #6B7280; margin-top: 4px;">
                Còn {{ $overview['empty_tables'] }} bàn chưa có khách
            </div>
        </div>
    </div>

    {{-- Bộ Lọc Khu Vực & Trạng Thái Bàn --}}
    <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 12px; padding: 14px 20px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size: 13px; font-weight: 600; color: #8C827A;">Khu Vực:</span>
            <a href="{{ route('account.table_capacity.index', ['id' => $invitation->id, 'status' => $currentStatus]) }}" 
               style="padding: 5px 12px; border-radius: 6px; font-size: 12.5px; text-decoration: none; {{ empty($currentZone) ? 'background: #1F1D1A; color: #FFFFFF; font-weight: 600;' : 'background: #FAF8F5; color: #555;' }}">
                Tất Cả
            </a>
            @foreach($allZones as $zone)
                <a href="{{ route('account.table_capacity.index', ['id' => $invitation->id, 'zone' => $zone, 'status' => $currentStatus]) }}" 
                   style="padding: 5px 12px; border-radius: 6px; font-size: 12.5px; text-decoration: none; {{ $currentZone === $zone ? 'background: #1F1D1A; color: #FFFFFF; font-weight: 600;' : 'background: #FAF8F5; color: #555;' }}">
                    {{ $zone }}
                </a>
            @endforeach
        </div>

        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <span style="font-size: 13px; font-weight: 600; color: #8C827A;">Trạng Thái:</span>
            <a href="{{ route('account.table_capacity.index', ['id' => $invitation->id, 'zone' => $currentZone]) }}" 
               style="padding: 5px 12px; border-radius: 6px; font-size: 12.5px; text-decoration: none; {{ empty($currentStatus) ? 'background: #1F1D1A; color: #FFFFFF; font-weight: 600;' : 'background: #FAF8F5; color: #555;' }}">
                Tất Cả
            </a>
            <a href="{{ route('account.table_capacity.index', ['id' => $invitation->id, 'zone' => $currentZone, 'status' => 'available']) }}" 
               style="padding: 5px 12px; border-radius: 6px; font-size: 12.5px; text-decoration: none; {{ $currentStatus === 'available' ? 'background: #D97706; color: #FFFFFF; font-weight: 600;' : 'background: #FAF8F5; color: #D97706;' }}">
                Còn Chỗ
            </a>
            <a href="{{ route('account.table_capacity.index', ['id' => $invitation->id, 'zone' => $currentZone, 'status' => 'ready_to_serve']) }}" 
               style="padding: 5px 12px; border-radius: 6px; font-size: 12.5px; text-decoration: none; {{ $currentStatus === 'ready_to_serve' ? 'background: #10B981; color: #FFFFFF; font-weight: 600;' : 'background: #FAF8F5; color: #047857;' }}">
                Đủ Mâm (100%)
            </a>
            <a href="{{ route('account.table_capacity.index', ['id' => $invitation->id, 'zone' => $currentZone, 'status' => 'overflow']) }}" 
               style="padding: 5px 12px; border-radius: 6px; font-size: 12.5px; text-decoration: none; {{ $currentStatus === 'overflow' ? 'background: #EF4444; color: #FFFFFF; font-weight: 600;' : 'background: #FAF8F5; color: #DC2626;' }}">
                Quá Tải
            </a>
        </div>
    </div>

    {{-- Lưới Heatmap Bàn Tiệc & Trạng Thái Sĩ Số --}}
    @if($tables->isEmpty())
        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 14px; padding: 48px 24px; text-align: center;">
            <p style="font-size: 14px; color: #8C827A; margin: 0;">Không tìm thấy bàn tiệc nào phù hợp với bộ lọc.</p>
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px; margin-bottom: 36px;">
            @foreach($tables as $table)
                <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 14px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        {{-- Header Bàn --}}
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                            <div>
                                <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 20px; color: #1F1D1A; margin: 0; font-weight: 700;">
                                    {{ $table['name'] }}
                                </h4>
                                <span style="font-size: 12px; color: #8C827A;">Khu: {{ $table['zone'] }}</span>
                            </div>

                            <span style="font-size: 11.5px; font-weight: 700; padding: 3px 8px; border-radius: 6px; background: {{ $table['status'] === 'overflow' ? '#FEE2E2' : ($table['status'] === 'ready_to_serve' ? '#DEF7EC' : ($table['status'] === 'available' ? '#FEF3C7' : '#F3F4F6')) }}; color: {{ $table['status_color'] }};">
                                {{ $table['status_label'] }}
                            </span>
                        </div>

                        {{-- Tiến Độ Lấp Đầy --}}
                        <div style="margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 4px;">
                                <span style="color: #6B7280;">Hiện diện: <strong>{{ $table['checked_in_count'] }}</strong> / {{ $table['capacity'] }} ghế</span>
                                <span style="font-weight: 700; color: {{ $table['status_color'] }};">{{ $table['occupancy_percent'] }}%</span>
                            </div>
                            <div style="background: #E5E7EB; border-radius: 99px; height: 8px; overflow: hidden;">
                                <div style="background: {{ $table['status_color'] }}; height: 100%; width: {{ min(100, $table['occupancy_percent']) }}%;"></div>
                            </div>
                        </div>

                        {{-- Danh Sách Khách Đã Ngồi --}}
                        <div style="background: #FAF8F5; border-radius: 8px; padding: 10px 12px; margin-bottom: 14px; max-height: 120px; overflow-y: auto;">
                            @if(empty($table['guests']))
                                <span style="font-size: 12px; color: #9CA3AF; font-style: italic;">Chưa có khách nào được xếp vào bàn này.</span>
                            @else
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    @foreach($table['guests'] as $guest)
                                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                                            <span style="color: {{ $guest['is_checked_in'] ? '#1F1D1A; font-weight: 600;' : '#8C827A;' }}">
                                                {{ $guest['is_checked_in'] ? '✓' : '○' }} {{ $guest['salutation'] }} {{ $guest['name'] }} ({{ $guest['attendees'] }})
                                            </span>
                                            @if($guest['is_checked_in'])
                                                <span style="font-size: 11px; color: #10B981;">{{ $guest['checked_in_at'] }}</span>
                                            @else
                                                <span style="font-size: 11px; color: #9CA3AF;">chưa đến</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Actions: Kê Thêm Ghế Phụ --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #F3EDE3; padding-top: 12px;">
                        <span style="font-size: 12px; color: #8C827A;">Sức chứa: {{ $table['capacity'] }} ghế</span>

                        <form action="{{ route('account.table_capacity.add_seat', [$invitation->id, $table['id']]) }}" method="POST" style="margin: 0;">
                            @csrf
                            <input type="hidden" name="seats" value="1">
                            <button type="submit" 
                                    style="background: #FFFFFF; border: 1px solid #D8CEBD; color: #4A453E; padding: 5px 10px; border-radius: 6px; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; font-weight: 500;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                <span>+1 Ghế Phụ</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

