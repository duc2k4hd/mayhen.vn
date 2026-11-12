<?php

namespace App\Services;

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\InvitationPersonaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvitationPersonaStudioController extends Controller
{
    public function __construct(
        protected InvitationPersonaService $personaService
    ) {}

    /**
     * Màn hình Studio Cá Nhân Hóa Lời Ngỏ Theo Nhóm Khách (IDEA-A01 & Mục 2.3 DOCX).
     */
    public function index(Request $request, int|string $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $summary = $this->personaService->getPersonaSummary($invitation);

        AuditLogger::log(
            'PERSONA_STUDIO_ACCESSED',
            'Invitation',
            $invitation->id,
            'Truy cập Studio Cá nhân hóa lời ngỏ theo nhóm khách (IDEA-A01)'
        );

        return view('client.pages.account.persona.index', [
            'invitation' => $invitation,
            'wedding' => $summary['wedding'],
            'isEnabled' => $summary['is_enabled'],
            'groups' => $summary['groups'],
            'stats' => $summary['stats'],
            'presets' => $summary['presets'],
        ]);
    }

    /**
     * Cập nhật cấu hình Persona và lời ngỏ các nhóm.
     */
    public function update(Request $request, int|string $invitationId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);

        $validated = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'groups' => ['nullable', 'array'],
            'groups.*.title' => ['nullable', 'string', 'max:255'],
            'groups.*.subtitle' => ['nullable', 'string', 'max:255'],
            'groups.*.content' => ['nullable', 'string', 'max:3000'],
        ]);

        $this->personaService->updatePersonaConfig($invitation, [
            'enabled' => $request->boolean('enabled'),
            'groups' => $validated['groups'] ?? [],
        ]);

        return redirect()
            ->route('account.persona.index', $invitation->id)
            ->with('status', 'Đã lưu cấu hình lời ngỏ theo nhóm thành công!');
    }

    /**
     * Đặt lại một nhóm về thông điệp mặc định.
     */
    public function resetGroup(Request $request, int|string $invitationId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $groupName = (string) $request->input('group_name');

        if ($groupName !== '') {
            $this->personaService->resetGroup($invitation, $groupName);
        }

        return redirect()
            ->route('account.persona.index', $invitation->id)
            ->with('status', "Đã đặt lại lời ngỏ của nhóm '{$groupName}' về mặc định.");
    }

    /**
     * Phân giải quyền sở hữu thiệp mời an toàn, chống IDOR (404 Not Found).
     */
    protected function resolveAuthorizedInvitation(Request $request, int|string $id): Invitation
    {
        $user = $request->user();
        abort_unless($user, 401, 'Vui lòng đăng nhập để tiếp tục.');

        $invitation = Invitation::with(['wedding', 'template'])->where('id', $id)->first();
        abort_unless($invitation, 404, 'Không tìm thấy thiệp mời.');

        $isOwner = ((int) $invitation->user_id === (int) $user->id)
            || ($invitation->wedding && (int) $invitation->wedding->user_id === (int) $user->id);

        abort_unless($isOwner, 403, 'Bạn không có quyền quản trị thiệp mời này.');

        return $invitation;
    }
}

