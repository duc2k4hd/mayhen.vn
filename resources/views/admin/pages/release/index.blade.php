@extends('admin.layouts.master')

@section('title', 'Kiểm Soát Phát Hành & Hoàn Tác (Release & Rollback Cockpit) — Mây Hẹn')
@section('page_title', 'Kiểm Soát Phát Hành & Hoàn Tác')

@section('content')
    {{-- Safe Mode Alert Banner --}}
    @if($isSafeModeActive)
        <div style="background: linear-gradient(135deg, #78281F, #922B21); color: #FFFFFF; padding: 20px 24px; border-radius: 12px; margin-bottom: 24px; box-shadow: 0 4px 16px rgba(146, 43, 33, 0.25); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                    🛡️
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: #FFFFFF;">HỆ THỐNG ĐANG Ở CHẾ ĐỘ PHÒNG VỆ KHẨN CẤP (SAFE MODE)</h3>
                    <p style="margin: 4px 0 0; font-size: 0.85rem; opacity: 0.9;">Các tác vụ nền nặng (AI Story, batch process) đang tạm ngắt để ưu tiên 100% tài nguyên cho việc tải thiệp và check-in ngày cưới.</p>
                </div>
            </div>
            @if(Auth::user()?->isSuperAdmin())
                <form action="{{ route('admin.release.toggle_safe_mode') }}" method="POST" onsubmit="return confirm('Xác nhận TẮT chế độ Safe Mode và khôi phục hoạt động bình thường?');">
                    @csrf
                    <input type="hidden" name="action" value="deactivate">
                    <button type="submit" class="mayhen_admin_btn" style="background: #FFFFFF; color: #922B21; font-weight: 700; border: none; padding: 10px 18px; border-radius: 8px; cursor: pointer;">
                        Hạ Cảnh Báo & Tắt Safe Mode
                    </button>
                </form>
            @endif
        </div>
    @endif

    {{-- Header Action & Quick Metrics Bar --}}
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 24px;">
        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 0.82rem; font-weight: 600; background: {{ $preflight['is_ready_for_release'] ? '#E8F8F5' : '#FDEDEC' }}; color: {{ $preflight['is_ready_for_release'] ? '#117864' : '#922B21' }}; border: 1px solid {{ $preflight['is_ready_for_release'] ? '#A3E4D7' : '#F5B7B1' }};">
                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: {{ $preflight['is_ready_for_release'] ? '#27AE60' : '#E74C3C' }};"></span>
                Preflight Sẵn Sàng: {{ $preflight['is_ready_for_release'] ? 'Đạt Chuẩn 100%' : 'Có Vấn Đề Hạ Tầng' }}
            </span>

            @php
                $activeKillSwitches = $flags->where('kill_switch_active', true)->count();
            @endphp
            @if($activeKillSwitches > 0)
                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 20px; font-size: 0.82rem; font-weight: 700; background: #FDEDEC; color: #922B21; border: 1px solid #F5B7B1;">
                    ⚡ {{ $activeKillSwitches }} Cờ Đang Bị Cắt Khẩn Cấp (Kill Switch)
                </span>
            @endif
        </div>

        <div style="display: flex; gap: 10px;">
            @if(Auth::user()?->isSuperAdmin() && !$isSafeModeActive)
                <form action="{{ route('admin.release.toggle_safe_mode') }}" method="POST" onsubmit="return confirm('Kích hoạt Chế Độ Phòng Vệ Khẩn Cấp? Các tác vụ nặng sẽ bị tạm khóa.');">
                    @csrf
                    <input type="hidden" name="action" value="activate">
                    <input type="hidden" name="reason" value="Quản trị viên kích hoạt phòng vệ khẩn cấp từ bảng điều khiển">
                    <button type="submit" class="mayhen_admin_btn" style="background: #922B21; color: #FFFFFF; font-weight: 600; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer;">
                        🛡️ Kích Hoạt Safe Mode Khẩn Cấp
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Preflight Checklist Cockpit --}}
    <div class="mayhen_admin_card" style="margin-bottom: 24px; padding: 20px 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 8px;">
            <div>
                <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                    1. Bảng Kiểm Tra Sẵn Sàng Nền Tảng (Preflight Readiness Cockpit)
                </h2>
                <p style="font-size: 0.8rem; color: var(--mh-admin-muted); margin: 4px 0 0;">
                    Tự động rà soát kết nối DB, động cơ Cache, thư mục Storage, hàng đợi Worker và an toàn môi trường trước mỗi đợt rollout.
                </p>
            </div>
            <span style="font-size: 0.75rem; color: #7A756E; font-family: monospace;">Kiểm tra lúc: {{ now()->format('H:i:s d/m/Y') }}</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
            @foreach($preflight['checks'] as $key => $check)
                <div style="background: #FAF8F5; border: 1px solid #ECE7E1; border-radius: 8px; padding: 14px 16px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="padding-right: 12px;">
                        <div style="font-size: 0.85rem; font-weight: 700; color: var(--mh-admin-charcoal);">
                            {{ $check['label'] }}
                        </div>
                        <div style="font-size: 0.75rem; color: #7A756E; margin-top: 3px;">
                            {{ $check['message'] }}
                            @if(!empty($check['latency_ms']))
                                &bull; <strong style="color: #27AE60;">{{ $check['latency_ms'] }} ms</strong>
                            @endif
                        </div>
                    </div>
                    <div>
                        @if($check['status'] === 'passed')
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: #E8F8F5; color: #27AE60; font-weight: 700; font-size: 0.85rem;">
                                ✓
                            </span>
                        @elseif($check['status'] === 'warning')
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: #FEF9E7; color: #F39C12; font-weight: 700; font-size: 0.85rem;">
                                !
                            </span>
                        @else
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: #FDEDEC; color: #E74C3C; font-weight: 700; font-size: 0.85rem;">
                                ✕
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Feature Flags Control Matrix --}}
    <div class="mayhen_admin_card" style="padding: 20px 24px;">
        <div style="margin-bottom: 20px;">
            <h2 style="font-size: 1.05rem; font-weight: 700; color: var(--mh-admin-charcoal); margin: 0;">
                2. Ma Trận Cờ Tính Năng & Cắt Khẩn Cấp (Feature Flags & Kill Switches)
            </h2>
            <p style="font-size: 0.8rem; color: var(--mh-admin-muted); margin: 4px 0 0;">
                Hệ thống phân luồng người dùng nhất quán (Sticky Rollout qua băm User ID) kết hợp bộ ngắt khẩn cấp từng tính năng.
            </p>
        </div>

        @php
            $groupedFlags = $flags->groupBy('category');
        @endphp

        @if($groupedFlags->isEmpty())
            <div style="text-align: center; padding: 40px 20px; color: var(--mh-admin-muted);">
                <p>Chưa có cờ tính năng nào trong cơ sở dữ liệu.</p>
            </div>
        @else
            @foreach($groupedFlags as $category => $categoryFlags)
                <div style="margin-bottom: 28px;">
                    <div style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--mh-admin-muted); margin-bottom: 12px; padding-bottom: 6px; border-bottom: 1px solid #ECE7E1; display: flex; align-items: center; gap: 8px;">
                        <span>Phân Nhóm: <strong>{{ strtoupper($category) }}</strong></span>
                        <span style="font-size: 0.72rem; padding: 2px 8px; border-radius: 10px; background: #EAE6DF; color: #4A463F;">{{ $categoryFlags->count() }} cờ</span>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 14px;">
                        @foreach($categoryFlags as $flag)
                            <div style="background: #FFFFFF; border: 1px solid {{ $flag->kill_switch_active ? '#F5B7B1' : '#E8E3DD' }}; border-left: 4px solid {{ $flag->kill_switch_active ? '#C0392B' : ($flag->is_enabled ? '#27AE60' : '#BDC3C7') }}; border-radius: 8px; padding: 18px 20px;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px;">
                                    <div style="flex: 1; min-width: 260px;">
                                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                            <span style="font-family: monospace; font-size: 0.88rem; font-weight: 700; color: var(--mh-admin-charcoal); background: #FAF8F5; padding: 3px 8px; border-radius: 4px; border: 1px solid #ECE7E1;">
                                                {{ $flag->key }}
                                            </span>
                                            <strong style="font-size: 0.95rem; color: var(--mh-admin-charcoal);">{{ $flag->name }}</strong>
                                            
                                            @if($flag->kill_switch_active)
                                                <span style="background: #FDEDEC; color: #922B21; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 4px; border: 1px solid #F5B7B1;">
                                                    ⚡ KILL SWITCH ĐANG BẬT
                                                </span>
                                            @elseif($flag->is_enabled)
                                                <span style="background: #E8F8F5; color: #117864; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 4px; border: 1px solid #A3E4D7;">
                                                    HOẠT ĐỘNG ({{ $flag->rollout_percentage }}%)
                                                </span>
                                            @else
                                                <span style="background: #EAECEE; color: #7F8C8D; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 4px;">
                                                    ĐÃ TẮT (0%)
                                                </span>
                                            @endif
                                        </div>

                                        <p style="font-size: 0.82rem; color: #5D5852; margin: 8px 0 10px; line-height: 1.45;">
                                            {{ $flag->description }}
                                        </p>

                                        {{-- Rollout Progress Bar --}}
                                        <div style="margin-top: 8px; max-width: 380px;">
                                            <div style="display: flex; justify-content: space-between; font-size: 0.72rem; color: #7A756E; margin-bottom: 4px;">
                                                <span>Tỷ lệ tiếp cận: <strong>{{ $flag->rollout_percentage }}%</strong></span>
                                                @if($flag->lastToggledBy)
                                                    <span>Chỉnh bởi: <em>{{ $flag->lastToggledBy->name }}</em></span>
                                                @endif
                                            </div>
                                            <div style="height: 6px; background: #ECE7E1; border-radius: 3px; overflow: hidden;">
                                                <div style="height: 100%; width: {{ $flag->kill_switch_active ? 0 : $flag->rollout_percentage }}%; background: {{ $flag->kill_switch_active ? '#C0392B' : ($flag->rollout_percentage == 100 ? '#27AE60' : '#F39C12') }}; border-radius: 3px;"></div>
                                            </div>
                                        </div>

                                        @if($flag->kill_switch_active)
                                            <div style="margin-top: 10px; font-size: 0.75rem; color: #922B21; background: #FDF2E9; padding: 6px 10px; border-radius: 4px;">
                                                ⚠️ Lý do cắt khẩn cấp: <strong>{{ $flag->kill_switch_reason }}</strong>
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Actions Panel --}}
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        @if(Auth::user()?->isAdmin())
                                            {{-- Toggle & Rollout Percentage Form --}}
                                            <form action="{{ route('admin.release.toggle_flag', $flag->id) }}" method="POST" style="display: flex; align-items: center; gap: 6px;">
                                                @csrf
                                                <select name="rollout_percentage" style="padding: 6px 8px; font-size: 0.8rem; border-radius: 6px; border: 1px solid #D1CCC6; background: #FFF;">
                                                    <option value="0" {{ $flag->rollout_percentage == 0 ? 'selected' : '' }}>0% (Tắt)</option>
                                                    <option value="10" {{ $flag->rollout_percentage == 10 ? 'selected' : '' }}>10% (Canary)</option>
                                                    <option value="25" {{ $flag->rollout_percentage == 25 ? 'selected' : '' }}>25% (Early)</option>
                                                    <option value="50" {{ $flag->rollout_percentage == 50 ? 'selected' : '' }}>50% (A/B Test)</option>
                                                    <option value="100" {{ $flag->rollout_percentage == 100 ? 'selected' : '' }}>100% (Toàn mạng)</option>
                                                </select>

                                                <input type="hidden" name="is_enabled" value="{{ $flag->is_enabled ? 1 : 1 }}">
                                                <button type="submit" class="mayhen_admin_btn" style="padding: 6px 12px; font-size: 0.8rem; font-weight: 600; background: #27AE60; color: #FFFFFF; border: none; border-radius: 6px; cursor: pointer;">
                                                    Lưu Cấu Hình
                                                </button>
                                            </form>

                                            {{-- Kill Switch Form --}}
                                            @if(!$flag->kill_switch_active)
                                                <form action="{{ route('admin.release.kill_switch', $flag->id) }}" method="POST" onsubmit="return confirm('CẢNH BÁO: Bạn chuẩn bị CẮT KHẨN CẤP tính năng {{ $flag->key }}. Toàn bộ người dùng sẽ bị ngừng sử dụng ngay lập tức?');" style="display: flex; align-items: center; gap: 4px;">
                                                    @csrf
                                                    <input type="hidden" name="kill_switch_reason" value="Quản trị viên kích hoạt Kill Switch khẩn cấp từ giao diện vận hành">
                                                    <button type="submit" class="mayhen_admin_btn" style="padding: 6px 12px; font-size: 0.8rem; font-weight: 600; background: #922B21; color: #FFFFFF; border: none; border-radius: 6px; cursor: pointer;">
                                                        ⚡ Kill Switch
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.release.reset_kill_switch', $flag->id) }}" method="POST" onsubmit="return confirm('Khôi phục hoạt động cho cờ {{ $flag->key }}?');">
                                                    @csrf
                                                    <button type="submit" class="mayhen_admin_btn" style="padding: 6px 12px; font-size: 0.8rem; font-weight: 600; background: #27AE60; color: #FFFFFF; border: none; border-radius: 6px; cursor: pointer;">
                                                        Khôi Phục Cờ
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
@endsection
