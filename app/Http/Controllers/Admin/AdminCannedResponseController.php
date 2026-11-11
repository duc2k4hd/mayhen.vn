<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CannedResponse;
use App\Services\CannedResponseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminCannedResponseController extends Controller
{
    public function __construct(
        protected CannedResponseService $cannedService
    ) {}

    /**
     * Màn hình quản trị danh mục mẫu phản hồi nhanh & Macro CSKH (Mục 17.1 PRD V3).
     */
    public function index(Request $request): View
    {
        $kpis = $this->cannedService->getCannedKpis();
        $categories = CannedResponse::CATEGORIES;
        $activeCategory = $request->query('category');
        $search = $request->query('search');

        $responses = $this->cannedService->getFilteredResponses($activeCategory, $search, 15);

        return view('admin.pages.support.canned.index', compact(
            'kpis',
            'categories',
            'activeCategory',
            'search',
            'responses'
        ));
    }

    /**
     * Tạo mẫu phản hồi nhanh mới.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'shortcut' => ['nullable', 'string', 'max:50', 'unique:canned_responses,shortcut'],
            'category' => ['required', 'string', Rule::in(array_keys(CannedResponse::CATEGORIES))],
            'body' => ['required', 'string', 'min:5'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề mẫu phản hồi.',
            'shortcut.unique' => 'Phím tắt này đã được sử dụng bởi một mẫu khác.',
            'category.required' => 'Vui lòng chọn phân loại chủ đề.',
            'body.required' => 'Vui lòng nhập nội dung phản hồi.',
        ]);

        try {
            $this->cannedService->createResponse($request->all(), $request->user());

            return redirect()
                ->route('admin.support.canned.index')
                ->with('success', 'Đã tạo mới mẫu phản hồi nhanh thành công.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi khi tạo mẫu: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Cập nhật thông tin mẫu phản hồi nhanh.
     */
    public function update(Request $request, CannedResponse $canned): RedirectResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'shortcut' => ['nullable', 'string', 'max:50', Rule::unique('canned_responses', 'shortcut')->ignore($canned->id)],
            'category' => ['required', 'string', Rule::in(array_keys(CannedResponse::CATEGORIES))],
            'body' => ['required', 'string', 'min:5'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề mẫu phản hồi.',
            'shortcut.unique' => 'Phím tắt này đã được sử dụng bởi một mẫu khác.',
            'category.required' => 'Vui lòng chọn phân loại chủ đề.',
            'body.required' => 'Vui lòng nhập nội dung phản hồi.',
        ]);

        try {
            $this->cannedService->updateResponse($canned, $request->all(), $request->user());

            return redirect()
                ->route('admin.support.canned.index')
                ->with('success', "Đã cập nhật mẫu phản hồi \"{$canned->title}\" thành công.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi khi cập nhật: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Xóa một mẫu phản hồi nhanh.
     */
    public function destroy(CannedResponse $canned, Request $request): RedirectResponse
    {
        try {
            $title = $canned->title;
            $this->cannedService->deleteResponse($canned, $request->user());

            return redirect()
                ->route('admin.support.canned.index')
                ->with('success', "Đã xóa mẫu phản hồi \"{$title}\" thành công.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi khi xóa: '.$e->getMessage());
        }
    }

    /**
     * Bật / Tắt kích hoạt mẫu.
     */
    public function toggle(CannedResponse $canned, Request $request): RedirectResponse
    {
        try {
            $this->cannedService->toggleResponse($canned, $request->user());
            $status = $canned->is_active ? 'kích hoạt' : 'tạm dừng';

            return redirect()
                ->route('admin.support.canned.index')
                ->with('success', "Đã {$status} mẫu phản hồi \"{$canned->title}\".");
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi khi đổi trạng thái: '.$e->getMessage());
        }
    }
}
