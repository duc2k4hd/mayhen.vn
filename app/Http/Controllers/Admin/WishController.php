<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\WeddingWish;
use App\Services\AuditLogger;
use App\Services\CsvSanitizerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WishController extends Controller
{
    /**
     * Common sensitive / spam keywords to flag.
     */
    public const SPAM_KEYWORDS = [
        'casino', 'cá độ', 'tai xiu', 'tài xỉu', 'bet88', 'kubet', 'shbet',
        'lừa đảo', 'sex', 'đụ', 'đm', 'vcl', 'clgt', 'vay tiền', 'tín dụng đen',
    ];

    /**
     * Display centralized guestbook wishes with moderation controls.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $weddingId = $request->input('wedding_id');

        $query = WeddingWish::query()->with([
            'wedding:id,user_id,groom_name,bride_name',
            'wedding.invitations:id,wedding_id,slug,is_published',
        ]);

        // 1. Text Search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('wedding', function ($wq) use ($search) {
                        $wq->where('groom_name', 'like', "%{$search}%")
                            ->orWhere('bride_name', 'like', "%{$search}%");
                    });
            });
        }

        // 2. Status Filter
        if ($status === 'approved') {
            $query->where('is_approved', true);
        } elseif ($status === 'pending') {
            $query->where('is_approved', false);
        } elseif ($status === 'flagged') {
            $query->where(function ($q) {
                foreach (self::SPAM_KEYWORDS as $kw) {
                    $q->orWhere('message', 'like', "%{$kw}%");
                }
            });
        }

        // 3. Wedding Filter
        if (! empty($weddingId)) {
            $query->where('wedding_id', (int) $weddingId);
        }

        $wishes = $query->latest('id')->paginate(15)->withQueryString();

        // System KPIs
        $totalWishes = WeddingWish::count();
        $approvedCount = WeddingWish::where('is_approved', true)->count();
        $pendingCount = WeddingWish::where('is_approved', false)->count();

        $totalGuests = Guest::count();
        $totalRsvps = GuestResponse::count();
        $attendingCount = GuestResponse::where('attending_status', 'attending')->count();
        $attendanceRate = $totalRsvps > 0 ? round(($attendingCount / $totalRsvps) * 100) : 0;

        return view('admin.pages.wishes.index', [
            'wishes' => $wishes,
            'currentSearch' => $search,
            'currentStatus' => $status,
            'currentWeddingId' => $weddingId,
            'totalWishes' => $totalWishes,
            'approvedCount' => $approvedCount,
            'pendingCount' => $pendingCount,
            'totalGuests' => $totalGuests,
            'totalRsvps' => $totalRsvps,
            'attendingCount' => $attendingCount,
            'attendanceRate' => $attendanceRate,
            'spamKeywords' => self::SPAM_KEYWORDS,
        ]);
    }

    /**
     * Toggle approval status of a wedding wish.
     */
    public function toggleApproval(int $id): RedirectResponse
    {
        $wish = WeddingWish::findOrFail($id);
        $newStatus = ! $wish->is_approved;

        $wish->update(['is_approved' => $newStatus]);

        AuditLogger::log(
            'toggle_wish_approval',
            'WeddingWish',
            $wish->id,
            ($newStatus ? 'Duyệt hiển thị' : 'Tạm ẩn')." lời chúc của '{$wish->sender_name}' trên thiệp cưới #{$wish->wedding_id}",
            ['is_approved' => $newStatus, 'wedding_id' => $wish->wedding_id]
        );

        $msg = $newStatus
            ? "Đã duyệt hiển thị lời chúc của \"{$wish->sender_name}\"."
            : "Đã tạm ẩn lời chúc của \"{$wish->sender_name}\" khỏi thiệp cưới.";

        return back()->with('success', $msg);
    }

    /**
     * Delete an inappropriate wish permanently.
     */
    public function destroy(int $id): RedirectResponse
    {
        $wish = WeddingWish::findOrFail($id);
        $sender = $wish->sender_name;
        $weddingId = $wish->wedding_id;
        $wish->delete();

        AuditLogger::log(
            'delete_wish',
            'WeddingWish',
            $id,
            "Xóa vĩnh viễn lời chúc từ '{$sender}' trên thiệp cưới #{$weddingId}",
            ['wedding_id' => $weddingId, 'sender_name' => $sender]
        );

        return back()->with('success', "Đã xóa vĩnh viễn lời chúc từ \"{$sender}\".");
    }

    /**
     * Batch approve multiple pending wishes (Wave 05).
     */
    public function batchApprove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wish_ids' => 'required|array',
            'wish_ids.*' => 'integer|exists:wedding_wishes,id',
        ]);

        $ids = $validated['wish_ids'];
        $count = WeddingWish::whereIn('id', $ids)->where('is_approved', false)->update(['is_approved' => true]);

        AuditLogger::log(
            'batch_approve_wishes',
            'WeddingWish',
            0,
            "Duyệt hàng loạt {$count} lời chúc mừng quan khách",
            ['wish_ids' => $ids, 'count' => $count]
        );

        return back()->with('success', "Đã duyệt hàng loạt {$count} lời chúc thành công.");
    }

    /**
     * Batch delete inappropriate wishes (Wave 05).
     */
    public function batchDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wish_ids' => 'required|array',
            'wish_ids.*' => 'integer|exists:wedding_wishes,id',
        ]);

        $ids = $validated['wish_ids'];
        $count = WeddingWish::whereIn('id', $ids)->delete();

        AuditLogger::log(
            'batch_delete_wishes',
            'WeddingWish',
            0,
            "Xóa hàng loạt {$count} lời chúc vi phạm",
            ['wish_ids' => $ids, 'count' => $count]
        );

        return back()->with('success', "Đã xóa hàng loạt {$count} lời chúc thành công.");
    }

    /**
     * Display centralized RSVP report across the platform.
     */
    public function rsvps(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');

        $query = GuestResponse::query()->with([
            'wedding:id,user_id,groom_name,bride_name',
            'wedding.invitations:id,wedding_id,slug',
            'guest',
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('wishes', 'like', "%{$search}%");
            });
        }

        if (! empty($status)) {
            $query->where('attending_status', $status);
        }

        $rsvps = $query->latest('id')->paginate(20)->withQueryString();

        $totalRsvps = GuestResponse::count();
        $attendingCount = GuestResponse::where('attending_status', 'attending')->count();
        $declinedCount = GuestResponse::where('attending_status', 'declined')->count();
        $uncertainCount = GuestResponse::where('attending_status', 'uncertain')->count();

        return view('admin.pages.wishes.rsvps', [
            'rsvps' => $rsvps,
            'currentSearch' => $search,
            'currentStatus' => $status,
            'totalRsvps' => $totalRsvps,
            'attendingCount' => $attendingCount,
            'declinedCount' => $declinedCount,
            'uncertainCount' => $uncertainCount,
        ]);
    }

    /**
     * Export wedding wishes as UTF-8 BOM CSV.
     */
    public function exportWishesCsv(Request $request): StreamedResponse
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $weddingId = $request->input('wedding_id');

        $query = WeddingWish::query()->with([
            'wedding:id,user_id,groom_name,bride_name',
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('wedding', function ($wq) use ($search) {
                        $wq->where('groom_name', 'like', "%{$search}%")
                            ->orWhere('bride_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status === 'approved') {
            $query->where('is_approved', true);
        } elseif ($status === 'pending') {
            $query->where('is_approved', false);
        } elseif ($status === 'flagged') {
            $query->where(function ($q) {
                foreach (self::SPAM_KEYWORDS as $kw) {
                    $q->orWhere('message', 'like', "%{$kw}%");
                }
            });
        }

        if (! empty($weddingId)) {
            $query->where('wedding_id', (int) $weddingId);
        }

        $wishes = $query->latest('id')->get();
        $filename = 'danh-sach-loi-chuc-mayhen-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($wishes) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'STT',
                'Mã Lời Chúc',
                'Người Gửi',
                'Mối Quan Hệ',
                'Nội Dung Lời Chúc',
                'Lời Cảm Ơn Của Cặp Đôi',
                'Cặp Đôi Hôn Lễ',
                'Trạng Thái Kiểm Duyệt',
                'Thời Gian Gửi',
            ]);

            $index = 1;
            foreach ($wishes as $wish) {
                $couple = $wish->wedding ? ($wish->wedding->groom_name.' & '.$wish->wedding->bride_name) : 'N/A';
                $statusLabel = $wish->is_approved ? 'Đã duyệt hiển thị' : 'Chờ duyệt / Tạm ẩn';

                fputcsv($handle, CsvSanitizerService::row([
                    $index++,
                    $wish->id,
                    $wish->sender_name,
                    $wish->relationship ?? 'Khách mời',
                    $wish->message,
                    $wish->couple_reply ?? '',
                    $couple,
                    $statusLabel,
                    $wish->created_at ? $wish->created_at->format('d/m/Y H:i:s') : 'N/A',
                ]));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Export centralized RSVPs as UTF-8 BOM CSV.
     */
    public function exportRsvpsCsv(Request $request): StreamedResponse
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');

        $query = GuestResponse::query()->with([
            'wedding:id,user_id,groom_name,bride_name',
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('wishes', 'like', "%{$search}%");
            });
        }

        if (! empty($status)) {
            $query->where('attending_status', $status);
        }

        $rsvps = $query->latest('id')->get();
        $filename = 'bao-cao-rsvp-quan-khach-mayhen-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rsvps) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'STT',
                'Mã RSVP',
                'Tên Khách Mời',
                'Số Điện Thoại',
                'Cặp Đôi Hôn Lễ',
                'Phản Hồi Tham Dự',
                'Số Người Lớn',
                'Số Trẻ Em',
                'Ăn Chay / Yêu Cầu Riêng',
                'Lời Nhắn Gửi Cặp Đôi',
                'Thời Gian Phản Hồi',
            ]);

            $index = 1;
            foreach ($rsvps as $rsvp) {
                $couple = $rsvp->wedding ? ($rsvp->wedding->groom_name.' & '.$rsvp->wedding->bride_name) : 'N/A';
                $statusLabel = match ($rsvp->attending_status) {
                    'attending' => 'Sẽ tham dự',
                    'declined' => 'Không thể đến',
                    'uncertain' => 'Chưa chắc chắn',
                    default => $rsvp->attending_status,
                };

                fputcsv($handle, CsvSanitizerService::row([
                    $index++,
                    $rsvp->id,
                    $rsvp->guest_name,
                    $rsvp->phone ?? 'Chưa cập nhật',
                    $couple,
                    $statusLabel,
                    $rsvp->adults_count ?? 1,
                    $rsvp->children_count ?? 0,
                    $rsvp->dietary_requirements ?? 'Không có',
                    $rsvp->wishes ?? '',
                    $rsvp->created_at ? $rsvp->created_at->format('d/m/Y H:i:s') : 'N/A',
                ]));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
