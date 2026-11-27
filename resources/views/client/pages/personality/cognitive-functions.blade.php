@extends('client.layouts.master')

@section('title', '8 Chức Năng Nhận Thức (Cognitive Functions) & Function Stack')
@section('meta_description', 'Tìm hiểu 8 chức năng nhận thức Se, Si, Ne, Ni, Te, Ti, Fe, Fi theo lý thuyết Carl Gustav Jung và bảng Function Stack tham chiếu của 16 nhóm tính cách.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/client/css/pages/personality.css') }}">
@endpush

@section('content')
<main class="mayhen_personality_page">
    <section class="mayhen_personality_hero">
        <div class="mayhen_personality_container">
            <span class="mayhen_personality_kicker">Lý thuyết Carl Gustav Jung (1921) & Isabel Myers (1980)</span>
            <h1 class="mayhen_personality_h1">Tám Chức Năng Nhận Thức (Cognitive Functions)</h1>
            <p class="mayhen_personality_lead">
                Hệ thống 8 chức năng nhận thức mô tả cách con người tiếp nhận thông tin (Se, Si, Ne, Ni) và cân nhắc quyết định (Te, Ti, Fe, Fi). Lưu ý: đây là khung lý thuyết tâm lý học, không phải vùng não hay chỉ số thần kinh đo trực tiếp.
            </p>
        </div>
    </section>

    <section class="mayhen_personality_section">
        <div class="mayhen_personality_container">
            <div class="mayhen_personality_grid_2">
                @foreach($functions as $code => $fn)
                    <div class="mayhen_personality_card">
                        <div class="mayhen_personality_type_code">{{ $code }} — {{ $fn['category'] }}</div>
                        <h2 class="mayhen_personality_h3">{{ $fn['name'] }}</h2>
                        <p style="line-height:1.65;">{{ $fn['definition'] }}</p>
                        <p style="font-size:0.88rem; color:var(--mayhen-personality-muted);"><strong>Ví dụ:</strong> {{ $fn['example'] }}</p>
                        <p style="font-size:0.84rem; color:var(--mayhen-personality-umber); margin:0;"><strong>Hiểu lầm cần tránh:</strong> {{ $fn['myth'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mayhen_personality_notice" style="margin-top:24px;">
                <strong>Ghi chú về khác biệt trường phái:</strong> Các tài liệu học thuật không thống nhất 100% về hướng nội/hướng ngoại của chức năng thứ ba (Tertiary). Function Stack hiển thị tại Mây Hẹn tuân theo quy ước phổ biến (Harold Grant / Myers-Briggs) để tham chiếu lý thuyết và tuyệt đối không tự tạo tỷ lệ % như "Ni 95%".
            </div>
        </div>
    </section>
</main>
@endsection
