<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class InvitationTemplateService
{
    /**
     * Cache static in-memory để không đọc lặp lại file JSON trong cùng request.
     */
    protected static array $schemaCache = [];

    /**
     * Đọc Schema cấu hình của mẫu thiệp (Schema-Driven Template Engine).
     */
    public function getSchema(string $slug, string $version = 'v1'): array
    {
        $cacheKey = "{$slug}:{$version}";
        if (isset(self::$schemaCache[$cacheKey])) {
            return self::$schemaCache[$cacheKey];
        }

        $schemaPath = resource_path("views/client/invitations/{$slug}/schema.json");

        // Tương thích ngược nếu còn ở đường dẫn cũ
        if (! File::exists($schemaPath)) {
            $schemaPath = resource_path("invitation-templates/{$slug}/schema.json");
        }

        if (File::exists($schemaPath)) {
            $json = File::get($schemaPath);
            $schema = json_decode($json, true) ?? [];
            self::$schemaCache[$cacheKey] = $schema;

            return $schema;
        }

        // Nếu chưa có file schema.json riêng, tự sinh schema dựa theo template trong DB
        $template = InvitationTemplate::where('slug', $slug)->first();
        $fallbackSchema = $this->generateFallbackSchema($template);
        self::$schemaCache[$cacheKey] = $fallbackSchema;

        return $fallbackSchema;
    }

    /**
     * Sinh Schema mặc định dựa trên cấu hình template trong database (Backward Compatible).
     */
    public function generateFallbackSchema(?InvitationTemplate $template): array
    {
        $config = $template?->default_config ?? [];
        $requiredInputs = $config['required_inputs'] ?? ['basic_info', 'wedding_event'];
        $tier = $config['tier'] ?? 'free';

        $sections = [];

        // 1. Thông tin cô dâu chú rể (Bắt buộc với mọi mẫu)
        $sections[] = [
            'id' => 'couple',
            'title' => 'Thông tin Cô dâu & Chú rể',
            'icon' => 'heart',
            'description' => 'Tên đôi uyên ương và phụ mẫu hai bên',
            'fields' => [
                [
                    'key' => 'wedding.groom_name',
                    'label' => 'Họ và tên Chú rể',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Ví dụ: Nguyễn Minh Anh',
                    'max' => 100,
                ],
                [
                    'key' => 'wedding.bride_name',
                    'label' => 'Họ và tên Cô dâu',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Ví dụ: Trần Ngọc Hà',
                    'max' => 100,
                ],
                [
                    'key' => 'wedding.groom_parents',
                    'label' => 'Họ tên Phụ mẫu Nhà Trai',
                    'type' => 'text',
                    'required' => false,
                    'placeholder' => 'Ông: Nguyễn Văn A & Bà: Lê Thị B',
                ],
                [
                    'key' => 'wedding.bride_parents',
                    'label' => 'Họ tên Phụ mẫu Nhà Gái',
                    'type' => 'text',
                    'required' => false,
                    'placeholder' => 'Ông: Trần Văn C & Bà: Phạm Thị D',
                ],
            ],
        ];

        // 2. Thời gian và Địa điểm tổ chức
        $sections[] = [
            'id' => 'event',
            'title' => 'Thời gian & Sảnh tiệc cưới',
            'icon' => 'calendar',
            'description' => 'Ngày giờ làm lễ và định vị sảnh tiệc đón khách',
            'fields' => [
                [
                    'key' => 'wedding.wedding_date',
                    'label' => 'Ngày cử hành hôn lễ',
                    'type' => 'date',
                    'required' => true,
                ],
                [
                    'key' => 'wedding.venue_name',
                    'label' => 'Tên sảnh tiệc / Trung tâm hội nghị',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => 'Ví dụ: The Grand Ballroom - White Palace',
                ],
                [
                    'key' => 'wedding.venue_address',
                    'label' => 'Địa chỉ cụ thể sảnh tiệc',
                    'type' => 'textarea',
                    'required' => true,
                    'placeholder' => 'Ví dụ: 194 Hoàng Văn Thụ, Phường 9, Phú Nhuận, TP. HCM',
                ],
            ],
        ];

        // 3. Hình ảnh cưới (Tùy theo mẫu hỗ trợ 1 ảnh hay album)
        $hasGallery = in_array('gallery_photos', $requiredInputs) || in_array($tier, ['plus', 'pro']);
        $photoFields = [
            [
                'key' => 'wedding.cover_photo_url',
                'label' => 'Ảnh bìa thiệp cưới chính',
                'type' => 'image',
                'required' => in_array('cover_photo', $requiredInputs),
                'placeholder' => 'Đường dẫn ảnh bìa chất lượng cao',
            ],
        ];

        if ($hasGallery) {
            $photoFields[] = [
                'key' => 'wedding.extra_data.gallery',
                'label' => 'Album ảnh cưới (Tối đa 15 ảnh)',
                'type' => 'gallery',
                'required' => false,
                'max_items' => 15,
            ];
        }

        $sections[] = [
            'id' => 'media',
            'title' => 'Hình ảnh & Album cưới',
            'icon' => 'image',
            'description' => 'Ảnh bìa đại diện và album khoảnh khắc ngọt ngào',
            'fields' => $photoFields,
        ];

        // 4. Lịch trình ngày cưới & Chuyện tình yêu (Mẫu Plus & Pro)
        $hasTimeline = in_array('timeline', $requiredInputs) || $tier === 'pro';
        $hasStory = in_array('love_story', $requiredInputs) || in_array($tier, ['plus', 'pro']);

        if ($hasTimeline || $hasStory) {
            $storyFields = [];
            if ($hasStory) {
                $storyFields[] = [
                    'key' => 'wedding.story',
                    'label' => 'Câu chuyện tình yêu của chúng mình',
                    'type' => 'textarea',
                    'required' => false,
                    'placeholder' => 'Chia sẻ ngắn gọn hành trình từ ngày đầu gặp gỡ đến ngày chung đôi...',
                    'max' => 2000,
                ];
            }

            if ($hasTimeline) {
                $storyFields[] = [
                    'key' => 'wedding.extra_data.timeline',
                    'label' => 'Lịch trình các mốc thời gian trong ngày',
                    'type' => 'repeater',
                    'required' => false,
                    'max_items' => 6,
                    'item_structure' => [
                        ['key' => 'time', 'label' => 'Giờ (Ví dụ: 17:30)', 'type' => 'text'],
                        ['key' => 'title', 'label' => 'Nội dung (Ví dụ: Đón khách)', 'type' => 'text'],
                    ],
                ];
            }

            $sections[] = [
                'id' => 'story_timeline',
                'title' => 'Chuyện Tình Yêu & Lịch Trình',
                'icon' => 'clock',
                'description' => 'Mốc thời gian ngày cưới và tâm tình gửi đến quan khách',
                'fields' => $storyFields,
            ];
        }

        // 5. Quà mừng cưới VietQR & Dresscode (Nếu mẫu hỗ trợ)
        $hasBankQr = in_array('bank_qr', $requiredInputs) || in_array($tier, ['plus', 'pro']);
        if ($hasBankQr) {
            $sections[] = [
                'id' => 'banking',
                'title' => 'Mã QR Mừng Cưới & Dresscode',
                'icon' => 'gift',
                'description' => 'Tài khoản ngân hàng mừng cưới từ xa và màu trang phục gợi ý',
                'fields' => [
                    [
                        'key' => 'wedding.bank_name',
                        'label' => 'Ngân hàng thụ hưởng',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'Ví dụ: Vietcombank, Techcombank, MB Bank',
                    ],
                    [
                        'key' => 'wedding.bank_account_number',
                        'label' => 'Số tài khoản ngân hàng',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'Nhập chính xác số tài khoản',
                    ],
                    [
                        'key' => 'wedding.bank_account_name',
                        'label' => 'Tên chủ tài khoản',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'NGUYEN MINH ANH',
                    ],
                    [
                        'key' => 'wedding.extra_data.dresscode_colors',
                        'label' => 'Mã màu trang phục (Dresscode)',
                        'type' => 'text',
                        'required' => false,
                        'placeholder' => 'Be, Trắng, Vàng pastel',
                    ],
                ],
            ];
        }

        // 6. Tùy biến giao diện thiệp (Appearance)
        $sections[] = [
            'id' => 'appearance',
            'title' => 'Tùy Chỉnh Giao Diện Thiệp',
            'icon' => 'palette',
            'description' => 'Màu sắc chủ đạo và nhạc nền lãng mạn',
            'fields' => [
                [
                    'key' => 'invitation.custom_config.accent_color',
                    'label' => 'Màu nhấn chủ đạo',
                    'type' => 'color',
                    'default' => $config['accent_color'] ?? '#C5A880',
                    'required' => false,
                ],
                [
                    'key' => 'wedding.music_url',
                    'label' => 'Link nhạc nền đám cưới (MP3)',
                    'type' => 'text',
                    'required' => false,
                    'placeholder' => 'Link file âm thanh bài hát yêu thích',
                ],
            ],
        ];

        return [
            'slug' => $template?->slug ?? 'default',
            'version' => $template?->current_version ?? 'v1',
            'tier' => $tier,
            'sections' => $sections,
        ];
    }

    /**
     * Tạo quy tắc validate động dựa trên schema của mẫu.
     */
    public function getValidationRules(InvitationTemplate $template): array
    {
        $schema = $this->getSchema($template->slug, $template->current_version ?? 'v1');
        $rules = [
            'template_id' => 'required|exists:invitation_templates,id',
            'wedding.groom_name' => 'required|string|max:100',
            'wedding.bride_name' => 'required|string|max:100',
            'wedding.wedding_date' => 'required|date',
            'wedding.venue_name' => 'nullable|string|max:255',
            'wedding.venue_address' => 'nullable|string|max:500',
            'wedding.groom_parents' => 'nullable|string|max:255',
            'wedding.bride_parents' => 'nullable|string|max:255',
            'wedding.story' => 'nullable|string|max:3000',
            'wedding.cover_photo_url' => 'nullable|url|max:500',
            'wedding.music_url' => 'nullable|url|max:500',
            'wedding.bank_name' => 'nullable|string|max:100',
            'wedding.bank_account_number' => 'nullable|string|max:50',
            'wedding.bank_account_name' => 'nullable|string|max:100',
            'wedding.extra_data' => 'nullable|array',
            'wedding.extra_data.gallery' => 'nullable|array|max:20',
            'wedding.extra_data.gallery.*' => 'nullable|url|max:500',
            'wedding.extra_data.timeline' => 'nullable|array|max:10',
            'invitation.custom_config' => 'nullable|array',
        ];

        return $rules;
    }

    /**
     * Lưu thông tin thiệp cưới an toàn trong Transaction.
     * Tách bạch dữ liệu chung vào bảng weddings và cấu hình riêng vào invitations.
     */
    public function saveInvitation(User $user, int $templateId, array $data, ?int $invitationId = null): Invitation
    {
        return DB::transaction(function () use ($user, $templateId, $data, $invitationId) {
            $weddingData = $data['wedding'] ?? [];
            $extraData = $weddingData['extra_data'] ?? [];

            // Nếu người dùng đã có wedding trước đó thì cập nhật, nếu chưa thì tạo mới
            $existingInvitation = $invitationId ? Invitation::find($invitationId) : null;
            $wedding = $existingInvitation?->wedding;

            if (! $wedding) {
                $wedding = Wedding::create([
                    'user_id' => $user->id,
                    'groom_name' => $weddingData['groom_name'] ?? '',
                    'bride_name' => $weddingData['bride_name'] ?? '',
                    'groom_parents' => $weddingData['groom_parents'] ?? null,
                    'bride_parents' => $weddingData['bride_parents'] ?? null,
                    'wedding_date' => $weddingData['wedding_date'] ?? now()->addMonths(2)->format('Y-m-d'),
                    'venue_name' => $weddingData['venue_name'] ?? 'Trung Tâm Tiệc Cưới',
                    'venue_address' => $weddingData['venue_address'] ?? 'TP. Hồ Chí Minh',
                    'story' => $weddingData['story'] ?? null,
                    'cover_photo_url' => $weddingData['cover_photo_url'] ?? null,
                    'music_url' => $weddingData['music_url'] ?? null,
                    'bank_name' => $weddingData['bank_name'] ?? null,
                    'bank_account_number' => $weddingData['bank_account_number'] ?? null,
                    'bank_account_name' => $weddingData['bank_account_name'] ?? null,
                    'extra_data' => $extraData,
                ]);
            } else {
                // Cập nhật wedding hiện có
                $wedding->update([
                    'groom_name' => $weddingData['groom_name'] ?? $wedding->groom_name,
                    'bride_name' => $weddingData['bride_name'] ?? $wedding->bride_name,
                    'groom_parents' => $weddingData['groom_parents'] ?? $wedding->groom_parents,
                    'bride_parents' => $weddingData['bride_parents'] ?? $wedding->bride_parents,
                    'wedding_date' => $weddingData['wedding_date'] ?? $wedding->wedding_date,
                    'venue_name' => $weddingData['venue_name'] ?? $wedding->venue_name,
                    'venue_address' => $weddingData['venue_address'] ?? $wedding->venue_address,
                    'story' => $weddingData['story'] ?? $wedding->story,
                    'cover_photo_url' => $weddingData['cover_photo_url'] ?? $wedding->cover_photo_url,
                    'music_url' => $weddingData['music_url'] ?? $wedding->music_url,
                    'bank_name' => $weddingData['bank_name'] ?? $wedding->bank_name,
                    'bank_account_number' => $weddingData['bank_account_number'] ?? $wedding->bank_account_number,
                    'bank_account_name' => $weddingData['bank_account_name'] ?? $wedding->bank_account_name,
                    'extra_data' => array_merge($wedding->extra_data ?? [], $extraData),
                ]);
            }

            $template = InvitationTemplate::findOrFail($templateId);
            $customConfig = $data['invitation']['custom_config'] ?? [];

            if ($existingInvitation) {
                $existingInvitation->update([
                    'template_id' => $template->id,
                    'template_version' => $template->current_version ?? 'v1',
                    'custom_config' => array_merge($existingInvitation->custom_config ?? [], $customConfig),
                ]);

                return $existingInvitation;
            }

            // Tạo link thiệp mới với slug độc nhất có hậu tố kép chống trùng (ví dụ: dang-minh-quan-le-ai-nhu-edsra-94y9rwhfget)
            $slugBase = Str::slug($wedding->groom_name.'-'.$wedding->bride_name);
            if (empty($slugBase)) {
                $slugBase = 'thiep-cuoi';
            }
            $trimmedBase = Str::limit($slugBase, 70, '');

            do {
                $suffix = Str::lower(Str::random(5)).'-'.Str::lower(Str::random(11));
                $uniqueSlug = $trimmedBase.'-'.$suffix;
            } while (Invitation::where('slug', $uniqueSlug)->exists());

            $invitation = Invitation::create([
                'wedding_id' => $wedding->id,
                'template_id' => $template->id,
                'slug' => $uniqueSlug,
                'title' => 'Thiệp Cưới '.$wedding->groom_name.' & '.$wedding->bride_name,
                'version_type' => 'general',
                'template_version' => $template->current_version ?? 'v1',
                'custom_config' => $customConfig,
                'is_published' => true,
                'published_at' => now(),
                'expires_at' => now()->addDays(60),
            ]);

            return $invitation;
        });
    }
}
