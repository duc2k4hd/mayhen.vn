<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Rules\NoProfanity;
use App\Services\WeddingAiCopywritingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiCopywriterController extends Controller
{
    public function __construct(
        protected WeddingAiCopywritingService $aiService
    ) {}

    /**
     * Gợi ý Lời Mời Cưới chuẩn phong cách Quiet Luxury.
     */
    public function invitations(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'groom_name' => ['nullable', 'string', 'max:50', new NoProfanity],
            'bride_name' => ['nullable', 'string', 'max:50', new NoProfanity],
            'host_type' => ['nullable', 'string', 'in:parents_both,parents_groom,parents_bride,couple'],
            'tone' => ['nullable', 'string', 'in:formal,modern,intimate'],
            'additional_note' => ['nullable', 'string', 'max:255', new NoProfanity],
            'wedding_id' => ['nullable', 'integer'],
        ]);

        $userId = $request->user()?->id;
        $weddingId = ! empty($validated['wedding_id']) ? (int) $validated['wedding_id'] : null;

        $result = $this->aiService->suggestInvitations($validated, $userId, $weddingId);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Gợi ý Tâm Thư Tri Ân & Lời Cảm Ơn Quan Khách.
     */
    public function thankYou(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'groom_name' => ['nullable', 'string', 'max:50', new NoProfanity],
            'bride_name' => ['nullable', 'string', 'max:50', new NoProfanity],
            'audience' => ['nullable', 'string', 'in:all_guests,close_friends,parents_family'],
            'tone' => ['nullable', 'string', 'in:heartfelt,formal,brief'],
            'wedding_id' => ['nullable', 'integer'],
        ]);

        $userId = $request->user()?->id;
        $weddingId = ! empty($validated['wedding_id']) ? (int) $validated['wedding_id'] : null;

        $result = $this->aiService->suggestThankYouNotes($validated, $userId, $weddingId);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Gợi ý Lời Giới Thiệu & Dẫn Chuyện Mở Đầu Thiệp Cưới.
     */
    public function introduction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'groom_name' => ['nullable', 'string', 'max:50', new NoProfanity],
            'bride_name' => ['nullable', 'string', 'max:50', new NoProfanity],
            'milestones' => ['nullable', 'string', 'max:255', new NoProfanity],
            'style' => ['nullable', 'string', 'in:editorial,poetic,modern'],
            'wedding_id' => ['nullable', 'integer'],
        ]);

        $userId = $request->user()?->id;
        $weddingId = ! empty($validated['wedding_id']) ? (int) $validated['wedding_id'] : null;

        $result = $this->aiService->suggestIntroductions($validated, $userId, $weddingId);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Soát lỗi chính tả & Tinh chỉnh câu từ Quiet Luxury.
     */
    public function polish(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:2000'],
            'style' => ['nullable', 'string', 'in:quiet_luxury,romantic,traditional'],
            'wedding_id' => ['nullable', 'integer'],
        ]);

        $userId = $request->user()?->id;
        $weddingId = ! empty($validated['wedding_id']) ? (int) $validated['wedding_id'] : null;

        $result = $this->aiService->polishAndCheckGrammar(
            rawText: $validated['text'],
            style: $validated['style'] ?? 'quiet_luxury',
            userId: $userId,
            weddingId: $weddingId
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Dịch nội dung thiệp cưới sang Tiếng Anh, Trung, Hàn (bảo toàn tên riêng & địa chỉ).
     */
    public function translate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:2000'],
            'target_lang' => ['nullable', 'string', 'in:en,zh,ko,vi'],
            'preserve_entities' => ['nullable', 'array'],
            'preserve_entities.*' => ['string', 'max:100'],
            'wedding_id' => ['nullable', 'integer'],
        ]);

        $userId = $request->user()?->id;
        $weddingId = ! empty($validated['wedding_id']) ? (int) $validated['wedding_id'] : null;

        $result = $this->aiService->translateContent(
            text: $validated['text'],
            targetLang: $validated['target_lang'] ?? 'en',
            preserveEntities: $validated['preserve_entities'] ?? [],
            userId: $userId,
            weddingId: $weddingId
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }
}
