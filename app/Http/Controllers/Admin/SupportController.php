<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Models\Wedding;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    /**
     * Danh sách tickets với KPI tổng quan.
     */
    public function index(Request $request): View
    {
        $query = SupportTicket::with(['user', 'assignedStaff'])
            ->latest();

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo ưu tiên
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Lọc theo phân loại
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Lọc theo staff phụ trách
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Hàng đợi VIP Concierge: Đám cưới diễn ra trong vòng 48 giờ tới (Innovation #16)
        $vipWeddingUserIds = Wedding::whereBetween('wedding_date', [Carbon::today(), Carbon::today()->addDays(2)])
            ->pluck('user_id')
            ->unique()
            ->all();

        if ($request->input('queue') === 'vip_48h') {
            $query->whereIn('user_id', $vipWeddingUserIds);
        }

        // Tìm kiếm theo tiêu đề hoặc mã ticket
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('ticket_code', 'like', "%{$term}%")
                    ->orWhere('subject', 'like', "%{$term}%");
            });
        }

        $tickets = $query->paginate(20)->withQueryString();

        // KPI cards
        $kpis = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'urgent' => SupportTicket::where('priority', 'urgent')->whereIn('status', ['open', 'in_progress'])->count(),
            'vip_48h' => SupportTicket::whereIn('user_id', $vipWeddingUserIds)->whereIn('status', ['open', 'in_progress'])->count(),
        ];

        // SLA: Thời gian phản hồi trung bình (giờ) — tính từ những ticket đã có phản hồi
        $ticketsWithResponse = SupportTicket::whereNotNull('first_responded_at')
            ->get(['created_at', 'first_responded_at']);

        $kpis['avg_first_response_hours'] = $ticketsWithResponse->isNotEmpty()
            ? round($ticketsWithResponse->avg(function ($ticket) {
                return $ticket->created_at->diffInMinutes($ticket->first_responded_at);
            }) / 60, 1)
            : null;

        // Danh sách staff để filter / assign
        $staffList = User::whereIn('role', ['admin', 'manager', 'staff'])->orderBy('name')->get(['id', 'name', 'role']);

        return view('admin.pages.support.index', compact('tickets', 'kpis', 'staffList', 'vipWeddingUserIds'));
    }

    /**
     * Chi tiết ticket và thread phản hồi.
     */
    public function show(SupportTicket $ticket): View
    {
        $ticket->load(['user', 'assignedStaff', 'replies.author']);

        $staffList = User::whereIn('role', ['admin', 'manager', 'staff'])->orderBy('name')->get(['id', 'name']);

        // Operational Memory & Incident SOP Cards (Innovation #17)
        $sopCards = [
            [
                'id' => 'audio_safari',
                'title' => 'Âm thanh không tự phát trên iPhone Safari',
                'category' => 'template',
                'reply_template' => 'Chào bạn, trên các thiết bị iPhone/iPad, trình duyệt Safari của Apple có chính sách bảo mật ngăn website tự ý phát âm thanh khi chưa có tương tác chạm màn hình từ người dùng. Bạn hoàn toàn yên tâm nhé: ngay khi quan khách bấm vào nút "Mở Thiệp" hoặc chạm nhẹ vào bất kỳ điểm nào trên thiệp, nhạc nền du dương sẽ tự động cất lên ngay lập tức.',
            ],
            [
                'id' => 'qr_pin_rescue',
                'title' => 'Quên mã PIN sảnh tiệc / Cần hỗ trợ điểm danh ngày cưới',
                'category' => 'technical',
                'reply_template' => 'Chào bạn, đội ngũ Atelier Mây Hẹn đã kiểm tra hệ thống tiếp tân cho tiệc cưới của bạn. Để xử lý gấp tại sảnh tiệc, bạn có thể sử dụng màn hình Rescue Cockpit ngày cưới trên hệ thống quản trị hoặc đăng nhập lại tài khoản chủ tiệc. Toàn bộ quan khách quét mã QR đều được điểm danh tức thời mà không lo bị nghẽn mạng.',
            ],
            [
                'id' => 'venue_change_last_minute',
                'title' => 'Đổi sảnh tiệc hoặc địa chỉ đón tiếp giờ chót',
                'category' => 'invitation',
                'reply_template' => 'Chào bạn, Mây Hẹn đã cập nhật thông tin địa điểm sảnh tiệc mới trực tiếp vào hệ thống thiệp của bạn. Toàn bộ quan khách khi truy cập link thiệp cưới hoặc quét mã QR trên phong bì sẽ thấy ngay địa chỉ và bản đồ Google Maps mới nhất mà bạn không cần phải in ấn lại thiệp giấy.',
            ],
            [
                'id' => 'vietqr_activation',
                'title' => 'Đối soát kích hoạt gói dịch vụ qua VietQR / SePay',
                'category' => 'billing',
                'reply_template' => 'Chào bạn, Mây Hẹn đã hoàn tất việc đối chiếu mã giao dịch thanh toán chuyển khoản trên hệ thống ngân hàng và đã kích hoạt thành công gói dịch vụ cho bạn. Bạn có thể tiếp tục tùy chỉnh và công bố thiệp cưới ngay bây giờ nhé.',
            ],
        ];

        return view('admin.pages.support.show', compact('ticket', 'staffList', 'sopCards'));
    }

    /**
     * Admin / staff gửi phản hồi cho ticket.
     */
    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $request->validate([
            'body' => ['required', 'string', 'min:5', 'max:5000'],
            'is_internal_note' => ['nullable', 'boolean'],
        ]);

        $isInternal = (bool) $request->boolean('is_internal_note');

        $reply = SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'body' => $request->body,
            'is_staff_reply' => true,
            'is_internal_note' => $isInternal,
        ]);

        // Ghi nhận thời điểm phản hồi đầu tiên (SLA)
        if (! $ticket->first_responded_at && ! $isInternal) {
            $ticket->update(['first_responded_at' => now()]);
        }

        // Chuyển status sang in_progress nếu đang open
        if ($ticket->status === 'open' && ! $isInternal) {
            $ticket->update(['status' => 'in_progress']);
        }

        AuditLogger::log(
            'ticket_reply',
            'SupportTicket',
            $ticket->id,
            ($isInternal ? '[Nội bộ] ' : '').'Phản hồi ticket '.$ticket->ticket_code,
            ['reply_id' => $reply->id]
        );

        return redirect()->route('admin.support.show', $ticket)
            ->with('success', 'Đã gửi phản hồi thành công.');
    }

    /**
     * Cập nhật trạng thái ticket.
     */
    public function updateStatus(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $newStatus = $request->status;

        if (! $ticket->canTransitionTo($newStatus)) {
            return back()->with('error', "Không thể chuyển từ '{$ticket->status_label}' sang '{$newStatus}'.");
        }

        $oldStatus = $ticket->status;
        $updateData = ['status' => $newStatus];

        if ($newStatus === 'resolved' && ! $ticket->resolved_at) {
            $updateData['resolved_at'] = now();
        }

        if ($newStatus === 'closed' && ! $ticket->closed_at) {
            $updateData['closed_at'] = now();
        }

        $ticket->update($updateData);

        AuditLogger::log(
            'ticket_status_changed',
            'SupportTicket',
            $ticket->id,
            "Chuyển trạng thái ticket {$ticket->ticket_code}: {$oldStatus} → {$newStatus}"
        );

        return back()->with('success', 'Đã cập nhật trạng thái ticket.');
    }

    /**
     * Giao ticket cho staff.
     */
    public function assignTo(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $request->validate([
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $assignedId = $request->assigned_to ?: null;
        $assignedUser = $assignedId ? User::find($assignedId) : null;

        $ticket->update(['assigned_to' => $assignedId]);

        AuditLogger::log(
            'ticket_assigned',
            'SupportTicket',
            $ticket->id,
            'Giao ticket '.$ticket->ticket_code.' cho '.($assignedUser?->name ?? 'không ai'),
            ['assigned_to' => $assignedId]
        );

        return back()->with('success', $assignedUser ? "Đã giao cho {$assignedUser->name}." : 'Đã bỏ giao việc.');
    }
}
