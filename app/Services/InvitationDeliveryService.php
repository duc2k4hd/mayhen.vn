<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestDelivery;
use App\Models\Invitation;
use App\Models\Wedding;
use Carbon\Carbon;
use Illuminate\Support\Str;

class InvitationDeliveryService
{
    /**
     * Mẫu lời mời tiêu chuẩn theo kênh.
     */
    public function getDefaultTemplate(string $channel = 'copy'): string
    {
        return match ($channel) {
            'zalo' => 'Kính gửi {salutation} {guest_name}, {couple_names} trân trọng gửi thiệp cưới online kính mời {salutation} tới chung vui ngày trọng đại vào ngày {wedding_date} tại {venue_name}. Kính mời {salutation} mở thiệp và gửi lời chúc/xác nhận tham dự tại link: {invitation_url}',
            'sms' => '{couple_names} tran trong kinh moi {salutation} {guest_name} toi du le cuoi ngay {wedding_date} tai {venue_name}. Xem thiep online tai: {invitation_url}',
            'email' => 'Thư Mời Cưới: {couple_names} trân trọng kính mời {salutation} {guest_name} tới tham dự lễ thành hôn vào ngày {wedding_date} tại {venue_name}. Chi tiết thiệp cưới và xác nhận tham dự: {invitation_url}',
            default => "Kính gửi {salutation} {guest_name}, {couple_names} trân trọng kính mời {salutation} tới chung vui trong ngày trọng đại của chúng mình vào ngày {wedding_date} tại {venue_name}.\n\nKính mời {salutation} xem thiệp cưới và xác nhận tham dự tại: {invitation_url}\n\nRất hân hạnh được đón tiếp {salutation}!",
        };
    }

    /**
     * Cá nhân hóa nội dung tin nhắn thiệp cưới với các trường merge.
     */
    public function buildPersonalizedMessage(
        Guest $guest,
        Wedding $wedding,
        ?Invitation $invitation = null,
        ?string $template = null
    ): string {
        $template = $template ?: $this->getDefaultTemplate('copy');

        $salutation = trim((string) ($guest->salutation ?: 'Quý khách'));
        $guestName = trim((string) $guest->name);
        $groomName = trim((string) $wedding->groom_name);
        $brideName = trim((string) $wedding->bride_name);
        $coupleNames = "{$groomName} & {$brideName}";

        $weddingDate = '';
        if ($wedding->wedding_date) {
            try {
                $weddingDate = Carbon::parse($wedding->wedding_date)->format('d/m/Y');
            } catch (\Throwable) {
                $weddingDate = (string) $wedding->wedding_date;
            }
        }

        $venueName = trim((string) ($wedding->venue_name ?: 'Trung tâm tiệc cưới'));

        // Xác định URL thiệp có token khách mời
        $invitationUrl = '';
        if ($invitation) {
            $params = ['slug' => $invitation->slug];
            if (! empty($guest->guest_token)) {
                $params['token'] = $guest->guest_token;
            }
            $invitationUrl = route('invitation.show', $params);
        }

        $replacements = [
            '{salutation}' => $salutation,
            '{guest_name}' => $guestName,
            '{groom_name}' => $groomName,
            '{bride_name}' => $brideName,
            '{couple_names}' => $coupleNames,
            '{wedding_date}' => $weddingDate,
            '{venue_name}' => $venueName,
            '{invitation_url}' => $invitationUrl,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Gửi / Ghi nhận phân phối thiệp cho 1 khách mời.
     */
    public function dispatchSingle(
        Guest $guest,
        string $channel = 'copy',
        ?string $customMessage = null,
        ?Invitation $invitation = null,
        ?string $batchId = null
    ): GuestDelivery {
        $wedding = $guest->wedding;
        if (! $invitation && $wedding) {
            $invitation = $wedding->invitations()->first();
        }

        $body = $this->buildPersonalizedMessage($guest, $wedding, $invitation, $customMessage);

        $recipient = match ($channel) {
            'sms', 'zalo' => $guest->phone,
            'email' => $guest->phone && filter_var($guest->phone, FILTER_VALIDATE_EMAIL) ? $guest->phone : null,
            default => $guest->phone,
        };

        $delivery = GuestDelivery::create([
            'wedding_id' => $wedding->id,
            'guest_id' => $guest->id,
            'channel' => in_array($channel, ['copy', 'zalo', 'sms', 'email']) ? $channel : 'copy',
            'status' => 'sent',
            'recipient' => $recipient,
            'message_body' => $body,
            'sent_at' => now(),
            'batch_id' => $batchId,
        ]);

        $guest->update([
            'is_sent' => true,
            'sent_at' => now(),
        ]);

        AuditLogger::log(
            'INVITATION_DELIVERY_SENT',
            'Guest',
            $guest->id,
            "Gửi thiệp cưới cho khách {$guest->name} qua kênh {$channel}"
        );

        return $delivery;
    }

    /**
     * Gửi / Ghi nhận phân phối thiệp hàng loạt.
     */
    public function dispatchBatch(
        Wedding $wedding,
        array $guestIds,
        string $channel = 'copy',
        ?string $customTemplate = null,
        ?Invitation $invitation = null
    ): array {
        $batchId = 'batch_'.Str::random(12);

        $guests = Guest::where('wedding_id', $wedding->id)
            ->whereIn('id', $guestIds)
            ->get();

        $successful = 0;
        $failed = 0;

        foreach ($guests as $guest) {
            try {
                $this->dispatchSingle($guest, $channel, $customTemplate, $invitation, $batchId);
                $successful++;
            } catch (\Throwable) {
                $failed++;
            }
        }

        return [
            'batch_id' => $batchId,
            'total' => $guests->count(),
            'successful' => $successful,
            'failed' => $failed,
        ];
    }

    /**
     * Báo cáo thống kê hiệu suất phân phối thiệp.
     */
    public function getDeliveryStats(Wedding $wedding): array
    {
        $totalGuests = $wedding->guests()->count();
        $sentGuests = $wedding->guests()->where('is_sent', true)->count();
        $unsentGuests = max(0, $totalGuests - $sentGuests);

        $openedAfterSent = $wedding->guests()
            ->where('is_sent', true)
            ->where(function ($query) {
                $query->where('open_count', '>', 0)
                    ->orWhereNotNull('opened_at');
            })
            ->count();

        $openRate = $sentGuests > 0 ? round(($openedAfterSent / $sentGuests) * 100, 1) : 0;

        $channelStats = GuestDelivery::where('wedding_id', $wedding->id)
            ->selectRaw('channel, count(*) as count')
            ->groupBy('channel')
            ->pluck('count', 'channel')
            ->toArray();

        return [
            'total_guests' => $totalGuests,
            'sent_guests' => $sentGuests,
            'unsent_guests' => $unsentGuests,
            'opened_after_sent' => $openedAfterSent,
            'open_rate' => $openRate,
            'channel_breakdown' => [
                'copy' => $channelStats['copy'] ?? 0,
                'zalo' => $channelStats['zalo'] ?? 0,
                'sms' => $channelStats['sms'] ?? 0,
                'email' => $channelStats['email'] ?? 0,
            ],
            'total_deliveries' => array_sum($channelStats),
        ];
    }
}
