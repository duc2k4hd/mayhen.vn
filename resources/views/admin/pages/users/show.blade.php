@extends('admin.layouts.master')

@section('title', 'Tài Khoản: ' . $user->name . ' — Mây Hẹn')
@section('page_title', 'Hồ Sơ & Phân Quyền: ' . $user->name)

@section('breadcrumb_parent')
    <a href="{{ route('admin.users.index') }}">Người Dùng</a>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/users.css') }}">
@endpush

@section('content')
    <!-- Top Action Breadcrumb Bar -->
    <div class="mayhen_admin_page_header">
        <div>
            <div class="mayhen_admin_page_title">
                <span>{{ $user->name }}</span>
                @php
                    $roleClass = match($user->role) {
                        'admin' => 'mayhen_admin_role_admin',
                        'manager' => 'mayhen_admin_role_manager',
                        'staff' => 'mayhen_admin_role_staff',
                        'partner' => 'mayhen_admin_role_partner',
                        default => 'mayhen_admin_role_customer',
                    };
                @endphp
                <span class="mayhen_admin_role_badge {{ $roleClass }}">{{ $user->role_label }}</span>
            </div>
            <div class="mayhen_admin_page_subtitle">
                Email: <strong>{{ $user->email }}</strong> &bull; SĐT: <strong>{{ $user->phone ?? 'Chưa cập nhật' }}</strong> &bull; Tham gia: <strong>{{ $user->created_at->format('d/m/Y') }}</strong>
            </div>
        </div>

        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.users.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                &larr; Quay Lại Danh Sách
            </a>
        </div>
    </div>

    <!-- 2 Columns Layout: Left Weddings & Orders | Right Profile & Security -->
    <div class="mayhen_admin_user_detail_grid">
        <!-- Left: Weddings & Order History -->
        <div>
            <!-- Weddings Card -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <span>Thiệp Cưới Của Người Dùng Này ({{ $user->weddings->count() }} lễ cưới)</span>
                    </div>
                </div>

                <div class="mayhen_admin_table_responsive">
                    <table class="mayhen_admin_table">
                        <thead>
                            <tr>
                                <th>Cặp Đôi & Ngày Cưới</th>
                                <th>Mẫu Thiệp</th>
                                <th>Đường Dẫn Công Khai</th>
                                <th>Trạng Thái</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($user->weddings as $wedding)
                                @if ($wedding->invitations->isNotEmpty())
                                    @foreach ($wedding->invitations as $inv)
                                        <tr>
                                            <td>
                                                <strong>{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</strong><br>
                                                <span style="font-size: 0.75rem; color: #7A756E;">
                                                    Ngày cưới: {{ $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : 'Chưa định' }}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{{ $inv->template->name ?? 'Mặc định' }}</strong>
                                            </td>
                                            <td>
                                                <code>/thiep/{{ $inv->slug }}</code>
                                            </td>
                                            <td>
                                                @if ($inv->is_published)
                                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Đã Xuất Bản</span>
                                                @else
                                                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Bản Nháp</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.invitations.show', $inv->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                                                    Quản Lý Thiệp &nearr;
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td>
                                            <strong>{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</strong><br>
                                            <span style="font-size: 0.75rem; color: #7A756E;">
                                                Ngày cưới: {{ $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : 'Chưa định' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="mayhen_admin_badge mayhen_admin_badge_muted">Chưa chọn mẫu</span>
                                        </td>
                                        <td>—</td>
                                        <td>
                                            <span class="mayhen_admin_badge mayhen_admin_badge_warning">Bản Nháp</span>
                                        </td>
                                        <td>—</td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #7A756E; padding: 24px;">
                                        Người dùng chưa tạo thiệp cưới nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Orders History Card -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                        <span>Lịch Sử Đơn Hàng & Thanh Toán ({{ $user->orders->count() }} đơn)</span>
                    </div>
                </div>

                <div class="mayhen_admin_table_responsive">
                    <table class="mayhen_admin_table">
                        <thead>
                            <tr>
                                <th>Mã Đơn</th>
                                <th>Gói Dịch Vụ</th>
                                <th>Số Tiền</th>
                                <th>Phương Thức</th>
                                <th>Trạng Thái</th>
                                <th>Thời Gian</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($user->orders as $order)
                                <tr>
                                    <td><code>{{ $order->order_code }}</code></td>
                                    <td><strong>{{ $order->plan->name ?? 'Gói Bản Quyền' }}</strong></td>
                                    <td><strong style="color: #1B5E20;">{{ number_format($order->amount, 0, ',', '.') }}đ</strong></td>
                                    <td><span style="font-size: 0.75rem; text-transform: uppercase;">{{ $order->payment_method }}</span></td>
                                    <td>
                                        @if ($order->isPaid())
                                            <span class="mayhen_admin_badge mayhen_admin_badge_success">Đã Thanh Toán</span>
                                        @elseif ($order->status === 'cancelled')
                                            <span class="mayhen_admin_badge mayhen_admin_badge_danger">Đã Hủy</span>
                                        @else
                                            <span class="mayhen_admin_badge mayhen_admin_badge_warning">Chờ Thanh Toán</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                                            Chi Tiết &nearr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #7A756E; padding: 24px;">
                                        Người dùng chưa phát sinh đơn hàng thanh toán nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Profile Info & RBAC & Password -->
        <div>
            <!-- Account Summary Card -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_body" style="text-align: center;">
                    <div class="mayhen_admin_user_hero_avatar" style="margin: 0 auto 12px auto;">
                        {{ strtoupper(mb_substr($user->name ?? 'U', 0, 1, 'UTF-8')) }}
                    </div>
                    <strong style="font-size: 1.15rem; color: var(--mh-admin-charcoal);">{{ $user->name }}</strong><br>
                    <span style="font-size: 0.8125rem; color: var(--mh-admin-muted);">{{ $user->email }}</span>

                    <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--mh-admin-border-subtle); display: grid; grid-template-columns: 1fr 1fr; gap: 12px; text-align: left;">
                        <div>
                            <div style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted);">Tổng Chi Tiêu</div>
                            <div style="font-size: 1.15rem; font-weight: 700; color: #1B5E20;">{{ number_format($totalSpent, 0, ',', '.') }}đ</div>
                        </div>
                        <div>
                            <div style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted);">Lễ Cưới Đã Tạo</div>
                            <div style="font-size: 1.15rem; font-weight: 700; color: var(--mh-admin-charcoal);">{{ $user->weddings->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Permissions Card (RBAC) -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        <span>Phân Quyền Vai Trò (RBAC)</span>
                    </div>
                </div>

                <div class="mayhen_admin_card_body">
                    @if (Auth::user()?->isSuperAdmin())
                        <form action="{{ route('admin.users.change_role', $user->id) }}" method="POST">
                            @csrf
                            <div style="margin-bottom: 16px;">
                                <label for="role" class="mayhen_admin_form_label">
                                    Chọn Vai Trò Hệ Thống:
                                </label>
                                <select id="role" name="role" class="mayhen_admin_form_select">
                                    <option value="customer" {{ $user->role === 'customer' || empty($user->role) ? 'selected' : '' }}>
                                        Người Dùng / Khách Hàng (Customer)
                                    </option>
                                    <option value="partner" {{ $user->role === 'partner' ? 'selected' : '' }}>
                                        Cộng Tác Viên / Studio (Partner)
                                    </option>
                                    <option value="staff" {{ $user->role === 'staff' ? 'selected' : '' }}>
                                        Nhân Viên CSKH / Vận Hành (Staff)
                                    </option>
                                    <option value="manager" {{ $user->role === 'manager' ? 'selected' : '' }}>
                                        Quản Lý Nghiệp Vụ (Manager)
                                    </option>
                                    <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>
                                        Quản Trị Viên Cấp Cao (Admin)
                                    </option>
                                </select>
                                <div class="mayhen_admin_form_help">
                                    Cấp quyền quản trị viên sẽ cho phép tài khoản đăng nhập vào /admin.
                                </div>
                            </div>

                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="width: 100%;" onclick="return confirm('Bạn có chắc chắn muốn thay đổi vai trò phân quyền cho tài khoản này?');">
                                Cập Nhật Quyền Hạn
                            </button>
                        </form>
                    @else
                        <div style="padding: 12px; background: #FAF8F5; border-radius: 8px; border: 1px solid var(--mh-admin-border-subtle); color: var(--mh-admin-muted); font-size: 0.8125rem;">
                            Vai trò hiện tại: <strong style="color: var(--mh-admin-charcoal);">{{ $user->role_label }}</strong>.<br>
                            <span style="font-size: 0.75rem; color: #8C827A;">Chỉ Quản trị viên cấp cao mới có thẩm quyền điều chỉnh phân quyền vai trò tài khoản.</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Reset Password Card -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <span>Đặt Lại Mật Khẩu Nhanh</span>
                    </div>
                </div>

                <div class="mayhen_admin_card_body">
                    @if (! Auth::user()?->isAdmin())
                        <div style="padding: 12px; background: #FAF8F5; border-radius: 8px; border: 1px solid var(--mh-admin-border-subtle); color: var(--mh-admin-muted); font-size: 0.8125rem;">
                            Tài khoản Nhân viên không được phép đặt lại mật khẩu của người dùng.
                        </div>
                    @elseif ($user->isSuperAdmin() && ! Auth::user()?->isSuperAdmin())
                        <div style="padding: 12px; background: #FAF8F5; border-radius: 8px; border: 1px solid var(--mh-admin-border-subtle); color: var(--mh-admin-muted); font-size: 0.8125rem;">
                            Chỉ Quản trị viên cấp cao mới có quyền đặt lại mật khẩu cho tài khoản Quản trị viên.
                        </div>
                    @else
                        <form action="{{ route('admin.users.reset_password', $user->id) }}" method="POST">
                            @csrf
                            <div style="margin-bottom: 12px;">
                                <label for="password" class="mayhen_admin_form_label">Mật khẩu mới (Tối thiểu 6 ký tự):</label>
                                <input type="password" id="password" name="password" class="mayhen_admin_form_input" required minlength="6">
                            </div>

                            <div style="margin-bottom: 16px;">
                                <label for="password_confirmation" class="mayhen_admin_form_label">Xác nhận mật khẩu:</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="mayhen_admin_form_input" required minlength="6">
                            </div>

                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="width: 100%;" onclick="return confirm('Bạn có chắc chắn muốn đặt lại mật khẩu mới cho tài khoản này?');">
                                Lưu Mật Khẩu Mới
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
