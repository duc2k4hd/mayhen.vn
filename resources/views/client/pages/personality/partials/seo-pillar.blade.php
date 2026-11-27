@if(! empty($seoPillar))
    @foreach((array) ($seoPillar['schemas'] ?? []) as $schemaObj)
        <script type="application/ld+json">{!! json_encode($schemaObj, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endforeach

    <section class="mayhen_personality_section mayhen_personality_seo_pillar" aria-label="Kiến thức chuyên sâu và câu hỏi thường gặp">
        <div class="mayhen_personality_container">
            {{-- Thanh Meta E-E-A-T & Mục lục nhảy nhanh (TOC) --}}
            <div class="mayhen_personality_seo_meta">
                <span><strong>Biên tập:</strong> {{ $seoPillar['meta']['author'] ?? 'Mây Hẹn' }}</span>
                <span>•</span>
                <span><strong>Cập nhật:</strong> {{ $seoPillar['meta']['updated'] ?? date('d/m/Y') }}</span>
                <span>•</span>
                <span>{{ $seoPillar['meta']['reading_time'] ?? '5 phút đọc' }}</span>
                @if(! empty($seoPillar['meta']['badge']))
                    <span class="mayhen_personality_badge_pilot">{{ $seoPillar['meta']['badge'] }}</span>
                @endif
            </div>

            @if(! empty($seoPillar['sections']))
                <nav class="mayhen_personality_toc" aria-label="Mục lục bài viết">
                    <div class="mayhen_personality_toc_title">Mục lục nhanh</div>
                    <ol class="mayhen_personality_toc_list">
                        @foreach($seoPillar['sections'] as $sec)
                            <li><a href="#{{ $sec['id'] }}">{{ $sec['heading'] }}</a></li>
                        @endforeach
                        @if(! empty($seoPillar['faqs']))
                            <li><a href="#cau-hoi-thuong-gap">Câu hỏi thường gặp (FAQ)</a></li>
                        @endif
                    </ol>
                </nav>

                <article class="mayhen_personality_editorial_body">
                    @foreach($seoPillar['sections'] as $sec)
                        <div id="{{ $sec['id'] }}" class="mayhen_personality_editorial_block">
                            <h2 class="mayhen_personality_h2">{{ $sec['heading'] }}</h2>
                            @foreach((array) ($sec['paragraphs'] ?? []) as $p)
                                <p>{{ $p }}</p>
                            @endforeach
                            @if(! empty($sec['bullets']))
                                <ul>
                                    @foreach($sec['bullets'] as $b)
                                        <li>{{ $b }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </article>
            @endif

            @if(! empty($seoPillar['faqs']))
                <div id="cau-hoi-thuong-gap" class="mayhen_personality_faq_wrap">
                    <span class="mayhen_personality_kicker">Giải đáp minh bạch</span>
                    <h2 class="mayhen_personality_h2">Câu hỏi thường gặp</h2>
                    <div class="mayhen_personality_faq_list">
                        @foreach($seoPillar['faqs'] as $idx => $faq)
                            <details class="mayhen_personality_faq_item" {{ $idx === 0 ? 'open' : '' }}>
                                <summary class="mayhen_personality_faq_q">{{ $faq['q'] }}</summary>
                                <div class="mayhen_personality_faq_a">{{ $faq['a'] }}</div>
                            </details>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Cụm liên kết nội bộ chuẩn SEO Topic Cluster --}}
            <div class="mayhen_personality_cluster_links">
                <div class="mayhen_personality_toc_title">Khám phá thêm trong hệ thống Bản Sắc Tâm Hồn</div>
                <div class="mayhen_personality_cluster_pills">
                    <a href="{{ route('personality.index') }}">Trắc nghiệm 16 nhóm tính cách</a>
                    <a href="{{ route('personality.big_five') }}">Trắc nghiệm Big Five (OCEAN)</a>
                    <a href="{{ route('personality.gallery') }}">Triển lãm 3D 16 hình tượng</a>
                    <a href="{{ route('personality.love') }}">Tính cách cặp đôi & Tình duyên</a>
                    <a href="{{ route('personality.cognitive_functions') }}">8 Chức năng nhận thức</a>
                    <a href="{{ route('personality.ei_guide') }}">Hướng nội & Hướng ngoại (E/I)</a>
                    <a href="{{ route('personality.methodology') }}">Phương pháp & Minh bạch</a>
                </div>
            </div>
        </div>
    </section>
@endif
