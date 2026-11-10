<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Services\AuditLogger;
use App\Services\CsvSanitizerService;
use App\Services\EntitlementStateMachineService;
use App\Services\GuestJourneyRehearsalService;
use App\Services\InvitationQualityPassportService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            $query->where('is_published', true)->where(function ($q) {
                $q->whereNull('custom_config->is_locked')->orWhere('custom_config->is_locked', false);
            });
        } elseif ($status === 'draft') {
            $query->where('is_published', false);
        } elseif ($status === 'expired') {
            $query->whereNotNull('expires_at')->where('expires_at', '<', now());
        } elseif ($status === 'locked') {
            $query->where('custom_config->is_locked', true);
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
    public function show(Request $request, int $id): View
    {
        $invitation = Invitation::query()
            ->with([
                'wedding.user',
                'wedding.events',
                'wedding.guests' => function ($q) {
                    $q->latest('id');
                },
                'wedding.guestResponses' => function ($q) {
                    $q->latest('id');
                },
                'wedding.wishes' => function ($q) {
                    $q->latest('id');
                },
                'wedding.gifts' => function ($q) {
                    $q->latest('id');
                },
                'template',
                'orders.plan',
            ])
            ->findOrFail($id);

        $allTemplates = InvitationTemplate::query()
            ->select('id', 'name', 'slug')
            ->orderBy('name')
            ->get();

        // Safe Support Lens (ADM-A06-025 / Innovation #7):
        // Default is TRUE (PII masked for groom/bride bank info and guest phone numbers)
        $isSafeLens = $request->input('safe_lens', '1') !== '0';

        if (! $isSafeLens) {
            AuditLogger::log(
                'view_unmasked_pii',
                'Invitation',
                $invitation->id,
                "Xem dữ liệu PII đầy đủ (không che) của thiệp \"{$invitation->slug}\"",
                ['invitation_id' => $invitation->id, 'slug' => $invitation->slug]
            );
        }

        // Invitation Quality Passport (Innovation #2)
        $qualityPassport = InvitationQualityPassportService::inspect($invitation);

        // Entitlements & Subscription State Machine (NXT-08)
        $subscriptionState = EntitlementStateMachineService::getSubscriptionState($invitation);
        $stateLabel = EntitlementStateMachineService::getStateLabel($subscriptionState);
        $activePlan = EntitlementStateMachineService::getActivePlan($invitation);
        $grantedEntitlements = EntitlementStateMachineService::getGrantedEntitlements($invitation);

        return view('admin.pages.invitations.show', [
            'invitation' => $invitation,
            'wedding' => $invitation->wedding,
            'template' => $invitation->template,
            'allTemplates' => $allTemplates,
            'isSafeLens' => $isSafeLens,
            'qualityPassport' => $qualityPassport,
            'subscriptionState' => $subscriptionState,
            'stateLabel' => $stateLabel,
            'activePlan' => $activePlan,
            'grantedEntitlements' => $grantedEntitlements,
        ]);
    }

    /**
     * Pre-Wedding Change Impact Simulator (Innovation #3).
     * Mô phỏng phạm vi ảnh hưởng trước khi thay đổi ngày cưới, chuyển mẫu hoặc khóa thiệp.
     */
    public function simulateImpact(Request $request, int $id): JsonResponse
    {
        $changeType = $request->input('change_type', 'wedding_date');
        $newValue = $request->input('new_value');

        $invitation = Invitation::with([
            'wedding.guests.response',
            'wedding.wishes',
            'wedding.tables',
            'template',
        ])->findOrFail($id);

        $wedding = $invitation->wedding;
        $totalGuests = $wedding ? $wedding->guests->count() : 0;
        $confirmedRsvps = $wedding ? $wedding->guests->filter(function ($g) {
            return $g->response && $g->response->attending_status === 'attending';
        })->count() : 0;
        $wishesCount = $wedding ? $wedding->wishes->count() : 0;
        $tablesCount = $wedding ? $wedding->tables->count() : 0;

        $warnings = [];
        $severity = 'low';

        if ($changeType === 'wedding_date') {
            if ($confirmedRsvps > 0) {
                $severity = 'high';
                $warnings[] = "Có {$confirmedRsvps} quan khách đã bấm xác nhận tham dự (RSVP) cho ngày cưới cũ.";
                $warnings[] = 'Lịch nhắc hẹn tự động và mã QR điểm danh của khách mời sẽ cần gửi lại qua Zalo/SMS.';
            } else {
                $severity = 'medium';
                $warnings[] = "Tổng số {$totalGuests} khách trong danh bạ chưa nhận được ngày cưới mới.";
            }

            if ($invitation->is_published) {
                $warnings[] = "Thiệp đang ở trạng thái CÔNG KHAI (đã có {$invitation->view_count} lượt xem). Khách truy cập sẽ thấy ngày mới ngay khi lưu.";
            }
        } elseif ($changeType === 'template') {
            $severity = 'medium';
            $warnings[] = 'Chuyển sang giao diện mẫu mới sẽ thay đổi cách dàn trang và vị trí album ảnh.';
            $warnings[] = 'Cần kiểm tra lại Hộ Chiếu Chất Lượng (Quality Passport) sau khi chuyển đổi mẫu.';
        } elseif ($changeType === 'lock') {
            $severity = 'high';
            $warnings[] = 'Khóa thiệp sẽ lập tức hiển thị màn hình thông báo tạm dừng đối với 100% quan khách truy cập link.';
            $warnings[] = 'Toàn bộ form gửi lời chúc mừng và điểm danh QR tại sảnh tiệc sẽ tạm ngưng hoạt động.';
        }

        $recommendation = match ($severity) {
            'high' => 'Hành động rủi ro cao: Vui lòng liên hệ và thống nhất trước với cô dâu chú rể trước khi thực hiện.',
            'medium' => 'Hành động trung bình: Khuyên bạn nên mở bản xem thử trước khi xác nhận cập nhật.',
            default => 'Hành động an toàn: Không ảnh hưởng lớn đến trải nghiệm khách mời.',
        };

        return response()->json([
            'success' => true,
            'change_type' => $changeType,
            'new_value' => $newValue,
            'severity' => $severity,
            'invitation' => [
                'id' => $invitation->id,
                'slug' => $invitation->slug,
                'is_published' => (bool) $invitation->is_published,
                'view_count' => $invitation->view_count,
            ],
            'wedding' => [
                'groom_name' => $wedding?->groom_name,
                'bride_name' => $wedding?->bride_name,
                'current_wedding_date' => $wedding?->wedding_date?->format('d/m/Y'),
            ],
            'metrics' => [
                'total_guests' => $totalGuests,
                'confirmed_rsvps' => $confirmedRsvps,
                'wishes_count' => $wishesCount,
                'tables_count' => $tablesCount,
            ],
            'warnings' => $warnings,
            'recommendation' => $recommendation,
        ]);
    }

    /**
     * Guest Journey Synthetic Rehearsal (Innovation #4).
     * Môi trường giả lập cho Admin / Staff diễn tập toàn bộ hành trình trải nghiệm của khách mời.
     */
    public function syntheticRehearsal(Request $request, int $id): View
    {
        $invitation = Invitation::with([
            'wedding.tables',
            'wedding.guests',
            'template',
        ])->findOrFail($id);

        $rehearsalGuest = [
            'name' => 'Quan Khách Diễn Tập (Synthetic VIP)',
            'phone' => '0999888777',
            'group_name' => 'Khách VIP Giả Lập',
            'table_name' => $invitation->wedding?->tables?->first()?->name ?? 'Bàn Danh Dự 01',
            'expected_attendees' => 2,
        ];

        return view('admin.pages.invitations.rehearsal', [
            'invitation' => $invitation,
            'wedding' => $invitation->wedding,
            'template' => $invitation->template,
            'rehearsalGuest' => $rehearsalGuest,
        ]);
    }

    /**
     * Chạy Diễn Tập Tự Động Toàn Diện E2E Runner (NXT-05).
     */
    public function runRehearsal(Request $request, int $id): JsonResponse
    {
        $invitation = Invitation::with(['wedding.tables', 'template'])->findOrFail($id);

        $results = GuestJourneyRehearsalService::run($invitation, $request->user());

        AuditLogger::log(
            'run_guest_journey_rehearsal',
            'Invitation',
            $invitation->id,
            "Thực thi diễn tập tự động E2E cho thiệp #{$invitation->id} ({$invitation->slug}). Kết quả: {$results['overall_status']} ({$results['stages_passed']}/5 bước đạt chuẩn)",
            [
                'invitation_id' => $invitation->id,
                'overall_status' => $results['overall_status'],
                'stages_passed' => $results['stages_passed'],
                'total_latency_ms' => $results['total_latency_ms'],
                'db_pollution_bytes' => $results['db_pollution_bytes'],
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $results,
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

        AuditLogger::log(
            'toggle_publish',
            'Invitation',
            $invitation->id,
            "Đã chuyển trạng thái thiệp \"{$invitation->slug}\" sang ".($newStatus ? 'Xuất bản' : 'Bản nháp'),
            ['slug' => $invitation->slug, 'is_published' => $newStatus]
        );

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

        AuditLogger::log(
            'extend_invitation',
            'Invitation',
            $invitation->id,
            "Gia hạn hiển thị thiệp \"{$invitation->slug}\" thêm {$days} ngày",
            ['slug' => $invitation->slug, 'days' => $days]
        );

        return back()->with('success', "Đã gia hạn thời gian hiển thị thiệp cưới thêm {$days} ngày.");
    }

    /**
     * Migrate the invitation to another template.
     */
    public function migrateTemplate(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'template_id' => 'required|integer|exists:invitation_templates,id',
        ]);

        $invitation = Invitation::findOrFail($id);
        $targetTemplate = InvitationTemplate::findOrFail((int) $request->input('template_id'));

        $invitation->update([
            'template_id' => $targetTemplate->id,
        ]);

        AuditLogger::log(
            'migrate_template',
            'Invitation',
            $invitation->id,
            "Chuyển đổi mẫu thiệp \"{$invitation->slug}\" sang mẫu \"{$targetTemplate->name}\"",
            ['slug' => $invitation->slug, 'template_id' => $targetTemplate->id, 'template_name' => $targetTemplate->name]
        );

        return back()->with('success', "Đã chuyển đổi mẫu thiệp sang \"{$targetTemplate->name}\" thành công.");
    }

    /**
     * Export guests list of an invitation as UTF-8 CSV with BOM for Excel.
     */
    public function exportGuests(int $id): StreamedResponse
    {
        $invitation = Invitation::with(['wedding.guests'])->findOrFail($id);
        $wedding = $invitation->wedding;
        $guests = $wedding?->guests ?? collect();

        $filename = "danh-sach-khach-moi-{$invitation->slug}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($guests) {
            $file = fopen('php://output', 'w');
            // Write UTF-8 BOM so Excel opens Vietnamese characters cleanly
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['STT', 'Họ Và Tên', 'Số Điện Thoại', 'Nhóm Khách', 'Trạng Thái Gửi', 'Đã Xem Thiệp']);

            foreach ($guests as $idx => $guest) {
                fputcsv($file, CsvSanitizerService::row([
                    $idx + 1,
                    $guest->name,
                    $guest->phone ?? '',
                    $guest->group_name ?? 'Chung',
                    $guest->is_sent ? 'Đã gửi' : 'Chưa gửi',
                    $guest->is_opened ? 'Đã xem' : 'Chưa xem',
                ]));
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    /**
     * Export wedding gifts list of an invitation as UTF-8 CSV with BOM for Excel.
     */
    public function exportGifts(int $id): StreamedResponse
    {
        $invitation = Invitation::with(['wedding.gifts'])->findOrFail($id);
        $wedding = $invitation->wedding;
        $gifts = $wedding?->gifts ?? collect();

        $filename = "so-mung-cuoi-{$invitation->slug}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($gifts) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, ['STT', 'Người Mừng Cưới', 'Nhóm Khách', 'Số Tiền (VNĐ)', 'Hình Thức Mừng', 'Lời Chúc / Ghi Chú', 'Đã Cảm Ơn', 'Thời Gian']);

            foreach ($gifts as $idx => $gift) {
                fputcsv($file, CsvSanitizerService::row([
                    $idx + 1,
                    $gift->sender_name,
                    $gift->sender_group ?? '',
                    number_format($gift->amount, 0, ',', '.'),
                    $gift->payment_method_label,
                    $gift->notes ?? '',
                    $gift->is_acknowledged ? 'Đã cảm ơn' : 'Chưa cảm ơn',
                    $gift->created_at ? $gift->created_at->format('d/m/Y H:i') : '',
                ]));
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    /**
     * Export RSVP responses list of an invitation as UTF-8 CSV with BOM for Excel.
     */
    public function exportRsvps(int $id): StreamedResponse
    {
        $invitation = Invitation::with(['wedding.guestResponses'])->findOrFail($id);
        $wedding = $invitation->wedding;
        $responses = $wedding?->guestResponses ?? collect();

        $filename = "danh-sach-rsvp-{$invitation->slug}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($responses) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'STT',
                'Tên Quan Khách',
                'Xác Nhận Tham Dự',
                'Người Lớn',
                'Trẻ Em',
                'Tổng Người',
                'Số Điện Thoại',
                'Yêu Cầu Ăn Uống / Lời Nhắn',
                'Thời Gian Phản Hồi',
            ]);

            foreach ($responses as $idx => $resp) {
                $statusLabel = match ($resp->attending_status) {
                    'attending' => 'Có tham dự',
                    'declined' => 'Không tham dự',
                    'uncertain' => 'Chưa chắc chắn',
                    default => $resp->attending_status ?? 'Chưa rõ',
                };

                $adults = (int) ($resp->adults_count ?? 1);
                $children = (int) ($resp->children_count ?? 0);
                $total = $adults + $children;

                fputcsv($file, CsvSanitizerService::row([
                    $idx + 1,
                    $resp->guest_name,
                    $statusLabel,
                    $adults,
                    $children,
                    $total,
                    $resp->phone ?? '',
                    $resp->dietary_requirements ?? '',
                    $resp->created_at ? $resp->created_at->format('d/m/Y H:i') : '',
                ]));
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    /**
     * Export wishes list of an invitation as UTF-8 CSV with BOM for Excel.
     */
    public function exportWishes(int $id): StreamedResponse
    {
        $invitation = Invitation::with(['wedding.wishes'])->findOrFail($id);
        $wedding = $invitation->wedding;
        $wishes = $wedding?->wishes ?? collect();

        $filename = "loi-chuc-mung-{$invitation->slug}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($wishes) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, [
                'STT',
                'Người Gửi Lời Chúc',
                'Nội Dung Lời Chúc',
                'Phản Hồi Của Cặp Đôi',
                'Thời Gian Gửi',
                'Trạng Thái Duyệt',
            ]);

            foreach ($wishes as $idx => $wish) {
                fputcsv($file, CsvSanitizerService::row([
                    $idx + 1,
                    $wish->name ?? $wish->sender_name ?? '',
                    $wish->message ?? '',
                    $wish->reply ?? '',
                    $wish->created_at ? $wish->created_at->format('d/m/Y H:i') : '',
                    $wish->is_approved ? 'Đã duyệt' : 'Chờ duyệt',
                ]));
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }

    /**
     * Lock an invitation due to spam, terms violation, or payment dispute.
     */
    public function lock(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $invitation = Invitation::findOrFail($id);
        $reason = trim((string) $request->input('reason', ''));
        if ($reason === '') {
            $reason = 'Thiệp cưới tạm thời bị khóa do yêu cầu từ Quản trị viên.';
        }

        $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode($invitation->custom_config, true) ?? []);
        $config['is_locked'] = true;
        $config['lock_reason'] = $reason;
        $config['locked_at'] = now()->toIso8601String();

        $invitation->update([
            'custom_config' => $config,
        ]);

        AuditLogger::log(
            'lock_invitation',
            'Invitation',
            $invitation->id,
            "Khóa thiệp cưới \"{$invitation->slug}\" với lý do: {$reason}",
            ['slug' => $invitation->slug, 'reason' => $reason]
        );

        return back()->with('success', "Đã khóa thiệp cưới \"{$invitation->slug}\" thành công.");
    }

    /**
     * Unlock an invitation.
     */
    public function unlock(int $id): RedirectResponse
    {
        $invitation = Invitation::findOrFail($id);

        $config = is_array($invitation->custom_config) ? $invitation->custom_config : (json_decode($invitation->custom_config, true) ?? []);
        unset($config['is_locked'], $config['lock_reason'], $config['locked_at']);

        $invitation->update([
            'custom_config' => $config,
        ]);

        AuditLogger::log(
            'unlock_invitation',
            'Invitation',
            $invitation->id,
            "Mở khóa thiệp cưới \"{$invitation->slug}\"",
            ['slug' => $invitation->slug]
        );

        return back()->with('success', "Đã mở khóa thiệp cưới \"{$invitation->slug}\" thành công.");
    }

    /**
     * Admin can thiệp cấp quyền hoặc thu hồi quyền lợi ngoại lệ (NXT-08).
     */
    public function overrideEntitlement(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        if (! $user || (! $user->isManager() && ! $user->isSuperAdmin())) {
            abort(403, 'Chỉ Quản lý (Manager) hoặc Quản trị viên tối cao mới có quyền can thiệp ngoại lệ gói cước.');
        }

        $validFeatures = array_keys(EntitlementStateMachineService::ENTITLEMENT_CATALOG);

        $request->validate([
            'feature_key' => ['required', 'string', 'in:'.implode(',', $validFeatures)],
            'action' => ['required', 'string', 'in:grant,revoke,reset'],
            'reason' => ['required', 'string', 'min:6', 'max:255'],
        ], [
            'reason.required' => 'Vui lòng cung cấp lý do can thiệp ngoại lệ để lưu vào Nhật ký kiểm toán.',
            'reason.min' => 'Lý do can thiệp ngoại lệ phải có tối thiểu 6 ký tự.',
        ]);

        $invitation = Invitation::findOrFail($id);
        $featureKey = $request->input('feature_key');
        $action = $request->input('action');
        $reason = trim((string) $request->input('reason'));

        $grant = match ($action) {
            'grant' => true,
            'revoke' => false,
            'reset' => null,
        };

        EntitlementStateMachineService::setOverride($invitation, $featureKey, $grant, $reason, $user->id);

        $featureLabel = EntitlementStateMachineService::ENTITLEMENT_CATALOG[$featureKey]['label'] ?? $featureKey;
        $actionText = match ($action) {
            'grant' => "Cấp ngoại lệ tính năng [{$featureLabel}]",
            'revoke' => "Thu hồi ngoại lệ tính năng [{$featureLabel}]",
            'reset' => "Đặt lại tính năng [{$featureLabel}] về mặc định của gói cước",
        };

        AuditLogger::log(
            'override_entitlement',
            'Invitation',
            $invitation->id,
            "{$actionText} của thiệp \"{$invitation->slug}\". Lý do: {$reason}",
            [
                'invitation_id' => $invitation->id,
                'slug' => $invitation->slug,
                'feature_key' => $featureKey,
                'action' => $action,
                'reason' => $reason,
            ]
        );

        return back()->with('success', "Đã cập nhật quyền lợi: {$actionText} thành công.");
    }
}
