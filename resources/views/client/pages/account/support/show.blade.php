@extends('client.pages.account.layout')

@section('page_title', 'Yêu Cầu #' . $ticket->ticket_code . ' | ' . config('app.name'))
@section('account_breadcrumb', 'Chi Tiết Yêu Cầu #' . $ticket->ticket_code)

@section('account_content')
<div class="mayhen_account_support_detail_wrapper">

    {{-- Nút Quay Lại --}}
    <div style="margin-bottom: 20px;">
        <a href="{{ route('account.support.index') }}" style="color: #8C827A; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
            &larr; <span>Quay lại danh sách yêu cầu</span>
        </a>
    </div>

    {{-- Thẻ Tiêu Đề & Thông Tin Tổng Quan --}}
    <div class="mayhen_account_card" style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 16px; padding: 28px; margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <span style="font-family: monospace; font-size: 14px; font-weight: 700; color: #9C7A4A; background: #FAF8F5; border: 1px solid #EAE3D9; padding: 3px 8px; border-radius: 6px;">
                        #{{ $ticket->ticket_code }}
                    </span>
                    <span style="font-size: 13px; color: #8C827A;">
                        Gửi lúc: {{ $ticket->created_at->format('d/m/Y H:i') }}
                    </span>
                </div>
                <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 26px; color: #1F1D1A; margin: 0;">
                    {{ $ticket->subject }}
                </h2>
            </div>

            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                {{-- Badge Trạng Thái --}}
                @if($ticket->status === 'open')
                    <span style="padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #FEF3C7; color: #92400E;">
                        Mới (Đang chờ chuyên viên)
                    </span>
                @elseif($ticket->status === 'in_progress')
                    <span style="padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #DBEAFE; color: #1E40AF;">
                        Đang được xử lý
                    </span>
                @elseif($ticket->status === 'resolved')
                    <span style="padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #DEF7EC; color: #03543F;">
                        Đã giải quyết hoàn tất
                    </span>
                @else
                    <span style="padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #F3F4F6; color: #4B5563;">
                        Đã đóng yêu cầu
                    </span>
                @endif

                {{-- Badge Mức Độ --}}
                @if($ticket->priority === 'urgent')
                    <span style="padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 700; background: #FDE8E8; color: #9B1C1C;">
                        Khẩn cấp
                    </span>
                @elseif($ticket->priority === 'high')
                    <span style="padding: 6px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; background: #FEF08A; color: #854D0E;">
                        Ưu tiên cao
                    </span>
                @endif
            </div>
        </div>

        {{-- Thanh Thông Tin Bổ Trợ --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; padding: 16px; background: #FAF8F5; border-radius: 12px; border: 1px solid #EAE3D9; font-size: 13px;">
            <div>
                <span style="color: #8C827A; display: block; margin-bottom: 2px;">Phân loại:</span>
                <strong style="color: #1F1D1A;">
                    @switch($ticket->category)
                        @case('technical') Kỹ thuật thiệp cưới @break
                        @case('billing') Gói cước & Thanh toán @break
                        @case('invitation') Nội dung & Ảnh thiệp @break
                        @default Thắc mắc chung
                    @endswitch
                </strong>
            </div>
            <div>
                <span style="color: #8C827A; display: block; margin-bottom: 2px;">Chuyên viên phụ trách:</span>
                <strong style="color: #1F1D1A;">
                    {{ $ticket->assignedStaff ? $ticket->assignedStaff->name : 'Đang điều phối chuyên viên phù hợp' }}
                </strong>
            </div>
            <div>
                <span style="color: #8C827A; display: block; margin-bottom: 2px;">Thời hạn cam kết SLA:</span>
                <strong style="color: {{ $ticket->isBreached() ? '#9B1C1C' : '#2E7D32' }};">
                    @if($ticket->sla_deadline_at)
                        {{ $ticket->sla_deadline_at->format('d/m/Y H:i') }}
                        @if($ticket->isBreached() && in_array($ticket->status, ['open', 'in_progress']))
                            (Đang tăng tốc xử lý)
                        @endif
                    @else
                        Tiếp nhận tức thời
                    @endif
                </strong>
            </div>
        </div>
    </div>

    {{-- Nội Dung Yêu Cầu Gốc (Original Ticket Content) --}}
    <div class="mayhen_account_card" style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 16px; padding: 24px; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #1F1D1A; color: #C5A880; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 15px;">
                {{ mb_substr(auth()->user()->name ?? 'U', 0, 1, 'UTF-8') }}
            </div>
            <div>
                <strong style="color: #1F1D1A; font-size: 15px;">{{ auth()->user()->name }}</strong>
                <span style="font-size: 12px; color: #8C827A; margin-left: 8px;">(Nội dung yêu cầu ban đầu)</span>
            </div>
        </div>
        <div style="font-size: 14px; line-height: 1.7; color: #2C2824; white-space: pre-wrap; background: #FAF8F5; padding: 16px 20px; border-radius: 10px; border-left: 3px solid #C5A880;">
            {{ $ticket->body }}
        </div>
    </div>

    {{-- Chuỗi Hội Thoại & Phản Hồi (Replies Thread) --}}
    @if($replies->isNotEmpty())
        <div style="margin-bottom: 28px;">
            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 20px; color: #1F1D1A; margin: 0 0 16px;">
                Tiến Trình Trao Đổi ({{ $replies->count() }})
            </h3>

            <div style="display: flex; flex-direction: column; gap: 16px;">
                @foreach($replies as $reply)
                    <div class="mayhen_account_card" style="background: {{ $reply->is_staff_reply ? '#FDFBF7' : '#FFFFFF' }}; border: 1px solid {{ $reply->is_staff_reply ? '#E5DCCD' : '#EAE3D9' }}; border-radius: 16px; padding: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                @if($reply->is_staff_reply)
                                    <div style="width: 34px; height: 34px; border-radius: 50%; background: #9C7A4A; color: #FFFFFF; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;">
                                        MH
                                    </div>
                                    <div>
                                        <strong style="color: #9C7A4A; font-size: 14px;">Chuyên Viên Mây Hẹn ({{ $reply->user?->name ?? 'Đội ngũ hỗ trợ' }})</strong>
                                        <span style="display: inline-block; margin-left: 6px; font-size: 11px; padding: 2px 8px; border-radius: 10px; background: #EFE8DD; color: #6B635B; font-weight: 600;">Hỗ Trợ Khách Hàng</span>
                                    </div>
                                @else
                                    <div style="width: 34px; height: 34px; border-radius: 50%; background: #1F1D1A; color: #C5A880; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;">
                                        {{ mb_substr($reply->user?->name ?? 'U', 0, 1, 'UTF-8') }}
                                    </div>
                                    <div>
                                        <strong style="color: #1F1D1A; font-size: 14px;">{{ $reply->user?->name }}</strong>
                                    </div>
                                @endif
                            </div>
                            <span style="font-size: 12px; color: #8C827A;">
                                {{ $reply->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        <div style="font-size: 14px; line-height: 1.7; color: #2C2824; white-space: pre-wrap; padding-left: 44px;">
                            {{ $reply->body }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Khung Gửi Phản Hồi Bổ Sung --}}
    @if($ticket->status === 'closed')
        <div class="mayhen_account_alert" style="background: #F3F4F6; border: 1px solid #E5E7EB; color: #4B5563; padding: 18px 24px; border-radius: 12px; text-align: center;">
            <p style="margin: 0; font-size: 14px;">
                🔒 Yêu cầu này đã được đóng hoàn tất. Nếu bạn cần hỗ trợ thêm vấn đề khác, vui lòng <a href="{{ route('account.support.index') }}#form_gui_ticket" style="color: #9C7A4A; font-weight: 600; text-decoration: underline;">tạo yêu cầu mới</a>.
            </p>
        </div>
    @else
        <div class="mayhen_account_card" style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 16px; padding: 24px;">
            <h4 style="font-family: 'Cormorant Garamond', serif; font-size: 20px; color: #1F1D1A; margin: 0 0 12px;">
                Gửi Phản Hồi Bổ Sung
            </h4>
            <p style="font-size: 13px; color: #8C827A; margin: 0 0 16px;">
                Bạn có thể bổ sung thêm chi tiết, hình ảnh mô tả hoặc phản hồi lại câu trả lời của chuyên viên tại đây.
            </p>

            <form action="{{ route('account.support.reply', $ticket->ticket_code) }}" method="POST">
                @csrf
                <div style="margin-bottom: 16px;">
                    <textarea name="body" rows="4" required placeholder="Nhập nội dung phản hồi của bạn..." style="width: 100%; padding: 12px 14px; border: 1px solid #D8CEBD; border-radius: 10px; font-size: 14px; background: #FAF8F5; color: #1F1D1A; line-height: 1.6; box-sizing: border-box;"></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_gold" style="padding: 10px 24px; font-size: 14px; font-weight: 600; border: none; cursor: pointer;">
                        Gửi Phản Hồi
                    </button>
                </div>
            </form>
        </div>
    @endif

</div>
@endsection

