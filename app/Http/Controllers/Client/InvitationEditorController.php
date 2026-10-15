<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Services\InvitationTemplateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvitationEditorController extends Controller
{
    public function __construct(
        protected InvitationTemplateService $templateService
    ) {}

    /**
     * Show the quick creation & live editor studio.
     */
    public function create(Request $request): View
    {
        $selectedTemplateSlug = $request->query('template', 'thanh-manh-01');
        $template = InvitationTemplate::where('slug', $selectedTemplateSlug)->first()
            ?? InvitationTemplate::first();

        $allTemplates = InvitationTemplate::where('is_active', true)->get();
        $schema = $this->templateService->getSchema($template->slug, $template->current_version ?? 'v1');

        return view('client.pages.editor', [
            'selectedTemplate' => $template,
            'allTemplates' => $allTemplates,
            'schema' => $schema,
        ]);
    }

    /**
     * Save draft invitation from editor.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $templateId = (int) $request->input('template_id');
        $template = InvitationTemplate::findOrFail($templateId);

        // Chuẩn hóa input: hỗ trợ cả dạng phẳng và dạng lồng nhau
        $rawWedding = $request->input('wedding', []);
        if (empty($rawWedding) && $request->has('groom_name')) {
            $rawWedding = [
                'groom_name' => $request->input('groom_name'),
                'bride_name' => $request->input('bride_name'),
                'groom_parents' => $request->input('groom_parents'),
                'bride_parents' => $request->input('bride_parents'),
                'wedding_date' => $request->input('wedding_date'),
                'venue_name' => $request->input('venue_name'),
                'venue_address' => $request->input('venue_address'),
                'story' => $request->input('story'),
                'cover_photo_url' => $request->input('cover_photo_url'),
                'music_url' => $request->input('music_url'),
                'bank_name' => $request->input('bank_name'),
                'bank_account_number' => $request->input('bank_account_number'),
                'bank_account_name' => $request->input('bank_account_name'),
                'extra_data' => $request->input('extra_data', []),
            ];
        }

        // Validate cơ bản
        $request->validate([
            'template_id' => 'required|exists:invitation_templates,id',
        ]);

        // Tìm hoặc tạo guest user
        $user = auth()->user() ?? User::firstOrCreate(
            ['email' => 'guest@mayhen.vn'],
            [
                'name' => 'Khách Trải Nghiệm',
                'password' => bcrypt(Str::random(16)),
                'role' => 'customer',
            ]
        );

        $invitation = $this->templateService->saveInvitation($user, $template->id, [
            'wedding' => $rawWedding,
            'invitation' => [
                'custom_config' => $request->input('invitation.custom_config', []),
            ],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'slug' => $invitation->slug,
                'preview_url' => route('invitation.show', $invitation->slug),
            ]);
        }

        return redirect()->route('invitation.show', $invitation->slug);
    }

    /**
     * Show published public invitation.
     */
    public function show(string $slug): View
    {
        $invitation = Invitation::with(['wedding', 'template'])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('client.pages.public-invitation', [
            'invitation' => $invitation,
            'wedding' => $invitation->wedding,
            'template' => $invitation->template,
        ]);
    }
}
