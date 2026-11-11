@extends('admin.layouts.master')

@section('title', 'Quản Trị Mẫu Phản Hồi Nhanh & Macro CSKH')

@section('content')
<div class="mayhen_admin_canned_cockpit">
    <!-- Header Section -->
    <div class="mayhen_admin_page_header" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="mayhen_admin_badge mayhen_admin_badge--gold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Customer Service Automation</span>
                <span style="color: var(--admin-text-muted); font-size: 0.85rem;">Mục 17.1 & Screen contract 05 PRD V3</span>
            </div>
            <h1 style="font-size: 1.6rem; font-weight: 600; color: var(--admin-charcoal); margin: 0;">
                Kho Quản Trị Mẫu Phản Hồi Nhanh & Macro CSKH
            </h1>
            <p style="margin: 0.25rem 0 0 0; color: var(--admin-text-muted); font-size: 0.9rem;">
                Chuẩn hóa văn phong Quiet Luxury, rút ngắn thời gian phản hồi và hỗ trợ giải quyết sự cố tức thì cho cặp đôi.
            </p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('admin.support.index') }}" class="mayhen_admin_btn mayhen_admin_btn--outline" style="font-size: 0.85rem;">
                Quay Lại Ticket Center
            </a>
            <button type="button" class="mayhen_admin_btn mayhen_admin_btn--primary" id="btn-open-create-modal" style="font-size: 0.85rem;">
                + Tạo Mẫu Macro Mới
            </button>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="mayhen_admin_kpi_grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid var(--admin-gold);">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Tổng Mẫu Phản Hồi</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: var(--admin-charcoal); margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['total']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Sẵn sàng áp dụng cho CSKH</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #059669;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Đang Kích Hoạt</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #059669; margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['active']) }}
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Hiển thị trên khung phản hồi ticket</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #2563EB;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Lượt Sử Dụng Tích Lũy</div>
            <div style="font-size: 1.5rem; font-weight: 700; color: #2563EB; margin: 0.4rem 0 0.25rem 0;">
                {{ number_format($kpis['total_usage']) }} <span style="font-size: 0.85rem; font-weight: normal; color: var(--admin-text-muted);">lần</span>
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Tiết kiệm thời gian xử lý ticket</div>
        </div>

        <div class="mayhen_admin_card" style="padding: 1.25rem; border-left: 3px solid #D97706;">
            <div style="font-size: 0.8rem; font-weight: 500; color: var(--admin-text-muted); text-transform: uppercase;">Chủ Đề Phổ Biến Nhất</div>
            <div style="font-size: 1.15rem; font-weight: 700; color: var(--admin-charcoal); margin: 0.6rem 0 0.25rem 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ $kpis['top_category'] }}
            </div>
            <div style="font-size: 0.8rem; color: var(--admin-text-muted);">Tần suất giải đáp sự cố cao nhất</div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="mayhen_admin_card" style="padding: 1rem 1.25rem; margin-bottom: 1.5rem;">
        <form action="{{ route('admin.support.canned.index') }}" method="GET" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                <span style="font-size: 0.85rem; font-weight: 600; color: var(--admin-charcoal); margin-right: 0.25rem;">Chủ đề:</span>
                <a href="{{ route('admin.support.canned.index', array_filter(['search' => $search])) }}" 
                   class="mayhen_admin_badge {{ empty($activeCategory) ? 'mayhen_admin_badge--gold' : 'mayhen_admin_badge--neutral' }}" style="text-decoration: none; padding: 0.35rem 0.75rem;">
                    Tất cả
                </a>
                @foreach($categories as $key => $meta)
                <a href="{{ route('admin.support.canned.index', array_filter(['category' => $key, 'search' => $search])) }}" 
                   class="mayhen_admin_badge {{ $activeCategory === $key ? 'mayhen_admin_badge--gold' : 'mayhen_admin_badge--neutral' }}" style="text-decoration: none; padding: 0.35rem 0.75rem;">
                    {{ $meta['label'] }}
                </a>
                @endforeach
            </div>

            <div style="display: flex; gap: 0.5rem; width: 100%; max-width: 340px;">
                @if(!empty($activeCategory))
                    <input type="hidden" name="category" value="{{ $activeCategory }}">
                @endif
                <input type="text" name="search" value="{{ $search }}" placeholder="Tìm theo tiêu đề, phím tắt (:safari)..." 
                       class="mayhen_admin_form_input" style="flex: 1; padding: 0.5rem 0.75rem; border: 1px solid var(--admin-border-color); border-radius: 4px; font-size: 0.85rem;">
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="padding: 0.5rem 0.85rem; font-size: 0.85rem;">
                    Tìm
                </button>
                @if($search || $activeCategory)
                <a href="{{ route('admin.support.canned.index') }}" class="mayhen_admin_btn mayhen_admin_btn--outline" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;" title="Xóa bộ lọc">
                    ✕
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="mayhen_admin_card" style="padding: 0; overflow: hidden; margin-bottom: 1.5rem;">
        <div class="mayhen_admin_table_container" style="overflow-x: auto;">
            <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--admin-border-color); text-align: left; background: var(--admin-bg-soft);">
                        <th style="padding: 0.75rem 1rem; color: var(--admin-text-muted); font-weight: 600;">Tiêu Đề & Phím Tắt</th>
                        <th style="padding: 0.75rem 1rem; color: var(--admin-text-muted); font-weight: 600;">Chủ Đề</th>
                        <th style="padding: 0.75rem 1rem; color: var(--admin-text-muted); font-weight: 600;">Trích Đoạn Nội Dung</th>
                        <th style="padding: 0.75rem 1rem; color: var(--admin-text-muted); font-weight: 600; text-align: center;">Lượt Dùng</th>
                        <th style="padding: 0.75rem 1rem; color: var(--admin-text-muted); font-weight: 600; text-align: center;">Trạng Thái</th>
                        <th style="padding: 0.75rem 1rem; color: var(--admin-text-muted); font-weight: 600; text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($responses as $item)
                    <tr style="border-bottom: 1px solid var(--admin-border-color);">
                        <td style="padding: 0.85rem 1rem;">
                            <div style="font-weight: 600; color: var(--admin-charcoal); font-size: 0.9rem;">
                                {{ $item->title }}
                            </div>
                            @if($item->shortcut)
                            <div style="margin-top: 3px;">
                                <code style="background: var(--admin-bg-soft); color: #B45309; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; border: 1px solid #FDE68A;">
                                    {{ $item->shortcut }}
                                </code>
                            </div>
                            @endif
                        </td>
                        <td style="padding: 0.85rem 1rem; white-space: nowrap;">
                            <span class="mayhen_admin_badge {{ $item->category_badge_class }}" style="font-size: 0.75rem;">
                                {{ $item->category_label }}
                            </span>
                        </td>
                        <td style="padding: 0.85rem 1rem; max-width: 380px;">
                            <div style="color: var(--admin-text-muted); line-height: 1.45; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" title="{{ $item->body }}">
                                {{ $item->body }}
                            </div>
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: center; white-space: nowrap;">
                            <span style="font-weight: 700; color: #2563EB;">{{ number_format($item->usage_count) }}</span>
                            @if($item->last_used_at)
                            <div style="font-size: 0.7rem; color: var(--admin-text-muted); margin-top: 2px;">
                                {{ $item->last_used_at->diffForHumans() }}
                            </div>
                            @endif
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: center; white-space: nowrap;">
                            <form action="{{ route('admin.support.canned.toggle', $item) }}" method="POST" style="display: inline-block;">
                                @csrf
                                <button type="submit" class="mayhen_admin_badge {{ $item->is_active ? 'mayhen_admin_badge--success' : 'mayhen_admin_badge--neutral' }}" style="border: none; cursor: pointer; font-size: 0.75rem;" title="Bấm để bật/tắt">
                                    {{ $item->is_active ? '● Đang Kích Hoạt' : '○ Tạm Dừng' }}
                                </button>
                            </form>
                        </td>
                        <td style="padding: 0.85rem 1rem; text-align: right; white-space: nowrap;">
                            <button type="button" class="mayhen_admin_btn mayhen_admin_btn--outline btn-edit-canned" 
                                    style="padding: 0.35rem 0.65rem; font-size: 0.75rem; margin-right: 0.25rem;"
                                    data-id="{{ $item->id }}"
                                    data-title="{{ $item->title }}"
                                    data-shortcut="{{ $item->shortcut }}"
                                    data-category="{{ $item->category }}"
                                    data-body="{{ $item->body }}"
                                    data-active="{{ $item->is_active ? '1' : '0' }}">
                                Sửa
                            </button>
                            <form action="{{ route('admin.support.canned.destroy', $item) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa mẫu phản hồi này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--danger" style="padding: 0.35rem 0.65rem; font-size: 0.75rem;">
                                    Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 3rem; text-align: center; color: var(--admin-text-muted);">
                            Chưa tìm thấy mẫu phản hồi nhanh nào phù hợp. Hãy tạo mẫu đầu tiên bằng nút bên trên.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($responses->hasPages())
        <div style="padding: 1rem; border-top: 1px solid var(--admin-border-color);">
            {{ $responses->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Thêm / Sửa Mẫu Macro (Pure CSS/JS) -->
<div id="canned-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div style="background: #fff; width: 100%; max-width: 600px; border-radius: 8px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--admin-border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 id="modal-title" style="margin: 0; font-size: 1.15rem; color: var(--admin-charcoal);">Tạo Mới Mẫu Phản Hồi Nhanh</h3>
            <button type="button" id="btn-close-modal" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--admin-text-muted);">✕</button>
        </div>

        <form id="canned-form" method="POST" action="{{ route('admin.support.canned.store') }}" style="padding: 1.5rem;">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--admin-charcoal);">
                    Tiêu Đề Mẫu Phản Hồi <span style="color: #EF4444;">*</span>
                </label>
                <input type="text" name="title" id="form-title" placeholder="Ví dụ: Hướng dẫn mở nhạc Safari trên iPhone" 
                       class="mayhen_admin_form_input" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px;" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--admin-charcoal);">
                        Phím Tắt Gọi Nhanh (Shortcut)
                    </label>
                    <input type="text" name="shortcut" id="form-shortcut" placeholder="Ví dụ: :safari, :qrpin" 
                           class="mayhen_admin_form_input" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px;">
                    <small style="color: var(--admin-text-muted); font-size: 0.75rem; display: block; margin-top: 0.25rem;">Bắt đầu bằng dấu hai chấm (:)</small>
                </div>

                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.35rem; color: var(--admin-charcoal);">
                        Chủ Đề Phân Loại <span style="color: #EF4444;">*</span>
                    </label>
                    <select name="category" id="form-category" class="mayhen_admin_form_select" style="width: 100%; padding: 0.55rem; border: 1px solid var(--admin-border-color); border-radius: 4px; background: #fff;" required>
                        @foreach($categories as $key => $meta)
                        <option value="{{ $key }}">{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                    <label style="font-size: 0.85rem; font-weight: 600; color: var(--admin-charcoal);">
                        Nội Dung Phản Hồi Chuẩn Mực <span style="color: #EF4444;">*</span>
                    </label>
                    <div style="font-size: 0.75rem; color: var(--admin-text-muted);">
                        Hỗ trợ biến động: <code>{customer_name}</code>, <code>{ticket_code}</code>, <code>{wedding_title}</code>
                    </div>
                </div>
                <textarea name="body" id="form-body" rows="6" placeholder="Nhập nội dung mẫu phản hồi..." 
                          class="mayhen_admin_form_input" style="width: 100%; padding: 0.65rem; border: 1px solid var(--admin-border-color); border-radius: 4px; line-height: 1.5; font-family: inherit;" required></textarea>
                
                <div style="margin-top: 0.5rem; display: flex; gap: 0.35rem; flex-wrap: wrap;">
                    <span style="font-size: 0.75rem; color: var(--admin-text-muted);">Chèn nhanh:</span>
                    <button type="button" class="btn-insert-tag" data-tag="{customer_name}" style="background: var(--admin-bg-soft); border: 1px solid var(--admin-border-color); border-radius: 3px; font-size: 0.75rem; cursor: pointer; padding: 1px 6px;">{customer_name}</button>
                    <button type="button" class="btn-insert-tag" data-tag="{wedding_title}" style="background: var(--admin-bg-soft); border: 1px solid var(--admin-border-color); border-radius: 3px; font-size: 0.75rem; cursor: pointer; padding: 1px 6px;">{wedding_title}</button>
                    <button type="button" class="btn-insert-tag" data-tag="{ticket_code}" style="background: var(--admin-bg-soft); border: 1px solid var(--admin-border-color); border-radius: 3px; font-size: 0.75rem; cursor: pointer; padding: 1px 6px;">{ticket_code}</button>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="is_active" id="form-active" value="1" checked style="width: 16px; height: 16px;">
                <label for="form-active" style="font-size: 0.85rem; color: var(--admin-charcoal); cursor: pointer;">
                    Kích hoạt mẫu này ngay sau khi lưu
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid var(--admin-border-color);">
                <button type="button" id="btn-cancel-modal" class="mayhen_admin_btn mayhen_admin_btn--outline" style="padding: 0.55rem 1.25rem;">
                    Hủy Bỏ
                </button>
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="padding: 0.55rem 1.5rem;">
                    Lưu Mẫu Phản Hồi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('canned-modal');
    const form = document.getElementById('canned-form');
    const formMethod = document.getElementById('form-method');
    const modalTitle = document.getElementById('modal-title');
    const formTitle = document.getElementById('form-title');
    const formShortcut = document.getElementById('form-shortcut');
    const formCategory = document.getElementById('form-category');
    const formBody = document.getElementById('form-body');
    const formActive = document.getElementById('form-active');

    const openModal = () => { modal.style.display = 'flex'; };
    const closeModal = () => { modal.style.display = 'none'; };

    document.getElementById('btn-open-create-modal')?.addEventListener('click', function () {
        modalTitle.textContent = 'Tạo Mới Mẫu Phản Hồi Nhanh';
        form.action = "{{ route('admin.support.canned.store') }}";
        formMethod.value = 'POST';
        formTitle.value = '';
        formShortcut.value = '';
        formCategory.value = 'general';
        formBody.value = '';
        formActive.checked = true;
        openModal();
    });

    document.getElementById('btn-close-modal')?.addEventListener('click', closeModal);
    document.getElementById('btn-cancel-modal')?.addEventListener('click', closeModal);

    document.querySelectorAll('.btn-edit-canned').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            modalTitle.textContent = 'Chỉnh Sửa Mẫu Phản Hồi Nhanh';
            form.action = `/admin/support/canned-responses/${id}`;
            formMethod.value = 'PUT';
            formTitle.value = this.getAttribute('data-title') || '';
            formShortcut.value = this.getAttribute('data-shortcut') || '';
            formCategory.value = this.getAttribute('data-category') || 'general';
            formBody.value = this.getAttribute('data-body') || '';
            formActive.checked = this.getAttribute('data-active') === '1';
            openModal();
        });
    });

    // Chèn biến placeholder vào vị trí con trỏ trong textarea
    document.querySelectorAll('.btn-insert-tag').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tag = this.getAttribute('data-tag');
            const start = formBody.selectionStart;
            const end = formBody.selectionEnd;
            const text = formBody.value;
            formBody.value = text.substring(0, start) + tag + text.substring(end);
            formBody.focus();
            formBody.selectionStart = formBody.selectionEnd = start + tag.length;
        });
    });
});
</script>
@endsection
