<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\Wedding;

class VendorBriefService
{
    /**
     * Tạo tài liệu tóm tắt tác nghiệp (Vendor Brief) theo vai trò.
     * Bảo vệ quyền riêng tư 100%: Tuyệt đối không để lộ SĐT hay tiền mừng cưới của khách.
     *
     * @return array{
     *     wedding_title: string,
     *     party_date: string,
     *     lunar_date: string,
     *     party_time: string,
     *     venue_name: string,
     *     venue_address: string,
     *     mc: array<string, mixed>,
     *     venue: array<string, mixed>,
     *     photographer: array<string, mixed>,
     *     generated_at: string
     * }
     */
    public function generate(Wedding $wedding, ?Invitation $invitation = null): array
    {
        $extra = is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?? []);
        $invitation = $invitation ?? $wedding->invitations()->first();
        $config = $invitation ? (is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode((string) $invitation->custom_config, true) ?? [])) : [];

        $groomName = $wedding->groom_name ?? 'Chú Rể';
        $brideName = $wedding->bride_name ?? 'Cô Dâu';
        $partyDate = $wedding->wedding_date ? $wedding->wedding_date->translatedFormat('l, d/m/Y') : 'Chưa đặt ngày';
        $lunarDate = trim((string) ($extra['event_party_lunar'] ?? ''));
        $partyTime = trim((string) ($extra['party_time'] ?? ($extra['event_party_time'] ?? '18:00')));
        $venueName = trim((string) ($wedding->venue_name ?? ($extra['party_venue'] ?? ($extra['event_party_venue'] ?? 'Sảnh Tiệc Cưới'))));
        $venueAddress = trim((string) ($wedding->venue_address ?? ($extra['event_party_address'] ?? '')));

        // 1. Dành cho MC & Dẫn Chương Trình (Master of Ceremonies)
        $groomFather = trim((string) ($extra['groom_father'] ?? ''));
        $groomMother = trim((string) ($extra['groom_mother'] ?? ''));
        $brideFather = trim((string) ($extra['bride_father'] ?? ''));
        $brideMother = trim((string) ($extra['bride_mother'] ?? ''));

        // Danh sách khách VIP / Bàn VIP cần xướng danh trang trọng (chỉ lấy tên và bàn, KHÔNG có SĐT)
        $vipGuests = $wedding->guests()
            ->where(function ($q) {
                $q->where('group_name', 'like', '%VIP%')
                    ->orWhere('table_name', 'like', '%VIP%');
            })
            ->select(['id', 'name', 'salutation', 'group_name', 'table_name'])
            ->get()
            ->map(fn (Guest $g) => [
                'name' => ($g->salutation ? $g->salutation.' ' : '').$g->name,
                'table' => $g->table_name ?: 'Chưa gán',
                'role_hint' => $g->group_name ?: 'Khách Quý',
            ])
            ->all();

        $mcBrief = [
            'role' => 'MC & Dẫn Chương Trình',
            'couple' => [
                'groom' => $groomName,
                'bride' => $brideName,
            ],
            'parents' => [
                'groom_side' => [
                    'father' => $groomFather ?: 'Ông (chưa cập nhật)',
                    'mother' => $groomMother ?: 'Bà (chưa cập nhật)',
                ],
                'bride_side' => [
                    'father' => $brideFather ?: 'Ông (chưa cập nhật)',
                    'mother' => $brideMother ?: 'Bà (chưa cập nhật)',
                ],
            ],
            'timeline' => [
                ['time' => $partyTime, 'event' => 'Đón tiếp quan khách & Chụp hình lưu niệm tại Backdrop'],
                ['time' => $this->addMinutesToTime($partyTime, 45), 'event' => 'Ổn định hội trường, mời quan khách vào bàn tiệc'],
                ['time' => $this->addMinutesToTime($partyTime, 60), 'event' => 'Khai mạc hôn lễ: Giới thiệu cô dâu chú rể & Song thân phụ mẫu'],
                ['time' => $this->addMinutesToTime($partyTime, 70), 'event' => 'Nghi thức dâng rượu tri ân phụ mẫu hai bên'],
                ['time' => $this->addMinutesToTime($partyTime, 80), 'event' => 'Nghi thức rót tháp rượu hạnh phúc & Cắt bánh cưới'],
                ['time' => $this->addMinutesToTime($partyTime, 85), 'event' => 'Cụng ly chúc phúc toàn thể hội trường & Khai tiệc mừng'],
                ['time' => $this->addMinutesToTime($partyTime, 105), 'event' => 'Đôi uyên ương cùng phụ mẫu đi chào bàn quan khách'],
            ],
            'vip_roster' => $vipGuests,
            'story_summary' => $wedding->story ? mb_substr(strip_tags($wedding->story), 0, 300).'...' : 'Tình yêu đơm hoa kết trái.',
        ];

        // 2. Dành cho Nhà Hàng & Ban Phục Vụ (Venue & Banquet Catering)
        $totalInvitedGuests = $wedding->guests()->count();
        $totalExpectedAttendees = (int) $wedding->guests()->sum('expected_attendees');
        $attendingResponses = GuestResponse::where('wedding_id', $wedding->id)->where('attending_status', 'attending')->count();
        $actualAttendeesSum = (int) GuestResponse::where('wedding_id', $wedding->id)->where('attending_status', 'attending')->sum('adults_count');

        // Thống kê bàn tiệc
        $tables = $wedding->guests()
            ->whereNotNull('table_name')
            ->where('table_name', '!=', '')
            ->selectRaw('table_name, count(*) as guests_count, sum(expected_attendees) as seats_count')
            ->groupBy('table_name')
            ->orderBy('table_name')
            ->get()
            ->map(fn ($t) => [
                'table_name' => $t->table_name,
                'seats_count' => (int) ($t->seats_count ?: $t->guests_count),
            ])
            ->all();

        // Nhu cầu ăn uống đặc biệt (chay, dị ứng)
        $dietaryList = GuestResponse::where('wedding_id', $wedding->id)
            ->whereNotNull('dietary_requirements')
            ->where('dietary_requirements', '!=', '')
            ->select(['guest_name', 'dietary_requirements'])
            ->get()
            ->map(fn ($r) => [
                'guest' => $r->guest_name,
                'requirement' => $r->dietary_requirements,
            ])
            ->all();

        $venueBrief = [
            'role' => 'Ban Quản Lý Sảnh & Phục Vụ Tiệc',
            'venue_name' => $venueName,
            'venue_address' => $venueAddress,
            'guest_stats' => [
                'total_invited' => $totalInvitedGuests,
                'expected_seats' => $totalExpectedAttendees ?: $totalInvitedGuests,
                'rsvp_confirmed_count' => $attendingResponses,
                'actual_confirmed_seats' => $actualAttendeesSum ?: $attendingResponses,
                'tables_count' => count($tables),
            ],
            'tables' => $tables,
            'dietary_requirements' => $dietaryList,
            'parking_guide' => $config['parking_guide'] ?? [],
        ];

        // 3. Dành cho Ekip Nhiếp Ảnh & Quay Phim (Photographer & Videographer)
        $photoShots = [
            ['moment' => 'First Look & Chuẩn Bị', 'desc' => 'Khoảnh khắc cô dâu chú rể lần đầu ngắm nhau trong trang phục cưới.'],
            ['moment' => 'Đón Khách Tại Photobooth', 'desc' => 'Chụp cùng quan khách tại backdrop và khu vực ký tên lưu bút.'],
            ['moment' => 'Bố Dắt Tay Cô Dâu', 'desc' => 'Khoảnh khắc xúc động khi bố trao tay con gái cho chú rể trên lễ đường.'],
            ['moment' => 'Trao Nhẫn & Lời Thề Nguyện', 'desc' => 'Cận cảnh trao nhẫn cưới và nụ hôn hạnh phúc của đôi uyên ương.'],
            ['moment' => 'Rót Rượu & Cắt Bánh', 'desc' => 'Góc máy trung toàn cảnh khói tháp rượu và bánh cưới lung linh.'],
            ['moment' => 'Tri Ân Cha Mẹ', 'desc' => 'Khoảnh khắc dâng rượu và những cái ôm nghẹn ngào cùng đấng sinh thành.'],
            ['moment' => 'Tung Hoa Cưới', 'desc' => 'Bắt trọn khoảnh khắc bó hoa cưới trên không trung và các bạn trẻ đón nhận.'],
            ['moment' => 'Chụp Lưu Niệm Từng Bàn', 'desc' => 'Đồng hành cùng cặp đôi chụp ảnh kỷ niệm tại từng bàn tiệc.'],
        ];

        $photoBrief = [
            'role' => 'Ekip Nhiếp Ảnh & Quay Phim',
            'key_shots' => $photoShots,
            'tables_count' => count($tables),
            'hall_name' => $venueName,
            'start_time' => $partyTime,
        ];

        return [
            'wedding_title' => "{$groomName} & {$brideName}",
            'party_date' => $partyDate,
            'lunar_date' => $lunarDate,
            'party_time' => $partyTime,
            'venue_name' => $venueName,
            'venue_address' => $venueAddress,
            'mc' => $mcBrief,
            'venue' => $venueBrief,
            'photographer' => $photoBrief,
            'generated_at' => now()->format('d/m/Y H:i'),
        ];
    }

    private function addMinutesToTime(string $timeStr, int $minutesToAdd): string
    {
        if (! str_contains($timeStr, ':')) {
            return $timeStr;
        }

        $parts = explode(':', trim($timeStr));
        $h = (int) ($parts[0] ?? 18);
        $m = (int) ($parts[1] ?? 0);

        $total = ($h * 60 + $m + $minutesToAdd) % (24 * 60);
        $newH = intdiv($total, 60);
        $newM = $total % 60;

        return sprintf('%02d:%02d', $newH, $newM);
    }
}
