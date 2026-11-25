@extends('client.layouts.master')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $canonicalUrl = route('policy.pricing');
@endphp

@section('title', 'Chính Sách Giá Dịch Vụ Thiệp Online & In Thiệp Giấy')
@section('meta_description', 'Chính sách niêm yết giá minh bạch tại ' . $brandName . ' (mayhen.vn): đồng tiền VND, chi tiết 4 gói Thiệp Online (0đ - 199.000đ), báo giá In Thiệp Cưới Giấy, thuế, hóa đơn và cam kết không phí ẩn.')
@section('canonical', $canonicalUrl)
@section('robots', 'index, follow, max-image-preview:large, max-snippet:-1')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/legal.css') }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/legal.js') }}"></script>
@endpush

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@' . 'context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'BreadcrumbList',
            '@id' => $canonicalUrl . '#breadcrumb',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Trang chủ', 'item' => route('home')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'Chính sách giá', 'item' => $canonicalUrl],
            ],
        ],
        [
            '@type' => 'WebPage',
            '@id' => $canonicalUrl . '#webpage',
            'url' => $canonicalUrl,
            'name' => 'Chính Sách Giá — ' . $brandName,
            'inLanguage' => 'vi-VN',
            'datePublished' => '2026-01-01T00:00:00+07:00',
            'dateModified' => '2026-11-22T00:00:00+07:00',
        ],
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@section('content')
<div class="mayhen_legal_wrapper">
    <header class="mayhen_legal_hero">
        <div class="mayhen_legal_hero_inner">
            <div class="mayhen_legal_kicker_wrap">
                <span class="mayhen_legal_kicker">Công Bố Bắt Buộc TMĐT • Mục 03</span>
                <span class="mayhen_legal_badge">Minh Bạch 100% — Không Phí Ẩn</span>
            </div>
            <h1 class="mayhen_legal_title">Chính Sách Giá Dịch Vụ &amp; Sản Phẩm {{ $brandName }}</h1>
            <p class="mayhen_legal_lead">
                Công bố công khai nguyên tắc niêm yết giá, cơ cấu gói dịch vụ Thiệp Online, báo giá Dịch vụ In Thiệp Giấy Cao Cấp, chính sách thuế và cam kết không thu phí phát sinh tại {{ $brandName }} (mayhen.vn).
            </p>
            <div class="mayhen_legal_meta_bar">
                <span class="mayhen_legal_meta_item">Đơn vị tiền tệ: Việt Nam Đồng (VNĐ)</span>
                <span class="mayhen_legal_meta_dot">&bull;</span>
                <span class="mayhen_legal_meta_item">Cập nhật: Tháng 11/2026</span>
            </div>
        </div>
    </header>

    <div class="mayhen_legal_body_section">
        <div class="mayhen_legal_container">
            <aside class="mayhen_legal_sidebar" aria-label="Mục lục chính sách giá">
                <div class="mayhen_legal_toc_title">Mục Lục Chính Sách</div>
                <ul class="mayhen_legal_toc_list">
                    <li><a href="#muc-1" class="mayhen_legal_toc_link is-active">1. Nguyên Tắc Niêm Yết Giá</a></li>
                    <li><a href="#muc-2" class="mayhen_legal_toc_link">2. Bảng Giá Thiệp Online</a></li>
                    <li><a href="#muc-3" class="mayhen_legal_toc_link">3. Giá Dịch Vụ In Thiệp Giấy</a></li>
                    <li><a href="#muc-4" class="mayhen_legal_toc_link">4. Thuế, Hóa Đơn &amp; Phí Phát Sinh</a></li>
                    <li><a href="#muc-5" class="mayhen_legal_toc_link">5. Điều Chỉnh Giá &amp; Ưu Đãi</a></li>
                </ul>

                @include('client.components.legal-switcher')

                <button type="button" id="mayhen_legal_print_btn" class="mayhen_legal_print_btn">
                    In / Lưu PDF Văn Bản Này
                </button>
            </aside>

            <main class="mayhen_legal_article">
                <section id="muc-1" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">1</span>
                        <span>Nguyên Tắc Niêm Yết Giá Công Khai</span>
                    </h2>
                    <p>
                        Toàn bộ giá sản phẩm và gói dịch vụ trên <strong>{{ $brandName }} (mayhen.vn)</strong> đều được niêm yết công khai, rõ ràng bằng đồng <strong>Việt Nam Đồng (VNĐ)</strong> tại trang <a href="{{ route('pricing') }}">Bảng Giá (mayhen.vn/bang-gia)</a> và trang <a href="{{ route('services') }}">Dịch Vụ In Thiệp (mayhen.vn/dich-vu)</a>. Khách hàng luôn nhìn thấy tổng số tiền cuối cùng cần thanh toán trước khi xác nhận đặt hàng.
                    </p>
                </section>

                <section id="muc-2" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">2</span>
                        <span>Cơ Cấu Giá 4 Gói Thiệp Cưới &amp; Thiệp Sự Kiện Online</span>
                    </h2>
                    <ul>
                        <li><strong>Gói Trải Nghiệm (Free): 0 VNĐ</strong> — Cho phép tạo tối đa 01 thiệp cơ bản không cần đăng nhập; đường link thiệp tồn tại 03 tháng (90 ngày) và tự động dọn dẹp khi hết hạn.</li>
                        <li><strong>Gói Thành Viên (Premium): 0 VNĐ</strong> — Tự động kích hoạt mặc định cho mọi tài khoản đăng ký/đăng nhập, tạo tối đa 03 thiệp (tính cả thiệp đã xóa), mỗi link thiệp tồn tại 03 tháng (90 ngày).</li>
                        <li><strong>Gói Hạnh Phúc (Plus): 99.000 VNĐ / 06 tháng</strong> — Đăng ký và gia hạn theo chu kỳ 06 tháng (180 ngày), tạo tối đa 10 thiệp (tính cả thiệp đã xóa); mỗi thiệp tạo trong gói luôn tồn tại trọn vẹn đủ 06 tháng kể từ ngày tạo, mở khóa kho mẫu Plus, Tạo Thiệp Tự Động AI, nhạc nền cưới và quản lý RSVP.</li>
                        <li><strong>Gói Trọn Vẹn (Wedding Pro VIP): 199.000 VNĐ / 01 năm</strong> — Đăng ký và gia hạn theo chu kỳ 01 năm (365 ngày), tạo tối đa 30 thiệp (tính cả thiệp đã xóa); mỗi thiệp tạo trong gói luôn tồn tại trọn vẹn đủ 01 năm kể từ ngày tạo, mở khóa toàn bộ mẫu Hoàng Gia Pro, tách link Nhà Trai &amp; Nhà Gái và xuất bản trọn bộ 138 mẫu Thiệp Sự Kiện &amp; Mùa Lễ Hội.</li>
                    </ul>
                    <div class="mayhen_legal_callout">
                        <div class="mayhen_legal_callout_title">Cam kết 0% phí tiền mừng cưới VietQR:</div>
                        {{ $brandName }} không thu bất kỳ khoản phí chiết khấu, phí duy trì server định kỳ hay phí trung gian nào đối với tiền mừng cưới / lì xì gửi qua mã VietQR trên thiệp. 100% số tiền chuyển thẳng vào tài khoản ngân hàng của khách hàng.
                    </div>
                </section>

                <section id="muc-3" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">3</span>
                        <span>Chính Sách Giá Dịch Vụ In Thiệp Cưới Giấy Cao Cấp</span>
                    </h2>
                    <p>
                        Đối với dịch vụ in thiệp giấy vật lý (Minimalist, Ép Kim Foil Stamping, Phong Thư Tem Sáp Wax Seal), đơn giá phụ thuộc vào 04 yếu tố minh bạch: <em>định lượng phôi giấy mỹ thuật, quy cách gia công (ép kim, dập nổi, tem sáp), số lượng bộ thiệp (nhận in từ 50 bộ) và yêu cầu in tên khách mời trực tiếp lên phong bì</em>.
                    </p>
                    <p>
                        {{ $brandName }} luôn gửi <strong>bảng báo giá chi tiết trọn gói</strong> (đã bao gồm thiệp chính, phong bì nắp nhọn, công thiết kế maket và in ấn) để khách hàng duyệt xác nhận trước khi đặt cọc sản xuất. Phí vận chuyển thực tế (nếu phát sinh ngoài khu vực miễn phí giao hàng) được thông báo rõ trước khi chốt đơn.
                    </p>
                </section>

                <section id="muc-4" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">4</span>
                        <span>Quy Định Về Thuế, Hóa Đơn &amp; Chi Phí Phát Sinh</span>
                    </h2>
                    <ul>
                        <li>Giá niêm yết của các gói Thiệp Online đã là mức giá trọn gói cuối cùng dành cho khách hàng cá nhân, không phát sinh phụ phí duy trì hàng tháng.</li>
                        <li>Khách hàng doanh nghiệp hoặc tổ chức có nhu cầu xuất hóa đơn/chứng từ thanh toán vui lòng cung cấp đầy đủ thông tin đơn vị (tên, mã số thuế, địa chỉ, email nhận hóa đơn) ngay tại thời điểm đặt hàng để bộ phận kế toán xử lý theo quy định pháp luật thuế hiện hành.</li>
                        <li>Mọi chi phí phát sinh ngoài phạm vi gói (ví dụ: đặt in thêm số lượng thiệp giấy ngoài đơn ban đầu, yêu cầu vận chuyển hỏa tốc) đều phải có sự đồng ý trước của khách hàng.</li>
                    </ul>
                </section>

                <section id="muc-5" class="mayhen_legal_section">
                    <h2 class="mayhen_legal_section_title">
                        <span class="mayhen_legal_section_num">5</span>
                        <span>Chính Sách Điều Chỉnh Giá &amp; Mã Ưu Đãi</span>
                    </h2>
                    <p>
                        {{ $brandName }} có quyền cập nhật bảng giá dịch vụ theo từng giai đoạn vận hành và sẽ thông báo công khai trên website trước khi áp dụng. Mọi đơn hàng đã thanh toán thành công trước thời điểm điều chỉnh giá đều được giữ nguyên mức giá và đầy đủ quyền lợi đã cam kết.
                    </p>
                </section>
            </main>
        </div>
    </div>
</div>
@endsection
