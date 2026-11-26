@extends('client.layouts.master')

@section('title', $article['seo_title'])
@section('meta_description', $article['meta_description'])
@section('canonical', route('tarot.card_detail', $card['slug']))
@section('og_type', 'article')
@if (! empty($article['image_url']))
    @section('og_image', $article['image_url'])
@endif

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/tarot.css') }}?v={{ filemtime(public_path('assets/client/css/pages/tarot.css')) }}">
@endpush

@push('structured_data')
    <script type="application/ld+json">{!! json_encode($article['schemas'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endpush

@section('content')
@php
    $domains = $card['domains'] ?? [];
    $yesNo = $article['yes_no'] ?? [];
@endphp

<div class="mayhen_tarot_page">
    <!-- 1. Editorial SEO Header & Breadcrumb -->
    <section class="mayhen_tarot_result_hero">
        <div class="mayhen_tarot_container">
            <nav class="mayhen_tarot_breadcrumb" aria-label="Đường dẫn">
                <a href="{{ route('home') }}">Trang chủ</a>
                <span>›</span>
                <a href="{{ route('tarot.index') }}">Xem Tarot Online</a>
                <span>›</span>
                <a href="{{ route('tarot.library') }}">Bách Khoa 78 Lá Bài</a>
                <span>›</span>
                <span>{{ $card['name_en'] }} ({{ $card['name_vi'] }})</span>
            </nav>

            <div class="mayhen_tarot_meta_row">
                <span class="mayhen_tarot_badge">{{ $card['arcana'] === 'major' ? '22 Ẩn Chính · Major Arcana' : '56 Ẩn Phụ · ' . ($card['suit_label_vi'] ?? '') }}</span>
                <span class="mayhen_tarot_badge">Nguyên tố: {{ $card['element'] }}</span>
                @if (! empty($card['roman']))
                    <span class="mayhen_tarot_badge">Số thứ tự RWS: {{ $card['roman'] }}</span>
                @endif
                <span class="mayhen_tarot_badge">⏱ {{ $article['reading_minutes'] }} phút đọc</span>
            </div>

            <h1 class="mayhen_tarot_title" style="font-size: clamp(24px, 3.3vw, 38px); max-width: 920px;">
                {{ $article['h1'] }}
            </h1>
            <p class="mayhen_tarot_subtitle" style="margin: 0; max-width: 820px;">
                {{ $article['meta_description'] }}
            </p>
        </div>
    </section>

    <div class="mayhen_tarot_container" style="margin-top: 28px;">
        <div class="mayhen_tarot_report_grid">
            <!-- Left Main Long-Form SEO Article -->
            <main>
                <!-- Section 1: Tổng quan & Hình ảnh gốc Rider-Waite-Smith -->
                <article class="mayhen_tarot_book_card" id="tong-quan">
                    <div class="mayhen_tarot_kicker">01 · TỔNG QUAN &amp; BIỂU TƯỢNG GỐC 1909</div>
                    <h2>{{ $card['name_en'] }} là gì? Tổng quan &amp; biểu tượng Rider–Waite–Smith</h2>

                    <p class="mayhen_tarot_article_p">{{ $article['intro_lead'] }}</p>

                    <div class="mayhen_tarot_article_hero_split">
                        <figure class="mayhen_tarot_rws_figure">
                            @if (! empty($article['image_url']))
                                <img src="{{ $article['image_url'] }}"
                                     alt="{{ $article['image_alt'] }}"
                                     width="240"
                                     height="412"
                                     loading="eager"
                                     decoding="async"
                                     class="mayhen_tarot_rws_img">
                            @else
                                <div class="mayhen_tarot_card_frame" style="width: 210px; height: 348px;" data-tarot-card-art='@json($card)'></div>
                            @endif
                            <figcaption>{{ $article['image_caption'] }}</figcaption>
                        </figure>

                        <div class="mayhen_tarot_infobox">
                            <h3>Thông số chuẩn Rider–Waite–Smith</h3>
                            <dl class="mayhen_tarot_infobox_list">
                                <div><dt>Tên tiếng Anh</dt><dd><strong>{{ $card['name_en'] }}</strong></dd></div>
                                <div><dt>Tên tiếng Việt</dt><dd>{{ $card['name_vi'] }}</dd></div>
                                @if (! empty($card['aliases']))
                                    <div><dt>Tên gọi khác</dt><dd>{{ implode(', ', $card['aliases']) }}</dd></div>
                                @endif
                                <div><dt>Phân nhóm</dt><dd>{{ $card['arcana'] === 'major' ? 'Ẩn Chính (Major Arcana)' : 'Ẩn Phụ — ' . ($card['suit_label_vi'] ?? '') }}</dd></div>
                                <div><dt>Nguyên tố</dt><dd>{{ $card['element'] }}</dd></div>
                                <div><dt>Từ khóa Xuôi</dt><dd>{{ implode(' · ', $card['keywords_upright'] ?? []) }}</dd></div>
                                <div><dt>Từ khóa Ngược</dt><dd>{{ implode(' · ', $card['keywords_reversed'] ?? []) }}</dd></div>
                            </dl>
                        </div>
                    </div>

                    <p class="mayhen_tarot_article_p">{{ $article['suit_context'] }}</p>

                    @if (! empty($card['symbols']))
                        <h3 style="margin-top: 18px;">Giải mã hệ thống biểu tượng trên lá bài {{ $card['name_en'] }}</h3>
                        <ul class="mayhen_tarot_action_list">
                            @foreach ($card['symbols'] as $sym)
                                <li>{{ $sym }}</li>
                            @endforeach
                        </ul>
                    @endif
                </article>

                <!-- Section 2: Ý nghĩa Xuôi -->
                <article class="mayhen_tarot_book_card" id="y-nghia-xuoi">
                    <div class="mayhen_tarot_kicker">02 · CHIỀU XUÔI (UPRIGHT)</div>
                    <h2>Ý nghĩa lá bài {{ $card['name_en'] }} Xuôi (Upright)</h2>
                    <p class="mayhen_tarot_kw_strip mayhen_tarot_kw_upright">
                        <strong>Từ khóa cốt lõi:</strong> {{ implode(' · ', $card['keywords_upright'] ?? []) }}
                    </p>
                    <p class="mayhen_tarot_article_p">{{ $card['upright_meaning'] ?? '' }}</p>
                    @if (! empty($domains['general']))
                        <p class="mayhen_tarot_article_p"><strong>Thông điệp tổng quan:</strong> {{ $domains['general'] }}</p>
                    @endif
                </article>

                <!-- Section 3: Ý nghĩa Ngược -->
                <article class="mayhen_tarot_book_card" id="y-nghia-nguoc">
                    <div class="mayhen_tarot_kicker">03 · CHIỀU NGƯỢC (REVERSED)</div>
                    <h2>Ý nghĩa lá bài {{ $card['name_en'] }} Ngược (Reversed) &amp; cách đọc đúng</h2>
                    <p class="mayhen_tarot_kw_strip mayhen_tarot_kw_reversed">
                        <strong>Từ khóa chiều ngược:</strong> {{ implode(' · ', $card['keywords_reversed'] ?? []) }}
                    </p>
                    <p class="mayhen_tarot_article_p">{{ $card['reversed_meaning'] ?? '' }}</p>
                    <p class="mayhen_tarot_article_p" style="color: #57534E;">{{ $article['reversed_guide'] }}</p>
                </article>

                <!-- Section 4: Tình yêu, Hôn nhân & Gia đình -->
                <article class="mayhen_tarot_book_card" id="tinh-yeu-hon-nhan">
                    <div class="mayhen_tarot_kicker">04 · TÌNH YÊU, HÔN NHÂN &amp; GIA ĐÌNH</div>
                    <h2>{{ $card['name_en'] }} trong Tình yêu, Hôn nhân &amp; Tổ ấm</h2>
                    <div class="mayhen_tarot_domain_grid">
                        @if (! empty($domains['love']))
                            <div class="mayhen_tarot_domain_box">
                                <h3>Tình yêu &amp; Hẹn hò</h3>
                                <p>{{ $domains['love'] }}</p>
                            </div>
                        @endif
                        @if (! empty($domains['marriage']))
                            <div class="mayhen_tarot_domain_box">
                                <h3>Hôn nhân &amp; Chuẩn bị kết hôn</h3>
                                <p>{{ $domains['marriage'] }}</p>
                            </div>
                        @endif
                        @if (! empty($domains['family']))
                            <div class="mayhen_tarot_domain_box">
                                <h3>Gia đình &amp; Người thân</h3>
                                <p>{{ $domains['family'] }}</p>
                            </div>
                        @endif
                    </div>
                </article>

                <!-- Section 5: Công việc, Tài chính & Bản thân -->
                <article class="mayhen_tarot_book_card" id="cong-viec-tai-chinh">
                    <div class="mayhen_tarot_kicker">05 · CÔNG VIỆC, TÀI CHÍNH &amp; PHÁT TRIỂN</div>
                    <h2>{{ $card['name_en'] }} trong Công việc, Tài chính &amp; Đời sống</h2>
                    <div class="mayhen_tarot_domain_grid">
                        @if (! empty($domains['work']))
                            <div class="mayhen_tarot_domain_box">
                                <h3>Công việc &amp; Sự nghiệp</h3>
                                <p>{{ $domains['work'] }}</p>
                            </div>
                        @endif
                        @if (! empty($domains['finance']))
                            <div class="mayhen_tarot_domain_box">
                                <h3>Tài chính &amp; Quản trị</h3>
                                <p>{{ $domains['finance'] }}</p>
                            </div>
                        @endif
                        @if (! empty($domains['growth']))
                            <div class="mayhen_tarot_domain_box">
                                <h3>Phát triển bản thân</h3>
                                <p>{{ $domains['growth'] }}</p>
                            </div>
                        @endif
                        @if (! empty($domains['balance']))
                            <div class="mayhen_tarot_domain_box">
                                <h3>Cân bằng tinh thần</h3>
                                <p>{{ $domains['balance'] }}</p>
                            </div>
                        @endif
                    </div>
                </article>

                <!-- Section 6: Yes / No có điều kiện -->
                <article class="mayhen_tarot_book_card" id="co-hay-khong">
                    <div class="mayhen_tarot_kicker">06 · TRẢI BÀI CÓ / KHÔNG (YES / NO)</div>
                    <h2>Lá bài {{ $card['name_en'] }} là Có hay Không?</h2>
                    <div class="mayhen_tarot_yesno_banner">
                        <strong>Xu hướng tham khảo:</strong> {{ $yesNo['signal'] ?? '' }}
                    </div>
                    <p class="mayhen_tarot_article_p"><strong>Khi lá bài Xuôi:</strong> {{ $yesNo['upright_note'] ?? '' }}</p>
                    <p class="mayhen_tarot_article_p"><strong>Khi lá bài Ngược:</strong> {{ $yesNo['reversed_note'] ?? '' }}</p>
                </article>

                <!-- Section 7: Hiểu lầm thường gặp & Câu hỏi tự vấn -->
                <article class="mayhen_tarot_book_card" id="hieu-lam-tu-van">
                    <div class="mayhen_tarot_kicker">07 · GÓC NHÌN NHÂN VĂN</div>
                    <h2>Những hiểu lầm thường gặp &amp; Câu hỏi tự vấn</h2>
                    @if (! empty($card['common_misconception']))
                        <div class="mayhen_tarot_callout_box">
                            <strong>Hiểu lầm cần tránh:</strong> {{ $card['common_misconception'] }}
                        </div>
                    @endif
                    @if (! empty($card['reflection_question']))
                        <div class="mayhen_tarot_reflection_box">
                            <strong>Câu hỏi tự vấn khi rút trúng {{ $card['name_en'] }}:</strong>
                            <p>“{{ $card['reflection_question'] }}”</p>
                        </div>
                    @endif
                </article>

                <!-- Section 8: FAQ riêng cho lá bài -->
                <article class="mayhen_tarot_book_card" id="faq-la-bai">
                    <div class="mayhen_tarot_kicker">08 · CÂU HỎI THƯỜNG GẶP (FAQ)</div>
                    <h2>Giải đáp nhanh về lá bài {{ $card['name_en'] }}</h2>
                    <div class="mayhen_tarot_faq_list">
                        @foreach (($article['faqs'] ?? []) as $faq)
                            <div class="mayhen_tarot_faq_item">
                                <h3>{{ $faq['q'] }}</h3>
                                <p>{{ $faq['a'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </article>

                <!-- Related Cards Internal Linking -->
                @if (! empty($article['related_cards']))
                    <article class="mayhen_tarot_book_card">
                        <div class="mayhen_tarot_kicker">LIÊN KẾT CÙNG BỘ BÀI</div>
                        <h2>Các lá bài cùng nhóm {{ $card['suit_label_vi'] ?? '' }} nên đọc thêm</h2>
                        <div class="mayhen_tarot_related_grid">
                            @foreach ($article['related_cards'] as $rel)
                                <a href="{{ route('tarot.card_detail', $rel['slug']) }}" class="mayhen_tarot_related_card">
                                    @if (! empty($rel['has_local_image']))
                                        <img src="{{ asset($rel['image_rel']) }}"
                                             alt="Lá bài {{ $rel['name_en'] }} ({{ $rel['name_vi'] }})"
                                             width="100"
                                             height="172"
                                             loading="lazy"
                                             decoding="async"
                                             class="mayhen_tarot_rws_thumb">
                                    @endif
                                    <strong>{{ $rel['name_en'] }}</strong>
                                    <span>{{ $rel['name_vi'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </article>
                @endif
            </main>

            <!-- Right Sticky Sidebar -->
            <aside class="mayhen_tarot_sidebar">
                <div class="mayhen_tarot_book_card mayhen_tarot_sidebar_toc">
                    <div class="mayhen_tarot_kicker">MỤC LỤC BÀI VIẾT</div>
                    <h3 style="margin-bottom: 10px;">Nội dung chính</h3>
                    <nav class="mayhen_tarot_toc_links" aria-label="Mục lục bài viết">
                        @foreach ($article['toc'] as $idx => $item)
                            <a href="#{{ $item['id'] }}" class="mayhen_tarot_toc_link">
                                <span>{{ str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="mayhen_tarot_book_card" style="text-align: center; background: #142D25; color: #FAF5EB; border-color: rgba(200,160,85,0.45);">
                    <div class="mayhen_tarot_kicker" style="color: #E7D2A3;">TRẢI BÀI MIỄN PHÍ</div>
                    <h3 style="color: #FFFDF8;">Muốn xem {{ $card['name_en'] }} xuất hiện trong trải bài của bạn?</h3>
                    <p style="font-size: 13.5px; color: rgba(250,245,235,0.82); margin: 0 0 16px;">
                        Tự tay rút bài trên vòng cung 3D chuẩn 78 lá Rider–Waite–Smith với thuật toán Fisher–Yates CSPRNG minh bạch.
                    </p>
                    <a href="{{ route('tarot.index') }}" class="mayhen_tarot_btn_primary" style="width: 100%; box-sizing: border-box;">
                        ✦ Xem Tarot Online Miễn Phí →
                    </a>
                </div>

                <div class="mayhen_tarot_book_card">
                    <div class="mayhen_tarot_kicker">ĐIỀU HƯỚNG 78 LÁ BÀI</div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        @if (! empty($adjacent['prev']))
                            <a href="{{ route('tarot.card_detail', $adjacent['prev']['slug']) }}" class="mayhen_tarot_btn_outline_dark" style="justify-content: space-between;">
                                <span>← Lá trước</span>
                                <strong>{{ $adjacent['prev']['name_en'] }}</strong>
                            </a>
                        @endif
                        @if (! empty($adjacent['next']))
                            <a href="{{ route('tarot.card_detail', $adjacent['next']['slug']) }}" class="mayhen_tarot_btn_outline_dark" style="justify-content: space-between;">
                                <strong>{{ $adjacent['next']['name_en'] }}</strong>
                                <span>Lá sau →</span>
                            </a>
                        @endif
                        <a href="{{ route('tarot.library') }}" class="mayhen_tarot_btn_editorial" style="justify-content: center;">
                            📖 Xem trọn bộ 78 lá bài
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/client/js/pages/tarot.js') }}?v={{ filemtime(public_path('assets/client/js/pages/tarot.js')) }}" defer></script>
@endpush
