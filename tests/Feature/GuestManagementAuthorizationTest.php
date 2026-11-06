<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;

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
