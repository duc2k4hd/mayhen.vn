<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\UserActivityLog;
use App\Services\Admin\ToolInsightCollectorService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Middleware ghi nhận hành vi sử dụng các công cụ Mây Hẹn (siêu nhẹ, không làm chậm response).
 * Chỉ ghi log trên các nhóm route công cụ/tạo dữ liệu trọng yếu; bỏ qua admin, asset tĩnh và API nội bộ không liên quan.
 */
class TrackToolUserActivity
{
    /** @var array<string, string> Prefix URL => tool_key */
    protected const TRACKED_PREFIXES = [
        'xem-ngay-cuoi' => 'wedding_date',
        'to-chuc-dam-cuoi' => 'wedding_journey',
        'cao-ly-dau-hinh' => 'marriage_compatibility',
        'xem-tuoi-vo-chong' => 'marriage_compatibility',
        'bat-tu' => 'bazi',
        'lich-am' => 'calendar',
        'tarot' => 'tarot',
        'than-so-hoc' => 'numerology',
        'tu-vi' => 'tuvi',
        'gieo-que' => 'iching',
        'gia-pha' => 'family_tree',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $path = ltrim($request->path(), '/');
            $toolKey = $this->resolveToolKey($path);
            if ($toolKey === null) {
                return $response;
            }

            if ($response->getStatusCode() >= 500 || ! Schema::hasTable('user_activity_logs')) {
                return $response;
            }

            // Chỉ ghi log đối với thao tác ghi (POST/PATCH/PUT/DELETE) hoặc lượt truy cập trang chính của công cụ
            $method = strtoupper($request->method());
            $isWrite = in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true);
            $isPageView = $method === 'GET' && ! $request->expectsJson();

            if (! $isWrite && ! $isPageView) {
                return $response;
            }

            $routeName = $request->route()?->getName();
            $action = $isWrite ? 'submit_or_mutate' : 'page_view';

            UserActivityLog::create([
                'user_id' => $request->user()?->id,
                'session_id' => $request->hasSession() ? substr((string) $request->session()->getId(), 0, 100) : null,
                'tool_key' => $toolKey,
                'action' => $action,
                'route_name' => $routeName ? substr($routeName, 0, 120) : null,
                'method' => $method,
                'url' => substr($request->fullUrl(), 0, 300),
                'summary' => ($isWrite ? 'Thao tác dữ liệu' : 'Truy cập trang').' · '.$path,
                'metadata_json' => [
                    'status_code' => $response->getStatusCode(),
                    'referer' => $request->headers->get('referer') ? substr((string) $request->headers->get('referer'), 0, 180) : null,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 300),
                'created_at' => now(),
            ]);

            // Tự động lưu dữ liệu người dùng nhập + kết quả phân tích gọn sạch (tách riêng từng loại công cụ) cho AI phân tích nhu cầu
            if ($isWrite && $response->getStatusCode() >= 200 && $response->getStatusCode() < 400) {
                $analysisPayload = [];
                if ($response instanceof JsonResponse) {
                    $decoded = $response->getData(true);
                    if (is_array($decoded)) {
                        $analysisPayload = $decoded;
                    }
                }

                $intent = $routeName ? str_replace('.', '_', $routeName) : str_replace('/', '_', substr($path, 0, 55));
                app(ToolInsightCollectorService::class)->record(
                    $toolKey,
                    $intent,
                    $request->all(),
                    $analysisPayload,
                    null,
                    $request
                );
            }
        } catch (Throwable) {
            // Không bao giờ làm gián đoạn trải nghiệm người dùng nếu ghi log lỗi
        }

        return $response;
    }

    protected function resolveToolKey(string $path): ?string
    {
        foreach (self::TRACKED_PREFIXES as $prefix => $key) {
            if ($path === $prefix || str_starts_with($path, $prefix.'/')) {
                return $key;
            }
        }

        return null;
    }
}
