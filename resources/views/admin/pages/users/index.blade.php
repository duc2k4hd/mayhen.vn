@extends('admin.layouts.master')

@section('title', 'Quản Lý Người Dùng — Mây Hẹn')
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
            <span>Khách Hàng (Người Dùng)</span>
            <span class="mayhen_admin_user_tab_count">{{ $customerCount }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="mayhen_admin_user_tab_item {{ $currentRole === 'admin' ? 'active' : '' }}">
            <span>Ban Quản Trị (Admin/Staff)</span>
            <span class="mayhen_admin_user_tab_count">{{ $adminCount }}</span>
        </a>
        <a href="{{ route('admin.users.index', ['role' => 'partner']) }}" class="mayhen_admin_user_tab_item {{ $currentRole === 'partner' ? 'active' : '' }}">
            <span>Cộng Tác Viên (Studio)</span>
            <span class="mayhen_admin_user_tab_count">{{ $partnerCount }}</span>
        </a>
    </div>

    <!-- Filter & Search Bar -->
    <form action="{{ route('admin.users.index') }}" method="GET" class="mayhen_admin_filter_bar">
        @if ($currentRole)
            <input type="hidden" name="role" value="{{ $currentRole }}">
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

        @if ($currentSearch || $currentRole)
            <a href="{{ route('admin.users.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                <span>Đặt Lại</span>
            </a>
        @endif
    </form>

    <!-- Users Table Card -->
    <div class="mayhen_admin_card">
        <div class="mayhen_admin_card_header">
            <div class="mayhen_admin_card_title">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>Danh Sách Tài Khoản ({{ $users->total() }} người dùng)</span>
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
                                        <strong style="font-size: 0.9375rem; color: var(--mh-admin-charcoal);">{{ $u->name }}</strong><br>
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

