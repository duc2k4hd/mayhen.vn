<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteAnnouncement;
use App\Services\SiteAnnouncementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAnnouncementController extends Controller
{
    public function __construct(
        protected SiteAnnouncementService $announcementService
    ) {}

    /**
     * Danh sách thông báo công khai & banner hệ thống (Mục 147 PRD V3).
     */
    public function index(): View
    {
        $announcements = SiteAnnouncement::with('creator')->latest('id')->paginate(15);
        $stats = $this->announcementService->getStatistics();

        return view('admin.pages.announcements.index', compact('announcements', 'stats'));
    }

    /**
     * Màn hình thêm mới thông báo.
     */
    public function create(): View
    {
        $announcement = new SiteAnnouncement([
            'type' => SiteAnnouncement::TYPE_PROMOTION,
            'placement' => SiteAnnouncement::PLACEMENT_TOP_BAR,
            'is_active' => true,
            'is_dismissible' => true,
        ]);

        return view('admin.pages.announcements.form', compact('announcement'));
    }

    /**
     * Lưu thông báo mới.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|string|in:info,warning,danger,promotion',
            'placement' => 'required|string|in:top_bar,client_dashboard,modal_popup',
            'cta_text' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'is_dismissible' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ], [
            'title.required' => 'Vui lòng nhập tiêu đề thông báo.',
            'content.required' => 'Vui lòng nhập nội dung thông báo.',
            'ends_at.after_or_equal' => 'Thời gian kết thúc phải diễn ra sau hoặc cùng thời điểm bắt đầu.',
        ]);

        $this->announcementService->createAnnouncement($validated, auth()->user());

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Đã khởi tạo thông báo công khai thành công.');
    }

    /**
     * Màn hình chỉnh sửa thông báo.
     */
    public function edit(int $id): View
    {
        $announcement = SiteAnnouncement::findOrFail($id);

        return view('admin.pages.announcements.form', compact('announcement'));
    }

    /**
     * Cập nhật thông báo.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $announcement = SiteAnnouncement::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|string|in:info,warning,danger,promotion',
            'placement' => 'required|string|in:top_bar,client_dashboard,modal_popup',
            'cta_text' => 'nullable|string|max:100',
            'cta_url' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'is_dismissible' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_dismissible'] = $request->boolean('is_dismissible');

        $this->announcementService->updateAnnouncement($announcement, $validated, auth()->user());

        return redirect()->route('admin.announcements.index')
            ->with('success', "Cập nhật thông báo #{$announcement->id} thành công.");
    }

    /**
     * Bật / tắt nhanh trạng thái thông báo.
     */
    public function toggle(int $id): RedirectResponse
    {
        $announcement = SiteAnnouncement::findOrFail($id);
        $newStatus = $this->announcementService->toggleStatus($announcement, auth()->user());

        $statusText = $newStatus ? 'Bật hiển thị' : 'Tắt';

        return back()->with('success', "Đã {$statusText} thông báo #{$announcement->id} thành công.");
    }

    /**
     * Xóa thông báo.
     */
    public function destroy(int $id): RedirectResponse
    {
        $announcement = SiteAnnouncement::findOrFail($id);
        $this->announcementService->deleteAnnouncement($announcement, auth()->user());

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Đã xóa thông báo thành công.');
    }
}

