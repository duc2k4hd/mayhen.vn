<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    /**
     * Display a listing of client wedding invitations with native fast filtering.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');
        $templateId = $request->input('template_id');

        $query = Invitation::query()
            ->with([
                'wedding:id,user_id,groom_name,bride_name,wedding_date,cover_photo_url',
                'wedding.user:id,name,email,phone',
                'template:id,name,slug,thumbnail_url',
            ])
            ->latest('id');

        // 1. Text Search: Couple names, Slug, Customer info
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('slug', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhereHas('wedding', function ($wq) use ($search) {
                        $wq->where('groom_name', 'like', "%{$search}%")
                            ->orWhere('bride_name', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($uq) use ($search) {
                                $uq->where('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");
                            });
                    });
            });
        }

        // 2. Status Filter
        if ($status === 'published') {
            $query->where('is_published', true);
        } elseif ($status === 'draft') {
            $query->where('is_published', false);
        } elseif ($status === 'expired') {
            $query->whereNotNull('expires_at')->where('expires_at', '<', now());
        }

        // 3. Template Filter
        if (! empty($templateId)) {
            $query->where('template_id', (int) $templateId);
        }

        $invitations = $query->paginate(15)->withQueryString();

        $templates = InvitationTemplate::query()
            ->select('id', 'name', 'slug')
            ->orderBy('name')
            ->get();

        return view('admin.pages.invitations.index', [
            'invitations' => $invitations,
            'templates' => $templates,
            'currentSearch' => $search,
            'currentStatus' => $status,
            'currentTemplateId' => $templateId,
        ]);
    }

    /**
     * Display the detailed monitoring view and live phone mockup of an invitation.
     */
    public function show(int $id): View
    {
        $invitation = Invitation::query()
            ->with([
                'wedding.user',
                'wedding.guests' => function ($q) {
                    $q->latest('id');
                },
                'wedding.guestResponses' => function ($q) {
                    $q->latest('id');
                },
                'wedding.wishes' => function ($q) {
                    $q->latest('id');
                },
                'template',
                'orders.plan',
            ])
            ->findOrFail($id);

        return view('admin.pages.invitations.show', [
            'invitation' => $invitation,
            'wedding' => $invitation->wedding,
            'template' => $invitation->template,
        ]);
    }

    /**
     * Toggle the publish status of an invitation.
     */
    public function togglePublish(int $id): RedirectResponse
    {
        $invitation = Invitation::findOrFail($id);
        $newStatus = ! $invitation->is_published;

        $updateData = ['is_published' => $newStatus];
        if ($newStatus && ! $invitation->published_at) {
            $updateData['published_at'] = now();
        }

        $invitation->update($updateData);

        $msg = $newStatus ? 'Đã kích hoạt xuất bản thiệp cưới thành công.' : 'Đã chuyển thiệp cưới về trạng thái bản nháp riêng tư.';

        return back()->with('success', $msg);
    }

    /**
     * Extend the expiration date of an invitation.
     */
    public function extend(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'days' => 'required|integer|in:30,60,90,180,365',
        ]);

        $invitation = Invitation::findOrFail($id);
        $days = (int) $request->input('days');

        $baseDate = ($invitation->expires_at && $invitation->expires_at->isFuture())
            ? $invitation->expires_at
            : Carbon::now();

        $invitation->update([
            'expires_at' => $baseDate->copy()->addDays($days),
        ]);

        return back()->with('success', "Đã gia hạn thời gian hiển thị thiệp cưới thêm {$days} ngày.");
    }
}
