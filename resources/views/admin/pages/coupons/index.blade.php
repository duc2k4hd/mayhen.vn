@extends('admin.layouts.master')

@section('title', 'Quản Lý Mã Giảm Giá & Ưu Đãi')
@section('page_title', 'Mã Giảm Giá & Chiến Dịch Ưu Đãi')

@section('content')
    <!-- KPI Header -->
    <div class="mayhen_admin_kpi_grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_kpi_card" style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mh-admin-muted); font-weight: 600;">Tổng Mã Ưu Đãi</div>
            <div style="font-size: 1.75rem; font-family: 'Playfair Display', serif; font-weight: 700; color: var(--mh-admin-charcoal); margin-top: 6px;">{{ $stats['total_coupons'] }}</div>
            <div style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-top: 4px;">Đã khởi tạo trong hệ thống</div>
        </div>

        <div class="mayhen_admin_kpi_card" style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mh-admin-muted); font-weight: 600;">Đang Có Hiệu Lực</div>
            <div style="font-size: 1.75rem; font-family: 'Playfair Display', serif; font-weight: 700; color: #1B5E20; margin-top: 6px;">{{ $stats['active_coupons'] }}</div>
            <div style="font-size: 0.8125rem; color: #2E7D32; margin-top: 4px;">Sẵn sàng áp dụng cho giỏ hàng</div>
        </div>

        <div class="mayhen_admin_kpi_card" style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mh-admin-muted); font-weight: 600;">Lượt Đã Sử Dụng</div>
            <div style="font-size: 1.75rem; font-family: 'Playfair Display', serif; font-weight: 700; color: #85182A; margin-top: 6px;">{{ number_format($stats['total_used_count']) }}</div>
            <div style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-top: 4px;">Tổng lượt khách đã dùng</div>
        </div>

        <div class="mayhen_admin_kpi_card" style="background: #fff; padding: 18px 20px; border-radius: 12px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mh-admin-muted); font-weight: 600;">Mã Đã Hết Hạn</div>
            <div style="font-size: 1.75rem; font-family: 'Playfair Display', serif; font-weight: 700; color: #D32F2F; margin-top: 6px;">{{ $stats['expired_coupons'] }}</div>
            <div style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-top: 4px;">Đã qua thời gian khuyến mãi</div>
        </div>
    </div>

    <!-- Notifications -->
    @if(session('success'))
        <div class="mayhen_admin_alert mayhen_admin_alert_success" style="margin-bottom: 20px; padding: 14px 18px; border-radius: 8px; background: #E8F5E9; border: 1px solid #C8E6C9; color: #2E7D32;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mayhen_admin_alert mayhen_admin_alert_danger" style="margin-bottom: 20px; padding: 14px 18px; border-radius: 8px; background: #FFEBEE; border: 1px solid #FFCDD2; color: #C62828;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Main Table Card -->
    <div class="mayhen_admin_card" style="background: #fff; border-radius: 14px; border: 1px solid var(--mh-admin-border-subtle); box-shadow: 0 4px 12px rgba(0,0,0,0.03); overflow: hidden;">
        <!-- Filter Toolbar -->
        <div style="padding: 18px 20px; border-bottom: 1px solid var(--mh-admin-border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <form action="{{ route('admin.coupons.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap; margin: 0; flex: 1;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo mã hoặc tên chiến dịch..." class="mayhen_admin_input" style="min-width: 260px; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.84375rem;">

                <select name="status" class="mayhen_admin_select" onchange="this.form.submit()" style="padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px; font-size: 0.84375rem;">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                    <option value="exhausted" {{ request('status') === 'exhausted' ? 'selected' : '' }}>Hết lượt dùng</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Vô hiệu hóa</option>
                </select>

                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="font-size: 0.8125rem;">
                    Lọc
                </button>

                @if(request()->hasAny(['q', 'status']))
                    <a href="{{ route('admin.coupons.index') }}" class="mayhen_admin_btn mayhen_admin_btn_text" style="font-size: 0.8125rem; color: var(--mh-admin-muted);">
                        Xóa bộ lọc
                    </a>
                @endif
            </form>

            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.coupons.export') }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="font-size: 0.8125rem;">
                    Xuất CSV
                </a>
                <button type="button" class="mayhen_admin_btn mayhen_admin_btn_primary" onclick="openCreateCouponModal()" style="font-size: 0.8125rem;">
                    + Tạo Mã Ưu Đãi
                </button>
            </div>
        </div>

        <!-- Table -->
        <div style="overflow-x: auto;">
            <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; font-size: 0.84375rem;">
                <thead>
                    <tr style="background: #FAF8F5; border-bottom: 1px solid var(--mh-admin-border-subtle); text-align: left; color: var(--mh-admin-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        <th style="padding: 12px 18px;">Mã Ưu Đãi</th>
                        <th style="padding: 12px 18px;">Chiến Dịch</th>
                        <th style="padding: 12px 18px;">Mức Giảm</th>
                        <th style="padding: 12px 18px;">Lượt Đã Dùng</th>
                        <th style="padding: 12px 18px;">Thời Gian</th>
                        <th style="padding: 12px 18px;">Trạng Thái</th>
                        <th style="padding: 12px 18px; text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                        @php $meta = $coupon->getStatusMeta(); @endphp
                        <tr style="border-bottom: 1px solid var(--mh-admin-border-subtle);">
                            <!-- Code -->
                            <td style="padding: 14px 18px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <span style="font-family: monospace; font-size: 0.95rem; font-weight: 700; color: #85182A; background: #FFF5F5; padding: 3px 8px; border-radius: 6px; border: 1px dashed #FFCDD2;">
                                        {{ $coupon->code }}
                                    </span>
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $coupon->code }}'); alert('Đã sao chép mã {{ $coupon->code }}!');" title="Sao chép mã" style="border: none; background: none; cursor: pointer; color: var(--mh-admin-muted); font-size: 0.8125rem;">
                                        📋
                                    </button>
                                </div>
                            </td>

                            <!-- Name -->
                            <td style="padding: 14px 18px;">
                                <strong style="color: var(--mh-admin-charcoal);">{{ $coupon->name }}</strong>
                                @if($coupon->min_order_amount > 0)
                                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                                        Đơn tối thiểu: {{ number_format($coupon->min_order_amount, 0, ',', '.') }}đ
                                    </div>
                                @endif
                            </td>

                            <!-- Discount -->
                            <td style="padding: 14px 18px;">
                                <div style="font-weight: 700; color: #1B5E20; font-size: 0.95rem;">
                                    {{ $coupon->formatted_discount }}
                                </div>
                                @if($coupon->discount_type === 'percent' && $coupon->max_discount_amount)
                                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                                        Tối đa: {{ number_format($coupon->max_discount_amount, 0, ',', '.') }}đ
                                    </div>
                                @endif
                            </td>

                            <!-- Usage Limit -->
                            <td style="padding: 14px 18px;">
                                <div>
                                    <strong>{{ $coupon->used_count }}</strong>
                                    <span style="color: var(--mh-admin-muted);">/ {{ $coupon->usage_limit ? number_format($coupon->usage_limit) : '∞' }}</span>
                                </div>
                                @if($coupon->usage_limit)
                                    @php $pct = min(100, round(($coupon->used_count / $coupon->usage_limit) * 100)); @endphp
                                    <div style="width: 100px; height: 5px; background: #E0E0E0; border-radius: 999px; margin-top: 4px; overflow: hidden;">
                                        <div style="width: {{ $pct }}%; height: 100%; background: {{ $pct >= 100 ? '#D32F2F' : '#2E7D32' }};"></div>
                                    </div>
                                @endif
                            </td>

                            <!-- Time validity -->
                            <td style="padding: 14px 18px; font-size: 0.78125rem; color: var(--mh-admin-muted);">
                                <div>Bắt đầu: {{ $coupon->starts_at ? $coupon->starts_at->format('d/m/Y') : 'Tức thì' }}</div>
                                <div>Hết hạn: {{ $coupon->expires_at ? $coupon->expires_at->format('d/m/Y') : 'Không hạn' }}</div>
                            </td>

                            <!-- Status Badge -->
                            <td style="padding: 14px 18px;">
                                <span class="mayhen_admin_badge {{ $meta['class'] }}" style="padding: 3px 8px; font-size: 0.75rem; border-radius: 4px;">
                                    {{ $meta['label'] }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td style="padding: 14px 18px; text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                                    <form action="{{ route('admin.coupons.toggle_active', $coupon) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 4px 8px; font-size: 0.75rem;">
                                            {{ $coupon->is_active ? 'Tạm Dừng' : 'Bật' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa mã ưu đãi {{ $coupon->code }}?');" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_outline" style="padding: 4px 8px; font-size: 0.75rem; color: #C62828; border-color: #FFCDD2;">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding: 40px; text-align: center; color: var(--mh-admin-muted);">
                                <div style="font-size: 2rem; margin-bottom: 8px;">🎟️</div>
                                <div>Không tìm thấy mã giảm giá nào phù hợp với bộ lọc.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($coupons->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid var(--mh-admin-border-subtle);">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tạo Mã Ưu Đãi Mới -->
    <div id="mayhen_coupon_modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #fff; width: 100%; max-width: 580px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); overflow: hidden;">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--mh-admin-border-subtle); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.3rem; margin: 0; color: var(--mh-admin-charcoal);">
                    Tạo Mã Giảm Giá Mới
                </h3>
                <button type="button" onclick="closeCreateCouponModal()" style="border: none; background: none; font-size: 1.25rem; cursor: pointer; color: var(--mh-admin-muted);">&times;</button>
            </div>

            <form action="{{ route('admin.coupons.store') }}" method="POST" style="padding: 24px;">
                @csrf

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 6px;">Mã Ưu Đãi (Viết hoa) <span style="color: #C62828;">*</span></label>
                        <input type="text" name="code" placeholder="VD: MAYHEN20, VIP2026" required style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px; text-transform: uppercase;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 6px;">Tên Chiến Dịch <span style="color: #C62828;">*</span></label>
                        <input type="text" name="name" placeholder="VD: Khai xuân may mắn" required style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 6px;">Loại Giảm Giá <span style="color: #C62828;">*</span></label>
                        <select name="discount_type" id="modal_discount_type" onchange="toggleDiscountInputs(this.value)" style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px;">
                            <option value="percent">Phần trăm (%)</option>
                            <option value="fixed">Số tiền cố định (VND)</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 6px;">Mức Giảm <span style="color: #C62828;">*</span></label>
                        <input type="number" name="discount_value" placeholder="VD: 20 (hoặc 50000)" min="1" required style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 6px;">Đơn Hàng Tối Thiểu (VND)</label>
                        <input type="number" name="min_order_amount" value="0" min="0" step="1000" style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px;">
                    </div>
                    <div id="modal_max_discount_container">
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 6px;">Mức Giảm Tối Đa (VND)</label>
                        <input type="number" name="max_discount_amount" placeholder="Để trống nếu không giới hạn" min="0" step="1000" style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 6px;">Giới Hạn Lượt Dùng</label>
                        <input type="number" name="usage_limit" placeholder="Vô hạn" min="1" style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 6px;">Bắt Đầu Lúc</label>
                        <input type="datetime-local" name="starts_at" style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.8125rem; font-weight: 600; margin-bottom: 6px;">Hết Hạn Lúc</label>
                        <input type="datetime-local" name="expires_at" style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px;">
                    </div>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 0.8125rem; font-weight: 600; cursor: pointer;">
                        <input type="checkbox" name="is_active" value="1" checked style="accent-color: #85182A; width: 16px; height: 16px;">
                        Kích hoạt mã giảm giá này ngay sau khi tạo
                    </label>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--mh-admin-border-subtle); padding-top: 16px;">
                    <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" onclick="closeCreateCouponModal()">Hủy Bỏ</button>
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">Khởi Tạo Mã</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateCouponModal() {
            document.getElementById('mayhen_coupon_modal').style.display = 'flex';
        }
        function closeCreateCouponModal() {
            document.getElementById('mayhen_coupon_modal').style.display = 'none';
        }
        function toggleDiscountInputs(type) {
            var maxContainer = document.getElementById('modal_max_discount_container');
            if (maxContainer) {
                maxContainer.style.display = (type === 'percent') ? 'block' : 'none';
            }
        }
    </script>
@endsection

