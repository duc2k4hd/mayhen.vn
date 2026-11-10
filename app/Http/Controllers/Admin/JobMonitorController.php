<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JobMonitorController extends Controller
{
    /**
     * Danh sách failed jobs với KPI.
     */
    public function index(Request $request): View
    {
        $query = DB::table('failed_jobs')
            ->orderByDesc('failed_at');

        // Tìm kiếm theo payload
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('queue', 'like', "%{$term}%")
                    ->orWhere('payload', 'like', "%{$term}%")
                    ->orWhere('exception', 'like', "%{$term}%");
            });
        }

        // Lọc theo queue
        if ($request->filled('queue')) {
            $query->where('queue', $request->queue);
        }

        $jobs = $query->paginate(20)->withQueryString();

        // Decode payload để lấy display name
        $jobs->through(function ($job) {
            $payload = json_decode($job->payload, true);
            $job->display_name = $payload['displayName'] ?? $payload['job'] ?? 'Unknown Job';
            $job->attempts = $payload['attempts'] ?? 0;
            $job->queue_name = $job->queue;

            return $job;
        });

        $kpis = [
            'total_failed' => DB::table('failed_jobs')->count(),
            'queues' => DB::table('failed_jobs')->distinct()->pluck('queue'),
        ];

        // Jobs by queue breakdown
        $byQueue = DB::table('failed_jobs')
            ->selectRaw('queue, count(*) as cnt')
            ->groupBy('queue')
            ->get()
            ->pluck('cnt', 'queue');

        return view('admin.pages.system.jobs', compact('jobs', 'kpis', 'byQueue'));
    }

    /**
     * Retry một failed job theo UUID.
     */
    public function retry(Request $request, string $uuid): RedirectResponse
    {
        $job = DB::table('failed_jobs')->where('uuid', $uuid)->first();

        if (! $job) {
            return back()->with('error', 'Không tìm thấy job này.');
        }

        try {
            Artisan::call('queue:retry', ['id' => [$uuid]]);
            $output = Artisan::output();

            $displayName = json_decode($job->payload, true)['displayName'] ?? $uuid;

            AuditLogger::log(
                'job_retry',
                'FailedJob',
                $uuid,
                "Retry job: {$displayName}",
                ['queue' => $job->queue]
            );

            return back()->with('success', "Đã đẩy lại job vào queue. {$output}");
        } catch (\Throwable $e) {
            return back()->with('error', 'Retry thất bại: '.$e->getMessage());
        }
    }

    /**
     * Xóa một failed job.
     */
    public function destroy(string $uuid): RedirectResponse
    {
        $job = DB::table('failed_jobs')->where('uuid', $uuid)->first();

        if (! $job) {
            return back()->with('error', 'Không tìm thấy job này.');
        }

        DB::table('failed_jobs')->where('uuid', $uuid)->delete();

        $displayName = json_decode($job->payload, true)['displayName'] ?? $uuid;

        AuditLogger::log(
            'job_deleted',
            'FailedJob',
            $uuid,
            "Xóa failed job: {$displayName}"
        );

        return back()->with('success', 'Đã xóa job thành công.');
    }

    /**
     * Retry tất cả failed jobs.
     */
    public function retryAll(): RedirectResponse
    {
        $count = DB::table('failed_jobs')->count();

        if ($count === 0) {
            return back()->with('info', 'Không có failed job nào để retry.');
        }

        try {
            Artisan::call('queue:retry', ['id' => ['all']]);

            AuditLogger::log(
                'job_retry_all',
                'FailedJob',
                'all',
                "Retry tất cả {$count} failed jobs"
            );

            return back()->with('success', "Đã đẩy lại {$count} failed jobs vào queue.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Retry all thất bại: '.$e->getMessage());
        }
    }

    /**
     * Xóa tất cả failed jobs (cẩn thận — chỉ super admin).
     */
    public function purge(): RedirectResponse
    {
        $count = DB::table('failed_jobs')->count();
        DB::table('failed_jobs')->truncate();

        AuditLogger::log(
            'job_purge_all',
            'FailedJob',
            'all',
            "Xóa toàn bộ {$count} failed jobs"
        );

        return back()->with('success', "Đã xóa toàn bộ {$count} failed jobs.");
    }
}
