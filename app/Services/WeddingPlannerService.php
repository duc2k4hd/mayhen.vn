<?php

namespace App\Services;

use App\Models\Wedding;
use App\Models\WeddingBudgetItem;
use App\Models\WeddingChecklistTask;
use Carbon\Carbon;

class WeddingPlannerService
{
    public function __construct(
        protected CsvSanitizerService $csvSanitizer
    ) {}

    /**
     * Tổng hợp toàn bộ số liệu tiến độ checklist và dự toán ngân sách.
     */
    public function getPlanSummary(Wedding $wedding): array
    {
        $tasks = $wedding->checklistTasks()->get();
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $overdueTasks = $tasks->filter(fn (WeddingChecklistTask $t) => $t->isOverdue())->count();
        $progressPercent = $totalTasks > 0 ? (int) round(($completedTasks / $totalTasks) * 100) : 0;

        $budgetItems = $wedding->budgetItems()->get();
        $targetBudget = $wedding->target_budget;
        $totalEstimated = (int) $budgetItems->sum('estimated_cost');
        $totalActual = (int) $budgetItems->sum('actual_cost');
        $totalPaid = (int) $budgetItems->sum('paid_amount');
        $totalPending = (int) $budgetItems->sum(fn (WeddingBudgetItem $i) => $i->pending_amount);
        $variance = $totalActual > 0 ? ($totalActual - $totalEstimated) : 0;
        $effectiveSpent = $totalActual > 0 ? $totalActual : $totalEstimated;
        $remainingFromTarget = $targetBudget > 0 ? ($targetBudget - $effectiveSpent) : 0;

        // Phân tích cơ cấu ngân sách theo từng danh mục
        $categories = [
            'venue_catering' => 'Sảnh Tiệc & Ẩm Thực',
            'attire_jewelry' => 'Trang Phục & Nhẫn Cưới',
            'photography_video' => 'Chụp Ảnh & Phóng Sự',
            'invitations_favors' => 'Thiệp Cưới & Quà Tặng',
            'decor_flowers' => 'Trang Trí & Gia Tiên',
            'ceremony_offerings' => 'Mâm Quả & Xe Hoa',
            'entertainment_music' => 'MC & Âm Nhạc',
            'contingency' => 'Dự Phòng Phát Sinh',
            'other' => 'Hạng Mục Khác',
        ];

        $categoryBreakdown = [];
        foreach ($categories as $key => $name) {
            $items = $budgetItems->where('category', $key);
            $catEst = (int) $items->sum('estimated_cost');
            $catAct = (int) $items->sum('actual_cost');
            $catPaid = (int) $items->sum('paid_amount');
            $cost = $catAct > 0 ? $catAct : $catEst;
            $percent = $effectiveSpent > 0 ? round(($cost / $effectiveSpent) * 100, 1) : 0;

            $categoryBreakdown[$key] = [
                'name' => $name,
                'count' => $items->count(),
                'estimated' => $catEst,
                'actual' => $catAct,
                'paid' => $catPaid,
                'cost' => $cost,
                'percent' => $percent,
            ];
        }

        return [
            'tasks' => [
                'total' => $totalTasks,
                'completed' => $completedTasks,
                'pending' => $tasks->where('status', 'pending')->count(),
                'in_progress' => $tasks->where('status', 'in_progress')->count(),
                'overdue' => $overdueTasks,
                'progress_percent' => $progressPercent,
            ],
            'budget' => [
                'target_budget' => $targetBudget,
                'total_estimated' => $totalEstimated,
                'total_actual' => $totalActual,
                'total_paid' => $totalPaid,
                'total_pending' => $totalPending,
                'variance' => $variance,
                'effective_spent' => $effectiveSpent,
                'remaining_from_target' => $remainingFromTarget,
                'category_breakdown' => $categoryBreakdown,
            ],
        ];
    }

    /**
     * Tự động khởi tạo danh mục việc cần làm chuẩn mực văn hóa cưới hỏi Việt Nam.
     */
    public function seedDefaultChecklist(Wedding $wedding): int
    {
        $weddingDate = $wedding->wedding_date ? Carbon::parse($wedding->wedding_date) : Carbon::now()->addMonths(3);

        $templates = [
            [
                'phase' => '12_months',
                'category' => 'venue_catering',
                'title' => 'Chọn ngày lành tháng tốt & Họp mặt hai gia đình dạm ngõ',
                'description' => 'Hai bên gia đình gặp mặt chính thức, xem ngày tổ chức lễ ăn hỏi và tiệc cưới.',
                'assignee_name' => 'Hai bên phụ mẫu',
                'days_before' => 300,
                'priority' => 'urgent',
            ],
            [
                'phase' => '12_months',
                'category' => 'venue_catering',
                'title' => 'Khảo sát và đặt cọc trung tâm tiệc cưới / Sảnh tiệc',
                'description' => 'Lựa chọn sảnh tiệc có sức chứa phù hợp, nếm thử món ăn và ký hợp đồng đặt cọc giữ chỗ.',
                'assignee_name' => 'Cô dâu & Chú rể',
                'days_before' => 240,
                'priority' => 'urgent',
            ],
            [
                'phase' => '6_months',
                'category' => 'photography',
                'title' => 'Lên concept & Chụp bộ ảnh cưới Pre-Wedding',
                'description' => 'Chọn studio uy tín, trang phục và địa điểm chụp ảnh để kịp chuẩn bị thiệp cưới và phóng sự.',
                'assignee_name' => 'Cô dâu',
                'days_before' => 150,
                'priority' => 'high',
            ],
            [
                'phase' => '6_months',
                'category' => 'attire_beauty',
                'title' => 'Đặt may / Thuê váy cưới, veston và áo dài gia tiên',
                'description' => 'Thử đồ cưới, may đo áo dài truyền thống cho lễ đính hôn và lễ rước dâu.',
                'assignee_name' => 'Cô dâu & Chú rể',
                'days_before' => 120,
                'priority' => 'high',
            ],
            [
                'phase' => '6_months',
                'category' => 'attire_beauty',
                'title' => 'Chọn mua cặp nhẫn cưới kỷ vật',
                'description' => 'Chọn mẫu nhẫn cưới yêu thích, khắc tên đôi uyên ương và ngày kỷ niệm.',
                'assignee_name' => 'Chú rể',
                'days_before' => 100,
                'priority' => 'high',
            ],
            [
                'phase' => '3_months',
                'category' => 'invitations_guests',
                'title' => 'Lập danh sách quan khách & Phân nhóm thiệp mời',
                'description' => 'Tổng hợp danh sách khách họ hàng, bạn bè, đồng nghiệp; chuẩn bị xưng hô chuẩn mực.',
                'assignee_name' => 'Cô dâu & Chú rể',
                'days_before' => 90,
                'priority' => 'high',
            ],
            [
                'phase' => '3_months',
                'category' => 'invitations_guests',
                'title' => 'Thiết kế & Xuất bản thiệp cưới online trên Mây Hẹn',
                'description' => 'Chọn mẫu thiệp Quiet Luxury, tùy biến câu chuyện tình yêu, album ảnh và kích hoạt RSVP cá nhân hóa.',
                'assignee_name' => 'Cô dâu & Chú rể',
                'days_before' => 75,
                'priority' => 'urgent',
            ],
            [
                'phase' => '3_months',
                'category' => 'ceremony_decor',
                'title' => 'Đặt tráp mâm quả lễ vật & Trang trí tư gia hai họ',
                'description' => 'Chuẩn bị số lượng tráp quả theo phong tục (6 hoặc 8 tráp), hoa tươi bàn thờ gia tiên.',
                'assignee_name' => 'Nhà trai & Nhà gái',
                'days_before' => 60,
                'priority' => 'medium',
            ],
            [
                'phase' => '1_month',
                'category' => 'invitations_guests',
                'title' => 'Gửi thiệp cưới cá nhân hóa qua Zalo/Tin nhắn cho quan khách',
                'description' => 'Gửi link thiệp kèm lời mời trang trọng, theo dõi tỷ lệ mở thiệp và phản hồi RSVP realtime.',
                'assignee_name' => 'Cô dâu & Chú rể',
                'days_before' => 30,
                'priority' => 'urgent',
            ],
            [
                'phase' => '1_month',
                'category' => 'logistics_rehearsal',
                'title' => 'Chọn đội bê tráp, xe hoa rước dâu và điều phối viên ngày cưới',
                'description' => 'Liên hệ đội hình bưng mâm quả nam nữ, sắp xếp xe hoa và người phụ trách hậu cần.',
                'assignee_name' => 'Bạn thân & Phụ rể',
                'days_before' => 20,
                'priority' => 'medium',
            ],
            [
                'phase' => '1_week',
                'category' => 'invitations_guests',
                'title' => 'Chốt danh sách tham dự RSVP & Sắp xếp sơ đồ bàn tiệc',
                'description' => 'Rà soát khách đã xác nhận tham gia, phân bổ chỗ ngồi trên Seating Studio của Mây Hẹn.',
                'assignee_name' => 'Cô dâu & Chú rể',
                'days_before' => 7,
                'priority' => 'urgent',
            ],
            [
                'phase' => '1_week',
                'category' => 'logistics_rehearsal',
                'title' => 'Diễn tập thử cổng điểm danh QR & Bàn giao ca trực lễ tân',
                'description' => 'Kiểm tra mã QR sảnh tiệc, hướng dẫn ban lễ tân quét mã đón khách mượt mà.',
                'assignee_name' => 'Trưởng ban lễ tân',
                'days_before' => 3,
                'priority' => 'high',
            ],
            [
                'phase' => 'wedding_day',
                'category' => 'logistics_rehearsal',
                'title' => 'Lễ Gia Tiên & Lễ Thành Hôn rước dâu',
                'description' => 'Đảm bảo đúng giờ hoàng đạo xuất hành, trao nhẫn cưới và bái lạy gia tiên.',
                'assignee_name' => 'Đôi uyên ương & Gia đình',
                'days_before' => 0,
                'priority' => 'urgent',
            ],
            [
                'phase' => 'wedding_day',
                'category' => 'venue_catering',
                'title' => 'Đón tiếp quan khách & Khai tiệc cưới tại sảnh',
                'description' => 'Đón khách chụp hình backdrop, hướng dẫn quét mã chia sẻ ảnh Photo Drop và ký sổ lưu bút số.',
                'assignee_name' => 'Ban lễ tân & MC',
                'days_before' => 0,
                'priority' => 'urgent',
            ],
            [
                'phase' => 'post_wedding',
                'category' => 'post_wedding',
                'title' => 'Gửi lời cảm ơn sau cưới & Đối soát quyết toán nhà hàng',
                'description' => 'Sử dụng công cụ Thank You Builder gửi lời cảm ơn cá nhân hóa đến từng vị khách đã đến chúc phúc.',
                'assignee_name' => 'Cô dâu & Chú rể',
                'days_before' => -2,
                'priority' => 'high',
            ],
            [
                'phase' => 'post_wedding',
                'category' => 'post_wedding',
                'title' => 'Khóa Hộp Thời Gian (Time Capsule) & Lưu giữ trang kỷ niệm số',
                'description' => 'Lưu giữ toàn bộ ảnh chụp của khách, lời chúc mừng cưới và niêm phong kỷ niệm ngày trọng đại.',
                'assignee_name' => 'Cô dâu & Chú rể',
                'days_before' => -7,
                'priority' => 'medium',
            ],
        ];

        $createdCount = 0;
        foreach ($templates as $idx => $t) {
            $dueDate = (clone $weddingDate)->subDays($t['days_before']);

            WeddingChecklistTask::create([
                'wedding_id' => $wedding->id,
                'phase' => $t['phase'],
                'category' => $t['category'],
                'title' => $t['title'],
                'description' => $t['description'],
                'assignee_name' => $t['assignee_name'],
                'due_date' => $dueDate->format('Y-m-d'),
                'priority' => $t['priority'],
                'status' => 'pending',
                'display_order' => $idx + 1,
            ]);
            $createdCount++;
        }

        AuditLogger::log(
            'PLANNER_CHECKLIST_SEEDED',
            'wedding',
            $wedding->id,
            "Đã khởi tạo bộ checklist {$createdCount} việc chuẩn bị cưới chuẩn Việt Nam.",
            ['tasks_count' => $createdCount]
        );

        return $createdCount;
    }

    /**
     * Tạo mới công việc chuẩn bị cưới.
     */
    public function createTask(Wedding $wedding, array $data): WeddingChecklistTask
    {
        $maxOrder = (int) $wedding->checklistTasks()->max('display_order');

        $task = WeddingChecklistTask::create([
            'wedding_id' => $wedding->id,
            'category' => $data['category'] ?? 'general',
            'phase' => $data['phase'] ?? 'wedding_day',
            'title' => trim((string) $data['title']),
            'description' => ! empty($data['description']) ? trim((string) $data['description']) : null,
            'assignee_name' => ! empty($data['assignee_name']) ? trim((string) $data['assignee_name']) : null,
            'due_date' => ! empty($data['due_date']) ? $data['due_date'] : null,
            'priority' => $data['priority'] ?? 'medium',
            'status' => $data['status'] ?? 'pending',
            'display_order' => $maxOrder + 1,
        ]);

        AuditLogger::log(
            'PLANNER_TASK_CREATED',
            'wedding_checklist_task',
            $task->id,
            "Tạo mới công việc: {$task->title}",
            ['title' => $task->title, 'phase' => $task->phase]
        );

        return $task;
    }

    /**
     * Cập nhật công việc chuẩn bị cưới.
     */
    public function updateTask(WeddingChecklistTask $task, array $data): WeddingChecklistTask
    {
        $isCompleting = ($data['status'] ?? '') === 'completed' && $task->status !== 'completed';
        $completedAt = $isCompleting ? now() : ($task->status === 'completed' ? $task->completed_at : null);

        $task->update([
            'category' => $data['category'] ?? $task->category,
            'phase' => $data['phase'] ?? $task->phase,
            'title' => trim((string) ($data['title'] ?? $task->title)),
            'description' => isset($data['description']) ? trim((string) $data['description']) : $task->description,
            'assignee_name' => isset($data['assignee_name']) ? trim((string) $data['assignee_name']) : $task->assignee_name,
            'due_date' => array_key_exists('due_date', $data) ? $data['due_date'] : $task->due_date,
            'priority' => $data['priority'] ?? $task->priority,
            'status' => $data['status'] ?? $task->status,
            'completed_at' => $completedAt,
        ]);

        return $task;
    }

    /**
     * Bật/tắt trạng thái hoàn thành của công việc.
     */
    public function toggleTask(WeddingChecklistTask $task): WeddingChecklistTask
    {
        if ($task->status === 'completed') {
            $task->update([
                'status' => 'pending',
                'completed_at' => null,
            ]);
        } else {
            $task->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        }

        return $task;
    }

    /**
     * Xóa công việc khỏi kế hoạch.
     */
    public function deleteTask(WeddingChecklistTask $task): bool
    {
        $title = $task->title;
        $id = $task->id;
        $weddingId = $task->wedding_id;

        $task->delete();

        AuditLogger::log(
            'PLANNER_TASK_DELETED',
            'wedding_checklist_task',
            $id,
            "Đã xóa công việc: {$title}",
            ['wedding_id' => $weddingId]
        );

        return true;
    }

    /**
     * Tạo mới khoản mục dự toán chi phí.
     */
    public function createBudgetItem(Wedding $wedding, array $data): WeddingBudgetItem
    {
        $maxOrder = (int) $wedding->budgetItems()->max('display_order');
        $est = max(0, (int) ($data['estimated_cost'] ?? 0));
        $act = max(0, (int) ($data['actual_cost'] ?? 0));
        $paid = max(0, (int) ($data['paid_amount'] ?? 0));
        $isPaid = ! empty($data['is_paid_in_full']) || ($act > 0 && $paid >= $act) || ($act === 0 && $est > 0 && $paid >= $est);

        $item = WeddingBudgetItem::create([
            'wedding_id' => $wedding->id,
            'category' => $data['category'] ?? 'other',
            'item_name' => trim((string) $data['item_name']),
            'estimated_cost' => $est,
            'actual_cost' => $act,
            'paid_amount' => $paid,
            'vendor_name' => ! empty($data['vendor_name']) ? trim((string) $data['vendor_name']) : null,
            'vendor_contact' => ! empty($data['vendor_contact']) ? trim((string) $data['vendor_contact']) : null,
            'notes' => ! empty($data['notes']) ? trim((string) $data['notes']) : null,
            'is_paid_in_full' => $isPaid,
            'display_order' => $maxOrder + 1,
        ]);

        AuditLogger::log(
            'PLANNER_BUDGET_ITEM_CREATED',
            'wedding_budget_item',
            $item->id,
            "Thêm khoản chi: {$item->item_name} (Dự toán: ".number_format($item->estimated_cost).' đ)',
            ['item_name' => $item->item_name, 'category' => $item->category]
        );

        return $item;
    }

    /**
     * Cập nhật khoản mục dự toán chi phí.
     */
    public function updateBudgetItem(WeddingBudgetItem $item, array $data): WeddingBudgetItem
    {
        $est = array_key_exists('estimated_cost', $data) ? max(0, (int) $data['estimated_cost']) : $item->estimated_cost;
        $act = array_key_exists('actual_cost', $data) ? max(0, (int) $data['actual_cost']) : $item->actual_cost;
        $paid = array_key_exists('paid_amount', $data) ? max(0, (int) $data['paid_amount']) : $item->paid_amount;
        $isPaid = ! empty($data['is_paid_in_full']) || ($act > 0 && $paid >= $act) || ($act === 0 && $est > 0 && $paid >= $est);

        $item->update([
            'category' => $data['category'] ?? $item->category,
            'item_name' => trim((string) ($data['item_name'] ?? $item->item_name)),
            'estimated_cost' => $est,
            'actual_cost' => $act,
            'paid_amount' => $paid,
            'vendor_name' => isset($data['vendor_name']) ? trim((string) $data['vendor_name']) : $item->vendor_name,
            'vendor_contact' => isset($data['vendor_contact']) ? trim((string) $data['vendor_contact']) : $item->vendor_contact,
            'notes' => isset($data['notes']) ? trim((string) $data['notes']) : $item->notes,
            'is_paid_in_full' => $isPaid,
        ]);

        return $item;
    }

    /**
     * Xóa khoản mục chi phí.
     */
    public function deleteBudgetItem(WeddingBudgetItem $item): bool
    {
        $name = $item->item_name;
        $id = $item->id;
        $weddingId = $item->wedding_id;

        $item->delete();

        AuditLogger::log(
            'PLANNER_BUDGET_ITEM_DELETED',
            'wedding_budget_item',
            $id,
            "Đã xóa khoản chi: {$name}",
            ['wedding_id' => $weddingId]
        );

        return true;
    }

    /**
     * Cập nhật hạn mức ngân sách mục tiêu (Target Budget).
     */
    public function updateTargetBudget(Wedding $wedding, int $targetBudget): void
    {
        $extra = is_array($wedding->extra_data) ? $wedding->extra_data : (json_decode((string) $wedding->extra_data, true) ?? []);
        $extra['target_budget'] = max(0, $targetBudget);

        $wedding->update(['extra_data' => $extra]);

        AuditLogger::log(
            'PLANNER_TARGET_BUDGET_UPDATED',
            'wedding',
            $wedding->id,
            'Cập nhật ngân sách mục tiêu: '.number_format($targetBudget).' đ',
            ['target_budget' => $targetBudget]
        );
    }

    /**
     * Xuất báo cáo bảng tính dự toán chi phí đám cưới CSV an toàn (có UTF-8 BOM).
     */
    public function exportBudgetCsv(Wedding $wedding): string
    {
        $summary = $this->getPlanSummary($wedding);
        $items = $wedding->budgetItems()->get();

        $rows = [];
        $rows[] = ['BẢNG TÍNH DỰ TOÁN & CHI PHÍ ĐÁM CƯỚI - MÂY HẸN (MAYHEN.VN)'];
        $rows[] = ['Cặp đôi:', "{$wedding->groom_name} & {$wedding->bride_name}"];
        $rows[] = ['Ngày cưới:', $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : 'Chưa định ngày'];
        $rows[] = ['Địa điểm:', $wedding->venue_name ?? ''];
        $rows[] = ['Hạn mức ngân sách dự kiến:', number_format($summary['budget']['target_budget']).' đ'];
        $rows[] = ['Tổng dự toán:', number_format($summary['budget']['total_estimated']).' đ'];
        $rows[] = ['Tổng thực chi:', number_format($summary['budget']['total_actual']).' đ'];
        $rows[] = ['Đã thanh toán / Đặt cọc:', number_format($summary['budget']['total_paid']).' đ'];
        $rows[] = ['Còn thiếu cần thanh toán:', number_format($summary['budget']['total_pending']).' đ'];
        $rows[] = ['Chênh lệch so với dự toán:', number_format($summary['budget']['variance']).' đ'];
        $rows[] = [];
        $rows[] = [
            'STT',
            'Hạng Mục',
            'Khoản Chi Tiết',
            'Dự Toán (VND)',
            'Thực Chi (VND)',
            'Đã Trả (VND)',
            'Còn Thiếu (VND)',
            'Chênh Lệch',
            'Nhà Cung Cấp',
            'Liên Hệ',
            'Trạng Thái',
            'Ghi Chú',
        ];

        foreach ($items as $idx => $item) {
            $status = $item->is_paid_in_full ? 'Đã tất toán' : ($item->paid_amount > 0 ? 'Đã cọc một phần' : 'Chưa thanh toán');
            $rows[] = [
                $idx + 1,
                $item->category_label,
                $item->item_name,
                $item->estimated_cost,
                $item->actual_cost,
                $item->paid_amount,
                $item->pending_amount,
                $item->variance,
                $item->vendor_name ?? '',
                $item->vendor_contact ?? '',
                $status,
                $item->notes ?? '',
            ];
        }

        // Tạo nội dung CSV kèm UTF-8 BOM
        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            $sanitizedRow = array_map(function ($val) {
                return is_string($val) ? $this->csvSanitizer->sanitize($val) : $val;
            }, $row);
            fputcsv($handle, $sanitizedRow);
        }
        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return "\xEF\xBB\xBF".$csvContent;
    }
}
