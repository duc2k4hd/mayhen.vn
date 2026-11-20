<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AiResearchTopic;
use App\Services\Intelligence\Research\CurriculumEngineService;
use App\Services\Intelligence\Research\MultiAgentResearchService;
use Illuminate\Console\Command;

class AiBrainDiscoverCommand extends Command
{
    protected $signature = 'ai:brain:discover
        {--topic= : ID hoặc slug chủ đề cụ thể cần khám phá nguồn}
        {--limit=1 : Số chủ đề tối đa chạy khám phá trong 1 lần gọi (1-5)}';

    protected $description = 'Khám phá nguồn nghiên cứu web hợp lệ mới cho các chủ đề trong Curriculum V3';

    public function handle(CurriculumEngineService $curriculum, MultiAgentResearchService $research): int
    {
        $curriculum->ensureVietnamWeddingCurriculum(null);

        $topicOpt = trim((string) $this->option('topic'));
        $limit = max(1, min(5, (int) $this->option('limit')));

        if ($topicOpt !== '') {
            $topics = AiResearchTopic::query()
                ->with('goal')
                ->where(function ($q) use ($topicOpt) {
                    if (ctype_digit($topicOpt)) {
                        $q->where('id', (int) $topicOpt);
                    } else {
                        $q->where('slug', $topicOpt);
                    }
                })
                ->get();
        } else {
            $topics = AiResearchTopic::query()
                ->with('goal')
                ->whereHas('goal', fn ($q) => $q->where('status', 'active'))
                ->orderByDesc('priority')
                ->orderBy('source_coverage')
                ->orderBy('id')
                ->limit($limit)
                ->get();
        }

        if ($topics->isEmpty()) {
            $this->warn('Không tìm thấy chủ đề nghiên cứu phù hợp.');

            return self::SUCCESS;
        }

        foreach ($topics as $topic) {
            $res = $research->runDiscoverStep($topic);
            $this->info("[{$res['phase']}] Domain: {$res['domain']} | Topic: {$res['topic']}");
            $this->line("  Queries: {$res['queries']} | Sources discovered: {$res['sources_discovered']} | Eligible: {$res['eligible_sources']} | Duplicates: {$res['duplicates']} | Rejected: {$res['rejected']}");
            if (! empty($res['channels'])) {
                $this->line('  Channels: '.implode(', ', (array) $res['channels']));
            }
            $this->line("  Next plan: {$res['next_plan']}");
        }

        return self::SUCCESS;
    }
}
