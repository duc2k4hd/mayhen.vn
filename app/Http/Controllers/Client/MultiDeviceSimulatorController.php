<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\MultiDeviceSimulatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MultiDeviceSimulatorController extends Controller
{
    public function __construct(
        protected MultiDeviceSimulatorService $simulatorService
    ) {}

    /**
     * Màn hình Studio Soi Đa Thiết Bị Chuẩn Xác 100% (IDEA-E10).
     */
    public function index(Request $request, int|string $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);

        $simulatorData = $this->simulatorService->getSimulatorData($invitation, $request);

        AuditLogger::log(
            'DEVICE_SIMULATOR_ACCESSED',
            'Invitation',
            $invitation->id,
            "Cặp đôi truy cập Studio Soi Đa Thiết Bị cho thiệp #{$invitation->id} (thiết bị: {$simulatorData['activeDevice']['name']})"
        );

        return view('client.pages.account.device_simulator.index', $simulatorData);
    }

    /**
     * Xác thực quyền sở hữu thiệp cưới của người dùng đăng nhập an toàn chống IDOR.
     */
    protected function resolveAuthorizedInvitation(Request $request, int|string $id): Invitation
    {
        $user = Auth::user();
        if (! $user) {
            abort(401, 'Vui lòng đăng nhập để thực hiện thao tác này.');
        }

        $invitation = Invitation::with(['wedding', 'template'])
            ->where('id', $id)
            ->firstOrFail();

        if ((int) $invitation->user_id !== (int) $user->id && ((int) ($invitation->wedding?->user_id ?? 0) !== (int) $user->id)) {
            abort(403, 'Bạn không có quyền truy cập hoặc chỉnh sửa thiệp cưới này.');
        }

        return $invitation;
    }
}
