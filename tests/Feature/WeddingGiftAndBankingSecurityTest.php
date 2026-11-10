<?php

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => Hash::make('SecretPassword123!'),
    ]);

    $this->template = InvitationTemplate::firstOrCreate(
        ['slug' => 'test-gift-template'],
        [
            'name' => 'Test Gift Template',
            'description' => 'Template test for gifts and QR',
            'category' => 'modern',
            'blade_view' => 'client.invitations.giua-muon-ngan-vi-sao',
            'thumbnail_url' => '/assets/img/demo.jpg',
            'is_active' => true,
        ]
    );

    $this->wedding = Wedding::create([
        'user_id' => $this->user->id,
        'groom_name' => 'Nguyễn Văn Minh',
        'bride_name' => 'Trần Thị Mai',
        'wedding_date' => now()->addDays(15)->toDateString(),
        'bank_name' => 'Vietcombank',
        'bank_account_number' => '0123456789',
        'bank_account_name' => 'NGUYEN VAN MINH',
        'extra_data' => [
            'is_gift_qr_enabled' => true,
        ],
    ]);

    $this->invitation = Invitation::create([
        'wedding_id' => $this->wedding->id,
        'template_id' => $this->template->id,
        'slug' => 'dam-cuoi-minh-mai-'.uniqid(),
        'title' => 'Thiệp Cưới Minh & Mai',
        'version_type' => 'free',
        'is_published' => true,
        'published_at' => now(),
    ]);
});

test('owner can toggle gift QR visibility and audit log is recorded', function () {
    expect($this->wedding->isGiftQrEnabled())->toBeTrue();

    // Tắt QR
    $response = $this->actingAs($this->user)
        ->post(route('account.gifts.toggle_qr', $this->invitation->id));

    $response->assertRedirect();
    expect($this->wedding->fresh()->isGiftQrEnabled())->toBeFalse();

    // Kiểm tra Audit Log
    $logs = AuditLogger::getRecentLogs(10, 'GIFT_QR_VISIBILITY_TOGGLED');
    expect(count($logs))->toBeGreaterThanOrEqual(1);

    // Bật lại QR
    $this->actingAs($this->user)
        ->post(route('account.gifts.toggle_qr', $this->invitation->id))
        ->assertRedirect();

    expect($this->wedding->fresh()->isGiftQrEnabled())->toBeTrue();
});

test('owner can update multi bank accounts when providing correct current password', function () {
    $accounts = [
        [
            'label' => 'Nhà Trai (Chú Rể Minh)',
            'bank_name' => 'VietinBank',
            'bank_account_number' => '102888999999',
            'account_holder' => 'NGUYEN VAN MINH',
        ],
        [
            'label' => 'Nhà Gái (Cô Dâu Mai)',
            'bank_name' => 'MB Bank',
            'bank_account_number' => '999988887777',
            'account_holder' => 'TRAN THI MAI',
        ],
    ];

    $response = $this->actingAs($this->user)
        ->post(route('account.gifts.update_banking', $this->invitation->id), [
            'current_password' => 'SecretPassword123!',
            'is_gift_qr_enabled' => 1,
            'accounts' => $accounts,
        ]);

    $response->assertRedirect()->assertSessionHas('success');

    $freshWedding = $this->wedding->fresh();
    $savedAccounts = $freshWedding->getBankAccounts();

    expect(count($savedAccounts))->toBe(2);
    expect($savedAccounts[0]['bank_name'])->toBe('VietinBank');
    expect($savedAccounts[0]['bank_account_number'])->toBe('102888999999');
    expect($savedAccounts[1]['bank_name'])->toBe('MB Bank');

    // Kiểm tra Audit Log ghi lại hành động nhạy cảm
    $logs = AuditLogger::getRecentLogs(10, 'BANK_ACCOUNT_UPDATED');
    expect(count($logs))->toBeGreaterThanOrEqual(1);
});

test('update bank accounts fails when current password is wrong or missing', function () {
    $response = $this->actingAs($this->user)
        ->post(route('account.gifts.update_banking', $this->invitation->id), [
            'current_password' => 'WrongPassword999!',
            'is_gift_qr_enabled' => 1,
            'accounts' => [
                [
                    'label' => 'Nhà Trai',
                    'bank_name' => 'Vietcombank',
                    'bank_account_number' => '123456',
                    'account_holder' => 'HACKER',
                ],
            ],
        ]);

    $response->assertSessionHasErrors('current_password');

    // Dữ liệu ngân hàng không bị thay đổi
    expect($this->wedding->fresh()->bank_account_number)->toBe('0123456789');
});

test('wedding bank account masking protects PII numbers according to least privilege', function () {
    expect(Wedding::maskAccountNumber('0123456789'))->toBe('012****789');
    expect(Wedding::maskAccountNumber('123456'))->toBe('12**56');
    expect(Wedding::maskAccountNumber('1234'))->toBe('****');
    expect(Wedding::maskAccountNumber(''))->toBe('—');
    expect(Wedding::maskAccountNumber(null))->toBe('—');
});

test('stranger cannot toggle QR or update banking settings of another wedding', function () {
    $stranger = User::factory()->create([
        'password' => Hash::make('StrangerPass123!'),
    ]);

    $this->actingAs($stranger)
        ->post(route('account.gifts.toggle_qr', $this->invitation->id))
        ->assertStatus(404);

    $this->actingAs($stranger)
        ->post(route('account.gifts.update_banking', $this->invitation->id), [
            'current_password' => 'StrangerPass123!',
            'accounts' => [
                [
                    'label' => 'Hacker Account',
                    'bank_name' => 'Agribank',
                    'bank_account_number' => '999999999',
                    'account_holder' => 'HACKER',
                ],
            ],
        ])
        ->assertStatus(404);
});
