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
        $query = InvitationTemplate::query()->where('is_active', true);

        if ($category && in_array($category, ['luxury', 'editorial', 'minimal', 'envelope'])) {
            $catFilter = ($category === 'envelope') ? 'luxury' : $category;
            $query->where('category', $catFilter);
        }

        $templates = $query->get();

        return view('client.pages.templates', [
            'templates' => $templates,
            'currentCategory' => $request->query('category'),
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
            ->take(2)
            ->get();

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

        return view('client.pages.preview', [
            'template' => $template,
        ]);
    }
}
