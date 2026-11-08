<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\PremiumValuePreviewService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PremiumPreviewController extends Controller
{
    /**
     * Màn hình Sandbox Trải Nghiệm Thử Tính Năng Cao Cấp Trước Thanh Toán (TASK-VAL-27).
     */
    public function preview(Request $request, string $feature, PremiumValuePreviewService $service): View
    {
        abort_unless($service->isSupported($feature), 404);

        $meta = $service->getFeatureMeta($feature);
        $sandboxData = $service->getSandboxData($feature);
        $proPlan = Plan::where('slug', 'pro')->where('is_active', true)->first();

        return view('client.pages.premium-preview', [
            'feature' => $feature,
            'meta' => $meta,
            'sandboxData' => $sandboxData,
            'proPlan' => $proPlan,
        ]);
    }
}

