<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use App\Services\InvitationTemplateService;
use Database\Seeders\InvitationTemplateSeeder;

beforeEach(function () {
    $this->seed(InvitationTemplateSeeder::class);

    $this->plusPlan = Plan::firstOrCreate(
        ['slug' => 'plus'],
        [
            'name' => 'Gói Plus',
            'price' => 99000,
            'duration_days' => 365,
            'max_invitations' => 3,
            'features' => ['AI Curated'],
            'is_active' => true,
        ]
    );

    $this->proPlan = Plan::firstOrCreate(
        ['slug' => 'pro'],
        [
            'name' => 'Gói Pro VIP',
            'price' => 199000,
            'duration_days' => 730,
            'max_invitations' => 10,
            'features' => ['All Pro Features'],
            'is_active' => true,
        ]
    );
});

function grantPaidPlan(User $user, Plan $plan, ?Invitation $invitation = null): Order
{
    return Order::create([
        'order_code' => 'MH'.strtoupper(substr(md5(uniqid('', true)), 0, 10)),
        'user_id' => $user->id,
        'plan_id' => $plan->id,
        'invitation_id' => $invitation?->id,
        'amount' => $plan->price,
        'status' => 'paid',
        'payment_method' => 'vietqr',
        'paid_at' => now(),
    ]);
}

it('blocks unauthenticated guests from accessing AI Curated wizard, generate, and all curated APIs', function () {
    $this->get(route('curated.create'))->assertRedirect(route('login'));
    $this->post(route('curated.generate'), [
        'groom_name' => 'Minh Tuấn',
        'bride_name' => 'Thu Hà',
        'style' => 'romantic',
        'palette' => 'ruby',
    ])->assertRedirect(route('login'));

    $this->postJson(route('curated.generate'), [
        'groom_name' => 'Minh Tuấn',
        'bride_name' => 'Thu Hà',
        'style' => 'romantic',
        'palette' => 'ruby',
    ])->assertStatus(401);

    $this->postJson(route('curated.remix', ['id' => 1]))->assertStatus(401);
    $this->postJson(route('curated.customize', ['id' => 1]), ['palette' => 'ruby'])->assertStatus(401);
    $this->postJson(route('curated.regenerate_copy', ['id' => 1]))->assertStatus(401);
    $this->postJson(route('curated.switch_variant'), ['invitation_id' => 1, 'section' => 'hero'])->assertStatus(401);
});

it('shows Plus upgrade gate to logged-in member without Plus plan and blocks Curated generate/API with 403', function () {
    $member = User::factory()->create(['role' => 'customer']);

    $this->actingAs($member)
        ->get(route('curated.create'))
        ->assertOk()
        ->assertSee('Nâng cấp Gói Plus để mở khóa Tạo Thiệp Tự Động AI');

    $this->actingAs($member)
        ->post(route('curated.generate'), [
            'groom_name' => 'Minh Tuấn',
            'bride_name' => 'Thu Hà',
            'style' => 'romantic',
            'palette' => 'ruby',
        ])
        ->assertRedirect(route('checkout.show', 'plus'));

    $this->actingAs($member)
        ->postJson(route('curated.generate'), [
            'groom_name' => 'Minh Tuấn',
            'bride_name' => 'Thu Hà',
            'style' => 'romantic',
            'palette' => 'ruby',
        ])
        ->assertStatus(403)
        ->assertJsonPath('code', 'PLUS_PLAN_REQUIRED');
});

it('allows Plus user to use Curated AI but blocks selecting Pro templates via customize API', function () {
    $plusUser = User::factory()->create(['role' => 'customer']);
    grantPaidPlan($plusUser, $this->plusPlan);

    $proTemplate = InvitationTemplate::where('is_active', true)
        ->where('slug', '!=', 'mayhen-curated')
        ->get()
        ->first(fn (InvitationTemplate $t) => $t->tier === 'pro');
    expect($proTemplate)->not->toBeNull();

    $res = $this->actingAs($plusUser)->postJson(route('curated.generate'), [
        'groom_name' => 'Hoàng Minh',
        'bride_name' => 'Ngọc Anh',
        'style' => 'romantic',
        'palette' => 'ruby',
    ]);
    $res->assertOk()->assertJsonPath('success', true);

    $invitation = Invitation::where('slug', $res->json('slug'))->with('template')->firstOrFail();
    expect($invitation->template->tier)->not->toBe('pro');

    // Plus user cố tình gửi slug mẫu Pro qua API customize -> bị chặn 403
    $this->actingAs($plusUser)
        ->postJson(route('curated.customize', ['id' => $invitation->id]), [
            'template_slug' => $proTemplate->slug,
        ])
        ->assertStatus(403)
        ->assertJsonPath('code', 'TEMPLATE_TIER_LOCKED');

    // Admin khóa thiệp -> mọi API curated bị chặn 403
    $cfg = $invitation->custom_config ?? [];
    $cfg['is_locked'] = true;
    $invitation->update(['custom_config' => $cfg]);

    $this->actingAs($plusUser)
        ->postJson(route('curated.remix', ['id' => $invitation->id]))
        ->assertStatus(403);
});

it('saves unpaid Plus/Pro template in Studio as draft only and blocks public viewing or publishing until paid', function () {
    $member = User::factory()->create(['role' => 'customer']);
    $proTemplate = InvitationTemplate::where('is_active', true)
        ->where('slug', '!=', 'mayhen-curated')
        ->get()
        ->first(fn (InvitationTemplate $t) => $t->tier === 'pro');
    expect($proTemplate)->not->toBeNull();

    $invitation = app(InvitationTemplateService::class)->saveInvitation($member, $proTemplate->id, [
        'wedding' => [
            'groom_name' => 'Quốc Bảo',
            'bride_name' => 'Lan Chi',
            'wedding_date' => now()->addMonths(2)->format('Y-m-d'),
        ],
    ]);

    expect($invitation->is_published)->toBeFalse();

    // Chủ sở hữu vẫn xem trước được bản nháp
    $this->actingAs($member)
        ->get(route('invitation.show', $invitation->slug))
        ->assertOk()
        ->assertSee('Quốc Bảo');

    // Khách vãng lai không thể xem công khai khi chưa thanh toán
    auth()->logout();
    $this->get(route('invitation.show', $invitation->slug))
        ->assertOk()
        ->assertViewIs('client.pages.invitation-unpublished');

    // Chủ sở hữu cố gắng bật Xuất bản trong Account khi chưa thanh toán -> bị chặn
    $this->actingAs($member)
        ->post(route('account.invitations.toggle_publish', $invitation->id))
        ->assertSessionHas('error');
    expect($invitation->fresh()->is_published)->toBeFalse();

    // Sau khi thanh toán gói Pro -> xuất bản thành công và khách ngoài xem được
    grantPaidPlan($member, $this->proPlan, $invitation);
    $this->actingAs($member)
        ->post(route('account.invitations.toggle_publish', $invitation->id))
        ->assertSessionHas('success');
    expect($invitation->fresh()->is_published)->toBeTrue();

    auth()->logout();
    $this->get(route('invitation.show', $invitation->slug))
        ->assertOk()
        ->assertSee('Quốc Bảo');
});

it('strips protected system keys from client custom_config to prevent mass-assignment privilege escalation', function () {
    $member = User::factory()->create(['role' => 'customer']);
    $freeTemplate = InvitationTemplate::where('is_active', true)
        ->get()
        ->first(fn (InvitationTemplate $t) => $t->tier === 'free');
    expect($freeTemplate)->not->toBeNull();

    $invitation = app(InvitationTemplateService::class)->saveInvitation($member, $freeTemplate->id, [
        'wedding' => [
            'groom_name' => 'Tuấn Kiệt',
            'bride_name' => 'Mai Phương',
            'wedding_date' => now()->addMonths(2)->format('Y-m-d'),
        ],
        'invitation' => [
            'custom_config' => [
                'theme_color' => '#85182A',
                'is_locked' => true,
                'entitlement_overrides' => ['custom_domain' => true, 'remove_branding' => true],
                'admin_notes' => 'spoofed',
            ],
        ],
    ]);

    $savedConfig = $invitation->fresh()->custom_config;
    expect($savedConfig['theme_color'] ?? null)->toBe('#85182A')
        ->and($savedConfig)->not->toHaveKey('is_locked')
        ->and($savedConfig)->not->toHaveKey('entitlement_overrides')
        ->and($savedConfig)->not->toHaveKey('admin_notes');
});

it('requires authentication on AI Copywriter & AI Story endpoints and blocks wedding_id IDOR', function () {
    $this->postJson(route('invitation.aiStory'), ['groom_name' => 'A', 'bride_name' => 'B'])
        ->assertStatus(401);
    $this->postJson(route('api.ai.invitations'), ['groom_name' => 'A', 'bride_name' => 'B'])
        ->assertStatus(401);

    $owner = User::factory()->create(['role' => 'customer']);
    $attacker = User::factory()->create(['role' => 'customer']);
    $ownersWedding = Wedding::create([
        'user_id' => $owner->id,
        'groom_name' => 'Chủ Thiệp',
        'bride_name' => 'Cô Dâu',
        'wedding_date' => now()->addMonths(2)->toDateString(),
    ]);

    $this->actingAs($attacker)
        ->postJson(route('api.ai.invitations'), [
            'groom_name' => 'Hacker',
            'bride_name' => 'Test',
            'wedding_id' => $ownersWedding->id,
        ])
        ->assertStatus(403);
});
