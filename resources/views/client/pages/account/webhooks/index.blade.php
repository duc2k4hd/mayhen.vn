@extends('client.pages.account.layout')

@section('page_title', 'Tự Động Hóa & Webhook | ' . config('app.name'))
@section('account_breadcrumb', 'Tự Động Hóa & Webhook')

@section('account_content')
<div class="mayhen_account_webhooks_wrapper">
    {{-- Header --}}
    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div>
            <div style="margin-bottom: 8px;">
                <a href="{{ route('account.invitations') }}" style="color: #8C827A; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px;">
                    &larr; <span>Quay lại danh sách thiệp</span>
                </a>
            </div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 28px; color: #1F1D1A; margin: 0;">
                Tự Động Hóa & Outbound Webhook
            </h2>
            <p style="font-size: 14px; color: #8C827A; margin: 4px 0 0 0;">
                Đám cưới: <strong style="color: #1F1D1A;">{{ $wedding->groom_name }} & {{ $wedding->bride_name }}</strong> • Tự động đẩy thông báo RSVP, Lời chúc, Mừng cưới tức thì về Telegram, Discord, Google Sheets, Zapier.
            </p>
        </div>

        <button type="button" class="mayhen_account_btn mayhen_account_btn_primary" onclick="openCreateModal()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Tạo Webhook Mới</span>
        </button>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div style="background: #DEF7EC; border: 1px solid #BCF0DA; color: #03543F; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
            {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background: #FDE8E8; border: 1px solid #F8B4B4; color: #9B1C1C; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Alert Trạng Thái Ping Realtime --}}
    <div id="pingAlert" style="display: none; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;"></div>

    {{-- Webhooks List --}}
    <div style="display: flex; flex-direction: column; gap: 18px; margin-bottom: 32px;">
        @forelse($webhooks as $webhook)
            <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 14px; padding: 22px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 14px; margin-bottom: 14px;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                            <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; color: #1F1D1A; margin: 0;">
                                {{ $webhook->name }}
                            </h3>

                            @if($webhook->isCircuitBroken())
                                <span style="font-size: 11px; font-weight: 700; background: #FDE8E8; color: #9B1C1C; padding: 2px 8px; border-radius: 12px;">
                                    Tạm Ngắt Mạch (Lỗi liên tiếp {{ $webhook->failure_count }} lần)
                                </span>
                            @elseif($webhook->is_active)
                                <span style="font-size: 11px; font-weight: 600; background: #DEF7EC; color: #03543F; padding: 2px 8px; border-radius: 12px; display: inline-flex; align-items: center; gap: 4px;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #03543F;"></span>
                                    Đang Lắng Nghe
                                </span>
                            @else
                                <span style="font-size: 11px; font-weight: 600; background: #F3F4F6; color: #6B7280; padding: 2px 8px; border-radius: 12px;">
                                    Tạm Dừng
                                </span>
                            @endif
                        </div>

                        <div style="font-family: monospace; font-size: 13px; color: #9C7A4A; word-break: break-all; margin-bottom: 8px;">
                            {{ $webhook->payload_url }}
                        </div>

                        {{-- Event Badges --}}
                        <div style="display: flex; flex-wrap: wrap; gap: 6px; align-items: center;">
                            <span style="font-size: 12px; color: #8C827A;">Sự kiện:</span>
                            @foreach($webhook->events ?? [] as $evt)
                                <span style="font-size: 11px; background: #FAF8F5; border: 1px solid #EAE3D9; padding: 2px 8px; border-radius: 6px; color: #1F1D1A; font-family: monospace;">
                                    @if($evt === 'rsvp.submitted') 📋 rsvp.submitted (Xác nhận tham dự)
                                    @elseif($evt === 'wish.created') ✍️ wish.created (Lời chúc mới)
                                    @elseif($evt === 'gift.received') 💰 gift.received (Tiền mừng cưới)
                                    @else {{ $evt }}
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" id="btnPing{{ $webhook->id }}" onclick="pingWebhook({{ $webhook->id }})">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                            <span>Gửi Thử (Ping)</span>
                        </button>

                        <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" onclick="openEditModal({{ json_encode($webhook) }})">
                            Sửa
                        </button>

                        <form method="POST" action="{{ route('account.webhooks.destroy', [$invitation->id, $webhook->id]) }}" onsubmit="return confirm('Bạn có chắc muốn xóa webhook này?');" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="mayhen_account_btn mayhen_account_btn_sm" style="background: transparent; color: #9B1C1C; border: 1px solid #F8B4B4;">
                                Xóa
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Secret Key & Meta --}}
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; padding-top: 12px; border-top: 1px solid #FAF8F5; font-size: 12px; color: #8C827A;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span>Khóa bí mật (HMAC SHA-256 Secret):</span>
                        <code style="background: #FAF8F5; padding: 2px 6px; border-radius: 4px; color: #1F1D1A;">{{ $webhook->secret }}</code>
                        <button type="button" style="background: none; border: none; color: #9C7A4A; cursor: pointer; text-decoration: underline; font-size: 11px;" onclick="navigator.clipboard.writeText('{{ $webhook->secret }}'); alert('Đã sao chép khóa bí mật!');">
                            Chép
                        </button>
                    </div>

                    <div>
                        @if($webhook->last_dispatched_at)
                            <span>Bắn gần nhất: {{ $webhook->last_dispatched_at->format('d/m/Y H:i:s') }}</span>
                            @if($webhook->last_status_code)
                                <span style="font-weight: 600; color: {{ $webhook->last_status_code >= 200 && $webhook->last_status_code < 300 ? '#2E7D32' : '#9B1C1C' }};">
                                    (HTTP {{ $webhook->last_status_code }})
                                </span>
                            @endif
                        @else
                            <span>Chưa có đợt phát tin nào</span>
                        @endif
                    </div>
                </div>

                {{-- Deliveries List (Recent 3) --}}
                @if($webhook->deliveries->isNotEmpty())
                    <div style="margin-top: 14px; background: #FAF8F5; border-radius: 8px; padding: 10px 14px;">
                        <span style="font-size: 11px; text-transform: uppercase; color: #8C827A; letter-spacing: 0.5px; display: block; margin-bottom: 6px;">
                            Nhật ký 3 lần gửi gần nhất:
                        </span>
                        <div style="display: flex; flex-direction: column; gap: 4px; font-size: 12px;">
                            @foreach($webhook->deliveries->take(3) as $del)
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span>
                                        <strong style="color: {{ $del->is_success ? '#2E7D32' : '#9B1C1C' }};">
                                            {{ $del->is_success ? '✓ Thành công' : '✕ Lỗi' }}
                                        </strong>
                                        • Sự kiện: <code>{{ $del->event_type }}</code> ({{ $del->duration_ms }}ms)
                                    </span>
                                    <span style="color: #8C827A; font-size: 11px;">
                                        {{ $del->created_at->format('H:i:s d/m/Y') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div style="background: #FFFFFF; border: 1px dashed #EAE3D9; border-radius: 14px; padding: 40px; text-align: center; color: #8C827A;">
                Chưa có Webhook nào được cấu hình. Bấm "Tạo Webhook Mới" để tự động kết nối đám cưới của bạn với Telegram Bot, Discord hoặc Google Sheets.
            </div>
        @endforelse
    </div>
</div>

{{-- Modal Thêm / Chỉnh Sửa Webhook --}}
<div id="webhookModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #FFFFFF; border-radius: 16px; max-width: 520px; width: 92%; margin: 70px auto; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2);">
        <h3 id="webhookModalTitle" style="font-family: 'Cormorant Garamond', serif; font-size: 24px; color: #1F1D1A; margin-top: 0; margin-bottom: 16px;">
            Tạo Webhook Mới
        </h3>

        <form id="webhookForm" method="POST" action="{{ route('account.webhooks.store', $invitation->id) }}">
            @csrf
            <input type="hidden" name="_method" id="webhookFormMethod" value="POST">

            <div style="margin-bottom: 14px;">
                <label style="font-size: 13px; font-weight: 600; color: #1F1D1A; display: block; margin-bottom: 6px;">Tên Webhook Gợi Nhớ <span style="color: #9B1C1C;">*</span></label>
                <input type="text" name="name" id="webhookName" placeholder="Ví dụ: Telegram Bot Báo Khách, Google Sheets..." style="width: 100%; padding: 10px 14px; border: 1px solid #EAE3D9; border-radius: 8px; font-size: 14px;" required>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="font-size: 13px; font-weight: 600; color: #1F1D1A; display: block; margin-bottom: 6px;">Đường Dẫn Endpoint (Payload URL) <span style="color: #9B1C1C;">*</span></label>
                <input type="url" name="payload_url" id="webhookUrl" placeholder="https://api.telegram.org/... hoặc https://hooks.zapier.com/..." style="width: 100%; padding: 10px 14px; border: 1px solid #EAE3D9; border-radius: 8px; font-size: 14px; font-family: monospace;" required>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="font-size: 13px; font-weight: 600; color: #1F1D1A; display: block; margin-bottom: 8px;">Chọn Các Sự Kiện Nhận Thông Báo <span style="color: #9B1C1C;">*</span></label>
                
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="events[]" value="rsvp.submitted" id="evtRsvp" checked>
                        <span><strong>rsvp.submitted</strong> — Khách mời xác nhận tham dự hoặc từ chối</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="events[]" value="wish.created" id="evtWish" checked>
                        <span><strong>wish.created</strong> — Có quan khách gửi lời chúc mừng mới</span>
                    </label>

                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                        <input type="checkbox" name="events[]" value="gift.received" id="evtGift" checked>
                        <span><strong>gift.received</strong> — Có giao dịch mừng cưới qua chuyển khoản VietQR</span>
                    </label>
                </div>
            </div>

            <div style="margin-bottom: 22px;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #1F1D1A; cursor: pointer;">
                    <input type="checkbox" name="is_active" id="webhookIsActive" value="1" checked>
                    <span>Kích hoạt Webhook này ngay lập tức</span>
                </label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline" onclick="closeWebhookModal()">
                    Hủy Bỏ
                </button>
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">
                    Lưu Webhook
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('webhookModalTitle').textContent = 'Tạo Webhook Mới';
        document.getElementById('webhookForm').action = '{{ route("account.webhooks.store", $invitation->id) }}';
        document.getElementById('webhookFormMethod').value = 'POST';

        document.getElementById('webhookName').value = '';
        document.getElementById('webhookUrl').value = '';
        document.getElementById('evtRsvp').checked = true;
        document.getElementById('evtWish').checked = true;
        document.getElementById('evtGift').checked = true;
        document.getElementById('webhookIsActive').checked = true;

        document.getElementById('webhookModal').style.display = 'block';
    }

    function openEditModal(webhook) {
        document.getElementById('webhookModalTitle').textContent = 'Chỉnh Sửa Webhook: ' + webhook.name;
        document.getElementById('webhookForm').action = '/tai-khoan/thiep/{{ $invitation->id }}/webhooks/' + webhook.id;
        document.getElementById('webhookFormMethod').value = 'PUT';

        document.getElementById('webhookName').value = webhook.name || '';
        document.getElementById('webhookUrl').value = webhook.payload_url || '';

        const evts = webhook.events || [];
        document.getElementById('evtRsvp').checked = evts.includes('rsvp.submitted');
        document.getElementById('evtWish').checked = evts.includes('wish.created');
        document.getElementById('evtGift').checked = evts.includes('gift.received');
        document.getElementById('webhookIsActive').checked = Boolean(webhook.is_active);

        document.getElementById('webhookModal').style.display = 'block';
    }

    function closeWebhookModal() {
        document.getElementById('webhookModal').style.display = 'none';
    }

    function pingWebhook(id) {
        const btn = document.getElementById('btnPing' + id);
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span>Đang gửi...</span>';
        btn.disabled = true;

        const alertBox = document.getElementById('pingAlert');

        fetch('/tai-khoan/thiep/{{ $invitation->id }}/webhooks/' + id + '/ping', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(res => res.json())
        .then(data => {
            btn.innerHTML = originalText;
            btn.disabled = false;

            alertBox.style.display = 'block';
            if (data.success) {
                alertBox.style.background = '#DEF7EC';
                alertBox.style.borderColor = '#BCF0DA';
                alertBox.style.color = '#03543F';
            } else {
                alertBox.style.background = '#FDE8E8';
                alertBox.style.borderColor = '#F8B4B4';
                alertBox.style.color = '#9B1C1C';
            }
            alertBox.textContent = data.message;

            setTimeout(() => {
                location.reload();
            }, 1800);
        })
        .catch(err => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            alertBox.style.display = 'block';
            alertBox.style.background = '#FDE8E8';
            alertBox.style.borderColor = '#F8B4B4';
            alertBox.style.color = '#9B1C1C';
            alertBox.textContent = 'Lỗi kết nối kiểm tra: ' + err.message;
        });
    }
</script>
@endsection

