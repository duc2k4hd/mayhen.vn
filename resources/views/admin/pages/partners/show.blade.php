@extends('admin.layouts.master')

@section('title', 'Chi Tiết Đối Tác: ' . $partner->business_name)

@section('content')
<div class="mayhen_admin_page">
    {{-- Header --}}
    <div class="mayhen_admin_page_header">
        <div>
            <div class="mayhen_admin_breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Tổng quan</a>
                <span>/</span>
                <a href="{{ route('admin.partners.index') }}">Đối tác</a>
                <span>/</span>
                <span>{{ $partner->business_name }}</span>
            </div>
            <h1 class="mayhen_admin_page_title">{{ $partner->business_name }}</h1>
            <p class="mayhen_admin_page_subtitle">Mã đối tác: <strong style="font-family: monospace; color: #9C7A4A;">{{ $partner->partner_code }}</strong> • Tham gia từ: {{ $partner->created_at->format('d/m/Y') }}</p>
        </div>
        <div class="mayhen_admin_page_actions">
            <a href="{{ route('admin.partners.index') }}" class="mayhen_admin_btn mayhen_admin_btn--outline">
                &larr; Quay lại danh sách
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="mayhen_admin_alert mayhen_admin_alert--success">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mayhen_admin_alert mayhen_admin_alert--danger">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Overview Grid --}}
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
        {{-- Profile Card --}}
        <div class="mayhen_admin_card">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 20px; color: #1F1D1A; margin-bottom: 16px;">
                Thông Tin Doanh Nghiệp & Liên Hệ
            </h3>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 14px;">
                <div>
                    <span style="color: #8C827A; display: block; font-size: 12px;">Chủ tài khoản hệ thống:</span>
                    <strong style="color: #1F1D1A;">{{ $partner->user ? $partner->user->name : 'N/A' }}</strong>
                    <div style="font-size: 12px; color: #8C827A;">User ID: #{{ $partner->user_id }}</div>
                </div>

                <div>
                    <span style="color: #8C827A; display: block; font-size: 12px;">Trạng thái hoạt động:</span>
                    @if($partner->status === 'active')
                        <span class="mayhen_admin_badge mayhen_admin_badge--success">Đang Hoạt Động</span>
                    @elseif($partner->status === 'pending')
                        <span class="mayhen_admin_badge mayhen_admin_badge--warning">Chờ Phê Duyệt</span>
                    @else
                        <span class="mayhen_admin_badge mayhen_admin_badge--danger">Đã Đình Chỉ</span>
                    @endif
                </div>

                <div>
                    <span style="color: #8C827A; display: block; font-size: 12px;">Số điện thoại:</span>
                    <strong style="color: #1F1D1A;">{{ $partner->phone }}</strong>
                </div>

                <div>
                    <span style="color: #8C827A; display: block; font-size: 12px;">Email:</span>
                    <strong style="color: #1F1D1A;">{{ $partner->email }}</strong>
                </div>

                <div>
                    <span style="color: #8C827A; display: block; font-size: 12px;">Địa chỉ trụ sở / Studio:</span>
                    <span style="color: #1F1D1A;">{{ $partner->address ?? 'Chưa cập nhật' }}</span>
                </div>

                <div>
                    <span style="color: #8C827A; display: block; font-size: 12px;">Website / Portfolio:</span>
                    @if($partner->website)
                        <a href="{{ $partner->website }}" target="_blank" rel="noopener" style="color: #9C7A4A; text-decoration: underline;">
                            {{ $partner->website }}
                        </a>
                    @else
                        <span style="color: #8C827A;">Chưa cập nhật</span>
                    @endif
                </div>
            </div>

            @if($partner->status === 'suspended' && !empty($partner->custom_config['suspension_reason']))
                <div style="margin-top: 16px; padding: 12px 16px; background: #FDE8E8; border: 1px solid #F8B4B4; border-radius: 8px; font-size: 13px; color: #9B1C1C;">
                    <strong>Lý do đình chỉ:</strong> {{ $partner->custom_config['suspension_reason'] }}
                </div>
            @endif
        </div>

        {{-- Financial & Policy Management --}}
        <div class="mayhen_admin_card">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 20px; color: #1F1D1A; margin-bottom: 16px;">
                Chính Sách & Ví Hoa Hồng
            </h3>

            <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 12px; padding: 16px; margin-bottom: 20px; text-align: center;">
                <span style="color: #8C827A; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">Số dư ví hiện tại</span>
                <div style="font-size: 28px; font-weight: 700; color: #9C7A4A; margin-top: 4px;">
                    {{ number_format($partner->wallet_balance, 0, ',', '.') }} ₫
                </div>
            </div>

            {{-- Form Cập Nhật Chiết Khấu --}}
            <form method="POST" action="{{ route('admin.partners.update_rate', $partner->id) }}" style="margin-bottom: 20px;">
                @csrf
                @method('PUT')
                <div style="margin-bottom: 12px;">
                    <label class="mayhen_admin_label" style="font-size: 12px;">Tỷ lệ chiết khấu (%)</label>
                    <input type="number" step="0.5" min="0" max="100" name="commission_rate" value="{{ old('commission_rate', $partner->commission_rate) }}" class="mayhen_admin_input" style="width: 100%;" required>
                </div>

                <div style="margin-bottom: 16px;">
                    <label class="mayhen_admin_label" style="font-size: 12px;">Hạng đối tác (Tier)</label>
                    <select name="tier" class="mayhen_admin_select" style="width: 100%;">
                        <option value="silver" {{ $partner->tier === 'silver' ? 'selected' : '' }}>Silver (Bạc)</option>
                        <option value="gold" {{ $partner->tier === 'gold' ? 'selected' : '' }}>Gold (Vàng)</option>
                        <option value="platinum" {{ $partner->tier === 'platinum' ? 'selected' : '' }}>Platinum (Bạch Kim)</option>
                    </select>
                </div>

                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="width: 100%;">
                    Lưu Thay Đổi Chính Sách
                </button>
            </form>

            {{-- Hành Động Trạng Thái --}}
            <div style="border-top: 1px solid #EAE3D9; padding-top: 16px;">
                @if($partner->status === 'pending')
                    <form method="POST" action="{{ route('admin.partners.approve', $partner->id) }}" style="margin-bottom: 8px;">
                        @csrf
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--success" style="width: 100%;" onclick="return confirm('Phê duyệt đối tác này?')">
                            Phê Duyệt Hồ Sơ
                        </button>
                    </form>
                @endif

                @if($partner->status === 'active')
                    <button type="button" class="mayhen_admin_btn mayhen_admin_btn--danger" style="width: 100%;" onclick="document.getElementById('suspendModal').style.display='block'">
                        Đình Chỉ Hoạt Động
                    </button>
                @elseif($partner->status === 'suspended')
                    <form method="POST" action="{{ route('admin.partners.approve', $partner->id) }}">
                        @csrf
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--success" style="width: 100%;" onclick="return confirm('Mở khóa lại cho đối tác này?')">
                            Kích Hoạt Lại Hoạt Động
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabs / Danh sách Đám Cưới & Hoa Hồng --}}
    <div class="mayhen_admin_card mayhen_admin_mb_4">
        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #1F1D1A; margin-bottom: 16px;">
            Đám Cưới Do Đối Tác Quản Lý ({{ $weddings->total() }})
        </h3>

        <div class="mayhen_admin_table_wrapper">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th>Mã & Tên Đám Cưới</th>
                        <th>Cặp Đôi</th>
                        <th>Ngày Cưới</th>
                        <th>Mẫu Thiệp</th>
                        <th>Trạng Thái</th>
                        <th style="text-align: right;">Ngày Tạo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($weddings as $wedding)
                        <tr>
                            <td>
                                <strong style="color: #1F1D1A;">{{ $wedding->venue_name ?? 'Đám Cưới ' . $wedding->groom_name . ' & ' . $wedding->bride_name }}</strong>
                                <div style="font-size: 11px; font-family: monospace; color: #8C827A;">#{{ $wedding->id }}</div>
                            </td>
                            <td>
                                {{ $wedding->groom_name }} & {{ $wedding->bride_name }}
                            </td>
                            <td>
                                {{ $wedding->wedding_date ? \Carbon\Carbon::parse($wedding->wedding_date)->format('d/m/Y') : 'Chưa định ngày' }}
                            </td>
                            <td>
                                {{ $wedding->invitation ? $wedding->invitation->template_id : 'Chưa chọn mẫu' }}
                            </td>
                            <td>
                                <span class="mayhen_admin_badge mayhen_admin_badge--success">
                                    {{ $wedding->venue_name ? 'Đã xếp sảnh' : 'Chờ hoàn thiện' }}
                                </span>
                            </td>
                            <td style="text-align: right; color: #8C827A; font-size: 13px;">
                                {{ $wedding->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 24px; color: #8C827A;">
                                Đối tác chưa khởi tạo đám cưới nào trên nền tảng.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($weddings->hasPages())
            <div style="padding: 12px 16px; border-top: 1px solid #EAE3D9;">
                {{ $weddings->links() }}
            </div>
        @endif
    </div>

    {{-- Lịch Sử Hoa Hồng --}}
    <div class="mayhen_admin_card">
        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #1F1D1A; margin-bottom: 16px;">
            Lịch Sử Giao Dịch Hoa Hồng & Chi Trả ({{ $commissions->total() }})
        </h3>

        <div class="mayhen_admin_table_wrapper">
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Loại</th>
                        <th>Số Tiền</th>
                        <th>Mô Tả / Đơn Hàng</th>
                        <th>Chứng Từ Ngân Hàng</th>
                        <th>Trạng Thái</th>
                        <th style="text-align: right;">Thời Gian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $comm)
                        <tr>
                            <td style="font-family: monospace; font-weight: 600;">#{{ $comm->id }}</td>
                            <td>
                                @if($comm->type === 'payout')
                                    <span class="mayhen_admin_badge mayhen_admin_badge--danger">Rút tiền / Chi trả</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge--success">Hoa hồng</span>
                                @endif
                            </td>
                            <td style="font-weight: 600; color: {{ $comm->type === 'payout' ? '#9B1C1C' : '#2E7D32' }};">
                                {{ $comm->formatted_amount }}
                            </td>
                            <td>
                                <div>{{ $comm->description ?? 'Chiết khấu đơn hàng' }}</div>
                                @if($comm->order)
                                    <div style="font-size: 11px; color: #8C827A;">Đơn: {{ $comm->order->order_code }}</div>
                                @endif
                            </td>
                            <td>
                                @if($comm->payout_reference_code)
                                    <span style="font-family: monospace; font-size: 12px; background: #F3F4F6; padding: 2px 6px; border-radius: 4px; color: #1F1D1A;">
                                        {{ $comm->payout_reference_code }}
                                    </span>
                                    @if($comm->reconciledBy)
                                        <div style="font-size: 11px; color: #8C827A;">Duyệt bởi: {{ $comm->reconciledBy->name }}</div>
                                    @endif
                                @else
                                    <span style="color: #8C827A; font-size: 12px;">Chưa có</span>
                                @endif
                            </td>
                            <td>
                                @if($comm->status === 'paid')
                                    <span class="mayhen_admin_badge mayhen_admin_badge--success">Đã Chi Trả</span>
                                @elseif($comm->status === 'approved')
                                    <span class="mayhen_admin_badge mayhen_admin_badge--info">Đã Duyệt</span>
                                @else
                                    <span class="mayhen_admin_badge mayhen_admin_badge--warning">Chờ Xử Lý</span>
                                @endif
                            </td>
                            <td style="text-align: right; color: #8C827A; font-size: 13px;">
                                {{ $comm->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 24px; color: #8C827A;">
                                Chưa có giao dịch hoa hồng nào được ghi nhận.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($commissions->hasPages())
            <div style="padding: 12px 16px; border-top: 1px solid #EAE3D9;">
                {{ $commissions->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Modal Đình Chỉ Đối Tác --}}
<div id="suspendModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #FFFFFF; border-radius: 16px; max-width: 480px; width: 90%; margin: 100px auto; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #9B1C1C; margin-bottom: 12px;">
            Đình Chỉ Hoạt Động Đối Tác
        </h3>
        <p style="font-size: 13px; color: #555; margin-bottom: 16px;">
            Hành động này sẽ tạm dừng quyền quản lý đám cưới và không cho phép đối tác tạo dự án mới trên hệ thống.
        </p>

        <form method="POST" action="{{ route('admin.partners.suspend', $partner->id) }}">
            @csrf
            <div style="margin-bottom: 20px;">
                <label class="mayhen_admin_label" style="font-size: 12px;">Lý do đình chỉ (Bắt buộc)</label>
                <textarea name="reason" rows="4" class="mayhen_admin_input" style="width: 100%; resize: vertical;" placeholder="Ví dụ: Vi phạm quy định bảo mật thông tin khách hàng..." required></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="mayhen_admin_btn mayhen_admin_btn--outline" onclick="document.getElementById('suspendModal').style.display='none'">
                    Hủy Bỏ
                </button>
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--danger">
                    Xác Nhận Đình Chỉ
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
