<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Điều Phối Chỗ Ngồi Sảnh Tiệc (Usher Tablet) • {{ $wedding->groom_name }} & {{ $wedding->bride_name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Cormorant+Garamond:wght@600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        body {
            margin: 0; padding: 0;
            background-color: #FAF8F5;
            color: #1F1D1A;
            font-family: 'Inter', -apple-system, sans-serif;
            min-height: 100vh;
        }
        .mayhen_usher_header {
            position: sticky; top: 0; z-index: 100;
            background: #FFFFFF;
            border-bottom: 1px solid #EFE8DD;
            padding: 14px 20px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        .mayhen_usher_title {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 22px; margin: 0; font-weight: 700; color: #1F1D1A;
        }
        .mayhen_usher_stats_bar {
            background: #1F1D1A; color: #FFFFFF;
            padding: 12px 20px;
            display: flex; justify-content: space-around; align-items: center;
            font-size: 13.5px;
        }
        .mayhen_usher_stat_item { text-align: center; }
        .mayhen_usher_stat_val { font-size: 18px; font-weight: 700; color: #C5A880; }
        .mayhen_filter_bar {
            padding: 12px 20px;
            background: #FAF8F5;
            display: flex; gap: 8px; overflow-x: auto;
            white-space: nowrap; -webkit-overflow-scrolling: touch;
        }
        .mayhen_filter_pill {
            background: #FFFFFF; border: 1px solid #D8CEBD;
            color: #4A453E; padding: 8px 16px; border-radius: 99px;
            font-size: 13.5px; font-weight: 600; cursor: pointer; text-decoration: none;
        }
        .mayhen_filter_pill.active {
            background: #1F1D1A; color: #FFFFFF; border-color: #1F1D1A;
        }
        .mayhen_grid {
            padding: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 16px;
        }
        .mayhen_card {
            background: #FFFFFF; border: 2px solid #EFE8DD;
            border-radius: 16px; padding: 18px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            transition: border-color 0.2s, transform 0.1s;
        }
        .mayhen_card.status_overflow { border-color: #EF4444; background: #FFF5F5; }
        .mayhen_card.status_ready_to_serve { border-color: #10B981; background: #F0FDF4; }
        .mayhen_card.status_available { border-color: #F59E0B; }
        .mayhen_card_name {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 24px; font-weight: 700; margin: 0 0 4px 0; color: #1F1D1A;
        }
        .mayhen_seat_badge {
            font-size: 15px; font-weight: 700; padding: 6px 12px; border-radius: 8px;
            display: inline-block; margin-top: 8px;
        }
        .mayhen_seat_available { background: #FEF3C7; color: #92400E; }
        .mayhen_seat_full { background: #DEF7EC; color: #046C4E; }
        .mayhen_seat_overflow { background: #FEE2E2; color: #991B1B; }
        .mayhen_seat_empty { background: #F3F4F6; color: #4B5563; }
    </style>
</head>
<body>
    {{-- Header --}}
    <header class="mayhen_usher_header">
        <div>
            <h1 class="mayhen_usher_title">
                {{ $wedding->groom_name }} & {{ $wedding->bride_name }}
            </h1>
            <span style="font-size: 12px; color: #8C827A; letter-spacing: 0.05em; text-transform: uppercase;">
                Điều Phối Chỗ Ngồi Sảnh Tiệc • Live Usher
            </span>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <span id="live_indicator" style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: #10B981; font-weight: 600;">
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981; display: inline-block; animation: pulse 1.5s infinite;"></span>
                <span>REALTIME</span>
            </span>
        </div>
    </header>

    {{-- Stats Bar --}}
    <div class="mayhen_usher_stats_bar">
        <div class="mayhen_usher_stat_item">
            <div style="font-size: 11px; opacity: 0.8; text-transform: uppercase;">Hiện diện</div>
            <div class="mayhen_usher_stat_val" id="stat_checked_in">{{ $overview['total_checked_in'] }}</div>
        </div>
        <div class="mayhen_usher_stat_item">
            <div style="font-size: 11px; opacity: 0.8; text-transform: uppercase;">Lấp đầy</div>
            <div class="mayhen_usher_stat_val" id="stat_percent">{{ $overview['venue_occupancy_percent'] }}%</div>
        </div>
        <div class="mayhen_usher_stat_item">
            <div style="font-size: 11px; opacity: 0.8; text-transform: uppercase;">Đủ mâm</div>
            <div class="mayhen_usher_stat_val" id="stat_ready" style="color: #34D399;">{{ $overview['ready_to_serve_tables'] }}</div>
        </div>
        <div class="mayhen_usher_stat_item">
            <div style="font-size: 11px; opacity: 0.8; text-transform: uppercase;">Còn chỗ</div>
            <div class="mayhen_usher_stat_val" id="stat_active" style="color: #FBBF24;">{{ $overview['active_tables'] }}</div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="mayhen_filter_bar">
        <button type="button" class="mayhen_filter_pill active" onclick="filterZone('all', this)">Tất Cả Bàn</button>
        @foreach($allZones as $zone)
            <button type="button" class="mayhen_filter_pill" onclick="filterZone('{{ $zone }}', this)">{{ $zone }}</button>
        @endforeach
    </div>

    {{-- Grid Bàn Tiệc --}}
    <div class="mayhen_grid" id="tables_grid">
        @foreach($tables as $table)
            <div class="mayhen_card status_{{ $table['status'] }}" data-zone="{{ $table['zone'] }}">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="mayhen_card_name">{{ $table['name'] }}</div>
                        <div style="font-size: 12.5px; color: #8C827A;">Khu: {{ $table['zone'] }}</div>
                    </div>
                    <div style="font-size: 13px; font-weight: 700; color: #555;">
                        {{ $table['checked_in_count'] }} / {{ $table['capacity'] }}
                    </div>
                </div>

                <div>
                    @if($table['status'] === 'overflow')
                        <div class="mayhen_seat_badge mayhen_seat_overflow">
                            QUÁ TẢI (+{{ $table['overflow_count'] }} người)
                        </div>
                    @elseif($table['status'] === 'ready_to_serve')
                        <div class="mayhen_seat_badge mayhen_seat_full">
                            ĐỦ MÂM (100%)
                        </div>
                    @elseif($table['status'] === 'available')
                        <div class="mayhen_seat_badge mayhen_seat_available">
                            CÒN {{ $table['available_seats'] }} GHẾ TRỐNG
                        </div>
                    @else
                        <div class="mayhen_seat_badge mayhen_seat_empty">
                            BÀN TRỐNG ({{ $table['capacity'] }} chỗ)
                        </div>
                    @endif
                </div>

                {{-- Khách trong bàn --}}
                <div style="margin-top: 14px; padding-top: 10px; border-top: 1px dashed rgba(0,0,0,0.1); font-size: 12.5px; color: #4A453E;">
                    @if(empty($table['guests']))
                        <div style="color: #9CA3AF; font-style: italic;">Chưa có khách check-in</div>
                    @else
                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            @foreach($table['guests'] as $guest)
                                @if($guest['is_checked_in'])
                                    <span style="background: #FFFFFF; border: 1px solid #D8CEBD; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                                        {{ $guest['name'] }} ({{ $guest['attendees'] }})
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    {{-- Script Realtime Polling --}}
    <script>
        let currentFilter = 'all';

        function filterZone(zone, btn) {
            currentFilter = zone;
            document.querySelectorAll('.mayhen_filter_pill').forEach(el => el.classList.remove('active'));
            if (btn) btn.classList.add('active');

            const cards = document.querySelectorAll('.mayhen_card');
            cards.forEach(card => {
                if (zone === 'all' || card.dataset.zone === zone) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Polling feed mỗi 2.5 giây
        const feedUrl = "{{ route('table_capacity.feed', $invitation->slug) }}";
        setInterval(async () => {
            try {
                const res = await fetch(feedUrl, { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                if (data.success && data.overview) {
                    document.getElementById('stat_checked_in').textContent = data.overview.total_checked_in;
                    document.getElementById('stat_percent').textContent = data.overview.venue_occupancy_percent + '%';
                    document.getElementById('stat_ready').textContent = data.overview.ready_to_serve_tables;
                    document.getElementById('stat_active').textContent = data.overview.active_tables;
                }
            } catch (err) {
                // Im lặng khi mạng chập chờn
            }
        }, 2500);
    </script>
</body>
</html>

