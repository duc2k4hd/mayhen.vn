<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestDelivery;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Wedding;

class GentleReminderPlannerService
{
    /**
     * Bộ mẫu tin nhắn nhắc hẹn RSVP tế nhị theo 4 nhóm đối tượng văn hóa cưới Việt Nam.
     */
    public const PRESET_MESSAGES = [
        'respectful' => [
            'name' => 'Trân Trọng & Kính Cẩn',
            'target' => 'Người Lớn, Họ Hàng & Trưởng Bối',
            'desc' => 'Dạ thưa lịch thiệp, hỏi thăm sức khỏe và xin ý kiến tham dự để gia đình chuẩn bị chu đáo.',
            'template' => 'Dạ kính gửi {xung_ho} {ten_khach}, ngày vui của tụi con ({chu_re} & {co_dau}) đang tới gần ({ngay_cuoi}). Để gia đình tiện sắp xếp bàn tiệc đón tiếp chu đáo và trọn vẹn nhất, tụi con xin phép được hỏi thăm {xung_ho} có thu xếp chung vui cùng gia đình được không ạ? {xung_ho} có thể xem lại thiệp và phản hồi tại: {link_thiep} . Kính chúc {xung_ho} nhiều sức khỏe và bình an!',
        ],
        'intimate' => [
            'name' => 'Thân Mật & Ấm Áp',
            'target' => 'Bạn Thân, Bạn Học & Đồng Trang Lứa',
            'desc' => 'Giọng văn vui vẻ, nhắc chốt số lượng bàn để xếp ngồi cạnh nhau cho vui.',
            'template' => 'Alo {xung_ho} {ten_khach} ơi, ngày cưới của {chu_re} & {co_dau} ({ngay_cuoi}) sắp tới rồi nè! Tụi mình đang chốt số lượng mâm cỗ với sảnh tiệc để xếp chỗ ngồi cạnh nhau cho rôm rả, {xung_ho} bấm vào link xác nhận giúp tụi mình xem hôm đó có tới quẩy cùng được không nha: {link_thiep} . Hẹn gặp {xung_ho} hôm đó nhé!',
        ],
        'polite' => [
            'name' => 'Lịch Thiệp & Trang Nhã',
            'target' => 'Đồng Nghiệp, Khách Cơ Quan & Đối Tác',
            'desc' => 'Trang trọng, gọn gàng, tôn trọng thời gian của người nhận.',
            'template' => 'Kính gửi {xung_ho} {ten_khach}, hôn lễ của {chu_re} & {co_dau} sẽ được tổ chức vào {ngay_cuoi}. Tụi mình xin phép được gửi lại thông tin thiệp cưới để {xung_ho} tiện theo dõi lịch trình: {link_thiep} . Rất mong có dịp được đón tiếp {xung_ho} trong ngày trọng đại!',
        ],
        'urgent_deadline' => [
            'name' => 'Nhẹ Nhàng Nhắc Ngày Chốt Cỗ',
            'target' => 'Khách Chưa Trả Lời Khi Sát Hạn Chót',
            'desc' => 'Giải thích lý do cần chốt danh sách với nhà hàng trong 24-48 giờ tới.',
            'template' => 'Chào {xung_ho} {ten_khach}, chỉ còn ít ngày nữa là tới lễ cưới của {chu_re} & {co_dau} ({ngay_cuoi}). Do phía trung tâm tiệc cưới cần chốt số lượng thực đơn chính xác, tụi mình nhờ {xung_ho} dành 30 giây xác nhận giúp xem có tham dự được không tại: {link_thiep} . Cảm ơn {xung_ho} rất nhiều!',
        ],
    ];

    /**
     * Phân tích và lập kế hoạch nhắc nhở RSVP tế nhị trước ngày cưới.
     */
    public function plan(Wedding $wedding, ?Invitation $invitation = null, ?string $filterGroup = null): array
    {
        $invitation = $invitation ?? $wedding->invitations()->first();
        $groomName = $wedding->groom_name ?? 'Chú Rể';
        $brideName = $wedding->bride_name ?? 'Cô Dâu';
        $partyDate = $wedding->wedding_date ? $wedding->wedding_date->translatedFormat('l, d/m/Y') : 'ngày cưới';

        $guestQuery = $wedding->guests();
        if ($filterGroup && $filterGroup !== 'all') {
            $guestQuery->where('group_name', $filterGroup);
        }
        $allGuests = $guestQuery->get();

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

                $unsent[] = $this->buildReminderItem($guest, 'unsent', $msg, $guestUrl, $partyDate);
            } elseif (! $hasResponded) {
                // Khách đã gửi nhưng chưa phản hồi RSVP
                $msg = $isSenior
                    ? "Dạ kính gửi {$salutation} {$guest->name}, ngày vui của tụi con ({$groomName} & {$brideName}) đang tới gần ({$partyDate}). Để gia đình tiện sắp xếp bàn tiệc đón tiếp chu đáo nhất, tụi con xin phép được hỏi thăm {$salutation} có thu xếp chung vui cùng gia đình được không ạ? {$salutation} có thể xem lại thiệp tại: {$guestUrl} . Kính chúc {$salutation} nhiều sức khỏe!"
                    : "Alo {$salutation} {$guest->name} ơi, ngày cưới của {$groomName} & {$brideName} ({$partyDate}) sắp tới rồi nè! Tụi mình đang chốt số lượng bàn với nhà hàng để xếp chỗ ngồi cạnh nhau cho vui, {$salutation} xác nhận giúp tụi mình xem hôm đó có tới chung vui được không nha: {$guestUrl} . Hẹn gặp {$salutation}!";

                $unresponsive[] = $this->buildReminderItem($guest, 'unresponsive', $msg, $guestUrl, $partyDate);
            } elseif ($response->attending_status === 'attending' && empty($guest->table_name) && empty($guest->table_id)) {
                // Khách đã xác nhận tham dự nhưng chưa gán bàn
                $attendingUnseated[] = $this->buildReminderItem($guest, 'attending_unseated', "Đã xác nhận tham dự ({$response->adults_count} suất) - Cần gán vị trí bàn tiệc.", $guestUrl, $partyDate);
            }
        }

        // Lấy danh sách nhóm khách thực tế để làm bộ lọc
        $groups = $wedding->guests()
            ->whereNotNull('group_name')
            ->where('group_name', '!=', '')
            ->distinct()
            ->pluck('group_name')
            ->toArray();

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
                'reminded_total' => $allGuests->where('reminder_count', '>', 0)->count(),
            ],
            'recommended_time' => $recommendedTime,
            'groups' => $groups,
            'presets' => self::PRESET_MESSAGES,
            'reminders' => array_merge($unresponsive, $unsent, $attendingUnseated),
            'unresponsive_list' => $unresponsive,
            'unsent_list' => $unsent,
            'unseated_list' => $attendingUnseated,
        ];
    }

    /**
     * Ghi nhận lượt nhắc hẹn cho 1 khách mời.
     */
    public static function recordReminder(Guest $guest, string $channel = 'zalo', ?string $customMessage = null, ?User $user = null): Guest
    {
        $guest->reminder_count = ((int) $guest->reminder_count) + 1;
        $guest->reminded_at = now();
        $guest->save();

        GuestDelivery::create([
            'wedding_id' => $guest->wedding_id,
            'guest_id' => $guest->id,
            'channel' => 'reminder_'.$channel,
            'status' => 'delivered',
            'recipient' => $guest->phone ?? $guest->name,
            'message_body' => $customMessage ?? 'Nhắc hẹn RSVP tế nhị qua '.strtoupper($channel),
            'sent_at' => now(),
        ]);

        if (class_exists(AuditLogger::class)) {
            AuditLogger::log(
                'GUEST_REMINDER_RECORDED',
                'Guest',
                $guest->id,
                "Ghi nhận nhắc hẹn RSVP cho khách #{$guest->id} ({$guest->name}) lần thứ {$guest->reminder_count}",
                [
                    'guest_id' => $guest->id,
                    'wedding_id' => $guest->wedding_id,
                    'channel' => $channel,
                    'reminder_count' => $guest->reminder_count,
                    'user_id' => $user?->id,
                ]
            );
        }

        return $guest;
    }

    /**
     * Ghi nhận nhắc hẹn hàng loạt cho danh sách khách mời.
     *
     * @param  array<int, int>  $guestIds
     * @return int số lượng khách đã cập nhật
     */
    public static function batchRecordReminders(Wedding $wedding, array $guestIds, string $channel = 'zalo', ?User $user = null): int
    {
        $guests = Guest::where('wedding_id', $wedding->id)
            ->whereIn('id', $guestIds)
            ->get();

        $count = 0;
        foreach ($guests as $guest) {
            self::recordReminder($guest, $channel, null, $user);
            $count++;
        }

        return $count;
    }

    /**
     * Soạn thảo tin nhắn tùy chỉnh theo mẫu preset cho 1 khách mời cụ thể.
     */
    public static function renderMessageForGuest(Guest $guest, Wedding $wedding, ?Invitation $invitation, string $presetKey = 'intimate'): string
    {
        $preset = self::PRESET_MESSAGES[$presetKey] ?? self::PRESET_MESSAGES['intimate'];
        $template = $preset['template'];

        $guestUrl = ! empty($guest->guest_token)
            ? route('guest.concierge', ['guestToken' => $guest->guest_token])
            : ($invitation ? url('/thiep/'.$invitation->slug) : url('/'));

        $partyDate = $wedding->wedding_date ? $wedding->wedding_date->translatedFormat('l, d/m/Y') : 'ngày cưới';

        $replacements = [
            '{xung_ho}' => $guest->salutation ?: 'Bạn',
            '{ten_khach}' => $guest->name,
            '{chu_re}' => $wedding->groom_name ?? 'Chú Rể',
            '{co_dau}' => $wedding->bride_name ?? 'Cô Dâu',
            '{ngay_cuoi}' => $partyDate,
            '{link_thiep}' => $guestUrl,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Xuất danh sách khách cần nhắc hẹn ra file CSV UTF-8 BOM chuẩn tiếng Việt.
     */
    public static function exportReminderCsv(Wedding $wedding, ?Invitation $invitation = null): string
    {
        $planner = new self;
        $planData = $planner->plan($wedding, $invitation);
        $reminders = $planData['reminders'];

        $output = fopen('php://temp', 'r+');
        fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM

        fputcsv($output, [
            'ID',
            'Họ Tên Khách',
            'Xưng Hô',
            'Nhóm Khách',
            'Số Điện Thoại',
            'Phân Loại Nhắc Hẹn',
            'Số Lần Đã Nhắc',
            'Lần Nhắc Gần Nhất',
            'Mẫu Tin Nhắn Gợi Ý',
            'Đường Dẫn Mở Thiệp Cá Nhân',
        ]);

        foreach ($reminders as $item) {
            $guest = Guest::find($item['guest_id']);
            fputcsv($output, [
                $item['guest_id'],
                $item['guest_name'],
                $item['salutation'],
                $item['group_name'],
                $item['phone'] ?? '',
                match ($item['status_type']) {
                    'unresponsive' => 'Chưa phản hồi RSVP',
                    'unsent' => 'Chưa gửi thiệp',
                    'attending_unseated' => 'Đã RSVP chưa xếp bàn',
                    default => $item['status_type'],
                },
                $guest?->reminder_count ?? 0,
                $guest?->reminded_at ? $guest->reminded_at->format('d/m/Y H:i') : 'Chưa nhắc',
                $item['suggested_message'],
                $item['guest_url'] ?? '',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
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
     *     zalo_link: ?string,
     *     guest_url: string,
     *     reminder_count: int,
     *     reminded_at: ?string
     * }
     */
    private function buildReminderItem(Guest $guest, string $statusType, string $msg, string $guestUrl, string $partyDate): array
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
            'guest_url' => $guestUrl,
            'reminder_count' => (int) ($guest->reminder_count ?? 0),
            'reminded_at' => $guest->reminded_at ? $guest->reminded_at->format('d/m H:i') : null,
        ];
    }
}
