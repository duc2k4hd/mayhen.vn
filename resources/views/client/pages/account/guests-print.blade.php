<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Điểm Danh & Đón Tiếp Quan Khách — {{ $wedding->groom_name }} & {{ $wedding->bride_name }} | {{ config('app.name') }}</title>
    
    <!-- Favicon -->
    @include('client.components.favicon')

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* MAYHEN.VN — GUEST RECEPTION & CHECK-IN SHEET PRINT STYLESHEET */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: #F8F6F0;
            color: #1F1D1A;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Top Bar Thao Tác (Ẩn khi in) */
        .mayhen_print_topbar {
            background: #FFFFFF;
            border-bottom: 1px solid #EAE3D9;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(31, 29, 26, 0.05);
        }

        .mayhen_print_topbar_info {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
            color: #5A534B;
        }

        .mayhen_print_topbar_btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .mayhen_print_topbar_btn_gold {
            background: linear-gradient(135deg, #C5A880 0%, #B8935D 100%);
            color: #FFFFFF;
        }

        .mayhen_print_topbar_btn_gold:hover {
            opacity: 0.92;
            transform: translateY(-1px);
        }

        .mayhen_print_topbar_btn_outline {
            background: #FFFFFF;
            border-color: #D8CEBD;
            color: #4A453E;
        }

        .mayhen_print_topbar_btn_outline:hover {
            border-color: #1F1D1A;
            color: #1F1D1A;
        }

        /* Khung Khổ Sách In A4 */
        .mayhen_print_container {
            max-width: 1000px;
            margin: 28px auto 60px;
            background: #FFFFFF;
            border: 1px solid #E8DFD3;
            border-radius: 8px;
            padding: 40px 48px;
            box-shadow: 0 10px 40px rgba(31, 29, 26, 0.06);
        }

        /* Bìa & Header Trang Trọng */
        .mayhen_print_header {
            text-align: center;
            padding-bottom: 24px;
            margin-bottom: 24px;
            border-bottom: 2px solid #EFE8DD;
            position: relative;
        }

        .mayhen_print_badge {
            display: inline-block;
            font-size: 11px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #9C7A4A;
            margin-bottom: 6px;
            font-weight: 600;
        }

        .mayhen_print_title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 34px;
            font-weight: 700;
            color: #1F1D1A;
            line-height: 1.15;
            margin-bottom: 6px;
        }

        .mayhen_print_couple {
            font-family: 'Cormorant Garamond', serif;
            font-size: 26px;
            font-weight: 600;
            color: #9C7A4A;
            margin-bottom: 10px;
        }

        .mayhen_print_meta {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            font-size: 13px;
            color: #5A534B;
            flex-wrap: wrap;
        }

        /* Thống Kê Bàn Tiệc Nhanh */
        .mayhen_print_stats_bar {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 12px;
            background: #FAF8F5;
            border: 1px solid #EAE3D9;
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 24px;
            text-align: center;
        }

        .mayhen_print_stat_item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .mayhen_print_stat_val {
            font-family: 'Cormorant Garamond', serif;
            font-size: 24px;
            font-weight: 700;
            color: #1F1D1A;
            line-height: 1;
        }

        .mayhen_print_stat_lbl {
            font-size: 11px;
            color: #7A726A;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Bảng Danh Sách Quan Khách */
        .mayhen_print_table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            text-align: left;
            margin-bottom: 32px;
        }

        .mayhen_print_table th {
            background: #FAF8F5;
            border: 1px solid #D8CEBD;
            padding: 10px 12px;
            font-weight: 600;
            color: #332F2B;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .mayhen_print_table td {
            border: 1px solid #EAE3D9;
            padding: 10px 12px;
            vertical-align: middle;
            color: #1F1D1A;
        }

        .mayhen_print_table tr:nth-child(even) td {
            background-color: #FCFBF9;
        }

        .mayhen_print_checkbox_cell {
            width: 60px;
            text-align: center;
        }

        .mayhen_print_check_box {
            display: inline-block;
            width: 22px;
            height: 22px;
            border: 1.5px solid #666;
            border-radius: 4px;
            background: #FFFFFF;
        }

        .mayhen_print_gift_cell {
            width: 140px;
        }

        .mayhen_print_gift_line {
            display: block;
            width: 100%;
            height: 18px;
            border-bottom: 1px dotted #B0A79C;
        }

        .mayhen_print_status_tag {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 600;
        }

        .mayhen_print_status_attending {
            background: #E8F5E9;
            color: #2E7D32;
        }

        .mayhen_print_status_declined {
            background: #FFEBEE;
            color: #C62828;
        }

        .mayhen_print_status_pending {
            background: #F5F5F5;
            color: #757575;
        }

        /* Chân Trang Ký Xác Nhận */
        .mayhen_print_reception_footer {
            margin-top: 36px;
            padding-top: 24px;
            border-top: 1px solid #EAE3D9;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .mayhen_print_sign_col {
            text-align: center;
            min-width: 200px;
        }

        .mayhen_print_sign_col_title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #5A534B;
            margin-bottom: 60px;
        }

        .mayhen_print_sign_col_line {
            border-top: 1px solid #1F1D1A;
            padding-top: 6px;
            font-size: 12px;
            color: #7A726A;
        }

        /* ==========================================================================
           QUY TẮC IN ẤN (@media print)
           ========================================================================== */
        @media print {
            body {
                background: #FFFFFF !important;
                color: #000000 !important;
            }

            .mayhen_print_topbar {
                display: none !important;
            }

            .mayhen_print_container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 10px !important;
                border: none !important;
                box-shadow: none !important;
                background: #FFFFFF !important;
            }

            .mayhen_print_table th {
                background: #F0EDE8 !important;
                color: #000000 !important;
                border-color: #999999 !important;
            }

            .mayhen_print_table td {
                border-color: #CCCCCC !important;
                color: #000000 !important;
            }

            .mayhen_print_check_box {
                border-color: #000000 !important;
            }

            @page {
                size: A4 portrait;
                margin: 10mm 10mm 10mm 10mm;
            }
        }
    </style>
</head>
<body>
    <!-- Top Bar Công Cụ (Ẩn khi in) -->
    <div class="mayhen_print_topbar">
        <div class="mayhen_print_topbar_info">
            <a href="{{ route('guests.index', $invitation->id) }}" class="mayhen_print_topbar_btn mayhen_print_topbar_btn_outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Quay Lại Quản Lý Khách</span>
            </a>
            <span>Danh sách: <strong>{{ count($guests) }}</strong> quan khách</span>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <!-- Bộ lọc trạng thái tham dự -->
            <div style="display: flex; align-items: center; gap: 6px; font-size: 13px;">
                <span>Trạng thái:</span>
                <select onchange="window.location.href = this.value" style="padding: 6px 10px; border-radius: 6px; border: 1px solid #D8CEBD; font-family: inherit; font-size: 13px; background: #FFFFFF; cursor: pointer;">
                    <option value="{{ route('guests.print', ['id' => $invitation->id, 'status' => 'all', 'group' => $filterGroup]) }}" {{ $filterStatus === 'all' ? 'selected' : '' }}>
                        Tất cả khách mời
                    </option>
                    <option value="{{ route('guests.print', ['id' => $invitation->id, 'status' => 'attending', 'group' => $filterGroup]) }}" {{ $filterStatus === 'attending' ? 'selected' : '' }}>
                        Chỉ khách tham dự ({{ $attendingGuestsCount }})
                    </option>
                </select>
            </div>

            <!-- Bộ lọc nhóm khách -->
            <div style="display: flex; align-items: center; gap: 6px; font-size: 13px;">
                <span>Nhóm:</span>
                <select onchange="window.location.href = this.value" style="padding: 6px 10px; border-radius: 6px; border: 1px solid #D8CEBD; font-family: inherit; font-size: 13px; background: #FFFFFF; cursor: pointer;">
                    <option value="{{ route('guests.print', ['id' => $invitation->id, 'status' => $filterStatus]) }}">-- Tất cả nhóm --</option>
                    @foreach($availableGroups as $grp)
                        <option value="{{ route('guests.print', ['id' => $invitation->id, 'status' => $filterStatus, 'group' => $grp]) }}" {{ $filterGroup === $grp ? 'selected' : '' }}>
                            {{ $grp }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="button" onclick="window.print()" class="mayhen_print_topbar_btn mayhen_print_topbar_btn_gold">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                <span>In Bảng Điểm Danh (A4)</span>
            </button>
        </div>
    </div>

    <!-- Khung Khổ Giấy In A4 -->
    <main class="mayhen_print_container">
        <!-- Header Danh Sách Đón Tiếp -->
        <header class="mayhen_print_header">
            <span class="mayhen_print_badge">Bàn Lễ Tân & Đón Tiếp Quan Khách</span>
            <h1 class="mayhen_print_title">BẢNG ĐIỂM DANH & ĐÓN TIẾP HÔN LỄ</h1>
            <div class="mayhen_print_couple">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</div>

            <div class="mayhen_print_meta">
                @if($wedding->wedding_date)
                    <div>Hôn lễ: <strong>{{ \Carbon\Carbon::parse($wedding->wedding_date)->format('d/m/Y') }}</strong></div>
                @endif
                @if(!empty($wedding->extra_data['event_party_address']))
                    <div>Sảnh tiệc: <strong>{{ $wedding->extra_data['event_party_address'] }}</strong></div>
                @endif
                <div>Ngày in: {{ now()->format('d/m/Y H:i') }}</div>
            </div>
        </header>

        <!-- Thống Kê Nhanh Suất Tiệc -->
        <section class="mayhen_print_stats_bar">
            <div class="mayhen_print_stat_item">
                <span class="mayhen_print_stat_val">{{ number_format($totalGuestsCount) }}</span>
                <span class="mayhen_print_stat_lbl">Tổng Khách Mời</span>
            </div>
            <div class="mayhen_print_stat_item">
                <span class="mayhen_print_stat_val" style="color: #2E7D32;">{{ number_format($attendingGuestsCount) }}</span>
                <span class="mayhen_print_stat_lbl">Xác Nhận Đi</span>
            </div>
            <div class="mayhen_print_stat_item">
                <span class="mayhen_print_stat_val">{{ number_format($totalAdults) }}</span>
                <span class="mayhen_print_stat_lbl">Người Lớn (RSVP)</span>
            </div>
            <div class="mayhen_print_stat_item">
                <span class="mayhen_print_stat_val">{{ number_format($totalChildren) }}</span>
                <span class="mayhen_print_stat_lbl">Trẻ Em (RSVP)</span>
            </div>
            <div class="mayhen_print_stat_item">
                <span class="mayhen_print_stat_val" style="color: #E65100;">{{ number_format($totalVegetarian) }}</span>
                <span class="mayhen_print_stat_lbl">Ăn Chay / ĐB</span>
            </div>
        </section>

        <!-- Bảng Danh Sách Quan Khách -->
        <table class="mayhen_print_table">
            <thead>
                <tr>
                    <th style="width: 38px; text-align: center;">STT</th>
                    <th>Tên Khách Mời & Xưng Hô</th>
                    <th style="width: 110px;">Nhóm Khách</th>
                    <th style="width: 95px;">Điện Thoại</th>
                    <th style="width: 100px; text-align: center;">RSVP</th>
                    <th style="width: 75px; text-align: center;">Suất Đi</th>
                    <th>Ghi Chú / Ăn Chay</th>
                    <th class="mayhen_print_checkbox_cell">Đến [✓]</th>
                    <th class="mayhen_print_gift_cell">Mừng Cưới / Quà</th>
                </tr>
            </thead>
            <tbody>
                @forelse($guests as $index => $g)
                    @php
                        $resp = $g->response;
                    @endphp
                    <tr>
                        <td style="text-align: center; color: #7A726A;">{{ $index + 1 }}</td>
                        <td>
                            <strong style="font-size: 13px;">{{ $g->name }}</strong>
                            @if($g->salutation)
                                <span style="font-size: 11px; color: #7A726A;">({{ $g->salutation }})</span>
                            @endif
                        </td>
                        <td>{{ $g->group_name ?? '—' }}</td>
                        <td>{{ $g->phone ?? '—' }}</td>
                        <td style="text-align: center;">
                            @if($resp)
                                @if($resp->attending_status === 'attending')
                                    <span class="mayhen_print_status_tag mayhen_print_status_attending">Tham dự</span>
                                @else
                                    <span class="mayhen_print_status_tag mayhen_print_status_declined">Bận / Không</span>
                                @endif
                            @else
                                <span class="mayhen_print_status_tag mayhen_print_status_pending">Chưa rõ</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($resp && $resp->attending_status === 'attending')
                                <strong>{{ $resp->adults_count }} lớn</strong>
                                @if($resp->children_count > 0)
                                    <br><span style="font-size: 10px; color: #7A726A;">+{{ $resp->children_count }} trẻ</span>
                                @endif
                            @else
                                {{ $g->expected_attendees }} suất
                            @endif
                        </td>
                        <td style="font-size: 11px; color: #5A534B;">
                            @if($resp && $resp->dietary_requirements)
                                <span style="color: #E65100; font-weight: 600;">Ăn chay:</span> {{ $resp->dietary_requirements }}
                            @elseif($resp && $resp->wishes)
                                {{ Str::limit($resp->wishes, 45) }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="mayhen_print_checkbox_cell">
                            <span class="mayhen_print_check_box"></span>
                        </td>
                        <td class="mayhen_print_gift_cell">
                            <span class="mayhen_print_gift_line"></span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 36px; color: #8C827A;">
                            Chưa có khách mời nào trong danh sách.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Chân Trang Ký Xác Nhận Tiếp Tân -->
        <footer class="mayhen_print_reception_footer">
            <div style="font-size: 12px; color: #7A726A; line-height: 1.6;">
                <div>* Ghi chú cho ban lễ tân:</div>
                <div>1. Đánh dấu tích [✓] vào ô "Đến" khi quan khách bước vào sảnh cưới.</div>
                <div>2. Ghi rõ số phong bì hoặc ký hiệu quà mừng vào cột "Mừng Cưới / Quà" để đối soát sau tiệc.</div>
            </div>

            <div class="mayhen_print_sign_col">
                <div class="mayhen_print_sign_col_title">Trưởng Ban Lễ Tân / Đại Diện</div>
                <div class="mayhen_print_sign_col_line">(Ký và ghi rõ họ tên)</div>
            </div>
        </footer>
    </main>
</body>
</html>

