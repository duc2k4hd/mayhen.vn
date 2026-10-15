@extends('client.layouts.master')

@section('title', 'Bảng Giá Thiệp Cưới Online Chi Tiết '. date('Y').' | ' . config('app.name'))
@section('meta_description', 'Xem bảng giá thiệp cưới online mới nhất '. date('Y').' tại ' . config('app.name') . '. Giá thiệp cưới từ 0đ (miễn phí), gói Plus 149.000đ, Pro 299.000đ dùng trọn đời. Không chi phí ẩn, tiết kiệm đến 90% so với in thiệp giấy.')
@section('meta_keywords', 'bảng giá thiệp cưới, giá thiệp cưới, giá thiệp cưới online, chi phí làm thiệp cưới, giá in thiệp cưới, thiệp cưới online giá rẻ, thiệp cưới online miễn phí, ' . mb_strtolower(config('app.name')))

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => url('/bang-gia') . '/#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Trang chủ',
                    'item' => url('/'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => 'Bảng giá',
                    'item' => url('/bang-gia'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => 'Bảng giá thiệp cưới',
                    'item' => url('/bang-gia'),
                ],
            ],
        ],
        [
            '@type' => 'Product',
            '@id' => url('/bang-gia') . '/#product',
            'name' => 'Thiệp Cưới Online ' . config('app.name'),
            'description' => 'Nền tảng tạo thiệp cưới online, thiệp cưới điện tử cao cấp. Bảng giá thiệp cưới trọn gói dùng trọn đời không phát sinh phí ẩn.',
            'brand' => [
                '@type' => 'Brand',
                'name' => config('app.name'),
            ],
            'offers' => [
                '@type' => 'AggregateOffer',
                'priceCurrency' => 'VND',
                'lowPrice' => '0',
                'highPrice' => '299000',
                'offerCount' => '3',
            ],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => url('/bang-gia') . '/#faq',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => 'Giá thiệp cưới online tại ' . config('app.name') . ' là bao nhiêu?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Bảng giá thiệp cưới online tại ' . config('app.name') . ' gồm 3 gói: Gói Trải Nghiệm (Free) giá 0đ, Gói Hạnh Phúc (Plus) giá 149.000đ và Gói Trọn Vẹn (Pro VIP) giá 299.000đ. Toàn bộ thanh toán 1 lần duy nhất cho toàn bộ đám cưới, dùng trọn đời mà không thu phí duy trì hàng tháng.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Chi phí làm thiệp cưới online so với giá in thiệp cưới giấy chênh lệch thế nào?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Một đám cưới trung bình từ 300 - 500 khách in thiệp giấy thường tốn từ 3.000.000đ đến 6.000.000đ, chưa kể thời gian đi phát tận tay. Với giá thiệp cưới online chỉ từ 0đ đến 149.000đ - 299.000đ, bạn có thể gửi link cho hàng ngàn khách mời qua Zalo và mạng xã hội, tiết kiệm tới 90% chi phí.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Gói thiệp cưới miễn phí (0đ) có bị giới hạn khách mời hay bị xóa sau đám cưới không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Gói miễn phí tại ' . config('app.name') . ' cho phép bạn tạo 1 thiệp cưới tối giản, xem trước và chia sẻ không giới hạn số lượng khách truy cập. Thiệp không bị chèn quảng cáo khó chịu của bên thứ ba.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Đám cưới có 2 lễ ở 2 quê (Nhà Trai và Nhà Gái) thì tính giá thiệp cưới thế nào?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Với Gói Trọn Vẹn (Pro), hệ thống tự động nhân bản cho bạn 2 đường dẫn thiệp riêng biệt: 1 link Lễ Thành Hôn dành cho Nhà Trai và 1 link Lễ Vu Quy dành cho Nhà Gái. Khách nhà nào sẽ nhận đúng ngày giờ và địa chỉ của nhà đó, không cần mua thêm gói thứ hai.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Có phát sinh khoản phí duy trì, phí server hay phí nhận tiền mừng cưới không?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Hoàn toàn không. ' . config('app.name') . ' cam kết không phí duy trì server, không phí gia hạn ẩn. Tiền mừng cưới qua mã QR chuyển thẳng 100% vào tài khoản ngân hàng chính chủ của cô dâu chú rể, ' . config('app.name') . ' không giữ tiền và không thu bất kỳ đồng phí giao dịch nào.',
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => 'Mất bao lâu để hoàn thiện một mẫu thiệp cưới sau khi chọn gói?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Bạn chỉ mất khoảng 2 phút với mẫu thiệp tối giản (nhập tên và địa điểm tiệc cưới) hoặc 5 đến 10 phút với mẫu thiệp hoa lá, phong thư tem sáp đầy đủ album ảnh và nhạc nền. Bạn có thể tự tạo và xem thử hoàn toàn miễn phí trước khi quyết định thanh toán.',
                    ],
                ],
            ],
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/pricing.css') }}">
@endpush

@section('content')
    <div class="mayhen_client_layout_container mayhen_pricing_wrapper">
        <!-- ==========================================
             1. HERO HEADER
             ========================================== -->
        <header class="mayhen_pricing_header">
            <span class="mayhen_pricing_kicker">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                Bảng Giá Thiệp Cưới Minh Bạch {{ date('Y') }}
            </span>
            <h1 class="mayhen_pricing_title">
                Bảng Giá Thiệp Cưới Online <br>
                <span class="mayhen_pricing_title_accent">Thanh Toán 1 Lần, Dùng Trọn Đời</span>
            </h1>
            <p class="mayhen_pricing_desc">
                So với giá in thiệp cưới giấy truyền thống từ 3 - 6 triệu đồng, thiệp cưới online {{ config('app.name') }} giúp bạn tiết kiệm đến 90% chi phí. Dùng thử miễn phí, chỉ trả tiền khi bạn hoàn toàn hài lòng với thiệp mời của mình.
            </p>

            <div class="mayhen_pricing_trust_strip">
                <div class="mayhen_pricing_trust_item">
                    <svg class="mayhen_pricing_trust_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Không phí phát sinh ẩn</span>
                </div>
                <div class="mayhen_pricing_trust_item">
                    <svg class="mayhen_pricing_trust_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Không giới hạn lượt khách xem</span>
                </div>
                <div class="mayhen_pricing_trust_item">
                    <svg class="mayhen_pricing_trust_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Không chèn quảng cáo của bên thứ ba</span>
                </div>
                <div class="mayhen_pricing_trust_item">
                    <svg class="mayhen_pricing_trust_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Nhận mừng cưới 0% phí trung gian</span>
                </div>
            </div>
        </header>

        <!-- ==========================================
             2. PRICING CARDS (BẢNG 3 GÓI GIÁ THIỆP CƯỚI)
             ========================================== -->
        <div class="mayhen_pricing_grid" id="bang-gia-cac-goi">
            @foreach ($plans as $plan)
                <div class="mayhen_pricing_card {{ $plan->is_featured ? 'mayhen_pricing_card_featured' : '' }}">
                    @if ($plan->is_featured)
                        <div class="mayhen_pricing_card_badge">85% Cặp Đôi Lựa Chọn</div>
                    @endif

                    <h2 class="mayhen_pricing_name">{{ $plan->name }}</h2>
                    <p class="mayhen_pricing_card_desc">{{ $plan->description }}</p>

                    <div class="mayhen_pricing_price_wrap">
                        <span class="mayhen_pricing_amount">{{ $plan->formatted_price }}</span>
                        @if (!empty($plan->period))
                            <span class="mayhen_pricing_period">{{ $plan->period }}</span>
                        @endif
                    </div>

                    <div class="mayhen_pricing_feature_list">
                        @foreach ($plan->features as $feature)
                            <div class="mayhen_pricing_feature_item">
                                <svg class="mayhen_pricing_feature_check" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ $feature }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mayhen_pricing_card_footer">
                        @if ($plan->slug === 'free')
                            <a href="{{ route('templates.index', ['tier' => 'free']) }}" class="mayhen_client_button_base mayhen_client_button_secondary mayhen_client_button_lg" style="width: 100%; text-align: center; justify-content: center;">
                                {{ $plan->cta }}
                            </a>
                        @elseif ($plan->slug === 'pro')
                            <a href="{{ route('checkout.show', 'pro') }}" class="mayhen_client_button_base mayhen_client_button_secondary mayhen_client_button_lg" style="width: 100%; text-align: center; justify-content: center;">
                                {{ $plan->cta }}
                            </a>
                        @else
                            <a href="{{ route('checkout.show', $plan->slug) }}" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_lg" style="width: 100%; text-align: center; justify-content: center;">
                                {{ $plan->cta }}
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- ==========================================
             3. BOX SO SÁNH GIÁ THIỆP CƯỚI GIẤY VS THIỆP ONLINE
             ========================================== -->
        <section class="mayhen_pricing_section" aria-labelledby="so-sanh-gia-heading">
            <div class="mayhen_pricing_section_header">
                <span class="mayhen_pricing_section_kicker">So Sánh Thực Tế</span>
                <h2 id="so-sanh-gia-heading" class="mayhen_pricing_section_title">
                    Giá Thiệp Cưới Giấy Truyền Thống vs Giá Thiệp Cưới Online
                </h2>
                <p class="mayhen_pricing_section_desc">
                    Để bạn có cái nhìn khách quan nhất trước khi quyết định chi ngân sách cho thiệp mời ngày cưới.
                </p>
            </div>

            <div class="mayhen_pricing_table_wrapper">
                <table class="mayhen_pricing_compare_table">
                    <thead>
                        <tr>
                            <th>Tiêu Chí So Sánh</th>
                            <th>Thiệp Cưới In Giấy Truyền Thống</th>
                            <th>Thiệp Cưới Online {{ config('app.name') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Chi phí cho 300 - 500 khách</td>
                            <td>
                                <span class="mayhen_pricing_badge_bad">3.000.000đ – 6.000.000đ</span>
                                <div>Từ 6.000đ – 15.000đ/thiệp. Nếu in thêm số lượng ít sẽ bị đội giá rất cao.</div>
                            </td>
                            <td>
                                <span class="mayhen_pricing_badge_good">0đ – 149.000đ – 299.000đ</span>
                                <div>Thanh toán 1 lần duy nhất, gửi cho 50 hay 1.000 khách giá vẫn giữ nguyên.</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Thời gian hoàn thành</td>
                            <td>
                                <span class="mayhen_pricing_badge_bad">Chờ từ 7 – 15 ngày</span>
                                <div>Phải chờ nhà in làm phôi, in ấn, vận chuyển và viết tay từng phong bì.</div>
                            </td>
                            <td>
                                <span class="mayhen_pricing_badge_good">Chỉ từ 2 – 5 phút</span>
                                <div>Nhập thông tin trực tiếp trên điện thoại, có link thiệp cưới gửi ngay lập tức.</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Xử lý khi sai sót thông tin</td>
                            <td>
                                <span class="mayhen_pricing_badge_bad">Rất tốn kém & phiền toái</span>
                                <div>Sai ngày, giờ hoặc tên bố mẹ là phải bỏ toàn bộ lô thiệp và tốn tiền in lại từ đầu.</div>
                            </td>
                            <td>
                                <span class="mayhen_pricing_badge_good">Sửa miễn phí trong 10 giây</span>
                                <div>Đăng nhập chỉnh sửa bất kỳ lúc nào, link thiệp tự động cập nhật ngay tức thì.</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Gửi thiệp cho bạn bè ở xa</td>
                            <td>
                                <span class="mayhen_pricing_badge_bad">Khó khăn & dễ thất lạc</span>
                                <div>Phải gửi bưu điện tốn thêm tiền cước, hoặc phải nhờ người chuyển phát hộ.</div>
                            </td>
                            <td>
                                <span class="mayhen_pricing_badge_good">1 chạm qua Zalo / Facebook</span>
                                <div>Gửi link riêng có sẵn tên khách mời, kèm âm thanh và nhạc nền lãng mạn.</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Báo hỷ & đếm số lượng khách (RSVP)</td>
                            <td>
                                <span class="mayhen_pricing_badge_bad">Phải gọi điện hỏi từng người</span>
                                <div>Không biết chính xác có bao nhiêu người đến, rất dễ đặt thừa hoặc thiếu cỗ bàn.</div>
                            </td>
                            <td>
                                <span class="mayhen_pricing_badge_good">Khách bấm xác nhận trên web</span>
                                <div>Hệ thống tự thống kê danh sách khách đi 1 người hay đi kèm gia đình, xuất file Excel dễ dàng.</div>
                            </td>
                        </tr>
                        <tr>
                            <td>Mừng cưới từ xa</td>
                            <td>
                                <span class="mayhen_pricing_badge_bad">Bất tiện cho khách không đến được</span>
                                <div>Khách ngại hỏi số tài khoản, hoặc phải nhờ người đi phong bì hộ.</div>
                            </td>
                            <td>
                                <span class="mayhen_pricing_badge_good">Quét mã QR tiện lợi ngay trên thiệp</span>
                                <div>Mã QR chuyển khoản ngân hàng chính chủ, chúc phúc và mừng cưới chỉ trong 10 giây.</div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ==========================================
             4. BẢNG TÍNH DỰ TOÁN CHI PHÍ CƯỚI THEO QUY MÔ
             ========================================== -->
        <section class="mayhen_pricing_section" aria-labelledby="du-toan-chi-phi-heading">
            <div class="mayhen_pricing_section_header">
                <span class="mayhen_pricing_section_kicker">Tính Toán Thực Tế</span>
                <h2 id="du-toan-chi-phi-heading" class="mayhen_pricing_section_title">
                    Dự Toán Chi Phí & Mức Tiết Kiệm Theo Từng Quy Mô Đám Cưới
                </h2>
                <p class="mayhen_pricing_section_desc">
                    Xem bạn sẽ tiết kiệm được bao nhiêu tiền khi kết hợp giữa thiệp giấy mời họ hàng và thiệp cưới online gửi bạn bè.
                </p>
            </div>

            <div class="mayhen_pricing_calc_grid">
                <div class="mayhen_pricing_calc_card">
                    <div class="mayhen_pricing_calc_scale">Tiệc Ấm Cúng</div>
                    <div class="mayhen_pricing_calc_guests">100 – 150 Khách</div>
                    <div class="mayhen_pricing_calc_row">
                        <span>Giá in thiệp giấy:</span>
                        <strong>1.200.000đ</strong>
                    </div>
                    <div class="mayhen_pricing_calc_row">
                        <span>Giá thiệp online:</span>
                        <strong>149.000đ</strong>
                    </div>
                    <div class="mayhen_pricing_calc_saved_wrap">
                        <div class="mayhen_pricing_calc_saved_label">Tiết kiệm dự kiến</div>
                        <div class="mayhen_pricing_calc_saved_amount">~1.050.000đ</div>
                    </div>
                </div>

                <div class="mayhen_pricing_calc_card">
                    <div class="mayhen_pricing_calc_scale">Tiệc Tiêu Chuẩn</div>
                    <div class="mayhen_pricing_calc_guests">250 – 350 Khách</div>
                    <div class="mayhen_pricing_calc_row">
                        <span>Giá in thiệp giấy:</span>
                        <strong>2.800.000đ</strong>
                    </div>
                    <div class="mayhen_pricing_calc_row">
                        <span>Giá thiệp online:</span>
                        <strong>149.000đ</strong>
                    </div>
                    <div class="mayhen_pricing_calc_saved_wrap">
                        <div class="mayhen_pricing_calc_saved_label">Tiết kiệm dự kiến</div>
                        <div class="mayhen_pricing_calc_saved_amount">~2.650.000đ</div>
                    </div>
                </div>

                <div class="mayhen_pricing_calc_card">
                    <div class="mayhen_pricing_calc_scale">Đại Tiệc Hai Họ</div>
                    <div class="mayhen_pricing_calc_guests">500 – 700 Khách</div>
                    <div class="mayhen_pricing_calc_row">
                        <span>Giá in thiệp giấy:</span>
                        <strong>5.200.000đ</strong>
                    </div>
                    <div class="mayhen_pricing_calc_row">
                        <span>Giá thiệp online:</span>
                        <strong>299.000đ</strong>
                    </div>
                    <div class="mayhen_pricing_calc_saved_wrap">
                        <div class="mayhen_pricing_calc_saved_label">Tiết kiệm dự kiến</div>
                        <div class="mayhen_pricing_calc_saved_amount">~4.900.000đ</div>
                    </div>
                </div>

                <div class="mayhen_pricing_calc_card">
                    <div class="mayhen_pricing_calc_scale">Đám Cưới Quy Mô Lớn</div>
                    <div class="mayhen_pricing_calc_guests">1.000+ Khách</div>
                    <div class="mayhen_pricing_calc_row">
                        <span>Giá in thiệp giấy:</span>
                        <strong>8.500.000đ+</strong>
                    </div>
                    <div class="mayhen_pricing_calc_row">
                        <span>Giá thiệp online:</span>
                        <strong>299.000đ</strong>
                    </div>
                    <div class="mayhen_pricing_calc_saved_wrap">
                        <div class="mayhen_pricing_calc_saved_label">Tiết kiệm dự kiến</div>
                        <div class="mayhen_pricing_calc_saved_amount">> 8.200.000đ</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==========================================
             5. BOX 4 CAM KẾT VỀ GIÁ TẠI {{ mb_strtoupper(config('app.name')) }}
             ========================================== -->
        <section class="mayhen_pricing_section" aria-labelledby="cam-ket-gia-heading">
            <div class="mayhen_pricing_section_header">
                <span class="mayhen_pricing_section_kicker">Sự Khác Biệt</span>
                <h2 id="cam-ket-gia-heading" class="mayhen_pricing_section_title">
                    Tại Sao Giá Thiệp Cưới Online {{ config('app.name') }} Được Tin Dùng?
                </h2>
                <p class="mayhen_pricing_section_desc">
                    Chúng tôi định giá dựa trên giá trị thật, không cạnh tranh bằng cách giấu bớt chi phí rồi đòi tiền phụ thu về sau.
                </p>
            </div>

            <div class="mayhen_pricing_diff_grid">
                <div class="mayhen_pricing_diff_card">
                    <div class="mayhen_pricing_diff_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    </div>
                    <div>
                        <h3 class="mayhen_pricing_diff_title">1. Tuyệt Đối Không Chèn Quảng Cáo Rác</h3>
                        <p class="mayhen_pricing_diff_text">
                            Nhiều website làm thiệp cưới miễn phí thường chèn banner quảng cáo game, vay tiền, thực phẩm chức năng nhấp nháy làm mất đi sự trang nghiêm của ngày cưới. {{ config('app.name') }} cam kết giao diện 100% sạch sẽ, thanh lịch theo chuẩn Quiet Luxury.
                        </p>
                    </div>
                </div>

                <div class="mayhen_pricing_diff_card">
                    <div class="mayhen_pricing_diff_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    </div>
                    <div>
                        <h3 class="mayhen_pricing_diff_title">2. Thanh Toán 1 Lần — Dùng Trọn Đời</h3>
                        <p class="mayhen_pricing_diff_text">
                            Không có chuyện sau ngày cưới link thiệp bị khóa hoặc yêu cầu trả thêm tiền duy trì máy chủ hàng tháng. Mẫu thiệp của bạn vẫn tồn tại như một kỷ niệm đẹp để bạn bè mở xem lại sau 1 năm hay 5 năm.
                        </p>
                    </div>
                </div>

                <div class="mayhen_pricing_diff_card">
                    <div class="mayhen_pricing_diff_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    </div>
                    <div>
                        <h3 class="mayhen_pricing_diff_title">3. Nhận Tiền Mừng Cưới 0% Phí Trung Gian</h3>
                        <p class="mayhen_pricing_diff_text">
                            Khách mời quét mã QR mừng cưới là tiền chuyển thẳng 100% về tài khoản ngân hàng chính chủ của cô dâu chú rể. {{ config('app.name') }} không giữ hộ tiền và không thu bất kỳ đồng phí giao dịch nào.
                        </p>
                    </div>
                </div>

                <div class="mayhen_pricing_diff_card">
                    <div class="mayhen_pricing_diff_icon_wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <div>
                        <h3 class="mayhen_pricing_diff_title">4. Tách Biệt 2 Link Cho Nhà Trai & Nhà Gái</h3>
                        <p class="mayhen_pricing_diff_text">
                            Với Gói Pro, bạn chỉ cần nhập thông tin 1 lần, hệ thống tự động tách làm 2 link riêng (Lễ Vu Quy & Lễ Thành Hôn). Khách bên nào nhận đúng thông tin địa điểm và giờ tiệc bên đó, không lo khách đi nhầm nơi.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==========================================
             6. QUY TRÌNH 3 BƯỚC TẠO THIỆP
             ========================================== -->
        <section class="mayhen_pricing_section" aria-labelledby="cach-tao-thiep-heading">
            <div class="mayhen_pricing_section_header">
                <span class="mayhen_pricing_section_kicker">Rất Đơn Giản</span>
                <h2 id="cach-tao-thiep-heading" class="mayhen_pricing_section_title">
                    Cách Tạo Thiệp Cưới Online Trong 3 Bước
                </h2>
                <p class="mayhen_pricing_section_desc">
                    Ai cũng có thể tự tạo thiệp cưới trên điện thoại chỉ trong vài phút, không cần biết đồ họa hay lập trình.
                </p>
            </div>

            <div class="mayhen_pricing_steps_grid">
                <div class="mayhen_pricing_step_card">
                    <div class="mayhen_pricing_step_num">01</div>
                    <h3 class="mayhen_pricing_step_title">Chọn Mẫu Thiệp Ưng Ý</h3>
                    <p class="mayhen_pricing_step_desc">
                        Lựa chọn phong cách phù hợp với đám cưới của bạn trong kho 50 mẫu: tối giản, hoa lá, truyền thống Song Hỷ, phong thư hoàng gia tem sáp 3D hay bìa tạp chí thời trang.
                    </p>
                </div>

                <div class="mayhen_pricing_step_card">
                    <div class="mayhen_pricing_step_num">02</div>
                    <h3 class="mayhen_pricing_step_title">Điền Thông Tin Cưới</h3>
                    <p class="mayhen_pricing_step_desc">
                        Nhập tên cô dâu chú rể, ngày giờ làm lễ, địa chỉ sảnh tiệc, tải lên vài bức ảnh cưới và dán link bài hát bạn thích. Mọi thao tác đều có hướng dẫn rõ ràng từng bước.
                    </p>
                </div>

                <div class="mayhen_pricing_step_card">
                    <div class="mayhen_pricing_step_num">03</div>
                    <h3 class="mayhen_pricing_step_title">Xem Thử & Gửi Khách Mời</h3>
                    <p class="mayhen_pricing_step_desc">
                        Mở xem thử trực tiếp trên điện thoại của bạn hoàn toàn miễn phí. Khi thấy thiệp đã đẹp và chuẩn xác 100%, bạn mới cần chọn gói để nhận link chính thức gửi cho bạn bè.
                    </p>
                </div>
            </div>
        </section>

        <!-- ==========================================
             7. FAQ CHUẨN SEO
             ========================================== -->
        <section class="mayhen_pricing_section" aria-labelledby="faq-gia-heading">
            <div class="mayhen_pricing_section_header">
                <span class="mayhen_pricing_section_kicker">Hỏi Đáp Rõ Ràng</span>
                <h2 id="faq-gia-heading" class="mayhen_pricing_section_title">
                    Câu Hỏi Thường Gặp Về Giá Thiệp Cưới Online
                </h2>
                <p class="mayhen_pricing_section_desc">
                    Giải đáp trung thực mọi băn khoăn của cô dâu chú rể trước khi kích hoạt gói dịch vụ.
                </p>
            </div>

            <div class="mayhen_pricing_faq_list">
                <article class="mayhen_pricing_faq_item">
                    <h3 class="mayhen_pricing_faq_q">
                        <span class="mayhen_pricing_faq_q_badge">Q.</span>
                        Giá thiệp cưới online tại {{ config('app.name') }} là bao nhiêu? Có phát sinh thêm tiền không?
                    </h3>
                    <p class="mayhen_pricing_faq_a">
                        {{ config('app.name') }} có 3 gói niêm yết rõ ràng: <strong>Gói Trải Nghiệm (Free) giá 0đ</strong>, <strong>Gói Hạnh Phúc (Plus) giá 149.000đ</strong> và <strong>Gói Trọn Vẹn (Pro) giá 299.000đ</strong>. Đây là mức giá trọn gói thanh toán đúng 1 lần cho cả đám cưới, tuyệt đối không có phụ phí hay phát sinh bất kỳ khoản nào khác.
                    </p>
                </article>

                <article class="mayhen_pricing_faq_item">
                    <h3 class="mayhen_pricing_faq_q">
                        <span class="mayhen_pricing_faq_q_badge">Q.</span>
                        Tôi có thể tự làm thiệp cưới và xem thử trước khi trả tiền không?
                    </h3>
                    <p class="mayhen_pricing_faq_a">
                        Chắc chắn có. Bạn hoàn toàn có thể tự chọn mẫu, điền thông tin và bấm xem trước (Live Demo) ngay trên điện thoại miễn phí 100%. Khi nào bạn thấy giao diện thiệp thực sự ưng ý thì mới cần thanh toán để xuất bản link chính thức.
                    </p>
                </article>

                <article class="mayhen_pricing_faq_item">
                    <h3 class="mayhen_pricing_faq_q">
                        <span class="mayhen_pricing_faq_q_badge">Q.</span>
                        Đám cưới có 2 lễ (Nhà Trai và Nhà Gái ở 2 quê) thì nên chọn gói nào?
                    </h3>
                    <p class="mayhen_pricing_faq_a">
                        Bạn nên chọn <strong>Gói Trọn Vẹn (Pro 299.000đ)</strong>. Gói này hỗ trợ tạo 1 lần nhưng sinh ra 2 link riêng biệt: 1 link Lễ Thành Hôn cho Nhà Trai và 1 link Lễ Vu Quy cho Nhà Gái với thời gian, sơ đồ bàn tiệc và số tài khoản nhận mừng cưới tách riêng, giúp khách hai bên không bị nhầm lẫn.
                    </p>
                </article>

                <article class="mayhen_pricing_faq_item">
                    <h3 class="mayhen_pricing_faq_q">
                        <span class="mayhen_pricing_faq_q_badge">Q.</span>
                        Khách mời lớn tuổi trong gia đình có biết mở xem thiệp cưới online không?
                    </h3>
                    <p class="mayhen_pricing_faq_a">
                        Tất cả mẫu thiệp tại {{ config('app.name') }} đều được tối ưu giao diện cực kỳ trực quan, font chữ to rõ ràng, nút bấm lớn. Người nhận chỉ cần bấm vào đường link gửi qua Zalo là xem được ngay trên trình duyệt mà không cần cài đặt thêm ứng dụng nào.
                    </p>
                </article>

                <article class="mayhen_pricing_faq_item">
                    <h3 class="mayhen_pricing_faq_q">
                        <span class="mayhen_pricing_faq_q_badge">Q.</span>
                        Sau khi đám cưới diễn ra thì link thiệp có bị xóa mất không?
                    </h3>
                    <p class="mayhen_pricing_faq_a">
                        Không. Link thiệp cưới của bạn được lưu giữ như một kỷ niệm đẹp của hai bạn. Bạn bè, người thân vẫn có thể mở lại xem album ảnh và những lời chúc phúc bất cứ lúc nào.
                    </p>
                </article>

                <article class="mayhen_pricing_faq_item">
                    <h3 class="mayhen_pricing_faq_q">
                        <span class="mayhen_pricing_faq_q_badge">Q.</span>
                        Tiền mừng cưới gửi qua mã QR trên thiệp sẽ về tài khoản của ai?
                    </h3>
                    <p class="mayhen_pricing_faq_a">
                        Tiền mừng chuyển thẳng 100% vào tài khoản ngân hàng cá nhân của cô dâu hoặc chú rể theo mã VietQR chính chủ bạn cài đặt. {{ config('app.name') }} không can thiệp, không giữ tiền và không thu bất kỳ khoản chiết khấu nào.
                    </p>
                </article>
            </div>
        </section>

        <!-- ==========================================
             8. BOTTOM ACTION BOX
             ========================================== -->
        <section class="mayhen_pricing_cta_box">
            <span class="mayhen_pricing_cta_kicker">Bắt Đầu Thật Dễ Dàng</span>
            <h2 class="mayhen_pricing_cta_title">Tự Tạo Thiệp Cưới Online Của Riêng Bạn</h2>
            <p class="mayhen_pricing_cta_desc">
                Chọn phong cách bạn yêu thích trong kho 50 mẫu thiệp cưới thật, điền thông tin và xem trước trực tiếp trên điện thoại hoàn toàn miễn phí.
            </p>
            <div class="mayhen_pricing_cta_actions">
                <a href="{{ route('templates.index') }}" class="mayhen_client_button_base mayhen_client_button_primary mayhen_client_button_lg">
                    Khám Phá 50 Mẫu Thiệp Cưới
                </a>
                <a href="{{ route('templates.index', ['tier' => 'free']) }}" class="mayhen_client_button_base mayhen_client_button_ghost mayhen_client_button_lg" style="color: #FFFFFF; border-color: rgba(255, 255, 255, 0.3);">
                    Tạo Thiệp Miễn Phí (0đ)
                </a>
            </div>
        </section>
    </div>
@endsection
