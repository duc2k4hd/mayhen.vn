@extends('client.layouts.master')

@section('title', 'Cố Vấn Khảo Sát & Đề Xuất Gói Cưới Phù Hợp | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/plan-advisor.css') }}">
@endpush

@section('content')
<div class="mayhen_advisor_wrap" id="mayhen_advisor_app" data-analyze-url="{{ route('pricing.advisor.analyze') }}">
    <div class="mayhen_advisor_container">
        {{-- Header --}}
        <header class="mayhen_advisor_header">
            <span class="advisor_badge">PLAN FIT ADVISOR</span>
            <h1 class="advisor_title">Cố Vấn Đề Xuất Gói Cưới Phù Hợp</h1>
            <p class="advisor_subtitle">
                Trả lời nhanh 4 câu hỏi về quy mô hôn lễ của hai bạn. Thuật toán của Mây Hẹn sẽ đề xuất gói dịch vụ tối ưu, minh bạch và tiết kiệm nhất &mdash; không upsell lãng phí.
            </p>
        </header>

        {{-- Main Two-Column Workspace --}}
        <div class="mayhen_advisor_grid">
            {{-- Left Column: 4 Questionnaire Groups --}}
            <div class="advisor_quiz_card">
                {{-- Q1: Quy mô khách mời --}}
                <div class="quiz_group">
                    <label class="quiz_label">1. Quy mô khách mời dự kiến của hai bạn?</label>
                    <span class="quiz_hint">Ước tính tổng số khách mời sẽ nhận thiệp cưới</span>
                    <div class="advisor_options_grid">
                        <button type="button" class="advisor_opt_btn" data-group="guest_scale" data-val="small">
                            <span class="advisor_opt_title">Dưới 100 khách</span>
                            <span class="advisor_opt_desc">Tiệc cưới ấm cúng, người thân &amp; bạn bè thân thiết</span>
                        </button>
                        <button type="button" class="advisor_opt_btn is-active" data-group="guest_scale" data-val="medium">
                            <span class="advisor_opt_title">100 - 300 khách</span>
                            <span class="advisor_opt_desc">Quy mô tiêu chuẩn phổ biến của đám cưới Việt Nam</span>
                        </button>
                        <button type="button" class="advisor_opt_btn" data-group="guest_scale" data-val="large">
                            <span class="advisor_opt_title">Trên 300 khách</span>
                            <span class="advisor_opt_desc">Hôn lễ quy mô lớn, đông đủ họ hàng hai bên</span>
                        </button>
                    </div>
                </div>

                {{-- Q2: Số lượng sự kiện & sảnh tiệc --}}
                <div class="quiz_group">
                    <label class="quiz_label">2. Số lượng tiệc cưới và lễ nghi cần làm thiệp?</label>
                    <span class="quiz_hint">Cần thiệp chung hay thiệp riêng cho từng bên gia đình?</span>
                    <div class="advisor_options_grid">
                        <button type="button" class="advisor_opt_btn" data-group="events_count" data-val="single">
                            <span class="advisor_opt_title">1 Tiệc Chung</span>
                            <span class="advisor_opt_desc">Chung 1 ngày giờ &amp; sảnh tiệc cho cô dâu chú rể</span>
                        </button>
                        <button type="button" class="advisor_opt_btn is-active" data-group="events_count" data-val="dual">
                            <span class="advisor_opt_title">2 Tiệc Riêng Biệt</span>
                            <span class="advisor_opt_desc">Tiệc Nhà Trai &amp; Tiệc Nhà Gái khác ngày/giờ</span>
                        </button>
                        <button type="button" class="advisor_opt_btn" data-group="events_count" data-val="multi">
                            <span class="advisor_opt_title">3 Tiệc Trở Lên</span>
                            <span class="advisor_opt_desc">Lễ Gia Tiên + Tiệc Quê + Tiệc Báo Hỷ Thành Phố</span>
                        </button>
                    </div>
                </div>

                {{-- Q3: Nhu cầu vận hành ngày cưới --}}
                <div class="quiz_group">
                    <label class="quiz_label">3. Hai bạn có cần công nghệ đón tiếp ngày cưới?</label>
                    <span class="quiz_hint">Sơ đồ xếp bàn, quét QR check-in cổng tiệc &amp; bàn giao ca</span>
                    <div class="advisor_options_grid">
                        <button type="button" class="advisor_opt_btn" data-group="operations_need" data-val="basic">
                            <span class="advisor_opt_title">Cơ Bản</span>
                            <span class="advisor_opt_desc">Chỉ cần gửi thiệp online đẹp và nhận phản hồi RSVP</span>
                        </button>
                        <button type="button" class="advisor_opt_btn is-active" data-group="operations_need" data-val="advanced">
                            <span class="advisor_opt_title">Nâng Cao (Khuyên Dùng)</span>
                            <span class="advisor_opt_desc">Sơ đồ sảnh tiệc, QR Check-in và bàn lễ tân thông minh</span>
                        </button>
                    </div>
                </div>

                {{-- Q4: Nhu cầu lưu giữ kỷ niệm sau cưới --}}
                <div class="quiz_group">
                    <label class="quiz_label">4. Thời gian lưu giữ kỷ niệm sau đám cưới?</label>
                    <span class="quiz_hint">Két Kỷ Niệm (Memory Vault), album ảnh khách chụp &amp; hộp thời gian</span>
                    <div class="advisor_options_grid">
                        <button type="button" class="advisor_opt_btn" data-group="memory_retention" data-val="short">
                            <span class="advisor_opt_title">6 Tháng</span>
                            <span class="advisor_opt_desc">Đủ để khách xem lại ảnh cưới và gửi lời chúc</span>
                        </button>
                        <button type="button" class="advisor_opt_btn is-active" data-group="memory_retention" data-val="long">
                            <span class="advisor_opt_title">2 Năm &amp; Xuất Ngoại Tuyến</span>
                            <span class="advisor_opt_desc">Lưu trữ 2 năm + Gói ZIP ngoại tuyến mở xem trọn đời</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Right Column: Live Recommendation Card --}}
            <aside class="advisor_result_card" id="advisor_result_card">
                <div class="result_head">
                    <span style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #8C8479; letter-spacing: 0.05em;">GÓI ĐỀ XUẤT TỐI ƯU</span>
                    <span class="result_fit_badge" id="res_fit_score">{{ $initialAnalysis['fit_score'] }}% Phù Hợp</span>
                </div>

                <h2 class="result_plan_name" id="res_plan_name">{{ $initialAnalysis['recommended_plan']['name'] }}</h2>
                <div class="result_plan_price">
                    <span id="res_plan_price">{{ number_format($initialAnalysis['recommended_plan']['price']) }}đ</span>
                    <span class="result_plan_duration" id="res_plan_duration">/ {{ $initialAnalysis['recommended_plan']['duration_days'] }} ngày</span>
                </div>

                <div class="advisor_savings_box">
                    <span class="savings_box_label">ƯỚC TÍNH TIẾT KIỆM SO VỚI THIỆP GIẤY</span>
                    <div class="savings_box_value" id="res_savings_amount">
                        Tiết kiệm ~{{ number_format($initialAnalysis['traditional_cost_comparison']['savings_amount']) }}đ ({{ $initialAnalysis['traditional_cost_comparison']['savings_percent'] }}%)
                    </div>
                    <span class="savings_box_note">So với chi phí in ấn thiệp giấy truyền thống cùng công gửi thiệp</span>
                </div>

                <div class="result_reasons_title">LÝ DO GÓI NÀY PHÙ HỢP NHẤT:</div>
                <ul class="result_reasons_list" id="res_reasons_list">
                    @foreach($initialAnalysis['fit_reasons'] as $reason)
                        <li class="result_reason_item">
                            <span style="color: #2E7D32;">✓</span>
                            <span>{{ $reason }}</span>
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('checkout.show', $initialAnalysis['recommended_plan']['slug']) }}" class="advisor_checkout_btn" id="res_checkout_link">
                    ✨ Đăng Ký Gói Này Ngay &rarr;
                </a>
            </aside>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const appEl = document.getElementById('mayhen_advisor_app');
    const analyzeUrl = appEl ? appEl.getAttribute('data-analyze-url') : '';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const state = {
        guest_scale: 'medium',
        events_count: 'dual',
        operations_need: 'advanced',
        memory_retention: 'long',
    };

    const optButtons = document.querySelectorAll('.advisor_opt_btn');
    optButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const group = btn.getAttribute('data-group');
            const val = btn.getAttribute('data-val');
            if (!group || !val) return;

            // Highlight button trong nhóm
            document.querySelectorAll(`.advisor_opt_btn[data-group="${group}"]`).forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');

            state[group] = val;
            triggerAnalysis();
        });
    });

    async function triggerAnalysis() {
        if (!analyzeUrl) return;

        try {
            const res = await fetch(analyzeUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(state)
            });

            const json = await res.json();
            if (json.success && json.analysis) {
                renderAnalysis(json.analysis);
            }
        } catch (e) {
            // silent fail
        }
    }

    function renderAnalysis(data) {
        const plan = data.recommended_plan;
        const comp = data.traditional_cost_comparison;

        document.getElementById('res_fit_score').innerText = `${data.fit_score}% Phù Hợp`;
        document.getElementById('res_plan_name').innerText = plan.name;
        document.getElementById('res_plan_price').innerText = `${new Intl.NumberFormat('vi-VN').format(plan.price)}đ`;
        document.getElementById('res_plan_duration').innerText = `/ ${plan.duration_days} ngày`;

        document.getElementById('res_savings_amount').innerText = 
            `Tiết kiệm ~${new Intl.NumberFormat('vi-VN').format(comp.savings_amount)}đ (${comp.savings_percent}%)`;

        const reasonsUl = document.getElementById('res_reasons_list');
        reasonsUl.innerHTML = '';
        data.fit_reasons.forEach(r => {
            const li = document.createElement('li');
            li.className = 'result_reason_item';
            li.innerHTML = `<span style="color: #2E7D32;">✓</span> <span>${r}</span>`;
            reasonsUl.appendChild(li);
        });

        const checkoutLink = document.getElementById('res_checkout_link');
        if (checkoutLink) {
            checkoutLink.href = `/thanh-toan/${plan.slug}`;
        }
    }
});
</script>
@endpush

