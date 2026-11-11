@extends('admin.layouts.master')

@section('title', 'Soạn Mẫu: ' . $template->name)

@section('content')
<div class="mayhen_admin_page_header" style="margin-bottom: 20px;">
    <div class="mayhen_admin_page_header_left">
        <div style="margin-bottom: 6px;">
            <a href="{{ route('admin.email-templates.index') }}" style="color: #9C7A4A; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 4px;">
                &larr; Quay lại danh sách mẫu thông báo
            </a>
        </div>
        <h1 class="mayhen_admin_page_title" style="font-size: 22px;">{{ $template->name }}</h1>
        <p class="mayhen_admin_page_subtitle">Mã định danh: <code style="background: #F8F5EE; padding: 2px 6px; border-radius: 4px; font-size: 12px; color: #5C5449;">{{ $template->code }}</code> • Kênh truyền thông: <span class="mayhen_admin_badge {{ $template->channel_badge['class'] ?? 'mayhen_admin_badge--secondary' }}">{{ strtoupper($template->channel) }}</span></p>
    </div>
    <div class="mayhen_admin_page_header_right">
        <form method="POST" action="{{ route('admin.email-templates.reset', $template) }}" onsubmit="return confirm('Bạn có chắc chắn muốn khôi phục mẫu này về nội dung mặc định của Mây Hẹn Atelier? Toàn bộ tùy biến hiện tại sẽ được thay bằng mẫu chuẩn ban đầu.');">
            @csrf
            <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="font-size: 12.5px; padding: 6px 12px; color: #8C2D19;">
                Khôi Phục Mẫu Gốc Atelier
            </button>
        </form>
    </div>
</div>

@if(session('success'))
    <div style="background: #E8F5E9; border: 1px solid #C8E6C9; border-left: 4px solid #2E7D32; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; color: #1B5E20; font-size: 13.5px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background: #FFEBEE; border: 1px solid #FFCDD2; border-left: 4px solid #C62828; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; color: #B71C1C; font-size: 13.5px;">
        {{ session('error') }}
    </div>
@endif

<div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 24px; align-items: start;">
    <!-- CỘT TRÁI: FORM BIÊN TẬP NỘI DUNG -->
    <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 24px;">
        <form method="POST" action="{{ route('admin.email-templates.update', $template) }}" id="template-edit-form">
            @csrf
            @method('PUT')

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                    Tên Nhận Diện Mẫu <span style="color: #C62828;">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $template->name) }}" class="mayhen_admin_input" style="width: 100%;" required>
                @error('name')
                    <div style="color: #C62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                    Tiêu Đề Thư Điện Tử (Subject) <span style="color: #C62828;">*</span>
                </label>
                <input type="text" name="subject" id="template_subject" value="{{ old('subject', $template->subject) }}" class="mayhen_admin_input" style="width: 100%;" required>
                <p style="font-size: 11.5px; color: #7A7265; margin-top: 4px;">Có thể nhúng biến động như <code>@{{ order_code }}</code>, <code>@{{ customer_name }}</code> vào tiêu đề.</p>
                @error('subject')
                    <div style="color: #C62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <!-- Hộp Danh Sách Biến Số Khả Dụng -->
            <div style="background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 6px; padding: 14px 16px; margin-bottom: 20px;">
                <div style="font-size: 12.5px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                    Danh Sách Biến Số Động (Bấm để chèn vào nội dung)
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                    @if(!empty($template->variables))
                        @foreach($template->variables as $varKey => $varDesc)
                            <button type="button" 
                                class="token-insert-btn" 
                                data-token="@{{ {{ $varKey }} }}" 
                                title="{{ $varDesc }}" 
                                style="background: #FFFFFF; border: 1px solid #D8CEBD; border-radius: 4px; padding: 4px 8px; font-size: 12px; color: #8C6D3B; cursor: pointer; transition: all 0.2s;">
                                @{{ {{ $varKey }} }}
                            </button>
                        @endforeach
                    @else
                        <span style="font-size: 12px; color: #7A7265;">Mẫu này không sử dụng biến số thay thế.</span>
                    @endif
                </div>
                <div style="font-size: 11px; color: #8C8476; margin-top: 8px;">
                    Gợi ý: Đặt con trỏ vào ô soạn thảo bên dưới rồi bấm vào biến số ở trên để chèn nhanh.
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label style="font-size: 13px; font-weight: 600; color: #1F1D1A;">
                        Nội Dung Thư HTML (Body HTML) <span style="color: #C62828;">*</span>
                    </label>
                    <span style="font-size: 11.5px; color: #7A7265;">Hỗ trợ HTML + Inline CSS Quiet Luxury</span>
                </div>
                <textarea name="body_html" id="template_body_html" rows="18" class="mayhen_admin_textarea" style="width: 100%; font-size: 12.5px; line-height: 1.5; padding: 12px;" required>{{ old('body_html', $template->body_html) }}</textarea>
                @error('body_html')
                    <div style="color: #C62828; font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 13px; font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">
                    Nội Dung Văn Bản Thuần (Plain Text Fallback)
                </label>
                <textarea name="body_text" rows="5" class="mayhen_admin_textarea" style="width: 100%; font-size: 13px; line-height: 1.5; padding: 10px;">{{ old('body_text', $template->body_text) }}</textarea>
                <p style="font-size: 11.5px; color: #7A7265; margin-top: 4px;">Hiển thị cho các ứng dụng email không hỗ trợ HTML hoặc chế độ xem nhanh của đồng hồ thông minh.</p>
            </div>

            <div style="margin-bottom: 24px; padding: 12px 16px; background: #FAF8F5; border-radius: 6px; border: 1px solid #EFE8DD;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13.5px; font-weight: 500; color: #1F1D1A; margin: 0;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }} style="width: 16px; height: 16px;">
                    Kích hoạt sử dụng mẫu này khi hệ thống tự động gửi thông báo
                </label>
            </div>

            <div style="display: flex; gap: 12px; align-items: center;">
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="padding: 10px 24px; font-size: 13.5px;">
                    Lưu Thay Đổi Mẫu
                </button>
                <a href="{{ route('admin.email-templates.index') }}" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="padding: 10px 18px; font-size: 13.5px; text-decoration: none;">
                    Hủy Bỏ
                </a>
            </div>
        </form>
    </div>

    <!-- CỘT PHẢI: GỬI THỬ NGHIỆM & LIVE PREVIEW -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
        <!-- Card 1: Gửi Thư Thử Nghiệm -->
        <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; padding: 20px;">
            <h3 style="margin: 0 0 6px; font-size: 15px; font-weight: 600; color: #1F1D1A;">
                Gửi Thư Thử Nghiệm (Test Dispatch)
            </h3>
            <p style="margin: 0 0 14px; font-size: 12.5px; color: #7A7265;">
                Hệ thống sẽ nạp dữ liệu mẫu giả định và gửi thư thật đến hòm thư kiểm tra của bạn.
            </p>

            <form id="test-email-form" onsubmit="sendTestEmail(event)">
                <div style="display: flex; gap: 8px;">
                    <input type="email" id="test_email_input" placeholder="admin@mayhen.vn" value="{{ auth()->user()->email ?? '' }}" class="mayhen_admin_input" style="flex: 1;" required>
                    <button type="submit" id="btn-send-test" class="mayhen_admin_btn mayhen_admin_btn--primary" style="padding: 0 16px; font-size: 13px; white-space: nowrap;">
                        Gửi Test
                    </button>
                </div>
            </form>
            <div id="test-result-box" style="margin-top: 10px; font-size: 12px; display: none;"></div>

            @if($template->last_tested_at)
                <div style="margin-top: 12px; font-size: 11.5px; color: #7A7265; border-top: 1px dashed #EFE8DD; padding-top: 8px;">
                    Lần test gần nhất: <strong>{{ $template->last_tested_at->format('H:i d/m/Y') }}</strong> gửi tới <em>{{ $template->last_tested_email }}</em>
                </div>
            @endif
        </div>

        <!-- Card 2: Xem Trước Trực Quan (Live Preview) -->
        <div class="mayhen_admin_card" style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 8px; overflow: hidden; display: flex; flex-direction: column;">
            <div style="padding: 14px 18px; border-bottom: 1px solid #EFE8DD; background: #FAF8F5; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 14px; font-weight: 600; color: #1F1D1A;">
                        Xem Trước Giao Diện (Live Preview)
                    </h3>
                    <span style="font-size: 11px; color: #7A7265;">Mô phỏng hiển thị trên hộp thư người nhận</span>
                </div>
                <button type="button" onclick="refreshPreview()" class="mayhen_admin_btn mayhen_admin_btn--secondary" style="font-size: 11.5px; padding: 4px 8px;">
                    Làm Mới
                </button>
            </div>
            <div style="background: #EDEAE4; padding: 12px; display: flex; justify-content: center;">
                <iframe id="preview-iframe" src="{{ route('admin.email-templates.preview', $template) }}" style="width: 100%; height: 560px; border: 1px solid #D8CEBD; border-radius: 6px; background: #FFFFFF;"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
// Chèn biến số vào con trỏ của textarea HTML
document.querySelectorAll('.token-insert-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const token = this.getAttribute('data-token');
        const textarea = document.getElementById('template_body_html');
        
        if (textarea) {
            const startPos = textarea.selectionStart;
            const endPos = textarea.selectionEnd;
            const currentVal = textarea.value;
            
            textarea.value = currentVal.substring(0, startPos) + token + currentVal.substring(endPos);
            textarea.selectionStart = textarea.selectionEnd = startPos + token.length;
            textarea.focus();
            
            // Animation phản hồi
            const originalBg = this.style.backgroundColor;
            this.style.backgroundColor = '#EFE8DD';
            setTimeout(() => { this.style.backgroundColor = originalBg; }, 200);
        }
    });
});

// Làm mới khung Preview
function refreshPreview() {
    const iframe = document.getElementById('preview-iframe');
    if (iframe) {
        iframe.src = iframe.src.split('?')[0] + '?t=' + new Date().getTime();
    }
}

// Gửi thư thử nghiệm bằng AJAX
function sendTestEmail(e) {
    e.preventDefault();
    const email = document.getElementById('test_email_input').value;
    const btn = document.getElementById('btn-send-test');
    const resultBox = document.getElementById('test-result-box');

    if (!email) return;

    btn.disabled = true;
    btn.textContent = 'Đang gửi...';
    resultBox.style.display = 'block';
    resultBox.style.color = '#7A7265';
    resultBox.textContent = 'Đang kết nối gửi email thử nghiệm...';

    fetch('{{ route("admin.email-templates.test", $template) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ test_email: email })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = 'Gửi Test';
        if (data.success) {
            resultBox.style.color = '#2E7D32';
            resultBox.textContent = '✓ ' + data.message;
        } else {
            resultBox.style.color = '#C62828';
            resultBox.textContent = '✗ ' + data.message;
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.textContent = 'Gửi Test';
        resultBox.style.color = '#C62828';
        resultBox.textContent = '✗ Có lỗi xảy ra trong quá trình gửi email.';
    });
}
</script>
@endsection

