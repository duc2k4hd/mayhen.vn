<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Services\VietQrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class VietQrController extends Controller
{
    /**
     * Endpoint công khai sinh ảnh Vector SVG VietQR chuẩn Napas 247 thuần PHP.
     */
    public function generate(Request $request, string $bank, string $account): Response
    {
        $amount = (int) $request->query('amount', 0);
        $memo = (string) $request->query('memo', '');
        $size = min(max((int) $request->query('size', 300), 120), 800);

        $svg = VietQrService::generateSvg($bank, $account, $amount, $memo, $size);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=utf-8',
            'Cache-Control' => 'public, max-age=604800, immutable',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Danh sách 50+ Ngân Hàng Việt Nam chuẩn Napas 247 (Phục vụ Select/Dropdown trong Studio & Admin).
     */
    public function banks(): JsonResponse
    {
        $banks = VietQrService::getBankList();

        return response()->json([
            'success' => true,
            'data' => array_values($banks),
        ]);
    }
}

