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
     * Danh sách 6 Section chuẩn hóa của hệ thống Mây Hẹn (Tối đa 6 phần, không thừa).
     */
    public const CANONICAL_SECTION_ORDER = [
        'couple',
        'groom_family',
        'bride_family',
        'album',
        'timeline',
        'gift',
    ];

    /**
     * Định nghĩa chuẩn mực 6 phần của một mẫu thiệp cưới Mây Hẹn.
     */
    public static function getCanonicalSections(): array
    {
        return [
            'couple' => [
                'id' => 'couple',
                'title' => 'Đôi Uyên Ương',
                'short_title' => 'Đôi Uyên Ương',
                'icon' => 'heart',
                'description' => 'Thông tin cô dâu, chú rể, ngày cưới và ảnh đại diện',
                'fields' => [
                    [
                        'key' => 'wedding.groom_name',
                        'label' => 'Họ và tên Chú rể',
                        'type' => 'text',
                        'required' => true,
                        'default' => 'Tràng Đức',
                        'placeholder' => 'Ví dụ: Tràng Đức',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.bride_name',
                        'label' => 'Họ và tên Cô dâu',
                        'type' => 'text',
                        'required' => true,
                        'default' => 'Ngọc Phượng',
                        'placeholder' => 'Ví dụ: Ngọc Phượng',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_photo_url',
                        'label' => 'Ảnh chân dung Chú rể (Tỉ lệ chuẩn 3:4)',
                        'type' => 'image',
                        'required' => false,
                        'default' => 'assets/images/groom.webp',
                        'placeholder' => 'Đường dẫn ảnh Chú rể',
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_photo_url',
                        'label' => 'Ảnh chân dung Cô dâu (Tỉ lệ chuẩn 3:4)',
                        'type' => 'image',
                        'required' => false,
                        'default' => 'assets/images/bride.webp',
                        'placeholder' => 'Đường dẫn ảnh Cô dâu',
                    ],
                    [
                        'key' => 'wedding.wedding_date',
                        'label' => 'Ngày cử hành hôn lễ',
                        'type' => 'date',
                        'required' => true,
                        'default' => '2027-03-07',
                        'placeholder' => 'YYYY-MM-DD',
                    ],
                    [
                        'key' => 'wedding.extra_data.wedding_lunar_date',
                        'label' => 'Ngày âm lịch cử hành hôn lễ',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Nhằm ngày 12 tháng 11 năm Bính Ngọ',
                        'placeholder' => 'Ví dụ: Nhằm ngày 12 tháng 11 năm Bính Ngọ',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.cover_photo_url',
                        'label' => 'Ảnh bìa đại diện thiệp',
                        'type' => 'image',
                        'required' => false,
                        'default' => 'assets/images/cover.webp',
                        'placeholder' => 'Đường dẫn ảnh bìa',
                    ],
                    [
                        'key' => 'wedding.extra_data.party_time',
                        'label' => 'Giờ tổ chức tiệc cưới chính',
                        'type' => 'time',
                        'required' => false,
                        'default' => '18:00',
                        'placeholder' => 'Ví dụ: 18:00',
                        'max' => 50,
                    ],
                    [
                        'key' => 'wedding.extra_data.party_venue',
                        'label' => 'Tên nơi tổ chức / Trung tâm tiệc cưới',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Trung tâm Tiệc cưới Ngân Hà',
                        'placeholder' => 'Ví dụ: Trung tâm Tiệc cưới Ngân Hà',
                        'max' => 150,
                    ],
                    [
                        'key' => 'wedding.extra_data.event_party_address',
                        'label' => 'Địa chỉ nơi tổ chức tiệc cưới',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Số 88 Trần Duy Hưng, Cầu Giấy, Hà Nội',
                        'placeholder' => 'Địa chỉ chi tiết sảnh tiệc hoặc nhà hàng tiệc cưới...',
                        'max' => 300,
                    ],
                    [
                        'key' => 'wedding.extra_data.save_date_photo_url',
                        'label' => 'Ảnh thiệp mời tiệc cưới (Save The Date)',
                        'type' => 'image',
                        'required' => false,
                        'default' => 'assets/images/savedate.webp',
                        'placeholder' => 'Đường dẫn ảnh thiệp tiệc cưới',
                    ],
                    [
                        'key' => 'wedding.extra_data.couple_quote',
                        'label' => 'Lời ngỏ / Thông điệp tình yêu',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Tình yêu không phải là nhìn nhau, mà là cùng nhau nhìn về một hướng.',
                        'placeholder' => 'Chia sẻ ngắn gọn lời ngỏ gửi đến quan khách...',
                        'max' => 500,
                    ],
                ],
            ],
            'groom_family' => [
                'id' => 'groom_family',
                'title' => 'Nhà Trai',
                'short_title' => 'Nhà Trai',
                'icon' => 'home',
                'description' => 'Thông tin phụ mẫu, ngày giờ và địa chỉ hôn lễ nhà trai',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.groom_father',
                        'label' => 'Họ tên Cha (Nhà Trai)',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Ông Nguyễn Thăng Long',
                        'placeholder' => 'Ví dụ: Ông Nguyễn Thăng Long',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_mother',
                        'label' => 'Họ tên Mẹ (Nhà Trai)',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Bà Nguyễn Thị Mai',
                        'placeholder' => 'Ví dụ: Bà Nguyễn Thị Mai',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_party_time',
                        'label' => 'Giờ tổ chức hôn lễ / tiệc Nhà Trai',
                        'type' => 'time',
                        'required' => false,
                        'default' => '11:00',
                        'placeholder' => 'Ví dụ: 11:00',
                        'max' => 50,
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_party_date',
                        'label' => 'Ngày tổ chức hôn lễ Nhà Trai',
                        'type' => 'date',
                        'required' => false,
                        'default' => '2027-03-07',
                        'placeholder' => 'YYYY-MM-DD',
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_party_lunar',
                        'label' => 'Ngày âm lịch Nhà Trai',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Ngày 29 tháng 01 năm Đinh Mùi',
                        'placeholder' => 'Ví dụ: Ngày 29 tháng 01 năm Đinh Mùi',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_party_address',
                        'label' => 'Địa chỉ hôn lễ / Tiệc cưới Nhà Trai',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Tư gia Nhà Trai - Xã Tân Dân, Huyện Phú Xuyên, Hà Nội',
                        'placeholder' => 'Địa chỉ chi tiết sảnh tiệc hoặc tư gia Nhà Trai',
                        'max' => 300,
                    ],
                ],
            ],
            'bride_family' => [
                'id' => 'bride_family',
                'title' => 'Nhà Gái',
                'short_title' => 'Nhà Gái',
                'icon' => 'home',
                'description' => 'Thông tin phụ mẫu, ngày giờ và địa chỉ hôn lễ nhà gái',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.bride_father',
                        'label' => 'Họ tên Cha (Nhà Gái)',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Ông Trần Đình Thắng',
                        'placeholder' => 'Ví dụ: Ông Trần Đình Thắng',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_mother',
                        'label' => 'Họ tên Mẹ (Nhà Gái)',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Bà Lê Thị Hồng',
                        'placeholder' => 'Ví dụ: Bà Lê Thị Hồng',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_party_time',
                        'label' => 'Giờ tổ chức hôn lễ / tiệc Nhà Gái',
                        'type' => 'time',
                        'required' => false,
                        'default' => '09:00',
                        'placeholder' => 'Ví dụ: 09:00',
                        'max' => 50,
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_party_date',
                        'label' => 'Ngày tổ chức hôn lễ Nhà Gái',
                        'type' => 'date',
                        'required' => false,
                        'default' => '2027-03-07',
                        'placeholder' => 'YYYY-MM-DD',
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_party_lunar',
                        'label' => 'Ngày âm lịch Nhà Gái',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Ngày 29 tháng 01 năm Đinh Mùi',
                        'placeholder' => 'Ví dụ: Ngày 29 tháng 01 năm Đinh Mùi',
                        'max' => 100,
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_party_address',
                        'label' => 'Địa chỉ hôn lễ / Tiệc cưới Nhà Gái',
                        'type' => 'textarea',
                        'required' => false,
                        'default' => 'Tư gia Nhà Gái - Phường Cống Vị, Ba Đình, Hà Nội',
                        'placeholder' => 'Địa chỉ chi tiết sảnh tiệc hoặc tư gia Nhà Gái',
                        'max' => 300,
                    ],
                ],
            ],
            'album' => [
                'id' => 'album',
                'title' => 'Ảnh Cưới (10 Ảnh)',
                'short_title' => 'Ảnh Cưới',
                'icon' => 'image',
                'description' => 'Bộ sưu tập 10 ảnh cưới ngọt ngào và đáng nhớ',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.gallery',
                        'label' => 'Album 10 Ảnh Cưới',
                        'type' => 'gallery',
                        'required' => false,
                        'max_items' => 10,
                    ],
                ],
            ],
            'timeline' => [
                'id' => 'timeline',
                'title' => 'Lịch Trình Hôn Lễ',
                'short_title' => 'Lịch Trình',
                'icon' => 'clock',
                'description' => 'Các mốc thời gian diễn ra hôn lễ và tiệc mừng',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.timeline',
                        'label' => 'Lịch trình các mốc thời gian',
                        'type' => 'repeater',
                        'required' => false,
                        'max_items' => 6,
                        'default' => [
                            ['time' => '08:30', 'title' => 'Đón Khách'],
                            ['time' => '09:30', 'title' => 'Cử Hành Hôn Lễ'],
                            ['time' => '10:30', 'title' => 'Khai Tiệc Mừng'],
                            ['time' => '11:30', 'title' => 'Chụp Ảnh Kỷ Niệm'],
                        ],
                        'item_structure' => [
                            ['key' => 'time', 'label' => 'Giờ', 'type' => 'time'],
                            ['key' => 'title', 'label' => 'Nội dung', 'type' => 'text'],
                        ],
                    ],
                ],
            ],
            'gift' => [
                'id' => 'gift',
                'title' => 'Hộp Mừng Cưới',
                'short_title' => 'Mừng Cưới',
                'icon' => 'gift',
                'description' => 'Số tài khoản ngân hàng và mã QR mừng cưới',
                'fields' => [
                    [
                        'key' => 'wedding.extra_data.groom_bank_name',
                        'label' => 'Ngân hàng Chú rể',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Vietcombank',
                        'placeholder' => 'Ví dụ: Vietcombank',
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_bank_account',
                        'label' => 'Số tài khoản Chú rể',
                        'type' => 'text',
                        'required' => false,
                        'default' => '1012345678',
                        'placeholder' => 'Nhập số tài khoản Chú rể',
                    ],
                    [
                        'key' => 'wedding.extra_data.groom_bank_holder',
                        'label' => 'Tên chủ tài khoản (Chú rể)',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'NGUYEN TRANG DUC',
                        'placeholder' => 'Ví dụ: NGUYEN TRANG DUC',
                    ],
                    [
                        'key' => 'wedding.extra_data.qr_groom_image',
                        'label' => 'Mã QR Mừng Cưới Chú Rể',
                        'type' => 'image',
                        'required' => false,
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_bank_name',
                        'label' => 'Ngân hàng Cô dâu',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'Techcombank',
                        'placeholder' => 'Ví dụ: Techcombank',
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_bank_account',
                        'label' => 'Số tài khoản Cô dâu',
                        'type' => 'text',
                        'required' => false,
                        'default' => '1903888899',
                        'placeholder' => 'Nhập số tài khoản Cô dâu',
                    ],
                    [
                        'key' => 'wedding.extra_data.bride_bank_holder',
                        'label' => 'Tên chủ tài khoản (Cô dâu)',
                        'type' => 'text',
                        'required' => false,
                        'default' => 'LE NGOC PHUONG',
                        'placeholder' => 'Ví dụ: LE NGOC PHUONG',
                    ],
                    [
                        'key' => 'wedding.extra_data.qr_bride_image',
                        'label' => 'Mã QR Mừng Cưới Cô Dâu',
                        'type' => 'image',
                        'required' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * Chuẩn hóa Schema đảm bảo tuân thủ đúng tối đa 6 phần (Không thừa, được phép thiếu).
     */
    public function normalizeSchema(array $rawSchema, ?string $slug = null): array
    {
        $canonical = self::getCanonicalSections();
        $supportedKeys = $rawSchema['supported_sections'] ?? null;

        // Nếu chưa có supported_sections, lấy từ các section ids hiện có trong file JSON
        if (! is_array($supportedKeys) || empty($supportedKeys)) {
            $existingSectionIds = array_column($rawSchema['sections'] ?? [], 'id');
            // Ánh xạ các section cũ sang 6 key chuẩn
            $supportedKeys = [];
            foreach ($existingSectionIds as $sid) {
                $mapped = match ($sid) {
                    'couple' => 'couple',
                    'groom_events', 'groom_family' => 'groom_family',
                    'bride_events', 'bride_family' => 'bride_family',
                    'events', 'event' => ['groom_family', 'bride_family'],
                    'gallery', 'album', 'gallery_and_photos' => 'album',
                    'timeline', 'story_and_timeline' => 'timeline',
                    'banking', 'gift', 'gift_and_music' => 'gift',
                    default => null,
                };

                if (is_array($mapped)) {
                    foreach ($mapped as $m) {
                        if (! in_array($m, $supportedKeys, true)) {
                            $supportedKeys[] = $m;
                        }
                    }
                } elseif ($mapped && ! in_array($mapped, $supportedKeys, true)) {
                    $supportedKeys[] = $mapped;
                }
            }
        }

        // Lọc chỉ giữ các section thuộc 6 canonical section IDs
        $validSupported = array_values(array_intersect(self::CANONICAL_SECTION_ORDER, (array) $supportedKeys));
        if (empty($validSupported)) {
            $validSupported = self::CANONICAL_SECTION_ORDER;
        }

        // Tạo mảng lookup các field overrides từ raw schema nếu có
        $rawSectionsById = [];
        foreach ($rawSchema['sections'] ?? [] as $sec) {
            if (isset($sec['id'])) {
                $rawSectionsById[$sec['id']] = $sec;
            }
        }

        // Dựng danh sách sections chuẩn theo đúng thứ tự logic (1 -> 6)
        $normalizedSections = [];
        foreach ($validSupported as $sectionId) {
            if (! isset($canonical[$sectionId])) {
                continue;
            }

            $secDef = $canonical[$sectionId];

            // Nếu trong file JSON có cung cấp title/description/fields riêng, hợp nhất một cách an toàn
            $rawSec = $rawSectionsById[$sectionId] ?? null;
            if ($rawSec) {
                if (! empty($rawSec['title'])) {
                    $secDef['title'] = $rawSec['title'];
                }
                if (! empty($rawSec['short_title'])) {
                    $secDef['short_title'] = $rawSec['short_title'];
                }
                if (! empty($rawSec['description'])) {
                    $secDef['description'] = $rawSec['description'];
                }

                // Cập nhật defaults cho các fields từ raw schema nếu trùng key
                $rawFieldsByKey = [];
                foreach ($rawSec['fields'] ?? [] as $rf) {
                    $k = $rf['key'] ?? $rf['name'] ?? null;
                    if ($k) {
                        $rawFieldsByKey[$k] = $rf;
                    }
                }

                $processedKeys = [];
                $mergedFields = [];

                foreach ($secDef['fields'] as $fDef) {
                    $fKey = $fDef['key'];
                    $processedKeys[] = $fKey;
                    if (isset($rawFieldsByKey[$fKey])) {
                        $rf = $rawFieldsByKey[$fKey];
                        $fDef = array_merge($fDef, $rf);
                    }
                    $mergedFields[] = $fDef;
                }

                // Bảo tồn toàn bộ các fields riêng do mẫu thiệp định nghĩa mà canonical chưa có
                foreach ($rawSec['fields'] ?? [] as $rf) {
                    $k = $rf['key'] ?? $rf['name'] ?? null;
                    if ($k && ! in_array($k, $processedKeys, true)) {
                        $mergedFields[] = $rf;
                        $processedKeys[] = $k;
                    }
                }

                $secDef['fields'] = $mergedFields;
            }

            $normalizedSections[] = $secDef;
        }

        return [
            'slug' => $rawSchema['slug'] ?? ($slug ?? 'default'),
            'version' => $rawSchema['version'] ?? 'v1',
            'name' => $rawSchema['name'] ?? 'Mẫu Thiệp Mây Hẹn',
            'tier' => $rawSchema['tier'] ?? 'free',
            'supported_sections' => $validSupported,
            'sections' => $normalizedSections,
        ];
    }

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
            $rawSchema = json_decode($json, true) ?? [];
            $schema = $this->normalizeSchema($rawSchema, $slug);
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
        $canonical = self::getCanonicalSections();

        return [
            'slug' => $template?->slug ?? 'default',
            'version' => $template?->current_version ?? 'v1',
            'name' => $template?->name ?? 'Mẫu Thiệp Mây Hẹn',
            'tier' => $template?->default_config['tier'] ?? 'free',
            'supported_sections' => self::CANONICAL_SECTION_ORDER,
            'sections' => array_values($canonical),
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
            'wedding.cover_photo_url' => 'nullable|string',
            'wedding.music_url' => 'nullable|url|max:500',
            'wedding.bank_name' => 'nullable|string|max:100',
            'wedding.bank_account_number' => 'nullable|string|max:50',
            'wedding.bank_account_name' => 'nullable|string|max:100',
            'wedding.extra_data' => 'nullable|array',
            'wedding.extra_data.gallery' => 'nullable|array|max:20',
            'wedding.extra_data.gallery.*' => 'nullable|string',
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

    /**
     * Lưu bản nháp định kỳ kèm kiểm tra khóa lạc quan (Optimistic Concurrency Control).
     *
     * @param  array<string, mixed>  $draftConfig
     * @param  array<string, mixed>  $weddingDelta
     * @return array{success: bool, conflict: bool, new_revision_id?: int, current_revision?: int, saved_at?: string, message?: string}
     */
    public function saveDraftWithLock(Invitation $invitation, int $clientRevision, array $draftConfig, array $weddingDelta = []): array
    {
        return DB::transaction(function () use ($invitation, $clientRevision, $draftConfig, $weddingDelta) {
            // Khóa dòng để đọc revision mới nhất chống race condition
            $currentInvitation = Invitation::where('id', $invitation->id)->lockForUpdate()->first();
            if (! $currentInvitation) {
                return [
                    'success' => false,
                    'conflict' => true,
                    'message' => 'Không tìm thấy thiệp cưới tương ứng.',
                ];
            }

            $currentRevision = (int) ($currentInvitation->revision_id ?? 0);

            // Kiểm tra xung đột phiên bản (Optimistic Concurrency Check)
            if ($clientRevision < $currentRevision) {
                return [
                    'success' => false,
                    'conflict' => true,
                    'current_revision' => $currentRevision,
                    'message' => 'Phiên làm việc đã cũ hoặc đã được chỉnh sửa từ một tab khác. Vui lòng làm mới trang.',
                ];
            }

            // Khử blob URLs trong dữ liệu nếu có
            $sanitize = function (mixed $value) use (&$sanitize): mixed {
                if (is_array($value)) {
                    return array_map($sanitize, $value);
                }
                if (is_string($value) && str_starts_with(trim($value), 'blob:')) {
                    return null;
                }

                return $value;
            };

            $cleanDraft = $sanitize($draftConfig);
            $cleanWeddingDelta = $sanitize($weddingDelta);

            // Cập nhật wedding nếu có thay đổi delta
            $wedding = $currentInvitation->wedding;
            if ($wedding && ! empty($cleanWeddingDelta)) {
                $allowedFields = [
                    'groom_name', 'bride_name', 'groom_parents', 'bride_parents',
                    'wedding_date', 'venue_name', 'venue_address', 'story',
                    'cover_photo_url', 'music_url', 'bank_name',
                    'bank_account_number', 'bank_account_name',
                ];

                $fieldsToUpdate = [];
                foreach ($allowedFields as $f) {
                    if (array_key_exists($f, $cleanWeddingDelta)) {
                        $fieldsToUpdate[$f] = $cleanWeddingDelta[$f];
                    }
                }

                if (isset($cleanWeddingDelta['extra_data']) && is_array($cleanWeddingDelta['extra_data'])) {
                    $fieldsToUpdate['extra_data'] = array_merge($wedding->extra_data ?? [], $cleanWeddingDelta['extra_data']);
                }

                if (! empty($fieldsToUpdate)) {
                    $wedding->update($fieldsToUpdate);
                }
            }

            $newRevision = $currentRevision + 1;
            $mergedDraft = array_merge($currentInvitation->draft_config ?? [], $cleanDraft);

            $currentInvitation->update([
                'draft_config' => $mergedDraft,
                'revision_id' => $newRevision,
                'last_autosaved_at' => now(),
            ]);

            return [
                'success' => true,
                'conflict' => false,
                'new_revision_id' => $newRevision,
                'saved_at' => now()->toIso8601String(),
            ];
        });
    }
}
