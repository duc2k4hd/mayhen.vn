@php
    $config = $config ?? (\App\Services\WeddingInteractiveEndingService::resolveInteractiveEndingConfig($invitation ?? null));
    $wedding = $invitation->wedding ?? null;
    $isEmbed = request()->boolean('embed') || !empty($isEmbed);
    $endingStyle = $config['ending_style'] ?? 'intimate_editorial';
    $wishes = $wishes ?? ($wedding ? \App\Models\WeddingWish::where('wedding_id', $wedding->id)->where('is_approved', true)->where('is_private', false)->orderByDesc('is_pinned')->orderByDesc('created_at')->take(10)->get() : collect());
@endphp

@if(!empty($config['enabled']))
<section class="mayhen_invitation_interactive_ending mayhen_invitation_ending--{{ $endingStyle }}" id="section-interactive-ending" data-mayhen-section="rsvp,wishes">
    <div class="mayhen_invitation_interactive_wrap">

        {{-- 10.1 BẢNG KÝ TÊN ĐIỆN TỬ (DIGITAL SIGNATURE CANVAS) --}}
        @if(!empty($config['signature_enabled']))
        <div class="mayhen_invitation_signature" id="signatureModule">
            <div class="mayhen_invitation_signature_header">
                <div class="mayhen_invitation_signature_badge">Kỷ Niệm Ngày Chung Đôi</div>
                <h3 class="mayhen_invitation_signature_title">{{ $config['signature_title'] ?? 'Sổ Ký Tên Điện Tử' }}</h3>
                @if(!empty($config['signature_subtitle']))
                    <p class="mayhen_invitation_signature_desc">{{ $config['signature_subtitle'] }}</p>
                @endif
            </div>

            {{-- Vùng Canvas Ký Tên Nghệ Thuật (Tuyệt đối KHÔNG chèn ảnh cặp đôi, dùng pure CSS Canvas) --}}
            <div class="mayhen_invitation_signature_board mayhen_invitation_signature_bg--{{ $config['signature_bg_style'] ?? 'stardust' }}">
                <div class="mayhen_invitation_signature_decor">✦ ✦ ✦</div>
                <canvas id="mayhenSignatureCanvas" class="mayhen_invitation_signature_canvas" width="600" height="240"></canvas>
                <div class="mayhen_invitation_signature_placeholder" id="canvasPlaceholder">
                    <span class="mayhen_invitation_signature_pen_icon">✍️</span>
                    <span>Dùng ngón tay hoặc chuột để ký tên hoặc viết lời chúc ngắn tại đây</span>
                </div>
            </div>

            {{-- Thanh Công Cụ Vẽ: Đổi màu mực & Nút Thao Tác --}}
            <div class="mayhen_invitation_signature_tools">
                <div class="mayhen_invitation_signature_palette">
                    <span class="mayhen_invitation_signature_palette_label">Màu mực:</span>
                    <div class="mayhen_invitation_signature_colors" id="paletteColors">
                        <button type="button" class="mayhen_invitation_color_btn active" data-color="#C5A880" style="background-color: #C5A880;" title="Vàng Hoàng Gia"></button>
                        <button type="button" class="mayhen_invitation_color_btn" data-color="#2C2C2C" style="background-color: #2C2C2C;" title="Than Chì"></button>
                        <button type="button" class="mayhen_invitation_color_btn" data-color="#1B2A38" style="background-color: #1B2A38;" title="Xanh Đêm"></button>
                        <button type="button" class="mayhen_invitation_color_btn" data-color="#85182A" style="background-color: #85182A;" title="Đỏ Rượu Vang"></button>
                    </div>
                </div>

                <div class="mayhen_invitation_signature_actions">
                    <button type="button" class="mayhen_invitation_sig_btn mayhen_invitation_sig_btn--clear" id="btnSignatureClear">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18m-2 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        Xóa nét vẽ
                    </button>
                    <button type="button" class="mayhen_invitation_sig_btn mayhen_invitation_sig_btn--save" id="btnSignatureSave">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        Lưu chữ ký
                    </button>
                </div>
            </div>

            <div class="mayhen_invitation_sig_status" id="signatureSaveStatus" style="display: none;"></div>
        </div>
        @endif

        {{-- 10.3 SỔ LƯU BÚT CHÚC PHÚC (GUESTBOOK / WISHES) --}}
        @if(!empty($config['wishes_enabled']))
        <div class="mayhen_invitation_wishes" id="wishesModule">
            <div class="mayhen_invitation_wishes_header">
                <div class="mayhen_invitation_wishes_badge">Gửi Lời Yêu Thương</div>
                <h3 class="mayhen_invitation_wishes_title">{{ $config['wishes_title'] ?? 'Sổ Lưu Bút Chúc Phúc' }}</h3>
                @if(!empty($config['wishes_subtitle']))
                    <p class="mayhen_invitation_wishes_desc">{{ $config['wishes_subtitle'] }}</p>
                @endif
            </div>

            {{-- Form Gửi Lời Chúc --}}
            <form class="mayhen_invitation_wish_form" id="invitationWishForm">
                @csrf
                <div class="mayhen_invitation_wish_field">
                    <label class="mayhen_invitation_wish_label">Tên của bạn <span class="required">*</span></label>
                    <input type="text" name="name" class="mayhen_invitation_wish_name_input" placeholder="Ví dụ: Bạn Minh & Gia đình" required maxlength="100">
                </div>

                <div class="mayhen_invitation_wish_field">
                    <label class="mayhen_invitation_wish_label">Lời chúc phúc <span class="required">*</span></label>
                    <textarea name="message" class="mayhen_invitation_wish_textarea" rows="3" placeholder="Chúc hai bạn trăm năm hạnh phúc, răng long đầu bạc..." required maxlength="500"></textarea>
                </div>

                @if(!empty($config['allow_private_wishes']))
                <div class="mayhen_invitation_wish_privacy">
                    <label class="mayhen_invitation_checkbox_label">
                        <input type="checkbox" name="is_private" value="1">
                        <span>Chỉ gửi riêng cho cô dâu &amp; chú rể (không hiển thị công khai)</span>
                    </label>
                </div>
                @endif

                <div class="mayhen_invitation_wish_submit_row">
                    <button type="submit" class="mayhen_invitation_wish_submit_btn" id="btnSubmitWish">
                        <span>Gửi Lời Chúc Phúc</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                    </button>
                </div>
            </form>

            {{-- Danh Sách Lời Chúc (Wish Wall) --}}
            <div class="mayhen_invitation_wish_list" id="wishListContainer">
                @forelse($wishes as $wish)
                    <div class="mayhen_invitation_wish_item {{ $wish->is_pinned ? 'is-pinned' : '' }}">
                        @if($wish->is_pinned)
                            <div class="mayhen_invitation_wish_pin_badge">✦ Lời chúc nổi bật</div>
                        @endif
                        <div class="mayhen_invitation_wish_item_head">
                            <span class="mayhen_invitation_wish_sender">{{ $wish->name }}</span>
                            <span class="mayhen_invitation_wish_time">{{ $wish->created_at ? $wish->created_at->diffForHumans() : '' }}</span>
                        </div>
                        <div class="mayhen_invitation_wish_item_body">
                            {{ $wish->message }}
                        </div>

                        {{-- Hiển thị chữ ký nếu có --}}
                        @if(!empty($wish->signature_url))
                            <div class="mayhen_invitation_wish_signature_preview">
                                <img src="{{ $wish->signature_url }}" alt="Chữ ký của {{ $wish->name }}" loading="lazy">
                            </div>
                        @endif

                        {{-- Phản hồi từ cặp đôi --}}
                        @if(!empty($wish->reply))
                            <div class="mayhen_invitation_wish_reply">
                                <div class="mayhen_invitation_wish_reply_title">Lời cảm ơn từ Cô Dâu &amp; Chú Rể:</div>
                                <div class="mayhen_invitation_wish_reply_content">{{ $wish->reply }}</div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="mayhen_invitation_wish_empty" id="wishEmptyState">
                        Hãy là người đầu tiên gửi lời chúc phúc yêu thương đến đôi uyên ương!
                    </div>
                @endforelse
            </div>
        </div>
        @endif

        {{-- 10.4 LỜI CẢM ƠN KẾT THIỆP (ENDING & THANK YOU) --}}
        <div class="mayhen_invitation_ending">
            {{-- Ảnh Kỷ Niệm Khép Lại Thiệp --}}
            @if(!empty($config['ending_photo_url']))
                <div class="mayhen_invitation_ending_photo_wrap">
                    <img src="{{ $config['ending_photo_url'] }}" alt="Kỷ niệm hôn lễ" class="mayhen_invitation_ending_photo" data-sync-target="hero_photo_url" loading="lazy">
                    <div class="mayhen_invitation_ending_monogram">✦</div>
                </div>
            @endif

            @if(!empty($config['ending_kicker']))
                <div class="mayhen_invitation_ending_kicker">{{ $config['ending_kicker'] }}</div>
            @endif

            <h2 class="mayhen_invitation_ending_title">{{ $config['ending_title'] ?? 'Cảm Ơn' }}</h2>

            <p class="mayhen_invitation_ending_text" data-sync-target="thank_you_message">
                {{ $config['thank_you_message'] ?? 'Sự hiện diện của Quý khách là niềm vinh hạnh cho gia đình chúng tôi.' }}
            </p>

            <div class="mayhen_invitation_ending_couple_name">
                <span data-sync-target="groom_name">{{ $wedding->groom_name ?? 'Chú Rể' }}</span>
                <span class="ampersand">&amp;</span>
                <span data-sync-target="bride_name">{{ $wedding->bride_name ?? 'Cô Dâu' }}</span>
            </div>

            <div class="mayhen_invitation_ending_brand">
                <span class="mayhen_invitation_brand_icon">✦</span>
                <span class="mayhen_invitation_brand_text">Mây Hẹn — Thiệp Cưới Nghệ Thuật</span>
            </div>
        </div>

    </div>
</section>

{{-- PURE VANILLA JAVASCRIPT CHO BẢNG VẼ CANVAS & SỔ LƯU BÚT --}}
<script>
(function() {
    'use strict';

    // 1. Logic Vẽ Trên Canvas
    const canvas = document.getElementById('mayhenSignatureCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        const placeholder = document.getElementById('canvasPlaceholder');
        const clearBtn = document.getElementById('btnSignatureClear');
        const saveBtn = document.getElementById('btnSignatureSave');
        const statusEl = document.getElementById('signatureSaveStatus');
        const colorBtns = document.querySelectorAll('.mayhen_invitation_color_btn');

        let isDrawing = false;
        let hasDrawn = false;
        let currentColor = '#C5A880';
        let lastX = 0;
        let lastY = 0;

        // Cân chỉnh DPI cho màn hình Retina
        function setupCanvasDpi() {
            const rect = canvas.getBoundingClientRect();
            const dpr = window.devicePixelRatio || 1;
            canvas.width = rect.width * dpr;
            canvas.height = rect.height * dpr;
            ctx.scale(dpr, dpr);
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.lineWidth = 2.5;
            ctx.strokeStyle = currentColor;
        }

        setTimeout(setupCanvasDpi, 100);
        window.addEventListener('resize', function() {
            // Chỉ chỉnh lại khi chưa vẽ gì để tránh mất nét
            if (!hasDrawn) {
                setupCanvasDpi();
            }
        });

        // Đổi màu mực
        colorBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                colorBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentColor = this.getAttribute('data-color') || '#C5A880';
                ctx.strokeStyle = currentColor;
            });
        });

        function getCoordinates(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: clientX - rect.left,
                y: clientY - rect.top
            };
        }

        function startDraw(e) {
            isDrawing = true;
            const coords = getCoordinates(e);
            lastX = coords.x;
            lastY = coords.y;
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            hasDrawn = true;
            const coords = getCoordinates(e);

            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(coords.x, coords.y);
            ctx.stroke();

            lastX = coords.x;
            lastY = coords.y;
        }

        function stopDraw() {
            isDrawing = false;
        }

        // Sự kiện chuột & cảm ứng
        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        window.addEventListener('mouseup', stopDraw);

        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        window.addEventListener('touchend', stopDraw);

        // Xóa bảng vẽ
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                const rect = canvas.getBoundingClientRect();
                ctx.clearRect(0, 0, rect.width, rect.height);
                hasDrawn = false;
                if (placeholder) {
                    placeholder.style.display = 'flex';
                }
                if (statusEl) statusEl.style.display = 'none';
            });
        }

        // Lưu chữ ký
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                if (!hasDrawn) {
                    alert('Bạn vui lòng ký tên hoặc viết lời chúc ngắn trước khi lưu nhé!');
                    return;
                }

                const guestNameInput = document.querySelector('.mayhen_invitation_wish_name_input');
                let guestName = guestNameInput ? guestNameInput.value.trim() : '';
                if (!guestName) {
                    guestName = prompt('Vui lòng nhập họ tên của bạn để lưu cùng chữ ký:') || '';
                }

                if (!guestName.trim()) {
                    alert('Cần có họ tên để ghi dấu chữ ký vào sổ lưu niệm.');
                    return;
                }

                saveBtn.disabled = true;
                saveBtn.textContent = 'Đang lưu chữ ký...';

                const dataUrl = canvas.toDataURL('image/png');
                const invitationId = '{{ $invitation->id ?? "" }}';
                const endpoint = '/api/thiep/' + invitationId + '/so-ky-ten/luu-chu-ky';

                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        guest_name: guestName,
                        signature_data: dataUrl
                    })
                })
                .then(r => r.json())
                .then(data => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '✓ Đã lưu chữ ký';
                    if (statusEl) {
                        statusEl.style.display = 'block';
                        statusEl.className = 'mayhen_invitation_sig_status ' + (data.success ? 'is-success' : 'is-error');
                        statusEl.textContent = data.message || (data.success ? 'Chữ ký của bạn đã được ghi lại trang trọng!' : 'Có lỗi xảy ra.');
                    }
                })
                .catch(() => {
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Lưu chữ ký';
                    if (statusEl) {
                        statusEl.style.display = 'block';
                        statusEl.className = 'mayhen_invitation_sig_status is-error';
                        statusEl.textContent = 'Không thể kết nối máy chủ. Vui lòng thử lại sau.';
                    }
                });
            });
        }
    }

    // 2. Logic Gửi Lời Chúc (Wish Submission)
    const wishForm = document.getElementById('invitationWishForm');
    if (wishForm) {
        wishForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const submitBtn = document.getElementById('btnSubmitWish');
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Đang gửi lời chúc...</span>';

            const formData = new FormData(wishForm);
            const slug = '{{ $invitation->slug ?? "" }}';
            const endpoint = '/thiep/' + slug + '/wishes';

            fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;

                if (data.success) {
                    alert('Cảm ơn bạn! Lời chúc phúc ấm áp đã được gửi thành công.');
                    wishForm.reset();

                    // Chèn lời chúc mới vào đầu danh sách nếu công khai
                    const list = document.getElementById('wishListContainer');
                    const emptyState = document.getElementById('wishEmptyState');
                    if (emptyState) emptyState.remove();

                    if (list && data.wish) {
                        const itemHtml = `
                            <div class="mayhen_invitation_wish_item">
                                <div class="mayhen_invitation_wish_item_head">
                                    <span class="mayhen_invitation_wish_sender">${data.wish.name}</span>
                                    <span class="mayhen_invitation_wish_time">Vừa xong</span>
                                </div>
                                <div class="mayhen_invitation_wish_item_body">${data.wish.message}</div>
                            </div>
                        `;
                        list.insertAdjacentHTML('afterbegin', itemHtml);
                    }
                } else {
                    alert(data.message || 'Không thể gửi lời chúc. Vui lòng thử lại.');
                }
            })
            .catch(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                alert('Có lỗi kết nối khi gửi lời chúc. Vui lòng kiểm tra lại mạng.');
            });
        });
    }
})();
</script>

<style>
/* Pure CSS BEM cho Section 10 Mây Hẹn */
.mayhen_invitation_interactive_ending {
    position: relative;
    padding: 5rem 1.5rem;
    background: #FAF8F5;
    color: #2C2C2C;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    overflow: hidden;
}

.mayhen_invitation_interactive_wrap {
    max-width: 680px;
    margin: 0 auto;
}

/* 10.1 Bảng ký tên */
.mayhen_invitation_signature {
    background: #FFFFFF;
    border: 1px solid rgba(197, 168, 128, 0.3);
    border-radius: 12px;
    padding: 2rem 1.5rem;
    margin-bottom: 3.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.mayhen_invitation_signature_header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.mayhen_invitation_signature_badge {
    display: inline-block;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #C5A880;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.mayhen_invitation_signature_title {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 1.6rem;
    color: #2C2C2C;
    margin: 0 0 0.5rem;
    font-weight: 600;
}

.mayhen_invitation_signature_desc {
    font-size: 0.9rem;
    color: #7A7A7A;
    margin: 0;
    line-height: 1.5;
}

.mayhen_invitation_signature_board {
    position: relative;
    width: 100%;
    height: 220px;
    border: 1.5px dashed #C5A880;
    border-radius: 8px;
    background: #FCFBF9;
    overflow: hidden;
    cursor: crosshair;
    touch-action: none;
}

.mayhen_invitation_signature_bg--stardust {
    background: radial-gradient(circle at 50% 50%, #FFFDF9 0%, #F5F0E6 100%);
}

.mayhen_invitation_signature_bg--wax_seal {
    background: linear-gradient(135deg, #FFFDF9 0%, #F7EFE3 100%);
}

.mayhen_invitation_signature_decor {
    position: absolute;
    top: 8px;
    right: 12px;
    font-size: 0.75rem;
    color: rgba(197, 168, 128, 0.4);
    pointer-events: none;
    letter-spacing: 0.2em;
}

.mayhen_invitation_signature_canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 2;
}

.mayhen_invitation_signature_placeholder {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: #A39D94;
    font-size: 0.85rem;
    pointer-events: none;
    text-align: center;
    padding: 1rem;
    z-index: 1;
}

.mayhen_invitation_signature_pen_icon {
    font-size: 1.4rem;
}

.mayhen_invitation_signature_tools {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.mayhen_invitation_signature_palette {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.mayhen_invitation_signature_palette_label {
    font-size: 0.85rem;
    color: #666;
}

.mayhen_invitation_signature_colors {
    display: flex;
    gap: 0.4rem;
}

.mayhen_invitation_color_btn {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid #FFFFFF;
    box-shadow: 0 0 0 1px #DDD;
    cursor: pointer;
    transition: transform 0.15s ease;
}

.mayhen_invitation_color_btn.active {
    transform: scale(1.2);
    box-shadow: 0 0 0 2px #C5A880;
}

.mayhen_invitation_signature_actions {
    display: flex;
    gap: 0.5rem;
}

.mayhen_invitation_sig_btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 0.9rem;
    font-size: 0.85rem;
    border-radius: 6px;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.2s ease;
    font-family: inherit;
}

.mayhen_invitation_sig_btn--clear {
    background: #F4F1EC;
    color: #666;
    border-color: #E2DDD5;
}

.mayhen_invitation_sig_btn--clear:hover {
    background: #EAE5DD;
}

.mayhen_invitation_sig_btn--save {
    background: #C5A880;
    color: #FFFFFF;
    font-weight: 500;
}

.mayhen_invitation_sig_btn--save:hover {
    background: #B4956B;
}

.mayhen_invitation_sig_status {
    margin-top: 0.8rem;
    padding: 0.6rem;
    border-radius: 6px;
    font-size: 0.85rem;
    text-align: center;
}

.mayhen_invitation_sig_status.is-success {
    background: #E8F5E9;
    color: #2E7D32;
}

.mayhen_invitation_sig_status.is-error {
    background: #FFEBEE;
    color: #C62828;
}

/* 10.3 Sổ lưu bút */
.mayhen_invitation_wishes {
    background: #FFFFFF;
    border: 1px solid rgba(197, 168, 128, 0.3);
    border-radius: 12px;
    padding: 2rem 1.5rem;
    margin-bottom: 3.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.mayhen_invitation_wishes_header {
    text-align: center;
    margin-bottom: 1.5rem;
}

.mayhen_invitation_wishes_badge {
    display: inline-block;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: #C5A880;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.mayhen_invitation_wishes_title {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 1.6rem;
    color: #2C2C2C;
    margin: 0 0 0.5rem;
    font-weight: 600;
}

.mayhen_invitation_wishes_desc {
    font-size: 0.9rem;
    color: #7A7A7A;
    margin: 0;
    line-height: 1.5;
}

.mayhen_invitation_wish_field {
    margin-bottom: 1rem;
}

.mayhen_invitation_wish_label {
    display: block;
    font-size: 0.85rem;
    font-weight: 500;
    color: #444;
    margin-bottom: 0.35rem;
}

.mayhen_invitation_wish_name_input,
.mayhen_invitation_wish_textarea {
    width: 100%;
    padding: 0.75rem 0.9rem;
    border: 1px solid #DDD8D0;
    border-radius: 6px;
    font-size: 0.9rem;
    font-family: inherit;
    background: #FAF8F5;
    transition: border-color 0.2s ease;
    box-sizing: border-box;
}

.mayhen_invitation_wish_name_input:focus,
.mayhen_invitation_wish_textarea:focus {
    outline: none;
    border-color: #C5A880;
    background: #FFFFFF;
}

.mayhen_invitation_wish_privacy {
    margin-bottom: 1rem;
    font-size: 0.85rem;
    color: #666;
}

.mayhen_invitation_checkbox_label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.mayhen_invitation_wish_submit_btn {
    width: 100%;
    padding: 0.85rem;
    background: #2C2C2C;
    color: #FFFFFF;
    border: none;
    border-radius: 6px;
    font-size: 0.95rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: background 0.2s ease;
    font-family: inherit;
}

.mayhen_invitation_wish_submit_btn:hover {
    background: #111111;
}

.mayhen_invitation_wish_list {
    margin-top: 2rem;
    border-top: 1px solid #F0ECE4;
    padding-top: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    max-height: 480px;
    overflow-y: auto;
}

.mayhen_invitation_wish_item {
    background: #FAF8F5;
    border: 1px solid #EFEAE1;
    border-radius: 8px;
    padding: 1rem 1.2rem;
    position: relative;
}

.mayhen_invitation_wish_item.is-pinned {
    background: #FFFDF9;
    border-color: #C5A880;
}

.mayhen_invitation_wish_pin_badge {
    display: inline-block;
    font-size: 0.7rem;
    color: #C5A880;
    font-weight: 600;
    letter-spacing: 0.05em;
    margin-bottom: 0.4rem;
}

.mayhen_invitation_wish_item_head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 0.4rem;
}

.mayhen_invitation_wish_sender {
    font-weight: 600;
    color: #2C2C2C;
    font-size: 0.9rem;
}

.mayhen_invitation_wish_time {
    font-size: 0.75rem;
    color: #999;
}

.mayhen_invitation_wish_item_body {
    font-size: 0.9rem;
    color: #555;
    line-height: 1.5;
    white-space: pre-line;
}

.mayhen_invitation_wish_signature_preview {
    margin-top: 0.6rem;
    max-width: 140px;
}

.mayhen_invitation_wish_signature_preview img {
    max-width: 100%;
    height: auto;
    filter: drop-shadow(0 1px 2px rgba(0,0,0,0.05));
}

.mayhen_invitation_wish_reply {
    margin-top: 0.75rem;
    padding: 0.6rem 0.8rem;
    background: #F4EFE6;
    border-left: 2px solid #C5A880;
    border-radius: 4px;
    font-size: 0.85rem;
}

.mayhen_invitation_wish_reply_title {
    font-weight: 600;
    color: #8C6D45;
    margin-bottom: 0.2rem;
    font-size: 0.8rem;
}

.mayhen_invitation_wish_empty {
    text-align: center;
    color: #999;
    font-size: 0.9rem;
    font-style: italic;
    padding: 1.5rem;
}

/* 10.4 Lời cảm ơn kết thiệp */
.mayhen_invitation_ending {
    text-align: center;
    padding: 2rem 1rem 0;
}

.mayhen_invitation_ending_photo_wrap {
    position: relative;
    width: 110px;
    height: 110px;
    margin: 0 auto 1.5rem;
    border-radius: 50%;
    padding: 4px;
    border: 1.5px solid #C5A880;
    box-shadow: 0 4px 15px rgba(197, 168, 128, 0.15);
}

.mayhen_invitation_ending_photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    display: block;
}

.mayhen_invitation_ending_monogram {
    position: absolute;
    bottom: -6px;
    right: 4px;
    width: 26px;
    height: 26px;
    background: #C5A880;
    color: #FFFFFF;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    border: 2px solid #FFFFFF;
}

.mayhen_invitation_ending_kicker {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.15em;
    color: #C5A880;
    margin-bottom: 0.5rem;
}

.mayhen_invitation_ending_title {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 2.2rem;
    color: #2C2C2C;
    margin: 0 0 1rem;
    font-weight: 600;
}

.mayhen_invitation_ending_text {
    font-size: 1rem;
    color: #666;
    line-height: 1.7;
    max-width: 520px;
    margin: 0 auto 1.8rem;
    font-style: italic;
}

.mayhen_invitation_ending_couple_name {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 1.4rem;
    color: #2C2C2C;
    font-weight: 600;
    margin-bottom: 2rem;
}

.mayhen_invitation_ending_couple_name .ampersand {
    font-style: italic;
    color: #C5A880;
    margin: 0 0.4rem;
}

.mayhen_invitation_ending_brand {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    color: #A0988A;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    border-top: 1px solid rgba(197, 168, 128, 0.2);
    padding-top: 1.5rem;
}

.mayhen_invitation_brand_icon {
    color: #C5A880;
}
</style>
@endif

