<?php

namespace App\Services;

use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\Wedding;

class PersonalThankYouService
{
    /**
     * Phân tích và tạo kế hoạch lời cảm ơn sau cưới cá nhân hóa cho từng nhóm khách.
     *
     * @return array{
     *     stats: array{
     *         total_guests: int,
     *         attended_count: int,
     *         gift_only_count: int,
     *         general_count: int
     *     },
     *     album_link: string,
     *     messages: array<int, array{
     *         guest_id: int,
     *         guest_name: string,
     *         salutation: string,
     *         group_name: string,
     *         category: string,
     *         category_label: string,
     *         suggested_message: string,
     *         phone: ?string,
     *         zalo_link: ?string
     *     }>
     * }
     */
    public function buildThankYouPlan(Wedding $wedding, ?Invitation $invitation = null): array
    {
        $invitation = $invitation ?? $wedding->invitations()->first();
        $coupleName = trim(($wedding->groom_name ?? 'Chú rể').' & '.($wedding->bride_name ?? 'Cô dâu'));

        $albumLink = $invitation
            ? route('photo_drop.show', ['slug' => $invitation->slug])
            : url('/');

        $allGuests = $wedding->guests()->get();
        $responses = GuestResponse::where('wedding_id', $wedding->id)->get()->keyBy('guest_id');

        $attendedCount = 0;
        $giftOnlyCount = 0;
        $generalCount = 0;

        $messages = [];

        foreach ($allGuests as $guest) {
            $response = $responses->get($guest->id);
            $hasCheckIn = $guest->checked_in_at !== null;
            $isAttendingResponse = $response && $response->attending_status === 'attending';
            $isDeclinedResponse = $response && $response->attending_status === 'declined';

            // Phân loại nhóm đối tượng
            if ($hasCheckIn || $isAttendingResponse) {
                $category = 'attended';
                $categoryLabel = 'Đã đến chung vui';
                $attendedCount++;
            } elseif ($isDeclinedResponse) {
                $category = 'gift_only';
                $categoryLabel = 'Gửi quà / chúc phúc từ xa';
                $giftOnlyCount++;
            } else {
                $category = 'general';
                $categoryLabel = 'Khách mời chung';
                $generalCount++;
            }

            $salutation = ! empty($guest->salutation) ? trim($guest->salutation) : 'Bạn';
            $guestName = $guest->name;
            $groupName = ! empty($guest->group_name) ? trim($guest->group_name) : 'Khách mời';

            // Tạo tin nhắn cá nhân hóa theo nhóm và văn hóa xưng hô
            $suggestedMessage = $this->composeMessage(
                category: $category,
                salutation: $salutation,
                guestName: $guestName,
                groupName: $groupName,
                coupleName: $coupleName,
                albumLink: $albumLink
            );

            $phoneClean = preg_replace('/[^0-9]/', '', (string) $guest->phone);
            $zaloLink = ! empty($phoneClean) ? 'https://zalo.me/'.$phoneClean : null;

            $messages[] = [
                'guest_id' => $guest->id,
                'guest_name' => $guestName,
                'salutation' => $salutation,
                'group_name' => $groupName,
                'category' => $category,
                'category_label' => $categoryLabel,
                'suggested_message' => $suggestedMessage,
                'phone' => $guest->phone,
                'zalo_link' => $zaloLink,
            ];
        }

        return [
            'stats' => [
                'total_guests' => $allGuests->count(),
                'attended_count' => $attendedCount,
                'gift_only_count' => $giftOnlyCount,
                'general_count' => $generalCount,
            ],
            'album_link' => $albumLink,
            'messages' => $messages,
        ];
    }

    /**
     * Soạn nội dung lời cảm ơn phù hợp với danh xưng và nhóm khách.
     */
    private function composeMessage(
        string $category,
        string $salutation,
        string $guestName,
        string $groupName,
        string $coupleName,
        string $albumLink
    ): string {
        // Tránh lặp danh xưng nếu tên khách đã gõ sẵn danh xưng (ví dụ: Salutation "Bác" + Name "Bác Trần Văn Phú")
        $cleanName = $guestName;
        if (! empty($salutation) && mb_stripos($guestName, $salutation, 0, 'UTF-8') === 0) {
            $cleanName = trim(mb_substr($guestName, mb_strlen($salutation, 'UTF-8'), null, 'UTF-8'));
        }
        $fullGreeting = ! empty($cleanName) ? "{$salutation} {$cleanName}" : $salutation;

        $groupLower = mb_strtolower($groupName, 'UTF-8');
        $isSeniorFamily = str_contains($groupLower, 'gia đình') ||
                          str_contains($groupLower, 'họ hàng') ||
                          str_contains($groupLower, 'bác') ||
                          str_contains($groupLower, 'chú') ||
                          str_contains($groupLower, 'cô') ||
                          str_contains($groupLower, 'vip');

        if ($category === 'attended') {
            if ($isSeniorFamily) {
                return "Kính gửi {$fullGreeting}, vợ chồng con {$coupleName} xin trân trọng gửi lời cảm ơn sâu sắc nhất tới {$salutation} đã dành thời gian quý báu đến chung vui và chúc phúc cho hôn lễ của chúng con. Sự hiện diện ấm áp của {$salutation} là niềm vinh hạnh to lớn của gia đình hai bên. Kính chúc {$salutation} và toàn gia luôn dồi dào sức khỏe, an khang thịnh vượng! Con xin gửi {$salutation} album ảnh kỷ niệm của ngày vui tại: {$albumLink}";
            }

            return "Gửi {$fullGreeting} thân mến! Cảm ơn {$salutation} rất nhiều vì đã đến chung vui và quẩy hết mình cùng vợ chồng mình ({$coupleName}) trong ngày cưới vừa qua! Có {$salutation} bên cạnh, bữa tiệc thật sự trọn vẹn và tràn ngập tiếng cười. Mời {$salutation} xem lại những khoảnh khắc siêu đáng nhớ của ngày hôm đó tại album kỷ niệm: {$albumLink}. Hẹn sớm gặp lại {$salutation} nhé!";
        }

        if ($category === 'gift_only') {
            if ($isSeniorFamily) {
                return "Kính gửi {$fullGreeting}, vợ chồng con {$coupleName} xin gửi lời cảm ơn chân thành nhất tới {$salutation} vì đã gửi lời chúc phúc và món quà ý nghĩa cho ngày cưới của chúng con. Dù {$salutation} không thể trực tiếp tham dự, tình cảm ấm áp của {$salutation} chúng con luôn vô cùng trân quý. Kính chúc {$salutation} vạn sự như ý và nhiều sức khỏe! Con xin kính gửi {$salutation} album ảnh hôn lễ tại: {$albumLink}";
            }

            return "Gửi {$fullGreeting}! Vợ chồng mình ({$coupleName}) xin cảm ơn {$salutation} rất nhiều vì món quà và lời chúc mừng ngày cưới thật ý nghĩa. Dù tiếc là không thể gặp mặt {$salutation} trực tiếp trong buổi tiệc, nhưng tình cảm của {$salutation} chúng mình luôn ghi nhớ. Mời {$salutation} cùng ngắm nhìn những khoảnh khắc ngày cưới tại: {$albumLink}. Hôm nào rảnh mình hẹn cà phê tạ lễ nhé!";
        }

        // Trường hợp general
        if ($isSeniorFamily) {
            return "Kính gửi {$fullGreeting}, vợ chồng con {$coupleName} xin gửi lời tri ân chân thành nhất tới {$salutation} và gia đình đã luôn yêu thương, đồng hành và chúc phúc cho ngày hạnh phúc của chúng con. Kính chúc {$salutation} dồi dào sức khỏe và bình an. Con xin gửi {$salutation} album ảnh ngày cưới tại: {$albumLink}";
        }

        return "Gửi {$fullGreeting}! Vợ chồng mình ({$coupleName}) xin gửi lời cảm ơn chân thành đến {$salutation} vì những tình cảm và sự quan tâm dành cho ngày cưới của chúng mình. Mời {$salutation} cùng xem lại những khoảnh khắc hạnh phúc trong album kỷ niệm tại: {$albumLink}. Chúc {$salutation} luôn ngập tràn niềm vui!";
    }
}
