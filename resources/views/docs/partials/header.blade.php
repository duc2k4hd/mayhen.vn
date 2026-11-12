{{-- Unified Docs Sticky Header (2 Tiers) --}}
<header class="mayhen_docs_header">
  <div class="mayhen_docs_header_inner">
    {{-- Mobile Menu Trigger --}}
    <button type="button" class="mayhen_docs_icon_button mayhen_docs_mobile_menu_button" aria-label="Mở danh mục tài liệu">
      <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
    </button>

    {{-- Brand Mark --}}
    <a href="{{ $space === 'admin' ? route('admin.docs.index') : route('docs.index') }}" class="mayhen_docs_brand">
      <span class="mayhen_docs_brand_mark">
        <svg viewBox="0 0 24 24"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"></path></svg>
      </span>
      <span class="mayhen_docs_brand_text">
        <strong>MÂY HẸN</strong>
        <small>{{ $space === 'admin' ? 'ADMIN KNOWLEDGE BASE' : 'TÀI LIỆU HƯỚNG DẪN' }}</small>
      </span>
    </a>

    <div class="mayhen_docs_header_rule"></div>

    {{-- Search Trigger Pill --}}
    <button type="button" class="mayhen_docs_search_top" data-action="open-search" aria-label="Tìm kiếm tài liệu">
      <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
      <span>Tìm kiếm hướng dẫn...</span>
      <kbd>Ctrl K</kbd>
    </button>

    <div class="mayhen_docs_spacer" style="flex: 1;"></div>

    {{-- Header Links --}}
    <nav class="mayhen_docs_header_links">
      <a href="{{ route('home') }}" target="_blank" rel="noopener">Trang chủ mayhen.vn</a>
      <a href="{{ route('templates.index') }}">Mẫu thiệp</a>
      <a href="{{ route('pricing') }}">Bảng giá</a>
      @if($space === 'admin')
        <a href="{{ route('admin.dashboard') }}" class="mayhen_docs_header_login">Vào Admin Portal</a>
      @else
        @if(auth()->check() && auth()->user()->isAdmin())
          <a href="{{ route('admin.docs.index') }}" class="mayhen_docs_header_login" title="Chuyển sang tài liệu quản trị">Sang Admin Docs</a>
        @endif
        <a href="{{ route('invitation.create') }}" class="mayhen_docs_header_signup">Tạo thiệp ngay</a>
      @endif
    </nav>

    {{-- Theme Switcher Button --}}
    <button type="button" class="mayhen_docs_icon_button mayhen_docs_theme_toggle" data-action="toggle-theme" title="Đổi giao diện Sáng / Tối">
      <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
    </button>
  </div>

  {{-- Subheader / Breadcrumb Bar --}}
  <div class="mayhen_docs_subheader">
    <div class="mayhen_docs_subnav">
      <a href="{{ $space === 'admin' ? route('admin.docs.index') : route('docs.index') }}" class="{{ request()->routeIs('*.index') ? 'mayhen_docs_subnav_active' : '' }}">
        <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
        <span>Tổng quan</span>
      </a>
      @if(isset($currentCategory))
        <span>/</span>
        <span class="mayhen_docs_subnav_cat">{{ $categories[$currentCategory]['name'] ?? $currentCategory }}</span>
      @endif
      @if(isset($article))
        <span>/</span>
        <span class="mayhen_docs_subnav_article">{{ $article['title'] }}</span>
      @endif
    </div>
  </div>

  {{-- Top Progress Bar --}}
  <div class="mayhen_docs_progress" aria-hidden="true"></div>
</header>
