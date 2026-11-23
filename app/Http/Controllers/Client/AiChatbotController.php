<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\AskChatbotRequest;
use App\Models\AiChatbotLog;
use App\Services\Chatbot\ProjectChatbotService;
use App\Services\Intelligence\CustomerMemoryService;
use App\Services\Intelligence\SecurityPolicyGate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class AiChatbotController extends Controller
{
    public function __construct(
        protected ProjectChatbotService $chatbotService,
        protected SecurityPolicyGate $policyGate,
        protected CustomerMemoryService $customerMemory
    ) {}

    public function ask(AskChatbotRequest $request): JsonResponse
    {
        $result = $this->chatbotService->ask(
            rawMessage: (string) $request->validated('message'),
            userId: $request->user()?->id,
            sessionId: $request->session()->getId(),
            ipAddress: $request->ip()
        );

        return response()->json([
            'ok' => true,
            'data' => $result,
        ]);
    }

    /**
     * Ghi nhận tín hiệu đánh giá chất lượng Hữu ích / Chưa đúng (chống IDOR & chống thao túng spam).
     * Feedback chỉ là tín hiệu phân tích cho Admin & Learning Agent, TUYỆT ĐỐI không tự sửa tri thức.
     */
    public function feedback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'log_id' => ['required', 'integer', 'exists:ai_chatbot_logs,id'],
            'feedback' => ['required', 'string', Rule::in(['helpful', 'unhelpful'])],
        ]);

        $sessionId = $request->session()->getId();
        $sessionHash = hash('sha256', $sessionId !== '' ? $sessionId : 'guest');
        $ipHash = $request->ip() ? hash('sha256', (string) $request->ip()) : null;

        $isGenuine = $this->policyGate->isFeedbackGenuine($sessionHash, $ipHash, (string) $validated['feedback']);

        $updateData = ['feedback' => $validated['feedback']];
        if (Schema::hasColumn('ai_chatbot_logs', 'feedback_valid')) {
            $updateData['feedback_valid'] = $isGenuine;
        }

        // Chỉ cho phép cập nhật log thuộc chính phiên hội thoại của người dùng hiện tại (chống IDOR)
        $updated = AiChatbotLog::query()
            ->where('id', (int) $validated['log_id'])
            ->where('session_hash', $sessionHash)
            ->update($updateData);

        if ($updated === 0) {
            return response()->json([
                'ok' => false,
                'message' => 'Không tìm thấy phiên phản hồi hợp lệ.',
            ], 403);
        }

        return response()->json([
            'ok' => true,
            'message' => $validated['feedback'] === 'helpful'
                ? 'Cảm ơn đánh giá của bạn!'
                : 'Cảm ơn phản hồi của bạn! Đội ngũ Mây Hẹn đã ghi nhận để cải thiện chất lượng tư vấn.',
        ]);
    }

    /**
     * Cho phép khách hàng tự xóa bộ nhớ ngữ cảnh của chính mình (Quyền riêng tư / DSAR).
     */
    public function forgetMemory(Request $request): JsonResponse
    {
        $sessionId = $request->session()->getId();
        $sessionHash = hash('sha256', $sessionId !== '' ? $sessionId : 'guest');

        $this->customerMemory->forgetScope($request->user()?->id, $sessionHash);
        Cache::put('mayhen_chatbot_reset_at:'.$sessionHash, now()->toDateTimeString(), now()->addDays(7));

        return response()->json([
            'ok' => true,
            'message' => 'Đã xóa ngữ cảnh phiên trò chuyện của bạn.',
        ]);
    }
}
