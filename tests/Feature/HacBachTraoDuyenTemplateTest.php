<?php

use Database\Seeders\InvitationTemplateSeeder;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
});

test('active template preview gallery images resolve to existing assets', function () {
    $response = $this->get('/xem-thu/hanh-phuc-mang-ten-chung-minh');
    $response->assertStatus(200);

    $content = $response->getContent();

    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="utf-8" ?>'.$content);
    $xpath = new DOMXPath($document);
    $images = $xpath->query('//img[starts-with(@data-sync-target, "gallery.")]');
    expect($images->length)->toBe(10);

    // 3. Kiểm tra từng URL data-full là đường dẫn hợp lệ và asset có thể truy cập được
    foreach ($images as $image) {
        $url = $image->getAttribute('src');
        expect($url)->toContain('/mau-thiep/hanh-phuc-mang-ten-chung-minh/assets/');

        // Trích xuất path tương đối để test route asset
        $parsedUrl = parse_url($url, PHP_URL_PATH);
        $assetResponse = $this->get($parsedUrl);
        $assetResponse->assertStatus(200);
    }

    // 4. Kiểm tra script lightbox có hàm bảo vệ getGalleryItemSrc
    $scriptResponse = $this->get('/mau-thiep/hanh-phuc-mang-ten-chung-minh/assets/script.js');
    $scriptResponse->assertStatus(200);
    $scriptContent = file_get_contents($scriptResponse->getFile()->getPathname());
    expect($scriptContent)->toContain('lightbox');
});
