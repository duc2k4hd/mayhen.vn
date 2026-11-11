@extends('admin.layouts.master')

@section('title', 'Trung Tâm Giám Sát Đón Tiếp & Điểm Danh Ngày Cưới')

@section('content')
<div class="mayhen_admin_checkin_container" style="display: flex; flex-direction: column; gap: 24px; font-family: var(--mh-admin-font);">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #78716c; margin-bottom: 6px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Tổng quan</a>
                <span>/</span>
                <a href="{{ route('admin.guests.index') }}" style="color: inherit; text-decoration: none;">Khách mời &amp; RSVP</a>
                <span>/</span>
                <span style="color: #1c1917; font-weight: 600;">Trạm Điểm Danh Ngày Cưới</span>
            </div>
            <h1 style="font-size: 24px; font-weight: 700; color: #1c1917; margin: 0; line-height: 1.2;">Trung Tâm Giám Sát Đón Tiếp &amp; Điểm Danh Ngày Cưới</h1>
            <p style="font-size: 14px; color: #78716c; margin: 6px 0 0 0;">Giám sát luồng khách thời gian thực, quét mã QR/nhập token, điều chỉnh sĩ số thực tế và phân bổ bàn tiệc trực tiếp.</p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            @if($activeWedding)
                <button type="button" onclick="refreshLiveStats()" class="mayhen_admin_btn" id="btnRefreshStats" style="padding: 9px 14px; font-size: 13px; border-radius: 8px; background: #ffffff; border: 1px solid #d6d3d1; color: #44403c; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                    <span>Làm Mới Số Liệu</span>
                </button>
                <a href="{{ route('admin.guests.checkin.export', $activeWedding->id) }}" class="mayhen_admin_btn" style="padding: 9px 16px; font-size: 13px; border-radius: 8px; background: #ffffff; border: 1px solid #d6d3d1; color: #44403c; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Xuất Báo Cáo CSV
                </a>
                <button type="button" onclick="openWalkInModal()" class="mayhen_admin_btn" style="padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 8px; background: #292524; color: #ffffff; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Đón Khách Phát Sinh (Walk-in)
                </button>
            @endif
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 14px 18px; color: #166534; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 14px 18px; color: #991b1b; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Bộ Chọn Tiệc Cưới Hoạt Động -->
    <div class="mayhen_admin_card" style="padding: 14px 20px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <form method="GET" action="{{ route('admin.guests.checkin.index') }}" style="display: flex; align-items: center; gap: 12px; flex: 1;">
            <label style="font-size: 13px; font-weight: 600; color: #44403c; white-space: nowrap;">Tiệc cưới giám sát:</label>
            <select name="wedding_id" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff; max-width: 450px; width: 100%;">
                @forelse($allWeddings as $w)
                    <option value="{{ $w->id }}" {{ $activeWedding?->id == $w->id ? 'selected' : '' }}>
                        #{{ $w->id }} — {{ $w->groom_name }} &amp; {{ $w->bride_name }} ({{ $w->wedding_date ? $w->wedding_date->format('d/m/Y') : 'Chưa đặt ngày' }})
                    </option>
                @empty
                    <option value="">Chưa có dữ liệu đám cưới</option>
                @endforelse
            </select>
        </form>

        @if($activeWedding)
            <div style="font-size: 12px; color: #78716c; display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
                <span>Ngày cưới: <strong style="color: #1c1917;">{{ $activeWedding->wedding_date ? $activeWedding->wedding_date->format('d/m/Y') : '—' }}</strong></span>
                <span>•</span>
                <span>Địa điểm: <strong style="color: #1c1917;">{{ $activeWedding->venue_name ?: 'Chưa cập nhật sảnh' }}</strong></span>
                <span>•</span>
                <span id="liveBadge" style="display: inline-flex; align-items: center; gap: 5px; color: #15803d; font-weight: 600;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e; display: inline-block;"></span>
                    Live Monitor
                </span>
            </div>
        @endif
    </div>

    @if($activeWedding && $cockpit)
        <!-- 5 KPI Thẻ Đón Tiếp Cốt Lõi -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 14px;">
            <!-- Tổng Dự Kiến -->
            <div class="mayhen_admin_card" style="padding: 16px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
                <div style="font-size: 11px; text-transform: uppercase; color: #78716c; font-weight: 600; letter-spacing: 0.5px;">Tổng Sĩ Số Dự Kiến</div>
                <div style="font-size: 26px; font-weight: 700; color: #1c1917; margin-top: 4px;" id="statTotalExpected">
                    {{ $cockpit['stats']['total_expected_attendees'] }} <span style="font-size: 14px; font-weight: 500; color: #78716c;">người</span>
                </div>
                <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                    Tổng số quan khách: <strong id="statTotalGuests">{{ $cockpit['stats']['total_guests'] }}</strong> thiệp
                </div>
            </div>

            <!-- Đã Đón Tiếp (Turnout) -->
            <div class="mayhen_admin_card" style="padding: 16px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
                <div style="font-size: 11px; text-transform: uppercase; color: #047857; font-weight: 600; letter-spacing: 0.5px;">Đã Đón Tiếp (Check-in)</div>
                <div style="font-size: 26px; font-weight: 700; color: #047857; margin-top: 4px;" id="statCheckedInAttendees">
                    {{ $cockpit['stats']['checked_in_attendees'] }} <span style="font-size: 14px; font-weight: 500; color: #059669;">người</span>
                </div>
                <div style="font-size: 12px; color: #047857; margin-top: 2px; display: flex; align-items: center; justify-content: space-between;">
                    <span><strong id="statCheckedInGuests">{{ $cockpit['stats']['checked_in_guests'] }}</strong> quan khách</span>
                    <span style="background: #ecfdf5; padding: 2px 6px; border-radius: 4px; font-weight: 700;" id="statTurnoutRate">{{ $cockpit['stats']['turnout_rate'] }}%</span>
                </div>
            </div>

            <!-- Chưa Đến -->
            <div class="mayhen_admin_card" style="padding: 16px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
                <div style="font-size: 11px; text-transform: uppercase; color: #b45309; font-weight: 600; letter-spacing: 0.5px;">Chưa Đến</div>
                <div style="font-size: 26px; font-weight: 700; color: #b45309; margin-top: 4px;" id="statNotCheckedInAttendees">
                    {{ $cockpit['stats']['not_checked_in_attendees'] }} <span style="font-size: 14px; font-weight: 500; color: #d97706;">người</span>
                </div>
                <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                    Còn lại: <strong id="statNotCheckedInGuests">{{ $cockpit['stats']['not_checked_in_guests'] }}</strong> quan khách
                </div>
            </div>

            <!-- Đến Một Phần -->
            <div class="mayhen_admin_card" style="padding: 16px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
                <div style="font-size: 11px; text-transform: uppercase; color: #4338ca; font-weight: 600; letter-spacing: 0.5px;">Đến Một Phần (Partial)</div>
                <div style="font-size: 26px; font-weight: 700; color: #4338ca; margin-top: 4px;" id="statPartiallyCheckedIn">
                    {{ $cockpit['stats']['partially_checked_in_guests'] }} <span style="font-size: 14px; font-weight: 500; color: #6366f1;">nhóm</span>
                </div>
                <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                    Đi ít hơn sĩ số xác nhận
                </div>
            </div>

            <!-- Khách Phát Sinh (Walk-in) -->
            <div class="mayhen_admin_card" style="padding: 16px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
                <div style="font-size: 11px; text-transform: uppercase; color: #be185d; font-weight: 600; letter-spacing: 0.5px;">Khách Phát Sinh (Walk-in)</div>
                <div style="font-size: 26px; font-weight: 700; color: #be185d; margin-top: 4px;" id="statWalkIn">
                    {{ $cockpit['stats']['walk_in_guests'] }} <span style="font-size: 14px; font-weight: 500; color: #ec4899;">khách</span>
                </div>
                <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                    Bổ sung trực tiếp tại cửa
                </div>
            </div>
        </div>

        <!-- Thanh Tiến Độ Đón Tiếp Trực Quan -->
        <div class="mayhen_admin_card" style="padding: 16px 20px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-size: 13px; font-weight: 600; color: #1c1917;">Tiến Độ Đón Tiếp Quan Khách</span>
                <span style="font-size: 13px; font-weight: 700; color: #047857;" id="progressLabel">{{ $cockpit['stats']['turnout_rate'] }}% Sĩ Số Hoàn Thành</span>
            </div>
            <div style="height: 10px; background: #f5f5f4; border-radius: 999px; overflow: hidden; position: relative;">
                <div id="progressBar" style="height: 100%; background: linear-gradient(90deg, #10b981 0%, #059669 100%); width: {{ min(100, $cockpit['stats']['turnout_rate']) }}%; transition: width 0.4s ease;"></div>
            </div>
        </div>

        <!-- Layout 2 Cột: Timeline Khung Giờ & Trạm Tìm Kiếm Tức Thì -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 20px;">
            <!-- Cột Trái: Timeline Đón Khách Theo Khung Giờ -->
            <div class="mayhen_admin_card" style="padding: 20px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4; display: flex; flex-direction: column; gap: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h3 style="font-size: 15px; font-weight: 700; color: #1c1917; margin: 0;">Luồng Khách Theo Khung Giờ (Hourly Flow)</h3>
                        <p style="font-size: 12px; color: #78716c; margin: 3px 0 0 0;">Phục vụ ban tổ chức và bếp điều phối món ăn đúng nhịp đón khách.</p>
                    </div>
                </div>

                <div id="timelineContainer" style="display: flex; flex-direction: column; gap: 10px; margin-top: 4px;">
                    @foreach($cockpit['timeline'] as $slot)
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <div style="display: flex; justify-content: space-between; font-size: 12px;">
                                <span style="font-weight: 600; color: #44403c;">{{ $slot['label'] }}</span>
                                <span style="color: #78716c;"><strong>{{ $slot['count'] }}</strong> lượt ({{ $slot['attendees'] }} người)</span>
                            </div>
                            <div style="height: 7px; background: #f5f5f4; border-radius: 4px; overflow: hidden;">
                                <div style="height: 100%; background: #059669; width: {{ $slot['percentage'] }}%; border-radius: 4px;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Cột Phải: Trạm Quét QR & Tìm Kiếm Tức Thì -->
            <div class="mayhen_admin_card" style="padding: 20px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4; display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <h3 style="font-size: 15px; font-weight: 700; color: #1c1917; margin: 0;">Trạm Quét QR &amp; Tìm Nhanh Quan Khách</h3>
                    <p style="font-size: 12px; color: #78716c; margin: 3px 0 0 0;">Nhập mã token, quét link QR hoặc gõ Tên/SĐT/Bàn để điểm danh ngay.</p>
                </div>

                <!-- Input Search -->
                <div style="position: relative;">
                    <input type="text" id="quickLookupInput" placeholder="Dán mã QR, gõ họ tên, số điện thoại hoặc bàn..." style="width: 100%; padding: 11px 40px 11px 14px; border: 2px solid #d6d3d1; border-radius: 8px; font-size: 14px; font-family: var(--mh-admin-font); outline: none; box-sizing: border-box;" oninput="handleQuickSearch(this.value)">
                    <div style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #78716c;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </div>
                </div>

                <!-- Quick Search Results List -->
                <div id="quickSearchResults" style="display: flex; flex-direction: column; gap: 8px; max-height: 280px; overflow-y: auto;">
                    <div style="text-align: center; padding: 24px; color: #a8a29e; font-size: 13px;">
                        Gõ từ khóa hoặc dán mã QR vào ô trên để tìm kiếm quan khách...
                    </div>
                </div>
            </div>
        </div>

        <!-- Bộ Lọc & Danh Sách Toàn Bộ Quan Khách Đón Tiếp -->
        <div class="mayhen_admin_card" style="padding: 20px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4; display: flex; flex-direction: column; gap: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #1c1917; margin: 0;">Sổ Điểm Danh &amp; Đón Tiếp Quan Khách</h3>

                <!-- Bộ Lọc Trạng Thái Tabs -->
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}" style="padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 600; {{ $filters['status'] === 'all' ? 'background: #292524; color: #ffffff;' : 'background: #f5f5f4; color: #44403c;' }}">
                        Tất cả ({{ $cockpit['stats']['total_guests'] }})
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'checked_in', 'page' => 1]) }}" style="padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 600; {{ $filters['status'] === 'checked_in' ? 'background: #059669; color: #ffffff;' : 'background: #ecfdf5; color: #047857;' }}">
                        Đã đến ({{ $cockpit['stats']['checked_in_guests'] }})
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'not_checked_in', 'page' => 1]) }}" style="padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 600; {{ $filters['status'] === 'not_checked_in' ? 'background: #d97706; color: #ffffff;' : 'background: #fffbeb; color: #b45309;' }}">
                        Chưa đến ({{ $cockpit['stats']['not_checked_in_guests'] }})
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'partially_checked_in', 'page' => 1]) }}" style="padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 600; {{ $filters['status'] === 'partially_checked_in' ? 'background: #4f46e5; color: #ffffff;' : 'background: #eef2ff; color: #4338ca;' }}">
                        Một phần ({{ $cockpit['stats']['partially_checked_in_guests'] }})
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'walk_in', 'page' => 1]) }}" style="padding: 6px 12px; border-radius: 6px; font-size: 12px; text-decoration: none; font-weight: 600; {{ $filters['status'] === 'walk_in' ? 'background: #db2777; color: #ffffff;' : 'background: #fdf2f8; color: #be185d;' }}">
                        Phát sinh ({{ $cockpit['stats']['walk_in_guests'] }})
                    </a>
                </div>
            </div>

            <!-- Form Tìm Kiếm & Lọc Nâng Cao -->
            <form method="GET" action="{{ route('admin.guests.checkin.index') }}" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="hidden" name="wedding_id" value="{{ $activeWedding->id }}">
                <input type="hidden" name="status" value="{{ $filters['status'] }}">

                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Tìm theo tên, SĐT, bàn tiệc..." style="padding: 8px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); min-width: 220px; flex: 1;">

                <!-- Lọc Theo Bàn Tiệc -->
                <select name="table_id" style="padding: 8px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff;">
                    <option value="">— Mọi Bàn Tiệc —</option>
                    @foreach($cockpit['tables'] as $tbl)
                        <option value="{{ $tbl['id'] }}" {{ $filters['table_id'] == $tbl['id'] ? 'selected' : '' }}>
                            {{ $tbl['name'] }} ({{ $tbl['occupied_seats'] }}/{{ $tbl['capacity'] }})
                        </option>
                    @endforeach
                </select>

                <!-- Lọc Theo Nhóm Khách -->
                <select name="group_name" style="padding: 8px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff;">
                    <option value="">— Mọi Nhóm Khách —</option>
                    @foreach($cockpit['groups'] as $grp)
                        <option value="{{ $grp }}" {{ $filters['group_name'] === $grp ? 'selected' : '' }}>
                            {{ $grp }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="mayhen_admin_btn" style="padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #292524; color: #ffffff; border: none; cursor: pointer;">
                    Lọc Danh Sách
                </button>
                @if(!empty($filters['q']) || !empty($filters['table_id']) || !empty($filters['group_name']) || $filters['status'] !== 'all')
                    <a href="{{ route('admin.guests.checkin.index', ['wedding_id' => $activeWedding->id]) }}" style="padding: 8px 14px; font-size: 13px; border-radius: 6px; background: #f5f5f4; color: #44403c; text-decoration: none; display: inline-flex; align-items: center;">
                        Xóa Bộ Lọc
                    </a>
                @endif
            </form>

            <!-- Bảng Dữ Liệu Quan Khách -->
            <div style="overflow-x: auto; border: 1px solid #e7e5e4; border-radius: 8px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                    <thead>
                        <tr style="background: #fafaf9; border-bottom: 1px solid #e7e5e4; color: #78716c; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <th style="padding: 12px 14px;">Quan Khách</th>
                            <th style="padding: 12px 14px;">Bàn Tiệc</th>
                            <th style="padding: 12px 14px; text-align: center;">Dự Kiến</th>
                            <th style="padding: 12px 14px; text-align: center;">Thực Tế</th>
                            <th style="padding: 12px 14px;">Trạng Thái Đón Tiếp</th>
                            <th style="padding: 12px 14px;">Thời Điểm</th>
                            <th style="padding: 12px 14px;">Ghi Chú</th>
                            <th style="padding: 12px 14px; text-align: right;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cockpit['guests'] as $guest)
                            @php
                                $isCheckedIn = $guest->checked_in_at !== null;
                                $expected = $guest->actualAttendeesCount();
                                $actual = $isCheckedIn ? ($guest->checked_in_attendees ?: $expected) : 0;
                                $isPartial = $isCheckedIn && $actual > 0 && $actual < $expected;
                                $isWalkIn = str_contains(mb_strtolower((string)$guest->group_name), 'phát sinh') || str_contains(mb_strtolower((string)$guest->check_in_note), 'khách phát sinh');
                            @endphp
                            <tr style="border-bottom: 1px solid #f5f5f4; {{ $isCheckedIn ? 'background: #fafdfb;' : '' }}">
                                <!-- Tên & Nhóm -->
                                <td style="padding: 12px 14px;">
                                    <div style="font-weight: 600; color: #1c1917;">{{ $guest->name }}</div>
                                    <div style="font-size: 11px; color: #78716c;">
                                        {{ $guest->salutation ?: 'Thân mời' }} • {{ $guest->phone ?: 'Chưa có SĐT' }} • <span style="background: #f5f5f4; padding: 1px 6px; border-radius: 4px;">{{ $guest->group_name ?: 'Khách chung' }}</span>
                                    </div>
                                </td>

                                <!-- Bàn Tiệc -->
                                <td style="padding: 12px 14px;">
                                    @if($guest->table)
                                        <span style="font-weight: 600; color: #1c1917;">{{ $guest->table->name }}</span>
                                        <div style="font-size: 11px; color: #78716c;">{{ $guest->table->zone ?? 'Sảnh tiệc' }}</div>
                                    @elseif($guest->table_name)
                                        <span style="font-weight: 600; color: #44403c;">{{ $guest->table_name }}</span>
                                    @else
                                        <span style="color: #a8a29e; font-style: italic;">Chưa xếp bàn</span>
                                    @endif
                                </td>

                                <!-- Dự kiến -->
                                <td style="padding: 12px 14px; text-align: center; font-weight: 600; color: #44403c;">
                                    {{ $expected }}
                                </td>

                                <!-- Thực tế -->
                                <td style="padding: 12px 14px; text-align: center;">
                                    @if($isCheckedIn)
                                        <span style="font-weight: 700; color: {{ $isPartial ? '#4338ca' : '#047857' }};">
                                            {{ $actual }}
                                        </span>
                                    @else
                                        <span style="color: #a8a29e;">—</span>
                                    @endif
                                </td>

                                <!-- Trạng thái -->
                                <td style="padding: 12px 14px;">
                                    @if($isCheckedIn)
                                        @if($isPartial)
                                            <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #eef2ff; color: #4338ca;">
                                                Đến một phần ({{ $actual }}/{{ $expected }})
                                            </span>
                                        @elseif($isWalkIn)
                                            <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #fdf2f8; color: #be185d;">
                                                Đã đón (Phát sinh)
                                            </span>
                                        @else
                                            <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #ecfdf5; color: #047857;">
                                                Đã đón tiếp
                                            </span>
                                        @endif
                                    @else
                                        <span style="display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #f5f5f4; color: #78716c;">
                                            Chưa đến
                                        </span>
                                    @endif
                                </td>

                                <!-- Thời điểm -->
                                <td style="padding: 12px 14px; font-size: 12px; color: #78716c;">
                                    {{ $guest->checked_in_at ? $guest->checked_in_at->format('H:i:s d/m') : '—' }}
                                </td>

                                <!-- Ghi chú -->
                                <td style="padding: 12px 14px; font-size: 12px; color: #78716c; max-width: 180px;">
                                    <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $guest->check_in_note }}">
                                        {{ $guest->check_in_note ?: '—' }}
                                    </div>
                                    @if($guest->response?->dietary_requirements)
                                        <div style="color: #b45309; font-size: 11px;">Ăn kiêng: {{ $guest->response->dietary_requirements }}</div>
                                    @endif
                                </td>

                                <!-- Hành động -->
                                <td style="padding: 12px 14px; text-align: right;">
                                    <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                        @if(! $isCheckedIn)
                                            <button type="button" onclick="quickCheckIn({{ $guest->id }}, {{ $expected }})" class="mayhen_admin_btn" style="padding: 6px 12px; font-size: 12px; font-weight: 600; border-radius: 6px; background: #059669; color: #ffffff; border: none; cursor: pointer;">
                                                Đón Ngay
                                            </button>
                                        @else
                                            <button type="button" onclick="cancelCheckIn({{ $guest->id }})" class="mayhen_admin_btn" style="padding: 6px 10px; font-size: 12px; border-radius: 6px; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; cursor: pointer;">
                                                Hủy
                                            </button>
                                        @endif
                                        <button type="button" onclick="openAdjustModal({{ $guest->id }}, '{{ addslashes($guest->name) }}', {{ $expected }}, {{ $isCheckedIn ? $actual : $expected }}, '{{ addslashes($guest->check_in_note ?? '') }}', {{ $guest->table_id ?: 'null' }})" class="mayhen_admin_btn" style="padding: 6px 10px; font-size: 12px; border-radius: 6px; background: #f5f5f4; color: #44403c; border: 1px solid #d6d3d1; cursor: pointer;" title="Điều chỉnh sĩ số & đổi bàn">
                                            Sửa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="padding: 32px; text-align: center; color: #a8a29e;">
                                    Không tìm thấy quan khách nào phù hợp với bộ lọc hiện tại.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Phân Trang -->
            @if($cockpit['guests']->hasPages())
                <div style="display: flex; justify-content: center; padding: 12px 0;">
                    {{ $cockpit['guests']->links() }}
                </div>
            @endif
        </div>
    @else
        <!-- Empty State Khi Chưa Chọn Đám Cưới -->
        <div class="mayhen_admin_card" style="padding: 48px; text-align: center; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#a8a29e" stroke-width="1.5" style="margin-bottom: 12px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <h3 style="font-size: 16px; font-weight: 700; color: #1c1917; margin: 0 0 6px 0;">Chưa Chọn Tiệc Cưới Giám Sát</h3>
            <p style="font-size: 14px; color: #78716c; max-width: 420px; margin: 0 auto 16px auto;">Vui lòng chọn một tiệc cưới ở thanh trên để kích hoạt trạm điều hành đón tiếp và giám sát luồng khách thời gian thực.</p>
        </div>
    @endif
</div>

<!-- Modal 1: Điểm Danh & Điều Chỉnh Sĩ Số / Bàn Tiệc -->
<div id="modalAdjust" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 480px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); font-family: var(--mh-admin-font);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 17px; font-weight: 700; color: #1c1917; margin: 0;">Điều Chỉnh Điểm Danh &amp; Bàn Tiệc</h3>
            <button type="button" onclick="closeAdjustModal()" style="background: none; border: none; font-size: 20px; color: #78716c; cursor: pointer;">&times;</button>
        </div>

        <form id="formAdjust" method="POST" action="">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Quan khách:</label>
                    <div id="modalGuestName" style="font-size: 15px; font-weight: 700; color: #1c1917;"></div>
                    <div id="modalGuestExpected" style="font-size: 12px; color: #78716c;"></div>
                </div>

                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Sĩ số thực tế đến tiệc (người):</label>
                    <input type="number" name="attendees" id="modalAttendeesInput" min="1" max="50" required style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 14px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>

                @if($activeWedding && $cockpit)
                    <div>
                        <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Chuyển / Xếp vào bàn tiệc:</label>
                        <select name="table_id" id="modalTableSelect" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff; box-sizing: border-box;">
                            <option value="">— Giữ nguyên bàn hiện tại —</option>
                            @foreach($cockpit['tables'] as $tbl)
                                <option value="{{ $tbl['id'] }}">
                                    {{ $tbl['name'] }} (Còn {{ $tbl['available_seats'] }}/{{ $tbl['capacity'] }} chỗ)
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Ghi chú đón tiếp (tùy chọn):</label>
                    <input type="text" name="note" id="modalNoteInput" placeholder="Ví dụ: Kê thêm 1 ghế trẻ em, ngồi cùng bàn công ty..." style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeAdjustModal()" style="padding: 9px 16px; font-size: 13px; border-radius: 6px; background: #f5f5f4; color: #44403c; border: 1px solid #d6d3d1; cursor: pointer;">Hủy</button>
                <button type="submit" style="padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #059669; color: #ffffff; border: none; cursor: pointer;">Lưu &amp; Xác Nhận Đón</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Thêm Khách Phát Sinh (Walk-in) -->
<div id="modalWalkIn" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 500px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); font-family: var(--mh-admin-font);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 17px; font-weight: 700; color: #1c1917; margin: 0;">Đón Tiếp Khách Phát Sinh (Walk-in)</h3>
            <button type="button" onclick="closeWalkInModal()" style="background: none; border: none; font-size: 20px; color: #78716c; cursor: pointer;">&times;</button>
        </div>

        @if($activeWedding)
            <form method="POST" action="{{ route('admin.guests.checkin.walk_in', $activeWedding->id) }}">
                @csrf
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Họ và Tên khách phát sinh: <span style="color: #dc2626;">*</span></label>
                        <input type="text" name="name" required placeholder="Ví dụ: Anh Nguyễn Văn An" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 14px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Danh xưng:</label>
                            <input type="text" name="salutation" placeholder="Bạn / Anh / Chị" value="Bạn" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Số điện thoại:</label>
                            <input type="text" name="phone" placeholder="09xxxxxxx" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Số người đi cùng: <span style="color: #dc2626;">*</span></label>
                            <input type="number" name="attendees" value="1" min="1" max="20" required style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 14px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Nhóm quan khách:</label>
                            <input type="text" name="group_name" value="Khách phát sinh" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                        </div>
                    </div>

                    @if($cockpit)
                        <div>
                            <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Bàn tiệc xếp vào:</label>
                            <select name="table_id" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff; box-sizing: border-box;">
                                <option value="">— Chưa xếp bàn (Xếp sau) —</option>
                                @foreach($cockpit['tables'] as $tbl)
                                    <option value="{{ $tbl['id'] }}">
                                        {{ $tbl['name'] }} (Còn {{ $tbl['available_seats'] }}/{{ $tbl['capacity'] }} chỗ)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Ghi chú đón tiếp:</label>
                        <input type="text" name="note" placeholder="Ví dụ: Bạn của cô dâu chưa báo trước..." style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                    <button type="button" onclick="closeWalkInModal()" style="padding: 9px 16px; font-size: 13px; border-radius: 6px; background: #f5f5f4; color: #44403c; border: 1px solid #d6d3d1; cursor: pointer;">Hủy</button>
                    <button type="submit" style="padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #be185d; color: #ffffff; border: none; cursor: pointer;">Tạo &amp; Check-in Ngay</button>
                </div>
            </form>
        @endif
    </div>
</div>

<script>
    const activeWeddingId = {{ $activeWedding?->id ?: 'null' }};
    let searchDebounceTimer = null;

    // 1. Tìm Kiếm Nhanh / Quét Mã QR
    function handleQuickSearch(query) {
        clearTimeout(searchDebounceTimer);
        const trimmed = query.trim();
        const resultsEl = document.getElementById('quickSearchResults');

        if (!activeWeddingId || trimmed.length === 0) {
            resultsEl.innerHTML = '<div style="text-align: center; padding: 24px; color: #a8a29e; font-size: 13px;">Gõ từ khóa hoặc dán mã QR vào ô trên để tìm kiếm quan khách...</div>';
            return;
        }

        resultsEl.innerHTML = '<div style="text-align: center; padding: 20px; color: #78716c; font-size: 13px;">Đang tìm kiếm quan khách...</div>';

        searchDebounceTimer = setTimeout(() => {
            fetch(`/admin/guests/checkin/weddings/${activeWeddingId}/search?q=${encodeURIComponent(trimmed)}`, {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (!data.guests || data.guests.length === 0) {
                    resultsEl.innerHTML = '<div style="text-align: center; padding: 20px; color: #dc2626; font-size: 13px;">Không tìm thấy khách nào với từ khóa này.</div>';
                    return;
                }

                let html = '';
                data.guests.forEach(g => {
                    const statusBadge = g.is_checked_in
                        ? `<span style="background: #ecfdf5; color: #047857; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600;">Đã đón lúc ${g.checked_in_time}</span>`
                        : `<span style="background: #f5f5f4; color: #78716c; padding: 2px 6px; border-radius: 4px; font-size: 11px; font-weight: 600;">Chưa đến</span>`;

                    const actionBtn = !g.is_checked_in
                        ? `<button type="button" onclick="quickCheckIn(${g.id}, ${g.expected_attendees})" style="padding: 5px 12px; font-size: 12px; font-weight: 600; border-radius: 6px; background: #059669; color: #ffffff; border: none; cursor: pointer;">Đón Ngay (${g.expected_attendees})</button>`
                        : `<button type="button" onclick="cancelCheckIn(${g.id})" style="padding: 5px 10px; font-size: 12px; border-radius: 6px; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; cursor: pointer;">Hủy Check-in</button>`;

                    html += `
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border: 1px solid #e7e5e4; border-radius: 8px; background: #ffffff;">
                            <div>
                                <div style="font-weight: 700; color: #1c1917; font-size: 13px;">${escapeHtml(g.name)} ${statusBadge}</div>
                                <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                                    ${escapeHtml(g.salutation)} • Bàn: <strong>${escapeHtml(g.table_name)}</strong> • Dự kiến: <strong>${g.expected_attendees}</strong> người
                                </div>
                            </div>
                            <div style="display: flex; gap: 6px; align-items: center;">
                                ${actionBtn}
                                <button type="button" onclick="openAdjustModal(${g.id}, '${escapeJs(g.name)}', ${g.expected_attendees}, ${g.checked_in_attendees}, '${escapeJs(g.check_in_note || '')}', ${g.table_id || 'null'})" style="padding: 5px 8px; font-size: 12px; border-radius: 6px; background: #f5f5f4; color: #44403c; border: 1px solid #d6d3d1; cursor: pointer;">Sửa</button>
                            </div>
                        </div>
                    `;
                });
                resultsEl.innerHTML = html;
            })
            .catch(err => {
                resultsEl.innerHTML = '<div style="text-align: center; padding: 20px; color: #dc2626; font-size: 13px;">Lỗi kết nối khi tìm kiếm.</div>';
            });
        }, 250);
    }

    // 2. Điểm danh nhanh 1 chạm
    function quickCheckIn(guestId, attendees) {
        fetch(`/admin/guests/checkin/guests/${guestId}/checkin`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ attendees: attendees })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                refreshLiveStats();
                const input = document.getElementById('quickLookupInput');
                if (input && input.value.trim().length > 0) {
                    handleQuickSearch(input.value);
                } else {
                    location.reload();
                }
            } else {
                alert(data.message || 'Có lỗi xảy ra.');
            }
        })
        .catch(err => alert('Lỗi mạng khi điểm danh quan khách.'));
    }

    // 3. Hủy điểm danh
    function cancelCheckIn(guestId) {
        if (!confirm('Bạn có chắc chắn muốn hủy điểm danh cho quan khách này?')) {
            return;
        }

        fetch(`/admin/guests/checkin/guests/${guestId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                refreshLiveStats();
                const input = document.getElementById('quickLookupInput');
                if (input && input.value.trim().length > 0) {
                    handleQuickSearch(input.value);
                } else {
                    location.reload();
                }
            } else {
                alert(data.message || 'Có lỗi xảy ra.');
            }
        })
        .catch(err => alert('Lỗi mạng khi hủy điểm danh.'));
    }

    // 4. Mở Modal Điều chỉnh
    function openAdjustModal(id, name, expected, actual, note, tableId) {
        document.getElementById('formAdjust').action = `/admin/guests/checkin/guests/${id}/checkin`;
        document.getElementById('modalGuestName').textContent = name;
        document.getElementById('modalGuestExpected').textContent = `Sĩ số dự kiến: ${expected} người`;
        document.getElementById('modalAttendeesInput').value = actual || expected || 1;
        document.getElementById('modalNoteInput').value = note || '';

        const tableSelect = document.getElementById('modalTableSelect');
        if (tableSelect) {
            tableSelect.value = tableId || '';
        }

        document.getElementById('modalAdjust').style.display = 'flex';
    }

    function closeAdjustModal() {
        document.getElementById('modalAdjust').style.display = 'none';
    }

    // 5. Mở Modal Walk-in
    function openWalkInModal() {
        document.getElementById('modalWalkIn').style.display = 'flex';
    }

    function closeWalkInModal() {
        document.getElementById('modalWalkIn').style.display = 'none';
    }

    // 6. Live Polling Cập Nhật Số Liệu
    function refreshLiveStats() {
        if (!activeWeddingId) return;

        const btn = document.getElementById('btnRefreshStats');
        if (btn) btn.style.opacity = '0.5';

        fetch(`/admin/guests/checkin/weddings/${activeWeddingId}/live-stats`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (btn) btn.style.opacity = '1';
            if (data.success && data.stats) {
                const s = data.stats;
                const setEl = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

                setEl('statTotalExpected', `${s.total_expected_attendees} người`);
                setEl('statTotalGuests', s.total_guests);
                setEl('statCheckedInAttendees', `${s.checked_in_attendees} người`);
                setEl('statCheckedInGuests', s.checked_in_guests);
                setEl('statTurnoutRate', `${s.turnout_rate}%`);
                setEl('statNotCheckedInAttendees', `${s.not_checked_in_attendees} người`);
                setEl('statNotCheckedInGuests', s.not_checked_in_guests);
                setEl('statPartiallyCheckedIn', `${s.partially_checked_in_guests} nhóm`);
                setEl('statWalkIn', `${s.walk_in_guests} khách`);
                setEl('progressLabel', `${s.turnout_rate}% Sĩ Số Hoàn Thành`);

                const pBar = document.getElementById('progressBar');
                if (pBar) pBar.style.width = `${Math.min(100, s.turnout_rate)}%`;

                // Cập nhật timeline
                if (data.timeline) {
                    const tContainer = document.getElementById('timelineContainer');
                    if (tContainer) {
                        let tHtml = '';
                        data.timeline.forEach(slot => {
                            tHtml += `
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <div style="display: flex; justify-content: space-between; font-size: 12px;">
                                        <span style="font-weight: 600; color: #44403c;">${slot.label}</span>
                                        <span style="color: #78716c;"><strong>${slot.count}</strong> lượt (${slot.attendees} người)</span>
                                    </div>
                                    <div style="height: 7px; background: #f5f5f4; border-radius: 4px; overflow: hidden;">
                                        <div style="height: 100%; background: #059669; width: ${slot.percentage}%; border-radius: 4px;"></div>
                                    </div>
                                </div>
                            `;
                        });
                        tContainer.innerHTML = tHtml;
                    }
                }
            }
        })
        .catch(err => {
            if (btn) btn.style.opacity = '1';
        });
    }

    // Auto poll mỗi 15 giây
    if (activeWeddingId) {
        setInterval(refreshLiveStats, 15000);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function escapeJs(str) {
        if (!str) return '';
        return str.toString().replace(/'/g, "\\'").replace(/"/g, '\\"');
    }
</script>
@endsection

