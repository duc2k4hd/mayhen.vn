<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\BaziChart;
use App\Models\CalendarFamilyEvent;
use App\Models\CoupleCompatibilityReport;
use App\Models\FamilyTree;
use App\Models\IchingReading;
use App\Models\NumerologyReading;
use App\Models\PersonalitySession;
use App\Models\PlatformSetting;
use App\Models\TarotReading;
use App\Models\ToolRetentionArchive;
use App\Models\TuViChart;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Models\UserCookieConsent;
use App\Models\WeddingProject;
use App\Services\SocialBannerService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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
        'personality' => 'personality',
    ];

    /**
     * Ánh xạ prefix URL công khai trên Client -> tool_key để tự động đồng bộ Banner OG 1200x600.
     *
     * @var array<string, string>
     */
    public const CLIENT_PREFIX_TO_TOOL = [
        'gia-pha' => 'family_tree',
        'gieo-que' => 'iching',
        'tu-vi' => 'tuvi',
        'than-so-hoc' => 'numerology',
        'tarot' => 'tarot',
        'lich-am' => 'calendar',
        'bat-tu' => 'bazi',
        'cao-ly-dau-hinh' => 'marriage_compatibility',
        'xem-tuoi-vo-chong' => 'marriage_compatibility',
        'xem-ngay-cuoi' => 'wedding_journey',
        'to-chuc-dam-cuoi' => 'wedding_journey',
        'mbti' => 'personality',
        'trac-nghiem-tinh-cach' => 'personality',
        'big-five' => 'personality',
    ];

    public const SOCIAL_BANNER_WIDTH = 1200;

    public const SOCIAL_BANNER_HEIGHT = 600;

    /**
     * Single Source of Truth cho toàn bộ 10 phân hệ công cụ trên Mây Hẹn.
     *
     * @var array<string, array<string, mixed>>
     */
    public const TOOL_REGISTRY = [
        'personality' => [
            'label' => 'Trắc Nghiệm Tính Cách (16 Nhóm & Big Five)',
            'short' => 'Tính Cách',
            'table' => 'personality_sessions',
            'model' => PersonalitySession::class,
            'title_col' => 'suggested_type',
            'slug_col' => 'slug',
            'share_col' => 'share_token',
            'public_url' => '/mbti',
            'accent' => '#1E4D3B',
        ],
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
            'title_col' => 'profile_name',
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
            'title_col' => null,
            'profile_table' => 'numerology_profiles',
            'profile_title_col' => 'display_name',
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
            'title_col' => null,
            'profile_table' => 'tuvi_profiles',
            'profile_title_col' => 'nickname',
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
            'title_col' => 'title',
            'slug_col' => 'slug',
            'share_col' => 'visibility',
            'public_url' => '/gia-pha',
            'accent' => '#15803D',
        ],
    ];

    /**
     * Bộ nhớ đệm schema theo process để tránh query `information_schema` lặp lại.
     *
     * @var array<string, bool>
     */
    protected static array $tableExistsMemo = [];

    /**
     * @var array<string, list<string>>
     */
    protected static array $tableColumnsMemo = [];

    public static function clearSchemaMemo(): void
    {
        self::$tableExistsMemo = [];
        self::$tableColumnsMemo = [];
    }

    protected function hasTableCached(string $table): bool
    {
        if (array_key_exists($table, self::$tableExistsMemo)) {
            return self::$tableExistsMemo[$table];
        }

        if (! app()->runningUnitTests()) {
            return self::$tableExistsMemo[$table] = (bool) Cache::remember(
                'mayhen_schema_tbl_exists_v1_'.$table,
                300,
                static fn (): bool => Schema::hasTable($table)
            );
        }

        return self::$tableExistsMemo[$table] = Schema::hasTable($table);
    }

    /**
     * @return list<string>
     */
    protected function getColumnsCached(string $table): array
    {
        if (isset(self::$tableColumnsMemo[$table])) {
            return self::$tableColumnsMemo[$table];
        }

        if (! $this->hasTableCached($table)) {
            return self::$tableColumnsMemo[$table] = [];
        }

        if (! app()->runningUnitTests()) {
            return self::$tableColumnsMemo[$table] = Cache::remember(
                'mayhen_schema_tbl_cols_v1_'.$table,
                300,
                static fn (): array => Schema::getColumnListing($table)
            );
        }

        return self::$tableColumnsMemo[$table] = Schema::getColumnListing($table);
    }

    protected function hasColumnCached(string $table, string $column): bool
    {
        return in_array($column, $this->getColumnsCached($table), true);
    }

    /**
     * Tổng hợp KPI 1 phân hệ công cụ bằng 1 truy vấn SQL gộp duy nhất (tránh full-scan nhiều lần).
     *
     * @param  array<string, mixed>  $cfg
     * @return array<string, mixed>
     */
    protected function computeSingleToolMetrics(
        string $key,
        array $cfg,
        Carbon $cutoff,
        Carbon $expiringSoonCutoff,
        Carbon $last7Days
    ): array {
        $table = $cfg['table'];
        if (! $this->hasTableCached($table)) {
            return array_merge($cfg, [
                'key' => $key,
                'total' => 0,
                'last_7_days' => 0,
                'shared' => 0,
                'expired' => 0,
                'expiring_soon' => 0,
                'exists' => false,
            ]);
        }

        $columns = $this->getColumnsCached($table);
        $cutoffStr = $cutoff->toDateTimeString();
        $expSoonStr = $expiringSoonCutoff->toDateTimeString();
        $last7Str = $last7Days->toDateTimeString();

        $selectSql = 'COUNT(*) as total_cnt'
            .', COALESCE(SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END), 0) as recent_cnt'
            .', COALESCE(SUM(CASE WHEN created_at < ? THEN 1 ELSE 0 END), 0) as expired_cnt'
            .', COALESCE(SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END), 0) as exp_soon_cnt';

        $bindings = [$last7Str, $cutoffStr, $cutoffStr, $expSoonStr];

        $shareCol = $cfg['share_col'] ?? null;
        if ($shareCol && in_array($shareCol, $columns, true)) {
            if ($shareCol === 'visibility' && in_array('is_published', $columns, true)) {
                $selectSql .= ", COALESCE(SUM(CASE WHEN visibility = 'public' AND is_published = 1 THEN 1 ELSE 0 END), 0) as shared_cnt";
            } else {
                $selectSql .= ", COALESCE(SUM(CASE WHEN `{$shareCol}` IS NOT NULL AND `{$shareCol}` != '' THEN 1 ELSE 0 END), 0) as shared_cnt";
            }
        } else {
            $selectSql .= ', 0 as shared_cnt';
        }

        $query = DB::table($table)->selectRaw($selectSql, $bindings);
        if (in_array('deleted_at', $columns, true)) {
            $query->whereNull('deleted_at');
        }

        $agg = $query->first();

        return array_merge($cfg, [
            'key' => $key,
            'total' => (int) ($agg->total_cnt ?? 0),
            'last_7_days' => (int) ($agg->recent_cnt ?? 0),
            'shared' => (int) ($agg->shared_cnt ?? 0),
            'expired' => (int) ($agg->expired_cnt ?? 0),
            'expiring_soon' => (int) ($agg->exp_soon_cnt ?? 0),
            'exists' => true,
        ]);
    }

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
            $metrics = $this->computeSingleToolMetrics($key, $cfg, $cutoff, $expiringSoonCutoff, $last7Days);
            $perTool[$key] = $metrics;

            $totalActive += $metrics['total'];
            $totalLast7Days += $metrics['last_7_days'];
            $totalShared += $metrics['shared'];
            $totalExpired += $metrics['expired'];
            $totalExpiringSoon += $metrics['expiring_soon'];
        }

        $archivedCount = $this->hasTableCached('tool_retention_archives')
            ? ToolRetentionArchive::query()->count()
            : 0;
        $activity24h = 0;
        $activityTotal = 0;
        if ($this->hasTableCached('user_activity_logs')) {
            $actAgg = DB::table('user_activity_logs')->selectRaw(
                'COUNT(*) as total_cnt, COALESCE(SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END), 0) as last_24h_cnt',
                [now()->subDay()->toDateTimeString()]
            )->first();
            $activityTotal = (int) ($actAgg->total_cnt ?? 0);
            $activity24h = (int) ($actAgg->last_24h_cnt ?? 0);
        }
        $consentTotal = 0;
        $consentAnalyticsAccepted = 0;
        if ($this->hasTableCached('user_cookie_consents')) {
            $conAgg = DB::table('user_cookie_consents')->selectRaw(
                'COUNT(*) as total_cnt, COALESCE(SUM(CASE WHEN consent_analytics = 1 THEN 1 ELSE 0 END), 0) as accepted_cnt'
            )->first();
            $consentTotal = (int) ($conAgg->total_cnt ?? 0);
            $consentAnalyticsAccepted = (int) ($conAgg->accepted_cnt ?? 0);
        }

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
     * Liệt kê danh sách bản ghi người dùng đã tạo trong 1 công cụ cụ thể (chỉ SELECT cột nhẹ, tuyệt đối không kéo JSON payload lớn gây tràn RAM).
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
        if (! $this->hasTableCached($table)) {
            return ['tool' => $cfg, 'key' => $toolKey, 'rows' => [], 'total' => 0, 'paginator' => null];
        }

        $columns = $this->getColumnsCached($table);
        $titleCol = $cfg['title_col'] ?? null;
        $slugCol = $cfg['slug_col'] ?? null;
        $shareCol = $cfg['share_col'] ?? null;
        $profileTable = $cfg['profile_table'] ?? null;
        $profileTitleCol = $cfg['profile_title_col'] ?? null;

        // Chỉ SELECT các cột metadata gọn nhẹ để tránh OOM khi bảng chứa hàng chục MB JSON
        $desiredCols = array_filter([
            'id',
            'user_id',
            'profile_id',
            'created_at',
            $titleCol,
            $slugCol,
            $shareCol,
            'is_published',
        ]);
        $selectCols = array_values(array_unique(array_intersect($desiredCols, $columns)));
        if ($selectCols === []) {
            $selectCols = ['id'];
        }

        $query = DB::table($table)->select($selectCols)->orderByDesc('id');
        if (in_array('deleted_at', $columns, true)) {
            $query->whereNull('deleted_at');
        }

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $matchedProfileIds = [];
            if ($profileTable && $profileTitleCol && in_array('profile_id', $columns, true) && $this->hasTableCached($profileTable)) {
                $matchedProfileIds = DB::table($profileTable)
                    ->where($profileTitleCol, 'like', '%'.$search.'%')
                    ->limit(200)
                    ->pluck('id')
                    ->all();
            }

            $query->where(function ($q) use ($cfg, $columns, $search, $matchedProfileIds) {
                if (! empty($cfg['title_col']) && in_array($cfg['title_col'], $columns, true)) {
                    $q->orWhere($cfg['title_col'], 'like', '%'.$search.'%');
                }
                if (! empty($cfg['slug_col']) && in_array($cfg['slug_col'], $columns, true)) {
                    $q->orWhere($cfg['slug_col'], 'like', '%'.$search.'%');
                }
                if ($matchedProfileIds !== []) {
                    $q->orWhereIn('profile_id', $matchedProfileIds);
                }
                if (is_numeric($search)) {
                    $q->orWhere('id', (int) $search);
                    if (in_array('user_id', $columns, true)) {
                        $q->orWhere('user_id', (int) $search);
                    }
                }
            });
        }

        if (! empty($filters['user_id']) && in_array('user_id', $columns, true)) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (($filters['expired_only'] ?? false) === true) {
            $query->where('created_at', '<', Carbon::now('Asia/Ho_Chi_Minh')->subMonths(self::DEFAULT_RETENTION_MONTHS));
        }

        $paginator = $query->paginate($perPage);
        $items = collect($paginator->items());

        $userIds = $items->pluck('user_id')->filter()->unique()->values()->all();
        $usersMap = $userIds !== []
            ? User::query()->whereIn('id', $userIds)->get(['id', 'name', 'email', 'current_plan_slug'])->keyBy('id')
            : collect();

        $profilesMap = collect();
        if ($profileTable && $profileTitleCol && $this->hasTableCached($profileTable)) {
            $profileIds = $items->pluck('profile_id')->filter()->unique()->values()->all();
            if ($profileIds !== []) {
                $profilesMap = DB::table($profileTable)
                    ->whereIn('id', $profileIds)
                    ->pluck($profileTitleCol, 'id');
            }
        }

        $rows = [];
        foreach ($items as $item) {
            $arr = (array) $item;
            $createdAt = ! empty($arr['created_at']) ? Carbon::parse($arr['created_at']) : null;
            $expiresAt = $createdAt ? $createdAt->copy()->addMonths(self::DEFAULT_RETENTION_MONTHS) : null;
            $u = ! empty($arr['user_id']) ? $usersMap->get((int) $arr['user_id']) : null;

            $displayTitle = null;
            if ($titleCol && isset($arr[$titleCol]) && trim((string) $arr[$titleCol]) !== '') {
                $displayTitle = (string) $arr[$titleCol];
            } elseif (! empty($arr['profile_id']) && $profilesMap->has($arr['profile_id'])) {
                $displayTitle = (string) $profilesMap->get($arr['profile_id']);
            } elseif ($slugCol && ! empty($arr[$slugCol])) {
                $displayTitle = (string) $arr[$slugCol];
            } else {
                $displayTitle = "#{$arr['id']}";
            }

            $shareValue = null;
            if ($shareCol === 'visibility') {
                if (($arr['visibility'] ?? null) === 'public' && ! empty($arr['is_published'])) {
                    $shareValue = (string) ($arr[$slugCol] ?? 'public');
                }
            } elseif ($shareCol && ! empty($arr[$shareCol])) {
                $shareValue = (string) $arr[$shareCol];
            }

            $rows[] = [
                'id' => (int) $arr['id'],
                'title' => $displayTitle,
                'slug' => $slugCol && isset($arr[$slugCol]) ? (string) $arr[$slugCol] : null,
                'share_token' => $shareValue,
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
        if (! $cfg || ! $cfg['share_col'] || ! $this->hasTableCached($cfg['table'])) {
            return false;
        }
        $table = $cfg['table'];
        $columns = $this->getColumnsCached($table);
        if (! in_array($cfg['share_col'], $columns, true)) {
            return false;
        }

        if ($cfg['share_col'] === 'visibility') {
            $update = ['visibility' => 'password'];
            if (in_array('is_published', $columns, true)) {
                $update['is_published'] = false;
            }

            return DB::table($table)->where('id', $id)->update($update) > 0;
        }

        $update = [$cfg['share_col'] => null];
        if (in_array('is_public_shared', $columns, true)) {
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
            if (! $this->hasTableCached($table)) {
                continue;
            }

            $columns = $this->getColumnsCached($table);
            $summaryCols = array_values(array_unique(array_intersect(
                array_filter([
                    'id',
                    'user_id',
                    'profile_id',
                    'created_at',
                    $cfg['title_col'] ?? null,
                    $cfg['slug_col'] ?? null,
                    $cfg['share_col'] ?? null,
                    'status',
                    'region_style',
                    'budget_total_vnd',
                    'guest_target',
                    'is_published',
                ]),
                $columns
            )));

            $expiredRows = DB::table($table)
                ->select($summaryCols !== [] ? $summaryCols : ['id'])
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

                        $hadShareLink = $shareCol === 'visibility'
                            ? (($arr['visibility'] ?? null) === 'public' && ! empty($arr['is_published']))
                            : (bool) ($shareCol && ! empty($arr[$shareCol]));

                        ToolRetentionArchive::create([
                            'tool_key' => $key,
                            'source_table' => $table,
                            'source_record_id' => (int) $arr['id'],
                            'user_id' => $arr['user_id'] ?? null,
                            'user_email' => $u?->email,
                            'user_plan_tier' => $u?->current_plan_slug ?? 'free',
                            'resource_slug' => $slugCol && isset($arr[$slugCol]) ? (string) $arr[$slugCol] : null,
                            'title' => mb_substr((string) ($titleCol && isset($arr[$titleCol]) && $arr[$titleCol] !== '' ? $arr[$titleCol] : ($slugCol && ! empty($arr[$slugCol]) ? $arr[$slugCol] : "#{$arr['id']}")), 0, 220),
                            'created_originally_at' => $createdAt,
                            'expired_at' => $createdAt->copy()->addMonths($months),
                            'archived_at' => now(),
                            'analytics_summary_json' => [
                                'had_share_link' => $hadShareLink,
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
     * Lấy KPI chuyên sâu cho 1 phân hệ công cụ độc lập (chỉ truy vấn đúng bảng của công cụ đó, tránh quét cả 8 bảng còn lại).
     *
     * @return array<string, mixed>
     */
    public function getSingleToolStats(string $toolKey): array
    {
        if (! isset(self::TOOL_REGISTRY[$toolKey])) {
            $toolKey = array_key_first(self::TOOL_REGISTRY);
        }

        $cfg = self::TOOL_REGISTRY[$toolKey];
        $cutoff = Carbon::now('Asia/Ho_Chi_Minh')->subMonths(self::DEFAULT_RETENTION_MONTHS);
        $expiringSoonCutoff = Carbon::now('Asia/Ho_Chi_Minh')->subMonths(max(1, self::DEFAULT_RETENTION_MONTHS - 1));
        $last7Days = Carbon::now('Asia/Ho_Chi_Minh')->subDays(7);

        $meta = $this->computeSingleToolMetrics($toolKey, $cfg, $cutoff, $expiringSoonCutoff, $last7Days);

        $archivedCount = $this->hasTableCached('tool_retention_archives')
            ? ToolRetentionArchive::query()->where('tool_key', $toolKey)->count()
            : 0;
        $activity7d = $this->hasTableCached('user_activity_logs')
            ? UserActivityLog::query()
                ->where('tool_key', $toolKey)
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
        if (! $cfg || ! $this->hasTableCached($cfg['table'])) {
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

        $recentActivities = ! empty($arr['user_id']) && $this->hasTableCached('user_activity_logs')
            ? UserActivityLog::query()
                ->where('user_id', (int) $arr['user_id'])
                ->latest('id')
                ->limit(15)
                ->get()
            : collect();

        $formattedFields = [];
        foreach ($arr as $col => $val) {
            if (is_string($val)) {
                $len = strlen($val);
                if ($len > 262144) {
                    $formattedFields[$col] = mb_substr($val, 0, 8192, 'UTF-8')
                        ."\n... [Đã rút gọn hiển thị để bảo vệ bộ nhớ: tổng ".number_format($len).' bytes]';

                    continue;
                }
                if (str_starts_with(trim($val), '{') || str_starts_with(trim($val), '[')) {
                    $decoded = json_decode($val, true);
                    $formattedFields[$col] = json_last_error() === JSON_ERROR_NONE
                        ? (string) json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        : $val;

                    continue;
                }
            }
            $formattedFields[$col] = $val;
        }

        $shareToken = ($cfg['share_col'] ?? null) === 'visibility'
            ? ((($arr['visibility'] ?? null) === 'public' && ! empty($arr['is_published'])) ? (string) ($arr['slug'] ?? 'public') : null)
            : ($cfg['share_col'] && isset($arr[$cfg['share_col']]) ? (string) $arr[$cfg['share_col']] : null);

        return [
            'tool' => $cfg,
            'tool_key' => $toolKey,
            'id' => $id,
            'raw' => $arr,
            'fields' => $formattedFields,
            'user' => $user,
            'created_at' => $createdAt,
            'expires_at' => $expiresAt,
            'share_token' => $shareToken,
            'recent_activities' => $recentActivities,
        ];
    }

    /**
     * Lưu tóm tắt phân tích rồi xóa sớm 1 bản ghi cụ thể từ trang quản trị Admin.
     */
    public function archiveAndDeleteSingle(string $toolKey, int $id): bool
    {
        $cfg = self::TOOL_REGISTRY[$toolKey] ?? null;
        if (! $cfg || ! $this->hasTableCached($cfg['table'])) {
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
     * Đọc nguyên bản key tool_social_banner_{$toolKey} (không fallback).
     */
    public static function getRawToolSocialBanner(string $toolKey): ?string
    {
        $val = trim((string) PlatformSetting::getValue(
            PlatformSetting::GROUP_BRAND_SEO,
            'tool_social_banner_'.$toolKey,
            ''
        ));

        return $val !== '' ? $val : null;
    }

    /**
     * Lấy đường dẫn Banner Mạng Xã Hội (1200x600) đã cấu hình cho 1 công cụ (ưu tiên tool banner, fallback slot SEO tương ứng).
     */
    public static function getToolSocialBanner(string $toolKey): ?string
    {
        $direct = self::getRawToolSocialBanner($toolKey);
        if ($direct !== null) {
            return $direct;
        }

        $slots = SocialBannerService::TOOL_TO_SLOTS[$toolKey] ?? [];
        foreach (array_reverse($slots) as $slotKey) {
            $slotBanner = SocialBannerService::getRawSlotBanner($slotKey);
            if ($slotBanner !== null) {
                return $slotBanner;
            }
        }

        return null;
    }

    /**
     * Lưu hoặc xóa Banner Mạng Xã Hội (1200x600) cho 1 công cụ (tự động đồng bộ sang các slot SEO tương ứng).
     */
    public function saveToolSocialBanner(string $toolKey, ?string $bannerPath, ?int $updatedBy = null, bool $syncSeoSlots = true): void
    {
        $cleanPath = $bannerPath ? trim($bannerPath) : '';
        $label = self::TOOL_REGISTRY[$toolKey]['label'] ?? $toolKey;
        PlatformSetting::updateOrCreate(
            [
                'group' => PlatformSetting::GROUP_BRAND_SEO,
                'key' => 'tool_social_banner_'.$toolKey,
            ],
            [
                'value' => $cleanPath,
                'label' => "Banner Social 1200×600 — {$label}",
                'is_secret' => false,
                'updated_by' => $updatedBy,
            ]
        );

        if ($syncSeoSlots) {
            $seoService = app(SocialBannerService::class);
            foreach (SocialBannerService::TOOL_TO_SLOTS[$toolKey] ?? [] as $slotKey) {
                $seoService->saveBanner($slotKey, $cleanPath !== '' ? $cleanPath : null, $updatedBy, syncTool: false);
            }
        }

        PlatformSetting::clearRuntimeCache();
    }

    /**
     * Tự động phát hiện Banner Social 1200x600 tương ứng với URL công cụ đang truy cập trên Client.
     *
     * @return array{tool_key: string, url: string, width: int, height: int, alt: string}|null
     */
    public static function resolveSocialBannerForPath(string $requestPath): ?array
    {
        $path = ltrim($requestPath, '/');
        foreach (self::CLIENT_PREFIX_TO_TOOL as $prefix => $toolKey) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                $stored = self::getToolSocialBanner($toolKey);
                if ($stored === null) {
                    return null;
                }
                $absUrl = str_starts_with($stored, 'http://') || str_starts_with($stored, 'https://')
                    ? $stored
                    : asset(ltrim($stored, '/'));
                $label = self::TOOL_REGISTRY[$toolKey]['label'] ?? 'Mây Hẹn';

                return [
                    'tool_key' => $toolKey,
                    'url' => $absUrl,
                    'width' => self::SOCIAL_BANNER_WIDTH,
                    'height' => self::SOCIAL_BANNER_HEIGHT,
                    'alt' => "{$label} — Mây Hẹn (1200×600)",
                ];
            }
        }

        return null;
    }

    /**
     * @param  list<string>  $cells
     */
    protected function csvRow(array $cells): string
    {
        return implode(',', array_map(static fn (string $c): string => '"'.str_replace('"', '""', $c).'"', $cells));
    }
}
