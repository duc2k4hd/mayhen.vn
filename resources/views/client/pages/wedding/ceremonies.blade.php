@extends('client.layouts.master')

@section('title', 'Nghi Lễ Cưới Hỏi 3 Miền Việt Nam & Thủ Tục Đăng Ký Kết Hôn Pháp Lý')
@section('meta_description', 'Cẩm nang nghi lễ cưới hỏi truyền thống Bắc – Trung – Nam (Dạm ngõ, Ăn hỏi, Lễ gia tiên, Đón dâu, Lại mặt) và hướng dẫn thủ tục đăng ký kết hôn chuẩn Luật Hôn nhân và Gia đình 2014 qua dichvucong.gov.vn.')
@section('canonical_url', route('wedding.ceremonies'))

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/wedding-journey.css') }}">
@endpush

@section('content')
<div class="mayhen_wedding_planner_page">
    <div class="mayhen_wedding_planner_container">
        <section class="mayhen_wedding_planner_hero">
            <span class="mayhen_wedding_planner_eyebrow">Thư Viện Văn Hóa &amp; Pháp Lý Hôn Nhân Việt Nam</span>
            <h1 class="mayhen_wedding_planner_h1">Nghi Lễ Cưới Hỏi 3 Miền &amp; Hướng Dẫn Đăng Ký Kết Hôn</h1>
            <p class="mayhen_wedding_planner_lead">Phân biệt rõ phong tục cưới hỏi Miền Bắc – Miền Trung – Miền Nam cùng hướng dẫn thủ tục đăng ký kết hôn chính thức tại Cổng Dịch vụ công Quốc gia (dichvucong.gov.vn).</p>
            <nav class="mayhen_wedding_planner_subnav">
                <a href="{{ route('wedding.dates.index') }}" class="mayhen_wedding_planner_pill">Xem Ngày Cưới</a>
                <a href="{{ route('wedding.planner.index') }}" class="mayhen_wedding_planner_pill">Tổ Chức Đám Cưới</a>
                <a href="{{ route('wedding.ceremonies') }}" class="mayhen_wedding_planner_pill is-active">Nghi Lễ &amp; Pháp Lý</a>
            </nav>
        </section>

        <div class="mayhen_wedding_planner_grid_3">
            <section class="mayhen_wedding_planner_card">
                <h2 class="mayhen_wedding_planner_card_title">Phong Tục Miền Bắc</h2>
                <p class="mayhen_wedding_planner_card_sub">Trang trọng, chuẩn mực lễ nghi truyền thống.</p>
                <ul style="padding-left:18px;font-size:14px;line-height:1.7;">
                    <li><strong>Số tráp ăn hỏi:</strong> Số lẻ (5, 7, 9 hoặc 11 tráp) nhưng lễ vật bên trong thường chia chẵn tượng trưng có đôi có cặp.</li>
                    <li><strong>Lễ vật tiêu biểu:</strong> Trầu cau, chè, hạt sen, bánh cốm, bánh phu thê, rượu thuốc, lợn sữa quay hoặc xôi gấc.</li>
                    <li><strong>Nghi thức lại quả:</strong> Nhà gái chia lại một phần lễ vật (thường ngửa nắp tráp) gửi nhà trai mang về.</li>
                </ul>
            </section>

            <section class="mayhen_wedding_planner_card">
                <h2 class="mayhen_wedding_planner_card_title">Phong Tục Miền Trung</h2>
                <p class="mayhen_wedding_planner_card_sub">Mộc mạc, chu đáo và giàu tình nghĩa gia tộc.</p>
                <ul style="padding-left:18px;font-size:14px;line-height:1.7;">
                    <li><strong>Số mâm lễ:</strong> Linh hoạt theo thỏa thuận hai nhà (thường 4, 6 hoặc 5, 7 mâm), không quá nặng hình thức.</li>
                    <li><strong>Lễ vật đặc trưng:</strong> Trầu cau, bánh phu thê (bánh xu xê), trà rượu và cặp nến tơ hồng thắp trên bàn thờ gia tiên.</li>
                    <li><strong>Lễ rước dâu:</strong> Chú trọng giờ xuất hành và sự có mặt đông đủ của họ hàng hai bên trong nghi lễ gia tiên.</li>
                </ul>
            </section>

            <section class="mayhen_wedding_planner_card">
                <h2 class="mayhen_wedding_planner_card_title">Phong Tục Miền Nam</h2>
                <p class="mayhen_wedding_planner_card_sub">Phóng khoáng, rộn ràng và đề cao sự sung túc.</p>
                <ul style="padding-left:18px;font-size:14px;line-height:1.7;">
                    <li><strong>Số mâm quả:</strong> Số chẵn (thường 6 hoặc 8 mâm quả), tượng trưng cho sự song hỷ viên mãn.</li>
                    <li><strong>Lễ vật đặc trưng:</strong> Cặp đèn cầy long phụng cỡ lớn, trà rượu, bánh kem/bánh pía, mâm ngũ quả, heo quay và khay trầu rượu dẫn đầu.</li>
                    <li><strong>Nghi thức lên đèn:</strong> Chú rể và cô dâu cùng thắp cặp đèn cầy long phụng trên bàn thờ gia tiên nhà gái.</li>
                </ul>
            </section>
        </div>

        <section class="mayhen_wedding_planner_card">
            <h2 class="mayhen_wedding_planner_card_title">Hướng Dẫn Đăng Ký Kết Hôn Hợp Pháp Tại Việt Nam</h2>
            <div class="mayhen_wedding_budget_alert mayhen_wedding_budget_alert--warn">
                <strong>Lưu ý pháp lý bắt buộc:</strong> Theo Điểm a Khoản 1 Điều 8 <strong>Luật Hôn nhân và Gia đình năm 2014</strong>, độ tuổi kết hôn hợp pháp là <strong>Nam từ đủ 20 tuổi trở lên, Nữ từ đủ 18 tuổi trở lên</strong> — tính chính xác theo <strong>ngày, tháng, năm sinh tròn Dương lịch</strong>. <strong>Tuyệt đối không sử dụng tuổi mụ</strong> để tính điều kiện kết hôn pháp lý.
            </div>
            <div class="mayhen_wedding_planner_grid_equal">
                <div>
                    <h3 style="margin-top:0;">1. Hồ sơ cần chuẩn bị</h3>
                    <ul style="padding-left:18px;font-size:14px;line-height:1.7;">
                        <li>Tờ khai đăng ký kết hôn theo mẫu (hoặc biểu mẫu điện tử khi nộp online).</li>
                        <li>CCCD / Hộ chiếu còn thời hạn của cả hai bên nam và nữ.</li>
                        <li>Giấy xác nhận tình trạng hôn nhân (nếu không thường trú tại nơi đăng ký và chưa đồng bộ VNeID).</li>
                        <li>Bản án / Quyết định ly hôn có hiệu lực pháp luật (nếu một trong hai bên đã từng kết hôn).</li>
                    </ul>
                </div>
                <div>
                    <h3 style="margin-top:0;">2. Quy trình nộp trực tuyến qua Cổng Dịch vụ công Quốc gia</h3>
                    <ol style="padding-left:18px;font-size:14px;line-height:1.7;">
                        <li>Truy cập <strong>https://dichvucong.gov.vn</strong> và đăng nhập bằng tài khoản định danh điện tử <strong>VNeID</strong>.</li>
                        <li>Tìm thủ tục <em>“Đăng ký kết hôn”</em> và chọn UBND cấp xã/phường nơi cư trú của một trong hai bên.</li>
                        <li>Kê khai tờ khai điện tử, đính kèm ảnh chụp giấy tờ và nhận lịch hẹn.</li>
                        <li>Đến ngày hẹn, <strong>cả hai bên nam và nữ cùng có mặt trực tiếp tại UBND</strong> để ký Sổ đăng ký kết hôn và nhận Giấy chứng nhận kết hôn.</li>
                    </ol>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
