@if(! empty($seoPillar))
    @foreach((array) ($seoPillar['schemas'] ?? []) as $schemaObj)
        <script type="application/ld+json">{!! json_encode($schemaObj, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
    @endforeach

    <section class="mayhen_personality_section mayhen_personality_seo_pillar" aria-label="Kiến thức chuyên sâu và câu hỏi thường gặp">
        <div class="mayhen_personality_container">
            <div class="mayhen_personality_seo_layout">
                <div>
                    <div class="mayhen_personality_editorial_body">
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
                            @foreach($seoPillar['sections'] as $idx => $sec)
                                <div id="{{ $sec['id'] }}" class="mayhen_personality_editorial_block">
                                    <span class="mayhen_personality_chapter_num">Phần {{ str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT) }}</span>
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
                        @endif
                    </div>

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
                </div>

                <aside class="mayhen_personality_seo_aside">
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
                    @endif

                    <div class="mayhen_personality_card" style="background:#1C1917; color:#FAF5EB; border-color:rgba(207,168,96,0.4);">
                        <span class="mayhen_personality_kicker" style="color:#CFA860;">Bắt đầu miễn phí</span>
                        <h3 class="mayhen_personality_h3" style="color:#FAF5EB;">Khám phá Bản Sắc Tâm Hồn</h3>
                        <p style="font-size:0.86rem; line-height:1.6; color:#D6CBB8; margin:6px 0 16px;">
                            Chấm điểm xác định 100%, xem căn cứ từ chính câu trả lời của bạn và tải thẻ nghệ thuật PNG.
                        </p>
                        <a href="{{ route('personality.start', ['method' => 'mayhen_16types_standard_v1']) }}" class="mayhen_personality_btn mayhen_personality_btn--primary mayhen_personality_btn--sm" style="width:100%; background:#CFA860; color:#1C1917;">
                            Làm bài 64 câu ngay →
                        </a>
                    </div>

                    <div class="mayhen_personality_toc">
                        <div class="mayhen_personality_toc_title">Chuyên mục liên quan</div>
                        <div class="mayhen_personality_cluster_pills">
                            <a href="{{ route('personality.index') }}">Trắc nghiệm 16 nhóm</a>
                            <a href="{{ route('personality.big_five') }}">Big Five (OCEAN)</a>
                            <a href="{{ route('personality.gallery') }}">Triển lãm 3D 16 nhóm</a>
                            <a href="{{ route('personality.love') }}">Tính cách cặp đôi</a>
                            <a href="{{ route('personality.cognitive_functions') }}">8 Chức năng nhận thức</a>
                            <a href="{{ route('personality.ei_guide') }}">Hướng nội & Hướng ngoại</a>
                            <a href="{{ route('personality.methodology') }}">Phương pháp đo lường</a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endif
