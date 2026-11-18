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
    "R": ["11110", "10001", "10001", "11110", "10100", "10010", "10001"],
    "S": ["01111", "10000", "10000", "01110", "00001", "00001", "11110"],
    "T": ["11111", "00100", "00100", "00100", "00100", "00100", "00100"],
    "U": ["10001", "10001", "10001", "10001", "10001", "10001", "01110"],
    "V": ["10001", "10001", "10001", "10001", "10001", "01010", "00100"],
    "Y": ["10001", "10001", "01010", "00100", "00100", "00100", "00100"],
    "0": ["01110", "10001", "10011", "10101", "11001", "10001", "01110"],
    "1": ["00100", "01100", "00100", "00100", "00100", "00100", "01110"],
    "2": ["01110", "10001", "00001", "00110", "01000", "10000", "11111"],
    "6": ["01110", "10000", "10000", "11110", "10001", "10001", "01110"],
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

    # 6. Stylized Editorial Pre-Wedding Portrait Artwork (512x768)
    cp = np.zeros((768, 512, 4), dtype=np.float32)
    py, px = np.mgrid[0:768, 0:512]
    # Soft sunlit garden sky -> meadow gradient matching f05 easel palette
    cp[:, :, 0] = np.where(py < 460, 0.80 + 0.14 * (1.0 - py / 460.0), 0.52 + 0.12 * ((768 - py) / 308.0))
    cp[:, :, 1] = np.where(py < 460, 0.88 + 0.08 * (1.0 - py / 460.0), 0.72 + 0.10 * ((768 - py) / 308.0))
    cp[:, :, 2] = np.where(py < 460, 0.96, 0.56)
    cp[:, :, 3] = 1.0
    # Stylized groom (dark suit) & bride (flowing white gown silhouette)
    groom_mask = ((np.abs(px - 295) < 26) & (py > 330) & (py < 610)) | (((px - 295) ** 2 + (py - 302) ** 2) < 20 ** 2)
    cp[groom_mask, :3] = (0.16, 0.18, 0.24)
    gown_w = np.clip((py - 355) * 0.42, 16, 130)
    bride_mask = ((np.abs(px - 215) < gown_w) & (py > 355) & (py < 665)) | (((px - 215) ** 2 + (py - 322) ** 2) < 19 ** 2)
    cp[bride_mask, :3] = (0.97, 0.98, 1.0)
    draw_border(cp, 14, 14, 498, 754, 6, (0.96, 0.94, 0.90))
    save_rgba_png("tex_couple_canvas.png", cp)

    # 7. Honest 16:9 LED Screen Idle Slate (1280x720)
    # "CHƯƠNG TRÌNH TRỰC TIẾP CHƯA BẮT ĐẦU" (Zero fake LIVE badge!)
    led = np.zeros((720, 1280, 4), dtype=np.float32)
    ly, lx = np.mgrid[0:720, 0:1280]
    radial = np.sqrt(((lx - 640) / 640.0) ** 2 + ((ly - 360) / 360.0) ** 2)
    vignette = np.clip(1.0 - 0.45 * radial, 0.35, 1.0)
    led[:, :, 0] = 0.09 * vignette
    led[:, :, 1] = 0.08 * vignette
    led[:, :, 2] = 0.08 * vignette
    led[:, :, 3] = 1.0
    gold = (0.78, 0.66, 0.50)
    soft_cream = (0.94, 0.90, 0.84)
    muted = (0.62, 0.58, 0.52)
    draw_border(led, 36, 36, 1244, 684, 3, gold)
    draw_border(led, 48, 48, 1232, 672, 1, muted)
    draw_text_centered(led, "MAY HEN VIRTUAL STAGE", 175, 5, gold, 3)
    draw_rect(led, 460, 248, 820, 251, gold)
    draw_text_centered(led, "CHUONG TRINH TRUC TIEP", 305, 7, soft_cream, 2)
    draw_text_centered(led, "CHUA BAT DAU", 385, 7, soft_cream, 2)
    draw_text_centered(led, "SAN SANG KET NOI TIN HIEU TU CHU TIEC", 505, 4, muted, 2)
    save_rgba_png("tex_led_idle_slate.png", led)


if __name__ == "__main__":
    build_all_textures()
