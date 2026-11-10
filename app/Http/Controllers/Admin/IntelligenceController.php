<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingWish;
use App\Services\WeddingReadinessRadarService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class IntelligenceController extends Controller
{
    public function __construct(
        protected WeddingReadinessRadarService $radarService
    ) {}

    /**
     * Bảng điều khiển Radar Sẵn Sàng Ngày Cưới (Wedding Readiness Radar).
     */
    public function readinessRadar(Request $request): View
    {
        $daysAhead = (int) $request->input('days', 30);
        $daysAhead = in_array($daysAhead, [7, 14, 30, 60, 90]) ? $daysAhead : 30;

        $radarData = $this->radarService->scan($daysAhead);
        $items = $radarData['items'];

        // Lọc theo cấp độ rủi ro (red, yellow, green)
        if ($request->filled('risk')) {
            $risk = $request->input('risk');
            $items = $items->where('risk_level', $risk);
        }

        // Lọc theo tìm kiếm từ khóa
        if ($request->filled('search')) {
            $term = mb_strtolower($request->input('search'));
            $items = $items->filter(function ($item) use ($term) {
                return str_contains(mb_strtolower($item['couple']), $term)
                    || str_contains(mb_strtolower($item['wedding']->user?->name ?? ''), $term)
                    || str_contains(mb_strtolower($item['wedding']->venue_name ?? ''), $term);
            });
        }

        return view('admin.pages.intelligence.radar', [
            'kpis' => $radarData['kpis'],
            'items' => $items,
            'currentDays' => $daysAhead,
            'currentRisk' => $request->input('risk'),
            'currentSearch' => $request->input('search'),
        ]);
    }

    /**
     * Hộp Thư Tác Vụ Vận Hành Tập Trung (Action Inbox).
     */
    public function actionInbox(Request $request): View
    {
        $priorityFilter = $request->input('priority', 'all');

        $actions = collect();

        // 1. P0: Đơn hàng chờ đối soát thanh toán
        $pendingOrders = Order::with('user', 'plan')
            ->where('status', 'pending')
            ->latest()
            ->get();

        foreach ($pendingOrders as $order) {
            $actions->push([
                'id' => 'order_'.$order->id,
                'priority' => 'p0',
                'priority_label' => 'P0 — Khẩn',
                'category' => 'Tài chính & Đơn hàng',
                'title' => "Đơn hàng #{$order->order_code} chờ đối soát ngân hàng",
                'description' => "Khách hàng {$order->user?->name} ({$order->user?->email}) tạo đơn gói {$order->plan?->name} trị giá ".number_format($order->amount, 0, ',', '.').'đ.',
                'action_url' => route('admin.orders.show', $order->id),
                'action_label' => 'Đối soát ngay',
                'created_at' => $order->created_at,
            ]);
        }

        // 2. P1: Support Tickets khẩn cấp hoặc mở chưa được phản hồi
        if (Schema::hasTable('support_tickets')) {
            $urgentTickets = SupportTicket::with('user')
                ->whereIn('status', ['open', 'in_progress'])
                ->whereIn('priority', ['urgent', 'high'])
                ->latest()
                ->get();

            foreach ($urgentTickets as $ticket) {
                $actions->push([
                    'id' => 'ticket_'.$ticket->id,
                    'priority' => 'p1',
                    'priority_label' => 'P1 — Cao',
                    'category' => 'Hỗ trợ khách hàng',
                    'title' => "[{$ticket->ticket_code}] {$ticket->subject}",
                    'description' => "Ticket mức ưu tiên {$ticket->priority_label} từ khách hàng {$ticket->user?->name}.",
                    'action_url' => route('admin.support.show', $ticket->id),
                    'action_label' => 'Xử lý ticket',
                    'created_at' => $ticket->created_at,
                ]);
            }
        }

        // 3. P1: Đám cưới rủi ro cao diễn ra trong 7 ngày tới
        $radar7Days = $this->radarService->scan(7);
        foreach ($radar7Days['items']->where('risk_level', 'red') as $radarItem) {
            $wedding = $radarItem['wedding'];
            $daysLeft = $radarItem['days_remaining'];
            $actions->push([
                'id' => 'radar_'.$wedding->id,
                'priority' => 'p1',
                'priority_label' => 'P1 — Cao',
                'category' => 'Sẵn sàng ngày cưới',
                'title' => "Đám cưới {$radarItem['couple']} còn {$daysLeft} ngày (Điểm: {$radarItem['score']}/100)",
                'description' => 'Thiếu các thông tin thiết yếu: '.collect($radarItem['issues'])->pluck('title')->join(', ').'.',
                'action_url' => route('admin.intelligence.radar', ['search' => $radarItem['couple']]),
                'action_label' => 'Xem chi tiết radar',
                'created_at' => $wedding->created_at,
            ]);
        }

        // 4. P1: Lời chúc chưa duyệt
        $pendingWishesCount = WeddingWish::where('is_approved', false)->count();
        if ($pendingWishesCount > 0) {
            $actions->push([
                'id' => 'wishes_batch',
                'priority' => 'p1',
                'priority_label' => 'P1 — Cao',
                'category' => 'Kiểm duyệt nội dung',
                'title' => "Có {$pendingWishesCount} lời chúc mừng đang chờ kiểm duyệt",
                'description' => 'Khách mời đã gửi lời chúc vào sổ lưu bút cần được duyệt để hiển thị công khai trên thiệp.',
                'action_url' => route('admin.wishes.index', ['status' => 'pending']),
                'action_label' => 'Duyệt lời chúc',
                'created_at' => Carbon::now(),
            ]);
        }

        // 5. P2: Queue Failed Jobs
        $failedJobsCount = DB::table('failed_jobs')->count();
        if ($failedJobsCount > 0) {
            $actions->push([
                'id' => 'failed_jobs',
                'priority' => 'p2',
                'priority_label' => 'P2 — Thường',
                'category' => 'Kỹ thuật hệ thống',
                'title' => "Có {$failedJobsCount} tác vụ nền (Queue Job) bị thất bại",
                'description' => 'Một số tác vụ gửi email hoặc xử lý nền bị lỗi cần kiểm tra và retry.',
                'action_url' => route('admin.jobs.index'),
                'action_label' => 'Kiểm tra hàng đợi',
                'created_at' => Carbon::now(),
            ]);
        }

        // Lọc theo mức độ ưu tiên
        if ($priorityFilter !== 'all') {
            $actions = $actions->where('priority', $priorityFilter);
        }

        // Sắp xếp ưu tiên: P0 trước, rồi đến P1, P2
        $priorityWeight = ['p0' => 0, 'p1' => 1, 'p2' => 2];
        $actions = $actions->sortBy(fn ($item) => $priorityWeight[$item['priority']] ?? 99)->values();

        $kpis = [
            'total' => $actions->count(),
            'p0' => $actions->where('priority', 'p0')->count(),
            'p1' => $actions->where('priority', 'p1')->count(),
            'p2' => $actions->where('priority', 'p2')->count(),
        ];

        return view('admin.pages.intelligence.inbox', [
            'actions' => $actions,
            'kpis' => $kpis,
            'currentPriority' => $priorityFilter,
        ]);
    }

    /**
     * Thám Tử Đối Soát Doanh Thu & Giao Dịch (Finance Reconciliation Detective).
     */
    public function reconciliationDetective(Request $request): View
    {
        $orders = Order::with(['user', 'plan'])
            ->whereIn('status', ['paid', 'pending'])
            ->latest('id')
            ->take(100)
            ->get();

        // Đọc webhook logs để đối chiếu
        $webhookLogs = [];
        $logPath = storage_path('logs/payment_webhooks.jsonl');
        if (File::exists($logPath)) {
            $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (is_array($lines)) {
                foreach (array_slice(array_reverse($lines), 0, 200) as $line) {
                    $item = json_decode($line, true);
                    if (is_array($item)) {
                        $webhookLogs[] = $item;
                    }
                }
            }
        }

        $reconciledItems = $orders->map(function ($order) use ($webhookLogs) {
            $expectedAmount = (int) $order->amount;

            // Tìm log webhook tương ứng qua order_code hoặc provider_transaction_id
            $matchingLog = collect($webhookLogs)->first(function ($log) use ($order) {
                $content = $log['content'] ?? ($log['data']['content'] ?? '');
                $transId = $log['transaction_id'] ?? ($log['data']['id'] ?? null);

                return str_contains((string) $content, $order->order_code)
                    || ($order->provider_transaction_id && $transId == $order->provider_transaction_id);
            });

            $paidAmount = $matchingLog
                ? (int) ($matchingLog['transferAmount'] ?? ($matchingLog['data']['transferAmount'] ?? $order->amount))
                : ($order->isPaid() ? $expectedAmount : 0);

            $diff = $paidAmount - $expectedAmount;

            $status = match (true) {
                $order->isPaid() && $diff === 0 => 'matched',
                $order->isPaid() && $diff < 0 => 'underpaid',
                $order->isPaid() && $diff > 0 => 'overpaid',
                default => 'pending_match',
            };

            return [
                'order' => $order,
                'expected_amount' => $expectedAmount,
                'paid_amount' => $paidAmount,
                'diff' => $diff,
                'status' => $status,
                'log' => $matchingLog,
            ];
        });

        // Filter by status if selected
        if ($request->filled('status')) {
            $reconciledItems = $reconciledItems->where('status', $request->input('status'));
        }

        $kpis = [
            'total' => $orders->count(),
            'matched' => $reconciledItems->where('status', 'matched')->count(),
            'underpaid' => $reconciledItems->where('status', 'underpaid')->count(),
            'overpaid' => $reconciledItems->where('status', 'overpaid')->count(),
            'pending_match' => $reconciledItems->where('status', 'pending_match')->count(),
        ];

        return view('admin.pages.intelligence.reconciliation', [
            'items' => $reconciledItems,
            'kpis' => $kpis,
            'currentStatus' => $request->input('status'),
        ]);
    }

    /**
     * Delight Recovery Planner (Innovation #13).
     * Phát hiện các cặp đôi gặp khó khăn hoặc bị kẹt khi tạo thiệp trong Studio để CSKH chủ động hỗ trợ.
     */
    public function delightRecovery(Request $request): View
    {
        $stuckCases = collect();

        // 1. Paid but no published invitation (Gặp khó khăn sau khi thanh toán)
        $paidUsers = User::whereHas('orders', function ($q) {
            $q->where('status', 'paid');
        })->with(['orders.plan', 'weddings.invitations'])->get();

        foreach ($paidUsers as $user) {
            $hasPublished = $user->weddings->flatMap->invitations->contains('is_published', true);
            if (! $hasPublished) {
                $lastOrder = $user->orders->sortByDesc('paid_at')->first();
                $stuckCases->push([
                    'type' => 'paid_unpublished',
                    'severity' => 'high',
                    'title' => 'Đã thanh toán nhưng chưa xuất bản thiệp',
                    'user' => $user,
                    'wedding' => $user->weddings->first(),
                    'last_activity' => $lastOrder?->paid_at?->diffForHumans() ?? 'Gần đây',
                    'reason' => 'Khách đã mua gói '.($lastOrder?->plan?->name ?? 'dịch vụ').' nhưng chưa công bố thiệp. Có thể gặp khó khăn khâu tải ảnh hoặc chọn nhạc.',
                    'action_label' => 'Liên hệ hỗ trợ hoàn thiện',
                ]);
            }
        }

        // 2. Drafts abandoned (Bản nháp bị bỏ dở > 3 ngày)
        $abandonedDrafts = Invitation::where('is_published', false)
            ->where('updated_at', '<', now()->subDays(3))
            ->with(['wedding.user', 'template'])
            ->latest('updated_at')
            ->take(20)
            ->get();

        foreach ($abandonedDrafts as $draft) {
            if (! $stuckCases->contains(fn ($c) => ($c['user']->id ?? null) === $draft->wedding?->user_id)) {
                $stuckCases->push([
                    'type' => 'stuck_draft',
                    'severity' => 'medium',
                    'title' => 'Bản nháp ngừng cập nhật > 3 ngày',
                    'user' => $draft->wedding?->user,
                    'wedding' => $draft->wedding,
                    'invitation' => $draft,
                    'last_activity' => $draft->updated_at?->diffForHumans() ?? 'Vài ngày trước',
                    'reason' => 'Đã chọn mẫu '.($draft->template?->name ?? 'thiệp').' nhưng chưa hoàn tất thông tin đôi uyên ương hoặc ngày cưới.',
                    'action_label' => 'Nhắn tin hỏi thăm tiến độ',
                ]);
            }
        }

        // 3. High Views but 0 RSVPs (Lượt xem cao bất thường nhưng 0 phản hồi RSVP)
        $zeroRsvps = Invitation::where('is_published', true)
            ->where('view_count', '>=', 30)
            ->whereDoesntHave('wedding.guests.response')
            ->with(['wedding.user', 'template'])
            ->latest('view_count')
            ->take(10)
            ->get();

        foreach ($zeroRsvps as $zeroInv) {
            $stuckCases->push([
                'type' => 'zero_rsvps',
                'severity' => 'medium',
                'title' => 'Thiệp đạt '.$zeroInv->view_count.' lượt xem nhưng 0 phản hồi RSVP',
                'user' => $zeroInv->wedding?->user,
                'wedding' => $zeroInv->wedding,
                'invitation' => $zeroInv,
                'last_activity' => $zeroInv->updated_at?->diffForHumans() ?? 'Gần đây',
                'reason' => 'Khách xem nhiều nhưng chưa ai xác nhận tham dự. Cần kiểm tra form RSVP hoặc cài đặt hạn chót phản hồi.',
                'action_label' => 'Kiểm tra kỹ thuật thiệp',
            ]);
        }

        $kpis = [
            'total' => $stuckCases->count(),
            'high_priority' => $stuckCases->where('severity', 'high')->count(),
            'paid_unpublished' => $stuckCases->where('type', 'paid_unpublished')->count(),
            'stuck_drafts' => $stuckCases->where('type', 'stuck_draft')->count(),
            'zero_rsvps' => $stuckCases->where('type', 'zero_rsvps')->count(),
        ];

        return view('admin.pages.intelligence.delight_recovery', [
            'cases' => $stuckCases,
            'kpis' => $kpis,
        ]);
    }
}
