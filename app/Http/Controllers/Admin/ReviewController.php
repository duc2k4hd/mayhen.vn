<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * Bảng điều khiển kiểm duyệt đánh giá & cảm nhận khách hàng (Mục 7.14 DOCX).
     */
    public function index(Request $request): View
    {
        $query = Review::with(['user', 'wedding', 'template', 'moderator'])
            ->latest('id');

        // Lọc theo trạng thái kiểm duyệt
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo số sao đánh giá
        if ($request->filled('rating')) {
            $query->where('rating', (int) $request->rating);
        }

        // Lọc ghim trang chủ
        if ($request->has('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }

        // Tìm kiếm theo tên cặp đôi hoặc nội dung
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('couple_name', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $reviews = $query->paginate(20)->withQueryString();

        // KPI metrics
        $kpis = [
            'total' => Review::count(),
            'pending' => Review::where('status', 'pending')->count(),
            'approved' => Review::where('status', 'approved')->count(),
            'featured' => Review::where('status', 'approved')->where('is_featured', true)->count(),
            'avg_rating' => round((float) Review::where('status', 'approved')->avg('rating') ?: 5.0, 1),
        ];

        return view('admin.pages.reviews.index', compact('reviews', 'kpis'));
    }

    /**
     * Phê duyệt hiển thị cảm nhận của khách hàng (1-Click Approve).
     */
    public function approve(Request $request, Review $review): RedirectResponse
    {
        $review->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->input('admin_notes', $review->admin_notes),
        ]);

        AuditLogger::log(
            'REVIEW_APPROVED',
            'Review',
            $review->id,
            "Quản trị viên đã duyệt xuất bản đánh giá của cặp đôi {$review->couple_name}",
            ['review_id' => $review->id, 'rating' => $review->rating]
        );

        return back()->with('success', "Đã phê duyệt và xuất bản đánh giá của cặp đôi {$review->couple_name}.");
    }

    /**
     * Từ chối hoặc ẩn cảm nhận (Reject / Hide).
     */
    public function reject(Request $request, Review $review): RedirectResponse
    {
        $review->update([
            'status' => 'rejected',
            'is_featured' => false,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->input('admin_notes', 'Đã ẩn bởi quản trị viên.'),
        ]);

        AuditLogger::log(
            'REVIEW_REJECTED',
            'Review',
            $review->id,
            "Quản trị viên đã ẩn/từ chối đánh giá #{$review->id} của {$review->couple_name}",
            ['review_id' => $review->id]
        );

        return back()->with('warning', "Đã chuyển đánh giá của cặp đôi {$review->couple_name} sang trạng thái Từ chối / Ẩn.");
    }

    /**
     * Bật hoặc tắt ghim nổi bật lên Trang Chủ (Toggle Featured).
     */
    public function toggleFeatured(Request $request, Review $review): RedirectResponse
    {
        if ($review->status !== 'approved') {
            return back()->with('error', 'Chỉ những đánh giá đã được phê duyệt mới có thể ghim lên Trang Chủ.');
        }

        $newFeatured = ! $review->is_featured;
        $review->update(['is_featured' => $newFeatured]);

        AuditLogger::log(
            'REVIEW_FEATURED_TOGGLED',
            'Review',
            $review->id,
            ($newFeatured ? 'Ghim' : 'Bỏ ghim')." đánh giá #{$review->id} trên Trang Chủ",
            ['review_id' => $review->id, 'is_featured' => $newFeatured]
        );

        return back()->with('success', $newFeatured
            ? "Đã ghim đánh giá của {$review->couple_name} lên Trang Chủ."
            : "Đã gỡ ghim đánh giá của {$review->couple_name} khỏi Trang Chủ."
        );
    }

    /**
     * Xóa vĩnh viễn đánh giá rác hoặc vi phạm quy chuẩn.
     */
    public function destroy(Review $review): RedirectResponse
    {
        $couple = $review->couple_name;
        $id = $review->id;
        $review->delete();

        AuditLogger::log(
            'REVIEW_DELETED',
            'Review',
            $id,
            "Quản trị viên đã xóa vĩnh viễn đánh giá #{$id} của {$couple}"
        );

        return back()->with('success', "Đã xóa đánh giá của {$couple} thành công.");
    }
}

