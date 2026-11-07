@extends('client.layouts.master')

@section('page_title', 'Quản Lý Khách Mời & Sổ Ký Tên RSVP | ' . config('app.name'))
@section('meta_description', 'Quản lý danh sách khách mời, xuất file Excel CSV, tạo link cá nhân hóa gửi qua Zalo và theo dõi phản hồi tham dự.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/guests.css') }}">
@endpush

@section('content')
    <div class="mayhen_guest_container">
        <!-- Breadcrumbs -->
        <div class="mayhen_guest_breadcrumbs">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span class="mayhen_guest_breadcrumbs_sep">/</span>
            <a href="{{ route('account.dashboard') }}">Tài khoản</a>
            <span class="mayhen_guest_breadcrumbs_sep">/</span>
            <a href="{{ route('account.invitations') }}">Thiệp cưới</a>
            <span class="mayhen_guest_breadcrumbs_sep">/</span>
            <span class="mayhen_guest_breadcrumbs_current">Khách Mời & RSVP</span>
        </div>

        <!-- Session Feedback Alerts -->
        @if(session('success'))
            <div class="mayhen_guest_alert mayhen_guest_alert_success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mayhen_guest_alert mayhen_guest_alert_danger">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Header -->
        <div class="mayhen_guest_header_card">
            <div>
                <span class="mayhen_guest_sub_title">Sổ Theo Dõi Quan Khách</span>
                <h1 class="mayhen_guest_title">Khách Mời & Phản Hồi RSVP</h1>
                <p class="mayhen_guest_meta">
                    Lễ thành hôn: <strong>{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</strong> &bull;
                    @if($wedding->wedding_date)
                        Ngày cưới: <strong>{{ \Carbon\Carbon::parse($wedding->wedding_date)->format('d/m/Y') }}</strong>
                    @endif
                </p>
            </div>
            <div class="mayhen_guest_header_actions">
                <a href="{{ route('guests.print', $invitation->id) }}" target="_blank" class="mayhen_guest_btn mayhen_guest_btn_gold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    <span>In Bảng Điểm Danh (A4)</span>
                </a>
                <a href="{{ route('guests.export', $invitation->id) }}" class="mayhen_guest_btn mayhen_guest_btn_outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Xuất Danh Sách CSV (Excel)</span>
                </a>
                <a href="{{ route('invitation.show', $invitation->slug) }}" class="mayhen_guest_btn mayhen_guest_btn_outline" target="_blank">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span>Xem Thiệp Chính</span>
                </a>
                <a href="{{ route('invitation.create', ['edit' => $invitation->slug]) }}" class="mayhen_guest_btn mayhen_guest_btn_outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                    </svg>
                    <span>Vào Studio</span>
                </a>
            </div>
        </div>

        <!-- 4 Stats Cards Grid -->
        <div class="mayhen_guest_stats_grid">
            <div class="mayhen_guest_stat_card">
                <span class="mayhen_guest_stat_label">Tổng Số Lời Mời</span>
                <span class="mayhen_guest_stat_number">{{ number_format($totalGuestsCount) }}</span>
                <span class="mayhen_guest_stat_desc">Số thiệp gửi cá nhân hóa</span>
            </div>
            <div class="mayhen_guest_stat_card">
                <span class="mayhen_guest_stat_label">Khách Dự Kiến</span>
                <span class="mayhen_guest_stat_number">{{ number_format($totalInvited) }}</span>
                <span class="mayhen_guest_stat_desc">Ước tính người tham dự</span>
            </div>
            <div class="mayhen_guest_stat_card">
                <span class="mayhen_guest_stat_label">Xác Nhận Tham Dự</span>
                <span class="mayhen_guest_stat_number" style="color: #2E7D32;">{{ number_format($attendingCount) }}</span>
                <span class="mayhen_guest_stat_desc">Quan khách báo sẽ đến</span>
            </div>
            <div class="mayhen_guest_stat_card">
                <span class="mayhen_guest_stat_label">Từ Chối / Bận</span>
                <span class="mayhen_guest_stat_number" style="color: #C62828;">{{ number_format($declinedCount) }}</span>
                <span class="mayhen_guest_stat_desc">Không thể sắp xếp dự tiệc</span>
            </div>
        </div>

        <!-- Response Progress Bar -->
        <div class="mayhen_guest_progress_card">
            <div class="mayhen_guest_progress_header">
                <span>Tỷ lệ phản hồi RSVP của quan khách:</span>
                <strong>{{ $responseRate }}% ({{ $totalResponses }}/{{ $totalGuestsCount }} khách)</strong>
            </div>
            <div class="mayhen_guest_progress_track">
                <div class="mayhen_guest_progress_fill" style="width: {{ $responseRate }}%;"></div>
            </div>
        </div>

        <!-- Add Guest Box (Tabs: Thêm Đơn Lẻ & Nhập Nhanh Hàng Loạt) -->
        <div class="mayhen_guest_add_card">
            <div class="mayhen_guest_tab_header">
                <button type="button" class="mayhen_guest_add_tab_btn active" data-tab-target="single">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                    <span>Thêm Từng Khách Mời</span>
                </button>
                <button type="button" class="mayhen_guest_add_tab_btn" data-tab-target="batch">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="8" y1="6" x2="21" y2="6"></line>
                        <line x1="8" y1="12" x2="21" y2="12"></line>
                        <line x1="8" y1="18" x2="21" y2="18"></line>
                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                    </svg>
                    <span>Nhập Nhanh Nhiều Khách (Hàng Loạt)</span>
                </button>
            </div>

            <!-- Single Add Form -->
            <div class="mayhen_guest_tab_pane active" id="tab-single">
                <form action="{{ route('guests.store', $invitation->id) }}" method="POST">
                    @csrf
                    <div class="mayhen_guest_form_grid">
                        <div class="mayhen_guest_form_field">
                            <label class="mayhen_guest_form_label">Tên khách mời *</label>
                            <input type="text" name="name" class="mayhen_guest_form_input" required placeholder="Ví dụ: Anh Hoàng & Bạn gái">
                        </div>

                        <div class="mayhen_guest_form_field">
                            <label class="mayhen_guest_form_label">Cách xưng hô</label>
                            <select name="salutation" class="mayhen_guest_form_select">
                                <option value="Kính mời">Kính mời (Người lớn tuổi / Trang trọng)</option>
                                <option value="Thân mời" selected>Thân mời (Bạn bè / Đồng nghiệp)</option>
                                <option value="Thương mời">Thương mời (Người thân thiết)</option>
                            </select>
                        </div>

                        <div class="mayhen_guest_form_field">
                            <label class="mayhen_guest_form_label">Nhóm khách</label>
                            <input type="text" name="group_name" class="mayhen_guest_form_input" value="Bạn Bè" placeholder="Họ Nhà Trai, Bạn Đại Học...">
                        </div>

                        <div class="mayhen_guest_form_field">
                            <label class="mayhen_guest_form_label">Số người dự kiến</label>
                            <input type="number" name="expected_attendees" class="mayhen_guest_form_input" min="1" max="10" value="2">
                        </div>

                        <div class="mayhen_guest_form_field">
                            <label class="mayhen_guest_form_label">Số điện thoại (tùy chọn)</label>
                            <input type="tel" name="phone" class="mayhen_guest_form_input" placeholder="0912345678">
                        </div>

                        <div style="display: flex; align-items: flex-end;">
                            <button type="submit" class="mayhen_guest_btn mayhen_guest_btn_primary" style="height: 42px; width: 100%;">
                                + Thêm Khách
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Batch Add Form -->
            <div class="mayhen_guest_tab_pane" id="tab-batch" style="display: none;">
                <form action="{{ route('guests.batch_store', $invitation->id) }}" method="POST">
                    @csrf
                    <p style="font-size: 13px; color: #7A726A; margin: 0 0 12px 0;">
                        Dán danh sách khách mời vào khung dưới đây, mỗi dòng một khách. Định dạng hỗ trợ: <br>
                        <code>Tên khách mời | Cách xưng hô | Nhóm | Số người | Số điện thoại</code>
                    </p>
                    <textarea name="batch_input" rows="6" class="mayhen_guest_form_textarea" required placeholder="Ví dụ:
Anh Hoàng & Bạn gái | Thân mời | Bạn Đại học | 2 | 0912345678
Gia đình Chú Bảy | Kính mời | Họ Nhà Trai | 4
Bạn Minh Thảo | Thân mời | Đồng Nghiệp | 1"></textarea>

                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 14px; gap: 12px; flex-wrap: wrap;">
                        <div style="display: flex; gap: 12px; font-size: 13px;">
                            <div>
                                <label style="font-size: 12px; color: #6B635B;">Xưng hô mặc định:</label>
                                <input type="text" name="default_salutation" value="Thân mời" class="mayhen_guest_form_input" style="height: 34px; padding: 4px 10px; font-size: 13px;">
                            </div>
                            <div>
                                <label style="font-size: 12px; color: #6B635B;">Nhóm mặc định:</label>
                                <input type="text" name="default_group" value="Bạn Bè" class="mayhen_guest_form_input" style="height: 34px; padding: 4px 10px; font-size: 13px;">
                            </div>
                        </div>
                        <button type="submit" class="mayhen_guest_btn mayhen_guest_btn_primary">
                            + Nhập Hàng Loạt Vào Danh Sách
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="mayhen_guest_toolbar">
            <form action="{{ route('guests.index', $invitation->id) }}" method="GET" class="mayhen_guest_filter_form">
                <div class="mayhen_guest_search_box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="q" value="{{ $filterSearch }}" placeholder="Tìm kiếm theo tên hoặc SĐT..." class="mayhen_guest_search_input">
                </div>

                <select name="group" class="mayhen_guest_toolbar_select" onchange="this.form.submit()">
                    <option value="">-- Tất cả nhóm khách --</option>
                    @foreach($availableGroups as $grp)
                        <option value="{{ $grp }}" {{ $filterGroup === $grp ? 'selected' : '' }}>{{ $grp }}</option>
                    @endforeach
                </select>

                <select name="status" class="mayhen_guest_toolbar_select" onchange="this.form.submit()">
                    <option value="">-- Tất cả trạng thái RSVP --</option>
                    <option value="attending" {{ $filterStatus === 'attending' ? 'selected' : '' }}>Đã xác nhận tham dự</option>
                    <option value="pending" {{ $filterStatus === 'pending' ? 'selected' : '' }}>Chưa gửi phản hồi</option>
                    <option value="declined" {{ $filterStatus === 'declined' ? 'selected' : '' }}>Từ chối / Không tham gia</option>
                </select>

                @if($filterSearch || $filterGroup || $filterStatus)
                    <a href="{{ route('guests.index', $invitation->id) }}" class="mayhen_guest_reset_link">
                        Đặt lại
                    </a>
                @endif
            </form>

            <span style="font-size: 13px; color: #7A726A; white-space: nowrap;">
                Hiển thị: <strong>{{ $guests->count() }}</strong> khách
            </span>
        </div>

        <!-- Guests Table Card -->
        <div class="mayhen_guest_table_card">
            <div style="overflow-x: auto;">
                <table class="mayhen_guest_table">
                    <thead>
                        <tr>
                            <th>Tên Khách Mời</th>
                            <th>Xưng Hô</th>
                            <th>Nhóm</th>
                            <th>Số Dự Kiến</th>
                            <th>Phản Hồi RSVP</th>
                            <th>Công Cụ Mời Zalo</th>
                            <th style="text-align: right;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($guests as $g)
                            @php
                                $personalUrl = route('invitation.show', ['slug' => $invitation->slug, 'token' => $g->guest_token]);
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: #1F1D1A;">{{ $g->name }}</div>
                                    @if($g->phone)
                                        <div style="font-size: 11px; color: #8C827A;">{{ $g->phone }}</div>
                                    @endif
                                </td>
                                <td>{{ $g->salutation }}</td>
                                <td>
                                    <span class="mayhen_guest_group_badge">{{ $g->group_name }}</span>
                                </td>
                                <td>{{ $g->expected_attendees }} người</td>
                                <td>
                                    @if ($g->response)
                                        @if ($g->response->attending_status === 'attending')
                                            <span class="mayhen_guest_badge_attending">
                                                Tham gia ({{ $g->response->adults_count + $g->response->children_count }} người)
                                            </span>
                                            @if($g->response->wishes)
                                                <div style="font-size: 11px; color: #6B635B; margin-top: 4px; font-style: italic;">
                                                    "{{ Str::limit($g->response->wishes, 35) }}"
                                                </div>
                                            @endif
                                        @else
                                            <span class="mayhen_guest_badge_declined">Không tham gia</span>
                                        @endif
                                    @else
                                        <span class="mayhen_guest_badge_pending">Chưa phản hồi</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="mayhen_guest_actions_cell">
                                        <button type="button" class="mayhen_guest_action_btn mayhen_guest_action_zalo"
                                            data-mayhen-action="copy-zalo"
                                            data-guest-name="{{ $g->name }}"
                                            data-guest-salutation="{{ $g->salutation }}"
                                            data-guest-group="{{ $g->group_name }}"
                                            data-guest-link="{{ $personalUrl }}"
                                            data-couple-names="{{ $wedding->groom_name }} & {{ $wedding->bride_name }}"
                                            data-wedding-date="{{ $wedding->wedding_date ? \Carbon\Carbon::parse($wedding->wedding_date)->format('d.m.Y') : '' }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                            </svg>
                                            Chép Mời Zalo
                                        </button>
                                        <button type="button" class="mayhen_guest_action_btn mayhen_guest_action_link"
                                            data-mayhen-action="copy-link"
                                            data-guest-link="{{ $personalUrl }}">
                                            Copy Link
                                        </button>
                                        <a href="{{ $personalUrl }}" target="_blank" class="mayhen_guest_action_btn" style="text-decoration: none;" title="Xem thiệp với vai trò khách này">
                                            Mở
                                        </a>
                                    </div>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <!-- Nút Sửa Khách Mời -->
                                    <button type="button" class="mayhen_guest_icon_btn mayhen_guest_open_edit_btn"
                                        title="Chỉnh sửa thông tin khách"
                                        data-guest-id="{{ $g->id }}"
                                        data-guest-name="{{ $g->name }}"
                                        data-guest-salutation="{{ $g->salutation }}"
                                        data-guest-group="{{ $g->group_name }}"
                                        data-guest-phone="{{ $g->phone ?? '' }}"
                                        data-guest-attendees="{{ $g->expected_attendees }}"
                                        data-update-url="{{ route('guests.update', $g->id) }}">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                    </button>

                                    <!-- Nút Cập Nhật RSVP Thủ Công -->
                                    <button type="button" class="mayhen_guest_icon_btn mayhen_guest_icon_btn_rsvp mayhen_guest_open_rsvp_btn"
                                        title="Xác nhận RSVP hộ khách"
                                        data-guest-id="{{ $g->id }}"
                                        data-guest-name="{{ $g->name }}"
                                        data-rsvp-status="{{ $g->response ? $g->response->attending_status : 'pending' }}"
                                        data-rsvp-adults="{{ $g->response ? $g->response->adults_count : $g->expected_attendees }}"
                                        data-rsvp-children="{{ $g->response ? $g->response->children_count : 0 }}"
                                        data-rsvp-dietary="{{ $g->response ? $g->response->dietary_requirements : '' }}"
                                        data-rsvp-notes="{{ $g->response ? $g->response->wishes : '' }}"
                                        data-rsvp-url="{{ route('guests.manual_rsvp', $g->id) }}">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                        </svg>
                                    </button>

                                    <!-- Nút Xóa Khách Mời -->
                                    <form action="{{ route('guests.destroy', $g->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khách mời này khỏi danh sách?');" style="margin: 0; display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="mayhen_guest_del_btn" title="Xóa khách mời">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 48px 20px; color: #8C827A;">
                                    @if($filterSearch || $filterGroup || $filterStatus)
                                        Không tìm thấy khách mời nào phù hợp với bộ lọc hiện tại.
                                        <div style="margin-top: 10px;">
                                            <a href="{{ route('guests.index', $invitation->id) }}" style="color: #9C7A4A; font-weight: 500;">
                                                Xem toàn bộ danh sách &rarr;
                                            </a>
                                        </div>
                                    @else
                                        Chưa có khách mời nào trong danh sách. Hãy thêm khách mời đầu tiên ở khung trên!
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Modal Sửa Thông Tin Khách Mời -->
    <div id="mayhen-edit-guest-modal" class="mayhen_guest_modal" aria-hidden="true" role="dialog" aria-labelledby="mayhen-edit-modal-title">
        <div class="mayhen_guest_modal_backdrop"></div>
        <div class="mayhen_guest_modal_dialog">
            <div class="mayhen_guest_modal_header">
                <h3 id="mayhen-edit-modal-title" class="mayhen_guest_modal_title">Chỉnh Sửa Khách Mời</h3>
                <button type="button" class="mayhen_guest_modal_close" aria-label="Đóng">&times;</button>
            </div>
            <form id="mayhen-edit-guest-form" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="mayhen_guest_modal_body">
                    <div>
                        <label class="mayhen_guest_form_label">Họ và tên khách mời *</label>
                        <input type="text" name="name" id="edit-guest-name" required class="mayhen_guest_form_input">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="mayhen_guest_form_label">Cách xưng hô</label>
                            <input type="text" name="salutation" id="edit-guest-salutation" class="mayhen_guest_form_input" placeholder="Thân mời, Kính mời...">
                        </div>
                        <div>
                            <label class="mayhen_guest_form_label">Nhóm khách mời</label>
                            <input type="text" name="group_name" id="edit-guest-group" class="mayhen_guest_form_input" placeholder="Bạn Bè, Nhà Trai...">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="mayhen_guest_form_label">Số điện thoại</label>
                            <input type="text" name="phone" id="edit-guest-phone" class="mayhen_guest_form_input" placeholder="09xxxxxxx">
                        </div>
                        <div>
                            <label class="mayhen_guest_form_label">Số người dự kiến</label>
                            <input type="number" name="expected_attendees" id="edit-guest-attendees" min="1" max="20" required class="mayhen_guest_form_input">
                        </div>
                    </div>
                </div>
                <div class="mayhen_guest_modal_footer">
                    <button type="button" class="mayhen_guest_btn mayhen_guest_btn_outline mayhen_guest_modal_cancel">Hủy</button>
                    <button type="submit" class="mayhen_guest_btn mayhen_guest_btn_primary">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Xác Nhận RSVP Thủ Công Hộ Khách -->
    <div id="mayhen-rsvp-guest-modal" class="mayhen_guest_modal" aria-hidden="true" role="dialog" aria-labelledby="mayhen-rsvp-modal-title">
        <div class="mayhen_guest_modal_backdrop"></div>
        <div class="mayhen_guest_modal_dialog">
            <div class="mayhen_guest_modal_header">
                <div>
                    <h3 id="mayhen-rsvp-modal-title" class="mayhen_guest_modal_title">Cập Nhật Trạng Thái RSVP</h3>
                    <p id="mayhen-rsvp-guest-subtitle" style="font-size: 13px; color: #7A726A; margin: 2px 0 0 0;">Khách: Nguyễn Văn A</p>
                </div>
                <button type="button" class="mayhen_guest_modal_close" aria-label="Đóng">&times;</button>
            </div>
            <form id="mayhen-rsvp-guest-form" method="POST" action="">
                @csrf
                <div class="mayhen_guest_modal_body">
                    <div>
                        <label class="mayhen_guest_form_label">Trạng thái tham dự *</label>
                        <select name="attending_status" id="rsvp-status-select" required class="mayhen_guest_form_select">
                            <option value="attending">Sẽ tham gia tiệc cưới</option>
                            <option value="declined">Không thể tham gia (Từ chối)</option>
                            <option value="pending">Chưa xác nhận (Đặt lại)</option>
                        </select>
                    </div>

                    <div id="rsvp-headcount-fields" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="mayhen_guest_form_label">Số người lớn</label>
                            <input type="number" name="adults_count" id="rsvp-adults-count" min="1" max="20" class="mayhen_guest_form_input" value="1">
                        </div>
                        <div>
                            <label class="mayhen_guest_form_label">Số trẻ em đi kèm</label>
                            <input type="number" name="children_count" id="rsvp-children-count" min="0" max="20" class="mayhen_guest_form_input" value="0">
                        </div>
                    </div>

                    <div>
                        <label class="mayhen_guest_form_label">Yêu cầu đặc biệt về ẩm thực (nếu có)</label>
                        <input type="text" name="dietary_requirements" id="rsvp-dietary" class="mayhen_guest_form_input" placeholder="Ăn chay, dị ứng hải sản...">
                    </div>

                    <div>
                        <label class="mayhen_guest_form_label">Ghi chú hoặc lời nhắn hộ khách</label>
                        <textarea name="notes" id="rsvp-notes" rows="2" class="mayhen_guest_form_textarea" placeholder="Bác báo đi cùng con gái..."></textarea>
                    </div>
                </div>
                <div class="mayhen_guest_modal_footer">
                    <button type="button" class="mayhen_guest_btn mayhen_guest_btn_outline mayhen_guest_modal_cancel">Hủy</button>
                    <button type="submit" class="mayhen_guest_btn mayhen_guest_btn_primary">Cập Nhật Trạng Thái</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Thẻ Mời Zalo & Messenger Khắc Tên (TASK-V1-05: Social Share Card) -->
    <div id="mayhen-share-zalo-modal" class="mayhen_guest_modal" aria-hidden="true" role="dialog" aria-labelledby="mayhen-share-modal-title">
        <div class="mayhen_guest_modal_backdrop"></div>
        <div class="mayhen_guest_modal_dialog" style="max-width: 560px;">
            <div class="mayhen_guest_modal_header">
                <div>
                    <h3 id="mayhen-share-modal-title" class="mayhen_guest_modal_title">Thẻ Mời Zalo & Messenger Khắc Tên</h3>
                    <p style="font-size: 13px; color: #7A726A; margin: 2px 0 0 0;">Lời mời cá nhân hóa gửi riêng cho từng vị khách quý</p>
                </div>
                <button type="button" class="mayhen_guest_modal_close" aria-label="Đóng">&times;</button>
            </div>
            <div class="mayhen_guest_modal_body">
                <!-- Thẻ xem trước bưu thiếp mini (Mini Invitation Card Preview) -->
                <div class="mayhen_guest_share_card_preview" style="background: linear-gradient(145deg, #FAF8F5 0%, #F5EFEB 100%); border: 1px solid #E5DCCD; border-radius: 12px; padding: 18px 20px; box-shadow: inset 0 0 0 1px rgba(197, 168, 128, 0.25);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px dashed #D8CEBD; padding-bottom: 8px;">
                        <span style="font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: #9C7A4A; font-weight: 600;">MÂY HẸN • SAVE OUR DATE</span>
                        <span id="share-card-group" class="mayhen_guest_group_badge" style="background: #EFE8DD; color: #6B5E4B; font-size: 11px;">Nhóm: Bạn Bè</span>
                    </div>
                    <div style="text-align: center; margin: 10px 0;">
                        <div style="font-size: 13px; color: #7A726A; font-style: italic;">Trân trọng kính mời</div>
                        <h4 id="share-card-guest-name" style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 22px; font-weight: 700; color: #1F1D1A; margin: 4px 0 4px 0;">Nguyễn Văn A</h4>
                        <div style="font-size: 13px; color: #9C7A4A; font-weight: 500;">Tới dự lễ thành hôn của</div>
                        <div id="share-card-couple" style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 19px; color: #1F1D1A; font-weight: 600; margin-top: 2px;">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</div>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: center; gap: 14px; font-size: 12px; color: #5A534B; border-top: 1px dashed #D8CEBD; padding-top: 8px; margin-top: 10px; flex-wrap: wrap;">
                        <span>📅 <strong id="share-card-date">{{ $wedding->wedding_date ? \Carbon\Carbon::parse($wedding->wedding_date)->format('d.m.Y') : 'Ngày trọng đại' }}</strong></span>
                        @if($wedding->venue_name)
                            <span>📍 <strong>{{ $wedding->venue_name }}</strong></span>
                        @endif
                    </div>
                </div>

                <!-- Bộ chọn 3 phong cách lời nhắn -->
                <div>
                    <label class="mayhen_guest_form_label" style="margin-bottom: 6px;">Chọn phong cách lời mời:</label>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                        <button type="button" class="mayhen_guest_share_style_btn is-active" data-style="formal" style="padding: 8px 6px; border-radius: 8px; border: 1px solid #C5A880; background: #FAF8F5; font-size: 12px; font-weight: 600; color: #1F1D1A; cursor: pointer; text-align: center;">
                            🌟 Trang Trọng
                        </button>
                        <button type="button" class="mayhen_guest_share_style_btn" data-style="intimate" style="padding: 8px 6px; border-radius: 8px; border: 1px solid #E5DCCD; background: #FFF; font-size: 12px; color: #5A534B; cursor: pointer; text-align: center;">
                            💖 Thân Mật
                        </button>
                        <button type="button" class="mayhen_guest_share_style_btn" data-style="warm" style="padding: 8px 6px; border-radius: 8px; border: 1px solid #E5DCCD; background: #FFF; font-size: 12px; color: #5A534B; cursor: pointer; text-align: center;">
                            🕊️ Lịch Thiệp
                        </button>
                    </div>
                </div>

                <!-- Ô xem và sửa tin nhắn -->
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <label class="mayhen_guest_form_label" style="margin: 0;">Nội dung tin nhắn sẽ gửi:</label>
                        <span id="share-copy-toast" style="font-size: 12px; color: #2E7D32; font-weight: 500; display: none;">✓ Đã sao chép vào bộ nhớ tạm!</span>
                    </div>
                    <textarea id="share-message-text" rows="5" class="mayhen_guest_form_textarea" style="font-size: 13px; line-height: 1.5; font-family: inherit; resize: vertical;"></textarea>
                </div>
            </div>
            <div class="mayhen_guest_modal_footer" style="flex-wrap: wrap; justify-content: space-between; gap: 10px;">
                <button type="button" class="mayhen_guest_btn mayhen_guest_btn_outline mayhen_guest_modal_cancel">Đóng</button>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" id="share-copy-link-only-btn" class="mayhen_guest_btn mayhen_guest_btn_outline" style="font-size: 13px;">
                        Copy Link
                    </button>
                    <button type="button" id="share-copy-message-btn" class="mayhen_guest_btn mayhen_guest_btn_primary" style="font-size: 13px;">
                        📋 Sao Chép Lời Mời
                    </button>
                    <a id="share-open-zalo-btn" href="https://zalo.me/" target="_blank" rel="noopener noreferrer" class="mayhen_guest_btn" style="background: #0068FF; color: #FFF; border: none; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        Mở Zalo
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/guests.js') }}"></script>
@endpush
