@extends('client.layouts.master')

@section('title', 'Sổ Quẻ Kinh Dịch Của Tôi | ' . \App\Models\PlatformSetting::getBrandName())
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/iching.css') }}">
@endpush

@section('content')
<div class="mayhen_iching_page" style="padding: 40px 0 70px; min-height: 70vh;">
    <div class="mayhen_iching_container">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom:26px;">
            <div>
                <h1 style="font-family:'MayhenV3EditorialSerif', Georgia, serif; font-size:30px; margin:0 0 6px;">
                    Sổ Quẻ Kinh Dịch Của Tôi
                </h1>
                <p style="margin:0; font-size:14px; color:#5C5349;">
                    Lưu giữ các lá quẻ bạn đã gieo để đối chiếu Ứng Kỳ và chiêm nghiệm theo thời gian.
                </p>
            </div>
            <a href="{{ route('iching.index') }}" class="mayhen_iching_btn_primary">
                ☯ Gieo Quẻ Mới
            </a>
        </div>

        @if(session('success'))
            <div style="padding:12px 16px; border-radius:12px; background:#E8F5E9; color:#2E7D32; margin-bottom:18px; font-weight:600;">
                {{ session('success') }}
            </div>
        @endif

        @if($readings->isEmpty())
            <div style="background:#FFFFFF; border:1px solid #E5D9C6; border-radius:18px; padding:42px 24px; text-align:center;">
                <p style="font-size:15px; color:#5C5349; margin:0 0 18px;">
                    Bạn chưa lưu lá quẻ nào trong Sổ Quẻ. Hãy tĩnh tâm và gieo lá quẻ đầu tiên ngay!
                </p>
                <a href="{{ route('iching.index') }}" class="mayhen_iching_btn_primary">
                    Bắt Đầu Gieo Quẻ &rarr;
                </a>
            </div>
        @else
            <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap:16px;">
                @foreach($readings as $item)
                    @php
                        $pHex = $hexagrams[$item->primary_hexagram_no] ?? null;
                        $cHex = $item->changed_hexagram_no ? ($hexagrams[$item->changed_hexagram_no] ?? null) : null;
                        $topicName = $topics[$item->topic]['label'] ?? 'Tổng Quan';
                    @endphp
                    <article style="background:#FFFFFF; border:1px solid #E5D9C6; border-radius:16px; padding:20px; display:flex; flex-direction:column; gap:10px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                            <span class="mayhen_iching_level_pill" style="margin:0;">{{ $topicName }}</span>
                            <small style="color:#6A635B;">{{ $item->created_at?->format('H:i • d/m/Y') }}</small>
                        </div>
                        <h2 style="font-family:'MayhenV3EditorialSerif', Georgia, serif; font-size:20px; margin:0; color:#85182A;">
                            {{ $pHex ? ($pHex['name'] . ' (' . $pHex['hanzi'] . ')') : ('Quẻ #' . $item->primary_hexagram_no) }}
                            @if($cHex)
                                &rarr; {{ $cHex['name'] }}
                            @endif
                        </h2>
                        <p style="font-size:13.5px; color:#4A423A; margin:0; line-height:1.55;">
                            “{{ \Illuminate\Support\Str::limit($item->question, 110) }}”
                        </p>
                        <div style="display:flex; gap:8px; margin-top:auto; padding-top:8px;">
                            <a href="{{ route('iching.show', $item->slug) }}" class="mayhen_iching_btn_primary" style="padding:8px 14px; font-size:12.5px;">
                                Xem Luận Giải &rarr;
                            </a>
                            <form method="POST" action="{{ route('iching.destroy', $item->slug) }}" style="margin-left:auto;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mayhen_iching_btn_outline" style="padding:8px 11px; font-size:12.5px; color:#B91C1C;">
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>

            <div style="margin-top:24px;">
                {{ $readings->links('client.components.pagination') }}
            </div>
        @endif
    </div>
</div>
@endsection
