<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Services\AdminGiftLedgerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AdminGiftController extends Controller
{
    public function __construct(
        protected AdminGiftLedgerService $ledgerService
    ) {}

    /**
     * Hiển thị bảng điều khiển Sổ Mừng Cưới Kỹ Thuật Số & Quản Lý Quà Mừng.
     */
    public function index(Request $request): View
    {
        $filters = [
            'wedding_id' => $request->input('wedding_id'),
            'payment_method' => $request->input('payment_method'),
            'status' => $request->input('status'),
            'sender_group' => $request->input('sender_group'),
            'q' => $request->input('q', ''),
        ];

        $data = $this->ledgerService->getLedgerData($filters);

        return view('admin.pages.gifts.index', array_merge($data, [
            'filters' => $filters,
        ]));
    }

    /**
     * Ghi nhận quà mừng / phong bì mới vào sổ mừng cưới.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'wedding_id' => 'required|integer|exists:weddings,id',
            'sender_name' => 'required|string|max:190',
            'guest_id' => 'nullable|integer|exists:guests,id',
            'sender_group' => 'nullable|string|max:50',
            'amount' => 'nullable|integer|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,other',
            'notes' => 'nullable|string|max:1000',
            'is_acknowledged' => 'nullable|boolean',
        ], [
            'wedding_id.required' => 'Vui lòng chọn tiệc cưới.',
            'sender_name.required' => 'Vui lòng nhập tên người mừng cưới.',
            'payment_method.required' => 'Vui lòng chọn hình thức mừng cưới.',
        ]);

        $operator = $request->user()?->name ?? 'Admin';
        $gift = $this->ledgerService->recordGift((int) $validated['wedding_id'], $validated, $operator);

        $msg = "Đã ghi nhận quà mừng từ \"{$gift->sender_name}\" ({$gift->formatted_amount}) vào sổ thành công.";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'gift' => $gift,
                'message' => $msg,
            ], 201);
        }

        return back()->with('success', $msg);
    }

    /**
     * Cập nhật thông tin món quà mừng cưới.
     */
    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'sender_name' => 'required|string|max:190',
            'guest_id' => 'nullable|integer|exists:guests,id',
            'sender_group' => 'nullable|string|max:50',
            'amount' => 'nullable|integer|min:0',
            'payment_method' => 'required|in:cash,bank_transfer,other',
            'notes' => 'nullable|string|max:1000',
            'is_acknowledged' => 'nullable|boolean',
        ], [
            'sender_name.required' => 'Vui lòng nhập tên người mừng cưới.',
        ]);

        $operator = $request->user()?->name ?? 'Admin';
        $gift = $this->ledgerService->updateGift($id, $validated, $operator);

        $msg = "Đã cập nhật thông tin quà mừng từ \"{$gift->sender_name}\" thành công.";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'gift' => $gift,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Chuyển đổi trạng thái đã cảm ơn / chưa cảm ơn của món quà.
     */
    public function toggleAcknowledgement(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $operator = $request->user()?->name ?? 'Admin';
        $result = $this->ledgerService->toggleAcknowledgement($id, $operator);

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($result);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Đánh dấu đã cảm ơn hàng loạt món quà mừng cưới.
     */
    public function batchAcknowledge(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gift_ids' => 'required|array',
            'gift_ids.*' => 'integer|exists:wedding_gifts,id',
        ], [
            'gift_ids.required' => 'Vui lòng chọn ít nhất một món quà mừng để đánh dấu cảm ơn.',
        ]);

        $operator = $request->user()?->name ?? 'Admin';
        $count = $this->ledgerService->batchAcknowledge($validated['gift_ids'], $operator);

        return back()->with('success', "Đã đánh dấu đã cảm ơn cho {$count} món quà mừng thành công.");
    }

    /**
     * Xóa một món quà mừng cưới.
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $operator = $request->user()?->name ?? 'Admin';
        $this->ledgerService->deleteGift($id, $operator);

        return back()->with('success', 'Đã xóa món quà mừng cưới thành công.');
    }

    /**
     * Xóa hàng loạt món quà mừng cưới.
     */
    public function batchDelete(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gift_ids' => 'required|array',
            'gift_ids.*' => 'integer|exists:wedding_gifts,id',
        ], [
            'gift_ids.required' => 'Vui lòng chọn ít nhất một món quà mừng để xóa.',
        ]);

        $operator = $request->user()?->name ?? 'Admin';
        $count = $this->ledgerService->batchDelete($validated['gift_ids'], $operator);

        return back()->with('success', "Đã xóa hàng loạt {$count} món quà mừng thành công.");
    }

    /**
     * Xuất tệp Sổ mừng cưới định dạng CSV UTF-8 BOM.
     */
    public function export(Request $request): Response
    {
        $wedding = null;
        if ($request->has('wedding_id') && ! empty($request->input('wedding_id'))) {
            $wedding = Wedding::find((int) $request->input('wedding_id'));
        }

        return $this->ledgerService->exportLedgerCsv($wedding, $request->all());
    }
}
