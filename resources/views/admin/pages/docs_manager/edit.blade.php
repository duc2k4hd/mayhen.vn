@extends('admin.layouts.master')

@section('title', 'Chỉnh Sửa Tài Liệu | ' . \App\Models\PlatformSetting::getBrandName() . ' Admin')
@section('page_title', 'Chỉnh Sửa Tài Liệu Docs')

@push('styles')
<style>
    .mayhen_admin_form_layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 24px;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .mayhen_admin_form_layout {
            grid-template-columns: 1fr;
        }
    }
    .mayhen_admin_card {
        background: #FFFFFF;
        border: 1px solid var(--mh-admin-border, #E5E7EB);
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        margin-bottom: 24px;
    }
    .mayhen_admin_card_title {
        font-size: 15px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .mayhen_admin_form_group {
        margin-bottom: 18px;
    }
    .mayhen_admin_label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }
    .mayhen_admin_label .required {
        color: #DC2626;
    }
    .mayhen_admin_input,
    .mayhen_admin_select,
    .mayhen_admin_textarea {
        width: 100%;
        padding: 9px 12px;
        font-size: 13.5px;
        border: 1px solid var(--mh-admin-border, #E5E7EB);
        border-radius: 8px;
        background: #FFFFFF;
        color: #1F2937;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
        box-sizing: border-box;
    }
    .mayhen_admin_input:focus,
    .mayhen_admin_select:focus,
    .mayhen_admin_textarea:focus {
        border-color: #2563EB;
        box-shadow: 0 0 0 2px rgba(37,99,235,0.15);
    }
    .mayhen_admin_help_text {
        font-size: 12px;
        color: #6B7280;
        margin-top: 4px;
        line-height: 1.4;
    }
    .mayhen_admin_toolbar {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        background: #F9FAFB;
        border: 1px solid var(--mh-admin-border, #E5E7EB);
        border-bottom: none;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        flex-wrap: wrap;
    }
    .mayhen_admin_tb_btn {
        background: #FFFFFF;
        border: 1px solid #D1D5DB;
        border-radius: 4px;
        padding: 4px 8px;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        transition: all 0.15s;
    }
    .mayhen_admin_tb_btn:hover {
        background: #E5E7EB;
        color: #111827;
    }
    .mayhen_admin_editor_textarea {
        border-top-left-radius: 0;
        border-top-right-radius: 0;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 13.5px;
        line-height: 1.6;
        min-height: 520px;
        resize: vertical;
    }
    .mayhen_admin_space_selector {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 16px;
    }
    .mayhen_admin_space_opt {
        position: relative;
    }
    .mayhen_admin_space_opt input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .mayhen_admin_space_card {
        border: 2px solid #E5E7EB;
        border-radius: 8px;
        padding: 12px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: #FFFFFF;
    }
    .mayhen_admin_space_opt input:checked + .mayhen_admin_space_card {
        border-color: #2563EB;
        background: #EFF6FF;
    }
    .mayhen_admin_space_card .space_title {
        font-weight: 700;
        font-size: 13px;
        color: #1F2937;
        margin-bottom: 2px;
    }
    .mayhen_admin_space_opt input:checked + .mayhen_admin_space_card .space_title {
        color: #1D4ED8;
    }
    .mayhen_admin_space_card .space_desc {
        font-size: 11px;
        color: #6B7280;
    }
    .mayhen_admin_btn_save {
        width: 100%;
        background: #2563EB;
        color: #FFFFFF;
        padding: 11px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .mayhen_admin_btn_save:hover {
        background: #1D4ED8;
    }
    .mayhen_admin_btn_cancel {
        width: 100%;
        background: #FFFFFF;
        color: #4B5563;
        border: 1px solid #D1D5DB;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 10px;
        transition: all 0.2s;
    }
    .mayhen_admin_btn_cancel:hover {
        background: #F3F4F6;
        color: #111827;
    }
</style>
@endpush

@section('content')
<div class="mayhen_admin_content_wrap">
    <!-- Breadcrumb / Back button -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <a href="{{ route('admin.docs_manager.index', ['space' => $space]) }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: #4B5563; text-decoration: none;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            <span>Quay lại Danh sách Docs</span>
        </a>

        @php
            $liveUrl = ($space === 'client')
                ? route('docs.show', ['category' => $category, 'slug' => $slug])
                : route('admin.docs.show', ['category' => $category, 'slug' => $slug]);
        @endphp
        <a href="{{ $liveUrl }}" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: #2563EB; text-decoration: none;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            <span>Xem trực tiếp bài viết trên Web</span>
        </a>
    </div>

    @if($errors->any())
        <div style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; font-size: 13px;">
            <div style="font-weight: 700; margin-bottom: 6px;">Vui lòng kiểm tra lại các trường dữ liệu:</div>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.docs_manager.update', ['space' => $space, 'category' => $category, 'slug' => $slug]) }}">
        @csrf
        @method('PUT')

        <div class="mayhen_admin_form_layout">
            <!-- Left Main Column: Content Editor -->
            <div>
                <div class="mayhen_admin_card">
                    <div class="mayhen_admin_card_title">
                        <span>📝 Nội Dung & Định Dạng Tài Liệu [{{ $article['id'] ?? $slug }}]</span>
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_label" for="doc_title">Tiêu Đề Bài Viết <span class="required">*</span></label>
                        <input type="text" id="doc_title" name="title" value="{{ old('title', $article['title'] ?? '') }}" class="mayhen_admin_input" placeholder="Tiêu đề bài viết..." required>
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_label" for="doc_summary">Tóm Tắt Ngắn (Summary)</label>
                        <textarea id="doc_summary" name="summary" rows="2" class="mayhen_admin_textarea" placeholder="Tóm tắt ngắn gọn về tài liệu...">{{ old('summary', $article['summary'] ?? '') }}</textarea>
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_label">Nội Dung Chi Tiết Bài Viết <span class="required">*</span></label>
                        @include('admin.pages.docs_manager.partials.editor_canvas')
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings & Metadata -->
            <div>
                <!-- Space Choice Card -->
                <div class="mayhen_admin_card">
                    <div class="mayhen_admin_card_title">
                        <span>🌐 Không Gian & Danh Mục</span>
                    </div>

                    <div class="mayhen_admin_space_selector">
                        <label class="mayhen_admin_space_opt">
                            <input type="radio" name="space" value="client" {{ old('space', $space) === 'client' ? 'checked' : '' }} onchange="onSpaceChange('client')">
                            <div class="mayhen_admin_space_card">
                                <div class="space_title">💍 Client Docs</div>
                                <div class="space_desc">Khách hàng</div>
                            </div>
                        </label>
                        <label class="mayhen_admin_space_opt">
                            <input type="radio" name="space" value="admin" {{ old('space', $space) === 'admin' ? 'checked' : '' }} onchange="onSpaceChange('admin')">
                            <div class="mayhen_admin_space_card">
                                <div class="space_title">🛡️ Admin SOP</div>
                                <div class="space_desc">Vận hành Admin</div>
                            </div>
                        </label>
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_label" for="doc_id">Mã Định Danh (ID) <span class="required">*</span></label>
                        <input type="text" id="doc_id" name="id" value="{{ old('id', $article['id'] ?? $slug) }}" class="mayhen_admin_input" required style="font-family: monospace;">
                        <div class="mayhen_admin_help_text">Nếu sửa ID, hệ thống sẽ tự động đổi tên tệp tin `.md` tương ứng.</div>
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_label" for="category_slug_select">Chuyên Mục Hiện Có</label>
                        <select id="category_slug_select" name="category_slug" class="mayhen_admin_select" onchange="onCategorySelect(this.value)">
                            <option value="">-- Chọn chuyên mục có sẵn --</option>
                            @foreach($categoryOptions as $opt)
                                <option value="{{ $opt['slug'] }}" data-space="{{ $opt['space'] }}" data-name="{{ $opt['name'] }}" {{ old('category_slug', $category) === $opt['slug'] ? 'selected' : '' }}>
                                    [{{ strtoupper($opt['space']) }}] {{ $opt['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_label" for="category_name">Tên Chuyên Mục Hiển Thị <span class="required">*</span></label>
                        <input type="text" id="category_name" name="category_name" value="{{ old('category_name', $article['category'] ?? '') }}" class="mayhen_admin_input" required>
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_label" for="new_category_slug">Slug Thư Mục Mới (Nếu chuyển)</label>
                        <input type="text" id="new_category_slug" name="new_category_slug" value="{{ old('new_category_slug') }}" class="mayhen_admin_input" placeholder="Để trống nếu giữ nguyên" style="font-family: monospace;">
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_label" for="doc_order">Thứ Tự Sắp Xếp (Order)</label>
                        <input type="number" id="doc_order" name="order" value="{{ old('order', $article['order'] ?? 1) }}" min="1" max="999" class="mayhen_admin_input">
                    </div>
                </div>

                <!-- Metadata Card -->
                <div class="mayhen_admin_card">
                    <div class="mayhen_admin_card_title">
                        <span>⚙️ Thuộc Tính & Phân Quyền</span>
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_label" for="doc_reader">Đối Tượng Độc Giả (Reader)</label>
                        <input type="text" id="doc_reader" name="reader" value="{{ old('reader', $article['reader'] ?? '') }}" class="mayhen_admin_input">
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_label" for="doc_tags">Thẻ Gắn Kèm (Tags)</label>
                        @php
                            $tagsVal = is_array($article['tags'] ?? null) ? implode(', ', $article['tags']) : ($article['tags'] ?? '');
                        @endphp
                        <input type="text" id="doc_tags" name="tags" value="{{ old('tags', $tagsVal) }}" class="mayhen_admin_input" placeholder="tag1, tag2">
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_label" for="doc_role">Phân Quyền Truy Cập (Required Role)</label>
                        @php
                            $currentRole = old('required_role', $article['required_role'] ?? 'all');
                        @endphp
                        <select id="doc_role" name="required_role" class="mayhen_admin_select">
                            <option value="all" {{ $currentRole === 'all' ? 'selected' : '' }}>Tất cả (Mọi người đều xem được)</option>
                            <option value="admin" {{ $currentRole === 'admin' ? 'selected' : '' }}>Admin (Quản trị viên trở lên)</option>
                            <option value="manager" {{ $currentRole === 'manager' ? 'selected' : '' }}>Manager (Trưởng nhóm vận hành)</option>
                            <option value="finance" {{ $currentRole === 'finance' ? 'selected' : '' }}>Finance (Kế toán & Đối soát)</option>
                            <option value="support" {{ $currentRole === 'support' ? 'selected' : '' }}>Support (CSKH & Hỗ trợ)</option>
                            <option value="customer" {{ $currentRole === 'customer' ? 'selected' : '' }}>Customer (Khách hàng đã đăng nhập)</option>
                        </select>
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_label" for="doc_risk">Mức Độ Rủi Ro (Risk Level)</label>
                        @php
                            $currentRisk = old('risk_level', $article['risk_level'] ?? 'LOW');
                        @endphp
                        <select id="doc_risk" name="risk_level" class="mayhen_admin_select">
                            <option value="LOW" {{ $currentRisk === 'LOW' ? 'selected' : '' }}>LOW (Thấp)</option>
                            <option value="MEDIUM" {{ $currentRisk === 'MEDIUM' ? 'selected' : '' }}>MEDIUM (Trung bình)</option>
                            <option value="HIGH" {{ $currentRisk === 'HIGH' ? 'selected' : '' }}>HIGH (Cao)</option>
                            <option value="CRITICAL" {{ $currentRisk === 'CRITICAL' ? 'selected' : '' }}>CRITICAL (Nghiêm trọng)</option>
                        </select>
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_label" for="mapped_page">Trang Liên Kết (Mapped Page)</label>
                        <input type="text" id="mapped_page" name="mapped_page" value="{{ old('mapped_page', $article['mapped_page'] ?? '') }}" class="mayhen_admin_input">
                    </div>

                    <div class="mayhen_admin_form_group">
                        <label class="mayhen_admin_label" for="mapped_route">Route Liên Kết (Mapped Route)</label>
                        <input type="text" id="mapped_route" name="mapped_route" value="{{ old('mapped_route', $article['mapped_route'] ?? '') }}" class="mayhen_admin_input">
                    </div>

                    <!-- Action Buttons -->
                    <div style="margin-top: 24px;">
                        <button type="submit" class="mayhen_admin_btn_save">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Lưu Cập Nhật Tài Liệu</span>
                        </button>
                        <a href="{{ route('admin.docs_manager.index', ['space' => $space]) }}" class="mayhen_admin_btn_cancel">
                            Hủy Bỏ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function onCategorySelect(val) {
    if (!val) return;
    const sel = document.getElementById('category_slug_select');
    const opt = sel.options[sel.selectedIndex];
    if (opt) {
        const catName = opt.getAttribute('data-name');
        if (catName) {
            document.getElementById('category_name').value = catName;
        }
        document.getElementById('new_category_slug').value = '';
    }
}

function onSpaceChange(space) {
    const select = document.getElementById('category_slug_select');
    Array.from(select.options).forEach(opt => {
        if (!opt.value) return;
        const optSpace = opt.getAttribute('data-space');
        if (optSpace === space) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const currentSpace = document.querySelector('input[name="space"]:checked')?.value || '{{ $space }}';
    onSpaceChange(currentSpace);
});
</script>
@endsection
