<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlatformSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSecurityController extends Controller
{
    public function __construct(
        protected PlatformSecurityService $securityService
    ) {}

    /**
     * Bảng điều khiển Quản trị An Ninh Mạng, Tường Lửa & Rate Limiting (Mục 177 PRD V3).
     */
    public function index(Request $request): View
    {
        $currentTab = $request->query('tab', 'ip-rules');
        if (! in_array($currentTab, ['ip-rules', 'rate-limits', 'threat-logs'], true)) {
            $currentTab = 'ip-rules';
        }

        $kpis = $this->securityService->getSecurityKpis();
        $rateLimitConfigs = $this->securityService->getRateLimitConfigs();

        $ipRules = null;
        $threatLogs = null;

        if ($currentTab === 'ip-rules') {
            $ipRules = $this->securityService->getFilteredIpRules(
                search: $request->query('search'),
                type: $request->query('type'),
                perPage: 15
            );
        } elseif ($currentTab === 'threat-logs') {
            $threatLogs = $this->securityService->getFilteredThreatLogs(
                eventType: $request->query('event_type'),
                ip: $request->query('ip'),
                perPage: 20
            );
        }

        return view('admin.pages.settings.security.index', compact(
            'currentTab',
            'kpis',
            'rateLimitConfigs',
            'ipRules',
            'threatLogs'
        ));
    }

    /**
     * Thêm mới một quy tắc IP (Blacklist hoặc Whitelist).
     */
    public function storeIpRule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ip_address' => ['required', 'ip'],
            'type' => ['required', 'in:blacklist,whitelist'],
            'reason' => ['required', 'string', 'max:255'],
            'duration_hours' => ['nullable', 'integer', 'min:1', 'max:8760'], // tối đa 1 năm
        ]);

        $rule = $this->securityService->addIpRule($validated, $request->user());
        $typeName = $rule->type === 'blacklist' ? 'Sổ đen' : 'Sổ trắng';

        return redirect()->route('admin.settings.security.index', ['tab' => 'ip-rules'])
            ->with('success', "Đã thêm địa chỉ IP {$rule->ip_address} vào {$typeName} thành công.");
    }

    /**
     * Gỡ bỏ quy tắc IP khỏi Tường lửa.
     */
    public function destroyIpRule(int $id, Request $request): RedirectResponse
    {
        $this->securityService->removeIpRule($id, $request->user());

        return redirect()->route('admin.settings.security.index', ['tab' => 'ip-rules'])
            ->with('success', 'Đã gỡ bỏ quy tắc IP khỏi hệ thống an ninh thành công.');
    }

    /**
     * Cập nhật các thông số Rate Limiting toàn nền tảng.
     */
    public function updateRateLimits(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'auth_rate_limit_per_minute' => ['required', 'integer', 'min:3', 'max:60'],
            'rsvp_rate_limit_per_minute' => ['required', 'integer', 'min:5', 'max:120'],
            'wishes_rate_limit_per_minute' => ['required', 'integer', 'min:5', 'max:100'],
            'api_rate_limit_per_minute' => ['required', 'integer', 'min:10', 'max:1000'],
            'strict_rate_limiting' => ['nullable', 'boolean'],
        ]);

        $this->securityService->updateRateLimitConfigs($validated, $request->user());

        return redirect()->route('admin.settings.security.index', ['tab' => 'rate-limits'])
            ->with('success', 'Đã cập nhật các thông số ngưỡng Rate Limiting an toàn thành công.');
    }

    /**
     * Xóa sạch bộ nhớ đệm Rate Limit Cache.
     */
    public function flushCache(Request $request): RedirectResponse
    {
        $this->securityService->flushRateLimitCache();

        return redirect()->route('admin.settings.security.index', ['tab' => 'rate-limits'])
            ->with('success', 'Đã làm sạch bộ đệm Rate Limiter Cache. Mọi giới hạn tạm thời đã được gỡ bỏ cho người dùng.');
    }
}
