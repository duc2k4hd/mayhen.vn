@extends('admin.layouts.master')

@section('title', 'Radar Sẵn Sàng Ngày Cưới')
@section('page_title', 'Radar Sẵn Sàng Ngày Cưới (Wedding Readiness Radar)')

@section('content')
    {{-- KPI Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Đám Cưới Sắp Tới ({{ $currentDays }} Ngày)
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                {{ number_format($kpis['total_upcoming'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #7A756E; margin-top: 4px;">Điểm sẵn sàng TB: <strong>{{ $kpis['avg_score'] }}%</strong></div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #C0392B;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Rủi Ro Cao (Cần Hỗ Trợ Gấp)
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #C0392B;">
                {{ number_format($kpis['high_risk_count'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #C0392B; margin-top: 4px; font-weight: 600;">Điểm < 60% — Thiếu mục cốt lõi</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #F39C12;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Cần Bổ Sung Chi Tiết
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #F39C12;">
                {{ number_format($kpis['medium_risk_count'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #F39C12; margin-top: 4px; font-weight: 600;">Điểm 60% - 79%</div>
        </div>

        <div class="mayhen_admin_card" style="margin-bottom: 0; padding: 18px 20px; border-left: 4px solid #27AE60;">
            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--mh-admin-muted); margin-bottom: 6px;">
                Hoàn Hảo — Sẵn Sàng 100%
            </div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #27AE60;">
                {{ number_format($kpis['ready_count'], 0, ',', '.') }}
            </div>
            <div style="font-size: 0.75rem; color: #27AE60; margin-top: 4px; font-weight: 600;">Đầy đủ mọi điều kiện</div>
        </div>
    </div>

    {{-- Bộ Lọc & Tìm Kiếm --}}
    <div class="mayhen_admin_card" style="margin-bottom: 20px;">
        <form method="GET" action="{{ route('admin.intelligence.radar') }}" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end;">
            <div style="flex: 1; min-width: 220px;">
                <label style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-muted); display: block; margin-bottom: 4px;">TÌM KIẾM ĐÁM CƯỚI</label>
                <input type="text" name="search" value="{{ $currentSearch }}"
                       placeholder="Tên cô dâu, chú rể, địa điểm..."
                       style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.875rem;">
            </div>

            <div style="min-width: 160px;">
                <label style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-muted); display: block; margin-bottom: 4px;">KHOẢNG THỜI GIAN</label>
                <select name="days" style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.875rem;">
                    <option value="7" {{ $currentDays == 7 ? 'selected' : '' }}>7 ngày tới (Gấp nhất)</option>
                    <option value="14" {{ $currentDays == 14 ? 'selected' : '' }}>14 ngày tới</option>
                    <option value="30" {{ $currentDays == 30 ? 'selected' : '' }}>30 ngày tới (Tiêu chuẩn)</option>
                    <option value="60" {{ $currentDays == 60 ? 'selected' : '' }}>60 ngày tới</option>
                </select>
            </div>

            <div style="min-width: 160px;">
                <label style="font-size: 0.75rem; font-weight: 600; color: var(--mh-admin-muted); display: block; margin-bottom: 4px;">MỨC ĐỘ RỦI RO</label>
                <select name="risk" style="width: 100%; padding: 8px 12px; border: 1px solid var(--mh-admin-border); border-radius: 6px; font-size: 0.875rem;">
                    <option value="">Tất cả mức độ</option>
                    <option value="red" {{ $currentRisk === 'red' ? 'selected' : '' }}>🔴 Rủi ro cao (< 60%)</option>
                    <option value="yellow" {{ $currentRisk === 'yellow' ? 'selected' : '' }}>🟡 Cần lưu ý (60% - 79%)</option>
                    <option value="green" {{ $currentRisk === 'green' ? 'selected' : '' }}>🟢 Hoàn hảo (>= 80%)</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="mayhen_admin_btn mayhen_admin_btn--primary" style="padding: 8px 16px;">Quét Radar</button>
                <a href="{{ route('admin.intelligence.radar') }}" class="mayhen_admin_btn mayhen_admin_btn--ghost" style="padding: 8px 16px;">Đặt lại</a>
            </div>
        </form>
    </div>

    {{-- Danh Sách Kết Quả Radar --}}
    <div class="mayhen_admin_card" style="padding: 0; overflow: hidden;">
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--mh-admin-border); display: flex; align-items: center; justify-content: space-between;">
            <span style="font-weight: 700; font-size: 0.875rem;">{{ $items->count() }} đám cưới được theo dõi</span>
        </div>

        @if($items->isEmpty())
            <div style="padding: 48px; text-align: center; color: var(--mh-admin-muted);">
                <div style="font-size: 2.2rem; margin-bottom: 12px;">🎉</div>
                <div style="font-weight: 600; font-size: 1rem; color: var(--mh-admin-charcoal);">Không tìm thấy đám cưới nào phù hợp</div>
                <div style="font-size: 0.85rem; margin-top: 4px;">Tất cả đám cưới trong phạm vi đã quét đều an toàn hoặc không có lịch cưới sắp tới.</div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="mayhen_admin_table">
                    <thead>
                        <tr>
                            <th>Đôi Uyên Ương & Ngày Cưới</th>
                            <th>Thời Gian Còn Lại</th>
                            <th>Điểm Sẵn Sàng (Radar)</th>
                            <th>Hạng Mục Cốt Lõi</th>
                            <th>Cảnh Báo Vận Hành</th>
                            <th style="text-align: right;">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            @php
                                $w = $item['wedding'];
                                $badgeColor = match($item['risk_level']) {
                                    'red' => '#C0392B',
                                    'yellow' => '#F39C12',
                                    'green' => '#27AE60',
                                };
                            @endphp
                            <tr>
                                <td>
                                    <div style="font-weight: 700; font-size: 0.9rem; color: var(--mh-admin-charcoal);">
                                        {{ $item['couple'] }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--mh-admin-muted); margin-top: 2px;">
                                        Tài khoản: {{ $w->user?->name ?? '—' }} ({{ $w->user?->email ?? '' }})
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; font-size: 0.85rem;">
                                        {{ $item['wedding_date'] ? $item['wedding_date']->format('d/m/Y') : '—' }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: {{ $item['days_remaining'] <= 7 ? '#C0392B; font-weight: 700;' : 'var(--mh-admin-muted)' }};">
                                        @if($item['days_remaining'] === 0)
                                            Hôm nay là ngày cưới!
                                        @elseif($item['days_remaining'] > 0)
                                            Còn {{ $item['days_remaining'] }} ngày nữa
                                        @else
                                            Đã qua {{ abs($item['days_remaining']) }} ngày
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 70px; height: 8px; background: #eee; border-radius: 4px; overflow: hidden;">
                                            <div style="width: {{ $item['score'] }}%; height: 100%; background: {{ $badgeColor }}; border-radius: 4px;"></div>
                                        </div>
                                        <span style="font-weight: 700; font-size: 0.85rem; color: {{ $badgeColor }};">
                                            {{ $item['score'] }}%
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 4px; font-size: 0.75rem;" title="Kiểm tra: Địa điểm | Ảnh bìa | Nhạc | Sức chứa | Xuất bản">
                                        <span style="color: {{ $item['checklist']['venue'] ? '#27AE60' : '#C0392B' }};" title="Địa điểm tiệc cưới">📍{{ $item['checklist']['venue'] ? '✓' : '✗' }}</span>
                                        <span style="color: {{ $item['checklist']['cover_photo'] ? '#27AE60' : '#C0392B' }};" title="Ảnh bìa đám cưới">🖼️{{ $item['checklist']['cover_photo'] ? '✓' : '✗' }}</span>
                                        <span style="color: {{ $item['checklist']['music'] ? '#27AE60' : '#F39C12' }};" title="Nhạc nền">🎵{{ $item['checklist']['music'] ? '✓' : '✗' }}</span>
                                        <span style="color: {{ $item['checklist']['capacity'] ? '#27AE60' : '#C0392B' }};" title="Sức chứa bàn tiệc">🪑{{ $item['checklist']['capacity'] ? '✓' : '✗' }}</span>
                                        <span style="color: {{ $item['checklist']['published'] ? '#27AE60' : '#C0392B' }};" title="Trạng thái xuất bản">🌐{{ $item['checklist']['published'] ? '✓' : '✗' }}</span>
                                    </div>
                                </td>
                                <td style="max-width: 280px;">
                                    @if(count($item['issues']) > 0)
                                        <ul style="margin: 0; padding-left: 16px; font-size: 0.75rem; color: #555;">
                                            @foreach($item['issues'] as $issue)
                                                <li style="color: {{ $issue['severity'] === 'critical' ? '#C0392B' : '#7A756E' }};">
                                                    <strong>{{ $issue['title'] }}</strong>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span style="color: #27AE60; font-size: 0.75rem; font-weight: 600;">✓ Hoàn hảo</span>
                                    @endif
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <div style="display: inline-flex; gap: 6px;">
                                        @if($item['primary_invitation'])
                                            <a href="{{ route('admin.invitations.show', $item['primary_invitation']->id) }}"
                                               class="mayhen_admin_btn mayhen_admin_btn--ghost"
                                               style="padding: 4px 8px; font-size: 0.75rem;"
                                               title="Xem thiệp cưới">
                                                Xem thiệp
                                            </a>
                                        @endif
                                        @if($w->user)
                                            <a href="{{ route('admin.users.show', $w->user->id) }}"
                                               class="mayhen_admin_btn mayhen_admin_btn--secondary"
                                               style="padding: 4px 8px; font-size: 0.75rem;"
                                               title="Hồ sơ khách hàng">
                                                Hồ sơ
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

