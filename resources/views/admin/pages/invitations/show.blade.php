@extends('admin.layouts.master')

@section('title', 'Chi Tiết Thiệp #' . $invitation->id . ' — Mây Hẹn')
@section('page_title', 'Chi Tiết Thiệp Cưới #' . $invitation->id)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/invitations.css') }}">
@endpush

@section('content')
    <!-- Top Action Breadcrumb Bar -->
    <div class="mayhen_admin_page_header">
        <div>
            <div class="mayhen_admin_page_title">
                <span>{{ $wedding->groom_name ?? 'Chú Rể' }} & {{ $wedding->bride_name ?? 'Cô Dâu' }}</span>
                @if ($invitation->is_published)
                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Đã Xuất Bản</span>
                @else
                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Bản Nháp</span>
                @endif
            </div>
            <div class="mayhen_admin_page_subtitle">
                Mã định danh: <code>{{ $invitation->slug }}</code> &bull; Mẫu áp dụng: <strong>{{ $template->name ?? 'Mặc định' }}</strong>
            </div>
        </div>

        <div style="display: flex; gap: 8px;">
            <a href="{{ route('admin.invitations.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                &larr; Quay Lại Danh Sách
            </a>
            <a href="{{ url('/thiep/' . $invitation->slug) }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn_primary">
                Xem Thiệp Bên Ngoài &nearr;
            </a>
        </div>
    </div>

    <!-- 2 Columns Layout: Left Phone Mockup | Right Details -->
    <div class="mayhen_admin_detail_layout">
        <!-- Left: Live Phone Mockup -->
        <div class="mayhen_admin_phone_col">
            <div class="mayhen_admin_phone_wrapper">
                <div class="mayhen_admin_phone_island"></div>
                <div class="mayhen_admin_phone_screen">
                    <iframe 
                        src="{{ url('/thiep/' . $invitation->slug . '?embed=1') }}" 
                        title="Live Preview Thiệp Cưới {{ $invitation->slug }}"
                        loading="lazy"
                    ></iframe>
                </div>
            </div>

            <!-- Administrative Actions Box -->
            <div class="mayhen_admin_phone_actions">
                <!-- Toggle Publish -->
                <form action="{{ route('admin.invitations.toggle_publish', $invitation->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="mayhen_admin_btn {{ $invitation->is_published ? 'mayhen_admin_btn_outline' : 'mayhen_admin_btn_primary' }}" style="width: 100%;">
                        {{ $invitation->is_published ? 'Ẩn Thiệp (Thu hồi về Bản Nháp)' : 'Xuất Bản Thiệp (Công Khai)' }}
                    </button>
                </form>

                <!-- Extend Expiry -->
                <div class="mayhen_admin_card" style="margin-top: 8px; margin-bottom: 0; padding: 14px;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 8px;">
                        Gia Hạn Hiển Thị Thiệp
                    </div>
                    <form action="{{ route('admin.invitations.extend', $invitation->id) }}" method="POST" style="display: flex; gap: 8px;">
                        @csrf
                        <select name="days" class="mayhen_admin_select" style="flex: 1; min-width: 0;">
                            <option value="30">+30 Ngày</option>
                            <option value="60">+60 Ngày</option>
                            <option value="90">+90 Ngày</option>
                            <option value="180">+6 Tháng</option>
                            <option value="365">+1 Năm</option>
                        </select>
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                            Gia Hạn
                        </button>
                    </form>
                    <div style="font-size: 0.6875rem; color: #7A756E; margin-top: 6px;">
                        Hạn hiện tại: <strong>{{ $invitation->expires_at ? $invitation->expires_at->format('d/m/Y H:i') : 'Vĩnh viễn / Chưa đặt' }}</strong>
                    </div>
                </div>

                <!-- Migrate Template -->
                <div class="mayhen_admin_card" style="margin-top: 8px; margin-bottom: 0; padding: 14px;">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 8px;">
                        Chuyển Đổi Mẫu Thiệp
                    </div>
                    <form action="{{ route('admin.invitations.migrate_template', $invitation->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn chuyển đổi thiệp này sang mẫu mới không?');">
                        @csrf
                        <select name="template_id" class="mayhen_admin_select" style="width: 100%; margin-bottom: 8px;">
                            @foreach ($allTemplates as $tpl)
                                <option value="{{ $tpl->id }}" {{ $invitation->template_id === $tpl->id ? 'selected' : '' }}>
                                    {{ $tpl->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm" style="width: 100%;">
                            Chuyển Mẫu
                        </button>
                    </form>
                    <div style="font-size: 0.6875rem; color: #7A756E; margin-top: 6px;">
                        Mẫu hiện tại: <strong>{{ $template->name ?? '—' }}</strong>
                    </div>
                </div>

                @php
                    $customCfg = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode($invitation->custom_config, true) ?? []);
                    $isLocked = !empty($customCfg['is_locked']);
                    $lockReason = $customCfg['lock_reason'] ?? '';
                @endphp

                <!-- Lock / Unlock Enforcement -->
                <div class="mayhen_admin_card" style="margin-top: 8px; margin-bottom: 0; padding: 14px; {{ $isLocked ? 'background-color: #FFF5F5; border-color: #FFCDD2;' : '' }}">
                    <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: {{ $isLocked ? '#C62828' : 'var(--mh-admin-muted)' }}; margin-bottom: 8px;">
                        {{ $isLocked ? 'Thiệp Đang Bị Khóa' : 'Khóa Thiệp Cưỡng Chế' }}
                    </div>

                    @if ($isLocked)
                        <div style="font-size: 0.8125rem; color: #C62828; margin-bottom: 10px; line-height: 1.4;">
                            Lý do: <strong>{{ $lockReason }}</strong>
                        </div>
                        <form action="{{ route('admin.invitations.unlock', $invitation->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn mở khóa thiệp cưới này không?');">
                            @csrf
                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary mayhen_admin_btn_sm" style="width: 100%;">
                                Mở Khóa Thiệp
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.invitations.lock', $invitation->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn khóa thiệp cưới này không? Khách truy cập sẽ thấy thông báo tạm dừng.');">
                            @csrf
                            <input 
                                type="text" 
                                name="reason" 
                                class="mayhen_admin_input" 
                                placeholder="Lý do khóa (Spam, vi phạm, tranh chấp...)" 
                                style="font-size: 0.75rem; padding: 6px 10px; margin-bottom: 8px; width: 100%;"
                            >
                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_sm" style="width: 100%; color: #C62828; background-color: #FFEBEE; border-color: #FFCDD2;">
                                Khóa Thiệp Này
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Information & Tabs -->
        <div>
            <!-- Wedding Main Info Card -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header">
                    <div class="mayhen_admin_card_title">
                        <span>Thông Tin Sự Kiện & Cặp Đôi</span>
                    </div>
                </div>
                <div class="mayhen_admin_card_body">
                    <div class="mayhen_admin_info_grid">
                        <div class="mayhen_admin_info_item">
                            <div class="mayhen_admin_info_label">Chú Rể</div>
                            <div class="mayhen_admin_info_val">{{ $wedding->groom_name ?? '—' }}</div>
                        </div>

                        <div class="mayhen_admin_info_item">
                            <div class="mayhen_admin_info_label">Cô Dâu</div>
                            <div class="mayhen_admin_info_val">{{ $wedding->bride_name ?? '—' }}</div>
                        </div>

                        <div class="mayhen_admin_info_item">
                            <div class="mayhen_admin_info_label">Ngày Cưới Chính Thức</div>
                            <div class="mayhen_admin_info_val">
                                {{ $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : 'Chưa định ngày' }}
                            </div>
                        </div>

                        <div class="mayhen_admin_info_item">
                            <div class="mayhen_admin_info_label">Chủ Sở Hữu (Tài Khoản)</div>
                            <div class="mayhen_admin_info_val">
                                @if ($wedding->user)
                                    {{ $wedding->user->name }} ({{ $wedding->user->email }})
                                @else
                                    Khách Vãng Lai
                                @endif
                            </div>
                        </div>

                        <div class="mayhen_admin_info_item">
                            <div class="mayhen_admin_info_label">Thân Phụ Mẫu Nhà Trai</div>
                            <div class="mayhen_admin_info_val">{{ $wedding->groom_parents ?? '—' }}</div>
                        </div>

                        <div class="mayhen_admin_info_item">
                            <div class="mayhen_admin_info_label">Thân Phụ Mẫu Nhà Gái</div>
                            <div class="mayhen_admin_info_val">{{ $wedding->bride_parents ?? '—' }}</div>
                        </div>

                        <div class="mayhen_admin_info_item">
                            <div class="mayhen_admin_info_label">Địa Điểm / Trung Tâm Tiệc Cưới</div>
                            <div class="mayhen_admin_info_val">{{ $wedding->venue_name ?? '—' }}</div>
                        </div>

                        <div class="mayhen_admin_info_item">
                            <div class="mayhen_admin_info_label">Địa Chỉ Sảnh Tiệc</div>
                            <div class="mayhen_admin_info_val">{{ $wedding->venue_address ?? '—' }}</div>
                        </div>

                        <div class="mayhen_admin_info_item">
                            <div class="mayhen_admin_info_label">Tài Khoản Mừng Cưới</div>
                            <div class="mayhen_admin_info_val">
                                @if ($wedding->bank_name || $wedding->bank_account_number)
                                    {{ $wedding->bank_name }} &bull; {{ $wedding->bank_account_number }}<br>
                                    <span style="font-size: 0.8125rem; color: #7A756E;">Chủ TK: {{ $wedding->bank_account_name }}</span>
                                @else
                                    Chưa thiết lập
                                @endif
                            </div>
                        </div>

                        <div class="mayhen_admin_info_item">
                            <div class="mayhen_admin_info_label">Thời Gian Tạo & Lưu Gần Nhất</div>
                            <div class="mayhen_admin_info_val" style="font-size: 0.8125rem;">
                                Tạo: {{ $invitation->created_at ? $invitation->created_at->format('d/m/Y H:i') : '—' }}<br>
                                Lưu gần nhất: {{ $invitation->last_autosaved_at ? $invitation->last_autosaved_at->format('d/m/Y H:i:s') : 'Chưa có' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabbed Interactions Card: Guests / Wishes / Revisions -->
            <div class="mayhen_admin_card">
                <div class="mayhen_admin_card_header">
                    <nav class="mayhen_admin_tabs_nav" role="tablist">
                        <button type="button" class="mayhen_admin_tab_btn active" data-tab-target="tab-guests">
                            Khách Mời ({{ $wedding->guests->count() }})
                        </button>
                        <button type="button" class="mayhen_admin_tab_btn" data-tab-target="tab-rsvp">
                            Phản Hồi RSVP ({{ $wedding->guestResponses->count() }})
                        </button>
                        <button type="button" class="mayhen_admin_tab_btn" data-tab-target="tab-wishes">
                            Lời Chúc ({{ $wedding->wishes->count() }})
                        </button>
                        <button type="button" class="mayhen_admin_tab_btn" data-tab-target="tab-gifts">
                            Sổ Mừng Cưới ({{ $wedding->gifts->count() }})
                        </button>
                        <button type="button" class="mayhen_admin_tab_btn" data-tab-target="tab-config">
                            Hồ Sơ Kỹ Thuật (Schema)
                        </button>
                    </nav>
                </div>

                <div class="mayhen_admin_card_body">
                    <!-- Tab 1: Guests List -->
                    <div id="tab-guests" class="mayhen_admin_tab_pane active">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                            <div style="font-size: 0.84375rem; color: var(--mh-admin-muted);">
                                Tổng cộng <strong>{{ $wedding->guests->count() }}</strong> khách mời đã lưu.
                            </div>
                            @if ($wedding->guests->count() > 0)
                                <a href="{{ route('admin.invitations.export_guests', $invitation->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    <span>Xuất Danh Sách Khách (CSV)</span>
                                </a>
                            @endif
                        </div>

                        <div class="mayhen_admin_table_responsive">
                            <table class="mayhen_admin_table">
                                <thead>
                                    <tr>
                                        <th>Họ & Tên</th>
                                        <th>Nhóm Khách</th>
                                        <th>Số Điện Thoại</th>
                                        <th>Trạng Thái Mời</th>
                                        <th>Đường Dẫn Riêng</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($wedding->guests as $guest)
                                        <tr>
                                            <td><strong>{{ $guest->name }}</strong></td>
                                            <td><span class="mayhen_admin_badge mayhen_admin_badge_muted">{{ $guest->group_name ?? 'Chung' }}</span></td>
                                            <td>{{ $guest->phone ?? '—' }}</td>
                                            <td>
                                                <span class="mayhen_admin_badge {{ $guest->is_sent ? 'mayhen_admin_badge_success' : 'mayhen_admin_badge_warning' }}">
                                                    {{ $guest->is_sent ? 'Đã gửi thiệp' : 'Chưa gửi' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($guest->token)
                                                    <a href="{{ url('/thiep/' . $invitation->slug . '?token=' . $guest->token) }}" target="_blank" class="mayhen_admin_inv_slug_pill">
                                                        Mở link khách &nearr;
                                                    </a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" style="text-align: center; color: #7A756E; padding: 24px;">
                                                Cặp đôi chưa tạo danh sách khách mời riêng trên hệ thống.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: RSVP Responses -->
                    <div id="tab-rsvp" class="mayhen_admin_tab_pane">
                        <div class="mayhen_admin_table_responsive">
                            <table class="mayhen_admin_table">
                                <thead>
                                    <tr>
                                        <th>Tên Quan Khách</th>
                                        <th>Tham Dự</th>
                                        <th>Số Người</th>
                                        <th>Số Điện Thoại</th>
                                        <th>Lời Nhắn / Chế Độ Ăn</th>
                                        <th>Thời Gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($wedding->guestResponses as $resp)
                                        <tr>
                                            <td><strong>{{ $resp->guest_name }}</strong></td>
                                            <td>
                                                @if ($resp->attending === 'yes')
                                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">Có Tham Dự</span>
                                                @elseif ($resp->attending === 'no')
                                                    <span class="mayhen_admin_badge mayhen_admin_badge_danger">Không Tham Dự</span>
                                                @else
                                                    <span class="mayhen_admin_badge mayhen_admin_badge_warning">Chưa Chắc Chắn</span>
                                                @endif
                                            </td>
                                            <td>{{ $resp->number_of_guests ?? 1 }} người</td>
                                            <td>{{ $resp->phone ?? '—' }}</td>
                                            <td>{{ $resp->dietary_requirements ?? '—' }}</td>
                                            <td>{{ $resp->created_at ? $resp->created_at->format('d/m/Y H:i') : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" style="text-align: center; color: #7A756E; padding: 24px;">
                                                Chưa có phản hồi xác nhận RSVP nào từ khách mời.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 3: Wishes -->
                    <div id="tab-wishes" class="mayhen_admin_tab_pane">
                        <div class="mayhen_admin_table_responsive">
                            <table class="mayhen_admin_table">
                                <thead>
                                    <tr>
                                        <th>Người Chúc</th>
                                        <th>Nội Dung Lời Chúc</th>
                                        <th>Thời Gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($wedding->wishes as $wish)
                                        <tr>
                                            <td><strong>{{ $wish->sender_name }}</strong></td>
                                            <td>{{ $wish->message }}</td>
                                            <td>{{ $wish->created_at ? $wish->created_at->format('d/m/Y H:i') : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" style="text-align: center; color: #7A756E; padding: 24px;">
                                                Chưa có lời chúc nào được gửi cho thiệp cưới này.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 4: Wedding Gifts Tracker -->
                    <div id="tab-gifts" class="mayhen_admin_tab_pane">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                            <div style="font-size: 0.84375rem; color: var(--mh-admin-muted);">
                                Tổng tiền mừng ghi nhận: <strong>{{ number_format((int) $wedding->gifts->sum('amount'), 0, ',', '.') }} đ</strong> 
                                ({{ $wedding->gifts->count() }} khoản mừng &bull; CK VietQR: {{ number_format((int) $wedding->gifts->where('payment_method', 'bank_transfer')->sum('amount'), 0, ',', '.') }} đ)
                            </div>
                            @if ($wedding->gifts->count() > 0)
                                <a href="{{ route('admin.invitations.export_gifts', $invitation->id) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="7 10 12 15 17 10"></polyline>
                                        <line x1="12" y1="15" x2="12" y2="3"></line>
                                    </svg>
                                    <span>Xuất Sổ Mừng Cưới (CSV)</span>
                                </a>
                            @endif
                        </div>

                        <div class="mayhen_admin_table_responsive">
                            <table class="mayhen_admin_table">
                                <thead>
                                    <tr>
                                        <th>Người Mừng Cưới</th>
                                        <th>Nhóm Khách</th>
                                        <th>Số Tiền</th>
                                        <th>Hình Thức</th>
                                        <th>Lời Chúc / Ghi Chú</th>
                                        <th>Trạng Thái</th>
                                        <th>Thời Gian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($wedding->gifts as $gift)
                                        <tr>
                                            <td><strong>{{ $gift->sender_name }}</strong></td>
                                            <td><span class="mayhen_admin_badge mayhen_admin_badge_muted">{{ $gift->sender_group ?? 'Chung' }}</span></td>
                                            <td><strong style="color: #9C7A4A;">{{ number_format($gift->amount, 0, ',', '.') }} đ</strong></td>
                                            <td>
                                                @if ($gift->payment_method === 'bank_transfer')
                                                    <span class="mayhen_admin_badge mayhen_admin_badge_success">VietQR</span>
                                                @elseif ($gift->payment_method === 'cash')
                                                    <span class="mayhen_admin_badge mayhen_admin_badge_primary">Tiền Mặt</span>
                                                @else
                                                    <span class="mayhen_admin_badge mayhen_admin_badge_muted">Quà Tặng</span>
                                                @endif
                                            </td>
                                            <td style="max-width: 220px; font-size: 0.75rem; color: #5A534B; font-style: italic;">
                                                {{ $gift->notes ? "\"{$gift->notes}\"" : '—' }}
                                            </td>
                                            <td>
                                                <span class="mayhen_admin_badge {{ $gift->is_acknowledged ? 'mayhen_admin_badge_success' : 'mayhen_admin_badge_muted' }}">
                                                    {{ $gift->is_acknowledged ? 'Đã cảm ơn' : 'Chưa' }}
                                                </span>
                                            </td>
                                            <td>{{ $gift->created_at ? $gift->created_at->format('d/m/Y H:i') : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" style="text-align: center; color: #7A756E; padding: 24px;">
                                                Chưa có khoản mừng cưới nào được ghi nhận cho thiệp này.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 5: Technical Schema Config -->
                    <div id="tab-config" class="mayhen_admin_tab_pane">
                        <div class="mayhen_admin_info_grid">
                            <div class="mayhen_admin_info_item">
                                <div class="mayhen_admin_info_label">Schema Version</div>
                                <div class="mayhen_admin_info_val"><code>{{ $invitation->schema_version ?? '2.0.0' }}</code></div>
                            </div>
                            <div class="mayhen_admin_info_item">
                                <div class="mayhen_admin_info_label">Template Version</div>
                                <div class="mayhen_admin_info_val"><code>{{ $invitation->template_version ?? '1.0.0' }}</code></div>
                            </div>
                            <div class="mayhen_admin_info_item">
                                <div class="mayhen_admin_info_label">Revision ID</div>
                                <div class="mayhen_admin_info_val">#{{ $invitation->revision_id ?? 1 }}</div>
                            </div>
                            <div class="mayhen_admin_info_item">
                                <div class="mayhen_admin_info_label">Trạng Thái Bản Quyền Gói</div>
                                <div class="mayhen_admin_info_val">
                                    @if ($invitation->orders->where('status', 'paid')->isNotEmpty())
                                        <span class="mayhen_admin_badge mayhen_admin_badge_success">Đã Nâng Cấp Gói</span>
                                    @else
                                        <span class="mayhen_admin_badge mayhen_admin_badge_muted">Gói Miễn Phí / Chưa Thanh Toán</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tabBtns = document.querySelectorAll('.mayhen_admin_tab_btn');
        const tabPanes = document.querySelectorAll('.mayhen_admin_tab_pane');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-tab-target');
                tabBtns.forEach(b => b.classList.remove('active'));
                tabPanes.forEach(p => p.classList.remove('active'));

                btn.classList.add('active');
                const targetPane = document.getElementById(targetId);
                if (targetPane) targetPane.classList.add('active');
            });
        });
    });
</script>
@endpush

