<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Invitation;
use App\Models\WeddingTable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EmergencyOfflinePackService
{
    /**
     * Tạo bản snapshot dữ liệu ngoại tuyến đầy đủ của thiệp cưới.
     */
    public function getOfflineSnapshot(Invitation $invitation): array
    {
        $wedding = $invitation->wedding;
        if (! $wedding) {
            throw new \RuntimeException('Thiệp cưới chưa được liên kết thông tin đám cưới.');
        }

        // 1. Dữ liệu tổng quan đám cưới
        $weddingSummary = [
            'groom_name' => $wedding->groom_name,
            'bride_name' => $wedding->bride_name,
            'wedding_date' => $wedding->wedding_date,
            'wedding_date_formatted' => $wedding->wedding_date ? Carbon::parse($wedding->wedding_date)->format('d/m/Y') : '',
            'venue_name' => $wedding->venue_name,
            'venue_address' => $wedding->venue_address,
            'slug' => $invitation->slug,
            'title' => $invitation->title,
        ];

        // 2. Danh sách bàn tiệc
        $tables = WeddingTable::where('wedding_id', $wedding->id)
            ->orderBy('order')
            ->orderBy('name')
            ->get()
            ->map(function (WeddingTable $t) {
                return [
                    'id' => $t->id,
                    'name' => $t->name,
                    'capacity' => $t->capacity ?: 10,
                    'side' => $t->side ?: 'general',
                    'notes' => $t->notes,
                ];
            })
            ->values()
            ->toArray();

        // 3. Danh sách khách mời
        $guests = Guest::where('wedding_id', $wedding->id)
            ->with('response')
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->map(function (Guest $g) {
                return [
                    'id' => $g->id,
                    'name' => $g->name,
                    'salutation' => $g->salutation ?: 'Quý khách',
                    'phone' => $g->phone ?: '',
                    'group_name' => $g->group_name ?: 'Khách mời chung',
                    'table_name' => $g->table_name ?: 'Chưa xếp bàn',
                    'table_id' => $g->table_id,
                    'expected_attendees' => (int) ($g->expected_attendees ?: 1),
                    'actual_attendees' => (int) $g->actualAttendeesCount(),
                    'is_attending' => $g->response ? ($g->response->attending_status === 'attending') : null,
                    'is_checked_in' => ! empty($g->checked_in_at),
                    'checked_in_at' => $g->checked_in_at ? Carbon::parse($g->checked_in_at)->format('H:i d/m') : null,
                    'guest_token' => $g->guest_token,
                ];
            })
            ->values()
            ->toArray();

        $totalGuests = count($guests);
        $checkedInCount = collect($guests)->where('is_checked_in', true)->count();
        $totalExpected = collect($guests)->sum('actual_attendees');

        return [
            'success' => true,
            'generated_at' => now()->toIso8601String(),
            'generated_at_human' => now()->format('H:i:s d/m/Y'),
            'version' => '1.0',
            'wedding' => $weddingSummary,
            'kpis' => [
                'total_guests' => $totalGuests,
                'checked_in_count' => $checkedInCount,
                'total_expected' => $totalExpected,
                'total_tables' => count($tables),
            ],
            'tables' => $tables,
            'guests' => $guests,
        ];
    }

    /**
     * Sinh tệp HTML độc lập chạy ngoại tuyến 100% không cần server (Single-File Offline Bundle).
     */
    public function generateStandaloneHtml(Invitation $invitation): string
    {
        $snapshot = $this->getOfflineSnapshot($invitation);
        $jsonPayload = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
        $coupleName = htmlspecialchars("{$snapshot['wedding']['groom_name']} & {$snapshot['wedding']['bride_name']}");
        $venue = htmlspecialchars($snapshot['wedding']['venue_name'] ?: 'Trung Tâm Tiệc Cưới');
        $date = htmlspecialchars($snapshot['wedding']['wedding_date_formatted'] ?: '');

        return <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Cổng Đón Khách Ngoại Tuyến — {$coupleName} | Mây Hẹn</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #F8F6F0;
            color: #1F1D1A;
            line-height: 1.5;
            padding-bottom: 80px;
        }
        .header {
            background: #1F1D1A;
            color: #FAF8F5;
            padding: 16px 20px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }
        .brand {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            color: #C5A880;
            text-transform: uppercase;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 999px;
            background: rgba(255,255,255,0.15);
            color: #FAF8F5;
        }
        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #FF9800;
        }
        .title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .meta {
            font-size: 12px;
            color: rgba(250,248,245,0.7);
        }
        .kpi-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            padding: 12px 16px;
            background: #FFFDF9;
            border-bottom: 1px solid #EAE3D9;
        }
        .kpi-card {
            background: #FAF8F5;
            border: 1px solid rgba(197, 168, 128, 0.3);
            border-radius: 8px;
            padding: 8px 10px;
            text-align: center;
        }
        .kpi-num {
            font-size: 17px;
            font-weight: 700;
            color: #1F1D1A;
        }
        .kpi-label {
            font-size: 10px;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .search-bar {
            padding: 12px 16px;
            background: #FFFFFF;
            border-bottom: 1px solid #EAE3D9;
            position: sticky;
            top: 86px;
            z-index: 90;
        }
        .search-input {
            width: 100%;
            padding: 12px 16px;
            font-size: 15px;
            border: 1.5px solid #C5A880;
            border-radius: 10px;
            background: #FFFDF9;
            color: #1F1D1A;
            outline: none;
        }
        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(197,168,128,0.25);
        }
        .guest-list {
            padding: 12px 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .guest-card {
            background: #FFFFFF;
            border: 1px solid #E8DFD3;
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }
        .guest-card.checked-in {
            background: #F4F8F4;
            border-color: #A3C9A3;
        }
        .guest-info {
            flex: 1;
            padding-right: 12px;
        }
        .guest-name {
            font-size: 15px;
            font-weight: 600;
            color: #1F1D1A;
            margin-bottom: 2px;
        }
        .guest-group {
            font-size: 11px;
            color: #8C6D3F;
            font-weight: 500;
        }
        .guest-table {
            display: inline-block;
            font-size: 12px;
            font-weight: 600;
            color: #1F1D1A;
            background: #F0EAE1;
            padding: 2px 8px;
            border-radius: 4px;
            margin-top: 4px;
        }
        .btn-checkin {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .btn-checkin.action-check {
            background: #1F1D1A;
            color: #FAF8F5;
        }
        .btn-checkin.action-undo {
            background: #E8F5E9;
            color: #2E7D32;
            border-color: #A5D6A7;
        }
        .bottom-dock {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: #FFFFFF;
            border-top: 1px solid #EAE3D9;
            padding: 10px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            box-shadow: 0 -4px 16px rgba(0,0,0,0.06);
        }
        .btn-export {
            padding: 8px 14px;
            background: #C5A880;
            color: #FFF;
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }
        .offline-notice {
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-top">
            <span class="brand">MÂY HẸN • OFFLINE CONCIERGE</span>
            <span class="status-badge" id="netStatus">
                <span class="status-dot"></span>
                <span id="netStatusText">Gói Ngoại Tuyến</span>
            </span>
        </div>
        <h1 class="title">{$coupleName}</h1>
        <div class="meta">{$date} • {$venue}</div>
    </div>

    <div class="kpi-row">
        <div class="kpi-card">
            <div class="kpi-num" id="kpiTotal">0</div>
            <div class="kpi-label">Tổng Khách</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-num" id="kpiChecked" style="color: #2E7D32;">0</div>
            <div class="kpi-label">Đã Đón</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-num" id="kpiTables">0</div>
            <div class="kpi-label">Số Bàn Tiệc</div>
        </div>
    </div>

    <div class="search-bar">
        <input type="text" id="searchInput" class="search-input" placeholder="🔍 Tìm theo Tên hoặc Số điện thoại..." autofocus>
    </div>

    <div class="guest-list" id="guestListContainer">
        <!-- Render bằng Pure JavaScript -->
    </div>

    <div class="bottom-dock">
        <div class="offline-notice">
            <span>Dữ liệu lưu ngoại tuyến an toàn trên thiết bị này.</span>
        </div>
        <button type="button" class="btn-export" id="btnExportCsv">Xuất Báo Cáo CSV</button>
    </div>

    <script id="offlineDataPayload" type="application/json">
        {$jsonPayload}
    </script>

    <script>
    (function() {
        'use strict';
        const rawJson = document.getElementById('offlineDataPayload').textContent;
        const initialData = JSON.parse(rawJson);
        const storageKey = 'mayhen_offline_checkins_' + initialData.wedding.slug;

        // Lấy danh sách ID đã check-in lưu cục bộ trong localStorage
        let localCheckins = {};
        try {
            localCheckins = JSON.parse(localStorage.getItem(storageKey) || '{}');
        } catch (e) {
            localCheckins = {};
        }

        // Đồng bộ trạng thái check-in cục bộ vào danh sách
        const guests = initialData.guests.map(g => {
            if (localCheckins[g.id]) {
                g.is_checked_in = true;
                g.checked_in_at = localCheckins[g.id];
            }
            return g;
        });

        const listContainer = document.getElementById('guestListContainer');
        const searchInput = document.getElementById('searchInput');
        const kpiTotal = document.getElementById('kpiTotal');
        const kpiChecked = document.getElementById('kpiChecked');
        const kpiTables = document.getElementById('kpiTables');
        const btnExportCsv = document.getElementById('btnExportCsv');

        kpiTotal.textContent = guests.length;
        kpiTables.textContent = initialData.tables.length;

        function updateCheckedKpi() {
            const count = guests.filter(g => g.is_checked_in).length;
            kpiChecked.textContent = count;
        }
        updateCheckedKpi();

        function renderGuests(items) {
            listContainer.innerHTML = '';
            if (!items.length) {
                listContainer.innerHTML = '<div style="text-align: center; padding: 40px 16px; color: #888;">Không tìm thấy khách mời nào phù hợp.</div>';
                return;
            }

            items.forEach(g => {
                const card = document.createElement('div');
                card.className = 'guest-card' + (g.is_checked_in ? ' checked-in' : '');
                card.id = 'guest-card-' + g.id;

                card.innerHTML = `
                    <div class="guest-info">
                        <div class="guest-name">\${g.name}</div>
                        <div class="guest-group">\${g.salutation} • \${g.group_name} (\${g.actual_attendees} suất)</div>
                        <div class="guest-table">🍽️ \${g.table_name}</div>
                    </div>
                    <div>
                        <button type="button" class="btn-checkin \${g.is_checked_in ? 'action-undo' : 'action-check'}" data-id="\${g.id}">
                            \${g.is_checked_in ? '✓ Đã Đón' : 'Đón Khách'}
                        </button>
                    </div>
                `;

                const btn = card.querySelector('.btn-checkin');
                btn.addEventListener('click', () => toggleCheckin(g, btn, card));
                listContainer.appendChild(card);
            });
        }

        function toggleCheckin(guest, btn, card) {
            guest.is_checked_in = !guest.is_checked_in;
            if (guest.is_checked_in) {
                const nowStr = new Date().toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
                guest.checked_in_at = nowStr;
                localCheckins[guest.id] = nowStr;
                card.classList.add('checked-in');
                btn.className = 'btn-checkin action-undo';
                btn.textContent = '✓ Đã Đón';
            } else {
                delete localCheckins[guest.id];
                guest.checked_in_at = null;
                card.classList.remove('checked-in');
                btn.className = 'btn-checkin action-check';
                btn.textContent = 'Đón Khách';
            }
            try {
                localStorage.setItem(storageKey, JSON.stringify(localCheckins));
            } catch (e) {}
            updateCheckedKpi();
        }

        // Tìm kiếm tức thời (Client-side Search)
        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();
            if (!query) {
                renderGuests(guests);
                return;
            }
            const filtered = guests.filter(g => {
                const nameMatch = g.name.toLowerCase().includes(query);
                const phoneMatch = g.phone ? g.phone.includes(query) : false;
                const tableMatch = g.table_name.toLowerCase().includes(query);
                return nameMatch || phoneMatch || tableMatch;
            });
            renderGuests(filtered);
        });

        // Xuất file CSV ngoại tuyến
        btnExportCsv.addEventListener('click', function() {
            let csv = "\\uFEFF"; // UTF-8 BOM
            csv += "ID,Khách Mời,Xưng Hô,Số Điện Thoại,Phân Nhóm,Bàn Tiệc,Số Suất,Đã Đón,Thời Gian Đón\\n";
            guests.forEach(g => {
                csv += `"\${g.id}","\${g.name}","\${g.salutation}","\${g.phone}","\${g.group_name}","\${g.table_name}","\${g.actual_attendees}","\${g.is_checked_in ? 'Đã đón' : 'Chưa đón'}","\${g.checked_in_at || ''}"\\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'Diem_Danh_Ngoai_Tuyen_' + initialData.wedding.slug + '.csv';
            link.click();
        });

        // Khởi tạo ban đầu
        renderGuests(guests);
    })();
    </script>
</body>
</html>
HTML;
    }

    /**
     * Đồng bộ danh sách khách check-in ngoại tuyến từ lễ tân lên hệ thống.
     */
    public function syncOfflineCheckins(Invitation $invitation, array $checkins): array
    {
        $wedding = $invitation->wedding;
        if (! $wedding) {
            throw new \RuntimeException('Thiệp cưới không tồn tại đám cưới.');
        }

        $syncedCount = 0;
        $failedCount = 0;
        $guestIds = array_keys($checkins);

        $guests = Guest::where('wedding_id', $wedding->id)
            ->whereIn('id', $guestIds)
            ->get();

        foreach ($guests as $guest) {
            try {
                $checkinTime = $checkins[$guest->id] ?? null;
                $timestamp = $checkinTime ? Carbon::parse($checkinTime) : now();

                // Chống ghi đè scan mới (G2-006 Deterministic Replay):
                // Nếu khách chưa check-in, hoặc thời điểm offline diễn ra sớm hơn, cập nhật mốc sớm nhất
                if ($guest->checked_in_at === null || $timestamp->lessThan($guest->checked_in_at)) {
                    $guest->checked_in_at = $timestamp;
                    $guest->checked_in_attendees = $guest->checked_in_attendees ?: $guest->actualAttendeesCount();
                    $guest->save();
                }

                $syncedCount++;
            } catch (\Throwable $e) {
                $failedCount++;
                Log::warning("Lỗi đồng bộ check-in offline cho khách #{$guest->id}: {$e->getMessage()}");
            }
        }

        AuditLogger::log(
            'OFFLINE_CHECKINS_SYNCED',
            'Invitation',
            $invitation->id,
            "Đã đồng bộ thành công {$syncedCount} lượt đón khách ngoại tuyến lên máy chủ.",
            ['wedding_id' => $wedding->id, 'synced_count' => $syncedCount, 'failed_count' => $failedCount]
        );

        return [
            'success' => true,
            'message' => "Đã đồng bộ thành công {$syncedCount} lượt đón khách vào hệ thống.",
            'synced_count' => $syncedCount,
            'failed_count' => $failedCount,
        ];
    }
}
