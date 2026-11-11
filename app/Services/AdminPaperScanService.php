<?php

namespace App\Services;

use App\Models\PaperInvitationScan;
use App\Models\Wedding;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class AdminPaperScanService
{
    /**
     * Lấy dữ liệu tổng quan và danh sách các lượt quét thiệp giấy AI OCR.
     */
    public function getScansOverview(array $filters = []): array
    {
        // 1. Thống kê 4 KPI
        $totalScans = PaperInvitationScan::count();
        $appliedScans = PaperInvitationScan::where('status', 'applied')->count();
        $highConfidenceScans = PaperInvitationScan::where('confidence_score', '>=', 0.8)->count();
        $avgConfidence = $totalScans > 0 ? round((float) PaperInvitationScan::avg('confidence_score') * 100, 1) : 0;

        $stats = [
            'total_scans' => $totalScans,
            'applied_scans' => $appliedScans,
            'high_confidence_scans' => $highConfidenceScans,
            'avg_confidence' => $avgConfidence,
        ];

        // 2. Query danh sách
        $query = PaperInvitationScan::with(['user:id,name,email,phone', 'wedding:id,title,groom_name,bride_name,wedding_date', 'invitation:id,slug,title'])
            ->latest();

        // Lọc từ khóa
        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function ($q) use ($keyword) {
                $q->where('raw_extracted_text', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('wedding', function ($wq) use ($keyword) {
                        $wq->where('title', 'like', "%{$keyword}%")
                            ->orWhere('groom_name', 'like', "%{$keyword}%")
                            ->orWhere('bride_name', 'like', "%{$keyword}%");
                    });
            });
        }

        // Lọc theo trạng thái
        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        // Lọc theo độ tin cậy
        if (! empty($filters['confidence'])) {
            if ($filters['confidence'] === 'high') {
                $query->where('confidence_score', '>=', 0.8);
            } elseif ($filters['confidence'] === 'medium') {
                $query->whereBetween('confidence_score', [0.5, 0.79]);
            } elseif ($filters['confidence'] === 'low') {
                $query->where('confidence_score', '<', 0.5);
            }
        }

        $scans = $query->paginate(15)->withQueryString();

        return [
            'stats' => $stats,
            'scans' => $scans,
        ];
    }

    /**
     * Cập nhật dữ liệu đã hiệu đính (verified_data) cho lượt quét.
     */
    public function updateVerifiedData(PaperInvitationScan $scan, array $verifiedData, string $operator = 'Admin'): PaperInvitationScan
    {
        $scan->verified_data = $verifiedData;
        $scan->save();

        return $scan;
    }

    /**
     * Áp dụng dữ liệu trích xuất vào tiệc cưới thật (Wedding).
     */
    public function applyScanToWedding(PaperInvitationScan $scan, ?array $overrideData = null, string $operator = 'Admin'): Wedding
    {
        $data = $overrideData ?: ($scan->verified_data ?: $scan->parsed_data);

        $wedding = $scan->wedding;
        if (! $wedding && $scan->user) {
            $wedding = Wedding::create([
                'user_id' => $scan->user_id,
                'title' => 'Lễ Thành Hôn '.($data['groom_name'] ?? 'Chú Rể').' & '.($data['bride_name'] ?? 'Cô Dâu'),
                'groom_name' => $data['groom_name'] ?? 'Chú Rể',
                'bride_name' => $data['bride_name'] ?? 'Cô Dâu',
                'wedding_date' => ! empty($data['wedding_date']) ? $data['wedding_date'] : now()->addMonths(2),
                'venue_name' => $data['venue_name'] ?? null,
                'venue_address' => $data['venue_address'] ?? null,
                'status' => 'active',
            ]);
            $scan->wedding_id = $wedding->id;
        } elseif ($wedding) {
            if (! empty($data['groom_name'])) {
                $wedding->groom_name = $data['groom_name'];
            }
            if (! empty($data['bride_name'])) {
                $wedding->bride_name = $data['bride_name'];
            }
            if (! empty($data['wedding_date'])) {
                $wedding->wedding_date = $data['wedding_date'];
            }
            if (! empty($data['venue_name'])) {
                $wedding->venue_name = $data['venue_name'];
            }
            if (! empty($data['venue_address'])) {
                $wedding->venue_address = $data['venue_address'];
            }
            $wedding->save();
        }

        $scan->status = 'applied';
        $scan->applied_at = now();
        $scan->save();

        return $wedding;
    }

    /**
     * Xóa một lượt quét thiệp giấy (kèm tệp ảnh nếu có).
     */
    public function deleteScan(PaperInvitationScan $scan, string $operator = 'Admin'): bool
    {
        if ($scan->image_path && Storage::disk('public')->exists($scan->image_path)) {
            Storage::disk('public')->delete($scan->image_path);
        }

        return (bool) $scan->delete();
    }

    /**
     * Xuất tệp báo cáo danh sách lượt quét thiệp giấy CSV UTF-8 BOM.
     */
    public function exportScansCsv(array $filters = []): Response
    {
        $query = PaperInvitationScan::with(['user', 'wedding'])->latest();

        if (! empty($filters['q'])) {
            $keyword = trim($filters['q']);
            $query->where(function ($q) use ($keyword) {
                $q->where('raw_extracted_text', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($uq) use ($keyword) {
                        $uq->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        $scans = $query->get();

        $headers = [
            'ID Lượt Quét',
            'Chủ Tài Khoản',
            'Email',
            'Tiệc Cưới Liên Kết',
            'Chú Rể Trích Xuất',
            'Cô Dâu Trích Xuất',
            'Ngày Cưới Trích Xuất',
            'Địa Điểm Trích Xuất',
            'Độ Tin Cậy (%)',
            'Trạng Thái',
            'Mô Hình AI',
            'Thời Gian Quét',
            'Thời Gian Áp Dụng',
        ];

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $headers);

        foreach ($scans as $item) {
            $p = $item->parsed_data ?? [];
            fputcsv($handle, [
                $item->id,
                $item->user?->name ?: '—',
                $item->user?->email ?: '—',
                $item->wedding?->title ?: '—',
                $p['groom_name'] ?? '—',
                $p['bride_name'] ?? '—',
                $p['wedding_date'] ?? '—',
                $p['venue_name'] ?? '—',
                round($item->confidence_score * 100, 1).'%',
                $item->status,
                $item->ai_model ?: 'heuristic_vision_parser',
                $item->created_at ? $item->created_at->format('d/m/Y H:i:s') : '—',
                $item->applied_at ? $item->applied_at->format('d/m/Y H:i:s') : 'Chưa áp dụng',
            ]);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        $fileName = 'mayhen_paper_invitation_scans_'.date('Ymd_His').'.csv';

        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
