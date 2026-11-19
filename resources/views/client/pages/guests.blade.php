@extends('client.layouts.master')

@php
    $guestsBrandName = \App\Models\PlatformSetting::getBrandName();
    $guestsBrandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
@endphp

@section('title', 'Quản Lý Khách Mời & Phản Hồi RSVP — ' . $wedding->groom_name . ' & ' . $wedding->bride_name . ' | ' . $guestsBrandName)
@section('page_title', 'Quản Lý Khách Mời & Sổ Ký Tên RSVP | ' . $guestsBrandName)
@section('meta_description', 'Quản lý danh sách khách mời, xuất file Excel CSV, tạo link cá nhân hóa gửi qua Zalo và theo dõi phản hồi tham dự.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/guests.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/client/css/components/client-ops-tour.css') }}">
@endpush

@section('content')
    <div class="mayhen_guest_container"
        data-ops-tour="guest-crm"
        data-user-id="{{ auth()->id() ?? 'guest' }}"
        data-invitation-id="{{ $invitation->id }}"
        data-presend-url="{{ route('guests.pre_send_validation', $invitation->id) }}"
        data-reminder-url="{{ route('guests.reminder_planner', $invitation->id) }}"
        data-thankyou-url="{{ route('guests.thankyou_builder', $invitation->id) }}"
        data-delivery-history-url="{{ route('guests.delivery.history', $invitation->id) }}"
        data-all-guest-ids='@json($allGuestIds ?? [])'
        data-unsent-guest-ids='@json($unsentGuestIds ?? [])'
    >
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

        @if(session('warning'))
            <div class="mayhen_guest_alert mayhen_guest_alert_warning" style="background: #FFF8E1; border: 1px solid #FFE082; color: #8D6E63; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F57F17" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
                <span style="font-size: 13.5px; font-weight: 500;">{{ session('warning') }}</span>
            </div>
        @endif

        <!-- Header -->
        <div class="mayhen_guest_header_card" data-ops-tour-anchor="guest-header">
            <div class="mayhen_guest_header_top">
                <div class="mayhen_guest_header_info">
                    <span class="mayhen_guest_sub_title">Sổ Theo Dõi Quan Khách</span>
                    <h1 class="mayhen_guest_title">Khách Mời &amp; Phản Hồi RSVP</h1>
                    <p class="mayhen_guest_meta">
                        Lễ thành hôn: <strong>{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}</strong>
                        @if($wedding->wedding_date)
                            &bull; Ngày cưới: <strong>{{ \Carbon\Carbon::parse($wedding->wedding_date)->format('d/m/Y') }}</strong>
                        @endif
                    </p>
                </div>
                <div class="mayhen_guest_header_utility">
                    <button type="button" id="mayhen_guest_tour_trigger_btn" class="mayhen_guest_btn mayhen_guest_btn_outline mayhen_guest_tour_btn" aria-label="Mở hướng dẫn sử dụng quản lý khách mời">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <span>Hướng dẫn</span>
                    </button>
                    <a href="{{ route('invitation.show', $invitation->slug) }}" class="mayhen_guest_btn mayhen_guest_btn_outline" target="_blank" rel="noopener">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span>Xem Thiệp</span>
                    </a>
                </div>
            </div>

            <div class="mayhen_guest_header_actions" data-ops-tour-anchor="guest-primary-actions">
                <button type="button" id="btn-open-presend-modal" class="mayhen_guest_btn mayhen_guest_btn_gold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <span>Soát Lỗi Trước Khi Gửi</span>
                </button>
                <button type="button" id="btn-open-delivery-modal" class="mayhen_guest_btn mayhen_guest_btn_gold">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                    <span>Theo Dõi Gửi Thiệp</span>
                </button>
                <a href="{{ route('seating.index', $invitation->id) }}" class="mayhen_guest_btn mayhen_guest_btn_outline" data-ops-tour-anchor="guest-seating-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="4"></circle>
                    </svg>
                    <span>Sơ Đồ Bàn Tiệc</span>
                </a>
                <a href="{{ route('checkin.portal', $invitation->slug) }}" target="_blank" rel="noopener" class="mayhen_guest_btn mayhen_guest_btn_outline" data-ops-tour-anchor="guest-checkin-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span>Cổng Quét QR Tiếp Tân</span>
                </a>
                <a href="{{ route('guests.export', array_filter(['id' => $invitation->id, 'q' => $filterSearch, 'group' => $filterGroup, 'side' => $filterSide ?? '', 'table' => $filterTable ?? '', 'status' => $filterStatus])) }}" class="mayhen_guest_btn mayhen_guest_btn_outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Xuất CSV (Excel)</span>
                </a>
                <a href="{{ route('guests.print', array_filter(['id' => $invitation->id, 'q' => $filterSearch, 'group' => $filterGroup, 'side' => $filterSide ?? '', 'table' => $filterTable ?? '', 'status' => $filterStatus])) }}" target="_blank" rel="noopener" class="mayhen_guest_btn mayhen_guest_btn_outline">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    <span>In Danh Sách (A4)</span>
                </a>
            </div>

            <details class="mayhen_guest_more_tools" id="mayhen_guest_more_tools">
                <summary class="mayhen_guest_more_tools_summary">
                    <span>Công cụ mở rộng (Nhắc hẹn, Cảm ơn, Lọc trùng, Cổng gia đình, Báo bếp...)</span>
                    <span class="mayhen_guest_more_tools_caret" aria-hidden="true">▾</span>
                </summary>
                <div class="mayhen_guest_more_tools_grid">
                    <button type="button" id="btn-open-reminder-modal" class="mayhen_guest_btn mayhen_guest_btn_outline">
                        <span>🔔 Nhắc Nhở Tế Nhị</span>
                    </button>
                    <button type="button" id="btn-open-thankyou-modal" class="mayhen_guest_btn mayhen_guest_btn_outline">
                        <span>💝 Lời Cảm Ơn Sau Cưới</span>
                    </button>
                    <a href="{{ route('account.duplicate_guests.index', $invitation->id) }}" class="mayhen_guest_btn mayhen_guest_btn_outline">
                        <span>🔍 Lọc Trùng &amp; Gộp Khách</span>
                    </a>
                    <a href="{{ route('account.household.index', $invitation->id) }}" class="mayhen_guest_btn mayhen_guest_btn_outline">
                        <span>🍽️ Hộ Gia Đình &amp; Báo Bếp</span>
                    </a>
                    <a href="{{ route('account.rsvp_history.index', $invitation->id) }}" class="mayhen_guest_btn mayhen_guest_btn_outline">
                        <span>📜 Sổ Biến Động RSVP</span>
                    </a>
                    <a href="{{ route('account.family_portal.index', $invitation->id) }}" class="mayhen_guest_btn mayhen_guest_btn_outline">
                        <span>👨‍👩‍👧‍👦 Cổng Gia Đình Hai Họ</span>
                    </a>
                    <a href="{{ route('account.smart_groups.index', $invitation->id) }}" class="mayhen_guest_btn mayhen_guest_btn_outline">
                        <span>🗂️ Thiệp Theo Nhóm Khách</span>
                    </a>
                    <a href="{{ route('coordination.index', $invitation->id) }}" class="mayhen_guest_btn mayhen_guest_btn_outline">
                        <span>📊 Điều Phối Ngày Cưới Realtime</span>
                    </a>
                </div>
            </details>
        </div>

        <!-- 6 Stats Cards Grid (Phân tách tuyệt đối Lời mời ≠ Suất dự kiến ≠ Người xác nhận ≠ Đã điểm danh) -->
        <div class="mayhen_guest_stats_grid" data-ops-tour-anchor="guest-kpi-grid">
            <div class="mayhen_guest_stat_card">
                <span class="mayhen_guest_stat_label">Số Lời Mời</span>
                <span class="mayhen_guest_stat_number">{{ number_format($totalGuestsCount) }}</span>
                <span class="mayhen_guest_stat_desc">Nhóm / hộ khách trong danh sách</span>
            </div>
            <div class="mayhen_guest_stat_card">
                <span class="mayhen_guest_stat_label">Suất Dự Kiến</span>
                <span class="mayhen_guest_stat_number">{{ number_format($totalInvited) }}</span>
                <span class="mayhen_guest_stat_desc">Tổng số người dự kiến mời</span>
            </div>
            <div class="mayhen_guest_stat_card">
                <span class="mayhen_guest_stat_label">Người Xác Nhận Đến</span>
                <span class="mayhen_guest_stat_number" style="color: #2E7D32;">{{ number_format($attendingCount) }}</span>
                <span class="mayhen_guest_stat_desc">
                    {{ number_format($guestMetrics['confirmed_adults'] ?? $attendingCount) }} người lớn &bull; {{ number_format($guestMetrics['confirmed_children'] ?? 0) }} trẻ em
                </span>
            </div>
            <div class="mayhen_guest_stat_card">
                <span class="mayhen_guest_stat_label">Báo Bận / Chờ Trả Lời</span>
                <span class="mayhen_guest_stat_number" style="color: #C62828;">{{ number_format($declinedCount) }}</span>
                <span class="mayhen_guest_stat_desc">
                    lời mời báo bận &bull; {{ number_format($guestMetrics['pending_links_count'] ?? max(0, $totalGuestsCount - $totalResponses)) }} chờ phản hồi
                </span>
            </div>
            <div class="mayhen_guest_stat_card">
                <span class="mayhen_guest_stat_label">Đã Đánh Dấu Gửi / Đã Mở</span>
                <span class="mayhen_guest_stat_number" style="color: #9C7A4A;">{{ number_format($deliveryStats['sent_guests'] ?? 0) }}/{{ number_format($totalGuestsCount) }}</span>
                <span class="mayhen_guest_stat_desc">{{ number_format($openedCount ?? 0) }} lời mời đã mở xem link</span>
            </div>
            <div class="mayhen_guest_stat_card">
                <span class="mayhen_guest_stat_label">Đã Điểm Danh Đến Tiệc</span>
                <span class="mayhen_guest_stat_number" style="color: #1D4ED8;">{{ number_format($checkedInCount) }}</span>
                <span class="mayhen_guest_stat_desc">người có mặt ({{ number_format($guestMetrics['checked_in_links_count'] ?? 0) }} lượt mời)</span>
            </div>
        </div>

        <!-- Response Progress Bar -->
        <div class="mayhen_guest_progress_card">
            <div class="mayhen_guest_progress_header">
                <span>
                    Tiến độ phản hồi của danh sách mời:
                    @if(!empty($guestMetrics['public_walkin_responses_count']))
                        <strong style="color: #9C7A4A; margin-left: 6px;">(+{{ $guestMetrics['public_walkin_responses_count'] }} phản hồi qua link chung)</strong>
                    @endif
                </span>
                <strong>{{ min(100, $responseRate) }}% ({{ $totalResponses }}/{{ $totalGuestsCount }} lời mời)</strong>
            </div>
            <div class="mayhen_guest_progress_track">
                <div class="mayhen_guest_progress_fill" style="width: {{ min(100, max(0, $responseRate)) }}%;"></div>
            </div>
        </div>

        <!-- Add Guest Box (Tabs: Thêm Đơn Lẻ & Nhập Nhanh Hàng Loạt) -->
        <div class="mayhen_guest_add_card" data-ops-tour-anchor="guest-add-box">
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
                    <span>Nhập Nhanh Nhiều Khách</span>
                </button>
                <button type="button" class="mayhen_guest_add_tab_btn" data-tab-target="csv">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="8" y1="13" x2="16" y2="13"></line>
                        <line x1="8" y1="17" x2="16" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span>Nhập Từ Tệp CSV (Excel)</span>
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
                            <label class="mayhen_guest_form_label">Bàn tiệc (tùy chọn)</label>
                            <input type="text" name="table_name" class="mayhen_guest_form_input" placeholder="Bàn 01, VIP 02...">
                        </div>

                        <div class="mayhen_guest_form_field">
                            <label class="mayhen_guest_form_label">Số người dự kiến</label>
                            <input type="number" name="expected_attendees" class="mayhen_guest_form_input" min="1" max="10" value="2">
                        </div>

                        <div class="mayhen_guest_form_field">
                            <label class="mayhen_guest_form_label">Suất tối đa (+1 Policy)</label>
                            <input type="number" name="max_attendees" class="mayhen_guest_form_input" min="1" max="20" placeholder="VD: 2 suất">
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
                        <code>Tên khách mời | Cách xưng hô | Nhóm | Số người | Số điện thoại | Bàn tiệc</code>
                    </p>
                    <textarea name="batch_input" rows="6" class="mayhen_guest_form_textarea" required placeholder="Ví dụ:
Anh Hoàng & Bạn gái | Thân mời | Bạn Đại học | 2 | 0912345678 | Bàn 08
Gia đình Chú Bảy | Kính mời | Họ Nhà Trai | 4 | | Bàn VIP 01
Bạn Minh Thảo | Thân mời | Đồng Nghiệp | 1 | 0987654321 | Bàn 05"></textarea>

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

            <!-- CSV Smart Import Form -->
            <div class="mayhen_guest_tab_pane" id="tab-csv" style="display: none;">
                <div style="background: #FDFBF7; border: 1px dashed #D8CEBD; border-radius: 8px; padding: 14px 18px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <div style="font-weight: 600; color: #2C2824; font-size: 14px; margin-bottom: 4px;">📥 Tải Tệp CSV Mẫu Chuẩn (Excel Ready)</div>
                        <p style="font-size: 12px; color: #7A726A; margin: 0; line-height: 1.5;">
                            Tệp mẫu đã cấu hình sẵn UTF-8 BOM chống lỗi font tiếng Việt trên Microsoft Excel, tự nhận diện cột Họ tên, Xưng hô, Nhóm, Số người, SĐT, Bàn tiệc và Ghi chú.
                        </p>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <a href="{{ route('guests.csv_template', $invitation->id) }}" class="mayhen_guest_btn mayhen_guest_btn_outline" style="white-space: nowrap; font-size: 13px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                            <span>Tải Tệp CSV Mẫu</span>
                        </a>
                        <a href="{{ route('docs.show', ['category' => 'guest-rsvp', 'slug' => 'CLI-GUEST-002']) }}" target="_blank" rel="noopener noreferrer" class="mayhen_guest_btn mayhen_guest_btn_outline" style="white-space: nowrap; font-size: 13px; background: #FAF7F2;" title="Xem hướng dẫn chi tiết cách lập danh sách khách và nhập hàng loạt bằng CSV">
                            📖 Hướng Dẫn Nhập CSV
                        </a>
                    </div>
                </div>

                <form action="{{ route('guests.import_csv', $invitation->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mayhen_guest_csv_grid">
                        <div class="mayhen_guest_form_field mayhen_guest_csv_file_col">
                            <label class="mayhen_guest_form_label">Chọn tệp CSV từ máy tính *</label>
                            <input type="file" name="csv_file" accept=".csv,text/csv,text/plain" required class="mayhen_guest_form_input" style="padding: 6px 10px; height: auto;">
                            <span style="font-size: 11px; color: #8C827A; margin-top: 4px; display: block;">Hệ thống tự động phát hiện dấu phân cách (dấu phẩy, dấu chấm phẩy, tab). Dung lượng tối đa 5MB.</span>
                        </div>

                        <div class="mayhen_guest_form_field">
                            <label class="mayhen_guest_form_label">Chế độ xử lý trùng lặp</label>
                            <select name="duplicate_mode" class="mayhen_guest_form_select">
                                <option value="skip" selected>Bỏ qua nếu đã tồn tại (Khuyên dùng)</option>
                                <option value="update">Cập nhật thông tin khách trùng</option>
                            </select>
                            <span style="font-size: 11px; color: #8C827A; margin-top: 4px; display: block;">Trùng khi khớp SĐT hoặc khớp (Họ tên + Nhóm).</span>
                        </div>

                        <div class="mayhen_guest_form_field">
                            <label class="mayhen_guest_form_label">Xưng hô mặc định</label>
                            <input type="text" name="default_salutation" value="Thân mời" class="mayhen_guest_form_input" placeholder="Thân mời">
                            <span style="font-size: 11px; color: #8C827A; margin-top: 4px; display: block;">Dùng khi dòng không có cột xưng hô.</span>
                        </div>

                        <div class="mayhen_guest_form_field">
                            <label class="mayhen_guest_form_label">Nhóm khách mặc định</label>
                            <input type="text" name="default_group" value="Bạn Bè" class="mayhen_guest_form_input" placeholder="Bạn Bè">
                            <span style="font-size: 11px; color: #8C827A; margin-top: 4px; display: block;">Dùng khi dòng không có cột nhóm.</span>
                        </div>

                        <div style="display: flex; align-items: flex-end;">
                            <button type="submit" class="mayhen_guest_btn mayhen_guest_btn_primary" style="height: 42px; width: 100%;">
                                + Tải Lên & Nhập Danh Sách
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Quick Dynamic Segments -->
        <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 6px; margin-bottom: 12px; scrollbar-width: thin;">
            <a href="{{ route('guests.index', $invitation->id) }}"
               style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; font-size: 12px; border-radius: 20px; border: 1px solid {{ empty($filterSide) && empty($filterStatus) && empty($filterGroup) ? '#C5A880' : '#E5DCCD' }}; background: {{ empty($filterSide) && empty($filterStatus) && empty($filterGroup) ? '#C5A880' : '#fff' }}; color: {{ empty($filterSide) && empty($filterStatus) && empty($filterGroup) ? '#fff' : '#4A453E' }}; white-space: nowrap;">
                Tất Cả ({{ $totalGuestsCount }})
            </a>
            <a href="{{ route('guests.index', ['id' => $invitation->id, 'side' => 'groom']) }}"
               style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; font-size: 12px; border-radius: 20px; border: 1px solid {{ ($filterSide ?? '') === 'groom' ? '#C5A880' : '#E5DCCD' }}; background: {{ ($filterSide ?? '') === 'groom' ? '#C5A880' : '#fff' }}; color: {{ ($filterSide ?? '') === 'groom' ? '#fff' : '#4A453E' }}; white-space: nowrap;">
                🤵 Nhà Trai
            </a>
            <a href="{{ route('guests.index', ['id' => $invitation->id, 'side' => 'bride']) }}"
               style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; font-size: 12px; border-radius: 20px; border: 1px solid {{ ($filterSide ?? '') === 'bride' ? '#C5A880' : '#E5DCCD' }}; background: {{ ($filterSide ?? '') === 'bride' ? '#C5A880' : '#fff' }}; color: {{ ($filterSide ?? '') === 'bride' ? '#fff' : '#4A453E' }}; white-space: nowrap;">
                👰 Nhà Gái
            </a>
            <a href="{{ route('guests.index', ['id' => $invitation->id, 'status' => 'pending']) }}"
               style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; font-size: 12px; border-radius: 20px; border: 1px solid {{ ($filterStatus ?? '') === 'pending' ? '#C5A880' : '#E5DCCD' }}; background: {{ ($filterStatus ?? '') === 'pending' ? '#C5A880' : '#fff' }}; color: {{ ($filterStatus ?? '') === 'pending' ? '#fff' : '#4A453E' }}; white-space: nowrap;">
                ⏳ Chưa Phản Hồi
            </a>
            <a href="{{ route('guests.index', ['id' => $invitation->id, 'status' => 'attending']) }}"
               style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; font-size: 12px; border-radius: 20px; border: 1px solid {{ ($filterStatus ?? '') === 'attending' ? '#C5A880' : '#E5DCCD' }}; background: {{ ($filterStatus ?? '') === 'attending' ? '#C5A880' : '#fff' }}; color: {{ ($filterStatus ?? '') === 'attending' ? '#fff' : '#4A453E' }}; white-space: nowrap;">
                ✅ Sẽ Tham Dự
            </a>
            <a href="{{ route('guests.index', ['id' => $invitation->id, 'status' => 'opened']) }}"
               style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; font-size: 12px; border-radius: 20px; border: 1px solid {{ ($filterStatus ?? '') === 'opened' ? '#C5A880' : '#E5DCCD' }}; background: {{ ($filterStatus ?? '') === 'opened' ? '#C5A880' : '#fff' }}; color: {{ ($filterStatus ?? '') === 'opened' ? '#fff' : '#4A453E' }}; white-space: nowrap;">
                👁 Đã Mở Thiệp
            </a>
            <a href="{{ route('guests.index', ['id' => $invitation->id, 'status' => 'checked_in']) }}"
               style="text-decoration: none; display: inline-flex; align-items: center; gap: 4px; padding: 5px 12px; font-size: 12px; border-radius: 20px; border: 1px solid {{ ($filterStatus ?? '') === 'checked_in' ? '#C5A880' : '#E5DCCD' }}; background: {{ ($filterStatus ?? '') === 'checked_in' ? '#C5A880' : '#fff' }}; color: {{ ($filterStatus ?? '') === 'checked_in' ? '#fff' : '#4A453E' }}; white-space: nowrap;">
                🎫 Đã Điểm Danh
            </a>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="mayhen_guest_toolbar" data-ops-tour-anchor="guest-filter-toolbar">
            <form action="{{ route('guests.index', $invitation->id) }}" method="GET" class="mayhen_guest_filter_form">
                <div class="mayhen_guest_search_box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="q" value="{{ $filterSearch }}" placeholder="Tìm kiếm theo tên, SĐT hoặc bàn..." class="mayhen_guest_search_input">
                </div>

                <select name="side" class="mayhen_guest_toolbar_select" onchange="this.form.submit()">
                    <option value="">-- Bên đại diện --</option>
                    <option value="groom" {{ ($filterSide ?? '') === 'groom' ? 'selected' : '' }}>🤵 Họ Nhà Trai</option>
                    <option value="bride" {{ ($filterSide ?? '') === 'bride' ? 'selected' : '' }}>👰 Họ Nhà Gái</option>
                </select>

                <select name="group" class="mayhen_guest_toolbar_select" onchange="this.form.submit()">
                    <option value="">-- Tất cả nhóm khách --</option>
                    @foreach($availableGroups as $grp)
                        <option value="{{ $grp }}" {{ $filterGroup === $grp ? 'selected' : '' }}>{{ $grp }}</option>
                    @endforeach
                </select>

                <select name="table" class="mayhen_guest_toolbar_select" onchange="this.form.submit()">
                    <option value="">-- Tất cả bàn tiệc --</option>
                    @foreach($availableTables as $tbl)
                        <option value="{{ $tbl }}" {{ ($filterTable ?? '') === $tbl ? 'selected' : '' }}>{{ $tbl }}</option>
                    @endforeach
                </select>

                <select name="status" class="mayhen_guest_toolbar_select" onchange="this.form.submit()">
                    <option value="">-- Trạng thái RSVP & Điểm danh --</option>
                    <option value="opened" {{ $filterStatus === 'opened' ? 'selected' : '' }}>👁 Đã mở xem thiệp</option>
                    <option value="not_opened" {{ $filterStatus === 'not_opened' ? 'selected' : '' }}>○ Chưa mở thiệp</option>
                    <option value="attending" {{ $filterStatus === 'attending' ? 'selected' : '' }}>Đã xác nhận tham dự</option>
                    <option value="pending" {{ $filterStatus === 'pending' ? 'selected' : '' }}>Chưa gửi phản hồi</option>
                    <option value="declined" {{ $filterStatus === 'declined' ? 'selected' : '' }}>Từ chối / Không tham gia</option>
                    <option value="checked_in" {{ $filterStatus === 'checked_in' ? 'selected' : '' }}>✓ Đã điểm danh tại sảnh</option>
                    <option value="not_checked_in" {{ $filterStatus === 'not_checked_in' ? 'selected' : '' }}>○ Chưa điểm danh</option>
                </select>

                @if($filterSearch || $filterGroup || ($filterSide ?? '') || ($filterTable ?? '') || $filterStatus)
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
        <div class="mayhen_guest_table_card" data-ops-tour-anchor="guest-table">
            <div style="overflow-x: auto;">
                <table class="mayhen_guest_table">
                    <thead>
                        <tr>
                            <th>Tên Khách Mời</th>
                            <th>Xưng Hô</th>
                            <th>Nhóm</th>
                            <th>Bàn Tiệc</th>
                            <th>Số Suất Mời</th>
                            <th>Lượt Mở Thiệp</th>
                            <th>Phản Hồi RSVP</th>
                            <th>Điểm Danh</th>
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
                                <td>
                                    @if($g->table_name)
                                        <span style="font-weight: 600; color: #9C7A4A; background: #FAF5EE; padding: 2px 8px; border-radius: 4px; font-size: 12px; border: 1px solid #EADBCE;">
                                            {{ $g->table_name }}
                                        </span>
                                    @else
                                        <span style="color: #A0988E; font-size: 12px;">Chưa xếp</span>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #1F1D1A;">{{ $g->expected_attendees }} người</div>
                                    @if($g->max_attendees)
                                        <div style="font-size: 11px; color: #9C7A4A; font-weight: 600;" title="Số suất tối đa được phép xác nhận tham dự (+1 Policy)">
                                            (Tối đa {{ $g->max_attendees }} suất)
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if ($g->isOpened())
                                        <span style="display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:99px; background:#EFF6FF; color:#1D4ED8; font-size:11px; font-weight:600;" title="Lần mở gần nhất: {{ $g->last_opened_at?->format('H:i d/m/Y') }} ({{ $g->last_device ?? 'Thiết bị lạ' }})">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            Đã xem ({{ $g->open_count }})
                                        </span>
                                        <div style="font-size: 10px; color: #64748B; margin-top: 2px;">
                                            {{ $g->last_device ?? 'Di động' }} &bull; {{ $g->last_opened_at ? $g->last_opened_at->diffForHumans() : '' }}
                                        </div>
                                    @else
                                        <span style="color: #94A3B8; font-size: 11px;">○ Chưa xem</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($g->response)
                                        @if ($g->response->attending_status === 'attending')
                                            <span class="mayhen_guest_badge_attending">
                                                Tham gia ({{ $g->response->adults_count + $g->response->children_count }} người)
                                            </span>
                                            @if($g->response->is_late)
                                                <span style="display: inline-block; padding: 1px 6px; border-radius: 4px; background: #FEF3C7; color: #92400E; font-size: 10px; font-weight: 700; margin-left: 4px;" title="Phản hồi sau ngày hạn chót">Báo muộn</span>
                                            @endif
                                            @if($g->response->needs_reconfirmation)
                                                <span style="display: inline-block; padding: 1px 6px; border-radius: 4px; background: #FEE2E2; color: #991B1B; font-size: 10px; font-weight: 700; margin-left: 4px;" title="Đám cưới đã dời lịch, cần khách gửi lại xác nhận">Chờ báo lại</span>
                                            @endif
                                            @if($g->response->wishes)
                                                <div style="font-size: 11px; color: #6B635B; margin-top: 4px; font-style: italic;">
                                                    "{{ Str::limit($g->response->wishes, 35) }}"
                                                </div>
                                            @endif
                                        @else
                                            <span class="mayhen_guest_badge_declined">Không tham gia</span>
                                            @if($g->response->is_late)
                                                <span style="display: inline-block; padding: 1px 6px; border-radius: 4px; background: #FEF3C7; color: #92400E; font-size: 10px; font-weight: 700; margin-left: 4px;" title="Phản hồi sau ngày hạn chót">Báo muộn</span>
                                            @endif
                                        @endif
                                    @else
                                        <span class="mayhen_guest_badge_pending">Chưa phản hồi</span>
                                    @endif
                                </td>
                                <td>
                                    @if($g->checked_in_at)
                                        <span style="display:inline-flex; align-items: center; gap: 3px; padding: 2px 8px; border-radius: 99px; background: #E8F5E9; color: #2E7D32; font-size: 11px; font-weight: 600;">
                                            ✓ Đã tới ({{ $g->checked_in_at->format('H:i') }})
                                        </span>
                                    @else
                                        <span style="color: #A0988E; font-size: 11px;">○ Chưa tới</span>
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
                                        <button type="button" class="mayhen_guest_action_btn mayhen_guest_toggle_sent_btn {{ $g->is_sent ? 'mayhen_guest_sent_active' : '' }}"
                                            data-guest-id="{{ $g->id }}"
                                            data-sent-url="{{ route('guests.toggle_sent', $g->id) }}"
                                            title="{{ $g->is_sent ? 'Đã gửi lúc ' . ($g->sent_at?->format('H:i d/m/Y') ?? '') . ' - Bấm để hủy' : 'Bấm để đánh dấu đã gửi thiệp' }}">
                                            <span class="mayhen_sent_dot" style="display:inline-block; width:6px; height:6px; border-radius:50%; background:{{ $g->is_sent ? '#16A34A' : '#9CA3AF' }}; margin-right:4px;"></span>
                                            <span class="mayhen_sent_text">{{ $g->is_sent ? 'Đã gửi' : 'Chưa gửi' }}</span>
                                        </button>
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
                                        data-guest-table="{{ $g->table_name ?? '' }}"
                                        data-guest-phone="{{ $g->phone ?? '' }}"
                                        data-guest-attendees="{{ $g->expected_attendees }}"
                                        data-guest-max-attendees="{{ $g->max_attendees ?? '' }}"
                                        data-update-url="{{ route('guests.update', $g->id) }}">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 20h9"></path>
                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                        </svg>
                                    </button>

                                    <!-- Nút Thu Hồi & Tái Tạo Link Mới (Section 29 Master PRD V3) -->
                                    <form action="{{ route('guests.regenerate_token', $g->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn thu hồi link thiệp cũ và cấp link mới cho khách này? Link cũ sẽ bị vô hiệu hóa tức thì.');" style="margin: 0; display: inline-block;">
                                        @csrf
                                        <button type="submit" class="mayhen_guest_icon_btn" title="Thu hồi link cũ & Tái tạo link mới">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="23 4 23 10 17 10"></polyline>
                                                <polyline points="1 20 1 14 7 14"></polyline>
                                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                            </svg>
                                        </button>
                                    </form>

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
                                <td colspan="10" style="text-align: center; padding: 48px 20px; color: #8C827A;">
                                    @if($filterSearch || $filterGroup || $filterSide || $filterTable || $filterStatus)
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
            @if(method_exists($guests, 'hasPages') && $guests->hasPages())
                <div class="mayhen_guest_pagination_bar">
                    {{ $guests->links() }}
                </div>
            @endif
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

                    <div style="margin-top: 12px;">
                        <label class="mayhen_guest_form_label">Số suất tối đa được phép xác nhận (+1 Policy)</label>
                        <input type="number" name="max_attendees" id="edit-guest-max-attendees" min="1" max="50" class="mayhen_guest_form_input" placeholder="Để trống nếu không giới hạn">
                        <div style="font-size: 11px; color: #8C827A; margin-top: 4px;">
                            Giới hạn số người khách được chọn khi phản hồi RSVP. Ví dụ: nhập 2 nếu chỉ cho phép dẫn thêm tối đa 1 người (+1).
                        </div>
                    </div>

                    <div style="margin-top: 12px;">
                        <label class="mayhen_guest_form_label">Vị trí bàn tiệc</label>
                        <input type="text" name="table_name" id="edit-guest-table" class="mayhen_guest_form_input" placeholder="Bàn 01, Bàn VIP 02, Bàn Bạn Cấp 3...">
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
                        <span style="font-size: 10px; letter-spacing: 2px; text-transform: uppercase; color: #9C7A4A; font-weight: 600;">{{ $guestsBrandNameUpper }} • SAVE OUR DATE</span>
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

    <!-- Modal Bàn Soát Lỗi Trước Khi Gửi & Trợ Lý Xưng Hô (TASK-VAL-10) -->
    <div class="mayhen_guest_modal" id="mayhen_modal_presend_validation" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="mayhen_guest_modal_backdrop" id="presend-modal-backdrop"></div>
        <div class="mayhen_guest_modal_dialog" style="max-width: 680px; max-height: 90vh; overflow-y: auto;">
            <div class="mayhen_guest_modal_header">
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #9C7A4A; font-weight: 700;">PRE-SEND VALIDATION DESK</span>
                    <h3 class="mayhen_guest_modal_title" style="margin: 2px 0 0 0;">Bàn Soát Lỗi Trước Khi Gửi Thiệp</h3>
                </div>
                <button type="button" class="mayhen_guest_modal_close" id="presend-modal-close" aria-label="Đóng">&times;</button>
            </div>
            <div class="mayhen_guest_modal_body" style="padding: 20px 24px;">
                <div id="presend-loading" style="text-align: center; padding: 40px 20px; color: #7A726A;">
                    <div style="font-size: 24px; margin-bottom: 8px;">⏳</div>
                    <span>Đang rà soát toàn bộ danh sách khách mời và các chỉ số an tâm...</span>
                </div>

                <div id="presend-content" style="display: none; flex-direction: column; gap: 20px;">
                    <!-- Thẻ Điểm Số Sẵn Sàng (Readiness Score Card) -->
                    <div style="background: linear-gradient(135deg, #FAF8F5 0%, #F5EFE6 100%); border: 1px solid #E5DCCD; border-radius: 14px; padding: 18px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;">
                        <div>
                            <span style="font-size: 12px; color: #7A726A; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px;">Chỉ số sẵn sàng gửi thiệp</span>
                            <div style="display: flex; align-items: baseline; gap: 8px; margin-top: 4px;">
                                <span id="presend-score-val" style="font-family: 'Cormorant Garamond', serif; font-size: 38px; font-weight: 700; color: #1F1D1A; line-height: 1;">100%</span>
                                <span id="presend-score-badge" style="font-size: 12px; font-weight: 600; padding: 2px 10px; border-radius: 12px; background: #E8F5E9; color: #2E7D32;">Tuyệt vời</span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 16px; font-size: 13px;">
                            <div>
                                <span style="color: #7A726A; display: block;">Khách đạt chuẩn:</span>
                                <strong id="presend-ready-count" style="color: #2E7D32; font-size: 16px;">0</strong>
                            </div>
                            <div style="border-left: 1px solid #D8CEBD; padding-left: 16px;">
                                <span style="color: #7A726A; display: block;">Cần xem lại:</span>
                                <strong id="presend-flagged-count" style="color: #C62828; font-size: 16px;">0</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách các vấn đề phát hiện -->
                    <div>
                        <h4 style="font-size: 14px; font-weight: 700; color: #1F1D1A; margin: 0 0 10px 0;">Danh Sách Điểm Cần Hoàn Thiện:</h4>
                        <div id="presend-issues-list" style="display: flex; flex-direction: column; gap: 10px;">
                            <!-- Dynamic items inserted via JS -->
                        </div>
                    </div>

                    <!-- Trợ Lý Xưng Hô Lễ Nghi (Family Etiquette Assistant) -->
                    <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 12px; padding: 16px;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <span style="font-size: 16px;">📜</span>
                            <h4 style="font-size: 13.5px; font-weight: 700; color: #1F1D1A; margin: 0;">Trợ Lý Xưng Hô Lễ Nghi (Family Etiquette Assistant)</h4>
                        </div>
                        <p style="font-size: 12.5px; color: #6E665E; margin: 0 0 12px 0; line-height: 1.5;">
                            Gợi ý danh xưng chuẩn mực văn hóa Việt tương ứng với từng nhóm khách để thiệp mời trang trọng và lễ phép nhất:
                        </p>
                        <div id="presend-etiquette-table" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; font-size: 12.5px;">
                            <!-- Dynamic suggestions -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="mayhen_guest_modal_footer" style="justify-content: flex-end;">
                <button type="button" class="mayhen_guest_btn mayhen_guest_btn_primary" id="presend-btn-done">
                    Đã Hiểu & Quay Lại Danh Sách
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Trợ Lý Nhắc Nhở RSVP Tế Nhị (TASK-VAL-16) --}}
    <div class="mayhen_guest_modal" id="mayhen_modal_reminder_planner" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="mayhen_guest_modal_backdrop" id="reminder-modal-backdrop"></div>
        <div class="mayhen_guest_modal_dialog" style="max-width: 720px; max-height: 90vh; overflow-y: auto;">
            <div class="mayhen_guest_modal_header">
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #9C7A4A; font-weight: 700;">GENTLE REMINDER PLANNER</span>
                    <h3 class="mayhen_guest_modal_title" style="margin: 2px 0 0 0;">Trợ Lý Lên Lịch Nhắc Nhở Tế Nhị</h3>
                </div>
                <button type="button" class="mayhen_guest_modal_close" id="reminder-modal-close" aria-label="Đóng">&times;</button>
            </div>
            <div class="mayhen_guest_modal_body" style="padding: 20px 24px;">
                <div id="reminder-loading" style="text-align: center; padding: 40px 20px; color: #7A726A;">
                    <div style="font-size: 24px; margin-bottom: 8px;">⏳</div>
                    <span>Đang tổng hợp danh sách khách cần nhắc nhở và chuẩn bị tin nhắn tế nhị...</span>
                </div>

                <div id="reminder-content" style="display: none; flex-direction: column; gap: 18px;">
                    {{-- Thẻ thống kê 3 nhóm khách --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
                        <div style="background: #FFF8E1; border: 1px solid #FFE082; border-radius: 10px; padding: 12px 14px;">
                            <span style="font-size: 11px; color: #8D6E63; text-transform: uppercase; font-weight: 700; display: block;">Chưa phản hồi RSVP</span>
                            <strong id="reminder-unresponsive-count" style="font-size: 20px; color: #F57F17;">0</strong>
                            <span style="font-size: 11px; color: #7A726A; display: block;">khách đã gửi thiệp</span>
                        </div>
                        <div style="background: #E8F5E9; border: 1px solid #C8E6C9; border-radius: 10px; padding: 12px 14px;">
                            <span style="font-size: 11px; color: #2E7D32; text-transform: uppercase; font-weight: 700; display: block;">Chưa gửi thiệp</span>
                            <strong id="reminder-unsent-count" style="font-size: 20px; color: #2E7D32;">0</strong>
                            <span style="font-size: 11px; color: #7A726A; display: block;">khách trong danh bạ</span>
                        </div>
                        <div style="background: #F3E5F5; border: 1px solid #E1BEE7; border-radius: 10px; padding: 12px 14px;">
                            <span style="font-size: 11px; color: #6A1B9A; text-transform: uppercase; font-weight: 700; display: block;">Chưa xếp bàn tiệc</span>
                            <strong id="reminder-unseated-count" style="font-size: 20px; color: #6A1B9A;">0</strong>
                            <span style="font-size: 11px; color: #7A726A; display: block;">khách đã xác nhận đến</span>
                        </div>
                    </div>

                    {{-- Khung giờ vàng đề xuất --}}
                    <div style="background: #FAF8F5; border: 1px dashed #C5A880; border-radius: 10px; padding: 12px 16px;">
                        <div style="display: flex; align-items: center; gap: 6px; font-weight: 700; font-size: 13px; color: #9C7A4A; margin-bottom: 4px;">
                            <span>⏰</span> <span id="reminder-timing-label">Khung Giờ Vàng Gửi Tin Nhắn Tế Nhị</span>
                        </div>
                        <div id="reminder-timing-windows" style="font-size: 12px; color: #4A453E; line-height: 1.5;"></div>
                        <div id="reminder-timing-tip" style="font-size: 11px; color: #7A726A; margin-top: 4px; font-style: italic;"></div>
                    </div>

                    {{-- Danh sách khách và lời nhắn --}}
                    <div>
                        <h4 style="font-size: 14px; font-weight: 700; color: #1F1D1A; margin: 0 0 10px 0;">Danh Sách & Tin Nhắn Gợi Ý Cá Nhân Hóa:</h4>
                        <div id="reminder-list-container" style="display: flex; flex-direction: column; gap: 10px; max-height: 400px; overflow-y: auto;">
                            {{-- Dynamic via JS --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="mayhen_guest_modal_footer" style="justify-content: flex-end;">
                <button type="button" class="mayhen_guest_btn mayhen_guest_btn_primary" id="reminder-btn-done">
                    Đóng Cửa Sổ
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Lời Cảm Ơn Sau Cưới Cá Nhân Hóa (TASK-VAL-17) --}}
    <div class="mayhen_guest_modal" id="mayhen_modal_thankyou_builder" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="mayhen_guest_modal_backdrop" id="thankyou-modal-backdrop"></div>
        <div class="mayhen_guest_modal_dialog" style="max-width: 760px; max-height: 90vh; overflow-y: auto;">
            <div class="mayhen_guest_modal_header">
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #9C7A4A; font-weight: 700;">PERSONAL THANK-YOU BUILDER</span>
                    <h3 class="mayhen_guest_modal_title" style="margin: 2px 0 0 0;">Trợ Lý Soạn Lời Cảm Ơn Sau Cưới</h3>
                </div>
                <button type="button" class="mayhen_guest_modal_close" id="thankyou-modal-close" aria-label="Đóng">&times;</button>
            </div>
            <div class="mayhen_guest_modal_body" style="padding: 20px 24px;">
                <div id="thankyou-loading" style="text-align: center; padding: 40px 20px; color: #7A726A;">
                    <div style="font-size: 24px; margin-bottom: 8px;">⏳</div>
                    <span>Đang phân loại khách đã dự tiệc, khách gửi quà từ xa và soạn lời tri ân...</span>
                </div>

                <div id="thankyou-content" style="display: none; flex-direction: column; gap: 18px;">
                    {{-- Thẻ thống kê 3 nhóm khách --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
                        <div style="background: #E8F5E9; border: 1px solid #C8E6C9; border-radius: 10px; padding: 12px 14px;">
                            <span style="font-size: 11px; color: #2E7D32; text-transform: uppercase; font-weight: 700; display: block;">Đã Đến Chung Vui</span>
                            <strong id="thankyou-attended-count" style="font-size: 20px; color: #2E7D32;">0</strong>
                            <span style="font-size: 11px; color: #7A726A; display: block;">khách đã check-in / xác nhận</span>
                        </div>
                        <div style="background: #FFF8E1; border: 1px solid #FFE082; border-radius: 10px; padding: 12px 14px;">
                            <span style="font-size: 11px; color: #8D6E63; text-transform: uppercase; font-weight: 700; display: block;">Gửi Quà / Lời Chúc Từ Xa</span>
                            <strong id="thankyou-gift-only-count" style="font-size: 20px; color: #F57F17;">0</strong>
                            <span style="font-size: 11px; color: #7A726A; display: block;">khách không thể đến</span>
                        </div>
                        <div style="background: #F3E5F5; border: 1px solid #E1BEE7; border-radius: 10px; padding: 12px 14px;">
                            <span style="font-size: 11px; color: #6A1B9A; text-transform: uppercase; font-weight: 700; display: block;">Khách Mời Chung</span>
                            <strong id="thankyou-general-count" style="font-size: 20px; color: #6A1B9A;">0</strong>
                            <span style="font-size: 11px; color: #7A726A; display: block;">khách trong danh sách</span>
                        </div>
                    </div>

                    {{-- Thông tin đính kèm --}}
                    <div style="background: #FAF8F5; border: 1px dashed #C5A880; border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <div style="font-size: 13px; color: #4A453E;">
                            <strong style="color: #9C7A4A;">📷 Album Ảnh Kỷ Niệm Đính Kèm:</strong>
                            <span id="thankyou-album-url" style="word-break: break-all; margin-left: 6px; font-size: 12px; color: #7A726A;"></span>
                        </div>
                        <span style="font-size: 11px; color: #7A726A; font-style: italic;">Tự động chèn link để khách xem lại ảnh tiệc</span>
                    </div>

                    {{-- Danh sách khách và lời cảm ơn --}}
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <h4 style="font-size: 14px; font-weight: 700; color: #1F1D1A; margin: 0;">Danh Sách Lời Cảm Ơn Đã Soạn Sẵn:</h4>
                            <div style="display: flex; gap: 6px;" id="thankyou-filter-tabs">
                                <button type="button" class="mayhen_guest_filter_pill active" data-thankyou-filter="all" style="padding: 4px 10px; font-size: 11px; border-radius: 20px; border: 1px solid #C5A880; background: #C5A880; color: #fff; cursor: pointer;">Tất Cả</button>
                                <button type="button" class="mayhen_guest_filter_pill" data-thankyou-filter="attended" style="padding: 4px 10px; font-size: 11px; border-radius: 20px; border: 1px solid #E5DCCD; background: #fff; color: #4A453E; cursor: pointer;">Đã Đến</button>
                                <button type="button" class="mayhen_guest_filter_pill" data-thankyou-filter="gift_only" style="padding: 4px 10px; font-size: 11px; border-radius: 20px; border: 1px solid #E5DCCD; background: #fff; color: #4A453E; cursor: pointer;">Gửi Quà</button>
                            </div>
                        </div>
                        <div id="thankyou-list-container" style="display: flex; flex-direction: column; gap: 10px; max-height: 400px; overflow-y: auto;">
                            {{-- Dynamic via JS --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="mayhen_guest_modal_footer" style="justify-content: flex-end;">
                <button type="button" class="mayhen_guest_btn mayhen_guest_btn_primary" id="thankyou-btn-done">
                    Hoàn Tất & Đóng
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Trung Tâm Ghi Nhận Gửi Thiệp & Theo Dõi Mở Link (Section 30 Master PRD V3) --}}
    <div class="mayhen_guest_modal" id="mayhen_modal_delivery_cockpit" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="mayhen_guest_modal_backdrop" id="delivery-modal-backdrop"></div>
        <div class="mayhen_guest_modal_dialog" style="max-width: 820px; max-height: 90vh; overflow-y: auto;">
            <div class="mayhen_guest_modal_header">
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; color: #9C7A4A; font-weight: 700;">THEO DÕI GỬI THIỆP &amp; LƯỢT MỞ XEM</span>
                    <h3 class="mayhen_guest_modal_title" style="margin: 2px 0 0 0;">Đánh Dấu Đã Gửi &amp; Theo Dõi Mở Thiệp</h3>
                </div>
                <button type="button" class="mayhen_guest_modal_close" id="delivery-modal-close" aria-label="Đóng">&times;</button>
            </div>
            <div class="mayhen_guest_modal_body" style="padding: 20px 24px;">
                <div style="background: #FAF8F5; border: 1px solid #E5DCCD; border-left: 4px solid #C5A880; border-radius: 10px; padding: 12px 14px; font-size: 12.5px; color: #4A453E; line-height: 1.5;">
                    <strong>Lưu ý minh bạch:</strong> {{ $guestsBrandName }} không tự động nhắn Zalo/SMS/Email thay bạn để bảo vệ tài khoản cá nhân khỏi bị đánh dấu tin rác. Công cụ này giúp bạn chuẩn bị lời mời cá nhân hóa và đánh dấu hàng loạt những khách mà hai bạn đã tự gửi thiệp. Lượt <strong>Đã Mở Xem Thiệp</strong> được ghi nhận tự động khi khách thực sự bấm mở link riêng.
                </div>

                {{-- 4 Thẻ chỉ số phân phối --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-bottom: 12px;">
                    <div style="background: #EBF5FF; border: 1px solid #BFDBFE; border-radius: 10px; padding: 12px 14px;">
                        <span style="font-size: 11px; color: #1E40AF; text-transform: uppercase; font-weight: 700; display: block;">Đã Đánh Dấu Gửi</span>
                        <strong style="font-size: 22px; color: #1E40AF;">{{ $deliveryStats['sent_guests'] ?? 0 }}</strong>
                        <span style="font-size: 11px; color: #6B7280; display: block;">lời mời đã đánh dấu gửi</span>
                    </div>
                    <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 10px; padding: 12px 14px;">
                        <span style="font-size: 11px; color: #92400E; text-transform: uppercase; font-weight: 700; display: block;">Chưa Đánh Dấu Gửi</span>
                        <strong style="font-size: 22px; color: #92400E;">{{ $deliveryStats['unsent_guests'] ?? 0 }}</strong>
                        <span style="font-size: 11px; color: #6B7280; display: block;">lời mời chưa gửi</span>
                    </div>
                    <div style="background: #ECFDF5; border: 1px solid #A7F3D0; border-radius: 10px; padding: 12px 14px;">
                        <span style="font-size: 11px; color: #065F46; text-transform: uppercase; font-weight: 700; display: block;">Khách Đã Mở Link</span>
                        <strong style="font-size: 22px; color: #065F46;">{{ $deliveryStats['opened_after_sent'] ?? 0 }}</strong>
                        <span style="font-size: 11px; color: #6B7280; display: block;">khách thực tế đã xem thiệp</span>
                    </div>
                    <div style="background: #FAF5FF; border: 1px solid #E9D5FF; border-radius: 10px; padding: 12px 14px;">
                        <span style="font-size: 11px; color: #6B21A8; text-transform: uppercase; font-weight: 700; display: block;">Tỷ Lệ Mở Xem</span>
                        <strong style="font-size: 22px; color: #6B21A8;">{{ $deliveryStats['open_rate'] ?? 0 }}%</strong>
                        <span style="font-size: 11px; color: #6B7280; display: block;">trên số lời mời đã gửi</span>
                    </div>
                </div>

                {{-- Khối đánh dấu đã gửi hàng loạt --}}
                <div style="background: #FAF8F5; border: 1px solid #E5DCCD; border-radius: 12px; padding: 18px 20px; margin-bottom: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 6px;">
                        <h4 style="font-size: 15px; font-weight: 700; color: #1F1D1A; margin: 0;">📌 Đánh Dấu Đã Tự Gửi Hàng Loạt &amp; Lưu Mẫu Lời Mời</h4>
                        <span style="font-size: 12px; color: #8C827A;">Ghi sổ nhật ký gửi thủ công</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #4A453E; margin-bottom: 4px;">Kênh Bạn Đã Tự Gửi</label>
                            <select id="delivery-batch-channel" class="mayhen_guest_toolbar_select" style="width: 100%;">
                                <option value="copy">📋 Sao chép lời mời gửi riêng</option>
                                <option value="zalo">💬 Đã tự gửi qua Zalo</option>
                                <option value="sms">📱 Đã tự nhắn SMS / iMessage</option>
                                <option value="email">✉️ Đã tự gửi qua Email / Messenger</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #4A453E; margin-bottom: 4px;">Đối Tượng Ghi Nhận</label>
                            <select id="delivery-batch-scope" class="mayhen_guest_toolbar_select" style="width: 100%;">
                                <option value="unsent">Chỉ khách chưa đánh dấu gửi ({{ $deliveryStats['unsent_guests'] ?? 0 }} lời mời)</option>
                                <option value="all">Toàn bộ danh sách ({{ $totalGuestsCount }} lời mời)</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="display: block; font-size: 12px; font-weight: 600; color: #4A453E; margin-bottom: 4px;">
                            Mẫu Lời Mời Lưu Trong Nhật Ký (Tùy chọn)
                        </label>
                        <textarea id="delivery-batch-template" rows="3" style="width: 100%; border: 1px solid #D1C7BD; border-radius: 8px; padding: 10px 12px; font-size: 13px; font-family: inherit; line-height: 1.5; box-sizing: border-box;" placeholder="Nhập lời mời tùy biến hoặc để trống để dùng mẫu mặc định..."></textarea>
                        <div style="font-size: 11px; color: #8C827A; margin-top: 4px; display: flex; flex-wrap: wrap; gap: 6px;">
                            <span style="background: #EAE6DF; padding: 2px 6px; border-radius: 4px;">{salutation}</span>
                            <span style="background: #EAE6DF; padding: 2px 6px; border-radius: 4px;">{guest_name}</span>
                            <span style="background: #EAE6DF; padding: 2px 6px; border-radius: 4px;">{couple_names}</span>
                            <span style="background: #EAE6DF; padding: 2px 6px; border-radius: 4px;">{wedding_date}</span>
                            <span style="background: #EAE6DF; padding: 2px 6px; border-radius: 4px;">{venue_name}</span>
                            <span style="background: #EAE6DF; padding: 2px 6px; border-radius: 4px;">{invitation_url}</span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" id="delivery-btn-dispatch-batch" class="mayhen_guest_btn mayhen_guest_btn_gold"
                            data-batch-url="{{ route('guests.delivery.batch', $invitation->id) }}"
                            style="box-shadow: 0 4px 12px rgba(197, 168, 128, 0.4);">
                            <span>Xác Nhận Đánh Dấu Đã Gửi Hàng Loạt</span>
                        </button>
                    </div>
                </div>

                {{-- Báo cáo lịch sử ghi nhận gần đây --}}
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h4 style="font-size: 14px; font-weight: 700; color: #1F1D1A; margin: 0;">📜 Nhật Ký Đánh Dấu Đã Gửi Gần Nhất</h4>
                        <span style="font-size: 12px; color: #8C827A;" id="delivery-history-count">Đang tải...</span>
                    </div>
                    <div id="delivery-history-container" style="max-height: 250px; overflow-y: auto; border: 1px solid #E5DCCD; border-radius: 8px;">
                        {{-- Render qua JS --}}
                    </div>
                </div>
            </div>
            <div class="mayhen_guest_modal_footer" style="justify-content: flex-end;">
                <button type="button" class="mayhen_guest_btn mayhen_guest_btn_primary" id="delivery-btn-done">
                    Đóng Cửa Sổ
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/guests.js') }}"></script>
    <script type="module" src="{{ asset('assets/client/js/components/client-ops-tour.js') }}"></script>
@endpush
