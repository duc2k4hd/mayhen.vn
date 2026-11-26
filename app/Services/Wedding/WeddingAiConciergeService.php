<?php

declare(strict_types=1);

namespace App\Services\Wedding;

use App\Services\Bazi\BaziSafetyGuardService;
use App\Services\Intelligence\AiGovernanceService;
use App\Services\Intelligence\Contracts\LlmProviderInterface;

/**
 * AI Wedding Concierge & Content Helper (Mây Hẹn – The Wedding Journey).
 *
 * - Grounded 100% trên dữ liệu thực của cặp đôi / workspace.
 * - KHÔNG BAO GIỜ tự bịa ngày đẹp, không tự bịa báo giá nhà cung cấp, không khẳng định sảnh còn trống.
 * - Mọi đề xuất lên kế hoạch tuân thủ mẫu Proposal-with-Confirmation (chỉ trả về bản nháp có cấu trúc, không tự ý ghi đè DB).
 */
class WeddingAiConciergeService
{
    public const SCRIPT_TYPES = [
        'mc_ceremony' => 'Kịch bản MC Lễ Thành Hôn / Tiệc Cưới',
        'speech_groom' => 'Bài phát biểu đại diện Nhà Trai',
        'speech_bride' => 'Bài phát biểu đại diện Nhà Gái',
        'vows' => 'Lời thề nguyện (Wedding Vows) cặp đôi',
        'thank_you' => 'Tin nhắn cảm ơn khách mời sau lễ cưới',
        'workspace_advice' => 'Trợ lý phân tích kế hoạch & tối ưu tiến độ hôn lễ',
    ];

    public function __construct(
        protected LlmProviderInterface $provider,
        protected AiGovernanceService $governance,
        protected BaziSafetyGuardService $safetyGuard
    ) {}

    /**
     * Tạo kịch bản / lời phát biểu / tư vấn kế hoạch cưới có căn cứ.
     *
     * @param  array<string, mixed>  $context
     * @return array{type: string, title: string, content: string, source: 'ai'|'template', disclaimer: string}
     */
    public function generate(string $type, array $context, bool $allowAi = true): array
    {
        if (! isset(self::SCRIPT_TYPES[$type])) {
            $type = 'mc_ceremony';
        }

        $title = self::SCRIPT_TYPES[$type];
        $notes = trim((string) ($context['custom_notes'] ?? ''));
        if ($notes !== '') {
            $safety = $this->safetyGuard->checkQuestion($notes);
            if (! $safety['safe']) {
                return [
                    'type' => $type,
                    'title' => $title,
                    'content' => $safety['message'] ?? 'Nội dung yêu cầu không phù hợp để xử lý.',
                    'source' => 'template',
                    'disclaimer' => 'Hệ thống an toàn nội dung Mây Hẹn đã tạm dừng yêu cầu này.',
                ];
            }
        }

        $policy = $this->governance->getPolicy();
        $canUseAi = $allowAi && (bool) ($policy['enabled'] ?? false) && $this->provider->isAvailable();

        if ($canUseAi) {
            $prompt = $this->buildGroundedPrompt($type, $context);
            $response = $this->provider->complete(
                'Bạn là Trợ lý Tổ chức Đám cưới chuyên nghiệp của Mây Hẹn (mayhen.vn), am hiểu văn hóa cưới hỏi Việt Nam (Bắc, Trung, Nam). Viết tiếng Việt trang trọng, ấm áp, tinh tế. TUYỆT ĐỐI không bịa báo giá nhà cung cấp, không khẳng định tình trạng đặt sảnh, không tự tính ngày tốt xấu bằng AI, chỉ dùng đúng tên cặp đôi, ngày cưới và dữ liệu được cung cấp.',
                $prompt,
                ['temperature' => 0.45, 'max_tokens' => 1200]
            );

            $text = trim((string) ($response['content'] ?? ''));
            if ($text !== '') {
                return [
                    'type' => $type,
                    'title' => $title,
                    'content' => $text,
                    'source' => 'ai',
                    'disclaimer' => 'Nội dung được biên soạn bởi AI Wedding Concierge Mây Hẹn dựa trên thông tin bạn cung cấp. Vui lòng đọc duyệt và cá nhân hóa trước khi sử dụng.',
                ];
            }
        }

        return [
            'type' => $type,
            'title' => $title,
            'content' => $this->buildStructuredTemplate($type, $context),
            'source' => 'template',
            'disclaimer' => 'Bản mẫu chuẩn biên tập từ thư viện Mây Hẹn (đã điền sẵn tên và ngày của hai bạn).',
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function buildGroundedPrompt(string $type, array $context): string
    {
        $groom = (string) ($context['groom_name'] ?? 'Chú rể');
        $bride = (string) ($context['bride_name'] ?? 'Cô dâu');
        $date = (string) ($context['wedding_date'] ?? 'ngày trọng đại sắp tới');
        $region = match ((string) ($context['region'] ?? 'bac')) {
            'trung' => 'Miền Trung',
            'nam' => 'Miền Nam',
            default => 'Miền Bắc',
        };
        $tone = (string) ($context['tone'] ?? 'Trang trọng, ấm áp');
        $notes = (string) ($context['custom_notes'] ?? '');

        $extra = '';
        if ($type === 'workspace_advice') {
            $summaryJson = json_encode($context['workspace_summary'] ?? [], JSON_UNESCAPED_UNICODE);
            $extra = "\nDữ liệu thực tế từ Hồ sơ Tổ chức Đám cưới: {$summaryJson}\nHãy đưa ra 5 khuyến nghị hành động ưu tiên trong 2 tuần tới, chỉ ra rủi ro ngân sách/tiến độ nếu có (không bịa báo giá).";
        }

        return 'Yêu cầu biên soạn: '.self::SCRIPT_TYPES[$type]."\n"
            ."- Chú rể: {$groom}\n"
            ."- Cô dâu: {$bride}\n"
            ."- Ngày cưới dự kiến: {$date}\n"
            ."- Nghi lễ vùng miền: {$region}\n"
            ."- Phong cách ngôn từ: {$tone}\n"
            .($notes !== '' ? "- Ghi chú riêng của cặp đôi: {$notes}\n" : '')
            .$extra
            ."\nTrình bày rõ ràng bằng Markdown với các phần có tiêu đề ngắn gọn, dễ đọc khi cầm trên tay.";
    }

    /**
     * @param  array<string, mixed>  $context
     */
    protected function buildStructuredTemplate(string $type, array $context): string
    {
        $groom = trim((string) ($context['groom_name'] ?? '')) ?: 'Chú rể';
        $bride = trim((string) ($context['bride_name'] ?? '')) ?: 'Cô dâu';
        $date = trim((string) ($context['wedding_date'] ?? '')) ?: 'ngày hạnh phúc hôm nay';

        return match ($type) {
            'speech_groom' => "### Bài Phát Biểu Đại Diện Họ Nhà Trai\n\n"
                ."Kính thưa các cụ, các ông bà, cô dì chú bác cùng toàn thể quan viên hai họ và quý vị khách quý,\n\n"
                ."Hôm nay, vào ngày {$date}, trong không khí hân hoan và đầm ấm, tôi xin thay mặt gia đình họ nhà trai gửi lời chào trân trọng và lời chúc sức khỏe tới gia đình họ nhà gái cùng toàn thể quý khách.\n\n"
                ."Sau thời gian tìm hiểu, được sự vun đắp của hai bên gia đình, cháu {$groom} và cháu {$bride} đã quyết định nên duyên vợ chồng. Gia đình chúng tôi xin trân trọng cảm ơn gia đình ông bà thông gia đã sinh thành, dưỡng dục cháu {$bride} và hôm nay tin tưởng gửi gắm cháu về làm dâu hiền của gia đình.\n\n"
                .'Chúc hai cháu trăm năm hạnh phúc, luôn yêu thương và đồng hành cùng nhau. Xin trân trọng cảm ơn!',

            'speech_bride' => "### Bài Phát Biểu Đại Diện Họ Nhà Gái\n\n"
                ."Kính thưa quan viên hai họ, quý vị đại biểu và bạn bè thân hữu của hai cháu,\n\n"
                ."Hôm nay là ngày {$date} — một dấu mốc thật ý nghĩa đối với hai gia đình chúng tôi. Thay mặt họ nhà gái, tôi xin chân thành cảm ơn sự đón tiếp chu đáo của gia đình họ nhà trai và sự hiện diện quý báu của tất cả quý vị.\n\n"
                ."Từ hôm nay, cháu {$bride} chính thức làm dâu gia đình anh chị thông gia. Gia đình chúng tôi rất mong ông bà thông gia thương yêu, chỉ bảo để hai cháu {$groom} và {$bride} xây dựng tổ ấm thuận hòa, bền vững.\n\n"
                .'Kính chúc toàn thể quý vị dồi dào sức khỏe và có một buổi tiệc thật đầm ấm. Xin cảm ơn!',

            'vows' => "### Lời Thề Nguyện Của {$groom} & {$bride}\n\n"
                ."Gửi người bạn đời của anh/em,\n\n"
                ."Cảm ơn vì đã bước vào cuộc đời nhau bằng sự chân thành và kiên nhẫn. Trước sự chứng kiến của gia đình hai bên và những người thương yêu nhất trong ngày {$date}, chúng ta hứa sẽ:\n"
                ."- Luôn lắng nghe nhau trước khi phán xét, đặc biệt trong những ngày nhiều áp lực.\n"
                ."- Giữ sự tôn trọng, minh bạch và sẻ chia trong mọi quyết định lớn nhỏ của gia đình.\n"
                ."- Cùng nhau vun đắp một mái nhà bình yên, nơi cả hai luôn muốn trở về mỗi ngày.\n\n"
                .'Từ hôm nay và mãi về sau, cảm ơn vì đã chọn đồng hành cùng nhau.',

            'thank_you' => "### Tin Nhắn Cảm Ơn Khách Mời Sau Lễ Cưới\n\n"
                ."Gia đình {$groom} & {$bride} xin gửi lời cảm ơn chân thành nhất tới Anh/Chị/Bạn đã dành thời gian quý báu đến chung vui và gửi lời chúc phúc trong ngày cưới ({$date}).\n\n"
                ."Sự hiện diện và tình cảm ấm áp của mọi người là món quà tuyệt vời nhất giúp ngày trọng đại của chúng em thêm trọn vẹn. Trong lúc tổ chức nếu có điều gì sơ suất, rất mong mọi người hoan hỷ bỏ qua.\n\n"
                .'Kính chúc Anh/Chị/Bạn cùng gia đình luôn mạnh khỏe, bình an và thật nhiều niềm vui!',

            'workspace_advice' => "### Gợi Ý Hành Động Ưu Tiên Cho Kế Hoạch Hôn Lễ ({$groom} & {$bride})\n\n"
                ."1. **Khóa các mốc không thể dời**: Xác nhận lại ngày lễ chính thức ({$date}), sảnh tiệc và giờ cử hành nghi lễ hai họ.\n"
                ."2. **Rà soát dòng tiền & lịch thanh toán**: Đối chiếu phần *Còn phải trả* trong bảng Ngân sách, luôn giữ lại 5–10% quỹ dự phòng cho chi phí phát sinh ngày cưới.\n"
                ."3. **Chốt danh sách khách mời & RSVP**: Gửi nhắc nhẹ khách chưa phản hồi trước ngày cưới ít nhất 14 ngày để chốt số mâm tiệc sát thực tế.\n"
                ."4. **Kiểm tra sơ đồ bàn tiệc 2D**: Đảm bảo không có khách bị xếp trùng bàn và các bàn gia đình hai họ gần sân khấu.\n"
                .'5. **Rà soát xung đột lịch trình ngày cưới**: Dành tối thiểu 20–30 phút đệm di chuyển giữa nhà gái, nhà trai và nhà hàng.',

            default => "### Kịch Bản MC Lễ Thành Hôn – {$groom} & {$bride}\n\n"
                ."1. **Đón khách & Ổn định chỗ ngồi (10 phút)**: Nhạc nền nhẹ nhàng, mời quan khách ổn định bàn tiệc.\n"
                ."2. **Tuyên bố lý do & Giới thiệu hai họ (5 phút)**: MC gửi lời chào mừng ngày {$date}, giới thiệu đại diện nhà trai và nhà gái.\n"
                ."3. **Nghi thức bước vào lễ đường (5 phút)**: Mời chú rể {$groom} và cô dâu {$bride} tiến lên sân khấu trong tràng pháo tay.\n"
                ."4. **Phát biểu cảm ơn của đại diện hai gia đình (5 phút)**: Cha/mẹ hoặc trưởng tộc hai bên gửi lời tri ân quan khách.\n"
                ."5. **Trao nhẫn cưới – Rót rượu Champagne – Cắt bánh cưới (10 phút)**: Nghi thức đánh dấu khởi đầu hôn nhân.\n"
                .'6. **Nâng ly khai tiệc & Chúc mừng (5 phút)**: Toàn thể hội trường cùng nâng ly chúc phúc đôi tân lang – tân nương.',
        };
    }
}
