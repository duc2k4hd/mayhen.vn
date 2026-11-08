<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Wedding;

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

        if (! $invitation->is_published) {
            $issues['unpublished'] = [
                'type' => 'unpublished',
                'title' => 'Thiệp Cưới Chưa Được Xuất Bản',
                'severity' => 'critical',
                'description' => 'Thiệp cưới đang ở chế độ Riêng tư/Chưa xuất bản. Khách mở link sẽ thấy thông báo thiệp chưa phát hành.',
                'count' => $totalGuests,
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
        if ($totalGuests > 0) {
            $penalty = ($flaggedCount / $totalGuests) * 80;
            if (! $invitation->is_published) {
                $penalty += 20;
            }
            $readinessScore = (int) max(0, min(100, round(100 - $penalty)));
        }

        return [
            'readiness_score' => $readinessScore,
            'total_guests' => $totalGuests,
            'ready_guests_count' => $readyCount,
            'flagged_guests_count' => $flaggedCount,
            'is_invitation_published' => (bool) $invitation->is_published,
            'issues' => $issues,
            'etiquette_suggestions' => $etiquetteSuggestions,
        ];
    }
}
