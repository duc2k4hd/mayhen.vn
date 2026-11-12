<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\WeddingLocationAdvisoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WeddingLocationAdvisoryController extends Controller
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
     * Màn hình Studio cấu hình Chỉ dẫn địa điểm, Bãi đỗ xe & Thời tiết (Section 30 PRD V3 & IDEA-A02).
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $weatherPresets = WeddingLocationAdvisoryService::getPresetWeatherConditions();
        $advisoryConfig = WeddingLocationAdvisoryService::resolveAdvisoryConfig($invitation);

        return view('client.pages.account.location_advisory.index', compact(
            'invitation',
            'weatherPresets',
            'advisoryConfig'
        ));
    }

    /**
     * Cập nhật thông tin chỉ dẫn địa điểm, bãi đỗ xe và thời tiết.
     */
    public function update(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'enabled' => 'nullable|boolean',
            'title' => 'required|string|max:150',
            'subtitle' => 'nullable|string|max:255',
            'venue_name' => 'required|string|max:255',
            'venue_address' => 'required|string|max:255',
            'map_url' => 'nullable|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'reception_phone' => 'nullable|string|max:30',
            'reception_coordinator' => 'nullable|string|max:100',
            'car_parking' => 'nullable|string|max:500',
            'bike_parking' => 'nullable|string|max:500',
            'accessibility_note' => 'nullable|string|max:500',
            'weather_enabled' => 'nullable|boolean',
            'condition_key' => 'required|string|max:50',
            'temperature_celsius' => 'nullable|integer|between:-10,50',
            'advisory_note' => 'nullable|string|max:500',
        ]);

        $validated['enabled'] = $request->boolean('enabled');
        $validated['weather_enabled'] = $request->boolean('weather_enabled');

        WeddingLocationAdvisoryService::updateAdvisoryConfig($invitation, $validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật chỉ dẫn địa điểm, bãi đỗ xe và thời tiết thành công!',
                'advisory' => WeddingLocationAdvisoryService::resolveAdvisoryConfig($invitation),
            ]);
        }

        return redirect()->route('account.location_advisory.index', $invitation->id)
            ->with('success', 'Đã cập nhật chỉ dẫn địa điểm, bãi đỗ xe và lưu ý thời tiết thành công!');
    }
}
