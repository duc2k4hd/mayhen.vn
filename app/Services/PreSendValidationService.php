<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Wedding;
use Carbon\Carbon;

class PreSendValidationService
{
    /**
     * Rà soát toàn bộ danh sách khách mời và phát hiện các rủi ro trước khi gửi thiệp.
     *
     * @return array{
     *     readiness_score: int,
     *     total_guests: int,
     *     ready_guests_count: int,
     *     flagged_guests_count: int,
     *     is_invitation_published: bool,
     *     issues: array<string, array{
     *         type: string,
     *         title: string,
     *         severity: string,
     *         description: string,
     *         count: int,
     *         guests: array<int, array{id: int, name: string, detail: string}>
     *     }>,
     *     etiquette_suggestions: array<string, string>
     * }
     */
    public function validate(Wedding $wedding, Invitation $invitation): array
    {
        $guests = $wedding->guests()->get();
        $totalGuests = $guests->count();

        $missingSalutation = [];
        $missingGroup = [];
        $invalidAttendees = [];
        $duplicateCandidates = [];

        $namesMap = [];
        $phonesMap = [];

        foreach ($guests as $g) {
            // 1. Thiếu danh xưng
            if (empty(trim((string) $g->salutation))) {
                $missingSalutation[] = [
                    'id' => $g->id,
                    'name' => $g->name,
                    'detail' => 'Chưa có xưng hô (Bác / Chú / Cô / Anh / Chị / Bạn)',
                ];
            }

            // 2. Thiếu phân nhóm
            if (empty(trim((string) $g->group_name))) {
                $missingGroup[] = [
                    'id' => $g->id,
                    'name' => $g->name,
                    'detail' => 'Chưa phân nhóm (sẽ không nhận được lời ngỏ riêng)',
                ];
            }

            // 3. Số lượng suất bất thường
            if ($g->expected_attendees <= 0) {
                $invalidAttendees[] = [
                    'id' => $g->id,
                    'name' => $g->name,
                    'detail' => 'Số người dự tiệc không hợp lệ (<= 0)',
                ];
            }

            // 4. Kiểm tra trùng lặp tên
            $normName = mb_strtolower(trim($g->name));
            if (isset($namesMap[$normName])) {
                $duplicateCandidates[] = [
                    'id' => $g->id,
                    'name' => $g->name,
                    'detail' => "Trùng tên với khách #{$namesMap[$normName]['id']}",
                ];
            } else {
                $namesMap[$normName] = ['id' => $g->id, 'name' => $g->name];
            }

            // 5. Kiểm tra trùng lặp số điện thoại
            if (! empty($g->phone)) {
                $cleanPhone = preg_replace('/[^0-9]/', '', (string) $g->phone);
                if (strlen($cleanPhone) >= 9) {
                    if (isset($phonesMap[$cleanPhone])) {
                        $duplicateCandidates[] = [
                            'id' => $g->id,
                            'name' => $g->name,
                            'detail' => "Trùng số điện thoại ({$g->phone}) với {$phonesMap[$cleanPhone]['name']}",
                        ];
                    } else {
                        $phonesMap[$cleanPhone] = ['id' => $g->id, 'name' => $g->name];
                    }
                }
            }
        }

        $issues = [];

        // Rà soát thông tin cốt lõi bên trong thiệp cưới trước khi gửi cho người thân / bạn bè
        $extra = is_array($wedding->extra_data)
            ? $wedding->extra_data
            : (json_decode((string) ($wedding->extra_data ?? '{}'), true) ?: []);

        $groomName = trim((string) ($wedding->groom_name ?? ''));
        $brideName = trim((string) ($wedding->bride_name ?? ''));
        $hasCoupleNames = $groomName !== '' && $brideName !== ''
            && mb_strtolower($groomName, 'UTF-8') !== 'chú rể'
            && mb_strtolower($brideName, 'UTF-8') !== 'cô dâu';

        $hasWeddingDate = ! empty($wedding->wedding_date);

        $venueAddress = trim((string) ($wedding->venue_address ?: ($extra['event_party_address'] ?? ($extra['party_venue'] ?? ''))));
        $hasVenueAddress = $venueAddress !== '';

        $mapLink = trim((string) ($extra['event_party_map_link'] ?? ($extra['party_map_url'] ?? '')));
        $hasMapLink = $mapLink !== '' && $mapLink !== '#' && ! str_starts_with($mapLink, '#map');

        $invitationChecks = [
            'is_published' => [
                'passed' => (bool) $invitation->is_published,
                'label' => 'Đã xuất bản thiệp công khai',
                'detail' => $invitation->is_published ? 'Khách mở link sẽ xem được ngay.' : 'Thiệp đang ở chế độ nháp/riêng tư.',
            ],
            'couple_names' => [
                'passed' => $hasCoupleNames,
                'label' => 'Tên Chú rể & Cô dâu',
                'detail' => $hasCoupleNames ? "{$groomName} & {$brideName}" : 'Chưa điền đầy đủ tên thật của cô dâu, chú rể.',
            ],
            'wedding_date' => [
                'passed' => $hasWeddingDate,
                'label' => 'Ngày cử hành hôn lễ',
                'detail' => $hasWeddingDate ? Carbon::parse($wedding->wedding_date)->format('d/m/Y') : 'Chưa chọn ngày cưới.',
            ],
            'venue_address' => [
                'passed' => $hasVenueAddress,
                'label' => 'Địa chỉ tổ chức tiệc',
                'detail' => $hasVenueAddress ? $venueAddress : 'Chưa điền địa chỉ nhà hàng hoặc tư gia.',
            ],
            'map_link' => [
                'passed' => $hasMapLink,
                'label' => 'Bản đồ Google Maps',
                'detail' => $hasMapLink ? 'Đã gắn link chỉ đường.' : 'Nên dán link Google Maps để khách tìm đường dễ dàng.',
            ],
        ];

        $isInvitationInfoReady = (bool) $invitation->is_published && $hasCoupleNames && $hasWeddingDate && $hasVenueAddress;

        if (! $invitation->is_published) {
            $issues['unpublished'] = [
                'type' => 'unpublished',
                'title' => 'Thiệp Cưới Chưa Được Xuất Bản',
                'severity' => 'critical',
                'description' => 'Thiệp cưới đang ở chế độ Riêng tư/Chưa xuất bản. Khách mở link sẽ thấy thông báo thiệp chưa phát hành.',
                'count' => max(1, $totalGuests),
                'guests' => [],
            ];
        }

        if (! $hasCoupleNames) {
            $issues['missing_couple_names'] = [
                'type' => 'missing_couple_names',
                'title' => 'Thiếu Tên Cô Dâu / Chú Rể Trong Thiệp',
                'severity' => 'critical',
                'description' => 'Hãy vào Studio Chỉnh Sửa để điền tên thật của cô dâu và chú rể trước khi gửi link.',
                'count' => 1,
                'guests' => [],
            ];
        }

        if (! $hasWeddingDate) {
            $issues['missing_wedding_date'] = [
                'type' => 'missing_wedding_date',
                'title' => 'Chưa Chọn Ngày Cưới Trong Thiệp',
                'severity' => 'critical',
                'description' => 'Thiệp chưa có ngày tổ chức hôn lễ. Hãy cập nhật ngày cưới trong Studio trước khi gửi.',
                'count' => 1,
                'guests' => [],
            ];
        }

        if (! $hasVenueAddress) {
            $issues['missing_venue_address'] = [
                'type' => 'missing_venue_address',
                'title' => 'Chưa Điền Địa Chỉ Tổ Chức Tiệc',
                'severity' => 'warning',
                'description' => 'Khách mời cần biết địa chỉ nhà hàng hoặc tư gia để tới chung vui.',
                'count' => 1,
                'guests' => [],
            ];
        }

        if (count($missingSalutation) > 0) {
            $issues['missing_salutation'] = [
                'type' => 'missing_salutation',
                'title' => 'Khách Mời Chưa Có Danh Xưng Lễ Nghi',
                'severity' => 'warning',
                'description' => 'Khách chưa có từ xưng hô tôn kính (như Bác, Chú, Cô, Dì, Anh, Chị) dễ gây cảm giác thiếu chu đáo khi nhận thiệp.',
                'count' => count($missingSalutation),
                'guests' => $missingSalutation,
            ];
        }

        if (count($missingGroup) > 0) {
            $issues['missing_group'] = [
                'type' => 'missing_group',
                'title' => 'Khách Mời Chưa Được Phân Nhóm',
                'severity' => 'info',
                'description' => 'Khách chưa được xếp vào nhóm nào (Bạn Bè, Họ Hàng, Đồng Nghiệp...) nên sẽ không nhận được lời ngỏ cá nhân hóa.',
                'count' => count($missingGroup),
                'guests' => $missingGroup,
            ];
        }

        if (count($duplicateCandidates) > 0) {
            $issues['duplicate_candidates'] = [
                'type' => 'duplicate_candidates',
                'title' => 'Khách Mời Có Nguy Cơ Bị Trùng Lặp',
                'severity' => 'warning',
                'description' => 'Phát hiện các dòng khách mời trùng tên hoặc trùng số điện thoại.',
                'count' => count($duplicateCandidates),
                'guests' => $duplicateCandidates,
            ];
        }

        if (count($invalidAttendees) > 0) {
            $issues['invalid_attendees'] = [
                'type' => 'invalid_attendees',
                'title' => 'Số Lượng Suất Mời Bất Thường',
                'severity' => 'critical',
                'description' => 'Khách có số lượng suất tham dự dự kiến bằng 0 hoặc không hợp lệ.',
                'count' => count($invalidAttendees),
                'guests' => $invalidAttendees,
            ];
        }

        // Gợi ý danh xưng lễ nghi văn hóa Việt
        $etiquetteSuggestions = [
            'Gia Đình & Họ Hàng' => 'Bác / Cô / Chú / Dì / Cậu / Mợ',
            'Bạn Bè' => 'Bạn / Cậu',
            'Đồng Nghiệp' => 'Anh / Chị / Bạn',
            'Khách VIP' => 'Kính gửi Quý khách / Bác / Chú',
            'Bà Con Lối Xóm' => 'Bác / Cô / Chú / Anh / Chị',
        ];

        // Tính điểm sẵn sàng
        $flaggedGuestIds = array_unique(array_merge(
            array_column($missingSalutation, 'id'),
            array_column($missingGroup, 'id'),
            array_column($invalidAttendees, 'id'),
            array_column($duplicateCandidates, 'id')
        ));
        $flaggedCount = count($flaggedGuestIds);
        $readyCount = max(0, $totalGuests - $flaggedCount);

        $readinessScore = 100;
        if (! $invitation->is_published) {
            $readinessScore -= 20;
        }
        if (! $hasCoupleNames) {
            $readinessScore -= 20;
        }
        if (! $hasWeddingDate) {
            $readinessScore -= 15;
        }
        if (! $hasVenueAddress) {
            $readinessScore -= 15;
        }
        if ($totalGuests > 0) {
            $readinessScore -= (int) round(($flaggedCount / $totalGuests) * 30);
        }
        $readinessScore = (int) max(0, min(100, $readinessScore));

        return [
            'readiness_score' => $readinessScore,
            'total_guests' => $totalGuests,
            'ready_guests_count' => $readyCount,
            'flagged_guests_count' => $flaggedCount,
            'is_invitation_published' => (bool) $invitation->is_published,
            'is_invitation_info_ready' => $isInvitationInfoReady,
            'invitation_checks' => $invitationChecks,
            'issues' => $issues,
            'etiquette_suggestions' => $etiquetteSuggestions,
        ];
    }
}
