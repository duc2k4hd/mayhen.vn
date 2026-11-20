<?php

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

        $parsedUrl = parse_url($path);
        $cleanPath = ltrim($parsedUrl['path'] ?? $path, '/');
        $fullPath = public_path($cleanPath);

        if (file_exists($fullPath)) {
            $version = (string) filemtime($fullPath);
        } else {
            $resPath = resource_path($cleanPath);
            if (file_exists($resPath)) {
                $version = (string) filemtime($resPath);
            } else {
                $version = '1.0';
            }
        }

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
