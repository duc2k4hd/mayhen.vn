<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\NotificationGatewayConfigService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminNotificationGatewayController extends Controller
{
    public function __construct(
        protected NotificationGatewayConfigService $gatewayService
    ) {}

    /**
     * Màn hình quản trị cấu hình cổng tin nhắn SMS & Zalo ZNS (Mục 174 PRD V3).
     */
    public function index(Request $request): View
    {
        $activeTab = $request->query('tab', 'zalo');
        if (! in_array($activeTab, ['zalo', 'sms', 'sandbox'], true)) {
            $activeTab = 'zalo';
        }

        $kpis = $this->gatewayService->getGatewayKpis();
        $configs = $this->gatewayService->getGatewayConfigs(maskSecrets: true);
        $testLogs = $this->gatewayService->getTestLogs(25);

        return view('admin.pages.settings.notifications.index', compact(
            'activeTab',
            'kpis',
            'configs',
            'testLogs'
        ));
    }

    /**
     * Cập nhật cấu hình cổng thông báo (Zalo hoặc SMS).
     */
    public function update(Request $request, string $gateway): RedirectResponse
    {
        if (! in_array($gateway, ['zalo', 'sms'], true)) {
            return back()->with('error', 'Cổng cấu hình không hợp lệ.');
        }

        try {
            $result = $this->gatewayService->updateGatewayConfigs($gateway, $request->all(), $request->user());
            $count = count($result['updated_keys']);

            $msg = $count > 0
                ? "Đã lưu thành công {$count} thiết lập cho cổng ".($gateway === 'zalo' ? 'Zalo ZNS' : 'SMS Brandname').'.'
                : 'Cấu hình không có thay đổi nào mới.';

            return redirect()
                ->route('admin.settings.notifications.index', ['tab' => $gateway])
                ->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi khi lưu cấu hình: '.$e->getMessage());
        }
    }

    /**
     * Kiểm tra kết nối nhanh (Ping Gateway).
     */
    public function ping(Request $request, string $gateway): JsonResponse|RedirectResponse
    {
        $result = $this->gatewayService->testConnection($gateway);

        if ($request->wantsJson()) {
            return response()->json($result);
        }

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    /**
     * Gửi tin nhắn thử nghiệm (Test Dispatch) tới số điện thoại kiểm tra.
     */
    public function sendTest(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'channel' => ['required', 'string', 'in:zalo_zns,sms_brandname'],
            'recipient' => ['required', 'string', 'min:9', 'max:15'],
            'template_type' => ['required', 'string', 'max:50'],
        ], [
            'channel.required' => 'Vui lòng chọn kênh gửi tin nhắn thử nghiệm.',
            'recipient.required' => 'Vui lòng nhập số điện thoại nhận tin.',
            'recipient.min' => 'Số điện thoại phải có ít nhất 9 chữ số.',
            'recipient.max' => 'Số điện thoại không được vượt quá 15 chữ số.',
            'template_type.required' => 'Vui lòng chọn mẫu nội dung kiểm thử.',
        ]);

        try {
            $result = $this->gatewayService->dispatchTestMessage(
                channel: $request->input('channel'),
                recipient: $request->input('recipient'),
                templateType: $request->input('template_type'),
                admin: $request->user()
            );

            if ($request->wantsJson()) {
                return response()->json($result);
            }

            return redirect()
                ->route('admin.settings.notifications.index', ['tab' => 'sandbox'])
                ->with('success', $result['message']);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()
                ->route('admin.settings.notifications.index', ['tab' => 'sandbox'])
                ->with('error', 'Thử nghiệm thất bại: '.$e->getMessage());
        }
    }
}
