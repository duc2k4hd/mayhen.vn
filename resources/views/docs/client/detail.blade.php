@extends('docs.layout')

@php
  $space = $space ?? 'client';
  $isAdmin = $space === 'admin';
  $indexRoute = $isAdmin ? route('admin.docs.index') : route('docs.index');
  $labelSmall = $isAdmin ? 'ADMIN KNOWLEDGE BASE' : 'DOCS & GUIDES';
  $catName = $categories[$currentCategory]['name'] ?? $currentCategory;
  $updatedAt = $article['updated_at'] ?? date('d/m/Y');
  $readTime = $article['read_time'] ?? '5';
@endphp

@section('body')
<div class="mayhen_docs_detail_app" id="mayhen_docs_detail_app" data-space="{{ $space }}" data-theme="light" data-width="comfortable">

  {{-- Reading Progress Bar --}}
  <div class="mayhen_docs_detail_reading_progress" id="mayhen_docs_detail_reading_progress" aria-hidden="true"></div>

  {{-- Skip Link --}}
  <a class="mayhen_docs_detail_skip_link" href="#mayhen_docs_detail_article">Bỏ qua điều hướng, tới nội dung</a>

  {{-- ============================================================
       HEADER
  ============================================================ --}}
  <header class="mayhen_docs_detail_header">
    <div class="mayhen_docs_detail_header_left">
      <button type="button" class="mayhen_docs_detail_icon_button mayhen_docs_detail_mobile_menu" id="mayhen_docs_detail_menu_open" aria-label="Mở danh mục tài liệu" aria-controls="mayhen_docs_detail_sidebar" aria-expanded="false">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
      </button>
      <a href="{{ $indexRoute }}" class="mayhen_docs_detail_brand" aria-label="Mây Hẹn Docs - về đầu trang">
        <span class="mayhen_docs_detail_brand_mark" aria-hidden="true">
          <svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="23"/><circle cx="26" cy="26" r="18" stroke-dasharray="1.2 4"/><path d="M12 34V18l8 10 6-10 6 10 8-10v16"/><path d="M17 15c3-3 6-4 9-4s6 1 9 4"/></svg>
        </span>
        <span class="mayhen_docs_detail_brand_text"><strong>Mây Hẹn</strong><small>{{ $labelSmall }}</small></span>
      </a>
      <span class="mayhen_docs_detail_header_divider" aria-hidden="true"></span>
      <span class="mayhen_docs_detail_header_label">Trung tâm hướng dẫn</span>
    </div>
    <div class="mayhen_docs_detail_header_right">
      <button type="button" class="mayhen_docs_detail_search_trigger" id="mayhen_docs_detail_search_trigger" aria-label="Tìm kiếm tài liệu">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
        <span>Tìm trong tài liệu...</span><kbd>Ctrl K</kbd>
      </button>
      <button type="button" class="mayhen_docs_detail_icon_button mayhen_docs_detail_header_icon" id="mayhen_docs_detail_theme_quick" aria-label="Đổi giao diện sáng tối">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.3 15.7A8.5 8.5 0 0 1 8.3 3.7 8.5 8.5 0 1 0 20.3 15.7Z"/></svg>
      </button>
      <button type="button" class="mayhen_docs_detail_icon_button mayhen_docs_detail_header_icon" data-modal="mayhen_docs_detail_settings_dialog" aria-label="Cài đặt đọc">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/><circle cx="9" cy="7" r="2"/><circle cx="15" cy="12" r="2"/><circle cx="11" cy="17" r="2"/></svg>
      </button>
      <button type="button" class="mayhen_docs_detail_help_button" data-modal="mayhen_docs_detail_shortcuts_dialog">
        <span aria-hidden="true">?</span><span class="mayhen_docs_detail_help_text">Trợ giúp</span>
      </button>
    </div>
  </header>

  {{-- ============================================================
       BREADCRUMB BAR
  ============================================================ --}}
  <div class="mayhen_docs_detail_breadcrumb_bar">
    <button type="button" class="mayhen_docs_detail_icon_button mayhen_docs_detail_desktop_sidebar_toggle" id="mayhen_docs_detail_sidebar_toggle" aria-label="Thu gọn thanh điều hướng">
      <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16"/></svg>
    </button>
    <nav class="mayhen_docs_detail_breadcrumb" aria-label="Đường dẫn tài liệu">
      <a href="{{ $indexRoute }}">Tổng quan</a><span>/</span>
      <span>{{ $catName }}</span><span>/</span>
      <span aria-current="page">{{ $article['title'] }}</span>
    </nav>
    <span class="mayhen_docs_detail_breadcrumb_end"><span aria-hidden="true">✦</span> Made with love, for your day</span>
  </div>

  {{-- Mobile Scrim --}}
  <div class="mayhen_docs_detail_mobile_scrim" id="mayhen_docs_detail_mobile_scrim" hidden></div>

  {{-- ============================================================
       3-COLUMN LAYOUT: Sidebar | Article | TOC
  ============================================================ --}}
  <div class="mayhen_docs_detail_layout" id="mayhen_docs_detail_layout">

    {{-- ---- LEFT SIDEBAR ---- --}}
    <aside class="mayhen_docs_detail_sidebar" id="mayhen_docs_detail_sidebar" aria-label="Danh mục tài liệu">
      <div class="mayhen_docs_detail_sidebar_inner">
        <div class="mayhen_docs_detail_sidebar_mobile_top">
          <strong>Mục lục tài liệu</strong>
          <button type="button" class="mayhen_docs_detail_icon_button" id="mayhen_docs_detail_menu_close" aria-label="Đóng danh mục">
            <svg viewBox="0 0 24 24"><path d="M5 5 19 19M19 5 5 19"/></svg>
          </button>
        </div>
        <div class="mayhen_docs_detail_sidebar_overline">{{ $isAdmin ? 'ADMIN KNOWLEDGE BASE' : 'THƯ VIỆN MÂY HẸN' }}</div>
        <h2 class="mayhen_docs_detail_sidebar_heading">{{ $isAdmin ? 'SOP & Vận hành' : 'Hướng dẫn sử dụng' }}</h2>
        <p class="mayhen_docs_detail_sidebar_desc">{{ $isAdmin ? 'Quy trình chuẩn, chính xác và cập nhật.' : 'Từng bước nhỏ, cho ngày vui trọn vẹn.' }}</p>

        <div class="mayhen_docs_detail_sidebar_nav">
          @foreach((array)$categories as $catSlug => $cat)
            <section class="mayhen_docs_detail_nav_group">
              <button type="button"
                class="mayhen_docs_detail_nav_group_toggle"
                aria-expanded="{{ $catSlug === $currentCategory ? 'true' : 'false' }}"
                aria-controls="mayhen_docs_detail_group_{{ $catSlug }}">
                <span class="mayhen_docs_detail_nav_group_icon">{{ ['✦','❀','♡','↗','◇'][$loop->index % 5] }}</span>
                {{ $cat['name'] }}
                <span class="mayhen_docs_detail_chevron">⌄</span>
              </button>
              <div class="mayhen_docs_detail_nav_links"
                id="mayhen_docs_detail_group_{{ $catSlug }}"
                @if($catSlug !== $currentCategory) hidden @endif>
                @foreach($cat['articles'] as $art)
                  <a href="{{ $art['url'] }}"
                    class="mayhen_docs_detail_nav_link {{ $art['slug'] === ($article['slug'] ?? '') ? 'mayhen_docs_detail_nav_active' : '' }}">
                    {{ $art['title'] }}
                  </a>
                @endforeach
              </div>
            </section>
          @endforeach
        </div>

        <div class="mayhen_docs_detail_sidebar_help">
          <span class="mayhen_docs_detail_sidebar_help_flower">✿</span>
          <strong>Bạn cần thêm một chút trợ giúp?</strong>
          <p>Chúng mình luôn muốn hành trình chuẩn bị {{ $isAdmin ? 'vận hành' : 'cưới' }} thật nhẹ nhàng.</p>
          <button type="button" data-modal="mayhen_docs_detail_feedback_dialog">Gửi lời nhắn <span>↗</span></button>
        </div>
      </div>
    </aside>

    {{-- ---- CENTER ARTICLE ---- --}}
    <main class="mayhen_docs_detail_main" id="mayhen_docs_detail_main">
      <article class="mayhen_docs_detail_article" id="mayhen_docs_detail_article">

        {{-- Article Kicker --}}
        <div class="mayhen_docs_detail_article_kicker">
          <span class="mayhen_docs_detail_kicker_line"></span>
          {{ strtoupper($catName) }}
          <span class="mayhen_docs_detail_kicker_dot">·</span>
          {{ $readTime }} PHÚT ĐỌC
        </div>

        {{-- Article Header --}}
        <div class="mayhen_docs_detail_article_header">
          <div class="mayhen_docs_detail_article_header_art" aria-hidden="true">
            <svg viewBox="0 0 140 138"><path d="M68 132c-5-36-1-68 17-96"/><path d="M81 43C56 29 42 10 47 3c27 3 40 16 34 40ZM70 62C45 60 22 47 23 35c27-5 48 3 47 27ZM65 85C41 94 18 91 11 79c22-16 45-13 54 6ZM83 48c26-16 36-33 34-45C92 13 78 27 83 48ZM77 77c25-8 42-24 44-39-27 2-43 16-44 39ZM74 109c24 2 46-8 54-22-24-9-46-1-54 22"/><path d="M87 27c15-10 28-11 40-3M48 111c-10-5-19-5-27-2"/></svg>
          </div>
          <h1>{{ $article['title'] }}</h1>
          @if(!empty($article['summary']))
            <p class="mayhen_docs_detail_lead">{{ $article['summary'] }}</p>
          @endif
          <div class="mayhen_docs_detail_article_meta">
            <span class="mayhen_docs_detail_author_avatar" aria-hidden="true">M</span>
            <span>
              <strong>Đội ngũ Mây Hẹn</strong>
              <small>{{ $isAdmin ? 'Tài liệu vận hành nội bộ' : 'Hướng dẫn chính thức' }}</small>
            </span>
            <span class="mayhen_docs_detail_meta_sep"></span>
            <span class="mayhen_docs_detail_update">Cập nhật: {{ $updatedAt }}</span>
          </div>
        </div>

        {{-- Article Actions Bar --}}
        <div class="mayhen_docs_detail_article_actions">
          <div class="mayhen_docs_detail_action_primary">
            <button type="button" class="mayhen_docs_detail_btn mayhen_docs_detail_btn_outline" id="mayhen_docs_detail_copy_markdown" title="Sao chép nội dung">
              <svg viewBox="0 0 24 24"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></svg> Sao chép Markdown
            </button>
            <button type="button" class="mayhen_docs_detail_btn mayhen_docs_detail_btn_ghost" id="mayhen_docs_detail_bookmark" aria-pressed="false">
              <svg viewBox="0 0 24 24"><path d="M6 4h12v17l-6-4-6 4V4Z"/></svg><span>Lưu bài</span>
            </button>
            <button type="button" class="mayhen_docs_detail_btn mayhen_docs_detail_btn_ghost" data-modal="mayhen_docs_detail_share_dialog">
              <svg viewBox="0 0 24 24"><path d="M13 6h7v7M20 6l-10 10"/><path d="M18 15v5H4V6h5"/></svg> Chia sẻ
            </button>
          </div>
          <div class="mayhen_docs_detail_action_secondary">
            <button type="button" class="mayhen_docs_detail_font_button" id="mayhen_docs_detail_font_decrease" aria-label="Giảm cỡ chữ">A−</button>
            <button type="button" class="mayhen_docs_detail_font_button" id="mayhen_docs_detail_font_increase" aria-label="Tăng cỡ chữ">A+</button>
            <button type="button" class="mayhen_docs_detail_font_button mayhen_docs_detail_focus_button" id="mayhen_docs_detail_focus_toggle" aria-pressed="false" title="Chế độ tập trung">
              <svg viewBox="0 0 24 24"><path d="M8 4H4v4M16 4h4v4M20 16v4h-4M4 16v4h4"/></svg>
            </button>
            <button type="button" class="mayhen_docs_detail_btn mayhen_docs_detail_btn_outline mayhen_docs_detail_toc_mobile_button" data-modal="mayhen_docs_detail_toc_dialog">Mục lục <span>⌄</span></button>
          </div>
        </div>

        {{-- Article Body (Rendered Markdown HTML) --}}
        <div class="mayhen_docs_detail_article_body" id="mayhen_docs_detail_article_body">
          {!! $article['html_body'] !!}
        </div>

        {{-- Next/Previous Navigation --}}
        @if(!empty($article['prev']) || !empty($article['next']))
          <section class="mayhen_docs_detail_doc_section mayhen_docs_detail_next_section">
            <div class="mayhen_docs_detail_section_head"><h2>Tiếp tục hành trình</h2></div>
            <div class="mayhen_docs_detail_next_grid">
              @if(!empty($article['prev']))
                <a href="{{ $article['prev']['url'] }}" class="mayhen_docs_detail_next_card">
                  <span>BÀI TRƯỚC</span>
                  <strong>{{ $article['prev']['title'] }}</strong>
                  <b>←</b>
                </a>
              @endif
              @if(!empty($article['next']))
                <a href="{{ $article['next']['url'] }}" class="mayhen_docs_detail_next_card">
                  <span>BÀI TIẾP THEO</span>
                  <strong>{{ $article['next']['title'] }}</strong>
                  <b>↗</b>
                </a>
              @endif
            </div>
          </section>
        @endif

      </article>
    </main>

    {{-- ---- RIGHT TOC ---- --}}
    <aside class="mayhen_docs_detail_toc" id="mayhen_docs_detail_toc" aria-label="Mục lục bài viết">
      <div class="mayhen_docs_detail_toc_inner">
        <div class="mayhen_docs_detail_toc_eyebrow">TRONG BÀI VIẾT NÀY</div>
        <div class="mayhen_docs_detail_toc_rule"></div>
        <nav id="mayhen_docs_detail_toc_links" aria-label="Mục lục">
          @foreach($article['toc'] ?? [] as $item)
            <a href="#{{ $item['id'] }}" style="{{ $item['level'] > 2 ? 'padding-left: 22px; font-size: 10.5px;' : '' }}">{{ $item['text'] }}</a>
          @endforeach
        </nav>
        <div class="mayhen_docs_detail_toc_bottom">
          <div class="mayhen_docs_detail_toc_progress">
            <span>Tiến độ đọc</span>
            <b id="mayhen_docs_detail_read_percent">0%</b>
          </div>
          <div class="mayhen_docs_detail_toc_track">
            <span id="mayhen_docs_detail_toc_progress_bar"></span>
          </div>
          <button type="button" data-modal="mayhen_docs_detail_settings_dialog" class="mayhen_docs_detail_toc_settings">⚙ &nbsp; Tùy chỉnh trải nghiệm đọc</button>
        </div>
      </div>
    </aside>

  </div>{{-- .mayhen_docs_detail_layout --}}

  {{-- ============================================================
       FOOTER
  ============================================================ --}}
  <footer class="mayhen_docs_detail_footer">
    <div class="mayhen_docs_detail_footer_flower" aria-hidden="true">
      <svg viewBox="0 0 1200 140" preserveAspectRatio="xMidYMax slice"><path d="M0 140h1200"/><path d="M60 140C140 60 160 90 190 15M95 99C50 70 32 40 24 20c55 5 77 28 71 79ZM132 62C158 20 194 10 225 15c-21 40-48 54-93 47ZM110 110c-10-42-1-65 20-90M1040 140c-24-36-21-68-36-117m20 55c18-30 44-48 70-47-4 38-29 58-70 47Zm-11 24c-42-11-61-34-70-66 40 4 60 24 70 66ZM1125 140c-24-42-24-78-16-103M1108 78c-20-17-34-37-36-64 34 10 43 32 36 64Zm5 38c25-33 48-45 74-41-7 32-27 46-74 41Z"/></svg>
    </div>
    <div class="mayhen_docs_detail_footer_top">
      <div class="mayhen_docs_detail_footer_logo">
        <span>✿</span><strong>Mây Hẹn</strong>
        <small>Một chút yêu thương, một đời kỷ niệm.</small>
      </div>
      <button type="button" class="mayhen_docs_detail_btn mayhen_docs_detail_btn_outline" id="mayhen_docs_detail_footer_top">Về đầu trang ↑</button>
    </div>
    <div class="mayhen_docs_detail_footer_columns">
      <div>
        <h3>Bài viết này có hữu ích không?</h3>
        <p>Chúng mình luôn lắng nghe để từng trang hướng dẫn tốt hơn.</p>
        <div class="mayhen_docs_detail_feedback_buttons">
          <button type="button" data-feedback-vote="yes">♡ Có, rất hữu ích</button>
          <button type="button" data-feedback-vote="no">Chưa thật sự</button>
        </div>
      </div>
      <div>
        <h3>Giúp tài liệu Mây Hẹn ngày một tốt hơn</h3>
        <p>Bạn thấy chỗ nào chưa rõ? Có ý tưởng cải thiện?</p>
        <button type="button" class="mayhen_docs_detail_btn mayhen_docs_detail_btn_outline" data-modal="mayhen_docs_detail_feedback_dialog">Góp ý tài liệu ↗</button>
      </div>
      <div class="mayhen_docs_detail_footer_links">
        <h3>CẦN THÊM TRỢ GIÚP?</h3>
        <button type="button" id="mayhen_docs_detail_footer_search">Tìm kiếm hướng dẫn</button>
        <button type="button" data-modal="mayhen_docs_detail_shortcuts_dialog">Phím tắt hữu ích</button>
        <button type="button" data-modal="mayhen_docs_detail_settings_dialog">Cài đặt hiển thị</button>
      </div>
    </div>
    <div class="mayhen_docs_detail_footer_bottom">
      <span>© {{ date('Y') }} Mây Hẹn · Thiết kế bằng sự trân trọng.</span>
      <span>Tài liệu chính thức của nền tảng Mây Hẹn</span>
    </div>
  </footer>

  {{-- Back To Top --}}
  <button type="button" class="mayhen_docs_detail_back_top" id="mayhen_docs_detail_back_top" aria-label="Về đầu trang">
    <svg viewBox="0 0 24 24"><path d="m6 14 6-6 6 6M12 8v12"/></svg>
  </button>
  <div class="mayhen_docs_detail_toast" id="mayhen_docs_detail_toast" role="status" aria-live="polite"></div>

  {{-- ============================================================
       MODAL DIALOGS
  ============================================================ --}}
  {{-- Search Dialog --}}
  <dialog class="mayhen_docs_detail_dialog mayhen_docs_detail_search_dialog" id="mayhen_docs_detail_search_dialog" aria-labelledby="mayhen_docs_detail_search_title">
    <div class="mayhen_docs_detail_dialog_shell">
      <div class="mayhen_docs_detail_dialog_header">
        <span class="mayhen_docs_detail_modal_eyebrow">TÌM KIẾM TÀI LIỆU</span>
        <button type="button" class="mayhen_docs_detail_dialog_close" data-close-dialog aria-label="Đóng"><span>×</span></button>
      </div>
      <h2 id="mayhen_docs_detail_search_title">Bạn đang tìm điều gì?</h2>
      <div class="mayhen_docs_detail_search_box">
        <svg viewBox="0 0 24 24"><circle cx="10" cy="10" r="6"/><path d="m15 15 5 5"/></svg>
        <input id="mayhen_docs_detail_search_input" type="search" placeholder="Thử 'RSVP', 'xuất bản', 'khách mời'..." autocomplete="off" aria-label="Tìm trong tài liệu">
        <kbd>ESC</kbd>
      </div>
      <div class="mayhen_docs_detail_search_count" id="mayhen_docs_detail_search_count">Các mục hướng dẫn gợi ý</div>
      <div class="mayhen_docs_detail_search_results" id="mayhen_docs_detail_search_results"></div>
      <p class="mayhen_docs_detail_search_hint">↑ ↓ Chọn kết quả · Enter mở mục · Esc đóng</p>
    </div>
  </dialog>

  {{-- Settings Dialog --}}
  <dialog class="mayhen_docs_detail_dialog" id="mayhen_docs_detail_settings_dialog" aria-labelledby="mayhen_docs_detail_settings_title">
    <div class="mayhen_docs_detail_dialog_shell">
      <div class="mayhen_docs_detail_dialog_header">
        <span class="mayhen_docs_detail_modal_eyebrow">CÁ NHÂN HÓA</span>
        <button type="button" class="mayhen_docs_detail_dialog_close" data-close-dialog aria-label="Đóng">×</button>
      </div>
      <h2 id="mayhen_docs_detail_settings_title">Không gian đọc của bạn</h2>
      <p class="mayhen_docs_detail_dialog_desc">Điều chỉnh nhẹ nhàng để việc đọc thoải mái hơn. Tùy chọn được lưu trên thiết bị này.</p>
      <div class="mayhen_docs_detail_setting_group">
        <h3>Giao diện</h3>
        <div class="mayhen_docs_detail_segment" id="mayhen_docs_detail_theme_segment">
          <button type="button" data-theme-option="light" aria-pressed="true">☼ Sáng</button>
          <button type="button" data-theme-option="dark" aria-pressed="false">☾ Tối</button>
          <button type="button" data-theme-option="system" aria-pressed="false">◐ Hệ thống</button>
        </div>
      </div>
      <div class="mayhen_docs_detail_setting_group">
        <h3>Độ rộng nội dung</h3>
        <div class="mayhen_docs_detail_segment" id="mayhen_docs_detail_width_segment">
          <button type="button" data-width-option="comfortable" aria-pressed="true">Vừa phải</button>
          <button type="button" data-width-option="wide" aria-pressed="false">Rộng hơn</button>
        </div>
      </div>
      <div class="mayhen_docs_detail_setting_group">
        <h3>Cỡ chữ bài viết</h3>
        <div class="mayhen_docs_detail_setting_font">
          <button type="button" id="mayhen_docs_detail_settings_smaller">A−</button>
          <span id="mayhen_docs_detail_settings_font_value">16px</span>
          <button type="button" id="mayhen_docs_detail_settings_bigger">A+</button>
        </div>
      </div>
      <button type="button" class="mayhen_docs_detail_btn mayhen_docs_detail_btn_primary mayhen_docs_detail_dialog_done" data-close-dialog>Hoàn tất</button>
    </div>
  </dialog>

  {{-- Share Dialog --}}
  <dialog class="mayhen_docs_detail_dialog" id="mayhen_docs_detail_share_dialog" aria-labelledby="mayhen_docs_detail_share_title">
    <div class="mayhen_docs_detail_dialog_shell">
      <div class="mayhen_docs_detail_dialog_header">
        <span class="mayhen_docs_detail_modal_eyebrow">LAN TỎA YÊU THƯƠNG</span>
        <button type="button" class="mayhen_docs_detail_dialog_close" data-close-dialog aria-label="Đóng">×</button>
      </div>
      <div class="mayhen_docs_detail_share_art" aria-hidden="true">✉ <span>♡</span></div>
      <h2 id="mayhen_docs_detail_share_title">Chia sẻ hướng dẫn này</h2>
      <p class="mayhen_docs_detail_dialog_desc">Gửi trang hướng dẫn hữu ích này cho người thân.</p>
      <div class="mayhen_docs_detail_share_field">
        <input id="mayhen_docs_detail_share_input" readonly aria-label="Đường dẫn hiện tại">
        <button type="button" id="mayhen_docs_detail_copy_share">Sao chép</button>
      </div>
      <div class="mayhen_docs_detail_share_row">
        <button type="button" class="mayhen_docs_detail_btn mayhen_docs_detail_btn_primary" id="mayhen_docs_detail_native_share">Chia sẻ qua thiết bị ↗</button>
        <button type="button" class="mayhen_docs_detail_btn mayhen_docs_detail_btn_outline" id="mayhen_docs_detail_print">In / Lưu PDF</button>
      </div>
    </div>
  </dialog>

  {{-- Feedback Dialog --}}
  <dialog class="mayhen_docs_detail_dialog" id="mayhen_docs_detail_feedback_dialog" aria-labelledby="mayhen_docs_detail_feedback_title">
    <div class="mayhen_docs_detail_dialog_shell">
      <div class="mayhen_docs_detail_dialog_header">
        <span class="mayhen_docs_detail_modal_eyebrow">CHÚNG MÌNH LẮNG NGHE</span>
        <button type="button" class="mayhen_docs_detail_dialog_close" data-close-dialog aria-label="Đóng">×</button>
      </div>
      <h2 id="mayhen_docs_detail_feedback_title">Bạn muốn nhắn gì với Mây Hẹn?</h2>
      <p class="mayhen_docs_detail_dialog_desc">Góp ý về nội dung, giao diện hoặc hướng dẫn cần bổ sung.</p>
      <label class="mayhen_docs_detail_field_label" for="mayhen_docs_detail_feedback_message">Lời nhắn của bạn</label>
      <textarea class="mayhen_docs_detail_dialog" id="mayhen_docs_detail_feedback_message" rows="5" maxlength="1200" placeholder="Mình mong tài liệu bổ sung thêm..." style="width:100%;background:var(--mayhen-docs-detail-bg);border:1px solid var(--mayhen-docs-detail-border-strong);color:var(--mayhen-docs-detail-text);padding:11px 12px;border-radius:8px;outline:none;resize:vertical;min-height:110px;font-size:12px;line-height:1.7;font-family:inherit"></textarea>
      <div class="mayhen_docs_detail_dialog_actions">
        <button type="button" class="mayhen_docs_detail_btn mayhen_docs_detail_btn_outline" data-close-dialog>Đóng</button>
        <button type="button" class="mayhen_docs_detail_btn mayhen_docs_detail_btn_primary" id="mayhen_docs_detail_copy_feedback">Sao chép góp ý</button>
      </div>
    </div>
  </dialog>

  {{-- Shortcuts Dialog --}}
  <dialog class="mayhen_docs_detail_dialog" id="mayhen_docs_detail_shortcuts_dialog" aria-labelledby="mayhen_docs_detail_shortcuts_title">
    <div class="mayhen_docs_detail_dialog_shell">
      <div class="mayhen_docs_detail_dialog_header">
        <span class="mayhen_docs_detail_modal_eyebrow">MẸO NHỎ KHI ĐỌC</span>
        <button type="button" class="mayhen_docs_detail_dialog_close" data-close-dialog aria-label="Đóng">×</button>
      </div>
      <h2 id="mayhen_docs_detail_shortcuts_title">Phím tắt & tiện ích</h2>
      <div class="mayhen_docs_detail_shortcut_list">
        <div><span>Tìm kiếm tài liệu</span><kbd>Ctrl / ⌘ + K</kbd></div>
        <div><span>Mở tìm kiếm nhanh</span><kbd>/</kbd></div>
        <div><span>Xem phím tắt</span><kbd>?</kbd></div>
        <div><span>Đóng hộp thoại</span><kbd>Esc</kbd></div>
        <div><span>Di chuyển giữa các nút</span><kbd>Tab</kbd></div>
      </div>
      <button type="button" class="mayhen_docs_detail_btn mayhen_docs_detail_btn_primary mayhen_docs_detail_dialog_done" data-close-dialog>Đã hiểu, cảm ơn!</button>
    </div>
  </dialog>

  {{-- TOC Modal (mobile) --}}
  <dialog class="mayhen_docs_detail_dialog mayhen_docs_detail_toc_dialog" id="mayhen_docs_detail_toc_dialog" aria-labelledby="mayhen_docs_detail_toc_title">
    <div class="mayhen_docs_detail_dialog_shell">
      <div class="mayhen_docs_detail_dialog_header">
        <span class="mayhen_docs_detail_modal_eyebrow">ĐI ĐẾN MỤC</span>
        <button type="button" class="mayhen_docs_detail_dialog_close" data-close-dialog aria-label="Đóng">×</button>
      </div>
      <h2 id="mayhen_docs_detail_toc_title">Mục lục bài viết</h2>
      <nav class="mayhen_docs_detail_toc_modal_links" id="mayhen_docs_detail_toc_modal_links" aria-label="Mục lục trong hộp thoại">
        @foreach($article['toc'] ?? [] as $item)
          <a href="#{{ $item['id'] }}">{{ $item['text'] }}</a>
        @endforeach
      </nav>
    </div>
  </dialog>

  {{-- Search Data for JS --}}
  <script type="application/json" id="mayhen_docs_detail_search_data">
    {!! json_encode(collect((array)$categories)->flatMap(fn($cat) => collect($cat['articles'] ?? [])->map(fn($art) => [
      'title' => $art['title'],
      'url'   => $art['url'],
      'category' => $cat['name'],
    ]))->values()) !!}
  </script>

</div>{{-- .mayhen_docs_detail_app --}}
@endsection
