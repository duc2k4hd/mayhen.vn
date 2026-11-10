<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Plan;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Danh sách gói cước và đăng ký (Wave 06 - ADM-A10).
     */
    public function index(Request $request): View
    {
        $plans = Plan::withCount([
            'orders as total_orders_count',
            'orders as paid_orders_count' => fn ($q) => $q->where('status', 'paid'),
        ])->orderBy('price', 'asc')->get();

        $stats = [
            'total_plans' => $plans->count(),
            'active_plans' => $plans->where('is_active', true)->count(),
            'total_paid_orders' => Order::where('status', 'paid')->count(),
            'total_revenue' => Order::where('status', 'paid')->sum('amount'),
        ];

        return view('admin.pages.plans.index', compact('plans', 'stats'));
    }

    /**
     * Màn hình chỉnh sửa gói cước.
     */
    public function edit(Plan $plan): View
    {
        $plan->loadCount([
            'orders as paid_orders_count' => fn ($q) => $q->where('status', 'paid'),
        ]);

        return view('admin.pages.plans.edit', compact('plan'));
    }

    /**
     * Cập nhật thông tin gói cước và ghi audit log (Bảo toàn snapshot cho đơn cũ).
     */
    public function update(Request $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'price' => 'required|integer|min:0',
            'duration_days' => 'required|integer|min:0',
            'max_invitations' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Tên gói cước không được để trống.',
            'price.required' => 'Giá gói cước không được để trống.',
            'price.min' => 'Giá gói cước không được nhỏ hơn 0đ.',
            'max_invitations.min' => 'Số lượng thiệp tối đa phải từ 1 trở lên.',
        ]);

        $oldPrice = $plan->price;
        $oldName = $plan->name;

        // Lọc bỏ feature rỗng
        $features = array_values(array_filter($request->input('features', []), fn ($f) => filled(trim((string) $f))));

        $plan->update([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'duration_days' => $validated['duration_days'],
            'max_invitations' => $validated['max_invitations'],
            'features' => $features,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogger::log(
            'plan_updated',
            'Plan',
            $plan->id,
            "Cập nhật gói cước [{$plan->name}] (Giá cũ: ".number_format($oldPrice, 0, ',', '.').'đ -> Mới: '.number_format($plan->price, 0, ',', '.').'đ)',
            [
                'plan_id' => $plan->id,
                'slug' => $plan->slug,
                'old_name' => $oldName,
                'new_name' => $plan->name,
                'old_price' => $oldPrice,
                'new_price' => $plan->price,
                'is_active' => $plan->is_active,
            ]
        );

        return redirect()->route('admin.plans.index')
            ->with('success', "Gói cước \"{$plan->name}\" đã được cập nhật thành công.");
    }

    /**
     * Bật/Tắt trạng thái hoạt động của gói cước.
     */
    public function toggleActive(Plan $plan): RedirectResponse
    {
        $newStatus = ! $plan->is_active;
        $plan->update(['is_active' => $newStatus]);

        AuditLogger::log(
            'plan_status_toggled',
            'Plan',
            $plan->id,
            ($newStatus ? 'Kích hoạt' : 'Tạm dừng')." gói cước [{$plan->name}]",
            ['plan_id' => $plan->id, 'slug' => $plan->slug, 'is_active' => $newStatus]
        );

        $statusText = $newStatus ? 'kích hoạt' : 'tạm dừng';

        return back()->with('success', "Đã {$statusText} gói cước \"{$plan->name}\".");
    }
}
