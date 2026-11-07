<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Partner;
use App\Models\Wedding;
use App\Rules\NoProfanity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PartnerWeddingController extends Controller
{
    /**
     * Danh sách toàn bộ thiệp cưới của khách hàng thuộc Studio.
     */
    public function index(Request $request): View
    {
        $partner = $this->resolvePartner();
        $query = $partner->weddings()->with(['invitations.template']);

        // Tìm kiếm theo từ khóa
        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('groom_name', 'like', "%{$search}%")
                    ->orWhere('bride_name', 'like', "%{$search}%")
                    ->orWhere('venue_name', 'like', "%{$search}%")
                    ->orWhere('client_notes', 'like', "%{$search}%");
            });
        }

        // Lọc theo trạng thái xuất bản
        $status = $request->query('status');
        if ($status === 'published') {
            $query->whereHas('invitations', fn ($q) => $q->where('is_published', true));
        } elseif ($status === 'draft') {
            $query->whereHas('invitations', fn ($q) => $q->where('is_published', false));
        }

        $weddings = $query->latest()->paginate(12)->withQueryString();

        return view('partner.weddings.index', [
            'partner' => $partner,
            'weddings' => $weddings,
            'search' => $search,
            'status' => $status,
        ]);
    }

    /**
     * Màn hình khởi tạo đám cưới mới cho khách hàng của Studio.
     */
    public function create(): View
    {
        $partner = $this->resolvePartner();
        $templates = InvitationTemplate::where('is_active', true)->orderBy('display_order')->get();

        return view('partner.weddings.create', [
            'partner' => $partner,
            'templates' => $templates,
        ]);
    }

    /**
     * Lưu trữ đám cưới mới và chuyển thẳng vào Studio để tùy biến thiết kế.
     */
    public function store(Request $request): RedirectResponse
    {
        $partner = $this->resolvePartner();

        $validated = $request->validate([
            'template_id' => ['required', 'exists:invitation_templates,id'],
            'groom_name' => ['required', 'string', 'max:255', new NoProfanity],
            'bride_name' => ['required', 'string', 'max:255', new NoProfanity],
            'wedding_date' => ['required', 'date'],
            'venue_name' => ['nullable', 'string', 'max:255', new NoProfanity],
            'venue_address' => ['nullable', 'string', 'max:500'],
            'client_notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'template_id.required' => 'Vui lòng chọn một mẫu thiệp cưới bắt đầu.',
            'groom_name.required' => 'Vui lòng nhập tên chú rể.',
            'bride_name.required' => 'Vui lòng nhập tên cô dâu.',
            'wedding_date.required' => 'Vui lòng chọn ngày tổ chức hôn lễ.',
        ]);

        $handoverCode = strtoupper(Str::random(6));

        // Khởi tạo đám cưới gắn với partner_id
        $wedding = Wedding::create([
            'user_id' => auth()->id(),
            'partner_id' => $partner->id,
            'handover_code' => $handoverCode,
            'groom_name' => strip_tags(trim($validated['groom_name'])),
            'bride_name' => strip_tags(trim($validated['bride_name'])),
            'wedding_date' => $validated['wedding_date'],
            'venue_name' => ! empty($validated['venue_name']) ? strip_tags(trim($validated['venue_name'])) : null,
            'venue_address' => ! empty($validated['venue_address']) ? strip_tags(trim($validated['venue_address'])) : null,
            'client_notes' => ! empty($validated['client_notes']) ? strip_tags(trim($validated['client_notes'])) : null,
        ]);

        // Tạo lời chào mẫu
        $wedding->extra_data = [
            'story_quote' => 'Hai trái tim cùng chung một nhịp đập, cùng nhau viết nên câu chuyện hạnh phúc dài lâu.',
        ];
        $wedding->save();

        // Tạo bản ghi thiệp cưới
        $baseSlug = Str::slug($wedding->groom_name.'-'.$wedding->bride_name);
        $slug = $baseSlug.'-'.Str::lower(Str::random(5));
        while (Invitation::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.Str::lower(Str::random(5));
        }

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $validated['template_id'],
            'slug' => $slug,
            'is_published' => false,
            'custom_config' => [
                'partner_branding' => [
                    'partner_name' => $partner->business_name,
                    'partner_code' => $partner->partner_code,
                ],
            ],
        ]);

        return redirect()->route('invitation.create', ['edit' => $invitation->slug])
            ->with('success', 'Đã khởi tạo thiệp cưới thành công cho cặp đôi '.$wedding->groom_name.' & '.$wedding->bride_name.'! Đang chuyển vào Studio để chỉnh sửa.');
    }

    /**
     * Màn hình tạo mã bàn giao thiệp cưới cho cô dâu chú rể.
     */
    public function handover(int $weddingId): View
    {
        $partner = $this->resolvePartner();
        $wedding = $partner->weddings()->with(['invitations.template'])->findOrFail($weddingId);

        if (! $wedding->handover_code) {
            $wedding->update(['handover_code' => strtoupper(Str::random(6))]);
        }

        return view('partner.weddings.handover', [
            'partner' => $partner,
            'wedding' => $wedding,
            'invitation' => $wedding->invitations->first(),
        ]);
    }

    /**
     * Đổi mã bàn giao mới (nếu mã cũ bị lộ).
     */
    public function regenerateHandoverCode(int $weddingId): RedirectResponse
    {
        $partner = $this->resolvePartner();
        $wedding = $partner->weddings()->findOrFail($weddingId);

        $newCode = strtoupper(Str::random(6));
        $wedding->update(['handover_code' => $newCode]);

        return back()->with('success', 'Đã tạo mã bàn giao mới thành công: '.$newCode);
    }

    /**
     * Xử lý phía Cặp Đôi (Client): Nhập mã bàn giao để nhận thiệp cưới về tài khoản cá nhân.
     */
    public function claim(Request $request): RedirectResponse
    {
        abort_unless(auth()->check(), 401, 'Vui lòng đăng nhập để nhận thiệp cưới.');

        $validated = $request->validate([
            'handover_code' => ['required', 'string', 'size:6'],
        ], [
            'handover_code.required' => 'Vui lòng nhập mã bàn giao 6 ký tự do Studio cung cấp.',
            'handover_code.size' => 'Mã bàn giao gồm đúng 6 ký tự.',
        ]);

        $code = strtoupper(trim($validated['handover_code']));
        $claimed = false;
        $wedding = null;

        DB::transaction(function () use ($code, &$claimed, &$wedding) {
            $wedding = Wedding::where('handover_code', $code)->lockForUpdate()->first();
            if ($wedding) {
                $wedding->update([
                    'user_id' => auth()->id(),
                    'handover_code' => null,
                ]);
                $claimed = true;
            }
        });

        if (! $claimed || ! $wedding) {
            return back()->with('error', 'Mã bàn giao không chính xác hoặc đã được sử dụng. Vui lòng liên hệ Studio của bạn để kiểm tra.');
        }

        return redirect()->route('account.invitations')
            ->with('success', 'Chúc mừng! Bạn đã nhận thành công quyền quản trị thiệp cưới của '.$wedding->groom_name.' & '.$wedding->bride_name.' từ Studio đối tác!');
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
