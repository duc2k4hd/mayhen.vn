@php
    $topAnnouncement = app(\App\Services\SiteAnnouncementService::class)->getActiveAnnouncement(\App\Models\SiteAnnouncement::PLACEMENT_TOP_BAR);
@endphp

@if($topAnnouncement)
    @php
        $styles = $topAnnouncement->getTypeStyles();
    @endphp
    <div id="mayhen_announcement_{{ $topAnnouncement->id }}" class="mayhen_announcement_bar" style="background: {{ $styles['bg'] }}; border-bottom: 1px solid {{ $styles['border'] }}; color: {{ $styles['text'] }}; padding: 8px 16px; font-size: 13px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; display: flex; align-items: center; justify-content: center; position: relative; z-index: 99998; transition: all 0.3s ease;">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: center; gap: 10px; flex-wrap: wrap; text-align: center; padding-right: {{ $topAnnouncement->is_dismissible ? '32px' : '0' }};">
            <span style="background: {{ $styles['badge_bg'] }}; color: {{ $styles['badge_text'] }}; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px;">
                {{ $topAnnouncement->type_label }}
            </span>
            <span style="font-weight: 600;">{{ $topAnnouncement->title }}:</span>
            <span>{{ $topAnnouncement->content }}</span>

            @if(!empty($topAnnouncement->cta_text))
                <a href="{{ route('announcement.click', $topAnnouncement->id) }}" style="background: {{ $styles['border'] }}; color: #FFFFFF; text-decoration: none; padding: 3px 10px; border-radius: 4px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; margin-left: 6px; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">
                    <span>{{ $topAnnouncement->cta_text }}</span>
                    <span>&rarr;</span>
                </a>
            @endif
        </div>

        @if($topAnnouncement->is_dismissible)
            <button type="button" aria-label="Đóng thông báo" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 18px; line-height: 1; color: {{ $styles['text'] }}; cursor: pointer; opacity: 0.6; padding: 4px;" onclick="dismissAnnouncement({{ $topAnnouncement->id }})" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.6'">
                &times;
            </button>
        @endif
    </div>

    <script>
        (function() {
            var aid = {{ $topAnnouncement->id }};
            var key = 'mayhen_dismissed_announcement_' + aid;
            var bar = document.getElementById('mayhen_announcement_' + aid);

            if (localStorage.getItem(key)) {
                if (bar) bar.style.display = 'none';
                return;
            }

            // Ghi nhận lượt xem (impression) qua API
            try {
                fetch('{{ route("announcement.impression", $topAnnouncement->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                }).catch(function() {});
            } catch(e) {}
        })();

        function dismissAnnouncement(id) {
            var bar = document.getElementById('mayhen_announcement_' + id);
            if (bar) {
                bar.style.opacity = '0';
                setTimeout(function() {
                    bar.style.display = 'none';
                }, 250);
            }
            try {
                localStorage.setItem('mayhen_dismissed_announcement_' + id, '1');
            } catch(e) {}
        }
    </script>
@endif
