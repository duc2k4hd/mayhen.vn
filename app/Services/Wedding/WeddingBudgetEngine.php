<?php

declare(strict_types=1);

namespace App\Services\Wedding;

/**
 * Wedding Budget Engine (Mây Hẹn – The Wedding Journey).
 *
 * - 100% số nguyên VND (int) — TUYỆT ĐỐI KHÔNG DÙNG FLOAT để tránh sai số làm tròn.
 * - Tách bạch rõ ràng: Budgeted (Ngân sách) / Estimated (Ước tính) / Committed (Hợp đồng đã chốt) / Paid (Đã trả gồm cọc) / Outstanding (Còn phải trả) / Refunded (Hoàn tiền).
 * - BẤT BIẾN CHỐNG TÍNH TRÙNG TIỀN CỌC: Tiền cọc nằm trong Paid và KHÔNG BAO GIỜ cộng thêm vào Committed.
 */
class WeddingBudgetEngine
{
    /** @var array<string, array{label: string, default_ratio: int}> Tỷ trọng tham khảo (tổng = 100) */
    public const DEFAULT_CATEGORIES = [
        'venue_catering' => ['label' => 'Nhà hàng & Tiệc cưới', 'default_ratio' => 48],
        'ceremony_trays' => ['label' => 'Lễ ăn hỏi, tráp & Gia tiên', 'default_ratio' => 10],
        'photo_video' => ['label' => 'Chụp ảnh & Quay phim', 'default_ratio' => 10],
        'attire_makeup' => ['label' => 'Váy cưới, Vest & Trang điểm', 'default_ratio' => 9],
        'decor_flowers' => ['label' => 'Trang trí sảnh, Hoa tươi & Âm thanh', 'default_ratio' => 8],
        'rings_jewelry' => ['label' => 'Nhẫn cưới & Trang sức', 'default_ratio' => 6],
        'invitations_transport' => ['label' => 'Thiệp cưới, Xe hoa & Di chuyển', 'default_ratio' => 4],
        'contingency' => ['label' => 'Quỹ dự phòng phát sinh', 'default_ratio' => 5],
    ];

    /**
     * Phân bổ ngân sách mẫu theo tổng tiền VND (số nguyên chính xác 100%, tổng các hạng mục = đúng tổng ngân sách).
     *
     * @return list<array{category: string, label: string, ratio: int, budgeted_vnd: int}>
     */
    public function suggestAllocation(int $totalBudgetVnd): array
    {
        $total = max(0, $totalBudgetVnd);
        $rows = [];
        $allocated = 0;
        $keys = array_keys(self::DEFAULT_CATEGORIES);
        $lastKey = end($keys);

        foreach (self::DEFAULT_CATEGORIES as $key => $meta) {
            if ($key === $lastKey) {
                $amount = max(0, $total - $allocated);
            } else {
                $amount = (int) intdiv($total * $meta['default_ratio'], 100);
                $allocated += $amount;
            }

            $rows[] = [
                'category' => $key,
                'label' => $meta['label'],
                'ratio' => $meta['default_ratio'],
                'budgeted_vnd' => $amount,
            ];
        }

        return $rows;
    }

    /**
     * Tổng hợp kế toán cho danh sách hạng mục chi phí cưới (không bao giờ tính trùng cọc vào hợp đồng).
     *
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public function summarize(int $projectBudgetVnd, array $items): array
    {
        $totalBudgeted = 0;
        $totalEstimated = 0;
        $totalCommitted = 0;
        $totalPaid = 0;
        $totalDeposit = 0;
        $totalOutstanding = 0;
        $totalRefunded = 0;
        $cancelledCount = 0;

        $byPayer = [
            'couple' => 0,
            'groom_family' => 0,
            'bride_family' => 0,
            'other' => 0,
        ];

        $byCategory = [];
        $normalizedItems = [];

        foreach ($items as $item) {
            $status = (string) ($item['status'] ?? 'planned');
            $isCancelled = $status === 'cancelled';

            $budgeted = max(0, (int) ($item['budgeted_vnd'] ?? 0));
            $estimated = max(0, (int) ($item['estimated_vnd'] ?? 0));
            $committed = max(0, (int) ($item['committed_vnd'] ?? 0));
            $deposit = max(0, (int) ($item['deposit_vnd'] ?? 0));
            // paid_vnd luôn >= deposit_vnd (nếu người dùng nhập paid_vnd < deposit_vnd thì lấy max để nhất quán)
            $paid = max($deposit, max(0, (int) ($item['paid_vnd'] ?? 0)));
            $refunded = max(0, (int) ($item['refunded_vnd'] ?? 0));

            if ($isCancelled) {
                $cancelledCount++;
                $netPaidAfterRefund = max(0, $paid - $refunded);
                $totalRefunded += $refunded;
                $totalPaid += $netPaidAfterRefund;
                $outstanding = 0;
            } else {
                // BẤT BIẾN: outstanding chỉ tính trên giá trị hợp đồng đã chốt (committed) trừ đã thanh toán (paid). Không bao giờ cộng deposit vào committed!
                $outstanding = $committed > 0 ? max(0, $committed - $paid) : 0;

                $totalBudgeted += $budgeted;
                $totalEstimated += $estimated;
                $totalCommitted += $committed;
                $totalDeposit += $deposit;
                $totalPaid += $paid;
                $totalOutstanding += $outstanding;
                $totalRefunded += $refunded;

                $payer = (string) ($item['payer_side'] ?? 'couple');
                if (! isset($byPayer[$payer])) {
                    $payer = 'other';
                }
                $byPayer[$payer] += ($committed > 0 ? $committed : ($estimated > 0 ? $estimated : $budgeted));
            }

            $cat = (string) ($item['category'] ?? 'contingency');
            if (! isset($byCategory[$cat])) {
                $byCategory[$cat] = [
                    'category' => $cat,
                    'label' => self::DEFAULT_CATEGORIES[$cat]['label'] ?? $cat,
                    'budgeted_vnd' => 0,
                    'committed_vnd' => 0,
                    'paid_vnd' => 0,
                    'outstanding_vnd' => 0,
                ];
            }
            if (! $isCancelled) {
                $byCategory[$cat]['budgeted_vnd'] += $budgeted;
                $byCategory[$cat]['committed_vnd'] += $committed;
                $byCategory[$cat]['paid_vnd'] += $paid;
                $byCategory[$cat]['outstanding_vnd'] += $outstanding;
            }

            $normalizedItems[] = array_merge($item, [
                'budgeted_vnd' => $budgeted,
                'estimated_vnd' => $estimated,
                'committed_vnd' => $committed,
                'deposit_vnd' => $deposit,
                'paid_vnd' => $paid,
                'outstanding_vnd' => $outstanding,
                'refunded_vnd' => $refunded,
            ]);
        }

        $effectivePlanTarget = $projectBudgetVnd > 0 ? $projectBudgetVnd : $totalBudgeted;
        $projectedTotal = $totalCommitted > 0 ? $totalCommitted : $totalEstimated;
        $remainingBudget = $effectivePlanTarget - $projectedTotal;
        $overBudgetVnd = max(0, $projectedTotal - $effectivePlanTarget);

        return [
            'project_budget_vnd' => $effectivePlanTarget,
            'total_budgeted_vnd' => $totalBudgeted,
            'total_estimated_vnd' => $totalEstimated,
            'total_committed_vnd' => $totalCommitted,
            'total_deposit_vnd' => $totalDeposit,
            'total_paid_vnd' => $totalPaid,
            'total_outstanding_vnd' => $totalOutstanding,
            'total_refunded_vnd' => $totalRefunded,
            'remaining_budget_vnd' => $remainingBudget,
            'over_budget_vnd' => $overBudgetVnd,
            'is_over_budget' => $overBudgetVnd > 0,
            'cancelled_count' => $cancelledCount,
            'by_payer' => $byPayer,
            'by_category' => array_values($byCategory),
            'items' => $normalizedItems,
        ];
    }

    /**
     * Tạo nội dung CSV UTF-8 BOM để người dùng mở trực tiếp trên Excel không lỗi font tiếng Việt.
     *
     * @param  list<array<string, mixed>>  $items
     */
    public function exportCsv(string $projectName, array $items): string
    {
        $bom = "\xEF\xBB\xBF";
        $lines = [];
        $lines[] = $this->csvRow(['Dự án', 'Hạng mục', 'Tên khoản chi', 'Bên chi trả', 'Trạng thái', 'Ngân sách (VND)', 'Ước tính (VND)', 'Hợp đồng đã chốt (VND)', 'Tiền cọc (VND)', 'Đã thanh toán (VND)', 'Còn phải trả (VND)', 'Ghi chú']);

        foreach ($items as $item) {
            $committed = max(0, (int) ($item['committed_vnd'] ?? 0));
            $estimated = max(0, (int) ($item['estimated_vnd'] ?? 0));
            $deposit = max(0, (int) ($item['deposit_vnd'] ?? 0));
            $paid = max($deposit, max(0, (int) ($item['paid_vnd'] ?? 0)));
            $status = (string) ($item['status'] ?? 'planned');
            $outstanding = $status === 'cancelled' ? 0 : max(0, ($committed > 0 ? $committed : $estimated) - $paid);

            $lines[] = $this->csvRow([
                $projectName,
                self::DEFAULT_CATEGORIES[(string) ($item['category'] ?? '')]['label'] ?? (string) ($item['category'] ?? ''),
                (string) ($item['title'] ?? ''),
                (string) ($item['payer_side'] ?? 'couple'),
                $status,
                (string) max(0, (int) ($item['budgeted_vnd'] ?? 0)),
                (string) $estimated,
                (string) $committed,
                (string) $deposit,
                (string) $paid,
                (string) $outstanding,
                (string) ($item['notes'] ?? ''),
            ]);
        }

        return $bom.implode("\r\n", $lines)."\r\n";
    }

    /**
     * @param  list<string>  $cells
     */
    protected function csvRow(array $cells): string
    {
        return implode(',', array_map(static function (string $cell): string {
            $escaped = str_replace('"', '""', $cell);

            return '"'.$escaped.'"';
        }, $cells));
    }
}
