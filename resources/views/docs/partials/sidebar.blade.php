{{-- Unified Docs Left Sidebar (Accordion Tree) --}}
<aside class="mayhen_docs_sidebar" aria-label="Danh mục hướng dẫn">
  <div class="mayhen_docs_sidebar_top">
    <span>{{ $space === 'admin' ? 'DANH MỤC QUY TRÌNH SOP' : 'DANH MỤC HƯỚNG DẪN' }}</span>
    <button type="button" class="mayhen_docs_sidebar_close" aria-label="Đóng menu">
      <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </button>
  </div>

  <nav class="mayhen_docs_side_nav">
    {{-- Home Link --}}
    <a href="{{ $space === 'admin' ? route('admin.docs.index') : route('docs.index') }}" class="mayhen_docs_sidebar_home {{ request()->routeIs('*.index') ? 'mayhen_docs_side_active' : '' }}">
      <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path></svg>
      <span>Trang tổng quan</span>
    </a>

    {{-- Categories & Articles Loop --}}
    @foreach($categories as $catKey => $cat)
      @php
        $isCurrentCat = isset($currentCategory) && $currentCategory === $catKey;
      @endphp
      <div class="mayhen_docs_sidebar_section">
        <button type="button" class="mayhen_docs_side_group" aria-expanded="{{ $isCurrentCat ? 'true' : 'false' }}">
          <span>{{ $cat['name'] }}</span>
          <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </button>

        <ul class="mayhen_docs_side_articles" {{ $isCurrentCat ? '' : 'hidden' }}>
          @foreach($cat['articles'] as $art)
            @php
              $isActiveArticle = isset($article) && ($article['id'] === $art['id']);
            @endphp
            <li>
              <a href="{{ $art['url'] }}" class="mayhen_docs_side_link {{ $isActiveArticle ? 'is-active' : '' }}">
                <span class="mayhen_docs_side_bullet"></span>
                <span>{{ $art['title'] }}</span>
              </a>
            </li>
          @endforeach
        </ul>
      </div>
    @endforeach
  </nav>
</aside>

