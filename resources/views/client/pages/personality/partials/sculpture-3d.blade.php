@php
    $sculptureKey = $sculptureKey ?? ($typeInfo['sculpture_key'] ?? 'architect_monolith');
    $primaryColor = $primaryColor ?? ($typeInfo['palette']['primary'] ?? '#1E4D3B');
    $accentColor = $accentColor ?? ($typeInfo['palette']['accent'] ?? '#CFA860');
    $labelCode = $labelCode ?? ($typeInfo['code'] ?? '3D');
    $canvasHeight = $canvasHeight ?? 240;
    $compact = $compact ?? false;
@endphp
<div class="mayhen_personality_3d_wrap" data-sculpture-3d
     data-sculpture-key="{{ $sculptureKey }}"
     data-primary="{{ $primaryColor }}"
     data-accent="{{ $accentColor }}">
    <div class="mayhen_personality_3d_canvas_box" data-layer-3d>
        <canvas style="width:100%; height:{{ $canvasHeight }}px; display:block; cursor:grab; touch-action:none;" aria-label="Tác phẩm 3D tương tác {{ $labelCode }}"></canvas>
        @if(! $compact)
            <div class="mayhen_personality_3d_controls">
                <span class="mayhen_personality_3d_hint">Kéo để xoay 360°</span>
                <div style="display:flex; gap:4px;">
                    <button type="button" data-3d-zoom-in class="mayhen_personality_3d_ctrl_btn" title="Phóng to" aria-label="Phóng to">+</button>
                    <button type="button" data-3d-zoom-out class="mayhen_personality_3d_ctrl_btn" title="Thu nhỏ" aria-label="Thu nhỏ">−</button>
                    <button type="button" data-3d-reset class="mayhen_personality_3d_ctrl_btn" title="Đặt lại góc nhìn" aria-label="Đặt lại góc nhìn">↺</button>
                </div>
            </div>
        @endif
    </div>
    @if(! empty($fallbackSvg))
        <div class="mayhen_personality_2d_fallback" data-layer-2d hidden>
            {!! $fallbackSvg !!}
        </div>
    @endif
</div>
