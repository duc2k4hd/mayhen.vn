<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\InvitationTemplate;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Display the May Hen marketing landing page.
     */
    public function index(): View
    {
        $featuredTemplates = InvitationTemplate::query()
            ->where('is_active', true)
            ->orderByDesc('is_featured')
            ->take(20)
            ->get();

        $galleryTemplates = InvitationTemplate::query()
            ->where('is_active', true)
            ->whereNotNull('thumbnail_url')
            ->select(['id', 'name', 'slug', 'category', 'thumbnail_url', 'is_featured', 'default_config'])
            ->orderByDesc('is_featured')
            ->orderBy('id')
            ->take(50)
            ->get();

        return view('client.pages.home', [
            'featuredTemplates' => $featuredTemplates,
            'galleryTemplates' => $galleryTemplates,
        ]);
    }
}
