<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Support\Arr;

function makeGuestManagementInvitation(User $owner, string $slug): Invitation
{
    $template = InvitationTemplate::create([
        'slug' => 'template-'.$slug,
        'name' => 'Mẫu kiểm thử',
        'thumbnail_url' => '/test-thumbnail.webp',
    ]);

    $wedding = Wedding::create([
        'user_id' => $owner->id,
        'groom_name' => 'Minh',
        'bride_name' => 'An',
        'wedding_date' => '2027-01-01',
    ]);

    return Invitation::create([
        'wedding_id' => $wedding->id,
        'template_id' => $template->id,
        'slug' => $slug,
    ]);
}

test('a wedding owner can view add and delete guests', function () {
    $owner = User::factory()->create();
    $invitation = makeGuestManagementInvitation($owner, 'owner-wedding');
    $guest = Guest::create(['wedding_id' => $invitation->wedding_id, 'name' => 'Khách cũ']);

    $this->actingAs($owner)->get(route('guests.index', $invitation->id))
        ->assertOk()->assertSee('Khách cũ');
    $this->actingAs($owner)->post(route('guests.store', $invitation->id), ['name' => 'Khách mới'])
        ->assertRedirect(route('guests.index', $invitation->id));
    $this->assertDatabaseHas('guests', ['wedding_id' => $invitation->wedding_id, 'name' => 'Khách mới']);

    $this->actingAs($owner)->delete(route('guests.destroy', $guest->id))->assertRedirect();
    $this->assertDatabaseMissing('guests', ['id' => $guest->id]);
});

test('other users cannot view add or delete guests from a wedding', function () {
    $invitation = makeGuestManagementInvitation(User::factory()->create(), 'private-wedding');
    $guest = Guest::create(['wedding_id' => $invitation->wedding_id, 'name' => 'Khách riêng']);
    $otherUser = User::factory()->create();

    $this->actingAs($otherUser)->get(route('guests.index', $invitation->id))
        ->assertNotFound()->assertDontSee('Khách riêng');
    $this->actingAs($otherUser)->post(route('guests.store', $invitation->id), ['name' => 'Khách lạ'])
        ->assertNotFound();
    $this->actingAs($otherUser)->delete(route('guests.destroy', $guest->id))->assertNotFound();

    $this->assertDatabaseHas('guests', ['id' => $guest->id]);
    $this->assertDatabaseMissing('guests', ['wedding_id' => $invitation->wedding_id, 'name' => 'Khách lạ']);
});

test('anonymous visitors without the owner session cannot manage guests', function () {
    $invitation = makeGuestManagementInvitation(User::factory()->create(), 'anonymous-wedding');
    $guest = Guest::create(['wedding_id' => $invitation->wedding_id, 'name' => 'Khách riêng']);

    $this->get(route('guests.index', $invitation->id))->assertNotFound();
    $this->post(route('guests.store', $invitation->id), ['name' => 'Khách lạ'])->assertNotFound();
    $this->delete(route('guests.destroy', $guest->id))->assertNotFound();
    $this->assertDatabaseHas('guests', ['id' => $guest->id]);
    $this->assertDatabaseMissing('guests', ['wedding_id' => $invitation->wedding_id, 'name' => 'Khách lạ']);
});

test('an owner session can manage guests across invitations belonging to the same wedding', function () {
    $invitation = makeGuestManagementInvitation(User::factory()->create(), 'session-wedding');
    $anotherInvitation = Invitation::create([
        'wedding_id' => $invitation->wedding_id,
        'template_id' => $invitation->template_id,
        'slug' => 'session-wedding-second',
    ]);
    $guest = Guest::create(['wedding_id' => $invitation->wedding_id, 'name' => 'Khách cũ']);
    $this->withSession(['owned_invitation_slugs' => [$invitation->slug]]);

    $this->get(route('guests.index', $anotherInvitation->id))->assertOk()->assertSee('Khách cũ');
    $this->post(route('guests.store', $anotherInvitation->id), ['name' => 'Khách mới'])
        ->assertRedirect(route('guests.index', $anotherInvitation->id));
    $this->delete(route('guests.destroy', $guest->id))->assertRedirect();
    $this->assertDatabaseHas('guests', ['wedding_id' => $invitation->wedding_id, 'name' => 'Khách mới']);
    $this->assertDatabaseMissing('guests', ['id' => $guest->id]);
});

test('an owner session for another wedding cannot manage guests', function () {
    $ownInvitation = makeGuestManagementInvitation(User::factory()->create(), 'session-own-wedding');
    $otherInvitation = makeGuestManagementInvitation(User::factory()->create(), 'session-other-wedding');
    $otherGuest = Guest::create(['wedding_id' => $otherInvitation->wedding_id, 'name' => 'Khách riêng']);
    $this->withSession(['owned_invitation_slugs' => [$ownInvitation->slug]]);

    $this->get(route('guests.index', $otherInvitation->id))->assertNotFound();
    $this->post(route('guests.store', $otherInvitation->id), ['name' => 'Khách lạ'])->assertNotFound();
    $this->delete(route('guests.destroy', $otherGuest->id))->assertNotFound();
    $this->assertDatabaseHas('guests', ['id' => $otherGuest->id]);
    $this->assertDatabaseMissing('guests', ['wedding_id' => $otherInvitation->wedding_id, 'name' => 'Khách lạ']);
});

test('autosave rejects both older and newer revisions without changing data', function (int $revision) {
    $owner = User::factory()->create();
    $invitation = makeGuestManagementInvitation($owner, 'revision-conflict');
    $invitation->update(['revision_id' => 5, 'draft_config' => ['theme' => 'original']]);

    $this->actingAs($owner)->postJson(route('invitation.autosave'), [
        'invitation_id' => $invitation->id,
        'revision_id' => $revision,
        'draft_config' => ['theme' => 'changed'],
        'wedding_delta' => ['groom_name' => 'Changed'],
    ])->assertStatus(409)->assertJson(['conflict' => true, 'current_revision' => 5]);

    expect($invitation->fresh()->draft_config)->toBe(['theme' => 'original']);
    expect($invitation->fresh()->revision_id)->toBe(5);
    expect($invitation->wedding->fresh()->groom_name)->toBe('Minh');
})->with([4, 6]);

test('autosave accepts the current revision and increments it', function () {
    $owner = User::factory()->create();
    $invitation = makeGuestManagementInvitation($owner, 'revision-current');
    $invitation->update(['revision_id' => 5]);

    $this->actingAs($owner)->postJson(route('invitation.autosave'), [
        'invitation_id' => $invitation->id,
        'revision_id' => 5,
        'draft_config' => ['theme' => 'updated'],
        'wedding_delta' => ['groom_name' => 'Tuấn'],
    ])->assertOk()->assertJson(['success' => true, 'new_revision_id' => 6]);

    expect($invitation->fresh()->draft_config)->toBe(['theme' => 'updated']);
    expect($invitation->wedding->fresh()->groom_name)->toBe('Tuấn');
});

test('autosave merges partial gallery and timeline edits without losing other entries', function () {
    $owner = User::factory()->create();
    $invitation = makeGuestManagementInvitation($owner, 'autosave-list-merge');
    $invitation->wedding->update(['extra_data' => [
        'gallery' => ['/storage/first.webp', '/storage/second.webp', '/storage/third.webp'],
        'timeline' => [
            ['time' => '08:00', 'title' => 'Đón khách'],
            ['time' => '09:00', 'title' => 'Lễ cưới'],
        ],
        'other' => 'Giữ lại',
    ]]);

    $this->actingAs($owner)->postJson(route('invitation.autosave'), [
        'invitation_id' => $invitation->id,
        'revision_id' => $invitation->fresh()->revision_id,
        'wedding_delta' => ['extra_data' => [
            'gallery' => [1 => '/storage/new.webp'],
            'timeline' => [1 => ['title' => 'Khai tiệc']],
        ]],
    ])->assertOk();

    expect($invitation->wedding->fresh()->extra_data)->toBe([
        'gallery' => ['/storage/first.webp', '/storage/new.webp', '/storage/third.webp'],
        'timeline' => [
            ['time' => '08:00', 'title' => 'Đón khách'],
            ['time' => '09:00', 'title' => 'Khai tiệc'],
        ],
        'other' => 'Giữ lại',
    ]);
});

test('autosave supports explicit clearing of a gallery entry and an entire timeline', function () {
    $owner = User::factory()->create();
    $invitation = makeGuestManagementInvitation($owner, 'autosave-list-clear');
    $invitation->wedding->update(['extra_data' => [
        'gallery' => ['/storage/first.webp', '/storage/second.webp'],
        'timeline' => [['time' => '08:00', 'title' => 'Đón khách']],
    ]]);

    $this->actingAs($owner)->postJson(route('invitation.autosave'), [
        'invitation_id' => $invitation->id,
        'revision_id' => $invitation->fresh()->revision_id,
        'wedding_delta' => ['extra_data' => ['gallery' => [1 => ''], 'timeline' => []]],
    ])->assertOk();

    expect($invitation->wedding->fresh()->extra_data)->toBe([
        'gallery' => ['/storage/first.webp', null],
        'timeline' => [],
    ]);
});

test('autosave does not replace stored images with temporary blob urls', function () {
    $owner = User::factory()->create();
    $invitation = makeGuestManagementInvitation($owner, 'autosave-pending-image');
    $invitation->wedding->update(['extra_data' => [
        'gallery' => ['/storage/first.webp', '/storage/second.webp'],
        'hero_photo_url' => '/storage/hero.webp',
    ]]);

    $this->actingAs($owner)->postJson(route('invitation.autosave'), [
        'invitation_id' => $invitation->id,
        'revision_id' => $invitation->fresh()->revision_id,
        'wedding_delta' => ['extra_data' => [
            'gallery' => [1 => 'blob:pending'],
            'hero_photo_url' => 'blob:pending',
        ]],
    ])->assertOk();

    expect($invitation->wedding->fresh()->extra_data)->toBe([
        'gallery' => ['/storage/first.webp', '/storage/second.webp'],
        'hero_photo_url' => '/storage/hero.webp',
    ]);
});

test('saving an existing invitation rejects a missing or mismatched revision', function (?int $revision) {
    $owner = User::factory()->create();
    $invitation = makeGuestManagementInvitation($owner, 'manual-revision-conflict');
    $invitation->update(['revision_id' => 5]);
    $payload = [
        'template_id' => $invitation->template_id,
        'invitation_id' => $invitation->id,
        'wedding' => ['groom_name' => 'Tuấn'],
    ];
    if ($revision !== null) {
        $payload['revision_id'] = $revision;
    }

    $this->actingAs($owner)->postJson(route('invitation.store'), $payload)
        ->assertUnprocessable()->assertJsonValidationErrors('revision_id');
    expect($invitation->fresh()->revision_id)->toBe(5);
    expect($invitation->wedding->fresh()->groom_name)->toBe('Minh');
})->with([null, 4, 6]);

test('saving an existing invitation advances its revision', function () {
    $owner = User::factory()->create();
    $invitation = makeGuestManagementInvitation($owner, 'manual-revision-current');
    $invitation->update(['revision_id' => 5]);

    $this->actingAs($owner)->postJson(route('invitation.store'), [
        'template_id' => $invitation->template_id,
        'invitation_id' => $invitation->id,
        'revision_id' => 5,
        'wedding' => ['groom_name' => 'Tuấn'],
    ])->assertOk()->assertJson(['success' => true]);

    expect($invitation->fresh()->revision_id)->toBe(6);
    expect($invitation->wedding->fresh()->groom_name)->toBe('Tuấn');
});

test('saving an invitation owned by someone else does not create another invitation', function () {
    $invitation = makeGuestManagementInvitation(User::factory()->create(), 'manual-other-owner');

    $this->actingAs(User::factory()->create())->postJson(route('invitation.store'), [
        'template_id' => $invitation->template_id,
        'invitation_id' => $invitation->id,
        'revision_id' => $invitation->fresh()->revision_id,
        'wedding' => ['groom_name' => 'Tuấn'],
    ])->assertNotFound();

    $this->assertDatabaseCount('invitations', 1);
    expect($invitation->wedding->fresh()->groom_name)->toBe('Minh');
});

test('manual saves preserve omitted album and timeline entries and clear explicit values', function () {
    $owner = User::factory()->create();
    $invitation = makeGuestManagementInvitation($owner, 'manual-partial-lists');
    $invitation->wedding->update([
        'cover_photo_url' => '/storage/cover.webp',
        'story' => 'Câu chuyện cũ',
        'extra_data' => [
            'gallery' => ['/storage/first.webp', '/storage/second.webp', '/storage/third.webp'],
            'timeline' => [
                ['time' => '08:00', 'title' => 'Đón khách'],
                ['time' => '09:00', 'title' => 'Lễ cưới'],
            ],
            'other' => 'Giữ lại',
        ],
    ]);

    $this->actingAs($owner)->postJson(route('invitation.store'), [
        'template_id' => $invitation->template_id,
        'invitation_id' => $invitation->id,
        'revision_id' => $invitation->fresh()->revision_id,
        'wedding' => [
            'story' => null,
            'cover_photo_url' => 'blob:pending',
            'extra_data' => [
                'gallery' => [1 => '/storage/new.webp', 2 => null],
                'timeline' => [1 => ['title' => 'Khai tiệc']],
            ],
        ],
    ])->assertOk();

    $wedding = $invitation->wedding->fresh();
    expect($wedding->story)->toBeNull()
        ->and($wedding->cover_photo_url)->toBe('/storage/cover.webp')
        ->and($wedding->extra_data)->toBe([
            'gallery' => ['/storage/first.webp', '/storage/new.webp', null],
            'timeline' => [
                ['time' => '08:00', 'title' => 'Đón khách'],
                ['time' => '09:00', 'title' => 'Khai tiệc'],
            ],
            'other' => 'Giữ lại',
        ]);

    $this->postJson(route('invitation.store'), [
        'template_id' => $invitation->template_id,
        'invitation_id' => $invitation->id,
        'revision_id' => $invitation->fresh()->revision_id,
        'wedding' => ['extra_data' => ['gallery' => [], 'timeline' => []]],
    ])->assertOk();

    expect($invitation->wedding->fresh()->extra_data)->toBe([
        'gallery' => [], 'timeline' => [], 'other' => 'Giữ lại',
    ]);
});

test('manual save publishes autosaved settings and discards the superseded config draft', function () {
    $owner = User::factory()->create();
    $invitation = makeGuestManagementInvitation($owner, 'manual-promotes-draft');
    $invitation->update([
        'custom_config' => ['palette' => 'cream', 'layout' => 'original'],
        'draft_config' => ['palette' => 'rose', 'sections_hidden' => ['dresscode']],
    ]);

    $this->actingAs($owner)->postJson(route('invitation.store'), [
        'template_id' => $invitation->template_id,
        'invitation_id' => $invitation->id,
        'revision_id' => $invitation->fresh()->revision_id,
        'wedding' => ['groom_name' => 'Tuấn'],
        'invitation' => ['custom_config' => ['palette' => 'sage']],
    ])->assertOk();

    $freshConfig = Arr::except($invitation->fresh()->custom_config, ['_previous_published_snapshot']);

    expect($freshConfig)->toBe([
        'palette' => 'sage', 'layout' => 'original', 'sections_hidden' => ['dresscode'],
    ])->and($invitation->fresh()->draft_config)->toBeNull();
});

test('an anonymous owner can save an invitation with the correct revision', function () {
    $invitation = makeGuestManagementInvitation(User::factory()->create(), 'manual-session-owner');

    $this->withSession(['owned_invitation_slugs' => [$invitation->slug]])
        ->postJson(route('invitation.store'), [
            'template_id' => $invitation->template_id,
            'invitation_id' => $invitation->id,
            'revision_id' => $invitation->fresh()->revision_id,
            'wedding' => ['groom_name' => 'Tuấn'],
        ])->assertOk()->assertJson(['success' => true]);

    expect($invitation->wedding->fresh()->groom_name)->toBe('Tuấn');
});

test('a manual save invalidates an older autosave revision', function () {
    $owner = User::factory()->create();
    $invitation = makeGuestManagementInvitation($owner, 'manual-invalidates-autosave');
    $invitation->update(['revision_id' => 5]);
    $this->actingAs($owner);

    $this->postJson(route('invitation.store'), [
        'template_id' => $invitation->template_id,
        'invitation_id' => $invitation->id,
        'revision_id' => 5,
        'wedding' => ['groom_name' => 'Tuấn'],
    ])->assertOk();
    $this->postJson(route('invitation.autosave'), [
        'invitation_id' => $invitation->id,
        'revision_id' => 5,
        'wedding_delta' => ['groom_name' => 'Minh'],
    ])->assertStatus(409);

    expect($invitation->wedding->fresh()->groom_name)->toBe('Tuấn');
});

test('shared wedding edits invalidate stale sibling invitation saves', function (string $saveMode) {
    $owner = User::factory()->create();
    $first = makeGuestManagementInvitation($owner, 'shared-revision-first');
    $sibling = Invitation::create([
        'wedding_id' => $first->wedding_id,
        'template_id' => $first->template_id,
        'slug' => 'shared-revision-sibling',
    ]);
    $staleRevision = $sibling->fresh()->revision_id;
    $this->actingAs($owner);

    $payload = [
        'invitation_id' => $first->id,
        'revision_id' => $first->fresh()->revision_id,
    ];
    if ($saveMode === 'manual') {
        $payload['template_id'] = $first->template_id;
        $payload['wedding'] = ['groom_name' => 'Tuấn'];
        $routeName = 'invitation.store';
    } else {
        $payload['wedding_delta'] = ['groom_name' => 'Tuấn'];
        $routeName = 'invitation.autosave';
    }
    $this->postJson(route($routeName), $payload)->assertOk();

    expect($sibling->fresh()->revision_id)->toBe($staleRevision + 1);
    $payload['invitation_id'] = $sibling->id;
    $payload['revision_id'] = $staleRevision;
    if ($saveMode === 'manual') {
        $payload['wedding'] = ['groom_name' => 'Minh'];
        $this->postJson(route($routeName), $payload)->assertUnprocessable()->assertJsonValidationErrors('revision_id');
    } else {
        $payload['wedding_delta'] = ['groom_name' => 'Minh'];
        $this->postJson(route($routeName), $payload)->assertConflict();
    }
    expect($first->wedding->fresh()->groom_name)->toBe('Tuấn');
})->with(['manual', 'autosave']);

test('config-only autosaves keep sibling wedding revisions valid', function () {
    $owner = User::factory()->create();
    $first = makeGuestManagementInvitation($owner, 'config-only-first');
    $sibling = Invitation::create([
        'wedding_id' => $first->wedding_id,
        'template_id' => $first->template_id,
        'slug' => 'config-only-sibling',
    ]);
    $revision = $sibling->fresh()->revision_id;

    $this->actingAs($owner)->postJson(route('invitation.autosave'), [
        'invitation_id' => $first->id,
        'revision_id' => $first->fresh()->revision_id,
        'draft_config' => ['palette' => 'sage'],
    ])->assertOk();

    expect($sibling->fresh()->revision_id)->toBe($revision);
});

test('studio saves reject malformed wedding values without changing stored data', function (string $mode, array|string $wedding, string $field) {
    $owner = User::factory()->create();
    $invitation = makeGuestManagementInvitation($owner, 'invalid-wedding-input');
    $revision = $invitation->fresh()->revision_id;
    $payload = ['invitation_id' => $invitation->id, 'revision_id' => $revision];
    $prefix = $mode === 'manual' ? 'wedding' : 'wedding_delta';
    $payload[$prefix] = $wedding;
    if ($mode === 'manual') {
        $payload['template_id'] = $invitation->template_id;
    }

    $this->actingAs($owner)->postJson(route($mode === 'manual' ? 'invitation.store' : 'invitation.autosave'), $payload)
        ->assertUnprocessable()->assertJsonValidationErrors($prefix.($field === '' ? '' : '.'.$field));

    expect($invitation->fresh()->revision_id)->toBe($revision)
        ->and($invitation->wedding->fresh()->groom_name)->toBe('Minh')
        ->and($invitation->wedding->fresh()->wedding_date->format('Y-m-d'))->toBe('2027-01-01');
})->with(['manual', 'autosave'])->with([
    'invalid shape' => ['invalid', ''],
    'empty date' => [['wedding_date' => ''], 'wedding_date'],
    'invalid date' => [['wedding_date' => '2027-02-31'], 'wedding_date'],
    'null groom' => [['groom_name' => null], 'groom_name'],
    'array bride' => [['bride_name' => ['invalid']], 'bride_name'],
    'array cover' => [['cover_photo_url' => ['invalid']], 'cover_photo_url'],
    'invalid gallery' => [['extra_data' => ['gallery' => 'invalid']], 'extra_data.gallery'],
    'invalid parent' => [['extra_data' => ['groom_father' => ['invalid']]], 'extra_data.groom_father'],
    'invalid timeline title' => [['extra_data' => ['timeline' => [['title' => ['invalid']]]]], 'extra_data.timeline.0.title'],
    'invalid timeline time' => [['extra_data' => ['timeline' => [['time' => ['invalid']]]]], 'extra_data.timeline.0.time'],
]);

test('partial legacy manual saves preserve unsubmitted required wedding fields', function () {
    $owner = User::factory()->create();
    $invitation = makeGuestManagementInvitation($owner, 'legacy-partial-save');

    $this->actingAs($owner)->postJson(route('invitation.store'), [
        'template_id' => $invitation->template_id,
        'invitation_id' => $invitation->id,
        'revision_id' => $invitation->fresh()->revision_id,
        'groom_name' => 'Tuấn',
    ])->assertOk();

    expect($invitation->wedding->fresh()->groom_name)->toBe('Tuấn')
        ->and($invitation->wedding->fresh()->bride_name)->toBe('An')
        ->and($invitation->wedding->fresh()->wedding_date->format('Y-m-d'))->toBe('2027-01-01');
});
