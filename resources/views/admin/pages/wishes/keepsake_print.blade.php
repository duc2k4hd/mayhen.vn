<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sổ Lưu Bút Kỷ Niệm: {{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }} | Mây Hẹn</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --font-main: 'Open Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --color-cream: #faf8f5;
            --color-card-bg: #ffffff;
            --color-border: #e7e5e4;
            --color-charcoal: #1c1917;
            --color-muted: #78716c;
            --color-gold: #b45309;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-main);
            background: var(--color-cream);
            color: var(--color-charcoal);
            line-height: 1.6;
            padding: 30px;
        }

        /* Action Toolbar */
        .keepsake-toolbar {
            max-width: 900px;
            margin: 0 auto 30px auto;
            background: #ffffff;
            border: 1px solid var(--color-border);
            border-radius: 10px;
            padding: 14px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        .keepsake-btn {
            font-family: var(--font-main);
            font-size: 13px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid var(--color-border);
            background: #ffffff;
            color: var(--color-charcoal);
        }

        .keepsake-btn-primary {
            background: #292524;
            color: #ffffff;
            border-color: #292524;
        }

        /* Album Container */
        .keepsake-book {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid var(--color-border);
            border-radius: 16px;
            padding: 50px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
        }

        /* Cover Section */
        .keepsake-cover {
            text-align: center;
            padding-bottom: 40px;
            border-bottom: 2px solid #f5f5f4;
            margin-bottom: 40px;
        }

        .keepsake-brand {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--color-gold);
            margin-bottom: 12px;
        }

        .keepsake-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--color-charcoal);
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .keepsake-couple {
            font-size: 24px;
            font-weight: 600;
            color: var(--color-gold);
            margin-bottom: 12px;
        }

        .keepsake-meta {
            font-size: 14px;
            color: var(--color-muted);
        }

        /* Wishes Grid */
        .keepsake-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 24px;
        }

        .keepsake-card {
            background: #fafaf9;
            border: 1px solid #e7e5e4;
            border-radius: 12px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 16px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .keepsake-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid #f0ede8;
            padding-bottom: 10px;
        }

        .keepsake-sender {
            font-weight: 700;
            font-size: 16px;
            color: var(--color-charcoal);
        }

        .keepsake-date {
            font-size: 12px;
            color: var(--color-muted);
        }

        .keepsake-message {
            font-size: 14px;
            line-height: 1.7;
            font-style: italic;
            color: #332f2b;
        }

        .keepsake-signature-wrap {
            margin-top: 8px;
            padding-top: 10px;
            border-top: 1px dashed #d6d3d1;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .keepsake-signature-img {
            max-height: 60px;
            max-width: 160px;
            object-fit: contain;
            filter: contrast(130%);
        }

        .keepsake-reply {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 12px;
            color: #14532d;
            margin-top: 6px;
        }

        .keepsake-footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: var(--color-muted);
            border-top: 1px solid #f5f5f4;
            padding-top: 24px;
        }

        /* Print Mode */
        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }

            .keepsake-toolbar {
                display: none !important;
            }

            .keepsake-book {
                border: none;
                box-shadow: none;
                padding: 20px 0;
                max-width: 100%;
            }

            .keepsake-grid {
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }

            .keepsake-card {
                border: 1px solid #d6d3d1;
                background: #ffffff;
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Action Toolbar (Ẩn khi in) -->
    <div class="keepsake-toolbar">
        <div>
            <span style="font-weight: 700; font-size: 14px;">Bản In Sổ Lưu Bút Kỷ Niệm Ngày Cưới</span>
            <span style="font-size: 12px; color: var(--color-muted); margin-left: 8px;">(Khổ in A4 / Lưu PDF trọn đời)</span>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.wishes.guestbook.index', ['wedding_id' => $wedding->id]) }}" class="keepsake-btn">
                &larr; Quay Lại Quản Lý
            </a>
            <button type="button" onclick="window.print()" class="keepsake-btn keepsake-btn-primary">
                In Sách / Lưu PDF
            </button>
        </div>
    </div>

    <!-- Main Book -->
    <div class="keepsake-book">
        <!-- Cover Header -->
        <div class="keepsake-cover">
            <div class="keepsake-brand">Mây Hẹn • Wedding Keepsake Guestbook</div>
            <h1 class="keepsake-title">Sổ Lưu Bút Kỷ Niệm</h1>
            <div class="keepsake-couple">{{ $wedding->groom_name }} &amp; {{ $wedding->bride_name }}</div>
            <div class="keepsake-meta">
                Ngày cưới: <strong>{{ $wedding->wedding_date ? $wedding->wedding_date->format('d/m/Y') : '—' }}</strong>
                @if($wedding->venue_name)
                    • Sảnh tiệc: <strong>{{ $wedding->venue_name }}</strong>
                @endif
                • Tổng cộng: <strong>{{ $wishes->count() }}</strong> lời chúc phúc
            </div>
        </div>

        <!-- Wishes Cards Grid -->
        <div class="keepsake-grid">
            @forelse($wishes as $wish)
                <div class="keepsake-card">
                    <div>
                        <div class="keepsake-card-header">
                            <div>
                                <div class="keepsake-sender">{{ $wish->sender_name }}</div>
                                @if($wish->guest && $wish->guest->group_name)
                                    <div style="font-size: 11px; color: var(--color-muted);">{{ $wish->guest->group_name }}</div>
                                @endif
                            </div>
                            <div class="keepsake-date">
                                {{ $wish->created_at ? $wish->created_at->format('d/m/Y') : '' }}
                            </div>
                        </div>

                        <div class="keepsake-message" style="margin-top: 12px;">
                            “{{ $wish->message }}”
                        </div>

                        @if(!empty($wish->reply))
                            <div class="keepsake-reply">
                                <strong>Lời đáp từ cặp đôi:</strong> {{ $wish->reply }}
                            </div>
                        @endif
                    </div>

                    @if(!empty($wish->signature_url))
                        <div class="keepsake-signature-wrap">
                            <img src="{{ $wish->signature_url }}" alt="Chữ ký {{ $wish->sender_name }}" class="keepsake-signature-img">
                        </div>
                    @endif
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--color-muted);">
                    Chưa có lời chúc nào trong sổ lưu bút.
                </div>
            @endforelse
        </div>

        <!-- Footer -->
        <div class="keepsake-footer">
            Tác phẩm lưu niệm được tạo tự động bởi nền tảng Mây Hẹn (mayhen.vn) • Gìn giữ mọi xúc cảm ngày hạnh phúc.
        </div>
    </div>

</body>
</html>

