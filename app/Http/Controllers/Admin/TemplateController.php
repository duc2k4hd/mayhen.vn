<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvitationTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class TemplateController extends Controller
{
    /**
     * Display all templates in admin.
     */
    public function index(): View
    {
        $templates = InvitationTemplate::withCount('invitations')->latest()->get();

        return view('admin.pages.templates', [
            'templates' => $templates,
        ]);
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(int $id): RedirectResponse
    {
        $template = InvitationTemplate::findOrFail($id);
        $template->update(['is_active' => ! $template->is_active]);

        return back()->with('success', 'Đã cập nhật trạng thái hoạt động của mẫu.');
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(int $id): RedirectResponse
    {
        $template = InvitationTemplate::findOrFail($id);
        $template->update(['is_featured' => ! $template->is_featured]);

        return back()->with('success', 'Đã cập nhật trạng thái nổi bật của mẫu.');
    }
}
