"""
MÂY HẸN — VIRTUAL WEDDING WORLD 3D
Procedural PBR & Graphic Texture Generator (Runs in Blender 5.2 Python + NumPy)

Generates 100% local, clean, copyright-safe PNG textures for the W03 venue:
  - tex_starlight_velvet.png   (1024x1024) Starry blackout liner
  - tex_turf_albedo.png        (512x512)   Event artificial grass albedo
  - tex_turf_normal.png        (512x512)   Event artificial grass normal map
  - tex_carpet_slate.png       (512x512)   Blue-grey foyer & aisle felt carpet
  - tex_welcome_left.png       (512x896)   "LỄ TÂN HÔN" terracotta welcome board
  - tex_welcome_right.png      (512x1024)  "NHÀ CÓ HỶ" + endless mystic knot
  - tex_couple_canvas.png      (512x768)   Romantic stylized couple portrait art
  - tex_led_idle_slate.png     (1280x720)  Honest 16:9 LED screen idle slate
"""

import os
import math
import numpy as np
import bpy


TEX_DIR = r"d:\Project\Laravel\mayhen.vn\Blender\venues\royal-crimson-marquee\v1\textures"


# Clean 5x7 bitmap glyphs for crisp graphic signage without external font dependencies
GLYPHS_5X7 = {
    "A": ["01110", "10001", "10001", "11111", "10001", "10001", "10001"],
    "B": ["11110", "10001", "10001", "11110", "10001", "10001", "11110"],
    "C": ["01111", "10000", "10000", "10000", "10000", "10000", "01111"],
    "D": ["11110", "10001", "10001", "10001", "10001", "10001", "11110"],
    "E": ["11111", "10000", "10000", "11110", "10000", "10000", "11111"],
    "G": ["01111", "10000", "10000", "10011", "10001", "10001", "01111"],
    "H": ["10001", "10001", "10001", "11111", "10001", "10001", "10001"],
    "I": ["01110", "00100", "00100", "00100", "00100", "00100", "01110"],
    "K": ["10001", "10010", "10100", "11000", "10100", "10010", "10001"],
    "L": ["10000", "10000", "10000", "10000", "10000", "10000", "11111"],
    "M": ["10001", "11011", "10101", "10101", "10001", "10001", "10001"],
    "N": ["10001", "11001", "10101", "10011", "10001", "10001", "10001"],
    "O": ["01110", "10001", "10001", "10001", "10001", "10001", "01110"],
    "P": ["11110", "10001", "10001", "11110", "10000", "10000", "10000"],
    "Q": ["01110", "10001", "10001", "10001", "10101", "10010", "01101"],
    "R": ["11110", "10001", "10001", "11110", "10100", "10010", "10001"],
    "S": ["01111", "10000", "10000", "01110", "00001", "00001", "11110"],
    "T": ["11111", "00100", "00100", "00100", "00100", "00100", "00100"],
    "U": ["10001", "10001", "10001", "10001", "10001", "10001", "01110"],
    "V": ["10001", "10001", "10001", "10001", "10001", "01010", "00100"],
    "Y": ["10001", "10001", "01010", "00100", "00100", "00100", "00100"],
    "0": ["01110", "10001", "10011", "10101", "11001", "10001", "01110"],
    "1": ["00100", "01100", "00100", "00100", "00100", "00100", "01110"],
    "2": ["01110", "10001", "00001", "00110", "01000", "10000", "11111"],
    "3": ["11110", "00001", "00001", "01110", "00001", "00001", "11110"],
    "4": ["10010", "10010", "10010", "11111", "00010", "00010", "00010"],
    "5": ["11111", "10000", "10000", "11110", "00001", "00001", "11110"],
    "6": ["01110", "10000", "10000", "11110", "10001", "10001", "01110"],
    "7": ["11111", "00001", "00010", "00100", "01000", "01000", "01000"],
    "8": ["01110", "10001", "10001", "01110", "10001", "10001", "01110"],
    "9": ["01110", "10001", "10001", "01111", "00001", "00001", "01110"],
    ".": ["00000", "00000", "00000", "00000", "00000", "01100", "01100"],
    "-": ["00000", "00000", "00000", "11111", "00000", "00000", "00000"],
    "&": ["01100", "10010", "10100", "01000", "10101", "10010", "01101"],
    " ": ["00000", "00000", "00000", "00000", "00000", "00000", "00000"],
}


def save_rgba_png(filename, arr_rgba):
    """Saves an (H, W, 4) float32 [0..1] array (top-to-bottom row order) as PNG via Blender."""
    os.makedirs(TEX_DIR, exist_ok=True)
    h, w, _ = arr_rgba.shape
    # Blender image pixels start from bottom-left
    flipped = np.flipud(np.clip(arr_rgba, 0.0, 1.0)).astype(np.float32)
    img = bpy.data.images.new(name=filename, width=w, height=h, alpha=True)
    img.pixels.foreach_set(flipped.ravel())
    path = os.path.join(TEX_DIR, filename)
    img.filepath_raw = path
    img.file_format = "PNG"
    img.save()
    bpy.data.images.remove(img)
    print(f"SAVED_TEXTURE: {filename} ({w}x{h})")
    return path


def draw_rect(canvas, x0, y0, x1, y1, color):
    h, w, _ = canvas.shape
    xa, xb = max(0, int(x0)), min(w, int(x1))
    ya, yb = max(0, int(y0)), min(h, int(y1))
    if xa < xb and ya < yb:
        canvas[ya:yb, xa:xb, :3] = color[:3]


def draw_border(canvas, x0, y0, x1, y1, thickness, color):
    draw_rect(canvas, x0, y0, x1, y0 + thickness, color)
    draw_rect(canvas, x0, y1 - thickness, x1, y1, color)
    draw_rect(canvas, x0, y0, x0 + thickness, y1, color)
    draw_rect(canvas, x1 - thickness, y0, x1, y1, color)


def draw_text_centered(canvas, text, center_y, scale, color, letter_gap=2):
    text = text.upper()
    char_w = 5 * scale
    gap_px = letter_gap * scale
    total_w = len(text) * char_w + max(0, len(text) - 1) * gap_px
    start_x = (canvas.shape[1] - total_w) // 2
    for idx, ch in enumerate(text):
        glyph = GLYPHS_5X7.get(ch, GLYPHS_5X7[" "])
        gx = start_x + idx * (char_w + gap_px)
        for r_i, row in enumerate(glyph):
            for c_i, bit in enumerate(row):
                if bit == "1":
                    draw_rect(
                        canvas,
                        gx + c_i * scale,
                        center_y + r_i * scale,
                        gx + (c_i + 1) * scale,
                        center_y + (r_i + 1) * scale,
                        color,
                    )


def build_all_textures():
    rng = np.random.default_rng(20260929)

    # 1. Starlight Blackout Velvet (1024x1024)
    h, w = 1024, 1024
    yy, xx = np.mgrid[0:h, 0:w]
    drape_wave = 0.012 * np.sin(xx * (2.0 * math.pi / 64.0))
    velvet = np.zeros((h, w, 4), dtype=np.float32)
    velvet[:, :, 0] = 0.024 + drape_wave
    velvet[:, :, 1] = 0.026 + drape_wave
    velvet[:, :, 2] = 0.038 + drape_wave * 1.2
    velvet[:, :, 3] = 1.0
    # Sprinkle warm-white & royal-blue fiber-optic starlights
    for count, col_rgb, rad in [
        (520, (0.98, 0.94, 0.84), 1),
        (180, (0.42, 0.62, 1.00), 1),
        (95, (1.00, 0.97, 0.90), 2),
    ]:
        sy = rng.integers(4, h - 4, size=count)
        sx = rng.integers(4, w - 4, size=count)
        for dy in range(-rad, rad + 1):
            for dx in range(-rad, rad + 1):
                falloff = 1.0 / (1.0 + dx * dx + dy * dy)
                velvet[sy + dy, sx + dx, 0] = np.maximum(velvet[sy + dy, sx + dx, 0], col_rgb[0] * falloff)
                velvet[sy + dy, sx + dx, 1] = np.maximum(velvet[sy + dy, sx + dx, 1], col_rgb[1] * falloff)
                velvet[sy + dy, sx + dx, 2] = np.maximum(velvet[sy + dy, sx + dx, 2], col_rgb[2] * falloff)
    save_rgba_png("tex_starlight_velvet.png", velvet)

    # 2. Event Artificial Turf Albedo & Normal Map (512x512)
    th, tw = 512, 512
    noise_a = rng.normal(0.0, 1.0, (th, tw)).astype(np.float32)
    noise_b = np.roll(noise_a, 1, axis=0) * 0.5 + noise_a * 0.5
    turf = np.zeros((th, tw, 4), dtype=np.float32)
    turf[:, :, 0] = 0.13 + 0.025 * noise_b
    turf[:, :, 1] = 0.33 + 0.048 * noise_b
    turf[:, :, 2] = 0.14 + 0.022 * noise_b
    turf[:, :, 3] = 1.0
    save_rgba_png("tex_turf_albedo.png", turf)

    gx = np.roll(noise_b, -1, axis=1) - np.roll(noise_b, 1, axis=1)
    gy = np.roll(noise_b, -1, axis=0) - np.roll(noise_b, 1, axis=0)
    nx = -gx * 0.35
    ny = -gy * 0.35
    nz = np.ones_like(nx)
    norm_len = np.sqrt(nx * nx + ny * ny + nz * nz)
    turf_n = np.zeros((th, tw, 4), dtype=np.float32)
    turf_n[:, :, 0] = (nx / norm_len) * 0.5 + 0.5
    turf_n[:, :, 1] = (ny / norm_len) * 0.5 + 0.5
    turf_n[:, :, 2] = (nz / norm_len) * 0.5 + 0.5
    turf_n[:, :, 3] = 1.0
    save_rgba_png("tex_turf_normal.png", turf_n)

    # 3. Blue-Grey Event Felt Carpet (512x512)
    ch, cw = 512, 512
    cy, cx = np.mgrid[0:ch, 0:cw]
    felt = rng.normal(0.0, 0.016, (ch, cw)).astype(np.float32)
    weave = 0.008 * (np.sin(cx * 0.35) + np.cos(cy * 0.35))
    carpet = np.zeros((ch, cw, 4), dtype=np.float32)
    carpet[:, :, 0] = 0.34 + felt + weave
    carpet[:, :, 1] = 0.39 + felt + weave
    carpet[:, :, 2] = 0.47 + felt + weave
    carpet[:, :, 3] = 1.0
    save_rgba_png("tex_carpet_slate.png", carpet)

    # 4. Left Welcome Sign ("LE TAN HON / CAO NHAN & TRAN THAO / 29.09.2026")
    wl = np.zeros((896, 512, 4), dtype=np.float32)
    wy, wx = np.mgrid[0:896, 0:512]
    grad = 1.0 - 0.16 * (wy / 896.0)
    wl[:, :, 0] = 0.80 * grad
    wl[:, :, 1] = 0.36 * grad
    wl[:, :, 2] = 0.31 * grad
    wl[:, :, 3] = 1.0
    ivory = (0.98, 0.95, 0.90)
    draw_border(wl, 22, 22, 490, 874, 3, ivory)
    draw_text_centered(wl, "LE TAN HON", 180, 5, ivory, 2)
    draw_text_centered(wl, "CAO NHAN", 320, 6, ivory, 2)
    draw_text_centered(wl, "&", 405, 5, ivory, 2)
    draw_text_centered(wl, "TRAN THAO", 480, 6, ivory, 2)
    draw_rect(wl, 140, 585, 372, 588, ivory)
    draw_text_centered(wl, "29.09.2026", 630, 5, ivory, 2)
    draw_text_centered(wl, "19.08 BINH NGO", 705, 3, ivory, 2)
    save_rgba_png("tex_welcome_left.png", wl)

    # 5. Right Taller Welcome Sign ("NHA CO HY" + Endless Mystic Knot)
    wr = np.zeros((1024, 512, 4), dtype=np.float32)
    ry, rx = np.mgrid[0:1024, 0:512]
    rgrad = 1.0 - 0.18 * (ry / 1024.0)
    wr[:, :, 0] = 0.82 * rgrad
    wr[:, :, 1] = 0.37 * rgrad
    wr[:, :, 2] = 0.32 * rgrad
    wr[:, :, 3] = 1.0
    draw_border(wr, 22, 22, 490, 1002, 3, ivory)
    draw_rect(wr, 198, 22, 203, 520, ivory)  # Hanging cord line matching f05!
    draw_text_centered(wr, "NHA", 150, 7, ivory, 2)
    draw_text_centered(wr, "CO", 240, 7, ivory, 2)
    draw_text_centered(wr, "HY", 330, 7, ivory, 2)
    # Diamond lattice endless knot centered around (200, 620)
    kx, ky = 200, 625
    for offset in [-54, -18, 18, 54]:
        for t in range(-85, 86):
            for w_px in range(-4, 5):
                px1 = kx + t + offset // 2
                py1 = ky + t - offset // 2 + w_px
                px2 = kx + t - offset // 2
                py2 = ky - t - offset // 2 + w_px
                if 40 <= px1 < 470 and 460 <= py1 < 800:
                    wr[py1, px1, :3] = ivory
                if 40 <= px2 < 470 and 460 <= py2 < 800:
                    wr[py2, px2, :3] = ivory
    # Tassel below knot
    draw_rect(wr, kx - 3, 745, kx + 4, 835, ivory)
    draw_rect(wr, kx - 12, 835, kx + 13, 915, ivory)
    save_rgba_png("tex_welcome_right.png", wr)

    # 6. Real Wedding Couple Photographs (Loaded from local Mây Hẹn wedding photo assets!)
    PHOTO_SRC_DIR = r"d:\Project\Laravel\mayhen.vn\resources\views\client\invitations\vom-troi-may-ao-cuoi\assets\images"
    GATE_REF_PATH = r"d:\Project\Laravel\mayhen.vn\docs\virtual-wedding-3d\references\dragon_phoenix_gate_ref.jpg"

    def load_image_top_down(filepath):
        """Loads an image via Blender and returns (H, W, 4) float32 top-to-bottom array."""
        b_img = bpy.data.images.load(filepath)
        w_i, h_i = b_img.size[0], b_img.size[1]
        raw = np.empty(w_i * h_i * 4, dtype=np.float32)
        b_img.pixels.foreach_get(raw)
        bpy.data.images.remove(b_img)
        arr = raw.reshape((h_i, w_i, 4))
        return np.flipud(arr).copy()

    def resize_nn(arr, out_h, out_w):
        in_h, in_w, _ = arr.shape
        yi = np.clip((np.arange(out_h) * in_h // out_h), 0, in_h - 1)
        xi = np.clip((np.arange(out_w) * in_w // out_w), 0, in_w - 1)
        return arr[yi[:, None], xi[None, :], :].copy()

    def frame_wedding_photo(src_arr, out_h, out_w, landscape=False):
        """Frames a real wedding photograph with a luxury gallery mat and gold border."""
        in_h, in_w, _ = src_arr.shape
        target_aspect = out_w / float(out_h)
        in_aspect = in_w / float(in_h)
        if in_aspect > target_aspect:
            new_w = int(in_h * target_aspect)
            x0 = (in_w - new_w) // 2
            cropped = src_arr[:, x0:x0 + new_w, :]
        else:
            new_h = int(in_w / target_aspect)
            y0 = max(0, int((in_h - new_h) * 0.25))
            cropped = src_arr[y0:y0 + new_h, :, :]
        out = resize_nn(cropped, out_h, out_w)
        # Subtle warm contrast & vibrancy boost for wedding lighting
        out[:, :, :3] = np.clip((out[:, :, :3] - 0.5) * 1.08 + 0.52, 0.0, 1.0)
        out[:, :, 3] = 1.0
        # Luxury ivory inner mat + 24K champagne gold border
        draw_border(out, 0, 0, out_w, out_h, 14, (0.85, 0.71, 0.42))
        draw_border(out, 14, 14, out_w - 14, out_h - 14, 10, (0.98, 0.96, 0.92))
        draw_border(out, 24, 24, out_w - 24, out_h - 24, 3, (0.78, 0.62, 0.34))
        return out

    photo_map = [
        ("tex_couple_canvas.png", "hero-couple.webp", 960, 640, False),
        ("tex_wedding_photo_01.png", "ceremony-couple.webp", 1080, 720, False),
        ("tex_wedding_photo_02.png", "save-date.webp", 1080, 720, False),
        ("tex_wedding_photo_03.png", "gallery-03.webp", 720, 1080, True),
        ("tex_wedding_photo_04.png", "envelope-couple.webp", 1080, 720, False),
        ("tex_wedding_photo_05.png", "gallery-02.webp", 1080, 720, False),
        ("tex_wedding_photo_06.png", "footer-couple.webp", 1080, 720, False),
    ]
    for out_name, src_name, oh, ow, is_land in photo_map:
        src_path = os.path.join(PHOTO_SRC_DIR, src_name)
        if os.path.exists(src_path):
            raw_img = load_image_top_down(src_path)
            framed = frame_wedding_photo(raw_img, oh, ow, landscape=is_land)
            save_rgba_png(out_name, framed)

    # 7. Honest 16:9 LED Screen Idle Slate (1280x720)
    # "CHƯƠNG TRÌNH TRỰC TIẾP CHƯA BẮT ĐẦU" (Zero fake LIVE badge!)
    led = np.zeros((720, 1280, 4), dtype=np.float32)
    ly, lx = np.mgrid[0:720, 0:1280]
    radial = np.sqrt(((lx - 640) / 640.0) ** 2 + ((ly - 360) / 360.0) ** 2)
    vignette = np.clip(1.0 - 0.45 * radial, 0.35, 1.0)
    led[:, :, 0] = 0.18 * vignette
    led[:, :, 1] = 0.025 * vignette
    led[:, :, 2] = 0.045 * vignette
    led[:, :, 3] = 1.0
    gold = (0.90, 0.76, 0.48)
    soft_cream = (0.98, 0.95, 0.90)
    muted = (0.82, 0.72, 0.58)
    draw_border(led, 36, 36, 1244, 684, 5, gold)
    draw_border(led, 50, 50, 1230, 670, 2, muted)
    draw_text_centered(led, "LE TAN HON - CAO NHAN & TRAN THAO", 165, 4, gold, 2)
    draw_rect(led, 380, 228, 900, 232, gold)
    draw_text_centered(led, "CHUONG TRINH TRUC TIEP", 295, 6, soft_cream, 2)
    draw_text_centered(led, "CHUA BAT DAU", 380, 6, soft_cream, 2)
    draw_text_centered(led, "SAN SANG KET NOI TIN HIEU - 29.09.2026", 505, 4, muted, 2)
    save_rgba_png("tex_led_idle_slate.png", led)

    # 8. Royal Deep Crimson Carpet Runner with White & Gold Ornamental Border (1024x1024)
    # Rich royal red in sRGB -> stays vivid deep crimson under Cycles & WebGL lighting (never pink!)
    rc_h, rc_w = 1024, 1024
    ry_g, rx_g = np.mgrid[0:rc_h, 0:rc_w]
    rc_noise = rng.normal(0.0, 0.012, (rc_h, rc_w)).astype(np.float32)
    rc = np.zeros((rc_h, rc_w, 4), dtype=np.float32)
    rc[:, :, 0] = 0.46 + rc_noise
    rc[:, :, 1] = 0.016 + np.abs(rc_noise) * 0.2
    rc[:, :, 2] = 0.032 + np.abs(rc_noise) * 0.2
    rc[:, :, 3] = 1.0
    ivory_b = (0.94, 0.91, 0.85)
    gold_b = (0.84, 0.68, 0.34)
    crimson_b = (0.38, 0.014, 0.028)
    for bx0, bx1 in [(38, 118), (906, 986)]:
        draw_rect(rc, bx0, 0, bx1, rc_h, ivory_b)
        draw_rect(rc, bx0 + 10, 0, bx1 - 10, rc_h, crimson_b)
        draw_rect(rc, bx0 + 20, 0, bx1 - 20, rc_h, gold_b)
        for ky in range(0, rc_h, 32):
            draw_rect(rc, bx0 + 14, ky + 6, bx1 - 14, ky + 14, ivory_b)
            draw_rect(rc, bx0 + 24, ky + 18, bx1 - 24, ky + 26, crimson_b)
    save_rgba_png("tex_red_carpet_royal.png", rc)

    # 9. Deep Royal Burgundy Draped Velvet Curtain Texture (1024x1024)
    bv_h, bv_w = 1024, 1024
    by_g, bx_g = np.mgrid[0:bv_h, 0:bv_w]
    pleat = 0.5 + 0.5 * np.sin(bx_g * (2.0 * math.pi / 48.0))
    sub_pleat = 0.5 + 0.5 * np.cos(bx_g * (2.0 * math.pi / 19.0) + 0.7)
    sheen = 0.22 * (pleat ** 1.6) + 0.08 * sub_pleat
    v_grad = 1.0 - 0.18 * (by_g / float(bv_h))
    bv = np.zeros((bv_h, bv_w, 4), dtype=np.float32)
    bv[:, :, 0] = np.clip((0.24 + sheen) * v_grad, 0.06, 0.54)
    bv[:, :, 1] = np.clip((0.008 + sheen * 0.04) * v_grad, 0.004, 0.06)
    bv[:, :, 2] = np.clip((0.024 + sheen * 0.07) * v_grad, 0.01, 0.10)
    bv[:, :, 3] = 1.0
    save_rgba_png("tex_velvet_burgundy.png", bv)

    # 10. QR Check-In & Wedding Gift Acrylic Standee Texture (800x1200)
    # For the Quầy Lễ Tân Quét Mã QR Check-In
    qr_img = np.zeros((1200, 800, 4), dtype=np.float32)
    qy, qx = np.mgrid[0:1200, 0:800]
    q_grad = 1.0 - 0.22 * (qy / 1200.0)
    # Royal Burgundy & Gold Header + Ivory Card Body
    qr_img[:, :, 0] = 0.44 * q_grad
    qr_img[:, :, 1] = 0.04 * q_grad
    qr_img[:, :, 2] = 0.09 * q_grad
    qr_img[:, :, 3] = 1.0
    draw_border(qr_img, 18, 18, 782, 1182, 10, (0.88, 0.73, 0.42))
    draw_border(qr_img, 34, 34, 766, 1166, 3, (0.98, 0.94, 0.86))
    draw_text_centered(qr_img, "MAY HEN - CONG DON KHACH", 72, 4, (0.92, 0.78, 0.48), 2)
    draw_text_centered(qr_img, "QUET MA QR CHECK-IN", 135, 5, (1.0, 0.97, 0.92), 2)
    draw_text_centered(qr_img, "& MUNG CUOI TRUC TUYEN", 198, 4, (0.95, 0.82, 0.54), 2)
    # White QR Card Box
    draw_rect(qr_img, 130, 270, 670, 810, (0.99, 0.98, 0.96))
    draw_border(qr_img, 130, 270, 670, 810, 8, (0.85, 0.69, 0.36))
    # Deterministic High-Contrast 29x29 QR Code Matrix inside (172..628, 312..768)
    qr_n = 29
    cell_px = 15  # 29 * 15 = 435px
    qx0, qy0 = (800 - qr_n * cell_px) // 2, 270 + (540 - qr_n * cell_px) // 2
    qr_grid = rng.integers(0, 2, size=(qr_n, qr_n))
    # Clear finder pattern zones
    qr_grid[0:8, 0:8] = 0
    qr_grid[0:8, qr_n - 8:qr_n] = 0
    qr_grid[qr_n - 8:qr_n, 0:8] = 0
    for fy, fx in [(0, 0), (0, qr_n - 7), (qr_n - 7, 0)]:
        qr_grid[fy:fy + 7, fx:fx + 7] = 1
        qr_grid[fy + 1:fy + 6, fx + 1:fx + 6] = 0
        qr_grid[fy + 2:fy + 5, fx + 2:fx + 5] = 1
    # Alignment pattern at bottom-right (20..25, 20..25)
    qr_grid[20:25, 20:25] = 1
    qr_grid[21:24, 21:24] = 0
    qr_grid[22, 22] = 1
    # Timing patterns
    for ti in range(8, qr_n - 8):
        qr_grid[6, ti] = (ti + 1) % 2
        qr_grid[ti, 6] = (ti + 1) % 2
    dark_burg = (0.14, 0.02, 0.04)
    for r_i in range(qr_n):
        for c_i in range(qr_n):
            if qr_grid[r_i, c_i] == 1:
                draw_rect(
                    qr_img,
                    qx0 + c_i * cell_px,
                    qy0 + r_i * cell_px,
                    qx0 + (c_i + 1) * cell_px,
                    qy0 + (r_i + 1) * cell_px,
                    dark_burg,
                )
    # Center monogram badge inside QR code
    draw_rect(qr_img, 362, 502, 438, 578, (0.58, 0.05, 0.11))
    draw_border(qr_img, 362, 502, 438, 578, 3, (0.92, 0.78, 0.46))
    draw_text_centered(qr_img, "NT", 522, 5, (1.0, 0.97, 0.90), 1)
    # Couple Name & Instructions below QR
    draw_text_centered(qr_img, "CAO NHAN & TRAN THAO", 865, 5, (1.0, 0.96, 0.90), 2)
    draw_text_centered(qr_img, "29.09.2026 - ROYAL CRIMSON", 935, 3, (0.92, 0.78, 0.48), 2)
    draw_rect(qr_img, 140, 990, 660, 993, (0.85, 0.70, 0.40))
    draw_text_centered(qr_img, "1. QUET MA NHAN SO BAN TIEC", 1025, 3, (0.98, 0.94, 0.88), 2)
    draw_text_centered(qr_img, "2. GUI LOI CHUC & MUNG CUOI", 1080, 3, (0.98, 0.94, 0.88), 2)
    save_rgba_png("tex_qr_checkin_standee.png", qr_img)

    # 11. Exact Dragon & Phoenix Entrance Gate RGBA Cutout + 3D Relief Displacement Map
    # Built directly from dragon_phoenix_gate_ref.jpg so the Entrance Gate matches the user's photo 100%!
    if os.path.exists(GATE_REF_PATH):
        gate_raw = load_image_top_down(GATE_REF_PATH)
        gh, gw, _ = gate_raw.shape
        # Upscale 2x for crisp 2048x868 rendering
        gate_hi = resize_nn(gate_raw, gh * 2, gw * 2)
        Hh, Hw, _ = gate_hi.shape
        gy_n, gx_n = np.mgrid[0:Hh, 0:Hw]
        yn = gy_n / float(Hh)
        xn = gx_n / float(Hw)

        # Enhance color richness: pure ivory white on Dragon & Phoenix, deep royal burgundy on curtains & roses
        r_c = gate_hi[:, :, 0]
        g_c = gate_hi[:, :, 1]
        b_c = gate_hi[:, :, 2]
        lum = 0.299 * r_c + 0.587 * g_c + 0.114 * b_c
        redness = np.clip(r_c - 0.5 * (g_c + b_c), 0.0, 1.0)

        # 1) Cut out sky/white marquee roof above the burgundy curtain while preserving 100% of White Dragon & Phoenix heads/wings!
        is_white_sculpt = (lum > 0.52) & (redness < 0.16)
        is_burgundy = (redness > 0.10) | (lum < 0.28)
        curtain_top_L = 0.025 + 0.045 * np.clip(xn / 0.305, 0.0, 1.0)
        sky_mask = (xn < 0.305) & (yn < curtain_top_L) & (~is_burgundy)
        # Cleanly remove ONLY dark truss bars or saturated blue sky above the curtain while keeping 100% of White Dragon & Phoenix heads/wings!
        is_blue_sky = (b_c - r_c > 0.065) & (redness < 0.04)
        is_dark_truss = (lum < 0.38) & (redness < 0.06) & (~is_white_sculpt)
        sky_mask |= (xn >= 0.305) & (xn < 0.342) & (yn < 0.048) & (~is_burgundy) & (is_blue_sky | is_dark_truss | (lum > 0.72))
        sky_mask |= (xn >= 0.425) & (xn <= 0.520) & (yn < 0.095) & (~is_burgundy) & (~is_white_sculpt) & ((lum < 0.55) | is_blue_sky)
        sky_mask |= (xn > 0.438) & (xn < 0.508) & (yn < 0.075) & (~is_burgundy)
        # Clean V-gap between Phoenix head crest (<=0.542) and raised Phoenix wing tip (>=0.596) + truss bar above wing
        sky_mask |= (xn >= 0.542) & (xn <= 0.596) & (yn < 0.082) & (~is_burgundy)
        sky_mask |= (xn > 0.520) & (xn <= 0.675) & (yn < 0.045) & (is_blue_sky | is_dark_truss)
        curtain_top_R = 0.068 - 0.015 * np.clip((xn - 0.675) / 0.325, 0.0, 1.0)
        sky_mask |= (xn > 0.675) & (yn < curtain_top_R) & (~is_burgundy)
        sky_mask |= (xn > 0.87) & (yn < 0.075) & (~is_burgundy)
        sky_mask |= (xn > 0.978) & (yn > 0.55) & (lum > 0.42)

        # 2) One continuous smooth parabolic/Roman archway cutout (zero rectangular stair-steps!)
        arch_cx = 0.488
        dy_arch = np.clip((yn - 0.265) / 0.40, 0.0, 1.0)
        arch_half_w = 0.082 * np.sqrt(dy_arch)
        in_arch_zone = (yn >= 0.265) & (np.abs(xn - arch_cx) < arch_half_w)
        is_red_drape_or_rose = (redness > 0.14) & (r_c > 0.20)
        in_arch_hole = in_arch_zone & (~(is_white_sculpt & (yn < 0.35))) & (~(is_red_drape_or_rose & (yn < 0.46)))

        # 3) Cleanly trim bright green exterior grass along bottom floor seam while keeping full left slatted easel stand
        is_green_floor = (yn > 0.78) & (g_c > r_c * 0.96) & (redness < 0.14) & (xn > 0.145)
        is_green_floor |= (yn > 0.88) & (g_c > r_c * 0.98) & (redness < 0.12)
        is_bottom_edge_trim = (yn > 0.89) & (xn > 0.145) & (~is_red_drape_or_rose) & (~(is_white_sculpt & (xn > 0.21) & (xn < 0.34)))
        is_bottom_carpet = (yn > 0.62) & (np.abs(xn - 0.492) < 0.080)

        alpha = np.ones((Hh, Hw), dtype=np.float32)
        alpha[sky_mask | in_arch_hole | is_green_floor | is_bottom_edge_trim | is_bottom_carpet] = 0.0

        # Enhance color vibrancy: pure white Dragon & Phoenix reliefs, deep royal burgundy velvet & roses
        gate_out = np.zeros((Hh, Hw, 4), dtype=np.float32)
        gate_out[:, :, 0] = np.clip(np.where(is_white_sculpt, r_c * 1.08, r_c * 0.92), 0.0, 1.0)
        gate_out[:, :, 1] = np.clip(np.where(is_white_sculpt, g_c * 1.08, g_c * 0.78), 0.0, 1.0)
        gate_out[:, :, 2] = np.clip(np.where(is_white_sculpt, b_c * 1.10, b_c * 0.80), 0.0, 1.0)
        gate_out[:, :, 3] = alpha
        save_rgba_png("tex_dragon_phoenix_gate_rgba.png", gate_out)

        # Build gentle smooth 3D Relief Displacement Map (0..1)
        disp = np.zeros((Hh, Hw, 4), dtype=np.float32)
        sculpture_h = np.clip((lum - 0.38) * 1.35, 0.0, 1.0) * (1.0 - redness * 0.75)
        rose_h = np.clip(redness * 1.10, 0.0, 0.55) * np.where((xn > 0.18) & (xn < 0.82) & (yn > 0.10), 1.0, 0.2)
        total_h = np.maximum(sculpture_h, rose_h) * alpha
        disp[:, :, 0] = total_h
        disp[:, :, 1] = total_h
        disp[:, :, 2] = total_h
        disp[:, :, 3] = alpha
        save_rgba_png("tex_dragon_phoenix_gate_disp.png", disp)


if __name__ == "__main__":
    build_all_textures()
