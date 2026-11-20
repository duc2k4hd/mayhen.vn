<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAiKnowledgeRequest;
use App\Models\AiChatbotLog;
use App\Models\AiKnowledgeEntry;
use App\Models\AiKnowledgeVersion;
use App\Models\AiLearningRun;
use App\Models\InvitationTemplate;
use App\Services\AuditLogger;
use App\Services\Chatbot\ChatbotProviderClient;
use App\Services\Chatbot\ChatbotSecurityGuard;
use App\Services\DocumentationService;
use App\Services\Intelligence\AiEvaluationService;
use App\Services\Intelligence\AiGovernanceService;
use App\Services\Intelligence\CustomerMemoryService;
use App\Services\Intelligence\MultiAgentLearningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AiChatbotAdminController extends Controller
{
    public function __construct(
        protected ChatbotSecurityGuard $securityGuard,
        protected DocumentationService $documentationService,
        protected AiGovernanceService $governance,
        protected MultiAgentLearningService $learningService,
        protected AiEvaluationService $evaluationService,
        protected CustomerMemoryService $memoryService,
        protected ChatbotProviderClient $providerClient
    ) {}

    public function index(Request $request): View
    {
        $tab = (string) $request->query('tab', 'approved');
        // Giữ tương thích ngược tên tab cũ
        if ($tab === 'pending') {
            $tab = 'learning';
        } elseif ($tab === 'logs') {
            $tab = 'quality';
        }
        if (! in_array($tab, ['approved', 'learning', 'quality', 'governance'], true)) {
            $tab = 'approved';
        }

        $search = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', '');

        $approvedQuery = AiKnowledgeEntry::query()
            ->with(['versions' => fn ($q) => $q->limit(6)])
            ->where('status', AiKnowledgeEntry::STATUS_APPROVED)
            ->orderByDesc('priority')
            ->orderByDesc('updated_at');

        if ($search !== '') {
            $approvedQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('keywords', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        if ($category !== '' && array_key_exists($category, AiKnowledgeEntry::CATEGORIES)) {
            $approvedQuery->where('category', $category);
        }

        $approvedEntries = $approvedQuery->paginate(15, ['*'], 'approved_page')->withQueryString();

        $pendingEntries = AiKnowledgeEntry::query()
            ->where('status', AiKnowledgeEntry::STATUS_PENDING)
            ->latest('id')
            ->paginate(15, ['*'], 'pending_page')
            ->withQueryString();

        $logs = AiChatbotLog::query()
            ->latest('id')
            ->paginate(20, ['*'], 'logs_page')
            ->withQueryString();

        $totalChats = AiChatbotLog::query()->count();
        $blockedChats = AiChatbotLog::query()->where('response_mode', AiChatbotLog::MODE_BLOCKED_SECURITY)->count();
        $noContextChats = AiChatbotLog::query()->where('response_mode', AiChatbotLog::MODE_NO_CONTEXT_FALLBACK)->count();
        $reviewedChats = AiChatbotLog::query()->where('response_mode', AiChatbotLog::MODE_LLM_REVIEWED)->count();
        $reviewerRejectedChats = Schema::hasColumn('ai_chatbot_logs', 'review_status')
            ? AiChatbotLog::query()->where('review_status', 'rejected_fallback')->count()
            : 0;
        $validChats = max(0, $totalChats - $blockedChats);
        $resolvedRate = $validChats > 0 ? round((($validChats - $noContextChats) / $validChats) * 100, 1) : 0.0;
        $missingEvidenceRate = $validChats > 0 ? round(($noContextChats / $validChats) * 100, 1) : 0.0;

        $stats = [
            'approved_count' => AiKnowledgeEntry::query()->liveApproved()->count(),
            'outdated_count' => Schema::hasColumn('ai_knowledge_entries', 'is_outdated')
                ? AiKnowledgeEntry::query()->where('status', AiKnowledgeEntry::STATUS_APPROVED)->where('is_outdated', true)->count()
                : 0,
            'pending_count' => AiKnowledgeEntry::query()->where('status', AiKnowledgeEntry::STATUS_PENDING)->count(),
            'total_chats' => $totalChats,
            'blocked_security_count' => $blockedChats,
            'reviewed_count' => $reviewedChats,
            'reviewer_rejected_count' => $reviewerRejectedChats,
            'resolved_rate' => $resolvedRate,
            'missing_evidence_rate' => $missingEvidenceRate,
            'helpful_count' => AiChatbotLog::query()->where('feedback', 'helpful')->count(),
            'unhelpful_count' => AiChatbotLog::query()->where('feedback', 'unhelpful')->count(),
            'today_tokens_used' => $this->governance->getTodayTokensUsed(),
        ];

        $templates = InvitationTemplate::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['slug', 'name', 'category']);

        $clientDocOptions = [];
        foreach ($this->documentationService->getCategories('client') as $cat) {
            foreach ($cat['articles'] ?? [] as $article) {
                $rawDocUrl = (string) ($article['url'] ?? '');
                $docPath = str_starts_with($rawDocUrl, '/')
                    ? $rawDocUrl
                    : (string) (parse_url($rawDocUrl, PHP_URL_PATH) ?? '');
                if ($docPath !== '' && str_starts_with($docPath, '/docs/')) {
                    $clientDocOptions[] = [
                        'url' => $docPath,
                        'label' => ($cat['name'] ?? 'Docs').' — '.($article['title'] ?? ''),
                    ];
                }
            }
        }

        $policy = $this->governance->getPolicy();
        $aiRuntimeConfig = [
            'enabled' => (bool) config('services.ninerouter.enabled', false),
            'provider' => $this->providerClient->providerName(),
            'model' => $policy['draft_model'],
            'reviewer_model' => $policy['reviewer_model'],
            'base_url' => (string) config('services.ninerouter.base_url', ''),
            'has_token' => (string) config('services.ninerouter.api_key', '') !== '',
            'kill_switch' => $policy['kill_switch'],
            'last_provider_status' => AiChatbotLog::query()
                ->whereNotNull('provider_status')
                ->where('provider_status', '!=', 'not_called')
                ->latest('id')
                ->value('provider_status'),
        ];

        $customerInsights = $tab === 'learning' || $tab === 'quality'
            ? $this->learningService->getCustomerInsights(30)
            : [];

        $learningRuns = Schema::hasTable('ai_learning_runs') && in_array($tab, ['learning', 'quality'], true)
            ? AiLearningRun::query()->latest('id')->limit(12)->get()
            : collect();

        $latestEvalRun = Schema::hasTable('ai_learning_runs') && $tab === 'quality'
            ? AiLearningRun::query()->where('run_type', AiLearningRun::TYPE_REGRESSION_EVAL)->latest('id')->first()
            : null;

        $availableModels = $tab === 'governance'
            ? Cache::remember('mayhen_ninerouter_models_list_v1', 300, function (): array {
                $res = $this->providerClient->listModels();

                return $res['ok'] ? $res['models'] : [];
            })
            : [];

        $activeChunkedRun = $tab === 'learning'
            ? $this->learningService->getActiveChunkedRun()
            : null;

        return view('admin.pages.ai-chatbot.index', [
            'tab' => $tab,
            'search' => $search,
            'category' => $category,
            'categories' => AiKnowledgeEntry::CATEGORIES,
            'approvedEntries' => $approvedEntries,
            'pendingEntries' => $pendingEntries,
            'logs' => $logs,
            'stats' => $stats,
            'templates' => $templates,
            'clientDocOptions' => $clientDocOptions,
            'aiRuntimeConfig' => $aiRuntimeConfig,
            'policy' => $policy,
            'customerInsights' => $customerInsights,
            'learningRuns' => $learningRuns,
            'latestEvalRun' => $latestEvalRun,
            'availableModels' => $availableModels,
            'activeChunkedRun' => $activeChunkedRun ? $this->learningService->formatRunSnapshot($activeChunkedRun) : null,
        ]);
    }

    public function store(AdminAiKnowledgeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $evidenceRefs = ['admin_verified'];
        if (! empty($data['template_slug'])) {
            $evidenceRefs[] = 'template:'.$data['template_slug'];
        }
        if (! empty($data['doc_url'])) {
            $evidenceRefs[] = 'doc:'.$data['doc_url'];
        }

        $entry = AiKnowledgeEntry::query()->create([
            'category' => $data['category'],
            'title' => $this->securityGuard->sanitizePlainText((string) $data['title'], 190),
            'keywords' => isset($data['keywords']) ? $this->securityGuard->sanitizePlainText((string) $data['keywords'], 500) : null,
            'answer' => $this->securityGuard->sanitizePlainText((string) $data['answer'], 2000),
            'template_slug' => $data['template_slug'] ?? null,
            'doc_url' => $data['doc_url'] ?? null,
            'source' => AiKnowledgeEntry::SOURCE_ADMIN,
            'status' => AiKnowledgeEntry::STATUS_APPROVED,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'is_outdated' => false,
            'priority' => (int) ($data['priority'] ?? 7),
            'version' => 1,
            'trust_score' => 100,
            'evidence_refs' => $evidenceRefs,
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
            'effective_from' => $request->filled('effective_from') ? $request->date('effective_from') : null,
            'effective_until' => $request->filled('effective_until') ? $request->date('effective_until') : null,
        ]);

        $entry->recordVersion(Auth::id(), 'Admin khởi tạo tri thức chính thức v1');

        AuditLogger::log('AI_KNOWLEDGE_CREATED', 'AiKnowledgeEntry', $entry->id, "Admin thêm tri thức chính thức: {$entry->title}");

        return redirect()
            ->route('admin.ai_chatbot.index', ['tab' => 'approved'])
            ->with('success', 'Đã thêm tri thức chính thức (v1) và kích hoạt vào bộ não Mây Hẹn.');
    }

    public function update(AdminAiKnowledgeRequest $request, AiKnowledgeEntry $entry): RedirectResponse
    {
        $data = $request->validated();

        // Đảm bảo lưu snapshot trước khi tăng phiên bản nếu chưa có v1
        if ($entry->versions()->count() === 0) {
            $entry->recordVersion(Auth::id(), 'Bản gốc trước khi chỉnh sửa');
        }

        $nextVersion = max((int) ($entry->version ?: 1), (int) $entry->versions()->max('version')) + 1;

        $entry->update([
            'category' => $data['category'],
            'title' => $this->securityGuard->sanitizePlainText((string) $data['title'], 190),
            'keywords' => isset($data['keywords']) ? $this->securityGuard->sanitizePlainText((string) $data['keywords'], 500) : null,
            'answer' => $this->securityGuard->sanitizePlainText((string) $data['answer'], 2000),
            'template_slug' => $data['template_slug'] ?? null,
            'doc_url' => $data['doc_url'] ?? null,
            'priority' => (int) ($data['priority'] ?? $entry->priority),
            'is_active' => (bool) ($data['is_active'] ?? false),
            'version' => $nextVersion,
            'effective_from' => $request->filled('effective_from') ? $request->date('effective_from') : $entry->effective_from,
            'effective_until' => $request->filled('effective_until') ? $request->date('effective_until') : $entry->effective_until,
        ]);

        $entry->recordVersion(Auth::id(), "Admin cập nhật tri thức lên v{$nextVersion}");

        AuditLogger::log('AI_KNOWLEDGE_UPDATED', 'AiKnowledgeEntry', $entry->id, "Cập nhật tri thức #{$entry->id} lên v{$nextVersion}");

        return redirect()
            ->route('admin.ai_chatbot.index', ['tab' => 'approved'])
            ->with('success', "Đã cập nhật mục tri thức lên phiên bản v{$nextVersion} (có lưu lịch sử Rollback).");
    }

    public function approve(Request $request, AiKnowledgeEntry $entry): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'min:3', 'max:190'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'answer' => ['nullable', 'string', 'min:5', 'max:2000'],
            'template_slug' => ['nullable', 'string', 'max:120', 'exists:invitation_templates,slug'],
            'doc_url' => ['nullable', 'string', 'max:190', 'regex:/^\/docs(\/[a-zA-Z0-9\-_]+)*$/'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $nextVersion = max(1, (int) ($entry->version ?: 1));

        $entry->update([
            'title' => ! empty($validated['title']) ? $this->securityGuard->sanitizePlainText((string) $validated['title'], 190) : $entry->title,
            'keywords' => array_key_exists('keywords', $validated) && $validated['keywords'] !== null
                ? $this->securityGuard->sanitizePlainText((string) $validated['keywords'], 500)
                : $entry->keywords,
            'answer' => ! empty($validated['answer']) ? $this->securityGuard->sanitizePlainText((string) $validated['answer'], 2000) : $entry->answer,
            'template_slug' => $validated['template_slug'] ?? $entry->template_slug,
            'doc_url' => $validated['doc_url'] ?? $entry->doc_url,
            'priority' => (int) ($validated['priority'] ?? $entry->priority),
            'status' => AiKnowledgeEntry::STATUS_APPROVED,
            'is_active' => true,
            'is_outdated' => false,
            'version' => $nextVersion,
            'trust_score' => 100,
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        $entry->recordVersion(Auth::id(), 'Admin phê duyệt & xuất bản vào Kho Tri Thức chính thức');

        AuditLogger::log('AI_KNOWLEDGE_APPROVED', 'AiKnowledgeEntry', $entry->id, "Phê duyệt & xuất bản tri thức #{$entry->id}: {$entry->title}");

        return redirect()
            ->route('admin.ai_chatbot.index', ['tab' => 'learning'])
            ->with('success', 'Đã phê duyệt và xuất bản tri thức vào Bộ não Mây Hẹn.');
    }

    public function reject(Request $request, AiKnowledgeEntry $entry): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string', 'max:190'],
        ]);

        $entry->update([
            'status' => AiKnowledgeEntry::STATUS_REJECTED,
            'is_active' => false,
            'rejection_reason' => isset($validated['rejection_reason'])
                ? $this->securityGuard->sanitizePlainText((string) $validated['rejection_reason'], 190)
                : 'Không đạt kiểm duyệt nội dung.',
        ]);

        AuditLogger::log('AI_KNOWLEDGE_REJECTED', 'AiKnowledgeEntry', $entry->id, "Từ chối ứng viên tri thức #{$entry->id}");

        return redirect()
            ->route('admin.ai_chatbot.index', ['tab' => 'learning'])
            ->with('success', 'Đã từ chối đề xuất tri thức.');
    }

    public function rollback(AiKnowledgeEntry $entry, AiKnowledgeVersion $version): RedirectResponse
    {
        if ((int) $version->knowledge_entry_id !== (int) $entry->id) {
            abort(404);
        }

        $entry->rollbackToVersion($version, Auth::id());

        AuditLogger::log('AI_KNOWLEDGE_ROLLBACK', 'AiKnowledgeEntry', $entry->id, "Rollback tri thức #{$entry->id} về phiên bản v{$version->version}");

        return redirect()
            ->route('admin.ai_chatbot.index', ['tab' => 'approved'])
            ->with('success', "Đã khôi phục (Rollback) tri thức về nội dung của phiên bản v{$version->version}.");
    }

    public function toggleOutdated(AiKnowledgeEntry $entry): RedirectResponse
    {
        $nextState = ! (bool) $entry->is_outdated;
        $entry->update([
            'is_outdated' => $nextState,
            'is_active' => $nextState ? false : true,
        ]);

        AuditLogger::log(
            'AI_KNOWLEDGE_OUTDATED_TOGGLED',
            'AiKnowledgeEntry',
            $entry->id,
            $nextState ? "Đánh dấu lỗi thời & ngưng dùng tri thức #{$entry->id}" : "Khôi phục hiệu lực tri thức #{$entry->id}"
        );

        return redirect()
            ->route('admin.ai_chatbot.index', ['tab' => 'approved'])
            ->with('success', $nextState ? 'Đã đánh dấu lỗi thời và gỡ khỏi ngữ cảnh AI live.' : 'Đã kích hoạt lại mục tri thức.');
    }

    public function destroy(AiKnowledgeEntry $entry): RedirectResponse
    {
        $id = $entry->id;
        $title = $entry->title;
        $entry->delete();

        AuditLogger::log('AI_KNOWLEDGE_DELETED', 'AiKnowledgeEntry', $id, "Xóa mục tri thức #{$id}: {$title}");

        return redirect()
            ->back()
            ->with('success', 'Đã xóa mục tri thức khỏi kho AI.');
    }

    public function runSelfLearning(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'topics' => ['nullable', 'array'],
            'topics.*' => ['string', 'in:templates,pricing,rsvp,docs,general'],
            'per_topic' => ['nullable', 'integer', 'min:1', 'max:3'],
        ]);

        $topics = ! empty($validated['topics']) ? $validated['topics'] : ['templates', 'pricing', 'rsvp'];
        $perTopic = (int) ($validated['per_topic'] ?? 2);

        $result = $this->learningService->runSelfLearningCycle(
            topics: $topics,
            perTopic: $perTopic,
            triggeredByUserId: Auth::id()
        );

        return redirect()
            ->route('admin.ai_chatbot.index', ['tab' => 'learning'])
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Khởi tạo phiên Client-Server Chunked Micro-Step Learning (phản hồi < 50ms, 0 timeout).
     */
    public function startChunkedLearning(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'topics' => ['required', 'array', 'min:1'],
            'topics.*' => ['string', 'in:templates,pricing,rsvp,docs,general'],
            'per_topic' => ['required', 'integer', 'min:1', 'max:3'],
            'rounds' => ['required', 'integer', 'min:1', 'max:60'],
            'cooldown_ms' => ['required', 'integer', 'min:1500', 'max:15000'],
        ]);

        $res = $this->learningService->startChunkedRun(
            topics: $validated['topics'],
            perTopic: (int) $validated['per_topic'],
            rounds: (int) $validated['rounds'],
            cooldownMs: (int) $validated['cooldown_ms'],
            triggeredByUserId: Auth::id()
        );

        return response()->json($res, $res['ok'] ? 201 : 409);
    }

    /**
     * Thực thi ĐÚNG 1 vi-bước (propose hoặc verify) rồi giải phóng PHP-FPM & RAM ngay lập tức.
     */
    public function stepChunkedLearning(AiLearningRun $run): JsonResponse
    {
        if ($run->run_type !== AiLearningRun::TYPE_SELF_TRAIN) {
            return response()->json(['ok' => false, 'message' => 'Loại phiên chạy không hợp lệ.'], 422);
        }

        $res = $this->learningService->executeNextMicroStep($run);

        return response()->json($res);
    }

    public function pauseChunkedLearning(AiLearningRun $run): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'run' => $this->learningService->pauseRun($run),
        ]);
    }

    public function resumeChunkedLearning(AiLearningRun $run): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'run' => $this->learningService->resumeRun($run),
        ]);
    }

    public function cancelChunkedLearning(AiLearningRun $run): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'run' => $this->learningService->cancelRun($run),
        ]);
    }

    public function runEvaluation(): RedirectResponse
    {
        $eval = $this->evaluationService->runRegressionSuite(Auth::id());

        return redirect()
            ->route('admin.ai_chatbot.index', ['tab' => 'quality'])
            ->with(
                $eval['failed'] === 0 ? 'success' : 'error',
                "Regression Evaluation hoàn tất: {$eval['passed']}/{$eval['total']} kịch bản đạt chuẩn ({$eval['pass_rate']}%)."
            );
    }

    public function updateGovernance(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kill_switch' => ['nullable', 'boolean'],
            'draft_model' => ['nullable', 'string', 'max:120'],
            'reviewer_model' => ['nullable', 'string', 'max:120'],
            'enable_reviewer_for_complex' => ['nullable', 'boolean'],
            'max_review_rounds' => ['required', 'integer', 'min:0', 'max:2'],
            'memory_enabled' => ['nullable', 'boolean'],
            'retention_days' => ['required', 'integer', 'min:7', 'max:365'],
            'daily_token_budget' => ['required', 'integer', 'min:10000', 'max:50000000'],
            'allowed_sources' => ['required', 'array', 'min:1'],
            'allowed_sources.*' => ['string', 'in:approved_knowledge,templates,plans,client_docs'],
        ]);

        $this->governance->updatePolicy($validated, Auth::id());

        return redirect()
            ->route('admin.ai_chatbot.index', ['tab' => 'governance'])
            ->with('success', 'Đã lưu chính sách AI Governance và áp dụng ngay lập tức.');
    }

    public function purgeRetention(): RedirectResponse
    {
        $result = $this->memoryService->purgeExpired();

        AuditLogger::log(
            'AI_RETENTION_PURGE_MANUAL',
            'AiGovernance',
            Auth::id() ?? 0,
            "Admin dọn dẹp dữ liệu AI quá hạn: {$result['memories_deleted']} memory, {$result['logs_deleted']} logs",
            $result
        );

        return redirect()
            ->route('admin.ai_chatbot.index', ['tab' => 'governance'])
            ->with('success', "Đã dọn dẹp {$result['memories_deleted']} bộ nhớ khách hàng và {$result['logs_deleted']} nhật ký hội thoại quá hạn.");
    }
}
