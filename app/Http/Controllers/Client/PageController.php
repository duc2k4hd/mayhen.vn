<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function services(): View
    {
        return view('client.pages.services');
    }

    public function pricing(): View
    {
        $plans = Plan::where('is_active', true)->get();

        return view('client.pages.pricing', ['plans' => $plans]);
    }

    public function howItWorks(): View
    {
        return view('client.pages.how-it-works');
    }

    public function about(): View
    {
        return view('client.pages.about');
    }

    public function contact(): View
    {
        return view('client.pages.contact');
    }

    public function faq(): View
    {
        return view('client.pages.faq');
    }

    public function terms(): View
    {
        return view('client.pages.terms');
    }

    public function privacy(): View
    {
        return view('client.pages.privacy');
    }
}
