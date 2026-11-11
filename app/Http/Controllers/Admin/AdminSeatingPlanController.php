<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Wedding;
use App\Models\WeddingTable;
use App\Services\AdminSeatingPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminSeatingPlanController extends Controller
{
    public function __construct(
        protected AdminSeatingPlanService $seatingPlanService
    ) {}

    /**
     * Bảng điều khiển trung tâm Sơ đồ bàn tiệc & Chỗ ngồi quan khách
     */
    public function index(Request $request): View
    {
        $weddingId = $request->query('wedding_id');
        $weddings = Wedding::with('user')->latest()->take(50)->get();

        $selectedWedding = $weddingId
            ? Wedding::find($weddingId)
            : $weddings->first();

        $data = $selectedWedding
            ? $this->seatingPlanService->getSeatingPlanData($selectedWedding)
            : [
                'wedding' => null,
                'tables' => collect(),
                'tables_by_zone' => [],
                'unassigned_guests' => collect(),
                'health' => null,
            ];

        return view('admin.pages.guests.seating.index', array_merge($data, [
            'weddings' => $weddings,
            'selectedWedding' => $selectedWedding,
        ]));
    }

    /**
     * Tạo mới một bàn tiệc
     */
    public function storeTable(Request $request, int $weddingId): RedirectResponse
    {
        $wedding = Wedding::findOrFail($weddingId);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'shape' => 'nullable|in:round,long,square',
            'capacity' => 'required|integer|min:1|max:50',
            'zone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:255',
        ], [
            'name.required' => 'Vui lòng nhập tên hoặc số thứ tự bàn tiệc.',
            'capacity.required' => 'Vui lòng nhập sức chứa số ghế ngồi của bàn.',
            'capacity.min' => 'Sức chứa tối thiểu là 1 ghế.',
            'capacity.max' => 'Sức chứa tối đa cho một bàn là 50 ghế.',
        ]);

        $table = $this->seatingPlanService->createTable($wedding, $validated);

        return back()->with('success', "Đã tạo bàn tiệc [{$table->name}] thành công với sức chứa {$table->capacity} ghế.");
    }

    /**
     * Cập nhật thông tin bàn tiệc
     */
    public function updateTable(Request $request, int $weddingId, int $tableId): RedirectResponse
    {
        $table = WeddingTable::where('wedding_id', $weddingId)->findOrFail($tableId);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'shape' => 'nullable|in:round,long,square',
            'capacity' => 'required|integer|min:1|max:50',
            'zone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:255',
        ]);

        $this->seatingPlanService->updateTable($table, $validated);

        return back()->with('success', "Đã cập nhật thông tin bàn [{$table->name}].");
    }

    /**
     * Xóa bàn tiệc (tự động gỡ khách về trạng thái chưa xếp)
     */
    public function destroyTable(int $weddingId, int $tableId): RedirectResponse
    {
        $table = WeddingTable::where('wedding_id', $weddingId)->findOrFail($tableId);
        $tableName = $table->name;

        $this->seatingPlanService->deleteTable($table);

        return back()->with('success', "Đã xóa bàn tiệc [{$tableName}]. Các khách trong bàn đã được chuyển về danh sách chưa xếp chỗ an toàn.");
    }

    /**
     * Xếp danh sách khách vào bàn tiệc
     */
    public function assign(Request $request, int $weddingId, int $tableId): RedirectResponse|JsonResponse
    {
        $table = WeddingTable::where('wedding_id', $weddingId)->findOrFail($tableId);

        $validated = $request->validate([
            'guest_ids' => 'required|array|min:1',
            'guest_ids.*' => 'integer|exists:guests,id',
        ], [
            'guest_ids.required' => 'Vui lòng chọn ít nhất một khách mời để xếp vào bàn.',
        ]);

        $result = $this->seatingPlanService->assignGuestsToTable($table, $validated['guest_ids']);
        $message = "Đã xếp {$result['assigned_count']} quan khách vào bàn [{$table->name}] thành công.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Gỡ khách khỏi bàn tiệc
     */
    public function unassign(Request $request, int $weddingId, int $guestId): RedirectResponse|JsonResponse
    {
        $guest = Guest::where('wedding_id', $weddingId)->findOrFail($guestId);
        $guestName = $guest->name;

        $this->seatingPlanService->unassignGuest($guest);
        $message = "Đã gỡ khách mời [{$guestName}] khỏi bàn tiệc.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Xuất danh sách sơ đồ bàn tiệc sang tệp CSV UTF-8 BOM
     */
    public function export(int $weddingId): Response
    {
        $wedding = Wedding::findOrFail($weddingId);
        $csvContent = $this->seatingPlanService->exportSeatingPlanCsv($wedding);

        $fileName = 'so_do_ban_tiec_wedding_'.$wedding->id.'.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");
    }
}
