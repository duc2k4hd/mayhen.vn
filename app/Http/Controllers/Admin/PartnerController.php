<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\PartnerCommission;
use App\Services\PartnerManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PartnerController extends Controller
{
    public function __construct(
        protected PartnerManagementService $partnerService
    ) {}

    /**
     * Danh sách mạng lưới đối tác Studio & Wedding Planner.
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $request->query('search'),
            'status' => $request->query('status', 'all'),
            'tier' => $request->query('tier', 'all'),
        ];

        $metrics = $this->partnerService->getPartnerNetworkMetrics();
        $partners = $this->partnerService->getPartnersList($filters, 15);

        return view('admin.pages.partners.index', [
            'metrics' => $metrics,
            'partners' => $partners,
            'filters' => $filters,
        ]);
    }

    /**
     * Chi tiết hồ sơ đối tác, đám cưới quản lý & hoa hồng.
     */
    public function show(int $id): View
    {
        $partner = Partner::with(['user'])->findOrFail($id);

        $weddings = $partner->weddings()->with(['user', 'invitation'])->latest()->paginate(10, ['*'], 'weddings_page');
        $commissions = $partner->commissions()->with(['order', 'wedding', 'reconciledBy'])->latest()->paginate(10, ['*'], 'commissions_page');

        return view('admin.pages.partners.show', [
            'partner' => $partner,
            'weddings' => $weddings,
            'commissions' => $commissions,
        ]);
    }

    /**
     * Phê duyệt hồ sơ đối tác Studio.
     */
    public function approve(Request $request, int $id): RedirectResponse
    {
        $partner = Partner::findOrFail($id);
        $adminId = (int) $request->user()->id;

        $this->partnerService->approvePartner($partner, $adminId);

        return back()->with('success', "Đã phê duyệt và kích hoạt đối tác '{$partner->business_name}' thành công.");
    }

    /**
     * Đình chỉ hoạt động của đối tác Studio.
     */
    public function suspend(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ], [
            'reason.required' => 'Vui lòng nhập lý do đình chỉ đối tác.',
            'reason.min' => 'Lý do đình chỉ phải có ít nhất 5 ký tự.',
        ]);

        $partner = Partner::findOrFail($id);
        $adminId = (int) $request->user()->id;

        $this->partnerService->suspendPartner($partner, $request->input('reason'), $adminId);

        return back()->with('success', "Đã đình chỉ đối tác '{$partner->business_name}'.");
    }

    /**
     * Cập nhật tỷ lệ hoa hồng & hạng đối tác.
     */
    public function updateRate(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
            'tier' => 'required|string|in:silver,gold,platinum',
        ], [
            'commission_rate.required' => 'Vui lòng nhập tỷ lệ hoa hồng.',
            'commission_rate.numeric' => 'Tỷ lệ hoa hồng phải là số.',
            'commission_rate.min' => 'Tỷ lệ hoa hồng không được âm.',
            'commission_rate.max' => 'Tỷ lệ hoa hồng không vượt quá 100%.',
            'tier.in' => 'Hạng đối tác không hợp lệ.',
        ]);

        $partner = Partner::findOrFail($id);
        $adminId = (int) $request->user()->id;

        $this->partnerService->updateCommissionRate(
            $partner,
            (float) $request->input('commission_rate'),
            $request->input('tier'),
            $adminId
        );

        return back()->with('success', "Đã cập nhật chính sách hoa hồng cho đối tác '{$partner->business_name}'.");
    }

    /**
     * Sổ đối soát & quyết toán hoa hồng toàn mạng lưới.
     */
    public function commissions(Request $request): View
    {
        $filters = [
            'partner_id' => $request->query('partner_id'),
            'status' => $request->query('status', 'all'),
            'type' => $request->query('type', 'all'),
        ];

        $query = PartnerCommission::with(['partner', 'order', 'wedding', 'reconciledBy'])->latest();

        if (! empty($filters['partner_id'])) {
            $query->where('partner_id', $filters['partner_id']);
        }
        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        $commissions = $query->paginate(20)->withQueryString();
        $partners = Partner::orderBy('business_name')->get(['id', 'business_name', 'partner_code']);

        return view('admin.pages.partners.commissions', [
            'commissions' => $commissions,
            'partners' => $partners,
            'filters' => $filters,
        ]);
    }

    /**
     * Quyết toán và chi trả hoa hồng có chứng từ ngân hàng thật (P0).
     */
    public function settle(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'bank_reference_code' => 'required|string|min:4|max:100',
            'reconciliation_note' => 'nullable|string|max:1000',
        ], [
            'bank_reference_code.required' => 'Bắt buộc phải nhập mã giao dịch/chứng từ chuyển khoản ngân hàng.',
            'bank_reference_code.min' => 'Mã giao dịch ngân hàng phải có tối thiểu 4 ký tự.',
        ]);

        $commission = PartnerCommission::findOrFail($id);
        $adminId = (int) $request->user()->id;

        $this->partnerService->settleCommission(
            $commission,
            $request->input('bank_reference_code'),
            $request->input('reconciliation_note') ?? 'Quyết toán chuyển khoản ngân hàng',
            $adminId
        );

        return back()->with('success', "Đã quyết toán giao dịch hoa hồng #{$commission->id} thành công với mã chứng từ '{$request->input('bank_reference_code')}'.");
    }

    /**
     * Xuất file CSV đối soát hoa hồng.
     */
    public function exportCommissions(Request $request): StreamedResponse
    {
        $filters = [
            'partner_id' => $request->query('partner_id'),
            'status' => $request->query('status', 'all'),
            'type' => $request->query('type', 'all'),
        ];

        $data = $this->partnerService->getCommissionsForExport($filters);

        $fileName = 'mayhen_partner_commissions_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($data) {
            $file = fopen('php://output', 'w');
            // Xuất UTF-8 BOM để Excel hiển thị đúng dấu tiếng Việt
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'ID',
                'Mã Đối Tác',
                'Tên Doanh Nghiệp',
                'Số Tiền (VNĐ)',
                'Loại Giao Dịch',
                'Trạng Thái',
                'Mô Tả',
                'Mã Đơn Hàng',
                'Mã Chứng Từ Ngân Hàng',
                'Thời Gian Đối Soát',
                'Người Đối Soát',
                'Ngày Tạo',
            ]);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row['id'],
                    $row['partner_code'],
                    $row['business_name'],
                    $row['amount'],
                    $row['type'],
                    $row['status'],
                    $row['description'],
                    $row['order_code'],
                    $row['payout_reference_code'],
                    $row['reconciled_at'],
                    $row['reconciled_by'],
                    $row['created_at'],
                ]);
            }

            fclose($file);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
