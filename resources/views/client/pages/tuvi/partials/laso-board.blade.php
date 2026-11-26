@php
    /**
     * Bảng Lá Số Tử Vi 4x4 Chuẩn Nam Phái Việt Nam (Tử Vi Đẩu Số Tân Biên)
     *
     * @var array<string, mixed> $chart
     * @var string $nickname
     * @var bool $showExportControls
     * @var bool $isDemo
     */
    $showExportControls = $showExportControls ?? true;
    $isDemo = $isDemo ?? false;
    $summary = $chart['summary'] ?? [];
    $birth = $chart['birth'] ?? [];
    $palacesByBranch = $chart['palaces_by_branch'] ?? [];
    $ageMu = (int) ($chart['age_mu'] ?? 1);
    $viewedYear = (int) ($chart['viewed_year'] ?? now('Asia/Ho_Chi_Minh')->year);
    $currentTieuHanBranch = $summary['current_tieu_han']['branch_index'] ?? null;
    $currentLuuNienBranch = $summary['current_luu_nien']['branch_index'] ?? null;

    $elementClass = static function (?string $element): string {
        return match ($element) {
            'Kim' => 'mayhen_tuvi_el_kim',
            'Mộc' => 'mayhen_tuvi_el_moc',
            'Thủy' => 'mayhen_tuvi_el_thuy',
            'Hỏa' => 'mayhen_tuvi_el_hoa',
            'Thổ' => 'mayhen_tuvi_el_tho',
            default => '',
        };
    };

    $tuHoaClass = static function (?string $code): string {
        return match ($code) {
            'hoa_loc' => 'mayhen_tuvi_tuhoa_loc',
            'hoa_quyen' => 'mayhen_tuvi_tuhoa_quyen',
            'hoa_khoa' => 'mayhen_tuvi_tuhoa_khoa',
            'hoa_ky' => 'mayhen_tuvi_tuhoa_ky',
            default => '',
        };
    };

    // Fallback tính Chủ Mệnh / Chủ Thân / Âm Dương Lý / Quan hệ Cục–Mệnh cho các lá số cũ đã lưu DB trước bản cập nhật
    $menhBranchIdx = (int) ($summary['menh_branch_index'] ?? 2);
    $yearBranchIdx = (int) ($birth['pillars']['year']['branch_index'] ?? 0);
    $isYangYear = (bool) ($birth['pillars']['year']['is_yang_stem'] ?? (($yearBranchIdx % 2) === 0));

    $chuMenhMap = [
        0 => 'Tham Lang', 1 => 'Cự Môn', 2 => 'Lộc Tồn', 3 => 'Văn Khúc',
        4 => 'Liêm Trinh', 5 => 'Vũ Khúc', 6 => 'Phá Quân', 7 => 'Vũ Khúc',
        8 => 'Liêm Trinh', 9 => 'Văn Khúc', 10 => 'Lộc Tồn', 11 => 'Cự Môn',
    ];
    $chuThanMap = [
        0 => 'Linh Tinh', 1 => 'Thiên Tướng', 2 => 'Thiên Lương', 3 => 'Thiên Đồng',
        4 => 'Văn Xương', 5 => 'Thiên Cơ', 6 => 'Hỏa Tinh', 7 => 'Thiên Tướng',
        8 => 'Thiên Lương', 9 => 'Thiên Đồng', 10 => 'Văn Xương', 11 => 'Thiên Cơ',
    ];

    $chuMenh = $summary['chu_menh'] ?? ($chuMenhMap[$menhBranchIdx] ?? 'Tham Lang');
    $chuThan = $summary['chu_than'] ?? ($chuThanMap[$yearBranchIdx] ?? 'Linh Tinh');
    $amDuongLy = $summary['am_duong_ly'] ?? (($isYangYear === (($menhBranchIdx % 2) === 0)) ? 'Âm Dương Thuận Lý' : 'Âm Dương Nghịch Lý');
    $cucMenhRelation = $summary['cuc_menh_relation'] ?? '';
    if ($cucMenhRelation === '') {
        $cEl = (string) ($summary['cuc']['element'] ?? '');
        $mEl = (string) ($summary['nap_am']['element'] ?? '');
        $gen = ['Kim' => 'Thủy', 'Thủy' => 'Mộc', 'Mộc' => 'Hỏa', 'Hỏa' => 'Thổ', 'Thổ' => 'Kim'];
        $ov = ['Kim' => 'Mộc', 'Mộc' => 'Thổ', 'Thổ' => 'Thủy', 'Thủy' => 'Hỏa', 'Hỏa' => 'Kim'];
        if ($cEl !== '' && $mEl !== '') {
            if ($cEl === $mEl) {
                $cucMenhRelation = 'Mệnh Cục Bình Hòa (Tỷ Hòa)';
            } elseif (($gen[$cEl] ?? '') === $mEl) {
                $cucMenhRelation = 'Cục sinh Mệnh (Rất thuận lợi)';
            } elseif (($gen[$mEl] ?? '') === $cEl) {
                $cucMenhRelation = 'Mệnh sinh Cục (Cần nỗ lực bù đắp)';
            } elseif (($ov[$mEl] ?? '') === $cEl) {
                $cucMenhRelation = 'Mệnh khắc Cục (Chủ động vượt khó)';
            } elseif (($ov[$cEl] ?? '') === $mEl) {
                $cucMenhRelation = 'Cục khắc Mệnh (Hoàn cảnh rèn bản lĩnh)';
            }
        }
    }

    $pillars = $birth['pillars'] ?? [];
    $tuTruParts = array_filter([
        ! empty($pillars['year']['label']) ? 'Năm ' . $pillars['year']['label'] : null,
        ! empty($pillars['month']['label']) ? 'Tháng ' . $pillars['month']['label'] : null,
        ! empty($pillars['day']['label']) ? 'Ngày ' . $pillars['day']['label'] : null,
        ! empty($pillars['hour']['label']) ? $pillars['hour']['label'] : null,
    ]);
    $tuTruText = implode(' · ', $tuTruParts);
@endphp

<div class="mayhen_tuvi_laso_shell" data-tuvi-board-scope>
    <div class="mayhen_tuvi_laso_toolbar">
        <div class="mayhen_tuvi_laso_hint_group">
            <span class="mayhen_tuvi_laso_legend_dot mayhen_tuvi_laso_legend_dot--selected">Cung đang chọn</span>
            <span class="mayhen_tuvi_laso_legend_dot mayhen_tuvi_laso_legend_dot--triad">Tam Hợp</span>
            <span class="mayhen_tuvi_laso_legend_dot mayhen_tuvi_laso_legend_dot--opposite">Xung Chiếu</span>
            <span class="mayhen_tuvi_laso_hint_text">Chạm vào bất kỳ cung nào để soi Tam Phương Tứ Chính</span>
        </div>

        @if($showExportControls)
            <div class="mayhen_tuvi_btn_row">
                <label class="mayhen_tuvi_laso_checkbox">
                    <input type="checkbox" data-tuvi-export-hide-pii checked>
                    <span>Ẩn tên &amp; ngày giờ sinh khi xuất ảnh</span>
                </label>
                <button type="button" class="mayhen_tuvi_btn_primary mayhen_tuvi_btn_sm" data-tuvi-export-png>
                    ↓ Xuất Ảnh Lá Số PNG (HiDPI)
                </button>
            </div>
        @endif
    </div>

    <div class="mayhen_tuvi_chart_board" role="grid" aria-label="Bảng Lá Số Tử Vi Đẩu Số 12 Cung">
        {{-- Lớp SVG vẽ đường Tam Phương Tứ Chính (Tam Hợp + Xung Chiếu) xuyên qua Thiên Bàn --}}
        <svg class="mayhen_tuvi_board_svg" data-tuvi-board-svg aria-hidden="true"></svg>

        @foreach($palacesByBranch as $p)
            @php
                $branchIdx = (int) ($p['branch_index'] ?? 0);
                $isCurrentTh = (bool) ($p['is_current_tieu_han'] ?? ($currentTieuHanBranch !== null && (int) $currentTieuHanBranch === $branchIdx));
                $isCurrentLn = (bool) ($p['is_current_luu_nien'] ?? ($currentLuuNienBranch !== null && (int) $currentLuuNienBranch === $branchIdx));

                $goodStars = [];
                $challengeStars = [];
                foreach (($p['aux_stars'] ?? []) as $aux) {
                    $nature = $aux['nature'] ?? 'trung';
                    if ($nature === 'thach_thuc') {
                        $challengeStars[] = $aux;
                    } else {
                        $goodStars[] = $aux;
                    }
                }

                $tuHoaGood = [];
                $tuHoaKy = [];
                foreach (($p['tu_hoa'] ?? []) as $th) {
                    if (($th['code'] ?? '') === 'hoa_ky') {
                        $tuHoaKy[] = $th;
                    } else {
                        $tuHoaGood[] = $th;
                    }
                }

                $tieuHanList = $p['tieu_han_branches'] ?? [];
                $tieuHanLabel = ! empty($tieuHanList) ? 'TH: ' . implode(',', $tieuHanList) : '';
            @endphp

            <button type="button"
                    class="mayhen_tuvi_palace_cell mayhen_tuvi_branch_{{ $branchIdx }} {{ ! empty($p['is_menh']) ? 'is-menh-cell' : '' }}"
                    data-branch-idx="{{ $branchIdx }}"
                    data-palace-code="{{ $p['code'] ?? '' }}">
                {{-- Hàng trên: Can Chi cung • Tên Cung + THÂN • Đại Hạn --}}
                <div class="mayhen_tuvi_cell_top">
                    <span class="mayhen_tuvi_cell_branch">{{ $p['can_chi'] ?? '' }}</span>
                    <span class="mayhen_tuvi_cell_name">
                        {{ $p['short_name'] ?? '' }}@if(! empty($p['is_than']))<span class="mayhen_tuvi_than_tag">THÂN</span>@endif
                    </span>
                    <span class="mayhen_tuvi_cell_daihan" title="Đại Hạn {{ $p['dai_han_start'] ?? 0 }}–{{ $p['dai_han_end'] ?? 0 }} tuổi">
                        {{ $p['dai_han_start'] ?? 0 }}
                    </span>
                </div>

                {{-- 14 Chính Tinh (màu Ngũ Hành + Miếu/Vượng/Đắc/Bình/Hãm) --}}
                <div class="mayhen_tuvi_cell_majors">
                    @forelse($p['major_stars'] ?? [] as $ms)
                        <div class="mayhen_tuvi_major_line {{ $elementClass($ms['element'] ?? null) }}">
                            {{ $ms['name'] ?? '' }}
                            <small class="mayhen_tuvi_brightness">({{ $ms['brightness'] ?? 'B' }})</small>
                        </div>
                    @empty
                        <div class="mayhen_tuvi_major_line mayhen_tuvi_vcd_label">Vô Chính Diệu</div>
                    @endforelse
                </div>

                {{-- Phụ Tinh chia 2 cột chuẩn Nam Phái: Trái = Cát Tinh & Tứ Hóa Cát | Phải = Sát Tinh, Hóa Kỵ & Lưu Niên --}}
                <div class="mayhen_tuvi_cell_aux_grid">
                    <div class="mayhen_tuvi_aux_col mayhen_tuvi_aux_col--left">
                        @foreach($tuHoaGood as $th)
                            <span class="mayhen_tuvi_tuhoa_pill {{ $tuHoaClass($th['code'] ?? '') }}">
                                {{ $th['name'] ?? '' }}
                            </span>
                        @endforeach
                        @foreach($goodStars as $aux)
                            <span class="mayhen_tuvi_aux_item mayhen_tuvi_aux_cat {{ $elementClass($aux['element'] ?? null) }}">
                                {{ $aux['name'] ?? '' }}
                            </span>
                        @endforeach
                        @if(! empty($p['ring_bac_si']))
                            <span class="mayhen_tuvi_aux_item mayhen_tuvi_aux_ring">
                                {{ $p['ring_bac_si'] }}
                            </span>
                        @endif
                    </div>

                    <div class="mayhen_tuvi_aux_col mayhen_tuvi_aux_col--right">
                        @foreach($tuHoaKy as $th)
                            <span class="mayhen_tuvi_tuhoa_pill {{ $tuHoaClass($th['code'] ?? '') }}">
                                {{ $th['name'] ?? '' }}
                            </span>
                        @endforeach
                        @foreach($challengeStars as $aux)
                            <span class="mayhen_tuvi_aux_item mayhen_tuvi_aux_sat">
                                {{ $aux['name'] ?? '' }}
                            </span>
                        @endforeach
                        @if(! empty($p['ring_thai_tue']))
                            <span class="mayhen_tuvi_aux_item mayhen_tuvi_aux_ring_tt">
                                {{ $p['ring_thai_tue'] }}
                            </span>
                        @endif
                        @foreach($p['luu_nien_stars'] ?? [] as $lnStar)
                            <span class="mayhen_tuvi_aux_item mayhen_tuvi_aux_luunien">
                                {{ $lnStar }}
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Hàng dưới: Tiểu Hạn / Lưu Niên • Vòng Tràng Sinh • Tuần / Triệt --}}
                <div class="mayhen_tuvi_cell_bottom">
                    <span class="mayhen_tuvi_cell_bottom_left">
                        @if($isCurrentTh)
                            <span class="mayhen_tuvi_han_pill mayhen_tuvi_han_pill--th" title="Tiểu Hạn năm {{ $viewedYear }}">T.H {{ $viewedYear }}</span>
                        @elseif($tieuHanLabel !== '')
                            <span class="mayhen_tuvi_cell_th_text">{{ $tieuHanLabel }}</span>
                        @endif
                        @if($isCurrentLn)
                            <span class="mayhen_tuvi_han_pill mayhen_tuvi_han_pill--ln" title="Lưu Niên Thái Tuế năm {{ $viewedYear }}">L.N</span>
                        @endif
                    </span>

                    <span class="mayhen_tuvi_cell_trangsinh">{{ $p['ring_trang_sinh'] ?? '' }}</span>

                    <span class="mayhen_tuvi_cell_bottom_right">
                        @if(! empty($p['has_tuan']))
                            <span class="mayhen_tuvi_tuan_triet_tag mayhen_tuvi_tuan_tag">TUẦN</span>
                        @endif
                        @if(! empty($p['has_triet']))
                            <span class="mayhen_tuvi_tuan_triet_tag mayhen_tuvi_triet_tag">TRIỆT</span>
                        @endif
                    </span>
                </div>
            </button>
        @endforeach

        {{-- Ô TRUNG TÂM THIÊN BÀN 2×2 --}}
        <div class="mayhen_tuvi_center_plaque">
            <div class="mayhen_tuvi_center_header">
                <span class="mayhen_tuvi_badge" style="margin: 0 auto 6px;">
                    {{ $isDemo ? 'LÁ SỐ MẪU · MÂY HẸN THIÊN MỆNH' : 'LÁ SỐ TỬ VI · MÂY HẸN THIÊN MỆNH' }}
                </span>
                <h2>{{ $nickname }}</h2>
                <div class="mayhen_tuvi_center_school">
                    {{ $chart['school_name'] ?? 'Nam Phái Việt Nam (Tử Vi Đẩu Số Tân Biên)' }}
                </div>
            </div>

            <div class="mayhen_tuvi_center_meta">
                <div><strong>Dương lịch:</strong> {{ $birth['solar']['label'] ?? '' }}</div>
                <div><strong>Âm lịch:</strong> {{ $birth['lunar']['label'] ?? '' }}</div>
                @if($tuTruText !== '')
                    <div class="mayhen_tuvi_center_meta_full">
                        <strong>Tứ Trụ:</strong> {{ $tuTruText }}
                        @if(! empty($pillars['hour']['range']))
                            ({{ $pillars['hour']['range'] }})
                        @endif
                    </div>
                @endif
                <div>
                    <strong>Âm Dương:</strong> {{ $chart['yin_yang_gender_label'] ?? '' }}
                </div>
                <div>
                    <strong>Thuận/Nghịch:</strong> {{ $amDuongLy }}
                </div>
                <div>
                    <strong>Bản Mệnh:</strong>
                    <span class="{{ $elementClass($summary['nap_am']['element'] ?? null) }}">
                        {{ $summary['nap_am']['name'] ?? '' }}
                    </span>
                </div>
                <div>
                    <strong>Ngũ Hành Cục:</strong>
                    <span class="{{ $elementClass($summary['cuc']['element'] ?? null) }}">
                        {{ $summary['cuc']['name'] ?? '' }}
                    </span>
                </div>
                @if($cucMenhRelation !== '')
                    <div class="mayhen_tuvi_center_meta_full">
                        <strong>Cục – Mệnh:</strong> {{ $cucMenhRelation }}
                    </div>
                @endif
                <div><strong>Chủ Mệnh:</strong> {{ $chuMenh }}</div>
                <div><strong>Chủ Thân:</strong> {{ $chuThan }}</div>
                <div><strong>Mệnh tại:</strong> {{ $summary['menh_branch_name'] ?? '' }}</div>
                <div><strong>Thân cư:</strong> {{ $summary['than_palace_name'] ?? '' }}</div>
                <div>
                    <strong>Năm xem:</strong> {{ $viewedYear }} ({{ $summary['current_luu_nien']['can_chi'] ?? '' }} • {{ $ageMu }} tuổi)
                </div>
                <div>
                    <strong>Tuần / Triệt:</strong>
                    {{ implode('–', $summary['tuan_branches'] ?? []) }} / {{ implode('–', $summary['triet_branches'] ?? []) }}
                </div>
            </div>

            <div class="mayhen_tuvi_element_legend" aria-label="Chú thích màu Ngũ Hành">
                <span class="mayhen_tuvi_el_kim">● Kim</span>
                <span class="mayhen_tuvi_el_moc">● Mộc</span>
                <span class="mayhen_tuvi_el_thuy">● Thủy</span>
                <span class="mayhen_tuvi_el_hoa">● Hỏa</span>
                <span class="mayhen_tuvi_el_tho">● Thổ</span>
            </div>
        </div>
    </div>

    <div class="mayhen_tuvi_card mayhen_tuvi_board_inspector" data-tuvi-board-inspector aria-live="polite"></div>
</div>
