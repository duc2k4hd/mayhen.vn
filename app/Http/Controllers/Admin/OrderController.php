<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;

class OrderController extends Controller
{
    /**
     * Display all orders in admin.
     */
    public function index(): View
    {
        $orders = Order::with(['user', 'plan', 'invitation'])->latest()->paginate(15);

        return view('admin.pages.orders', [
            'orders' => $orders,
        ]);
    }
}
