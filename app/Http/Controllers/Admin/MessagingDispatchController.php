<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MessagingDispatch;
use App\Models\Wedding;
use App\Services\ReliableMessagingDispatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MessagingDispatchController extends Controller
{
    /**
     * Danh sách các đợt phát tin nhắn thiệp cưới và chỉ số KPI.
     */
    public function index(Request $request): View
    {
        $query = MessagingDispatch::with(['wedding', 'creator', 'approver'])
            ->latest();

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('wedding_id')) {
            $query->where('wedding_id', $request->wedding_id);
        }

        $dispatches = $query->paginate(15)->withQueryString();

        $kpis = [
            'total_campaigns' => MessagingDispatch::count(),
            'total_sent' => MessagingDispatch::sum('successful_count'),
            'total_cost_vnd' => MessagingDispatch::sum('total_cost_vnd'),
            'deduplicated_saved' => MessagingDispatch::sum('deduplicated_count'),
        ];

        return view('admin.pages.messaging.index', compact('dispatches', 'kpis'));
    }

    /**
     * Màn hình tạo mới đợt phát tin nhắn.
     */
    public function create(Request $request): View
    {
        $weddings = Wedding::latest()->limit(50)->get(['id', 'groom_name', 'bride_name', 'wedding_date']);
        $selectedWeddingId = $request->query('wedding_id');

        $defaultTemplate = "Kính gửi [DANH_XUNG] [TEN_KHACH],\n\n[CAP_DOI] trân trọng kính mời [DANH_XUNG] tới dự lễ thành hôn vào ngày [NGAY_CUOI].\n\nXem thiệp cưới và gửi lời chúc phúc tại: [LINK_THIEP]\n\nRất hân hạnh được đón tiếp [DANH_XUNG]!";

        return view('admin.pages.messaging.create', compact('weddings', 'selectedWeddingId', 'defaultTemplate'));
    }

    /**
     * Lưu chiến dịch phát tin ở trạng thái bản nháp.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wedding_id' => ['required', 'exists:weddings,id'],
            'campaign_name' => ['required', 'string', 'min:3', 'max:150'],
            'channel' => ['required', 'in:zalo_zns,sms_brandname,invitation_link'],
            'filter_group' => ['nullable', 'string', 'max:100'],
            'message_template' => ['required', 'string', 'min:10', 'max:2000'],
        ], [
            'wedding_id.required' => 'Vui lòng chọn tiệc cưới phát tin.',
            'campaign_name.required' => 'Vui lòng nhập tên chiến dịch.',
            'message_template.required' => 'Vui lòng soạn nội dung mẫu tin nhắn.',
        ]);

        $wedding = Wedding::findOrFail($validated['wedding_id']);
        $dispatch = ReliableMessagingDispatchService::createCampaign($wedding, $validated, Auth::user());

        return redirect()->route('admin.messaging.show', $dispatch->id)
            ->with('success', "Đã tạo bản nháp chiến dịch \"{$dispatch->campaign_name}\". Vui lòng bấm \"Chạy Kiểm Thử Dry-Run\" để khử trùng lặp và tính toán chi phí.");
    }

    /**
     * Chi tiết chiến dịch: Xem trước tin nhắn từng khách và nút duyệt gửi.
     */
    public function show(MessagingDispatch $dispatch): View
    {
        $dispatch->load(['wedding.invitation', 'creator', 'approver', 'logs' => function ($q) {
            $q->latest()->limit(100);
        }]);

        return view('admin.pages.messaging.show', compact('dispatch'));
    }

    /**
     * Thực hiện kiểm thử Dry-Run không tốn phí.
     */
    public function dryRun(MessagingDispatch $dispatch): RedirectResponse
    {
        $result = ReliableMessagingDispatchService::runDryRun($dispatch);

        return back()->with('success', "Kiểm thử Dry-Run thành công: {$result['valid']} tin hợp lệ sẵn sàng gửi, {$result['deduplicated']} tin được loại bỏ chống gửi trùng lặp. Chi phí dự kiến: ".number_format($result['estimated_cost'], 0, ',', '.').'đ.');
    }

    /**
     * Phê duyệt gửi tin thật (Yêu cầu Manager trở lên - Dual Approval).
     */
    public function dispatchReal(MessagingDispatch $dispatch): RedirectResponse
    {
        if (! Auth::user()?->isAdmin()) {
            return back()->with('error', 'Chỉ Quản lý hoặc Quản trị viên cấp cao mới có quyền phê duyệt gửi tin nhắn thật.');
        }

        try {
            ReliableMessagingDispatchService::dispatchRealCampaign($dispatch, Auth::user());

            return back()->with('success', "ĐÃ PHÊ DUYỆT VÀ PHÁT TIN THÀNH CÔNG: {$dispatch->successful_count} tin nhắn đã được gửi đến quan khách.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi phát tin: '.$e->getMessage());
        }
    }
}
