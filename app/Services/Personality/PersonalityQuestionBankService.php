<?php

declare(strict_types=1);

namespace App\Services\Personality;

/**
 * Kho Câu Hỏi Chuẩn Hóa Độc Lập Của Mây Hẹn (96 câu 16 nhóm tính cách + 50 câu Big Five IPIP).
 *
 * - 100% câu hỏi 16 nhóm được tự biên soạn gần gũi đời sống Việt Nam (họp nhóm, gặp gỡ bạn bè, lập kế hoạch, giải quyết bất đồng, gia đình).
 * - Không sao chép câu hỏi MBTI® có bản quyền hay 16Personalities.
 * - Cân bằng tuyệt đối câu thuận (+) và câu đảo (-) ở cả 3 phiên bản Quick (32), Standard (64), Deep (96).
 * - Big Five sử dụng cấu trúc 50 câu IPIP (Public Domain — ipip.ori.org), 10 câu/chiều, giữ nguyên hướng chấm chuẩn.
 */
class PersonalityQuestionBankService
{
    /**
     * Lấy bộ câu hỏi đầy đủ theo mã phương pháp.
     *
     * @return list<array{id: string, dimension: string, direction: int, tier: string, context: string, text: string}>
     */
    public function getQuestionsForMethod(string $methodId): array
    {
        if ($methodId === PersonalityMethodRegistry::BIG_FIVE_IPIP50) {
            return $this->bigFiveIpip50Questions();
        }

        $all = $this->sixteenTypeQuestions();

        return match ($methodId) {
            PersonalityMethodRegistry::QUICK_16 => array_values(array_filter($all, fn (array $q): bool => $q['tier'] === 'quick')),
            PersonalityMethodRegistry::STANDARD_16 => array_values(array_filter($all, fn (array $q): bool => in_array($q['tier'], ['quick', 'standard'], true))),
            default => $all,
        };
    }

    /**
     * Tạo thứ tự câu hỏi đan xen đều các chiều (có thể trộn nhẹ theo seed cố định của phiên).
     *
     * @return list<string>
     */
    public function buildDeterministicOrder(string $methodId, string $seed): array
    {
        $questions = $this->getQuestionsForMethod($methodId);
        $grouped = [];
        foreach ($questions as $q) {
            $grouped[$q['dimension']][] = $q['id'];
        }

        // Xáo nhẹ trong nội bộ từng chiều dựa trên seed cố định để giữ nguyên khi refresh
        foreach ($grouped as $dim => &$ids) {
            usort($ids, fn (string $a, string $b): int => strcmp(hash('sha256', $seed.'|'.$a), hash('sha256', $seed.'|'.$b)));
        }
        unset($ids);

        // Đan xen vòng tròn các chiều để người làm bài không thấy nhiều câu cùng 1 chiều liên tiếp
        $order = [];
        $dims = array_keys($grouped);
        $maxLen = max(array_map('count', $grouped));
        for ($i = 0; $i < $maxLen; $i++) {
            foreach ($dims as $dim) {
                if (isset($grouped[$dim][$i])) {
                    $order[] = $grouped[$dim][$i];
                }
            }
        }

        return $order;
    }

    /**
     * @return array<string, array{id: string, dimension: string, direction: int, tier: string, context: string, text: string}>
     */
    public function mapById(string $methodId): array
    {
        $map = [];
        foreach ($this->getQuestionsForMethod($methodId) as $q) {
            $map[$q['id']] = $q;
        }

        return $map;
    }

    /**
     * 96 câu hỏi 16 nhóm tính cách (24 câu/chiều: 8 quick [4+,4-], 8 standard [4+,4-], 8 deep [4+,4-]).
     * Quy ước chiều dương (+1): E (EI), N (SN), T (TF), J (JP). Chiều đảo (-1): I, S, F, P.
     *
     * @return list<array{id: string, dimension: string, direction: int, tier: string, context: string, text: string}>
     */
    public function sixteenTypeQuestions(): array
    {
        return [
            // ==================== EI (24 câu: +1 = E, -1 = I) ====================
            ['id' => 'EI01', 'dimension' => 'EI', 'direction' => 1, 'tier' => 'quick', 'context' => 'giao_tiep', 'text' => 'Khi có một ý tưởng mới, tôi thường thích trao đổi ngay với người khác để ý tưởng rõ ràng hơn.'],
            ['id' => 'EI02', 'dimension' => 'EI', 'direction' => -1, 'tier' => 'quick', 'context' => 'nang_luong', 'text' => 'Sau khi tham gia một buổi gặp mặt đông người, tôi thường muốn dành thời gian riêng để lấy lại sự tập trung.'],
            ['id' => 'EI03', 'dimension' => 'EI', 'direction' => 1, 'tier' => 'quick', 'context' => 'xa_hoi', 'text' => 'Tôi cảm thấy tràn đầy năng lượng hơn khi trò chuyện và kết nối với nhiều gương mặt mới trong một sự kiện.'],
            ['id' => 'EI04', 'dimension' => 'EI', 'direction' => -1, 'tier' => 'quick', 'context' => 'giao_tiep', 'text' => 'Trước khi phát biểu trong cuộc họp nhóm, tôi thích suy nghĩ thấu đáo trong đầu rồi mới chia sẻ.'],
            ['id' => 'EI05', 'dimension' => 'EI', 'direction' => 1, 'tier' => 'quick', 'context' => 'cong_viec', 'text' => 'Tôi thích làm việc trong không gian có đồng đội trao đổi qua lại hơn là làm một mình suốt cả ngày.'],
            ['id' => 'EI06', 'dimension' => 'EI', 'direction' => -1, 'tier' => 'quick', 'context' => 'nang_luong', 'text' => 'Một buổi tối cuối tuần yên tĩnh làm việc mình thích giúp tôi hồi phục tinh thần tốt hơn một bữa tiệc sôi động.'],
            ['id' => 'EI07', 'dimension' => 'EI', 'direction' => 1, 'tier' => 'quick', 'context' => 'xa_hoi', 'text' => 'Tôi thường là người chủ động bắt chuyện để phá tan bầu không khí im lặng khi gặp nhóm mới.'],
            ['id' => 'EI08', 'dimension' => 'EI', 'direction' => -1, 'tier' => 'quick', 'context' => 'xa_hoi', 'text' => 'Tôi thường cảm thấy thoải mái nhất khi trò chuyện sâu với một hoặc hai người thân quen thay vì một nhóm lớn.'],

            ['id' => 'EI09', 'dimension' => 'EI', 'direction' => 1, 'tier' => 'standard', 'context' => 'giao_tiep', 'text' => 'Trong lúc nói chuyện, suy nghĩ của tôi thường tự mạch lạc dần nhờ việc diễn đạt thành lời.'],
            ['id' => 'EI10', 'dimension' => 'EI', 'direction' => -1, 'tier' => 'standard', 'context' => 'giao_tiep', 'text' => 'Tôi thường giữ kín cảm xúc và kế hoạch cá nhân cho đến khi thực sự thân thiết với ai đó.'],
            ['id' => 'EI11', 'dimension' => 'EI', 'direction' => 1, 'tier' => 'standard', 'context' => 'cong_viec', 'text' => 'Tôi thích tham gia các buổi thảo luận mở để nghe nhiều góc nhìn ngay từ bước đầu.'],
            ['id' => 'EI12', 'dimension' => 'EI', 'direction' => -1, 'tier' => 'standard', 'context' => 'cong_viec', 'text' => 'Khi cần xử lý một nhiệm vụ quan trọng, tôi thích tìm góc yên tĩnh ít bị gián đoạn nhất.'],
            ['id' => 'EI13', 'dimension' => 'EI', 'direction' => 1, 'tier' => 'standard', 'context' => 'nang_luong', 'text' => 'Nếu phải ở một mình quá lâu trong nhiều ngày liên tiếp, tôi dễ cảm thấy thiếu hứng khởi.'],
            ['id' => 'EI14', 'dimension' => 'EI', 'direction' => -1, 'tier' => 'standard', 'context' => 'nang_luong', 'text' => 'Lịch hẹn gặp gỡ dày đặc cả tuần khiến tôi nhanh cảm thấy mệt mỏi dù mọi người rất thân thiện.'],
            ['id' => 'EI15', 'dimension' => 'EI', 'direction' => 1, 'tier' => 'standard', 'context' => 'xa_hoi', 'text' => 'Tôi dễ dàng hòa nhập vào câu chuyện của một nhóm bạn mới quen ngay từ lần gặp đầu tiên.'],
            ['id' => 'EI16', 'dimension' => 'EI', 'direction' => -1, 'tier' => 'standard', 'context' => 'xa_hoi', 'text' => 'Khi đến một buổi tiệc cưới hoặc họp lớp đông người, tôi thường quan sát trước một lúc rồi mới chọn người bắt chuyện.'],

            ['id' => 'EI17', 'dimension' => 'EI', 'direction' => 1, 'tier' => 'deep', 'context' => 'tinh_cam', 'text' => 'Khi gặp chuyện vui hoặc áp lực, tôi muốn gọi điện hoặc gặp ngay bạn thân để chia sẻ.'],
            ['id' => 'EI18', 'dimension' => 'EI', 'direction' => -1, 'tier' => 'deep', 'context' => 'tinh_cam', 'text' => 'Khi có tâm sự hoặc mệt mỏi, tôi thường tự tiêu hóa trong im lặng trước khi kể cho ai nghe.'],
            ['id' => 'EI19', 'dimension' => 'EI', 'direction' => 1, 'tier' => 'deep', 'context' => 'hoc_tap', 'text' => 'Tôi tiếp thu kiến thức tốt hơn khi được thảo luận nhóm và đặt câu hỏi qua lại trực tiếp.'],
            ['id' => 'EI20', 'dimension' => 'EI', 'direction' => -1, 'tier' => 'deep', 'context' => 'hoc_tap', 'text' => 'Tôi thích tự đọc tài liệu và ghi chép một mình trước khi trao đổi với người khác.'],
            ['id' => 'EI21', 'dimension' => 'EI', 'direction' => 1, 'tier' => 'deep', 'context' => 'xa_hoi', 'text' => 'Tôi có xu hướng duy trì một mạng lưới quen biết rộng ở nhiều môi trường khác nhau.'],
            ['id' => 'EI22', 'dimension' => 'EI', 'direction' => -1, 'tier' => 'deep', 'context' => 'xa_hoi', 'text' => 'Tôi ưu tiên dành thời gian cho một vài mối quan hệ bền chặt hơn là mở rộng nhiều kết nối mới.'],
            ['id' => 'EI23', 'dimension' => 'EI', 'direction' => 1, 'tier' => 'deep', 'context' => 'giao_tiep', 'text' => 'Trong các cuộc trò chuyện, tôi thường phản hồi khá nhanh và hào hứng tiếp nối mạch nói.'],
            ['id' => 'EI24', 'dimension' => 'EI', 'direction' => -1, 'tier' => 'deep', 'context' => 'giao_tiep', 'text' => 'Tôi thường thích nhắn tin hoặc viết ra để diễn đạt những suy nghĩ sâu hơn là nói ứng biến ngay lập tức.'],

            // ==================== SN (24 câu: +1 = N, -1 = S) ====================
            ['id' => 'SN01', 'dimension' => 'SN', 'direction' => 1, 'tier' => 'quick', 'context' => 'nhan_thuc', 'text' => 'Khi đọc một thông tin mới, tôi thường nghĩ ngay đến những khả năng có thể phát triển từ thông tin đó.'],
            ['id' => 'SN02', 'dimension' => 'SN', 'direction' => -1, 'tier' => 'quick', 'context' => 'hoc_tap', 'text' => 'Khi học một công việc mới, tôi thích bắt đầu từ ví dụ cụ thể và các bước thực hiện rõ ràng.'],
            ['id' => 'SN03', 'dimension' => 'SN', 'direction' => 1, 'tier' => 'quick', 'context' => 'nhan_thuc', 'text' => 'Tôi thường bị thu hút bởi bức tranh tổng thể và ý nghĩa sâu xa hơn là từng chi tiết rời rạc.'],
            ['id' => 'SN04', 'dimension' => 'SN', 'direction' => -1, 'tier' => 'quick', 'context' => 'cong_viec', 'text' => 'Tôi tin tưởng hơn vào những dữ kiện đã được kiểm chứng thực tế so với các giả thuyết mới mẻ.'],
            ['id' => 'SN05', 'dimension' => 'SN', 'direction' => 1, 'tier' => 'quick', 'context' => 'sang_tao', 'text' => 'Tôi thích tưởng tượng cách một sản phẩm hoặc dự án có thể thay đổi trong vài năm tới.'],
            ['id' => 'SN06', 'dimension' => 'SN', 'direction' => -1, 'tier' => 'quick', 'context' => 'cong_viec', 'text' => 'Tôi thích áp dụng phương pháp đã chứng minh hiệu quả thay vì thử nghiệm cách hoàn toàn chưa rõ kết quả.'],
            ['id' => 'SN07', 'dimension' => 'SN', 'direction' => 1, 'tier' => 'quick', 'context' => 'giao_tiep', 'text' => 'Khi giải thích một vấn đề, tôi hay dùng hình ảnh ẩn dụ hoặc liên tưởng để người nghe nắm ý lớn.'],
            ['id' => 'SN08', 'dimension' => 'SN', 'direction' => -1, 'tier' => 'quick', 'context' => 'giao_tiep', 'text' => 'Khi nghe người khác trình bày, tôi muốn họ nêu rõ con số, thời gian và sự kiện cụ thể.'],

            ['id' => 'SN09', 'dimension' => 'SN', 'direction' => 1, 'tier' => 'standard', 'context' => 'nhan_thuc', 'text' => 'Tôi thường nhận ra mối liên hệ bất ngờ giữa hai chủ đề tưởng như không liên quan đến nhau.'],
            ['id' => 'SN10', 'dimension' => 'SN', 'direction' => -1, 'tier' => 'standard', 'context' => 'nhan_thuc', 'text' => 'Tôi dễ chú ý đến những chi tiết thực tế xung quanh như ánh sáng, cách bày trí hay chất liệu đồ vật.'],
            ['id' => 'SN11', 'dimension' => 'SN', 'direction' => 1, 'tier' => 'standard', 'context' => 'hoc_tap', 'text' => 'Tôi thích hiểu nguyên lý và khái niệm nền tảng trước khi đi vào thao tác chi tiết.'],
            ['id' => 'SN12', 'dimension' => 'SN', 'direction' => -1, 'tier' => 'standard', 'context' => 'hoc_tap', 'text' => 'Tôi nhớ bài tốt nhất khi tự tay thực hành trên một tình huống thật thay vì chỉ bàn lý thuyết.'],
            ['id' => 'SN13', 'dimension' => 'SN', 'direction' => 1, 'tier' => 'standard', 'context' => 'cong_viec', 'text' => 'Tôi cảm thấy hứng thú khi được cải tiến quy trình theo một hướng mới hoàn toàn.'],
            ['id' => 'SN14', 'dimension' => 'SN', 'direction' => -1, 'tier' => 'standard', 'context' => 'cong_viec', 'text' => 'Tôi ưu tiên hoàn thiện chính xác từng chi tiết của hiện tại trước khi nghĩ quá xa về tương lai.'],
            ['id' => 'SN15', 'dimension' => 'SN', 'direction' => 1, 'tier' => 'standard', 'context' => 'nhan_thuc', 'text' => 'Khi đọc sách hoặc xem phim, tôi hay suy ngẫm về thông điệp ngầm đằng sau câu chuyện.'],
            ['id' => 'SN16', 'dimension' => 'SN', 'direction' => -1, 'tier' => 'standard', 'context' => 'nhan_thuc', 'text' => 'Tôi thích những câu chuyện bám sát đời sống thực tế và diễn biến mạch lạc dễ hình dung.'],

            ['id' => 'SN17', 'dimension' => 'SN', 'direction' => 1, 'tier' => 'deep', 'context' => 'cuoc_song', 'text' => 'Tâm trí tôi thường hướng về những điều có thể xảy ra sắp tới hơn là những gì đang diễn ra trước mắt.'],
            ['id' => 'SN18', 'dimension' => 'SN', 'direction' => -1, 'tier' => 'deep', 'context' => 'cuoc_song', 'text' => 'Tôi cảm thấy an tâm nhất khi tập trung giải quyết những việc thiết thực ngay trong hiện tại.'],
            ['id' => 'SN19', 'dimension' => 'SN', 'direction' => 1, 'tier' => 'deep', 'context' => 'cong_viec', 'text' => 'Các đầu việc lặp đi lặp lại theo đúng một khuôn mẫu cố định thường khiến tôi nhanh giảm hứng thú.'],
            ['id' => 'SN20', 'dimension' => 'SN', 'direction' => -1, 'tier' => 'deep', 'context' => 'cong_viec', 'text' => 'Tôi đánh giá cao hướng dẫn có danh sách kiểm tra (checklist) rõ ràng để không bỏ sót chi tiết.'],
            ['id' => 'SN21', 'dimension' => 'SN', 'direction' => 1, 'tier' => 'deep', 'context' => 'giao_tiep', 'text' => 'Tôi thích các cuộc trò chuyện về xu hướng tương lai, triết lý sống hoặc những giả định mới mẻ.'],
            ['id' => 'SN22', 'dimension' => 'SN', 'direction' => -1, 'tier' => 'deep', 'context' => 'giao_tiep', 'text' => 'Tôi thích chia sẻ và lắng nghe những kinh nghiệm thực tế có thể áp dụng ngay vào đời sống.'],
            ['id' => 'SN23', 'dimension' => 'SN', 'direction' => 1, 'tier' => 'deep', 'context' => 'nhan_thuc', 'text' => 'Đôi khi tôi bỏ qua vài tiểu tiết nhỏ vì đang tập trung vào ý tưởng lớn hơn.'],
            ['id' => 'SN24', 'dimension' => 'SN', 'direction' => -1, 'tier' => 'deep', 'context' => 'nhan_thuc', 'text' => 'Tôi thường phát hiện nhanh những lỗi sai nhỏ về số liệu, ngày tháng hoặc chính tả trong tài liệu.'],

            // ==================== TF (24 câu: +1 = T, -1 = F) ====================
            ['id' => 'TF01', 'dimension' => 'TF', 'direction' => 1, 'tier' => 'quick', 'context' => 'quyet_dinh', 'text' => 'Khi cân nhắc các lựa chọn quan trọng, tôi thường so sánh chúng dựa trên những tiêu chí nhất quán và khách quan.'],
            ['id' => 'TF02', 'dimension' => 'TF', 'direction' => -1, 'tier' => 'quick', 'context' => 'quyet_dinh', 'text' => 'Khi đưa ra quyết định ảnh hưởng đến người khác, tôi đặc biệt chú ý đến hoàn cảnh và cảm nhận của họ.'],
            ['id' => 'TF03', 'dimension' => 'TF', 'direction' => 1, 'tier' => 'quick', 'context' => 'giao_tiep', 'text' => 'Tôi ưu tiên nói thẳng điểm chưa hợp lý trong một phương án để cả nhóm sửa sớm, dù có thể hơi khô khan.'],
            ['id' => 'TF04', 'dimension' => 'TF', 'direction' => -1, 'tier' => 'quick', 'context' => 'giao_tiep', 'text' => 'Khi góp ý cho đồng nghiệp hoặc người thân, tôi luôn cân nhắc cách nói dịu dàng để giữ sự tôn trọng và hòa khí.'],
            ['id' => 'TF05', 'dimension' => 'TF', 'direction' => 1, 'tier' => 'quick', 'context' => 'bat_dong', 'text' => 'Trong một cuộc tranh luận, việc tìm ra lập luận đúng logic quan trọng với tôi hơn việc giữ cho mọi người đều hài lòng.'],
            ['id' => 'TF06', 'dimension' => 'TF', 'direction' => -1, 'tier' => 'quick', 'context' => 'bat_dong', 'text' => 'Tôi thường tìm cách dung hòa để các bên đều cảm thấy được lắng nghe khi xảy ra bất đồng.'],
            ['id' => 'TF07', 'dimension' => 'TF', 'direction' => 1, 'tier' => 'quick', 'context' => 'cong_viec', 'text' => 'Tôi cảm thấy thuyết phục hơn bởi bằng chứng rõ ràng và phân tích thiệt hơn so với lời kêu gọi cảm xúc.'],
            ['id' => 'TF08', 'dimension' => 'TF', 'direction' => -1, 'tier' => 'quick', 'context' => 'tinh_cam', 'text' => 'Khi bạn thân kể chuyện buồn, phản xạ đầu tiên của tôi là đồng cảm và ở bên họ trước khi bàn giải pháp.'],

            ['id' => 'TF09', 'dimension' => 'TF', 'direction' => 1, 'tier' => 'standard', 'context' => 'quyet_dinh', 'text' => 'Tôi thích tách cảm xúc cá nhân sang một bên để đánh giá công bằng ưu và nhược điểm của từng phương án.'],
            ['id' => 'TF10', 'dimension' => 'TF', 'direction' => -1, 'tier' => 'standard', 'context' => 'quyet_dinh', 'text' => 'Một lựa chọn dù hợp lý trên giấy tờ nhưng đi ngược lại giá trị sống của tôi sẽ khiến tôi rất khó chấp nhận.'],
            ['id' => 'TF11', 'dimension' => 'TF', 'direction' => 1, 'tier' => 'standard', 'context' => 'cong_viec', 'text' => 'Khi đánh giá kết quả công việc, tôi ưu tiên sự công bằng theo đúng tiêu chuẩn chung cho tất cả mọi người.'],
            ['id' => 'TF12', 'dimension' => 'TF', 'direction' => -1, 'tier' => 'standard', 'context' => 'cong_viec', 'text' => 'Khi đánh giá một tình huống, tôi thường xem xét cả nỗ lực và hoàn cảnh riêng của từng người liên quan.'],
            ['id' => 'TF13', 'dimension' => 'TF', 'direction' => 1, 'tier' => 'standard', 'context' => 'bat_dong', 'text' => 'Khi ai đó chia sẻ khó khăn, tôi có xu hướng nhanh chóng phân tích nguyên nhân để giúp họ tháo gỡ.'],
            ['id' => 'TF14', 'dimension' => 'TF', 'direction' => -1, 'tier' => 'standard', 'context' => 'bat_dong', 'text' => 'Bầu không khí căng thẳng kéo dài giữa những người thân quen thường ảnh hưởng mạnh đến tâm trạng của tôi.'],
            ['id' => 'TF15', 'dimension' => 'TF', 'direction' => 1, 'tier' => 'standard', 'context' => 'giao_tiep', 'text' => 'Tôi thấy dễ dàng hơn khi nhận xét công việc dựa trên hiệu quả thực tế thay vì sợ làm người khác buồn.'],
            ['id' => 'TF16', 'dimension' => 'TF', 'direction' => -1, 'tier' => 'standard', 'context' => 'giao_tiep', 'text' => 'Tôi dễ nhận ra sự thay đổi nhỏ trong giọng nói hoặc sắc mặt của người đối diện khi họ đang không vui.'],

            ['id' => 'TF17', 'dimension' => 'TF', 'direction' => 1, 'tier' => 'deep', 'context' => 'quyet_dinh', 'text' => 'Khi mua một món đồ giá trị lớn, tôi chủ yếu dựa vào bảng so sánh thông số, độ bền và chi phí.'],
            ['id' => 'TF18', 'dimension' => 'TF', 'direction' => -1, 'tier' => 'deep', 'context' => 'quyet_dinh', 'text' => 'Cảm giác gắn bó và ý nghĩa tinh thần của một lựa chọn thường có trọng lượng lớn trong quyết định của tôi.'],
            ['id' => 'TF19', 'dimension' => 'TF', 'direction' => 1, 'tier' => 'deep', 'context' => 'cong_viec', 'text' => 'Tôi thích được ghi nhận vì năng lực giải quyết vấn đề sắc bén hơn là vì sự dễ mến.'],
            ['id' => 'TF20', 'dimension' => 'TF', 'direction' => -1, 'tier' => 'deep', 'context' => 'cong_viec', 'text' => 'Tôi có thêm động lực rất lớn khi biết công việc của mình thực sự giúp người khác cảm thấy tốt hơn.'],
            ['id' => 'TF21', 'dimension' => 'TF', 'direction' => 1, 'tier' => 'deep', 'context' => 'bat_dong', 'text' => 'Tôi có thể tranh luận gay gắt về một quan điểm rồi vẫn giữ quan hệ bình thường ngay sau đó.'],
            ['id' => 'TF22', 'dimension' => 'TF', 'direction' => -1, 'tier' => 'deep', 'context' => 'bat_dong', 'text' => 'Những lời nói quá sắc bén trong lúc tranh luận thường khiến tôi suy nghĩ rất lâu sau khi cuộc nói chuyện kết thúc.'],
            ['id' => 'TF23', 'dimension' => 'TF', 'direction' => 1, 'tier' => 'deep', 'context' => 'tinh_cam', 'text' => 'Trong mối quan hệ, tôi đề cao sự minh bạch, thẳng thắn và cùng nhau giải quyết gốc rễ vấn đề.'],
            ['id' => 'TF24', 'dimension' => 'TF', 'direction' => -1, 'tier' => 'deep', 'context' => 'tinh_cam', 'text' => 'Trong mối quan hệ, sự thấu cảm ấm áp và cảm giác được trân trọng là điều tôi đặt lên hàng đầu.'],

            // ==================== JP (24 câu: +1 = J, -1 = P) ====================
            ['id' => 'JP01', 'dimension' => 'JP', 'direction' => 1, 'tier' => 'quick', 'context' => 'ke_hoach', 'text' => 'Tôi thích xác định rõ những việc cần làm trước khi bắt đầu một ngày bận rộn.'],
            ['id' => 'JP02', 'dimension' => 'JP', 'direction' => -1, 'tier' => 'quick', 'context' => 'ke_hoach', 'text' => 'Tôi cảm thấy thoải mái khi thay đổi kế hoạch để tận dụng một cơ hội hoặc cảm hứng bất ngờ.'],
            ['id' => 'JP03', 'dimension' => 'JP', 'direction' => 1, 'tier' => 'quick', 'context' => 'cong_viec', 'text' => 'Tôi cảm thấy nhẹ nhõm nhất khi hoàn thành xong công việc trước hạn thay vì để sát ngày mới làm.'],
            ['id' => 'JP04', 'dimension' => 'JP', 'direction' => -1, 'tier' => 'quick', 'context' => 'cong_viec', 'text' => 'Tôi thường làm việc rất tập trung và sáng tạo khi gần đến hạn chót hoặc khi có sự linh hoạt về giờ giấc.'],
            ['id' => 'JP05', 'dimension' => 'JP', 'direction' => 1, 'tier' => 'quick', 'context' => 'quyet_dinh', 'text' => 'Khi đã đủ thông tin cần thiết, tôi thích chốt quyết định sớm để bắt tay vào triển khai.'],
            ['id' => 'JP06', 'dimension' => 'JP', 'direction' => -1, 'tier' => 'quick', 'context' => 'quyet_dinh', 'text' => 'Tôi thích giữ các phương án mở càng lâu càng tốt phòng khi xuất hiện lựa chọn phù hợp hơn.'],
            ['id' => 'JP07', 'dimension' => 'JP', 'direction' => 1, 'tier' => 'quick', 'context' => 'cuoc_song', 'text' => 'Khi chuẩn bị một chuyến đi chơi, tôi thích đặt sẵn lịch trình, chỗ ở và giờ di chuyển rõ ràng.'],
            ['id' => 'JP08', 'dimension' => 'JP', 'direction' => -1, 'tier' => 'quick', 'context' => 'cuoc_song', 'text' => 'Khi đi du lịch, tôi thích đến nơi rồi tùy tâm trạng và thời tiết mà khám phá tự do.'],

            ['id' => 'JP09', 'dimension' => 'JP', 'direction' => 1, 'tier' => 'standard', 'context' => 'ke_hoach', 'text' => 'Những đầu việc còn dang dở chưa chốt rõ thường khiến tôi bận tâm cho đến khi giải quyết xong.'],
            ['id' => 'JP10', 'dimension' => 'JP', 'direction' => -1, 'tier' => 'standard', 'context' => 'ke_hoach', 'text' => 'Một lịch trình chi tiết từng giờ đôi khi khiến tôi cảm thấy gò bó và mất tự nhiên.'],
            ['id' => 'JP11', 'dimension' => 'JP', 'direction' => 1, 'tier' => 'standard', 'context' => 'cong_viec', 'text' => 'Tôi thường chia dự án lớn thành từng mốc nhỏ có thời hạn cụ thể để dễ kiểm soát tiến độ.'],
            ['id' => 'JP12', 'dimension' => 'JP', 'direction' => -1, 'tier' => 'standard', 'context' => 'cong_viec', 'text' => 'Tôi thích xoay chuyển linh hoạt giữa nhiều đầu việc tùy theo mức độ hứng khởi ở từng thời điểm.'],
            ['id' => 'JP13', 'dimension' => 'JP', 'direction' => 1, 'tier' => 'standard', 'context' => 'cuoc_song', 'text' => 'Tôi ưu tiên làm xong trách nhiệm cần thiết rồi mới yên tâm nghỉ ngơi hoặc giải trí.'],
            ['id' => 'JP14', 'dimension' => 'JP', 'direction' => -1, 'tier' => 'standard', 'context' => 'cuoc_song', 'text' => 'Tôi thường đan xen giữa làm việc và thư giãn để giữ tâm trạng thoải mái trong suốt ngày dài.'],
            ['id' => 'JP15', 'dimension' => 'JP', 'direction' => 1, 'tier' => 'standard', 'context' => 'xa_hoi', 'text' => 'Khi hẹn bạn bè, tôi thích thống nhất trước giờ giấc và địa điểm cụ thể từ sớm.'],
            ['id' => 'JP16', 'dimension' => 'JP', 'direction' => -1, 'tier' => 'standard', 'context' => 'xa_hoi', 'text' => 'Tôi thấy hoàn toàn bình thường với những lời rủ ngẫu hứng sát giờ mà không cần hẹn trước nhiều ngày.'],

            ['id' => 'JP17', 'dimension' => 'JP', 'direction' => 1, 'tier' => 'deep', 'context' => 'ke_hoach', 'text' => 'Tôi thích sắp xếp tài liệu, đồ dùng hoặc ghi chú theo một hệ thống nhất quán để tìm là thấy ngay.'],
            ['id' => 'JP18', 'dimension' => 'JP', 'direction' => -1, 'tier' => 'deep', 'context' => 'ke_hoach', 'text' => 'Tôi không quá đặt nặng việc mọi thứ phải gọn theo khuôn mẫu, miễn là mình vẫn nhớ vị trí và thấy thoải mái.'],
            ['id' => 'JP19', 'dimension' => 'JP', 'direction' => 1, 'tier' => 'deep', 'context' => 'cong_viec', 'text' => 'Tôi cảm thấy làm việc hiệu quả hơn khi quy trình và tiêu chí hoàn thành được xác định rõ từ đầu.'],
            ['id' => 'JP20', 'dimension' => 'JP', 'direction' => -1, 'tier' => 'deep', 'context' => 'cong_viec', 'text' => 'Tôi thích vừa làm vừa điều chỉnh hướng đi khi có thêm thông tin mới xuất hiện giữa chừng.'],
            ['id' => 'JP21', 'dimension' => 'JP', 'direction' => 1, 'tier' => 'deep', 'context' => 'cuoc_song', 'text' => 'Tôi thường duy trì những thói quen sinh hoạt đều đặn để giữ cuộc sống ổn định và chủ động.'],
            ['id' => 'JP22', 'dimension' => 'JP', 'direction' => -1, 'tier' => 'deep', 'context' => 'cuoc_song', 'text' => 'Tôi thích mỗi ngày có sự biến hóa mới mẻ hơn là lặp lại cùng một khung giờ cố định.'],
            ['id' => 'JP23', 'dimension' => 'JP', 'direction' => 1, 'tier' => 'deep', 'context' => 'quyet_dinh', 'text' => 'Khi tổ chức một sự kiện quan trọng, tôi muốn mọi hạng mục đều có phương án chốt trước ngày diễn ra.'],
            ['id' => 'JP24', 'dimension' => 'JP', 'direction' => -1, 'tier' => 'deep', 'context' => 'quyet_dinh', 'text' => 'Khi có tình huống phát sinh ngoài dự kiến, tôi dễ dàng ứng biến tại chỗ mà không quá lo lắng.'],
        ];
    }

    /**
     * 50 câu hỏi Big Five OCEAN theo cấu trúc IPIP 50-Item Big-Five Factor Markers (Public Domain — ipip.ori.org).
     * Bản Việt hóa thử nghiệm (Pilot v1.0), 10 câu/chiều, giữ đúng hướng chấm thuận (+1) / đảo (-1).
     * Lưu ý: chiều N tính theo hướng Neuroticism (nhạy cảm cảm xúc).
     *
     * @return list<array{id: string, dimension: string, direction: int, tier: string, context: string, text: string}>
     */
    public function bigFiveIpip50Questions(): array
    {
        return [
            // O — Openness / Intellect (7+, 3-)
            ['id' => 'BF_O01', 'dimension' => 'O', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi có vốn từ vựng và cách diễn đạt phong phú.'],
            ['id' => 'BF_O02', 'dimension' => 'O', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi gặp khó khăn khi tiếp cận những ý tưởng quá trừu tượng.'],
            ['id' => 'BF_O03', 'dimension' => 'O', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi có trí tưởng tượng sống động và nhiều hình ảnh.'],
            ['id' => 'BF_O04', 'dimension' => 'O', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi không mấy hứng thú với các khái niệm mang tính trừu tượng.'],
            ['id' => 'BF_O05', 'dimension' => 'O', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi thường nảy ra nhiều ý tưởng mới mẻ và thú vị.'],
            ['id' => 'BF_O06', 'dimension' => 'O', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi ít khi mơ mộng hay tưởng tượng những kịch bản giả định.'],
            ['id' => 'BF_O07', 'dimension' => 'O', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi nắm bắt nhanh các khái niệm và góc nhìn mới.'],
            ['id' => 'BF_O08', 'dimension' => 'O', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi thích tìm hiểu nghệ thuật, văn hóa và những cách nhìn đa chiều.'],
            ['id' => 'BF_O09', 'dimension' => 'O', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi thường dành thời gian chiêm nghiệm sâu về nhiều vấn đề trong cuộc sống.'],
            ['id' => 'BF_O10', 'dimension' => 'O', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi luôn tràn đầy ý tưởng khi bắt đầu một chủ đề mới.'],

            // C — Conscientiousness (6+, 4-)
            ['id' => 'BF_C01', 'dimension' => 'C', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi luôn chuẩn bị kỹ lưỡng trước khi bắt đầu việc quan trọng.'],
            ['id' => 'BF_C02', 'dimension' => 'C', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi đôi khi để đồ đạc xung quanh hơi bừa bộn.'],
            ['id' => 'BF_C03', 'dimension' => 'C', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi chú ý kỹ đến các chi tiết nhỏ trong công việc.'],
            ['id' => 'BF_C04', 'dimension' => 'C', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi dễ làm xáo trộn trật tự khi đang vội.'],
            ['id' => 'BF_C05', 'dimension' => 'C', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi thường xử lý xong các việc cần làm ngay thay vì trì hoãn.'],
            ['id' => 'BF_C06', 'dimension' => 'C', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi hay quên đặt đồ vật trở lại đúng vị trí cũ.'],
            ['id' => 'BF_C07', 'dimension' => 'C', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi thích sự ngăn nắp và có trật tự rõ ràng.'],
            ['id' => 'BF_C08', 'dimension' => 'C', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi đôi khi né tránh những đầu việc hành chính tẻ nhạt.'],
            ['id' => 'BF_C09', 'dimension' => 'C', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi tuân thủ khá tốt lịch trình và kế hoạch đã đề ra.'],
            ['id' => 'BF_C10', 'dimension' => 'C', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi làm việc cẩn thận và có yêu cầu cao về độ chính xác.'],

            // E — Extraversion (5+, 5-)
            ['id' => 'BF_E01', 'dimension' => 'E', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi thường là người tạo không khí vui vẻ trong các buổi tụ họp.'],
            ['id' => 'BF_E02', 'dimension' => 'E', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi thường nói khá ít trong các cuộc gặp gỡ đông người.'],
            ['id' => 'BF_E03', 'dimension' => 'E', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi cảm thấy rất thoải mái khi ở giữa nhiều người.'],
            ['id' => 'BF_E04', 'dimension' => 'E', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi thích đứng ở phía sau quan sát hơn là ở vị trí nổi bật.'],
            ['id' => 'BF_E05', 'dimension' => 'E', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi thường chủ động mở đầu cuộc trò chuyện.'],
            ['id' => 'BF_E06', 'dimension' => 'E', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi ít khi có nhiều chuyện xã giao để nói với người mới quen.'],
            ['id' => 'BF_E07', 'dimension' => 'E', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tại các buổi tiệc, tôi thường trò chuyện với nhiều người khác nhau.'],
            ['id' => 'BF_E08', 'dimension' => 'E', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi không thích thu hút quá nhiều sự chú ý về phía mình.'],
            ['id' => 'BF_E09', 'dimension' => 'E', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi không ngại trở thành tâm điểm của cuộc trò chuyện.'],
            ['id' => 'BF_E10', 'dimension' => 'E', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi khá trầm lặng khi tiếp xúc với người lạ.'],

            // A — Agreeableness (6+, 4-)
            ['id' => 'BF_A01', 'dimension' => 'A', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi không quá để tâm đến cảm nhận của người không thân.'],
            ['id' => 'BF_A02', 'dimension' => 'A', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi thực sự quan tâm đến câu chuyện và đời sống của mọi người.'],
            ['id' => 'BF_A03', 'dimension' => 'A', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Đôi khi tôi phản ứng khá gay gắt khi bất đồng quan điểm.'],
            ['id' => 'BF_A04', 'dimension' => 'A', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi dễ đồng cảm với cảm xúc vui buồn của người khác.'],
            ['id' => 'BF_A05', 'dimension' => 'A', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi ít khi bận tâm đến rắc rối cá nhân của người ngoài.'],
            ['id' => 'BF_A06', 'dimension' => 'A', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi có tấm lòng mềm mại và dễ xúc động trước sự chân thành.'],
            ['id' => 'BF_A07', 'dimension' => 'A', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi không thực sự thích dành nhiều thời gian xã giao với người lạ.'],
            ['id' => 'BF_A08', 'dimension' => 'A', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi sẵn lòng dành thời gian lắng nghe và hỗ trợ người khác.'],
            ['id' => 'BF_A09', 'dimension' => 'A', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi dễ cảm nhận được tâm trạng của những người xung quanh.'],
            ['id' => 'BF_A10', 'dimension' => 'A', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi cố gắng làm cho người ở cạnh mình cảm thấy thoải mái và yên tâm.'],

            // N — Neuroticism / Nhạy cảm cảm xúc (8+, 2- theo chiều Neuroticism; Emotional Stability = 100 - N)
            ['id' => 'BF_N01', 'dimension' => 'N', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi dễ cảm thấy căng thẳng khi nhiều việc dồn đến cùng lúc.'],
            ['id' => 'BF_N02', 'dimension' => 'N', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi thường giữ được trạng thái thư thái phần lớn thời gian.'],
            ['id' => 'BF_N03', 'dimension' => 'N', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi hay lo lắng trước những tình huống chưa rõ kết quả.'],
            ['id' => 'BF_N04', 'dimension' => 'N', 'direction' => -1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi hiếm khi cảm thấy xuống tinh thần trong thời gian dài.'],
            ['id' => 'BF_N05', 'dimension' => 'N', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi dễ bị xao động khi kế hoạch bị ảnh hưởng bất ngờ.'],
            ['id' => 'BF_N06', 'dimension' => 'N', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi dễ cảm thấy bồn chồn khi gặp chuyện không như ý.'],
            ['id' => 'BF_N07', 'dimension' => 'N', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tâm trạng của tôi đôi khi thay đổi khá nhanh theo hoàn cảnh.'],
            ['id' => 'BF_N08', 'dimension' => 'N', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi có những lúc cảm xúc lên xuống khá rõ trong ngày.'],
            ['id' => 'BF_N09', 'dimension' => 'N', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi dễ cảm thấy khó chịu khi mọi thứ diễn ra quá lộn xộn.'],
            ['id' => 'BF_N10', 'dimension' => 'N', 'direction' => 1, 'tier' => 'ipip50', 'context' => 'ipip', 'text' => 'Tôi đôi khi cảm thấy chùng xuống khi chịu nhiều áp lực.'],
        ];
    }
}
