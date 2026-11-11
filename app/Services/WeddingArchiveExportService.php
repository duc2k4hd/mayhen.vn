<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Wedding;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class WeddingArchiveExportService
{
    /**
     * Tạo tệp nén ZIP chứa trọn bộ dữ liệu kỷ niệm ngày cưới.
     *
     * @return array{
     *     zip_path: string,
     *     filename: string,
     *     files_count: int,
     *     size_bytes: int
     * }
     */
    public function createArchive(Invitation $invitation): array
    {
        $wedding = $invitation->wedding;
        if (! $wedding) {
            throw new \RuntimeException('Không tìm thấy thông tin đám cưới tương ứng với thiệp này.');
        }

        $tempDir = storage_path('app/temp-archives');
        if (! File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        $groomSlug = Str::slug($wedding->groom_name ?: 'chu-re');
        $brideSlug = Str::slug($wedding->bride_name ?: 'co-dau');
        $dateStr = $wedding->wedding_date ? $wedding->wedding_date->format('Y-m-d') : date('Y-m-d');
        $filename = "Ky-Niem-Cuoi-{$groomSlug}-va-{$brideSlug}-{$dateStr}.zip";
        $zipPath = $tempDir.DIRECTORY_SEPARATOR.uniqid('archive_', true).'.zip';

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Không thể khởi tạo tệp nén ZIP trên máy chủ.');
        }

        $filesCount = 0;

        // 1. Tệp README.txt chào mừng
        $readmeContent = $this->generateReadme($wedding, $invitation);
        $zip->addFromString('README.txt', $readmeContent);
        $filesCount++;

        // 2. Tệp Sổ Lưu Bút & Lời Chúc Mừng (so-luu-but-loi-chuc.csv)
        $wishesCsv = $this->generateWishesCsv($wedding);
        $zip->addFromString('so-luu-but-loi-chuc.csv', $wishesCsv);
        $filesCount++;

        // 3. Tệp Danh Sách Khách Mời & Phản Hồi RSVP (danh-sach-khach-moi-rsvp.csv)
        $guestsCsv = $this->generateGuestsCsv($wedding);
        $zip->addFromString('danh-sach-khach-moi-rsvp.csv', $guestsCsv);
        $filesCount++;

        // 4. Tệp Dữ Liệu Chi Tiết Hôn Lễ (thong-tin-ngay-cuoi.json)
        $weddingJson = $this->generateWeddingJson($wedding, $invitation);
        $zip->addFromString('thong-tin-ngay-cuoi.json', $weddingJson);
        $filesCount++;

        // 5. Tệp Sổ Lưu Niệm Tĩnh Mở Offline (so-luu-niem-offline.html)
        $offlineHtml = $this->generateOfflineHtml($wedding, $invitation);
        $zip->addFromString('so-luu-niem-offline.html', $offlineHtml);
        $filesCount++;

        // 6. Đóng gói ảnh kỷ niệm từ Album cưới (nếu có trên storage nội bộ)
        $filesCount += $this->packAlbumPhotos($zip, $invitation);

        $zip->close();

        return [
            'zip_path' => $zipPath,
            'filename' => $filename,
            'files_count' => $filesCount,
            'size_bytes' => File::size($zipPath),
        ];
    }

    /**
     * Sinh nội dung tệp README.txt hướng dẫn và lời chúc phúc.
     */
    protected function generateReadme(Wedding $wedding, Invitation $invitation): string
    {
        $exportDate = date('d/m/Y H:i');
        $couple = "{$wedding->groom_name} & {$wedding->bride_name}";
        $weddingDate = $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : 'Chưa cập nhật';

        return <<<TXT
========================================================================
            MÂY HẸN ATELIER — KHO LƯU TRỮ KỶ NIỆM NGÀY CHUNG ĐÔI
========================================================================

Đôi Uyên Ương: {$couple}
Ngày Hôn Lễ: {$weddingDate}
Đường Dẫn Thiệp: {$invitation->slug}
Thời Điểm Đóng Gói Kỷ Niệm: {$exportDate}

Thân gửi Cô Dâu & Chú Rể,

Mây Hẹn vô cùng vinh hạnh được đồng hành cùng hai bạn trong những khoảnh khắc
thiêng liêng và đáng nhớ nhất của cuộc đời.

Gói tệp nén ZIP này là kho báu kỷ niệm số chứa đựng trọn vẹn những lời chúc
phúc ấm áp, danh sách người thân bè bạn đã đến chung vui, cùng toàn bộ dữ liệu
hôn lễ của bạn:

1. `so-luu-niem-offline.html`: Trang kỷ niệm độc lập tĩnh. Bạn có thể mở trực
   tiếp bằng bất kỳ trình duyệt nào (Chrome, Safari, Edge...) trên máy tính hoặc
   điện thoại mà không cần kết nối mạng Internet hay máy chủ.
2. `so-luu-but-loi-chuc.csv`: Toàn bộ lời chúc chân thành từ khách mời và phản
   hồi của hai bạn, định dạng UTF-8 chuẩn mở trực tiếp trên Microsoft Excel.
3. `danh-sach-khach-moi-rsvp.csv`: Danh sách quan khách, tình trạng xác nhận tham
   dự, nhóm khách, bàn tiệc và mừng cưới.
4. `thong-tin-ngay-cuoi.json`: Toàn bộ cấu trúc sự kiện, lịch trình và câu
   chuyện tình yêu phục vụ lưu trữ số lâu dài.
5. Thư mục `anh-album/`: Các bức ảnh cưới kỷ niệm bạn đã tải lên hệ thống.

Chúc hai bạn một hành trình hôn nhân ngập tràn yêu thương, thấu hiểu và hạnh phúc
bất tận!

Trân trọng,
Đội ngũ Mây Hẹn Atelier (https://mayhen.vn)
TXT;
    }

    /**
     * Sinh CSV Sổ Lưu Bút kèm mã BOM UTF-8 hỗ trợ Excel hiển thị tiếng Việt mượt mà.
     */
    protected function generateWishesCsv(Wedding $wedding): string
    {
        $output = "\xEF\xBB\xBF"; // UTF-8 BOM
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['STT', 'Họ Và Tên Khách Mời', 'Thời Gian Gửi', 'Trạng Thái Duyệt', 'Lời Chúc Phúc', 'Phản Hồi Từ Cô Dâu & Chú Rể']);

        $wishes = $wedding->wishes()->oldest('id')->get();
        $index = 1;

        foreach ($wishes as $wish) {
            fputcsv($handle, [
                $index++,
                $wish->name ?: 'Khách ẩn danh',
                $wish->created_at ? $wish->created_at->format('d/m/Y H:i') : '',
                $wish->is_approved ? 'Đã duyệt' : 'Chờ duyệt',
                $wish->message ?: '',
                $wish->reply ?: '',
            ]);
        }

        rewind($handle);
        $output .= stream_get_contents($handle);
        fclose($handle);

        return $output;
    }

    /**
     * Sinh CSV Danh Sách Khách Mời & Phản Hồi RSVP.
     */
    protected function generateGuestsCsv(Wedding $wedding): string
    {
        $output = "\xEF\xBB\xBF"; // UTF-8 BOM
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['STT', 'Họ Và Tên', 'Số Điện Thoại', 'Nhóm Khách', 'Xác Nhận Tham Dự', 'Số Người Đi Cùng', 'Ghi Chú / Bàn Tiệc', 'Mừng Cưới (VND)']);

        $guests = $wedding->guests()->with(['response', 'gifts'])->oldest('id')->get();
        $index = 1;

        foreach ($guests as $guest) {
            $response = $guest->response;
            $statusLabel = match ($response?->attending_status) {
                'attending', 'confirmed' => 'Tham dự',
                'declined' => 'Không tham dự',
                'tentative' => 'Chưa chắc chắn',
                default => 'Chưa phản hồi',
            };

            $attendeesCount = $response ? ($response->adults_count + $response->children_count) : ($guest->expected_attendees ?? 1);
            $totalGift = $guest->gifts->sum('amount');

            fputcsv($handle, [
                $index++,
                $guest->name ?: 'Khách mời',
                $guest->phone ?: '',
                $guest->group_name ?: 'Chung',
                $statusLabel,
                $attendeesCount,
                $guest->table_name ?: '',
                $totalGift > 0 ? number_format($totalGift, 0, ',', '.').' đ' : '',
            ]);
        }

        rewind($handle);
        $output .= stream_get_contents($handle);
        fclose($handle);

        return $output;
    }

    /**
     * Sinh JSON cấu trúc thông tin đám cưới.
     */
    protected function generateWeddingJson(Wedding $wedding, Invitation $invitation): string
    {
        $data = [
            'app' => 'May Hen Atelier',
            'version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'wedding' => [
                'id' => $wedding->id,
                'groom_name' => $wedding->groom_name,
                'bride_name' => $wedding->bride_name,
                'wedding_date' => $wedding->wedding_date?->format('Y-m-d'),
                'love_story' => $wedding->love_story,
                'custom_config' => $wedding->custom_config,
            ],
            'invitation' => [
                'id' => $invitation->id,
                'title' => $invitation->title,
                'slug' => $invitation->slug,
                'created_at' => $invitation->created_at?->toIso8601String(),
                'expires_at' => $invitation->expires_at?->toIso8601String(),
                'content_payload' => $invitation->content_payload,
            ],
            'events' => $wedding->events()->orderBy('event_time')->get()->map(fn ($event) => [
                'name' => $event->name,
                'event_time' => $event->event_time?->format('Y-m-d H:i'),
                'location_name' => $event->location_name,
                'location_address' => $event->location_address,
            ])->all(),
            'stats' => [
                'total_wishes' => $wedding->wishes()->count(),
                'total_guests' => $wedding->guests()->count(),
                'attending_guests' => $wedding->guestResponses()->whereIn('attending_status', ['attending', 'confirmed'])->count(),
            ],
        ];

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Sinh tệp HTML độc lập tĩnh (Offline Standalone Memorial Book).
     */
    protected function generateOfflineHtml(Wedding $wedding, Invitation $invitation): string
    {
        $groom = htmlspecialchars($wedding->groom_name ?: 'Chú Rể', ENT_QUOTES, 'UTF-8');
        $bride = htmlspecialchars($wedding->bride_name ?: 'Cô Dâu', ENT_QUOTES, 'UTF-8');
        $weddingDate = $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : 'Ngày Chung Đôi';
        $wishes = $wedding->wishes()->where('is_approved', true)->latest('id')->get();
        $totalWishes = $wishes->count();

        $wishesHtml = '';
        if ($totalWishes === 0) {
            $wishesHtml = '<div class="mayhen_empty_box">Chưa có lời chúc nào được lưu trong sổ.</div>';
        } else {
            foreach ($wishes as $wish) {
                $name = htmlspecialchars($wish->name ?: 'Khách quý', ENT_QUOTES, 'UTF-8');
                $message = nl2br(htmlspecialchars($wish->message ?: '', ENT_QUOTES, 'UTF-8'));
                $time = $wish->created_at ? $wish->created_at->format('d/m/Y H:i') : '';
                $replyHtml = '';

                if (! empty($wish->reply)) {
                    $replyText = nl2br(htmlspecialchars($wish->reply, ENT_QUOTES, 'UTF-8'));
                    $replyHtml = "
                    <div class=\"mayhen_wish_reply\">
                        <div class=\"mayhen_wish_reply_header\">♥ Lời cảm ơn từ {$groom} &amp; {$bride}</div>
                        <div class=\"mayhen_wish_reply_content\">{$replyText}</div>
                    </div>";
                }

                $wishesHtml .= "
                <article class=\"mayhen_wish_card\">
                    <div class=\"mayhen_wish_top\">
                        <div class=\"mayhen_wish_author\">{$name}</div>
                        <time class=\"mayhen_wish_time\">{$time}</time>
                    </div>
                    <div class=\"mayhen_wish_body\">{$message}</div>
                    {$replyHtml}
                </article>";
            }
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sổ Lưu Niệm Hôn Lễ — {$groom} &amp; {$bride}</title>
    <style>
        :root {
            --mayhen-cream: #FDFBF7;
            --mayhen-beige: #F4EFEB;
            --mayhen-card: #FFFFFF;
            --mayhen-charcoal: #2C2825;
            --mayhen-muted: #7A7269;
            --mayhen-gold: #C5A059;
            --mayhen-border: #E8E2D9;
            --mayhen-font: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            --mayhen-serif: "Playfair Display", Georgia, "Times New Roman", serif;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--mayhen-cream);
            color: var(--mayhen-charcoal);
            font-family: var(--mayhen-font);
            line-height: 1.6;
            padding: 40px 20px;
        }
        .mayhen_archive_wrap {
            max-width: 800px;
            margin: 0 auto;
        }
        .mayhen_archive_hero {
            text-align: center;
            padding: 50px 20px;
            background: var(--mayhen-card);
            border-radius: 16px;
            border: 1px solid var(--mayhen-border);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            margin-bottom: 30px;
        }
        .mayhen_archive_tag {
            font-size: 12px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--mayhen-gold);
            font-weight: 600;
            margin-bottom: 12px;
        }
        .mayhen_archive_title {
            font-family: var(--mayhen-serif);
            font-size: 36px;
            font-weight: 400;
            color: var(--mayhen-charcoal);
            margin-bottom: 8px;
        }
        .mayhen_archive_subtitle {
            font-size: 15px;
            color: var(--mayhen-muted);
        }
        .mayhen_archive_section {
            background: var(--mayhen-card);
            border-radius: 16px;
            border: 1px solid var(--mayhen-border);
            padding: 30px;
            margin-bottom: 24px;
        }
        .mayhen_section_title {
            font-family: var(--mayhen-serif);
            font-size: 22px;
            font-weight: 600;
            color: var(--mayhen-charcoal);
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--mayhen-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .mayhen_section_count {
            font-size: 13px;
            font-family: var(--mayhen-font);
            color: var(--mayhen-gold);
            font-weight: 500;
        }
        .mayhen_wish_card {
            padding: 18px;
            border-radius: 10px;
            background: var(--mayhen-cream);
            border: 1px solid var(--mayhen-border);
            margin-bottom: 16px;
        }
        .mayhen_wish_card:last-child { margin-bottom: 0; }
        .mayhen_wish_top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .mayhen_wish_author {
            font-weight: 600;
            color: var(--mayhen-charcoal);
            font-size: 15px;
        }
        .mayhen_wish_time {
            font-size: 12px;
            color: var(--mayhen-muted);
        }
        .mayhen_wish_body {
            font-size: 14px;
            color: #4A443E;
            white-space: pre-line;
        }
        .mayhen_wish_reply {
            margin-top: 12px;
            padding: 12px;
            border-radius: 8px;
            background: #FFF9F2;
            border-left: 3px solid var(--mayhen-gold);
        }
        .mayhen_wish_reply_header {
            font-size: 12px;
            font-weight: 600;
            color: var(--mayhen-gold);
            margin-bottom: 4px;
        }
        .mayhen_wish_reply_content {
            font-size: 13px;
            color: #5C554D;
        }
        .mayhen_empty_box {
            text-align: center;
            padding: 40px;
            color: var(--mayhen-muted);
            font-size: 14px;
        }
        .mayhen_footer {
            text-align: center;
            padding: 30px;
            color: var(--mayhen-muted);
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="mayhen_archive_wrap">
        <header class="mayhen_archive_hero">
            <div class="mayhen_archive_tag">Sổ Kỷ Niệm Hôn Lễ Trọn Đời</div>
            <h1 class="mayhen_archive_title">{$groom} &amp; {$bride}</h1>
            <p class="mayhen_archive_subtitle">Ngày chung đôi: {$weddingDate} • Lưu trữ bởi Mây Hẹn Atelier</p>
        </header>

        <section class="mayhen_archive_section">
            <h2 class="mayhen_section_title">
                <span>Sổ Lưu Bút &amp; Lời Chúc Phúc</span>
                <span class="mayhen_section_count">{$totalWishes} lời chúc</span>
            </h2>
            <div class="mayhen_wishes_list">
                {$wishesHtml}
            </div>
        </section>

        <footer class="mayhen_footer">
            <p>Trang kỷ niệm được lưu trữ tự động ở chế độ Offline. Bạn có thể mở tệp này mọi lúc mà không cần mạng Internet.</p>
            <p style="margin-top: 6px; font-weight: 500;">Mây Hẹn Atelier — Tinh hoa thiệp cưới số</p>
        </footer>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Đóng gói các tệp ảnh album cưới vào thư mục anh-album trong file ZIP.
     */
    protected function packAlbumPhotos(ZipArchive $zip, Invitation $invitation): int
    {
        $payload = $invitation->content_payload ?? [];
        $albums = $payload['albums'] ?? $payload['photos'] ?? [];
        if (! is_array($albums) || empty($albums)) {
            return 0;
        }

        $count = 0;
        $index = 1;

        foreach ($albums as $item) {
            $url = is_string($item) ? $item : ($item['url'] ?? $item['image'] ?? null);
            if (! $url || ! is_string($url)) {
                continue;
            }

            // Kiểm tra xem ảnh có nằm trong public/storage hoặc public/assets không
            $localPath = null;
            if (str_starts_with($url, '/storage/')) {
                $localPath = storage_path('app/public/'.substr($url, 9));
            } elseif (str_starts_with($url, '/assets/')) {
                $localPath = public_path(substr($url, 1));
            }

            if ($localPath && File::exists($localPath) && File::isFile($localPath)) {
                $ext = pathinfo($localPath, PATHINFO_EXTENSION) ?: 'jpg';
                $entryName = sprintf('anh-album/anh-cuoi-%02d.%s', $index++, $ext);
                $zip->addFile($localPath, $entryName);
                $count++;
            }
        }

        return $count;
    }
}
