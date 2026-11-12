<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DocumentationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDocsController extends Controller
{
    public function __construct(
        protected DocumentationService $docsService
    ) {}

    /**
     * Hiển thị Trang chủ Trung tâm Quy trình Vận hành Quản trị (Admin Knowledge Center).
     */
    public function index(): View
    {
        $categories = $this->docsService->getCategories('admin');

        return view('docs.admin.index', [
            'space' => 'admin',
            'pageTitle' => 'Admin Knowledge Base — Sổ tay Quy trình Vận hành Tiêu chuẩn (SOP) Mây Hẹn',
            'metaDescription' => 'Tài liệu bàn giao, quy trình SOP vận hành hệ thống, ma trận RBAC, hướng dẫn đối soát tài chính và xử lý sự cố khẩn cấp.',
            'categories' => $categories,
        ]);
    }

    /**
     * Hiển thị chi tiết một quy trình SOP Admin.
     */
    public function show(string $category, string $slug): View
    {
        $categories = $this->docsService->getCategories('admin');
        $article = $this->docsService->getArticle('admin', $category, $slug);

        if ($article === null) {
            abort(404, 'Không tìm thấy quy trình vận hành này.');
        }

        return view('docs.admin.detail', [
            'space' => 'admin',
            'pageTitle' => ($article['title'] ?? 'Quy trình SOP').' — Admin Knowledge Base',
            'metaDescription' => $article['summary'] ?? '',
            'categories' => $categories,
            'currentCategory' => $category,
            'article' => $article,
        ]);
    }

    /**
     * API Tìm kiếm Admin Docs nội bộ.
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = (string) $request->query('q', '');
        $results = $this->docsService->search('admin', $keyword);

        return response()->json([
            'success' => true,
            'count' => count($results),
            'data' => $results,
        ]);
    }
}
