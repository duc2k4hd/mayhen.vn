<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminRbacService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminRbacController extends Controller
{
    public function __construct(
        protected AdminRbacService $rbacService
    ) {}

    /**
     * Màn hình chính Trung Tâm Phân Quyền & Bàn Giao Vai Trò Vận Hành (Mục 67 PRD V3).
     */
    public function index(Request $request): View
    {
        $filters = [
            'q' => $request->input('q', ''),
            'role' => $request->input('role', ''),
            'status' => $request->input('status', 'all'),
        ];

        $stats = $this->rbacService->getStaffStats();
        $staffMembers = $this->rbacService->getStaffList($filters, 15);
        $roleMatrix = $this->rbacService->getRoleMatrix();

        return view('admin.pages.rbac.index', [
            'stats' => $stats,
            'staffMembers' => $staffMembers,
            'roleMatrix' => $roleMatrix,
            'roles' => AdminRbacService::ROLES,
            'permissionGroups' => AdminRbacService::PERMISSION_GROUPS,
            'filters' => $filters,
            'activeTab' => $request->input('tab', 'staff'),
        ]);
    }

    /**
     * Tạo tài khoản nhân sự nội bộ mới.
     */
    public function storeStaff(Request $request): RedirectResponse
    {
        $validRoles = array_keys(AdminRbacService::ROLES);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', Rule::in($validRoles)],
        ], [
            'name.required' => 'Vui lòng nhập họ và tên nhân sự.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng.',
            'password.required' => 'Vui lòng thiết lập mật khẩu khởi tạo.',
            'password.min' => 'Mật khẩu phải có độ dài tối thiểu 8 ký tự.',
            'role.required' => 'Vui lòng chọn vai trò vận hành.',
            'role.in' => 'Vai trò vận hành không hợp lệ.',
        ]);

        $operator = $request->user()?->name ?? 'Admin';
        $staff = $this->rbacService->createStaff($validated, $operator);

        return back()->with('success', "Đã tạo tài khoản nhân sự {$staff->name} ({$staff->role}) thành công.");
    }

    /**
     * Chuyển giao vai trò vận hành cho nhân sự.
     */
    public function updateRole(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $validRoles = array_keys(AdminRbacService::ROLES);

        $request->validate([
            'role' => ['required', 'string', Rule::in($validRoles)],
        ]);

        $operator = $request->user()?->name ?? 'Admin';

        try {
            $staff = $this->rbacService->updateStaffRole($id, $request->input('role'), $operator);
            $msg = "Đã chuyển giao vai trò của {$staff->name} sang '{$staff->role_label}' thành công.";

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $msg,
                    'new_role' => $staff->role,
                    'new_role_label' => $staff->role_label,
                ]);
            }

            return back()->with('success', $msg);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['role' => $e->getMessage()]);
        }
    }

    /**
     * Khóa hoặc mở khóa quyền nhân sự tức thời.
     */
    public function toggleLock(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $operator = $request->user()?->name ?? 'Admin';
        $reason = $request->input('reason');

        try {
            $result = $this->rbacService->toggleStaffLock($id, $reason, $operator);

            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'status' => $result['status'],
                    'message' => $result['message'],
                ]);
            }

            return back()->with('success', $result['message']);
        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['lock' => $e->getMessage()]);
        }
    }

    /**
     * Lưu cấu hình ma trận phân quyền RBAC.
     */
    public function updateMatrix(Request $request): RedirectResponse
    {
        $operatorId = $request->user()?->id;
        $matrix = $request->input('matrix', []);

        if (! is_array($matrix)) {
            return back()->withErrors(['matrix' => 'Dữ liệu ma trận không đúng định dạng mảng.']);
        }

        $this->rbacService->saveRoleMatrix($matrix, $operatorId);

        return redirect()->route('admin.rbac.index', ['tab' => 'matrix'])
            ->with('success', 'Đã lưu và cập nhật ma trận phân quyền RBAC thành công theo Mục 67 PRD V3.');
    }

    /**
     * Xuất danh sách nhân sự nội bộ định dạng CSV UTF-8 BOM.
     */
    public function export(Request $request): Response
    {
        return $this->rbacService->exportStaffCsv($request->all());
    }
}
