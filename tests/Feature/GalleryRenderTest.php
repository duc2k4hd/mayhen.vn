<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;

function galleryRenderInvitation(string $templateSlug, array $extraData): Invitation
{
    $template = InvitationTemplate::create([
        'slug' => $templateSlug,
        'name' => 'Mẫu kiểm thử',
        'thumbnail_url' => '/test-thumbnail.webp',
        'default_config' => ['tier' => 'free'],
    ]);
    $wedding = Wedding::create([
        'user_id' => User::factory()->create()->id,
        'groom_name' => 'Minh',
        'bride_name' => 'An',
        'wedding_date' => '2027-01-01',
        'extra_data' => $extraData,
    ]);

    return Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => 'render-test',
        'is_published' => true,
    ]);
}

test('public invitations preserve uploaded album image URLs', function (string $templateSlug, string $imageUrl) {
    $invitation = galleryRenderInvitation($templateSlug, ['gallery' => [$imageUrl]]);

    $expected = $templateSlug === 'giua-muon-ngan-vi-sao' ? "url('".e($imageUrl)."')" : 'src="'.e($imageUrl).'"';
    $this->get('/thiep/'.$invitation->slug)->assertOk()->assertSee($expected, false);
})->with(['giua-muon-ngan-vi-sao', 'hanh-phuc-mang-ten-chung-minh'])->with([
    'raster' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jf1kAAAAASUVORK5CYII=',
    'stored' => '/storage/weddings/album.webp',
    'uploads' => '/uploads/weddings/album.webp',
]);

test('public invitations retain safe QR SVG fallbacks', function (string $templateSlug, string $imageUrl) {
    $invitation = galleryRenderInvitation($templateSlug, ['qr_groom_image' => $imageUrl]);

    $expected = $templateSlug === 'giua-muon-ngan-vi-sao' ? "url('".e($imageUrl)."')" : 'src="'.e($imageUrl).'"';
    $this->get('/thiep/'.$invitation->slug)->assertOk()->assertSee($expected, false);
})->with(['giua-muon-ngan-vi-sao', 'hanh-phuc-mang-ten-chung-minh'])->with([
    'base64' => 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0h10v10z"/></svg>'),
    'utf8' => 'data:image/svg+xml;utf8,'.rawurlencode('<svg xmlns="http://www.w3.org/2000/svg"><rect width="10" height="10"/></svg>'),
]);

test('public invitations reject invalid uploaded image data', function (string $templateSlug, string $imageUrl) {
    $invitation = galleryRenderInvitation($templateSlug, ['gallery' => [$imageUrl]]);

    $expected = $templateSlug === 'giua-muon-ngan-vi-sao' ? "url('".e($imageUrl)."')" : 'src="'.e($imageUrl).'"';
    $this->get('/thiep/'.$invitation->slug)->assertOk()->assertDontSee($expected, false);
})->with(['giua-muon-ngan-vi-sao', 'hanh-phuc-mang-ten-chung-minh'])->with([
    'html' => 'data:text/html;base64,'.base64_encode('<script>alert(1)</script>'),
    'fake image' => 'data:image/png;base64,'.base64_encode('not an image'),
    'active svg' => 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"/>'),
]);

test('public invitations keep explicitly cleared optional text empty', function (string $templateSlug) {
    $invitation = galleryRenderInvitation($templateSlug, ['groom_mother' => null, 'story_title' => null]);

    $response = $this->get('/thiep/'.$invitation->slug)->assertOk();
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
    $xpath = new DOMXPath($document);
    foreach (['groom_mother', 'story_title'] as $key) {
        $nodes = $xpath->query('//*[@data-sync-target="'.$key.'"]');
        expect($nodes->length)->toBeGreaterThan(0);
        foreach ($nodes as $node) {
            expect(trim($node->textContent))->toBe('');
        }
    }
})->with(['giua-muon-ngan-vi-sao', 'hanh-phuc-mang-ten-chung-minh', 'ngay-tinh-yeu-dom-hoa', 'tron-kiep-nay-ben-nhau']);

test('public invitations retain template defaults for absent optional text', function (string $templateSlug) {
    $invitation = galleryRenderInvitation($templateSlug, []);

    $response = $this->get('/thiep/'.$invitation->slug)->assertOk();
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
    $xpath = new DOMXPath($document);
    foreach (['groom_mother', 'story_title'] as $key) {
        $nodes = $xpath->query('//*[@data-sync-target="'.$key.'"]');
        expect($nodes->length)->toBeGreaterThan(0);
        foreach ($nodes as $node) {
            expect(trim($node->textContent))->not->toBe('');
        }
    }
})->with(['giua-muon-ngan-vi-sao', 'hanh-phuc-mang-ten-chung-minh', 'ngay-tinh-yeu-dom-hoa', 'tron-kiep-nay-ben-nhau']);

test('public invitations derive their date labels from the saved wedding date', function (string $templateSlug) {
    $invitation = galleryRenderInvitation($templateSlug, [
        'wedding_date_digits' => '22.11.2026',
        'wedding_day' => '22',
        'wedding_month' => '11',
        'wedding_year' => '2026',
        'calendar_month_year' => 'Tháng 11/2026',
    ]);
    $invitation->wedding->update(['wedding_date' => '2027-02-14']);

    $response = $this->get('/thiep/'.$invitation->slug)->assertOk();
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
    $xpath = new DOMXPath($document);
    $dateNodes = $xpath->query('//*[@data-sync-target="wedding_date_digits"]');
    expect($dateNodes->length)->toBeGreaterThan(0);
    foreach ($dateNodes as $node) {
        expect(preg_replace('/\s+/', '', $node->textContent))->toBe('14.02.2027');
    }
})->with(['giua-muon-ngan-vi-sao', 'hanh-phuc-mang-ten-chung-minh', 'ngay-tinh-yeu-dom-hoa', 'tron-kiep-nay-ben-nhau']);

test('the editor restores saved dates and explicit clears without reviving template defaults', function (string $templateSlug) {
    $imageUrl = 'https://mayhen.vn/storage/weddings/album.webp';
    $invitation = galleryRenderInvitation($templateSlug, [
        'story_title' => null,
        'groom_bank_account' => null,
        'groom_account_number' => '0000000000',
        'gallery' => [$imageUrl],
        'timeline' => [['time' => null, 'title' => 'Lễ cưới']],
    ]);

    $response = $this->withSession(['owned_invitation_slugs' => [$invitation->slug]])
        ->get(route('invitation.create', ['edit' => $invitation->slug]))->assertOk();
    $document = new DOMDocument;
    @$document->loadHTML('<?xml encoding="utf-8" ?>'.$response->getContent());
    $xpath = new DOMXPath($document);
    foreach ([
        'wedding_date' => '2027-01-01',
        'extra_data.story_title' => '',
        'extra_data.groom_bank_account' => '',
        'extra_data.gallery.0' => $imageUrl,
        'extra_data.timeline.0.time' => '',
    ] as $key => $value) {
        $nodes = $xpath->query('//*[@data-sync-key="wedding.'.$key.'"]');
        expect($nodes->length)->toBeGreaterThan(0);
        foreach ($nodes as $node) {
            expect($node->getAttribute('value'))->toBe($value);
        }
    }
})->with(['giua-muon-ngan-vi-sao', 'hanh-phuc-mang-ten-chung-minh', 'ngay-tinh-yeu-dom-hoa', 'tron-kiep-nay-ben-nhau']);
