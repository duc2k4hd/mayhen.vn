@extends('client.layouts.master')

@section('title', 'Quản Lý Khách Mời & RSVP — Mây Hẹn')
@section('meta_description', 'Quản lý danh sách khách mời, tạo link cá nhân hóa gửi qua Zalo và theo dõi phản hồi tham dự.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/guests.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container mayhen_guest_wrapper">
        <!-- Header -->
        <div class="mayhen_guest_header">
            <div>
                <h1 class="mayhen_guest_title">Quản Lý Khách Mời & RSVP</h1>
                <p style="color: var(--mayhen-color-text-muted); font-size: 0.9375rem; margin-bottom: 0;">
                    Đám cưới: {{ $wedding->groom_name }} & {{ $wedding->bride_name }} &bull; {{ $wedding->wedding_date->format('d/m/Y') }}
                </p>
            </div>
            <div>
                <a href="{{ route('invitation.show', $invitation->slug) }}" class="mayhen_client_button_base mayhen_client_button_secondary mayhen_client_button_sm" target="_blank">
                    Xem Trang Thiệp Chính
                </a>
            </div>
        </div>

        <!-- 4 Stats Cards -->
        <div class="mayhen_guest_stats_grid">
            <div class="mayhen_guest_stat_card">
                <span class="mayhen_guest_stat_number">{{ $guests->count() }}</span>
                <span class="mayhen_guest_stat_label">Tổng Lời Mời</span>
            </div>
            <div class="mayhen_guest_stat_card">
                <span class="mayhen_guest_stat_number">{{ $totalInvited }}</span>
                <span class="mayhen_guest_stat_label">Số Khách Dự Kiến</span>
            </div>
            <div class="mayhen_guest_stat_card">
                <span class="mayhen_guest_stat_number" style="color: #2D5A27;">{{ $attendingCount }}</span>
                <span class="mayhen_guest_stat_label">Xác Nhận Tham Dự</span>
            </div>
            <div class="mayhen_guest_stat_card">
                <span class="mayhen_guest_stat_number" style="color: var(--mayhen-color-danger);">{{ $declinedCount }}</span>
                <span class="mayhen_guest_stat_label">Từ Chối / Bận</span>
            </div>
        </div>

        <!-- Add Guest Quick Form -->
        <div class="mayhen_guest_add_card">
            <h2 class="mayhen_guest_add_title">Thêm Khách Mời Mới</h2>
            <form action="{{ route('guests.store', $invitation->id) }}" method="POST">
                @csrf
                <div class="mayhen_guest_form_row">
                    <div class="mayhen_client_form_group" style="margin-bottom: 0;">
                        <label class="mayhen_client_form_label">Tên khách mời *</label>
                        <input type="text" name="name" class="mayhen_client_form_input" required placeholder="Ví dụ: Anh Hoàng & Bạn gái">
                    </div>

                    <div class="mayhen_client_form_group" style="margin-bottom: 0;">
                        <label class="mayhen_client_form_label">Cách xưng hô</label>
                        <select name="salutation" class="mayhen_client_form_select">
                            <option value="Kính mời">Kính mời (Người lớn tuổi/Trang trọng)</option>
                            <option value="Thân mời" selected>Thân mời (Bạn bè/Đồng nghiệp)</option>
                            <option value="Thương mời">Thương mời (Người thân thiết)</option>
                        </select>
                    </div>

                    <div class="mayhen_client_form_group" style="margin-bottom: 0;">
                        <label class="mayhen_client_form_label">Nhóm khách</label>
                        <input type="text" name="group_name" class="mayhen_client_form_input" value="Bạn Bè" placeholder="Bạn Đại học, Họ Hàng...">
                    </div>

                    <div class="mayhen_client_form_group" style="margin-bottom: 0;">
                        <label class="mayhen_client_form_label">Số người dự kiến</label>
                        <input type="number" name="expected_attendees" class="mayhen_client_form_input" min="1" max="10" value="2">
                    </div>

                    <div>
                        <button type="submit" class="mayhen_client_button_base mayhen_client_button_primary" style="height: 48px;">
                            + Thêm Khách
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Guests Table -->
        <div class="mayhen_guest_table_card">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--mayhen-color-border); display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-family: var(--mayhen-font-serif); font-size: 1.25rem; margin: 0;">Danh Sách Khách & Công Cụ Gửi Mời</h2>
                <span style="font-size: 0.8125rem; color: var(--mayhen-color-text-muted);">{{ $guests->count() }} khách</span>
            </div>

            <div style="overflow-x: auto;">
                <table class="mayhen_guest_table">
                    <thead>
                        <tr>
                            <th>Tên Khách Mời</th>
                            <th>Xưng Hô</th>
                            <th>Nhóm</th>
                            <th>Dự Kiến</th>
                            <th>Phản Hồi RSVP</th>
                            <th>Công Cụ Mời Zalo</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($guests as $g)
                            @php
                                $personalUrl = route('invitation.show', ['slug' => $invitation->slug, 'token' => $g->guest_token]);
                            @endphp
                            <tr>
                                <td><strong>{{ $g->name }}</strong></td>
                                <td>{{ $g->salutation }}</td>
                                <td><span style="background: var(--mayhen-color-bg-soft); padding: 3px 8px; border-radius: 4px; font-size: 0.75rem;">{{ $g->group_name }}</span></td>
                                <td>{{ $g->expected_attendees }} người</td>
                                <td>
                                    @if ($g->response)
                                        @if ($g->response->attending_status === 'attending')
                                            <span class="mayhen_guest_badge_attending">Đã xác nhận ({{ $g->response->adults_count + $g->response->children_count }} người)</span>
                                        @else
                                            <span style="color: var(--mayhen-color-danger); font-size: 0.75rem;">Không tham dự</span>
                                        @endif
                                    @else
                                        <span class="mayhen_guest_badge_pending">Chưa phản hồi</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="mayhen_guest_actions_cell">
                                        <button type="button" class="mayhen_client_button_base mayhen_client_button_secondary mayhen_client_button_sm"
                                            data-mayhen-action="copy-zalo"
                                            data-guest-name="{{ $g->name }}"
                                            data-guest-salutation="{{ $g->salutation }}"
                                            data-guest-link="{{ $personalUrl }}"
                                            data-couple-names="{{ $wedding->groom_name }} & {{ $wedding->bride_name }}"
                                            data-wedding-date="{{ $wedding->wedding_date->format('d.m.Y') }}">
                                            Sao Chép Lời Mời Zalo
                                        </button>
                                        <button type="button" class="mayhen_client_button_base mayhen_client_button_ghost mayhen_client_button_sm"
                                            data-mayhen-action="copy-link"
                                            data-guest-link="{{ $personalUrl }}">
                                            Copy Link
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <form action="{{ route('guests.destroy', $g->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa khách mời này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="color: var(--mayhen-color-danger); font-size: 0.8125rem;">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 3rem; color: var(--mayhen-color-text-muted);">
                                    Chưa có khách mời nào trong danh sách. Hãy thêm khách mời đầu tiên ở khung trên!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/guests.js') }}"></script>
@endpush
