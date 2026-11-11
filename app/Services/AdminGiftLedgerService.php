<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Wedding;
use App\Models\WeddingGift;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminGiftLedgerService
{
    /**
     * Che số tài khoản ngân hàng để bảo vệ quyền riêng tư tài chính (Mục 35 PRD V3).
     */
    public function maskAccountNumber(?string $accountNumber): string
    {
        if (empty($accountNumber)) {
            return '—';
        }

        $clean = trim($accountNumber);
        $len = strlen($clean);

        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        $lastFour = substr($clean, -4);
        $maskedLength = max(4, $len - 4);

        return str_repeat('*', $maskedLength).$lastFour;
    }

    /**
     * Trích xuất toàn bộ dữ liệu sổ mừng cưới, KPI tài chính và danh sách quà mừng.
     */
    public function getLedgerData(array $filters = []): array
    {
        $selectedWeddingId = ! empty($filters['wedding_id']) ? (int) $filters['wedding_id'] : null;

        $activeWedding = null;
        if ($selectedWeddingId) {
            $activeWedding = Wedding::with(['guests:id,wedding_id,name,group_name,table_name'])->find($selectedWeddingId);
        }

        $allWeddings = Wedding::select('id', 'groom_name', 'bride_name', 'wedding_date')
            ->orderByDesc('wedding_date')
            ->limit(50)
            ->get();

        // 1. Thống kê KPI tài chính quà mừng
        $statsQuery = WeddingGift::query();
        if ($activeWedding) {
            $statsQuery->where('wedding_id', $activeWedding->id);
        }

        $totalAmount = (int) $statsQuery->sum('amount');
        $totalCount = (clone $statsQuery)->count();

        $cashAmount = (int) (clone $statsQuery)->where('payment_method', 'cash')->sum('amount');
        $cashCount = (clone $statsQuery)->where('payment_method', 'cash')->count();

        $transferAmount = (int) (clone $statsQuery)->where('payment_method', 'bank_transfer')->sum('amount');
        $transferCount = (clone $statsQuery)->where('payment_method', 'bank_transfer')->count();

        $otherCount = (clone $statsQuery)->where('payment_method', 'other')->count();

        $acknowledgedCount = (clone $statsQuery)->where('is_acknowledged', true)->count();
        $acknowledgedRate = $totalCount > 0 ? round(($acknowledgedCount / $totalCount) * 100, 1) : 0;

        $stats = [
            'total_amount' => $totalAmount,
            'total_count' => $totalCount,
            'cash_amount' => $cashAmount,
            'cash_count' => $cashCount,
            'transfer_amount' => $transferAmount,
            'transfer_count' => $transferCount,
            'other_count' => $otherCount,
            'acknowledged_count' => $acknowledgedCount,
            'acknowledged_rate' => $acknowledgedRate,
        ];

        // 2. Thông tin tài khoản nhận quà mừng cưới (Masked)
        $bankInfo = null;
        if ($activeWedding) {
            $hasBankConfig = ! empty($activeWedding->bank_account_number) && ! empty($activeWedding->bank_name);
            $bankInfo = [
                'bank_name' => $activeWedding->bank_name ?: 'Chưa thiết lập ngân hàng',
                'bank_account_number_masked' => $this->maskAccountNumber($activeWedding->bank_account_number),
                'bank_account_name' => $activeWedding->bank_account_name ?: 'Chưa cập nhật tên',
                'has_bank_config' => $hasBankConfig,
            ];
        }

        // 3. Danh sách khách mời của đám cưới phục vụ chọn nhanh trong modal
        $availableGuests = $activeWedding
            ? $activeWedding->guests()->select('id', 'name', 'group_name', 'table_name')->orderBy('name')->get()
            : collect();

        // 4. Lọc và phân trang sổ mừng cưới
        $gifts = $this->getFilteredGifts($filters, $activeWedding);

        return [
            'activeWedding' => $activeWedding,
            'allWeddings' => $allWeddings,
            'stats' => $stats,
            'bankInfo' => $bankInfo,
            'availableGuests' => $availableGuests,
            'gifts' => $gifts,
        ];
    }

    /**
     * Lọc danh sách quà mừng theo các tiêu chí và phân trang.
     */
    protected function getFilteredGifts(array $filters = [], ?Wedding $wedding = null): LengthAwarePaginator
    {
        $query = WeddingGift::query()->with([
            'wedding:id,user_id,groom_name,bride_name,wedding_date',
            'guest:id,name,group_name,table_name,phone',
        ]);

        if ($wedding) {
            $query->where('wedding_id', $wedding->id);
        }

        // 1. Tìm kiếm từ khóa
        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function ($q) use ($keyword) {
                $q->where('sender_name', 'like', "%{$keyword}%")
                    ->orWhere('sender_group', 'like', "%{$keyword}%")
                    ->orWhere('notes', 'like', "%{$keyword}%")
                    ->orWhereHas('guest', function ($gq) use ($keyword) {
                        $gq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('wedding', function ($wq) use ($keyword) {
                        $wq->where('groom_name', 'like', "%{$keyword}%")
                            ->orWhere('bride_name', 'like', "%{$keyword}%");
                    });
            });
        }

        // 2. Lọc phương thức thanh toán
        if (! empty($filters['payment_method']) && in_array($filters['payment_method'], ['cash', 'bank_transfer', 'other'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        // 3. Lọc trạng thái cảm ơn
        if (! empty($filters['status'])) {
            if ($filters['status'] === 'acknowledged') {
                $query->where('is_acknowledged', true);
            } elseif ($filters['status'] === 'pending') {
                $query->where('is_acknowledged', false);
            }
        }

        // 4. Lọc nhóm người gửi
        if (! empty($filters['sender_group'])) {
            $query->where('sender_group', $filters['sender_group']);
        }

        return $query->latest('id')->paginate(25)->withQueryString();
    }

    /**
     * Ghi nhận món quà mừng cưới mới vào sổ mừng cưới.
     */
    public function recordGift(int $weddingId, array $data, ?string $operatorName = null): WeddingGift
    {
        return DB::transaction(function () use ($weddingId, $data, $operatorName) {
            $wedding = Wedding::findOrFail($weddingId);

            $amount = max(0, (int) ($data['amount'] ?? 0));
            $guestId = ! empty($data['guest_id']) ? (int) $data['guest_id'] : null;

            $senderName = trim((string) $data['sender_name']);
            $senderGroup = ! empty($data['sender_group']) ? trim((string) $data['sender_group']) : null;

            // Nếu có liên kết với khách mời và chưa nhập nhóm, lấy nhóm của khách
            if ($guestId && empty($senderGroup)) {
                $guest = Guest::find($guestId);
                if ($guest) {
                    $senderGroup = $guest->group_name;
                }
            }

            $gift = $wedding->gifts()->create([
                'guest_id' => $guestId,
                'sender_name' => $senderName,
                'sender_group' => $senderGroup ?: 'Khách chung',
                'amount' => $amount,
                'payment_method' => $data['payment_method'] ?? 'cash',
                'notes' => ! empty($data['notes']) ? trim((string) $data['notes']) : null,
                'is_acknowledged' => ! empty($data['is_acknowledged']),
            ]);

            AuditLogger::log(
                'record_wedding_gift',
                'WeddingGift',
                $gift->id,
                "Ghi nhận quà mừng từ \"{$gift->sender_name}\" ({$gift->formatted_amount}) cho tiệc cưới #{$weddingId}.",
                [
                    'wedding_id' => $weddingId,
                    'operator' => $operatorName,
                    'amount' => $amount,
                    'payment_method' => $gift->payment_method,
                ]
            );

            return $gift;
        });
    }

    /**
     * Cập nhật thông tin món quà mừng cưới.
     */
    public function updateGift(int $giftId, array $data, ?string $operatorName = null): WeddingGift
    {
        return DB::transaction(function () use ($giftId, $data, $operatorName) {
            $gift = WeddingGift::lockForUpdate()->findOrFail($giftId);

            $amount = max(0, (int) ($data['amount'] ?? $gift->amount));
            $guestId = array_key_exists('guest_id', $data) ? ($data['guest_id'] ? (int) $data['guest_id'] : null) : $gift->guest_id;

            $gift->update([
                'guest_id' => $guestId,
                'sender_name' => trim((string) ($data['sender_name'] ?? $gift->sender_name)),
                'sender_group' => ! empty($data['sender_group']) ? trim((string) $data['sender_group']) : $gift->sender_group,
                'amount' => $amount,
                'payment_method' => $data['payment_method'] ?? $gift->payment_method,
                'notes' => array_key_exists('notes', $data) ? trim((string) $data['notes']) : $gift->notes,
                'is_acknowledged' => array_key_exists('is_acknowledged', $data) ? (bool) $data['is_acknowledged'] : $gift->is_acknowledged,
            ]);

            AuditLogger::log(
                'update_wedding_gift',
                'WeddingGift',
                $gift->id,
                "Cập nhật quà mừng #{$gift->id} từ \"{$gift->sender_name}\" ({$gift->formatted_amount}).",
                [
                    'wedding_id' => $gift->wedding_id,
                    'operator' => $operatorName,
                    'amount' => $amount,
                    'payment_method' => $gift->payment_method,
                ]
            );

            return $gift;
        });
    }

    /**
     * Chuyển đổi trạng thái đã cảm ơn / chưa cảm ơn của món quà.
     */
    public function toggleAcknowledgement(int $giftId, ?string $operatorName = null): array
    {
        return DB::transaction(function () use ($giftId, $operatorName) {
            $gift = WeddingGift::lockForUpdate()->findOrFail($giftId);
            $newStatus = ! $gift->is_acknowledged;

            $gift->update(['is_acknowledged' => $newStatus]);

            $statusText = $newStatus ? 'Đã cảm ơn' : 'Chưa cảm ơn';
            $message = "Đã cập nhật trạng thái quà mừng từ \"{$gift->sender_name}\": {$statusText}.";

            AuditLogger::log(
                'toggle_gift_acknowledgement',
                'WeddingGift',
                $gift->id,
                $message,
                [
                    'wedding_id' => $gift->wedding_id,
                    'operator' => $operatorName,
                    'is_acknowledged' => $newStatus,
                ]
            );

            return [
                'success' => true,
                'gift' => $gift,
                'is_acknowledged' => $newStatus,
                'message' => $message,
            ];
        });
    }

    /**
     * Đánh dấu đã cảm ơn hàng loạt quà mừng được chọn.
     */
    public function batchAcknowledge(array $giftIds, ?string $operatorName = null): int
    {
        return DB::transaction(function () use ($giftIds, $operatorName) {
            $count = WeddingGift::whereIn('id', $giftIds)
                ->where('is_acknowledged', false)
                ->update(['is_acknowledged' => true]);

            AuditLogger::log(
                'batch_acknowledge_gifts',
                'WeddingGift',
                0,
                "Đánh dấu đã cảm ơn hàng loạt {$count} món quà mừng.",
                [
                    'operator' => $operatorName,
                    'gift_ids' => $giftIds,
                    'count' => $count,
                ]
            );

            return $count;
        });
    }

    /**
     * Xóa vĩnh viễn một món quà mừng cưới.
     */
    public function deleteGift(int $giftId, ?string $operatorName = null): bool
    {
        return DB::transaction(function () use ($giftId, $operatorName) {
            $gift = WeddingGift::findOrFail($giftId);
            $sender = $gift->sender_name;
            $weddingId = $gift->wedding_id;
            $amount = $gift->amount;

            $gift->delete();

            AuditLogger::log(
                'delete_wedding_gift',
                'WeddingGift',
                $giftId,
                "Xóa quà mừng từ \"{$sender}\" (".number_format($amount, 0, ',', '.')." đ) trên tiệc cưới #{$weddingId}.",
                [
                    'wedding_id' => $weddingId,
                    'operator' => $operatorName,
                    'sender' => $sender,
                ]
            );

            return true;
        });
    }

    /**
     * Xóa hàng loạt món quà mừng cưới được chọn.
     */
    public function batchDelete(array $giftIds, ?string $operatorName = null): int
    {
        return DB::transaction(function () use ($giftIds, $operatorName) {
            $count = WeddingGift::whereIn('id', $giftIds)->delete();

            AuditLogger::log(
                'batch_delete_gifts',
                'WeddingGift',
                0,
                "Xóa hàng loạt {$count} món quà mừng.",
                [
                    'operator' => $operatorName,
                    'gift_ids' => $giftIds,
                    'count' => $count,
                ]
            );

            return $count;
        });
    }

    /**
     * Xuất toàn bộ sổ mừng cưới định dạng CSV UTF-8 BOM.
     */
    public function exportLedgerCsv(?Wedding $wedding = null, array $filters = []): Response
    {
        $query = WeddingGift::query()->with([
            'wedding:id,groom_name,bride_name',
            'guest:id,name,group_name,table_name',
        ]);

        if ($wedding) {
            $query->where('wedding_id', $wedding->id);
        }

        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (! empty($filters['status'])) {
            if ($filters['status'] === 'acknowledged') {
                $query->where('is_acknowledged', true);
            } elseif ($filters['status'] === 'pending') {
                $query->where('is_acknowledged', false);
            }
        }

        $gifts = $query->latest('id')->get();
        $filename = 'so-mung-cuoi-'.($wedding ? Str::slug("{$wedding->groom_name}-{$wedding->bride_name}") : 'toan-san').'-'.now()->format('Ymd-His').'.csv';

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'STT',
            'Mã Quà',
            'Tiệc Cưới',
            'Người Mừng Cưới',
            'Nhóm Quan Khách',
            'Bàn Tiệc',
            'Số Tiền (VNĐ)',
            'Hình Thức Mừng',
            'Trạng Thái Cảm Ơn',
            'Ghi Chú',
            'Thời Gian Ghi Nhận',
        ]);

        $index = 1;
        foreach ($gifts as $g) {
            $coupleName = $g->wedding ? "{$g->wedding->groom_name} & {$g->wedding->bride_name}" : '—';
            $tableName = $g->guest?->table_name ?? '—';
            $ackLabel = $g->is_acknowledged ? 'Đã cảm ơn' : 'Chưa cảm ơn';

            fputcsv($handle, CsvSanitizerService::row([
                $index++,
                $g->id,
                $coupleName,
                $g->sender_name,
                $g->sender_group ?: 'Khách chung',
                $tableName,
                $g->amount,
                $g->payment_method_label,
                $ackLabel,
                $g->notes ?: '',
                $g->created_at ? $g->created_at->format('d/m/Y H:i:s') : '—',
            ]));
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
