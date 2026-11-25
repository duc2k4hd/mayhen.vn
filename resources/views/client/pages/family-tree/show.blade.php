@extends('client.layouts.event-studio')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $themeSlug = $treeData['template_style'] ?? 'hoang-kim-tu-duong';
@endphp

@section('title', ($treeData['title'] ?? 'Gia Phả Online') . ' | ' . $brandName)
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/family-tree.css') }}">
@endpush

@section('content')
@if(!empty($isLocked))
    <div class="mayhen_family_tree_gate_page">
        <div class="mayhen_family_tree_gate_card">
            <span class="mayhen_family_tree_kicker">🔒 Gia Phả Riêng Tư</span>
            <h1 style="font-family:'MayhenV3EditorialSerif', Georgia, serif; font-size:24px; margin:8px 0 8px;">
                {{ $treeData['title'] }}
            </h1>
            <p style="font-size:14px; color:#5C5349; line-height:1.6; margin:0 0 18px;">
                Gia phả này được chủ dòng họ bảo vệ bằng mật khẩu riêng. Vui lòng nhập mật khẩu để xem phả đồ và lịch giỗ.
            </p>
            @if($errors->has('password'))
                <p style="color:#B91C1C; font-size:13px; font-weight:600; margin-bottom:12px;">
                    {{ $errors->first('password') }}
                </p>
            @endif
            <form method="POST" action="{{ route('family_tree.unlock', $treeData['slug']) }}">
                @csrf
                <div class="mayhen_family_tree_field">
                    <input type="password" name="password" class="mayhen_family_tree_input" placeholder="Nhập mật khẩu gia phả..." required>
                </div>
                <button type="submit" class="mayhen_family_tree_btn_primary" style="width:100%;">
                    Mở Xem Gia Phả &rarr;
                </button>
            </form>
        </div>
    </div>
@else
    <div class="mayhen_family_tree_workspace mayhen_family_tree_theme_{{ $themeSlug }}"
         id="mayhenFamilyTreeApp"
         data-mode="viewer">

        <header class="mayhen_family_tree_topbar">
            <div class="mayhen_family_tree_topbar_group">
                <a href="{{ route('family_tree.index') }}" class="mayhen_family_tree_top_link">
                    &larr; {{ $brandName }} Gia Phả
                </a>
                <div class="mayhen_family_tree_brand_title">
                    <strong>{{ $treeData['title'] }}</strong>
                    <small>{{ $stats['total_members'] }} thành viên • {{ $stats['generations_count'] }} đời • {{ $stats['memorial_count'] }} ngày giỗ Âm lịch</small>
                </div>
            </div>

            <div class="mayhen_family_tree_topbar_group">
                @if(!empty($isTemplatePreview))
                    <a href="{{ route('family_tree.studio', ['mau' => $themeSlug]) }}" class="mayhen_family_tree_top_cta">
                        ✦ Dùng Mẫu Này Tạo Gia Phả &rarr;
                    </a>
                @else
                    <a href="{{ route('family_tree.studio') }}" class="mayhen_family_tree_top_cta">
                        ✦ Tạo Gia Phả Cho Họ Nhà Bạn
                    </a>
                @endif
            </div>
        </header>

        <div class="mayhen_family_tree_main_split">
            <section class="mayhen_family_tree_stage_wrap" aria-label="Cây gia phả trực tuyến">
                <div class="mayhen_family_tree_toolbar">
                    <div class="mayhen_family_tree_toolbar_group">
                        <button type="button" class="mayhen_family_tree_pill_btn is-active" data-layout-btn="vertical">Phả Đồ Đứng</button>
                        <button type="button" class="mayhen_family_tree_pill_btn" data-layout-btn="horizontal">Phả Đồ Ngang</button>
                        <button type="button" class="mayhen_family_tree_pill_btn" data-layout-btn="chronicle">Cuốn Phả Ký</button>
                    </div>

                    <div class="mayhen_family_tree_toolbar_group">
                        <div class="mayhen_family_tree_search_box">
                            <input type="search" id="mayhenFtSearchInput" class="mayhen_family_tree_search_input" placeholder="Tìm tên người thân...">
                            <div id="mayhenFtSearchResults" class="mayhen_family_tree_search_results"></div>
                        </div>
                        <button type="button" class="mayhen_family_tree_pill_btn" id="mayhenFtBtnAnniversary">🕯️ Lịch Giỗ</button>
                        <button type="button" class="mayhen_family_tree_pill_btn" id="mayhenFtBtnKinship">🧭 Xưng Hô</button>
                        <button type="button" class="mayhen_family_tree_pill_btn" id="mayhenFtExportPng">⬇ Xuất PNG</button>
                        <button type="button" class="mayhen_family_tree_pill_btn" id="mayhenFtPrintBtn">🖨 In</button>
                    </div>
                </div>

                <div class="mayhen_family_tree_canvas_viewport" id="mayhenFtViewport">
                    <div class="mayhen_family_tree_canvas_header">
                        <h1 id="mayhenFtCanvasTitle">{{ $treeData['title'] }}</h1>
                        <p id="mayhenFtCanvasSub">{{ $treeData['ancestral_hall'] }}</p>
                    </div>

                    <div class="mayhen_family_tree_world" id="mayhenFtWorld">
                        <svg class="mayhen_family_tree_svg_lines" id="mayhenFtSvgLines" width="4000" height="3000"></svg>
                        <div id="mayhenFtNodes"></div>
                    </div>

                    <div class="mayhen_family_tree_zoom_dock">
                        <button type="button" id="mayhenFtZoomOut" title="Thu nhỏ">−</button>
                        <span id="mayhenFtZoomLabel" style="font-size:12px; font-weight:700;">100%</span>
                        <button type="button" id="mayhenFtZoomIn" title="Phóng to">+</button>
                        <button type="button" id="mayhenFtZoomFit" title="Vừa màn hình">Vừa khung</button>
                    </div>
                </div>

                <div class="mayhen_family_tree_chronicle_view" id="mayhenFtChronicleView"></div>
            </section>
        </div>
    </div>

    <aside class="mayhen_family_tree_drawer" id="mayhenFtDrawer" aria-label="Hồ sơ thành viên">
        <div class="mayhen_family_tree_drawer_head">
            <strong id="mayhenFtDrawerTitle">Hồ sơ</strong>
            <button type="button" class="mayhen_family_tree_pill_btn" id="mayhenFtDrawerClose">Đóng ✕</button>
        </div>
        <div class="mayhen_family_tree_drawer_body" id="mayhenFtDrawerBody"></div>
    </aside>

    <div class="mayhen_family_tree_modal_backdrop" id="mayhenFtToolModal">
        <div class="mayhen_family_tree_modal">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
                <h2 id="mayhenFtToolModalTitle" style="margin:0; font-size:19px; color:#85182A;">Công cụ</h2>
                <button type="button" class="mayhen_family_tree_pill_btn" id="mayhenFtToolModalClose">✕</button>
            </div>
            <div id="mayhenFtToolModalBody"></div>
        </div>
    </div>

    <div class="mayhen_family_tree_toast" id="mayhenFtToast"></div>

    <script id="mayhenFamilyTreeBootstrap" type="application/json">@json($treeData, JSON_UNESCAPED_UNICODE)</script>
@endif
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/family-tree.js') }}"></script>
@endpush
