@extends('docs.layout')

@section('content')
<div class="mayhen_docs_body_layout">
  {{-- Sidebar --}}
  @include('docs.partials.sidebar')

  {{-- Main Content Viewport --}}
  <main id="main-content" class="mayhen_docs_main_viewport">
    {{-- Hero Search Banner --}}
    <section class="mayhen_docs_hero">
      <div class="mayhen_docs_hero_content">
        <span class="mayhen_docs_hero_badge">TRUNG TÂM TRỢ GIÚP MÂY HẸN</span>
        <h1 class="mayhen_docs_hero_title">Chúng tôi có thể giúp gì cho ngày cưới của bạn?</h1>
        <p class="mayhen_docs_hero_subtitle">Hướng dẫn chi tiết từng bước tạo thiệp lãng mạn, quản lý khách mời thông minh và thanh toán an toàn.</p>

        {{-- Big Search Bar --}}
        <div class="mayhen_docs_hero_search_box" data-action="open-search">
          <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <span>Tìm kiếm hướng dẫn (ví dụ: đổi ảnh bìa, xác nhận rsvp, xếp bàn...)...</span>
          <kbd>Ctrl K</kbd>
        </div>

        {{-- Trending Pills --}}
        <div class="mayhen_docs_trending_tags">
          <span class="mayhen_docs_trending_label">Chủ đề tìm nhiều:</span>
          <button type="button" class="mayhen_docs_tag_pill" data-action="open-search">#TạoThiệpLầnĐầu</button>
          <button type="button" class="mayhen_docs_tag_pill" data-action="open-search">#ThayẢnhCưới</button>
          <button type="button" class="mayhen_docs_tag_pill" data-action="open-search">#XácNhậnRSVP</button>
          <button type="button" class="mayhen_docs_tag_pill" data-action="open-search">#SơĐồBànTiệc</button>
          <button type="button" class="mayhen_docs_tag_pill" data-action="open-search">#SePayVietQR</button>
        </div>
      </div>
    </section>

    {{-- Fast Role Selection --}}
    <section class="mayhen_docs_roles_grid">
      <div class="mayhen_docs_role_card">
        <div class="mayhen_docs_role_icon">👰🤵</div>
        <h3>Cô Dâu & Chú Rể</h3>
        <p>Bắt đầu tạo thiệp cưới, thay ảnh, đổi nhạc lãng mạn và gửi thiệp mời đến bạn bè.</p>
        <a href="#cat-02-studio-editor" class="mayhen_docs_role_link">Vào xem hướng dẫn Studio →</a>
      </div>
      <div class="mayhen_docs_role_card">
        <div class="mayhen_docs_role_icon">💌</div>
        <h3>Khách Mời Dự Tiệc</h3>
        <p>Cách mở thiệp, xác nhận tham dự (RSVP), chỉ đường Google Maps và gửi lời chúc phúc.</p>
        <a href="#cat-04-guest-rsvp" class="mayhen_docs_role_link">Xem hướng dẫn RSVP →</a>
      </div>
      <div class="mayhen_docs_role_card">
        <div class="mayhen_docs_role_icon">📷</div>
        <h3>Đối Tác & Studio</h3>
        <p>Tạo thiệp cho khách hàng của studio, quản lý chiết khấu hoa hồng và rút tiền an toàn.</p>
        <a href="#cat-10-partner-privacy" class="mayhen_docs_role_link">Cổng đối tác B2B →</a>
      </div>
    </section>

    {{-- Categories & Articles Grid --}}
    <section class="mayhen_docs_categories_section">
      <div class="mayhen_docs_section_header">
        <h2>TẤT CẢ CHUYÊN MỤC HƯỚNG DẪN</h2>
        <p>Chọn chuyên mục bên dưới để xem danh sách toàn bộ các bài viết chi tiết.</p>
      </div>

      <div class="mayhen_docs_category_cards_grid">
        @forelse($categories as $catSlug => $cat)
          <div id="cat-{{ $catSlug }}" class="mayhen_docs_cat_card">
            <div class="mayhen_docs_cat_card_header">
              <span class="mayhen_docs_cat_order">0{{ $cat['order'] }}</span>
              <h3>{{ $cat['name'] }}</h3>
              <span class="mayhen_docs_cat_count">{{ count($cat['articles']) }} bài viết</span>
            </div>
            <ul class="mayhen_docs_cat_article_list">
              @foreach(array_slice($cat['articles'], 0, 5) as $art)
                <li>
                  <a href="{{ $art['url'] }}">
                    <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                    <span>{{ $art['title'] }}</span>
                  </a>
                </li>
              @endforeach
            </ul>
            @if(count($cat['articles']) > 5)
              <div class="mayhen_docs_cat_more">
                <a href="{{ $cat['articles'][0]['url'] }}">Xem thêm {{ count($cat['articles']) - 5 }} bài hướng dẫn khác...</a>
              </div>
            @endif
          </div>
        @empty
          <div class="mayhen_docs_empty_notice">
            <p>Hệ thống bài viết đang được đồng bộ. Vui lòng quay lại trong giây lát.</p>
          </div>
        @endforelse
      </div>
    </section>

    {{-- Direct Support Callout --}}
    <section class="mayhen_docs_support_banner">
      <div class="mayhen_docs_support_inner">
        <div>
          <h3>Bạn vẫn cần thêm sự trợ giúp trực tiếp?</h3>
          <p>Đội ngũ chuyên viên Mây Hẹn luôn sẵn sàng đồng hành cùng bạn 24/7 để ngày cưới diễn ra hoàn hảo nhất.</p>
        </div>
        <div class="mayhen_docs_support_actions">
          <a href="{{ route('contact') }}" class="mayhen_docs_btn_primary">Gửi yêu cầu hỗ trợ</a>
          <a href="https://zalo.me" target="_blank" rel="noopener" class="mayhen_docs_btn_outline">Nhắn tin Zalo</a>
        </div>
      </div>
    </section>
  </main>
</div>
@endsection

