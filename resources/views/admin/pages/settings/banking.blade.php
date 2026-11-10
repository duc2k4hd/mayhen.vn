@extends('admin.layouts.master')

@section('title', 'Cấu Hình Ngân Hàng & VietQR — Mây Hẹn')
@section('page_title', 'Cấu Hình Ngân Hàng & Cổng Thanh Toán SePay')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/pages/settings.css') }}">
@endpush

@section('content')
    <!-- Intro Card -->
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 20px;">
        <h3 style="font-family: 'Playfair Display', serif; font-size: 1.25rem; margin-bottom: 4px; color: var(--mh-admin-charcoal);">
            Tài Khoản Thu Tiền & Tích Hợp Webhook SePay
        </h3>
        <p style="color: var(--mh-admin-muted); font-size: 0.84375rem; margin: 0;">
            Cấu hình số tài khoản ngân hàng nhận tiền mua gói thiệp cưới của Mây Hẹn. Hệ thống sẽ sinh mã VietQR tự động kèm mã đơn hàng và đối soát qua Webhook SePay.
        </p>
    </div>

    <div class="mayhen_admin_banking_layout">
        <!-- Form Settings -->
        <div class="mayhen_admin_card" style="padding: 28px;">
            <form action="{{ route('admin.settings.banking.update') }}" method="POST">
                @csrf

                <!-- Bank Selection -->
                <div style="margin-bottom: 20px;">
                    <label class="mayhen_admin_label" for="bank_select">Ngân Hàng Thụ Hưởng (VietQR NAPAS) <span style="color: #C62828;">*</span></label>
                    <select name="bank_code" id="bank_select" class="mayhen_admin_select" style="width: 100%;" required onchange="updateBankName(this)">
                        @foreach($supportedBanks as $bank)
                            <option 
                                value="{{ $bank['code'] }}" 
                                data-name="{{ $bank['short_name'] }}"
                                {{ ($bankingConfig['bank_code'] ?? '') === $bank['code'] ? 'selected' : '' }}
                            >
                                {{ $bank['short_name'] }} ({{ $bank['code'] }}) — {{ $bank['name'] }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="bank_name" id="bank_name_hidden" value="{{ $bankingConfig['bank_name'] ?? 'VietinBank' }}">
                    @error('bank_code')
                        <div style="color: #C62828; font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Account Number & Name -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div>
                        <label class="mayhen_admin_label" for="bank_account">Số Tài Khoản Ngân Hàng <span style="color: #C62828;">*</span></label>
                        <input 
                            type="text" 
                            id="bank_account" 
                            name="bank_account" 
                            value="{{ old('bank_account', $bankingConfig['bank_account'] ?? '') }}" 
                            class="mayhen_admin_input" 
                            placeholder="Ví dụ: 103870000000" 
                            required
                        >
                        @error('bank_account')
                            <div style="color: #C62828; font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div>
                        <label class="mayhen_admin_label" for="bank_account_name">Tên Chủ Tài Khoản (Không dấu) <span style="color: #C62828;">*</span></label>
                        <input 
                            type="text" 
                            id="bank_account_name" 
                            name="bank_account_name" 
                            value="{{ old('bank_account_name', $bankingConfig['bank_account_name'] ?? '') }}" 
                            class="mayhen_admin_input" 
                            placeholder="Ví dụ: MAY HEN ATELIER" 
                            required
                        >
                        @error('bank_account_name')
                            <div style="color: #C62828; font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- QR Template -->
                <div style="margin-bottom: 20px;">
                    <label class="mayhen_admin_label" for="qr_template">Kiểu Mẫu VietQR</label>
                    <select name="qr_template" id="qr_template" class="mayhen_admin_select" style="width: 100%;">
                        <option value="compact" {{ ($bankingConfig['qr_template'] ?? '') === 'compact' ? 'selected' : '' }}>Nhỏ gọn (Compact - Chuẩn cho mobile)</option>
                        <option value="compact2" {{ ($bankingConfig['qr_template'] ?? '') === 'compact2' ? 'selected' : '' }}>Nhỏ gọn viền xanh (Compact2)</option>
                        <option value="qr_only" {{ ($bankingConfig['qr_template'] ?? '') === 'qr_only' ? 'selected' : '' }}>Chỉ mã QR thuần (QR Only)</option>
                        <option value="print" {{ ($bankingConfig['qr_template'] ?? '') === 'print' ? 'selected' : '' }}>Khung in đầy đủ logo (Print)</option>
                    </select>
                </div>

                <!-- Webhook Secret Token -->
                <div style="margin-bottom: 28px; padding-top: 16px; border-top: 1px solid var(--mh-admin-border-subtle);">
                    <label class="mayhen_admin_label" for="webhook_secret">SePay Webhook API Secret Key</label>
                    <input 
                        type="password" 
                        id="webhook_secret" 
                        name="webhook_secret" 
                        value="{{ old('webhook_secret', $bankingConfig['webhook_secret'] ?? '') }}" 
                        class="mayhen_admin_input" 
                        placeholder="Nhập API Secret Key của SePay để xác thực chữ ký"
                    >
                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 4px;">
                        Webhook Endpoint nhận giao dịch: <code>{{ url('/api/payment/sepay/webhook') }}</code>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit" class="mayhen_admin_btn mayhen_admin_btn_primary" style="padding: 10px 24px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <span>Lưu Cấu Hình Ngân Hàng</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Live VietQR Preview Card -->
        <div class="mayhen_admin_qr_preview_card">
            <h4 style="font-size: 1rem; font-weight: 700; color: var(--mh-admin-charcoal); margin-bottom: 4px;">
                Xem Trước VietQR Mẫu
            </h4>
            <div style="font-size: 0.8125rem; color: var(--mh-admin-muted); margin-bottom: 16px;">
                Mô phỏng mã QR thanh toán hiển thị cho khách mua gói
            </div>

            @php
                $demoBank = $bankingConfig['bank_code'] ?? 'ICB';
                $demoAcc = $bankingConfig['bank_account'] ?? '103870000000';
                $demoName = urlencode($bankingConfig['bank_account_name'] ?? 'MAY HEN ATELIER');
                $demoTpl = $bankingConfig['qr_template'] ?? 'compact';
                $qrUrl = "https://img.vietqr.io/image/{$demoBank}-{$demoAcc}-{$demoTpl}.png?accountName={$demoName}&amount=149000&addInfo=MHDEMO12345";
            @endphp

            <div class="mayhen_admin_qr_image_box">
                <img src="{{ $qrUrl }}" alt="VietQR Demo Preview" loading="lazy" onerror="this.onerror=null; this.src='{{ asset('assets/client/images/vietqr-placeholder.png') }}';">
            </div>

            <div style="font-weight: 700; font-size: 0.9375rem; color: var(--mh-admin-charcoal);">
                {{ $bankingConfig['bank_account_name'] ?? 'MAY HEN ATELIER' }}
            </div>
            <div style="font-family: monospace; font-size: 0.875rem; color: var(--mh-admin-gold); font-weight: 600; margin: 4px 0;">
                STK: {{ $bankingConfig['bank_account'] ?? '—' }}
            </div>
            <div style="font-size: 0.75rem; color: var(--mh-admin-muted);">
                Ngân hàng: {{ $bankingConfig['bank_name'] ?? '—' }}
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function updateBankName(select) {
                const opt = select.options[select.selectedIndex];
                const bankName = opt.getAttribute('data-name');
                if (bankName) {
                    document.getElementById('bank_name_hidden').value = bankName;
                }
            }
        </script>
    @endpush
@endsection

