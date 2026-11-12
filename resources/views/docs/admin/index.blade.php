@extends('docs.layout')

@section('content')
<div class="mayhen_docs_body_layout">
  {{-- Sidebar --}}
  @include('docs.partials.sidebar')

  {{-- Main Content Viewport --}}
  <main id="main-content" class="mayhen_docs_main_viewport">
    {{-- Admin Hero Banner --}}
    <section class="mayhen_docs_hero mayhen_docs_hero_admin">
      <div class="mayhen_docs_hero_content">
        <span class="mayhen_docs_hero_badge mayhen_docs_hero_badge_admin">QUẢN TRỊ & BÀN GIAO VẬN HÀNH</span>
        <h1 class="mayhen_docs_hero_title">Sổ tay Quy trình Vận hành Tiêu chuẩn (Admin SOP)</h1>
        <p class="mayhen_docs_hero_subtitle">Tài liệu bàn giao toàn diện dành cho nhân sự mới, quản lý, kế toán và đội ngũ hỗ trợ kỹ thuật Mây Hẹn.</p>

        {{-- Big Search Bar --}}
        <div class="mayhen_docs_hero_search_box" data-action="open-search">
          <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <span>Tìm kiếm quy trình SOP (ví dụ: kích hoạt sepay, hoàn tiền, khóa thiệp, backup...)...</span>
          <kbd>Ctrl K</kbd>
        </div>

        {{-- Admin Fast Tags --}}
        <div class="mayhen_docs_trending_tags">
          <span class="mayhen_docs_trending_label">Quy trình tra cứu nhanh:</span>
          <button type="button" class="mayhen_docs_tag_pill" data-action="open-search">#DuyệtHoànTiền</button>
          <button type="button" class="mayhen_docs_tag_pill" data-action="open-search">#KíchHoạtSePay</button>
          <button type="button" class="mayhen_docs_tag_pill" data-action="open-search">#KhóaThiệpViPhạm</button>
          <button type="button" class="mayhen_docs_tag_pill" data-action="open-search">#ChẩnĐoánAsset</button>
          <button type="button" class="mayhen_docs_tag_pill" data-action="open-search">#SựCốLễTânP0</button>
        </div>
      </div>
    </section>

    {{-- Admin Security Banner --}}
    <div class="mayhen_docs_callout mayhen_docs_callout_danger" style="margin: 20px 32px 0;">
      <div class="mayhen_docs_callout_badge">NGUYÊN TẮC BẢO MẬT BẮT BUỘC</div>
      <div class="mayhen_docs_callout_text">
        Toàn bộ nội dung trong không gian Admin Knowledge Base là tài liệu lưu hành nội bộ mật. Nghiêm cấm sao chép, chụp màn hình quy trình nghiệp vụ hoặc cung cấp mã tham chiếu hệ thống ra bên ngoài. Mọi thao tác đều được hệ thống ghi nhận vào Audit Log.
      </div>
    </div>

    {{-- Role SOP Cards --}}
    <section class="mayhen_docs_roles_grid">
      <div class="mayhen_docs_role_card">
        <div class="mayhen_docs_role_icon">🎧</div>
        <h3>CSKH & Tiếp Nhận (Support)</h3>
        <p>Tra cứu Customer 360, hỗ trợ khách đổi ảnh, đổi ngày cưới, trả lời ticket và sử dụng câu trả lời mẫu.</p>
        <a href="#cat-08-support-messaging" class="mayhen_docs_role_link">SOP CSKH →</a>
      </div>
      <div class="mayhen_docs_role_card">
        <div class="mayhen_docs_role_icon">💳</div>
        <h3>Kế Toán & Tài Chính (Finance)</h3>
        <p>Đối soát giao dịch SePay/VietQR, kích hoạt đơn thủ công, xét duyệt hoàn tiền và xuất hóa đơn VAT.</p>
        <a href="#cat-06-orders-refunds" class="mayhen_docs_role_link">SOP Tài chính →</a>
      </div>
      <div class="mayhen_docs_role_card">
        <div class="mayhen_docs_role_icon">🛡️</div>
        <h3>Quản Lý & Kỹ Thuật (Manager)</h3>
        <p>Kiểm định mẫu thiệp, phát hành release & rollback, phân quyền RBAC, giám sát sức khỏe và xử lý sự cố P0.</p>
        <a href="#cat-10-system-health" class="mayhen_docs_role_link">SOP Hệ thống →</a>
      </div>
    </section>

    {{-- Categories & SOPs Grid --}}
    <section class="mayhen_docs_categories_section">
      <div class="mayhen_docs_section_header">
        <h2>10 DANH MỤC QUY TRÌNH VẬN HÀNH TIÊU CHUẨN (SOP)</h2>
        <p>Chọn quy trình để đọc điều kiện tiên quyết, các bước thực hiện và bằng chứng kiểm toán bắt buộc.</p>
      </div>

      <div class="mayhen_docs_category_cards_grid">
        @forelse($categories as $catSlug => $cat)
          <div id="cat-{{ $catSlug }}" class="mayhen_docs_cat_card">
            <div class="mayhen_docs_cat_card_header">
              <span class="mayhen_docs_cat_order">SOP 0{{ $cat['order'] }}</span>
              <h3>{{ $cat['name'] }}</h3>
              <span class="mayhen_docs_cat_count">{{ count($cat['articles']) }} quy trình</span>
            </div>
            <ul class="mayhen_docs_cat_article_list">
              @foreach(array_slice($cat['articles'], 0, 5) as $art)
                <li>
                  <a href="{{ $art['url'] }}">
                    <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <span>{{ $art['title'] }}</span>
                  </a>
                </li>
              @endforeach
            </ul>
            @if(count($cat['articles']) > 5)
              <div class="mayhen_docs_cat_more">
                <a href="{{ $cat['articles'][0]['url'] }}">Xem thêm {{ count($cat['articles']) - 5 }} quy trình khác...</a>
              </div>
            @endif
          </div>
        @empty
          <div class="mayhen_docs_empty_notice">
            <p>Hệ thống quy trình SOP đang được đồng bộ. Vui lòng quay lại trong giây lát.</p>
          </div>
        @endforelse
      </div>
    </section>
  </main>
</div>
@endsection

