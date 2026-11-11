<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuestRequest;
use App\Http\Requests\SubmitRsvpRequest;
use App\Http\Requests\SubmitWishRequest;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\GuestResponseLog;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Models\WeddingWish;
use App\Rules\NoProfanity;
use App\Services\AuditLogger;
use App\Services\ContentModerationService;
use App\Services\CsvSanitizerService;
use App\Services\GentleReminderPlannerService;
use App\Services\InvitationDeliveryService;
use App\Services\OutboundWebhookService;
use App\Services\PersonalThankYouService;
use App\Services\PreSendValidationService;
use App\Services\RsvpEnforcementService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GuestController extends Controller
{
    /**
     * Display guest management and RSVP dashboard.
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        // Danh sách nhóm và bàn tiệc độc nhất để hiển thị dropdown bộ lọc
        $availableGroups = Guest::where('wedding_id', $wedding->id)
            ->whereNotNull('group_name')
            ->distinct()
            ->pluck('group_name');

        $availableTables = Guest::where('wedding_id', $wedding->id)
            ->whereNotNull('table_name')
            ->distinct()
            ->pluck('table_name');

        // Thống kê tổng quan (toàn bộ đám cưới)
        $allGuests = $wedding->guests()->with('response')->get();
        $totalInvited = (int) $allGuests->sum('expected_attendees');
        $totalGuestsCount = $allGuests->count();
        $totalResponses = $wedding->guestResponses()->count();
        $attendingCount = (int) ($wedding->guestResponses()->where('attending_status', 'attending')->sum('adults_count')
            + $wedding->guestResponses()->where('attending_status', 'attending')->sum('children_count'));
        $declinedCount = (int) $wedding->guestResponses()->where('attending_status', 'declined')->count();
        $responseRate = $totalGuestsCount > 0 ? (int) round(($totalResponses / $totalGuestsCount) * 100) : 0;
        $checkedInCount = (int) $allGuests->whereNotNull('checked_in_at')->sum(function (Guest $g) {
            return $g->checked_in_attendees ?: $g->expected_attendees;
        });
        $openedCount = $allGuests->filter(fn (Guest $g) => $g->isOpened())->count();

        // Xử lý bộ lọc danh sách hiển thị
        $guestsQuery = $wedding->guests()->with('response');

        if ($search = trim((string) $request->query('q'))) {
            $guestsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('table_name', 'like', "%{$search}%");
            });
        }

        if ($groupFilter = $request->query('group')) {
            $guestsQuery->where('group_name', $groupFilter);
        }

        if ($sideFilter = $request->query('side')) {
            if ($sideFilter === 'groom') {
                $guestsQuery->where('group_name', 'like', '%Nhà Trai%');
            } elseif ($sideFilter === 'bride') {
                $guestsQuery->where('group_name', 'like', '%Nhà Gái%');
            }
        }

        if ($tableFilter = $request->query('table')) {
            $guestsQuery->where('table_name', $tableFilter);
        }

        if ($statusFilter = $request->query('status')) {
            if ($statusFilter === 'attending') {
                $guestsQuery->whereHas('response', fn ($q) => $q->where('attending_status', 'attending'));
            } elseif ($statusFilter === 'declined') {
                $guestsQuery->whereHas('response', fn ($q) => $q->where('attending_status', 'declined'));
            } elseif ($statusFilter === 'pending') {
                $guestsQuery->whereDoesntHave('response');
            } elseif ($statusFilter === 'checked_in') {
                $guestsQuery->whereNotNull('checked_in_at');
            } elseif ($statusFilter === 'not_checked_in') {
                $guestsQuery->whereNull('checked_in_at');
            } elseif ($statusFilter === 'opened') {
                $guestsQuery->where(function ($q) {
                    $q->where('open_count', '>', 0)
                        ->orWhereNotNull('opened_at');
                });
            } elseif ($statusFilter === 'not_opened') {
                $guestsQuery->where(function ($q) {
                    $q->where('open_count', '<=', 0)
                        ->orWhereNull('open_count');
                })->whereNull('opened_at');
            }
        }

        $guests = $guestsQuery->latest()->get();

        $deliveryStats = app(InvitationDeliveryService::class)->getDeliveryStats($wedding);

        return view('client.pages.guests', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'guests' => $guests,
            'deliveryStats' => $deliveryStats,
            'totalInvited' => $totalInvited,
            'totalGuestsCount' => $totalGuestsCount,
            'totalResponses' => $totalResponses,
            'attendingCount' => $attendingCount,
            'declinedCount' => $declinedCount,
            'responseRate' => $responseRate,
            'checkedInCount' => $checkedInCount,
            'openedCount' => $openedCount,
            'availableGroups' => $availableGroups,
            'availableTables' => $availableTables,
            'filterSearch' => $search,
            'filterGroup' => $groupFilter,
            'filterSide' => $sideFilter,
            'filterTable' => $tableFilter,
            'filterStatus' => $statusFilter,
        ]);
    }

    /**
     * Store new guest with personalized link.
     */
    public function store(StoreGuestRequest $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        $tableName = $request->validated('table_name') ? trim((string) $request->validated('table_name')) : null;
        $tableId = null;
        if (! empty($tableName)) {
            $matchedTable = $wedding->tables()->where('name', $tableName)->first();
            $tableId = $matchedTable?->id;
        }

        Guest::create([
            'wedding_id' => $wedding->id,
            'name' => $request->validated('name'),
            'salutation' => $request->validated('salutation') ?? 'Thân mời',
            'group_name' => $request->validated('group_name') ?? 'Bạn Bè',
            'table_name' => $tableName,
            'table_id' => $tableId,
            'phone' => $request->validated('phone'),
            'expected_attendees' => $request->validated('expected_attendees') ?? 1,
            'max_attendees' => $request->validated('max_attendees') ? (int) $request->validated('max_attendees') : null,
            'event_ids' => $request->validated('event_ids'),
            'guest_token' => Str::random(32),
        ]);

        $redirect = redirect()->route('guests.index', $invitationId)
            ->with('success', 'Đã thêm khách mời thành công!');

        // Cảnh báo quá tải bàn tiệc nếu việc thêm khách vượt quá sức chứa của bàn (Flow 09)
        if ($tableId) {
            $table = $wedding->tables()->find($tableId);
            if ($table && $table->isOverCapacity()) {
                $over = $table->assigned_attendees_count - $table->capacity;
                $redirect->with('warning', "Lưu ý: Bàn \"{$table->name}\" đang bị quá tải ghế sau khi thêm khách ({$table->assigned_attendees_count}/{$table->capacity} người, thừa {$over} chỗ). Vui lòng điều chỉnh lại sơ đồ bàn tiệc.");
            }
        }

        return $redirect;
    }

    /**
     * Tải tệp CSV mẫu chuẩn để chuẩn bị danh sách khách mời (Section 28.3 PRD V3).
     */
    public function downloadCsvTemplate(int $invitationId): Response
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        $headers = [
            'Họ và Tên',
            'Danh Xưng',
            'Nhóm Khách',
            'Số Lượng Mời',
            'Số Điện Thoại',
            'Bàn Tiệc',
            'Ghi Chú',
        ];

        $sampleRows = [
            [
                'Bác Nguyễn Văn Hùng',
                'Kính mời Bác',
                'Họ Nhà Trai',
                '2',
                '0912345678',
                'Bàn VIP 01',
                'Người lớn tuổi cần ngồi gần lối đi',
            ],
            [
                'Bạn Lê Thị Mai',
                'Thân mời',
                'Bạn Đại Học',
                '1',
                '0987654321',
                'Bàn 06',
                'Bạn thân cùng lớp đại học',
            ],
            [
                'Gia đình Chú Tuấn',
                'Kính mời Gia đình',
                'Đồng Nghiệp',
                '3',
                '0901234567',
                'Bàn 08',
                'Có 1 em bé đi cùng',
            ],
        ];

        $fp = fopen('php://temp', 'r+');
        // UTF-8 BOM
        fwrite($fp, "\xEF\xBB\xBF");
        fputcsv($fp, $headers);
        foreach ($sampleRows as $row) {
            fputcsv($fp, $row);
        }
        rewind($fp);
        $csv = stream_get_contents($fp);
        fclose($fp);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="mau_danh_sach_khach_moi_mayhen.csv"',
        ]);
    }

    /**
     * Nhập danh sách khách mời thông minh từ tệp CSV (Section 28.3 PRD V3).
     */
    public function importCsv(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        $request->validate([
            'csv_file' => ['required', 'file', 'max:5120'],
            'duplicate_mode' => ['required', 'string', 'in:skip,update'],
            'default_salutation' => ['nullable', 'string', 'max:50'],
            'default_group' => ['nullable', 'string', 'max:50'],
        ], [
            'csv_file.required' => 'Vui lòng chọn tệp CSV để tải lên.',
            'csv_file.file' => 'Tệp tải lên không hợp lệ.',
            'csv_file.max' => 'Dung lượng tệp CSV tối đa là 5MB.',
        ]);

        $file = $request->file('csv_file');
        $duplicateMode = $request->input('duplicate_mode', 'skip');
        $defaultSalutation = $request->input('default_salutation') ?: 'Thân mời';
        $defaultGroup = $request->input('default_group') ?: 'Bạn Bè';

        $content = file_get_contents($file->getRealPath());
        if ($content === false || trim($content) === '') {
            return back()->with('error', 'Tệp CSV tải lên bị rỗng.');
        }

        // Bỏ UTF-8 BOM nếu có
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        $lines = preg_split("/\r\n|\n|\r/", trim($content));
        if (empty($lines)) {
            return back()->with('error', 'Tệp CSV không chứa dòng dữ liệu nào.');
        }

        // Tự phát hiện delimiter (, ; hoặc \t)
        $firstLine = $lines[0];
        $commaCount = substr_count($firstLine, ',');
        $semicolonCount = substr_count($firstLine, ';');
        $tabCount = substr_count($firstLine, "\t");

        $delimiter = ',';
        if ($semicolonCount > $commaCount && $semicolonCount > $tabCount) {
            $delimiter = ';';
        } elseif ($tabCount > $commaCount && $tabCount > $semicolonCount) {
            $delimiter = "\t";
        }

        $parsedRows = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $parsed = str_getcsv($line, $delimiter);
            if (! empty($parsed)) {
                $parsedRows[] = $parsed;
            }
        }

        if (empty($parsedRows)) {
            return back()->with('error', 'Không thể đọc dữ liệu từ tệp CSV.');
        }

        // Kiểm tra xem dòng đầu có phải là Header không
        $headerRow = $parsedRows[0];
        $headerRowLower = array_map(fn ($col) => mb_strtolower(trim($col), 'UTF-8'), $headerRow);

        $nameCol = null;
        $salutationCol = null;
        $groupCol = null;
        $attendeesCol = null;
        $phoneCol = null;
        $tableCol = null;
        $notesCol = null;

        $hasHeader = false;
        foreach ($headerRowLower as $idx => $colName) {
            $normalized = Str::slug($colName, ' ');
            if (preg_match('/(ho va ten|ho ten|ten|name|full name|guest)/i', $normalized)) {
                $nameCol = $idx;
                $hasHeader = true;
            } elseif (preg_match('/(danh xung|xung ho|salutation)/i', $normalized)) {
                $salutationCol = $idx;
                $hasHeader = true;
            } elseif (preg_match('/(nhom|group|phan loai)/i', $normalized)) {
                $groupCol = $idx;
                $hasHeader = true;
            } elseif (preg_match('/(so luong|so nguoi|suat moi|headcount|attendees)/i', $normalized)) {
                $attendeesCol = $idx;
                $hasHeader = true;
            } elseif (preg_match('/(dien thoai|sdt|phone|mobile|lien he)/i', $normalized)) {
                $phoneCol = $idx;
                $hasHeader = true;
            } elseif (preg_match('/(ban|ban tiec|table)/i', $normalized)) {
                $tableCol = $idx;
                $hasHeader = true;
            } elseif (preg_match('/(ghi chu|note|luu y)/i', $normalized)) {
                $notesCol = $idx;
                $hasHeader = true;
            }
        }

        $startIndex = 0;
        if ($hasHeader && $nameCol !== null) {
            $startIndex = 1;
        } else {
            // Fallback vị trí mặc định
            $nameCol = 0;
            $salutationCol = 1;
            $groupCol = 2;
            $attendeesCol = 3;
            $phoneCol = 4;
            $tableCol = 5;
            $notesCol = 6;
        }

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use (
            $parsedRows, $startIndex, $wedding, $nameCol, $salutationCol, $groupCol,
            $attendeesCol, $phoneCol, $tableCol, $notesCol, $defaultSalutation, $defaultGroup,
            $duplicateMode, &$createdCount, &$updatedCount, &$skippedCount
        ) {
            $totalRows = count($parsedRows);
            for ($i = $startIndex; $i < $totalRows; $i++) {
                $row = $parsedRows[$i];
                $name = isset($row[$nameCol]) ? trim($row[$nameCol]) : '';
                if ($name === '') {
                    continue;
                }

                // Lọc bỏ từ ngữ vi phạm
                if (ContentModerationService::containsProfanity($name)) {
                    $skippedCount++;

                    continue;
                }

                $salutation = ($salutationCol !== null && isset($row[$salutationCol]) && trim($row[$salutationCol]) !== '')
                    ? trim($row[$salutationCol])
                    : $defaultSalutation;

                $group = ($groupCol !== null && isset($row[$groupCol]) && trim($row[$groupCol]) !== '')
                    ? trim($row[$groupCol])
                    : $defaultGroup;

                $attendees = 2;
                if ($attendeesCol !== null && isset($row[$attendeesCol])) {
                    $val = preg_replace('/[^0-9]/', '', trim($row[$attendeesCol]));
                    if ($val !== '' && is_numeric($val)) {
                        $attendees = max(1, (int) $val);
                    }
                }

                $phone = null;
                if ($phoneCol !== null && isset($row[$phoneCol])) {
                    $rawPhone = trim($row[$phoneCol]);
                    // Chuẩn hóa số điện thoại: loại bỏ dấu cách, gạch nối, chấm
                    $cleanedPhone = preg_replace('/[^0-9+]/', '', $rawPhone);
                    if (str_starts_with($cleanedPhone, '+84')) {
                        $cleanedPhone = '0'.substr($cleanedPhone, 3);
                    }
                    if ($cleanedPhone !== '') {
                        $phone = Str::limit($cleanedPhone, 20, '');
                    }
                }

                $table = ($tableCol !== null && isset($row[$tableCol])) ? trim($row[$tableCol]) : null;
                $tableClean = $table ? Str::limit($table, 100, '') : null;
                $tableId = null;
                if (! empty($tableClean)) {
                    $matchedTable = $wedding->tables()->where('name', $tableClean)->first();
                    $tableId = $matchedTable?->id;
                }

                $notes = ($notesCol !== null && isset($row[$notesCol])) ? trim($row[$notesCol]) : null;
                $notesClean = $notes ? Str::limit($notes, 255, '') : null;

                // Kiểm tra trùng lặp theo số điện thoại hoặc (tên + nhóm)
                $existingGuest = null;
                if ($phone) {
                    $existingGuest = Guest::where('wedding_id', $wedding->id)->where('phone', $phone)->first();
                }
                if (! $existingGuest) {
                    $existingGuest = Guest::where('wedding_id', $wedding->id)
                        ->where('name', $name)
                        ->where('group_name', $group)
                        ->first();
                }

                if ($existingGuest) {
                    if ($duplicateMode === 'skip') {
                        $skippedCount++;

                        continue;
                    } elseif ($duplicateMode === 'update') {
                        $existingGuest->update([
                            'name' => Str::limit($name, 190, ''),
                            'salutation' => Str::limit($salutation, 50, ''),
                            'group_name' => Str::limit($group, 50, ''),
                            'expected_attendees' => $attendees,
                            'table_name' => $tableClean ?: $existingGuest->table_name,
                            'table_id' => $tableId ?: $existingGuest->table_id,
                            'check_in_note' => $notesClean ?: $existingGuest->check_in_note,
                        ]);
                        $updatedCount++;

                        continue;
                    }
                }

                // Tạo khách mới
                Guest::create([
                    'wedding_id' => $wedding->id,
                    'name' => Str::limit($name, 190, ''),
                    'salutation' => Str::limit($salutation, 50, ''),
                    'group_name' => Str::limit($group, 50, ''),
                    'table_name' => $tableClean,
                    'table_id' => $tableId,
                    'phone' => $phone,
                    'expected_attendees' => $attendees,
                    'guest_token' => Str::random(32),
                    'check_in_note' => $notesClean,
                ]);
                $createdCount++;
            }
        });

        // Ghi nhật ký kiểm toán bất biến
        AuditLogger::log(
            'GUEST_CSV_IMPORTED',
            'wedding',
            $wedding->id,
            "Nhập danh sách khách mời qua tệp CSV: tạo mới {$createdCount}, cập nhật {$updatedCount}, bỏ qua {$skippedCount}.",
            ['created' => $createdCount, 'updated' => $updatedCount, 'skipped' => $skippedCount, 'mode' => $duplicateMode]
        );

        $msg = "Đã xử lý tệp CSV thành công: Thêm mới {$createdCount} khách mời";
        if ($updatedCount > 0) {
            $msg .= ", cập nhật {$updatedCount} khách";
        }
        if ($skippedCount > 0) {
            $msg .= ", bỏ qua {$skippedCount} dòng (trùng lặp hoặc lỗi)";
        }
        $msg .= '.';

        return back()->with('success', $msg);
    }

    /**
     * Nhập nhanh nhiều khách mời cùng lúc (Batch Import).
     */
    public function batchStore(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        $request->validate([
            'batch_input' => ['required', 'string', 'max:20000'],
            'default_salutation' => ['nullable', 'string', 'max:50'],
            'default_group' => ['nullable', 'string', 'max:50'],
        ], [
            'batch_input.required' => 'Vui lòng nhập danh sách khách mời.',
        ]);

        $defaultSalutation = $request->input('default_salutation') ?: 'Thân mời';
        $defaultGroup = $request->input('default_group') ?: 'Bạn Bè';

        $rawLines = preg_split("/\r\n|\n|\r/", (string) $request->input('batch_input'));
        $createdCount = 0;
        $skippedCount = 0;

        DB::transaction(function () use ($rawLines, $wedding, $defaultSalutation, $defaultGroup, &$createdCount, &$skippedCount) {
            foreach ($rawLines as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }

                // Hỗ trợ phân cách bằng dấu gạch đứng (|), dấu chấm phẩy (;) hoặc dấu tab
                $parts = preg_split('/[|;\t]/', $line);
                $name = trim($parts[0] ?? '');
                if ($name === '') {
                    continue;
                }

                // Lọc bỏ các dòng chứa từ ngữ thô tục / cấm kỵ (Content Moderation)
                if (ContentModerationService::containsProfanity($name)) {
                    $skippedCount++;

                    continue;
                }

                $salutation = isset($parts[1]) && trim($parts[1]) !== '' ? trim($parts[1]) : $defaultSalutation;
                $group = isset($parts[2]) && trim($parts[2]) !== '' ? trim($parts[2]) : $defaultGroup;
                $attendees = isset($parts[3]) && is_numeric(trim($parts[3])) ? max(1, (int) trim($parts[3])) : 2;
                $phone = isset($parts[4]) && trim($parts[4]) !== '' ? trim($parts[4]) : null;
                $table = isset($parts[5]) && trim($parts[5]) !== '' ? trim($parts[5]) : null;
                $tableClean = $table ? Str::limit($table, 100, '') : null;
                $tableId = null;
                if (! empty($tableClean)) {
                    $matchedTable = $wedding->tables()->where('name', $tableClean)->first();
                    $tableId = $matchedTable?->id;
                }

                Guest::create([
                    'wedding_id' => $wedding->id,
                    'name' => Str::limit($name, 190, ''),
                    'salutation' => Str::limit($salutation, 50, ''),
                    'group_name' => Str::limit($group, 50, ''),
                    'table_name' => $tableClean,
                    'table_id' => $tableId,
                    'phone' => $phone ? Str::limit($phone, 20, '') : null,
                    'expected_attendees' => $attendees,
                    'guest_token' => Str::random(32),
                ]);

                $createdCount++;
            }
        });

        if ($createdCount === 0) {
            return back()->with('error', 'Không tìm thấy thông tin khách mời hợp lệ để thêm.'.($skippedCount > 0 ? " (Đã lọc bỏ {$skippedCount} dòng chứa ngôn từ không phù hợp)." : ''));
        }

        $redirect = redirect()->route('guests.index', $invitationId)
            ->with('success', "Đã thêm thành công {$createdCount} khách mời vào danh sách!".($skippedCount > 0 ? " (Đã lọc bỏ {$skippedCount} dòng chứa từ ngữ không phù hợp)." : ''));

        // Cảnh báo nếu danh sách thêm vào làm bàn tiệc nào bị quá tải sức chứa
        $overloadedTables = $wedding->tables()->get()->filter->isOverCapacity();
        if ($overloadedTables->isNotEmpty()) {
            $tableDetails = $overloadedTables->map(fn ($t) => "Bàn \"{$t->name}\" ({$t->assigned_attendees_count}/{$t->capacity} người)")->implode(', ');
            $redirect->with('warning', "Lưu ý: Có bàn tiệc đang bị quá tải ghế sau khi thêm khách ({$tableDetails}). Vui lòng điều chỉnh lại sơ đồ bàn tiệc.");
        }

        return $redirect;
    }

    /**
     * Xuất danh sách khách mời dạng CSV UTF-8 BOM cho Excel (Hỗ trợ lọc theo tìm kiếm, nhóm, bàn, trạng thái).
     */
    public function exportCsv(Request $request, int $invitationId): StreamedResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        $guestsQuery = $wedding->guests()->with('response');

        if ($search = trim((string) $request->query('q'))) {
            $guestsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('table_name', 'like', "%{$search}%");
            });
        }

        if ($groupFilter = $request->query('group')) {
            $guestsQuery->where('group_name', $groupFilter);
        }

        if ($sideFilter = $request->query('side')) {
            if ($sideFilter === 'groom') {
                $guestsQuery->where('group_name', 'like', '%Nhà Trai%');
            } elseif ($sideFilter === 'bride') {
                $guestsQuery->where('group_name', 'like', '%Nhà Gái%');
            }
        }

        if ($tableFilter = $request->query('table')) {
            $guestsQuery->where('table_name', $tableFilter);
        }

        if ($statusFilter = $request->query('status')) {
            if ($statusFilter === 'attending') {
                $guestsQuery->whereHas('response', fn ($q) => $q->where('attending_status', 'attending'));
            } elseif ($statusFilter === 'declined') {
                $guestsQuery->whereHas('response', fn ($q) => $q->where('attending_status', 'declined'));
            } elseif ($statusFilter === 'pending') {
                $guestsQuery->whereDoesntHave('response');
            } elseif ($statusFilter === 'checked_in') {
                $guestsQuery->whereNotNull('checked_in_at');
            } elseif ($statusFilter === 'not_checked_in') {
                $guestsQuery->whereNull('checked_in_at');
            }
        }

        $guests = $guestsQuery->orderBy('group_name')->orderBy('name')->get();

        $filename = 'danh-sach-khach-moi-'.Str::slug($wedding->groom_name.'-'.$wedding->bride_name).'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($guests, $invitation) {
            $handle = fopen('php://output', 'w');
            // Ghi UTF-8 BOM để Excel tiếng Việt không bị lỗi font
            fwrite($handle, "\xEF\xBB\xBF");

            // Header CSV
            fputcsv($handle, [
                'STT',
                'Tên Khách Mời',
                'Cách Xưng Hô',
                'Nhóm Khách',
                'Bàn Tiệc',
                'Số Điện Thoại',
                'Số Người Dự Kiến',
                'Trạng Thái RSVP',
                'Người Lớn (RSVP)',
                'Trẻ Em (RSVP)',
                'Điểm Danh Ngày Cưới',
                'Thời Điểm Điểm Danh',
                'Yêu Cầu Ăn Uống',
                'Lời Chúc / Ghi Chú',
                'Link Thiệp Cá Nhân Hóa',
            ]);

            $index = 1;
            foreach ($guests as $g) {
                $response = $g->response;
                $rsvpStatus = 'Chưa phản hồi';
                $adults = '';
                $children = '';
                $dietary = '';
                $wishes = '';

                if ($response) {
                    $rsvpStatus = $response->attending_status === 'attending' ? 'Đã xác nhận tham dự' : 'Từ chối / Không tham gia';
                    $adults = $response->adults_count;
                    $children = $response->children_count;
                    $dietary = $response->dietary_requirements ?? '';
                    $wishes = $response->wishes ?? '';
                }

                $checkInStatus = $g->checked_in_at ? 'Đã có mặt' : 'Chưa điểm danh';
                $checkInTime = $g->checked_in_at ? $g->checked_in_at->format('H:i d/m/Y') : '';
                $personalUrl = route('invitation.show', ['slug' => $invitation->slug, 'token' => $g->guest_token]);

                fputcsv($handle, CsvSanitizerService::row([
                    $index++,
                    $g->name,
                    $g->salutation,
                    $g->group_name,
                    $g->table_name ?? 'Chưa xếp bàn',
                    $g->phone ?? '',
                    $g->expected_attendees,
                    $rsvpStatus,
                    $adults,
                    $children,
                    $checkInStatus,
                    $checkInTime,
                    $dietary,
                    $wishes,
                    $personalUrl,
                ]));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Trang in danh sách khách đón tiếp & điểm danh bàn tiệc (Reception Check-in Sheet - Khổ A4).
     */
    public function print(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        $filterStatus = $request->query('status', 'all');
        $filterGroup = $request->query('group');
        $filterTable = $request->query('table');
        $search = trim((string) $request->query('q'));

        $query = $wedding->guests()->with('response');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('table_name', 'like', "%{$search}%");
            });
        }

        if ($filterGroup) {
            $query->where('group_name', $filterGroup);
        }

        if ($filterTable) {
            $query->where('table_name', $filterTable);
        }

        if ($filterStatus && $filterStatus !== 'all') {
            if ($filterStatus === 'attending') {
                $query->whereHas('response', fn ($q) => $q->where('attending_status', 'attending'));
            } elseif ($filterStatus === 'declined') {
                $query->whereHas('response', fn ($q) => $q->where('attending_status', 'declined'));
            } elseif ($filterStatus === 'pending') {
                $query->whereDoesntHave('response');
            } elseif ($filterStatus === 'checked_in') {
                $query->whereNotNull('checked_in_at');
            } elseif ($filterStatus === 'not_checked_in') {
                $query->whereNull('checked_in_at');
            }
        }

        $guests = $query->orderBy('group_name')->orderBy('name')->get();

        // Thống kê nhanh toàn diện
        $allGuests = $wedding->guests()->with('response')->get();
        $totalGuestsCount = $allGuests->count();
        $totalExpectedAttendees = (int) $allGuests->sum('expected_attendees');
        $attendingResponses = $wedding->guestResponses()->where('attending_status', 'attending')->get();
        $attendingGuestsCount = $allGuests->filter(fn ($g) => $g->response && $g->response->attending_status === 'attending')->count();
        $totalAdults = (int) $attendingResponses->sum('adults_count');
        $totalChildren = (int) $attendingResponses->sum('children_count');
        $totalVegetarian = (int) $attendingResponses->filter(fn ($r) => ! empty($r->dietary_requirements))->count();

        $availableGroups = Guest::where('wedding_id', $wedding->id)
            ->whereNotNull('group_name')
            ->distinct()
            ->pluck('group_name');

        return view('client.pages.account.guests-print', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'guests' => $guests,
            'filterStatus' => $filterStatus,
            'filterGroup' => $filterGroup,
            'filterTable' => $filterTable,
            'filterSearch' => $search,
            'availableGroups' => $availableGroups,
            'totalGuestsCount' => $totalGuestsCount,
            'totalExpectedAttendees' => $totalExpectedAttendees,
            'attendingGuestsCount' => $attendingGuestsCount,
            'totalAdults' => $totalAdults,
            'totalChildren' => $totalChildren,
            'totalVegetarian' => $totalVegetarian,
        ]);
    }

    /**
     * Cập nhật thông tin khách mời.
     */
    public function update(Request $request, int $guestId): JsonResponse|RedirectResponse
    {
        $guest = Guest::with('wedding')->findOrFail($guestId);
        $this->authorizeGuestManagement($guest->wedding);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'salutation' => ['nullable', 'string', 'max:50'],
            'group_name' => ['nullable', 'string', 'max:50'],
            'table_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'expected_attendees' => ['required', 'integer', 'min:1', 'max:50'],
            'max_attendees' => ['nullable', 'integer', 'min:1', 'max:50'],
            'event_ids' => ['nullable', 'array'],
            'event_ids.*' => ['integer'],
        ], [
            'name.required' => 'Vui lòng nhập tên khách mời.',
            'expected_attendees.required' => 'Vui lòng nhập số người tham dự dự kiến.',
            'expected_attendees.min' => 'Số người tham dự tối thiểu là 1.',
        ]);

        $tableId = $guest->table_id;
        $tableName = $guest->table_name;
        if (array_key_exists('table_name', $validated)) {
            $rawTable = trim((string) $validated['table_name']);
            if ($rawTable === '') {
                $tableName = null;
                $tableId = null;
            } else {
                $tableName = $rawTable;
                $matchedTable = $guest->wedding->tables()->where('name', $rawTable)->first();
                $tableId = $matchedTable?->id;
            }
        }

        $guest->update([
            'name' => $validated['name'],
            'salutation' => $validated['salutation'] ?? $guest->salutation,
            'group_name' => $validated['group_name'] ?? $guest->group_name,
            'table_name' => $tableName,
            'table_id' => $tableId,
            'phone' => array_key_exists('phone', $validated) ? ($validated['phone'] ?? null) : $guest->phone,
            'expected_attendees' => (int) $validated['expected_attendees'],
            'max_attendees' => array_key_exists('max_attendees', $validated) ? ($validated['max_attendees'] ? (int) $validated['max_attendees'] : null) : $guest->max_attendees,
            'event_ids' => array_key_exists('event_ids', $validated) ? $validated['event_ids'] : $guest->event_ids,
        ]);

        // Cảnh báo quá tải bàn tiệc nếu việc thay đổi số người làm vượt sức chứa của bàn (Flow 09)
        $tableOverloadedWarning = null;
        if ($guest->table_id) {
            $table = $guest->wedding->tables()->find($guest->table_id);
            if ($table && $table->isOverCapacity()) {
                $over = $table->assigned_attendees_count - $table->capacity;
                $tableOverloadedWarning = "Lưu ý: Bàn \"{$table->name}\" đang bị quá tải ghế ({$table->assigned_attendees_count}/{$table->capacity} người, thừa {$over} chỗ). Vui lòng điều chỉnh lại sơ đồ bàn tiệc.";
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật thông tin khách mời thành công.',
                'warning' => $tableOverloadedWarning,
                'is_table_overloaded' => ! empty($tableOverloadedWarning),
                'guest' => $guest,
            ]);
        }

        $redirect = back()->with('success', 'Đã cập nhật thông tin khách mời thành công.');
        if ($tableOverloadedWarning) {
            $redirect->with('warning', $tableOverloadedWarning);
        }

        return $redirect;
    }

    /**
     * Thu hồi link thiệp cũ và tái tạo mã định danh token mới cho khách mời.
     */
    public function regenerateToken(Request $request, int $guestId): JsonResponse|RedirectResponse
    {
        $guest = Guest::with('wedding.invitations')->findOrFail($guestId);
        $this->authorizeGuestManagement($guest->wedding);

        $oldToken = $guest->guest_token;
        $newToken = $guest->revokeAndRegenerateToken();

        AuditLogger::log(
            'GUEST_TOKEN_REVOKED',
            'Guest',
            $guest->id,
            "Chủ tiệc đã thu hồi token cũ [{$oldToken}] và cấp mã mới [{$newToken}] cho khách mời {$guest->name}"
        );

        $invitation = $guest->wedding->invitations()->first();
        $newUrl = $invitation ? route('invitation.show', ['slug' => $invitation->slug, 'token' => $newToken]) : null;

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Đã thu hồi liên kết cũ và tái tạo mã thiệp mới cho khách \"{$guest->name}\" thành công!",
                'guest_id' => $guest->id,
                'new_token' => $newToken,
                'invitation_url' => $newUrl,
            ]);
        }

        return back()->with('success', "Đã thu hồi mã cũ và cấp liên kết thiệp mới cho khách \"{$guest->name}\" thành công!");
    }

    /**
     * Cặp đôi cập nhật trạng thái xác nhận tham dự (RSVP) thủ công hộ khách mời.
     */
    public function manualRsvp(Request $request, int $guestId): JsonResponse|RedirectResponse
    {
        $guest = Guest::with(['wedding', 'response'])->findOrFail($guestId);
        $this->authorizeGuestManagement($guest->wedding);

        $validated = $request->validate([
            'attending_status' => ['required', 'in:attending,declined,pending'],
            'adults_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'dietary_requirements' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $status = $validated['attending_status'];

        if ($status === 'pending') {
            if ($guest->response) {
                $guest->response->delete();
            }
        } else {
            $adults = $status === 'attending' ? (int) ($validated['adults_count'] ?? $guest->expected_attendees) : 0;
            $children = $status === 'attending' ? (int) ($validated['children_count'] ?? 0) : 0;

            if ($guest->response) {
                GuestResponseLog::create([
                    'guest_response_id' => $guest->response->id,
                    'wedding_id' => $guest->wedding_id,
                    'guest_id' => $guest->id,
                    'previous_status' => $guest->response->attending_status,
                    'new_status' => $status,
                    'previous_adults' => $guest->response->adults_count ?? 1,
                    'new_adults' => $adults,
                    'previous_children' => $guest->response->children_count ?? 0,
                    'new_children' => $children,
                    'previous_dietary' => $guest->response->dietary_requirements,
                    'new_dietary' => $validated['dietary_requirements'] ?? null,
                    'previous_wishes' => $guest->response->wishes,
                    'new_wishes' => $validated['notes'] ?? null,
                    'source' => 'manual_host',
                    'ip_address' => $request->ip(),
                ]);
            }

            GuestResponse::updateOrCreate(
                ['guest_id' => $guest->id],
                [
                    'wedding_id' => $guest->wedding_id,
                    'guest_name' => $guest->name,
                    'attending_status' => $status,
                    'adults_count' => $adults,
                    'children_count' => $children,
                    'dietary_requirements' => $validated['dietary_requirements'] ?? null,
                    'wishes' => $validated['notes'] ?? null,
                    'needs_reconfirmation' => false,
                    'reconfirmed_at' => now(),
                    'ip_address' => $request->ip(),
                ]
            );
        }

        // Cảnh báo quá tải bàn tiệc nếu việc thay đổi trạng thái RSVP làm vượt sức chứa của bàn (Flow 09)
        $tableOverloadedWarning = null;
        if ($guest->table_id) {
            $table = $guest->wedding->tables()->find($guest->table_id);
            if ($table && $table->isOverCapacity()) {
                $over = $table->assigned_attendees_count - $table->capacity;
                $tableOverloadedWarning = "Lưu ý: Bàn \"{$table->name}\" đang bị quá tải ghế sau khi cập nhật RSVP ({$table->assigned_attendees_count}/{$table->capacity} người, thừa {$over} chỗ). Vui lòng điều chỉnh lại sơ đồ bàn tiệc.";
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật trạng thái RSVP cho khách mời thành công.',
                'warning' => $tableOverloadedWarning,
                'is_table_overloaded' => ! empty($tableOverloadedWarning),
            ]);
        }

        $redirect = back()->with('success', 'Đã cập nhật trạng thái RSVP cho khách mời thành công.');
        if ($tableOverloadedWarning) {
            $redirect->with('warning', $tableOverloadedWarning);
        }

        return $redirect;
    }

    /**
     * Bật/tắt trạng thái đã gửi thiệp cho khách mời.
     */
    public function toggleSent(Request $request, int $guestId): JsonResponse
    {
        $guest = Guest::with('wedding')->findOrFail($guestId);
        $this->authorizeGuestManagement($guest->wedding);

        $newSent = ! $guest->is_sent;
        $guest->update([
            'is_sent' => $newSent,
            'sent_at' => $newSent ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'is_sent' => $guest->is_sent,
            'sent_at' => $guest->sent_at?->format('H:i d/m/Y'),
            'message' => $guest->is_sent ? 'Đã đánh dấu đã gửi thiệp' : 'Đã hủy đánh dấu đã gửi thiệp',
        ]);
    }

    /**
     * Bàn Soát Lỗi Trước Khi Gửi Thiệp & Trợ Lý Xưng Hô (Pre-Send Validation Desk - TASK-VAL-10).
     */
    public function preSendValidation(int $invitationId, PreSendValidationService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeGuestManagement($invitation->wedding);

        $result = $service->validate($invitation->wedding, $invitation);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * API Kế Hoạch Nhắc Nhở RSVP Tế Nhị Trước Ngày Cưới (TASK-VAL-16).
     */
    public function reminderPlanner(int $invitationId, GentleReminderPlannerService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeGuestManagement($invitation->wedding);

        $result = $service->plan($invitation->wedding, $invitation);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * API Trợ Lý Soạn Lời Cảm Ơn Sau Cưới Cá Nhân Hóa (TASK-VAL-17).
     */
    public function thankYouBuilder(int $invitationId, PersonalThankYouService $service): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeGuestManagement($invitation->wedding);

        $result = $service->buildThankYouPlan($invitation->wedding, $invitation);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Delete guest.
     */
    public function destroy(int $guestId): RedirectResponse
    {
        $guest = Guest::with('wedding')->findOrFail($guestId);
        $this->authorizeGuestManagement($guest->wedding);
        $guest->delete();

        return back()->with('success', 'Đã xóa khách mời khỏi danh sách.');
    }

    private function authorizeGuestManagement(Wedding $wedding): void
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

    private function ensureInvitationInteractable(Invitation $invitation): void
    {
        $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode($invitation->custom_config, true) ?? []);
        if (! empty($config['is_locked'])) {
            abort(403, 'Thiệp cưới tạm thời bị khóa.');
        }

        if (! $invitation->is_published) {
            abort(404, 'Thiệp cưới chưa được xuất bản.');
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            abort(410, 'Thiệp cưới đã hết hạn lưu trữ.');
        }
    }

    /**
     * Submit RSVP from public invitation.
     */
    public function submitRsvp(SubmitRsvpRequest $request, string $slug): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $this->ensureInvitationInteractable($invitation);
        $wedding = $invitation->wedding;

        // 0. Chủ nhân thiệp không được phép gửi xác nhận tham dự cho chính mình
        if ($invitation->isOwner()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn là chủ nhân của thiệp cưới này nên không thể tự gửi xác nhận tham dự cho chính mình.',
                ], 403);
            }

            return back()->with('rsvp_error', 'Bạn là chủ nhân của thiệp cưới này.');
        }

        // 0.1. Kiểm tra chính sách và hạn chót RSVP (RSVP-03 Policy)
        $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode((string) $invitation->custom_config, true) ?? []);
        $allowRsvp = $config['allow_rsvp'] ?? true;
        if (! $allowRsvp) {
            $msg = 'Tính năng xác nhận tham dự (RSVP) hiện đang tạm đóng theo cài đặt của cô dâu chú rể.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('rsvp_error', $msg);
        }

        // 0.2. Kiểm tra chế độ đóng băng RSVP Hậu Sự Kiện (Post-Wedding RSVP Freezing - Section 36 PRD V3)
        if ($invitation->isRsvpFrozen()) {
            $msg = 'Hôn lễ đã diễn ra thành công tốt đẹp! Form xác nhận tham dự (RSVP) đã được đóng lại.';
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'is_frozen' => true,
                    'message' => $msg,
                ], 422);
            }

            return back()->with('rsvp_error', $msg);
        }

        // 0.3. Kiểm tra Hạn chót & Khóa nhận phản hồi (RsvpEnforcementService - Section 31 PRD V3)
        if (! RsvpEnforcementService::isRsvpOpen($invitation)) {
            $status = RsvpEnforcementService::getDeadlineStatus($invitation);
            $closedNotice = $status['closed_notice'];
            if (! empty($status['contact_phone'])) {
                $closedNotice .= " Hotline liên hệ: {$status['contact_phone']}";
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'is_closed' => true,
                    'message' => $closedNotice,
                    'contact_phone' => $status['contact_phone'],
                ], 422);
            }

            return back()->with('rsvp_error', $closedNotice);
        }

        $rsvpDeadlineStr = $config['rsvp_deadline'] ?? null;
        $isLateRsvp = false;
        if ($rsvpDeadlineStr) {
            $deadline = Carbon::parse($rsvpDeadlineStr)->endOfDay();
            if (Carbon::now()->isAfter($deadline)) {
                $isLateRsvp = true;
            }
        }

        $query = $request->query();
        $guestToken = null;
        if (array_key_exists('token', $query)) {
            $guestToken = $query['token'];
            if (! is_string($guestToken) || $guestToken === '' || strlen($guestToken) > 64) {
                abort(404);
            }

            // Phòng vệ kiểm tra token đã bị thu hồi (Revoked Token - Section 29 Master PRD V3)
            $isRevoked = $wedding->guests()
                ->whereJsonContains('revoked_tokens', $guestToken)
                ->exists();

            if ($isRevoked) {
                $revokedNotice = 'Đường link thiệp mời này đã được chủ tiệc thu hồi và cấp lại mã mới. Quý khách vui lòng liên hệ cô dâu chú rể để nhận đường link thiệp mới nhất.';
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'is_revoked' => true,
                        'message' => $revokedNotice,
                    ], 422);
                }

                return back()->with('rsvp_error', $revokedNotice);
            }
        }
        $ip = $request->ip();

        $allowRsvpUpdate = ! empty($config['allow_rsvp_update']);
        $resultStatus = null; // 'created' or 'updated'

        try {
            $created = DB::transaction(function () use ($wedding, $guestToken, $ip, $request, $isLateRsvp, $allowRsvpUpdate, &$resultStatus): bool {
                $lockedWedding = Wedding::whereKey($wedding->id)->lockForUpdate()->firstOrFail();
                $guest = $guestToken === null
                    ? null
                    : $lockedWedding->guests()->where('guest_token', $guestToken)->firstOrFail();

                // Kiểm tra số suất tối đa được phép (Headcount Policy - Section 29 Master PRD V3)
                if ($guest && $guest->max_attendees > 0 && $request->validated('attending_status') === 'attending') {
                    $adults = (int) ($request->validated('adults_count') ?? 1);
                    $children = (int) ($request->validated('children_count') ?? 0);
                    $totalAttendees = $adults + $children;

                    if ($totalAttendees > $guest->max_attendees) {
                        throw new \InvalidArgumentException("Số lượng người tham dự ({$totalAttendees} người) vượt quá số suất thiệp mời cho phép (tối đa {$guest->max_attendees} suất). Vui lòng điều chỉnh lại hoặc liên hệ cô dâu chú rể để được sắp xếp chu đáo.");
                    }
                }

                // Kiểm tra phạm vi sự kiện được phân quyền (Event Scope - Section 29 Master PRD V3)
                if ($guest && $request->filled('event_id')) {
                    $eventId = (int) $request->input('event_id');
                    if (! $guest->isEventAllowed($eventId)) {
                        throw new \InvalidArgumentException('Rất tiếc, thiệp mời của quý khách không thuộc phạm vi sự kiện này. Vui lòng liên hệ cô dâu chú rể.');
                    }
                }

                $existingResponse = GuestResponse::where('wedding_id', $lockedWedding->id);
                if ($guest) {
                    $existingResponse->where('guest_id', $guest->id);
                } else {
                    $phone = $request->validated('phone');
                    if (! empty($phone)) {
                        $existingResponse->whereNull('guest_id')->where('phone', $phone);
                    } else {
                        $existingResponse->whereNull('guest_id')
                            ->where('guest_name', $request->validated('guest_name'))
                            ->where('ip_address', $ip);
                    }
                }

                if ($existingResponse->exists()) {
                    $existing = $existingResponse->first();
                    // Chỉ cho phép cập nhật khi khách cần tái xác nhận dời lịch HOẶC có token cá nhân và chủ thiệp bật allow_rsvp_update
                    $canUpdate = $existing && ($existing->needs_reconfirmation || ($guest !== null && $allowRsvpUpdate));

                    if ($canUpdate) {
                        GuestResponseLog::create([
                            'guest_response_id' => $existing->id,
                            'wedding_id' => $lockedWedding->id,
                            'guest_id' => $guest?->id,
                            'previous_status' => $existing->attending_status,
                            'new_status' => $request->validated('attending_status'),
                            'previous_adults' => $existing->adults_count ?? 1,
                            'new_adults' => $request->validated('adults_count') ?? 1,
                            'previous_children' => $existing->children_count ?? 0,
                            'new_children' => $request->validated('children_count') ?? 0,
                            'previous_dietary' => $existing->dietary_requirements,
                            'new_dietary' => $request->validated('dietary_requirements'),
                            'previous_wishes' => $existing->wishes,
                            'new_wishes' => $request->validated('wishes'),
                            'source' => 'guest_online',
                            'ip_address' => $ip,
                        ]);

                        $existing->update([
                            'attending_status' => $request->validated('attending_status'),
                            'adults_count' => $request->validated('adults_count') ?? 1,
                            'children_count' => $request->validated('children_count') ?? 0,
                            'shuttle_bus_needed' => (bool) $request->validated('shuttle_bus_needed', false),
                            'shuttle_pickup_point' => $request->validated('shuttle_pickup_point'),
                            'shuttle_passengers' => (int) ($request->validated('shuttle_passengers') ?? 0),
                            'menu_preference' => $request->validated('menu_preference'),
                            'dietary_requirements' => $request->validated('dietary_requirements'),
                            'wishes' => $request->validated('wishes'),
                            'needs_reconfirmation' => false,
                            'is_late' => $isLateRsvp,
                            'reconfirmed_at' => now(),
                            'ip_address' => $ip,
                        ]);

                        AuditLogger::log(
                            'RSVP_UPDATED',
                            'GuestResponse',
                            $existing->id,
                            "Khách {$existing->guest_name} đã cập nhật lại phản hồi RSVP sang {$request->validated('attending_status')}"
                        );

                        $resultStatus = 'updated';

                        return true;
                    }

                    return false;
                }

                GuestResponse::create([
                    'wedding_id' => $lockedWedding->id,
                    'guest_id' => $guest?->id,
                    'guest_name' => $request->validated('guest_name'),
                    'phone' => $request->validated('phone'),
                    'attending_status' => $request->validated('attending_status'),
                    'adults_count' => $request->validated('adults_count') ?? 1,
                    'children_count' => $request->validated('children_count') ?? 0,
                    'shuttle_bus_needed' => (bool) $request->validated('shuttle_bus_needed', false),
                    'shuttle_pickup_point' => $request->validated('shuttle_pickup_point'),
                    'shuttle_passengers' => (int) ($request->validated('shuttle_passengers') ?? 0),
                    'menu_preference' => $request->validated('menu_preference'),
                    'dietary_requirements' => $request->validated('dietary_requirements'),
                    'wishes' => $request->validated('wishes'),
                    'is_late' => $isLateRsvp,
                    'is_wishes_public' => true,
                    'ip_address' => $ip,
                ]);

                if (! empty($request->validated('wishes'))) {
                    WeddingWish::create([
                        'wedding_id' => $lockedWedding->id,
                        'ip_address' => $ip,
                        'name' => $request->validated('guest_name'),
                        'message' => $request->validated('wishes'),
                        'is_approved' => true,
                    ]);
                }

                $resultStatus = 'created';

                return true;
            });
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'headcount_exceeded' => true,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('rsvp_error', $e->getMessage());
        }

        if (! $created) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã gửi xác nhận tham dự cho lễ cưới này rồi và không thể thay đổi.',
                ], 422);
            }

            return back()->with('rsvp_error', 'Bạn đã gửi xác nhận tham dự cho lễ cưới này rồi.');
        }

        // Bắn Outbound Webhook (nếu có cấu hình)
        try {
            $webhookEvent = $resultStatus === 'updated' ? 'rsvp.updated' : 'rsvp.submitted';
            app(OutboundWebhookService::class)->dispatch($wedding, $webhookEvent, [
                'guest_name' => $request->validated('guest_name'),
                'phone' => $request->validated('phone'),
                'attending_status' => $request->validated('attending_status'),
                'adults_count' => (int) ($request->validated('adults_count') ?? 1),
                'children_count' => (int) ($request->validated('children_count') ?? 0),
                'dietary_requirements' => $request->validated('dietary_requirements'),
                'wishes' => $request->validated('wishes'),
                'is_updated' => $resultStatus === 'updated',
            ]);
        } catch (\Throwable $e) {
            // Safe fallback
        }

        if ($request->wantsJson()) {
            $msg = $resultStatus === 'updated'
                ? 'Cảm ơn quý khách! Phản hồi tham dự của quý khách đã được cập nhật thành công.'
                : ($request->validated('attending_status') === 'attending'
                    ? 'Cảm ơn quý khách đã xác nhận tham dự! Chúng tôi rất hân hạnh được đón tiếp.'
                    : 'Cảm ơn quý khách đã gửi phản hồi! Chúng tôi rất tiếc vì quý khách chưa thể sắp xếp tham dự.');

            return response()->json([
                'success' => true,
                'is_updated' => $resultStatus === 'updated',
                'attending_status' => $request->validated('attending_status'),
                'message' => $msg,
            ]);
        }

        $flashMsg = $resultStatus === 'updated'
            ? 'Cảm ơn quý khách! Phản hồi tham dự đã được cập nhật thành công.'
            : 'Cảm ơn quý khách! Phản hồi đã được ghi nhận.';

        return back()->with('rsvp_success', $flashMsg);
    }

    /**
     * Demo RSVP submission for template preview mode.
     */
    public function submitDemoRsvp(SubmitRsvpRequest $request, string $slug): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $request->validated('attending_status') === 'attending'
                ? 'Cảm ơn bạn đã xác nhận tham dự! (Thử nghiệm)'
                : 'Cảm ơn bạn đã gửi phản hồi! (Thử nghiệm)',
        ], 200);
    }

    /**
     * Submit a wish to the wedding guestbook.
     * Lời chúc mới được lưu riêng, không dùng IP để xác định người gửi.
     */
    public function submitWish(SubmitWishRequest $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $this->ensureInvitationInteractable($invitation);
        $wedding = $invitation->wedding;

        // Chủ nhân thiệp không gửi lời chúc cho chính mình mà chỉ trả lời lời chúc của khách
        if ($invitation->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn là chủ nhân của thiệp cưới này nên không thể tự gửi lời chúc. Bạn có thể trả lời các lời chúc của khách mời.',
            ], 403);
        }

        $ip = $request->ip();

        // 1. Phân giải danh tính khách mời nếu có token
        $guestId = null;
        if ($guestToken = $request->input('guest_token')) {
            $matchedGuest = Guest::where('wedding_id', $wedding->id)->where('guest_token', $guestToken)->first();
            if ($matchedGuest) {
                $guestId = $matchedGuest->id;
            }
        }

        // 2. Xử lý lưu ảnh chữ ký điện tử (Signature Canvas) nếu có
        $signatureUrl = null;
        $signatureData = $request->input('signature_data');
        if ($signatureData && is_string($signatureData) && str_starts_with($signatureData, 'data:image/')) {
            try {
                if (preg_match('/^data:image\/(\w+);base64,/', $signatureData, $typeMatches)) {
                    $imageType = strtolower($typeMatches[1]);
                    if (in_array($imageType, ['png', 'jpeg', 'webp'], true)) {
                        $base64Str = substr($signatureData, strpos($signatureData, ',') + 1);
                        $decoded = base64_decode($base64Str);
                        if ($decoded !== false && strlen($decoded) <= 1024 * 1024) { // Tối đa 1MB
                            $filename = 'signatures/'.$slug.'/'.Str::uuid().'.'.($imageType === 'jpeg' ? 'jpg' : $imageType);
                            Storage::disk('public')->put($filename, $decoded);
                            $signatureUrl = Storage::url($filename);
                        }
                    }
                }
            } catch (\Throwable $e) {
                $signatureUrl = null;
            }
        }

        // 3. Quyết định chế độ duyệt & quyền riêng tư
        $isPrivate = (bool) $request->boolean('is_private');
        $moderationMode = $wedding->getWishModerationMode();

        if ($isPrivate) {
            $isApproved = false; // Lời chúc riêng tư không hiển thị ra ngoài công khai
            $successMsg = 'Lời chúc riêng tư của bạn đã được gửi an toàn tới cô dâu & chú rể!';
        } elseif ($moderationMode === 'manual') {
            $isApproved = false; // Chế độ kiểm duyệt trước
            $successMsg = 'Lời chúc của bạn đã được gửi thành công và đang chờ cô dâu & chú rể duyệt hiển thị!';
        } else {
            $isApproved = true; // Chế độ tự động hiển thị
            $successMsg = 'Cảm ơn bạn đã gửi lời chúc tốt đẹp tới cô dâu & chú rể!';
        }

        $wish = WeddingWish::create([
            'wedding_id' => $wedding->id,
            'guest_id' => $guestId,
            'name' => $request->validated('name'),
            'message' => $request->validated('message'),
            'is_approved' => $isApproved,
            'is_pinned' => false,
            'is_private' => $isPrivate,
            'signature_url' => $signatureUrl,
            'ip_address' => $ip,
        ]);

        // Bắn Outbound Webhook thông báo lời chúc mới
        try {
            app(OutboundWebhookService::class)->dispatch($wedding, 'wish.created', [
                'wish_id' => $wish->id,
                'name' => $wish->name,
                'message' => $wish->message,
                'is_private' => $isPrivate,
                'is_approved' => $isApproved,
            ]);
        } catch (\Throwable $e) {
            // Safe fallback
        }

        return response()->json([
            'success' => true,
            'is_update' => false,
            'message' => $successMsg,
            'wish' => [
                'id' => $wish->id,
                'name' => $wish->name,
                'message' => $wish->message,
                'reply' => $wish->reply,
                'is_private' => $isPrivate,
                'is_approved' => $isApproved,
                'signature_url' => $wish->signature_url,
                'created_at_human' => $wish->created_at->diffForHumans(),
                'created_at_date' => $wish->created_at->format('d/m/Y H:i'),
            ],
        ], 201);
    }

    /**
     * Reply to a wish (Owner only).
     */
    public function replyWish(Request $request, string $slug, int $wishId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $this->ensureInvitationInteractable($invitation);

        if (! $invitation->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền phản hồi lời chúc của thiệp cưới này.',
            ], 403);
        }

        $request->validate([
            'reply' => ['required', 'string', 'max:1000', new NoProfanity],
        ], [
            'reply.required' => 'Vui lòng nhập nội dung phản hồi.',
            'reply.max' => 'Nội dung phản hồi không được vượt quá 1000 ký tự.',
        ]);

        $wedding = $invitation->wedding;
        $wish = WeddingWish::where('wedding_id', $wedding->id)->findOrFail($wishId);

        $wish->update([
            'reply' => $request->input('reply'),
            'replied_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Đã gửi phản hồi lời chúc thành công!',
            'reply' => $wish->reply,
            'replied_at_human' => 'Vừa xong',
        ]);
    }

    /**
     * Get RSVP responses list and statistics (Owner only).
     */
    public function getRsvpResponses(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();

        if ($invitation->isLocked()) {
            return response()->json([
                'success' => false,
                'message' => 'Thiệp cưới tạm thời bị khóa do yêu cầu từ Quản trị viên.',
            ], 403);
        }

        if (! $invitation->isOwner()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem danh sách phản hồi của thiệp cưới này.',
            ], 403);
        }

        $wedding = $invitation->wedding;
        $responses = GuestResponse::where('wedding_id', $wedding->id)
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'guest_name' => $r->guest_name,
                'phone' => $r->phone,
                'attending_status' => $r->attending_status,
                'adults_count' => $r->adults_count,
                'children_count' => $r->children_count,
                'wishes' => $r->wishes,
                'created_at_human' => $r->created_at?->diffForHumans() ?? 'Vừa xong',
                'created_at_date' => $r->created_at?->format('d/m/Y H:i') ?? '',
            ]);

        return response()->json([
            'success' => true,
            'stats' => [
                'total' => $responses->count(),
                'attending' => $responses->where('attending_status', 'attending')->count(),
                'attending_guests' => (int) ($responses->where('attending_status', 'attending')->sum('adults_count')
                    + $responses->where('attending_status', 'attending')->sum('children_count')),
                'declined' => $responses->where('attending_status', 'declined')->count(),
            ],
            'responses' => $responses,
        ]);
    }

    /**
     * Get wishes list for an invitation.
     */
    public function getWishes(string $slug): JsonResponse
    {
        $invitation = Invitation::with('wedding')->where('slug', $slug)->firstOrFail();
        $this->ensureInvitationInteractable($invitation);
        $wishes = $invitation->wedding->wishes()
            ->visibleOnInvitation()
            ->orderByDesc('is_pinned')
            ->latest()
            ->take(50)
            ->get()
            ->map(fn ($wish) => [
                'id' => $wish->id,
                'name' => $wish->name,
                'message' => $wish->message,
                'reply' => $wish->reply,
                'is_pinned' => (bool) $wish->is_pinned,
                'signature_url' => $wish->signature_url,
                'created_at_human' => $wish->created_at->diffForHumans(),
                'created_at_date' => $wish->created_at->format('d/m/Y H:i'),
            ]);

        return response()->json([
            'success' => true,
            'wishes' => $wishes,
        ]);
    }

    /**
     * Demo wish submission for template preview mode.
     */
    public function submitDemoWish(SubmitWishRequest $request, string $slug): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn bạn đã gửi lời chúc thử nghiệm!',
            'wish' => [
                'id' => rand(1000, 9999),
                'name' => $request->validated('name'),
                'message' => $request->validated('message'),
                'reply' => null,
                'created_at_human' => 'Vừa xong',
                'created_at_date' => now()->format('d/m/Y H:i'),
            ],
        ], 201);
    }
}
