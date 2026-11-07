<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGuestRequest;
use App\Http\Requests\SubmitRsvpRequest;
use App\Http\Requests\SubmitWishRequest;
use App\Models\Guest;
use App\Models\GuestResponse;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Models\WeddingWish;
use App\Rules\NoProfanity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $guests = $guestsQuery->latest()->get();

        return view('client.pages.guests', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'guests' => $guests,
            'totalInvited' => $totalInvited,
            'totalGuestsCount' => $totalGuestsCount,
            'totalResponses' => $totalResponses,
            'attendingCount' => $attendingCount,
            'declinedCount' => $declinedCount,
            'responseRate' => $responseRate,
            'checkedInCount' => $checkedInCount,
            'availableGroups' => $availableGroups,
            'availableTables' => $availableTables,
            'filterSearch' => $search,
            'filterGroup' => $groupFilter,
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

        Guest::create([
            'wedding_id' => $wedding->id,
            'name' => $request->validated('name'),
            'salutation' => $request->validated('salutation') ?? 'Thân mời',
            'group_name' => $request->validated('group_name') ?? 'Bạn Bè',
            'table_name' => $request->validated('table_name'),
            'phone' => $request->validated('phone'),
            'expected_attendees' => $request->validated('expected_attendees') ?? 1,
            'guest_token' => Str::random(32),
        ]);

        return redirect()->route('guests.index', $invitationId)
            ->with('success', 'Đã thêm khách mời thành công!');
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

        DB::transaction(function () use ($rawLines, $wedding, $defaultSalutation, $defaultGroup, &$createdCount) {
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

                $salutation = isset($parts[1]) && trim($parts[1]) !== '' ? trim($parts[1]) : $defaultSalutation;
                $group = isset($parts[2]) && trim($parts[2]) !== '' ? trim($parts[2]) : $defaultGroup;
                $attendees = isset($parts[3]) && is_numeric(trim($parts[3])) ? max(1, (int) trim($parts[3])) : 2;
                $phone = isset($parts[4]) && trim($parts[4]) !== '' ? trim($parts[4]) : null;
                $table = isset($parts[5]) && trim($parts[5]) !== '' ? trim($parts[5]) : null;

                Guest::create([
                    'wedding_id' => $wedding->id,
                    'name' => Str::limit($name, 190, ''),
                    'salutation' => Str::limit($salutation, 50, ''),
                    'group_name' => Str::limit($group, 50, ''),
                    'table_name' => $table ? Str::limit($table, 100, '') : null,
                    'phone' => $phone ? Str::limit($phone, 20, '') : null,
                    'expected_attendees' => $attendees,
                    'guest_token' => Str::random(32),
                ]);

                $createdCount++;
            }
        });

        if ($createdCount === 0) {
            return back()->with('error', 'Không tìm thấy thông tin khách mời hợp lệ để thêm.');
        }

        return redirect()->route('guests.index', $invitationId)
            ->with('success', "Đã thêm thành công {$createdCount} khách mời vào danh sách!");
    }

    /**
     * Xuất danh sách khách mời dạng CSV UTF-8 BOM cho Excel.
     */
    public function exportCsv(int $invitationId): StreamedResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        $guests = $wedding->guests()->with('response')->orderBy('group_name')->get();

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

                fputcsv($handle, [
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
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Trang in danh sách khách đón tiếp & điểm danh bàn tiệc (Reception Check-in Sheet).
     */
    public function print(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeGuestManagement($wedding);

        $filterStatus = $request->query('status', 'all');
        $filterGroup = $request->query('group');

        $query = $wedding->guests()->with('response')->orderBy('group_name')->orderBy('name');

        if ($filterGroup) {
            $query->where('group_name', $filterGroup);
        }

        if ($filterStatus === 'attending') {
            $query->whereHas('response', function ($q) {
                $q->where('attending_status', 'attending');
            });
        }

        $guests = $query->get();

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
        ], [
            'name.required' => 'Vui lòng nhập tên khách mời.',
            'expected_attendees.required' => 'Vui lòng nhập số người tham dự dự kiến.',
            'expected_attendees.min' => 'Số người tham dự tối thiểu là 1.',
        ]);

        $guest->update([
            'name' => $validated['name'],
            'salutation' => $validated['salutation'] ?? 'Thân mời',
            'group_name' => $validated['group_name'] ?? 'Bạn Bè',
            'table_name' => $validated['table_name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'expected_attendees' => (int) $validated['expected_attendees'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật thông tin khách mời thành công.',
                'guest' => $guest,
            ]);
        }

        return back()->with('success', 'Đã cập nhật thông tin khách mời thành công.');
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
                    'ip_address' => $request->ip(),
                ]
            );
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật trạng thái RSVP cho khách mời thành công.',
            ]);
        }

        return back()->with('success', 'Đã cập nhật trạng thái RSVP cho khách mời thành công.');
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

        $query = $request->query();
        $guestToken = null;
        if (array_key_exists('token', $query)) {
            $guestToken = $query['token'];
            if (! is_string($guestToken) || $guestToken === '' || strlen($guestToken) > 64) {
                abort(404);
            }
        }
        $ip = $request->ip();

        $created = DB::transaction(function () use ($wedding, $guestToken, $ip, $request): bool {
            $lockedWedding = Wedding::whereKey($wedding->id)->lockForUpdate()->firstOrFail();
            $guest = $guestToken === null
                ? null
                : $lockedWedding->guests()->where('guest_token', $guestToken)->firstOrFail();

            $existingResponse = GuestResponse::where('wedding_id', $lockedWedding->id);
            if ($guest) {
                $existingResponse->where('guest_id', $guest->id);
            } else {
                $existingResponse->whereNull('guest_id')->where('ip_address', $ip);
            }

            if ($existingResponse->exists()) {
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
                'dietary_requirements' => $request->validated('dietary_requirements'),
                'wishes' => $request->validated('wishes'),
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

            return true;
        });

        if (! $created) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn đã gửi xác nhận tham dự cho lễ cưới này rồi và không thể thay đổi.',
                ], 422);
            }

            return back()->with('rsvp_error', 'Bạn đã gửi xác nhận tham dự cho lễ cưới này rồi.');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'attending_status' => $request->validated('attending_status'),
                'message' => $request->validated('attending_status') === 'attending'
                    ? 'Cảm ơn quý khách đã xác nhận tham dự! Chúng tôi rất hân hạnh được đón tiếp.'
                    : 'Cảm ơn quý khách đã gửi phản hồi! Chúng tôi rất tiếc vì quý khách chưa thể sắp xếp tham dự.',
            ]);
        }

        return back()->with('rsvp_success', 'Cảm ơn quý khách! Phản hồi đã được ghi nhận.');
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

        $wish = WeddingWish::create([
            'wedding_id' => $wedding->id,
            'name' => $request->validated('name'),
            'message' => $request->validated('message'),
            'is_approved' => true,
            'ip_address' => $ip,
        ]);

        return response()->json([
            'success' => true,
            'is_update' => false,
            'message' => 'Cảm ơn bạn đã gửi lời chúc tốt đẹp tới cô dâu & chú rể!',
            'wish' => [
                'id' => $wish->id,
                'name' => $wish->name,
                'message' => $wish->message,
                'reply' => $wish->reply,
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
            ->where('is_approved', true)
            ->take(50)
            ->get()
            ->map(fn ($wish) => [
                'id' => $wish->id,
                'name' => $wish->name,
                'message' => $wish->message,
                'reply' => $wish->reply,
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
