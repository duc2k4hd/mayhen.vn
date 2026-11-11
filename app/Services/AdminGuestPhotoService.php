<?php

namespace App\Services;

use App\Models\GuestPhoto;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminGuestPhotoService
{
    /**
     * Lấy danh sách ảnh khách gửi và thống kê 4 chỉ số KPI kiểm duyệt.
     */
    public function getGuestPhotosOverview(array $filters = []): array
    {
        // 1. Thống kê KPI
        $totalPhotos = GuestPhoto::count();
        $pendingPhotos = GuestPhoto::where('is_approved', false)->count();
        $approvedPhotos = GuestPhoto::where('is_approved', true)->count();
        $totalBytes = (int) GuestPhoto::sum('file_size');
        $totalStorageMb = round($totalBytes / (1024 * 1024), 2);

        $stats = [
            'total_photos' => $totalPhotos,
            'pending_photos' => $pendingPhotos,
            'approved_photos' => $approvedPhotos,
            'total_storage_mb' => $totalStorageMb,
            'total_size_mb' => $totalStorageMb,
        ];

        // 2. Query danh sách
        $query = GuestPhoto::with(['wedding.user', 'guest'])
            ->latest();

        // Lọc từ khóa
        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function ($q) use ($keyword) {
                $q->where('uploader_name', 'like', "%{$keyword}%")
                    ->orWhere('caption', 'like', "%{$keyword}%")
                    ->orWhere('ip_address', 'like', "%{$keyword}%")
                    ->orWhereHas('wedding', function ($wq) use ($keyword) {
                        $wq->where('title', 'like', "%{$keyword}%")
                            ->orWhere('groom_name', 'like', "%{$keyword}%")
                            ->orWhere('bride_name', 'like', "%{$keyword}%");
                    });
            });
        }

        // Lọc theo tiệc cưới
        if (! empty($filters['wedding_id'])) {
            $query->where('wedding_id', $filters['wedding_id']);
        }

        // Lọc theo trạng thái kiểm duyệt
        if (isset($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'approved') {
                $query->where('is_approved', true);
            } elseif ($filters['status'] === 'pending') {
                $query->where('is_approved', false);
            }
        }

        $photos = $query->paginate(16)->withQueryString();

        return [
            'stats' => $stats,
            'photos' => $photos,
        ];
    }

    /**
     * Chuyển đổi trạng thái duyệt 1-chạm cho một ảnh.
     */
    public function toggleApproval(int $id, string $operator = 'Admin'): array
    {
        $photo = GuestPhoto::findOrFail($id);
        $photo->is_approved = ! $photo->is_approved;
        $photo->save();

        $statusText = $photo->is_approved ? 'Đã duyệt hiển thị' : 'Đã tạm ẩn';

        return [
            'success' => true,
            'is_approved' => $photo->is_approved,
            'message' => "{$statusText} ảnh của \"{$photo->uploader_name}\" thành công.",
        ];
    }

    /**
     * Duyệt hiển thị hàng loạt ảnh được chọn.
     */
    public function batchApprove(array $ids, string $operator = 'Admin'): int
    {
        return GuestPhoto::whereIn('id', $ids)->update(['is_approved' => true]);
    }

    /**
     * Xóa một ảnh của khách (kèm xóa file vật lý nếu có).
     */
    public function deletePhoto(int $id, string $operator = 'Admin'): bool
    {
        $photo = GuestPhoto::findOrFail($id);

        if ($photo->photo_url && (str_starts_with($photo->photo_url, '/storage/') || str_starts_with($photo->photo_url, 'storage/'))) {
            $relativePath = ltrim(Str::after($photo->photo_url, 'storage/'), '/');
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        return (bool) $photo->delete();
    }

    /**
     * Xóa hàng loạt ảnh được chọn.
     */
    public function batchDelete(array $ids, string $operator = 'Admin'): int
    {
        $photos = GuestPhoto::whereIn('id', $ids)->get();
        $count = 0;

        foreach ($photos as $photo) {
            if ($this->deletePhoto($photo->id, $operator)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Lấy danh sách ảnh đã duyệt phục vụ Live Slideshow máy chiếu tiệc cưới.
     */
    public function getSlideshowPhotos(?int $weddingId = null, int $limit = 50): Collection
    {
        $query = GuestPhoto::with(['wedding'])
            ->where('is_approved', true)
            ->latest();

        if ($weddingId) {
            $query->where('wedding_id', $weddingId);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Xuất tệp báo cáo danh sách ảnh khách gửi CSV UTF-8 BOM.
     */
    public function exportPhotosCsv(array $filters = []): Response
    {
        $query = GuestPhoto::with(['wedding', 'guest'])->latest();

        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function ($q) use ($keyword) {
                $q->where('uploader_name', 'like', "%{$keyword}%")
                    ->orWhere('caption', 'like', "%{$keyword}%");
            });
        }

        if (! empty($filters['wedding_id'])) {
            $query->where('wedding_id', $filters['wedding_id']);
        }

        if (isset($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'approved') {
                $query->where('is_approved', true);
            } elseif ($filters['status'] === 'pending') {
                $query->where('is_approved', false);
            }
        }

        $photos = $query->get();

        $headers = [
            'ID Ảnh',
            'Tiệc Cưới',
            'Người Đăng',
            'Khách Danh Bạ',
            'Đường Dẫn Ảnh',
            'Dung Lượng (KB)',
            'Lời Chúc / Chú Thích',
            'Trạng Thái Duyệt',
            'Địa Chỉ IP',
            'Thời Gian Tải Lên',
        ];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $headers);

        foreach ($photos as $item) {
            $sizeKb = round($item->file_size / 1024, 1);
            fputcsv($handle, [
                $item->id,
                $item->wedding?->title ?: '—',
                $item->uploader_name,
                $item->guest?->name ?: 'Khách vãng lai',
                $item->photo_url,
                $sizeKb,
                $item->caption ?: '—',
                $item->is_approved ? 'Đã duyệt' : 'Chờ duyệt',
                $item->ip_address ?: '—',
                $item->created_at ? $item->created_at->format('d/m/Y H:i:s') : '—',
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        $fileName = 'mayhen_guest_photos_'.date('Ymd_His').'.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
