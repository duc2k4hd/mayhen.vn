<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Wedding;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LiveCoordinationController extends Controller
{
    /**
     * Bảng điều khiển ngày cưới thời gian thực (Live Command Center).
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $data = $this->gatherLiveStats($wedding, $invitation);

        return view('client.pages.live-coordination', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'stats' => $data['stats'],
            'tableWaves' => $data['tables'],
            'recentActivities' => $data['recent_activities'],
            'checkinPin' => $this->getReceptionistPin($invitation),
        ]);
    }

    /**
     * API Polling dữ liệu thời gian thực (gọi mỗi 8-10 giây để cập nhật mà không reload).
     */
    public function liveStats(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $data = $this->gatherLiveStats($wedding, $invitation);

        return response()->json($data);
    }

    /**
     * Thu thập toàn bộ thống kê sĩ số và trạng thái các bàn tiệc.
     *
     * @return array<string, mixed>
     */
    private function gatherLiveStats(Wedding $wedding, Invitation $invitation): array
    {
        $guests = $wedding->guests()->with('response')->get();

        $totalInvitedGuests = $guests->count();
        $totalExpectedAttendees = (int) $guests->sum('expected_attendees');

        // RSVP metrics
        $rsvpAttending = $wedding->guestResponses()->where('attending_status', 'attending')->count();
        $rsvpTotalAttendees = (int) ($wedding->guestResponses()->where('attending_status', 'attending')->sum('adults_count')
            + $wedding->guestResponses()->where('attending_status', 'attending')->sum('children_count'));

        // Check-in metrics
        $checkedInGuests = $guests->whereNotNull('checked_in_at');
        $checkedInCount = (int) $checkedInGuests->sum(function (Guest $g) {
            return $g->checked_in_attendees ?: $g->expected_attendees;
        });
        $checkedInGuestsCount = $checkedInGuests->count();

        // Tỷ lệ đón khách (%)
        $arrivalRate = $totalExpectedAttendees > 0
            ? (int) min(100, round(($checkedInCount / $totalExpectedAttendees) * 100))
            : 0;

        // Gom nhóm theo bàn tiệc (Table Seating Wave)
        $groupedByTable = $guests->groupBy(function (Guest $g) {
            return ! empty($g->table_name) ? trim($g->table_name) : 'Chưa xếp bàn';
        });

        $tableWaves = [];
        foreach ($groupedByTable as $tableName => $tableGuests) {
            $tableExpected = (int) $tableGuests->sum('expected_attendees');
            $tableCheckedIn = (int) $tableGuests->whereNotNull('checked_in_at')->sum(function (Guest $g) {
                return $g->checked_in_attendees ?: $g->expected_attendees;
            });
            $fillRate = $tableExpected > 0 ? (int) min(100, round(($tableCheckedIn / $tableExpected) * 100)) : 0;

            $tableWaves[] = [
                'table_name' => $tableName,
                'total_expected' => $tableExpected,
                'checked_in_count' => $tableCheckedIn,
                'fill_rate' => $fillRate,
                'is_unassigned' => ($tableName === 'Chưa xếp bàn'),
                'guests' => $tableGuests->map(function (Guest $g) {
                    return [
                        'id' => $g->id,
                        'name' => $g->name,
                        'salutation' => $g->salutation,
                        'expected' => $g->expected_attendees,
                        'is_checked_in' => $g->checked_in_at !== null,
                        'checked_in_time' => $g->checked_in_at ? $g->checked_in_at->format('H:i') : null,
                    ];
                })->values()->all(),
            ];
        }

        // Sắp xếp các bàn có tên lên trước, chưa xếp bàn xuống cuối
        usort($tableWaves, function ($a, $b) {
            if ($a['is_unassigned']) {
                return 1;
            }
            if ($b['is_unassigned']) {
                return -1;
            }

            return strnatcasecmp($a['table_name'], $b['table_name']);
        });

        // Hoạt động mới nhất
        $recentActivities = $wedding->guests()
            ->whereNotNull('checked_in_at')
            ->orderByDesc('checked_in_at')
            ->limit(15)
            ->get()
            ->map(function (Guest $g) {
                return [
                    'id' => $g->id,
                    'name' => $g->name,
                    'salutation' => $g->salutation,
                    'table_name' => $g->table_name ?: 'Chưa xếp bàn',
                    'attendees' => $g->checked_in_attendees ?: $g->expected_attendees,
                    'time' => $g->checked_in_at ? $g->checked_in_at->format('H:i') : '',
                ];
            })->values()->all();

        return [
            'stats' => [
                'total_invited_guests' => $totalInvitedGuests,
                'total_expected_attendees' => $totalExpectedAttendees,
                'rsvp_attending_count' => $rsvpAttending,
                'rsvp_total_attendees' => $rsvpTotalAttendees,
                'checked_in_count' => $checkedInCount,
                'checked_in_guests_count' => $checkedInGuestsCount,
                'arrival_rate' => $arrivalRate,
                'pending_attendees' => max(0, $totalExpectedAttendees - $checkedInCount),
            ],
            'tables' => $tableWaves,
            'recent_activities' => $recentActivities,
        ];
    }

    private function authorizeOwner(Wedding $wedding): void
    {
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        $ownedSlugs = (array) request()->session()->get('owned_invitation_slugs', []);
        if ($ownedSlugs !== [] && $wedding->invitations()->whereIn('slug', $ownedSlugs)->exists()) {
            return;
        }

        abort(404);
    }

    private function getReceptionistPin(Invitation $invitation): string
    {
        $config = is_array($invitation->custom_config)
            ? $invitation->custom_config
            : (json_decode((string) $invitation->custom_config, true) ?? []);

        if (! empty($config['checkin_pin'])) {
            return (string) $config['checkin_pin'];
        }

        $wedding = $invitation->wedding;
        if ($wedding && $wedding->wedding_date) {
            return $wedding->wedding_date->format('dmy');
        }

        return str_pad((string) ($invitation->id * 7919 % 1000000), 6, '0', STR_PAD_LEFT);
    }
}
