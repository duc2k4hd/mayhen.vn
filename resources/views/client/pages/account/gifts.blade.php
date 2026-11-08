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
    <div id="mayhen-edit-gift-modal" class="mayhen_account_modal" aria-hidden="true" role="dialog" aria-labelledby="mayhen-edit-gift-title" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; align-items: center; justify-content: center; padding: 20px; box-sizing: border-box;">
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const editModal = document.getElementById('mayhen-edit-gift-modal');
        const editForm = document.getElementById('mayhen-edit-gift-form');
        const nameInput = document.getElementById('modal-sender-name');
        const groupInput = document.getElementById('modal-sender-group');
        const amountInput = document.getElementById('modal-amount');
        const methodSelect = document.getElementById('modal-payment-method');
        const notesInput = document.getElementById('modal-notes');
        const ackCheckbox = document.getElementById('modal-acknowledged');

        document.querySelectorAll('.mayhen-edit-gift-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const updateUrl = btn.getAttribute('data-update-url');
                const name = btn.getAttribute('data-sender-name') || '';
                const group = btn.getAttribute('data-sender-group') || '';
                const amount = btn.getAttribute('data-amount') || '0';
                const method = btn.getAttribute('data-payment-method') || 'cash';
                const notes = btn.getAttribute('data-notes') || '';
                const ack = btn.getAttribute('data-acknowledged') === '1';

                if (editForm) editForm.action = updateUrl;
                if (nameInput) nameInput.value = name;
                if (groupInput) groupInput.value = group;
                if (amountInput) amountInput.value = amount;
                if (methodSelect) methodSelect.value = method;
                if (notesInput) notesInput.value = notes;
                if (ackCheckbox) ackCheckbox.checked = ack;

                if (editModal) {
                    editModal.style.display = 'flex';
                    editModal.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                }
            });
        });
    });

    function closeEditGiftModal() {
        const modal = document.getElementById('mayhen-edit-gift-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('is-open');
            document.body.style.overflow = '';
        }
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeEditGiftModal();
        }
    });
</script>
@endpush
