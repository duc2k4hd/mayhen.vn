@extends('admin.layouts.master')

@section('title', 'Sổ Mừng Cưới Kỹ Thuật Số & Quản Lý Quà Mừng')

@section('content')
<div class="mayhen_admin_gifts_container" style="display: flex; flex-direction: column; gap: 24px; font-family: var(--mh-admin-font);">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #78716c; margin-bottom: 6px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Tổng quan</a>
                <span>/</span>
                <span style="color: #1c1917; font-weight: 600;">Sổ mừng cưới &amp; Quà tặng</span>
            </div>
            <h1 style="font-size: 24px; font-weight: 700; color: #1c1917; margin: 0; line-height: 1.2;">Sổ Mừng Cưới Kỹ Thuật Số &amp; Quản Lý Quà Mừng</h1>
            <p style="font-size: 14px; color: #78716c; margin: 6px 0 0 0;">Theo dõi toàn bộ quà mừng cưới, phong bì tiền mặt, chuyển khoản VietQR, bảo vệ dữ liệu tài khoản và quản lý lời cảm ơn.</p>
        </div>

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('admin.gifts.export', ['wedding_id' => $activeWedding?->id, 'payment_method' => $filters['payment_method'], 'status' => $filters['status']]) }}" class="mayhen_admin_btn" style="padding: 9px 16px; font-size: 13px; border-radius: 8px; background: #ffffff; border: 1px solid #d6d3d1; color: #44403c; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Xuất Sổ Mừng CSV
            </a>
            <button type="button" onclick="openCreateGiftModal()" class="mayhen_admin_btn" style="padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 8px; background: #292524; color: #ffffff; border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Ghi Nhận Quà Mừng
            </button>
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
        <form method="GET" action="{{ route('admin.gifts.index') }}" style="display: flex; align-items: center; gap: 12px; flex: 1;">
            <label style="font-size: 13px; font-weight: 600; color: #44403c; white-space: nowrap;">Đang xem sổ mừng cưới:</label>
            <select name="wedding_id" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff; max-width: 450px; width: 100%;">
                <option value="">— Toàn bộ các tiệc cưới trên sàn —</option>
                @foreach($allWeddings as $w)
                    <option value="{{ $w->id }}" {{ $activeWedding?->id == $w->id ? 'selected' : '' }}>
                        #{{ $w->id }} — {{ $w->groom_name }} &amp; {{ $w->bride_name }} ({{ $w->wedding_date ? $w->wedding_date->format('d/m/Y') : 'Chưa đặt ngày' }})
                    </option>
                @endforeach
            </select>
        </form>

        @if($activeWedding)
            <div style="font-size: 12px; color: #78716c;">
                Chủ tiệc: <strong style="color: #1c1917;">{{ $activeWedding->user?->name ?? 'Vãng lai' }}</strong> • Ngày cưới: <strong style="color: #1c1917;">{{ $activeWedding->wedding_date ? $activeWedding->wedding_date->format('d/m/Y') : '—' }}</strong>
            </div>
        @endif
    </div>

    @if($bankInfo)
        <!-- Financial Privacy & Data Masking Shield Banner -->
        <div style="background: #fafaf9; border: 1px solid #e7e5e4; border-radius: 10px; padding: 14px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #f5f5f4; display: flex; align-items: center; justify-content: center; color: #78716c;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <div>
                    <div style="font-size: 13px; font-weight: 600; color: #1c1917;">
                        Tài khoản nhận quà mừng: {{ $bankInfo['bank_name'] }} • <span style="font-family: monospace; background: #e7e5e4; padding: 2px 6px; border-radius: 4px;">{{ $bankInfo['bank_account_number_masked'] }}</span>
                    </div>
                    <div style="font-size: 12px; color: #78716c;">
                        Chủ tài khoản: <strong>{{ $bankInfo['bank_account_name'] }}</strong> • Dữ liệu số tài khoản được mã hóa che chắn (Masked) tuân thủ bảo vệ quyền riêng tư cá nhân.
                    </div>
                </div>
            </div>
            <div style="font-size: 12px; color: #059669; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                Financial Privacy Shield Active
            </div>
        </div>
    @endif

    <!-- 4 KPI Tài Chính Quà Mừng Cưới -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px;">
        <!-- Tổng Tiền Mừng -->
        <div class="mayhen_admin_card" style="padding: 16px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
            <div style="font-size: 11px; text-transform: uppercase; color: #78716c; font-weight: 600; letter-spacing: 0.5px;">Tổng Giá Trị Mừng Cưới</div>
            <div style="font-size: 26px; font-weight: 700; color: #047857; margin-top: 4px;">
                {{ number_format($stats['total_amount'], 0, ',', '.') }} <span style="font-size: 14px; font-weight: 500; color: #059669;">đ</span>
            </div>
            <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                Tổng số lượt mừng: <strong>{{ number_format($stats['total_count']) }}</strong> lượt
            </div>
        </div>

        <!-- Tiền Mặt / Phong Bì -->
        <div class="mayhen_admin_card" style="padding: 16px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
            <div style="font-size: 11px; text-transform: uppercase; color: #b45309; font-weight: 600; letter-spacing: 0.5px;">Tiền Mặt / Phong Bì Trực Tiếp</div>
            <div style="font-size: 26px; font-weight: 700; color: #b45309; margin-top: 4px;">
                {{ number_format($stats['cash_amount'], 0, ',', '.') }} <span style="font-size: 14px; font-weight: 500; color: #d97706;">đ</span>
            </div>
            <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                Chiếm <strong>{{ number_format($stats['cash_count']) }}</strong> phong bì tại bàn tiếp tân
            </div>
        </div>

        <!-- Chuyển Khoản VietQR -->
        <div class="mayhen_admin_card" style="padding: 16px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
            <div style="font-size: 11px; text-transform: uppercase; color: #4338ca; font-weight: 600; letter-spacing: 0.5px;">Chuyển Khoản VietQR</div>
            <div style="font-size: 26px; font-weight: 700; color: #4338ca; margin-top: 4px;">
                {{ number_format($stats['transfer_amount'], 0, ',', '.') }} <span style="font-size: 14px; font-weight: 500; color: #6366f1;">đ</span>
            </div>
            <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                Ghi nhận <strong>{{ number_format($stats['transfer_count']) }}</strong> giao dịch số
            </div>
        </div>

        <!-- Tiến Độ Cảm Ơn -->
        <div class="mayhen_admin_card" style="padding: 16px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
            <div style="font-size: 11px; text-transform: uppercase; color: #be185d; font-weight: 600; letter-spacing: 0.5px;">Tiến Độ Gửi Lời Cảm Ơn</div>
            <div style="font-size: 26px; font-weight: 700; color: #be185d; margin-top: 4px;">
                {{ $stats['acknowledged_rate'] }}%
            </div>
            <div style="font-size: 12px; color: #78716c; margin-top: 2px;">
                Đã cảm ơn <strong>{{ $stats['acknowledged_count'] }}</strong> / {{ $stats['total_count'] }} người mừng
            </div>
        </div>
    </div>

    <!-- Thanh Lọc & Tìm Kiếm Quà Mừng -->
    <div class="mayhen_admin_card" style="padding: 16px 20px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
        <form method="GET" action="{{ route('admin.gifts.index') }}" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
            <input type="hidden" name="wedding_id" value="{{ $activeWedding?->id }}">

            <!-- Ô tìm kiếm -->
            <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Tìm theo tên người mừng, nhóm, ghi chú..." style="padding: 8px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); min-width: 240px; flex: 1;">

            <!-- Lọc Phương thức -->
            <select name="payment_method" style="padding: 8px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff;">
                <option value="">— Mọi Hình Thức Mừng —</option>
                <option value="cash" {{ $filters['payment_method'] === 'cash' ? 'selected' : '' }}>Tiền mặt / Phong bì</option>
                <option value="bank_transfer" {{ $filters['payment_method'] === 'bank_transfer' ? 'selected' : '' }}>Chuyển khoản VietQR</option>
                <option value="other" {{ $filters['payment_method'] === 'other' ? 'selected' : '' }}>Quà mừng hiện vật</option>
            </select>

            <!-- Lọc Trạng thái cảm ơn -->
            <select name="status" style="padding: 8px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff;">
                <option value="">— Mọi Trạng Thái Cảm Ơn —</option>
                <option value="acknowledged" {{ $filters['status'] === 'acknowledged' ? 'selected' : '' }}>Đã gửi lời cảm ơn</option>
                <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Chưa gửi lời cảm ơn</option>
            </select>

            <button type="submit" class="mayhen_admin_btn" style="padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #292524; color: #ffffff; border: none; cursor: pointer;">
                Lọc Sổ Mừng
            </button>

            @if(!empty($filters['q']) || !empty($filters['payment_method']) || !empty($filters['status']))
                <a href="{{ route('admin.gifts.index', ['wedding_id' => $activeWedding?->id]) }}" style="padding: 8px 14px; font-size: 13px; border-radius: 6px; background: #f5f5f4; color: #44403c; text-decoration: none; display: inline-flex; align-items: center;">
                    Đặt Lại
                </a>
            @endif
        </form>
    </div>

    <!-- Bảng Sổ Mừng Cưới Kỹ Thuật Số -->
    <div class="mayhen_admin_card" style="padding: 20px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4; display: flex; flex-direction: column; gap: 14px;">
        <!-- Batch Action Toolbar -->
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="font-size: 14px; font-weight: 700; color: #1c1917;">
                Danh Sách Quà Mừng &amp; Phong Bì ({{ $gifts->total() }} lượt)
            </div>

            <div id="batchActionGroup" style="display: none; align-items: center; gap: 8px;">
                <span style="font-size: 12px; color: #78716c;" id="selectedCountText">Đã chọn 0 mục</span>
                <button type="button" onclick="submitBatchAction('acknowledge')" class="mayhen_admin_btn" style="padding: 6px 12px; font-size: 12px; font-weight: 600; border-radius: 6px; background: #059669; color: #ffffff; border: none; cursor: pointer;">
                    Đánh Dấu Đã Cảm Ơn
                </button>
                <button type="button" onclick="submitBatchAction('delete')" class="mayhen_admin_btn" style="padding: 6px 12px; font-size: 12px; border-radius: 6px; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; cursor: pointer;">
                    Xóa Hàng Loạt
                </button>
            </div>
        </div>

        <form id="formBatchActions" method="POST" action="">
            @csrf
            <div style="overflow-x: auto; border: 1px solid #e7e5e4; border-radius: 8px;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                    <thead>
                        <tr style="background: #fafaf9; border-bottom: 1px solid #e7e5e4; color: #78716c; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                            <th style="padding: 12px 14px; width: 40px; text-align: center;">
                                <input type="checkbox" id="checkAllGifts" onchange="toggleSelectAllGifts(this)">
                            </th>
                            <th style="padding: 12px 14px;">Người Mừng Cưới</th>
                            @if(! $activeWedding)
                                <th style="padding: 12px 14px;">Tiệc Cưới</th>
                            @endif
                            <th style="padding: 12px 14px;">Bàn Tiệc</th>
                            <th style="padding: 12px 14px;">Số Tiền / Hiện Vật</th>
                            <th style="padding: 12px 14px;">Hình Thức</th>
                            <th style="padding: 12px 14px;">Trạng Thái Cảm Ơn</th>
                            <th style="padding: 12px 14px;">Ghi Chú</th>
                            <th style="padding: 12px 14px;">Thời Gian</th>
                            <th style="padding: 12px 14px; text-align: right;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($gifts as $gift)
                            <tr style="border-bottom: 1px solid #f5f5f4;">
                                <!-- Checkbox -->
                                <td style="padding: 12px 14px; text-align: center;">
                                    <input type="checkbox" name="gift_ids[]" value="{{ $gift->id }}" class="gift-checkbox" onchange="updateBatchToolbar()">
                                </td>

                                <!-- Người mừng & nhóm -->
                                <td style="padding: 12px 14px;">
                                    <div style="font-weight: 600; color: #1c1917;">{{ $gift->sender_name }}</div>
                                    <div style="font-size: 11px; color: #78716c;">
                                        <span style="background: #f5f5f4; padding: 1px 6px; border-radius: 4px;">{{ $gift->sender_group ?: 'Khách chung' }}</span>
                                        @if($gift->guest)
                                            • Khách danh bạ: <strong style="color: #44403c;">{{ $gift->guest->name }}</strong>
                                        @endif
                                    </div>
                                </td>

                                <!-- Tiệc cưới nếu xem toàn sàn -->
                                @if(! $activeWedding)
                                    <td style="padding: 12px 14px;">
                                        @if($gift->wedding)
                                            <div style="font-weight: 600; color: #1c1917;">{{ $gift->wedding->groom_name }} &amp; {{ $gift->wedding->bride_name }}</div>
                                            <div style="font-size: 11px; color: #78716c;">#{{ $gift->wedding->id }} • {{ $gift->wedding->wedding_date ? $gift->wedding->wedding_date->format('d/m/Y') : '—' }}</div>
                                        @else
                                            <span style="color: #a8a29e;">—</span>
                                        @endif
                                    </td>
                                @endif

                                <!-- Bàn tiệc -->
                                <td style="padding: 12px 14px;">
                                    @if($gift->guest && $gift->guest->table_name)
                                        <span style="font-weight: 600; color: #44403c;">{{ $gift->guest->table_name }}</span>
                                    @else
                                        <span style="color: #a8a29e; font-style: italic;">Chưa xếp bàn</span>
                                    @endif
                                </td>

                                <!-- Số tiền / Hiện vật -->
                                <td style="padding: 12px 14px;">
                                    @if($gift->payment_method === 'other' && $gift->amount == 0)
                                        <span style="font-weight: 600; color: #78716c; font-style: italic;">Quà hiện vật</span>
                                    @else
                                        <span style="font-size: 14px; font-weight: 700; color: #047857;">
                                            {{ number_format($gift->amount, 0, ',', '.') }} đ
                                        </span>
                                    @endif
                                </td>

                                <!-- Hình thức -->
                                <td style="padding: 12px 14px;">
                                    @if($gift->payment_method === 'bank_transfer')
                                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #eef2ff; color: #4338ca;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                                            VietQR
                                        </span>
                                    @elseif($gift->payment_method === 'cash')
                                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #fffbeb; color: #b45309;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                            Tiền mặt / Phong bì
                                        </span>
                                    @else
                                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: #fdf2f8; color: #be185d;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line></svg>
                                            Hiện vật
                                        </span>
                                    @endif
                                </td>

                                <!-- Trạng thái cảm ơn -->
                                <td style="padding: 12px 14px;">
                                    <form method="POST" action="{{ route('admin.gifts.toggle_acknowledgement', $gift->id) }}" style="display: inline;">
                                        @csrf
                                        @if($gift->is_acknowledged)
                                            <button type="submit" title="Bấm để chuyển thành chưa cảm ơn" style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                Đã cảm ơn
                                            </button>
                                        @else
                                            <button type="submit" title="Bấm để đánh dấu đã cảm ơn" style="background: #f5f5f4; border: 1px solid #d6d3d1; color: #78716c; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                                Chưa cảm ơn
                                            </button>
                                        @endif
                                    </form>
                                </td>

                                <!-- Ghi chú -->
                                <td style="padding: 12px 14px; font-size: 12px; color: #78716c; max-width: 180px;">
                                    <div style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $gift->notes }}">
                                        {{ $gift->notes ?: '—' }}
                                    </div>
                                </td>

                                <!-- Thời gian -->
                                <td style="padding: 12px 14px; font-size: 12px; color: #78716c;">
                                    {{ $gift->created_at ? $gift->created_at->format('d/m/Y H:i') : '—' }}
                                </td>

                                <!-- Thao tác -->
                                <td style="padding: 12px 14px; text-align: right;">
                                    <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                        <button type="button" onclick="openEditGiftModal({{ $gift->id }}, '{{ addslashes($gift->sender_name) }}', {{ $gift->guest_id ?: 'null' }}, '{{ addslashes($gift->sender_group ?? '') }}', {{ $gift->amount }}, '{{ $gift->payment_method }}', '{{ addslashes($gift->notes ?? '') }}', {{ $gift->is_acknowledged ? 'true' : 'false' }})" class="mayhen_admin_btn" style="padding: 5px 8px; font-size: 12px; border-radius: 6px; background: #f5f5f4; color: #44403c; border: 1px solid #d6d3d1; cursor: pointer;" title="Chỉnh sửa quà mừng">
                                            Sửa
                                        </button>

                                        <form method="POST" action="{{ route('admin.gifts.destroy', $gift->id) }}" onsubmit="return confirm('Bạn có chắc chắn muốn xóa món quà mừng cưới này?');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="mayhen_admin_btn" style="padding: 5px 8px; font-size: 12px; border-radius: 6px; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; cursor: pointer;" title="Xóa món quà">
                                                Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $activeWedding ? 9 : 10 }}" style="padding: 36px; text-align: center; color: #a8a29e;">
                                    Chưa có quà mừng cưới nào được ghi nhận trong sổ mừng.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <!-- Phân trang -->
        @if($gifts->hasPages())
            <div style="display: flex; justify-content: center; padding: 12px 0;">
                {{ $gifts->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal 1: Ghi Nhận Quà Mừng Mới -->
<div id="modalCreateGift" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 500px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); font-family: var(--mh-admin-font);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 17px; font-weight: 700; color: #1c1917; margin: 0;">Ghi Nhận Quà Mừng Mới Vào Sổ</h3>
            <button type="button" onclick="closeCreateGiftModal()" style="background: none; border: none; font-size: 20px; color: #78716c; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.gifts.store') }}">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <!-- Chọn Tiệc Cưới -->
                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Tiệc cưới: <span style="color: #dc2626;">*</span></label>
                    <select name="wedding_id" id="createWeddingSelect" required style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff; box-sizing: border-box;">
                        @foreach($allWeddings as $w)
                            <option value="{{ $w->id }}" {{ $activeWedding?->id == $w->id ? 'selected' : '' }}>
                                #{{ $w->id }} — {{ $w->groom_name }} &amp; {{ $w->bride_name }} ({{ $w->wedding_date ? $w->wedding_date->format('d/m/Y') : 'Chưa đặt ngày' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Chọn Khách Trong Danh Bạ (Tùy chọn) -->
                @if($availableGuests->isNotEmpty())
                    <div>
                        <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Chọn nhanh quan khách trong danh bạ:</label>
                        <select id="createGuestSelect" onchange="autoFillGuestData(this)" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff; box-sizing: border-box;">
                            <option value="">— Gõ tên thủ công bên dưới —</option>
                            @foreach($availableGuests as $g)
                                <option value="{{ $g->id }}" data-name="{{ $g->name }}" data-group="{{ $g->group_name }}">
                                    {{ $g->name }} ({{ $g->group_name ?: 'Chung' }} • Bàn: {{ $g->table_name ?: 'Chưa xếp' }})
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="guest_id" id="createGuestIdInput">
                    </div>
                @endif

                <!-- Tên Người Mừng -->
                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Tên người mừng cưới: <span style="color: #dc2626;">*</span></label>
                    <input type="text" name="sender_name" id="createSenderNameInput" required placeholder="Ví dụ: Bác Ba Hàng Xóm, Bạn Thân Cấp 3..." style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 14px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <!-- Số tiền -->
                    <div>
                        <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Số tiền mừng (VNĐ):</label>
                        <input type="number" name="amount" id="createAmountInput" min="0" step="10000" placeholder="500000" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 14px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                    </div>

                    <!-- Hình thức -->
                    <div>
                        <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Hình thức mừng: <span style="color: #dc2626;">*</span></label>
                        <select name="payment_method" required style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff; box-sizing: border-box;">
                            <option value="cash" selected>Tiền mặt / Phong bì</option>
                            <option value="bank_transfer">Chuyển khoản VietQR</option>
                            <option value="other">Quà mừng hiện vật</option>
                        </select>
                    </div>
                </div>

                <!-- Nhóm Quan Khách -->
                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Nhóm người gửi:</label>
                    <input type="text" name="sender_group" id="createSenderGroupInput" placeholder="Nhà trai / Nhà gái / Bạn chú rể / Đồng nghiệp..." style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>

                <!-- Ghi chú -->
                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Ghi chú / Lời chúc kèm theo:</label>
                    <input type="text" name="notes" placeholder="Ví dụ: Chúc mừng trăm năm hạnh phúc, Mừng đôi nhẫn vàng..." style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>

                <!-- Checkbox Đã Cảm Ơn -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="is_acknowledged" value="1" id="createIsAckCheckbox">
                    <label for="createIsAckCheckbox" style="font-size: 13px; color: #44403c; cursor: pointer;">Đã gửi lời tri ân / tin nhắn cảm ơn cho món quà này</label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeCreateGiftModal()" style="padding: 9px 16px; font-size: 13px; border-radius: 6px; background: #f5f5f4; color: #44403c; border: 1px solid #d6d3d1; cursor: pointer;">Hủy</button>
                <button type="submit" style="padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #059669; color: #ffffff; border: none; cursor: pointer;">Lưu Vào Sổ Mừng</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Chỉnh Sửa Quà Mừng -->
<div id="modalEditGift" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 500px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); font-family: var(--mh-admin-font);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 17px; font-weight: 700; color: #1c1917; margin: 0;">Chỉnh Sửa Quà Mừng Cưới</h3>
            <button type="button" onclick="closeEditGiftModal()" style="background: none; border: none; font-size: 20px; color: #78716c; cursor: pointer;">&times;</button>
        </div>

        <form id="formEditGift" method="POST" action="">
            @csrf
            @method('PUT')
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Tên người mừng cưới: <span style="color: #dc2626;">*</span></label>
                    <input type="text" name="sender_name" id="editSenderNameInput" required style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 14px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Số tiền mừng (VNĐ):</label>
                        <input type="number" name="amount" id="editAmountInput" min="0" step="10000" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 14px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                    </div>

                    <div>
                        <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Hình thức mừng: <span style="color: #dc2626;">*</span></label>
                        <select name="payment_method" id="editPaymentMethodSelect" required style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff; box-sizing: border-box;">
                            <option value="cash">Tiền mặt / Phong bì</option>
                            <option value="bank_transfer">Chuyển khoản VietQR</option>
                            <option value="other">Quà mừng hiện vật</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Nhóm người gửi:</label>
                    <input type="text" name="sender_group" id="editSenderGroupInput" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>

                <div>
                    <label style="font-size: 13px; font-weight: 600; color: #44403c; display: block; margin-bottom: 4px;">Ghi chú:</label>
                    <input type="text" name="notes" id="editNotesInput" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="is_acknowledged" value="1" id="editIsAckCheckbox">
                    <label for="editIsAckCheckbox" style="font-size: 13px; color: #44403c; cursor: pointer;">Đã gửi lời tri ân / tin nhắn cảm ơn</label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeEditGiftModal()" style="padding: 9px 16px; font-size: 13px; border-radius: 6px; background: #f5f5f4; color: #44403c; border: 1px solid #d6d3d1; cursor: pointer;">Hủy</button>
                <button type="submit" style="padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #292524; color: #ffffff; border: none; cursor: pointer;">Cập Nhật Quà Mừng</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateGiftModal() {
        document.getElementById('modalCreateGift').style.display = 'flex';
    }

    function closeCreateGiftModal() {
        document.getElementById('modalCreateGift').style.display = 'none';
    }

    function openEditGiftModal(id, name, guestId, group, amount, method, notes, isAck) {
        document.getElementById('formEditGift').action = `/admin/gifts/${id}`;
        document.getElementById('editSenderNameInput').value = name;
        document.getElementById('editAmountInput').value = amount || 0;
        document.getElementById('editPaymentMethodSelect').value = method;
        document.getElementById('editSenderGroupInput').value = group || '';
        document.getElementById('editNotesInput').value = notes || '';
        document.getElementById('editIsAckCheckbox').checked = isAck;

        document.getElementById('modalEditGift').style.display = 'flex';
    }

    function closeEditGiftModal() {
        document.getElementById('modalEditGift').style.display = 'none';
    }

    function autoFillGuestData(selectEl) {
        const option = selectEl.options[selectEl.selectedIndex];
        if (option && option.value) {
            document.getElementById('createGuestIdInput').value = option.value;
            document.getElementById('createSenderNameInput').value = option.getAttribute('data-name') || '';
            document.getElementById('createSenderGroupInput').value = option.getAttribute('data-group') || '';
        } else {
            document.getElementById('createGuestIdInput').value = '';
        }
    }

    function toggleSelectAllGifts(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.gift-checkbox');
        checkboxes.forEach(cb => cb.checked = masterCheckbox.checked);
        updateBatchToolbar();
    }

    function updateBatchToolbar() {
        const checked = document.querySelectorAll('.gift-checkbox:checked');
        const toolbar = document.getElementById('batchActionGroup');
        const counter = document.getElementById('selectedCountText');

        if (checked.length > 0) {
            toolbar.style.display = 'flex';
            counter.textContent = `Đã chọn ${checked.length} mục`;
        } else {
            toolbar.style.display = 'none';
        }
    }

    function submitBatchAction(actionType) {
        const form = document.getElementById('formBatchActions');
        const checked = document.querySelectorAll('.gift-checkbox:checked');

        if (checked.length === 0) {
            alert('Vui lòng chọn ít nhất một món quà mừng.');
            return;
        }

        if (actionType === 'acknowledge') {
            form.action = "{{ route('admin.gifts.batch_acknowledge') }}";
            form.submit();
        } else if (actionType === 'delete') {
            if (confirm(`Bạn có chắc chắn muốn xóa vĩnh viễn ${checked.length} món quà mừng đã chọn?`)) {
                form.action = "{{ route('admin.gifts.batch_delete') }}";
                form.submit();
            }
        }
    }
</script>
@endsection

