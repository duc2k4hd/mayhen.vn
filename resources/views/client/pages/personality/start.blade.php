@extends('client.layouts.master')

@section('title', 'Bắt Đầu Trắc Nghiệm Tính Cách — Bản Sắc Tâm Hồn')
@section('meta_description', 'Chọn phiên bản bài trắc nghiệm 16 nhóm tính cách (32, 64, 96 câu) hoặc Big Five OCEAN (50 câu IPIP) miễn phí, tự động lưu câu trả lời.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/personality.css') }}">
@endpush

@section('content')
<main class="mayhen_personality_page">
    <section class="mayhen_personality_hero">
        <div class="mayhen_personality_narrow">
            <span class="mayhen_personality_kicker">Bắt đầu phiên làm bài mới</span>
            <h1 class="mayhen_personality_h1">Chọn phiên bản trắc nghiệm</h1>
            <p class="mayhen_personality_lead">
                Hãy trả lời dựa trên <strong>cách bạn thường suy nghĩ và hành động</strong> trong đời sống hằng ngày, thay vì hình mẫu bạn mong muốn trở thành. Không có đáp án đúng hay sai.
            </p>

            <form method="POST" action="{{ route('personality.session.create') }}" class="mayhen_personality_card">
                @csrf
                <div style="margin-bottom: 18px;">
                    <label for="display_name" style="display:block; font-weight:600; margin-bottom:6px;">Tên hiển thị trên báo cáo (tùy chọn — không bắt buộc)</label>
                    <input type="text" id="display_name" name="display_name" class="mayhen_personality_input" placeholder="Ví dụ: Minh Anh" maxlength="100">
                </div>

                <div style="display:grid; gap:12px; margin-bottom:22px;">
                    @foreach($methods as $id => $m)
                        <label style="display:flex; gap:12px; align-items:flex-start; padding:16px; border:1px solid var(--mayhen-personality-border); border-radius:10px; cursor:pointer; background: {{ $selectedMethod === $id ? '#EEF4F1' : '#FFFFFF' }};">
                            <input type="radio" name="method_id" value="{{ $id }}" {{ $selectedMethod === $id ? 'checked' : '' }} style="margin-top:4px;">
                            <div>
                                <div style="font-weight:700; color:var(--mayhen-personality-ink);">{{ $m['name'] }}</div>
                                <div style="font-size:0.86rem; color:var(--mayhen-personality-muted); margin-top:4px;">
                                    {{ $m['question_count'] }} câu • Dự kiến {{ $m['estimated_minutes'] }} • {{ $m['validation_badge'] }}
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="mayhen_personality_actions">
                    <button type="submit" class="mayhen_personality_btn mayhen_personality_btn--primary">
                        Tạo phiên & Bắt đầu trả lời
                    </button>
                    <a href="{{ route('personality.index') }}" class="mayhen_personality_btn mayhen_personality_btn--outline">
                        Quay lại trang chính
                    </a>
                </div>
            </form>
        </div>
    </section>
</main>
@endsection
