<?php

use App\Services\VietQrService;

test('vietqr service resolves bank bins accurately from abbreviations and full names', function () {
    expect(VietQrService::resolveBankBin('VCB'))->toBe('970436');
    expect(VietQrService::resolveBankBin('vietcombank'))->toBe('970436');
    expect(VietQrService::resolveBankBin('TCB'))->toBe('970407');
    expect(VietQrService::resolveBankBin('Techcombank'))->toBe('970407');
    expect(VietQrService::resolveBankBin('MB'))->toBe('970422');
    expect(VietQrService::resolveBankBin('MBBank'))->toBe('970422');
    expect(VietQrService::resolveBankBin('ACB'))->toBe('970416');
    expect(VietQrService::resolveBankBin('TPBank'))->toBe('970423');
    expect(VietQrService::resolveBankBin('BIDV'))->toBe('970418');
    expect(VietQrService::resolveBankBin('Agribank'))->toBe('970405');
    // Truyền trực tiếp 6 số BIN
    expect(VietQrService::resolveBankBin('970422'))->toBe('970422');
});

test('vietqr service sanitizes transfer memo to uppercase ascii without accents', function () {
    $memo = 'Mừng Cưới Minh Quân & Hoàng Yến #123';
    $clean = VietQrService::sanitizeMemo($memo);

    expect($clean)->toBe('Mung Cuoi Minh Quan Hoang Yen 123');
});

test('vietqr service generates valid emvco napas 247 payload with crc16 checksum', function () {
    $payload = VietQrService::generatePayload(
        bankNameOrBin: 'Vietcombank',
        accountNumber: '0123456789',
        amount: 500000,
        memo: 'Mung cuoi ban'
    );

    // Bắt đầu bằng Version Format 000201
    expect($payload)->toStartWith('000201');
    // Có Tag 01 Dynamic QR (12)
    expect($payload)->toContain('010212');
    // Có AID của Napas A000000727
    expect($payload)->toContain('A000000727');
    // Có mã BIN Vietcombank 970436
    expect($payload)->toContain('970436');
    // Có số tài khoản
    expect($payload)->toContain('0123456789');
    // Có mã dịch vụ QRIBFTTA
    expect($payload)->toContain('QRIBFTTA');
    // Có tiền tệ 704 (VND)
    expect($payload)->toContain('5303704');
    // Có số tiền 500000
    expect($payload)->toContain('5406500000');
    // Có mã quốc gia VN
    expect($payload)->toContain('5802VN');
    // Có nội dung chuyển khoản
    expect($payload)->toContain('Mung cuoi ban');
    // Có Tag 63 CRC16 dài 4 ký tự hex ở đuôi
    expect(strlen($payload))->toBeGreaterThan(70);
    expect(substr($payload, -8, 4))->toBe('6304');
});

test('vietqr service generates pure php svg and data uri correctly', function () {
    $svg = VietQrService::generateSvg('MBBank', '999988887777', 0, 'Chuc mung hanh phuc', 300);

    expect($svg)->toContain('<svg')
        ->toContain('width="300" height="300"')
        ->toContain('viewBox=')
        ->toContain('</svg>');

    $dataUri = VietQrService::generateDataUri('MBBank', '999988887777');
    expect($dataUri)->toStartWith('data:image/svg+xml;base64,');
});

test('public endpoint returns ultra fast vietqr vector svg with cache headers', function () {
    $response = $this->get('/api/vietqr/vietcombank/0123456789?amount=200000&memo=Mung+cuoi');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/svg+xml; charset=utf-8');
    $response->assertHeader('Cache-Control');
    expect($response->getContent())->toContain('<svg');
});

test('public endpoint returns list of supported banks', function () {
    $response = $this->get(route('api.vietqr.banks'));

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'data' => [
            '*' => ['bin', 'short_name', 'name'],
        ],
    ]);

    $data = $response->json('data');
    expect(count($data))->toBeGreaterThan(30);
});
