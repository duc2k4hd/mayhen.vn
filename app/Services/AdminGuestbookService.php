<?php

namespace App\Services;

use App\Models\Wedding;
use App\Models\WeddingWish;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminGuestbookService
{
    /**
     * Trích xuất toàn bộ dữ liệu Sổ Lưu Bút Kỷ Niệm & Chữ Ký Điện Tử theo tiệc cưới.
     */
    public function getGuestbookData(Wedding $wedding, array $filters = []): array
    {
        $allWishes = $wedding->wishes()->with(['guest:id,name,group_name,table_name'])->get();

        $totalCount = $allWishes->count();
        $approvedCount = $allWishes->where('is_approved', true)->count();
        $pendingCount = $allWishes->where('is_approved', false)->count();
        $pinnedCount = $allWishes->where('is_pinned', true)->count();
        $signatureCount = $allWishes->filter(fn (WeddingWish $w) => ! empty($w->signature_url))->count();
        $privateCount = $allWishes->where('is_private', true)->count();
        $repliedCount = $allWishes->filter(fn (WeddingWish $w) => ! empty($w->reply))->count();
        $replyRate = $totalCount > 0 ? round(($repliedCount / $totalCount) * 100, 1) : 0;

        $stats = [
            'total_wishes' => $totalCount,
            'approved_wishes' => $approvedCount,
            'pending_wishes' => $pendingCount,
            'pinned_wishes' => $pinnedCount,
            'signature_wishes' => $signatureCount,
            'private_wishes' => $privateCount,
            'replied_wishes' => $repliedCount,
            'reply_rate' => $replyRate,
        ];

        $wishes = $this->getFilteredWishes($wedding, $filters);

        return [
            'wedding' => $wedding,
            'stats' => $stats,
            'wishes' => $wishes,
        ];
    }

    /**
     * Lọc và phân trang danh sách lời chúc trong sổ lưu bút.
     */
    protected function getFilteredWishes(Wedding $wedding, array $filters = []): LengthAwarePaginator
    {
        $query = $wedding->wishes()->with(['guest:id,name,group_name,table_name']);

        // 1. Tìm kiếm từ khóa
        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('message', 'like', "%{$keyword}%")
                    ->orWhere('reply', 'like', "%{$keyword}%");
            });
        }

        // 2. Lọc trạng thái / danh mục
        $tab = $filters['tab'] ?? 'all';
        if ($tab === 'pinned') {
            $query->where('is_pinned', true);
        } elseif ($tab === 'signature') {
            $query->whereNotNull('signature_url')->where('signature_url', '!=', '');
        } elseif ($tab === 'pending') {
            $query->where('is_approved', false);
        } elseif ($tab === 'approved') {
            $query->where('is_approved', true);
        } elseif ($tab === 'private') {
            $query->where('is_private', true);
        } elseif ($tab === 'replied') {
            $query->whereNotNull('reply')->where('reply', '!=', '');
        }

        return $query->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(18)
            ->withQueryString();
    }

    /**
     * Ghim hoặc bỏ ghim lời chúc nổi bật.
     */
    public function togglePin(int $wishId, ?string $operatorName = null): array
    {
        return DB::transaction(function () use ($wishId, $operatorName) {
            $wish = WeddingWish::lockForUpdate()->findOrFail($wishId);
            $newStatus = ! $wish->is_pinned;

            $wish->update(['is_pinned' => $newStatus]);

            $message = $newStatus
                ? "Đã ghim nổi bật lời chúc từ \"{$wish->sender_name}\" lên đầu sổ lưu bút."
                : "Đã bỏ ghim lời chúc từ \"{$wish->sender_name}\".";

            AuditLogger::log(
                'toggle_wish_pin',
                'WeddingWish',
                $wish->id,
                $message,
                [
                    'wedding_id' => $wish->wedding_id,
                    'operator' => $operatorName,
                    'is_pinned' => $newStatus,
                ]
            );

            return [
                'success' => true,
                'wish' => $wish,
                'is_pinned' => $newStatus,
                'message' => $message,
            ];
        });
    }

    /**
     * Duyệt hoặc tạm ẩn lời chúc.
     */
    public function toggleApproval(int $wishId, ?string $operatorName = null): array
    {
        return DB::transaction(function () use ($wishId, $operatorName) {
            $wish = WeddingWish::lockForUpdate()->findOrFail($wishId);
            $newStatus = ! $wish->is_approved;

            $wish->update(['is_approved' => $newStatus]);

            $message = $newStatus
                ? "Đã duyệt hiển thị lời chúc từ \"{$wish->sender_name}\"."
                : "Đã tạm ẩn lời chúc từ \"{$wish->sender_name}\".";

            AuditLogger::log(
                'toggle_wish_approval',
                'WeddingWish',
                $wish->id,
                $message,
                [
                    'wedding_id' => $wish->wedding_id,
                    'operator' => $operatorName,
                    'is_approved' => $newStatus,
                ]
            );

            return [
                'success' => true,
                'wish' => $wish,
                'is_approved' => $newStatus,
                'message' => $message,
            ];
        });
    }

    /**
     * Chuyển đổi trạng thái gửi riêng tư / công khai của lời chúc.
     */
    public function togglePrivacy(int $wishId, ?string $operatorName = null): array
    {
        return DB::transaction(function () use ($wishId, $operatorName) {
            $wish = WeddingWish::lockForUpdate()->findOrFail($wishId);
            $newStatus = ! $wish->is_private;

            $wish->update(['is_private' => $newStatus]);

            $message = $newStatus
                ? "Đã chuyển lời chúc từ \"{$wish->sender_name}\" thành gửi riêng tư."
                : "Đã chuyển lời chúc từ \"{$wish->sender_name}\" thành hiển thị công khai.";

            AuditLogger::log(
                'toggle_wish_privacy',
                'WeddingWish',
                $wish->id,
                $message,
                [
                    'wedding_id' => $wish->wedding_id,
                    'operator' => $operatorName,
                    'is_private' => $newStatus,
                ]
            );

            return [
                'success' => true,
                'wish' => $wish,
                'is_private' => $newStatus,
                'message' => $message,
            ];
        });
    }

    /**
     * Viết lời đáp lễ / hồi đáp tri ân của cặp đôi.
     */
    public function replyWish(int $wishId, string $replyMessage, ?string $operatorName = null): WeddingWish
    {
        return DB::transaction(function () use ($wishId, $replyMessage, $operatorName) {
            $wish = WeddingWish::lockForUpdate()->findOrFail($wishId);

            $wish->update([
                'reply' => trim($replyMessage),
                'replied_at' => now(),
            ]);

            AuditLogger::log(
                'reply_wedding_wish',
                'WeddingWish',
                $wish->id,
                "Gửi lời đáp lễ tri ân tới \"{$wish->sender_name}\" trên tiệc cưới #{$wish->wedding_id}.",
                [
                    'wedding_id' => $wish->wedding_id,
                    'operator' => $operatorName,
                    'reply' => $replyMessage,
                ]
            );

            return $wish;
        });
    }

    /**
     * Xóa lời đáp lễ của cặp đôi.
     */
    public function deleteReply(int $wishId, ?string $operatorName = null): WeddingWish
    {
        return DB::transaction(function () use ($wishId, $operatorName) {
            $wish = WeddingWish::lockForUpdate()->findOrFail($wishId);

            $wish->update([
                'reply' => null,
                'replied_at' => null,
            ]);

            AuditLogger::log(
                'delete_wish_reply',
                'WeddingWish',
                $wish->id,
                "Xóa lời đáp lễ gửi tới \"{$wish->sender_name}\" trên tiệc cưới #{$wish->wedding_id}.",
                [
                    'wedding_id' => $wish->wedding_id,
                    'operator' => $operatorName,
                ]
            );

            return $wish;
        });
    }

    /**
     * Xóa vĩnh viễn lời chúc trong sổ lưu bút.
     */
    public function deleteWish(int $wishId, ?string $operatorName = null): bool
    {
        return DB::transaction(function () use ($wishId, $operatorName) {
            $wish = WeddingWish::findOrFail($wishId);
            $sender = $wish->sender_name;
            $weddingId = $wish->wedding_id;

            $wish->delete();

            AuditLogger::log(
                'delete_guestbook_wish',
                'WeddingWish',
                $wishId,
                "Xóa vĩnh viễn lời chúc từ \"{$sender}\" trên tiệc cưới #{$weddingId}.",
                [
                    'wedding_id' => $weddingId,
                    'operator' => $operatorName,
                    'sender' => $sender,
                ]
            );

            return true;
        });
    }

    /**
     * Xuất tệp Sổ Lưu Bút Kỷ Niệm định dạng CSV UTF-8 BOM.
     */
    public function exportGuestbookCsv(Wedding $wedding, array $filters = []): Response
    {
        $query = $wedding->wishes()->with(['guest']);

        if (! empty($filters['tab'])) {
            if ($filters['tab'] === 'pinned') {
                $query->where('is_pinned', true);
            } elseif ($filters['tab'] === 'signature') {
                $query->whereNotNull('signature_url')->where('signature_url', '!=', '');
            } elseif ($filters['tab'] === 'approved') {
                $query->where('is_approved', true);
            } elseif ($filters['tab'] === 'private') {
                $query->where('is_private', true);
            }
        }

        $wishes = $query->orderByDesc('is_pinned')->latest('id')->get();
        $filename = 'so-luu-but-'.Str::slug("{$wedding->groom_name}-{$wedding->bride_name}").'-'.now()->format('Ymd-His').'.csv';

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'STT',
            'Mã Lời Chúc',
            'Người Gửi Lời Chúc',
            'Nhóm Quan Khách',
            'Bàn Tiệc',
            'Nội Dung Lời Chúc',
            'Lời Đáp Lễ Của Cặp Đôi',
            'Chữ Ký Điện Tử (URL)',
            'Đã Ghim Nổi Bật',
            'Trạng Thái Duyệt',
            'Gửi Riêng Tư',
            'Thời Gian Gửi',
            'Thời Gian Đáp Lễ',
        ]);

        $index = 1;
        foreach ($wishes as $w) {
            $group = $w->guest?->group_name ?? 'Khách chung';
            $table = $w->guest?->table_name ?? '—';
            $isPinned = $w->is_pinned ? 'Đã ghim' : 'Không';
            $isApproved = $w->is_approved ? 'Đã duyệt' : 'Chờ duyệt / Ẩn';
            $isPrivate = $w->is_private ? 'Riêng tư' : 'Công khai';
            $created = $w->created_at ? $w->created_at->format('d/m/Y H:i:s') : '—';
            $replied = $w->replied_at ? $w->replied_at->format('d/m/Y H:i:s') : '—';

            fputcsv($handle, CsvSanitizerService::row([
                $index++,
                $w->id,
                $w->sender_name,
                $group,
                $table,
                $w->message,
                $w->reply ?: '',
                $w->signature_url ?: '',
                $isPinned,
                $isApproved,
                $isPrivate,
                $created,
                $replied,
            ]));
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}

