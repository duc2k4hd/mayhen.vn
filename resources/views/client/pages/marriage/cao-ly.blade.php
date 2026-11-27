@extends('client.layouts.master')

@section('title', $pageMeta['title'])
@section('meta_description', $pageMeta['meta_description'])
@section('canonical', $pageMeta['canonical'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/marriage.css') }}?v={{ filemtime(public_path('assets/client/css/pages/marriage.css')) }}">
@endpush

@section('content')
<div class="mayhen_marriage_page">
    <section class="mayhen_marriage_hero">
        <div class="mayhen_marriage_container">
            <div class="mayhen_marriage_eyebrow">✦ MÂY HẸN – DUYÊN HỢP · PHÉP TRA CÓ HƯỚNG CAN CHỒNG × CHI VỢ</div>
            <h1 class="mayhen_marriage_h1">{{ $pageMeta['h1'] }}</h1>
            <p class="mayhen_marriage_lead">
                Thấu hiểu nhân duyên, cùng nhau vun đắp trăm năm. Tra cứu trọn bộ 120 tổ hợp Cao Ly Đầu Hình theo quy tắc cổ truyền <strong>Nam dụng Thiên Can – Nữ dụng Địa Chi</strong> với Vòng Thiên Văn Đồng Ngọc, 6 Huy Chương Lục Cảnh và Bảng Tra 120 quẻ tương tác miễn phí.
            </p>

            <nav class="mayhen_marriage_nav" aria-label="Điều hướng Duyên Hợp">
                <a href="{{ route('marriage.caoly.index') }}" class="mayhen_marriage_pill {{ $subView === null ? 'is-active' : '' }}">Vòng Tra Cao Ly</a>
                <a href="{{ route('marriage.caoly.matrix') }}" class="mayhen_marriage_pill {{ $subView === 'bang-tra' ? 'is-active' : '' }}">Bảng Tra 120 Tổ Hợp</a>
                <a href="{{ route('marriage.caoly.scenes') }}" class="mayhen_marriage_pill {{ $subView === 'luc-canh' ? 'is-active' : '' }}">6 Huy Chương Lục Cảnh</a>
                <a href="{{ route('marriage.compatibility.index') }}" class="mayhen_marriage_pill">Xem Tuổi Vợ Chồng Tổng Hợp &rarr;</a>
                @auth
                    <a href="{{ route('marriage.book.index') }}" class="mayhen_marriage_pill">Sổ Nhân Duyên Của Tôi</a>
                @endauth
            </nav>

            {{-- 1. VÒNG THIÊN VĂN ĐỒNG & NGỌC 10 THIÊN CAN × 12 ĐỊA CHI --}}
            <div class="mayhen_marriage_two_col">
                <div class="mayhen_marriage_card mayhen_cao_ly_wheel_wrap">
                    <div style="font-size:11px;letter-spacing:0.14em;color:#9A7228;font-weight:600;">MÂY HẸN · DUYÊN HỢP</div>
                    <h2 class="mayhen_marriage_card_title" style="color:#85182A;">Cao Ly Đầu Hình</h2>
                    <p class="mayhen_marriage_card_sub" style="margin-bottom:10px;"><em>Thiên Can chồng kết nối Địa Chi vợ (bấm trực tiếp trên vòng tròn)</em></p>

                    <div id="mayhenCaoLyWheelSvgWrap"></div>

                    <div class="mayhen_marriage_form_row" style="width:100%;margin-top:14px;text-align:left;">
                        <div class="mayhen_marriage_field">
                            <label for="mayhenCaoLyStemSelect" style="color:#1E4D3B;">Thiên Can chồng</label>
                            <select id="mayhenCaoLyStemSelect" class="mayhen_marriage_select">
                                @foreach ($stems as $idx => $s)
                                    <option value="{{ $idx }}" @selected($idx === 6)>{{ $s['hanzi'] }} – {{ $s['name'] }} ({{ $s['yin_yang'] }} {{ $s['element'] }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mayhen_marriage_field">
                            <label for="mayhenCaoLyBranchSelect" style="color:#85182A;">Địa Chi vợ</label>
                            <select id="mayhenCaoLyBranchSelect" class="mayhen_marriage_select">
                                @foreach ($branches as $idx => $b)
                                    <option value="{{ $idx }}" @selected($idx === 7)>{{ $b['hanzi'] }} – {{ $b['name'] }} ({{ $b['zodiac'] }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mayhen_marriage_card">
                    <div id="mayhenCaoLyResultBox"></div>
                    <div style="margin-top:18px;padding-top:14px;border-top:1px solid var(--mayhen-cao-ly-border);font-size:12.5px;color:#6E6259;line-height:1.6;">
                        <strong>Lưu ý minh bạch nguồn dữ liệu:</strong> Cao Ly Đầu Hình là phép tra có hướng (Can chồng × Chi vợ); đảo vai trò hai người sẽ cho tổ hợp khác. Các bản in cổ truyền có thể tồn tại dị bản phân loại — Mây Hẹn giữ thuật ngữ lịch sử và diễn giải bằng ngôn ngữ nhân văn, không dự đoán chia ly hay tuổi thọ.
                    </div>
                    <div style="margin-top:14px;display:flex;flex-wrap:wrap;gap:10px;">
                        <a href="{{ route('marriage.compatibility.index') }}" class="mayhen_marriage_btn">Xem Tuổi Vợ Chồng Đa Phương Pháp &rarr;</a>
                        <a href="{{ route('bazi.love') }}" class="mayhen_marriage_btn mayhen_marriage_btn--outline">Bát Tự Tình Duyên</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="mayhen_marriage_container">
        {{-- 2. SÁU HUY CHƯƠNG LỤC CẢNH CAO LY ĐẦU HÌNH --}}
        <section class="mayhen_marriage_section" id="luc-canh">
            <div class="mayhen_marriage_card">
                <h2 class="mayhen_marriage_card_title">Sáu Huy Chương Lục Cảnh Cao Ly Đầu Hình</h2>
                <p class="mayhen_marriage_card_sub">Mỗi cảnh mang một hình tượng văn hóa trung lập, không sử dụng hình ảnh gây sợ hãi. Bấm vào từng huy chương để xem ý nghĩa lịch sử và diễn giải hiện đại.</p>

                <div class="mayhen_cao_ly_medals_grid">
                    @foreach ($lucCanh as $key => $sc)
                        <div class="mayhen_cao_ly_medal_card {{ $loop->first ? 'is-active' : '' }}"
                             data-name="{{ $sc['name'] }}"
                             data-hanzi="{{ $sc['hanzi'] }}"
                             data-hist="{{ $sc['historical_note'] }}"
                             data-modern="{{ $sc['modern_insight'] }}"
                             data-tip="{{ $sc['couple_tip'] }}">
                            <svg width="118" height="118" viewBox="0 0 120 120" aria-hidden="true" style="margin-bottom:8px;">
                                <circle cx="60" cy="60" r="56" fill="{{ $sc['color'] }}" stroke="#CFA860" stroke-width="3.5" />
                                <circle cx="60" cy="60" r="42" fill="none" stroke="rgba(250,245,235,0.45)" stroke-width="1.2" />
                                @for ($a = 0; $a < 12; $a++)
                                    <ellipse cx="60" cy="11" rx="2.4" ry="5.5" fill="none" stroke="rgba(244,220,170,0.75)" stroke-width="1" transform="rotate({{ $a * 30 }} 60 60)" />
                                @endfor
                                <text x="60" y="69" text-anchor="middle" fill="#FAF5EB" font-size="27" font-weight="700" font-family="serif">{{ $sc['hanzi'] }}</text>
                            </svg>
                            <div style="font-weight:700;font-size:15px;color:#292524;">{{ $sc['name'] }}</div>
                            <div style="font-size:12.5px;color:#6E6259;">{{ $sc['keyword'] }}</div>
                        </div>
                    @endforeach
                </div>

                @php $firstScene = reset($lucCanh); @endphp
                <div id="mayhenLucCanhActiveDetail" style="margin-top:16px;padding:16px 18px;background:#FAF5EB;border:1px solid var(--mayhen-cao-ly-border);border-radius:12px;font-size:13.5px;line-height:1.6;">
                    <strong style="color:#85182A;font-size:17px;">{{ $firstScene['name'] }} ({{ $firstScene['hanzi'] }})</strong>
                    <p style="margin:6px 0;"><strong>Ngữ cảnh cổ truyền:</strong> {{ $firstScene['historical_note'] }}</p>
                    <p style="margin:6px 0;color:#1E4D3B;"><strong>Diễn giải hiện đại Mây Hẹn:</strong> {{ $firstScene['modern_insight'] }}</p>
                    <p style="margin:6px 0 0;"><strong>Gợi ý cặp đôi:</strong> {{ $firstScene['couple_tip'] }}</p>
                </div>
            </div>
        </section>

        {{-- 3. BẢNG TRA 120 TỔ HỢP TƯƠNG TÁC (10 THIÊN CAN × 12 ĐỊA CHI) --}}
        <section class="mayhen_marriage_section" id="bang-tra-120">
            <div class="mayhen_marriage_card">
                <h2 class="mayhen_marriage_card_title">Bảng Tra 120 Tổ Hợp Cao Ly Đầu Hình (10 Thiên Can × 12 Địa Chi)</h2>
                <p class="mayhen_marriage_card_sub">Chọn bất kỳ ô nào trên ma trận để xác định mã khóa `CLDH-{CAN}-{CHI}` và xem ngay đồ hình tương ứng.</p>

                <div class="mayhen_cao_ly_matrix_wrap">
                    <table class="mayhen_cao_ly_matrix">
                        <thead>
                            <tr>
                                <th>Can / Chi</th>
                                @foreach ($branches as $b)
                                    <th>{{ $b['name'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($stems as $sIdx => $s)
                                <tr>
                                    <th>{{ $s['name'] }}</th>
                                    @foreach ($branches as $bIdx => $b)
                                        <td>
                                            <button type="button" class="mayhen_cao_ly_matrix_cell" data-s="{{ $sIdx }}" data-b="{{ $bIdx }}" aria-label="{{ $s['name'] }} – {{ $b['name'] }}">·</button>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div id="mayhenCaoLyMatrixDetail" style="margin-top:16px;padding-top:14px;border-top:1px solid var(--mayhen-cao-ly-border);"></div>
            </div>
        </section>

        {{-- 4. PILLAR SEO CONTENT CHUẨN GOOGLE E-E-A-T CHO /cao-ly-dau-hinh --}}
        <section class="mayhen_marriage_section">
            <article class="mayhen_marriage_card mayhen_marriage_seo_article">
                <h2>Cao Ly Đầu Hình Là Gì? Nguồn Gốc &amp; Phân Biệt Với "Cao Linh Đầu Hình"</h2>
                <p><strong>Cao Ly Đầu Hình</strong> là một phương pháp xem tuổi vợ chồng cổ truyền quen thuộc trong văn hóa cưới hỏi Việt Nam, được phổ biến rộng rãi qua các bản biên soạn dân gian (tiêu biểu là bản của soạn giả <em>Đoàn Văn Đẩu</em>). Trong đời sống thường ngày, nhiều người tìm kiếm với tên gọi <strong>"Cao Linh Đầu Hình"</strong> hoặc <strong>"Cao Lý Đầu Hình"</strong> — đây thực chất là cách đọc chệch âm từ cùng một hệ thống tra cứu 120 tổ hợp <strong>Can chồng – Chi vợ</strong>.</p>
                <p>Điểm đặc trưng của Cao Ly Đầu Hình là phép tra <strong>không dựa vào 12 con giáp đơn lẻ</strong> mà kết hợp trực tiếp <strong>Thiên Can năm sinh của người chồng</strong> (10 Can) với <strong>Địa Chi năm sinh của người vợ</strong> (12 Chi), tạo nên ma trận đúng <strong>10 × 12 = 120 tổ hợp</strong> có mã khóa duy nhất.</p>

                <h2>Quy Tắc Tra Cứu Chuẩn: Nam Dụng Thiên Can – Nữ Dụng Địa Chi</h2>
                <p>Để tra cứu Cao Ly Đầu Hình chính xác, cần thực hiện đúng 3 bước sau:</p>
                <ul>
                    <li><strong>Bước 1 — Xác định đúng năm Âm lịch của hai người:</strong> Người sinh vào tháng 1 hoặc đầu tháng 2 Dương lịch trước Mùng 1 Tết Nguyên Đán sẽ thuộc <em>Can Chi của năm Âm lịch trước đó</em>. Bạn có thể đối chiếu nhanh tại <a href="{{ route('calendar.index') }}" style="color:#1E4D3B;font-weight:600;">Lịch Âm &amp; Vạn Niên Lịch Mây Hẹn</a>.</li>
                    <li><strong>Bước 2 — Tách Can chồng và Chi vợ:</strong> Ví dụ chồng sinh năm 1990 (<strong>Canh</strong> Ngọ), vợ sinh năm 1991 (Tân <strong>Mùi</strong>). Ta lấy Thiên Can <strong>Canh</strong> của chồng ghép với Địa Chi <strong>Mùi</strong> của vợ để tra đúng bản ghi <strong>Canh – Mùi (`CLDH-CANH-MUI`)</strong>.</li>
                    <li><strong>Bước 3 — Hiểu rõ tính có hướng:</strong> Cao Ly Đầu Hình là phép tra <em>có hướng</em> (Chồng 1990 × Vợ 1991 cho tổ hợp <strong>Canh – Mùi</strong>, còn nếu đảo vai trò Chồng 1991 × Vợ 1990 sẽ ra tổ hợp <strong>Tân – Ngọ</strong> hoàn toàn khác).</li>
                </ul>

                <div class="mayhen_marriage_seo_callout">
                    <strong>Nguyên tắc minh bạch tại Mây Hẹn – Duyên Hợp:</strong> Nhiều bản in dân gian của Cao Ly Đầu Hình tồn tại dị bản về phân loại Cát – Bình – Hung. Mây Hẹn giữ trung thực thuật ngữ lịch sử để tham khảo văn hóa, đồng thời diễn giải bằng ngôn ngữ nhân văn hiện đại — tuyệt đối không chấm điểm `%` may rủi và không khuyên hủy hôn hay bán lễ giải hạn.
                </div>

                <h2>Giải Mã 6 Lục Cảnh Cao Ly Đầu Hình Theo Góc Nhìn Hiện Đại</h2>
                <p>Trong các tài liệu tổng hợp hiện đại, 120 tổ hợp thường được quy chiếu về <strong>Lục Cảnh</strong> (6 nhóm cảnh ngộ biểu tượng). Thay vì nhìn nhận một chiều gây lo lắng, Mây Hẹn đối chiếu cả ý nghĩa cổ truyền lẫn giá trị ứng dụng cho cặp đôi trẻ:</p>

                <div class="mayhen_marriage_seo_table_wrap">
                    <table class="mayhen_marriage_seo_table">
                        <thead>
                            <tr>
                                <th>Lục Cảnh</th>
                                <th>Ý nghĩa cổ truyền</th>
                                <th>Diễn giải nhân văn tại Mây Hẹn</th>
                                <th>Điều cặp đôi nên cùng vun đắp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>1. Nhất Phú Quý (富貴)</strong></td>
                                <td>Biểu trưng sự sung túc, gia đạo đủ đầy</td>
                                <td>Gợi mở tinh thần hoạch định tài chính rõ ràng</td>
                                <td>Thống nhất quỹ chung và mục tiêu 3–5 năm</td>
                            </tr>
                            <tr>
                                <td><strong>2. Nhì Bần Tiện (勤儉)</strong></td>
                                <td>Nhắc giai đoạn đầu cần nhiều nỗ lực vật chất</td>
                                <td>Đề cao tinh thần Cần Kiệm, sẻ chia đồng lòng</td>
                                <td>Lập ngân sách cưới vừa sức, tránh áp lực nợ</td>
                            </tr>
                            <tr>
                                <td><strong>3. Tam Vinh Hiển (榮顯)</strong></td>
                                <td>Biểu trưng uy tín, thuận đường công danh</td>
                                <td>Ủng hộ nhau phát triển sự nghiệp &amp; bản thân</td>
                                <td>Cân bằng lịch công việc với thời gian cho tổ ấm</td>
                            </tr>
                            <tr>
                                <td><strong>4. Tứ Đạt Đạo (達道)</strong></td>
                                <td>Biểu trưng sự hanh thông, vợ chồng đồng tâm</td>
                                <td>Phối hợp nhịp nhàng, tôn trọng thế mạnh nhau</td>
                                <td>Giữ thói quen trò chuyện sâu mỗi cuối tuần</td>
                            </tr>
                            <tr>
                                <td><strong>5. Ngũ Biệt Ly (相思)</strong></td>
                                <td>Cảnh báo sự xa cách về không gian hoặc nhịp sống</td>
                                <td>Không phải kết luận ly hôn — nhắc giữ kết nối</td>
                                <td>Giải quyết bất đồng trong ngày, tránh im lặng</td>
                            </tr>
                            <tr>
                                <td><strong>6. Lục Tuyệt Mạng (珍重)</strong></td>
                                <td>Thuật ngữ cảnh báo mạnh trong văn bản xưa</td>
                                <td><strong>Không liên quan tuổi thọ hay cái chết</strong> — nhắc trân trọng nhau</td>
                                <td>Rèn giao tiếp mềm mỏng và tôn trọng ranh giới</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h2>Vì Sao Nên Tra Cứu Cao Ly Đầu Hình Trên Mây Hẹn?</h2>
                <div class="mayhen_marriage_seo_table_wrap">
                    <table class="mayhen_marriage_seo_table">
                        <thead>
                            <tr>
                                <th>Tiêu chí</th>
                                <th>Các trang bói toán phổ thông</th>
                                <th>Mây Hẹn – Duyên Hợp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Trải nghiệm tra cứu</strong></td>
                                <td>Nhiều quảng cáo bật lên, bảng chữ dài khó đọc trên điện thoại</td>
                                <td>0 quảng cáo rác, Vòng Thiên Văn Đồng Ngọc &amp; Ma trận 120 ô tương tác 60fps</td>
                            </tr>
                            <tr>
                                <td><strong>Độ chuẩn lịch pháp</strong></td>
                                <td>Thường chỉ lấy năm Dương lịch, sai Can Chi với người sinh trước Tết</td>
                                <td>Tích hợp lõi <a href="{{ route('calendar.index') }}" style="color:#1E4D3B;font-weight:600;">Lịch Âm Việt Nam UTC+7</a> xử lý chuẩn trước/sau Tết</td>
                            </tr>
                            <tr>
                                <td><strong>Đa phương pháp</strong></td>
                                <td>Phán xét một chiều hoặc cộng điểm `%` hợp nhau thiếu căn cứ</td>
                                <td>Kết nối trực tiếp sang <a href="{{ route('marriage.compatibility.index') }}" style="color:#85182A;font-weight:600;">Xem Tuổi Vợ Chồng</a>, <a href="{{ route('bazi.love') }}" style="color:#1E4D3B;font-weight:600;">Bát Tự Tình Duyên</a> &amp; <a href="{{ route('templates.index') }}" style="color:#1E4D3B;font-weight:600;">Thiệp Cưới Online</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h2>Câu Hỏi Thường Gặp Về Cao Ly Đầu Hình (FAQ)</h2>
                <div class="mayhen_marriage_faq_list">
                    <details class="mayhen_marriage_faq_item" open>
                        <summary>Cao Ly Đầu Hình có phải là phép xem tuổi duy nhất khi kết hôn không?</summary>
                        <p>Không. Cao Ly Đầu Hình chỉ là một lớp tham khảo dân gian dựa trên Can chồng và Chi vợ. Để có góc nhìn đầy đủ và khách quan hơn, bạn nên đối chiếu thêm Ngũ Hành Nạp Âm, Cung Phi Bát Trạch Du Niên tại công cụ <a href="{{ route('marriage.compatibility.index') }}" style="color:#85182A;font-weight:600;">Xem Tuổi Vợ Chồng Tổng Hợp</a> và quan trọng nhất là sự thấu hiểu thực tế giữa hai người.</p>
                    </details>
                    <details class="mayhen_marriage_faq_item">
                        <summary>Gặp cảnh Ngũ Biệt Ly hoặc Lục Tuyệt Mạng có phải không nên cưới nhau không?</summary>
                        <p>Hoàn toàn không. Đây là thuật ngữ mang tính ước lệ của văn bản cổ học, không phải chẩn đoán về tuổi thọ, bệnh tật hay kết cục ly hôn. Hôn nhân bền vững được quyết định bởi cách hai người giao tiếp, tôn trọng, sẻ chia tài chính và đồng hành cùng nhau mỗi ngày.</p>
                    </details>
                    <details class="mayhen_marriage_faq_item">
                        <summary>Sinh vào tháng 1 Dương lịch thì tra Cao Ly theo năm nào?</summary>
                        <p>Nếu bạn sinh trước ngày Mùng 1 Tết Nguyên Đán của năm đó, Can Chi năm sinh theo lịch Việt Nam vẫn thuộc năm Âm lịch trước. Hãy nhập đủ ngày tháng năm sinh tại trang Xem Tuổi Vợ Chồng để hệ thống tự động quy đổi chuẩn UTC+7.</p>
                    </details>
                    <details class="mayhen_marriage_faq_item">
                        <summary>Vì sao cùng một tổ hợp Can – Chi nhưng hai website lại ghi Cát/Hung khác nhau?</summary>
                        <p>Do Cao Ly Đầu Hình lưu truyền qua nhiều bản in và bản sao chép dân gian khác nhau theo vùng miền. Vì vậy Mây Hẹn luôn hiển thị rõ mã tổ hợp, nguồn tham khảo và tập trung vào gợi ý ứng dụng thực tế thay vì phán xét cực đoan.</p>
                    </details>
                </div>
            </article>
        </section>
    </div>
</div>

@php
    $caoLySchema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebApplication',
                'name' => 'Xem Cao Ly Đầu Hình Online Miễn Phí — Mây Hẹn Duyên Hợp',
                'url' => $pageMeta['canonical'],
                'applicationCategory' => 'LifestyleApplication',
                'operatingSystem' => 'All',
                'offers' => ['@type' => 'Offer', 'price' => '0', 'priceCurrency' => 'VND'],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang Chủ', 'item' => route('home')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Xem Cao Ly Đầu Hình', 'item' => route('marriage.caoly.index')],
                ],
            ],
            [
                '@type' => 'FAQPage',
                'mainEntity' => [
                    ['@type' => 'Question', 'name' => 'Cao Ly Đầu Hình có phải là phép xem tuổi duy nhất khi kết hôn không?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Không. Cao Ly Đầu Hình chỉ là một lớp tham khảo dựa trên Thiên Can chồng và Địa Chi vợ; nên kết hợp thêm Nạp Âm, Cung Phi Bát Trạch và sự thấu hiểu thực tế của cặp đôi.']],
                    ['@type' => 'Question', 'name' => 'Gặp cảnh Ngũ Biệt Ly hoặc Lục Tuyệt Mạng có phải không nên cưới nhau không?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Không. Đây là thuật ngữ ước lệ trong văn bản cổ học, hoàn toàn không phải kết luận về tuổi thọ hay ly hôn; hạnh phúc hôn nhân phụ thuộc vào sự giao tiếp, tôn trọng và đồng hành của hai vợ chồng.']],
                    ['@type' => 'Question', 'name' => 'Sinh vào tháng 1 Dương lịch thì tra Cao Ly theo năm nào?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Người sinh trước Mùng 1 Tết Nguyên Đán thuộc Can Chi của năm Âm lịch trước đó. Mây Hẹn hỗ trợ quy đổi tự động theo lịch Việt Nam UTC+7 khi bạn nhập đủ ngày tháng năm sinh.']],
                    ['@type' => 'Question', 'name' => 'Vì sao cùng một tổ hợp Can – Chi nhưng hai website lại ghi Cát/Hung khác nhau?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Do Cao Ly Đầu Hình lưu truyền qua nhiều dị bản in ấn dân gian khác nhau. Mây Hẹn công khai mã tổ hợp, nguồn tham chiếu và diễn giải theo hướng nhân văn, thực tế.']],
                ],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($caoLySchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
<script id="mayhenCaoLyDataset" type="application/json">{!! json_encode(['stems' => array_values($stems), 'branches' => array_values($branches), 'records' => $all120], JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/marriage.js') }}"></script>
@endpush
