<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\Wedding;
use Illuminate\Support\Str;

class CeremonyForkService
{
    /**
     * Danh sách 4 chế độ nhân bản thiệp cưới chuẩn phong tục hôn lễ Việt Nam (Mục 2.2 DOCX & Mục 25 PRD V3).
     *
     * @return array<string, array{id: string, name: string, ceremony_title: string, icon: string, description: string, badge_class: string}>
     */
    public static function getForkModes(): array
    {
        return [
            'bride_side' => [
                'id' => 'bride_side',
                'name' => 'Nhà Gái — Lễ Vu Quy',
                'ceremony_title' => 'LỄ VU QUY',
                'icon' => '🌸',
                'description' => 'Ưu tiên hiển thị nhà gái & cô dâu trước, đổi tiêu đề thành Lễ Vu Quy, cấu hình giờ tiệc và tài khoản mừng cưới bên nhà gái.',
                'badge_class' => 'mayhen_badge_rose',
            ],
            'groom_side' => [
                'id' => 'groom_side',
                'name' => 'Nhà Trai — Lễ Thành Hôn',
                'ceremony_title' => 'LỄ THÀNH HÔN',
                'icon' => '👑',
                'description' => 'Ưu tiên hiển thị nhà trai & chú rể trước, đổi tiêu đề thành Lễ Thành Hôn, cấu hình giờ tiệc và tài khoản mừng cưới bên nhà trai.',
                'badge_class' => 'mayhen_badge_gold',
            ],
            'party_side' => [
                'id' => 'party_side',
                'name' => 'Tiệc Báo Hỷ — Bạn Hữu',
                'ceremony_title' => 'TIỆC BÁO HỶ',
                'icon' => '🕊️',
                'description' => 'Dành cho tiệc thân mật cùng bạn bè, đồng nghiệp tại nơi làm việc/sinh sống xa quê hương.',
                'badge_class' => 'mayhen_badge_navy',
            ],
            'backup_copy' => [
                'id' => 'backup_copy',
                'name' => 'Bản Sao Lưu Dự Phòng',
                'ceremony_title' => 'BẢN SAO LƯU',
                'icon' => '📋',
                'description' => 'Tạo bản sao lưu nguyên trạng toàn bộ thông tin để thử nghiệm giao diện hoặc chỉnh sửa an toàn.',
                'badge_class' => 'mayhen_badge_neutral',
            ],
        ];
    }

    /**
     * Nhân bản thiệp cưới và phân nhánh hôn lễ thông minh.
     *
     * @param  array<string, mixed>  $options
     * @return array{wedding: Wedding, invitation: Invitation, slug: string, message: string}
     */
    public static function fork(Wedding $sourceWedding, Invitation $sourceInvitation, array $options): array
    {
        $mode = $options['mode'] ?? 'bride_side';
        $separateProject = ! empty($options['separate_project']);
        $customSlugSuffix = trim((string) ($options['slug_suffix'] ?? ''));

        $modes = self::getForkModes();
        $modeInfo = $modes[$mode] ?? $modes['bride_side'];

        // 1. Xác định Slug mới duy nhất
        $baseSlug = Str::limit($sourceInvitation->slug, 50, '');
        $suffix = match ($mode) {
            'bride_side' => 'nha-gai-vu-quy',
            'groom_side' => 'nha-trai-thanh-hon',
            'party_side' => 'tiec-bao-hy',
            default => 'ban-sao',
        };

        if ($customSlugSuffix !== '') {
            $suffix = Str::slug($customSlugSuffix);
        }

        $newSlug = "{$baseSlug}-{$suffix}-".Str::lower(Str::random(4));

        // Đảm bảo slug chưa tồn tại
        while (Invitation::where('slug', $newSlug)->exists()) {
            $newSlug = "{$baseSlug}-{$suffix}-".Str::lower(Str::random(5));
        }

        // 2. Trích xuất extra_data hiện tại
        $extra = is_array($sourceWedding->extra_data)
            ? $sourceWedding->extra_data
            : (json_decode($sourceWedding->extra_data ?? '[]', true) ?: []);

        // 3. Tinh chỉnh dữ liệu theo chế độ nhân bản (Văn hóa cưới Việt Nam)
        $newExtra = $extra;
        $newTitle = $sourceInvitation->title ?: 'Thiệp Cưới';
        $targetGroomName = $sourceWedding->groom_name;
        $targetBrideName = $sourceWedding->bride_name;
        $targetVenueName = $sourceWedding->venue_name;
        $targetVenueAddress = $sourceWedding->venue_address;
        $targetWeddingDate = $sourceWedding->wedding_date;

        if ($mode === 'bride_side') {
            $newTitle = "Thiệp Vu Quy — {$targetBrideName} & {$targetGroomName}";
            $newExtra['ceremony_type'] = 'vu_quy';
            $newExtra['ceremony_label'] = 'LỄ VU QUY';
            $newExtra['display_order_couple'] = 'bride_first'; // Cô dâu trước
            $newExtra['display_order_family'] = 'bride_first'; // Nhà gái trước

            // Đổi địa chỉ tiệc sang nhà gái nếu có thông tin
            if (! empty($options['venue_name'])) {
                $targetVenueName = $options['venue_name'];
            } elseif (! empty($extra['bride_party_venue'])) {
                $targetVenueName = $extra['bride_party_venue'];
            }

            if (! empty($options['venue_address'])) {
                $targetVenueAddress = $options['venue_address'];
            } elseif (! empty($extra['bride_party_address'])) {
                $targetVenueAddress = $extra['bride_party_address'];
            }

            if (! empty($options['wedding_date'])) {
                $targetWeddingDate = $options['wedding_date'];
            }
        } elseif ($mode === 'groom_side') {
            $newTitle = "Thiệp Thành Hôn — {$targetGroomName} & {$targetBrideName}";
            $newExtra['ceremony_type'] = 'thanh_hon';
            $newExtra['ceremony_label'] = 'LỄ THÀNH HÔN';
            $newExtra['display_order_couple'] = 'groom_first'; // Chú rể trước
            $newExtra['display_order_family'] = 'groom_first'; // Nhà trai trước

            if (! empty($options['venue_name'])) {
                $targetVenueName = $options['venue_name'];
            }
            if (! empty($options['venue_address'])) {
                $targetVenueAddress = $options['venue_address'];
            }
            if (! empty($options['wedding_date'])) {
                $targetWeddingDate = $options['wedding_date'];
            }
        } elseif ($mode === 'party_side') {
            $newTitle = "Thiệp Báo Hỷ — {$targetGroomName} & {$targetBrideName}";
            $newExtra['ceremony_type'] = 'bao_hy';
            $newExtra['ceremony_label'] = 'TIỆC BÁO HỶ';

            if (! empty($options['venue_name'])) {
                $targetVenueName = $options['venue_name'];
            }
            if (! empty($options['venue_address'])) {
                $targetVenueAddress = $options['venue_address'];
            }
            if (! empty($options['wedding_date'])) {
                $targetWeddingDate = $options['wedding_date'];
            }
        } else {
            $newTitle = "{$newTitle} (Bản Sao)";
        }

        // 4. Phân nhánh dự án (Full Fork Wedding hoặc Dùng chung Wedding)
        $targetWedding = $sourceWedding;
        if ($separateProject) {
            $targetWedding = $sourceWedding->replicate();
            $targetWedding->groom_name = $targetGroomName;
            $targetWedding->bride_name = $targetBrideName;
            $targetWedding->venue_name = $targetVenueName;
            $targetWedding->venue_address = $targetVenueAddress;
            $targetWedding->wedding_date = $targetWeddingDate;
            $targetWedding->extra_data = $newExtra;
            $targetWedding->save();
        }

        // 5. Tạo thiệp cưới mới
        $newInvitation = $sourceInvitation->replicate([
            'slug',
            'is_published',
            'published_at',
            'view_count',
        ]);

        $newInvitation->wedding_id = $targetWedding->id;
        $newInvitation->slug = $newSlug;
        $newInvitation->title = $newTitle;
        $newInvitation->is_published = false;
        $newInvitation->published_at = null;
        $newInvitation->view_count = 0;

        // Lưu tag nhận diện vào custom_config
        $config = is_array($sourceInvitation->custom_config)
            ? $sourceInvitation->custom_config
            : (json_decode($sourceInvitation->custom_config ?? '[]', true) ?: []);

        $config['ceremony_fork'] = [
            'forked_from_id' => $sourceInvitation->id,
            'mode' => $mode,
            'ceremony_title' => $modeInfo['ceremony_title'],
            'forked_at' => now()->toIso8601String(),
        ];

        $newInvitation->custom_config = $config;
        $newInvitation->save();

        AuditLogger::log(
            'CEREMONY_FORKED',
            'Invitation',
            $newInvitation->id,
            "Nhân bản thiệp cưới theo chế độ [{$modeInfo['name']}] từ thiệp gốc #{$sourceInvitation->id}",
            [
                'source_invitation_id' => $sourceInvitation->id,
                'source_wedding_id' => $sourceWedding->id,
                'new_invitation_id' => $newInvitation->id,
                'new_wedding_id' => $targetWedding->id,
                'mode' => $mode,
                'new_slug' => $newSlug,
                'separate_project' => $separateProject,
            ]
        );

        return [
            'wedding' => $targetWedding,
            'invitation' => $newInvitation,
            'slug' => $newSlug,
            'message' => "Đã nhân bản thiệp cưới thành công cho [{$modeInfo['name']}]!",
        ];
    }
}
