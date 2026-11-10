@extends('admin.layouts.master')

@section('title', 'Ticket #' . $ticket->ticket_code . ' — Hỗ Trợ')
@section('page_title', 'Ticket: ' . $ticket->ticket_code)

@section('content')
    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mayhen_admin_alert mayhen_admin_alert--success" style="margin-bottom: 16px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mayhen_admin_alert mayhen_admin_alert--danger" style="margin-bottom: 16px;">{{ session('error') }}</div>
    @endif

    <div style="display: grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: start;">

        {{-- Cột trái: thread --}}
        <div>
            {{-- Nội dung ticket gốc --}}
            <div class="mayhen_admin_card" style="margin-bottom: 16px;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 16px;">
                    <div>
                        <h2 style="font-size: 1.1rem; font-weight: 700; margin: 0 0 6px;">{{ $ticket->subject }}</h2>
                        <div style="font-size: 0.8rem; color: var(--mh-admin-muted);">
                            Từ: <strong>{{ $ticket->user->name }}</strong> ({{ $ticket->user->email }}) — {{ $ticket->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <span class="mayhen_admin_badge {{ $ticket->status_badge_class }}">{{ $ticket->status_label }}</span>
                        <span class="mayhen_admin_badge {{ $ticket->priority_badge_class }}">{{ $ticket->priority_label }}</span>
                        <span class="mayhen_admin_badge mayhen_admin_badge--neutral">{{ $ticket->category_label }}</span>
                    </div>
                </div>
                <div style="background: #f9f7f4; border-radius: 8px; padding: 16px; line-height: 1.65; color: #3D3730; white-space: pre-wrap;">{{ $ticket->body }}</div>
            </div>

            {{-- Replies thread --}}
            @foreach($ticket->replies as $reply)
                <div class="mayhen_admin_card" style="margin-bottom: 12px; {{ $reply->is_staff_reply ? 'border-left: 4px solid #8B7355;' : '' }} {{ $reply->is_internal_note ? 'border-left: 4px solid #F39C12; background: #FFF9EB;' : '' }}">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <div style="font-size: 0.8rem; color: var(--mh-admin-muted);">
                            @if($reply->is_internal_note)
                                🔒 <strong>Ghi chú nội bộ</strong> — {{ $reply->author->name ?? '—' }} lúc {{ $reply->created_at->format('d/m/Y H:i') }}
                            @elseif($reply->is_staff_reply)
                                👩‍💼 <strong>{{ $reply->author->name ?? 'Staff' }}</strong> (Staff) — {{ $reply->created_at->format('d/m/Y H:i') }}
                            @else
                                👤 <strong>{{ $reply->author->name ?? 'Khách hàng' }}</strong> — {{ $reply->created_at->format('d/m/Y H:i') }}
                            @endif
                        </div>
                    </div>
                    <div style="line-height: 1.65; color: #3D3730; white-space: pre-wrap;">{{ $reply->body }}</div>
                </div>
            @endforeach

            {{-- Operational Memory / Incident SOP Cards (Innovation #17) --}}
            @if(!empty($sopCards))
                <div class="mayhen_admin_card" style="margin-top: 20px; background: #FFFDF9; border-left: 4px solid var(--mh-admin-gold); padding: 16px 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 1.2rem;">💡</span>
                            <strong style="font-size: 0.875rem; color: var(--mh-admin-charcoal);">Sổ Tay Vận Hành & SOP Cứu Nguy Nhanh (Operational Memory)</strong>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--mh-admin-muted);">Bấm để tự động chèn mẫu câu trả lời chuẩn Quiet Luxury</span>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        @foreach($sopCards as $sop)
                            <button type="button" 
                                    class="mayhen_admin_btn mayhen_admin_btn--ghost mayhen_admin_btn_sm sop-insert-btn"
                                    style="font-size: 0.75rem; border: 1px dashed var(--mh-admin-border); background: #FAF7F2; text-align: left;"
                                    data-template="{{ $sop['reply_template'] }}">
                                📝 {{ $sop['title'] }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Form phản hồi --}}
            @if(!in_array($ticket->status, ['closed']))
                <div class="mayhen_admin_card" style="margin-top: 14px;">
                    <h3 style="font-size: 0.9rem; font-weight: 700; margin: 0 0 14px;">Gửi Phản Hồi</h3>
                    <form method="POST" action="{{ route('admin.support.reply', $ticket) }}">
                        @csrf
                        <textarea id="reply-body-textarea" name="body" rows="6" required minlength="5" maxlength="5000"
                                  placeholder="Nhập nội dung phản hồi hoặc chọn mẫu SOP bên trên..."
                                  style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.875rem; resize: vertical; line-height: 1.6; box-sizing: border-box;">{{ old('body') }}</textarea>
                        @error('body')
                            <div style="color: #C0392B; font-size: 0.8rem; margin-top: 4px;">{{ $message }}</div>
                        @enderror

                        <div style="display: flex; align-items: center; gap: 12px; margin-top: 12px; flex-wrap: wrap;">
                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary">Gửi Phản Hồi</button>

                            <label style="display: flex; align-items: center; gap: 6px; font-size: 0.8rem; cursor: pointer;">
                                <input type="checkbox" name="is_internal_note" value="1">
                                🔒 Ghi chú nội bộ (chỉ staff thấy)
                            </label>
                        </div>
                    </form>
                </div>
            @else
                <div style="padding: 16px; text-align: center; color: var(--mh-admin-muted); font-size: 0.875rem;">
                    Ticket đã đóng. Không thể gửi thêm phản hồi.
                </div>
            @endif
        </div>

        {{-- Cột phải: Thông tin & điều khiển --}}
        <div>
            {{-- Thông tin KPI ticket --}}
            <div class="mayhen_admin_card" style="margin-bottom: 16px;">
                <h3 style="font-size: 0.85rem; font-weight: 700; margin: 0 0 14px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mh-admin-muted);">Thông Tin Ticket</h3>

                <table style="width: 100%; font-size: 0.8rem;">
                    <tr>
                        <td style="padding: 5px 0; color: var(--mh-admin-muted);">Mã ticket:</td>
                        <td style="padding: 5px 0; text-align: right; font-weight: 600;">{{ $ticket->ticket_code }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: var(--mh-admin-muted);">Phản hồi đầu:</td>
                        <td style="padding: 5px 0; text-align: right;">
                            {{ $ticket->first_response_hours ? $ticket->first_response_hours . 'h' : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: var(--mh-admin-muted);">Giải quyết trong:</td>
                        <td style="padding: 5px 0; text-align: right;">
                            {{ $ticket->resolution_hours ? $ticket->resolution_hours . 'h' : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0; color: var(--mh-admin-muted);">Số replies:</td>
                        <td style="padding: 5px 0; text-align: right;">{{ $ticket->replies->count() }}</td>
                    </tr>
                </table>
            </div>

            {{-- Chuyển trạng thái --}}
            @can('admin.manager')
            <div class="mayhen_admin_card" style="margin-bottom: 16px;">
                <h3 style="font-size: 0.85rem; font-weight: 700; margin: 0 0 14px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mh-admin-muted);">Cập Nhật Trạng Thái</h3>
                <form method="POST" action="{{ route('admin.support.update_status', $ticket) }}">
                    @csrf
                    <select name="status" style="width: 100%; padding: 8px 10px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.875rem; margin-bottom: 10px;">
                        @foreach(['open' => 'Mới', 'in_progress' => 'Đang xử lý', 'resolved' => 'Đã giải quyết', 'closed' => 'Đóng'] as $val => $label)
                            <option value="{{ $val }}"
                                {{ $ticket->status === $val ? 'selected' : '' }}
                                {{ !$ticket->canTransitionTo($val) && $ticket->status !== $val ? 'disabled' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="width: 100%;">Cập Nhật</button>
                </form>
            </div>
            @endcan

            {{-- Giao việc --}}
            @can('admin.manager')
            <div class="mayhen_admin_card" style="margin-bottom: 16px;">
                <h3 style="font-size: 0.85rem; font-weight: 700; margin: 0 0 14px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mh-admin-muted);">Giao Cho Staff</h3>
                <form method="POST" action="{{ route('admin.support.assign', $ticket) }}">
                    @csrf
                    <select name="assigned_to" style="width: 100%; padding: 8px 10px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.875rem; margin-bottom: 10px;">
                        <option value="">— Chưa giao —</option>
                        @foreach($staffList as $staff)
                            <option value="{{ $staff->id }}" {{ $ticket->assigned_to == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--ghost" style="width: 100%;">Lưu Giao Việc</button>
                </form>
            </div>
            @endcan

            {{-- Thông tin khách hàng --}}
            <div class="mayhen_admin_card">
                <h3 style="font-size: 0.85rem; font-weight: 700; margin: 0 0 14px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mh-admin-muted);">Thông Tin Khách Hàng</h3>
                <div style="font-size: 0.8rem;">
                    <div style="margin-bottom: 6px;"><strong>{{ $ticket->user->name ?? '—' }}</strong></div>
                    <div style="color: var(--mh-admin-muted); margin-bottom: 6px;">{{ $ticket->user->email ?? '—' }}</div>
                    <div style="margin-top: 10px;">
                        <a href="{{ route('admin.users.show', $ticket->user_id) }}"
                           class="mayhen_admin_btn mayhen_admin_btn--ghost"
                           style="padding: 6px 12px; font-size: 0.75rem;">Xem Hồ Sơ</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var textarea = document.getElementById('reply-body-textarea');
                if (!textarea) return;

                document.querySelectorAll('.sop-insert-btn').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var templateText = this.getAttribute('data-template') || '';
                        if (!textarea.value.trim()) {
                            textarea.value = templateText;
                        } else {
                            textarea.value += "\n\n" + templateText;
                        }
                        textarea.focus();
                    });
                });
            });
        </script>
    @endpush
@endsection

