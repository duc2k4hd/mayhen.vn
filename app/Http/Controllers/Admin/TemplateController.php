<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\InvitationTemplateRelease;
use App\Services\AuditLogger;
use App\Services\CsvSanitizerService;
use App\Services\InvitationTemplateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TemplateController extends Controller
{
    /**
     * Display all templates with filters and KPI metrics.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $category = $request->input('category');
        $status = $request->input('status');

        $query = InvitationTemplate::query()->withCount('invitations');

        // 1. Text Search: Name, Slug
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // 2. Category Filter
        if (! empty($category)) {
            $query->where('category', $category);
        }

        // 3. Status Filter
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status === 'featured') {
            $query->where('is_featured', true);
        }

        $templates = $query->latest('id')->paginate(15)->withQueryString();

        // Summary KPI Metrics
        $totalCount = InvitationTemplate::count();
        $activeCount = InvitationTemplate::where('is_active', true)->count();
        $featuredCount = InvitationTemplate::where('is_featured', true)->count();
        $totalInvitationsCount = Invitation::count();

        return view('admin.pages.templates.index', [
            'templates' => $templates,
            'currentSearch' => $search,
            'currentCategory' => $category,
            'currentStatus' => $status,
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
            'featuredCount' => $featuredCount,
            'totalInvitationsCount' => $totalInvitationsCount,
        ]);
    }

    /**
     * Display the template edit form alongside the Schema Validator Inspector.
     */
    public function edit(int $id): View
    {
        $template = InvitationTemplate::withCount('invitations')->findOrFail($id);

        // Comprehensive Schema & Code Integrity Inspection (Wave 04)
        $audit = InvitationTemplateService::inspectTemplateIntegrity($template->slug);

        // Template Visual DNA (Innovation #10)
        $visualDna = InvitationTemplateService::extractVisualDna($template->slug);

        return view('admin.pages.templates.edit', [
            'template' => $template,
            'audit' => $audit,
            'visualDna' => $visualDna,
            'schemaExists' => $audit['files']['schema_json']['exists'] ?? false,
            'rawSchema' => $audit['raw_schema'] ?? [],
            'sectionsReport' => $audit['sections_report'] ?? [],
            'isCanonicalValid' => $audit['is_canonical_valid'] ?? false,
            'galleryValid' => $audit['gallery_valid'] ?? false,
        ]);
    }

    /**
     * Update template metadata and tier configurations.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $template = InvitationTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:500',
            'category' => 'required|string|max:50',
            'tier' => 'required|string|in:free,plus,pro',
            'thumbnail_url' => 'nullable|string|max:500',
            'is_active' => 'nullable',
            'is_featured' => 'nullable',
        ]);

        $defaultConfig = $template->default_config ?? [];
        $defaultConfig['tier'] = $validated['tier'];
        if (isset($validated['description'])) {
            $defaultConfig['description'] = $validated['description'];
        }

        $template->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'thumbnail_url' => $validated['thumbnail_url'] ?? $template->thumbnail_url,
            'is_active' => $request->has('is_active'),
            'is_featured' => $request->has('is_featured'),
            'default_config' => $defaultConfig,
        ]);

        InvitationTemplate::clearTemplateCaches($template);

        AuditLogger::log(
            'update_template',
            'InvitationTemplate',
            $template->id,
            "Cập nhật cấu hình mẫu thiệp '{$template->name}' (Tier: {$validated['tier']})",
            ['tier' => $validated['tier'], 'is_active' => $template->is_active, 'is_featured' => $template->is_featured]
        );

        return redirect()->route('admin.templates.index')->with('success', "Đã cập nhật thông tin mẫu thiệp \"{$template->name}\" thành công.");
    }

    /**
     * Toggle active status (Supports both AJAX and standard form submissions).
     */
    public function toggleActive(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $template = InvitationTemplate::findOrFail($id);
        $template->update(['is_active' => ! $template->is_active]);

        InvitationTemplate::clearTemplateCaches($template);

        AuditLogger::log(
            'toggle_template_active',
            'InvitationTemplate',
            $template->id,
            ($template->is_active ? 'Kích hoạt hiển thị' : 'Tạm ẩn')." mẫu thiệp '{$template->name}'",
            ['is_active' => $template->is_active]
        );

        $msg = $template->is_active ? 'Đã kích hoạt hiển thị mẫu thiệp.' : 'Đã tạm ẩn mẫu thiệp khỏi hệ thống.';

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $template->is_active,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Toggle featured status (Supports both AJAX and standard form submissions).
     */
    public function toggleFeatured(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $template = InvitationTemplate::findOrFail($id);
        $template->update(['is_featured' => ! $template->is_featured]);

        InvitationTemplate::clearTemplateCaches($template);

        AuditLogger::log(
            'toggle_template_featured',
            'InvitationTemplate',
            $template->id,
            ($template->is_featured ? 'Ghim nổi bật' : 'Bỏ ghim nổi bật')." mẫu thiệp '{$template->name}'",
            ['is_featured' => $template->is_featured]
        );

        $msg = $template->is_featured ? 'Đã ghim mẫu thiệp nổi bật.' : 'Đã bỏ ghim nổi bật của mẫu thiệp.';

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_featured' => $template->is_featured,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Scan disk directories in resources/views/client/invitations and sync them into DB.
     */
    public function scanDiscovery(): RedirectResponse
    {
        $templatesDir = resource_path('views/client/invitations');
        $syncedCount = 0;

        if (File::isDirectory($templatesDir)) {
            $directories = File::directories($templatesDir);

            foreach ($directories as $dir) {
                $slug = basename($dir);
                if ($slug === 'partials') {
                    continue;
                }

                $schemaFile = "{$dir}/schema.json";
                $templateName = ucwords(str_replace('-', ' ', $slug));
                $description = 'Mẫu thiệp cưới cao cấp phong cách Quiet Luxury.';
                $category = 'modern';
                $tier = 'plus';

                if (File::exists($schemaFile)) {
                    $schemaData = json_decode(File::get($schemaFile), true) ?? [];
                    if (! empty($schemaData['name'])) {
                        $templateName = $schemaData['name'];
                    }
                    if (! empty($schemaData['description'])) {
                        $description = $schemaData['description'];
                    }
                    if (! empty($schemaData['category'])) {
                        $category = $schemaData['category'];
                    }
                }

                $thumbnailUrl = "/mau-thiep/{$slug}/assets/images/hero-couple.webp";
                $localThumb = "{$dir}/assets/images/hero-couple.webp";
                if (! File::exists($localThumb)) {
                    $thumbnailUrl = '/assets/client/images/sample-hero.webp';
                }

                $template = InvitationTemplate::firstOrNew(['slug' => $slug]);
                $template->name = $templateName;
                $template->category = $category;
                $template->thumbnail_url = $thumbnailUrl;

                $config = $template->default_config ?? [];
                $config['description'] = $description;
                $config['tier'] = $config['tier'] ?? $tier;
                $config['complexity'] = $config['complexity'] ?? 'standard';
                $template->default_config = $config;

                if (! $template->exists) {
                    $template->is_active = true;
                    $template->is_featured = false;
                }

                $template->save();
                $syncedCount++;
            }
        }

        InvitationTemplate::clearTemplateCaches();

        AuditLogger::log(
            'scan_templates',
            'InvitationTemplate',
            0,
            "Quét và đồng bộ {$syncedCount} mẫu thiệp cưới từ kho mã nguồn",
            ['synced_count' => $syncedCount]
        );

        return redirect()->route('admin.templates.index')->with('success', "Đã quét và đồng bộ thành công {$syncedCount} mẫu thiệp cưới từ kho mã nguồn.");
    }

    /**
     * Export templates directory data as UTF-8 BOM CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $search = trim((string) $request->input('q', ''));
        $category = $request->input('category');
        $status = $request->input('status');

        $query = InvitationTemplate::query()->withCount('invitations');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if (! empty($category)) {
            $query->where('category', $category);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($status === 'featured') {
            $query->where('is_featured', true);
        }

        $templates = $query->latest('id')->get();
        $filename = 'kho-mau-thiep-mayhen-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($templates) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'STT',
                'Mã Mẫu',
                'Tên Mẫu Thiệp',
                'Slug Kỹ Thuật',
                'Phong Cách Thiết Kế',
                'Phân Hạng Gói',
                'Số Thiệp Đã Tạo',
                'Ghim Nổi Bật',
                'Trạng Thái Hoạt Động',
                'Ngày Khởi Tạo',
            ]);

            $index = 1;
            foreach ($templates as $tpl) {
                $tierLabel = match ($tpl->tier) {
                    'free' => 'Gói Miễn Phí',
                    'pro' => 'Gói Pro VIP',
                    default => 'Gói Hạnh Phúc (Plus)',
                };

                fputcsv($handle, CsvSanitizerService::row([
                    $index++,
                    $tpl->id,
                    $tpl->name,
                    $tpl->slug,
                    $tpl->category_label,
                    $tierLabel,
                    $tpl->invitations_count,
                    $tpl->is_featured ? 'Có (Nổi bật)' : 'Không',
                    $tpl->is_active ? 'Đang hoạt động' : 'Tạm ẩn',
                    $tpl->created_at ? $tpl->created_at->format('d/m/Y H:i:s') : 'N/A',
                ]));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Màn hình Phòng Thí Nghiệm Phát Hành & Visual Regression Lab (NXT-04).
     */
    public function releaseLab(int $id): View
    {
        $template = InvitationTemplate::withCount('invitations')
            ->with(['releases' => fn ($q) => $q->with(['creator', 'publisher'])->latest('id')])
            ->findOrFail($id);

        $preflight = InvitationTemplateService::runPreflightAudit($template->slug);
        $visualDna = InvitationTemplateService::extractVisualDna($template->slug);

        return view('admin.pages.templates.release_lab', [
            'template' => $template,
            'preflight' => $preflight,
            'visualDna' => $visualDna,
            'releases' => $template->releases,
            'viewports' => $preflight['viewports'] ?? [375, 430, 768, 1440],
        ]);
    }

    /**
     * Tạo bản phát hành thử nghiệm mới (Staged Release).
     */
    public function createRelease(Request $request, int $id): RedirectResponse
    {
        $template = InvitationTemplate::findOrFail($id);

        $validated = $request->validate([
            'version' => [
                'required',
                'string',
                'max:30',
                'regex:/^v?\d+(\.\d+){1,2}$/',
            ],
            'rollout_percentage' => 'required|integer|min:0|max:100',
            'release_notes' => 'nullable|string|max:2000',
        ], [
            'version.regex' => 'Định dạng phiên bản phải chuẩn Semantic Versioning (ví dụ: v1.0.0, v1.1, 2.0.1).',
            'rollout_percentage.min' => 'Tỷ lệ triển khai tối thiểu là 0%.',
            'rollout_percentage.max' => 'Tỷ lệ triển khai tối đa là 100%.',
        ]);

        // Đảm bảo không trùng version đã tồn tại của template này
        $exists = $template->releases()->where('version', $validated['version'])->exists();
        if ($exists) {
            return back()->withErrors(['version' => "Phiên bản {$validated['version']} đã tồn tại trong lịch sử phát hành của mẫu thiệp này."])->withInput();
        }

        $preflight = InvitationTemplateService::runPreflightAudit($template->slug);

        DB::transaction(function () use ($template, $validated, $preflight, $request) {
            $release = InvitationTemplateRelease::create([
                'template_id' => $template->id,
                'version' => $validated['version'],
                'release_status' => 'staged',
                'rollout_percentage' => (int) $validated['rollout_percentage'],
                'release_notes' => $validated['release_notes'] ?? null,
                'preflight_snapshot' => $preflight,
                'creator_id' => $request->user()?->id,
            ]);

            $template->update([
                'staged_version' => $release->version,
                'release_status' => 'staged',
                'staged_at' => now(),
            ]);

            AuditLogger::log(
                'create_template_release',
                'InvitationTemplateRelease',
                $release->id,
                "Khởi tạo bản phát hành thử nghiệm '{$release->version}' cho mẫu '{$template->name}' (Rollout: {$release->rollout_percentage}%)",
                [
                    'template_id' => $template->id,
                    'version' => $release->version,
                    'rollout_percentage' => $release->rollout_percentage,
                    'is_ready_for_release' => $preflight['is_ready_for_release'],
                ]
            );
        });

        return redirect()->route('admin.templates.release_lab', $template->id)
            ->with('success', "Đã tạo bản phát hành thử nghiệm phiên bản \"{$validated['version']}\" thành công.");
    }

    /**
     * Phê duyệt & Công bố phiên bản ra toàn hệ thống (Publish Release).
     */
    public function publishRelease(Request $request, int $id, int $releaseId): RedirectResponse
    {
        $template = InvitationTemplate::findOrFail($id);
        $release = InvitationTemplateRelease::where('template_id', $template->id)->findOrFail($releaseId);

        // Chạy lại Preflight Audit tại thời điểm phê duyệt
        $preflight = InvitationTemplateService::runPreflightAudit($template->slug);
        if (! $preflight['is_ready_for_release']) {
            $blockerMsg = implode(', ', $preflight['blockers']);

            return back()->withErrors([
                'publish_error' => "Không thể công bố phiên bản vì chưa vượt qua Preflight Quality Gate: {$blockerMsg}",
            ]);
        }

        DB::transaction(function () use ($template, $release, $preflight, $request) {
            // Đánh dấu các bản published trước đó thành deprecated
            InvitationTemplateRelease::where('template_id', $template->id)
                ->where('release_status', 'published')
                ->where('id', '!=', $release->id)
                ->update(['release_status' => 'deprecated']);

            $oldVersion = $template->current_version;

            $release->update([
                'release_status' => 'published',
                'publisher_id' => $request->user()?->id,
                'published_at' => now(),
                'preflight_snapshot' => $preflight,
            ]);

            $template->update([
                'previous_version' => $oldVersion,
                'current_version' => $release->version,
                'staged_version' => null,
                'release_status' => 'published',
                'rollout_percentage' => $release->rollout_percentage,
                'release_notes' => $release->release_notes,
                'published_at' => now(),
            ]);

            InvitationTemplate::clearTemplateCaches($template);

            AuditLogger::log(
                'publish_template_release',
                'InvitationTemplateRelease',
                $release->id,
                "Công bố chính thức phiên bản '{$release->version}' cho mẫu '{$template->name}' (Từ bản cũ: '{$oldVersion}')",
                [
                    'template_id' => $template->id,
                    'old_version' => $oldVersion,
                    'new_version' => $release->version,
                    'rollout_percentage' => $release->rollout_percentage,
                ]
            );
        });

        return redirect()->route('admin.templates.release_lab', $template->id)
            ->with('success', "Đã công bố chính thức phiên bản \"{$release->version}\" cho mẫu thiệp \"{$template->name}\".");
    }

    /**
     * Hoàn nguyên tức thì về phiên bản ổn định trước đó (1-Click Instant Rollback).
     */
    public function rollbackRelease(Request $request, int $id, int $releaseId): RedirectResponse
    {
        $template = InvitationTemplate::findOrFail($id);
        $targetRelease = InvitationTemplateRelease::where('template_id', $template->id)->findOrFail($releaseId);

        $validated = $request->validate([
            'rollback_reason' => 'required|string|min:6|max:255',
        ], [
            'rollback_reason.required' => 'Bắt buộc nhập lý do hoàn nguyên (rollback) để lưu vết kiểm toán.',
            'rollback_reason.min' => 'Lý do hoàn nguyên phải từ 6 ký tự trở lên.',
        ]);

        DB::transaction(function () use ($template, $targetRelease, $validated, $request) {
            $currentVersion = $template->current_version;

            // Đánh dấu bản phát hành hiện tại thành rolled_back
            InvitationTemplateRelease::where('template_id', $template->id)
                ->where('version', $currentVersion)
                ->update([
                    'release_status' => 'rolled_back',
                    'rolled_back_at' => now(),
                    'rollback_reason' => $validated['rollback_reason'],
                ]);

            // Chuyển bản targetRelease thành published
            $targetRelease->update([
                'release_status' => 'published',
                'published_at' => now(),
                'rollback_reason' => null,
            ]);

            $template->update([
                'previous_version' => $currentVersion,
                'current_version' => $targetRelease->version,
                'staged_version' => null,
                'release_status' => 'published',
                'rollout_percentage' => 100, // Hoàn nguyên về mức an toàn 100%
                'rolled_back_at' => now(),
            ]);

            InvitationTemplate::clearTemplateCaches($template);

            AuditLogger::log(
                'rollback_template_release',
                'InvitationTemplateRelease',
                $targetRelease->id,
                "Hoàn nguyên khẩn cấp mẫu '{$template->name}' từ '{$currentVersion}' về '{$targetRelease->version}'. Lý do: {$validated['rollback_reason']}",
                [
                    'template_id' => $template->id,
                    'rolled_back_from' => $currentVersion,
                    'rolled_back_to' => $targetRelease->version,
                    'reason' => $validated['rollback_reason'],
                    'admin_id' => $request->user()?->id,
                ]
            );
        });

        return redirect()->route('admin.templates.release_lab', $template->id)
            ->with('success', "Đã hoàn nguyên thành công mẫu thiệp \"{$template->name}\" về phiên bản an toàn \"{$targetRelease->version}\".");
    }

    /**
     * Cập nhật tỷ lệ phân phối thử nghiệm (Canary Staged Rollout).
     */
    public function updateRollout(Request $request, int $id, int $releaseId): RedirectResponse|JsonResponse
    {
        $template = InvitationTemplate::findOrFail($id);
        $release = InvitationTemplateRelease::where('template_id', $template->id)->findOrFail($releaseId);

        $validated = $request->validate([
            'rollout_percentage' => 'required|integer|min:0|max:100',
        ]);

        $newPercentage = (int) $validated['rollout_percentage'];

        DB::transaction(function () use ($template, $release, $newPercentage) {
            $release->update(['rollout_percentage' => $newPercentage]);

            if ($release->release_status === 'published' || $release->release_status === 'staged') {
                $template->update(['rollout_percentage' => $newPercentage]);
            }

            InvitationTemplate::clearTemplateCaches($template);

            AuditLogger::log(
                'update_template_rollout',
                'InvitationTemplateRelease',
                $release->id,
                "Điều chỉnh tỷ lệ triển khai phiên bản '{$release->version}' lên {$newPercentage}%",
                ['template_id' => $template->id, 'rollout_percentage' => $newPercentage]
            );
        });

        $msg = "Đã cập nhật tỷ lệ triển khai thành công lên {$newPercentage}%.";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'rollout_percentage' => $newPercentage,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }
}
