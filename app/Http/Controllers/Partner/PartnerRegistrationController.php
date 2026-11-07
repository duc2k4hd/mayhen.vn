<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\User;
use App\Rules\NoProfanity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PartnerRegistrationController extends Controller
{
    /**
     * Giao diện đăng ký trở thành đối tác Studio / Wedding Planner.
     */
    public function create(): View|RedirectResponse
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->partner) {
                return redirect()->route('partner.dashboard')->with('info', 'Bạn đã là đối tác của Mây Hẹn!');
            }
        }

        return view('partner.register');
    }

    /**
     * Xử lý đăng ký đối tác mới.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255', new NoProfanity],
            'phone' => ['required', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'string', 'max:255'],
            'partner_type' => ['nullable', 'string', 'max:50'],
        ], [
            'business_name.required' => 'Vui lòng nhập tên Studio, Ảnh Viện hoặc Thương Hiệu Cưới của bạn.',
            'phone.required' => 'Vui lòng nhập số điện thoại liên hệ.',
            'phone.regex' => 'Số điện thoại không đúng định dạng.',
            'email.required' => 'Vui lòng nhập địa chỉ email doanh nghiệp.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
        ]);

        $user = auth()->user();

        // Nếu khách chưa đăng nhập, kiểm tra email để liên kết hoặc yêu cầu đăng nhập
        if (! $user) {
            $existingUser = User::where('email', $validated['email'])->first();
            if ($existingUser) {
                return redirect()->route('login')->with('warning', 'Email này đã tồn tại tài khoản. Vui lòng đăng nhập trước khi đăng ký đối tác.');
            }

            // Tạo tài khoản mới cho đối tác
            $user = User::create([
                'name' => strip_tags(trim($validated['business_name'])),
                'email' => strtolower(trim($validated['email'])),
                'phone' => trim($validated['phone']),
                'password' => bcrypt(Str::random(16)), // Mật khẩu ngẫu nhiên an toàn
                'role' => 'partner',
            ]);

            auth()->login($user);
        } else {
            // Nâng cấp role người dùng hiện tại thành partner
            if ($user->role !== 'admin' && $user->role !== 'manager') {
                $user->update(['role' => 'partner']);
            }
        }

        $baseSlug = Str::slug($validated['business_name']);
        $slug = $baseSlug;
        $counter = 1;
        while (Partner::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        $partner = Partner::create([
            'user_id' => $user->id,
            'business_name' => strip_tags(trim($validated['business_name'])),
            'slug' => $slug,
            'partner_code' => Partner::generatePartnerCode(),
            'phone' => trim($validated['phone']),
            'email' => strtolower(trim($validated['email'])),
            'address' => ! empty($validated['address']) ? strip_tags(trim($validated['address'])) : null,
            'website' => ! empty($validated['website']) ? strip_tags(trim($validated['website'])) : null,
            'tier' => 'silver',
            'commission_rate' => 20.00,
            'wallet_balance' => 0.00,
            'status' => 'active', // Kích hoạt ngay để đối tác bắt đầu trải nghiệm
            'custom_config' => [
                'partner_type' => $validated['partner_type'] ?? 'wedding_studio',
                'branding' => [
                    'footer_text' => 'Đồng hành cùng '.strip_tags(trim($validated['business_name'])),
                    'show_badge' => true,
                ],
            ],
        ]);

        return redirect()->route('partner.dashboard')->with('success', 'Chào mừng '.$partner->business_name.' chính thức gia nhập Mạng Lưới Đối Tác Đẳng Cấp của Mây Hẹn!');
    }
}
