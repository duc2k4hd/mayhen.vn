<?php

namespace App\Services;

class AiStoryWriterService
{
    /**
     * Sinh các phương án gợi ý Lời ngỏ & Câu chuyện tình yêu Quiet Luxury.
     *
     * @param  string  $groom  Tên chú rể
     * @param  string  $bride  Tên cô dâu
     * @param  string|null  $keywords  Từ khóa kỷ niệm (ví dụ: "Đà Lạt, yêu xa 3 năm, quán cafe")
     * @param  string  $tone  Tông giọng: 'romantic', 'minimal', 'cheerful'
     * @param  string|null  $howWeMet  Câu chuyện gặp gỡ hoặc thời gian bên nhau
     * @return array Danh sách 3 phương án gợi ý
     */
    public function generate(string $groom, string $bride, ?string $keywords = null, string $tone = 'romantic', ?string $howWeMet = null): array
    {
        $cleanGroom = htmlspecialchars(strip_tags(trim($groom)), ENT_QUOTES, 'UTF-8') ?: 'Chú Rể';
        $cleanBride = htmlspecialchars(strip_tags(trim($bride)), ENT_QUOTES, 'UTF-8') ?: 'Cô Dâu';
        $cleanKeywords = $keywords ? htmlspecialchars(strip_tags(trim($keywords)), ENT_QUOTES, 'UTF-8') : '';
        $cleanHowWeMet = $howWeMet ? htmlspecialchars(strip_tags(trim($howWeMet)), ENT_QUOTES, 'UTF-8') : '';

        // Tách từ khóa thành các mẩu kỷ niệm nếu có
        $keywordList = array_values(array_filter(array_map('trim', explode(',', $cleanKeywords))));

        $options = [
            $this->buildOption(1, 'romantic', $cleanGroom, $cleanBride, $keywordList, $cleanHowWeMet),
            $this->buildOption(2, 'minimal', $cleanGroom, $cleanBride, $keywordList, $cleanHowWeMet),
            $this->buildOption(3, 'cheerful', $cleanGroom, $cleanBride, $keywordList, $cleanHowWeMet),
        ];

        // Nếu người dùng chọn đích danh 1 phong cách, đưa phong cách đó lên đầu
        if ($tone === 'minimal') {
            usort($options, fn ($a, $b) => $a['style_code'] === 'minimal' ? -1 : 1);
        } elseif ($tone === 'cheerful') {
            usort($options, fn ($a, $b) => $a['style_code'] === 'cheerful' ? -1 : 1);
        }

        return [
            'success' => true,
            'couple' => "{$cleanGroom} & {$cleanBride}",
            'options' => $options,
        ];
    }

    /**
     * Dựng phương án lời ngỏ theo phong cách và kỷ niệm.
     */
    protected function buildOption(int $id, string $style, string $groom, string $bride, array $keywords, string $howWeMet = ''): array
    {
        $hasKeywords = count($keywords) > 0;
        $memoryText = $hasKeywords ? implode(' • ', $keywords) : '';

        if ($style === 'minimal') {
            $title = 'Lời Ngỏ';
            $subtitle = 'Hành Trình Bên Nhau';

            if (! empty($howWeMet)) {
                $memoryClause = $hasKeywords
                    ? "Bắt đầu từ cơ duyên \"{$howWeMet}\" và đong đầy qua những kỷ niệm ({$memoryText}), chúng mình nhận ra đối phương chính là bến đỗ bình yên nhất."
                    : "Khởi đầu từ cơ duyên \"{$howWeMet}\", chúng mình may mắn tìm thấy nhau giữa cuộc đời và cùng sẻ chia từng khoảnh khắc giản dị.";
            } else {
                $memoryClause = $hasKeywords
                    ? "Từ những kỷ niệm đong đầy ({$memoryText}), chúng mình nhận ra đối phương chính là bến đỗ bình yên nhất."
                    : 'Giữa thế giới bao la rộng lớn, chúng mình may mắn tìm thấy nhau và cùng sẻ chia từng khoảnh khắc giản dị.';
            }

            $content = "Hạnh phúc không nằm ở những điều lớn lao, mà đong đầy trong từng cái nắm tay và sự thấu hiểu mỗi ngày. {$memoryClause} Cảm ơn bạn vì đã luôn đồng hành, và hôm nay cùng chúng mình bước sang chương mới trọn vẹn nhất.";

            return [
                'id' => $id,
                'style_code' => 'minimal',
                'style_name' => 'Tối Giản & Thanh Lịch',
                'tag' => 'Quiet Luxury',
                'title' => $title,
                'subtitle' => $subtitle,
                'content' => $content,
            ];
        }

        if ($style === 'cheerful') {
            $title = 'Chuyện Chúng Mình';
            $subtitle = 'Ngày Tình Yêu Đơm Hoa';

            if (! empty($howWeMet)) {
                $memoryClause = $hasKeywords
                    ? "Từ những ngày \"{$howWeMet}\" cho đến những kỷ niệm khó quên như \"{$memoryText}\", tụi mình đã cùng nhau đi qua biết bao tiếng cười lẫn những lúc cùng vượt khó."
                    : "Từ ngày định mệnh khi \"{$howWeMet}\", tụi mình nhận ra rằng cuộc sống này sẽ vui hơn gấp bội khi có người kia ở cạnh!";
            } else {
                $memoryClause = $hasKeywords
                    ? "Bắt đầu từ những kỷ niệm đáng nhớ như \"{$memoryText}\", tụi mình đã cùng nhau đi qua biết bao tiếng cười lẫn những lúc cùng vượt khó."
                    : 'Đi cùng nhau qua những năm tháng tuổi trẻ rực rỡ, tụi mình nhận ra rằng cuộc sống này sẽ vui hơn gấp bội khi có người kia ở cạnh!';
            }

            $content = "Thế là sau bao ngày hẹn ước, {$groom} & {$bride} chính thức về chung một nhà! {$memoryClause} Rất mong được đón tiếp bạn trong ngày vui để cùng tụi mình nâng ly và quẩy hết mình nhé!";

            return [
                'id' => $id,
                'style_code' => 'cheerful',
                'style_name' => 'Trẻ Trung & Hóm Hỉnh',
                'tag' => 'Tươi Vui & Ấm Áp',
                'title' => $title,
                'subtitle' => $subtitle,
                'content' => $content,
            ];
        }

        // Mặc định: romantic (Lãng Mạn & Sâu Lắng)
        $title = 'Chuyện Tình Yêu';
        $subtitle = 'Hành Trình Gặp Gỡ & Đồng Điệu';

        if (! empty($howWeMet)) {
            $memoryClause = $hasKeywords
                ? "Bắt đầu từ khoảnh khắc diệu kỳ \"{$howWeMet}\", từng dấu mốc bên nhau ({$memoryText}) đều là những mảnh ghép định mệnh đưa hai tâm hồn hòa vào làm một."
                : "Khởi đầu từ định mệnh \"{$howWeMet}\", hai trái tim đã rung động và cùng nhau đi tìm bến đỗ hạnh phúc.";
        } else {
            $memoryClause = $hasKeywords
                ? "Từng dấu mốc bên nhau ({$memoryText}) đều là những mảnh ghép định mệnh đưa hai tâm hồn hòa vào làm một."
                : 'Vũ trụ bao la với hàng triệu vì tinh tú, nhưng định mệnh đã dẫn lối để hai tâm hồn đồng điệu tìm thấy nhau.';
        }

        $content = "{$memoryClause} Cảm ơn vì đã cùng nhau đi qua những năm tháng thanh xuân, cùng sẻ chia những ước mơ và bao dung cho nhau từng khiếm khuyết. Ngày hôm nay, {$groom} và {$bride} nguyện cùng nắm tay viết tiếp câu chuyện tình yêu đến mãi mãi về sau.";

        return [
            'id' => $id,
            'style_code' => 'romantic',
            'style_name' => 'Lãng Mạn & Sâu Lắng',
            'tag' => 'Trữ Tình & Sâu Sắc',
            'title' => $title,
            'subtitle' => $subtitle,
            'content' => $content,
        ];
    }
}
