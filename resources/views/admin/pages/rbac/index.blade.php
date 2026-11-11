@extends('admin.layouts.master')

@section('title', 'Ma Trận Phân Quyền & Bàn Giao Vai Trò Vận Hành')

@section('content')
<div class="mayhen_admin_rbac" style="padding: 24px; max-width: 1440px; margin: 0 auto; font-family: var(--mh-admin-font, 'Open Sans', sans-serif);">

    <!-- Header Section -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <h1 style="font-size: 24px; font-weight: 700; color: #1e293b; margin: 0; letter-spacing: -0.5px;">
                    Ma Trận Phân Quyền & Bàn Giao Vai Trò Vận Hành
                </h1>
                <span style="font-size: 11px; font-weight: 700; background: #fdf2f8; color: #be185d; padding: 3px 8px; border-radius: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    RBAC & Staff Governance
                </span>
            </div>
            <p style="font-size: 14px; color: #64748b; margin: 0;">
                Kiểm soát phân quyền theo hành động (Action-level RBAC) và quản trị bàn giao trách nhiệm vận hành theo Mục 2.1, 2.2 & 67 PRD V3.
            </p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button" onclick="openCreateStaffModal()" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 8px; background: #0f172a; color: #ffffff; border: none; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                Thêm Nhân Sự Mới
            </button>
            <a href="{{ route('admin.rbac.export', request()->all()) }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.2s;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" x2="12" y1="15" y2="3"></line></svg>
                Xuất Báo Cáo CSV
            </a>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; color: #065f46; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; color: #991b1b; font-size: 13px;">
            <div style="font-weight: 600; margin-bottom: 4px;">Đã xảy ra lỗi:</div>
            <ul style="margin: 0; padding-left: 18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 4 KPI Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Tổng Nhân Sự Nội Bộ
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #1e293b;">
                    {{ number_format($stats['total_staff']) }}
                </div>
                <div style="background: #f1f5f9; color: #475569; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                    Tất cả vai trò
                </div>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #be185d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Quản Trị Cấp Cao (Super Admin)
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #be185d;">
                    {{ number_format($stats['super_admins']) }}
                </div>
                <div style="background: #fdf2f8; color: #be185d; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                    Toàn quyền
                </div>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #047857; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Đang Hoạt Động Bình Thường
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #059669;">
                    {{ number_format($stats['active_staff']) }}
                </div>
                <div style="background: #ecfdf5; color: #065f46; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                    Active
                </div>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 12px; font-weight: 600; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                Đang Tạm Khóa / Đình Chỉ
            </div>
            <div style="display: flex; align-items: baseline; justify-content: space-between;">
                <div style="font-size: 26px; font-weight: 700; color: #dc2626;">
                    {{ number_format($stats['locked_staff']) }}
                </div>
                <div style="background: #fef2f2; color: #991b1b; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 600;">
                    Khóa tức thì
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
        <button type="button" onclick="switchTab('staff')" id="tabBtnStaff" style="padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; {{ $activeTab === 'staff' ? 'background: #0f172a; color: #ffffff;' : 'background: #f8fafc; color: #64748b;' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            Danh Sách Nhân Sự & Bàn Giao Vai Trò
        </button>
        <button type="button" onclick="switchTab('matrix')" id="tabBtnMatrix" style="padding: 10px 20px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; {{ $activeTab === 'matrix' ? 'background: #0f172a; color: #ffffff;' : 'background: #f8fafc; color: #64748b;' }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            Ma Trận Phân Quyền RBAC (Mục 67 PRD V3)
        </button>
    </div>

    <!-- TAB 1: DANH SÁCH NHÂN SỰ -->
    <div id="tabContentStaff" style="display: {{ $activeTab === 'staff' ? 'block' : 'none' }};">
        <!-- Filter Bar -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <form method="GET" action="{{ route('admin.rbac.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center;">
                <input type="hidden" name="tab" value="staff">

                <div style="flex: 1; min-width: 220px; position: relative;">
                    <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="Tìm theo tên, email hoặc số điện thoại..." style="width: 100%; padding: 8px 12px 8px 36px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; font-family: inherit;">
                    <svg style="position: absolute; left: 12px; top: 10px; color: #94a3b8;" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>

                <div style="min-width: 200px;">
                    <select name="role" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; background: #fff; font-family: inherit;">
                        <option value="">-- Tất cả vai trò --</option>
                        @foreach($roles as $key => $roleData)
                            <option value="{{ $key }}" {{ $filters['role'] === $key ? 'selected' : '' }}>
                                {{ $roleData['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="min-width: 160px;">
                    <select name="status" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; outline: none; background: #fff; font-family: inherit;">
                        <option value="all" {{ $filters['status'] === 'all' ? 'selected' : '' }}>Tất cả trạng thái</option>
                        <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>🟢 Đang hoạt động</option>
                        <option value="locked" {{ $filters['status'] === 'locked' ? 'selected' : '' }}>🔴 Đang tạm khóa</option>
                    </select>
                </div>

                <button type="submit" style="padding: 8px 16px; border-radius: 8px; background: #0f172a; color: #ffffff; border: none; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                    Lọc dữ liệu
                </button>

                @if(!empty($filters['q']) || !empty($filters['role']) || $filters['status'] !== 'all')
                    <a href="{{ route('admin.rbac.index', ['tab' => 'staff']) }}" style="padding: 8px 14px; border-radius: 8px; background: #f1f5f9; color: #64748b; text-decoration: none; font-size: 13px; font-weight: 600;">
                        Đặt lại
                    </a>
                @endif
            </form>
        </div>

        <!-- Staff Table -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02); margin-bottom: 24px;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; font-weight: 600;">
                        <th style="padding: 12px 18px;">Nhân Sự</th>
                        <th style="padding: 12px 18px;">Vai Trò Vận Hành</th>
                        <th style="padding: 12px 18px;">Số Điện Thoại</th>
                        <th style="padding: 12px 18px;">Trạng Thái</th>
                        <th style="padding: 12px 18px;">Ngày Tham Gia</th>
                        <th style="padding: 12px 18px; text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffMembers as $member)
                        @php
                            $roleMeta = $roles[$member->role] ?? null;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;">
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 700; color: #1e293b; margin-bottom: 2px;">
                                    {{ $member->name }}
                                </div>
                                <div style="font-size: 12px; color: #64748b;">
                                    {{ $member->email }}
                                </div>
                            </td>
                            <td style="padding: 14px 18px;">
                                @if($roleMeta)
                                    <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; background: {{ $roleMeta['bg_color'] }}; color: {{ $roleMeta['badge_color'] }}; border: 1px solid {{ $roleMeta['badge_color'] }}33;">
                                        {{ $roleMeta['label'] }}
                                    </span>
                                @else
                                    <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #f1f5f9; color: #475569;">
                                        {{ $member->role }}
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 14px 18px; color: #475569;">
                                {{ $member->phone ?? '—' }}
                            </td>
                            <td style="padding: 14px 18px;">
                                @if($member->is_locked)
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #fef2f2; color: #b91c1c;" title="{{ $member->lock_reason }}">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                        Đang tạm khóa
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; background: #ecfdf5; color: #047857;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                        Đang hoạt động
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 14px 18px; color: #64748b; font-size: 12px;">
                                {{ $member->created_at ? $member->created_at->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td style="padding: 14px 18px; text-align: right;">
                                <div style="display: inline-flex; gap: 8px;">
                                    <button type="button" onclick="openHandoverModal({{ $member->id }}, '{{ addslashes($member->name) }}', '{{ $member->role }}')" style="padding: 6px 10px; border-radius: 6px; background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-size: 12px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" title="Chuyển giao vai trò">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>
                                        Đổi vai trò
                                    </button>

                                    <form method="POST" action="{{ route('admin.rbac.toggle_lock', $member->id) }}" style="margin: 0;" onsubmit="return confirm('{{ $member->is_locked ? "Bạn có chắc chắn muốn mở khóa cho nhân sự {$member->name}?" : "CẢNH BÁO: Nhân sự {$member->name} sẽ bị đình chỉ quyền truy cập ngay lập tức. Tiếp tục?" }}');">
                                        @csrf
                                        @if($member->is_locked)
                                            <button type="submit" style="padding: 6px 10px; border-radius: 6px; background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 12px; font-weight: 600; cursor: pointer;">
                                                Mở khóa
                                            </button>
                                        @else
                                            <button type="submit" style="padding: 6px 10px; border-radius: 6px; background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-size: 12px; font-weight: 600; cursor: pointer;">
                                                Khóa quyền
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 36px 18px; text-align: center; color: #64748b;">
                                Không tìm thấy nhân sự nào phù hợp với bộ lọc.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 20px;">
            {{ $staffMembers->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- TAB 2: MA TRẬN PHÂN QUYỀN RBAC (MỤC 67 PRD V3) -->
    <div id="tabContentMatrix" style="display: {{ $activeTab === 'matrix' ? 'block' : 'none' }};">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
                <div>
                    <h2 style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">
                        Ma Trận Phân Bổ Quyền Hạn Hành Động (Action Permission Matrix)
                    </h2>
                    <p style="font-size: 13px; color: #64748b; margin: 0;">
                        Tick chọn các quyền hành động cho phép đối với từng vai trò. Quản trị viên cấp cao (Super Admin) luôn giữ toàn quyền hệ thống.
                    </p>
                </div>
                <div>
                    <button type="button" onclick="document.getElementById('matrixForm').submit()" style="padding: 10px 20px; border-radius: 8px; background: #be185d; color: #ffffff; border: none; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(190, 24, 93, 0.25);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        Lưu Cấu Hình Ma Trận
                    </button>
                </div>
            </div>

            <form id="matrixForm" method="POST" action="{{ route('admin.rbac.update_matrix') }}">
                @csrf

                <div style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 10px;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; min-width: 900px;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #334155;">
                                <th style="padding: 14px 18px; width: 35%;">Hành Động Hệ Thống / Phân Nhóm</th>
                                @foreach($roles as $roleKey => $roleData)
                                    <th style="padding: 14px 12px; text-align: center; width: 10%;">
                                        <div style="font-weight: 700; font-size: 12px; color: {{ $roleData['badge_color'] }}; margin-bottom: 2px;">
                                            {{ $roleData['label'] }}
                                        </div>
                                        <span style="font-size: 10px; font-weight: 500; color: #64748b; font-family: monospace;">
                                            {{ $roleKey }}
                                        </span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissionGroups as $groupKey => $group)
                                <!-- Group Header Row -->
                                <tr style="background: #f1f5f9; border-top: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">
                                    <td colspan="{{ count($roles) + 1 }}" style="padding: 10px 18px; font-weight: 700; color: #1e293b; font-size: 13px; letter-spacing: 0.3px;">
                                        📁 {{ $group['label'] }} <span style="font-weight: 400; color: #64748b; font-size: 11px;">({{ count($group['permissions']) }} quyền)</span>
                                    </td>
                                </tr>

                                @foreach($group['permissions'] as $permKey => $permLabel)
                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                        <td style="padding: 10px 18px;">
                                            <div style="font-weight: 600; color: #334155;">
                                                {{ $permLabel }}
                                            </div>
                                            <div style="font-size: 11px; color: #94a3b8; font-family: monospace;">
                                                {{ $permKey }}
                                            </div>
                                        </td>

                                        @foreach($roles as $roleKey => $roleData)
                                            <td style="padding: 10px 12px; text-align: center; vertical-align: middle;">
                                                @if($roleKey === 'admin')
                                                    <!-- Super Admin luôn bật -->
                                                    <input type="checkbox" checked disabled style="cursor: not-allowed; accent-color: #be185d; width: 16px; height: 16px;">
                                                @else
                                                    @php
                                                        $isChecked = in_array($permKey, $roleMatrix[$roleKey] ?? [], true);
                                                    @endphp
                                                    <input type="checkbox" name="matrix[{{ $roleKey }}][]" value="{{ $permKey }}" {{ $isChecked ? 'checked' : '' }} style="cursor: pointer; accent-color: #be185d; width: 16px; height: 16px;">
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                    <button type="submit" style="padding: 10px 24px; border-radius: 8px; background: #be185d; color: #ffffff; border: none; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(190, 24, 93, 0.25);">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        Lưu Cấu Hình Ma Trận Phân Quyền
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Thêm Nhân Sự Mới -->
<div id="createStaffModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 500px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); position: relative;">
        <button type="button" onclick="closeCreateStaffModal()" style="position: absolute; top: 18px; right: 18px; background: none; border: none; color: #94a3b8; cursor: pointer;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>

        <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">
            Thêm Tài Khoản Nhân Sự Mới
        </h3>
        <p style="font-size: 13px; color: #64748b; margin: 0 0 20px 0;">
            Khởi tạo tài khoản nhân viên nội bộ và phân bổ vai trò vận hành ban đầu.
        </p>

        <form method="POST" action="{{ route('admin.rbac.store_staff') }}">
            @csrf

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">
                    Họ và tên <span style="color: #ef4444;">*</span>
                </label>
                <input type="text" name="name" required placeholder="Ví dụ: Lê Hoàng Long" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; outline: none;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">
                    Email đăng nhập <span style="color: #ef4444;">*</span>
                </label>
                <input type="email" name="email" required placeholder="nhanvien@mayhen.vn" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; outline: none;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">
                    Số điện thoại
                </label>
                <input type="text" name="phone" placeholder="0901234567" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; outline: none;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">
                    Mật khẩu khởi tạo <span style="color: #ef4444;">*</span>
                </label>
                <input type="password" name="password" required minlength="8" placeholder="Tối thiểu 8 ký tự" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; outline: none;">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">
                    Vai trò vận hành <span style="color: #ef4444;">*</span>
                </label>
                <select name="role" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; outline: none; background: #fff;">
                    @foreach($roles as $key => $roleData)
                        <option value="{{ $key }}" {{ $key === 'staff' ? 'selected' : '' }}>
                            {{ $roleData['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeCreateStaffModal()" style="padding: 8px 16px; border-radius: 8px; background: #f1f5f9; color: #64748b; border: none; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Hủy bỏ
                </button>
                <button type="submit" style="padding: 8px 18px; border-radius: 8px; background: #0f172a; color: #ffffff; border: none; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Tạo tài khoản
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Chuyển Giao Vai Trò Vận Hành -->
<div id="handoverModal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 480px; width: 100%; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); position: relative;">
        <button type="button" onclick="closeHandoverModal()" style="position: absolute; top: 18px; right: 18px; background: none; border: none; color: #94a3b8; cursor: pointer;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>

        <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">
            Chuyển Giao Vai Trò Vận Hành
        </h3>
        <p style="font-size: 13px; color: #64748b; margin: 0 0 16px 0;">
            Điều chỉnh phạm vi quyền hạn theo vị trí công việc mới của nhân sự.
        </p>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px; margin-bottom: 18px;">
            <div style="font-size: 13px; font-weight: 700; color: #1e293b;" id="handoverMemberName"></div>
            <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                Vai trò hiện tại: <span id="handoverCurrentRole" style="font-weight: 600; color: #be185d;"></span>
            </div>
        </div>

        <form id="handoverForm" method="POST" action="">
            @csrf

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 12px; font-weight: 600; color: #475569; margin-bottom: 6px;">
                    Vai trò mới bàn giao <span style="color: #ef4444;">*</span>
                </label>
                <select name="role" id="handoverNewRoleSelect" required style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; outline: none; background: #fff;">
                    @foreach($roles as $key => $roleData)
                        <option value="{{ $key }}">
                            {{ $roleData['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeHandoverModal()" style="padding: 8px 16px; border-radius: 8px; background: #f1f5f9; color: #64748b; border: none; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Hủy bỏ
                </button>
                <button type="submit" style="padding: 8px 18px; border-radius: 8px; background: #be185d; color: #ffffff; border: none; font-size: 13px; font-weight: 600; cursor: pointer;">
                    Xác nhận bàn giao
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function switchTab(tab) {
        const staffTab = document.getElementById('tabContentStaff');
        const matrixTab = document.getElementById('tabContentMatrix');
        const btnStaff = document.getElementById('tabBtnStaff');
        const btnMatrix = document.getElementById('tabBtnMatrix');

        if (tab === 'matrix') {
            staffTab.style.display = 'none';
            matrixTab.style.display = 'block';
            btnStaff.style.background = '#f8fafc';
            btnStaff.style.color = '#64748b';
            btnMatrix.style.background = '#0f172a';
            btnMatrix.style.color = '#ffffff';
        } else {
            staffTab.style.display = 'block';
            matrixTab.style.display = 'none';
            btnStaff.style.background = '#0f172a';
            btnStaff.style.color = '#ffffff';
            btnMatrix.style.background = '#f8fafc';
            btnMatrix.style.color = '#64748b';
        }
    }

    // Modal Create Staff
    function openCreateStaffModal() {
        document.getElementById('createStaffModal').style.display = 'flex';
    }

    function closeCreateStaffModal() {
        document.getElementById('createStaffModal').style.display = 'none';
    }

    // Modal Handover Role
    function openHandoverModal(id, name, currentRole) {
        document.getElementById('handoverMemberName').textContent = 'Nhân sự: ' + name;
        document.getElementById('handoverCurrentRole').textContent = currentRole;
        document.getElementById('handoverNewRoleSelect').value = currentRole;
        document.getElementById('handoverForm').action = '/admin/rbac/staff/' + id + '/role';
        document.getElementById('handoverModal').style.display = 'flex';
    }

    function closeHandoverModal() {
        document.getElementById('handoverModal').style.display = 'none';
    }
</script>
@endsection

