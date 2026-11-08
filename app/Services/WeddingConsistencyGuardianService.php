<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Wedding;

class WeddingConsistencyGuardianService
{
    /**
     * Kiểm tra tính nhất quán và toàn vẹn của dữ liệu đám cưới trên mọi phân hệ.
     *
     * @return array{
     *     score: int,
     *     status: string,
     *     issues: array<int, array{severity: string, field: string, field_target?: string, message: string, suggestion: string}>,
     *     checks_passed: int,
     *     checks_total: int
     * }
     */
    public function inspect(Wedding $wedding, ?Invitation $invitation = null): array
    {
        $issues = [];
        $checksTotal = 6;
        $checksPassed = 0;

        $extra = is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?? []);

        // 1. Kiểm tra ngày tổ chức hôn lễ và hạn chót RSVP
        if ($wedding->wedding_date) {
            $checksPassed++;

            // Kiểm tra tính hợp lý của hạn chót RSVP so với ngày cưới
            $rsvpDeadline = trim((string) ($extra['rsvp_deadline'] ?? ($extra['event_party_rsvp_deadline'] ?? '')));
            if ($rsvpDeadline !== '') {
                $weddingTimestamp = strtotime((string) $wedding->wedding_date);
                $deadlineTimestamp = strtotime($rsvpDeadline);
                if ($weddingTimestamp && $deadlineTimestamp && $deadlineTimestamp > $weddingTimestamp) {
                    $issues[] = [
                        'severity' => 'critical',
                        'field' => 'rsvp_deadline',
                        'field_target' => 'input_rsvp_deadline',
                        'message' => 'Hạn chót phản hồi RSVP ('.$rsvpDeadline.') đang được đặt sau ngày tổ chức hôn lễ.',
                        'suggestion' => 'Vui lòng chỉnh lại hạn chót RSVP trước ngày cưới tối thiểu 3 - 7 ngày để kịp chốt số lượng mâm cỗ.',
                    ];
                }
            }
        } else {
            $issues[] = [
                'severity' => 'critical',
                'field' => 'wedding_date',
                'field_target' => 'input_wedding_date',
                'message' => 'Chưa cấu hình ngày tổ chức hôn lễ chính thức.',
                'suggestion' => 'Vui lòng chọn ngày cưới để hệ thống kích hoạt đồng hồ đếm ngược và lịch hẹn.',
            ];
        }

        // 2. Kiểm tra tên địa điểm và sảnh tiệc
        $venue = trim((string) ($wedding->venue_name ?? ($extra['party_venue'] ?? ($extra['event_party_venue'] ?? ''))));
        $address = trim((string) ($wedding->venue_address ?? ($extra['event_party_address'] ?? '')));
        if ($venue !== '' && $address !== '') {
            $checksPassed++;
        } elseif ($venue !== '' || $address !== '') {
            $issues[] = [
                'severity' => 'warning',
                'field' => 'venue_address',
                'field_target' => 'input_venue_address',
                'message' => 'Thông tin sảnh tiệc chưa đầy đủ (đã có tên sảnh nhưng thiếu địa chỉ chi tiết hoặc ngược lại).',
                'suggestion' => 'Vui lòng điền cả tên sảnh và số nhà, tên đường để khách mời dễ dàng tìm kiếm.',
            ];
        } else {
            $issues[] = [
                'severity' => 'critical',
                'field' => 'venue_name',
                'field_target' => 'input_venue_name',
                'message' => 'Chưa có thông tin địa điểm và sảnh tổ chức tiệc cưới.',
                'suggestion' => 'Khách mời sẽ không biết đến dự tiệc tại trung tâm tiệc cưới nào.',
            ];
        }

        // 3. Kiểm tra tính hợp lệ của liên kết bản đồ Google Maps
        $mapUrl = trim((string) ($extra['event_party_map_url'] ?? ($extra['google_map_url'] ?? '')));
        if ($mapUrl !== '') {
            if (str_contains($mapUrl, 'google.com/maps') || str_contains($mapUrl, 'maps.app.goo.gl') || str_contains($mapUrl, 'goo.gl/maps')) {
                $checksPassed++;
            } else {
                $issues[] = [
                    'severity' => 'warning',
                    'field' => 'event_party_map_url',
                    'field_target' => 'input_event_party_map_url',
                    'message' => 'Liên kết bản đồ không thuộc định dạng chuẩn của Google Maps.',
                    'suggestion' => 'Khuyên dùng liên kết chia sẻ từ ứng dụng Google Maps để khách mở app chỉ đường 1 chạm.',
                ];
            }
        } else {
            $issues[] = [
                'severity' => 'info',
                'field' => 'event_party_map_url',
                'field_target' => 'input_event_party_map_url',
                'message' => 'Chưa gắn liên kết Google Maps chỉ đường.',
                'suggestion' => 'Gắn link Google Maps giúp khách lớn tuổi và khách ở xa đi lại thuận tiện hơn.',
            ];
        }

        // 4. Kiểm tra tài khoản ngân hàng mừng cưới (Nhà Trai & Nhà Gái)
        $groomBankAcc = trim((string) ($extra['groom_bank_account'] ?? ''));
        $groomBankName = trim((string) ($extra['groom_bank_name'] ?? ''));
        $brideBankAcc = trim((string) ($extra['bride_bank_account'] ?? ''));
        $brideBankName = trim((string) ($extra['bride_bank_name'] ?? ''));

        $bankIssuesCount = 0;
        if ($groomBankAcc !== '' && $groomBankName === '') {
            $bankIssuesCount++;
            $issues[] = [
                'severity' => 'warning',
                'field' => 'groom_bank_name',
                'field_target' => 'input_groom_bank_name',
                'message' => 'Đã có số tài khoản Nhà Trai nhưng chưa chọn ngân hàng thụ hưởng.',
                'suggestion' => 'Vui lòng chọn tên ngân hàng (ví dụ: Vietcombank, MB Bank) để mã VietQR tạo chính xác.',
            ];
        } elseif ($groomBankAcc !== '' && strlen($groomBankAcc) < 6) {
            $bankIssuesCount++;
            $issues[] = [
                'severity' => 'warning',
                'field' => 'groom_bank_account',
                'field_target' => 'input_groom_bank_account',
                'message' => 'Số tài khoản Nhà Trai có độ dài bất thường (< 6 chữ số).',
                'suggestion' => 'Vui lòng kiểm tra lại để tránh khách gửi mừng cưới nhầm số tài khoản.',
            ];
        }

        if ($brideBankAcc !== '' && $brideBankName === '') {
            $bankIssuesCount++;
            $issues[] = [
                'severity' => 'warning',
                'field' => 'bride_bank_name',
                'field_target' => 'input_bride_bank_name',
                'message' => 'Đã có số tài khoản Nhà Gái nhưng chưa chọn ngân hàng thụ hưởng.',
                'suggestion' => 'Vui lòng chọn tên ngân hàng (ví dụ: Vietcombank, Techcombank) để mã VietQR Nhà Gái hiển thị chính xác.',
            ];
        } elseif ($brideBankAcc !== '' && strlen($brideBankAcc) < 6) {
            $bankIssuesCount++;
            $issues[] = [
                'severity' => 'warning',
                'field' => 'bride_bank_account',
                'field_target' => 'input_bride_bank_account',
                'message' => 'Số tài khoản Nhà Gái có độ dài bất thường (< 6 chữ số).',
                'suggestion' => 'Vui lòng kiểm tra lại để tránh khách gửi mừng cưới nhầm số tài khoản.',
            ];
        }

        if ($bankIssuesCount === 0) {
            $checksPassed++;
        }

        // 5. Kiểm tra thông tin phụ mẫu hai bên
        $hasGroomParents = ! empty($wedding->groom_parents) || ! empty($extra['groom_father']) || ! empty($extra['groom_mother']);
        $hasBrideParents = ! empty($wedding->bride_parents) || ! empty($extra['bride_father']) || ! empty($extra['bride_mother']);
        if ($hasGroomParents && $hasBrideParents) {
            $checksPassed++;
        } else {
            $issues[] = [
                'severity' => 'info',
                'field' => 'parents',
                'field_target' => 'input_groom_parents',
                'message' => 'Chưa điền đủ tên phụ mẫu hai bên gia đình Nhà Trai và Nhà Gái.',
                'suggestion' => 'Điền đầy đủ tên phụ mẫu giúp thiệp cưới trang trọng và đúng lễ nghi truyền thống.',
            ];
        }

        // 6. Kiểm tra mã PIN tiếp tân (dành cho ngày cưới)
        $config = $invitation ? (is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode((string) $invitation->custom_config, true) ?? [])) : [];
        $checkinPin = $config['checkin_pin'] ?? '123456';
        if ($checkinPin === '123456' || $checkinPin === '000000') {
            $issues[] = [
                'severity' => 'info',
                'field' => 'checkin_pin',
                'field_target' => 'input_checkin_pin',
                'message' => 'Mã PIN Cổng Tiếp Tân đang dùng mã mặc định dễ đoán ('.$checkinPin.').',
                'suggestion' => 'Đổi mã PIN riêng biệt trước ngày cưới để bảo vệ danh sách bàn tiệc tốt hơn.',
            ];
        } else {
            $checksPassed++;
        }

        $score = (int) round(($checksPassed / $checksTotal) * 100);

        return [
            'score' => $score,
            'status' => $score >= 80 ? 'excellent' : ($score >= 50 ? 'needs_attention' : 'critical'),
            'issues' => $issues,
            'checks_passed' => $checksPassed,
            'checks_total' => $checksTotal,
        ];
    }

    /**
     * So Sánh Khác Biệt & Báo Cáo Tác Động Khi Sửa Ngày Giờ Thiệp Đã Gửi (Publish Diff & Impact Report - TASK-VAL-09).
     *
     * @param  array<string, mixed>  $newData
     * @return array{
     *     has_critical_changes: bool,
     *     impacted_guests_count: int,
     *     impacted_rsvp_count: int,
     *     warning_level: string,
     *     changes: array<int, array{field: string, label: string, old: ?string, new: ?string}>,
     *     advice: string
     * }
     */
    public function evaluatePublishImpact(Wedding $wedding, array $newData): array
    {
        $changes = [];
        $extra = is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?? []);
        $newExtra = is_array($newData['extra_data'] ?? null) ? $newData['extra_data'] : [];

        // 1. So sánh ngày cưới
        $oldDate = $wedding->wedding_date ? $wedding->wedding_date->format('Y-m-d') : null;
        $newDate = ! empty($newData['wedding_date']) ? substr((string) $newData['wedding_date'], 0, 10) : null;
        if ($oldDate && $newDate && $oldDate !== $newDate) {
            $changes[] = [
                'field' => 'wedding_date',
                'label' => 'Ngày tổ chức hôn lễ',
                'old' => $oldDate,
                'new' => $newDate,
            ];
        }

        // 2. So sánh giờ tiệc cưới
        $oldTime = trim((string) ($extra['party_time'] ?? ($extra['event_party_time'] ?? '')));
        $newTime = trim((string) ($newExtra['party_time'] ?? ($newExtra['event_party_time'] ?? ($newData['party_time'] ?? ''))));
        if ($oldTime !== '' && $newTime !== '' && $oldTime !== $newTime) {
            $changes[] = [
                'field' => 'party_time',
                'label' => 'Giờ khai tiệc / Đón khách',
                'old' => $oldTime,
                'new' => $newTime,
            ];
        }

        // 3. So sánh tên sảnh tiệc / địa điểm
        $oldVenue = trim((string) ($wedding->venue_name ?? ($extra['party_venue'] ?? ($extra['event_party_venue'] ?? ''))));
        $newVenue = trim((string) ($newData['venue_name'] ?? ($newExtra['party_venue'] ?? ($newExtra['event_party_venue'] ?? ''))));
        if ($oldVenue !== '' && $newVenue !== '' && $oldVenue !== $newVenue) {
            $changes[] = [
                'field' => 'venue_name',
                'label' => 'Trung tâm / Sảnh tiệc',
                'old' => $oldVenue,
                'new' => $newVenue,
            ];
        }

        // 4. So sánh địa chỉ sảnh tiệc
        $oldAddr = trim((string) ($wedding->venue_address ?? ($extra['event_party_address'] ?? '')));
        $newAddr = trim((string) ($newData['venue_address'] ?? ($newExtra['event_party_address'] ?? '')));
        if ($oldAddr !== '' && $newAddr !== '' && $oldAddr !== $newAddr) {
            $changes[] = [
                'field' => 'venue_address',
                'label' => 'Địa chỉ tổ chức tiệc cưới',
                'old' => $oldAddr,
                'new' => $newAddr,
            ];
        }

        // 5. So sánh tài khoản ngân hàng mừng cưới
        $oldGroomBank = trim((string) ($extra['groom_bank_account'] ?? ''));
        $newGroomBank = trim((string) ($newExtra['groom_bank_account'] ?? ''));
        if ($oldGroomBank !== '' && $newGroomBank !== '' && $oldGroomBank !== $newGroomBank) {
            $changes[] = [
                'field' => 'groom_bank_account',
                'label' => 'Số tài khoản ngân hàng Nhà Trai',
                'old' => $oldGroomBank,
                'new' => $newGroomBank,
            ];
        }

        $oldBrideBank = trim((string) ($extra['bride_bank_account'] ?? ''));
        $newBrideBank = trim((string) ($newExtra['bride_bank_account'] ?? ''));
        if ($oldBrideBank !== '' && $newBrideBank !== '' && $oldBrideBank !== $newBrideBank) {
            $changes[] = [
                'field' => 'bride_bank_account',
                'label' => 'Số tài khoản ngân hàng Nhà Gái',
                'old' => $oldBrideBank,
                'new' => $newBrideBank,
            ];
        }

        $hasCriticalChanges = count($changes) > 0;
        $totalGuests = $wedding->guests()->count();
        $attendingGuests = $wedding->guestResponses()->where('attending_status', 'attending')->count();

        $warningLevel = 'none';
        $advice = 'Không phát hiện thay đổi trọng yếu nào ảnh hưởng đến lịch trình của quan khách.';

        if ($hasCriticalChanges) {
            $hasDateOrVenueChange = collect($changes)->contains(fn ($c) => in_array($c['field'], ['wedding_date', 'venue_name', 'venue_address'], true));
            if ($hasDateOrVenueChange) {
                $warningLevel = 'critical';
                $advice = "Cảnh báo cao độ: Bạn đang thay đổi ngày cưới hoặc địa điểm sảnh tiệc. Có {$totalGuests} khách mời trong danh sách (trong đó {$attendingGuests} khách đã xác nhận tham dự) có thể bị đi nhầm ngày hoặc nhầm địa điểm. Bạn nên gửi tin nhắn thông báo điều chỉnh ngay sau khi lưu.";
            } else {
                $warningLevel = 'warning';
                $advice = "Lưu ý: Bạn đang điều chỉnh giờ tiệc hoặc số tài khoản mừng cưới. Có {$totalGuests} khách mời có thể bị ảnh hưởng bởi thay đổi này.";
            }
        }

        return [
            'has_critical_changes' => $hasCriticalChanges,
            'impacted_guests_count' => $totalGuests,
            'impacted_rsvp_count' => $attendingGuests,
            'warning_level' => $warningLevel,
            'changes' => $changes,
            'advice' => $advice,
        ];
    }

    /**
     * Bảng Đánh Giá Độ Sẵn Sàng Ngày Cưới (Wedding Readiness Checklist - TASK-VAL-12).
     * Đánh giá 7 tiêu chí sống còn trước ngày diễn ra hôn lễ.
     *
     * @return array{
     *     score: int,
     *     passed_count: int,
     *     total_count: int,
     *     status: string,
     *     items: array<int, array{key: string, title: string, passed: bool, description: string, action_url: string, action_text: string}>
     * }
     */
    public function calculateReadinessChecklist(Wedding $wedding, ?Invitation $invitation = null): array
    {
        $invitation = $invitation ?? $wedding->invitations()->first();
        $extra = is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?? []);
        $config = $invitation ? (is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode((string) $invitation->custom_config, true) ?? [])) : [];

        $items = [];

        // 1. Xuất bản thiệp công khai
        $isPublished = (bool) ($invitation ? $invitation->is_published : false);
        $items[] = [
            'key' => 'publish',
            'title' => 'Xuất bản thiệp cưới công khai',
            'passed' => $isPublished,
            'description' => $isPublished
                ? 'Thiệp đã được kích hoạt xuất bản và sẵn sàng gửi tới khách mời.'
                : 'Thiệp đang ở trạng thái bản nháp, cần bật xuất bản để khách mở được liên kết.',
            'action_url' => $invitation ? route('account.invitations.settings', $invitation->id) : '#',
            'action_text' => 'Cài đặt xuất bản',
        ];

        // 2. Thời gian & Địa điểm sảnh tiệc
        $hasDate = ! empty($wedding->wedding_date);
        $venueName = trim((string) ($wedding->venue_name ?? ($extra['party_venue'] ?? ($extra['event_party_venue'] ?? ''))));
        $venueAddress = trim((string) ($wedding->venue_address ?? ($extra['event_party_address'] ?? '')));
        $hasVenue = $venueName !== '' && $venueAddress !== '';
        $partyTime = trim((string) ($extra['party_time'] ?? ($extra['event_party_time'] ?? '')));
        $dateTimeVenuePassed = $hasDate && $hasVenue && $partyTime !== '';
        $items[] = [
            'key' => 'date_time_venue',
            'title' => 'Thời gian & Địa điểm hôn lễ',
            'passed' => $dateTimeVenuePassed,
            'description' => $dateTimeVenuePassed
                ? 'Đã cấu hình đầy đủ ngày cưới, giờ khai tiệc và địa chỉ trung tâm tiệc cưới.'
                : 'Còn thiếu ngày cưới, giờ đón khách hoặc địa chỉ chi tiết sảnh tiệc.',
            'action_url' => $invitation ? route('invitation.create', ['edit' => $invitation->slug]) : '#',
            'action_text' => 'Chỉnh sửa Studio',
        ];

        // 3. Liên kết Google Maps
        $mapUrl = trim((string) ($extra['event_party_map_url'] ?? ($extra['google_map_url'] ?? '')));
        $hasValidMap = $mapUrl !== '' && (str_contains($mapUrl, 'google.com/maps') || str_contains($mapUrl, 'maps.app.goo.gl') || str_contains($mapUrl, 'goo.gl/maps'));
        $items[] = [
            'key' => 'maps_navigation',
            'title' => 'Định vị Google Maps sảnh tiệc',
            'passed' => $hasValidMap,
            'description' => $hasValidMap
                ? 'Đã gắn liên kết Google Maps chuẩn giúp khách mở ứng dụng chỉ đường 1 chạm.'
                : 'Chưa có liên kết Google Maps chỉ đường tới sảnh tiệc cưới.',
            'action_url' => $invitation ? route('invitation.create', ['edit' => $invitation->slug]) : '#',
            'action_text' => 'Gắn link Maps',
        ];

        // 4. Hộp mừng cưới & VietQR
        $hasBank = ! empty($extra['groom_bank_account']) || ! empty($extra['bride_bank_account']) || ! empty($wedding->bank_account_number);
        $items[] = [
            'key' => 'vietqr_banking',
            'title' => 'Hộp mừng cưới & Mã VietQR',
            'passed' => $hasBank,
            'description' => $hasBank
                ? 'Đã thiết lập tài khoản ngân hàng để tạo mã VietQR mừng cưới phương xa.'
                : 'Chưa cấu hình số tài khoản mừng cưới cho Nhà Trai hoặc Nhà Gái.',
            'action_url' => $invitation ? route('invitation.create', ['edit' => $invitation->slug]) : '#',
            'action_text' => 'Thêm tài khoản',
        ];

        // 5. Danh sách khách mời
        $guestCount = $wedding->guests()->count();
        $hasGuests = $guestCount > 0;
        $items[] = [
            'key' => 'guest_roster',
            'title' => 'Danh sách khách mời & RSVP',
            'passed' => $hasGuests,
            'description' => $hasGuests
                ? "Đã khởi tạo danh sách gồm {$guestCount} khách mời sẵn sàng nhận thiệp cá nhân hóa."
                : 'Chưa có khách mời nào trong danh sách. Hãy thêm khách hoặc nhập từ file Excel.',
            'action_url' => $invitation ? route('guests.index', $invitation->id) : '#',
            'action_text' => 'Quản lý khách',
        ];

        // 6. Sơ đồ xếp bàn tiệc
        $hasSeating = $wedding->guests()->whereNotNull('table_name')->where('table_name', '!=', '')->exists();
        $items[] = [
            'key' => 'seating_planner',
            'title' => 'Sơ đồ phân bổ bàn tiệc',
            'passed' => $hasSeating,
            'description' => $hasSeating
                ? 'Đã phân bổ bàn tiệc cho khách mời, hỗ trợ tiếp tân đón khách chu đáo.'
                : 'Chưa xếp bàn tiệc cho khách. Khách có thể bối rối khi tìm chỗ ngồi trong sảnh.',
            'action_url' => $invitation ? route('seating.index', $invitation->id) : '#',
            'action_text' => 'Xếp bàn tiệc',
        ];

        // 7. Bộ dự phòng sự cố & Mã PIN tiếp tân
        $checkinPin = $config['checkin_pin'] ?? '123456';
        $isPinSafe = $checkinPin !== '123456' && $checkinPin !== '000000';
        $items[] = [
            'key' => 'resilience_kit',
            'title' => 'Phương án dự phòng & Mã PIN tiếp tân',
            'passed' => $isPinSafe,
            'description' => $isPinSafe
                ? 'Mã PIN bảo vệ cổng tiếp tân an toàn và Bộ Dự Phòng Ngày Cưới đã sẵn sàng in ấn.'
                : 'Mã PIN tiếp tân đang dùng mặc định ('.$checkinPin.'). Nên đổi mã PIN và in sẵn Bộ Dự Phòng.',
            'action_url' => $invitation ? route('account.invitations.resilience_kit', $invitation->id) : '#',
            'action_text' => 'Bộ dự phòng',
        ];

        $totalCount = count($items);
        $passedCount = collect($items)->where('passed', true)->count();
        $score = (int) round(($passedCount / $totalCount) * 100);

        return [
            'score' => $score,
            'passed_count' => $passedCount,
            'total_count' => $totalCount,
            'status' => $score >= 85 ? 'ready' : ($score >= 50 ? 'needs_attention' : 'incomplete'),
            'items' => $items,
        ];
    }

    /**
     * Bộ Phát Hiện Xung Đột Lịch Trình Lễ Cưới & Tiệc Cưới Hai Nhà (Dual-Ceremony Collision Detector - TASK-VAL-14).
     * Phát hiện:
     * 1. Xung đột thời gian giữa Lễ Vu Quy (Nhà Gái) và Lễ Thành Hôn (Nhà Trai) quá sát nhau (< 90 phút).
     * 2. Xung đột giữa Lễ Gia Tiên và Giờ Khai Tiệc Cưới (trùng giờ hoặc giãn cách < 60 phút).
     * 3. Xung đột lệch chi nhánh giữa Tên sảnh tiệc và Đường dẫn Google Maps (đặc biệt các chuỗi White Palace, Adora, Asiana...).
     * 4. Khách mời họ hàng bị gán nhầm sự kiện.
     *
     * @return array{
     *     has_collisions: bool,
     *     highest_severity: string,
     *     collisions_count: int,
     *     collisions: array<int, array{type: string, severity: string, title: string, message: string, suggestion: string}>
     * }
     */
    public function detectCeremonyCollisions(Wedding $wedding, ?Invitation $invitation = null): array
    {
        $collisions = [];
        $extra = is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?? []);

        // 1. Phân tích thời gian các sự kiện
        $brideCeremonyTime = trim((string) ($extra['ceremony_time'] ?? ''));
        $groomCeremonyTime = trim((string) ($extra['ceremony_groom_time'] ?? ''));
        $partyTime = trim((string) ($extra['party_time'] ?? ($extra['event_party_time'] ?? '')));

        $brideMinutes = $this->timeToMinutes($brideCeremonyTime);
        $groomMinutes = $this->timeToMinutes($groomCeremonyTime);
        $partyMinutes = $this->timeToMinutes($partyTime);

        // A. Kiểm tra xung đột giữa Lễ Vu Quy và Lễ Thành Hôn
        if ($brideMinutes !== null && $groomMinutes !== null) {
            $diffMinutes = abs($groomMinutes - $brideMinutes);
            if ($diffMinutes === 0) {
                $collisions[] = [
                    'type' => 'ceremony_overlap',
                    'severity' => 'critical',
                    'title' => 'Trùng giờ Lễ Vu Quy và Lễ Thành Hôn',
                    'message' => "Lễ Vu Quy (Nhà Gái) và Lễ Thành Hôn (Nhà Trai) đều đang được đặt cùng một thời điểm ({$brideCeremonyTime}).",
                    'suggestion' => 'Vui lòng sắp xếp giờ đón dâu và giờ làm lễ tại hai gia đình so le nhau để hai họ có thời gian di chuyển trang trọng.',
                ];
            } elseif ($diffMinutes < 90) {
                $collisions[] = [
                    'type' => 'ceremony_tight_schedule',
                    'severity' => 'warning',
                    'title' => 'Khoảng cách giữa hai lễ gia tiên quá gấp',
                    'message' => "Lễ Vu Quy ({$brideCeremonyTime}) và Lễ Thành Hôn ({$groomCeremonyTime}) chỉ cách nhau {$diffMinutes} phút.",
                    'suggestion' => 'Khuyên nên giãn cách tối thiểu 90 - 120 phút để dự phòng kẹt xe và đảm bảo đoàn rước dâu kịp giờ hoàng đạo.',
                ];
            }
        }

        // B. Kiểm tra xung đột giữa Lễ Gia Tiên và Giờ Khai Tiệc
        $latestCeremonyMinutes = max(array_filter([$brideMinutes, $groomMinutes], fn ($m) => $m !== null) ?: [null]);
        if ($latestCeremonyMinutes !== null && $partyMinutes !== null) {
            if ($latestCeremonyMinutes > $partyMinutes) {
                $collisions[] = [
                    'type' => 'ceremony_after_party',
                    'severity' => 'critical',
                    'title' => 'Lễ gia tiên diễn ra sau giờ khai tiệc',
                    'message' => 'Lễ gia tiên tại nhà đang được thiết lập sau giờ khai tiệc tại nhà hàng tiệc cưới.',
                    'suggestion' => 'Vui lòng kiểm tra lại giờ làm lễ gia tiên (buổi sáng/trưa) và giờ đón khách tại trung tâm tiệc cưới (buổi chiều/tối).',
                ];
            } elseif ($partyMinutes - $latestCeremonyMinutes < 60) {
                $gap = $partyMinutes - $latestCeremonyMinutes;
                $collisions[] = [
                    'type' => 'ceremony_to_party_rushed',
                    'severity' => 'warning',
                    'title' => 'Khoảng cách giữa lễ gia tiên và tiệc cưới quá ngắn',
                    'message' => "Lễ gia tiên kết thúc quá sát giờ đón khách tại nhà hàng (chỉ cách {$gap} phút).",
                    'suggestion' => 'Cô dâu chú rể cần tối thiểu 60 - 90 phút để di chuyển tới sảnh tiệc, dặm lại trang điểm và thay trang phục đón khách.',
                ];
            }
        }

        // C. Kiểm tra nghi vấn lệch chi nhánh giữa Tên sảnh tiệc và Link Google Maps
        $venueName = mb_strtolower((string) ($wedding->venue_name ?? ($extra['party_venue'] ?? ($extra['event_party_venue'] ?? ''))), 'UTF-8');
        $mapUrl = mb_strtolower((string) ($extra['event_party_map_url'] ?? ($extra['google_map_url'] ?? '')), 'UTF-8');

        if ($mapUrl !== '') {
            $multiBranchVenues = [
                'white palace' => [
                    'hoàng văn thụ' => ['hoang-van-thu', 'hoang_van_thu', 'hvt', '194-hoang-van-thu'],
                    'phạm văn đồng' => ['pham-van-dong', 'pham_van_dong', 'pvd', '108-pham-van-dong'],
                ],
                'adora' => [
                    'center' => ['431-hoang-van-thu', 'adora-center'],
                    'grand' => ['nguyen-kiem', 'adora-grand'],
                    'dynasty' => ['le-dai-hanh', 'adora-dynasty'],
                    'riverside' => ['ben-van-don', 'adora-riverside'],
                    'premium' => ['nguyen-van-linh', 'adora-premium'],
                ],
                'asiana plaza' => [
                    'bình thạnh' => ['phan-dang-luu', 'binh-thanh'],
                    'tân phú' => ['vuon-lai', 'tan-phu'],
                ],
            ];

            foreach ($multiBranchVenues as $brand => $branches) {
                if (str_contains($venueName, $brand)) {
                    foreach ($branches as $branchKey => $keywords) {
                        if (str_contains($venueName, $branchKey)) {
                            // Tên sảnh là chi nhánh A, kiểm tra xem mapUrl có dính keyword của chi nhánh B khác không
                            foreach ($branches as $otherBranchKey => $otherKeywords) {
                                if ($otherBranchKey !== $branchKey) {
                                    foreach ($otherKeywords as $kw) {
                                        if (str_contains($mapUrl, $kw)) {
                                            $collisions[] = [
                                                'type' => 'venue_map_branch_mismatch',
                                                'severity' => 'critical',
                                                'title' => 'Cảnh báo lệch chi nhánh Google Maps',
                                                'message' => "Tên sảnh tiệc ghi chi nhánh '{$branchKey}' nhưng link Google Maps dường như đang dẫn tới chi nhánh '{$otherBranchKey}'.",
                                                'suggestion' => 'Vui lòng mở liên kết Google Maps kiểm tra lại ngay để tránh khách mời đi nhầm địa điểm sảnh tiệc.',
                                            ];
                                            break 3;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // D. Kiểm tra khách họ hàng bị gán nhầm sự kiện
        $mismatchedGuestsCount = 0;
        $brideAddress = trim((string) ($extra['ceremony_address'] ?? ''));
        $groomAddress = trim((string) ($extra['ceremony_groom_address'] ?? ''));
        if ($brideAddress !== '' || $groomAddress !== '') {
            $brideFamilyGuests = $wedding->guests()
                ->where('group_name', 'like', '%Nhà Gái%')
                ->where(function ($q) {
                    $q->where('check_in_note', 'like', '%nhà trai%')
                        ->orWhere('check_in_note', 'like', '%thành hôn%');
                })
                ->count();
            if ($brideFamilyGuests > 0) {
                $mismatchedGuestsCount += $brideFamilyGuests;
            }
        }

        if ($mismatchedGuestsCount > 0) {
            $collisions[] = [
                'type' => 'guest_assignment_mismatch',
                'severity' => 'warning',
                'title' => 'Khách họ hàng có thể bị gán nhầm sự kiện',
                'message' => "Phát hiện {$mismatchedGuestsCount} khách thuộc nhóm họ hàng có ghi chú tham dự sự kiện của nhà bên kia.",
                'suggestion' => 'Rà soát lại danh sách khách mời để đảm bảo khách nhận đúng thiệp mời dự Lễ Vu Quy hoặc Lễ Thành Hôn.',
            ];
        }

        $highestSeverity = 'none';
        if (collect($collisions)->contains(fn ($c) => $c['severity'] === 'critical')) {
            $highestSeverity = 'critical';
        } elseif (collect($collisions)->contains(fn ($c) => $c['severity'] === 'warning')) {
            $highestSeverity = 'warning';
        } elseif (count($collisions) > 0) {
            $highestSeverity = 'info';
        }

        return [
            'has_collisions' => count($collisions) > 0,
            'highest_severity' => $highestSeverity,
            'collisions_count' => count($collisions),
            'collisions' => $collisions,
        ];
    }

    /**
     * Chuyển đổi chuỗi giờ "HH:MM" thành số phút trong ngày.
     */
    private function timeToMinutes(?string $timeStr): ?int
    {
        if (! $timeStr || ! str_contains($timeStr, ':')) {
            return null;
        }

        $parts = explode(':', trim($timeStr));
        if (count($parts) < 2) {
            return null;
        }

        $h = (int) $parts[0];
        $m = (int) $parts[1];

        if ($h < 0 || $h > 23 || $m < 0 || $m > 59) {
            return null;
        }

        return $h * 60 + $m;
    }
}
