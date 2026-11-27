<?php

namespace App\Services;

use DateTime;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CloudflareR2StorageService
{
    protected string $endpoint;

    protected string $accountId;

    protected string $accessKey;

    protected string $secretKey;

    protected string $bucket;

    protected string $region;

    protected ?string $publicUrl;

    public function __construct()
    {
        $diskConfig = config('filesystems.disks.r2', []);

        $this->accountId = trim((string) ($diskConfig['account_id'] ?? ''));

        $rawEndpoint = trim((string) ($diskConfig['endpoint'] ?? ''));
        if ($rawEndpoint === '' && $this->accountId !== '') {
            $rawEndpoint = "{$this->accountId}.r2.cloudflarestorage.com";
        }

        // Chuẩn hóa chỉ lấy hostname (loại bỏ https:// và path bucket nếu có)
        $parsedHost = parse_url(str_starts_with($rawEndpoint, 'http') ? $rawEndpoint : 'https://'.$rawEndpoint, PHP_URL_HOST);
        $this->endpoint = is_string($parsedHost) && $parsedHost !== ''
            ? $parsedHost
            : preg_replace('#^https?://#i', '', rtrim($rawEndpoint, '/'));

        $this->accessKey = trim((string) ($diskConfig['key'] ?? ''));
        $this->secretKey = trim((string) ($diskConfig['secret'] ?? ''));
        $this->bucket = trim((string) ($diskConfig['bucket'] ?? 'mayhen'));
        $this->region = trim((string) ($diskConfig['region'] ?? 'auto')) ?: 'auto';

        $rawPublicUrl = $diskConfig['url'] ?? 'https://media.mayhen.vn';
        $this->publicUrl = ! empty($rawPublicUrl) ? rtrim((string) $rawPublicUrl, '/') : 'https://media.mayhen.vn';
    }

    /**
     * Kiểm tra xem thông tin kết nối Cloudflare R2 đã được cấu hình đầy đủ chưa.
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
     * Lấy hostname S3 API endpoint hiện tại.
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * Lấy Public Base URL (ví dụ https://media.mayhen.vn).
     */
    public function getPublicUrl(): ?string
    {
        return $this->publicUrl;
    }

    /**
     * Sinh URL công khai cho một đường dẫn tệp qua custom domain (https://media.mayhen.vn).
     */
    public function url(string $path): string
    {
        $path = trim($path);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            if ($this->publicUrl && (str_contains($path, 'r2.cloudflarestorage.com') || str_contains($path, '.r2.dev'))) {
                $storageRelPath = $this->extractStoragePath($path);

                return "{$this->publicUrl}/{$storageRelPath}";
            }

            return $path;
        }

        $cleanPath = ltrim($this->extractStoragePath($path), '/');

        if ($this->publicUrl) {
            return "{$this->publicUrl}/{$cleanPath}";
        }

        return "https://{$this->endpoint}/{$this->bucket}/{$cleanPath}";
    }

    /**
     * Tải tệp lên Cloudflare R2 Object Storage (tự động fallback an toàn về đĩa public nếu có sự cố).
     *
     * @param  UploadedFile|string  $file  Tệp tải lên, đường dẫn tệp cục bộ hoặc chuỗi binary raw
     * @param  string  $directory  Thư mục đích (mặc định weddings/photos)
     * @param  string|null  $customFilename  Tên tệp tùy chỉnh (nếu rỗng tự sinh UUID)
     * @return array{success: bool, url: string, relative_url: string, storage_path: string, file_name: string, disk: string}
     */
    public function upload(
        UploadedFile|string $file,
        string $directory = 'weddings/photos',
        ?string $customFilename = null
    ): array {
        $directory = trim($directory, '/');

        $allowedExtensions = [
            'jpeg', 'png', 'jpg', 'webp', 'gif', 'heic', 'heif', 'avif', 'svg',
            'mp3', 'm4a', 'wav', 'aac', 'ogg', 'pdf',
        ];

        // 1. Phân giải tên tệp và nội dung
        if ($file instanceof UploadedFile) {
            $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
            if (empty($ext) || ! in_array($ext, $allowedExtensions, true)) {
                $ext = 'jpg';
            }

            $filename = $customFilename ?: self::buildReadableFilename($file->getClientOriginalName(), $ext);
            $mimeType = $file->getMimeType() ?: 'application/octet-stream';
            $content = (string) file_get_contents($file->getRealPath());
        } elseif (is_string($file) && file_exists($file)) {
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION) ?: 'jpg');
            $filename = $customFilename ?: self::buildReadableFilename(basename($file), $ext);
            $mimeType = mime_content_type($file) ?: 'application/octet-stream';
            $content = (string) file_get_contents($file);
        } else {
            // Chuỗi nội dung raw (ví dụ base64 decode ảnh chữ ký / photo wish)
            $filename = $customFilename ?: self::buildReadableFilename('media', 'bin');
            $mimeType = $this->guessMimeTypeFromFilename($filename);
            $content = (string) $file;
        }

        $storagePath = "{$directory}/{$filename}";

        // 2. Ưu tiên tải lên Cloudflare R2 Object Storage nếu đã cấu hình
        if ($this->isConfigured()) {
            try {
                $uploaded = $this->putObject($storagePath, $content, $mimeType);
                if ($uploaded) {
                    $fileUrl = $this->url($storagePath);

                    return [
                        'success' => true,
                        'url' => $fileUrl,
                        'relative_url' => $fileUrl,
                        'storage_path' => $storagePath,
                        'file_name' => $filename,
                        'disk' => 'r2',
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning('Cloudflare R2 upload encountered an error, falling back to local public storage: '.$e->getMessage(), [
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
     * Xóa tệp trên Cloudflare R2 Object Storage hoặc Local Storage.
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
                Log::warning('Cloudflare R2 delete failed: '.$e->getMessage(), ['path' => $path]);
            }
        }

        // Đồng thời dọn dẹp trên local public nếu còn sót
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        return true;
    }

    /**
     * Gửi yêu cầu PUT Object lên Cloudflare R2 (Path-Style S3 API, AWS Signature Version 4).
     */
    public function putObject(string $path, string $content, string $mimeType = 'application/octet-stream'): bool
    {
        $host = $this->endpoint;
        $uri = '/'.rawurlencode($this->bucket).'/'.$this->encodeS3Key($path);

        $headers = [
            'host' => $host,
            'content-type' => $mimeType,
        ];

        $signedHeaders = $this->signV4('PUT', $uri, '', $headers, $content);
        $curlHeaders = [];
        foreach ($signedHeaders as $k => $v) {
            $curlHeaders[] = "{$k}: {$v}";
        }

        $url = "https://{$host}{$uri}";
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

        Log::error("Cloudflare R2 PUT failed [{$httpCode}]: {$response} | cURL error: {$curlError}", [
            'url' => $url,
        ]);

        return false;
    }

    /**
     * Gửi yêu cầu DELETE Object lên Cloudflare R2 (Path-Style S3 API, AWS Signature Version 4).
     */
    public function deleteObject(string $path): bool
    {
        $host = $this->endpoint;
        $uri = '/'.rawurlencode($this->bucket).'/'.$this->encodeS3Key($path);

        $headers = [
            'host' => $host,
        ];

        $signedHeaders = $this->signV4('DELETE', $uri, '', $headers, '');
        $curlHeaders = [];
        foreach ($signedHeaders as $k => $v) {
            $curlHeaders[] = "{$k}: {$v}";
        }

        $url = "https://{$host}{$uri}";
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
     * Kiểm tra trạng thái kết nối Cloudflare R2 Object Storage (Chẩn đoán hạ tầng).
     *
     * @return array{success: bool, latency_ms: float, message: string}
     */
    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'latency_ms' => 0,
                'message' => 'Chưa cấu hình đầy đủ CLOUDFLARE_ACCOUNT_ID, CLOUDFLARE_R2_ACCESS_KEY_ID, CLOUDFLARE_R2_SECRET_ACCESS_KEY hoặc CLOUDFLARE_R2_BUCKET_NAME trong .env.',
            ];
        }

        $startTime = microtime(true);

        try {
            $testFile = 'diagnostics/test-'.time().'.txt';
            $testContent = 'Cloudflare R2 Diagnostic Ping at '.now()->toIso8601String();

            $putSuccess = $this->putObject($testFile, $testContent, 'text/plain');
            if (! $putSuccess) {
                $latency = round((microtime(true) - $startTime) * 1000, 2);

                return [
                    'success' => false,
                    'latency_ms' => $latency,
                    'message' => "Không thể ghi tệp thử nghiệm lên Cloudflare R2 Bucket [{$this->bucket}]. Vui lòng kiểm tra lại Account ID, tên Bucket và cặp khóa API R2.",
                ];
            }

            // Dọn dẹp tệp thử nghiệm
            $this->deleteObject($testFile);

            $latency = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'success' => true,
                'latency_ms' => $latency,
                'message' => "Kết nối Cloudflare R2 Object Storage (Bucket: {$this->bucket}) phản hồi hoàn hảo ({$latency} ms).",
            ];
        } catch (\Throwable $e) {
            $latency = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'success' => false,
                'latency_ms' => $latency,
                'message' => 'Lỗi kết nối Cloudflare R2: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Trích xuất đường dẫn tương đối từ URL lưu trữ (media.mayhen.vn, r2.cloudflarestorage.com, r2.dev hoặc /storage/).
     */
    public function extractStoragePath(string $pathOrUrl): string
    {
        $path = trim($pathOrUrl);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $parsed = parse_url($path, PHP_URL_PATH);
            $path = (string) $parsed;
        }

        $path = ltrim($path, '/');

        // Nếu là URL dạng path-style (r2.cloudflarestorage.com/{bucket}/path...)
        if (! empty($this->bucket) && str_starts_with($path, $this->bucket.'/')) {
            $path = substr($path, strlen($this->bucket) + 1);
        }

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        return ltrim($path, '/');
    }

    /**
     * Tạo tên tệp dạng slug tiếng Việt không dấu dễ đọc, có ngày tạo & hậu tố ngắn chống trùng (thay cho UUID dài khó đọc).
     */
    public static function buildReadableFilename(?string $originalName, string $ext, ?string $prefixSlug = null): string
    {
        $rawBase = pathinfo((string) $originalName, PATHINFO_FILENAME);
        $slug = Str::slug($rawBase, '-');

        if ($slug === '' || in_array($slug, ['blob', 'image', 'img', 'photo', 'file', 'media', 'tmp'], true)) {
            $slug = $prefixSlug ? Str::slug($prefixSlug, '-') : 'anh-cuoi-mayhen';
        }

        if ($prefixSlug && ! str_starts_with($slug, Str::slug($prefixSlug, '-'))) {
            $prefix = Str::limit(Str::slug($prefixSlug, '-'), 24, '');
            $slug = trim($prefix.'-'.$slug, '-');
        }

        $slug = trim(substr($slug, 0, 48), '-');
        $dateStamp = now()->format('Ymd');
        $shortRand = Str::lower(Str::random(4));
        $cleanExt = strtolower(ltrim($ext, '.')) ?: 'jpg';

        return "{$slug}-{$dateStamp}-{$shortRand}.{$cleanExt}";
    }

    /**
     * Encode từng segment của S3 key theo chuẩn RFC 3986 (giữ nguyên dấu /).
     */
    protected function encodeS3Key(string $key): string
    {
        $segments = explode('/', ltrim($key, '/'));

        return implode('/', array_map('rawurlencode', $segments));
    }

    /**
     * Suy đoán MIME type cho dữ liệu raw dựa theo đuôi tệp.
     */
    protected function guessMimeTypeFromFilename(string $filename): string
    {
        return match (strtolower(pathinfo($filename, PATHINFO_EXTENSION))) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'avif' => 'image/avif',
            'svg' => 'image/svg+xml',
            'mp3' => 'audio/mpeg',
            'm4a' => 'audio/mp4',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'pdf' => 'application/pdf',
            default => 'application/octet-stream',
        };
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
     * Ký thuật toán AWS Signature Version 4 chuẩn xác cho Cloudflare R2 S3 API.
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
