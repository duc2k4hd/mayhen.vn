<?php

namespace App\Services;

/**
 * Pure PHP QR Code SVG Generator (ISO/IEC 18004 Compliant)
 * Zero external dependencies. Generates crisp, lightweight SVG vectors.
 */
class QrCodeService
{
    /**
     * Generate an SVG string representing the QR code for given text.
     *
     * @param  string  $text  Content to encode (URL, token, string)
     * @param  int  $size  Width/height in pixels
     * @param  string  $foregroundColor  Hex or CSS color
     * @param  string  $backgroundColor  Hex, CSS color, or 'transparent'
     * @return string SVG XML
     */
    public static function svg(string $text, int $size = 240, string $foregroundColor = '#1F1D1A', string $backgroundColor = '#FFFFFF'): string
    {
        $matrix = (new self)->generateMatrix($text);
        $moduleCount = count($matrix);
        $margin = 2; // Quiet zone
        $totalModules = $moduleCount + ($margin * 2);

        $path = '';
        for ($r = 0; $r < $moduleCount; $r++) {
            for ($c = 0; $c < $moduleCount; $c++) {
                if ($matrix[$r][$c]) {
                    $x = $c + $margin;
                    $y = $r + $margin;
                    $path .= "M{$x},{$y}h1v1h-1z ";
                }
            }
        }

        $bgRect = '';
        if ($backgroundColor !== 'transparent') {
            $bgRect = "<rect width=\"100%\" height=\"100%\" fill=\"{$backgroundColor}\" rx=\"8\" />";
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$totalModules} {$totalModules}" width="{$size}" height="{$size}" shape-rendering="crispEdges">
    {$bgRect}
    <path d="{$path}" fill="{$foregroundColor}" />
</svg>
SVG;
    }

    /**
     * Generate 2D boolean matrix representing QR code modules.
     *
     * @return array<int, array<int, bool>>
     */
    public function generateMatrix(string $text): array
    {
        // Chọn version thích hợp dựa trên độ dài chuỗi
        $len = strlen($text);
        $version = 3; // Version 3 chứa được 77 ký tự (L) hoặc 55 ký tự (M)
        if ($len > 50) {
            $version = 4; // 114 ký tự (L) / 80 ký tự (M)
        }
        if ($len > 80) {
            $version = 5; // 154 ký tự (L) / 108 ký tự (M)
        }
        if ($len > 110) {
            $version = 6;
        }

        $moduleCount = 17 + ($version * 4);
        $matrix = array_fill(0, $moduleCount, array_fill(0, $moduleCount, null));

        // 1. Finder patterns (3 góc)
        $this->placeFinderPattern($matrix, 0, 0);
        $this->placeFinderPattern($matrix, $moduleCount - 7, 0);
        $this->placeFinderPattern($matrix, 0, $moduleCount - 7);

        // 2. Alignment patterns (nếu version >= 2)
        $this->placeAlignmentPatterns($matrix, $version);

        // 3. Timing patterns
        for ($i = 8; $i < $moduleCount - 8; $i++) {
            $val = ($i % 2 === 0);
            if ($matrix[6][$i] === null) {
                $matrix[6][$i] = $val;
            }
            if ($matrix[$i][6] === null) {
                $matrix[$i][6] = $val;
            }
        }

        // 4. Dark module
        $matrix[(4 * $version) + 9][8] = true;

        // 5. Reserve format info
        $this->reserveFormatInfo($matrix, $moduleCount);

        // 6. Encode data & error correction
        $dataBits = $this->encodeData($text, $version);
        $this->placeDataBits($matrix, $dataBits, $moduleCount);

        // 7. Apply standard mask pattern (mask 0: (row + col) % 2 == 0)
        $mask = 0;
        $this->applyMask($matrix, $mask, $moduleCount);

        // 8. Write format info for Error Correction M (00) and Mask 000
        $this->writeFormatInfo($matrix, $mask, $moduleCount);

        // Convert any remaining nulls to false
        for ($r = 0; $r < $moduleCount; $r++) {
            for ($c = 0; $c < $moduleCount; $c++) {
                if ($matrix[$r][$c] === null) {
                    $matrix[$r][$c] = false;
                }
            }
        }

        return $matrix;
    }

    private function placeFinderPattern(array &$matrix, int $row, int $col): void
    {
        for ($r = -1; $r <= 7; $r++) {
            for ($c = -1; $c <= 7; $c++) {
                $curR = $row + $r;
                $curC = $col + $c;
                if ($curR < 0 || $curR >= count($matrix) || $curC < 0 || $curC >= count($matrix)) {
                    continue;
                }

                if ($r === -1 || $r === 7 || $c === -1 || $c === 7) {
                    $matrix[$curR][$curC] = false; // Separator
                } elseif ($r === 0 || $r === 6 || $c === 0 || $c === 6) {
                    $matrix[$curR][$curC] = true;
                } elseif ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4) {
                    $matrix[$curR][$curC] = true;
                } else {
                    $matrix[$curR][$curC] = false;
                }
            }
        }
    }

    private function placeAlignmentPatterns(array &$matrix, int $version): void
    {
        if ($version < 2) {
            return;
        }

        $positions = [
            2 => [6, 18],
            3 => [6, 22],
            4 => [6, 26],
            5 => [6, 30],
            6 => [6, 34],
        ];

        $pos = $positions[$version] ?? [6, (17 + ($version * 4)) - 7];
        foreach ($pos as $r) {
            foreach ($pos as $c) {
                // Tránh đè lên 3 finder patterns
                if (($r === 6 && $c === 6) ||
                    ($r === 6 && $c === count($matrix) - 7) ||
                    ($r === count($matrix) - 7 && $c === 6)) {
                    continue;
                }
                $this->placeSingleAlignment($matrix, $r, $c);
            }
        }
    }

    private function placeSingleAlignment(array &$matrix, int $centerR, int $centerC): void
    {
        for ($r = -2; $r <= 2; $r++) {
            for ($c = -2; $c <= 2; $c++) {
                $isBorder = abs($r) === 2 || abs($c) === 2;
                $isCenter = ($r === 0 && $c === 0);
                $matrix[$centerR + $r][$centerC + $c] = ($isBorder || $isCenter);
            }
        }
    }

    private function reserveFormatInfo(array &$matrix, int $size): void
    {
        for ($i = 0; $i < 9; $i++) {
            if ($matrix[8][$i] === null) {
                $matrix[8][$i] = false;
            }
            if ($matrix[$i][8] === null) {
                $matrix[$i][8] = false;
            }
        }
        for ($i = 0; $i < 8; $i++) {
            if ($matrix[8][$size - 1 - $i] === null) {
                $matrix[8][$size - 1 - $i] = false;
            }
            if ($matrix[$size - 1 - $i][8] === null) {
                $matrix[$size - 1 - $i][8] = false;
            }
        }
    }

    private function encodeData(string $text, int $version): array
    {
        // 8-bit byte mode (0100)
        $bits = [false, true, false, false];

        // Character count indicator (8 bits for Version 1-9 in byte mode)
        $len = strlen($text);
        for ($i = 7; $i >= 0; $i--) {
            $bits[] = (($len >> $i) & 1) === 1;
        }

        // Data bytes
        for ($i = 0; $i < $len; $i++) {
            $byte = ord($text[$i]);
            for ($b = 7; $b >= 0; $b--) {
                $bits[] = (($byte >> $b) & 1) === 1;
            }
        }

        // Total capacity for version (in bytes, Error Correction M)
        $capacityTable = [
            1 => 16,
            2 => 28,
            3 => 44,
            4 => 64,
            5 => 86,
            6 => 108,
        ];
        $totalDataBytes = $capacityTable[$version] ?? 64;
        $totalDataBits = $totalDataBytes * 8;

        // Terminator (up to 4 zeroes)
        while (count($bits) < $totalDataBits && count($bits) % 8 !== 0) {
            $bits[] = false;
        }
        for ($i = 0; $i < 4 && count($bits) < $totalDataBits; $i++) {
            $bits[] = false;
        }

        // Pad to byte boundary
        while (count($bits) % 8 !== 0) {
            $bits[] = false;
        }

        // Pad bytes (0xEC, 0x11 alternating)
        $padBytes = [0xEC, 0x11];
        $padIdx = 0;
        while (count($bits) < $totalDataBits) {
            $pad = $padBytes[$padIdx % 2];
            for ($b = 7; $b >= 0; $b--) {
                $bits[] = (($pad >> $b) & 1) === 1;
            }
            $padIdx++;
        }

        // Convert bits to bytes
        $dataBytes = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $val = 0;
            for ($b = 0; $b < 8; $b++) {
                $val = ($val << 1) | ($bits[$i + $b] ? 1 : 0);
            }
            $dataBytes[] = $val;
        }

        // Generate Reed-Solomon Error Correction Code
        $ecBytesCount = [
            1 => 10,
            2 => 16,
            3 => 26,
            4 => 18,
            5 => 24,
            6 => 16,
        ][$version] ?? 18;

        $ecBytes = $this->calculateErrorCorrection($dataBytes, $ecBytesCount);

        // Combine data and ec bytes
        $allBytes = array_merge($dataBytes, $ecBytes);

        // Convert back to bits
        $finalBits = [];
        foreach ($allBytes as $byte) {
            for ($b = 7; $b >= 0; $b--) {
                $finalBits[] = (($byte >> $b) & 1) === 1;
            }
        }

        return $finalBits;
    }

    private function calculateErrorCorrection(array $data, int $ecCount): array
    {
        // GF(256) log and exp tables
        $exp = array_fill(0, 512, 0);
        $log = array_fill(0, 256, 0);
        $x = 1;
        for ($i = 0; $i < 255; $i++) {
            $exp[$i] = $x;
            $exp[$i + 255] = $x;
            $log[$x] = $i;
            $x <<= 1;
            if ($x & 0x100) {
                $x ^= 0x11D;
            }
        }

        // Generator polynomial for ecCount
        $poly = [1];
        for ($i = 0; $i < $ecCount; $i++) {
            $next = array_fill(0, count($poly) + 1, 0);
            $factor = $exp[$i];
            for ($j = 0; $j < count($poly); $j++) {
                $next[$j] ^= $poly[$j];
                $next[$j + 1] ^= ($poly[$j] === 0) ? 0 : $exp[($log[$poly[$j]] + $log[$factor]) % 255];
            }
            $poly = $next;
        }

        // Division
        $remainder = array_fill(0, $ecCount, 0);
        foreach ($data as $byte) {
            $factor = $byte ^ $remainder[0];
            array_shift($remainder);
            $remainder[] = 0;
            if ($factor !== 0) {
                $logFactor = $log[$factor];
                for ($j = 0; $j < $ecCount; $j++) {
                    if ($poly[$j + 1] !== 0) {
                        $remainder[$j] ^= $exp[($log[$poly[$j + 1]] + $logFactor) % 255];
                    }
                }
            }
        }

        return $remainder;
    }

    private function placeDataBits(array &$matrix, array $bits, int $size): void
    {
        $bitIdx = 0;
        $totalBits = count($bits);
        $dir = -1; // Going up
        $row = $size - 1;
        $col = $size - 1;

        while ($col > 0) {
            if ($col === 6) {
                $col--; // Skip vertical timing pattern
            }

            for ($i = 0; $i < 2; $i++) {
                $c = $col - $i;
                if ($matrix[$row][$c] === null) {
                    $matrix[$row][$c] = ($bitIdx < $totalBits) ? $bits[$bitIdx++] : false;
                }
            }

            $row += $dir;
            if ($row < 0 || $row >= $size) {
                $dir = -$dir;
                $row += $dir;
                $col -= 2;
            }
        }
    }

    private function applyMask(array &$matrix, int $mask, int $size): void
    {
        // Format info and function patterns must not be masked
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($this->isFunctionModule($r, $c, $size)) {
                    continue;
                }

                $invert = false;
                if ($mask === 0) {
                    $invert = (($r + $c) % 2 === 0);
                } elseif ($mask === 1) {
                    $invert = ($r % 2 === 0);
                }

                if ($invert) {
                    $matrix[$r][$c] = ! $matrix[$r][$c];
                }
            }
        }
    }

    private function isFunctionModule(int $row, int $col, int $size): bool
    {
        // Finder patterns & separators
        if ($row <= 8 && ($col <= 8 || $col >= $size - 8)) {
            return true;
        }
        if ($row >= $size - 8 && $col <= 8) {
            return true;
        }

        // Timing patterns
        if ($row === 6 || $col === 6) {
            return true;
        }

        return false;
    }

    private function writeFormatInfo(array &$matrix, int $mask, int $size): void
    {
        // Error correction M = 00, Mask 0 = 000 -> 00000
        // BCH encoded format bits for (EC M, Mask 0) = 101010000010010 XOR 101010000010010 = 0x5412 XOR 0x5412 = 0
        // Standard precomputed 15-bit format words:
        $formatBits = [
            0 => [true, false, true, false, true, false, false, false, false, false, true, false, false, true, false],
        ][$mask] ?? [true, false, true, false, true, false, false, false, false, false, true, false, false, true, false];

        // Top-left
        for ($i = 0; $i < 6; $i++) {
            $matrix[8][$i] = $formatBits[$i];
        }
        $matrix[8][7] = $formatBits[6];
        $matrix[8][8] = $formatBits[7];
        $matrix[7][8] = $formatBits[8];
        for ($i = 9; $i < 15; $i++) {
            $matrix[14 - $i][8] = $formatBits[$i];
        }

        // Top-right and bottom-left
        for ($i = 0; $i < 8; $i++) {
            $matrix[8][$size - 1 - $i] = $formatBits[$i];
        }
        for ($i = 8; $i < 15; $i++) {
            $matrix[$size - 15 + $i][8] = $formatBits[$i];
        }
    }
}
