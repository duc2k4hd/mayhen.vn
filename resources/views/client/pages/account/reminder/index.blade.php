@extends('client.pages.account.layout')

@php
    $pageTitle = 'Trợ Lý Nhắc Hẹn RSVP — Mây Hẹn';
    $coupleName = ($wedding->groom_name ?? 'Chú rể') . ' & ' . ($wedding->bride_name ?? 'Cô dâu');
@endphp

@section('account_breadcrumb', 'Trợ Lý Nhắc Hẹn RSVP')

@section('account_content')
<div class="mayhen_reminder_container">
    {{-- Header & Headline --}}
    <div class="mayhen_reminder_header">
        <div class="mayhen_reminder_headline">
            <div class="mayhen_reminder_badge">Smart RSVP Follow-Up &amp; Gentle Reminder</div>
            <h1 class="mayhen_reminder_title">Trợ Lý Nhắc Hẹn RSVP Tế Nhị</h1>
            <p class="mayhen_reminder_subtitle">
                Tự động rà soát quan khách chưa phản hồi, gợi ý lời nhắn lịch thiệp theo văn hóa cưới Việt và gửi nhắc hẹn 1-chạm qua Zalo cho ngày vui của <strong>{{ $coupleName }}</strong>.
            </p>
        </div>
        <div class="mayhen_reminder_actions">
            <a href="{{ route('account.reminder.export_csv', $invitation->id) }}" class="mayhen_reminder_btn mayhen_reminder_btn--outline">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Xuất Danh Sách (CSV)
            </a>
            <button type="button" class="mayhen_reminder_btn mayhen_reminder_btn--primary" id="btnBatchRecord">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Đánh Dấu Đã Nhắc Hàng Loạt
            </button>
        </div>
    </div>

    {{-- Thống Kê 4 Khối Chỉ Số (Counters) --}}
    <div class="mayhen_reminder_stat_grid">
        <div class="mayhen_reminder_stat_card {{ $selectedStatus === 'unresponsive' ? 'is-active' : '' }}">
            <a href="{{ route('account.reminder.index', ['id' => $invitation->id, 'status' => 'unresponsive', 'group' => $selectedGroup]) }}" class="mayhen_reminder_stat_link">
                <div class="mayhen_reminder_stat_num" style="color: #D97706;">{{ $stats['unresponsive_count'] }}</div>
                <div class="mayhen_reminder_stat_label">Chưa Phản Hồi RSVP</div>
                <div class="mayhen_reminder_stat_desc">Đã gửi thiệp nhưng chưa chốt đi hay không</div>
            </a>
        </div>
        <div class="mayhen_reminder_stat_card {{ $selectedStatus === 'unsent' ? 'is-active' : '' }}">
            <a href="{{ route('account.reminder.index', ['id' => $invitation->id, 'status' => 'unsent', 'group' => $selectedGroup]) }}" class="mayhen_reminder_stat_link">
                <div class="mayhen_reminder_stat_num" style="color: #6B7280;">{{ $stats['unsent_count'] }}</div>
                <div class="mayhen_reminder_stat_label">Chưa Từng Gửi Thiệp</div>
                <div class="mayhen_reminder_stat_desc">Khách mới thêm vào danh bạ</div>
            </a>
        </div>
        <div class="mayhen_reminder_stat_card {{ $selectedStatus === 'attending_unseated' ? 'is-active' : '' }}">
            <a href="{{ route('account.reminder.index', ['id' => $invitation->id, 'status' => 'attending_unseated', 'group' => $selectedGroup]) }}" class="mayhen_reminder_stat_link">
                <div class="mayhen_reminder_stat_num" style="color: #2563EB;">{{ $stats['attending_unseated_count'] }}</div>
                <div class="mayhen_reminder_stat_label">Đã RSVP Chưa Gán Bàn</div>
                <div class="mayhen_reminder_stat_desc">Đã báo có đi, cần xếp vị trí ngồi</div>
            </a>
        </div>
        <div class="mayhen_reminder_stat_card">
            <div class="mayhen_reminder_stat_num" style="color: #059669;">{{ $stats['reminded_total'] }} / {{ $stats['total_guests'] }}</div>
            <div class="mayhen_reminder_stat_label">Đã Được Nhắc Hẹn</div>
            <div class="mayhen_reminder_stat_desc">Quan khách đã nhận tin nhắn nhắc</div>
        </div>
    </div>

    {{-- Banner Khung Giờ Vàng Gửi Tin Nhắn Tế Nhị --}}
    <div class="mayhen_reminder_advisory_box">
        <div class="mayhen_reminder_advisory_head">
            <span class="mayhen_reminder_advisory_icon">⏰</span>
            <div>
                <strong class="mayhen_reminder_advisory_title">{{ $recommendedTime['label'] }}</strong>
                <p class="mayhen_reminder_advisory_sub">{{ $recommendedTime['tip'] }}</p>
            </div>
        </div>
        <div class="mayhen_reminder_time_chips">
            @foreach($recommendedTime['time_windows'] as $window)
                <div class="mayhen_reminder_time_chip">✦ {{ $window }}</div>
            @endforeach
        </div>
    </div>

    {{-- Thẻ Mẫu Lời Nhắn Tế Nhị (Preset Switcher) --}}
    <div class="mayhen_reminder_card">
        <div class="mayhen_reminder_card_header">
            <div class="mayhen_reminder_card_icon">✍️</div>
            <div>
                <h2 class="mayhen_reminder_card_title">Bộ Mẫu Lời Nhắn Nhắc Hẹn Lễ Nghi Văn Hóa Cưới Việt</h2>
                <p class="mayhen_reminder_card_desc">Chọn giọng văn phù hợp cho từng vị khách. Hệ thống tự động thay thế [Tên khách], [Xưng hô], [Ngày cưới] và [Đường link cá nhân].</p>
            </div>
        </div>
        <div class="mayhen_reminder_card_body">
            <div class="mayhen_reminder_presets_grid">
                @foreach($presets as $key => $preset)
                    <div class="mayhen_reminder_preset_card {{ $key === 'intimate' ? 'is-selected' : '' }}" data-preset-key="{{ $key }}">
                        <div class="mayhen_reminder_preset_target">{{ $preset['target'] }}</div>
                        <div class="mayhen_reminder_preset_name">{{ $preset['name'] }}</div>
                        <div class="mayhen_reminder_preset_desc">{{ $preset['desc'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Bảng Quản Trị Danh Sách Khách & Gửi Nhắc Hẹn --}}
    <div class="mayhen_reminder_card" style="margin-top: 1.5rem;">
        <div class="mayhen_reminder_card_header" style="justify-content: space-between; flex-wrap: wrap;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="mayhen_reminder_card_icon">📋</div>
                <div>
                    <h2 class="mayhen_reminder_card_title">Danh Sách Cần Nhắc Hẹn ({{ count($reminders) }})</h2>
                    <p class="mayhen_reminder_card_desc">Lọc theo nhóm khách mời và trạng thái để gửi nhắc hẹn chính xác.</p>
                </div>
            </div>

            {{-- Bộ Lọc Nhóm & Trạng Thái --}}
            <div class="mayhen_reminder_filters">
                <form action="{{ route('account.reminder.index', $invitation->id) }}" method="GET" class="mayhen_reminder_filter_form">
                    <input type="hidden" name="status" value="{{ $selectedStatus }}">
                    <label class="mayhen_reminder_filter_label">Nhóm khách:</label>
                    <select name="group" class="mayhen_reminder_select" onchange="this.form.submit()">
                        <option value="all" {{ $selectedGroup === 'all' ? 'selected' : '' }}>Tất cả nhóm</option>
                        @foreach($groups as $grp)
                            <option value="{{ $grp }}" {{ $selectedGroup === $grp ? 'selected' : '' }}>{{ $grp }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="mayhen_reminder_tabs">
            <a href="{{ route('account.reminder.index', ['id' => $invitation->id, 'status' => 'all', 'group' => $selectedGroup]) }}" class="mayhen_reminder_tab {{ $selectedStatus === 'all' ? 'is-active' : '' }}">
                Tất cả ({{ $stats['total_guests'] }})
            </a>
            <a href="{{ route('account.reminder.index', ['id' => $invitation->id, 'status' => 'unresponsive', 'group' => $selectedGroup]) }}" class="mayhen_reminder_tab {{ $selectedStatus === 'unresponsive' ? 'is-active' : '' }}">
                Chưa phản hồi ({{ $stats['unresponsive_count'] }})
            </a>
            <a href="{{ route('account.reminder.index', ['id' => $invitation->id, 'status' => 'unsent', 'group' => $selectedGroup]) }}" class="mayhen_reminder_tab {{ $selectedStatus === 'unsent' ? 'is-active' : '' }}">
                Chưa gửi thiệp ({{ $stats['unsent_count'] }})
            </a>
            <a href="{{ route('account.reminder.index', ['id' => $invitation->id, 'status' => 'attending_unseated', 'group' => $selectedGroup]) }}" class="mayhen_reminder_tab {{ $selectedStatus === 'attending_unseated' ? 'is-active' : '' }}">
                Chưa xếp bàn ({{ $stats['attending_unseated_count'] }})
            </a>
        </div>

        <div class="mayhen_reminder_card_body" style="padding: 0;">
            <div class="mayhen_reminder_table_wrap">
                <table class="mayhen_reminder_table">
                    <thead>
                        <tr>
                            <th width="40"><input type="checkbox" id="checkAllGuests"></th>
                            <th>Khách Mời</th>
                            <th>Nhóm Khách</th>
                            <th>Số Điện Thoại</th>
                            <th>Trạng Thái</th>
                            <th>Số Lần Nhắc</th>
                            <th>Lần Nhắc Gần Nhất</th>
                            <th style="text-align: right;">Hành Động 1-Chạm</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reminders as $item)
                            <tr id="row-guest-{{ $item['guest_id'] }}">
                                <td>
                                    <input type="checkbox" class="guest-checkbox" value="{{ $item['guest_id'] }}">
                                </td>
                                <td>
                                    <strong>{{ $item['salutation'] }} {{ $item['guest_name'] }}</strong>
                                </td>
                                <td>
                                    <span class="mayhen_reminder_group_tag">{{ $item['group_name'] }}</span>
                                </td>
                                <td style="font-family: monospace;">
                                    {{ $item['phone'] ?? '—' }}
                                </td>
                                <td>
                                    @if($item['status_type'] === 'unresponsive')
                                        <span class="mayhen_reminder_badge mayhen_reminder_badge--warning">Chưa RSVP</span>
                                    @elseif($item['status_type'] === 'unsent')
                                        <span class="mayhen_reminder_badge mayhen_reminder_badge--gray">Chưa gửi thiệp</span>
                                    @elseif($item['status_type'] === 'attending_unseated')
                                        <span class="mayhen_reminder_badge mayhen_reminder_badge--blue">Chưa xếp bàn</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="mayhen_reminder_count_pill" id="count-guest-{{ $item['guest_id'] }}">
                                        {{ $item['reminder_count'] > 0 ? $item['reminder_count'] . ' lần' : 'Chưa' }}
                                    </span>
                                </td>
                                <td style="font-size: 0.8rem; color: #777;" id="time-guest-{{ $item['guest_id'] }}">
                                    {{ $item['reminded_at'] ?? '—' }}
                                </td>
                                <td style="text-align: right;">
                                    <div class="mayhen_reminder_row_actions">
                                        {{-- Nút Xem Trước & Tùy Biến Lời Nhắn --}}
                                        <button type="button" class="mayhen_reminder_action_btn btn-preview-msg" 
                                            data-guest-id="{{ $item['guest_id'] }}"
                                            data-guest-name="{{ $item['guest_name'] }}"
                                            title="Xem trước & sửa lời nhắn">
                                            👁️
                                        </button>

                                        {{-- Nút Mở Zalo nếu có SĐT --}}
                                        @if(!empty($item['zalo_link']))
                                            <a href="{{ $item['zalo_link'] }}" target="_blank" class="mayhen_reminder_action_btn btn-zalo"
                                                data-guest-id="{{ $item['guest_id'] }}"
                                                title="Mở Zalo nhắn tin cho khách">
                                                💬 Zalo
                                            </a>
                                        @endif

                                        {{-- Nút Sao Chép Tin Nhắn Kèm Link --}}
                                        <button type="button" class="mayhen_reminder_action_btn btn-copy-msg" 
                                            data-guest-id="{{ $item['guest_id'] }}"
                                            data-msg="{{ $item['suggested_message'] }}"
                                            title="Sao chép lời nhắn kèm link riêng">
                                            📋 Chép
                                        </button>

                                        {{-- Nút Đánh Dấu Đã Nhắc --}}
                                        <button type="button" class="mayhen_reminder_action_btn btn-record-reminder" 
                                            data-guest-id="{{ $item['guest_id'] }}"
                                            title="Đánh dấu đã nhắc vị khách này">
                                            ✓
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="mayhen_reminder_empty">
                                    Tuyệt vời! Không có khách mời nào cần nhắc hẹn trong nhóm này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL XEM TRƯỚC VÀ CHỈNH SỬA TIN NHẮN TRƯỚC KHI GỬI --}}
<div class="mayhen_reminder_modal_overlay" id="previewModal" style="display: none;">
    <div class="mayhen_reminder_modal">
        <div class="mayhen_reminder_modal_head">
            <h3 id="modalTitle">Soạn Lời Nhắn Nhắc Hẹn Cho Quan Khách</h3>
            <button type="button" class="mayhen_reminder_modal_close" id="closePreviewModal">&times;</button>
        </div>
        <div class="mayhen_reminder_modal_body">
            <div style="margin-bottom: 1rem;">
                <label class="mayhen_reminder_label">Đổi giọng văn mẫu:</label>
                <div class="mayhen_reminder_modal_preset_buttons">
                    <button type="button" class="modal-preset-btn" data-key="respectful">Trân Trọng (Người lớn)</button>
                    <button type="button" class="modal-preset-btn is-active" data-key="intimate">Thân Mật (Bạn bè)</button>
                    <button type="button" class="modal-preset-btn" data-key="polite">Lịch Thiệp (Đồng nghiệp)</button>
                    <button type="button" class="modal-preset-btn" data-key="urgent_deadline">Gấp (Chốt cỗ)</button>
                </div>
            </div>

            <div style="margin-bottom: 1rem;">
                <label class="mayhen_reminder_label">Nội dung tin nhắn hoàn chỉnh:</label>
                <textarea id="modalMessageTextarea" class="mayhen_reminder_textarea" rows="5"></textarea>
            </div>

            <div class="mayhen_reminder_modal_hint">
                💡 <em>Mẹo:</em> Bạn có thể nhấn <strong>"Sao Chép &amp; Đánh Dấu"</strong> để hệ thống tự động copy tin nhắn vào clipboard và tăng số lần đã nhắc của khách.
            </div>
        </div>
        <div class="mayhen_reminder_modal_foot">
            <button type="button" class="mayhen_reminder_btn mayhen_reminder_btn--outline" id="cancelModal">Đóng</button>
            <button type="button" class="mayhen_reminder_btn mayhen_reminder_btn--primary" id="copyAndRecordModal">
                📋 Sao Chép &amp; Đánh Dấu Đã Nhắc
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const invitationId = '{{ $invitation->id }}';
    const csrfToken = '{{ csrf_token() }}';
    let currentSelectedPreset = 'intimate';

    // 1. Chuyển đổi Preset Mẫu
    const presetCards = document.querySelectorAll('.mayhen_reminder_preset_card');
    presetCards.forEach(card => {
        card.addEventListener('click', function() {
            presetCards.forEach(c => c.classList.remove('is-selected'));
            this.classList.add('is-selected');
            currentSelectedPreset = this.getAttribute('data-preset-key');
        });
    });

    // 2. Checkbox Chọn Tất Cả
    const checkAll = document.getElementById('checkAllGuests');
    const guestCheckboxes = document.querySelectorAll('.guest-checkbox');
    if (checkAll) {
        checkAll.addEventListener('change', function() {
            guestCheckboxes.forEach(cb => cb.checked = this.checked);
        });
    }

    // 3. Đánh Dấu Đã Nhắc Cho 1 Khách
    document.querySelectorAll('.btn-record-reminder').forEach(btn => {
        btn.addEventListener('click', function() {
            const guestId = this.getAttribute('data-guest-id');
            recordSingleReminder(guestId);
        });
    });

    function recordSingleReminder(guestId) {
        const url = `/tai-khoan/thiep/${invitationId}/nhac-hen/ghi-nhan/${guestId}`;
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ channel: 'zalo' })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                const countEl = document.getElementById(`count-guest-${guestId}`);
                const timeEl = document.getElementById(`time-guest-${guestId}`);
                if (countEl) countEl.textContent = `${res.reminder_count} lần`;
                if (timeEl) timeEl.textContent = res.reminded_at || 'Vừa xong';
                alert(res.message);
            }
        })
        .catch(() => alert('Có lỗi kết nối.'));
    }

    // 4. Sao Chép Tin Nhắn Kèm Link
    document.querySelectorAll('.btn-copy-msg').forEach(btn => {
        btn.addEventListener('click', function() {
            const guestId = this.getAttribute('data-guest-id');
            const msg = this.getAttribute('data-msg');
            navigator.clipboard.writeText(msg).then(() => {
                const originalText = this.textContent;
                this.textContent = 'Đã chép!';
                setTimeout(() => this.textContent = originalText, 2000);

                if (confirm('Đã sao chép tin nhắn! Bạn có muốn đánh dấu là ĐÃ NHẮC vị khách này luôn không?')) {
                    recordSingleReminder(guestId);
                }
            });
        });
    });

    // 5. Đánh Dấu Hàng Loạt
    const btnBatch = document.getElementById('btnBatchRecord');
    if (btnBatch) {
        btnBatch.addEventListener('click', function() {
            const selectedIds = Array.from(document.querySelectorAll('.guest-checkbox:checked')).map(cb => cb.value);
            if (selectedIds.length === 0) {
                alert('Vui lòng chọn ít nhất 1 khách mời để đánh dấu nhắc hẹn hàng loạt.');
                return;
            }

            if (!confirm(`Bạn có chắc muốn đánh dấu đã nhắc hẹn cho ${selectedIds.length} khách mời đã chọn?`)) {
                return;
            }

            const url = `/tai-khoan/thiep/${invitationId}/nhac-hen/ghi-nhan-hang-loat`;
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ guest_ids: selectedIds, channel: 'zalo' })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    alert(res.message);
                    window.location.reload();
                }
            })
            .catch(() => alert('Không thể kết nối máy chủ.'));
        });
    }

    // 6. Modal Xem Trước & Tùy Biến
    const previewModal = document.getElementById('previewModal');
    const modalTextarea = document.getElementById('modalMessageTextarea');
    const modalTitle = document.getElementById('modalTitle');
    let currentGuestIdInModal = null;

    document.querySelectorAll('.btn-preview-msg').forEach(btn => {
        btn.addEventListener('click', function() {
            currentGuestIdInModal = this.getAttribute('data-guest-id');
            const guestName = this.getAttribute('data-guest-name');
            modalTitle.textContent = `Lời Nhắn Nhắc Hẹn: ${guestName}`;
            loadModalMessage(currentGuestIdInModal, currentSelectedPreset);
            previewModal.style.display = 'flex';
        });
    });

    function loadModalMessage(guestId, presetKey) {
        modalTextarea.value = 'Đang tải nội dung tin nhắn...';
        const url = `/tai-khoan/thiep/${invitationId}/nhac-hen/xem-truoc/${guestId}?preset=${presetKey}`;
        fetch(url, {
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                modalTextarea.value = data.message;
            }
        });
    }

    document.querySelectorAll('.modal-preset-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.modal-preset-btn').forEach(b => b.classList.remove('is-active'));
            this.classList.add('is-active');
            const key = this.getAttribute('data-key');
            if (currentGuestIdInModal) {
                loadModalMessage(currentGuestIdInModal, key);
            }
        });
    });

    function closeModal() {
        previewModal.style.display = 'none';
        currentGuestIdInModal = null;
    }

    document.getElementById('closePreviewModal')?.addEventListener('click', closeModal);
    document.getElementById('cancelModal')?.addEventListener('click', closeModal);

    document.getElementById('copyAndRecordModal')?.addEventListener('click', function() {
        const text = modalTextarea.value;
        navigator.clipboard.writeText(text).then(() => {
            if (currentGuestIdInModal) {
                recordSingleReminder(currentGuestIdInModal);
            }
            closeModal();
        });
    });
});
</script>

<style>
/* Pure CSS Quiet Luxury cho Trợ Lý Nhắc Hẹn RSVP */
.mayhen_reminder_container {
    max-width: 1040px;
    margin: 0 auto;
    padding: 1.5rem 0 5rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #2C2C2C;
}

.mayhen_reminder_header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.mayhen_reminder_badge {
    display: inline-block;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #C5A880;
    font-weight: 600;
    margin-bottom: 0.35rem;
}

.mayhen_reminder_title {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 2rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
    color: #1A1A1A;
}

.mayhen_reminder_subtitle {
    font-size: 0.95rem;
    color: #666;
    margin: 0;
    max-width: 680px;
    line-height: 1.5;
}

.mayhen_reminder_actions {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.mayhen_reminder_btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.1rem;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    font-family: inherit;
    border: none;
    transition: all 0.2s ease;
}

.mayhen_reminder_btn--outline {
    background: #FFFFFF;
    color: #2C2C2C;
    border: 1px solid #DDD8D0;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}

.mayhen_reminder_btn--outline:hover {
    background: #F8F6F2;
    border-color: #C5A880;
    color: #8C6D45;
}

.mayhen_reminder_btn--primary {
    background: #2C2C2C;
    color: #FFFFFF;
}

.mayhen_reminder_btn--primary:hover {
    background: #111111;
}

/* Stat Grid */
.mayhen_reminder_stat_grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
    margin-bottom: 1.8rem;
}

.mayhen_reminder_stat_card {
    background: #FFFFFF;
    border: 1px solid #ECE7DE;
    border-radius: 8px;
    padding: 1.25rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    transition: all 0.2s;
}

.mayhen_reminder_stat_card.is-active {
    border-color: #C5A880;
    background: #FFFDF9;
    box-shadow: 0 4px 12px rgba(197, 168, 128, 0.15);
}

.mayhen_reminder_stat_link {
    text-decoration: none;
    color: inherit;
    display: block;
}

.mayhen_reminder_stat_num {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.mayhen_reminder_stat_label {
    font-size: 0.85rem;
    font-weight: 600;
    color: #2C2C2C;
    margin-bottom: 0.2rem;
}

.mayhen_reminder_stat_desc {
    font-size: 0.75rem;
    color: #888;
}

/* Advisory Box */
.mayhen_reminder_advisory_box {
    background: #F9F7F3;
    border: 1px solid #EAE3D7;
    border-left: 3px solid #C5A880;
    border-radius: 8px;
    padding: 1.2rem 1.5rem;
    margin-bottom: 1.8rem;
}

.mayhen_reminder_advisory_head {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-bottom: 0.75rem;
}

.mayhen_reminder_advisory_icon {
    font-size: 1.3rem;
    line-height: 1;
}

.mayhen_reminder_advisory_title {
    font-size: 0.95rem;
    color: #2C2C2C;
}

.mayhen_reminder_advisory_sub {
    font-size: 0.85rem;
    color: #666;
    margin: 0.2rem 0 0;
}

.mayhen_reminder_time_chips {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.mayhen_reminder_time_chip {
    padding: 0.35rem 0.85rem;
    background: #FFF;
    border: 1px solid #E2D9CC;
    border-radius: 20px;
    font-size: 0.8rem;
    color: #6B5234;
    font-weight: 500;
}

/* Card */
.mayhen_reminder_card {
    background: #FFFFFF;
    border: 1px solid #ECE7DE;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}

.mayhen_reminder_card_header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.5rem 1.8rem;
    border-bottom: 1px solid #F3EFE8;
}

.mayhen_reminder_card_icon {
    font-size: 1.6rem;
    line-height: 1;
}

.mayhen_reminder_card_title {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0 0 0.25rem;
    color: #2C2C2C;
}

.mayhen_reminder_card_desc {
    font-size: 0.85rem;
    color: #777;
    margin: 0;
}

.mayhen_reminder_card_body {
    padding: 1.8rem;
}

/* Presets Grid */
.mayhen_reminder_presets_grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
}

.mayhen_reminder_preset_card {
    border: 1.5px solid #E5E0D6;
    border-radius: 8px;
    padding: 1.1rem;
    background: #FCFBF9;
    cursor: pointer;
    transition: all 0.2s;
}

.mayhen_reminder_preset_card.is-selected {
    border-color: #C5A880;
    background: #FFFDF9;
    box-shadow: 0 4px 12px rgba(197, 168, 128, 0.15);
}

.mayhen_reminder_preset_target {
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #8C6D45;
    background: #F4EFE6;
    display: inline-block;
    padding: 0.15rem 0.5rem;
    border-radius: 4px;
    font-weight: 600;
    margin-bottom: 0.4rem;
}

.mayhen_reminder_preset_name {
    font-weight: 600;
    font-size: 0.9rem;
    color: #2C2C2C;
    margin-bottom: 0.25rem;
}

.mayhen_reminder_preset_desc {
    font-size: 0.8rem;
    color: #777;
    line-height: 1.4;
}

/* Tabs & Filters */
.mayhen_reminder_tabs {
    display: flex;
    border-bottom: 1px solid #ECE7DE;
    background: #FBF9F6;
    overflow-x: auto;
}

.mayhen_reminder_tab {
    padding: 0.85rem 1.4rem;
    font-size: 0.85rem;
    font-weight: 500;
    color: #666;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    white-space: nowrap;
    transition: all 0.15s;
}

.mayhen_reminder_tab.is-active {
    color: #2C2C2C;
    font-weight: 600;
    border-bottom-color: #C5A880;
    background: #FFFFFF;
}

.mayhen_reminder_select {
    padding: 0.4rem 0.8rem;
    border: 1px solid #DDD8D0;
    border-radius: 6px;
    font-size: 0.85rem;
    font-family: inherit;
    background: #FFF;
}

/* Table */
.mayhen_reminder_table_wrap {
    overflow-x: auto;
}

.mayhen_reminder_table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.mayhen_reminder_table th {
    background: #FBF9F6;
    padding: 0.85rem 1rem;
    text-align: left;
    font-weight: 600;
    color: #444;
    border-bottom: 1px solid #ECE7DE;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.mayhen_reminder_table td {
    padding: 0.9rem 1rem;
    border-bottom: 1px solid #F3EFE8;
    vertical-align: middle;
}

.mayhen_reminder_group_tag {
    font-size: 0.75rem;
    background: #F3EFE8;
    color: #665239;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
}

.mayhen_reminder_badge {
    display: inline-block;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.mayhen_reminder_badge--warning {
    background: #FEF3C7;
    color: #92400E;
}

.mayhen_reminder_badge--gray {
    background: #F3F4F6;
    color: #4B5563;
}

.mayhen_reminder_badge--blue {
    background: #DBEAFE;
    color: #1E40AF;
}

.mayhen_reminder_count_pill {
    font-size: 0.8rem;
    font-weight: 600;
    color: #059669;
}

.mayhen_reminder_row_actions {
    display: flex;
    gap: 0.35rem;
    justify-content: flex-end;
}

.mayhen_reminder_action_btn {
    padding: 0.35rem 0.65rem;
    border-radius: 4px;
    border: 1px solid #E2DDD5;
    background: #FFF;
    cursor: pointer;
    font-size: 0.8rem;
    font-family: inherit;
    text-decoration: none;
    color: #333;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    transition: all 0.15s;
}

.mayhen_reminder_action_btn:hover {
    background: #F4EFE6;
    border-color: #C5A880;
    color: #8C6D45;
}

.mayhen_reminder_action_btn.btn-zalo {
    color: #0068FF;
    border-color: #B2D7FF;
}

.mayhen_reminder_action_btn.btn-zalo:hover {
    background: #EBF4FF;
}

.mayhen_reminder_empty {
    text-align: center;
    padding: 2.5rem;
    color: #999;
    font-style: italic;
}

/* Modal */
.mayhen_reminder_modal_overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.mayhen_reminder_modal {
    background: #FFF;
    border-radius: 12px;
    width: 100%;
    max-width: 550px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    overflow: hidden;
}

.mayhen_reminder_modal_head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.2rem 1.5rem;
    border-bottom: 1px solid #ECE7DE;
}

.mayhen_reminder_modal_head h3 {
    margin: 0;
    font-family: "Playfair Display", Georgia, serif;
    font-size: 1.15rem;
    color: #2C2C2C;
}

.mayhen_reminder_modal_close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #999;
}

.mayhen_reminder_modal_body {
    padding: 1.5rem;
}

.mayhen_reminder_label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.35rem;
}

.mayhen_reminder_modal_preset_buttons {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
}

.modal-preset-btn {
    padding: 0.3rem 0.65rem;
    border: 1px solid #DDD8D0;
    border-radius: 4px;
    background: #FBF9F6;
    font-size: 0.75rem;
    cursor: pointer;
    font-family: inherit;
}

.modal-preset-btn.is-active {
    background: #C5A880;
    border-color: #C5A880;
    color: #FFF;
    font-weight: 500;
}

.mayhen_reminder_textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #DDD8D0;
    border-radius: 6px;
    font-size: 0.9rem;
    font-family: inherit;
    line-height: 1.5;
    background: #FCFBF9;
    box-sizing: border-box;
}

.mayhen_reminder_textarea:focus {
    outline: none;
    border-color: #C5A880;
    background: #FFF;
}

.mayhen_reminder_modal_hint {
    font-size: 0.8rem;
    color: #666;
    background: #FAF8F5;
    padding: 0.6rem 0.8rem;
    border-radius: 4px;
}

.mayhen_reminder_modal_foot {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #ECE7DE;
    background: #FAF8F5;
}
</style>
@endsection

