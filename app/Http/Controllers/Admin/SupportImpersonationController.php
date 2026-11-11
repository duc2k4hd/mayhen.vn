<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SupportImpersonationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupportImpersonationController extends Controller
{
    public function __construct(
        protected SupportImpersonationService $impersonationService
    ) {}

    /**
     * Bắt đầu phiên hỗ trợ xem dưới góc nhìn khách hàng (Mục 17.2 PRD V3).
     */
    public function impersonate(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:255',
            'ticket_code' => 'nullable|string|max:50',
        ], [
            'reason.required' => 'Vui lòng cung cấp lý do hỗ trợ khách hàng để lưu nhật ký kiểm toán.',
            'reason.min' => 'Lý do hỗ trợ phải có ít nhất 5 ký tự.',
        ]);

        $admin = auth()->user();
        if (! $admin) {
            abort(401, 'Chưa đăng nhập quản trị viên.');
        }

        $customer = User::findOrFail($id);

        try {
            $this->impersonationService->startImpersonation(
                $admin,
                $customer,
                $validated['reason'],
                $validated['ticket_code'] ?? null
            );

            return redirect()->route('account.dashboard')
                ->with('success', "Đã kích hoạt chế độ hỗ trợ an toàn dưới góc nhìn của khách hàng: {$customer->name} ({$customer->email}).");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Rời khỏi chế độ hỗ trợ và đưa Quản trị viên trở về Admin.
     */
    public function leave(Request $request): RedirectResponse
    {
        $admin = $this->impersonationService->stopImpersonation();

        if ($admin) {
            return redirect()->route('admin.users.index')
                ->with('success', 'Đã kết thúc phiên hỗ trợ khách hàng an toàn và trở về trang Quản trị viên.');
        }

        return redirect()->route('login');
    }

    /**
     * API kiểm tra thời gian phiên hỗ trợ còn lại.
     */
    public function status()
    {
        $session = $this->impersonationService->getCurrentSession();

        return response()->json([
            'is_impersonating' => (bool) $session,
            'session' => $session,
        ]);
    }
}
