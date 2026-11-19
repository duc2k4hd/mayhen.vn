<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\InvitationTemplate;
use App\Models\InvitationTemplateRelease;
use App\Models\PlatformSetting;
use App\Services\AuditLogger;
use App\Services\CsvSanitizerService;
use App\Services\InvitationTemplateService;
use App\Services\WeddingMusicService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
     * Show form for creating a new template manually.
     */
    public function create(): View
    {
        return view('admin.pages.templates.create', [
            'availableCategories' => [
                'luxury' => 'Hoàng Gia Tem Sáp (Luxury)',
                'modern' => 'Hiện Đại Sắc Nét (Modern)',
                'traditional' => 'Truyền Thống Á Đông (Traditional)',
                'floral' => 'Hoa Lá Thiên Nhiên (Floral)',
                'minimal' => 'Tối Giản Tinh Tế (Minimal)',
                'editorial' => 'Tạp Chí Editorial (Editorial)',
            ],
            'availableTiers' => [
                'free' => 'Gói Miễn Phí (Free)',
                'plus' => 'Gói Hạnh Phúc (Plus - Mặc định)',
                'pro' => 'Gói Cao Cấp VIP (Pro)',
            ],
        ]);
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:100|alpha_dash|unique:invitation_templates,slug',
            'category' => 'required|string|max:50',
            'tier' => 'required|string|in:free,plus,pro',
            'description' => 'nullable|string|max:1000',
            'thumbnail_url' => 'nullable|string|max:500',
            'thumbnail_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,svg|max:10240',
            'full_page_preview_url' => 'nullable|string|max:1000',
            'full_page_preview_file' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:25600',
            'is_active' => 'nullable',
            'is_featured' => 'nullable',
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        $baseSlug = $slug;
        $counter = 1;
        while (InvitationTemplate::where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        $thumbnailUrl = $validated['thumbnail_url'] ?? "/assets/client/images/templates/{$slug}/thumb.webp";
        if ($request->hasFile('thumbnail_file')) {
            $uploadedThumb = $this->handleFileUpload($request, 'thumbnail_file', $slug, 'thumbnail');
            if ($uploadedThumb) {
                $thumbnailUrl = $uploadedThumb;
            }
        }

        $defaultConfig = [
            'tier' => $validated['tier'],
            'description' => $validated['description'] ?? '',
        ];

        if ($request->hasFile('full_page_preview_file')) {
            $uploadedFullPreview = $this->handleFileUpload($request, 'full_page_preview_file', $slug, 'preview');
            if ($uploadedFullPreview) {
                $defaultConfig['full_page_preview'] = $uploadedFullPreview;
            }
        } elseif ($request->filled('full_page_preview_url')) {
            $defaultConfig['full_page_preview'] = trim((string) $request->input('full_page_preview_url'));
        }

        $template = InvitationTemplate::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'category' => $validated['category'],
            'thumbnail_url' => $thumbnailUrl,
            'demo_url' => "/xem-thu/{$slug}",
            'is_active' => $request->has('is_active'),
            'is_featured' => $request->has('is_featured'),
            'default_config' => $defaultConfig,
            'current_version' => '1.0.0',
            'release_status' => 'published',
            'published_at' => now(),
        ]);

        $templateDir = resource_path("views/client/invitations/{$slug}");
        if (! File::isDirectory($templateDir)) {
            File::makeDirectory($templateDir, 0755, true);
            File::makeDirectory("{$templateDir}/assets", 0755, true);
            File::makeDirectory("{$templateDir}/assets/images", 0755, true);

            $scaffoldBlade = <<<'BLADE'
            @extends('client.layouts.invitation')

            @section('title', 'Thiệp Cưới ' . ($templateData['groom_name'] ?? 'Chú Rể') . ' & ' . ($templateData['bride_name'] ?? 'Cô Dâu'))

            @push('styles')
                <style>
                    .mayhen_invitation_container {
                        max-width: 480px;
                        margin: 0 auto;
                        background: #FFFDF9;
                        min-height: 100vh;
                        position: relative;
                    }
                </style>
            @endpush

            @section('content')
            <div class="mayhen_invitation_container">
                {{-- Section 1: Opening Envelope --}}
                <section class="mayhen_invitation_opening" id="section-opening" data-sync-target="opening">
                    <!-- Opening content -->
                </section>

                {{-- Section 2: Hero Cover --}}
                <section class="mayhen_invitation_hero" id="section-hero">
                    <h1 data-sync-target="hero_title">{{ $templateData['groom_name'] ?? 'Minh Quân' }} & {{ $templateData['bride_name'] ?? 'Ái Như' }}</h1>
                </section>
            </div>
            @endsection
            BLADE;
            File::put("{$templateDir}/template.blade.php", $scaffoldBlade);
        }

        InvitationTemplate::clearTemplateCaches($template);

        AuditLogger::log(
            'create_template',
            'InvitationTemplate',
            $template->id,
            "Tạo mới mẫu thiệp cưới '{$template->name}' (Slug: {$slug})",
            ['tier' => $validated['tier'], 'category' => $validated['category']]
        );

        return redirect()->route('admin.templates.edit', $template->id)->with('success', "Đã tạo mẫu thiệp cưới \"{$template->name}\" thành công. Hãy bổ sung thông tin và ảnh cuộn toàn trang bên dưới.");
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

        // Danh sách bài hát phong phú phân tách rõ Tác Giả, Bài Nhạc cho Popup Chọn Nhạc
        $musicService = app(WeddingMusicService::class);
        $diskCatalog = $musicService->getCatalog();
        $curatedTracks = WeddingMusicService::getCuratedTracks();

        $musicCatalog = [];
        $seenUrls = [];

        // 1. Quét các bài hát từ đĩa lưu trữ (singer / musics)
        foreach ($diskCatalog as $song) {
            $url = $song['url'] ?? '';
            if (! empty($url) && ! isset($seenUrls[$url])) {
                $seenUrls[$url] = true;
                $musicCatalog[] = [
                    'id' => $song['id'] ?? md5($url),
                    'title' => $song['title'] ?? 'Bài Hát Tuyển Chọn',
                    'artist' => $song['artist'] ?? PlatformSetting::getBrandName().' Collection',
                    'genre' => 'V-Pop / Trữ Tình',
                    'duration' => $song['duration'] ?? '04:15',
                    'url' => $url,
                    'tag' => 'Lưu Trữ',
                    'description' => 'Giai điệu tuyển chọn chất lượng cao lưu trữ trong hệ thống '.PlatformSetting::getBrandName().'.',
                ];
            }
        }

        // 2. Thêm các bản nhạc cưới kinh điển tuyển chọn
        foreach ($curatedTracks as $song) {
            $url = $song['preview_url'] ?? ($song['url'] ?? '');
            if (! empty($url) && ! isset($seenUrls[$url])) {
                $seenUrls[$url] = true;
                $musicCatalog[] = [
                    'id' => $song['id'] ?? md5($url),
                    'title' => $song['title'] ?? 'Giai Điệu Cưới',
                    'artist' => $song['artist'] ?? 'Chamber Ensemble',
                    'genre' => $song['genre'] ?? 'Acoustic / Classical',
                    'duration' => $song['duration'] ?? '03:30',
                    'url' => $url,
                    'tag' => $song['tag'] ?? 'Tuyển Chọn',
                    'description' => $song['description'] ?? 'Tác phẩm hòa tấu lãng mạn cho ngày cưới.',
                ];
            }
        }

        // 3. Thêm bản nhạc mặc định theo thư mục mẫu thiệp nếu có
        $defaultTemplateAudio = "/mau-thiep/{$template->slug}/assets/wedding-song.mp3";
        if (! isset($seenUrls[$defaultTemplateAudio])) {
            $musicCatalog[] = [
                'id' => 'template_default_audio',
                'title' => "Giai Điệu Gốc ({$template->name})",
                'artist' => PlatformSetting::getBrandName().' Studio',
                'genre' => 'Nhạc Nền Độc Quyền Theo Mẫu',
                'duration' => '03:20',
                'url' => $defaultTemplateAudio,
                'tag' => 'Theo Mẫu',
                'description' => 'Bản phối âm mộc mạc đặc trưng được thiết kế riêng cho mẫu thiệp cưới này.',
            ];
        }

        // Danh sách Typography Font tuyển chọn có dấu đẹp cho thiệp cưới
        $availableHeadingFonts = [
            'Playfair Display',
            'Cormorant Garamond',
            'Cinzel',
            'Great Vibes',
            'Dancing Script',
            'Lora',
            'Merriweather',
            'Alex Brush',
            'Montserrat',
            'Be Vietnam Pro',
            'Inter',
            'Quicksand',
        ];

        $availableBodyFonts = [
            'Montserrat',
            'Inter',
            'Be Vietnam Pro',
            'Open Sans',
            'Roboto',
            'Quicksand',
            'Nunito Sans',
            'Lora',
        ];

        $availableScriptFonts = [
            'Dancing Script',
            'Great Vibes',
            'Alex Brush',
            'Parisienne',
            'Sacramento',
            'Playfair Display',
        ];

        // Phát hiện ảnh chụp toàn trang có sẵn trong assets của mẫu thiệp
        $detectedLocalFullPreview = null;
        $potentialLocalPaths = [
            "views/client/invitations/{$template->slug}/assets/images/{$template->slug}.webp" => "/mau-thiep/{$template->slug}/assets/images/{$template->slug}.webp",
            "views/client/invitations/{$template->slug}/assets/{$template->slug}.webp" => "/mau-thiep/{$template->slug}/assets/{$template->slug}.webp",
            "views/client/invitations/{$template->slug}/assets/images/preview.webp" => "/mau-thiep/{$template->slug}/assets/images/preview.webp",
            "views/client/invitations/{$template->slug}/assets/preview.webp" => "/mau-thiep/{$template->slug}/assets/preview.webp",
            "views/client/invitations/{$template->slug}/assets/images/full.webp" => "/mau-thiep/{$template->slug}/assets/images/full.webp",
            "views/client/invitations/{$template->slug}/assets/full.webp" => "/mau-thiep/{$template->slug}/assets/full.webp",
        ];

        foreach ($potentialLocalPaths as $relPath => $webUrl) {
            if (file_exists(resource_path($relPath))) {
                $detectedLocalFullPreview = $webUrl;
                break;
            }
        }

        return view('admin.pages.templates.edit', [
            'template' => $template,
            'audit' => $audit,
            'visualDna' => $visualDna,
            'schemaExists' => $audit['files']['schema_json']['exists'] ?? false,
            'rawSchema' => $audit['raw_schema'] ?? [],
            'sectionsReport' => $audit['sections_report'] ?? [],
            'isCanonicalValid' => $audit['is_canonical_valid'] ?? false,
            'galleryValid' => $audit['gallery_valid'] ?? false,
            'musicCatalog' => $musicCatalog,
            'availableHeadingFonts' => $availableHeadingFonts,
            'availableBodyFonts' => $availableBodyFonts,
            'availableScriptFonts' => $availableScriptFonts,
            'detectedLocalFullPreview' => $detectedLocalFullPreview,
            'defaultConfig' => $template->default_config ?? [],
        ]);
    }

    /**
     * Update template metadata, showcase presentation data and configurations.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $template = InvitationTemplate::findOrFail($id);

        $validated = $request->validate([
            // 1. Basic & Tier
            'name' => 'required|string|max:150',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|string|max:50',
            'tier' => 'required|string|in:free,plus,pro',
            'thumbnail_url' => 'nullable|string|max:500',
            'thumbnail_file' => 'nullable|file|mimes:jpeg,png,jpg,webp,svg|max:10240',
            'full_page_preview_url' => 'nullable|string|max:1000',
            'full_page_preview_file' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:25600',
            'is_active' => 'nullable',
            'is_featured' => 'nullable',

            // 2. Music & Fonts
            'music_url' => 'nullable|string|max:1000',
            'music_file' => 'nullable|file|mimes:mp3,wav,m4a,ogg|max:20480',
            'heading_font' => 'nullable|string|max:100',
            'body_font' => 'nullable|string|max:100',
            'script_font' => 'nullable|string|max:100',

            // 3. Couple & Ceremony
            'ceremony_type' => 'nullable|string|max:100',
            'groom_name' => 'nullable|string|max:100',
            'bride_name' => 'nullable|string|max:100',
            'wedding_date' => 'nullable|string|max:50',
            'party_time' => 'nullable|string|max:50',
            'wedding_lunar_date' => 'nullable|string|max:200',
            'groom_father' => 'nullable|string|max:100',
            'groom_mother' => 'nullable|string|max:100',
            'groom_parents_address' => 'nullable|string|max:500',
            'bride_father' => 'nullable|string|max:100',
            'bride_mother' => 'nullable|string|max:100',
            'bride_parents_address' => 'nullable|string|max:500',
            'groom_bio' => 'nullable|string|max:2000',
            'bride_bio' => 'nullable|string|max:2000',
            'party_venue' => 'nullable|string|max:255',
            'event_party_address' => 'nullable|string|max:1000',
            'party_map_url' => 'nullable|string|max:1000',

            // Lễ Ăn Hỏi, Lễ Thành Hôn & Tiệc Nhà Gái
            'engagement_time' => 'nullable|string|max:50',
            'engagement_venue' => 'nullable|string|max:255',
            'engagement_day' => 'nullable|string|max:20',
            'engagement_weekday' => 'nullable|string|max:50',
            'engagement_month_year' => 'nullable|string|max:50',
            'engagement_lunar_date' => 'nullable|string|max:200',
            'ceremony_time' => 'nullable|string|max:50',
            'ceremony_venue' => 'nullable|string|max:255',
            'bride_party_time' => 'nullable|string|max:50',
            'bride_party_venue' => 'nullable|string|max:255',
            'bride_party_address' => 'nullable|string|max:1000',
            'bride_party_map_url' => 'nullable|string|max:1000',

            // 4. Photos
            'cover_photo_url' => 'nullable|string|max:1000',
            'cover_photo_file' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:15360',
            'groom_photo_url' => 'nullable|string|max:1000',
            'groom_photo_file' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:15360',
            'bride_photo_url' => 'nullable|string|max:1000',
            'bride_photo_file' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:15360',
            'save_date_photo_url' => 'nullable|string|max:1000',
            'save_date_photo_file' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:15360',
            'triptych_left_url' => 'nullable|string|max:1000',
            'triptych_left_file' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:15360',
            'triptych_center_url' => 'nullable|string|max:1000',
            'triptych_center_file' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:15360',
            'triptych_right_url' => 'nullable|string|max:1000',
            'triptych_right_file' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:15360',
            'footer_photo_url' => 'nullable|string|max:1000',
            'footer_photo_file' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:15360',
            'gallery_urls' => 'nullable|array',
            'gallery_urls.*' => 'nullable|string|max:1000',
            'gallery_files' => 'nullable|array',
            'gallery_files.*' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:15360',

            // 5. Story, Timeline & Gift
            'slogan' => 'nullable|string|max:500',
            'story_title' => 'nullable|string|max:200',
            'story_subtitle' => 'nullable|string|max:200',
            'couple_quote' => 'nullable|string|max:1000',
            'story_content' => 'nullable|string|max:2000',
            'ending_title' => 'nullable|string|max:200',
            'ending_text' => 'nullable|string|max:1000',
            'story_stage_1_title' => 'nullable|string|max:200',
            'story_stage_1_desc' => 'nullable|string|max:2000',
            'story_stage_2_title' => 'nullable|string|max:200',
            'story_stage_2_desc' => 'nullable|string|max:2000',
            'story_stage_3_title' => 'nullable|string|max:200',
            'story_stage_3_desc' => 'nullable|string|max:2000',
            'footer_thank_you_text' => 'nullable|string|max:1000',
            'timeline' => 'nullable|array',
            'timeline.*.time' => 'nullable|string|max:50',
            'timeline.*.title' => 'nullable|string|max:255',
            'dresscode_colors' => 'nullable|string|max:255',
            'dresscode_note' => 'nullable|string|max:1000',
            'groom_bank_name' => 'nullable|string|max:100',
            'groom_bank_account' => 'nullable|string|max:100',
            'groom_bank_owner' => 'nullable|string|max:150',
            'groom_qr_url' => 'nullable|string|max:1000',
            'groom_qr_file' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:10240',
            'bride_bank_name' => 'nullable|string|max:100',
            'bride_bank_account' => 'nullable|string|max:100',
            'bride_bank_owner' => 'nullable|string|max:150',
            'bride_qr_url' => 'nullable|string|max:1000',
            'bride_qr_file' => 'nullable|file|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $defaultConfig = $template->default_config ?? [];
        $slug = $template->slug;

        // --- 1. Xử lý Thumbnail (kèm dọn dẹp storage cũ nếu upload ảnh mới) ---
        $thumbnailUrl = $template->thumbnail_url;
        if ($request->hasFile('thumbnail_file')) {
            $uploaded = $this->handleFileUpload($request, 'thumbnail_file', $slug, 'thumbnail', $thumbnailUrl);
            if ($uploaded) {
                $thumbnailUrl = $uploaded;
            }
        } elseif ($request->filled('thumbnail_url')) {
            $thumbnailUrl = $request->input('thumbnail_url');
        }

        // --- 1.2. Xử lý Ảnh Chụp Toàn Mẫu Cuộn (Full Page Scroll Preview) ---
        if ($request->hasFile('full_page_preview_file')) {
            $oldFullPreview = $defaultConfig['full_page_preview'] ?? null;
            $uploadedFullPreview = $this->handleFileUpload($request, 'full_page_preview_file', $slug, 'preview', $oldFullPreview);
            if ($uploadedFullPreview) {
                $defaultConfig['full_page_preview'] = $uploadedFullPreview;
            }
        } elseif ($request->has('full_page_preview_url')) {
            $fullPageUrl = trim((string) $request->input('full_page_preview_url'));
            if ($fullPageUrl !== '') {
                $defaultConfig['full_page_preview'] = $fullPageUrl;
            } else {
                unset($defaultConfig['full_page_preview']);
            }
        }

        // --- 2. Xử lý Nhạc nền Showcase (kèm dọn dẹp storage cũ nếu upload file mới) ---
        if ($request->hasFile('music_file')) {
            $uploadedMusic = $this->handleFileUpload($request, 'music_file', $slug, 'music', $defaultConfig['music_url'] ?? null);
            if ($uploadedMusic) {
                $defaultConfig['music_url'] = $uploadedMusic;
            }
        } elseif ($request->has('music_url')) {
            $defaultConfig['music_url'] = $request->input('music_url');
        }

        // --- 3. Xử lý Phông chữ Showcase ---
        foreach (['heading_font', 'body_font', 'script_font'] as $fontField) {
            if ($request->filled($fontField)) {
                $defaultConfig[$fontField] = $request->input($fontField);
            }
        }

        // --- 4. Xử lý Thông tin Cặp đôi & Tiệc cưới Showcase ---
        foreach ([
            'ceremony_type', 'groom_name', 'bride_name', 'wedding_date', 'party_time',
            'wedding_lunar_date', 'groom_father', 'groom_mother', 'groom_parents_address',
            'bride_father', 'bride_mother', 'bride_parents_address', 'groom_bio', 'bride_bio',
            'party_venue', 'event_party_address', 'party_map_url',
            'engagement_time', 'engagement_venue', 'engagement_day', 'engagement_weekday',
            'engagement_month_year', 'engagement_lunar_date', 'ceremony_time', 'ceremony_venue',
            'bride_party_time', 'bride_party_venue', 'bride_party_address', 'bride_party_map_url',
        ] as $infoField) {
            if ($request->has($infoField)) {
                $defaultConfig[$infoField] = $request->input($infoField);
            }
        }

        // --- 5. Xử lý Ảnh trưng bày chính (Hero, Chú rể, Cô dâu, Save Date, Triptych, Footer) ---
        $photoUploadFields = [
            'cover_photo_file' => 'cover_photo_url',
            'hero_cover_file' => 'cover_photo_url',
            'groom_photo_file' => 'groom_photo_url',
            'bride_photo_file' => 'bride_photo_url',
            'save_date_photo_file' => 'save_date_photo_url',
            'couple_photo_file' => 'couple_photo_url',
            'triptych_left_file' => 'triptych_left_url',
            'triptych_center_file' => 'triptych_center_url',
            'triptych_right_file' => 'triptych_right_url',
            'footer_photo_file' => 'footer_photo_url',
        ];
        foreach ($photoUploadFields as $fileInput => $urlField) {
            if ($request->hasFile($fileInput)) {
                $oldFile = $defaultConfig[$urlField] ?? ($defaultConfig['hero_cover_url'] ?? null);
                $uploadedPhoto = $this->handleFileUpload($request, $fileInput, $slug, 'photos', $oldFile);
                if ($uploadedPhoto) {
                    $defaultConfig[$urlField] = $uploadedPhoto;
                    if ($urlField === 'cover_photo_url') {
                        $defaultConfig['hero_photo_url'] = $uploadedPhoto;
                        $defaultConfig['hero_cover_url'] = $uploadedPhoto;
                    }
                }
            } elseif ($request->filled($urlField)) {
                $defaultConfig[$urlField] = $request->input($urlField);
                if ($urlField === 'cover_photo_url') {
                    $defaultConfig['hero_photo_url'] = $request->input($urlField);
                    $defaultConfig['hero_cover_url'] = $request->input($urlField);
                }
            }
        }

        // --- 6. Xử lý Album 10 Ảnh cưới trưng bày (Gallery) ---
        $existingGallery = $defaultConfig['gallery'] ?? [];
        if (! is_array($existingGallery)) {
            $existingGallery = [];
        }
        $galleryUrls = $request->input('gallery_urls', $request->input('gallery_url', []));
        for ($i = 0; $i < 10; $i++) {
            $galleryFileInput = "gallery_files.{$i}";
            if ($request->hasFile($galleryFileInput)) {
                $oldGalleryPhoto = $existingGallery[$i] ?? null;
                $uploadedGalleryPhoto = $this->handleFileUpload($request, $galleryFileInput, $slug, 'gallery', $oldGalleryPhoto);
                if ($uploadedGalleryPhoto) {
                    $existingGallery[$i] = $uploadedGalleryPhoto;
                }
            } elseif (isset($galleryUrls[$i]) && $galleryUrls[$i] !== '') {
                $existingGallery[$i] = $galleryUrls[$i];
            }
        }
        $defaultConfig['gallery'] = $existingGallery;

        // --- 7. Xử lý Chuyện tình yêu, Lời ngỏ, Slogan, Lịch trình & Dresscode ---
        foreach ([
            'slogan', 'story_title', 'story_subtitle', 'couple_quote', 'story_content',
            'ending_title', 'ending_text',
            'story_stage_1_title', 'story_stage_1_desc',
            'story_stage_2_title', 'story_stage_2_desc',
            'story_stage_3_title', 'story_stage_3_desc',
            'footer_thank_you_text', 'dresscode_colors', 'dresscode_note',
        ] as $storyField) {
            if ($request->has($storyField)) {
                $defaultConfig[$storyField] = $request->input($storyField);
            }
        }

        if ($request->filled('ending_text') && empty($defaultConfig['footer_thank_you_text'])) {
            $defaultConfig['footer_thank_you_text'] = $request->input('ending_text');
        } elseif ($request->filled('footer_thank_you_text') && empty($defaultConfig['ending_text'])) {
            $defaultConfig['ending_text'] = $request->input('footer_thank_you_text');
        }

        if ($request->has('timeline')) {
            $defaultConfig['timeline'] = array_values(array_filter($request->input('timeline', []), function ($item) {
                return ! empty($item['title']) || ! empty($item['time']);
            }));
        }

        // --- 8. Xử lý Mừng cưới & Mã QR ---
        foreach ([
            'groom_bank_name', 'groom_bank_account', 'groom_bank_owner',
            'bride_bank_name', 'bride_bank_account', 'bride_bank_owner',
        ] as $bankField) {
            if ($request->has($bankField)) {
                $defaultConfig[$bankField] = $request->input($bankField);
            }
        }

        if ($request->hasFile('groom_qr_file')) {
            $uploadedGroomQr = $this->handleFileUpload($request, 'groom_qr_file', $slug, 'qr', $defaultConfig['groom_qr_url'] ?? null);
            if ($uploadedGroomQr) {
                $defaultConfig['groom_qr_url'] = $uploadedGroomQr;
            }
        } elseif ($request->filled('groom_qr_url')) {
            $defaultConfig['groom_qr_url'] = $request->input('groom_qr_url');
        }

        if ($request->hasFile('bride_qr_file')) {
            $uploadedBrideQr = $this->handleFileUpload($request, 'bride_qr_file', $slug, 'qr', $defaultConfig['bride_qr_url'] ?? null);
            if ($uploadedBrideQr) {
                $defaultConfig['bride_qr_url'] = $uploadedBrideQr;
            }
        } elseif ($request->filled('bride_qr_url')) {
            $defaultConfig['bride_qr_url'] = $request->input('bride_qr_url');
        }

        // Cấu hình phân hạng và mô tả
        $defaultConfig['tier'] = $validated['tier'];
        if (isset($validated['description'])) {
            $defaultConfig['description'] = $validated['description'];
        }

        $template->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'thumbnail_url' => $thumbnailUrl,
            'is_active' => $request->has('is_active'),
            'is_featured' => $request->has('is_featured'),
            'default_config' => $defaultConfig,
        ]);

        InvitationTemplate::clearTemplateCaches($template);

        AuditLogger::log(
            'update_template',
            'InvitationTemplate',
            $template->id,
            "Cập nhật toàn diện cấu hình & dữ liệu trưng bày mẫu thiệp '{$template->name}' (Tier: {$validated['tier']})",
            ['tier' => $validated['tier'], 'is_active' => $template->is_active, 'is_featured' => $template->is_featured]
        );

        if ($request->has('save_and_continue')) {
            return redirect()->route('admin.templates.edit', $template->id)->with('success', "Đã lưu cập nhật thông tin và dữ liệu trưng bày mẫu thiệp \"{$template->name}\" thành công.");
        }

        return redirect()->route('admin.templates.index')->with('success', "Đã lưu cập nhật thông tin và dữ liệu trưng bày mẫu thiệp \"{$template->name}\" thành công.");
    }

    /**
     * Tải tệp lên đĩa public storage và tự động xóa tệp cũ trong storage để tránh rác dung lượng.
     */
    protected function handleFileUpload(Request $request, string $inputName, string $slug, string $folder, ?string $oldUrl = null): ?string
    {
        if (! $request->hasFile($inputName)) {
            return null;
        }

        $file = $request->file($inputName);
        if (! $file || ! $file->isValid()) {
            return null;
        }

        // 1. Tự động xóa tệp cũ trong Storage nếu tệp cũ bắt đầu bằng /storage/
        $this->deleteStorageFileIfApplicable($oldUrl);

        // 2. Lưu tệp mới an toàn
        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension());
        $cleanFolder = Str::slug($folder);
        $filename = Str::slug($slug).'-'.$cleanFolder.'-'.Str::random(10).'.'.$ext;
        $path = $file->storeAs("templates/{$slug}/{$cleanFolder}", $filename, 'public');

        return '/storage/'.$path;
    }

    /**
     * Xóa tệp khỏi Storage nếu tệp cũ nằm trong thư mục storage public đã upload trước đó.
     */
    protected function deleteStorageFileIfApplicable(?string $url): void
    {
        if (empty($url)) {
            return;
        }

        $path = parse_url($url, PHP_URL_PATH);
        if ($path && str_starts_with($path, '/storage/')) {
            $relativeStoragePath = substr($path, strlen('/storage/'));
            if (Storage::disk('public')->exists($relativeStoragePath)) {
                Storage::disk('public')->delete($relativeStoragePath);
            }
        }
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
