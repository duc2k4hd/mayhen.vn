<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\User;
use App\Models\Wedding;
use Carbon\Carbon;
use Illuminate\Support\Str;

class QualityDoctorService
{
    /**
     * Chẩn đoán toàn diện chất lượng thiệp cưới qua 10 Section tiêu chuẩn và dữ liệu hôn lễ.
     *
     * @return array{
     *     invitation: Invitation,
     *     wedding: Wedding,
     *     kpis: array{
     *         health_score: int,
     *         grade: string,
     *         badge_color: string,
     *         critical_count: int,
     *         warning_count: int,
     *         suggestion_count: int,
     *         passed_count: int,
     *         total_checks: int
     *     },
     *     checks: array<int, array{
     *         id: string,
     *         section_code: string,
     *         section_name: string,
     *         title: string,
     *         status: string,
     *         severity: string,
     *         impact_score: int,
     *         message: string,
     *         suggestion: string,
     *         action_url: string,
     *         action_label: string
     *     }>,
     *     rehearsal_url: string
     * }
     */
    public function diagnose(Invitation $invitation): array
    {
        $wedding = $invitation->wedding ?? Wedding::find($invitation->wedding_id);

        if (! $wedding) {
            return $this->buildFallbackDiagnosis($invitation);
        }

        $extra = is_array($wedding->extra_data)
            ? $wedding->extra_data
            : (json_decode((string) $wedding->extra_data, true) ?? []);

        $custom = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        $checks = [];
        $score = 100;
        $settingsUrl = route('account.invitations.settings', $invitation->id);

        // ==========================================
        // 1. SECTION 1: OPENING & NHẠC NỀN & NIÊM PHONG SÁP
        // ==========================================
        $musicUrl = trim((string) ($custom['music_url'] ?? ($custom['music']['url'] ?? ($wedding->music_url ?? ($extra['music_url'] ?? '')))));
        if ($musicUrl !== '') {
            $checks[] = [
                'id' => 'sec1_music',
                'section_code' => 'section_1',
                'section_name' => '1. Mở Đầu & Âm Nhạc',
                'title' => 'Giai điệu hôn lễ du dương',
                'status' => 'pass',
                'severity' => 'none',
                'impact_score' => 0,
                'message' => 'Thiệp đã có nhạc nền giúp tạo cảm xúc lắng đọng khi quan khách mở thiệp.',
                'suggestion' => 'Có thể tinh chỉnh chế độ tự động phát nhẹ nhàng hoặc chọn bản nhạc ý nghĩa nhất.',
                'action_url' => $settingsUrl,
                'action_label' => 'Đổi Nhạc Nền',
            ];
        } else {
            $score -= 6;
            $checks[] = [
                'id' => 'sec1_music',
                'section_code' => 'section_1',
                'section_name' => '1. Mở Đầu & Âm Nhạc',
                'title' => 'Chưa cấu hình nhạc nền cho thiệp',
                'status' => 'suggestion',
                'severity' => 'suggestion',
                'impact_score' => 6,
                'message' => 'Thiệp chưa có giai điệu đón khách. Âm nhạc tạo ra 40% cảm xúc ban đầu khi mở phong bì.',
                'suggestion' => 'Khuyên dùng: Chọn 1 bản nhạc Acoustic hoặc Piano không lời nhẹ nhàng trong Studio.',
                'action_url' => $settingsUrl,
                'action_label' => 'Chọn Nhạc Ngay',
            ];
        }

        // ==========================================
        // 2. SECTION 2: HERO COVER & CẶP ĐÔI
        // ==========================================
        $groom = trim((string) ($wedding->groom_name ?? ($custom['hero']['groom_name'] ?? '')));
        $bride = trim((string) ($wedding->bride_name ?? ($custom['hero']['bride_name'] ?? '')));
        $heroImage = trim((string) ($wedding->cover_photo_url ?? ($custom['hero']['image_url'] ?? ($custom['cover_image'] ?? ''))));

        if ($groom === '' || $bride === '') {
            $score -= 25;
            $checks[] = [
                'id' => 'sec2_couple_names',
                'section_code' => 'section_2',
                'section_name' => '2. Ảnh Bìa & Danh Tính Cặp Đôi',
                'title' => 'Thiếu tên Chú Rể hoặc Cô Dâu',
                'status' => 'critical',
                'severity' => 'critical',
                'impact_score' => 25,
                'message' => 'Tên của Chú Rể hoặc Cô Dâu đang để trống. Quan khách sẽ không biết chủ nhân hôn lễ là ai.',
                'suggestion' => 'Bắt buộc: Cập nhật đầy đủ họ và tên Chú Rể và Cô Dâu trước khi phát thiệp.',
                'action_url' => $settingsUrl,
                'action_label' => 'Điền Tên Cặp Đôi',
            ];
        } else {
            $checks[] = [
                'id' => 'sec2_couple_names',
                'section_code' => 'section_2',
                'section_name' => '2. Ảnh Bìa & Danh Tính Cặp Đôi',
                'title' => 'Đã định danh cặp đôi: '.$groom.' & '.$bride,
                'status' => 'pass',
                'severity' => 'none',
                'impact_score' => 0,
                'message' => 'Tên hai nhân vật chính đã được thiết lập rõ ràng, trang trọng.',
                'suggestion' => 'Kiểm tra thêm cách xưng hô thân mật hoặc tên tiếng Anh nếu có.',
                'action_url' => $settingsUrl,
                'action_label' => 'Chỉnh Sửa',
            ];
        }

        if ($heroImage === '') {
            $score -= 10;
            $checks[] = [
                'id' => 'sec2_hero_image',
                'section_code' => 'section_2',
                'section_name' => '2. Ảnh Bìa & Danh Tính Cặp Đôi',
                'title' => 'Chưa có ảnh bìa đại diện Hero Cover',
                'status' => 'warning',
                'severity' => 'warning',
                'impact_score' => 10,
                'message' => 'Thiệp đang sử dụng ảnh mặc định của mẫu hoặc chưa tải lên ảnh bìa cưới của hai bạn.',
                'suggestion' => 'Nên chọn 1 bức ảnh cưới đẹp nhất với tỉ lệ dọc (3:4 hoặc 9:16) chất lượng cao.',
                'action_url' => $settingsUrl,
                'action_label' => 'Tải Ảnh Bìa',
            ];
        } else {
            $checks[] = [
                'id' => 'sec2_hero_image',
                'section_code' => 'section_2',
                'section_name' => '2. Ảnh Bìa & Danh Tính Cặp Đôi',
                'title' => 'Ảnh bìa thiệp cưới đã sẵn sàng',
                'status' => 'pass',
                'severity' => 'none',
                'impact_score' => 0,
                'message' => 'Ảnh bìa sắc nét, hiển thị trang trọng tại vị trí trung tâm thiệp.',
                'suggestion' => 'Đã tối ưu chuẩn hiển thị trên mọi thiết bị di động.',
                'action_url' => $settingsUrl,
                'action_label' => 'Đổi Ảnh Bìa',
            ];
        }

        // ==========================================
        // 3. SECTION 3: LOVE STORY & QUOTE
        // ==========================================
        $story = trim((string) ($wedding->story ?? ($custom['story']['content'] ?? ($extra['story'] ?? ''))));
        if ($story !== '') {
            $checks[] = [
                'id' => 'sec3_story',
                'section_code' => 'section_3',
                'section_name' => '3. Câu Chuyện Tình Yêu & Trích Dẫn',
                'title' => 'Câu chuyện tình yêu đầy cảm xúc',
                'status' => 'pass',
                'severity' => 'none',
                'impact_score' => 0,
                'message' => 'Đã chia sẻ hành trình tình yêu ngọt ngào để người thân và bạn bè cùng đồng cảm.',
                'suggestion' => 'Có thể bổ sung các mốc thời gian kỷ niệm (Ngày đầu gặp gỡ, Ngày tỏ tình, Ngày cầu hôn).',
                'action_url' => $settingsUrl,
                'action_label' => 'Xem Lại Lời Ngỏ',
            ];
        } else {
            $score -= 4;
            $checks[] = [
                'id' => 'sec3_story',
                'section_code' => 'section_3',
                'section_name' => '3. Câu Chuyện Tình Yêu & Trích Dẫn',
                'title' => 'Chưa viết câu chuyện tình yêu hoặc trích dẫn',
                'status' => 'suggestion',
                'severity' => 'suggestion',
                'impact_score' => 4,
                'message' => 'Thiệp chưa có lời ngỏ chia sẻ về hành trình của hai bạn.',
                'suggestion' => 'Gợi ý: Dùng tính năng AI Copywriting hoặc viết 2-3 câu kỷ niệm giản dị, chân thành.',
                'action_url' => $settingsUrl,
                'action_label' => 'Viết Lời Ngỏ',
            ];
        }

        // ==========================================
        // 4. SECTION 4: TỨ THÂN PHỤ MẪU & THỨ BẬC
        // ==========================================
        $groomParents = trim((string) ($wedding->groom_parents ?? ($extra['groom_parents'] ?? ($custom['family']['groom_parents'] ?? ''))));
        $brideParents = trim((string) ($wedding->bride_parents ?? ($extra['bride_parents'] ?? ($custom['family']['bride_parents'] ?? ''))));

        if ($groomParents === '' || $brideParents === '') {
            $score -= 12;
            $checks[] = [
                'id' => 'sec4_parents',
                'section_code' => 'section_4',
                'section_name' => '4. Tứ Thân PhỤ Mẫu & Gia Tộc',
                'title' => 'Chưa có đầy đủ thông tin Cha Mẹ hai bên',
                'status' => 'warning',
                'severity' => 'warning',
                'impact_score' => 12,
                'message' => 'Thiếu họ tên Phụ mẫu Nhà Trai hoặc Phụ mẫu Nhà Gái. Đây là lễ nghi kính trọng bậc sinh thành trong văn hóa cưới Việt.',
                'suggestion' => 'Vui lòng điền đầy đủ tên Ông/Bà hai bên (và ghi rõ Trưởng nam/Út nam/Trưởng nữ/Út nữ nếu có).',
                'action_url' => $settingsUrl,
                'action_label' => 'Cập Nhật Gia Đình',
            ];
        } else {
            $checks[] = [
                'id' => 'sec4_parents',
                'section_code' => 'section_4',
                'section_name' => '4. Tứ Thân Phụ Mẫu & Gia Tộc',
                'title' => 'Thông tin Phụ mẫu hai bên gia đình trọn vẹn',
                'status' => 'pass',
                'severity' => 'none',
                'impact_score' => 0,
                'message' => 'Họ tên và danh xưng phụ mẫu hai bên đã được thiết lập đúng mực.',
                'suggestion' => 'Quan khách lớn tuổi và họ hàng sẽ rất hài lòng với sự chỉn chu này.',
                'action_url' => $settingsUrl,
                'action_label' => 'Kiểm Tra',
            ];
        }

        // ==========================================
        // 5. SECTION 5: COUNTDOWN & HẠN CHÓT RSVP
        // ==========================================
        if (! $wedding->wedding_date) {
            $score -= 25;
            $checks[] = [
                'id' => 'sec5_date',
                'section_code' => 'section_5',
                'section_name' => '5. Ngày Cưới & Lịch Hẹn Đếm Ngược',
                'title' => 'Chưa cấu hình ngày tổ chức hôn lễ chính thức',
                'status' => 'critical',
                'severity' => 'critical',
                'impact_score' => 25,
                'message' => 'Chưa có ngày cưới. Đồng hồ đếm ngược và tính năng lưu lịch của quan khách sẽ không hoạt động.',
                'suggestion' => 'Bắt buộc: Chọn ngày tổ chức hôn lễ trong phần thông tin chung.',
                'action_url' => $settingsUrl,
                'action_label' => 'Chọn Ngày Cưới',
            ];
        } else {
            $weddingTimestamp = strtotime((string) $wedding->wedding_date);
            $rsvpDeadlineStr = trim((string) ($extra['rsvp_deadline'] ?? ($custom['rsvp']['deadline'] ?? '')));

            if ($rsvpDeadlineStr !== '') {
                $deadlineTimestamp = strtotime($rsvpDeadlineStr);
                if ($weddingTimestamp && $deadlineTimestamp && $deadlineTimestamp > $weddingTimestamp) {
                    $score -= 15;
                    $checks[] = [
                        'id' => 'sec5_rsvp_conflict',
                        'section_code' => 'section_5',
                        'section_name' => '5. Ngày Cưới & Lịch Hẹn Đếm Ngược',
                        'title' => 'Xung đột ngày: Hạn RSVP sau ngày cưới',
                        'status' => 'critical',
                        'severity' => 'critical',
                        'impact_score' => 15,
                        'message' => 'Hạn chót RSVP ('.date('d/m/Y', $deadlineTimestamp).') đang nằm sau ngày cưới ('.date('d/m/Y', $weddingTimestamp).').',
                        'suggestion' => 'Khắc phục ngay: Đặt hạn chót RSVP trước ngày cưới từ 3 đến 7 ngày để kịp chốt cỗ với nhà hàng.',
                        'action_url' => $settingsUrl,
                        'action_label' => 'Sửa Hạn Chót RSVP',
                    ];
                } else {
                    $checks[] = [
                        'id' => 'sec5_rsvp_conflict',
                        'section_code' => 'section_5',
                        'section_name' => '5. Ngày Cưới & Lịch Hẹn Đếm Ngược',
                        'title' => 'Ngày cưới & Hạn chót RSVP hoàn toàn hợp lý',
                        'status' => 'pass',
                        'severity' => 'none',
                        'impact_score' => 0,
                        'message' => 'Ngày cưới: '.Carbon::parse($wedding->wedding_date)->format('d/m/Y').' | Hạn RSVP: '.date('d/m/Y', $deadlineTimestamp).'.',
                        'suggestion' => 'Đảm bảo khoảng cách thời gian đủ để dâu rể sắp xếp bàn tiệc chu đáo.',
                        'action_url' => $settingsUrl,
                        'action_label' => 'Xem Chi Tiết',
                    ];
                }
            } else {
                $score -= 5;
                $checks[] = [
                    'id' => 'sec5_rsvp_deadline_missing',
                    'section_code' => 'section_5',
                    'section_name' => '5. Ngày Cưới & Lịch Hẹn Đếm Ngược',
                    'title' => 'Chưa đặt hạn chót xác nhận tham dự (RSVP Deadline)',
                    'status' => 'warning',
                    'severity' => 'warning',
                    'impact_score' => 5,
                    'message' => 'Khách mời không biết thời hạn phản hồi, dễ dẫn đến tình trạng trả lời sát ngày hoặc quên.',
                    'suggestion' => 'Nên đặt hạn chót trước ngày cưới khoảng 5 - 7 ngày.',
                    'action_url' => $settingsUrl,
                    'action_label' => 'Đặt Hạn Chót',
                ];
            }
        }

        // ==========================================
        // 6. SECTION 6: SẢNH TIỆC & BẢN ĐỒ GOOGLE MAPS
        // ==========================================
        $venue = trim((string) ($wedding->venue_name ?? ($custom['event']['venue'] ?? ($extra['venue_name'] ?? ''))));
        $address = trim((string) ($wedding->venue_address ?? ($custom['event']['address'] ?? ($extra['venue_address'] ?? ''))));
        $mapUrl = trim((string) ($extra['google_maps_url'] ?? ($extra['map_link'] ?? ($custom['event']['map_url'] ?? ''))));

        if ($venue === '' || $address === '') {
            $score -= 20;
            $checks[] = [
                'id' => 'sec6_venue',
                'section_code' => 'section_6',
                'section_name' => '6. Sảnh Tiệc & Bản Đồ Chỉ Đường',
                'title' => 'Thiếu tên trung tâm tiệc cưới hoặc địa chỉ chi tiết',
                'status' => 'critical',
                'severity' => 'critical',
                'impact_score' => 20,
                'message' => 'Chưa có tên sảnh tiệc hoặc địa chỉ cụ thể. Quan khách sẽ không biết đi đến đâu.',
                'suggestion' => 'Bắt buộc: Điền đầy đủ tên sảnh tiệc (VD: Trống Đồng Palace) và số nhà, tên đường.',
                'action_url' => $settingsUrl,
                'action_label' => 'Điền Sảnh Tiệc',
            ];
        } elseif ($mapUrl === '') {
            $score -= 8;
            $checks[] = [
                'id' => 'sec6_map_url',
                'section_code' => 'section_6',
                'section_name' => '6. Sảnh Tiệc & Bản Đồ Chỉ Đường',
                'title' => 'Chưa gắn liên kết Google Maps chỉ đường',
                'status' => 'warning',
                'severity' => 'warning',
                'impact_score' => 8,
                'message' => 'Đã có địa chỉ nhưng thiếu liên kết Google Maps để khách nhấn 1-chạm mở GPS dẫn đường.',
                'suggestion' => 'Dán link Google Maps hoặc tọa độ địa điểm tiệc cưới để quan khách dễ tìm đường.',
                'action_url' => $settingsUrl,
                'action_label' => 'Gắn Link Bản Đồ',
            ];
        } else {
            $checks[] = [
                'id' => 'sec6_venue_complete',
                'section_code' => 'section_6',
                'section_name' => '6. Sảnh Tiệc & Bản Đồ Chỉ Đường',
                'title' => 'Địa điểm rõ ràng, có bản đồ chỉ đường thông minh',
                'status' => 'pass',
                'severity' => 'none',
                'impact_score' => 0,
                'message' => 'Sảnh: '.$venue.' | Đã có link Google Maps kích hoạt điều hướng nhanh.',
                'suggestion' => 'Khách mời mở thiệp trên điện thoại có thể bấm dẫn đường trực tiếp.',
                'action_url' => $settingsUrl,
                'action_label' => 'Kiểm Tra Bản Đồ',
            ];
        }

        // ==========================================
        // 7. SECTION 7: TIMELINE & DRESSCODE
        // ==========================================
        $dresscode = $custom['dresscode'] ?? ($extra['dresscode'] ?? null);
        $hasDresscode = ! empty($dresscode);

        if (! $hasDresscode) {
            $score -= 4;
            $checks[] = [
                'id' => 'sec7_dresscode',
                'section_code' => 'section_7',
                'section_name' => '7. Lịch Trình & Quy Ước Trang Phục',
                'title' => 'Chưa quy ước màu sắc trang phục (Dresscode)',
                'status' => 'suggestion',
                'severity' => 'suggestion',
                'impact_score' => 4,
                'message' => 'Thiệp chưa có bảng màu trang phục gợi ý cho quan khách.',
                'suggestion' => 'Gợi ý: Cung cấp 3-4 mã màu trang phục hài hòa với không gian tiệc để ảnh kỷ niệm lung linh nhất.',
                'action_url' => $settingsUrl,
                'action_label' => 'Thêm Dresscode',
            ];
        } else {
            $checks[] = [
                'id' => 'sec7_dresscode',
                'section_code' => 'section_7',
                'section_name' => '7. Lịch Trình & Quy Ước Trang Phục',
                'title' => 'Đã có bảng màu trang phục (Dresscode) tinh tế',
                'status' => 'pass',
                'severity' => 'none',
                'impact_score' => 0,
                'message' => 'Bảng màu dresscode giúp bữa tiệc đồng điệu và ảnh chụp kỷ niệm sang trọng.',
                'suggestion' => 'Đã bao gồm ghi chú trang phục tránh trùng màu với váy cưới cô dâu.',
                'action_url' => $settingsUrl,
                'action_label' => 'Xem Bảng Màu',
            ];
        }

        // ==========================================
        // 8. SECTION 8: ALBUM 10 ẢNH KỶ NIỆM
        // ==========================================
        $gallery = $custom['gallery'] ?? ($extra['gallery'] ?? ($custom['album'] ?? []));
        $photoCount = is_array($gallery) ? count($gallery) : 0;

        if ($photoCount === 0) {
            $score -= 6;
            $checks[] = [
                'id' => 'sec8_gallery',
                'section_code' => 'section_8',
                'section_name' => '8. Album Ảnh Kỷ Niệm Cưới',
                'title' => 'Chưa có ảnh trong Album ảnh cưới',
                'status' => 'suggestion',
                'severity' => 'suggestion',
                'impact_score' => 6,
                'message' => 'Album 10 ảnh đang trống. Quan khách rất thích ngắm nhìn những khoảnh khắc hạnh phúc của hai bạn.',
                'suggestion' => 'Tải lên 6 - 10 bức ảnh cưới kỷ niệm đẹp nhất.',
                'action_url' => $settingsUrl,
                'action_label' => 'Tải Album Ảnh',
            ];
        } elseif ($photoCount < 4) {
            $score -= 3;
            $checks[] = [
                'id' => 'sec8_gallery',
                'section_code' => 'section_8',
                'section_name' => '8. Album Ảnh Kỷ Niệm Cưới',
                'title' => 'Album ảnh cưới mới có '.$photoCount.' ảnh',
                'status' => 'suggestion',
                'severity' => 'suggestion',
                'impact_score' => 3,
                'message' => 'Nên có từ 4 đến 10 ảnh để tạo thành bộ sưu tập ảnh cưới hoàn chỉnh trên di động.',
                'suggestion' => 'Có thể tải thêm ảnh pre-wedding hoặc ảnh đời thường ngọt ngào.',
                'action_url' => $settingsUrl,
                'action_label' => 'Thêm Ảnh Cưới',
            ];
        } else {
            $checks[] = [
                'id' => 'sec8_gallery',
                'section_code' => 'section_8',
                'section_name' => '8. Album Ảnh Kỷ Niệm Cưới',
                'title' => 'Album ảnh cưới trọn vẹn ('.$photoCount.' ảnh)',
                'status' => 'pass',
                'severity' => 'none',
                'impact_score' => 0,
                'message' => 'Bộ sưu tập ảnh cưới phong phú, trình chiếu mượt mà chuẩn mobile.',
                'suggestion' => 'Ảnh được tối ưu nén tự động để tải nhanh trong vòng 1.5 giây.',
                'action_url' => $settingsUrl,
                'action_label' => 'Quản Lý Ảnh',
            ];
        }

        // ==========================================
        // 9. SECTION 9: HỘP MỪNG CƯỚI & VIETQR
        // ==========================================
        $giftConfig = $custom['gift_registry'] ?? ($extra['gift_registry'] ?? []);
        $giftEnabled = (bool) ($giftConfig['is_enabled'] ?? true);
        $bankNum = trim((string) ($wedding->bank_account_number ?? ($giftConfig['groom_side']['account_number'] ?? ($giftConfig['bank_account_number'] ?? ''))));
        $bankName = trim((string) ($wedding->bank_name ?? ($giftConfig['groom_side']['bank_name'] ?? ($giftConfig['bank_name'] ?? ''))));

        if ($giftEnabled && ($bankNum === '' || $bankName === '')) {
            $score -= 10;
            $checks[] = [
                'id' => 'sec9_gift',
                'section_code' => 'section_9',
                'section_name' => '9. Hộp Mừng Cưới 3D & VietQR',
                'title' => 'Bật mừng cưới nhưng chưa có số tài khoản hoặc ngân hàng',
                'status' => 'warning',
                'severity' => 'warning',
                'impact_score' => 10,
                'message' => 'Hộp mừng cưới được bật nhưng thiếu thông tin tài khoản ngân hàng để tạo mã VietQR.',
                'suggestion' => 'Vào Studio Hộp Mừng Cưới để chọn ngân hàng và nhập số tài khoản để tự sinh mã VietQR NAPAS.',
                'action_url' => route('account.gift_registry.index', $invitation->id),
                'action_label' => 'Cấu Hình VietQR',
            ];
        } else {
            $checks[] = [
                'id' => 'sec9_gift',
                'section_code' => 'section_9',
                'section_name' => '9. Hộp Mừng Cưới 3D & VietQR',
                'title' => 'Hộp mừng cưới & VietQR NAPAS chuẩn hóa',
                'status' => 'pass',
                'severity' => 'none',
                'impact_score' => 0,
                'message' => 'Mã VietQR động tự động điền cú pháp mừng cưới và số tài khoản chính xác.',
                'suggestion' => 'Khách ở xa có thể gửi lời chúc và mừng cưới thuận tiện chỉ với 1 lượt quét.',
                'action_url' => route('account.gift_registry.index', $invitation->id),
                'action_label' => 'Quản Trị Mừng Cưới',
            ];
        }

        // ==========================================
        // 10. SECTION 10: SỔ KÝ TÊN & LỜI CẢM ƠN KẾT THIỆP
        // ==========================================
        $endingConfig = $custom['interactive_ending'] ?? ($extra['interactive_ending'] ?? []);
        $thankMsg = trim((string) ($endingConfig['thank_you_message'] ?? ($wedding->client_notes ?? '')));

        if ($thankMsg === '') {
            $score -= 3;
            $checks[] = [
                'id' => 'sec10_ending',
                'section_code' => 'section_10',
                'section_name' => '10. Sổ Ký Tên & Lời Cảm Ơn Kết Thiệp',
                'title' => 'Chưa có lời cảm ơn tri ân khép lại thiệp cưới',
                'status' => 'suggestion',
                'severity' => 'suggestion',
                'impact_score' => 3,
                'message' => 'Thiệp đang dùng lời cảm ơn mặc định, chưa có dấu ấn cá nhân của hai bạn.',
                'suggestion' => 'Nên thêm lời cảm ơn chân thành tới những người thân thương đã đọc hết thiệp.',
                'action_url' => route('account.interactive_ending.index', $invitation->id),
                'action_label' => 'Sửa Lời Cảm Ơn',
            ];
        } else {
            $checks[] = [
                'id' => 'sec10_ending',
                'section_code' => 'section_10',
                'section_name' => '10. Sổ Ký Tên & Lời Cảm Ơn Kết Thiệp',
                'title' => 'Sổ ký tên cảm ứng & Lời cảm ơn ấm áp',
                'status' => 'pass',
                'severity' => 'none',
                'impact_score' => 0,
                'message' => 'Sổ ký tên cảm ứng đa sắc và lời tri ân đã sẵn sàng đón nhận phúc lành.',
                'suggestion' => 'Lời cảm ơn để lại dư vị ngọt ngào và kính trọng trong lòng quan khách.',
                'action_url' => route('account.interactive_ending.index', $invitation->id),
                'action_label' => 'Xem Sổ Ký Tên',
            ];
        }

        // ==========================================
        // 11. KHÁCH MỜI & SƠ ĐỒ BÀN TIỆC
        // ==========================================
        $totalGuests = $wedding->guests()->count();
        if ($totalGuests === 0) {
            $score -= 8;
            $checks[] = [
                'id' => 'sys_guest_list',
                'section_code' => 'guests_tables',
                'section_name' => 'Hệ Thống Khách Mời & Sắp Bàn',
                'title' => 'Danh sách khách mời chưa có ai',
                'status' => 'warning',
                'severity' => 'warning',
                'impact_score' => 8,
                'message' => 'Bạn chưa nhập danh sách khách mời để tạo thiệp cá nhân hóa và quản lý RSVP.',
                'suggestion' => 'Vào Quản lý Khách mời để thêm khách hoặc nhập nhanh từ file Excel.',
                'action_url' => route('guests.index', $invitation->id),
                'action_label' => 'Thêm Khách Mời',
            ];
        } else {
            $unnamedSalutation = $wedding->guests()->where(function ($q) {
                $q->whereNull('salutation')->orWhere('salutation', '');
            })->count();

            if ($unnamedSalutation > 0) {
                $score -= 5;
                $checks[] = [
                    'id' => 'sys_salutation',
                    'section_code' => 'guests_tables',
                    'section_name' => 'Hệ Thống Khách Mời & Sắp Bàn',
                    'title' => $unnamedSalutation.' khách mời chưa có danh xưng lễ nghi',
                    'status' => 'warning',
                    'severity' => 'warning',
                    'impact_score' => 5,
                    'message' => 'Một số khách chưa được gắn xưng hô (Bác / Chú / Cô / Anh / Chị / Bạn), thiệp gửi đi sẽ thiếu chữ xưng hô trang trọng.',
                    'suggestion' => 'Bổ sung danh xưng để lời mời tự động hiển thị: "Kính mời [Danh Xưng] [Tên Khách]".',
                    'action_url' => route('guests.index', $invitation->id),
                    'action_label' => 'Sửa Danh Xưng',
                ];
            } else {
                $checks[] = [
                    'id' => 'sys_guest_list',
                    'section_code' => 'guests_tables',
                    'section_name' => 'Hệ Thống Khách Mời & Sắp Bàn',
                    'title' => 'Danh sách '.$totalGuests.' khách mời có danh xưng chuẩn mực',
                    'status' => 'pass',
                    'severity' => 'none',
                    'impact_score' => 0,
                    'message' => '100% khách mời đã được phân danh xưng kính cẩn, sẵn sàng gửi thiệp riêng.',
                    'suggestion' => 'Có thể sử dụng Studio Trợ Lý Nhắc Hẹn để phân loại và theo dõi phản hồi.',
                    'action_url' => route('guests.index', $invitation->id),
                    'action_label' => 'Xem Khách Mời',
                ];
            }
        }

        // Đảm bảo điểm số trong khoảng 0 - 100
        $finalScore = max(0, min(100, $score));

        // Đếm theo từng phân loại
        $criticalCount = 0;
        $warningCount = 0;
        $suggestionCount = 0;
        $passedCount = 0;

        foreach ($checks as $c) {
            if ($c['status'] === 'critical') {
                $criticalCount++;
            } elseif ($c['status'] === 'warning') {
                $warningCount++;
            } elseif ($c['status'] === 'suggestion') {
                $suggestionCount++;
            } elseif ($c['status'] === 'pass') {
                $passedCount++;
            }
        }

        // Xác định hạng điểm
        if ($finalScore >= 90) {
            $grade = 'Xuất Sắc (Thượng Hạng)';
            $badgeColor = 'green';
        } elseif ($finalScore >= 75) {
            $grade = 'Tốt (Đạt Chuẩn Gửi Đi)';
            $badgeColor = 'blue';
        } elseif ($finalScore >= 50) {
            $grade = 'Cần Cải Thiện Thêm';
            $badgeColor = 'amber';
        } else {
            $grade = 'Rủi Ro Cao (Cần Sửa Gấp)';
            $badgeColor = 'red';
        }

        // Link diễn tập
        $rehearsalUrl = route('invitation.show', $invitation->slug).'?mode=rehearsal&preview_vip=true';

        return [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'kpis' => [
                'health_score' => $finalScore,
                'grade' => $grade,
                'badge_color' => $badgeColor,
                'critical_count' => $criticalCount,
                'warning_count' => $warningCount,
                'suggestion_count' => $suggestionCount,
                'passed_count' => $passedCount,
                'total_checks' => count($checks),
            ],
            'checks' => $checks,
            'rehearsal_url' => $rehearsalUrl,
        ];
    }

    /**
     * Tạo hoặc tìm khách mời mẫu Diễn Tập để dâu rể gửi thử thiệp nghiệm thu.
     */
    public function ensureRehearsalGuest(Invitation $invitation, User $user): Guest
    {
        $wedding = $invitation->wedding ?? Wedding::findOrFail($invitation->wedding_id);

        $testGuest = Guest::where('wedding_id', $wedding->id)
            ->where('group_name', 'Khách Diễn Tập Thử Nghiệm')
            ->first();

        if (! $testGuest) {
            $testGuest = Guest::create([
                'wedding_id' => $wedding->id,
                'name' => 'Khách Diễn Tập (VIP Test)',
                'salutation' => 'Kính Mời Anh/Chị',
                'phone' => $user->phone ?? '0900000000',
                'group_name' => 'Khách Diễn Tập Thử Nghiệm',
                'guest_token' => 'REHEARSAL_'.Str::random(32),
                'expected_attendees' => 2,
                'is_sent' => true,
            ]);
        }

        return $testGuest;
    }

    /**
     * Fallback khi không tìm thấy Wedding model.
     *
     * @return array<string, mixed>
     */
    protected function buildFallbackDiagnosis(Invitation $invitation): array
    {
        return [
            'invitation' => $invitation,
            'wedding' => new Wedding,
            'kpis' => [
                'health_score' => 20,
                'grade' => 'Thiếu Dữ Liệu Hôn Lễ',
                'badge_color' => 'red',
                'critical_count' => 1,
                'warning_count' => 0,
                'suggestion_count' => 0,
                'passed_count' => 0,
                'total_checks' => 1,
            ],
            'checks' => [
                [
                    'id' => 'no_wedding',
                    'section_code' => 'general',
                    'section_name' => 'Dữ Liệu Chung',
                    'title' => 'Không tìm thấy thông tin đám cưới',
                    'status' => 'critical',
                    'severity' => 'critical',
                    'impact_score' => 80,
                    'message' => 'Thiệp chưa được liên kết với hồ sơ đám cưới hợp lệ.',
                    'suggestion' => 'Vui lòng liên hệ hỗ trợ hoặc tạo lại thiệp.',
                    'action_url' => route('account.invitations'),
                    'action_label' => 'Về Danh Sách Thiệp',
                ],
            ],
            'rehearsal_url' => route('invitation.show', $invitation->slug),
        ];
    }
}
