@extends('admin.layouts.master')

@section('title', 'Mây Hẹn Intelligence Engine — Quản Trị Não Bộ AI')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/ai-chatbot.css') }}?v={{ file_exists(public_path('assets/admin/css/pages/ai-chatbot.css')) ? filemtime(public_path('assets/admin/css/pages/ai-chatbot.css')) : time() }}">
@endpush

@section('content')
<div class="mayhen_admin_ai_wrap">
    <div class="mayhen_admin_ai_header">
        <div>
            <h1 class="mayhen_admin_ai_title">Mây Hẹn Intelligence Engine — Trung Tâm Quản Trị AI</h1>
            <p class="mayhen_admin_ai_subtitle">
                Kiến trúc Multi-Agent &amp; Zero-Trust Governance: Bộ não tri thức độc lập của Mây Hẹn, Admin phê duyệt tuyệt đối, khách hàng chỉ là tín hiệu nhu cầu (không tự dạy AI).
            </p>
        </div>
        <div class="mayhen_admin_ai_runtime_box">
            <span class="mayhen_admin_ai_badge {{ $aiRuntimeConfig['kill_switch'] ? 'mayhen_admin_ai_badge_danger' : ($aiRuntimeConfig['enabled'] && $aiRuntimeConfig['model'] !== '' ? 'mayhen_admin_ai_badge_ok' : 'mayhen_admin_ai_badge_warn') }}">
                @if($aiRuntimeConfig['kill_switch'])
                    KILL SWITCH ĐANG BẬT (Tạm ngưng LLM)
                @else
                    {{ strtoupper($aiRuntimeConfig['provider']) }} • Draft: {{ $aiRuntimeConfig['model'] ?: 'Chưa chọn' }} | Reviewer: {{ $aiRuntimeConfig['reviewer_model'] ?: 'Mặc định' }}
                @endif
            </span>
            <span class="mayhen_admin_ai_runtime_note">
                Token hôm nay: <strong>{{ number_format($stats['today_tokens_used']) }}</strong> / {{ number_format($policy['daily_token_budget']) }}
                @if(! empty($aiRuntimeConfig['last_provider_status']) && $aiRuntimeConfig['last_provider_status'] !== 'ok')
                    • Trạng thái gần nhất: <code>{{ $aiRuntimeConfig['last_provider_status'] }}</code>
                @endif
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="mayhen_admin_ai_alert mayhen_admin_ai_alert_success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="mayhen_admin_ai_alert mayhen_admin_ai_alert_danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="mayhen_admin_ai_alert mayhen_admin_ai_alert_danger">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Thẻ KPI tổng quan --}}
    <div class="mayhen_admin_ai_stats">
        <div class="mayhen_admin_ai_stat_card">
            <span class="mayhen_admin_ai_stat_label">Tri thức chính thức đang chạy</span>
            <strong class="mayhen_admin_ai_stat_value">{{ number_format($stats['approved_count']) }}</strong>
            <small class="mayhen_admin_ai_meta">Lỗi thời: {{ $stats['outdated_count'] }}</small>
        </div>
        <div class="mayhen_admin_ai_stat_card">
            <span class="mayhen_admin_ai_stat_label">Ứng viên AI chờ Admin duyệt</span>
            <strong class="mayhen_admin_ai_stat_value mayhen_admin_ai_stat_warn">{{ number_format($stats['pending_count']) }}</strong>
            <small class="mayhen_admin_ai_meta">Bắt buộc duyệt trước khi chạy</small>
        </div>
        <div class="mayhen_admin_ai_stat_card">
            <span class="mayhen_admin_ai_stat_label">Tỷ lệ giải quyết / Hội thoại</span>
            <strong class="mayhen_admin_ai_stat_value">{{ $stats['resolved_rate'] }}%</strong>
            <small class="mayhen_admin_ai_meta">{{ number_format($stats['total_chats']) }} lượt • Qua Critic: {{ $stats['reviewed_count'] }}</small>
        </div>
        <div class="mayhen_admin_ai_stat_card">
            <span class="mayhen_admin_ai_stat_label">Đã chặn bảo mật (Zero-Trust)</span>
            <strong class="mayhen_admin_ai_stat_value mayhen_admin_ai_stat_danger">{{ number_format($stats['blocked_security_count']) }}</strong>
            <small class="mayhen_admin_ai_meta">Hữu ích: {{ $stats['helpful_count'] }} • Chưa đúng: {{ $stats['unhelpful_count'] }}</small>
        </div>
    </div>

    {{-- 4 Trung Tâm Quản Trị --}}
    <div class="mayhen_admin_ai_tabs">
        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'approved']) }}" class="mayhen_admin_ai_tab {{ $tab === 'approved' ? 'mayhen_admin_ai_tab_active' : '' }}">
            1. Kho Tri Thức &amp; Phiên Bản ({{ $stats['approved_count'] }})
        </a>
        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'learning']) }}" class="mayhen_admin_ai_tab {{ $tab === 'learning' ? 'mayhen_admin_ai_tab_active' : '' }}">
            2. AI Learning Center &amp; Duyệt ({{ $stats['pending_count'] }})
        </a>
        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'quality']) }}" class="mayhen_admin_ai_tab {{ $tab === 'quality' ? 'mayhen_admin_ai_tab_active' : '' }}">
            3. Quality Center &amp; Nhật Ký ({{ $stats['total_chats'] }})
        </a>
        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'governance']) }}" class="mayhen_admin_ai_tab {{ $tab === 'governance' ? 'mayhen_admin_ai_tab_active' : '' }}">
            4. AI Governance &amp; Chính Sách
        </a>
    </div>

    {{-- TAB 1: KNOWLEDGE MANAGEMENT (VERSIONING, ROLLBACK, EFFECTIVE WINDOW, OUTDATED) --}}
    @if($tab === 'approved')
        <section class="mayhen_admin_ai_card" id="mayhenAdminTrainFormCard">
            <h2 class="mayhen_admin_ai_card_title">Huấn Luyện Tri Thức Chính Thức (Admin Xác Thực — Trust 100%)</h2>
            <form method="POST" action="{{ route('admin.ai_chatbot.entries.store') }}" class="mayhen_admin_ai_form_grid">
                @csrf
                <label class="mayhen_admin_ai_field">
                    <span>Chủ đề</span>
                    <select name="category" required>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="mayhen_admin_ai_field">
                    <span>Độ ưu tiên (1 - 10)</span>
                    <input type="number" name="priority" min="1" max="10" value="{{ old('priority', 7) }}">
                </label>

                <label class="mayhen_admin_ai_field mayhen_admin_ai_field_full">
                    <span>Câu hỏi chuẩn / Tiêu đề tri thức</span>
                    <input type="text" name="title" id="mayhenAdminTrainTitle" maxlength="190" required value="{{ old('title', request('prefill_q')) }}" placeholder="Ví dụ: Thiệp cưới online Mây Hẹn có gửi qua Zalo dễ dàng không?">
                </label>

                <label class="mayhen_admin_ai_field mayhen_admin_ai_field_full">
                    <span>Từ khóa kích hoạt (cách nhau bởi dấu phẩy)</span>
                    <input type="text" name="keywords" maxlength="500" value="{{ old('keywords') }}" placeholder="zalo, chia sẻ link, gửi thiệp, điện thoại">
                </label>

                <label class="mayhen_admin_ai_field mayhen_admin_ai_field_full">
                    <span>Nội dung sự thật chuẩn xác (AI sẽ dùng để diễn đạt tự nhiên, không in nguyên văn)</span>
                    <textarea name="answer" rows="4" maxlength="2000" required placeholder="Nhập sự thật chính xác để AI tổng hợp ngắn gọn và hướng khách sử dụng dịch vụ...">{{ old('answer') }}</textarea>
                </label>

                <label class="mayhen_admin_ai_field">
                    <span>Gắn kèm Mẫu Thiệp (Bằng chứng)</span>
                    <select name="template_slug">
                        <option value="">— Không gắn mẫu cố định —</option>
                        @foreach($templates as $tpl)
                            <option value="{{ $tpl->slug }}" @selected(old('template_slug') === $tpl->slug)>{{ $tpl->name }} ({{ $tpl->category }})</option>
                        @endforeach
                    </select>
                </label>

                <label class="mayhen_admin_ai_field">
                    <span>Gắn kèm Tài Liệu Client Docs (Bằng chứng)</span>
                    <select name="doc_url">
                        <option value="">— Không gắn bài Docs cố định —</option>
                        @foreach($clientDocOptions as $docOpt)
                            <option value="{{ $docOpt['url'] }}" @selected(old('doc_url') === $docOpt['url'])>{{ $docOpt['label'] }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="mayhen_admin_ai_field">
                    <span>Hiệu lực từ ngày (Tùy chọn)</span>
                    <input type="date" name="effective_from" value="{{ old('effective_from') }}">
                </label>

                <label class="mayhen_admin_ai_field">
                    <span>Hết hạn hiệu lực (Tùy chọn)</span>
                    <input type="date" name="effective_until" value="{{ old('effective_until') }}">
                </label>

                <div class="mayhen_admin_ai_form_actions">
                    <input type="hidden" name="is_active" value="1">
                    <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary">Lưu &amp; Xuất Bản (v1)</button>
                </div>
            </form>
        </section>

        <section class="mayhen_admin_ai_card">
            <form method="GET" action="{{ route('admin.ai_chatbot.index') }}" class="mayhen_admin_ai_filter_bar">
                <input type="hidden" name="tab" value="approved">
                <input type="text" name="q" value="{{ $search }}" placeholder="Tìm theo câu hỏi, từ khóa, nội dung...">
                <select name="category">
                    <option value="">Tất cả chủ đề</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}" @selected($category === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="mayhen_admin_ai_btn">Lọc</button>
            </form>

            <div class="mayhen_admin_ai_list">
                @forelse($approvedEntries as $entry)
                    <article class="mayhen_admin_ai_item {{ $entry->is_outdated ? 'mayhen_admin_ai_item_outdated' : '' }}">
                        <div class="mayhen_admin_ai_item_head">
                            <div class="mayhen_admin_ai_tag_row">
                                <span class="mayhen_admin_ai_tag">{{ $entry->category_label }}</span>
                                <span class="mayhen_admin_ai_tag {{ $entry->source === 'ai_self_train' ? 'mayhen_admin_ai_tag_customer' : '' }}">
                                    {{ $entry->source_label }}
                                </span>
                                <span class="mayhen_admin_ai_tag">v{{ $entry->version ?? 1 }} • Trust {{ $entry->trust_score ?? 100 }}%</span>
                                @if($entry->is_outdated)
                                    <span class="mayhen_admin_ai_tag mayhen_admin_ai_tag_danger">Lỗi thời</span>
                                @endif
                                <span class="mayhen_admin_ai_meta">Ưu tiên: {{ $entry->priority }} • Dùng: {{ $entry->usage_count }} lượt</span>
                            </div>
                            <div class="mayhen_admin_ai_inline_actions">
                                <form method="POST" action="{{ route('admin.ai_chatbot.entries.outdated', $entry) }}" class="mayhen_admin_ai_inline_form">
                                    @csrf
                                    <button type="submit" class="mayhen_admin_ai_btn">{{ $entry->is_outdated ? 'Khôi phục hiệu lực' : 'Đánh dấu lỗi thời' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.ai_chatbot.entries.destroy', $entry) }}" class="mayhen_admin_ai_inline_form" data-confirm-delete="Xóa mục tri thức này khỏi kho AI?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_danger_ghost">Xóa</button>
                                </form>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.ai_chatbot.entries.update', $entry) }}" class="mayhen_admin_ai_edit_form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="category" value="{{ $entry->category }}">
                            <div class="mayhen_admin_ai_edit_row">
                                <input type="text" name="title" value="{{ $entry->title }}" maxlength="190" required>
                                <input type="number" name="priority" value="{{ $entry->priority }}" min="1" max="10" class="mayhen_admin_ai_input_sm" title="Độ ưu tiên">
                            </div>
                            <input type="text" name="keywords" value="{{ $entry->keywords }}" maxlength="500" placeholder="Từ khóa kích hoạt...">
                            <textarea name="answer" rows="3" maxlength="2000" required>{{ $entry->answer }}</textarea>
                            <div class="mayhen_admin_ai_edit_footer">
                                <select name="template_slug">
                                    <option value="">Mẫu thiệp: Không</option>
                                    @foreach($templates as $tpl)
                                        <option value="{{ $tpl->slug }}" @selected($entry->template_slug === $tpl->slug)>{{ $tpl->name }}</option>
                                    @endforeach
                                </select>
                                <select name="doc_url">
                                    <option value="">Docs: Không</option>
                                    @foreach($clientDocOptions as $docOpt)
                                        <option value="{{ $docOpt['url'] }}" @selected($entry->doc_url === $docOpt['url'])>{{ $docOpt['label'] }}</option>
                                    @endforeach
                                </select>
                                <label class="mayhen_admin_ai_checkbox">
                                    <input type="checkbox" name="is_active" value="1" @checked($entry->is_active)>
                                    <span>Đang bật</span>
                                </label>
                                <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary">Lưu phiên bản mới</button>
                            </div>
                        </form>

                        @if($entry->versions->isNotEmpty())
                            <details class="mayhen_admin_ai_versions_box">
                                <summary>Lịch sử phiên bản &amp; Rollback ({{ $entry->versions->count() }} bản lưu)</summary>
                                <ul class="mayhen_admin_ai_version_list">
                                    @foreach($entry->versions as $ver)
                                        <li>
                                            <div>
                                                <strong>v{{ $ver->version }}</strong> ({{ $ver->created_at?->format('d/m/Y H:i') }}) — {{ $ver->change_reason ?: 'Cập nhật' }}
                                                <div class="mayhen_admin_ai_meta">{{ \Illuminate\Support\Str::limit($ver->answer, 140) }}</div>
                                            </div>
                                            @if((int) $ver->version !== (int) $entry->version)
                                                <form method="POST" action="{{ route('admin.ai_chatbot.entries.rollback', [$entry, $ver]) }}" class="mayhen_admin_ai_inline_form">
                                                    @csrf
                                                    <button type="submit" class="mayhen_admin_ai_btn">Rollback về v{{ $ver->version }}</button>
                                                </form>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        @endif
                    </article>
                @empty
                    <p class="mayhen_admin_ai_empty">Chưa có mục tri thức nào khớp bộ lọc.</p>
                @endforelse
            </div>

            <div class="mayhen_admin_ai_pagination">{{ $approvedEntries->links() }}</div>
        </section>
    @endif

    {{-- TAB 2: AI LEARNING CENTER & ADMIN APPROVAL QUEUE --}}
    @if($tab === 'learning')
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
            <div class="mayhen_admin_ai_item_head">
                <div>
                    <h2 class="mayhen_admin_ai_card_title">Client-Server Chunked Self-Learning (Chống Timeout &amp; Tối Ưu Hosting Yếu)</h2>
                    <p class="mayhen_admin_ai_subtitle">
                        Chia nhỏ thành từng <strong>vi-bước siêu nhẹ</strong> (mỗi request chỉ gọi đúng 1 lần AI &lt; 10s rồi giải phóng RAM/PHP-FPM ngay): tự động nghỉ nhịp nhường CPU cho khách hàng thật, tự thử lại khi rớt mạng, hỗ trợ chạy liên tục nhiều giờ mà không bao giờ timeout hay sập hosting. Toàn bộ ứng viên đạt Critic AI + Hard-Guard đều đưa vào Hàng đợi chờ Admin phê duyệt.
                    </p>
                </div>
                <span class="mayhen_admin_ai_tag mayhen_admin_ai_tag_ok" id="mayhenRunnerStatusBadge">Sẵn sàng</span>
            </div>

            <div class="mayhen_admin_ai_learning_trigger" id="mayhenRunnerConfigBox">
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
                        <span>Số ứng viên mỗi chủ đề / vòng</span>
                        <select id="mayhenRunnerPerTopic">
                            <option value="1">1 mục (Nhẹ nhất)</option>
                            <option value="2" selected>2 mục (Cân bằng)</option>
                            <option value="3">3 mục (Phong phú)</option>
                        </select>
                    </label>

                    <label class="mayhen_admin_ai_field">
                        <span>Số vòng lặp (Hỗ trợ chạy hàng tiếng)</span>
                        <select id="mayhenRunnerRounds">
                            <option value="1">1 vòng (Nhanh ~ 1–2 phút)</option>
                            <option value="3" selected>3 vòng (~ 4–6 phút)</option>
                            <option value="10">10 vòng (~ 15–20 phút)</option>
                            <option value="30">30 vòng (~ 45–60 phút)</option>
                            <option value="60">60 vòng (Chạy sâu ~ 1.5–2 tiếng)</option>
                        </select>
                    </label>

                    <label class="mayhen_admin_ai_field">
                        <span>Nhịp nghỉ giữa mỗi vi-bước (Nhường CPU cho khách)</span>
                        <select id="mayhenRunnerCooldown">
                            <option value="2000">2 giây (Server khỏe)</option>
                            <option value="3500" selected>3.5 giây (Khuyên dùng Shared Hosting)</option>
                            <option value="5000">5 giây (Hosting yếu / giờ cao điểm)</option>
                            <option value="8000">8 giây (Siêu tiết kiệm tài nguyên)</option>
                        </select>
                    </label>
                </div>

                <div class="mayhen_admin_ai_runner_actions">
                    <button type="button" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary" id="mayhenRunnerStartBtn">
                        Bắt Đầu Chạy Chia Nhỏ (Client-Server)
                    </button>
                    <button type="button" class="mayhen_admin_ai_btn" id="mayhenRunnerPauseBtn" hidden>Tạm Dừng</button>
                    <button type="button" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary" id="mayhenRunnerResumeBtn" hidden>Tiếp Tục Chạy</button>
                    <button type="button" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_danger_ghost" id="mayhenRunnerCancelBtn" hidden>Dừng Hẳn Phiên</button>
                    <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'learning']) }}" class="mayhen_admin_ai_btn" id="mayhenRunnerRefreshBtn" hidden>Tải Lại Hàng Đợi Duyệt</a>
                </div>
            </div>

            {{-- Thanh tiến độ & Log trực tiếp theo từng vi-bước --}}
            <div class="mayhen_admin_ai_runner_progress_panel" id="mayhenRunnerProgressPanel" hidden>
                <div class="mayhen_admin_ai_runner_stats_row">
                    <span>Phiên: <strong id="mayhenRunnerIdText">#-</strong></span>
                    <span>Tiến độ: <strong id="mayhenRunnerStepText">0 / 0</strong> (<strong id="mayhenRunnerPercentText">0%</strong>)</span>
                    <span>Đề xuất: <strong id="mayhenRunnerProposedText">0</strong></span>
                    <span>Đạt chờ duyệt: <strong id="mayhenRunnerPassedText">0</strong></span>
                    <span>Đã loại: <strong id="mayhenRunnerRejectedText">0</strong></span>
                    <span>Thời gian: <strong id="mayhenRunnerElapsedText">00:00:00</strong></span>
                </div>
                <div class="mayhen_admin_ai_progress_track" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" id="mayhenRunnerProgressTrack">
                    <div class="mayhen_admin_ai_progress_fill" id="mayhenRunnerProgressFill" style="width: 0%;"></div>
                </div>
                <div class="mayhen_admin_ai_runner_live_msg" id="mayhenRunnerLiveMsg">Đang chờ khởi động...</div>
                <ul class="mayhen_admin_ai_runner_log" id="mayhenRunnerLogList"></ul>
            </div>

            <p class="mayhen_admin_ai_meta">
                Mẹo vận hành trên Azdigi/Shared Hosting: Bạn có thể để mở tab này chạy trực tiếp nhiều giờ, hoặc đóng trình duyệt — Cron <code>mayhen:ai-self-train-step</code> mỗi phút sẽ tự chạy nối tiếp đúng 1 vi-bước rồi thoát nên không bao giờ bị giới hạn CPU/RAM hay timeout.
            </p>
        </section>

        {{-- Passive Customer Intelligence --}}
        <section class="mayhen_admin_ai_insights_grid">
            <div class="mayhen_admin_ai_card">
                <h3 class="mayhen_admin_ai_card_title">Nhu Cầu Khách Hàng (30 ngày)</h3>
                <p class="mayhen_admin_ai_meta">
                    Hội thoại hợp lệ: <strong>{{ $customerInsights['total_valid_chats'] ?? 0 }}</strong> •
                    Tỷ lệ hài lòng: <strong>{{ $customerInsights['helpful_rate'] ?? 0 }}%</strong> •
                    Spam feedback đã lọc: <strong>{{ $customerInsights['invalid_feedback_filtered'] ?? 0 }}</strong>
                </p>
                <ul class="mayhen_admin_ai_simple_list">
                    @forelse($customerInsights['top_customer_questions'] ?? [] as $q)
                        <li><span>{{ \Illuminate\Support\Str::limit($q['question'], 95) }}</span> <strong>{{ $q['hits'] }} lượt</strong></li>
                    @empty
                        <li class="mayhen_admin_ai_meta">Chưa có đủ dữ liệu câu hỏi phổ biến.</li>
                    @endforelse
                </ul>
            </div>

            <div class="mayhen_admin_ai_card">
                <h3 class="mayhen_admin_ai_card_title">Khoảng Trống Tri Thức &amp; Phản Hồi Chưa Tốt</h3>
                <ul class="mayhen_admin_ai_simple_list">
                    @forelse($customerInsights['unhelpful_cases'] ?? [] as $u)
                        <li>
                            <div>
                                <strong>[Chưa đúng]</strong> {{ \Illuminate\Support\Str::limit($u['message'], 90) }}
                                <div class="mayhen_admin_ai_meta">{{ $u['created_at'] }} • {{ $u['reply_preview'] }}</div>
                            </div>
                        </li>
                    @empty
                        <li class="mayhen_admin_ai_meta">Không có phản hồi "Chưa đúng" nào trong 30 ngày qua.</li>
                    @endforelse
                </ul>
            </div>
        </section>

        {{-- Hàng đợi duyệt ứng viên tri thức --}}
        <section class="mayhen_admin_ai_card">
            <h2 class="mayhen_admin_ai_card_title">Hàng Đợi Phê Duyệt Ứng Viên Tri Thức ({{ $stats['pending_count'] }})</h2>
            <p class="mayhen_admin_ai_subtitle">
                Mọi mục dưới đây đều đang bị cách ly khỏi AI live. Admin xem điểm Fact Score, bằng chứng nguồn và nhận xét của Critic AI rồi bấm Phê Duyệt hoặc Từ Chối.
            </p>

            <div class="mayhen_admin_ai_list">
                @forelse($pendingEntries as $entry)
                    @php $review = is_array($entry->review_summary) ? $entry->review_summary : []; @endphp
                    <article class="mayhen_admin_ai_item mayhen_admin_ai_item_pending">
                        <div class="mayhen_admin_ai_item_head">
                            <div class="mayhen_admin_ai_tag_row">
                                <span class="mayhen_admin_ai_tag mayhen_admin_ai_tag_customer">{{ $entry->source_label }}</span>
                                <span class="mayhen_admin_ai_tag">{{ $entry->category_label }}</span>
                                @if(isset($review['fact_score']))
                                    <span class="mayhen_admin_ai_tag mayhen_admin_ai_tag_ok">Critic Fact Score: {{ $review['fact_score'] }}/100</span>
                                @endif
                                <span class="mayhen_admin_ai_meta">Tạo lúc: {{ $entry->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>

                        @if(! empty($review['reason']) || ! empty($entry->evidence_refs))
                            <div class="mayhen_admin_ai_evidence_box">
                                @if(! empty($review['reason']))
                                    <div><strong>Phản biện Critic AI ({{ $review['reviewer_model'] ?? 'Reviewer' }}):</strong> {{ $review['reason'] }}</div>
                                @endif
                                @if(! empty($entry->evidence_refs))
                                    <div class="mayhen_admin_ai_meta"><strong>Bằng chứng nguồn:</strong> {{ implode(' • ', (array) $entry->evidence_refs) }}</div>
                                @endif
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.ai_chatbot.entries.approve', $entry) }}" class="mayhen_admin_ai_edit_form">
                            @csrf
                            <label class="mayhen_admin_ai_field">
                                <span>Câu hỏi / Tiêu đề tri thức (có thể hiệu đính)</span>
                                <input type="text" name="title" value="{{ $entry->title }}" maxlength="190" required>
                            </label>
                            <label class="mayhen_admin_ai_field">
                                <span>Từ khóa kích hoạt</span>
                                <input type="text" name="keywords" value="{{ $entry->keywords }}" maxlength="500">
                            </label>
                            <label class="mayhen_admin_ai_field">
                                <span>Nội dung chuẩn xác (Admin xác minh trước khi xuất bản)</span>
                                <textarea name="answer" rows="3" maxlength="2000" required>{{ $entry->answer }}</textarea>
                            </label>
                            <div class="mayhen_admin_ai_edit_footer">
                                <select name="template_slug">
                                    <option value="">Mẫu thiệp: Không</option>
                                    @foreach($templates as $tpl)
                                        <option value="{{ $tpl->slug }}" @selected($entry->template_slug === $tpl->slug)>{{ $tpl->name }}</option>
                                    @endforeach
                                </select>
                                <select name="doc_url">
                                    <option value="">Docs: Không</option>
                                    @foreach($clientDocOptions as $docOpt)
                                        <option value="{{ $docOpt['url'] }}" @selected($entry->doc_url === $docOpt['url'])>{{ $docOpt['label'] }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary">Phê Duyệt &amp; Xuất Bản</button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('admin.ai_chatbot.entries.reject', $entry) }}" class="mayhen_admin_ai_reject_bar">
                            @csrf
                            <input type="text" name="rejection_reason" maxlength="190" placeholder="Lý do từ chối (tùy chọn)...">
                            <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_danger_ghost">Từ chối</button>
                        </form>
                    </article>
                @empty
                    <p class="mayhen_admin_ai_empty">Chưa có ứng viên nào trong hàng đợi. Hãy bấm "Chạy Multi-Agent Learning Ngay" ở trên để AI tự phân tích &amp; đề xuất tri thức mới.</p>
                @endforelse
            </div>

            <div class="mayhen_admin_ai_pagination">{{ $pendingEntries->links() }}</div>
        </section>
    @endif

    {{-- TAB 3: AI QUALITY CENTER, REGRESSION EVALUATION & AUDIT LOGS --}}
    @if($tab === 'quality')
        <section class="mayhen_admin_ai_card">
            <div class="mayhen_admin_ai_item_head">
                <div>
                    <h2 class="mayhen_admin_ai_card_title">Bộ Kiểm Định Chất Lượng &amp; Bảo Mật Hồi Quy (Regression Evaluation)</h2>
                    <p class="mayhen_admin_ai_meta">
                        Tỷ lệ thiếu bằng chứng: <strong>{{ $stats['missing_evidence_rate'] }}%</strong> •
                        Lượt bị Critic/Policy từ chối chuyển Fallback an toàn: <strong>{{ $stats['reviewer_rejected_count'] }}</strong>
                    </p>
                </div>
                <form method="POST" action="{{ route('admin.ai_chatbot.evaluate') }}" class="mayhen_admin_ai_inline_form">
                    @csrf
                    <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary">Chạy Regression Benchmark Ngay</button>
                </form>
            </div>

            @if($latestEvalRun && ! empty($latestEvalRun->metrics['cases']))
                <div class="mayhen_admin_ai_eval_summary">
                    Lần kiểm định gần nhất ({{ $latestEvalRun->finished_at?->format('d/m/Y H:i') }}):
                    <strong>{{ $latestEvalRun->candidates_passed }}/{{ $latestEvalRun->candidates_proposed }} đạt ({{ $latestEvalRun->metrics['pass_rate'] ?? 0 }}%)</strong>
                </div>
                <div class="mayhen_admin_ai_table_wrap">
                    <table class="mayhen_admin_ai_table">
                        <thead>
                            <tr>
                                <th>Mã</th>
                                <th>Kịch bản kiểm định</th>
                                <th>Kết quả</th>
                                <th>Chi tiết thực thi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestEvalRun->metrics['cases'] as $case)
                                <tr>
                                    <td class="mayhen_admin_ai_nowrap"><code>{{ $case['id'] }}</code></td>
                                    <td>{{ $case['name'] }}</td>
                                    <td>
                                        <span class="mayhen_admin_ai_tag {{ $case['passed'] ? 'mayhen_admin_ai_tag_ok' : 'mayhen_admin_ai_tag_danger' }}">
                                            {{ $case['passed'] ? 'PASS' : 'FAIL' }}
                                        </span>
                                    </td>
                                    <td>{{ $case['detail'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        @if($learningRuns->isNotEmpty())
            <section class="mayhen_admin_ai_card">
                <h2 class="mayhen_admin_ai_card_title">Lịch Sử Chạy Learning &amp; Evaluation Jobs</h2>
                <div class="mayhen_admin_ai_table_wrap">
                    <table class="mayhen_admin_ai_table">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Loại Job</th>
                                <th>Trạng thái</th>
                                <th>Đề xuất / Đạt / Loại</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($learningRuns as $run)
                                <tr>
                                    <td class="mayhen_admin_ai_nowrap">{{ $run->created_at?->format('d/m H:i') }}</td>
                                    <td>{{ $run->run_type_label }}</td>
                                    <td><span class="mayhen_admin_ai_tag">{{ strtoupper($run->status) }}</span></td>
                                    <td>{{ $run->candidates_proposed }} đề xuất • <strong>{{ $run->candidates_passed }} đạt</strong> • {{ $run->candidates_rejected }} loại</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <section class="mayhen_admin_ai_card">
            <h2 class="mayhen_admin_ai_card_title">Nhật Ký Hội Thoại, Truy Vết Critic &amp; Tường Lửa Zero-Trust (Đã che PII)</h2>
            <div class="mayhen_admin_ai_table_wrap">
                <table class="mayhen_admin_ai_table">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Câu hỏi khách hàng</th>
                            <th>Phản hồi của Trợ lý</th>
                            <th>Rủi ro, Critic &amp; Bảo mật</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            @php $meta = is_array($log->trace_metadata) ? $log->trace_metadata : []; @endphp
                            <tr>
                                <td class="mayhen_admin_ai_nowrap">{{ $log->created_at?->format('d/m H:i') }}<br><small>{{ $log->latency_ms }}ms</small></td>
                                <td>{{ $log->user_message }}</td>
                                <td>
                                    <div class="mayhen_admin_ai_log_reply">{{ \Illuminate\Support\Str::limit($log->bot_reply, 180) }}</div>
                                    @if(! empty($meta['draft_model']))
                                        <small class="mayhen_admin_ai_meta">
                                            Model: {{ $meta['draft_model'] }}
                                            @if(! empty($meta['reviewer_model']))
                                                | Critic: {{ $meta['reviewer_model'] }} ({{ $meta['review_score'] ?? '-' }}/100)
                                            @endif
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="mayhen_admin_ai_tag {{ $log->response_mode === 'blocked_security' ? 'mayhen_admin_ai_tag_danger' : '' }}">
                                        {{ $log->response_mode_label }}
                                    </span>
                                    <div class="mayhen_admin_ai_meta">Rủi ro: {{ $log->risk_level_label }} • Review: {{ $log->review_status ?? '-' }}</div>
                                    @if($log->feedback)
                                        <div class="mayhen_admin_ai_meta">
                                            Đánh giá: {{ $log->feedback === 'helpful' ? 'Hữu ích' : 'Chưa đúng' }}
                                            @if($log->feedback_valid === false)
                                                <strong class="mayhen_admin_ai_flag_text">(Lọc spam)</strong>
                                            @endif
                                        </div>
                                    @endif
                                    @if(! empty($log->security_flags))
                                        <div class="mayhen_admin_ai_flag_text">{{ implode(', ', $log->security_flags) }}</div>
                                    @endif
                                </td>
                                <td class="mayhen_admin_ai_nowrap">
                                    @if($log->response_mode !== 'blocked_security')
                                        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'approved', 'prefill_q' => $log->user_message]) }}#mayhenAdminTrainFormCard" class="mayhen_admin_ai_btn">
                                            Tạo tri thức
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="mayhen_admin_ai_empty">Chưa có lượt hội thoại nào được ghi nhận.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mayhen_admin_ai_pagination">{{ $logs->links() }}</div>
        </section>
    @endif

    {{-- TAB 4: AI GOVERNANCE & POLICY CONTROL --}}
    @if($tab === 'governance')
        <section class="mayhen_admin_ai_card">
            <h2 class="mayhen_admin_ai_card_title">Chính Sách Quản Trị Hệ Thống AI (AI Governance &amp; Kill Switch)</h2>
            <form method="POST" action="{{ route('admin.ai_chatbot.governance.update') }}" class="mayhen_admin_ai_form_grid">
                @csrf
                <div class="mayhen_admin_ai_field mayhen_admin_ai_field_full mayhen_admin_ai_kill_box">
                    <label class="mayhen_admin_ai_checkbox">
                        <input type="checkbox" name="kill_switch" value="1" @checked($policy['kill_switch'])>
                        <strong>Bật chế độ Khóa Khẩn Cấp (Emergency Kill Switch) — Ngắt gọi mọi model AI bên ngoài ngay lập tức, chuyển 100% sang Fallback xác thực nội bộ</strong>
                    </label>
                </div>

                <label class="mayhen_admin_ai_field">
                    <span>Draft Model (Reasoning &amp; Sales Agent)</span>
                    <input type="text" name="draft_model" list="mayhen9RouterModels" value="{{ $policy['draft_model'] }}" placeholder="Ví dụ: my-combo hoặc ag/gemini-3.8-flash">
                </label>

                <label class="mayhen_admin_ai_field">
                    <span>Reviewer Model mạnh nhất (Critic &amp; Fact-Checker Agent)</span>
                    <input type="text" name="reviewer_model" list="mayhen9RouterModels" value="{{ $policy['reviewer_model'] }}" placeholder="Ví dụ: google-6-astra hoặc cx/gpt-6-astra">
                </label>

                <datalist id="mayhen9RouterModels">
                    @foreach($availableModels as $m)
                        <option value="{{ $m }}"></option>
                    @endforeach
                </datalist>

                <label class="mayhen_admin_ai_field">
                    <span>Giới hạn số vòng phản biện Critic (0 - 2, cấm lặp vô hạn)</span>
                    <input type="number" name="max_review_rounds" min="0" max="2" value="{{ $policy['max_review_rounds'] }}" required>
                </label>

                <label class="mayhen_admin_ai_field">
                    <span>Ngân sách Token tối đa mỗi ngày</span>
                    <input type="number" name="daily_token_budget" min="10000" max="50000000" step="10000" value="{{ $policy['daily_token_budget'] }}" required>
                </label>

                <label class="mayhen_admin_ai_field">
                    <span>Thời hạn lưu dữ liệu Retention (7 - 365 ngày)</span>
                    <input type="number" name="retention_days" min="7" max="365" value="{{ $policy['retention_days'] }}" required>
                </label>

                <div class="mayhen_admin_ai_field">
                    <span>Cơ chế kiểm soát vận hành</span>
                    <label class="mayhen_admin_ai_checkbox">
                        <input type="checkbox" name="enable_reviewer_for_complex" value="1" @checked($policy['enable_reviewer_for_complex'])>
                        <span>Bật Critic Reviewer AI cho câu phức tạp &amp; rủi ro cao</span>
                    </label>
                    <label class="mayhen_admin_ai_checkbox">
                        <input type="checkbox" name="memory_enabled" value="1" @checked($policy['memory_enabled'])>
                        <span>Bật Scoped Customer Memory (cô lập theo từng tài khoản/phiên)</span>
                    </label>
                </div>

                <div class="mayhen_admin_ai_field mayhen_admin_ai_field_full">
                    <span>Nguồn tri thức được phép AI truy xuất (Allowlist)</span>
                    <div class="mayhen_admin_ai_checkbox_group">
                        @php
                            $sourceLabels = [
                                'approved_knowledge' => 'Kho Tri Thức Admin Đã Duyệt',
                                'templates' => 'Kho Mẫu Thiệp Đang Hoạt Động',
                                'plans' => 'Bảng Giá & Gói Dịch Vụ Chính Thức',
                                'client_docs' => 'Tài Liệu Hướng Dẫn Client Docs',
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

                <div class="mayhen_admin_ai_form_actions">
                    <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary">Lưu Chính Sách AI Governance</button>
                </div>
            </form>
        </section>

        <section class="mayhen_admin_ai_card">
            <h2 class="mayhen_admin_ai_card_title">Thực Thi Chính Sách Retention &amp; Quyền Riêng Tư Dữ Liệu</h2>
            <p class="mayhen_admin_ai_subtitle">
                Tự động dọn dẹp mọi bản ghi Customer Memory và Nhật ký hội thoại cũ hơn <strong>{{ $policy['retention_days'] }} ngày</strong> (cũng tự động chạy mỗi ngày lúc 03:30 sáng qua Scheduler).
            </p>
            <form method="POST" action="{{ route('admin.ai_chatbot.retention.purge') }}">
                @csrf
                <button type="submit" class="mayhen_admin_ai_btn">Dọn Dẹp Dữ Liệu Quá Hạn Retention Ngay</button>
            </form>
        </section>
    @endif
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/admin/js/pages/ai-chatbot.js') }}?v={{ file_exists(public_path('assets/admin/js/pages/ai-chatbot.js')) ? filemtime(public_path('assets/admin/js/pages/ai-chatbot.js')) : time() }}"></script>
@endpush
