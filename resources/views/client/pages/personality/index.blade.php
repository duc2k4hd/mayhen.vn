@extends('client.layouts.master')

@section('title', 'Trắc Nghiệm MBTI Online, Test Tính Cách Miễn Phí — Bản Sắc Tâm Hồn')
@section('meta_description', 'Trắc nghiệm 16 nhóm tính cách & Big Five OCEAN miễn phí theo phương pháp chấm điểm xác định, minh bạch vùng ranh giới, Best-Fit Exploration, 16 hình tượng nghệ thuật và đối chiếu cặp đôi.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/personality.css') }}">
@endpush

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/personality.js') }}"></script>
@endpush

@section('content')
<main class="mayhen_personality_page">
    <section class="mayhen_personality_hero">
        <div class="mayhen_personality_container">
            <div class="mayhen_personality_hero_grid">
                <div>
                    <span class="mayhen_personality_kicker">Mây Hẹn • The Art of Personality — Bản Sắc Tâm Hồn</span>
                    <h1 class="mayhen_personality_h1">Trắc Nghiệm MBTI Online, Test Tính Cách Miễn Phí</h1>
                    <p class="mayhen_personality_lead">
                        Hiểu mình sâu hơn, hiểu nhau nhiều hơn. Khám phá 16 nhóm tính cách qua bộ câu hỏi tiếng Việt độc lập, thuật toán chấm điểm xác định 100%, nút <strong>Xem căn cứ</strong> từ câu trả lời thật và trải nghiệm nghệ thuật Fine Art của Mây Hẹn.
                    </p>
                    <div class="mayhen_personality_actions">
                        <a href="{{ route('personality.start', ['method' => 'mayhen_16types_standard_v1']) }}" class="mayhen_personality_btn mayhen_personality_btn--primary">
                            Bắt đầu bài test 64 câu miễn phí
                        </a>
                        <a href="{{ route('personality.start', ['method' => 'mayhen_16types_quick_v1']) }}" class="mayhen_personality_btn mayhen_personality_btn--outline">
                            Bản nhanh 32 câu (5–7 phút)
                        </a>
                        <a href="{{ route('personality.big_five') }}" class="mayhen_personality_btn mayhen_personality_btn--outline">
                            Trắc nghiệm Big Five (OCEAN)
                        </a>
                    </div>
                    <div style="margin-top: 18px;">
                        <span class="mayhen_personality_badge_pilot">
                            Phiên bản Thử nghiệm Minh bạch (Pilot Beta v1.0) • Miễn phí không cần đăng nhập
                        </span>
                    </div>
                </div>
                <div class="mayhen_personality_sculpture_stage">
                    <div class="mayhen_personality_sculpture_card" data-mode="3d" style="max-width: 300px; width: 100%;">
                        {!! $artworks['INFJ'] ?? '' !!}
                        <div style="margin-top: 12px;">
                            <div class="mayhen_personality_type_code">16 PORTRAITS</div>
                            <p style="margin: 4px 0 0; font-size: 0.85rem; color: var(--mayhen-personality-muted);">
                                Bộ sưu tập hình tượng nghệ thuật độc quyền Mây Hẹn — Không sao chép nhân vật bên thứ ba
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <span class="mayhen_personality_kicker">3 Phiên Bản 16 Nhóm & Big Five Độc Lập</span>
            <h2 class="mayhen_personality_h2">Chọn độ sâu bài trắc nghiệm phù hợp với bạn</h2>
            <p class="mayhen_personality_sub">
                Không yêu cầu ngày giờ sinh. Mọi câu trả lời tự động lưu an toàn khi làm bài và chấm điểm bằng thuật toán xác định (AI tuyệt đối không tự chọn mã nhóm thay bạn).
            </p>
            <div class="mayhen_personality_grid_4">
                @foreach($methods as $methodId => $m)
                    <div class="mayhen_personality_card" style="display:flex; flex-direction:column; justify-content:space-between; gap:14px;">
                        <div>
                            <div style="font-size:0.78rem; font-weight:600; color:var(--mayhen-personality-jade); text-transform:uppercase;">
                                {{ $m['short_name'] }} • {{ $m['estimated_minutes'] }}
                            </div>
                            <h3 class="mayhen_personality_h3" style="margin-top:6px;">{{ $m['name'] }}</h3>
                            <p style="font-size:0.88rem; color:var(--mayhen-personality-muted); line-height:1.6; margin:0;">
                                {{ $m['theoretical_model'] }}
                            </p>
                        </div>
                        <form method="POST" action="{{ route('personality.session.create') }}">
                            @csrf
                            <input type="hidden" name="method_id" value="{{ $methodId }}">
                            <button type="submit" class="mayhen_personality_btn mayhen_personality_btn--primary mayhen_personality_btn--sm" style="width:100%;">
                                Bắt đầu ngay ({{ $m['question_count'] }} câu)
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <span class="mayhen_personality_kicker">Bốn Cặp Xu Hướng Trung Lập</span>
            <h2 class="mayhen_personality_h2">Không có chiều nào tốt hơn chiều còn lại</h2>
            <p class="mayhen_personality_sub">
                Mây Hẹn diễn giải cả 4 cặp xu hướng theo tinh thần trung lập, tách biệt hoàn toàn với biến thể -A/-T và tôn trọng vùng ranh giới (45–55) khi bạn linh hoạt giữa hai phía.
            </p>
            <div class="mayhen_personality_grid_2">
                @foreach($dimensions as $dim)
                    <div class="mayhen_personality_card">
                        <div class="mayhen_personality_type_code">{{ $dim['positive_code'] }} / {{ $dim['negative_code'] }}</div>
                        <h3 class="mayhen_personality_h3" style="margin-top:4px;">{{ $dim['name'] }}</h3>
                        <p style="font-size:0.92rem; line-height:1.65; color:var(--mayhen-personality-ink); margin:8px 0;">
                            {{ $dim['neutral_summary'] }}
                        </p>
                        <p style="font-size:0.84rem; color:var(--mayhen-personality-muted); margin:0;">
                            <strong>Lưu ý:</strong> {{ $dim['clarification'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <div style="display:flex; justify-content:space-between; align-items:flex-end; gap:16px; flex-wrap:wrap; margin-bottom:24px;">
                <div>
                    <span class="mayhen_personality_kicker">The Art of Personality — 16 Portraits</span>
                    <h2 class="mayhen_personality_h2" style="margin-bottom:4px;">Bộ sưu tập 16 hình tượng nghệ thuật</h2>
                </div>
                <a href="{{ route('personality.gallery') }}" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">
                    Mở Triển lãm 3D / 2D đầy đủ
                </a>
            </div>
            <div class="mayhen_personality_grid_4">
                @foreach($types as $code => $t)
                    <a href="{{ route('personality.type', ['typeCode' => strtolower($code)]) }}" class="mayhen_personality_type_tile">
                        <div style="margin-bottom:10px; text-align:center;">{!! $artworks[$code] ?? '' !!}</div>
                        <div class="mayhen_personality_type_code">{{ $code }}</div>
                        <div style="font-weight:700; color:var(--mayhen-personality-crimson); font-size:0.95rem;">{{ $t['art_title'] }}</div>
                        <div style="font-size:0.84rem; color:var(--mayhen-personality-muted); margin-top:2px;">{{ $t['vietnamese_name'] }}</div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <div class="mayhen_personality_grid_2">
                <div class="mayhen_personality_card">
                    <span class="mayhen_personality_kicker">Tính năng chủ lực</span>
                    <h2 class="mayhen_personality_h2">Mây Hẹn Personality for Couples</h2>
                    <p style="line-height:1.65; color:var(--mayhen-personality-muted);">
                        Hai người làm bài độc lập, mời nhau kết nối bằng mã riêng tư, đối chiếu 4 chiều xu hướng và 6 câu hỏi thấu hiểu thực tế. Tuyệt đối <strong>không tạo tỷ lệ hợp nhau 98% giả</strong> — chỉ tập trung vào cách lắng nghe, giao tiếp và chuẩn bị hôn nhân bền vững.
                    </p>
                    <div class="mayhen_personality_actions" style="margin-top:16px;">
                        <a href="{{ route('personality.love') }}" class="mayhen_personality_btn mayhen_personality_btn--crimson mayhen_personality_btn--sm">
                            Khám phá Tính cách Cặp đôi
                        </a>
                        <a href="{{ route('personality.cognitive_functions') }}" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">
                            8 Chức năng Nhận thức
                        </a>
                    </div>
                </div>
                <div class="mayhen_personality_card">
                    <span class="mayhen_personality_kicker">Minh bạch học thuật & Bản quyền</span>
                    <h2 class="mayhen_personality_h2">Phương pháp & Giới hạn</h2>
                    <p style="line-height:1.65; color:var(--mayhen-personality-muted);">
                        Đây là công cụ trắc nghiệm 16 nhóm tính cách độc lập do Mây Hẹn phát triển tham chiếu khung Jung–Myers, <strong>không phải bài MBTI® chính thức có cấp phép</strong>. Kết quả không dùng để chẩn đoán sức khỏe tâm thần hay quyết định tuyển dụng.
                    </p>
                    <div class="mayhen_personality_actions" style="margin-top:16px;">
                        <a href="{{ route('personality.methodology') }}" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">
                            Đọc Báo cáo Phương pháp
                        </a>
                        <a href="{{ route('personality.ei_guide') }}" class="mayhen_personality_btn mayhen_personality_btn--outline mayhen_personality_btn--sm">
                            Hiểu đúng Hướng nội / Hướng ngoại
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <div class="mayhen_personality_notice">
                <strong>Liên kết hệ sinh thái Mây Hẹn:</strong> Bạn có thể khám phá thêm
                <a href="{{ url('/to-chuc-dam-cuoi') }}">Tổ Chức Đám Cưới</a>,
                <a href="{{ url('/mau-thiep') }}">Kho Thiệp Cưới Online</a>,
                <a href="{{ url('/than-so-hoc/tinh-duyen') }}">Thần Số Học Tình Duyên</a> và
                <a href="{{ url('/tarot') }}">Xem Tarot Online</a>.
                <em>Lưu ý rõ ràng: các công cụ văn hóa – huyền học và trắc nghiệm tâm lý sử dụng phương pháp hoàn toàn khác nhau, Mây Hẹn tuyệt đối không trộn chung thành một chỉ số tổng hợp.</em>
            </div>
        </div>
    </section>
</main>
@endsection
