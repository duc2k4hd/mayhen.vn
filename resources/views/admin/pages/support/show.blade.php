@extends('admin.layouts.master')

@section('title', 'Ticket #' . $ticket->ticket_code)
@section('page_title', 'Ticket: ' . $ticket->ticket_code)

@section('content')
    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mayhen_admin_alert mayhen_admin_alert--success" style="margin-bottom: 16px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mayhen_admin_alert mayhen_admin_alert--danger" style="margin-bottom: 16px;">{{ session('error') }}</div>
    @endif

    {{-- Incident / Escalation Banner (NXT-09) --}}
    @if($ticket->is_incident)
        <div class="mayhen_admin_card" style="margin-bottom: 16px; border-left: 5px solid #C0392B; background: #FFF5F5; padding: 16px 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.3rem;">🔥</span>
                        <strong style="color: #C0392B; font-size: 0.95rem;">
                            {{ \App\Services\IncidentResponseAndEscalationService::getSeverityLabel($ticket->incident_severity) }}
                        </strong>
                    </div>
                    <div style="font-size: 0.8125rem; color: #555; margin-top: 4px;">
                        Khai báo lúc: <strong>{{ $ticket->incident_declared_at ? $ticket->incident_declared_at->format('d/m/Y H:i') : '—' }}</strong> &bull;
                        Người công bố: <strong>{{ $ticket->incidentDeclarer->name ?? 'Hệ thống' }}</strong>
                    </div>
                    @if($ticket->escalation_reason)
                        <div style="font-size: 0.8125rem; color: #7A2E26; margin-top: 4px;">
                            Lý do: <em>{{ $ticket->escalation_reason }}</em>
                        </div>
                    @endif
                </div>

                <div>
                    <span class="mayhen_admin_badge" style="background: #C0392B; color: #fff; font-size: 0.75rem; padding: 6px 12px; font-weight: 700;">
                        WAR ROOM ĐANG MỞ
                    </span>
                </div>
            </div>
        </div>
    @elseif($ticket->isEscalated())
        <div class="mayhen_admin_card" style="margin-bottom: 16px; border-left: 5px solid #D35400; background: #FFF9F0; padding: 14px 18px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.2rem;">⚠️</span>
                <div>
                    <strong style="color: #D35400; font-size: 0.875rem;">
                        TICKET ĐÃ ĐƯỢC LEO THANG CẤP {{ strtoupper($ticket->escalation_level) }}
                    </strong>
                    <div style="font-size: 0.75rem; color: #7A4E1A; margin-top: 2px;">
                        {{ $ticket->escalation_reason ?? 'Vi phạm thời hạn cam kết dịch vụ SLA' }} (Leo thang lúc {{ $ticket->escalated_at ? $ticket->escalated_at->format('d/m/Y H:i') : '—' }})
                    </div>
                </div>
            </div>
        </div>
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

            {{-- Khung Điều Phối Sự Cố & Biên Bản Postmortem (NXT-09) --}}
            @can('admin.manager')
            <div class="mayhen_admin_card" style="margin-bottom: 16px; border: 1px solid {{ $ticket->is_incident ? '#FFCDD2' : 'var(--mh-admin-border)' }}; {{ $ticket->is_incident ? 'background: #FFFBFB;' : '' }}">
                <h3 style="font-size: 0.85rem; font-weight: 700; margin: 0 0 12px; text-transform: uppercase; letter-spacing: 0.05em; color: {{ $ticket->is_incident ? '#C0392B' : 'var(--mh-admin-muted)' }};">
                    {{ $ticket->is_incident ? '🚨 Điều Phối Sự Cố & Postmortem' : '⚡ Điều Phối Khẩn Cấp (NXT-09)' }}
                </h3>

                @if($ticket->is_incident)
                    @if($ticket->status === 'resolved' || $ticket->status === 'closed')
                        {{-- Đã đóng sự cố: Hiển thị biên bản Postmortem đã duyệt --}}
                        <div style="font-size: 0.75rem; color: #2E7D32; font-weight: 700; margin-bottom: 8px;">
                            ✓ Sự cố đã được xử lý khép kín
                        </div>
                        <div style="font-size: 0.75rem; margin-bottom: 8px;">
                            <strong style="color: #4A453E;">1. Nguyên nhân gốc rễ (Root Cause):</strong>
                            <div style="background: #F4F1EC; padding: 6px 8px; border-radius: 4px; margin-top: 2px; white-space: pre-wrap;">{{ $ticket->root_cause }}</div>
                        </div>
                        <div style="font-size: 0.75rem; margin-bottom: 8px;">
                            <strong style="color: #4A453E;">2. Biện pháp khắc phục:</strong>
                            <div style="background: #F4F1EC; padding: 6px 8px; border-radius: 4px; margin-top: 2px; white-space: pre-wrap;">{{ $ticket->mitigation_action }}</div>
                        </div>
                        <div style="font-size: 0.75rem;">
                            <strong style="color: #4A453E;">3. Đúc kết Postmortem:</strong>
                            <div style="background: #F4F1EC; padding: 6px 8px; border-radius: 4px; margin-top: 2px; white-space: pre-wrap;">{{ $ticket->postmortem_notes }}</div>
                        </div>
                    @else
                        {{-- Sự cố đang mở: Biểu mẫu bắt buộc Postmortem để ký duyệt đóng sự cố --}}
                        <form method="POST" action="{{ route('admin.support.resolve_incident', $ticket) }}">
                            @csrf
                            <div style="font-size: 0.75rem; color: #C0392B; margin-bottom: 8px; line-height: 1.4;">
                                Bắt buộc lập biên bản điều tra để đóng sự cố:
                            </div>
                            <div style="margin-bottom: 8px;">
                                <label style="font-size: 0.6875rem; font-weight: 700; display: block; margin-bottom: 2px;">Nguyên nhân gốc (>=10 ký tự) *</label>
                                <textarea name="root_cause" required minlength="10" rows="2" class="mayhen_admin_input" style="width: 100%; font-size: 0.75rem; box-sizing: border-box;" placeholder="Lỗi phát sinh do đâu?"></textarea>
                            </div>
                            <div style="margin-bottom: 8px;">
                                <label style="font-size: 0.6875rem; font-weight: 700; display: block; margin-bottom: 2px;">Biện pháp xử lý (>=10 ký tự) *</label>
                                <textarea name="mitigation_action" required minlength="10" rows="2" class="mayhen_admin_input" style="width: 100%; font-size: 0.75rem; box-sizing: border-box;" placeholder="Đã can thiệp khắc phục như thế nào?"></textarea>
                            </div>
                            <div style="margin-bottom: 10px;">
                                <label style="font-size: 0.6875rem; font-weight: 700; display: block; margin-bottom: 2px;">Đúc kết phòng ngừa (>=10 ký tự) *</label>
                                <textarea name="postmortem_notes" required minlength="10" rows="2" class="mayhen_admin_input" style="width: 100%; font-size: 0.75rem; box-sizing: border-box;" placeholder="Làm sao để không bao giờ tái diễn?"></textarea>
                            </div>
                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn_sm" style="width: 100%;">
                                Ký Duyệt Postmortem &amp; Đóng Sự Cố
                            </button>
                        </form>
                    @endif
                @else
                    {{-- Chưa phải sự cố: Menu khai báo sự cố & leo thang --}}
                    <details style="margin-bottom: 8px;">
                        <summary style="font-size: 0.75rem; font-weight: 600; color: #C0392B; cursor: pointer;">
                            🔥 Khai Báo Sự Cố Vận Hành &dtrif;
                        </summary>
                        <form method="POST" action="{{ route('admin.support.declare_incident', $ticket) }}" style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #eee;">
                            @csrf
                            <select name="severity" style="width: 100%; padding: 4px 6px; font-size: 0.75rem; margin-bottom: 6px; border-radius: 4px; border: 1px solid var(--mh-admin-border);">
                                <option value="sev1">🔴 SEV-1: Critical (Liệt thiệp/Tiệc cưới)</option>
                                <option value="sev2" selected>🟠 SEV-2: Major (Lỗi tính năng chính)</option>
                                <option value="sev3">🟡 SEV-3: Minor (Lỗi nhẹ)</option>
                            </select>
                            <input type="text" name="reason" required minlength="6" placeholder="Lý do công bố sự cố..." style="width: 100%; font-size: 0.75rem; padding: 4px 6px; margin-bottom: 6px; border-radius: 4px; border: 1px solid var(--mh-admin-border); box-sizing: border-box;">
                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_sm" style="width: 100%; background: #C0392B; color: #fff;">
                                Công Bố Sự Cố
                            </button>
                        </form>
                    </details>

                    <details>
                        <summary style="font-size: 0.75rem; font-weight: 600; color: #D35400; cursor: pointer;">
                            ⚠️ Leo Thang Ticket Thủ Công &dtrif;
                        </summary>
                        <form method="POST" action="{{ route('admin.support.escalate', $ticket) }}" style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #eee;">
                            @csrf
                            <select name="level" style="width: 100%; padding: 4px 6px; font-size: 0.75rem; margin-bottom: 6px; border-radius: 4px; border: 1px solid var(--mh-admin-border);">
                                <option value="manager" selected>Quản lý (Manager)</option>
                                <option value="super_admin">Quản trị viên tối cao (Super Admin)</option>
                            </select>
                            <input type="text" name="reason" required minlength="6" placeholder="Lý do leo thang..." style="width: 100%; font-size: 0.75rem; padding: 4px 6px; margin-bottom: 6px; border-radius: 4px; border: 1px solid var(--mh-admin-border); box-sizing: border-box;">
                            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--ghost mayhen_admin_btn_sm" style="width: 100%; border-color: #D35400; color: #D35400;">
                                Chuyển Cấp Xử Lý
                            </button>
                        </form>
                    </details>
                @endif
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

