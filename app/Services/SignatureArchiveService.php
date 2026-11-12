<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Wedding;
use App\Models\WeddingWish;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SignatureArchiveService
{
    /**
     * Lấy dữ liệu tổng quan kho lưu trữ bút tích chữ ký tay.
     *
     * @return array{
     *     kpis: array{
     *         total_signatures: int,
     *         pinned_signatures: int,
     *         with_message_count: int,
     *         guest_signature_rate: float,
     *         recent_signature: ?array{name: string, time: string}
     *     },
     *     config: array{
     *         groom_signature_url: ?string,
     *         bride_signature_url: ?string,
     *         mosaic_theme: string,
     *         mosaic_title: string,
     *         mosaic_quote: string
     *     },
     *     signatures: array<int, array<string, mixed>>
     * }
     */
    public function getSignaturesSummary(Wedding $wedding, ?Invitation $invitation = null): array
    {
        $wishes = WeddingWish::with('guest')
            ->where('wedding_id', $wedding->id)
            ->whereNotNull('signature_url')
            ->where('signature_url', '!=', '')
            ->orderByDesc('is_pinned')
            ->orderByDesc('id')
            ->get();

        $totalSignatures = $wishes->count();
        $pinnedCount = $wishes->where('is_pinned', true)->count();
        $withMessageCount = $wishes->filter(fn ($w) => ! empty(trim((string) $w->message)))->count();

        $totalGuests = $wedding->guests()->count();
        $signatureRate = $totalGuests > 0 ? round(($totalSignatures / $totalGuests) * 100, 1) : 0.0;

        $latestWish = $wishes->first();
        $recentSignature = $latestWish ? [
            'name' => $latestWish->sender_name,
            'time' => $latestWish->created_at ? $latestWish->created_at->diffForHumans() : 'Vừa xong',
        ] : null;

        // Cấu hình từ custom_config của thiệp
        $invConfig = [];
        if ($invitation) {
            $invConfig = is_array($invitation->custom_config)
                ? $invitation->custom_config
                : (json_decode((string) $invitation->custom_config, true) ?: []);
        }

        $sigConfig = $invConfig['signature_archive'] ?? [];
        $config = [
            'groom_signature_url' => $sigConfig['groom_signature_url'] ?? null,
            'bride_signature_url' => $sigConfig['bride_signature_url'] ?? null,
            'mosaic_theme' => $sigConfig['mosaic_theme'] ?? 'cream_gold',
            'mosaic_title' => $sigConfig['mosaic_title'] ?? 'Bức Tranh Ký Tên Vàng — Tri Ân Quan Khách',
            'mosaic_quote' => $sigConfig['mosaic_quote'] ?? 'Mỗi nét mực ký tay là một mảnh ghép thiêng liêng đong đầy yêu thương cho hạnh phúc của chúng tôi.',
        ];

        $signatures = $wishes->map(fn (WeddingWish $w) => [
            'id' => $w->id,
            'name' => $w->sender_name,
            'message' => $w->message,
            'signature_url' => $w->signature_url,
            'is_pinned' => (bool) $w->is_pinned,
            'is_approved' => (bool) $w->is_approved,
            'guest_group' => $w->guest?->group_name ?? 'Quan Khách',
            'created_at_formatted' => $w->created_at ? $w->created_at->format('d/m/Y H:i') : '',
            'time_ago' => $w->created_at ? $w->created_at->diffForHumans() : 'Gần đây',
        ])->all();

        return [
            'kpis' => [
                'total_signatures' => $totalSignatures,
                'pinned_signatures' => $pinnedCount,
                'with_message_count' => $withMessageCount,
                'guest_signature_rate' => $signatureRate,
                'recent_signature' => $recentSignature,
            ],
            'config' => $config,
            'signatures' => $signatures,
        ];
    }

    /**
     * Cập nhật chữ ký tay của Cô Dâu & Chú Rể và cấu hình Tranh Chữ Ký.
     *
     * @param  array{
     *     groom_signature_data?: ?string,
     *     bride_signature_data?: ?string,
     *     mosaic_theme?: ?string,
     *     mosaic_title?: ?string,
     *     mosaic_quote?: ?string
     * }  $payload
     */
    public function updateCoupleSignatures(Invitation $invitation, array $payload): Invitation
    {
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?: []);

        $sigConfig = $config['signature_archive'] ?? [];

        // Xử lý lưu chữ ký Chú rể nếu gửi dạng dataURL (base64 PNG)
        if (! empty($payload['groom_signature_data'])) {
            $groomUrl = $this->storeSignatureBase64($payload['groom_signature_data'], 'groom_'.$invitation->id);
            if ($groomUrl) {
                $sigConfig['groom_signature_url'] = $groomUrl;
            }
        }

        // Xử lý lưu chữ ký Cô dâu nếu gửi dạng dataURL (base64 PNG)
        if (! empty($payload['bride_signature_data'])) {
            $brideUrl = $this->storeSignatureBase64($payload['bride_signature_data'], 'bride_'.$invitation->id);
            if ($brideUrl) {
                $sigConfig['bride_signature_url'] = $brideUrl;
            }
        }

        if (isset($payload['mosaic_theme'])) {
            $allowedThemes = ['cream_gold', 'ivory_charcoal', 'obsidian_night', 'dusty_rose'];
            $sigConfig['mosaic_theme'] = in_array($payload['mosaic_theme'], $allowedThemes, true)
                ? $payload['mosaic_theme']
                : 'cream_gold';
        }

        if (! empty($payload['mosaic_title'])) {
            $sigConfig['mosaic_title'] = trim(strip_tags((string) $payload['mosaic_title']));
        }

        if (! empty($payload['mosaic_quote'])) {
            $sigConfig['mosaic_quote'] = trim(strip_tags((string) $payload['mosaic_quote']));
        }

        $config['signature_archive'] = $sigConfig;
        $invitation->custom_config = $config;
        $invitation->save();

        return $invitation;
    }

    /**
     * Ghim hoặc bỏ ghim chữ ký trang trọng.
     */
    public function togglePin(Wedding $wedding, int $wishId): WeddingWish
    {
        $wish = WeddingWish::where('wedding_id', $wedding->id)
            ->whereNotNull('signature_url')
            ->findOrFail($wishId);

        $wish->is_pinned = ! $wish->is_pinned;
        $wish->save();

        return $wish;
    }

    /**
     * Xóa chữ ký (null hóa signature_url mà không xóa mất lời chúc của khách nếu có).
     */
    public function removeSignature(Wedding $wedding, int $wishId): WeddingWish
    {
        $wish = WeddingWish::where('wedding_id', $wedding->id)->findOrFail($wishId);
        $wish->signature_url = null;
        $wish->is_pinned = false;
        $wish->save();

        return $wish;
    }

    /**
     * Dữ liệu phục vụ trình chiếu toàn màn hình Bức Tranh Ký Tên Vàng (Mosaic Wall).
     *
     * @return array<string, mixed>
     */
    public function getMosaicData(Wedding $wedding, Invitation $invitation): array
    {
        $summary = $this->getSignaturesSummary($wedding, $invitation);

        $coupleNames = ($wedding->groom_name ?? 'Chú Rể').' & '.($wedding->bride_name ?? 'Cô Dâu');

        return [
            'wedding_title' => $coupleNames,
            'wedding_date' => $wedding->wedding_date,
            'config' => $summary['config'],
            'kpis' => $summary['kpis'],
            'signatures' => $summary['signatures'],
        ];
    }

    /**
     * Lưu chuỗi Base64 Data URL thành file ảnh PNG trong suốt.
     */
    protected function storeSignatureBase64(string $base64Data, string $prefix): ?string
    {
        if (! str_contains($base64Data, 'data:image')) {
            return null;
        }

        try {
            @[$type, $data] = explode(';', $base64Data);
            @[, $data] = explode(',', $data);

            if (empty($data)) {
                return null;
            }

            $decoded = base64_decode($data);
            if ($decoded === false) {
                return null;
            }

            $fileName = 'signatures/'.$prefix.'_'.Str::random(10).'.png';
            Storage::disk('public')->put($fileName, $decoded);

            return Storage::disk('public')->url($fileName);
        } catch (\Throwable) {
            return null;
        }
    }
}
