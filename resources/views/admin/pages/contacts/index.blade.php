@extends('admin.layouts.master')

@section('title', 'Quản Lý Yêu Cầu Liên Hệ & Báo Giá')
@section('page_title', 'Quản Lý Yêu Cầu Liên Hệ & Báo Giá')

@section('content')
    {{-- KPI Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Tổng Yêu Cầu
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                {{ number_format($kpis['total'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Toàn thời gian</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #E67E22; background: #FFFDF9;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #E67E22; margin-bottom: 6px;">
                Mới Tiếp Nhận
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #E67E22;">
                {{ number_format($kpis['new'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #E67E22; margin-top: 4px; font-weight: 600;">Cần liên hệ trong 15p</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #2980B9;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Đang Tư Vấn & Báo Giá
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #2980B9;">
                {{ number_format($kpis['consulting'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #2980B9; margin-top: 4px; font-weight: 600;">Đang chăm sóc</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #27AE60; background: #F4FAF6;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: #27AE60; margin-bottom: 6px;">
                Đã Chốt Đơn
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #27AE60;">
                {{ number_format($kpis['converted'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #27AE60; margin-top: 4px; font-weight: 600;">Chuyển đổi thành công</div>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #E8F5E9; border-left: 4px solid #2E7D32; color: #1B5E20; padding: 12px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 0.88rem;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Toolbar --}}
    <div class="mayhen_admin_card" style="padding: 16px 20px; margin-bottom: 20px;">
        <form action="{{ route('admin.contacts.index') }}" method="GET" style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; margin: 0;">
            <div style="flex: 1; min-width: 200px;">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="Tìm theo tên, SĐT, ghi chú..." 
                    class="mayhen_admin_input"
                    style="margin: 0; padding: 8px 12px; font-size: 0.85rem;"
                >
            </div>

            <div>
                <select name="status" class="mayhen_admin_select" style="margin: 0; padding: 8px 12px; font-size: 0.85rem;">
                    <option value="">Tất cả trạng thái</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Mới Tiếp Nhận</option>
                    <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>Đã Liên Hệ</option>
                    <option value="consulting" {{ request('status') === 'consulting' ? 'selected' : '' }}>Đang Tư Vấn</option>
                    <option value="quoted" {{ request('status') === 'quoted' ? 'selected' : '' }}>Đã Báo Giá</option>
                    <option value="converted" {{ request('status') === 'converted' ? 'selected' : '' }}>Đã Chốt Đơn</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Đã Đóng</option>
                </select>
            </div>

            <div>
                <select name="need" class="mayhen_admin_select" style="margin: 0; padding: 8px 12px; font-size: 0.85rem;">
                    <option value="">Tất cả nhu cầu</option>
                    <option value="print-high-end" {{ request('need') === 'print-high-end' ? 'selected' : '' }}>In thiệp cao cấp</option>
                    <option value="print-minimal" {{ request('need') === 'print-minimal' ? 'selected' : '' }}>In thiệp tối giản</option>
                    <option value="print-fast" {{ request('need') === 'print-fast' ? 'selected' : '' }}>In gấp 3-5 ngày</option>
                    <option value="visit-studio" {{ request('need') === 'visit-studio' ? 'selected' : '' }}>Hẹn đến Studio</option>
                    <option value="combo-online" {{ request('need') === 'combo-online' ? 'selected' : '' }}>Combo In + Online</option>
                    <option value="partner" {{ request('need') === 'partner' ? 'selected' : '' }}>Hợp tác Studio/Planner</option>
                </select>
            </div>

            <div>
                <select name="assigned_to" class="mayhen_admin_select" style="margin: 0; padding: 8px 12px; font-size: 0.85rem;">
                    <option value="">Tất cả người phụ trách</option>
                    @foreach($staffList as $staff)
                        <option value="{{ $staff->id }}" {{ request('assigned_to') == $staff->id ? 'selected' : '' }}>
                            {{ $staff->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn_sm">
                Lọc Yêu Cầu
            </button>

            @if(request()->hasAny(['search', 'status', 'need', 'assigned_to']))
                <a href="{{ route('admin.contacts.index') }}" class="mayhen_admin_btn mayhen_admin_btn--ghost mayhen_admin_btn_sm">
                    Xóa Lọc
                </a>
            @endif
        </form>
    </div>

    {{-- Danh sách yêu cầu liên hệ --}}
    <div class="mayhen_admin_card" style="padding: 0; overflow: hidden;">
        @if($contacts->isEmpty())
            <div style="padding: 48px 24px; text-align: center; color: var(--mh-admin-muted);">
                <div style="font-size: 2rem; margin-bottom: 8px;">📬</div>
                <div style="font-size: 1rem; font-weight: 600; color: var(--mh-admin-charcoal);">Chưa có yêu cầu liên hệ nào phù hợp</div>
                <div style="font-size: 0.85rem; margin-top: 4px;">Các yêu cầu đặt hẹn và báo giá gửi từ website sẽ xuất hiện tại đây.</div>
            </div>
        @else
            <div class="mayhen_admin_table_responsive">
                <table class="mayhen_admin_table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">ID</th>
                            <th>Khách Hàng</th>
                            <th>Cưới & Số Lượng</th>
                            <th>Nhu Cầu & Tin Nhắn</th>
                            <th>Trạng Thái</th>
                            <th>Phụ Trách & Ghi Chú</th>
                            <th style="width: 140px; text-align: right;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($contacts as $item)
                            @php
                                $badge = $item->getStatusBadgeStyle();
                            @endphp
                            <tr>
                                <td>
                                    <span style="font-family: monospace; font-weight: 600; color: var(--mh-admin-muted);">#{{ $item->id }}</span>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--mh-admin-charcoal); font-size: 0.95rem;">
                                        {{ $item->name }}
                                    </div>
                                    <div style="margin-top: 4px; display: flex; align-items: center; gap: 8px;">
                                        <a href="tel:{{ $item->phone }}" style="font-family: monospace; font-weight: 600; color: #2980B9; text-decoration: none;">
                                            📞 {{ $item->phone }}
                                        </a>
                                        <a href="https://zalo.me/{{ preg_replace('/[^0-9]/', '', $item->phone) }}" target="_blank" style="font-size: 0.75rem; background: #E8F4FD; color: #0068FF; padding: 2px 6px; border-radius: 4px; text-decoration: none; font-weight: 600;">
                                            Zalo
                                        </a>
                                    </div>
                                    @if($item->email)
                                        <div style="font-size: 0.78rem; color: var(--mh-admin-muted); margin-top: 2px;">
                                            ✉️ {{ $item->email }}
                                        </div>
                                    @endif
                                    <div style="font-size: 0.72rem; color: #9C948A; margin-top: 4px;">
                                        Gửi lúc: {{ $item->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </td>
                                <td>
                                    @if($item->wedding_date)
                                        <div style="font-size: 0.88rem; font-weight: 600; color: var(--mh-admin-charcoal);">
                                            📅 {{ $item->wedding_date->format('d/m/Y') }}
                                        </div>
                                    @else
                                        <div style="font-size: 0.82rem; color: var(--mh-admin-muted);">Chưa rõ ngày</div>
                                    @endif
                                    <div style="font-size: 0.8rem; color: #7A756E; margin-top: 3px;">
                                        SL: <strong>{{ $item->quantity ?: 'Chưa chọn' }}</strong>
                                    </div>
                                </td>
                                <td style="max-width: 280px;">
                                    <div style="font-size: 0.85rem; font-weight: 600; color: #9C7A4A;">
                                        {{ $item->need_label }}
                                    </div>
                                    @if($item->message)
                                        <div style="font-size: 0.8rem; color: #4A453E; margin-top: 4px; line-height: 1.4; background: #FAF8F5; padding: 6px 10px; border-radius: 6px; font-style: italic;">
                                            "{{ Str::limit($item->message, 120) }}"
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <span style="display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; background: {{ $badge['bg'] }}; color: {{ $badge['color'] }}; border: 1px solid {{ $badge['border'] }};">
                                        {{ $item->status_label }}
                                    </span>
                                    @if($item->contacted_at)
                                        <div style="font-size: 0.72rem; color: #7A756E; margin-top: 4px;">
                                            LH: {{ $item->contacted_at->format('d/m H:i') }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem; font-weight: 600; color: var(--mh-admin-charcoal);">
                                        {{ $item->assignedStaff?->name ?: 'Chưa phân công' }}
                                    </div>
                                    @if($item->admin_notes)
                                        <div style="font-size: 0.75rem; color: #7A756E; margin-top: 3px; font-style: italic;">
                                            📝 {{ Str::limit($item->admin_notes, 60) }}
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                        <button 
                                            type="button" 
                                            class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn_sm"
                                            onclick="openEditContactModal({{ json_encode($item) }})"
                                            title="Cập nhật trạng thái và ghi chú"
                                        >
                                            Xử Lý
                                        </button>
                                        <form action="{{ route('admin.contacts.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa yêu cầu này?')" style="margin: 0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--danger mayhen_admin_btn_sm" title="Xóa yêu cầu">
                                                🗑
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="padding: 16px 20px;">
                {{ $contacts->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Xử Lý & Phân Công Lead --}}
    <div id="contactEditModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(31, 29, 26, 0.6); backdrop-filter: blur(4px); z-index: 9999; align-items: center; justify-content: center; padding: 16px;">
        <div style="background: #FFFFFF; border-radius: 14px; max-width: 520px; width: 100%; padding: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid #EFE8DD; padding-bottom: 10px;">
                <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                    Xử Lý Yêu Cầu: <span id="modalContactName" style="color: #9C7A4A;"></span>
                </h3>
                <button type="button" onclick="closeEditContactModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #7A756E;">&times;</button>
            </div>

            <form id="contactEditForm" action="" method="POST">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                        Trạng Thái Xử Lý *
                    </label>
                    <select id="modalContactStatus" name="status" class="mayhen_admin_select" required style="width: 100%;">
                        <option value="new">Mới Tiếp Nhận</option>
                        <option value="contacted">Đã Liên Hệ Khách</option>
                        <option value="consulting">Đang Tư Vấn Chuyên Sâu</option>
                        <option value="quoted">Đã Gửi Báo Giá Chi Tiết</option>
                        <option value="converted">Đã Chốt Đơn (Thành Công)</option>
                        <option value="closed">Đã Đóng / Không Có Nhu Cầu</option>
                    </select>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                        Chuyên Viên Tư Vấn Phụ Trách
                    </label>
                    <select id="modalContactAssignedTo" name="assigned_to" class="mayhen_admin_select" style="width: 100%;">
                        <option value="">-- Chưa Phân Công --</option>
                        @foreach($staffList as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->role }})</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                        Ghi Chú Nội Bộ / Tiến Trình Tư Vấn
                    </label>
                    <textarea id="modalContactNotes" name="admin_notes" class="mayhen_admin_textarea" rows="4" placeholder="Ví dụ: Đã gọi điện lúc 10h, khách hẹn tối nay gửi mẫu giấy qua Zalo, ngân sách tầm 5 triệu..."></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeEditContactModal()" class="mayhen_admin_btn mayhen_admin_btn--ghost">
                        Hủy
                    </button>
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary">
                        Lưu Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditContactModal(contact) {
            const modal = document.getElementById('contactEditModal');
            const form = document.getElementById('contactEditForm');
            const nameSpan = document.getElementById('modalContactName');
            const statusSelect = document.getElementById('modalContactStatus');
            const assignedSelect = document.getElementById('modalContactAssignedTo');
            const notesTextarea = document.getElementById('modalContactNotes');

            nameSpan.textContent = contact.name + ' (' + contact.phone + ')';
            form.action = '/admin/contacts/' + contact.id;
            statusSelect.value = contact.status || 'new';
            assignedSelect.value = contact.assigned_to || '';
            notesTextarea.value = contact.admin_notes || '';

            modal.style.display = 'flex';
        }

        function closeEditContactModal() {
            document.getElementById('contactEditModal').style.display = 'none';
        }
    </script>
@endsection

