@extends('client.layouts.master')

@section('page_title', 'Thiết Lập Thiệp Cưới Từng Bước | ' . config('app.name'))
@section('meta_description', 'Khởi tạo thiệp cưới online cao cấp của bạn chỉ trong vài phút với hướng dẫn từng bước chuẩn Quiet Luxury.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/onboarding.css') }}">
@endpush

@section('content')
<div class="mayhen_onboarding_page">
    <div class="mayhen_onboarding_container">
        <!-- Top Bar Header -->
        <div class="mayhen_onboarding_header">
            <div class="mayhen_onboarding_badge">MÂY HẸN ONBOARDING</div>
            <h1 class="mayhen_onboarding_title">Khởi Tạo Thiệp Cưới Của Bạn</h1>
            <p class="mayhen_onboarding_subtitle">
                Chào mừng <strong>{{ $user->name }}</strong>. Hãy cùng Mây Hẹn thiết lập thông tin ban đầu để tạo nên tác phẩm thiệp cưới tinh tế nhất.
            </p>
        </div>

        <!-- Wizard Progress Bar -->
        <div class="mayhen_onboarding_stepper">
            <div class="mayhen_onboarding_step_track">
                <div class="mayhen_onboarding_step_fill" id="onboarding-progress-fill" style="width: 25%;"></div>
            </div>
            <div class="mayhen_onboarding_step_nodes">
                <div class="mayhen_onboarding_node active" data-step-node="1">
                    <div class="mayhen_onboarding_node_circle">1</div>
                    <span class="mayhen_onboarding_node_label">Mục Đích</span>
                </div>
                <div class="mayhen_onboarding_node" data-step-node="2">
                    <div class="mayhen_onboarding_node_circle">2</div>
                    <span class="mayhen_onboarding_node_label">Chọn Mẫu</span>
                </div>
                <div class="mayhen_onboarding_node" data-step-node="3">
                    <div class="mayhen_onboarding_node_circle">3</div>
                    <span class="mayhen_onboarding_node_label">Thông Tin Cưới</span>
                </div>
                <div class="mayhen_onboarding_node" data-step-node="4">
                    <div class="mayhen_onboarding_node_circle">4</div>
                    <span class="mayhen_onboarding_node_label">Khởi Tạo</span>
                </div>
            </div>
        </div>

        <!-- Main Wizard Card -->
        <div class="mayhen_onboarding_card">
            <!-- Step 1: Role Intent -->
            <div class="mayhen_onboarding_step_pane active" id="onboarding-step-1">
                <h2 class="mayhen_onboarding_step_title">Bạn dự định tạo thiệp cưới cho ai?</h2>
                <p class="mayhen_onboarding_step_desc">Lựa chọn này giúp Mây Hẹn tối ưu hóa các mẫu câu xưng hô và trải nghiệm phù hợp nhất với bạn.</p>

                <div class="mayhen_onboarding_intent_grid">
                    <label class="mayhen_onboarding_intent_card {{ ($draft['role_intent'] ?? 'couple') === 'couple' ? 'selected' : '' }}">
                        <input type="radio" name="wizard_role_intent" value="couple" {{ ($draft['role_intent'] ?? 'couple') === 'couple' ? 'checked' : '' }} class="mayhen_onboarding_radio">
                        <div class="mayhen_onboarding_intent_icon">💍</div>
                        <div class="mayhen_onboarding_intent_content">
                            <h3 class="mayhen_onboarding_intent_title">Cho Đám Cưới Của Chính Mình</h3>
                            <p class="mayhen_onboarding_intent_text">Tôi là Cô Dâu hoặc Chú Rể, đang tự tay chuẩn bị thiệp cưới cho ngày trọng đại.</p>
                        </div>
                    </label>

                    <label class="mayhen_onboarding_intent_card {{ ($draft['role_intent'] ?? '') === 'relative' ? 'selected' : '' }}">
                        <input type="radio" name="wizard_role_intent" value="relative" {{ ($draft['role_intent'] ?? '') === 'relative' ? 'checked' : '' }} class="mayhen_onboarding_radio">
                        <div class="mayhen_onboarding_intent_icon">🎁</div>
                        <div class="mayhen_onboarding_intent_content">
                            <h3 class="mayhen_onboarding_intent_title">Tạo Hộ Người Thân / Bạn Bè</h3>
                            <p class="mayhen_onboarding_intent_text">Tôi muốn tạo một tấm thiệp cưới ý nghĩa để làm quà tặng người thân hoặc bạn bè thân thiết.</p>
                        </div>
                    </label>

                    <label class="mayhen_onboarding_intent_card {{ ($draft['role_intent'] ?? '') === 'planner' ? 'selected' : '' }}">
                        <input type="radio" name="wizard_role_intent" value="planner" {{ ($draft['role_intent'] ?? '') === 'planner' ? 'checked' : '' }} class="mayhen_onboarding_radio">
                        <div class="mayhen_onboarding_intent_icon">📋</div>
                        <div class="mayhen_onboarding_intent_content">
                            <h3 class="mayhen_onboarding_intent_title">Wedding Planner & Studio Cưới</h3>
                            <p class="mayhen_onboarding_intent_text">Tôi là đơn vị tổ chức sự kiện cưới hoặc studio nhiếp ảnh phục vụ nhiều cặp đôi.</p>
                        </div>
                    </label>
                </div>

                <div class="mayhen_onboarding_actions" style="justify-content: flex-end;">
                    <button type="button" class="mayhen_onboarding_btn mayhen_onboarding_btn_primary" id="btn-next-step-1">
                        Tiếp Tục Chọn Mẫu Thiệp →
                    </button>
                </div>
            </div>

            <!-- Step 2: Choose Template -->
            <div class="mayhen_onboarding_step_pane" id="onboarding-step-2" style="display: none;">
                <h2 class="mayhen_onboarding_step_title">Chọn phong cách thiệp cưới yêu thích</h2>
                <p class="mayhen_onboarding_step_desc">Bạn hoàn toàn có thể thay đổi phông chữ, màu sắc và nội dung trong Studio sau này.</p>

                <div class="mayhen_onboarding_templates_grid">
                    @forelse($templates as $tmpl)
                        @php
                            $isSelected = (int)($draft['template_id'] ?? 0) === $tmpl->id;
                            $thumb = $tmpl->thumbnail_url ?: asset('assets/client/images/template-placeholder.webp');
                        @endphp
                        <div class="mayhen_onboarding_template_card {{ $isSelected ? 'selected' : '' }}" data-template-id="{{ $tmpl->id }}" data-template-name="{{ $tmpl->name }}" data-template-thumb="{{ $thumb }}">
                            <div class="mayhen_onboarding_template_media">
                                <img src="{{ $thumb }}" alt="{{ $tmpl->name }}" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('assets/client/images/template-placeholder.webp') }}';">
                                <span class="mayhen_onboarding_template_badge">{{ $tmpl->category ?? 'Quiet Luxury' }}</span>
                            </div>
                            <div class="mayhen_onboarding_template_info">
                                <h3 class="mayhen_onboarding_template_title">{{ $tmpl->name }}</h3>
                                <p class="mayhen_onboarding_template_desc">{{ Str::limit($tmpl->description ?? 'Thiết kế tối giản, thanh lịch và giàu cảm xúc.', 65) }}</p>
                                <div class="mayhen_onboarding_template_actions">
                                    <a href="{{ route('templates.preview', $tmpl->slug) }}" target="_blank" class="mayhen_onboarding_btn_preview">
                                        👁 Xem Thử
                                    </a>
                                    <button type="button" class="mayhen_onboarding_btn_select {{ $isSelected ? 'active' : '' }}">
                                        {{ $isSelected ? '✓ Đã Chọn' : 'Chọn Mẫu Này' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p style="grid-column: span 3; text-align: center; color: #8C827A; padding: 40px;">
                            Hiện đang cập nhật danh sách mẫu thiệp cưới.
                        </p>
                    @endforelse
                </div>

                <div class="mayhen_onboarding_actions">
                    <button type="button" class="mayhen_onboarding_btn mayhen_onboarding_btn_outline" id="btn-back-step-2">
                        ← Quay Lại
                    </button>
                    <button type="button" class="mayhen_onboarding_btn mayhen_onboarding_btn_primary" id="btn-next-step-2">
                        Tiếp Tục Điền Thông Tin Cưới →
                    </button>
                </div>
            </div>

            <!-- Step 3: Couple & Wedding Info -->
            <div class="mayhen_onboarding_step_pane" id="onboarding-step-3" style="display: none;">
                <h2 class="mayhen_onboarding_step_title">Thông tin cặp đôi & Ngày trọng đại</h2>
                <p class="mayhen_onboarding_step_desc">Những thông tin này sẽ được điền tự động vào thiệp cưới của bạn. Bạn có thể bổ sung thêm ảnh và câu chuyện tình yêu ở bước kế tiếp.</p>

                <div class="mayhen_onboarding_form_grid">
                    <div class="mayhen_onboarding_form_group">
                        <label class="mayhen_onboarding_label">Tên Chú Rể *</label>
                        <input type="text" id="wizard-groom-name" class="mayhen_onboarding_input" value="{{ $draft['groom_name'] ?? '' }}" placeholder="Ví dụ: Hoàng Long" required>
                        <span class="mayhen_onboarding_hint">Tên thường gọi hoặc tên đầy đủ hiển thị trên thiệp.</span>
                    </div>

                    <div class="mayhen_onboarding_form_group">
                        <label class="mayhen_onboarding_label">Tên Cô Dâu *</label>
                        <input type="text" id="wizard-bride-name" class="mayhen_onboarding_input" value="{{ $draft['bride_name'] ?? '' }}" placeholder="Ví dụ: Mai Phương" required>
                        <span class="mayhen_onboarding_hint">Tên hiển thị bên cạnh chú rể.</span>
                    </div>

                    <div class="mayhen_onboarding_form_group">
                        <label class="mayhen_onboarding_label">Ngày Tổ Chức Hôn Lễ *</label>
                        <input type="date" id="wizard-wedding-date" class="mayhen_onboarding_input" value="{{ $draft['wedding_date'] ?? '' }}" required>
                        <span class="mayhen_onboarding_hint">Hệ thống sẽ tự động hiển thị đồng hồ đếm ngược (Countdown).</span>
                    </div>

                    <div class="mayhen_onboarding_form_group">
                        <label class="mayhen_onboarding_label">Tên Trung Tâm Tiệc Cưới / Sảnh Tiệc</label>
                        <input type="text" id="wizard-venue-name" class="mayhen_onboarding_input" value="{{ $draft['venue_name'] ?? '' }}" placeholder="Ví dụ: Trung tâm Tiệc cưới The Adora Center">
                    </div>

                    <div class="mayhen_onboarding_form_group" style="grid-column: span 2;">
                        <label class="mayhen_onboarding_label">Địa Chỉ Tổ Chức Hôn Lễ</label>
                        <input type="text" id="wizard-venue-address" class="mayhen_onboarding_input" value="{{ $draft['venue_address'] ?? '' }}" placeholder="Ví dụ: 431 Hoàng Văn Thụ, Phường 4, Quận Tân Bình, TP. Hồ Chí Minh">
                        <span class="mayhen_onboarding_hint">Khách mời có thể mở trực tiếp bản đồ chỉ đường từ thiệp mời cưới.</span>
                    </div>
                </div>

                <div class="mayhen_onboarding_actions">
                    <button type="button" class="mayhen_onboarding_btn mayhen_onboarding_btn_outline" id="btn-back-step-3">
                        ← Quay Lại
                    </button>
                    <button type="button" class="mayhen_onboarding_btn mayhen_onboarding_btn_primary" id="btn-next-step-3">
                        Xem Lại & Khởi Tạo Thiệp →
                    </button>
                </div>
            </div>

            <!-- Step 4: Summary & Complete -->
            <div class="mayhen_onboarding_step_pane" id="onboarding-step-4" style="display: none;">
                <h2 class="mayhen_onboarding_step_title">Xem lại và xác nhận khởi tạo thiệp</h2>
                <p class="mayhen_onboarding_step_desc">Thiệp cưới điện tử của bạn sẽ được khởi tạo tức thì. Bạn sẽ được chuyển trực tiếp vào Studio để tinh chỉnh hình ảnh, lời tựa và âm nhạc.</p>

                <div class="mayhen_onboarding_summary_card">
                    <div class="mayhen_onboarding_summary_row">
                        <span class="mayhen_onboarding_summary_label">Mẫu Thiệp Đã Chọn</span>
                        <strong class="mayhen_onboarding_summary_value" id="summary-template-name">--</strong>
                    </div>
                    <div class="mayhen_onboarding_summary_row">
                        <span class="mayhen_onboarding_summary_label">Cặp Đôi Uyên Ương</span>
                        <strong class="mayhen_onboarding_summary_value" id="summary-couple-names">--</strong>
                    </div>
                    <div class="mayhen_onboarding_summary_row">
                        <span class="mayhen_onboarding_summary_label">Ngày Trọng Đại</span>
                        <span class="mayhen_onboarding_summary_value" id="summary-wedding-date">--</span>
                    </div>
                    <div class="mayhen_onboarding_summary_row">
                        <span class="mayhen_onboarding_summary_label">Địa Điểm Tổ Chức</span>
                        <span class="mayhen_onboarding_summary_value" id="summary-venue-details">--</span>
                    </div>
                </div>

                <form action="{{ route('onboarding.complete') }}" method="POST" id="onboarding-complete-form">
                    @csrf
                    <input type="hidden" name="role_intent" id="final-role-intent" value="{{ $draft['role_intent'] ?? 'couple' }}">
                    <input type="hidden" name="template_id" id="final-template-id" value="{{ $draft['template_id'] ?? '' }}">
                    <input type="hidden" name="groom_name" id="final-groom-name" value="{{ $draft['groom_name'] ?? '' }}">
                    <input type="hidden" name="bride_name" id="final-bride-name" value="{{ $draft['bride_name'] ?? '' }}">
                    <input type="hidden" name="wedding_date" id="final-wedding-date" value="{{ $draft['wedding_date'] ?? '' }}">
                    <input type="hidden" name="venue_name" id="final-venue-name" value="{{ $draft['venue_name'] ?? '' }}">
                    <input type="hidden" name="venue_address" id="final-venue-address" value="{{ $draft['venue_address'] ?? '' }}">

                    <div class="mayhen_onboarding_actions">
                        <button type="button" class="mayhen_onboarding_btn mayhen_onboarding_btn_outline" id="btn-back-step-4">
                            ← Sửa Lại Thông Tin
                        </button>
                        <button type="submit" class="mayhen_onboarding_btn mayhen_onboarding_btn_gold" style="font-size: 15px; padding: 12px 28px;">
                            ✨ Tạo Thiệp & Mở Studio Chỉnh Sửa
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Footer Help -->
        <div class="mayhen_onboarding_footer">
            <span>Cần trợ giúp trong quá trình thiết lập?</span>
            <a href="{{ route('contact') }}" target="_blank">Liên hệ tư vấn viên Mây Hẹn</a>
            <span>•</span>
            <form action="{{ route('onboarding.reset') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" style="background: none; border: none; padding: 0; color: #8C827A; font-size: 13px; text-decoration: underline; cursor: pointer;">
                    Đặt lại từ đầu
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        window.MAYHEN_ONBOARDING_CONFIG = {
            saveStepUrl: "{{ route('onboarding.save_step') }}",
            csrfToken: "{{ csrf_token() }}",
            initialStep: {{ (int)($draft['step'] ?? 1) }}
        };
    </script>
    <script type="module" src="{{ asset('assets/client/js/pages/onboarding.js') }}"></script>
@endpush
