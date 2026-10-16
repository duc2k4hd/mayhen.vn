<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Danh sách ngân hàng & ví điện tử hợp tác VietQR NAPAS
    |--------------------------------------------------------------------------
    | Dùng để sinh mã VietQR chuyển khoản mừng cưới chuẩn xác và an toàn tuyệt đối.
    */
    'supported' => [
        // ── Nhóm Big 4 ──
        [ 'code' => 'VCB',             'short_name' => 'Vietcombank',        'name' => 'Ngân hàng TMCP Ngoại Thương Việt Nam' ],
        [ 'code' => 'CTG',             'short_name' => 'VietinBank',         'name' => 'Ngân hàng TMCP Công thương Việt Nam' ],
        [ 'code' => 'BIDV',            'short_name' => 'BIDV',               'name' => 'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam' ],
        [ 'code' => 'VBA',             'short_name' => 'Agribank',           'name' => 'Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam' ],

        // ── Nhóm TMCP lớn ──
        [ 'code' => 'TCB',             'short_name' => 'Techcombank',        'name' => 'Ngân hàng TMCP Kỹ thương Việt Nam' ],
        [ 'code' => 'MB',              'short_name' => 'MBBank',             'name' => 'Ngân hàng TMCP Quân đội' ],
        [ 'code' => 'ACB',             'short_name' => 'ACB',                'name' => 'Ngân hàng TMCP Á Châu' ],
        [ 'code' => 'VPB',             'short_name' => 'VPBank',             'name' => 'Ngân hàng TMCP Việt Nam Thịnh Vượng' ],
        [ 'code' => 'TPB',             'short_name' => 'TPBank',             'name' => 'Ngân hàng TMCP Tiên Phong' ],
        [ 'code' => 'STB',             'short_name' => 'Sacombank',          'name' => 'Ngân hàng TMCP Sài Gòn Thương Tín' ],
        [ 'code' => 'HDB',             'short_name' => 'HDBank',             'name' => 'Ngân hàng TMCP Phát triển TP. Hồ Chí Minh' ],
        [ 'code' => 'VIB',             'short_name' => 'VIB',                'name' => 'Ngân hàng TMCP Quốc tế Việt Nam' ],
        [ 'code' => 'SHB',             'short_name' => 'SHB',                'name' => 'Ngân hàng TMCP Sài Gòn - Hà Nội' ],
        [ 'code' => 'OCB',             'short_name' => 'OCB',                'name' => 'Ngân hàng TMCP Phương Đông' ],
        [ 'code' => 'MSB',             'short_name' => 'MSB',                'name' => 'Ngân hàng TMCP Hàng Hải Việt Nam' ],
        [ 'code' => 'LPB',             'short_name' => 'LPBank',             'name' => 'Ngân hàng TMCP Lộc Phát Việt Nam' ],
        [ 'code' => 'SSB',             'short_name' => 'SeABank',            'name' => 'Ngân hàng TMCP Đông Nam Á' ],
        [ 'code' => 'EIB',             'short_name' => 'Eximbank',           'name' => 'Ngân hàng TMCP Xuất Nhập khẩu Việt Nam' ],
        [ 'code' => 'NAB',             'short_name' => 'NamABank',           'name' => 'Ngân hàng TMCP Nam Á' ],
        [ 'code' => 'BAB',             'short_name' => 'BacABank',           'name' => 'Ngân hàng TMCP Bắc Á' ],
        [ 'code' => 'ABB',             'short_name' => 'ABBANK',             'name' => 'Ngân hàng TMCP An Bình' ],
        [ 'code' => 'VIETBANK',        'short_name' => 'VietBank',           'name' => 'Ngân hàng TMCP Việt Nam Thương Tín' ],
        [ 'code' => 'VIETCAPITALBANK', 'short_name' => 'BVBank',             'name' => 'Ngân hàng TMCP Bản Việt' ],
        [ 'code' => 'BVB',             'short_name' => 'BaoVietBank',        'name' => 'Ngân hàng TMCP Bảo Việt' ],
        [ 'code' => 'KLB',             'short_name' => 'KienLongBank',       'name' => 'Ngân hàng TMCP Kiên Long' ],
        [ 'code' => 'SGICB',           'short_name' => 'SaigonBank',         'name' => 'Ngân hàng TMCP Sài Gòn Công Thương' ],
        [ 'code' => 'PGB',             'short_name' => 'PGBank',             'name' => 'Ngân hàng TMCP Thịnh vượng và Phát triển' ],
        [ 'code' => 'GPB',             'short_name' => 'GPBank',             'name' => 'Ngân hàng TM TNHH MTV Dầu Khí Toàn Cầu' ],
        [ 'code' => 'OCEANBANK',       'short_name' => 'OceanBank',          'name' => 'Ngân hàng TM TNHH MTV Đại Dương' ],
        [ 'code' => 'PVCB',            'short_name' => 'PVcomBank',          'name' => 'Ngân hàng TMCP Đại Chúng Việt Nam' ],
        [ 'code' => 'NCB',             'short_name' => 'NCB',                'name' => 'Ngân hàng TMCP Quốc Dân' ],
        [ 'code' => 'VAB',             'short_name' => 'VietABank',          'name' => 'Ngân hàng TMCP Việt Á' ],
        [ 'code' => 'SCB',             'short_name' => 'SCB',                'name' => 'Ngân hàng TMCP Sài Gòn' ],
        [ 'code' => 'CBB',             'short_name' => 'CBBank',             'name' => 'Ngân hàng TM TNHH MTV Xây dựng Việt Nam' ],

        // ── Nhóm Ngân hàng Nước ngoài & Liên doanh ──
        [ 'code' => 'SHBVN',           'short_name' => 'ShinhanBank',        'name' => 'Ngân hàng TNHH MTV Shinhan Việt Nam (Shinhan Bank)' ],
        [ 'code' => 'WVN',             'short_name' => 'Woori',              'name' => 'Ngân hàng TNHH MTV Woori Việt Nam (Woori Bank)' ],
        [ 'code' => 'KBHN',            'short_name' => 'KookminHN',          'name' => 'Ngân hàng Kookmin - Chi nhánh Hà Nội' ],
        [ 'code' => 'KBHCM',           'short_name' => 'KookminHCM',         'name' => 'Ngân hàng Kookmin - Chi nhánh TP. Hồ Chí Minh' ],
        [ 'code' => 'KEBHANAHN',       'short_name' => 'KEBHanaHN',          'name' => 'Ngân hàng KEB Hana – Chi nhánh Hà Nội' ],
        [ 'code' => 'KEBHANAHCM',      'short_name' => 'KEBHanaHCM',         'name' => 'Ngân hàng KEB Hana – Chi nhánh TP. Hồ Chí Minh' ],
        [ 'code' => 'KBank',           'short_name' => 'KBank',              'name' => 'Ngân hàng Đại chúng TNHH Kasikornbank' ],
        [ 'code' => 'SCVN',            'short_name' => 'StandardChartered',  'name' => 'Ngân hàng TNHH MTV Standard Chartered Bank Việt Nam' ],
        [ 'code' => 'PBVN',            'short_name' => 'PublicBank',         'name' => 'Ngân hàng TNHH MTV Public Việt Nam' ],
        [ 'code' => 'HLBVN',           'short_name' => 'HongLeong',          'name' => 'Ngân hàng TNHH MTV Hong Leong Việt Nam' ],
        [ 'code' => 'CIMB',            'short_name' => 'CIMB',               'name' => 'Ngân hàng TNHH MTV CIMB Việt Nam' ],
        [ 'code' => 'HSBC',            'short_name' => 'HSBC',               'name' => 'Ngân hàng TNHH MTV HSBC (Việt Nam)' ],
        [ 'code' => 'CITIBANK',        'short_name' => 'Citibank',           'name' => 'Ngân hàng Citibank, N.A. - Chi nhánh Hà Nội' ],
        [ 'code' => 'UOB',             'short_name' => 'UnitedOverseas',     'name' => 'Ngân hàng United Overseas - Chi nhánh TP. Hồ Chí Minh' ],
        [ 'code' => 'IVB',             'short_name' => 'IndovinaBank',       'name' => 'Ngân hàng TNHH Indovina' ],
        [ 'code' => 'VRB',             'short_name' => 'VRB',                'name' => 'Ngân hàng Liên doanh Việt - Nga' ],
        [ 'code' => 'IBK - HN',        'short_name' => 'IBKHN',              'name' => 'Ngân hàng Công nghiệp Hàn Quốc - CN Hà Nội' ],
        [ 'code' => 'IBK - HCM',       'short_name' => 'IBKHCM',             'name' => 'Ngân hàng Công nghiệp Hàn Quốc - CN TP. HCM' ],
        [ 'code' => 'NHB HN',          'short_name' => 'Nonghyup',           'name' => 'Ngân hàng Nonghyup - Chi nhánh Hà Nội' ],
        [ 'code' => 'DBS',             'short_name' => 'DBSBank',            'name' => 'DBS Bank Ltd - Chi nhánh TP. Hồ Chí Minh' ],

        // ── Ngân hàng Chính sách & Hợp tác xã ──
        [ 'code' => 'COOPBANK',        'short_name' => 'COOPBANK',           'name' => 'Ngân hàng Hợp tác xã Việt Nam' ],
        [ 'code' => 'VBSP',            'short_name' => 'VBSP',               'name' => 'Ngân hàng Chính sách Xã hội' ],

        // ── Ngân hàng số & Ví điện tử ──
        [ 'code' => 'CAKE',            'short_name' => 'CAKE',               'name' => 'Ngân hàng số CAKE by VPBank' ],
        [ 'code' => 'TIMO',            'short_name' => 'Timo',               'name' => 'Ngân hàng số Timo by BVBank' ],
        [ 'code' => 'VTLMONEY',        'short_name' => 'ViettelMoney',       'name' => 'Tổng Công ty Dịch vụ số Viettel (Viettel Money)' ],
        [ 'code' => 'VNPTMONEY',       'short_name' => 'VNPTMoney',          'name' => 'Tập đoàn Bưu chính Viễn thông VNPT (VNPT Money)' ],
        [ 'code' => 'Vikki',           'short_name' => 'Vikki',              'name' => 'Ngân hàng TNHH MTV Số Vikki' ],
        [ 'code' => 'Ubank',           'short_name' => 'Ubank',              'name' => 'Ngân hàng số Ubank by VPBank' ],
        [ 'code' => 'momo',            'short_name' => 'MoMo',               'name' => 'Ví điện tử MoMo' ],
    ],
];
