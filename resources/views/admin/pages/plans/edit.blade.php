@extends('admin.layouts.master')

@section('title', 'Chỉnh Sửa Gói Cước: ' . $plan->name)
@section('page_title', 'Chỉnh Sửa Gói Cước')

@section('content')
    <div style="max-width: 800px; margin: 0 auto;">
        <!-- Top Back bar -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <a href="{{ route('admin.plans.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline" style="font-size: 0.8125rem;">
                &larr; Quay lại danh sách gói
            </a>
            <span class="mayhen_admin_badge {{ $plan->is_active ? 'mayhen_admin_badge_success' : 'mayhen_admin_badge_warning' }}">
                {{ $plan->is_active ? 'Đang Mở Bán' : 'Tạm Dừng' }}
            </span>
        </div>

        @if($errors->any())
            <div class="mayhen_admin_alert mayhen_admin_alert_danger" style="margin-bottom: 20px; padding: 14px 18px; border-radius: 8px; background: #FFEBEE; border: 1px solid #FFCDD2; color: #C62828;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card Form -->
        <div class="mayhen_admin_card" style="background: #fff; border-radius: 14px; border: 1px solid var(--mh-admin-border-subtle); padding: 28px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
            <div style="border-bottom: 1px solid var(--mh-admin-border-subtle); padding-bottom: 16px; margin-bottom: 24px;">
                <h3 style="font-size: 1.4rem; color: var(--mh-admin-charcoal); margin: 0 0 6px;">
                    Cấu Hình Gói Dịch Vụ: {{ $plan->name }}
                </h3>
                <p style="color: var(--mh-admin-muted); font-size: 0.8125rem; margin: 0;">
                    Bảo toàn Snapshot Lịch Sử: Việc thay đổi giá bán hoặc thời hạn ở đây chỉ áp dụng cho các đơn hàng mới, không làm thay đổi số tiền đối soát của {{ number_format($plan->paid_orders_count) }} đơn hàng cũ đã thanh toán.
                </p>
            </div>

            <form action="{{ route('admin.plans.update', $plan) }}" method="POST">
                @csrf
                @method('PUT')

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <!-- Tên gói -->
                    <div>
                        <label class="mayhen_admin_label" style="display: block; font-size: 0.8125rem; font-weight: 600; color: var(--mh-admin-charcoal); margin-bottom: 6px;">
                            Tên Gói Cước <span style="color: #C62828;">*</span>
                        </label>
                        <input type="text" name="name" class="mayhen_admin_input" value="{{ old('name', $plan->name) }}" required style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px;">
                    </div>

                    <!-- Slug định danh -->
                    <div>
                        <label class="mayhen_admin_label" style="display: block; font-size: 0.8125rem; font-weight: 600; color: var(--mh-admin-muted); margin-bottom: 6px;">
                            Mã Định Danh (Slug bất biến)
                        </label>
                        <input type="text" value="{{ $plan->slug }}" disabled style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border-subtle); border-radius: 8px; background: #FAF8F5; color: var(--mh-admin-muted); cursor: not-allowed;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <!-- Giá bán VND -->
                    <div>
                        <label class="mayhen_admin_label" style="display: block; font-size: 0.8125rem; font-weight: 600; color: var(--mh-admin-charcoal); margin-bottom: 6px;">
                            Giá Bán (VND) <span style="color: #C62828;">*</span>
                        </label>
                        <input type="number" name="price" class="mayhen_admin_input" value="{{ old('price', $plan->price) }}" min="0" step="1000" required style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px;">
                        <small style="font-size: 0.75rem; color: var(--mh-admin-muted);">Nhập 0 nếu là gói miễn phí.</small>
                    </div>

                    <!-- Thời hạn ngày -->
                    <div>
                        <label class="mayhen_admin_label" style="display: block; font-size: 0.8125rem; font-weight: 600; color: var(--mh-admin-charcoal); margin-bottom: 6px;">
                            Thời Hạn (Ngày) <span style="color: #C62828;">*</span>
                        </label>
                        <input type="number" name="duration_days" class="mayhen_admin_input" value="{{ old('duration_days', $plan->duration_days) }}" min="0" required style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px;">
                        <small style="font-size: 0.75rem; color: var(--mh-admin-muted);">365 = 1 năm, 3650 = 10 năm.</small>
                    </div>

                    <!-- Số thiệp tối đa -->
                    <div>
                        <label class="mayhen_admin_label" style="display: block; font-size: 0.8125rem; font-weight: 600; color: var(--mh-admin-charcoal); margin-bottom: 6px;">
                            Số Thiệp Tối Đa <span style="color: #C62828;">*</span>
                        </label>
                        <input type="number" name="max_invitations" class="mayhen_admin_input" value="{{ old('max_invitations', $plan->max_invitations) }}" min="1" required style="width: 100%; padding: 10px 12px; border: 1px solid var(--mh-admin-border); border-radius: 8px;">
                    </div>
                </div>

                <!-- Features Array -->
                <div style="margin-bottom: 24px;">
                    <label class="mayhen_admin_label" style="display: block; font-size: 0.8125rem; font-weight: 600; color: var(--mh-admin-charcoal); margin-bottom: 8px;">
                        Danh Sách Đặc Quyền & Tính Năng Gói (Mỗi dòng 1 tính năng)
                    </label>
                    <div id="mayhen_features_container" style="display: flex; flex-direction: column; gap: 8px;">
                        @php
                            $features = old('features', $plan->features ?? []);
                            if (empty($features)) $features = [''];
                        @endphp
                        @foreach($features as $idx => $feat)
                            <div class="mayhen_feature_row" style="display: flex; gap: 10px;">
                                <input type="text" name="features[]" class="mayhen_admin_input" value="{{ $feat }}" placeholder="Ví dụ: Nhạc nền đám cưới độc quyền" style="flex: 1; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px;">
                                <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" onclick="this.parentElement.remove()" style="padding: 6px 12px; color: #C62828; border-color: #FFCDD2;">✕</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" onclick="addFeatureRow()" style="margin-top: 10px; font-size: 0.8125rem;">
                        + Thêm đặc quyền
                    </button>
                </div>

                <!-- Active switch -->
                <div style="margin-bottom: 28px; background: #FAF8F5; padding: 14px 18px; border-radius: 8px; display: flex; align-items: center; gap: 12px;">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #85182A; cursor: pointer;">
                    <label for="is_active" style="font-size: 0.84375rem; font-weight: 600; color: var(--mh-admin-charcoal); cursor: pointer;">
                        Kích hoạt mở bán gói cước này công khai cho khách hàng lựa chọn
                    </label>
                </div>

                <!-- Submit actions -->
                <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--mh-admin-border-subtle); padding-top: 20px;">
                    <a href="{{ route('admin.plans.index') }}" class="mayhen_admin_btn mayhen_admin_btn_outline">
                        Hủy Bỏ
                    </a>
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary">
                        Lưu Cấu Hình Gói
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function addFeatureRow() {
            var container = document.getElementById('mayhen_features_container');
            var div = document.createElement('div');
            div.className = 'mayhen_feature_row';
            div.style.cssText = 'display: flex; gap: 10px;';
            div.innerHTML = '<input type="text" name="features[]" class="mayhen_admin_input" placeholder="Nhập tên đặc quyền..." style="flex: 1; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px;">' +
                            '<button type="button" class="mayhen_admin_btn mayhen_admin_btn_outline" onclick="this.parentElement.remove()" style="padding: 6px 12px; color: #C62828; border-color: #FFCDD2;">✕</button>';
            container.appendChild(div);
        }
    </script>
@endsection

