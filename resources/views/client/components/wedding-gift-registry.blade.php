@php
    $giftConfig = $giftConfig ?? (\App\Services\WeddingGiftRegistryService::resolveGiftRegistryConfig($invitation ?? null));
    $wedding = $invitation->wedding ?? null;
    $isEmbed = request()->boolean('embed') || !empty($isEmbed);
    $boxStyle = $giftConfig['box_style'] ?? 'ribbon_box';
    $maskAccounts = !empty($giftConfig['mask_account_numbers']) && empty($guest);

    $maskNumber = function ($number) use ($maskAccounts) {
        $clean = trim($number);
        if (!$maskAccounts || strlen($clean) <= 4) {
            return $clean;
        }
        return substr($clean, 0, -4) . '••••';
    };
@endphp

@if(!empty($giftConfig['enabled']))
<section class="mayhen_invitation_gift mayhen_invitation_gift--{{ $boxStyle }}" id="section-gift" data-mayhen-section="gift">
    <div class="mayhen_invitation_gift_wrap">
        
        <!-- Header & Lời ngỏ lễ nghi -->
        <div class="mayhen_invitation_gift_header">
            @if(!empty($giftConfig['kicker']))
                <div class="mayhen_invitation_gift_kicker">{{ $giftConfig['kicker'] }}</div>
            @endif
            <h2 class="mayhen_invitation_gift_title">{{ $giftConfig['title'] }}</h2>
            @if(!empty($giftConfig['etiquette_note']))
                <p class="mayhen_invitation_gift_desc">{{ $giftConfig['etiquette_note'] }}</p>
            @endif
        </div>

        <!-- Khối Hộp Mừng Cưới 3D / Bì Thư Hoàng Gia -->
        <div class="mayhen_invitation_gift_box_container">
            @if($boxStyle === 'ribbon_box')
                <div class="mayhen_invitation_gift_box" id="mayhenGiftBoxTrigger">
                    <div class="mayhen_invitation_gift_box_lid">
                        <div class="mayhen_invitation_gift_ribbon"></div>
                        <div class="mayhen_invitation_gift_bow"></div>
                    </div>
                    <div class="mayhen_invitation_gift_box_body">
                        <div class="mayhen_invitation_gift_box_hint">
                            <span class="mayhen_invitation_gift_box_icon">🎁</span>
                            <span class="mayhen_invitation_gift_box_text">Chạm để mở Hộp Mừng Cưới</span>
                        </div>
                    </div>
                </div>
            @elseif($boxStyle === 'wax_envelope')
                <div class="mayhen_invitation_gift_envelope" id="mayhenGiftEnvelopeTrigger">
                    <div class="mayhen_invitation_gift_envelope_flap">
                        <div class="mayhen_invitation_gift_wax_seal">✦</div>
                    </div>
                    <div class="mayhen_invitation_gift_envelope_body">
                        <span class="mayhen_invitation_gift_envelope_text">Chạm để mở phong bì mừng cưới</span>
                    </div>
                </div>
            @endif

            <!-- Nội dung Thẻ Ngân Hàng Mừng Cưới (Bung ra khi mở hoặc hiển thị trực tiếp) -->
            <div class="mayhen_invitation_gift_content {{ $boxStyle === 'editorial_card' ? 'is-always-open' : '' }}" id="mayhenGiftContent">
                
                <!-- Thanh chuyển Tab nếu hiển thị song song -->
                @if(!empty($giftConfig['groom']['enabled']) && !empty($giftConfig['bride']['enabled']))
                    <div class="mayhen_invitation_gift_tabs">
                        <button type="button" class="mayhen_invitation_gift_tab_btn active" data-target="groom">
                            {{ $giftConfig['groom']['role_label'] ?? 'Chú Rể' }}
                        </button>
                        <button type="button" class="mayhen_invitation_gift_tab_btn" data-target="bride">
                            {{ $giftConfig['bride']['role_label'] ?? 'Cô Dâu' }}
                        </button>
                        @if(!empty($giftConfig['wishlist']['enabled']) && !empty($giftConfig['wishlist']['items']))
                            <button type="button" class="mayhen_invitation_gift_tab_btn" data-target="wishlist">
                                🎁 Quà Tân Hôn
                            </button>
                        @endif
                    </div>
                @endif

                <!-- Thẻ Tài Khoản Nhà Trai / Chú Rể -->
                @if(!empty($giftConfig['groom']['enabled']) && !empty($giftConfig['groom']['account_number']))
                    <div class="mayhen_invitation_gift_card mayhen_invitation_gift_card_groom active" id="giftCardGroom">
                        <div class="mayhen_invitation_gift_card_badge">
                            <span class="mayhen_invitation_gift_role_groom">{{ $giftConfig['groom']['role_label'] ?? 'Chú Rể' }}</span>
                        </div>

                        <!-- Mã QR VietQR -->
                        @if(!empty($giftConfig['groom']['qr_url']))
                            <div class="mayhen_invitation_gift_qr_wrap">
                                <img src="{{ $giftConfig['groom']['qr_url'] }}" alt="VietQR Chú Rể" class="mayhen_invitation_gift_qr_groom" data-sync-target="qr_groom_image" loading="lazy">
                                <div class="mayhen_invitation_gift_qr_sub">Quét mã VietQR bằng mọi ứng dụng ngân hàng</div>
                            </div>
                        @endif

                        <!-- Chi tiết tài khoản -->
                        <div class="mayhen_invitation_gift_details">
                            <div class="mayhen_invitation_gift_row">
                                <span class="mayhen_invitation_gift_label">Ngân hàng:</span>
                                <span class="mayhen_invitation_gift_val mayhen_invitation_gift_bank_name_groom" data-sync-target="groom_bank_name">{{ $giftConfig['groom']['bank_name'] }}</span>
                            </div>
                            <div class="mayhen_invitation_gift_row">
                                <span class="mayhen_invitation_gift_label">Chủ tài khoản:</span>
                                <span class="mayhen_invitation_gift_val mayhen_invitation_gift_account_holder_groom" data-sync-target="groom_account_name">{{ $giftConfig['groom']['account_holder'] }}</span>
                            </div>
                            <div class="mayhen_invitation_gift_row mayhen_invitation_gift_row_highlight">
                                <span class="mayhen_invitation_gift_label">Số tài khoản:</span>
                                <b class="mayhen_invitation_gift_val mayhen_invitation_gift_account_number_groom" data-sync-target="groom_bank_account">{{ $maskNumber($giftConfig['groom']['account_number']) }}</b>
                                <button type="button" class="mayhen_invitation_copy_btn" data-copy="{{ $giftConfig['groom']['account_number'] }}" title="Sao chép số tài khoản">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    Sao chép
                                </button>
                            </div>
                            @if(!empty($giftConfig['groom']['transfer_note']))
                                <div class="mayhen_invitation_gift_row">
                                    <span class="mayhen_invitation_gift_label">Nội dung CK:</span>
                                    <span class="mayhen_invitation_gift_val">{{ $giftConfig['groom']['transfer_note'] }}</span>
                                    <button type="button" class="mayhen_invitation_copy_btn" data-copy="{{ $giftConfig['groom']['transfer_note'] }}" title="Sao chép nội dung">
                                        Sao chép
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Thẻ Tài Khoản Nhà Gái / Cô Dâu -->
                @if(!empty($giftConfig['bride']['enabled']) && !empty($giftConfig['bride']['account_number']))
                    <div class="mayhen_invitation_gift_card mayhen_invitation_gift_card_bride {{ empty($giftConfig['groom']['enabled']) ? 'active' : '' }}" id="giftCardBride">
                        <div class="mayhen_invitation_gift_card_badge">
                            <span class="mayhen_invitation_gift_role_bride">{{ $giftConfig['bride']['role_label'] ?? 'Cô Dâu' }}</span>
                        </div>

                        <!-- Mã QR VietQR -->
                        @if(!empty($giftConfig['bride']['qr_url']))
                            <div class="mayhen_invitation_gift_qr_wrap">
                                <img src="{{ $giftConfig['bride']['qr_url'] }}" alt="VietQR Cô Dâu" class="mayhen_invitation_gift_qr_bride" data-sync-target="qr_bride_image" loading="lazy">
                                <div class="mayhen_invitation_gift_qr_sub">Quét mã VietQR bằng mọi ứng dụng ngân hàng</div>
                            </div>
                        @endif

                        <!-- Chi tiết tài khoản -->
                        <div class="mayhen_invitation_gift_details">
                            <div class="mayhen_invitation_gift_row">
                                <span class="mayhen_invitation_gift_label">Ngân hàng:</span>
                                <span class="mayhen_invitation_gift_val mayhen_invitation_gift_bank_name_bride" data-sync-target="bride_bank_name">{{ $giftConfig['bride']['bank_name'] }}</span>
                            </div>
                            <div class="mayhen_invitation_gift_row">
                                <span class="mayhen_invitation_gift_label">Chủ tài khoản:</span>
                                <span class="mayhen_invitation_gift_val mayhen_invitation_gift_account_holder_bride" data-sync-target="bride_account_name">{{ $giftConfig['bride']['account_holder'] }}</span>
                            </div>
                            <div class="mayhen_invitation_gift_row mayhen_invitation_gift_row_highlight">
                                <span class="mayhen_invitation_gift_label">Số tài khoản:</span>
                                <b class="mayhen_invitation_gift_val mayhen_invitation_gift_account_number_bride" data-sync-target="bride_bank_account">{{ $maskNumber($giftConfig['bride']['account_number']) }}</b>
                                <button type="button" class="mayhen_invitation_copy_btn" data-copy="{{ $giftConfig['bride']['account_number'] }}" title="Sao chép số tài khoản">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                    Sao chép
                                </button>
                            </div>
                            @if(!empty($giftConfig['bride']['transfer_note']))
                                <div class="mayhen_invitation_gift_row">
                                    <span class="mayhen_invitation_gift_label">Nội dung CK:</span>
                                    <span class="mayhen_invitation_gift_val">{{ $giftConfig['bride']['transfer_note'] }}</span>
                                    <button type="button" class="mayhen_invitation_copy_btn" data-copy="{{ $giftConfig['bride']['transfer_note'] }}" title="Sao chép nội dung">
                                        Sao chép
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Danh sách Món Quà Tân Hôn Mong Ước (Wishlist Registry - IDEA-F07) -->
                @if(!empty($giftConfig['wishlist']['enabled']) && !empty($giftConfig['wishlist']['items']))
                    <div class="mayhen_invitation_gift_card mayhen_invitation_wishlist_card" id="giftCardWishlist">
                        <div class="mayhen_invitation_wishlist_header">
                            <h3 class="mayhen_invitation_wishlist_title">{{ $giftConfig['wishlist']['title'] }}</h3>
                            <p class="mayhen_invitation_wishlist_sub">{{ $giftConfig['wishlist']['subtitle'] }}</p>
                        </div>
                        <div class="mayhen_invitation_wishlist_grid">
                            @foreach($giftConfig['wishlist']['items'] as $item)
                                <div class="mayhen_invitation_wishlist_item {{ $item['status'] === 'reserved' ? 'is-reserved' : '' }}">
                                    <div class="mayhen_invitation_wishlist_item_body">
                                        <div class="mayhen_invitation_wishlist_cat">{{ $item['category'] }}</div>
                                        <div class="mayhen_invitation_wishlist_name">{{ $item['name'] }}</div>
                                        @if(!empty($item['estimated_price']))
                                            <div class="mayhen_invitation_wishlist_price">{{ $item['estimated_price'] }}</div>
                                        @endif
                                        @if($item['status'] === 'reserved')
                                            <div class="mayhen_invitation_wishlist_reserved_tag">
                                                ✓ Đã có người gửi tặng ({{ $item['reserved_by'] }})
                                            </div>
                                        @else
                                            <button type="button" class="mayhen_invitation_wishlist_btn" onclick="openGiftReserveModal({{ $item['id'] }}, '{{ addslashes($item['name']) }}')">
                                                🎁 Tôi muốn tặng món này
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</section>

<!-- Toast thông báo sao chép -->
<div class="mayhen_gift_toast" id="mayhenGiftToast">Đã sao chép vào bộ nhớ tạm!</div>

<!-- Modal Đăng Ký Tặng Quà Tân Hôn (Wishlist Modal) -->
<div class="mayhen_gift_modal" id="mayhenGiftReserveModal" style="display: none;">
    <div class="mayhen_gift_modal_backdrop" onclick="closeGiftReserveModal()"></div>
    <div class="mayhen_gift_modal_content">
        <button type="button" class="mayhen_gift_modal_close" onclick="closeGiftReserveModal()">✕</button>
        <h3 class="mayhen_gift_modal_title">Đăng Ký Gửi Tặng Món Quà</h3>
        <p class="mayhen_gift_modal_sub" id="giftReserveItemName"></p>
        
        <form id="giftReserveForm" onsubmit="submitGiftReserve(event)">
            <input type="hidden" id="giftReserveItemId" value="">
            <div class="mayhen_gift_form_group">
                <label for="giftGuestName">Tên của bạn hoặc gia đình <span style="color:#e11d48">*</span></label>
                <input type="text" id="giftGuestName" required placeholder="Ví dụ: Hoàng Long & Mai Phương" class="mayhen_gift_input" value="{{ $guest->name ?? '' }}">
            </div>
            <div class="mayhen_gift_form_group">
                <label for="giftGuestPhone">Số điện thoại (để tiện liên hệ)</label>
                <input type="tel" id="giftGuestPhone" placeholder="0901234567" class="mayhen_gift_input" value="{{ $guest->phone ?? '' }}">
            </div>
            <div class="mayhen_gift_form_actions">
                <button type="button" class="mayhen_gift_btn_cancel" onclick="closeGiftReserveModal()">Hủy</button>
                <button type="submit" class="mayhen_gift_btn_submit" id="giftReserveSubmitBtn">Xác Nhận Tặng Quà</button>
            </div>
        </form>
    </div>
</div>

<style>
/* CSS thuần BEM Quiet Luxury cho Section 9: Hộp Mừng Cưới */
.mayhen_invitation_gift {
    padding: 60px 20px;
    background: transparent;
    font-family: inherit;
    text-align: center;
    position: relative;
    overflow: hidden;
}
.mayhen_invitation_gift_wrap {
    max-width: 680px;
    margin: 0 auto;
}
.mayhen_invitation_gift_kicker {
    font-size: 12px;
    letter-spacing: 3px;
    text-transform: uppercase;
    color: var(--mh-gold-dark, #9c7a4a);
    margin-bottom: 8px;
    font-weight: 600;
}
.mayhen_invitation_gift_title {
    font-family: 'Playfair Display', Georgia, serif;
    font-size: 26px;
    color: var(--mh-charcoal, #1f1d1a);
    margin: 0 0 12px 0;
    letter-spacing: 0.5px;
}
.mayhen_invitation_gift_desc {
    font-size: 14px;
    line-height: 1.6;
    color: #64748b;
    max-width: 520px;
    margin: 0 auto 30px auto;
}
/* Trigger Hộp Quà 3D */
.mayhen_invitation_gift_box {
    width: 220px;
    height: 140px;
    margin: 0 auto 20px auto;
    background: linear-gradient(135deg, #be185d 0%, #9d174d 100%);
    border-radius: 12px;
    position: relative;
    cursor: pointer;
    box-shadow: 0 15px 30px rgba(190, 24, 93, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.mayhen_invitation_gift_box:hover {
    transform: translateY(-4px) scale(1.02);
    box-shadow: 0 20px 35px rgba(190, 24, 93, 0.35);
}
.mayhen_invitation_gift_ribbon {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 50%;
    width: 24px;
    background: #fbbf24;
    transform: translateX(-50%);
}
.mayhen_invitation_gift_box_hint {
    position: relative;
    z-index: 2;
    color: #ffffff;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}
.mayhen_invitation_gift_box_icon {
    font-size: 24px;
}
/* Trigger Phong Bì Hoàng Gia */
.mayhen_invitation_gift_envelope {
    width: 240px;
    height: 140px;
    margin: 0 auto 20px auto;
    background: #fdfbf7;
    border: 1px solid #d8cebd;
    border-radius: 8px;
    position: relative;
    cursor: pointer;
    box-shadow: 0 10px 25px rgba(0,0,0,0.06);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease;
}
.mayhen_invitation_gift_wax_seal {
    width: 36px;
    height: 36px;
    background: #be185d;
    color: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px auto;
    font-size: 16px;
    box-shadow: 0 2px 8px rgba(190, 24, 93, 0.4);
}
.mayhen_invitation_gift_envelope_text {
    font-size: 12px;
    color: #475569;
    font-weight: 600;
}
/* Nội dung bung ra */
.mayhen_invitation_gift_content {
    display: none;
    animation: giftFadeIn 0.4s ease forwards;
}
.mayhen_invitation_gift_content.is-open,
.mayhen_invitation_gift_content.is-always-open {
    display: block;
}
@keyframes giftFadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}
/* Tabs */
.mayhen_invitation_gift_tabs {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.mayhen_invitation_gift_tab_btn {
    padding: 8px 18px;
    border-radius: 30px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}
.mayhen_invitation_gift_tab_btn.active {
    background: var(--mh-charcoal, #1f1d1a);
    color: #ffffff;
    border-color: var(--mh-charcoal, #1f1d1a);
}
/* Thẻ ngân hàng */
.mayhen_invitation_gift_card {
    display: none;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px 20px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
    text-align: center;
}
.mayhen_invitation_gift_card.active {
    display: block;
}
.mayhen_invitation_gift_card_badge {
    display: inline-block;
    padding: 4px 14px;
    background: #fdf2f8;
    color: #be185d;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 16px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.mayhen_invitation_gift_qr_wrap {
    margin-bottom: 20px;
}
.mayhen_invitation_gift_qr_wrap img {
    max-width: 200px;
    height: auto;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    padding: 6px;
    background: #ffffff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.mayhen_invitation_gift_qr_sub {
    font-size: 11px;
    color: #94a3b8;
    margin-top: 6px;
}
/* Details */
.mayhen_invitation_gift_details {
    max-width: 440px;
    margin: 0 auto;
    text-align: left;
    background: #f8fafc;
    border-radius: 10px;
    padding: 12px 16px;
}
.mayhen_invitation_gift_row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 13px;
    border-bottom: 1px dashed #e2e8f0;
    gap: 8px;
}
.mayhen_invitation_gift_row:last-child {
    border-bottom: none;
}
.mayhen_invitation_gift_label {
    color: #64748b;
    font-size: 12px;
}
.mayhen_invitation_gift_val {
    color: #1e293b;
    font-weight: 600;
    text-align: right;
    word-break: break-all;
}
.mayhen_invitation_copy_btn {
    padding: 4px 8px;
    border-radius: 4px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #334155;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    white-space: nowrap;
    transition: all 0.15s ease;
}
.mayhen_invitation_copy_btn:hover {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
}
/* Wishlist items */
.mayhen_invitation_wishlist_header {
    margin-bottom: 18px;
}
.mayhen_invitation_wishlist_title {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}
.mayhen_invitation_wishlist_sub {
    font-size: 12px;
    color: #64748b;
    margin: 0;
}
.mayhen_invitation_wishlist_grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
    text-align: left;
}
.mayhen_invitation_wishlist_item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px;
    transition: all 0.2s;
}
.mayhen_invitation_wishlist_item.is-reserved {
    opacity: 0.65;
    background: #f1f5f9;
}
.mayhen_invitation_wishlist_cat {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    color: #94a3b8;
    margin-bottom: 2px;
}
.mayhen_invitation_wishlist_name {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 4px;
}
.mayhen_invitation_wishlist_price {
    font-size: 12px;
    font-weight: 600;
    color: #be185d;
    margin-bottom: 8px;
}
.mayhen_invitation_wishlist_btn {
    width: 100%;
    padding: 6px 10px;
    background: #be185d;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 0.2s;
}
.mayhen_invitation_wishlist_btn:hover {
    opacity: 0.9;
}
.mayhen_invitation_wishlist_reserved_tag {
    font-size: 11px;
    color: #047857;
    font-weight: 600;
}
/* Toast */
.mayhen_gift_toast {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%) translateY(50px);
    background: rgba(15, 23, 42, 0.9);
    color: #ffffff;
    padding: 10px 20px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 600;
    opacity: 0;
    pointer-events: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 999999;
}
.mayhen_gift_toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}
/* Modal Wishlist */
.mayhen_gift_modal {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.mayhen_gift_modal_backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
}
.mayhen_gift_modal_content {
    position: relative;
    z-index: 2;
    background: #ffffff;
    border-radius: 14px;
    max-width: 420px;
    width: 100%;
    padding: 24px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
    text-align: left;
}
.mayhen_gift_modal_close {
    position: absolute;
    top: 14px;
    right: 14px;
    background: none;
    border: none;
    font-size: 16px;
    color: #94a3b8;
    cursor: pointer;
}
.mayhen_gift_modal_title {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 4px 0;
}
.mayhen_gift_modal_sub {
    font-size: 13px;
    color: #be185d;
    font-weight: 600;
    margin: 0 0 16px 0;
}
.mayhen_gift_form_group {
    margin-bottom: 12px;
}
.mayhen_gift_form_group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 4px;
}
.mayhen_gift_input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 13px;
    font-family: inherit;
    box-sizing: border-box;
}
.mayhen_gift_form_actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 18px;
}
.mayhen_gift_btn_cancel {
    padding: 8px 14px;
    background: #f1f5f9;
    color: #475569;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}
.mayhen_gift_btn_submit {
    padding: 8px 16px;
    background: #be185d;
    color: #ffffff;
    border: none;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Mở Hộp Quà 3D / Bì Thư
    const boxTrigger = document.getElementById('mayhenGiftBoxTrigger');
    const envTrigger = document.getElementById('mayhenGiftEnvelopeTrigger');
    const giftContent = document.getElementById('mayhenGiftContent');

    function openGiftContent() {
        if (giftContent) {
            giftContent.classList.add('is-open');
        }
        if (boxTrigger) boxTrigger.style.display = 'none';
        if (envTrigger) envTrigger.style.display = 'none';
    }

    if (boxTrigger) boxTrigger.addEventListener('click', openGiftContent);
    if (envTrigger) envTrigger.addEventListener('click', openGiftContent);

    // 2. Chuyển Tabs Chú Rể / Cô Dâu / Wishlist
    const tabBtns = document.querySelectorAll('.mayhen_invitation_gift_tab_btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const target = this.dataset.target;
            document.querySelectorAll('.mayhen_invitation_gift_card').forEach(card => card.classList.remove('active'));

            if (target === 'groom') {
                const c = document.getElementById('giftCardGroom');
                if (c) c.classList.add('active');
            } else if (target === 'bride') {
                const c = document.getElementById('giftCardBride');
                if (c) c.classList.add('active');
            } else if (target === 'wishlist') {
                const c = document.getElementById('giftCardWishlist');
                if (c) c.classList.add('active');
            }
        });
    });

    // 3. Sao chép số tài khoản & nội dung 1 chạm
    const copyBtns = document.querySelectorAll('.mayhen_invitation_copy_btn');
    const toast = document.getElementById('mayhenGiftToast');

    function showToast(text) {
        if (!toast) return;
        toast.textContent = text || 'Đã sao chép vào bộ nhớ tạm!';
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2200);
    }

    copyBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const textToCopy = this.getAttribute('data-copy');
            if (!textToCopy) return;

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textToCopy).then(() => showToast('Đã sao chép: ' + textToCopy));
            } else {
                const input = document.createElement('textarea');
                input.value = textToCopy;
                input.style.position = 'fixed';
                input.style.left = '-9999px';
                document.body.appendChild(input);
                input.focus();
                input.select();
                try {
                    document.execCommand('copy');
                    showToast('Đã sao chép: ' + textToCopy);
                } catch (err) {
                    showToast('Sao chép thất bại');
                }
                document.body.removeChild(input);
            }
        });
    });
});

// Modal Đăng ký Quà Tân Hôn
function openGiftReserveModal(itemId, itemName) {
    const modal = document.getElementById('mayhenGiftReserveModal');
    const idInp = document.getElementById('giftReserveItemId');
    const nameTxt = document.getElementById('giftReserveItemName');
    if (modal && idInp && nameTxt) {
        idInp.value = itemId;
        nameTxt.textContent = itemName;
        modal.style.display = 'flex';
    }
}

function closeGiftReserveModal() {
    const modal = document.getElementById('mayhenGiftReserveModal');
    if (modal) modal.style.display = 'none';
}

function submitGiftReserve(e) {
    e.preventDefault();
    const itemId = document.getElementById('giftReserveItemId').value;
    const guestName = document.getElementById('giftGuestName').value;
    const guestPhone = document.getElementById('giftGuestPhone').value;
    const btn = document.getElementById('giftReserveSubmitBtn');

    if (!itemId || !guestName) return;

    btn.disabled = true;
    btn.textContent = 'Đang ghi nhận...';

    const invitationId = '{{ $invitation->id ?? 0 }}';

    fetch('/api/thiep/' + invitationId + '/hop-mung-cuoi/nhan-qua', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            item_id: itemId,
            guest_name: guestName,
            guest_phone: guestPhone
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = 'Xác Nhận Tặng Quà';
        if (data.success) {
            alert(data.message);
            closeGiftReserveModal();
            window.location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.textContent = 'Xác Nhận Tặng Quà';
        alert('Không thể kết nối đến máy chủ, vui lòng thử lại sau.');
    });
}
</script>
@endif

