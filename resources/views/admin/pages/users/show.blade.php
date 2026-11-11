@extends('admin.layouts.master')

@section('title', 'Tài Khoản: ' . $user->name . '')
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
                @if ($user->isLocked())
                    <span class="mayhen_admin_badge mayhen_admin_badge_danger" style="margin-left: 6px;">Tài Khoản Đang Bị Khóa</span>
                @else
                    <span class="mayhen_admin_badge mayhen_admin_badge_success" style="margin-left: 6px;">Hoạt Động</span>
                @endif
            </div>
            <div class="mayhen_admin_page_subtitle">
                Email: <strong>{{ $user->email }}</strong> &bull; SĐT: <strong>{{ $user->phone ?? 'Chưa cập nhật' }}</strong> &bull; Tham gia: <strong>{{ $user->created_at->format('d/m/Y') }}</strong>
                @if ($user->isLocked())
                    &bull; <span style="color: #C62828;">Khóa lúc: {{ $user->locked_at ? $user->locked_at->format('d/m/Y H:i') : 'N/A' }} ({{ $user->lock_reason }})</span>
                @endif
            </div>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            {{-- Nút Chế độ xem dưới góc nhìn khách hàng (Mục 17.2 PRD V3) --}}
            @if(Auth::id() !== $user->id)
                <button type="button" class="mayhen_admin_btn" style="background: linear-gradient(135deg, #C5A880 0%, #9C7A4A 100%); color: #FFFFFF;" onclick="document.getElementById('mayhen_admin_impersonate_modal').style.display='flex';" title="Xem dưới góc nhìn khách hàng kèm kiểm toán chặt chẽ">
                    🛡️ Xem Dưới Góc Nhìn Khách
                </button>
            @endif

            @if (Auth::user()?->isSuperAdmin())
                <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" onclick="document.getElementById('mayhen_admin_dsar_modal').style.display='flex';" title="Trích xuất toàn bộ dữ liệu cá nhân JSON theo Luật 91/2025/QH15">
                    📥 Xuất Dữ Liệu PII (DSAR)
                </button>
            @endif
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

            <!-- Support Tickets Card (One-Case Timeline - Innovation #5) -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--mh-admin-gold);">
                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                        </svg>
                        <span>Yêu Cầu Hỗ Trợ (Support Tickets — {{ count($supportTickets ?? []) }})</span>
                    </div>
                    <a href="{{ route('admin.support.index', ['search' => $user->name]) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                        Xem tất cả &rarr;
                    </a>
                </div>

                <div class="mayhen_admin_table_responsive">
                    <table class="mayhen_admin_table">
                        <thead>
                            <tr>
                                <th>Mã Ticket</th>
                                <th>Tiêu Đề</th>
                                <th>Phân Loại</th>
                                <th>Ưu Tiên</th>
                                <th>Trạng Thái</th>
                                <th>Thời Gian</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($supportTickets as $t)
                                <tr>
                                    <td><code>{{ $t->ticket_code }}</code></td>
                                    <td><strong>{{ Str::limit($t->subject, 35) }}</strong></td>
                                    <td><span class="mayhen_admin_badge mayhen_admin_badge--neutral">{{ $t->category_label }}</span></td>
                                    <td><span class="mayhen_admin_badge {{ $t->priority_badge_class }}">{{ $t->priority_label }}</span></td>
                                    <td><span class="mayhen_admin_badge {{ $t->status_badge_class }}">{{ $t->status_label }}</span></td>
                                    <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('admin.support.show', $t->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                                            Xem &nearr;
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; color: #7A756E; padding: 24px;">
                                        Khách hàng chưa gửi ticket hỗ trợ nào.
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

            <!-- Account Security & Lock / Unlock Status Card (ADM-A04-014 / 015) -->
            <div class="mayhen_admin_card" style="{{ $user->isLocked() ? 'border-color: #FFCDD2; background-color: #FFF9F9;' : '' }}">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: {{ $user->isLocked() ? '#C62828' : 'var(--mh-admin-gold)' }};">
                            @if ($user->isLocked())
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            @else
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
                            @endif
                        </svg>
                        <span>Trạng Thái Tài Khoản</span>
                    </div>
                </div>

                <div class="mayhen_admin_card_body">
                    @if ($user->isLocked())
                        <div style="padding: 12px; background: #FFEBEE; border-radius: 8px; border: 1px solid #FFCDD2; color: #C62828; margin-bottom: 14px; font-size: 0.8125rem;">
                            <strong>Tài khoản này đang bị khóa!</strong><br>
                            Lý do: <em>{{ $user->lock_reason }}</em><br>
                            Thời gian khóa: {{ $user->locked_at ? $user->locked_at->format('d/m/Y H:i:s') : 'N/A' }}
                        </div>

                        @if (Auth::user()?->isAdmin())
                            <form action="{{ route('admin.users.unlock', $user->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="width: 100%;" onclick="return confirm('Bạn có chắc chắn muốn mở khóa tài khoản này?');">
                                    Mở Khóa Tài Khoản
                                </button>
                            </form>
                        @endif
                    @else
                        <div style="font-size: 0.8125rem; color: #2E7D32; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: #2E7D32;"></span>
                            Tài khoản đang hoạt động bình thường và được phép đăng nhập.
                        </div>

                        @if (Auth::user()?->isAdmin() && $user->id !== Auth::id())
                            <form action="{{ route('admin.users.lock', $user->id) }}" method="POST">
                                @csrf
                                <div style="margin-bottom: 10px;">
                                    <input 
                                        type="text" 
                                        name="reason" 
                                        class="mayhen_admin_form_input" 
                                        placeholder="Nhập lý do khóa (Spam, vi phạm điều khoản...)" 
                                        style="font-size: 0.8125rem; padding: 7px 10px;"
                                    >
                                </div>
                                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_sm" style="width: 100%; color: #C62828; background: #FFEBEE; border-color: #FFCDD2;" onclick="return confirm('Bạn có chắc chắn muốn khóa tài khoản này không? Người dùng sẽ không thể đăng nhập.');">
                                    Khóa Tài Khoản Này
                                </button>
                            </form>
                        @elseif ($user->id === Auth::id())
                            <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                                Đây là tài khoản của chính bạn (không thể tự khóa).
                            </div>
                        @endif
                    @endif
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

            <!-- Privacy & Retention Map Card (Innovation #14 - Luật 91/2025/QH15) -->
            <div class="mayhen_admin_card" style="border-left: 4px solid #1565C0;">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: #1565C0;">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        <span>Quyền Dữ Liệu (Luật 91/2025/QH15)</span>
                    </div>
                </div>

                <div class="mayhen_admin_card_body">
                    <div style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 16px;">
                        Quản trị vòng đời dữ liệu cá nhân, quyền tiếp cận và quyền được quên theo Luật Bảo vệ dữ liệu cá nhân.
                    </div>

                    <!-- DSAR Export Button & Form -->
                    <div style="margin-bottom: 16px;">
                        @if (Auth::user()?->isSuperAdmin())
                            <button type="button" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="width: 100%; justify-content: center;" onclick="document.getElementById('mayhen_admin_dsar_modal').style.display='flex';">
                                📥 Trích Xuất Dữ Liệu Cá Nhân (DSAR JSON)
                            </button>
                            <div style="font-size: 0.75rem; color: #8C827A; margin-top: 4px;">
                                Yêu cầu lý do kiểm toán, tự động che mờ số tài khoản ngân hàng và SĐT theo Luật 91/2025/QH15.
                            </div>
                        @else
                            <div style="padding: 10px; background: #FAF8F5; border-radius: 6px; font-size: 0.75rem; color: var(--mh-admin-muted);">
                                🔒 Chỉ Quản trị viên cấp cao (Super Admin) mới có thẩm quyền trích xuất dữ liệu cá nhân DSAR.
                            </div>
                        @endif
                    </div>

                    <!-- Anonymize Action -->
                    @if (Auth::user()?->isSuperAdmin() && ! $user->isAdmin())
                        <div style="padding-top: 14px; border-top: 1px dashed var(--mh-admin-border);">
                            <form action="{{ route('admin.users.anonymize', $user->id) }}" method="POST" onsubmit="return confirm('CẢNH BÁO BẢO MẬT:\n\nBạn sắp ẩn danh hóa toàn bộ thông tin cá nhân của người dùng này theo Luật 91/2025/QH15.\n\n- Họ tên, Email, SĐT và tài khoản ngân hàng sẽ bị xóa/che vĩnh viễn.\n- Sổ cái đơn hàng và thanh toán vẫn được giữ lại để đối soát kế toán.\n\nBạn có CHẮC CHẮN muốn tiếp tục?');">
                                @csrf
                                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--danger" style="width: 100%; justify-content: center;">
                                    🛡️ Ẩn Danh Hóa PII (Quyền Được Quên)
                                </button>
                                <div style="font-size: 0.75rem; color: #C62828; margin-top: 4px;">
                                    Hành động không thể đảo ngược. Chỉ áp dụng khi có yêu cầu hợp lệ từ chủ thể dữ liệu.
                                </div>
                            </form>
                        </div>
                    @elseif ($user->isAdmin())
                        <div style="font-size: 0.75rem; color: var(--mh-admin-muted); font-style: italic;">
                            Không thể ẩn danh hóa tài khoản Quản trị viên/Quản lý.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal DSAR Access Request (NXT-02 - Luật 91/2025/QH15) -->
    @if (Auth::user()?->isSuperAdmin())
        <div id="mayhen_admin_dsar_modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
            <div style="background: #FFFFFF; border-radius: 12px; width: 90%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden;">
                <div style="background: var(--mh-admin-surface); padding: 16px 20px; border-bottom: 1px solid var(--mh-admin-border); display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-weight: 700; font-size: 1rem; color: var(--mh-admin-charcoal);">
                        📥 Trích Xuất Dữ Liệu Cá Nhân DSAR
                    </div>
                    <button type="button" onclick="document.getElementById('mayhen_admin_dsar_modal').style.display='none';" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: var(--mh-admin-muted);">&times;</button>
                </div>

                <form action="{{ route('admin.users.export_personal_data', $user->id) }}" method="GET" style="padding: 20px;">
                    <div style="font-size: 0.8125rem; color: #4B5563; line-height: 1.5; margin-bottom: 16px; background: #EFF6FF; border: 1px solid #BFDBFE; padding: 12px; border-radius: 6px;">
                        <strong>Bảo Vệ Quyền Riêng Tư (Luật 91/2025/QH15):</strong><br>
                        Hành động trích xuất hồ sơ cá nhân sẽ được ghi vào Nhật ký kiểm toán bất biến (Audit Log) kèm địa chỉ IP và danh tính của bạn.
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                            Lý Do Trích Xuất Kiểm Toán (Tối thiểu 10 ký tự) <span style="color: #DC2626;">*</span>
                        </label>
                        <textarea name="dsar_reason" required minlength="10" rows="3" class="mayhen_admin_form_input" style="width: 100%; font-size: 0.8125rem;" placeholder="VD: Khách hàng gửi yêu cầu cung cấp hồ sơ cá nhân qua email/ticket hỗ trợ..."></textarea>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                            Mã Ticket Hỗ Trợ Kèm Theo (Tùy chọn)
                        </label>
                        <input type="text" name="ticket_code" class="mayhen_admin_form_input" style="width: 100%; font-size: 0.8125rem;" placeholder="VD: TK-20261110-001">
                    </div>

                    <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" id="unmask_checkbox" name="unmask" value="1">
                        <label for="unmask_checkbox" style="font-size: 0.75rem; color: #4B5563; cursor: pointer;">
                            Mở che mờ dữ liệu (Unmasking PII — Yêu cầu kiểm toán khắt khe)
                        </label>
                    </div>

                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" onclick="document.getElementById('mayhen_admin_dsar_modal').style.display='none';">
                            Hủy Bỏ
                        </button>
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">
                            Xác Nhận & Xuất File JSON
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Modal Kích Hoạt Chế Độ Xem Dưới Góc Nhìn Khách Hàng (Mục 17.2 PRD V3) --}}
    @if(Auth::id() !== $user->id)
        <div id="mayhen_admin_impersonate_modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.55); z-index: 99999; align-items: center; justify-content: center;">
            <div style="background: #FFFFFF; border-radius: 12px; width: 90%; max-width: 520px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden;">
                <div style="background: #FEF3C7; padding: 16px 20px; border-bottom: 1px solid #FDE68A; display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-weight: 700; font-size: 1rem; color: #78350F; display: flex; align-items: center; gap: 8px;">
                        <span>🛡️</span>
                        <span>Xem Dưới Góc Nhìn Khách Hàng</span>
                    </div>
                    <button type="button" onclick="document.getElementById('mayhen_admin_impersonate_modal').style.display='none';" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #78350F;">&times;</button>
                </div>

                <form action="{{ route('admin.users.impersonate', $user->id) }}" method="POST" style="padding: 20px;">
                    @csrf
                    <div style="font-size: 0.8125rem; color: #92400E; line-height: 1.5; margin-bottom: 16px; background: #FFFBEB; border: 1px solid #FDE68A; padding: 12px; border-radius: 6px;">
                        <strong>Quy Chuẩn Bảo Mật (Mục 17.2 PRD V3):</strong><br>
                        • Phiên hỗ trợ kéo dài tối đa <strong>30 phút</strong> và tự động kết thúc.<br>
                        • Vô hiệu hóa tính năng đổi mật khẩu / thông tin thanh toán nhạy cảm.<br>
                        • Mọi thao tác bắt đầu & kết thúc đều được ghi vết kiểm toán vĩnh viễn (Audit Log).
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                            Lý Do Kích Hoạt Phiên Hỗ Trợ <span style="color: #DC2626;">*</span>
                        </label>
                        <textarea name="reason" required minlength="5" maxlength="255" rows="3" class="mayhen_admin_form_input" style="width: 100%; font-size: 0.8125rem;" placeholder="VD: Khách hàng yêu cầu kiểm tra lỗi hiển thị thiệp trên điện thoại..."></textarea>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                            Mã Ticket Hỗ Trợ (Tùy chọn)
                        </label>
                        <input type="text" name="ticket_code" class="mayhen_admin_form_input" style="width: 100%; font-size: 0.8125rem;" placeholder="VD: TK-20261111-002">
                    </div>

                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" onclick="document.getElementById('mayhen_admin_impersonate_modal').style.display='none';">
                            Hủy Bỏ
                        </button>
                        <button type="submit" class="mayhen_admin_btn" style="background: linear-gradient(135deg, #C5A880 0%, #9C7A4A 100%); color: #FFFFFF;">
                            Bắt Đầu Phiên Hỗ Trợ (30 Phút)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
