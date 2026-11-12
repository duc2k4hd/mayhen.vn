<?php

namespace App\Services;

use App\Models\Invitation;

class MultilingualInvitationService
{
    /**
     * Danh sách 4 ngôn ngữ cốt lõi chuẩn văn phong hôn lễ Mây Hẹn (Mục 2.10 DOCX).
     *
     * @var array<string, array{code: string, name: string, native_name: string, flag: string, dir: string}>
     */
    public const SUPPORTED_LOCALES = [
        'vi' => [
            'code' => 'vi',
            'name' => 'Tiếng Việt',
            'native_name' => 'Tiếng Việt',
            'flag' => '🇻🇳',
            'dir' => 'ltr',
        ],
        'en' => [
            'code' => 'en',
            'name' => 'Tiếng Anh',
            'native_name' => 'English',
            'flag' => '🇬🇧',
            'dir' => 'ltr',
        ],
        'ko' => [
            'code' => 'ko',
            'name' => 'Tiếng Hàn',
            'native_name' => '한국어',
            'flag' => '🇰🇷',
            'dir' => 'ltr',
        ],
        'zh' => [
            'code' => 'zh',
            'name' => 'Tiếng Trung',
            'native_name' => '中文',
            'flag' => '🇨🇳',
            'dir' => 'ltr',
        ],
    ];

    /**
     * Bộ từ điển chuyên ngành cưới (Wedding Lexicon) chuẩn Quiet Luxury cho 10 sections.
     *
     * @var array<string, array<string, string>>
     */
    protected static array $lexicons = [
        'vi' => [
            // Chung & Điều hướng
            'general.tap_to_open' => 'Chạm nhẹ để mở thiệp',
            'general.close' => 'Đóng',
            'general.save' => 'Lưu lại',
            'general.share' => 'Chia sẻ thiệp',
            'general.copy_link' => 'Sao chép liên kết',
            'general.copied' => 'Đã chép!',

            // Section 1: Opening
            'opening.wedding_invitation' => 'THIỆP CƯỚI',
            'opening.open_card' => 'Mở Thiệp',
            'opening.welcome' => 'Trân trọng kính mời',

            // Section 2: Hero Cover
            'hero.wedding_of' => 'Hôn Lễ Của',
            'hero.cordially_invite' => 'Trân trọng kính mời quý khách đến dự buổi tiệc chung vui cùng gia đình chúng tôi',
            'hero.groom_side_title' => 'LỄ THÀNH HÔN',
            'hero.bride_side_title' => 'LỄ VU QUY',
            'hero.party_title' => 'TIỆC BÁO HỶ',

            // Section 3: Love Story & Quote
            'story.title' => 'Chuyện Tình Yêu',
            'story.subtitle' => 'Từng khoảnh khắc đong đầy yêu thương',
            'story.quote_default' => 'Tình yêu trọn vẹn là cùng nhau nhìn về một hướng và đồng hành qua năm tháng.',

            // Section 4: Family & Couple
            'family.title' => 'Cặp Đôi & Gia Đình',
            'family.groom_side' => 'Nhà Trai',
            'family.bride_side' => 'Nhà Gái',
            'family.groom_title' => 'Chú Rể',
            'family.bride_title' => 'Cô Dâu',
            'family.parents' => 'Thân phụ & Thân mẫu',
            'family.father' => 'Thân phụ',
            'family.mother' => 'Thân mẫu',

            // Section 5: Countdown & Calendar
            'countdown.title' => 'Đếm Ngược Ngày Vui',
            'countdown.days' => 'Ngày',
            'countdown.hours' => 'Giờ',
            'countdown.minutes' => 'Phút',
            'countdown.seconds' => 'Giây',
            'countdown.calendar_add' => 'Thêm vào Lịch',

            // Section 6: Events & Maps
            'events.title' => 'Thông Tin Sự Kiện',
            'events.ceremony' => 'Lễ Cưới',
            'events.banquet' => 'Tiệc Chiêu Đãi',
            'events.time' => 'Thời gian',
            'events.venue' => 'Địa điểm',
            'events.get_directions' => 'Chỉ đường Google Maps',

            // Section 7: Timeline
            'timeline.title' => 'Lịch Trình Hôn Lễ',
            'timeline.welcome' => 'Đón tiếp quan khách',
            'timeline.ceremony' => 'Nghi thức cử hành hôn lễ',
            'timeline.banquet' => 'Khai tiệc chiêu đãi',
            'timeline.photos' => 'Chụp ảnh kỷ niệm cùng cô dâu chú rể',

            // Section 8: Dresscode
            'dresscode.title' => 'Quy Chuẩn Trang Phục',
            'dresscode.subtitle' => 'Gợi ý tone màu trang phục',
            'dresscode.note' => 'Sự hiện diện với trang phục trang nhã của quý khách là niềm vinh hạnh của chúng tôi',

            // Section 9: Album Photos
            'album.title' => 'Khoảnh Khắc Kỷ Niệm',
            'album.subtitle' => 'Album ảnh cưới của chúng tôi',
            'album.view_full' => 'Xem toàn bộ hình ảnh',

            // Section 10: Gift, RSVP & Interactive
            'gift.title' => 'Hộp Mừng Cưới',
            'gift.subtitle' => 'Gửi quà mừng hạnh phúc tới đôi uyên ương',
            'gift.groom_bank' => 'Mừng Chú Rể',
            'gift.bride_bank' => 'Mừng Cô Dâu',
            'gift.qr_scan_note' => 'Quét mã VietQR tiện lợi qua ứng dụng ngân hàng',

            'rsvp.title' => 'Xác Nhận Tham Dự',
            'rsvp.subtitle' => 'Vui lòng xác nhận tham dự trước ngày cưới để gia đình chuẩn bị chu đáo nhất',
            'rsvp.attending' => 'Tôi sẽ tham dự',
            'rsvp.declined' => 'Rất tiếc không thể tham dự',
            'rsvp.not_sure' => 'Chưa chắc chắn',
            'rsvp.adults' => 'Số người lớn',
            'rsvp.children' => 'Số trẻ em',
            'rsvp.dietary' => 'Yêu cầu ăn chay / ăn kiêng',
            'rsvp.dietary_placeholder' => 'Ví dụ: 1 phần ăn chay...',
            'rsvp.wishes' => 'Lời chúc phúc',
            'rsvp.wishes_placeholder' => 'Gửi lời chúc tốt đẹp nhất tới cô dâu chú rể...',
            'rsvp.submit' => 'Gửi Xác Nhận',
            'rsvp.submitted_success' => 'Cảm ơn bạn đã gửi xác nhận tham dự!',

            'guestbook.title' => 'Sổ Lưu Bút Kỷ Niệm',
            'guestbook.send_wish' => 'Gửi lời chúc',
            'guestbook.signature_canvas' => 'Ký tên chúc mừng',
            'guestbook.clear_signature' => 'Ký lại',
            'guestbook.save_signature' => 'Gửi chữ ký',
        ],

        'en' => [
            // General & Navigation
            'general.tap_to_open' => 'Tap gently to open invitation',
            'general.close' => 'Close',
            'general.save' => 'Save',
            'general.share' => 'Share Invitation',
            'general.copy_link' => 'Copy Link',
            'general.copied' => 'Copied!',

            // Section 1: Opening
            'opening.wedding_invitation' => 'WEDDING INVITATION',
            'opening.open_card' => 'Open Invitation',
            'opening.welcome' => 'Cordially invites you',

            // Section 2: Hero Cover
            'hero.wedding_of' => 'The Wedding Of',
            'hero.cordially_invite' => 'Together with their families, cordially invite you to celebrate their wedding',
            'hero.groom_side_title' => 'WEDDING CEREMONY',
            'hero.bride_side_title' => 'BRIDE RECEPTION',
            'hero.party_title' => 'WEDDING CELEBRATION',

            // Section 3: Love Story & Quote
            'story.title' => 'Our Love Story',
            'story.subtitle' => 'Every single moment filled with love',
            'story.quote_default' => 'True love is looking in the same direction and walking hand in hand through the years.',

            // Section 4: Family & Couple
            'family.title' => 'The Couple & Families',
            'family.groom_side' => "Groom's Family",
            'family.bride_side' => "Bride's Family",
            'family.groom_title' => 'The Groom',
            'family.bride_title' => 'The Bride',
            'family.parents' => 'Parents of the Groom & Bride',
            'family.father' => 'Father',
            'family.mother' => 'Mother',

            // Section 5: Countdown & Calendar
            'countdown.title' => 'Countdown to the Big Day',
            'countdown.days' => 'Days',
            'countdown.hours' => 'Hours',
            'countdown.minutes' => 'Minutes',
            'countdown.seconds' => 'Seconds',
            'countdown.calendar_add' => 'Add to Calendar',

            // Section 6: Events & Maps
            'events.title' => 'Wedding Events',
            'events.ceremony' => 'Solemn Ceremony',
            'events.banquet' => 'Wedding Banquet',
            'events.time' => 'Time',
            'events.venue' => 'Venue',
            'events.get_directions' => 'Get Directions on Google Maps',

            // Section 7: Timeline
            'timeline.title' => 'Wedding Program',
            'timeline.welcome' => 'Guest Welcome & Reception',
            'timeline.ceremony' => 'Wedding Solemnization',
            'timeline.banquet' => 'Wedding Banquet Commences',
            'timeline.photos' => 'Commemorative Photo Session',

            // Section 8: Dresscode
            'dresscode.title' => 'Dress Code Guide',
            'dresscode.subtitle' => 'Recommended Color Palette',
            'dresscode.note' => 'Your elegant presence is our greatest honor',

            // Section 9: Album Photos
            'album.title' => 'Precious Moments',
            'album.subtitle' => 'Our Wedding Photo Gallery',
            'album.view_full' => 'View Full Gallery',

            // Section 10: Gift, RSVP & Interactive
            'gift.title' => 'Wedding Gift Box',
            'gift.subtitle' => 'Send your warm congratulations and gifts to the newlyweds',
            'gift.groom_bank' => 'To the Groom',
            'gift.bride_bank' => 'To the Bride',
            'gift.qr_scan_note' => 'Scan bank QR code conveniently with your banking app',

            'rsvp.title' => 'RSVP Confirmation',
            'rsvp.subtitle' => 'Please confirm your attendance so that we may welcome you with the utmost warmth',
            'rsvp.attending' => 'Joyfully Accept',
            'rsvp.declined' => 'Regretfully Decline',
            'rsvp.not_sure' => 'Undecided',
            'rsvp.adults' => 'Adults',
            'rsvp.children' => 'Children',
            'rsvp.dietary' => 'Dietary Restrictions (e.g. Vegetarian)',
            'rsvp.dietary_placeholder' => 'E.g., 1 vegetarian meal...',
            'rsvp.wishes' => 'Warm Wishes',
            'rsvp.wishes_placeholder' => 'Write your sweetest wishes for the couple...',
            'rsvp.submit' => 'Send RSVP',
            'rsvp.submitted_success' => 'Thank you for your RSVP confirmation!',

            'guestbook.title' => 'Wedding Guestbook',
            'guestbook.send_wish' => 'Leave a Wish',
            'guestbook.signature_canvas' => 'Digital Signature',
            'guestbook.clear_signature' => 'Clear',
            'guestbook.save_signature' => 'Send Signature',
        ],

        'ko' => [
            // General & Navigation
            'general.tap_to_open' => '화면을 터치하여 청첩장을 열어주세요',
            'general.close' => '닫기',
            'general.save' => '저장',
            'general.share' => '청첩장 공유',
            'general.copy_link' => '링크 복사',
            'general.copied' => '복사 완료!',

            // Section 1: Opening
            'opening.wedding_invitation' => '청첩장',
            'opening.open_card' => '청첩장 열기',
            'opening.welcome' => '소중한 분들을 모십니다',

            // Section 2: Hero Cover
            'hero.wedding_of' => '결혼합니다',
            'hero.cordially_invite' => '서로를 아끼고 사랑하며 평생을 함께할 자리에 소중한 분들을 초대합니다',
            'hero.groom_side_title' => '결혼식',
            'hero.bride_side_title' => '피로연',
            'hero.party_title' => '웨딩 파티',

            // Section 3: Love Story & Quote
            'story.title' => '러브 스토리',
            'story.subtitle' => '사랑으로 가득 찬 우리들의 순간',
            'story.quote_default' => '사랑은 같은 방향을 바라보며 함께 걸어가는 아름다운 동행입니다.',

            // Section 4: Family & Couple
            'family.title' => '신랑 신부 & 혼주',
            'family.groom_side' => '신랑측',
            'family.bride_side' => '신부측',
            'family.groom_title' => '신랑',
            'family.bride_title' => '신부',
            'family.parents' => '혼주',
            'family.father' => '부',
            'family.mother' => '모',

            // Section 5: Countdown & Calendar
            'countdown.title' => '결혼식 디데이',
            'countdown.days' => '일',
            'countdown.hours' => '시간',
            'countdown.minutes' => '분',
            'countdown.seconds' => '초',
            'countdown.calendar_add' => '캘린더에 추가',

            // Section 6: Events & Maps
            'events.title' => '예식 안내',
            'events.ceremony' => '예식',
            'events.banquet' => '피로연',
            'events.time' => '일시',
            'events.venue' => '장소',
            'events.get_directions' => '오시는 길 (Google 지도)',

            // Section 7: Timeline
            'timeline.title' => '예식 순서',
            'timeline.welcome' => '하객 맞이 및 포토존',
            'timeline.ceremony' => '본식 진행',
            'timeline.banquet' => '피로연 식사',
            'timeline.photos' => '기념 사진 촬영',

            // Section 8: Dresscode
            'dresscode.title' => '드레스코드',
            'dresscode.subtitle' => '추천 컬러 가이드',
            'dresscode.note' => '단정한 차림으로 자리를 빛내주시면 감사하겠습니다',

            // Section 9: Album Photos
            'album.title' => '웨딩 갤러리',
            'album.subtitle' => '우리의 가장 아름다운 순간',
            'album.view_full' => '전체 사진 보기',

            // Section 10: Gift, RSVP & Interactive
            'gift.title' => '축의금 전달',
            'gift.subtitle' => '새로운 출발을 축복해 주시는 따뜻한 마음에 감사드립니다',
            'gift.groom_bank' => '신랑측 계좌번호',
            'gift.bride_bank' => '신부측 계좌번호',
            'gift.qr_scan_note' => '간편하게 QR 코드로 마음을 전해보세요',

            'rsvp.title' => '참석 여부 전달',
            'rsvp.subtitle' => '원활한 예식 준비를 위해 참석 여부를 미리 알려주시면 감사하겠습니다',
            'rsvp.attending' => '참석합니다',
            'rsvp.declined' => '불참합니다',
            'rsvp.not_sure' => '미정',
            'rsvp.adults' => '대인',
            'rsvp.children' => '소인',
            'rsvp.dietary' => '식사 관련 요청사항 (예: 채식)',
            'rsvp.dietary_placeholder' => '예: 채식 1인...',
            'rsvp.wishes' => '축하 메시지',
            'rsvp.wishes_placeholder' => '신랑 신부에게 따뜻한 축하를 남겨주세요...',
            'rsvp.submit' => '참석 여부 전송',
            'rsvp.submitted_success' => '소중한 답변이 성공적으로 전달되었습니다!',

            'guestbook.title' => '온라인 방명록',
            'guestbook.send_wish' => '축하 글 남기기',
            'guestbook.signature_canvas' => '전자 서명 남기기',
            'guestbook.clear_signature' => '지우기',
            'guestbook.save_signature' => '서명 전송',
        ],

        'zh' => [
            // General & Navigation
            'general.tap_to_open' => '轻触屏幕开启请柬',
            'general.close' => '关闭',
            'general.save' => '保存',
            'general.share' => '分享请柬',
            'general.copy_link' => '复制链接',
            'general.copied' => '已复制！',

            // Section 1: Opening
            'opening.wedding_invitation' => '婚礼请柬',
            'opening.open_card' => '开启请柬',
            'opening.welcome' => '诚挚邀请您的光临',

            // Section 2: Hero Cover
            'hero.wedding_of' => '我们要结婚了',
            'hero.cordially_invite' => '谨定于良辰吉日举行婚礼，诚邀您及家人莅临观礼，分享喜悦',
            'hero.groom_side_title' => '男方婚礼',
            'hero.bride_side_title' => '女方出阁',
            'hero.party_title' => '婚宴答谢',

            // Section 3: Love Story & Quote
            'story.title' => '爱情故事',
            'story.subtitle' => '记录相识相爱的美好点滴',
            'story.quote_default' => '执子之手，与子偕老。愿与你携手共度每一个春夏秋冬。',

            // Section 4: Family & Couple
            'family.title' => '新人与双方父母',
            'family.groom_side' => '男方家庭',
            'family.bride_side' => '女方家庭',
            'family.groom_title' => '新郎',
            'family.bride_title' => '新娘',
            'family.parents' => '双方家长',
            'family.father' => '父亲',
            'family.mother' => '母亲',

            // Section 5: Countdown & Calendar
            'countdown.title' => '婚礼倒计时',
            'countdown.days' => '天',
            'countdown.hours' => '时',
            'countdown.minutes' => '分',
            'countdown.seconds' => '秒',
            'countdown.calendar_add' => '添加至日历',

            // Section 6: Events & Maps
            'events.title' => '典礼及宴席信息',
            'events.ceremony' => '结婚典礼',
            'events.banquet' => '喜宴招待',
            'events.time' => '吉时',
            'events.venue' => '席设',
            'events.get_directions' => '地图导航 (Google Maps)',

            // Section 7: Timeline
            'timeline.title' => '婚礼流程',
            'timeline.welcome' => '恭候入席 & 留影',
            'timeline.ceremony' => '婚礼仪式开始',
            'timeline.banquet' => '婚宴开席',
            'timeline.photos' => '合影留念',

            // Section 8: Dresscode
            'dresscode.title' => '着装建议',
            'dresscode.subtitle' => '建议穿搭配色',
            'dresscode.note' => '您的优雅出席是我们的莫大荣幸',

            // Section 9: Album Photos
            'album.title' => '婚纱相册',
            'album.subtitle' => '定格甜蜜幸福时刻',
            'album.view_full' => '浏览全部相片',

            // Section 10: Gift, RSVP & Interactive
            'gift.title' => '礼金转账',
            'gift.subtitle' => '感谢您送上的美好祝福与心意',
            'gift.groom_bank' => '新郎礼金账户',
            'gift.bride_bank' => '新娘礼金账户',
            'gift.qr_scan_note' => '可扫码便捷转账',

            'rsvp.title' => '出席回执确认',
            'rsvp.subtitle' => '为方便准备宴席及安排座位，请于婚期前确认出席情况',
            'rsvp.attending' => '确认出席',
            'rsvp.declined' => '遗憾缺席',
            'rsvp.not_sure' => '待定',
            'rsvp.adults' => '成人人数',
            'rsvp.children' => '儿童人数',
            'rsvp.dietary' => '饮食禁忌（如素食）',
            'rsvp.dietary_placeholder' => '如：素食1位...',
            'rsvp.wishes' => '祝福留言',
            'rsvp.wishes_placeholder' => '写下您对新人的美好祝愿...',
            'rsvp.submit' => '提交出席回执',
            'rsvp.submitted_success' => '感谢您的回复！期待与您相聚！',

            'guestbook.title' => '电子留言册',
            'guestbook.send_wish' => '写祝福',
            'guestbook.signature_canvas' => '电子签名',
            'guestbook.clear_signature' => '清除重签',
            'guestbook.save_signature' => '发送签名',
        ],
    ];

    /**
     * Xác định Locale hiện tại của người dùng khi xem thiệp cưới.
     */
    public static function resolveLocale(?string $requestedLocale = null, ?Invitation $invitation = null): string
    {
        $requested = strtolower(trim((string) $requestedLocale));

        // 1. Kiểm tra request query param ?lang=
        if ($requested !== '' && isset(self::SUPPORTED_LOCALES[$requested])) {
            return $requested;
        }

        // 2. Kiểm tra cấu hình mặc định của thiệp
        if ($invitation) {
            $config = is_array($invitation->custom_config)
                ? $invitation->custom_config
                : (json_decode((string) $invitation->custom_config, true) ?: []);

            $defaultLocale = $config['i18n']['default_locale'] ?? null;
            if ($defaultLocale && isset(self::SUPPORTED_LOCALES[$defaultLocale])) {
                return $defaultLocale;
            }
        }

        // 3. Fallback mặc định tiếng Việt
        return 'vi';
    }

    /**
     * Lấy danh sách ngôn ngữ được kích hoạt cho một thiệp cưới cụ thể.
     *
     * @return array<string, array{code: string, name: string, native_name: string, flag: string, dir: string, is_default: bool}>
     */
    public static function getActiveLocalesForInvitation(?Invitation $invitation = null): array
    {
        $config = [];
        if ($invitation) {
            $config = is_array($invitation->custom_config)
                ? $invitation->custom_config
                : (json_decode((string) $invitation->custom_config, true) ?: []);
        }

        $enabledCodes = $config['i18n']['enabled_locales'] ?? ['vi', 'en'];
        $defaultCode = $config['i18n']['default_locale'] ?? 'vi';

        // Luôn đảm bảo có tiếng Việt
        if (! in_array('vi', $enabledCodes, true)) {
            $enabledCodes[] = 'vi';
        }

        $result = [];
        foreach ($enabledCodes as $code) {
            if (isset(self::SUPPORTED_LOCALES[$code])) {
                $localeData = self::SUPPORTED_LOCALES[$code];
                $localeData['is_default'] = ($code === $defaultCode);
                $result[$code] = $localeData;
            }
        }

        return $result;
    }

    /**
     * Dịch một chuỗi văn bản theo locale và key.
     *
     * @param  array<string, string>  $replace
     */
    public static function trans(string $key, ?string $locale = null, array $replace = [], ?Invitation $invitation = null): string
    {
        $locale = self::resolveLocale($locale, $invitation);

        // Kiểm tra xem thiệp có custom override trong custom_config['i18n']['overrides'][locale][key] không
        if ($invitation) {
            $config = is_array($invitation->custom_config)
                ? $invitation->custom_config
                : (json_decode((string) $invitation->custom_config, true) ?: []);

            $override = $config['i18n']['overrides'][$locale][$key] ?? null;
            if (! empty($override)) {
                return self::applyReplacements((string) $override, $replace);
            }
        }

        // Tìm trong lexicon của locale
        $text = self::$lexicons[$locale][$key] ?? (self::$lexicons['vi'][$key] ?? $key);

        return self::applyReplacements($text, $replace);
    }

    /**
     * Trả về toàn bộ từ điển theo locale dưới dạng JSON payload.
     *
     * @return array<string, string>
     */
    public static function getDictionary(string $locale, ?Invitation $invitation = null): array
    {
        $resolved = self::resolveLocale($locale, $invitation);
        $base = self::$lexicons[$resolved] ?? self::$lexicons['vi'];

        if ($invitation) {
            $config = is_array($invitation->custom_config)
                ? $invitation->custom_config
                : (json_decode((string) $invitation->custom_config, true) ?: []);

            $overrides = $config['i18n']['overrides'][$resolved] ?? [];
            if (is_array($overrides) && ! empty($overrides)) {
                $base = array_merge($base, $overrides);
            }
        }

        return $base;
    }

    /**
     * Thay thế placeholder dạng :key hoặc {key}.
     *
     * @param  array<string, string>  $replace
     */
    protected static function applyReplacements(string $line, array $replace): string
    {
        if (empty($replace)) {
            return $line;
        }

        foreach ($replace as $key => $value) {
            $line = str_replace(
                [':'.$key, '{'.$key.'}', ':'.strtoupper($key)],
                [$value, $value, $value],
                $line
            );
        }

        return $line;
    }
}
