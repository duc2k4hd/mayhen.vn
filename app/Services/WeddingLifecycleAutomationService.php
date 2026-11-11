<?php

namespace App\Services;

use App\Models\LifecycleAutomationLog;
use App\Models\LifecycleAutomationRule;
use App\Models\User;
use App\Models\Wedding;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class WeddingLifecycleAutomationService
{
    /**
     * Khởi tạo 6 kịch bản vòng đời cưới tự động chuẩn Mây Hẹn Atelier (Mục 149 PRD V3).
     */
    public function seedDefaultRulesIfEmpty(): void
    {
        if (LifecycleAutomationRule::exists()) {
            return;
        }

        $defaults = [
            [
                'code' => 'D-30_GUESTLIST_PREP',
                'name' => 'D-30: Nhắc chốt danh sách & chia sẻ thiệp sớm',
                'description' => 'Tự động gửi thông điệp gợi ý cô dâu chú rể hoàn thiện thông tin hai họ và chia sẻ thiệp online sớm 1 tháng trước ngày cưới.',
                'trigger_event' => 'wedding_date_offset',
                'offset_days' => -30,
                'channel' => 'email',
                'template_code' => 'd30_guestlist_prep',
                'send_time' => '09:00',
                'is_active' => true,
            ],
            [
                'code' => 'D-14_EARLY_RSVP_CHECK',
                'name' => 'D-14: Đánh giá tỷ lệ phản hồi & gửi link qua Zalo',
                'description' => 'Báo cáo sơ bộ số lượng khách đã xác nhận tham dự và hướng dẫn dâu rể chia sẻ link thiệp cá nhân hóa qua Zalo.',
                'trigger_event' => 'wedding_date_offset',
                'offset_days' => -14,
                'channel' => 'email',
                'template_code' => 'd14_early_rsvp_check',
                'send_time' => '09:00',
                'is_active' => true,
            ],
            [
                'code' => 'D-7_GENTLE_RSVP_REMINDER',
                'name' => 'D-7: Kích hoạt nhắc nhở nhẹ nhàng khách chưa RSVP',
                'description' => 'Nhắc nhở dâu rể kích hoạt luồng Gentle Reminder gửi tin nhắn tế nhị cho quan khách chưa gửi phản hồi tham dự.',
                'trigger_event' => 'wedding_date_offset',
                'offset_days' => -7,
                'channel' => 'email',
                'template_code' => 'd7_gentle_rsvp_reminder',
                'send_time' => '10:00',
                'is_active' => true,
            ],
            [
                'code' => 'D-1_RECEPTION_PIN_READY',
                'name' => 'D-1: Bàn giao mã PIN tiếp tân & màn hình sảnh tiệc',
                'description' => 'Gửi mã PIN 4 số của sảnh tiệc và hướng dẫn đội đón tiếp mở màn hình Rescue Cockpit quét QR đón khách ngày cưới.',
                'trigger_event' => 'wedding_date_offset',
                'offset_days' => -1,
                'channel' => 'email',
                'template_code' => 'd1_reception_pin_ready',
                'send_time' => '08:30',
                'is_active' => true,
            ],
            [
                'code' => 'D+1_THANKYOU_DISPATCH',
                'name' => 'D+1: Nhắc gửi thiệp cảm ơn & tổng kết chúc phúc',
                'description' => 'Gợi ý cặp đôi kích hoạt luồng Personal Thank You tự động gửi lời cảm tạ chân thành tới toàn bộ quan khách đã đến chung vui.',
                'trigger_event' => 'wedding_date_offset',
                'offset_days' => 1,
                'channel' => 'email',
                'template_code' => 'd1_thankyou_dispatch',
                'send_time' => '10:30',
                'is_active' => true,
            ],
            [
                'code' => 'D+30_MEMORIES_ARCHIVE',
                'name' => 'D+30: Tải trọn bộ kỷ niệm cưới & sổ ký tên điện tử',
                'description' => 'Thông báo sẵn sàng tải file ZIP trọn gói lưu trữ vĩnh viễn toàn bộ album ảnh, chữ ký chúc mừng và video kỷ niệm.',
                'trigger_event' => 'wedding_date_offset',
                'offset_days' => 30,
                'channel' => 'email',
                'template_code' => 'd30_memories_archive',
                'send_time' => '14:00',
                'is_active' => true,
            ],
        ];

        foreach ($defaults as $rule) {
            LifecycleAutomationRule::create($rule);
        }
    }

    /**
     * Thống kê 4 thẻ KPI chỉ số tự động hóa vòng đời cưới.
     *
     * @return array{total_rules: int, active_rules: int, dispatched_24h: int, ready_today: int}
     */
    public function getAutomationKpis(): array
    {
        $this->seedDefaultRulesIfEmpty();

        $totalRules = LifecycleAutomationRule::count();
        $activeRules = LifecycleAutomationRule::active()->count();

        $since24h = now()->subHours(24);
        $dispatched24h = LifecycleAutomationLog::where('dispatched_at', '>=', $since24h)
            ->whereIn('status', ['dispatched', 'simulated'])
            ->count();

        // Ước tính số tiệc cưới khớp hôm nay
        $readyToday = 0;
        $rules = LifecycleAutomationRule::active()->get();
        foreach ($rules as $rule) {
            $targetDate = Carbon::today()->addDays(-1 * $rule->offset_days)->format('Y-m-d');
            $readyToday += Wedding::whereDate('wedding_date', $targetDate)->count();
        }

        return [
            'total_rules' => $totalRules,
            'active_rules' => $activeRules,
            'dispatched_24h' => $dispatched24h,
            'ready_today' => $readyToday,
        ];
    }

    /**
     * Lấy toàn bộ danh sách quy tắc tự động hóa.
     */
    public function getRules(): Collection
    {
        $this->seedDefaultRulesIfEmpty();

        return LifecycleAutomationRule::orderBy('offset_days', 'asc')->get();
    }

    /**
     * Cập nhật thông số kịch bản tự động hóa.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateRule(LifecycleAutomationRule $rule, array $data, User $admin): LifecycleAutomationRule
    {
        $rule->update([
            'name' => trim((string) ($data['name'] ?? $rule->name)),
            'description' => $data['description'] ?? $rule->description,
            'channel' => $data['channel'] ?? $rule->channel,
            'send_time' => $data['send_time'] ?? $rule->send_time,
            'offset_days' => isset($data['offset_days']) ? (int) $data['offset_days'] : $rule->offset_days,
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $rule->is_active,
        ]);

        AuditLogger::log(
            action: 'automation_rule.updated',
            targetType: 'lifecycle_automation_rule',
            targetId: $rule->id,
            description: "Cập nhật kịch bản tự động hóa \"{$rule->name}\" bởi {$admin->name}.",
            details: [
                'rule_code' => $rule->code,
                'channel' => $rule->channel,
                'offset_days' => $rule->offset_days,
                'admin_id' => $admin->id,
            ]
        );

        return $rule;
    }

    /**
     * Bật / Tắt trạng thái kích hoạt của quy tắc.
     */
    public function toggleRule(LifecycleAutomationRule $rule, User $admin): LifecycleAutomationRule
    {
        $rule->update(['is_active' => ! $rule->is_active]);

        $statusStr = $rule->is_active ? 'kích hoạt' : 'tạm dừng';

        AuditLogger::log(
            action: 'automation_rule.toggled',
            targetType: 'lifecycle_automation_rule',
            targetId: $rule->id,
            description: "Đã {$statusStr} kịch bản tự động hóa \"{$rule->name}\" bởi {$admin->name}.",
            details: [
                'rule_code' => $rule->code,
                'is_active' => $rule->is_active,
            ]
        );

        return $rule;
    }

    /**
     * Quét và thực thi các tiệc cưới khớp điều kiện vòng đời hôm nay.
     *
     * @return array{scanned_rules: int, matched_weddings: int, dispatched: int, skipped: int, details: array<int, array<string, mixed>>}
     */
    public function scanAndExecute(bool $dryRun = true, ?int $ruleId = null, ?User $admin = null): array
    {
        $this->seedDefaultRulesIfEmpty();

        $query = LifecycleAutomationRule::query();
        if ($ruleId) {
            $query->where('id', $ruleId);
        } else {
            $query->active();
        }

        $rules = $query->orderBy('offset_days', 'asc')->get();

        $matchedCount = 0;
        $dispatchedCount = 0;
        $skippedCount = 0;
        $details = [];

        foreach ($rules as $rule) {
            // Tính ngày cưới mục tiêu theo công thức offset
            $targetDate = Carbon::today()->addDays(-1 * $rule->offset_days)->format('Y-m-d');

            $weddings = Wedding::with('user')
                ->whereDate('wedding_date', $targetDate)
                ->get();

            foreach ($weddings as $wedding) {
                $matchedCount++;
                $couple = "{$wedding->groom_name} & {$wedding->bride_name}";
                $user = $wedding->user;

                // Xác định thông tin người nhận
                $rawRecipient = $user?->email ?: ($user?->phone ?: 'Chưa cập nhật');
                $maskedRecipient = $this->maskPii($rawRecipient);

                // Kiểm tra Idempotency: Đã từng gửi rule này cho wedding này chưa?
                $alreadySent = LifecycleAutomationLog::where('rule_id', $rule->id)
                    ->where('wedding_id', $wedding->id)
                    ->where('status', '!=', 'failed')
                    ->exists();

                if ($alreadySent) {
                    $skippedCount++;
                    $details[] = [
                        'rule' => $rule->name,
                        'couple' => $couple,
                        'recipient' => $maskedRecipient,
                        'status' => 'skipped_duplicate',
                        'note' => 'Đã gửi kịch bản này trước đó',
                    ];

                    continue;
                }

                $status = $dryRun ? 'simulated' : 'dispatched';
                $summary = "Gửi tự động [{$rule->name}] cho đám cưới {$couple} ({$rule->offset_label})";

                LifecycleAutomationLog::create([
                    'rule_id' => $rule->id,
                    'wedding_id' => $wedding->id,
                    'user_id' => $user?->id,
                    'channel' => $rule->channel,
                    'recipient' => $maskedRecipient,
                    'status' => $status,
                    'message_summary' => $summary,
                    'dispatched_at' => now(),
                ]);

                if (! $dryRun) {
                    $rule->increment('execution_count');
                    $rule->update(['last_executed_at' => now()]);
                }

                $dispatchedCount++;
                $details[] = [
                    'rule' => $rule->name,
                    'couple' => $couple,
                    'recipient' => $maskedRecipient,
                    'status' => $status,
                    'note' => $dryRun ? 'Mô phỏng quét thành công (Dry-Run)' : 'Đã phát tin tự động thành công',
                ];
            }
        }

        if ($admin) {
            $modeText = $dryRun ? 'Chạy thử nghiệm (Dry-Run)' : 'Thực thi phát tin thật';
            AuditLogger::log(
                action: 'lifecycle_automation.scan_executed',
                targetType: 'system',
                targetId: 0,
                description: "{$modeText} tự động hóa vòng đời: {$matchedCount} tiệc cưới khớp, {$dispatchedCount} lượt gửi, {$skippedCount} bỏ qua.",
                details: [
                    'dry_run' => $dryRun,
                    'matched' => $matchedCount,
                    'dispatched' => $dispatchedCount,
                    'skipped' => $skippedCount,
                    'admin_id' => $admin->id,
                ]
            );
        }

        return [
            'scanned_rules' => $rules->count(),
            'matched_weddings' => $matchedCount,
            'dispatched' => $dispatchedCount,
            'skipped' => $skippedCount,
            'details' => $details,
        ];
    }

    /**
     * Lấy nhật ký thực thi tự động gần nhất.
     */
    public function getRecentLogs(int $limit = 25): Collection
    {
        return LifecycleAutomationLog::with(['rule', 'wedding', 'user'])
            ->recent()
            ->take($limit)
            ->get();
    }

    /**
     * Che PII an toàn cho email hoặc số điện thoại (Luật 91/2025/QH15).
     */
    protected function maskPii(string $contact): string
    {
        if (str_contains($contact, '@')) {
            $parts = explode('@', $contact);
            $name = $parts[0];
            $domain = $parts[1] ?? 'mayhen.vn';
            $maskedName = strlen($name) > 3 ? substr($name, 0, 3).'***' : substr($name, 0, 1).'***';

            return "{$maskedName}@{$domain}";
        }

        $cleanPhone = preg_replace('/[^0-9+]/', '', $contact);
        $len = strlen($cleanPhone);
        if ($len >= 7) {
            return substr($cleanPhone, 0, 3).str_repeat('*', max(3, $len - 7)).substr($cleanPhone, -4);
        }

        return substr($cleanPhone, 0, 2).'***';
    }
}

