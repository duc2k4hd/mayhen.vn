<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\InvitationTemplate;
use App\Services\AuditLogger;
use App\Services\InvitationTemplateService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function __construct(
        protected InvitationTemplateService $templateService
    ) {}

    /**
     * Hiển thị màn hình Onboarding Wizard từng bước (Section 21.2 Master PRD V3).
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = Auth::user();

        // Lấy danh sách mẫu thiệp cưới đang hoạt động
        $templates = InvitationTemplate::where('is_active', true)
            ->orderBy('id', 'asc')
            ->get();

        // Lấy dữ liệu nháp đang lưu trong phiên (nếu có)
        $draft = session('onboarding_draft', [
            'step' => 1,
            'role_intent' => 'couple', // 'couple' | 'relative' | 'planner'
            'template_id' => $templates->first()?->id,
            'groom_name' => '',
            'bride_name' => '',
            'wedding_date' => Carbon::now()->addMonths(2)->format('Y-m-d'),
            'venue_name' => '',
            'venue_address' => '',
        ]);

        return view('client.pages.onboarding', [
            'user' => $user,
            'templates' => $templates,
            'draft' => $draft,
        ]);
    }

    /**
     * Lưu dữ liệu nháp của từng bước vào phiên (Autosave / Step transition).
     */
    public function saveStep(Request $request): JsonResponse
    {
        $step = (int) $request->input('step', 1);
        $draft = session('onboarding_draft', []);

        if ($step === 1) {
            $validated = $request->validate([
                'role_intent' => ['required', 'string', 'in:couple,relative,planner'],
            ]);
            $draft['role_intent'] = $validated['role_intent'];
            $draft['step'] = 2;
        } elseif ($step === 2) {
            $validated = $request->validate([
                'template_id' => ['required', 'integer', 'exists:invitation_templates,id'],
            ]);
            $draft['template_id'] = (int) $validated['template_id'];
            $draft['step'] = 3;
        } elseif ($step === 3) {
            $validated = $request->validate([
                'groom_name' => ['required', 'string', 'max:100'],
                'bride_name' => ['required', 'string', 'max:100'],
                'wedding_date' => ['required', 'date'],
                'venue_name' => ['nullable', 'string', 'max:150'],
                'venue_address' => ['nullable', 'string', 'max:255'],
            ], [
                'groom_name.required' => 'Vui lòng nhập tên Chú Rể.',
                'bride_name.required' => 'Vui lòng nhập tên Cô Dâu.',
                'wedding_date.required' => 'Vui lòng chọn ngày tổ chức hôn lễ.',
            ]);

            $draft['groom_name'] = trim((string) $validated['groom_name']);
            $draft['bride_name'] = trim((string) $validated['bride_name']);
            $draft['wedding_date'] = $validated['wedding_date'];
            $draft['venue_name'] = trim((string) ($validated['venue_name'] ?? ''));
            $draft['venue_address'] = trim((string) ($validated['venue_address'] ?? ''));
            $draft['step'] = 4;
        }

        session(['onboarding_draft' => $draft]);

        return response()->json([
            'success' => true,
            'message' => "Đã lưu thông tin bước {$step} thành công.",
            'draft' => $draft,
            'next_step' => $draft['step'] ?? ($step + 1),
        ]);
    }

    /**
     * Hoàn tất quy trình Onboarding, khởi tạo thiệp cưới và chuyển đến Studio Editor.
     */
    public function complete(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'role_intent' => ['required', 'string', 'in:couple,relative,planner'],
            'template_id' => ['required', 'integer', 'exists:invitation_templates,id'],
            'groom_name' => ['required', 'string', 'max:100'],
            'bride_name' => ['required', 'string', 'max:100'],
            'wedding_date' => ['required', 'date'],
            'venue_name' => ['nullable', 'string', 'max:150'],
            'venue_address' => ['nullable', 'string', 'max:255'],
        ], [
            'groom_name.required' => 'Vui lòng nhập tên Chú Rể.',
            'bride_name.required' => 'Vui lòng nhập tên Cô Dâu.',
            'wedding_date.required' => 'Vui lòng chọn ngày cưới.',
            'template_id.required' => 'Vui lòng chọn một mẫu thiệp cưới.',
        ]);

        $template = InvitationTemplate::findOrFail((int) $validated['template_id']);

        // Chuẩn bị payload khởi tạo thiệp cưới tương thích chuẩn schema Mây Hẹn
        $weddingData = [
            'groom_name' => trim((string) $validated['groom_name']),
            'bride_name' => trim((string) $validated['bride_name']),
            'wedding_date' => $validated['wedding_date'],
            'venue_name' => trim((string) ($validated['venue_name'] ?: 'Trung Tâm Tiệc Cưới')),
            'venue_address' => trim((string) ($validated['venue_address'] ?: 'TP. Hồ Chí Minh')),
            'extra_data' => [
                'role_intent' => $validated['role_intent'],
                'event_party_address' => trim((string) ($validated['venue_address'] ?: '')),
                'event_party_venue' => trim((string) ($validated['venue_name'] ?: '')),
                'onboarding_source' => 'wizard_v3',
            ],
        ];

        // Khởi tạo Wedding & Invitation qua InvitationTemplateService
        $invitation = $this->templateService->saveInvitation(
            $user,
            $template->id,
            ['wedding' => $weddingData]
        );

        // Xóa bản nháp trong session sau khi tạo thành công
        session()->forget('onboarding_draft');

        // Ghi nhật ký kiểm toán bất biến
        AuditLogger::log(
            'ONBOARDING_COMPLETED',
            'invitation',
            $invitation->id,
            "Người dùng {$user->name} đã hoàn tất Onboarding Wizard và khởi tạo thiệp cưới [{$invitation->slug}].",
            [
                'template_slug' => $template->slug,
                'role_intent' => $validated['role_intent'],
                'groom_name' => $validated['groom_name'],
                'bride_name' => $validated['bride_name'],
            ]
        );

        return redirect()->route('invitation.create', ['edit' => $invitation->slug])
            ->with('success', "Chúc mừng bạn! Thiệp cưới \"{$validated['groom_name']} & {$validated['bride_name']}\" đã được khởi tạo thành công. Bắt đầu tùy chỉnh thiệp ngay nhé!");
    }

    /**
     * Làm lại từ đầu (Xóa bản nháp).
     */
    public function reset(): RedirectResponse
    {
        session()->forget('onboarding_draft');

        return redirect()->route('onboarding.index')
            ->with('info', 'Đã đặt lại quy trình thiết lập thiệp cưới.');
    }
}
