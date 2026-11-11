<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\BlogService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    /**
     * Màn hình Trang Chủ Cẩm Nang & Chuyên Đề Cưới (Editorial Magazine).
     */
    public function index(Request $request): View
    {
        $categorySlug = $request->string('category')->trim();
        $search = $request->string('q')->trim();

        $selectedCategory = null;
        if (! empty($categorySlug)) {
            $selectedCategory = BlogCategory::where('slug', $categorySlug)->first();
        }

        $posts = BlogService::getPublishedPosts(
            perPage: 9,
            categorySlug: $categorySlug ?: null,
            search: $search ?: null
        );

        $categories = BlogCategory::active()->withCount('publishedPosts')->get();
        $featuredPosts = empty($categorySlug) && empty($search) ? BlogService::getFeaturedPosts(3) : collect();

        return view('client.pages.blog.index', compact(
            'posts',
            'categories',
            'selectedCategory',
            'featuredPosts',
            'search'
        ));
    }

    /**
     * Xem Chi Tiết Bài Viết Cẩm Nang.
     */
    public function show(string $slug): View
    {
        $post = BlogPost::with(['category', 'user'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Nếu bài viết chưa xuất bản thì chỉ Admin mới được xem trước
        if (! $post->isPublished()) {
            if (! auth()->check() || ! auth()->user()->isAdmin()) {
                abort(404);
            }
        }

        // Tăng view count an toàn
        BlogService::recordView($post);

        // Bóc tách mục lục (Table of Contents) và inject id neo cuộn
        $tocResult = BlogService::extractTableOfContentsAndInjectIds($post->content);
        $toc = $tocResult['toc'];
        $processedContent = $tocResult['content'];

        // Lấy bài viết liên quan
        $relatedPosts = BlogService::getRelatedPosts($post, 3);

        return view('client.pages.blog.show', compact(
            'post',
            'toc',
            'processedContent',
            'relatedPosts'
        ));
    }
}
