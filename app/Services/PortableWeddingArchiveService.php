<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Wedding;
use Illuminate\Support\Facades\File;
use ZipArchive;

class PortableWeddingArchiveService
{
    /**
     * Tạo gói lưu trữ kỷ niệm hôn nhân ngoại tuyến hoàn chỉnh (ZIP).
     *
     * @return array{
     *     zip_path: string,
     *     zip_filename: string,
     *     manifest: array<string, mixed>
     * }
     */
    public function generateArchive(Invitation $invitation): array
    {
        $wedding = $invitation->wedding;
        $tempDir = storage_path('app/temp/archive_'.uniqid($invitation->slug.'_', true));
        File::ensureDirectoryExists($tempDir);

        $groomName = $wedding->groom_name ?? 'Chú Rể';
        $brideName = $wedding->bride_name ?? 'Cô Dâu';
        $weddingDate = $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : 'Chưa đặt';
        $venue = $wedding->venue_name ?? 'Tư gia';
        $address = $wedding->venue_address ?? '';

        // 1. Dữ liệu lời chúc và sổ lưu bút
        $wishes = $wedding->wishes()
            ->where('is_approved', true)
            ->oldest()
            ->get();

        $wishesData = $wishes->map(fn ($w) => [
            'id' => $w->id,
            'name' => $w->name,
            'message' => $w->message,
            'reply' => $w->reply,
            'created_at' => $w->created_at?->format('d/m/Y H:i') ?? '',
        ])->values()->toArray();

        $wishesJson = json_encode($wishesData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $wishesJsonPath = $tempDir.'/loi_chuc_va_chu_ky.json';
        File::put($wishesJsonPath, $wishesJson);

        // 2. Danh sách khách mời và RSVP dạng CSV (UTF-8 with BOM)
        $guests = $wedding->guests()->with('response')->get();
        $csvLines = [];
        $csvLines[] = "\xEF\xBB\xBF".'STT,Họ và Tên,Danh Xưng,Nhóm Khách,Bàn Tiệc,Số Suất Mời,Trạng Thái RSVP,Điểm Danh';
        $index = 1;
        foreach ($guests as $g) {
            $rsvpStatus = match ($g->response?->attending_status) {
                'attending' => 'Tham dự',
                'declined' => 'Không thể đến',
                'uncertain' => 'Chưa chắc chắn',
                default => 'Chưa phản hồi'
            };
            $checkIn = $g->checked_in_at ? 'Đã điểm danh ('.$g->checked_in_at->format('H:i d/m').')' : 'Chưa điểm danh';
            $csvLines[] = sprintf(
                '"%d","%s","%s","%s","%s","%d","%s","%s"',
                $index++,
                str_replace('"', '""', $g->name),
                str_replace('"', '""', (string) $g->salutation),
                str_replace('"', '""', (string) $g->group_name),
                str_replace('"', '""', (string) $g->table_name),
                (int) $g->expected_attendees,
                $rsvpStatus,
                $checkIn
            );
        }
        $guestsCsvPath = $tempDir.'/danh_sach_khach_moi_va_rsvp.csv';
        File::put($guestsCsvPath, implode("\r\n", $csvLines));

        // 2.1. Dữ liệu sổ mừng cưới và tiền mừng (Section 35 & 36 PRD V3)
        $gifts = $wedding->gifts()->latest()->get();
        $giftCsvLines = ["\xEF\xBB\xBF".'STT,Họ và Tên Người Mừng,Nhóm Khách,Số Tiền (VNĐ),Hình Thức,Đã Cảm Ơn,Ghi Chú,Thời Gian'];
        $giftIdx = 1;
        foreach ($gifts as $gft) {
            $giftCsvLines[] = sprintf(
                '"%d","%s","%s","%s","%s","%s","%s","%s"',
                $giftIdx++,
                str_replace('"', '""', $gft->sender_name),
                str_replace('"', '""', (string) $gft->sender_group),
                number_format((float) $gft->amount, 0, '', ''),
                $gft->payment_method === 'bank_transfer' ? 'Chuyển khoản' : ($gft->payment_method === 'cash' ? 'Tiền mặt' : 'Khác'),
                $gft->is_acknowledged ? 'Đã cảm ơn' : 'Chưa',
                str_replace('"', '""', (string) $gft->notes),
                $gft->created_at ? $gft->created_at->format('d/m/Y H:i') : ''
            );
        }
        $giftCsvPath = $tempDir.'/so_mung_cuoi_va_tien_mung.csv';
        File::put($giftCsvPath, implode("\r\n", $giftCsvLines));

        // 2.2. Dữ liệu lịch trình và nhà cung cấp cưới (Vendors & Events)
        $eventsData = $wedding->events()->orderBy('display_order')->get()->map(fn ($ev) => [
            'name' => $ev->name,
            'event_date' => $ev->event_date?->format('d/m/Y'),
            'event_time' => $ev->event_time,
            'venue_name' => $ev->venue_name,
            'venue_address' => $ev->venue_address,
        ])->toArray();

        $vendorsData = $wedding->vendors()->orderBy('category')->get()->map(fn ($vd) => [
            'category' => $vd->category,
            'name' => $vd->name,
            'contact_person' => $vd->contact_person,
            'phone' => $vd->phone,
            'status' => $vd->status,
            'contract_amount' => $vd->contract_amount,
            'paid_amount' => $vd->paid_amount,
        ])->toArray();

        $eventsAndVendorsPath = $tempDir.'/lich_trinh_va_nha_cung_cap.json';
        File::put($eventsAndVendorsPath, json_encode([
            'events' => $eventsData,
            'vendors' => $vendorsData,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // 2.3. Trọn bộ dữ liệu hôn lễ chuẩn Data Portability (Law 91/2025/QH15)
        $fullDump = [
            'project' => 'Mây Hẹn (mayhen.vn)',
            'exported_at' => now()->toIso8601String(),
            'wedding' => [
                'groom_name' => $groomName,
                'bride_name' => $brideName,
                'wedding_date' => $weddingDate,
                'venue' => $venue,
                'address' => $address,
            ],
            'statistics' => [
                'total_guests' => $guests->count(),
                'total_wishes' => $wishes->count(),
                'total_gifts' => $gifts->count(),
                'total_gift_amount' => (int) $gifts->sum('amount'),
            ],
            'wishes' => $wishesData,
            'events' => $eventsData,
            'vendors' => $vendorsData,
        ];
        $fullDumpPath = $tempDir.'/toan_bo_du_lieu_hon_le.json';
        File::put($fullDumpPath, json_encode($fullDump, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // 3. Trang thiệp tĩnh ngoại tuyến index.html độc lập (Standalone Offline HTML)
        $htmlContent = $this->renderStandaloneHtml($wedding, $invitation, $wishes, $guests);
        $htmlPath = $tempDir.'/index.html';
        File::put($htmlPath, $htmlContent);

        // 4. File hướng dẫn lưu trữ trân trọng
        $readmeContent = $this->renderReadme($groomName, $brideName, $weddingDate);
        $readmePath = $tempDir.'/HUONG_DAN_LUU_TRU_KY_NIEM.txt';
        File::put($readmePath, $readmeContent);

        // 5. Bảng kê khai chứng thực số (Digital Provenance Manifest & SHA-256 Integrity Checksums)
        $manifest = [
            'project' => 'Mây Hẹn (mayhen.vn) — Wedding Keepsake Archive',
            'version' => '2.0-offline',
            'wedding' => [
                'groom_name' => $groomName,
                'bride_name' => $brideName,
                'wedding_date' => $weddingDate,
                'venue' => $venue,
                'address' => $address,
                'invitation_slug' => $invitation->slug,
            ],
            'statistics' => [
                'total_guests' => $guests->count(),
                'total_wishes' => $wishes->count(),
                'exported_at' => now()->toIso8601String(),
            ],
            'checksums_sha256' => [
                'index.html' => hash_file('sha256', $htmlPath),
                'loi_chuc_va_chu_ky.json' => hash_file('sha256', $wishesJsonPath),
                'danh_sach_khach_moi_va_rsvp.csv' => hash_file('sha256', $guestsCsvPath),
                'so_mung_cuoi_va_tien_mung.csv' => hash_file('sha256', $giftCsvPath),
                'lich_trinh_va_nha_cung_cap.json' => hash_file('sha256', $eventsAndVendorsPath),
                'toan_bo_du_lieu_hon_le.json' => hash_file('sha256', $fullDumpPath),
                'HUONG_DAN_LUU_TRU_KY_NIEM.txt' => hash_file('sha256', $readmePath),
            ],
        ];

        $manifestJson = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $manifestPath = $tempDir.'/wedding_manifest.json';
        File::put($manifestPath, $manifestJson);

        // 6. Đóng gói thành file ZIP
        $zipFilename = 'Ky_Niem_Hon_Nhan_'.preg_replace('/[^a-zA-Z0-9_-]/', '_', $invitation->slug).'_'.date('Ymd').'.zip';
        $zipPath = storage_path('app/temp/'.$zipFilename);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zip->addFile($htmlPath, 'index.html');
            $zip->addFile($wishesJsonPath, 'loi_chuc_va_chu_ky.json');
            $zip->addFile($guestsCsvPath, 'danh_sach_khach_moi_va_rsvp.csv');
            $zip->addFile($giftCsvPath, 'so_mung_cuoi_va_tien_mung.csv');
            $zip->addFile($eventsAndVendorsPath, 'lich_trinh_va_nha_cung_cap.json');
            $zip->addFile($fullDumpPath, 'toan_bo_du_lieu_hon_le.json');
            $zip->addFile($readmePath, 'HUONG_DAN_LUU_TRU_KY_NIEM.txt');
            $zip->addFile($manifestPath, 'wedding_manifest.json');
            $zip->close();
        }

        // Dọn dẹp thư mục tạm
        File::deleteDirectory($tempDir);

        return [
            'zip_path' => $zipPath,
            'zip_filename' => $zipFilename,
            'manifest' => $manifest,
        ];
    }

    /**
     * Render trang HTML tĩnh ngoại tuyến hoàn chỉnh, không cần kết nối internet.
     */
    private function renderStandaloneHtml(Wedding $wedding, Invitation $invitation, $wishes, $guests): string
    {
        $groom = htmlspecialchars($wedding->groom_name ?? 'Chú Rể', ENT_QUOTES, 'UTF-8');
        $bride = htmlspecialchars($wedding->bride_name ?? 'Cô Dâu', ENT_QUOTES, 'UTF-8');
        $weddingDate = htmlspecialchars($wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : 'Chưa đặt ngày', ENT_QUOTES, 'UTF-8');
        $venue = htmlspecialchars($wedding->venue_name ?? 'Tư Gia Hai Nhà', ENT_QUOTES, 'UTF-8');
        $address = htmlspecialchars($wedding->venue_address ?? '', ENT_QUOTES, 'UTF-8');

        $wishesHtml = '';
        if ($wishes->isEmpty()) {
            $wishesHtml = '<p style="text-align: center; color: #7A726A; font-style: italic;">Chưa có lời chúc nào được lưu trong sổ.</p>';
        } else {
            foreach ($wishes as $w) {
                $name = htmlspecialchars($w->name, ENT_QUOTES, 'UTF-8');
                $msg = nl2br(htmlspecialchars($w->message, ENT_QUOTES, 'UTF-8'));
                $time = $w->created_at ? $w->created_at->format('d/m/Y H:i') : '';
                $replyHtml = '';
                if (! empty($w->reply)) {
                    $replyText = nl2br(htmlspecialchars($w->reply, ENT_QUOTES, 'UTF-8'));
                    $replyHtml = "<div class=\"wish-reply\"><strong>Hồi đáp từ {$groom} & {$bride}:</strong><p>{$replyText}</p></div>";
                }

                $wishesHtml .= <<<HTML
                <div class="wish-card">
                    <div class="wish-header">
                        <strong class="wish-name">{$name}</strong>
                        <span class="wish-time">{$time}</span>
                    </div>
                    <div class="wish-message">{$msg}</div>
                    {$replyHtml}
                </div>
HTML;
            }
        }

        $totalGuests = $guests->count();
        $totalAttended = $guests->whereNotNull('checked_in_at')->count();
        $totalWishes = $wishes->count();

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kỷ Niệm Ngày Cưới: {$groom} &amp; {$bride} — Mây Hẹn</title>
    <style>
        :root {
            --bg-cream: #FAF8F5;
            --bg-card: #FFFFFF;
            --border-beige: #E5DCCD;
            --text-dark: #1F1D1A;
            --text-muted: #7A726A;
            --gold-accent: #C5A880;
            --gold-deep: #9C7A4A;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: var(--bg-cream);
            color: var(--text-dark);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            padding: 30px 16px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            padding: 40px 20px;
            background: var(--bg-card);
            border: 1px solid var(--border-beige);
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }
        .badge {
            display: inline-block;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--gold-deep);
            font-weight: 700;
            margin-bottom: 12px;
        }
        h1 {
            font-family: "Playfair Display", Georgia, serif;
            font-size: 36px;
            color: var(--text-dark);
            margin-bottom: 8px;
            font-weight: 600;
        }
        .date {
            font-size: 16px;
            color: var(--gold-deep);
            font-weight: 600;
            margin-bottom: 6px;
        }
        .venue {
            font-size: 14px;
            color: var(--text-muted);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-beige);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
        }
        .stat-num {
            font-size: 24px;
            font-weight: 700;
            color: var(--gold-deep);
            display: block;
        }
        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-title {
            font-size: 20px;
            font-family: Georgia, serif;
            color: var(--text-dark);
            margin: 32px 0 16px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .section-title::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--border-beige);
        }
        .wish-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .wish-card {
            background: var(--bg-card);
            border: 1px solid var(--border-beige);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
        }
        .wish-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px dashed var(--border-beige);
            padding-bottom: 8px;
        }
        .wish-name {
            font-size: 15px;
            color: var(--text-dark);
            font-weight: 600;
        }
        .wish-time {
            font-size: 12px;
            color: var(--text-muted);
        }
        .wish-message {
            font-size: 14.5px;
            color: #4A453E;
            line-height: 1.6;
        }
        .wish-reply {
            margin-top: 12px;
            background: #FAF8F5;
            border-left: 3px solid var(--gold-accent);
            padding: 10px 14px;
            border-radius: 4px;
            font-size: 13px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 24px;
            border-top: 1px solid var(--border-beige);
            font-size: 12px;
            color: var(--text-muted);
        }
        @media (max-width: 600px) {
            h1 { font-size: 26px; }
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <span class="badge">⚜️ KỶ NIỆM HÔN LỄ TRỌN VẸN ⚜️</span>
            <h1>{$groom} &amp; {$bride}</h1>
            <div class="date">Ngày cưới: {$weddingDate}</div>
            <div class="venue">{$venue} — {$address}</div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-num">{$totalGuests}</span>
                <span class="stat-label">Khách Mời Trong Danh Bạ</span>
            </div>
            <div class="stat-card">
                <span class="stat-num">{$totalAttended}</span>
                <span class="stat-label">Quan Khách Đã Điểm Danh</span>
            </div>
            <div class="stat-card">
                <span class="stat-num">{$totalWishes}</span>
                <span class="stat-label">Lời Chúc Phúc Đã Lưu</span>
            </div>
        </div>

        <h2 class="section-title">Sổ Lưu Bút &amp; Lời Chúc Mừng</h2>
        <div class="wish-list">
            {$wishesHtml}
        </div>

        <footer class="footer">
            <p>Tài liệu lưu trữ kỷ niệm ngoại tuyến độc lập — MayHen.vn</p>
            <p>Trang web tự chứa (Self-contained) có thể mở trên bất kỳ thiết bị nào mà không cần kết nối mạng internet.</p>
        </footer>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Render văn bản hướng dẫn lưu trữ trân trọng.
     */
    private function renderReadme(string $groom, string $bride, string $weddingDate): string
    {
        return <<<TXT
================================================================================
             MÂY HẸN (MAYHEN.VN) — BỘ LƯU TRỮ KỶ NIỆM HÔN LỄ NGOẠI TUYẾN
================================================================================

Kính gửi đôi uyên ương {$groom} & {$bride},

Gói tài liệu nén này là BẢN LƯU TRỮ ĐỘC LẬP HOÀN CHỈNH lưu giữ toàn bộ ký ức,
danh sách khách mời, phản hồi RSVP, sổ lưu bút và những lời chúc phúc quý báu
trong ngày hạnh phúc ({$weddingDate}) của hai bạn.

--------------------------------------------------------------------------------
DANH MỤC TÀI LIỆU TRONG GÓI LƯU TRỮ:
--------------------------------------------------------------------------------
1. index.html:
   Trang web kỷ niệm tĩnh tự chứa (Self-contained). Bạn có thể mở trực tiếp
   bằng bất kỳ trình duyệt web nào (Chrome, Safari, Edge, Cốc Cốc...) trên
   máy tính, máy tính bảng hay điện thoại di động mà HOÀN TOÀN KHÔNG CẦN INTERNET.

2. danh_sach_khach_moi_va_rsvp.csv:
   Bảng tính Excel tổng hợp toàn bộ danh bạ khách mời, số suất đi kèm,
   trạng thái xác nhận tham dự và thời điểm điểm danh đón tiếp.

3. loi_chuc_va_chu_ky.json:
   Dữ liệu nguyên gốc của toàn bộ lời chúc phúc và hồi đáp từ cô dâu chú rể.

4. wedding_manifest.json:
   Bảng kê khai chứng thực số kèm mã băm SHA-256 bảo đảm tính toàn vẹn, chứng minh
   tài liệu kỷ niệm này nguyên bản và không bị sai lệch theo thời gian.

--------------------------------------------------------------------------------
LỜI KHUYÊN BẢO QUẢN KỶ NIỆM BỀN VỮNG:
--------------------------------------------------------------------------------
- Hãy lưu gói Zip này vào ít nhất 2 nơi: một bản trên ổ cứng máy tính cá nhân
  hoặc USB gia đình, và một bản trên dịch vụ lưu trữ đám mây (Google Drive, iCloud...).
- Dù 10 hay 20 năm sau, khi mở lại file index.html, những lời chúc mừng và khoảnh khắc
  ngày hạnh phúc của hai bạn vẫn vẹn nguyên như ngày đầu tiên.

Mây Hẹn chúc hai bạn trăm năm hạnh phúc, tình nghĩa đậm sâu và luôn mỉm cười khi nhìn lại
những kỷ niệm đẹp đẽ này!

Trân trọng,
Đội ngũ Mây Hẹn (mayhen.vn)
TXT;
    }
}
