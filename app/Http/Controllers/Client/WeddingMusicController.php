<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\WeddingMusicService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WeddingMusicController extends Controller
{
    /**
     * Xác thực quyền sở hữu thiệp cưới của người dùng đang đăng nhập.
     */
    protected function authorizeInvitationOwner(Invitation $invitation): void
    {
        $user = Auth::user();
        if (! $user || ! $invitation->wedding || $invitation->wedding->user_id !== $user->id) {
            abort(403, 'Bạn không có quyền thao tác trên thiệp cưới này.');
        }
    }

    /**
     * Màn hình Quản trị Âm nhạc Hôn lễ & Thư viện Bản quyền Quiet Luxury.
     */
    public function index(Request $request, int $invitationId, WeddingMusicService $musicService): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $curatedTracks = WeddingMusicService::getCuratedTracks();
        $musicConfig = WeddingMusicService::resolveMusicConfig($invitation);
        $fullCatalog = $musicService->getCatalog();

        return view('client.pages.account.music.index', compact('invitation', 'curatedTracks', 'musicConfig', 'fullCatalog'));
    }

    /**
     * Cập nhật cấu hình âm nhạc hôn lễ (Bật/tắt, chọn bài hát, âm lượng, autoplay).
     */
    public function update(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'track_id' => 'required|string|max:100',
            'volume' => 'required|numeric|min:0.1|max:1.0',
            'autoplay' => 'nullable|boolean',
            'loop' => 'nullable|boolean',
            'fade_in' => 'nullable|boolean',
            'pause_on_video' => 'nullable|boolean',
            'floating_dock' => 'nullable|boolean',
            'is_custom_upload' => 'nullable|boolean',
            'title' => 'nullable|string|max:255',
            'artist' => 'nullable|string|max:255',
            'url' => 'nullable|string|max:500',
        ]);

        $validated['enabled'] = $request->boolean('enabled');
        $validated['autoplay'] = $request->boolean('autoplay');
        $validated['loop'] = $request->boolean('loop');
        $validated['fade_in'] = $request->boolean('fade_in');
        $validated['pause_on_video'] = $request->boolean('pause_on_video');
        $validated['floating_dock'] = $request->boolean('floating_dock');

        $updatedConfig = WeddingMusicService::updateMusicSettings($invitation, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã lưu cấu hình âm nhạc hôn lễ thành công!',
                'data' => $updatedConfig,
            ]);
        }

        return redirect()->route('account.music.index', $invitation->id)
            ->with('success', 'Đã lưu cấu hình âm nhạc hôn lễ thành công!');
    }

    /**
     * Tải lên tệp âm thanh riêng do cặp đôi tự cung cấp (MP3, M4A, WAV, AAC, OGG).
     */
    public function upload(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $request->validate([
            'audio_file' => 'required|file|mimes:mp3,m4a,wav,aac,ogg,audio/mpeg,audio/mp4,audio/wav,audio/aac,audio/ogg|max:15360',
            'custom_title' => 'nullable|string|max:150',
            'custom_artist' => 'nullable|string|max:150',
        ]);

        try {
            $musicConfig = WeddingMusicService::uploadCustomAudio(
                $invitation,
                $request->file('audio_file'),
                $request->input('custom_title'),
                $request->input('custom_artist')
            );

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tải lên bài hát riêng thành công!',
                    'data' => $musicConfig,
                ]);
            }

            return redirect()->route('account.music.index', $invitation->id)
                ->with('success', 'Tải lên bài hát riêng thành công!');
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()
                ->withErrors(['audio_file' => $e->getMessage()])
                ->withInput();
        }
    }
}
