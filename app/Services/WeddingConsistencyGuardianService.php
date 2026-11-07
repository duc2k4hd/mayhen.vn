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
     *     issues: array<int, array{severity: string, field: string, message: string, suggestion: string}>,
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

        // 1. Kiểm tra ngày tổ chức hôn lễ
        if ($wedding->wedding_date) {
            $checksPassed++;
        } else {
            $issues[] = [
                'severity' => 'critical',
                'field' => 'wedding_date',
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
                'message' => 'Thông tin sảnh tiệc chưa đầy đủ (đã có tên sảnh nhưng thiếu địa chỉ chi tiết hoặc ngược lại).',
                'suggestion' => 'Vui lòng điền cả tên sảnh và số nhà, tên đường để khách mời dễ dàng tìm kiếm.',
            ];
        } else {
            $issues[] = [
                'severity' => 'critical',
                'field' => 'venue_name',
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
                    'message' => 'Liên kết bản đồ không thuộc định dạng chuẩn của Google Maps.',
                    'suggestion' => 'Khuyên dùng liên kết chia sẻ từ ứng dụng Google Maps để khách mở app chỉ đường 1 chạm.',
                ];
            }
        } else {
            $issues[] = [
                'severity' => 'info',
                'field' => 'event_party_map_url',
                'message' => 'Chưa gắn liên kết Google Maps chỉ đường.',
                'suggestion' => 'Gắn link Google Maps giúp khách lớn tuổi và khách ở xa đi lại thuận tiện hơn.',
            ];
        }

        // 4. Kiểm tra tài khoản ngân hàng mừng cưới
        $groomBankAcc = trim((string) ($extra['groom_bank_account'] ?? ''));
        $groomBankName = trim((string) ($extra['groom_bank_name'] ?? ''));
        if ($groomBankAcc !== '' && $groomBankName === '') {
            $issues[] = [
                'severity' => 'warning',
                'field' => 'groom_bank_name',
                'message' => 'Đã có số tài khoản Nhà Trai nhưng chưa chọn ngân hàng thụ hưởng.',
                'suggestion' => 'Vui lòng chọn tên ngân hàng (ví dụ: Vietcombank, MB Bank) để mã VietQR tạo chính xác.',
            ];
        } elseif ($groomBankAcc !== '' && strlen($groomBankAcc) < 6) {
            $issues[] = [
                'severity' => 'warning',
                'field' => 'groom_bank_account',
                'message' => 'Số tài khoản Nhà Trai có độ dài bất thường (< 6 chữ số).',
                'suggestion' => 'Vui lòng kiểm tra lại để tránh khách gửi mừng cưới nhầm số tài khoản.',
            ];
        } else {
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
}
