<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\MultilingualInvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MultilingualInvitationController extends Controller
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
     * Màn hình cấu hình Đa Ngôn Ngữ thiệp cưới (Mục 2.10 DOCX).
     */
    public function index(Request $request, int $id): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $this->authorizeInvitationOwner($invitation);

        $supportedLocales = MultilingualInvitationService::SUPPORTED_LOCALES;

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $i18nConfig = $config['i18n'] ?? [
            'enabled_locales' => ['vi', 'en'],
            'default_locale' => 'vi',
            'overrides' => [],
        ];

        if (! in_array('vi', $i18nConfig['enabled_locales'] ?? [], true)) {
            $i18nConfig['enabled_locales'][] = 'vi';
        }

        // Tạo danh sách liên kết theo từng ngôn ngữ để chia sẻ cho khách quốc tế
        $localizedLinks = [];
        foreach ($supportedLocales as $code => $data) {
            $localizedLinks[$code] = [
                'code' => $code,
                'name' => $data['name'],
                'native_name' => $data['native_name'],
                'flag' => $data['flag'],
                'is_enabled' => in_array($code, $i18nConfig['enabled_locales'], true),
                'is_default' => ($code === ($i18nConfig['default_locale'] ?? 'vi')),
                'url' => url("/thiep/{$invitation->slug}?lang={$code}"),
            ];
        }

        return view('client.pages.account.multilingual.index', [
            'invitation' => $invitation,
            'wedding' => $invitation->wedding,
            'supportedLocales' => $supportedLocales,
            'i18nConfig' => $i18nConfig,
            'localizedLinks' => $localizedLinks,
            'dictionaryVi' => MultilingualInvitationService::getDictionary('vi', $invitation),
            'dictionaryEn' => MultilingualInvitationService::getDictionary('en', $invitation),
        ]);
    }

    /**
     * Lưu cấu hình ngôn ngữ hỗ trợ và bản dịch tùy biến.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'enabled_locales' => ['required', 'array', 'min:1'],
            'enabled_locales.*' => ['string', 'in:vi,en,ko,zh'],
            'default_locale' => ['required', 'string', 'in:vi,en,ko,zh'],
            'overrides' => ['nullable', 'array'],
        ]);

        $enabledLocales = $validated['enabled_locales'];
        if (! in_array('vi', $enabledLocales, true)) {
            $enabledLocales[] = 'vi';
        }

        $defaultLocale = $validated['default_locale'];
        if (! in_array($defaultLocale, $enabledLocales, true)) {
            $defaultLocale = 'vi';
        }

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $config['i18n'] = [
            'enabled_locales' => array_values($enabledLocales),
            'default_locale' => $defaultLocale,
            'overrides' => $validated['overrides'] ?? [],
            'updated_at' => now()->toIso8601String(),
        ];

        $invitation->custom_config = $config;
        $invitation->save();

        AuditLogger::log(
            'MULTILINGUAL_CONFIG_UPDATED',
            'Invitation',
            $invitation->id,
            "Cập nhật cấu hình đa ngôn ngữ thiệp cưới #{$invitation->id}",
            [
                'enabled_locales' => $enabledLocales,
                'default_locale' => $defaultLocale,
            ]
        );

        return redirect()->route('account.multilingual.index', $invitation->id)
            ->with('success', 'Đã lưu cấu hình đa ngôn ngữ thiệp cưới thành công!');
    }

    /**
     * API công khai trả về gói từ điển theo ngôn ngữ cho thiệp cưới.
     */
    public function apiTranslations(Request $request, string $slug, string $locale): JsonResponse
    {
        $invitation = Invitation::where('slug', $slug)->firstOrFail();

        $dictionary = MultilingualInvitationService::getDictionary($locale, $invitation);

        return response()->json([
            'success' => true,
            'locale' => $locale,
            'slug' => $slug,
            'dictionary' => $dictionary,
        ]);
    }
}
