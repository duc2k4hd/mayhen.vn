<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\QualityDoctorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QualityDoctorController extends Controller
{
    public function __construct(
        protected QualityDoctorService $doctorService
    ) {}

    /**
     * Màn hình Studio Bác Sĩ Chất Lượng Thiệp & Diễn Tập.
     */
    public function index(Request $request, int|string $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $diagnosis = $this->doctorService->diagnose($invitation);

        AuditLogger::log(
            'QUALITY_DOCTOR_INSPECTED',
            'Invitation',
            $invitation->id,
            'Đã rà soát sức khỏe thiệp cưới qua Bác Sĩ Chất Lượng (Điểm: '.$diagnosis['kpis']['health_score'].'/100)'
        );

        return view('client.pages.account.quality_doctor.index', [
            'invitation' => $invitation,
            'wedding' => $diagnosis['wedding'],
            'kpis' => $diagnosis['kpis'],
            'checks' => $diagnosis['checks'],
            'rehearsalUrl' => $diagnosis['rehearsal_url'],
        ]);
    }

    /**
     * Quét lại chẩn đoán chất lượng thiệp qua AJAX.
     */
    public function rescan(Request $request, int|string $invitationId): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $diagnosis = $this->doctorService->diagnose($invitation);

        return response()->json([
            'success' => true,
            'kpis' => $diagnosis['kpis'],
            'checks' => $diagnosis['checks'],
            'rehearsal_url' => $diagnosis['rehearsal_url'],
            'message' => 'Đã quét lại chất lượng thiệp thành công.',
        ]);
    }

    /**
     * Tạo khách mời diễn tập mẫu để dâu rể gửi thử thiệp test.
     */
    public function simulate(Request $request, int|string $invitationId): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $user = $request->user();

        $testGuest = $this->doctorService->ensureRehearsalGuest($invitation, $user);
        $personalRehearsalUrl = route('invitation.show', [
            'slug' => $invitation->slug,
            'guest' => $testGuest->guest_token,
        ]).'?mode=rehearsal&preview_vip=true';

        AuditLogger::log(
            'REHEARSAL_GUEST_CREATED',
            'Invitation',
            $invitation->id,
            'Đã tạo khách mời diễn tập thử nghiệm: '.$testGuest->name.' (Token: '.$testGuest->guest_token.')'
        );

        return response()->json([
            'success' => true,
            'guest' => [
                'id' => $testGuest->id,
                'name' => $testGuest->name,
                'salutation' => $testGuest->salutation,
                'guest_token' => $testGuest->guest_token,
            ],
            'rehearsal_url' => $personalRehearsalUrl,
            'message' => 'Đã tạo liên kết diễn tập cá nhân hóa thành công!',
        ]);
    }

    /**
     * Kiểm tra quyền sở hữu thiệp mời.
     */
    protected function resolveAuthorizedInvitation(Request $request, int|string $invitationId): Invitation
    {
        $user = $request->user();

        $invitation = Invitation::with('wedding')->findOrFail($invitationId);

        if (! $invitation->wedding || ($invitation->wedding->user_id !== $user->id && ! ($user->is_admin ?? false))) {
            abort(403, 'Bạn không có quyền truy cập thiệp cưới này.');
        }

        return $invitation;
    }
}
