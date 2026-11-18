"""
MÂY HẸN — VIRTUAL WEDDING WORLD 3D
Wave W02: Metric-Accurate Architectural Graybox Builder (Blender 5.2 LTS)

Creates an editable, clean, real-world metric scale (1 unit = 1.0m) graybox
scene matching the exact spatial layout of the reference video:
  - Covered Foyer (-18.0m to -13.5m): Reception gallery table, stepped welcome
    boards, easel, central 3.2m x 3.15m portal, Song Hỷ plaque, Photobooth stage.
  - Main Banquet Hall (-13.5m to +5.2m): 2.20m central aisle, 16 x Ø1.50m round
    tables (2 columns per wing x 4 rows), 160 x 0.90m Tiffany chair proxies,
    side air coolers, ceiling canopy & chandelier volume proxies.
  - Ceremony Catwalk & Stage (+5.2m to +12.0m): 0.45m catwalk + 0.65m main stage,
    riser steps, backdrop, cake & champagne pedestals, band zones, and dual 16:9
    LED screen placeholders (3.20m x 1.80m).
  - Human Reference Proxies (1.70m) to visually prove scale & clearance.
"""

import math
import os
import bpy


PROJECT_ROOT = r"d:\Project\Laravel\mayhen.vn"
BLEND_OUT = os.path.join(
    PROJECT_ROOT,
    "Blender",
    "venues",
    "royal-crimson-marquee",
    "v1",
    "mayhen_royal_crimson_graybox_v1.blend",
)
RENDER_DIR = os.path.join(
    PROJECT_ROOT,
    "docs",
    "virtual-wedding-3d",
    "renders",
    "w02-graybox",
)


def reset_scene():
    bpy.ops.wm.read_factory_settings(use_empty=True)
    scene = bpy.context.scene
    scene.unit_settings.system = "METRIC"
    scene.unit_settings.scale_length = 1.0
    scene.unit_settings.length_unit = "METERS"
    return scene


def make_collection(name, parent=None):
    col = bpy.data.collections.new(name)
    if parent is None:
        bpy.context.scene.collection.children.link(col)
    else:
        parent.children.link(col)
    return col


def build_hierarchy():
    root = make_collection("MAYHEN_VIRTUAL_WEDDING")
    cols = {"ROOT": root}
    cols["REFERENCES"] = make_collection("REFERENCES", root)

    env = make_collection("ENVIRONMENT", root)
    cols["ENVIRONMENT"] = env
    for sub in ["GROUND", "EXTERIOR", "ARCHITECTURE", "ROOF_AND_STRUCTURE", "ENTRANCE"]:
        cols[sub] = make_collection(sub, env)

    cer = make_collection("CEREMONY", root)
    cols["CEREMONY"] = cer
    for sub in ["STAGE", "AISLE", "BACKDROP", "LED_SCREENS"]:
        cols[sub] = make_collection(sub, cer)

    din = make_collection("DINING", root)
    cols["DINING"] = din
    for sub in ["TABLES", "CHAIRS", "TABLEWARE", "DECORATIONS"]:
        cols[sub] = make_collection(sub, din)

    dec = make_collection("DECOR", root)
    cols["DECOR"] = dec
    for sub in ["FLOWERS", "FABRICS", "ORNAMENTS"]:
        cols[sub] = make_collection(sub, dec)

    cols["LIGHTING"] = make_collection("LIGHTING", root)

    chars = make_collection("CHARACTERS", root)
    cols["CHARACTERS"] = chars
    for sub in ["STAFF", "GUEST_NPCS", "CHARACTER_ANIMATIONS"]:
        cols[sub] = make_collection(sub, chars)

    for top in ["INTERACTION_ZONES", "COLLIDERS", "NAVIGATION", "CAMERAS", "WEB_EXPORT"]:
        cols[top] = make_collection(top, root)

    return cols


def make_clay_mat(name, rgba, roughness=0.72, metallic=0.0):
    mat = bpy.data.materials.new(name=name)
    bsdf = mat.node_tree.nodes.get("Principled BSDF")
    bsdf.inputs["Base Color"].default_value = rgba
    bsdf.inputs["Roughness"].default_value = roughness
    bsdf.inputs["Metallic"].default_value = metallic
    return mat


def link_exclusively(obj, target_col):
    for c in list(obj.users_collection):
        c.objects.unlink(obj)
    target_col.objects.link(obj)


def add_box(name, size_xyz, center_xyz, mat, col):
    bpy.ops.mesh.primitive_cube_add(size=1.0, location=center_xyz)
    obj = bpy.context.active_object
    obj.name = name
    obj.scale = (size_xyz[0], size_xyz[1], size_xyz[2])
    bpy.ops.object.transform_apply(location=False, rotation= False, scale=True)
    if mat:
        obj.data.materials.append(mat)
    link_exclusively(obj, col)
    return obj


def add_cylinder(name, radius, depth, center_xyz, mat, col, vertices=32):
    bpy.ops.mesh.primitive_cylinder_add(
        vertices=vertices, radius=radius, depth=depth, location=center_xyz
    )
    obj = bpy.context.active_object
    obj.name = name
    if mat:
        obj.data.materials.append(mat)
    link_exclusively(obj, col)
    return obj


def add_human_proxy(name, x, y, z_floor, mat, col, height=1.70):
    """Metric 1.70m architectural human scale proxy grounded at z_floor."""
    torso_h = height * 0.78
    head_r = 0.115
    body = add_cylinder(
        f"{name}_Body",
        radius=0.19,
        depth=torso_h,
        center_xyz=(x, y, z_floor + torso_h / 2.0),
        mat=mat,
        col=col,
        vertices=20,
    )
    bpy.ops.mesh.primitive_uv_sphere_add(
        segments=20,
        ring_count=12,
        radius=head_r,
        location=(x, y, z_floor + torso_h + head_r * 1.15),
    )
    head = bpy.context.active_object
    head.name = f"{name}_Head"
    head.data.materials.append(mat)
    link_exclusively(head, col)
    head.parent = body
    return body


def build_graybox():
    scene = reset_scene()
    cols = build_hierarchy()

    m_ground = make_clay_mat("GB_Ground_Turf", (0.72, 0.78, 0.71, 1.0), 0.85)
    m_aisle = make_clay_mat("GB_Aisle_Carpet", (0.56, 0.60, 0.66, 1.0), 0.82)
    m_struct = make_clay_mat("GB_Truss_Structure", (0.78, 0.79, 0.81, 1.0), 0.45, 0.35)
    m_wall = make_clay_mat("GB_Partition_Curtain", (0.74, 0.52, 0.54, 1.0), 0.78)
    m_stage = make_clay_mat("GB_Stage_Riser", (0.28, 0.28, 0.30, 1.0), 0.80)
    m_furniture = make_clay_mat("GB_Table_Chair", (0.91, 0.89, 0.85, 1.0), 0.68)
    m_fabric = make_clay_mat("GB_Ceiling_Drapery", (0.94, 0.91, 0.86, 1.0), 0.74)
    m_canopy_red = make_clay_mat("GB_Center_Canopy", (0.68, 0.32, 0.36, 1.0), 0.65)
    m_led = make_clay_mat("GB_LED_Screen_16x9", (0.18, 0.24, 0.32, 1.0), 0.35)
    m_human = make_clay_mat("GB_Human_170cm", (0.82, 0.64, 0.42, 1.0), 0.55)

    # 1. Ground & Aisle Carpets (Z = 0.00m)
    add_box("GB_Exterior_Apron", (24.0, 38.0, 0.08), (0.0, -3.5, -0.04), m_ground, cols["GROUND"])
    add_box("GB_Foyer_Carpet", (18.0, 4.5, 0.012), (0.0, -15.75, 0.006), m_aisle, cols["GROUND"])
    add_box("GB_Central_Aisle_220cm", (2.20, 18.7, 0.014), (0.0, -4.15, 0.007), m_aisle, cols["AISLE"])

    # 2. Steel Truss Portal Frames (7 bays across Y = -18m .. +12m, span 18.0m, eave 4.5m, ridge 6.2m)
    bay_ys = [-18.0, -13.5, -8.5, -3.5, 1.5, 6.8, 12.0]
    for idx, y_pos in enumerate(bay_ys):
        add_box(f"GB_Truss_Col_L_{idx}", (0.35, 0.35, 4.50), (-9.0, y_pos, 2.25), m_struct, cols["ROOF_AND_STRUCTURE"])
        add_box(f"GB_Truss_Col_R_{idx}", (0.35, 0.35, 4.50), (9.0, y_pos, 2.25), m_struct, cols["ROOF_AND_STRUCTURE"])
        # Pitched rafters to ridge (9.0m horizontal, 1.7m rise -> length 9.16m, angle 10.7 deg)
        angle = math.atan2(1.70, 9.0)
        rafter_len = math.hypot(9.0, 1.70)
        r_left = add_box(f"GB_Rafter_L_{idx}", (rafter_len, 0.30, 0.30), (-4.5, y_pos, 5.35), m_struct, cols["ROOF_AND_STRUCTURE"])
        r_left.rotation_euler = (0.0, -angle, 0.0)
        r_right = add_box(f"GB_Rafter_R_{idx}", (rafter_len, 0.30, 0.30), (4.5, y_pos, 5.35), m_struct, cols["ROOF_AND_STRUCTURE"])
        r_right.rotation_euler = (0.0, angle, 0.0)

    # Longitudinal purlins
    for x_p, z_p in [(-9.0, 4.50), (0.0, 6.20), (9.0, 4.50)]:
        add_box(f"GB_Purlin_{int(x_p)}", (0.28, 30.4, 0.28), (x_p, -3.0, z_p), m_struct, cols["ROOF_AND_STRUCTURE"])

    # Side enclosure walls (leaving front foyer Y < -13.5m open on front, enclosed main hall)
    add_box("GB_SideWall_Left", (0.12, 25.5, 4.45), (-8.9, -0.75, 2.225), m_struct, cols["ARCHITECTURE"])
    add_box("GB_SideWall_Right", (0.12, 25.5, 4.45), (8.9, -0.75, 2.225), m_struct, cols["ARCHITECTURE"])
    add_box("GB_BackWall_North", (18.0, 0.15, 4.50), (0.0, 12.0, 2.25), m_wall, cols["ARCHITECTURE"])

    # 3. Foyer-to-Hall Crimson Partition Wall at Y = -13.5m with 3.20m x 3.15m Central Portal
    add_box("GB_FoyerWall_Left", (7.40, 0.22, 4.45), (-5.30, -13.5, 2.225), m_wall, cols["ENTRANCE"])
    add_box("GB_FoyerWall_Right", (7.40, 0.22, 4.45), (5.30, -13.5, 2.225), m_wall, cols["ENTRANCE"])
    add_box("GB_FoyerPortal_Header", (3.20, 0.26, 1.30), (0.0, -13.5, 3.80), m_wall, cols["ENTRANCE"])
    add_box("GB_FloralArch_LeftCol", (0.85, 0.85, 3.35), (-1.85, -13.85, 1.675), m_canopy_red, cols["FLOWERS"])
    add_box("GB_FloralArch_RightCol", (0.85, 0.85, 3.35), (1.85, -13.85, 1.675), m_canopy_red, cols["FLOWERS"])

    # Foyer Left: Reception Gallery Table + Welcome Boards + Easel
    add_box("GB_Reception_Table", (2.40, 0.75, 0.76), (-5.60, -14.45, 0.38), m_furniture, cols["ENTRANCE"])
    add_box("GB_Gift_MoneyBox", (0.42, 0.30, 0.34), (-6.45, -14.45, 0.93), m_furniture, cols["ENTRANCE"])
    add_box("GB_WelcomeBoard_Low", (0.85, 0.08, 1.55), (-3.55, -14.15, 0.775), m_wall, cols["ENTRANCE"])
    add_box("GB_WelcomeBoard_High", (0.85, 0.08, 1.90), (-2.70, -14.05, 0.95), m_wall, cols["ENTRANCE"])
    add_box("GB_Portrait_Easel", (0.65, 0.55, 1.45), (-2.45, -14.85, 0.725), m_furniture, cols["ENTRANCE"])

    # Foyer Right: Song Hỷ Relief + Photobooth Platform & NT Monogram
    add_box("GB_SongHy_Relief", (1.25, 0.14, 1.35), (2.75, -13.62, 1.35), m_canopy_red, cols["ENTRANCE"])
    add_box("GB_Photobooth_Platform", (4.20, 1.40, 0.12), (5.50, -14.35, 0.06), m_stage, cols["ENTRANCE"])
    add_box("GB_Photobooth_NT_Block", (1.25, 0.22, 1.05), (5.50, -14.10, 0.645), m_furniture, cols["ENTRANCE"])

    # 4. Ceremony Catwalk (0.45m) & Main Stage (0.65m) + Riser Steps
    add_box("GB_Aisle_Step_01", (2.20, 0.42, 0.22), (0.0, 5.00, 0.11), m_stage, cols["STAGE"])
    add_box("GB_Catwalk_Runway", (3.00, 3.30, 0.45), (0.0, 6.85, 0.225), m_stage, cols["STAGE"])
    add_box("GB_Main_Stage", (10.40, 3.40, 0.65), (0.0, 10.20, 0.325), m_stage, cols["STAGE"])
    add_box("GB_Stage_Backdrop", (9.60, 0.20, 4.20), (0.0, 11.75, 2.75), m_wall, cols["BACKDROP"])
    add_box("GB_Cake_Pedestal", (0.90, 0.90, 0.85), (-3.65, 9.35, 1.075), m_furniture, cols["STAGE"])
    add_box("GB_Champagne_Pedestal", (1.00, 0.90, 0.85), (3.65, 9.35, 1.075), m_furniture, cols["STAGE"])
    add_cylinder("GB_DrumKit_Zone", 0.85, 0.95, (2.20, 10.30, 1.125), m_struct, cols["STAGE"], 24)
    add_box("GB_Keyboard_Zone", (1.30, 0.45, 0.85), (-2.35, 10.25, 1.075), m_struct, cols["STAGE"])

    # Dual 16:9 Stage LED Screens (3.20m x 1.80m) flanking stage backdrop
    add_box("GB_LED_Screen_Left", (3.20, 0.12, 1.80), (-6.35, 10.60, 2.45), m_led, cols["LED_SCREENS"])
    add_box("GB_LED_Screen_Right", (3.20, 0.12, 1.80), (6.35, 10.60, 2.45), m_led, cols["LED_SCREENS"])

    # 5. Dining Hall: 16 Round Ø1.50m Tables (4 rows x 4 columns) + 160 Chairs (Zero Overlap!)
    table_xs = [-6.40, -3.00, 3.00, 6.40]
    table_ys = [-10.20, -6.40, -2.60, 1.20]

    # Shared mesh data for tables & chairs even in graybox
    bpy.ops.mesh.primitive_cylinder_add(vertices=32, radius=0.75, depth=0.76, location=(0, 0, 0))
    proto_table = bpy.context.active_object
    proto_table.data.materials.append(m_furniture)
    table_mesh = proto_table.data
    bpy.data.objects.remove(proto_table, do_unlink=True)

    bpy.ops.mesh.primitive_cube_add(size=1.0, location=(0, 0, 0))
    proto_chair = bpy.context.active_object
    proto_chair.scale = (0.40, 0.42, 0.90)
    bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
    proto_chair.data.materials.append(m_furniture)
    chair_mesh = proto_chair.data
    bpy.data.objects.remove(proto_chair, do_unlink=True)

    t_count = 0
    for r_i, ty in enumerate(table_ys):
        for c_i, tx in enumerate(table_xs):
            t_count += 1
            t_obj = bpy.data.objects.new(f"GB_Table_{t_count:02d}", table_mesh)
            t_obj.location = (tx, ty, 0.38)
            cols["TABLES"].objects.link(t_obj)

            chair_ring_r = 1.04
            for s_i in range(10):
                ang = (2.0 * math.pi * s_i) / 10.0
                cx = tx + math.cos(ang) * chair_ring_r
                cy = ty + math.sin(ang) * chair_ring_r
                c_obj = bpy.data.objects.new(f"GB_Chair_T{t_count:02d}_{s_i+1:02d}", chair_mesh)
                c_obj.location = (cx, cy, 0.45)
                c_obj.rotation_euler = (0.0, 0.0, ang + math.pi / 2.0)
                cols["CHAIRS"].objects.link(c_obj)

    # Side Evaporative Air Coolers
    for idx, (ax, ay) in enumerate([(-8.25, -8.3), (-8.25, -0.7), (8.25, -8.3), (8.25, -0.7)]):
        add_box(f"GB_AirCooler_{idx+1}", (0.75, 0.65, 1.72), (ax, ay, 0.86), m_struct, cols["ARCHITECTURE"])

    # 6. Ceiling Drapery & Chandelier Volume Proxies (Clearance Z >= 3.55m)
    add_cylinder("GB_Central_Crimson_Canopy_Proxy", 2.40, 0.95, (0.0, 1.50, 4.95), m_canopy_red, cols["FABRICS"], 32)
    for idx, x_rib in enumerate([-2.2, -1.5, 1.5, 2.2]):
        add_box(f"GB_Ceiling_Ribbon_Proxy_{idx+1}", (0.18, 15.0, 0.90), (x_rib, -6.0, 4.75), m_fabric, cols["FABRICS"])
    chandelier_coords = [
        (0.0, -9.5, 4.05),
        (0.0, 1.5, 3.95),
        (-4.7, -8.3, 3.95),
        (-4.7, -0.7, 3.95),
        (4.7, -8.3, 3.95),
        (4.7, -0.7, 3.95),
    ]
    for idx, (lx, ly, lz) in enumerate(chandelier_coords):
        add_cylinder(f"GB_Chandelier_Proxy_{idx+1}", 0.48, 0.90, (lx, ly, lz), m_fabric, cols["LIGHTING"], 20)

    # 7. Anthropometric 1.70m Human Scale Proxies (Entrance, Aisle, Dining, Stage)
    add_human_proxy("ScaleHuman_Entrance_170cm", 0.0, -14.5, 0.012, m_human, cols["REFERENCES"])
    add_human_proxy("ScaleHuman_Reception_170cm", -5.6, -15.1, 0.012, m_human, cols["REFERENCES"])
    add_human_proxy("ScaleHuman_Aisle_170cm", 0.0, -3.5, 0.014, m_human, cols["REFERENCES"])
    add_human_proxy("ScaleHuman_Stage_170cm", 0.0, 9.6, 0.65, m_human, cols["REFERENCES"])

    # 8. Walkable Navigation & Colliders Metadata
    nav = add_box("NavBounds_MainWalkable", (16.8, 30.0, 0.02), (0.0, -5.0, 0.01), None, cols["NAVIGATION"])
    nav.hide_render = True
    col_stage = add_box("Collider_Stage_Main", (10.4, 3.4, 2.2), (0.0, 10.2, 1.1), None, cols["COLLIDERS"])
    col_stage.hide_render = True

    # 9. Clean Studio Clay Lighting for Graybox Inspection
    world = bpy.data.worlds.new("GB_World")
    scene.world = world
    bg = world.node_tree.nodes.get("Background")
    bg.inputs["Color"].default_value = (0.86, 0.88, 0.92, 1.0)
    bg.inputs["Strength"].default_value = 0.85

    sun_data = bpy.data.lights.new("GB_Sun", type="SUN")
    sun_data.energy = 2.6
    sun_data.angle = math.radians(8.0)
    sun_obj = bpy.data.objects.new("GB_Sun", sun_data)
    sun_obj.rotation_euler = (math.radians(48), math.radians(18), math.radians(28))
    cols["LIGHTING"].objects.link(sun_obj)

    for iy in [-15.5, -8.0, -1.0, 7.5]:
        area_d = bpy.data.lights.new(f"GB_Fill_{int(iy)}", type="AREA")
        area_d.energy = 420.0
        area_d.size = 6.5
        area_o = bpy.data.objects.new(f"GB_Fill_{int(iy)}", area_d)
        area_o.location = (0.0, iy, 4.35)
        cols["LIGHTING"].objects.link(area_o)

    # 10. Cameras
    cameras = [
        ("Cam_GB_01_Overview_Cutaway", (-11.5, -24.5, 8.8), (math.radians(66), 0.0, math.radians(-26)), 24.0, "PERSP", 0),
        ("Cam_GB_02_Foyer_Entrance", (0.0, -19.2, 1.62), (math.radians(89), 0.0, 0.0), 21.0, "PERSP", 0),
        ("Cam_GB_03_Aisle_To_Stage", (0.0, -8.8, 1.65), (math.radians(90), 0.0, 0.0), 21.0, "PERSP", 0),
        ("Cam_GB_04_Top_Blueprint", (0.0, -3.0, 34.0), (0.0, 0.0, 0.0), 50.0, "ORTHO", 36.0),
    ]
    cam_objs = []
    for cname, loc, rot, lens, ctype, oscale in cameras:
        cd = bpy.data.cameras.new(cname)
        cd.type = ctype
        if ctype == "ORTHO":
            cd.ortho_scale = oscale
        else:
            cd.lens = lens
        co = bpy.data.objects.new(cname, cd)
        co.location = loc
        co.rotation_euler = rot
        cols["CAMERAS"].objects.link(co)
        cam_objs.append(co)

    # Save editable .blend
    os.makedirs(os.path.dirname(BLEND_OUT), exist_ok=True)
    os.makedirs(RENDER_DIR, exist_ok=True)
    scene.camera = cam_objs[2]
    bpy.ops.wm.save_as_mainfile(filepath=BLEND_OUT)
    print("SAVED_GRAYBOX_BLEND:", BLEND_OUT)

    # Render 4 W02 Graybox Verification Views via Cycles CUDA
    scene.render.engine = "CYCLES"
    prefs = bpy.context.preferences.addons["cycles"].preferences
    try:
        prefs.compute_device_type = "CUDA"
        prefs.get_devices()
        for d in prefs.devices:
            d.use = True
        scene.cycles.device = "GPU"
    except Exception as exc:
        print("CUDA fallback note:", exc)

    scene.cycles.samples = 48
    scene.cycles.use_denoising = True
    scene.render.resolution_x = 1280
    scene.render.resolution_y = 720
    scene.render.image_settings.file_format = "PNG"

    out_files = [
        "graybox_01_overview.png",
        "graybox_02_foyer_entrance.png",
        "graybox_03_aisle_to_stage.png",
        "graybox_04_top_orthographic_blueprint.png",
    ]
    # Temporarily hide roof rafters in orthographic top blueprint so interior table clearances are 100% visible
    for cam_o, fname in zip(cam_objs, out_files):
        hide_roof = "Blueprint" in cam_o.name or "Overview" in cam_o.name
        for o in cols["ROOF_AND_STRUCTURE"].objects:
            if "Rafter" in o.name or "Purlin" in o.name:
                o.hide_render = "Blueprint" in cam_o.name
        for o in cols["FABRICS"].objects:
            o.hide_render = "Blueprint" in cam_o.name
        scene.camera = cam_o
        scene.render.filepath = os.path.join(RENDER_DIR, fname)
        bpy.ops.render.render(write_still=True)
        print("RENDERED_GRAYBOX:", fname)


if __name__ == "__main__":
    build_graybox()
