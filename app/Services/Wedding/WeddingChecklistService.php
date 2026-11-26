<?php

declare(strict_types=1);

namespace App\Services\Wedding;

use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Smart Wedding Checklist Service (Mây Hẹn – The Wedding Journey).
 *
 * - Hỗ trợ 3 vùng miền (Bắc / Trung / Nam) & 3 phong cách (Nhà hàng / Ngoài trời / Tối giản).
 * - Tự động co giãn hạn chót theo khoảng thời gian còn lại đến ngày cưới (12m / 6m / 3m / 30d / 14d), không bao giờ tạo hạn chót ở quá khứ.
 * - Kiểm tra chu trình phụ thuộc (Dependency Cycle Detection bằng DFS) để ngăn deadlock công việc.
 */
class WeddingChecklistService
{
    /**
     * Sinh danh sách công việc mẫu có co giãn thời gian thực tế theo ngày cưới và vùng miền.
     *
     * @return list<array<string, mixed>>
     */
    public function generateStarterTasks(?string $weddingDateIso, string $region = 'bac', string $style = 'nha_hang'): array
    {
        $today = Carbon::now('Asia/Ho_Chi_Minh')->startOfDay();
        $weddingDate = $weddingDateIso
            ? Carbon::parse($weddingDateIso, 'Asia/Ho_Chi_Minh')->startOfDay()
            : $today->copy()->addMonths(6);

        $daysRemaining = max(7, (int) $today->diffInDays($weddingDate, false));
        $scaleRatio = min(1.0, $daysRemaining / 180.0);

        $baseTasks = [
            ['code' => 'T01', 'phase' => 'Khởi động & Thống nhất', 'title' => 'Thống nhất ngân sách tổng & đóng góp hai gia đình', 'offset_days' => 180, 'priority' => 'high', 'assignee' => 'Cặp đôi & Hai họ', 'depends_on' => null],
            ['code' => 'T02', 'phase' => 'Khởi động & Thống nhất', 'title' => 'Xem ngày cưới & chốt 2–3 ngày ứng viên thuận tiện', 'offset_days' => 170, 'priority' => 'high', 'assignee' => 'Cặp đôi', 'depends_on' => 'T01'],
            ['code' => 'T03', 'phase' => 'Khởi động & Thống nhất', 'title' => 'Lên danh sách khách mời dự kiến & ước lượng số bàn', 'offset_days' => 160, 'priority' => 'high', 'assignee' => 'Hai gia đình', 'depends_on' => 'T01'],
            ['code' => 'T04', 'phase' => 'Địa điểm & Nhà cung cấp', 'title' => 'Khảo sát và đặt cọc nhà hàng / sảnh tiệc cưới', 'offset_days' => 145, 'priority' => 'high', 'assignee' => 'Cặp đôi', 'depends_on' => 'T03'],
            ['code' => 'T05', 'phase' => 'Địa điểm & Nhà cung cấp', 'title' => 'Đặt ekip chụp ảnh phóng sự cưới & quay phim', 'offset_days' => 120, 'priority' => 'medium', 'assignee' => 'Cặp đôi', 'depends_on' => 'T04'],
            ['code' => 'T06', 'phase' => 'Địa điểm & Nhà cung cấp', 'title' => 'Chọn váy cưới, vest chú rể và áo dài ăn hỏi', 'offset_days' => 95, 'priority' => 'medium', 'assignee' => 'Cô dâu & Chú rể', 'depends_on' => 'T04'],
            ['code' => 'T07', 'phase' => 'Thủ tục & Nghi lễ', 'title' => 'Nộp hồ sơ đăng ký kết hôn trên Cổng Dịch vụ công Quốc gia (dichvucong.gov.vn)', 'offset_days' => 75, 'priority' => 'high', 'assignee' => 'Cô dâu & Chú rể', 'depends_on' => null],
            ['code' => 'T08', 'phase' => 'Thủ tục & Nghi lễ', 'title' => $this->getRegionalCeremonyTaskTitle($region), 'offset_days' => 60, 'priority' => 'high', 'assignee' => 'Hai gia đình', 'depends_on' => 'T02'],
            ['code' => 'T09', 'phase' => 'Thiệp cưới & Khách mời', 'title' => 'Tạo thiệp cưới online trên Mây Hẹn & mở cổng xác nhận RSVP', 'offset_days' => 45, 'priority' => 'high', 'assignee' => 'Cặp đôi', 'depends_on' => 'T04'],
            ['code' => 'T10', 'phase' => 'Thiệp cưới & Khách mời', 'title' => 'Gửi thiệp mời (trực tiếp + Zalo/link cá nhân hóa) & tổng hợp chế độ ăn', 'offset_days' => 30, 'priority' => 'high', 'assignee' => 'Cặp đôi & Hai họ', 'depends_on' => 'T09'],
            ['code' => 'T11', 'phase' => 'Hoàn thiện & Chạy chương trình', 'title' => 'Chốt số bàn chính thức & sắp xếp sơ đồ bàn tiệc trên Studio 2D', 'offset_days' => 14, 'priority' => 'high', 'assignee' => 'Cặp đôi', 'depends_on' => 'T10'],
            ['code' => 'T12', 'phase' => 'Hoàn thiện & Chạy chương trình', 'title' => 'Chốt kịch bản MC, lời phát biểu hai họ & lịch trình ngày cưới', 'offset_days' => 7, 'priority' => 'medium', 'assignee' => 'Cặp đôi & MC', 'depends_on' => 'T11'],
            ['code' => 'T13', 'phase' => 'Hoàn thiện & Chạy chương trình', 'title' => 'Kiểm tra lại toàn bộ trang phục, nhẫn cưới, phong bì lì xì & phương án dự phòng', 'offset_days' => 2, 'priority' => 'high', 'assignee' => 'Cô dâu & Chú rể', 'depends_on' => 'T12'],
        ];

        if ($style === 'ngoai_troi') {
            $baseTasks[] = [
                'code' => 'T14',
                'phase' => 'Địa điểm & Nhà cung cấp',
                'title' => 'Lập phương án dự phòng thời tiết (nhà bạt, dù che, sảnh trong nhà dự phòng)',
                'offset_days' => 40,
                'priority' => 'high',
                'assignee' => 'Cặp đôi & Wedding Planner',
                'depends_on' => 'T04',
            ];
        }

        $result = [];
        foreach ($baseTasks as $idx => $task) {
            $scaledOffset = (int) round($task['offset_days'] * $scaleRatio);
            $dueDate = $weddingDate->copy()->subDays(max(1, $scaledOffset));
            if ($dueDate->lt($today)) {
                // Không bao giờ tạo hạn chót ở quá khứ
                $dueDate = $today->copy()->addDays(min($daysRemaining - 1, max(1, $idx + 1)));
            }

            $result[] = [
                'code' => $task['code'],
                'phase' => $task['phase'],
                'title' => $task['title'],
                'due_date' => $dueDate->toDateString(),
                'priority' => $task['priority'],
                'status' => 'todo',
                'assignee' => $task['assignee'],
                'depends_on_code' => $task['depends_on'],
                'sort_order' => ($idx + 1) * 10,
            ];
        }

        return $result;
    }

    /**
     * Kiểm tra vòng lặp phụ thuộc (Dependency Cycle Detection bằng DFS).
     * Trả về danh sách mã/ID tạo thành chu trình nếu có; mảng rỗng nếu hợp lệ (DAG).
     *
     * @param  array<int|string, int|string|null>  $dependencyMap  [taskId => parentTaskId|null]
     * @return list<int|string>
     */
    public function detectDependencyCycle(array $dependencyMap): array
    {
        $state = []; // 0/unset = unvisited, 1 = visiting, 2 = visited

        foreach (array_keys($dependencyMap) as $node) {
            if (($state[$node] ?? 0) !== 0) {
                continue;
            }

            $path = [];
            $curr = $node;
            while ($curr !== null && array_key_exists($curr, $dependencyMap)) {
                if (($state[$curr] ?? 0) === 1) {
                    $cycleStart = array_search($curr, $path, true);
                    $cycle = $cycleStart !== false ? array_slice($path, (int) $cycleStart) : [$curr];
                    $cycle[] = $curr;

                    return $cycle;
                }
                if (($state[$curr] ?? 0) === 2) {
                    break;
                }

                $state[$curr] = 1;
                $path[] = $curr;
                $curr = $dependencyMap[$curr];
            }

            foreach ($path as $visitedNode) {
                $state[$visitedNode] = 2;
            }
        }

        return [];
    }

    /**
     * Xác thực trước khi gắn phụ thuộc cho một công việc.
     *
     * @param  array<int|string, int|string|null>  $existingDependencies
     */
    public function assertValidDependency(int|string $taskId, int|string|null $dependsOnId, array $existingDependencies): void
    {
        if ($dependsOnId === null || $dependsOnId === '') {
            return;
        }
        if ((string) $taskId === (string) $dependsOnId) {
            throw new InvalidArgumentException('Một công việc không thể tự phụ thuộc vào chính nó.');
        }

        $existingDependencies[$taskId] = $dependsOnId;
        $cycle = $this->detectDependencyCycle($existingDependencies);
        if ($cycle !== []) {
            throw new InvalidArgumentException('Phát hiện vòng lặp phụ thuộc công việc ('.implode(' → ', $cycle).'). Vui lòng chọn công việc tiền đề khác.');
        }
    }

    protected function getRegionalCeremonyTaskTitle(string $region): string
    {
        return match ($region) {
            'trung' => 'Chuẩn bị lễ vật ăn hỏi Miền Trung (trầu cau, bánh phu thê, nến tơ hồng, rượu trà)',
            'nam' => 'Chuẩn bị mâm quả Miền Nam (số chẵn 6 hoặc 8 mâm, đèn cầy long phụng, trà rượu)',
            default => 'Chuẩn bị tráp ăn hỏi Miền Bắc (số lẻ 5, 7 hoặc 9 tráp, đội bê tráp hai bên)',
        };
    }
}
