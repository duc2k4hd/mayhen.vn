@extends('client.pages.account.layout')

@section('page_title', 'Sổ Kiểm Toán & Biến Động RSVP | ' . config('app.name'))
@section('account_breadcrumb', 'Sổ Kiểm Toán & Biến Động RSVP')

@section('account_content')
<div class="mayhen_rsvp_history_studio">
    {{-- Header & Top Actions --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <div style="margin-bottom: 8px; display: flex; align-items: center; gap: 12px; font-size: 13px;">
                <a href="{{ route('account.invitations') }}" style="color: #8C827A; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                    &larr; Danh sách thiệp
                </a>
                <span style="color: #D8CEBD;">•</span>
                <a href="{{ route('guests.index', $invitation->id) }}" style="color: #9C7A4A; text-decoration: none; font-weight: 500;">
                    Danh sách khách mời &rarr;
                </a>
            </div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #1F1D1A; margin: 0; font-weight: 600;">
                Sổ Kiểm Toán & Biến Động RSVP
            </h2>
            <p style="font-size: 14px; color: #8C827A; margin: 4px 0 0 0;">
                Theo dõi sát sao từng thay đổi phản hồi của khách mời • Cân đối mâm cỗ và phát hiện biến động sĩ số kịp thời trước ngày cưới.
            </p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('account.rsvp_history.export', array_merge(['id' => $invitation->id], request()->query())) }}" 
               class="mayhen_account_btn" 
               style="background: #FFFFFF; border: 1px solid #D8CEBD; color: #1F1D1A; padding: 10px 18px; border-radius: 8px; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 500;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span>Xuất File CSV Biến Động</span>
            </a>
            <a href="{{ route('account.rsvp_enforcement.index', $invitation->id) }}" 
               class="mayhen_account_btn" 
               style="background: #9C7A4A; color: #FFFFFF; padding: 10px 18px; border-radius: 8px; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 500;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span>Cài Đặt Hạn Chót RSVP</span>
            </a>
        </div>
    </div>

    {{-- 4 KPI Biến Động Sĩ Số Cỗ Cưới --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 12px; padding: 18px 20px;">
            <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #8C827A; margin-bottom: 6px;">Tổng Lượt Thay Đổi</div>
            <div style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: #1F1D1A; line-height: 1;">
                {{ number_format($summary['total_logs']) }}
            </div>
            <div style="font-size: 12px; color: #8C827A; margin-top: 6px;">
                24h qua: <strong style="color: #9C7A4A;">{{ $summary['recent_24h'] }}</strong> • 7 ngày qua: {{ $summary['recent_7d'] }}
            </div>
        </div>

        <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 12px; padding: 18px 20px;">
            <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #9B1C1C; margin-bottom: 6px;">Đổi Thành "Không Tham Dự"</div>
            <div style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: #9B1C1C; line-height: 1;">
                {{ number_format($summary['switched_to_declined']) }}
            </div>
            <div style="font-size: 12px; color: #9B1C1C; margin-top: 6px;">
                Cảnh báo giảm khách • Dôi dư mâm cỗ
            </div>
        </div>

        <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 12px; padding: 18px 20px;">
            <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #046C4E; margin-bottom: 6px;">Đổi Thành "Có Tham Dự"</div>
            <div style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: #046C4E; line-height: 1;">
                {{ number_format($summary['switched_to_attending']) }}
            </div>
            <div style="font-size: 12px; color: #046C4E; margin-top: 6px;">
                Khách bổ sung tham dự • Cần thêm ghế
            </div>
        </div>

        <div style="background: #FAF8F5; border: 1px solid #D8CEBD; border-radius: 12px; padding: 18px 20px;">
            <div style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #9C7A4A; margin-bottom: 6px;">Biến Động Sĩ Số Ròng</div>
            <div style="font-family: 'Cormorant Garamond', serif; font-size: 32px; font-weight: 700; color: {{ $summary['net_headcount_impact'] >= 0 ? '#046C4E' : '#9B1C1C' }}; line-height: 1;">
                {{ $summary['net_headcount_impact'] > 0 ? '+' : '' }}{{ $summary['net_headcount_impact'] }} <span style="font-size: 18px; font-family: 'Inter', sans-serif; font-weight: 500;">suất</span>
            </div>
            <div style="font-size: 12px; color: #8C827A; margin-top: 6px;">
                Tăng: +{{ $summary['headcount_increased'] }} lượt • Giảm: -{{ $summary['headcount_decreased'] }} lượt
            </div>
        </div>
    </div>

    {{-- Bộ Lọc Đa Tiêu Chí --}}
    <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px;">
        <form action="{{ route('account.rsvp_history.index', $invitation->id) }}" method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr auto; gap: 12px; align-items: center;">
            <div>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                       placeholder="Tìm kiếm theo tên khách, SĐT, nhóm..." 
                       style="width: 100%; box-sizing: border-box; padding: 9px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; background: #FAF8F5;">
            </div>

            <div>
                <select name="type" style="width: 100%; box-sizing: border-box; padding: 9px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; background: #FAF8F5; color: #1F1D1A;">
                    <option value="">-- Loại biến động --</option>
                    <option value="switched_declined" {{ ($filters['type'] ?? '') === 'switched_declined' ? 'selected' : '' }}>Đổi sang "Không đi"</option>
                    <option value="switched_attending" {{ ($filters['type'] ?? '') === 'switched_attending' ? 'selected' : '' }}>Đổi sang "Có đi"</option>
                    <option value="headcount_changed" {{ ($filters['type'] ?? '') === 'headcount_changed' ? 'selected' : '' }}>Thay đổi số người</option>
                    <option value="dietary_changed" {{ ($filters['type'] ?? '') === 'dietary_changed' ? 'selected' : '' }}>Thay đổi khẩu vị món</option>
                </select>
            </div>

            <div>
                <select name="source" style="width: 100%; box-sizing: border-box; padding: 9px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; background: #FAF8F5; color: #1F1D1A;">
                    <option value="">-- Nguồn thay đổi --</option>
                    <option value="guest_online" {{ ($filters['source'] ?? '') === 'guest_online' ? 'selected' : '' }}>Khách tự đổi trên web</option>
                    <option value="manual_host" {{ ($filters['source'] ?? '') === 'manual_host' ? 'selected' : '' }}>Cô dâu / Chú rể sửa</option>
                </select>
            </div>

            <div>
                <select name="time_range" style="width: 100%; box-sizing: border-box; padding: 9px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 13px; background: #FAF8F5; color: #1F1D1A;">
                    <option value="">-- Thời gian --</option>
                    <option value="24h" {{ ($filters['time_range'] ?? '') === '24h' ? 'selected' : '' }}>Trong 24 giờ qua</option>
                    <option value="7d" {{ ($filters['time_range'] ?? '') === '7d' ? 'selected' : '' }}>Trong 7 ngày qua</option>
                    <option value="30d" {{ ($filters['time_range'] ?? '') === '30d' ? 'selected' : '' }}>Trong 30 ngày qua</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" style="background: #1F1D1A; color: #FFFFFF; border: none; padding: 9px 16px; border-radius: 8px; font-size: 13px; cursor: pointer; font-weight: 500;">
                    Lọc
                </button>
                @if(!empty($filters))
                    <a href="{{ route('account.rsvp_history.index', $invitation->id) }}" style="background: #EAE3D9; color: #555; padding: 9px 12px; border-radius: 8px; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center;">
                        Xóa
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Bảng Nhật Ký Biến Động Chi Tiết --}}
    <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 14px; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #EAE3D9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 20px; color: #1F1D1A; margin: 0; font-weight: 600;">
                Nhật Ký Lịch Sử Chi Tiết ({{ $logs->count() }} lượt ghi nhận)
            </h3>
            <span style="font-size: 12px; color: #8C827A;">
                Tự động lưu vết vĩnh viễn theo chuẩn kiểm toán
            </span>
        </div>

        @if($logs->isEmpty())
            <div style="padding: 60px 20px; text-align: center;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #FAF8F5; border: 1px solid #EAE3D9; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 14px; color: #9C7A4A;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                </div>
                <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 20px; color: #1F1D1A; margin: 0 0 6px 0;">Chưa Có Biến Động Nào</h4>
                <p style="font-size: 13px; color: #8C827A; margin: 0 auto; max-width: 420px;">
                    Khi quan khách thay đổi câu trả lời hoặc cặp đôi điều chỉnh số lượng người tham dự, toàn bộ lịch sử sẽ được ghi lại chi tiết tại đây.
                </p>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                    <thead>
                        <tr style="background: #FAF8F5; border-bottom: 1px solid #EAE3D9; color: #8C827A; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <th style="padding: 12px 18px;">Thời Gian</th>
                            <th style="padding: 12px 18px;">Khách Mời</th>
                            <th style="padding: 12px 18px;">Biến Động Trạng Thái</th>
                            <th style="padding: 12px 18px;">Sĩ Số Cũ ➔ Mới</th>
                            <th style="padding: 12px 18px;">Thực Đơn / Ghi Chú</th>
                            <th style="padding: 12px 18px;">Nguồn Cập Nhật</th>
                            <th style="padding: 12px 18px; text-align: right;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            @php
                                $guest = $log->guest;
                                $prevStatus = $log->previous_status;
                                $newStatus = $log->new_status;
                                $prevTotal = (int) $log->previous_adults + (int) $log->previous_children;
                                $newTotal = (int) $log->new_adults + (int) $log->new_children;

                                $isStatusChanged = $prevStatus !== $newStatus;
                                $isHeadcountChanged = $prevTotal !== $newTotal;
                            @endphp
                            <tr style="border-bottom: 1px solid #F3EFEA; transition: background 0.15s ease;" onmouseover="this.style.background='#FFFDF9'" onmouseout="this.style.background='transparent'">
                                {{-- Thời gian --}}
                                <td style="padding: 14px 18px; white-space: nowrap;">
                                    <div style="font-weight: 500; color: #1F1D1A;">
                                        {{ $log->created_at ? $log->created_at->format('H:i') : '--:--' }}
                                    </div>
                                    <div style="font-size: 11px; color: #8C827A;">
                                        {{ $log->created_at ? $log->created_at->format('d/m/Y') : '' }}
                                    </div>
                                </td>

                                {{-- Khách mời --}}
                                <td style="padding: 14px 18px;">
                                    <div style="font-weight: 600; color: #1F1D1A;">
                                        {{ $guest?->name ?: 'Khách chưa gán ID' }}
                                    </div>
                                    <div style="font-size: 11px; color: #8C827A; display: flex; gap: 8px; margin-top: 2px;">
                                        @if($guest?->group_name)
                                            <span>Nhóm: {{ $guest->group_name }}</span>
                                        @endif
                                        @if($guest?->phone)
                                            <span>• SĐT: {{ $guest->phone }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Biến động trạng thái --}}
                                <td style="padding: 14px 18px;">
                                    <div style="display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                        {{-- Trạng thái cũ --}}
                                        @if($prevStatus === 'attending')
                                            <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; background: #DEF7EC; color: #03543F;">Có tham dự</span>
                                        @elseif($prevStatus === 'declined')
                                            <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; background: #FDE8E8; color: #9B1C1C;">Không tham dự</span>
                                        @else
                                            <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 500; background: #F3F4F6; color: #4B5563;">Chưa phản hồi</span>
                                        @endif

                                        <span style="color: #9C7A4A; font-weight: 600;">&rarr;</span>

                                        {{-- Trạng thái mới --}}
                                        @if($newStatus === 'attending')
                                            <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #DEF7EC; color: #03543F; border: 1px solid #31C48D;">Có tham dự</span>
                                        @elseif($newStatus === 'declined')
                                            <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #FDE8E8; color: #9B1C1C; border: 1px solid #F98080;">Không tham dự</span>
                                        @else
                                            <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #FEF08A; color: #713F12;">Chưa chắc chắn</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Sĩ số --}}
                                <td style="padding: 14px 18px;">
                                    @if($isHeadcountChanged)
                                        <div style="font-weight: 600; color: {{ $newTotal > $prevTotal ? '#046C4E' : '#9B1C1C' }};">
                                            {{ $prevTotal }} khách ➔ {{ $newTotal }} khách
                                        </div>
                                        <div style="font-size: 11px; color: #8C827A;">
                                            ({{ $newTotal - $prevTotal > 0 ? '+' : '' }}{{ $newTotal - $prevTotal }} người)
                                        </div>
                                    @else
                                        <span style="color: #555;">{{ $newTotal }} khách (Giữ nguyên)</span>
                                    @endif
                                </td>

                                {{-- Thực đơn & Ghi chú --}}
                                <td style="padding: 14px 18px; max-width: 240px;">
                                    @if($log->new_dietary)
                                        <div style="font-size: 12px; color: #723B13; background: #FEF3C7; padding: 2px 6px; border-radius: 4px; display: inline-block; margin-bottom: 2px;">
                                            🥗 {{ $log->new_dietary }}
                                        </div>
                                    @endif
                                    @if($log->new_wishes)
                                        <div style="font-size: 12px; color: #555; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $log->new_wishes }}">
                                            "{{ $log->new_wishes }}"
                                        </div>
                                    @endif
                                    @if(!$log->new_dietary && !$log->new_wishes)
                                        <span style="color: #A39A91; font-size: 12px;">-- Không có ghi chú --</span>
                                    @endif
                                </td>

                                {{-- Nguồn cập nhật --}}
                                <td style="padding: 14px 18px; white-space: nowrap;">
                                    @if($log->source === 'manual_host')
                                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: #9C7A4A; font-weight: 500;">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #9C7A4A;"></span>
                                            Dâu rể sửa tay
                                        </span>
                                    @else
                                        <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: #0E9F6E; font-weight: 500;">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #0E9F6E;"></span>
                                            Khách tự đổi
                                        </span>
                                    @endif
                                    @if($log->ip_address)
                                        <div style="font-size: 10px; color: #A39A91; font-family: monospace;">{{ $log->ip_address }}</div>
                                    @endif
                                </td>

                                {{-- Thao tác --}}
                                <td style="padding: 14px 18px; text-align: right; white-space: nowrap;">
                                    @if($guest)
                                        <button type="button" 
                                                onclick="openGuestTimelineModal({{ $guest->id }}, '{{ addslashes($guest->name) }}')"
                                                style="background: #FAF8F5; border: 1px solid #D8CEBD; color: #1F1D1A; padding: 6px 12px; border-radius: 6px; font-size: 12px; cursor: pointer; font-weight: 500;">
                                            Xem dòng thời gian &rarr;
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- Modal Xem Dòng Thời Gian Cá Nhân Hóa Của Khách (Pure Vanilla JS) --}}
<div id="mayhen_guest_timeline_modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
    <div style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 580px; max-height: 85vh; overflow-y: auto; padding: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); border: 1px solid #EAE3D9; position: relative;">
        <button type="button" onclick="closeGuestTimelineModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 20px; color: #8C827A; cursor: pointer;">
            &times;
        </button>

        <div style="margin-bottom: 20px;">
            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #9C7A4A; font-weight: 600;">Hành Trình Khách Mời</span>
            <h3 id="timeline_guest_name" style="font-family: 'Cormorant Garamond', serif; font-size: 24px; color: #1F1D1A; margin: 4px 0 0 0; font-weight: 600;">
                Dòng Thời Gian Tương Tác
            </h3>
            <p id="timeline_guest_meta" style="font-size: 13px; color: #8C827A; margin: 4px 0 0 0;">
                Đang tải dữ liệu lịch sử...
            </p>
        </div>

        <div id="timeline_content_container" style="display: flex; flex-direction: column; gap: 18px; position: relative; padding-left: 18px; border-left: 2px solid #EAE3D9; margin-left: 8px;">
            {{-- Render bằng JS --}}
        </div>

        <div style="margin-top: 24px; text-align: right; border-top: 1px solid #EAE3D9; padding-top: 16px;">
            <button type="button" onclick="closeGuestTimelineModal()" style="background: #1F1D1A; color: #FFFFFF; border: none; padding: 8px 18px; border-radius: 8px; font-size: 13px; cursor: pointer; font-weight: 500;">
                Đóng
            </button>
        </div>
    </div>
</div>

<script>
    const timelineApiBase = "{{ route('account.rsvp_history.guest_timeline', ['id' => $invitation->id, 'guestId' => '__GUEST_ID__']) }}";

    function openGuestTimelineModal(guestId, guestName) {
        const modal = document.getElementById('mayhen_guest_timeline_modal');
        const titleEl = document.getElementById('timeline_guest_name');
        const metaEl = document.getElementById('timeline_guest_meta');
        const container = document.getElementById('timeline_content_container');

        titleEl.textContent = guestName;
        metaEl.textContent = 'Đang tải toàn bộ dữ liệu lịch sử tương tác...';
        container.innerHTML = '<div style="padding: 20px 0; color: #8C827A; font-size: 13px;">Đang tải...</div>';
        modal.style.display = 'flex';

        const url = timelineApiBase.replace('__GUEST_ID__', guestId);
        fetch(url, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                container.innerHTML = '<div style="color: #9B1C1C; font-size: 13px;">Không thể tải dữ liệu.</div>';
                return;
            }

            const guest = data.guest;
            metaEl.textContent = (guest.group_name ? 'Nhóm: ' + guest.group_name : '') + (guest.table_name ? ' • Bàn: ' + guest.table_name : '') + ' • Dự kiến: ' + guest.expected_attendees + ' người';

            if (!data.timeline || data.timeline.length === 0) {
                container.innerHTML = '<div style="color: #8C827A; font-size: 13px;">Chưa có hoạt động tương tác nào được ghi nhận.</div>';
                return;
            }

            let html = '';
            data.timeline.forEach(item => {
                let badgeBg = '#FAF8F5';
                let badgeColor = '#9C7A4A';
                let dotColor = '#9C7A4A';

                if (item.badge_type === 'success') {
                    badgeBg = '#DEF7EC';
                    badgeColor = '#03543F';
                    dotColor = '#0E9F6E';
                } else if (item.badge_type === 'danger') {
                    badgeBg = '#FDE8E8';
                    badgeColor = '#9B1C1C';
                    dotColor = '#F05252';
                }

                html += `
                    <div style="position: relative;">
                        <span style="position: absolute; left: -25px; top: 4px; width: 12px; height: 12px; border-radius: 50%; background: ${dotColor}; border: 2px solid #FFFFFF;"></span>
                        <div style="display: flex; justify-content: space-between; align-items: baseline; gap: 8px;">
                            <strong style="color: #1F1D1A; font-size: 14px;">${item.title}</strong>
                            <span style="font-size: 11px; color: #8C827A; white-space: nowrap;">${item.timestamp_formatted}</span>
                        </div>
                        <div style="font-size: 13px; color: #555; margin-top: 4px; line-height: 1.4;">
                            ${item.description}
                        </div>
                        <div style="font-size: 11px; color: #8C827A; margin-top: 4px;">
                            Thực hiện bởi: <span style="font-weight: 500; color: #1F1D1A;">${item.actor}</span>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        })
        .catch(err => {
            console.error(err);
            container.innerHTML = '<div style="color: #9B1C1C; font-size: 13px;">Có lỗi xảy ra khi kết nối máy chủ.</div>';
        });
    }

    function closeGuestTimelineModal() {
        document.getElementById('mayhen_guest_timeline_modal').style.display = 'none';
    }

    // Đóng khi click ngoài modal box
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('mayhen_guest_timeline_modal');
        if (e.target === modal) {
            closeGuestTimelineModal();
        }
    });
</script>
@endsection
