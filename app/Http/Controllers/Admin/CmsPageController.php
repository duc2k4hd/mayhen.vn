<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CmsPageController extends Controller
{
    /**
     * Danh sách các trang nội dung tĩnh hệ thống & tùy chỉnh.
     */
    public function index(): View
    {
        $pages = CmsPage::with('updater')->orderBy('is_system', 'desc')->orderBy('title')->get();

        $stats = [
            'total' => $pages->count(),
            'system' => $pages->where('is_system', true)->count(),
            'custom' => $pages->where('is_system', false)->count(),
            'published' => $pages->where('is_published', true)->count(),
        ];

        return view('admin.pages.cms_pages.index', compact('pages', 'stats'));
    }

    /**
     * Giao diện tạo trang nội dung mới.
     */
    public function create(): View
    {
        return view('admin.pages.cms_pages.create');
    }

    /**
     * Lưu trang nội dung mới.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:cms_pages,slug'],
            'lead_summary' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề trang.',
            'content.required' => 'Nội dung trang không được để trống.',
        ]);

        $page = CmsPage::create([
            'title' => $validated['title'],
            'slug' => ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']),
            'lead_summary' => $validated['lead_summary'] ?? null,
            'content' => $validated['content'],
            'is_system' => false,
            'is_published' => $request->boolean('is_published', true),
            'published_at' => $request->boolean('is_published', true) ? now() : null,
            'meta_title' => $validated['meta_title'] ?? $validated['title'],
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        AuditLogger::log(
            action: 'PAGE_CREATED',
            targetType: 'CmsPage',
            targetId: $page->id,
            description: "Quản trị viên tạo trang nội dung mới: {$page->title} ({$page->slug})"
        );

        return redirect()->route('admin.pages.index')->with('success', 'Đã tạo trang nội dung mới thành công.');
    }

    /**
     * Giao diện chỉnh sửa trang nội dung.
     */
    public function edit(int $id): View
    {
        $page = CmsPage::findOrFail($id);

        return view('admin.pages.cms_pages.edit', compact('page'));
    }

    /**
     * Cập nhật nội dung trang tĩnh.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $page = CmsPage::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:cms_pages,slug,'.$id],
            'lead_summary' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
        ]);

        $page->title = $validated['title'];
        if (! $page->is_system && ! empty($validated['slug'])) {
            $page->slug = Str::slug($validated['slug']);
        }
        $page->lead_summary = $validated['lead_summary'] ?? null;
        $page->content = $validated['content'];
        $page->is_published = $request->boolean('is_published', true);
        $page->meta_title = $validated['meta_title'] ?? $validated['title'];
        $page->meta_description = $validated['meta_description'] ?? null;
        $page->meta_keywords = $validated['meta_keywords'] ?? null;
        $page->updated_by = Auth::id();

        $page->save();

        AuditLogger::log(
            action: 'PAGE_UPDATED',
            targetType: 'CmsPage',
            targetId: $page->id,
            description: "Quản trị viên cập nhật trang nội dung: {$page->title} ({$page->slug})"
        );

        return redirect()->route('admin.pages.index')->with('success', 'Đã cập nhật trang nội dung thành công.');
    }

    /**
     * Xóa trang nội dung (Chỉ cho phép xóa trang tùy chỉnh, cấm xóa trang hệ thống).
     */
    public function destroy(int $id): RedirectResponse
    {
        $page = CmsPage::findOrFail($id);

        if ($page->is_system) {
            return back()->with('error', 'Không thể xóa trang nội dung cốt lõi của hệ thống.');
        }

        $page->delete();

        AuditLogger::log(
            action: 'PAGE_DELETED',
            targetType: 'CmsPage',
            targetId: $id,
            description: "Quản trị viên đã xóa trang nội dung tùy chỉnh: {$page->title}"
        );

        return redirect()->route('admin.pages.index')->with('success', 'Đã xóa trang nội dung thành công.');
    }

    /**
     * Bật/tắt trạng thái xuất bản nhanh.
     */
    public function togglePublish(Request $request, int $id): JsonResponse
    {
        $page = CmsPage::findOrFail($id);
        $page->is_published = ! $page->is_published;
        $page->save();

        return response()->json([
            'success' => true,
            'is_published' => $page->is_published,
            'message' => $page->is_published ? 'Đã xuất bản trang lên website.' : 'Đã ẩn trang khỏi website.',
        ]);
    }
}
