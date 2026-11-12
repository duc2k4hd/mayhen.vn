@extends('docs.layout')

@section('content')
<div class="mayhen_docs_body_layout">
  {{-- Left Sidebar --}}
  @include('docs.partials.sidebar')

  {{-- Center SOP Main Content --}}
  <main id="main-content" class="mayhen_docs_article_viewport">
    <article class="mayhen_docs_article mayhen_docs_article_admin">
      {{-- Breadcrumbs --}}
      <nav class="mayhen_docs_breadcrumb_nav" aria-label="Đường dẫn quy trình">
        <ol>
          <li><a href="{{ route('admin.docs.index') }}">Admin SOP</a></li>
          <li><span>/</span></li>
          <li><span class="mayhen_docs_bc_cat">{{ $categories[$currentCategory]['name'] ?? $currentCategory }}</span></li>
          <li><span>/</span></li>
          <li class="is-active" aria-current="page">{{ $article['title'] }}</li>
        </ol>
      </nav>

      {{-- Article Title & Admin SOP Meta --}}
      <header class="mayhen_docs_article_header">
        <div class="mayhen_docs_sop_badge_bar">
          <span class="mayhen_docs_badge_sop_id">{{ $article['id'] }}</span>
          <span class="mayhen_docs_badge_risk mayhen_docs_badge_risk_{{ strtolower($article['risk_level'] ?? 'medium') }}">
            RỦI RO: {{ $article['risk_level'] ?? 'MEDIUM' }}
          </span>
          <span class="mayhen_docs_badge_role">VAI TRÒ: {{ strtoupper($article['required_role'] ?? 'STAFF') }}</span>
        </div>

        <h1 class="mayhen_docs_article_title">{{ $article['title'] }}</h1>

        <div class="mayhen_docs_article_meta">
          <span class="mayhen_docs_meta_item">
            <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            <span>Hiệu lực từ: {{ $article['updated_at'] }}</span>
          </span>
          @if(!empty($article['mapped_route']))
            <span class="mayhen_docs_meta_item mayhen_docs_badge_route">
              <span>Endpoint: <code>{{ $article['mapped_route'] }}</code></span>
            </span>
          @endif
        </div>

        {{-- Summary Banner --}}
        @if(!empty($article['summary']))
          <div class="mayhen_docs_summary_box mayhen_docs_summary_box_admin">
            <strong>Mục tiêu nghiệp vụ:</strong> {{ $article['summary'] }}
          </div>
        @endif
      </header>

      {{-- SOP Body (Rendered from Markdown) --}}
      <div class="mayhen_docs_article_body">
        {!! $article['html_body'] !!}
      </div>

      {{-- Audit Sign-off Box --}}
      <div class="mayhen_docs_audit_signoff_box">
        <h4>Cam kết Tuân thủ Quy trình Vận hành</h4>
        <p>Mọi thao tác can thiệp theo quy trình này đều được lưu trữ vĩnh viễn vào bảng <code>audit_logs</code>. Nhân viên chịu trách nhiệm trực tiếp trước Kế toán trưởng và Ban Giám đốc về tính xác thực của chứng từ đối soát.</p>
      </div>
    </article>
  </main>

  {{-- Right Sidebar: Sticky Table of Contents (TOC) --}}
  <aside class="mayhen_docs_toc_sidebar" aria-label="Mục lục quy trình">
    <div class="mayhen_docs_toc_sticky">
      <span class="mayhen_docs_toc_title">CÁC BƯỚC THAO TÁC (SOP)</span>
      @if(!empty($article['toc']))
        <ul class="mayhen_docs_toc_list">
          @foreach($article['toc'] as $item)
            <li class="mayhen_docs_toc_item_h{{ $item['level'] }}">
              <a href="#{{ $item['id'] }}" class="mayhen_docs_toc_link">{{ $item['text'] }}</a>
            </li>
          @endforeach
        </ul>
      @else
        <p class="mayhen_docs_toc_empty">Quy trình ngắn, không có đề mục phụ.</p>
      @endif

      <div class="mayhen_docs_toc_actions">
        <button type="button" class="mayhen_docs_btn_action" onclick="navigator.clipboard.writeText(window.location.href); alert('Đã sao chép liên kết quy trình SOP!');">
          <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
          <span>Sao chép liên kết</span>
        </button>
        <button type="button" class="mayhen_docs_btn_action" onclick="window.print();">
          <svg class="mayhen_docs_icon" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
          <span>In tài liệu bàn giao</span>
        </button>
      </div>
    </div>
  </aside>
</div>
@endsection

