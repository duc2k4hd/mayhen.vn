<?php

namespace App\Services;

use App\Models\Wedding;
use App\Models\WeddingKeepsake;
use App\Models\WeddingTimeCapsule;
use Carbon\Carbon;
use Illuminate\Http\Response;

class AdminKeepsakeService
{
    /**
     * Lấy dữ liệu thống kê và danh sách tiệc cưới hậu sự kiện.
     */
    public function getKeepsakesOverview(array $filters = []): array
    {
        $now = Carbon::now();

        // 1. Thống kê KPI tổng thể
        $totalPastWeddings = Wedding::whereNotNull('wedding_date')
            ->where('wedding_date', '<=', $now)
            ->count();

        $activeKeepsakesCount = WeddingKeepsake::where('is_enabled', true)->count();

        $sealedCapsulesCount = WeddingTimeCapsule::where('is_sealed', true)->count();
        $unlockedCapsulesCount = WeddingTimeCapsule::where('is_sealed', false)->count();

        $exportedArchivesCount = WeddingKeepsake::whereNotNull('last_exported_at')->count();

        $stats = [
            'total_past_weddings' => $totalPastWeddings,
            'active_keepsakes' => $activeKeepsakesCount,
            'sealed_capsules' => $sealedCapsulesCount,
            'unlocked_capsules' => $unlockedCapsulesCount,
            'exported_archives' => $exportedArchivesCount,
        ];

        // 2. Query danh sách tiệc cưới kèm keepsake và time capsules
        $query = Wedding::with(['user:id,name,email', 'keepsake', 'timeCapsules'])
            ->withCount(['guests', 'wishes', 'gifts', 'timeCapsules']);

        // Bộ lọc từ khóa
        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('groom_name', 'like', "%{$keyword}%")
                    ->orWhere('bride_name', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
            });
        }

        // Bộ lọc trạng thái keepsake
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'enabled') {
                $query->whereHas('keepsake', fn ($kq) => $kq->where('is_enabled', true));
            } elseif ($filters['status'] === 'disabled') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('keepsake')
                        ->orWhereHas('keepsake', fn ($kq) => $kq->where('is_enabled', false));
                });
            } elseif ($filters['status'] === 'has_capsules') {
                $query->has('timeCapsules');
            } elseif ($filters['status'] === 'exported') {
                $query->whereHas('keepsake', fn ($kq) => $kq->whereNotNull('last_exported_at'));
            }
        }

        // Ưu tiên sắp xếp ngày cưới gần nhất
        $weddings = $query->orderByDesc('wedding_date')->paginate(15)->withQueryString();

        return [
            'stats' => $stats,
            'weddings' => $weddings,
        ];
    }

    /**
     * Lấy thông tin chi tiết hậu sự kiện & lưu niệm của một tiệc cưới.
     */
    public function getWeddingKeepsakeDetail(Wedding $wedding): array
    {
        // Tải hoặc tự động khởi tạo keepsake nếu chưa có
        $keepsake = $wedding->keepsake ?: WeddingKeepsake::create([
            'wedding_id' => $wedding->id,
            'is_enabled' => false,
            'thank_you_title' => 'Lời Cảm Ơn Tri Ân Từ Cô Dâu & Chú Rể',
            'thank_you_message' => 'Hôn lễ của chúng mình đã diễn ra thật trọn vẹn và ngập tràn hạnh phúc trong sự chứng kiến và chúc phúc của tất cả mọi người. Cảm ơn vì đã là một phần ký ức tuyệt vời nhất!',
            'show_wishes' => true,
            'show_photos' => true,
            'show_video' => true,
            'hide_rsvp' => true,
            'hide_gifts' => false,
            'anniversary_date' => $wedding->wedding_date,
        ]);

        $wedding->load([
            'user:id,name,email,phone',
            'invitation:id,wedding_id,slug,title,is_published',
            'timeCapsules' => fn ($q) => $q->orderBy('unlock_date'),
        ]);

        // Thống kê tổng kết sự kiện
        $guestsCount = $wedding->guests()->count();
        $attendedCount = $wedding->guests()->where('attendance_status', 'attending')->count();
        $wishesCount = $wedding->wishes()->count();
        $giftsCount = $wedding->gifts()->count();
        $totalGiftAmount = (float) $wedding->gifts()->sum('amount');

        $wrapUpSummary = [
            'total_guests' => $guestsCount,
            'attended_guests' => $attendedCount,
            'total_wishes' => $wishesCount,
            'total_gifts' => $giftsCount,
            'total_gift_amount' => $totalGiftAmount,
            'milestone_label' => $keepsake->milestone_label,
            'days_since_wedding' => $keepsake->daysSinceWedding(),
        ];

        return [
            'wedding' => $wedding,
            'keepsake' => $keepsake,
            'timeCapsules' => $wedding->timeCapsules,
            'summary' => $wrapUpSummary,
        ];
    }

    /**
     * Cập nhật cấu hình Trang Kỷ Niệm Hậu Sự Kiện.
     */
    public function updateKeepsakeSettings(Wedding $wedding, array $data, string $operator = 'Admin'): WeddingKeepsake
    {
        $keepsake = $wedding->keepsake ?: new WeddingKeepsake(['wedding_id' => $wedding->id]);

        $keepsake->fill([
            'is_enabled' => (bool) ($data['is_enabled'] ?? false),
            'thank_you_title' => trim($data['thank_you_title'] ?? 'Lời Cảm Ơn Tri Ân'),
            'thank_you_message' => trim($data['thank_you_message'] ?? ''),
            'video_url' => ! empty($data['video_url']) ? trim($data['video_url']) : null,
            'show_wishes' => (bool) ($data['show_wishes'] ?? false),
            'show_photos' => (bool) ($data['show_photos'] ?? false),
            'show_video' => (bool) ($data['show_video'] ?? false),
            'hide_rsvp' => (bool) ($data['hide_rsvp'] ?? false),
            'hide_gifts' => (bool) ($data['hide_gifts'] ?? false),
            'anniversary_date' => ! empty($data['anniversary_date']) ? Carbon::parse($data['anniversary_date']) : $wedding->wedding_date,
        ]);

        if (isset($data['highlight_photos']) && is_array($data['highlight_photos'])) {
            $keepsake->highlight_photos = array_values(array_filter($data['highlight_photos']));
        }

        $keepsake->save();

        return $keepsake;
    }

    /**
     * Bật/Tắt nhanh chế độ Kỷ niệm Hậu sự kiện.
     */
    public function toggleKeepsakeMode(Wedding $wedding, string $operator = 'Admin'): array
    {
        $keepsake = $wedding->keepsake ?: WeddingKeepsake::create([
            'wedding_id' => $wedding->id,
            'is_enabled' => false,
            'thank_you_title' => 'Lời Cảm Ơn Tri Ân',
            'thank_you_message' => 'Cảm ơn bạn đã luôn đồng hành cùng hạnh phúc của chúng mình!',
            'show_wishes' => true,
            'show_photos' => true,
            'show_video' => true,
            'hide_rsvp' => true,
            'hide_gifts' => false,
            'anniversary_date' => $wedding->wedding_date,
        ]);

        $keepsake->is_enabled = ! $keepsake->is_enabled;
        $keepsake->save();

        $statusText = $keepsake->is_enabled ? 'Đã kích hoạt Chế độ Kỷ niệm' : 'Đã tạm tắt Chế độ Kỷ niệm';

        return [
            'success' => true,
            'is_enabled' => $keepsake->is_enabled,
            'message' => "{$statusText} cho lễ cưới \"{$wedding->title}\".",
        ];
    }

    /**
     * Mở niêm phong bức thư trong Hộp Thư Kỷ Niệm Thời Gian (Time Capsule).
     */
    public function unlockCapsule(int $capsuleId, string $operator = 'Admin'): array
    {
        $capsule = WeddingTimeCapsule::findOrFail($capsuleId);

        $capsule->is_sealed = false;
        $capsule->unlocked_at = Carbon::now('Asia/Ho_Chi_Minh');
        $capsule->save();

        return [
            'success' => true,
            'capsule' => $capsule,
            'message' => "Đã mở niêm phong thành công bức thư \"{$capsule->title}\".",
        ];
    }

    /**
     * Xóa vĩnh viễn một bức thư trong Hộp thư thời gian.
     */
    public function deleteCapsule(int $capsuleId, string $operator = 'Admin'): bool
    {
        $capsule = WeddingTimeCapsule::findOrFail($capsuleId);

        return (bool) $capsule->delete();
    }

    /**
     * Xuất tệp gói đóng gói lưu trữ hậu sự kiện (Event Wrap-up Archive Metadata JSON).
     */
    public function exportWrapUpArchive(Wedding $wedding): Response
    {
        $keepsake = $wedding->keepsake;
        if ($keepsake) {
            $keepsake->last_exported_at = Carbon::now();
            $keepsake->save();
        }

        $guests = $wedding->guests()->select(['name', 'phone', 'group_name', 'table_name', 'attendance_status', 'guest_count'])->get();
        $wishes = $wedding->wishes()->select(['name', 'message', 'reply', 'is_approved', 'is_pinned', 'created_at'])->get();
        $gifts = $wedding->gifts()->select(['sender_name', 'amount', 'gift_type', 'message', 'is_acknowledged', 'created_at'])->get();
        $capsules = $wedding->timeCapsules()->select(['sender_name', 'recipient_name', 'title', 'content', 'milestone_years', 'unlock_date', 'is_sealed'])->get();

        $archiveData = [
            'platform' => 'Mây Hẹn (mayhen.vn)',
            'export_type' => 'Wedding Event Wrap-up Keepsake Archive',
            'exported_at' => Carbon::now()->toIso8601String(),
            'wedding' => [
                'id' => $wedding->id,
                'title' => $wedding->title,
                'groom_name' => $wedding->groom_name,
                'bride_name' => $wedding->bride_name,
                'wedding_date' => $wedding->wedding_date?->format('Y-m-d'),
                'venue_name' => $wedding->venue_name,
                'venue_address' => $wedding->venue_address,
            ],
            'keepsake_settings' => $keepsake ? [
                'is_enabled' => $keepsake->is_enabled,
                'milestone_label' => $keepsake->milestone_label,
                'thank_you_title' => $keepsake->thank_you_title,
                'thank_you_message' => $keepsake->thank_you_message,
                'video_url' => $keepsake->video_url,
                'anniversary_date' => $keepsake->anniversary_date?->format('Y-m-d'),
                'highlight_photos' => $keepsake->highlight_photos,
            ] : null,
            'statistics' => [
                'total_guests' => $guests->count(),
                'total_wishes' => $wishes->count(),
                'total_gifts' => $gifts->count(),
                'total_gift_amount' => (float) $gifts->sum('amount'),
                'total_time_capsules' => $capsules->count(),
            ],
            'guests' => $guests,
            'wishes' => $wishes,
            'gifts' => $gifts,
            'time_capsules' => $capsules,
        ];

        $jsonContent = json_encode($archiveData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fileName = 'mayhen_wedding_archive_'.$wedding->id.'_'.date('Ymd_His').'.json';

        return response($jsonContent, 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
