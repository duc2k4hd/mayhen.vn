@extends('client.pages.account.layout')

@section('account_title', 'Gói Dự Phòng Mất Mạng Ngày Cưới | Mây Hẹn')

@section('account_content')
<div class="mayhen_offline_container">
    {{-- Header Ribbon --}}
    <div class="mayhen_offline_header">
        <div>
            <div class="mayhen_offline_kicker">EMERGENCY RESCUE PACK • IDEA-C07 &amp; TASK-VAL-11</div>
            <h1 class="mayhen_offline_title">Gói Dự Phòng Mất Mạng Ngày Cưới</h1>
            <p class="mayhen_offline_desc">
                Cứu cánh thực tế khi trung tâm tiệc cưới ở tầng hầm hoặc đông người làm nghẽn sóng 4G/WiFi. Tải sẵn 1 file HTML duy nhất chứa toàn bộ danh sách khách và số bàn tiệc để tra cứu &amp; điểm danh ngoại tuyến 100%.
            </p>
        </div>
        <div class="mayhen_offline_actions">
            <a href="{{ route('account.invitations') }}" class="mayhen_offline_btn mayhen_offline_btn--outline">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                <span>Quay Lại Danh Sách</span>
            </a>
            <a href="{{ route('account.offline_pack.download_html', $invitation->id) }}" class="mayhen_offline_btn mayhen_offline_btn--gold">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span>Tải File HTML Ngoại Tuyến</span>
            </a>
        </div>
    </div>

    {{-- 4 Card KPI Thông Số Gói Dữ Liệu --}}
    <div class="mayhen_offline_kpi_grid">
        <div class="mayhen_offline_kpi_card">
            <div class="mayhen_offline_kpi_num">{{ $snapshot['kpis']['total_guests'] }}</div>
            <div class="mayhen_offline_kpi_label">Khách Mời Đã Đóng Gói</div>
        </div>
        <div class="mayhen_offline_kpi_card">
            <div class="mayhen_offline_kpi_num">{{ $snapshot['kpis']['total_tables'] }}</div>
            <div class="mayhen_offline_kpi_label">Bàn Tiệc Đã Xếp Chỗ</div>
        </div>
        <div class="mayhen_offline_kpi_card">
            <div class="mayhen_offline_kpi_num" style="color: #2E7D32;">&lt; 150 KB</div>
            <div class="mayhen_offline_kpi_label">Dung Lượng Gói Cực Nhẹ</div>
        </div>
        <div class="mayhen_offline_kpi_card">
            <div class="mayhen_offline_kpi_num" style="font-size: 1rem; color: #8C6D3F;">{{ $snapshot['generated_at_human'] }}</div>
            <div class="mayhen_offline_kpi_label">Tạo Bản Snapshot</div>
        </div>
    </div>

    {{-- Hướng Dẫn Sử Dụng Khẩn Cấp 3 Bước --}}
    <div class="mayhen_offline_guide_card">
        <div class="mayhen_offline_guide_head">
            <span class="mayhen_offline_guide_icon">🛡️</span>
            <div>
                <h3 class="mayhen_offline_guide_title">Kế Hoạch Dự Phòng Ngoại Tuyến 3 Bước Cho Đội Đón Khách</h3>
                <p class="mayhen_offline_guide_subtitle">Đảm bảo việc đón tiếp quan khách diễn ra trôi chảy, không một giây chậm trễ dù mất sạch kết nối Internet.</p>
            </div>
        </div>

        <div class="mayhen_offline_steps_grid">
            <div class="mayhen_offline_step">
                <div class="mayhen_offline_step_num">1</div>
                <div class="mayhen_offline_step_body">
                    <strong>Tải file về máy trước khi đến tiệc</strong>
                    <p>Bấm nút tải file HTML về điện thoại hoặc gửi qua Zalo / AirDrop cho người thân, bạn bè phụ trách bàn lễ tân đón khách.</p>
                </div>
            </div>

            <div class="mayhen_offline_step">
                <div class="mayhen_offline_step_num">2</div>
                <div class="mayhen_offline_step_body">
                    <strong>Mở trực tiếp trên trình duyệt điện thoại</strong>
                    <p>File HTML tự chạy 100% trong Safari / Chrome mà không cần mạng. Gõ tên hoặc 4 số cuối điện thoại để tra cứu số bàn ngay tức thì.</p>
                </div>
            </div>

            <div class="mayhen_offline_step">
                <div class="mayhen_offline_step_num">3</div>
                <div class="mayhen_offline_step_body">
                    <strong>Ghi nhận đón khách &amp; Xuất báo cáo</strong>
                    <p>Bấm nút "Đón Khách" để đánh dấu. Sau tiệc, bấm "Xuất Báo Cáo CSV" ngay trên màn hình để đối soát chính xác sĩ số tham dự.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Khung Tải Về & Tùy Chọn --}}
    <div class="mayhen_offline_download_box">
        <div class="mayhen_offline_download_info">
            <h4>Tải Tệp HTML Độc Lập Chạy Mọi Thiết Bị</h4>
            <p>Tệp bao gồm toàn bộ mã nguồn CSS, giao diện và cơ sở dữ liệu khách mời của thiệp cưới <strong>{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}</strong>.</p>
        </div>
        <div class="mayhen_offline_download_btns">
            <a href="{{ route('account.offline_pack.download_html', $invitation->id) }}" class="mayhen_offline_btn mayhen_offline_btn--gold">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span>Tải File .HTML (Khuyến Nghị)</span>
            </a>
            <a href="{{ route('account.offline_pack.download_json', $invitation->id) }}" class="mayhen_offline_btn mayhen_offline_btn--outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="8 17 12 21 16 17"></polyline><line x1="12" y1="12" x2="12" y2="21"></line><path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"></path></svg>
                <span>Tải Dữ Liệu .JSON</span>
            </a>
        </div>
    </div>

    {{-- Bản Xem Trước Danh Sách Khách Trong Gói --}}
    <div class="mayhen_offline_table_card">
        <div class="mayhen_offline_table_head">
            <h3 class="mayhen_offline_table_title">Bản Xem Trước Dữ Liệu Khách Mời Sẵn Sàng Ngoại Tuyến</h3>
            <span class="mayhen_offline_table_badge">{{ count($snapshot['guests']) }} khách đã sẵn sàng</span>
        </div>

        <div class="mayhen_offline_table_wrap">
            <table class="mayhen_offline_table">
                <thead>
                    <tr>
                        <th>Khách Mời</th>
                        <th>Xưng Hô</th>
                        <th>Phân Nhóm</th>
                        <th>Bàn Tiệc</th>
                        <th>Số Suất</th>
                        <th>Trạng Thái RSVP</th>
                        <th>Check-in</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(array_slice($snapshot['guests'], 0, 15) as $guest)
                        <tr>
                            <td><strong>{{ $guest['name'] }}</strong></td>
                            <td>{{ $guest['salutation'] }}</td>
                            <td><span class="mayhen_offline_group_pill">{{ $guest['group_name'] }}</span></td>
                            <td><strong>🍽️ {{ $guest['table_name'] }}</strong></td>
                            <td>{{ $guest['actual_attendees'] }}</td>
                            <td>
                                @if($guest['is_attending'] === true)
                                    <span class="mayhen_status_attending">✓ Tham dự</span>
                                @elseif($guest['is_attending'] === false)
                                    <span class="mayhen_status_declined">✗ Từ chối</span>
                                @else
                                    <span class="mayhen_status_pending">Chờ phản hồi</span>
                                @endif
                            </td>
                            <td>
                                @if($guest['is_checked_in'])
                                    <span class="mayhen_status_checked">✓ Đã đón ({{ $guest['checked_in_at'] }})</span>
                                @else
                                    <span class="text-muted">Chưa đón</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #888;">
                                Chưa có khách mời nào trong danh sách. Hãy thêm khách trước khi tải gói ngoại tuyến.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(count($snapshot['guests']) > 15)
            <div class="mayhen_offline_table_more">
                <span>... và {{ count($snapshot['guests']) - 15 }} khách mời khác đã được nạp đầy đủ vào gói tải về.</span>
            </div>
        @endif
    </div>
</div>

<style>
/* Pure CSS BEM cho Phân Hệ Studio Gói Ngoại Tuyến (IDEA-C07) */
.mayhen_offline_container {
    padding: 1.5rem 0 3rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #1F1D1A;
}

.mayhen_offline_header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid rgba(197, 168, 128, 0.25);
}

.mayhen_offline_kicker {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: #9C7A4A;
    margin-bottom: 0.4rem;
}

.mayhen_offline_title {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 2rem;
    font-weight: 700;
    color: #1F1D1A;
    line-height: 1.2;
    margin-bottom: 0.5rem;
}

.mayhen_offline_desc {
    font-size: 0.95rem;
    color: #666;
    max-width: 680px;
    line-height: 1.5;
}

.mayhen_offline_actions {
    display: flex;
    gap: 0.75rem;
    flex-shrink: 0;
}

.mayhen_offline_btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.25rem;
    border-radius: 8px;
    font-size: 0.88rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.2s ease;
}

.mayhen_offline_btn--gold {
    background: linear-gradient(135deg, #C5A880 0%, #A8875A 100%);
    color: #FFF;
    border: none;
}

.mayhen_offline_btn--gold:hover {
    filter: brightness(1.08);
}

.mayhen_offline_btn--outline {
    background: #FFF;
    color: #444;
    border-color: #DCD6CD;
}

.mayhen_offline_btn--outline:hover {
    border-color: #C5A880;
    color: #1F1D1A;
}

/* KPI Cards */
.mayhen_offline_kpi_grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1.25rem;
    margin-bottom: 2rem;
}

@media (max-width: 900px) {
    .mayhen_offline_kpi_grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.mayhen_offline_kpi_card {
    background: #FFFFFF;
    border: 1px solid rgba(197, 168, 128, 0.3);
    border-radius: 12px;
    padding: 1.25rem;
    text-align: center;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
}

.mayhen_offline_kpi_num {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 2.2rem;
    font-weight: 700;
    color: #1F1D1A;
    line-height: 1.1;
    margin-bottom: 0.35rem;
}

.mayhen_offline_kpi_label {
    font-size: 0.8rem;
    color: #777;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

/* Guide Card */
.mayhen_offline_guide_card {
    background: #FFFDF9;
    border: 1px solid rgba(197, 168, 128, 0.45);
    border-radius: 12px;
    padding: 1.75rem;
    margin-bottom: 2rem;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.03);
}

.mayhen_offline_guide_head {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px dashed rgba(197, 168, 128, 0.35);
}

.mayhen_offline_guide_icon {
    font-size: 2rem;
}

.mayhen_offline_guide_title {
    font-family: "Cormorant Garamond", Georgia, serif;
    font-size: 1.35rem;
    font-weight: 700;
    color: #1F1D1A;
    margin: 0 0 0.2rem;
}

.mayhen_offline_guide_subtitle {
    font-size: 0.88rem;
    color: #666;
    margin: 0;
}

.mayhen_offline_steps_grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .mayhen_offline_steps_grid {
        grid-template-columns: 1fr;
    }
}

.mayhen_offline_step {
    display: flex;
    gap: 0.85rem;
}

.mayhen_offline_step_num {
    width: 32px;
    height: 32px;
    background: #C5A880;
    color: #FFF;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.95rem;
    flex-shrink: 0;
}

.mayhen_offline_step_body strong {
    display: block;
    font-size: 0.92rem;
    color: #1F1D1A;
    margin-bottom: 0.3rem;
}

.mayhen_offline_step_body p {
    font-size: 0.82rem;
    color: #666;
    line-height: 1.45;
    margin: 0;
}

/* Download Box */
.mayhen_offline_download_box {
    background: #FFFFFF;
    border: 1px solid rgba(197, 168, 128, 0.35);
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .mayhen_offline_download_box {
        flex-direction: column;
        align-items: flex-start;
    }
}

.mayhen_offline_download_info h4 {
    font-size: 1.05rem;
    font-weight: 600;
    color: #1F1D1A;
    margin: 0 0 0.25rem;
}

.mayhen_offline_download_info p {
    font-size: 0.85rem;
    color: #666;
    margin: 0;
}

.mayhen_offline_download_btns {
    display: flex;
    gap: 0.75rem;
    flex-shrink: 0;
}

/* Table Card */
.mayhen_offline_table_card {
    background: #FFFFFF;
    border: 1px solid rgba(197, 168, 128, 0.3);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.02);
}

.mayhen_offline_table_head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #EFEAE2;
}

.mayhen_offline_table_title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0;
}

.mayhen_offline_table_badge {
    background: #FAF8F5;
    border: 1px solid #E5DFD7;
    color: #8C6D3F;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 4px;
}

.mayhen_offline_table_wrap {
    overflow-x: auto;
}

.mayhen_offline_table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.88rem;
    text-align: left;
}

.mayhen_offline_table th {
    background: #FAF8F5;
    color: #555;
    font-weight: 600;
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid #EFEAE2;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.mayhen_offline_table td {
    padding: 0.85rem 1.25rem;
    border-bottom: 1px solid #F5F1EB;
    color: #333;
}

.mayhen_offline_group_pill {
    display: inline-block;
    font-size: 0.75rem;
    background: #F4F1EA;
    color: #6B5B45;
    padding: 2px 8px;
    border-radius: 4px;
}

.mayhen_status_attending { color: #2E7D32; font-weight: 600; font-size: 0.8rem; }
.mayhen_status_declined { color: #C62828; font-weight: 600; font-size: 0.8rem; }
.mayhen_status_pending { color: #888; font-size: 0.8rem; }
.mayhen_status_checked { color: #1B5E20; font-weight: 600; font-size: 0.8rem; }

.mayhen_offline_table_more {
    padding: 1rem 1.5rem;
    text-align: center;
    background: #FAF8F5;
    font-size: 0.82rem;
    color: #777;
    border-top: 1px dashed #E5DFD7;
}
</style>
@endsection

