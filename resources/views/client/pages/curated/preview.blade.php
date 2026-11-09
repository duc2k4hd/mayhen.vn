<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Xem Thử Thiệp Tự Phối Độc Bản: {{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }} | {{ config('app.name') }}</title>
    
    @include('client.components.favicon')

    {{-- Fonts Chuẩn Quiet Luxury --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Stylesheet Chuyên Biệt Màn Hình Preview --}}
    <link rel="stylesheet" href="/assets/client/css/pages/curated-preview.css?v={{ time() }}">
</head>
<body class="mayhen_curated_preview_body">

<div class="mayhen_curated_preview_page" id="curatedPreviewApp" 
     data-invitation-id="{{ $invitation->id }}" 
     data-invitation-slug="{{ $invitation->slug }}"
     data-palette="{{ $currentPalette }}"
     data-style="{{ $currentStyle }}"
     data-should-animate="{{ !empty($shouldAnimate) ? '1' : '0' }}">
    
    {{-- THANH ĐIỀU KHIỂN TRÊN CÙNG (STUDIO TOPBAR) --}}
    <header class="mayhen_curated_topbar">
        <div class="mayhen_curated_topbar_left">
            <a href="{{ route('curated.create') }}" class="mayhen_curated_btn_back" title="Quay lại bảng tạo thiệp">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                <span>Tạo Lại</span>
            </a>
            <div class="mayhen_curated_brand_badge">
                <span class="brand_dot">✦</span>
                <span class="brand_name">MÂY HẸN</span>
                <span class="brand_tag">ATELIER</span>
            </div>
            <div class="mayhen_curated_info_pill">
                <span class="info_names">{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}</span>
                <span class="info_sep">&bull;</span>
                <span class="info_tag" title="Mẫu thiết kế nguồn nguyên bản Mây Hẹn">Mẫu nguồn: <strong>{{ $template->name ?? 'Giữa Muôn Ngàn Vì Sao' }}</strong></span>
                <span class="info_sep">&bull;</span>
                <span class="info_tag">{{ $styles[$currentStyle]['name'] ?? 'Lãng Mạn' }}</span>
                <span class="info_sep">&bull;</span>
                <span class="info_palette_badge" title="Bảng màu: {{ $palettes[$currentPalette]['name'] ?? $currentPalette }}">
                    <span class="palette_dot" style="background-color: {{ $palettes[$currentPalette]['primary'] ?? '#85182A' }}"></span>
                    {{ $palettes[$currentPalette]['short_name'] ?? ($palettes[$currentPalette]['name'] ?? 'Hồng Ngọc') }}
                </span>
            </div>
        </div>

        <div class="mayhen_curated_topbar_center">
            {{-- CHUYỂN ĐỔI CHẾ ĐỘ XEM (VIEWPORT SWITCHER) --}}
            <div class="mayhen_curated_viewport_switcher">
                <button type="button" class="viewport_btn is-active" data-viewport="mobile" title="Khung Điện Thoại (Mobile 390px)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    <span>Điện thoại</span>
                </button>
                <button type="button" class="viewport_btn" data-viewport="tablet" title="Khung Máy Tính Bảng (Tablet 768px)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    <span>Máy tính bảng</span>
                </button>
                <button type="button" class="viewport_btn" data-viewport="desktop" title="Toàn Màn Hình (Desktop)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span>Toàn cảnh</span>
                </button>
            </div>

            {{-- LIVE CRAFTING PROGRESS PILL & SKIP --}}
            <div class="mayhen_curated_crafting_pill" id="craftingStatusPill" style="{{ empty($shouldAnimate) ? 'display: none;' : '' }}">
                <span class="crafting_spinner"></span>
                <span class="crafting_status_text" id="craftingStatusText">Đang trình bày thiệp...</span>
                <button type="button" class="crafting_btn_skip" id="btnSkipCrafting" title="Bỏ qua và xem ngay thiệp thành phẩm">Bỏ qua ›</button>
            </div>
        </div>

        <div class="mayhen_curated_topbar_right">
            {{-- NÚT MỞ BẢNG BỐ CỤC CHO MOBILE (DRAWER TOGGLE) --}}
            <button type="button" class="mayhen_curated_btn_action btn_toggle_drawer" id="btnToggleSidebarDrawer" title="Mở bảng tùy biến bố cục 7 section">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
                <span>Bố Cục</span>
            </button>

            {{-- NÚT XEM LẠI QUÁ TRÌNH DỰNG THIỆP (REPLAY) --}}
            <button type="button" class="mayhen_curated_btn_action" id="btnReplayCrafting" title="Xem lại quá trình dựng thiệp trực tiếp">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                <span>Xem lại</span>
            </button>

            {{-- NÚT PHỐI LẠI TOÀN BỘ (REMIX LAYOUT) - BẢO TOÀN MÀU SẮC --}}
            <button type="button" class="mayhen_curated_btn_action btn_remix" id="btnRemixAll" title="Phối lại ngẫu nhiên 7 mục - Giữ nguyên bảng màu">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                <span>Phối lại</span>
            </button>

            {{-- CHỈNH SỬA CHI TIẾT TRONG STUDIO --}}
            <a href="{{ route('invitation.create', ['edit' => $invitation->slug]) }}" class="mayhen_curated_btn_action" title="Mở Studio để chỉnh sửa chữ, ảnh, nhạc...">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                <span>Vào Studio</span>
            </a>

            {{-- XUẤT BẢN THIỆP CƯỚI --}}
            <button type="button" class="mayhen_curated_btn_publish" id="btnOpenPublishModal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                <span>Xuất bản</span>
            </button>
        </div>
    </header>

    {{-- KHÔNG GIAN LÀM VIỆC CHÍNH (STUDIO WORKSPACE) --}}
    <main class="mayhen_curated_workspace">
        
        {{-- BẢNG ĐIỀU KHIỂN BIẾN THỂ 7 SECTION (SIDEBAR CONTROLLER) --}}
        <aside class="mayhen_curated_variants_sidebar" id="variantsSidebar">
            <div class="sidebar_header">
                <div class="sidebar_header_top">
                    <span class="sidebar_kicker">7 SECTION TIÊU CHUẨN</span>
                    <button type="button" class="sidebar_close_btn" id="btnCloseSidebarDrawer" aria-label="Đóng bảng tùy biến">&times;</button>
                </div>
                <h2 class="sidebar_title">Đổi Bố Cục Từng Mục</h2>
                <p class="sidebar_desc">Tùy biến riêng lẻ 3 biến thể mỹ thuật của từng section</p>
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
                        'album' => 'variant_a',
                    ];
                @endphp

                @foreach($sectionDefinitions as $secKey => $secDef)
                    @php
                        $activeVariant = $curatedSections[$secKey] ?? 'variant_a';
                        $variantData = $secDef['variants'][$activeVariant] ?? null;
                        $variantName = is_array($variantData) ? ($variantData['name'] ?? 'Kiểu Mặc Định') : ($variantData ?? 'Kiểu Mặc Định');
                    @endphp
                    <div class="section_ctrl_card" data-section="{{ $secKey }}">
                        <div class="section_ctrl_head">
                            <span class="section_ctrl_num">#0{{ $loop->iteration }}</span>
                            <h3 class="section_ctrl_title">{{ $secDef['title'] ?? ($secDef['name'] ?? 'Mục') }}</h3>
                        </div>

                        <div class="section_ctrl_body">
                            <div class="section_ctrl_variant_info">
                                <span class="variant_badge">Biến thể</span>
                                <span class="section_ctrl_variant_label" id="label-{{ $secKey }}">
                                    {{ $variantName }}
                                </span>
                            </div>

                            <div class="section_ctrl_nav_btns">
                                <button type="button" class="btn_variant_nav btn_prev" data-action="switch" data-section="{{ $secKey }}" data-direction="prev" title="Biến thể trước">
                                    ‹
                                </button>
                                <button type="button" class="btn_variant_nav btn_next" data-action="switch" data-section="{{ $secKey }}" data-direction="next" title="Biến thể kế tiếp">
                                    ›
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="sidebar_footer">
                <div class="sidebar_palette_lock">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="palette_lock_svg"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <div class="palette_lock_text">
                        <strong>Bảo toàn màu sắc:</strong> Tông màu <span class="palette_highlight">{{ $palettes[$currentPalette]['short_name'] ?? ($palettes[$currentPalette]['name'] ?? 'đã chọn') }}</span> được cố định bất biến khi đổi bố cục.
                    </div>
                </div>
            </div>
        </aside>

        {{-- LỚP NỀN MỜ CHO MOBILE DRAWER --}}
        <div class="mayhen_curated_sidebar_backdrop" id="sidebarBackdrop"></div>

        {{-- KHU VỰC SÂN KHẤU PREVIEW (PREVIEW STAGE) --}}
        <section class="mayhen_curated_stage_area">
            
            <div class="mayhen_curated_phone_wrapper" id="phoneWrapper">
                <div class="mayhen_curated_phone_bezel">
                    {{-- DYNAMIC ISLAND & SPEAKER --}}
                    <div class="phone_notch">
                        <span class="phone_speaker"></span>
                        <span class="phone_lens"></span>
                    </div>

                    {{-- MÀN HÌNH CHÍNH (IFRAME) --}}
                    <div class="phone_screen">
                        <iframe 
                            src="{{ route('invitation.show', ['slug' => $invitation->slug]) }}{{ (!empty($shouldAnimate)) ? '?crafting=1' : '' }}" 
                            id="curatedPreviewIframe" 
                            class="curated_preview_iframe"
                            title="Thiệp Cưới Tự Phối {{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}"
                            allow="autoplay">
                        </iframe>
                    </div>

                    {{-- HOME INDICATOR --}}
                    <div class="phone_home_indicator"></div>
                </div>
            </div>

        </section>

    </main>

    {{-- MODAL XUẤT BẢN THIỆP CƯỚI (PUBLISH MODAL) --}}
    <div class="mayhen_curated_modal" id="publishModal" aria-hidden="true">
        <div class="modal_backdrop" id="modalBackdrop"></div>
        <div class="modal_dialog">
            <button type="button" class="modal_close" id="btnClosePublishModal" aria-label="Đóng">&times;</button>
            <div class="modal_header">
                <div class="modal_crest">✦ MÂY HẸN &bull; QUIET LUXURY ✦</div>
                <h2 class="modal_title">Thiệp Cưới Độc Bản Đã Sẵn Sàng!</h2>
                <p class="modal_desc">Tác phẩm thiệp cưới tự phối dành riêng cho {{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }} đã được lưu bền vững vào hệ thống.</p>
            </div>

            <div class="modal_body">
                <div class="share_url_box">
                    <label for="publicShareUrl">Đường dẫn chia sẻ trực tuyến:</label>
                    <div class="share_url_input_wrap">
                        <input type="text" id="publicShareUrl" value="{{ url('/thiep/' . $invitation->slug) }}" readonly>
                        <button type="button" class="btn_copy_url" id="btnCopyShareUrl">
                            <span id="copyBtnText">Sao Chép</span>
                        </button>
                    </div>
                </div>

                <div class="modal_quick_actions">
                    <a href="{{ route('invitation.show', ['slug' => $invitation->slug]) }}" target="_blank" class="modal_btn modal_btn_primary">
                        Mở Thiệp Khách Mời ↗
                    </a>
                    <a href="{{ route('invitation.create', ['edit' => $invitation->slug]) }}" class="modal_btn modal_btn_secondary">
                        Vào Studio Tinh Chỉnh Chữ &amp; Ảnh
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="/assets/client/js/pages/curated-preview.js?v={{ time() }}"></script>
</body>
</html>
