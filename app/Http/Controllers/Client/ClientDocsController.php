<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\DocumentationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ClientDocsController extends Controller
{
    public function __construct(
        protected DocumentationService $docsService
    ) {}

    /**
     * Hiển thị Trang chủ Trung tâm Hướng dẫn Khách hàng.
     * Tối ưu hóa phản hồi với ETag và Cache-Control chuẩn CDN, chịu tải hàng triệu người dùng.
     */
    public function index(Request $request): Response|View
    {
        $categories = $this->docsService->getCategories('client');

        $etag = '"mayhen_docs_idx_'.md5(serialize(array_keys($categories)).count($categories)).'"';

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'public, max-age=3600, stale-while-revalidate=86400');
        }

        return response()
            ->view('docs.client.index', [
                'space' => 'client',
                'pageTitle' => 'Trung tâm Hướng dẫn Sử dụng thiệp cưới Mây Hẹn | Dành cho Cô dâu, Chú rể & Khách mời',
                'metaDescription' => 'Hướng dẫn chi tiết từng bước tạo thiệp cưới online, chọn mẫu, chỉnh sửa ảnh, quản lý khách mời RSVP và thanh toán VietQR trên Mây Hẹn.',
                'categories' => $categories,
            ])
            ->header('Cache-Control', 'public, max-age=3600, stale-while-revalidate=86400')
            ->header('ETag', $etag);
    }

    /**
     * Hiển thị chi tiết một bài hướng dẫn Client.
     * Cung cấp ETag + Last-Modified để trình duyệt và CDN trả về 304 Not Modified tức thì (0ms CPU).
     */
    public function show(Request $request, string $category, string $slug): Response|View
    {
        $categories = $this->docsService->getCategories('client');
        $article = $this->docsService->getArticle('client', $category, $slug);

        if ($article === null) {
            abort(404, 'Không tìm thấy bài viết hướng dẫn này.');
        }

        $articleId = $article['id'] ?? $slug;
        $updatedAt = $article['updated_at'] ?? '2026-01-01';
        $etag = '"mayhen_docs_art_'.md5($articleId.'_'.$updatedAt).'"';
        $lastModified = gmdate('D, d M Y H:i:s T', strtotime($updatedAt));

        if ($request->header('If-None-Match') === $etag) {
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Last-Modified', $lastModified)
                ->header('Cache-Control', 'public, max-age=3600, stale-while-revalidate=86400');
        }

        return response()
            ->view('docs.client.detail', [
                'space' => 'client',
                'pageTitle' => ($article['title'] ?? 'Hướng dẫn').' | Mây Hẹn Docs',
                'metaDescription' => $article['summary'] ?? '',
                'categories' => $categories,
                'currentCategory' => $category,
                'article' => $article,
            ])
            ->header('Cache-Control', 'public, max-age=3600, stale-while-revalidate=86400')
            ->header('ETag', $etag)
            ->header('Last-Modified', $lastModified);
    }

    /**
     * API Tìm kiếm Client Docs (trả về JSON).
     */
    public function search(Request $request): JsonResponse
    {
        $keyword = (string) $request->query('q', '');
        $results = $this->docsService->search('client', $keyword);

        return response()
            ->json([
                'success' => true,
                'count' => count($results),
                'data' => $results,
            ])
            ->header('Cache-Control', 'public, max-age=300');
    }
}
