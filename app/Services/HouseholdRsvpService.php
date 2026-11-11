<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Wedding;
use App\Models\WeddingTable;

class HouseholdRsvpService
{
    /**
     * Tính toán ma trận số liệu khách mời và điều phối hộ gia đình (Mục 2.4 DOCX).
     *
     * @return array<string, mixed>
     */
    public static function computeMetrics(Wedding $wedding): array
    {
        $guests = Guest::with(['response', 'table'])
            ->where('wedding_id', $wedding->id)
            ->get();

        $totalInvitations = $guests->count();

        $totalExpectedAttendees = 0;
        $confirmedAdults = 0;
        $confirmedChildren = 0;
        $totalAttendingGuests = 0;
        $totalDeclinedGuests = 0;
        $totalUncertainGuests = 0;
        $totalPendingGuests = 0;

        // Phân loại hộ gia đình
        $individualCount = 0; // 1 người
        $coupleCount = 0;     // 2 người (+1)
        $familyCount = 0;     // 3+ người

        // Phân bổ nhà trai / nhà gái
        $groomSideGuests = 0;
        $brideSideGuests = 0;
        $mutualFriendsGuests = 0;

        // Thống kê ẩm thực & ăn kiêng
        $vegetarianCount = 0;
        $dietaryList = [];

        foreach ($guests as $guest) {
            $maxOrExpected = (int) ($guest->max_attendees ?: ($guest->expected_attendees ?: 1));
            $totalExpectedAttendees += $maxOrExpected;

            // Phân loại lời mời
            if ($maxOrExpected <= 1) {
                $individualCount++;
            } elseif ($maxOrExpected === 2) {
                $coupleCount++;
            } else {
                $familyCount++;
            }

            // Phân loại bên gia đình theo nhóm (group_name)
            $groupLower = mb_strtolower(trim((string) $guest->group_name));
            if (str_contains($groupLower, 'nhà trai') || str_contains($groupLower, 'chú rể') || str_contains($groupLower, 'trai')) {
                $groomSideGuests++;
            } elseif (str_contains($groupLower, 'nhà gái') || str_contains($groupLower, 'cô dâu') || str_contains($groupLower, 'gái')) {
                $brideSideGuests++;
            } else {
                $mutualFriendsGuests++;
            }

            $response = $guest->response;
            if ($response) {
                if ($response->attending_status === 'attending') {
                    $totalAttendingGuests++;
                    $adults = max(1, (int) ($response->adults_count ?: 1));
                    $children = max(0, (int) ($response->children_count ?: 0));

                    $confirmedAdults += $adults;
                    $confirmedChildren += $children;

                    if (! empty($response->dietary_requirements)) {
                        $dietaryText = trim((string) $response->dietary_requirements);
                        $dietaryLower = mb_strtolower($dietaryText);

                        if (str_contains($dietaryLower, 'chay')) {
                            $vegetarianCount++;
                        }

                        $dietaryList[] = [
                            'guest_id' => $guest->id,
                            'guest_name' => $guest->name,
                            'salutation' => $guest->salutation ?: 'Khách mời',
                            'table_name' => $guest->table_name ?: ($guest->table?->name ?: 'Chưa xếp bàn'),
                            'requirements' => $dietaryText,
                            'adults' => $adults,
                            'children' => $children,
                        ];
                    }
                } elseif ($response->attending_status === 'declined') {
                    $totalDeclinedGuests++;
                } else {
                    $totalUncertainGuests++;
                }
            } else {
                $totalPendingGuests++;
            }
        }

        $totalResponded = $totalAttendingGuests + $totalDeclinedGuests + $totalUncertainGuests;
        $responseRate = $totalInvitations > 0 ? round(($totalResponded / $totalInvitations) * 100, 1) : 0;
        $totalConfirmedAttendees = $confirmedAdults + $confirmedChildren;

        // Ước tính số bàn tiệc cần chuẩn bị (10 người/bàn)
        $tablesNeeded = (int) ceil($totalConfirmedAttendees / 10);
        if ($tablesNeeded === 0 && $totalConfirmedAttendees > 0) {
            $tablesNeeded = 1;
        }

        return [
            'total_invitations' => $totalInvitations,
            'total_expected_attendees' => $totalExpectedAttendees,
            'total_confirmed_attendees' => $totalConfirmedAttendees,
            'confirmed_adults' => $confirmedAdults,
            'confirmed_children' => $confirmedChildren,
            'attending_invitations' => $totalAttendingGuests,
            'declined_invitations' => $totalDeclinedGuests,
            'uncertain_invitations' => $totalUncertainGuests,
            'pending_invitations' => $totalPendingGuests,
            'response_rate' => $responseRate,
            'tables_needed' => $tablesNeeded,
            'household_breakdown' => [
                'individual' => $individualCount,
                'couple' => $coupleCount,
                'family' => $familyCount,
            ],
            'family_breakdown' => [
                'groom_side' => $groomSideGuests,
                'bride_side' => $brideSideGuests,
                'mutual_friends' => $mutualFriendsGuests,
            ],
            'dietary_summary' => [
                'total_special_dietary' => count($dietaryList),
                'vegetarian_count' => $vegetarianCount,
                'list' => $dietaryList,
            ],
        ];
    }

    /**
     * Xuất tệp CSV Bảng Kê Suất Ăn & Bàn Tiệc cho Quản Lý Nhà Hàng & Bếp Trưởng (UTF-8 BOM).
     */
    public static function exportKitchenBriefCsv(Wedding $wedding): string
    {
        $tables = WeddingTable::with(['guests.response'])
            ->where('wedding_id', $wedding->id)
            ->orderBy('id')
            ->get();

        $unassignedGuests = Guest::with('response')
            ->where('wedding_id', $wedding->id)
            ->whereNull('table_id')
            ->get();

        $csvLines = [];
        // Header UTF-8 BOM
        $csvLines[] = "\xEF\xBB\xBF".implode(',', [
            '"Bàn Tiệc"',
            '"Vị Trí / Khu Vực"',
            '"Số Khách Mời"',
            '"Người Lớn (Suất)"',
            '"Trẻ Em (Suất)"',
            '"Tổng Người Tham Dự"',
            '"Suất Ăn Chay"',
            '"Yêu Cầu Ăn Kiêng & Dị Ứng"',
            '"Danh Sách Khách Ngồi Bàn"',
        ]);

        foreach ($tables as $table) {
            $tableAdults = 0;
            $tableChildren = 0;
            $tableVegetarian = 0;
            $dietaryNotes = [];
            $guestNames = [];

            foreach ($table->guests as $g) {
                $guestNames[] = $g->name;
                $resp = $g->response;
                if ($resp && $resp->attending_status === 'attending') {
                    $ad = max(1, (int) ($resp->adults_count ?: 1));
                    $ch = max(0, (int) ($resp->children_count ?: 0));
                    $tableAdults += $ad;
                    $tableChildren += $ch;

                    if (! empty($resp->dietary_requirements)) {
                        $dietaryNotes[] = "{$g->name}: {$resp->dietary_requirements}";
                        if (str_contains(mb_strtolower($resp->dietary_requirements), 'chay')) {
                            $tableVegetarian++;
                        }
                    }
                } else {
                    $tableAdults += max(1, (int) ($g->expected_attendees ?: 1));
                }
            }

            $csvLines[] = implode(',', [
                '"'.str_replace('"', '""', $table->name).'"',
                '"'.str_replace('"', '""', $table->zone ?: 'Sảnh chính').'"',
                count($table->guests),
                $tableAdults,
                $tableChildren,
                $tableAdults + $tableChildren,
                $tableVegetarian,
                '"'.str_replace('"', '""', implode('; ', $dietaryNotes)).'"',
                '"'.str_replace('"', '""', implode(', ', $guestNames)).'"',
            ]);
        }

        // Bổ sung nhóm khách chưa xếp bàn nếu có
        if ($unassignedGuests->isNotEmpty()) {
            $unAdults = 0;
            $unChildren = 0;
            $unVegetarian = 0;
            $unNotes = [];
            $unNames = [];

            foreach ($unassignedGuests as $g) {
                $unNames[] = $g->name;
                $resp = $g->response;
                if ($resp && $resp->attending_status === 'attending') {
                    $ad = max(1, (int) ($resp->adults_count ?: 1));
                    $ch = max(0, (int) ($resp->children_count ?: 0));
                    $unAdults += $ad;
                    $unChildren += $ch;

                    if (! empty($resp->dietary_requirements)) {
                        $unNotes[] = "{$g->name}: {$resp->dietary_requirements}";
                        if (str_contains(mb_strtolower($resp->dietary_requirements), 'chay')) {
                            $unVegetarian++;
                        }
                    }
                } else {
                    $unAdults += max(1, (int) ($g->expected_attendees ?: 1));
                }
            }

            $csvLines[] = implode(',', [
                '"(Chưa Xếp Bàn Tiệc)"',
                '"Khu vực chờ"',
                count($unassignedGuests),
                $unAdults,
                $unChildren,
                $unAdults + $unChildren,
                $unVegetarian,
                '"'.str_replace('"', '""', implode('; ', $unNotes)).'"',
                '"'.str_replace('"', '""', implode(', ', $unNames)).'"',
            ]);
        }

        return implode("\r\n", $csvLines);
    }
}
