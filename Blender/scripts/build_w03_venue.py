"""
MÂY HẸN — VIRTUAL WEDDING WORLD 3D
Wave W03: High-Fidelity PBR Venue, 5-Timeframe Lighting, Articulated Animated NPCs & GLB Exporter
Blender 5.2.3 LTS Automation Script

Faithfully reconstructs the Royal Crimson Marquee ("Lễ Tân Hôn — Cao Nhân & Trần Thảo", 29.09.2026, NT)
from the reference video "FDown.vn_Tai_video_Facebook_720p (HD)_7b85.mp4" (frames f01..f18):
  - Full-width pleated crimson foyer curtain wall at Y = -13.5m with 3.2m x 3.15m portal, white header plaque,
    and cascading tri-color floral arch towers (white hydrangeas, crimson roses, blush cherry blossoms).
  - Foyer Left: Reception gallery table (cream silk + crimson runner, white money box, 6 photo frames),
    stepped terracotta welcome boards ("LỄ TÂN HÔN" & "NHÀ CÓ HỶ" with planar UVs), and couple portrait easel.
  - Foyer Right: 3D red Song Hỷ relief, dark-carpeted photobooth stage, freestanding 3D "NT" monogram,
    neon script header, and lush floral pillars.
  - Main Banquet Hall: Slate blue-grey felt carpet aisle (2.20m) + green artificial turf wings, blackout
    starlight velvet walls & roof, 12-petal crimson velvet ceiling dome, longitudinal ivory silk wave drapes,
    6 multi-tier crystal chandeliers, 4 industrial air coolers, 16 round Ø1.50m tables with 10-guest porcelain
    tableware settings, and 160 instanced white Tiffany chairs (zero overlap, >1.0m clearance).
  - Ceremony Catwalk & Stage: Charcoal-carpeted runway with low floral hedges & gobo floor discs, pleated
    crimson velvet stage backdrop with glowing 3D "NT" monogram, cake & champagne pyramid pedestals,
    5-piece drum kit & keyboard band zone, overhead lighting truss + volumetric beam cones, and dual 16:9
    LED screens with crisp planar UVs ("CHƯƠNG TRÌNH TRỰC TIẾP CHƯA BẮT ĐẦU").
  - Articulated Demo NPCs with staggered 72-frame (3.0s) looping keyframe animations.
"""

import math
import os
import bpy


PROJECT_ROOT = r"d:\Project\Laravel\mayhen.vn"
TEX_DIR = os.path.join(
    PROJECT_ROOT,
    "Blender",
    "venues",
    "royal-crimson-marquee",
    "v1",
    "textures",
)
BLEND_OUT = os.path.join(
    PROJECT_ROOT,
    "Blender",
    "venues",
    "royal-crimson-marquee",
    "v1",
    "mayhen_royal_crimson_marquee_v1.blend",
)
GLB_OUT = os.path.join(
    PROJECT_ROOT,
    "public",
    "assets",
    "virtual-venues",
    "royal-crimson-marquee",
    "v1",
    "mayhen_royal_crimson_marquee_v1.glb",
)
RENDER_DIR = os.path.join(
    PROJECT_ROOT,
    "docs",
    "virtual-wedding-3d",
    "renders",
    "w03-pbr",
)


def reset_scene():
    bpy.ops.wm.read_factory_settings(use_empty=True)
    scene = bpy.context.scene
    scene.unit_settings.system = "METRIC"
    scene.unit_settings.scale_length = 1.0
    scene.unit_settings.length_unit = "METERS"
    scene.frame_start = 1
    scene.frame_end = 72
    scene.render.fps = 24
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


def link_exclusively(obj, target_col):
    for c in list(obj.users_collection):
        c.objects.unlink(obj)
    target_col.objects.link(obj)


# ==================== PBR MATERIAL FACTORY ====================
def create_pbr_materials():
    mats = {}

    def new_mat(name):
        mat = bpy.data.materials.new(name=name)
        nodes = mat.node_tree.nodes
        nodes.clear()
        output = nodes.new("ShaderNodeOutputMaterial")
        bsdf = nodes.new("ShaderNodeBsdfPrincipled")
        mat.node_tree.links.new(bsdf.outputs["BSDF"], output.inputs["Surface"])
        return mat, nodes, bsdf

    # 1. Crimson Velvet (Rich wine-red velvet with sheen)
    mat, nodes, bsdf = new_mat("Mat_Crimson_Velvet")
    bsdf.inputs["Base Color"].default_value = (0.52, 0.045, 0.11, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.78
    bsdf.inputs["Sheen Weight"].default_value = 1.0
    bsdf.inputs["Sheen Tint"].default_value = (0.90, 0.18, 0.28, 1.0)
    bsdf.inputs["Subsurface Weight"].default_value = 0.06
    mats["Crimson_Velvet"] = mat

    # 2. Ivory Silk Drapery (Translucent warm ceiling & table fabric)
    mat, nodes, bsdf = new_mat("Mat_Ivory_Silk")
    bsdf.inputs["Base Color"].default_value = (0.96, 0.94, 0.90, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.48
    bsdf.inputs["Sheen Weight"].default_value = 0.80
    bsdf.inputs["Subsurface Weight"].default_value = 0.12
    mats["Ivory_Silk"] = mat

    # 3. White Tiffany Resin / Lacquer
    mat, nodes, bsdf = new_mat("Mat_Tiffany_White")
    bsdf.inputs["Base Color"].default_value = (0.95, 0.95, 0.96, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.26
    bsdf.inputs["Specular IOR Level"].default_value = 0.65
    mats["Tiffany_White"] = mat

    # 4. Starlight Velvet Ceiling & Wall Liner
    mat, nodes, bsdf = new_mat("Mat_Starlight_Liner")
    tex_path = os.path.join(TEX_DIR, "tex_starlight_velvet.png")
    if os.path.exists(tex_path):
        img_node = nodes.new("ShaderNodeTexImage")
        img_node.image = bpy.data.images.load(tex_path)
        coord_node = nodes.new("ShaderNodeTexCoord")
        mapping_node = nodes.new("ShaderNodeMapping")
        mapping_node.inputs["Scale"].default_value = (6.0, 8.0, 1.0)
        mat.node_tree.links.new(coord_node.outputs["UV"], mapping_node.inputs["Vector"])
        mat.node_tree.links.new(mapping_node.outputs["Vector"], img_node.inputs["Vector"])
        mat.node_tree.links.new(img_node.outputs["Color"], bsdf.inputs["Base Color"])
        mat.node_tree.links.new(img_node.outputs["Color"], bsdf.inputs["Emission Color"])
        bsdf.inputs["Emission Strength"].default_value = 3.2
    else:
        bsdf.inputs["Base Color"].default_value = (0.015, 0.015, 0.025, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.92
    mats["Starlight_Liner"] = mat

    # 5. Slate Blue-Grey Felt Carpet (Aisle & Foyer)
    mat, nodes, bsdf = new_mat("Mat_Carpet_Slate")
    tex_path = os.path.join(TEX_DIR, "tex_carpet_slate.png")
    if os.path.exists(tex_path):
        img_node = nodes.new("ShaderNodeTexImage")
        img_node.image = bpy.data.images.load(tex_path)
        coord_node = nodes.new("ShaderNodeTexCoord")
        mapping_node = nodes.new("ShaderNodeMapping")
        mapping_node.inputs["Scale"].default_value = (3.0, 12.0, 1.0)
        mat.node_tree.links.new(coord_node.outputs["UV"], mapping_node.inputs["Vector"])
        mat.node_tree.links.new(mapping_node.outputs["Vector"], img_node.inputs["Vector"])
        mat.node_tree.links.new(img_node.outputs["Color"], bsdf.inputs["Base Color"])
    else:
        bsdf.inputs["Base Color"].default_value = (0.34, 0.40, 0.50, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.90
    mats["Carpet_Slate"] = mat

    # 6. Event Artificial Turf Grass (Dining Wings)
    mat, nodes, bsdf = new_mat("Mat_Turf_Grass")
    alb_path = os.path.join(TEX_DIR, "tex_turf_albedo.png")
    norm_path = os.path.join(TEX_DIR, "tex_turf_normal.png")
    if os.path.exists(alb_path):
        alb_node = nodes.new("ShaderNodeTexImage")
        alb_node.image = bpy.data.images.load(alb_path)
        coord_node = nodes.new("ShaderNodeTexCoord")
        mapping_node = nodes.new("ShaderNodeMapping")
        mapping_node.inputs["Scale"].default_value = (12.0, 18.0, 1.0)
        mat.node_tree.links.new(coord_node.outputs["UV"], mapping_node.inputs["Vector"])
        mat.node_tree.links.new(mapping_node.outputs["Vector"], alb_node.inputs["Vector"])
        mat.node_tree.links.new(alb_node.outputs["Color"], bsdf.inputs["Base Color"])
        if os.path.exists(norm_path):
            norm_node = nodes.new("ShaderNodeTexImage")
            norm_node.image = bpy.data.images.load(norm_path)
            norm_node.image.colorspace_settings.name = "Non-Color"
            norm_map = nodes.new("ShaderNodeNormalMap")
            norm_map.inputs["Strength"].default_value = 0.75
            mat.node_tree.links.new(mapping_node.outputs["Vector"], norm_node.inputs["Vector"])
            mat.node_tree.links.new(norm_node.outputs["Color"], norm_map.inputs["Color"])
            mat.node_tree.links.new(norm_map.outputs["Normal"], bsdf.inputs["Normal"])
    else:
        bsdf.inputs["Base Color"].default_value = (0.16, 0.34, 0.14, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.86
    mats["Turf_Grass"] = mat

    # 7. Polished Gold Brass & Emissive Gold Monogram
    mat, nodes, bsdf = new_mat("Mat_Gold_Brass")
    bsdf.inputs["Base Color"].default_value = (0.90, 0.74, 0.32, 1.0)
    bsdf.inputs["Metallic"].default_value = 0.94
    bsdf.inputs["Roughness"].default_value = 0.22
    mats["Gold_Brass"] = mat

    mat, nodes, bsdf = new_mat("Mat_Monogram_Glow")
    bsdf.inputs["Base Color"].default_value = (1.0, 0.96, 0.86, 1.0)
    bsdf.inputs["Emission Color"].default_value = (1.0, 0.92, 0.74, 1.0)
    bsdf.inputs["Emission Strength"].default_value = 4.2
    bsdf.inputs["Roughness"].default_value = 0.20
    mats["Monogram_Glow"] = mat

    # 8. Crystal Glass & Warm Chandelier Bulbs
    mat, nodes, bsdf = new_mat("Mat_Crystal_Glass")
    bsdf.inputs["Base Color"].default_value = (0.98, 0.99, 1.0, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.05
    bsdf.inputs["IOR"].default_value = 1.52
    bsdf.inputs["Transmission Weight"].default_value = 0.92
    bsdf.inputs["Emission Color"].default_value = (1.0, 0.95, 0.82, 1.0)
    bsdf.inputs["Emission Strength"].default_value = 0.45
    mats["Crystal_Glass"] = mat

    mat, nodes, bsdf = new_mat("Mat_Chandelier_Bulb")
    bsdf.inputs["Base Color"].default_value = (1.0, 0.93, 0.78, 1.0)
    bsdf.inputs["Emission Color"].default_value = (1.0, 0.88, 0.62, 1.0)
    bsdf.inputs["Emission Strength"].default_value = 8.0
    mats["Chandelier_Bulb"] = mat

    # 9. Planar-Mapped Textures: LED Screen, Welcome Left/Right, Couple Canvas
    def make_planar_tex_mat(mat_name, filename, emission_strength=0.0):
        m, nds, b = new_mat(mat_name)
        p = os.path.join(TEX_DIR, filename)
        if os.path.exists(p):
            img_node = nds.new("ShaderNodeTexImage")
            img_node.image = bpy.data.images.load(p)
            img_node.extension = "CLIP"
            m.node_tree.links.new(img_node.outputs["Color"], b.inputs["Base Color"])
            if emission_strength > 0:
                m.node_tree.links.new(img_node.outputs["Color"], b.inputs["Emission Color"])
                b.inputs["Emission Strength"].default_value = emission_strength
        b.inputs["Roughness"].default_value = 0.30 if emission_strength > 0 else 0.65
        return m

    mats["LED_Screen"] = make_planar_tex_mat("Mat_LED_Screen", "tex_led_idle_slate.png", emission_strength=2.6)
    mats["Welcome_Left"] = make_planar_tex_mat("Mat_Welcome_Left", "tex_welcome_left.png", emission_strength=0.15)
    mats["Welcome_Right"] = make_planar_tex_mat("Mat_Welcome_Right", "tex_welcome_right.png", emission_strength=0.15)
    mats["Couple_Canvas"] = make_planar_tex_mat("Mat_Couple_Canvas", "tex_couple_canvas.png", emission_strength=0.12)

    # 10. Structural & Stage Materials
    mat, nodes, bsdf = new_mat("Mat_Truss_Silver")
    bsdf.inputs["Base Color"].default_value = (0.76, 0.78, 0.82, 1.0)
    bsdf.inputs["Metallic"].default_value = 0.85
    bsdf.inputs["Roughness"].default_value = 0.32
    mats["Truss_Silver"] = mat

    mat, nodes, bsdf = new_mat("Mat_Stage_Charcoal")
    bsdf.inputs["Base Color"].default_value = (0.07, 0.07, 0.08, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.72
    mats["Stage_Charcoal"] = mat

    # 11. Florals: Hydrangea White, Crimson Rose, Blush Pink Blossom, Eucalyptus
    mat, nodes, bsdf = new_mat("Mat_Rose_Cream")
    bsdf.inputs["Base Color"].default_value = (0.97, 0.95, 0.92, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.68
    bsdf.inputs["Subsurface Weight"].default_value = 0.15
    mats["Rose_Cream"] = mat

    mat, nodes, bsdf = new_mat("Mat_Rose_Crimson")
    bsdf.inputs["Base Color"].default_value = (0.65, 0.05, 0.14, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.68
    bsdf.inputs["Sheen Weight"].default_value = 0.6
    mats["Rose_Crimson"] = mat

    mat, nodes, bsdf = new_mat("Mat_Blossom_Pink")
    bsdf.inputs["Base Color"].default_value = (0.96, 0.74, 0.78, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.62
    bsdf.inputs["Subsurface Weight"].default_value = 0.20
    mats["Blossom_Pink"] = mat

    mat, nodes, bsdf = new_mat("Mat_Foliage_Green")
    bsdf.inputs["Base Color"].default_value = (0.18, 0.34, 0.20, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.65
    mats["Foliage_Green"] = mat

    # 12. Tableware & Props
    mat, nodes, bsdf = new_mat("Mat_Porcelain")
    bsdf.inputs["Base Color"].default_value = (0.98, 0.98, 0.97, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.12
    mats["Porcelain"] = mat

    mat, nodes, bsdf = new_mat("Mat_Drink_BlueCan")
    bsdf.inputs["Base Color"].default_value = (0.10, 0.28, 0.68, 1.0)
    bsdf.inputs["Metallic"].default_value = 0.80
    bsdf.inputs["Roughness"].default_value = 0.25
    mats["Drink_BlueCan"] = mat

    mat, nodes, bsdf = new_mat("Mat_Wood_Warm")
    bsdf.inputs["Base Color"].default_value = (0.48, 0.30, 0.16, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.50
    mats["Wood_Warm"] = mat

    # 13. Translucent Stage Beam Cone & Floor Gobo Disc
    mat, nodes, bsdf = new_mat("Mat_Stage_Beam_Cone")
    bsdf.inputs["Base Color"].default_value = (0.92, 0.96, 1.0, 1.0)
    bsdf.inputs["Emission Color"].default_value = (0.88, 0.94, 1.0, 1.0)
    bsdf.inputs["Emission Strength"].default_value = 0.38
    bsdf.inputs["Alpha"].default_value = 0.055
    bsdf.inputs["Roughness"].default_value = 1.0
    mat.blend_method = "BLEND" if hasattr(mat, "blend_method") else "OPAQUE"
    mats["Stage_Beam_Cone"] = mat

    mat, nodes, bsdf = new_mat("Mat_Gobo_Disc")
    bsdf.inputs["Base Color"].default_value = (0.95, 0.97, 1.0, 1.0)
    bsdf.inputs["Emission Color"].default_value = (0.90, 0.95, 1.0, 1.0)
    bsdf.inputs["Emission Strength"].default_value = 1.35
    bsdf.inputs["Roughness"].default_value = 0.60
    mats["Gobo_Disc"] = mat

    # 14. Characters & Attire
    mat, nodes, bsdf = new_mat("Mat_NPC_Skin")
    bsdf.inputs["Base Color"].default_value = (0.86, 0.67, 0.54, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.55
    bsdf.inputs["Subsurface Weight"].default_value = 0.18
    mats["NPC_Skin"] = mat

    mat, nodes, bsdf = new_mat("Mat_NPC_Hair")
    bsdf.inputs["Base Color"].default_value = (0.06, 0.05, 0.05, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.45
    mats["NPC_Hair"] = mat

    mat, nodes, bsdf = new_mat("Mat_NPC_Dark_Suit")
    bsdf.inputs["Base Color"].default_value = (0.09, 0.10, 0.14, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.72
    mats["NPC_Dark_Suit"] = mat

    mat, nodes, bsdf = new_mat("Mat_NPC_Bridal_Gown")
    bsdf.inputs["Base Color"].default_value = (0.98, 0.97, 0.96, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.42
    bsdf.inputs["Sheen Weight"].default_value = 0.85
    mats["NPC_Bridal_Gown"] = mat

    mat, nodes, bsdf = new_mat("Mat_NPC_AoDai_Red")
    bsdf.inputs["Base Color"].default_value = (0.72, 0.08, 0.16, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.38
    bsdf.inputs["Sheen Weight"].default_value = 0.90
    mats["NPC_AoDai_Red"] = mat

    mat, nodes, bsdf = new_mat("Mat_NPC_Guest_Blue")
    bsdf.inputs["Base Color"].default_value = (0.22, 0.32, 0.52, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.65
    mats["NPC_Guest_Blue"] = mat

    return mats


# ==================== GEOMETRY & UV BUILDERS ====================
def add_box(name, size, loc, rot=(0, 0, 0), mat=None, col=None):
    bpy.ops.mesh.primitive_cube_add(size=1.0, location=loc, rotation=rot)
    obj = bpy.context.active_object
    obj.name = name
    obj.scale = size
    bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
    if mat:
        obj.data.materials.append(mat)
    if col:
        link_exclusively(obj, col)
    return obj


def add_cylinder(name, radius, depth, loc, rot=(0, 0, 0), vertices=24, mat=None, col=None):
    bpy.ops.mesh.primitive_cylinder_add(
        radius=radius,
        depth=depth,
        location=loc,
        rotation=rot,
        vertices=vertices,
    )
    obj = bpy.context.active_object
    obj.name = name
    if mat:
        obj.data.materials.append(mat)
    if col:
        link_exclusively(obj, col)
    return obj


def add_sphere(name, radius, loc, scale=(1, 1, 1), segments=16, rings=10, mat=None, col=None):
    bpy.ops.mesh.primitive_uv_sphere_add(
        segments=segments,
        ring_count=rings,
        radius=radius,
        location=loc,
    )
    obj = bpy.context.active_object
    obj.name = name
    if scale != (1, 1, 1):
        obj.scale = scale
        bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
    if mat:
        obj.data.materials.append(mat)
    if col:
        link_exclusively(obj, col)
    return obj


def add_planar_quad_z(name, width, length, loc, mat, col, u_repeat=1.0, v_repeat=1.0):
    """Creates a horizontal floor/ceiling quad facing +Z with clean planar UVs."""
    mesh = bpy.data.meshes.new(name)
    obj = bpy.data.objects.new(name, mesh)
    col.objects.link(obj)
    hw, hl = width * 0.5, length * 0.5
    verts = [(-hw, -hl, 0.0), (hw, -hl, 0.0), (hw, hl, 0.0), (-hw, hl, 0.0)]
    faces = [(0, 1, 2, 3)]
    mesh.from_pydata(verts, [], faces)
    uv_layer = mesh.uv_layers.new(name="UVMap")
    uvs = [(0.0, 0.0), (u_repeat, 0.0), (u_repeat, v_repeat), (0.0, v_repeat)]
    for loop_idx, uv in enumerate(uvs):
        uv_layer.data[loop_idx].uv = uv
    mesh.update()
    obj.location = loc
    if mat:
        obj.data.materials.append(mat)
    return obj


def add_textured_board_panel(name, width, height, loc, rot=(0, 0, 0), mat=None, frame_mat=None, col=None, thickness=0.04):
    """
    Creates a framed vertical display board/LED screen whose front face (-Y local)
    has an exact 0..1 upright planar UV map (zero distortion or side-face stretching!).
    """
    parent_frame = None
    if frame_mat:
        parent_frame = add_box(
            f"{name}_Frame",
            (width + 0.05, thickness, height + 0.05),
            loc,
            rot,
            frame_mat,
            col,
        )

    mesh = bpy.data.meshes.new(f"{name}_Surface")
    surf_obj = bpy.data.objects.new(f"{name}_Surface", mesh)
    col.objects.link(surf_obj)

    hw, hh = width * 0.5, height * 0.5
    y_front = -(thickness * 0.5 + 0.003)
    # Front quad facing -Y (audience view): bottom-left, bottom-right, top-right, top-left
    verts = [
        (-hw, y_front, -hh),
        (hw, y_front, -hh),
        (hw, y_front, hh),
        (-hw, y_front, hh),
    ]
    faces = [(0, 1, 2, 3)]
    mesh.from_pydata(verts, [], faces)
    uv_layer = mesh.uv_layers.new(name="UVMap")
    uvs = [(0.0, 0.0), (1.0, 0.0), (1.0, 1.0), (0.0, 1.0)]
    for i, uv in enumerate(uvs):
        uv_layer.data[i].uv = uv
    mesh.update()

    if mat:
        surf_obj.data.materials.append(mat)
    if parent_frame:
        surf_obj.parent = parent_frame
    else:
        surf_obj.location = loc
        surf_obj.rotation_euler = rot
    return surf_obj


def add_pleated_curtain(name, x0, x1, y_base, z0, z1, pleat_depth, pleat_count, mat, col):
    """Builds a realistic vertically pleated curtain mesh with sinusoidal folds."""
    mesh = bpy.data.meshes.new(name)
    obj = bpy.data.objects.new(name, mesh)
    col.objects.link(obj)

    verts = []
    faces = []
    steps = max(12, pleat_count * 4)
    for i in range(steps + 1):
        t = i / float(steps)
        x = x0 + (x1 - x0) * t
        y = y_base + math.sin(t * pleat_count * 2.0 * math.pi) * (pleat_depth * 0.5)
        verts.append((x, y, z0))
        verts.append((x, y, z1))

    for i in range(steps):
        i0 = i * 2
        faces.append((i0, i0 + 2, i0 + 3, i0 + 1))

    mesh.from_pydata(verts, [], faces)
    for poly in mesh.polygons:
        poly.use_smooth = True
    mesh.update()
    if mat:
        obj.data.materials.append(mat)
    return obj


def add_catenary_swag(name, pt_a, pt_b, sag, width, mat, col, segments=18, along_y=False):
    """Creates a smooth catenary draped fabric ribbon hanging between pt_a and pt_b."""
    mesh = bpy.data.meshes.new(name)
    obj = bpy.data.objects.new(name, mesh)
    col.objects.link(obj)

    verts = []
    faces = []
    hw = width * 0.5
    for i in range(segments + 1):
        t = i / float(segments)
        x = pt_a[0] + (pt_b[0] - pt_a[0]) * t
        y = pt_a[1] + (pt_b[1] - pt_a[1]) * t
        z = pt_a[2] + (pt_b[2] - pt_a[2]) * t - sag * 4.0 * t * (1.0 - t)
        if along_y:
            verts.append((x - hw, y, z))
            verts.append((x + hw, y, z))
        else:
            verts.append((x, y - hw, z))
            verts.append((x, y + hw, z))

    for i in range(segments):
        i0 = i * 2
        faces.append((i0, i0 + 2, i0 + 3, i0 + 1))

    mesh.from_pydata(verts, [], faces)
    for poly in mesh.polygons:
        poly.use_smooth = True
    mesh.update()
    if mat:
        obj.data.materials.append(mat)
    return obj


def add_floral_tower(name, base_xyz, height, radius, mats, col, with_blossoms=True):
    """Builds an organic multi-cluster floral column matching the video's lush arches."""
    bx, by, bz = base_xyz
    # Internal stand
    add_cylinder(f"{name}_Stand", 0.06, height * 0.85, (bx, by, bz + height * 0.425), mat=mats["Gold_Brass"], col=col)

    # Deterministic golden-angle spiral of organic flower clusters
    cluster_count = 18
    for i in range(cluster_count):
        frac = i / float(cluster_count - 1)
        z = bz + 0.18 + frac * (height - 0.25)
        ang = i * 2.39996
        r_off = radius * (0.45 + 0.35 * math.sin(i * 1.7)) * (1.0 - 0.25 * frac)
        cx = bx + math.cos(ang) * r_off
        cy = by + math.sin(ang) * r_off
        c_rad = radius * (0.58 - 0.18 * frac)

        if frac < 0.38:
            fmat = mats["Rose_Cream"] if (i % 2 == 0) else mats["Rose_Crimson"]
        elif frac < 0.72:
            fmat = mats["Rose_Crimson"]
        else:
            fmat = mats["Blossom_Pink"] if with_blossoms else mats["Rose_Cream"]

        add_sphere(
            f"{name}_Cluster_{i}",
            c_rad,
            (cx, cy, z),
            scale=(1.15, 0.95, 0.88),
            segments=12,
            rings=8,
            mat=fmat,
            col=col,
        )


def build_shared_tiffany_chair_mesh(mats):
    """Builds a single joined white Tiffany chair mesh with ivory cushion for 160x instancing."""
    temp_objs = []

    def part_cyl(r, d, loc, rot=(0, 0, 0), mat_key="Tiffany_White"):
        bpy.ops.mesh.primitive_cylinder_add(vertices=10, radius=r, depth=d, location=loc, rotation=rot)
        o = bpy.context.active_object
        o.data.materials.append(mats[mat_key])
        temp_objs.append(o)

    def part_box(sz, loc, mat_key="Tiffany_White"):
        bpy.ops.mesh.primitive_cube_add(size=1.0, location=loc)
        o = bpy.context.active_object
        o.scale = sz
        bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
        o.data.materials.append(mats[mat_key])
        temp_objs.append(o)

    # 4 Legs
    for lx, ly in [(-0.18, -0.18), (0.18, -0.18), (-0.18, 0.18), (0.18, 0.18)]:
        part_cyl(0.016, 0.45, (lx, ly, 0.225))
    # Seat frame & soft ivory cushion
    part_box((0.40, 0.40, 0.03), (0.0, 0.0, 0.44), "Tiffany_White")
    part_box((0.38, 0.38, 0.045), (0.0, 0.0, 0.475), "Ivory_Silk")
    # Backrest uprights (extending to Z = 0.92m)
    for bx in [-0.18, 0.18]:
        part_cyl(0.015, 0.46, (bx, 0.18, 0.68))
    # 5 Vertical Tiffany spindles
    for sx in [-0.12, -0.06, 0.0, 0.06, 0.12]:
        part_cyl(0.008, 0.38, (sx, 0.18, 0.67))
    # Horizontal backrest crossbars
    part_box((0.38, 0.024, 0.03), (0.0, 0.18, 0.89), "Tiffany_White")
    part_box((0.36, 0.018, 0.022), (0.0, 0.18, 0.62), "Tiffany_White")

    bpy.ops.object.select_all(action="DESELECT")
    for o in temp_objs:
        o.select_set(True)
    bpy.context.view_layer.objects.active = temp_objs[0]
    bpy.ops.object.join()
    joined = bpy.context.active_object
    joined.name = "Proto_Tiffany_Chair_White"
    chair_mesh = joined.data
    chair_mesh.name = "Mesh_Tiffany_Chair_White"
    bpy.data.objects.remove(joined, do_unlink=True)
    return chair_mesh


def build_shared_banquet_table_mesh(mats):
    """Builds a joined Ø1.50m banquet table with draped cloth & 10-guest porcelain settings."""
    temp_objs = []

    def add_p_cyl(r, d, loc, verts=24, mat_key="Ivory_Silk"):
        bpy.ops.mesh.primitive_cylinder_add(vertices=verts, radius=r, depth=d, location=loc)
        o = bpy.context.active_object
        o.data.materials.append(mats[mat_key])
        temp_objs.append(o)

    # Draped tablecloth & top surface
    add_p_cyl(0.76, 0.74, (0.0, 0.0, 0.37), 32, "Ivory_Silk")
    add_p_cyl(0.75, 0.03, (0.0, 0.0, 0.755), 32, "Porcelain")
    # Center Lazy Susan turntable & low floral/Serving centerpiece
    add_p_cyl(0.38, 0.02, (0.0, 0.0, 0.78), 24, "Porcelain")
    add_p_cyl(0.14, 0.12, (0.0, 0.0, 0.85), 16, "Rose_Crimson")

    # 10 Place settings around perimeter (R = 0.56m) matching f16
    for i in range(10):
        ang = (2.0 * math.pi * i) / 10.0
        px = math.cos(ang) * 0.56
        py = math.sin(ang) * 0.56
        # Dinner plate + soup bowl
        add_p_cyl(0.105, 0.012, (px, py, 0.776), 14, "Porcelain")
        add_p_cyl(0.055, 0.045, (px, py, 0.802), 12, "Porcelain")
        # Beverage can/bottle slightly inward-right
        bx = math.cos(ang + 0.14) * 0.44
        by = math.sin(ang + 0.14) * 0.44
        add_p_cyl(0.032, 0.115, (bx, by, 0.825), 10, "Drink_BlueCan")

    bpy.ops.object.select_all(action="DESELECT")
    for o in temp_objs:
        o.select_set(True)
    bpy.context.view_layer.objects.active = temp_objs[0]
    bpy.ops.object.join()
    joined = bpy.context.active_object
    table_mesh = joined.data
    table_mesh.name = "Mesh_Banquet_Table_150cm"
    bpy.data.objects.remove(joined, do_unlink=True)
    return table_mesh


def add_3d_nt_monogram(name, center_xyz, scale_h, mat, col):
    """Builds the sculpted 3D 'NT' monogram emblem seen on the stage backdrop and photobooth."""
    cx, cy, cz = center_xyz
    s = scale_h
    bar_w = 0.09 * s
    depth = 0.06 * s

    # Letter N (Left vertical, Right vertical, Diagonal)
    add_box(f"{name}_N_Left", (bar_w, depth, s), (cx - 0.36 * s, cy, cz), mat=mat, col=col)
    add_box(f"{name}_N_Right", (bar_w, depth, s), (cx + 0.02 * s, cy, cz), mat=mat, col=col)
    diag_len = math.hypot(0.38 * s, s * 0.92)
    diag_ang = math.atan2(0.38 * s, s * 0.92)
    add_box(f"{name}_N_Diag", (bar_w * 0.95, depth, diag_len), (cx - 0.17 * s, cy - 0.01, cz), (0, -diag_ang, 0), mat=mat, col=col)

    # Letter T (Interlocked slightly lower-right as in the video's "NT" logo!)
    t_h = s * 0.88
    t_cz = cz - 0.08 * s
    add_box(f"{name}_T_Stem", (bar_w, depth, t_h), (cx + 0.24 * s, cy - 0.015, t_cz), mat=mat, col=col)
    add_box(f"{name}_T_TopBar", (0.48 * s, depth, bar_w * 0.95), (cx + 0.24 * s, cy - 0.015, t_cz + t_h * 0.5), mat=mat, col=col)


def add_crystal_chandelier(name, loc, mats, col_lighting):
    """Builds a multi-arm white & crystal chandelier with warm illumination."""
    lx, ly, lz = loc
    add_cylinder(f"{name}_Chain", 0.02, 0.85, (lx, ly, lz + 0.425), mat=mats["Gold_Brass"], col=col_lighting)
    add_cylinder(f"{name}_Core", 0.12, 0.42, (lx, ly, lz - 0.05), vertices=16, mat=mats["Tiffany_White"], col=col_lighting)
    add_cylinder(f"{name}_TierUpper", 0.56, 0.14, (lx, ly, lz - 0.02), vertices=20, mat=mats["Crystal_Glass"], col=col_lighting)
    add_cylinder(f"{name}_TierMid", 0.38, 0.18, (lx, ly, lz - 0.18), vertices=16, mat=mats["Crystal_Glass"], col=col_lighting)
    add_sphere(f"{name}_Pendant", 0.08, (lx, ly, lz - 0.34), mat=mats["Crystal_Glass"], col=col_lighting)

    # 6 glowing candle bulbs around upper ring
    for b_i in range(6):
        ang = (2.0 * math.pi * b_i) / 6.0
        bx = lx + math.cos(ang) * 0.48
        by = ly + math.sin(ang) * 0.48
        add_cylinder(f"{name}_Candle_{b_i}", 0.025, 0.09, (bx, by, lz + 0.08), vertices=8, mat=mats["Chandelier_Bulb"], col=col_lighting)

    light_d = bpy.data.lights.new(f"{name}_Point", type="POINT")
    light_d.energy = 320.0
    light_d.color = (1.0, 0.91, 0.76)
    light_d.shadow_soft_size = 0.45
    light_o = bpy.data.objects.new(f"{name}_Point", light_d)
    light_o.location = (lx, ly, lz - 0.25)
    col_lighting.objects.link(light_o)
    return light_o


# ==================== ARTICULATED ANIMATED CHARACTERS ====================
def add_animated_npc(name, role, loc, yaw_deg, phase_offset, mats, col_npc, seated=False):
    """
    Builds a well-proportioned articulated human character with hierarchical limbs
    and a 72-frame (3.0s) seamless looping keyframe animation offset by phase_offset.
    """
    rad = math.radians(yaw_deg)
    root_empty = bpy.data.objects.new(name, None)
    root_empty.empty_display_type = "PLAIN_AXES"
    root_empty.empty_display_size = 0.25
    root_empty.location = loc
    root_empty.rotation_euler = (0.0, 0.0, rad)
    col_npc.objects.link(root_empty)

    if role == "BRIDE":
        outfit_mat = mats["NPC_Bridal_Gown"]
    elif role in ("RECEPTIONIST", "USHER", "GUEST_AODAI"):
        outfit_mat = mats["NPC_AoDai_Red"]
    elif role in ("WAITER", "STAFF"):
        outfit_mat = mats["Tiffany_White"]
    elif role == "GUEST_BLUE":
        outfit_mat = mats["NPC_Guest_Blue"]
    else:
        outfit_mat = mats["NPC_Dark_Suit"]

    base_z = -0.32 if seated else 0.0

    # Lower body / Skirt / Trousers
    if role == "BRIDE":
        lower = add_cylinder(f"{name}_GownSkirt", 0.36, 0.92, (0, 0, base_z + 0.46), vertices=20, mat=outfit_mat, col=col_npc)
    elif role in ("RECEPTIONIST", "USHER", "GUEST_AODAI"):
        lower = add_cylinder(f"{name}_AoDaiPanels", 0.20, 0.88, (0, 0, base_z + 0.44), vertices=16, mat=outfit_mat, col=col_npc)
    else:
        lower = add_box(f"{name}_Trousers", (0.28, 0.18, 0.82), (0, 0, base_z + 0.41), mat=mats["NPC_Dark_Suit"], col=col_npc)
    lower.parent = root_empty

    # Upper Torso (Animated breathing/bowing pivot at waist Z = base_z + 0.85)
    torso = add_cylinder(f"{name}_Torso", 0.16, 0.50, (0, 0, base_z + 1.10), vertices=16, mat=outfit_mat, col=col_npc)
    torso.parent = root_empty

    # Shoulders
    shoulders = add_sphere(f"{name}_Shoulders", 0.21, (0, 0, 0.20), scale=(1.0, 0.55, 0.42), mat=outfit_mat, col=col_npc)
    shoulders.parent = torso

    # Head & Hair (Parented to torso at +0.38m)
    head = add_sphere(f"{name}_Head", 0.105, (0, 0, 0.38), scale=(0.92, 0.98, 1.12), mat=mats["NPC_Skin"], col=col_npc)
    head.parent = torso
    hair = add_sphere(f"{name}_Hair", 0.112, (0, 0.015, 0.03), scale=(0.95, 1.02, 1.05), mat=mats["NPC_Hair"], col=col_npc)
    hair.parent = head

    # Left & Right Arms (Parented to torso at shoulder height)
    arm_l = add_cylinder(f"{name}_ArmL", 0.042, 0.48, (-0.21, -0.04, 0.02), (math.radians(15), 0, math.radians(8)), vertices=10, mat=outfit_mat, col=col_npc)
    arm_r = add_cylinder(f"{name}_ArmR", 0.042, 0.48, (0.21, -0.04, 0.02), (math.radians(18), 0, math.radians(-8)), vertices=10, mat=outfit_mat, col=col_npc)
    arm_l.parent = torso
    arm_r.parent = torso

    if role == "BRIDE":
        bouquet = add_sphere(f"{name}_Bouquet", 0.11, (0.0, -0.22, -0.06), mat=mats["Rose_Cream"], col=col_npc)
        bouquet.parent = torso

    # Keyframe 72-frame (3.0s) looping animation with phase offset so NPCs never move in unison
    frames = [1, 19, 37, 55, 72]
    for idx, f in enumerate(frames):
        ang = (2.0 * math.pi * (idx / 4.0)) + phase_offset
        if role in ("RECEPTIONIST", "USHER"):
            # Polite welcoming bow & arm wave
            torso.rotation_euler = (math.radians(6.0 * math.sin(ang)), 0.0, math.radians(4.0 * math.cos(ang)))
            arm_r.rotation_euler = (math.radians(35.0 + 18.0 * math.sin(ang)), 0.0, math.radians(-12.0))
        elif role in ("DRUMMER", "KEYBOARD", "GUITAR"):
            # Active musical rhythm
            torso.rotation_euler = (math.radians(4.0 * math.sin(ang * 2.0)), math.radians(3.0 * math.cos(ang)), 0.0)
            arm_l.rotation_euler = (math.radians(45.0 + 22.0 * math.sin(ang * 2.0)), 0.0, 0.0)
            arm_r.rotation_euler = (math.radians(45.0 - 22.0 * math.sin(ang * 2.0)), 0.0, 0.0)
        else:
            # Subtle lifelike conversation & breathing sway
            torso.rotation_euler = (math.radians(2.2 * math.sin(ang)), 0.0, math.radians(5.0 * math.cos(ang)))
            arm_r.rotation_euler = (math.radians(20.0 + 10.0 * math.sin(ang)), 0.0, math.radians(-8.0))

        torso.keyframe_insert(data_path="rotation_euler", frame=f)
        arm_l.keyframe_insert(data_path="rotation_euler", frame=f)
        arm_r.keyframe_insert(data_path="rotation_euler", frame=f)

    return root_empty


# ==================== MAIN W03 VENUE BUILDER ====================
def build_w03_venue():
    scene = reset_scene()
    cols = build_hierarchy()
    mats = create_pbr_materials()

    # 1. Ground, Green Turf Dining Wings & Slate Blue-Grey Carpets (f01, f05, f16, f18)
    add_planar_quad_z("Floor_Exterior_Turf", 26.0, 38.0, (0.0, -3.0, 0.0), mats["Turf_Grass"], cols["GROUND"], 14.0, 20.0)
    add_planar_quad_z("Floor_Foyer_SlateCarpet", 18.0, 4.7, (0.0, -15.85, 0.012), mats["Carpet_Slate"], cols["GROUND"], 6.0, 2.0)
    add_planar_quad_z("Floor_Central_Aisle_220cm", 2.20, 18.7, (0.0, -4.15, 0.015), mats["Carpet_Slate"], cols["AISLE"], 1.2, 9.0)

    # 2. Steel Truss Portal Frames (7 bays across Y = -18.0m .. +12.0m, span 18.0m, eave 4.5m, ridge 6.2m)
    bay_ys = [-18.0, -13.5, -8.5, -3.5, 1.5, 6.8, 12.0]
    rafter_angle = math.atan2(1.70, 9.0)
    rafter_len = math.hypot(9.0, 1.70)
    for idx, y_pos in enumerate(bay_ys):
        add_box(f"Truss_Col_L_{idx}", (0.30, 0.30, 4.50), (-8.95, y_pos, 2.25), mat=mats["Truss_Silver"], col=cols["ROOF_AND_STRUCTURE"])
        add_box(f"Truss_Col_R_{idx}", (0.30, 0.30, 4.50), (8.95, y_pos, 2.25), mat=mats["Truss_Silver"], col=cols["ROOF_AND_STRUCTURE"])
        add_box(f"Truss_Rafter_L_{idx}", (rafter_len, 0.24, 0.24), (-4.5, y_pos, 5.35), (0.0, -rafter_angle, 0.0), mats["Truss_Silver"], cols["ROOF_AND_STRUCTURE"])
        add_box(f"Truss_Rafter_R_{idx}", (rafter_len, 0.24, 0.24), (4.5, y_pos, 5.35), (0.0, rafter_angle, 0.0), mats["Truss_Silver"], cols["ROOF_AND_STRUCTURE"])

    for x_p, z_p in [(-8.95, 4.50), (0.0, 6.20), (8.95, 4.50)]:
        add_box(f"Truss_Purlin_{int(x_p)}", (0.22, 30.2, 0.22), (x_p, -3.0, z_p), mat=mats["Truss_Silver"], col=cols["ROOF_AND_STRUCTURE"])

    # Blackout Starlight Side Walls & Pitched Roof Liner (f03, f05, f18)
    add_box("Wall_Starlight_Left", (0.08, 30.0, 4.48), (-8.85, -3.0, 2.24), mat=mats["Starlight_Liner"], col=cols["ARCHITECTURE"])
    add_box("Wall_Starlight_Right", (0.08, 30.0, 4.48), (8.85, -3.0, 2.24), mat=mats["Starlight_Liner"], col=cols["ARCHITECTURE"])
    add_box("Wall_Starlight_Back", (17.8, 0.08, 5.80), (0.0, 11.95, 2.90), mat=mats["Starlight_Liner"], col=cols["ARCHITECTURE"])

    roof_l = add_box("Roof_Starlight_Left", (rafter_len, 30.0, 0.04), (-4.5, -3.0, 5.42), (0.0, -rafter_angle, 0.0), mats["Starlight_Liner"], cols["ROOF_AND_STRUCTURE"])
    roof_r = add_box("Roof_Starlight_Right", (rafter_len, 30.0, 0.04), (4.5, -3.0, 5.42), (0.0, rafter_angle, 0.0), mats["Starlight_Liner"], cols["ROOF_AND_STRUCTURE"])

    # 3. Foyer-to-Hall Crimson Pleated Curtain Wall at Y = -13.5m & 3.20m x 3.15m Portal (f01, f05, f09)
    add_pleated_curtain("Foyer_Curtain_Left", -8.85, -1.60, -13.50, 0.0, 4.45, 0.14, 18, mats["Crimson_Velvet"], cols["ENTRANCE"])
    add_pleated_curtain("Foyer_Curtain_Right", 1.60, 8.85, -13.50, 0.0, 4.45, 0.14, 18, mats["Crimson_Velvet"], cols["ENTRANCE"])
    add_pleated_curtain("Foyer_Portal_Valance", -1.65, 1.65, -13.46, 3.15, 4.45, 0.12, 9, mats["Crimson_Velvet"], cols["ENTRANCE"])
    # White ornamental header plaque above portal (f01)
    add_box("Foyer_Portal_WhitePlaque", (1.18, 0.05, 0.42), (0.0, -13.38, 3.82), mat=mats["Porcelain"], col=cols["ENTRANCE"])

    # Cascading Floral Arch Columns flanking portal (f01)
    add_floral_tower("Portal_FloralTower_Left", (-1.75, -13.78, 0.0), 3.35, 0.48, mats, cols["FLOWERS"], with_blossoms=True)
    add_floral_tower("Portal_FloralTower_Right", (1.75, -13.78, 0.0), 3.35, 0.48, mats, cols["FLOWERS"], with_blossoms=True)

    # Foyer Left: Reception Gallery Table + Stepped Welcome Boards + Couple Portrait Easel (f05)
    add_box("Reception_Table_Cloth", (2.60, 0.78, 0.76), (-5.45, -14.45, 0.38), mat=mats["Ivory_Silk"], col=cols["ENTRANCE"])
    add_box("Reception_Table_Runner", (0.38, 0.82, 0.77), (-4.35, -14.45, 0.385), mat=mats["Crimson_Velvet"], col=cols["ENTRANCE"])
    add_box("Reception_LuckyMoneyBox", (0.44, 0.32, 0.36), (-6.35, -14.40, 0.94), mat=mats["Porcelain"], col=cols["ENTRANCE"])
    for pf_i, pf_x in enumerate([-5.75, -5.45, -5.15, -4.85, -4.55]):
        add_box(f"Reception_PhotoFrame_{pf_i}", (0.16, 0.04, 0.22), (pf_x, -14.32, 0.87), (math.radians(-12), 0, math.radians(8)), mats["Porcelain"], cols["ENTRANCE"])
    add_floral_tower("Reception_FloorRoseCluster", (-5.95, -13.98, 0.0), 0.72, 0.28, mats, cols["FLOWERS"], with_blossoms=False)

    # Stepped Terracotta Welcome Boards with planar UVs (f05, f12)
    add_textured_board_panel(
        "Welcome_Board_Left",
        width=0.84,
        height=1.52,
        loc=(-3.55, -14.05, 0.76),
        rot=(0, 0, math.radians(4)),
        mat=mats["Welcome_Left"],
        frame_mat=mats["Gold_Brass"],
        col=cols["ENTRANCE"],
    )
    add_textured_board_panel(
        "Welcome_Board_Right",
        width=0.88,
        height=1.92,
        loc=(-2.68, -13.92, 0.96),
        rot=(0, 0, math.radians(-2)),
        mat=mats["Welcome_Right"],
        frame_mat=mats["Gold_Brass"],
        col=cols["ENTRANCE"],
    )

    # Wooden Tripod Portrait Easel with Couple Canvas (f01, f05)
    add_box("Easel_Leg_L", (0.04, 0.04, 1.55), (-2.68, -14.70, 0.75), (math.radians(8), 0, math.radians(-8)), mats["Wood_Warm"], cols["ENTRANCE"])
    add_box("Easel_Leg_R", (0.04, 0.04, 1.55), (-2.22, -14.70, 0.75), (math.radians(8), 0, math.radians(8)), mats["Wood_Warm"], cols["ENTRANCE"])
    add_box("Easel_Leg_Back", (0.04, 0.04, 1.55), (-2.45, -15.05, 0.75), (math.radians(-16), 0, 0), mats["Wood_Warm"], cols["ENTRANCE"])
    add_textured_board_panel(
        "Couple_Portrait_Canvas",
        width=0.64,
        height=0.94,
        loc=(-2.45, -14.62, 0.62),
        rot=(math.radians(8), 0, 0),
        mat=mats["Couple_Canvas"],
        frame_mat=mats["Gold_Brass"],
        col=cols["ENTRANCE"],
        thickness=0.025,
    )

    # Foyer Right: 3D Red Song Hỷ Block + Photobooth Stage + 3D "NT" Monogram (f01, f09)
    add_box("Foyer_SongHy_Block", (1.22, 0.18, 1.38), (2.68, -13.68, 0.69), mat=mats["Rose_Crimson"], col=cols["ENTRANCE"])
    add_box("Photobooth_Stage_Riser", (4.60, 1.45, 0.12), (5.55, -14.32, 0.06), mat=mats["Stage_Charcoal"], col=cols["ENTRANCE"])
    add_catenary_swag("Photobooth_Valance_Swag", (3.60, -13.42, 4.10), (6.40, -13.42, 4.10), 1.15, 0.25, mats["Crimson_Velvet"], cols["ENTRANCE"])
    add_box("Photobooth_Neon_Script", (0.85, 0.04, 0.42), (5.00, -13.38, 2.95), mat=mats["Monogram_Glow"], col=cols["ENTRANCE"])
    add_3d_nt_monogram("Photobooth_NT", (5.25, -14.05, 0.66), 1.05, mats["Porcelain"], cols["ENTRANCE"])
    add_floral_tower("Photobooth_BaseFlorals", (5.15, -14.25, 0.12), 0.65, 0.45, mats, cols["FLOWERS"], with_blossoms=False)
    add_floral_tower("Photobooth_RightTower", (7.15, -14.05, 0.12), 2.85, 0.52, mats, cols["FLOWERS"], with_blossoms=True)

    # 4. Ceiling Canopy: Central 12-Petal Crimson Dome + Ivory Silk Waves + Side Swags (f01, f03, f18)
    dome_cx, dome_cy, dome_cz = 0.0, 0.50, 4.95
    for p_i in range(12):
        ang = (2.0 * math.pi * p_i) / 12.0
        ox = dome_cx + math.cos(ang) * 2.55
        oy = dome_cy + math.sin(ang) * 2.55
        ix = dome_cx + math.cos(ang) * 0.35
        iy = dome_cy + math.sin(ang) * 0.35
        add_catenary_swag(
            f"Crimson_Dome_Petal_{p_i:02d}",
            (ox, oy, dome_cz + 0.25),
            (ix, iy, dome_cz - 0.15),
            sag=0.52,
            width=0.68,
            mat=mats["Crimson_Velvet"],
            col=cols["FABRICS"],
            segments=14,
        )

    # Longitudinal Scalloped Ivory Silk Wave Drapes (forming the white wave tunnel in f01, f03, f18)
    wave_y_Anchors = [-13.4, -9.8, -6.2, -2.6, 1.0, 4.6, 8.2]
    for w_i in range(len(wave_y_Anchors) - 1):
        ya, yb = wave_y_Anchors[w_i], wave_y_Anchors[w_i + 1]
        for wx, wz, wsag in [(-1.55, 5.15, 0.72), (1.55, 5.15, 0.72), (-2.45, 4.95, 0.65), (2.45, 4.95, 0.65)]:
            add_catenary_swag(
                f"Ivory_Wave_{w_i}_{int(wx*10)}",
                (wx, ya, wz),
                (wx, yb, wz),
                sag=wsag,
                width=0.78,
                mat=mats["Ivory_Silk"],
                col=cols["FABRICS"],
                segments=14,
                along_y=True,
            )

    # Transverse Catenary Side Swags from inner wave rails to side eaves (f03, f18)
    for s_i, sy in enumerate([-11.5, -7.8, -4.2, -0.6, 3.0, 6.6]):
        add_catenary_swag(f"Side_Swag_L_{s_i}", (-2.5, sy, 4.85), (-8.75, sy, 4.35), 0.78, 0.85, mats["Ivory_Silk"], cols["FABRICS"])
        add_catenary_swag(f"Side_Swag_R_{s_i}", (2.5, sy, 4.85), (8.75, sy, 4.35), 0.78, 0.85, mats["Ivory_Silk"], cols["FABRICS"])

    # Hanging Gold Rings & White Wisteria Garlands along center ceiling (f03, f07, f15)
    for g_i, (gx, gy, gz) in enumerate([
        (-0.55, -1.5, 4.45), (0.55, -1.5, 4.45), (-0.35, 3.5, 4.55), (0.35, 3.5, 4.55),
        (-0.85, 7.2, 4.50), (0.0, 7.5, 4.55), (0.85, 7.2, 4.50),
    ]):
        add_cylinder(f"Wisteria_Strand_{g_i}", 0.025, 0.95, (gx, gy, gz), vertices=8, mat=mats["Rose_Cream"], col=cols["ORNAMENTS"])
        add_cylinder(f"Gold_Ring_Pendant_{g_i}", 0.11, 0.02, (gx, gy, gz - 0.52), (math.radians(90), 0, 0), 16, mats["Monogram_Glow"], cols["ORNAMENTS"])

    # 5. 6 Multi-Tier Crystal Chandeliers (f03, f16, f18)
    chan_lights = []
    for idx, c_loc in enumerate([
        (0.0, -8.2, 4.25),
        (0.0, 0.5, 4.20),
        (-4.7, -8.2, 4.10),
        (-4.7, -0.6, 4.10),
        (4.7, -8.2, 4.10),
        (4.7, -0.6, 4.10),
    ]):
        cl = add_crystal_chandelier(f"Chandelier_{idx+1:02d}", c_loc, mats, cols["LIGHTING"])
        chan_lights.append(cl)

    # 6. Dining Hall: 16 Instanced Ø1.50m Banquet Tables & 160 White Tiffany Chairs (f16, f18)
    proto_table_mesh = build_shared_banquet_table_mesh(mats)
    proto_chair_mesh = build_shared_tiffany_chair_mesh(mats)

    table_xs = [-6.40, -3.00, 3.00, 6.40]
    table_ys = [-10.20, -6.40, -2.60, 1.20]
    t_count = 0
    for ty in table_ys:
        for tx in table_xs:
            t_count += 1
            t_obj = bpy.data.objects.new(f"Banquet_Table_{t_count:02d}", proto_table_mesh)
            t_obj.location = (tx, ty, 0.0)
            cols["TABLES"].objects.link(t_obj)

            chair_ring_r = 1.06
            for s_i in range(10):
                ang = (2.0 * math.pi * s_i) / 10.0
                cx = tx + math.cos(ang) * chair_ring_r
                cy = ty + math.sin(ang) * chair_ring_r
                c_obj = bpy.data.objects.new(f"Tiffany_Chair_T{t_count:02d}_{s_i+1:02d}", proto_chair_mesh)
                c_obj.location = (cx, cy, 0.0)
                c_obj.rotation_euler = (0.0, 0.0, ang + math.pi / 2.0)
                cols["CHAIRS"].objects.link(c_obj)

    # 4 Industrial Evaporative Air Coolers along side walls (f03, f16, f18)
    for idx, (ax, ay) in enumerate([(-8.20, -8.3), (-8.20, -0.7), (8.20, -8.3), (8.20, -0.7)]):
        add_box(f"AirCooler_Tank_{idx+1}", (0.72, 0.65, 1.10), (ax, ay, 0.55), mat=mats["Porcelain"], col=cols["ARCHITECTURE"])
        add_box(f"AirCooler_Top_{idx+1}", (0.70, 0.62, 0.62), (ax, ay, 1.41), mat=mats["Rose_Crimson"], col=cols["ARCHITECTURE"])

    # 7. Ceremony Catwalk & Main Stage (+5.0m to +11.8m, matching f04, f07, f15, f16)
    add_box("Stage_Aisle_Step", (2.40, 0.45, 0.22), (0.0, 5.05, 0.11), mat=mats["Stage_Charcoal"], col=cols["STAGE"])
    add_box("Stage_Catwalk_Runway", (2.80, 3.20, 0.45), (0.0, 6.85, 0.225), mat=mats["Stage_Charcoal"], col=cols["STAGE"])
    add_box("Stage_Main_Platform", (10.40, 3.40, 0.65), (0.0, 10.15, 0.325), mat=mats["Stage_Charcoal"], col=cols["STAGE"])

    # Gobo floor light pattern discs on catwalk & stage front (f15, f16)
    add_cylinder("Stage_Gobo_Disc_1", 0.95, 0.008, (0.0, 6.35, 0.455), vertices=24, mat=mats["Gobo_Disc"], col=cols["STAGE"])
    add_cylinder("Stage_Gobo_Disc_2", 0.95, 0.008, (0.0, 8.85, 0.655), vertices=24, mat=mats["Gobo_Disc"], col=cols["STAGE"])

    # Low Red & White Rose Hedges flanking Catwalk (f15, f16)
    for hy in [5.55, 6.35, 7.15, 7.95]:
        for hx in [-1.38, 1.38]:
            add_sphere(f"Catwalk_Hedge_White_{int(hx)}_{int(hy*10)}", 0.28, (hx, hy, 0.62), mat=mats["Rose_Cream"], col=cols["FLOWERS"])
            add_sphere(f"Catwalk_Hedge_Red_{int(hx)}_{int(hy*10)}", 0.26, (hx * 0.92, hy + 0.32, 0.68), mat=mats["Rose_Crimson"], col=cols["FLOWERS"])

    # Pleated Crimson Velvet Stage Backdrop + Glowing 3D "NT" Monogram (f07, f08, f15)
    add_pleated_curtain("Stage_Backdrop_Left", -4.80, -1.65, 11.65, 0.65, 4.85, 0.14, 10, mats["Crimson_Velvet"], cols["BACKDROP"])
    add_pleated_curtain("Stage_Backdrop_Right", 1.65, 4.80, 11.65, 0.65, 4.85, 0.14, 10, mats["Crimson_Velvet"], cols["BACKDROP"])
    add_pleated_curtain("Stage_Backdrop_CenterArch", -1.75, 1.75, 11.78, 0.65, 4.85, 0.10, 12, mats["Crimson_Velvet"], cols["BACKDROP"])
    add_3d_nt_monogram("Stage_Monogram_NT", (0.0, 11.55, 3.65), 0.92, mats["Monogram_Glow"], cols["BACKDROP"])

    # Lush Asymmetric Stage Floral Towers framing center arch (f07, f15)
    add_floral_tower("Stage_ArchFloral_Left", (-1.75, 11.15, 0.65), 2.55, 0.45, mats, cols["FLOWERS"], with_blossoms=True)
    add_floral_tower("Stage_ArchFloral_Right", (1.75, 11.15, 0.65), 2.85, 0.48, mats, cols["FLOWERS"], with_blossoms=True)

    # Stage Left: Crimson Draped Pedestal + 2-Tier Wedding Cake Stand (f07)
    add_box("Stage_Cake_Pedestal", (0.95, 0.85, 0.85), (-3.35, 9.15, 1.075), mat=mats["Crimson_Velvet"], col=cols["STAGE"])
    add_cylinder("Stage_Cake_Stand", 0.04, 0.45, (-3.35, 9.15, 1.72), mat=mats["Porcelain"], col=cols["STAGE"])
    add_cylinder("Stage_Cake_Lower", 0.22, 0.14, (-3.18, 9.10, 1.62), mat=mats["Blossom_Pink"], col=cols["STAGE"])
    add_cylinder("Stage_Cake_Upper", 0.18, 0.14, (-3.50, 9.18, 1.92), mat=mats["Porcelain"], col=cols["STAGE"])

    # Stage Right: Crimson Draped Pedestal + 5-Tier Champagne Glass Pyramid + Betel Trays (f07, f15)
    add_box("Stage_Champagne_Pedestal", (1.05, 0.85, 0.85), (3.35, 9.15, 1.075), mat=mats["Crimson_Velvet"], col=cols["STAGE"])
    for t_i, r_tier in enumerate([0.36, 0.28, 0.20, 0.13, 0.06]):
        add_cylinder(f"Champagne_Tier_{t_i}", r_tier, 0.09, (3.35, 9.15, 1.55 + t_i * 0.095), vertices=16, mat=mats["Crystal_Glass"], col=cols["STAGE"])
    # Wedding Betel/Gift Trays (Mâm tráp cưới) in front of stage right (f15)
    add_cylinder("Wedding_Tray_1", 0.28, 0.55, (2.25, 7.65, 0.52), mat=mats["Rose_Crimson"], col=cols["STAGE"])
    add_cylinder("Wedding_Tray_2", 0.28, 0.65, (2.95, 7.65, 0.58), mat=mats["Rose_Crimson"], col=cols["STAGE"])

    # Live Band Instruments: 5-Piece Drum Kit (Stage Right) & Keyboard (Stage Left) (f07, f15)
    add_cylinder("Band_Drum_Bass", 0.30, 0.38, (2.15, 10.35, 0.95), (math.radians(90), 0, 0), 20, mats["Stage_Charcoal"], cols["STAGE"])
    add_cylinder("Band_Drum_Snare", 0.16, 0.12, (1.82, 10.05, 1.18), mat=mats["Porcelain"], col=cols["STAGE"])
    add_cylinder("Band_Drum_Tom1", 0.14, 0.14, (2.02, 10.28, 1.32), mat=mats["Stage_Charcoal"], col=cols["STAGE"])
    add_cylinder("Band_Drum_Tom2", 0.15, 0.15, (2.30, 10.28, 1.32), mat=mats["Stage_Charcoal"], col=cols["STAGE"])
    add_cylinder("Band_Cymbal_L", 0.22, 0.012, (1.72, 10.35, 1.55), mat=mats["Gold_Brass"], col=cols["STAGE"])
    add_cylinder("Band_Cymbal_R", 0.24, 0.012, (2.58, 10.35, 1.58), mat=mats["Gold_Brass"], col=cols["STAGE"])

    add_box("Band_Keyboard_Stand", (0.05, 0.42, 0.72), (-2.35, 10.15, 1.01), mat=mats["Truss_Silver"], col=cols["STAGE"])
    add_box("Band_Keyboard_Body", (1.15, 0.32, 0.09), (-2.35, 10.15, 1.40), mat=mats["Stage_Charcoal"], col=cols["STAGE"])

    # Dual 16:9 Stage LED Screens (3.20m x 1.80m) with crisp planar UVs & metadata (Part VI & XVII.8)
    led_l = add_textured_board_panel(
        "LED_Screen_Left_16x9",
        width=3.20,
        height=1.80,
        loc=(-6.35, 10.35, 2.45),
        rot=(0, 0, math.radians(-12)),
        mat=mats["LED_Screen"],
        frame_mat=mats["Stage_Charcoal"],
        col=cols["LED_SCREENS"],
        thickness=0.10,
    )
    led_l["mayhen_screen_id"] = "LED_STAGE_LEFT"
    led_l["aspect_ratio"] = "16:9"

    led_r = add_textured_board_panel(
        "LED_Screen_Right_16x9",
        width=3.20,
        height=1.80,
        loc=(6.35, 10.35, 2.45),
        rot=(0, 0, math.radians(12)),
        mat=mats["LED_Screen"],
        frame_mat=mats["Stage_Charcoal"],
        col=cols["LED_SCREENS"],
        thickness=0.10,
    )
    led_r["mayhen_screen_id"] = "LED_STAGE_RIGHT"
    led_r["aspect_ratio"] = "16:9"

    # Stage Overhead Lighting Truss + Moving-Head Beam Cones (f04, f07, f15)
    add_box("Stage_Light_Truss", (9.60, 0.22, 0.22), (0.0, 10.85, 4.75), mat=mats["Truss_Silver"], col=cols["LIGHTING"])
    for px in [-3.8, -3.0, -2.2, -1.4, -0.6, 0.6, 1.4, 2.2, 3.0, 3.8]:
        add_sphere(f"Stage_ParLamp_{int(px*10)}", 0.09, (px, 10.75, 4.62), mat=mats["Chandelier_Bulb"], col=cols["LIGHTING"])

    # Subtle translucent stage beam cones (Top down & Floor moving heads)
    for bx, rot_z in [(-2.6, 16), (-1.1, 6), (1.1, -6), (2.6, -16)]:
        bpy.ops.mesh.primitive_cone_add(
            vertices=16,
            radius1=1.25,
            radius2=0.08,
            depth=4.5,
            location=(bx, 9.6, 2.55),
            rotation=(math.radians(14), 0, math.radians(rot_z)),
        )
        cone = bpy.context.active_object
        cone.name = f"Stage_BeamCone_{int(bx*10)}"
        cone.data.materials.append(mats["Stage_Beam_Cone"])
        cone.visible_shadow = False
        link_exclusively(cone, cols["LIGHTING"])

    # 8. Articulated Animated Demo Character NPCs (Never blocking camera center axis!)
    npcs_spec = [
        ("NPC_Bride", "BRIDE", (-0.38, 9.55, 0.65), 0.0, 0.0, False, cols["STAFF"]),
        ("NPC_Groom", "GROOM", (0.38, 9.55, 0.65), 0.0, 0.6, False, cols["STAFF"]),
        ("NPC_Receptionist_01", "RECEPTIONIST", (-5.20, -15.05, 0.012), 180.0, 1.1, False, cols["STAFF"]),
        ("NPC_Usher_Portal", "USHER", (-2.15, -12.40, 0.015), -25.0, 1.7, False, cols["STAFF"]),
        ("NPC_Waiter_Hall", "WAITER", (1.65, -5.20, 0.015), 35.0, 2.3, False, cols["STAFF"]),
        ("NPC_Staff_Prep", "STAFF", (-4.60, -4.50, 0.015), -60.0, 2.9, False, cols["STAFF"]),
        ("NPC_Band_Drummer", "DRUMMER", (2.15, 10.75, 0.65), 10.0, 0.4, True, cols["STAFF"]),
        ("NPC_Band_Keyboardist", "KEYBOARD", (-2.35, 10.55, 0.65), -15.0, 1.4, False, cols["STAFF"]),
        ("NPC_Band_Guitarist", "GUITAR", (-3.55, 10.25, 0.65), -20.0, 2.1, False, cols["STAFF"]),
        # Seated Banquet Guests across front/mid tables (matching f01, f03, f18)
        ("NPC_Guest_01", "GUEST_BLUE", (-1.95, -6.40, 0.0), -90.0, 0.3, True, cols["GUEST_NPCS"]),
        ("NPC_Guest_02", "GUEST_AODAI", (-3.00, -5.35, 0.0), 0.0, 1.2, True, cols["GUEST_NPCS"]),
        ("NPC_Guest_03", "GUEST_BLUE", (1.95, -6.40, 0.0), 90.0, 2.0, True, cols["GUEST_NPCS"]),
        ("NPC_Guest_04", "GUEST_AODAI", (3.00, -5.35, 0.0), 0.0, 2.7, True, cols["GUEST_NPCS"]),
        ("NPC_Guest_05", "GUEST_BLUE", (-1.95, -2.60, 0.0), -90.0, 0.9, True, cols["GUEST_NPCS"]),
        ("NPC_Guest_06", "GUEST_AODAI", (1.95, -2.60, 0.0), 90.0, 1.8, True, cols["GUEST_NPCS"]),
    ]
    for n_name, n_role, n_loc, n_yaw, n_phase, is_seated, target_col in npcs_spec:
        add_animated_npc(n_name, n_role, n_loc, n_yaw, n_phase, mats, target_col, seated=is_seated)

    # 9. Walkable Navigation & Interaction Zone Metadata
    nav = add_box("NavBounds_MainWalkable", (16.8, 29.0, 0.02), (0.0, -3.5, 0.005), mat=None, col=cols["NAVIGATION"])
    nav.hide_render = True
    nav.hide_viewport = True
    for z_name, z_loc, z_sz in [
        ("Zone_Foyer_Reception", (-4.8, -14.5, 1.0), (4.5, 2.2, 2.0)),
        ("Zone_Foyer_Photobooth", (5.5, -14.3, 1.0), (4.6, 2.2, 2.0)),
        ("Zone_Ceremony_Stage", (0.0, 9.2, 1.5), (10.4, 4.5, 3.0)),
    ]:
        z_obj = add_box(z_name, z_sz, z_loc, mat=None, col=cols["INTERACTION_ZONES"])
        z_obj.hide_render = True
        z_obj.hide_viewport = True

    # 10. Comprehensive Foyer, Hall & Stage Lighting System + 5 Time-of-Day Presets
    world = bpy.data.worlds.new("W03_World")
    scene.world = world
    bg_node = world.node_tree.nodes.get("Background")

    sun_data = bpy.data.lights.new("Sun_Exterior", type="SUN")
    sun_obj = bpy.data.objects.new("Sun_Exterior", sun_data)
    cols["LIGHTING"].objects.link(sun_obj)

    # Foyer & Interior Area Fill Lights (ensuring foyer welcome boards, photobooth & dining hall are well-lit!)
    fill_lights = []
    fill_specs = [
        ("Fill_Foyer_Left", (-3.8, -16.2, 3.85), (math.radians(28), 0, 0), 420.0, 4.5),
        ("Fill_Foyer_Right_Photobooth", (4.8, -16.2, 3.85), (math.radians(28), 0, 0), 460.0, 4.8),
        ("Fill_Foyer_Center_Portal", (0.0, -15.8, 3.95), (math.radians(22), 0, 0), 380.0, 4.0),
        ("Fill_Hall_South", (0.0, -9.5, 4.45), (0, 0, 0), 420.0, 7.5),
        ("Fill_Hall_Mid", (0.0, -3.5, 4.45), (0, 0, 0), 420.0, 7.5),
        ("Fill_Hall_North", (0.0, 2.5, 4.45), (0, 0, 0), 420.0, 7.5),
        ("Fill_Stage_Front", (0.0, 6.2, 4.35), (math.radians(35), 0, 0), 680.0, 5.5),
    ]
    for f_name, f_loc, f_rot, f_energy, f_size in fill_specs:
        ad = bpy.data.lights.new(f_name, type="AREA")
        ad.energy = f_energy
        ad.size = f_size
        ad.color = (1.0, 0.95, 0.88)
        ao = bpy.data.objects.new(f_name, ad)
        ao.location = f_loc
        ao.rotation_euler = f_rot
        cols["LIGHTING"].objects.link(ao)
        fill_lights.append((ao, f_energy))

    def apply_lighting_preset(preset_key):
        if preset_key == "DAWN_05H":
            bg_node.inputs["Color"].default_value = (0.18, 0.24, 0.42, 1.0)
            bg_node.inputs["Strength"].default_value = 0.55
            sun_data.energy = 1.4
            sun_data.color = (1.0, 0.68, 0.48)
            sun_obj.rotation_euler = (math.radians(78), math.radians(12), math.radians(35))
            scale_int = 0.65
        elif preset_key == "MORNING_08H":
            bg_node.inputs["Color"].default_value = (0.68, 0.82, 0.98, 1.0)
            bg_node.inputs["Strength"].default_value = 1.10
            sun_data.energy = 3.8
            sun_data.color = (1.0, 0.96, 0.88)
            sun_obj.rotation_euler = (math.radians(58), math.radians(15), math.radians(25))
            scale_int = 0.85
        elif preset_key == "NOON_12H":
            bg_node.inputs["Color"].default_value = (0.85, 0.93, 1.0, 1.0)
            bg_node.inputs["Strength"].default_value = 1.45
            sun_data.energy = 5.2
            sun_data.color = (1.0, 0.99, 0.96)
            sun_obj.rotation_euler = (math.radians(18), math.radians(5), math.radians(10))
            scale_int = 1.00
        elif preset_key == "SUNSET_17H":
            bg_node.inputs["Color"].default_value = (0.68, 0.34, 0.22, 1.0)
            bg_node.inputs["Strength"].default_value = 0.78
            sun_data.energy = 2.6
            sun_data.color = (1.0, 0.52, 0.24)
            sun_obj.rotation_euler = (math.radians(76), math.radians(-18), math.radians(-35))
            scale_int = 1.05
        else:  # NIGHT_20H (Gala Celebration mode)
            bg_node.inputs["Color"].default_value = (0.04, 0.05, 0.09, 1.0)
            bg_node.inputs["Strength"].default_value = 0.25
            sun_data.energy = 0.2
            sun_data.color = (0.6, 0.7, 1.0)
            sun_obj.rotation_euler = (math.radians(45), 0, 0)
            scale_int = 1.25

        for cl in chan_lights:
            cl.data.energy = 300.0 * scale_int
        for ao, base_e in fill_lights:
            ao.data.energy = base_e * scale_int

    # 11. Verification Cameras (Matching Reference Video Frames f01, f03, f05, f07, f09, f16)
    camera_specs = [
        ("Cam_PBR_01_Overview", (-12.5, -24.5, 9.5), (math.radians(64), 0.0, math.radians(-28)), 22.0),
        ("Cam_PBR_02_Foyer_Entrance", (0.0, -18.2, 1.55), (math.radians(89.5), 0.0, 0.0), 20.0),
        ("Cam_PBR_03_Aisle_To_Stage", (0.0, -9.2, 1.62), (math.radians(90.5), 0.0, 0.0), 20.0),
        ("Cam_PBR_04_Gala_Stage_Close", (0.0, 3.2, 1.55), (math.radians(91.0), 0.0, 0.0), 21.0),
        ("Cam_PBR_05_Foyer_Photobooth", (1.2, -17.2, 1.50), (math.radians(89.0), 0.0, math.radians(-36)), 21.0),
        ("Cam_PBR_06_Dining_Table", (-1.1, -8.5, 1.48), (math.radians(82.0), 0.0, math.radians(38)), 24.0),
    ]
    cam_objs = {}
    for cname, loc, rot, lens in camera_specs:
        cd = bpy.data.cameras.new(cname)
        cd.lens = lens
        co = bpy.data.objects.new(cname, cd)
        co.location = loc
        co.rotation_euler = rot
        cols["CAMERAS"].objects.link(co)
        cam_objs[cname] = co

    # Save Master Editable .blend File
    os.makedirs(os.path.dirname(BLEND_OUT), exist_ok=True)
    os.makedirs(os.path.dirname(GLB_OUT), exist_ok=True)
    os.makedirs(RENDER_DIR, exist_ok=True)

    scene.camera = cam_objs["Cam_PBR_02_Foyer_Entrance"]
    apply_lighting_preset("NIGHT_20H")
    bpy.ops.wm.save_as_mainfile(filepath=BLEND_OUT)
    print("SAVED_MASTER_BLEND:", BLEND_OUT)

    # Export WebGL-Ready GLB with Instanced Meshes & Animations
    bpy.ops.export_scene.gltf(
        filepath=GLB_OUT,
        export_format="GLB",
        export_apply=True,
        export_yup=True,
        export_materials="EXPORT",
        export_cameras=True,
        export_lights=True,
        export_animations=True,
    )
    print("EXPORTED_WEBGL_GLB:", GLB_OUT, f"({os.path.getsize(GLB_OUT)} bytes)")

    # 12. Render Verification Views & 5 Time-of-Day Presets via Cycles GPU CUDA
    scene.render.engine = "CYCLES"
    prefs = bpy.context.preferences.addons["cycles"].preferences
    try:
        prefs.compute_device_type = "CUDA"
        prefs.get_devices()
        for d in prefs.devices:
            d.use = True
        scene.cycles.device = "GPU"
    except Exception as exc:
        print("CUDA device note:", exc)

    scene.cycles.samples = 48
    scene.cycles.use_denoising = True
    scene.render.resolution_x = 1280
    scene.render.resolution_y = 720
    scene.render.image_settings.file_format = "PNG"

    apply_lighting_preset("NIGHT_20H")
    views_to_render = [
        ("Cam_PBR_01_Overview", "w03_01_overview.png", True),
        ("Cam_PBR_02_Foyer_Entrance", "w03_02_foyer_entrance.png", False),
        ("Cam_PBR_03_Aisle_To_Stage", "w03_03_aisle_to_stage.png", False),
        ("Cam_PBR_04_Gala_Stage_Close", "w03_04_stage_gala_night.png", False),
        ("Cam_PBR_05_Foyer_Photobooth", "w03_05_foyer_photobooth.png", False),
        ("Cam_PBR_06_Dining_Table", "w03_06_dining_table_detail.png", False),
    ]
    for cam_name, fname, cutaway_roof in views_to_render:
        roof_l.hide_render = cutaway_roof
        roof_r.hide_render = cutaway_roof
        scene.camera = cam_objs[cam_name]
        scene.render.filepath = os.path.join(RENDER_DIR, fname)
        bpy.ops.render.render(write_still=True)
        print("RENDERED_VIEW:", fname)

    roof_l.hide_render = False
    roof_r.hide_render = False

    # Render 5 Time-of-Day Lighting Presets from Foyer Entrance (showing both exterior daylight & interior)
    scene.camera = cam_objs["Cam_PBR_02_Foyer_Entrance"]
    for preset_key, fname in [
        ("DAWN_05H", "w03_lighting_01_dawn_05h.png"),
        ("MORNING_08H", "w03_lighting_02_morning_08h.png"),
        ("NOON_12H", "w03_lighting_03_noon_12h.png"),
        ("SUNSET_17H", "w03_lighting_04_sunset_17h.png"),
        ("NIGHT_20H", "w03_lighting_05_night_20h.png"),
    ]:
        apply_lighting_preset(preset_key)
        scene.render.filepath = os.path.join(RENDER_DIR, fname)
        bpy.ops.render.render(write_still=True)
        print("RENDERED_LIGHTING_PRESET:", fname)


if __name__ == "__main__":
    build_w03_venue()
