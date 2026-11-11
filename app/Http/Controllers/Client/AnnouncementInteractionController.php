<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\SiteAnnouncement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class AnnouncementInteractionController extends Controller
{
    /**
     * Ghi nhận lượt bấm CTA và điều hướng đến đích.
     */
    public function trackClick(int $id): RedirectResponse
    {
        $announcement = SiteAnnouncement::findOrFail($id);
        $announcement->recordClick();

        if (! empty($announcement->cta_url)) {
            return redirect()->away($announcement->cta_url);
        }

        return back();
    }

    /**
     * API ghi nhận lượt xem (impression).
     */
    public function trackImpression(int $id): JsonResponse
    {
        $announcement = SiteAnnouncement::findOrFail($id);
        $announcement->recordImpression();

        return response()->json(['success' => true]);
    }
}
