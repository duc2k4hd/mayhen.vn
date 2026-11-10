@extends('admin.layouts.master')

@section('title', 'Quản Lý Gói Cước & Đăng Ký — Atelier Command Center')
@section('page_title', 'Gói Cước & Bảng Giá Dịch Vụ')

@section('content')
    <!-- KPI Header -->
    <div class="mayhen_admin_kpi_grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_kpi_card" style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mh-admin-muted); font-weight: 600;">Tổng Số Gói Cước</div>
            <div style="font-size: 1.75rem; font-family: 'Playfair Display', serif; font-weight: 700; color: var(--mh-admin-charcoal); margin-top: 6px;">{{ $stats['total_plans'] }}</div>
            <div style="font-size: 0.8125rem; color: #2E7D32; margin-top: 4px;">{{ $stats['active_plans'] }} gói đang mở bán</div>
        </div>

        <div class="mayhen_admin_kpi_card" style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mh-admin-muted); font-weight: 600;">Tổng Đơn Hàng Thành Công</div>
            <div style="font-size: 1.75rem; font-family: 'Playfair Display', serif; font-weight: 700; color: #1B5E20; margin-top: 6px;">{{ number_format($stats['total_paid_orders']) }}</div>
            <div style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-top: 4px;">Đã hoàn tất thanh toán</div>
        </div>

        <div class="mayhen_admin_kpi_card" style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mh-admin-muted); font-weight: 600;">Doanh Thu Đăng Ký</div>
            <div style="font-size: 1.75rem; font-family: 'Playfair Display', serif; font-weight: 700; color: #85182A; margin-top: 6px;">{{ number_format($stats['total_revenue'], 0, ',', '.') }}đ</div>
            <div style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-top: 4px;">Giá trị thực tế thu từ gói</div>
        </div>

        <div class="mayhen_admin_kpi_card" style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02); display: flex; flex-direction: column; justify-content: center; align-items: flex-start;">
            <div style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 8px;">Xem bảng giá công khai của khách hàng:</div>
            <a href="{{ route('pricing') }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn_outline" style="font-size: 0.8125rem; width: 100%; text-align: center;">
                Xem Landing Page Bảng Giá &nearr;
            </a>
        </div>
    </div>

    <!-- Alert / Notice -->
    @if(session('success'))
        <div class="mayhen_admin_alert mayhen_admin_alert_success" style="margin-bottom: 20px; padding: 14px 18px; border-radius: 8px; background: #E8F5E9; border: 1px solid #C8E6C9; color: #2E7D32;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Plans Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
        @foreach($plans as $plan)
            <div class="mayhen_admin_card" style="background: #fff; border-radius: 14px; border: {{ $plan->slug === 'plus' ? '2px solid var(--mh-admin-gold, #C5A880)' : '1px solid var(--mh-admin-border-subtle)' }}; padding: 24px; position: relative; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                @if($plan->slug === 'plus')
                    <div style="position: absolute; top: -12px; right: 20px; background: var(--mh-admin-gold, #C5A880); color: #fff; font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; padding: 3px 12px; border-radius: 999px;">
                        Gói Trọng Tâm
                    </div>
                @endif

                <div>
                    <!-- Header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                        <div>
                            <span class="mayhen_admin_badge {{ $plan->is_active ? 'mayhen_admin_badge_success' : 'mayhen_admin_badge_warning' }}" style="display: inline-block; padding: 2px 8px; font-size: 0.6875rem; border-radius: 4px; margin-bottom: 6px;">
                                {{ $plan->is_active ? 'Đang Mở Bán' : 'Tạm Dừng' }}
                            </span>
                            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.4rem; color: var(--mh-admin-charcoal); margin: 0;">
                                {{ $plan->name }}
                            </h3>
                            <code style="font-size: 0.75rem; color: var(--mh-admin-muted);">slug: {{ $plan->slug }}</code>
                        </div>

                        <div style="text-align: right;">
                            <div style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #85182A;">
                                {{ $plan->formatted_price }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                                {{ $plan->price == 0 ? 'Trải nghiệm' : 'Thanh toán 1 lần' }}
                            </div>
                        </div>
                    </div>

                    <!-- Meta specs -->
                    <div style="background: #FAF8F5; border-radius: 8px; padding: 12px 14px; margin-bottom: 18px; font-size: 0.8125rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: var(--mh-admin-muted);">Thời hạn lưu trữ:</span>
                            <strong style="color: var(--mh-admin-charcoal);">{{ $plan->duration_days }} ngày</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="color: var(--mh-admin-muted);">Số thiệp tối đa:</span>
                            <strong style="color: var(--mh-admin-charcoal);">{{ $plan->max_invitations }} thiệp</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--mh-admin-muted);">Đơn hàng đã thanh toán:</span>
                            <strong style="color: #1B5E20;">{{ number_format($plan->paid_orders_count) }} đơn</strong>
                        </div>
                    </div>

                    <!-- Features checklist -->
                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mh-admin-muted); margin-bottom: 10px;">
                            Quyền lợi cấu hình ({{ is_array($plan->features) ? count($plan->features) : 0 }} mục)
                        </div>
                        <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.8125rem; color: #4A453E;">
                            @if(is_array($plan->features))
                                @foreach(array_slice($plan->features, 0, 5) as $feature)
                                    <li style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                        <span style="color: #2E7D32; font-weight: bold;">✓</span>
                                        <span>{{ $feature }}</span>
                                    </li>
                                @endforeach
                                @if(count($plan->features) > 5)
                                    <li style="color: var(--mh-admin-muted); font-size: 0.75rem; font-style: italic; margin-top: 4px;">
                                        + thêm {{ count($plan->features) - 5 }} đặc quyền khác...
                                    </li>
                                @endif
                            @else
                                <li style="color: var(--mh-admin-muted); font-style: italic;">Chưa cấu hình danh sách quyền lợi.</li>
                            @endif
                        </ul>
                    </div>
                </div>

                <!-- Card Actions -->
                <div style="border-top: 1px solid var(--mh-admin-border-subtle); padding-top: 16px; display: flex; gap: 10px; justify-content: flex-end;">
                    <form action="{{ route('admin.plans.toggle_active', $plan) }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="font-size: 0.8125rem;">
                            {{ $plan->is_active ? 'Tạm Dừng' : 'Mở Bán' }}
                        </button>
                    </form>

                    <a href="{{ route('admin.plans.edit', $plan) }}" class="mayhen_admin_btn mayhen_admin_btn_primary" style="font-size: 0.8125rem;">
                        Chỉnh Sửa
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endsection

