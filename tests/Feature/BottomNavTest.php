<?php

use App\Models\InvitationTemplate;
use Database\Seeders\InvitationTemplateSeeder;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);
});

test('bottom navigation bar renders on all client pages with 5 svg icons', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('mayhen_client_bottom_nav_wrapper', false);
    $response->assertSee('mayhen_client_bottom_nav_item_create', false);
    $response->assertSee('Trang chủ', false);
    $response->assertSee('Mẫu thiệp', false);
    $response->assertSee('Tạo thiệp', false);
    $response->assertSee('Yêu thích', false);
    $response->assertSee('Trợ lý AI', false);
});

test('bottom navigation bar renders on preview page for active templates', function () {
    $templates = InvitationTemplate::where('is_active', true)->pluck('slug');
    expect($templates)->not->toBeEmpty();

    foreach ($templates as $slug) {
        $response = $this->get(route('templates.preview', ['slug' => $slug]));
        $response->assertStatus(200);
        $response->assertSee('mayhen_client_bottom_nav_wrapper', false);
        $response->assertSee('bottom-nav.css', false);
        $response->assertSee(route('invitation.create', ['template' => $slug]), false);
    }
});

test('bottom navigation bar is hidden on preview page when embed parameter is present', function () {
    $templates = InvitationTemplate::where('is_active', true)->pluck('slug');
    expect($templates)->not->toBeEmpty();

    foreach ($templates as $slug) {
        $response = $this->get(route('templates.preview', ['slug' => $slug, 'embed' => 1]));
        $response->assertStatus(200);
        $response->assertDontSee('class="mayhen_client_bottom_nav_wrapper', false);
        $response->assertDontSee('bottom-nav.css', false);
    }
});
