<?php

namespace App\Services;

use App\Mail\WeddingInvitationCreatedMail;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InvitationPersistenceService
{
    public function __construct(
        public PlanAccessService $planAccess,
        public CloudflareR2StorageService $r2Storage,
        public WeddingStorageService $storageService,
    ) {}

    /**
     * Chuẩn hóa và kiểm duyệt dữ liệu đám cưới từ Studio Editor trước khi lưu.
     *
     * @param  array<string, mixed>  $rawWedding
     * @return array<string, mixed>
     */
    public function normalizeEditorWeddingPayload(array $rawWedding): array
    {
        $extraData = (array) ($rawWedding['extra_data'] ?? []);

        if (! empty($rawWedding['groom_name'])) {
            $rawWedding['groom_name'] = $this->validateAndSanitizeName((string) $rawWedding['groom_name'], 'Họ và tên Chú Rể');
        }
        if (! empty($rawWedding['bride_name'])) {
            $rawWedding['bride_name'] = $this->validateAndSanitizeName((string) $rawWedding['bride_name'], 'Họ và tên Cô Dâu');
        }
        if (! empty($extraData['groom_father'])) {
            $extraData['groom_father'] = $this->validateAndSanitizeName((string) $extraData['groom_father'], 'Họ tên Cha (Nhà Trai)');
        }
        if (! empty($extraData['groom_mother'])) {
            $extraData['groom_mother'] = $this->validateAndSanitizeName((string) $extraData['groom_mother'], 'Họ tên Mẹ (Nhà Trai)');
        }
        if (! empty($extraData['bride_father'])) {
            $extraData['bride_father'] = $this->validateAndSanitizeName((string) $extraData['bride_father'], 'Họ tên Cha (Nhà Gái)');
        }
        if (! empty($extraData['bride_mother'])) {
            $extraData['bride_mother'] = $this->validateAndSanitizeName((string) $extraData['bride_mother'], 'Họ tên Mẹ (Nhà Gái)');
        }

        if (! empty($extraData['event_party_address'])) {
            $extraData['event_party_address'] = mb_substr(trim(strip_tags((string) $extraData['event_party_address'])), 0, 255);
        }
        if (! empty($extraData['party_venue'])) {
            $extraData['party_venue'] = mb_substr(trim(strip_tags((string) $extraData['party_venue'])), 0, 150);
        }
        if (! empty($extraData['party_time'])) {
            $extraData['party_time'] = mb_substr(trim(strip_tags((string) $extraData['party_time'])), 0, 30);
        }
        if (! empty($extraData['event_party_time'])) {
            $extraData['event_party_time'] = mb_substr(trim(strip_tags((string) $extraData['event_party_time'])), 0, 30);
        }
        if (! empty($extraData['groom_bank_account'])) {
            $extraData['groom_bank_account'] = preg_replace('/[^a-zA-Z0-9]/', '', (string) $extraData['groom_bank_account']);
        }
        if (! empty($extraData['bride_bank_account'])) {
            $extraData['bride_bank_account'] = preg_replace('/[^a-zA-Z0-9]/', '', (string) $extraData['bride_bank_account']);
        }

        $rawWedding['extra_data'] = $extraData;

        if (empty($rawWedding['groom_parents']) && (! empty($extraData['groom_father']) || ! empty($extraData['groom_mother']))) {
            $rawWedding['groom_parents'] = $this->composeParentsLine(
                trim((string) ($extraData['groom_father'] ?? '')),
                trim((string) ($extraData['groom_mother'] ?? ''))
            );
        }

        if (empty($rawWedding['bride_parents']) && (! empty($extraData['bride_father']) || ! empty($extraData['bride_mother']))) {
            $rawWedding['bride_parents'] = $this->composeParentsLine(
                trim((string) ($extraData['bride_father'] ?? '')),
                trim((string) ($extraData['bride_mother'] ?? ''))
            );
        }

        return $rawWedding;
    }

    /**
     * Validate và sanitize họ tên con người (tối đa 4 từ, tối đa 30 ký tự, chỉ chữ cái và khoảng trắng).
     */
    public function validateAndSanitizeName(?string $name, string $fieldLabel): string
    {
        if ($name === null || trim($name) === '') {
            return '';
        }

        $clean = trim((string) preg_replace('/\s+/u', ' ', strip_tags($name)));

        if (mb_strlen($clean) > 30) {
            throw ValidationException::withMessages([
                $fieldLabel => "{$fieldLabel} không được vượt quá 30 ký tự (hiện tại: ".mb_strlen($clean).' ký tự).',
            ]);
        }

        $words = array_values(array_filter(preg_split('/\s+/u', $clean) ?: []));
        if (count($words) > 4) {
            throw ValidationException::withMessages([
                $fieldLabel => "{$fieldLabel} chỉ được tối đa 4 từ (hiện tại: ".count($words).' từ).',
            ]);
        }

        if (! preg_match('/^[\p{L}\s]+$/u', $clean)) {
            throw ValidationException::withMessages([
                $fieldLabel => "{$fieldLabel} chỉ được chứa chữ cái và khoảng trắng, không chứa số hoặc ký tự đặc biệt.",
            ]);
        }

        return $clean;
    }

    /**
     * Lưu thông tin thiệp cưới an toàn trong Transaction (khóa User/Device chống race condition hạn mức,
     * khóa Wedding trước Invitation, kiểm soát xung đột & snapshot).
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $sessionSlugs
     */
    public function saveInvitation(
        User $user,
        int $templateId,
        array $data,
        ?int $invitationId = null,
        ?int $expectedRevision = null,
        ?string $auditIp = null,
        ?string $guestDeviceId = null,
        array $sessionSlugs = []
    ): Invitation {
        $isGuest = $this->planAccess->isGuestUser($user);

        $execute = function () use ($user, $templateId, $data, $invitationId, $expectedRevision, $auditIp, $guestDeviceId, $sessionSlugs, $isGuest): Invitation {
            return DB::transaction(function () use ($user, $templateId, $data, $invitationId, $expectedRevision, $auditIp, $guestDeviceId, $sessionSlugs, $isGuest): Invitation {
                $weddingData = $this->sanitizePendingAssets($data['wedding'] ?? []);
                $extraData = $weddingData['extra_data'] ?? [];

                $weddingId = $invitationId ? Invitation::whereKey($invitationId)->value('wedding_id') : null;
                $wedding = $weddingId ? Wedding::whereKey($weddingId)->lockForUpdate()->firstOrFail() : null;
                $existingInvitation = $invitationId ? Invitation::whereKey($invitationId)->lockForUpdate()->firstOrFail() : null;

                $quotaSummary = null;
                if (! $existingInvitation) {
                    // Khóa hàng User trong DB transaction để triệt tiêu tuyệt đối race-condition tạo vượt hạn mức
                    if (! $isGuest) {
                        User::whereKey($user->id)->lockForUpdate()->first();
                    }

                    $quotaSummary = $this->planAccess->assertCanCreateInvitation(
                        $isGuest ? null : $user,
                        $isGuest ? $guestDeviceId : null,
                        $isGuest ? $sessionSlugs : []
                    );
                }

                $existingConfig = [];
                if ($existingInvitation) {
                    $existingConfig = $this->decodeJsonArray($existingInvitation->custom_config);

                    if (! empty($existingConfig['is_locked'])) {
                        throw ValidationException::withMessages([
                            'is_locked' => 'Thiệp cưới tạm thời bị khóa bởi Quản trị viên, không thể lưu hoặc xuất bản thay đổi.',
                        ]);
                    }
                }

                if ($existingInvitation && $existingInvitation->wedding_id !== $wedding?->id) {
                    throw ValidationException::withMessages([
                        'revision_id' => 'Dữ liệu thiệp đã thay đổi. Vui lòng tải lại trước khi tiếp tục.',
                    ]);
                }

                if ($existingInvitation && $expectedRevision !== (int) $existingInvitation->revision_id) {
                    throw ValidationException::withMessages([
                        'revision_id' => 'Thiệp đã được thay đổi ở phiên khác. Hãy giữ bản nháp và kiểm tra trước khi tiếp tục.',
                    ]);
                }

                $bankChangedAt = null;
                if ($existingInvitation && $wedding && $auditIp !== null) {
                    if ($this->detectAndAuditBankChange($wedding, $existingInvitation, $weddingData, $user, $auditIp)) {
                        $bankChangedAt = now()->toIso8601String();
                    }
                }

                $previousSnapshot = null;
                if (! $wedding) {
                    $wedding = Wedding::create([
                        'user_id' => $user->id,
                        'groom_name' => $weddingData['groom_name'] ?? '',
                        'bride_name' => $weddingData['bride_name'] ?? '',
                        'groom_parents' => $weddingData['groom_parents'] ?? null,
                        'bride_parents' => $weddingData['bride_parents'] ?? null,
                        'wedding_date' => $weddingData['wedding_date'] ?? now()->addMonths(2)->format('Y-m-d'),
                        'venue_name' => $weddingData['venue_name'] ?? 'Trung Tâm Tiệc Cưới',
                        'venue_address' => $weddingData['venue_address'] ?? 'TP. Hồ Chí Minh',
                        'story' => $weddingData['story'] ?? null,
                        'cover_photo_url' => $weddingData['cover_photo_url'] ?? null,
                        'music_url' => $weddingData['music_url'] ?? null,
                        'bank_name' => $weddingData['bank_name'] ?? null,
                        'bank_account_number' => $weddingData['bank_account_number'] ?? null,
                        'bank_account_name' => $weddingData['bank_account_name'] ?? null,
                        'extra_data' => $extraData,
                    ]);
                } else {
                    if ($existingInvitation) {
                        $previousSnapshot = $this->capturePublishedSnapshot($wedding, $existingInvitation, $existingConfig);
                    }

                    $fieldsToUpdate = array_intersect_key($weddingData, array_flip([
                        'groom_name', 'bride_name', 'groom_parents', 'bride_parents',
                        'wedding_date', 'venue_name', 'venue_address', 'story',
                        'cover_photo_url', 'music_url', 'bank_name',
                        'bank_account_number', 'bank_account_name',
                    ]));
                    $fieldsToUpdate['extra_data'] = $this->mergeWeddingExtraData($wedding->extra_data ?? [], $extraData);
                    $wedding->update($fieldsToUpdate);
                }

                $template = InvitationTemplate::findOrFail($templateId);
                $customConfig = $this->sanitizePendingAssets($data['invitation']['custom_config'] ?? []);
                $customConfig = $this->planAccess->sanitizeClientCustomConfig($customConfig, $existingConfig);

                if (isset($customConfig['visual_editor']) && is_array($customConfig['visual_editor'])) {
                    $customConfig['visual_editor'] = VisualEditorSanitizerService::sanitize($customConfig['visual_editor']);
                }

                if ($existingInvitation) {
                    $baseVisual = is_array($existingInvitation->custom_config['visual_editor'] ?? null)
                        ? $existingInvitation->custom_config['visual_editor']
                        : [];
                    if (is_array($existingInvitation->draft_config['visual_editor'] ?? null)) {
                        $baseVisual = VisualEditorSanitizerService::sanitizeAndMerge($baseVisual, $existingInvitation->draft_config['visual_editor']);
                    }
                    if (isset($data['invitation']['custom_config']['visual_editor']) && is_array($data['invitation']['custom_config']['visual_editor'])) {
                        $baseVisual = VisualEditorSanitizerService::sanitizeAndMerge($baseVisual, $data['invitation']['custom_config']['visual_editor']);
                    }

                    $mergedConfig = array_merge($existingConfig, $existingInvitation->draft_config ?? [], $customConfig);
                    $mergedConfig = $this->planAccess->sanitizeClientCustomConfig($mergedConfig, $existingConfig);
                    if ($baseVisual !== []) {
                        $mergedConfig['visual_editor'] = $baseVisual;
                    }
                    if ($previousSnapshot !== null) {
                        $mergedConfig['_previous_published_snapshot'] = $previousSnapshot;
                    }
                    if ($bankChangedAt !== null) {
                        $mergedConfig['last_bank_updated_at'] = $bankChangedAt;
                    }

                    $existingInvitation->setRelation('template', $template);
                    $canPublish = $this->planAccess->canPublishInvitation($existingInvitation, $user);

                    $updateData = [
                        'template_id' => $template->id,
                        'template_version' => $template->current_version ?? 'v1',
                        'custom_config' => $mergedConfig,
                        'draft_config' => null,
                        'revision_id' => (int) $existingInvitation->revision_id + 1,
                    ];
                    if (! $canPublish) {
                        $updateData['is_published'] = false;
                    }

                    $existingInvitation->update($updateData);
                    if ($wedding->wasChanged()) {
                        Invitation::where('wedding_id', $wedding->id)
                            ->whereKeyNot($existingInvitation->id)
                            ->increment('revision_id');
                    }

                    return $existingInvitation;
                }

                $slugBase = Str::slug($wedding->groom_name.'-'.$wedding->bride_name);
                if ($slugBase === '') {
                    $slugBase = 'thiep-cuoi';
                }
                $trimmedBase = Str::limit($slugBase, 70, '');

                do {
                    $suffix = Str::lower(Str::random(5)).'-'.Str::lower(Str::random(11));
                    $uniqueSlug = $trimmedBase.'-'.$suffix;
                } while (Invitation::withTrashed()->where('slug', $uniqueSlug)->exists());

                $createdPlanSlug = (string) ($quotaSummary['plan_slug'] ?? ($isGuest ? 'free' : 'premium'));
                $expiresAt = $this->planAccess->calculateInvitationExpiresAt($createdPlanSlug);
                $canPublishNew = $this->planAccess->canUseTemplateTier($user, $template->tier);

                return Invitation::create([
                    'wedding_id' => $wedding->id,
                    'owner_user_id' => $isGuest ? null : $user->id,
                    'guest_device_id' => $isGuest && $guestDeviceId ? $guestDeviceId : null,
                    'plan_slug' => $createdPlanSlug,
                    'plan_cycle_order_id' => $quotaSummary['active_cycle_order_id'] ?? null,
                    'template_id' => $template->id,
                    'slug' => $uniqueSlug,
                    'title' => 'Thiệp Cưới '.$wedding->groom_name.' & '.$wedding->bride_name,
                    'version_type' => 'general',
                    'template_version' => $template->current_version ?? 'v1',
                    'custom_config' => $customConfig,
                    'is_published' => $canPublishNew,
                    'published_at' => $canPublishNew ? now() : null,
                    'expires_at' => $expiresAt,
                ]);
            });
        };

        if (! $invitationId && $isGuest && ! empty($guestDeviceId)) {
            $lockKey = 'mayhen_free_quota_'.sha1($guestDeviceId);
            /** @var Invitation $saved */
            $saved = Cache::lock($lockKey, 10)->block(5, $execute);
        } else {
            /** @var Invitation $saved */
            $saved = $execute();
        }

        Cache::forget("invitation_{$saved->slug}");

        return $saved;
    }

    /**
     * Lưu bản nháp định kỳ kèm kiểm tra khóa lạc quan (Optimistic Concurrency Control).
     *
     * @param  array<string, mixed>  $draftConfig
     * @param  array<string, mixed>  $weddingDelta
     * @return array{success: bool, conflict: bool, locked?: bool, new_revision_id?: int, current_revision?: int, saved_at?: string, message?: string}
     */
    public function saveDraftWithLock(
        Invitation $invitation,
        int $clientRevision,
        array $draftConfig,
        array $weddingDelta = []
    ): array {
        return DB::transaction(function () use ($invitation, $clientRevision, $draftConfig, $weddingDelta): array {
            $weddingId = Invitation::whereKey($invitation->id)->value('wedding_id');
            $wedding = $weddingId ? Wedding::whereKey($weddingId)->lockForUpdate()->first() : null;
            $currentInvitation = Invitation::where('id', $invitation->id)->lockForUpdate()->first();

            if (! $currentInvitation) {
                return [
                    'success' => false,
                    'conflict' => true,
                    'message' => 'Không tìm thấy thiệp cưới tương ứng.',
                ];
            }

            $currentConfig = $this->decodeJsonArray($currentInvitation->custom_config);
            if (! empty($currentConfig['is_locked'])) {
                return [
                    'success' => false,
                    'conflict' => true,
                    'locked' => true,
                    'message' => 'Thiệp cưới đang bị khóa bởi Quản trị viên, không thể tự động lưu nháp.',
                ];
            }

            $currentRevision = (int) ($currentInvitation->revision_id ?? 0);
            if ($currentInvitation->wedding_id !== $wedding?->id) {
                return ['success' => false, 'conflict' => true, 'current_revision' => $currentRevision];
            }

            if ($clientRevision !== $currentRevision) {
                return [
                    'success' => false,
                    'conflict' => true,
                    'current_revision' => $currentRevision,
                    'message' => 'Phiên bản chỉnh sửa không khớp với máy chủ. Vui lòng kiểm tra bản nháp trước khi làm mới trang.',
                ];
            }

            $cleanDraft = $this->sanitizePendingAssets($draftConfig);
            $cleanWeddingDelta = $this->sanitizePendingAssets($weddingDelta);

            if ($wedding && ! empty($cleanWeddingDelta)) {
                $allowedFields = [
                    'groom_name', 'bride_name', 'groom_parents', 'bride_parents',
                    'wedding_date', 'venue_name', 'venue_address', 'story',
                    'cover_photo_url', 'music_url', 'bank_name',
                    'bank_account_number', 'bank_account_name',
                ];

                $fieldsToUpdate = [];
                foreach ($allowedFields as $field) {
                    if (array_key_exists($field, $cleanWeddingDelta)) {
                        $fieldsToUpdate[$field] = $cleanWeddingDelta[$field];
                    }
                }

                if (isset($cleanWeddingDelta['extra_data']) && is_array($cleanWeddingDelta['extra_data'])) {
                    $fieldsToUpdate['extra_data'] = $this->mergeWeddingExtraData($wedding->extra_data ?? [], $cleanWeddingDelta['extra_data']);
                }

                if (! empty($fieldsToUpdate)) {
                    $wedding->update($fieldsToUpdate);
                }
            }

            $newRevision = $currentRevision + 1;
            $existingDraft = is_array($currentInvitation->draft_config) ? $currentInvitation->draft_config : [];
            $incomingVisual = $draftConfig['visual_editor'] ?? null;
            $mergedDraft = array_merge($existingDraft, $cleanDraft);

            if (is_array($incomingVisual)) {
                $seedVisual = is_array($existingDraft['visual_editor'] ?? null)
                    ? $existingDraft['visual_editor']
                    : (is_array($currentConfig['visual_editor'] ?? null) ? $currentConfig['visual_editor'] : []);
                $mergedDraft['visual_editor'] = VisualEditorSanitizerService::sanitizeAndMerge($seedVisual, $incomingVisual);
            } elseif (array_key_exists('visual_editor', $draftConfig) && $incomingVisual === null) {
                $mergedDraft['visual_editor'] = VisualEditorSanitizerService::emptyState();
            }

            $currentInvitation->update([
                'draft_config' => $mergedDraft,
                'revision_id' => $newRevision,
                'last_autosaved_at' => now(),
            ]);

            if ($wedding?->wasChanged()) {
                Invitation::where('wedding_id', $wedding->id)
                    ->whereKeyNot($currentInvitation->id)
                    ->increment('revision_id');
            }

            return [
                'success' => true,
                'conflict' => false,
                'new_revision_id' => $newRevision,
                'saved_at' => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Khôi phục khẩn cấp bản xuất bản an toàn trước đó với khóa Wedding -> Invitation đồng nhất (Emergency Rollback).
     *
     * @return array{ok: bool, status_code: int, message: string, revision_id?: int, abort_message?: string}
     */
    public function rollbackToPreviousSnapshot(Invitation $invitation): array
    {
        /** @var array{ok: bool, status_code: int, message: string, revision_id?: int, slug?: string, abort_message?: string} $result */
        $result = DB::transaction(function () use ($invitation): array {
            $weddingId = Invitation::whereKey($invitation->id)->value('wedding_id');
            $wedding = $weddingId ? Wedding::whereKey($weddingId)->lockForUpdate()->first() : null;
            $lockedInvitation = Invitation::whereKey($invitation->id)->lockForUpdate()->firstOrFail();

            $config = $this->decodeJsonArray($lockedInvitation->custom_config);
            if (! empty($config['is_locked'])) {
                return [
                    'ok' => false,
                    'status_code' => 403,
                    'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên, không thể khôi phục phiên bản.',
                    'abort_message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
                ];
            }

            $snapshot = $config['_previous_published_snapshot'] ?? null;
            if (! is_array($snapshot) || empty($snapshot['wedding'])) {
                return [
                    'ok' => false,
                    'status_code' => 422,
                    'message' => 'Chưa có bản phát hành an toàn trước đó để khôi phục.',
                ];
            }

            $currentSnapshot = null;
            if ($wedding) {
                $currentSnapshot = $this->capturePublishedSnapshot($wedding, $lockedInvitation, $config, isRollbackSwap: true);

                $wData = $snapshot['wedding'];
                $wedding->update([
                    'groom_name' => $wData['groom_name'] ?? $wedding->groom_name,
                    'bride_name' => $wData['bride_name'] ?? $wedding->bride_name,
                    'groom_parents' => $wData['groom_parents'] ?? null,
                    'bride_parents' => $wData['bride_parents'] ?? null,
                    'wedding_date' => $wData['wedding_date'] ?? $wedding->wedding_date,
                    'venue_name' => $wData['venue_name'] ?? $wedding->venue_name,
                    'venue_address' => $wData['venue_address'] ?? $wedding->venue_address,
                    'story' => $wData['story'] ?? null,
                    'cover_photo_url' => $wData['cover_photo_url'] ?? null,
                    'music_url' => $wData['music_url'] ?? null,
                    'bank_name' => $wData['bank_name'] ?? null,
                    'bank_account_number' => $wData['bank_account_number'] ?? null,
                    'bank_account_name' => $wData['bank_account_name'] ?? null,
                    'extra_data' => $wData['extra_data'] ?? [],
                ]);
            }

            $restoredConfig = is_array($snapshot['custom_config'] ?? null) ? $snapshot['custom_config'] : [];
            if ($currentSnapshot !== null) {
                $restoredConfig['_previous_published_snapshot'] = $currentSnapshot;
            } else {
                unset($restoredConfig['_previous_published_snapshot']);
            }
            $restoredConfig['last_rollback_at'] = now()->toIso8601String();

            $newRevision = (int) $lockedInvitation->revision_id + 1;
            $lockedInvitation->update([
                'template_id' => $snapshot['template_id'] ?? $lockedInvitation->template_id,
                'custom_config' => $restoredConfig,
                'revision_id' => $newRevision,
            ]);

            if ($wedding?->wasChanged()) {
                Invitation::where('wedding_id', $wedding->id)
                    ->whereKeyNot($lockedInvitation->id)
                    ->increment('revision_id');
            }

            AuditLogger::log(
                'emergency_rollback',
                'invitation',
                $lockedInvitation->id,
                'Khôi phục khẩn cấp bản xuất bản thiệp cưới về phiên bản trước (Emergency Rollback)',
                [
                    'wedding_id' => $wedding?->id,
                    'restored_revision' => $newRevision,
                    'restored_at' => now()->toIso8601String(),
                ]
            );

            return [
                'ok' => true,
                'status_code' => 200,
                'message' => 'Đã khôi phục thành công thiệp cưới về phiên bản an toàn trước đó!',
                'revision_id' => $newRevision,
                'slug' => $lockedInvitation->slug,
            ];
        });

        if (! empty($result['ok']) && ! empty($result['slug'])) {
            Cache::forget("invitation_{$result['slug']}");
        }

        unset($result['slug']);

        return $result;
    }

    /**
     * Kiểm tra MIME ảnh thật, quyền sở hữu, khóa thiệp, hạn mức dung lượng và tải ảnh lên R2/Local.
     *
     * @return array{
     *     ok: bool,
     *     status_code: int,
     *     payload: array<string, mixed>,
     *     new_session_bytes?: int
     * }
     */
    public function uploadEditorAsset(
        UploadedFile $file,
        ?Invitation $invitation,
        ?User $user,
        ?string $ipAddress,
        int $sessionUploadedBytes = 0,
        bool $isAuthenticated = false
    ): array {
        $realPath = $file->getRealPath();
        $mimeType = $file->getMimeType() ?: '';
        if ($mimeType !== 'image/heic' && $mimeType !== 'image/heif') {
            $imgInfo = $realPath ? @getimagesize($realPath) : false;
            if ($imgInfo === false) {
                return [
                    'ok' => false,
                    'status_code' => 422,
                    'payload' => [
                        'success' => false,
                        'message' => 'Tệp tải lên không phải là hình ảnh hợp lệ.',
                    ],
                ];
            }
        }

        $newSessionBytes = null;
        if ($invitation !== null) {
            $isAuthorized = $invitation->isOwner() || ($user !== null && $user->isAdmin());
            if (! $isAuthorized) {
                return [
                    'ok' => false,
                    'status_code' => 403,
                    'payload' => [
                        'success' => false,
                        'message' => 'Bạn không có quyền tải tệp vào thiệp cưới này.',
                    ],
                ];
            }

            if ($invitation->isLocked()) {
                return [
                    'ok' => false,
                    'status_code' => 403,
                    'payload' => [
                        'success' => false,
                        'locked' => true,
                        'message' => 'Thiệp cưới đang bị khóa bởi Quản trị viên, không thể tải thêm tệp.',
                    ],
                ];
            }

            if ($invitation->wedding && ! $this->storageService->canUpload($invitation->wedding, $invitation, $file->getSize())) {
                $usage = $this->storageService->getStorageUsage($invitation->wedding, $invitation);

                return [
                    'ok' => false,
                    'status_code' => 422,
                    'payload' => [
                        'success' => false,
                        'message' => "Dung lượng lưu trữ của thiệp cưới đã vượt quá giới hạn gói ({$usage['limit_mb']} MB). Bạn đã dùng {$usage['used_mb']} MB. Vui lòng nâng cấp gói hoặc dọn bớt ảnh trong Kho Tệp.",
                        'quota_exceeded' => true,
                    ],
                ];
            }
        } else {
            $isGuestUnauthenticated = ! $isAuthenticated;
            $sessionLimitBytes = $isGuestUnauthenticated ? (15 * 1024 * 1024) : (40 * 1024 * 1024);

            if (($sessionUploadedBytes + $file->getSize()) > $sessionLimitBytes) {
                $limitMb = (int) ($sessionLimitBytes / (1024 * 1024));

                return [
                    'ok' => false,
                    'status_code' => 422,
                    'payload' => [
                        'success' => false,
                        'message' => "Phiên soạn thảo tạm đã đạt giới hạn {$limitMb} MB ảnh tải lên. Vui lòng đăng nhập và lưu thiệp để tiếp tục.",
                        'quota_exceeded' => true,
                    ],
                ];
            }

            if ($isGuestUnauthenticated) {
                $ipCacheKey = 'studio_guest_upload_bytes_ip_'.sha1((string) $ipAddress);
                $ipBytes = (int) Cache::get($ipCacheKey, 0);
                if (($ipBytes + $file->getSize()) > (15 * 1024 * 1024)) {
                    return [
                        'ok' => false,
                        'status_code' => 429,
                        'payload' => [
                            'success' => false,
                            'message' => 'Bạn đã đạt giới hạn tải ảnh cho khách trải nghiệm (15 MB/giờ). Vui lòng đăng nhập để tiếp tục tải ảnh.',
                            'quota_exceeded' => true,
                        ],
                    ];
                }
                Cache::put($ipCacheKey, $ipBytes + $file->getSize(), now()->addHour());
            }

            $newSessionBytes = $sessionUploadedBytes + $file->getSize();
        }

        $allowedExtensions = ['jpeg', 'png', 'jpg', 'webp', 'gif', 'heic'];
        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        if (! in_array($ext, $allowedExtensions, true)) {
            $ext = 'jpg';
        }
        $prefixSlug = $invitation?->slug ?: 'thiep-cuoi';
        $filename = CloudflareR2StorageService::buildReadableFilename($file->getClientOriginalName(), $ext, $prefixSlug);

        $uploadResult = $this->r2Storage->upload($file, 'weddings/photos', $filename);

        $response = [
            'ok' => true,
            'status_code' => 200,
            'payload' => [
                'success' => true,
                'file_name' => $uploadResult['file_name'],
                'file_url' => $uploadResult['url'],
                'relative_path' => $uploadResult['relative_url'],
                'storage_path' => $uploadResult['storage_path'],
                'disk' => $uploadResult['disk'],
            ],
        ];

        if ($newSessionBytes !== null) {
            $response['new_session_bytes'] = $newSessionBytes;
        }

        return $response;
    }

    /**
     * Đưa email chúc mừng tạo thiệp lần đầu vào hàng đợi (idempotent qua flag created_mail_sent_at).
     */
    public function queueCreatedMailIfNeeded(Invitation $invitation, User $user): void
    {
        $latestConfig = $this->decodeJsonArray($invitation->custom_config);
        if (! empty($latestConfig['created_mail_sent_at']) || empty($user->email)) {
            return;
        }

        $latestConfig['created_mail_sent_at'] = now()->toIso8601String();
        $invitation->forceFill(['custom_config' => $latestConfig])->save();

        try {
            Mail::to($user->email)->queue(new WeddingInvitationCreatedMail($invitation));
        } catch (\Throwable $e) {
            Log::warning('Không thể đưa email chúc mừng tạo thiệp cưới vào hàng đợi: '.$e->getMessage(), [
                'invitation_id' => $invitation->id,
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Đóng gói snapshot bản xuất bản chuẩn (loại trừ _previous_published_snapshot lồng đệ quy).
     *
     * @param  array<string, mixed>  $config
     * @return array<string, mixed>
     */
    public function capturePublishedSnapshot(
        Wedding $wedding,
        Invitation $invitation,
        array $config,
        bool $isRollbackSwap = false
    ): array {
        $snapshot = [
            'saved_at' => now()->toIso8601String(),
            'template_id' => $invitation->template_id,
            'wedding' => [
                'groom_name' => $wedding->groom_name,
                'bride_name' => $wedding->bride_name,
                'groom_parents' => $wedding->groom_parents,
                'bride_parents' => $wedding->bride_parents,
                'wedding_date' => $wedding->wedding_date ? $wedding->wedding_date->format('Y-m-d') : null,
                'venue_name' => $wedding->venue_name,
                'venue_address' => $wedding->venue_address,
                'story' => $wedding->story,
                'cover_photo_url' => $wedding->cover_photo_url,
                'music_url' => $wedding->music_url,
                'bank_name' => $wedding->bank_name,
                'bank_account_number' => $wedding->bank_account_number,
                'bank_account_name' => $wedding->bank_account_name,
                'extra_data' => $this->decodeJsonArray($wedding->extra_data),
            ],
            'custom_config' => Arr::except($config, ['_previous_published_snapshot']),
        ];

        if ($isRollbackSwap) {
            $snapshot['is_rollback_swap'] = true;
        }

        return $snapshot;
    }

    /**
     * Phát hiện thay đổi tài khoản ngân hàng mừng cưới trên bản ghi đã khóa và ghi nhật ký chống giả mạo.
     *
     * @param  array<string, mixed>  $newWeddingData
     */
    private function detectAndAuditBankChange(
        Wedding $lockedWedding,
        Invitation $lockedInvitation,
        array $newWeddingData,
        User $user,
        string $ipAddress
    ): bool {
        $oldExtra = $this->decodeJsonArray($lockedWedding->extra_data);
        $oldGroomBank = (string) ($oldExtra['groom_bank_account'] ?? ($lockedWedding->bank_account_number ?? ''));
        $oldGroomBankName = (string) ($oldExtra['groom_bank_name'] ?? ($lockedWedding->bank_name ?? ''));
        $oldBrideBank = (string) ($oldExtra['bride_bank_account'] ?? '');
        $oldBrideBankName = (string) ($oldExtra['bride_bank_name'] ?? '');

        $newExtra = (array) ($newWeddingData['extra_data'] ?? []);
        $newGroomBank = (string) ($newExtra['groom_bank_account'] ?? ($newWeddingData['bank_account_number'] ?? ''));
        $newGroomBankName = (string) ($newExtra['groom_bank_name'] ?? ($newWeddingData['bank_name'] ?? ''));
        $newBrideBank = (string) ($newExtra['bride_bank_account'] ?? '');
        $newBrideBankName = (string) ($newExtra['bride_bank_name'] ?? '');

        $isBankChanged = ($oldGroomBank !== $newGroomBank)
            || ($oldGroomBankName !== $newGroomBankName)
            || ($oldBrideBank !== $newBrideBank)
            || ($oldBrideBankName !== $newBrideBankName);

        if (! $isBankChanged) {
            return false;
        }

        AuditLogger::log(
            'BANK_ACCOUNT_CHANGED',
            'Wedding',
            $lockedWedding->id,
            "Tài khoản mừng cưới của thiệp [{$lockedInvitation->slug}] đã được cập nhật.",
            [
                'invitation_id' => $lockedInvitation->id,
                'invitation_slug' => $lockedInvitation->slug,
                'old_accounts' => [
                    'groom' => $oldGroomBank ? "{$oldGroomBankName} - {$oldGroomBank}" : null,
                    'bride' => $oldBrideBank ? "{$oldBrideBankName} - {$oldBrideBank}" : null,
                ],
                'new_accounts' => [
                    'groom' => $newGroomBank ? "{$newGroomBankName} - {$newGroomBank}" : null,
                    'bride' => $newBrideBank ? "{$newBrideBankName} - {$newBrideBank}" : null,
                ],
                'user_id' => $user->id,
                'ip' => $ipAddress,
            ]
        );

        return true;
    }

    private function composeParentsLine(string $father, string $mother): ?string
    {
        if ($father !== '' && $mother !== '') {
            return 'ÔNG: '.$father.' & BÀ: '.$mother;
        }
        if ($father !== '') {
            return 'ÔNG: '.$father;
        }
        if ($mother !== '') {
            return 'BÀ: '.$mother;
        }

        return null;
    }

    /**
     * @param  array<string|int, mixed>  $values
     * @return array<string|int, mixed>
     */
    private function sanitizePendingAssets(array $values): array
    {
        $sanitized = [];
        foreach ($values as $key => $value) {
            if (is_string($value) && str_starts_with(trim($value), 'blob:')) {
                continue;
            }
            $cleanValue = is_array($value) ? $this->sanitizePendingAssets($value) : $value;
            if (is_array($value) && $value !== [] && $cleanValue === []) {
                continue;
            }
            $sanitized[$key] = $cleanValue;
        }

        return $sanitized;
    }

    /**
     * @param  array<string, mixed>  $existing
     * @param  array<string, mixed>  $changes
     * @return array<string, mixed>
     */
    private function mergeWeddingExtraData(array $existing, array $changes): array
    {
        foreach (['gallery', 'timeline'] as $listKey) {
            if (isset($changes[$listKey]) && is_array($changes[$listKey])
                && $changes[$listKey] !== [] && is_array($existing[$listKey] ?? null)) {
                $changes[$listKey] = array_replace_recursive($existing[$listKey], $changes[$listKey]);
            }
        }

        return array_merge($existing, $changes);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonArray(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        return json_decode((string) $raw, true) ?? [];
    }
}
