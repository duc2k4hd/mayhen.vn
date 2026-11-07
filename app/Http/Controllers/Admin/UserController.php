<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of all system users with role filtering and search.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $role = $request->input('role');

        $query = User::query()
            ->withCount('weddings')
            ->with(['orders' => function ($q) {
                $q->where('status', 'paid');
            }]);

        // 1. Text Search: Name, Email, Phone
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // 2. Role Filter
        if ($role === 'customer') {
            $query->where(function ($q) {
                $q->whereIn('role', ['customer', 'user'])->orWhereNull('role');
            });
        } elseif ($role === 'admin') {
            $query->whereIn('role', ['admin', 'manager', 'staff']);
        } elseif ($role === 'partner') {
            $query->where('role', 'partner');
        } elseif (! empty($role) && $role !== 'all') {
            $query->where('role', $role);
        }

        $users = $query->latest('id')->paginate(15)->withQueryString();

        // Summary Counts for Navigation Tabs
        $totalCount = User::count();
        $customerCount = User::where(function ($q) {
            $q->whereIn('role', ['customer', 'user'])->orWhereNull('role');
        })->count();
        $adminCount = User::whereIn('role', ['admin', 'manager', 'staff'])->count();
        $partnerCount = User::where('role', 'partner')->count();

        return view('admin.pages.users.index', [
            'users' => $users,
            'currentSearch' => $search,
            'currentRole' => $role,
            'totalCount' => $totalCount,
            'customerCount' => $customerCount,
            'adminCount' => $adminCount,
            'partnerCount' => $partnerCount,
        ]);
    }

    /**
     * Display detailed user information including their weddings and order history.
     */
    public function show(int $id): View
    {
        $user = User::with([
            'weddings.invitations.template',
            'orders.plan',
        ])->findOrFail($id);

        $totalSpent = $user->orders->where('status', 'paid')->sum('amount');

        return view('admin.pages.users.show', [
            'user' => $user,
            'totalSpent' => $totalSpent,
        ]);
    }

    /**
     * Change user role (RBAC: Admin, Manager, Staff, Partner, Customer).
     */
    public function changeRole(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'role' => 'required|string|in:admin,manager,staff,partner,customer',
        ]);

        $user = User::findOrFail($id);
        $newRole = $request->input('role');

        // Security Defense: Admin cannot demote their own account
        if ($user->id === Auth::id() && $newRole !== 'admin') {
            return back()->with('error', 'Bạn không thể tự hạ quyền tài khoản Quản trị viên của chính mình.');
        }

        $user->update(['role' => $newRole]);

        return back()->with('success', "Đã cập nhật vai trò tài khoản {$user->name} thành \"{$user->role_label}\" thành công.");
    }

    /**
     * Reset password for customer.
     */
    public function resetPassword(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return back()->with('success', "Đã đặt lại mật khẩu mới cho người dùng \"{$user->name}\" thành công.");
    }
}
