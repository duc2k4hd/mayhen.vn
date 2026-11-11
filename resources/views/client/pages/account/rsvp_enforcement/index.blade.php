@extends('client.pages.account.layout')

@php
    $pageTitle = 'Quản Lý Hạn Chót RSVP & Khảo Sát Đưa Đón / Thực Đơn — Mây Hẹn';
    $wedding = $invitation->wedding;
    $coupleName = ($wedding->groom_name ?? 'Chú rể') . ' & ' . ($wedding->bride_name ?? 'Cô dâu');
@endphp

@section('account_breadcrumb', 'Hạn Chót & Khảo Sát RSVP')

@section('account_content')
<div class="mayhen_rsvpenf_container">
    {{-- Header & Headline --}}
    <div class="mayhen_rsvpenf_header">
        <div class="mayhen_rsvpenf_headline">
            <div class="mayhen_rsvpenf_badge">RSVP Deadline &amp; Guest Experience Suite</div>
            <h1 class="mayhen_rsvpenf_title">Hạn Chót RSVP, Xe Đưa Đón &amp; Khẩu Vị Tiệc Cưới</h1>
            <p class="mayhen_rsvpenf_subtitle">
                Thiết lập hạn chót xác nhận tham dự để kịp chốt bàn với trung tâm tiệc cưới của <strong>{{ $coupleName }}</strong>, đồng thời khảo sát nhu cầu xe buýt đưa đón và khẩu vị thực đơn (ăn chay, dị ứng thực phẩm) để tiếp đón quan khách chu đáo nhất.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mayhen_rsvpenf_alert mayhen_rsvpenf_alert--success">
            <span class="mayhen_rsvpenf_alert_icon">✓</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mayhen_rsvpenf_alert mayhen_rsvpenf_alert--danger">
            <span class="mayhen_rsvpenf_alert_icon">!</span>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Thẻ Trạng Thái Hạn Chót Tổng Quan --}}
    <div class="mayhen_rsvpenf_status_card {{ $deadlineStatus['is_open'] ? 'is-open' : 'is-closed' }}">
        <div class="mayhen_rsvpenf_status_info">
            <div class="mayhen_rsvpenf_status_indicator">
                <span class="mayhen_rsvpenf_status_dot"></span>
            </div>
            <div>
                <div class="mayhen_rsvpenf_status_sub">Tình trạng cổng xác nhận RSVP:</div>
                <h3 class="mayhen_rsvpenf_status_title">
                    @if($invitation->isRsvpFrozen())
                        ĐÃ ĐÓNG BĂNG HẬU HÔN LỄ
                    @elseif($deadlineStatus['is_manually_closed'])
                        ĐÃ CHỦ ĐỘNG KHÓA FORM RSVP
                    @elseif($deadlineStatus['is_expired'])
                        ĐÃ HẾT HẠN CHÓT XÁC NHẬN
                    @else
                        ĐANG MỞ TIẾP NHẬN PHẢN HỒI
                    @endif
                </h3>
                <p class="mayhen_rsvpenf_status_meta">
                    @if(!empty($deadlineStatus['formatted_deadline']))
                        Hạn chót: <strong>{{ $deadlineStatus['formatted_deadline'] }}</strong> — 
                    @endif
                    <span class="mayhen_rsvpenf_chip {{ $deadlineStatus['badge_class'] }}">
                        {{ $deadlineStatus['status_label'] }}
                    </span>
                </p>
            </div>
        </div>
        <div class="mayhen_rsvpenf_metrics_quick">
            <div class="mayhen_rsvpenf_metric_box">
                <div class="mayhen_rsvpenf_metric_num">{{ $shuttleReport['total_passengers'] }}</div>
                <div class="mayhen_rsvpenf_metric_lbl">Khách cần xe đưa đón</div>
            </div>
            <div class="mayhen_rsvpenf_metric_box">
                <div class="mayhen_rsvpenf_metric_num">{{ $dietaryReport['preference_counts']['vegetarian'] + $dietaryReport['preference_counts']['vegan'] }}</div>
                <div class="mayhen_rsvpenf_metric_lbl">Suất ăn chay / thuần chay</div>
            </div>
            <div class="mayhen_rsvpenf_metric_box">
                <div class="mayhen_rsvpenf_metric_num">{{ count($dietaryReport['special_dietary_list']) }}</div>
                <div class="mayhen_rsvpenf_metric_lbl">Yêu cầu khẩu vị đặc biệt</div>
            </div>
        </div>
    </div>

    {{-- Form Cài Đặt Chính --}}
    <form action="{{ route('account.rsvp_enforcement.update', $invitation->id) }}" method="POST" id="rsvpenfForm" class="mayhen_rsvpenf_form">
        @csrf

        {{-- Section 1: Hạn Chót & Khóa Nhận Phản Hồi --}}
        <div class="mayhen_rsvpenf_card">
            <div class="mayhen_rsvpenf_card_header">
                <div class="mayhen_rsvpenf_card_icon">⏳</div>
                <div>
                    <h2 class="mayhen_rsvpenf_card_title">1. Quy Định Hạn Chót &amp; Khóa Form Nhận Lời Mời</h2>
                    <p class="mayhen_rsvpenf_card_desc">Giúp chủ tiệc kiểm soát thời điểm chốt bàn tiệc với nhà hàng và tự động đóng form khi hết hạn.</p>
                </div>
            </div>

            <div class="mayhen_rsvpenf_grid_2">
                <div class="mayhen_rsvpenf_field">
                    <label class="mayhen_rsvpenf_label" for="deadline">
                        Ngày hạn chót xác nhận tham dự
                        <span class="mayhen_rsvpenf_hint">(Khuyến nghị: trước ngày cưới 5 - 7 ngày)</span>
                    </label>
                    <input type="date" id="deadline" name="deadline" class="mayhen_rsvpenf_input"
                           value="{{ old('deadline', $enforcementConfig['deadline']) }}">
                </div>

                <div class="mayhen_rsvpenf_field">
                    <label class="mayhen_rsvpenf_label" for="contact_phone">
                        Hotline liên hệ khi form đóng
                        <span class="mayhen_rsvpenf_hint">(Số điện thoại cô dâu/chú rể để khách gọi trực tiếp)</span>
                    </label>
                    <input type="text" id="contact_phone" name="contact_phone" class="mayhen_rsvpenf_input"
                           placeholder="Ví dụ: 0901234567"
                           value="{{ old('contact_phone', $enforcementConfig['contact_phone']) }}">
                </div>
            </div>

            <div class="mayhen_rsvpenf_toggles">
                <label class="mayhen_rsvpenf_checkbox_label">
                    <input type="checkbox" name="lock_after_deadline" value="1" {{ !empty($enforcementConfig['lock_after_deadline']) ? 'checked' : '' }}>
                    <span>
                        <strong>Tự động khóa cổng RSVP khi quá hạn chót:</strong>
                        <span class="mayhen_rsvpenf_subdesc">Khách truy cập sau hạn chót sẽ thấy thông báo trang trọng và số điện thoại liên hệ thay vì form gửi.</span>
                    </span>
                </label>

                <label class="mayhen_rsvpenf_checkbox_label mayhen_rsvpenf_checkbox_label--danger">
                    <input type="checkbox" name="is_manually_closed" value="1" {{ !empty($enforcementConfig['is_manually_closed']) ? 'checked' : '' }}>
                    <span>
                        <strong>Chủ động đóng nhận phản hồi ngay lập tức (Khóa thủ công):</strong>
                        <span class="mayhen_rsvpenf_subdesc">Dành cho trường hợp đã đủ số bàn tiệc sớm hơn dự kiến và muốn ngừng nhận thêm khách xác nhận ngay.</span>
                    </span>
                </label>
            </div>

            <div class="mayhen_rsvpenf_field">
                <label class="mayhen_rsvpenf_label" for="closed_notice">
                    Thông báo trang trọng khi form RSVP đã đóng
                </label>
                <textarea id="closed_notice" name="closed_notice" rows="3" class="mayhen_rsvpenf_textarea">{{ old('closed_notice', $enforcementConfig['closed_notice']) }}</textarea>
            </div>
        </div>

        {{-- Section 2: Dịch Vụ Xe Buýt Đưa Đón --}}
        <div class="mayhen_rsvpenf_card">
            <div class="mayhen_rsvpenf_card_header">
                <div class="mayhen_rsvpenf_card_icon">🚌</div>
                <div class="mayhen_rsvpenf_card_title_row">
                    <div>
                        <h2 class="mayhen_rsvpenf_card_title">2. Khảo Sát &amp; Điều Phối Xe Buýt Đưa Đón Khách Mời</h2>
                        <p class="mayhen_rsvpenf_card_desc">Thích hợp cho đám cưới tổ chức tại resort, bãi biển hoặc ngoại ô có xe đón khách từ điểm tập trung.</p>
                    </div>
                    <label class="mayhen_rsvpenf_switch">
                        <input type="checkbox" name="shuttle_bus_enabled" id="shuttleBusToggle" value="1" {{ !empty($enforcementConfig['shuttle_bus']['enabled']) ? 'checked' : '' }}>
                        <span class="mayhen_rsvpenf_slider"></span>
                        <span class="mayhen_rsvpenf_switch_text">Bật xe đưa đón</span>
                    </label>
                </div>
            </div>

            <div id="shuttleBusContent" class="mayhen_rsvpenf_collapsible {{ !empty($enforcementConfig['shuttle_bus']['enabled']) ? 'is-visible' : '' }}">
                <div class="mayhen_rsvpenf_grid_2">
                    <div class="mayhen_rsvpenf_field">
                        <label class="mayhen_rsvpenf_label" for="shuttle_bus_title">Tiêu đề khảo sát trên thiệp</label>
                        <input type="text" id="shuttle_bus_title" name="shuttle_bus_title" class="mayhen_rsvpenf_input"
                               value="{{ old('shuttle_bus_title', $enforcementConfig['shuttle_bus']['title']) }}">
                    </div>
                    <div class="mayhen_rsvpenf_field">
                        <label class="mayhen_rsvpenf_label" for="shuttle_bus_description">Mô tả &amp; hướng dẫn đón khách</label>
                        <input type="text" id="shuttle_bus_description" name="shuttle_bus_description" class="mayhen_rsvpenf_input"
                               value="{{ old('shuttle_bus_description', $enforcementConfig['shuttle_bus']['description']) }}">
                    </div>
                </div>

                <div class="mayhen_rsvpenf_subhead">
                    <h3>Danh sách các điểm đón xe</h3>
                    <button type="button" class="mayhen_rsvpenf_btn_outline" id="addPickupPointBtn">+ Thêm điểm đón mới</button>
                </div>

                <div id="pickupPointsContainer" class="mayhen_rsvpenf_pickup_list">
                    @foreach($enforcementConfig['shuttle_bus']['pickup_points'] as $index => $point)
                        <div class="mayhen_rsvpenf_point_item" data-point-index="{{ $index }}">
                            <div class="mayhen_rsvpenf_point_header">
                                <span class="mayhen_rsvpenf_point_tag">Điểm đón #{{ $index + 1 }}</span>
                                <button type="button" class="mayhen_rsvpenf_point_del" onclick="removePickupPoint(this)">✕ Xóa điểm này</button>
                            </div>
                            <input type="hidden" name="pickup_points[{{ $index }}][id]" value="{{ $point['id'] ?? 'point-' . ($index + 1) }}">
                            <div class="mayhen_rsvpenf_grid_3">
                                <div class="mayhen_rsvpenf_field">
                                    <label class="mayhen_rsvpenf_label">Tên điểm đón *</label>
                                    <input type="text" name="pickup_points[{{ $index }}][name]" class="mayhen_rsvpenf_input" required
                                           placeholder="Ví dụ: Nhà Hát Thành Phố" value="{{ $point['name'] ?? '' }}">
                                </div>
                                <div class="mayhen_rsvpenf_field">
                                    <label class="mayhen_rsvpenf_label">Địa chỉ đón</label>
                                    <input type="text" name="pickup_points[{{ $index }}][address]" class="mayhen_rsvpenf_input"
                                           placeholder="Số nhà, tên đường, quận..." value="{{ $point['address'] ?? '' }}">
                                </div>
                                <div class="mayhen_rsvpenf_field">
                                    <label class="mayhen_rsvpenf_label">Giờ xuất phát</label>
                                    <input type="text" name="pickup_points[{{ $index }}][departure_time]" class="mayhen_rsvpenf_input"
                                           placeholder="Ví dụ: 15:30" value="{{ $point['departure_time'] ?? '' }}">
                                </div>
                            </div>
                            <div class="mayhen_rsvpenf_grid_2">
                                <div class="mayhen_rsvpenf_field">
                                    <label class="mayhen_rsvpenf_label">Tên phụ trách xe / Trưởng đoàn</label>
                                    <input type="text" name="pickup_points[{{ $index }}][coordinator_name]" class="mayhen_rsvpenf_input"
                                           placeholder="Ví dụ: Anh Tuấn" value="{{ $point['coordinator_name'] ?? '' }}">
                                </div>
                                <div class="mayhen_rsvpenf_field">
                                    <label class="mayhen_rsvpenf_label">Số điện thoại phụ trách</label>
                                    <input type="text" name="pickup_points[{{ $index }}][coordinator_phone]" class="mayhen_rsvpenf_input"
                                           placeholder="Số điện thoại" value="{{ $point['coordinator_phone'] ?? '' }}">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Section 3: Khảo Sát Khẩu Vị & Thực Đơn Tiệc Cưới --}}
        <div class="mayhen_rsvpenf_card">
            <div class="mayhen_rsvpenf_card_header">
                <div class="mayhen_rsvpenf_card_icon">🥗</div>
                <div class="mayhen_rsvpenf_card_title_row">
                    <div>
                        <h2 class="mayhen_rsvpenf_card_title">3. Khảo Sát Thực Đơn &amp; Khẩu Vị Tiệc Cưới (Ăn Chay, Dị Ứng)</h2>
                        <p class="mayhen_rsvpenf_card_desc">Cho phép khách chọn thực đơn chay, thực đơn trẻ em hoặc ghi chú dị ứng để chuyển cho bếp trưởng.</p>
                    </div>
                    <label class="mayhen_rsvpenf_switch">
                        <input type="checkbox" name="dietary_survey_enabled" id="dietarySurveyToggle" value="1" {{ !empty($enforcementConfig['dietary_survey']['enabled']) ? 'checked' : '' }}>
                        <span class="mayhen_rsvpenf_slider"></span>
                        <span class="mayhen_rsvpenf_switch_text">Bật khảo sát món</span>
                    </label>
                </div>
            </div>

            <div id="dietarySurveyContent" class="mayhen_rsvpenf_collapsible {{ !empty($enforcementConfig['dietary_survey']['enabled']) ? 'is-visible' : '' }}">
                <div class="mayhen_rsvpenf_grid_2">
                    <div class="mayhen_rsvpenf_field">
                        <label class="mayhen_rsvpenf_label" for="dietary_survey_title">Tiêu đề khảo sát thực đơn</label>
                        <input type="text" id="dietary_survey_title" name="dietary_survey_title" class="mayhen_rsvpenf_input"
                               value="{{ old('dietary_survey_title', $enforcementConfig['dietary_survey']['title']) }}">
                    </div>
                    <div class="mayhen_rsvpenf_field">
                        <label class="mayhen_rsvpenf_label" for="dietary_survey_description">Lời ngỏ chu đáo đến khách</label>
                        <input type="text" id="dietary_survey_description" name="dietary_survey_description" class="mayhen_rsvpenf_input"
                               value="{{ old('dietary_survey_description', $enforcementConfig['dietary_survey']['description']) }}">
                    </div>
                </div>

                <div class="mayhen_rsvpenf_dietary_options">
                    <div class="mayhen_rsvpenf_dietary_option_item">
                        <span class="mayhen_dietary_icon">🥩</span>
                        <div>
                            <strong>Thực đơn tiêu chuẩn</strong>
                            <p>Đầy đủ các món sơn hào hải vị theo set tiệc cưới chuẩn.</p>
                        </div>
                    </div>
                    <div class="mayhen_rsvpenf_dietary_option_item">
                        <span class="mayhen_dietary_icon">🥬</span>
                        <div>
                            <strong>Thực đơn chay / Thuần chay (Vegetarian / Vegan)</strong>
                            <p>Món chay thanh tịnh, chế biến sạch sẽ riêng biệt.</p>
                        </div>
                    </div>
                    <div class="mayhen_rsvpenf_dietary_option_item">
                        <span class="mayhen_dietary_icon">☪️</span>
                        <div>
                            <strong>Thực đơn Halal / Không thịt heo</strong>
                            <p>Phục vụ theo tín ngưỡng văn hóa và sở thích ăn uống của khách.</p>
                        </div>
                    </div>
                    <div class="mayhen_rsvpenf_dietary_option_item">
                        <span class="mayhen_dietary_icon">👶</span>
                        <div>
                            <strong>Thực đơn trẻ em</strong>
                            <p>Các món dễ ăn, khẩu phần vừa vặn cho các bé đi cùng bố mẹ.</p>
                        </div>
                    </div>
                    <div class="mayhen_rsvpenf_dietary_option_item">
                        <span class="mayhen_dietary_icon">⚠️</span>
                        <div>
                            <strong>Dị ứng thực phẩm đặc biệt</strong>
                            <p>Ghi nhận dị ứng hải sản, hạt đậu phộng, gluten hoặc sữa.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Thanh Nút Thao Tác Lưu Cài Đặt --}}
        <div class="mayhen_rsvpenf_actions">
            <a href="{{ route('account.invitations.settings', $invitation->id) }}" class="mayhen_rsvpenf_btn_back">← Quay lại cài đặt thiệp</a>
            <button type="submit" class="mayhen_rsvpenf_btn_primary">Lưu Cấu Hình Hạn Chót &amp; Khảo Sát</button>
        </div>
    </form>

    {{-- Bảng Báo Cáo 1: Danh Sách Xe Buýt Đưa Đón --}}
    <div class="mayhen_rsvpenf_report_section">
        <div class="mayhen_rsvpenf_report_header">
            <div>
                <h2 class="mayhen_rsvpenf_report_title">Bảng Kê Điều Phối Xe Buýt Đưa Đón (Shuttle Bus Manifest)</h2>
                <p class="mayhen_rsvpenf_report_sub">Tổng cộng: <strong>{{ $shuttleReport['total_passengers'] }}</strong> chỗ ngồi / <strong>{{ $shuttleReport['total_registrations'] }}</strong> lượt đăng ký</p>
            </div>
            @if($shuttleReport['total_passengers'] > 0)
                <a href="{{ route('account.rsvp_enforcement.export_shuttle', $invitation->id) }}" class="mayhen_rsvpenf_btn_export">
                    📥 Tải File CSV Cho Đội Xe (UTF-8 BOM)
                </a>
            @endif
        </div>

        @if($shuttleReport['total_passengers'] === 0)
            <div class="mayhen_rsvpenf_empty_state">
                <span class="mayhen_rsvpenf_empty_icon">🚌</span>
                <p>Chưa có khách mời nào đăng ký xe đưa đón hoặc tính năng đang tắt.</p>
            </div>
        @else
            <div class="mayhen_rsvpenf_points_grid">
                @foreach($shuttleReport['by_point'] as $pt)
                    <div class="mayhen_rsvpenf_point_card">
                        <div class="mayhen_rsvpenf_point_top">
                            <div>
                                <h3 class="mayhen_rsvpenf_pt_name">{{ $pt['name'] }}</h3>
                                <div class="mayhen_rsvpenf_pt_meta">
                                    <span>📍 {{ $pt['address'] ?: 'Điểm hẹn trung tâm' }}</span>
                                    <span>⏰ Giờ đón: {{ $pt['departure_time'] ?: '—' }}</span>
                                </div>
                            </div>
                            <div class="mayhen_rsvpenf_pt_count">
                                <strong>{{ $pt['passengers_count'] }}</strong> chỗ
                            </div>
                        </div>

                        @if(!empty($pt['coordinator_name']) || !empty($pt['coordinator_phone']))
                            <div class="mayhen_rsvpenf_pt_coord">
                                Phụ trách: <strong>{{ $pt['coordinator_name'] }}</strong> ({{ $pt['coordinator_phone'] }})
                            </div>
                        @endif

                        <div class="mayhen_rsvpenf_table_responsive">
                            <table class="mayhen_rsvpenf_table">
                                <thead>
                                    <tr>
                                        <th>Khách mời</th>
                                        <th>Điện thoại</th>
                                        <th>Số chỗ</th>
                                        <th>Bàn tiệc</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pt['guests'] as $g)
                                        <tr>
                                            <td><strong>{{ $g['guest_name'] }}</strong></td>
                                            <td>{{ $g['phone'] }}</td>
                                            <td><span class="mayhen_rsvpenf_badge_pill">{{ $g['passengers'] }}</span></td>
                                            <td>{{ $g['table_name'] }}</td>
                                            <td>{{ $g['dietary'] ?: ($g['wishes'] ?: '—') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Chưa có khách chọn điểm này.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(!empty($shuttleReport['unassigned_list']))
                <div class="mayhen_rsvpenf_point_card mayhen_rsvpenf_point_card--unassigned">
                    <h3 class="mayhen_rsvpenf_pt_name">Khách đăng ký khác hoặc chưa gán điểm đón cụ thể</h3>
                    <div class="mayhen_rsvpenf_table_responsive">
                        <table class="mayhen_rsvpenf_table">
                            <thead>
                                <tr>
                                    <th>Khách mời</th>
                                    <th>Điện thoại</th>
                                    <th>Số chỗ</th>
                                    <th>Điểm đón ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($shuttleReport['unassigned_list'] as $un)
                                    <tr>
                                        <td>{{ $un['guest_name'] }}</td>
                                        <td>{{ $un['phone'] }}</td>
                                        <td><span class="mayhen_rsvpenf_badge_pill">{{ $un['passengers'] }}</span></td>
                                        <td>{{ $un['requested_point'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif
    </div>

    {{-- Bảng Báo Cáo 2: Thống Kê Thực Đơn & Dị Ứng --}}
    <div class="mayhen_rsvpenf_report_section">
        <div class="mayhen_rsvpenf_report_header">
            <div>
                <h2 class="mayhen_rsvpenf_report_title">Bảng Kê Thực Đơn &amp; Yêu Cầu Dị Ứng Cho Bếp Trưởng</h2>
                <p class="mayhen_rsvpenf_report_sub">Tổng số khách xác nhận tham dự: <strong>{{ $dietaryReport['total_confirmed_guests'] }}</strong> người</p>
            </div>
            @if(count($dietaryReport['special_dietary_list']) > 0)
                <a href="{{ route('account.rsvp_enforcement.export_dietary', $invitation->id) }}" class="mayhen_rsvpenf_btn_export">
                    📥 Tải File CSV Cho Bếp Trưởng (UTF-8 BOM)
                </a>
            @endif
        </div>

        <div class="mayhen_rsvpenf_dietary_grid">
            <div class="mayhen_rsvpenf_dietary_stat_card">
                <div class="mayhen_rsvpenf_dietary_stat_num">{{ $dietaryReport['preference_counts']['standard'] }}</div>
                <div class="mayhen_rsvpenf_dietary_stat_lbl">🥩 Thực đơn tiêu chuẩn</div>
            </div>
            <div class="mayhen_rsvpenf_dietary_stat_card">
                <div class="mayhen_rsvpenf_dietary_stat_num">{{ $dietaryReport['preference_counts']['vegetarian'] }}</div>
                <div class="mayhen_rsvpenf_dietary_stat_lbl">🥬 Thực đơn chay</div>
            </div>
            <div class="mayhen_rsvpenf_dietary_stat_card">
                <div class="mayhen_rsvpenf_dietary_stat_num">{{ $dietaryReport['preference_counts']['vegan'] }}</div>
                <div class="mayhen_rsvpenf_dietary_stat_lbl">🌱 Thuần chay (Vegan)</div>
            </div>
            <div class="mayhen_rsvpenf_dietary_stat_card">
                <div class="mayhen_rsvpenf_dietary_stat_num">{{ $dietaryReport['preference_counts']['halal'] }}</div>
                <div class="mayhen_rsvpenf_dietary_stat_lbl">☪️ Halal / Không thịt heo</div>
            </div>
            <div class="mayhen_rsvpenf_dietary_stat_card">
                <div class="mayhen_rsvpenf_dietary_stat_num">{{ $dietaryReport['preference_counts']['kids'] }}</div>
                <div class="mayhen_rsvpenf_dietary_stat_lbl">👶 Thực đơn trẻ em</div>
            </div>
        </div>

        @if(empty($dietaryReport['special_dietary_list']))
            <div class="mayhen_rsvpenf_empty_state">
                <span class="mayhen_rsvpenf_empty_icon">🥗</span>
                <p>Toàn bộ khách mời đều dùng thực đơn tiêu chuẩn, không có ghi chú dị ứng nào.</p>
            </div>
        @else
            <div class="mayhen_rsvpenf_point_card">
                <h3 class="mayhen_rsvpenf_pt_name">Danh sách khách có khẩu vị đặc biệt hoặc dị ứng thực phẩm</h3>
                <div class="mayhen_rsvpenf_table_responsive">
                    <table class="mayhen_rsvpenf_table">
                        <thead>
                            <tr>
                                <th>Bàn tiệc</th>
                                <th>Khách mời</th>
                                <th>Điện thoại</th>
                                <th>Khẩu vị đăng ký</th>
                                <th>Chi tiết yêu cầu / Cảnh báo dị ứng</th>
                                <th>Số suất</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dietaryReport['special_dietary_list'] as $sp)
                                <tr>
                                    <td><strong>{{ $sp['table_name'] }}</strong></td>
                                    <td><strong>{{ $sp['guest_name'] }}</strong></td>
                                    <td>{{ $sp['phone'] }}</td>
                                    <td>
                                        <span class="mayhen_rsvpenf_chip badge--warning">{{ $sp['menu_preference'] }}</span>
                                    </td>
                                    <td><strong class="text-danger">{{ $sp['requirements'] }}</strong></td>
                                    <td>{{ $sp['total'] }} ({{ $sp['adults'] }}L + {{ $sp['children'] }}N)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
/* CSS Độc Bản Phân Hệ Quản Lý Hạn Chót & Khảo Sát RSVP - Quiet Luxury */
.mayhen_rsvpenf_container {
    max-width: 1040px;
    margin: 0 auto;
    padding-bottom: 60px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #2C2824;
}
.mayhen_rsvpenf_header {
    margin-bottom: 24px;
}
.mayhen_rsvpenf_badge {
    display: inline-block;
    padding: 4px 12px;
    background: #EFE8DD;
    color: #9C7A4A;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 8px;
}
.mayhen_rsvpenf_title {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 32px;
    font-weight: 600;
    color: #1F1D1A;
    margin: 0 0 8px 0;
}
.mayhen_rsvpenf_subtitle {
    font-size: 14.5px;
    line-height: 1.6;
    color: #6A645A;
    margin: 0;
}
.mayhen_rsvpenf_alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 24px;
    font-size: 14px;
}
.mayhen_rsvpenf_alert--success {
    background: #EBF7EE;
    color: #1E6B38;
    border: 1px solid #C3E6CB;
}
.mayhen_rsvpenf_alert--danger {
    background: #FDF2F2;
    color: #9E1C1C;
    border: 1px solid #F8D7DA;
}
.mayhen_rsvpenf_alert_icon {
    font-weight: bold;
    font-size: 16px;
}

/* Thẻ trạng thái tổng quan */
.mayhen_rsvpenf_status_card {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #FFFFFF;
    border-radius: 14px;
    padding: 24px 28px;
    border: 1px solid #E5DCCD;
    box-shadow: 0 4px 16px rgba(0,0,0,0.03);
    margin-bottom: 28px;
    gap: 20px;
    flex-wrap: wrap;
}
.mayhen_rsvpenf_status_info {
    display: flex;
    align-items: flex-start;
    gap: 16px;
}
.mayhen_rsvpenf_status_indicator {
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 4px;
}
.mayhen_rsvpenf_status_dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: #28A745;
    box-shadow: 0 0 0 4px rgba(40,167,69,0.2);
    animation: mayhen_pulse 2s infinite;
}
.is-closed .mayhen_rsvpenf_status_dot {
    background: #DC3545;
    box-shadow: 0 0 0 4px rgba(220,53,69,0.2);
}
@keyframes mayhen_pulse {
    0% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.1); opacity: 1; }
    100% { transform: scale(0.95); opacity: 0.8; }
}
.mayhen_rsvpenf_status_sub {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #8C8275;
    font-weight: 600;
}
.mayhen_rsvpenf_status_title {
    font-size: 20px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 2px 0 6px 0;
}
.mayhen_rsvpenf_status_meta {
    font-size: 13.5px;
    color: #555047;
    margin: 0;
}
.mayhen_rsvpenf_chip {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}
.badge--success, .badge--open, .badge--active {
    background: #E8F5E9;
    color: #2E7D32;
}
.badge--urgent {
    background: #FFF3E0;
    color: #E65100;
}
.badge--warning {
    background: #FFF8E1;
    color: #F57F17;
}
.badge--closed, .badge--frozen {
    background: #FFEBEE;
    color: #C62828;
}

.mayhen_rsvpenf_metrics_quick {
    display: flex;
    gap: 16px;
}
.mayhen_rsvpenf_metric_box {
    background: #FAF8F5;
    border: 1px solid #EFE8DD;
    border-radius: 10px;
    padding: 12px 18px;
    text-align: center;
    min-width: 120px;
}
.mayhen_rsvpenf_metric_num {
    font-size: 24px;
    font-weight: 700;
    color: #9C7A4A;
    line-height: 1.1;
}
.mayhen_rsvpenf_metric_lbl {
    font-size: 11px;
    color: #7A7265;
    margin-top: 4px;
}

/* Thẻ Card */
.mayhen_rsvpenf_card {
    background: #FFFFFF;
    border-radius: 14px;
    border: 1px solid #E5DCCD;
    padding: 24px 28px;
    margin-bottom: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
}
.mayhen_rsvpenf_card_header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 20px;
}
.mayhen_rsvpenf_card_icon {
    font-size: 26px;
    line-height: 1;
}
.mayhen_rsvpenf_card_title_row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    flex-wrap: wrap;
    gap: 12px;
}
.mayhen_rsvpenf_card_title {
    font-size: 18px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 4px 0;
}
.mayhen_rsvpenf_card_desc {
    font-size: 13.5px;
    color: #6C665C;
    margin: 0;
}

/* Form inputs & grids */
.mayhen_rsvpenf_grid_2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
.mayhen_rsvpenf_grid_3 {
    display: grid;
    grid-template-columns: 1fr 1.5fr 1fr;
    gap: 14px;
}
.mayhen_rsvpenf_field {
    margin-bottom: 16px;
}
.mayhen_rsvpenf_label {
    display: block;
    font-size: 13.5px;
    font-weight: 600;
    color: #38342E;
    margin-bottom: 6px;
}
.mayhen_rsvpenf_hint {
    font-size: 12px;
    font-weight: 400;
    color: #8C8275;
}
.mayhen_rsvpenf_input, .mayhen_rsvpenf_textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 14px;
    font-size: 14px;
    border: 1px solid #D8CEBD;
    border-radius: 8px;
    background: #FAF8F5;
    color: #1F1D1A;
    transition: border-color 0.2s, background 0.2s;
}
.mayhen_rsvpenf_input:focus, .mayhen_rsvpenf_textarea:focus {
    outline: none;
    border-color: #9C7A4A;
    background: #FFFFFF;
}

/* Toggles & Switch */
.mayhen_rsvpenf_toggles {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin: 16px 0 20px 0;
    padding: 14px;
    background: #FAF8F5;
    border-radius: 10px;
    border: 1px solid #EFE8DD;
}
.mayhen_rsvpenf_checkbox_label {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 14px;
    cursor: pointer;
    color: #2C2824;
}
.mayhen_rsvpenf_checkbox_label input[type="checkbox"] {
    margin-top: 3px;
    width: 16px;
    height: 16px;
    accent-color: #9C7A4A;
}
.mayhen_rsvpenf_checkbox_label--danger input[type="checkbox"] {
    accent-color: #DC3545;
}
.mayhen_rsvpenf_subdesc {
    display: block;
    font-size: 12.5px;
    color: #6C665C;
    margin-top: 2px;
}

.mayhen_rsvpenf_switch {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}
.mayhen_rsvpenf_switch input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}
.mayhen_rsvpenf_slider {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    background-color: #D8CEBD;
    border-radius: 24px;
    transition: 0.3s;
}
.mayhen_rsvpenf_slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: 0.3s;
}
.mayhen_rsvpenf_switch input:checked + .mayhen_rsvpenf_slider {
    background-color: #9C7A4A;
}
.mayhen_rsvpenf_switch input:checked + .mayhen_rsvpenf_slider:before {
    transform: translateX(20px);
}
.mayhen_rsvpenf_switch_text {
    font-size: 13.5px;
    font-weight: 600;
    color: #38342E;
}

/* Collapsible */
.mayhen_rsvpenf_collapsible {
    display: none;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px dashed #E5DCCD;
}
.mayhen_rsvpenf_collapsible.is-visible {
    display: block;
}

/* Pickup points dynamic */
.mayhen_rsvpenf_subhead {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0 14px 0;
}
.mayhen_rsvpenf_subhead h3 {
    font-size: 15px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0;
}
.mayhen_rsvpenf_btn_outline {
    background: transparent;
    border: 1px solid #9C7A4A;
    color: #9C7A4A;
    padding: 6px 14px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}
.mayhen_rsvpenf_btn_outline:hover {
    background: #9C7A4A;
    color: #FFFFFF;
}

.mayhen_rsvpenf_pickup_list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
.mayhen_rsvpenf_point_item {
    background: #FAF8F5;
    border: 1px solid #E5DCCD;
    border-radius: 10px;
    padding: 16px 18px;
}
.mayhen_rsvpenf_point_header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.mayhen_rsvpenf_point_tag {
    font-size: 12px;
    font-weight: 700;
    color: #9C7A4A;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.mayhen_rsvpenf_point_del {
    background: transparent;
    border: none;
    color: #DC3545;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}
.mayhen_rsvpenf_point_del:hover {
    text-decoration: underline;
}

/* Dietary Options Preview */
.mayhen_rsvpenf_dietary_options {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 12px;
    margin-top: 14px;
}
.mayhen_rsvpenf_dietary_option_item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #FAF8F5;
    border: 1px solid #EFE8DD;
    padding: 12px 14px;
    border-radius: 8px;
}
.mayhen_dietary_icon {
    font-size: 22px;
    line-height: 1;
}
.mayhen_rsvpenf_dietary_option_item strong {
    font-size: 13.5px;
    color: #1F1D1A;
}
.mayhen_rsvpenf_dietary_option_item p {
    font-size: 12px;
    color: #7A7265;
    margin: 2px 0 0 0;
    line-height: 1.4;
}

/* Action buttons */
.mayhen_rsvpenf_actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    gap: 16px;
}
.mayhen_rsvpenf_btn_back {
    color: #6C665C;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
}
.mayhen_rsvpenf_btn_back:hover {
    color: #1F1D1A;
}
.mayhen_rsvpenf_btn_primary {
    background: #9C7A4A;
    color: #FFFFFF;
    border: none;
    padding: 12px 28px;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(156,122,74,0.25);
    transition: background 0.2s;
}
.mayhen_rsvpenf_btn_primary:hover {
    background: #846538;
}

/* Report Sections */
.mayhen_rsvpenf_report_section {
    margin-top: 48px;
    padding-top: 36px;
    border-top: 1px solid #E5DCCD;
}
.mayhen_rsvpenf_report_header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
}
.mayhen_rsvpenf_report_title {
    font-size: 20px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 4px 0;
}
.mayhen_rsvpenf_report_sub {
    font-size: 13.5px;
    color: #6C665C;
    margin: 0;
}
.mayhen_rsvpenf_btn_export {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #FAF8F5;
    border: 1px solid #9C7A4A;
    color: #9C7A4A;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 13.5px;
    font-weight: 600;
    transition: all 0.2s;
}
.mayhen_rsvpenf_btn_export:hover {
    background: #9C7A4A;
    color: #FFFFFF;
}

.mayhen_rsvpenf_empty_state {
    text-align: center;
    padding: 40px 20px;
    background: #FAF8F5;
    border-radius: 12px;
    border: 1px dashed #D8CEBD;
    color: #8C8275;
}
.mayhen_rsvpenf_empty_icon {
    font-size: 32px;
    display: block;
    margin-bottom: 8px;
}

/* Points Grid & Tables */
.mayhen_rsvpenf_points_grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.mayhen_rsvpenf_point_card {
    background: #FFFFFF;
    border: 1px solid #E5DCCD;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}
.mayhen_rsvpenf_point_card--unassigned {
    border-color: #E2B974;
    background: #FFFDF9;
}
.mayhen_rsvpenf_point_top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}
.mayhen_rsvpenf_pt_name {
    font-size: 16px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 4px 0;
}
.mayhen_rsvpenf_pt_meta {
    font-size: 13px;
    color: #6C665C;
    display: flex;
    gap: 16px;
}
.mayhen_rsvpenf_pt_count {
    background: #EFE8DD;
    color: #9C7A4A;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 13px;
    white-space: nowrap;
}
.mayhen_rsvpenf_pt_coord {
    font-size: 12.5px;
    color: #555047;
    margin-bottom: 12px;
    background: #FAF8F5;
    padding: 6px 12px;
    border-radius: 6px;
}

.mayhen_rsvpenf_table_responsive {
    overflow-x: auto;
    margin-top: 10px;
}
.mayhen_rsvpenf_table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
}
.mayhen_rsvpenf_table th {
    text-align: left;
    padding: 8px 12px;
    background: #FAF8F5;
    color: #7A7265;
    font-weight: 600;
    border-bottom: 1px solid #E5DCCD;
}
.mayhen_rsvpenf_table td {
    padding: 10px 12px;
    border-bottom: 1px solid #EFE8DD;
    color: #2C2824;
}
.mayhen_rsvpenf_badge_pill {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 10px;
    background: #EFE8DD;
    font-weight: 700;
    color: #9C7A4A;
    font-size: 12px;
}

/* Dietary Grid Stats */
.mayhen_rsvpenf_dietary_grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.mayhen_rsvpenf_dietary_stat_card {
    background: #FFFFFF;
    border: 1px solid #E5DCCD;
    border-radius: 10px;
    padding: 16px;
    text-align: center;
}
.mayhen_rsvpenf_dietary_stat_num {
    font-size: 26px;
    font-weight: 700;
    color: #9C7A4A;
    line-height: 1;
}
.mayhen_rsvpenf_dietary_stat_lbl {
    font-size: 12px;
    color: #6C665C;
    margin-top: 6px;
    font-weight: 600;
}

@media (max-width: 768px) {
    .mayhen_rsvpenf_grid_2, .mayhen_rsvpenf_grid_3 {
        grid-template-columns: 1fr;
    }
    .mayhen_rsvpenf_status_card {
        flex-direction: column;
        align-items: flex-start;
    }
    .mayhen_rsvpenf_metrics_quick {
        width: 100%;
        justify-content: space-between;
    }
    .mayhen_rsvpenf_metric_box {
        min-width: 0;
        flex: 1;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Toggle Shuttle Bus content
    const shuttleToggle = document.getElementById('shuttleBusToggle');
    const shuttleContent = document.getElementById('shuttleBusContent');
    if (shuttleToggle && shuttleContent) {
        shuttleToggle.addEventListener('change', function() {
            if (this.checked) {
                shuttleContent.classList.add('is-visible');
            } else {
                shuttleContent.classList.remove('is-visible');
            }
        });
    }

    // 2. Toggle Dietary content
    const dietaryToggle = document.getElementById('dietarySurveyToggle');
    const dietaryContent = document.getElementById('dietarySurveyContent');
    if (dietaryToggle && dietaryContent) {
        dietaryToggle.addEventListener('change', function() {
            if (this.checked) {
                dietaryContent.classList.add('is-visible');
            } else {
                dietaryContent.classList.remove('is-visible');
            }
        });
    }

    // 3. Add pickup point dynamically
    const addBtn = document.getElementById('addPickupPointBtn');
    const container = document.getElementById('pickupPointsContainer');
    if (addBtn && container) {
        addBtn.addEventListener('click', function() {
            const currentItems = container.querySelectorAll('.mayhen_rsvpenf_point_item');
            const nextIndex = currentItems.length;

            const pointDiv = document.createElement('div');
            pointDiv.className = 'mayhen_rsvpenf_point_item';
            pointDiv.setAttribute('data-point-index', nextIndex);
            pointDiv.innerHTML = `
                <div class="mayhen_rsvpenf_point_header">
                    <span class="mayhen_rsvpenf_point_tag">Điểm đón #${nextIndex + 1}</span>
                    <button type="button" class="mayhen_rsvpenf_point_del" onclick="removePickupPoint(this)">✕ Xóa điểm này</button>
                </div>
                <input type="hidden" name="pickup_points[${nextIndex}][id]" value="point-${Date.now()}">
                <div class="mayhen_rsvpenf_grid_3">
                    <div class="mayhen_rsvpenf_field">
                        <label class="mayhen_rsvpenf_label">Tên điểm đón *</label>
                        <input type="text" name="pickup_points[${nextIndex}][name]" class="mayhen_rsvpenf_input" required
                               placeholder="Ví dụ: Trung Tâm Hội Nghị Triển Lãm">
                    </div>
                    <div class="mayhen_rsvpenf_field">
                        <label class="mayhen_rsvpenf_label">Địa chỉ đón</label>
                        <input type="text" name="pickup_points[${nextIndex}][address]" class="mayhen_rsvpenf_input"
                               placeholder="Số nhà, tên đường, quận...">
                    </div>
                    <div class="mayhen_rsvpenf_field">
                        <label class="mayhen_rsvpenf_label">Giờ xuất phát</label>
                        <input type="text" name="pickup_points[${nextIndex}][departure_time]" class="mayhen_rsvpenf_input"
                               placeholder="Ví dụ: 15:45">
                    </div>
                </div>
                <div class="mayhen_rsvpenf_grid_2">
                    <div class="mayhen_rsvpenf_field">
                        <label class="mayhen_rsvpenf_label">Tên phụ trách xe / Trưởng đoàn</label>
                        <input type="text" name="pickup_points[${nextIndex}][coordinator_name]" class="mayhen_rsvpenf_input"
                               placeholder="Ví dụ: Anh Huy">
                    </div>
                    <div class="mayhen_rsvpenf_field">
                        <label class="mayhen_rsvpenf_label">Số điện thoại phụ trách</label>
                        <input type="text" name="pickup_points[${nextIndex}][coordinator_phone]" class="mayhen_rsvpenf_input"
                               placeholder="Số điện thoại">
                    </div>
                </div>
            `;
            container.appendChild(pointDiv);
        });
    }
});

function removePickupPoint(button) {
    const item = button.closest('.mayhen_rsvpenf_point_item');
    const container = document.getElementById('pickupPointsContainer');
    if (item && container) {
        if (container.querySelectorAll('.mayhen_rsvpenf_point_item').length <= 1) {
            alert('Cần có ít nhất một điểm đón xe trong danh sách.');
            return;
        }
        item.remove();
        // Cập nhật lại số thứ tự
        const items = container.querySelectorAll('.mayhen_rsvpenf_point_item');
        items.forEach((elem, idx) => {
            const tag = elem.querySelector('.mayhen_rsvpenf_point_tag');
            if (tag) tag.textContent = 'Điểm đón #' + (idx + 1);
        });
    }
}
</script>
@endsection
