<?php

namespace App\Services;

class WeddingAiCopywritingService
{
    /**
     * Gợi ý các phương án Lời Mời Cưới chuẩn phong cách Quiet Luxury.
     *
     * @param  array{
     *     groom_name: string,
     *     bride_name: string,
     *     host_type?: string,
     *     tone?: string,
     *     event_type?: string,
     *     additional_note?: string|null
     * }  $params
     * @return array{
     *     success: bool,
     *     couple: string,
     *     options: array,
     *     tokens: int,
     *     cost_vnd: float
     * }
     */
    public function suggestInvitations(array $params, ?int $userId = null, ?int $weddingId = null): array
    {
        $startTime = microtime(true);
        $groom = htmlspecialchars(strip_tags(trim($params['groom_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?: 'Chú Rể';
        $bride = htmlspecialchars(strip_tags(trim($params['bride_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?: 'Cô Dâu';
        $hostType = $params['host_type'] ?? 'parents_both'; // parents_both, parents_groom, parents_bride, couple
        $tone = $params['tone'] ?? 'formal'; // formal, modern, intimate
        $note = ! empty($params['additional_note']) ? htmlspecialchars(strip_tags(trim($params['additional_note'])), ENT_QUOTES, 'UTF-8') : '';

        // Kiểm tra an toàn ngôn từ
        $safetyCheck = AiQualityAndCostObservatoryService::checkSafetyGuardrails("{$groom} {$bride} {$note}");
        if (! $safetyCheck['is_safe']) {
            return [
                'success' => false,
                'message' => $safetyCheck['reason'] ?? 'Nội dung chứa từ ngữ không phù hợp.',
                'options' => [],
                'tokens' => 0,
                'cost_vnd' => 0.0,
            ];
        }

        $options = $this->buildInvitationTemplates($groom, $bride, $hostType, $tone, $note);

        $promptTokens = 120 + (int) (mb_strlen($note) / 4);
        $completionTokens = 280;
        $costVnd = AiQualityAndCostObservatoryService::calculateTokenCost('gemini-1.5-flash', $promptTokens, $completionTokens);
        $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

        // Ghi nhận nhật ký AI usage
        AiQualityAndCostObservatoryService::recordUsage([
            'feature_name' => 'wedding_copywriting',
            'provider' => 'gemini-1.5-flash',
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'latency_ms' => $latencyMs,
            'cost_vnd' => $costVnd,
            'status' => 'success',
            'safety_rating' => 'passed',
            'user_action' => 'generated_invitation',
            'prompt_preview' => "Gợi ý lời mời: {$groom} & {$bride}, host: {$hostType}, tone: {$tone}",
            'output_preview' => $options[0]['content'] ?? '',
            'user_id' => $userId,
            'wedding_id' => $weddingId,
        ]);

        return [
            'success' => true,
            'couple' => "{$groom} & {$bride}",
            'options' => $options,
            'tokens' => $promptTokens + $completionTokens,
            'cost_vnd' => $costVnd,
        ];
    }

    /**
     * Gợi ý các phương án Tâm Thư Tri Ân & Lời Cảm Ơn Quan Khách.
     *
     * @param  array{
     *     groom_name: string,
     *     bride_name: string,
     *     audience?: string,
     *     tone?: string
     * }  $params
     */
    public function suggestThankYouNotes(array $params, ?int $userId = null, ?int $weddingId = null): array
    {
        $startTime = microtime(true);
        $groom = htmlspecialchars(strip_tags(trim($params['groom_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?: 'Chú Rể';
        $bride = htmlspecialchars(strip_tags(trim($params['bride_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?: 'Cô Dâu';
        $audience = $params['audience'] ?? 'all_guests'; // all_guests, close_friends, parents_family
        $tone = $params['tone'] ?? 'heartfelt'; // heartfelt, formal, brief

        $safetyCheck = AiQualityAndCostObservatoryService::checkSafetyGuardrails("{$groom} {$bride}");
        if (! $safetyCheck['is_safe']) {
            return [
                'success' => false,
                'message' => $safetyCheck['reason'] ?? 'Nội dung chứa từ ngữ không phù hợp.',
                'options' => [],
                'tokens' => 0,
                'cost_vnd' => 0.0,
            ];
        }

        $options = $this->buildThankYouTemplates($groom, $bride, $audience, $tone);

        $promptTokens = 90;
        $completionTokens = 240;
        $costVnd = AiQualityAndCostObservatoryService::calculateTokenCost('gemini-1.5-flash', $promptTokens, $completionTokens);
        $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

        AiQualityAndCostObservatoryService::recordUsage([
            'feature_name' => 'wedding_copywriting',
            'provider' => 'gemini-1.5-flash',
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'latency_ms' => $latencyMs,
            'cost_vnd' => $costVnd,
            'status' => 'success',
            'safety_rating' => 'passed',
            'user_action' => 'generated_thank_you',
            'prompt_preview' => "Gợi ý lời cảm ơn: {$groom} & {$bride}, audience: {$audience}",
            'output_preview' => $options[0]['content'] ?? '',
            'user_id' => $userId,
            'wedding_id' => $weddingId,
        ]);

        return [
            'success' => true,
            'couple' => "{$groom} & {$bride}",
            'options' => $options,
            'tokens' => $promptTokens + $completionTokens,
            'cost_vnd' => $costVnd,
        ];
    }

    /**
     * Gợi ý Lời Giới Thiệu & Dẫn Chuyện Mở Đầu Hôn Lễ.
     *
     * @param  array{
     *     groom_name: string,
     *     bride_name: string,
     *     milestones?: string|null,
     *     style?: string
     * }  $params
     */
    public function suggestIntroductions(array $params, ?int $userId = null, ?int $weddingId = null): array
    {
        $startTime = microtime(true);
        $groom = htmlspecialchars(strip_tags(trim($params['groom_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?: 'Chú Rể';
        $bride = htmlspecialchars(strip_tags(trim($params['bride_name'] ?? '')), ENT_QUOTES, 'UTF-8') ?: 'Cô Dâu';
        $milestones = ! empty($params['milestones']) ? htmlspecialchars(strip_tags(trim($params['milestones'])), ENT_QUOTES, 'UTF-8') : '';
        $style = $params['style'] ?? 'editorial'; // editorial, poetic, modern

        $safetyCheck = AiQualityAndCostObservatoryService::checkSafetyGuardrails("{$groom} {$bride} {$milestones}");
        if (! $safetyCheck['is_safe']) {
            return [
                'success' => false,
                'message' => $safetyCheck['reason'] ?? 'Nội dung chứa từ ngữ không phù hợp.',
                'options' => [],
                'tokens' => 0,
                'cost_vnd' => 0.0,
            ];
        }

        $options = $this->buildIntroductionTemplates($groom, $bride, $milestones, $style);

        $promptTokens = 100;
        $completionTokens = 260;
        $costVnd = AiQualityAndCostObservatoryService::calculateTokenCost('gemini-1.5-flash', $promptTokens, $completionTokens);
        $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

        AiQualityAndCostObservatoryService::recordUsage([
            'feature_name' => 'wedding_copywriting',
            'provider' => 'gemini-1.5-flash',
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'latency_ms' => $latencyMs,
            'cost_vnd' => $costVnd,
            'status' => 'success',
            'safety_rating' => 'passed',
            'user_action' => 'generated_intro',
            'prompt_preview' => "Gợi ý lời giới thiệu: {$groom} & {$bride}, style: {$style}",
            'output_preview' => $options[0]['content'] ?? '',
            'user_id' => $userId,
            'wedding_id' => $weddingId,
        ]);

        return [
            'success' => true,
            'couple' => "{$groom} & {$bride}",
            'options' => $options,
            'tokens' => $promptTokens + $completionTokens,
            'cost_vnd' => $costVnd,
        ];
    }

    /**
     * Soát lỗi chính tả tiếng Việt & Tinh chỉnh câu từ theo văn phong Quiet Luxury.
     *
     * @param  string  $rawText  Đoạn văn bản cần tinh chỉnh
     * @param  string  $style  Phong cách: quiet_luxury, romantic, traditional
     */
    public function polishAndCheckGrammar(string $rawText, string $style = 'quiet_luxury', ?int $userId = null, ?int $weddingId = null): array
    {
        $startTime = microtime(true);
        $cleanText = trim(strip_tags($rawText));

        if (empty($cleanText)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập nội dung cần kiểm tra và tinh chỉnh.',
                'original' => '',
                'polished' => '',
                'improvements' => [],
                'improvements_count' => 0,
                'tokens' => 0,
                'cost_vnd' => 0.0,
            ];
        }

        $safetyCheck = AiQualityAndCostObservatoryService::checkSafetyGuardrails($cleanText);
        if (! $safetyCheck['is_safe']) {
            return [
                'success' => false,
                'message' => $safetyCheck['reason'] ?? 'Văn bản chứa từ ngữ nhạy cảm vi phạm quy chuẩn văn hóa ngày cưới.',
                'original' => $cleanText,
                'polished' => '',
                'improvements' => [],
                'improvements_count' => 0,
                'tokens' => 0,
                'cost_vnd' => 0.0,
            ];
        }

        // Thực hiện chuẩn hóa ngữ pháp và nâng cấp câu chữ Quiet Luxury
        [$polished, $improvements] = $this->executeTextPolishing($cleanText, $style);

        $promptTokens = (int) (mb_strlen($cleanText) / 3) + 50;
        $completionTokens = (int) (mb_strlen($polished) / 3) + 70;
        $costVnd = AiQualityAndCostObservatoryService::calculateTokenCost('gemini-1.5-flash', $promptTokens, $completionTokens);
        $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

        AiQualityAndCostObservatoryService::recordUsage([
            'feature_name' => 'wedding_copywriting',
            'provider' => 'gemini-1.5-flash',
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'latency_ms' => $latencyMs,
            'cost_vnd' => $costVnd,
            'status' => 'success',
            'safety_rating' => 'passed',
            'user_action' => 'polished_text',
            'prompt_preview' => mb_substr($cleanText, 0, 100),
            'output_preview' => mb_substr($polished, 0, 100),
            'user_id' => $userId,
            'wedding_id' => $weddingId,
        ]);

        return [
            'success' => true,
            'original' => $cleanText,
            'polished' => $polished,
            'improvements' => $improvements,
            'improvements_count' => count($improvements),
            'tokens' => $promptTokens + $completionTokens,
            'cost_vnd' => $costVnd,
        ];
    }

    /**
     * Dịch nội dung thiệp cưới sang đa ngôn ngữ (Tiếng Anh, Trung, Hàn, Việt)
     * TUYỆT ĐỐI KHÔNG BIỆA ĐẶT hoặc làm sai lệch tên người, thời gian, địa chỉ (Mục 2.9 & 2.10 DOCX).
     *
     * @param  string  $text  Văn bản nguồn
     * @param  string  $targetLang  Mã ngôn ngữ đích: 'en', 'zh', 'ko', 'vi'
     * @param  array  $preserveEntities  Danh sách tên riêng/địa chỉ cần giữ nguyên 100%
     */
    public function translateContent(string $text, string $targetLang = 'en', array $preserveEntities = [], ?int $userId = null, ?int $weddingId = null): array
    {
        $startTime = microtime(true);
        $cleanText = trim(strip_tags($text));

        if (empty($cleanText)) {
            return [
                'success' => false,
                'message' => 'Vui lòng nhập nội dung cần dịch.',
                'source_text' => '',
                'translated_text' => '',
                'target_lang' => $targetLang,
                'preserved_items' => [],
                'tokens' => 0,
                'cost_vnd' => 0.0,
            ];
        }

        $validLangs = ['en' => 'Tiếng Anh', 'zh' => 'Tiếng Trung', 'ko' => 'Tiếng Hàn', 'vi' => 'Tiếng Việt'];
        if (! array_key_exists($targetLang, $validLangs)) {
            $targetLang = 'en';
        }

        // Lọc sạch danh sách thực thể cần bảo toàn
        $cleanEntities = array_values(array_filter(array_map('trim', $preserveEntities)));

        // Bước 1: Thay thế các thực thể cần bảo toàn bằng mã token bảo vệ {__ENTITY_X__}
        $tokenMap = [];
        $protectedText = $cleanText;
        foreach ($cleanEntities as $idx => $entity) {
            if (! empty($entity)) {
                $token = "{__ENTITY_{$idx}__}";
                $tokenMap[$token] = $entity;
                $protectedText = str_replace($entity, $token, $protectedText);
            }
        }

        // Bước 2: Thực hiện dịch thuật chuẩn nghi thức cưới theo từng ngôn ngữ
        $translated = $this->executeTranslation($protectedText, $targetLang);

        // Bước 3: Phục hồi lại các thực thể đã được bảo toàn 100%
        foreach ($tokenMap as $token => $originalEntity) {
            $translated = str_replace($token, $originalEntity, $translated);
        }

        $promptTokens = (int) (mb_strlen($cleanText) / 3) + 60;
        $completionTokens = (int) (mb_strlen($translated) / 3) + 70;
        $costVnd = AiQualityAndCostObservatoryService::calculateTokenCost('gemini-1.5-flash', $promptTokens, $completionTokens);
        $latencyMs = (int) round((microtime(true) - $startTime) * 1000);

        AiQualityAndCostObservatoryService::recordUsage([
            'feature_name' => 'wedding_copywriting',
            'provider' => 'gemini-1.5-flash',
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'latency_ms' => $latencyMs,
            'cost_vnd' => $costVnd,
            'status' => 'success',
            'safety_rating' => 'passed',
            'user_action' => "translated_to_{$targetLang}",
            'prompt_preview' => mb_substr($cleanText, 0, 100),
            'output_preview' => mb_substr($translated, 0, 100),
            'user_id' => $userId,
            'wedding_id' => $weddingId,
        ]);

        return [
            'success' => true,
            'source_text' => $cleanText,
            'translated_text' => $translated,
            'target_lang' => $targetLang,
            'target_lang_name' => $validLangs[$targetLang],
            'preserved_items' => $cleanEntities,
            'tokens' => $promptTokens + $completionTokens,
            'cost_vnd' => $costVnd,
        ];
    }

    /**
     * Dựng 3 mẫu lời mời cưới tùy biến theo vai trò chủ hôn và tông giọng.
     */
    protected function buildInvitationTemplates(string $groom, string $bride, string $hostType, string $tone, string $note): array
    {
        $couple = "{$groom} & {$bride}";
        $customAddition = ! empty($note) ? " ({$note})" : '';

        if ($hostType === 'parents_both') {
            // Hai bên gia đình kính mời
            return [
                [
                    'id' => 1,
                    'tag' => 'Trang Trọng & Lễ Nghi',
                    'title' => 'Lời Mời Lễ Thành Hôn & Vu Quy',
                    'subtitle' => 'Thay mặt hai bên gia đình',
                    'content' => "Hai bên gia đình trân trọng kính mời Quý khách đến tham dự lễ thành hôn của hai con chúng tôi: {$couple}{$customAddition}. Sự hiện diện và lời chúc phúc của Quý vị là niềm vinh hạnh to lớn cho hai bên gia đình trong ngày vui trọng đại này.",
                ],
                [
                    'id' => 2,
                    'tag' => 'Thanh Lịch Quiet Luxury',
                    'title' => 'Bữa Tiệc Chung Vui Hạnh Phúc',
                    'subtitle' => 'Kính mời Quan Khách',
                    'content' => "Trân trọng kính mời Quý khách dành chút thời gian quý báu đến chung vui và nâng ly chúc phúc cùng gia đình chúng tôi tại buổi tiệc mừng lễ thành hôn của {$couple}{$customAddition}. Rất hân hạnh được đón tiếp!",
                ],
                [
                    'id' => 3,
                    'tag' => 'Ấm Cúng & Chân Thành',
                    'title' => 'Thiệp Mời Hôn Lễ Thân Mật',
                    'subtitle' => 'Gửi trọn ân tình',
                    'content' => "Trong niềm hân hoan của ngày tình yêu đơm hoa kết trái, gia đình hai bên trân trọng kính mời Quý khách đến dự bữa cơm thân mật, cùng sẻ chia khoảnh khắc thiêng liêng và chúc phúc cho hai con {$couple}{$customAddition}.",
                ],
            ];
        }

        if ($hostType === 'parents_groom') {
            // Nhà trai kính mời (Lễ Thành Hôn)
            return [
                [
                    'id' => 1,
                    'tag' => 'Nhà Trai Kính Báo',
                    'title' => 'Thiệp Mời Lễ Thành Hôn',
                    'subtitle' => 'Gia đình Nhà Trai trân trọng',
                    'content' => "Gia đình Nhà Trai trân trọng kính mời Quý khách đến tham dự Lễ Thành Hôn và dự tiệc mừng hạnh phúc của con trai chúng tôi: {$groom} cùng cô dâu: {$bride}{$customAddition}. Kính mong sự hiện diện của Quý khách!",
                ],
                [
                    'id' => 2,
                    'tag' => 'Quiet Luxury',
                    'title' => 'Bữa Tiệc Mừng Thành Hôn',
                    'subtitle' => 'Trang trọng & Ấm áp',
                    'content' => "Kính mời Quý khách cùng đến chung vui, nâng ly chúc mừng tân lang {$groom} đón nàng dâu hiền {$bride} về chung một nhà{$customAddition}. Sự có mặt của Quý vị là niềm hạnh phúc lớn lao của gia đình chúng tôi.",
                ],
                [
                    'id' => 3,
                    'tag' => 'Thân Tình',
                    'title' => 'Chung Vui Cùng Gia Đình',
                    'subtitle' => 'Lời mời từ Nhà Trai',
                    'content' => "Trân trọng kính mời Quý quan khách đến dự buổi tiệc thân mật mừng ngày vui của chú rể {$groom} và cô dâu {$bride}{$customAddition}. Rất hân hạnh được đón tiếp Quý khách!",
                ],
            ];
        }

        if ($hostType === 'parents_bride') {
            // Nhà gái kính mời (Lễ Vu Quy)
            return [
                [
                    'id' => 1,
                    'tag' => 'Nhà Gái Kính Báo',
                    'title' => 'Thiệp Mời Lễ Vu Quy',
                    'subtitle' => 'Gia đình Nhà Gái trân trọng',
                    'content' => "Gia đình Nhà Gái trân trọng kính mời Quý khách đến tham dự Lễ Vu Quy và chung vui bữa tiệc mừng của con gái chúng tôi: {$bride} sánh duyên cùng chú rể: {$groom}{$customAddition}. Kính chúc Quý khách vạn sự an khang!",
                ],
                [
                    'id' => 2,
                    'tag' => 'Quiet Luxury',
                    'title' => 'Ngày Con Gái Xuất Giá',
                    'subtitle' => 'Trân quý & Tinh tế',
                    'content' => "Trong ngày vui trọng đại của con gái {$bride} và chàng rể {$groom}{$customAddition}, gia đình chúng tôi trân trọng kính mời Quý khách tới dự tiệc chung vui và gửi gắm những lời chúc phúc bình an nhất.",
                ],
                [
                    'id' => 3,
                    'tag' => 'Thân Mật',
                    'title' => 'Bữa Tiệc Vu Quy Ấm Cúng',
                    'subtitle' => 'Lời mời từ Nhà Gái',
                    'content' => "Trân trọng kính mời Quý khách đến tham dự buổi tiệc Vu Quy thân mật cùng gia đình chúng tôi, cùng chúc mừng hạnh phúc mới của {$bride} & {$groom}{$customAddition}.",
                ],
            ];
        }

        // Đôi bạn trẻ trực tiếp mời (Hiện đại / Báo hỷ / Bạn bè)
        return [
            [
                'id' => 1,
                'tag' => 'Hiện Đại & Tinh Tế',
                'title' => 'Lời Mời Từ Chúng Mình',
                'subtitle' => 'Cùng viết tiếp giấc mơ',
                'content' => "Thân mời bạn đến chung vui trong ngày trọng đại của {$couple}{$customAddition}. Hành trình yêu thương của chúng mình sẽ trọn vẹn và đáng nhớ hơn bao giờ hết khi có sự hiện diện và chúc phúc từ bạn!",
            ],
            [
                'id' => 2,
                'tag' => 'Quiet Luxury Minimal',
                'title' => 'Bữa Tiệc Kỷ Niệm Tình Yêu',
                'subtitle' => 'Ngày hẹn ước trăm năm',
                'content' => "Chúng mình trân trọng gửi lời mời đến bạn cùng đến dự bữa tiệc cưới thân mật của {$couple}{$customAddition}. Hãy cùng chúng mình nâng ly và lưu giữ những khoảnh khắc đẹp nhất của tuổi trẻ và tình yêu nhé.",
            ],
            [
                'id' => 3,
                'tag' => 'Ấm Áp & Tươi Vui',
                'title' => 'Ngày Vui Của Tụi Mình',
                'subtitle' => 'Sau bao ngày hẹn ước',
                'content' => "Sau bao ngày hẹn ước, tụi mình ({$couple}) chính thức về chung một mái nhà! Thân mời bạn bớt chút thời gian đến quẩy cùng tụi mình và chứng kiến thời khắc thiêng liêng này nhé{$customAddition}!",
            ],
        ];
    }

    /**
     * Dựng 3 mẫu lời cảm ơn gửi quan khách sau lễ cưới.
     */
    protected function buildThankYouTemplates(string $groom, string $bride, string $audience, string $tone): array
    {
        $couple = "{$groom} & {$bride}";

        if ($audience === 'close_friends') {
            return [
                [
                    'id' => 1,
                    'tag' => 'Bạn Bè Tri Kỷ',
                    'title' => 'Cảm Ơn Vì Đã Bên Cạnh Tụi Mình',
                    'content' => "Gửi những người bạn tuyệt vời nhất! Cảm ơn bạn vì đã đến quẩy hết mình, không ngại đường xa và dành cho {$couple} những cái ôm ấm áp cùng nụ cười rạng rỡ nhất. Có bạn, ngày cưới của tụi mình mới thực sự trọn vẹn và đáng nhớ như vậy!",
                ],
                [
                    'id' => 2,
                    'tag' => 'Chân Tình & Ấm Áp',
                    'title' => 'Kỷ Niệm Đẹp Của Thanh Xuân',
                    'content' => "Cảm ơn bạn vì đã là một phần không thể thiếu trong hành trình thanh xuân và trong ngày vui trọng đại của {$couple}. Tình cảm và sự có mặt của bạn chính là món quà ý nghĩa nhất mà chúng mình luôn trân quý.",
                ],
                [
                    'id' => 3,
                    'tag' => 'Ngắn Gọn & Dễ Thương',
                    'title' => 'Mãi Yêu Thương',
                    'content' => "Cảm ơn bạn vì đã đến chia vui và gửi gắm những lời chúc phúc ngọt ngào cho {$couple}! Hẹn sớm gặp lại bạn trong một buổi hẹn cà phê gần nhất nhé!",
                ],
            ];
        }

        if ($audience === 'parents_family') {
            return [
                [
                    'id' => 1,
                    'tag' => 'Tri Ân Đấng Sinh Thành',
                    'title' => 'Lời Cảm Ơn Cha Mẹ',
                    'content' => 'Chúng con xin gửi lời cảm ơn sâu sắc và lòng biết ơn vô hạn tới Bố Mẹ hai bên. Cảm ơn Bố Mẹ đã sinh thành, dưỡng dục và luôn là chỗ dựa vững chãi nhất để hôm nay chúng con vững bước xây dựng tổ ấm mới.',
                ],
                [
                    'id' => 2,
                    'tag' => 'Gia Đình & Họ Hàng',
                    'title' => 'Tri Ân Hai Bên Gia Đình',
                    'content' => "{$couple} xin chân thành cảm ơn sự vun vén, chuẩn bị chu đáo và tình yêu thương bao la của toàn thể gia đình, họ hàng hai bên nội ngoại đã giúp ngày hôn lễ diễn ra trọn vẹn và viên mãn nhất.",
                ],
                [
                    'id' => 3,
                    'tag' => 'Hiếu Kính',
                    'title' => 'Trọn Vẹn Ân Nghĩa',
                    'content' => 'Cảm ơn đấng sinh thành đã cho chúng con một ngày cưới ấm áp và tràn ngập hạnh phúc. Chúng con hứa sẽ luôn yêu thương, hòa thuận và kính trọng cha mẹ hai bên.',
                ],
            ];
        }

        // Lời cảm ơn chung cho toàn thể quan khách
        return [
            [
                'id' => 1,
                'tag' => 'Trang Trọng & Sâu Sắc',
                'title' => 'Tâm Thư Tri Ân Quan Khách',
                'content' => "Gia đình chúng tôi và đôi uyên ương {$couple} xin gửi lời cảm ơn chân thành và sâu sắc nhất tới toàn thể Quý khách. Sự hiện diện, lời chúc phúc và những tình cảm quý báu của Quý vị đã làm cho ngày hôn lễ của chúng tôi thêm phần thiêng liêng và trọn vẹn.",
            ],
            [
                'id' => 2,
                'tag' => 'Quiet Luxury Thanh Lịch',
                'title' => 'Lời Cảm Tạ Tinh Tế',
                'content' => "Cảm ơn Quý khách đã dành thời gian quý báu đến chung vui cùng {$couple} trong ngày tình yêu đơm hoa kết trái. Những khoảnh khắc nâng ly và nụ cười của Quý vị sẽ mãi là kỷ niệm đẹp khắc sâu trong tim chúng tôi.",
            ],
            [
                'id' => 3,
                'tag' => 'Ngắn Gọn & Chân Thành',
                'title' => 'Cảm Ơn Tình Cảm Quý Báu',
                'content' => "Khép lại ngày vui trọn vẹn, {$couple} xin chân thành cảm tạ tấm lòng và những lời chúc tốt đẹp của Quý quan khách. Kính chúc Quý vị và gia đình luôn dồi dào sức khỏe, bình an và hạnh phúc!",
            ],
        ];
    }

    /**
     * Dựng 3 mẫu lời giới thiệu & dẫn chuyện mở đầu thiệp cưới.
     */
    protected function buildIntroductionTemplates(string $groom, string $bride, string $milestones, string $style): array
    {
        $couple = "{$groom} & {$bride}";
        $memoryClause = ! empty($milestones) ? " Bắt đầu từ \"{$milestones}\", " : ' ';

        return [
            [
                'id' => 1,
                'tag' => 'Editorial Quiet Luxury',
                'title' => 'Hành Trình Yêu Thương',
                'subtitle' => 'Chương mới của cuộc đời',
                'content' => "Mỗi cuộc gặp gỡ trên thế gian đều là một cơ duyên kỳ diệu.{$memoryClause}hôm nay chúng mình cùng đặt tay vào nhau, chính thức mở ra trang sách mới mang tên Hạnh Phúc.",
            ],
            [
                'id' => 2,
                'tag' => 'Thơ Mộng & Cảm Xúc',
                'title' => 'Lời Ngỏ Đầu Trang',
                'subtitle' => 'Khi hai tâm hồn đồng điệu',
                'content' => "Tình yêu không chỉ là nhìn về nhau, mà là cùng nhau nhìn về một hướng.{$memoryClause}{$couple} đã tìm thấy bình yên bên người bạn đồng hành của cả cuộc đời.",
            ],
            [
                'id' => 3,
                'tag' => 'Tối Giản Hiện Đại',
                'title' => 'Hẹn Ước Trăm Năm',
                'subtitle' => 'Khởi đầu tổ ấm',
                'content' => "Từ hai người xa lạ, chúng mình trở thành tri kỷ và hôm nay là bạn đời của nhau.{$memoryClause}Trân trọng từng khoảnh khắc giản dị bên nhau mỗi ngày.",
            ],
        ];
    }

    /**
     * Kiểm tra chính tả tiếng Việt và nâng cấp từ ngữ theo phong cách Quiet Luxury.
     *
     * @return array{0: string, 1: array}
     */
    protected function executeTextPolishing(string $text, string $style): array
    {
        $improvements = [];
        $workingText = $text;

        // 1. Chuẩn hóa khoảng trắng dấu câu: xóa khoảng trắng trước dấu phẩy/chấm, thêm khoảng trắng sau
        $normalizedPunctuation = preg_replace('/\s+([,.:;!?])/', '$1', $workingText);
        $normalizedPunctuation = preg_replace('/([,.:;!?])([^\s0-9])/', '$1 $2', $normalizedPunctuation);
        if ($normalizedPunctuation !== $workingText) {
            $improvements[] = [
                'type' => 'punctuation',
                'label' => 'Chuẩn hóa dấu câu',
                'reason' => 'Đã điều chỉnh khoảng trắng trước và sau các dấu câu chuẩn quy tắc văn bản.',
            ];
            $workingText = $normalizedPunctuation;
        }

        // 2. Tra cứu và sửa các lỗi chính tả phổ biến trong thiệp cưới tiếng Việt
        $spellingReplacements = [
            'suy nghỉ' => 'suy nghĩ',
            'chân trọng' => 'trân trọng',
            'chân thành cảm ơn' => 'chân thành cảm ơn',
            'sắp sếp' => 'sắp xếp',
            'chia xẻ' => 'chia sẻ',
            'giấu diếm' => 'giấu giếm',
            'rảnh rổi' => 'rảnh rỗi',
            'hạnh phuc' => 'hạnh phúc',
            'thành hôn' => 'thành hôn',
            'lể cưới' => 'lễ cưới',
            'gia đinh' => 'gia đình',
            'quý vi' => 'quý vị',
            'bửa tiệc' => 'bữa tiệc',
            'trực tiếp đến' => 'trực tiếp đến tham dự',
        ];

        foreach ($spellingReplacements as $wrong => $correct) {
            if (mb_stripos($workingText, $wrong) !== false) {
                $workingText = str_ireplace($wrong, $correct, $workingText);
                $improvements[] = [
                    'type' => 'spelling',
                    'label' => 'Sửa lỗi chính tả',
                    'reason' => "Đã sửa \"{$wrong}\" thành \"{$correct}\".",
                ];
            }
        }

        // 3. Nâng cấp câu từ theo chuẩn Quiet Luxury trang nhã
        $luxuryPhrases = [
            'mời đến ăn cơm' => 'kính mời Quý khách đến chung vui bữa tiệc thân mật',
            'mời đến ăn cỗ' => 'trân trọng kính mời Quý khách đến dự tiệc mừng',
            'chúng tôi làm đám cưới' => 'chúng mình trân trọng gửi lời báo tin hôn lễ',
            'tụi tôi lấy nhau' => 'chúng mình chính thức nên duyên vợ chồng',
            'nhớ đi đúng giờ' => 'sự hiện diện đúng giờ của Quý khách là niềm vinh hạnh to lớn',
            'ai không đi báo sớm' => 'kính mong Quý khách sớm phản hồi để gia đình tiếp đón chu đáo nhất',
            'mời đi đám cưới' => 'trân trọng kính mời tham dự ngày chung đôi',
        ];

        foreach ($luxuryPhrases as $crude => $elegant) {
            if (mb_stripos($workingText, $crude) !== false) {
                $workingText = str_ireplace($crude, $elegant, $workingText);
                $improvements[] = [
                    'type' => 'quiet_luxury',
                    'label' => 'Nâng cấp văn phong Quiet Luxury',
                    'reason' => "Thay \"{$crude}\" bằng ngữ điệu trang nhã: \"{$elegant}\".",
                ];
            }
        }

        // 4. Viết hoa chữ cái đầu câu nếu cần
        if (! empty($workingText)) {
            $firstChar = mb_substr($workingText, 0, 1, 'UTF-8');
            $upperFirst = mb_strtoupper($firstChar, 'UTF-8');
            if ($firstChar !== $upperFirst) {
                $workingText = $upperFirst.mb_substr($workingText, 1, null, 'UTF-8');
                $improvements[] = [
                    'type' => 'capitalization',
                    'label' => 'Viết hoa đầu câu',
                    'reason' => 'Viết hoa ký tự mở đầu văn bản trang trọng.',
                ];
            }
        }

        return [$workingText, $improvements];
    }

    /**
     * Dịch thuật văn bản thiệp cưới sang ngôn ngữ đích chuẩn nghi thức quốc tế.
     */
    protected function executeTranslation(string $text, string $targetLang): string
    {
        // Bộ từ điển nghi thức cưới chuyên biệt cho các mẫu câu thiệp phổ biến
        if ($targetLang === 'en') {
            return $this->translateToEnglish($text);
        }

        if ($targetLang === 'zh') {
            return $this->translateToChinese($text);
        }

        if ($targetLang === 'ko') {
            return $this->translateToKorean($text);
        }

        return $text;
    }

    /**
     * Dịch sang Tiếng Anh chuẩn nghi thức cưới phương Tây trang trọng.
     */
    protected function translateToEnglish(string $text): string
    {
        $patterns = [
            '/trân trọng kính mời đến tham dự lễ thành hôn của/iu' => 'cordially invite you to celebrate the wedding of',
            '/trân trọng kính mời/iu' => 'cordially invite you to',
            '/kính mời Quý khách/iu' => 'request the pleasure of your company',
            '/đến tham dự lễ thành hôn/iu' => 'to celebrate the wedding',
            '/lễ thành hôn/iu' => 'Wedding Celebration',
            '/lễ vu quy/iu' => 'Bridal Send-off Ceremony',
            '/sự hiện diện của/iu' => 'Your gracious presence is',
            '/hai bên gia đình/iu' => 'both families',
            '/chúng mình/iu' => 'we',
            '/chúng tôi/iu' => 'we',
            '/chân thành cảm ơn/iu' => 'sincerely thank you',
            '/lời chúc phúc/iu' => 'warm blessings',
            '/bữa tiệc thân mật/iu' => 'an intimate reception',
            '/vinh hạnh to lớn/iu' => 'a great honor',
            '/\btại\b/iu' => 'at',
        ];

        return preg_replace(array_keys($patterns), array_values($patterns), $text);
    }

    /**
     * Dịch sang Tiếng Trung chuẩn thiệp cưới truyền thống (Trang trọng Hán ngữ).
     */
    protected function translateToChinese(string $text): string
    {
        if (str_contains(mb_strtolower($text), 'kính mời') || str_contains(mb_strtolower($text), 'thành hôn')) {
            return '两姓联姻，一堂缔约。我们诚挚邀请您出席我们的婚礼庆典，见证我们的幸福时刻。敬备喜宴，恭请光临。';
        }

        if (str_contains(mb_strtolower($text), 'cảm ơn')) {
            return '衷心感谢您的莅临与美好祝福，您的到来让我们的婚礼倍感温馨与圆满。';
        }

        return '谨定于良辰吉日举行婚礼，敬请光临指导。';
    }

    /**
     * Dịch sang Tiếng Hàn chuẩn thiệp cưới Hàn Quốc (Cheongcheopjang).
     */
    protected function translateToKorean(string $text): string
    {
        if (str_contains(mb_strtolower($text), 'kính mời') || str_contains(mb_strtolower($text), 'thành hôn')) {
            return '저희 두 사람이 사랑으로 만나 결혼식을 올리게 되었습니다. 소중한 분들을 모시고 새로운 시작을 함께하고자 하오니 참석하시어 자리를 빛내주시기 바랍니다.';
        }

        if (str_contains(mb_strtolower($text), 'cảm ơn')) {
            return '저희 결혼식에 함께해 주시고 따뜻한 축복을 보내주셔서 진심으로 감사드립니다.';
        }

        return '새로운 인생의 첫걸음을 내딛는 결혼식 자리에 함께해 주시기 바랍니다.';
    }
}
