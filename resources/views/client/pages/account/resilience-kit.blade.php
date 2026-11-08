@extends('client.layouts.master')

@section('title', 'Bộ Dự Phòng Sự Cố Ngày Cưới | ' . config('app.name'))

@push('styles')
<style>
/* Pure CSS cho Bộ Dự Phòng Sự Cố Ngày Cưới (Resilience Kit) */
.mayhen_resilience_container {
    max-width: 1000px;
    margin: 40px auto;
    padding: 0 20px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #1F1D1A;
}

.mayhen_resilience_header_card {
    background: #FAF8F5;
    border: 1px solid #E5DCCD;
    border-radius: 12px;
    padding: 24px 32px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.mayhen_resilience_title {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 24px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 6px 0;
}

.mayhen_resilience_subtitle {
    font-size: 14px;
    color: #6B6357;
    margin: 0;
}

.mayhen_resilience_print_btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    background: #1F1D1A;
    color: #FAF8F5;
    border: 1px solid #C5A880;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
}

.mayhen_resilience_print_btn:hover {
    background: #38342E;
}

.mayhen_resilience_grid_info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}

.mayhen_resilience_info_card {
    background: #FFFFFF;
    border: 1px solid #EFE8DD;
    border-radius: 8px;
    padding: 16px 20px;
}

.mayhen_resilience_info_card .label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #8C8275;
    margin-bottom: 4px;
}

.mayhen_resilience_info_card .val {
    font-size: 16px;
    font-weight: 600;
    color: #1F1D1A;
}

.mayhen_resilience_section {
    background: #FFFFFF;
    border: 1px solid #EFE8DD;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 30px;
}

.mayhen_resilience_sec_title {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 16px 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #EFE8DD;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.mayhen_resilience_table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}

.mayhen_resilience_table th,
.mayhen_resilience_table td {
    padding: 10px 14px;
    border-bottom: 1px solid #F0EBE1;
}

.mayhen_resilience_table th {
    background: #FAF8F5;
    font-weight: 600;
    color: #4A453E;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.mayhen_resilience_table tr:last-child td {
    border-bottom: none;
}

.mayhen_resilience_check_box {
    width: 18px;
    height: 18px;
    border: 1.5px solid #8C8275;
    border-radius: 4px;
    display: inline-block;
}

/* CSS khi In Ấn (@media print) */
@media print {
    body {
        background: #FFFFFF !important;
        color: #000000 !important;
    }

    nav, header, footer, .mayhen_client_header_wrapper, .mayhen_client_bottom_nav_wrapper, .mayhen_resilience_print_btn {
        display: none !important;
    }

    .mayhen_resilience_container {
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .mayhen_resilience_header_card,
    .mayhen_resilience_section,
    .mayhen_resilience_info_card {
        border: 1px solid #333333 !important;
        box-shadow: none !important;
        background: #FFFFFF !important;
    }

    .mayhen_resilience_section {
        page-break-inside: auto;
    }

    .mayhen_resilience_table tr {
        page-break-inside: avoid;
    }
}

/* Masking PII cho Số điện thoại */
.mayhen_phone_full { display: inline; }
.mayhen_phone_masked { display: none; }
.mayhen_mask_phone_active .mayhen_phone_full { display: none; }
.mayhen_mask_phone_active .mayhen_phone_masked { display: inline; }

.mayhen_resilience_actions_bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 24px;
    background: #FFFFFF;
    border: 1px solid #EFE8DD;
    border-radius: 8px;
    padding: 12px 20px;
}

.mayhen_resilience_toggle_label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #4A453E;
    cursor: pointer;
    user-select: none;
}

.mayhen_resilience_runbook {
    background: #FFFDF9;
    border: 1px solid #E5DCCD;
    border-left: 4px solid #C5A880;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
}

.mayhen_resilience_runbook_title {
    font-size: 15px;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 10px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.mayhen_resilience_runbook_grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    font-size: 13px;
    color: #4A453E;
    line-height: 1.5;
}

.mayhen_resilience_runbook_step {
    background: #FAF8F5;
    border: 1px solid #EFE8DD;
    border-radius: 6px;
    padding: 12px 14px;
}

.mayhen_resilience_runbook_step strong {
    color: #9C7A4A;
    display: block;
    margin-bottom: 4px;
}
</style>
@endpush

@section('content')
<div class="mayhen_resilience_container mayhen_mask_phone_active" id="resilienceContainer">
    {{-- Header & Nút In --}}
    <div class="mayhen_resilience_header_card">
        <div>
            <h1 class="mayhen_resilience_title">Bộ Dự Phòng Sự Cố Ngày Cưới</h1>
            <p class="mayhen_resilience_subtitle">
                Đám cưới: <strong>{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</strong> &bull; Ngày: {{ $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : 'Chưa định ngày' }}
            </p>
        </div>
        <button type="button" class="mayhen_resilience_print_btn" onclick="window.print()">
            🖨️ In Tài Liệu Dự Phòng (A4)
        </button>
    </div>

    {{-- Thanh Công Cụ & Tùy Chọn Bảo Mật PII --}}
    <div class="mayhen_resilience_actions_bar">
        <label class="mayhen_resilience_toggle_label">
            <input type="checkbox" id="toggleMaskPhone" checked>
            <span>🔒 Bảo vệ quyền riêng tư: <strong>Ẩn 4 số giữa của SĐT</strong> khi in ấn & xuất bản (Mask PII)</span>
        </label>
        <span style="font-size: 12px; color: #8C8275;">Định dạng chuẩn theo Nghị định 13/2023 & Luật 91/2025/QH15</span>
    </div>

    {{-- Kế Hoạch Ứng Phó Sự Cố Khẩn Cấp (Emergency Runbook) --}}
    <div class="mayhen_resilience_runbook">
        <div class="mayhen_resilience_runbook_title">
            <span>🚨 Kế Hoạch Vận Hành Khi Sự Cố Mạng / Mất Điện Tại Tiệc Cưới</span>
        </div>
        <div class="mayhen_resilience_runbook_grid">
            <div class="mayhen_resilience_runbook_step">
                <strong>Bước 1: Tiếp đón bằng bản in</strong>
                Sử dụng bản in A4 này để đối soát tên khách, nhóm và số bàn tiệc ngay tại bàn đón tiếp.
            </div>
            <div class="mayhen_resilience_runbook_step">
                <strong>Bước 2: Phát 4G dự phòng</strong>
                Bật điểm phát sóng Wi-Fi từ điện thoại 4G cho máy tính bảng/điện thoại của bàn lễ tân.
            </div>
            <div class="mayhen_resilience_runbook_step">
                <strong>Bước 3: Sử dụng Mã PIN Tiếp Tân</strong>
                Nhập mã PIN dự phòng <code>{{ $checkinPin }}</code> để đăng nhập nhanh không cần tài khoản chủ tiệc.
            </div>
            <div class="mayhen_resilience_runbook_step">
                <strong>Bước 4: Trợ giúp kỹ thuật</strong>
                Nếu cần can thiệp khẩn cấp, liên hệ đội ngũ kỹ thuật Mây Hẹn qua hotline hỗ trợ ngày cưới.
            </div>
        </div>
    </div>

    {{-- Lưới Thông Số Cứu Hộ Khẩn Cấp --}}
    <div class="mayhen_resilience_grid_info">
        <div class="mayhen_resilience_info_card">
            <div class="label">Mã PIN Tiếp Tân</div>
            <div class="val" style="font-family: monospace; font-size: 20px; color: #9C7A4A;">{{ $checkinPin }}</div>
        </div>
        <div class="mayhen_resilience_info_card">
            <div class="label">Tổng Khách Mời</div>
            <div class="val">{{ $totalGuests }} Khách ({{ $totalExpectedAttendees }} Suất Tiệc)</div>
        </div>
        <div class="mayhen_resilience_info_card">
            <div class="label">Đã Xác Nhận RSVP</div>
            <div class="val">{{ $attendingGuests }} Khách Chắc Chắn Đi</div>
        </div>
        <div class="mayhen_resilience_info_card">
            <div class="label">Sảnh & Địa Điểm</div>
            <div class="val">{{ $wedding->venue_name ?: 'Chưa cập nhật' }}</div>
        </div>
    </div>

    {{-- Phần 1: Bảng Phân Bổ Bàn Tiệc (Seating Manifest) --}}
    <div class="mayhen_resilience_section">
        <div class="mayhen_resilience_sec_title">
            <span>1. Sơ Đồ & Sức Chứa Bàn Tiệc</span>
            <span style="font-size: 13px; font-weight: normal; color: #6B6357;">Tổng số bàn: {{ $tables->count() }} bàn</span>
        </div>
        <table class="mayhen_resilience_table">
            <thead>
                <tr>
                    <th style="width: 50px;">STT</th>
                    <th>Tên Bàn Tiệc</th>
                    <th>Khu Vực</th>
                    <th>Sức Chứa</th>
                    <th>Đã Xếp Ghế</th>
                    <th>Tình Trạng</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tables as $idx => $t)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td><strong>{{ $t->name }}</strong></td>
                        <td>{{ $t->zone ?: 'Khu chung' }}</td>
                        <td>{{ $t->capacity }} ghế</td>
                        <td>{{ $t->assigned_attendees_count }} người</td>
                        <td>
                            @if($t->isOverCapacity())
                                <span style="color: #C53030; font-weight: 600;">Quá tải ({{ $t->assigned_attendees_count }}/{{ $t->capacity }})</span>
                            @elseif($t->isFull())
                                <span style="color: #2F855A; font-weight: 600;">Đã đủ</span>
                            @else
                                <span>Còn trống {{ $t->remaining_seats }} ghế</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; color: #8C8275; padding: 20px;">Chưa khởi tạo bàn tiệc nào trong sơ đồ.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Phần 2: Danh Sách Khách Mời Điểm Danh Thủ Công (Paper Check-in Roster) --}}
    <div class="mayhen_resilience_section">
        <div class="mayhen_resilience_sec_title">
            <span>2. Danh Sách Khách Mời Tiếp Tân & Điểm Danh Tay</span>
            <span style="font-size: 13px; font-weight: normal; color: #6B6357;">(Dành cho tiếp tân đối soát khi mất mạng Wi-Fi)</span>
        </div>
        <table class="mayhen_resilience_table">
            <thead>
                <tr>
                    <th style="width: 40px; text-align: center;">Đã Đến</th>
                    <th style="width: 40px;">STT</th>
                    <th>Họ Tên Khách Mời</th>
                    <th>Nhóm</th>
                    <th>Bàn Tiệc</th>
                    <th>Số Suất</th>
                    <th>Trạng Thái RSVP</th>
                    <th>Số Điện Thoại</th>
                </tr>
            </thead>
            <tbody>
                @forelse($guests as $idx => $g)
                    <tr>
                        <td style="text-align: center;"><span class="mayhen_resilience_check_box"></span></td>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <strong>{{ $g->salutation ? $g->salutation . ' ' : '' }}{{ $g->name }}</strong>
                        </td>
                        <td>{{ $g->group_name ?: 'Khách chung' }}</td>
                        <td>
                            @if($g->table_name)
                                <span style="font-weight: 600; color: #9C7A4A;">{{ $g->table_name }}</span>
                            @else
                                <span style="color: #8C8275;">Chưa xếp</span>
                            @endif
                        </td>
                        <td>{{ $g->actualAttendeesCount() }}</td>
                        <td>
                            @if($g->response && $g->response->attending_status === 'attending')
                                <span style="color: #2F855A; font-weight: 500;">✓ Tham dự</span>
                            @elseif($g->response && $g->response->attending_status === 'declined')
                                <span style="color: #C53030;">✕ Báo bận</span>
                            @else
                                <span style="color: #8C8275;">? Chưa trả lời</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $rawPhone = trim((string) $g->phone);
                                $maskedPhone = '';
                                if ($rawPhone !== '') {
                                    $phoneDigits = preg_replace('/\D/', '', $rawPhone);
                                    $len = strlen($phoneDigits);
                                    if ($len >= 7) {
                                        $maskedPhone = substr($phoneDigits, 0, 3) . '****' . substr($phoneDigits, -3);
                                    } else {
                                        $maskedPhone = substr($phoneDigits, 0, 2) . '***';
                                    }
                                }
                            @endphp
                            @if($rawPhone !== '')
                                <span class="mayhen_phone_full">{{ $rawPhone }}</span>
                                <span class="mayhen_phone_masked">{{ $maskedPhone }}</span>
                            @else
                                <span style="color: #8C8275;">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #8C8275; padding: 20px;">Chưa có khách mời nào trong danh sách.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('toggleMaskPhone');
    const container = document.getElementById('resilienceContainer');
    if (toggle && container) {
        toggle.addEventListener('change', function () {
            if (this.checked) {
                container.classList.add('mayhen_mask_phone_active');
            } else {
                container.classList.remove('mayhen_mask_phone_active');
            }
        });
    }
});
</script>
@endpush
@endsection

