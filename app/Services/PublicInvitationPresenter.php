<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\PlatformSetting;
use App\Models\Wedding;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Deep module owning public invitation presentation resolution:
 * all 6 lifecycle gates, guest token validation, single persona placeholder engine,
 * bank privacy masking, RSVP deadline status, Open Graph tags, and view-data assembly.
 */
class PublicInvitationPresenter
{
    public function __construct(
        protected PlanAccessService $planAccess,
        protected WeddingAnalyticsService $analyticsService
    ) {}

    /**
     * Resolve the public presentation outcome for `/thiep/{slug}`.
     *
     * @return array{
     *     type: 'view'|'redirect',
     *     view?: string,
     *     data?: array<string, mixed>,
     *     redirect_route?: string,
     *     redirect_params?: mixed
     * }
     */
    public function resolve(string $slug, Request $request): array
    {
        $invitation = Invitation::with(['wedding.partner', 'wedding.keepsake', 'template'])
            ->where('slug', $slug)
            ->firstOrFail();

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return [
                'type' => 'view',
                'view' => 'client.pages.invitation-locked',
                'data' => [
                    'invitation' => $invitation,
                    'lockReason' => $config['lock_reason'] ?? 'Thiệp cưới tạm thời bị khóa do yêu cầu từ Quản trị viên.',
                ],
            ];
        }

        $isOwner = $invitation->isOwner();

        if ($invitation->shouldRedirectToMemoryVault() && ! $isOwner && ! $request->query('preview')) {
            return [
                'type' => 'redirect',
                'redirect_route' => 'memory.vault',
                'redirect_params' => $slug,
            ];
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast() && ! $isOwner) {
            return [
                'type' => 'view',
                'view' => 'client.pages.invitation-expired',
                'data' => ['invitation' => $invitation],
            ];
        }

        $canBePubliclyViewed = $invitation->is_published
            && $this->planAccess->canPublishInvitation($invitation);
        if (! $canBePubliclyViewed && ! $isOwner) {
            return [
                'type' => 'view',
                'view' => 'client.pages.invitation-unpublished',
                'data' => ['invitation' => $invitation],
            ];
        }

        if (! empty($config['password']) && ! $isOwner) {
            $isUnlocked = session("unlocked_invitation_{$invitation->id}") === true;
            if (! $isUnlocked) {
                return [
                    'type' => 'view',
                    'view' => 'client.pages.invitation-password',
                    'data' => [
                        'invitation' => $invitation,
                        'wedding' => $invitation->wedding,
                    ],
                ];
            }
        }

        $wedding = $invitation->wedding;
        $privacySettings = is_array($config['privacy_settings'] ?? null) ? $config['privacy_settings'] : [];
        $wishesVisibility = $privacySettings['wishes_visibility'] ?? 'public';

        $wishes = ($wedding && ($isOwner || $wishesVisibility !== 'couple_only'))
            ? $wedding->wishes()->where('is_approved', true)->where('is_private', false)->take(50)->get()
            : collect([]);

        $wishSubmitUrl = route('invitation.wish.store', $slug);
        [$guest, $rsvpRouteParameters] = $this->resolveGuestFromRequest($request, $wedding, $slug);
        $rsvpSubmitUrl = route('rsvp.submit', $rsvpRouteParameters);

        $currentRsvp = ($wedding && $guest)
            ? GuestResponse::where('wedding_id', $wedding->id)
                ->where('guest_id', $guest->id)
                ->latest()
                ->first()
            : null;

        $rsvpStats = null;
        $rsvpResponses = collect([]);

        if ($isOwner && $wedding) {
            $rsvpResponses = GuestResponse::where('wedding_id', $wedding->id)->latest()->get();
            $attending = $rsvpResponses->where('attending_status', 'attending');
            $rsvpStats = [
                'total' => $rsvpResponses->count(),
                'attending' => $attending->count(),
                'attending_guests' => (int) ($attending->sum('adults_count') + $attending->sum('children_count')),
                'declined' => $rsvpResponses->where('attending_status', 'declined')->count(),
            ];
        }

        if (! $isOwner) {
            $this->analyticsService->recordVisit($invitation, $request, $guest);
            if ($guest) {
                $guest->recordOpen($request->userAgent());
            }
        }

        $template = $invitation->template;
        $personaConfig = $config['persona_messages'] ?? [];
        $isPersonaEnabled = ! empty($personaConfig['enabled']);
        $personaMessage = null;

        $isRehearsal = false;
        $rehearsalGroup = null;
        $availableGroups = ['Bạn Bè', 'Gia Đình & Họ Hàng', 'Đồng Nghiệp', 'Khách VIP'];
        if ($wedding) {
            $dbGroups = $wedding->guests()
                ->whereNotNull('group_name')
                ->where('group_name', '!=', '')
                ->distinct()
                ->pluck('group_name')
                ->all();
            $availableGroups = array_values(array_unique(array_filter(array_merge($availableGroups, $dbGroups))));
        }

        if ($isOwner && ($request->boolean('rehearsal') || $request->has('rehearsal_group') || $request->has('group'))) {
            $isRehearsal = true;
            $reqGroup = $request->query('rehearsal_group') ?: $request->query('group');
            if (is_string($reqGroup) && trim($reqGroup) !== '') {
                $rehearsalGroup = Str::limit(strip_tags(trim($reqGroup)), 50, '');
            }

            if ($rehearsalGroup) {
                if (! $guest) {
                    $guest = $this->makeRehearsalGuest($wedding, $rehearsalGroup);
                }

                if ($isPersonaEnabled) {
                    $personaMessage = $this->buildPersonaMessage($personaConfig, $rehearsalGroup, $wedding, $guest, $rehearsalGroup);
                }
            }
        } elseif ($isPersonaEnabled && $guest) {
            $groupKey = ! empty($guest->group_name) ? trim($guest->group_name) : 'default';
            $personaMessage = $this->buildPersonaMessage(
                $personaConfig,
                $groupKey,
                $wedding,
                $guest,
                $guest->group_name ?: 'Khách Quý'
            );
        }

        [$rsvpDeadlineDate, $isRsvpClosed, $isRsvpLate] = $this->resolveRsvpDeadline($config);
        $og = $this->buildOpenGraphData($invitation, $wedding, $template, $guest, $config);

        $this->applyBankPrivacyMask($wedding, $privacySettings, $isOwner, $guest);

        $ceremonyScope = $wedding
            ? SmartGroupInvitationService::resolveCeremonyVisibility($wedding, $guest, $rehearsalGroup, $invitation)
            : [
                'show_bride_ceremony' => true,
                'show_groom_ceremony' => true,
                'show_main_party' => true,
                'tailored_group_notice' => null,
                'events' => collect([]),
            ];

        if (! $personaMessage && $guest) {
            $personaMessage = SmartGroupInvitationService::resolveGroupGreeting($invitation, $guest);
        }

        $viewData = [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'template' => $template,
            'isCuratedSeven' => array_key_exists('is_curated_seven', $config)
                ? (bool) $config['is_curated_seven']
                : ! empty($config['is_curated']),
            'guest' => $guest,
            'smartEvents' => $ceremonyScope['events'],
            'ceremonyScope' => $ceremonyScope,
            'showBrideCeremony' => $ceremonyScope['show_bride_ceremony'],
            'showGroomCeremony' => $ceremonyScope['show_groom_ceremony'],
            'showMainParty' => $ceremonyScope['show_main_party'],
            'tailoredGroupNotice' => $ceremonyScope['tailored_group_notice'],
            'wishes' => $wishes,
            'wishSubmitUrl' => $wishSubmitUrl,
            'rsvpSubmitUrl' => $rsvpSubmitUrl,
            'currentRsvp' => $currentRsvp,
            'myWish' => null,
            'isOwner' => $isOwner,
            'rsvpStats' => $rsvpStats,
            'rsvpResponses' => $rsvpResponses,
            'personaMessage' => $personaMessage,
            'isRehearsal' => $isRehearsal,
            'rehearsalGroup' => $rehearsalGroup,
            'availableGroups' => $availableGroups,
            'rsvpDeadline' => $rsvpDeadlineDate,
            'isRsvpClosed' => $isRsvpClosed,
            'isRsvpLate' => $isRsvpLate,
            'rsvpClosedNotice' => $config['rsvp_closed_notice'] ?? null,
            'ogTitle' => $og['title'],
            'ogDescription' => $og['description'],
            'ogImage' => $og['image'],
            'ogUrl' => $og['url'],
            'isPublicInvitationRoute' => true,
        ];

        $customView = $template ? "client.invitations.{$template->slug}.template" : null;
        $viewName = ($customView && view()->exists($customView))
            ? $customView
            : 'client.pages.public-invitation';

        return [
            'type' => 'view',
            'view' => $viewName,
            'data' => $viewData,
        ];
    }

    /**
     * Single persona placeholder formatter shared by both rehearsal and live guests.
     *
     * @param  array<string, mixed>  $personaConfig
     * @return array<string, mixed>|null
     */
    public function buildPersonaMessage(
        array $personaConfig,
        string $groupKey,
        ?Wedding $wedding,
        Guest $guest,
        string $displayGroupName
    ): ?array {
        $groupMsg = $personaConfig['groups'][$groupKey] ?? ($personaConfig['groups']['default'] ?? null);

        if (! is_array($groupMsg) || (empty($groupMsg['content']) && empty($groupMsg['title']) && empty($groupMsg['subtitle']) && empty($groupMsg['message']))) {
            return null;
        }

        $salutation = ! empty($guest->salutation) ? trim((string) $guest->salutation) : 'Quý khách';
        $groom = $wedding->groom_name ?? 'Chú Rể';
        $bride = $wedding->bride_name ?? 'Cô Dâu';
        $coupleName = "{$groom} & {$bride}";
        $dateStr = $wedding && $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : '';

        $replace = static function (?string $text) use ($guest, $salutation, $coupleName, $groom, $bride, $dateStr): ?string {
            if (empty($text)) {
                return null;
            }

            return str_replace(
                ['{guest_name}', '{salutation}', '{couple_name}', '{groom_name}', '{bride_name}', '{wedding_date}'],
                [$guest->name ?? 'Quý khách', $salutation, $coupleName, $groom, $bride, $dateStr],
                $text
            );
        };

        $rawContent = $groupMsg['content'] ?? ($groupMsg['message'] ?? null);
        $formattedContent = $replace($rawContent);

        return [
            'title' => $replace($groupMsg['title'] ?? null),
            'subtitle' => $replace($groupMsg['subtitle'] ?? null),
            'content' => $formattedContent,
            'message' => $formattedContent,
            'group_name' => $displayGroupName,
        ];
    }

    /**
     * @return array{0: ?Guest, 1: array<string, string>}
     */
    protected function resolveGuestFromRequest(Request $request, ?Wedding $wedding, string $slug): array
    {
        $rsvpRouteParameters = ['slug' => $slug];
        $query = $request->query();
        $guest = null;

        if (array_key_exists('token', $query)) {
            $guestToken = $query['token'];
            if (! is_string($guestToken) || $guestToken === '' || strlen($guestToken) > 64) {
                abort(404);
            }

            abort_unless($wedding, 404);
            $guest = $wedding->guests()->where('guest_token', $guestToken)->first();
            if (! $guest) {
                $isRevoked = $wedding->guests()->whereJsonContains('revoked_tokens', $guestToken)->exists();
                if ($isRevoked) {
                    abort(410, 'Đường link thiệp mời này đã được thu hồi và cấp mã mới. Quý khách vui lòng liên hệ cô dâu chú rể để nhận đường link mới nhất.');
                }
                abort(404);
            }
            $rsvpRouteParameters['token'] = $guestToken;
        }

        return [$guest, $rsvpRouteParameters];
    }

    protected function makeRehearsalGuest(?Wedding $wedding, string $rehearsalGroup): Guest
    {
        $dummyGuest = new Guest;
        $dummyGuest->id = 0;
        $dummyGuest->wedding_id = $wedding ? $wedding->id : 0;
        $dummyGuest->name = "Khách Quý ({$rehearsalGroup})";
        $dummyGuest->salutation = 'Quý khách';
        $dummyGuest->group_name = $rehearsalGroup;
        $dummyGuest->guest_token = 'rehearsal-demo-token';
        $dummyGuest->expected_attendees = 2;
        $dummyGuest->table_name = ($rehearsalGroup === 'Khách VIP') ? 'Bàn VIP 01' : 'Bàn 08';

        return $dummyGuest;
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array{0: ?Carbon, 1: bool, 2: bool}
     */
    protected function resolveRsvpDeadline(array $config): array
    {
        $rsvpDeadlineStr = $config['rsvp_deadline'] ?? null;
        if (! $rsvpDeadlineStr) {
            return [null, false, false];
        }

        $deadline = Carbon::parse($rsvpDeadlineStr)->endOfDay();
        if (! Carbon::now()->isAfter($deadline)) {
            return [$deadline, false, false];
        }

        if (! empty($config['rsvp_lock_after_deadline'])) {
            return [$deadline, true, false];
        }

        return [$deadline, false, true];
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array{title: string, description: string, image: ?string, url: string}
     */
    protected function buildOpenGraphData(Invitation $invitation, ?Wedding $wedding, mixed $template, ?Guest $guest, array $config): array
    {
        $groomName = $wedding->groom_name ?? 'Chú Rể';
        $brideName = $wedding->bride_name ?? 'Cô Dâu';
        $weddingDateFormatted = $wedding && $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : '';
        $weddingExtra = $wedding && is_array($wedding->extra_data)
            ? $wedding->extra_data
            : ($wedding ? (json_decode((string) $wedding->extra_data, true) ?? []) : []);
        $partyTime = $weddingExtra['party_time'] ?? ($weddingExtra['event_party_time'] ?? '11:00');
        $venueName = ($wedding?->venue_name) ?: ($weddingExtra['party_venue'] ?? ($wedding?->venue_address ?? 'Tư gia'));

        if ($guest) {
            $fullGreeting = $guest->formattedFullGreeting();
            $ogTitle = "{$fullGreeting} tới dự lễ cưới {$groomName} & {$brideName}";
            $ogDescription = "Trân trọng {$fullGreeting} tới chung vui cùng gia đình chúng em vào lúc {$partyTime} ngày {$weddingDateFormatted} tại {$venueName}.";
        } else {
            $ogTitle = ! empty($config['og_title']) ? $config['og_title'] : "Thiệp cưới {$groomName} & {$brideName} — ".PlatformSetting::getBrandName();
            $ogDescription = ! empty($config['og_description']) ? $config['og_description'] : "Trân trọng kính mời quý khách tới chung vui cùng gia đình chúng em vào lúc {$partyTime} ngày {$weddingDateFormatted} tại {$venueName}.";
        }

        $ogImage = ($wedding?->cover_photo_url) ?: ($weddingExtra['hero_photo_url'] ?? ($template?->thumbnail_url ?? null));
        if ($ogImage && ! str_starts_with($ogImage, 'http')) {
            $ogImage = url($ogImage);
        }

        return [
            'title' => $ogTitle,
            'description' => $ogDescription,
            'image' => $ogImage,
            'url' => route('invitation.show', $invitation->slug),
        ];
    }

    /**
     * @param  array<string, mixed>  $privacySettings
     */
    protected function applyBankPrivacyMask(?Wedding $wedding, array $privacySettings, bool $isOwner, ?Guest $guest): void
    {
        $bankVisibility = $privacySettings['bank_visibility'] ?? 'everyone';
        if (! $wedding || $isOwner) {
            return;
        }

        if ($bankVisibility === 'hidden' || ($bankVisibility === 'named_guests_only' && ! $guest)) {
            $wedding->setAttribute('bank_name', null);
            $wedding->setAttribute('bank_account_number', null);
            $wedding->setAttribute('bank_account_name', null);
            $maskedExtra = is_array($wedding->extra_data) ? $wedding->extra_data : [];
            unset(
                $maskedExtra['groom_bank_account'],
                $maskedExtra['groom_bank_name'],
                $maskedExtra['groom_bank_holder'],
                $maskedExtra['bride_bank_account'],
                $maskedExtra['bride_bank_name'],
                $maskedExtra['bride_bank_holder'],
                $maskedExtra['bank_accounts'],
                $maskedExtra['gift_registry']
            );
            $wedding->setAttribute('extra_data', $maskedExtra);
        }
    }
}
