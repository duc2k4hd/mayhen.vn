@extends('client.layouts.master')

@section('title', 'Gia Phả Của Tôi')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/family-tree.css') }}">
@endpush

@section('content')
<div class="mayhen_family_tree_my_page">
    <div class="mayhen_family_tree_container">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom:26px;">
            <div>
                <h1 style="font-family:'MayhenV3EditorialSerif', Georgia, serif; font-size:30px; margin:0 0 6px;">
                    Gia Phả Online Của Tôi
                </h1>
                <p style="margin:0; font-size:14px; color:#5C5349;">
                    Gói hiện tại: <strong>{{ $currentPlanLabel }}</strong> • Đã lưu {{ $trees->count() }} / {{ $maxTrees }} gia phả link thật.
                </p>
            </div>
            <a href="{{ route('family_tree.studio') }}" class="mayhen_family_tree_btn_primary">
                + Tạo Gia Phả Mới
            </a>
        </div>

        @if(session('success'))
            <div style="padding:12px 16px; border-radius:12px; background:#E8F5E9; color:#2E7D32; margin-bottom:18px; font-weight:600;">
                {{ session('success') }}
            </div>
        @endif

        @if($trees->isEmpty())
            <div style="background:#FFFFFF; border:1px solid #E5DAC9; border-radius:18px; padding:42px 24px; text-align:center;">
                <p style="font-size:15px; color:#5C5349; margin:0 0 18px;">
                    Bạn chưa xuất bản link gia phả thật nào. Hãy vào Studio để tạo gia phả dòng họ ngay!
                </p>
                <a href="{{ route('family_tree.studio') }}" class="mayhen_family_tree_btn_primary">
                    Bắt Đầu Tạo Gia Phả &rarr;
                </a>
            </div>
        @else
            <div class="mayhen_family_tree_my_grid">
                @foreach($trees as $tree)
                    @php
                        $memberCount = is_array($tree->members_json) ? count($tree->members_json) : 0;
                    @endphp
                    <article class="mayhen_family_tree_my_card">
                        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                            <h2 style="font-family:'MayhenV3EditorialSerif', Georgia, serif; font-size:19px; margin:0; color:#85182A;">
                                {{ $tree->title }}
                            </h2>
                            <span style="font-size:11px; padding:3px 9px; border-radius:999px; background:#FAF5EB; border:1px solid #CFA860; font-weight:700;">
                                {{ $tree->visibility === 'password' ? '🔒 Có mật khẩu' : 'Link riêng' }}
                            </span>
                        </div>
                        <p style="font-size:13px; color:#5C5349; margin:0;">
                            {{ $memberCount }} thành viên • {{ $tree->view_count }} lượt xem • Hết hạn: {{ $tree->expires_at ? $tree->expires_at->format('d/m/Y') : 'Vĩnh viễn' }}
                        </p>
                        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-top:auto; padding-top:8px;">
                            <a href="{{ route('family_tree.show', $tree->slug) }}" target="_blank" class="mayhen_family_tree_btn_outline" style="padding:8px 12px; font-size:12.5px;">
                                Mở Link
                            </a>
                            <a href="{{ route('family_tree.studio', ['tree' => $tree->slug]) }}" class="mayhen_family_tree_btn_primary" style="padding:8px 12px; font-size:12.5px;">
                                Chỉnh Sửa
                            </a>
                            <form method="POST" action="{{ route('family_tree.destroy', $tree->slug) }}" style="margin-left:auto;" data-ft-clear-storage>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mayhen_family_tree_btn_outline" style="padding:8px 11px; font-size:12.5px; color:#B91C1C;">
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    <script type="module" src="{{ asset_v('assets/client/js/pages/family-tree.js') }}"></script>
@endpush
