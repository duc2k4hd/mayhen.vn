<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Models\Wedding;
use App\Services\AuditLogger;
use App\Services\CannedResponseService;
use App\Services\IncidentResponseAndEscalationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function __construct(
        protected ?CannedResponseService $cannedService = null
    ) {
        $this->cannedService ??= app(CannedResponseService::class);
    }

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

        // Hàng đợi lọc nhanh: VIP 48h, Sự cố vận hành, Đã leo thang
        if ($request->input('queue') === 'vip_48h') {
            $query->whereIn('user_id', $vipWeddingUserIds);
        } elseif ($request->input('queue') === 'incidents') {
            $query->where('is_incident', true);
        } elseif ($request->input('queue') === 'escalated') {
            $query->whereNotNull('escalated_at');
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
            'escalated' => SupportTicket::whereNotNull('escalated_at')->whereIn('status', ['open', 'in_progress'])->count(),
            'incidents' => SupportTicket::where('is_incident', true)->whereIn('status', ['open', 'in_progress'])->count(),
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

        // Operational Memory & Incident SOP Cards / Canned Responses (Innovation #17 & Mục 17.1 PRD V3)
        $sopCards = $this->cannedService->getActiveResponsesForTicket($ticket);

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

        // Tăng lượt sử dụng nếu phản hồi từ Macro
        if ($request->filled('canned_response_id')) {
            $this->cannedService->incrementUsage((int) $request->input('canned_response_id'));
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

        // Chặn đóng sự cố nghiêm trọng nếu chưa có biên bản Postmortem (NXT-09)
        if ($newStatus === 'resolved' && $ticket->is_incident && empty($ticket->root_cause)) {
            return back()->with('error', 'Đây là Sự cố vận hành. Bắt buộc hoàn tất Biên bản Postmortem (Nguyên nhân gốc & Biện pháp khắc phục) tại khung Điều Phối Sự Cố trước khi đóng.');
        }

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

    /**
     * Khai báo sự cố vận hành chính thức (NXT-09).
     */
    public function declareIncident(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || (! $user->isManager() && ! $user->isSuperAdmin())) {
            abort(403, 'Chỉ Quản lý hoặc Quản trị viên tối cao mới có quyền khai báo Sự Cố Vận Hành.');
        }

        $request->validate([
            'severity' => ['required', 'in:sev1,sev2,sev3'],
            'reason' => ['required', 'string', 'min:6', 'max:255'],
        ], [
            'reason.required' => 'Vui lòng cung cấp lý do khai báo sự cố vận hành.',
            'reason.min' => 'Lý do khai báo sự cố phải có tối thiểu 6 ký tự.',
        ]);

        IncidentResponseAndEscalationService::declareIncident(
            $ticket,
            $request->input('severity'),
            $request->input('reason'),
            $user->id
        );

        return back()->with('success', 'Đã công bố Sự Cố Vận Hành ['.strtoupper($request->input('severity'))."] cho ticket #{$ticket->ticket_code}.");
    }

    /**
     * Khép kín sự cố bằng biên bản Postmortem điều tra nguyên nhân gốc (NXT-09).
     */
    public function resolveIncident(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || (! $user->isManager() && ! $user->isSuperAdmin())) {
            abort(403, 'Chỉ Quản lý hoặc Quản trị viên tối cao mới có quyền đóng sự cố và ký duyệt Postmortem.');
        }

        $request->validate([
            'root_cause' => ['required', 'string', 'min:10', 'max:5000'],
            'mitigation_action' => ['required', 'string', 'min:10', 'max:5000'],
            'postmortem_notes' => ['required', 'string', 'min:10', 'max:5000'],
        ], [
            'root_cause.required' => 'Bắt buộc làm rõ nguyên nhân gốc rễ (Root Cause) của sự cố.',
            'root_cause.min' => 'Mô tả nguyên nhân gốc phải có tối thiểu 10 ký tự.',
            'mitigation_action.required' => 'Bắt buộc ghi nhận biện pháp khắc phục (Mitigation Action).',
            'mitigation_action.min' => 'Mô tả biện pháp khắc phục phải có tối thiểu 10 ký tự.',
            'postmortem_notes.required' => 'Bắt buộc ghi nhận bài học rút ra và quy trình phòng ngừa tái diễn.',
            'postmortem_notes.min' => 'Bài học rút ra phải có tối thiểu 10 ký tự.',
        ]);

        IncidentResponseAndEscalationService::resolveWithPostmortem(
            $ticket,
            $request->input('root_cause'),
            $request->input('mitigation_action'),
            $request->input('postmortem_notes'),
            $user->id
        );

        return back()->with('success', "Đã ký duyệt biên bản Postmortem và đóng sự cố #{$ticket->ticket_code} thành công.");
    }

    /**
     * Leo thang ticket thủ công lên cấp Quản lý hoặc Super Admin (NXT-09).
     */
    public function escalate(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $request->validate([
            'level' => ['required', 'in:manager,super_admin'],
            'reason' => ['required', 'string', 'min:6', 'max:255'],
        ]);

        $ticket->update([
            'escalated_at' => now(),
            'escalation_level' => $request->input('level'),
            'escalation_reason' => $request->input('reason'),
        ]);

        AuditLogger::log(
            'ticket_manual_escalated',
            'SupportTicket',
            $ticket->id,
            "Leo thang ticket [{$ticket->ticket_code}] lên [{$request->input('level')}]. Lý do: {$request->input('reason')}"
        );

        return back()->with('success', "Đã leo thang ticket #{$ticket->ticket_code} lên cấp ".strtoupper($request->input('level')).'.');
    }

    /**
     * Kích hoạt quét tự động các ticket quá hạn SLA để leo thang (NXT-09).
     */
    public function scanSlaBreaches(): RedirectResponse
    {
        $escalated = IncidentResponseAndEscalationService::checkAndEscalateTickets();

        $count = $escalated->count();
        if ($count > 0) {
            return back()->with('warning', "Đã quét và phát hiện {$count} ticket vi phạm hạn SLA. Toàn bộ đã được tự động leo thang lên Quản lý.");
        }

        return back()->with('success', 'Toàn bộ ticket đang trong hạn cam kết SLA. Không phát hiện vi phạm.');
    }
}
