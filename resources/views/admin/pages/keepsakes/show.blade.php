@extends('admin.layouts.master')

@section('title', 'Quản Trị Kỷ Niệm: ' . ($wedding->title ?: ($wedding->groom_name . ' & ' . $wedding->bride_name)))

@section('content')
<div class="mayhen_admin_keepsakes_detail" style="padding: 24px; max-width: 1400px; margin: 0 auto; font-family: var(--mh-admin-font, 'Open Sans', sans-serif);">

    <!-- Breadcrumb & Back -->
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.keepsakes.index') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: #64748b; text-decoration: none; transition: color 0.2s;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Quay lại danh sách Lưu Trữ Kỷ Niệm
        </a>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; color: #065f46; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- Wedding Header Card -->
    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                <h1 style="font-size: 22px; font-weight: 700; color: #1e293b; margin: 0;">
                    {{ $wedding->title ?: ($wedding->groom_name . ' & ' . $wedding->bride_name) }}
                </h1>
                <span style="font-size: 12px; font-weight: 700; background: #eff6ff; color: #1d4ed8; padding: 3px 10px; border-radius: 12px;">
                    {{ $summary['milestone_label'] }}
                </span>
                @if($keepsake->is_enabled)
                    <span style="font-size: 11px; font-weight: 700; background: #ecfdf5; color: #047857; padding: 3px 8px; border-radius: 10px;">
                        Đang Mở Kỷ Niệm
                    </span>
                @else
                    <span style="font-size: 11px; font-weight: 700; background: #f1f5f9; color: #64748b; padding: 3px 8px; border-radius: 10px;">
                        Tạm Tắt Kỷ Niệm
                    </span>
                @endif
            </div>
            <div style="display: flex; align-items: center; gap: 12px; font-size: 13px; color: #64748b;">
                <span>Cô dâu: <strong>{{ $wedding->bride_name }}</strong></span>
                <span>•</span>
                <span>Chú rể: <strong>{{ $wedding->groom_name }}</strong></span>
                <span>•</span>
                <span>Ngày cưới: <strong>{{ $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : '—' }}</strong> (Cách đây {{ $summary['days_since_wedding'] }} ngày)</span>
            </div>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.keepsakes.export_archive', $wedding->id) }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; background: #1e293b; color: #ffffff; font-size: 13px; font-weight: 600; text-decoration: none; transition: background 0.2s;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" x2="12" y1="15" y2="3"></line></svg>
                Xuất Gói Lưu Trữ JSON
            </a>
            @if($wedding->invitation && $wedding->invitation->slug)
                <a href="/thiep/{{ $wedding->invitation->slug }}" target="_blank" style="display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 8px; background: #ffffff; color: #334155; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 600; text-decoration: none;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" x2="21" y1="14" y2="3"></line></svg>
                    Xem Thiệp / Trang Kỷ Niệm
                </a>
            @endif
        </div>
    </div>

    <!-- 4 Quick Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-align: center;">
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Khách Mời / Điểm Danh</div>
            <div style="font-size: 20px; font-weight: 700; color: #1e293b;">
                {{ $summary['attended_guests'] }} / {{ $summary['total_guests'] }}
            </div>
            <span style="font-size: 11px; color: #059669; font-weight: 600;">
                {{ $summary['total_guests'] > 0 ? round(($summary['attended_guests'] / $summary['total_guests']) * 100) : 0 }}% tham dự
            </span>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-align: center;">
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Quà Mừng Ghi Nhận</div>
            <div style="font-size: 20px; font-weight: 700; color: #059669;">
                {{ number_format($summary['total_gift_amount']) }} đ
            </div>
            <span style="font-size: 11px; color: #64748b;">{{ $summary['total_gifts'] }} phong bì / lượt mừng</span>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-align: center;">
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Lời Chúc Sổ Lưu Bút</div>
            <div style="font-size: 20px; font-weight: 700; color: #d97706;">
                {{ $summary['total_wishes'] }}
            </div>
            <span style="font-size: 11px; color: #64748b;">lưu niệm trọn đời</span>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; text-align: center;">
            <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">Hộp Thư Thời Gian</div>
            <div style="font-size: 20px; font-weight: 700; color: #6366f1;">
                {{ $timeCapsules->count() }}
            </div>
            <span style="font-size: 11px; color: #64748b;">{{ $timeCapsules->where('is_sealed', true)->count() }} bức thư niêm phong</span>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div style="display: flex; gap: 8px; border-bottom: 2px solid #e2e8f0; margin-bottom: 24px;">
        <button type="button" onclick="switchKeepsakeTab('settings')" id="tab-btn-settings" style="padding: 10px 18px; border: none; background: transparent; font-size: 14px; font-weight: 700; color: #1e293b; border-bottom: 2px solid #1e293b; margin-bottom: -2px; cursor: pointer; transition: all 0.2s;">
            1. Cấu Hình Trang Kỷ Niệm
        </button>
        <button type="button" onclick="switchKeepsakeTab('capsules')" id="tab-btn-capsules" style="padding: 10px 18px; border: none; background: transparent; font-size: 14px; font-weight: 600; color: #64748b; border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer; transition: all 0.2s;">
            2. Hộp Thư Thời Gian (Time Capsule)
        </button>
        <button type="button" onclick="switchKeepsakeTab('wrapup')" id="tab-btn-wrapup" style="padding: 10px 18px; border: none; background: transparent; font-size: 14px; font-weight: 600; color: #64748b; border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer; transition: all 0.2s;">
            3. Tổng Kết &amp; Bảo Mật Dữ Liệu
        </button>
    </div>

    <!-- TAB 1: Cấu hình Trang Kỷ Niệm -->
    <div id="tab-pane-settings" style="display: block;">
        <form method="POST" action="{{ route('admin.keepsakes.update_settings', $wedding->id) }}">
            @csrf
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); margin-bottom: 24px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-top: 0; margin-bottom: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                    Trạng Thái &amp; Thông Điệp Tri Ân Hậu Hôn Lễ
                </h3>

                <!-- Bật tắt chế độ -->
                <div style="margin-bottom: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-weight: 700; font-size: 14px; color: #1e293b; margin-bottom: 2px;">
                            Kích hoạt Chế độ Kỷ niệm (Post-Wedding Keepsake Mode)
                        </div>
                        <div style="font-size: 12px; color: #64748b;">
                            Khi bật, thiệp cưới chuyển đổi thành Trang Lưu Niệm Kỷ Niệm với lời cảm ơn, album ảnh khoảnh khắc và video kỷ niệm.
                        </div>
                    </div>
                    <label style="position: relative; display: inline-block; width: 44px; height: 24px;">
                        <input type="hidden" name="is_enabled" value="0">
                        <input type="checkbox" name="is_enabled" value="1" {{ $keepsake->is_enabled ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;" onchange="this.nextElementSibling.style.background = this.checked ? '#10b981' : '#cbd5e1'; this.nextElementSibling.querySelector('span').style.transform = this.checked ? 'translateX(20px)' : 'translateX(0)';">
                        <span style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: {{ $keepsake->is_enabled ? '#10b981' : '#cbd5e1' }}; transition: .3s; border-radius: 24px;">
                            <span style="position: absolute; content: ''; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; transform: {{ $keepsake->is_enabled ? 'translateX(20px)' : 'translateX(0)' }};"></span>
                        </span>
                    </label>
                </div>

                <!-- Tiêu đề cảm ơn -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                        Tiêu Đề Lời Cảm Ơn <span style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" name="thank_you_title" value="{{ old('thank_you_title', $keepsake->thank_you_title) }}" required style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; box-sizing: border-box;">
                </div>

                <!-- Nội dung cảm ơn -->
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                        Nội Dung Thông Điệp Tri Ân
                    </label>
                    <textarea name="thank_you_message" rows="4" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; box-sizing: border-box; line-height: 1.5;">{{ old('thank_you_message', $keepsake->thank_you_message) }}</textarea>
                </div>

                <!-- Video kỷ niệm & Ngày cưới -->
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                            Video Phóng Sự Cưới (YouTube / Vimeo Embed URL)
                        </label>
                        <input type="url" name="video_url" value="{{ old('video_url', $keepsake->video_url) }}" placeholder="https://www.youtube.com/watch?v=..." style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; box-sizing: border-box;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                            Ngày Kỷ Niệm Cưới
                        </label>
                        <input type="date" name="anniversary_date" value="{{ old('anniversary_date', $keepsake->anniversary_date ? $keepsake->anniversary_date->format('Y-m-d') : ($wedding->wedding_date ? $wedding->wedding_date->format('Y-m-d') : '')) }}" style="width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-family: inherit; box-sizing: border-box;">
                    </div>
                </div>

                @if($keepsake->video_url && $keepsake->getEmbeddedVideoUrl())
                    <div style="margin-bottom: 20px; padding: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                        <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 8px;">Xem thử Video Kỷ Niệm:</div>
                        <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 6px;">
                            <iframe src="{{ $keepsake->getEmbeddedVideoUrl() }}" style="position: absolute; top:0; left: 0; width: 100%; height: 100%; border: 0;" allowfullscreen></iframe>
                        </div>
                    </div>
                @endif

                <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-top: 24px; margin-bottom: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                    Tùy Chọn Hiển Thị Các Khối Trên Trang Kỷ Niệm
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; background: #ffffff;">
                        <input type="hidden" name="hide_rsvp" value="0">
                        <input type="checkbox" name="hide_rsvp" value="1" {{ $keepsake->hide_rsvp ? 'checked' : '' }}>
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: #1e293b;">Ẩn Form RSVP (Đóng xác nhận)</div>
                            <div style="font-size: 11px; color: #64748b;">Ngăn khách mới gửi đăng ký tham dự</div>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; background: #ffffff;">
                        <input type="hidden" name="hide_gifts" value="0">
                        <input type="checkbox" name="hide_gifts" value="1" {{ $keepsake->hide_gifts ? 'checked' : '' }}>
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: #1e293b;">Ẩn Tài Khoản Mừng Cưới</div>
                            <div style="font-size: 11px; color: #64748b;">Ẩn khối VietQR và tài khoản ngân hàng</div>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; background: #ffffff;">
                        <input type="hidden" name="show_wishes" value="0">
                        <input type="checkbox" name="show_wishes" value="1" {{ $keepsake->show_wishes ? 'checked' : '' }}>
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: #1e293b;">Hiển Thị Sổ Lưu Bút Kỷ Niệm</div>
                            <div style="font-size: 11px; color: #64748b;">Cho phép xem lại lời chúc &amp; chữ ký số</div>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; gap: 10px; padding: 12px 14px; border: 1px solid #e2e8f0; border-radius: 8px; cursor: pointer; background: #ffffff;">
                        <input type="hidden" name="show_photos" value="0">
                        <input type="checkbox" name="show_photos" value="1" {{ $keepsake->show_photos ? 'checked' : '' }}>
                        <div>
                            <div style="font-size: 13px; font-weight: 600; color: #1e293b;">Hiển Thị Album Khoảnh Khắc</div>
                            <div style="font-size: 11px; color: #64748b;">Trình chiếu bộ sưu tập ảnh ngày vui</div>
                        </div>
                    </label>
                </div>

                <div style="text-align: right;">
                    <button type="submit" style="padding: 10px 24px; background: #1e293b; color: #ffffff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                        Lưu Cấu Hình Kỷ Niệm
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- TAB 2: Hộp Thư Thời Gian (Time Capsule) -->
    <div id="tab-pane-capsules" style="display: none;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">
                        Hộp Thư Kỷ Niệm Thời Gian (Time Capsule Vault)
                    </h3>
                    <p style="font-size: 13px; color: #64748b; margin: 0;">
                        Những bức thư niêm phong sáp gửi tới tương lai (Kỷ niệm 1 năm, 5 năm, 10 năm ngày cưới).
                    </p>
                </div>
                <span style="font-size: 12px; font-weight: 700; background: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 12px;">
                    {{ $timeCapsules->count() }} bức thư đã gửi
                </span>
            </div>

            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: 600; font-size: 11px; text-transform: uppercase;">
                            <th style="padding: 12px 16px;">Người Gửi &amp; Nhận</th>
                            <th style="padding: 12px 16px;">Tiêu Đề Bức Thư</th>
                            <th style="padding: 12px 16px;">Cột Mốc / Ngày Mở Khóa</th>
                            <th style="padding: 12px 16px; text-align: center;">Trạng Thái Niêm Phong</th>
                            <th style="padding: 12px 16px; text-align: right;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($timeCapsules as $tc)
                            <tr style="border-bottom: 1px solid #f1f5f9;" id="capsule-row-{{ $tc->id }}">
                                <td style="padding: 14px 16px;">
                                    <div style="font-weight: 600; color: #1e293b;">
                                        {{ $tc->sender_name ?: 'Người giấu tên' }}
                                    </div>
                                    <div style="font-size: 11px; color: #64748b;">
                                        Gửi tới: <strong>{{ $tc->recipient_name ?: 'Cặp đôi' }}</strong>
                                    </div>
                                </td>

                                <td style="padding: 14px 16px;">
                                    <div style="font-weight: 600; color: #334155; margin-bottom: 2px;">
                                        {{ $tc->title }}
                                    </div>
                                    <div style="font-size: 12px; color: #64748b; max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        @if($tc->is_sealed)
                                            <span style="color: #d97706; font-style: italic;">[Nội dung đang niêm phong sáp bảo mật]</span>
                                        @else
                                            {{ $tc->content }}
                                        @endif
                                    </div>
                                </td>

                                <td style="padding: 14px 16px;">
                                    <div style="font-weight: 600; color: #1e293b;">
                                        {{ $tc->unlock_date ? $tc->unlock_date->format('d/m/Y') : 'Chưa định' }}
                                    </div>
                                    <span style="font-size: 11px; color: #64748b;">
                                        Kỷ niệm {{ $tc->milestone_years }} năm
                                    </span>
                                </td>

                                <td style="padding: 14px 16px; text-align: center;">
                                    @if($tc->is_sealed)
                                        <span id="capsule-status-{{ $tc->id }}" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 12px; background: #fef3c7; color: #b45309; font-size: 11px; font-weight: 700;">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #d97706;"></span>
                                            Niêm Phong Sáp
                                        </span>
                                    @else
                                        <span style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 12px; background: #ecfdf5; color: #047857; font-size: 11px; font-weight: 700;">
                                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981;"></span>
                                            Đã Mở Niêm Phong
                                        </span>
                                    @endif
                                </td>

                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        @if($tc->is_sealed)
                                            <button type="button" onclick="unlockCapsule({{ $tc->id }})" id="btn-unlock-{{ $tc->id }}" style="padding: 6px 12px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; color: #d97706; font-size: 12px; font-weight: 600; cursor: pointer;">
                                                Mở Sớm
                                            </button>
                                        @endif
                                        <button type="button" onclick="deleteCapsule({{ $tc->id }})" style="padding: 6px 10px; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 6px; color: #e11d48; font-size: 12px; font-weight: 600; cursor: pointer;">
                                            Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="padding: 30px; text-align: center; color: #64748b;">
                                    Chưa có bức thư gửi tương lai nào trong Hộp Thư Thời Gian của tiệc cưới này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 3: Báo Cáo Tổng Kết & Đóng Gói Lưu Trữ -->
    <div id="tab-pane-wrapup" style="display: none;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); margin-bottom: 24px;">
            <div style="margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">
                    Báo Cáo Tổng Kết Sự Kiện Hôn Lễ (Event Wrap-up Ledger)
                </h3>
                <p style="font-size: 13px; color: #64748b; margin: 0;">
                    Đối soát toàn bộ dữ liệu tổ chức tiệc cưới, đảm bảo quyền riêng tư dữ liệu cá nhân (Nghị định 13/2023/NĐ-CP &amp; Luật 91/2025/QH15).
                </p>
            </div>

            <!-- Bảng đối soát -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;">
                    <div style="font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 8px;">1. DANH BẠ KHÁCH MỜI</div>
                    <div style="font-size: 13px; color: #334155; line-height: 1.8;">
                        • Tổng số khách trong danh bạ: <strong>{{ $summary['total_guests'] }}</strong> người<br>
                        • Xác nhận tham dự (RSVP): <strong>{{ $summary['attended_guests'] }}</strong> khách<br>
                        • Trạng thái đồng bộ: Đã số hóa 100%
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;">
                    <div style="font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 8px;">2. TỔNG KẾT QUÀ MỪNG CƯỚI</div>
                    <div style="font-size: 13px; color: #334155; line-height: 1.8;">
                        • Tổng số lượt ghi nhận: <strong>{{ $summary['total_gifts'] }}</strong> lượt<br>
                        • Tổng số tiền mừng: <strong style="color: #059669;">{{ number_format($summary['total_gift_amount']) }} đ</strong><br>
                        • Bảo mật số tài khoản: Đã che định dạng Least Privilege
                    </div>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;">
                    <div style="font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 8px;">3. SỔ LƯU BÚT &amp; KỶ NIỆM</div>
                    <div style="font-size: 13px; color: #334155; line-height: 1.8;">
                        • Tổng số lời chúc lưu niệm: <strong>{{ $summary['total_wishes'] }}</strong> lời chúc<br>
                        • Thư gửi tương lai: <strong>{{ $timeCapsules->count() }}</strong> bức thư<br>
                        • Đóng gói lưu trữ lần cuối: <strong>{{ $keepsake->last_exported_at ? $keepsake->last_exported_at->format('d/m/Y H:i:s') : 'Chưa xuất' }}</strong>
                    </div>
                </div>
            </div>

            <!-- Tải gói lưu trữ -->
            <div style="background: #fdf2f8; border: 1px solid #fbcfe8; border-radius: 8px; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                <div>
                    <div style="font-weight: 700; font-size: 14px; color: #9d174d; margin-bottom: 4px;">
                        Tải Gói Đóng Gói Toàn Diện Hậu Hôn Lễ (Keepsake Data Archive)
                    </div>
                    <div style="font-size: 12px; color: #831843;">
                        Tệp JSON lưu trữ trọn đời bao gồm toàn bộ danh bạ khách, quà mừng, lời lưu bút kỷ niệm, thư thời gian để bàn giao cho cặp đôi.
                    </div>
                </div>
                <a href="{{ route('admin.keepsakes.export_archive', $wedding->id) }}" style="padding: 12px 20px; background: #be185d; color: #ffffff; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" x2="12" y1="15" y2="3"></line></svg>
                    Tải Tệp JSON Ngay
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function switchKeepsakeTab(tabId) {
    const tabs = ['settings', 'capsules', 'wrapup'];
    tabs.forEach(t => {
        const pane = document.getElementById('tab-pane-' + t);
        const btn = document.getElementById('tab-btn-' + t);
        if (pane && btn) {
            if (t === tabId) {
                pane.style.display = 'block';
                btn.style.color = '#1e293b';
                btn.style.borderBottomColor = '#1e293b';
                btn.style.fontWeight = '700';
            } else {
                pane.style.display = 'none';
                btn.style.color = '#64748b';
                btn.style.borderBottomColor = 'transparent';
                btn.style.fontWeight = '600';
            }
        }
    });
}

function unlockCapsule(capsuleId) {
    if (!confirm('Bạn có chắc chắn muốn mở niêm phong sớm bức thư kỷ niệm này không?')) return;

    const btn = document.getElementById('btn-unlock-' + capsuleId);
    if (btn) btn.disabled = true;

    fetch('/admin/keepsakes/capsules/' + capsuleId + '/unlock', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra.');
            if (btn) btn.disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi kết nối máy chủ.');
        if (btn) btn.disabled = false;
    });
}

function deleteCapsule(capsuleId) {
    if (!confirm('CẢNH BÁO: Bức thư này sẽ bị xóa vĩnh viễn khỏi Hộp thư thời gian. Bạn có chắc chắn không?')) return;

    fetch('/admin/keepsakes/capsules/' + capsuleId, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const row = document.getElementById('capsule-row-' + capsuleId);
            if (row) row.remove();
        } else {
            alert(data.message || 'Không thể xóa bức thư.');
        }
    })
    .catch(err => {
        console.error(err);
        alert('Lỗi kết nối máy chủ.');
    });
}
</script>
@endsection

