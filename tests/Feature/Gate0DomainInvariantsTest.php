<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\Order;
use App\Models\Plan;
use App\Models\User;
use App\Models\Wedding;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Gate0DomainInvariantsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private Wedding $wedding;

    private Invitation $invitation;

    private InvitationTemplate $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = InvitationTemplate::create([
            'name' => 'Celestial Romance',
            'slug' => 'celestial-romance',
            'thumbnail_url' => '/assets/templates/celestial.webp',
            'category' => 'luxury',
            'is_active' => true,
        ]);

        $this->owner = User::factory()->create();
        $this->wedding = Wedding::create([
            'user_id' => $this->owner->id,
            'groom_name' => 'Hoàng Long',
            'bride_name' => 'Ngọc Mai',
            'wedding_date' => Carbon::now()->addMonths(2),
        ]);

        $this->invitation = Invitation::create([
            'wedding_id' => $this->wedding->id,
            'template_id' => $this->template->id,
            'slug' => 'long-mai-wedding',
            'is_published' => true,
            'expires_at' => Carbon::now()->addYear(),
            'custom_config' => [],
        ]);
    }

    public function test_photo_drop_blocks_unauthorized_guests_when_invitation_is_locked(): void
    {
        $this->invitation->update([
            'custom_config' => ['is_locked' => true, 'lock_reason' => 'Bị khóa kiểm tra'],
        ]);

        $response = $this->get("/photo-drop/{$this->invitation->slug}");
        $response->assertStatus(200);
        $response->assertViewIs('client.pages.invitation-locked');

        Storage::fake('public');
        $file = UploadedFile::fake()->image('wedding_photo.jpg');

        $uploadResponse = $this->postJson("/api/photo-drop/{$this->invitation->slug}/upload", [
            'uploader_name' => 'Khách Vãng Lai',
            'photos' => [$file],
        ]);
        $uploadResponse->assertStatus(403);
    }

    public function test_photo_drop_blocks_unauthorized_guests_when_invitation_is_unpublished(): void
    {
        $this->invitation->update(['is_published' => false]);

        $response = $this->get("/photo-drop/{$this->invitation->slug}");
        $response->assertStatus(404);

        Storage::fake('public');
        $file = UploadedFile::fake()->image('wedding_photo.jpg');

        $uploadResponse = $this->postJson("/api/photo-drop/{$this->invitation->slug}/upload", [
            'uploader_name' => 'Khách Vãng Lai',
            'photos' => [$file],
        ]);
        $uploadResponse->assertStatus(403);
    }

    public function test_photo_drop_blocks_unauthorized_guests_when_invitation_is_expired(): void
    {
        $this->invitation->update(['expires_at' => Carbon::now()->subDay()]);

        $response = $this->get("/photo-drop/{$this->invitation->slug}");
        $response->assertStatus(200);
        $response->assertViewIs('client.pages.invitation-expired');

        Storage::fake('public');
        $file = UploadedFile::fake()->image('wedding_photo.jpg');

        $uploadResponse = $this->postJson("/api/photo-drop/{$this->invitation->slug}/upload", [
            'uploader_name' => 'Khách Vãng Lai',
            'photos' => [$file],
        ]);
        $uploadResponse->assertStatus(403);
    }

    public function test_time_capsule_blocks_guests_when_invitation_is_locked_unpublished_or_expired(): void
    {
        // 1. Locked
        $this->invitation->update([
            'custom_config' => ['is_locked' => true],
            'is_published' => true,
            'expires_at' => Carbon::now()->addMonths(6),
        ]);
        $resLocked = $this->get("/thiep/{$this->invitation->slug}/hop-thoi-gian");
        $resLocked->assertStatus(200);
        $resLocked->assertViewIs('client.pages.invitation-locked');

        // 2. Unpublished
        $this->invitation->update([
            'custom_config' => [],
            'is_published' => false,
        ]);
        $resUnpublished = $this->get("/thiep/{$this->invitation->slug}/hop-thoi-gian");
        $resUnpublished->assertStatus(404);

        // 3. Expired
        $this->invitation->update([
            'is_published' => true,
            'expires_at' => Carbon::now()->subDay(),
        ]);
        $resExpired = $this->get("/thiep/{$this->invitation->slug}/hop-thoi-gian");
        $resExpired->assertStatus(200);
        $resExpired->assertViewIs('client.pages.invitation-expired');
    }

    public function test_memory_vault_blocks_guests_when_invitation_is_locked_unpublished_or_expired(): void
    {
        // 1. Locked
        $this->invitation->update([
            'custom_config' => [
                'is_locked' => true,
                'memory_vault' => ['enabled' => true],
            ],
            'is_published' => true,
            'expires_at' => Carbon::now()->addMonths(6),
        ]);
        $resLocked = $this->get("/ky-niem/{$this->invitation->slug}");
        $resLocked->assertStatus(200);
        $resLocked->assertViewIs('client.pages.invitation-locked');

        // 2. Unpublished
        $this->invitation->update([
            'custom_config' => [
                'memory_vault' => ['enabled' => true],
            ],
            'is_published' => false,
        ]);
        $resUnpublished = $this->get("/ky-niem/{$this->invitation->slug}");
        $resUnpublished->assertStatus(200);
        $resUnpublished->assertViewIs('client.pages.invitation-unpublished');

        // 3. Expired
        $this->invitation->update([
            'is_published' => true,
            'expires_at' => Carbon::now()->subDay(),
        ]);
        $resExpired = $this->get("/ky-niem/{$this->invitation->slug}");
        $resExpired->assertStatus(200);
        $resExpired->assertViewIs('client.pages.invitation-expired');
    }

    public function test_payment_webhook_idempotency_prevents_duplicate_expiry_extension(): void
    {
        $secret = 'test-webhook-secret-key-12345';
        config([
            'services.sepay.webhook_secret' => $secret,
            'services.sepay.bank_account' => '1234567890',
        ]);

        $plan = Plan::create(['slug' => 'pro', 'name' => 'Gói Pro', 'price' => 499000]);

        $order = Order::create([
            'user_id' => $this->owner->id,
            'invitation_id' => $this->invitation->id,
            'plan_id' => $plan->id,
            'order_code' => 'MHGATE0TEST1',
            'amount' => 499000,
            'status' => 'pending',
            'payment_method' => 'sepay',
        ]);

        $payload = [
            'id' => 999888,
            'gateway' => 'Vietcombank',
            'transactionDate' => '2026-11-09 20:00:00',
            'accountNumber' => '1234567890',
            'code' => null,
            'content' => 'SEPAY MHGATE0TEST1',
            'transferType' => 'in',
            'transferAmount' => 499000,
            'accumulated' => 499000,
            'referenceCode' => 'MBVCB.999888',
            'description' => 'SEPAY MHGATE0TEST1',
        ];

        $rawBody = json_encode($payload);
        $timestamp = (string) now()->timestamp;
        $signature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret);

        $headers = [
            'X-SePay-Timestamp' => $timestamp,
            'X-SePay-Signature' => $signature,
        ];

        // First webhook call: Activates order and extends expiry
        $res1 = $this->postJson('/api/payments/sepay/webhook', $payload, $headers);

        $res1->assertStatus(200);
        $res1->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals('paid', $order->status);
        $this->invitation->refresh();
        $initialExpiry = $this->invitation->expires_at->toIso8601String();

        // Second webhook call with SAME transaction ID: must be idempotent and NOT change expiry
        $timestamp2 = (string) now()->timestamp;
        $signature2 = 'sha256='.hash_hmac('sha256', $timestamp2.'.'.$rawBody, $secret);
        $headers2 = [
            'X-SePay-Timestamp' => $timestamp2,
            'X-SePay-Signature' => $signature2,
        ];

        $res2 = $this->postJson('/api/payments/sepay/webhook', $payload, $headers2);

        $res2->assertStatus(200);
        $res2->assertJson(['success' => true, 'message' => 'Order already processed (idempotent)']);

        $this->invitation->refresh();
        $this->assertEquals($initialExpiry, $this->invitation->expires_at->toIso8601String());
    }

    public function test_cancelled_order_transitions_to_paid_when_late_payment_arrives(): void
    {
        $secret = 'test-webhook-secret-key-12345';
        config([
            'services.sepay.webhook_secret' => $secret,
            'services.sepay.bank_account' => '1234567890',
        ]);

        $plan = Plan::create(['slug' => 'plus', 'name' => 'Gói Plus', 'price' => 299000]);

        $order = Order::create([
            'user_id' => $this->owner->id,
            'invitation_id' => $this->invitation->id,
            'plan_id' => $plan->id,
            'order_code' => 'MHGATE0CAN01',
            'amount' => 299000,
            'status' => 'cancelled',
            'payment_method' => 'sepay',
        ]);

        $payload = [
            'id' => 999889,
            'gateway' => 'Vietcombank',
            'transactionDate' => '2026-11-09 20:05:00',
            'accountNumber' => '1234567890',
            'code' => null,
            'content' => 'SEPAY MHGATE0CAN01',
            'transferType' => 'in',
            'transferAmount' => 299000,
            'accumulated' => 299000,
            'referenceCode' => 'MBVCB.999889',
            'description' => 'SEPAY MHGATE0CAN01',
        ];

        $rawBody = json_encode($payload);
        $timestamp = (string) now()->timestamp;
        $signature = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret);

        $headers = [
            'X-SePay-Timestamp' => $timestamp,
            'X-SePay-Signature' => $signature,
        ];

        $response = $this->postJson('/api/payments/sepay/webhook', $payload, $headers);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals('paid', $order->status);
    }
}
