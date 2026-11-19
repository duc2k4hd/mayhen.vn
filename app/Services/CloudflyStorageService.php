<?php

namespace App\Services;

use DateTime;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CloudflyStorageService
{
    protected string $endpoint;

    protected string $accessKey;

    protected string $secretKey;

    protected string $bucket;

    protected string $region;

    protected ?string $publicUrl;

    public function __construct()
    {
        $diskConfig = config('filesystems.disks.cloudfly', []);

        $rawEndpoint = (string) ($diskConfig['endpoint'] ?? env('CLOUDFLY_OBJECT_STORAGE_ENDPOINT', 's3.cloudfly.vn'));
        $this->endpoint = preg_replace('#^https?://#i', '', rtrim($rawEndpoint, '/')) ?? 's3.cloudfly.vn';

        $this->accessKey = trim((string) ($diskConfig['key'] ?? env('CLOUDFLY_OBJECT_STORAGE_ACCESS_KEY', '')));
        $this->secretKey = trim((string) ($diskConfig['secret'] ?? env('CLOUDFLY_OBJECT_STORAGE_SECRET_KEY', '')));
        $this->bucket = trim((string) ($diskConfig['bucket'] ?? env('CLOUDFLY_OBJECT_STORAGE_BUCKET', 'mayhen-media')));
        $this->region = trim((string) ($diskConfig['region'] ?? env('CLOUDFLY_OBJECT_STORAGE_REGION', 'us-east-1')));

        $rawPublicUrl = $diskConfig['url'] ?? env('CLOUDFLY_OBJECT_STORAGE_PUBLIC_URL');
        $this->publicUrl = ! empty($rawPublicUrl) ? rtrim((string) $rawPublicUrl, '/') : null;
    }

    /**
     * Kiểm tra xem thông tin kết nối Cloudfly Object Storage đã được cấu hình đầy đủ chưa.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->endpoint)
            && ! empty($this->accessKey)
            && ! empty($this->secretKey)
            && ! empty($this->bucket);
    }

    /**
     * Lấy tên bucket hiện tại.
     */
    public function getBucket(): string
    {
        return $this->bucket;
    }

    /**
     * Lấy endpoint hiện tại.
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * Sinh URL công khai cho một đường dẫn tệp.
     */
    public function url(string $path): string
    {
        $path = ltrim($path, '/');

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        if ($this->publicUrl) {
            return "{$this->publicUrl}/{$path}";
        }

        return "https://{$this->bucket}.{$this->endpoint}/{$path}";
    }

    /**
     * Tải tệp lên Cloudfly Object Storage (với cơ chế Fallback an toàn về đĩa public nếu có sự cố).
     *
     * @param  UploadedFile|string  $file  Tệp tải lên hoặc đường dẫn tệp cục bộ
     * @param  string  $directory  Thư mục đích (mặc định weddings/photos)
     * @param  string|null  $customFilename  Tên tệp tùy chỉnh (nếu rỗng tự sinh UUID)
     * @param  string  $visibility  Quyền truy cập S3 (mặc định public-read)
     * @return array{success: bool, url: string, relative_url: string, storage_path: string, file_name: string, disk: string}
     */
    public function upload(
        UploadedFile|string $file,
        string $directory = 'weddings/photos',
        ?string $customFilename = null,
        string $visibility = 'public-read'
    ): array {
        $directory = trim($directory, '/');

        // 1. Phân giải tên tệp và nội dung
        if ($file instanceof UploadedFile) {
            $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
            if (empty($ext) || ! in_array($ext, ['jpeg', 'png', 'jpg', 'webp', 'gif', 'heic', 'heif', 'svg', 'mp3', 'pdf'], true)) {
                $ext = 'jpg';
            }

            $filename = $customFilename ?: (Str::uuid()->toString().'.'.$ext);
            $mimeType = $file->getMimeType() ?: 'application/octet-stream';
            $content = file_get_contents($file->getRealPath());
        } elseif (is_string($file) && file_exists($file)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION) ?: 'jpg');
            $filename = $customFilename ?: (Str::uuid()->toString().'.'.$ext);
            $mimeType = mime_content_type($file) ?: 'application/octet-stream';
            $content = file_get_contents($file);
        } else {
            // Chuỗi nội dung raw (ví dụ base64 decode hoặc svg raw)
            $filename = $customFilename ?: (Str::uuid()->toString().'.bin');
            $mimeType = 'application/octet-stream';
            $content = (string) $file;
        }

        $storagePath = "{$directory}/{$filename}";

        // 2. Thử tải lên Cloudfly Object Storage nếu đã cấu hình
        if ($this->isConfigured()) {
            try {
                $uploaded = $this->putObject($storagePath, $content, $mimeType, $visibility);
                if ($uploaded) {
                    $fileUrl = $this->url($storagePath);

                    return [
                        'success' => true,
                        'url' => $fileUrl,
                        'relative_url' => $fileUrl,
                        'storage_path' => $storagePath,
                        'file_name' => $filename,
                        'disk' => 'cloudfly',
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('Cloudfly S3 upload encountered an error, falling back to local public storage: '.$e->getMessage(), [
                    'path' => $storagePath,
                ]);
            }
        }

        // 3. Fallback an toàn về Local Public Storage
        Storage::disk('public')->put($storagePath, $content);
        $localUrl = Storage::disk('public')->url($storagePath);
        $relativeUrl = '/storage/'.$storagePath;

        return [
            'success' => true,
            'url' => $localUrl,
            'relative_url' => $relativeUrl,
            'storage_path' => $storagePath,
            'file_name' => $filename,
            'disk' => 'public',
        ];
    }

    /**
     * Xóa tệp trên Cloudfly Object Storage hoặc Local Storage.
     */
    public function delete(string $pathOrUrl): bool
    {
        $path = $this->extractStoragePath($pathOrUrl);
        if (empty($path)) {
            return false;
        }

        if ($this->isConfigured()) {
            try {
                $this->deleteObject($path);
            } catch (\Throwable $e) {
                Log::warning('Cloudfly S3 delete failed: '.$e->getMessage(), ['path' => $path]);
            }
        }

        // Đồng thời dọn dẹp trên local public nếu còn sót
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return true;
    }

    /**
     * Gửi yêu cầu PUT Object lên Cloudfly S3 sử dụng AWS Signature Version 4.
     */
    public function putObject(string $path, string $content, string $mimeType = 'application/octet-stream', string $acl = 'public-read'): bool
    {
        $path = '/'.ltrim($path, '/');
        $host = "{$this->bucket}.{$this->endpoint}";

        $headers = [
            'host' => $host,
            'content-type' => $mimeType,
            'x-amz-acl' => $acl,
        ];

        $signedHeaders = $this->signV4('PUT', $path, '', $headers, $content);
        $curlHeaders = [];
        foreach ($signedHeaders as $k => $v) {
            $curlHeaders[] = "{$k}: {$v}";
        }

        $url = "https://{$host}{$path}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        if ($caBundle = $this->resolveCaBundle()) {
            curl_setopt($ch, CURLOPT_CAINFO, $caBundle);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        Log::error("Cloudfly S3 PUT failed [{$httpCode}]: {$response} | cURL error: {$curlError}", [
            'url' => $url,
        ]);

        return false;
    }

    /**
     * Gửi yêu cầu DELETE Object lên Cloudfly S3 sử dụng AWS Signature Version 4.
     */
    public function deleteObject(string $path): bool
    {
        $path = '/'.ltrim($path, '/');
        $host = "{$this->bucket}.{$this->endpoint}";

        $headers = [
            'host' => $host,
        ];

        $signedHeaders = $this->signV4('DELETE', $path, '', $headers, '');
        $curlHeaders = [];
        foreach ($signedHeaders as $k => $v) {
            $curlHeaders[] = "{$k}: {$v}";
        }

        $url = "https://{$host}{$path}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        if ($caBundle = $this->resolveCaBundle()) {
            curl_setopt($ch, CURLOPT_CAINFO, $caBundle);
        }

        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return $httpCode >= 200 && $httpCode < 300;
    }

    /**
     * Tự động tìm CA Bundle hợp lệ trên Windows/Linux nếu php.ini chưa cấu hình curl.cainfo.
     */
    protected function resolveCaBundle(): ?string
    {
        foreach ([ini_get('curl.cainfo'), ini_get('openssl.cafile')] as $configured) {
            if (is_string($configured) && $configured !== '' && is_file($configured)) {
                return $configured;
            }
        }

        $candidates = [
            'C:\\Program Files\\Git\\mingw64\\etc\\ssl\\certs\\ca-bundle.crt',
            'C:\\Program Files\\Git\\usr\\ssl\\certs\\ca-bundle.crt',
            'C:\\laragon\\etc\\ssl\\cacert.pem',
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/pki/tls/certs/ca-bundle.crt',
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Kiểm tra trạng thái kết nối Cloudfly Object Storage (Chẩn đoán hạ tầng).
     *
     * @return array{success: bool, latency_ms: float, message: string}
     */
    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'latency_ms' => 0,
                'message' => 'Chưa cấu hình đầy đủ Access Key, Secret Key hoặc Tên Bucket của Cloudfly Object Storage trong .env.',
            ];
        }

        $startTime = microtime(true);

        try {
            $testFile = 'diagnostics/test-'.time().'.txt';
            $testContent = 'Cloudfly S3 Diagnostic Ping at '.now()->toIso8601String();

            $putSuccess = $this->putObject($testFile, $testContent, 'text/plain', 'public-read');
            if (! $putSuccess) {
                $latency = round((microtime(true) - $startTime) * 1000, 2);

                return [
                    'success' => false,
                    'latency_ms' => $latency,
                    'message' => "Không thể ghi tệp thử nghiệm lên Cloudfly Bucket [{$this->bucket}]. Vui lòng kiểm tra lại cặp khóa API.",
                ];
            }

            // Dọn dẹp tệp thử nghiệm
            $this->deleteObject($testFile);

            $latency = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'success' => true,
                'latency_ms' => $latency,
                'message' => "Kết nối Cloudfly Object Storage (Bucket: {$this->bucket}) phản hồi hoàn hảo ({$latency} ms).",
            ];
        } catch (\Throwable $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'success' => false,
                'latency_ms' => $latency,
                'message' => 'Lỗi kết nối Cloudfly: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Trích xuất đường dẫn tương đối từ URL lưu trữ.
     */
    protected function extractStoragePath(string $pathOrUrl): string
    {
        $path = trim($pathOrUrl);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsed = parse_url($path, PHP_URL_PATH);
            $path = ltrim((string) $parsed, '/');

            // Nếu là URL dạng path-style (s3.cloudfly.vn/bucket/path...)
            if (str_starts_with($path, $this->bucket.'/')) {
                $path = substr($path, strlen($this->bucket) + 1);
            }
        }

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        return ltrim($path, '/');
    }

    /**
     * Ký thuật toán AWS Signature Version 4 chuẩn xác cho S3 REST API.
     *
     * @param  array<string, string>  $headers
     * @return array<string, string>
     */
    protected function signV4(string $method, string $uri, string $queryString, array $headers, string $payload): array
    {
        $now = new DateTime('UTC');
        $dateStamp = $now->format('Ymd');
        $amzDate = $now->format('Ymd\THis\Z');

        $headers['x-amz-date'] = $amzDate;
        $headers['x-amz-content-sha256'] = hash('sha256', $payload);

        ksort($headers);
        $canonicalHeaders = '';
        $signedHeadersArr = [];
        foreach ($headers as $k => $v) {
            $lk = strtolower($k);
            $canonicalHeaders .= $lk.':'.trim($v)."\n";
            $signedHeadersArr[] = $lk;
        }
        $signedHeaders = implode(';', $signedHeadersArr);

        $canonicalRequest = $method."\n"
            .$uri."\n"
            .$queryString."\n"
            .$canonicalHeaders."\n"
            .$signedHeaders."\n"
            .hash('sha256', $payload);

        $algorithm = 'AWS4-HMAC-SHA256';
        $service = 's3';
        $credentialScope = $dateStamp.'/'.$this->region.'/'.$service.'/aws4_request';
        $stringToSign = $algorithm."\n"
            .$amzDate."\n"
            .$credentialScope."\n"
            .hash('sha256', $canonicalRequest);

        $kSecret = 'AWS4'.$this->secretKey;
        $kDate = hash_hmac('sha256', $dateStamp, $kSecret, true);
        $kRegion = hash_hmac('sha256', $this->region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $authorization = "{$algorithm} Credential={$this->accessKey}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";
        $headers['Authorization'] = $authorization;

        return $headers;
    }
}
