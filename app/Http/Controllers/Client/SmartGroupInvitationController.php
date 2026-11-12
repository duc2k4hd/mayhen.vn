<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\SmartGroupInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SmartGroupInvitationController extends Controller
{
    /**
     * Xác thực quyền sở hữu thiệp cưới của người dùng đang đăng nhập.
     */
    protected function authorizeInvitationOwner(Invitation $invitation): void
    {
        $user = Auth::user();
        if (! $user || ! $invitation->wedding || $invitation->wedding->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền thao tác trên thiệp cưới này.');
        }
    }

    /**
     * Màn hình Studio cấu hình thiệp cưới thông minh theo nhóm khách (Mục 2.3 DOCX).
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding.events', 'wedding.guests'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $wedding = $invitation->wedding;
        $presetGroups = SmartGroupInvitationService::getPresetGroups();

        // Lấy danh sách các nhóm thực tế từ danh bạ khách mời
        $existingGroupNames = $wedding->guests()
            ->whereNotNull('group_name')
            ->where('group_name', '!=', '')
            ->distinct()
            ->pluck('group_name')
            ->all();

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $smartGroupsConfig = $config['smart_groups'] ?? [];

        $events = $wedding->events()->where('is_active', true)->orderBy('display_order')->get();

        return view('client.pages.account.smart_groups.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'presetGroups' => $presetGroups,
            'existingGroupNames' => $existingGroupNames,
            'smartGroupsConfig' => $smartGroupsConfig,
            'events' => $events,
        ]);
    }

    /**
     * Lưu cấu hình phân tuyến sự kiện và lời mời theo nhóm khách.
     */
    public function update(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'smart_groups' => ['nullable', 'array'],
            'smart_groups.*.greeting' => ['nullable', 'string', 'max:1000'],
            'smart_groups.*.title' => ['nullable', 'string', 'max:255'],
            'smart_groups.*.allowed_event_ids' => ['nullable', 'array'],
            'smart_groups.*.allowed_event_ids.*' => ['integer'],
        ]);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $config['smart_groups'] = $validated['smart_groups'] ?? [];
        $invitation->custom_config = $config;
        $invitation->save();

        AuditLogger::log(
            'SMART_GROUPS_CONFIG_UPDATED',
            'Invitation',
            $invitation->id,
            "Cập nhật cấu hình phân tuyến thiệp theo nhóm khách cho thiệp #{$invitation->id}"
        );

        return redirect()->route('account.smart_groups.index', $invitation->id)
            ->with('success', 'Đã lưu cấu hình thiệp thông minh theo nhóm khách thành công!');
    }
}
