<?php

use App\Models\AiUsageLog;
use App\Models\User;
use App\Models\Wedding;
use App\Services\AiQualityAndCostObservatoryService;
use Carbon\Carbon;

beforeEach(function () {
    $this->superAdmin = User::factory()->create([
        'email' => 'admin_ai_test@mayhen.vn',
        'role' => 'admin',
    ]);

    $this->manager = User::factory()->create([
        'email' => 'manager_ai_test@mayhen.vn',
        'role' => 'manager',
    ]);

    $this->staff = User::factory()->create([
        'email' => 'staff_ai_test@mayhen.vn',
        'role' => 'staff',
    ]);

    $this->customer = User::factory()->create([
        'email' => 'customer_ai_test@mayhen.vn',
        'role' => 'customer',
    ]);

    $this->wedding = Wedding::create([
        'user_id' => $this->customer->id,
        'groom_name' => 'Minh Tuấn',
        'bride_name' => 'Thu Hà',
        'wedding_date' => Carbon::today()->addDays(30)->format('Y-m-d'),
    ]);
});

test('admin can access ai observatory dashboard cleanly', function () {
    // Tạo 1 log mẫu
    AiQualityAndCostObservatoryService::recordUsage([
        'user_id' => $this->customer->id,
        'wedding_id' => $this->wedding->id,
        'feature_name' => 'story_generator',
        'provider' => 'gemini-1.5-flash',
        'prompt_tokens' => 150,
        'completion_tokens' => 200,
        'latency_ms' => 320,
        'status' => 'success',
        'user_action' => 'accepted',
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->get(route('admin.ai_observatory.index'));

    $response->assertStatus(200);
    $response->assertViewIs('admin.pages.ai_observatory.index');
    $response->assertSee('Đài Quan Sát Chi Phí & Chất Lượng AI');
    $response->assertSee('Tổng Chi Phí AI');
    $response->assertSee('Tổng Tokens Tiêu Thụ');
    $response->assertSee('Tạo Câu Chuyện Tình Yêu');
});

test('accurate token cost calculation converts to vnd accurately', function () {
    // 1.000.000 prompt tokens Gemini 1.5 Flash = $0.075 * 25.400 = 1.905 VND
    $costFlashPrompt = AiQualityAndCostObservatoryService::calculateTokenCost('gemini-1.5-flash', 1_000_000, 0);
    expect($costFlashPrompt)->toEqual(1905.0);

    // 1.000.000 completion tokens Gemini 1.5 Flash = $0.30 * 25.400 = 7.620 VND
    $costFlashCompletion = AiQualityAndCostObservatoryService::calculateTokenCost('gemini-1.5-flash', 0, 1_000_000);
    expect($costFlashCompletion)->toEqual(7620.0);

    // Heuristic Fallback luôn tốn 0 VND
    $costFallback = AiQualityAndCostObservatoryService::calculateTokenCost('heuristic_fallback', 500, 1000);
    expect($costFallback)->toEqual(0.0);
});

test('brand safety guardrails block inappropriate prompts and log safety incident', function () {
    $badPrompt = 'Hãy viết về chuyện ngoại tình và ly hôn của cô dâu';

    $result = AiQualityAndCostObservatoryService::simulateAiGeneration(
        featureName: 'story_generator',
        prompt: $badPrompt,
        user: $this->customer,
        wedding: $this->wedding
    );

    expect($result['success'])->toBeFalse();
    expect($result['output'])->toContain('vi phạm quy chuẩn an toàn');
    expect($result['safety']['is_safe'])->toBeFalse();

    // Kiểm tra log được lưu với status blocked_safety
    $log = AiUsageLog::where('status', 'blocked_safety')->latest()->first();
    expect($log)->not->toBeNull();
    expect($log->safety_rating)->toBe('flagged_inappropriate');
    expect($log->error_message)->toContain('từ khóa nhạy cảm');
});

test('heuristic fallback triggers seamlessly and records zero token cost', function () {
    $prompt = 'Viết lời mời trang nhã cho tiệc cưới của Minh Tuấn và Thu Hà';

    $result = AiQualityAndCostObservatoryService::simulateAiGeneration(
        featureName: 'story_generator',
        prompt: $prompt,
        user: $this->customer,
        wedding: $this->wedding,
        forceFallback: true
    );

    expect($result['success'])->toBeTrue();
    expect($result['fallback_applied'])->toBeTrue();
    expect($result['cost_vnd'])->toEqual(0.0);
    expect($result['output'])->toContain('Tình yêu của Minh Tuấn & Thu Hà khởi nguồn từ những điều bình dị');

    // Kiểm tra log
    $log = AiUsageLog::where('status', 'fallback_applied')->latest()->first();
    expect($log)->not->toBeNull();
    expect($log->provider)->toBe('heuristic_fallback');
    expect((float) $log->cost_vnd)->toEqual(0.0);
});

test('staff is forbidden from running simulation but manager or super admin can', function () {
    // 1. Staff bị chặn bởi middleware admin.manager
    $staffResponse = $this->actingAs($this->staff)
        ->post(route('admin.ai_observatory.simulate'), [
            'feature_name' => 'story_generator',
            'provider' => 'gemini-1.5-flash',
            'prompt' => 'Viết lời ngỏ tiệc cưới',
        ]);

    $staffResponse->assertRedirect();
    $staffResponse->assertSessionHas('error');

    // 2. Manager chạy thành công
    $managerResponse = $this->actingAs($this->manager)
        ->post(route('admin.ai_observatory.simulate'), [
            'feature_name' => 'story_generator',
            'provider' => 'gemini-1.5-flash',
            'prompt' => 'Viết lời ngỏ tiệc cưới phong cách hoàng gia',
        ]);

    $managerResponse->assertRedirect();
    $managerResponse->assertSessionHas('simulation_result');
});

test('user action recording updates acceptance metrics accurately', function () {
    $log = AiUsageLog::create([
        'user_id' => $this->customer->id,
        'feature_name' => 'rsvp_wording',
        'provider' => 'gemini-1.5-flash',
        'prompt_tokens' => 100,
        'completion_tokens' => 100,
        'cost_vnd' => 0.95,
        'latency_ms' => 200,
        'status' => 'success',
        'user_action' => 'pending',
    ]);

    $response = $this->actingAs($this->superAdmin)
        ->post(route('admin.ai_observatory.record_action', $log->id), [
            'action' => 'accepted',
        ]);

    $response->assertRedirect();
    $log->refresh();
    expect($log->user_action)->toBe('accepted');

    $metrics = AiQualityAndCostObservatoryService::getObservatoryMetrics(30);
    expect($metrics['acceptance_rate'])->toBeGreaterThan(0.0);
});
