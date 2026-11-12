<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\DocumentationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientDocsController extends Controller
{
    public function __construct(
        protected DocumentationService $docsService
    ) {}

    /**
     * Hiển thị Trang chủ Trung tâm Hướng dẫn Khách hàng.
     */
    public function index(): View
    {
        $categories = $this->docsService->getCategories('client');

        return view('docs.client.index', [
            'space' => 'client',
            'pageTitle' => 'Trung tâm Hướng dẫn Sử dụng Mây Hẹn — Dành cho Cô dâu, Chú rể & Khách mời',
            'metaDescription' => 'Hướng dẫn chi tiết từng bước tạo thiệp cưới online, chọn mẫu, chỉnh sửa ảnh, quản lý khách mời RSVP và thanh toán VietQR trên Mây Hẹn.',
            'categories' => $categories,
        ]);
    }

    /**
     * Hiển thị chi tiết một bài hướng dẫn Client.
     */
    public function show(string $category, string $slug): View
    {
        $categories = $this->docsService->getCategories('client');
        $article = $this->docsService->getArticle('client', $category, $slug);

        if ($article === null) {
            abort(404, 'Không tìm thấy bài viết hướng dẫn này.');
        }

        return view('docs.client.detail', [
            'space' => 'client',
            'pageTitle' => ($article['title'] ?? 'Hướng dẫn').' — Mây Hẹn Docs',
            'metaDescription' => $article['summary'] ?? '',
            'categories' => $categories,
            'currentCategory' => $category,
            'article' => $article,
        ]);
    }

    /**
     * API Tìm kiếm Client Docs (trả về JSON).
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = (string) $request->query('q', '');
        $results = $this->docsService->search('client', $keyword);

        return response()->json([
            'success' => true,
            'count' => count($results),
            'data' => $results,
        ]);
    }
}
