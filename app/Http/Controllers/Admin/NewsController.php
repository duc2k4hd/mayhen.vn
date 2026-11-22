<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsPost;
use App\Services\BlogService;
use App\Services\NewsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class NewsController extends Controller
{
    /**
     * Hiển thị danh sách Tin Tức & Cập Nhật Dự Án trong Admin (/admin/news).
     */
    public function index(Request $request): View
    {
        $query = NewsPost::with(['user']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_label')) {
            $query->where('category_label', $request->input('category_label'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $posts = $query->latest('created_at')->paginate(15)->withQueryString();
        $categoryLabels = NewsPost::CATEGORY_LABELS;

        $stats = [
            'total' => NewsPost::count(),
            'published' => NewsPost::where('status', 'published')->count(),
            'draft' => NewsPost::where('status', 'draft')->count(),
            'broadcasted' => NewsPost::whereNotNull('email_broadcasted_at')->count(),
            'total_views' => (int) NewsPost::sum('view_count'),
        ];

        return view('admin.pages.news.index', compact('posts', 'categoryLabels', 'stats'));
    }

    /**
     * Form tạo bài tin tức mới (/admin/news/create).
     */
    public function create(): View
    {
        $categoryLabels = NewsPost::CATEGORY_LABELS;

        return view('admin.pages.news.create', compact('categoryLabels'));
    }

    /**
     * Lưu bài tin tức mới & tự động đưa email tóm tắt vào hàng đợi gửi toàn bộ user nếu được bật.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:news_posts,slug'],
            'category_label' => ['nullable', 'string', 'max:120'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'author_name' => ['nullable', 'string', 'max:150'],
            'tags' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:draft,published,scheduled,archived'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'send_email_on_publish' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        if (NewsPost::where('slug', $slug)->exists()) {
            $slug .= '-'.time();
        }

        $coverUrl = null;
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('news/covers', 'public');
            $coverUrl = '/storage/'.$path;
        }

        $tagsArray = [];
        if (! empty($validated['tags'])) {
            $tagsArray = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
        }

        $readingTime = BlogService::calculateReadingTime($validated['content']);
        $publishedAt = $validated['published_at'] ?? ($validated['status'] === 'published' ? now() : null);
        $sendEmailOnPublish = $request->boolean('send_email_on_publish');

        $post = NewsPost::create([
            'user_id' => Auth::id(),
            'title' => $validated['title'],
            'slug' => $slug,
            'category_label' => ($validated['category_label'] ?? null) ?: 'Cập Nhật Hệ Thống',
            'summary' => $validated['summary'] ?? Str::limit(strip_tags($validated['content']), 220),
            'content' => $validated['content'],
            'cover_image_url' => $coverUrl,
            'author_name' => ($validated['author_name'] ?? null) ?: (Auth::user()?->name ?? 'Ban Phát Triển Mây Hẹn'),
            'tags' => $tagsArray,
            'status' => $validated['status'],
            'published_at' => $publishedAt,
            'reading_time_minutes' => $readingTime,
            'is_featured' => $request->boolean('is_featured'),
            'send_email_on_publish' => $sendEmailOnPublish,
            'meta_title' => ($validated['meta_title'] ?? null) ?: $validated['title'],
            'meta_description' => ($validated['meta_description'] ?? null) ?: ($validated['summary'] ?? Str::limit(strip_tags($validated['content']), 160)),
            'meta_keywords' => $validated['meta_keywords'] ?? null,
        ]);

        $queuedCount = 0;
        if ($post->status === 'published' && $sendEmailOnPublish) {
            $queuedCount = NewsService::queueSummaryBroadcastToAllUsers($post);
        }

        $message = 'Đã tạo bản tin thành công.';
        if ($queuedCount > 0) {
            $message .= " Đã đưa {$queuedCount} email tóm tắt vào hàng đợi gửi tới thành viên Mây Hẹn.";
        }

        return redirect()->route('admin.news.index')->with('success', $message);
    }

    /**
     * Form chỉnh sửa bài tin tức (/admin/news/{id}/edit).
     */
    public function edit(int $id): View
    {
        $post = NewsPost::findOrFail($id);
        $categoryLabels = NewsPost::CATEGORY_LABELS;

        return view('admin.pages.news.edit', compact('post', 'categoryLabels'));
    }

    /**
     * Cập nhật bài tin tức & tự động đưa email tóm tắt vào hàng đợi nếu bài được xuất bản và chưa từng gửi (hoặc bật gửi).
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $post = NewsPost::findOrFail($id);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:news_posts,slug,'.$post->id],
            'category_label' => ['nullable', 'string', 'max:120'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],
            'author_name' => ['nullable', 'string', 'max:150'],
            'tags' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'in:draft,published,scheduled,archived'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'send_email_on_publish' => ['nullable', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['title']);
        if (NewsPost::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
            $slug .= '-'.time();
        }

        $coverUrl = $post->cover_image_url;
        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')->store('news/covers', 'public');
            $coverUrl = '/storage/'.$path;
        }

        $tagsArray = [];
        if (! empty($validated['tags'])) {
            $tagsArray = array_values(array_filter(array_map('trim', explode(',', $validated['tags']))));
        }

        $readingTime = BlogService::calculateReadingTime($validated['content']);
        $publishedAt = $validated['published_at'] ?? ($validated['status'] === 'published' ? ($post->published_at ?? now()) : $post->published_at);
        $sendEmailOnPublish = $request->boolean('send_email_on_publish');

        $post->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'category_label' => ($validated['category_label'] ?? null) ?: 'Cập Nhật Hệ Thống',
            'summary' => $validated['summary'] ?? Str::limit(strip_tags($validated['content']), 220),
            'content' => $validated['content'],
            'cover_image_url' => $coverUrl,
            'author_name' => ($validated['author_name'] ?? null) ?: $post->author_name,
            'tags' => $tagsArray,
            'status' => $validated['status'],
            'published_at' => $publishedAt,
            'reading_time_minutes' => $readingTime,
            'is_featured' => $request->boolean('is_featured'),
            'send_email_on_publish' => $sendEmailOnPublish,
            'meta_title' => ($validated['meta_title'] ?? null) ?: $validated['title'],
            'meta_description' => ($validated['meta_description'] ?? null) ?: ($validated['summary'] ?? Str::limit(strip_tags($validated['content']), 160)),
            'meta_keywords' => $validated['meta_keywords'] ?? null,
        ]);

        $queuedCount = 0;
        if ($post->status === 'published' && $sendEmailOnPublish && $post->email_broadcasted_at === null) {
            $queuedCount = NewsService::queueSummaryBroadcastToAllUsers($post);
        }

        $message = 'Đã cập nhật bản tin thành công.';
        if ($queuedCount > 0) {
            $message .= " Đã đưa {$queuedCount} email tóm tắt vào hàng đợi gửi tới thành viên Mây Hẹn.";
        }

        return redirect()->route('admin.news.index')->with('success', $message);
    }

    /**
     * Xóa bài tin tức.
     */
    public function destroy(int $id): RedirectResponse
    {
        $post = NewsPost::findOrFail($id);
        $post->delete();

        return redirect()->route('admin.news.index')->with('success', 'Đã xóa bản tin.');
    }

    /**
     * Bật/tắt nhanh trạng thái xuất bản.
     */
    public function togglePublish(int $id): RedirectResponse
    {
        $post = NewsPost::findOrFail($id);
        $newStatus = $post->status === 'published' ? 'draft' : 'published';

        $post->update([
            'status' => $newStatus,
            'published_at' => $newStatus === 'published' ? ($post->published_at ?? now()) : $post->published_at,
        ]);

        $queuedCount = 0;
        if ($newStatus === 'published' && $post->send_email_on_publish && $post->email_broadcasted_at === null) {
            $queuedCount = NewsService::queueSummaryBroadcastToAllUsers($post);
        }

        $message = 'Đã chuyển trạng thái bản tin sang '.($newStatus === 'published' ? 'Xuất bản' : 'Bản nháp').'.';
        if ($queuedCount > 0) {
            $message .= " Đã đưa {$queuedCount} email tóm tắt vào hàng đợi gửi thành viên.";
        }

        return back()->with('success', $message);
    }

    /**
     * Phát lệnh thủ công đưa email tóm tắt bản tin vào hàng đợi gửi tới toàn bộ User đã đăng ký.
     */
    public function broadcastEmail(int $id): RedirectResponse
    {
        $post = NewsPost::findOrFail($id);

        if ($post->status !== 'published') {
            return back()->with('error', 'Chỉ có thể phát hành email thông báo cho bản tin đã ở trạng thái Xuất bản (Live).');
        }

        $queuedCount = NewsService::queueSummaryBroadcastToAllUsers($post);

        return back()->with('success', "Đã đưa {$queuedCount} email tóm tắt bản tin \"{$post->title}\" vào hàng đợi để gửi tới toàn bộ thành viên Mây Hẹn.");
    }
}
