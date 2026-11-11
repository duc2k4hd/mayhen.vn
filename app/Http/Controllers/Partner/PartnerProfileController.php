<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\InvitationTemplate;
use App\Models\Partner;
use App\Rules\NoProfanity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PartnerProfileController extends Controller
{
    /**
     * Màn hình cài đặt thương hiệu & hồ sơ Studio.
     */
    public function edit(): View
    {
        $partner = $this->resolvePartner();
        $allTemplates = InvitationTemplate::where('is_active', true)->get();

        return view('partner.profile', [
            'partner' => $partner,
            'allTemplates' => $allTemplates,
        ]);
    }

    /**
     * Cập nhật thông tin Studio & cấu hình Co-branding.
     */
    public function update(Request $request): RedirectResponse
    {
        $partner = $this->resolvePartner();

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255', new NoProfanity],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'footer_text' => ['nullable', 'string', 'max:150', new NoProfanity],
            'show_badge' => ['nullable', 'boolean'],
            'bio' => ['nullable', 'string', 'max:500', new NoProfanity],
            'exclusive_perk' => ['nullable', 'string', 'max:255', new NoProfanity],
            'curated_template_ids' => ['nullable', 'array'],
            'curated_template_ids.*' => ['integer', 'exists:invitation_templates,id'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
        ], [
            'business_name.required' => 'Vui lòng nhập tên Studio hoặc thương hiệu của bạn.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'email.required' => 'Vui lòng nhập email liên hệ.',
            'logo.mimes' => 'Logo chỉ chấp nhận định dạng JPG, PNG, WebP hoặc SVG.',
            'logo.max' => 'Dung lượng logo không được vượt quá 4MB.',
        ]);

        $partnerData = [
            'business_name' => strip_tags(trim($validated['business_name'])),
            'phone' => trim($validated['phone']),
            'email' => strtolower(trim($validated['email'])),
            'address' => ! empty($validated['address']) ? strip_tags(trim($validated['address'])) : null,
            'website' => ! empty($validated['website']) ? strip_tags(trim($validated['website'])) : null,
        ];

        // Xử lý upload logo mới an toàn
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $ext = strtolower($file->getClientOriginalExtension());
            $fileName = 'partner_logo_'.$partner->id.'_'.Str::random(8).'.'.$ext;
            $path = $file->storeAs('partners/logos', $fileName, 'public');
            $partnerData['logo_url'] = Storage::disk('public')->url($path);
        }

        // Cập nhật cấu hình co-branding & showcase công khai
        $currentConfig = is_array($partner->custom_config) ? $partner->custom_config : [];
        $currentConfig['branding'] = [
            'footer_text' => ! empty($validated['footer_text'])
                ? strip_tags(trim($validated['footer_text']))
                : 'Đồng hành cùng '.$partnerData['business_name'],
            'show_badge' => $request->boolean('show_badge', true),
        ];

        $currentConfig['showcase'] = [
            'bio' => ! empty($validated['bio']) ? strip_tags(trim($validated['bio'])) : null,
            'exclusive_perk' => ! empty($validated['exclusive_perk']) ? strip_tags(trim($validated['exclusive_perk'])) : null,
            'curated_template_ids' => array_map('intval', $validated['curated_template_ids'] ?? []),
            'social_links' => [
                'facebook' => $validated['facebook_url'] ?? null,
                'instagram' => $validated['instagram_url'] ?? null,
                'tiktok' => $validated['tiktok_url'] ?? null,
            ],
        ];

        $partnerData['custom_config'] = $currentConfig;

        $partner->update($partnerData);

        return back()->with('success', 'Đã cập nhật hồ sơ Studio và trang giới thiệu Showcase thành công!');
    }

    private function resolvePartner(): Partner
    {
        $user = auth()->user();
        if ($user->partner) {
            return $user->partner;
        }

        if ($user->isAdmin()) {
            $partner = Partner::first();
            if ($partner) {
                return $partner;
            }
        }

        abort(403, 'Bạn chưa có thông tin đối tác Studio.');
    }
}
