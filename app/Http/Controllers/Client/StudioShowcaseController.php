<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\InvitationTemplate;
use App\Models\Partner;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StudioShowcaseController extends Controller
{
    /**
     * Hiển thị trang giới thiệu & danh mục thiệp cưới tuyển chọn riêng của Studio đối tác.
     * (Mục 8.3 DOCX & Section 38 PRD V3: Studio Co-branded Showcase Page).
     */
    public function show(Request $request, string $slug): View
    {
        $partner = Partner::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        // Tự động ghi nhận mã giới thiệu của đối tác vào Session để hưởng hoa hồng khi khách tạo thiệp
        session([
            'partner_referral_id' => $partner->id,
            'partner_referral_code' => $partner->partner_code,
        ]);

        $customConfig = is_array($partner->custom_config) ? $partner->custom_config : [];
        $showcaseConfig = $customConfig['showcase'] ?? [];
        $curatedIds = $showcaseConfig['curated_template_ids'] ?? [];

        // Lấy danh sách mẫu thiệp cưới được Studio chọn lọc đưa lên showcase
        if (! empty($curatedIds) && is_array($curatedIds)) {
            $templates = InvitationTemplate::whereIn('id', $curatedIds)
                ->where('is_active', true)
                ->get();

            // Nếu các mẫu được chọn tạm thời không khả dụng, nạp fallback các mẫu active
            if ($templates->isEmpty()) {
                $templates = InvitationTemplate::where('is_active', true)
                    ->orderByDesc('is_featured')
                    ->take(6)
                    ->get();
            }
        } else {
            $templates = InvitationTemplate::where('is_active', true)
                ->orderByDesc('is_featured')
                ->take(6)
                ->get();
        }

        return view('client.pages.studio.showcase', [
            'partner' => $partner,
            'templates' => $templates,
            'showcaseConfig' => $showcaseConfig,
        ]);
    }
}

