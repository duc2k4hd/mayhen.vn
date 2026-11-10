@extends('client.pages.account.layout')

@php
    $groom = $wedding->groom_name ?? 'Chú Rể';
    $bride = $wedding->bride_name ?? 'Cô Dâu';
    $pageTitle = "Sổ Mừng Cưới: {$groom} & {$bride}";
@endphp

@section('account_breadcrumb')
    <a href="{{ route('account.invitations') }}" style="color: inherit; text-decoration: none;">Thiệp cưới</a>
    <span class="mayhen_account_breadcrumbs_sep">/</span>
    <span>Sổ mừng cưới</span>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/account.css') }}?v={{ file_exists(public_path('assets/client/css/pages/account.css')) ? filemtime(public_path('assets/client/css/pages/account.css')) : '1.0' }}">
@endpush

@section('account_content')
    <!-- Header Thông Tin & Thao Tác -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px;">
        <div>
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 26px; font-weight: 600; color: #1F1D1A; margin: 0;">
                Sổ Mừng Cưới & Quản Lý Tiền Mừng
            </h2>
            <p style="font-size: 13px; color: #7A726A; margin: 4px 0 0 0;">
                Hôn lễ của <strong>{{ $groom }} & {{ $bride }}</strong> • Ghi chép và theo dõi toàn bộ quà mừng cưới từ quan khách hai họ.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <button type="button" id="mayhen-open-banking-btn" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                <span>⚙️ Cài Đặt Tài Khoản & QR</span>
            </button>
            <a href="{{ route('account.gifts.print', array_filter(['id' => $invitation->id, 'q' => $filterSearch, 'method' => $filterMethod, 'group' => $filterGroup, 'ack' => $filterAck])) }}" target="_blank" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                <span>In Sổ Mừng Cưới (A4)</span>
            </a>
            <a href="{{ route('account.gifts.export', array_filter(['id' => $invitation->id, 'q' => $filterSearch, 'method' => $filterMethod, 'group' => $filterGroup, 'ack' => $filterAck])) }}" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Xuất File CSV (Excel)</span>
            </a>
            <button type="button" onclick="document.getElementById('add-gift-box').scrollIntoView({ behavior: 'smooth' }); document.getElementById('gift-sender-name').focus();" class="mayhen_account_btn mayhen_account_btn_gold mayhen_account_btn_sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Ghi Nhận Mừng Cưới</span>
            </button>
        </div>
    </div>

    <!-- Banner Trạng Thái Hiển Thị QR Nhận Quà (Section 35 PRD V3) -->
    <div class="mayhen_account_form_card" style="margin-bottom: 24px; padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; background: #FAF8F5; border: 1px solid #EFE8DD;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="width: 42px; height: 42px; border-radius: 10px; background: #EFE8DD; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                🎁
            </div>
            <div>
                <div style="font-size: 15px; font-weight: 600; color: #1F1D1A; display: flex; align-items: center; gap: 8px;">
                    <span>Hộp Mừng Cưới & Mã VietQR Công Khai:</span>
                    @if($isGiftQrEnabled)
                        <span class="mayhen_account_status_badge published" style="font-size: 11px;">Đang Hiển Thị</span>
                    @else
                        <span class="mayhen_account_status_badge locked" style="font-size: 11px;">Đang Tạm Khóa / Ẩn</span>
                    @endif
                </div>
                <div style="font-size: 13px; color: #7A726A; margin-top: 2px;">
                    @if($isGiftQrEnabled)
                        Mã VietQR và số tài khoản ngân hàng đang hiển thị công khai trên thiệp cưới. Quan khách có thể quét mã gửi quà mừng trực tiếp.
                    @else
                        Mã QR và thông tin tài khoản đang được ẩn hoàn toàn khỏi thiệp cưới công khai (Bảo vệ riêng tư hôn lễ).
                    @endif
                </div>
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <form action="{{ route('account.gifts.toggle_qr', $invitation->id) }}" method="POST" style="margin: 0;">
                @csrf
                @if($isGiftQrEnabled)
                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" style="white-space: nowrap; color: #C62828; border-color: #FFCDD2;">
                        🔒 Tạm Khóa / Ẩn QR Khỏi Thiệp
                    </button>
                @else
                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary mayhen_account_btn_sm" style="white-space: nowrap;">
                        🔓 Bật Hiển Thị QR Công Khai
                    </button>
                @endif
            </form>
        </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <div class="mayhen_account_kpi_grid" style="margin-bottom: 28px;">
        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Tổng Tiền Mừng Cưới</span>
                <svg class="mayhen_account_kpi_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                    <line x1="2" y1="10" x2="22" y2="10"></line>
                </svg>
            </div>
            <div class="mayhen_account_kpi_val" style="color: #9C7A4A;">{{ number_format($totalAmount, 0, ',', '.') }} đ</div>
            <div class="mayhen_account_kpi_desc">Toàn bộ quà mừng ghi nhận</div>
        </div>

        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Số Lượt Mừng Cưới</span>
                <svg class="mayhen_account_kpi_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="mayhen_account_kpi_val">{{ number_format($totalGiftsCount, 0, ',', '.') }}</div>
            <div class="mayhen_account_kpi_desc">Quan khách đã chúc mừng</div>
        </div>

        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Mừng Trung Bình</span>
                <svg class="mayhen_account_kpi_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
            </div>
            <div class="mayhen_account_kpi_val">{{ number_format($avgAmount, 0, ',', '.') }} đ</div>
            <div class="mayhen_account_kpi_desc">Trung bình trên mỗi khách</div>
        </div>

        <div class="mayhen_account_kpi_card">
            <div class="mayhen_account_kpi_header">
                <span class="mayhen_account_kpi_title">Cơ Cấu Mừng Cưới</span>
                <svg class="mayhen_account_kpi_icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 2v20"></path>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
            </div>
            <div style="font-size: 13px; color: #1F1D1A; margin-top: 6px; line-height: 1.6;">
                <div>CK VietQR: <strong>{{ number_format($bankAmount, 0, ',', '.') }} đ</strong></div>
                <div>Tiền mặt: <strong>{{ number_format($cashAmount, 0, ',', '.') }} đ</strong></div>
            </div>
        </div>
    </div>

    <!-- Khối Ghi Nhận Mừng Cưới Nhanh (Add Gift Card) -->
    <div id="add-gift-box" class="mayhen_account_section" style="margin-bottom: 28px;">
        <div class="mayhen_account_section_header">
            <h3 class="mayhen_account_section_title">Ghi Nhận Khoản Mừng Cưới Mới</h3>
            <p class="mayhen_account_section_desc">Nhập tiền mừng khi mở phong bì cưới hoặc xác nhận chuyển khoản ngân hàng.</p>
        </div>

        <form action="{{ route('account.gifts.store', $invitation->id) }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 16px;">
                <div>
                    <label class="mayhen_account_form_label" for="gift-sender-name">Tên người mừng cưới *</label>
                    <input type="text" name="sender_name" id="gift-sender-name" required class="mayhen_account_form_input" placeholder="Ví dụ: Bác Ba, Anh Tuấn..." list="guest-names-datalist">
                    <datalist id="guest-names-datalist">
                        @foreach($guests as $gst)
                            <option value="{{ $gst->name }}">{{ $gst->group_name ? "({$gst->group_name})" : '' }}</option>
                        @endforeach
                    </datalist>
                </div>

                <div>
                    <label class="mayhen_account_form_label" for="gift-sender-group">Nhóm quan khách</label>
                    <input type="text" name="sender_group" id="gift-sender-group" class="mayhen_account_form_input" placeholder="Bạn Bè, Họ Nhà Trai, Đồng Nghiệp..." list="group-names-datalist">
                    <datalist id="group-names-datalist">
                        <option value="Bạn Bè">
                        <option value="Họ Nhà Trai">
                        <option value="Họ Nhà Gái">
                        <option value="Đồng Nghiệp">
                        <option value="Bạn Đại Học">
                        <option value="Bạn Cấp 3">
                    </datalist>
                </div>

                <div>
                    <label class="mayhen_account_form_label" for="gift-payment-method">Hình thức mừng cưới *</label>
                    <select name="payment_method" id="gift-payment-method" required class="mayhen_account_form_input">
                        <option value="cash">Tiền mặt / Phong bì</option>
                        <option value="bank_transfer">Chuyển khoản VietQR</option>
                        <option value="other">Quà mừng hiện vật</option>
                    </select>
                </div>
            </div>

            <!-- Số tiền & Các nút chọn nhanh mệnh giá -->
            <div style="margin-bottom: 16px;">
                <label class="mayhen_account_form_label" for="gift-amount">Số tiền mừng cưới (VNĐ) *</label>
                <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                    <div style="max-width: 280px; width: 100%;">
                        <input type="number" name="amount" id="gift-amount" required min="0" step="10000" class="mayhen_account_form_input" placeholder="500000" value="500000">
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" onclick="document.getElementById('gift-amount').value = 500000;">500.000 đ</button>
                        <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" onclick="document.getElementById('gift-amount').value = 1000000;">1.000.000 đ</button>
                        <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" onclick="document.getElementById('gift-amount').value = 2000000;">2.000.000 đ</button>
                        <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" onclick="document.getElementById('gift-amount').value = 5000000;">5.000.000 đ</button>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 16px; align-items: center;">
                <div>
                    <label class="mayhen_account_form_label" for="gift-notes">Lời chúc mừng hoặc ghi chú đính kèm</label>
                    <input type="text" name="notes" id="gift-notes" class="mayhen_account_form_input" placeholder="Chúc hai bạn trăm năm hạnh phúc...">
                </div>
                <div style="padding-top: 20px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: #1F1D1A;">
                        <input type="checkbox" name="is_acknowledged" value="1" style="width: 18px; height: 18px; accent-color: #C5A880;">
                        <span>Đã gửi lời cảm ơn tới người mừng</span>
                    </label>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end;">
                <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Lưu Vào Sổ Mừng Cưới</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Toolbar Tìm Kiếm & Lọc Danh Sách -->
    <div style="background: #FAF8F5; border: 1px solid #EAE3D9; border-radius: 14px; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; flex-wrap: wrap;">
        <form action="{{ route('account.gifts.index', $invitation->id) }}" method="GET" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap; flex-grow: 1; margin: 0;">
            <div style="position: relative; display: flex; align-items: center; min-width: 240px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#8C827A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px;">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" value="{{ $filterSearch }}" placeholder="Tìm theo tên hoặc lời chúc..." class="mayhen_account_form_input" style="height: 38px; padding-left: 36px;">
            </div>

            <select name="method" class="mayhen_account_form_input" style="height: 38px; width: auto;" onchange="this.form.submit()">
                <option value="">-- Tất cả hình thức --</option>
                <option value="cash" {{ $filterMethod === 'cash' ? 'selected' : '' }}>Tiền mặt / Phong bì</option>
                <option value="bank_transfer" {{ $filterMethod === 'bank_transfer' ? 'selected' : '' }}>Chuyển khoản VietQR</option>
                <option value="other" {{ $filterMethod === 'other' ? 'selected' : '' }}>Quà mừng hiện vật</option>
            </select>

            <select name="group" class="mayhen_account_form_input" style="height: 38px; width: auto;" onchange="this.form.submit()">
                <option value="">-- Tất cả nhóm khách --</option>
                @foreach($availableGroups as $grp)
                    <option value="{{ $grp }}" {{ $filterGroup === $grp ? 'selected' : '' }}>{{ $grp }}</option>
                @endforeach
            </select>

            <select name="ack" class="mayhen_account_form_input" style="height: 38px; width: auto;" onchange="this.form.submit()">
                <option value="">-- Trạng thái cảm ơn --</option>
                <option value="yes" {{ $filterAck === 'yes' ? 'selected' : '' }}>Đã gửi lời cảm ơn</option>
                <option value="no" {{ $filterAck === 'no' ? 'selected' : '' }}>Chưa gửi lời cảm ơn</option>
            </select>

            @if($filterSearch || $filterMethod || $filterGroup || $filterAck)
                <a href="{{ route('account.gifts.index', $invitation->id) }}" style="font-size: 13px; color: #C62828; text-decoration: none; padding: 6px 10px;">
                    Đặt lại
                </a>
            @endif
        </form>

        <span style="font-size: 13px; color: #7A726A; white-space: nowrap;">
            Hiển thị: <strong>{{ $gifts->total() }}</strong> khoản mừng
        </span>
    </div>

    <!-- Bảng Danh Sách Sổ Mừng Cưới -->
    <div style="background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 18px; overflow: hidden; box-shadow: 0 4px 16px rgba(31,29,26,0.03);">
        <div style="overflow-x: auto;">
            <table class="mayhen_account_table" style="width: 100%; border-collapse: collapse; font-size: 13px; text-align: left;">
                <thead>
                    <tr style="background: #FAF8F5; border-bottom: 1px solid #EAE3D9;">
                        <th style="padding: 14px 18px; font-weight: 600; color: #4A453E;">Người Mừng Cưới</th>
                        <th style="padding: 14px 18px; font-weight: 600; color: #4A453E;">Nhóm Khách</th>
                        <th style="padding: 14px 18px; font-weight: 600; color: #4A453E;">Số Tiền Mừng</th>
                        <th style="padding: 14px 18px; font-weight: 600; color: #4A453E;">Hình Thức</th>
                        <th style="padding: 14px 18px; font-weight: 600; color: #4A453E;">Lời Chúc / Ghi Chú</th>
                        <th style="padding: 14px 18px; font-weight: 600; color: #4A453E;">Trạng Thái Cảm Ơn</th>
                        <th style="padding: 14px 18px; font-weight: 600; color: #4A453E; text-align: right;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gifts as $gift)
                        <tr style="border-bottom: 1px solid #F0EBE3;">
                            <td style="padding: 16px 18px;">
                                <div style="font-weight: 600; color: #1F1D1A;">{{ $gift->sender_name }}</div>
                                <div style="font-size: 11px; color: #8C827A;">{{ $gift->created_at->format('d/m/Y H:i') }}</div>
                            </td>
                            <td style="padding: 16px 18px;">
                                <span style="display: inline-block; padding: 3px 8px; background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 6px; font-size: 11px; color: #6B635B;">
                                    {{ $gift->sender_group ?? 'Bạn Bè' }}
                                </span>
                            </td>
                            <td style="padding: 16px 18px;">
                                <div style="font-weight: 700; font-size: 15px; color: #9C7A4A;">
                                    {{ number_format($gift->amount, 0, ',', '.') }} đ
                                </div>
                            </td>
                            <td style="padding: 16px 18px;">
                                @if($gift->payment_method === 'bank_transfer')
                                    <span style="display: inline-block; padding: 4px 10px; background: #E8F5E9; color: #2E7D32; border: 1px solid #C8E6C9; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                        VietQR
                                    </span>
                                @elseif($gift->payment_method === 'cash')
                                    <span style="display: inline-block; padding: 4px 10px; background: #E3F2FD; color: #1565C0; border: 1px solid #BBDEFB; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                        Tiền Mặt
                                    </span>
                                @else
                                    <span style="display: inline-block; padding: 4px 10px; background: #FFF8E1; color: #F57F17; border: 1px solid #FFECB3; border-radius: 12px; font-size: 12px; font-weight: 500;">
                                        Quà Tặng
                                    </span>
                                @endif
                            </td>
                            <td style="padding: 16px 18px; max-width: 260px;">
                                <span style="font-style: italic; color: #5A534B; font-size: 12px;">
                                    {{ $gift->notes ? "\"{$gift->notes}\"" : '—' }}
                                </span>
                            </td>
                            <td style="padding: 16px 18px;">
                                <form action="{{ route('account.gifts.toggle_ack', [$invitation->id, $gift->id]) }}" method="POST" style="margin: 0;">
                                    @csrf
                                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" style="font-size: 11px; padding: 4px 10px;" title="Nhấn để đổi trạng thái">
                                        @if($gift->is_acknowledged)
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2E7D32" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"></polyline>
                                            </svg>
                                            <span style="color: #2E7D32; font-weight: 600;">Đã cảm ơn</span>
                                        @else
                                            <span style="color: #7A726A;">Chưa gửi</span>
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td style="padding: 16px 18px; text-align: right; white-space: nowrap;">
                                <button type="button" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm mayhen-edit-gift-btn"
                                    data-gift-id="{{ $gift->id }}"
                                    data-sender-name="{{ $gift->sender_name }}"
                                    data-sender-group="{{ $gift->sender_group }}"
                                    data-amount="{{ $gift->amount }}"
                                    data-payment-method="{{ $gift->payment_method }}"
                                    data-notes="{{ $gift->notes ?? '' }}"
                                    data-acknowledged="{{ $gift->is_acknowledged ? '1' : '0' }}"
                                    data-update-url="{{ route('account.gifts.update', [$invitation->id, $gift->id]) }}"
                                    title="Chỉnh sửa khoản mừng">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9"></path>
                                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                    </svg>
                                </button>

                                <form action="{{ route('account.gifts.destroy', [$invitation->id, $gift->id]) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khoản mừng cưới này khỏi sổ?');" style="display: inline-block; margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_sm" style="background: transparent; color: #A39B92; border: none; padding: 6px;" title="Xóa khoản mừng">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 48px 20px; color: #8C827A;">
                                @if($filterSearch || $filterMethod || $filterGroup || $filterAck)
                                    Không tìm thấy khoản mừng cưới nào phù hợp với bộ lọc hiện tại.
                                    <div style="margin-top: 10px;">
                                        <a href="{{ route('account.gifts.index', $invitation->id) }}" style="color: #9C7A4A; font-weight: 500;">
                                            Xem toàn bộ sổ mừng cưới &rarr;
                                        </a>
                                    </div>
                                @else
                                    Chưa có khoản mừng cưới nào được ghi nhận. Hãy thêm khoản mừng đầu tiên ở khung trên!
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($gifts->hasPages())
            <div style="padding: 16px 20px; border-top: 1px solid #F0EBE3;">
                {{ $gifts->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Chỉnh Sửa Khoản Mừng Cưới -->
    <div id="mayhen-edit-gift-modal" class="mayhen_account_modal" aria-hidden="true" role="dialog" aria-labelledby="mayhen-edit-gift-title" style="display: none !important; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
        <div class="mayhen_account_modal_backdrop" onclick="closeEditGiftModal()" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(31, 29, 26, 0.6); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);"></div>
        <div class="mayhen_account_modal_dialog" style="position: relative; background: #FFFFFF; border: 1px solid #EAE3D9; border-radius: 20px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 50px rgba(31, 29, 26, 0.2); z-index: 10000;">
            <div class="mayhen_account_modal_header" style="display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid #F0EBE3;">
                <h3 id="mayhen-edit-gift-title" class="mayhen_account_modal_title" style="font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 600; color: #1F1D1A; margin: 0;">Chỉnh Sửa Khoản Mừng Cưới</h3>
                <button type="button" class="mayhen_account_modal_close" onclick="closeEditGiftModal()" aria-label="Đóng" style="background: none; border: none; font-size: 26px; line-height: 1; color: #8C827A; cursor: pointer; padding: 4px; transition: color 0.2s;">&times;</button>
            </div>
            <form id="mayhen-edit-gift-form" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="mayhen_account_modal_body" style="padding: 24px; display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <label class="mayhen_account_form_label">Tên người mừng cưới *</label>
                        <input type="text" name="sender_name" id="modal-sender-name" required class="mayhen_account_form_input">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="mayhen_account_form_label">Nhóm quan khách</label>
                            <input type="text" name="sender_group" id="modal-sender-group" class="mayhen_account_form_input">
                        </div>
                        <div>
                            <label class="mayhen_account_form_label">Hình thức mừng</label>
                            <select name="payment_method" id="modal-payment-method" class="mayhen_account_form_input">
                                <option value="cash">Tiền mặt / Phong bì</option>
                                <option value="bank_transfer">Chuyển khoản VietQR</option>
                                <option value="other">Quà mừng hiện vật</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mayhen_account_form_label">Số tiền mừng (VNĐ) *</label>
                        <input type="number" name="amount" id="modal-amount" required min="0" step="10000" class="mayhen_account_form_input">
                    </div>

                    <div>
                        <label class="mayhen_account_form_label">Lời chúc hoặc ghi chú</label>
                        <input type="text" name="notes" id="modal-notes" class="mayhen_account_form_input">
                    </div>

                    <div>
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px;">
                            <input type="checkbox" name="is_acknowledged" id="modal-acknowledged" value="1" style="width: 18px; height: 18px; accent-color: #C5A880;">
                            <span>Đã gửi lời cảm ơn</span>
                        </label>
                    </div>
                </div>
                <div class="mayhen_account_modal_footer" style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; padding: 16px 24px; border-top: 1px solid #F0EBE3; background: #FAF8F5; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px;">
                    <button type="button" class="mayhen_account_btn mayhen_account_btn_outline" onclick="closeEditGiftModal()">Hủy</button>
                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Cài Đặt Đa Tài Khoản Ngân Hàng & Bảo Mật (Section 35 PRD V3) -->
    <div id="mayhen-banking-modal" class="mayhen_account_modal_backdrop" style="display: none; position: fixed; inset: 0; background: rgba(31, 29, 26, 0.6); z-index: 1050; align-items: center; justify-content: center; padding: 20px; overflow-y: auto;">
        <div class="mayhen_account_modal_dialog" style="background: #FFFFFF; border-radius: 20px; max-width: 680px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.15); border: 1px solid #EAE3D9; max-height: 90vh; display: flex; flex-direction: column;">
            <div class="mayhen_account_modal_header" style="display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid #F0EBE3;">
                <div>
                    <h3 style="font-family: 'Cormorant Garamond', serif; font-size: 22px; font-weight: 600; color: #1F1D1A; margin: 0;">
                        Cài Đặt Tài Khoản Ngân Hàng & Mã QR Nhận Quà
                    </h3>
                    <p style="font-size: 13px; color: #7A726A; margin: 2px 0 0 0;">
                        Hỗ trợ đa tài khoản (Nhà trai / Nhà gái) và VietQR tự động.
                    </p>
                </div>
                <button type="button" onclick="closeBankingModal()" style="background: none; border: none; font-size: 20px; color: #8C827A; cursor: pointer; padding: 4px;">✕</button>
            </div>

            <form action="{{ route('account.gifts.update_banking', $invitation->id) }}" method="POST" style="display: flex; flex-direction: column; overflow: hidden; flex: 1;">
                @csrf
                <div class="mayhen_account_modal_body" style="padding: 24px; overflow-y: auto; flex: 1;">
                    <!-- Trạng thái hiển thị công khai -->
                    <div style="background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 12px; padding: 14px 18px; margin-bottom: 20px;">
                        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                            <input type="checkbox" name="is_gift_qr_enabled" value="1" {{ $isGiftQrEnabled ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #C5A880;">
                            <div>
                                <span style="font-size: 14px; font-weight: 600; color: #1F1D1A;">Cho phép hiển thị Hộp Mừng Cưới & QR trên thiệp công khai</span>
                                <div style="font-size: 12px; color: #7A726A; margin-top: 2px;">
                                    Bỏ chọn nếu bạn muốn tắt hoàn toàn tính năng quét mã QR trên thiệp cưới.
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Danh sách tài khoản ngân hàng -->
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <h4 style="font-size: 14px; font-weight: 600; color: #1F1D1A; margin: 0;">
                            Danh Sách Tài Khoản Nhận Quà Mừng
                        </h4>
                        <button type="button" id="mayhen-add-bank-row-btn" class="mayhen_account_btn mayhen_account_btn_outline mayhen_account_btn_sm" style="font-size: 12px; padding: 4px 10px;">
                            + Thêm Tài Khoản
                        </button>
                    </div>

                    <div id="mayhen-bank-accounts-container">
                        @forelse($bankAccounts as $idx => $acc)
                            <div class="mayhen_bank_account_row" style="background: #FFFFFF; border: 1px solid #E8DFD3; border-radius: 12px; padding: 16px; margin-bottom: 14px; position: relative;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                    <span style="font-size: 13px; font-weight: 600; color: #9C7A4A;">Tài Khoản #{{ $idx + 1 }}</span>
                                    <button type="button" class="mayhen-remove-bank-row-btn" style="background: none; border: none; color: #C62828; font-size: 12px; cursor: pointer; padding: 4px 8px;">✕ Xóa bỏ</button>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                    <div>
                                        <label class="mayhen_account_form_label" style="font-size: 12px;">Nhãn tài khoản *</label>
                                        <input type="text" name="accounts[{{ $idx }}][label]" required class="mayhen_account_form_input" style="height: 38px; font-size: 13px;" placeholder="Ví dụ: Nhà Trai (Chú Rể)" value="{{ $acc['label'] ?? '' }}">
                                    </div>
                                    <div>
                                        <label class="mayhen_account_form_label" style="font-size: 12px;">Tên ngân hàng *</label>
                                        <input type="text" name="accounts[{{ $idx }}][bank_name]" required class="mayhen_account_form_input" style="height: 38px; font-size: 13px;" placeholder="Ví dụ: MB Bank, Vietcombank..." value="{{ $acc['bank_name'] ?? '' }}">
                                    </div>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                    <div>
                                        <label class="mayhen_account_form_label" style="font-size: 12px;">Số tài khoản *</label>
                                        <input type="text" name="accounts[{{ $idx }}][bank_account_number]" required class="mayhen_account_form_input" style="height: 38px; font-size: 13px;" placeholder="Nhập số tài khoản..." value="{{ $acc['bank_account_number'] ?? '' }}">
                                    </div>
                                    <div>
                                        <label class="mayhen_account_form_label" style="font-size: 12px;">Tên chủ tài khoản (In hoa) *</label>
                                        <input type="text" name="accounts[{{ $idx }}][account_holder]" required class="mayhen_account_form_input" style="height: 38px; font-size: 13px; text-transform: uppercase;" placeholder="NGUYEN VAN A" value="{{ $acc['account_holder'] ?? '' }}">
                                    </div>
                                </div>
                                <div>
                                    <label class="mayhen_account_form_label" style="font-size: 12px;">Ảnh mã QR tùy chọn (Để trống sẽ tự sinh mã VietQR chuẩn)</label>
                                    <input type="url" name="accounts[{{ $idx }}][qr_image_url]" class="mayhen_account_form_input" style="height: 38px; font-size: 13px;" placeholder="https://..." value="{{ $acc['qr_image_url'] ?? '' }}">
                                </div>
                            </div>
                        @empty
                            <div class="mayhen_bank_account_row" style="background: #FFFFFF; border: 1px solid #E8DFD3; border-radius: 12px; padding: 16px; margin-bottom: 14px; position: relative;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                    <span style="font-size: 13px; font-weight: 600; color: #9C7A4A;">Tài Khoản #1</span>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                    <div>
                                        <label class="mayhen_account_form_label" style="font-size: 12px;">Nhãn tài khoản *</label>
                                        <input type="text" name="accounts[0][label]" required class="mayhen_account_form_input" style="height: 38px; font-size: 13px;" placeholder="Ví dụ: Nhà Trai (Chú Rể)" value="Tài Khoản Mừng Cưới">
                                    </div>
                                    <div>
                                        <label class="mayhen_account_form_label" style="font-size: 12px;">Tên ngân hàng *</label>
                                        <input type="text" name="accounts[0][bank_name]" required class="mayhen_account_form_input" style="height: 38px; font-size: 13px;" placeholder="Ví dụ: MB Bank, Vietcombank...">
                                    </div>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                    <div>
                                        <label class="mayhen_account_form_label" style="font-size: 12px;">Số tài khoản *</label>
                                        <input type="text" name="accounts[0][bank_account_number]" required class="mayhen_account_form_input" style="height: 38px; font-size: 13px;" placeholder="Nhập số tài khoản...">
                                    </div>
                                    <div>
                                        <label class="mayhen_account_form_label" style="font-size: 12px;">Tên chủ tài khoản (In hoa) *</label>
                                        <input type="text" name="accounts[0][account_holder]" required class="mayhen_account_form_input" style="height: 38px; font-size: 13px; text-transform: uppercase;" placeholder="NGUYEN VAN A">
                                    </div>
                                </div>
                                <div>
                                    <label class="mayhen_account_form_label" style="font-size: 12px;">Ảnh mã QR tùy chọn (Để trống sẽ tự sinh mã VietQR chuẩn)</label>
                                    <input type="url" name="accounts[0][qr_image_url]" class="mayhen_account_form_input" style="height: 38px; font-size: 13px;" placeholder="https://...">
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Xác thực mật khẩu bảo vệ Account Takeover (Section 35 PRD V3) -->
                    <div style="background: #FFF8E1; border: 1px solid #FFE082; border-radius: 12px; padding: 14px 18px; margin-top: 16px;">
                        <div style="display: flex; align-items: flex-start; gap: 10px; margin-bottom: 10px;">
                            <span style="font-size: 18px;">🛡️</span>
                            <div>
                                <span style="font-size: 13px; font-weight: 600; color: #B78103;">Xác Thực Mật Khẩu Bảo Mật (Chống Chiếm Đoạt Tài Khoản)</span>
                                <div style="font-size: 12px; color: #6D4C41; margin-top: 2px;">
                                    Để bảo vệ an toàn cho quà mừng cưới của bạn, hệ thống yêu cầu xác nhận mật khẩu đăng nhập trước khi áp dụng thay đổi thông tin ngân hàng.
                                </div>
                            </div>
                        </div>
                        <label class="mayhen_account_form_label" style="font-size: 12px; color: #4E342E;">Mật khẩu tài khoản hiện tại của bạn *</label>
                        <input type="password" name="current_password" required class="mayhen_account_form_input" style="height: 38px; font-size: 13px; max-width: 320px;" placeholder="Nhập mật khẩu để xác nhận...">
                    </div>
                </div>

                <div class="mayhen_account_modal_footer" style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; padding: 16px 24px; border-top: 1px solid #F0EBE3; background: #FAF8F5; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px;">
                    <button type="button" class="mayhen_account_btn mayhen_account_btn_outline" onclick="closeBankingModal()">Hủy</button>
                    <button type="submit" class="mayhen_account_btn mayhen_account_btn_primary">Xác Nhận & Cập Nhật</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset('assets/client/js/pages/gifts.js') }}?v={{ file_exists(public_path('assets/client/js/pages/gifts.js')) ? filemtime(public_path('assets/client/js/pages/gifts.js')) : '1.0' }}"></script>
@endpush
