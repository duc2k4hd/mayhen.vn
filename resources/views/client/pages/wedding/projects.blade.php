@extends('client.layouts.master')

@section('title', 'Hồ Sơ Tổ Chức Đám Cưới Của Tôi')
@section('meta_robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/wedding-journey.css') }}">
@endpush

@section('content')
<div class="mayhen_wedding_planner_page">
    <div class="mayhen_wedding_planner_container">
        <section class="mayhen_wedding_planner_hero">
            <span class="mayhen_wedding_planner_eyebrow">Không Gian Tổ Chức Hôn Lễ Cá Nhân</span>
            <h1 class="mayhen_wedding_planner_h1">Hồ Sơ Tổ Chức Đám Cưới Của Tôi</h1>
            <p class="mayhen_wedding_planner_lead">Gói hiện tại: <strong>{{ $planSummary['tier_label'] }}</strong> — Đang sử dụng {{ $projects->count() }}/{{ $planSummary['max_projects'] }} hồ sơ.</p>
            <div class="mayhen_wedding_planner_subnav">
                <a href="{{ route('wedding.planner.index') }}" class="mayhen_wedding_planner_pill">+ Tạo Hồ Sơ Đám Cưới Mới</a>
                <a href="{{ route('wedding.dates.index') }}" class="mayhen_wedding_planner_pill">Xem Ngày Cưới</a>
                <a href="{{ route('account.invitations') }}" class="mayhen_wedding_planner_pill">Thiệp Cưới Của Tôi</a>
            </div>
        </section>

        @if ($projects->isEmpty())
            <section class="mayhen_wedding_planner_card" style="text-align:center;padding:40px 24px;">
                <h2 class="mayhen_wedding_planner_card_title">Bạn chưa có hồ sơ tổ chức đám cưới nào</h2>
                <p class="mayhen_wedding_planner_card_sub">Khởi tạo ngay để tự động nhận Checklist 3 miền, bảng Ngân sách VND và Studio Sơ đồ bàn tiệc 2D.</p>
                <a href="{{ route('wedding.planner.index') }}" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--crimson">Khởi Tạo Hồ Sơ Ngay →</a>
            </section>
        @else
            <div class="mayhen_wedding_planner_grid_3">
                @foreach ($projects as $p)
                    <article class="mayhen_wedding_planner_card">
                        <span class="mayhen_wedding_date_badge mayhen_wedding_date_badge--favorable" style="margin-bottom:8px;">{{ strtoupper($p->status) }}</span>
                        <h2 style="margin:0 0 6px;font-size:20px;">{{ $p->title }}</h2>
                        <p style="font-size:13.5px;color:#5F5750;margin:0 0 12px;">
                            Ngày dự kiến: <strong>{{ $p->wedding_date ? $p->wedding_date->format('d/m/Y') : 'Chưa chốt' }}</strong><br>
                            Ngân sách: <strong>{{ number_format((int) $p->budget_total_vnd, 0, ',', '.') }} ₫</strong> · {{ $p->guest_target }} khách<br>
                            {{ $p->tasks_count }} công việc · {{ $p->expenses_count }} khoản chi · {{ $p->vendors_count }} NCC
                        </p>
                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                            <a href="{{ route('wedding.workspace.show', ['slug' => $p->slug]) }}" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--primary mayhen_wedding_planner_btn--sm">Mở Workspace →</a>
                            <form action="{{ route('wedding.workspace.destroy', ['slug' => $p->slug]) }}" method="POST" onsubmit="return confirm('Xóa vĩnh viễn hồ sơ đám cưới này?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mayhen_wedding_planner_btn mayhen_wedding_planner_btn--outline mayhen_wedding_planner_btn--sm">Xóa</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
