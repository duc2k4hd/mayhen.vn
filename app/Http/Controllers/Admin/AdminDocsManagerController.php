<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DocumentationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminDocsManagerController extends Controller
{
    public function __construct(
        protected DocumentationService $docsService
    ) {}

    /**
     * Danh sách toàn bộ tài liệu (Client Docs & Admin SOP).
     */
    public function index(Request $request): View
    {
        $space = $request->query('space', 'all');
        if (! in_array($space, ['all', 'client', 'admin'], true)) {
            $space = 'all';
        }

        $category = $request->query('category');
        $search = $request->query('q');

        // Lấy danh sách bài viết theo bộ lọc
        $articles = $this->docsService->getAllArticlesWithMetadata(
            $space === 'all' ? null : $space,
            $category,
            $search
        );

        // Thống kê KPIs toàn bộ hệ thống
        $allArticles = $this->docsService->getAllArticlesWithMetadata();
        $totalDocs = count($allArticles);
        $clientDocsCount = count(array_filter($allArticles, fn ($a) => ($a['space'] ?? '') === 'client'));
        $adminDocsCount = count(array_filter($allArticles, fn ($a) => ($a['space'] ?? '') === 'admin'));

        $categoryOptions = $this->docsService->getCategoryOptions();
        $categoryCount = count($categoryOptions);

        return view('admin.pages.docs_manager.index', [
            'articles' => $articles,
            'currentSpace' => $space,
            'currentCategory' => $category,
            'currentSearch' => $search,
            'totalDocs' => $totalDocs,
            'clientDocsCount' => $clientDocsCount,
            'adminDocsCount' => $adminDocsCount,
            'categoryCount' => $categoryCount,
            'categoryOptions' => $categoryOptions,
            'pageTitle' => 'Quản Lý Toàn Bộ Docs — Mây Hẹn Admin',
        ]);
    }

    /**
     * Màn hình tạo mới tài liệu.
     */
    public function create(Request $request): View
    {
        $selectedSpace = $request->query('space', 'client');
        if (! in_array($selectedSpace, ['client', 'admin'], true)) {
            $selectedSpace = 'client';
        }

        $categoryOptions = $this->docsService->getCategoryOptions();

        return view('admin.pages.docs_manager.create', [
            'selectedSpace' => $selectedSpace,
            'categoryOptions' => $categoryOptions,
            'pageTitle' => 'Thêm Mới Tài Liệu Docs — Mây Hẹn Admin',
        ]);
    }

    /**
     * Xử lý lưu tài liệu mới.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'space' => ['required', 'string', 'in:client,admin'],
            'category_slug' => ['nullable', 'string', 'max:100'],
            'new_category_slug' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9\-]+$/i'],
            'category_name' => ['required', 'string', 'max:150'],
            'id' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\-_]+$/i'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'reader' => ['nullable', 'string', 'max:200'],
            'order' => ['nullable', 'integer', 'min:1', 'max:999'],
            'tags' => ['nullable', 'string', 'max:255'],
            'mapped_page' => ['nullable', 'string', 'max:255'],
            'mapped_route' => ['nullable', 'string', 'max:255'],
            'risk_level' => ['nullable', 'string', 'in:LOW,MEDIUM,HIGH,CRITICAL'],
            'required_role' => ['nullable', 'string', 'in:all,admin,manager,finance,support,customer'],
            'content' => ['required', 'string'],
        ], [
            'space.required' => 'Vui lòng chọn không gian tài liệu (Khách hàng hoặc Vận hành Admin).',
            'id.required' => 'Mã tài liệu (ID) không được để trống.',
            'id.regex' => 'Mã tài liệu chỉ được chứa chữ cái, chữ số, gạch nối (-) hoặc gạch dưới (_).',
            'category_name.required' => 'Tên chuyên mục hiển thị là bắt buộc.',
            'title.required' => 'Tiêu đề tài liệu là bắt buộc.',
            'content.required' => 'Nội dung chi tiết Markdown không được để trống.',
        ]);

        $catSlug = ! empty($validated['new_category_slug'])
            ? trim($validated['new_category_slug'])
            : trim((string) ($validated['category_slug'] ?? ''));

        if (empty($catSlug)) {
            $catSlug = Str::slug($validated['category_name']);
        }

        $validated['category_slug'] = $catSlug;

        $created = $this->docsService->createArticle($validated);

        if (! $created) {
            return back()->withInput()->with('error', 'Không thể tạo tài liệu. Vui lòng kiểm tra lại mã ID hoặc quyền ghi tệp.');
        }

        return redirect()
            ->route('admin.docs_manager.index', ['space' => $validated['space']])
            ->with('success', "Đã tạo tài liệu \"{$validated['title']}\" thành công!");
    }

    /**
     * Màn hình chỉnh sửa tài liệu.
     */
    public function edit(string $space, string $category, string $slug): View
    {
        $space = in_array($space, ['client', 'admin'], true) ? $space : 'client';
        $article = $this->docsService->getArticleRaw($space, $category, $slug);

        if ($article === null) {
            abort(404, 'Không tìm thấy tài liệu này trong hệ thống.');
        }

        $categoryOptions = $this->docsService->getCategoryOptions();

        return view('admin.pages.docs_manager.edit', [
            'space' => $space,
            'category' => $category,
            'slug' => $slug,
            'article' => $article,
            'categoryOptions' => $categoryOptions,
            'pageTitle' => 'Chỉnh Sửa Tài Liệu: '.($article['title'] ?? $slug),
        ]);
    }

    /**
     * Xử lý cập nhật tài liệu.
     */
    public function update(Request $request, string $space, string $category, string $slug): RedirectResponse
    {
        $validated = $request->validate([
            'space' => ['required', 'string', 'in:client,admin'],
            'category_slug' => ['nullable', 'string', 'max:100'],
            'new_category_slug' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9\-]+$/i'],
            'category_name' => ['required', 'string', 'max:150'],
            'id' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\-_]+$/i'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:500'],
            'reader' => ['nullable', 'string', 'max:200'],
            'order' => ['nullable', 'integer', 'min:1', 'max:999'],
            'tags' => ['nullable', 'string', 'max:255'],
            'mapped_page' => ['nullable', 'string', 'max:255'],
            'mapped_route' => ['nullable', 'string', 'max:255'],
            'risk_level' => ['nullable', 'string', 'in:LOW,MEDIUM,HIGH,CRITICAL'],
            'required_role' => ['nullable', 'string', 'in:all,admin,manager,finance,support,customer'],
            'content' => ['required', 'string'],
        ], [
            'space.required' => 'Vui lòng chọn không gian tài liệu.',
            'id.required' => 'Mã tài liệu (ID) không được để trống.',
            'category_name.required' => 'Tên chuyên mục hiển thị là bắt buộc.',
            'title.required' => 'Tiêu đề tài liệu là bắt buộc.',
            'content.required' => 'Nội dung chi tiết Markdown không được để trống.',
        ]);

        $catSlug = ! empty($validated['new_category_slug'])
            ? trim($validated['new_category_slug'])
            : trim((string) ($validated['category_slug'] ?? ''));

        if (empty($catSlug)) {
            $catSlug = Str::slug($validated['category_name']);
        }

        $validated['category_slug'] = $catSlug;

        $updated = $this->docsService->updateArticle($space, $category, $slug, $validated);

        if (! $updated) {
            return back()->withInput()->with('error', 'Không thể cập nhật tài liệu. Vui lòng kiểm tra lại quyền ghi tệp.');
        }

        return redirect()
            ->route('admin.docs_manager.index', ['space' => $validated['space']])
            ->with('success', "Đã cập nhật tài liệu \"{$validated['title']}\" thành công!");
    }

    /**
     * Xóa tài liệu.
     */
    public function destroy(string $space, string $category, string $slug): RedirectResponse
    {
        $deleted = $this->docsService->deleteArticle($space, $category, $slug);

        if (! $deleted) {
            return back()->with('error', 'Không tìm thấy hoặc không thể xóa tài liệu này.');
        }

        return redirect()
            ->route('admin.docs_manager.index', ['space' => $space])
            ->with('success', "Đã xóa tài liệu [{$slug}] thành công!");
    }

    /**
     * API Preview Render Markdown theo thời gian thực chuẩn giao diện Docs Mây Hẹn.
     */
    public function preview(Request $request): JsonResponse
    {
        $content = (string) $request->input('content', '');
        $title = (string) $request->input('title', 'Tiêu đề tài liệu');
        $summary = (string) $request->input('summary', '');
        $categoryName = (string) $request->input('category_name', 'Chuyên mục');
        $space = (string) $request->input('space', 'client');

        $toc = [];
        $htmlBody = $this->docsService->renderMarkdownBody($content, $toc);

        return response()->json([
            'success' => true,
            'html_body' => $htmlBody,
            'toc' => $toc,
            'title' => $title,
            'summary' => $summary,
            'category_name' => $categoryName,
            'space' => $space,
            'updated_at' => date('d/m/Y'),
        ]);
    }
}
