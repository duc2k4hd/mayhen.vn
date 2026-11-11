@extends('admin.layouts.master')

@section('title', 'Quản Trị Mẫu Email & Tin Nhắn Tự Động')

@section('content')
<div class="mayhen_admin_page_header">
    <div class="mayhen_admin_page_header_left">
        <h1 class="mayhen_admin_page_title">Mẫu Email & Tin Nhắn Tự Động</h1>
        <p class="mayhen_admin_page_subtitle">Quản trị các kịch bản thư điện tử và tin nhắn thông báo gửi tới Cặp đôi & Khách mời trong vòng đời ngày cưới</p>
    </div>
</div>

<!-- Thống Kê Nhanh KPI -->
<div class="mayhen_admin_kpi_grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="mayhen_admin_kpi_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px;">
        <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Tổng Số Mẫu</div>
        <div style="font-size: 24px; font-weight: 600; color: #1F1D1A;">{{ $stats['total'] }}</div>
    </div>
    <div class="mayhen_admin_kpi_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px;">
        <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Thư Điện Tử (Email)</div>
        <div style="font-size: 24px; font-weight: 600; color: #2D6A4F;">{{ $stats['email'] }}</div>
    </div>
    <div class="mayhen_admin_kpi_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px;">
        <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Tin Nhắn (SMS / ZNS)</div>
        <div style="font-size: 24px; font-weight: 600; color: #0284C7;">{{ $stats['sms'] }}</div>
    </div>
    <div class="mayhen_admin_kpi_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 18px 20px;">
        <div style="font-size: 12px; color: #7A7265; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">Đang Kích Hoạt</div>
        <div style="font-size: 24px; font-weight: 600; color: #9C7A4A;">{{ $stats['active'] }}</div>
    </div>
</div>

<!-- Bộ Lọc & Tìm Kiếm -->
<div class="mayhen_admin_filter_bar" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
    <form method="GET" action="{{ route('admin.email-templates.index') }}" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
        <div style="flex: 1; min-width: 240px;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên, mã code hoặc tiêu đề..." class="mayhen_admin_input" style="width: 100%;">
        </div>
        <div style="width: 160px;">
            <select name="channel" class="mayhen_admin_select" style="width: 100%;" onchange="this.form.submit()">
                <option value="">Tất cả kênh</option>
                <option value="email" {{ request('channel') === 'email' ? 'selected' : '' }}>Email</option>
                <option value="sms" {{ request('channel') === 'sms' ? 'selected' : '' }}>Tin nhắn SMS</option>
                <option value="zalo" {{ request('channel') === 'zalo' ? 'selected' : '' }}>Zalo ZNS</option>
            </select>
        </div>
        <div style="width: 160px;">
            <select name="status" class="mayhen_admin_select" style="width: 100%;" onchange="this.form.submit()">
                <option value="">Tất cả trạng thái</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang dùng</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tạm ngừng</option>
            </select>
        </div>
        <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="height: 38px; padding: 0 16px;">
            Lọc Danh Sách
        </button>
        @if(request()->anyFilled(['search', 'channel', 'status']))
            <a href="{{ route('admin.email-templates.index') }}" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="height: 38px; padding: 0 14px; text-decoration: none; display: inline-flex; align-items: center;">
                Xóa Bộ Lọc
            </a>
        @endif
    </form>
</div>

<!-- Bảng Danh Sách Mẫu Email -->
<div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; overflow: hidden;">
    <div style="overflow-x: auto;">
        <table class="mayhen_admin_table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #FAF8F5; border-bottom: 1px solid #EFE8DD; color: #7A7265; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                    <th style="padding: 12px 16px;">Kênh & Mã Code</th>
                    <th style="padding: 12px 16px;">Tên Nhận Diện & Tiêu Đề</th>
                    <th style="padding: 12px 16px; width: 200px;">Biến Số Hỗ Trợ</th>
                    <th style="padding: 12px 16px; width: 180px;">Thử Nghiệm Gần Nhất</th>
                    <th style="padding: 12px 16px; width: 120px; text-align: center;">Trạng Thái</th>
                    <th style="padding: 12px 16px; width: 140px; text-align: right;">Hành Động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($templates as $tmpl)
                    <tr style="border-bottom: 1px solid #F5F1EB; font-size: 13.5px;" id="template-row-{{ $tmpl->id }}">
                        <td style="padding: 14px 16px; vertical-align: top;">
                            <div style="margin-bottom: 4px;">
                                <span class="mayhen_admin_badge {{ $tmpl->channel_badge['class'] ?? 'mayhen_admin_badge--secondary' }}" style="font-size: 11px; padding: 2px 8px; border-radius: 4px; display: inline-block;">
                                    {{ strtoupper($tmpl->channel) }}
                                </span>
                            </div>
                            <code style="font-size: 11.5px; color: #5C5449; background: #F8F5EE; padding: 2px 6px; border-radius: 3px; font-family: monospace;">{{ $tmpl->code }}</code>
                        </td>
                        <td style="padding: 14px 16px; vertical-align: top;">
                            <div style="font-weight: 600; color: #1F1D1A; margin-bottom: 4px;">
                                <a href="{{ route('admin.email-templates.edit', $tmpl) }}" style="color: #1F1D1A; text-decoration: none;">
                                    {{ $tmpl->name }}
                                </a>
                            </div>
                            <div style="color: #7A7265; font-size: 12.5px; line-height: 1.4;">
                                {{ $tmpl->subject }}
                            </div>
                        </td>
                        <td style="padding: 14px 16px; vertical-align: top;">
                            <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                @if(!empty($tmpl->variables))
                                    @php $varCount = 0; @endphp
                                    @foreach(array_keys($tmpl->variables) as $varKey)
                                        @if($varCount < 3)
                                            <span style="font-size: 11px; background: #FAF8F5; border: 1px solid #EFE8DD; padding: 2px 6px; border-radius: 4px; color: #8C6D3B; font-family: monospace;">
                                                @{{ {{ $varKey }} }}
                                            </span>
                                            @php $varCount++; @endphp
                                        @endif
                                    @endforeach
                                    @if(count($tmpl->variables) > 3)
                                        <span style="font-size: 11px; color: #8C8476; padding: 2px 4px;">+{{ count($tmpl->variables) - 3 }} biến khác</span>
                                    @endif
                                @else
                                    <span style="color: #A8A196; font-size: 12px;">Không có biến</span>
                                @endif
                            </div>
                        </td>
                        <td style="padding: 14px 16px; vertical-align: top;">
                            @if($tmpl->last_tested_at)
                                <div style="font-size: 12px; color: #2D6A4F; font-weight: 500;">
                                    {{ $tmpl->last_tested_at->format('H:i d/m/Y') }}
                                </div>
                                <div style="font-size: 11px; color: #7A7265; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 160px;" title="{{ $tmpl->last_tested_email }}">
                                    {{ $tmpl->last_tested_email }}
                                </div>
                            @else
                                <span style="font-size: 12px; color: #A8A196;">Chưa gửi thử</span>
                            @endif
                        </td>
                        <td style="padding: 14px 16px; vertical-align: top; text-align: center;">
                            <button type="button" 
                                onclick="toggleTemplateActive({{ $tmpl->id }}, this)" 
                                class="mayhen_admin_badge {{ $tmpl->is_active ? 'mayhen_admin_badge--success' : 'mayhen_admin_badge--secondary' }}" 
                                style="border: none; cursor: pointer; padding: 4px 10px; border-radius: 12px; font-size: 11.5px;">
                                {{ $tmpl->is_active ? 'Đang dùng' : 'Tạm ngừng' }}
                            </button>
                        </td>
                        <td style="padding: 14px 16px; vertical-align: top; text-align: right; white-space: nowrap;">
                            <a href="{{ route('admin.email-templates.preview', $tmpl) }}" target="_blank" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="padding: 4px 8px; font-size: 12px; text-decoration: none; margin-right: 4px;" title="Xem mẫu trực tiếp">
                                Xem
                            </a>
                            <a href="{{ route('admin.email-templates.edit', $tmpl) }}" class="mayhen_admin_btn mayhen_admin_btn--primary" style="padding: 4px 10px; font-size: 12px; text-decoration: none;">
                                Soạn Mẫu
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 36px; text-align: center; color: #8C8476;">
                            Chưa tìm thấy mẫu email thông báo nào.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($templates->hasPages())
        <div style="padding: 16px; border-top: 1px solid #EFE8DD;">
            {{ $templates->links() }}
        </div>
    @endif
</div>

<script>
function toggleTemplateActive(id, btn) {
    btn.disabled = true;
    fetch(`/admin/email-templates/${id}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        if (data.success) {
            if (data.is_active) {
                btn.className = 'mayhen_admin_badge mayhen_admin_badge--success';
                btn.textContent = 'Đang dùng';
            } else {
                btn.className = 'mayhen_admin_badge mayhen_admin_badge--secondary';
                btn.textContent = 'Tạm ngừng';
            }
        }
    })
    .catch(() => {
        btn.disabled = false;
        alert('Có lỗi xảy ra khi đổi trạng thái mẫu thông báo.');
    });
}
</script>
@endsection

