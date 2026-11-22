<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\GuestResponseLog;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Models\WeddingWish;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RsvpLedgerService
{
    public function __construct(
        public GuestInteractionService $guestInteraction,
        public OutboundWebhookService $webhooks,
    ) {}

    /**
     * Ghi nhận hoặc cập nhật phản hồi RSVP từ trang thiệp công khai (1 seam duy nhất).
     *
     * @param  array<string, mixed>  $payload
     * @return array{
     *     ok: bool,
     *     status_code: int,
     *     action?: string,
     *     is_updated?: bool,
     *     attending_status?: string,
     *     response?: GuestResponse,
     *     json_message?: string,
     *     flash_message?: string,
     *     message?: string,
     *     redirect_message?: string,
     *     extra?: array<string, mixed>
     * }
     */
    public function submitPublic(
        Invitation $invitation,
        array $payload,
        mixed $guestToken = null,
        ?string $ipAddress = null,
        bool $tokenParameterPresent = false
    ): array {
        $this->guestInteraction->assertInteractable($invitation);
        $wedding = $invitation->wedding;

        if ($invitation->isOwner()) {
            return $this->failure(
                403,
                'Bạn là chủ nhân của thiệp cưới này nên không thể tự gửi xác nhận tham dự cho chính mình.',
                [],
                'Bạn là chủ nhân của thiệp cưới này.'
            );
        }

        $config = $this->decodeConfig($invitation);
        if (! ($config['allow_rsvp'] ?? true)) {
            return $this->failure(
                422,
                'Tính năng xác nhận tham dự (RSVP) hiện đang tạm đóng theo cài đặt của cô dâu chú rể.'
            );
        }

        if ($invitation->isRsvpFrozen()) {
            return $this->failure(
                422,
                'Hôn lễ đã diễn ra thành công tốt đẹp! Form xác nhận tham dự (RSVP) đã được đóng lại.',
                ['is_frozen' => true]
            );
        }

        if (! RsvpEnforcementService::isRsvpOpen($invitation)) {
            $status = RsvpEnforcementService::getDeadlineStatus($invitation);
            $closedNotice = $status['closed_notice'];
            if (! empty($status['contact_phone'])) {
                $closedNotice .= " Hotline liên hệ: {$status['contact_phone']}";
            }

            return $this->failure(
                422,
                $closedNotice,
                [
                    'is_closed' => true,
                    'contact_phone' => $status['contact_phone'],
                ]
            );
        }

        $isLateRsvp = false;
        $rsvpDeadlineStr = $config['rsvp_deadline'] ?? null;
        if ($rsvpDeadlineStr) {
            $deadline = Carbon::parse($rsvpDeadlineStr)->endOfDay();
            if (Carbon::now()->isAfter($deadline)) {
                $isLateRsvp = true;
            }
        }

        if ($tokenParameterPresent) {
            if (! is_string($guestToken) || $guestToken === '' || strlen($guestToken) > 64) {
                abort(404);
            }

            $isRevoked = $wedding->guests()
                ->whereJsonContains('revoked_tokens', $guestToken)
                ->exists();

            if ($isRevoked) {
                return $this->failure(
                    422,
                    'Đường link thiệp mời này đã được chủ tiệc thu hồi và cấp lại mã mới. Quý khách vui lòng liên hệ cô dâu chú rể để nhận đường link thiệp mới nhất.',
                    ['is_revoked' => true]
                );
            }
        } else {
            $guestToken = null;
        }

        $allowRsvpUpdate = ! empty($config['allow_rsvp_update']);
        $attendingStatus = (string) ($payload['attending_status'] ?? 'attending');
        $resolvedHeadcount = $this->resolveHeadcount(
            $attendingStatus,
            $payload['adults_count'] ?? null,
            $payload['children_count'] ?? null,
            1
        );

        try {
            /** @var array{action: string|null, response: GuestResponse|null} $txResult */
            $txResult = DB::transaction(function () use (
                $wedding,
                $guestToken,
                $ipAddress,
                $payload,
                $isLateRsvp,
                $allowRsvpUpdate,
                $config,
                $attendingStatus,
                $resolvedHeadcount
            ): array {
                $lockedWedding = Wedding::whereKey($wedding->id)->lockForUpdate()->firstOrFail();
                $guest = $guestToken === null
                    ? null
                    : $lockedWedding->guests()->where('guest_token', $guestToken)->firstOrFail();

                if ($guest && $guest->max_attendees > 0 && $attendingStatus === 'attending') {
                    $requestedTotal = (int) ($payload['adults_count'] ?? 1) + (int) ($payload['children_count'] ?? 0);
                    if ($requestedTotal > $guest->max_attendees) {
                        throw new \InvalidArgumentException(
                            "Số lượng người tham dự ({$requestedTotal} người) vượt quá số suất thiệp mời cho phép (tối đa {$guest->max_attendees} suất). Vui lòng điều chỉnh lại hoặc liên hệ cô dâu chú rể để được sắp xếp chu đáo."
                        );
                    }
                }

                $privacyCfg = is_array($config['privacy_settings'] ?? null) ? $config['privacy_settings'] : [];
                if ($guest === null && array_key_exists('anonymous_rsvp_allowed', $privacyCfg) && ! $privacyCfg['anonymous_rsvp_allowed']) {
                    throw new \InvalidArgumentException(
                        'Thiệp cưới này chỉ nhận xác nhận tham dự qua đường link thiệp mời cá nhân của từng khách.'
                    );
                }

                $targetEventId = isset($payload['event_id']) && $payload['event_id'] !== '' && $payload['event_id'] !== null
                    ? (int) $payload['event_id']
                    : null;

                if ($targetEventId !== null) {
                    if (! $lockedWedding->events()->where('id', $targetEventId)->exists()) {
                        throw new \InvalidArgumentException('Sự kiện được chọn không hợp lệ hoặc không thuộc lễ cưới này.');
                    }

                    if ($guest && ! $guest->isEventAllowed($targetEventId)) {
                        throw new \InvalidArgumentException('Rất tiếc, thiệp mời của quý khách không thuộc phạm vi sự kiện này. Vui lòng liên hệ cô dâu chú rể.');
                    }
                }

                $existingQuery = GuestResponse::where('wedding_id', $lockedWedding->id);
                if ($targetEventId !== null) {
                    $existingQuery->where('event_id', $targetEventId);
                } else {
                    $existingQuery->whereNull('event_id');
                }

                if ($guest) {
                    $existingQuery->where('guest_id', $guest->id);
                } else {
                    $phone = $payload['phone'] ?? null;
                    if (! empty($phone)) {
                        $existingQuery->whereNull('guest_id')->where('phone', $phone);
                    } else {
                        $existingQuery->whereNull('guest_id')
                            ->where('guest_name', $payload['guest_name'] ?? '')
                            ->where('ip_address', $ipAddress);
                    }
                }

                $existing = $existingQuery->first();
                if ($existing) {
                    $canUpdate = $existing->needs_reconfirmation || ($guest !== null && $allowRsvpUpdate);
                    if (! $canUpdate) {
                        return ['action' => null, 'response' => null];
                    }

                    $this->recordChangeLog(
                        response: $existing,
                        weddingId: $lockedWedding->id,
                        guestId: $guest?->id,
                        previousStatus: (string) $existing->attending_status,
                        newStatus: $attendingStatus,
                        previousAdults: (int) ($existing->adults_count ?? 1),
                        newAdults: $resolvedHeadcount['adults'],
                        previousChildren: (int) ($existing->children_count ?? 0),
                        newChildren: $resolvedHeadcount['children'],
                        previousDietary: $existing->dietary_requirements,
                        newDietary: $payload['dietary_requirements'] ?? null,
                        previousWishes: $existing->wishes,
                        newWishes: $payload['wishes'] ?? null,
                        source: 'guest_online',
                        ipAddress: $ipAddress
                    );

                    $existing->update([
                        'event_id' => $targetEventId ?? $existing->event_id,
                        'attending_status' => $attendingStatus,
                        'adults_count' => $resolvedHeadcount['adults'],
                        'children_count' => $resolvedHeadcount['children'],
                        'shuttle_bus_needed' => $attendingStatus === 'declined' ? false : (bool) ($payload['shuttle_bus_needed'] ?? false),
                        'shuttle_pickup_point' => $attendingStatus === 'declined' ? null : ($payload['shuttle_pickup_point'] ?? null),
                        'shuttle_passengers' => $attendingStatus === 'declined' ? 0 : (int) ($payload['shuttle_passengers'] ?? 0),
                        'menu_preference' => $payload['menu_preference'] ?? null,
                        'dietary_requirements' => $payload['dietary_requirements'] ?? null,
                        'wishes' => $payload['wishes'] ?? null,
                        'needs_reconfirmation' => false,
                        'is_late' => $isLateRsvp,
                        'reconfirmed_at' => now(),
                        'ip_address' => $ipAddress,
                    ]);

                    AuditLogger::log(
                        'RSVP_UPDATED',
                        'GuestResponse',
                        $existing->id,
                        "Khách {$existing->guest_name} đã cập nhật lại phản hồi RSVP sang {$attendingStatus}"
                    );

                    return ['action' => 'updated', 'response' => $existing];
                }

                $response = GuestResponse::create([
                    'wedding_id' => $lockedWedding->id,
                    'guest_id' => $guest?->id,
                    'event_id' => $targetEventId,
                    'guest_name' => $payload['guest_name'] ?? '',
                    'phone' => $payload['phone'] ?? null,
                    'attending_status' => $attendingStatus,
                    'adults_count' => $resolvedHeadcount['adults'],
                    'children_count' => $resolvedHeadcount['children'],
                    'shuttle_bus_needed' => $attendingStatus === 'declined' ? false : (bool) ($payload['shuttle_bus_needed'] ?? false),
                    'shuttle_pickup_point' => $attendingStatus === 'declined' ? null : ($payload['shuttle_pickup_point'] ?? null),
                    'shuttle_passengers' => $attendingStatus === 'declined' ? 0 : (int) ($payload['shuttle_passengers'] ?? 0),
                    'menu_preference' => $payload['menu_preference'] ?? null,
                    'dietary_requirements' => $payload['dietary_requirements'] ?? null,
                    'wishes' => $payload['wishes'] ?? null,
                    'is_late' => $isLateRsvp,
                    'is_wishes_public' => true,
                    'ip_address' => $ipAddress,
                ]);

                if (! empty($payload['wishes']) && ($config['allow_wishes'] ?? true)) {
                    $wishAutoApprove = method_exists($lockedWedding, 'getWishModerationMode')
                        ? $lockedWedding->getWishModerationMode() !== 'manual'
                        : true;

                    WeddingWish::create([
                        'wedding_id' => $lockedWedding->id,
                        'guest_id' => $guest?->id,
                        'ip_address' => $ipAddress,
                        'name' => $payload['guest_name'] ?? '',
                        'message' => $payload['wishes'],
                        'is_approved' => $wishAutoApprove,
                    ]);
                }

                return ['action' => 'created', 'response' => $response];
            });
        } catch (\InvalidArgumentException $e) {
            return $this->failure(422, $e->getMessage(), ['headcount_exceeded' => true]);
        }

        if ($txResult['action'] === null || $txResult['response'] === null) {
            return $this->failure(
                422,
                'Bạn đã gửi xác nhận tham dự cho lễ cưới này rồi và không thể thay đổi.',
                [],
                'Bạn đã gửi xác nhận tham dự cho lễ cưới này rồi.'
            );
        }

        $isUpdated = $txResult['action'] === 'updated';

        try {
            $webhookEvent = $isUpdated ? 'rsvp.updated' : 'rsvp.submitted';
            $this->webhooks->dispatch($wedding, $webhookEvent, [
                'guest_name' => $payload['guest_name'] ?? '',
                'phone' => $payload['phone'] ?? null,
                'attending_status' => $attendingStatus,
                'adults_count' => $resolvedHeadcount['adults'],
                'children_count' => $resolvedHeadcount['children'],
                'dietary_requirements' => $payload['dietary_requirements'] ?? null,
                'wishes' => $payload['wishes'] ?? null,
                'is_updated' => $isUpdated,
            ]);
        } catch (\Throwable) {
            // Safe fallback
        }

        $jsonMessage = $isUpdated
            ? 'Cảm ơn quý khách! Phản hồi tham dự của quý khách đã được cập nhật thành công.'
            : ($attendingStatus === 'attending'
                ? 'Cảm ơn quý khách đã xác nhận tham dự! Chúng tôi rất hân hạnh được đón tiếp.'
                : 'Cảm ơn quý khách đã gửi phản hồi! Chúng tôi rất tiếc vì quý khách chưa thể sắp xếp tham dự.');

        $flashMessage = $isUpdated
            ? 'Cảm ơn quý khách! Phản hồi tham dự đã được cập nhật thành công.'
            : 'Cảm ơn quý khách! Phản hồi đã được ghi nhận.';

        return [
            'ok' => true,
            'status_code' => 200,
            'action' => $txResult['action'],
            'is_updated' => $isUpdated,
            'attending_status' => $attendingStatus,
            'response' => $txResult['response'],
            'json_message' => $jsonMessage,
            'flash_message' => $flashMessage,
        ];
    }

    /**
     * Chủ tiệc cập nhật hoặc đặt lại trạng thái RSVP thủ công hộ khách mời.
     *
     * @param  array<string, mixed>  $validated
     * @return array{
     *     ok: bool,
     *     status_code: int,
     *     response?: ?GuestResponse,
     *     warning?: ?string,
     *     is_table_overloaded?: bool,
     *     message: string,
     *     errors?: array<string, array<int, string>>
     * }
     */
    public function recordManualByHost(
        Guest $guest,
        array $validated,
        ?string $ipAddress = null,
        bool $hasExplicitCounts = false
    ): array {
        $status = (string) $validated['attending_status'];

        if ($status === 'attending') {
            $adultsCheck = $hasExplicitCounts
                ? (int) ($validated['adults_count'] ?? 0)
                : max(1, (int) $guest->expected_attendees);
            $childrenCheck = (int) ($validated['children_count'] ?? 0);

            if ($adultsCheck + $childrenCheck < 1) {
                $msg = 'Khi xác nhận tham dự, tổng số người lớn và trẻ em phải từ 1 người trở lên.';

                return [
                    'ok' => false,
                    'status_code' => 422,
                    'message' => $msg,
                    'errors' => ['adults_count' => [$msg]],
                ];
            }
        }

        /** @var GuestResponse|null $savedResponse */
        $savedResponse = DB::transaction(function () use ($guest, $validated, $status, $ipAddress): ?GuestResponse {
            $lockedGuest = Guest::with(['wedding', 'response'])->whereKey($guest->id)->lockForUpdate()->firstOrFail();

            if ($status === 'pending') {
                if ($lockedGuest->response) {
                    AuditLogger::log(
                        'GUEST_MANUAL_RSVP_RESET',
                        'Guest',
                        $lockedGuest->id,
                        "Chủ tiệc đã đặt lại trạng thái RSVP của khách {$lockedGuest->name} từ [{$lockedGuest->response->attending_status}] về [pending]"
                    );
                    $lockedGuest->responses()->delete();
                }

                return null;
            }

            $hasExplicitAdults = array_key_exists('adults_count', $validated) && $validated['adults_count'] !== null;
            $adults = $status === 'attending'
                ? ($hasExplicitAdults ? (int) $validated['adults_count'] : max(1, (int) $lockedGuest->expected_attendees))
                : 0;
            $children = $status === 'attending' ? (int) ($validated['children_count'] ?? 0) : 0;

            $existingResponse = $lockedGuest->response;
            $cleanDietary = isset($validated['dietary_requirements']) && trim((string) $validated['dietary_requirements']) !== ''
                ? strip_tags(trim((string) $validated['dietary_requirements']))
                : $existingResponse?->dietary_requirements;
            $cleanNotes = isset($validated['notes']) && trim((string) $validated['notes']) !== ''
                ? strip_tags(trim((string) $validated['notes']))
                : $existingResponse?->wishes;

            $prevStatus = (string) ($existingResponse?->attending_status ?? 'pending');
            $prevAdults = (int) ($existingResponse?->adults_count ?? 0);
            $prevChildren = (int) ($existingResponse?->children_count ?? 0);
            $prevDietary = $existingResponse?->dietary_requirements;
            $prevWishes = $existingResponse?->wishes;

            $response = GuestResponse::updateOrCreate(
                ['guest_id' => $lockedGuest->id, 'event_id' => $existingResponse?->event_id],
                [
                    'wedding_id' => $lockedGuest->wedding_id,
                    'guest_name' => $lockedGuest->name,
                    'attending_status' => $status,
                    'adults_count' => $adults,
                    'children_count' => $children,
                    'dietary_requirements' => $cleanDietary,
                    'wishes' => $cleanNotes,
                    'needs_reconfirmation' => false,
                    'reconfirmed_at' => now(),
                    'ip_address' => $ipAddress,
                ]
            );

            $this->recordChangeLog(
                response: $response,
                weddingId: $lockedGuest->wedding_id,
                guestId: $lockedGuest->id,
                previousStatus: $prevStatus,
                newStatus: $status,
                previousAdults: $prevAdults,
                newAdults: $adults,
                previousChildren: $prevChildren,
                newChildren: $children,
                previousDietary: $prevDietary,
                newDietary: $cleanDietary,
                previousWishes: $prevWishes,
                newWishes: $cleanNotes,
                source: 'manual_host',
                ipAddress: $ipAddress
            );

            return $response;
        });

        $tableOverloadedWarning = $this->buildTableOverloadWarning($guest->fresh(['wedding']));

        return [
            'ok' => true,
            'status_code' => 200,
            'response' => $savedResponse,
            'warning' => $tableOverloadedWarning,
            'is_table_overloaded' => ! empty($tableOverloadedWarning),
            'message' => 'Đã cập nhật trạng thái RSVP cho khách mời thành công.',
        ];
    }

    /**
     * Đại diện Cổng Gia Đình (Nhà Trai / Nhà Gái) cập nhật phản hồi RSVP hộ khách mời.
     *
     * @return array{
     *     ok: bool,
     *     response: GuestResponse,
     *     warning: ?string,
     *     is_table_overloaded: bool
     * }
     */
    public function recordByCoHost(
        Guest $guest,
        string $status,
        ?int $adults = null,
        ?string $note = null,
        string $side = FamilyCoHostPortalService::SIDE_GROOM,
        ?string $ipAddress = null
    ): array {
        /** @var GuestResponse $response */
        $response = DB::transaction(function () use ($guest, $status, $adults, $note, $side, $ipAddress): GuestResponse {
            $lockedGuest = Guest::with(['wedding', 'response'])->whereKey($guest->id)->lockForUpdate()->firstOrFail();
            $prevResponse = $lockedGuest->response;

            $prevStatus = (string) ($prevResponse?->attending_status ?? 'pending');
            $prevAdults = (int) ($prevResponse?->adults_count ?? 0);
            $prevChildren = (int) ($prevResponse?->children_count ?? 0);
            $prevDietary = $prevResponse?->dietary_requirements;
            $prevWishes = $prevResponse?->wishes;

            $newAdults = $status === 'attending'
                ? max(1, $adults ?? max(1, (int) $lockedGuest->expected_attendees))
                : 0;
            $newChildren = $status === 'attending' ? $prevChildren : 0;
            $newWishes = $note !== null
                ? (trim($note) !== '' ? strip_tags(trim($note)) : null)
                : $prevWishes;

            $saved = GuestResponse::updateOrCreate(
                ['guest_id' => $lockedGuest->id, 'event_id' => $prevResponse?->event_id],
                [
                    'wedding_id' => $lockedGuest->wedding_id,
                    'guest_name' => $lockedGuest->name,
                    'attending_status' => $status,
                    'adults_count' => $newAdults,
                    'children_count' => $newChildren,
                    'dietary_requirements' => $prevDietary,
                    'wishes' => $newWishes,
                    'needs_reconfirmation' => false,
                    'reconfirmed_at' => now(),
                ]
            );

            $this->recordChangeLog(
                response: $saved,
                weddingId: $lockedGuest->wedding_id,
                guestId: $lockedGuest->id,
                previousStatus: $prevStatus,
                newStatus: $status,
                previousAdults: $prevAdults,
                newAdults: $newAdults,
                previousChildren: $prevChildren,
                newChildren: $newChildren,
                previousDietary: $prevDietary,
                newDietary: $prevDietary,
                previousWishes: $prevWishes,
                newWishes: $newWishes,
                source: 'co_host_'.$side,
                ipAddress: $ipAddress
            );

            $sideLabel = $side === FamilyCoHostPortalService::SIDE_GROOM ? 'Họ Nhà Trai' : 'Họ Nhà Gái';
            AuditLogger::log(
                'CO_HOST_RSVP_UPDATED',
                'GuestResponse',
                $saved->id,
                "Đại diện {$sideLabel} đã cập nhật phản hồi của khách '{$lockedGuest->name}' sang {$status} ({$newAdults} người)"
            );

            return $saved;
        });

        $tableOverloadedWarning = $this->buildTableOverloadWarning($guest->fresh(['wedding']));

        return [
            'ok' => true,
            'response' => $response,
            'warning' => $tableOverloadedWarning,
            'is_table_overloaded' => ! empty($tableOverloadedWarning),
        ];
    }

    /**
     * Chuẩn hóa số lượng người lớn & trẻ em theo trạng thái tham dự.
     *
     * @return array{adults: int, children: int}
     */
    public function resolveHeadcount(
        string $attendingStatus,
        mixed $adultsInput,
        mixed $childrenInput,
        int $defaultAdults = 1
    ): array {
        if ($attendingStatus === 'declined') {
            return ['adults' => 0, 'children' => 0];
        }

        $adults = $adultsInput !== null && $adultsInput !== ''
            ? max(0, (int) $adultsInput)
            : max(1, $defaultAdults);
        $children = $childrenInput !== null && $childrenInput !== ''
            ? max(0, (int) $childrenInput)
            : 0;

        if ($attendingStatus === 'attending' && ($adults + $children) < 1) {
            $adults = 1;
        }

        return ['adults' => $adults, 'children' => $children];
    }

    /**
     * Cảnh báo quá tải ghế khi trạng thái RSVP vừa cập nhật vượt sức chứa bàn tiệc.
     */
    public function buildTableOverloadWarning(?Guest $guest): ?string
    {
        if (! $guest || ! $guest->table_id || ! $guest->wedding) {
            return null;
        }

        $table = $guest->wedding->tables()->find($guest->table_id);
        if (! $table || ! $table->isOverCapacity()) {
            return null;
        }

        $over = $table->assigned_attendees_count - $table->capacity;

        return "Lưu ý: Bàn \"{$table->name}\" đang bị quá tải ghế sau khi cập nhật RSVP ({$table->assigned_attendees_count}/{$table->capacity} người, thừa {$over} chỗ). Vui lòng điều chỉnh lại sơ đồ bàn tiệc.";
    }

    /**
     * Ghi 1 dòng nhật ký biến động RSVP chuẩn hóa vào sổ kiểm toán `guest_response_logs`.
     */
    private function recordChangeLog(
        GuestResponse $response,
        int $weddingId,
        ?int $guestId,
        string $previousStatus,
        string $newStatus,
        int $previousAdults,
        int $newAdults,
        int $previousChildren,
        int $newChildren,
        ?string $previousDietary,
        ?string $newDietary,
        ?string $previousWishes,
        ?string $newWishes,
        string $source,
        ?string $ipAddress
    ): void {
        GuestResponseLog::create([
            'guest_response_id' => $response->id,
            'wedding_id' => $weddingId,
            'guest_id' => $guestId,
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
            'previous_adults' => $previousAdults,
            'new_adults' => $newAdults,
            'previous_children' => $previousChildren,
            'new_children' => $newChildren,
            'previous_dietary' => $previousDietary,
            'new_dietary' => $newDietary,
            'previous_wishes' => $previousWishes,
            'new_wishes' => $newWishes,
            'source' => $source,
            'ip_address' => $ipAddress,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeConfig(Invitation $invitation): array
    {
        if (is_array($invitation->custom_config)) {
            return $invitation->custom_config;
        }

        return json_decode((string) $invitation->custom_config, true) ?? [];
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array{ok: false, status_code: int, message: string, redirect_message: string, extra: array<string, mixed>}
     */
    private function failure(int $statusCode, string $message, array $extra = [], ?string $redirectMessage = null): array
    {
        return [
            'ok' => false,
            'status_code' => $statusCode,
            'message' => $message,
            'redirect_message' => $redirectMessage ?? $message,
            'extra' => $extra,
        ];
    }
}
