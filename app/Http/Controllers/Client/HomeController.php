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

        return view('client.pages.home', [
            'featuredTemplates' => $featuredTemplates,
        ]);
    }
}
