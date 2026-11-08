{{-- Section 5: Events & Maps - Variant B: Thư Mời Niêm Phong (Wax Seal Letter) --}}
<section class="mayhen_invitation_events mayhen_invitation_events--wax mayhen_curated_craft_node" data-curated-section="events" data-mayhen-section="wedding_event">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Thư Mời Niêm Phong Sáp (Mẫu 2/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="events" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_events_inner">
        <div class="mayhen_invitation_events_kicker">TRÂN TRỌNG KÍNH MỜI</div>
        <h2 class="mayhen_invitation_events_title">Tiệc Mừng Tân Hôn</h2>
        
        <div class="mayhen_invitation_wax_envelope_card">
            <div class="mayhen_invitation_envelope_top_flap">
                <img src="{{ $assetBase }}/wax-seal.svg" alt="Wax Seal" class="mayhen_invitation_wax_seal_big">
            </div>
            <div class="mayhen_invitation_envelope_letter_body">
                <p class="mayhen_invitation_guest_salut">{{ $guestSalutation }}</p>
                <h3 class="mayhen_invitation_guest_name">{{ $guestName }}</h3>
                <p class="mayhen_invitation_party_intro">Tới dự bữa cơm thân mật chung vui cùng gia đình chúng tôi tại</p>
                
                <h4 class="mayhen_invitation_party_venue" data-sync-target="party_venue">{{ $partyVenue }}</h4>
                <p class="mayhen_invitation_party_address" data-sync-target="event_party_address">{{ $partyAddress }}</p>
                
                <div class="mayhen_invitation_party_time_pill">
                    <span>Vào lúc <strong data-sync-target="party_time">{{ $partyTime }}</strong></span>
                    <span>Ngày <strong data-sync-target="wedding_date_digits">{{ $weddingDateDigits }}</strong></span>
                </div>
            </div>
        </div>

        {{-- Khung Bản Đồ --}}
        @if($hasVenueInfo && !empty($partyMapLink))
            <div class="mayhen_invitation_map_box">
                <iframe src="{{ $partyMapLink }}" class="mayhen_invitation_map_iframe" loading="lazy" allowfullscreen title="Bản đồ chỉ đường"></iframe>
                <div class="mayhen_invitation_map_footer_bar">
                    <span class="mayhen_invitation_map_address_text" data-sync-target="event_party_address">📍 {{ $partyAddress }}</span>
                    <a href="https://maps.google.com/?q={{ urlencode($partyVenue . ' ' . $partyAddress) }}" target="_blank" rel="noopener noreferrer" class="mayhen_invitation_map_open_link">Chỉ đường Google Maps ↗</a>
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

