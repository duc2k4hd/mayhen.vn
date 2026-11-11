<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\CeremonyForkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CeremonyForkController extends Controller
{
    /**
     * Kiểm tra quyền sở hữu thiệp cưới của người dùng hiện tại.
     */
    protected function authorizeInvitationOwner(Invitation $invitation): void
    {
        $user = Auth::user();
        if (! $user || ! $invitation->wedding || $invitation->wedding->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền thao tác trên thiệp cưới này.');
        }
    }

    /**
     * Lấy danh sách tùy chọn và dữ liệu nguồn để phục vụ Modal nhân bản lễ cưới.
     */
    public function options(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $wedding = $invitation->wedding;
        $modes = CeremonyForkService::getForkModes();

        $extra = is_array($wedding->extra_data)
            ? $wedding->extra_data
            : (json_decode($wedding->extra_data ?? '[]', true) ?: []);

        return response()->json([
            'success' => true,
            'invitation' => [
                'id' => $invitation->id,
                'slug' => $invitation->slug,
                'title' => $invitation->title,
            ],
            'wedding' => [
                'id' => $wedding->id,
                'groom_name' => $wedding->groom_name,
                'bride_name' => $wedding->bride_name,
                'wedding_date' => $wedding->wedding_date ? $wedding->wedding_date->format('Y-m-d') : null,
                'venue_name' => $wedding->venue_name,
                'venue_address' => $wedding->venue_address,
                'extra_data' => $extra,
            ],
            'modes' => array_values($modes),
        ]);
    }

    /**
     * Thực hiện nhân bản và phân nhánh thiệp cưới theo văn hóa cưới Việt Nam.
     */
    public function fork(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'mode' => ['required', 'string', 'in:bride_side,groom_side,party_side,backup_copy'],
            'separate_project' => ['nullable', 'boolean'],
            'slug_suffix' => ['nullable', 'string', 'max:50'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'venue_address' => ['nullable', 'string', 'max:255'],
            'wedding_date' => ['nullable', 'date'],
        ]);

        $validated['separate_project'] = $request->boolean('separate_project');

        $result = CeremonyForkService::fork($invitation->wedding, $invitation, $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'invitation' => [
                    'id' => $result['invitation']->id,
                    'slug' => $result['slug'],
                    'title' => $result['invitation']->title,
                ],
                'redirect_url' => route('account.invitations'),
                'editor_url' => route('invitation.create', ['edit' => $result['slug']]),
            ]);
        }

        return redirect()->route('account.invitations')
            ->with('success', $result['message']);
    }
}

