@extends('client.pages.account.layout')

@section('page_title', 'Thông Báo Dời Lịch Cưới & Tái Xác Nhận RSVP | ' . config('app.name'))
@section('account_breadcrumb', 'Thông Báo Dời Lịch Cưới')

@section('account_content')
<div style="max-width: 1100px; margin: 0 auto;">
    {{-- Header --}}
    <div style="margin-bottom: 24px;">
        <div style="margin-bottom: 8px;">
            <a href="{{ route('account.invitations') }}" style="color: #8C827A; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                &larr; <span>Quay lại danh sách thiệp</span>
            </a>
        </div>
        <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #1F1D1A; margin: 0;">
            Trung Tâm Thông Báo Dời Lịch Cưới & Tái Xác Nhận RSVP
        </h2>
        <p style="font-size: 14px; color: #8C827A; margin: 4px 0 0 0;">
            Đám cưới: <strong style="color: #1F1D1A;">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</strong> • 
            Lịch hiện tại: <strong style="color: #9C7A4A;">{{ $data['current_date'] ? $data['current_date']->format('d/m/Y') : 'Chưa định ngày' }}</strong> 
            @if($data['current_venue']) • Sảnh tiệc: <strong style="color: #1F1D1A;">{{ $data['current_venue'] }}</strong>@endif
        </p>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div style="background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; padding: 14px 18px; border-radius: 10px; margin-bottom: 24px; font-size: 14px; display: flex; align-items: flex-start; gap: 10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0; margin-top:1px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if($errors->any())
        <div style="background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; padding: 14px 18px; border-radius: 10px; margin-bottom: 24px; font-size: 14px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Cảnh Báo Tác Động Khi Dời Ngày (Impact Assessment) --}}
    <div style="background: #FFFDF9; border: 1px solid #EADBCE; border-radius: 14px; padding: 22px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
            <span style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 50%; background: #FEF3C7; color: #D97706;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            </span>
            <h3 style="font-size: 16px; font-weight: 700; color: #1F1D1A; margin: 0;">
                Đánh Giá Tác Động Khi Điều Chỉnh Lịch Hôn Lễ
            </h3>
        </div>
        <p style="font-size: 13px; color: #7A726A; margin: 0 0 16px 0; line-height: 1.5;">
            Khi bạn thay đổi ngày cưới hoặc địa điểm, đường link thiệp cưới cũ vẫn được giữ nguyên hoạt động bình thường, toàn bộ lịch đếm ngược và thông tin trong thiệp sẽ tự động đồng bộ sang ngày mới. Tuy nhiên, bạn cần gửi tin nhắn thông báo điều chỉnh tới những quan khách đã từng nhận thiệp.
        </p>

        {{-- KPI Grid Tác Động --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
            <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 10px; padding: 14px;">
                <div style="font-size: 11px; text-transform: uppercase; color: #8C827A;">Tổng Khách Mời</div>
                <div style="font-size: 22px; font-weight: 700; color: #1F1D1A; font-family: 'Cormorant Garamond', serif;">
                    {{ $data['total_guests'] }} khách
                </div>
            </div>
            <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 10px; padding: 14px;">
                <div style="font-size: 11px; text-transform: uppercase; color: #8C827A;">Đã Gửi Thiệp Mời</div>
                <div style="font-size: 22px; font-weight: 700; color: #9C7A4A; font-family: 'Cormorant Garamond', serif;">
                    {{ $data['sent_guests'] }} thiệp
                </div>
            </div>
            <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 10px; padding: 14px;">
                <div style="font-size: 11px; text-transform: uppercase; color: #8C827A;">Đã Báo Tham Dự</div>
                <div style="font-size: 22px; font-weight: 700; color: #047857; font-family: 'Cormorant Garamond', serif;">
                    {{ $data['attending_count'] }} người
                </div>
            </div>
            <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 10px; padding: 14px;">
                <div style="font-size: 11px; text-transform: uppercase; color: #8C827A;">Cần Xác Nhận Lại</div>
                <div style="font-size: 22px; font-weight: 700; color: #B91C1C; font-family: 'Cormorant Garamond', serif;">
                    {{ $data['needs_reconfirm_count'] }} khách
                </div>
            </div>
        </div>
    </div>

    {{-- Form Thực Hiện Điều Chỉnh Lịch Hôn Lễ --}}
    <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 14px; padding: 24px; margin-bottom: 28px;">
        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #1F1D1A; margin: 0 0 16px 0;">
            1. Thiết Lập Ngày Cưới & Sảnh Tiệc Mới
        </h3>

        <form action="{{ route('account.reschedule.submit', $invitation->id) }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                        Ngày tổ chức hôn lễ mới *
                    </label>
                    <input type="date" name="new_date" required min="{{ date('Y-m-d') }}" 
                           value="{{ old('new_date', $data['current_date'] ? $data['current_date']->format('Y-m-d') : '') }}" 
                           style="width: 100%; padding: 12px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 14px; box-sizing: border-box; font-weight: 600; color: #9C7A4A;">
                    <span style="font-size: 12px; color: #8C827A; display: block; margin-top: 4px;">
                        Ngày cũ: {{ $data['current_date'] ? $data['current_date']->format('d/m/Y') : 'Chưa có' }}
                    </span>
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                        Lý do điều chỉnh lịch trình (tùy chọn)
                    </label>
                    <input type="text" name="reason" placeholder="Ví dụ: Chọn lại ngày hoàng đạo đẹp hơn, Dời sảnh tiệc..." 
                           value="{{ old('reason') }}"
                           style="width: 100%; padding: 12px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                        Tên sảnh tiệc / Trung tâm tiệc cưới mới (nếu có đổi)
                    </label>
                    <input type="text" name="new_venue" placeholder="Để trống nếu giữ nguyên sảnh cũ" 
                           value="{{ old('new_venue', $data['current_venue']) }}"
                           style="width: 100%; padding: 12px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                </div>

                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                        Địa chỉ sảnh tiệc mới
                    </label>
                    <input type="text" name="new_venue_address" placeholder="Để trống nếu giữ nguyên địa chỉ cũ" 
                           value="{{ old('new_venue_address', $data['current_address']) }}"
                           style="width: 100%; padding: 12px 14px; border: 1px solid #D8CEBD; border-radius: 8px; font-size: 14px; box-sizing: border-box;">
                </div>
            </div>

            {{-- Tùy chọn xử lý RSVP --}}
            <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 10px; padding: 16px; margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 700; color: #1F1D1A; margin-bottom: 10px;">
                    Phương thức xử lý phản hồi tham dự (RSVP):
                </label>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                        <input type="radio" name="rsvp_action" value="keep_existing" checked style="margin-top: 3px;">
                        <div>
                            <strong style="color: #1F1D1A; font-size: 13px;">Giữ nguyên trạng thái RSVP cũ</strong>
                            <div style="font-size: 12px; color: #7A726A;">Chỉ cập nhật lại ngày và giờ. Nếu khách bận không thể đến ngày mới, khách sẽ chủ động cập nhật lại thiệp.</div>
                        </div>
                    </label>

                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                        <input type="radio" name="rsvp_action" value="request_reconfirm" style="margin-top: 3px;">
                        <div>
                            <strong style="color: #B91C1C; font-size: 13px;">Yêu cầu toàn bộ quan khách xác nhận lại (Re-confirm RSVP)</strong>
                            <div style="font-size: 12px; color: #7A726A;">Đánh dấu toàn bộ khách đã RSVP là cần xác nhận lại. Thiệp cưới sẽ hiển thị thông báo mời khách gửi lại phản hồi cho ngày cưới mới.</div>
                        </div>
                    </label>
                </div>
            </div>

            <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary" onclick="return confirm('Bạn có chắc chắn muốn dời ngày cưới sang ngày mới?');">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                <span>Cập Nhật Ngày Cưới Mới & Lưu Lịch Trình</span>
            </button>
        </form>
    </div>

    {{-- Lịch Sử Các Lần Điều Chỉnh Lịch Hôn Lễ --}}
    @if($data['history']->isNotEmpty())
        <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 14px; padding: 24px; margin-bottom: 28px;">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #1F1D1A; margin: 0 0 16px 0;">
                Nhật Ký Các Đợt Điều Chỉnh Ngày Cưới
            </h3>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: #FAF8F5; border-bottom: 1px solid #EAE3D9; text-align: left;">
                            <th style="padding: 10px 14px; color: #7A726A;">Thời Điểm Thay Đổi</th>
                            <th style="padding: 10px 14px; color: #7A726A;">Ngày Cũ &rarr; Ngày Mới</th>
                            <th style="padding: 10px 14px; color: #7A726A;">Lý Do</th>
                            <th style="padding: 10px 14px; color: #7A726A;">Xử Lý RSVP</th>
                            <th style="padding: 10px 14px; color: #7A726A;">Số Khách Ảnh Hưởng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data['history'] as $h)
                            <tr style="border-bottom: 1px solid #F4EFEA;">
                                <td style="padding: 12px 14px; color: #8C827A;">{{ $h->created_at->format('H:i d/m/Y') }}</td>
                                <td style="padding: 12px 14px;">
                                    <span style="color: #A0988E; text-decoration: line-through;">{{ $h->old_date ? $h->old_date->format('d/m/Y') : 'Chưa có' }}</span>
                                    &rarr;
                                    <strong style="color: #9C7A4A;">{{ $h->new_date->format('d/m/Y') }}</strong>
                                </td>
                                <td style="padding: 12px 14px; color: #1F1D1A;">{{ $h->reason ?: 'Không ghi chú' }}</td>
                                <td style="padding: 12px 14px;">
                                    <span style="display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; background: {{ $h->rsvp_action === 'request_reconfirm' ? '#FEE2E2' : '#EFF6FF' }}; color: {{ $h->rsvp_action === 'request_reconfirm' ? '#B91C1C' : '#1D4ED8' }};">
                                        {{ $h->rsvp_action_label }}
                                    </span>
                                </td>
                                <td style="padding: 12px 14px; color: #1F1D1A; font-weight: 600;">{{ $h->affected_guests_count }} khách</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Danh Sách Quan Khách & Công Cụ Gửi Thông Báo Zalo --}}
    <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 14px; padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
            <div>
                <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #1F1D1A; margin: 0 0 4px 0;">
                    2. Danh Sách Quan Khách & Tin Nhắn Zalo Thông Báo
                </h3>
                <p style="font-size: 13px; color: #7A726A; margin: 0;">
                    Bấm nút <strong>"Chép Tin Zalo"</strong> để tự động tạo lời mời điều chỉnh lịch trình trang trọng cho từng người bạn/người thân.
                </p>
            </div>
            <span style="font-size: 13px; color: #7A726A;">
                Tổng số: <strong>{{ $guests->count() }}</strong> khách
            </span>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                <thead>
                    <tr style="background: #FAF8F5; border-bottom: 1px solid #EAE3D9;">
                        <th style="padding: 12px 14px; color: #7A726A;">Khách Mời</th>
                        <th style="padding: 12px 14px; color: #7A726A;">Xưng Hô</th>
                        <th style="padding: 12px 14px; color: #7A726A;">Nhóm</th>
                        <th style="padding: 12px 14px; color: #7A726A;">Phản Hồi RSVP</th>
                        <th style="padding: 12px 14px; color: #7A726A;">Trạng Thái Thiệp</th>
                        <th style="padding: 12px 14px; text-align: right; color: #7A726A;">Công Cụ Thông Báo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guests as $g)
                        <tr style="border-bottom: 1px solid #F4EFEA;">
                            <td style="padding: 12px 14px;">
                                <strong style="color: #1F1D1A;">{{ $g->name }}</strong>
                                @if($g->phone)
                                    <div style="font-size: 11px; color: #8C827A;">{{ $g->phone }}</div>
                                @endif
                            </td>
                            <td style="padding: 12px 14px; color: #7A726A;">{{ $g->salutation }}</td>
                            <td style="padding: 12px 14px;">
                                <span style="background: #FAF8F5; border: 1px solid #EAE3D9; padding: 2px 8px; border-radius: 4px; font-size: 11px; color: #7A726A;">
                                    {{ $g->group_name }}
                                </span>
                            </td>
                            <td style="padding: 12px 14px;">
                                @if($g->response)
                                    @if($g->response->needs_reconfirmation)
                                        <span style="background: #FEF3C7; color: #D97706; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                            ⚠️ Cần xác nhận lại
                                        </span>
                                    @elseif($g->response->attending_status === 'attending')
                                        <span style="background: #DEF7EC; color: #03543F; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                            Tham gia ({{ $g->response->adults_count + $g->response->children_count }} người)
                                        </span>
                                    @else
                                        <span style="background: #FDE8E8; color: #9B1C1C; padding: 2px 8px; border-radius: 4px; font-size: 11px;">
                                            Từ chối
                                        </span>
                                    @endif
                                @else
                                    <span style="color: #A0988E; font-size: 12px;">Chưa phản hồi</span>
                                @endif
                            </td>
                            <td style="padding: 12px 14px;">
                                @if($g->is_sent)
                                    <span style="color: #047857; font-size: 11px; font-weight: 600;">✓ Đã gửi</span>
                                @else
                                    <span style="color: #A0988E; font-size: 11px;">○ Chưa gửi</span>
                                @endif
                                @if($g->open_count > 0)
                                    <div style="font-size: 10px; color: #1D4ED8;">Đã xem ({{ $g->open_count }} lần)</div>
                                @endif
                            </td>
                            <td style="padding: 12px 14px; text-align: right; white-space: nowrap;">
                                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm copy-reschedule-btn"
                                        data-msg-url="{{ route('account.reschedule.message', ['id' => $invitation->id, 'guestId' => $g->id]) }}"
                                        title="Chép tin nhắn thông báo dời ngày gửi qua Zalo">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                    <span>Chép Tin Zalo</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding: 32px; text-align: center; color: #8C827A;">
                                Chưa có khách mời nào trong danh sách.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Script Pure Vanilla Sao Chép Tin Nhắn --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.copy-reschedule-btn').forEach(btn => {
        btn.addEventListener('click', async () => {
            const url = btn.getAttribute('data-msg-url');
            if (!url) return;

            const originalText = btn.innerHTML;
            btn.innerHTML = '<span>Đang lấy tin...</span>';
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success && data.message) {
                    if (navigator.clipboard) {
                        await navigator.clipboard.writeText(data.message);
                        btn.innerHTML = '<span style="color: #047857; font-weight: 700;">✓ Đã chép tin!</span>';
                        setTimeout(() => {
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        }, 2500);
                    } else {
                        prompt('Sao chép tin nhắn dưới đây để gửi qua Zalo:', data.message);
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                }
            } catch (err) {
                console.error('Lỗi lấy mẫu tin:', err);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    });
});
</script>
@endsection

