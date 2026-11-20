<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Intelligence\MultiAgentLearningService;
use Illuminate\Console\Command;

class AiSelfTrainCommand extends Command
{
    protected $signature = 'mayhen:ai-self-train
                            {--topics=all : Danh sách chủ đề cách nhau bằng dấu phẩy (templates,pricing,rsvp,docs,general) hoặc all}
                            {--per-topic=2 : Số ứng viên tri thức đề xuất mỗi chủ đề (1-4)}';

    protected $description = 'Chạy Mây Hẹn Multi-Agent Learning Pipeline (AI Proposal <-> Critic Reviewer -> Hàng đợi Admin duyệt)';

    public function handle(MultiAgentLearningService $learningService): int
    {
        $rawTopics = trim((string) $this->option('topics'));
        $topics = $rawTopics === '' || strtolower($rawTopics) === 'all'
            ? ['templates', 'pricing', 'rsvp', 'docs', 'general']
            : array_values(array_filter(array_map('trim', explode(',', $rawTopics))));

        $perTopic = max(1, min(4, (int) $this->option('per-topic')));

        $this->info('Khởi chạy Mây Hẹn Multi-Agent Learning Pipeline (Chủ đề: '.implode(', ', $topics)." | {$perTopic} mục/chủ đề)...");
        $this->line('Lưu ý Governance: Mọi đề xuất vượt qua Critic AI & Hard-Guard đều vào Hàng đợi chờ Admin phê duyệt (không tự xuất bản).');

        $result = $learningService->runSelfLearningCycle(
            topics: $topics,
            perTopic: $perTopic,
            triggeredByUserId: null
        );

        if (! $result['ok']) {
            $this->error($result['message']);

            return self::FAILURE;
        }

        $this->info($result['message']);

        if ($result['created_entries'] !== []) {
            $this->table(
                ['ID', 'Chủ đề', 'Tiêu đề đề xuất (Chờ Admin duyệt)', 'Fact Score'],
                array_map(fn (array $e) => [$e['id'], $e['category'], $e['title'], $e['fact_score'].'/100'], $result['created_entries'])
            );
        }

        if ($result['rejection_reasons'] !== []) {
            $this->warn('Các mục bị Critic AI / Hard-Guard từ chối:');
            foreach (array_slice($result['rejection_reasons'], 0, 10) as $reason) {
                $this->line(' - '.$reason);
            }
        }

        return self::SUCCESS;
    }
}
