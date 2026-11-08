<?php

namespace App\Services;

class CsvSanitizerService
{
    /**
     * Danh sách ký tự nguy hiểm có thể kích hoạt thực thi lệnh hoặc công thức tính toán
     * trong Microsoft Excel, LibreOffice, Apple Numbers, Google Sheets (CWE-1236: CSV Formula Injection).
     */
    private const DANGEROUS_PREFIXES = ['=', '+', '-', '@', "\t", "\r"];

    /**
     * Vệ sinh an toàn một giá trị trước khi ghi vào tệp CSV.
     * Nếu giá trị là chuỗi và bắt đầu bằng ký tự nguy hiểm, tự động thêm dấu nháy đơn ' ở đầu
     * để phần mềm bảng tính nhận diện là chuỗi ký tự thuần (text), hoàn toàn vô hiệu hóa mã lệnh.
     */
    public static function sanitize(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $trimmed = ltrim($value);
        if ($trimmed !== '' && in_array($trimmed[0], self::DANGEROUS_PREFIXES, true)) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * Vệ sinh an toàn toàn bộ một mảng hàng (row) trước khi gọi fputcsv().
     *
     * @param  array<int|string, mixed>  $row
     * @return array<int|string, mixed>
     */
    public static function row(array $row): array
    {
        return array_map([self::class, 'sanitize'], $row);
    }
}

