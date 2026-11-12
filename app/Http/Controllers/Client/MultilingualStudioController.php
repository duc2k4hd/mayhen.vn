<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\MultilingualInvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MultilingualStudioController extends Controller
{
    /**
     * Màn hình Studio Quản Trị Đa Ngôn Ngữ & Bản Dịch Thiệp Cưới.
     */
    public function index(Request $request, int|string $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $i18nConfig = $config['i18n'] ?? [];
        $enabledLocales = $i18nConfig['enabled_locales'] ?? ['vi', 'en'];
        $defaultLocale = $i18nConfig['default_locale'] ?? 'vi';
        $overrides = $i18nConfig['overrides'] ?? [];

        $allLocales = MultilingualInvitationService::SUPPORTED_LOCALES;

        // Nhóm các cụm từ theo từng Section thiệp cưới để biên tập trực quan
        $sectionGroups = [
            'hero' => [
                'title' => 'Phần 2: Bìa Thiệp & Lời Chào (Hero Cover)',
                'keys' => [
                    'hero.wedding_of' => 'Hôn Lễ Của',
                    'hero.cordially_invite' => 'Lời mời trang trọng',
                    'hero.groom_side_title' => 'Tiêu đề Nhà Trai',
                    'hero.bride_side_title' => 'Tiêu đề Nhà Gái',
                ],
            ],
            'story' => [
                'title' => 'Phần 3: Chuyện Tình & Lời Ngỏ (Love Story & Quote)',
                'keys' => [
                    'story.title' => 'Tiêu đề câu chuyện',
                    'story.subtitle' => 'Phụ đề chuyện tình',
                    'story.quote_default' => 'Lời ngỏ / Danh ngôn tình yêu',
                ],
            ],
            'family' => [
                'title' => 'Phần 4: Đôi Uyên Ương & Gia Đình (Family & Couple)',
                'keys' => [
                    'family.title' => 'Tiêu đề gia đình',
                    'family.groom_side' => 'Nhà Trai',
                    'family.bride_side' => 'Nhà Gái',
                    'family.parents' => 'Phụ mẫu hai bên',
                ],
            ],
            'events' => [
                'title' => 'Phần 6: Sự Kiện & Địa Điểm (Events & Maps)',
                'keys' => [
                    'events.title' => 'Tiêu đề sự kiện',
                    'events.ceremony' => 'Nghi thức Lễ Cưới',
                    'events.banquet' => 'Tiệc Chiêu Đãi',
                    'events.venue' => 'Sảnh tiệc / Địa điểm',
                ],
            ],
            'dresscode' => [
                'title' => 'Phần 8: Quy Chuẩn Trang Phục (Dress Code)',
                'keys' => [
                    'dresscode.title' => 'Tiêu đề trang phục',
                    'dresscode.note' => 'Ghi chú dresscode lịch thiệp',
                ],
            ],
            'gift' => [
                'title' => 'Phần 10: Mừng Cưới & Chúc Phúc (Gift & RSVP)',
                'keys' => [
                    'gift.title' => 'Tiêu đề hộp mừng cưới',
                    'gift.subtitle' => 'Phụ đề mừng cưới',
                    'rsvp.title' => 'Tiêu đề xác nhận tham dự',
                    'rsvp.subtitle' => 'Phụ đề hạn chót RSVP',
                ],
            ],
        ];

        // Lấy từ điển mẫu của từng ngôn ngữ phục vụ gợi ý placeholder
        $dictionaries = [];
        foreach (array_keys($allLocales) as $code) {
            $dictionaries[$code] = MultilingualInvitationService::getDictionary($code, null);
        }

        AuditLogger::log(
            'MULTILINGUAL_STUDIO_ACCESSED',
            'Invitation',
            $invitation->id,
            'Xem cấu hình đa ngôn ngữ thiệp cưới: '.implode(', ', $enabledLocales)
        );

        return view('client.pages.account.multilingual.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'allLocales' => $allLocales,
            'enabledLocales' => $enabledLocales,
            'defaultLocale' => $defaultLocale,
            'overrides' => $overrides,
            'sectionGroups' => $sectionGroups,
            'dictionaries' => $dictionaries,
        ]);
    }

    /**
     * Cập nhật cấu hình ngôn ngữ và bản dịch tùy biến.
     */
    public function update(Request $request, int|string $invitationId): JsonResponse|RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);

        $validated = $request->validate([
            'enabled_locales' => 'required|array|min:1',
            'enabled_locales.*' => 'string|in:vi,en,ko,zh',
            'default_locale' => 'required|string|in:vi,en,ko,zh',
            'overrides' => 'nullable|array',
        ]);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        // Luôn đảm bảo có tiếng Việt
        $enabled = $validated['enabled_locales'];
        if (! in_array('vi', $enabled, true)) {
            $enabled[] = 'vi';
        }

        // Lọc bỏ các override rỗng
        $cleanOverrides = [];
        if (! empty($validated['overrides']) && is_array($validated['overrides'])) {
            foreach ($validated['overrides'] as $locale => $keys) {
                if (is_array($keys)) {
                    foreach ($keys as $key => $val) {
                        $trimmed = trim((string) $val);
                        if ($trimmed !== '') {
                            $cleanOverrides[$locale][$key] = strip_tags($trimmed);
                        }
                    }
                }
            }
        }

        $config['i18n'] = [
            'enabled_locales' => array_values(array_unique($enabled)),
            'default_locale' => $validated['default_locale'],
            'overrides' => $cleanOverrides,
        ];

        $invitation->custom_config = $config;
        $invitation->save();

        AuditLogger::log(
            'MULTILINGUAL_SETTINGS_UPDATED',
            'Invitation',
            $invitation->id,
            'Cập nhật ngôn ngữ: '.implode(', ', $enabled)." (Mặc định: {$validated['default_locale']})"
        );

        if (! $request->expectsJson()) {
            return redirect()->route('account.multilingual.index', $invitation->id)
                ->with('success', 'Đã lưu cấu hình đa ngôn ngữ và bản dịch thành công!');
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu cấu hình đa ngôn ngữ và bản dịch thành công!',
            'config' => $config['i18n'],
        ]);
    }

    /**
     * Đưa bản dịch tùy biến về mặc định của hệ thống Mây Hẹn.
     */
    public function reset(Request $request, int|string $invitationId): JsonResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        if (isset($config['i18n'])) {
            $config['i18n']['overrides'] = [];
            $invitation->custom_config = $config;
            $invitation->save();
        }

        AuditLogger::log(
            'MULTILINGUAL_OVERRIDES_RESET',
            'Invitation',
            $invitation->id,
            'Đã đưa toàn bộ bản dịch tùy biến về mặc định từ điển Mây Hẹn'
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã đặt lại bản dịch về từ điển mặc định.',
        ]);
    }

    /**
     * Xác thực quyền sở hữu thiệp cưới.
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
