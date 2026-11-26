<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\BaziChart;
use App\Models\CalendarFamilyEvent;
use App\Models\CoupleCompatibilityReport;
use App\Models\FamilyTree;
use App\Models\IchingReading;
use App\Models\NumerologyReading;
use App\Models\TarotReading;
use App\Models\ToolRetentionArchive;
use App\Models\TuViChart;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Models\UserCookieConsent;
use App\Models\WeddingProject;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Trung Tâm Quản Trị Toàn Bộ Hệ Sinh Thái Công Cụ, Theo Dõi Hành Vi & Lưu Trữ 6 Tháng (Mây Hẹn Admin).
 *
 * - Bao phủ 100% 9 phân hệ công cụ: Gia Phả, Gieo Quẻ, Tử Vi, Thần Số Học, Tarot, Lịch Âm Gia Đình, Bát Tự, Cao Ly / Xem Tuổi, Xem Ngày Cưới & Tổ Chức Đám Cưới.
 * - Chính sách lưu trữ 6 tháng (180 ngày): Cron Job tự động nén thông tin phân tích vào `tool_retention_archives` rồi xóa dữ liệu chi tiết cũ để tối ưu lưu trữ và tuân thủ Luật BVDLCN 91/2025/QH15.
 */
class ToolsGovernanceService
{
    public const DEFAULT_RETENTION_MONTHS = 6;

    /**
     * Ánh xạ URL slug quản trị độc lập trong /admin/{slug} -> tool_key.
     *
     * @var array<string, string>
     */
    public const MODULE_URL_MAP = [
        'family-tree' => 'family_tree',
        'iching' => 'iching',
        'tuvi' => 'tuvi',
        'numerology' => 'numerology',
        'tarot' => 'tarot',
        'calendar' => 'calendar',
        'bazi' => 'bazi',
        'marriage-compatibility' => 'marriage_compatibility',
        'wedding-journey' => 'wedding_journey',
    ];

    /**
     * Single Source of Truth cho toàn bộ 9 phân hệ công cụ trên Mây Hẹn.
     *
     * @var array<string, array<string, mixed>>
     */
    public const TOOL_REGISTRY = [
        'wedding_journey' => [
            'label' => 'Tổ Chức Đám Cưới & Ngày Cưới',
            'short' => 'Đám Cưới',
            'table' => 'wedding_projects',
            'model' => WeddingProject::class,
            'title_col' => 'title',
            'slug_col' => 'slug',
            'share_col' => null,
            'public_url' => '/to-chuc-dam-cuoi',
            'accent' => '#85182A',
        ],
        'marriage_compatibility' => [
            'label' => 'Cao Ly & Xem Tuổi Vợ Chồng',
            'short' => 'Duyên Hợp',
            'table' => 'couple_compatibility_reports',
            'model' => CoupleCompatibilityReport::class,
            'title_col' => 'title',
            'slug_col' => 'slug',
            'share_col' => 'share_token',
            'public_url' => '/xem-tuoi-vo-chong',
            'accent' => '#9A3412',
        ],
        'bazi' => [
            'label' => 'Tra Cứu Bát Tự (Tứ Trụ)',
            'short' => 'Bát Tự',
            'table' => 'bazi_charts',
            'model' => BaziChart::class,
            'title_col' => 'subject_name',
            'slug_col' => 'slug',
            'share_col' => 'share_token',
            'public_url' => '/bat-tu',
            'accent' => '#1E4D3B',
        ],
        'calendar' => [
            'label' => 'Lịch Âm & Sự Kiện Gia Đình',
            'short' => 'Lịch Âm',
            'table' => 'calendar_family_events',
            'model' => CalendarFamilyEvent::class,
            'title_col' => 'title',
            'slug_col' => null,
            'share_col' => null,
            'public_url' => '/lich-am',
            'accent' => '#0F766E',
        ],
        'tarot' => [
            'label' => 'Xem Tarot (78 Lá RWS)',
            'short' => 'Tarot',
            'table' => 'tarot_readings',
            'model' => TarotReading::class,
            'title_col' => 'question',
            'slug_col' => 'slug',
            'share_col' => 'share_token',
            'public_url' => '/tarot',
            'accent' => '#6D28D9',
        ],
        'numerology' => [
            'label' => 'Thần Số Học (Pythagoras)',
            'short' => 'Thần Số',
            'table' => 'numerology_readings',
            'model' => NumerologyReading::class,
            'title_col' => 'full_name',
            'slug_col' => 'slug',
            'share_col' => 'share_token',
            'public_url' => '/than-so-hoc',
            'accent' => '#1D4ED8',
        ],
        'tuvi' => [
            'label' => 'Tử Vi Đẩu Số (Thiên Mệnh)',
            'short' => 'Tử Vi',
            'table' => 'tuvi_charts',
            'model' => TuViChart::class,
            'title_col' => 'subject_name',
            'slug_col' => 'slug',
            'share_col' => 'share_token',
            'public_url' => '/tu-vi',
            'accent' => '#B45309',
        ],
        'iching' => [
            'label' => 'Gieo Quẻ Kinh Dịch',
            'short' => 'Kinh Dịch',
            'table' => 'iching_readings',
            'model' => IchingReading::class,
            'title_col' => 'question',
            'slug_col' => 'slug',
            'share_col' => 'share_token',
            'public_url' => '/gieo-que',
            'accent' => '#334155',
        ],
        'family_tree' => [
            'label' => 'Dựng Gia Phả Dòng Họ',
            'short' => 'Gia Phả',
            'table' => 'family_trees',
            'model' => FamilyTree::class,
            'title_col' => 'name',
            'slug_col' => 'slug',
            'share_col' => 'share_token',
            'public_url' => '/gia-pha',
            'accent' => '#15803D',
        ],
    ];

    /**
     * Tổng hợp KPI toàn bộ 9 công cụ + Telemetry + Cookie Consent + Trạng thái Lưu trữ 6 tháng.
     *
     * @return array<string, mixed>
     */
    public function getOverviewStats(int $retentionMonths = self::DEFAULT_RETENTION_MONTHS): array
    {
        $cutoff = Carbon::now('Asia/Ho_Chi_Minh')->subMonths($retentionMonths);
        $expiringSoonCutoff = Carbon::now('Asia/Ho_Chi_Minh')->subMonths(max(1, $retentionMonths - 1));
        $last7Days = Carbon::now('Asia/Ho_Chi_Minh')->subDays(7);

        $perTool = [];
        $totalActive = 0;
        $totalLast7Days = 0;
        $totalShared = 0;
        $totalExpired = 0;
        $totalExpiringSoon = 0;

        foreach (self::TOOL_REGISTRY as $key => $cfg) {
            $table = $cfg['table'];
            if (! Schema::hasTable($table)) {
                $perTool[$key] = array_merge($cfg, [
                    'key' => $key,
                    'total' => 0,
                    'last_7_days' => 0,
                    'shared' => 0,
                    'expired' => 0,
                    'expiring_soon' => 0,
                    'exists' => false,
                ]);

                continue;
            }

            $total = DB::table($table)->count();
            $recent = DB::table($table)->where('created_at', '>=', $last7Days)->count();
            $expired = DB::table($table)->where('created_at', '<', $cutoff)->count();
            $expSoon = DB::table($table)
                ->where('created_at', '>=', $cutoff)
                ->where('created_at', '<', $expiringSoonCutoff)
                ->count();

            $shared = 0;
            if ($cfg['share_col'] && Schema::hasColumn($table, $cfg['share_col'])) {
                $shared = DB::table($table)->whereNotNull($cfg['share_col'])->where($cfg['share_col'], '!=', '')->count();
            }

            $totalActive += $total;
            $totalLast7Days += $recent;
            $totalShared += $shared;
            $totalExpired += $expired;
            $totalExpiringSoon += $expSoon;

            $perTool[$key] = array_merge($cfg, [
                'key' => $key,
                'total' => $total,
                'last_7_days' => $recent,
                'shared' => $shared,
                'expired' => $expired,
                'expiring_soon' => $expSoon,
                'exists' => true,
            ]);
        }

        $archivedCount = Schema::hasTable('tool_retention_archives')
            ? ToolRetentionArchive::query()->count()
            : 0;
        $activity24h = Schema::hasTable('user_activity_logs')
            ? UserActivityLog::query()->where('created_at', '>=', now()->subDay())->count()
            : 0;
        $activityTotal = Schema::hasTable('user_activity_logs')
            ? UserActivityLog::query()->count()
            : 0;
        $consentTotal = Schema::hasTable('user_cookie_consents')
            ? UserCookieConsent::query()->count()
            : 0;
        $consentAnalyticsAccepted = Schema::hasTable('user_cookie_consents')
            ? UserCookieConsent::query()->where('consent_analytics', true)->count()
            : 0;

        return [
            'retention_months' => $retentionMonths,
            'cutoff_date' => $cutoff->toDateTimeString(),
            'totals' => [
                'active_records' => $totalActive,
                'last_7_days' => $totalLast7Days,
                'shared_links' => $totalShared,
                'expired_ready_to_prune' => $totalExpired,
                'expiring_within_30_days' => $totalExpiringSoon,
                'archived_analytics' => $archivedCount,
                'activity_24h' => $activity24h,
                'activity_total' => $activityTotal,
                'consent_total' => $consentTotal,
                'consent_analytics_rate' => $consentTotal > 0 ? round(($consentAnalyticsAccepted / $consentTotal) * 100, 1) : 0.0,
            ],
            'per_tool' => $perTool,
        ];
    }

    /**
     * Liệt kê danh sách bản ghi người dùng đã tạo trong 1 công cụ cụ thể (có tìm kiếm, lọc user, tính ngày còn lại trước mốc 6 tháng).
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function listToolRecords(string $toolKey, array $filters = [], int $perPage = 25): array
    {
        if (! isset(self::TOOL_REGISTRY[$toolKey])) {
            $toolKey = 'wedding_journey';
        }
        $cfg = self::TOOL_REGISTRY[$toolKey];
        $table = $cfg['table'];
        if (! Schema::hasTable($table)) {
            return ['tool' => $cfg, 'key' => $toolKey, 'rows' => [], 'total' => 0];
        }

        $query = DB::table($table)->orderByDesc('id');

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $query->where(function ($q) use ($cfg, $table, $search) {
                if ($cfg['title_col'] && Schema::hasColumn($table, $cfg['title_col'])) {
                    $q->orWhere($cfg['title_col'], 'like', '%'.$search.'%');
                }
                if ($cfg['slug_col'] && Schema::hasColumn($table, $cfg['slug_col'])) {
                    $q->orWhere($cfg['slug_col'], 'like', '%'.$search.'%');
                }
                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                    if (Schema::hasColumn($table, 'user_id')) {
                        $q->orWhere('user_id', (int) $search);
                    }
                }
            });
        }

        if (! empty($filters['user_id']) && Schema::hasColumn($table, 'user_id')) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (($filters['expired_only'] ?? false) === true) {
            $query->where('created_at', '<', Carbon::now('Asia/Ho_Chi_Minh')->subMonths(self::DEFAULT_RETENTION_MONTHS));
        }

        $paginator = $query->paginate($perPage);
        $userIds = collect($paginator->items())->pluck('user_id')->filter()->unique()->values()->all();
        $usersMap = $userIds !== []
            ? User::query()->whereIn('id', $userIds)->get(['id', 'name', 'email', 'current_plan_slug'])->keyBy('id')
            : collect();

        $rows = [];
        foreach ($paginator->items() as $item) {
            $arr = (array) $item;
            $createdAt = ! empty($arr['created_at']) ? Carbon::parse($arr['created_at']) : null;
            $expiresAt = $createdAt ? $createdAt->copy()->addMonths(self::DEFAULT_RETENTION_MONTHS) : null;
            $u = ! empty($arr['user_id']) ? $usersMap->get((int) $arr['user_id']) : null;

            $titleCol = $cfg['title_col'];
            $slugCol = $cfg['slug_col'];
            $shareCol = $cfg['share_col'];

            $rows[] = [
                'id' => (int) $arr['id'],
                'title' => (string) ($titleCol && isset($arr[$titleCol]) && $arr[$titleCol] !== '' ? $arr[$titleCol] : "#{$arr['id']}"),
                'slug' => $slugCol && isset($arr[$slugCol]) ? (string) $arr[$slugCol] : null,
                'share_token' => $shareCol && isset($arr[$shareCol]) ? (string) $arr[$shareCol] : null,
                'user_id' => $arr['user_id'] ?? null,
                'user_name' => $u?->name ?? 'Khách / Ẩn danh',
                'user_email' => $u?->email ?? null,
                'user_plan' => $u?->current_plan_slug ?? 'free',
                'created_at' => $createdAt?->format('d/m/Y H:i'),
                'expires_at' => $expiresAt?->format('d/m/Y'),
                'days_left' => $expiresAt ? (int) now()->diffInDays($expiresAt, false) : null,
                'is_expired' => $expiresAt ? $expiresAt->isPast() : false,
            ];
        }

        return [
            'tool' => $cfg,
            'key' => $toolKey,
            'rows' => $rows,
            'paginator' => $paginator,
        ];
    }

    /**
     * Thu hồi link chia sẻ công khai của một bản ghi công cụ.
     */
    public function revokeShareLink(string $toolKey, int $id): bool
    {
        $cfg = self::TOOL_REGISTRY[$toolKey] ?? null;
        if (! $cfg || ! $cfg['share_col'] || ! Schema::hasTable($cfg['table'])) {
            return false;
        }
        $table = $cfg['table'];
        if (! Schema::hasColumn($table, $cfg['share_col'])) {
            return false;
        }

        $update = [$cfg['share_col'] => null];
        if (Schema::hasColumn($table, 'is_public_shared')) {
            $update['is_public_shared'] = false;
        }

        return DB::table($table)->where('id', $id)->update($update) > 0;
    }

    /**
     * Cron Job Lưu trữ & Dọn dẹp 6 tháng:
     * Quét toàn bộ bản ghi/link cũ hơn `$retentionMonths` tháng, trích xuất siêu dữ liệu phân tích vào `tool_retention_archives`
     * rồi xóa sạch bản ghi chi tiết (đảm bảo giải phóng dung lượng & vô hiệu hóa link cũ sau 6 tháng).
     *
     * @return array<string, mixed>
     */
    public function archiveAndPruneExpired(
        int $retentionMonths = self::DEFAULT_RETENTION_MONTHS,
        bool $dryRun = false,
        ?string $onlyTool = null
    ): array {
        $months = max(1, min(36, $retentionMonths));
        $cutoff = Carbon::now('Asia/Ho_Chi_Minh')->subMonths($months);
        $summary = [
            'dry_run' => $dryRun,
            'retention_months' => $months,
            'cutoff' => $cutoff->toIso8601String(),
            'total_candidates' => 0,
            'total_archived_and_pruned' => 0,
            'per_tool' => [],
        ];

        $targets = $onlyTool && isset(self::TOOL_REGISTRY[$onlyTool])
            ? [$onlyTool => self::TOOL_REGISTRY[$onlyTool]]
            : self::TOOL_REGISTRY;

        foreach ($targets as $key => $cfg) {
            $table = $cfg['table'];
            if (! Schema::hasTable($table)) {
                continue;
            }

            $expiredRows = DB::table($table)
                ->where('created_at', '<', $cutoff)
                ->orderBy('id')
                ->limit(500)
                ->get();

            $count = $expiredRows->count();
            $summary['total_candidates'] += $count;
            $prunedCount = 0;

            if (! $dryRun && $count > 0) {
                $userIds = $expiredRows->pluck('user_id')->filter()->unique()->values()->all();
                $usersMap = $userIds !== []
                    ? User::query()->whereIn('id', $userIds)->get(['id', 'email', 'current_plan_slug'])->keyBy('id')
                    : collect();

                DB::transaction(function () use ($expiredRows, $key, $cfg, $table, $months, $usersMap, &$prunedCount): void {
                    $idsToDelete = [];
                    foreach ($expiredRows as $row) {
                        $arr = (array) $row;
                        $u = ! empty($arr['user_id']) ? $usersMap->get((int) $arr['user_id']) : null;
                        $createdAt = ! empty($arr['created_at']) ? Carbon::parse($arr['created_at']) : now();
                        $titleCol = $cfg['title_col'];
                        $slugCol = $cfg['slug_col'];
                        $shareCol = $cfg['share_col'];

                        ToolRetentionArchive::create([
                            'tool_key' => $key,
                            'source_table' => $table,
                            'source_record_id' => (int) $arr['id'],
                            'user_id' => $arr['user_id'] ?? null,
                            'user_email' => $u?->email,
                            'user_plan_tier' => $u?->current_plan_slug ?? 'free',
                            'resource_slug' => $slugCol && isset($arr[$slugCol]) ? (string) $arr[$slugCol] : null,
                            'title' => mb_substr((string) ($titleCol && isset($arr[$titleCol]) ? $arr[$titleCol] : "#{$arr['id']}"), 0, 220),
                            'created_originally_at' => $createdAt,
                            'expired_at' => $createdAt->copy()->addMonths($months),
                            'archived_at' => now(),
                            'analytics_summary_json' => [
                                'had_share_link' => (bool) ($shareCol && ! empty($arr[$shareCol])),
                                'status' => $arr['status'] ?? null,
                                'region_style' => $arr['region_style'] ?? null,
                                'budget_total_vnd' => $arr['budget_total_vnd'] ?? null,
                                'guest_target' => $arr['guest_target'] ?? null,
                                'created_month' => $createdAt->format('Y-m'),
                            ],
                        ]);

                        $idsToDelete[] = (int) $arr['id'];
                    }

                    if ($idsToDelete !== []) {
                        $prunedCount = DB::table($table)->whereIn('id', $idsToDelete)->delete();
                    }
                });
            }

            $summary['total_archived_and_pruned'] += $prunedCount;
            $summary['per_tool'][$key] = [
                'label' => $cfg['label'],
                'candidates' => $count,
                'pruned' => $prunedCount,
            ];
        }

        return $summary;
    }

    /**
     * Xuất CSV UTF-8 BOM cho kho lưu trữ phân tích 6 tháng hoặc nhật ký hành vi người dùng.
     */
    public function exportCsv(string $scope = 'archives'): string
    {
        $bom = "\xEF\xBB\xBF";
        $lines = [];

        if ($scope === 'activities') {
            $lines[] = $this->csvRow(['ID', 'Thời gian', 'User ID', 'Công cụ', 'Hành động', 'Method', 'URL', 'Tóm tắt', 'IP']);
            foreach (UserActivityLog::query()->latest('id')->limit(2000)->get() as $log) {
                $lines[] = $this->csvRow([
                    (string) $log->id,
                    (string) $log->created_at?->format('Y-m-d H:i:s'),
                    (string) ($log->user_id ?? 'guest'),
                    (string) $log->tool_key,
                    (string) $log->action,
                    (string) $log->method,
                    (string) $log->url,
                    (string) $log->summary,
                    (string) $log->ip_address,
                ]);
            }
        } elseif ($scope === 'consents') {
            $lines[] = $this->csvRow(['ID', 'Thời gian chấp thuận', 'User ID', 'Phiên bản CS', 'Thiết yếu', 'Phân tích', 'Cá nhân hóa AI', 'Marketing', 'Nguồn URL']);
            foreach (UserCookieConsent::query()->latest('id')->limit(2000)->get() as $c) {
                $lines[] = $this->csvRow([
                    (string) $c->id,
                    (string) $c->accepted_at?->format('Y-m-d H:i:s'),
                    (string) ($c->user_id ?? 'guest'),
                    (string) $c->policy_version,
                    $c->consent_essential ? 'Có' : 'Không',
                    $c->consent_analytics ? 'Có' : 'Không',
                    $c->consent_personalization ? 'Có' : 'Không',
                    $c->consent_marketing ? 'Có' : 'Không',
                    (string) $c->source_url,
                ]);
            }
        } else {
            $lines[] = $this->csvRow(['ID', 'Công cụ', 'Bảng gốc', 'ID gốc', 'User ID', 'Email', 'Gói cước', 'Tiêu đề', 'Slug cũ', 'Tạo lúc', 'Lưu trữ lúc']);
            foreach (ToolRetentionArchive::query()->latest('id')->limit(2000)->get() as $a) {
                $lines[] = $this->csvRow([
                    (string) $a->id,
                    (string) $a->tool_key,
                    (string) $a->source_table,
                    (string) $a->source_record_id,
                    (string) ($a->user_id ?? 'guest'),
                    (string) ($a->user_email ?? ''),
                    (string) ($a->user_plan_tier ?? 'free'),
                    (string) $a->title,
                    (string) ($a->resource_slug ?? ''),
                    (string) $a->created_originally_at?->format('Y-m-d H:i:s'),
                    (string) $a->archived_at?->format('Y-m-d H:i:s'),
                ]);
            }
        }

        return $bom.implode("\r\n", $lines)."\r\n";
    }

    /**
     * Lấy KPI chuyên sâu cho 1 phân hệ công cụ độc lập.
     *
     * @return array<string, mixed>
     */
    public function getSingleToolStats(string $toolKey): array
    {
        $overview = $this->getOverviewStats();
        $meta = $overview['per_tool'][$toolKey] ?? reset($overview['per_tool']);

        $archivedCount = Schema::hasTable('tool_retention_archives')
            ? ToolRetentionArchive::query()->where('tool_key', $toolKey)->count()
            : 0;
        $activity7d = Schema::hasTable('user_activity_logs')
            ? UserActivityLog::query()
                ->where('tool_key', $toolKey === 'wedding_journey' ? 'wedding_journey' : $toolKey)
                ->where('created_at', '>=', now()->subDays(7))
                ->count()
            : 0;

        return array_merge($meta, [
            'archived_count' => $archivedCount,
            'activity_7d' => $activity7d,
        ]);
    }

    /**
     * Xem chi tiết trọn bộ dữ liệu 1 bản ghi người dùng đã tạo + hành vi gần nhất của user đó.
     *
     * @return array<string, mixed>|null
     */
    public function inspectRecord(string $toolKey, int $id): ?array
    {
        $cfg = self::TOOL_REGISTRY[$toolKey] ?? null;
        if (! $cfg || ! Schema::hasTable($cfg['table'])) {
            return null;
        }

        $row = DB::table($cfg['table'])->where('id', $id)->first();
        if (! $row) {
            return null;
        }

        $arr = (array) $row;
        $user = ! empty($arr['user_id'])
            ? User::query()->find((int) $arr['user_id'], ['id', 'name', 'email', 'current_plan_slug', 'created_at'])
            : null;
        $createdAt = ! empty($arr['created_at']) ? Carbon::parse($arr['created_at']) : null;
        $expiresAt = $createdAt ? $createdAt->copy()->addMonths(self::DEFAULT_RETENTION_MONTHS) : null;

        $recentActivities = ! empty($arr['user_id']) && Schema::hasTable('user_activity_logs')
            ? UserActivityLog::query()
                ->where('user_id', (int) $arr['user_id'])
                ->latest('id')
                ->limit(15)
                ->get()
            : collect();

        $formattedFields = [];
        foreach ($arr as $col => $val) {
            if (is_string($val) && (str_starts_with(trim($val), '{') || str_starts_with(trim($val), '['))) {
                $decoded = json_decode($val, true);
                $formattedFields[$col] = json_last_error() === JSON_ERROR_NONE
                    ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                    : $val;
            } else {
                $formattedFields[$col] = $val;
            }
        }

        return [
            'tool' => $cfg,
            'tool_key' => $toolKey,
            'id' => $id,
            'raw' => $arr,
            'fields' => $formattedFields,
            'user' => $user,
            'created_at' => $createdAt,
            'expires_at' => $expiresAt,
            'share_token' => $cfg['share_col'] && isset($arr[$cfg['share_col']]) ? (string) $arr[$cfg['share_col']] : null,
            'recent_activities' => $recentActivities,
        ];
    }

    /**
     * Lưu tóm tắt phân tích rồi xóa sớm 1 bản ghi cụ thể từ trang quản trị Admin.
     */
    public function archiveAndDeleteSingle(string $toolKey, int $id): bool
    {
        $cfg = self::TOOL_REGISTRY[$toolKey] ?? null;
        if (! $cfg || ! Schema::hasTable($cfg['table'])) {
            return false;
        }
        $table = $cfg['table'];
        $row = DB::table($table)->where('id', $id)->first();
        if (! $row) {
            return false;
        }
        $arr = (array) $row;
        $user = ! empty($arr['user_id']) ? User::query()->find((int) $arr['user_id'], ['id', 'email', 'current_plan_slug']) : null;
        $createdAt = ! empty($arr['created_at']) ? Carbon::parse($arr['created_at']) : now();
        $titleCol = $cfg['title_col'];
        $slugCol = $cfg['slug_col'];

        return DB::transaction(function () use ($table, $toolKey, $id, $arr, $user, $createdAt, $titleCol, $slugCol): bool {
            ToolRetentionArchive::create([
                'tool_key' => $toolKey,
                'source_table' => $table,
                'source_record_id' => $id,
                'user_id' => $arr['user_id'] ?? null,
                'user_email' => $user?->email,
                'user_plan_tier' => $user?->current_plan_slug ?? 'free',
                'resource_slug' => $slugCol && isset($arr[$slugCol]) ? (string) $arr[$slugCol] : null,
                'title' => mb_substr((string) ($titleCol && isset($arr[$titleCol]) ? $arr[$titleCol] : "#{$id}"), 0, 220),
                'created_originally_at' => $createdAt,
                'expired_at' => now(),
                'archived_at' => now(),
                'analytics_summary_json' => [
                    'archived_by' => 'admin_manual',
                    'created_month' => $createdAt->format('Y-m'),
                ],
            ]);

            return DB::table($table)->where('id', $id)->delete() > 0;
        });
    }

    /**
     * Xuất CSV riêng cho 1 phân hệ công cụ.
     */
    public function exportToolCsv(string $toolKey): string
    {
        $data = $this->listToolRecords($toolKey, [], 1000);
        $bom = "\xEF\xBB\xBF";
        $lines = [];
        $lines[] = $this->csvRow(['ID', 'Công cụ', 'Tiêu đề', 'Slug', 'User ID', 'Người tạo', 'Email', 'Gói', 'Ngày tạo', 'Hạn 6 tháng', 'Link chia sẻ']);

        foreach ($data['rows'] as $row) {
            $lines[] = $this->csvRow([
                (string) $row['id'],
                (string) $data['tool']['label'],
                (string) $row['title'],
                (string) ($row['slug'] ?? ''),
                (string) ($row['user_id'] ?? 'guest'),
                (string) $row['user_name'],
                (string) ($row['user_email'] ?? ''),
                (string) $row['user_plan'],
                (string) ($row['created_at'] ?? ''),
                (string) ($row['expires_at'] ?? ''),
                $row['share_token'] ? 'Đang mở' : 'Riêng tư',
            ]);
        }

        return $bom.implode("\r\n", $lines)."\r\n";
    }

    /**
     * @param  list<string>  $cells
     */
    protected function csvRow(array $cells): string
    {
        return implode(',', array_map(static fn (string $c): string => '"'.str_replace('"', '""', $c).'"', $cells));
    }
}
