@php
    $curatedBrandName = \App\Models\PlatformSetting::getBrandName();
    $curatedBrandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
    $weddingExtra = is_array($wedding->extra_data ?? null) ? $wedding->extra_data : [];
    $selectableTemplates = $selectableTemplates ?? [];
    $isCuratedSeven = $isCuratedSeven ?? true;
    $weddingDateVal = $wedding->wedding_date ? \Illuminate\Support\Carbon::parse($wedding->wedding_date)->format('Y-m-d') : now()->addMonths(2)->format('Y-m-d');
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="application-name" content="{{ $curatedBrandName }}">
    <title>Xem Thử Thiệp Tự Phối Độc Bản: {{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }} | {{ $curatedBrandName }}</title>

    @include('client.components.favicon')

    {{-- Fonts Chuẩn Quiet Luxury đồng bộ dự án --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">

    {{-- Design Tokens hệ thống + Stylesheet Preview Workbench V2 --}}
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/curated-preview.css') }}">
</head>
<body class="mayhen_curated_preview_body">

<div class="mayhen_curated_preview_page" id="curatedPreviewApp"
     data-invitation-id="{{ $invitation->id }}"
     data-invitation-slug="{{ $invitation->slug }}"
     data-public-url="{{ url('/thiep/' . $invitation->slug) }}"
     data-palette="{{ $currentPalette }}"
     data-style="{{ $currentStyle }}"
     data-template-slug="{{ $template->slug }}"
     data-is-curated-seven="{{ $isCuratedSeven ? '1' : '0' }}"
     data-should-animate="{{ !empty($shouldAnimate) ? '1' : '0' }}">

    {{-- THANH ĐIỀU KHIỂN TRÊN CÙNG (STUDIO TOPBAR) --}}
    <header class="mayhen_curated_topbar {{ !empty($shouldAnimate) ? 'is_crafting' : '' }}">
        <div class="mayhen_curated_topbar_left">
            <a href="{{ route('curated.create') }}" class="mayhen_curated_btn_back" title="Quay lại bảng tạo thiệp">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                <span>Tạo Lại</span>
            </a>
            <div class="mayhen_curated_brand_badge">
                <span class="brand_dot">✦</span>
                <span class="brand_name">{{ $curatedBrandNameUpper }}</span>
                <span class="brand_tag">CURATED</span>
            </div>
            <div class="mayhen_curated_info_pill">
                <span class="info_names" id="topbarCoupleNames">{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}</span>
                <span class="info_sep">&bull;</span>
                <span class="info_tag" id="topbarStyleName">{{ $styles[$currentStyle]['name'] ?? 'Lãng Mạn' }}</span>
                <span class="info_sep">&bull;</span>
                <span class="info_palette_badge" title="Bảng màu: {{ $palettes[$currentPalette]['name'] ?? $currentPalette }}">
                    <span class="palette_dot" id="topbarPaletteDot" style="background-color: {{ $palettes[$currentPalette]['primary'] ?? '#85182A' }}"></span>
                    <span id="topbarPaletteName">{{ $palettes[$currentPalette]['short_name'] ?? ($palettes[$currentPalette]['name'] ?? 'Đỏ Ruby') }}</span>
                </span>
            </div>
            <span class="mobile_couple_title" id="mobileCoupleTitle">{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}</span>
        </div>

        <div class="mayhen_curated_topbar_center">
            {{-- CHUYỂN ĐỔI CHẾ ĐỘ XEM (VIEWPORT SWITCHER - DESKTOP/TABLET) --}}
            <div class="mayhen_curated_viewport_switcher">
                <button type="button" class="viewport_btn is-active" data-viewport="mobile" title="Khung Điện Thoại (390px)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    <span>Điện thoại</span>
                </button>
                <button type="button" class="viewport_btn" data-viewport="tablet" title="Khung Máy Tính Bảng (768px)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                    <span>Tablet</span>
                </button>
                <button type="button" class="viewport_btn" data-viewport="desktop" title="Toàn Màn Hình">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    <span>Toàn cảnh</span>
                </button>
            </div>

            {{-- LIVE CRAFTING PROGRESS PILL & SKIP --}}
            <div class="mayhen_curated_crafting_pill {{ !empty($shouldAnimate) ? 'is-active' : '' }}" id="craftingStatusPill" style="{{ empty($shouldAnimate) ? 'display: none;' : '' }}">
                <span class="crafting_spinner"></span>
                <span class="crafting_status_text" id="craftingStatusText">Đang dựng tác phẩm...</span>
                <div class="crafting_progress_wrap">
                    <div class="crafting_progress_track">
                        <div class="crafting_progress_bar" id="craftingProgressBar" style="width: 0%;"></div>
                    </div>
                    <span class="crafting_progress_pct" id="craftingProgressPct">0%</span>
                </div>
                <button type="button" class="crafting_btn_skip" id="btnSkipCrafting" title="Bỏ qua hoạt cảnh">Bỏ qua ›</button>
            </div>
        </div>

        <div class="mayhen_curated_topbar_right">
            <button type="button" class="mayhen_curated_btn_action desktop_only_btn" id="btnReplayCrafting" title="Xem lại quá trình dựng thiệp">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                <span>Xem lại</span>
            </button>

            <button type="button" class="mayhen_curated_btn_action btn_remix desktop_only_btn" id="btnRemixAll" title="Đổi mẫu thiệp khác cùng gu — Giữ nguyên bảng màu">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                <span>Phối lại</span>
            </button>

            <a href="{{ route('invitation.create', ['edit' => $invitation->slug]) }}" class="mayhen_curated_btn_action desktop_only_btn" title="Mở Studio chuyên sâu để tải ảnh cưới, đổi nhạc, hiệu ứng">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                <span>Vào Studio</span>
            </a>

            <button type="button" class="mayhen_curated_btn_publish" id="btnOpenPublishModal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                <span>Xuất bản</span>
            </button>
        </div>
    </header>

    {{-- KHÔNG GIAN LÀM VIỆC CHÍNH (WORKBENCH) --}}
    <main class="mayhen_curated_workspace">

        {{-- BẢNG TÙY BIẾN 3 TAB (DESKTOP SIDEBAR / MOBILE BOTTOM SHEET) --}}
        <aside class="mayhen_curated_variants_sidebar" id="variantsSidebar" aria-label="Bảng tùy chỉnh thiệp cưới">
            <div class="mobile_sheet_handle" aria-hidden="true"><span></span></div>

            <div class="sidebar_header">
                <div class="sidebar_header_top">
                    <span class="sidebar_kicker">{{ $curatedBrandNameUpper }} &bull; CURATED STUDIO</span>
                    <button type="button" class="sidebar_close_btn" id="btnCloseSidebarDrawer" aria-label="Đóng bảng tùy chỉnh">&times;</button>
                </div>
                <h2 class="sidebar_title">Tùy Chỉnh Bố Cục Từng Mục</h2>
            </div>

            {{-- 3 TAB CHỨC NĂNG RÕ RÀNG --}}
            <div class="sidebar_tabs" role="tablist">
                <button type="button" class="sidebar_tab is-active" data-tab="design" role="tab" aria-selected="true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.9 0 1.6-.7 1.6-1.6 0-.4-.2-.8-.4-1.1-.3-.3-.4-.7-.4-1.1 0-.9.7-1.6 1.6-1.6H16c3.3 0 6-2.7 6-6 0-4.9-4.5-8.6-10-8.6z"/></svg>
                    <span>Giao diện</span>
                </button>
                <button type="button" class="sidebar_tab" data-tab="content" role="tab" aria-selected="false">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                    <span>Nội dung &amp; AI</span>
                </button>
                <button type="button" class="sidebar_tab" data-tab="sections" role="tab" aria-selected="false">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                    <span>Mục lục</span>
                </button>
            </div>

            <div class="sidebar_panels_scroll">

                {{-- ================= TAB 1: GIAO DIỆN (MẪU THIỆP, BẢNG MÀU, PHONG CÁCH, 7↔10 MỤC) ================= --}}
                <div class="sidebar_tab_panel is-active" data-tab-panel="design">

                    {{-- Thẻ mẫu hiện tại + Đổi nhanh --}}
                    <div class="curated_active_tpl_card">
                        <div class="active_tpl_meta">
                            <span class="active_tpl_badge">✦ Mẫu đang hiển thị</span>
                            <span class="active_tpl_mode_pill" id="activeModePill">{{ $isCuratedSeven ? '7 Mục Tinh Gọn' : '10 Mục Đầy Đủ' }}</span>
                        </div>
                        <h3 class="template_source_name" id="templateSourceName">{{ $template->name }}</h3>
                        <button type="button" class="btn_remix_sidebar" id="btnRemixSidebar">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                            <span>Đổi Sang Mẫu Khác Cùng Gu</span>
                        </button>
                    </div>

                    {{-- Chế độ 7 mục vs 10 mục đầy đủ --}}
                    <div class="ctrl_block">
                        <div class="ctrl_block_head">
                            <span class="ctrl_block_label">Quy mô nội dung thiệp</span>
                        </div>
                        <div class="mode_toggle_segmented" id="layoutModeToggle">
                            <button type="button" class="mode_seg_btn {{ $isCuratedSeven ? 'is-active' : '' }}" data-curated-seven="1">
                                <strong>7 Mục Tinh Gọn</strong>
                                <small>Nhẹ nhàng, tập trung</small>
                            </button>
                            <button type="button" class="mode_seg_btn {{ !$isCuratedSeven ? 'is-active' : '' }}" data-curated-seven="0">
                                <strong>10 Mục Đầy Đủ</strong>
                                <small>Có RSVP, QR, Dresscode</small>
                            </button>
                        </div>
                    </div>

                    {{-- Chọn Bảng Màu trực tiếp (1 chạm) --}}
                    <div class="ctrl_block">
                        <div class="ctrl_block_head">
                            <span class="ctrl_block_label">Bảng màu chủ đạo</span>
                            <span class="ctrl_block_hint">Đổi ngay 1 chạm</span>
                        </div>
                        <div class="palette_picker_grid" id="palettePickerGrid">
                            @foreach($palettes as $pKey => $pVal)
                                <button type="button"
                                        class="palette_chip_btn {{ $currentPalette === $pKey ? 'is-active' : '' }}"
                                        data-pick-palette="{{ $pKey }}"
                                        title="{{ $pVal['name'] }}">
                                    <span class="palette_chip_dots">
                                        <i style="background: {{ $pVal['primary'] }}"></i>
                                        <i style="background: {{ $pVal['accent'] }}"></i>
                                    </span>
                                    <span class="palette_chip_name">{{ $pVal['short_name'] ?? $pVal['name'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Chọn Phong Cách Thẩm Mỹ --}}
                    <div class="ctrl_block">
                        <div class="ctrl_block_head">
                            <span class="ctrl_block_label">Phong cách thẩm mỹ</span>
                        </div>
                        <div class="style_picker_grid" id="stylePickerGrid">
                            @foreach($styles as $sKey => $sVal)
                                <button type="button"
                                        class="style_chip_btn {{ $currentStyle === $sKey ? 'is-active' : '' }}"
                                        data-pick-style="{{ $sKey }}">
                                    {{ $sVal['name'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Thư Viện Chọn Mẫu Thiệp Trực Tiếp Có Ảnh Thumbnail --}}
                    <div class="ctrl_block">
                        <div class="ctrl_block_head">
                            <span class="ctrl_block_label">Kho mẫu thiệp cao cấp ({{ count($selectableTemplates) }})</span>
                            <span class="ctrl_block_hint">Chạm để áp dụng</span>
                        </div>
                        <div class="template_gallery_grid" id="templateGalleryGrid">
                            @foreach($selectableTemplates as $tplItem)
                                <button type="button"
                                        class="tpl_thumb_card {{ $template->slug === $tplItem['slug'] ? 'is-active' : '' }}"
                                        data-pick-template="{{ $tplItem['slug'] }}"
                                        title="{{ $tplItem['name'] }}">
                                    <div class="tpl_thumb_img_wrap">
                                        <img src="{{ $tplItem['thumbnail_url'] }}" alt="{{ $tplItem['name'] }}" loading="lazy">
                                        @if(!empty($tplItem['recommended']))
                                            <span class="tpl_thumb_badge">Hợp gu</span>
                                        @endif
                                        <span class="tpl_thumb_check" aria-hidden="true">✓</span>
                                    </div>
                                    <span class="tpl_thumb_name">{{ $tplItem['name'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- ================= TAB 2: NỘI DUNG & TRỢ LÝ AI VIẾT LỜI NGỎ ================= --}}
                <div class="sidebar_tab_panel" data-tab-panel="content">
                    <form id="curatedQuickEditForm" class="quick_edit_form" novalidate>
                        <div class="ctrl_block">
                            <div class="ctrl_block_head">
                                <span class="ctrl_block_label">Đôi Uyên Ương &amp; Nghi Lễ</span>
                            </div>
                            <div class="qe_grid_2">
                                <div class="qe_field">
                                    <label for="qeGroomName">Tên Chú rể</label>
                                    <input type="text" id="qeGroomName" name="groom_name" value="{{ $wedding->groom_name }}" maxlength="50" required>
                                </div>
                                <div class="qe_field">
                                    <label for="qeBrideName">Tên Cô dâu</label>
                                    <input type="text" id="qeBrideName" name="bride_name" value="{{ $wedding->bride_name }}" maxlength="50" required>
                                </div>
                            </div>
                            <div class="qe_field">
                                <label for="qeCeremonyType">Loại hình hôn lễ</label>
                                <select id="qeCeremonyType" name="ceremony_type">
                                    @php $curCeremony = $weddingExtra['ceremony_type'] ?? 'Lễ Thành Hôn'; @endphp
                                    @foreach(['Lễ Thành Hôn', 'Lễ Vu Quy', 'Lễ Tân Hôn', 'Tiệc Báo Hỷ'] as $ct)
                                        <option value="{{ $ct }}" {{ $curCeremony === $ct ? 'selected' : '' }}>{{ $ct }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="ctrl_block">
                            <div class="ctrl_block_head">
                                <span class="ctrl_block_label">Thời Gian &amp; Địa Điểm</span>
                            </div>
                            <div class="qe_grid_2">
                                <div class="qe_field">
                                    <label for="qeWeddingDate">Ngày cưới</label>
                                    <input type="date" id="qeWeddingDate" name="wedding_date" value="{{ $weddingDateVal }}">
                                </div>
                                <div class="qe_field">
                                    <label for="qePartyTime">Giờ khai tiệc</label>
                                    <input type="time" id="qePartyTime" name="party_time" value="{{ $weddingExtra['party_time'] ?? '18:00' }}">
                                </div>
                            </div>
                            <div class="qe_field">
                                <label for="qeVenueName">Tên trung tâm / Sảnh tiệc</label>
                                <input type="text" id="qeVenueName" name="venue_name" value="{{ $wedding->venue_name ?? ($weddingExtra['party_venue'] ?? '') }}" maxlength="120" placeholder="Ví dụ: GEM Center">
                            </div>
                            <div class="qe_field">
                                <label for="qeVenueAddress">Địa chỉ chi tiết</label>
                                <input type="text" id="qeVenueAddress" name="venue_address" value="{{ $wedding->venue_address ?? ($weddingExtra['event_party_address'] ?? '') }}" maxlength="255" placeholder="Số nhà, đường, quận, thành phố">
                            </div>
                        </div>

                        <div class="ctrl_block">
                            <div class="ctrl_block_head">
                                <span class="ctrl_block_label">Lời Ngỏ &amp; Trích Dẫn AI</span>
                                <button type="button" class="btn_ai_rewrite" id="btnAiRegenerateCopy">
                                    <span>✦ AI Viết Lại</span>
                                </button>
                            </div>
                            <div class="qe_field">
                                <label for="qeStoryTitle">Tiêu đề chuyện tình</label>
                                <input type="text" id="qeStoryTitle" name="story_title" value="{{ $weddingExtra['story_title'] ?? '' }}" maxlength="100">
                            </div>
                            <div class="qe_field">
                                <label for="qeCoupleQuote">Câu trích dẫn tình yêu</label>
                                <textarea id="qeCoupleQuote" name="couple_quote" rows="2" maxlength="400">{{ $weddingExtra['couple_quote'] ?? '' }}</textarea>
                            </div>
                            <div class="qe_field">
                                <label for="qeStoryContent">Lời ngỏ gửi khách mời</label>
                                <textarea id="qeStoryContent" name="story_content" rows="4" maxlength="1200">{{ $weddingExtra['story_content'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <details class="qe_parents_details">
                            <summary>Chỉnh sửa Song Thân hai họ</summary>
                            <div class="qe_grid_2" style="margin-top: 10px;">
                                <div class="qe_field">
                                    <label for="qeGroomFather">Thân phụ Chú rể</label>
                                    <input type="text" id="qeGroomFather" name="groom_father" value="{{ $weddingExtra['groom_father'] ?? '' }}" maxlength="60">
                                </div>
                                <div class="qe_field">
                                    <label for="qeGroomMother">Thân mẫu Chú rể</label>
                                    <input type="text" id="qeGroomMother" name="groom_mother" value="{{ $weddingExtra['groom_mother'] ?? '' }}" maxlength="60">
                                </div>
                                <div class="qe_field">
                                    <label for="qeBrideFather">Thân phụ Cô dâu</label>
                                    <input type="text" id="qeBrideFather" name="bride_father" value="{{ $weddingExtra['bride_father'] ?? '' }}" maxlength="60">
                                </div>
                                <div class="qe_field">
                                    <label for="qeBrideMother">Thân mẫu Cô dâu</label>
                                    <input type="text" id="qeBrideMother" name="bride_mother" value="{{ $weddingExtra['bride_mother'] ?? '' }}" maxlength="60">
                                </div>
                            </div>
                        </details>

                        <button type="submit" class="btn_save_quick_edit" id="btnSaveQuickEdit">
                            <span>Lưu &amp; Cập Nhật Thiệp</span>
                        </button>
                    </form>
                </div>

                {{-- ================= TAB 3: MỤC LỤC & LỐI TẮT STUDIO ================= --}}
                <div class="sidebar_tab_panel" data-tab-panel="sections">
                    <p class="sections_intro_hint">Chạm vào từng mục bên dưới để cuộn nhanh trên thiệp xem trước:</p>
                    <div class="sidebar_sections_list">
                        @foreach($sectionDefinitions as $secKey => $secDef)
                            <button type="button" class="section_ctrl_card" data-section="{{ $secKey }}">
                                <div class="section_ctrl_head">
                                    <span class="section_ctrl_num">#0{{ $loop->iteration }}</span>
                                    <h3 class="section_ctrl_title">{{ $secDef['title'] ?? ($secDef['name'] ?? 'Mục') }}</h3>
                                    <span class="section_ctrl_arrow" aria-hidden="true">›</span>
                                </div>
                                <p class="section_ctrl_desc">{{ $secDef['description'] ?? 'Thiết kế chuẩn mực theo mẫu gốc' }}</p>
                            </button>
                        @endforeach
                    </div>

                    <div class="studio_upsell_card">
                        <strong>Muốn tải ảnh cưới thật &amp; đổi nhạc nền?</strong>
                        <p>Mở Studio Chuyên Sâu để thay toàn bộ ảnh bìa, Album 10 ảnh, nhạc cưới, mã VietQR và font chữ.</p>
                        <a href="{{ route('invitation.create', ['edit' => $invitation->slug]) }}" class="btn_open_studio_full">
                            Mở Studio Chuyên Sâu →
                        </a>
                    </div>
                </div>

            </div>
        </aside>

        {{-- LỚP NỀN MỜ CHO MOBILE BOTTOM SHEET --}}
        <div class="mayhen_curated_sidebar_backdrop" id="sidebarBackdrop"></div>

        {{-- KHU VỰC SÂN KHẤU PREVIEW (PREVIEW STAGE) --}}
        <section class="mayhen_curated_stage_area">
            <div class="mayhen_curated_phone_wrapper" id="phoneWrapper">
                <div class="mayhen_curated_phone_bezel">
                    <div class="phone_notch" aria-hidden="true">
                        <span class="phone_speaker"></span>
                        <span class="phone_lens"></span>
                    </div>

                    <div class="phone_screen">
                        <iframe
                            src="{{ route('invitation.show', array_filter(['slug' => $invitation->slug, 'crafting' => !empty($shouldAnimate) ? 1 : null, 'embed' => 1, 'palette' => $currentPalette])) }}"
                            id="curatedPreviewIframe"
                            class="curated_preview_iframe"
                            title="Thiệp Cưới Tự Phối {{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}"
                            allow="autoplay">
                        </iframe>
                    </div>

                    <div class="phone_home_indicator" aria-hidden="true"></div>
                </div>
            </div>
        </section>

    </main>

    {{-- THANH CÔNG CỤ ĐÁY DÀNH RIÊNG CHO ĐIỆN THOẠI (GỌN GÀNG, SẠCH SẼ, DỄ BẤM 1 TAY) --}}
    <nav class="mayhen_curated_mobile_dock" aria-label="Công cụ tùy chỉnh nhanh">
        <button type="button" class="mobile_dock_btn" data-open-sheet-tab="design">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><circle cx="13.5" cy="6.5" r=".5"/><circle cx="17.5" cy="10.5" r=".5"/><circle cx="8.5" cy="7.5" r=".5"/><circle cx="6.5" cy="12.5" r=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.9 0 1.6-.7 1.6-1.6 0-.4-.2-.8-.4-1.1-.3-.3-.4-.7-.4-1.1 0-.9.7-1.6 1.6-1.6H16c3.3 0 6-2.7 6-6 0-4.9-4.5-8.6-10-8.6z"/></svg>
            <span>Giao diện</span>
        </button>
        <button type="button" class="mobile_dock_btn" data-open-sheet-tab="content">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            <span>Sửa &amp; AI</span>
        </button>
        <button type="button" class="mobile_dock_btn" id="btnMobileQuickRemix">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
            <span>Đổi mẫu</span>
        </button>
        <a href="{{ route('invitation.create', ['edit' => $invitation->slug]) }}" class="mobile_dock_btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            <span>Studio</span>
        </a>
    </nav>

    {{-- MODAL XUẤT BẢN & TẠO LINK MỜI CÁ NHÂN HÓA --}}
    <div class="mayhen_curated_modal" id="publishModal" aria-hidden="true">
        <div class="modal_backdrop" id="modalBackdrop"></div>
        <div class="modal_dialog" role="dialog" aria-modal="true" aria-labelledby="publishModalTitle">
            <button type="button" class="modal_close" id="btnClosePublishModal" aria-label="Đóng">&times;</button>
            <div class="modal_header">
                <div class="modal_crest">✦ {{ $curatedBrandNameUpper }} &bull; QUIET LUXURY ✦</div>
                <h2 class="modal_title" id="publishModalTitle">Thiệp Cưới Độc Bản Đã Sẵn Sàng!</h2>
                <p class="modal_desc">Tác phẩm thiệp cưới dành riêng cho <strong id="modalCoupleName">{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}</strong> đã được lưu bền vững vào hệ thống.</p>
            </div>

            <div class="modal_body">
                <div class="share_url_box">
                    <label for="guestPersonalizeInput">Cá nhân hóa tên khách mời (tùy chọn):</label>
                    <input type="text" id="guestPersonalizeInput" class="share_guest_input" placeholder="Ví dụ: Anh Minh & Gia đình (để trống nếu gửi chung)" maxlength="80">
                </div>

                <div class="share_url_box">
                    <label for="publicShareUrl">Đường dẫn gửi thiệp trực tuyến:</label>
                    <div class="share_url_input_wrap">
                        <input type="text" id="publicShareUrl" value="{{ url('/thiep/' . $invitation->slug) }}" readonly>
                        <button type="button" class="btn_copy_url" id="btnCopyShareUrl">
                            <span id="copyBtnText">Sao Chép</span>
                        </button>
                    </div>
                </div>

                <button type="button" class="btn_copy_invite_msg" id="btnCopyInviteMessage">
                    ✦ Sao Chép Lời Mời Kèm Link (Dán Ngay Vào Zalo / Messenger)
                </button>

                <div class="modal_quick_actions">
                    <a href="{{ route('invitation.show', ['slug' => $invitation->slug]) }}" id="btnOpenGuestInvitation" target="_blank" rel="noopener noreferrer" class="modal_btn modal_btn_primary">
                        Mở Thiệp Khách Mời ↗
                    </a>
                    <a href="{{ route('invitation.create', ['edit' => $invitation->slug]) }}" class="modal_btn modal_btn_secondary">
                        Vào Studio Tải Ảnh &amp; Nhạc
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- TOAST THÔNG BÁO MƯỢT MÀ (KHÔNG DÙNG ALERT) --}}
    <div id="curatedToast" class="mayhen_curated_toast" role="status" aria-live="polite"></div>

</div>

<script src="{{ asset_v('assets/client/js/pages/curated-preview.js') }}"></script>
</body>
</html>
