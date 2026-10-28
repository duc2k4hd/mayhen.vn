<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    public function services(): View
    {
        return view('client.pages.services');
    }

    public function pricing(): View
    {
        $rawPlans = Cache::remember('mayhen_pricing_plans', 86400, function () {
            $plans = Plan::where('is_active', true)
                ->whereIn('slug', ['free', 'plus', 'pro'])
                ->orderBy('price')
                ->get();

            if ($plans->where('slug', 'plus')->isEmpty()) {
                $plans = Plan::where('is_active', true)
                    ->whereIn('slug', ['free', 'premium', 'pro'])
                    ->orderBy('price')
                    ->get();
            }

            return $plans->map->getAttributes()->all();
        });

        $plans = Plan::hydrate($rawPlans);

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
