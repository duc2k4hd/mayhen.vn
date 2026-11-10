<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\Wedding;
use App\Services\AuditLogger;
use App\Services\IncidentResponseAndEscalationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientSupportController extends Controller
{
    /**
     * Danh sách các yêu cầu hỗ trợ của khách hàng.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $tickets = SupportTicket::where('user_id', $user->id)
            ->with('assignedStaff')
            ->latest()
            ->paginate(10);

        $isVip48h = Wedding::where('user_id', $user->id)
            ->whereBetween('wedding_date', [Carbon::today(), Carbon::today()->addDays(2)])
            ->exists();

        $openTicketsCount = SupportTicket::where('user_id', $user->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->count();

        $resolvedTicketsCount = SupportTicket::where('user_id', $user->id)
            ->whereIn('status', ['resolved', 'closed'])
            ->count();

        return view('client.pages.account.support.index', [
            'tickets' => $tickets,
            'isVip48h' => $isVip48h,
            'openTicketsCount' => $openTicketsCount,
            'resolvedTicketsCount' => $resolvedTicketsCount,
        ]);
    }

    /**
     * Gửi yêu cầu hỗ trợ mới từ khách hàng.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'subject' => ['required', 'string', 'min:5', 'max:200'],
            'category' => ['required', 'in:technical,billing,invitation,general'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'body' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'subject.required' => 'Vui lòng nhập tiêu đề yêu cầu hỗ trợ.',
            'subject.min' => 'Tiêu đề cần có ít nhất 5 ký tự.',
            'category.required' => 'Vui lòng chọn phân loại yêu cầu.',
            'category.in' => 'Phân loại yêu cầu không hợp lệ.',
            'priority.required' => 'Vui lòng chọn mức độ ưu tiên.',
            'priority.in' => 'Mức độ ưu tiên không hợp lệ.',
            'body.required' => 'Vui lòng mô tả chi tiết nội dung cần trợ giúp.',
            'body.min' => 'Nội dung cần có ít nhất 10 ký tự để chuyên viên có đủ thông tin hỗ trợ.',
        ]);

        // Kiểm tra tiệc cưới trong 48h để tự động ưu tiên cao nhất
        $isVip48h = Wedding::where('user_id', $user->id)
            ->whereBetween('wedding_date', [Carbon::today(), Carbon::today()->addDays(2)])
            ->exists();

        $priority = $validated['priority'];
        if ($isVip48h && $priority !== 'urgent') {
            $priority = 'urgent';
        }

        $ticket = new SupportTicket([
            'ticket_code' => SupportTicket::generateCode(),
            'user_id' => $user->id,
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $priority,
            'status' => 'open',
            'body' => $validated['body'],
        ]);

        $ticket->sla_deadline_at = IncidentResponseAndEscalationService::calculateSlaDeadline($ticket);
        $ticket->save();

        AuditLogger::log(
            action: 'support.ticket_submitted_by_client',
            targetType: 'support_ticket',
            targetId: $ticket->id,
            description: 'Khách hàng gửi yêu cầu hỗ trợ '.$ticket->ticket_code,
            details: [
                'ticket_code' => $ticket->ticket_code,
                'subject' => $ticket->subject,
                'category' => $ticket->category,
                'priority' => $ticket->priority,
                'is_vip_48h' => $isVip48h,
                'sla_deadline_at' => $ticket->sla_deadline_at?->toIso8601String(),
            ],
        );

        return redirect()->route('account.support.show', $ticket->ticket_code)
            ->with('success', 'Yêu cầu hỗ trợ '.$ticket->ticket_code.' đã được gửi thành công. Đội ngũ Mây Hẹn sẽ phản hồi trong thời gian sớm nhất!');
    }

    /**
     * Xem chi tiết ticket và chuỗi hội thoại trao đổi.
     */
    public function show(Request $request, string $ticketCode): View
    {
        $user = $request->user();

        $ticket = SupportTicket::where('ticket_code', $ticketCode)
            ->where('user_id', $user->id)
            ->with(['assignedStaff'])
            ->firstOrFail();

        $replies = $ticket->replies()
            ->where('is_internal_note', false)
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('client.pages.account.support.show', [
            'ticket' => $ticket,
            'replies' => $replies,
        ]);
    }

    /**
     * Khách hàng gửi phản hồi bổ sung vào ticket.
     */
    public function reply(Request $request, string $ticketCode): RedirectResponse
    {
        $user = $request->user();

        $ticket = SupportTicket::where('ticket_code', $ticketCode)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($ticket->status === 'closed') {
            return back()->with('error', 'Yêu cầu hỗ trợ này đã được đóng hoàn tất. Vui lòng gửi một yêu cầu hỗ trợ mới nếu bạn cần trợ giúp thêm.');
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:5000'],
        ], [
            'body.required' => 'Vui lòng nhập nội dung phản hồi.',
            'body.min' => 'Nội dung phản hồi tối thiểu 2 ký tự.',
        ]);

        SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'body' => $validated['body'],
            'is_staff_reply' => false,
            'is_internal_note' => false,
        ]);

        // Nếu ticket đang ở resolved mà khách hàng phản hồi lại, mở lại in_progress
        if ($ticket->status === 'resolved') {
            $ticket->update(['status' => 'in_progress']);
        }

        AuditLogger::log(
            action: 'support.client_reply_sent',
            targetType: 'support_ticket',
            targetId: $ticket->id,
            description: 'Khách hàng phản hồi bổ sung cho ticket '.$ticket->ticket_code,
            details: [
                'ticket_code' => $ticket->ticket_code,
                'body_length' => mb_strlen($validated['body']),
            ],
        );

        return back()->with('success', 'Phản hồi của bạn đã được chuyển tới chuyên viên phụ trách.');
    }
}
