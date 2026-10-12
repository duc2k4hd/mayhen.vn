<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvitationEditorController extends Controller
{
    /**
     * Show the quick creation & live editor studio.
     */
    public function create(Request $request): View
    {
        $selectedTemplateSlug = $request->query('template', 'envelope-reveal-luxury');
        $template = InvitationTemplate::where('slug', $selectedTemplateSlug)->first()
            ?? InvitationTemplate::first();

        $allTemplates = InvitationTemplate::where('is_active', true)->get();

        return view('client.pages.editor', [
            'selectedTemplate' => $template,
            'allTemplates' => $allTemplates,
        ]);
    }

    /**
     * Save draft invitation from editor.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'template_id' => 'required|exists:invitation_templates,id',
            'groom_name' => 'required|string|max:100',
            'bride_name' => 'required|string|max:100',
            'wedding_date' => 'required|date',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'nullable|string|max:255',
            'story' => 'nullable|string|max:1000',
            'cover_photo_url' => 'nullable|url|max:500',
        ]);

        // Find or create temporary guest session/user
        $user = auth()->user() ?? User::firstOrCreate(
            ['email' => 'guest@mayhen.vn'],
            [
                'name' => 'Khách Trải Nghiệm',
                'password' => bcrypt(Str::random(16)),
                'role' => 'customer',
            ]
        );

        $wedding = Wedding::create([
            'user_id' => $user->id,
            'groom_name' => $validated['groom_name'],
            'bride_name' => $validated['bride_name'],
            'wedding_date' => $validated['wedding_date'],
            'venue_name' => $validated['venue_name'] ?? 'The Grand Ballroom',
            'venue_address' => $validated['venue_address'] ?? 'TP. Hồ Chí Minh',
            'story' => $validated['story'] ?? 'Hành trình tình yêu của chúng mình.',
            'cover_photo_url' => $validated['cover_photo_url'] ?? 'https://images.unsplash.com/photo-1519741497674-611481863552?auto=format&fit=crop&w=800&q=80',
        ]);

        $slugBase = Str::slug($validated['groom_name'].'-'.$validated['bride_name']);
        $uniqueSlug = $slugBase.'-'.Str::lower(Str::random(5));

        $invitation = Invitation::create([
            'wedding_id' => $wedding->id,
            'template_id' => $validated['template_id'],
            'slug' => $uniqueSlug,
            'title' => 'Thiệp Cưới '.$validated['groom_name'].' & '.$validated['bride_name'],
            'version_type' => 'general',
            'is_published' => true,
            'published_at' => now(),
            'expires_at' => now()->addDays(30),
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
