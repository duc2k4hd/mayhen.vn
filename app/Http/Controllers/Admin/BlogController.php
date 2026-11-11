<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Services\BlogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    /**
     * Danh sách bài viết & bộ lọc.
     */
    public function index(Request $request): View
    {
        $query = BlogPost::with(['category', 'user'])->latest('id');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhere('author_name', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(15)->withQueryString();
        $categories = BlogCategory::orderBy('sort_order')->get();

        $stats = [
            'total' => BlogPost::count(),
            'published' => BlogPost::where('status', 'published')->count(),
            'draft' => BlogPost::where('status', 'draft')->count(),
            'total_views' => BlogPost::sum('view_count'),
        ];

        return view('admin.pages.blog.index', compact('posts', 'categories', 'stats'));
    }

    /**
     * Giao diện soạn thảo bài viết mới.
     */
    public function create(): View
    {
        $categories = BlogCategory::orderBy('sort_order')->get();

        return view('admin.pages.blog.create', compact('categories'));
    }

    /**
     * Lưu bài viết mới vào hệ thống.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:blog_posts,slug'],
            'category_id' => ['nullable', 'exists:blog_categories,id'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'author_name' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:draft,published,scheduled,archived'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string'],
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề bài viết.',
            'content.required' => 'Nội dung bài viết không được để trống.',
            'cover_image.max' => 'Ảnh bìa không được vượt quá 5MB.',
        ]);

        $coverUrl = null;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $fileName = 'blog_'.time().'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('blog_covers', $fileName, 'public');
            $coverUrl = '/storage/'.$path;
        }

        $tagsArray = null;
        if (! empty($validated['tags'])) {
            $tagsArray = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
        }

        $readingTime = BlogService::calculateReadingTime($validated['content']);

        $post = BlogPost::create([
            'user_id' => Auth::id(),
            'category_id' => $validated['category_id'] ?? null,
            'title' => $validated['title'],
            'slug' => ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']),
            'summary' => $validated['summary'] ?? null,
            'content' => $validated['content'],
            'cover_image_url' => $coverUrl ?? '/assets/client/images/home/hero-wedding-card.webp',
            'author_name' => $validated['author_name'] ?: 'Mây Hẹn Atelier',
            'tags' => $tagsArray,
            'status' => $validated['status'],
            'published_at' => $validated['status'] === 'published' ? ($validated['published_at'] ?? now()) : $validated['published_at'],
            'reading_time_minutes' => $readingTime,
            'is_featured' => $request->boolean('is_featured'),
            'meta_title' => $validated['meta_title'] ?? $validated['title'],
            'meta_description' => $validated['meta_description'] ?? ($validated['summary'] ?? null),
            'meta_keywords' => $validated['meta_keywords'] ?? null,
        ]);

        return redirect()->route('admin.blog.index')->with('success', 'Đã tạo bài viết cẩm nang thành công.');
    }

    /**
     * Giao diện chỉnh sửa bài viết.
     */
    public function edit(int $id): View
    {
        $post = BlogPost::findOrFail($id);
        $categories = BlogCategory::orderBy('sort_order')->get();

        return view('admin.pages.blog.edit', compact('post', 'categories'));
    }

    /**
     * Cập nhật bài viết.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $post = BlogPost::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:blog_posts,slug,'.$id],
            'category_id' => ['nullable', 'exists:blog_categories,id'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'author_name' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:draft,published,scheduled,archived'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $fileName = 'blog_'.time().'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('blog_covers', $fileName, 'public');
            $post->cover_image_url = '/storage/'.$path;
        }

        if (isset($validated['tags'])) {
            $post->tags = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
        }

        $post->title = $validated['title'];
        if (! empty($validated['slug'])) {
            $post->slug = Str::slug($validated['slug']);
        }
        $post->category_id = $validated['category_id'] ?? null;
        $post->summary = $validated['summary'] ?? null;
        $post->content = $validated['content'];
        $post->author_name = ! empty($validated['author_name']) ? $validated['author_name'] : ($post->author_name ?: 'Mây Hẹn Atelier');
        $post->status = $validated['status'] ?? $post->status;
        $post->published_at = $validated['status'] === 'published' && empty($post->published_at) ? now() : ($validated['published_at'] ?? $post->published_at);
        $post->reading_time_minutes = BlogService::calculateReadingTime($validated['content']);
        $post->is_featured = $request->boolean('is_featured');
        $post->meta_title = $validated['meta_title'] ?? $validated['title'];
        $post->meta_description = $validated['meta_description'] ?? ($validated['summary'] ?? null);
        $post->meta_keywords = $validated['meta_keywords'] ?? null;

        $post->save();

        return redirect()->route('admin.blog.index')->with('success', 'Đã cập nhật bài viết thành công.');
    }

    /**
     * Xóa bài viết.
     */
    public function destroy(int $id): RedirectResponse
    {
        $post = BlogPost::findOrFail($id);
        $post->delete();

        return redirect()->route('admin.blog.index')->with('success', 'Đã xóa bài viết thành công.');
    }

    /**
     * Bật/tắt trạng thái xuất bản nhanh.
     */
    public function togglePublish(Request $request, int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);
        $post->status = $post->status === 'published' ? 'draft' : 'published';
        if ($post->status === 'published' && empty($post->published_at)) {
            $post->published_at = now();
        }
        $post->save();

        return response()->json([
            'success' => true,
            'status' => $post->status,
            'message' => $post->status === 'published' ? 'Đã xuất bản bài viết lên trang chủ cẩm nang.' : 'Đã chuyển bài viết về bản nháp.',
        ]);
    }

    /**
     * Danh sách chuyên mục blog.
     */
    public function categories(): View
    {
        $categories = BlogCategory::withCount('posts')->orderBy('sort_order')->get();

        return view('admin.pages.blog.categories', compact('categories'));
    }

    /**
     * Thêm chuyên mục blog mới.
     */
    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', 'unique:blog_categories,slug'],
            'description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        BlogCategory::create([
            'name' => $validated['name'],
            'slug' => ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        return back()->with('success', 'Đã thêm chuyên mục mới thành công.');
    }
}
