<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AiResearchGoal;
use App\Models\AiResearchTopic;
use App\Services\Intelligence\Research\CurriculumEngineService;
use Illuminate\Console\Command;

class AiBrainCurriculumCommand extends Command
{
    protected $signature = 'ai:brain:curriculum {--seed : Khởi tạo / bổ sung lộ trình Ngành Cưới Việt Nam mặc định}';

    protected $description = 'Hiển thị toàn bộ Chương trình học động (Dynamic Curriculum L1-L7) và tiến độ bao phủ';

    public function handle(CurriculumEngineService $curriculum): int
    {
        if ($this->option('seed') || AiResearchGoal::query()->count() === 0) {
            $seeded = $curriculum->ensureVietnamWeddingCurriculum(null);
            $this->info("Đã sẵn sàng lộ trình Ngành Cưới Việt Nam (Goal #{$seeded['goal_id']}: +{$seeded['topics_created']} mới, tổng {$seeded['total_topics']} chủ đề).");
        }

        $goals = AiResearchGoal::query()->orderByDesc('priority')->get();
        $this->table(
            ['ID', 'Mục tiêu nghiên cứu', 'Lĩnh vực', 'Quốc gia/NN', 'Tự chủ', 'Trạng thái'],
            $goals->map(fn (AiResearchGoal $g) => [
                $g->id,
                $g->title,
                $g->domain,
                "{$g->country}/{$g->language}",
                $g->autonomy_level,
                $g->status,
            ])->all()
        );

        $topics = AiResearchTopic::query()->orderByDesc('priority')->orderBy('learning_stage')->orderBy('id')->get();
        $this->table(
            ['ID', 'Domain', 'Chủ đề', 'Cấp', 'Nguồn', 'Bằng chứng', 'Điểm', 'Trạng thái', 'Lịch kế tiếp'],
            $topics->map(fn (AiResearchTopic $t) => [
                $t->id,
                $t->domain,
                mb_substr($t->title, 0, 48, 'UTF-8'),
                'L'.$t->learning_stage,
                $t->source_coverage,
                $t->evidence_count,
                $t->quality_score.'/100',
                $t->status,
                $t->next_research_at?->format('d/m H:i') ?? 'Ngay',
            ])->all()
        );

        $next = $curriculum->pickNextResearchAction();
        $this->line("Hành động đề xuất kế tiếp: [{$next['action']}] {$next['reason']}");

        return self::SUCCESS;
    }
}
