<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ImportBatch;
use App\Models\Wedding;
use App\Services\GuestBatchImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminGuestImportController extends Controller
{
    public function __construct(
        protected GuestBatchImportService $importService
    ) {}

    /**
     * Màn hình chính Wizard 4 bước nhập khách hàng loạt
     */
    public function index(Request $request): View
    {
        $weddingId = $request->query('wedding_id');
        $weddings = Wedding::with('user')->latest()->take(50)->get();
        $selectedWedding = $weddingId ? Wedding::find($weddingId) : $weddings->first();

        // Lấy danh sách các đợt import gần nhất
        $recentBatches = ImportBatch::with('wedding')
            ->when($selectedWedding, fn ($q) => $q->where('wedding_id', $selectedWedding->id))
            ->latest()
            ->take(10)
            ->get();

        return view('admin.pages.guests.import', [
            'weddings' => $weddings,
            'selectedWedding' => $selectedWedding,
            'recentBatches' => $recentBatches,
            'currentStep' => 1,
            'batch' => null,
        ]);
    }

    /**
     * Bước 1: Tiếp nhận tệp tải lên và phân tích tiêu đề
     */
    public function upload(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wedding_id' => 'required|exists:weddings,id',
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ], [
            'wedding_id.required' => 'Vui lòng chọn đám cưới cần nhập danh sách khách.',
            'file.required' => 'Vui lòng chọn tệp CSV hoặc TXT danh sách khách.',
            'file.mimes' => 'Định dạng tệp không được hỗ trợ. Vui lòng sử dụng tệp CSV.',
            'file.max' => 'Dung lượng tệp vượt quá giới hạn cho phép (tối đa 5MB).',
        ]);

        try {
            $batch = $this->importService->parseUploadedFile(
                $request->file('file'),
                (int) $validated['wedding_id'],
                Auth::id()
            );

            return redirect()->route('admin.guests.import.mapping', $batch->batch_id)
                ->with('success', "Đã tải lên tệp [{$batch->file_name}] thành công với {$batch->total_rows} dòng dữ liệu. Vui lòng kiểm tra ánh xạ cột.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Lỗi khi đọc tệp dữ liệu: '.$e->getMessage());
        }
    }

    /**
     * Bước 2: Hiển thị bảng ánh xạ cột nguồn sang trường đích
     */
    public function mapping(string $batchId): View
    {
        $batch = ImportBatch::with('wedding')->where('batch_id', $batchId)->firstOrFail();
        $rawRows = $batch->raw_preview_data ?? [];
        $sampleHeaders = ! empty($rawRows) ? array_keys($rawRows[0]) : [];

        return view('admin.pages.guests.import', [
            'weddings' => collect([$batch->wedding]),
            'selectedWedding' => $batch->wedding,
            'recentBatches' => collect(),
            'currentStep' => 2,
            'batch' => $batch,
            'sampleHeaders' => $sampleHeaders,
            'sampleRows' => array_slice($rawRows, 0, 5),
        ]);
    }

    /**
     * Bước 3: Xem trước phân tích lỗi & xác nhận (Dry Run)
     */
    public function preview(Request $request, string $batchId): View|RedirectResponse
    {
        $batch = ImportBatch::with('wedding')->where('batch_id', $batchId)->firstOrFail();

        $validated = $request->validate([
            'mapping' => 'required|array',
            'duplicate_mode' => 'required|in:skip,update,create_always',
        ]);

        $dryRun = $this->importService->previewAndValidate(
            $batch,
            $validated['mapping'],
            $validated['duplicate_mode']
        );

        return view('admin.pages.guests.import', [
            'weddings' => collect([$batch->wedding]),
            'selectedWedding' => $batch->wedding,
            'recentBatches' => collect(),
            'currentStep' => 3,
            'batch' => $batch,
            'dryRun' => $dryRun,
        ]);
    }

    /**
     * Bước 4: Thực thi nhập dữ liệu vào cơ sở dữ liệu
     */
    public function execute(Request $request, string $batchId): View|RedirectResponse
    {
        $batch = ImportBatch::with('wedding')->where('batch_id', $batchId)->firstOrFail();

        try {
            $result = $this->importService->executeImport($batch);

            return view('admin.pages.guests.import', [
                'weddings' => collect([$batch->wedding]),
                'selectedWedding' => $batch->wedding,
                'recentBatches' => collect(),
                'currentStep' => 4,
                'batch' => $batch->fresh(),
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            return redirect()->route('admin.guests.import.mapping', $batch->batch_id)
                ->with('error', 'Lỗi khi thực thi nhập dữ liệu: '.$e->getMessage());
        }
    }

    /**
     * Xuất tệp CSV chứa danh sách các dòng bị lỗi để người dùng sửa lại
     */
    public function exportFailed(string $batchId): Response
    {
        $batch = ImportBatch::where('batch_id', $batchId)->firstOrFail();
        $csvContent = $this->importService->exportFailedRowsCsv($batch);

        $fileName = 'danh_sach_loi_import_'.$batch->batch_id.'.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', "attachment; filename=\"{$fileName}\"");
    }
}
