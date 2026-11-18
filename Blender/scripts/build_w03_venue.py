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
import sys
import bpy

sys.path.append(os.path.dirname(os.path.abspath(__file__)))
from human_character_builder import build_realistic_human


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

    # 1. Crimson Velvet (Rich deep royal wine-burgundy velvet, zero coral/pink washout)
    mat, nodes, bsdf = new_mat("Mat_Crimson_Velvet")
    bsdf.inputs["Base Color"].default_value = (0.135, 0.005, 0.016, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.86
    bsdf.inputs["Specular IOR Level"].default_value = 0.10
    bsdf.inputs["Sheen Weight"].default_value = 0.12
    bsdf.inputs["Sheen Tint"].default_value = (0.65, 0.04, 0.08, 1.0)
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
    mats["Welcome_Left"] = make_planar_tex_mat("Mat_Welcome_Left", "tex_welcome_left.png", emission_strength=0.22)
    mats["Welcome_Right"] = make_planar_tex_mat("Mat_Welcome_Right", "tex_welcome_right.png", emission_strength=0.22)
    mats["Couple_Canvas"] = make_planar_tex_mat("Mat_Couple_Canvas", "tex_couple_canvas.png", emission_strength=0.32)

    # 9b. High-Resolution Real Wedding Couple Portraits, QR Check-In Standee, Royal Red Carpet & Dragon-Phoenix Gate
    for wp_idx in range(1, 7):
        mats[f"Wedding_Photo_{wp_idx:02d}"] = make_planar_tex_mat(
            f"Mat_Wedding_Photo_{wp_idx:02d}",
            f"tex_wedding_photo_{wp_idx:02d}.png",
            emission_strength=0.35,
        )

    mats["QR_Checkin_Standee"] = make_planar_tex_mat(
        "Mat_QR_Checkin_Standee",
        "tex_qr_checkin_standee.png",
        emission_strength=0.55,
    )

    # Royal Red Carpet Runner with White & Gold Greek-Key Borders
    mat, nodes, bsdf = new_mat("Mat_Red_Carpet_Royal")
    rc_path = os.path.join(TEX_DIR, "tex_red_carpet_royal.png")
    if os.path.exists(rc_path):
        img_node = nodes.new("ShaderNodeTexImage")
        img_node.image = bpy.data.images.load(rc_path)
        mat.node_tree.links.new(img_node.outputs["Color"], bsdf.inputs["Base Color"])
    else:
        bsdf.inputs["Base Color"].default_value = (0.62, 0.03, 0.06, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.92
    bsdf.inputs["Specular IOR Level"].default_value = 0.12
    bsdf.inputs["Sheen Weight"].default_value = 0.35
    mats["Red_Carpet_Royal"] = mat

    # Deep Royal Burgundy Draped Velvet Texture
    mat, nodes, bsdf = new_mat("Mat_Velvet_Burgundy")
    bv_path = os.path.join(TEX_DIR, "tex_velvet_burgundy.png")
    if os.path.exists(bv_path):
        img_node = nodes.new("ShaderNodeTexImage")
        img_node.image = bpy.data.images.load(bv_path)
        mat.node_tree.links.new(img_node.outputs["Color"], bsdf.inputs["Base Color"])
    else:
        bsdf.inputs["Base Color"].default_value = (0.30, 0.012, 0.035, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.85
    bsdf.inputs["Specular IOR Level"].default_value = 0.12
    bsdf.inputs["Sheen Weight"].default_value = 0.14
    mats["Velvet_Burgundy"] = mat

    # Sculpted White Jade / Porcelain for 3D Dragon & Phoenix Relief Accents
    mat, nodes, bsdf = new_mat("Mat_Sculpted_White_Jade")
    bsdf.inputs["Base Color"].default_value = (0.98, 0.98, 0.99, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.28
    bsdf.inputs["Subsurface Weight"].default_value = 0.15
    bsdf.inputs["Emission Color"].default_value = (0.98, 0.97, 0.95, 1.0)
    bsdf.inputs["Emission Strength"].default_value = 0.18
    mats["Sculpted_White_Jade"] = mat

    # Dragon & Phoenix Entrance Gate RGBA Cutout Material
    mat, nodes, bsdf = new_mat("Mat_Dragon_Phoenix_Gate")
    gate_rgba_path = os.path.join(TEX_DIR, "tex_dragon_phoenix_gate_rgba.png")
    if os.path.exists(gate_rgba_path):
        img_node = nodes.new("ShaderNodeTexImage")
        img_node.image = bpy.data.images.load(gate_rgba_path)
        img_node.extension = "CLIP"
        mat.node_tree.links.new(img_node.outputs["Color"], bsdf.inputs["Base Color"])
        mat.node_tree.links.new(img_node.outputs["Alpha"], bsdf.inputs["Alpha"])
        mat.node_tree.links.new(img_node.outputs["Color"], bsdf.inputs["Emission Color"])
        bsdf.inputs["Emission Strength"].default_value = 0.62
    bsdf.inputs["Roughness"].default_value = 0.85
    bsdf.inputs["Specular IOR Level"].default_value = 0.08
    if hasattr(mat, "blend_method"):
        mat.blend_method = "CLIP"
    if hasattr(mat, "alpha_threshold"):
        mat.alpha_threshold = 0.35
    mats["Dragon_Phoenix_Gate"] = mat

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
    bsdf.inputs["Base Color"].default_value = (0.24, 0.006, 0.018, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.80
    bsdf.inputs["Specular IOR Level"].default_value = 0.10
    bsdf.inputs["Sheen Weight"].default_value = 0.08
    bsdf.inputs["Sheen Tint"].default_value = (0.60, 0.04, 0.06, 1.0)
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

    # 13. Subtle Translucent Stage Beam Cone & Warm Gobo Disc
    mat, nodes, bsdf = new_mat("Mat_Stage_Beam_Cone")
    bsdf.inputs["Base Color"].default_value = (1.0, 0.96, 0.88, 1.0)
    bsdf.inputs["Emission Color"].default_value = (1.0, 0.94, 0.84, 1.0)
    bsdf.inputs["Emission Strength"].default_value = 0.25
    bsdf.inputs["Alpha"].default_value = 0.022
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
    """Builds an organic smooth-shaded multi-petal floral column (ruby crimson roses, cream hydrangeas & eucalyptus)."""
    bx, by, bz = base_xyz
    add_cylinder(f"{name}_Stand", 0.055, height * 0.85, (bx, by, bz + height * 0.425), mat=mats["Gold_Brass"], col=col)

    cluster_count = 24
    for i in range(cluster_count):
        frac = i / float(cluster_count - 1)
        z = bz + 0.16 + frac * (height - 0.22)
        ang = i * 2.39996
        r_off = radius * (0.42 + 0.34 * math.sin(i * 1.7)) * (1.0 - 0.22 * frac)
        cx = bx + math.cos(ang) * r_off
        cy = by + math.sin(ang) * r_off
        c_rad = radius * (0.42 - 0.10 * frac)

        if i % 5 == 0:
            fmat = mats["Foliage_Green"]
        elif frac < 0.40:
            fmat = mats["Rose_Crimson"] if (i % 2 == 0) else mats["Rose_Cream"]
        elif frac < 0.78:
            fmat = mats["Rose_Crimson"]
        else:
            fmat = mats["Rose_Cream"] if with_blossoms else mats["Rose_Crimson"]

        sp = add_sphere(
            f"{name}_Cluster_{i}",
            c_rad,
            (cx, cy, z),
            scale=(1.15, 0.95, 0.85),
            segments=12,
            rings=8,
            mat=fmat,
            col=col,
        )
        for poly in sp.data.polygons:
            poly.use_smooth = True


def build_shared_tiffany_chair_mesh(mats):
    """Builds a smooth-shaded white Tiffany chair mesh with ivory cushion & wine-burgundy bow for 160x instancing."""
    temp_objs = []

    def part_cyl(r, d, loc, rot=(0, 0, 0), mat_key="Tiffany_White"):
        bpy.ops.mesh.primitive_cylinder_add(vertices=12, radius=r, depth=d, location=loc, rotation=rot)
        o = bpy.context.active_object
        for poly in o.data.polygons:
            poly.use_smooth = True
        o.data.materials.append(mats[mat_key])
        temp_objs.append(o)

    def part_box(sz, loc, rot=(0, 0, 0), mat_key="Tiffany_White"):
        bpy.ops.mesh.primitive_cube_add(size=1.0, location=loc, rotation=rot)
        o = bpy.context.active_object
        o.scale = sz
        bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
        o.data.materials.append(mats[mat_key])
        temp_objs.append(o)

    for lx, ly in [(-0.18, -0.18), (0.18, -0.18), (-0.18, 0.18), (0.18, 0.18)]:
        part_cyl(0.016, 0.45, (lx, ly, 0.225))
    part_box((0.40, 0.40, 0.03), (0.0, 0.0, 0.44), mat_key="Tiffany_White")
    part_box((0.38, 0.38, 0.048), (0.0, 0.0, 0.476), mat_key="Ivory_Silk")
    for bx in [-0.18, 0.18]:
        part_cyl(0.015, 0.46, (bx, 0.18, 0.68))
    for sx in [-0.12, -0.06, 0.0, 0.06, 0.12]:
        part_cyl(0.008, 0.38, (sx, 0.18, 0.67))
    part_box((0.38, 0.024, 0.03), (0.0, 0.18, 0.89), mat_key="Tiffany_White")
    part_box((0.36, 0.018, 0.022), (0.0, 0.18, 0.62), mat_key="Tiffany_White")
    # Rich Deep Burgundy Velvet Sash Band & Bow on Chair Backrest
    part_box((0.39, 0.035, 0.065), (0.0, 0.185, 0.76), mat_key="Crimson_Velvet")
    part_box((0.14, 0.045, 0.055), (0.0, 0.205, 0.76), mat_key="Gold_Brass")
    part_box((0.065, 0.025, 0.28), (-0.035, 0.205, 0.61), (0, math.radians(8), 0), mat_key="Crimson_Velvet")
    part_box((0.065, 0.025, 0.28), (0.035, 0.205, 0.61), (0, math.radians(-8), 0), mat_key="Crimson_Velvet")

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
    """Builds a smooth-shaded Ø1.50m Royal Banquet Table with burgundy velvet drop, 5-arm gold candelabra & 10 luxury place settings."""
    temp_objs = []

    def add_p_cyl(r, d, loc, verts=24, mat_key="Ivory_Silk"):
        bpy.ops.mesh.primitive_cylinder_add(vertices=verts, radius=r, depth=d, location=loc)
        o = bpy.context.active_object
        for poly in o.data.polygons:
            poly.use_smooth = True
        o.data.materials.append(mats[mat_key])
        temp_objs.append(o)

    def add_p_sphere(r, loc, scale=(1, 1, 1), mat_key="Rose_Crimson"):
        bpy.ops.mesh.primitive_uv_sphere_add(segments=10, ring_count=8, radius=r, location=loc)
        o = bpy.context.active_object
        if scale != (1, 1, 1):
            o.scale = scale
            bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
        for poly in o.data.polygons:
            poly.use_smooth = True
        o.data.materials.append(mats[mat_key])
        temp_objs.append(o)

    # Draped floor-length ivory tablecloth + Deep Burgundy Velvet Skirt Collar + Gold rim
    add_p_cyl(0.76, 0.74, (0.0, 0.0, 0.37), 32, "Ivory_Silk")
    add_p_cyl(0.766, 0.14, (0.0, 0.0, 0.68), 32, "Crimson_Velvet")
    add_p_cyl(0.75, 0.025, (0.0, 0.0, 0.755), 32, "Porcelain")
    # Center Lazy Susan glass turntable with gold rim
    add_p_cyl(0.40, 0.015, (0.0, 0.0, 0.775), 24, "Gold_Brass")
    add_p_cyl(0.38, 0.018, (0.0, 0.0, 0.782), 24, "Porcelain")

    # 5-Arm 24K Gold Candelabra + Delicate Crimson Rose & Ivory Hydrangea Centerpiece
    add_p_cyl(0.11, 0.03, (0.0, 0.0, 0.80), 16, "Gold_Brass")
    add_p_cyl(0.020, 0.50, (0.0, 0.0, 1.05), 12, "Gold_Brass")
    add_p_sphere(0.085, (0.0, 0.0, 1.07), (1.15, 1.15, 0.72), "Foliage_Green")
    for fl_i in range(22):
        fl_t = fl_i / 21.0
        fl_ang = fl_i * 2.39996
        fl_r = 0.135 * math.sqrt(max(0.04, 1.0 - fl_t * 0.86))
        fl_z = 1.035 + fl_t * 0.135
        fl_mat = "Rose_Crimson" if (fl_i % 5 != 2) else "Rose_Cream"
        fl_rad = 0.031 if fl_mat == "Rose_Crimson" else 0.023
        add_p_sphere(fl_rad, (math.cos(fl_ang) * fl_r, math.sin(fl_ang) * fl_r, fl_z), (1.08, 1.0, 0.84), fl_mat)

    add_p_cyl(0.012, 0.15, (0.0, 0.0, 1.36), 8, "Porcelain")
    add_p_sphere(0.022, (0.0, 0.0, 1.44), (0.8, 0.8, 1.4), "Chandelier_Bulb")
    for c_i in range(4):
        c_ang = (math.pi * 0.5) * c_i
        cx = math.cos(c_ang) * 0.18
        cy = math.sin(c_ang) * 0.18
        add_p_cyl(0.010, 0.12, (cx, cy, 1.28), 8, "Gold_Brass")
        add_p_cyl(0.011, 0.11, (cx, cy, 1.36), 8, "Porcelain")
        add_p_sphere(0.020, (cx, cy, 1.43), (0.8, 0.8, 1.4), "Chandelier_Bulb")

    # 10 Luxury Place Settings around perimeter (R = 0.56m)
    for i in range(10):
        ang = (2.0 * math.pi * i) / 10.0
        px = math.cos(ang) * 0.56
        py = math.sin(ang) * 0.56
        add_p_cyl(0.122, 0.008, (px, py, 0.772), 16, "Gold_Brass")
        add_p_cyl(0.105, 0.012, (px, py, 0.780), 14, "Porcelain")
        add_p_cyl(0.055, 0.042, (px, py, 0.804), 12, "Porcelain")
        add_p_sphere(0.032, (px, py, 0.832), (1.0, 1.0, 0.7), "Rose_Crimson")
        bx = math.cos(ang + 0.16) * 0.43
        by = math.sin(ang + 0.16) * 0.43
        add_p_cyl(0.028, 0.12, (bx, by, 0.83), 10, "Crystal_Glass")
        dx = math.cos(ang - 0.15) * 0.43
        dy = math.sin(ang - 0.15) * 0.43
        add_p_cyl(0.028, 0.105, (dx, dy, 0.82), 10, "Drink_BlueCan")

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


def add_crystal_chandelier(name, loc, mats, col_lighting, scale=1.0):
    """Builds an ornate multi-tiered Empire bell crystal chandelier matching the user's reference photo."""
    lx, ly, lz = loc
    s = scale
    add_cylinder(f"{name}_Chain", 0.018 * s, 0.95 * s, (lx, ly, lz + 0.475 * s), mat=mats["Gold_Brass"], col=col_lighting)
    add_sphere(f"{name}_TopCrown", 0.14 * s, (lx, ly, lz + 0.34 * s), (1.2, 1.2, 0.65), mat=mats["Gold_Brass"], col=col_lighting)
    # Bell-shaped crystal bead cone connecting top crown to main gold hoop
    bpy.ops.mesh.primitive_cone_add(
        vertices=24,
        radius1=0.58 * s,
        radius2=0.13 * s,
        depth=0.52 * s,
        location=(lx, ly, lz + 0.06 * s),
    )
    bell = bpy.context.active_object
    bell.name = f"{name}_EmpireBell"
    bell.data.materials.append(mats["Crystal_Glass"])
    link_exclusively(bell, col_lighting)

    # Main Ornate Gold Rim & 3 Concentric Lower Crystal Drop Tiers
    add_cylinder(f"{name}_GoldHoop", 0.60 * s, 0.06 * s, (lx, ly, lz - 0.18 * s), vertices=24, mat=mats["Gold_Brass"], col=col_lighting)
    add_cylinder(f"{name}_Tier1", 0.52 * s, 0.16 * s, (lx, ly, lz - 0.28 * s), vertices=20, mat=mats["Crystal_Glass"], col=col_lighting)
    add_cylinder(f"{name}_Tier2", 0.36 * s, 0.16 * s, (lx, ly, lz - 0.40 * s), vertices=16, mat=mats["Crystal_Glass"], col=col_lighting)
    add_cylinder(f"{name}_Tier3", 0.20 * s, 0.15 * s, (lx, ly, lz - 0.50 * s), vertices=14, mat=mats["Crystal_Glass"], col=col_lighting)
    add_sphere(f"{name}_FinialDrop", 0.075 * s, (lx, ly, lz - 0.60 * s), (0.85, 0.85, 1.35), mat=mats["Chandelier_Bulb"], col=col_lighting)

    # 8 glowing candle bulbs & hanging crystal teardrops around the main hoop
    for b_i in range(8):
        ang = (2.0 * math.pi * b_i) / 8.0
        bx = lx + math.cos(ang) * 0.55 * s
        by = ly + math.sin(ang) * 0.55 * s
        add_cylinder(f"{name}_Candle_{b_i}", 0.022 * s, 0.10 * s, (bx, by, lz - 0.10 * s), vertices=8, mat=mats["Chandelier_Bulb"], col=col_lighting)
        add_sphere(f"{name}_Drop_{b_i}", 0.032 * s, (bx * 0.98 + lx * 0.02, by * 0.98 + ly * 0.02, lz - 0.34 * s), (0.7, 0.7, 1.6), mat=mats["Crystal_Glass"], col=col_lighting)

    light_d = bpy.data.lights.new(f"{name}_Point", type="POINT")
    light_d.energy = 360.0 * s
    light_d.color = (1.0, 0.91, 0.76)
    light_d.shadow_soft_size = 0.45
    light_o = bpy.data.objects.new(f"{name}_Point", light_d)
    light_o.location = (lx, ly, lz - 0.25 * s)
    col_lighting.objects.link(light_o)
    return light_o


def build_shared_luxury_floral_mesh(mats):
    """Builds a smooth-shaded multi-petal crimson rose & ivory hydrangea arrangement for instancing."""
    temp_objs = []

    def add_fl_sphere(r, loc, scale=(1, 1, 1), mat_key="Rose_Crimson"):
        bpy.ops.mesh.primitive_uv_sphere_add(segments=12, ring_count=8, radius=r, location=loc)
        o = bpy.context.active_object
        if scale != (1, 1, 1):
            o.scale = scale
            bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
        for poly in o.data.polygons:
            poly.use_smooth = True
        o.data.materials.append(mats[mat_key])
        temp_objs.append(o)

    # Dark emerald foliage core + delicate perimeter leaves
    add_fl_sphere(0.145, (0.0, 0.0, 0.04), (1.25, 1.25, 0.72), "Foliage_Green")
    for l_i in range(16):
        ang = (2.0 * math.pi * l_i) / 16.0
        add_fl_sphere(0.056, (math.cos(ang) * 0.25, math.sin(ang) * 0.25, -0.065), (1.30, 0.55, 0.24), "Foliage_Green")

    # Dense Fibonacci spiral of 54 tight ruby crimson & dainty cream rosebuds (zero polka-dot look)
    golden_ang = 2.39996
    for i in range(54):
        t = i / 53.0
        z_off = -0.04 + t * 0.23
        r_ring = 0.245 * math.sqrt(max(0.02, 1.0 - t * 0.90))
        ang = i * golden_ang
        bx = math.cos(ang) * r_ring
        by = math.sin(ang) * r_ring
        mat_k = "Rose_Crimson" if (i % 6 != 1) else "Rose_Cream"
        rad_b = (0.041 + 0.011 * (1.0 - t)) if mat_k == "Rose_Crimson" else 0.030
        add_fl_sphere(rad_b, (bx, by, z_off), (1.08, 1.0, 0.85), mat_k)
        # Inner sculpted rosebud crown for layered petal depth
        if i % 2 == 0:
            add_fl_sphere(rad_b * 0.56, (bx * 1.04, by * 1.04, z_off + 0.024), (0.95, 0.95, 0.90), "Rose_Crimson")

    # Hanging sparkling crystal teardrop accents under the floral dome rim
    for d_i in range(8):
        ang = (2.0 * math.pi * d_i) / 8.0
        add_fl_sphere(0.024, (math.cos(ang) * 0.23, math.sin(ang) * 0.23, -0.16), (0.75, 0.75, 1.95), "Crystal_Glass")

    bpy.ops.object.select_all(action="DESELECT")
    for o in temp_objs:
        o.select_set(True)
    bpy.context.view_layer.objects.active = temp_objs[0]
    bpy.ops.object.join()
    joined = bpy.context.active_object
    fl_mesh = joined.data
    fl_mesh.name = "Mesh_Luxury_Floral_Dome"
    bpy.data.objects.remove(joined, do_unlink=True)
    return fl_mesh


def add_illuminated_crystal_floral_pedestal(name, loc_xy, mats, col_decor, col_flowers, floral_mesh=None, height=1.18):
    """Builds a glowing gold-and-crystal column topped with a smooth multi-petal floral dome & hanging crystals."""
    px, py = loc_xy
    add_box(f"{name}_Base1", (0.42, 0.42, 0.06), (px, py, 0.03), mat=mats["Gold_Brass"], col=col_decor)
    add_box(f"{name}_Base2", (0.34, 0.34, 0.06), (px, py, 0.09), mat=mats["Gold_Brass"], col=col_decor)
    for rx, ry in [(-0.13, -0.13), (0.13, -0.13), (-0.13, 0.13), (0.13, 0.13)]:
        add_cylinder(f"{name}_Rod_{int(rx*100)}_{int(ry*100)}", 0.014, height - 0.16, (px + rx, py + ry, 0.12 + (height - 0.16) * 0.5), vertices=8, mat=mats["Gold_Brass"], col=col_decor)
    add_cylinder(f"{name}_CrystalCore", 0.11, height - 0.18, (px, py, 0.12 + (height - 0.18) * 0.5), vertices=14, mat=mats["Crystal_Glass"], col=col_decor)
    add_cylinder(f"{name}_WarmGlow", 0.045, height - 0.28, (px, py, 0.16 + (height - 0.28) * 0.5), vertices=10, mat=mats["Chandelier_Bulb"], col=col_decor)
    add_box(f"{name}_Cap", (0.40, 0.40, 0.06), (px, py, height - 0.01), mat=mats["Gold_Brass"], col=col_decor)

    if floral_mesh is not None:
        fl_obj = bpy.data.objects.new(f"{name}_FloralArrangement", floral_mesh)
        fl_obj.location = (px, py, height + 0.14)
        fl_obj.rotation_euler = (0.0, 0.0, (px + py) * 0.45)
        col_flowers.objects.link(fl_obj)

    for c_i in range(6):
        ang = (2.0 * math.pi * c_i) / 6.0
        cx = px + math.cos(ang) * 0.19
        cy = py + math.sin(ang) * 0.19
        add_cylinder(f"{name}_Drop_{c_i}", 0.007, 0.24, (cx, cy, height - 0.14), vertices=6, mat=mats["Crystal_Glass"], col=col_decor)


def add_gold_easel_wedding_portrait(name, loc_xyz, yaw_deg, width, height, photo_mat, mats, col, with_easel=True):
    """Builds a large gold-framed Bride & Groom wedding portrait on a luxury golden easel with picture light."""
    lx, ly, lz = loc_xyz
    yaw_rad = math.radians(yaw_deg)
    sin_y, cos_y = math.sin(yaw_rad), math.cos(yaw_rad)

    if with_easel:
        # Luxury Gold Tripod Easel
        add_cylinder(f"{name}_EaselCenter", 0.022, lz + height * 0.62, (lx, ly, (lz + height * 0.62) * 0.5), vertices=10, mat=mats["Gold_Brass"], col=col)
        add_box(f"{name}_EaselShelf", (width + 0.16, 0.12, 0.04), (lx, ly, lz - height * 0.5 - 0.03), (0, 0, yaw_rad), mat=mats["Gold_Brass"], col=col)
        # Back support strut
        bx = lx + sin_y * 0.32
        by = ly + cos_y * 0.32
        add_cylinder(f"{name}_EaselBackLeg", 0.018, lz + height * 0.3, (bx, by, (lz + height * 0.3) * 0.48), (math.radians(-14) * cos_y, math.radians(14) * sin_y, yaw_rad), vertices=8, mat=mats["Gold_Brass"], col=col)

    # Ornate Gold Frame + High-Res Couple Wedding Portrait Panel
    board = add_textured_board_panel(
        name,
        width=width,
        height=height,
        loc=(lx, ly, lz),
        rot=(0, 0, yaw_rad),
        mat=photo_mat,
        frame_mat=mats["Gold_Brass"],
        col=col,
        thickness=0.05,
    )
    # Warm Picture Light Bar above frame
    add_box(f"{name}_PicLightArm", (0.04, 0.22, 0.03), (lx, ly, lz + height * 0.5 + 0.06), (0, 0, yaw_rad), mat=mats["Gold_Brass"], col=col)
    add_cylinder(
        f"{name}_PicLightBulb",
        0.025,
        width * 0.55,
        (lx - sin_y * 0.12, ly - cos_y * 0.12, lz + height * 0.5 + 0.06),
        (0, math.radians(90), yaw_rad),
        vertices=10,
        mat=mats["Chandelier_Bulb"],
        col=col,
    )
    return board


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

    # 1. Ground, Green Turf Courtyard/Dining Wings & Continuous Royal Red Carpet Runner
    add_planar_quad_z("Floor_Exterior_Turf", 32.0, 56.0, (0.0, -10.5, 0.0), mats["Turf_Grass"], cols["GROUND"], 18.0, 30.0)
    # Royal Red Carpet with White-Gold Greek-Key Border running from Exterior Courtyard (-30.0m) through Dragon-Phoenix Gate (-13.5m) to Stage (+5.05m)
    add_planar_quad_z("Floor_Royal_Red_Carpet", 1.96, 35.2, (0.0, -12.50, 0.018), mats["Red_Carpet_Royal"], cols["AISLE"], 1.0, 18.0)

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

    # Blackout Starlight Side Walls & Pitched Roof Liner (Main Banquet Hall Y = -13.5m .. +12.0m)
    add_box("Wall_Starlight_Left", (0.08, 25.5, 4.48), (-8.85, -0.75, 2.24), mat=mats["Starlight_Liner"], col=cols["ARCHITECTURE"])
    add_box("Wall_Starlight_Right", (0.08, 25.5, 4.48), (8.85, -0.75, 2.24), mat=mats["Starlight_Liner"], col=cols["ARCHITECTURE"])
    add_box("Wall_Starlight_Back", (17.8, 0.08, 5.80), (0.0, 11.95, 2.90), mat=mats["Starlight_Liner"], col=cols["ARCHITECTURE"])

    roof_l = add_box("Roof_Starlight_Left", (rafter_len, 26.0, 0.04), (-4.5, -0.5, 5.42), (0.0, -rafter_angle, 0.0), mats["Starlight_Liner"], cols["ROOF_AND_STRUCTURE"])
    roof_r = add_box("Roof_Starlight_Right", (rafter_len, 26.0, 0.04), (4.5, -0.5, 5.42), (0.0, rafter_angle, 0.0), mats["Starlight_Liner"], cols["ROOF_AND_STRUCTURE"])

    # Blackout Starlight Front Corner Closures & Pitched Gable Tympanum (seals front roof opening above Dragon-Phoenix Gate)
    add_box("Front_CornerWall_Left", (1.58, 0.12, 4.48), (-8.10, -13.45, 2.24), mat=mats["Starlight_Liner"], col=cols["ARCHITECTURE"])
    add_box("Front_CornerWall_Right", (1.58, 0.12, 4.48), (8.10, -13.45, 2.24), mat=mats["Starlight_Liner"], col=cols["ARCHITECTURE"])
    gable_bm = bpy.data.meshes.new("Front_Gable_Tympanum")
    gable_verts = [
        (-8.88, -13.49, 4.35), (8.88, -13.49, 4.35), (0.0, -13.49, 6.42),
        (-8.88, -13.41, 4.35), (8.88, -13.41, 4.35), (0.0, -13.41, 6.42),
    ]
    gable_faces = [
        (0, 1, 2), (3, 5, 4),
        (0, 3, 4, 1), (1, 4, 5, 2), (2, 5, 3, 0),
    ]
    gable_bm.from_pydata(gable_verts, [], gable_faces)
    gable_bm.update()
    gable_obj = bpy.data.objects.new("Front_Gable_Tympanum", gable_bm)
    gable_obj.data.materials.append(mats["Starlight_Liner"])
    cols["ROOF_AND_STRUCTURE"].objects.link(gable_obj)

    # 3. CỔNG CƯỚI RỒNG PHƯỢNG BẠCH NGỌC TRÊN NỀN NHUNG ĐỎ RƯỢU VANG (Y = -13.50m, matching user's photo 100%!)
    # Structural Burgundy Velvet Archway Frame & Backing narrowed strictly inside the 14.8m Dragon-Phoenix span
    add_pleated_curtain("Foyer_Curtain_Left", -7.35, -1.24, -13.42, 0.0, 4.35, 0.10, 14, mats["Velvet_Burgundy"], cols["ENTRANCE"])
    add_pleated_curtain("Foyer_Curtain_Right", 1.24, 7.35, -13.42, 0.0, 4.35, 0.10, 14, mats["Velvet_Burgundy"], cols["ENTRANCE"])
    add_pleated_curtain("Foyer_Portal_Valance", -1.35, 1.35, -13.40, 3.12, 4.35, 0.08, 8, mats["Velvet_Burgundy"], cols["ENTRANCE"])
    add_box("Foyer_Arch_Lining_L", (0.14, 0.22, 3.10), (-1.24, -13.44, 1.55), mat=mats["Crimson_Velvet"], col=cols["ENTRANCE"])
    add_box("Foyer_Arch_Lining_R", (0.14, 0.22, 3.10), (1.24, -13.44, 1.55), mat=mats["Crimson_Velvet"], col=cols["ENTRANCE"])

    # High-Precision 3D Relief-Displaced Dragon & Phoenix Entrance Gate (208x88 smooth emboss = 0.09m depth, razor-sharp edges!)
    disp_path = os.path.join(TEX_DIR, "tex_dragon_phoenix_gate_disp.png")
    if os.path.exists(disp_path):
        import numpy as np
        d_img = bpy.data.images.load(disp_path)
        dw, dh = d_img.size[0], d_img.size[1]
        d_raw = np.empty(dw * dh * 4, dtype=np.float32)
        d_img.pixels.foreach_get(d_raw)
        bpy.data.images.remove(d_img)
        d_arr = d_raw.reshape((dh, dw, 4))

        nx, nz = 208, 88
        gate_w, gate_h = 14.80, 4.44
        y_gate = -13.56
        g_verts = []
        g_alphas = []
        for iz in range(nz + 1):
            v = iz / float(nz)
            iy_px = min(dh - 1, max(0, int(v * (dh - 1))))
            z_pos = v * gate_h
            for ix in range(nx + 1):
                u = ix / float(nx)
                ix_px = min(dw - 1, max(0, int(u * (dw - 1))))
                h_val = float(d_arr[iy_px, ix_px, 0])
                a_val = float(d_arr[iy_px, ix_px, 3])
                x_pos = -gate_w * 0.5 + u * gate_w
                y_pos = y_gate - 0.09 * h_val
                g_verts.append((x_pos, y_pos, z_pos))
                g_alphas.append(a_val)

        g_faces = []
        g_uvs = []
        stride = nx + 1
        for iz in range(nz):
            v0 = iz / float(nz)
            v1 = (iz + 1) / float(nz)
            for ix in range(nx):
                i00 = iz * stride + ix
                i10 = i00 + 1
                i11 = (iz + 1) * stride + ix + 1
                i01 = (iz + 1) * stride + ix
                if max(g_alphas[i00], g_alphas[i10], g_alphas[i11], g_alphas[i01]) > 0.15:
                    u0 = ix / float(nx)
                    u1 = (ix + 1) / float(nx)
                    g_faces.append((i00, i10, i11, i01))
                    g_uvs.extend([(u0, v0), (u1, v0), (u1, v1), (u0, v1)])

        g_mesh = bpy.data.meshes.new("Gate_Dragon_Phoenix_3DRelief")
        g_obj = bpy.data.objects.new("Gate_Dragon_Phoenix_3DRelief", g_mesh)
        cols["ENTRANCE"].objects.link(g_obj)
        g_mesh.from_pydata(g_verts, [], g_faces)
        uv_l = g_mesh.uv_layers.new(name="UVMap")
        for l_idx, uv_pt in enumerate(g_uvs):
            uv_l.data[l_idx].uv = uv_pt
        for poly in g_mesh.polygons:
            poly.use_smooth = True
        g_mesh.update()
        g_obj.data.materials.append(mats["Dragon_Phoenix_Gate"])

    # Build Shared Smooth Multi-Petal Luxury Floral Dome Mesh for reuse across all pedestals & arches
    proto_floral_mesh = build_shared_luxury_floral_mesh(mats)

    # 3D Glowing Edison Bulb String along Top Cornice of Dragon-Phoenix Gate (matching photo!)
    for eb_i in range(28):
        t_eb = eb_i / 27.0
        eb_x = -7.15 + 14.30 * t_eb
        eb_z = 4.24 - 0.06 * math.sin(t_eb * 10.0 * math.pi)
        add_sphere(f"Gate_EdisonBulb_{eb_i:02d}", 0.036, (eb_x, -13.68, eb_z), (0.85, 0.85, 1.3), segments=10, rings=8, mat=mats["Chandelier_Bulb"], col=cols["LIGHTING"])

    # Warm Point Glow next to the Left Empire Chandelier on the Dragon-Phoenix Gate
    gate_chan_light = bpy.data.lights.new("Gate_Left_Empire_Glow", type="POINT")
    gate_chan_light.energy = 180.0
    gate_chan_light.color = (1.0, 0.91, 0.76)
    gate_chan_o = bpy.data.objects.new("Gate_Left_Empire_Glow", gate_chan_light)
    gate_chan_o.location = (-4.85, -14.10, 3.00)
    cols["LIGHTING"].objects.link(gate_chan_o)

    # Golden Rope Stanchions & Velvet Ropes framing Exterior Royal Red Carpet Approach (Y = -20.5m .. -15.2m)
    for st_i, st_y in enumerate([-20.5, -18.5, -16.5, -14.8]):
        for side_s, sx in [("L", -1.32), ("R", 1.32)]:
            add_cylinder(f"Ext_Stanchion_Base_{side_s}_{st_i}", 0.14, 0.03, (sx, st_y, 0.015), vertices=14, mat=mats["Gold_Brass"], col=cols["ORNAMENTS"])
            add_cylinder(f"Ext_Stanchion_Post_{side_s}_{st_i}", 0.024, 0.86, (sx, st_y, 0.445), vertices=12, mat=mats["Gold_Brass"], col=cols["ORNAMENTS"])
            add_sphere(f"Ext_Stanchion_Cap_{side_s}_{st_i}", 0.048, (sx, st_y, 0.90), segments=12, rings=8, mat=mats["Gold_Brass"], col=cols["ORNAMENTS"])
        if st_i < 3:
            nxt_y = [-20.5, -18.5, -16.5, -14.8][st_i + 1]
            add_catenary_swag(f"Ext_Rope_L_{st_i}", (-1.32, st_y, 0.82), (-1.32, nxt_y, 0.82), 0.16, 0.038, mats["Crimson_Velvet"], cols["ORNAMENTS"], 10, along_y=True)
            add_catenary_swag(f"Ext_Rope_R_{st_i}", (1.32, st_y, 0.82), (1.32, nxt_y, 0.82), 0.16, 0.038, mats["Crimson_Velvet"], cols["ORNAMENTS"], 10, along_y=True)

    # Exterior Floral Border Bushes framing outer turf wings (Y = -14.8m, outside the 14.8m gate wings)
    for fx_e in [-8.35, -7.85, 7.85, 8.35]:
        fl_ext = bpy.data.objects.new(f"Ext_CornerFloral_{int(fx_e*10)}", proto_floral_mesh)
        fl_ext.location = (fx_e, -14.10, 0.25)
        fl_ext.scale = (1.6, 1.6, 1.4)
        cols["FLOWERS"].objects.link(fl_ext)

    # 3b. KHÔNG GIAN NGOÀI RẠP: QUẦY LỄ TÂN QUÉT MÃ QR CHECK-IN & ĐẠI TRIỂN LÃM ẢNH CƯỚI CỠ LỚN (Y = -28.5m .. -23.0m)
    # Placed in the Exterior Welcome Courtyard so the Forecourt (Y = -22.5m .. -13.5m) gives a 100% unobstructed full-width view of the Dragon-Phoenix Gate!
    # Left Exterior Wing: QUẦY LỄ TÂN QUÉT MÃ QR CHECK-IN & MỪNG CƯỚI (X = -4.95m, Y = -25.4m)
    qr_x, qr_y = -4.95, -25.40
    add_box("QR_Reception_CarpetPad", (5.20, 3.60, 0.02), (qr_x, qr_y, 0.01), mat=mats["Crimson_Velvet"], col=cols["ENTRANCE"])
    add_box("QR_Reception_Desk_Body", (3.00, 0.90, 0.86), (qr_x, qr_y, 0.43), mat=mats["Ivory_Silk"], col=cols["ENTRANCE"])
    add_box("QR_Reception_Desk_VelvetSkirt", (3.04, 0.92, 0.42), (qr_x, qr_y, 0.65), mat=mats["Crimson_Velvet"], col=cols["ENTRANCE"])
    add_box("QR_Reception_Desk_GoldTrim", (3.08, 0.96, 0.05), (qr_x, qr_y, 0.86), mat=mats["Gold_Brass"], col=cols["ENTRANCE"])

    # Overhead Golden Floral Canopy over QR Check-In Reception Desk (pedestals flank back corners cleanly)
    for post_dx in [-1.42, 1.42]:
        add_cylinder(f"QR_Canopy_Post_{int(post_dx*10)}", 0.032, 2.60, (qr_x + post_dx, qr_y + 0.36, 1.30), mat=mats["Gold_Brass"], col=cols["ENTRANCE"])
        add_illuminated_crystal_floral_pedestal(f"QR_Canopy_FloralPost_{int(post_dx*10)}", (qr_x + post_dx * 1.18, qr_y + 0.36), mats, cols["ORNAMENTS"], cols["FLOWERS"], proto_floral_mesh, height=1.10)
    add_box("QR_Canopy_TopBar", (3.10, 0.14, 0.08), (qr_x, qr_y + 0.36, 2.60), mat=mats["Gold_Brass"], col=cols["ENTRANCE"])
    add_catenary_swag("QR_Canopy_Drape", (qr_x - 1.42, qr_y + 0.36, 2.56), (qr_x + 1.42, qr_y + 0.36, 2.56), 0.45, 0.34, mats["Crimson_Velvet"], cols["ENTRANCE"])

    # Large Illuminated Acrylic QR Check-In Standee right on the Reception Counter + Standing Floor QR Kiosk! (positive yaw = South-East toward plaza)
    add_box("QR_Desk_Standee_Base", (0.62, 0.24, 0.05), (qr_x + 0.72, qr_y - 0.16, 0.90), mat=mats["Gold_Brass"], col=cols["ENTRANCE"])
    qr_desk_board = add_textured_board_panel(
        "QR_Checkin_Desk_Standee",
        width=0.58,
        height=0.88,
        loc=(qr_x + 0.72, qr_y - 0.16, 1.36),
        rot=(0, 0, math.radians(28)),
        mat=mats["QR_Checkin_Standee"],
        frame_mat=mats["Gold_Brass"],
        col=cols["ENTRANCE"],
        thickness=0.03,
    )
    qr_desk_board["mayhen_interaction"] = "QR_CHECKIN_STANDEE"

    # Dedicated Standing Floor QR Check-In Kiosk beside the Reception Desk facing South-East toward red carpet arrivals
    add_box("QR_Floor_Kiosk_Base", (0.72, 0.48, 0.56), (qr_x + 2.02, qr_y - 0.15, 0.28), mat=mats["Gold_Brass"], col=cols["ENTRANCE"])
    add_textured_board_panel(
        "QR_Checkin_Floor_Kiosk",
        width=0.82,
        height=1.22,
        loc=(qr_x + 2.02, qr_y - 0.15, 1.18),
        rot=(0, 0, math.radians(32)),
        mat=mats["QR_Checkin_Standee"],
        frame_mat=mats["Gold_Brass"],
        col=cols["ENTRANCE"],
        thickness=0.04,
    )

    # Reception Tablet Check-in Terminal, Golden Gift Box (Thùng Mừng Cưới), Red Guestbook & 5 Tabletop Couple Photos
    add_box("QR_Tablet_Stand", (0.28, 0.20, 0.22), (qr_x + 0.05, qr_y - 0.15, 0.98), (math.radians(-25), 0, 0), mats["Gold_Brass"], cols["ENTRANCE"])
    add_box("Reception_LuckyMoneyBox", (0.48, 0.36, 0.42), (qr_x - 0.95, qr_y - 0.10, 1.08), mat=mats["Gold_Brass"], col=cols["ENTRANCE"])
    add_box("Reception_Guestbook", (0.42, 0.30, 0.03), (qr_x - 0.40, qr_y - 0.18, 0.89), mat=mats["Rose_Crimson"], col=cols["ENTRANCE"])
    for pf_i, pf_dx in enumerate([-1.28, -0.68, -0.22, 0.32, 1.22]):
        p_mat = mats[f"Wedding_Photo_{(pf_i % 6) + 1:02d}"]
        add_textured_board_panel(
            f"Reception_TablePhoto_{pf_i+1}",
            width=0.22,
            height=0.32,
            loc=(qr_x + pf_dx, qr_y + 0.16, 1.04),
            rot=(0, 0, math.radians(-10 + pf_i * 5)),
            mat=p_mat,
            frame_mat=mats["Gold_Brass"],
            col=cols["ENTRANCE"],
            thickness=0.02,
        )

    # 3 Monumental Gold-Framed Pre-Wedding Portraits flanking the QR Reception Plaza (positive yaw faces South-East toward plaza)
    add_gold_easel_wedding_portrait("Ext_Left_GrandPortrait_01", (-7.35, -24.20, 1.32), 32.0, 1.38, 2.06, mats["Wedding_Photo_02"], mats, cols["ENTRANCE"])
    add_gold_easel_wedding_portrait("Ext_Left_GrandPortrait_02", (-7.25, -26.80, 1.32), 38.0, 1.38, 2.06, mats["Wedding_Photo_04"], mats, cols["ENTRANCE"])
    add_gold_easel_wedding_portrait("Ext_Left_GrandPortrait_03", (-4.95, -28.00, 1.32), 16.0, 1.38, 2.06, mats["Wedding_Photo_05"], mats, cols["ENTRANCE"])

    # Right Exterior Wing: KHU TRIỂN LÃM ẢNH CƯỚI CỠ ĐẠI & PHOTOBOOTH SONG HỶ (X = +5.4m, Y = -25.4m)
    add_box("Photobooth_Stage_Riser", (5.40, 3.40, 0.08), (5.35, -25.40, 0.04), mat=mats["Stage_Charcoal"], col=cols["ENTRANCE"])
    add_box("Foyer_SongHy_Block", (1.28, 0.20, 1.45), (3.85, -24.55, 0.80), (0, 0, math.radians(-18)), mat=mats["Rose_Crimson"], col=cols["ENTRANCE"])
    add_3d_nt_monogram("Photobooth_NT", (5.25, -24.65, 0.78), 1.10, mats["Monogram_Glow"], cols["ENTRANCE"])
    add_textured_board_panel("Welcome_Board_Left", 0.84, 1.52, (3.25, -26.85, 0.84), (0, 0, math.radians(-24)), mats["Welcome_Left"], mats["Gold_Brass"], cols["ENTRANCE"])
    add_textured_board_panel("Welcome_Board_Right", 0.88, 1.92, (4.15, -27.35, 1.04), (0, 0, math.radians(-18)), mats["Welcome_Right"], mats["Gold_Brass"], cols["ENTRANCE"])

    # 3 Monumental Gold-Framed Pre-Wedding Portraits on the Right Exterior Gallery (negative yaw faces South-West toward plaza)
    add_gold_easel_wedding_portrait("Ext_Right_GrandPortrait_01", (7.35, -24.20, 1.32), -32.0, 1.38, 2.06, mats["Wedding_Photo_06"], mats, cols["ENTRANCE"])
    add_gold_easel_wedding_portrait("Ext_Right_GrandPortrait_02", (7.25, -26.80, 1.32), -38.0, 1.38, 2.06, mats["Wedding_Photo_01"], mats, cols["ENTRANCE"])
    add_gold_easel_wedding_portrait("Ext_Right_GrandPortrait_03", (5.45, -28.00, 1.32), -16.0, 1.38, 2.06, mats["Couple_Canvas"], mats, cols["ENTRANCE"])

    # 6 Illuminated Crystal & Smooth Rose Columns lining the Exterior Royal Red Carpet Approach (leaving open sightline around -25m .. -28m for QR showcase!)
    for ext_i, ext_y in enumerate([-29.4, -22.2, -17.6]):
        add_illuminated_crystal_floral_pedestal(f"Ext_Aisle_CrystalCol_L_{ext_i+1}", (-2.25, ext_y), mats, cols["ORNAMENTS"], cols["FLOWERS"], proto_floral_mesh, height=1.18)
        add_illuminated_crystal_floral_pedestal(f"Ext_Aisle_CrystalCol_R_{ext_i+1}", (2.25, ext_y), mats, cols["ORNAMENTS"], cols["FLOWERS"], proto_floral_mesh, height=1.18)

    # 4. Ceiling Canopy: Central 12-Petal Crimson Dome + Ivory & Burgundy Silk Waves + Side Swags (f01, f03, f18)
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

    # Longitudinal Scalloped Ivory & Burgundy Silk Wave Drapes (kept strictly behind Y = -13.1m gable)
    wave_y_Anchors = [-13.1, -9.5, -5.9, -2.3, 1.3, 4.9, 8.5]
    for w_i in range(len(wave_y_Anchors) - 1):
        ya, yb = wave_y_Anchors[w_i], wave_y_Anchors[w_i + 1]
        for wx, wz, wsag, wmat in [
            (-1.55, 5.15, 0.72, mats["Ivory_Silk"]),
            (1.55, 5.15, 0.72, mats["Ivory_Silk"]),
            (-2.45, 4.95, 0.65, mats["Crimson_Velvet"]),
            (2.45, 4.95, 0.65, mats["Crimson_Velvet"]),
            (-3.45, 4.80, 0.58, mats["Ivory_Silk"]),
            (3.45, 4.80, 0.58, mats["Ivory_Silk"]),
        ]:
            add_catenary_swag(
                f"Ceiling_Wave_{w_i}_{int(wx*10)}",
                (wx, ya, wz),
                (wx, yb, wz),
                sag=wsag,
                width=0.78,
                mat=wmat,
                col=cols["FABRICS"],
                segments=14,
                along_y=True,
            )

    # Transverse Catenary Side Swags from inner wave rails to side eaves (f03, f18)
    for s_i, sy in enumerate([-11.5, -7.8, -4.2, -0.6, 3.0, 6.6]):
        s_mat = mats["Ivory_Silk"] if (s_i % 2 == 0) else mats["Crimson_Velvet"]
        add_catenary_swag(f"Side_Swag_L_{s_i}", (-2.5, sy, 4.85), (-8.75, sy, 4.35), 0.78, 0.85, s_mat, cols["FABRICS"])
        add_catenary_swag(f"Side_Swag_R_{s_i}", (2.5, sy, 4.85), (8.75, sy, 4.35), 0.78, 0.85, s_mat, cols["FABRICS"])

    # Hanging Gold Rings & White Wisteria Garlands along center ceiling (f03, f07, f15)
    for g_i, (gx, gy, gz) in enumerate([
        (-0.55, -10.5, 4.45), (0.55, -10.5, 4.45), (-0.55, -5.5, 4.45), (0.55, -5.5, 4.45),
        (-0.55, -1.5, 4.45), (0.55, -1.5, 4.45), (-0.35, 3.5, 4.55), (0.35, 3.5, 4.55),
        (-0.85, 7.2, 4.50), (0.0, 7.5, 4.55), (0.85, 7.2, 4.50),
    ]):
        add_cylinder(f"Wisteria_Strand_{g_i}", 0.025, 0.95, (gx, gy, gz), vertices=8, mat=mats["Rose_Cream"], col=cols["ORNAMENTS"])
        add_cylinder(f"Gold_Ring_Pendant_{g_i}", 0.11, 0.02, (gx, gy, gz - 0.52), (math.radians(90), 0, 0), 16, mats["Monogram_Glow"], cols["ORNAMENTS"])

    # 5. 6 Multi-Tier Empire Crystal Chandeliers inside Banquet Hall
    chan_lights = [gate_chan_o]
    for idx, c_loc in enumerate([
        (0.0, -8.2, 4.25),
        (0.0, 0.5, 4.20),
        (-4.7, -8.2, 4.10),
        (-4.7, -0.6, 4.10),
        (4.7, -8.2, 4.10),
        (4.7, -0.6, 4.10),
    ]):
        cl = add_crystal_chandelier(f"Chandelier_{idx+1:02d}", c_loc, mats, cols["LIGHTING"], scale=1.0)
        chan_lights.append(cl)

    # 8 Illuminated Crystal & Smooth Rose Columns lining the Interior Royal Red Carpet Aisle
    for int_i, int_y in enumerate([-10.5, -6.5, -2.5, 1.5]):
        add_illuminated_crystal_floral_pedestal(f"Int_Aisle_CrystalCol_L_{int_i+1}", (-1.42, int_y), mats, cols["ORNAMENTS"], cols["FLOWERS"], proto_floral_mesh, height=1.22)
        add_illuminated_crystal_floral_pedestal(f"Int_Aisle_CrystalCol_R_{int_i+1}", (1.42, int_y), mats, cols["ORNAMENTS"], cols["FLOWERS"], proto_floral_mesh, height=1.22)

    # 6 Monumental Gold-Framed Wedding Portraits mounted along the Left & Right Banquet Hall Walls (+90 faces +X, -90 faces -X)
    add_gold_easel_wedding_portrait("Hall_WallPortrait_L1", (-8.52, -9.80, 2.38), 90.0, 1.52, 2.26, mats["Wedding_Photo_02"], mats, cols["ORNAMENTS"], with_easel=False)
    add_gold_easel_wedding_portrait("Hall_WallPortrait_L2", (-8.52, -4.60, 2.38), 90.0, 1.52, 2.26, mats["Wedding_Photo_04"], mats, cols["ORNAMENTS"], with_easel=False)
    add_gold_easel_wedding_portrait("Hall_WallPortrait_L3", (-8.52, 0.40, 2.38), 90.0, 1.52, 2.26, mats["Couple_Canvas"], mats, cols["ORNAMENTS"], with_easel=False)
    add_gold_easel_wedding_portrait("Hall_WallPortrait_R1", (8.52, -9.80, 2.38), -90.0, 1.52, 2.26, mats["Wedding_Photo_05"], mats, cols["ORNAMENTS"], with_easel=False)
    add_gold_easel_wedding_portrait("Hall_WallPortrait_R2", (8.52, -4.60, 2.38), -90.0, 1.52, 2.26, mats["Wedding_Photo_06"], mats, cols["ORNAMENTS"], with_easel=False)
    add_gold_easel_wedding_portrait("Hall_WallPortrait_R3", (8.52, 0.40, 2.38), -90.0, 1.52, 2.26, mats["Wedding_Photo_01"], mats, cols["ORNAMENTS"], with_easel=False)

    # 6. Dining Hall: 16 Instanced Ø1.50m Royal Banquet Tables & 160 White Tiffany Chairs with Burgundy Velvet Bows
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
    for idx, (ax, ay) in enumerate([(-8.20, -6.8), (-8.20, 3.0), (8.20, -6.8), (8.20, 3.0)]):
        add_box(f"AirCooler_Tank_{idx+1}", (0.72, 0.65, 1.10), (ax, ay, 0.55), mat=mats["Porcelain"], col=cols["ARCHITECTURE"])
        add_box(f"AirCooler_Top_{idx+1}", (0.70, 0.62, 0.62), (ax, ay, 1.41), mat=mats["Crimson_Velvet"], col=cols["ARCHITECTURE"])

    # 7. Ceremony Catwalk & Main Stage (+5.0m to +11.8m) with Gold Moulding & Smooth Floral Borders
    add_box("Stage_Aisle_Step", (2.40, 0.45, 0.22), (0.0, 5.05, 0.11), mat=mats["Stage_Charcoal"], col=cols["STAGE"])
    add_box("Stage_Catwalk_Runway", (2.80, 3.20, 0.45), (0.0, 6.85, 0.225), mat=mats["Stage_Charcoal"], col=cols["STAGE"])
    add_box("Stage_Main_Platform", (11.20, 3.40, 0.65), (0.0, 10.15, 0.325), mat=mats["Stage_Charcoal"], col=cols["STAGE"])
    # 24K Gold Moulding Trim framing Catwalk & Stage Front
    add_box("Stage_Catwalk_GoldTrim_L", (0.06, 3.22, 0.06), (-1.40, 6.85, 0.44), mat=mats["Gold_Brass"], col=cols["STAGE"])
    add_box("Stage_Catwalk_GoldTrim_R", (0.06, 3.22, 0.06), (1.40, 6.85, 0.44), mat=mats["Gold_Brass"], col=cols["STAGE"])
    add_box("Stage_Platform_GoldFrontTrim", (11.24, 0.06, 0.07), (0.0, 8.44, 0.64), mat=mats["Gold_Brass"], col=cols["STAGE"])

    # Royal Red Carpet overlay along Catwalk to Center Stage
    add_planar_quad_z("Stage_Catwalk_RedCarpet", 1.85, 3.80, (0.0, 7.15, 0.46), mats["Red_Carpet_Royal"], cols["STAGE"], 1.0, 2.0)

    # Gobo floor light pattern discs on catwalk & stage front
    add_cylinder("Stage_Gobo_Disc_1", 0.90, 0.008, (0.0, 6.35, 0.465), vertices=24, mat=mats["Gobo_Disc"], col=cols["STAGE"])
    add_cylinder("Stage_Gobo_Disc_2", 0.95, 0.008, (0.0, 8.85, 0.655), vertices=24, mat=mats["Gobo_Disc"], col=cols["STAGE"])

    # Smooth Multi-Petal Floral Hedgerows flanking Catwalk (using instanced proto_floral_mesh!)
    for hy in [5.55, 6.25, 6.95, 7.65]:
        for hx in [-1.22, 1.22]:
            ch = bpy.data.objects.new(f"Catwalk_Floral_{int(hx*10)}_{int(hy*10)}", proto_floral_mesh)
            ch.location = (hx, hy, 0.60)
            ch.scale = (0.85, 0.95, 0.80)
            ch.rotation_euler = (0.0, 0.0, hy)
            cols["FLOWERS"].objects.link(ch)

    # Pleated Deep Burgundy Velvet Stage Backdrop + Glowing 3D "NT" Monogram + Golden Filigree Double Ring Arch
    add_pleated_curtain("Stage_Backdrop_Left", -5.20, -1.65, 11.65, 0.65, 4.85, 0.14, 12, mats["Crimson_Velvet"], cols["BACKDROP"])
    add_pleated_curtain("Stage_Backdrop_Right", 1.65, 5.20, 11.65, 0.65, 4.85, 0.14, 12, mats["Crimson_Velvet"], cols["BACKDROP"])
    add_pleated_curtain("Stage_Backdrop_CenterArch", -1.75, 1.75, 11.78, 0.65, 4.85, 0.10, 12, mats["Crimson_Velvet"], cols["BACKDROP"])
    add_3d_nt_monogram("Stage_Monogram_NT", (0.0, 11.48, 3.65), 0.96, mats["Monogram_Glow"], cols["BACKDROP"])

    # Smooth Continuous 24K Gold Concentric Double Halo Ring Arch framing Stage Center + Floral Blossoms
    for ring_idx, (m_rad, m_thick) in enumerate([(1.74, 0.048), (1.58, 0.024)]):
        bpy.ops.mesh.primitive_torus_add(
            major_segments=56,
            minor_segments=14,
            major_radius=m_rad,
            minor_radius=m_thick,
            location=(0.0, 11.42, 2.55),
            rotation=(math.radians(90.0), 0.0, 0.0),
        )
        ring_obj = bpy.context.active_object
        ring_obj.name = f"Stage_GoldHaloRing_{ring_idx+1}"
        for poly in ring_obj.data.polygons:
            poly.use_smooth = True
        ring_obj.data.materials.append(mats["Gold_Brass"])
        link_exclusively(ring_obj, cols["BACKDROP"])

    for r_i in range(7):
        ang_s = math.pi * (0.08 + 0.84 * (r_i / 6.0))
        rx = math.cos(ang_s) * 1.66
        rz = 2.55 + math.sin(ang_s) * 1.66
        add_sphere(f"Stage_ArchBulb_{r_i:02d}", 0.042, (rx, 11.35, rz), segments=10, rings=8, mat=mats["Chandelier_Bulb"], col=cols["LIGHTING"])
        if r_i % 2 == 0:
            afl = bpy.data.objects.new(f"Stage_ArchCluster_{r_i:02d}", proto_floral_mesh)
            afl.location = (rx * 1.02, 11.32, rz)
            afl.scale = (0.76, 0.55, 0.76)
            cols["FLOWERS"].objects.link(afl)

    # Lush Asymmetric Stage Floral Towers framing center arch
    add_floral_tower("Stage_ArchFloral_Left", (-1.75, 11.15, 0.65), 2.75, 0.45, mats, cols["FLOWERS"], with_blossoms=True)
    add_floral_tower("Stage_ArchFloral_Right", (1.75, 11.15, 0.65), 2.95, 0.48, mats, cols["FLOWERS"], with_blossoms=True)

    # 2 Monumental Gold-Framed Stage Wedding Portraits (1.58m x 2.34m) flanking Stage Arch facing audience!
    add_gold_easel_wedding_portrait("Stage_GrandPortrait_Left", (-4.05, 11.20, 2.52), 8.0, 1.58, 2.34, mats["Wedding_Photo_02"], mats, cols["STAGE"], with_easel=False)
    add_gold_easel_wedding_portrait("Stage_GrandPortrait_Right", (4.05, 11.20, 2.52), -8.0, 1.58, 2.34, mats["Wedding_Photo_01"], mats, cols["STAGE"], with_easel=False)

    # Stage Left: Solid Deep Burgundy Velvet Pedestal + Gold Moulding + 5-Tier Royal Wedding Cake with Crimson Roses
    add_box("Stage_Cake_Pedestal", (0.98, 0.88, 0.85), (-3.35, 9.15, 1.075), mat=mats["Crimson_Velvet"], col=cols["STAGE"])
    add_box("Stage_Cake_GoldTrim", (1.02, 0.92, 0.04), (-3.35, 9.15, 1.50), mat=mats["Gold_Brass"], col=cols["STAGE"])
    add_box("Stage_Cake_GoldBase", (1.02, 0.92, 0.04), (-3.35, 9.15, 0.67), mat=mats["Gold_Brass"], col=cols["STAGE"])
    for ck_i, ck_r in enumerate([0.34, 0.27, 0.21, 0.15, 0.09]):
        ck_z = 1.58 + ck_i * 0.14
        add_cylinder(f"Stage_Cake_Tier_{ck_i+1}", ck_r, 0.13, (-3.35, 9.15, ck_z), vertices=20, mat=mats["Porcelain"], col=cols["STAGE"])
        add_cylinder(f"Stage_Cake_GoldBand_{ck_i+1}", ck_r + 0.006, 0.018, (-3.35, 9.15, ck_z - 0.05), vertices=20, mat=mats["Gold_Brass"], col=cols["STAGE"])
        add_sphere(f"Stage_Cake_Rose_{ck_i+1}", 0.042, (-3.35 + ck_r * 0.85, 9.15 - 0.08, ck_z + 0.04), mat=mats["Rose_Crimson"], col=cols["FLOWERS"])

    # Stage Right: Solid Deep Burgundy Velvet Pedestal + 5-Tier Illuminated Crystal Champagne Pyramid
    add_box("Stage_Champagne_Pedestal", (1.05, 0.85, 0.85), (3.35, 9.15, 1.075), mat=mats["Crimson_Velvet"], col=cols["STAGE"])
    add_box("Stage_Champagne_GoldBase", (1.08, 0.88, 0.04), (3.35, 9.15, 0.67), mat=mats["Gold_Brass"], col=cols["STAGE"])
    add_box("Stage_Champagne_LEDTop", (0.92, 0.74, 0.04), (3.35, 9.15, 1.50), mat=mats["Monogram_Glow"], col=cols["STAGE"])
    for t_i, r_tier in enumerate([0.36, 0.28, 0.20, 0.13, 0.06]):
        add_cylinder(f"Champagne_Tier_{t_i}", r_tier, 0.09, (3.35, 9.15, 1.56 + t_i * 0.095), vertices=16, mat=mats["Crystal_Glass"], col=cols["STAGE"])

    # Solid Velvet Table & 6 Traditional Red-Gold Wedding Betel/Gift Trays (Mâm Quả Cưới — strictly grounded!)
    add_box("Wedding_Tray_Table_Body", (1.60, 0.78, 0.72), (2.75, 7.65, 0.36), mat=mats["Crimson_Velvet"], col=cols["STAGE"])
    add_box("Wedding_Tray_Table_GoldTop", (1.64, 0.82, 0.04), (2.75, 7.65, 0.73), mat=mats["Gold_Brass"], col=cols["STAGE"])
    for tr_i, (trx, try_y) in enumerate([
        (2.15, 7.45), (2.75, 7.45), (3.35, 7.45),
        (2.15, 7.85), (2.75, 7.85), (3.35, 7.85),
    ]):
        add_cylinder(f"MamQua_Base_{tr_i+1}", 0.18, 0.14, (trx, try_y, 0.82), vertices=16, mat=mats["Gold_Brass"], col=cols["STAGE"])
        add_sphere(f"MamQua_SilkDome_{tr_i+1}", 0.19, (trx, try_y, 0.90), (1.0, 1.0, 1.15), segments=14, rings=8, mat=mats["Rose_Crimson"], col=cols["STAGE"])
        add_sphere(f"MamQua_GoldFinial_{tr_i+1}", 0.032, (trx, try_y, 1.12), mat=mats["Gold_Brass"], col=cols["STAGE"])

    # Live Band Instruments: 5-Piece Drum Kit (Stage Right) & Keyboard (Stage Left)
    add_cylinder("Band_Drum_Bass", 0.30, 0.38, (2.15, 10.35, 0.95), (math.radians(90), 0, 0), 20, mats["Stage_Charcoal"], cols["STAGE"])
    add_cylinder("Band_Drum_Snare", 0.16, 0.12, (1.82, 10.05, 1.18), mat=mats["Porcelain"], col=cols["STAGE"])
    add_cylinder("Band_Drum_Tom1", 0.14, 0.14, (2.02, 10.28, 1.32), mat=mats["Stage_Charcoal"], col=cols["STAGE"])
    add_cylinder("Band_Drum_Tom2", 0.15, 0.15, (2.30, 10.28, 1.32), mat=mats["Stage_Charcoal"], col=cols["STAGE"])
    add_cylinder("Band_Cymbal_L", 0.22, 0.012, (1.72, 10.35, 1.55), mat=mats["Gold_Brass"], col=cols["STAGE"])
    add_cylinder("Band_Cymbal_R", 0.24, 0.012, (2.58, 10.35, 1.58), mat=mats["Gold_Brass"], col=cols["STAGE"])

    add_box("Band_Keyboard_Stand", (0.05, 0.42, 0.72), (-2.35, 10.15, 1.01), mat=mats["Truss_Silver"], col=cols["STAGE"])
    add_box("Band_Keyboard_Body", (1.15, 0.32, 0.09), (-2.35, 10.15, 1.40), mat=mats["Stage_Charcoal"], col=cols["STAGE"])

    # Dual 16:9 Stage LED Screens (3.04m x 1.71m) tilted inward cleanly toward audience with 24K Gold Frames
    led_l = add_textured_board_panel(
        "LED_Screen_Left_16x9",
        width=3.04,
        height=1.71,
        loc=(-5.85, 10.15, 2.48),
        rot=(0, 0, math.radians(16)),
        mat=mats["LED_Screen"],
        frame_mat=mats["Gold_Brass"],
        col=cols["LED_SCREENS"],
        thickness=0.10,
    )
    led_l["mayhen_screen_id"] = "LED_STAGE_LEFT"
    led_l["aspect_ratio"] = "16:9"

    led_r = add_textured_board_panel(
        "LED_Screen_Right_16x9",
        width=3.04,
        height=1.71,
        loc=(5.85, 10.15, 2.48),
        rot=(0, 0, math.radians(-16)),
        mat=mats["LED_Screen"],
        frame_mat=mats["Gold_Brass"],
        col=cols["LED_SCREENS"],
        thickness=0.10,
    )
    led_r["mayhen_screen_id"] = "LED_STAGE_RIGHT"
    led_r["aspect_ratio"] = "16:9"

    # Stage Overhead Lighting Truss + Subtle Backlight Beam Shafts (tucked cleanly behind couple & performers)
    add_box("Stage_Light_Truss", (9.60, 0.22, 0.22), (0.0, 10.85, 4.75), mat=mats["Truss_Silver"], col=cols["LIGHTING"])
    for px in [-3.8, -3.0, -2.2, -1.4, -0.6, 0.6, 1.4, 2.2, 3.0, 3.8]:
        add_sphere(f"Stage_ParLamp_{int(px*10)}", 0.09, (px, 10.75, 4.62), mat=mats["Chandelier_Bulb"], col=cols["LIGHTING"])

    for bx, rot_z in [(-1.45, 8), (-0.45, 3), (0.45, -3), (1.45, -8)]:
        bpy.ops.mesh.primitive_cone_add(
            vertices=16,
            radius1=0.52,
            radius2=0.06,
            depth=4.1,
            location=(bx, 11.15, 2.75),
            rotation=(math.radians(6), 0, math.radians(rot_z)),
        )
        cone = bpy.context.active_object
        cone.name = f"Stage_BeamCone_{int(bx*10)}"
        cone.data.materials.append(mats["Stage_Beam_Cone"])
        cone.visible_shadow = False
        link_exclusively(cone, cols["LIGHTING"])

    # 8. Realistic Clothed Human Characters (Walking, Carrying Trays, Conversing, QR Check-in & Performing)
    human_specs = [
        # Stage Couple & Band
        {"name": "NPC_Bride", "role": "BRIDE", "loc": (-0.42, 9.55, 0.65), "yaw": 0.0, "phase": 0.0, "action": "TALK_STAND", "col": cols["STAFF"]},
        {"name": "NPC_Groom", "role": "GROOM", "loc": (0.42, 9.55, 0.65), "yaw": 0.0, "phase": 0.7, "action": "TALK_STAND", "col": cols["STAFF"]},
        {"name": "NPC_Band_Drummer", "role": "DRUMMER", "loc": (2.15, 10.75, 0.65), "yaw": 10.0, "phase": 0.4, "action": "BAND_PLAY", "outfit": "NAVY", "col": cols["STAFF"]},
        {"name": "NPC_Band_Keyboardist", "role": "KEYBOARD", "loc": (-2.35, 10.55, 0.65), "yaw": -12.0, "phase": 1.3, "action": "BAND_PLAY", "outfit": "NAVY", "col": cols["STAFF"]},
        {"name": "NPC_Band_Guitarist", "role": "GUITAR", "loc": (-3.05, 10.25, 0.65), "yaw": -18.0, "phase": 2.1, "action": "BAND_PLAY", "outfit": "DEFAULT", "col": cols["STAFF"]},

        # Waiters Walking & Carrying Loaded Serving Trays (Bê đồ & Đi bộ)
        {"name": "NPC_Waiter_Carry_L", "role": "WAITER_CARRY", "loc": (-2.05, -7.80, 0.015), "walk_target": (-2.05, -3.60, 0.015), "phase": 0.2, "action": "CARRY_WALK", "col": cols["STAFF"]},
        {"name": "NPC_Waiter_Carry_R", "role": "WAITER_CARRY", "loc": (2.05, -3.80, 0.015), "walk_target": (2.05, -8.20, 0.015), "phase": 1.6, "action": "CARRY_WALK", "col": cols["STAFF"]},
        {"name": "NPC_Waiter_Carry_Outer", "role": "WAITER_CARRY", "loc": (-4.70, -8.40, 0.015), "walk_target": (-4.70, -4.40, 0.015), "phase": 2.5, "action": "CARRY_WALK", "col": cols["STAFF"]},

        # Walking Usher & Arriving Guests (Đi bộ tự nhiên)
        {"name": "NPC_Usher_Walking", "role": "USHER", "loc": (-2.05, -12.60, 0.015), "walk_target": (-2.05, -9.20, 0.015), "phase": 0.9, "action": "WALK", "outfit": "CRIMSON", "col": cols["STAFF"]},
        {"name": "NPC_Guest_Walk_Aisle", "role": "MALE_SUIT", "loc": (0.45, -11.80, 0.015), "walk_target": (0.45, -8.20, 0.015), "phase": 1.8, "action": "WALK", "outfit": "NAVY", "col": cols["GUEST_NPCS"]},

        # QR Check-In Receptionist & Guest Scanning QR Code at Quầy Lễ Tân Quét Mã
        {"name": "NPC_Receptionist_01", "role": "RECEPTIONIST", "loc": (-4.85, -24.45, 0.015), "yaw": 0.0, "phase": 0.5, "action": "TALK_STAND", "outfit": "CRIMSON", "col": cols["STAFF"]},
        {"name": "NPC_Guest_Foyer_Talk", "role": "MALE_SUIT", "loc": (-3.95, -26.25, 0.015), "yaw": 155.0, "phase": 1.9, "action": "TALK_STAND", "outfit": "NAVY", "col": cols["GUEST_NPCS"]},

        # Face-to-Face Conversing Pair on Central Red Carpet Aisle
        {"name": "NPC_Guest_Talk_L", "role": "MALE_SHIRT", "loc": (-0.52, -5.20, 0.018), "yaw": 42.0, "phase": 0.3, "action": "TALK_STAND", "outfit": "SKY", "col": cols["GUEST_NPCS"]},
        {"name": "NPC_Guest_Talk_R", "role": "FEMALE_AODAI", "loc": (0.52, -5.20, 0.018), "yaw": -42.0, "phase": 1.7, "action": "TALK_STAND", "outfit": "GOLD", "col": cols["GUEST_NPCS"]},

        # Seated Banquet Guests Conversing Across Tables
        {"name": "NPC_Guest_Seated_01", "role": "MALE_SHIRT", "loc": (-1.94, -6.40, 0.0), "yaw": 90.0, "phase": 0.4, "action": "TALK_SEATED", "outfit": "CREAM", "col": cols["GUEST_NPCS"]},
        {"name": "NPC_Guest_Seated_02", "role": "FEMALE_AODAI", "loc": (-3.00, -5.34, 0.0), "yaw": 0.0, "phase": 1.2, "action": "TALK_SEATED", "outfit": "PINK", "col": cols["GUEST_NPCS"]},
        {"name": "NPC_Guest_Seated_03", "role": "MALE_SUIT", "loc": (1.94, -6.40, 0.0), "yaw": -90.0, "phase": 2.1, "action": "TALK_SEATED", "outfit": "NAVY", "col": cols["GUEST_NPCS"]},
        {"name": "NPC_Guest_Seated_04", "role": "FEMALE_AODAI", "loc": (3.00, -5.34, 0.0), "yaw": 0.0, "phase": 2.8, "action": "TALK_SEATED", "outfit": "CRIMSON", "col": cols["GUEST_NPCS"]},
    ]
    for h_spec in human_specs:
        build_realistic_human(h_spec, mats, h_spec["col"])

    # 9. Walkable Navigation & Interaction Zone Metadata
    nav = add_box("NavBounds_MainWalkable", (16.8, 42.0, 0.02), (0.0, -9.0, 0.005), mat=None, col=cols["NAVIGATION"])
    nav.hide_render = True
    nav.hide_viewport = True
    for z_name, z_loc, z_sz in [
        ("Zone_Foyer_Reception", (-4.85, -25.2, 1.0), (4.8, 3.4, 2.0)),
        ("Zone_Foyer_Photobooth", (5.35, -25.2, 1.0), (5.2, 3.4, 2.0)),
        ("Zone_Ceremony_Stage", (0.0, 9.2, 1.5), (11.2, 4.5, 3.0)),
    ]:
        z_obj = add_box(z_name, z_sz, z_loc, mat=None, col=cols["INTERACTION_ZONES"])
        z_obj.hide_render = True
        z_obj.hide_viewport = True

    # 10. Comprehensive Exterior Gate, QR Reception, Hall & Stage Lighting System
    world = bpy.data.worlds.new("W03_World")
    scene.world = world
    bg_node = world.node_tree.nodes.get("Background")

    sun_data = bpy.data.lights.new("Sun_Exterior", type="SUN")
    sun_obj = bpy.data.objects.new("Sun_Exterior", sun_data)
    cols["LIGHTING"].objects.link(sun_obj)

    fill_lights = []
    fill_specs = [
        ("Fill_Foyer_Left", (-5.0, -27.8, 3.95), (math.radians(40), 0, 0), 420.0, 5.2),
        ("Fill_Foyer_Right_Photobooth", (5.0, -27.8, 3.95), (math.radians(40), 0, 0), 420.0, 5.2),
        ("Fill_Foyer_Center_Portal", (0.0, -18.5, 4.10), (math.radians(45), 0, 0), 210.0, 6.0),
        ("Fill_Hall_South", (0.0, -9.5, 4.45), (0, 0, 0), 340.0, 7.5),
        ("Fill_Hall_Mid", (0.0, -3.5, 4.45), (0, 0, 0), 340.0, 7.5),
        ("Fill_Hall_North", (0.0, 2.5, 4.45), (0, 0, 0), 340.0, 7.5),
        ("Fill_Stage_Front", (0.0, 6.2, 4.35), (math.radians(35), 0, 0), 580.0, 6.0),
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
            bg_node.inputs["Strength"].default_value = 0.28
            sun_data.energy = 0.30
            sun_data.color = (0.7, 0.8, 1.0)
            sun_obj.rotation_euler = (math.radians(45), 0, 0)
            scale_int = 1.15

        for cl in chan_lights:
            cl.data.energy = 280.0 * scale_int
        for ao, base_e in fill_lights:
            ao.data.energy = base_e * scale_int

    # 11. Verification Cameras (Full Dragon-Phoenix Gate, QR Check-In Desk & Large Wedding Photos, Aisle, Stage, Dining, Characters)
    camera_specs = [
        ("Cam_PBR_01_Overview", (-14.5, -32.5, 11.2), (math.radians(64), 0.0, math.radians(-26)), 21.0),
        ("Cam_PBR_02_Foyer_Entrance", (0.0, -22.8, 1.85), (math.radians(90.5), 0.0, 0.0), 17.5),
        ("Cam_PBR_03_Aisle_To_Stage", (0.0, -9.2, 1.62), (math.radians(90.5), 0.0, 0.0), 20.0),
        ("Cam_PBR_04_Gala_Stage_Close", (0.0, 2.0, 1.58), (math.radians(91.0), 0.0, 0.0), 19.0),
        ("Cam_PBR_05_Foyer_Photobooth", (0.4, -28.4, 1.72), (math.radians(89.0), 0.0, math.radians(48)), 19.5),
        ("Cam_PBR_06_Dining_Table", (-1.1, -8.5, 1.48), (math.radians(82.0), 0.0, math.radians(38)), 24.0),
        ("Cam_PBR_07_Characters_Closeup", (0.0, -7.8, 1.52), (math.radians(88.5), 0.0, 0.0), 32.0),
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

    scene.frame_set(12)  # Mid-stride & active gesture frame for still renders
    apply_lighting_preset("NIGHT_20H")
    views_to_render = [
        ("Cam_PBR_02_Foyer_Entrance", "w03_02_foyer_entrance.png", False),
        ("Cam_PBR_05_Foyer_Photobooth", "w03_05_foyer_photobooth.png", False),
        ("Cam_PBR_03_Aisle_To_Stage", "w03_03_aisle_to_stage.png", False),
        ("Cam_PBR_04_Gala_Stage_Close", "w03_04_stage_gala_night.png", False),
        ("Cam_PBR_06_Dining_Table", "w03_06_dining_table_detail.png", False),
        ("Cam_PBR_07_Characters_Closeup", "w03_07_characters_closeup.png", False),
        ("Cam_PBR_01_Overview", "w03_01_overview.png", True),
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


if __name__ == "__main__":
    build_w03_venue()
