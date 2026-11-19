<?php

declare(strict_types=1);

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\AskChatbotRequest;
use App\Http\Requests\SubmitChatbotTrainingRequest;
use App\Models\AiChatbotLog;
use App\Services\Chatbot\ProjectChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AiChatbotController extends Controller
{
    public function __construct(
        protected ProjectChatbotService $chatbotService
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

    public function train(SubmitChatbotTrainingRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->chatbotService->submitCustomerTraining(
            rawQuestion: (string) $validated['question'],
            rawSuggestedAnswer: (string) $validated['suggested_answer'],
            category: (string) ($validated['category'] ?? 'general'),
            userId: $request->user()?->id,
            sessionId: $request->session()->getId()
        );

        return response()->json([
            'ok' => $result['accepted'],
            'message' => $result['message'],
            'entry_id' => $result['entry_id'],
        ], $result['accepted'] ? 201 : 422);
    }

    public function feedback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'log_id' => ['required', 'integer', 'exists:ai_chatbot_logs,id'],
            'feedback' => ['required', 'string', Rule::in(['helpful', 'unhelpful'])],
        ]);

        $sessionHash = hash('sha256', $request->session()->getId() !== '' ? $request->session()->getId() : 'guest');

        // Chỉ cho phép cập nhật log thuộc chính phiên hội thoại của người dùng hiện tại (chống IDOR)
        $updated = AiChatbotLog::query()
            ->where('id', (int) $validated['log_id'])
            ->where('session_hash', $sessionHash)
            ->update(['feedback' => $validated['feedback']]);

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
                : 'Cảm ơn góp ý! Bạn có thể bấm "Góp ý dạy AI" để bổ sung câu trả lời chuẩn xác hơn.',
        ]);
    }
}
