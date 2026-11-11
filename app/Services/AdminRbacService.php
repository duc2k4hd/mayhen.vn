<?php

namespace App\Services;

use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AdminRbacService
{
    /**
     * Danh mục các vai trò vận hành nội bộ theo chuẩn PRD V3 (Mục 2.1 & 67).
     */
    public const ROLES = [
        'admin' => [
            'key' => 'admin',
            'label' => 'Quản Trị Viên Cấp Cao (Super Admin)',
            'badge_color' => '#be185d',
            'bg_color' => '#fdf2f8',
            'description' => 'Toàn quyền cấu hình nền tảng, quản trị ma trận phân quyền và can thiệp hệ thống có audit.',
        ],
        'manager' => [
            'key' => 'manager',
            'label' => 'Quản Lý Vận Hành (Platform Operator)',
            'badge_color' => '#1d4ed8',
            'bg_color' => '#eff6ff',
            'description' => 'Giám sát tiệc cưới, kiểm duyệt nội dung, điều phối vận hành và hỗ trợ khách hàng đa kênh.',
        ],
        'designer' => [
            'key' => 'designer',
            'label' => 'Chuyên Viên Mẫu Thiệp (Template Designer)',
            'badge_color' => '#7c3aed',
            'bg_color' => '#f5f3ff',
            'description' => 'Thiết kế mẫu thiệp mới, biên tập asset đồ họa, quản trị phiên bản và schema cấu hình Studio.',
        ],
        'finance' => [
            'key' => 'finance',
            'label' => 'Kế Toán & Tài Chính (Finance & Billing)',
            'badge_color' => '#047857',
            'bg_color' => '#ecfdf5',
            'description' => 'Quản lý đơn hàng, đối soát dòng tiền VietQR/SePay, xuất hóa đơn VAT và phê duyệt hoàn tiền.',
        ],
        'support' => [
            'key' => 'support',
            'label' => 'Chăm Sóc Khách Hàng (Customer Support)',
            'badge_color' => '#b45309',
            'bg_color' => '#fffbeb',
            'description' => 'Tiếp nhận hỗ trợ khách hàng, giải quyết khiếu nại, theo dõi tiến độ gửi tin và chẩn đoán sự cố.',
        ],
        'staff' => [
            'key' => 'staff',
            'label' => 'Nhân Viên Sự Kiện & Lễ Tân (Event & Check-in Staff)',
            'badge_color' => '#475569',
            'bg_color' => '#f1f5f9',
            'description' => 'Vận hành trạm quét QR điểm danh đón khách tại sảnh tiệc, tra cứu khách mời và điều phối bàn.',
        ],
    ];

    /**
     * Cây 9 nhóm quyền hành động (Action-Level Permissions) theo chuẩn Mục 2.2 & 67 PRD V3.
     */
    public const PERMISSION_GROUPS = [
        'templates' => [
            'label' => 'Kho Mẫu Thiệp Cưới',
            'permissions' => [
                'templates.view' => 'Xem danh sách & chi tiết mẫu thiệp',
                'templates.create' => 'Tạo mẫu thiệp mới / Import ZIP',
                'templates.edit' => 'Chỉnh sửa bố cục & mã nguồn mẫu',
                'templates.publish' => 'Phê duyệt & Xuất bản mẫu lên Store',
                'templates.archive' => 'Lưu trữ & Ngưng phát hành mẫu',
            ],
        ],
        'weddings' => [
            'label' => 'Dự Án Tiệc Cưới',
            'permissions' => [
                'weddings.view' => 'Xem danh sách & thông tin tiệc cưới',
                'weddings.edit' => 'Hiệu chỉnh thông tin & ngày cưới',
                'weddings.publish' => 'Xuất bản & cấp phát tên miền thiệp',
                'weddings.delete' => 'Xóa vĩnh viễn dự án tiệc cưới',
            ],
        ],
        'guests' => [
            'label' => 'Quản Lý Khách Mời',
            'permissions' => [
                'guests.view' => 'Xem danh bạ quan khách & bàn tiệc',
                'guests.import' => 'Nhập danh sách khách hàng loạt (Excel/CSV)',
                'guests.export' => 'Xuất danh bạ khách & mã QR thiệp',
                'guests.delete' => 'Xóa thông tin khách mời',
            ],
        ],
        'rsvp' => [
            'label' => 'Phản Hồi RSVP & Lời Chúc',
            'permissions' => [
                'rsvp.view' => 'Xem phản hồi tham dự & thực đơn chọn',
                'rsvp.moderate' => 'Kiểm duyệt lời chúc mừng & sổ lưu bút',
                'rsvp.export' => 'Xuất báo cáo điểm danh & RSVP',
            ],
        ],
        'finance' => [
            'label' => 'Tài Chính & Đơn Hàng',
            'permissions' => [
                'finance.view' => 'Xem danh sách đơn hàng & doanh thu',
                'finance.refund' => 'Thực hiện hoàn tiền cho khách hàng',
                'finance.reconcile' => 'Đối soát giao dịch ngân hàng & SePay',
            ],
        ],
        'settings' => [
            'label' => 'Cấu Hình Hệ Thống',
            'permissions' => [
                'settings.view' => 'Xem tham số nền tảng & cổng thông báo',
                'settings.edit' => 'Hiệu chỉnh cấu hình hệ thống & SEO',
                'settings.billing' => 'Cấu hình cổng thanh toán & tài khoản nhận',
            ],
        ],
        'support' => [
            'label' => 'Hỗ Trợ & Chăm Sóc',
            'permissions' => [
                'support.view' => 'Xem danh sách ticket & phản hồi khách',
                'support.access_sensitive' => 'Xem dữ liệu nhạy cảm theo ủy quyền',
                'support.impersonate' => 'Đăng nhập hỗ trợ khách hàng (Impersonate)',
            ],
        ],
        'audit' => [
            'label' => 'Nhật Ký & Giám Sát',
            'permissions' => [
                'audit.view' => 'Xem nhật ký kiểm toán hệ thống & API log',
                'audit.export' => 'Xuất nhật ký kiểm toán phục vụ thanh tra',
            ],
        ],
        'security' => [
            'label' => 'An Ninh & Hạ Tầng',
            'permissions' => [
                'security.view' => 'Xem đài cảnh báo nguy cơ an ninh & IP',
                'security.manage_ip' => 'Thêm/Xóa danh sách chặn IP tường lửa',
                'security.backup' => 'Tạo & Phục hồi sao lưu cơ sở dữ liệu',
            ],
        ],
    ];

    /**
     * Bảng ma trận phân quyền mặc định theo Mục 67 PRD V3.
     */
    protected const DEFAULT_ROLE_PERMISSIONS = [
        'admin' => [
            'templates.view', 'templates.create', 'templates.edit', 'templates.publish', 'templates.archive',
            'weddings.view', 'weddings.edit', 'weddings.publish', 'weddings.delete',
            'guests.view', 'guests.import', 'guests.export', 'guests.delete',
            'rsvp.view', 'rsvp.moderate', 'rsvp.export',
            'finance.view', 'finance.refund', 'finance.reconcile',
            'settings.view', 'settings.edit', 'settings.billing',
            'support.view', 'support.access_sensitive', 'support.impersonate',
            'audit.view', 'audit.export',
            'security.view', 'security.manage_ip', 'security.backup',
        ],
        'manager' => [
            'templates.view', 'templates.create', 'templates.edit',
            'weddings.view', 'weddings.edit', 'weddings.publish',
            'guests.view', 'guests.import', 'guests.export',
            'rsvp.view', 'rsvp.moderate', 'rsvp.export',
            'finance.view', 'finance.reconcile',
            'settings.view',
            'support.view', 'support.impersonate',
            'audit.view',
            'security.view',
        ],
        'designer' => [
            'templates.view', 'templates.create', 'templates.edit', 'templates.publish',
            'weddings.view',
            'audit.view',
        ],
        'finance' => [
            'finance.view', 'finance.refund', 'finance.reconcile',
            'settings.view', 'settings.billing',
            'audit.view', 'audit.export',
        ],
        'support' => [
            'weddings.view',
            'guests.view',
            'rsvp.view', 'rsvp.moderate',
            'support.view', 'support.impersonate',
            'audit.view',
        ],
        'staff' => [
            'guests.view', 'guests.import',
            'rsvp.view', 'rsvp.moderate',
        ],
    ];

    /**
     * Thống kê 4 chỉ số KPI về nhân sự vận hành.
     */
    public function getStaffStats(): array
    {
        $internalRoles = array_keys(self::ROLES);

        $totalStaff = User::whereIn('role', $internalRoles)->count();
        $superAdmins = User::where('role', 'admin')->count();
        $activeStaff = User::whereIn('role', $internalRoles)->where('is_locked', false)->count();
        $lockedStaff = User::whereIn('role', $internalRoles)->where('is_locked', true)->count();

        return [
            'total_staff' => $totalStaff,
            'super_admins' => $superAdmins,
            'active_staff' => $activeStaff,
            'locked_staff' => $lockedStaff,
        ];
    }

    /**
     * Lấy danh sách nhân sự nội bộ có phân trang và bộ lọc.
     */
    public function getStaffList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $internalRoles = array_keys(self::ROLES);

        $query = User::whereIn('role', $internalRoles)
            ->latest('id');

        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        if (! empty($filters['role']) && isset(self::ROLES[$filters['role']])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'active') {
                $query->where('is_locked', false);
            } elseif ($filters['status'] === 'locked') {
                $query->where('is_locked', true);
            }
        }

        return $query->paginate($perPage);
    }

    /**
     * Lấy ma trận phân quyền hiện hành (merge cấu hình tùy biến với mặc định).
     */
    public function getRoleMatrix(): array
    {
        $setting = PlatformSetting::where('group', PlatformSetting::GROUP_SECURITY)
            ->where('key', 'rbac_role_permissions_matrix')
            ->first();

        if ($setting && ! empty($setting->value)) {
            $customMatrix = json_decode($setting->value, true);
            if (is_array($customMatrix)) {
                // Đảm bảo có đầy đủ các roles
                $merged = self::DEFAULT_ROLE_PERMISSIONS;
                foreach ($customMatrix as $role => $perms) {
                    if (isset(self::ROLES[$role]) && is_array($perms)) {
                        $merged[$role] = $perms;
                    }
                }

                return $merged;
            }
        }

        return self::DEFAULT_ROLE_PERMISSIONS;
    }

    /**
     * Lưu cấu hình ma trận phân quyền RBAC vào PlatformSetting.
     */
    public function saveRoleMatrix(array $matrix, int|User|null $operator = null): bool
    {
        $operatorId = $operator instanceof User ? $operator->id : (is_numeric($operator) ? (int) $operator : null);
        $cleanMatrix = [];

        foreach (array_keys(self::ROLES) as $role) {
            // Super admin luôn giữ full quyền an toàn tuyệt đối
            if ($role === 'admin') {
                $cleanMatrix['admin'] = self::DEFAULT_ROLE_PERMISSIONS['admin'];

                continue;
            }

            $perms = $matrix[$role] ?? [];
            $validPerms = [];

            if (is_array($perms)) {
                foreach ($perms as $perm) {
                    if ($this->isValidPermission($perm)) {
                        $validPerms[] = $perm;
                    }
                }
            }

            $cleanMatrix[$role] = array_values(array_unique($validPerms));
        }

        $setting = PlatformSetting::firstOrNew([
            'group' => PlatformSetting::GROUP_SECURITY,
            'key' => 'rbac_role_permissions_matrix',
        ]);

        $setting->value = json_encode($cleanMatrix, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $setting->label = 'Ma trận phân quyền vai trò vận hành RBAC';
        $setting->description = 'Cấu hình phân bổ quyền hạn hành động theo từng vai trò nhân sự nội bộ theo Mục 67 PRD V3.';
        $setting->is_secret = false;
        $setting->version = ($setting->version ?? 0) + 1;
        $setting->updated_by = $operatorId;
        $setting->save();

        return true;
    }

    /**
     * Kiểm tra một tài khoản có quyền thực hiện hành động hay không (Hạt nhân bảo mật PRD 2.2).
     */
    public function hasPermission(User $user, string $permission): bool
    {
        // 1. Tài khoản bị khóa thì từ chối toàn bộ
        if ($user->isLocked()) {
            return false;
        }

        // 2. Super Admin luôn có toàn quyền tuyệt đối
        if ($user->isSuperAdmin()) {
            return true;
        }

        // 3. Đối chiếu role với ma trận phân quyền
        $matrix = $this->getRoleMatrix();
        $rolePermissions = $matrix[$user->role] ?? [];

        return in_array($permission, $rolePermissions, true);
    }

    /**
     * Chuyển giao vai trò vận hành cho nhân sự.
     */
    public function updateStaffRole(int $userId, string $newRole, string $operator): User
    {
        if (! isset(self::ROLES[$newRole])) {
            throw new \InvalidArgumentException("Vai trò '{$newRole}' không hợp lệ.");
        }

        $staff = User::findOrFail($userId);

        // Bảo vệ: Nếu đang là Super Admin duy nhất thì không được hạ quyền
        if ($staff->role === 'admin' && $newRole !== 'admin') {
            $adminCount = User::where('role', 'admin')->where('is_locked', false)->count();
            if ($adminCount <= 1) {
                throw new \DomainException('Không thể hạ quyền Quản trị viên cấp cao duy nhất đang hoạt động của hệ thống.');
            }
        }

        $staff->role = $newRole;
        $staff->save();

        return $staff;
    }

    /**
     * Khóa hoặc mở khóa quyền nhân sự tức thời.
     */
    public function toggleStaffLock(int $userId, ?string $reason, string $operator): array
    {
        $staff = User::findOrFail($userId);

        // Không cho phép tự khóa chính mình
        if ($staff->name === $operator || $staff->email === $operator) {
            throw new \DomainException('Bạn không thể tự khóa tài khoản của chính mình.');
        }

        if ($staff->isLocked()) {
            $staff->is_locked = false;
            $staff->lock_reason = null;
            $staff->locked_at = null;
            $staff->save();

            return [
                'status' => 'unlocked',
                'message' => "Đã mở khóa và khôi phục quyền truy cập cho nhân sự {$staff->name}.",
            ];
        }

        // Không cho phép khóa Super Admin duy nhất
        if ($staff->role === 'admin') {
            $activeAdmins = User::where('role', 'admin')->where('is_locked', false)->count();
            if ($activeAdmins <= 1) {
                throw new \DomainException('Không thể khóa Quản trị viên cấp cao duy nhất của hệ thống.');
            }
        }

        $staff->is_locked = true;
        $staff->lock_reason = $reason ?: "Tạm đình chỉ quyền vận hành bởi {$operator}";
        $staff->locked_at = now();
        $staff->save();

        return [
            'status' => 'locked',
            'message' => "Đã khóa và đình chỉ tức thời quyền truy cập của nhân sự {$staff->name}.",
        ];
    }

    /**
     * Tạo tài khoản nhân sự nội bộ mới.
     */
    public function createStaff(array $data, string $operator): User
    {
        $role = $data['role'] ?? 'staff';
        if (! isset(self::ROLES[$role])) {
            throw new \InvalidArgumentException("Vai trò '{$role}' không hợp lệ.");
        }

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => $role,
            'is_locked' => false,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Xuất danh sách nhân sự nội bộ định dạng CSV UTF-8 BOM.
     */
    public function exportStaffCsv(array $filters = []): Response
    {
        $internalRoles = array_keys(self::ROLES);

        $query = User::whereIn('role', $internalRoles)->latest('id');

        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        if (! empty($filters['role']) && isset(self::ROLES[$filters['role']])) {
            $query->where('role', $filters['role']);
        }

        $staffMembers = $query->get();

        $filename = 'mayhen_staff_rbac_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Mã Nhân Sự',
            'Họ Và Tên',
            'Email',
            'Số Điện Thoại',
            'Mã Vai Trò',
            'Tên Vai Trò Vận Hành',
            'Trạng Thái',
            'Lý Do Khóa (Nếu Có)',
            'Ngày Khóa',
            'Ngày Khởi Tạo',
        ];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM
        fputcsv($handle, $headers);

        foreach ($staffMembers as $staff) {
            $roleInfo = self::ROLES[$staff->role] ?? null;
            $roleLabel = $roleInfo ? $roleInfo['label'] : $staff->role;
            $statusLabel = $staff->is_locked ? 'Đang bị khóa' : 'Đang hoạt động';

            fputcsv($handle, [
                $staff->id,
                $staff->name,
                $staff->email,
                $staff->phone ?? 'N/A',
                $staff->role,
                $roleLabel,
                $statusLabel,
                $staff->lock_reason ?? '',
                $staff->locked_at ? $staff->locked_at->format('d/m/Y H:i') : '',
                $staff->created_at ? $staff->created_at->format('d/m/Y H:i') : '',
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Kiểm tra một permission key có hợp lệ trong cây danh mục hay không.
     */
    protected function isValidPermission(string $permissionKey): bool
    {
        foreach (self::PERMISSION_GROUPS as $group) {
            if (isset($group['permissions'][$permissionKey])) {
                return true;
            }
        }

        return false;
    }
}
