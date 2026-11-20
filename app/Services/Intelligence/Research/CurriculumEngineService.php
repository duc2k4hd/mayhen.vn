<?php

declare(strict_types=1);

namespace App\Services\Intelligence\Research;

use App\Models\AiResearchClaim;
use App\Models\AiResearchGoal;
use App\Models\AiResearchSource;
use App\Models\AiResearchTopic;
use App\Services\AuditLogger;
use App\Services\Chatbot\ChatbotProviderClient;
use App\Services\Intelligence\AiGovernanceService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Dynamic Learning Curriculum Engine V3:
 * - Quản lý cây chủ đề nghiên cứu theo 7 giai đoạn (L1 Foundation -> L7 Expansion).
 * - Khởi tạo lộ trình chuyên sâu Ngành Cưới & Thiệp Cưới Điện Tử Việt Nam (4 Domains cốt lõi).
 * - Cho phép Admin giao bất kỳ mục tiêu nghiên cứu mới nào mà không cần sửa code.
 * - Tự điều phối hành động nghiên cứu tiếp theo (xóa bỏ tình trạng idle_no_new_data sai!).
 */
class CurriculumEngineService
{
    public function __construct(
        protected AiGovernanceService $governance,
        protected ChatbotProviderClient $providerClient
    ) {}

    /**
     * Khởi tạo idempotent Chương trình nghiên cứu chuyên sâu Ngành Cưới Việt Nam (Mục III & V).
     *
     * @return array{goal_id: int, topics_created: int, total_topics: int}
     */
    public function ensureVietnamWeddingCurriculum(?int $adminUserId = null): array
    {
        if (! Schema::hasTable('ai_research_goals') || ! Schema::hasTable('ai_research_topics')) {
            return ['goal_id' => 0, 'topics_created' => 0, 'total_topics' => 0];
        }

        $goal = AiResearchGoal::query()->firstOrCreate(
            ['slug' => 'vietnamese-wedding-mastery'],
            [
                'title' => 'Chuyên sâu Ngành Cưới & Thiệp Cưới Điện Tử Việt Nam',
                'domain' => 'vietnamese_wedding',
                'description' => 'Nghiên cứu toàn diện thị trường thiệp cưới điện tử, văn hóa cưới hỏi 3 miền Việt Nam, quản lý sự kiện cưới và trải nghiệm khách mời để phục vụ khách hàng Mây Hẹn tốt nhất.',
                'priority' => 10,
                'country' => 'VN',
                'language' => 'vi',
                'max_depth' => 7,
                'autonomy_level' => $this->governance->getPolicy()['research_autonomy_level'],
                'daily_url_limit' => 25,
                'allow_auto_expand' => true,
                'status' => AiResearchGoal::STATUS_ACTIVE,
                'created_by_user_id' => $adminUserId,
            ]
        );

        $seedTopics = [
            // Domain 1: Thiệp cưới điện tử
            [
                'slug' => 'vn-digital-wedding-invitations-trends',
                'title' => 'Thiệp cưới online và xu hướng thiệp cưới điện tử tại Việt Nam',
                'domain' => 'digital_invitations',
                'category' => 'templates',
                'priority' => 10,
                'search_queries' => ['thiệp cưới online Việt Nam', 'xu hướng thiệp cưới điện tử', 'website thiệp cưới hiện đại'],
                'learning_objectives' => ['Nắm lợi ích, xu hướng và các loại thiệp cưới online phổ biến tại Việt Nam', 'Hiểu kỳ vọng của cặp đôi trẻ và gia đình hai bên'],
                'unknown_concepts' => ['thiệp cưới cá nhân hóa gửi Zalo', 'tỷ lệ mở thiệp trên điện thoại'],
            ],
            [
                'slug' => 'vn-wedding-invitation-wording-etiquette',
                'title' => 'Cách viết nội dung thiệp cưới và cách xưng hô chuẩn Việt Nam',
                'domain' => 'digital_invitations',
                'category' => 'general',
                'priority' => 10,
                'search_queries' => ['cách viết thiệp cưới chuẩn Việt Nam', 'cách ghi tên bố mẹ trên thiệp cưới', 'cách xưng hô trên thiệp mời cưới'],
                'learning_objectives' => ['Phân biệt cách ghi thiệp nhà trai, nhà gái, báo hỷ', 'Hiểu cách ghi trong hoàn cảnh đặc biệt một cách tế nhị'],
                'unknown_concepts' => ['cách ghi thiệp cưới khi song thân đã mất', 'lời mời cưới hiện đại thân mật'],
            ],
            [
                'slug' => 'vn-digital-invitation-zalo-mobile-ux',
                'title' => 'Gửi thiệp cưới qua Zalo, mạng xã hội và trải nghiệm di động',
                'domain' => 'digital_invitations',
                'category' => 'templates',
                'priority' => 9,
                'search_queries' => ['gửi thiệp cưới online qua Zalo', 'lời nhắn gửi thiệp cưới qua tin nhắn lịch sự', 'trải nghiệm xem thiệp cưới điện thoại'],
                'learning_objectives' => ['Cách gửi thiệp online lịch sự cho từng nhóm khách mời', 'Tối ưu hiển thị ảnh đại diện chia sẻ link & tốc độ mở'],
                'unknown_concepts' => ['cách gửi thiệp online cho người lớn tuổi'],
            ],
            [
                'slug' => 'vn-multilingual-personalized-invitations',
                'title' => 'Thiệp cưới đa ngôn ngữ và cá nhân hóa tên khách mời',
                'domain' => 'digital_invitations',
                'category' => 'templates',
                'priority' => 8,
                'search_queries' => ['thiệp cưới song ngữ Việt Anh', 'cá nhân hóa thiệp cưới online', 'mẫu lời mời cưới tiếng Anh tự nhiên'],
                'learning_objectives' => ['Quy chuẩn trình bày thiệp song ngữ cho cặp đôi có khách quốc tế'],
                'unknown_concepts' => ['thiệp cưới đa ngôn ngữ Việt Nhật Hàn'],
            ],

            // Domain 2: Văn hóa cưới hỏi Việt Nam
            [
                'slug' => 'vn-wedding-dam-ngo-an-hoi-rituals',
                'title' => 'Nghi thức lễ dạm ngõ và lễ ăn hỏi trong văn hóa cưới Việt Nam',
                'domain' => 'wedding_culture_vn',
                'category' => 'general',
                'priority' => 10,
                'search_queries' => ['lễ dạm ngõ là gì Việt Nam', 'lễ ăn hỏi truyền thống Việt Nam', 'trình tự nghi lễ cưới hỏi Việt Nam'],
                'learning_objectives' => ['Hiểu rõ trình tự lễ dạm ngõ, lễ ăn hỏi, tráp lễ và vai trò hai bên gia đình'],
                'unknown_concepts' => ['số lượng tráp ăn hỏi theo vùng miền', 'lễ lại mặt sau cưới'],
            ],
            [
                'slug' => 'vn-wedding-northern-traditions',
                'title' => 'Phong tục cưới hỏi miền Bắc Việt Nam',
                'domain' => 'wedding_culture_vn',
                'category' => 'general',
                'priority' => 9,
                'search_queries' => ['phong tục cưới hỏi miền Bắc', 'lễ ăn hỏi miền Bắc mấy tráp', 'nghi thức đón dâu miền Bắc'],
                'learning_objectives' => ['Phân biệt đặc trưng phong tục cưới miền Bắc, lễ nạp tài và rước dâu'],
                'unknown_concepts' => ['số tráp lẻ miền Bắc', 'tiệc cưới dựng rạp và nhà hàng miền Bắc'],
            ],
            [
                'slug' => 'vn-wedding-central-traditions',
                'title' => 'Phong tục cưới hỏi miền Trung Việt Nam',
                'domain' => 'wedding_culture_vn',
                'category' => 'general',
                'priority' => 8,
                'search_queries' => ['phong tục cưới hỏi miền Trung', 'nghi lễ cưới hỏi truyền thống miền Trung', 'lễ vật cưới miền Trung'],
                'learning_objectives' => ['Hiểu sự trang trọng, mộc mạc và nét riêng của nghi lễ cưới miền Trung'],
                'unknown_concepts' => ['khay trầu rượu miền Trung', 'lễ rước dâu miền Trung'],
            ],
            [
                'slug' => 'vn-wedding-southern-traditions',
                'title' => 'Phong tục cưới hỏi miền Nam Việt Nam',
                'domain' => 'wedding_culture_vn',
                'category' => 'general',
                'priority' => 9,
                'search_queries' => ['phong tục cưới hỏi miền Nam', 'lễ lên đèn đám cưới miền Nam', 'số tráp cưới miền Nam'],
                'learning_objectives' => ['Hiểu nghi thức lễ lên đèn, số mâm quả chẵn và sự cởi mở trong tiệc cưới miền Nam'],
                'unknown_concepts' => ['trình tự lễ gia tiên miền Nam'],
            ],
            [
                'slug' => 'vn-wedding-traditional-meets-modern',
                'title' => 'Kết hợp lễ gia tiên truyền thống và tiệc cưới hiện đại',
                'domain' => 'wedding_culture_vn',
                'category' => 'general',
                'priority' => 8,
                'search_queries' => ['tổ chức lễ gia tiên kết hợp tiệc cưới hiện đại', 'đám cưới tối giản hiện đại Việt Nam', 'lễ vu quy và lễ thành hôn khác nhau thế nào'],
                'learning_objectives' => ['Phân biệt Lễ Vu Quy, Lễ Thành Hôn, Lễ Tân Hôn và Tiệc Báo Hỷ'],
                'unknown_concepts' => ['tổ chức cưới gộp hai họ một ngày'],
            ],

            // Domain 3: Quản lý đám cưới
            [
                'slug' => 'vn-wedding-planning-timeline',
                'title' => 'Lập kế hoạch đám cưới và Timeline điều phối ngày cưới',
                'domain' => 'wedding_planning',
                'category' => 'docs',
                'priority' => 9,
                'search_queries' => ['lập kế hoạch đám cưới chi tiết', 'timeline ngày cưới chuẩn', 'checklist chuẩn bị đám cưới Việt Nam'],
                'learning_objectives' => ['Các mốc chuẩn bị trước cưới 6 tháng đến ngày cưới và điều phối sự kiện'],
                'unknown_concepts' => ['phương án dự phòng khi thời tiết xấu ngày cưới'],
            ],
            [
                'slug' => 'vn-wedding-guest-list-rsvp',
                'title' => 'Quản lý danh sách khách mời và xác nhận tham dự RSVP',
                'domain' => 'wedding_planning',
                'category' => 'rsvp',
                'priority' => 10,
                'search_queries' => ['quản lý khách mời đám cưới', 'RSVP đám cưới là gì', 'cách dự trù số lượng khách mời tiệc cưới'],
                'learning_objectives' => ['Quy trình phân nhóm khách, theo dõi RSVP và hạn chế sai lệch bàn tiệc'],
                'unknown_concepts' => ['cách nhắc lịch khách mời chưa phản hồi RSVP lịch sự'],
            ],
            [
                'slug' => 'vn-wedding-seating-qr-checkin',
                'title' => 'Sắp xếp bàn tiệc cưới và điểm danh Check-in ngày cưới',
                'domain' => 'wedding_planning',
                'category' => 'rsvp',
                'priority' => 9,
                'search_queries' => ['cách sắp xếp bàn tiệc cưới khoa học', 'check in đám cưới bằng mã QR', 'đón tiếp khách mời tiệc cưới chuyên nghiệp'],
                'learning_objectives' => ['Nguyên tắc xếp bàn theo nhóm họ hàng/đồng nghiệp và quy trình check-in nhanh'],
                'unknown_concepts' => ['xử lý khách phát sinh đột xuất tại sảnh tiệc'],
            ],
            [
                'slug' => 'vn-wedding-budget-gifts-keepsakes',
                'title' => 'Quản lý ngân sách cưới, quà mừng và lưu giữ kỷ niệm',
                'domain' => 'wedding_planning',
                'category' => 'pricing',
                'priority' => 8,
                'search_queries' => ['quản lý ngân sách đám cưới Việt Nam', 'mừng cưới chuyển khoản QR tế nhị', 'lưu giữ ảnh và lời chúc đám cưới'],
                'learning_objectives' => ['Cách phân bổ ngân sách cưới hợp lý và trình bày mừng cưới trực tuyến tinh tế'],
                'unknown_concepts' => ['xu hướng lưu bút và ảnh khách chụp tại tiệc cưới'],
            ],

            // Domain 4: Sản phẩm & Trải nghiệm người dùng
            [
                'slug' => 'vn-wedding-website-ux-ui-mobile',
                'title' => 'Trải nghiệm người dùng UX/UI website thiệp cưới Mobile-First',
                'domain' => 'wedding_ux_product',
                'category' => 'templates',
                'priority' => 8,
                'search_queries' => ['thiết kế website đám cưới đẹp UX', 'tối ưu trải nghiệm người dùng trên di động', 'trợ năng chữ lớn cho người lớn tuổi trên web'],
                'learning_objectives' => ['Thiết kế tinh gọn dễ đọc cho cả khách trẻ và người lớn tuổi'],
                'unknown_concepts' => ['tối ưu tốc độ tải ảnh cưới trên mạng 4G'],
            ],
            [
                'slug' => 'vn-wedding-rsvp-conversion-optimization',
                'title' => 'Tối ưu tỷ lệ phản hồi RSVP và hoàn tất biểu mẫu khách mời',
                'domain' => 'wedding_ux_product',
                'category' => 'rsvp',
                'priority' => 8,
                'search_queries' => ['cách tăng tỷ lệ xác nhận tham dự sự kiện RSVP', 'tối ưu form đăng ký ngắn gọn trên di động'],
                'learning_objectives' => ['Giảm ma sát biểu mẫu để khách mời xác nhận tham dự trong dưới 15 giây'],
                'unknown_concepts' => ['thời điểm gửi nhắc lịch trước ngày cưới hiệu quả nhất'],
            ],
            [
                'slug' => 'vn-wedding-couple-journey-service-trends',
                'title' => 'Hành trình khách hàng và xu hướng dịch vụ cưới tại Việt Nam',
                'domain' => 'wedding_ux_product',
                'category' => 'general',
                'priority' => 7,
                'search_queries' => ['xu hướng dịch vụ cưới hỏi Việt Nam', 'hành trình chuẩn bị đám cưới của cặp đôi trẻ'],
                'learning_objectives' => ['Hiểu các nỗi đau và tiêu chí lựa chọn dịch vụ cưới số của cặp đôi Việt Nam'],
                'unknown_concepts' => ['vai trò của wedding planner và studio ảnh cưới tại Việt Nam'],
            ],
        ];

        $created = 0;
        foreach ($seedTopics as $t) {
            $exists = AiResearchTopic::query()->where('slug', $t['slug'])->exists();
            if (! $exists) {
                AiResearchTopic::query()->create(array_merge($t, [
                    'goal_id' => $goal->id,
                    'learning_stage' => 1,
                    'status' => AiResearchTopic::STATUS_PENDING,
                    'review_state' => 'unreviewed',
                    'next_research_at' => now(),
                ]));
                $created++;
            }
        }

        return [
            'goal_id' => (int) $goal->id,
            'topics_created' => $created,
            'total_topics' => AiResearchTopic::query()->where('goal_id', $goal->id)->count(),
        ];
    }

    /**
     * Admin-Defined Learning Goal (Mục X): Tạo mục tiêu bất kỳ & tự sinh cây Curriculum con không cần sửa code.
     *
     * @param  array<string, mixed>  $input
     * @return array{goal: AiResearchGoal, topics_created: int}
     */
    public function createAdminGoal(array $input, ?int $adminUserId = null): array
    {
        $title = trim((string) ($input['title'] ?? ''));
        $domain = Str::slug((string) ($input['domain'] ?? $title), '_');
        if ($domain === '') {
            $domain = 'custom_domain';
        }
        $domain = Str::limit($domain, 55, '');

        $baseSlug = Str::slug($title, '-');
        $slug = Str::limit($baseSlug !== '' ? $baseSlug : 'goal', 80, '').'-'.substr(md5($title.microtime(true)), 0, 6);

        $allowedDomains = $this->parseDomainList($input['allowed_domains'] ?? null);
        $disallowedDomains = $this->parseDomainList($input['disallowed_domains'] ?? null);

        $goal = AiResearchGoal::query()->create([
            'slug' => $slug,
            'title' => Str::limit($title, 185, ''),
            'domain' => $domain,
            'description' => isset($input['description']) ? Str::limit(trim((string) $input['description']), 1000, '') : null,
            'priority' => max(1, min(10, (int) ($input['priority'] ?? 8))),
            'country' => strtoupper(Str::limit(trim((string) ($input['country'] ?? 'VN')), 10, '')) ?: 'VN',
            'language' => strtolower(Str::limit(trim((string) ($input['language'] ?? 'vi')), 10, '')) ?: 'vi',
            'max_depth' => max(1, min(7, (int) ($input['max_depth'] ?? 7))),
            'autonomy_level' => in_array((string) ($input['autonomy_level'] ?? ''), ['conservative', 'supervised', 'autonomous'], true)
                ? (string) $input['autonomy_level']
                : 'supervised',
            'allowed_domains' => $allowedDomains !== [] ? $allowedDomains : null,
            'disallowed_domains' => $disallowedDomains !== [] ? $disallowedDomains : null,
            'daily_url_limit' => max(2, min(100, (int) ($input['daily_url_limit'] ?? 20))),
            'allow_auto_expand' => array_key_exists('allow_auto_expand', $input) ? (bool) $input['allow_auto_expand'] : true,
            'status' => AiResearchGoal::STATUS_ACTIVE,
            'created_by_user_id' => $adminUserId,
        ]);

        $subtopics = $this->decomposeGoalIntoSubtopics($goal);
        $created = 0;

        foreach ($subtopics as $idx => $sub) {
            $subTitle = Str::limit(trim((string) ($sub['title'] ?? '')), 185, '');
            if ($subTitle === '') {
                continue;
            }
            $subSlug = Str::limit(Str::slug($subTitle, '-') ?: 'topic', 95, '').'-'.$goal->id.'-'.($idx + 1);
            AiResearchTopic::query()->create([
                'goal_id' => $goal->id,
                'slug' => $subSlug,
                'title' => $subTitle,
                'domain' => $goal->domain,
                'category' => 'general',
                'priority' => max(1, min(10, (int) $goal->priority - ($idx > 2 ? 1 : 0))),
                'learning_stage' => 1,
                'learning_objectives' => array_values(array_map('strval', (array) ($sub['objectives'] ?? []))),
                'search_queries' => array_values(array_map('strval', (array) ($sub['queries'] ?? [$subTitle]))),
                'unknown_concepts' => array_values(array_map('strval', (array) ($sub['unknown_concepts'] ?? []))),
                'status' => AiResearchTopic::STATUS_PENDING,
                'review_state' => 'unreviewed',
                'next_research_at' => now(),
            ]);
            $created++;
        }

        AuditLogger::log(
            'AI_RESEARCH_GOAL_CREATED',
            'AiResearchGoal',
            $goal->id,
            "Admin thêm mục tiêu nghiên cứu mới: {$goal->title} ({$created} chủ đề con)",
            ['domain' => $goal->domain, 'topics_created' => $created]
        );

        return ['goal' => $goal, 'topics_created' => $created];
    }

    /**
     * Bộ lập lịch chống trạng thái idle giả (Mục VI):
     * Luôn tìm đúng việc có giá trị nhất cần làm tiếp theo trong Curriculum.
     *
     * @return array{action: string, topic: ?AiResearchTopic, reason: string, next_scheduled_at: ?string}
     */
    public function pickNextResearchAction(): array
    {
        if (! Schema::hasTable('ai_research_goals') || ! Schema::hasTable('ai_research_topics')) {
            return ['action' => 'schema_missing', 'topic' => null, 'reason' => 'Chưa chạy migration bảng Research V3.', 'next_scheduled_at' => null];
        }

        // Tự khởi tạo lộ trình Ngành Cưới Việt Nam nếu chưa có mục tiêu nào
        if (AiResearchGoal::query()->count() === 0) {
            $this->ensureVietnamWeddingCurriculum(null);
        }

        $activeGoalIds = AiResearchGoal::query()
            ->where('status', AiResearchGoal::STATUS_ACTIVE)
            ->orderByDesc('priority')
            ->pluck('id')
            ->all();

        if ($activeGoalIds === []) {
            return ['action' => 'no_active_goals', 'topic' => null, 'reason' => 'Không có mục tiêu nghiên cứu nào đang kích hoạt.', 'next_scheduled_at' => null];
        }

        // 1. Ưu tiên cao nhất: Nguồn đã phát hiện (discovered) hoặc đã tải chờ trích (fetched)
        $sourceNeedingExtract = AiResearchSource::query()
            ->whereIn('status', [AiResearchSource::STATUS_DISCOVERED, AiResearchSource::STATUS_FETCHED])
            ->whereHas('topic', fn ($q) => $q->whereIn('goal_id', $activeGoalIds))
            ->orderByDesc('relevance_score')
            ->orderByDesc('trust_score')
            ->orderBy('id')
            ->first();

        if ($sourceNeedingExtract && $sourceNeedingExtract->topic) {
            return [
                'action' => 'research_extract',
                'topic' => $sourceNeedingExtract->topic,
                'reason' => "Có nguồn web mới cần thu thập & trích xuất cho chủ đề [{$sourceNeedingExtract->topic->title}].",
                'next_scheduled_at' => null,
            ];
        }

        // 2. Các claim giả thuyết (hypothesis) cần kiểm chứng chéo & phản biện
        $claimNeedingVerify = AiResearchClaim::query()
            ->where('verification_status', AiResearchClaim::STATUS_HYPOTHESIS)
            ->whereHas('topic', fn ($q) => $q->whereIn('goal_id', $activeGoalIds))
            ->orderBy('id')
            ->first();

        if ($claimNeedingVerify && $claimNeedingVerify->topic) {
            return [
                'action' => 'research_verify',
                'topic' => $claimNeedingVerify->topic,
                'reason' => "Có luận điểm cần đối chiếu bằng chứng & phản biện cho chủ đề [{$claimNeedingVerify->topic->title}].",
                'next_scheduled_at' => null,
            ];
        }

        // 3. Chủ đề chưa đạt bao phủ nguồn hoặc đã tới lịch nghiên cứu lại (next_research_at <= now)
        $topicNeedingDiscover = AiResearchTopic::query()
            ->whereIn('goal_id', $activeGoalIds)
            ->where(function ($q) {
                $q->whereNull('next_research_at')->orWhere('next_research_at', '<=', now());
            })
            ->where('status', '!=', AiResearchTopic::STATUS_COMPLETED)
            ->orderByDesc('priority')
            ->orderBy('learning_stage')
            ->orderBy('source_coverage')
            ->orderBy('id')
            ->first();

        if ($topicNeedingDiscover) {
            return [
                'action' => 'research_discover',
                'topic' => $topicNeedingDiscover,
                'reason' => "Chủ đề [{$topicNeedingDiscover->title}] (L{$topicNeedingDiscover->learning_stage}, coverage={$topicNeedingDiscover->source_coverage}) cần khám phá thêm nguồn xác thực.",
                'next_scheduled_at' => null,
            ];
        }

        // 4. Mở rộng chủ đề con có kiểm soát cho các topic đã đạt độ sâu và có unknown_concepts
        $topicForExpand = AiResearchTopic::query()
            ->whereIn('goal_id', $activeGoalIds)
            ->whereHas('goal', fn ($q) => $q->where('allow_auto_expand', true))
            ->where('learning_stage', '>=', 2)
            ->where('learning_stage', '<', 7)
            ->where('status', '!=', AiResearchTopic::STATUS_EXPANDING)
            ->orderByDesc('priority')
            ->orderBy('id')
            ->first();

        if ($topicForExpand && ! empty($topicForExpand->unknown_concepts)) {
            return [
                'action' => 'research_expand',
                'topic' => $topicForExpand,
                'reason' => "Mở rộng chủ đề liên quan từ [{$topicForExpand->title}] theo khái niệm còn thiếu.",
                'next_scheduled_at' => null,
            ];
        }

        // 5. Không còn việc tức thời -> trả lịch nghiên cứu tiếp theo chính xác (không đốt token)
        $nextAt = AiResearchTopic::query()
            ->whereIn('goal_id', $activeGoalIds)
            ->whereNotNull('next_research_at')
            ->orderBy('next_research_at')
            ->value('next_research_at');

        return [
            'action' => 'idle_until_next_research',
            'topic' => null,
            'reason' => $nextAt
                ? "Các chủ đề hiện tại đã hoàn thành lượt nghiên cứu gần nhất — lịch nghiên cứu kế tiếp vào {$nextAt}."
                : 'Tất cả mục tiêu đang ở trạng thái đã lên lịch chờ chu kỳ tái xác minh.',
            'next_scheduled_at' => $nextAt ? (string) $nextAt : null,
        ];
    }

    /**
     * Mở rộng có kiểm soát các chủ đề con liên quan nằm trong ranh giới Goal/Domain được Admin cho phép.
     *
     * @return array{created: int, topics: list<string>}
     */
    public function expandTopic(AiResearchTopic $topic): array
    {
        $goal = $topic->goal;
        if (! $goal || ! $goal->allow_auto_expand) {
            return ['created' => 0, 'topics' => []];
        }

        // Giới hạn tổng số topic con trên mỗi goal tránh phình curriculum vô hạn
        if (AiResearchTopic::query()->where('goal_id', $goal->id)->count() >= 60) {
            $topic->update(['learning_stage' => min(7, $topic->learning_stage + 1), 'next_research_at' => now()->addDays(14)]);

            return ['created' => 0, 'topics' => []];
        }

        $unknowns = array_values(array_filter(array_map('trim', (array) ($topic->unknown_concepts ?? []))));
        $createdNames = [];
        $remainingUnknowns = [];

        foreach ($unknowns as $idx => $concept) {
            if (count($createdNames) >= 2) {
                $remainingUnknowns[] = $concept;

                continue;
            }
            $slug = Str::limit(Str::slug($concept, '-') ?: 'subtopic', 95, '').'-p'.$topic->id;
            if (AiResearchTopic::query()->where('slug', $slug)->exists()) {
                continue;
            }

            AiResearchTopic::query()->create([
                'goal_id' => $goal->id,
                'parent_id' => $topic->id,
                'slug' => $slug,
                'title' => Str::limit(ucfirst($concept), 185, ''),
                'domain' => $topic->domain,
                'category' => $topic->category,
                'priority' => max(4, (int) $topic->priority - 1),
                'learning_stage' => 1,
                'learning_objectives' => ["Nghiên cứu chuyên sâu về {$concept} trong ngữ cảnh {$goal->title}"],
                'prerequisites' => [$topic->slug],
                'search_queries' => [$concept.' Việt Nam', $concept.' '.$topic->title],
                'status' => AiResearchTopic::STATUS_PENDING,
                'review_state' => 'unreviewed',
                'next_research_at' => now(),
            ]);
            $createdNames[] = ucfirst($concept);
        }

        $topic->update([
            'unknown_concepts' => $remainingUnknowns,
            'learning_stage' => min(7, max(2, (int) $topic->learning_stage + 1)),
            'status' => AiResearchTopic::STATUS_SCHEDULED,
            'next_research_at' => now()->addDays(7),
        ]);

        return ['created' => count($createdNames), 'topics' => $createdNames];
    }

    /**
     * Cập nhật khách quan tiến độ & cấp độ (L1 -> L7) dựa trên nguồn và bằng chứng thực tế.
     */
    public function refreshTopicProgress(AiResearchTopic $topic): void
    {
        $fetchedSources = AiResearchSource::query()
            ->where('topic_id', $topic->id)
            ->whereIn('status', [AiResearchSource::STATUS_FETCHED, AiResearchSource::STATUS_EXTRACTED])
            ->count();

        $verifiedClaims = AiResearchClaim::query()
            ->where('topic_id', $topic->id)
            ->whereIn('verification_status', [AiResearchClaim::STATUS_VERIFIED, AiResearchClaim::STATUS_PROMOTED])
            ->count();

        $distinctDomains = AiResearchSource::query()
            ->where('topic_id', $topic->id)
            ->whereIn('status', [AiResearchSource::STATUS_FETCHED, AiResearchSource::STATUS_EXTRACTED])
            ->distinct('domain')
            ->count('domain');

        $stage = 1;
        if ($fetchedSources >= 1 && $verifiedClaims >= 1) {
            $stage = 2;
        }
        if ($distinctDomains >= 2 && $verifiedClaims >= 3) {
            $stage = 3;
        }
        if ($distinctDomains >= 3 && $verifiedClaims >= 5) {
            $stage = 4;
        }
        if ($distinctDomains >= 3 && $verifiedClaims >= 7) {
            $stage = 5;
        }
        if ($distinctDomains >= 4 && $verifiedClaims >= 10) {
            $stage = 6;
        }

        $qualityScore = min(100, ($fetchedSources * 15) + ($verifiedClaims * 10) + ($distinctDomains * 10));
        $recheckDays = (int) config('ai_brain.research.recheck_days', 14);
        $goalDepth = (int) ($topic->goal?->max_depth ?? 7);

        $topic->update([
            'source_coverage' => $fetchedSources,
            'evidence_count' => $verifiedClaims,
            'quality_score' => $qualityScore,
            'learning_stage' => min($goalDepth, max((int) $topic->learning_stage, $stage)),
            'review_state' => $verifiedClaims > 0 ? 'verified' : 'unreviewed',
            'last_researched_at' => now(),
            'status' => $stage >= $goalDepth ? AiResearchTopic::STATUS_COMPLETED : AiResearchTopic::STATUS_SCHEDULED,
            'next_research_at' => $fetchedSources >= 3 ? now()->addDays($recheckDays) : now()->addHours(12),
        ]);
    }

    /**
     * Phân rã mục tiêu Admin nhập thành 4 chủ đề con có cấu trúc (dùng Planner AI nếu sẵn sàng, fallback sạch bằng code).
     *
     * @return list<array{title: string, objectives: list<string>, queries: list<string>, unknown_concepts: list<string>}>
     */
    protected function decomposeGoalIntoSubtopics(AiResearchGoal $goal): array
    {
        $policy = $this->governance->getPolicy();
        $budget = $this->governance->hasRemainingLearningBudget();

        if (! $policy['kill_switch'] && $budget['allowed'] && trim((string) $policy['draft_model']) !== '') {
            $sys = 'Bạn là Curriculum Planner AI. Hãy phân rã mục tiêu học thành 4 chủ đề con nghiên cứu từ nền tảng đến ứng dụng bằng tiếng Việt. CHỈ TRẢ VỀ JSON: {"topics":[{"title":"...","objectives":["..."],"queries":["truy vấn tiếng Việt 1","truy vấn 2"],"unknown_concepts":["khái niệm mở rộng"]}]}';
            $user = "Mục tiêu: {$goal->title}\nLĩnh vực: {$goal->domain}\nMô tả: {$goal->description}\nQuốc gia: {$goal->country}, Ngôn ngữ: {$goal->language}";

            $res = $this->providerClient->callJson($sys, $user, $policy['draft_model'], 0.25, 900, 10);
            if ($res['ok'] && ! empty($res['data']['topics']) && is_array($res['data']['topics'])) {
                $valid = array_values(array_filter($res['data']['topics'], fn ($x) => is_array($x) && ! empty($x['title'])));
                if ($valid !== []) {
                    return array_slice($valid, 0, 5);
                }
            }
        }

        // Deterministic fallback phân rã có cấu trúc theo 4 trụ cột khi chưa gọi LLM
        $t = $goal->title;
        $ctx = strtoupper((string) $goal->country) === 'VN' ? ' tại Việt Nam' : '';

        return [
            [
                'title' => "Khái niệm nền tảng và thuật ngữ cốt lõi về {$t}",
                'objectives' => ["Hiểu khái niệm cơ bản và phạm vi của {$t}"],
                'queries' => ["{$t} là gì{$ctx}", "tổng quan {$t}{$ctx}"],
                'unknown_concepts' => ["phân loại {$t}"],
            ],
            [
                'title' => "Quy trình thực hành và phương pháp chuẩn cho {$t}",
                'objectives' => ["Nắm các bước triển khai và quy chuẩn phổ biến về {$t}"],
                'queries' => ["quy trình {$t}{$ctx}", "hướng dẫn {$t} chi tiết"],
                'unknown_concepts' => ["lỗi thường gặp khi làm {$t}"],
            ],
            [
                'title' => "Xu hướng thị trường và kinh nghiệm thực tế về {$t}",
                'objectives' => ["Tổng hợp xu hướng mới và nhu cầu khách hàng xoay quanh {$t}"],
                'queries' => ["xu hướng {$t}{$ctx}", "kinh nghiệm {$t} thực tế"],
                'unknown_concepts' => ["tối ưu trải nghiệm người dùng {$t}"],
            ],
            [
                'title' => "Ứng dụng và cải tiến dịch vụ liên quan đến {$t}",
                'objectives' => ['Đánh giá các ý tưởng áp dụng hợp lý cho sản phẩm dịch vụ'],
                'queries' => ["giải pháp công nghệ {$t}{$ctx}", "cải thiện hiệu quả {$t}"],
                'unknown_concepts' => ["đo lường hiệu quả {$t}"],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    protected function parseDomainList(mixed $raw): array
    {
        if (is_array($raw)) {
            $items = $raw;
        } else {
            $items = preg_split('/[\s,;]+/', trim((string) $raw)) ?: [];
        }

        return array_values(array_unique(array_filter(array_map(
            fn ($v) => strtolower(trim((string) $v)),
            $items
        ))));
    }
}
