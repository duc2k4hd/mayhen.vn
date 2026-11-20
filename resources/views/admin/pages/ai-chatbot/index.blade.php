@extends('admin.layouts.master')

@section('title', 'Huấn Luyện & Kiểm Duyệt Trợ Lý AI')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/ai-chatbot.css') }}?v={{ file_exists(public_path('assets/admin/css/pages/ai-chatbot.css')) ? filemtime(public_path('assets/admin/css/pages/ai-chatbot.css')) : time() }}">
@endpush

@section('content')
<div class="mayhen_admin_ai_wrap">
    <div class="mayhen_admin_ai_header">
        <div>
            <h1 class="mayhen_admin_ai_title">Huấn Luyện &amp; Kiểm Duyệt Trợ Lý Chatbot AI</h1>
            <p class="mayhen_admin_ai_subtitle">
                Kiến trúc Zero-Trust: Chỉ trả lời từ tri thức đã duyệt, kho mẫu thiệp thật và tài liệu hướng dẫn Client Docs. Mọi góp ý từ khách hàng đều phải qua kiểm duyệt trước khi áp dụng.
            </p>
        </div>
        <div class="mayhen_admin_ai_runtime_box">
            <span class="mayhen_admin_ai_badge {{ $aiRuntimeConfig['enabled'] && $aiRuntimeConfig['model'] !== '' ? 'mayhen_admin_ai_badge_ok' : 'mayhen_admin_ai_badge_warn' }}">
                9Router: {{ $aiRuntimeConfig['model'] ?: 'Chưa cấu hình model' }}
            </span>
            @if(! empty($aiRuntimeConfig['last_provider_status']) && $aiRuntimeConfig['last_provider_status'] !== 'ok')
                <span class="mayhen_admin_ai_runtime_note">
                    Trạng thái LLM gần nhất: <code>{{ $aiRuntimeConfig['last_provider_status'] }}</code> — Chatbot tự động dùng chế độ Tri thức Xác thực nội bộ.
                </span>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="mayhen_admin_ai_alert mayhen_admin_ai_alert_success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mayhen_admin_ai_alert mayhen_admin_ai_alert_danger">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Thẻ KPI --}}
    <div class="mayhen_admin_ai_stats">
        <div class="mayhen_admin_ai_stat_card">
            <span class="mayhen_admin_ai_stat_label">Tri thức đang hoạt động</span>
            <strong class="mayhen_admin_ai_stat_value">{{ number_format($stats['approved_count']) }}</strong>
        </div>
        <div class="mayhen_admin_ai_stat_card">
            <span class="mayhen_admin_ai_stat_label">Góp ý khách chờ duyệt</span>
            <strong class="mayhen_admin_ai_stat_value mayhen_admin_ai_stat_warn">{{ number_format($stats['pending_count']) }}</strong>
        </div>
        <div class="mayhen_admin_ai_stat_card">
            <span class="mayhen_admin_ai_stat_label">Tổng lượt hội thoại</span>
            <strong class="mayhen_admin_ai_stat_value">{{ number_format($stats['total_chats']) }}</strong>
        </div>
        <div class="mayhen_admin_ai_stat_card">
            <span class="mayhen_admin_ai_stat_label">Đã chặn Prompt Injection</span>
            <strong class="mayhen_admin_ai_stat_value mayhen_admin_ai_stat_danger">{{ number_format($stats['blocked_security_count']) }}</strong>
        </div>
    </div>

    {{-- Tabs điều hướng --}}
    <div class="mayhen_admin_ai_tabs">
        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'approved']) }}" class="mayhen_admin_ai_tab {{ $tab === 'approved' ? 'mayhen_admin_ai_tab_active' : '' }}">
            Kho Tri Thức Đã Duyệt ({{ $stats['approved_count'] }})
        </a>
        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'pending']) }}" class="mayhen_admin_ai_tab {{ $tab === 'pending' ? 'mayhen_admin_ai_tab_active' : '' }}">
            Hàng Đợi Duyệt Khách Hàng ({{ $stats['pending_count'] }})
        </a>
        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'logs']) }}" class="mayhen_admin_ai_tab {{ $tab === 'logs' ? 'mayhen_admin_ai_tab_active' : '' }}">
            Nhật Ký Hội Thoại &amp; Bảo Mật ({{ $stats['total_chats'] }})
        </a>
    </div>

    {{-- TAB 1: KHO TRI THỨC ĐÃ DUYỆT & FORM HUẤN LUYỆN MỚI --}}
    @if($tab === 'approved')
        <section class="mayhen_admin_ai_card" id="mayhenAdminTrainFormCard">
            <h2 class="mayhen_admin_ai_card_title">Huấn Luyện Tri Thức Mới (Admin Xác Thực Trực Tiếp)</h2>
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
                    <input type="text" name="title" id="mayhenAdminTrainTitle" maxlength="190" required value="{{ old('title', request('prefill_q')) }}" placeholder="Ví dụ: Mẫu thiệp cưới phong cách hoàng gia đỏ sang trọng nào nổi bật nhất?">
                </label>

                <label class="mayhen_admin_ai_field mayhen_admin_ai_field_full">
                    <span>Từ khóa kích hoạt (cách nhau bởi dấu phẩy)</span>
                    <input type="text" name="keywords" maxlength="500" value="{{ old('keywords') }}" placeholder="hoàng gia, màu đỏ, sang trọng, tem sáp, phong thư">
                </label>

                <label class="mayhen_admin_ai_field mayhen_admin_ai_field_full">
                    <span>Câu trả lời chuẩn xác (Chỉ dùng văn bản thuần, không HTML)</span>
                    <textarea name="answer" rows="4" maxlength="2000" required placeholder="Nhập nội dung chính xác để AI dựa vào đó tư vấn cho khách hàng...">{{ old('answer') }}</textarea>
                </label>

                <label class="mayhen_admin_ai_field">
                    <span>Gắn kèm Mẫu Thiệp (Tùy chọn)</span>
                    <select name="template_slug">
                        <option value="">— Không gắn mẫu cố định —</option>
                        @foreach($templates as $tpl)
                            <option value="{{ $tpl->slug }}" @selected(old('template_slug') === $tpl->slug)>{{ $tpl->name }} ({{ $tpl->category }})</option>
                        @endforeach
                    </select>
                </label>

                <label class="mayhen_admin_ai_field">
                    <span>Gắn kèm Tài Liệu Client Docs (Tùy chọn)</span>
                    <select name="doc_url">
                        <option value="">— Không gắn bài Docs cố định —</option>
                        @foreach($clientDocOptions as $docOpt)
                            <option value="{{ $docOpt['url'] }}" @selected(old('doc_url') === $docOpt['url'])>{{ $docOpt['label'] }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="mayhen_admin_ai_form_actions">
                    <input type="hidden" name="is_active" value="1">
                    <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary">Lưu &amp; Kích Hoạt Tri Thức</button>
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
                    <article class="mayhen_admin_ai_item">
                        <div class="mayhen_admin_ai_item_head">
                            <div>
                                <span class="mayhen_admin_ai_tag">{{ $entry->category_label }}</span>
                                <span class="mayhen_admin_ai_tag {{ $entry->source === 'customer' ? 'mayhen_admin_ai_tag_customer' : '' }}">
                                    {{ $entry->source === 'customer' ? 'Khách góp ý (Đã duyệt)' : 'Admin huấn luyện' }}
                                </span>
                                <span class="mayhen_admin_ai_meta">Ưu tiên: {{ $entry->priority }} • Đã dùng: {{ $entry->usage_count }} lượt</span>
                            </div>
                            <form method="POST" action="{{ route('admin.ai_chatbot.entries.destroy', $entry) }}" class="mayhen_admin_ai_inline_form" data-confirm-delete="Xóa mục tri thức này khỏi kho AI?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_danger_ghost">Xóa</button>
                            </form>
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
                                <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary">Cập nhật</button>
                            </div>
                        </form>
                    </article>
                @empty
                    <p class="mayhen_admin_ai_empty">Chưa có mục tri thức nào khớp bộ lọc.</p>
                @endforelse
            </div>

            <div class="mayhen_admin_ai_pagination">{{ $approvedEntries->links() }}</div>
        </section>
    @endif

    {{-- TAB 2: HÀNG ĐỢI KIỂM DUYỆT ĐÓNG GÓP TỪ KHÁCH HÀNG --}}
    @if($tab === 'pending')
        <section class="mayhen_admin_ai_card">
            <h2 class="mayhen_admin_ai_card_title">Hàng Đợi Kiểm Duyệt Đề Xuất Huấn Luyện Từ Khách Hàng</h2>
            <p class="mayhen_admin_ai_subtitle">
                Các mục dưới đây đang bị cách ly hoàn toàn khỏi AI live. Hãy đọc kỹ, chỉnh sửa lại cho chuẩn xác và chỉ bấm Phê duyệt khi nội dung hoàn toàn đáng tin cậy.
            </p>

            <div class="mayhen_admin_ai_list">
                @forelse($pendingEntries as $entry)
                    <article class="mayhen_admin_ai_item mayhen_admin_ai_item_pending">
                        <div class="mayhen_admin_ai_item_head">
                            <div>
                                <span class="mayhen_admin_ai_tag mayhen_admin_ai_tag_customer">Chờ duyệt</span>
                                <span class="mayhen_admin_ai_tag">{{ $entry->category_label }}</span>
                                <span class="mayhen_admin_ai_meta">Gửi lúc: {{ $entry->created_at?->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.ai_chatbot.entries.approve', $entry) }}" class="mayhen_admin_ai_edit_form">
                            @csrf
                            <label class="mayhen_admin_ai_field">
                                <span>Câu hỏi khách gửi (có thể biên tập lại)</span>
                                <input type="text" name="title" value="{{ $entry->title }}" maxlength="190" required>
                            </label>
                            <label class="mayhen_admin_ai_field">
                                <span>Từ khóa kích hoạt</span>
                                <input type="text" name="keywords" value="{{ $entry->keywords }}" maxlength="500" placeholder="Nhập thêm từ khóa liên quan...">
                            </label>
                            <label class="mayhen_admin_ai_field">
                                <span>Câu trả lời đề xuất (Admin kiểm tra &amp; hiệu đính trước khi duyệt)</span>
                                <textarea name="answer" rows="3" maxlength="2000" required>{{ $entry->answer }}</textarea>
                            </label>
                            <div class="mayhen_admin_ai_edit_footer">
                                <select name="template_slug">
                                    <option value="">Mẫu thiệp: Không</option>
                                    @foreach($templates as $tpl)
                                        <option value="{{ $tpl->slug }}">{{ $tpl->name }}</option>
                                    @endforeach
                                </select>
                                <select name="doc_url">
                                    <option value="">Docs: Không</option>
                                    @foreach($clientDocOptions as $docOpt)
                                        <option value="{{ $docOpt['url'] }}">{{ $docOpt['label'] }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_primary">Phê Duyệt &amp; Kích Hoạt</button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('admin.ai_chatbot.entries.reject', $entry) }}" class="mayhen_admin_ai_reject_bar">
                            @csrf
                            <input type="text" name="rejection_reason" maxlength="190" placeholder="Lý do từ chối (tùy chọn)...">
                            <button type="submit" class="mayhen_admin_ai_btn mayhen_admin_ai_btn_danger_ghost">Từ chối</button>
                        </form>
                    </article>
                @empty
                    <p class="mayhen_admin_ai_empty">Tuyệt vời! Không có đề xuất nào đang chờ duyệt.</p>
                @endforelse
            </div>

            <div class="mayhen_admin_ai_pagination">{{ $pendingEntries->links() }}</div>
        </section>
    @endif

    {{-- TAB 3: NHẬT KÝ HỘI THOẠI & CẢNH BÁO BẢO MẬT --}}
    @if($tab === 'logs')
        <section class="mayhen_admin_ai_card">
            <h2 class="mayhen_admin_ai_card_title">Nhật Ký Hội Thoại &amp; Tường Lửa Zero-Trust</h2>
            <div class="mayhen_admin_ai_table_wrap">
                <table class="mayhen_admin_ai_table">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Câu hỏi khách hàng</th>
                            <th>Phản hồi của Trợ lý</th>
                            <th>Chế độ &amp; Bảo mật</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="mayhen_admin_ai_nowrap">{{ $log->created_at?->format('d/m H:i') }}<br><small>{{ $log->latency_ms }}ms</small></td>
                                <td>{{ $log->user_message }}</td>
                                <td>
                                    <div class="mayhen_admin_ai_log_reply">{{ \Illuminate\Support\Str::limit($log->bot_reply, 180) }}</div>
                                    @if(! empty($log->matched_template_slugs))
                                        <small class="mayhen_admin_ai_meta">Mẫu gợi ý: {{ implode(', ', $log->matched_template_slugs) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="mayhen_admin_ai_tag {{ $log->response_mode === 'blocked_security' ? 'mayhen_admin_ai_tag_danger' : '' }}">
                                        {{ $log->response_mode_label }}
                                    </span>
                                    @if($log->feedback)
                                        <div class="mayhen_admin_ai_meta">Đánh giá: {{ $log->feedback === 'helpful' ? 'Hữu ích' : 'Chưa đúng' }}</div>
                                    @endif
                                    @if(! empty($log->security_flags))
                                        <div class="mayhen_admin_ai_flag_text">{{ implode(', ', $log->security_flags) }}</div>
                                    @endif
                                </td>
                                <td class="mayhen_admin_ai_nowrap">
                                    @if($log->response_mode !== 'blocked_security')
                                        <a href="{{ route('admin.ai_chatbot.index', ['tab' => 'approved', 'prefill_q' => $log->user_message]) }}#mayhenAdminTrainFormCard" class="mayhen_admin_ai_btn">
                                            Dạy câu này
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
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/admin/js/pages/ai-chatbot.js') }}?v={{ file_exists(public_path('assets/admin/js/pages/ai-chatbot.js')) ? filemtime(public_path('assets/admin/js/pages/ai-chatbot.js')) : time() }}"></script>
@endpush
