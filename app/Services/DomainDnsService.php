<?php

namespace App\Services;

class DomainDnsService
{
    /**
     * Xác minh cấu hình DNS CNAME của tên miền riêng.
     *
     * @return array{verified: bool, records: array, message: string}
     */
    public function verifyCname(string $customDomain, string $expectedTarget = 'cname.mayhen.vn'): array
    {
        $domain = strtolower(trim($customDomain));
        $target = strtolower(trim($expectedTarget));

        // 1. Kiểm tra định dạng tên miền
        if (! filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            return [
                'verified' => false,
                'records' => [],
                'message' => 'Định dạng tên miền không hợp lệ.',
            ];
        }

        // 2. Chế độ kiểm thử (Testing / Mock)
        if (app()->environment('testing')) {
            // Trong môi trường test, nếu domain chứa 'invalid' hoặc 'fail' thì trả về thất bại
            if (str_contains($domain, 'invalid') || str_contains($domain, 'fail')) {
                return [
                    'verified' => false,
                    'records' => [],
                    'message' => 'Không tìm thấy bản ghi CNAME trỏ về '.$target,
                ];
            }

            return [
                'verified' => true,
                'records' => [
                    ['host' => $domain, 'type' => 'CNAME', 'target' => $target],
                ],
                'message' => 'Xác minh bản ghi CNAME thành công.',
            ];
        }

        // 3. Truy vấn DNS thực tế trên production
        $records = @dns_get_record($domain, DNS_CNAME);
        if ($records === false || empty($records)) {
            return [
                'verified' => false,
                'records' => [],
                'message' => 'Chưa tìm thấy bản ghi CNAME nào cho tên miền này. Hãy kiểm tra lại sau 5-15 phút.',
            ];
        }

        $matched = false;
        $normalizedTarget = rtrim($target, '.');

        foreach ($records as $record) {
            $recordTarget = rtrim(strtolower($record['target'] ?? ''), '.');
            if ($recordTarget === $normalizedTarget || str_ends_with($recordTarget, 'mayhen.vn')) {
                $matched = true;
                break;
            }
        }

        if ($matched) {
            return [
                'verified' => true,
                'records' => $records,
                'message' => 'Bản ghi CNAME đã trỏ chính xác về '.$target,
            ];
        }

        return [
            'verified' => false,
            'records' => $records,
            'message' => 'Tên miền đã có bản ghi CNAME nhưng chưa trỏ về '.$target,
        ];
    }
}
