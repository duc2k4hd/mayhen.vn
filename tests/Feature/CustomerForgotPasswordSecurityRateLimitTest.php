<?php

use App\Mail\CustomerResetPasswordMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

beforeEach(function () {
    $this->customer = User::factory()->create([
        'email' => 'customer_limit_test@mayhen.vn',
        'role' => 'customer',
        'password' => bcrypt('OldPassword123!'),
        'last_password_reset_at' => null,
    ]);

    $this->admin = User::factory()->create([
        'email' => 'admin_limit_test@mayhen.vn',
        'role' => 'admin',
        'password' => bcrypt('AdminPassword123!'),
        'last_password_reset_at' => null,
    ]);

    $this->staff = User::factory()->create([
        'email' => 'staff_limit_test@mayhen.vn',
        'role' => 'staff',
        'password' => bcrypt('StaffPassword123!'),
        'last_password_reset_at' => null,
    ]);
});

test('first forgot password request for customer pushes email to queue and records token', function () {
    Mail::fake();

    $response = $this->post(route('password.email'), [
        'email' => $this->customer->email,
    ]);

    $response->assertSessionHas('status');
    Mail::assertQueued(CustomerResetPasswordMail::class, function ($mail) {
        return $mail->user->email === $this->customer->email;
    });

    $tokenRecord = DB::table('password_reset_tokens')->where('email', $this->customer->email)->first();
    expect($tokenRecord)->not->toBeNull();
});

test('customer cannot request another reset link when one is already in-flight and unused', function () {
    Mail::fake();

    // 1. Initial request creates in-flight token
    Password::broker()->createToken($this->customer);
    expect(DB::table('password_reset_tokens')->where('email', $this->customer->email)->exists())->toBeTrue();

    // 2. Second request should be blocked
    $response = $this->post(route('password.email'), [
        'email' => $this->customer->email,
    ]);

    $response->assertSessionHasErrors('email');
    $response->assertSessionHas('in_flight', true);

    // No additional email queued
    Mail::assertNothingQueued();

    // JSON response check
    $jsonResponse = $this->postJson(route('password.email'), [
        'email' => $this->customer->email,
    ]);

    $jsonResponse->assertStatus(422);
    $jsonResponse->assertJson([
        'success' => false,
        'in_flight' => true,
    ]);
});

test('customer resetting password updates last_password_reset_at timestamp', function () {
    $token = Password::broker()->createToken($this->customer);

    $response = $this->post(route('password.update'), [
        'token' => $token,
        'email' => $this->customer->email,
        'password' => 'NewPassword2026!',
        'password_confirmation' => 'NewPassword2026!',
    ]);

    $response->assertRedirect(route('login'));
    $response->assertSessionHas('success');

    $freshCustomer = $this->customer->fresh();
    expect($freshCustomer->last_password_reset_at)->not->toBeNull();
    expect($freshCustomer->last_password_reset_at->diffInMinutes(now()))->toBeLessThan(2);

    // In-flight token is deleted upon successful reset
    expect(DB::table('password_reset_tokens')->where('email', $this->customer->email)->exists())->toBeFalse();
});

test('customer cannot request password reset within 7 days of recent password reset', function () {
    Mail::fake();

    // Customer recently changed password 2 days ago
    $this->customer->update([
        'last_password_reset_at' => Carbon::now()->subDays(2),
    ]);

    $response = $this->post(route('password.email'), [
        'email' => $this->customer->email,
    ]);

    $response->assertSessionHasErrors('email');
    $response->assertSessionHas('cooldown', true);
    Mail::assertNothingQueued();

    // JSON response check
    $jsonResponse = $this->postJson(route('password.email'), [
        'email' => $this->customer->email,
    ]);

    $jsonResponse->assertStatus(429);
    $jsonResponse->assertJson([
        'success' => false,
        'cooldown' => true,
    ]);
    expect($jsonResponse->json('message'))->toContain('trong vòng 7 ngày');
});

test('customer can request password reset after 7 day cooldown has expired', function () {
    Mail::fake();

    // Customer changed password 8 days ago (cooldown passed)
    $this->customer->update([
        'last_password_reset_at' => Carbon::now()->subDays(8),
    ]);

    $response = $this->post(route('password.email'), [
        'email' => $this->customer->email,
    ]);

    $response->assertSessionHas('status');
    Mail::assertQueued(CustomerResetPasswordMail::class);
});

test('admin and staff are exempt from in-flight and 7-day cooldown restrictions', function () {
    Mail::fake();

    // Setup: Admin and Staff have in-flight token AND recently changed password 1 hour ago
    $this->admin->update(['last_password_reset_at' => Carbon::now()->subHour()]);
    Password::broker()->createToken($this->admin);

    $this->staff->update(['last_password_reset_at' => Carbon::now()->subHour()]);
    Password::broker()->createToken($this->staff);

    // 1. Admin can request password reset anytime
    $adminResponse = $this->post(route('password.email'), [
        'email' => $this->admin->email,
    ]);
    $adminResponse->assertSessionHas('status');
    $adminResponse->assertSessionMissing('errors');

    // 2. Staff can request password reset anytime
    $staffResponse = $this->post(route('password.email'), [
        'email' => $this->staff->email,
    ]);
    $staffResponse->assertSessionHas('status');
    $staffResponse->assertSessionMissing('errors');

    Mail::assertQueued(CustomerResetPasswordMail::class, 2);
});
