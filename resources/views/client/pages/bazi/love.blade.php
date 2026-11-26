@extends('client.layouts.master')

@section('title', $pageMeta['title'])
@section('meta_description', $pageMeta['meta_description'])
@section('canonical', $pageMeta['canonical'])

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/bazi.css') }}?v={{ filemtime(public_path('assets/client/css/pages/bazi.css')) }}">
@endpush

@section('content')
<div class="mayhen_bazi_page">
    <section class="mayhen_bazi_hero">
        <div class="mayhen_bazi_container">
            <div class="mayhen_bazi_eyebrow">✦ MÂY HẸN – TỨ TRỤ KẾT DUYÊN · HÔN NHÂN &amp; ĐỒNG HÀNH</div>
            <h1 class="mayhen_bazi_h1">{{ $pageMeta['h1'] }}</h1>
            <p class="mayhen_bazi_lead">Lập hai lá số Bát Tự độc lập để thấu hiểu tương tác Nhật Chủ, Cung Phối Ngẫu, Ngũ Hành bổ trợ và 12 chủ đề đồng hành trước hôn nhân. Không chấm điểm % hợp nhau giả và không kết luận chia ly.</p>

            <form id="mayhenBaziLoveForm" data-endpoint="{{ route('bazi.love.analyze') }}">
                <div class="mayhen_bazi_two_col">
                    @foreach (['a' => ['Người Thứ Nhất (A)', 1995, 5, 15, 'male'], 'b' => ['Người Thứ Hai (B)', 1997, 8, 20, 'female']] as $prefix => $def)
                        <div class="mayhen_bazi_card">
                            <h2 class="mayhen_bazi_card_title">{{ $def[0] }}</h2>
                            <div class="mayhen_bazi_form_grid mayhen_bazi_form_grid--2">
                                <div class="mayhen_bazi_field">
                                    <label>Tên / Biệt danh</label>
                                    <input name="{{ $prefix }}_name" type="text" class="mayhen_bazi_input" placeholder="{{ $def[0] }}">
                                </div>
                                <div class="mayhen_bazi_field">
                                    <label>Loại lịch</label>
                                    <select name="{{ $prefix }}_calendar_type" class="mayhen_bazi_select">
                                        <option value="solar">Dương lịch</option>
                                        <option value="lunar">Âm lịch Việt Nam</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mayhen_bazi_form_grid">
                                <div class="mayhen_bazi_field">
                                    <label>Ngày</label>
                                    <input name="{{ $prefix }}_day" type="number" min="1" max="31" value="{{ $def[3] }}" class="mayhen_bazi_input" required>
                                </div>
                                <div class="mayhen_bazi_field">
                                    <label>Tháng</label>
                                    <input name="{{ $prefix }}_month" type="number" min="1" max="12" value="{{ $def[2] }}" class="mayhen_bazi_input" required>
                                </div>
                                <div class="mayhen_bazi_field">
                                    <label>Năm</label>
                                    <input name="{{ $prefix }}_year" type="number" min="1900" max="2100" value="{{ $def[1] }}" class="mayhen_bazi_input" required>
                                </div>
                            </div>
                            <div class="mayhen_bazi_form_grid mayhen_bazi_form_grid--2">
                                <div class="mayhen_bazi_field">
                                    <label>Giờ sinh (để trống nếu không biết)</label>
                                    <input name="{{ $prefix }}_hour" type="number" min="0" max="23" placeholder="0–23" class="mayhen_bazi_input">
                                </div>
                                <div class="mayhen_bazi_field">
                                    <label>Giới tính (tùy chọn)</label>
                                    <select name="{{ $prefix }}_gender" class="mayhen_bazi_select">
                                        <option value="male" @selected($def[4] === 'male')>Nam</option>
                                        <option value="female" @selected($def[4] === 'female')>Nữ</option>
                                        <option value="unspecified">Không chọn</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="margin-top:16px;text-align:center;">
                    <button type="submit" class="mayhen_bazi_btn mayhen_bazi_btn--crimson">Phân Tích Tứ Trụ Kết Duyên</button>
                    <a href="{{ route('bazi.index') }}" class="mayhen_bazi_btn mayhen_bazi_btn--outline" style="margin-left:8px;">&larr; Quay Lại Lập Lá Số Cá Nhân</a>
                </div>
            </form>

            <div id="mayhenBaziLoveResult"></div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/client/js/pages/bazi.js') }}?v={{ filemtime(public_path('assets/client/js/pages/bazi.js')) }}" defer></script>
@endpush
