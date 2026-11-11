@php
    $storyConfig = \App\Services\LoveStoryTimelineService::resolveLoveStoryConfig($invitation ?? null);
    $availableIcons = \App\Services\LoveStoryTimelineService::getAvailableIcons();
@endphp

@if(($storyConfig['enabled'] ?? true) && !empty($storyConfig['milestones']))
<div class="mayhen_invitation_love_story_timeline" data-mayhen-component="love-story-timeline">
    <div class="mayhen_timeline_header">
        <span class="mayhen_timeline_kicker">Our Love Journey</span>
        <h2 class="mayhen_timeline_main_title">{{ $storyConfig['title'] }}</h2>
        @if(!empty($storyConfig['subtitle']))
            <p class="mayhen_timeline_subtitle">{{ $storyConfig['subtitle'] }}</p>
        @endif
        <div class="mayhen_timeline_header_divider">
            <span class="mayhen_timeline_crest">✦</span>
        </div>
    </div>

    <div class="mayhen_timeline_track">
        <div class="mayhen_timeline_spine" aria-hidden="true"></div>

        @foreach($storyConfig['milestones'] as $index => $item)
            @php
                $isEven = ($index % 2 === 0);
                $iconInfo = $availableIcons[$item['icon']] ?? ['symbol' => '♥', 'name' => 'Love'];
            @endphp
            <div class="mayhen_timeline_node {{ $isEven ? 'mayhen_node--left' : 'mayhen_node--right' }}" data-milestone-id="{{ $item['id'] }}">
                {{-- Nút mốc thời gian trung tâm --}}
                <div class="mayhen_timeline_anchor" aria-hidden="true">
                    <span class="mayhen_anchor_glow"></span>
                    <span class="mayhen_anchor_dot">
                        <span class="mayhen_anchor_icon">{{ $iconInfo['symbol'] }}</span>
                    </span>
                </div>

                {{-- Thẻ nội dung kỷ niệm --}}
                <div class="mayhen_timeline_card">
                    <div class="mayhen_timeline_date_badge">
                        <span>{{ $item['date_label'] }}</span>
                    </div>

                    @if(!empty($item['photo_url']) && !str_contains($item['photo_url'], 'placeholder'))
                        <div class="mayhen_timeline_photo_wrap">
                            <img src="{{ $item['photo_url'] }}" alt="{{ $item['title'] }}" class="mayhen_timeline_photo" loading="lazy">
                        </div>
                    @endif

                    <h3 class="mayhen_timeline_item_title">{{ $item['title'] }}</h3>
                    <p class="mayhen_timeline_item_desc">{{ $item['description'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>

<style>
/* ==========================================================================
   QUIET LUXURY LOVE STORY TIMELINE COMPONENT (ĐỀ ÁN 132 — MÂY HẸN)
   100% Pure Vanilla CSS — BEM Prefix mayhen_timeline_*
   ========================================================================== */
.mayhen_invitation_love_story_timeline {
    max-width: 860px;
    margin: 40px auto;
    padding: 20px 16px;
    font-family: 'Inter', -apple-system, sans-serif;
    color: #1F1D1A;
}

.mayhen_timeline_header {
    text-align: center;
    margin-bottom: 48px;
}
.mayhen_timeline_kicker {
    display: inline-block;
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: #9C7A4A;
    margin-bottom: 6px;
}
.mayhen_timeline_main_title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 2.2rem;
    font-weight: 600;
    color: #1F1D1A;
    margin: 0 0 8px;
}
.mayhen_timeline_subtitle {
    font-size: 0.92rem;
    color: #635E56;
    max-width: 540px;
    margin: 0 auto 16px;
    line-height: 1.6;
}
.mayhen_timeline_header_divider {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}
.mayhen_timeline_header_divider::before,
.mayhen_timeline_header_divider::after {
    content: '';
    width: 48px;
    height: 1px;
    background: linear-gradient(90deg, transparent, #C5A880, transparent);
}
.mayhen_timeline_crest {
    color: #9C7A4A;
    font-size: 14px;
    padding: 0 10px;
}

/* Timeline Track & Spine */
.mayhen_timeline_track {
    position: relative;
    padding: 20px 0;
}
.mayhen_timeline_spine {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 50%;
    width: 2px;
    background: linear-gradient(180deg, transparent, #C5A880 15%, #C5A880 85%, transparent);
    transform: translateX(-50%);
}

/* Timeline Node */
.mayhen_timeline_node {
    position: relative;
    display: flex;
    align-items: center;
    margin-bottom: 48px;
    width: 100%;
}
.mayhen_timeline_node:last-child {
    margin-bottom: 0;
}

/* Center Anchor */
.mayhen_timeline_anchor {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 32px;
    height: 32px;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: center;
}
.mayhen_anchor_glow {
    position: absolute;
    inset: -4px;
    border-radius: 50%;
    background: rgba(212, 175, 55, 0.25);
    animation: mayhenAnchorPulse 2.4s infinite ease-in-out;
}
@keyframes mayhenAnchorPulse {
    0%, 100% { transform: scale(0.9); opacity: 0.5; }
    50% { transform: scale(1.2); opacity: 0.9; }
}
.mayhen_anchor_dot {
    position: relative;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #FAF7F2;
    border: 2px solid #C5A880;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
}
.mayhen_anchor_icon {
    font-size: 11px;
    color: #9C7A4A;
}

/* Card */
.mayhen_timeline_card {
    position: relative;
    width: calc(50% - 40px);
    background: rgba(255, 255, 255, 0.92);
    border: 1px solid rgba(197, 168, 128, 0.35);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 20px rgba(31, 29, 26, 0.05);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease;
}
.mayhen_timeline_card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(31, 29, 26, 0.1);
    border-color: #C5A880;
}

.mayhen_node--left .mayhen_timeline_card {
    margin-right: auto;
    text-align: right;
}
.mayhen_node--right .mayhen_timeline_card {
    margin-left: auto;
    text-align: left;
}

.mayhen_timeline_date_badge {
    display: inline-block;
    padding: 3px 10px;
    background: rgba(197, 168, 128, 0.15);
    color: #9C7A4A;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    border-radius: 999px;
    margin-bottom: 10px;
}

.mayhen_timeline_photo_wrap {
    margin-bottom: 12px;
    overflow: hidden;
    border-radius: 8px;
    max-height: 200px;
    border: 1px solid rgba(197, 168, 128, 0.25);
}
.mayhen_timeline_photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.5s ease;
}
.mayhen_timeline_card:hover .mayhen_timeline_photo {
    transform: scale(1.04);
}

.mayhen_timeline_item_title {
    font-family: 'Cormorant Garamond', Georgia, serif;
    font-size: 1.3rem;
    font-weight: 600;
    color: #1F1D1A;
    margin: 0 0 6px;
}

.mayhen_timeline_item_desc {
    font-size: 0.85rem;
    line-height: 1.6;
    color: #635E56;
    margin: 0;
}

/* Responsive trên Mobile (< 640px): Chuyển thành timeline trục dọc bên trái */
@media (max-width: 640px) {
    .mayhen_timeline_spine {
        left: 20px;
        transform: none;
    }
    .mayhen_timeline_anchor {
        left: 20px;
        transform: translateX(-50%);
    }
    .mayhen_timeline_card {
        width: calc(100% - 50px);
        margin-left: auto !important;
        margin-right: 0 !important;
        text-align: left !important;
    }
    .mayhen_timeline_main_title {
        font-size: 1.8rem;
    }
}
</style>
@endif

