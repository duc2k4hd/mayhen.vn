@extends('client.layouts.master')

@section('title', 'Chính Sách Cookie, Thu Thập Dữ Liệu & Lưu Trữ 6 Tháng')
@section('meta_description', 'Thông tin minh bạch về cách Mây Hẹn sử dụng Cookie, ghi nhận dữ liệu sử dụng công cụ, chính sách lưu trữ link 6 tháng và quyền bảo vệ dữ liệu cá nhân theo Luật 91/2025/QH15.')
@section('canonical_url', route('policy.cookies'))

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/wedding-journey.css') }}">
@endpush

@section('content')
<div class="mayhen_wedding_planner_page">
    <div class="mayhen_wedding_planner_container">
        <nav class="mayhen_wedding_seo_breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a>
            <span>/</span>
            <span>Chính Sách Cookie &amp; Thu Thập Dữ Liệu</span>
        </nav>

        <section class="mayhen_wedding_planner_hero">
            <span class="mayhen_wedding_planner_eyebrow">Tuân Thủ Luật Bảo Vệ Dữ Liệu Cá Nhân 91/2025/QH15 · Phiên bản {{ $policyVersion }}</span>
            <h1 class="mayhen_wedding_planner_h1">Chính Sách Cookie, Thu Thập Dữ Liệu &amp; Lưu Trữ 6 Tháng</h1>
            <p class="mayhen_wedding_planner_lead">Mây Hẹn cam kết minh bạch tuyệt đối về loại Cookie được đặt trên trình duyệt, dữ liệu hành vi sử dụng các công cụ trực tuyến, vòng đời lưu trữ 6 tháng đối với các đường dẫn người dùng tạo ra và quyền kiểm soát dữ liệu của bạn.</p>
            <div class="mayhen_wedding_planner_subnav">
                <button type="button" class="mayhen_wedding_planner_pill mayhen_wedding_planner_pill--crimson is-active" data-mayhen-open-cookie-settings>Mở Lại Cài Đặt Cookie Của Tôi</button>
                <a href="{{ route('privacy') }}" class="mayhen_wedding_planner_pill">Chính Sách Bảo Mật</a>
                <a href="{{ route('terms') }}" class="mayhen_wedding_planner_pill">Điều Khoản Dịch Vụ</a>
            </div>
        </section>

        <article class="mayhen_wedding_planner_seo" style="margin-top:0;">
            <h2>1. Các Nhóm Cookie &amp; Dữ Liệu Mây Hẹn Sử Dụng</h2>
            <div class="mayhen_wedding_date_compare_table_wrap">
                <table class="mayhen_wedding_date_compare_table">
                    <thead>
                        <tr><th>Nhóm Cookie</th><th>Bắt buộc?</th><th>Mục đích sử dụng cụ thể</th><th>Thời hạn</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Cookie Thiết Yếu (Essential)</strong></td>
                            <td>Bắt buộc</td>
                            <td>Duy trì phiên đăng nhập, bảo mật chống giả mạo CSRF, lưu lựa chọn chấp thuận Cookie của bạn.</td>
                            <td>Phiên / 6 tháng</td>
                        </tr>
                        <tr>
                            <td><strong>Cookie Phân Tích &amp; Hiệu Năng (Analytics)</strong></td>
                            <td>Tùy chọn</td>
                            <td>Đo lường lượt dùng 9 công cụ (Xem Ngày Cưới, Bát Tự, Tử Vi, Tarot, Cao Ly, Gia Phả...) để cải thiện tốc độ và chất lượng tính năng.</td>
                            <td>6 tháng</td>
                        </tr>
                        <tr>
                            <td><strong>Cookie Cá Nhân Hóa &amp; AI (Personalization)</strong></td>
                            <td>Tùy chọn</td>
                            <td>Ghi nhớ tùy chọn vùng miền cưới hỏi, bộ lọc gần nhất và hỗ trợ trải nghiệm AI Concierge phù hợp hơn.</td>
                            <td>6 tháng</td>
                        </tr>
                        <tr>
                            <td><strong>Cookie Tiếp Thị (Marketing)</strong></td>
                            <td>Tùy chọn</td>
                            <td>Hiển thị ưu đãi gói dịch vụ phù hợp; Mây Hẹn KHÔNG bán dữ liệu cá nhân cho bên thứ ba.</td>
                            <td>6 tháng</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h2>2. Chính Sách Lưu Trữ 6 Tháng Đối Với Link &amp; Dữ Liệu Công Cụ Người Dùng Tạo</h2>
            <p>Đối với các hồ sơ, lá số, bài luận và đường dẫn chia sẻ được tạo ra từ hệ sinh thái công cụ trực tuyến (Gia Phả, Gieo Quẻ, Tử Vi, Thần Số Học, Tarot, Lịch Gia Đình, Bát Tự, Xem Tuổi Vợ Chồng, Kế Hoạch Tổ Chức Đám Cưới):</p>
            <ul>
                <li><strong>Thời hạn hoạt động của link &amp; dữ liệu chi tiết:</strong> Được lưu trữ và truy cập đầy đủ trong vòng <strong>06 tháng (180 ngày)</strong> kể từ thời điểm khởi tạo.</li>
                <li><strong>Cơ chế dọn dẹp tự động (Cron Job định kỳ hằng ngày):</strong> Sau mốc 6 tháng, hệ thống tự động xóa bỏ dữ liệu chi tiết và vô hiệu hóa đường dẫn chia sẻ cũ nhằm bảo vệ quyền riêng tư và tối ưu hạ tầng.</li>
                <li><strong>Thông tin thống kê được giữ lại cho mục đích phân tích:</strong> Trước khi xóa dữ liệu chi tiết, hệ thống chỉ trích xuất và giữ lại các trường siêu dữ liệu thống kê tổng hợp (loại công cụ, tháng tạo, gói cước, chỉ số phân tích ẩn danh) để phục vụ nghiên cứu cải tiến chất lượng sản phẩm.</li>
            </ul>

            <h2>3. Quyền Của Chủ Thể Dữ Liệu Theo Luật 91/2025/QH15</h2>
            <p>Bạn có toàn quyền xem lại, chỉnh sửa, thay đổi lựa chọn Cookie bất kỳ lúc nào bằng nút <strong>Mở Lại Cài Đặt Cookie Của Tôi</strong> ở đầu trang, hoặc yêu cầu xóa sớm hồ sơ cá nhân trong tài khoản. Xem thêm chi tiết tại <a href="{{ route('privacy') }}">Chính Sách Bảo Mật</a> và <a href="{{ route('terms') }}">Điều Khoản Dịch Vụ</a>.</p>
        </article>
    </div>
</div>
@endsection
