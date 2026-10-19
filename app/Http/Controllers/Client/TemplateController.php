<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TemplateController extends Controller
{
    /**
     * Display template gallery with filter.
     */
    public function index(Request $request): View
    {
        $category = $request->query('category');
        $tier = $request->query('tier');
        $sort = $request->query('sort', 'featured');

        $query = InvitationTemplate::query()->where('is_active', true);

        if ($category && $category !== 'all') {
            $catFilter = ($category === 'envelope') ? 'luxury' : $category;
            $query->where('category', $catFilter);
        }

        if ($tier && in_array($tier, ['free', 'plus', 'pro'])) {
            if ($tier === 'plus') {
                $query->where(function ($q) {
                    $q->where('default_config->tier', 'plus')
                        ->orWhereNull('default_config->tier');
                });
            } else {
                $query->where('default_config->tier', $tier);
            }
        }

        if ($sort === 'popular') {
            $query->orderByRaw("CAST(JSON_UNQUOTE(JSON_EXTRACT(default_config, '$.stats.pairs_used')) AS UNSIGNED) DESC");
        } else {
            $query->orderBy('is_featured', 'desc')->orderBy('id', 'asc');
        }

        $templates = $query->get();

        return view('client.pages.templates', [
            'templates' => $templates,
            'currentCategory' => $category,
            'currentTier' => $tier,
            'currentSort' => $sort,
            'totalCount' => InvitationTemplate::where('is_active', true)->count(),
        ]);
    }

    /**
     * Display template detail page.
     */
    public function show(string $slug): View
    {
        $template = InvitationTemplate::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $invitation = null;
        $wedding = null;
        $editSlug = $request->query('edit') ?: $request->query('invitation');
        if ($editSlug) {
            $inv = Invitation::with('wedding')->where('slug', $editSlug)->first();
            if ($inv && $inv->isOwner()) {
                $invitation = $inv;
                $wedding = $inv->wedding;
            }
        }

        $invitation = null;
        $wedding = null;
        $editSlug = $request->query('edit') ?: $request->query('invitation');
        if ($editSlug) {
            $inv = Invitation::with('wedding')->where('slug', $editSlug)->first();
            if ($inv && $inv->isOwner()) {
                $invitation = $inv;
                $wedding = $inv->wedding;
            }
        }

        $invitation = null;
        $wedding = null;
        $editSlug = $request->query('edit') ?: $request->query('invitation');
        if ($editSlug) {
            $inv = Invitation::with('wedding')->where('slug', $editSlug)->first();
            if ($inv && $inv->isOwner()) {
                $invitation = $inv;
                $wedding = $inv->wedding;
            }
        }

        $similarTemplates = InvitationTemplate::where('id', '!=', $template->id)
            ->where('is_active', true)
            ->where('category', $template->category)
            ->take(3)
            ->get();

        if ($similarTemplates->count() < 3) {
            $needed = 3 - $similarTemplates->count();
            $additional = InvitationTemplate::where('id', '!=', $template->id)
                ->where('is_active', true)
                ->whereNotIn('id', $similarTemplates->pluck('id'))
                ->take($needed)
                ->get();
            $similarTemplates = $similarTemplates->merge($additional);
        }

        return view('client.pages.template-detail', [
            'template' => $template,
            'similarTemplates' => $similarTemplates,
        ]);
    }

    /**
     * Preview actual live invitation demo.
     */
    public function preview(string $slug, Request $request): View
    {
        $template = InvitationTemplate::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $invitation = null;
        $wedding = null;
        $editSlug = $request->query('edit') ?: $request->query('invitation');
        if ($editSlug) {
            $inv = Invitation::with('wedding')->where('slug', $editSlug)->first();
            if ($inv && $inv->isOwner()) {
                $invitation = $inv;
                $wedding = $inv->wedding;
            }
        }

        $wishSubmitUrl = route('templates.preview.wish', $slug);
        $rsvpSubmitUrl = route('templates.preview.rsvp', $slug);
        $wishes = collect([]);

        $customView = "client.invitations.{$template->slug}.template";
        if (view()->exists($customView)) {
            return view($customView, [
                'template' => $template,
                'wedding' => $wedding,
                'invitation' => $invitation,
                'wishes' => $wishes,
                'wishSubmitUrl' => $wishSubmitUrl,
                'rsvpSubmitUrl' => $rsvpSubmitUrl,
            ]);
        }

        return view('client.pages.preview', [
            'template' => $template,
        ]);
    }

    /**
     * Serve static assets directly from resources/views/client/invitations/{slug}/
     */
    public function asset(string $slug, string $path): BinaryFileResponse
    {
        // Làm sạch và chuẩn hóa path để chống directory traversal
        $safePath = str_replace(['..', "\0"], '', $path);
        $fullPath = resource_path("views/client/invitations/{$slug}/{$safePath}");

        if (! file_exists($fullPath) || is_dir($fullPath)) {
            $fallbackAssetsPath = resource_path("views/client/invitations/{$slug}/assets/{$safePath}");
            if (file_exists($fallbackAssetsPath) && ! is_dir($fallbackAssetsPath)) {
                $fullPath = $fallbackAssetsPath;
            } else {
                abort(404, 'Asset not found');
            }
        }

        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'json' => 'application/json',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'gif' => 'image/gif',
            'mp3' => 'audio/mpeg',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
        ];

        $isDev = config('app.debug') || app()->environment('local');
        $headers = [
            'Content-Type' => $mimeTypes[$extension] ?? (mime_content_type($fullPath) ?: 'application/octet-stream'),
            'Cache-Control' => $isDev ? 'no-cache, no-store, must-revalidate' : 'public, max-age=31536000',
        ];

        return response()->file($fullPath, $headers);
    }
}
