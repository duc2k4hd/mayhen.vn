@extends('docs.layout')

@section('content')
<div class="mayhen_docs_body_layout">
  {{-- Left Sidebar --}}
  @include('docs.partials.sidebar')

  {{-- Center Article Main Content --}}
  <main id="main-content" class="mayhen_docs_article_viewport">
    <article class="mayhen_docs_article">
      {{-- Breadcrumbs --}}
      <nav class="mayhen_docs_breadcrumb_nav" aria-label="Đường dẫn bài viết">
        <ol>
          <li><a href="{{ route('docs.index') }}">Tài liệu</a></li>
          <li><span>/</span></li>
          <li><span class="mayhen_docs_bc_cat">{{ $categories[$currentCategory]['name'] ?? $currentCategory }}</span></li>
          <li><span>/</span></li>
          <li class="is-active" aria-current="page">{{ $article['title'] }}</li>
        </ol>
      </nav>

      {{-- Article Title & Meta --}}
      <header class="mayhen_docs_article_header">
        <h1 class="mayhen_docs_article_title">{{ $article['title'] }}</h1>

        <div class="mayhen_docs_article_meta">
          <span class="mayhen_docs_meta_item">
            <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span>Cập nhật: {{ $article['updated_at'] }}</span>
          </span>
          <span class="mayhen_docs_meta_item">
            <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            <span>Dành cho: {{ $article['reader'] ?: 'Cô dâu, chú rể' }}</span>
          </span>
          @if(!empty($article['mapped_route']))
            <span class="mayhen_docs_meta_item mayhen_docs_badge_route">
              <span>Chức năng: <code>{{ $article['mapped_route'] }}</code></span>
            </span>
          @endif
        </div>

        {{-- Summary Banner --}}
        @if(!empty($article['summary']))
          <div class="mayhen_docs_summary_box">
            <strong>Tóm tắt:</strong> {{ $article['summary'] }}
          </div>
        @endif
      </header>

      {{-- Article Body (Rendered from Markdown) --}}
      <div class="mayhen_docs_article_body">
        {!! $article['html_body'] !!}
      </div>

      {{-- Feedback / Helpful Section --}}
      <div class="mayhen_docs_feedback_box">
        <h4>Bài viết này có hữu ích với bạn không?</h4>
        <div class="mayhen_docs_feedback_buttons">
          <button type="button" class="mayhen_docs_btn_feedback" onclick="alert('Cảm ơn bạn đã phản hồi! Mây Hẹn sẽ tiếp tục hoàn thiện tài liệu tốt hơn.')">👍 Có, rất hữu ích</button>
          <button type="button" class="mayhen_docs_btn_feedback" onclick="alert('Cảm ơn bạn! Chúng tôi đã ghi nhận và sẽ bổ sung hướng dẫn chi tiết hơn.')">👎 Chưa giải quyết được vấn đề</button>
        </div>
      </div>
    </article>
  </main>

  {{-- Right Sidebar: Sticky Table of Contents (TOC) --}}
  <aside class="mayhen_docs_toc_sidebar" aria-label="Mục lục bài viết">
    <div class="mayhen_docs_toc_sticky">
      <span class="mayhen_docs_toc_title">MỤC LỤC BÀI VIẾT</span>
      @if(!empty($article['toc']))
        <ul class="mayhen_docs_toc_list">
          @foreach($article['toc'] as $item)
            <li class="mayhen_docs_toc_item_h{{ $item['level'] }}">
              <a href="#{{ $item['id'] }}" class="mayhen_docs_toc_link">{{ $item['text'] }}</a>
            </li>
          @endforeach
        </ul>
      @else
        <p class="mayhen_docs_toc_empty">Bài viết ngắn, không có đề mục phụ.</p>
      @endif

      <div class="mayhen_docs_toc_actions">
        <button type="button" class="mayhen_docs_btn_action" onclick="navigator.clipboard.writeText(window.location.href); alert('Đã sao chép liên kết bài viết!');">
          <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
          <span>Sao chép liên kết</span>
        </button>
        <button type="button" class="mayhen_docs_btn_action" onclick="window.print();">
          <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
          <span>In / Lưu PDF</span>
        </button>
      </div>
    </div>
  </aside>
</div>
@endsection

