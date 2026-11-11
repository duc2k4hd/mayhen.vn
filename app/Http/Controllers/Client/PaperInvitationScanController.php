<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\PaperInvitationScan;
use App\Services\PaperInvitationParserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PaperInvitationScanController extends Controller
{
    /**
     * Màn hình tải ảnh thiệp giấy để quét bóc tách thông tin.
     */
    public function create(int $id): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);

        if ($invitation->wedding->user_id !== Auth::id()) {
            abort(404);
        }

        $wedding = $invitation->wedding;
        $recentScans = PaperInvitationScan::where('invitation_id', $invitation->id)
            ->latest()
            ->take(5)
            ->get();

        return view('client.pages.account.paper_scan.create', compact('invitation', 'wedding', 'recentScans'));
    }

    /**
     * Tiếp nhận ảnh thiệp giấy và kích hoạt bộ phân tích AI.
     */
    public function store(Request $request, int $id): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);

        if ($invitation->wedding->user_id !== Auth::id()) {
            abort(404);
        }

        $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'image.required' => 'Vui lòng chọn hoặc chụp ảnh thiệp cưới giấy của bạn.',
            'image.mimes' => 'Tệp tải lên phải là hình ảnh có định dạng JPG, JPEG, PNG hoặc WebP.',
            'image.max' => 'Dung lượng ảnh tối đa cho phép là 10MB.',
        ]);

        $scan = PaperInvitationParserService::parseImage(
            $request->file('image'),
            Auth::user(),
            $invitation->wedding,
            $invitation
        );

        return redirect()->route('account.paper_scan.review', [$invitation->id, $scan->id])
            ->with('success', "AI đã bóc tách thông tin thiệp thành công với độ tin cậy {$scan->confidence_score}%. Vui lòng rà soát và xác nhận dữ liệu trước khi áp dụng.");
    }

    /**
     * Màn hình đối chiếu & rà soát dữ liệu (Human-in-the-Loop Review - Mục 2.9 DOCX).
     */
    public function review(int $id, int $scanId): View
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);

        if ($invitation->wedding->user_id !== Auth::id()) {
            abort(404);
        }

        $scan = PaperInvitationScan::where('invitation_id', $invitation->id)->findOrFail($scanId);
        $parsed = $scan->parsed_data ?? [];

        return view('client.pages.account.paper_scan.review', compact('invitation', 'scan', 'parsed'));
    }

    /**
     * Xác nhận dữ liệu và áp dụng vào thiệp cưới trực tuyến.
     */
    public function apply(Request $request, int $id, int $scanId): RedirectResponse
    {
        $invitation = Invitation::with('wedding')->findOrFail($id);

        if ($invitation->wedding->user_id !== Auth::id()) {
            abort(404);
        }

        $scan = PaperInvitationScan::where('invitation_id', $invitation->id)->findOrFail($scanId);

        $validated = $request->validate([
            'groom_name' => ['required', 'string', 'max:255'],
            'bride_name' => ['required', 'string', 'max:255'],
            'groom_father' => ['nullable', 'string', 'max:255'],
            'groom_mother' => ['nullable', 'string', 'max:255'],
            'bride_father' => ['nullable', 'string', 'max:255'],
            'bride_mother' => ['nullable', 'string', 'max:255'],
            'wedding_date' => ['nullable', 'date'],
            'party_time' => ['nullable', 'string', 'max:50'],
            'lunar_date' => ['nullable', 'string', 'max:100'],
            'venue_name' => ['nullable', 'string', 'max:255'],
            'venue_address' => ['nullable', 'string', 'max:500'],
            'quote' => ['nullable', 'string', 'max:1000'],
        ], [
            'groom_name.required' => 'Họ và tên Chú Rể không được để trống.',
            'bride_name.required' => 'Họ và tên Cô Dâu không được để trống.',
        ]);

        PaperInvitationParserService::applyToWedding($scan, $validated, Auth::user());

        return redirect()->route('invitation.create', ['edit' => $invitation->slug])
            ->with('success', 'Đã đồng bộ thông tin từ thiệp giấy vào thiệp cưới online thành công! Bạn có thể tiếp tục tùy chỉnh trong Studio.');
    }
}
