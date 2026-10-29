<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\InvitationTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    /**
     * Display the May Hen marketing landing page.
     */
    public function index(): View
    {
        $rawFeatured = Cache::remember('mayhen_home_featured_templates', 3600, function () {
            return InvitationTemplate::query()
                ->where('is_active', true)
                ->select(['id', 'name', 'slug', 'category', 'thumbnail_url', 'is_featured', 'default_config'])
                ->orderByDesc('is_featured')
                ->take(20)
                ->get()
                ->map->getAttributes()
                ->all();
        });
        $featuredTemplates = InvitationTemplate::hydrate($rawFeatured);

        $rawGallery = Cache::remember('mayhen_home_gallery_templates', 3600, function () {
            return InvitationTemplate::query()
                ->where('is_active', true)
                ->whereNotNull('thumbnail_url')
                ->select(['id', 'name', 'slug', 'category', 'thumbnail_url', 'is_featured', 'default_config'])
                ->orderByDesc('is_featured')
                ->orderBy('id')
                ->take(50)
                ->get()
                ->map->getAttributes()
                ->all();
        });
        $galleryTemplates = InvitationTemplate::hydrate($rawGallery);

        return view('client.pages.home', [
            'featuredTemplates' => $featuredTemplates,
            'galleryTemplates' => $galleryTemplates,
        ]);
    }
}
