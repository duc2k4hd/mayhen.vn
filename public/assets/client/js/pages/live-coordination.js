/**
 * BẢNG ĐIỀU KHIỂN NGÀY CƯỚI REALTIME (LIVE COMMAND CENTER)
 * Mây Hẹn (mayhen.vn) — Pure Native ES Module
 * Zero External Libraries — Auto-Polling Realtime Stats
 */

document.addEventListener('DOMContentLoaded', () => {
    const appEl = document.getElementById('mayhen_live_app');
    if (!appEl) return;

    const statsUrl = appEl.getAttribute('data-stats-url');
    if (!statsUrl) return;

    // DOM Elements - Metrics
    const valTotalExpected = document.getElementById('val_total_expected');
    const valRsvpCount = document.getElementById('val_rsvp_count');
    const valCheckedIn = document.getElementById('val_checked_in');
    const valArrivalRate = document.getElementById('val_arrival_rate');
    const valArrivalProgress = document.getElementById('val_arrival_progress');
    const valPendingCount = document.getElementById('val_pending_count');

    // DOM Elements - Tables & Feed
    const tablesGrid = document.getElementById('tables_grid');
    const tableSummaryCount = document.getElementById('table_summary_count');
    const feedList = document.getElementById('feed_list');

    let pollInterval = null;
    let isFetching = false;

    /* --------------------------------------------------------------------------
       1. Fetch Live Stats
       -------------------------------------------------------------------------- */
    async function fetchStats() {
        if (isFetching) return;
        isFetching = true;

        try {
            const res = await fetch(statsUrl, {
                headers: {
                    'Accept': 'application/json',
                },
            });

            if (!res.ok) return;

            const data = await res.json();
            updateMetrics(data.stats);
            updateTables(data.tables);
            updateFeed(data.recent_activities);
        } catch (err) {
            // Silently ignore network glitch, will retry in next poll
        } finally {
            isFetching = false;
        }
    }

    /* --------------------------------------------------------------------------
       2. Update Top Metric Cards
       -------------------------------------------------------------------------- */
    function updateMetrics(stats) {
        if (!stats) return;

        if (valTotalExpected) valTotalExpected.textContent = stats.total_expected_attendees;
        if (valRsvpCount) valRsvpCount.textContent = stats.rsvp_total_attendees;
        if (valCheckedIn) valCheckedIn.textContent = stats.checked_in_count;
        if (valArrivalRate) valArrivalRate.textContent = `${stats.arrival_rate}%`;
        if (valArrivalProgress) valArrivalProgress.style.width = `${stats.arrival_rate}%`;
        if (valPendingCount) valPendingCount.textContent = stats.pending_attendees;
    }

    /* --------------------------------------------------------------------------
       3. Update Table Seating Waves Grid
       -------------------------------------------------------------------------- */
    function updateTables(tables) {
        if (!tablesGrid || !tables) return;

        if (tableSummaryCount) {
            tableSummaryCount.textContent = `${tables.length} Bàn Tiệc`;
        }

        if (tables.length === 0) {
            tablesGrid.innerHTML = '<div class="mayhen_live_empty_tables">Chưa có dữ liệu bàn tiệc. Hãy phân chia bàn tiệc trong danh sách khách mời!</div>';
            return;
        }

        // Render each table card
        tablesGrid.innerHTML = tables.map(wave => {
            const isFull = wave.fill_rate >= 100;
            const guestPills = (wave.guests || []).map(g => `
                <span class="table_guest_pill ${g.is_checked_in ? 'is-present' : 'is-absent'}" title="${g.name} (${g.expected} người) - ${g.is_checked_in ? 'Đã vào bàn lúc ' + g.checked_in_time : 'Chưa tới'}">
                    ${g.is_checked_in ? '✓' : '○'} ${g.name}
                </span>
            `).join('');

            return `
                <div class="mayhen_live_table_card ${wave.is_unassigned ? 'is-unassigned' : ''}">
                    <div class="table_card_header">
                        <div class="table_card_title_wrap">
                            <h3 class="table_card_name">${wave.table_name}</h3>
                            ${wave.is_unassigned ? '<span class="table_unassigned_badge">Cần xếp bàn</span>' : ''}
                        </div>
                        <div class="table_card_headcount">
                            <span class="table_current_count">${wave.checked_in_count}</span>
                            <span class="table_total_count">/ ${wave.total_expected} ghế</span>
                        </div>
                    </div>

                    <div class="table_progress_track">
                        <div class="table_progress_fill ${isFull ? 'is-full' : ''}" style="width: ${wave.fill_rate}%;"></div>
                    </div>

                    <div class="table_card_guests">
                        ${guestPills}
                    </div>
                </div>
            `;
        }).join('');
    }

    /* --------------------------------------------------------------------------
       4. Update Live Arrival Activity Feed
       -------------------------------------------------------------------------- */
    function updateFeed(activities) {
        if (!feedList || !activities) return;

        if (activities.length === 0) {
            feedList.innerHTML = '<div class="mayhen_live_feed_empty">Chưa có lượt check-in nào. Khi khách tới quét mã, dữ liệu sẽ tự động xuất hiện tại đây.</div>';
            return;
        }

        feedList.innerHTML = activities.map(act => `
            <div class="mayhen_live_feed_item">
                <span class="feed_item_time">${act.time}</span>
                <div class="feed_item_body">
                    <div class="feed_item_guest">
                        ${act.salutation ? act.salutation + ' ' : ''}<strong>${act.name}</strong>
                        <span class="feed_item_attendees">(${act.attendees} khách)</span>
                    </div>
                    <div class="feed_item_table">
                        Đã vào bàn: <strong>${act.table_name}</strong>
                    </div>
                </div>
            </div>
        `).join('');
    }

    /* --------------------------------------------------------------------------
       5. Initialize Auto-Polling (Every 8 Seconds)
       -------------------------------------------------------------------------- */
    // Initial poll after 4 seconds
    setTimeout(fetchStats, 4000);

    // Continuous polling
    pollInterval = setInterval(fetchStats, 8000);

    // Stop polling if user tabs away, resume when tab active
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
        } else {
            fetchStats();
            if (!pollInterval) {
                pollInterval = setInterval(fetchStats, 8000);
            }
        }
    });
});

