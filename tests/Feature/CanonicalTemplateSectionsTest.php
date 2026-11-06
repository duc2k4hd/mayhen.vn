<?php

use App\Models\InvitationTemplate;
use App\Services\InvitationTemplateService;
use Database\Seeders\InvitationTemplateSeeder;

test('all active templates use the ten canonical sections in order', function () {
    $this->seed(InvitationTemplateSeeder::class);
    $service = app(InvitationTemplateService::class);
    $canonicalAllowed = InvitationTemplateService::CANONICAL_SECTION_ORDER;
    $templates = InvitationTemplate::where('is_active', true)->pluck('slug');
    expect($templates)->not->toBeEmpty();
    expect($canonicalAllowed)->toHaveCount(10);

    foreach ($templates as $slug) {
        $schema = $service->getSchema($slug);

        expect($schema)->toBeArray()
            ->and($schema)->toHaveKey('sections')
            ->and($schema)->toHaveKey('supported_sections');

        $sectionIds = array_column($schema['sections'], 'id');

        expect($sectionIds)->toBe($canonicalAllowed)
            ->and($schema['supported_sections'])->toBe($canonicalAllowed);
    }
});
