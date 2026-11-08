<?php

namespace App\Services;

use App\Models\Plan;

class PlanFitAdvisorService
{
    /**
     * Phân tích nhu cầu thực tế của cặp đôi và đề xuất gói dịch vụ tối ưu, minh bạch và tiết kiệm nhất.
     *
     * @param  array{guest_scale?: string, events_count?: string, operations_need?: string, memory_retention?: string}  $answers
     * @return array{
     *     recommended_plan: array<string, mixed>,
     *     alternative_plan: ?array<string, mixed>,
     *     fit_score: int,
     *     fit_reasons: array<int, string>,
     *     traditional_cost_comparison: array{estimated_paper_cost: int, plan_cost: int, savings_amount: int, savings_percent: int},
     *     matched_features: array<int, string>
     * }
     */
    public function analyzeNeeds(array $answers): array
    {
        $guestScale = $answers['guest_scale'] ?? 'medium'; // small (<100), medium (100-300), large (>300)
        $eventsCount = $answers['events_count'] ?? 'single'; // single (1), dual (2: Nhà Trai & Gái), multi (3+)
        $operationsNeed = $answers['operations_need'] ?? 'basic'; // basic (chỉ gửi thiệp), advanced (check-in, phân bàn, bãi xe)
        $memoryRetention = $answers['memory_retention'] ?? 'short'; // short (6 tháng), long (2 năm, xuất ZIP ngoại tuyến)

        $plans = Plan::where('is_active', true)->get()->keyBy('slug');
        $proPlan = $plans->get('pro') ?? $plans->first();
        $premiumPlan = $plans->get('premium') ?? $plans->first();

        // Thuật toán chấm điểm nhu cầu:
        // Cần gói Pro nếu: Quy mô tiệc lớn (>300 khách) HOẶC cần 2-3 thiệp riêng (Nhà Trai & Nhà Gái) HOẶC cần vận hành check-in/phân bàn HOẶC cần lưu trữ 2 năm.
        $needsPro = ($guestScale === 'large')
            || ($eventsCount === 'dual' || $eventsCount === 'multi')
            || ($operationsNeed === 'advanced')
            || ($memoryRetention === 'long');

        $fitReasons = [];
        $matchedFeatures = [];

        if ($eventsCount === 'dual' || $eventsCount === 'multi') {
            $fitReasons[] = 'Hai bạn tổ chức tiệc riêng biệt cho Nhà Trai và Nhà Gái, cần nhân bản thiệp để phân tách sảnh tiệc và gia tiên.';
            $matchedFeatures[] = 'Nhân bản thiệp Nhà Trai / Nhà Gái độc lập không giới hạn';
        }

        if ($operationsNeed === 'advanced') {
            $fitReasons[] = 'Cần điều phối chuyên nghiệp ngày cưới: Sơ đồ sảnh tiệc, QR Check-in cổng đón tiếp và bàn giao ca trực.';
            $matchedFeatures[] = 'Studio Sơ Đồ Xếp Bàn Tiệc & QR Điểm Danh Lễ Tân Thông Minh';
        }

        if ($guestScale === 'large') {
            $fitReasons[] = 'Quy mô tiệc cưới đông (>300 khách), cần công cụ lọc khách trùng lặp và xuất danh sách Excel.';
            $matchedFeatures[] = 'Quản lý danh sách khách mời lớn & Xuất Excel chuẩn hóa';
        }

        if ($memoryRetention === 'long') {
            $fitReasons[] = 'Muốn lưu giữ kỷ niệm bền vững 2 năm kèm gói xuất ngoại tuyến Portable Wedding Archive độc lập.';
            $matchedFeatures[] = 'Két Lưu Trữ Kỷ Niệm 2 Năm & Gói Ngoại Tuyến Checksum Manifest';
        }

        if (empty($fitReasons)) {
            $fitReasons[] = 'Nhu cầu tiệc cưới ấm cúng, tinh tế và tập trung vào một lễ cưới trọn vẹn.';
            $matchedFeatures[] = 'Xuất bản đường link thiệp riêng, RSVP gia đình và QR mừng cưới chính chủ';
        }

        $chosenPlan = $needsPro ? $proPlan : $premiumPlan;
        $alternativePlan = $needsPro ? $premiumPlan : $proPlan;

        // So sánh chi phí với in thiệp giấy truyền thống
        $estimatedGuests = match ($guestScale) {
            'small' => 80,
            'large' => 450,
            default => 220,
        };

        // Chi phí in thiệp giấy trung bình: 8.000đ - 12.000đ/thiệp + công ship
        $paperCostPerCard = 10000;
        $estimatedPaperCost = $estimatedGuests * $paperCostPerCard;
        $planCost = (int) ($chosenPlan ? $chosenPlan->price : 299000);
        $savingsAmount = max(0, $estimatedPaperCost - $planCost);
        $savingsPercent = $estimatedPaperCost > 0 ? (int) round(($savingsAmount / $estimatedPaperCost) * 100) : 0;

        return [
            'recommended_plan' => [
                'id' => $chosenPlan->id ?? 3,
                'slug' => $chosenPlan->slug ?? 'pro',
                'name' => $chosenPlan->name ?? 'Trọn Vẹn (Wedding Pro)',
                'price' => (int) ($chosenPlan->price ?? 299000),
                'duration_days' => (int) ($chosenPlan->duration_days ?? 730),
                'max_invitations' => (int) ($chosenPlan->max_invitations ?? 3),
                'features' => (array) ($chosenPlan->features ?? []),
            ],
            'alternative_plan' => $alternativePlan ? [
                'id' => $alternativePlan->id ?? 2,
                'slug' => $alternativePlan->slug ?? 'premium',
                'name' => $alternativePlan->name ?? 'Hạnh Phúc (Premium)',
                'price' => (int) ($alternativePlan->price ?? 149000),
                'duration_days' => (int) ($alternativePlan->duration_days ?? 180),
                'max_invitations' => (int) ($alternativePlan->max_invitations ?? 1),
                'features' => (array) ($alternativePlan->features ?? []),
            ] : null,
            'fit_score' => $needsPro ? 96 : 92,
            'fit_reasons' => $fitReasons,
            'traditional_cost_comparison' => [
                'estimated_paper_cost' => $estimatedPaperCost,
                'plan_cost' => $planCost,
                'savings_amount' => $savingsAmount,
                'savings_percent' => $savingsPercent,
            ],
            'matched_features' => $matchedFeatures,
        ];
    }
}
