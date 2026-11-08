<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\AuditLogger;
use App\Services\CsvSanitizerService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingController extends Controller
{
    /**
     * Path to local banking configuration file.
     */
    protected string $bankingConfigPath;

    public function __construct()
    {
        $this->bankingConfigPath = storage_path('app/settings/banking.json');
    }

    /**
     * Display service plans management.
     */
    public function plans(): View
    {
        $plans = Plan::query()
            ->withCount('orders')
            ->withSum(['orders as paid_revenue' => function ($q) {
                $q->where('status', 'paid');
            }], 'amount')
            ->orderBy('price')
            ->get();

        return view('admin.pages.settings.plans.index', [
            'plans' => $plans,
        ]);
    }

    /**
     * Show the form for editing the specified plan.
     */
    public function editPlan(int $id): View
    {
        $plan = Plan::findOrFail($id);

        $availableFeatures = [
            'Nhạc nền đám cưới độc quyền',
            'Sổ lưu bút & Lời chúc mừng',
            'Xác nhận tham dự RSVP thời gian thực',
            'Bản đồ chỉ đường Google Maps & Apple Maps',
            'Album ảnh cưới chất lượng cao (10 ảnh)',
            'Mã VietQR mừng cưới thông minh',
            'Đếm ngược ngày cưới & Lịch sự kiện',
            'Không giới hạn lượt truy cập thiệp',
            'Tùy biến phong cách & Phông chữ tự do',
        ];

        return view('admin.pages.settings.plans.edit', [
            'plan' => $plan,
            'availableFeatures' => $availableFeatures,
        ]);
    }

    /**
     * Update the specified plan in storage.
     */
    public function updatePlan(Request $request, int $id): RedirectResponse
    {
        $plan = Plan::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'integer', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'max_invitations' => ['required', 'integer', 'min:1'],
            'features' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $plan->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'duration_days' => $validated['duration_days'],
            'max_invitations' => $validated['max_invitations'],
            'features' => $validated['features'] ?? [],
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogger::log(
            'update_plan',
            'Plan',
            $plan->id,
            "Cập nhật thông tin gói cước \"{$plan->name}\" (Giá: ".number_format($plan->price, 0, ',', '.').'đ)',
            ['plan_id' => $plan->id, 'price' => $plan->price, 'duration_days' => $plan->duration_days]
        );

        return redirect()->route('admin.settings.plans')
            ->with('success', "Đã cập nhật thông tin gói dịch vụ \"{$plan->name}\" thành công.");
    }

    /**
     * Toggle active status of a service plan.
     */
    public function togglePlanActive(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $plan = Plan::findOrFail($id);
        $plan->update(['is_active' => ! $plan->is_active]);

        $message = $plan->is_active
            ? "Gói dịch vụ \"{$plan->name}\" đã được kích hoạt mở bán."
            : "Gói dịch vụ \"{$plan->name}\" đã được tạm dừng mở bán.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $plan->is_active,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Display bank transfer & VietQR gateway settings.
     */
    public function banking(): View
    {
        $bankingConfig = $this->getBankingConfig();
        $supportedBanks = config('banks.supported', []);

        return view('admin.pages.settings.banking', [
            'bankingConfig' => $bankingConfig,
            'supportedBanks' => $supportedBanks,
        ]);
    }

    /**
     * Update bank transfer & VietQR gateway settings.
     */
    public function updateBanking(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'bank_code' => ['required', 'string', 'max:50'],
            'bank_name' => ['required', 'string', 'max:150'],
            'bank_account' => ['required', 'string', 'max:50'],
            'bank_account_name' => ['required', 'string', 'max:150'],
            'webhook_secret' => ['nullable', 'string', 'max:150'],
            'qr_template' => ['nullable', 'string', 'max:50'],
        ]);

        $dir = dirname($this->bankingConfigPath);
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        File::put(
            $this->bankingConfigPath,
            json_encode($validated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        AuditLogger::log(
            'update_banking',
            'Banking',
            'gateway',
            "Cập nhật tài khoản thụ hưởng VietQR: {$validated['bank_name']} - {$validated['bank_account']} ({$validated['bank_account_name']})",
            ['bank_code' => $validated['bank_code'], 'bank_account' => $validated['bank_account']]
        );

        return back()->with('success', 'Đã lưu cấu hình tài khoản ngân hàng & VietQR thành công.');
    }

    /**
     * Inspect public asset storage usage and metrics.
     */
    public function storageInspector(): View
    {
        $publicDisk = Storage::disk('public');
        $allFiles = $publicDisk->allFiles();

        $totalBytes = 0;
        $categoryStats = [
            'weddings' => ['count' => 0, 'bytes' => 0],
            'client' => ['count' => 0, 'bytes' => 0],
            'admin' => ['count' => 0, 'bytes' => 0],
            'other' => ['count' => 0, 'bytes' => 0],
        ];

        $recentFiles = [];

        foreach ($allFiles as $file) {
            $size = $publicDisk->size($file);
            $totalBytes += $size;

            if (str_starts_with($file, 'weddings/')) {
                $categoryStats['weddings']['count']++;
                $categoryStats['weddings']['bytes'] += $size;
            } elseif (str_starts_with($file, 'client/')) {
                $categoryStats['client']['count']++;
                $categoryStats['client']['bytes'] += $size;
            } elseif (str_starts_with($file, 'admin/')) {
                $categoryStats['admin']['count']++;
                $categoryStats['admin']['bytes'] += $size;
            } else {
                $categoryStats['other']['count']++;
                $categoryStats['other']['bytes'] += $size;
            }

            // Keep track of recent files
            if (count($recentFiles) < 20) {
                $recentFiles[] = [
                    'path' => $file,
                    'size' => $size,
                    'url' => asset('storage/'.$file),
                    'last_modified' => $publicDisk->lastModified($file),
                ];
            }
        }

        // Sort recent files by modified time desc
        usort($recentFiles, function ($a, $b) {
            return $b['last_modified'] <=> $a['last_modified'];
        });

        return view('admin.pages.settings.storage', [
            'totalFiles' => count($allFiles),
            'totalBytes' => $totalBytes,
            'categoryStats' => $categoryStats,
            'recentFiles' => $recentFiles,
        ]);
    }

    /**
     * Get banking configuration with fallback to services config.
     *
     * @return array<string, mixed>
     */
    protected function getBankingConfig(): array
    {
        if (File::exists($this->bankingConfigPath)) {
            $content = File::get($this->bankingConfigPath);
            $data = json_decode($content, true);
            if (is_array($data)) {
                return $data;
            }
        }

        return [
            'bank_code' => config('services.sepay.bank_code', 'ICB'),
            'bank_name' => config('services.sepay.bank_name', 'VietinBank'),
            'bank_account' => config('services.sepay.bank_account', '103870000000'),
            'bank_account_name' => config('services.sepay.bank_account_name', 'MAY HEN ATELIER'),
            'webhook_secret' => config('services.sepay.webhook_secret', ''),
            'qr_template' => 'compact',
        ];
    }

    /**
     * Display centralized administrative audit logs.
     */
    public function auditLogs(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $action = $request->input('action');

        $logs = AuditLogger::getRecentLogs(100, $action ?: null, $search ?: null);

        $availableActions = [
            'approve_order' => 'Duyệt Đơn Hàng',
            'cancel_order' => 'Hủy Đơn Hàng',
            'lock_invitation' => 'Khóa Thiệp Cưới',
            'unlock_invitation' => 'Mở Khóa Thiệp Cưới',
            'toggle_publish' => 'Bật/Ẩn Thiệp',
            'extend_invitation' => 'Gia Hạn Thiệp',
            'migrate_template' => 'Đổi Mẫu Thiệp',
            'change_user_role' => 'Phân Quyền User',
            'reset_user_password' => 'Đặt Lại Mật Khẩu',
            'scan_templates' => 'Quét Mẫu Mới',
            'update_template' => 'Cập Nhật Mẫu Thiệp',
            'toggle_template_active' => 'Bật/Tắt Mẫu Thiệp',
            'toggle_template_featured' => 'Ghim Mẫu Nổi Bật',
            'toggle_wish_approval' => 'Duyệt/Ẩn Lời Chúc',
            'delete_wish' => 'Xóa Lời Chúc',
            'update_plan' => 'Cập Nhật Gói Cước',
            'update_banking' => 'Cập Nhật Ngân Hàng',
        ];

        return view('admin.pages.settings.audit_logs', [
            'logs' => $logs,
            'currentSearch' => $search,
            'currentAction' => $action,
            'availableActions' => $availableActions,
        ]);
    }

    /**
     * Export administrative audit logs as UTF-8 BOM CSV.
     */
    public function exportAuditLogs(Request $request): StreamedResponse
    {
        $search = trim((string) $request->input('q', ''));
        $action = $request->input('action');

        $logs = AuditLogger::getRecentLogs(2000, $action ?: null, $search ?: null);
        $filename = 'nhat-ky-quan-tri-mayhen-'.now()->format('Ymd-His').'.csv';

        $availableActions = [
            'approve_order' => 'Duyệt Đơn Hàng',
            'cancel_order' => 'Hủy Đơn Hàng',
            'lock_invitation' => 'Khóa Thiệp Cưới',
            'unlock_invitation' => 'Mở Khóa Thiệp Cưới',
            'toggle_publish' => 'Bật/Ẩn Thiệp',
            'extend_invitation' => 'Gia Hạn Thiệp',
            'migrate_template' => 'Đổi Mẫu Thiệp',
            'change_user_role' => 'Phân Quyền User',
            'reset_user_password' => 'Đặt Lại Mật Khẩu',
            'scan_templates' => 'Quét Mẫu Mới',
            'update_template' => 'Cập Nhật Mẫu Thiệp',
            'toggle_template_active' => 'Bật/Tắt Mẫu Thiệp',
            'toggle_template_featured' => 'Ghim Mẫu Nổi Bật',
            'toggle_wish_approval' => 'Duyệt/Ẩn Lời Chúc',
            'delete_wish' => 'Xóa Lời Chúc',
            'update_plan' => 'Cập Nhật Gói Cước',
            'update_banking' => 'Cập Nhật Ngân Hàng',
        ];

        return response()->streamDownload(function () use ($logs, $availableActions) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'STT',
                'Thời Gian',
                'Quản Trị Viên',
                'Email',
                'Hành Động',
                'Đối Tượng Tác Động',
                'Mã Đối Tượng',
                'Nội Dung Chi Tiết',
                'Địa Chỉ IP',
            ]);

            $index = 1;
            foreach ($logs as $log) {
                $actionLabel = $availableActions[$log['action'] ?? ''] ?? ($log['action'] ?? 'Thao tác');
                $time = isset($log['timestamp']) ? Carbon::parse($log['timestamp'])->format('d/m/Y H:i:s') : 'N/A';

                fputcsv($handle, CsvSanitizerService::row([
                    $index++,
                    $time,
                    $log['admin_name'] ?? 'System',
                    $log['admin_email'] ?? 'N/A',
                    $actionLabel,
                    $log['target_type'] ?? 'Hệ thống',
                    $log['target_id'] ?? '',
                    $log['description'] ?? '',
                    $log['ip'] ?? '',
                ]));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
