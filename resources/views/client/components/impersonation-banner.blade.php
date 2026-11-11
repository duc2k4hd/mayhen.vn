@if(isset($impersonationSession) && $impersonationSession)
<aside class="mayhen_impersonation_banner" role="alert" style="background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); border-bottom: 2px solid #D97706; color: #78350F; padding: 10px 16px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; font-size: 13px; position: sticky; top: 0; z-index: 999999; box-shadow: 0 4px 12px rgba(217, 119, 6, 0.15);">
    <div style="max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <span style="background: #D97706; color: #FFFFFF; font-weight: 700; font-size: 11px; padding: 3px 8px; border-radius: 4px; text-transform: uppercase; letter-spacing: 0.5px; display: inline-flex; align-items: center; gap: 4px;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Hỗ Trợ Kỹ Thuật An Toàn
            </span>
            <span>
                Admin <strong>{{ $impersonationSession['admin_email'] }}</strong> đang xem dưới góc nhìn của <strong>{{ auth()->user()?->name ?? 'Khách hàng' }}</strong> ({{ auth()->user()?->email }}).
            </span>
            @if(!empty($impersonationSession['ticket']))
                <span style="background: rgba(120, 53, 15, 0.1); padding: 2px 6px; border-radius: 4px; font-size: 12px;">
                    Ticket: <strong>#{{ $impersonationSession['ticket'] }}</strong>
                </span>
            @endif
            <span style="font-size: 12px; color: #92400E; display: inline-flex; align-items: center; gap: 4px;">
                ⏳ Còn lại: <strong id="mayhen_impersonation_timer" style="font-family: monospace; font-size: 13px;">--:--</strong>
            </span>
        </div>

        <form action="{{ route('client.impersonation.leave') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" style="background: #B45309; color: #FFFFFF; border: none; padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; transition: background 0.2s; display: inline-flex; align-items: center; gap: 6px;" onmouseover="this.style.background='#92400E'" onmouseout="this.style.background='#B45309'">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Thoát Chế Độ Hỗ Trợ
            </button>
        </form>
    </div>

    <script>
        (function() {
            var expiresAt = {{ $impersonationSession['expires_at'] }} * 1000;
            var timerEl = document.getElementById('mayhen_impersonation_timer');

            function updateTimer() {
                var now = new Date().getTime();
                var diff = Math.floor((expiresAt - now) / 1000);

                if (diff <= 0) {
                    if (timerEl) timerEl.innerText = 'Hết hạn';
                    window.location.reload();
                    return;
                }

                var minutes = Math.floor(diff / 60);
                var seconds = diff % 60;
                var formatted = (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;

                if (timerEl) {
                    timerEl.innerText = formatted;
                }
            }

            updateTimer();
            setInterval(updateTimer, 1000);
        })();
    </script>
</aside>
@endif

