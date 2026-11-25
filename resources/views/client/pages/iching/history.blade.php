@extends('client.layouts.master')

@section('title', 'Sổ Quẻ Kinh Dịch Riêng Tư')
@section('robots', 'noindex, nofollow')

@push('styles')
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/common/invitation-fonts-v3.css') }}">
    <link rel="stylesheet" href="{{ asset_v('assets/client/css/pages/iching.css') }}">
@endpush

@section('content')
<div class="mayhen_iching_page mayhen_iching_page_padded">
    <div class="mayhen_iching_container">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:14px; margin-bottom:22px;">
            <div>
                <h1 style="font-family:'MayhenV3EditorialSerif', Georgia, serif; font-size:30px; margin:0 0 6px;">
                    Sổ Quẻ Riêng Tư Của Tôi
                </h1>
                <p style="margin:0; font-size:14px; color:#5C5349;">
                    Mọi lá quẻ đều được bảo mật riêng tư. Quay lại sau 7 / 30 / 90 ngày để ghi Nhật ký chiêm nghiệm.
                </p>
            </div>
            <a href="{{ route('iching.index') }}" class="mayhen_iching_btn_primary">
                ☯ Gieo Quẻ Mới
            </a>
        </div>

        <div class="mayhen_iching_filter_pills" style="margin-bottom:20px;">
            <a href="{{ route('iching.history') }}" class="mayhen_iching_filter_btn {{ ($filter ?? 'all') === 'all' && empty($topicFilter) ? 'is-active' : '' }}" style="text-decoration:none;">
                Tất cả lá quẻ
            </a>
            <a href="{{ route('iching.history', ['filter' => 'favorites']) }}" class="mayhen_iching_filter_btn {{ ($filter ?? 'all') === 'favorites' ? 'is-active' : '' }}" style="text-decoration:none;">
                ★ Yêu thích
            </a>
            @foreach($topics as $tKey => $tMeta)
                <a href="{{ route('iching.history', ['topic' => $tKey]) }}" class="mayhen_iching_filter_btn {{ ($topicFilter ?? '') === $tKey ? 'is-active' : '' }}" style="text-decoration:none;">
                    {{ $tMeta['label'] }}
                </a>
            @endforeach
        </div>

        @if(session('success'))
            <div style="padding:12px 16px; border-radius:12px; background:#E8F5E9; color:#2E7D32; margin-bottom:18px; font-weight:600;">
                {{ session('success') }}
            </div>
        @endif

        @if($readings->isEmpty())
            <div style="background:#FFFFFF; border:1px solid #E5D9C6; border-radius:18px; padding:42px 24px; text-align:center;">
                <p style="font-size:15px; color:#5C5349; margin:0 0 18px;">
                    Chưa có lá quẻ nào phù hợp bộ lọc này. Hãy tĩnh tâm và gieo lá quẻ cho điều bạn đang quan tâm.
                </p>
                <a href="{{ route('iching.index') }}" class="mayhen_iching_btn_primary">Bắt Đầu Gieo Quẻ &rarr;</a>
            </div>
        @else
            <div class="mayhen_iching_history_grid">
                @foreach($readings as $item)
                    @php
                        $pHex = $hexagrams[$item->primary_hexagram_no] ?? null;
                        $cHex = $item->changed_hexagram_no ? ($hexagrams[$item->changed_hexagram_no] ?? null) : null;
                        $topicName = $topics[$item->topic]['label'] ?? 'Tổng Quan';
                    @endphp
                    <article class="mayhen_iching_history_card">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap;">
                            <span class="mayhen_iching_level_pill" style="margin:0;">
                                {{ $topicName }}
                                @if($item->guidance_state)
                                    • {{ $item->guidance_state }}
                                @endif
                            </span>
                            <small style="color:#6A635B;">{{ $item->created_at?->format('H:i • d/m/Y') }}</small>
                        </div>

                        <h2 style="font-family:'MayhenV3EditorialSerif', Georgia, serif; font-size:20px; margin:0; color:#85182A;">
                            @if($item->is_favorite) ★ @endif
                            {{ $pHex ? ($pHex['name'] . ' (' . $pHex['hanzi'] . ')') : ('Quẻ #' . $item->primary_hexagram_no) }}
                            @if($cHex)
                                &rarr; {{ $cHex['name'] }}
                            @endif
                        </h2>

                        <p style="font-size:13.5px; color:#4A423A; margin:0; line-height:1.55;">
                            “{{ \Illuminate\Support\Str::limit($item->question, 110) }}”
                        </p>

                        <div style="font-size:12px; color:#6A635B; display:flex; flex-wrap:wrap; gap:10px;">
                            <span>{{ $item->isSharedActive() ? '🔗 Đang bật Thẻ Chia Sẻ' : '🔒 Riêng tư' }}</span>
                            <span>💬 {{ $item->followups_count ?? 0 }} hỏi sâu</span>
                            <span>📓 {{ $item->journals_count ?? 0 }} nhật ký</span>
                        </div>

                        <div style="display:flex; gap:8px; margin-top:auto; padding-top:8px;">
                            <a href="{{ route('iching.show', $item->slug) }}" class="mayhen_iching_btn_primary mayhen_iching_btn_sm">
                                Mở Lá Quẻ &rarr;
                            </a>
                            <form method="POST" action="{{ route('iching.destroy', $item->slug) }}" style="margin-left:auto;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="mayhen_iching_btn_outline mayhen_iching_btn_sm" style="color:#B91C1C;">
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
