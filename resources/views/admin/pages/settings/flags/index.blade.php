@extends('admin.layouts.master')

@section('title', 'Quản Trị Cờ Tính Năng & An Toàn Phát Hành')

@section('content')
<div class="mayhen_admin_flags_container" style="display: flex; flex-direction: column; gap: 24px;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #78716c; margin-bottom: 6px;">
                <a href="{{ route('admin.dashboard') }}" style="color: inherit; text-decoration: none;">Tổng quan</a>
                <span>/</span>
                <a href="{{ route('admin.settings.platform') }}" style="color: inherit; text-decoration: none;">Cài đặt hệ thống</a>
                <span>/</span>
                <span style="color: #1c1917; font-weight: 600;">Cờ tính năng & Safe Rollout</span>
            </div>
            <h1 style="font-size: 24px; font-weight: 700; color: #1c1917; margin: 0; line-height: 1.2;">Trung Tâm Kiểm Soát Phát Hành Tính Năng & Cầu Dao Khẩn Cấp</h1>
            <p style="font-size: 14px; color: #78716c; margin: 6px 0 0 0;">Quản lý triển khai theo tỷ lệ (Phased Rollout), phân nhóm vai trò và kích hoạt ngắt khẩn cấp tức thời (Kill Switch) theo chuẩn an toàn vận hành.</p>
        </div>
        <div style="display: flex; gap: 12px; align-items: center;">
            <button type="button" onclick="openCreateModal()" class="mayhen_admin_btn mayhen_admin_btn--primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; font-weight: 600; font-size: 13px; border-radius: 8px; cursor: pointer;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Tạo Cờ Tính Năng Mới
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
    @if(session('warning'))
        <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: 8px; padding: 14px 18px; color: #92400e; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            <span>{{ session('warning') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 14px 18px; color: #991b1b; font-size: 14px; display: flex; align-items: center; gap: 10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 14px 18px; color: #991b1b; font-size: 14px;">
            <div style="font-weight: 600; margin-bottom: 6px;">Vui lòng kiểm tra lại thông tin:</div>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Metrics KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
        <div class="mayhen_admin_card" style="padding: 18px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #78716c; letter-spacing: 0.5px;">Tổng Số Cờ Hệ Thống</div>
            <div style="font-size: 28px; font-weight: 700; color: #1c1917; margin-top: 6px;">{{ $metrics['total_flags'] }}</div>
            <div style="font-size: 12px; color: #a8a29e; margin-top: 4px;">Định nghĩa trong cơ sở dữ liệu</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 18px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #166534; letter-spacing: 0.5px;">Hoạt Động Toàn Phần (100%)</div>
            <div style="font-size: 28px; font-weight: 700; color: #166534; margin-top: 6px;">{{ $metrics['fully_active'] }}</div>
            <div style="font-size: 12px; color: #a8a29e; margin-top: 4px;">Sẵn sàng phục vụ toàn bộ người dùng</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 18px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: #b45309; letter-spacing: 0.5px;">Thử Nghiệm Safe Rollout</div>
            <div style="font-size: 28px; font-weight: 700; color: #b45309; margin-top: 6px;">{{ $metrics['beta_testing'] }}</div>
            <div style="font-size: 12px; color: #a8a29e; margin-top: 4px;">Áp dụng theo tỷ lệ % hoặc vai trò</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 18px; border-radius: 10px; background: {{ $metrics['kill_switched'] > 0 ? '#fff1f2' : '#ffffff' }}; border: 1px solid {{ $metrics['kill_switched'] > 0 ? '#fecdd3' : '#e7e5e4' }};">
            <div style="font-size: 12px; font-weight: 600; text-transform: uppercase; color: {{ $metrics['kill_switched'] > 0 ? '#e11d48' : '#78716c' }}; letter-spacing: 0.5px;">Cầu Dao Khẩn Cấp (Active)</div>
            <div style="font-size: 28px; font-weight: 700; color: {{ $metrics['kill_switched'] > 0 ? '#e11d48' : '#1c1917' }}; margin-top: 6px;">{{ $metrics['kill_switched'] }}</div>
            <div style="font-size: 12px; color: {{ $metrics['kill_switched'] > 0 ? '#e11d48' : '#a8a29e' }}; margin-top: 4px;">Tính năng bị đóng băng phòng vệ</div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="mayhen_admin_card" style="padding: 16px 20px; border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4;">
        <form method="GET" action="{{ route('admin.settings.flags.index') }}" style="display: flex; flex-wrap: wrap; gap: 14px; align-items: center; justify-content: space-between;">
            <div style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center; flex: 1;">
                <div style="position: relative; min-width: 260px;">
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Tìm theo tên, mã cờ, mô tả..." style="width: 100%; padding: 8px 14px 8px 36px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#a8a29e" stroke-width="2" style="position: absolute; left: 12px; top: 11px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>

                <select name="category" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff;">
                    <option value="all" {{ $filters['category'] === 'all' ? 'selected' : '' }}>Tất cả phân loại</option>
                    <option value="core" {{ $filters['category'] === 'core' ? 'selected' : '' }}>Hệ thống cốt lõi</option>
                    <option value="ai" {{ $filters['category'] === 'ai' ? 'selected' : '' }}>Trí tuệ nhân tạo (AI)</option>
                    <option value="commerce" {{ $filters['category'] === 'commerce' ? 'selected' : '' }}>Thương mại & Gói cước</option>
                    <option value="collaboration" {{ $filters['category'] === 'collaboration' ? 'selected' : '' }}>Cộng tác & Studio</option>
                    <option value="safety" {{ $filters['category'] === 'safety' ? 'selected' : '' }}>An toàn & Bảo vệ</option>
                </select>

                <select name="status" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); background: #ffffff;">
                    <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                    <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Hoạt động (100%)</option>
                    <option value="beta" {{ $filters['status'] === 'beta' ? 'selected' : '' }}>Thử nghiệm (Rollout &lt; 100%)</option>
                    <option value="kill_switched" {{ $filters['status'] === 'kill_switched' ? 'selected' : '' }}>Khóa khẩn cấp (Kill Switch)</option>
                    <option value="disabled" {{ $filters['status'] === 'disabled' ? 'selected' : '' }}>Đã tạm tắt</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="mayhen_admin_btn" style="padding: 8px 16px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #292524; color: #ffffff; border: none; cursor: pointer;">Lọc dữ liệu</button>
                <a href="{{ route('admin.settings.flags.index') }}" class="mayhen_admin_btn" style="padding: 8px 14px; font-size: 13px; border-radius: 6px; background: #f5f5f4; color: #44403c; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">Đặt lại</a>
            </div>
        </form>
    </div>

    <!-- Flags List Table -->
    <div class="mayhen_admin_card" style="border-radius: 10px; background: #ffffff; border: 1px solid #e7e5e4; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; font-family: var(--mh-admin-font);">
                <thead>
                    <tr style="background: #fafaf9; border-bottom: 1px solid #e7e5e4; color: #78716c; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 14px 20px; font-weight: 600;">Tính Năng & Mã Cờ</th>
                        <th style="padding: 14px 16px; font-weight: 600;">Phân Nhóm</th>
                        <th style="padding: 14px 16px; font-weight: 600;">Safe Rollout & Phạm Vi</th>
                        <th style="padding: 14px 16px; font-weight: 600;">Trạng Thái</th>
                        <th style="padding: 14px 16px; font-weight: 600;">Cập Nhật Gần Nhất</th>
                        <th style="padding: 14px 20px; font-weight: 600; text-align: right;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($flags as $flag)
                        <tr style="border-bottom: 1px solid #f5f5f4; transition: background 0.15s ease; {{ $flag->kill_switch_active ? 'background: #fffbfa;' : '' }}">
                            <!-- Name & Key -->
                            <td style="padding: 16px 20px; vertical-align: top;">
                                <div style="display: flex; align-items: flex-start; gap: 10px;">
                                    <div>
                                        <div style="font-weight: 700; color: #1c1917; font-size: 14px;">{{ $flag->name }}</div>
                                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                                            <code style="background: #f5f5f4; border: 1px solid #e7e5e4; padding: 2px 6px; border-radius: 4px; font-size: 11px; color: #44403c; font-family: monospace;">{{ $flag->key }}</code>
                                            @if($flag->category === 'core')
                                                <span style="font-size: 11px; background: #e0f2fe; color: #0369a1; padding: 1px 6px; border-radius: 4px; font-weight: 600;">Lõi bất biến</span>
                                            @endif
                                        </div>
                                        @if($flag->description)
                                            <div style="color: #78716c; font-size: 12px; margin-top: 6px; line-height: 1.4; max-width: 380px;">{{ $flag->description }}</div>
                                        @endif
                                        @if($flag->kill_switch_active && $flag->kill_switch_reason)
                                            <div style="margin-top: 6px; padding: 6px 10px; background: #fee2e2; border-left: 3px solid #ef4444; border-radius: 4px; color: #991b1b; font-size: 11px;">
                                                <strong>Lý do ngắt khẩn cấp:</strong> {{ $flag->kill_switch_reason }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Category -->
                            <td style="padding: 16px 16px; vertical-align: top;">
                                @php
                                    $catStyle = match($flag->category) {
                                        'core' => 'background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1;',
                                        'ai' => 'background: #f3e8ff; color: #6b21a8; border: 1px solid #d8b4fe;',
                                        'commerce' => 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;',
                                        'collaboration' => 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;',
                                        'safety' => 'background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa;',
                                        default => 'background: #f5f5f4; color: #44403c; border: 1px solid #e7e5e4;'
                                    };
                                @endphp
                                <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; font-size: 11px; font-weight: 600; {{ $catStyle }}">
                                    {{ $flag->category_label }}
                                </span>
                            </td>

                            <!-- Safe Rollout & Scope -->
                            <td style="padding: 16px 16px; vertical-align: top;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; font-size: 12px;">
                                    <span style="font-weight: 600; color: #1c1917;">{{ $flag->rollout_percentage }}%</span>
                                    <span style="color: #78716c; font-size: 11px;">Consistent Hash</span>
                                </div>
                                <div style="width: 140px; height: 6px; background: #e7e5e4; border-radius: 999px; overflow: hidden; margin-bottom: 6px;">
                                    <div style="height: 100%; width: {{ $flag->rollout_percentage }}%; background: {{ $flag->rollout_percentage == 100 ? '#10b981' : ($flag->rollout_percentage > 0 ? '#f59e0b' : '#9ca3af') }}; border-radius: 999px;"></div>
                                </div>
                                <div style="font-size: 11px; color: #78716c;">
                                    @if(empty($flag->target_roles))
                                        <span style="color: #059669;">Toàn bộ người dùng</span>
                                    @else
                                        <span>Vai trò: {{ implode(', ', $flag->target_roles) }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td style="padding: 16px 16px; vertical-align: top;">
                                @if($flag->kill_switch_active)
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                        Kill Switch
                                    </span>
                                @elseif(! $flag->is_enabled)
                                    <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; background: #f5f5f4; color: #78716c; border: 1px solid #e7e5e4;">
                                        Đã Tắt
                                    </span>
                                @elseif($flag->rollout_percentage < 100)
                                    <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; background: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                                        Beta ({{ $flag->rollout_percentage }}%)
                                    </span>
                                @else
                                    <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">
                                        Hoạt Động 100%
                                    </span>
                                @endif
                            </td>

                            <!-- Last Toggled -->
                            <td style="padding: 16px 16px; vertical-align: top; font-size: 12px; color: #78716c;">
                                <div>{{ $flag->last_toggled_at ? $flag->last_toggled_at->format('d/m/Y H:i') : 'Mặc định ban đầu' }}</div>
                                <div style="font-size: 11px; color: #a8a29e; margin-top: 2px;">
                                    Bởi: {{ $flag->lastToggledBy?->name ?? 'Hệ thống' }}
                                </div>
                            </td>

                            <!-- Actions -->
                            <td style="padding: 16px 20px; vertical-align: top; text-align: right;">
                                <div style="display: inline-flex; align-items: center; gap: 8px; justify-content: flex-end;">
                                    <!-- Toggle On/Off Form -->
                                    <form method="POST" action="{{ route('admin.settings.flags.toggle', $flag->key) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" title="{{ $flag->is_enabled ? 'Bấm để tắt tính năng' : 'Bấm để bật tính năng' }}" style="border: 1px solid {{ $flag->is_enabled ? '#d1d5db' : '#e5e7eb' }}; background: {{ $flag->is_enabled ? '#22c55e' : '#e5e7eb' }}; border-radius: 999px; width: 44px; height: 24px; position: relative; cursor: pointer; padding: 2px; transition: background 0.2s ease;">
                                            <span style="display: block; width: 18px; height: 18px; background: #ffffff; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.2); transform: {{ $flag->is_enabled ? 'translateX(20px)' : 'translateX(0)' }}; transition: transform 0.2s ease;"></span>
                                        </button>
                                    </form>

                                    <!-- Edit Rollout Button -->
                                    <button type="button" onclick="openRolloutModal('{{ $flag->key }}', '{{ addslashes($flag->name) }}', {{ $flag->rollout_percentage }}, {{ json_encode($flag->target_roles ?? []) }})" title="Điều chỉnh tỷ lệ Rollout & Vai trò" style="background: #f5f5f4; border: 1px solid #d6d3d1; padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; color: #44403c; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                        Rollout
                                    </button>

                                    <!-- Kill Switch Button -->
                                    @if($flag->kill_switch_active)
                                        <form method="POST" action="{{ route('admin.settings.flags.kill_switch', $flag->key) }}" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn HỦY KHÓA KHẨN CẤP và khôi phục tính năng này?');">
                                            @csrf
                                            <button type="submit" title="Hủy khóa khẩn cấp" style="background: #fee2e2; border: 1px solid #f87171; padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 700; color: #b91c1c; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                                                Mở Khóa
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" onclick="openKillSwitchModal('{{ $flag->key }}', '{{ addslashes($flag->name) }}')" title="Kích hoạt Cầu Dao Khẩn Cấp (Kill Switch)" style="background: #ffffff; border: 1px solid #fca5a5; padding: 6px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; color: #dc2626; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                                            Kill-Switch
                                        </button>
                                    @endif

                                    <!-- Delete Button (Only for non-core) -->
                                    @if($flag->category !== 'core')
                                        <form method="POST" action="{{ route('admin.settings.flags.destroy', $flag->key) }}" style="display: inline;" onsubmit="return confirm('Xác nhận xóa vĩnh viễn cờ tính năng này khỏi cơ sở dữ liệu? Thao tác này không thể hoàn tác.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Xóa cờ tính năng" style="background: transparent; border: none; padding: 6px; color: #a8a29e; cursor: pointer; border-radius: 4px;">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 40px 20px; text-align: center; color: #78716c;">
                                <div style="font-size: 14px; font-weight: 500;">Không tìm thấy cờ tính năng nào phù hợp với bộ lọc.</div>
                                <div style="font-size: 12px; color: #a8a29e; margin-top: 4px;">Thử đổi từ khóa tìm kiếm hoặc chọn danh mục khác.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal 1: Tạo Cờ Tính Năng Mới -->
<div id="createFlagModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: 12px; max-width: 520px; width: 90%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); font-family: var(--mh-admin-font); box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid #f5f5f4; padding-bottom: 12px;">
            <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #1c1917;">Tạo Cờ Tính Năng Mới</h3>
            <button type="button" onclick="closeCreateModal()" style="background: none; border: none; font-size: 20px; color: #a8a29e; cursor: pointer;">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.settings.flags.store') }}">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 4px;">Mã định danh (Key) <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="key" required placeholder="ví dụ: live_photo_drop_v2" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: monospace; box-sizing: border-box;">
                    <span style="font-size: 11px; color: #78716c;">Chỉ dùng chữ cái viết thường, số và dấu gạch dưới. Không thể thay đổi sau khi tạo.</span>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 4px;">Tên hiển thị <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="name" required placeholder="ví dụ: Live Photo Drop V2" style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 4px;">Phân loại danh mục <span style="color: #ef4444;">*</span></label>
                    <select name="category" required style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;">
                        <option value="core">Hệ thống cốt lõi (Core)</option>
                        <option value="ai">Trí tuệ nhân tạo (AI)</option>
                        <option value="commerce">Thương mại & Gói cước (Commerce)</option>
                        <option value="collaboration">Cộng tác & Studio (Collaboration)</option>
                        <option value="safety">An toàn & Bảo vệ (Safety)</option>
                    </select>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 4px;">Mô tả tính năng & Tác động</label>
                    <textarea name="description" rows="2" placeholder="Tóm tắt công năng và phạm vi ảnh hưởng khi bật..." style="width: 100%; padding: 9px 12px; border: 1px solid #d6d3d1; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box; resize: vertical;"></textarea>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 4px;">Tỷ lệ phát hành ban đầu (% Rollout)</label>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="range" id="createRolloutRange" min="0" max="100" value="0" style="flex: 1;" oninput="document.getElementById('createRolloutVal').innerText = this.value + '%'">
                        <span id="createRolloutVal" style="font-weight: 700; font-size: 14px; min-width: 45px; color: #1c1917;">0%</span>
                        <input type="hidden" name="rollout_percentage" id="createRolloutInput" value="0">
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                    <input type="checkbox" name="is_enabled" id="createIsEnabled" value="1" style="width: 16px; height: 16px; accent-color: #292524;">
                    <label for="createIsEnabled" style="font-size: 13px; color: #1c1917; font-weight: 500; cursor: pointer;">Kích hoạt cờ ngay sau khi khởi tạo</label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; padding-top: 14px; border-top: 1px solid #f5f5f4;">
                <button type="button" onclick="closeCreateModal()" style="padding: 9px 16px; font-size: 13px; border-radius: 6px; background: #f5f5f4; border: 1px solid #d6d3d1; color: #44403c; cursor: pointer;">Hủy bỏ</button>
                <button type="submit" style="padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #292524; border: none; color: #ffffff; cursor: pointer;">Tạo Cờ Tính Năng</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Cập Nhật Rollout & Target Roles -->
<div id="rolloutModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: 12px; max-width: 500px; width: 90%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2); font-family: var(--mh-admin-font); box-sizing: border-box;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid #f5f5f4; padding-bottom: 12px;">
            <div>
                <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #1c1917;">Điều Chỉnh Tỷ Lệ Phát Hành (Safe Rollout)</h3>
                <div id="rolloutFlagTitle" style="font-size: 12px; color: #78716c; margin-top: 3px;"></div>
            </div>
            <button type="button" onclick="closeRolloutModal()" style="background: none; border: none; font-size: 20px; color: #a8a29e; cursor: pointer;">&times;</button>
        </div>

        <form id="rolloutForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 8px;">Tỷ lệ áp dụng (% Rollout)</label>
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <input type="range" id="rolloutSlider" name="rollout_percentage" min="0" max="100" value="100" style="flex: 1;" oninput="updateRolloutDisplay(this.value)">
                        <span id="rolloutDisplay" style="font-weight: 700; font-size: 18px; min-width: 55px; color: #047857;">100%</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: #a8a29e; margin-top: 4px;">
                        <span>0% (Đóng băng)</span>
                        <span>25% (Beta 1)</span>
                        <span>50% (Phased)</span>
                        <span>100% (Toàn bộ)</span>
                    </div>
                </div>

                <div style="padding: 12px; background: #fafaf9; border-radius: 8px; border: 1px solid #e7e5e4;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #44403c; margin-bottom: 6px;">Giới hạn vai trò người dùng (Target Roles)</label>
                    <div style="font-size: 11px; color: #78716c; margin-bottom: 10px;">Nếu để trống, tính năng sẽ được áp dụng cho toàn bộ người dùng theo thuật toán băm nhất quán.</div>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer;">
                            <input type="checkbox" name="target_roles[]" value="admin" id="role_admin"> Quản trị viên (Admin)
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer;">
                            <input type="checkbox" name="target_roles[]" value="partner_studio" id="role_partner"> Đối tác Studio Cưới
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer;">
                            <input type="checkbox" name="target_roles[]" value="couple" id="role_couple"> Cô dâu & Chú rể
                        </label>
                        <label style="display: flex; align-items: center; gap: 6px; font-size: 12px; cursor: pointer;">
                            <input type="checkbox" name="target_roles[]" value="customer" id="role_customer"> Khách hàng chung
                        </label>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 22px; padding-top: 14px; border-top: 1px solid #f5f5f4;">
                <button type="button" onclick="closeRolloutModal()" style="padding: 9px 16px; font-size: 13px; border-radius: 6px; background: #f5f5f4; border: 1px solid #d6d3d1; color: #44403c; cursor: pointer;">Hủy bỏ</button>
                <button type="submit" style="padding: 9px 18px; font-size: 13px; font-weight: 600; border-radius: 6px; background: #292524; border: none; color: #ffffff; cursor: pointer;">Lưu Thay Đổi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Kích Hoạt Cầu Dao Khẩn Cấp (Kill Switch Modal) -->
<div id="killSwitchModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(3px);">
    <div style="background: #ffffff; border-radius: 12px; max-width: 480px; width: 90%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); font-family: var(--mh-admin-font); box-sizing: border-box; border-top: 4px solid #ef4444;">
        <div style="display: flex; gap: 14px; align-items: flex-start; margin-bottom: 16px;">
            <div style="width: 42px; height: 42px; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
            </div>
            <div>
                <h3 style="margin: 0; font-size: 17px; font-weight: 700; color: #991b1b;">Kích Hoạt Cầu Dao Khẩn Cấp (Kill Switch)</h3>
                <div id="killSwitchFlagName" style="font-size: 13px; font-weight: 600; color: #1c1917; margin-top: 4px;"></div>
            </div>
        </div>

        <p style="font-size: 13px; color: #4b5563; line-height: 1.5; margin: 0 0 16px 0;">
            Hành động này sẽ <strong>lập tức vô hiệu hóa tính năng này trên toàn bộ hệ thống</strong> (ngay cả khi tỷ lệ rollout là 100%). Mọi luồng xử lý liên quan sẽ bị ngắt để bảo vệ an toàn dữ liệu khách hàng.
        </p>

        <form id="killSwitchForm" method="POST" action="">
            @csrf
            <div>
                <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">Lý do ngắt khẩn cấp (Bắt buộc) <span style="color: #ef4444;">*</span></label>
                <textarea name="kill_switch_reason" required minlength="5" rows="3" placeholder="Ghi rõ sự cố kỹ thuật hoặc nguy cơ an ninh đang phát sinh..." style="width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; font-family: var(--mh-admin-font); box-sizing: border-box;"></textarea>
                <span style="font-size: 11px; color: #6b7280;">Thông tin này được ghi nhận vĩnh viễn vào Nhật ký kiểm toán hệ thống (Audit Logs).</span>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" onclick="closeKillSwitchModal()" style="padding: 9px 16px; font-size: 13px; border-radius: 6px; background: #f3f4f6; border: 1px solid #d1d5db; color: #374151; cursor: pointer;">Hủy bỏ</button>
                <button type="submit" style="padding: 9px 18px; font-size: 13px; font-weight: 700; border-radius: 6px; background: #dc2626; border: none; color: #ffffff; cursor: pointer;">Xác Nhận Ngắt Cầu Dao</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('createFlagModal').style.display = 'flex';
    }
    function closeCreateModal() {
        document.getElementById('createFlagModal').style.display = 'none';
    }

    document.getElementById('createRolloutRange').addEventListener('input', function() {
        document.getElementById('createRolloutInput').value = this.value;
    });

    function openRolloutModal(key, name, currentRollout, currentRoles) {
        document.getElementById('rolloutFlagTitle').innerText = name + ' (' + key + ')';
        var form = document.getElementById('rolloutForm');
        form.action = "{{ url('admin/settings/flags') }}/" + key + "/rollout";
        
        var slider = document.getElementById('rolloutSlider');
        slider.value = currentRollout;
        updateRolloutDisplay(currentRollout);

        // Reset roles checkboxes
        var roles = ['admin', 'partner_studio', 'couple', 'customer'];
        roles.forEach(function(r) {
            var el = document.getElementById('role_' + (r === 'partner_studio' ? 'partner' : r));
            if (el) {
                el.checked = currentRoles && currentRoles.indexOf(r) !== -1;
            }
        });

        document.getElementById('rolloutModal').style.display = 'flex';
    }

    function closeRolloutModal() {
        document.getElementById('rolloutModal').style.display = 'none';
    }

    function updateRolloutDisplay(val) {
        var el = document.getElementById('rolloutDisplay');
        el.innerText = val + '%';
        if (val == 100) {
            el.style.color = '#047857';
        } else if (val > 0) {
            el.style.color = '#b45309';
        } else {
            el.style.color = '#6b7280';
        }
    }

    function openKillSwitchModal(key, name) {
        document.getElementById('killSwitchFlagName').innerText = name + ' (' + key + ')';
        var form = document.getElementById('killSwitchForm');
        form.action = "{{ url('admin/settings/flags') }}/" + key + "/kill-switch";
        document.getElementById('killSwitchModal').style.display = 'flex';
    }

    function closeKillSwitchModal() {
        document.getElementById('killSwitchModal').style.display = 'none';
    }

    // Đóng modal khi bấm ra ngoài vùng nội dung
    window.addEventListener('click', function(e) {
        if (e.target === document.getElementById('createFlagModal')) closeCreateModal();
        if (e.target === document.getElementById('rolloutModal')) closeRolloutModal();
        if (e.target === document.getElementById('killSwitchModal')) closeKillSwitchModal();
    });
</script>
@endsection

