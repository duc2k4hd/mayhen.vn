{{-- Section 6: Wedding Timeline - Variant B: Thẻ Mốc So Le (Alternating Cards) --}}
<section class="mayhen_invitation_timeline mayhen_invitation_timeline--cards mayhen_curated_craft_node" data-curated-section="timeline" data-mayhen-section="timeline">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Thẻ Mốc So Le (Mẫu 2/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="timeline" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_timeline_inner">
        <div class="mayhen_curated_craft_unit" data-craft-unit="timeline_header">
            <div class="mayhen_invitation_timeline_kicker">TIMELINE</div>
            <h2 class="mayhen_invitation_timeline_title">Chương Trình Buổi Tiệc</h2>
        </div>
        
        <div class="mayhen_invitation_timeline_alternating_grid">
            @php
                $timelineData = $extraData['timeline'] ?? [
                    ['time' => '17:30', 'title' => 'Đón tiếp quan khách'],
                    ['time' => '18:00', 'title' => 'Cử hành hôn lễ'],
                    ['time' => '18:30', 'title' => 'Khai tiệc mừng'],
                    ['time' => '20:00', 'title' => 'Chụp hình & Tiễn khách'],
                ];
            @endphp
            @foreach($timelineData as $idx => $item)
                <div class="mayhen_invitation_timeline_card_box mayhen_curated_craft_unit {{ $idx % 2 === 0 ? 'is-left' : 'is-right' }}" data-craft-unit="timeline_item_{{ $idx }}">
                    <div class="mayhen_invitation_card_badge_time" data-sync-target="timeline.{{ $idx }}.time">
                        {{ $item['time'] ?? '' }}
                    </div>
                    <div class="mayhen_invitation_card_text_content" data-sync-target="timeline.{{ $idx }}.title">
                        {{ $item['title'] ?? '' }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

