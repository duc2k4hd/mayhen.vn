"""
MÂY HẸN — VIRTUAL WEDDING WORLD 3D
Renders the 5 distinct Time-of-Day lighting presets (05:00, 08:00, 12:00, 17:00, 20:00)
and generates the 3D Venue Walkthrough MP4 video from the master W03 .blend file.
"""

import math
import os
import subprocess
import bpy


PROJECT_ROOT = r"d:\Project\Laravel\mayhen.vn"
BLEND_PATH = os.path.join(
    PROJECT_ROOT,
    "Blender",
    "venues",
    "royal-crimson-marquee",
    "v1",
    "mayhen_royal_crimson_marquee_v1.blend",
)
RENDER_DIR = os.path.join(
    PROJECT_ROOT,
    "docs",
    "virtual-wedding-3d",
    "renders",
    "w03-pbr",
)
WALKTHROUGH_FRAMES_DIR = os.path.join(RENDER_DIR, "walkthrough_frames")
WALKTHROUGH_MP4 = os.path.join(RENDER_DIR, "w03_venue_walkthrough.mp4")
FFMPEG_BIN = r"C:\Users\DUC NOBI\AppData\Local\Microsoft\WinGet\Packages\Gyan.FFmpeg_Microsoft.Winget.Source_8wekyb3d8bbwe\ffmpeg-9.0.1-full_build\bin\ffmpeg.exe"


def main():
    bpy.ops.wm.open_mainfile(filepath=BLEND_PATH)
    scene = bpy.context.scene

    # Enable GPU Cycles
    scene.render.engine = "CYCLES"
    prefs = bpy.context.preferences.addons["cycles"].preferences
    try:
        prefs.compute_device_type = "CUDA"
        prefs.get_devices()
        for d in prefs.devices:
            d.use = True
        scene.cycles.device = "GPU"
    except Exception as exc:
        print("CUDA note:", exc)

    scene.cycles.samples = 40
    scene.cycles.use_denoising = True
    scene.render.resolution_x = 1280
    scene.render.resolution_y = 720
    scene.render.image_settings.file_format = "PNG"

    world = scene.world
    bg_node = world.node_tree.nodes.get("Background")
    sun_obj = bpy.data.objects.get("Sun_Exterior")
    sun_data = sun_obj.data

    foyer_fills = [
        bpy.data.objects.get("Fill_Foyer_Left"),
        bpy.data.objects.get("Fill_Foyer_Right_Photobooth"),
        bpy.data.objects.get("Fill_Foyer_Center_Portal"),
    ]
    hall_fills = [
        bpy.data.objects.get("Fill_Hall_South"),
        bpy.data.objects.get("Fill_Hall_Mid"),
        bpy.data.objects.get("Fill_Hall_North"),
    ]
    stage_fill = bpy.data.objects.get("Fill_Stage_Front")
    chan_lights = [o for o in bpy.data.objects if o.name.startswith("Chandelier_") and o.type == "LIGHT"]

    presets = [
        (
            "w03_lighting_01_dawn_05h.png",
            (0.10, 0.16, 0.36, 1.0), 0.35,        # World sky: Cool pre-dawn indigo
            1.2, (0.55, 0.68, 1.0), (80, 12, 35), # Sun: Low cool-rose horizon
            110.0, (0.48, 0.62, 0.95),            # Foyer fills: Early dawn blue twilight
            160.0, (0.95, 0.82, 0.65),            # Hall fills: Dim warm prep lights
            260.0,                                # Chandeliers: 60% prep glow
            340.0, (1.0, 0.85, 0.62),             # Stage fill
        ),
        (
            "w03_lighting_02_morning_08h.png",
            (0.62, 0.80, 0.98, 1.0), 0.95,        # World sky: Fresh morning blue
            4.5, (1.0, 0.92, 0.76), (64, 18, 28), # Sun: Crisp morning golden angle
            340.0, (1.0, 0.94, 0.82),             # Foyer fills: Warm morning daylight
            310.0, (1.0, 0.96, 0.90),             # Hall fills
            220.0,                                # Chandeliers
            480.0, (1.0, 0.94, 0.86),             # Stage fill
        ),
        (
            "w03_lighting_03_noon_12h.png",
            (0.88, 0.95, 1.00, 1.0), 1.45,        # World sky: Bright high-noon sky
            6.5, (1.0, 1.0, 0.98), (16, 4, 8),    # Sun: Overhead bright white
            620.0, (0.98, 0.99, 1.00),            # Foyer fills: High-key daylight
            460.0, (0.98, 0.98, 1.00),            # Hall fills: Bright daytime reception
            260.0,                                # Chandeliers
            620.0, (1.0, 0.98, 0.95),             # Stage fill
        ),
        (
            "w03_lighting_04_sunset_17h.png",
            (0.75, 0.28, 0.16, 1.0), 0.75,        # World sky: Deep sunset crimson-amber
            3.6, (1.0, 0.42, 0.15), (78, -16, -32), # Sun: Golden-hour raking orange
            320.0, (1.0, 0.54, 0.26),             # Foyer fills: Golden-hour amber glow
            280.0, (1.0, 0.72, 0.48),             # Hall fills: Warm amber-rose transition
            340.0,                                # Chandeliers ramping up
            580.0, (1.0, 0.78, 0.55),             # Stage fill
        ),
        (
            "w03_lighting_05_night_20h.png",
            (0.02, 0.025, 0.055, 1.0), 0.15,      # World sky: Deep night
            0.0, (0.4, 0.5, 0.8), (-35, 0, 0),    # Sun: Off (below horizon)
            150.0, (0.85, 0.75, 0.95),            # Foyer fills: Dramatic evening foyer spotlighting
            240.0, (1.0, 0.84, 0.64),             # Hall fills: Romantic warm candlelight
            420.0,                                # Chandeliers: Full 100% sparkling gala power
            780.0, (1.0, 0.88, 0.68),             # Stage fill: Gala spotlight intensity
        ),
    ]

    scene.camera = bpy.data.objects.get("Cam_PBR_02_Foyer_Entrance")
    for (
        fname,
        sky_col, sky_str,
        s_e, s_col, s_rot,
        foy_e, foy_col,
        hall_e, hall_col,
        chan_e,
        stg_e, stg_col,
    ) in presets:
        bg_node.inputs["Color"].default_value = sky_col
        bg_node.inputs["Strength"].default_value = sky_str
        sun_data.energy = s_e
        sun_data.color = s_col
        sun_obj.rotation_euler = tuple(math.radians(a) for a in s_rot)

        for fo in foyer_fills:
            if fo:
                fo.data.energy = foy_e
                fo.data.color = foy_col
        for ho in hall_fills:
            if ho:
                ho.data.energy = hall_e
                ho.data.color = hall_col
        for co in chan_lights:
            co.data.energy = chan_e
        if stage_fill:
            stage_fill.data.energy = stg_e
            stage_fill.data.color = stg_col

        scene.render.filepath = os.path.join(RENDER_DIR, fname)
        bpy.ops.render.render(write_still=True)
        print("RENDERED_TIMEFRAME:", fname)

    # Save the Night Gala state back to master .blend
    bpy.ops.wm.save_as_mainfile(filepath=BLEND_PATH)

    # Now render a smooth 48-frame (24fps) Walkthrough Video from Foyer (-18.2m) -> Portal -> Aisle -> Stage (+3.5m)
    os.makedirs(WALKTHROUGH_FRAMES_DIR, exist_ok=True)
    walk_cam_d = bpy.data.cameras.new("Cam_Walkthrough")
    walk_cam_d.lens = 20.0
    walk_cam_o = bpy.data.objects.new("Cam_Walkthrough", walk_cam_d)
    scene.collection.objects.link(walk_cam_o)
    scene.camera = walk_cam_o

    # Use EEVEE for fast 48-frame walkthrough sequence
    scene.render.engine = "BLENDER_EEVEE"
    scene.render.resolution_x = 960
    scene.render.resolution_y = 540

    total_frames = 48
    for f in range(1, total_frames + 1):
        scene.frame_set(f)
        t = (f - 1) / float(total_frames - 1)
        # Smooth ease-in-out S-curve from Y = -18.2m to Y = +3.2m
        smooth_t = t * t * (3.0 - 2.0 * t)
        cam_y = -18.2 + (3.2 - (-18.2)) * smooth_t
        # Gentle head-bob & look-up toward chandelier/stage
        cam_z = 1.58 + 0.04 * math.sin(t * math.pi)
        pitch_deg = 89.5 + 1.5 * math.sin(t * math.pi)
        walk_cam_o.location = (0.0, cam_y, cam_z)
        walk_cam_o.rotation_euler = (math.radians(pitch_deg), 0.0, 0.0)

        frame_path = os.path.join(WALKTHROUGH_FRAMES_DIR, f"frame_{f:03d}.png")
        scene.render.filepath = frame_path
        bpy.ops.render.render(write_still=True)

    # Encode MP4 via FFmpeg
    if os.path.exists(FFMPEG_BIN):
        cmd = [
            FFMPEG_BIN,
            "-y",
            "-framerate", "24",
            "-i", os.path.join(WALKTHROUGH_FRAMES_DIR, "frame_%03d.png"),
            "-c:v", "libx264",
            "-pix_fmt", "yuv420p",
            "-crf", "20",
            WALKTHROUGH_MP4,
        ]
        subprocess.run(cmd, check=True)
        print("ENCODED_WALKTHROUGH_MP4:", WALKTHROUGH_MP4, os.path.getsize(WALKTHROUGH_MP4))


if __name__ == "__main__":
    main()
