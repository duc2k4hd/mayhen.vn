<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\User;
use App\Models\WeddingWish;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WeddingInteractiveEndingService
{
    /**
     * Danh sách phong cách thiết kế Lời Cảm Ơn Kết Thiệp.
     */
    public const ENDING_STYLES = [
        'intimate_editorial' => [
            'name' => 'Tri Ân Tạp Chí Cảm Xúc',
            'desc' => 'Bố cục chữ Playfair nghệ thuật, khung ảnh kỷ niệm tròn khép lại thiệp, nền ấm áp Quiet Luxury.',
            'badge' => 'Khuyên dùng',
        ],
        'royal_seal' => [
            'name' => 'Con Dấu Hoàng Gia & Chữ Lồng Monogram',
            'desc' => 'Đậm chất lễ nghi châu Âu với tem sáp vàng cổ điển, viền chỉ vàng tinh xảo và chữ ký trang trọng.',
            'badge' => 'Quý phái',
        ],
        'minimalist_modern' => [
            'name' => 'Tối Giản Hiện Đại & Tinh Tế',
            'desc' => 'Đường nét thanh mảnh, khoảng trắng khoáng đạt, tôn vinh trọn vẹn thông điệp cảm ơn sâu sắc.',
            'badge' => 'Tối giản',
        ],
    ];

    /**
     * Mẫu lời cảm ơn tri ân đúc kết từ văn hóa cưới Việt Nam.
     */
    public const PRESET_THANK_YOU_MESSAGES = [
        'gratitude' => [
            'title' => 'Trân Trọng & Biết Ơn',
            'message' => 'Sự hiện diện cùng những lời chúc phúc ấm áp của Quý khách là món quà vô giá nhất đối với chúng tôi trong ngày khởi đầu tổ ấm mới.',
        ],
        'warmth' => [
            'title' => 'Thân Mật & Ấm Cúng',
            'message' => 'Cảm ơn bạn đã luôn đồng hành, sẻ chia và trở thành một phần tươi đẹp, ý nghĩa nhất trong câu chuyện tình yêu của hai đứa mình.',
        ],
        'poetic' => [
            'title' => 'Lãng Mạn & Sâu Lắng',
            'message' => 'Một ngày ngập tràn niềm vui và hạnh phúc trọn vẹn nhờ có sự hiện diện của bạn. Xin chân thành cảm ơn vì đã cùng chúng tôi viết nên kỷ niệm đẹp này.',
        ],
        'minimal' => [
            'title' => 'Tối Giản & Chân Thành',
            'message' => 'Cảm ơn Quý khách và các bạn đã dành tình cảm trân quý cho gia đình chúng tôi.',
        ],
    ];

    /**
     * Các kiểu nền nghệ thuật bảng ký tên điện tử (Pure CSS / SVG — Tuyệt đối KHÔNG dùng ảnh người).
     */
    public const SIGNATURE_BG_STYLES = [
        'stardust' => 'Bụi Sao Lấp Lánh (Stardust Canvas)',
        'wax_seal' => 'Tem Sáp Hoàng Gia (Royal Wax Seal Stamp)',
        'gold_mesh' => 'Lưới Vàng Cung Đình (Muted Gold Mesh)',
    ];

    /**
     * Lấy danh sách phong cách kết thiệp.
     */
    public static function getPresetEndingStyles(): array
    {
        return self::ENDING_STYLES;
    }

    /**
     * Lấy các mẫu lời cảm ơn mẫu.
     */
    public static function getPresetThankYouMessages(): array
    {
        return self::PRESET_THANK_YOU_MESSAGES;
    }

    /**
     * Phân giải cấu hình Section 10 cho thiệp cưới.
     */
    public static function resolveInteractiveEndingConfig(Invitation $invitation): array
    {
        $customConfig = $invitation->custom_config ?? [];
        $saved = $customConfig['interactive_ending'] ?? [];

        $wedding = $invitation->wedding;
        $coupleName = ($wedding?->groom_name ?? 'Chú Rể').' & '.($wedding?->bride_name ?? 'Cô Dâu');

        $default = [
            'enabled' => true,
            'ending_style' => 'intimate_editorial',
            'ending_kicker' => 'Lời Cảm Ơn',
            'ending_title' => 'Cảm Ơn Bạn Vì Đã Đến',
            'thank_you_message' => 'Sự hiện diện cùng những lời chúc phúc ấm áp của Quý khách là món quà vô giá nhất đối với chúng tôi trong ngày khởi đầu tổ ấm mới.',
            'couple_signature_text' => $coupleName,
            'ending_photo_url' => $wedding?->hero_photo_url ?? '/assets/client/images/sample-wedding-1.webp',

            // Sổ ký tên điện tử (Digital Signature Canvas)
            'signature_enabled' => true,
            'signature_title' => 'Sổ Ký Tên Điện Tử',
            'signature_subtitle' => 'Lưu lại nét bút và chữ ký kỷ niệm thân thương cho hai đứa mình',
            'signature_bg_style' => 'stardust',
            'pen_colors' => [
                ['name' => 'Vàng Cung Điện', 'hex' => '#C5A880'],
                ['name' => 'Than Chì Charcoal', 'hex' => '#2C2C2C'],
                ['name' => 'Xanh Đêm Sâu', 'hex' => '#1B2A38'],
                ['name' => 'Đỏ Rượu Vang', 'hex' => '#85182A'],
            ],

            // Sổ lưu bút chúc phúc (Guestbook / Wishes)
            'wishes_enabled' => true,
            'wishes_title' => 'Sổ Lưu Bút Chúc Phúc',
            'wishes_subtitle' => 'Gửi gắm những lời nhắn nhủ, chúc phúc tốt đẹp nhất tới đôi uyên ương',
            'require_approval' => false,
            'allow_private_wishes' => true,
        ];

        return array_replace_recursive($default, $saved);
    }

    /**
     * Cập nhật cấu hình Section 10 và đồng bộ vào Wedding model.
     */
    public static function updateInteractiveEndingConfig(Invitation $invitation, array $data, ?User $user = null): array
    {
        $current = self::resolveInteractiveEndingConfig($invitation);

        $newConfig = [
            'enabled' => isset($data['enabled']) ? (bool) $data['enabled'] : $current['enabled'],
            'ending_style' => in_array($data['ending_style'] ?? '', array_keys(self::ENDING_STYLES), true) ? $data['ending_style'] : $current['ending_style'],
            'ending_kicker' => trim($data['ending_kicker'] ?? $current['ending_kicker']),
            'ending_title' => trim($data['ending_title'] ?? $current['ending_title']),
            'thank_you_message' => trim($data['thank_you_message'] ?? $current['thank_you_message']),
            'couple_signature_text' => trim($data['couple_signature_text'] ?? $current['couple_signature_text']),
            'ending_photo_url' => trim($data['ending_photo_url'] ?? $current['ending_photo_url']),

            'signature_enabled' => isset($data['signature_enabled']) ? (bool) $data['signature_enabled'] : $current['signature_enabled'],
            'signature_title' => trim($data['signature_title'] ?? $current['signature_title']),
            'signature_subtitle' => trim($data['signature_subtitle'] ?? $current['signature_subtitle']),
            'signature_bg_style' => in_array($data['signature_bg_style'] ?? '', array_keys(self::SIGNATURE_BG_STYLES), true) ? $data['signature_bg_style'] : $current['signature_bg_style'],
            'pen_colors' => $current['pen_colors'],

            'wishes_enabled' => isset($data['wishes_enabled']) ? (bool) $data['wishes_enabled'] : $current['wishes_enabled'],
            'wishes_title' => trim($data['wishes_title'] ?? $current['wishes_title']),
            'wishes_subtitle' => trim($data['wishes_subtitle'] ?? $current['wishes_subtitle']),
            'require_approval' => isset($data['require_approval']) ? (bool) $data['require_approval'] : false,
            'allow_private_wishes' => isset($data['allow_private_wishes']) ? (bool) $data['allow_private_wishes'] : true,
        ];

        // 1. Lưu vào custom_config của Invitation
        $customConfig = $invitation->custom_config ?? [];
        $customConfig['interactive_ending'] = $newConfig;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        // 2. Đồng bộ vào Wedding model
        $wedding = $invitation->wedding;
        if ($wedding) {
            $extraData = $wedding->extra_data ?? [];
            $extraData['interactive_ending'] = $newConfig;
            $wedding->extra_data = $extraData;
            $wedding->save();
        }

        // 3. Ghi nhận Audit Log
        if (class_exists(AuditLogger::class)) {
            AuditLogger::log(
                'INTERACTIVE_ENDING_UPDATED',
                'Invitation',
                $invitation->id,
                "Cập nhật Sổ Ký Tên & Lời Cảm Ơn cho thiệp #{$invitation->id} ({$newConfig['ending_style']})",
                [
                    'invitation_id' => $invitation->id,
                    'wedding_id' => $wedding?->id,
                    'ending_style' => $newConfig['ending_style'],
                    'signature_enabled' => $newConfig['signature_enabled'],
                    'wishes_enabled' => $newConfig['wishes_enabled'],
                    'updated_by' => $user?->id,
                ]
            );
        }

        return $newConfig;
    }

    /**
     * Lưu chữ ký điện tử từ Canvas (Data URL Base64) thành file ảnh PNG.
     */
    public static function saveSignatureData(Invitation $invitation, string $guestName, string $signatureDataUrl, ?int $wishId = null): string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $signatureDataUrl, $type)) {
            $data = substr($signatureDataUrl, strpos($signatureDataUrl, ',') + 1);
            $type = strtolower($type[1]); // png

            $data = base64_decode($data);
            if ($data === false) {
                throw new \InvalidArgumentException('Dữ liệu chữ ký không hợp lệ.');
            }
        } else {
            throw new \InvalidArgumentException('Định dạng hình ảnh chữ ký không đúng.');
        }

        $folder = 'invitations/'.$invitation->id.'/signatures';
        $fileName = 'sig_'.Str::slug($guestName).'_'.Str::random(8).'.'.$type;
        $path = $folder.'/'.$fileName;

        Storage::disk('public')->put($path, $data);
        $url = Storage::disk('public')->url($path);

        // Nếu gắn với một lời chúc cụ thể, cập nhật vào bảng wedding_wishes
        if ($wishId && $invitation->wedding_id) {
            WeddingWish::where('wedding_id', $invitation->wedding_id)
                ->where('id', $wishId)
                ->update(['signature_url' => $url]);
        }

        return $url;
    }

    /**
     * Kiểm duyệt lời chúc trong Studio (Ghim, Duyệt/Ẩn, Trả lời, Xóa).
     */
    public static function moderateWish(Invitation $invitation, int $wishId, string $action, ?string $replyText = null): array
    {
        $wish = WeddingWish::where('wedding_id', $invitation->wedding_id)
            ->where('id', $wishId)
            ->firstOrFail();

        switch ($action) {
            case 'pin':
                $wish->is_pinned = ! $wish->is_pinned;
                $wish->save();
                $msg = $wish->is_pinned ? 'Đã ghim lời chúc ý nghĩa lên đầu sổ lưu bút.' : 'Đã bỏ ghim lời chúc.';
                break;

            case 'approve':
                $wish->is_approved = true;
                $wish->save();
                $msg = 'Đã phê duyệt hiển thị lời chúc công khai.';
                break;

            case 'hide':
                $wish->is_approved = false;
                $wish->save();
                $msg = 'Đã ẩn lời chúc khỏi thiệp mời.';
                break;

            case 'reply':
                $wish->reply = trim($replyText ?? '');
                $wish->replied_at = now();
                $wish->save();
                $msg = 'Đã lưu lời cảm ơn phản hồi của cô dâu chú rể.';
                break;

            case 'delete':
                $wish->delete();
                $msg = 'Đã xóa lời chúc khỏi hệ thống.';
                break;

            default:
                throw new \InvalidArgumentException('Hành động không hợp lệ.');
        }

        return [
            'success' => true,
            'message' => $msg,
            'wish' => $wish,
        ];
    }

    /**
     * Xuất danh sách lời chúc ra file CSV chuẩn UTF-8 tiếng Việt.
     */
    public static function exportWishesToCsv(Invitation $invitation): string
    {
        $wishes = WeddingWish::where('wedding_id', $invitation->wedding_id)
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->get();

        $output = fopen('php://temp', 'r+');

        // Ghi UTF-8 BOM để Excel hiển thị đúng tiếng Việt có dấu
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, [
            'ID',
            'Người Chúc',
            'Nội Dung Lời Chúc',
            'Trạng Thái Duyệt',
            'Ghim',
            'Kín Đáo',
            'Phản Hồi Từ Cặp Đôi',
            'Có Chữ Ký',
            'Thời Gian Gửi',
        ]);

        foreach ($wishes as $wish) {
            fputcsv($output, [
                $wish->id,
                $wish->name,
                $wish->message,
                $wish->is_approved ? 'Đã duyệt' : 'Chờ duyệt/Ẩn',
                $wish->is_pinned ? 'Có' : 'Không',
                $wish->is_private ? 'Riêng tư' : 'Công khai',
                $wish->reply ?? '',
                ! empty($wish->signature_url) ? 'Có' : 'Không',
                $wish->created_at ? $wish->created_at->format('d/m/Y H:i:s') : '',
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $csvContent;
    }
}
