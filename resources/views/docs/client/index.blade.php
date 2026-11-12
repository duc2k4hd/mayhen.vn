@extends('docs.layout')

@php
  $space = $space ?? 'client';
  $isAdmin = $space === 'admin';
  $indexRoute = $isAdmin ? route('admin.docs.index') : route('docs.index');
  $labelSmall = $isAdmin ? 'ADMIN KNOWLEDGE BASE' : 'TÀI LIỆU HƯỚNG DẪN';
  $heroEyebrow = $isAdmin ? 'QUẢN TRỊ & BÀN GIAO VẬN HÀNH' : 'TRUNG TÂM TRỢ GIÚP MÂY HẸN';
  $heroTitle   = $isAdmin ? 'Sổ tay Quy trình Vận hành Tiêu chuẩn (Admin SOP)' : 'Chúng tôi có thể giúp gì cho ngày cưới của bạn?';
  $heroDesc    = $isAdmin
    ? 'Tài liệu bàn giao toàn diện dành cho nhân sự mới, quản lý, kế toán và đội ngũ hỗ trợ kỹ thuật Mây Hẹn.'
    : 'Hướng dẫn chi tiết từng bước tạo thiệp lãng mạn, quản lý khách mời thông minh và thanh toán an toàn.';

  // Chips for index
  $chips = $isAdmin
    ? ['#DuyệtHoànTiền', '#KíchHoạtSePay', '#KhóaThiệpViPhạm', '#ChẩnĐoánAsset', '#SựCốLễTânP0']
    : ['#TạoThiệpLầnĐầu', '#ThayẢnhCưới', '#XácNhậnRSVP', '#SơĐồBànTiệc', '#SePayVietQR'];

  // Build path cards from first 3 categories
  $catArray = array_values((array)$categories);
  $pathCards = array_slice($catArray, 0, 3);
@endphp

@section('body')
<div class="mayhen_docs_app" id="mayhen_docs_app" data-space="{{ $space }}" data-theme="light" data-width="comfortable" data-motion="on">

  {{-- SVG Icon Sprite --}}
  <svg class="mayhen_docs_svg_library" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
    <symbol id="mayhen_docs_i_menu" viewBox="0 0 24 24"><path d="M4 7h16M4 12h16M4 17h16"/></symbol>
    <symbol id="mayhen_docs_i_x" viewBox="0 0 24 24"><path d="M5 5l14 14M19 5 5 19"/></symbol>
    <symbol id="mayhen_docs_i_search" viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="6.7"/><path d="m16 16 4.7 4.7"/></symbol>
    <symbol id="mayhen_docs_i_arrow" viewBox="0 0 24 24"><path d="M4 12h16m-6-6 6 6-6 6"/></symbol>
    <symbol id="mayhen_docs_i_chevron" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></symbol>
    <symbol id="mayhen_docs_i_book" viewBox="0 0 24 24"><path d="M12 6.5C9.5 4.7 6 4 3 4v14c3 0 6.5.7 9 2.5C14.5 18.7 18 18 21 18V4c-3 0-6.5.7-9 2.5Zm0 0v14"/></symbol>
    <symbol id="mayhen_docs_i_heart" viewBox="0 0 24 24"><path d="M20.5 8.5c0 4.4-8.5 10.5-8.5 10.5S3.5 12.9 3.5 8.5a4.5 4.5 0 0 1 8.5-2 4.5 4.5 0 0 1 8.5 2Z"/></symbol>
    <symbol id="mayhen_docs_i_sparkle" viewBox="0 0 24 24"><path d="m12 2 2.4 7.6L22 12l-7.6 2.4L12 22l-2.4-7.6L2 12l7.6-2.4L12 2Z"/></symbol>
    <symbol id="mayhen_docs_i_edit" viewBox="0 0 24 24"><path d="M4 20h4l12-12-4-4L4 16v4ZM14 6l4 4"/></symbol>
    <symbol id="mayhen_docs_i_users" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2M16.5 5.5a3 3 0 0 1 0 5.5M17 14a5 5 0 0 1 4 5"/></symbol>
    <symbol id="mayhen_docs_i_send" viewBox="0 0 24 24"><path d="m21 3-7 18-3.7-7.3L3 10 21 3ZM10.3 13.7 21 3"/></symbol>
    <symbol id="mayhen_docs_i_mail" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></symbol>
    <symbol id="mayhen_docs_i_image" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8" cy="9" r="1.5"/><path d="m4 18 5-5 3 3 3-4 5 6"/></symbol>
    <symbol id="mayhen_docs_i_calendar" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M7 3v4M17 3v4M3 10h18"/></symbol>
    <symbol id="mayhen_docs_i_check" viewBox="0 0 24 24"><path d="m4 12 5 5L20 6"/></symbol>
    <symbol id="mayhen_docs_i_copy" viewBox="0 0 24 24"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></symbol>
    <symbol id="mayhen_docs_i_settings" viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/><circle cx="9" cy="6" r="2"/><circle cx="16" cy="12" r="2"/><circle cx="11" cy="18" r="2"/></symbol>
    <symbol id="mayhen_docs_i_sun" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2M4.9 4.9l1.5 1.5m11.2 11.2 1.5 1.5M19.1 4.9l-1.5 1.5M6.4 17.6l-1.5 1.5"/></symbol>
    <symbol id="mayhen_docs_i_moon" viewBox="0 0 24 24"><path d="M20.5 15.6A8.8 8.8 0 0 1 8.4 3.5a8.8 8.8 0 1 0 12.1 12.1Z"/></symbol>
    <symbol id="mayhen_docs_i_bookmark" viewBox="0 0 24 24"><path d="M6 4h12v17l-6-4-6 4V4Z"/></symbol>
    <symbol id="mayhen_docs_i_help" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9.6 9.3a2.8 2.8 0 1 1 4.4 2.3C12.5 12.7 12 13 12 15m0 2.7v.3"/></symbol>
    <symbol id="mayhen_docs_i_link" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.1 0l2-2A5 5 0 0 0 12 3.9l-1.6 1.6m3.6 5.5a5 5 0 0 0-7.1 0l-2 2A5 5 0 0 0 12 20.1l1.6-1.6"/></symbol>
    <symbol id="mayhen_docs_i_left" viewBox="0 0 24 24"><path d="m15 5-7 7 7 7"/></symbol>
    <symbol id="mayhen_docs_i_right" viewBox="0 0 24 24"><path d="m9 5 7 7-7 7"/></symbol>
    <symbol id="mayhen_docs_i_printer" viewBox="0 0 24 24"><path d="M6 8V3h12v5M6 17H4V9h16v8h-2M6 14h12v7H6v-7Z"/></symbol>
    <symbol id="mayhen_docs_i_clock" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></symbol>
    <symbol id="mayhen_docs_i_star" viewBox="0 0 24 24"><path d="m12 2 3.2 6.5 7.2 1-5.2 5 1.2 7.1L12 18.2l-6.4 3.4 1.2-7.1-5.2-5 7.2-1L12 2Z"/></symbol>
  </svg>

  <a class="mayhen_docs_skip" href="#mayhen_docs_main">Chuyển đến nội dung chính</a>
  <div class="mayhen_docs_progress" id="mayhen_docs_progress" aria-hidden="true"></div>

  {{-- ============================================================
       HEADER (2-tier: brand bar + subnav bar)
  ============================================================ --}}
  <header class="mayhen_docs_header">
    <div class="mayhen_docs_header_inner">
      <button class="mayhen_docs_icon_button mayhen_docs_mobile_menu_button" id="mayhen_docs_menu_button" type="button" aria-label="Mở danh mục hướng dẫn" aria-controls="mayhen_docs_sidebar" aria-expanded="false">
        <svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_menu"/></svg>
      </button>
      <a class="mayhen_docs_brand" href="{{ $indexRoute }}" aria-label="Mây Hẹn Docs, về đầu trang">
        <span class="mayhen_docs_brand_mark" aria-hidden="true">
          <svg viewBox="0 0 50 50"><circle cx="25" cy="25" r="23"/><path d="M9 31c7-4 9-14 16-14s9 10 16 14M15 34c5-3 6-9 10-9s5 6 10 9"/><path d="M25 14c-3.5-5-10-1-7 4l7 5 7-5c3-5-3.5-9-7-4Z"/></svg>
        </span>
        <span class="mayhen_docs_brand_text"><strong>Mây Hẹn</strong><small>{{ $labelSmall }}</small></span>
      </a>
      <span class="mayhen_docs_header_rule" aria-hidden="true"></span>
      <button type="button" class="mayhen_docs_search_top" id="mayhen_docs_search_trigger" aria-label="Tìm kiếm tài liệu">
        <svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_search"/></svg>
        <span>Tìm kiếm hướng dẫn...</span>
        <kbd>Ctrl K</kbd>
      </button>
      <nav class="mayhen_docs_header_links" aria-label="Liên kết nhanh">
        <a href="{{ route('home') }}">Trang chủ mayhen.vn</a>
        <a href="{{ route('templates.index') }}">Mẫu thiệp</a>
        <a href="{{ route('pricing') }}">Bảng giá</a>
        @if($isAdmin)
          <a href="{{ route('admin.dashboard') }}" class="mayhen_docs_header_login">Vào Admin Portal</a>
        @else
          @if(auth()->check() && auth()->user()->isAdmin())
            <a href="{{ route('admin.docs.index') }}" class="mayhen_docs_header_login">Sang Admin</a>
          @endif
          <a class="mayhen_docs_header_signup" href="{{ route('invitation.create') }}">Tạo thiệp ngay <svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_arrow"/></svg></a>
        @endif
      </nav>
      <button type="button" class="mayhen_docs_icon_button mayhen_docs_mobile_search_button" id="mayhen_docs_mobile_search" aria-label="Tìm kiếm">
        <svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_search"/></svg>
      </button>
    </div>
    <div class="mayhen_docs_subheader">
      <nav class="mayhen_docs_subnav" aria-label="Chuyên mục chính">
        <a class="mayhen_docs_subnav_active" href="{{ $indexRoute }}">
          <svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_book"/></svg> Tài liệu hướng dẫn
        </a>
        @foreach(array_slice((array)$categories, 0, 3, true) as $slug => $cat)
          <a href="{{ ($isAdmin ? route('admin.docs.show', [$slug, $cat['articles'][0]['slug'] ?? '']) : route('docs.show', [$slug, $cat['articles'][0]['slug'] ?? ''])) }}">
            <svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_edit"/></svg> {{ $cat['name'] }}
          </a>
        @endforeach
      </nav>
    </div>
  </header>

  {{-- Overlay scrim for mobile sidebar --}}
  <div class="mayhen_docs_scrim" id="mayhen_docs_scrim" hidden></div>

  {{-- ============================================================
       BODY LAYOUT: Sidebar + Main
  ============================================================ --}}
  <div class="mayhen_docs_body_layout">

    {{-- ---- SIDEBAR ---- --}}
    <aside class="mayhen_docs_sidebar" id="mayhen_docs_sidebar" aria-label="Danh mục tài liệu">
      <div class="mayhen_docs_sidebar_top">
        <span>Tài liệu Mây Hẹn</span>
        <button type="button" class="mayhen_docs_icon_button mayhen_docs_sidebar_close" id="mayhen_docs_sidebar_close" aria-label="Đóng menu">
          <svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_x"/></svg>
        </button>
      </div>
      <nav class="mayhen_docs_side_nav" aria-label="Danh mục hướng dẫn">
        <a class="mayhen_docs_sidebar_home mayhen_docs_side_active" href="{{ $indexRoute }}">
          <svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_heart"/></svg> Tổng quan hướng dẫn
        </a>
        @foreach((array)$categories as $catSlug => $cat)
          <div class="mayhen_docs_sidebar_section">
            <span>{{ strtoupper($cat['name']) }}</span>
            @if(!empty($cat['articles']))
              <button type="button" class="mayhen_docs_side_group" data-group="{{ $catSlug }}" aria-expanded="false">
                {{ $cat['name'] }} <svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_chevron"/></svg>
              </button>
              <div class="mayhen_docs_side_children mayhen_docs_group_closed" id="mayhen_docs_group_{{ $catSlug }}">
                @foreach(array_slice($cat['articles'], 0, 6) as $art)
                  <a href="{{ $art['url'] }}" class="mayhen_docs_side_link">{{ $art['title'] }}</a>
                @endforeach
              </div>
            @endif
          </div>
        @endforeach
      </nav>
      <div class="mayhen_docs_sidebar_bottom">
        <div class="mayhen_docs_side_support">
          <span class="mayhen_docs_side_support_icon"><svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_help"/></svg></span>
          <div><strong>Đang cần giúp đỡ?</strong><p>Chúng mình luôn ở đây.</p></div>
          <a href="{{ route('contact') }}" aria-label="Liên hệ hỗ trợ"><svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_arrow"/></svg></a>
        </div>
      </div>
    </aside>

    {{-- ---- MAIN CONTENT ---- --}}
    <main class="mayhen_docs_main" id="mayhen_docs_main" tabindex="-1">
      <div class="mayhen_docs_content">

        {{-- HERO SECTION --}}
        <section class="mayhen_docs_hero" aria-labelledby="mayhen_docs_hero_title">
          <div class="mayhen_docs_hero_art" aria-hidden="true">
            <svg viewBox="0 0 520 310" class="mayhen_docs_hero_ribbons" xmlns="http://www.w3.org/2000/svg">
              <defs>
                <linearGradient id="mayhen_docs_ribbon_gradient" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#f4d4c1"/><stop offset=".52" stop-color="#e6aa9e"/><stop offset="1" stop-color="#bd7d76"/></linearGradient>
                <linearGradient id="mayhen_docs_ribbon_shine" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#fff5eb"/><stop offset="1" stop-color="#e1ae91"/></linearGradient>
              </defs>
              <path d="M426-120C217-84 285 65 412 68c146 4 132-98 6-56C297 52 295 205 458 199c124-4 191-159 121-278" fill="none" stroke="url(#mayhen_docs_ribbon_gradient)" stroke-width="58" stroke-linecap="round"/>
              <path d="M426-120C217-84 285 65 412 68c146 4 132-98 6-56C297 52 295 205 458 199c124-4 191-159 121-278" fill="none" stroke="url(#mayhen_docs_ribbon_shine)" stroke-opacity=".66" stroke-width="5"/>
              <path d="M340 181c19-8 28-28 27-52m0 0c-11 3-17 10-19 22m19-22c11 1 19 9 21 19M388 197c19-4 31-19 38-37m0 0c-12-1-20 4-26 14m26-14c12 5 16 13 16 23" fill="none" stroke="#af8675" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <div class="mayhen_docs_breadcrumb">
            <svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_book"/></svg>
            <span>Tài liệu hướng dẫn</span>
            <span class="mayhen_docs_breadcrumb_slash">/</span>
            <strong>Chào mừng bạn</strong>
          </div>
          <div class="mayhen_docs_hero_eyebrow"><span class="mayhen_docs_flower_dot">✻</span> {{ $heroEyebrow }}</div>
          <h1 id="mayhen_docs_hero_title">{{ $heroTitle }}</h1>
          <p class="mayhen_docs_hero_desc">{{ $heroDesc }}</p>
          <button type="button" class="mayhen_docs_hero_search" id="mayhen_docs_hero_search_btn">
            <span class="mayhen_docs_hero_search_icon"><svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_search"/></svg></span>
            <span>Bạn muốn tìm hiểu điều gì hôm nay?</span>
            <span class="mayhen_docs_hero_search_arrow"><svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_arrow"/></svg></span>
          </button>
          <div class="mayhen_docs_hero_chips">
            @foreach($chips as $chip)
              <button type="button" class="mayhen_docs_chip_primary">{{ $chip }}</button>
            @endforeach
          </div>
        </section>

        {{-- PATH CARDS (first 3 categories) --}}
        <section class="mayhen_docs_section" id="mayhen_docs_getstarted" aria-labelledby="mayhen_docs_getstarted_title">
          <div class="mayhen_docs_section_heading"><div>
            <span class="mayhen_docs_eyebrow">ĐIỂM BẮT ĐẦU CỦA BẠN</span>
            <h2 id="mayhen_docs_getstarted_title">Bạn muốn bắt đầu từ đâu?</h2>
            <p>Chọn chuyên mục phù hợp nhất với bạn.</p>
          </div></div>
          <div class="mayhen_docs_path_grid">
            @php
              $pathIcons = ['mayhen_docs_i_edit', 'mayhen_docs_i_users', 'mayhen_docs_i_send'];
              $pathNums  = ['01 / BẮT ĐẦU', '02 / QUẢN LÝ', '03 / CHIA SẺ'];
              $i = 0;
            @endphp
            @foreach(array_slice((array)$categories, 0, 3, true) as $catSlug => $cat)
              <article class="mayhen_docs_path_card">
                <span class="mayhen_docs_path_icon"><svg class="mayhen_docs_icon"><use href="#{{ $pathIcons[$i] ?? 'mayhen_docs_i_book' }}"/></svg></span>
                <span class="mayhen_docs_path_number">{{ $pathNums[$i] ?? sprintf('%02d / CHUYÊN MỤC', $i + 1) }}</span>
                <h3>{{ $cat['name'] }}</h3>
                <p>{{ $cat['description'] ?? 'Khám phá các hướng dẫn trong chuyên mục này.' }}</p>
                <div class="mayhen_docs_path_links">
                  @foreach(array_slice($cat['articles'], 0, 3) as $art)
                    <a href="{{ $art['url'] }}">{{ $art['title'] }} <svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_arrow"/></svg></a>
                  @endforeach
                </div>
              </article>
              @php $i++; @endphp
            @endforeach
          </div>
        </section>

        {{-- CATEGORIES OVERVIEW (all categories) --}}
        <section class="mayhen_docs_section mayhen_docs_popular_section" id="mayhen_docs_discover" aria-labelledby="mayhen_docs_popular_title">
          <div class="mayhen_docs_split_heading">
            <div>
              <span class="mayhen_docs_eyebrow">TẤT CẢ CHUYÊN MỤC</span>
              <h2 id="mayhen_docs_popular_title">Toàn bộ hướng dẫn Mây Hẹn</h2>
            </div>
          </div>
          <div class="mayhen_docs_popular_list">
            @php $num = 1; @endphp
            @foreach((array)$categories as $catSlug => $cat)
              @if(!empty($cat['articles']))
                <a href="{{ $cat['articles'][0]['url'] }}" class="mayhen_docs_popular_list_item">
                  <span class="mayhen_docs_popular_list_number">{{ sprintf('%02d', $num++) }}</span>
                  <span>
                    <strong>{{ $cat['name'] }}</strong>
                    <small>{{ count($cat['articles']) }} bài hướng dẫn</small>
                  </span>
                  <svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_arrow"/></svg>
                </a>
              @endif
            @endforeach
          </div>
        </section>

        {{-- FINAL CTA --}}
        <section class="mayhen_docs_final_cta" aria-label="Kết thúc hướng dẫn">
          <div class="mayhen_docs_final_ornament" aria-hidden="true">✻</div>
          <span class="mayhen_docs_eyebrow">{{ $isAdmin ? 'VẬN HÀNH HỆ THỐNG' : 'GỬI MỘT LỜI THƯƠNG' }}</span>
          <h2>{{ $isAdmin ? 'Hệ thống vận hành đúng quy trình, mọi đám cưới diễn ra suôn sẻ.' : 'Sẵn sàng viết nên lời hẹn của riêng bạn?' }}</h2>
          <p>{{ $isAdmin ? 'Mọi thắc mắc về quy trình vận hành, liên hệ trực tiếp đội ngũ kỹ thuật.' : 'Một tấm thiệp nhỏ, một kỷ niệm thật lớn. Cứ bắt đầu từ điều bạn yêu thích nhất.' }}</p>
          <div>
            @if($isAdmin)
              <a href="{{ route('admin.dashboard') }}" class="mayhen_docs_cta_main">Vào Admin Portal <svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_arrow"/></svg></a>
              <a href="{{ route('contact') }}" class="mayhen_docs_cta_outline">Liên hệ kỹ thuật</a>
            @else
              <a href="{{ route('invitation.create') }}" class="mayhen_docs_cta_main">Bắt đầu tạo thiệp <svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_arrow"/></svg></a>
              <a href="{{ route('contact') }}" class="mayhen_docs_cta_outline">Mình cần tư vấn</a>
            @endif
          </div>
        </section>

        <div class="mayhen_docs_article_foot">
          <span><svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_clock"/></svg> Trang hướng dẫn chính thức của Mây Hẹn</span>
        </div>

      </div>{{-- .mayhen_docs_content --}}
    </main>
  </div>{{-- .mayhen_docs_body_layout --}}

  {{-- FOOTER --}}
  <footer class="mayhen_docs_footer">
    <div class="mayhen_docs_footer_inner">
      <div class="mayhen_docs_footer_identity">
        <a href="{{ $indexRoute }}" class="mayhen_docs_footer_logo">✻ Mây Hẹn</a>
        <p>Từng chi tiết nhỏ, một ngày thật đáng nhớ.<br>Gửi lời thương theo cách của bạn.</p>
      </div>
      <div>
        <h3>Khám phá</h3>
        <a href="{{ $indexRoute }}">Tổng quan</a>
        @foreach(array_slice((array)$categories, 0, 4, true) as $catSlug => $cat)
          @if(!empty($cat['articles']))
            <a href="{{ $cat['articles'][0]['url'] }}">{{ $cat['name'] }}</a>
          @endif
        @endforeach
      </div>
      <div>
        <h3>Hỗ trợ</h3>
        <a href="{{ route('contact') }}">Liên hệ</a>
        @if(!$isAdmin)
          <a href="{{ route('invitation.create') }}">Tạo thiệp</a>
          <a href="{{ route('pricing') }}">Bảng giá</a>
        @endif
      </div>
      <div>
        <h3>Mây Hẹn</h3>
        <a href="{{ route('home') }}">Trang chủ</a>
        <a href="{{ route('templates.index') }}">Mẫu thiệp đẹp</a>
      </div>
    </div>
    <div class="mayhen_docs_footer_bottom">
      <span>© {{ date('Y') }} Mây Hẹn · Made with love ♡</span>
      <div>
        <a href="{{ $indexRoute }}">Lên đầu trang ↑</a>
      </div>
    </div>
  </footer>

  {{-- Search Modal Dialog --}}
  <dialog class="mayhen_docs_dialog mayhen_docs_dialog_search" id="mayhen_docs_dialog_search" aria-labelledby="mayhen_docs_search_title">
    <div class="mayhen_docs_dialog_header">
      <div><span class="mayhen_docs_modal_eyebrow">TÌM ĐIỀU BẠN CẦN</span><h2 id="mayhen_docs_search_title">Tìm kiếm hướng dẫn</h2></div>
      <button type="button" class="mayhen_docs_dialog_close" data-close aria-label="Đóng"><svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_x"/></svg></button>
    </div>
    <div class="mayhen_docs_search_input_wrap">
      <svg class="mayhen_docs_icon"><use href="#mayhen_docs_i_search"/></svg>
      <input id="mayhen_docs_search_input" type="search" placeholder="Ví dụ: RSVP, album cưới, thiết kế thiệp..." autocomplete="off" aria-label="Nhập từ khóa tìm kiếm">
      <kbd>Esc</kbd>
    </div>
    <div class="mayhen_docs_modal_results" id="mayhen_docs_search_results" role="listbox" aria-label="Kết quả tìm kiếm"></div>
    <div class="mayhen_docs_dialog_footer">
      <span>↑ ↓ Chọn kết quả · Enter Mở</span>
    </div>
  </dialog>

  {{-- Search Data for JS --}}
  <script type="application/json" id="mayhen_docs_search_data">
    {!! json_encode(collect((array)$categories)->flatMap(fn($cat) => collect($cat['articles'] ?? [])->map(fn($art) => [
      'title' => $art['title'],
      'url'   => $art['url'],
      'category' => $cat['name'],
    ]))->values()) !!}
  </script>

  <div class="mayhen_docs_toast" id="mayhen_docs_toast" aria-live="polite" role="status"></div>
  <button class="mayhen_docs_to_top" type="button" id="mayhen_docs_to_top" aria-label="Trở lên đầu trang">↑</button>

</div>{{-- .mayhen_docs_app --}}
@endsection
