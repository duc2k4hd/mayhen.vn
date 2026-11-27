@extends('admin.layouts.master')

@section('title', 'Chi Tiết Quét Thiệp Giấy #' . $scan->id)

@section('content')
<div class="mayhen_admin_paper_scans" style="padding: 24px; max-width: 1200px; margin: 0 auto; font-family: var(--mh-admin-font, 'Open Sans', sans-serif);">
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap;">
        <a href="{{ route('admin.paper_scans.index') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: #64748b; text-decoration: none;">
            ← Quay lại Đài Giám Sát Quét Thiệp Giấy
        </a>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <form method="POST" action="{{ route('admin.paper_scans.apply', $scan->id) }}" onsubmit="return confirm('Áp dụng dữ liệu quét này vào hồ sơ tiệc cưới?');">
                @csrf
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary">
                    Áp Dụng Vào Tiệc Cưới
                </button>
            </form>
            <form method="POST" action="{{ route('admin.paper_scans.destroy', $scan->id) }}" onsubmit="return confirm('Xóa lượt quét thiệp giấy này?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--danger">
                    Xóa Lượt Quét
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; color: #065f46; font-size: 13px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="mayhen_admin_card" style="padding: 20px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap;">
            <div>
                <h1 style="font-size: 20px; font-weight: 700; margin: 0 0 6px; color: #1e293b;">
                    Lượt Quét Thiệp Giấy #{{ $scan->id }}
                </h1>
                <div style="font-size: 13px; color: #64748b;">
                    Khách hàng: <strong>{{ $scan->user?->name ?? 'Khách ẩn danh' }}</strong>
                    @if($scan->user?->email)
                        ({{ $scan->user->email }})
                    @endif
                    &bull; Tạo lúc: <strong>{{ $scan->created_at?->format('d/m/Y H:i:s') ?? '—' }}</strong>
                </div>
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <span class="mayhen_admin_badge mayhen_admin_badge--info">
                    Độ tin cậy: {{ round(((float) ($scan->confidence_score ?? 0)) * 100) }}%
                </span>
                <span class="mayhen_admin_badge mayhen_admin_badge--neutral">
                    Trạng thái: {{ $scan->status }}
                </span>
                @if($scan->wedding)
                    <span class="mayhen_admin_badge mayhen_admin_badge--success">
                        {{ $scan->wedding->groom_name }} &amp; {{ $scan->wedding->bride_name }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
        <div class="mayhen_admin_card" style="padding: 20px;">
            <h2 style="font-size: 16px; font-weight: 700; margin: 0 0 12px; color: #1e293b;">Ảnh &amp; Văn Bản OCR Trích Xuất</h2>
            @if(! empty($scan->image_url))
                <div style="margin-bottom: 14px; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #f8fafc;">
                    <img src="{{ $scan->image_url }}" alt="Ảnh quét thiệp #{{ $scan->id }}" style="display: block; max-width: 100%; max-height: 360px; margin: 0 auto; object-fit: contain;">
                </div>
            @endif
            <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 6px;">Nội dung OCR thô:</div>
            <pre style="white-space: pre-wrap; margin: 0; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 12.5px; max-height: 280px; overflow: auto;">{{ $scan->raw_extracted_text ?: 'Chưa có văn bản trích xuất.' }}</pre>
        </div>

        <div class="mayhen_admin_card" style="padding: 20px;">
            <h2 style="font-size: 16px; font-weight: 700; margin: 0 0 12px; color: #1e293b;">Hiệu Đính Dữ Liệu Đám Cưới</h2>
            @php
                $verified = is_array($scan->verified_data) && $scan->verified_data !== [] ? $scan->verified_data : ($scan->parsed_data ?? []);
            @endphp
            <form method="POST" action="{{ route('admin.paper_scans.update_verified', $scan->id) }}">
                @csrf
                @method('PUT')
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; margin-bottom: 4px;">Tên Chú Rể</label>
                    <input type="text" name="verified_data[groom_name]" value="{{ old('verified_data.groom_name', $verified['groom_name'] ?? '') }}" class="mayhen_admin_form_input" style="width: 100%;">
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; margin-bottom: 4px;">Tên Cô Dâu</label>
                    <input type="text" name="verified_data[bride_name]" value="{{ old('verified_data.bride_name', $verified['bride_name'] ?? '') }}" class="mayhen_admin_form_input" style="width: 100%;">
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; margin-bottom: 4px;">Ngày Cưới</label>
                    <input type="date" name="verified_data[wedding_date]" value="{{ old('verified_data.wedding_date', $verified['wedding_date'] ?? '') }}" class="mayhen_admin_form_input" style="width: 100%;">
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; margin-bottom: 4px;">Tên Sảnh / Nhà Hàng</label>
                    <input type="text" name="verified_data[venue_name]" value="{{ old('verified_data.venue_name', $verified['venue_name'] ?? '') }}" class="mayhen_admin_form_input" style="width: 100%;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12.5px; font-weight: 600; margin-bottom: 4px;">Địa Chỉ Tổ Chức</label>
                    <input type="text" name="verified_data[venue_address]" value="{{ old('verified_data.venue_address', $verified['venue_address'] ?? '') }}" class="mayhen_admin_form_input" style="width: 100%;">
                </div>
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary">Lưu Dữ Liệu Hiệu Đính</button>
            </form>
        </div>
    </div>
</div>
@endsection
