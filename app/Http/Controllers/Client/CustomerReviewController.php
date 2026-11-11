<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Review;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerReviewController extends Controller
{
    /**
     * Khách hàng gửi đánh giá & cảm nhận sau khi tạo hoặc sử dụng thiệp cưới (Mục 7.14 DOCX).
     */
    public function store(Request $request, int $id): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);
        $wedding = $invitation->wedding;

        if ($wedding->user_id !== Auth::id()) {
            abort(404);
        }

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:10', 'max:5000'],
            'photo_url' => ['nullable', 'string', 'max:500'],
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá cho Mây Hẹn.',
            'rating.between' => 'Số sao đánh giá phải từ 1 đến 5 sao.',
            'content.required' => 'Vui lòng chia sẻ cảm nhận trải nghiệm thực tế của bạn.',
            'content.min' => 'Nội dung cảm nhận phải có tối thiểu 10 ký tự.',
        ]);

        $coupleName = "{$wedding->groom_name} & {$wedding->bride_name}";

        $review = Review::create([
            'user_id' => Auth::id(),
            'wedding_id' => $wedding->id,
            'invitation_id' => $invitation->id,
            'template_id' => $invitation->template_id,
            'couple_name' => $coupleName,
            'wedding_date' => $wedding->wedding_date,
            'rating' => $validated['rating'],
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'photo_url' => $validated['photo_url'] ?? $wedding->cover_photo_url,
            'status' => 'pending', // Bắt buộc kiểm duyệt trước khi xuất bản theo Mục 7.14 DOCX
            'is_featured' => false,
            'verified_purchase' => true,
        ]);

        AuditLogger::log(
            'CUSTOMER_REVIEW_SUBMITTED',
            'Review',
            $review->id,
            "Cặp đôi {$coupleName} đã gửi đánh giá {$validated['rating']} sao (Chờ kiểm duyệt)",
            [
                'review_id' => $review->id,
                'user_id' => Auth::id(),
                'wedding_id' => $wedding->id,
                'rating' => $validated['rating'],
            ]
        );

        return back()->with('success', 'Trân trọng cảm ơn cảm nhận quý báu của hai bạn! Đội ngũ Atelier Mây Hẹn sẽ kiểm duyệt và xuất bản đánh giá sớm nhất.');
    }
}

