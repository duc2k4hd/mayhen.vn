<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\WeddingWish;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Deep module owning public guest interactions on invitations:
 * interactability guards, hardened autograph/photo persistence,
 * wish submission, and wish reply mutations.
 */
class GuestInteractionService
{
    public function __construct(
        protected CloudflareR2StorageService $storage,
        protected OutboundWebhookService $webhooks
    ) {}

    /**
     * Single interactability guard for locked, unpublished, or expired invitations.
     * Returns null when interactable, or an error descriptor [status, message].
     *
     * @return array{status: int, message: string}|null
     */
    public function checkInteractable(Invitation $invitation, bool $allowOwnerBypass = false): ?array
    {
        if ($invitation->isLocked()) {
            return [
                'status' => 403,
                'message' => 'Thiệp cưới đang tạm thời bị khóa.',
            ];
        }

        $isOwnerOrAdmin = $allowOwnerBypass
            && ($invitation->isOwner() || (Auth::check() && Auth::user()->isAdmin()));

        if (! $isOwnerOrAdmin) {
            if (! $invitation->is_published) {
                return [
                    'status' => 404,
                    'message' => 'Thiệp cưới chưa được xuất bản.',
                ];
            }

            if ($invitation->isExpired()) {
                return [
                    'status' => 410,
                    'message' => 'Thiệp cưới đã hết thời hạn lưu trữ.',
                ];
            }
        }

        return null;
    }

    /**
     * Abort with appropriate HTTP status if invitation is not interactable.
     */
    public function assertInteractable(Invitation $invitation, bool $allowOwnerBypass = false): void
    {
        $error = $this->checkInteractable($invitation, $allowOwnerBypass);
        if ($error !== null) {
            abort($error['status'], $error['message']);
        }
    }

    /**
     * Single hardened autograph validation and upload pipeline.
     * Validates MIME header, strict base64 decoding, 1MB cap, and real image bytes.
     */
    public function storeAutograph(Invitation $invitation, string $guestName, string $signatureDataUrl): string
    {
        if (! preg_match('/^data:image\/(png|jpe?g|webp);base64,/i', $signatureDataUrl, $type)) {
            throw new \InvalidArgumentException('Định dạng hình ảnh chữ ký không hợp lệ (chỉ chấp nhận PNG, JPG hoặc WEBP).');
        }

        $rawBase64 = substr($signatureDataUrl, strpos($signatureDataUrl, ',') + 1);
        $ext = strtolower($type[1]);
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }

        $data = base64_decode($rawBase64, true);
        if ($data === false || strlen($data) > 1048576) {
            throw new \InvalidArgumentException('Dữ liệu chữ ký không hợp lệ hoặc vượt quá dung lượng cho phép.');
        }

        $imgInfo = @getimagesizefromstring($data);
        if ($imgInfo === false || ! in_array($imgInfo[2] ?? 0, [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP], true)) {
            throw new \InvalidArgumentException('Nội dung tệp chữ ký không phải là hình ảnh hợp lệ.');
        }

        $folder = 'invitations/'.$invitation->id.'/signatures';
        $safeSlug = Str::slug(strip_tags($guestName)) ?: 'guest';
        $fileName = 'sig_'.$safeSlug.'_'.Str::random(8).'.'.$ext;

        $uploadRes = $this->storage->upload($data, $folder, $fileName);

        return $uploadRes['url'];
    }

    /**
     * Save a standalone guest autograph (or attach it to an existing wish without overwriting).
     */
    public function saveAutograph(
        Invitation $invitation,
        string $guestName,
        string $signatureDataUrl,
        ?int $wishId = null,
        ?string $ip = null
    ): string {
        $url = $this->storeAutograph($invitation, $guestName, $signatureDataUrl);

        if ($invitation->wedding_id) {
            if ($wishId) {
                WeddingWish::where('wedding_id', $invitation->wedding_id)
                    ->where('id', $wishId)
                    ->whereNull('signature_url')
                    ->update(['signature_url' => $url]);
            } else {
                $cleanGuestName = Str::limit(strip_tags(trim($guestName)), 100, '') ?: 'Khách mời';
                WeddingWish::create([
                    'wedding_id' => $invitation->wedding_id,
                    'name' => $cleanGuestName,
                    'message' => 'Đã ký tên lưu bút chúc phúc',
                    'is_approved' => true,
                    'is_pinned' => false,
                    'is_private' => false,
                    'signature_url' => $url,
                    'ip_address' => $ip ?? request()?->ip(),
                ]);
            }
        }

        return $url;
    }

    /**
     * Store an optional attached photo for a wish (uploaded file or base64 data URI up to 5MB).
     */
    public function storePhotoWish(Invitation $invitation, ?UploadedFile $photoFile = null, ?string $photoDataUrl = null): ?string
    {
        $folder = "wishes_photos/{$invitation->slug}";

        if ($photoFile && $photoFile->isValid()) {
            try {
                $uploadRes = $this->storage->upload($photoFile, $folder);

                return $uploadRes['url'];
            } catch (\Throwable) {
                return null;
            }
        }

        if ($photoDataUrl && str_starts_with($photoDataUrl, 'data:image/')) {
            try {
                if (! preg_match('/^data:image\/(png|jpe?g|webp);base64,/i', $photoDataUrl, $matches)) {
                    return null;
                }

                $ext = strtolower($matches[1]);
                if ($ext === 'jpeg') {
                    $ext = 'jpg';
                }

                $base64 = substr($photoDataUrl, strpos($photoDataUrl, ',') + 1);
                $decoded = base64_decode($base64, true);
                if ($decoded === false || strlen($decoded) > 5 * 1024 * 1024) {
                    return null;
                }

                $imgInfo = @getimagesizefromstring($decoded);
                if ($imgInfo === false || ! in_array($imgInfo[2] ?? 0, [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP], true)) {
                    return null;
                }

                $filename = Str::uuid().'.'.$ext;
                $uploadRes = $this->storage->upload($decoded, $folder, $filename);

                return $uploadRes['url'];
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    /**
     * Persist a guest wish with optional autograph/photo, moderation policy, and webhook dispatch.
     *
     * @param  array{
     *     name: string,
     *     message: string,
     *     is_private?: bool,
     *     guest_token?: string|null,
     *     signature_data?: string|null,
     *     photo_data?: string|null
     * }  $input
     * @return array{wish: WeddingWish, message: string}
     */
    public function submitWish(
        Invitation $invitation,
        array $input,
        ?UploadedFile $photoFile = null,
        ?string $ip = null
    ): array {
        $wedding = $invitation->wedding;

        $guestId = null;
        if (! empty($input['guest_token']) && is_string($input['guest_token'])) {
            $matchedGuest = Guest::where('wedding_id', $wedding->id)
                ->where('guest_token', $input['guest_token'])
                ->first();
            if ($matchedGuest) {
                $guestId = $matchedGuest->id;
            }
        }

        $signatureUrl = null;
        $signatureData = $input['signature_data'] ?? null;
        if (is_string($signatureData) && str_starts_with($signatureData, 'data:image/')) {
            try {
                $signatureUrl = $this->storeAutograph($invitation, $input['name'], $signatureData);
            } catch (\Throwable) {
                $signatureUrl = null;
            }
        }

        $photoUrl = $this->storePhotoWish(
            $invitation,
            $photoFile,
            isset($input['photo_data']) && is_string($input['photo_data']) ? $input['photo_data'] : null
        );

        $isPrivate = ! empty($input['is_private']);
        $moderationMode = $wedding->getWishModerationMode();

        if ($isPrivate) {
            $isApproved = false;
            $successMsg = 'Lời chúc riêng tư của bạn đã được gửi an toàn tới cô dâu & chú rể!';
        } elseif ($moderationMode === 'manual') {
            $isApproved = false;
            $successMsg = 'Lời chúc của bạn đã được gửi thành công và đang chờ cô dâu & chú rể duyệt hiển thị!';
        } else {
            $isApproved = true;
            $successMsg = 'Cảm ơn bạn đã gửi lời chúc tốt đẹp tới cô dâu & chú rể!';
        }

        $wish = WeddingWish::create([
            'wedding_id' => $wedding->id,
            'guest_id' => $guestId,
            'name' => $input['name'],
            'message' => $input['message'],
            'is_approved' => $isApproved,
            'is_pinned' => false,
            'is_private' => $isPrivate,
            'signature_url' => $signatureUrl,
            'photo_url' => $photoUrl,
            'ip_address' => $ip,
        ]);

        try {
            $this->webhooks->dispatch($wedding, 'wish.created', [
                'wish_id' => $wish->id,
                'name' => $wish->name,
                'message' => $wish->message,
                'is_private' => $isPrivate,
                'is_approved' => $isApproved,
            ]);
        } catch (\Throwable) {
            // Non-blocking webhook fallback
        }

        return [
            'wish' => $wish,
            'message' => $successMsg,
        ];
    }

    /**
     * Single wish reply mutation shared across public invitation and studio moderation.
     */
    public function replyWish(Invitation $invitation, int $wishId, string $replyText): WeddingWish
    {
        $wish = WeddingWish::where('wedding_id', $invitation->wedding_id)
            ->findOrFail($wishId);

        $wish->update([
            'reply' => trim($replyText),
            'replied_at' => now(),
        ]);

        return $wish;
    }
}
