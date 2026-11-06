<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;

function makeRsvpTokenInvitation(string $slug): Invitation
{
    $template = InvitationTemplate::create([
        'slug' => 'rsvp-template-'.$slug,
        'name' => 'Mẫu kiểm thử RSVP',
        'thumbnail_url' => '/test-thumbnail.webp',
    ]);

    $wedding = Wedding::create([
        'user_id' => User::factory()->create()->id,
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

test('a valid guest token links an RSVP to the same wedding and guest', function () {
    $invitation = makeRsvpTokenInvitation('rsvp-valid');
    $guest = Guest::create(['wedding_id' => $invitation->wedding_id, 'name' => 'Khách mời']);

    $this->postJson(route('rsvp.submit', ['slug' => $invitation->slug, 'token' => $guest->guest_token]), [
        'guest_name' => 'Khách mời',
        'attending_status' => 'attending',
    ])->assertOk();

    $this->assertDatabaseHas('guest_responses', [
        'wedding_id' => $invitation->wedding_id,
        'guest_id' => $guest->id,
        'guest_name' => 'Khách mời',
    ]);
});

test('a token from another wedding cannot link or create an RSVP', function () {
    $invitation = makeRsvpTokenInvitation('rsvp-target');
    $otherInvitation = makeRsvpTokenInvitation('rsvp-other');
    $otherGuest = Guest::create(['wedding_id' => $otherInvitation->wedding_id, 'name' => 'Khách khác']);

    $this->postJson(route('rsvp.submit', ['slug' => $invitation->slug, 'token' => $otherGuest->guest_token]), [
        'guest_name' => 'Khách khác',
        'attending_status' => 'attending',
    ])->assertNotFound();

    $this->assertDatabaseCount('guest_responses', 0);
});

test('an unknown guest token cannot create an RSVP', function () {
    $invitation = makeRsvpTokenInvitation('rsvp-unknown');

    $this->postJson(route('rsvp.submit', ['slug' => $invitation->slug, 'token' => 'unknown-token']), [
        'guest_name' => 'Khách mời',
        'attending_status' => 'attending',
    ])->assertNotFound();

    $this->assertDatabaseCount('guest_responses', 0);
});

test('a malformed guest token cannot create an RSVP', function () {
    $invitation = makeRsvpTokenInvitation('rsvp-malformed');

    $this->postJson(route('rsvp.submit', $invitation->slug).'?token[]=invalid', [
        'guest_name' => 'Khách mời',
        'attending_status' => 'attending',
    ])->assertNotFound();

    $this->assertDatabaseCount('guest_responses', 0);
});

test('an RSVP without a token remains available as a general response', function () {
    $invitation = makeRsvpTokenInvitation('rsvp-general');

    $this->postJson(route('rsvp.submit', $invitation->slug), [
        'guest_name' => 'Khách mời',
        'attending_status' => 'attending',
    ])->assertOk();

    $this->assertDatabaseHas('guest_responses', [
        'wedding_id' => $invitation->wedding_id,
        'guest_id' => null,
        'guest_name' => 'Khách mời',
    ]);
});

test('a personalized invitation includes its token in the RSVP endpoint', function () {
    $invitation = makeRsvpTokenInvitation('rsvp-link');
    $guest = Guest::create(['wedding_id' => $invitation->wedding_id, 'name' => 'Khách mời']);

    $this->get(route('invitation.show', ['slug' => $invitation->slug, 'token' => $guest->guest_token]))
        ->assertOk()
        ->assertViewHas('rsvpSubmitUrl', route('rsvp.submit', [
            'slug' => $invitation->slug,
            'token' => $guest->guest_token,
        ]));
});

test('a personalized template renders the correct RSVP endpoint', function (string $templateSlug) {
    $invitation = makeRsvpTokenInvitation('rsvp-template-link');
    $invitation->template->update(['slug' => $templateSlug]);
    $guest = Guest::create(['wedding_id' => $invitation->wedding_id, 'name' => 'Khách mời']);

    $this->get(route('invitation.show', ['slug' => $invitation->slug, 'token' => $guest->guest_token]))
        ->assertOk()
        ->assertSee(route('rsvp.submit', ['slug' => $invitation->slug, 'token' => $guest->guest_token]));
})->with(['ngay-tinh-yeu-dom-hoa', 'tron-kiep-nay-ben-nhau', 'giua-muon-ngan-vi-sao', 'hanh-phuc-mang-ten-chung-minh']);

test('the template renders persisted wishes and states they are public', function (string $templateSlug) {
    $invitation = makeRsvpTokenInvitation('rsvp-starry-wishes');
    $invitation->template->update(['slug' => $templateSlug]);
    $invitation->wedding->wishes()->create([
        'name' => 'Khách kiểm thử',
        'message' => 'Chúc hai bạn hạnh phúc',
        'is_approved' => true,
        'ip_address' => '192.0.2.15',
    ]);

    $this->get(route('invitation.show', $invitation->slug))
        ->assertOk()
        ->assertSee('Khách kiểm thử')
        ->assertSee('Chúc hai bạn hạnh phúc')
        ->assertSee('Lời chúc sẽ hiển thị công khai trên thiệp.')
        ->assertDontSee('Chỉ cô dâu, chú rể');
})->with(['giua-muon-ngan-vi-sao', 'hanh-phuc-mang-ten-chung-minh']);

test('an RSVP stores the submitted adult and child counts', function () {
    $invitation = makeRsvpTokenInvitation('rsvp-counts');
    $guest = Guest::create(['wedding_id' => $invitation->wedding_id, 'name' => 'Khách mời']);

    $this->postJson(route('rsvp.submit', ['slug' => $invitation->slug, 'token' => $guest->guest_token]), [
        'guest_name' => 'Khách mời',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 1,
        'wishes' => 'Chúc hai bạn hạnh phúc',
    ])->assertOk()->assertJson(['success' => true]);

    $this->assertDatabaseHas('guest_responses', [
        'wedding_id' => $invitation->wedding_id,
        'guest_id' => $guest->id,
        'adults_count' => 2,
        'children_count' => 1,
    ]);
});
