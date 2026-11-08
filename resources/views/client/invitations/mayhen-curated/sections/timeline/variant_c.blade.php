{{-- Section 6: Wedding Timeline - Variant C: Tối Giản Tinh Tế (Minimal Steps) --}}
<section class="mayhen_invitation_timeline mayhen_invitation_timeline--steps mayhen_curated_craft_node" data-curated-section="timeline" data-mayhen-section="timeline">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Tối Giản Tinh Tế (Mẫu 3/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="timeline" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_timeline_inner">
        <div class="mayhen_invitation_timeline_kicker">SCHEDULE</div>
        <h2 class="mayhen_invitation_timeline_title">Mốc Thời Gian</h2>
        
        <div class="mayhen_invitation_timeline_steps_wrap">
            @php
                $timelineData = $extraData['timeline'] ?? [
                    ['time' => '17:30', 'title' => 'Đón tiếp quan khách'],
                    ['time' => '18:00', 'title' => 'Cử hành hôn lễ'],
                    ['time' => '18:30', 'title' => 'Khai tiệc mừng'],
                    ['time' => '20:00', 'title' => 'Chụp hình & Tiễn khách'],
                ];
            @endphp
            @foreach($timelineData as $idx => $item)
                <div class="mayhen_invitation_timeline_step_row">
                    <span class="mayhen_invitation_step_num">0{{ $idx + 1 }}</span>
                    <span class="mayhen_invitation_step_time" data-sync-target="timeline.{{ $idx }}.time">{{ $item['time'] ?? '' }}</span>
                    <span class="mayhen_invitation_step_sep">&mdash;</span>
                    <span class="mayhen_invitation_step_title" data-sync-target="timeline.{{ $idx }}.title">{{ $item['title'] ?? '' }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

