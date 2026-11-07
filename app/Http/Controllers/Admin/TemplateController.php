<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Services\InvitationTemplateService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        // Schema Integrity Inspection
        $schemaFilePath = resource_path("views/client/invitations/{$template->slug}/schema.json");
        $schemaExists = File::exists($schemaFilePath);
        $rawSchema = [];
        $sectionsReport = [];
        $isCanonicalValid = false;
        $galleryValid = false;

        if ($schemaExists) {
            $rawSchema = json_decode(File::get($schemaFilePath), true) ?? [];
            $presentSectionIds = collect($rawSchema['sections'] ?? [])->pluck('id')->filter()->all();

            $canonicalSections = InvitationTemplateService::CANONICAL_SECTION_ORDER;
            $canonicalDefinitions = InvitationTemplateService::getCanonicalSections();

            foreach ($canonicalSections as $canonId) {
                $isPassed = in_array($canonId, $presentSectionIds, true);
                $title = $canonicalDefinitions[$canonId]['title'] ?? ucfirst(str_replace('_', ' ', $canonId));

                $sectionsReport[] = [
                    'id' => $canonId,
                    'title' => $title,
                    'passed' => $isPassed,
                ];
            }

            // Check 10/10 section completeness
            $passedCount = collect($sectionsReport)->where('passed', true)->count();
            $isCanonicalValid = ($passedCount >= 10);

            // Check Album 10 photos standard
            foreach ($rawSchema['sections'] ?? [] as $sec) {
                foreach ($sec['fields'] ?? [] as $f) {
                    if (($f['key'] ?? '') === 'wedding.extra_data.gallery') {
                        $galleryValid = true;
                        break 2;
                    }
                }
            }
        }

        return view('admin.pages.templates.edit', [
            'template' => $template,
            'schemaExists' => $schemaExists,
            'rawSchema' => $rawSchema,
            'sectionsReport' => $sectionsReport,
            'isCanonicalValid' => $isCanonicalValid,
            'galleryValid' => $galleryValid,
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

                fputcsv($handle, [
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
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
