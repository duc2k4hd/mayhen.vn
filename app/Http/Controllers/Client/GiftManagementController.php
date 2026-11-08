<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\WeddingGift;
use App\Services\CsvSanitizerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GiftManagementController extends Controller
{
    /**
     * Hiển thị Sổ Mừng Cưới & Thống Kê Tài Chính Hôn Lễ.
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $wedding = $invitation->wedding;

        // Thống kê toàn diện
        $allGifts = $wedding->gifts()->get();
        $totalAmount = (int) $allGifts->sum('amount');
        $totalGiftsCount = $allGifts->count();
        $avgAmount = $totalGiftsCount > 0 ? (int) round($totalAmount / $totalGiftsCount) : 0;
        $cashAmount = (int) $allGifts->where('payment_method', 'cash')->sum('amount');
        $bankAmount = (int) $allGifts->where('payment_method', 'bank_transfer')->sum('amount');
        $otherAmount = (int) $allGifts->where('payment_method', 'other')->sum('amount');
        $acknowledgedCount = $allGifts->where('is_acknowledged', true)->count();

        // Nhóm mừng cưới độc nhất để lọc (dùng query trực tiếp tránh xung đột ORDER BY với DISTINCT trên MySQL)
        $availableGroups = WeddingGift::where('wedding_id', $wedding->id)
            ->whereNotNull('sender_group')
            ->distinct()
            ->pluck('sender_group');

        // Query danh sách có lọc
        $giftsQuery = $wedding->gifts()->with('guest');

        if ($search = trim((string) $request->query('q'))) {
            $giftsQuery->where(function ($q) use ($search) {
                $q->where('sender_name', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($methodFilter = $request->query('method')) {
            $giftsQuery->where('payment_method', $methodFilter);
        }

        if ($groupFilter = $request->query('group')) {
            $giftsQuery->where('sender_group', $groupFilter);
        }

        if ($ackFilter = $request->query('ack')) {
            if ($ackFilter === 'yes') {
                $giftsQuery->where('is_acknowledged', true);
            } elseif ($ackFilter === 'no') {
                $giftsQuery->where('is_acknowledged', false);
            }
        }

        $gifts = $giftsQuery->latest()->paginate(15)->withQueryString();

        // Danh sách khách mời để gợi ý khi thêm mừng cưới
        $guests = $wedding->guests()->select('id', 'name', 'group_name')->orderBy('name')->get();

        return view('client.pages.account.gifts', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'gifts' => $gifts,
            'totalAmount' => $totalAmount,
            'totalGiftsCount' => $totalGiftsCount,
            'avgAmount' => $avgAmount,
            'cashAmount' => $cashAmount,
            'bankAmount' => $bankAmount,
            'otherAmount' => $otherAmount,
            'acknowledgedCount' => $acknowledgedCount,
            'availableGroups' => $availableGroups,
            'guests' => $guests,
            'filterSearch' => $search,
            'filterMethod' => $methodFilter,
            'filterGroup' => $groupFilter,
            'filterAck' => $ackFilter,
        ]);
    }

    /**
     * Thêm mới một khoản mừng cưới vào sổ.
     */
    public function store(Request $request, int $invitationId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $validated = $request->validate([
            'sender_name' => ['required', 'string', 'max:190'],
            'sender_group' => ['nullable', 'string', 'max:50'],
            'amount' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'payment_method' => ['required', 'in:cash,bank_transfer,other'],
            'guest_id' => [
                'nullable',
                Rule::exists('guests', 'id')->where('wedding_id', $invitation->wedding_id),
            ],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_acknowledged' => ['nullable', 'boolean'],
        ], [
            'sender_name.required' => 'Vui lòng nhập tên người mừng cưới.',
            'amount.required' => 'Vui lòng nhập số tiền mừng.',
            'amount.min' => 'Số tiền mừng không được âm.',
            'payment_method.required' => 'Vui lòng chọn hình thức mừng cưới.',
        ]);

        WeddingGift::create([
            'wedding_id' => $invitation->wedding_id,
            'guest_id' => $validated['guest_id'] ?? null,
            'sender_name' => $validated['sender_name'],
            'sender_group' => $validated['sender_group'] ?? 'Bạn Bè',
            'amount' => (int) $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
            'is_acknowledged' => $request->boolean('is_acknowledged'),
        ]);

        return redirect()->route('account.gifts.index', $invitationId)
            ->with('success', 'Đã ghi nhận khoản mừng cưới thành công vào sổ!');
    }

    /**
     * Cập nhật thông tin khoản mừng cưới.
     */
    public function update(Request $request, int $invitationId, int $giftId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $gift = WeddingGift::where('wedding_id', $invitation->wedding_id)->findOrFail($giftId);

        $validated = $request->validate([
            'sender_name' => ['required', 'string', 'max:190'],
            'sender_group' => ['nullable', 'string', 'max:50'],
            'amount' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'payment_method' => ['required', 'in:cash,bank_transfer,other'],
            'guest_id' => [
                'nullable',
                Rule::exists('guests', 'id')->where('wedding_id', $invitation->wedding_id),
            ],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_acknowledged' => ['nullable', 'boolean'],
        ], [
            'sender_name.required' => 'Vui lòng nhập tên người mừng cưới.',
            'amount.required' => 'Vui lòng nhập số tiền mừng.',
        ]);

        $gift->update([
            'guest_id' => $validated['guest_id'] ?? null,
            'sender_name' => $validated['sender_name'],
            'sender_group' => $validated['sender_group'] ?? 'Bạn Bè',
            'amount' => (int) $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
            'is_acknowledged' => $request->boolean('is_acknowledged'),
        ]);

        return redirect()->route('account.gifts.index', $invitationId)
            ->with('success', 'Đã cập nhật thông tin khoản mừng cưới thành công!');
    }

    /**
     * Bật/tắt đánh dấu đã gửi lời cảm ơn.
     */
    public function toggleAcknowledge(Request $request, int $invitationId, int $giftId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $gift = WeddingGift::where('wedding_id', $invitation->wedding_id)->findOrFail($giftId);
        $gift->update([
            'is_acknowledged' => ! $gift->is_acknowledged,
        ]);

        $statusMsg = $gift->is_acknowledged ? 'Đã đánh dấu: Đã gửi lời cảm ơn.' : 'Đã chuyển về: Chưa gửi lời cảm ơn.';

        return back()->with('success', $statusMsg);
    }

    /**
     * Xóa một khoản mừng cưới khỏi sổ.
     */
    public function destroy(Request $request, int $invitationId, int $giftId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $gift = WeddingGift::where('wedding_id', $invitation->wedding_id)->findOrFail($giftId);
        $gift->delete();

        return redirect()->route('account.gifts.index', $invitationId)
            ->with('success', 'Đã xóa khoản mừng cưới khỏi sổ.');
    }

    /**
     * Xuất Sổ Mừng Cưới ra file CSV UTF-8 BOM cho Microsoft Excel (Hỗ trợ lọc theo tìm kiếm, hình thức, nhóm, cảm ơn).
     */
    public function exportCsv(Request $request, int $invitationId): StreamedResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $wedding = $invitation->wedding;
        $giftsQuery = $wedding->gifts()->with('guest');

        if ($search = trim((string) $request->query('q'))) {
            $giftsQuery->where(function ($q) use ($search) {
                $q->where('sender_name', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($methodFilter = $request->query('method')) {
            $giftsQuery->where('payment_method', $methodFilter);
        }

        if ($groupFilter = $request->query('group')) {
            $giftsQuery->where('sender_group', $groupFilter);
        }

        if ($ackFilter = $request->query('ack')) {
            if ($ackFilter === 'yes') {
                $giftsQuery->where('is_acknowledged', true);
            } elseif ($ackFilter === 'no') {
                $giftsQuery->where('is_acknowledged', false);
            }
        }

        $gifts = $giftsQuery->orderBy('sender_group')->latest('id')->get();

        $filename = 'so-mung-cuoi-'.Str::slug($wedding->groom_name.'-'.$wedding->bride_name).'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($gifts) {
            $handle = fopen('php://output', 'w');
            // Ghi UTF-8 BOM để Excel tiếng Việt không lỗi font
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'STT',
                'Tên Người Mừng Cưới',
                'Nhóm Khách',
                'Số Tiền (VNĐ)',
                'Hình Thức Mừng',
                'Lời Chúc / Ghi Chú',
                'Trạng Thái Cảm Ơn',
                'Thời Gian Ghi Nhận',
            ]);

            $index = 1;
            foreach ($gifts as $g) {
                fputcsv($handle, CsvSanitizerService::row([
                    $index++,
                    $g->sender_name,
                    $g->sender_group ?? '',
                    number_format($g->amount, 0, ',', '.'),
                    $g->payment_method_label,
                    $g->notes ?? '',
                    $g->is_acknowledged ? 'Đã cảm ơn' : 'Chưa cảm ơn',
                    $g->created_at ? $g->created_at->format('d/m/Y H:i') : '',
                ]));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Hiển thị trang In Sổ Mừng Cưới Khổ A4 (Đối Soát Tài Chính Sau Hôn Lễ).
     */
    public function print(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $this->authorizeInvitationOwner($invitation);

        $wedding = $invitation->wedding;
        $search = trim((string) $request->query('q'));

        // Query danh sách quà mừng có lọc
        $giftsQuery = $wedding->gifts()->with('guest');

        if ($search) {
            $giftsQuery->where(function ($q) use ($search) {
                $q->where('sender_name', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($methodFilter = $request->query('method')) {
            $giftsQuery->where('payment_method', $methodFilter);
        }

        if ($groupFilter = $request->query('group')) {
            $giftsQuery->where('sender_group', $groupFilter);
        }

        if ($ackFilter = $request->query('ack')) {
            if ($ackFilter === 'yes') {
                $giftsQuery->where('is_acknowledged', true);
            } elseif ($ackFilter === 'no') {
                $giftsQuery->where('is_acknowledged', false);
            }
        }

        $gifts = $giftsQuery->orderBy('sender_group')->latest('id')->get();

        // Thống kê tài chính cho bản in
        $totalAmount = (int) $gifts->sum('amount');
        $totalGiftsCount = $gifts->count();
        $cashAmount = (int) $gifts->where('payment_method', 'cash')->sum('amount');
        $bankAmount = (int) $gifts->where('payment_method', 'bank_transfer')->sum('amount');
        $otherAmount = (int) $gifts->where('payment_method', 'other')->sum('amount');
        $acknowledgedCount = $gifts->where('is_acknowledged', true)->count();

        // Danh sách nhóm có trong hôn lễ
        $availableGroups = WeddingGift::where('wedding_id', $wedding->id)
            ->whereNotNull('sender_group')
            ->distinct()
            ->pluck('sender_group');

        return view('client.pages.account.gifts-print', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'gifts' => $gifts,
            'totalAmount' => $totalAmount,
            'totalGiftsCount' => $totalGiftsCount,
            'cashAmount' => $cashAmount,
            'bankAmount' => $bankAmount,
            'otherAmount' => $otherAmount,
            'acknowledgedCount' => $acknowledgedCount,
            'availableGroups' => $availableGroups,
            'filterSearch' => $search,
            'filterMethod' => $methodFilter,
            'filterGroup' => $groupFilter,
            'filterAck' => $ackFilter,
        ]);
    }

    /**
     * Kiểm tra quyền sở hữu thiệp cưới của người dùng hiện tại.
     */
    private function authorizeInvitationOwner(Invitation $invitation): void
    {
        $wedding = $invitation->wedding;
        if (! $wedding) {
            abort(404);
        }

        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        abort(404);
    }
}
