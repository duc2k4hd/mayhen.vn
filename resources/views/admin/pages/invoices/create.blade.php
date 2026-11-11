@extends('admin.layouts.master')

@section('title', 'Lập Hóa Đơn Điện Tử Mới')
@section('page_title', 'Lập Hóa Đơn Điện Tử & Phiếu Thu VAT Mới')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/settings.css') }}">
@endpush

@section('content')
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 20px 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <a href="{{ route('admin.invoices.index') }}" style="color: #8C6B37; text-decoration: none; font-size: 0.8125rem; display: inline-flex; align-items: center; gap: 4px; margin-bottom: 6px;">
                    ← Quay lại danh sách hóa đơn
                </a>
                <h3 style="font-family: 'Playfair Display', serif; font-size: 1.35rem; margin: 0; color: var(--mh-admin-charcoal, #2B2927);">
                    Lập Yêu Cầu Hóa Đơn Cho Đơn Hàng
                </h3>
            </div>
        </div>
    </div>

    <!-- Alert Thông báo Session -->
    @if(session('error'))
        <div class="mayhen_admin_alert mayhen_admin_alert_danger" style="margin-bottom: 20px; padding: 12px 18px; border-radius: 8px; background: #FFEBEE; color: #B71C1C; border: 1px solid #FFCDD2;">
            <strong>✕ Lỗi:</strong> {{ session('error') }}
        </div>
    @endif

    <div class="mayhen_admin_card" style="padding: 28px; max-width: 800px; margin: 0 auto;">
        <form action="{{ route('admin.invoices.store') }}" method="POST">
            @csrf

            <!-- Chọn Đơn Hàng -->
            <div style="margin-bottom: 20px;">
                <label class="mayhen_admin_label" for="order_select" style="font-weight: 600; margin-bottom: 6px; display: block;">
                    Đơn Hàng Cần Xuất Hóa Đơn <span style="color: #C62828;">*</span>
                </label>
                @if($order)
                    <div style="padding: 12px 16px; background: #FAF9F6; border-radius: 8px; border: 1px solid #EBE7DF; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <strong style="color: var(--mh-admin-charcoal, #2B2927);">Đơn hàng #{{ $order->order_code }}</strong>
                            <span style="color: var(--mh-admin-muted, #7A756D); margin-left: 8px;">({{ $order->plan->name ?? 'Gói cước' }})</span>
                            <div style="font-size: 0.78125rem; color: #8C6B37; margin-top: 2px;">
                                Khách: {{ $order->user->name ?? 'N/A' }} ({{ $order->user->email ?? 'N/A' }})
                            </div>
                        </div>
                        <div style="font-family: monospace; font-weight: 600; font-size: 1rem;">
                            {{ number_format($order->amount, 0, ',', '.') }} đ
                        </div>
                    </div>
                    <input type="hidden" name="order_id" value="{{ $order->id }}">
                @else
                    <select name="order_id" id="order_select" class="mayhen_admin_select" style="width: 100%;" required>
                        <option value="">-- Chọn đơn hàng đã thanh toán chưa có hóa đơn --</option>
                        @foreach($paidOrders as $po)
                            <option value="{{ $po->id }}">
                                #{{ $po->order_code }} — {{ $po->user->name ?? 'Khách' }} ({{ number_format($po->amount, 0, ',', '.') }} đ) — {{ $po->created_at->format('d/m/Y') }}
                            </option>
                        @endforeach
                    </select>
                @endif
                @error('order_id')
                    <div style="color: #C62828; font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Thông Tin Người Nhận -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label class="mayhen_admin_label" for="customer_name" style="font-weight: 600; margin-bottom: 4px; display: block;">
                        Tên Khách Hàng / Đại Diện <span style="color: #C62828;">*</span>
                    </label>
                    <input type="text" 
                           name="customer_name" 
                           id="customer_name" 
                           class="mayhen_admin_input" 
                           style="width: 100%;" 
                           value="{{ old('customer_name', $order->user->name ?? '') }}" 
                           placeholder="VD: Nguyễn Văn An" 
                           required>
                    @error('customer_name')
                        <div style="color: #C62828; font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="mayhen_admin_label" for="customer_email" style="font-weight: 600; margin-bottom: 4px; display: block;">
                        Email Nhận Hóa Đơn Điện Tử <span style="color: #C62828;">*</span>
                    </label>
                    <input type="email" 
                           name="customer_email" 
                           id="customer_email" 
                           class="mayhen_admin_input" 
                           style="width: 100%;" 
                           value="{{ old('customer_email', $order->user->email ?? '') }}" 
                           placeholder="VD: an.nguyen@example.com" 
                           required>
                    @error('customer_email')
                        <div style="color: #C62828; font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="mayhen_admin_label" for="customer_phone" style="font-weight: 600; margin-bottom: 4px; display: block;">
                    Số Điện Thoại Liên Hệ
                </label>
                <input type="text" 
                       name="customer_phone" 
                       id="customer_phone" 
                       class="mayhen_admin_input" 
                       style="width: 100%;" 
                       value="{{ old('customer_phone') }}" 
                       placeholder="VD: 0988.776.xxx">
            </div>

            <!-- Thông Tin Xuất Hóa Đơn Công Ty (Tùy Chọn) -->
            <div style="padding: 16px; background: #FAF9F6; border-radius: 8px; border: 1px solid #EBE7DF; margin-bottom: 20px;">
                <div style="font-weight: 600; color: var(--mh-admin-charcoal, #2B2927); font-size: 0.875rem; margin-bottom: 12px;">
                    🏢 Thông Tin Doanh Nghiệp (Dành cho khách hàng cần xuất HĐ Công ty)
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 12px;">
                    <div>
                        <label class="mayhen_admin_label" for="company_name" style="font-size: 0.78125rem; margin-bottom: 2px; display: block;">
                            Tên Đơn Vị / Công Ty
                        </label>
                        <input type="text" 
                               name="company_name" 
                               id="company_name" 
                               class="mayhen_admin_input" 
                               style="width: 100%;" 
                               value="{{ old('company_name') }}" 
                               placeholder="VD: CÔNG TY TNHH DỊCH VỤ SỰ KIỆN MAY HEN">
                    </div>
                    <div>
                        <label class="mayhen_admin_label" for="tax_id" style="font-size: 0.78125rem; margin-bottom: 2px; display: block;">
                            Mã Số Thuế (MST)
                        </label>
                        <input type="text" 
                               name="tax_id" 
                               id="tax_id" 
                               class="mayhen_admin_input" 
                               style="width: 100%;" 
                               value="{{ old('tax_id') }}" 
                               placeholder="VD: 0109888888">
                    </div>
                </div>

                <div>
                    <label class="mayhen_admin_label" for="company_address" style="font-size: 0.78125rem; margin-bottom: 2px; display: block;">
                        Địa Chỉ Xuất Hóa Đơn Theo Giấy ĐKKD
                    </label>
                    <input type="text" 
                           name="company_address" 
                           id="company_address" 
                           class="mayhen_admin_input" 
                           style="width: 100%;" 
                           value="{{ old('company_address') }}" 
                           placeholder="VD: Số 88 Phố Huế, Quận Hai Bà Trưng, Hà Nội">
                </div>
            </div>

            <!-- Cấu Hình Thuế Suất VAT -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label class="mayhen_admin_label" for="vat_rate" style="font-weight: 600; margin-bottom: 4px; display: block;">
                        Thuế Suất VAT Áp Dụng
                    </label>
                    <select name="vat_rate" id="vat_rate" class="mayhen_admin_select" style="width: 100%;">
                        <option value="8" selected>8% (Thuế suất ưu đãi hiện hành)</option>
                        <option value="10">10% (Thuế suất chuẩn)</option>
                        <option value="0">0% (Không chịu thuế / Hóa đơn bán hàng)</option>
                    </select>
                </div>

                <div>
                    <label class="mayhen_admin_label" for="notes" style="font-weight: 600; margin-bottom: 4px; display: block;">
                        Ghi Chú Nội Bộ
                    </label>
                    <input type="text" 
                           name="notes" 
                           id="notes" 
                           class="mayhen_admin_input" 
                           style="width: 100%;" 
                           value="{{ old('notes') }}" 
                           placeholder="Ghi chú nội bộ cho kế toán...">
                </div>
            </div>

            <!-- Tùy Chọn Phát Hành Ngay -->
            <div style="margin-bottom: 24px; padding: 12px 16px; background: #F4F8F4; border-radius: 8px; border: 1px solid #D2E7D2;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 0.84375rem; color: #1B5E20; font-weight: 500;">
                    <input type="checkbox" name="issue_now" value="1" checked style="width: 16px; height: 16px;">
                    <span>Phát hành hóa đơn chính thức ngay (Tự động cấp số HĐĐT và gửi email)</span>
                </label>
            </div>

            <!-- Nút Lưu -->
            <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid #F0ECE4; padding-top: 20px;">
                <a href="{{ route('admin.invoices.index') }}" class="mayhen_admin_btn" style="background: #F4F1EA; border: 1px solid #D6D0C4; color: #5A554E; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-size: 0.875rem;">
                    Hủy Bỏ
                </a>
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="background: var(--mh-admin-primary, #A38048); color: #FFFFFF; border: none; padding: 10px 24px; border-radius: 6px; font-weight: 500; font-size: 0.875rem; cursor: pointer;">
                    Xác Nhận Lập Hóa Đơn
                </button>
            </div>
        </form>
    </div>
@endsection
