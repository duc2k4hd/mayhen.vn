{{-- Section 5: Events & Maps - Variant A: Vé Mời Danh Dự (Boarding Pass Luxury) --}}
<section class="mayhen_invitation_events mayhen_invitation_events--ticket mayhen_curated_craft_node" data-curated-section="events" data-mayhen-section="wedding_event">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Vé Mời Boarding Pass (Mẫu 1/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="events" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_events_inner">
        <div class="mayhen_invitation_events_kicker">INVITATION TICKET</div>
        <h2 class="mayhen_invitation_events_title">Thư Mời Hôn Lễ</h2>
        
        <div class="mayhen_invitation_ticket_card">
            <div class="mayhen_invitation_ticket_main">
                <div class="mayhen_invitation_ticket_header">
                    <span class="mayhen_invitation_ticket_brand">MÂY HẸN &bull; WEDDING PASS</span>
                    <span class="mayhen_invitation_ticket_class">VIP GUEST</span>
                </div>
                <div class="mayhen_invitation_ticket_body">
                    <div class="mayhen_invitation_guest_badge">
                        Kính mời: <strong class="mayhen_invitation_guest_name">{{ $guestName }}</strong>
                    </div>
                    <div class="mayhen_invitation_ticket_details_grid">
                        <div class="mayhen_invitation_ticket_detail">
                            <span class="lbl">THỜI GIAN</span>
                            <span class="val" data-sync-target="party_time">{{ $partyTime }}</span>
                        </div>
                        <div class="mayhen_invitation_ticket_detail">
                            <span class="lbl">NGÀY CƯỚI</span>
                            <span class="val" data-sync-target="wedding_date_digits">{{ $weddingDateDigits }}</span>
                        </div>
                        <div class="mayhen_invitation_ticket_detail">
                            <span class="lbl">TRUNG TÂM TIỆC CƯỚI</span>
                            <span class="val" data-sync-target="party_venue">{{ $partyVenue }}</span>
                        </div>
                        <div class="mayhen_invitation_ticket_detail">
                            <span class="lbl">ĐỊA CHỈ</span>
                            <span class="val" data-sync-target="event_party_address">{{ $partyAddress }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mayhen_invitation_ticket_stub">
                <div class="mayhen_invitation_ticket_stub_notch_top"></div>
                <div class="mayhen_invitation_ticket_stub_notch_bottom"></div>
                <div class="mayhen_invitation_stub_barcode">|||| | ||||| || |||||| |</div>
                <div class="mayhen_invitation_stub_couple">{{ $groomName }} &amp; {{ $brideName }}</div>
            </div>
        </div>

        {{-- Khung Bản Đồ --}}
        @if($hasVenueInfo && !empty($partyMapLink))
            <div class="mayhen_invitation_map_box">
                <iframe src="{{ $partyMapLink }}" class="mayhen_invitation_map_iframe" loading="lazy" allowfullscreen title="Bản đồ chỉ đường"></iframe>
                <div class="mayhen_invitation_map_footer_bar">
                    <span class="mayhen_invitation_map_address_text" data-sync-target="event_party_address">📍 {{ $partyAddress }}</span>
                    <a href="https://maps.google.com/?q={{ urlencode($partyVenue . ' ' . $partyAddress) }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_map_open_link">Mở Google Maps ↗</a>
                </div>
            </div>
        @else
            <div class="mayhen_invitation_map_placeholder">
                <div class="mayhen_invitation_map_ph_icon">📍</div>
                <p class="mayhen_invitation_map_ph_title">Chưa có định vị bản đồ sảnh tiệc</p>
                <span class="mayhen_invitation_map_ph_hint">Bổ sung địa chỉ sảnh tiệc trong Studio để hiển thị bản đồ dẫn đường tương tác</span>
            </div>
        @endif
    </div>
</section>

