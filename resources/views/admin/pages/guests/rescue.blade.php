@extends('admin.layouts.master')

@section('title', 'Rescue Cockpit Ngày Cưới')
@section('page_title', '🚨 Wedding Day Rescue Cockpit (Sự Cố & Sắp Bàn Khẩn Cấp)')

@section('content')
    <!-- Top Wedding Selector Bar -->
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 18px 20px; background: #FFFDF9; border: 1px solid var(--mh-admin-border);">
        <form action="{{ route('admin.guests.rescue') }}" method="GET" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 280px;">
                <span style="font-size: 1.4rem;">🎯</span>
                <div>
                    <strong style="font-size: 0.9375rem; color: var(--mh-admin-charcoal);">Đám Cưới Cần Cứu Nguy / Điều Phối:</strong>
                    <div style="font-size: 0.8125rem; color: var(--mh-admin-muted);">
                        @if ($todayWeddings->isNotEmpty())
                            Có <strong style="color: #0D47A1;">{{ $todayWeddings->count() }}</strong> đám cưới diễn ra HÔM NAY.
                        @else
                            Hôm nay không có lịch cưới. Đang chọn đám cưới gần nhất để diễn tập/xử lý.
                        @endif
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <select name="wedding_id" class="mayhen_admin_form_select" style="min-width: 260px;" onchange="this.form.submit()">
                    @if ($todayWeddings->isNotEmpty())
                        <optgroup label="🌟 ĐÁM CƯỚI HÔM NAY">
                            @foreach ($todayWeddings as $tw)
                                <option value="{{ $tw->id }}" {{ $activeWedding && $activeWedding->id === $tw->id ? 'selected' : '' }}>
                                    Hôm nay: {{ $tw->groom_name }} & {{ $tw->bride_name }} ({{ $tw->guests_count }} khách)
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                    <optgroup label="📋 TOÀN BỘ ĐÁM CƯỚI GẦN ĐÂY">
                        @foreach ($allWeddings as $w)
                            <option value="{{ $w->id }}" {{ $activeWedding && $activeWedding->id === $w->id ? 'selected' : '' }}>
                                {{ $w->groom_name }} & {{ $w->bride_name }} ({{ $w->wedding_date ? $w->wedding_date->format('d/m/Y') : 'Chưa định' }})
                            </option>
                        @endforeach
                    </optgroup>
                </select>

                <noscript>
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn_sm">Chọn</button>
                </noscript>

                <a href="{{ route('admin.guests.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                    &larr; Về Guest CRM
                </a>
            </div>
        </form>
    </div>

    @if ($activeWedding)
        <!-- Overcapacity Alert Banner -->
        @if ($isOverCapacity)
            <div class="mayhen_admin_card" style="background: #FFEBEE; border-left: 4px solid #C62828; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 1.5rem;">⚠️</span>
                    <div>
                        <strong style="font-size: 0.95rem; color: #C62828;">CẢNH BÁO QUÁ TẢI SẢNH TIỆC (OVERCAPACITY)</strong>
                        <div style="font-size: 0.8125rem; color: #5F2120; margin-top: 2px;">
                            Số khách đã xếp bàn ({{ $assignedCount }} người) đã vượt quá tổng sức chứa thực tế của các bàn tiệc ({{ $totalCapacity }} ghế). Hãy tạo thêm bàn dự phòng ngay!
                        </div>
                    </div>
                </div>
                <a href="#quick-table-form" class="mayhen_admin_btn mayhen_admin_btn--danger mayhen_admin_btn_sm">
                    + Tạo Bàn Dự Phòng Khẩn Cấp &darr;
                </a>
            </div>
        @endif

        <!-- Sảnh Tiệc & Capacity Metrics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 16px 20px;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 4px;">
                    Cặp Đôi & Địa Điểm
                </div>
                <div style="font-size: 1.15rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                    {{ $activeWedding->groom_name }} & {{ $activeWedding->bride_name }}
                </div>
                <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">
                    📍 {{ $activeWedding->venue_name ?? 'Chưa cập nhật sảnh' }}
                </div>
            </div>

            <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 16px 20px;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 4px;">
                    Tổng Bàn & Sức Chứa
                </div>
                <div style="font-size: 1.4rem; font-weight: 700; color: var(--mh-admin-gold);">
                    {{ $tables->count() }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--mh-admin-muted);">bàn ({{ $totalCapacity }} ghế)</span>
                </div>
                <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">
                    Đã xếp: <strong>{{ $assignedCount }}</strong> ghế
                </div>
            </div>

            <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 16px 20px; border-left: 4px solid #C62828;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #C62828; margin-bottom: 4px;">
                    Khách Walk-in / Chưa Bàn
                </div>
                <div style="font-size: 1.4rem; font-weight: 700; color: #C62828;">
                    {{ $unassignedGuests->count() }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--mh-admin-muted);">người</span>
                </div>
                <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">
                    Cần phân bổ bàn ngay lập tức
                </div>
            </div>

            <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 16px 20px; border-left: 4px solid #2E7D32;">
                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #2E7D32; margin-bottom: 4px;">
                    Đã Điểm Danh Check-in
                </div>
                <div style="font-size: 1.4rem; font-weight: 700; color: #2E7D32;">
                    {{ $checkedInCount }} <span style="font-size: 0.9rem; font-weight: 500; color: var(--mh-admin-muted);">người</span>
                </div>
                <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">
                    Tại sảnh tiếp tân ngày cưới
                </div>
            </div>
        </div>

        <!-- 2 Columns Grid: Left Unassigned Queue | Right Tables & Emergency Form -->
        <div style="display: grid; grid-template-columns: 3fr 2fr; gap: 24px;">
            <!-- Left: Unassigned Walk-in Guests -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <span>🚨 Hàng Đợi Khách Walk-in / Chưa Có Bàn Tiệc ({{ $unassignedGuests->count() }})</span>
                    </div>
                </div>

                <div class="mayhen_admin_table_responsive">
                    <table class="mayhen_admin_table">
                        <thead>
                            <tr>
                                <th>Quan Khách</th>
                                <th>Nhóm & Trạng Thái</th>
                                <th>Xếp Bàn Tiệc Nhanh (1-Click)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($unassignedGuests as $guest)
                                <tr>
                                    <td>
                                        <strong>{{ $guest->name }}</strong><br>
                                        <span style="font-size: 0.75rem; color: #7A756E;">
                                            SĐT: {{ $guest->phone ?? 'Không có' }} &bull; Sĩ số: <strong>{{ $guest->actualAttendeesCount() }} người</strong>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="mayhen_admin_badge mayhen_admin_badge_neutral">{{ $guest->group_name ?? 'Khách chung' }}</span><br>
                                        @if ($guest->isCheckedIn())
                                            <span class="mayhen_admin_badge mayhen_admin_badge_success" style="margin-top: 4px; font-size: 0.7rem;">
                                                Đã đến lúc {{ $guest->checked_in_at->format('H:i') }}
                                            </span>
                                        @else
                                            <span class="mayhen_admin_badge mayhen_admin_badge_warning" style="margin-top: 4px; font-size: 0.7rem;">
                                                Chưa check-in
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($tables->isNotEmpty())
                                            <form action="{{ route('admin.guests.assign_table', $guest->id) }}" method="POST" style="display: flex; gap: 6px; align-items: center;">
                                                @csrf
                                                <select name="table_id" class="mayhen_admin_form_select" style="padding: 4px 8px; font-size: 0.8125rem; min-width: 160px;" required>
                                                    <option value="" disabled selected>-- Chọn bàn --</option>
                                                    @foreach ($tables as $t)
                                                        @php
                                                            $assigned = $t->guests->count();
                                                            $seatsLeft = max(0, $t->capacity - $assigned);
                                                        @endphp
                                                        <option value="{{ $t->id }}">
                                                            {{ $t->name }} (Còn {{ $seatsLeft }}/{{ $t->capacity }} chỗ)
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn_sm" style="white-space: nowrap;">
                                                    Gán Bàn
                                                </button>
                                            </form>
                                        @else
                                            <span style="font-size: 0.75rem; color: #C62828;">Chưa có bàn tiệc nào. Tạo bàn bên phải &rarr;</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align: center; padding: 40px; color: var(--mh-admin-muted);">
                                        <div style="font-size: 2rem; margin-bottom: 8px;">🎉</div>
                                        <strong>Tuyệt vời! Không có quan khách nào bị sót bàn.</strong><br>
                                        <span style="font-size: 0.8125rem;">Tất cả quan khách đã được phân bổ bàn tiệc chu đáo.</span>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right: Tables Status & Emergency Table Form -->
            <div>
                <!-- Emergency Table Quick Create Form -->
                <div class="mayhen_admin_card" id="quick-table-form" style="border: 2px dashed var(--mh-admin-gold); background: #FFFDF9;">
                    <div class="mayhen_admin_card_header">
                        <div class="mayhen_admin_card_title">
                            <span>⚡ Tạo Bàn Dự Phòng Khẩn Cấp</span>
                        </div>
                    </div>

                    <div class="mayhen_admin_card_body">
                        <form action="{{ route('admin.guests.emergency_table', $activeWedding->id) }}" method="POST">
                            @csrf
                            <div style="margin-bottom: 12px;">
                                <label for="name" class="mayhen_admin_form_label">Tên Bàn Tiệc Khẩn Cấp:</label>
                                <input type="text" id="name" name="name" class="mayhen_admin_form_input" placeholder="Ví dụ: Bàn Dự Phòng Sảnh B" value="Bàn Dự Phòng #{{ $tables->count() + 1 }}">
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                                <div>
                                    <label for="capacity" class="mayhen_admin_form_label">Số Ghế (Chỗ ngồi):</label>
                                    <input type="number" id="capacity" name="capacity" class="mayhen_admin_form_input" min="1" max="30" value="10" required>
                                </div>
                                <div>
                                    <label for="zone" class="mayhen_admin_form_label">Khu Vực / Sảnh:</label>
                                    <input type="text" id="zone" name="zone" class="mayhen_admin_form_input" placeholder="Ví dụ: VIP / Sân Vườn" value="Khu Dự Phòng">
                                </div>
                            </div>

                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="width: 100%;">
                                + Tạo Bàn Dự Phòng Ngay
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Existing Tables List -->
                <div class="mayhen_admin_card">
                    <div class="mayhen_admin_card_header">
                        <div class="mayhen_admin_card_title">
                            <span>🪑 Danh Sách Bàn Tiệc ({{ $tables->count() }})</span>
                        </div>
                    </div>

                    <div class="mayhen_admin_card_body" style="padding: 0;">
                        <div style="max-height: 400px; overflow-y: auto;">
                            @forelse ($tables as $t)
                                @php
                                    $assigned = $t->guests->count();
                                    $isFull = $assigned >= $t->capacity && $t->capacity > 0;
                                @endphp
                                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--mh-admin-border-subtle);">
                                    <div>
                                        <strong style="font-size: 0.875rem; color: var(--mh-admin-charcoal);">{{ $t->name }}</strong>
                                        <span style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-left: 6px;">({{ $t->zone ?? 'Chung' }})</span>
                                        <div style="font-size: 0.75rem; color: #7A756E; margin-top: 2px;">
                                            Sức chứa: {{ $t->capacity }} chỗ &bull; Đang ngồi: <strong>{{ $assigned }}</strong> người
                                        </div>
                                    </div>
                                    <div>
                                        @if ($isFull)
                                            <span class="mayhen_admin_badge mayhen_admin_badge_danger">Đầy Chỗ</span>
                                        @else
                                            <span class="mayhen_admin_badge mayhen_admin_badge_success">Còn {{ $t->capacity - $assigned }} chỗ</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; padding: 30px; color: var(--mh-admin-muted); font-size: 0.8125rem;">
                                    Chưa có bàn tiệc nào được thiết lập cho đám cưới này.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="mayhen_admin_card" style="text-align: center; padding: 60px 20px;">
            <div style="font-size: 2.5rem; margin-bottom: 12px;">🕊️</div>
            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 6px;">
                Không Tìm Thấy Đám Cưới Nào
            </h3>
            <p style="font-size: 0.875rem; color: var(--mh-admin-muted); margin-bottom: 20px;">
                Chưa có dữ liệu đám cưới nào trong hệ thống để thực hiện điều phối khẩn cấp.
            </p>
            <a href="{{ route('admin.guests.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                &larr; Về Danh Sách Khách Mời
            </a>
        </div>
    @endif
@endsection

