@extends('client.layouts.master')

@section('title', 'Kỷ Niệm Ngày Cưới: ' . ($wedding->groom_name ?? 'Chú Rể') . ' & ' . ($wedding->bride_name ?? 'Cô Dâu'))

@section('content')
<main class="mayhen_anniversary_lounge" style="background-color: #FAF8F5; min-height: 100vh; padding: 60px 20px 100px 20px;">
    <div style="max-width: 860px; margin: 0 auto;">
        {{-- Monogram & Khởi Đầu --}}
        <div style="text-align: center; margin-bottom: 40px;">
            <div style="display: inline-block; width: 56px; height: 56px; line-height: 56px; border-radius: 50%; background-color: #FFFFFF; border: 1px solid #D8CEBD; color: #C5A880; font-size: 26px; box-shadow: 0 4px 14px rgba(0,0,0,0.03);">
                ⚜️
            </div>
            <div style="font-size: 13px; letter-spacing: 0.18em; text-transform: uppercase; color: #8C827A; margin-top: 18px; font-weight: 500;">
                Mây Hẹn • Wedding Anniversary Keepsake
            </div>
            <h1 style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 38px; color: #1F1D1A; margin: 8px 0; font-weight: 600;">
                {{ $wedding->groom_name }} & {{ $wedding->bride_name }}
            </h1>
            <p style="font-size: 16px; color: #9C7A4A; font-weight: 600; margin: 0; text-transform: uppercase; letter-spacing: 0.08em;">
                {{ $stats['current_milestone_name'] ?? 'Kỷ Niệm Ngày Cưới' }}
            </p>
        </div>

        {{-- Thẻ Dấu Mốc Chung Đôi (Hero Counter Card) --}}
        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 20px; padding: 40px 30px; text-align: center; margin-bottom: 36px; box-shadow: 0 6px 24px rgba(0,0,0,0.02);">
            <div style="font-size: 13px; text-transform: uppercase; letter-spacing: 0.12em; color: #8C827A; margin-bottom: 8px;">
                Khoảnh Khắc Cùng Nhau
            </div>
            <div style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 56px; font-weight: 700; color: #1F1D1A; line-height: 1.1;">
                {{ number_format($stats['days_married'] ?? 365) }}
            </div>
            <div style="font-size: 16px; color: #C5A880; font-weight: 500; margin-top: 6px; letter-spacing: 0.05em;">
                Ngày Chung Một Mái Nhà
            </div>

            <div style="display: flex; justify-content: center; gap: 30px; margin-top: 24px; padding-top: 24px; border-top: 1px solid #F0EAE1; flex-wrap: wrap;">
                <div>
                    <div style="font-size: 12px; color: #8C827A; text-transform: uppercase;">Ngày cưới</div>
                    <div style="font-size: 15px; font-weight: 600; color: #1F1D1A; margin-top: 2px;">{{ $stats['wedding_date'] ?? '' }}</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: #8C827A; text-transform: uppercase;">Thâm niên</div>
                    <div style="font-size: 15px; font-weight: 600; color: #1F1D1A; margin-top: 2px;">{{ $stats['years_married'] ?? 1 }} Năm</div>
                </div>
                <div>
                    <div style="font-size: 12px; color: #8C827A; text-transform: uppercase;">Mốc tiếp theo</div>
                    <div style="font-size: 15px; font-weight: 600; color: #047857; margin-top: 2px;">Còn {{ $stats['days_until_next_anniversary'] ?? 0 }} ngày</div>
                </div>
            </div>
        </div>

        {{-- So Sánh Ảnh Cưới Ngày Ấy & Ảnh Kỷ Niệm Hiện Tại --}}
        <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 20px; padding: 32px; margin-bottom: 36px; box-shadow: 0 4px 18px rgba(0,0,0,0.02);">
            <h3 style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 24px; color: #1F1D1A; margin: 0 0 20px 0; text-align: center; font-weight: 600;">
                Hành Trình Tình Yêu Theo Năm Tháng
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
                {{-- Ảnh Cưới Ngày Xưa --}}
                <div style="text-align: center;">
                    <div style="border-radius: 14px; overflow: hidden; aspect-ratio: 4/5; background: #FAF8F5; border: 1px solid #EFE8DD; margin-bottom: 12px;">
                        <img src="{{ $wedding->cover_photo_url ?: '/assets/templates/celestial.jpg' }}" alt="Ảnh ngày cưới" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                    </div>
                    <span style="font-size: 13px; font-weight: 600; color: #8C827A; text-transform: uppercase; letter-spacing: 0.05em;">
                        Ngày Chung Đôi • {{ $stats['wedding_date'] ?? '' }}
                    </span>
                </div>

                {{-- Ảnh Kỷ Niệm Hiện Tại --}}
                <div style="text-align: center;">
                    <div style="border-radius: 14px; overflow: hidden; aspect-ratio: 4/5; background: #FAF8F5; border: 1px solid #EFE8DD; margin-bottom: 12px;">
                        @if(!empty($config['anniversary_photo_url']))
                            <img src="{{ $config['anniversary_photo_url'] }}" alt="Ảnh kỷ niệm" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                        @else
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; flex-direction: column; padding: 20px; box-sizing: border-box; color: #8C827A;">
                                <div style="font-size: 32px; margin-bottom: 8px;">📷</div>
                                <p style="font-size: 13px; margin: 0; line-height: 1.5;">Bức ảnh kỷ niệm sau {{ $stats['years_married'] ?? 1 }} năm của cặp đôi sẽ xuất hiện tại đây.</p>
                            </div>
                        @endif
                    </div>
                    <span style="font-size: 13px; font-weight: 600; color: #9C7A4A; text-transform: uppercase; letter-spacing: 0.05em;">
                        Hôm Nay • {{ $stats['current_milestone_name'] ?? 'Kỷ Niệm' }}
                    </span>
                </div>
            </div>

            {{-- Lời Ngỏ Cảm Nhận Sau 1 Năm --}}
            @if(!empty($config['anniversary_reflection_note']))
                <div style="margin-top: 28px; padding: 22px; background: #FAF8F5; border-radius: 12px; border-left: 3px solid #C5A880;">
                    <p style="font-size: 14.5px; line-height: 1.7; color: #4A453E; margin: 0; font-style: italic;">
                        "{{ $config['anniversary_reflection_note'] }}"
                    </p>
                    <div style="font-size: 12px; color: #8C827A; text-align: right; margin-top: 8px; font-weight: 600;">
                        — {{ $wedding->groom_name }} & {{ $wedding->bride_name }}
                    </div>
                </div>
            @endif
        </div>

        {{-- Lời Chúc Ngày Cưới Từ Người Thân & Bạn Bè --}}
        @if(!empty($wishes) && count($wishes) > 0)
            <div style="background: #FFFFFF; border: 1px solid #EFE8DD; border-radius: 20px; padding: 32px; margin-bottom: 36px; box-shadow: 0 4px 18px rgba(0,0,0,0.02);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                    <h3 style="font-family: 'Cormorant Garamond', Georgia, serif; font-size: 24px; color: #1F1D1A; margin: 0; font-weight: 600;">
                        Sổ Lưu Bút Kỷ Niệm ({{ $total_wishes }} lời chúc)
                    </h3>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
                    @foreach($wishes as $wish)
                        <div style="background: #FAF8F5; border: 1px solid #EFE8DD; border-radius: 12px; padding: 16px; font-size: 13.5px;">
                            <div style="font-weight: 600; color: #1F1D1A; margin-bottom: 6px;">{{ $wish->sender_name }}</div>
                            <p style="color: #4A453E; margin: 0; line-height: 1.5; font-style: italic;">
                                "{{ $wish->message }}"
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Nút Điều Hướng Trải Nghiệm --}}
        <div style="text-align: center; display: flex; justify-content: center; gap: 14px; flex-wrap: wrap;">
            <a href="{{ route('invitation.show', $invitation->slug) }}" 
               style="background: #1F1D1A; color: #FFFFFF; padding: 13px 26px; border-radius: 8px; font-size: 14px; text-decoration: none; font-weight: 600;">
                Mở Lại Chiếc Thiệp Cưới Gốc &rarr;
            </a>
            <a href="{{ route('account.invitations') }}" 
               style="background: #FFFFFF; border: 1px solid #D8CEBD; color: #4A453E; padding: 13px 22px; border-radius: 8px; font-size: 14px; text-decoration: none; font-weight: 600;">
                Quản Trị Tại Studio
            </a>
        </div>
    </div>
</main>
@endsection

