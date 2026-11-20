<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminAiKnowledgeRequest;
use App\Models\AiChatbotLog;
use App\Models\AiKnowledgeEntry;
use App\Models\InvitationTemplate;
use App\Services\Chatbot\ChatbotSecurityGuard;
use App\Services\DocumentationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AiChatbotAdminController extends Controller
{
    public function __construct(
        protected ChatbotSecurityGuard $securityGuard,
        protected DocumentationService $documentationService
    ) {}

    public function index(Request $request): View
    {
        $tab = (string) $request->query('tab', 'approved');
        $search = trim((string) $request->query('q', ''));
        $category = (string) $request->query('category', '');

        $approvedQuery = AiKnowledgeEntry::query()
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

        $stats = [
            'approved_count' => AiKnowledgeEntry::query()->where('status', AiKnowledgeEntry::STATUS_APPROVED)->where('is_active', true)->count(),
            'pending_count' => AiKnowledgeEntry::query()->where('status', AiKnowledgeEntry::STATUS_PENDING)->count(),
            'total_chats' => AiChatbotLog::query()->count(),
            'blocked_security_count' => AiChatbotLog::query()->where('response_mode', AiChatbotLog::MODE_BLOCKED_SECURITY)->count(),
            'helpful_count' => AiChatbotLog::query()->where('feedback', 'helpful')->count(),
            'unhelpful_count' => AiChatbotLog::query()->where('feedback', 'unhelpful')->count(),
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

        $aiRuntimeConfig = [
            'enabled' => (bool) config('services.ninerouter.enabled', false),
            'model' => (string) config('services.ninerouter.model', ''),
            'base_url' => (string) config('services.ninerouter.base_url', ''),
            'has_token' => (string) config('services.ninerouter.api_key', '') !== '',
            'last_provider_status' => AiChatbotLog::query()
                ->whereNotNull('provider_status')
                ->where('provider_status', '!=', 'not_called')
                ->latest('id')
                ->value('provider_status'),
        ];

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
        ]);
    }

    public function store(AdminAiKnowledgeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        AiKnowledgeEntry::query()->create([
            'category' => $data['category'],
            'title' => $this->securityGuard->sanitizePlainText((string) $data['title'], 190),
            'keywords' => isset($data['keywords']) ? $this->securityGuard->sanitizePlainText((string) $data['keywords'], 500) : null,
            'answer' => $this->securityGuard->sanitizePlainText((string) $data['answer'], 2000),
            'template_slug' => $data['template_slug'] ?? null,
            'doc_url' => $data['doc_url'] ?? null,
            'source' => AiKnowledgeEntry::SOURCE_ADMIN,
            'status' => AiKnowledgeEntry::STATUS_APPROVED,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'priority' => (int) ($data['priority'] ?? 6),
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('admin.ai_chatbot.index', ['tab' => 'approved'])
            ->with('success', 'Đã thêm tri thức huấn luyện AI và kích hoạt ngay lập tức.');
    }

    public function update(AdminAiKnowledgeRequest $request, AiKnowledgeEntry $entry): RedirectResponse
    {
        $data = $request->validated();

        $entry->update([
            'category' => $data['category'],
            'title' => $this->securityGuard->sanitizePlainText((string) $data['title'], 190),
            'keywords' => isset($data['keywords']) ? $this->securityGuard->sanitizePlainText((string) $data['keywords'], 500) : null,
            'answer' => $this->securityGuard->sanitizePlainText((string) $data['answer'], 2000),
            'template_slug' => $data['template_slug'] ?? null,
            'doc_url' => $data['doc_url'] ?? null,
            'priority' => (int) ($data['priority'] ?? $entry->priority),
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()
            ->route('admin.ai_chatbot.index', ['tab' => 'approved'])
            ->with('success', 'Đã cập nhật mục tri thức huấn luyện AI.');
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
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return redirect()
            ->route('admin.ai_chatbot.index', ['tab' => 'pending'])
            ->with('success', 'Đã duyệt và đưa đóng góp của khách hàng vào kho tri thức chính thức.');
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

        return redirect()
            ->route('admin.ai_chatbot.index', ['tab' => 'pending'])
            ->with('success', 'Đã từ chối đề xuất huấn luyện từ khách hàng.');
    }

    public function destroy(AiKnowledgeEntry $entry): RedirectResponse
    {
        $entry->delete();

        return redirect()
            ->back()
            ->with('success', 'Đã xóa mục tri thức khỏi kho AI.');
    }
}
