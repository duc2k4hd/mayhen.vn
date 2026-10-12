<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

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

    /**
     * Mark order as paid manually.
     */
    public function markPaid(int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);
        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        if ($order->invitation) {
            $order->invitation->update([
                'is_published' => true,
                'published_at' => now(),
            ]);
        }

        return back()->with('success', 'Đã cập nhật đơn hàng thành Đã Thanh Toán.');
    }
}
