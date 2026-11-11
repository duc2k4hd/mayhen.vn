@extends('admin.layouts.master')

@section('title', 'Trung Tâm Điều Phối Sơ Đồ Bàn Tiệc & Chỗ Ngồi')

@section('content')
<div class="mayhen_admin_seating_container" style="display: flex; flex-direction: column; gap: 24px;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #78716c; margin-bottom: 6px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Tổng quan</a>
                <span>/</span>
                <a href="{{ route('admin.guests.index') }}" style="color: inherit; text-decoration: none;">Quản lý khách mời</a>
                <span>/</span>
                <span style="color: #1c1917; font-weight: 600;">Sơ đồ bàn tiệc (Seating Plan)</span>
            </div>
            <h1 style="font-size: 24px; font-weight: 700; color: #1c1917; margin: 0; line-height: 1.2;">Trung Tâm Điều Phối Sơ Đồ Bàn Tiệc &amp; Chỗ Ngồi</h1>
            <p style="font-size: 14px; color: #78716c; margin: 6px 0 0 0;">Quản lý sức chứa, phân bổ quan khách theo sảnh tiệc, kiểm soát quá tải và phát hiện bàn tiệc lệch thế hệ tự động.</p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            @if($selectedWedding)
                <a href="{{ route('admin.guests.seating.export', $selectedWedding->id) }}" class="mayhen_admin_btn" style="padding: 9px 16px; font-size: 13px; border-radius: 8px; background: #ffffff; border: 1px solid #d6d3d1; color: #44403c; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Xuất Sơ Đồ CSV
                </a>
                <button type="button" onclick="openCreateTableModal()" class="mayhen_admin_btn" style="padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 8px; background: #292524; color: #ffffff; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Thêm Bàn Tiệc Mới
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

    <!-- Bộ Chọn Đám Cưới -->
    <div class="mayhen_admin_card" style="padding: 14px 20px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
        <form method="GET" action="{{ route('admin.guests.seating.index') }}" style="display: flex; align-items: center; gap: 12px; flex: 1;">
            <label style="font-size: 13px; font-weight: 600; color: #44403c; white-space: nowrap;">Đang xem sơ đồ đám cưới:</label>
            <select name="wedding_id" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff; max-width: 420px; width: 100%;">
                @foreach($weddings as $w)
                    <option value="{{ $w->id }}" {{ $selectedWedding?->id == $w->id ? 'selected' : '' }}>
                        #{{ $w->id }} — {{ $w->groom_name }} &amp; {{ $w->bride_name }} ({{ $w->wedding_date ? $w->wedding_date->format('d/m/Y') : 'Chưa đặt ngày' }})
                    </option>
                @endforeach
            </select>
        </form>

        @if($selectedWedding)
            <div style="font-size: 12px; color: #78716c;">
                Chủ tiệc: <strong>{{ $selectedWedding->user?->name ?? 'Vãng lai' }}</strong> • {{ $selectedWedding->user?->phone ?? '—' }}
            </div>
        @endif
    </div>

    @if($selectedWedding && $health)
        <!-- KPI Thẻ Sức Khỏe Bàn Tiệc (Capacity Health Dashboard) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 14px;">
            <!-- Health Score Card -->
            <div class="mayhen_admin_card" style="padding: 18px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
                <div style="font-size: 11px; text-transform: uppercase; color: #78716c; font-weight: 600; letter-spacing: 0.5px;">Điểm Sức Khỏe Sơ Đồ Bàn</div>
                <div style="display: flex; align-items: baseline; gap: 8px; margin-top: 4px;">
                    <div style="font-size: 28px; font-weight: 700; color: {{ $health['health_score'] >= 85 ? '#15803d' : ($health['health_score'] >= 60 ? '#b45309' : '#dc2626') }};">
                        {{ $health['health_score'] }}<span style="font-size: 16px;">/100</span>
                    </div>
                </div>
                <div style="font-size: 12px; font-weight: 600; color: {{ $health['health_score'] >= 85 ? '#15803d' : ($health['health_score'] >= 60 ? '#b45309' : '#dc2626') }}; margin-top: 2px;">
                    {{ $health['status_label'] }}
                </div>
            </div>

            <!-- Total Tables Card -->
            <div class="mayhen_admin_card" style="padding: 18px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
                <div style="font-size: 11px; text-transform: uppercase; color: #78716c; font-weight: 600; letter-spacing: 0.5px;">Tổng Số Bàn Tiệc</div>
                <div style="font-size: 28px; font-weight: 700; color: #1c1917; margin-top: 4px;">{{ $health['metrics']['total_tables'] }}</div>
                <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                    Sức chứa toàn sảnh: <strong>{{ $health['metrics']['total_capacity'] }} ghế</strong>
                </div>
            </div>

            <!-- Assigned Seats Card -->
            <div class="mayhen_admin_card" style="padding: 18px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
                <div style="font-size: 11px; text-transform: uppercase; color: #047857; font-weight: 600; letter-spacing: 0.5px;">Khách Đã Xếp Bàn</div>
                <div style="font-size: 28px; font-weight: 700; color: #047857; margin-top: 4px;">{{ $health['metrics']['total_assigned'] }}</div>
                <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                    Tỷ lệ lấp đầy: <strong>{{ $health['metrics']['overall_occupancy_percent'] }}%</strong>
                </div>
            </div>

            <!-- Unassigned Guests Card -->
            <div class="mayhen_admin_card" style="padding: 18px; border-radius: 10px; background: {{ $health['metrics']['total_unassigned'] > 0 ? '#fffbeb' : '#ffffff' }}; border: 1px solid {{ $health['metrics']['total_unassigned'] > 0 ? '#fde68a' : '#e7e5e4' }};">
                <div style="font-size: 11px; text-transform: uppercase; color: {{ $health['metrics']['total_unassigned'] > 0 ? '#b45309' : '#78716c' }}; font-weight: 600; letter-spacing: 0.5px;">Chưa Xếp Chỗ</div>
                <div style="font-size: 28px; font-weight: 700; color: {{ $health['metrics']['total_unassigned'] > 0 ? '#b45309' : '#1c1917' }}; margin-top: 4px;">{{ $health['metrics']['total_unassigned'] }}</div>
                <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                    Cần xếp thêm vào bàn trống
                </div>
            </div>

            <!-- Overfilled Tables Card -->
            <div class="mayhen_admin_card" style="padding: 18px; border-radius: 10px; background: {{ $health['metrics']['overfilled_tables_count'] > 0 ? '#fef2f2' : '#ffffff' }}; border: 1px solid {{ $health['metrics']['overfilled_tables_count'] > 0 ? '#fecaca' : '#e7e5e4' }};">
                <div style="font-size: 11px; text-transform: uppercase; color: {{ $health['metrics']['overfilled_tables_count'] > 0 ? '#dc2626' : '#78716c' }}; font-weight: 600; letter-spacing: 0.5px;">Bàn Quá Tải Ghế</div>
                <div style="font-size: 28px; font-weight: 700; color: {{ $health['metrics']['overfilled_tables_count'] > 0 ? '#dc2626' : '#1c1917' }}; margin-top: 4px;">{{ $health['metrics']['overfilled_tables_count'] }}</div>
                <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                    Bàn quá thưa: {{ $health['metrics']['underfilled_tables_count'] }}
                </div>
            </div>
        </div>

        @if(!empty($health['issues']))
            <!-- Cảnh Báo Sức Chứa & Xung Đột Thế Hệ -->
            <div style="background: #fffbeb; border: 1px solid #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px; padding: 14px 18px;">
                <div style="font-weight: 700; font-size: 13px; color: #92400e; margin-bottom: 6px;">Phát Hiện Cảnh Báo Cần Tinh Chỉnh Sơ Đồ:</div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    @foreach($health['issues'] as $issue)
                        <div style="font-size: 12px; color: #78350f; display: flex; align-items: flex-start; gap: 6px;">
                            <span style="font-weight: 700;">•</span>
                            <div>
                                <strong>{{ $issue['table_name'] }}:</strong> {{ $issue['message'] }}
                                <span style="color: #92400e; font-style: italic;">({{ $issue['suggestion'] }})</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- BỐ CỤC 2 CỘT: KHÁCH CHƯA XẾP (TRÁI) & SƠ ĐỒ BÀN THEO SẢNH (PHẢI) -->
        <div style="display: grid; grid-template-columns: 340px 1fr; gap: 20px; align-items: flex-start;">
            
            <!-- CỘT TRÁI: DANH SÁCH KHÁCH CHƯA XẾP CHỖ -->
            <div class="mayhen_admin_card" style="border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4; display: flex; flex-direction: column; max-height: 800px;">
                <div style="padding: 16px; border-bottom: 1px solid #e7e5e4;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h3 style="margin: 0; font-size: 14px; font-weight: 700; color: #1c1917;">Khách Chưa Xếp Bàn ({{ $unassigned_guests->count() }})</h3>
                        <span style="font-size: 11px; background: #fef3c7; color: #b45309; padding: 2px 6px; border-radius: 4px; font-weight: 600;">
                            {{ $health['metrics']['total_unassigned'] }} suất
                        </span>
                    </div>

                    <!-- Input Tìm Khách -->
                    <input type="text" id="guestSearchInput" onkeyup="filterUnassignedGuests()" placeholder="Tìm theo tên khách, nhóm..." style="width: 100%; padding: 8px 10px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 12px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>

                <!-- Form Gán Bàn Nhanh -->
                <form id="bulkAssignForm" method="POST" action="" style="display: flex; flex-direction: column; flex: 1; overflow: hidden;">
                    @csrf
                    <div style="padding: 10px 16px; background: #fafaf9; border-bottom: 1px solid #e7e5e4; display: flex; gap: 8px; align-items: center;">
                        <select id="targetTableSelect" required style="flex: 1; padding: 6px 8px; border: 1px solid #d6d3d1; border-radius: 4px; font-size: 12px; font-family: var(--mh-admin-font); background: #ffffff;">
                            <option value="">-- Chọn bàn đích để xếp --</option>
                            @foreach($tables as $tbl)
                                <option value="{{ $tbl->id }}">
                                    {{ $tbl->name }} (Còn {{ $tbl->remaining_seats }}/{{ $tbl->capacity }} ghế)
                                </option>
                            @endforeach
                        </select>
                        <button type="button" onclick="submitBulkAssign()" style="padding: 6px 12px; font-size: 12px; font-weight: 600; border-radius: 4px; background: #292524; color: #ffffff; border: none; cursor: pointer; white-space: nowrap;">
                            Xếp Vào Bàn
                        </button>
                    </div>

                    <div id="unassignedListContainer" style="flex: 1; overflow-y: auto; padding: 12px 16px; display: flex; flex-direction: column; gap: 8px;">
                        @forelse($unassigned_guests as $ug)
                            <label class="unassigned-item" data-name="{{ mb_strtolower($ug->name, 'UTF-8') }}" data-group="{{ mb_strtolower((string) $ug->group_name, 'UTF-8') }}" style="display: flex; align-items: flex-start; gap: 10px; padding: 8px 10px; border: 1px solid #f5f5f4; border-radius: 6px; background: #fafaf9; cursor: pointer; transition: background 0.15s ease;">
                                <input type="checkbox" name="guest_ids[]" value="{{ $ug->id }}" style="margin-top: 3px; accent-color: #292524;">
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-weight: 600; font-size: 13px; color: #1c1917; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $ug->name }}
                                    </div>
                                    <div style="font-size: 11px; color: #78716c; display: flex; gap: 6px; margin-top: 2px;">
                                        <span>{{ $ug->group_name ?: 'Khách chung' }}</span>
                                        <span>•</span>
                                        <span style="font-weight: 600; color: #047857;">{{ $ug->expected_attendees ?: 1 }} suất</span>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div style="padding: 30px 10px; text-align: center; color: #059669; font-size: 13px; font-weight: 600;">
                                Toàn bộ quan khách đã được xếp bàn đầy đủ!
                            </div>
                        @endforelse
                    </div>
                </form>
            </div>

            <!-- CỘT PHẢI: LƯỚI BÀN TIỆC THEO KHU VỰC / SẢNH -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                @forelse($tables_by_zone as $zoneName => $zoneTables)
                    <div class="mayhen_admin_card" style="padding: 20px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid #f5f5f4; padding-bottom: 10px;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #292524;"></span>
                                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1c1917;">{{ $zoneName }}</h3>
                                <span style="font-size: 12px; color: #78716c;">({{ count($zoneTables) }} bàn)</span>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                            @foreach($zoneTables as $table)
                                @php
                                    $isOver = $table->isOverCapacity();
                                    $isFull = $table->isFull();
                                    $pct = min(100, round(($table->assigned_attendees_count / max(1, $table->capacity)) * 100));
                                    $barColor = $isOver ? '#ef4444' : ($isFull ? '#10b981' : '#f59e0b');
                                @endphp
                                <div style="border: 1px solid {{ $isOver ? '#fca5a5' : '#e7e5e4' }}; border-radius: 8px; background: {{ $isOver ? '#fffbfa' : '#ffffff' }}; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                    <!-- Header Bàn -->
                                    <div style="padding: 12px 14px; background: #fafaf9; border-bottom: 1px solid #f5f5f4; display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <div style="font-weight: 700; font-size: 14px; color: #1c1917;">{{ $table->name }}</div>
                                            <div style="font-size: 11px; color: #78716c; margin-top: 1px;">
                                                {{ $table->shape_label }} • Sức chứa: <strong>{{ $table->capacity }} ghế</strong>
                                            </div>
                                        </div>

                                        <div style="display: flex; gap: 4px; align-items: center;">
                                            <button type="button" onclick="openEditTableModal({{ $table->id }}, '{{ addslashes($table->name) }}', '{{ $table->shape }}', {{ $table->capacity }}, '{{ addslashes($table->zone ?? '') }}', '{{ addslashes($table->notes ?? '') }}')" title="Sửa bàn" style="background: none; border: none; padding: 4px; color: #78716c; cursor: pointer;">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                            </button>
                                            <form method="POST" action="{{ route('admin.guests.seating.tables.destroy', [$selectedWedding->id, $table->id]) }}" onsubmit="return confirm('Xóa bàn này? Khách trong bàn sẽ chuyển về danh sách chưa xếp.');" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Xóa bàn" style="background: none; border: none; padding: 4px; color: #ef4444; cursor: pointer;">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- Progress Lấp Đầy -->
                                    <div style="padding: 10px 14px 6px 14px;">
                                        <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                                            <span style="font-weight: 600; color: {{ $isOver ? '#dc2626' : '#1c1917' }};">
                                                Đã ngồi: {{ $table->assigned_attendees_count }}/{{ $table->capacity }}
                                            </span>
                                            <span style="font-size: 11px; color: {{ $isOver ? '#dc2626' : '#78716c' }};">
                                                {{ $isOver ? 'Quá ' . ($table->assigned_attendees_count - $table->capacity) . ' suất' : 'Còn ' . $table->remaining_seats . ' chỗ' }}
                                            </span>
                                        </div>
                                        <div style="width: 100%; height: 5px; background: #e7e5e4; border-radius: 999px; overflow: hidden;">
                                            <div style="height: 100%; width: {{ $pct }}%; background: {{ $barColor }}; border-radius: 999px;"></div>
                                        </div>
                                    </div>

                                    <!-- Danh Sách Khách Trong Bàn -->
                                    <div style="padding: 10px 14px; flex: 1; display: flex; flex-direction: column; gap: 6px; max-height: 220px; overflow-y: auto;">
                                        @forelse($table->guests as $guest)
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 5px 8px; background: #fafaf9; border-radius: 4px; font-size: 12px;">
                                                <div style="flex: 1; min-width: 0;">
                                                    <span style="font-weight: 600; color: #1c1917;">{{ $guest->name }}</span>
                                                    <span style="color: #78716c; font-size: 11px;">({{ $guest->expected_attendees ?: 1 }} suất)</span>
                                                </div>
                                                <form method="POST" action="{{ route('admin.guests.seating.unassign', [$selectedWedding->id, $guest->id]) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" title="Gỡ khách khỏi bàn" style="background: none; border: none; padding: 2px 4px; color: #a8a29e; cursor: pointer; font-size: 14px; line-height: 1;">
                                                        &times;
                                                    </button>
                                                </form>
                                            </div>
                                        @empty
                                            <div style="font-size: 12px; color: #a8a29e; font-style: italic; padding: 12px 0; text-align: center;">
                                                Bàn trống chưa có khách
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="mayhen_admin_card" style="padding: 40px; text-align: center; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#a8a29e" stroke-width="1.5" style="margin-bottom: 12px;"><rect x="3" y="3" width="18" height="18" rx="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                        <div style="font-size: 15px; font-weight: 700; color: #1c1917;">Đám cưới này chưa có bàn tiệc nào</div>
                        <div style="font-size: 13px; color: #78716c; margin-top: 4px;">Bấm nút "Thêm Bàn Tiệc Mới" ở góc phải trên để bắt đầu sắp xếp sơ đồ.</div>
                    </div>
                @endforelse
            </div>

        </div>
    @endif
</div>

<!-- Modal Thêm Bàn Mới -->
@if($selectedWedding)
<div id="createTableModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: 12px; max-width: 460px; width: 90%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); font-family: var(--mh-admin-font); box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid #f5f5f4; padding-bottom: 12px;">
            <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #1c1917;">Thêm Bàn Tiệc Mới</h3>
            <button type="button" onclick="closeCreateTableModal()" style="background: none; border: none; font-size: 20px; color: #a8a29e; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.guests.seating.tables.store', $selectedWedding->id) }}">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 4px;">Tên hoặc số bàn <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="name" required placeholder="ví dụ: Bàn 01 hoặc Bàn VIP Nhà Gái" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 4px;">Sức chứa (Ghế) <span style="color: #ef4444;">*</span></label>
                        <input type="number" name="capacity" value="10" min="1" max="50" required style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 4px;">Hình dáng bàn</label>
                        <select name="shape" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff; box-sizing: border-box;">
                            <option value="round">Bàn Tròn (10 ghế)</option>
                            <option value="long">Bàn Dài (Chữ nhật)</option>
                            <option value="square">Bàn Vuông</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 4px;">Khu vực / Sảnh tiệc</label>
                    <input type="text" name="zone" value="Sảnh Tiệc Chính" placeholder="ví dụ: Sảnh A, Tầng 2, Sân Vườn..." style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 4px;">Ghi chú bàn</label>
                    <input type="text" name="notes" placeholder="ví dụ: Gần lối đi cô dâu chú rể..." style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 14px; border-top: 1px solid #f5f5f4;">
                <button type="button" onclick="closeCreateTableModal()" style="padding: 9px 16px; font-size: 13px; border-radius: 6px; background: #f5f5f4; border: 1px solid #d6d3d1; color: #44403c; cursor: pointer;">Hủy bỏ</button>
                <button type="submit" style="padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #292524; border: none; color: #ffffff; cursor: pointer;">Tạo Bàn Tiệc</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Sửa Bàn -->
<div id="editTableModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: 12px; max-width: 460px; width: 90%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); font-family: var(--mh-admin-font); box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid #f5f5f4; padding-bottom: 12px;">
            <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #1c1917;">Chỉnh Sửa Bàn Tiệc</h3>
            <button type="button" onclick="closeEditTableModal()" style="background: none; border: none; font-size: 20px; color: #a8a29e; cursor: pointer;">&times;</button>
        </div>

        <form id="editTableForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 4px;">Tên hoặc số bàn <span style="color: #ef4444;">*</span></label>
                    <input type="text" id="editTableName" name="name" required style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 4px;">Sức chứa (Ghế) <span style="color: #ef4444;">*</span></label>
                        <input type="number" id="editTableCapacity" name="capacity" min="1" max="50" required style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 4px;">Hình dáng bàn</label>
                        <select id="editTableShape" name="shape" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff; box-sizing: border-box;">
                            <option value="round">Bàn Tròn</option>
                            <option value="long">Bàn Dài</option>
                            <option value="square">Bàn Vuông</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 4px;">Khu vực / Sảnh tiệc</label>
                    <input type="text" id="editTableZone" name="zone" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 4px;">Ghi chú</label>
                    <input type="text" id="editTableNotes" name="notes" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 14px; border-top: 1px solid #f5f5f4;">
                <button type="button" onclick="closeEditTableModal()" style="padding: 9px 16px; font-size: 13px; border-radius: 6px; background: #f5f5f4; border: 1px solid #d6d3d1; color: #44403c; cursor: pointer;">Hủy bỏ</button>
                <button type="submit" style="padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #292524; border: none; color: #ffffff; cursor: pointer;">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
    function openCreateTableModal() {
        document.getElementById('createTableModal').style.display = 'flex';
    }
    function closeCreateTableModal() {
        document.getElementById('createTableModal').style.display = 'none';
    }

    function openEditTableModal(tableId, name, shape, capacity, zone, notes) {
        document.getElementById('editTableName').value = name;
        document.getElementById('editTableShape').value = shape;
        document.getElementById('editTableCapacity').value = capacity;
        document.getElementById('editTableZone').value = zone;
        document.getElementById('editTableNotes').value = notes;

        var form = document.getElementById('editTableForm');
        form.action = "{{ url('admin/guests/seating/weddings/' . ($selectedWedding?->id ?? 0) . '/tables') }}/" + tableId;

        document.getElementById('editTableModal').style.display = 'flex';
    }
    function closeEditTableModal() {
        document.getElementById('editTableModal').style.display = 'none';
    }

    function filterUnassignedGuests() {
        var query = document.getElementById('guestSearchInput').value.toLowerCase();
        var items = document.querySelectorAll('.unassigned-item');
        items.forEach(function(el) {
            var name = el.getAttribute('data-name');
            var group = el.getAttribute('data-group');
            if (name.indexOf(query) !== -1 || group.indexOf(query) !== -1) {
                el.style.display = 'flex';
            } else {
                el.style.display = 'none';
            }
        });
    }

    function submitBulkAssign() {
        var tableId = document.getElementById('targetTableSelect').value;
        if (!tableId) {
            alert('Vui lòng chọn bàn đích để xếp khách.');
            return;
        }

        var checked = document.querySelectorAll('input[name="guest_ids[]"]:checked');
        if (checked.length === 0) {
            alert('Vui lòng tích chọn ít nhất 1 khách mời cần xếp vào bàn.');
            return;
        }

        var form = document.getElementById('bulkAssignForm');
        form.action = "{{ url('admin/guests/seating/weddings/' . ($selectedWedding?->id ?? 0) . '/tables') }}/" + tableId + "/assign";
        form.submit();
    }

    window.addEventListener('click', function(e) {
        if (e.target === document.getElementById('createTableModal')) closeCreateTableModal();
        if (e.target === document.getElementById('editTableModal')) closeEditTableModal();
    });
</script>
@endsection

