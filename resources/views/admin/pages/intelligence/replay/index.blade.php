@extends('admin.layouts.master')

@section('title', 'Tái Hiện Chuỗi Vận Hành (Operations Replay)')
@section('page_title', '⏱️ Read-Only Operations Replay (Tái Hiện Chuỗi Thao Tác Vận Hành Cô Lập)')

@section('content')
    <!-- Search / Scope Selection Card -->
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 24px;">
        <div style="max-width: 650px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 6px;">
                🔍 Tra Cứu & Buồng Phóng Replay Chuỗi Sự Kiện
            </h3>
            <p style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 20px;">
                Nhập mã định danh để hợp nhất toàn bộ hành vi phân tán (Đơn hàng, Webhook, Sửa thiệp, RSVP, Check-in, Khiếu nại) vào một dòng thời gian liên tục đã khử PII an toàn.
            </p>

            <form action="" method="GET" id="replayLaunchForm" onsubmit="event.preventDefault(); launchReplay();" style="display: flex; gap: 12px; flex-wrap: wrap;">
                <select id="scopeTypeSelect" class="mayhen_admin_form_select" style="min-width: 160px;">
                    <option value="wedding">Đám Cưới (ID)</option>
                    <option value="order">Đơn Hàng (Mã Đơn / ID)</option>
                    <option value="user">Khách Hàng (User ID)</option>
                </select>

                <input type="text" id="scopeIdInput" class="mayhen_admin_form_input" style="flex: 1; min-width: 220px;" placeholder="Ví dụ: 1 hoặc MH20261110..." required>

                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary">
                    Bắt Đầu Replay &rarr;
                </button>
            </form>
        </div>
    </div>

    <!-- 2 Columns: Recent Weddings & Recent Orders for 1-Click Replay -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
        <!-- Left: Recent Weddings -->
        <div class="mayhen_admin_card">
            <div class="mayhen_admin_card_header">
                <div class="mayhen_admin_card_title">
                    <span>💍 Đám Cưới Gần Đây (1-Click Replay)</span>
                </div>
            </div>

            <div class="mayhen_admin_table_responsive">
                <table class="mayhen_admin_table">
                    <thead>
                        <tr>
                            <th>Mã</th>
                            <th>Cặp Đôi</th>
                            <th>Ngày Cưới</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentWeddings as $w)
                            <tr>
                                <td><code>#{{ $w->id }}</code></td>
                                <td>
                                    <strong>{{ $w->groom_name }} &amp; {{ $w->bride_name }}</strong>
                                </td>
                                <td style="font-size: 0.8125rem;">
                                    {{ $w->wedding_date ? $w->wedding_date->format('d/m/Y') : 'Chưa định' }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.intelligence.replay.show', ['type' => 'wedding', 'id' => $w->id]) }}" class="mayhen_admin_btn mayhen_admin_btn--primary mayhen_admin_btn_sm">
                                        Replay &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--mh-admin-muted);">Chưa có dữ liệu đám cưới</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right: Recent Orders -->
        <div class="mayhen_admin_card">
            <div class="mayhen_admin_card_header">
                <div class="mayhen_admin_card_title">
                    <span>💰 Đơn Hàng Gần Đây (1-Click Replay)</span>
                </div>
            </div>

            <div class="mayhen_admin_table_responsive">
                <table class="mayhen_admin_table">
                    <thead>
                        <tr>
                            <th>Mã Đơn</th>
                            <th>Khách Hàng</th>
                            <th>Số Tiền</th>
                            <th>Trạng Thái</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $o)
                            <tr>
                                <td><code>{{ $o->order_code }}</code></td>
                                <td>{{ $o->user->name ?? 'Khách hàng' }}</td>
                                <td>{{ number_format($o->amount) }}đ</td>
                                <td>
                                    <span class="mayhen_admin_badge {{ $o->isPaid() ? 'mayhen_admin_badge_success' : 'mayhen_admin_badge_warning' }}">
                                        {{ strtoupper($o->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.intelligence.replay.show', ['type' => 'order', 'id' => $o->order_code]) }}" class="mayhen_admin_btn mayhen_admin_btn_outline mayhen_admin_btn_sm">
                                        Replay &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: var(--mh-admin-muted);">Chưa có đơn hàng nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function launchReplay() {
            const type = document.getElementById('scopeTypeSelect').value;
            const id = encodeURIComponent(document.getElementById('scopeIdInput').value.trim());
            if (id) {
                window.location.href = '/admin/intelligence/replay/' + type + '/' + id;
            }
        }
    </script>
@endsection

