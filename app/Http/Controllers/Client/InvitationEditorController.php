<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\WeddingWish;
use App\Services\InvitationTemplateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InvitationEditorController extends Controller
{
    public function __construct(
        protected InvitationTemplateService $templateService
    ) {}

    /**
     * Show the quick creation & live editor studio.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $selectedTemplateSlug = $request->query('template');

        // Mặc định chọn mẫu free nếu không chỉ định
        if (! $selectedTemplateSlug) {
            $template = InvitationTemplate::where('is_active', true)
                ->where('default_config->tier', 'free')
                ->first()
                ?? InvitationTemplate::where('is_active', true)->first();
        } else {
            $template = InvitationTemplate::where('slug', $selectedTemplateSlug)
                ->where('is_active', true)
                ->firstOrFail();
        }

        // Mẫu Plus & Pro bắt buộc phải đăng nhập mới được tạo/chỉnh sửa
        if ($template->tier !== 'free' && ! auth()->check()) {
            return redirect()->route('login', ['redirect' => $request->fullUrl()])
                ->with('warning', "Mẫu thiệp \"{$template->name}\" thuộc gói {$template->tier_label}. Vui lòng đăng nhập để bắt đầu tạo thiệp.");
        }

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

        // Lưu slug thiệp vào session để ghi nhận người tạo là chủ nhân thiệp
        $ownedSlugs = (array) $request->session()->get('owned_invitation_slugs', []);
        if (! in_array($invitation->slug, $ownedSlugs, true)) {
            $ownedSlugs[] = $invitation->slug;
            $request->session()->put('owned_invitation_slugs', $ownedSlugs);
        }

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
        $invitation = Invitation::with(['wedding.wishes', 'template'])
            ->where('slug', $slug)
            ->firstOrFail();

        $wedding = $invitation->wedding;
        $wishes = $wedding ? $wedding->wishes()->where('is_approved', true)->take(50)->get() : collect([]);
        $wishSubmitUrl = route('invitation.wish.store', $slug);
        $rsvpSubmitUrl = route('rsvp.submit', $slug);

        $clientIp = request()->ip();
        $currentRsvp = null;
        $myWish = null;

        if ($wedding) {
            $currentRsvp = GuestResponse::where('wedding_id', $wedding->id)
                ->where('ip_address', $clientIp)
                ->latest()
                ->first();

            $myWish = WeddingWish::where('wedding_id', $wedding->id)
                ->where('ip_address', $clientIp)
                ->first();
        }

        $isOwner = $invitation->isOwner();
        $rsvpStats = null;
        $rsvpResponses = collect([]);

        if ($isOwner && $wedding) {
            $rsvpResponses = GuestResponse::where('wedding_id', $wedding->id)
                ->latest()
                ->get();

            $rsvpStats = [
                'total' => $rsvpResponses->count(),
                'attending' => $rsvpResponses->where('attending_status', 'attending')->count(),
                'attending_guests' => (int) $rsvpResponses->where('attending_status', 'attending')->sum('adults_count'),
                'declined' => $rsvpResponses->where('attending_status', 'declined')->count(),
            ];
        }

        $template = $invitation->template;
        $customView = $template ? "client.invitations.{$template->slug}.template" : null;

        if ($customView && view()->exists($customView)) {
            return view($customView, [
                'invitation' => $invitation,
                'wedding' => $wedding,
                'template' => $template,
                'wishes' => $wishes,
                'wishSubmitUrl' => $wishSubmitUrl,
                'rsvpSubmitUrl' => $rsvpSubmitUrl,
                'currentRsvp' => $currentRsvp,
                'myWish' => $myWish,
                'isOwner' => $isOwner,
                'rsvpStats' => $rsvpStats,
                'rsvpResponses' => $rsvpResponses,
            ]);
        }

        return view('client.pages.public-invitation', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'template' => $template,
            'wishes' => $wishes,
            'wishSubmitUrl' => $wishSubmitUrl,
            'rsvpSubmitUrl' => $rsvpSubmitUrl,
            'currentRsvp' => $currentRsvp,
            'myWish' => $myWish,
            'isOwner' => $isOwner,
            'rsvpStats' => $rsvpStats,
            'rsvpResponses' => $rsvpResponses,
        ]);
    }

    /**
     * Upload an asset image and save directly into the current template's assets folder.
     */
    public function uploadAsset(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:jpeg,png,jpg,webp,svg,gif,heic|max:15360',
            'template_slug' => 'required|string',
        ]);

        $templateSlug = Str::slug($request->input('template_slug'));
        $file = $request->file('file');

        $ext = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid()->toString().'.'.$ext;

        // Thư mục public để trình duyệt phục vụ ngay lập tức
        $publicAssetDir = public_path("assets/client/invitations/{$templateSlug}/assets");
        // Thư mục views để đồng bộ trọn gói template folder
        $resourceAssetDir = resource_path("views/client/invitations/{$templateSlug}/assets");

        if (! File::exists($publicAssetDir)) {
            File::makeDirectory($publicAssetDir, 0755, true);
        }
        if (! File::exists($resourceAssetDir)) {
            File::makeDirectory($resourceAssetDir, 0755, true);
        }

        // Lưu vào public assets
        $file->move($publicAssetDir, $filename);

        // Đồng bộ bản sao sang resource views template assets
        $publicFilePath = $publicAssetDir.DIRECTORY_SEPARATOR.$filename;
        $resourceFilePath = $resourceAssetDir.DIRECTORY_SEPARATOR.$filename;
        if (File::exists($publicFilePath)) {
            File::copy($publicFilePath, $resourceFilePath);
        }

        $assetUrl = asset("assets/client/invitations/{$templateSlug}/assets/{$filename}");

        return response()->json([
            'success' => true,
            'file_name' => $filename,
            'file_url' => $assetUrl,
            'relative_path' => "assets/{$filename}",
        ]);
    }
}
