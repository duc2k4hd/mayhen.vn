{{-- Section 5: Events & Maps - Variant C: Cột Lễ & Tiệc Đôi (Dual Ceremony Split) --}}
<section class="mayhen_invitation_events mayhen_invitation_events--split mayhen_curated_craft_node" data-curated-section="events" data-mayhen-section="wedding_event">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Cột Lễ &amp; Tiệc Đôi (Mẫu 3/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="events" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_events_inner">
        <div class="mayhen_invitation_events_kicker">SCHEDULE &amp; VENUES</div>
        <h2 class="mayhen_invitation_events_title">Thông Tin Hôn Lễ</h2>
        
        <div class="mayhen_invitation_events_split_cards">
            {{-- Cột 1: Lễ Thành Hôn / Gia Tiên --}}
            <div class="mayhen_invitation_event_card_half">
                <span class="mayhen_invitation_event_tag">NGHI LỄ GIA TIÊN</span>
                <h3>Lễ Thành Hôn</h3>
                <div class="mayhen_invitation_time_row">
                    <span>Thời gian:</span>
                    <strong data-sync-target="ceremony_groom_time">{{ $ceremonyGroomTime }}</strong>
                </div>
                <div class="mayhen_invitation_venue_row">
                    <span>Địa điểm:</span>
                    <p data-sync-target="ceremony_groom_address">{{ $ceremonyGroomAddress }}</p>
                </div>
            </div>

            {{-- Cột 2: Tiệc Cưới Chính --}}
            <div class="mayhen_invitation_event_card_half mayhen_invitation_event_card_half--highlight">
                <span class="mayhen_invitation_event_tag">TIỆC MỪNG CHÍNH</span>
                <h3 data-sync-target="party_venue">{{ $partyVenue }}</h3>
                <div class="mayhen_invitation_time_row">
                    <span>Thời gian:</span>
                    <strong data-sync-target="party_time">{{ $partyTime }} &bull; {{ $weddingDateDigits }}</strong>
                </div>
                <div class="mayhen_invitation_venue_row">
                    <span>Địa chỉ:</span>
                    <p data-sync-target="event_party_address">{{ $partyAddress }}</p>
                </div>
            </div>
        </div>

        {{-- Khung Bản Đồ --}}
        <div class="mayhen_invitation_map_box">
            <iframe src="{{ $partyMapLink }}" class="mayhen_invitation_map_iframe" loading="lazy" allowfullscreen title="Bản đồ chỉ đường"></iframe>
            <div class="mayhen_invitation_map_footer_bar">
                <span class="mayhen_invitation_map_address_text" data-sync-target="event_party_address">📍 {{ $partyAddress }}</span>
                <a href="https://maps.google.com/?q={{ urlencode($partyVenue . ' ' . $partyAddress) }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_map_open_link">Chỉ đường Google Maps ↗</a>
            </div>
        </div>
    </div>
</section>

