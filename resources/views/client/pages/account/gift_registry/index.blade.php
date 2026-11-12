@extends('client.pages.account.layout')

@php
    $pageTitle = 'Hộp Mừng Cưới, VietQR & Quà Tân Hôn — Mây Hẹn';
    $wedding = $invitation->wedding;
    $coupleName = ($wedding->groom_name ?? 'Chú rể') . ' & ' . ($wedding->bride_name ?? 'Cô dâu');
@endphp

@section('account_breadcrumb', 'Hộp Mừng Cưới & VietQR')

@section('account_content')
<div class="mayhen_gift_admin_container">
    {{-- Header & Headline --}}
    <div class="mayhen_gift_admin_header">
        <div class="mayhen_gift_admin_headline">
            <div class="mayhen_gift_admin_badge">Digital Wedding Gift Registry &amp; VietQR Suite</div>
            <h1 class="mayhen_gift_admin_title">Hộp Mừng Cưới, VietQR &amp; Quà Tân Hôn</h1>
            <p class="mayhen_gift_admin_subtitle">
                Cấu hình tài khoản ngân hàng mừng cưới Nhà Trai / Nhà Gái, sinh mã VietQR chuẩn NAPAS 247 một chạm và danh sách quà mừng tân hôn mong ước cho ngày trọng đại của <strong>{{ $coupleName }}</strong>.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mayhen_gift_alert mayhen_gift_alert--success">
            <span class="mayhen_gift_alert_icon">✓</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mayhen_gift_alert mayhen_gift_alert--danger">
            <span class="mayhen_gift_alert_icon">!</span>
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('account.gift_registry.update', $invitation->id) }}" method="POST" id="giftRegistryForm" class="mayhen_gift_form">
        @csrf

        {{-- Section 1: Phong Cách Hiển Thị & Lời Ngỏ Tế Nhị --}}
        <div class="mayhen_gift_card">
            <div class="mayhen_gift_card_header">
                <div class="mayhen_gift_card_icon">🎁</div>
                <div class="mayhen_gift_card_title_row">
                    <div>
                        <h2 class="mayhen_gift_card_title">1. Phong Cách Hiển Thị &amp; Lời Ngỏ Lễ Nghi</h2>
                        <p class="mayhen_gift_card_desc">Chọn kiểu dáng Hộp Mừng Cưới và lời chia sẻ tinh tế giải tỏa sự ngượng ngùng khi nhận mừng cưới từ quan khách.</p>
                    </div>
                    <label class="mayhen_gift_switch">
                        <input type="checkbox" name="enabled" value="1" {{ !empty($registryConfig['enabled']) ? 'checked' : '' }}>
                        <span class="mayhen_gift_slider"></span>
                        <span class="mayhen_gift_switch_text">Hiển thị Hộp Mừng Cưới</span>
                    </label>
                </div>
            </div>

            <div class="mayhen_gift_card_body">
                <!-- Chọn 3 Phong Cách Hộp Quà -->
                <div class="mayhen_gift_form_group">
                    <label class="mayhen_gift_label">Phong cách hiển thị Hộp Mừng Cưới <span class="required">*</span></label>
                    <div class="mayhen_gift_style_grid">
                        @foreach($boxStyles as $styleKey => $style)
                            <label class="mayhen_gift_style_card {{ ($registryConfig['box_style'] ?? 'ribbon_box') === $styleKey ? 'selected' : '' }}">
                                <input type="radio" name="box_style" value="{{ $styleKey }}" {{ ($registryConfig['box_style'] ?? 'ribbon_box') === $styleKey ? 'checked' : '' }} onchange="updateStyleSelection(this)">
                                <div class="mayhen_gift_style_header">
                                    <span class="mayhen_gift_style_name">{{ $style['name'] }}</span>
                                    @if(!empty($style['badge']))
                                        <span class="mayhen_gift_style_badge">{{ $style['badge'] }}</span>
                                    @endif
                                </div>
                                <p class="mayhen_gift_style_desc">{{ $style['description'] }}</p>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="mayhen_gift_row_2col">
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label" for="kickerInput">Nhãn phụ trang trí (Kicker)</label>
                        <input type="text" id="kickerInput" name="kicker" class="mayhen_gift_input" value="{{ old('kicker', $registryConfig['kicker'] ?? 'Gửi Gắm Yêu Thương') }}" placeholder="Ví dụ: Gửi Gắm Yêu Thương">
                    </div>
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label" for="titleInput">Tiêu đề Section <span class="required">*</span></label>
                        <input type="text" id="titleInput" name="title" class="mayhen_gift_input" value="{{ old('title', $registryConfig['title'] ?? 'Hộp Mừng Cưới & Chúc Phúc') }}" required placeholder="Ví dụ: Hộp Mừng Cưới & Chúc Phúc">
                    </div>
                </div>

                <!-- Lời Ngỏ Tế Nhị & Bộ Chọn Mẫu 1-Click -->
                <div class="mayhen_gift_form_group">
                    <div class="mayhen_gift_label_flex">
                        <label class="mayhen_gift_label" for="etiquetteInput">Lời ngỏ tế nhị gửi quan khách <span class="required">*</span></label>
                        <span class="mayhen_gift_help">Chọn mẫu gợi ý bên dưới để tự động điền</span>
                    </div>

                    <div class="mayhen_gift_presets_bar">
                        @foreach($etiquetteNotes as $preset)
                            <button type="button" class="mayhen_gift_preset_chip" onclick="applyEtiquetteNote('{{ addslashes($preset['content']) }}')">
                                💬 {{ $preset['title'] }}
                            </button>
                        @endforeach
                    </div>

                    <textarea id="etiquetteInput" name="etiquette_note" class="mayhen_gift_textarea" rows="3" required placeholder="Nhập lời ngỏ tinh tế...">{{ old('etiquette_note', $registryConfig['etiquette_note'] ?? '') }}</textarea>
                </div>

                <!-- Che Số Tài Khoản -->
                <div class="mayhen_gift_form_group">
                    <label class="mayhen_gift_checkbox_row">
                        <input type="checkbox" name="mask_account_numbers" value="1" {{ !empty($registryConfig['mask_account_numbers']) ? 'checked' : '' }}>
                        <span>
                            <strong>Bảo vệ riêng tư:</strong> Che 4 số cuối tài khoản ngân hàng đối với khách vãng lai (Chỉ khách mở qua liên kết định danh cá nhân mới thấy số đầy đủ).
                        </span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Section 2: Tài Khoản Mừng Cưới Nhà Trai (Chú Rể) --}}
        <div class="mayhen_gift_card">
            <div class="mayhen_gift_card_header">
                <div class="mayhen_gift_card_icon">🤵</div>
                <div class="mayhen_gift_card_title_row">
                    <div>
                        <h2 class="mayhen_gift_card_title">2. Mừng Cưới Nhà Trai (Chú Rể)</h2>
                        <p class="mayhen_gift_card_desc">Thông tin tài khoản ngân hàng và mã VietQR nhận mừng cưới cho Chú rể hoặc gia đình Nhà trai.</p>
                    </div>
                    <label class="mayhen_gift_switch">
                        <input type="checkbox" name="groom_enabled" value="1" {{ !empty($registryConfig['groom']['enabled']) ? 'checked' : '' }}>
                        <span class="mayhen_gift_slider"></span>
                        <span class="mayhen_gift_switch_text">Kích hoạt</span>
                    </label>
                </div>
            </div>

            <div class="mayhen_gift_card_body">
                <div class="mayhen_gift_row_2col">
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label" for="groomRoleLabel">Nhãn vai trò hiển thị</label>
                        <input type="text" id="groomRoleLabel" name="groom_role_label" class="mayhen_gift_input" value="{{ old('groom_role_label', $registryConfig['groom']['role_label'] ?? 'Mừng Cưới Chú Rể') }}" placeholder="Ví dụ: Mừng Cưới Chú Rể">
                    </div>
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label" for="groomBankSelect">Ngân hàng thụ hưởng</label>
                        <select id="groomBankSelect" name="groom_bank_bin" class="mayhen_gift_select" onchange="syncGroomBankName(this)">
                            @foreach($banks as $b)
                                <option value="{{ $b['bin'] }}" data-name="{{ $b['short_name'] }}" {{ ($registryConfig['groom']['bank_bin'] ?? '970436') === $b['bin'] ? 'selected' : '' }}>
                                    {{ $b['short_name'] }} — {{ $b['name'] }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" id="groomBankNameInput" name="groom_bank_name" value="{{ $registryConfig['groom']['bank_name'] ?? 'Vietcombank' }}">
                    </div>
                </div>

                <div class="mayhen_gift_row_2col">
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label" for="groomAccountNumber">Số tài khoản ngân hàng</label>
                        <input type="text" id="groomAccountNumber" name="groom_account_number" class="mayhen_gift_input" value="{{ old('groom_account_number', $registryConfig['groom']['account_number'] ?? '') }}" placeholder="Nhập số tài khoản..." oninput="updateGroomQrPreview()">
                    </div>
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label" for="groomAccountHolder">Tên chủ tài khoản (Viết in hoa không dấu)</label>
                        <input type="text" id="groomAccountHolder" name="groom_account_holder" class="mayhen_gift_input" value="{{ old('groom_account_holder', $registryConfig['groom']['account_holder'] ?? '') }}" placeholder="NGUYEN MINH TUAN" oninput="updateGroomQrPreview()">
                    </div>
                </div>

                <div class="mayhen_gift_row_2col">
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label" for="groomTransferNote">Nội dung chuyển khoản gợi ý</label>
                        <input type="text" id="groomTransferNote" name="groom_transfer_note" class="mayhen_gift_input" value="{{ old('groom_transfer_note', $registryConfig['groom']['transfer_note'] ?? 'Mung cuoi Chu Re') }}" placeholder="Mung cuoi Chu Re" oninput="updateGroomQrPreview()">
                    </div>
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label">Ảnh mã QR riêng (Tùy chọn tải lên)</label>
                        <div class="mayhen_gift_upload_row">
                            <input type="file" id="groomQrFileInput" accept="image/*" class="mayhen_gift_file_input" onchange="uploadQr('groom')">
                            <input type="hidden" id="groomCustomQrInput" name="groom_custom_qr_url" value="{{ $registryConfig['groom']['custom_qr_url'] ?? '' }}">
                            <span class="mayhen_gift_upload_status" id="groomUploadStatus"></span>
                        </div>
                    </div>
                </div>

                <!-- Preview QR Chú Rể -->
                <div class="mayhen_gift_qr_preview_box">
                    <div class="mayhen_gift_qr_preview_img_wrap">
                        <img id="groomQrPreviewImg" src="{{ $registryConfig['groom']['qr_url'] ?: 'https://placehold.co/200x200?text=VietQR+Groom' }}" alt="Preview QR Chú rể">
                    </div>
                    <div class="mayhen_gift_qr_preview_info">
                        <div class="mayhen_gift_qr_preview_title">Xem Trước Mã VietQR Tự Động Sinh</div>
                        <p class="mayhen_gift_qr_preview_desc">Mã QR chuẩn NAPAS 247 cho phép quan khách quét bằng bất kỳ ứng dụng ngân hàng nào mà không cần nhập tay STK hay nội dung.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Tài Khoản Mừng Cưới Nhà Gái (Cô Dâu) --}}
        <div class="mayhen_gift_card">
            <div class="mayhen_gift_card_header">
                <div class="mayhen_gift_card_icon">👰</div>
                <div class="mayhen_gift_card_title_row">
                    <div>
                        <h2 class="mayhen_gift_card_title">3. Mừng Cưới Nhà Gái (Cô Dâu)</h2>
                        <p class="mayhen_gift_card_desc">Thông tin tài khoản ngân hàng và mã VietQR nhận mừng cưới cho Cô dâu hoặc gia đình Nhà gái.</p>
                    </div>
                    <label class="mayhen_gift_switch">
                        <input type="checkbox" name="bride_enabled" value="1" {{ !empty($registryConfig['bride']['enabled']) ? 'checked' : '' }}>
                        <span class="mayhen_gift_slider"></span>
                        <span class="mayhen_gift_switch_text">Kích hoạt</span>
                    </label>
                </div>
            </div>

            <div class="mayhen_gift_card_body">
                <div class="mayhen_gift_row_2col">
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label" for="brideRoleLabel">Nhãn vai trò hiển thị</label>
                        <input type="text" id="brideRoleLabel" name="bride_role_label" class="mayhen_gift_input" value="{{ old('bride_role_label', $registryConfig['bride']['role_label'] ?? 'Mừng Cưới Cô Dâu') }}" placeholder="Ví dụ: Mừng Cưới Cô Dâu">
                    </div>
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label" for="brideBankSelect">Ngân hàng thụ hưởng</label>
                        <select id="brideBankSelect" name="bride_bank_bin" class="mayhen_gift_select" onchange="syncBrideBankName(this)">
                            @foreach($banks as $b)
                                <option value="{{ $b['bin'] }}" data-name="{{ $b['short_name'] }}" {{ ($registryConfig['bride']['bank_bin'] ?? '970407') === $b['bin'] ? 'selected' : '' }}>
                                    {{ $b['short_name'] }} — {{ $b['name'] }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" id="brideBankNameInput" name="bride_bank_name" value="{{ $registryConfig['bride']['bank_name'] ?? 'Techcombank' }}">
                    </div>
                </div>

                <div class="mayhen_gift_row_2col">
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label" for="brideAccountNumber">Số tài khoản ngân hàng</label>
                        <input type="text" id="brideAccountNumber" name="bride_account_number" class="mayhen_gift_input" value="{{ old('bride_account_number', $registryConfig['bride']['account_number'] ?? '') }}" placeholder="Nhập số tài khoản..." oninput="updateBrideQrPreview()">
                    </div>
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label" for="brideAccountHolder">Tên chủ tài khoản (Viết in hoa không dấu)</label>
                        <input type="text" id="brideAccountHolder" name="bride_account_holder" class="mayhen_gift_input" value="{{ old('bride_account_holder', $registryConfig['bride']['account_holder'] ?? '') }}" placeholder="TRAN THU HA" oninput="updateBrideQrPreview()">
                    </div>
                </div>

                <div class="mayhen_gift_row_2col">
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label" for="brideTransferNote">Nội dung chuyển khoản gợi ý</label>
                        <input type="text" id="brideTransferNote" name="bride_transfer_note" class="mayhen_gift_input" value="{{ old('bride_transfer_note', $registryConfig['bride']['transfer_note'] ?? 'Mung cuoi Co Dau') }}" placeholder="Mung cuoi Co Dau" oninput="updateBrideQrPreview()">
                    </div>
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label">Ảnh mã QR riêng (Tùy chọn tải lên)</label>
                        <div class="mayhen_gift_upload_row">
                            <input type="file" id="brideQrFileInput" accept="image/*" class="mayhen_gift_file_input" onchange="uploadQr('bride')">
                            <input type="hidden" id="brideCustomQrInput" name="bride_custom_qr_url" value="{{ $registryConfig['bride']['custom_qr_url'] ?? '' }}">
                            <span class="mayhen_gift_upload_status" id="brideUploadStatus"></span>
                        </div>
                    </div>
                </div>

                <!-- Preview QR Cô Dâu -->
                <div class="mayhen_gift_qr_preview_box">
                    <div class="mayhen_gift_qr_preview_img_wrap">
                        <img id="brideQrPreviewImg" src="{{ $registryConfig['bride']['qr_url'] ?: 'https://placehold.co/200x200?text=VietQR+Bride' }}" alt="Preview QR Cô dâu">
                    </div>
                    <div class="mayhen_gift_qr_preview_info">
                        <div class="mayhen_gift_qr_preview_title">Xem Trước Mã VietQR Tự Động Sinh</div>
                        <p class="mayhen_gift_qr_preview_desc">Mã QR chuẩn NAPAS 247 cho phép quan khách quét bằng bất kỳ ứng dụng ngân hàng nào một chạm.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Danh Sách Món Quà Tân Hôn Mong Ước (Wishlist Registry - IDEA-F07) --}}
        <div class="mayhen_gift_card">
            <div class="mayhen_gift_card_header">
                <div class="mayhen_gift_card_icon">🎀</div>
                <div class="mayhen_gift_card_title_row">
                    <div>
                        <h2 class="mayhen_gift_card_title">4. Danh Sách Quà Tân Hôn Mong Ước (Gift Wishlist Registry)</h2>
                        <p class="mayhen_gift_card_desc">Gợi ý danh sách quà hiện vật tân hôn để bạn bè thân thiết chọn gửi tặng, tránh tặng trùng lặp món đồ.</p>
                    </div>
                    <label class="mayhen_gift_switch">
                        <input type="checkbox" name="wishlist_enabled" value="1" {{ !empty($registryConfig['wishlist']['enabled']) ? 'checked' : '' }}>
                        <span class="mayhen_gift_slider"></span>
                        <span class="mayhen_gift_switch_text">Kích hoạt Wishlist</span>
                    </label>
                </div>
            </div>

            <div class="mayhen_gift_card_body">
                <div class="mayhen_gift_row_2col">
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label" for="wishlistTitle">Tiêu đề danh sách quà</label>
                        <input type="text" id="wishlistTitle" name="wishlist_title" class="mayhen_gift_input" value="{{ old('wishlist_title', $registryConfig['wishlist']['title'] ?? 'Món Quà Tân Hôn Mong Ước') }}">
                    </div>
                    <div class="mayhen_gift_form_group">
                        <label class="mayhen_gift_label" for="wishlistSub">Phụ đề giải thích</label>
                        <input type="text" id="wishlistSub" name="wishlist_subtitle" class="mayhen_gift_input" value="{{ old('wishlist_subtitle', $registryConfig['wishlist']['subtitle'] ?? 'Gợi ý món quà hiện vật tân hôn cho tổ ấm nhỏ') }}">
                    </div>
                </div>

                <!-- Danh sách items -->
                <div class="mayhen_gift_wishlist_items_container" id="wishlistItemsContainer">
                    @php
                        $items = $registryConfig['wishlist']['items'] ?? [];
                    @endphp
                    @foreach($items as $idx => $item)
                        <div class="mayhen_gift_wishlist_item_row" id="wishlistItemRow_{{ $idx }}">
                            <input type="hidden" name="wishlist_items[{{ $idx }}][id]" value="{{ $item['id'] ?? ($idx + 1) }}">
                            <input type="hidden" name="wishlist_items[{{ $idx }}][status]" value="{{ $item['status'] ?? 'available' }}">
                            <input type="hidden" name="wishlist_items[{{ $idx }}][reserved_by]" value="{{ $item['reserved_by'] ?? '' }}">

                            <div class="mayhen_gift_wishlist_col_name">
                                <label class="mayhen_gift_mini_label">Tên món quà</label>
                                <input type="text" name="wishlist_items[{{ $idx }}][name]" class="mayhen_gift_input" value="{{ $item['name'] }}" placeholder="Ví dụ: Máy pha cà phê">
                            </div>
                            <div class="mayhen_gift_wishlist_col_cat">
                                <label class="mayhen_gift_mini_label">Phân loại</label>
                                <input type="text" name="wishlist_items[{{ $idx }}][category]" class="mayhen_gift_input" value="{{ $item['category'] }}" placeholder="Gia dụng bếp">
                            </div>
                            <div class="mayhen_gift_wishlist_col_price">
                                <label class="mayhen_gift_mini_label">Giá ước tính</label>
                                <input type="text" name="wishlist_items[{{ $idx }}][estimated_price]" class="mayhen_gift_input" value="{{ $item['estimated_price'] }}" placeholder="2.500.000 đ">
                            </div>
                            <div class="mayhen_gift_wishlist_col_link">
                                <label class="mayhen_gift_mini_label">Link sản phẩm (Tùy chọn)</label>
                                <input type="text" name="wishlist_items[{{ $idx }}][product_link]" class="mayhen_gift_input" value="{{ $item['product_link'] ?? '' }}" placeholder="https://...">
                            </div>
                            <div class="mayhen_gift_wishlist_col_action">
                                <button type="button" class="mayhen_gift_btn_delete_item" onclick="removeWishlistItem({{ $idx }})" title="Xóa món này">✕</button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mayhen_gift_wishlist_actions">
                    <button type="button" class="mayhen_gift_btn_add_item" onclick="addWishlistItem()">
                        + Thêm món quà mong ước
                    </button>
                </div>
            </div>
        </div>

        {{-- Footer Actions --}}
        <div class="mayhen_gift_form_footer">
            <a href="{{ route('account.invitations') }}" class="mayhen_gift_btn_secondary">
                ← Quay lại danh sách thiệp
            </a>
            <button type="submit" class="mayhen_gift_btn_primary">
                💾 Lưu Cấu Hình Hộp Mừng Cưới &amp; VietQR
            </button>
        </div>
    </form>
</div>

<style>
/* CSS thuần cho Studio Cấu hình Hộp Mừng Cưới */
.mayhen_gift_admin_container {
    max-width: 960px;
    margin: 0 auto;
    padding: 24px 16px 80px 16px;
    font-family: var(--mh-font-body, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif);
}
.mayhen_gift_admin_badge {
    display: inline-block;
    padding: 4px 12px;
    background: #fdf2f8;
    color: #be185d;
    font-size: 11px;
    font-weight: 700;
    border-radius: 20px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 8px;
}
.mayhen_gift_admin_title {
    font-size: 24px;
    font-weight: 700;
    color: #0f172a;
    margin: 0 0 8px 0;
}
.mayhen_gift_admin_subtitle {
    font-size: 14px;
    color: #64748b;
    margin: 0 0 24px 0;
    line-height: 1.6;
}
.mayhen_gift_alert {
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
}
.mayhen_gift_alert--success {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
}
.mayhen_gift_alert--danger {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
}
.mayhen_gift_card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    margin-bottom: 24px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.02);
}
.mayhen_gift_card_header {
    padding: 20px 24px;
    background: #fafaf9;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: flex-start;
    gap: 16px;
}
.mayhen_gift_card_icon {
    font-size: 24px;
    line-height: 1;
}
.mayhen_gift_card_title_row {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 12px;
}
.mayhen_gift_card_title {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}
.mayhen_gift_card_desc {
    font-size: 13px;
    color: #64748b;
    margin: 0;
}
.mayhen_gift_card_body {
    padding: 24px;
}
.mayhen_gift_switch {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}
.mayhen_gift_switch input {
    display: none;
}
.mayhen_gift_slider {
    width: 38px;
    height: 22px;
    background: #cbd5e1;
    border-radius: 20px;
    position: relative;
    transition: all 0.2s;
}
.mayhen_gift_slider:before {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #ffffff;
    top: 3px;
    left: 3px;
    transition: all 0.2s;
}
.mayhen_gift_switch input:checked + .mayhen_gift_slider {
    background: #be185d;
}
.mayhen_gift_switch input:checked + .mayhen_gift_slider:before {
    transform: translateX(16px);
}
.mayhen_gift_switch_text {
    font-size: 13px;
    font-weight: 600;
    color: #334155;
}
.mayhen_gift_style_grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 14px;
    margin-top: 8px;
}
.mayhen_gift_style_card {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    cursor: pointer;
    background: #ffffff;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
}
.mayhen_gift_style_card input {
    display: none;
}
.mayhen_gift_style_card.selected {
    border-color: #be185d;
    background: #fff5f8;
}
.mayhen_gift_style_header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.mayhen_gift_style_name {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
}
.mayhen_gift_style_badge {
    font-size: 10px;
    padding: 2px 8px;
    background: #fdf2f8;
    color: #be185d;
    border-radius: 10px;
    font-weight: 700;
}
.mayhen_gift_style_desc {
    font-size: 12px;
    color: #64748b;
    margin: 0;
    line-height: 1.5;
}
.mayhen_gift_form_group {
    margin-bottom: 18px;
}
.mayhen_gift_label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 6px;
}
.mayhen_gift_label_flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.mayhen_gift_help {
    font-size: 12px;
    color: #94a3b8;
}
.mayhen_gift_row_2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
@media (max-width: 640px) {
    .mayhen_gift_row_2col {
        grid-template-columns: 1fr;
    }
}
.mayhen_gift_input, .mayhen_gift_select, .mayhen_gift_textarea {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    box-sizing: border-box;
    background: #ffffff;
    transition: border-color 0.15s;
}
.mayhen_gift_input:focus, .mayhen_gift_select:focus, .mayhen_gift_textarea:focus {
    outline: none;
    border-color: #be185d;
}
.mayhen_gift_presets_bar {
    display: flex;
    gap: 8px;
    margin-bottom: 10px;
    flex-wrap: wrap;
}
.mayhen_gift_preset_chip {
    padding: 6px 12px;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all 0.15s;
}
.mayhen_gift_preset_chip:hover {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
}
.mayhen_gift_checkbox_row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 13px;
    color: #475569;
    cursor: pointer;
    line-height: 1.5;
}
.mayhen_gift_checkbox_row input {
    margin-top: 3px;
}
.mayhen_gift_qr_preview_box {
    display: flex;
    align-items: center;
    gap: 20px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px 20px;
    margin-top: 10px;
}
.mayhen_gift_qr_preview_img_wrap img {
    width: 110px;
    height: 110px;
    object-fit: contain;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 4px;
}
.mayhen_gift_qr_preview_title {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 4px;
}
.mayhen_gift_qr_preview_desc {
    font-size: 12px;
    color: #64748b;
    margin: 0;
    line-height: 1.5;
}
.mayhen_gift_upload_row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.mayhen_gift_file_input {
    font-size: 12px;
}
.mayhen_gift_upload_status {
    font-size: 12px;
    font-weight: 600;
    color: #be185d;
}
/* Wishlist items */
.mayhen_gift_wishlist_item_row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 2fr 40px;
    gap: 10px;
    align-items: flex-end;
    background: #fafaf9;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 10px;
}
@media (max-width: 768px) {
    .mayhen_gift_wishlist_item_row {
        grid-template-columns: 1fr 1fr;
    }
}
.mayhen_gift_mini_label {
    display: block;
    font-size: 11px;
    color: #64748b;
    margin-bottom: 4px;
    font-weight: 600;
}
.mayhen_gift_btn_delete_item {
    width: 36px;
    height: 38px;
    border-radius: 6px;
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: opacity 0.2s;
}
.mayhen_gift_btn_delete_item:hover {
    opacity: 0.85;
}
.mayhen_gift_btn_add_item {
    padding: 8px 16px;
    background: #f1f5f9;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    margin-top: 6px;
    transition: background 0.15s;
}
.mayhen_gift_btn_add_item:hover {
    background: #e2e8f0;
}
.mayhen_gift_form_footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    flex-wrap: wrap;
    gap: 12px;
}
.mayhen_gift_btn_secondary {
    padding: 10px 18px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}
.mayhen_gift_btn_primary {
    padding: 12px 24px;
    border-radius: 8px;
    background: #be185d;
    color: #ffffff;
    border: none;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(190, 24, 93, 0.25);
    transition: opacity 0.2s;
}
.mayhen_gift_btn_primary:hover {
    opacity: 0.92;
}
.required {
    color: #e11d48;
}
</style>

<script>
function updateStyleSelection(radio) {
    document.querySelectorAll('.mayhen_gift_style_card').forEach(card => card.classList.remove('selected'));
    radio.closest('.mayhen_gift_style_card').classList.add('selected');
}

function applyEtiquetteNote(content) {
    document.getElementById('etiquetteInput').value = content;
}

function syncGroomBankName(select) {
    const opt = select.options[select.selectedIndex];
    document.getElementById('groomBankNameInput').value = opt.getAttribute('data-name');
    updateGroomQrPreview();
}

function syncBrideBankName(select) {
    const opt = select.options[select.selectedIndex];
    document.getElementById('brideBankNameInput').value = opt.getAttribute('data-name');
    updateBrideQrPreview();
}

function updateGroomQrPreview() {
    const bin = document.getElementById('groomBankSelect').value;
    const acc = document.getElementById('groomAccountNumber').value.trim();
    const holder = encodeURIComponent(document.getElementById('groomAccountHolder').value.trim());
    const note = encodeURIComponent(document.getElementById('groomTransferNote').value.trim());
    const img = document.getElementById('groomQrPreviewImg');

    if (acc.length >= 4) {
        img.src = 'https://img.vietqr.io/image/' + bin + '-' + acc + '-compact2.png?amount=0&addInfo=' + note + '&accountName=' + holder;
    }
}

function updateBrideQrPreview() {
    const bin = document.getElementById('brideBankSelect').value;
    const acc = document.getElementById('brideAccountNumber').value.trim();
    const holder = encodeURIComponent(document.getElementById('brideAccountHolder').value.trim());
    const note = encodeURIComponent(document.getElementById('brideTransferNote').value.trim());
    const img = document.getElementById('brideQrPreviewImg');

    if (acc.length >= 4) {
        img.src = 'https://img.vietqr.io/image/' + bin + '-' + acc + '-compact2.png?amount=0&addInfo=' + note + '&accountName=' + holder;
    }
}

function uploadQr(side) {
    const input = document.getElementById(side + 'QrFileInput');
    const status = document.getElementById(side + 'UploadStatus');
    const previewImg = document.getElementById(side + 'QrPreviewImg');
    const customInp = document.getElementById(side + 'CustomQrInput');

    if (!input.files || !input.files[0]) return;

    status.textContent = 'Đang tải lên...';
    const formData = new FormData();
    formData.append('qr_file', input.files[0]);
    formData.append('side', side);

    fetch('{{ route("account.gift_registry.upload_qr", $invitation->id) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            status.textContent = '✓ Đã tải ảnh QR!';
            previewImg.src = data.url;
            customInp.value = data.url;
        } else {
            status.textContent = 'Lỗi: ' + (data.message || 'Thất bại');
        }
    })
    .catch(err => {
        status.textContent = 'Lỗi kết nối';
    });
}

let wishlistItemCount = {{ count($registryConfig['wishlist']['items'] ?? []) }};
function addWishlistItem() {
    const container = document.getElementById('wishlistItemsContainer');
    const idx = wishlistItemCount++;
    const row = document.createElement('div');
    row.className = 'mayhen_gift_wishlist_item_row';
    row.id = 'wishlistItemRow_' + idx;
    row.innerHTML = `
        <input type="hidden" name="wishlist_items[${idx}][id]" value="${idx + 1}">
        <input type="hidden" name="wishlist_items[${idx}][status]" value="available">
        <input type="hidden" name="wishlist_items[${idx}][reserved_by]" value="">
        <div class="mayhen_gift_wishlist_col_name">
            <label class="mayhen_gift_mini_label">Tên món quà</label>
            <input type="text" name="wishlist_items[${idx}][name]" class="mayhen_gift_input" placeholder="Tên món quà tân hôn..." required>
        </div>
        <div class="mayhen_gift_wishlist_col_cat">
            <label class="mayhen_gift_mini_label">Phân loại</label>
            <input type="text" name="wishlist_items[${idx}][category]" class="mayhen_gift_input" value="Gia dụng" placeholder="Gia dụng">
        </div>
        <div class="mayhen_gift_wishlist_col_price">
            <label class="mayhen_gift_mini_label">Giá ước tính</label>
            <input type="text" name="wishlist_items[${idx}][estimated_price]" class="mayhen_gift_input" placeholder="1.000.000 đ">
        </div>
        <div class="mayhen_gift_wishlist_col_link">
            <label class="mayhen_gift_mini_label">Link sản phẩm (Tùy chọn)</label>
            <input type="text" name="wishlist_items[${idx}][product_link]" class="mayhen_gift_input" placeholder="https://...">
        </div>
        <div class="mayhen_gift_wishlist_col_action">
            <button type="button" class="mayhen_gift_btn_delete_item" onclick="removeWishlistItem(${idx})" title="Xóa món này">✕</button>
        </div>
    `;
    container.appendChild(row);
}

function removeWishlistItem(idx) {
    const row = document.getElementById('wishlistItemRow_' + idx);
    if (row) row.remove();
}
</script>
@endsection

