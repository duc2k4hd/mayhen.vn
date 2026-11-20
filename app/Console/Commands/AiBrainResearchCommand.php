<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AiResearchSource;
use App\Models\AiResearchTopic;
use App\Services\Intelligence\Research\CurriculumEngineService;
use App\Services\Intelligence\Research\MultiAgentResearchService;
use Illuminate\Console\Command;

class AiBrainResearchCommand extends Command
{
    protected $signature = 'ai:brain:research
        {--topic= : ID hoặc slug chủ đề cụ thể cần nghiên cứu (tải nguồn + trích xuất + phản biện)}
        {--max-docs=2 : Số tài liệu web tối đa xử lý trong lượt này (1-4)}';

    protected $description = 'Thực thi trọn vẹn 1 lượt nghiên cứu: tải nội dung sạch, trích xuất luận điểm & phản biện đa nguồn';

    public function handle(CurriculumEngineService $curriculum, MultiAgentResearchService $research): int
    {
        $curriculum->ensureVietnamWeddingCurriculum(null);
        $topicOpt = trim((string) $this->option('topic'));
        $maxDocs = max(1, min(4, (int) $this->option('max-docs')));

        $topic = null;
        if ($topicOpt !== '') {
            $topic = AiResearchTopic::query()
                ->with('goal')
                ->where(fn ($q) => ctype_digit($topicOpt) ? $q->where('id', (int) $topicOpt) : $q->where('slug', $topicOpt))
                ->first();
        } else {
            $sourceTopicId = AiResearchSource::query()
                ->where('status', AiResearchSource::STATUS_DISCOVERED)
                ->value('topic_id');

            $topic = $sourceTopicId
                ? AiResearchTopic::query()->with('goal')->find((int) $sourceTopicId)
                : AiResearchTopic::query()->with('goal')->orderByDesc('priority')->orderBy('source_coverage')->first();
        }

        if (! $topic) {
            $this->warn('Không tìm thấy chủ đề nghiên cứu nào.');

            return self::SUCCESS;
        }

        // Nếu chủ đề chưa có nguồn discovered nào -> tự động discover trước 1 nhịp
        $pendingSources = AiResearchSource::query()
            ->where('topic_id', $topic->id)
            ->where('status', AiResearchSource::STATUS_DISCOVERED)
            ->count();

        if ($pendingSources === 0) {
            $disc = $research->runDiscoverStep($topic);
            $this->info("[{$disc['phase']}] {$disc['message']}");
        }

        $ext = $research->runExtractStep($topic, $maxDocs);
        $this->info("[{$ext['phase']}] {$ext['message']}");
        if (($ext['status'] ?? '') === 'budget_blocked') {
            return self::SUCCESS;
        }

        $ver = $research->runVerifyAndDistillStep($topic);
        $this->info("[{$ver['phase']}] {$ver['message']}");

        return ! empty($ver['ok']) ? self::SUCCESS : self::FAILURE;
    }
}
