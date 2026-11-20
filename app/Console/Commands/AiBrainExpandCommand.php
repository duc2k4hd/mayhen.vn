<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AiResearchTopic;
use App\Services\Intelligence\Research\CurriculumEngineService;
use App\Services\Intelligence\Research\MultiAgentResearchService;
use Illuminate\Console\Command;

class AiBrainExpandCommand extends Command
{
    protected $signature = 'ai:brain:expand {--topic= : ID hoặc slug chủ đề cần mở rộng sang chủ đề con liên quan}';

    protected $description = 'Đề xuất và lập kế hoạch mở rộng có kiểm soát các chủ đề nghiên cứu liên quan';

    public function handle(CurriculumEngineService $curriculum, MultiAgentResearchService $research): int
    {
        $curriculum->ensureVietnamWeddingCurriculum(null);
        $topicOpt = trim((string) $this->option('topic'));

        $topic = null;
        if ($topicOpt !== '') {
            $topic = AiResearchTopic::query()
                ->with('goal')
                ->where(fn ($q) => ctype_digit($topicOpt) ? $q->where('id', (int) $topicOpt) : $q->where('slug', $topicOpt))
                ->first();
        } else {
            $topic = AiResearchTopic::query()
                ->with('goal')
                ->whereHas('goal', fn ($q) => $q->where('status', 'active')->where('allow_auto_expand', true))
                ->orderByDesc('priority')
                ->orderByDesc('learning_stage')
                ->get()
                ->first(fn (AiResearchTopic $t) => ! empty($t->unknown_concepts));
        }

        if (! $topic) {
            $this->info('Hiện chưa có chủ đề nào còn khái niệm chưa khám phá để mở rộng thêm.');

            return self::SUCCESS;
        }

        $res = $research->runExpandStep($topic);
        $this->info("[{$res['phase']}] {$res['message']}");

        return self::SUCCESS;
    }
}
