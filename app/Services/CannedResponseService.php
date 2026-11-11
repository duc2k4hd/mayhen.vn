<?php

namespace App\Services;

use App\Models\CannedResponse;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CannedResponseService
{
    /**
     * Khởi tạo các mẫu phản hồi nhanh mặc định chuẩn Mây Hẹn Atelier nếu DB chưa có.
     */
    public function seedDefaultResponsesIfEmpty(): void
    {
        if (CannedResponse::exists()) {
            return;
        }

        $defaults = [
            [
                'title' => 'Âm thanh không tự phát trên iPhone Safari',
                'shortcut' => ':safari',
                'category' => CannedResponse::CATEGORY_TEMPLATE,
                'body' => 'Chào {customer_name}, trên các thiết bị iPhone/iPad, trình duyệt Safari của Apple có chính sách bảo mật ngăn website tự ý phát âm thanh khi chưa có tương tác chạm từ người dùng. Bạn hoàn toàn yên tâm nhé: ngay khi quan khách bấm vào nút "Mở Thiệp" hoặc chạm nhẹ vào bất kỳ điểm nào trên thiệp cưới {wedding_title}, nhạc nền du dương sẽ tự động cất lên ngay lập tức.',
                'is_active' => true,
                'usage_count' => 18,
            ],
            [
                'title' => 'Quên mã PIN sảnh tiệc / Cần hỗ trợ điểm danh ngày cưới',
                'shortcut' => ':qrpin',
                'category' => CannedResponse::CATEGORY_TECHNICAL,
                'body' => 'Chào {customer_name}, đội ngũ Atelier Mây Hẹn đã kiểm tra hệ thống tiếp tân cho tiệc cưới {wedding_title}. Để xử lý gấp tại sảnh tiệc, bạn có thể sử dụng màn hình Rescue Cockpit ngày cưới trên hệ thống hoặc đăng nhập lại tài khoản chủ tiệc. Toàn bộ quan khách quét mã QR đều được điểm danh tức thời mà không lo bị nghẽn mạng.',
                'is_active' => true,
                'usage_count' => 12,
            ],
            [
                'title' => 'Đổi sảnh tiệc hoặc địa chỉ đón tiếp giờ chót',
                'shortcut' => ':venue',
                'category' => CannedResponse::CATEGORY_INVITATION,
                'body' => 'Chào {customer_name}, Mây Hẹn đã hỗ trợ bạn cập nhật thông tin địa điểm sảnh tiệc mới trực tiếp vào hệ thống thiệp cưới {wedding_title}. Toàn bộ quan khách khi truy cập link thiệp cưới hoặc quét mã QR trên phong bì sẽ thấy ngay địa chỉ và bản đồ chỉ đường Google Maps mới nhất mà bạn không cần phải in ấn lại thiệp giấy.',
                'is_active' => true,
                'usage_count' => 8,
            ],
            [
                'title' => 'Đối soát kích hoạt gói dịch vụ qua VietQR / SePay',
                'shortcut' => ':vietqr',
                'category' => CannedResponse::CATEGORY_BILLING,
                'body' => 'Chào {customer_name}, Mây Hẹn đã hoàn tất việc đối chiếu mã giao dịch thanh toán chuyển khoản trên hệ thống ngân hàng liên quan đến ticket {ticket_code} và đã kích hoạt thành công gói dịch vụ cho bạn. Bạn có thể tiếp tục tùy chỉnh và công bố thiệp cưới ngay bây giờ nhé.',
                'is_active' => true,
                'usage_count' => 24,
            ],
            [
                'title' => 'Gia hạn thời gian lưu trữ thiệp cưới kỷ niệm',
                'shortcut' => ':extend',
                'category' => CannedResponse::CATEGORY_BILLING,
                'body' => 'Chào {customer_name}, gói lưu trữ thiệp cưới của bạn đã được gia hạn thêm thành công. Toàn bộ album ảnh, sổ ký tên chúc phúc và video kỷ niệm của {wedding_title} sẽ được bảo lưu vĩnh viễn trên hạ tầng đám mây Mây Hẹn Atelier.',
                'is_active' => true,
                'usage_count' => 6,
            ],
        ];

        foreach ($defaults as $item) {
            CannedResponse::create($item);
        }
    }

    /**
     * Thống kê 4 thẻ KPI chỉ số kho mẫu phản hồi nhanh.
     *
     * @return array{total: int, active: int, total_usage: int, top_category: string}
     */
    public function getCannedKpis(): array
    {
        $this->seedDefaultResponsesIfEmpty();

        $total = CannedResponse::count();
        $active = CannedResponse::active()->count();
        $totalUsage = (int) CannedResponse::sum('usage_count');

        $topCategoryRecord = CannedResponse::selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->orderByDesc('count')
            ->first();

        $topCategory = $topCategoryRecord
            ? (CannedResponse::CATEGORIES[$topCategoryRecord->category]['label'] ?? 'Khác')
            : 'Mẫu Thiệp & Âm Nhạc';

        return [
            'total' => $total,
            'active' => $active,
            'total_usage' => $totalUsage,
            'top_category' => $topCategory,
        ];
    }

    /**
     * Lấy danh sách mẫu phản hồi có phân trang, lọc và tìm kiếm.
     */
    public function getFilteredResponses(?string $category = null, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $this->seedDefaultResponsesIfEmpty();

        $query = CannedResponse::with('creator')->latest('id');

        if (! empty($category) && array_key_exists($category, CannedResponse::CATEGORIES)) {
            $query->where('category', $category);
        }

        if (! empty($search)) {
            $term = trim($search);
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('shortcut', 'like', "%{$term}%")
                    ->orWhere('body', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Tạo một mẫu phản hồi nhanh mới.
     *
     * @param  array<string, mixed>  $data
     */
    public function createResponse(array $data, User $creator): CannedResponse
    {
        $canned = CannedResponse::create([
            'title' => trim((string) $data['title']),
            'shortcut' => ! empty($data['shortcut']) ? trim((string) $data['shortcut']) : null,
            'category' => $data['category'] ?? CannedResponse::CATEGORY_GENERAL,
            'body' => trim((string) $data['body']),
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : true,
            'created_by' => $creator->id,
        ]);

        AuditLogger::log(
            action: 'canned_response.created',
            targetType: 'canned_response',
            targetId: $canned->id,
            description: "Tạo mẫu phản hồi nhanh \"{$canned->title}\" ({$canned->shortcut}) bởi {$creator->name}.",
            details: [
                'canned_id' => $canned->id,
                'shortcut' => $canned->shortcut,
                'category' => $canned->category,
                'creator_id' => $creator->id,
            ]
        );

        return $canned;
    }

    /**
     * Cập nhật mẫu phản hồi nhanh.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateResponse(CannedResponse $canned, array $data, User $editor): CannedResponse
    {
        $canned->update([
            'title' => trim((string) $data['title']),
            'shortcut' => ! empty($data['shortcut']) ? trim((string) $data['shortcut']) : null,
            'category' => $data['category'] ?? $canned->category,
            'body' => trim((string) $data['body']),
            'is_active' => isset($data['is_active']) ? (bool) $data['is_active'] : $canned->is_active,
        ]);

        AuditLogger::log(
            action: 'canned_response.updated',
            targetType: 'canned_response',
            targetId: $canned->id,
            description: "Cập nhật mẫu phản hồi nhanh \"{$canned->title}\" bởi {$editor->name}.",
            details: [
                'canned_id' => $canned->id,
                'editor_id' => $editor->id,
            ]
        );

        return $canned;
    }

    /**
     * Xóa mẫu phản hồi nhanh.
     */
    public function deleteResponse(CannedResponse $canned, User $admin): bool
    {
        $cannedId = $canned->id;
        $title = $canned->title;

        $canned->delete();

        AuditLogger::log(
            action: 'canned_response.deleted',
            targetType: 'canned_response',
            targetId: $cannedId,
            description: "Xóa mẫu phản hồi nhanh \"{$title}\" bởi {$admin->name}.",
            details: [
                'canned_id' => $cannedId,
                'admin_id' => $admin->id,
            ]
        );

        return true;
    }

    /**
     * Chuyển đổi trạng thái Bật / Tắt của mẫu.
     */
    public function toggleResponse(CannedResponse $canned, User $admin): CannedResponse
    {
        $canned->update(['is_active' => ! $canned->is_active]);

        $statusStr = $canned->is_active ? 'kích hoạt' : 'tạm dừng';

        AuditLogger::log(
            action: 'canned_response.toggled',
            targetType: 'canned_response',
            targetId: $canned->id,
            description: "Đã {$statusStr} mẫu phản hồi nhanh \"{$canned->title}\" bởi {$admin->name}.",
            details: [
                'canned_id' => $canned->id,
                'is_active' => $canned->is_active,
            ]
        );

        return $canned;
    }

    /**
     * Ghi nhận và tăng biến đếm số lượt sử dụng.
     */
    public function incrementUsage(int|CannedResponse $canned): void
    {
        if (is_int($canned)) {
            $canned = CannedResponse::find($canned);
        }

        if ($canned) {
            $canned->increment('usage_count');
            $canned->update(['last_used_at' => now()]);
        }
    }

    /**
     * Lấy toàn bộ mẫu phản hồi đang active và render sẵn nội dung đã merge cho ticket cụ thể.
     *
     * @return array<int, array{id: int, title: string, shortcut: ?string, category: string, category_label: string, category_badge: string, raw_body: string, merged_body: string, usage_count: int}>
     */
    public function getActiveResponsesForTicket(?SupportTicket $ticket = null): array
    {
        $this->seedDefaultResponsesIfEmpty();

        $cannedResponses = CannedResponse::active()
            ->popular()
            ->get();

        $output = [];
        foreach ($cannedResponses as $item) {
            $merged = $item->renderMergedBody($ticket);
            $output[] = [
                'id' => $item->id,
                'title' => $item->title,
                'shortcut' => $item->shortcut,
                'category' => $item->category,
                'category_label' => $item->category_label,
                'category_badge' => $item->category_badge_class,
                'raw_body' => $item->body,
                'merged_body' => $merged,
                'reply_template' => $merged,
                'usage_count' => $item->usage_count,
            ];
        }

        return $output;
    }
}
