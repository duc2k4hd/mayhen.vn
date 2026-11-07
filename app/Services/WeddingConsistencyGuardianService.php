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
}
