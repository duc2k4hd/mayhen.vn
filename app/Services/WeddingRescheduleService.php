<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Models\WeddingRescheduleLog;
use Carbon\Carbon;

class WeddingRescheduleService
{
    /**
     * Lấy dữ liệu tổng quan phục vụ trung tâm thông báo dời lịch cưới.
     */
    public function getRescheduleData(Wedding $wedding, Invitation $invitation): array
    {
        $guests = $wedding->guests()->get();
        $responses = $wedding->guestResponses()->get();
        $history = $wedding->rescheduleLogs()->with('invitation')->get();

        return [
            'total_guests' => $guests->count(),
            'sent_guests' => $guests->where('is_sent', true)->count(),
            'attending_count' => $responses->where('attending_status', 'attending')->count(),
            'declined_count' => $responses->where('attending_status', 'declined')->count(),
            'needs_reconfirm_count' => $responses->where('needs_reconfirmation', true)->count(),
            'current_date' => $wedding->wedding_date,
            'current_venue' => $wedding->venue_name,
            'current_address' => $wedding->venue_address,
            'history' => $history,
        ];
    }

    /**
     * Thực hiện quy trình cập nhật ngày cưới mới và ghi nhận nhật ký (Luồng G).
     */
    public function executeReschedule(Wedding $wedding, Invitation $invitation, array $data): WeddingRescheduleLog
    {
        $oldDate = $wedding->wedding_date;
        $newDate = Carbon::parse($data['new_date']);

        $oldVenue = $wedding->venue_name;
        $newVenue = ! empty($data['new_venue']) ? trim((string) $data['new_venue']) : $oldVenue;

        $oldAddress = $wedding->venue_address;
        $newAddress = ! empty($data['new_venue_address']) ? trim((string) $data['new_venue_address']) : $oldAddress;

        $rsvpAction = $data['rsvp_action'] ?? 'keep_existing';
        $reason = ! empty($data['reason']) ? trim((string) $data['reason']) : null;

        $affectedCount = $wedding->guests()->count();

        // 1. Cập nhật ngày và địa điểm trên đám cưới
        $wedding->update([
            'wedding_date' => $newDate,
            'venue_name' => $newVenue,
            'venue_address' => $newAddress,
        ]);

        // 2. Đồng bộ ngày trên các sự kiện cưới (Wedding Events) nếu có
        $primaryEvents = $wedding->events()->whereIn('event_type', ['banquet', 'ceremony_groom', 'ceremony_bride'])->get();
        foreach ($primaryEvents as $event) {
            $event->update([
                'event_date' => $newDate,
                'venue_name' => $newVenue,
                'venue_address' => $newAddress,
            ]);
        }

        // 3. Xử lý trạng thái RSVP nếu yêu cầu xác nhận lại
        if ($rsvpAction === 'request_reconfirm') {
            GuestResponse::where('wedding_id', $wedding->id)
                ->update(['needs_reconfirmation' => true]);
        }

        // 4. Ghi nhận log dời lịch cưới
        $log = WeddingRescheduleLog::create([
            'wedding_id' => $wedding->id,
            'invitation_id' => $invitation->id,
            'old_date' => $oldDate,
            'new_date' => $newDate,
            'old_venue' => $oldVenue,
            'new_venue' => $newVenue,
            'reason' => $reason,
            'rsvp_action' => $rsvpAction,
            'affected_guests_count' => $affectedCount,
            'notified_guests_count' => 0,
        ]);

        AuditLogger::log(
            'WEDDING_RESCHEDULED',
            'wedding',
            $wedding->id,
            "Cặp đôi dời ngày cưới từ {$oldDate?->format('d/m/Y')} sang {$newDate->format('d/m/Y')}. Xử lý RSVP: {$log->rsvp_action_label}.",
            [
                'old_date' => $oldDate?->format('Y-m-d'),
                'new_date' => $newDate->format('Y-m-d'),
                'rsvp_action' => $rsvpAction,
                'affected_guests' => $affectedCount,
            ]
        );

        return $log;
    }

    /**
     * Tạo nội dung tin nhắn Zalo/SMS trang trọng gửi quan khách thông báo dời ngày cưới.
     */
    public function generateZaloMessage(Guest $guest, Wedding $wedding, ?WeddingRescheduleLog $log = null): string
    {
        $invitation = $wedding->invitations()->first();
        $slug = $invitation?->slug ?? 'thiep-cuoi';
        $personalLink = route('invitation.show', ['slug' => $slug, 'token' => $guest->guest_token]);

        $salutation = $guest->salutation ?: 'Kính mời';
        $name = $guest->name;
        $coupleNames = "{$wedding->groom_name} & {$wedding->bride_name}";

        $newDateStr = $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : '';
        $oldDateStr = $log?->old_date ? $log->old_date->format('d/m/Y') : '';
        $venue = $wedding->venue_name ?? 'tư gia';

        $msg = "{$salutation} {$name} thân mến,\n\n";
        $msg .= "Đôi uyên ương {$coupleNames} xin trân trọng gửi lời chào và lời chúc sức khỏe đến {$name}.\n\n";
        $msg .= "Do có sự điều chỉnh lịch trình để chuẩn bị trọn vẹn nhất cho ngày vui trọng đại, chúng mình xin phép thông báo dời ngày tổ chức Lễ Cưới:\n";

        if ($oldDateStr && $oldDateStr !== $newDateStr) {
            $msg .= "• Ngày cưới mới: {$newDateStr} (thay cho ngày cũ {$oldDateStr})\n";
        } else {
            $msg .= "• Ngày cưới chính thức: {$newDateStr}\n";
        }

        if ($venue) {
            $msg .= "• Địa điểm: {$venue}\n";
        }

        if ($log && $log->reason) {
            $msg .= "• Lý do: {$log->reason}\n";
        }

        $msg .= "\nKính mời {$name} xem lại chi tiết thiệp cưới và lịch trình cập nhật tại đường dẫn dưới đây:\n";
        $msg .= "👉 {$personalLink}\n\n";

        if ($log && $log->rsvp_action === 'request_reconfirm') {
            $msg .= "Rất mong {$name} dành chút thời gian bấm vào liên kết trên để xác nhận lại khả năng tham dự cùng chúng mình nhé!\n\n";
        }

        $msg .= "Rất mong nhận được sự cảm thông và hiện diện chúc phúc của {$name}.\n";
        $msg .= "Trân trọng,\n{$coupleNames}";

        return $msg;
    }
}
