<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WeddingDomain;
use App\Services\AdminDomainManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDomainManagerController extends Controller
{
    public function __construct(
        protected AdminDomainManagerService $domainService
    ) {}

    /**
     * Màn hình quản trị tên miền riêng, subdomain & định tuyến (Mục 172 PRD V3).
     */
    public function index(Request $request): View
    {
        $currentTab = $request->query('tab', 'domains');
        if (! in_array($currentTab, ['domains', 'settings', 'diagnostic'], true)) {
            $currentTab = 'domains';
        }

        $kpis = $this->domainService->getDomainKpis();
        $domains = $this->domainService->getFilteredDomains($request->all(), 15);
        $routingConfig = $this->domainService->getPlatformRoutingConfig();
        $lookupResult = session('lookup_result');

        return view('admin.pages.settings.domains.index', compact(
            'currentTab',
            'kpis',
            'domains',
            'routingConfig',
            'lookupResult'
        ));
    }

    /**
     * Xác minh cấu hình DNS CNAME tức thời cho tên miền.
     */
    public function verify(Request $request, WeddingDomain $domain): RedirectResponse
    {
        $res = $this->domainService->verifyDomain($domain, $request->user());

        if ($res['success']) {
            return back()->with('success', $res['message']);
        }

        return back()->with('error', $res['message']);
    }

    /**
     * Cưỡng chế kích hoạt tên miền.
     */
    public function activate(Request $request, WeddingDomain $domain): RedirectResponse
    {
        $this->domainService->forceActivate($domain, $request->user());

        return back()->with('success', 'Đã cưỡng chế kích hoạt trạng thái ACTIVE cho tên miền.');
    }

    /**
     * Cưỡng chế ngắt kết nối tên miền.
     */
    public function deactivate(Request $request, WeddingDomain $domain): RedirectResponse
    {
        $this->domainService->forceDeactivate($domain, $request->user());

        return back()->with('success', 'Đã ngắt kết nối tên miền thành công.');
    }

    /**
     * Xóa vĩnh viễn liên kết tên miền.
     */
    public function destroy(Request $request, WeddingDomain $domain): RedirectResponse
    {
        $this->domainService->deleteDomain($domain, $request->user());

        return back()->with('success', 'Đã xóa bản ghi tên miền khỏi hệ thống.');
    }

    /**
     * Chẩn đoán DNS Lookup thời gian thực.
     */
    public function lookup(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'hostname' => ['required', 'string', 'max:255'],
        ], [
            'hostname.required' => 'Vui lòng nhập tên miền cần tra cứu DNS.',
        ]);

        try {
            $result = $this->domainService->performDnsLookup($request->input('hostname'));

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            return redirect()
                ->route('admin.settings.domains.index', ['tab' => 'diagnostic'])
                ->with('lookup_result', $result)
                ->with('success', "Tra cứu DNS hoàn tất cho tên miền \"{$result['hostname']}\" ({$result['latency_ms']}ms).");
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => $e->getMessage(),
                ], 422);
            }

            return redirect()
                ->route('admin.settings.domains.index', ['tab' => 'diagnostic'])
                ->with('error', 'Lỗi tra cứu DNS: '.$e->getMessage());
        }
    }

    /**
     * Cập nhật các thiết lập định tuyến toàn nền tảng.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'cname_target' => ['required', 'string', 'max:150'],
            'server_ip' => ['required', 'string', 'max:50'],
            'root_domain' => ['required', 'string', 'max:100'],
        ], [
            'cname_target.required' => 'Vui lòng nhập mục tiêu CNAME chuẩn.',
            'server_ip.required' => 'Vui lòng nhập địa chỉ IPv4 máy chủ.',
            'root_domain.required' => 'Vui lòng nhập tên miền gốc.',
        ]);

        try {
            $this->domainService->updatePlatformRoutingConfig($request->all(), $request->user());

            return redirect()
                ->route('admin.settings.domains.index', ['tab' => 'settings'])
                ->with('success', 'Đã lưu cấu hình định tuyến nền tảng thành công.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi khi lưu cấu hình: '.$e->getMessage());
        }
    }
}
