<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Plan;
use App\Services\InvitationLifecycleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvitationRenewalController extends Controller
{
    public function __construct(
        protected InvitationLifecycleService $lifecycleService
    ) {}

    /**
     * Lấy thông tin trạng thái vòng đời hạn dùng và danh sách gói gia hạn của thiệp cưới.
     */
    public function lifecycle(int $id): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        abort_unless($invitation->isOwner(), 403, 'Bạn không có quyền quản lý thiệp cưới này.');

        $statusData = $this->lifecycleService->getLifecycleStatus($invitation);

        return response()->json([
            'success' => true,
            'invitation_id' => $invitation->id,
            'slug' => $invitation->slug,
            'title' => $invitation->title,
            'lifecycle' => $statusData,
        ]);
    }

    /**
     * Khởi tạo yêu cầu gia hạn hoặc chuyển hướng đến cổng thanh toán VietQR.
     */
    public function initiateRenewal(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'integer', 'exists:plans,id'],
        ], [
            'plan_id.required' => 'Vui lòng chọn gói gia hạn mong muốn.',
            'plan_id.exists' => 'Gói dịch vụ không tồn tại trên hệ thống.',
        ]);

        $invitation = Invitation::with('wedding')->findOrFail($id);
        abort_unless($invitation->isOwner(), 403, 'Bạn không có quyền gia hạn thiệp cưới này.');

        $plan = Plan::where('is_active', true)->findOrFail($validated['plan_id']);

        // Tạo URL thanh toán kèm liên kết invitation_id
        $checkoutUrl = route('checkout.show', [
            'planSlug' => $plan->slug,
            'invitation_id' => $invitation->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Khởi tạo yêu cầu gia hạn gói {$plan->name} thành công.",
            'plan_name' => $plan->name,
            'checkout_url' => $checkoutUrl,
        ]);
    }
}
