<?php

declare(strict_types=1);

namespace App\Services\Intelligence;

use App\Models\AiKnowledgeEntry;
use App\Models\AiKnowledgeGap;
use App\Models\AiKnowledgeRelation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Progressive Domain Coverage (L0 -> L4) & Relational Knowledge Graph Service.
 * Lưu mạng quan hệ thực tế giữa các module của Mây Hẹn trên Relational DB hiện tại (0 phụ thuộc GraphDB ngoài).
 */
class KnowledgeGraphService
{
    public const CACHE_KEY = 'mayhen_ai_knowledge_relations_v1';

    /**
     * Đồng bộ các quan hệ cốt lõi ĐÃ XÁC MINH từ mã nguồn thật của Mây Hẹn (idempotent, 0 trùng lặp).
     */
    public function syncVerifiedCoreRelations(?int $userId = null): int
    {
        if (! Schema::hasTable('ai_knowledge_relations')) {
            return 0;
        }

        $coreEdges = [
            [
                'from_type' => 'module', 'from_key' => 'invitation', 'from_label' => 'Thiệp cưới điện tử',
                'relation_type' => 'uses',
                'to_type' => 'module', 'to_key' => 'template', 'to_label' => 'Kho mẫu thiệp Quiet Luxury',
                'description' => 'Thiệp cưới hỗ trợ đổi mẫu thiệp 1 chạm trong Studio Tạo Thiệp mà bảo toàn 100% thông tin cô dâu chú rể, ngày giờ và ảnh đã nhập.',
                'evidence_refs' => ['code:InvitationEditorController', 'doc:/docs/getting-started'],
            ],
            [
                'from_type' => 'module', 'from_key' => 'invitation', 'from_label' => 'Thiệp cưới điện tử',
                'relation_type' => 'manages',
                'to_type' => 'module', 'to_key' => 'guest', 'to_label' => 'Quản lý khách mời cá nhân hóa',
                'description' => 'Mỗi thiệp cưới tạo được danh sách khách mời riêng kèm đường dẫn cá nhân hóa gửi nhanh qua Zalo hoặc Messenger.',
                'evidence_refs' => ['table:guests', 'code:GuestController'],
            ],
            [
                'from_type' => 'module', 'from_key' => 'guest', 'from_label' => 'Khách mời',
                'relation_type' => 'submits',
                'to_type' => 'module', 'to_key' => 'rsvp', 'to_label' => 'Xác nhận tham dự (RSVP)',
                'description' => 'Khách mời phản hồi tham dự trực tiếp trên thiệp (số người đi cùng, yêu cầu thực đơn, xe đưa đón) và tự động đồng bộ về trang quản lý.',
                'evidence_refs' => ['table:guest_responses', 'table:guest_response_logs'],
            ],
            [
                'from_type' => 'module', 'from_key' => 'guest', 'from_label' => 'Khách mời',
                'relation_type' => 'assigned_to',
                'to_type' => 'module', 'to_key' => 'wedding_table', 'to_label' => 'Sắp xếp bàn tiệc',
                'description' => 'Khách đã xác nhận RSVP có thể được phân bổ trực tiếp vào từng bàn tiệc theo nhóm gia đình/bạn bè để kiểm soát chỗ ngồi.',
                'evidence_refs' => ['table:wedding_tables', 'column:guests.table_id'],
            ],
            [
                'from_type' => 'module', 'from_key' => 'guest', 'from_label' => 'Khách mời',
                'relation_type' => 'checked_in_via',
                'to_type' => 'module', 'to_key' => 'qr_checkin', 'to_label' => 'Điểm danh QR Check-in tại sảnh',
                'description' => 'Mỗi khách mời có mã QR riêng giúp lễ tân quét điểm danh nhanh tại tiệc cưới và đối chiếu ngay số bàn tiệc.',
                'evidence_refs' => ['column:guests.checkin_status', 'migration:add_table_and_checkin_fields_to_guests_table'],
            ],
            [
                'from_type' => 'module', 'from_key' => 'invitation', 'from_label' => 'Thiệp cưới điện tử',
                'relation_type' => 'includes',
                'to_type' => 'module', 'to_key' => 'gallery', 'to_label' => 'Album ảnh cưới & Photo Drop',
                'description' => 'Hỗ trợ hiển thị bộ ảnh cưới chuẩn 10 ảnh (lưu tốc độ cao qua Cloudflare R2) và thu thập ảnh khách chụp tại tiệc qua Photo Drop.',
                'evidence_refs' => ['table:guest_photos', 'service:WeddingGalleryService'],
            ],
            [
                'from_type' => 'module', 'from_key' => 'invitation', 'from_label' => 'Thiệp cưới điện tử',
                'relation_type' => 'includes',
                'to_type' => 'module', 'to_key' => 'timeline_location', 'to_label' => 'Lịch trình sự kiện & Bản đồ chỉ đường',
                'description' => 'Hiển thị mốc thời gian lễ gia tiên/tiệc cưới rõ ràng kèm chỉ đường bản đồ trực tiếp trên điện thoại.',
                'evidence_refs' => ['table:wedding_events', 'standards:10_sections'],
            ],
            [
                'from_type' => 'module', 'from_key' => 'invitation', 'from_label' => 'Thiệp cưới điện tử',
                'relation_type' => 'includes',
                'to_type' => 'module', 'to_key' => 'time_capsule', 'to_label' => 'Hộp thời gian & Sổ ký tên lời chúc',
                'description' => 'Khách gửi lời chúc, ký tên trực tiếp trên thiệp hoặc gửi thư vào Hộp thời gian niêm phong hẹn tự động mở khóa vào kỷ niệm 1 năm ngày cưới.',
                'evidence_refs' => ['table:wedding_time_capsules', 'table:wedding_wishes'],
            ],
            [
                'from_type' => 'module', 'from_key' => 'invitation', 'from_label' => 'Thiệp cưới điện tử',
                'relation_type' => 'includes',
                'to_type' => 'module', 'to_key' => 'gift_registry', 'to_label' => 'Quà tặng & Mừng cưới tiện lợi',
                'description' => 'Tích hợp mục quà tặng và mừng cưới lịch sự cho khách mời ở xa không thể trực tiếp tới dự tiệc.',
                'evidence_refs' => ['table:wedding_gifts', 'service:WeddingGiftRegistryService'],
            ],
            [
                'from_type' => 'module', 'from_key' => 'plan', 'from_label' => 'Gói dịch vụ Mây Hẹn',
                'relation_type' => 'paid_via',
                'to_type' => 'module', 'to_key' => 'vietqr_sepay', 'to_label' => 'Thanh toán chuyển khoản VietQR tự động',
                'description' => 'Nâng cấp gói dịch vụ được đối soát tự động qua chuyển khoản VietQR/SePay để kích hoạt quyền lợi nhanh chóng tại trang Bảng Giá.',
                'evidence_refs' => ['table:plans', 'table:orders', 'config:services.sepay'],
            ],
            [
                'from_type' => 'module', 'from_key' => 'invitation', 'from_label' => 'Thiệp cưới Mây Hẹn',
                'relation_type' => 'prints_with',
                'to_type' => 'module', 'to_key' => 'print_service', 'to_label' => 'Dịch vụ in thiệp cưới giấy mỹ thuật cao cấp',
                'description' => 'Dịch vụ in phôi giấy mỹ thuật Ý 300-450gsm, ép kim nhũ nhiệt, dập nổi 3D, tem sáp niêm phong thủ công và tặng kèm thiệp cưới online đồng bộ tại /dich-vu.',
                'evidence_refs' => ['route:services', 'view:client.pages.services'],
            ],
            [
                'from_type' => 'module', 'from_key' => 'plan_pro', 'from_label' => 'Gói Trọn Vẹn (Wedding Pro)',
                'relation_type' => 'unlocks',
                'to_type' => 'module', 'to_key' => 'event_invitation', 'to_label' => 'Thiệp Sự Kiện Gia Đình & Mùa Lễ Hội',
                'description' => 'Gói Pro VIP (199.000đ) mở khóa đặc quyền xuất bản trọn đời không giới hạn đường dẫn chính thức /loi-moi/{slug} cho 10 danh mục thiệp sự kiện phong cách Digital Stationery (Paperless Post phiên bản Việt).',
                'evidence_refs' => ['route:event_invitations.index', 'table:event_invitation_templates', 'doc:/docs/bat-dau-nhanh/thiep-su-kien-va-mua-le-hoi-pro'],
            ],
            [
                'from_type' => 'module', 'from_key' => 'invitation', 'from_label' => 'Thiệp cưới điện tử',
                'relation_type' => 'displays_on',
                'to_type' => 'module', 'to_key' => 'live_wall', 'to_label' => 'Bức tường LED sảnh tiệc (Live Projection Wall)',
                'description' => 'Trình chiếu ảnh cưới và lời chúc quan khách trực tiếp lên màn hình LED sảnh cưới với giao diện Quiet Luxury có kiểm duyệt tại /trinh-chieu/{slug}.',
                'evidence_refs' => ['route:live_wall.show', 'table:wedding_wishes'],
            ],
            [
                'from_type' => 'module', 'from_key' => 'guest', 'from_label' => 'Khách mời',
                'relation_type' => 'uploads_to',
                'to_type' => 'module', 'to_key' => 'photo_drop', 'to_label' => 'Live Guest Photo Drop tại bàn tiệc',
                'description' => 'Khách quét mã QR tại bàn tiệc để tải ngay ảnh khoảnh khắc tự nhiên lên thiệp (/photo-drop/{slug}), cặp đôi duyệt và tải về trọn bộ file ZIP.',
                'evidence_refs' => ['route:photo_drop.show', 'table:guest_photos'],
            ],
            [
                'from_type' => 'module', 'from_key' => 'wedding_table', 'from_label' => 'Sơ đồ bàn tiệc',
                'relation_type' => 'navigates_via',
                'to_type' => 'module', 'to_key' => 'table_finder', 'to_label' => 'Tra cứu vị trí bàn tiệc 1 chạm',
                'description' => 'Khách dự tiệc tra cứu nhanh số bàn và sơ đồ vị trí ngồi của mình chỉ bằng 1 chạm qua link /tra-cuu-ban-tiec hoặc quét QR thiệp.',
                'evidence_refs' => ['route:invitation.table_finder.search', 'table:wedding_tables'],
            ],
            [
                'from_type' => 'module', 'from_key' => 'partner', 'from_label' => 'Đối tác Studio & Wedding Planner',
                'relation_type' => 'collaborates_with',
                'to_type' => 'module', 'to_key' => 'studio_portal', 'to_label' => 'Cổng Đối Tác Studio B2B',
                'description' => 'Chương trình B2B cho Studio (/doi-tac/dang-ky): tạo thiệp hàng loạt, co-branding logo Studio, bàn giao bằng Handover Code và nhận hoa hồng chiết khấu.',
                'evidence_refs' => ['route:partner.register', 'route:partner.dashboard'],
            ],
        ];

        $synced = 0;
        foreach ($coreEdges as $edge) {
            $edgeHash = AiKnowledgeRelation::computeEdgeHash(
                $edge['from_type'],
                $edge['from_key'],
                $edge['relation_type'],
                $edge['to_type'],
                $edge['to_key']
            );

            AiKnowledgeRelation::query()->updateOrCreate(
                ['edge_hash' => $edgeHash],
                [
                    'from_type' => $edge['from_type'],
                    'from_key' => $edge['from_key'],
                    'relation_type' => $edge['relation_type'],
                    'to_type' => $edge['to_type'],
                    'to_key' => $edge['to_key'],
                    'from_label' => $edge['from_label'],
                    'to_label' => $edge['to_label'],
                    'description' => $edge['description'],
                    'domain_level' => 2,
                    'evidence_refs' => $edge['evidence_refs'],
                    'source_type' => 'verified_internal',
                    'status' => AiKnowledgeRelation::STATUS_APPROVED,
                    'is_active' => true,
                    'confidence_score' => 100,
                    'created_by_user_id' => $userId,
                ]
            );
            $synced++;
        }

        AiKnowledgeRelation::clearRelationCache();

        return $synced;
    }

    /**
     * Tra cứu ngữ cảnh quan hệ liên kết (0 DB query khi cache ấm) để AI hiểu luồng tính năng tổng thể.
     *
     * @return list<array{id: int, edge: string, description: string, evidence_refs: list<string>}>
     */
    public function findRelatedContext(string $normalizedQuery, string $intent): array
    {
        if (! Schema::hasTable('ai_knowledge_relations')) {
            return [];
        }

        $relations = Cache::remember(self::CACHE_KEY, 600, function (): array {
            return AiKnowledgeRelation::query()
                ->activeApproved()
                ->where('relation_type', '!=', 'suggests_improvement')
                ->orderByDesc('confidence_score')
                ->get(['id', 'from_key', 'from_label', 'relation_type', 'to_key', 'to_label', 'description', 'evidence_refs'])
                ->map(fn (AiKnowledgeRelation $r): array => [
                    'id' => (int) $r->id,
                    'from_key' => (string) $r->from_key,
                    'from_label' => (string) $r->from_label,
                    'relation_type' => (string) $r->relation_type,
                    'to_key' => (string) $r->to_key,
                    'to_label' => (string) $r->to_label,
                    'description' => (string) ($r->description ?? ''),
                    'evidence_refs' => array_values(array_map('strval', (array) ($r->evidence_refs ?? []))),
                    'search_blob' => Str::ascii(mb_strtolower(
                        $r->from_label.' '.$r->to_label.' '.$r->from_key.' '.$r->to_key.' '.($r->description ?? ''),
                        'UTF-8'
                    )),
                ])
                ->all();
        });

        if (! is_array($relations) || $relations === []) {
            return [];
        }

        $intentKeywords = match ($intent) {
            'templates' => ['template', 'mau thiep', 'invitation'],
            'pricing' => ['plan', 'vietqr', 'goi dich vu', 'thanh toan'],
            'docs' => ['rsvp', 'guest', 'qr', 'table', 'gallery', 'time_capsule'],
            default => [],
        };

        $tokens = array_values(array_filter(
            explode(' ', $normalizedQuery),
            fn (string $t) => strlen($t) >= 2
        ));

        $scored = [];
        foreach ($relations as $rel) {
            $blob = (string) ($rel['search_blob'] ?? '');
            $score = 0;

            foreach ($tokens as $tk) {
                if (str_contains($blob, $tk)) {
                    $score += 6;
                }
            }
            foreach ($intentKeywords as $ik) {
                if (str_contains($blob, $ik)) {
                    $score += 4;
                }
            }

            if ($score >= 6) {
                $scored[] = [
                    'id' => (int) $rel['id'],
                    'relation_type' => (string) $rel['relation_type'],
                    'edge' => "{$rel['from_label']} --[{$rel['relation_type']}]--> {$rel['to_label']}",
                    'description' => (string) $rel['description'],
                    'evidence_refs' => (array) $rel['evidence_refs'],
                    'score' => $score,
                ];
            }
        }

        usort($scored, fn (array $a, array $b) => $b['score'] <=> $a['score']);

        return array_values(array_map(
            fn (array $item): array => [
                'id' => $item['id'],
                'edge' => $item['edge'],
                'description' => $item['description'],
                'evidence_refs' => $item['evidence_refs'],
            ],
            array_slice($scored, 0, 3)
        ));
    }

    /**
     * Tính mức độ bao phủ tri thức thực tế theo Progressive Domain Learning (Level 0 → Level 4).
     *
     * @return array{
     *     current_unlocked_level: int,
     *     levels: list<array{level: int, code: string, name: string, approved_count: int, pending_count: int, relations_count: int, open_gaps: int, unlocked: bool, ready: bool}>
     * }
     */
    public function getDomainCoverage(): array
    {
        $defs = (array) config('ai_brain.domain_levels', []);
        $hasLevelCol = Schema::hasTable('ai_knowledge_entries') && Schema::hasColumn('ai_knowledge_entries', 'domain_level');
        $hasRelTable = Schema::hasTable('ai_knowledge_relations');
        $hasGapTable = Schema::hasTable('ai_knowledge_gaps');

        $approvedByLevel = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0];
        $pendingByLevel = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0];

        if ($hasLevelCol) {
            foreach (AiKnowledgeEntry::query()->liveApproved()->selectRaw('domain_level, COUNT(*) as c')->groupBy('domain_level')->get() as $row) {
                $lvl = max(0, min(4, (int) $row->domain_level));
                $approvedByLevel[$lvl] = (int) $row->c;
            }
            foreach (AiKnowledgeEntry::query()->where('status', AiKnowledgeEntry::STATUS_PENDING)->selectRaw('domain_level, COUNT(*) as c')->groupBy('domain_level')->get() as $row) {
                $lvl = max(0, min(4, (int) $row->domain_level));
                $pendingByLevel[$lvl] = (int) $row->c;
            }
        } elseif (Schema::hasTable('ai_knowledge_entries')) {
            $approvedByLevel[0] = AiKnowledgeEntry::query()->liveApproved()->count();
            $pendingByLevel[0] = AiKnowledgeEntry::query()->where('status', AiKnowledgeEntry::STATUS_PENDING)->count();
        }

        $relationsCount = $hasRelTable ? AiKnowledgeRelation::query()->activeApproved()->count() : 0;
        $openGaps = $hasGapTable ? AiKnowledgeGap::query()->where('status', AiKnowledgeGap::STATUS_OPEN)->count() : 0;

        $l0Ready = ($approvedByLevel[0] + $approvedByLevel[1]) >= (int) ($defs[0]['min_approved_to_advance'] ?? 2);
        $l1Ready = $l0Ready && ($approvedByLevel[0] + $approvedByLevel[1]) >= (int) ($defs[1]['min_approved_to_advance'] ?? 4);
        $l2Ready = $l1Ready && $relationsCount >= (int) ($defs[2]['min_relations_to_advance'] ?? 5);
        $l3Unlocked = $l2Ready;
        $l4Unlocked = $l2Ready && $openGaps > 0;

        $unlockedLevel = 0;
        if ($l0Ready) {
            $unlockedLevel = 1;
        }
        if ($l1Ready) {
            $unlockedLevel = 2;
        }
        if ($l2Ready) {
            $unlockedLevel = 3;
        }
        if ($l4Unlocked) {
            $unlockedLevel = 4;
        }

        $levels = [];
        for ($i = 0; $i <= 4; $i++) {
            $d = (array) ($defs[$i] ?? []);
            $levels[] = [
                'level' => $i,
                'code' => (string) ($d['code'] ?? 'L'.$i),
                'name' => (string) ($d['name'] ?? 'Level '.$i),
                'approved_count' => $approvedByLevel[$i],
                'pending_count' => $pendingByLevel[$i],
                'relations_count' => $i === 2 ? $relationsCount : 0,
                'open_gaps' => $i >= 3 ? $openGaps : 0,
                'unlocked' => $i <= $unlockedLevel,
                'ready' => match ($i) {
                    0 => $l0Ready,
                    1 => $l1Ready,
                    2 => $l2Ready,
                    3 => $l3Unlocked,
                    default => $l4Unlocked,
                },
            ];
        }

        return [
            'current_unlocked_level' => $unlockedLevel,
            'levels' => $levels,
        ];
    }
}
