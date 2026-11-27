@extends('admin.layouts.master')

@section('title', 'Chi Tiết Tác Vụ Lỗi #' . ($job->uuid ?? ''))

@section('content')
<div class="mayhen_admin_page_header" style="margin-bottom: 24px;">
    <div class="mayhen_admin_page_header_left">
        <a href="{{ route('admin.jobs.index', ['tab' => 'failed']) }}" style="font-size: 13px; color: #7A7265; text-decoration: none; font-weight: 600; display: inline-block; margin-bottom: 6px;">
            ← Quay lại Trung Tâm Chỉ Huy Hàng Đợi
        </a>
        <h1 class="mayhen_admin_page_title">Chi Tiết Tác Vụ Lỗi: {{ $job->display_name ?? 'Unknown Job' }}</h1>
        <p class="mayhen_admin_page_subtitle">
            UUID: <code>{{ $job->uuid }}</code> &bull; Queue: <strong>{{ $job->queue ?? 'default' }}</strong> &bull; Thời gian lỗi: <strong>{{ $job->failed_at }}</strong>
        </p>
    </div>
    <div class="mayhen_admin_page_header_right" style="display: flex; gap: 8px;">
        <form method="POST" action="{{ route('admin.jobs.retry', $job->uuid) }}">
            @csrf
            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary">Thử Lại Tác Vụ</button>
        </form>
        <form method="POST" action="{{ route('admin.jobs.destroy', $job->uuid) }}" onsubmit="return confirm('Xóa vĩnh viễn tác vụ lỗi này?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--danger">Xóa Bỏ</button>
        </form>
    </div>
</div>

<div class="mayhen_admin_card" style="padding: 20px; margin-bottom: 20px;">
    <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 14px;">
        <span class="mayhen_admin_badge {{ $job->category['badge_class'] ?? 'mayhen_admin_badge--neutral' }}">
            {{ $job->category['name'] ?? 'Hệ thống' }}
        </span>
        <span class="mayhen_admin_badge mayhen_admin_badge--neutral">
            Connection: {{ $job->connection ?? 'database' }}
        </span>
        @if(! empty($job->is_high_risk))
            <span class="mayhen_admin_badge mayhen_admin_badge--danger">Tác Vụ Nhạy Cảm (Cân nhắc trước khi Retry)</span>
        @endif
    </div>

    <h2 style="font-size: 15px; font-weight: 700; margin: 0 0 10px;">Sanitized Stack Trace (Đã Ẩn Dữ Liệu Nhạy Cảm)</h2>
    <pre style="white-space: pre-wrap; margin: 0; padding: 14px; background: #1e293b; color: #f8fafc; border-radius: 8px; font-size: 12px; max-height: 420px; overflow: auto;">{{ $job->sanitized_exception ?? $job->exception ?? 'Không có stack trace.' }}</pre>
</div>

<div class="mayhen_admin_card" style="padding: 20px;">
    <h2 style="font-size: 15px; font-weight: 700; margin: 0 0 10px;">Payload Hàng Đợi</h2>
    <pre style="white-space: pre-wrap; margin: 0; padding: 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 12px; max-height: 320px; overflow: auto;">{{ json_encode($job->raw_payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
</div>
@endsection
