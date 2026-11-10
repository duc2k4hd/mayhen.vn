<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CsvSanitizerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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
        $status = $request->input('status');

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

        // 3. Status Filter (Active / Locked)
        if ($status === 'locked') {
            $query->where('is_locked', true);
        } elseif ($status === 'active') {
            $query->where(function ($q) {
                $q->where('is_locked', false)->orWhereNull('is_locked');
            });
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
        $lockedCount = User::where('is_locked', true)->count();

        return view('admin.pages.users.index', [
            'users' => $users,
            'currentSearch' => $search,
            'currentRole' => $role,
            'currentStatus' => $status,
            'totalCount' => $totalCount,
            'customerCount' => $customerCount,
            'adminCount' => $adminCount,
            'managerCount' => $managerCount,
            'staffCount' => $staffCount,
            'partnerCount' => $partnerCount,
            'lockedCount' => $lockedCount,
        ]);
    }

    /**
     * Display detailed user information including their weddings, order history, and support tickets.
     */
    public function show(int $id): View
    {
        $user = User::with([
            'weddings.invitations.template',
            'orders.plan',
        ])->findOrFail($id);

        $totalSpent = $user->orders->where('status', 'paid')->sum('amount');
        $supportTickets = Schema::hasTable('support_tickets')
            ? SupportTicket::where('user_id', $user->id)->latest()->get()
            : collect();

        return view('admin.pages.users.show', [
            'user' => $user,
            'totalSpent' => $totalSpent,
            'supportTickets' => $supportTickets,
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

        // Security Defense 1: Admin cannot demote their own account
        if ($user->id === Auth::id() && $newRole !== 'admin') {
            return back()->with('error', 'Bạn không thể tự hạ quyền tài khoản Quản trị viên của chính mình.');
        }

        // Security Defense 2: Cannot demote the last Super Admin in the system (ADM-A05-009)
        if ($user->role === 'admin' && $newRole !== 'admin') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Không thể hạ quyền Quản trị viên cấp cao duy nhất còn lại trong hệ thống.');
            }
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
            'password' => 'required|string|min:8|confirmed',
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
     * Lock a user account (ADM-A04-014).
     */
    public function lock(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($id);

        // Security Defense 1: Cannot lock own account
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Bạn không thể tự khóa tài khoản của chính mình.');
        }

        // Security Defense 2: Cannot lock the last remaining active Super Admin
        if ($user->isSuperAdmin()) {
            if (! Auth::user()?->isSuperAdmin()) {
                return back()->with('error', 'Chỉ Quản trị viên cấp cao mới có quyền khóa tài khoản Quản trị viên.');
            }

            $activeAdminCount = User::where('role', 'admin')->where(function ($q) {
                $q->where('is_locked', false)->orWhereNull('is_locked');
            })->count();

            if ($activeAdminCount <= 1) {
                return back()->with('error', 'Không thể khóa Quản trị viên cấp cao duy nhất còn hoạt động trong hệ thống.');
            }
        }

        $reason = trim((string) $request->input('reason', ''));
        if ($reason === '') {
            $reason = 'Khóa bởi Quản trị viên do vi phạm quy chế hoặc yêu cầu nghiệp vụ.';
        }

        $user->update([
            'is_locked' => true,
            'lock_reason' => $reason,
            'locked_at' => now(),
        ]);

        AuditLogger::log(
            'lock_user',
            'User',
            $user->id,
            "Khóa tài khoản {$user->email} ({$user->name}) với lý do: {$reason}",
            ['user_id' => $user->id, 'reason' => $reason]
        );

        return back()->with('success', "Đã khóa tài khoản {$user->name} thành công.");
    }

    /**
     * Unlock a user account (ADM-A04-015).
     */
    public function unlock(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        // Security Defense: Manager cannot unlock Super Admin unless is Super Admin
        if ($user->isSuperAdmin() && ! Auth::user()?->isSuperAdmin()) {
            return back()->with('error', 'Chỉ Quản trị viên cấp cao mới có quyền mở khóa tài khoản Quản trị viên.');
        }

        $user->update([
            'is_locked' => false,
            'lock_reason' => null,
            'locked_at' => null,
        ]);

        AuditLogger::log(
            'unlock_user',
            'User',
            $user->id,
            "Mở khóa tài khoản {$user->email} ({$user->name})",
            ['user_id' => $user->id]
        );

        return back()->with('success', "Đã mở khóa tài khoản {$user->name} thành công.");
    }

    /**
     * Export users list as UTF-8 BOM CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $search = trim((string) $request->input('q', ''));
        $role = $request->input('role');
        $status = $request->input('status');

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

        if ($status === 'locked') {
            $query->where('is_locked', true);
        } elseif ($status === 'active') {
            $query->where(function ($q) {
                $q->where('is_locked', false)->orWhereNull('is_locked');
            });
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
                'Trạng Thái',
                'Số Thiệp Cưới Đã Tạo',
                'Tổng Tiền Đã Thanh Toán (VNĐ)',
                'Ngày Tham Gia',
            ]);

            $index = 1;
            foreach ($users as $user) {
                $totalSpent = $user->orders->sum('amount');
                $statusLabel = $user->isLocked() ? "Đã khóa ({$user->lock_reason})" : 'Hoạt động';
                fputcsv($handle, CsvSanitizerService::row([
                    $index++,
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone ?? 'Chưa cập nhật',
                    $user->role_label,
                    $statusLabel,
                    $user->weddings_count,
                    $totalSpent,
                    $user->created_at ? $user->created_at->format('d/m/Y H:i:s') : 'N/A',
                ]));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Export all personal data for a customer (Luật 91/2025/QH15 - Quyền tiếp cận dữ liệu cá nhân DSAR).
     */
    public function exportPersonalData(Request $request, int $id): StreamedResponse
    {
        $user = User::with([
            'weddings.invitations.template',
            'weddings.guests.response',
            'weddings.wishes',
            'orders.plan',
        ])->findOrFail($id);

        $tickets = Schema::hasTable('support_tickets')
            ? SupportTicket::where('user_id', $user->id)->with('replies')->get()
            : collect();

        $exportData = [
            'system' => 'Mây Hẹn (mayhen.vn)',
            'compliance' => 'Luật Bảo Vệ Dữ Liệu Cá Nhân Việt Nam (Luật 91/2025/QH15) — Right to Access (DSAR)',
            'exported_at' => now()->toIso8601String(),
            'operator' => [
                'id' => Auth::id(),
                'name' => Auth::user()?->name,
                'email' => Auth::user()?->email,
            ],
            'personal_profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'role_label' => $user->role_label,
                'is_locked' => (bool) $user->is_locked,
                'lock_reason' => $user->lock_reason,
                'created_at' => $user->created_at?->toIso8601String(),
                'updated_at' => $user->updated_at?->toIso8601String(),
            ],
            'weddings' => $user->weddings->map(function ($w) {
                return [
                    'id' => $w->id,
                    'groom_name' => $w->groom_name,
                    'bride_name' => $w->bride_name,
                    'groom_parents' => $w->groom_parents,
                    'bride_parents' => $w->bride_parents,
                    'wedding_date' => $w->wedding_date?->format('Y-m-d'),
                    'venue_name' => $w->venue_name,
                    'venue_address' => $w->venue_address,
                    'bank_info' => [
                        'bank_name' => $w->bank_name,
                        'bank_account_number' => $w->bank_account_number,
                        'bank_account_name' => $w->bank_account_name,
                    ],
                    'invitations' => $w->invitations->map(fn ($inv) => [
                        'id' => $inv->id,
                        'slug' => $inv->slug,
                        'template_name' => $inv->template?->name,
                        'is_published' => (bool) $inv->is_published,
                        'view_count' => $inv->view_count,
                        'created_at' => $inv->created_at?->toIso8601String(),
                    ]),
                    'guests_count' => $w->guests->count(),
                    'guests' => $w->guests->map(fn ($g) => [
                        'id' => $g->id,
                        'name' => $g->name,
                        'phone' => $g->phone,
                        'group_name' => $g->group_name,
                        'table_name' => $g->table_name,
                        'checked_in_at' => $g->checked_in_at?->toIso8601String(),
                        'rsvp_status' => $g->response?->attending_status,
                    ]),
                    'wishes' => $w->wishes->map(fn ($wish) => [
                        'id' => $wish->id,
                        'name' => $wish->name,
                        'message' => $wish->message,
                        'is_approved' => (bool) $wish->is_approved,
                        'created_at' => $wish->created_at?->toIso8601String(),
                    ]),
                ];
            }),
            'order_history' => $user->orders->map(function ($o) {
                return [
                    'id' => $o->id,
                    'order_code' => $o->order_code,
                    'plan_name' => $o->plan?->name,
                    'amount' => $o->amount,
                    'status' => $o->status,
                    'payment_method' => $o->payment_method,
                    'paid_at' => $o->paid_at?->toIso8601String(),
                ];
            }),
            'support_tickets' => $tickets->map(function ($t) {
                return [
                    'id' => $t->id,
                    'ticket_code' => $t->ticket_code,
                    'subject' => $t->subject,
                    'status' => $t->status,
                    'priority' => $t->priority,
                    'created_at' => $t->created_at?->toIso8601String(),
                    'replies_count' => $t->replies->count(),
                ];
            }),
        ];

        AuditLogger::log(
            'export_personal_data',
            'User',
            $user->id,
            "Trích xuất toàn bộ dữ liệu cá nhân (DSAR) của tài khoản {$user->email} ({$user->name}) theo Luật 91/2025/QH15",
            ['user_id' => $user->id, 'operator_id' => Auth::id()]
        );

        $filename = "mayhen_dsar_export_user_{$user->id}_".now()->format('Ymd_His').'.json';

        return response()->streamDownload(function () use ($exportData) {
            echo json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    /**
     * Anonymize customer PII while preserving financial orders (Right to be Forgotten - Luật 91/2025/QH15).
     */
    public function anonymize(Request $request, int $id): RedirectResponse
    {
        $user = User::with('weddings')->findOrFail($id);

        // Security Defense 1: Cannot anonymize own account
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Bạn không thể tự ẩn danh hóa tài khoản của chính mình.');
        }

        // Security Defense 2: Cannot anonymize Admin or Manager accounts
        if ($user->isAdmin()) {
            return back()->with('error', 'Không thể ẩn danh hóa tài khoản Quản trị viên hoặc Quản lý hệ thống.');
        }

        $oldEmail = $user->email;
        $oldName = $user->name;
        $anonymizedEmail = 'anonymized_'.$user->id.'_'.time().'@deleted.mayhen.vn';
        $anonymizedName = 'Khách Hàng Đã Ẩn Danh #'.$user->id;

        // 1. Mask User PII & Lock Account
        $user->update([
            'name' => $anonymizedName,
            'email' => $anonymizedEmail,
            'phone' => '0000000000',
            'password' => Hash::make(Str::random(32)),
            'remember_token' => null,
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => Auth::id(),
            'lock_reason' => 'Đã ẩn danh hóa dữ liệu cá nhân theo yêu cầu (Luật 91/2025/QH15).',
        ]);

        // 2. Mask Wedding PII (couple parents, bank accounts)
        foreach ($user->weddings as $wedding) {
            $wedding->update([
                'bank_account_number' => null,
                'bank_account_name' => null,
                'bank_name' => null,
                'groom_parents' => null,
                'bride_parents' => null,
                'client_notes' => 'Dữ liệu cá nhân đã được ẩn danh theo Luật 91/2025/QH15.',
            ]);
        }

        AuditLogger::log(
            'anonymize_user_data',
            'User',
            $user->id,
            "Ẩn danh hóa dữ liệu cá nhân tài khoản ID #{$user->id} ({$oldEmail}) theo Luật 91/2025/QH15",
            [
                'user_id' => $user->id,
                'operator_id' => Auth::id(),
                'anonymized_email' => $anonymizedEmail,
            ]
        );

        return back()->with('success', "Đã hoàn tất ẩn danh hóa dữ liệu cá nhân cho tài khoản ID #{$user->id} theo đúng Luật 91/2025/QH15. Sổ cái tài chính vẫn được bảo toàn nguyên vẹn.");
    }
}
