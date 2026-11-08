@extends('client.layouts.master')

@php
    $groomName = $wedding->groom_name ?? 'Chú Rể';
    $brideName = $wedding->bride_name ?? 'Cô Dâu';
    $pageTitle = 'Tài Liệu Tác Nghiệp Đối Tác (Vendor Brief) | ' . $groomName . ' & ' . $brideName . ' | ' . config('app.name');
@endphp

@section('title', $pageTitle)

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/vendor-brief.css') }}?v={{ file_exists(public_path('assets/client/css/pages/vendor-brief.css')) ? filemtime(public_path('assets/client/css/pages/vendor-brief.css')) : '1.0' }}">
@endpush

@section('content')
<div class="mayhen_vendor_brief_container">

    {{-- Top Action Header Card --}}
    <div class="mayhen_vendor_brief_header_card">
        <div>
            <div class="mayhen_vendor_brief_privacy_pill">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <span>Bảo Mật Cao Cấp: Đã ẩn 100% Số Điện Thoại & Tiền Mừng Cưới</span>
            </div>
            <h1 class="mayhen_vendor_brief_title">Bản Hướng Dẫn Tác Nghiệp Đối Tác</h1>
            <p class="mayhen_vendor_brief_subtitle">
                Tài liệu tóm lược tối giản theo vai trò dành cho MC, Quản lý sảnh tiệc nhà hàng và Ekip quay chụp trong ngày hôn lễ của <strong>{{ $groomName }} & {{ $brideName }}</strong>.
            </p>
        </div>
        <div class="mayhen_vendor_brief_actions">
            <a href="{{ route('account.invitations') }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                ← Danh Sách Thiệp
            </a>
            <button type="button" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm" onclick="window.print()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                <span>In Bản A4 / Lưu PDF</span>
            </button>
        </div>
    </div>

    {{-- Tabs Chuyển Vai Trò --}}
    <nav class="mayhen_vendor_brief_tabs" aria-label="Lọc vai trò đối tác">
        <button type="button" class="mayhen_vendor_brief_tab_btn active" data-tab="all">
            Xem Toàn Bộ (Tổng Thể)
        </button>
        <button type="button" class="mayhen_vendor_brief_tab_btn" data-tab="mc">
            🎤 MC & Dẫn Chương Trình
        </button>
        <button type="button" class="mayhen_vendor_brief_tab_btn" data-tab="venue">
            🍽️ Nhà Hàng & Ban Phục Vụ
        </button>
        <button type="button" class="mayhen_vendor_brief_tab_btn" data-tab="photo">
            📸 Ekip Nhiếp Ảnh & Quay Phim
        </button>
    </nav>

    {{-- PHẦN 1: DÀNH CHO MC & DẪN CHƯƠNG TRÌNH --}}
    <section class="mayhen_vendor_brief_section" id="brief-mc">
        <h2 class="mayhen_vendor_brief_sec_title">
            <span>1. Hướng Dẫn Tác Nghiệp: MC & Dẫn Chương Trình</span>
            <span class="mayhen_vendor_brief_role_badge">MC BRIEF</span>
        </h2>

        <div class="mayhen_vendor_brief_grid_2">
            <div class="mayhen_vendor_brief_info_card">
                <h5>Đôi Uyên Ương</h5>
                <p><strong>Chú Rể:</strong> {{ $brief['mc']['couple']['groom'] }}<br><strong>Cô Dâu:</strong> {{ $brief['mc']['couple']['bride'] }}</p>
            </div>
            <div class="mayhen_vendor_brief_info_card">
                <h5>Thời Gian & Sảnh Tiệc</h5>
                <p><strong>Ngày:</strong> {{ $brief['party_date'] }} ({{ $brief['party_time'] }})<br><strong>Địa điểm:</strong> {{ $brief['venue_name'] }}</p>
            </div>
        </div>

        <div class="mayhen_vendor_brief_grid_2">
            <div class="mayhen_vendor_brief_info_card">
                <h5>Song Thân Nhà Trai</h5>
                <p><strong>Thân Phụ:</strong> {{ $brief['mc']['parents']['groom_side']['father'] }}<br><strong>Thân Mẫu:</strong> {{ $brief['mc']['parents']['groom_side']['mother'] }}</p>
            </div>
            <div class="mayhen_vendor_brief_info_card">
                <h5>Song Thân Nhà Gái</h5>
                <p><strong>Thân Phụ:</strong> {{ $brief['mc']['parents']['bride_side']['father'] }}<br><strong>Thân Mẫu:</strong> {{ $brief['mc']['parents']['bride_side']['mother'] }}</p>
            </div>
        </div>

        <h4 style="font-size: 15px; margin: 18px 0 8px 0; color: #1F1D1A;">Timeline Nghi Lễ Chi Tiết:</h4>
        <table class="mayhen_vendor_brief_table">
            <thead>
                <tr>
                    <th style="width: 110px;">Thời Gian</th>
                    <th>Nội Dung & Nghi Lễ Trọng Điểm</th>
                </tr>
            </thead>
            <tbody>
                @foreach($brief['mc']['timeline'] as $t)
                    <tr>
                        <td><strong>{{ $t['time'] }}</strong></td>
                        <td>{{ $t['event'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if(!empty($brief['mc']['vip_roster']))
            <h4 style="font-size: 15px; margin: 20px 0 8px 0; color: #1F1D1A;">Danh Sách Quan Khách VIP Cần Xướng Danh:</h4>
            <table class="mayhen_vendor_brief_table">
                <thead>
                    <tr>
                        <th>Họ Và Tên Quan Khách</th>
                        <th>Vị Trí Bàn Tiệc</th>
                        <th>Vai Trò / Mối Quan Hệ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($brief['mc']['vip_roster'] as $vip)
                        <tr>
                            <td><strong>{{ $vip['name'] }}</strong></td>
                            <td><span style="color: #9C7A4A; font-weight: 600;">{{ $vip['table'] }}</span></td>
                            <td>{{ $vip['role_hint'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    {{-- PHẦN 2: DÀNH CHO NHÀ HÀNG & PHỤC VỤ --}}
    <section class="mayhen_vendor_brief_section" id="brief-venue">
        <h2 class="mayhen_vendor_brief_sec_title">
            <span>2. Hướng Dẫn Tác Nghiệp: Nhà Hàng & Ban Phục Vụ Tiệc</span>
            <span class="mayhen_vendor_brief_role_badge">VENUE & CATERING</span>
        </h2>

        <div class="mayhen_vendor_brief_grid_2">
            <div class="mayhen_vendor_brief_info_card">
                <h5>Số Liệu Khách Mời & Bàn Tiệc</h5>
                <p>
                    <strong>Khách mời dự kiến:</strong> {{ $brief['venue']['guest_stats']['expected_seats'] }} suất<br>
                    <strong>Khách đã RSVP xác nhận:</strong> {{ $brief['venue']['guest_stats']['actual_confirmed_seats'] }} suất<br>
                    <strong>Tổng số bàn đã xếp:</strong> {{ $brief['venue']['guest_stats']['tables_count'] }} bàn
                </p>
            </div>
            <div class="mayhen_vendor_brief_info_card">
                <h5>Địa Điểm & Sảnh Tiệc</h5>
                <p>
                    <strong>Sảnh:</strong> {{ $brief['venue']['venue_name'] }}<br>
                    <strong>Địa chỉ:</strong> {{ $brief['venue']['venue_address'] ?: 'Chưa cập nhật' }}
                </p>
            </div>
        </div>

        @if(!empty($brief['venue']['dietary_requirements']))
            <h4 style="font-size: 15px; margin: 18px 0 8px 0; color: #C62828;">⚠️ Nhu Cầu Ẩm Thực Đặc Biệt (Ăn Chay / Dị Ứng):</h4>
            <table class="mayhen_vendor_brief_table">
                <thead>
                    <tr>
                        <th style="width: 200px;">Khách Mời</th>
                        <th>Yêu Cầu Ăn Uống / Ghi Chú Nhà Bếp</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($brief['venue']['dietary_requirements'] as $diet)
                        <tr>
                            <td><strong>{{ $diet['guest'] }}</strong></td>
                            <td style="color: #C62828;">{{ $diet['requirement'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if(!empty($brief['venue']['tables']))
            <h4 style="font-size: 15px; margin: 20px 0 8px 0; color: #1F1D1A;">Sơ Đồ Phân Bổ Bàn Tiệc ({{ count($brief['venue']['tables']) }} bàn):</h4>
            <table class="mayhen_vendor_brief_table">
                <thead>
                    <tr>
                        <th>Tên Bàn Tiệc</th>
                        <th style="width: 140px; text-align: center;">Số Ghế Dự Kiến</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($brief['venue']['tables'] as $tbl)
                        <tr>
                            <td><strong>{{ $tbl['table_name'] }}</strong></td>
                            <td style="text-align: center;"><strong>{{ $tbl['seats_count'] }}</strong> ghế</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>

    {{-- PHẦN 3: DÀNH CHO EKIP NHIẾP ẢNH & QUAY PHIM --}}
    <section class="mayhen_vendor_brief_section" id="brief-photo">
        <h2 class="mayhen_vendor_brief_sec_title">
            <span>3. Hướng Dẫn Tác Nghiệp: Ekip Nhiếp Ảnh & Quay Phim</span>
            <span class="mayhen_vendor_brief_role_badge">PHOTO / VIDEO SHOT LIST</span>
        </h2>

        <div class="mayhen_vendor_brief_info_card" style="margin-bottom: 16px;">
            <h5>Thông Tin Khung Giờ & Bàn Tiệc</h5>
            <p>
                <strong>Giờ đón khách:</strong> {{ $brief['photographer']['start_time'] }} |
                <strong>Sảnh đón:</strong> {{ $brief['photographer']['hall_name'] }} |
                <strong>Tổng số bàn cần chụp kỷ niệm:</strong> {{ $brief['photographer']['tables_count'] }} bàn
            </p>
        </div>

        <h4 style="font-size: 15px; margin: 16px 0 10px 0; color: #1F1D1A;">Bảng Danh Sách 8 Khoảnh Khắc Vàng (Must-Have Shot List):</h4>
        <div style="display: flex; flex-direction: column; gap: 4px;">
            @foreach($brief['photographer']['key_shots'] as $shot)
                <div class="mayhen_vendor_brief_shot_item">
                    <div class="mayhen_vendor_brief_shot_box" aria-hidden="true"></div>
                    <div>
                        <div class="mayhen_vendor_brief_shot_title">{{ $shot['moment'] }}</div>
                        <div class="mayhen_vendor_brief_shot_desc">{{ $shot['desc'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Footer Stamp --}}
    <div style="text-align: center; font-size: 12px; color: #7A726A; margin-top: 24px; padding-top: 14px; border-top: 1px dashed #E5DCCD;">
        Mây Hẹn (mayhen.vn) • Bản xuất tự động lúc {{ $brief['generated_at'] }} • Tài liệu lưu hành nội bộ ngày cưới.
    </div>

</div>

@push('scripts')
<script type="module" src="{{ asset('assets/client/js/pages/vendor-brief.js') }}"></script>
@endpush
@endsection
