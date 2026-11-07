<?php

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingWish;
use Illuminate\Support\Facades\Event;

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
        'is_published' => true,
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

test('different guests on the same network can RSVP without overwriting each others wishes', function () {
    $invitation = makeRsvpTokenInvitation('rsvp-shared-network');
    $first = Guest::create(['wedding_id' => $invitation->wedding_id, 'name' => 'Khách một']);
    $second = Guest::create(['wedding_id' => $invitation->wedding_id, 'name' => 'Khách hai']);

    foreach ([$first, $second] as $guest) {
        $this->postJson(route('rsvp.submit', ['slug' => $invitation->slug, 'token' => $guest->guest_token]), [
            'guest_name' => $guest->name,
            'attending_status' => 'attending',
            'wishes' => 'Lời chúc từ '.$guest->name,
        ])->assertOk()->assertJson(['success' => true]);
        $this->assertDatabaseHas('guest_responses', ['wedding_id' => $invitation->wedding_id, 'guest_id' => $guest->id]);
        $this->assertDatabaseHas('wedding_wishes', ['wedding_id' => $invitation->wedding_id, 'name' => $guest->name, 'message' => 'Lời chúc từ '.$guest->name]);
    }

    $this->assertDatabaseCount('guest_responses', 2);
    $this->assertDatabaseCount('wedding_wishes', 2);
});

test('a guest cannot send a second RSVP using another IP address', function () {
    $invitation = makeRsvpTokenInvitation('rsvp-one-per-guest');
    $guest = Guest::create(['wedding_id' => $invitation->wedding_id, 'name' => 'Khách mời']);
    $url = route('rsvp.submit', ['slug' => $invitation->slug, 'token' => $guest->guest_token]);
    $payload = ['guest_name' => 'Khách mời', 'attending_status' => 'attending'];

    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.1'])->postJson($url, $payload)->assertOk();
    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.2'])->postJson($url, $payload)->assertStatus(422);
    $this->assertDatabaseCount('guest_responses', 1);
});

test('a general RSVP and a personalized RSVP do not block each other on a shared network', function () {
    $invitation = makeRsvpTokenInvitation('rsvp-shared-general');
    $guest = Guest::create(['wedding_id' => $invitation->wedding_id, 'name' => 'Khách mời']);
    $payload = ['guest_name' => 'Khách mời', 'attending_status' => 'attending'];

    $this->postJson(route('rsvp.submit', $invitation->slug), $payload)->assertOk();
    $this->postJson(route('rsvp.submit', ['slug' => $invitation->slug, 'token' => $guest->guest_token]), $payload)->assertOk();
    $this->postJson(route('rsvp.submit', $invitation->slug), $payload)->assertStatus(422);
    $this->assertDatabaseCount('guest_responses', 2);
});

test('wishes from the same network are recorded separately', function () {
    $invitation = makeRsvpTokenInvitation('wishes-shared-network');
    $url = route('invitation.wish.store', $invitation->slug);

    $this->postJson($url, ['name' => 'Khách một', 'message' => 'Lời chúc thứ nhất'])->assertCreated();
    $this->postJson($url, ['name' => 'Khách hai', 'message' => 'Lời chúc thứ hai'])->assertCreated();
    $this->assertDatabaseHas('wedding_wishes', ['wedding_id' => $invitation->wedding_id, 'name' => 'Khách một', 'message' => 'Lời chúc thứ nhất']);
    $this->assertDatabaseHas('wedding_wishes', ['wedding_id' => $invitation->wedding_id, 'name' => 'Khách hai', 'message' => 'Lời chúc thứ hai']);
});

test('the invitation only provides a response for the guest identified by its token', function () {
    $invitation = makeRsvpTokenInvitation('rsvp-view-shared-network');
    $first = Guest::create(['wedding_id' => $invitation->wedding_id, 'name' => 'Khách một']);
    $second = Guest::create(['wedding_id' => $invitation->wedding_id, 'name' => 'Khách hai']);
    $this->postJson(route('rsvp.submit', ['slug' => $invitation->slug, 'token' => $first->guest_token]), [
        'guest_name' => 'Khách một',
        'attending_status' => 'attending',
    ])->assertOk();

    $this->get(route('invitation.show', ['slug' => $invitation->slug, 'token' => $first->guest_token]))
        ->assertViewHas('currentRsvp', fn ($response) => $response?->guest_id === $first->id);
    $this->get(route('invitation.show', ['slug' => $invitation->slug, 'token' => $second->guest_token]))
        ->assertViewHas('currentRsvp', fn ($response) => $response === null);
    $this->get(route('invitation.show', $invitation->slug))
        ->assertViewHas('currentRsvp', fn ($response) => $response === null);
});

test('a token belonging to another wedding cannot be used to view a personalized invitation', function () {
    $invitation = makeRsvpTokenInvitation('rsvp-view-target');
    $other = makeRsvpTokenInvitation('rsvp-view-other');
    $guest = Guest::create(['wedding_id' => $other->wedding_id, 'name' => 'Khách khác']);

    $this->get(route('invitation.show', ['slug' => $invitation->slug, 'token' => $guest->guest_token]))->assertNotFound();
});

test('owner attendance totals include adults and children', function () {
    $invitation = makeRsvpTokenInvitation('rsvp-totals');
    $this->postJson(route('rsvp.submit', $invitation->slug), [
        'guest_name' => 'Khách mời',
        'attending_status' => 'attending',
        'adults_count' => 2,
        'children_count' => 3,
    ])->assertOk();

    $this->actingAs($invitation->wedding->user)
        ->getJson(route('invitation.rsvp.responses', $invitation->slug))
        ->assertOk()->assertJsonPath('stats.attending_guests', 5);
});

test('a failed wish save rolls back the RSVP', function () {
    $invitation = makeRsvpTokenInvitation('rsvp-transaction-rollback');
    $eventName = 'eloquent.creating: '.WeddingWish::class;
    Event::listen($eventName, fn () => throw new RuntimeException('Simulated wish save failure'));

    try {
        $this->postJson(route('rsvp.submit', $invitation->slug), [
            'guest_name' => 'Khách mời',
            'attending_status' => 'attending',
            'wishes' => 'Chúc hai bạn hạnh phúc',
        ])->assertInternalServerError();
    } finally {
        Event::forget($eventName);
    }

    $this->assertDatabaseCount('guest_responses', 0);
    $this->assertDatabaseCount('wedding_wishes', 0);
});

test('wishes stay within their own wedding when guests share a network', function () {
    $first = makeRsvpTokenInvitation('wishes-first-wedding');
    $second = makeRsvpTokenInvitation('wishes-second-wedding');

    foreach ([[$first, 'Lời chúc cho đôi thứ nhất'], [$second, 'Lời chúc cho đôi thứ hai']] as [$invitation, $message]) {
        $this->postJson(route('invitation.wish.store', $invitation->slug), [
            'name' => 'Khách mời', 'message' => $message,
        ])->assertCreated();
    }

    $this->getJson(route('invitation.wish.index', $first->slug))->assertOk()
        ->assertJsonCount(1, 'wishes')
        ->assertJsonPath('wishes.0.message', 'Lời chúc cho đôi thứ nhất');
    $this->getJson(route('invitation.wish.index', $second->slug))->assertOk()
        ->assertJsonCount(1, 'wishes')
        ->assertJsonPath('wishes.0.message', 'Lời chúc cho đôi thứ hai');
});

test('guest submissions reject inappropriate text without persisting it', function (string $routeName, array $payload, string $field) {
    $invitation = makeRsvpTokenInvitation('moderation-guest-submission');

    $this->postJson(route($routeName, $invitation->slug), $payload)
        ->assertUnprocessable()->assertJsonValidationErrors([$field]);

    $this->assertDatabaseCount('wedding_wishes', 0);
    $this->assertDatabaseCount('guest_responses', 0);
})->with([
    'wish message' => ['invitation.wish.store', ['name' => 'Khách mời', 'message' => 'Chúc hai bạn sớm ly dị'], 'message'],
    'rsvp guest name' => ['rsvp.submit', ['guest_name' => 'thằng mặt lồn', 'attending_status' => 'attending'], 'guest_name'],
    'rsvp wish' => ['rsvp.submit', ['guest_name' => 'Khách mời', 'attending_status' => 'attending', 'wishes' => 'Chúc hai bạn sớm ly dị'], 'wishes'],
]);

test('an owner cannot reply to a wish from another wedding', function () {
    $first = makeRsvpTokenInvitation('reply-first-wedding');
    $second = makeRsvpTokenInvitation('reply-second-wedding');
    $wish = $second->wedding->wishes()->create(['name' => 'Khách mời', 'message' => 'Chúc mừng hạnh phúc']);

    $this->actingAs($first->wedding->user)
        ->postJson(route('invitation.wish.reply', ['slug' => $first->slug, 'wishId' => $wish->id]), [
            'reply' => 'Cảm ơn bạn',
        ])->assertNotFound();

    expect($wish->fresh()->reply)->toBeNull();
});
