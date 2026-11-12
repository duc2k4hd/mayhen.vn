<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WeddingGiftRegistryService
{
    /**
     * Danh mục 20 ngân hàng phổ biến nhất Việt Nam kèm mã BIN chuyển khoản NAPAS 247.
     * Dùng để sinh mã VietQR chuẩn xác tức thì.
     */
    public const VIETNAMESE_BANKS = [
        ['code' => 'VCB', 'bin' => '970436', 'short_name' => 'Vietcombank', 'name' => 'Ngân hàng Ngoại thương Việt Nam'],
        ['code' => 'TCB', 'bin' => '970407', 'short_name' => 'Techcombank', 'name' => 'Ngân hàng Kỹ thương Việt Nam'],
        ['code' => 'MB', 'bin' => '970422', 'short_name' => 'MBBank', 'name' => 'Ngân hàng Quân đội'],
        ['code' => 'BIDV', 'bin' => '970418', 'short_name' => 'BIDV', 'name' => 'Ngân hàng Đầu tư và Phát triển'],
        ['code' => 'CTG', 'bin' => '970415', 'short_name' => 'VietinBank', 'name' => 'Ngân hàng Công thương Việt Nam'],
        ['code' => 'ACB', 'bin' => '970416', 'short_name' => 'ACB', 'name' => 'Ngân hàng Á Châu'],
        ['code' => 'VPB', 'bin' => '970432', 'short_name' => 'VPBank', 'name' => 'Ngân hàng Việt Nam Thịnh Vượng'],
        ['code' => 'TPB', 'bin' => '970423', 'short_name' => 'TPBank', 'name' => 'Ngân hàng Tiên Phong'],
        ['code' => 'STB', 'bin' => '970403', 'short_name' => 'Sacombank', 'name' => 'Ngân hàng Sài Gòn Thương Tín'],
        ['code' => 'VIB', 'bin' => '970441', 'short_name' => 'VIB', 'name' => 'Ngân hàng Quốc tế'],
        ['code' => 'HDB', 'bin' => '970437', 'short_name' => 'HDBank', 'name' => 'Ngân hàng Phát triển TP.HCM'],
        ['code' => 'SHB', 'bin' => '970443', 'short_name' => 'SHB', 'name' => 'Ngân hàng Sài Gòn - Hà Nội'],
        ['code' => 'OCB', 'bin' => '970448', 'short_name' => 'OCB', 'name' => 'Ngân hàng Phương Đông'],
        ['code' => 'MSB', 'bin' => '970426', 'short_name' => 'MSB', 'name' => 'Ngân hàng Hàng Hải'],
        ['code' => 'SEAB', 'bin' => '970440', 'short_name' => 'SeABank', 'name' => 'Ngân hàng Đông Nam Á'],
        ['code' => 'LPB', 'bin' => '970449', 'short_name' => 'LPBank', 'name' => 'Ngân hàng Lộc Phát Việt Nam'],
        ['code' => 'VBA', 'bin' => '970405', 'short_name' => 'Agribank', 'name' => 'Ngân hàng Nông nghiệp & PT Nông thôn'],
        ['code' => 'NAB', 'bin' => '970428', 'short_name' => 'Nam A Bank', 'name' => 'Ngân hàng Nam Á'],
        ['code' => 'BAB', 'bin' => '970409', 'short_name' => 'Bac A Bank', 'name' => 'Ngân hàng Bắc Á'],
        ['code' => 'CAKE', 'bin' => '546034', 'short_name' => 'Cake by VPBank', 'name' => 'Ngân hàng số Cake'],
    ];

    /**
     * 3 Phong cách hiển thị Hộp Mừng Cưới nghệ thuật.
     */
    public const BOX_STYLES = [
        'ribbon_box' => [
            'id' => 'ribbon_box',
            'name' => 'Hộp Quà Ruy Băng Lụa 3D',
            'description' => 'Hộp quà 3D tinh tế, chạm để mở nắp tung hiệu ứng chúc phúc và hiển thị thẻ QR mừng cưới.',
            'badge' => 'Phổ Biến Nhất',
        ],
        'wax_envelope' => [
            'id' => 'wax_envelope',
            'name' => 'Bì Thư Hoàng Gia Khắc Tem Sáp',
            'description' => 'Bì thư phong cách hoàng gia cổ điển gắn tem sáp niêm phong trang trọng, mở ra tấm thiệp tri ân.',
            'badge' => 'Quý Phái & Lễ Nghi',
        ],
        'editorial_card' => [
            'id' => 'editorial_card',
            'name' => 'Thẻ Ngân Hàng Tối Giản Editorial',
            'description' => 'Bố cục tối giản thanh lịch, đường nét dứt khoát, hiển thị song song hai tài khoản hiện đại.',
            'badge' => 'Tối Giản & Tinh Tế',
        ],
    ];

    /**
     * 4 Lời ngỏ tế nhị lễ nghi văn hóa cưới hỏi Việt Nam.
     */
    public const ETIQUETTE_NOTES = [
        'respectful' => [
            'id' => 'respectful',
            'title' => 'Trang Trọng & Tôn Kính',
            'content' => 'Sự hiện diện và lời chúc phúc của Quý khách là món quà quý giá và ý nghĩa nhất đối với gia đình chúng tôi. Nếu Quý khách muốn gửi gắm thêm tình cảm và lời chúc mừng từ phương xa, xin vui lòng gửi qua thông tin dưới đây.',
        ],
        'intimate' => [
            'id' => 'intimate',
            'title' => 'Thân Mật & Ấm Cúng',
            'content' => 'Tụi mình vô cùng hạnh phúc khi được đón tiếp bạn trong ngày vui này. Có mặt của bạn là niềm vui lớn nhất, mọi sự chúc phúc hay món quà từ bạn đều được tụi mình nâng niu và trân trọng.',
        ],
        'romantic' => [
            'id' => 'romantic',
            'title' => 'Lãng Mạn & Ngọt Ngào',
            'content' => 'Cảm ơn bạn đã là một phần tươi đẹp trong hành trình hạnh phúc của chúng mình. Mỗi lời chúc, mỗi món quà từ bạn đều là hạt mầm cho tổ ấm nhỏ mai sau thêm đong đầy.',
        ],
        'minimal' => [
            'id' => 'minimal',
            'title' => 'Hiện Đại & Tối Giản',
            'content' => 'Mọi lời chúc và tình cảm yêu thương của bạn dành cho ngày trọng đại đều là niềm vinh hạnh to lớn của hai vợ chồng.',
        ],
    ];

    /**
     * Lấy danh sách ngân hàng Việt Nam.
     */
    public static function getVietnameseBanks(): array
    {
        return self::VIETNAMESE_BANKS;
    }

    /**
     * Lấy danh sách phong cách hộp quà.
     */
    public static function getPresetBoxStyles(): array
    {
        return self::BOX_STYLES;
    }

    /**
     * Lấy danh sách lời ngỏ tế nhị.
     */
    public static function getPresetEtiquetteNotes(): array
    {
        return self::ETIQUETTE_NOTES;
    }

    /**
     * Sinh URL VietQR chuẩn NAPAS 247 tức thời.
     */
    public static function generateVietQrUrl(string $bankBin, string $accountNumber, string $accountHolder, string $transferNote = ''): string
    {
        $cleanBin = trim($bankBin);
        $cleanAccount = trim($accountNumber);
        $cleanHolder = rawurlencode(trim($accountHolder));
        $cleanNote = rawurlencode(trim($transferNote));

        if (empty($cleanBin) || empty($cleanAccount)) {
            return '';
        }

        return "https://img.vietqr.io/image/{$cleanBin}-{$cleanAccount}-compact2.png?amount=0&addInfo={$cleanNote}&accountName={$cleanHolder}";
    }

    /**
     * Cấu hình mặc định của Hộp Mừng Cưới & Quà Tặng.
     */
    public static function getDefaultConfig(): array
    {
        return [
            'enabled' => true,
            'box_style' => 'ribbon_box',
            'kicker' => 'Gửi Gắm Yêu Thương',
            'title' => 'Hộp Mừng Cưới & Chúc Phúc',
            'etiquette_note' => self::ETIQUETTE_NOTES['respectful']['content'],
            'mask_account_numbers' => false,
            'active_tab' => 'all', // 'all', 'groom_only', 'bride_only'
            'groom' => [
                'enabled' => true,
                'role_label' => 'Mừng Cưới Chú Rể',
                'bank_name' => 'Vietcombank',
                'bank_bin' => '970436',
                'account_number' => '',
                'account_holder' => '',
                'transfer_note' => 'Mung cuoi Chu Re',
                'qr_url' => '',
                'custom_qr_url' => '',
            ],
            'bride' => [
                'enabled' => true,
                'role_label' => 'Mừng Cưới Cô Dâu',
                'bank_name' => 'Techcombank',
                'bank_bin' => '970407',
                'account_number' => '',
                'account_holder' => '',
                'transfer_note' => 'Mung cuoi Co Dau',
                'qr_url' => '',
                'custom_qr_url' => '',
            ],
            'wishlist' => [
                'enabled' => true,
                'title' => 'Món Quà Tân Hôn Mong Ước',
                'subtitle' => 'Nếu bạn muốn gửi tặng món quà hiện vật thiết thực cho tổ ấm nhỏ của tụi mình',
                'items' => [
                    [
                        'id' => 1,
                        'name' => 'Máy Pha Cà Phê Espresso Mini',
                        'category' => 'Gia dụng bếp',
                        'estimated_price' => '2.500.000 đ',
                        'product_link' => '',
                        'status' => 'available', // available | reserved
                        'reserved_by' => '',
                    ],
                    [
                        'id' => 2,
                        'name' => 'Bộ Nồi Chảo Gang Tráng Men Cao Cấp',
                        'category' => 'Đồ dùng nhà bếp',
                        'estimated_price' => '3.200.000 đ',
                        'product_link' => '',
                        'status' => 'available',
                        'reserved_by' => '',
                    ],
                    [
                        'id' => 3,
                        'name' => 'Bữa Tối Lãng Mạn Tuần Trăng Mật',
                        'category' => 'Kỷ niệm trăng mật',
                        'estimated_price' => '1.800.000 đ',
                        'product_link' => '',
                        'status' => 'available',
                        'reserved_by' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * Phân giải cấu hình Hộp Mừng Cưới từ thiệp mời và fallback an toàn từ đám cưới.
     */
    public static function resolveGiftRegistryConfig(Invitation $invitation): array
    {
        $default = self::getDefaultConfig();
        $customConfig = $invitation->custom_config ?? [];
        $registryConfig = $customConfig['gift_registry'] ?? [];
        $wedding = $invitation->wedding;

        $config = array_replace_recursive($default, $registryConfig);

        // Fallback từ các cột tài khoản gốc của Wedding nếu chưa có cấu hình riêng
        if ($wedding) {
            if (empty($config['groom']['account_number']) && ! empty($wedding->bank_account_number)) {
                $config['groom']['account_number'] = (string) $wedding->bank_account_number;
                $config['groom']['account_holder'] = (string) ($wedding->bank_account_name ?: $wedding->groom_name);
                $config['groom']['bank_name'] = (string) ($wedding->bank_name ?: 'Vietcombank');
            }

            if (empty($config['groom']['account_holder']) && ! empty($wedding->groom_name)) {
                $config['groom']['account_holder'] = Str::upper(Str::ascii($wedding->groom_name));
            }

            if (empty($config['bride']['account_holder']) && ! empty($wedding->bride_name)) {
                $config['bride']['account_holder'] = Str::upper(Str::ascii($wedding->bride_name));
            }

            // Đồng bộ tên cô dâu chú rể vào transfer note mặc định
            if (empty($config['groom']['transfer_note']) && ! empty($wedding->groom_name)) {
                $config['groom']['transfer_note'] = 'Mung cuoi '.Str::ascii($wedding->groom_name);
            }
            if (empty($config['bride']['transfer_note']) && ! empty($wedding->bride_name)) {
                $config['bride']['transfer_note'] = 'Mung cuoi '.Str::ascii($wedding->bride_name);
            }
        }

        // Tự động sinh VietQR nếu chưa có QR tùy chọn
        if (empty($config['groom']['custom_qr_url']) && ! empty($config['groom']['account_number'])) {
            $config['groom']['qr_url'] = self::generateVietQrUrl(
                $config['groom']['bank_bin'] ?: '970436',
                $config['groom']['account_number'],
                $config['groom']['account_holder'],
                $config['groom']['transfer_note']
            );
        } else {
            $config['groom']['qr_url'] = $config['groom']['custom_qr_url'] ?: $config['groom']['qr_url'];
        }

        if (empty($config['bride']['custom_qr_url']) && ! empty($config['bride']['account_number'])) {
            $config['bride']['qr_url'] = self::generateVietQrUrl(
                $config['bride']['bank_bin'] ?: '970407',
                $config['bride']['account_number'],
                $config['bride']['account_holder'],
                $config['bride']['transfer_note']
            );
        } else {
            $config['bride']['qr_url'] = $config['bride']['custom_qr_url'] ?: $config['bride']['qr_url'];
        }

        return $config;
    }

    /**
     * Cập nhật cấu hình Hộp Mừng Cưới và đồng bộ dữ liệu vào Wedding model.
     */
    public static function updateGiftRegistryConfig(Invitation $invitation, array $data, ?User $user = null): array
    {
        $currentConfig = self::resolveGiftRegistryConfig($invitation);

        $groomBin = $data['groom_bank_bin'] ?? $currentConfig['groom']['bank_bin'];
        $groomAccount = trim($data['groom_account_number'] ?? '');
        $groomHolder = trim($data['groom_account_holder'] ?? '');
        $groomNote = trim($data['groom_transfer_note'] ?? 'Mung cuoi Chu Re');
        $groomBankName = $data['groom_bank_name'] ?? $currentConfig['groom']['bank_name'];
        $groomCustomQr = $data['groom_custom_qr_url'] ?? $currentConfig['groom']['custom_qr_url'];

        $brideBin = $data['bride_bank_bin'] ?? $currentConfig['bride']['bank_bin'];
        $brideAccount = trim($data['bride_account_number'] ?? '');
        $brideHolder = trim($data['bride_account_holder'] ?? '');
        $brideNote = trim($data['bride_transfer_note'] ?? 'Mung cuoi Co Dau');
        $brideBankName = $data['bride_bank_name'] ?? $currentConfig['bride']['bank_name'];
        $brideCustomQr = $data['bride_custom_qr_url'] ?? $currentConfig['bride']['custom_qr_url'];

        $groomQrUrl = ! empty($groomCustomQr) ? $groomCustomQr : self::generateVietQrUrl($groomBin, $groomAccount, $groomHolder, $groomNote);
        $brideQrUrl = ! empty($brideCustomQr) ? $brideCustomQr : self::generateVietQrUrl($brideBin, $brideAccount, $brideHolder, $brideNote);

        // Chuẩn hóa danh sách wishlist
        $wishlistItems = [];
        if (isset($data['wishlist_items']) && is_array($data['wishlist_items'])) {
            foreach ($data['wishlist_items'] as $index => $item) {
                if (! empty($item['name'])) {
                    $wishlistItems[] = [
                        'id' => (int) ($item['id'] ?? ($index + 1)),
                        'name' => trim($item['name']),
                        'category' => trim($item['category'] ?? 'Quà mừng'),
                        'estimated_price' => trim($item['estimated_price'] ?? ''),
                        'product_link' => trim($item['product_link'] ?? ''),
                        'status' => in_array($item['status'] ?? 'available', ['available', 'reserved'], true) ? $item['status'] : 'available',
                        'reserved_by' => trim($item['reserved_by'] ?? ''),
                    ];
                }
            }
        } else {
            $wishlistItems = $currentConfig['wishlist']['items'];
        }

        $newConfig = [
            'enabled' => isset($data['enabled']) ? (bool) $data['enabled'] : $currentConfig['enabled'],
            'box_style' => in_array($data['box_style'] ?? '', array_keys(self::BOX_STYLES), true) ? $data['box_style'] : $currentConfig['box_style'],
            'kicker' => trim($data['kicker'] ?? $currentConfig['kicker']),
            'title' => trim($data['title'] ?? $currentConfig['title']),
            'etiquette_note' => trim($data['etiquette_note'] ?? $currentConfig['etiquette_note']),
            'mask_account_numbers' => isset($data['mask_account_numbers']) ? (bool) $data['mask_account_numbers'] : false,
            'active_tab' => in_array($data['active_tab'] ?? '', ['all', 'groom_only', 'bride_only'], true) ? $data['active_tab'] : 'all',
            'groom' => [
                'enabled' => isset($data['groom_enabled']) ? (bool) $data['groom_enabled'] : true,
                'role_label' => trim($data['groom_role_label'] ?? 'Mừng Cưới Chú Rể'),
                'bank_name' => $groomBankName,
                'bank_bin' => $groomBin,
                'account_number' => $groomAccount,
                'account_holder' => $groomHolder,
                'transfer_note' => $groomNote,
                'qr_url' => $groomQrUrl,
                'custom_qr_url' => $groomCustomQr,
            ],
            'bride' => [
                'enabled' => isset($data['bride_enabled']) ? (bool) $data['bride_enabled'] : true,
                'role_label' => trim($data['bride_role_label'] ?? 'Mừng Cưới Cô Dâu'),
                'bank_name' => $brideBankName,
                'bank_bin' => $brideBin,
                'account_number' => $brideAccount,
                'account_holder' => $brideHolder,
                'transfer_note' => $brideNote,
                'qr_url' => $brideQrUrl,
                'custom_qr_url' => $brideCustomQr,
            ],
            'wishlist' => [
                'enabled' => isset($data['wishlist_enabled']) ? (bool) $data['wishlist_enabled'] : false,
                'title' => trim($data['wishlist_title'] ?? 'Món Quà Tân Hôn Mong Ước'),
                'subtitle' => trim($data['wishlist_subtitle'] ?? 'Gợi ý món quà hiện vật tân hôn cho tổ ấm nhỏ'),
                'items' => $wishlistItems,
            ],
        ];

        // 1. Lưu vào custom_config của Invitation
        $customConfig = $invitation->custom_config ?? [];
        $customConfig['gift_registry'] = $newConfig;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        // 2. Đồng bộ vào Wedding model để tương thích ngược
        $wedding = $invitation->wedding;
        if ($wedding) {
            $extraData = $wedding->extra_data ?? [];
            $extraData['gift_registry'] = $newConfig;
            $wedding->extra_data = $extraData;

            // Đồng bộ tài khoản chính ưu tiên chú rể hoặc cô dâu
            if (! empty($groomAccount)) {
                $wedding->bank_name = $groomBankName;
                $wedding->bank_account_number = $groomAccount;
                $wedding->bank_account_name = $groomHolder;
            } elseif (! empty($brideAccount)) {
                $wedding->bank_name = $brideBankName;
                $wedding->bank_account_number = $brideAccount;
                $wedding->bank_account_name = $brideHolder;
            }

            $wedding->save();
        }

        // 3. Ghi nhận Audit Log
        if (class_exists(AuditLogger::class)) {
            AuditLogger::log(
                'GIFT_REGISTRY_UPDATED',
                'Invitation',
                $invitation->id,
                "Cập nhật Hộp Mừng Cưới cho thiệp #{$invitation->id} ({$newConfig['box_style']})",
                [
                    'invitation_id' => $invitation->id,
                    'wedding_id' => $wedding?->id,
                    'box_style' => $newConfig['box_style'],
                    'has_groom' => ! empty($groomAccount),
                    'has_bride' => ! empty($brideAccount),
                    'wishlist_count' => count($wishlistItems),
                    'updated_by' => $user?->id,
                ]
            );
        }

        return $newConfig;
    }

    /**
     * Tải lên ảnh mã QR tùy chọn cho chú rể hoặc cô dâu.
     */
    public static function uploadCustomQrPhoto(Invitation $invitation, UploadedFile $file, string $side = 'groom'): string
    {
        $folder = 'invitations/'.$invitation->id.'/qr';
        $fileName = $side.'_custom_qr_'.Str::random(10).'.'.$file->getClientOriginalExtension();

        $path = $file->storeAs($folder, $fileName, 'public');
        $url = Storage::disk('public')->url($path);

        $config = self::resolveGiftRegistryConfig($invitation);
        if ($side === 'bride') {
            $config['bride']['custom_qr_url'] = $url;
        } else {
            $config['groom']['custom_qr_url'] = $url;
        }

        $customConfig = $invitation->custom_config ?? [];
        $customConfig['gift_registry'] = $config;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        if ($invitation->wedding) {
            $extra = $invitation->wedding->extra_data ?? [];
            $extra['gift_registry'] = $config;
            $invitation->wedding->extra_data = $extra;
            $invitation->wedding->save();
        }

        return $url;
    }

    /**
     * Khách mời đăng ký tặng một món quà trong danh sách Wishlist (IDEA-F07).
     */
    public static function reserveWishlistItem(Invitation $invitation, int $itemId, string $guestName, string $guestPhone = ''): array
    {
        $config = self::resolveGiftRegistryConfig($invitation);
        $items = $config['wishlist']['items'] ?? [];
        $found = false;
        $updatedItem = null;

        foreach ($items as &$item) {
            if ((int) $item['id'] === $itemId) {
                if ($item['status'] === 'reserved') {
                    return [
                        'success' => false,
                        'message' => 'Món quà này đã được một vị khách khác chọn gửi tặng. Bạn có thể chọn món quà khác nhé!',
                    ];
                }

                $item['status'] = 'reserved';
                $item['reserved_by'] = trim($guestName);
                if (! empty($guestPhone)) {
                    $item['reserved_phone'] = trim($guestPhone);
                }
                $item['reserved_at'] = now()->toIso8601String();
                $found = true;
                $updatedItem = $item;
                break;
            }
        }

        if (! $found) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy món quà trong danh sách.',
            ];
        }

        $config['wishlist']['items'] = $items;

        $customConfig = $invitation->custom_config ?? [];
        $customConfig['gift_registry'] = $config;
        $invitation->custom_config = $customConfig;
        $invitation->save();

        if ($invitation->wedding) {
            $extra = $invitation->wedding->extra_data ?? [];
            $extra['gift_registry'] = $config;
            $invitation->wedding->extra_data = $extra;
            $invitation->wedding->save();
        }

        return [
            'success' => true,
            'message' => "Cảm ơn bạn {$guestName}! Lời đăng ký gửi tặng món quà '{$updatedItem['name']}' đã được ghi nhận thành công.",
            'item' => $updatedItem,
        ];
    }
}
