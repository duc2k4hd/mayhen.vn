<?php

namespace App\Services;

use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\WeddingDomain;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminDomainManagerService
{
    public function __construct(
        protected ?DomainDnsService $dnsService = null
    ) {
        $this->dnsService ??= app(DomainDnsService::class);
    }

    /**
     * Thống kê 4 thẻ KPI chỉ số vận hành tên miền & định tuyến (Mục 172 PRD V3).
     *
     * @return array{total_custom: int, total_subdomains: int, active_count: int, pending_count: int}
     */
    public function getDomainKpis(): array
    {
        $totalCustom = WeddingDomain::whereNotNull('custom_domain')->where('custom_domain', '!=', '')->count();
        $totalSubdomains = WeddingDomain::whereNotNull('subdomain')->where('subdomain', '!=', '')->count();
        $activeCount = WeddingDomain::where('status', 'active')->count();
        $pendingCount = WeddingDomain::where('status', 'pending')->count();

        return [
            'total_custom' => $totalCustom,
            'total_subdomains' => $totalSubdomains,
            'active_count' => $activeCount,
            'pending_count' => $pendingCount,
        ];
    }

    /**
     * Lấy danh sách tên miền có phân trang, tìm kiếm và bộ lọc.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getFilteredDomains(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = WeddingDomain::with(['wedding.user', 'invitation'])->latest('id');

        if (! empty($filters['status']) && in_array($filters['status'], ['active', 'pending', 'failed'], true)) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            if ($filters['type'] === 'custom') {
                $query->whereNotNull('custom_domain')->where('custom_domain', '!=', '');
            } elseif ($filters['type'] === 'subdomain') {
                $query->where(function ($q) {
                    $q->whereNull('custom_domain')->orWhere('custom_domain', '');
                })->whereNotNull('subdomain');
            }
        }

        if (! empty($filters['search'])) {
            $term = trim((string) $filters['search']);
            $query->where(function ($q) use ($term) {
                $q->where('custom_domain', 'like', "%{$term}%")
                    ->orWhere('subdomain', 'like', "%{$term}%")
                    ->orWhereHas('wedding', function ($wq) use ($term) {
                        $wq->where('groom_name', 'like', "%{$term}%")
                            ->orWhere('bride_name', 'like', "%{$term}%");
                    });
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Xác minh cấu hình DNS thực tế của tên miền.
     *
     * @return array{success: bool, message: string, records: array}
     */
    public function verifyDomain(WeddingDomain $domain, User $admin): array
    {
        $targetDomain = $domain->custom_domain;

        if (empty($targetDomain)) {
            // Nếu chỉ là subdomain nội bộ, tự động kích hoạt
            $domain->update([
                'status' => 'active',
                'ssl_status' => 'active',
                'dns_verified_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => "Subdomain \"{$domain->subdomain}\" thuộc hạ tầng Mây Hẹn đã hoạt động.",
                'records' => [],
            ];
        }

        $expectedTarget = $domain->cname_target ?: $this->getExpectedCnameTarget();
        $res = $this->dnsService->verifyCname($targetDomain, $expectedTarget);

        if ($res['verified']) {
            $domain->update([
                'status' => 'active',
                'ssl_status' => 'active',
                'dns_verified_at' => now(),
                'dns_records' => $res['records'],
            ]);

            AuditLogger::log(
                action: 'domain.verified_active',
                targetType: 'wedding_domain',
                targetId: $domain->id,
                description: "Xác minh DNS thành công cho tên miền {$targetDomain} bởi {$admin->name}.",
                details: [
                    'domain' => $targetDomain,
                    'target' => $expectedTarget,
                    'admin_id' => $admin->id,
                ]
            );

            return [
                'success' => true,
                'message' => "Bản ghi CNAME của {$targetDomain} đã trỏ chính xác về {$expectedTarget}. Trạng thái kích hoạt: ACTIVE.",
                'records' => $res['records'],
            ];
        }

        $domain->update([
            'status' => 'failed',
            'ssl_status' => 'failed',
            'dns_records' => $res['records'],
        ]);

        AuditLogger::log(
            action: 'domain.verification_failed',
            targetType: 'wedding_domain',
            targetId: $domain->id,
            description: "Xác minh DNS thất bại cho tên miền {$targetDomain}: {$res['message']}",
            details: [
                'domain' => $targetDomain,
                'error' => $res['message'],
                'admin_id' => $admin->id,
            ]
        );

        return [
            'success' => false,
            'message' => $res['message'],
            'records' => $res['records'],
        ];
    }

    /**
     * Cưỡng chế kích hoạt tên miền (Bỏ qua bước DNS tự động).
     */
    public function forceActivate(WeddingDomain $domain, User $admin): bool
    {
        $domain->update([
            'status' => 'active',
            'ssl_status' => 'active',
            'dns_verified_at' => now(),
        ]);

        AuditLogger::log(
            action: 'domain.force_activated',
            targetType: 'wedding_domain',
            targetId: $domain->id,
            description: "Quản trị viên {$admin->name} cưỡng chế kích hoạt tên miền \"{$domain->activeUrl()}\".",
            details: [
                'domain_id' => $domain->id,
                'custom_domain' => $domain->custom_domain,
                'subdomain' => $domain->subdomain,
                'admin_id' => $admin->id,
            ]
        );

        return true;
    }

    /**
     * Cưỡng chế ngắt kết nối tên miền (Khi có vi phạm hoặc sự cố DNS).
     */
    public function forceDeactivate(WeddingDomain $domain, User $admin): bool
    {
        $domain->update([
            'status' => 'failed',
            'ssl_status' => 'failed',
        ]);

        AuditLogger::log(
            action: 'domain.force_deactivated',
            targetType: 'wedding_domain',
            targetId: $domain->id,
            description: "Quản trị viên {$admin->name} ngắt kết nối tên miền \"{$domain->activeUrl()}\".",
            details: [
                'domain_id' => $domain->id,
                'admin_id' => $admin->id,
            ]
        );

        return true;
    }

    /**
     * Xóa vĩnh viễn cấu hình tên miền.
     */
    public function deleteDomain(WeddingDomain $domain, User $admin): bool
    {
        $domainId = $domain->id;
        $url = $domain->activeUrl();

        $domain->delete();

        AuditLogger::log(
            action: 'domain.deleted',
            targetType: 'wedding_domain',
            targetId: $domainId,
            description: "Quản trị viên {$admin->name} đã xóa tên miền \"{$url}\".",
            details: [
                'domain_id' => $domainId,
                'admin_id' => $admin->id,
            ]
        );

        return true;
    }

    /**
     * Thực hiện chẩn đoán DNS Lookup trực tiếp cho một hostname bất kỳ.
     *
     * @return array{hostname: string, latency_ms: int, cname: array, a_records: array, ns_records: array, status: string, recommendation: string}
     */
    public function performDnsLookup(string $hostname): array
    {
        $startTime = microtime(true);
        $cleanHost = strtolower(trim($hostname));
        $cleanHost = preg_replace('#^https?://#', '', $cleanHost);
        $cleanHost = explode('/', $cleanHost)[0];

        if (empty($cleanHost) || ! filter_var($cleanHost, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new \InvalidArgumentException("Tên miền \"{$hostname}\" không đúng định dạng chuẩn.");
        }

        $expectedTarget = $this->getExpectedCnameTarget();

        // Testing environment: Mock kết quả tức thời
        if (app()->environment('testing')) {
            $isInvalid = str_contains($cleanHost, 'invalid') || str_contains($cleanHost, 'fail');
            $latency = random_int(15, 35);

            return [
                'hostname' => $cleanHost,
                'latency_ms' => $latency,
                'cname' => $isInvalid ? [] : [
                    ['host' => $cleanHost, 'type' => 'CNAME', 'target' => $expectedTarget],
                ],
                'a_records' => $isInvalid ? [] : [
                    ['host' => $cleanHost, 'type' => 'A', 'ip' => '103.179.189.27'],
                ],
                'ns_records' => [
                    ['host' => $cleanHost, 'type' => 'NS', 'target' => 'ns1.cloudflare.com'],
                    ['host' => $cleanHost, 'type' => 'NS', 'target' => 'ns2.cloudflare.com'],
                ],
                'status' => $isInvalid ? 'error' : 'matched',
                'recommendation' => $isInvalid
                    ? "Tên miền chưa có bản ghi CNAME trỏ về {$expectedTarget}. Hãy thêm bản ghi CNAME tại nhà cung cấp quản lý tên miền."
                    : "Cấu hình DNS chính xác! Bản ghi CNAME đã trỏ về {$expectedTarget}.",
            ];
        }

        $cnameRecords = @dns_get_record($cleanHost, DNS_CNAME) ?: [];
        $aRecords = @dns_get_record($cleanHost, DNS_A) ?: [];
        $nsRecords = @dns_get_record($cleanHost, DNS_NS) ?: [];

        $latency = (int) round((microtime(true) - $startTime) * 1000);

        $matched = false;
        foreach ($cnameRecords as $rec) {
            $recTarget = rtrim(strtolower($rec['target'] ?? ''), '.');
            if ($recTarget === rtrim(strtolower($expectedTarget), '.') || str_ends_with($recTarget, 'mayhen.vn')) {
                $matched = true;
                break;
            }
        }

        $status = $matched ? 'matched' : (empty($cnameRecords) && empty($aRecords) ? 'unresolvable' : 'mismatch');

        $recommendation = match ($status) {
            'matched' => "Cấu hình hoàn hảo! Tên miền {$cleanHost} đã trỏ chính xác về CNAME {$expectedTarget}.",
            'mismatch' => "Tên miền đã có bản ghi nhưng chưa trỏ về {$expectedTarget}. Vui lòng kiểm tra lại cấu hình DNS Host.",
            'unresolvable' => "Không tìm thấy bản ghi DNS nào cho {$cleanHost}. Vui lòng chờ DNS lan truyền hoặc cấu hình lại tại nhà đăng ký.",
        };

        return [
            'hostname' => $cleanHost,
            'latency_ms' => $latency,
            'cname' => $cnameRecords,
            'a_records' => $aRecords,
            'ns_records' => $nsRecords,
            'status' => $status,
            'recommendation' => $recommendation,
        ];
    }

    /**
     * Lấy các thiết lập định tuyến toàn nền tảng.
     *
     * @return array{cname_target: string, server_ip: string, root_domain: string, cloudflare_enabled: bool, blacklisted_subdomains: array<string>}
     */
    public function getPlatformRoutingConfig(): array
    {
        return [
            'cname_target' => $this->getExpectedCnameTarget(),
            'server_ip' => PlatformSetting::getValue('routing', 'server_ip', '103.179.189.27'),
            'root_domain' => PlatformSetting::getValue('routing', 'root_domain', 'mayhen.vn'),
            'cloudflare_enabled' => PlatformSetting::getValue('routing', 'cloudflare_enabled', '1') === '1',
            'blacklisted_subdomains' => WeddingDomain::BLACKLISTED_SUBDOMAINS,
        ];
    }

    /**
     * Cập nhật các thiết lập định tuyến nền tảng.
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePlatformRoutingConfig(array $data, User $admin): void
    {
        if (isset($data['cname_target'])) {
            PlatformSetting::setValue('routing', 'cname_target', trim((string) $data['cname_target']));
        }
        if (isset($data['server_ip'])) {
            PlatformSetting::setValue('routing', 'server_ip', trim((string) $data['server_ip']));
        }
        if (isset($data['root_domain'])) {
            PlatformSetting::setValue('routing', 'root_domain', trim((string) $data['root_domain']));
        }
        if (isset($data['cloudflare_enabled'])) {
            PlatformSetting::setValue('routing', 'cloudflare_enabled', $data['cloudflare_enabled'] ? '1' : '0');
        }

        AuditLogger::log(
            action: 'routing.settings_updated',
            targetType: 'platform_setting',
            targetId: 'routing',
            description: "Cập nhật thiết lập định tuyến CNAME/DNS bởi {$admin->name}.",
            details: [
                'admin_id' => $admin->id,
            ]
        );
    }

    /**
     * Lấy mục tiêu CNAME chuẩn của hệ thống.
     */
    public function getExpectedCnameTarget(): string
    {
        return PlatformSetting::getValue('routing', 'cname_target', 'cname.mayhen.vn');
    }
}

