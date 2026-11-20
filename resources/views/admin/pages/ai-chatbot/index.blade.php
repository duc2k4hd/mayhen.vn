@extends('admin.layouts.master')

@section('title', 'Quản Lý Trợ Lý AI')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/ai-chatbot.css') }}?v={{ file_exists(public_path('assets/admin/css/pages/ai-chatbot.css')) ? filemtime(public_path('assets/admin/css/pages/ai-chatbot.css')) : time() }}">
@endpush

@section('content')
<div class="mayhen_admin_ai_wrap">
    {{-- HEADER GỌN SẠCH & 2 HÀNH ĐỘNG CHÍNH --}}
    <header class="mayhen_admin_ai_header">
        <div class="mayhen_admin_ai_header_left">
            <div class="mayhen_admin_ai_title_row">
                <h1 class="mayhen_admin_ai_title">Quản Lý Trợ Lý AI</h1>
                @if($aiRuntimeConfig['kill_switch'])
                    <span class="mayhen_admin_ai_pill mayhen_admin_ai_pill_danger">
                        <span class="mayhen_admin_ai_dot"></span>Đang tạm tắt AI
                    </span>
                @elseif($aiRuntimeConfig['enabled'] && $aiRuntimeConfig['model'] !== '')
                    <span class="mayhen_admin_ai_pill mayhen_admin_ai_pill_ok">
                        <span class="mayhen_admin_ai_dot"></span>Đang hoạt động
                    </span>
                @else
                    <span class="mayhen_admin_ai_pill mayhen_admin_ai_pill_warn">
                        <span class="mayhen_admin_ai_dot"></span>Chưa chọn model
                    </span>
                @endif
            </div>
            <p class="mayhen_admin_ai_subtitle">
                Dạy AI hiểu đúng về Mây Hẹn, duyệt kiến thức mới AI tự đề xuất và theo dõi nhu cầu khách hàng.
            </p>
        </div>

        <div class="mayhen_admin_ai_header_cta">
            @if($tab === 'approved')
                <button type="button" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary" id="mayhenOpenNewKnowledgeBtn">
                    + Thêm kiến thức mới
                </button>
            @else
                <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'approved', 'new' => 1]) }}" class="mayhen_admin_ai_btn">
                    + Thêm kiến thức
                </a>
            @endif

            @if($tab !== 'learning')
                <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'learning']) }}" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_accent">
                    Cho AI tự học
                    @if($stats['pending_count'] > 0)
                        <span class="mayhen_admin_ai_count_dot">{{ $stats['pending_count'] }}</span>
                    @endif
                </a>
            @endif
        </div>
    </header>

    {{-- THÔNG BÁO KẾT QUẢ --}}
    @if(session('success'))
        <div class="mayhen_admin_ai_alert mayhen_admin_ai_alert_success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mayhen_admin_ai_alert mayhen_admin_ai_alert_danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="mayhen_admin_ai_alert mayhen_admin_ai_alert_danger">{{ $errors->first() }}</div>
    @endif

    {{-- 4 CHỈ SỐ TỔNG QUAN SÚC TÍCH --}}
    <section class="mayhen_admin_ai_stats" aria-label="Tổng quan Trợ lý AI">
        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'approved']) }}" class="mayhen_admin_ai_stat_card">
            <span class="mayhen_admin_ai_stat_label">Kiến thức đang chạy</span>
            <strong class="mayhen_admin_ai_stat_value">{{ number_format($stats['approved_count']) }}</strong>
            <span class="mayhen_admin_ai_stat_hint">{{ $stats['outdated_count'] > 0 ? $stats['outdated_count'].' mục đang tạm ẩn' : 'Tất cả đang sẵn sàng' }}</span>
        </a>
        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'learning']) }}" class="mayhen_admin_ai_stat_card {{ $stats['pending_count'] > 0 ? 'mayhen_admin_ai_stat_card_highlight' : '' }}">
            <span class="mayhen_admin_ai_stat_label">Chờ bạn duyệt</span>
            <strong class="mayhen_admin_ai_stat_value {{ $stats['pending_count'] > 0 ? 'mayhen_admin_ai_stat_warn' : '' }}">{{ number_format($stats['pending_count']) }}</strong>
            <span class="mayhen_admin_ai_stat_hint">{{ $stats['pending_count'] > 0 ? 'Bấm vào để duyệt nhanh' : 'Không có mục chờ duyệt' }}</span>
        </a>
        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'quality']) }}" class="mayhen_admin_ai_stat_card">
            <span class="mayhen_admin_ai_stat_label">Khách đã trò chuyện</span>
            <strong class="mayhen_admin_ai_stat_value">{{ number_format($stats['total_chats']) }}</strong>
            <span class="mayhen_admin_ai_stat_hint">Giải quyết tốt: {{ $stats['resolved_rate'] }}%</span>
        </a>
        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'quality']) }}" class="mayhen_admin_ai_stat_card">
            <span class="mayhen_admin_ai_stat_label">Đánh giá &amp; Bảo vệ</span>
            <strong class="mayhen_admin_ai_stat_value">{{ $stats['helpful_count'] }} <small class="mayhen_admin_ai_stat_sub">hữu ích</small></strong>
            <span class="mayhen_admin_ai_stat_hint">Đã chặn {{ $stats['blocked_security_count'] }} tin nhắn xấu</span>
        </a>
    </section>

    {{-- THANH 4 TAB NGẮN GỌN, DỄ HIỂU --}}
    <nav class="mayhen_admin_ai_tabs" aria-label="Điều hướng quản lý AI">
        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'approved']) }}" class="mayhen_admin_ai_tab {{ $tab === 'approved' ? 'mayhen_admin_ai_tab_active' : '' }}">
            <span>Kiến thức AI</span>
            <span class="mayhen_admin_ai_tab_num">{{ $stats['approved_count'] }}</span>
        </a>
        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'learning']) }}" class="mayhen_admin_ai_tab {{ $tab === 'learning' ? 'mayhen_admin_ai_tab_active' : '' }}">
            <span>Tự học &amp; Duyệt</span>
            @if($stats['pending_count'] > 0)
                <span class="mayhen_admin_ai_tab_num mayhen_admin_ai_tab_num_warn">{{ $stats['pending_count'] }}</span>
            @endif
        </a>
        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'quality']) }}" class="mayhen_admin_ai_tab {{ $tab === 'quality' ? 'mayhen_admin_ai_tab_active' : '' }}">
            <span>Lịch sử chat</span>
            <span class="mayhen_admin_ai_tab_num">{{ $stats['total_chats'] }}</span>
        </a>
        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'governance']) }}" class="mayhen_admin_ai_tab {{ $tab === 'governance' ? 'mayhen_admin_ai_tab_active' : '' }}">
            <span>Cài đặt AI</span>
        </a>
    </nav>

    {{-- =====================================================================
         TAB 1: KIẾN THỨC AI (GỌN GÀNG — CHỈ ĐỌC MẶC ĐỊNH, BẤM SỬA MỚI MỞ)
         ===================================================================== --}}
    @if($tab === 'approved')
        @php
            $openCreateByDefault = request()->boolean('new') || request()->filled('prefill_q') || $errors->any();
        @endphp

        {{-- Hộp Thêm Kiến Thức Mới (ẩn gọn mặc định để không rối màn hình) --}}
        <section class="mayhen_admin_ai_card mayhen_admin_ai_create_card" id="mayhenAdminTrainFormCard" @if(! $openCreateByDefault) hidden @endif>
            <div class="mayhen_admin_ai_card_head">
                <div>
                    <h2 class="mayhen_admin_ai_card_title">Thêm kiến thức mới cho AI</h2>
                    <p class="mayhen_admin_ai_card_desc">Viết ngắn gọn sự thật chính xác. Khi khách hỏi, AI sẽ tự diễn đạt tự nhiên và dẫn khách dùng dịch vụ.</p>
                </div>
                <button type="button" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_ghost" id="mayhenCloseNewKnowledgeBtn">Đóng</button>
            </div>

            <form method="POST" action="{{ route('admin.ai_chatbot.entries.store') }}" class="mayhen_admin_ai_form_grid">
                @csrf
                <label class="mayhen_admin_ai_field">
                    <span>Nhóm chủ đề</span>
                    <select name="category" required>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="mayhen_admin_ai_field">
                    <span>Độ ưu tiên (1 – 10)</span>
                    <input type="number" name="priority" min="1" max="10" value="{{ old('priority', 7) }}">
                </label>

                <label class="mayhen_admin_ai_field mayhen_admin_ai_field_full">
                    <span>Câu hỏi khách hàng thường hỏi</span>
                    <input type="text" name="title" id="mayhenAdminTrainTitle" maxlength="190" required value="{{ old('title', request('prefill_q')) }}" placeholder="Ví dụ: Thiệp cưới online Mây Hẹn có gửi qua Zalo được không?">
                </label>

                <label class="mayhen_admin_ai_field mayhen_admin_ai_field_full">
                    <span>Thông tin trả lời chính xác</span>
                    <textarea name="answer" rows="3" maxlength="2000" required placeholder="Nhập sự thật chuẩn xác (2–4 câu). AI sẽ dựa vào đây để tư vấn tự nhiên...">{{ old('answer') }}</textarea>
                </label>

                <label class="mayhen_admin_ai_field mayhen_admin_ai_field_full">
                    <span>Từ khóa liên quan (không bắt buộc, cách nhau dấu phẩy)</span>
                    <input type="text" name="keywords" maxlength="500" value="{{ old('keywords') }}" placeholder="zalo, gửi thiệp, chia sẻ link">
                </label>

                <details class="mayhen_admin_ai_advanced_box mayhen_admin_ai_field_full">
                    <summary>Tùy chọn gắn mẫu thiệp, bài hướng dẫn &amp; thời hạn (không bắt buộc)</summary>
                    <div class="mayhen_admin_ai_form_grid mayhen_admin_ai_advanced_inner">
                        <label class="mayhen_admin_ai_field">
                            <span>Gắn kèm Mẫu Thiệp</span>
                            <select name="template_slug">
                                <option value="">— Không gắn —</option>
                                @foreach($templates as $tpl)
                                    <option value="{{ $tpl->slug }}" @selected(old('template_slug') === $tpl->slug)>{{ $tpl->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="mayhen_admin_ai_field">
                            <span>Gắn kèm Tài liệu Hướng dẫn</span>
                            <select name="doc_url">
                                <option value="">— Không gắn —</option>
                                @foreach($clientDocOptions as $docOpt)
                                    <option value="{{ $docOpt['url'] }}" @selected(old('doc_url') === $docOpt['url'])>{{ $docOpt['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="mayhen_admin_ai_field">
                            <span>Áp dụng từ ngày</span>
                            <input type="date" name="effective_from" value="{{ old('effective_from') }}">
                        </label>
                        <label class="mayhen_admin_ai_field">
                            <span>Hết hạn vào ngày</span>
                            <input type="date" name="effective_until" value="{{ old('effective_until') }}">
                        </label>
                    </div>
                </details>

                <div class="mayhen_admin_ai_form_actions">
                    <input type="hidden" name="is_active" value="1">
                    <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary">Lưu &amp; Áp dụng ngay</button>
                </div>
            </form>
        </section>

        {{-- Thanh Tìm kiếm & Lọc Gọn --}}
        <section class="mayhen_admin_ai_card">
            <form method="GET" action="{{ route('admin.ai_chatbot.index') }}" class="mayhen_admin_ai_filter_bar">
                <input type="hidden" name="tab" value="approved">
                <input type="text" name="q" value="{{ $search }}" placeholder="Tìm kiếm câu hỏi hoặc nội dung kiến thức...">
                <select name="category">
                    <option value="">Tất cả chủ đề</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="mayhen_admin_ai_btn">Tìm</button>
                @if($search !== '' || $category !== '')
                    <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'approved']) }}" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_ghost">Xóa lọc</a>
                @endif
            </form>

            {{-- Danh sách Kiến Thức dạng Clean Cards (đọc cực dễ, bấm Sửa mới bung form) --}}
            <div class="mayhen_admin_ai_list">
                @forelse($approvedEntries as $entry)
                    <article class="mayhen_admin_ai_row_card {{ $entry->is_outdated || ! $entry->is_active ? 'mayhen_admin_ai_row_card_muted' : '' }}">
                        <div class="mayhen_admin_ai_row_main">
                            <div class="mayhen_admin_ai_row_badges">
                                <span class="mayhen_admin_ai_tag">{{ $entry->category_label }}</span>
                                @if($entry->is_outdated || ! $entry->is_active)
                                    <span class="mayhen_admin_ai_tag mayhen_admin_ai_tag_danger">Đang tạm ẩn</span>
                                @else
                                    <span class="mayhen_admin_ai_tag mayhen_admin_ai_tag_ok">Đang chạy</span>
                                @endif
                                <span class="mayhen_admin_ai_meta">
                                    {{ $entry->source === 'ai_self_train' ? 'AI đề xuất (Đã duyệt)' : 'Admin nhập' }}
                                    • Đã dùng {{ $entry->usage_count }} lần
                                </span>
                            </div>

                            <h3 class="mayhen_admin_ai_row_title">{{ $entry->title }}</h3>
                            <p class="mayhen_admin_ai_row_answer">{{ $entry->answer }}</p>

                            @if($entry->keywords || $entry->template_slug || $entry->doc_url)
                                <div class="mayhen_admin_ai_row_extras">
                                    @if($entry->template_slug)
                                        <span class="mayhen_admin_ai_chip">Mẫu: {{ $entry->template_slug }}</span>
                                    @endif
                                    @if($entry->doc_url)
                                        <span class="mayhen_admin_ai_chip">Hướng dẫn: {{ $entry->doc_url }}</span>
                                    @endif
                                    @if($entry->keywords)
                                        <span class="mayhen_admin_ai_meta">Từ khóa: {{ \Illuminate\Support\Str::limit($entry->keywords, 70) }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="mayhen_admin_ai_row_actions">
                            <button type="button" class="mayhen_admin_ai_btn" data-toggle-target="mayhenEditBox_{{ $entry->id }}">Sửa</button>
                            <form method="POST" action="{{ route('admin.ai_chatbot.entries.outdated', $entry) }}" class="mayhen_admin_ai_inline_form">
                                @csrf
                                <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_ghost">
                                    {{ $entry->is_outdated ? 'Bật lại' : 'Tạm ẩn' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.ai_chatbot.entries.destroy', $entry) }}" class="mayhen_admin_ai_inline_form" data-confirm-delete="Bạn chắc chắn muốn xóa kiến thức này?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_danger_ghost">Xóa</button>
                            </form>
                        </div>

                        {{-- Khung sửa nhanh (ẩn cho tới khi bấm nút Sửa) --}}
                        <div class="mayhen_admin_ai_drawer_edit" id="mayhenEditBox_{{ $entry->id }}" hidden>
                            <form method="POST" action="{{ route('admin.ai_chatbot.entries.update', $entry) }}" class="mayhen_admin_ai_form_grid">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="category" value="{{ $entry->category }}">
                                <label class="mayhen_admin_ai_field mayhen_admin_ai_field_full">
                                    <span>Câu hỏi</span>
                                    <input type="text" name="title" value="{{ $entry->title }}" maxlength="190" required>
                                </label>
                                <label class="mayhen_admin_ai_field mayhen_admin_ai_field_full">
                                    <span>Nội dung trả lời</span>
                                    <textarea name="answer" rows="3" maxlength="2000" required>{{ $entry->answer }}</textarea>
                                </label>
                                <label class="mayhen_admin_ai_field">
                                    <span>Từ khóa</span>
                                    <input type="text" name="keywords" value="{{ $entry->keywords }}" maxlength="500">
                                </label>
                                <label class="mayhen_admin_ai_field">
                                    <span>Độ ưu tiên (1–10)</span>
                                    <input type="number" name="priority" value="{{ $entry->priority }}" min="1" max="10">
                                </label>
                                <label class="mayhen_admin_ai_field">
                                    <span>Gắn mẫu thiệp</span>
                                    <select name="template_slug">
                                        <option value="">— Không gắn —</option>
                                        @foreach($templates as $tpl)
                                            <option value="{{ $tpl->slug }}" @selected($entry->template_slug === $tpl->slug)>{{ $tpl->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="mayhen_admin_ai_field">
                                    <span>Gắn tài liệu hướng dẫn</span>
                                    <select name="doc_url">
                                        <option value="">— Không gắn —</option>
                                        @foreach($clientDocOptions as $docOpt)
                                            <option value="{{ $docOpt['url'] }}" @selected($entry->doc_url === $docOpt['url'])>{{ $docOpt['label'] }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <div class="mayhen_admin_ai_form_actions">
                                    <label class="mayhen_admin_ai_checkbox">
                                        <input type="checkbox" name="is_active" value="1" @checked($entry->is_active)>
                                        <span>Cho phép AI sử dụng mục này</span>
                                    </label>
                                    <div class="mayhen_admin_ai_inline_actions">
                                        <button type="button" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_ghost" data-toggle-target="mayhenEditBox_{{ $entry->id }}">Hủy</button>
                                        <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary">Lưu thay đổi</button>
                                    </div>
                                </div>
                            </form>

                            @if($entry->versions->count() > 1)
                                <details class="mayhen_admin_ai_versions_box">
                                    <summary>Khôi phục phiên bản cũ (đang ở v{{ $entry->version ?? 1 }} • {{ $entry->versions->count() }} bản lưu)</summary>
                                    <ul class="mayhen_admin_ai_version_list">
                                        @foreach($entry->versions as $ver)
                                            <li>
                                                <div>
                                                    <strong>Bản v{{ $ver->version }}</strong> ({{ $ver->created_at?->format('d/m/Y H:i') }}) — {{ $ver->change_reason ?: 'Đã lưu' }}
                                                    <div class="mayhen_admin_ai_meta">{{ \Illuminate\Support\Str::limit($ver->answer, 120) }}</div>
                                                </div>
                                                @if((int) $ver->version !== (int) $entry->version)
                                                    <form method="POST" action="{{ route('admin.ai_chatbot.entries.rollback', [$entry, $ver]) }}" class="mayhen_admin_ai_inline_form">
                                                        @csrf
                                                        <button type="submit" class="mayhen_admin_ai_btn">Khôi phục bản này</button>
                                                    </form>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="mayhen_admin_ai_empty_state">
                        <p>Chưa có kiến thức nào khớp bộ lọc.</p>
                        <button type="button" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary" id="mayhenEmptyAddBtn">+ Thêm kiến thức đầu tiên</button>
                    </div>
                @endforelse
            </div>

            <div class="mayhen_admin_ai_pagination">{{ $approvedEntries->links() }}</div>
        </section>
    @endif

    {{-- =====================================================================
         TAB 2: TỰ HỌC & DUYỆT KIẾN THỨC MỚI (1-CHẠM PRESET + DUYỆT SIÊU NHANH)
         ===================================================================== --}}
    @if($tab === 'learning')
        {{-- Khối 1: Cho AI tự học chia nhỏ (3 nút chọn nhanh cho người mới) --}}
        <section
            class="mayhen_admin_ai_card"
            id="mayhenAdminChunkedRunner"
            data-start-url="{{ route('admin.ai_chatbot.learning.start') }}"
            data-step-url-template="{{ route('admin.ai_chatbot.learning.step', ['run' => '__RUN_ID__']) }}"
            data-pause-url-template="{{ route('admin.ai_chatbot.learning.pause', ['run' => '__RUN_ID__']) }}"
            data-resume-url-template="{{ route('admin.ai_chatbot.learning.resume', ['run' => '__RUN_ID__']) }}"
            data-cancel-url-template="{{ route('admin.ai_chatbot.learning.cancel', ['run' => '__RUN_ID__']) }}"
            data-active-run='@json($activeChunkedRun)'
        >
            <div class="mayhen_admin_ai_card_head">
                <div>
                    <h2 class="mayhen_admin_ai_card_title">Cho AI Tự Học &amp; Kiểm Tra Chéo</h2>
                    <p class="mayhen_admin_ai_card_desc">
                        AI sẽ đọc mẫu thiệp, bảng giá, tài liệu hướng dẫn và nhu cầu khách hàng để tự đề xuất kiến thức mới. Chạy chia nhỏ từng bước nhẹ nhàng — không làm chậm web, an toàn cho cả hosting yếu.
                    </p>
                </div>
                <span class="mayhen_admin_ai_tag mayhen_admin_ai_tag_ok" id="mayhenRunnerStatusBadge">Sẵn sàng</span>
            </div>

            {{-- 3 Nút Chế Độ Chọn Nhanh (1 chạm cực dễ hiểu cho người mới) --}}
            <div class="mayhen_admin_ai_preset_grid" id="mayhenRunnerPresets">
                <button type="button" class="mayhen_admin_ai_preset_card" data-preset-rounds="1" data-preset-per-topic="1" data-preset-cooldown="2500">
                    <strong>Chạy nhanh (~1 phút)</strong>
                    <span>Đề xuất nhẹ ~5 câu hỏi mới, phù hợp kiểm tra nhanh.</span>
                </button>
                <button type="button" class="mayhen_admin_ai_preset_card mayhen_admin_ai_preset_active" data-preset-rounds="3" data-preset-per-topic="2" data-preset-cooldown="3500">
                    <strong>Tiêu chuẩn (~5 phút) • Khuyên dùng</strong>
                    <span>Cân bằng chất lượng và độ mượt cho shared hosting.</span>
                </button>
                <button type="button" class="mayhen_admin_ai_preset_card" data-preset-rounds="30" data-preset-per-topic="2" data-preset-cooldown="5000">
                    <strong>Chạy sâu (~1 giờ+)</strong>
                    <span>Tự nghỉ 5s/bước nhường CPU cho khách, chạy nền qua Cron kể cả khi tắt tab.</span>
                </button>
            </div>

            {{-- Tùy chỉnh nâng cao (ẩn gọn mặc định) --}}
            <details class="mayhen_admin_ai_advanced_box">
                <summary>Tùy chỉnh chi tiết chủ đề, số vòng &amp; tốc độ chạy</summary>
                <div class="mayhen_admin_ai_advanced_inner">
                    <div class="mayhen_admin_ai_checkbox_group">
                        @foreach($categories as $key => $label)
                            <label class="mayhen_admin_ai_checkbox">
                                <input type="checkbox" name="runner_topics" value="{{ $key }}" checked>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="mayhen_admin_ai_runner_controls_grid">
                        <label class="mayhen_admin_ai_field">
                            <span>Số câu hỏi / chủ đề / vòng</span>
                            <select id="mayhenRunnerPerTopic">
                                <option value="1">1 câu (Nhẹ nhất)</option>
                                <option value="2" selected>2 câu (Cân bằng)</option>
                                <option value="3">3 câu (Nhiều hơn)</option>
                            </select>
                        </label>
                        <label class="mayhen_admin_ai_field">
                            <span>Số vòng chạy</span>
                            <select id="mayhenRunnerRounds">
                                <option value="1">1 vòng (~1 phút)</option>
                                <option value="3" selected>3 vòng (~5 phút)</option>
                                <option value="10">10 vòng (~15 phút)</option>
                                <option value="30">30 vòng (~45–60 phút)</option>
                                <option value="60">60 vòng (~1.5–2 giờ)</option>
                            </select>
                        </label>
                        <label class="mayhen_admin_ai_field">
                            <span>Thời gian nghỉ giữa mỗi bước</span>
                            <select id="mayhenRunnerCooldown">
                                <option value="2000">2 giây (Server mạnh)</option>
                                <option value="2500">2.5 giây</option>
                                <option value="3500" selected>3.5 giây (Khuyên dùng)</option>
                                <option value="5000">5 giây (Hosting yếu)</option>
                                <option value="8000">8 giây (Siêu tiết kiệm)</option>
                            </select>
                        </label>
                    </div>
                </div>
            </details>

            <div class="mayhen_admin_ai_runner_actions">
                <button type="button" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary" id="mayhenRunnerStartBtn">
                    Bắt đầu cho AI tự học
                </button>
                <button type="button" class="mayhen_admin_ai_btn" id="mayhenRunnerPauseBtn" hidden>Tạm dừng</button>
                <button type="button" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary" id="mayhenRunnerResumeBtn" hidden>Tiếp tục chạy</button>
                <button type="button" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_danger_ghost" id="mayhenRunnerCancelBtn" hidden>Dừng hẳn</button>
                <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'learning']) }}" class="mayhen_admin_ai_btn" id="mayhenRunnerRefreshBtn" hidden>Cập nhật danh sách chờ duyệt</a>
            </div>

            {{-- Khung Tiến Độ Trực Tiếp --}}
            <div class="mayhen_admin_ai_runner_progress_panel" id="mayhenRunnerProgressPanel" hidden>
                <div class="mayhen_admin_ai_runner_stats_row">
                    <span>Phiên <strong id="mayhenRunnerIdText">#-</strong></span>
                    <span>Tiến độ: <strong id="mayhenRunnerStepText">0 / 0</strong> (<strong id="mayhenRunnerPercentText">0%</strong>)</span>
                    <span>Đạt chờ duyệt: <strong id="mayhenRunnerPassedText">0</strong></span>
                    <span>Đã loại: <strong id="mayhenRunnerRejectedText">0</strong></span>
                    <span>Đã chạy: <strong id="mayhenRunnerElapsedText">00:00:00</strong></span>
                    <span hidden><strong id="mayhenRunnerProposedText">0</strong></span>
                </div>
                <div class="mayhen_admin_ai_progress_track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" id="mayhenRunnerProgressTrack">
                    <div class="mayhen_admin_ai_progress_fill" id="mayhenRunnerProgressFill" style="width: 0%;"></div>
                </div>
                <div class="mayhen_admin_ai_runner_live_msg" id="mayhenRunnerLiveMsg">Đang khởi tạo...</div>
                <ul class="mayhen_admin_ai_runner_log" id="mayhenRunnerLogList"></ul>
            </div>
        </section>

        {{-- Khối 2: Hàng Đợi Duyệt Siêu Gọn (1 chạm Duyệt / 1 chạm Duyệt tất cả) --}}
        <section class="mayhen_admin_ai_card">
            <div class="mayhen_admin_ai_card_head">
                <div>
                    <h2 class="mayhen_admin_ai_card_title">Kiến thức mới chờ bạn duyệt ({{ $stats['pending_count'] }})</h2>
                    <p class="mayhen_admin_ai_card_desc">AI chỉ sử dụng những mục sau khi bạn bấm <strong>Duyệt</strong>. Mọi mục dưới đây đều đã qua bước kiểm tra độ chính xác tự động.</p>
                </div>
                @if($stats['pending_count'] > 0)
                    <form method="POST" action="{{ route('admin.ai_chatbot.entries.approve_all') }}" data-confirm-delete="Duyệt và kích hoạt ngay toàn bộ {{ $stats['pending_count'] }} mục kiến thức đang chờ?">
                        @csrf
                        <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary">
                            ✓ Duyệt tất cả ({{ $stats['pending_count'] }})
                        </button>
                    </form>
                @endif
            </div>

            <div class="mayhen_admin_ai_list">
                @forelse($pendingEntries as $entry)
                    @php $review = is_array($entry->review_summary) ? $entry->review_summary : []; @endphp
                    <article class="mayhen_admin_ai_row_card mayhen_admin_ai_row_card_pending">
                        <div class="mayhen_admin_ai_row_main">
                            <div class="mayhen_admin_ai_row_badges">
                                <span class="mayhen_admin_ai_tag">{{ $entry->category_label }}</span>
                                @if(isset($review['fact_score']))
                                    <span class="mayhen_admin_ai_tag mayhen_admin_ai_tag_ok">Độ chính xác: {{ $review['fact_score'] }}/100</span>
                                @endif
                                <span class="mayhen_admin_ai_meta">{{ $entry->created_at?->format('d/m H:i') }}</span>
                            </div>

                            <h3 class="mayhen_admin_ai_row_title">{{ $entry->title }}</h3>
                            <p class="mayhen_admin_ai_row_answer">{{ $entry->answer }}</p>

                            @if(! empty($review['reason']))
                                <div class="mayhen_admin_ai_note_inline">
                                    <strong>Kiểm định tự động:</strong> {{ $review['reason'] }}
                                </div>
                            @endif
                        </div>

                        <div class="mayhen_admin_ai_row_actions">
                            <form method="POST" action="{{ route('admin.ai_chatbot.entries.approve', $entry) }}" class="mayhen_admin_ai_inline_form">
                                @csrf
                                <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary">✓ Duyệt</button>
                            </form>
                            <button type="button" class="mayhen_admin_ai_btn" data-toggle-target="mayhenPendingEdit_{{ $entry->id }}">Sửa</button>
                            <form method="POST" action="{{ route('admin.ai_chatbot.entries.reject', $entry) }}" class="mayhen_admin_ai_inline_form">
                                @csrf
                                <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_danger_ghost">Bỏ qua</button>
                            </form>
                        </div>

                        {{-- Khung chỉnh sửa trước khi duyệt (ẩn gọn, bấm Sửa mới hiện) --}}
                        <div class="mayhen_admin_ai_drawer_edit" id="mayhenPendingEdit_{{ $entry->id }}" hidden>
                            <form method="POST" action="{{ route('admin.ai_chatbot.entries.approve', $entry) }}" class="mayhen_admin_ai_form_grid">
                                @csrf
                                <label class="mayhen_admin_ai_field mayhen_admin_ai_field_full">
                                    <span>Chỉnh lại câu hỏi</span>
                                    <input type="text" name="title" value="{{ $entry->title }}" maxlength="190" required>
                                </label>
                                <label class="mayhen_admin_ai_field mayhen_admin_ai_field_full">
                                    <span>Chỉnh lại câu trả lời</span>
                                    <textarea name="answer" rows="3" maxlength="2000" required>{{ $entry->answer }}</textarea>
                                </label>
                                <label class="mayhen_admin_ai_field mayhen_admin_ai_field_full">
                                    <span>Từ khóa</span>
                                    <input type="text" name="keywords" value="{{ $entry->keywords }}" maxlength="500">
                                </label>
                                <label class="mayhen_admin_ai_field">
                                    <span>Gắn mẫu thiệp</span>
                                    <select name="template_slug">
                                        <option value="">— Không gắn —</option>
                                        @foreach($templates as $tpl)
                                            <option value="{{ $tpl->slug }}" @selected($entry->template_slug === $tpl->slug)>{{ $tpl->name }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="mayhen_admin_ai_field">
                                    <span>Gắn bài hướng dẫn</span>
                                    <select name="doc_url">
                                        <option value="">— Không gắn —</option>
                                        @foreach($clientDocOptions as $docOpt)
                                            <option value="{{ $docOpt['url'] }}" @selected($entry->doc_url === $docOpt['url'])>{{ $docOpt['label'] }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <div class="mayhen_admin_ai_form_actions">
                                    <button type="button" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_ghost" data-toggle-target="mayhenPendingEdit_{{ $entry->id }}">Đóng</button>
                                    <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary">Lưu &amp; Duyệt ngay</button>
                                </div>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="mayhen_admin_ai_empty_state">
                        <p>Không có kiến thức nào đang chờ duyệt. Bấm <strong>"Bắt đầu cho AI tự học"</strong> phía trên để AI tự tạo thêm kiến thức mới nhé.</p>
                    </div>
                @endforelse
            </div>

            <div class="mayhen_admin_ai_pagination">{{ $pendingEntries->links() }}</div>
        </section>

        {{-- Khối 3: Khách hàng đang hỏi gì nhiều nhất (gọn trong 2 cột) --}}
        <section class="mayhen_admin_ai_insights_grid">
            <div class="mayhen_admin_ai_card">
                <h3 class="mayhen_admin_ai_card_title">Khách hay hỏi gần đây (30 ngày)</h3>
                <ul class="mayhen_admin_ai_simple_list">
                    @forelse($customerInsights['top_customer_questions'] ?? [] as $q)
                        <li>
                            <span>{{ \Illuminate\Support\Str::limit($q['question'], 90) }}</span>
                            <span class="mayhen_admin_ai_tag">{{ $q['hits'] }} lần</span>
                        </li>
                    @empty
                        <li class="mayhen_admin_ai_meta">Chưa có dữ liệu câu hỏi lặp lại.</li>
                    @endforelse
                </ul>
            </div>

            <div class="mayhen_admin_ai_card">
                <h3 class="mayhen_admin_ai_card_title">Câu trả lời khách đánh giá "Chưa đúng"</h3>
                <ul class="mayhen_admin_ai_simple_list">
                    @forelse($customerInsights['unhelpful_cases'] ?? [] as $u)
                        <li>
                            <div>
                                <div>{{ \Illuminate\Support\Str::limit($u['message'], 85) }}</div>
                                <div class="mayhen_admin_ai_meta">{{ $u['created_at'] }}</div>
                            </div>
                            <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'approved', 'prefill_q' => $u['message']]) }}" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_ghost">Dạy lại</a>
                        </li>
                    @empty
                        <li class="mayhen_admin_ai_meta">Rất tốt! Chưa có phản hồi tiêu cực nào trong 30 ngày qua.</li>
                    @endforelse
                </ul>
            </div>
        </section>
    @endif

    {{-- =====================================================================
         TAB 3: LỊCH SỬ CHAT & KIỂM TRA AN TOÀN
         ===================================================================== --}}
    @if($tab === 'quality')
        <section class="mayhen_admin_ai_card">
            <div class="mayhen_admin_ai_card_head">
                <div>
                    <h2 class="mayhen_admin_ai_card_title">Nhật ký hội thoại khách hàng</h2>
                    <p class="mayhen_admin_ai_card_desc">
                        Thông tin cá nhân (SĐT, Email, STK) đã được che tự động. Bạn có thể bấm <strong>"Tạo kiến thức"</strong> từ bất kỳ câu hỏi hay nào.
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.ai_chatbot.evaluate') }}" class="mayhen_admin_ai_inline_form">
                    @csrf
                    <button type="submit" class="mayhen_admin_ai_btn">Kiểm tra an toàn hệ thống (10 bài test)</button>
                </form>
            </div>

            @if($latestEvalRun && ! empty($latestEvalRun->metrics['cases']))
                <details class="mayhen_admin_ai_advanced_box" open>
                    <summary>
                        Kết quả kiểm tra an toàn gần nhất ({{ $latestEvalRun->finished_at?->format('d/m H:i') }}):
                        <strong>{{ $latestEvalRun->candidates_passed }}/{{ $latestEvalRun->candidates_proposed }} đạt ({{ $latestEvalRun->metrics['pass_rate'] ?? 0 }}%)</strong>
                    </summary>
                    <div class="mayhen_admin_ai_table_wrap mayhen_admin_ai_advanced_inner">
                        <table class="mayhen_admin_ai_table">
                            <thead>
                                <tr>
                                    <th>Mã</th>
                                    <th>Nội dung kiểm tra</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestEvalRun->metrics['cases'] as $case)
                                    <tr>
                                        <td class="mayhen_admin_ai_nowrap"><code>{{ $case['id'] }}</code></td>
                                        <td>{{ $case['name'] }}</td>
                                        <td>
                                            <span class="mayhen_admin_ai_tag {{ $case['passed'] ? 'mayhen_admin_ai_tag_ok' : 'mayhen_admin_ai_tag_danger' }}">
                                                {{ $case['passed'] ? 'Đạt' : 'Cần kiểm tra' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </details>
            @endif

            <div class="mayhen_admin_ai_table_wrap">
                <table class="mayhen_admin_ai_table">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Khách hàng hỏi</th>
                            <th>AI trả lời</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="mayhen_admin_ai_nowrap">{{ $log->created_at?->format('d/m H:i') }}<br><small class="mayhen_admin_ai_meta">{{ $log->latency_ms }}ms</small></td>
                                <td>{{ $log->user_message }}</td>
                                <td><div class="mayhen_admin_ai_log_reply">{{ \Illuminate\Support\Str::limit($log->bot_reply, 160) }}</div></td>
                                <td>
                                    <span class="mayhen_admin_ai_tag {{ $log->response_mode === 'blocked_security' ? 'mayhen_admin_ai_tag_danger' : '' }}">
                                        {{ $log->response_mode_label }}
                                    </span>
                                    @if($log->feedback)
                                        <div class="mayhen_admin_ai_meta">
                                            {{ $log->feedback === 'helpful' ? '👍 Hữu ích' : '👎 Chưa đúng' }}
                                            @if($log->feedback_valid === false)
                                                <span class="mayhen_admin_ai_flag_text">(Đã lọc spam)</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="mayhen_admin_ai_nowrap">
                                    @if($log->response_mode !== 'blocked_security')
                                        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'approved', 'prefill_q' => $log->user_message]) }}" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_ghost">
                                            Tạo kiến thức
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="mayhen_admin_ai_empty">Chưa có cuộc trò chuyện nào được ghi nhận.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mayhen_admin_ai_pagination">{{ $logs->links() }}</div>
        </section>
    @endif

    {{-- =====================================================================
         TAB 4: CÀI ĐẶT AI (CÔNG TẮC GẠT TRỰC QUAN, RÕ RÀNG CHO NGƯỜI MỚI)
         ===================================================================== --}}
    @if($tab === 'governance')
        <section class="mayhen_admin_ai_card">
            <div class="mayhen_admin_ai_card_head">
                <div>
                    <h2 class="mayhen_admin_ai_card_title">Cài đặt vận hành Trợ lý AI</h2>
                    <p class="mayhen_admin_ai_card_desc">Kiểm soát trạng thái hoạt động, chọn mô hình AI và giới hạn sử dụng an toàn.</p>
                </div>
                <span class="mayhen_admin_ai_meta">Token đã dùng hôm nay: <strong>{{ number_format($stats['today_tokens_used']) }}</strong> / {{ number_format($policy['daily_token_budget']) }}</span>
            </div>

            <form method="POST" action="{{ route('admin.ai_chatbot.governance.update') }}" class="mayhen_admin_ai_settings_stack">
                @csrf

                {{-- 3 Công tắc gạt dễ hiểu nhất --}}
                <div class="mayhen_admin_ai_toggle_list">
                    <label class="mayhen_admin_ai_toggle_row mayhen_admin_ai_toggle_row_danger">
                        <div>
                            <strong>Tạm tắt gọi AI bên ngoài (Chế độ khẩn cấp)</strong>
                            <p>Khi bật, hệ thống ngừng gọi API AI và chỉ trả lời bằng thông tin dự phòng an toàn có sẵn trên web.</p>
                        </div>
                        <input type="checkbox" class="mayhen_admin_ai_switch" name="kill_switch" value="1" @checked($policy['kill_switch'])>
                    </label>

                    <label class="mayhen_admin_ai_toggle_row">
                        <div>
                            <strong>Tự động kiểm tra chéo câu trả lời quan trọng</strong>
                            <p>Dùng AI kiểm định lại câu trả lời liên quan tới giá cả, thanh toán và chính sách trước khi gửi cho khách.</p>
                        </div>
                        <input type="checkbox" class="mayhen_admin_ai_switch" name="enable_reviewer_for_complex" value="1" @checked($policy['enable_reviewer_for_complex'])>
                    </label>

                    <label class="mayhen_admin_ai_toggle_row">
                        <div>
                            <strong>Ghi nhớ sở thích phong cách thiệp của khách</strong>
                            <p>Giúp AI nhớ khách thích mẫu sang trọng hay miễn phí trong phiên chat (cô lập từng tài khoản, không lưu SĐT/Email).</p>
                        </div>
                        <input type="checkbox" class="mayhen_admin_ai_switch" name="memory_enabled" value="1" @checked($policy['memory_enabled'])>
                    </label>
                </div>

                {{-- Chọn Model --}}
                <div class="mayhen_admin_ai_form_grid">
                    <label class="mayhen_admin_ai_field">
                        <span>Mô hình AI trả lời khách hàng</span>
                        <input type="text" name="draft_model" list="mayhen9RouterModels" value="{{ $policy['draft_model'] }}" placeholder="Ví dụ: my-combo hoặc ag/gemini-3.8-flash">
                    </label>

                    <label class="mayhen_admin_ai_field">
                        <span>Mô hình AI kiểm tra chéo (nên chọn bản thông minh nhất)</span>
                        <input type="text" name="reviewer_model" list="mayhen9RouterModels" value="{{ $policy['reviewer_model'] }}" placeholder="Ví dụ: google-6-astra hoặc cx/gpt-6-astra">
                    </label>
                </div>

                <datalist id="mayhen9RouterModels">
                    @foreach($availableModels as $m)
                        <option value="{{ $m }}"></option>
                    @endforeach
                </datalist>

                {{-- Cài đặt nâng cao thu gọn gọn gàng --}}
                <details class="mayhen_admin_ai_advanced_box">
                    <summary>Cài đặt nâng cao (Nguồn dữ liệu, ngân sách token &amp; thời hạn lưu log)</summary>
                    <div class="mayhen_admin_ai_form_grid mayhen_admin_ai_advanced_inner">
                        <label class="mayhen_admin_ai_field">
                            <span>Số vòng kiểm tra chéo tối đa (0 – 2)</span>
                            <input type="number" name="max_review_rounds" min="0" max="2" value="{{ $policy['max_review_rounds'] }}" required>
                        </label>
                        <label class="mayhen_admin_ai_field">
                            <span>Giới hạn Token tối đa mỗi ngày</span>
                            <input type="number" name="daily_token_budget" min="10000" max="50000000" step="10000" value="{{ $policy['daily_token_budget'] }}" required>
                        </label>
                        <label class="mayhen_admin_ai_field">
                            <span>Tự xóa lịch sử chat cũ sau (ngày)</span>
                            <input type="number" name="retention_days" min="7" max="365" value="{{ $policy['retention_days'] }}" required>
                        </label>
                        <div class="mayhen_admin_ai_field mayhen_admin_ai_field_full">
                            <span>Nguồn dữ liệu AI được phép đọc</span>
                            <div class="mayhen_admin_ai_checkbox_group">
                                @php
                                    $sourceLabels = [
                                        'approved_knowledge' => 'Kiến thức Admin đã duyệt',
                                        'templates' => 'Kho mẫu thiệp đang mở',
                                        'plans' => 'Bảng giá & gói dịch vụ',
                                        'client_docs' => 'Bài hướng dẫn khách hàng',
                                    ];
                                @endphp
                                @foreach($sourceLabels as $sKey => $sLabel)
                                    <label class="mayhen_admin_ai_checkbox">
                                        <input type="checkbox" name="allowed_sources[]" value="{{ $sKey }}" @checked(in_array($sKey, $policy['allowed_sources'], true))>
                                        <span>{{ $sLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </details>

                <div class="mayhen_admin_ai_form_actions">
                    <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary">Lưu cài đặt</button>
                </div>
            </form>
        </section>

        <section class="mayhen_admin_ai_card mayhen_admin_ai_card_inline">
            <div>
                <h3 class="mayhen_admin_ai_card_title">Dọn dẹp dữ liệu hội thoại cũ</h3>
                <p class="mayhen_admin_ai_card_desc">Xóa ngay bộ nhớ &amp; lịch sử chat cũ hơn <strong>{{ $policy['retention_days'] }} ngày</strong> để nhẹ database (cũng tự chạy mỗi đêm lúc 03:30).</p>
            </div>
            <form method="POST" action="{{ route('admin.ai_chatbot.retention.purge') }}">
                @csrf
                <button type="submit" class="mayhen_admin_ai_btn">Dọn ngay</button>
            </form>
        </section>
    @endif
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/admin/js/pages/ai-chatbot.js') }}?v={{ file_exists(public_path('assets/admin/js/pages/ai-chatbot.js')) ? filemtime(public_path('assets/admin/js/pages/ai-chatbot.js')) : time() }}"></script>
@endpush
