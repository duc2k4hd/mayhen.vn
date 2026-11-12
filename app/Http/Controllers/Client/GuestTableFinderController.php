<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Services\AuditLogger;
use App\Services\GuestTableFinderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestTableFinderController extends Controller
{
    public function __construct(
        protected GuestTableFinderService $tableFinderService
    ) {}

    /**
     * Màn hình Studio Cấu hình Tra Cứu Bàn Tiệc & Hướng Dẫn Chỗ Ngồi.
     */
    public function index(Request $request, int|string $invitationId): View
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);
        $wedding = $invitation->wedding;

        $config = $this->tableFinderService->getConfig($invitation);
        $overview = $this->tableFinderService->getSeatingOverview($invitation);

        return view('client.pages.account.table_finder.index', [
            'invitation' => $invitation,
            'wedding' => $wedding,
            'config' => $config,
            'overview' => $overview,
        ]);
    }

    /**
     * Cập nhật cấu hình Tra cứu bàn tiệc.
     */
    public function update(Request $request, int|string $invitationId): RedirectResponse
    {
        $invitation = $this->resolveAuthorizedInvitation($request, $invitationId);

        $validated = $request->validate([
            'is_enabled' => 'nullable|boolean',
            'title' => 'required|string|max:150',
            'subtitle' => 'nullable|string|max:300',
            'show_table_name' => 'nullable|boolean',
            'show_table_zone' => 'nullable|boolean',
            'show_table_mates' => 'nullable|boolean',
            'allow_search_by_phone' => 'nullable|boolean',
            'welcome_message' => 'nullable|string|max:500',
            'empty_result_message' => 'nullable|string|max:500',
        ]);

        $params = [
            'is_enabled' => $request->boolean('is_enabled'),
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? '',
            'show_table_name' => $request->boolean('show_table_name'),
            'show_table_zone' => $request->boolean('show_table_zone'),
            'show_table_mates' => $request->boolean('show_table_mates'),
            'allow_search_by_phone' => $request->boolean('allow_search_by_phone'),
            'welcome_message' => $validated['welcome_message'] ?? '',
            'empty_result_message' => $validated['empty_result_message'] ?? '',
        ];

        $this->tableFinderService->updateConfig($invitation, $params);

        AuditLogger::log(
            'TABLE_FINDER_CONFIG_UPDATED',
            'Invitation',
            $invitation->id,
            'Đã cập nhật cấu hình Tra cứu bàn tiệc & Hướng dẫn chỗ ngồi'
        );

        return redirect()->route('account.table_finder.index', $invitation->id)
            ->with('success', 'Đã lưu cấu hình Tra cứu bàn tiệc thành công!');
    }

    /**
     * Public API quan khách tra cứu vị trí bàn tiệc qua từ khóa (Họ tên hoặc Số điện thoại).
     */
    public function search(Request $request, string $slug): JsonResponse
    {
        $invitation = Invitation::where('slug', $slug)->firstOrFail();

        $keyword = (string) $request->input('keyword', '');
        $guestToken = (string) $request->input('guest_token', '');

        // 1. Nếu có guest_token cá nhân hóa
        if (! empty($guestToken)) {
            $personalResult = $this->tableFinderService->findSeatingByGuestToken($invitation, $guestToken);
            if ($personalResult) {
                return response()->json([
                    'success' => true,
                    'found' => true,
                    'message' => 'Đã xác định vị trí bàn tiệc cá nhân hóa của Quý Khách!',
                    'results' => [$personalResult],
                ]);
            }
        }

        // 2. Tra cứu theo từ khóa
        $searchResult = $this->tableFinderService->searchSeatingByKeyword($invitation, $keyword);

        return response()->json([
            'success' => true,
            'found' => $searchResult['found'],
            'message' => $searchResult['message'],
            'results' => $searchResult['results'],
        ]);
    }

    /**
     * Xác thực quyền sở hữu thiệp mời.
     */
    protected function resolveAuthorizedInvitation(Request $request, int|string $invitationId): Invitation
    {
        $user = $request->user();
        $invitation = Invitation::with('wedding')->findOrFail($invitationId);

        if (! $invitation->wedding || ($invitation->wedding->user_id !== $user->id && ! ($user->is_admin ?? false))) {
            abort(403, 'Bạn không có quyền truy cập thiệp cưới này.');
        }

        return $invitation;
    }
}

