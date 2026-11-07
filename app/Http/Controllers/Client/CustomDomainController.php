<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Models\WeddingDomain;
use App\Services\DomainDnsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomDomainController extends Controller
{
    /**
     * Hiển thị giao diện quản lý Vanity Subdomain & Tên miền riêng.
     */
    public function show(int $id): View
    {
        $invitation = Invitation::with(['wedding', 'template', 'domain'])->findOrFail($id);
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404);

        $this->authorizeOwner($wedding);

        $domain = $invitation->domain ?? WeddingDomain::firstOrNew([
            'wedding_id' => $wedding->id,
            'invitation_id' => $invitation->id,
        ]);

        return view('client.pages.account.custom-domain', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'domain' => $domain,
            'cnameTarget' => $domain->cname_target ?: 'cname.mayhen.vn',
        ]);
    }

    /**
     * Đăng ký hoặc cập nhật Vanity Subdomain (ví dụ: tuanlan.mayhen.vn).
     */
    public function updateSubdomain(Request $request, int $id): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404);

        $this->authorizeOwner($wedding);

        $domain = WeddingDomain::firstOrNew([
            'wedding_id' => $wedding->id,
            'invitation_id' => $invitation->id,
        ]);

        $subdomainInput = strtolower(trim((string) $request->input('subdomain')));

        // Kiểm tra danh sách từ khóa cấm / bảo lưu
        if (in_array($subdomainInput, WeddingDomain::BLACKLISTED_SUBDOMAINS, true)) {
            throw ValidationException::withMessages([
                'subdomain' => "Tên miền phụ '{$subdomainInput}' được bảo lưu cho hệ thống. Vui lòng chọn tên khác.",
            ]);
        }

        $request->merge(['subdomain' => $subdomainInput]);

        $request->validate([
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:63',
                'regex:/^[a-z0-9]([a-z0-9\-]{1,61}[a-z0-9])?$/',
                Rule::unique('wedding_domains', 'subdomain')->ignore($domain->id),
            ],
        ], [
            'subdomain.required' => 'Vui lòng nhập tên miền phụ mong muốn.',
            'subdomain.min' => 'Tên miền phụ tối thiểu 3 ký tự.',
            'subdomain.max' => 'Tên miền phụ tối đa 63 ký tự.',
            'subdomain.regex' => 'Tên miền phụ chỉ gồm chữ thường, chữ số và dấu gạch ngang (không bắt đầu hoặc kết thúc bằng dấu gạch ngang).',
            'subdomain.unique' => 'Tên miền phụ này đã có cặp đôi khác đăng ký. Hãy chọn tên khác.',
        ]);

        $domain->subdomain = $subdomainInput;
        $domain->status = 'active';
        $domain->ssl_status = 'active';
        $domain->save();

        return redirect()->route('account.invitations.domain', $invitation->id)
            ->with('status', "Đã kích hoạt tên miền phụ thành công: {$domain->subdomainUrl()}");
    }

    /**
     * Đăng ký hoặc cập nhật Custom Domain riêng (ví dụ: tuanlan.wedding).
     */
    public function updateCustomDomain(Request $request, int $id): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404);

        $this->authorizeOwner($wedding);

        $domain = WeddingDomain::firstOrNew([
            'wedding_id' => $wedding->id,
            'invitation_id' => $invitation->id,
        ]);

        $customDomainInput = strtolower(trim((string) $request->input('custom_domain')));

        // Chuẩn hóa loại bỏ protocol nếu người dùng dán vào
        $customDomainInput = preg_replace('#^https?://#', '', $customDomainInput);
        $customDomainInput = rtrim($customDomainInput, '/');

        // Chặn cấu hình các domain chính của hệ thống
        $baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'mayhen.vn';
        $systemDomains = array_unique(array_filter([
            $baseDomain,
            'mayhen.vn',
            'localhost',
        ]));

        $isForbidden = false;
        foreach ($systemDomains as $sys) {
            if ($customDomainInput === $sys || str_ends_with($customDomainInput, '.'.$sys)) {
                $isForbidden = true;
                break;
            }
        }

        if ($isForbidden) {
            throw ValidationException::withMessages([
                'custom_domain' => 'Không thể đặt tên miền thuộc hệ thống tại mục này. Hãy sử dụng mục Tên Miền Phụ.',
            ]);
        }

        $request->merge(['custom_domain' => $customDomainInput]);

        $request->validate([
            'custom_domain' => [
                'required',
                'string',
                'min:4',
                'max:191',
                'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i',
                Rule::unique('wedding_domains', 'custom_domain')->ignore($domain->id),
            ],
        ], [
            'custom_domain.required' => 'Vui lòng nhập tên miền riêng của bạn.',
            'custom_domain.regex' => 'Định dạng tên miền không hợp lệ (ví dụ: damcuoituanlan.vn hoặc tuanlan.wedding).',
            'custom_domain.unique' => 'Tên miền riêng này đã được gắn cho một thiệp cưới khác.',
        ]);

        $domain->custom_domain = $customDomainInput;
        $domain->status = 'pending';
        $domain->ssl_status = 'pending';
        $domain->save();

        return redirect()->route('account.invitations.domain', $invitation->id)
            ->with('status', "Đã lưu tên miền riêng '{$domain->custom_domain}'. Hãy trỏ bản ghi CNAME theo hướng dẫn bên dưới rồi bấm Kiểm Tra Kết Nối.");
    }

    /**
     * Xác minh bản ghi DNS CNAME và kích hoạt tên miền riêng.
     */
    public function verifyDns(Request $request, int $id, DomainDnsService $dnsService): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404);

        $this->authorizeOwner($wedding);

        $domain = WeddingDomain::where('invitation_id', $invitation->id)->first();
        if (! $domain || ! $domain->custom_domain) {
            return redirect()->route('account.invitations.domain', $invitation->id)
                ->with('error', 'Chưa có tên miền riêng nào được cấu hình để kiểm tra.');
        }

        $result = $dnsService->verifyCname($domain->custom_domain, $domain->cname_target ?: 'cname.mayhen.vn');

        if ($result['verified']) {
            $domain->status = 'active';
            $domain->ssl_status = 'active';
            $domain->dns_verified_at = now();
            $domain->dns_records = $result['records'];
            $domain->save();

            return redirect()->route('account.invitations.domain', $invitation->id)
                ->with('status', "Xác minh DNS thành công! Tên miền riêng {$domain->customDomainUrl()} đã chính thức hoạt động.");
        }

        $domain->status = 'failed';
        $domain->dns_records = $result['records'];
        $domain->save();

        return redirect()->route('account.invitations.domain', $invitation->id)
            ->with('error', "Chưa nhận diện được bản ghi DNS: {$result['message']}");
    }

    /**
     * Gỡ bỏ cấu hình tên miền.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;
        abort_unless($wedding, 404);

        $this->authorizeOwner($wedding);

        $type = $request->input('type', 'all');
        $domain = WeddingDomain::where('invitation_id', $invitation->id)->first();

        if ($domain) {
            if ($type === 'subdomain') {
                $domain->subdomain = null;
                $domain->save();
                $msg = 'Đã gỡ bỏ tên miền phụ thành công.';
            } elseif ($type === 'custom_domain') {
                $domain->custom_domain = null;
                $domain->status = 'pending';
                $domain->save();
                $msg = 'Đã gỡ bỏ tên miền riêng thành công.';
            } else {
                $domain->delete();
                $msg = 'Đã xóa toàn bộ cấu hình tên miền của thiệp cưới.';
            }

            return redirect()->route('account.invitations.domain', $invitation->id)->with('status', $msg);
        }

        return redirect()->route('account.invitations.domain', $invitation->id);
    }

    /**
     * Xác thực phân quyền chống IDOR.
     */
    private function authorizeOwner(Wedding $wedding): void
    {
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        // Đối tác Studio sở hữu đám cưới
        if (auth()->check() && auth()->user()->partner && (int) $wedding->partner_id === (int) auth()->user()->partner->id) {
            return;
        }

        // Quản trị viên hệ thống
        if (auth()->check() && auth()->user()->isAdmin()) {
            return;
        }

        // Khách vãng lai có session sở hữu
        $ownedSlugs = (array) request()->session()->get('owned_invitation_slugs', []);
        if ($ownedSlugs !== [] && $wedding->invitations()->whereIn('slug', $ownedSlugs)->exists()) {
            return;
        }

        abort(404);
    }
}
