<?php

use Illuminate\Support\Facades\Cache;

if (! function_exists('asset_v')) {
    /**
     * Generate an asset path for the application with filemtime query parameter for cache busting.
     */
    function asset_v(string $path): string
    {
        // Don't modify external URLs or data URIs
        if (preg_match('#^(https?:)?//#i', $path) || str_starts_with($path, 'data:')) {
            return $path;
        }

        static $versionCache = [];

        $parsedUrl = parse_url($path);
        $cleanPath = ltrim($parsedUrl['path'] ?? $path, '/');

        // Tự động ưu tiên bản rút gọn (.min.css / .min.js) nếu tồn tại và mới hơn hoặc bằng bản gốc
        if (preg_match('/^(.*)(?<!\.min)\.(css|js)$/i', $cleanPath, $m)) {
            $minCandidate = $m[1].'.min.'.$m[2];
            $srcFull = public_path($cleanPath);
            $minFull = public_path($minCandidate);
            if (file_exists($minFull) && (! file_exists($srcFull) || filemtime($minFull) >= filemtime($srcFull))) {
                $cleanPath = $minCandidate;
            }
        }

        if (! isset($versionCache[$cleanPath])) {
            $fullPath = public_path($cleanPath);

            if (file_exists($fullPath)) {
                $versionCache[$cleanPath] = (string) filemtime($fullPath);
            } else {
                $resPath = resource_path($cleanPath);
                if (file_exists($resPath)) {
                    $versionCache[$cleanPath] = (string) filemtime($resPath);
                } else {
                    $versionCache[$cleanPath] = '1.0';
                }
            }
        }

        $version = $versionCache[$cleanPath];

        $baseAsset = asset($cleanPath);
        if (isset($parsedUrl['query']) && $parsedUrl['query'] !== '') {
            if (preg_match('/(?:^|&)v=[^&]*/', $parsedUrl['query'])) {
                $newQuery = preg_replace('/(?:^|&)v=[^&]*/', '&v='.$version, $parsedUrl['query']);
                $newQuery = ltrim($newQuery, '&');

                return $baseAsset.'?'.$newQuery;
            }

            return $baseAsset.'?'.$parsedUrl['query'].'&v='.$version;
        }

        return $baseAsset.'?v='.$version;
    }
}

if (! function_exists('asset_versioned')) {
    /**
     * Alias for asset_v().
     */
    function asset_versioned(string $path): string
    {
        return asset_v($path);
    }
}

if (! function_exists('inline_core_css')) {
    /**
     * Đọc, nối và rút gọn các tệp CSS cốt lõi (tokens, base, layout, buttons, header)
     * để nhúng trực tiếp vào <head>, triệt tiêu hoàn toàn các request chặn hiển thị (render-blocking).
     *
     * @param  array<int, string>  $paths
     */
    function inline_core_css(array $paths): string
    {
        static $ramCache = [];

        $sigParts = [];
        $fullPaths = [];
        foreach ($paths as $rel) {
            $clean = ltrim($rel, '/');
            $full = public_path($clean);
            if (is_file($full)) {
                $fullPaths[] = $full;
                $sigParts[] = $clean.':'.filemtime($full);
            }
        }

        if ($fullPaths === []) {
            return '';
        }

        $cacheKey = 'mayhen_inline_core_css_v2_'.md5(implode('|', $sigParts));
        if (isset($ramCache[$cacheKey])) {
            return $ramCache[$cacheKey];
        }

        return $ramCache[$cacheKey] = Cache::remember($cacheKey, 86400, function () use ($fullPaths): string {
            $combined = '';
            foreach ($fullPaths as $file) {
                $combined .= "\n".(string) file_get_contents($file);
            }

            // Rút gọn CSS an toàn: bỏ comment /* ... */, gộp khoảng trắng thừa
            // TUYỆT ĐỐI giữ nguyên khoảng trắng quanh toán tử + và - để không làm hỏng biểu thức calc(100% + 10px)
            $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $combined) ?? $combined;
            $css = preg_replace('/\s+/', ' ', $css) ?? $css;
            $css = preg_replace('/\s*([{}:;,>~])\s*/', '$1', $css) ?? $css;
            $css = str_replace(';}', '}', $css);

            return trim($css);
        });
    }
}
