@extends('admin.layouts.master')

@section('title', 'Quản Lý Người Dùng')
@section('page_title', 'Quản Lý Tài Khoản & Phân Quyền')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/users.css') }}">
@endpush

@section('content')
    <!-- Role Filter Tabs -->
    <div class="mayhen_admin_user_tabs">
        <a href="{{ route('admin.users.index') }}" class="mayhen_admin_user_tab_item {{ empty($currentRole) || $currentRole === 'all' ? 'active' : '' }}">
            <span>Tất Cả Người Dùng</span>
            <span class="mayhen_admin_user_tab_count">{{ $totalCount }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'customer']) }}" class="mayhen_admin_user_tab_item {{ $currentRole === 'customer' ? 'active' : '' }}">
            <span>Người Dùng</span>
            <span class="mayhen_admin_user_tab_count">{{ $customerCount }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="mayhen_admin_user_tab_item {{ $currentRole === 'admin' ? 'active' : '' }}">
            <span>Quản Trị Viên</span>
            <span class="mayhen_admin_user_tab_count">{{ $adminCount }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'manager']) }}" class="mayhen_admin_user_tab_item {{ $currentRole === 'manager' ? 'active' : '' }}">
            <span>Quản Lý</span>
            <span class="mayhen_admin_user_tab_count">{{ $managerCount }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'staff']) }}" class="mayhen_admin_user_tab_item {{ $currentRole === 'staff' ? 'active' : '' }}">
            <span>Nhân Viên</span>
            <span class="mayhen_admin_user_tab_count">{{ $staffCount }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'partner']) }}" class="mayhen_admin_user_tab_item {{ $currentRole === 'partner' ? 'active' : '' }}">
            <span>Cộng Tác Viên (Studio)</span>
            <span class="mayhen_admin_user_tab_count">{{ $partnerCount }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['status' => 'locked']) }}" class="mayhen_admin_user_tab_item {{ $currentStatus === 'locked' ? 'active' : '' }}" style="{{ $currentStatus === 'locked' ? 'border-color: #C62828; color: #C62828;' : '' }}">
            <span style="{{ $lockedCount > 0 ? 'color: #C62828; font-weight: 600;' : '' }}">Bị Khóa</span>
            <span class="mayhen_admin_user_tab_count" style="{{ $lockedCount > 0 ? 'background: #FFEBEE; color: #C62828;' : '' }}">{{ $lockedCount }}</span>
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <form action="{{ route('admin.users.index') }}" method="GET" class="mayhen_admin_filter_bar">
        @if ($currentRole)
            <input type="hidden" name="role" value="{{ $currentRole }}">
        @endif
        @if ($currentStatus)
            <input type="hidden" name="status" value="{{ $currentStatus }}">
        @endif

        <div class="mayhen_admin_search_box">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #7A756E;">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input 
                type="text" 
                name="q" 
                value="{{ $currentSearch }}" 
                placeholder="Tìm tên người dùng, email, số điện thoại..." 
                class="mayhen_admin_search_input"
            >
        </div>

        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">
            <span>Tìm Kiếm</span>
        </button>

        @if ($currentSearch || $currentRole || $currentStatus)
            <a href="{{ route('admin.users.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                <span>Đặt Lại</span>
            </a>
        @endif
    </form>

    <!-- Users Table Card -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
            <div class="mayhen_admin_card_title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Danh Sách Tài Khoản ({{ $users->total() }} người dùng)</span>
            </div>

            <div>
                <a href="{{ route('admin.users.export', ['q' => $currentSearch, 'role' => $currentRole, 'status' => $currentStatus]) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Xuất CSV (Excel)</span>
                </a>
            </div>
        </div>

        <div class="mayhen_admin_table_responsive">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th>Khách Hàng / Tài Khoản</th>
                        <th>Vai Trò (Phân Quyền)</th>
                        <th>Số Thiệp Đã Tạo</th>
                        <th>Tổng Tiền Đã Thanh Toán</th>
                        <th>Ngày Tham Gia</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                        @php
                            $roleClass = match($u->role) {
                                'admin' => 'mayhen_admin_role_admin',
                                'manager' => 'mayhen_admin_role_manager',
                                'staff' => 'mayhen_admin_role_staff',
                                'partner' => 'mayhen_admin_role_partner',
                                default => 'mayhen_admin_role_customer',
                            };
                            $totalSpent = $u->orders->sum('amount');
                        @endphp
                        <tr>
                            <!-- User Avatar & Identity -->
                            <td>
                                <div class="mayhen_admin_user_avatar_cell">
                                    <div class="mayhen_admin_avatar_sm">
                                        {{ strtoupper(mb_substr($u->name ?? 'U', 0, 1, 'UTF-8')) }}
                                    </div>
                                    <div>
                                        <strong style="font-size: 0.9375rem; color: var(--mh-admin-charcoal);">{{ $u->name }}</strong>
                                        @if ($u->isLocked())
                                            <span class="mayhen_admin_badge mayhen_admin_badge_danger" style="margin-left: 6px; font-size: 0.6875rem;">Đã khóa</span>
                                        @endif
                                        <br>
                                        <span style="font-size: 0.75rem; color: #7A756E;">{{ $u->email }}</span>
                                        @if ($u->phone)
                                            <br><span style="font-size: 0.75rem; color: #7A756E;">{{ $u->phone }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Role Badge -->
                            <td>
                                <span class="mayhen_admin_role_badge {{ $roleClass }}">
                                    {{ $u->role_label }}
                                </span>
                            </td>

                            <!-- Created Weddings Count -->
                            <td>
                                <strong style="font-size: 0.9375rem;">{{ $u->weddings_count }}</strong> thiệp
                            </td>

                            <!-- Total Spent -->
                            <td>
                                <strong style="font-size: 0.9375rem; color: #1B5E20;">
                                    {{ number_format($totalSpent, 0, ',', '.') }}đ
                                </strong>
                            </td>

                            <!-- Joined Date -->
                            <td>
                                <span style="font-size: 0.8125rem;">{{ $u->created_at->format('d/m/Y') }}</span>
                            </td>

                            <!-- Actions -->
                            <td>
                                <a href="{{ route('admin.users.show', $u->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                                    Chi Tiết & Phân Quyền &nearr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; color: #7A756E; padding: 40px;">
                                Không tìm thấy tài khoản người dùng nào phù hợp.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--mh-admin-border);">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection

