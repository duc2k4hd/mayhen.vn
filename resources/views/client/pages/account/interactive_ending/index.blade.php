@extends('client.pages.account.layout')

@php
    $pageTitle = 'Sổ Ký Tên & Lời Cảm Ơn — Mây Hẹn';
    $wedding = $invitation->wedding;
    $coupleName = ($wedding->groom_name ?? 'Chú rể') . ' & ' . ($wedding->bride_name ?? 'Cô dâu');
@endphp

@section('account_breadcrumb', 'Sổ Ký Tên & Lời Cảm Ơn')

@section('account_content')
<div class="mayhen_ending_admin_container">
    {{-- Header & Headline --}}
    <div class="mayhen_ending_admin_header">
        <div class="mayhen_ending_admin_headline">
            <div class="mayhen_ending_admin_badge">Digital Guestbook &amp; Ending Suite</div>
            <h1 class="mayhen_ending_admin_title">Sổ Ký Tên, Lưu Bút &amp; Lời Cảm Ơn</h1>
            <p class="mayhen_ending_admin_subtitle">
                Cấu hình bảng ký tên nghệ thuật cảm ứng, sổ lưu bút chúc phúc và lời cảm ơn tri ân khép lại thiệp cưới trang trọng cho ngày trọng đại của <strong>{{ $coupleName }}</strong>.
            </p>
        </div>
        <div class="mayhen_ending_admin_actions">
            <a href="{{ route('account.interactive_ending.export_wishes', $invitation->id) }}" class="mayhen_ending_export_btn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Xuất Lời Chúc (CSV)
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mayhen_ending_alert mayhen_ending_alert--success">
            <span class="mayhen_ending_alert_icon">✓</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mayhen_ending_alert mayhen_ending_alert--danger">
            <span class="mayhen_ending_alert_icon">!</span>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Thống Kê Nhanh (Counters) --}}
    <div class="mayhen_ending_stat_grid">
        <div class="mayhen_ending_stat_card">
            <div class="mayhen_ending_stat_num">{{ $stats['total'] }}</div>
            <div class="mayhen_ending_stat_label">Tổng Lời Chúc</div>
        </div>
        <div class="mayhen_ending_stat_card">
            <div class="mayhen_ending_stat_num">{{ $stats['approved'] }}</div>
            <div class="mayhen_ending_stat_label">Đã Phê Duyệt</div>
        </div>
        <div class="mayhen_ending_stat_card">
            <div class="mayhen_ending_stat_num">{{ $stats['with_signature'] }}</div>
            <div class="mayhen_ending_stat_label">Có Chữ Ký Điện Tử</div>
        </div>
        <div class="mayhen_ending_stat_card">
            <div class="mayhen_ending_stat_num">{{ $stats['private'] }}</div>
            <div class="mayhen_ending_stat_label">Lời Chúc Kín Đáo</div>
        </div>
    </div>

    <form action="{{ route('account.interactive_ending.update', $invitation->id) }}" method="POST" id="interactiveEndingForm" class="mayhen_ending_form">
        @csrf

        {{-- Section 1: Phong Cách Hiển Thị & Lời Tri Ân Kết Thiệp --}}
        <div class="mayhen_ending_card">
            <div class="mayhen_ending_card_header">
                <div class="mayhen_ending_card_icon">💌</div>
                <div class="mayhen_ending_card_title_row">
                    <div>
                        <h2 class="mayhen_ending_card_title">1. Phong Cách &amp; Lời Cảm Ơn Kết Thiệp</h2>
                        <p class="mayhen_ending_card_desc">Chọn kiểu dáng khép lại thiệp cưới và soạn lời tri ân chân thành gửi tới toàn thể quan khách.</p>
                    </div>
                    <label class="mayhen_ending_switch">
                        <input type="checkbox" name="enabled" value="1" {{ !empty($config['enabled']) ? 'checked' : '' }}>
                        <span class="mayhen_ending_slider"></span>
                        <span class="mayhen_ending_switch_text">Kích hoạt Section 10</span>
                    </label>
                </div>
            </div>

            <div class="mayhen_ending_card_body">
                {{-- Bộ Chọn 3 Phong Cách Kết Thiệp --}}
                <div class="mayhen_ending_form_group">
                    <label class="mayhen_ending_label">Phong Cách Trình Diễn Kết Thiệp <span class="required">*</span></label>
                    <div class="mayhen_ending_style_grid">
                        @foreach($endingStyles as $key => $style)
                            <label class="mayhen_ending_style_card {{ ($config['ending_style'] ?? 'intimate_editorial') === $key ? 'is-selected' : '' }}">
                                <input type="radio" name="ending_style" value="{{ $key }}" {{ ($config['ending_style'] ?? 'intimate_editorial') === $key ? 'checked' : '' }}>
                                <div class="mayhen_ending_style_badge">{{ $style['badge'] }}</div>
                                <div class="mayhen_ending_style_name">{{ $style['name'] }}</div>
                                <div class="mayhen_ending_style_desc">{{ $style['desc'] }}</div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Tiêu Đề & Nhãn Phụ --}}
                <div class="mayhen_ending_row">
                    <div class="mayhen_ending_col">
                        <label class="mayhen_ending_label">Nhãn Phụ (Kicker)</label>
                        <input type="text" name="ending_kicker" class="mayhen_ending_input" value="{{ old('ending_kicker', $config['ending_kicker'] ?? 'Lời Cảm Ơn') }}" placeholder="Ví dụ: Lời Cảm Ơn, Thank You">
                    </div>
                    <div class="mayhen_ending_col">
                        <label class="mayhen_ending_label">Tiêu Đề Chính <span class="required">*</span></label>
                        <input type="text" name="ending_title" class="mayhen_ending_input" value="{{ old('ending_title', $config['ending_title'] ?? 'Cảm Ơn Bạn Vì Đã Đến') }}" placeholder="Ví dụ: Cảm Ơn Bạn Vì Đã Đến" required>
                    </div>
                </div>

                {{-- Mẫu Lời Tri Ân 1-Click --}}
                <div class="mayhen_ending_form_group">
                    <label class="mayhen_ending_label">Gợi Ý Mẫu Lời Tri Ân Lễ Nghi Văn Hóa Cưới Việt</label>
                    <div class="mayhen_ending_preset_chips">
                        @foreach($thankYouPresets as $presetKey => $preset)
                            <button type="button" class="mayhen_ending_preset_chip" data-msg="{{ $preset['message'] }}">
                                ✦ {{ $preset['title'] }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Nội dung Lời Cảm Ơn --}}
                <div class="mayhen_ending_form_group">
                    <label class="mayhen_ending_label">Nội Dung Lời Cảm Ơn Tri Ân <span class="required">*</span></label>
                    <textarea name="thank_you_message" id="thankYouMessageInput" class="mayhen_ending_textarea" rows="3" placeholder="Nhập lời cảm ơn gửi đến quan khách..." required>{{ old('thank_you_message', $config['thank_you_message'] ?? '') }}</textarea>
                </div>

                {{-- Chữ Ký Tên Đôi Uyên Ương & Ảnh Kỷ Niệm Khép Thiệp --}}
                <div class="mayhen_ending_row">
                    <div class="mayhen_ending_col">
                        <label class="mayhen_ending_label">Chữ Ký Tên Đôi Uyên Ương</label>
                        <input type="text" name="couple_signature_text" class="mayhen_ending_input" value="{{ old('couple_signature_text', $config['couple_signature_text'] ?? $coupleName) }}" placeholder="Ví dụ: Hoàng Khang & Mỹ Duyên">
                    </div>
                    <div class="mayhen_ending_col">
                        <label class="mayhen_ending_label">Đường Dẫn Ảnh Tròn Khép Thiệp (URL)</label>
                        <input type="text" name="ending_photo_url" class="mayhen_ending_input" value="{{ old('ending_photo_url', $config['ending_photo_url'] ?? '') }}" placeholder="/assets/client/images/sample-wedding-1.webp">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Cấu Hình Sổ Ký Tên Nghệ Thuật (Digital Signature Canvas) --}}
        <div class="mayhen_ending_card">
            <div class="mayhen_ending_card_header">
                <div class="mayhen_ending_card_icon">✍️</div>
                <div class="mayhen_ending_card_title_row">
                    <div>
                        <h2 class="mayhen_ending_card_title">2. Bảng Ký Tên Nghệ Thuật Cảm Ứng</h2>
                        <p class="mayhen_ending_card_desc">Cho phép khách mời dùng tay hoặc chuột ký tên, vẽ lời chúc trực tiếp lên canvas nghệ thuật.</p>
                    </div>
                    <label class="mayhen_ending_switch">
                        <input type="checkbox" name="signature_enabled" value="1" {{ !empty($config['signature_enabled']) ? 'checked' : '' }}>
                        <span class="mayhen_ending_slider"></span>
                        <span class="mayhen_ending_switch_text">Bật Sổ Ký Tên</span>
                    </label>
                </div>
            </div>

            <div class="mayhen_ending_card_body">
                <div class="mayhen_ending_row">
                    <div class="mayhen_ending_col">
                        <label class="mayhen_ending_label">Tiêu Đề Khối Ký Tên <span class="required">*</span></label>
                        <input type="text" name="signature_title" class="mayhen_ending_input" value="{{ old('signature_title', $config['signature_title'] ?? 'Sổ Ký Tên Điện Tử') }}" required>
                    </div>
                    <div class="mayhen_ending_col">
                        <label class="mayhen_ending_label">Phong Cách Nền Canvas (Pure CSS / SVG)</label>
                        <select name="signature_bg_style" class="mayhen_ending_select">
                            @foreach($bgStyles as $bgKey => $bgName)
                                <option value="{{ $bgKey }}" {{ ($config['signature_bg_style'] ?? 'stardust') === $bgKey ? 'selected' : '' }}>
                                    {{ $bgName }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mayhen_ending_form_group">
                    <label class="mayhen_ending_label">Mô Tả / Hướng Dẫn Ký Tên</label>
                    <input type="text" name="signature_subtitle" class="mayhen_ending_input" value="{{ old('signature_subtitle', $config['signature_subtitle'] ?? 'Lưu lại nét bút và chữ ký kỷ niệm thân thương cho hai đứa mình') }}">
                </div>

                <div class="mayhen_ending_notice">
                    <span class="mayhen_ending_notice_icon">💡</span>
                    <span><strong>Quy chuẩn bảo mật &amp; thẩm mỹ:</strong> Bảng ký tên được trang trí thuần bằng hoa văn đồ họa hoàng gia và hiệu ứng bụi sao, tuyệt đối không dùng ảnh người để đảm bảo tính trang trọng và riêng tư.</span>
                </div>
            </div>
        </div>

        {{-- Section 3: Cấu Hình Sổ Lưu Bút & Kiểm Duyệt Lời Chúc (Guestbook) --}}
        <div class="mayhen_ending_card">
            <div class="mayhen_ending_card_header">
                <div class="mayhen_ending_card_icon">📖</div>
                <div class="mayhen_ending_card_title_row">
                    <div>
                        <h2 class="mayhen_ending_card_title">3. Sổ Lưu Bút Chúc Phúc &amp; Quyền Riêng Tư</h2>
                        <p class="mayhen_ending_card_desc">Khách mời gửi lời chúc, tùy chọn hiển thị công khai hoặc gửi riêng tư cho cặp đôi.</p>
                    </div>
                    <label class="mayhen_ending_switch">
                        <input type="checkbox" name="wishes_enabled" value="1" {{ !empty($config['wishes_enabled']) ? 'checked' : '' }}>
                        <span class="mayhen_ending_slider"></span>
                        <span class="mayhen_ending_switch_text">Bật Sổ Lưu Bút</span>
                    </label>
                </div>
            </div>

            <div class="mayhen_ending_card_body">
                <div class="mayhen_ending_row">
                    <div class="mayhen_ending_col">
                        <label class="mayhen_ending_label">Tiêu Đề Sổ Lưu Bút <span class="required">*</span></label>
                        <input type="text" name="wishes_title" class="mayhen_ending_input" value="{{ old('wishes_title', $config['wishes_title'] ?? 'Sổ Lưu Bút Chúc Phúc') }}" required>
                    </div>
                    <div class="mayhen_ending_col">
                        <label class="mayhen_ending_label">Mô Tả / Phụ Đề</label>
                        <input type="text" name="wishes_subtitle" class="mayhen_ending_input" value="{{ old('wishes_subtitle', $config['wishes_subtitle'] ?? 'Gửi gắm những lời chúc phúc tốt đẹp nhất tới đôi uyên ương') }}">
                    </div>
                </div>

                <div class="mayhen_ending_row" style="margin-top: 1rem;">
                    <div class="mayhen_ending_col">
                        <label class="mayhen_ending_checkbox_label">
                            <input type="checkbox" name="require_approval" value="1" {{ !empty($config['require_approval']) ? 'checked' : '' }}>
                            <span><strong>Kiểm duyệt trước khi đăng:</strong> Lời chúc của khách chỉ xuất hiện trên thiệp sau khi được bạn bấm Duyệt.</span>
                        </label>
                    </div>
                    <div class="mayhen_ending_col">
                        <label class="mayhen_ending_checkbox_label">
                            <input type="checkbox" name="allow_private_wishes" value="1" {{ !empty($config['allow_private_wishes']) ? 'checked' : '' }}>
                            <span><strong>Cho phép gửi kín đáo:</strong> Khách mời có thể chọn gửi lời chúc chỉ cặp đôi đọc được.</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Thanh Cố Định Lưu Cấu Hình --}}
        <div class="mayhen_ending_bottom_bar">
            <a href="{{ route('account.invitations') }}" class="mayhen_ending_btn mayhen_ending_btn--back">
                Quay lại danh sách
            </a>
            <button type="submit" class="mayhen_ending_btn mayhen_ending_btn--save">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Lưu Cấu Hình Section 10
            </button>
        </div>
    </form>

    {{-- Section 4: Danh Sách & Bảng Kiểm Duyệt Lời Chúc Của Khách Mời --}}
    <div class="mayhen_ending_card" style="margin-top: 2rem;">
        <div class="mayhen_ending_card_header">
            <div class="mayhen_ending_card_icon">💬</div>
            <div>
                <h2 class="mayhen_ending_card_title">Bảng Kiểm Duyệt &amp; Quản Lý Lời Chúc ({{ $stats['total'] }})</h2>
                <p class="mayhen_ending_card_desc">Xem toàn bộ lời chúc từ quan khách, ghim lời chúc ý nghĩa, phản hồi lời cảm ơn hoặc ẩn lời chúc không phù hợp.</p>
            </div>
        </div>

        <div class="mayhen_ending_card_body" style="padding: 0;">
            <div class="mayhen_ending_table_responsive">
                <table class="mayhen_ending_table">
                    <thead>
                        <tr>
                            <th>Khách Mời</th>
                            <th>Nội Dung Lời Chúc</th>
                            <th>Chữ Ký</th>
                            <th>Trạng Thái</th>
                            <th>Thời Gian</th>
                            <th style="text-align: right;">Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($wishes as $wish)
                            <tr class="{{ $wish->is_pinned ? 'is-row-pinned' : '' }}">
                                <td>
                                    <strong>{{ $wish->name }}</strong>
                                    @if($wish->is_private)
                                        <div class="mayhen_ending_badge mayhen_ending_badge--private">🔒 Kín đáo</div>
                                    @endif
                                    @if($wish->is_pinned)
                                        <div class="mayhen_ending_badge mayhen_ending_badge--pinned">✦ Ghim đầu trang</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="mayhen_ending_wish_text">{{ $wish->message }}</div>
                                    @if(!empty($wish->reply))
                                        <div class="mayhen_ending_wish_reply_box">
                                            <em>Phản hồi của bạn:</em> {{ $wish->reply }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($wish->signature_url))
                                        <a href="{{ $wish->signature_url }}" target="_blank" class="mayhen_ending_sig_thumb">
                                            <img src="{{ $wish->signature_url }}" alt="Chữ ký" width="60" height="30">
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($wish->is_approved)
                                        <span class="mayhen_ending_status mayhen_ending_status--approved">Đã duyệt</span>
                                    @else
                                        <span class="mayhen_ending_status mayhen_ending_status--hidden">Đang ẩn</span>
                                    @endif
                                </td>
                                <td style="font-size: 0.8rem; color: #777;">
                                    {{ $wish->created_at ? $wish->created_at->format('d/m/Y H:i') : '' }}
                                </td>
                                <td style="text-align: right;">
                                    <div class="mayhen_ending_row_actions">
                                        {{-- Nút Ghim/Bỏ Ghim --}}
                                        <button type="button" class="mayhen_ending_action_btn btn-pin" data-id="{{ $wish->id }}" data-action="pin" title="{{ $wish->is_pinned ? 'Bỏ ghim' : 'Ghim lên đầu' }}">
                                            📌
                                        </button>

                                        {{-- Nút Duyệt/Ẩn --}}
                                        @if($wish->is_approved)
                                            <button type="button" class="mayhen_ending_action_btn btn-hide" data-id="{{ $wish->id }}" data-action="hide" title="Ẩn lời chúc">
                                                👁️‍🗨️
                                            </button>
                                        @else
                                            <button type="button" class="mayhen_ending_action_btn btn-approve" data-id="{{ $wish->id }}" data-action="approve" title="Duyệt hiển thị">
                                                ✓
                                            </button>
                                        @endif

                                        {{-- Nút Phản Hồi --}}
                                        <button type="button" class="mayhen_ending_action_btn btn-reply" data-id="{{ $wish->id }}" data-reply="{{ $wish->reply }}" title="Gửi lời cảm ơn">
                                            💬
                                        </button>

                                        {{-- Nút Xóa --}}
                                        <button type="button" class="mayhen_ending_action_btn btn-delete" data-id="{{ $wish->id }}" data-action="delete" title="Xóa lời chúc">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="mayhen_ending_table_empty">
                                    Chưa có lời chúc nào được gửi cho thiệp cưới này.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($wishes->hasPages())
                <div class="mayhen_ending_pagination">
                    {{ $wishes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL PHẢN HỒI LỜI CHÚC TỪ CẶP ĐÔI --}}
<div class="mayhen_ending_modal_overlay" id="replyModal" style="display: none;">
    <div class="mayhen_ending_modal">
        <div class="mayhen_ending_modal_head">
            <h3>Gửi Lời Cảm Ơn / Phản Hồi Cho Quan Khách</h3>
            <button type="button" class="mayhen_ending_modal_close" id="closeReplyModal">&times;</button>
        </div>
        <div class="mayhen_ending_modal_body">
            <p style="font-size: 0.9rem; color: #666; margin-top: 0;">
                Lời cảm ơn của bạn sẽ xuất hiện trang trọng ngay bên dưới lời chúc của vị khách này trên thiệp cưới.
            </p>
            <textarea id="replyTextInput" class="mayhen_ending_textarea" rows="3" placeholder="Ví dụ: Vợ chồng mình cảm ơn bạn rất nhiều! Rất vui vì có sự hiện diện của bạn..."></textarea>
        </div>
        <div class="mayhen_ending_modal_foot">
            <button type="button" class="mayhen_ending_btn mayhen_ending_btn--back" id="cancelReplyModal">Hủy</button>
            <button type="button" class="mayhen_ending_btn mayhen_ending_btn--save" id="submitReplyModal">Lưu Phản Hồi</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Chọn nhanh Mẫu Lời Tri Ân
    const chips = document.querySelectorAll('.mayhen_ending_preset_chip');
    const msgTextarea = document.getElementById('thankYouMessageInput');
    chips.forEach(chip => {
        chip.addEventListener('click', function() {
            if (msgTextarea) {
                msgTextarea.value = this.getAttribute('data-msg') || '';
                msgTextarea.focus();
            }
        });
    });

    // 2. Chuyển đổi Card phong cách kết thiệp
    const styleCards = document.querySelectorAll('.mayhen_ending_style_card');
    styleCards.forEach(card => {
        card.addEventListener('click', function() {
            styleCards.forEach(c => c.classList.remove('is-selected'));
            this.classList.add('is-selected');
        });
    });

    // 3. Xử lý Kiểm Duyệt Lời Chúc (Ghim, Duyệt, Ẩn, Xóa) qua AJAX
    const invitationId = '{{ $invitation->id }}';
    const csrfToken = '{{ csrf_token() }}';

    function sendModerateRequest(wishId, action, replyText = '') {
        const url = `/tai-khoan/thiep/${invitationId}/so-ky-ten/kiem-duyet/${wishId}`;
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ action: action, reply_text: replyText })
        }).then(r => r.json());
    }

    document.querySelectorAll('.mayhen_ending_action_btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const wishId = this.getAttribute('data-id');
            const action = this.getAttribute('data-action');

            if (action === 'delete') {
                if (!confirm('Bạn có chắc chắn muốn xóa vĩnh viễn lời chúc này không?')) return;
            }

            if (action) {
                sendModerateRequest(wishId, action).then(res => {
                    if (res.success) {
                        alert(res.message);
                        window.location.reload();
                    } else {
                        alert(res.message || 'Có lỗi xảy ra.');
                    }
                }).catch(() => alert('Không thể kết nối máy chủ.'));
            }
        });
    });

    // 4. Modal Phản Hồi Lời Chúc
    const replyModal = document.getElementById('replyModal');
    const replyInput = document.getElementById('replyTextInput');
    let currentWishIdForReply = null;

    document.querySelectorAll('.btn-reply').forEach(btn => {
        btn.addEventListener('click', function() {
            currentWishIdForReply = this.getAttribute('data-id');
            replyInput.value = this.getAttribute('data-reply') || '';
            replyModal.style.display = 'flex';
        });
    });

    function closeReply() {
        replyModal.style.display = 'none';
        currentWishIdForReply = null;
    }

    document.getElementById('closeReplyModal')?.addEventListener('click', closeReply);
    document.getElementById('cancelReplyModal')?.addEventListener('click', closeReply);

    document.getElementById('submitReplyModal')?.addEventListener('click', function() {
        if (!currentWishIdForReply) return;
        const text = replyInput.value.trim();
        sendModerateRequest(currentWishIdForReply, 'reply', text).then(res => {
            if (res.success) {
                alert(res.message);
                window.location.reload();
            } else {
                alert(res.message || 'Không thể lưu phản hồi.');
            }
        }).catch(() => alert('Có lỗi xảy ra.'));
    });
});
</script>

<style>
/* Pure CSS Quiet Luxury cho Màn Hình Studio Section 10 */
.mayhen_ending_admin_container {
    max-width: 1040px;
    margin: 0 auto;
    padding: 1.5rem 0 5rem;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    color: #2C2C2C;
}

.mayhen_ending_admin_header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.mayhen_ending_admin_badge {
    display: inline-block;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #C5A880;
    font-weight: 600;
    margin-bottom: 0.35rem;
}

.mayhen_ending_admin_title {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 2rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
    color: #1A1A1A;
}

.mayhen_ending_admin_subtitle {
    font-size: 0.95rem;
    color: #666;
    margin: 0;
    max-width: 680px;
    line-height: 1.5;
}

.mayhen_ending_export_btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.1rem;
    background: #FFFFFF;
    color: #2C2C2C;
    border: 1px solid #DDD8D0;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}

.mayhen_ending_export_btn:hover {
    background: #F8F6F2;
    border-color: #C5A880;
    color: #8C6D45;
}

/* Stat Grid */
.mayhen_ending_stat_grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.mayhen_ending_stat_card {
    background: #FFFFFF;
    border: 1px solid #ECE7DE;
    border-radius: 8px;
    padding: 1.25rem;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}

.mayhen_ending_stat_num {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 1.8rem;
    font-weight: 600;
    color: #C5A880;
    margin-bottom: 0.25rem;
}

.mayhen_ending_stat_label {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #777;
}

/* Cards & Forms */
.mayhen_ending_card {
    background: #FFFFFF;
    border: 1px solid #ECE7DE;
    border-radius: 12px;
    margin-bottom: 1.8rem;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.02);
}

.mayhen_ending_card_header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.5rem 1.8rem;
    border-bottom: 1px solid #F3EFE8;
}

.mayhen_ending_card_icon {
    font-size: 1.6rem;
    line-height: 1;
}

.mayhen_ending_card_title_row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
    flex-wrap: wrap;
    gap: 1rem;
}

.mayhen_ending_card_title {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 1.3rem;
    font-weight: 600;
    margin: 0 0 0.25rem;
    color: #2C2C2C;
}

.mayhen_ending_card_desc {
    font-size: 0.85rem;
    color: #777;
    margin: 0;
}

.mayhen_ending_card_body {
    padding: 1.8rem;
}

/* Switch & Controls */
.mayhen_ending_switch {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    cursor: pointer;
}

.mayhen_ending_switch input {
    display: none;
}

.mayhen_ending_slider {
    position: relative;
    width: 44px;
    height: 24px;
    background: #DDD;
    border-radius: 12px;
    transition: 0.25s;
}

.mayhen_ending_slider:before {
    content: "";
    position: absolute;
    width: 18px;
    height: 18px;
    left: 3px;
    top: 3px;
    background: #FFF;
    border-radius: 50%;
    transition: 0.25s;
}

.mayhen_ending_switch input:checked + .mayhen_ending_slider {
    background: #C5A880;
}

.mayhen_ending_switch input:checked + .mayhen_ending_slider:before {
    transform: translateX(20px);
}

.mayhen_ending_switch_text {
    font-size: 0.85rem;
    font-weight: 500;
    color: #444;
}

/* Form Styles */
.mayhen_ending_form_group {
    margin-bottom: 1.25rem;
}

.mayhen_ending_label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 0.4rem;
}

.mayhen_ending_label .required {
    color: #C62828;
}

.mayhen_ending_input,
.mayhen_ending_select,
.mayhen_ending_textarea {
    width: 100%;
    padding: 0.75rem 0.9rem;
    border: 1px solid #DDD8D0;
    border-radius: 6px;
    font-size: 0.9rem;
    font-family: inherit;
    background: #FCFBF9;
    box-sizing: border-box;
    transition: border-color 0.2s;
}

.mayhen_ending_input:focus,
.mayhen_ending_select:focus,
.mayhen_ending_textarea:focus {
    outline: none;
    border-color: #C5A880;
    background: #FFF;
}

.mayhen_ending_row {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
}

.mayhen_ending_col {
    flex: 1;
    min-width: 260px;
}

/* Style Grid */
.mayhen_ending_style_grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1rem;
    margin-top: 0.5rem;
}

.mayhen_ending_style_card {
    border: 1.5px solid #E5E0D6;
    border-radius: 8px;
    padding: 1.25rem;
    cursor: pointer;
    background: #FCFBF9;
    position: relative;
    transition: all 0.2s;
}

.mayhen_ending_style_card input {
    display: none;
}

.mayhen_ending_style_card.is-selected {
    border-color: #C5A880;
    background: #FFFDF9;
    box-shadow: 0 4px 12px rgba(197, 168, 128, 0.15);
}

.mayhen_ending_style_badge {
    display: inline-block;
    font-size: 0.7rem;
    text-transform: uppercase;
    background: #F4EFE6;
    color: #8C6D45;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.mayhen_ending_style_name {
    font-weight: 600;
    font-size: 0.95rem;
    color: #2C2C2C;
    margin-bottom: 0.35rem;
}

.mayhen_ending_style_desc {
    font-size: 0.8rem;
    color: #777;
    line-height: 1.4;
}

/* Preset Chips */
.mayhen_ending_preset_chips {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 0.4rem;
}

.mayhen_ending_preset_chip {
    padding: 0.4rem 0.8rem;
    background: #F4EFE6;
    color: #6B5234;
    border: 1px solid #E8DFD0;
    border-radius: 20px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.15s;
    font-family: inherit;
}

.mayhen_ending_preset_chip:hover {
    background: #EAE2D4;
    border-color: #C5A880;
}

.mayhen_ending_notice {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    background: #F9F7F3;
    border-left: 3px solid #C5A880;
    padding: 0.9rem 1.2rem;
    border-radius: 0 6px 6px 0;
    font-size: 0.85rem;
    color: #555;
    line-height: 1.5;
    margin-top: 1rem;
}

.mayhen_ending_notice_icon {
    font-size: 1.2rem;
}

/* Bottom Bar */
.mayhen_ending_bottom_bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #FFFFFF;
    border: 1px solid #ECE7DE;
    border-radius: 8px;
    padding: 1rem 1.5rem;
    margin-top: 1.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}

.mayhen_ending_btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-size: 0.9rem;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    font-family: inherit;
    border: none;
    transition: all 0.2s;
}

.mayhen_ending_btn--back {
    background: #F4F1EC;
    color: #555;
}

.mayhen_ending_btn--back:hover {
    background: #EAE5DD;
}

.mayhen_ending_btn--save {
    background: #2C2C2C;
    color: #FFF;
}

.mayhen_ending_btn--save:hover {
    background: #111;
}

/* Table */
.mayhen_ending_table_responsive {
    overflow-x: auto;
}

.mayhen_ending_table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.mayhen_ending_table th {
    background: #FBF9F6;
    padding: 0.9rem 1.2rem;
    text-align: left;
    font-weight: 600;
    color: #444;
    border-bottom: 1px solid #ECE7DE;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.mayhen_ending_table td {
    padding: 1rem 1.2rem;
    border-bottom: 1px solid #F3EFE8;
    vertical-align: top;
}

.mayhen_ending_table tr.is-row-pinned {
    background: #FFFDF8;
}

.mayhen_ending_wish_text {
    color: #333;
    line-height: 1.5;
    max-width: 320px;
    white-space: pre-line;
}

.mayhen_ending_wish_reply_box {
    margin-top: 0.5rem;
    background: #F7F3EB;
    border-left: 2px solid #C5A880;
    padding: 0.4rem 0.6rem;
    font-size: 0.8rem;
    color: #665239;
    border-radius: 3px;
}

.mayhen_ending_badge {
    display: inline-block;
    font-size: 0.7rem;
    padding: 0.15rem 0.4rem;
    border-radius: 3px;
    margin-top: 0.25rem;
}

.mayhen_ending_badge--private {
    background: #EDE7F6;
    color: #5E35B1;
}

.mayhen_ending_badge--pinned {
    background: #FFF3E0;
    color: #E65100;
    font-weight: 600;
}

.mayhen_ending_status {
    display: inline-block;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.mayhen_ending_status--approved {
    background: #E8F5E9;
    color: #2E7D32;
}

.mayhen_ending_status--hidden {
    background: #FFEBEE;
    color: #C62828;
}

.mayhen_ending_sig_thumb img {
    border: 1px solid #DDD;
    border-radius: 4px;
    background: #FFF;
    display: block;
}

.mayhen_ending_row_actions {
    display: flex;
    gap: 0.35rem;
    justify-content: flex-end;
}

.mayhen_ending_action_btn {
    width: 30px;
    height: 30px;
    border-radius: 4px;
    border: 1px solid #E2DDD5;
    background: #FFF;
    cursor: pointer;
    font-size: 0.85rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s;
}

.mayhen_ending_action_btn:hover {
    background: #F4EFE6;
    border-color: #C5A880;
}

.mayhen_ending_table_empty {
    text-align: center;
    padding: 2.5rem;
    color: #999;
    font-style: italic;
}

/* Modal */
.mayhen_ending_modal_overlay {
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

.mayhen_ending_modal {
    background: #FFF;
    border-radius: 12px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    overflow: hidden;
}

.mayhen_ending_modal_head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.2rem 1.5rem;
    border-bottom: 1px solid #ECE7DE;
}

.mayhen_ending_modal_head h3 {
    margin: 0;
    font-family: "Playfair Display", Georgia, serif;
    font-size: 1.15rem;
    color: #2C2C2C;
}

.mayhen_ending_modal_close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #999;
}

.mayhen_ending_modal_body {
    padding: 1.5rem;
}

.mayhen_ending_modal_foot {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #ECE7DE;
    background: #FAF8F5;
}

.mayhen_ending_alert {
    padding: 1rem 1.25rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.9rem;
}

.mayhen_ending_alert--success {
    background: #E8F5E9;
    border: 1px solid #C8E6C9;
    color: #2E7D32;
}

.mayhen_ending_alert--danger {
    background: #FFEBEE;
    border: 1px solid #FFCDD2;
    color: #C62828;
}

.mayhen_ending_alert_icon {
    font-weight: bold;
    font-size: 1.1rem;
}
</style>
@endsection

