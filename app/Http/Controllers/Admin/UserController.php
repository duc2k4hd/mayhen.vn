<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            $query->where('role', 'admin');
        } elseif ($role === 'manager') {
            $query->where('role', 'manager');
        } elseif ($role === 'staff') {
            $query->where('role', 'staff');
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
        $adminCount = User::where('role', 'admin')->count();
        $managerCount = User::where('role', 'manager')->count();
        $staffCount = User::where('role', 'staff')->count();
        $partnerCount = User::where('role', 'partner')->count();

        return view('admin.pages.users.index', [
            'users' => $users,
            'currentSearch' => $search,
            'currentRole' => $role,
            'totalCount' => $totalCount,
            'customerCount' => $customerCount,
            'adminCount' => $adminCount,
            'managerCount' => $managerCount,
            'staffCount' => $staffCount,
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

        AuditLogger::log(
            'change_user_role',
            'User',
            $user->id,
            "Đổi vai trò tài khoản {$user->email} ({$user->name}) thành \"{$user->role_label}\"",
            ['user_id' => $user->id, 'new_role' => $newRole]
        );

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

        // Security Defense: Manager cannot reset password of Super Admin
        if ($user->isSuperAdmin() && ! Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Chỉ Quản trị viên cấp cao mới có thẩm quyền đặt lại mật khẩu cho tài khoản Quản trị viên.');
        }

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        AuditLogger::log(
            'reset_user_password',
            'User',
            $user->id,
            "Đặt lại mật khẩu cho tài khoản {$user->email} ({$user->name})",
            ['user_id' => $user->id]
        );

        return back()->with('success', "Đã đặt lại mật khẩu mới cho người dùng \"{$user->name}\" thành công.");
    }

    /**
     * Export users list as UTF-8 BOM CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $search = trim((string) $request->input('q', ''));
        $role = $request->input('role');

        $query = User::query()
            ->withCount('weddings')
            ->with(['orders' => function ($q) {
                $q->where('status', 'paid');
            }]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($role === 'customer') {
            $query->where(function ($q) {
                $q->whereIn('role', ['customer', 'user'])->orWhereNull('role');
            });
        } elseif ($role === 'admin') {
            $query->where('role', 'admin');
        } elseif ($role === 'manager') {
            $query->where('role', 'manager');
        } elseif ($role === 'staff') {
            $query->where('role', 'staff');
        } elseif ($role === 'partner') {
            $query->where('role', 'partner');
        } elseif (! empty($role) && $role !== 'all') {
            $query->where('role', $role);
        }

        $users = $query->latest('id')->get();

        $filename = 'danh-sach-nguoi-dung-mayhen-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'STT',
                'Mã ID',
                'Họ và Tên',
                'Email',
                'Số Điện Thoại',
                'Vai Trò (Phân Quyền)',
                'Số Thiệp Cưới Đã Tạo',
                'Tổng Tiền Đã Thanh Toán (VNĐ)',
                'Ngày Tham Gia',
            ]);

            $index = 1;
            foreach ($users as $user) {
                $totalSpent = $user->orders->sum('amount');
                fputcsv($handle, [
                    $index++,
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone ?? 'Chưa cập nhật',
                    $user->role_label,
                    $user->weddings_count,
                    $totalSpent,
                    $user->created_at ? $user->created_at->format('d/m/Y H:i:s') : 'N/A',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
