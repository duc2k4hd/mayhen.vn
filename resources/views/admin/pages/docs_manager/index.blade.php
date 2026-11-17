@extends('admin.layouts.master')

@section('title', 'Quản Lý Toàn Bộ Docs | Mây Hẹn Admin')
@section('page_title', 'Quản Lý Toàn Bộ Docs (Client & Admin SOP)')

@push('styles')
<style>
    .mayhen_admin_stat_grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .mayhen_admin_stat_card {
        background: #FFFFFF;
        border: 1px solid var(--mh-admin-border, #E5E7EB);
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .mayhen_admin_stat_label {
        font-size: 13px;
        color: var(--mh-admin-muted, #6B7280);
        margin-bottom: 6px;
    }
    .mayhen_admin_stat_val {
        font-size: 26px;
        font-weight: 700;
        color: var(--mh-admin-charcoal, #111827);
    }
    .mayhen_admin_filter_bar {
        background: #FFFFFF;
        border: 1px solid var(--mh-admin-border, #E5E7EB);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .mayhen_admin_filter_tabs {
        display: inline-flex;
        background: #F3F4F6;
        padding: 4px;
        border-radius: 8px;
        gap: 4px;
    }
    .mayhen_admin_filter_tab {
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 600;
        color: #4B5563;
        border-radius: 6px;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .mayhen_admin_filter_tab.is-active {
        background: #FFFFFF;
        color: #111827;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .mayhen_admin_search_form {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        max-width: 520px;
    }
    .mayhen_admin_input_search {
        flex: 1;
        padding: 8px 14px;
        font-size: 13px;
        border: 1px solid var(--mh-admin-border, #E5E7EB);
        border-radius: 8px;
        background: #FFFFFF;
        color: #1F2937;
        outline: none;
    }
    .mayhen_admin_input_search:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 2px rgba(37,99,235,0.15);
    }
    .mayhen_admin_select_cat {
        padding: 8px 12px;
        font-size: 13px;
        border: 1px solid var(--mh-admin-border, #E5E7EB);
        border-radius: 8px;
        background: #FFFFFF;
        color: #1F2937;
        outline: none;
    }
    .mayhen_admin_table_card {
        background: #FFFFFF;
        border: 1px solid var(--mh-admin-border, #E5E7EB);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .mayhen_admin_table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        text-align: left;
    }
    .mayhen_admin_table th {
        background: #F9FAFB;
        padding: 12px 16px;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid var(--mh-admin-border, #E5E7EB);
        white-space: nowrap;
    }
    .mayhen_admin_table td {
        padding: 14px 16px;
        border-bottom: 1px solid #F3F4F6;
        color: #1F2937;
        vertical-align: middle;
    }
    .mayhen_admin_table tr:last-child td {
        border-bottom: none;
    }
    .mayhen_admin_table tr:hover td {
        background: #FDFBF8;
    }
    .mayhen_badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 8px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.02em;
    }
    .mayhen_badge--client {
        background: #EFF6FF;
        color: #1D4ED8;
        border: 1px solid #BFDBFE;
    }
    .mayhen_badge--admin {
        background: #FEF3C7;
        color: #B45309;
        border: 1px solid #FDE68A;
    }
    .mayhen_badge--id {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        background: #F3F4F6;
        color: #374151;
        border: 1px solid #E5E7EB;
        font-size: 11.5px;
    }
    .mayhen_admin_btn_primary {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #2563EB;
        color: #FFFFFF;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }
    .mayhen_admin_btn_primary:hover {
        background: #1D4ED8;
    }
    .mayhen_admin_action_btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        border: 1px solid #E5E7EB;
        background: #FFFFFF;
        color: #4B5563;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .mayhen_admin_action_btn:hover {
        background: #F3F4F6;
        color: #111827;
        border-color: #D1D5DB;
    }
    .mayhen_admin_action_btn--delete:hover {
        background: #FEE2E2;
        color: #DC2626;
        border-color: #FCA5A5;
    }
    .mayhen_admin_empty_state {
        padding: 48px 24px;
        text-align: center;
        color: #6B7280;
    }
    .mayhen_admin_empty_icon {
        font-size: 40px;
        margin-bottom: 12px;
    }
</style>
@endpush

@section('content')
<div class="mayhen_admin_content_wrap">
    <!-- Feedback Alerts -->
    @if(session('success'))
        <div style="background: #ECFDF5; border: 1px solid #A7F3D0; color: #065F46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- Header KPIs -->
    <div class="mayhen_admin_stat_grid">
        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_label">📚 Tổng Số Tài Liệu</div>
            <div class="mayhen_admin_stat_val" style="color: #2563EB;">{{ number_format($totalDocs) }}</div>
        </div>
        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_label">💍 Docs Khách Hàng (Client)</div>
            <div class="mayhen_admin_stat_val" style="color: #059669;">{{ number_format($clientDocsCount) }}</div>
        </div>
        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_label">🛡️ Sổ Tay SOP Vận Hành (Admin)</div>
            <div class="mayhen_admin_stat_val" style="color: #D97706;">{{ number_format($adminDocsCount) }}</div>
        </div>
        <div class="mayhen_admin_stat_card">
            <div class="mayhen_admin_stat_label">🗂️ Số Lượng Chuyên Mục</div>
            <div class="mayhen_admin_stat_val" style="color: #4F46E5;">{{ number_format($categoryCount) }}</div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="mayhen_admin_filter_bar">
        <!-- Space Filter Tabs -->
        <div class="mayhen_admin_filter_tabs">
            <a href="{{ route('admin.docs_manager.index', array_merge(request()->query(), ['space' => 'all'])) }}" class="mayhen_admin_filter_tab {{ $currentSpace === 'all' ? 'is-active' : '' }}">
                Tất Cả ({{ $totalDocs }})
            </a>
            <a href="{{ route('admin.docs_manager.index', array_merge(request()->query(), ['space' => 'client'])) }}" class="mayhen_admin_filter_tab {{ $currentSpace === 'client' ? 'is-active' : '' }}">
                Khách Hàng ({{ $clientDocsCount }})
            </a>
            <a href="{{ route('admin.docs_manager.index', array_merge(request()->query(), ['space' => 'admin'])) }}" class="mayhen_admin_filter_tab {{ $currentSpace === 'admin' ? 'is-active' : '' }}">
                Admin SOP ({{ $adminDocsCount }})
            </a>
        </div>

        <!-- Search & Category Form -->
        <form method="GET" action="{{ route('admin.docs_manager.index') }}" class="mayhen_admin_search_form">
            <input type="hidden" name="space" value="{{ $currentSpace }}">

            <select name="category" class="mayhen_admin_select_cat" onchange="this.form.submit()">
                <option value="">-- Tất cả chuyên mục --</option>
                @foreach($categoryOptions as $opt)
                    @if($currentSpace === 'all' || $opt['space'] === $currentSpace)
                        <option value="{{ $opt['slug'] }}" {{ $currentCategory === $opt['slug'] ? 'selected' : '' }}>
                            [{{ strtoupper($opt['space']) }}] {{ $opt['name'] }} ({{ $opt['count'] }})
                        </option>
                    @endif
                @endforeach
            </select>

            <input type="text" name="q" value="{{ $currentSearch }}" placeholder="Tìm kiếm theo tiêu đề, ID, tóm tắt..." class="mayhen_admin_input_search">

            <button type="submit" class="mayhen_admin_action_btn" title="Tìm kiếm">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
            @if(!empty($currentSearch) || !empty($currentCategory))
                <a href="{{ route('admin.docs_manager.index', ['space' => $currentSpace]) }}" class="mayhen_admin_action_btn" title="Đặt lại bộ lọc">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </a>
            @endif
        </form>

        <!-- Create Button -->
        <a href="{{ route('admin.docs_manager.create', ['space' => $currentSpace !== 'all' ? $currentSpace : 'client']) }}" class="mayhen_admin_btn_primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Thêm Tài Liệu Mới</span>
        </a>
    </div>

    <!-- Table List -->
    <div class="mayhen_admin_table_card">
        @if(count($articles) > 0)
            <table class="mayhen_admin_table">
                <thead>
                    <tr>
                        <th style="width: 140px;">Mã Định Danh (ID)</th>
                        <th>Tiêu Đề & Tóm Tắt</th>
                        <th style="width: 120px;">Không Gian</th>
                        <th style="width: 180px;">Chuyên Mục</th>
                        <th style="width: 70px; text-align: center;">Thứ Tự</th>
                        <th style="width: 110px;">Cập Nhật</th>
                        <th style="width: 120px; text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($articles as $art)
                        <tr>
                            <td>
                                <span class="mayhen_badge mayhen_badge--id">{{ $art['id'] }}</span>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #111827; margin-bottom: 3px;">
                                    {{ $art['title'] }}
                                </div>
                                @if(!empty($art['summary']))
                                    <div style="font-size: 12px; color: #6B7280; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $art['summary'] }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if(($art['space'] ?? '') === 'client')
                                    <span class="mayhen_badge mayhen_badge--client">Khách Hàng</span>
                                @else
                                    <span class="mayhen_badge mayhen_badge--admin">Admin SOP</span>
                                @endif
                            </td>
                            <td>
                                <span style="font-weight: 500; color: #374151;">{{ $art['category_name'] ?? $art['category_slug'] }}</span>
                            </td>
                            <td style="text-align: center; font-weight: 600; color: #6B7280;">
                                {{ $art['order'] ?? 1 }}
                            </td>
                            <td style="font-size: 12px; color: #6B7280;">
                                {{ $art['updated_at'] ?? '---' }}
                            </td>
                            <td style="text-align: right;">
                                <div style="display: inline-flex; align-items: center; gap: 6px;">
                                    <!-- Xem trên live web -->
                                    @php
                                        $viewUrl = ($art['space'] === 'client')
                                            ? route('docs.show', ['category' => $art['category_slug'], 'slug' => $art['id']])
                                            : route('admin.docs.show', ['category' => $art['category_slug'], 'slug' => $art['id']]);
                                    @endphp
                                    <a href="{{ $viewUrl }}" target="_blank" rel="noopener noreferrer" class="mayhen_admin_action_btn" title="Xem tài liệu trên giao diện web">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    </a>

                                    <!-- Sửa -->
                                    <a href="{{ route('admin.docs_manager.edit', ['space' => $art['space'], 'category' => $art['category_slug'], 'slug' => $art['id']]) }}" class="mayhen_admin_action_btn" title="Chỉnh sửa nội dung & metadata">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>

                                    <!-- Xóa -->
                                    <form method="POST" action="{{ route('admin.docs_manager.destroy', ['space' => $art['space'], 'category' => $art['category_slug'], 'slug' => $art['id']]) }}" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết [{{ $art['id'] }}] không? Thao tác này sẽ xóa tệp markdown và không thể khôi phục!')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="mayhen_admin_action_btn mayhen_admin_action_btn--delete" title="Xóa bài viết">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="mayhen_admin_empty_state">
                <div class="mayhen_admin_empty_icon">📄</div>
                <div style="font-size: 15px; font-weight: 600; color: #374151; margin-bottom: 6px;">Không tìm thấy tài liệu phù hợp</div>
                <p style="font-size: 13px; max-width: 400px; margin: 0 auto 16px auto;">Không có bài viết nào khớp với tiêu chí tìm kiếm hoặc chuyên mục bạn đã chọn.</p>
                <a href="{{ route('admin.docs_manager.create', ['space' => $currentSpace !== 'all' ? $currentSpace : 'client']) }}" class="mayhen_admin_btn_primary">
                    Thêm Tài Liệu Đầu Tiên
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
