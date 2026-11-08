{{-- Section 6: Wedding Timeline - Variant A: Trục Chỉ Vàng (Golden Thread) --}}
<section class="mayhen_invitation_timeline mayhen_invitation_timeline--thread mayhen_curated_craft_node" data-curated-section="timeline" data-mayhen-section="timeline">
    @if(isset($showVariantControls) && $showVariantControls)
        <div class="mayhen_curated_section_badge">
            <span>Trục Chỉ Vàng (Mẫu 1/3)</span>
            <button type="button" class="mayhen_curated_switch_btn" data-switch-section="timeline" title="Đổi biến thể bố cục">↺ Đổi kiểu</button>
        </div>
    @endif
    <div class="mayhen_invitation_timeline_inner">
        <div class="mayhen_invitation_timeline_kicker">WEDDING TIMELINE</div>
        <h2 class="mayhen_invitation_timeline_title">Lịch Trình Hôn Lễ</h2>
        
        <div class="mayhen_invitation_timeline_list">
            @php
                $timelineData = $extraData['timeline'] ?? [
                    ['time' => '17:30', 'title' => 'Đón tiếp quan khách'],
                    ['time' => '18:00', 'title' => 'Cử hành hôn lễ'],
                    ['time' => '18:30', 'title' => 'Khai tiệc mừng'],
                    ['time' => '20:00', 'title' => 'Chụp hình & Tiễn khách'],
                ];
            @endphp
            @foreach($timelineData as $idx => $item)
                <div class="mayhen_invitation_timeline_item">
                    <div class="mayhen_invitation_timeline_time" data-sync-target="timeline.{{ $idx }}.time">
                        {{ $item['time'] ?? '' }}
                    </div>
                    <div class="mayhen_invitation_timeline_axis">
                        <span class="mayhen_invitation_timeline_dot"></span>
                        @if(! $loop->last)
                            <span class="mayhen_invitation_timeline_line"></span>
                        @endif
                    </div>
                    <div class="mayhen_invitation_timeline_text" data-sync-target="timeline.{{ $idx }}.title">
                        {{ $item['title'] ?? '' }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

