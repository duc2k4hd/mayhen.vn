<?php

namespace App\Services;

class VietQrService
{
    /**
     * Danh mục 50+ Ngân Hàng Việt Nam chuẩn NAPAS 247 (Mã BIN, Short Name, Tên đầy đủ).
     *
     * @return array<string, array{bin: string, short_name: string, name: string}>
     */
    public static function getBankList(): array
    {
        return [
            'VCB' => ['bin' => '970436', 'short_name' => 'Vietcombank', 'name' => 'Ngân hàng TMCP Ngoại thương Việt Nam'],
            'TCB' => ['bin' => '970407', 'short_name' => 'Techcombank', 'name' => 'Ngân hàng TMCP Kỹ thương Việt Nam'],
            'MB' => ['bin' => '970422', 'short_name' => 'MBBank', 'name' => 'Ngân hàng TMCP Quân đội'],
            'VPB' => ['bin' => '970432', 'short_name' => 'VPBank', 'name' => 'Ngân hàng TMCP Việt Nam Thịnh Vượng'],
            'ACB' => ['bin' => '970416', 'short_name' => 'ACB', 'name' => 'Ngân hàng TMCP Á Châu'],
            'BIDV' => ['bin' => '970418', 'short_name' => 'BIDV', 'name' => 'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam'],
            'CTG' => ['bin' => '970415', 'short_name' => 'VietinBank', 'name' => 'Ngân hàng TMCP Công thương Việt Nam'],
            'VBA' => ['bin' => '970405', 'short_name' => 'Agribank', 'name' => 'Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam'],
            'TPB' => ['bin' => '970423', 'short_name' => 'TPBank', 'name' => 'Ngân hàng TMCP Tiên Phong'],
            'STB' => ['bin' => '970403', 'short_name' => 'Sacombank', 'name' => 'Ngân hàng TMCP Sài Gòn Thương Tín'],
            'HDB' => ['bin' => '970437', 'short_name' => 'HDBank', 'name' => 'Ngân hàng TMCP Phát triển TP.HCM'],
            'VIB' => ['bin' => '970441', 'short_name' => 'VIB', 'name' => 'Ngân hàng TMCP Quốc tế Việt Nam'],
            'SHB' => ['bin' => '970443', 'short_name' => 'SHB', 'name' => 'Ngân hàng TMCP Sài Gòn - Hà Nội'],
            'SSB' => ['bin' => '970440', 'short_name' => 'SeABank', 'name' => 'Ngân hàng TMCP Đông Nam Á'],
            'OCB' => ['bin' => '970448', 'short_name' => 'OCB', 'name' => 'Ngân hàng TMCP Phương Đông'],
            'MSB' => ['bin' => '970426', 'short_name' => 'MSB', 'name' => 'Ngân hàng TMCP Hàng Hải Việt Nam'],
            'LPB' => ['bin' => '970449', 'short_name' => 'LPBank', 'name' => 'Ngân hàng TMCP Lộc Phát Việt Nam'],
            'EIB' => ['bin' => '970431', 'short_name' => 'Eximbank', 'name' => 'Ngân hàng TMCP Xuất Nhập Khẩu Việt Nam'],
            'ABB' => ['bin' => '970425', 'short_name' => 'ABBANK', 'name' => 'Ngân hàng TMCP An Bình'],
            'BAB' => ['bin' => '970409', 'short_name' => 'BacABank', 'name' => 'Ngân hàng TMCP Bắc Á'],
            'NAB' => ['bin' => '970428', 'short_name' => 'NamABank', 'name' => 'Ngân hàng TMCP Nam Á'],
            'VIETBANK' => ['bin' => '970433', 'short_name' => 'VietBank', 'name' => 'Ngân hàng TMCP Việt Nam Thương Tín'],
            'BVB' => ['bin' => '970454', 'short_name' => 'BVBank', 'name' => 'Ngân hàng TMCP Bản Việt'],
            'NCB' => ['bin' => '970419', 'short_name' => 'NCB', 'name' => 'Ngân hàng TMCP Quốc Dân'],
            'KLB' => ['bin' => '970452', 'short_name' => 'Kienlongbank', 'name' => 'Ngân hàng TMCP Kiên Long'],
            'VAB' => ['bin' => '970427', 'short_name' => 'VietABank', 'name' => 'Ngân hàng TMCP Việt Á'],
            'CAKE' => ['bin' => '546034', 'short_name' => 'CAKE', 'name' => 'Ngân hàng số CAKE by VPBank'],
            'UBANK' => ['bin' => '546035', 'short_name' => 'Ubank', 'name' => 'Ngân hàng số Ubank by VPBank'],
            'TIMO' => ['bin' => '963388', 'short_name' => 'Timo', 'name' => 'Ngân hàng số Timo by BanVietBank'],
            'WVN' => ['bin' => '970457', 'short_name' => 'WooriBank', 'name' => 'Ngân hàng TNHH MTV Woori Việt Nam'],
            'SHBVN' => ['bin' => '970424', 'short_name' => 'ShinhanBank', 'name' => 'Ngân hàng TNHH MTV Shinhan Việt Nam'],
            'SCB' => ['bin' => '970429', 'short_name' => 'SCB', 'name' => 'Ngân hàng TMCP Sài Gòn'],
            'VRB' => ['bin' => '970421', 'short_name' => 'VRB', 'name' => 'Ngân hàng Liên doanh Việt - Nga'],
            'IVB' => ['bin' => '970434', 'short_name' => 'IndovinaBank', 'name' => 'Ngân hàng TNHH Indovina'],
            'HLBVN' => ['bin' => '970442', 'short_name' => 'HongLeong', 'name' => 'Ngân hàng TNHH MTV Hong Leong Việt Nam'],
            'CBB' => ['bin' => '970444', 'short_name' => 'CBBank', 'name' => 'Ngân hàng Thương mại TNHH MTV Xây dựng Việt Nam'],
            'OJB' => ['bin' => '970414', 'short_name' => 'OceanBank', 'name' => 'Ngân hàng Thương mại TNHH MTV Đại Dương'],
            'GPB' => ['bin' => '970408', 'short_name' => 'GPBank', 'name' => 'Ngân hàng Thương mại TNHH MTV Dầu Khí Toàn Cầu'],
            'PGB' => ['bin' => '970430', 'short_name' => 'PGBank', 'name' => 'Ngân hàng TMCP Thịnh vượng và Phát triển'],
            'SAIGONBANK' => ['bin' => '970400', 'short_name' => 'SaigonBank', 'name' => 'Ngân hàng TMCP Sài Gòn Công Thương'],
        ];
    }

    /**
     * Nhận diện thông minh và chuyển đổi tên ngân hàng bất kỳ sang mã BIN 6 số Napas.
     */
    public static function resolveBankBin(string $input): string
    {
        $clean = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', $input)));

        // Nếu người dùng đã truyền trực tiếp mã BIN 6 chữ số
        if (preg_match('/^\d{6}$/', $clean)) {
            return $clean;
        }

        $banks = self::getBankList();

        // 1. Khớp theo Key viết tắt chính xác (VCB, TCB, MB, ACB...)
        if (isset($banks[$clean])) {
            return $banks[$clean]['bin'];
        }

        // 2. Khớp theo short_name hoặc tên ngân hàng
        foreach ($banks as $code => $info) {
            $shortClean = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $info['short_name']));
            if ($clean === $shortClean || str_contains($clean, $shortClean) || str_contains($shortClean, $clean)) {
                return $info['bin'];
            }
        }

        // 3. Fallback mặc định Vietcombank nếu không nhận diện được
        return '970436';
    }

    /**
     * Tạo chuỗi TLV (Tag-Length-Value) chuẩn EMVCo QR Code.
     */
    protected static function makeTlv(string $tag, string $value): string
    {
        $length = mb_strlen($value, 'UTF-8');

        return sprintf('%s%02d%s', $tag, $length, $value);
    }

    /**
     * Tính toán mã kiểm tra CRC-16/CCITT-FALSE (Đa thức 0x1021, Init 0xFFFF).
     */
    public static function calculateCrc16(string $data): string
    {
        $crc = 0xFFFF;
        $polynomial = 0x1021;
        $bytes = unpack('C*', $data);

        if (! is_array($bytes)) {
            return '0000';
        }

        foreach ($bytes as $byte) {
            for ($i = 0; $i < 8; $i++) {
                $bit = (($byte >> (7 - $i)) & 1) === 1;
                $c15 = (($crc >> 15) & 1) === 1;
                $crc = ($crc << 1) & 0xFFFF;
                if ($c15 ^ $bit) {
                    $crc ^= $polynomial;
                }
            }
        }

        return sprintf('%04X', $crc & 0xFFFF);
    }

    /**
     * Chuẩn hóa bỏ dấu tiếng Việt sang ASCII không dấu cho nội dung chuyển khoản an toàn.
     */
    public static function sanitizeMemo(string $str): string
    {
        $str = preg_replace('/[àáạảãâầấậẩẫăằắặẳẵ]/u', 'a', $str);
        $str = preg_replace('/[èéẹẻẽêềếệểễ]/u', 'e', $str);
        $str = preg_replace('/[ìíịỉĩ]/u', 'i', $str);
        $str = preg_replace('/[òóọỏõôồốộổỗơờớợởỡ]/u', 'o', $str);
        $str = preg_replace('/[ùúụủũưừứựửữ]/u', 'u', $str);
        $str = preg_replace('/[ỳýỵỷỹ]/u', 'y', $str);
        $str = preg_replace('/[đ]/u', 'd', $str);
        $str = preg_replace('/[ÀÁẠẢÃÂẦẤẬẨẪĂẰẮẶẲẴ]/u', 'A', $str);
        $str = preg_replace('/[ÈÉẸẺẼÊỀẾỆỂỄ]/u', 'E', $str);
        $str = preg_replace('/[ÌÍỊỈĨ]/u', 'I', $str);
        $str = preg_replace('/[ÒÓỌỎÕÔỒỐỘỔỖƠỜỚỢỞỠ]/u', 'O', $str);
        $str = preg_replace('/[ÙÚỤỦŨƯỪỨỰỬỮ]/u', 'U', $str);
        $str = preg_replace('/[ỲÝỴỶỸ]/u', 'Y', $str);
        $str = preg_replace('/[Đ]/u', 'D', $str);

        // Chỉ giữ lại chữ cái, số và khoảng trắng
        $clean = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $str);
        $clean = preg_replace('/\s+/', ' ', trim($clean));

        return mb_substr($clean, 0, 50, 'UTF-8');
    }

    /**
     * Sinh chuỗi mã VietQR EMVCo chuẩn Napas 247 hoàn chỉnh (Chuyển nhanh liên ngân hàng).
     */
    public static function generatePayload(
        string $bankNameOrBin,
        string $accountNumber,
        int|float $amount = 0,
        string $memo = ''
    ): string {
        $bin = self::resolveBankBin($bankNameOrBin);
        $cleanAccount = preg_replace('/[^a-zA-Z0-9]/', '', $accountNumber);

        // 1. Tag 00: Version Format (01)
        $payload = self::makeTlv('00', '01');

        // 2. Tag 01: Initiation Method (12: Dynamic QR hoặc 11: Static QR)
        $payload .= self::makeTlv('01', $amount > 0 ? '12' : '11');

        // 3. Tag 38: Merchant Account Info (Napas 247)
        // Subtag 00: AID của Napas
        $napasAid = self::makeTlv('00', 'A000000727');

        // Subtag 01: Chi tiết tài khoản thụ hưởng (Subtag 00: BIN ngân hàng, Subtag 01: Số tài khoản)
        $bankAccountDetail = self::makeTlv('00', $bin).self::makeTlv('01', $cleanAccount);
        $consumerId = self::makeTlv('01', $bankAccountDetail);

        // Subtag 02: Dịch vụ chuyển nhanh Napas (QRIBFTTA: Tài khoản / QRIBFTTC: Thẻ)
        $serviceCode = self::makeTlv('02', 'QRIBFTTA');

        $tag38Value = $napasAid.$consumerId.$serviceCode;
        $payload .= self::makeTlv('38', $tag38Value);

        // 4. Tag 53: Transaction Currency (704 = VND)
        $payload .= self::makeTlv('53', '704');

        // 5. Tag 54: Transaction Amount (nếu có số tiền cụ thể)
        if ($amount > 0) {
            $payload .= self::makeTlv('54', (string) ((int) $amount));
        }

        // 6. Tag 58: Country Code (VN)
        $payload .= self::makeTlv('58', 'VN');

        // 7. Tag 62: Additional Data (Nội dung chuyển khoản / Lời chúc mừng cưới)
        if (! empty($memo)) {
            $cleanMemo = self::sanitizeMemo($memo);
            if (! empty($cleanMemo)) {
                $subtag08 = self::makeTlv('08', $cleanMemo);
                $payload .= self::makeTlv('62', $subtag08);
            }
        }

        // 8. Tag 63: CRC16 Checksum
        $payloadBeforeCrc = $payload.'6304';
        $crc = self::calculateCrc16($payloadBeforeCrc);

        return $payloadBeforeCrc.$crc;
    }

    /**
     * Sinh Vector SVG mã VietQR chuẩn Napas 247 thuần PHP (Không cần thư viện ngoài hay gọi sang vietqr.io).
     */
    public static function generateSvg(
        string $bankNameOrBin,
        string $accountNumber,
        int|float $amount = 0,
        string $memo = '',
        int $size = 280
    ): string {
        $payload = self::generatePayload($bankNameOrBin, $accountNumber, $amount, $memo);

        // Sử dụng QrCodeService thuần PHP chuẩn ISO/IEC 18004
        return QrCodeService::svg($payload, $size, '#1F1D1A', '#FFFFFF');
    }

    /**
     * Sinh Data URI Vector SVG (data:image/svg+xml;base64,...) để gắn thẳng vào thẻ <img src="..."> trong thiệp cưới.
     */
    public static function generateDataUri(
        string $bankNameOrBin,
        string $accountNumber,
        int|float $amount = 0,
        string $memo = '',
        int $size = 280
    ): string {
        $svg = self::generateSvg($bankNameOrBin, $accountNumber, $amount, $memo, $size);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}

