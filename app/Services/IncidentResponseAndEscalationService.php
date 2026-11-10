<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\Wedding;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class IncidentResponseAndEscalationService
{
    /**
     * Mức độ nghiêm trọng của sự cố vận hành.
     */
    public const SEVERITY_SEV1 = 'sev1'; // Critical: Gián đoạn đám cưới, lỗi thanh toán hàng loạt, trắng thiệp

    public const SEVERITY_SEV2 = 'sev2'; // Major: Lỗi tính năng quan trọng (RSVP, nhạc, tải ảnh)

    public const SEVERITY_SEV3 = 'sev3'; // Minor: Lỗi hiển thị nhỏ, typography, thắc mắc tính năng

    /**
     * Tính toán thời hạn cam kết dịch vụ (SLA Response Deadline).
     */
    public static function calculateSlaDeadline(SupportTicket $ticket): Carbon
    {
        $baseTime = $ticket->created_at ?? now();

        // 1. Kiểm tra tiệc cưới VIP Concierge: diễn ra trong vòng 48 giờ tới
        $isVip48h = false;
        if ($ticket->user_id) {
            $isVip48h = Wedding::where('user_id', $ticket->user_id)
                ->whereBetween('wedding_date', [Carbon::today(), Carbon::today()->addDays(2)])
                ->exists();
        }

        // Hạn phản hồi SLA theo mức độ khẩn cấp
        if ($isVip48h || $ticket->priority === 'urgent') {
            return $baseTime->copy()->addMinutes(60); // 1 giờ
        }

        return match ($ticket->priority) {
            'high' => $baseTime->copy()->addHours(4),
            'medium' => $baseTime->copy()->addHours(12),
            'low' => $baseTime->copy()->addHours(24),
            default => $baseTime->copy()->addHours(12),
        };
    }

    /**
     * Quét và tự động leo thang các ticket vi phạm SLA (Auto-Escalation Engine).
     *
     * @return Collection<int, SupportTicket>
     */
    public static function checkAndEscalateTickets(): Collection
    {
        $overdueTickets = SupportTicket::query()
            ->whereIn('status', ['open', 'in_progress'])
            ->whereNotNull('sla_deadline_at')
            ->where('sla_deadline_at', '<', now())
            ->whereNull('escalated_at')
            ->get();

        foreach ($overdueTickets as $ticket) {
            $ticket->update([
                'escalated_at' => now(),
                'escalation_level' => 'manager',
                'escalation_reason' => 'Tự động leo thang: Quá hạn cam kết phản hồi SLA (hạn chót: '.$ticket->sla_deadline_at->format('d/m/Y H:i').')',
            ]);

            AuditLogger::log(
                'ticket_auto_escalated',
                'SupportTicket',
                $ticket->id,
                "Hệ thống tự động leo thang ticket [{$ticket->ticket_code}] lên Quản lý do vi phạm hạn SLA",
                [
                    'ticket_code' => $ticket->ticket_code,
                    'sla_deadline_at' => $ticket->sla_deadline_at->toIso8601String(),
                ]
            );
        }

        return $overdueTickets;
    }

    /**
     * Khai báo sự cố vận hành chính thức (Incident Declaration).
     */
    public static function declareIncident(SupportTicket $ticket, string $severity, string $reason, int $declaredById): void
    {
        $severity = in_array($severity, [self::SEVERITY_SEV1, self::SEVERITY_SEV2, self::SEVERITY_SEV3], true)
            ? $severity
            : self::SEVERITY_SEV2;

        $ticket->update([
            'is_incident' => true,
            'incident_severity' => $severity,
            'incident_declared_at' => now(),
            'incident_declared_by' => $declaredById,
            'priority' => 'urgent',
            'escalated_at' => $ticket->escalated_at ?? now(),
            'escalation_level' => 'super_admin',
            'escalation_reason' => 'Khai báo sự cố ['.strtoupper($severity)."]: {$reason}",
        ]);

        AuditLogger::log(
            'declare_operational_incident',
            'SupportTicket',
            $ticket->id,
            'Khai báo sự cố vận hành ['.strtoupper($severity)."] cho ticket [{$ticket->ticket_code}]. Lý do: {$reason}",
            [
                'ticket_code' => $ticket->ticket_code,
                'severity' => $severity,
                'reason' => $reason,
                'declared_by' => $declaredById,
            ]
        );
    }

    /**
     * Khép kín quy trình điều tra sự cố bằng biên bản Postmortem bắt buộc.
     */
    public static function resolveWithPostmortem(
        SupportTicket $ticket,
        string $rootCause,
        string $mitigationAction,
        string $postmortemNotes,
        int $resolvedById
    ): void {
        $ticket->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'root_cause' => trim($rootCause),
            'mitigation_action' => trim($mitigationAction),
            'postmortem_notes' => trim($postmortemNotes),
        ]);

        AuditLogger::log(
            'resolve_incident_with_postmortem',
            'SupportTicket',
            $ticket->id,
            "Đóng sự cố [{$ticket->ticket_code}] với biên bản Postmortem và xác nhận khắc phục",
            [
                'ticket_code' => $ticket->ticket_code,
                'root_cause' => $rootCause,
                'mitigation_action' => $mitigationAction,
                'resolved_by' => $resolvedById,
            ]
        );
    }

    /**
     * Nhãn hiển thị cấp độ sự cố tiếng Việt.
     */
    public static function getSeverityLabel(string $severity): string
    {
        return match ($severity) {
            self::SEVERITY_SEV1 => '🔴 SEV-1: Sự Cố Nghiêm Trọng (Critical)',
            self::SEVERITY_SEV2 => '🟠 SEV-2: Sự Cố Trọng Yếu (Major)',
            self::SEVERITY_SEV3 => '🟡 SEV-3: Sự Cố Nhẹ (Minor)',
            default => 'Không Phân Cấp',
        };
    }
}
