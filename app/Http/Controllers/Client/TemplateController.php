<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\InvitationTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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
    public function preview(string $slug): View
    {
        $template = InvitationTemplate::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $wishSubmitUrl = route('templates.preview.wish', $slug);
        $wishes = collect([]);

        $customView = "client.invitations.{$template->slug}.template";
        if (view()->exists($customView)) {
            return view($customView, [
                'template' => $template,
                'wedding' => null,
                'invitation' => null,
                'wishes' => $wishes,
                'wishSubmitUrl' => $wishSubmitUrl,
            ]);
        }

        return view('client.pages.preview', [
            'template' => $template,
        ]);
    }
}
