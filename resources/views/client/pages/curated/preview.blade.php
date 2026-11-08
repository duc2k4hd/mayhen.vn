@extends('client.layouts.master')

@section('title', 'Xem Thử Thiệp Tự Phối Độc Bản | ' . config('app.name'))

@push('styles')
    <link rel="stylesheet" href="/assets/client/css/pages/curated-preview.css?v={{ time() }}">
@endpush

@section('content')
<div class="mayhen_curated_preview_page" id="curatedPreviewApp" 
     data-invitation-id="{{ $invitation->id }}" 
     data-invitation-slug="{{ $invitation->slug }}"
     data-palette="{{ $currentPalette }}"
     data-style="{{ $currentStyle }}">
    
    {{-- THANH ĐIỀU KHIỂN TRÊN CÙNG (TOP ACTION BAR) --}}
    <header class="mayhen_curated_topbar">
        <div class="mayhen_curated_topbar_left">
            <a href="{{ route('curated.create') }}" class="mayhen_curated_btn_back" title="Quay lại bảng tạo thiệp">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                <span>Tạo lại</span>
            </a>
            <div class="mayhen_curated_info_pill">
                <span class="info_names">{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}</span>
                <span class="info_sep">&bull;</span>
                <span class="info_tag">{{ $styles[$currentStyle]['name'] ?? 'Lãng Mạn' }}</span>
                <span class="info_sep">&bull;</span>
                <span class="info_palette_badge" title="Bảng màu cố định: {{ $palettes[$currentPalette]['name'] ?? $currentPalette }}">
                    <span class="palette_dot" style="background-color: {{ $palettes[$currentPalette]['primary'] ?? '#85182A' }}"></span>
                    {{ $palettes[$currentPalette]['name'] ?? 'Hồng Ngọc' }}
                </span>
            </div>
        </div>

        <div class="mayhen_curated_topbar_center">
            {{-- CHUYỂN ĐỔI CHẾ ĐỘ XEM (DEVICE VIEWPORT) --}}
            <div class="mayhen_curated_viewport_switcher">
                <button type="button" class="viewport_btn is-active" data-viewport="mobile" title="Xem trên màn hình Điện thoại (Mobile 390px)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    <span>Mobile</span>
                </button>
                <button type="button" class="viewport_btn" data-viewport="tablet" title="Xem trên Máy tính bảng (Tablet 768px)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke_width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    <span>Tablet</span>
                </button>
                <button type="button" class="viewport_btn" data-viewport="desktop" title="Xem toàn màn hình Máy tính (Desktop)">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span>Toàn cảnh</span>
                </button>
            </div>
        </div>

        <div class="mayhen_curated_topbar_right">
            {{-- NÚT XEM LẠI QUÁ TRÌNH DỰNG THIỆP (REPLAY CRAFTING) --}}
            <button type="button" class="mayhen_curated_btn_action" id="btnReplayCrafting" title="Xem lại quá trình dựng thiệp từ trên xuống dưới">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                <span>Xem lại dựng thiệp ↺</span>
            </button>

            {{-- NÚT PHỐI LẠI TOÀN BỘ (REMIX LAYOUT) - BẢO TOÀN BẢNG MÀU --}}
            <button type="button" class="mayhen_curated_btn_action btn_remix" id="btnRemixAll" title="Phối lại ngẫu nhiên 7 mục - Giữ nguyên màu sắc">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                <span>Phối lại bố cục 🎲</span>
            </button>

            {{-- NÚT SANG STUDIO CHỈNH SỬA CHI TIẾT --}}
            <a href="{{ route('invitation.create', ['edit' => $invitation->slug]) }}" class="mayhen_curated_btn_action" title="Mở Studio để chỉnh sửa chữ, ảnh, nhạc...">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                <span>Chỉnh trong Studio ✏️</span>
            </a>

            {{-- NÚT XUẤT BẢN THIỆP CƯỚI --}}
            <button type="button" class="mayhen_curated_btn_publish" id="btnOpenPublishModal">
                <span>Xuất bản thiệp 🚀</span>
            </button>
        </div>
    </header>

    {{-- KHÔNG GIAN LÀM VIỆC CHÍNH (PREVIEW WORKSPACE) --}}
    <main class="mayhen_curated_workspace">
        
        {{-- BẢNG ĐIỀU KHIỂN ĐỔI BIẾN THỂ TỪNG SECTION (SECTION VARIANT CONTROLLER) --}}
        <aside class="mayhen_curated_variants_sidebar" id="variantsSidebar">
            <div class="sidebar_header">
                <h2 class="sidebar_title">Đổi Bố Cục Từng Mục</h2>
                <p class="sidebar_desc">Tinh chỉnh riêng lẻ từng section trong 7 phần tiêu chuẩn</p>
            </div>

            <div class="sidebar_sections_list">
                @php
                    $curatedSections = $curatedLayout['sections'] ?? [
                        'hero' => 'variant_a',
                        'story' => 'variant_a',
                        'parents' => 'variant_a',
                        'countdown' => 'variant_a',
                        'events' => 'variant_a',
                        'timeline' => 'variant_a',
                        'dresscode' => 'variant_a',
                    ];
                @endphp

                @foreach($sectionDefinitions as $secKey => $secDef)
                    @php
                        $activeVariant = $curatedSections[$secKey] ?? 'variant_a';
                        $variantData = $secDef['variants'][$activeVariant] ?? null;
                        $variantName = is_array($variantData) ? ($variantData['name'] ?? 'Kiểu Mặc Định') : ($variantData ?? 'Kiểu Mặc Định');
                    @endphp
                    <div class="section_ctrl_card" data-section="{{ $secKey }}">
                        <div class="section_ctrl_info">
                            <span class="section_ctrl_order">#0{{ $loop->iteration }}</span>
                            <div class="section_ctrl_text">
                                <h3 class="section_ctrl_name">{{ $secDef['title'] ?? ($secDef['name'] ?? 'Mục') }}</h3>
                                <span class="section_ctrl_variant_label" id="label-{{ $secKey }}">
                                    {{ $variantName }}
                                </span>
                            </div>
                        </div>

                        <div class="section_ctrl_actions">
                            <button type="button" class="btn_variant_nav btn_prev" data-action="switch" data-section="{{ $secKey }}" data-direction="prev" title="Kiểu trước">
                                ‹
                            </button>
                            <button type="button" class="btn_variant_nav btn_next" data-action="switch" data-section="{{ $secKey }}" data-direction="next" title="Kiểu kế tiếp">
                                ›
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="sidebar_footer">
                <div class="sidebar_note">
                    <strong>Lưu ý bảo toàn:</strong> Bảng màu <span class="palette_highlight">{{ $palettes[$currentPalette]['name'] ?? 'đã chọn' }}</span> được cố định xuyên suốt, không bị thay đổi khi đổi biến thể.
                </div>
            </div>
        </aside>

        {{-- KHUNG PREVIEW ĐIỆN THOẠI THẬT (PHONE FRAME & LIVE CRAFTING SIMULATOR) --}}
        <div class="mayhen_curated_stage_area">
            
            {{-- THANH THÔNG BÁO TIẾN TRÌNH DỰNG THIỆP (CRAFTING PROGRESS BANNER) --}}
            <div class="mayhen_curated_crafting_banner" id="craftingBanner">
                <span class="banner_sparkle">✦</span>
                <span class="banner_text">Đang hoàn thiện bản thiệp: Dựng các chi tiết nghệ thuật từ trên xuống dưới...</span>
                <span class="banner_sparkle">✦</span>
            </div>

            <div class="mayhen_curated_phone_wrapper" id="phoneWrapper">
                <div class="mayhen_curated_phone_bezel">
                    <div class="phone_notch">
                        <span class="phone_speaker"></span>
                        <span class="phone_lens"></span>
                    </div>

                    <div class="phone_screen">
                        <iframe 
                            src="{{ route('invitation.show', ['slug' => $invitation->slug]) }}?crafting=1" 
                            id="curatedPreviewIframe" 
                            class="curated_preview_iframe"
                            title="Thiệp Cưới Tự Phối {{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}"
                            allow="autoplay">
                        </iframe>
                    </div>

                    <div class="phone_home_indicator"></div>
                </div>
            </div>

        </div>

    </main>

    {{-- MODAL XUẤT BẢN THIỆP CƯỚI (PUBLISH MODAL) --}}
    <div class="mayhen_curated_modal" id="publishModal" aria-hidden="true">
        <div class="modal_backdrop" id="modalBackdrop"></div>
        <div class="modal_dialog">
            <button type="button" class="modal_close" id="btnClosePublishModal" aria-label="Đóng">&times;</button>
            <div class="modal_header">
                <div class="modal_crest">✦ MÂY HẸN ✦</div>
                <h2 class="modal_title">Thiệp Cưới Độc Bản Đã Sẵn Sàng!</h2>
                <p class="modal_desc">Chiếc thiệp được phối riêng cho {{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }} đã được lưu bền vững và sẵn sàng gửi trao.</p>
            </div>

            <div class="modal_body">
                <div class="share_url_box">
                    <label for="publicShareUrl">Đường dẫn thiệp cưới trực tuyến:</label>
                    <div class="share_url_input_wrap">
                        <input type="text" id="publicShareUrl" value="{{ url('/thiep/' . $invitation->slug) }}" readonly>
                        <button type="button" class="btn_copy_url" id="btnCopyShareUrl">
                            <span id="copyBtnText">Sao Chép</span>
                        </button>
                    </div>
                </div>

                <div class="modal_quick_actions">
                    <a href="{{ route('invitation.show', ['slug' => $invitation->slug]) }}" target="_blank" class="modal_btn modal_btn_primary">
                        Mở Thiệp Thực Tế ↗
                    </a>
                    <a href="{{ route('invitation.create', ['edit' => $invitation->slug]) }}" class="modal_btn modal_btn_secondary">
                        Vào Studio Tinh Chỉnh Chữ &amp; Ảnh ✏️
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    <script src="/assets/client/js/pages/curated-preview.js?v={{ time() }}"></script>
@endpush
