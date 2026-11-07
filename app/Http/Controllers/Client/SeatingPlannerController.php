<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Invitation;
use App\Models\Wedding;
use App\Models\WeddingTable;
use App\Rules\NoProfanity;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SeatingPlannerController extends Controller
{
    /**
     * Màn hình Studio Xếp Bàn Tiệc Trực Quan (Seating Planner Studio).
     */
    public function index(Request $request, int $invitationId): View
    {
        $invitation = Invitation::with(['wedding', 'template'])->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $tables = $wedding->tables()->with(['guests.response'])->get();

        // Khách mời chưa được xếp bàn
        $unassignedGuests = $wedding->guests()
            ->with('response')
            ->whereNull('table_id')
            ->orderBy('name')
            ->get();

        // Thống kê sức chứa & chỗ ngồi
        $totalTablesCount = $tables->count();
        $totalCapacity = (int) $tables->sum('capacity');
        $assignedSeats = (int) $tables->sum('assigned_attendees_count');
        $unassignedSeats = (int) $unassignedGuests->sum(function (Guest $g) {
            return $g->actualAttendeesCount();
        });
        $overCapacityCount = $tables->filter(fn (WeddingTable $t) => $t->isOverCapacity())->count();

        // Gom danh sách nhóm khu vực (zones)
        $zones = $tables->pluck('zone')->filter()->unique()->values()->all();

        return view('client.pages.account.seating-planner', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'tables' => $tables,
            'unassignedGuests' => $unassignedGuests,
            'totalTablesCount' => $totalTablesCount,
            'totalCapacity' => $totalCapacity,
            'assignedSeats' => $assignedSeats,
            'unassignedSeats' => $unassignedSeats,
            'overCapacityCount' => $overCapacityCount,
            'zones' => $zones,
        ]);
    }

    /**
     * Khởi tạo bàn tiệc mới trong sảnh.
     */
    public function storeTable(Request $request, int $invitationId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', new NoProfanity],
            'shape' => ['required', 'string', 'in:round,long,square'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'zone' => ['nullable', 'string', 'max:50', new NoProfanity],
            'notes' => ['nullable', 'string', 'max:255', new NoProfanity],
        ], [
            'name.required' => 'Vui lòng đặt tên cho bàn tiệc (ví dụ: Bàn VIP 01, Bàn Bạn Đại Học...).',
            'capacity.required' => 'Vui lòng nhập số ghế của bàn tiệc.',
            'capacity.min' => 'Bàn tiệc phải có ít nhất 1 ghế.',
            'capacity.max' => 'Sức chứa tối đa của một bàn tiệc là 50 người.',
        ]);

        $tablesCount = $wedding->tables()->count();
        // Tự động phân bố vị trí ban đầu theo lưới
        $posX = 60 + (($tablesCount % 4) * 220);
        $posY = 60 + (floor($tablesCount / 4) * 200);

        $table = WeddingTable::create([
            'wedding_id' => $wedding->id,
            'name' => strip_tags(trim($validated['name'])),
            'shape' => $validated['shape'],
            'capacity' => (int) $validated['capacity'],
            'zone' => ! empty($validated['zone']) ? strip_tags(trim($validated['zone'])) : null,
            'notes' => ! empty($validated['notes']) ? strip_tags(trim($validated['notes'])) : null,
            'pos_x' => (int) $posX,
            'pos_y' => (int) $posY,
            'display_order' => $tablesCount + 1,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Đã tạo bàn tiệc \"{$table->name}\" thành công!",
                'table' => $table,
            ]);
        }

        return back()->with('success', "Đã tạo bàn tiệc \"{$table->name}\" thành công!");
    }

    /**
     * Cập nhật thông tin bàn tiệc.
     */
    public function updateTable(Request $request, int $invitationId, int $tableId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $table = $wedding->tables()->findOrFail($tableId);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', new NoProfanity],
            'shape' => ['required', 'string', 'in:round,long,square'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'zone' => ['nullable', 'string', 'max:50', new NoProfanity],
            'notes' => ['nullable', 'string', 'max:255', new NoProfanity],
        ]);

        $newName = strip_tags(trim($validated['name']));
        $table->update([
            'name' => $newName,
            'shape' => $validated['shape'],
            'capacity' => (int) $validated['capacity'],
            'zone' => ! empty($validated['zone']) ? strip_tags(trim($validated['zone'])) : null,
            'notes' => ! empty($validated['notes']) ? strip_tags(trim($validated['notes'])) : null,
        ]);

        // Đồng bộ lại table_name của các khách thuộc bàn này
        $wedding->guests()->where('table_id', $table->id)->update(['table_name' => $newName]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật bàn tiệc thành công!',
                'table' => $table,
            ]);
        }

        return back()->with('success', 'Đã cập nhật thông tin bàn tiệc thành công!');
    }

    /**
     * Xóa bàn tiệc khỏi sảnh (Khách sẽ tự động chuyển về danh sách chưa xếp bàn).
     */
    public function deleteTable(int $invitationId, int $tableId): RedirectResponse|JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $table = $wedding->tables()->findOrFail($tableId);
        $tableName = $table->name;

        // Xóa tên bàn ở bảng guests trước khi xóa bàn
        $wedding->guests()->where('table_id', $table->id)->update([
            'table_id' => null,
            'table_name' => null,
        ]);

        $table->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Đã xóa bàn \"{$tableName}\". Toàn bộ khách thuộc bàn này đã được chuyển về danh sách chưa có bàn.",
            ]);
        }

        return back()->with('success', "Đã xóa bàn \"{$tableName}\" thành công!");
    }

    /**
     * API Lưu vị trí tọa độ các bàn tiệc sau khi kéo thả trên Canvas.
     */
    public function updatePositions(Request $request, int $invitationId): JsonResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $validated = $request->validate([
            'positions' => ['required', 'array'],
            'positions.*.id' => ['required', 'integer'],
            'positions.*.pos_x' => ['required', 'integer', 'min:0', 'max:3000'],
            'positions.*.pos_y' => ['required', 'integer', 'min:0', 'max:3000'],
        ]);

        foreach ($validated['positions'] as $pos) {
            $wedding->tables()
                ->where('id', $pos['id'])
                ->update([
                    'pos_x' => $pos['pos_x'],
                    'pos_y' => $pos['pos_y'],
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu vị trí sơ đồ sảnh tiệc thành công!',
        ]);
    }

    /**
     * Gán khách vào bàn tiệc hoặc gỡ khách về danh sách chưa có bàn.
     */
    public function assignGuest(Request $request, int $invitationId): JsonResponse|RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $validated = $request->validate([
            'guest_id' => ['required', 'integer'],
            'table_id' => ['nullable', 'integer'],
        ]);

        $guest = $wedding->guests()->findOrFail($validated['guest_id']);

        if (! empty($validated['table_id'])) {
            $table = $wedding->tables()->findOrFail($validated['table_id']);
            $guest->update([
                'table_id' => $table->id,
                'table_name' => $table->name,
            ]);
            $msg = "Đã xếp khách \"{$guest->name}\" vào bàn \"{$table->name}\"";
        } else {
            $guest->update([
                'table_id' => null,
                'table_name' => null,
            ]);
            $msg = "Đã gỡ khách \"{$guest->name}\" về danh sách chưa có bàn";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'guest' => $guest,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Màn hình in sơ đồ sảnh tiệc & danh sách khách theo bàn chuẩn A4/A3.
     */
    public function printChart(int $invitationId): View
    {
        $invitation = Invitation::with(['wedding.partner', 'template'])->findOrFail($invitationId);
        $wedding = $invitation->wedding;
        $this->authorizeOwner($wedding);

        $tables = $wedding->tables()->with('guests')->get();
        $unassignedGuests = $wedding->guests()->whereNull('table_id')->orderBy('name')->get();

        return view('client.pages.account.seating-chart-print', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'tables' => $tables,
            'unassignedGuests' => $unassignedGuests,
            'totalCapacity' => $tables->sum('capacity'),
            'totalAssigned' => $tables->sum('assigned_attendees_count'),
        ]);
    }

    private function authorizeOwner(Wedding $wedding): void
    {
        if (auth()->check() && (int) $wedding->user_id === (int) auth()->id()) {
            return;
        }

        // Nếu là Partner sở hữu đám cưới
        if (auth()->check() && auth()->user()->partner && (int) $wedding->partner_id === (int) auth()->user()->partner->id) {
            return;
        }

        // Quản trị viên
        if (auth()->check() && auth()->user()->isAdmin()) {
            return;
        }

        // Session khách vãng lai sở hữu
        $ownedSlugs = (array) request()->session()->get('owned_invitation_slugs', []);
        if ($ownedSlugs !== [] && $wedding->invitations()->whereIn('slug', $ownedSlugs)->exists()) {
            return;
        }

        abort(404);
    }
}
