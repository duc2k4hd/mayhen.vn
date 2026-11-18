<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Wedding;
use Illuminate\Support\Collection;

class WeddingGuestMetricsService
{
    /**
     * Tính toán bộ chỉ số chuẩn duy nhất (Single Source of Truth) cho 1 Đám Cưới.
     *
     * Tách bạch tuyệt đối 4 đại lượng:
     * 1. Số lời mời trong danh sách (invited_links_count)
     * 2. Số suất dự kiến ban đầu (expected_seats_sum)
     * 3. Số người đã xác nhận tham dự (confirmed_people = adults + children, khử trùng đa sự kiện)
     * 4. Số người đã đến thực tế tại sảnh (checked_in_people)
     *
     * @return array{
     *     invited_links_count: int,
     *     expected_seats_sum: int,
     *     projected_banquet_seats: int,
     *     responded_invited_count: int,
     *     response_rate: int,
     *     confirmed_links_count: int,
     *     confirmed_adults: int,
     *     confirmed_children: int,
     *     confirmed_people: int,
     *     declined_links_count: int,
     *     declined_freed_seats: int,
     *     pending_links_count: int,
     *     public_walkin_responses_count: int,
     *     public_walkin_attending_people: int,
     *     checked_in_links_count: int,
     *     checked_in_people: int,
     *     opened_links_count: int,
     *     sent_links_count: int
     * }
     */
    public function forWedding(Wedding $wedding, ?int $eventId = null): array
    {
        $guests = $wedding->guests()
            ->with(['responses', 'response'])
            ->get();

        $allResponsesQuery = GuestResponse::where('wedding_id', $wedding->id);
        if ($eventId !== null) {
            $allResponsesQuery->where('event_id', $eventId);
        }
        $allResponses = $allResponsesQuery->get();

        return $this->computeFromCollections($guests, $allResponses, $eventId);
    }

    /**
     * Tính toán tổng hợp chuẩn cho nhiều Đám Cưới (dùng cho Tổng Quan Tài Khoản /tai-khoan).
     *
     * @param  array<int, int>|Collection<int, int>  $weddingIds
     * @return array{
     *     invited_links_count: int,
     *     expected_seats_sum: int,
     *     projected_banquet_seats: int,
     *     responded_invited_count: int,
     *     response_rate: int,
     *     confirmed_links_count: int,
     *     confirmed_adults: int,
     *     confirmed_children: int,
     *     confirmed_people: int,
     *     declined_links_count: int,
     *     declined_freed_seats: int,
     *     pending_links_count: int,
     *     public_walkin_responses_count: int,
     *     public_walkin_attending_people: int,
     *     checked_in_links_count: int,
     *     checked_in_people: int,
     *     opened_links_count: int,
     *     sent_links_count: int
     * }
     */
    public function forWeddingIds(array|Collection $weddingIds): array
    {
        $ids = collect($weddingIds)->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        if ($ids === []) {
            return $this->computeFromCollections(collect(), collect());
        }

        $guests = Guest::whereIn('wedding_id', $ids)
            ->with(['responses', 'response'])
            ->get();

        $responses = GuestResponse::whereIn('wedding_id', $ids)->get();

        return $this->computeFromCollections($guests, $responses);
    }

    /**
     * @param  Collection<int, Guest>  $guests
     * @param  Collection<int, GuestResponse>  $responses
     * @return array{
     *     invited_links_count: int,
     *     expected_seats_sum: int,
     *     projected_banquet_seats: int,
     *     responded_invited_count: int,
     *     response_rate: int,
     *     confirmed_links_count: int,
     *     confirmed_adults: int,
     *     confirmed_children: int,
     *     confirmed_people: int,
     *     declined_links_count: int,
     *     declined_freed_seats: int,
     *     pending_links_count: int,
     *     public_walkin_responses_count: int,
     *     public_walkin_attending_people: int,
     *     checked_in_links_count: int,
     *     checked_in_people: int,
     *     opened_links_count: int,
     *     sent_links_count: int
     * }
     */
    public function computeFromCollections(Collection $guests, Collection $responses, ?int $eventId = null): array
    {
        if ($eventId !== null) {
            $guests = $guests->filter(fn (Guest $g) => $g->isEventAllowed($eventId))->values();
        }

        $invitedLinksCount = $guests->count();
        $expectedSeatsSum = (int) $guests->sum(fn (Guest $g) => max(1, (int) ($g->expected_attendees ?: 1)));

        $responsesByGuest = $responses->whereNotNull('guest_id')->groupBy('guest_id');
        $walkInResponses = $responses->whereNull('guest_id');

        $respondedInvitedCount = 0;
        $confirmedLinksCount = 0;
        $confirmedAdults = 0;
        $confirmedChildren = 0;
        $declinedLinksCount = 0;
        $declinedFreedSeats = 0;
        $projectedBanquetSeats = 0;
        $checkedInLinksCount = 0;
        $checkedInPeople = 0;
        $openedLinksCount = 0;
        $sentLinksCount = 0;

        foreach ($guests as $guest) {
            if ($guest->isOpened()) {
                $openedLinksCount++;
            }
            if ($guest->is_sent) {
                $sentLinksCount++;
            }

            $guestResponses = $responsesByGuest->get($guest->id, collect());
            $expectedForGuest = max(1, (int) ($guest->expected_attendees ?: 1));

            if ($guestResponses->isNotEmpty()) {
                $respondedInvitedCount++;

                $attendingRows = $guestResponses->where('attending_status', 'attending');
                if ($attendingRows->isNotEmpty()) {
                    $confirmedLinksCount++;
                    // Nếu khách phản hồi nhiều buổi lễ trong cùng 1 đám cưới, lấy buổi lễ có số người đi đông nhất để không cộng trùng nhân khẩu
                    $bestRow = $attendingRows->sortByDesc(fn (GuestResponse $r) => ((int) $r->adults_count + (int) $r->children_count))->first();
                    $ad = max(0, (int) ($bestRow->adults_count ?? 0));
                    $ch = max(0, (int) ($bestRow->children_count ?? 0));
                    if ($ad + $ch < 1) {
                        $ad = 1;
                    }
                    $confirmedAdults += $ad;
                    $confirmedChildren += $ch;
                    $projectedBanquetSeats += ($ad + $ch);
                } elseif ($guestResponses->every(fn (GuestResponse $r) => $r->attending_status === 'declined')) {
                    $declinedLinksCount++;
                    $declinedFreedSeats += $expectedForGuest;
                } else {
                    $projectedBanquetSeats += $expectedForGuest;
                }
            } else {
                $projectedBanquetSeats += $expectedForGuest;
            }

            if ($guest->checked_in_at !== null) {
                $checkedInLinksCount++;
                $checkedInPeople += (int) ($guest->checked_in_attendees ?: $guest->actualAttendeesCount($eventId));
            }
        }

        $walkInAttendingRows = $walkInResponses->where('attending_status', 'attending');
        $walkInAdults = 0;
        $walkInChildren = 0;
        foreach ($walkInAttendingRows as $wRow) {
            $wAd = max(0, (int) ($wRow->adults_count ?? 0));
            $wCh = max(0, (int) ($wRow->children_count ?? 0));
            if ($wAd + $wCh < 1) {
                $wAd = 1;
            }
            $walkInAdults += $wAd;
            $walkInChildren += $wCh;
        }
        $walkInAttendingPeople = $walkInAdults + $walkInChildren;
        $walkInDeclinedCount = $walkInResponses->where('attending_status', 'declined')->count();

        $confirmedAdults += $walkInAdults;
        $confirmedChildren += $walkInChildren;
        $confirmedPeople = $confirmedAdults + $confirmedChildren;
        $projectedBanquetSeats += $walkInAttendingPeople;

        $pendingLinksCount = max(0, $invitedLinksCount - $respondedInvitedCount);
        $responseRate = $invitedLinksCount > 0
            ? min(100, (int) round(($respondedInvitedCount / $invitedLinksCount) * 100))
            : 0;

        return [
            'invited_links_count' => $invitedLinksCount,
            'expected_seats_sum' => $expectedSeatsSum,
            'projected_banquet_seats' => $projectedBanquetSeats,
            'responded_invited_count' => $respondedInvitedCount,
            'response_rate' => $responseRate,
            'confirmed_links_count' => $confirmedLinksCount + $walkInAttendingRows->count(),
            'confirmed_adults' => $confirmedAdults,
            'confirmed_children' => $confirmedChildren,
            'confirmed_people' => $confirmedPeople,
            'declined_links_count' => $declinedLinksCount + $walkInDeclinedCount,
            'declined_freed_seats' => $declinedFreedSeats,
            'pending_links_count' => $pendingLinksCount,
            'public_walkin_responses_count' => $walkInResponses->count(),
            'public_walkin_attending_people' => $walkInAttendingPeople,
            'checked_in_links_count' => $checkedInLinksCount,
            'checked_in_people' => $checkedInPeople,
            'opened_links_count' => $openedLinksCount,
            'sent_links_count' => $sentLinksCount,
        ];
    }
}
