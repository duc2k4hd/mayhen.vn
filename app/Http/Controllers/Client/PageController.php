<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Models\Contact;
use App\Models\Plan;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    public function services(): View
    {
        return view('client.pages.services');
    }

    public function pricing(): View
    {
        $rawPlans = Cache::remember('mayhen_pricing_plans', 86400, function () {
            $plans = Plan::where('is_active', true)
                ->whereIn('slug', ['free', 'plus', 'pro'])
                ->orderBy('price')
                ->get();

            if ($plans->where('slug', 'plus')->isEmpty()) {
                $plans = Plan::where('is_active', true)
                    ->whereIn('slug', ['free', 'premium', 'pro'])
                    ->orderBy('price')
                    ->get();
            }

            return $plans->map->getAttributes()->all();
        });

        $plans = Plan::hydrate($rawPlans);

        return view('client.pages.pricing', ['plans' => $plans]);
    }

    public function howItWorks(): View
    {
        return view('client.pages.how-it-works');
    }

    public function about(): View
    {
        return view('client.pages.about');
    }

    public function contact(): View
    {
        return view('client.pages.contact');
    }

    /**
     * Tiếp nhận yêu cầu báo giá & đặt hẹn tư vấn in thiệp từ khách hàng.
     */
    public function submitContact(Request $request): JsonResponse|RedirectResponse
    {
        // 1. Chống bot spam qua honeypot field
        if ($request->filled('company_fax_trap')) {
            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => 'Yêu cầu của bạn đã được ghi nhận.']);
            }

            return back()->with('contact_success', true);
        }

        // 2. Validate dữ liệu
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'wedding_date' => ['nullable', 'date'],
            'quantity' => ['nullable', 'string', 'max:50'],
            'need' => ['required', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:3000'],
        ], [
            'name.required' => 'Vui lòng cho biết họ và tên của bạn.',
            'phone.required' => 'Vui lòng cung cấp số điện thoại hoặc Zalo để chúng tôi liên hệ tư vấn.',
            'need.required' => 'Vui lòng chọn nhu cầu tư vấn chính.',
        ]);

        // 3. Lưu vào Database
        $contact = Contact::create([
            'name' => trim(strip_tags($validated['name'])),
            'phone' => trim(strip_tags($validated['phone'])),
            'email' => ! empty($validated['email']) ? trim(strip_tags($validated['email'])) : null,
            'wedding_date' => $validated['wedding_date'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'need' => $validated['need'],
            'message' => ! empty($validated['message']) ? trim(strip_tags($validated['message'])) : null,
            'status' => 'new',
            'source' => 'web_contact_form',
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);

        // 4. Ghi Audit Log bất biến
        AuditLogger::log(
            action: 'CONTACT_SUBMITTED',
            targetType: 'Contact',
            targetId: $contact->id,
            description: "Khách hàng {$contact->name} ({$contact->phone}) gửi yêu cầu tư vấn in ấn / đặt hẹn",
            details: [
                'contact_id' => $contact->id,
                'name' => $contact->name,
                'phone' => $contact->phone,
                'need' => $contact->need,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cảm ơn bạn đã gửi thông tin! Chuyên viên tư vấn in ấn của Mây Hẹn đã tiếp nhận và sẽ chủ động liên hệ hỗ trợ bạn qua Zalo/Điện thoại trong vòng 15 phút.',
                'contact_id' => $contact->id,
            ]);
        }

        return back()->with('contact_success', true);
    }

    public function faq(): View
    {
        return view('client.pages.faq');
    }

    public function terms(): View
    {
        $page = CmsPage::where('slug', 'dieu-khoan-dich-vu')->published()->first();

        return view('client.pages.terms', compact('page'));
    }

    public function privacy(): View
    {
        $page = CmsPage::where('slug', 'chinh-sach-bao-mat')->published()->first();

        return view('client.pages.privacy', compact('page'));
    }

    public function showPage(string $slug): View
    {
        $page = CmsPage::where('slug', $slug)->published()->firstOrFail();

        return view('client.pages.custom-page', compact('page'));
    }
}
