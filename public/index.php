<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Zero-Boot Static Fast-Path cho tài nguyên mẫu thiệp (/mau-thiep/{slug}/assets/{path})
// Cắt hoàn toàn thời gian boot Laravel + MySQL session (~500ms -> <3ms trên cPanel)
if (
    isset($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'])
    && ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'HEAD')
) {
    $rawPath = (string) parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#^/mau-thiep/([a-z0-9_-]+)/(?:assets/)?(.+)$#', $rawPath, $m) === 1) {
        $slug = $m[1];
        $relPath = ltrim(str_replace(['\\', "\0"], ['/', ''], rawurldecode($m[2])), '/');
        $ext = strtolower(pathinfo($relPath, PATHINFO_EXTENSION));
        $allowedMimes = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'gif' => 'image/gif',
            'mp3' => 'audio/mpeg',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
        ];

        if (isset($allowedMimes[$ext])) {
            $baseDir = realpath(__DIR__.'/../resources/views/client/invitations/'.$slug);
            if ($baseDir !== false) {
                $candidates = in_array($relPath, ['styles.css', 'script.js'], true)
                    ? [$baseDir.'/'.$relPath, $baseDir.'/assets/'.$relPath]
                    : [$baseDir.'/assets/'.$relPath, $baseDir.'/'.$relPath];

                foreach ($candidates as $candidate) {
                    $realTarget = realpath($candidate);
                    if ($realTarget !== false && is_file($realTarget) && str_starts_with($realTarget, $baseDir)) {
                        $mtime = filemtime($realTarget) ?: time();
                        $size = filesize($realTarget) ?: 0;
                        $etag = '"'.md5($realTarget.'|'.$mtime.'|'.$size).'"';

                        header('Content-Type: '.$allowedMimes[$ext]);
                        header('Cache-Control: public, max-age=31536000, immutable');
                        header('ETag: '.$etag);
                        header('Last-Modified: '.gmdate('D, d M Y H:i:s', $mtime).' GMT');
                        header('X-Content-Type-Options: nosniff');

                        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim((string) $_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
                            http_response_code(304);
                            exit;
                        }

                        header('Content-Length: '.$size);
                        if ($_SERVER['REQUEST_METHOD'] !== 'HEAD') {
                            readfile($realTarget);
                        }
                        exit;
                    }
                }
            }
        }
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
