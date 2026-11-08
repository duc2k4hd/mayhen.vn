<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\GuestPhoto;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Rules\NoProfanity;
use App\Services\QrCodeService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class PhotoDropController extends Controller
{
    /**
     * Màn hình Photo Drop dành cho quan khách tại bàn tiệc (/photo-drop/{slug}).
     */
    public function show(Request $request, string $slug): View|RedirectResponse
    {
        $invitation = Invitation::with(['wedding'])->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404, 'Không tìm thấy thông tin đám cưới.');

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return view('client.pages.invitation-locked', [
                'invitation' => $invitation,
                'lockReason' => $config['lock_reason'] ?? 'Thiệp cưới tạm thời bị khóa do yêu cầu từ Quản trị viên.',
            ]);
        }

        $photoDropConfig = $config['photo_drop'] ?? [];
        $isClosed = isset($photoDropConfig['enabled']) && ! $photoDropConfig['enabled'];

        // Tự động nhận diện khách mời nếu truy cập qua token
        $guest = null;
        if ($token = $request->query('token')) {
            $guest = $wedding->guests()->where('guest_token', $token)->first();
        }

        // Danh sách 12 ảnh mới nhất đã duyệt để tạo cảm hứng cộng đồng
        $recentPhotos = $wedding->guestPhotos()
            ->approved()
            ->latest()
            ->limit(12)
            ->get();

        return view('client.pages.photo-drop', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'guest' => $guest,
            'isClosed' => $isClosed,
            'recentPhotos' => $recentPhotos,
            'totalPhotosCount' => $wedding->guestPhotos()->approved()->count(),
        ]);
    }

    /**
     * Xử lý tải lên nhiều ảnh từ khách mời (Tối đa 10 ảnh / lần, 12MB / ảnh).
     */
    public function upload(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with(['wedding'])->where('slug', $slug)->firstOrFail();
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404);

        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['is_locked'])) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới đang tạm thời bị khóa bởi Quản trị viên.',
            ], 403);
        }

        $photoDropConfig = $config['photo_drop'] ?? [];
        if (isset($photoDropConfig['enabled']) && ! $photoDropConfig['enabled']) {
            return response()->json([
                'success' => false,
                'message' => 'Hộp nhận ảnh kỷ niệm đã tạm thời đóng nhận thêm ảnh.',
            ], 403);
        }

        $validated = $request->validate([
            'uploader_name' => ['required', 'string', 'max:150', new NoProfanity],
            'caption' => ['nullable', 'string', 'max:255', new NoProfanity],
            'guest_token' => ['nullable', 'string', 'max:64'],
            'photos' => ['required', 'array', 'min:1', 'max:10'],
            'photos.*' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,webp,heic,heif',
                'max:12288', // 12MB
            ],
        ], [
            'uploader_name.required' => 'Vui lòng nhập tên của bạn để cặp đôi biết ai gửi ảnh nhé.',
            'photos.required' => 'Vui lòng chọn ít nhất 1 bức ảnh.',
            'photos.max' => 'Mỗi lần gửi tối đa 10 bức ảnh.',
            'photos.*.mimes' => 'Chỉ chấp nhận các định dạng ảnh hợp lệ (JPG, PNG, WebP, HEIC).',
            'photos.*.max' => 'Kích thước mỗi ảnh không được vượt quá 12MB.',
        ]);

        $uploaderName = strip_tags(trim($validated['uploader_name']));
        $caption = ! empty($validated['caption']) ? strip_tags(trim($validated['caption'])) : null;

        $guest = null;
        if (! empty($validated['guest_token'])) {
            $guest = $wedding->guests()->where('guest_token', $validated['guest_token'])->first();
        }

        // Kiểm tra chế độ kiểm duyệt (mặc định tự động duyệt nếu không cấu hình bắt buộc duyệt)
        $autoApprove = $photoDropConfig['auto_approve'] ?? true;

        $uploadedRecords = [];
        $storageDir = "guest_photos/{$wedding->id}";

        foreach ($request->file('photos') as $file) {
            $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'], true)) {
                $extension = 'jpg';
            }

            $fileName = Str::uuid().'.'.$extension;
            $path = $file->storeAs($storageDir, $fileName, 'public');

            $photoRecord = GuestPhoto::create([
                'wedding_id' => $wedding->id,
                'guest_id' => $guest?->id,
                'uploader_name' => $uploaderName,
                'photo_url' => Storage::disk('public')->url($path),
                'file_size' => $file->getSize(),
                'caption' => $caption,
                'is_approved' => $autoApprove,
                'ip_address' => $request->ip(),
            ]);

            $uploadedRecords[] = [
                'id' => $photoRecord->id,
                'url' => $photoRecord->photo_url,
                'uploader' => $photoRecord->uploader_name,
            ];
        }

        $message = $autoApprove
            ? 'Đã gửi thành công '.count($uploadedRecords).' bức ảnh kỷ niệm tặng cô dâu chú rể!'
            : 'Đã gửi thành công '.count($uploadedRecords).' bức ảnh. Ảnh sẽ xuất hiện ngay sau khi cặp đôi duyệt nhé!';

        return response()->json([
            'success' => true,
            'message' => $message,
            'count' => count($uploadedRecords),
            'photos' => $uploadedRecords,
        ]);
    }

    /**
     * Quản lý album ảnh của khách dành cho cặp đôi (/tai-khoan/thiep/{id}/album-khach).
     */
    public function manage(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding'])->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $query = $wedding->guestPhotos();

        // Lọc theo trạng thái
        $filterStatus = $request->query('status');
        if ($filterStatus === 'approved') {
            $query->approved();
        } elseif ($filterStatus === 'pending') {
            $query->pending();
        }

        $photos = $query->latest()->paginate(24)->withQueryString();

        // Thống kê tổng quan
        $allPhotos = $wedding->guestPhotos()->get();
        $totalPhotos = $allPhotos->count();
        $approvedCount = $allPhotos->where('is_approved', true)->count();
        $pendingCount = $allPhotos->where('is_approved', false)->count();
        $totalBytes = (int) $allPhotos->sum('file_size');
        $totalSizeMB = round($totalBytes / (1024 * 1024), 1);

        $dropUrl = route('photo_drop.show', $invitation->slug);
        $qrCodeSvg = QrCodeService::svg($dropUrl, 200, '#1F1D1A', '#FAF8F5');

        return view('client.pages.account.guest-photos', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'photos' => $photos,
            'totalPhotos' => $totalPhotos,
            'approvedCount' => $approvedCount,
            'pendingCount' => $pendingCount,
            'totalSizeMB' => $totalSizeMB,
            'filterStatus' => $filterStatus,
            'dropUrl' => $dropUrl,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    /**
     * Bật / Tắt trạng thái kiểm duyệt của một bức ảnh.
     */
    public function toggleApproval(int $invitationId, int $photoId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $photo = $wedding->guestPhotos()->findOrFail($photoId);
        $photo->update(['is_approved' => ! $photo->is_approved]);

        $statusText = $photo->is_approved ? 'Đã cho phép hiển thị ảnh' : 'Đã ẩn ảnh khỏi album công khai';

        return back()->with('success', "{$statusText} thành công!");
    }

    /**
     * Duyệt toàn bộ ảnh đang chờ kiểm duyệt cùng một lúc.
     */
    public function approveAll(int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $updatedCount = $wedding->guestPhotos()->pending()->update(['is_approved' => true]);

        return back()->with('success', "Đã duyệt và cho phép hiển thị {$updatedCount} bức ảnh thành công!");
    }

    /**
     * Xóa vĩnh viễn ảnh khỏi hệ thống.
     */
    public function destroy(int $invitationId, int $photoId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $photo = $wedding->guestPhotos()->findOrFail($photoId);

        // Xóa file vật lý
        if ($photo->photo_url) {
            $relativePath = str_replace(Storage::disk('public')->url(''), '', $photo->photo_url);
            $relativePath = ltrim($relativePath, '/');
            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            }
        }

        $photo->delete();

        return back()->with('success', 'Đã xóa bức ảnh khỏi album kỷ niệm.');
    }

    /**
     * Đóng gói toàn bộ ảnh của khách tải lên thành 1 file ZIP tải về máy tính.
     */
    public function downloadZip(int $invitationId): BinaryFileResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $photos = $wedding->guestPhotos()->get();
        if ($photos->isEmpty()) {
            return back()->with('error', 'Chưa có bức ảnh nào để tải xuống.');
        }

        $zipFileName = 'album-anh-khach-moi-'.Str::slug($wedding->groom_name.'-'.$wedding->bride_name).'-'.now()->format('Ymd-His').'.zip';
        $tempPath = storage_path('app/temp-'.$zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Không thể khởi tạo file nén ZIP.');
        }

        $addedCount = 0;
        foreach ($photos as $index => $photo) {
            $relativePath = str_replace(Storage::disk('public')->url(''), '', $photo->photo_url);
            $relativePath = ltrim($relativePath, '/');

            if (Storage::disk('public')->exists($relativePath)) {
                $fileContent = Storage::disk('public')->get($relativePath);
                $ext = pathinfo($relativePath, PATHINFO_EXTENSION) ?: 'jpg';
                $safeUploader = Str::slug($photo->uploader_name) ?: 'khach';
                $entryName = sprintf('%03d_%s_%s.%s', $index + 1, $safeUploader, substr($photo->id, 0, 8), $ext);
                $zip->addFromString($entryName, $fileContent);
                $addedCount++;
            }
        }

        $zip->close();

        if ($addedCount === 0 || ! file_exists($tempPath)) {
            return back()->with('error', 'Không tìm thấy file ảnh gốc để đóng gói.');
        }

        return response()->download($tempPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    private function authorizeOwner(Wedding $wedding): void
    {
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        $ownedSlugs = (array) request()->session()->get('owned_invitation_slugs', []);
        if ($ownedSlugs !== [] && $wedding->invitations()->whereIn('slug', $ownedSlugs)->exists()) {
            return;
        }

        abort(404);
    }
}
