@extends('client.layouts.master')

@section('title', 'MBTI Tình Duyên & Tính Cách Cặp Đôi — Personality for Couples')
@section('meta_description', 'Hai người làm bài trắc nghiệm tính cách độc lập, kết nối mã mời riêng tư để so sánh 4 chiều giao tiếp và 6 câu hỏi thấu hiểu cặp đôi — không tạo tỷ lệ hợp nhau giả.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/personality.css') }}">
@endpush

@section('content')
<main class="mayhen_personality_page">
    <section class="mayhen_personality_hero">
        <div class="mayhen_personality_container">
            <span class="mayhen_personality_kicker">Mây Hẹn • Personality for Couples</span>
            <h1 class="mayhen_personality_h1">Thấu Hiểu Nhau Qua Tính Cách Cặp Đôi</h1>
            <p class="mayhen_personality_lead">
                Mỗi người tự làm bài độc lập, sau đó kết nối bằng mã mời riêng tư. Mây Hẹn tuyệt đối <strong>không tạo tỷ lệ hợp nhau 98% giả</strong> — thay vào đó giúp hai bạn nhìn rõ điểm tương đồng, khác biệt giao tiếp và 12 chủ đề tình yêu để đồng hành bền vững.
            </p>
        </div>
    </section>

    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            @if($mySessions->isEmpty())
                <div class="mayhen_personality_card" style="text-align:center; padding:36px;">
                    <h2 class="mayhen_personality_h2">Bước 1: Hoàn thành bài trắc nghiệm cá nhân của bạn</h2>
                    <p style="color:var(--mayhen-personality-muted); max-width:560px; margin:0 auto 20px;">
                        Để bảo đảm tính khách quan, mỗi người cần tự làm bài độc lập thay vì làm thay người còn lại.
                    </p>
                    <a href="{{ route('personality.start', ['method' => 'mayhen_16types_quick_v1']) }}" class="mayhen_personality_btn mayhen_personality_btn--primary">
                        Làm bài 32 câu ngay (5–7 phút)
                    </a>
                </div>
            @else
                <div class="mayhen_personality_grid_2">
                    <form method="POST" action="{{ route('personality.couple.create') }}" class="mayhen_personality_card">
                        @csrf
                        <h2 class="mayhen_personality_h3">1. Tạo mã mời gửi người thương</h2>
                        <div style="display:grid; gap:12px; margin-top:12px;">
                            <input type="text" name="partner_a_name" class="mayhen_personality_input" placeholder="Tên hiển thị của bạn (vd: Minh)">
                            <select name="session_a_id" class="mayhen_personality_select" required>
                                @foreach($mySessions as $s)
                                    <option value="{{ $s->id }}">{{ $s->effectiveType() }} — {{ $s->submitted_at?->format('d/m/Y H:i') }}</option>
                                @endforeach
                            </select>
                            @foreach($reflectionQuestions as $q)
                                <div>
                                    <label style="display:block; font-size:0.84rem; font-weight:600; margin-bottom:4px;">{{ $q['label'] }}</label>
                                    <select name="answers[{{ $q['id'] }}]" class="mayhen_personality_select">
                                        <option value="">-- Không chia sẻ câu này --</option>
                                        @foreach($q['options'] as $opt)
                                            <option value="{{ $opt }}">{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                            <button type="submit" class="mayhen_personality_btn mayhen_personality_btn--crimson">
                                Tạo mã mời kết nối cặp đôi
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('personality.couple.join') }}" class="mayhen_personality_card">
                        @csrf
                        <h2 class="mayhen_personality_h3">2. Nhập mã mời từ người thương</h2>
                        <div style="display:grid; gap:12px; margin-top:12px;">
                            <input type="text" name="invite_code" class="mayhen_personality_input" placeholder="Nhập mã mời 8 ký tự (vd: AB12CD34)" required>
                            <input type="text" name="partner_b_name" class="mayhen_personality_input" placeholder="Tên hiển thị của bạn (vd: Lan)">
                            <select name="session_b_id" class="mayhen_personality_select" required>
                                @foreach($mySessions as $s)
                                    <option value="{{ $s->id }}">{{ $s->effectiveType() }} — {{ $s->submitted_at?->format('d/m/Y H:i') }}</option>
                                @endforeach
                            </select>
                            @foreach($reflectionQuestions as $q)
                                <div>
                                    <label style="display:block; font-size:0.84rem; font-weight:600; margin-bottom:4px;">{{ $q['label'] }}</label>
                                    <select name="answers[{{ $q['id'] }}]" class="mayhen_personality_select">
                                        <option value="">-- Không chia sẻ câu này --</option>
                                        @foreach($q['options'] as $opt)
                                            <option value="{{ $opt }}">{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endforeach
                            <button type="submit" class="mayhen_personality_btn mayhen_personality_btn--primary">
                                Kết nối & Xem đối chiếu hai hồ sơ
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </section>
</main>
@endsection
