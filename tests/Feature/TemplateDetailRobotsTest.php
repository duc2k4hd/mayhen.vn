<?php

use App\Models\InvitationTemplate;

test('trang chi tiết mẫu thiệp /mau-thiep/{slug} dùng robots noindex, follow', function () {
    $template = InvitationTemplate::create([
        'slug' => 'mau-robots-test',
        'name' => 'Mẫu Robots Test',
        'category' => 'modern',
        'thumbnail_url' => '/assets/client/images/template-placeholder.webp',
        'demo_url' => '/xem-thu/mau-robots-test',
        'default_config' => ['tier' => 'free'],
        'is_active' => true,
    ]);

    $response = $this->get(route('templates.show', $template->slug));

    $response->assertOk();
    $response->assertSee('<meta name="robots" content="noindex, follow">', false);
    $response->assertDontSee('content="index, follow', false);
});
