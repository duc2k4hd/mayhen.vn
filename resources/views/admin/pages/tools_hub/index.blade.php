@extends('admin.layouts.master')

@section('title', 'Trung Tâm Quản Trị Toàn Bộ Công Cụ, Hành Vi & Lưu Trữ 6 Tháng')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/admin/css/pages/tools-hub.css') }}">
@endpush

@section('content')
<div class="mayhen_admin_tools_wrap">
    <div class="mayhen_admin_tools_head">
        <div>
            <h1 class="mayhen_admin_tools_title">Trung Tâm Quản Trị Hệ Sinh Thái 9 Công Cụ &amp; Dữ Liệu Người Dùng</h1>
            <p class="mayhen_admin_tools_sub">Theo dõi tập trung toàn bộ dữ liệu người dùng tạo ra, dòng thời gian hành vi, bằng chứng chấp thuận Cookie (Luật 91/2025/QH15) và Cron Job lưu trữ 6 tháng.</p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('admin.tools_hub.export', ['scope' => 'activities']) }}" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--outline">Xuất CSV Hành Vi</a>
            <a href="{{ route('admin.tools_hub.export', ['scope' => 'consents']) }}" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--outline">Xuất CSV Cookie Consent</a>
            <a href="{{ route('admin.tools_hub.export', ['scope' => 'archives']) }}" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--primary">Xuất CSV Kho Lưu Trữ 6T</a>
        </div>
    </div>

    @if (session('success'))
        <div style="background:#DCFCE7;border:1px solid #86EFAC;color:#166534;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-weight:600;">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div style="background:#FEE2E2;border:1px solid #FCA5A5;color:#991B1B;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-weight:600;">
            {{ session('error') }}
        </div>
    @endif

    {{-- 5 KPI TỔNG QUAN --}}
    <div class="mayhen_admin_tools_kpis">
        <div class="mayhen_admin_tools_kpi">
            <div class="mayhen_admin_tools_kpi_label">Tổng Hồ Sơ / Link Đang Hoạt Động</div>
            <div class="mayhen_admin_tools_kpi_val">{{ number_format($overview['totals']['active_records']) }}</div>
            <div style="font-size:11.5px;color:#1E4D3B;">+{{ number_format($overview['totals']['last_7_days']) }} trong 7 ngày qua</div>
        </div>
        <div class="mayhen_admin_tools_kpi">
            <div class="mayhen_admin_tools_kpi_label">Link Chia Sẻ Công Khai</div>
            <div class="mayhen_admin_tools_kpi_val">{{ number_format($overview['totals']['shared_links']) }}</div>
            <div style="font-size:11.5px;color:#64748B;">Hiệu lực tối đa 6 tháng</div>
        </div>
        <div class="mayhen_admin_tools_kpi">
            <div class="mayhen_admin_tools_kpi_label">Quá Hạn 6 Tháng (Chờ Cron Dọn)</div>
            <div class="mayhen_admin_tools_kpi_val {{ $overview['totals']['expired_ready_to_prune'] > 0 ? 'mayhen_admin_tools_kpi_val--warn' : 'mayhen_admin_tools_kpi_val--ok' }}">
                {{ number_format($overview['totals']['expired_ready_to_prune']) }}
            </div>
            <div style="font-size:11.5px;color:#64748B;">Sắp hết hạn 30 ngày: {{ number_format($overview['totals']['expiring_within_30_days']) }}</div>
        </div>
        <div class="mayhen_admin_tools_kpi">
            <div class="mayhen_admin_tools_kpi_label">Hành Vi Người Dùng (24h / Tổng)</div>
            <div class="mayhen_admin_tools_kpi_val">{{ number_format($overview['totals']['activity_24h']) }}</div>
            <div style="font-size:11.5px;color:#64748B;">Tổng ghi nhận: {{ number_format($overview['totals']['activity_total']) }}</div>
        </div>
        <div class="mayhen_admin_tools_kpi">
            <div class="mayhen_admin_tools_kpi_label">Chấp Thuận Cookie &amp; Dữ Liệu</div>
            <div class="mayhen_admin_tools_kpi_val mayhen_admin_tools_kpi_val--ok">{{ number_format($overview['totals']['consent_total']) }}</div>
            <div style="font-size:11.5px;color:#64748B;">Đồng ý phân tích: {{ $overview['totals']['consent_analytics_rate'] }}%</div>
        </div>
    </div>

    {{-- LƯỚI 9 PHÂN HỆ CÔNG CỤ --}}
    <div class="mayhen_admin_tools_grid9">
        @foreach ($overview['per_tool'] as $key => $meta)
            <a href="{{ route('admin.tools_hub.index', ['tab' => 'records', 'tool' => $key]) }}"
               class="mayhen_admin_tools_card9 {{ $activeTab === 'records' && $activeTool === $key ? 'is-active' : '' }}"
               style="border-left-color: {{ $meta['accent'] }};">
                <div>
                    <div style="font-weight:700;font-size:14px;">{{ $meta['label'] }}</div>
                    <div style="font-size:12px;color:#64748B;margin-top:2px;">
                        7 ngày: +{{ $meta['last_7_days'] }} · Share: {{ $meta['shared'] }} · Quá hạn 6T: {{ $meta['expired'] }}
                    </div>
                </div>
                <div style="font-size:20px;font-weight:800;color:{{ $meta['accent'] }};">{{ number_format($meta['total']) }}</div>
            </a>
        @endforeach
    </div>

    {{-- NAVIGATION TABS --}}
    <div class="mayhen_admin_tools_tabs">
        <a href="{{ route('admin.tools_hub.index', ['tab' => 'records', 'tool' => $activeTool]) }}" class="mayhen_admin_tools_tab {{ $activeTab === 'records' ? 'is-active' : '' }}">1. Dữ Liệu &amp; Link Người Dùng Tạo</a>
        <a href="{{ route('admin.tools_hub.index', ['tab' => 'activities']) }}" class="mayhen_admin_tools_tab {{ $activeTab === 'activities' ? 'is-active' : '' }}">2. Dòng Thời Gian Hành Vi Người Dùng</a>
        <a href="{{ route('admin.tools_hub.index', ['tab' => 'consents']) }}" class="mayhen_admin_tools_tab {{ $activeTab === 'consents' ? 'is-active' : '' }}">3. Bằng Chứng Chấp Thuận Cookie &amp; Dữ Liệu</a>
        <a href="{{ route('admin.tools_hub.index', ['tab' => 'retention']) }}" class="mayhen_admin_tools_tab {{ $activeTab === 'retention' ? 'is-active' : '' }}">4. Cron Job Lưu Trữ 6 Tháng &amp; Kho Phân Tích ({{ number_format($overview['totals']['archived_analytics']) }})</a>
    </div>

    {{-- TAB 1: DANH SÁCH BẢN GHI NGƯỜI DÙNG TẠO --}}
    @if ($activeTab === 'records')
        <section class="mayhen_admin_tools_panel">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:16px;">
                <h2 style="margin:0;font-size:18px;">Dữ liệu công cụ: {{ $recordsData['tool']['label'] }}</h2>
                <form method="GET" action="{{ route('admin.tools_hub.index') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                    <input type="hidden" name="tab" value="records">
                    <input type="hidden" name="tool" value="{{ $activeTool }}">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm tiêu đề, slug, user ID..." style="padding:7px 10px;border:1px solid #CBD5E1;border-radius:8px;font-size:13px;">
                    <label style="font-size:12.5px;display:inline-flex;align-items:center;gap:5px;">
                        <input type="checkbox" name="expired_only" value="1" {{ request()->boolean('expired_only') ? 'checked' : '' }}> Chỉ xem quá hạn 6T
                    </label>
                    <button type="submit" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--primary">Lọc</button>
                </form>
            </div>

            <div class="mayhen_admin_tools_table_wrap">
                <table class="mayhen_admin_tools_table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tiêu đề / Slug</th>
                            <th>Người tạo &amp; Gói</th>
                            <th>Ngày tạo</th>
                            <th>Hạn lưu 6 tháng</th>
                            <th>Link chia sẻ</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recordsData['rows'] as $row)
                            <tr>
                                <td>#{{ $row['id'] }}</td>
                                <td>
                                    <strong>{{ \Illuminate\Support\Str::limit($row['title'], 70) }}</strong>
                                    @if ($row['slug'])
                                        <div style="font-size:11.5px;color:#64748B;">slug: {{ $row['slug'] }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div><strong>{{ $row['user_name'] }}</strong> ({{ strtoupper($row['user_plan']) }})</div>
                                    <div style="font-size:11.5px;color:#64748B;">{{ $row['user_email'] ?? ('User ID: ' . ($row['user_id'] ?? 'Khách')) }}</div>
                                </td>
                                <td>{{ $row['created_at'] }}</td>
                                <td>
                                    @if ($row['is_expired'])
                                        <span class="mayhen_admin_tools_badge mayhen_admin_tools_badge--danger">Quá hạn ({{ $row['expires_at'] }})</span>
                                    @elseif ($row['days_left'] !== null && $row['days_left'] <= 30)
                                        <span class="mayhen_admin_tools_badge mayhen_admin_tools_badge--warn">Còn {{ $row['days_left'] }} ngày</span>
                                    @else
                                        <span class="mayhen_admin_tools_badge mayhen_admin_tools_badge--ok">{{ $row['expires_at'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($row['share_token'])
                                        <span class="mayhen_admin_tools_badge mayhen_admin_tools_badge--ok">Đang mở</span>
                                    @else
                                        <span style="color:#94A3B8;font-size:12px;">Riêng tư</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($row['share_token'])
                                        <form method="POST" action="{{ route('admin.tools_hub.revoke_share', ['toolKey' => $activeTool, 'id' => $row['id']]) }}" onsubmit="return confirm('Thu hồi link chia sẻ công khai này?');">
                                            @csrf
                                            <button type="submit" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--danger" style="padding:5px 10px;font-size:12px;">Thu hồi link</button>
                                        </form>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="color:#64748B;text-align:center;padding:24px;">Chưa có dữ liệu nào trong phân hệ này.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if (! empty($recordsData['paginator']))
                <div style="margin-top:16px;">{{ $recordsData['paginator']->appends(request()->query())->links() }}</div>
            @endif
        </section>
    @endif

    {{-- TAB 2: DÒNG THỜI GIAN HÀNH VI NGƯỜI DÙNG --}}
    @if ($activeTab === 'activities')
        <section class="mayhen_admin_tools_panel">
            <h2 style="margin:0 0 14px;font-size:18px;">Nhật Ký Hành Vi Sử Dụng Công Cụ Theo Thời Gian Thực</h2>
            <div class="mayhen_admin_tools_table_wrap">
                <table class="mayhen_admin_tools_table">
                    <thead>
                        <tr><th>Thời gian</th><th>Người dùng</th><th>Công cụ</th><th>Hành động</th><th>Đường dẫn &amp; Tóm tắt</th><th>IP</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($activities as $act)
                            <tr>
                                <td>{{ $act->created_at?->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $act->user ? ($act->user->name . ' (#' . $act->user_id . ')') : 'Khách ẩn danh' }}</td>
                                <td><span class="mayhen_admin_tools_badge mayhen_admin_tools_badge--ok">{{ $act->tool_key }}</span></td>
                                <td><strong>{{ $act->method }}</strong> · {{ $act->action }}</td>
                                <td>
                                    <div>{{ $act->summary }}</div>
                                    <div style="font-size:11.5px;color:#64748B;">{{ \Illuminate\Support\Str::limit($act->url, 90) }}</div>
                                </td>
                                <td>{{ $act->ip_address }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="color:#64748B;text-align:center;padding:24px;">Chưa ghi nhận hành vi mới.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px;">{{ $activities->appends(request()->query())->links() }}</div>
        </section>
    @endif

    {{-- TAB 3: BẰNG CHỨNG CHẤP THUẬN COOKIE & DỮ LIỆU --}}
    @if ($activeTab === 'consents')
        <section class="mayhen_admin_tools_panel">
            <h2 style="margin:0 0 14px;font-size:18px;">Bằng Chứng Chấp Thuận Cookie &amp; Thu Thập Dữ Liệu (Luật 91/2025/QH15)</h2>
            <div class="mayhen_admin_tools_table_wrap">
                <table class="mayhen_admin_tools_table">
                    <thead>
                        <tr><th>Thời điểm</th><th>Người dùng</th><th>Phiên bản CS</th><th>Thiết yếu</th><th>Phân tích</th><th>Cá nhân hóa AI</th><th>Marketing</th><th>Trang nguồn</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($consents as $con)
                            <tr>
                                <td>{{ $con->accepted_at?->format('d/m/Y H:i:s') }}</td>
                                <td>{{ $con->user ? $con->user->email : ('Session: ' . substr($con->session_id, 0, 10) . '...') }}</td>
                                <td>{{ $con->policy_version }}</td>
                                <td><span class="mayhen_admin_tools_badge mayhen_admin_tools_badge--ok">Bắt buộc</span></td>
                                <td><span class="mayhen_admin_tools_badge {{ $con->consent_analytics ? 'mayhen_admin_tools_badge--ok' : 'mayhen_admin_tools_badge--warn' }}">{{ $con->consent_analytics ? 'Đồng ý' : 'Từ chối' }}</span></td>
                                <td><span class="mayhen_admin_tools_badge {{ $con->consent_personalization ? 'mayhen_admin_tools_badge--ok' : 'mayhen_admin_tools_badge--warn' }}">{{ $con->consent_personalization ? 'Đồng ý' : 'Từ chối' }}</span></td>
                                <td><span class="mayhen_admin_tools_badge {{ $con->consent_marketing ? 'mayhen_admin_tools_badge--ok' : 'mayhen_admin_tools_badge--warn' }}">{{ $con->consent_marketing ? 'Đồng ý' : 'Từ chối' }}</span></td>
                                <td style="font-size:11.5px;color:#64748B;">{{ \Illuminate\Support\Str::limit($con->source_url, 60) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" style="color:#64748B;text-align:center;padding:24px;">Chưa có bản ghi chấp thuận cookie nào.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px;">{{ $consents->appends(request()->query())->links() }}</div>
        </section>
    @endif

    {{-- TAB 4: CRON JOB LƯU TRỮ 6 THÁNG & KHO PHÂN TÍCH --}}
    @if ($activeTab === 'retention')
        <section class="mayhen_admin_tools_panel">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:14px;margin-bottom:18px;padding-bottom:16px;border-bottom:1px solid #E5E7EB;">
                <div>
                    <h2 style="margin:0 0 6px;font-size:18px;">Chính Sách Lưu Trữ 6 Tháng &amp; Điều Khiển Cron Job</h2>
                    <p style="margin:0;font-size:13px;color:#5F5750;">
                        Lịch tự động hằng ngày <strong>03:45 sáng</strong> (`php artisan mayhen:prune-expired-tools --months=6`).<br>
                        Hệ thống lưu lại bản tóm tắt phân tích trong <code>tool_retention_archives</code> rồi xóa sạch dữ liệu/link chi tiết cũ hơn 6 tháng.
                    </p>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <form method="POST" action="{{ route('admin.tools_hub.prune') }}">
                        @csrf
                        <input type="hidden" name="months" value="6">
                        <input type="hidden" name="dry_run" value="1">
                        <button type="submit" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--outline">Chạy Thử Kiểm Đếm (Dry-Run)</button>
                    </form>
                    <form method="POST" action="{{ route('admin.tools_hub.prune') }}" onsubmit="return confirm('Xác nhận lưu bản phân tích và xóa toàn bộ dữ liệu/link công cụ quá hạn 6 tháng?');">
                        @csrf
                        <input type="hidden" name="months" value="6">
                        <input type="hidden" name="dry_run" value="0">
                        <button type="submit" class="mayhen_admin_tools_btn mayhen_admin_tools_btn--danger">Chạy Dọn Dẹp 6 Tháng Ngay</button>
                    </form>
                </div>
            </div>

            <h3 style="margin:0 0 12px;font-size:15.5px;">Kho Lưu Trữ Thông Tin Phân Tích (Giữ Lại Sau Khi Xóa Dữ Liệu 6 Tháng)</h3>
            <div class="mayhen_admin_tools_table_wrap">
                <table class="mayhen_admin_tools_table">
                    <thead>
                        <tr><th>ID</th><th>Công cụ</th><th>Tiêu đề &amp; Slug cũ</th><th>Người dùng &amp; Gói</th><th>Tạo gốc</th><th>Lưu trữ lúc</th><th>Siêu dữ liệu phân tích</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($archives as $arc)
                            <tr>
                                <td>#{{ $arc->id }}</td>
                                <td><span class="mayhen_admin_tools_badge mayhen_admin_tools_badge--ok">{{ $arc->tool_key }}</span></td>
                                <td><strong>{{ $arc->title }}</strong><div style="font-size:11.5px;color:#64748B;">{{ $arc->resource_slug }}</div></td>
                                <td>{{ $arc->user_email ?? ('User #' . ($arc->user_id ?? 'guest')) }} ({{ strtoupper($arc->user_plan_tier ?? 'free') }})</td>
                                <td>{{ $arc->created_originally_at?->format('d/m/Y') }}</td>
                                <td>{{ $arc->archived_at?->format('d/m/Y H:i') }}</td>
                                <td style="font-size:11.5px;color:#334155;"><code>{{ json_encode($arc->analytics_summary_json, JSON_UNESCAPED_UNICODE) }}</code></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" style="color:#64748B;text-align:center;padding:24px;">Chưa có bản ghi nào quá hạn 6 tháng cần lưu trữ.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px;">{{ $archives->appends(request()->query())->links() }}</div>
        </section>
    @endif
</div>
@endsection
