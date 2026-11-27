@extends('client.layouts.master')

@section('title', 'Phương Pháp Trắc Nghiệm, Công Thức Chấm Điểm & Minh Bạch Khoa Học')
@section('meta_description', 'Công bố minh bạch công thức chấm điểm xác định, nguồn nghiên cứu gốc (Jung, Myers, McCrae & Costa, IPIP, ITC), trạng thái kiểm định Pilot và rà soát bản quyền nhãn hiệu.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/personality.css') }}">
@endpush

@section('content')
<main class="mayhen_personality_page">
    <section class="mayhen_personality_hero">
        <div class="mayhen_personality_narrow">
            <span class="mayhen_personality_kicker">Báo cáo minh bạch • Psychometrics & Ethics</span>
            <h1 class="mayhen_personality_h1">Phương Pháp Trắc Nghiệm Tính Cách Của Mây Hẹn</h1>
            <p class="mayhen_personality_lead">
                Mây Hẹn công khai toàn bộ công thức chấm điểm, nguồn tài liệu tham chiếu, những phần đã kiểm thử phần mềm và những phần đang trong lộ trình kiểm định tâm lý học thực nghiệm.
            </p>
        </div>
    </section>

    <section class="mayhen_personality_section">
        <div class="mayhen_personality_narrow">
            <div class="mayhen_personality_card" style="margin-bottom:20px;">
                <h2 class="mayhen_personality_h3">1. Công thức chấm điểm xác định (Không dùng AI chấm điểm)</h2>
                <p style="line-height:1.7;">
                    Với thang Likert 1–7 cho 16 nhóm tính cách:<br>
                    • Câu thuận: <code>điểm hiệu chỉnh = điểm trả lời (1..7)</code><br>
                    • Câu đảo chiều: <code>điểm hiệu chỉnh = 8 - điểm trả lời</code><br>
                    • Trung bình chiều: <code>Mean = Tổng điểm hiệu chỉnh / Số câu hợp lệ</code> (câu "Không chắc" không bị ép thành mức 4 trung lập)<br>
                    • Chỉ số nội bộ: <code>Positive Index = ((Mean - 1) / 6) × 100</code> và <code>Opposite Index = 100 - Positive Index</code><br>
                    • Quy tắc phân loại: tính từ số thực chưa làm tròn (<code>&gt; 50</code> chiều dương, <code>&lt; 50</code> chiều âm, <code>= 50</code> đánh dấu cân bằng chưa nghiêng rõ và trả về tất cả nhóm khả dĩ). Vùng <code>45–55</code> là vùng ranh giới UX gợi ý đối chiếu Best-Fit.
                </p>
            </div>

            <div class="mayhen_personality_card" style="margin-bottom:20px;">
                <h2 class="mayhen_personality_h3">2. Bản quyền, Nhãn hiệu & Các giới hạn</h2>
                <ul style="line-height:1.7; margin:8px 0 0 18px;">
                    <li>MBTI® là nhãn hiệu đã đăng ký của The Myers-Briggs Company. Bộ câu hỏi 32 / 64 / 96 câu của Mây Hẹn được biên soạn độc lập bằng tiếng Việt, không sao chép MBTI® chính thức hay 16Personalities.</li>
                    <li>Không gắn hậu tố <code>-A</code> / <code>-T</code> của hệ NERIS vào kết quả 4 cặp xu hướng.</li>
                    <li>Không tạo Percentile dân số khi chưa có tập dữ liệu chuẩn hóa Việt Nam độc lập.</li>
                    <li>Không sử dụng để chẩn đoán sức khỏe tâm thần, không dùng để tuyển dụng hay loại ứng viên, không tạo tỷ lệ hợp nhau cặp đôi giả.</li>
                </ul>
            </div>

            <div class="mayhen_personality_card">
                <h2 class="mayhen_personality_h3">3. Nguồn nghiên cứu tham chiếu</h2>
                <ul style="line-height:1.7; margin:8px 0 0 18px;">
                    @foreach($sources as $src)
                        <li><strong>{{ $src['id'] }}</strong> — {{ $src['author'] }} ({{ $src['year'] }}), <em>{{ $src['title'] }}</em>: {{ $src['scope'] }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>
</main>
@endsection
