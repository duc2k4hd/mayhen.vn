<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Mail\WeddingAnniversaryCongratulationMail;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\WeddingAnniversaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class WeddingAnniversaryController extends Controller
{
    /**
     * Màn hình Studio Cấu Hình & Quản Trị Nhắc Hẹn Kỷ Niệm Ngày Cưới.
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $wedding = $invitation->wedding;

        $config = WeddingAnniversaryService::getConfig($wedding, $invitation);
        $stats = WeddingAnniversaryService::calculateTimelineStats($wedding);
        $loungeData = WeddingAnniversaryService::getAnniversaryLoungeData($invitation);

        AuditLogger::log(
            'ANNIVERSARY_STUDIO_ACCESSED',
            'Invitation',
            $invitation->id,
            "Truy cập Studio Nhắc Hẹn Kỷ Niệm Ngày Cưới thiệp #{$invitation->id}"
        );

        return view('client.pages.account.anniversary.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'config' => $config,
            'stats' => $stats,
            'loungeData' => $loungeData,
        ]);
    }

    /**
     * Lưu cấu hình Nhắc Hẹn Kỷ Niệm Ngày Cưới từ Studio.
     */
    public function update(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $wedding = $invitation->wedding;

        $validated = $request->validate([
            'enable_reminders' => ['nullable', 'boolean'],
            'notification_email_1' => ['required', 'email', 'max:255'],
            'notification_email_2' => ['nullable', 'email', 'max:255'],
            'anniversary_photo_url' => ['nullable', 'string', 'max:1000'],
            'anniversary_reflection_note' => ['nullable', 'string', 'max:1000'],
            'one_year_moments' => ['nullable', 'array'],
            'one_year_moments.*' => ['string', 'max:255'],
        ]);

        WeddingAnniversaryService::updateConfig($wedding, $validated, $invitation, Auth::user());

        return back()->with('success', 'Đã lưu cấu hình Nhắc Hẹn Kỷ Niệm Ngày Cưới thành công.');
    }

    /**
     * Gửi thử email chúc mừng kỷ niệm xem trước tới email của cặp đôi.
     */
    public function testSend(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($invitationId);
        $wedding = $invitation->wedding;

        $config = WeddingAnniversaryService::getConfig($wedding, $invitation);
        $stats = WeddingAnniversaryService::calculateTimelineStats($wedding);

        $targetEmail = $config['notification_email_1'] ?: Auth::user()->email;

        if (! empty($targetEmail)) {
            Mail::to($targetEmail)->send(
                new WeddingAnniversaryCongratulationMail(
                    $wedding,
                    $invitation,
                    max(1, $stats['years_married']),
                    $stats['current_milestone_name']
                )
            );
        }

        AuditLogger::log(
            'ANNIVERSARY_TEST_EMAIL_SENT',
            'Wedding',
            $wedding->id,
            "Gửi email thử nghiệm chúc mừng kỷ niệm ngày cưới tới '{$targetEmail}'"
        );

        return back()->with('success', "Đã gửi email chúc mừng kỷ niệm xem trước tới '{$targetEmail}'. Hãy kiểm tra hộp thư đến của bạn!");
    }

    /**
     * Trang Kỷ Niệm Ngày Cưới (Anniversary Lounge Page) công khai.
     */
    public function lounge(Request $request, string $slug): View
    {
        $invitation = Invitation::with(['wedding.wishes', 'template'])
            ->where('slug', $slug)
            ->firstOrFail();

        $loungeData = WeddingAnniversaryService::getAnniversaryLoungeData($invitation);

        return view('client.pages.anniversary.lounge', $loungeData);
    }

    /**
     * Kiểm tra và phân quyền truy cập thiệp cưới của người dùng hiện tại hoặc Admin.
     */
    protected function resolveAuthorizedInvitation(int $invitationId): Invitation
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);

        if (! $user->isAdmin()) {
            if (! $invitation->wedding || (int) $invitation->wedding->user_id !== (int) $user->id) {
                abort(403, 'Bạn không có quyền quản lý cấu hình kỷ niệm của thiệp cưới này.');
            }
        }

        return $invitation;
    }
}
