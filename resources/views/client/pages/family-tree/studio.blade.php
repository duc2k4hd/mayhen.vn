@extends('client.layouts.event-studio')

@php
    $brandName = \App\Models\PlatformSetting::getBrandName();
    $themeSlug = $initialTreeData['template_style'] ?? 'hoang-kim-tu-duong';
    $studioReturnUrl = request()->getRequestUri() ?: route('family_tree.studio', [], false);
@endphp

@section('title', 'Studio Tạo Gia Phả Online Miễn Phí | ' . $brandName)
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/family-tree.css') }}">
@endpush

@section('content')
<div class="mayhen_family_tree_workspace mayhen_family_tree_theme_{{ $themeSlug }}"
     id="mayhenFamilyTreeApp"
     data-mode="studio">

    {{-- TOPBAR STUDIO GIA PHẢ --}}
    <header class="mayhen_family_tree_topbar">
        <div class="mayhen_family_tree_topbar_group">
            <a href="{{ route('family_tree.index') }}" class="mayhen_family_tree_top_link">
                &larr; Trang Gia Phả
            </a>
            <div class="mayhen_family_tree_brand_title">
                <strong>Studio Tạo Gia Phả Online</strong>
                <small>Gói hiện tại: {{ $currentPlanLabel }} • Tạo &amp; xuất ảnh miễn phí</small>
            </div>
        </div>

        <div class="mayhen_family_tree_topbar_group">
            @auth
                <a href="{{ route('family_tree.my_trees') }}" class="mayhen_family_tree_top_link">
                    Gia Phả Của Tôi
                </a>
            @endauth
            <button type="button" class="mayhen_family_tree_top_link" id="mayhenFtResetDraftBtn">
                ↺ Nạp lại mẫu gốc
            </button>
            <button type="button" class="mayhen_family_tree_top_cta" id="mayhenFtTopPublishBtn">
                ✦ Tạo Link Thật (Premium)
            </button>
        </div>
    </header>

    <div class="mayhen_family_tree_main_split">
        {{-- SIDEBAR 4 TAB BÊN TRÁI --}}
        <aside class="mayhen_family_tree_sidebar" aria-label="Bảng điều khiển tạo gia phả">
            <div class="mayhen_family_tree_tab_bar">
                <button type="button" class="mayhen_family_tree_tab_btn is-active" data-tab="info">1. Mẫu &amp; Họ</button>
                <button type="button" class="mayhen_family_tree_tab_btn" data-tab="members">2. Thành viên</button>
                <button type="button" class="mayhen_family_tree_tab_btn" data-tab="chronicle">3. Phả ký</button>
                <button type="button" class="mayhen_family_tree_tab_btn" data-tab="publish">4. Link thật</button>
            </div>

            <div class="mayhen_family_tree_tab_body">
                {{-- TAB 1: THÔNG TIN & MẪU GIA PHẢ --}}
                <div class="mayhen_family_tree_tab_panel is-active" data-panel="info">
                    <div class="mayhen_family_tree_field">
                        <label for="mayhenFtTitleInput">Tên cuốn gia phả *</label>
                        <input type="text" id="mayhenFtTitleInput" class="mayhen_family_tree_input" value="{{ $initialTreeData['title'] }}">
                    </div>
                    <div class="mayhen_family_tree_field">
                        <label for="mayhenFtSurnameInput">Tên dòng họ / Chi nhánh</label>
                        <input type="text" id="mayhenFtSurnameInput" class="mayhen_family_tree_input" value="{{ $initialTreeData['family_surname'] }}">
                    </div>
                    <div class="mayhen_family_tree_field">
                        <label for="mayhenFtHallInput">Từ đường / Quê gốc</label>
                        <input type="text" id="mayhenFtHallInput" class="mayhen_family_tree_input" value="{{ $initialTreeData['ancestral_hall'] }}">
                    </div>

                    <div class="mayhen_family_tree_field">
                        <label>Chọn mẫu phong cách phả đồ</label>
                        <div class="mayhen_family_tree_theme_picker">
                            @foreach($templates as $tpl)
                                <button type="button"
                                        class="mayhen_family_tree_theme_chip {{ $themeSlug === $tpl['slug'] ? 'is-active' : '' }}"
                                        data-theme-slug="{{ $tpl['slug'] }}">
                                    <strong>{{ $tpl['name'] }}</strong>
                                    <small>{{ $tpl['badge'] }}</small>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- TAB 2: DANH SÁCH THÀNH VIÊN --}}
                <div class="mayhen_family_tree_tab_panel" data-panel="members">
                    <div class="mayhen_family_tree_member_toolbar">
                        <div class="mayhen_family_tree_member_filter_row">
                            <input type="search"
                                   id="mayhenFtSidebarMemberSearch"
                                   class="mayhen_family_tree_input mayhen_family_tree_member_search_input"
                                   placeholder="Lọc nhanh tên thành viên...">
                            <span class="mayhen_family_tree_member_stats" id="mayhenFtSidebarMemberStats"></span>
                        </div>
                        <button type="button" class="mayhen_family_tree_btn_primary mayhen_family_tree_member_add_btn" id="mayhenFtAddStandaloneBtn">
                            + Thêm Con Cho Nhánh Đang Chọn
                        </button>
                        <div class="mayhen_family_tree_dnd_hint">
                            <span>⠿</span> Kéo vào <strong>nhóm Đời</strong> để chuyển đời • Thả vào <strong>giữa tên</strong> để gán làm Con • Kéo <strong>mép trên/dưới</strong> để đổi thứ tự.
                        </div>
                    </div>
                    <div id="mayhenFtSidebarMemberList" class="mayhen_family_tree_member_groups"></div>
                </div>

                {{-- TAB 3: PHẢ KÝ & TỘC ƯỚC --}}
                <div class="mayhen_family_tree_tab_panel" data-panel="chronicle">
                    <div class="mayhen_family_tree_field">
                        <label for="mayhenFtIntroInput">Lời nói đầu &amp; Phả ký dòng họ</label>
                        <textarea id="mayhenFtIntroInput" class="mayhen_family_tree_textarea" rows="6">{{ $initialTreeData['intro_chronicle'] }}</textarea>
                    </div>
                    <div class="mayhen_family_tree_field">
                        <label for="mayhenFtRulesInput">Tộc ước &amp; Gia huấn</label>
                        <textarea id="mayhenFtRulesInput" class="mayhen_family_tree_textarea" rows="6">{{ $initialTreeData['clan_rules'] }}</textarea>
                    </div>
                </div>

                {{-- TAB 4: XUẤT BẢN LINK THẬT & BẢO MẬT --}}
                <div class="mayhen_family_tree_tab_panel" data-panel="publish">
                    <div style="padding: 12px; border-radius: 12px; background: #FAF5EB; border: 1px solid #CFA860; margin-bottom: 14px;">
                        <strong style="font-size: 13px; color: #85182A;">Quyền tạo link thật: Gói Premium (0đ)</strong>
                        <p style="font-size: 12px; color: #5C5349; margin: 4px 0 0; line-height: 1.55;">
                            @if($canCreateRealLink)
                                Tài khoản của bạn đang ở gói <strong>{{ $currentPlanLabel }}</strong> — đủ điều kiện xuất bản link gia phả thật ngay lập tức.
                            @else
                                Bạn đang dùng thử miễn phí. Hãy đăng nhập tài khoản Mây Hẹn để nhận miễn phí gói <strong>Thành Viên (Premium)</strong> và tạo link chia sẻ thật cho dòng họ.
                            @endif
                        </p>
                    </div>

                    <div class="mayhen_family_tree_field">
                        <label for="mayhenFtVisibilitySelect">Chế độ bảo mật link chia sẻ</label>
                        <select id="mayhenFtVisibilitySelect" class="mayhen_family_tree_select">
                            <option value="public" {{ ($initialTreeData['visibility'] ?? 'public') === 'public' ? 'selected' : '' }}>Ai có link đều xem được (vẫn ẩn khỏi Google)</option>
                            <option value="password" {{ ($initialTreeData['visibility'] ?? '') === 'password' ? 'selected' : '' }}>Khóa bằng mật khẩu dòng họ</option>
                        </select>
                    </div>

                    <div class="mayhen_family_tree_field" id="mayhenFtPasswordWrap" style="{{ ($initialTreeData['visibility'] ?? '') === 'password' ? '' : 'display:none;' }}">
                        <label for="mayhenFtPasswordInput">Mật khẩu mở xem gia phả (tối thiểu 4 ký tự)</label>
                        <input type="password" id="mayhenFtPasswordInput" class="mayhen_family_tree_input" placeholder="Nhập mật khẩu...">
                    </div>

                    <button type="button"
                            class="mayhen_family_tree_btn_primary"
                            id="mayhenFtPublishBtn"
                            data-publish-url="{{ route('family_tree.publish') }}"
                            style="width: 100%;">
                        {{ !empty($editingTree) ? '💾 Cập Nhật Link Gia Phả Thật' : '✦ Xuất Bản & Tạo Link Thật' }}
                    </button>

                    <div id="mayhenFtPublishResult" style="display:none; margin-top: 14px; padding: 12px; border-radius: 12px; background: #F0F7F2; border: 1px solid #96C4A4;"></div>
                </div>
            </div>
        </aside>

        {{-- SÂN KHẤU PHẢ ĐỒ TƯƠNG TÁC --}}
        <section class="mayhen_family_tree_stage_wrap" aria-label="Sân khấu cây gia phả">
            <div class="mayhen_family_tree_toolbar">
                <div class="mayhen_family_tree_toolbar_group">
                    <button type="button" class="mayhen_family_tree_pill_btn is-active" data-layout-btn="vertical">Phả Đồ Đứng</button>
                    <button type="button" class="mayhen_family_tree_pill_btn" data-layout-btn="horizontal">Phả Đồ Ngang</button>
                    <button type="button" class="mayhen_family_tree_pill_btn" data-layout-btn="chronicle">Cuốn Phả Ký</button>
                </div>

                <div class="mayhen_family_tree_toolbar_group">
                    <div class="mayhen_family_tree_search_box">
                        <input type="search" id="mayhenFtSearchInput" class="mayhen_family_tree_search_input" placeholder="Tìm thành viên...">
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
                    <h1 id="mayhenFtCanvasTitle">{{ $initialTreeData['title'] }}</h1>
                    <p id="mayhenFtCanvasSub"></p>
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

{{-- DRAWER CHI TIẾT / CHỈNH SỬA THÀNH VIÊN --}}
<aside class="mayhen_family_tree_drawer" id="mayhenFtDrawer" aria-label="Thông tin thành viên">
    <div class="mayhen_family_tree_drawer_head">
        <strong id="mayhenFtDrawerTitle">Thành viên</strong>
        <button type="button" class="mayhen_family_tree_pill_btn" id="mayhenFtDrawerClose">Đóng ✕</button>
    </div>
    <div class="mayhen_family_tree_drawer_body" id="mayhenFtDrawerBody"></div>
</aside>

{{-- MODAL CÔNG CỤ LỊCH GIỖ & XƯNG HÔ --}}
<div class="mayhen_family_tree_modal_backdrop" id="mayhenFtToolModal">
    <div class="mayhen_family_tree_modal">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
            <h2 id="mayhenFtToolModalTitle" style="margin:0; font-size:19px; color:#85182A;">Công cụ gia phả</h2>
            <button type="button" class="mayhen_family_tree_pill_btn" id="mayhenFtToolModalClose">✕</button>
        </div>
        <div id="mayhenFtToolModalBody"></div>
    </div>
</div>

{{-- MODAL YÊU CẦU ĐĂNG NHẬP GÓI PREMIUM KHI TẠO LINK THẬT --}}
<div class="mayhen_family_tree_modal_backdrop" id="mayhenFtPremiumModal">
    <div class="mayhen_family_tree_modal" style="text-align:center;">
        <span class="mayhen_family_tree_kicker">Đặc quyền Thành Viên (Premium 0đ)</span>
        <h2 style="font-family:'MayhenV3EditorialSerif', Georgia, serif; margin:8px 0 10px; color:#1C1917;">
            Đăng Nhập Để Tạo Link Gia Phả Thật
        </h2>
        <p style="font-size:14px; line-height:1.65; color:#544D45; margin:0 0 12px;">
            Bạn vẫn có thể vẽ, lưu nháp trên máy và xuất ảnh PNG hoàn toàn miễn phí.
            Để xuất bản đường dẫn chia sẻ online cho dòng họ (kèm bảo mật mật khẩu), hãy đăng nhập hoặc tạo tài khoản Mây Hẹn để nhận ngay <strong>gói Thành Viên Premium miễn phí</strong>!
        </p>
        <p style="font-size:13px; line-height:1.55; color:#1E4D3B; background:#F0F7F2; border:1px solid #B7D8C2; border-radius:10px; padding:10px 12px; margin:0 0 18px;">
            ✦ Toàn bộ cây gia phả và thông tin bạn vừa nhập đã được <strong>tự động lưu an toàn</strong>. Sau khi đăng nhập hoặc đăng ký xong, hệ thống sẽ đưa bạn quay lại đúng trang này và giữ nguyên 100% dữ liệu.
        </p>
        <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
            <a href="{{ route('login', ['redirect' => $studioReturnUrl]) }}" id="mayhenFtModalLoginLink" class="mayhen_family_tree_btn_primary">
                Đăng Nhập Ngay &rarr;
            </a>
            <a href="{{ route('register', ['redirect' => $studioReturnUrl]) }}" id="mayhenFtModalRegisterLink" class="mayhen_family_tree_btn_gold">
                Đăng Ký Miễn Phí (Gói Premium)
            </a>
            <button type="button" class="mayhen_family_tree_btn_outline" id="mayhenFtPremiumModalClose">
                Tiếp Tục Dùng Thử
            </button>
        </div>
    </div>
</div>

<div class="mayhen_family_tree_toast" id="mayhenFtToast"></div>

<script id="mayhenFamilyTreeBootstrap" type="application/json">@json($initialTreeData, JSON_UNESCAPED_UNICODE)</script>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/family-tree.js') }}"></script>
@endpush
