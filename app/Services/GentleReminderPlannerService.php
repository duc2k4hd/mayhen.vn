<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\Wedding;

class GentleReminderPlannerService
{
    /**
     * Phân tích và lập kế hoạch nhắc nhở RSVP tế nhị trước ngày cưới.
     *
     * @return array{
     *     stats: array{
     *         total_guests: int,
     *         unresponsive_count: int,
     *         unsent_count: int,
     *         attending_unseated_count: int
     *     },
     *     recommended_time: array{
     *         label: string,
     *         time_windows: array<int, string>,
     *         tip: string
     *     },
     *     reminders: array<int, array{
     *         guest_id: int,
     *         guest_name: string,
     *         salutation: string,
     *         group_name: string,
     *         phone: ?string,
     *         status_type: string,
     *         suggested_message: string,
     *         zalo_link: ?string
     *     }>
     * }
     */
    public function plan(Wedding $wedding, ?Invitation $invitation = null): array
    {
        $invitation = $invitation ?? $wedding->invitations()->first();
        $groomName = $wedding->groom_name ?? 'Chú Rể';
        $brideName = $wedding->bride_name ?? 'Cô Dâu';
        $partyDate = $wedding->wedding_date ? $wedding->wedding_date->translatedFormat('l, d/m/Y') : 'ngày cưới';

        $allGuests = $wedding->guests()->get();
        $responses = GuestResponse::where('wedding_id', $wedding->id)->get()->keyBy('guest_id');

        $unresponsive = [];
        $unsent = [];
        $attendingUnseated = [];

        foreach ($allGuests as $guest) {
            $response = $responses->get($guest->id);
            $hasResponded = $response !== null;
            $isSent = (bool) $guest->is_sent;

            $guestUrl = ! empty($guest->guest_token)
                ? route('guest.concierge', ['guestToken' => $guest->guest_token])
                : ($invitation ? url('/thiep/'.$invitation->slug) : url('/'));

            $salutation = $guest->salutation ?: 'Bạn';
            $isSenior = in_array(mb_strtolower($salutation, 'UTF-8'), ['bác', 'chú', 'cô', 'dì', 'ông', 'bà', 'cụ'], true)
                || str_contains(mb_strtolower((string) $guest->group_name, 'UTF-8'), 'họ hàng')
                || str_contains(mb_strtolower((string) $guest->group_name, 'UTF-8'), 'người lớn');

            if (! $isSent) {
                // Khách chưa từng được gửi thiệp
                $msg = $isSenior
                    ? "Kính gửi {$salutation} {$guest->name}, gia đình tụi con xin trân trọng gửi thiệp mời hôn lễ của {$groomName} & {$brideName} vào {$partyDate}. Kính mời {$salutation} mở thiệp trực tuyến tại: {$guestUrl} . Rất mong được đón tiếp {$salutation}!"
                    : "Chào {$salutation} {$guest->name}, tụi mình xin gửi thiệp mời hôn lễ của {$groomName} & {$brideName} vào {$partyDate} nhé. Mời {$salutation} xem thiệp tại: {$guestUrl} . Mong sớm gặp {$salutation}!";

                $unsent[] = $this->buildReminderItem($guest, 'unsent', $msg);
            } elseif (! $hasResponded) {
                // Khách đã gửi nhưng chưa phản hồi RSVP
                $msg = $isSenior
                    ? "Dạ kính gửi {$salutation} {$guest->name}, ngày vui của tụi con ({$groomName} & {$brideName}) đang tới gần ({$partyDate}). Để gia đình tiện sắp xếp bàn tiệc đón tiếp chu đáo nhất, tụi con xin phép được hỏi thăm {$salutation} có thu xếp chung vui cùng gia đình được không ạ? {$salutation} có thể xem lại thiệp tại: {$guestUrl} . Kính chúc {$salutation} nhiều sức khỏe!"
                    : "Alo {$salutation} {$guest->name} ơi, ngày cưới của {$groomName} & {$brideName} ({$partyDate}) sắp tới rồi nè! Tụi mình đang chốt số lượng bàn với nhà hàng để xếp chỗ ngồi cạnh nhau cho vui, {$salutation} xác nhận giúp tụi mình xem hôm đó có tới chung vui được không nha: {$guestUrl} . Hẹn gặp {$salutation}!";

                $unresponsive[] = $this->buildReminderItem($guest, 'unresponsive', $msg);
            } elseif ($response->attending_status === 'attending' && empty($guest->table_name)) {
                // Khách đã xác nhận tham dự nhưng chưa gán bàn
                $attendingUnseated[] = $this->buildReminderItem($guest, 'attending_unseated', "Đã xác nhận tham dự ({$response->adults_count} suất) - Cần gán vị trí bàn tiệc.");
            }
        }

        // Đề xuất khung giờ gửi tin nhắn thích hợp theo văn hóa Việt Nam
        $recommendedTime = [
            'label' => 'Khung giờ vàng gửi tin nhắn tế nhị',
            'time_windows' => [
                'Trưa: 11:30 - 13:00 (Giờ nghỉ trưa thư giãn, không vướng bận công việc)',
                'Tối: 19:30 - 21:00 (Sau bữa cơm tối ấm cúng cùng gia đình)',
            ],
            'tip' => 'Tránh gửi vào đầu giờ sáng (08:00 - 10:00) khi mọi người bận họp hoặc sau 22:00 tránh làm phiền giấc ngủ của người lớn tuổi.',
        ];

        return [
            'stats' => [
                'total_guests' => $allGuests->count(),
                'unresponsive_count' => count($unresponsive),
                'unsent_count' => count($unsent),
                'attending_unseated_count' => count($attendingUnseated),
            ],
            'recommended_time' => $recommendedTime,
            'reminders' => array_merge($unresponsive, $unsent, $attendingUnseated),
        ];
    }

    /**
     * @return array{
     *     guest_id: int,
     *     guest_name: string,
     *     salutation: string,
     *     group_name: string,
     *     phone: ?string,
     *     status_type: string,
     *     suggested_message: string,
     *     zalo_link: ?string
     * }
     */
    private function buildReminderItem(Guest $guest, string $statusType, string $msg): array
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', (string) $guest->phone);
        $zaloLink = $cleanPhone ? "https://zalo.me/{$cleanPhone}" : null;

        return [
            'guest_id' => $guest->id,
            'guest_name' => $guest->name,
            'salutation' => $guest->salutation ?: 'Bạn',
            'group_name' => $guest->group_name ?: 'Khách Quý',
            'phone' => $guest->phone,
            'status_type' => $statusType,
            'suggested_message' => $msg,
            'zalo_link' => $zaloLink,
        ];
    }
}
