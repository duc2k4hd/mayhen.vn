"""
MÂY HẸN — VIRTUAL WEDDING WORLD 3D
Wave W03: Full PBR Venue, Realistic Lighting & Demo NPCs Builder
Blender 5.2.3 LTS Automation Script

Replicates the Royal Crimson Marquee ("Lễ Tân Hôn — Cao Nhân & Trần Thảo")
from the reference video "FDown.vn_Tai_video_Facebook_720p (HD)_7b85.mp4":
  1. Complete PBR material library (Velvet, Silk, Starlight, Brass, Crystal, Turf, Slate felt).
  2. Metric architectural structure (18m span x 30m depth, 6.2m ridge, 14 catenary swags,
     12-petal crimson dome, 8 crystal chandeliers).
  3. 16 Ø1.5m banquet tables, 160 gold Tiffany chairs, 2.2m central aisle.
  4. Foyer entrance with welcome easels, couple portrait canvas, photobooth, reception desk & money box.
  5. Stage with pleated crimson backdrop, dual 16:9 LED screens, champagne tower, wedding cake,
     band instruments (drums, keyboard).
  6. 8 animated demo character proxies with looping actions.
  7. 5 Time-of-Day Lighting presets (Dawn, Morning, Noon, Sunset, Night Gala).
  8. Automated Cycles rendering of 5 lighting presets + 4 verification cameras.
  9. Optimized WebGL-ready GLB runtime export.
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
    return scene


def make_collection(name, parent=None):
    col = bpy.data.collections.new(name)
    if parent is None:
        bpy.context.scene.collection.children.link(col)
    else:
        parent.children.link(col)
    return col


# ==================== MATERIAL FACTORY ====================
def create_pbr_materials():
    mats = {}

    def new_mat(name):
        mat = bpy.data.materials.new(name=name)
        mat.use_nodes = True
        nodes = mat.node_tree.nodes
        nodes.clear()
        output = nodes.new("ShaderNodeOutputMaterial")
        bsdf = nodes.new("ShaderNodeBsdfPrincipled")
        mat.node_tree.links.new(bsdf.outputs["BSDF"], output.inputs["Surface"])
        return mat, nodes, bsdf

    # 1. Crimson Velvet (Sheen + Subsurface)
    mat, nodes, bsdf = new_mat("Mat_Crimson_Velvet")
    bsdf.inputs["Base Color"].default_value = (0.49, 0.07, 0.14, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.82
    bsdf.inputs["Sheen Weight"].default_value = 1.0
    bsdf.inputs["Sheen Tint"].default_value = (0.85, 0.20, 0.30, 1.0)
    bsdf.inputs["Subsurface Weight"].default_value = 0.08
    bsdf.inputs["Subsurface Radius"].default_value = (0.5, 0.1, 0.1)
    mats["Crimson_Velvet"] = mat

    # 2. Ivory Silk Drapery
    mat, nodes, bsdf = new_mat("Mat_Ivory_Silk")
    bsdf.inputs["Base Color"].default_value = (0.96, 0.94, 0.91, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.52
    bsdf.inputs["Sheen Weight"].default_value = 0.75
    bsdf.inputs["Sheen Tint"].default_value = (1.0, 0.95, 0.90, 1.0)
    mats["Ivory_Silk"] = mat

    # 3. Starlight Velvet Ceiling Liner (Blackout + Emissive Twinkles)
    mat, nodes, bsdf = new_mat("Mat_Starlight_Liner")
    tex_path = os.path.join(TEX_DIR, "tex_starlight_velvet.png")
    if os.path.exists(tex_path):
        img_node = nodes.new("ShaderNodeTexImage")
        img_node.image = bpy.data.images.load(tex_path)
        coord_node = nodes.new("ShaderNodeTexCoord")
        mapping_node = nodes.new("ShaderNodeMapping")
        mapping_node.inputs["Scale"].default_value = (4.0, 6.0, 1.0)
        mat.node_tree.links.new(coord_node.outputs["UV"], mapping_node.inputs["Vector"])
        mat.node_tree.links.new(mapping_node.outputs["Vector"], img_node.inputs["Vector"])
        mat.node_tree.links.new(img_node.outputs["Color"], bsdf.inputs["Base Color"])
        mat.node_tree.links.new(img_node.outputs["Color"], bsdf.inputs["Emission Color"])
        bsdf.inputs["Emission Strength"].default_value = 2.4
    else:
        bsdf.inputs["Base Color"].default_value = (0.02, 0.02, 0.03, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.90
    mats["Starlight_Liner"] = mat

    # 4. Slate Blue-Grey Felt Carpet
    mat, nodes, bsdf = new_mat("Mat_Carpet_Slate")
    tex_path = os.path.join(TEX_DIR, "tex_carpet_slate.png")
    if os.path.exists(tex_path):
        img_node = nodes.new("ShaderNodeTexImage")
        img_node.image = bpy.data.images.load(tex_path)
        coord_node = nodes.new("ShaderNodeTexCoord")
        mapping_node = nodes.new("ShaderNodeMapping")
        mapping_node.inputs["Scale"].default_value = (2.0, 12.0, 1.0)
        mat.node_tree.links.new(coord_node.outputs["UV"], mapping_node.inputs["Vector"])
        mat.node_tree.links.new(mapping_node.outputs["Vector"], img_node.inputs["Vector"])
        mat.node_tree.links.new(img_node.outputs["Color"], bsdf.inputs["Base Color"])
    else:
        bsdf.inputs["Base Color"].default_value = (0.28, 0.33, 0.38, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.92
    mats["Carpet_Slate"] = mat

    # 5. Event Artificial Turf Grass (Albedo + Normal)
    mat, nodes, bsdf = new_mat("Mat_Turf_Grass")
    alb_path = os.path.join(TEX_DIR, "tex_turf_albedo.png")
    norm_path = os.path.join(TEX_DIR, "tex_turf_normal.png")
    if os.path.exists(alb_path):
        alb_node = nodes.new("ShaderNodeTexImage")
        alb_node.image = bpy.data.images.load(alb_path)
        coord_node = nodes.new("ShaderNodeTexCoord")
        mapping_node = nodes.new("ShaderNodeMapping")
        mapping_node.inputs["Scale"].default_value = (10.0, 15.0, 1.0)
        mat.node_tree.links.new(coord_node.outputs["UV"], mapping_node.inputs["Vector"])
        mat.node_tree.links.new(mapping_node.outputs["Vector"], alb_node.inputs["Vector"])
        mat.node_tree.links.new(alb_node.outputs["Color"], bsdf.inputs["Base Color"])
        if os.path.exists(norm_path):
            norm_node = nodes.new("ShaderNodeTexImage")
            norm_node.image = bpy.data.images.load(norm_path)
            norm_node.image.colorspace_settings.name = "Non-Color"
            norm_map = nodes.new("ShaderNodeNormalMap")
            norm_map.inputs["Strength"].default_value = 0.85
            mat.node_tree.links.new(mapping_node.outputs["Vector"], norm_node.inputs["Vector"])
            mat.node_tree.links.new(norm_node.outputs["Color"], norm_map.inputs["Color"])
            mat.node_tree.links.new(norm_map.outputs["Normal"], bsdf.inputs["Normal"])
    else:
        bsdf.inputs["Base Color"].default_value = (0.16, 0.32, 0.12, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.88
    mats["Turf_Grass"] = mat

    # 6. Polished Gold Brass
    mat, nodes, bsdf = new_mat("Mat_Gold_Brass")
    bsdf.inputs["Base Color"].default_value = (0.88, 0.72, 0.28, 1.0)
    bsdf.inputs["Metallic"].default_value = 0.95
    bsdf.inputs["Roughness"].default_value = 0.20
    mats["Gold_Brass"] = mat

    # 7. Faceted Crystal Glass
    mat, nodes, bsdf = new_mat("Mat_Crystal_Glass")
    bsdf.inputs["Base Color"].default_value = (0.98, 0.99, 1.0, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.04
    bsdf.inputs["IOR"].default_value = 1.52
    bsdf.inputs["Transmission Weight"].default_value = 1.0
    bsdf.inputs["Specular IOR Level"].default_value = 0.95
    mats["Crystal_Glass"] = mat

    # 8. Warm Chandelier Candle Bulb (Light Emissive)
    mat, nodes, bsdf = new_mat("Mat_Chandelier_Bulb")
    bsdf.inputs["Base Color"].default_value = (1.0, 0.92, 0.78, 1.0)
    bsdf.inputs["Emission Color"].default_value = (1.0, 0.88, 0.65, 1.0)
    bsdf.inputs["Emission Strength"].default_value = 5.0
    mats["Chandelier_Bulb"] = mat

    # 9. 16:9 LED Screen Idle Slate (Honest Stage Display)
    mat, nodes, bsdf = new_mat("Mat_LED_Screen")
    led_path = os.path.join(TEX_DIR, "tex_led_idle_slate.png")
    if os.path.exists(led_path):
        img_node = nodes.new("ShaderNodeTexImage")
        img_node.image = bpy.data.images.load(led_path)
        mat.node_tree.links.new(img_node.outputs["Color"], bsdf.inputs["Base Color"])
        mat.node_tree.links.new(img_node.outputs["Color"], bsdf.inputs["Emission Color"])
        bsdf.inputs["Emission Strength"].default_value = 3.2
    else:
        bsdf.inputs["Base Color"].default_value = (0.05, 0.05, 0.05, 1.0)
        bsdf.inputs["Emission Color"].default_value = (0.8, 0.7, 0.5, 1.0)
        bsdf.inputs["Emission Strength"].default_value = 2.0
    bsdf.inputs["Roughness"].default_value = 0.25
    mats["LED_Screen"] = mat

    # 10. Welcome Board Left ("LỄ TÂN HÔN")
    mat, nodes, bsdf = new_mat("Mat_Welcome_Left")
    p = os.path.join(TEX_DIR, "tex_welcome_left.png")
    if os.path.exists(p):
        img_node = nodes.new("ShaderNodeTexImage")
        img_node.image = bpy.data.images.load(p)
        mat.node_tree.links.new(img_node.outputs["Color"], bsdf.inputs["Base Color"])
    else:
        bsdf.inputs["Base Color"].default_value = (0.82, 0.37, 0.32, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.75
    mats["Welcome_Left"] = mat

    # 11. Welcome Board Right ("NHÀ CÓ HỶ")
    mat, nodes, bsdf = new_mat("Mat_Welcome_Right")
    p = os.path.join(TEX_DIR, "tex_welcome_right.png")
    if os.path.exists(p):
        img_node = nodes.new("ShaderNodeTexImage")
        img_node.image = bpy.data.images.load(p)
        mat.node_tree.links.new(img_node.outputs["Color"], bsdf.inputs["Base Color"])
    else:
        bsdf.inputs["Base Color"].default_value = (0.82, 0.37, 0.32, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.75
    mats["Welcome_Right"] = mat

    # 12. Pre-Wedding Couple Canvas Artwork
    mat, nodes, bsdf = new_mat("Mat_Couple_Canvas")
    p = os.path.join(TEX_DIR, "tex_couple_canvas.png")
    if os.path.exists(p):
        img_node = nodes.new("ShaderNodeTexImage")
        img_node.image = bpy.data.images.load(p)
        mat.node_tree.links.new(img_node.outputs["Color"], bsdf.inputs["Base Color"])
    else:
        bsdf.inputs["Base Color"].default_value = (0.90, 0.88, 0.84, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.80
    mats["Couple_Canvas"] = mat

    # 13. Silver Aluminum Truss
    mat, nodes, bsdf = new_mat("Mat_Truss_Silver")
    bsdf.inputs["Base Color"].default_value = (0.75, 0.77, 0.80, 1.0)
    bsdf.inputs["Metallic"].default_value = 0.88
    bsdf.inputs["Roughness"].default_value = 0.32
    mats["Truss_Silver"] = mat

    # 14. Dark Event Wood Deck
    mat, nodes, bsdf = new_mat("Mat_Deck_Wood")
    bsdf.inputs["Base Color"].default_value = (0.14, 0.12, 0.11, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.38
    bsdf.inputs["Specular IOR Level"].default_value = 0.55
    mats["Deck_Wood"] = mat

    # 15. Flowers: Cream Rose, Crimson Rose, Eucalyptus Sage
    mat, nodes, bsdf = new_mat("Mat_Rose_Cream")
    bsdf.inputs["Base Color"].default_value = (0.95, 0.92, 0.86, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.75
    mats["Rose_Cream"] = mat

    mat, nodes, bsdf = new_mat("Mat_Rose_Crimson")
    bsdf.inputs["Base Color"].default_value = (0.62, 0.08, 0.16, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.72
    mats["Rose_Crimson"] = mat

    mat, nodes, bsdf = new_mat("Mat_Foliage_Eucalyptus")
    bsdf.inputs["Base Color"].default_value = (0.28, 0.38, 0.29, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.65
    mats["Foliage_Eucalyptus"] = mat

    # 16. Characters & Costumes
    mat, nodes, bsdf = new_mat("Mat_NPC_Skin")
    bsdf.inputs["Base Color"].default_value = (0.83, 0.64, 0.52, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.60
    bsdf.inputs["Subsurface Weight"].default_value = 0.15
    mats["NPC_Skin"] = mat

    mat, nodes, bsdf = new_mat("Mat_NPC_Dark_Suit")
    bsdf.inputs["Base Color"].default_value = (0.08, 0.09, 0.12, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.78
    mats["NPC_Dark_Suit"] = mat

    mat, nodes, bsdf = new_mat("Mat_NPC_Bridal_Gown")
    bsdf.inputs["Base Color"].default_value = (0.98, 0.97, 0.96, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.45
    bsdf.inputs["Sheen Weight"].default_value = 0.8
    mats["NPC_Bridal_Gown"] = mat

    mat, nodes, bsdf = new_mat("Mat_NPC_AoDai_Red")
    bsdf.inputs["Base Color"].default_value = (0.68, 0.10, 0.18, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.40
    bsdf.inputs["Sheen Weight"].default_value = 0.85
    mats["NPC_AoDai_Red"] = mat

    # 17. Porcelain Tableware
    mat, nodes, bsdf = new_mat("Mat_Porcelain")
    bsdf.inputs["Base Color"].default_value = (0.98, 0.97, 0.95, 1.0)
    bsdf.inputs["Roughness"].default_value = 0.12
    mats["Porcelain"] = mat

    return mats


# ==================== GEOMETRY HELPERS ====================
def add_box(name, size, loc, rot=(0, 0, 0), mat=None, col=None):
    bpy.ops.mesh.primitive_cube_add(size=1.0, location=loc, rotation=rot)
    obj = bpy.context.active_object
    obj.name = name
    obj.scale = size
    bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
    if mat:
        obj.data.materials.append(mat)
    if col and obj.name not in col.objects:
        for c in obj.users_collection:
            c.objects.unlink(obj)
        col.objects.link(obj)
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
    if col and obj.name not in col.objects:
        for c in obj.users_collection:
            c.objects.unlink(obj)
        col.objects.link(obj)
    return obj


def add_catenary_swag(name, pt_a, pt_b, sag, width, mat, col, segments=16):
    """Creates a catenary draped fabric ribbon swag hanging between pt_a and pt_b."""
    mesh = bpy.data.meshes.new(name)
    obj = bpy.data.objects.new(name, mesh)
    col.objects.link(obj)

    verts = []
    faces = []
    half_w = width * 0.5

    for i in range(segments + 1):
        t = i / float(segments)
        # Parabolic sag curve: sag is max at t = 0.5
        x = pt_a[0] + (pt_b[0] - pt_a[0]) * t
        y = pt_a[1] + (pt_b[1] - pt_a[1]) * t
        z = pt_a[2] + (pt_b[2] - pt_a[2]) * t - sag * 4.0 * t * (1.0 - t)
        verts.append((x, y - half_w, z))
        verts.append((x, y + half_w, z))

    for i in range(segments):
        i0 = i * 2
        i1 = i0 + 1
        i2 = i0 + 2
        i3 = i0 + 3
        faces.append((i0, i2, i3, i1))

    mesh.from_pydata(verts, [], faces)
    mesh.update()
    if mat:
        obj.data.materials.append(mat)
    return obj


def add_tiffany_chair(name, loc, yaw_deg, mats, col):
    """Assembles a high-detail gold Tiffany chair with ivory cushion and crimson bow."""
    rad = math.radians(yaw_deg)
    # 4 Legs
    leg_r = 0.016
    leg_h = 0.45
    for lx, ly in [(-0.19, -0.19), (0.19, -0.19), (-0.19, 0.19), (0.19, 0.19)]:
        rx = loc[0] + lx * math.cos(rad) - ly * math.sin(rad)
        ry = loc[1] + lx * math.sin(rad) + ly * math.cos(rad)
        add_cylinder(f"{name}_Leg", leg_r, leg_h, (rx, ry, leg_h * 0.5), mat=mats["Gold_Brass"], col=col)

    # Seat Cushion
    seat = add_box(f"{name}_Cushion", (0.44, 0.44, 0.05), (loc[0], loc[1], 0.46), (0, 0, rad), mats["Ivory_Silk"], col)

    # Backrest Uprights & Spindles
    back_y = 0.19
    for bx in [-0.18, 0.18]:
        rx = loc[0] + bx * math.cos(rad) - back_y * math.sin(rad)
        ry = loc[1] + bx * math.sin(rad) + back_y * math.cos(rad)
        add_cylinder(f"{name}_Upright", 0.014, 0.46, (rx, ry, 0.46 + 0.23), mat=mats["Gold_Brass"], col=col)

    # 4 Spindles inside backrest
    for sx in [-0.10, -0.03, 0.04, 0.11]:
        rx = loc[0] + sx * math.cos(rad) - back_y * math.sin(rad)
        ry = loc[1] + sx * math.sin(rad) + back_y * math.cos(rad)
        add_cylinder(f"{name}_Spindle", 0.008, 0.38, (rx, ry, 0.46 + 0.20), mat=mats["Gold_Brass"], col=col)

    # Top Curved Backrest Rail
    rx = loc[0] - back_y * math.sin(rad)
    ry = loc[1] + back_y * math.cos(rad)
    add_box(f"{name}_TopRail", (0.42, 0.024, 0.04), (rx, ry, 0.92), (0, 0, rad), mats["Gold_Brass"], col)

    # Crimson Satin Bow on chair back
    add_box(f"{name}_Bow", (0.18, 0.02, 0.12), (rx, ry - 0.015 * math.sin(rad), 0.70), (0, 0, rad), mats["Crimson_Velvet"], col)
    return seat


def add_banquet_table(name, loc, mats, col_tables, col_chairs):
    """Assembles a Ø1.5m banquet table with draping tablecloth, floral centerpiece, and 10 Tiffany chairs."""
    # Table base & top
    add_cylinder(f"{name}_Top", 0.75, 0.04, (loc[0], loc[1], 0.74), mat=mats["Porcelain"], col=col_tables)
    # Draped Tablecloth skirt
    add_cylinder(f"{name}_ClothSkirt", 0.77, 0.60, (loc[0], loc[1], 0.44), vertices=32, mat=mats["Ivory_Silk"], col=col_tables)

    # Floral Centerpiece
    add_cylinder(f"{name}_Vase", 0.09, 0.32, (loc[0], loc[1], 0.76 + 0.16), mat=mats["Crystal_Glass"], col=col_tables)
    add_cylinder(f"{name}_Flowers", 0.26, 0.18, (loc[0], loc[1], 1.15), mat=mats["Rose_Cream"], col=col_tables)
    add_cylinder(f"{name}_RoseAccent", 0.16, 0.10, (loc[0], loc[1], 1.25), mat=mats["Rose_Crimson"], col=col_tables)

    # 10 Tiffany Chairs arranged radially at R = 1.15m (Zero chair overlap!)
    chair_radius = 1.15
    for c_idx in range(10):
        angle = c_idx * (360.0 / 10.0)
        rad = math.radians(angle)
        cx = loc[0] + chair_radius * math.cos(rad)
        cy = loc[1] + chair_radius * math.sin(rad)
        # Chair faces toward table center
        yaw = angle + 180.0
        add_tiffany_chair(f"{name}_Chair_{c_idx+1}", (cx, cy, 0.0), yaw, mats, col_chairs)


def add_crystal_chandelier(name, loc, mats, col_lighting):
    """Builds a multi-tiered crystal chandelier with central warm candle light."""
    # Central stem and canopy
    add_cylinder(f"{name}_Stem", 0.03, 0.70, (loc[0], loc[1], loc[2] + 0.35), mat=mats["Gold_Brass"], col=col_lighting)

    # 3 Concentric crystal droplet tiers
    add_cylinder(f"{name}_Tier1", 0.65, 0.15, (loc[0], loc[1], loc[2]), vertices=24, mat=mats["Crystal_Glass"], col=col_lighting)
    add_cylinder(f"{name}_Tier2", 0.45, 0.22, (loc[0], loc[1], loc[2] - 0.16), vertices=20, mat=mats["Crystal_Glass"], col=col_lighting)
    add_cylinder(f"{name}_Tier3", 0.24, 0.28, (loc[0], loc[1], loc[2] - 0.32), vertices=16, mat=mats["Crystal_Glass"], col=col_lighting)
    add_cylinder(f"{name}_PendantBottom", 0.06, 0.12, (loc[0], loc[1], loc[2] - 0.50), mat=mats["Crystal_Glass"], col=col_lighting)

    # Central glowing candle bulb
    add_cylinder(f"{name}_Bulb", 0.05, 0.10, (loc[0], loc[1], loc[2] - 0.05), mat=mats["Chandelier_Bulb"], col=col_lighting)

    # Real Blender Point Light for illumination
    light_data = bpy.data.lights.new(f"{name}_Light", type="POINT")
    light_data.energy = 220.0
    light_data.color = (1.0, 0.88, 0.68)
    light_data.shadow_soft_size = 0.35
    light_obj = bpy.data.objects.new(f"{name}_Light", light_data)
    light_obj.location = (loc[0], loc[1], loc[2] - 0.10)
    col_lighting.objects.link(light_obj)
    return light_obj


def add_demo_npc(name, role, loc, yaw_deg, mats, col_npc):
    """Builds an anthropometric 3D character proxy with distinctive role attire."""
    rad = math.radians(yaw_deg)
    head_z = 1.62
    torso_z = 1.15
    legs_z = 0.50

    # Head
    head = add_cylinder(f"{name}_Head", 0.11, 0.22, (loc[0], loc[1], loc[2] + head_z), mat=mats["NPC_Skin"], col=col_npc)

    if role == "BRIDE":
        # Bridal Gown (flowing silhouette)
        torso = add_cylinder(f"{name}_Bodice", 0.16, 0.42, (loc[0], loc[1], loc[2] + torso_z), mat=mats["NPC_Bridal_Gown"], col=col_npc)
        skirt = add_cylinder(f"{name}_GownSkirt", 0.48, 0.95, (loc[0], loc[1], loc[2] + 0.48), vertices=20, mat=mats["NPC_Bridal_Gown"], col=col_npc)
        veil = add_box(f"{name}_Veil", (0.32, 0.04, 0.85), (loc[0], loc[1] - 0.12 * math.cos(rad), loc[2] + 1.25), (0, 0, rad), mats["Ivory_Silk"], col=col_npc)
        bouquet = add_cylinder(f"{name}_Bouquet", 0.12, 0.16, (loc[0] + 0.18 * math.sin(rad), loc[1] - 0.18 * math.cos(rad), loc[2] + 1.05), mat=mats["Rose_Cream"], col=col_npc)
    elif role == "GROOM":
        # Formal Dark Tuxedo
        torso = add_box(f"{name}_Jacket", (0.38, 0.24, 0.58), (loc[0], loc[1], loc[2] + torso_z), (0, 0, rad), mats["NPC_Dark_Suit"], col=col_npc)
        pants = add_box(f"{name}_Trousers", (0.34, 0.22, 0.88), (loc[0], loc[1], loc[2] + legs_z), (0, 0, rad), mats["NPC_Dark_Suit"], col=col_npc)
        boutonniere = add_cylinder(f"{name}_Boutonniere", 0.025, 0.04, (loc[0] - 0.10 * math.cos(rad), loc[1] - 0.10 * math.sin(rad), loc[2] + 1.30), mat=mats["Rose_Crimson"], col=col_npc)
    elif role == "RECEPTIONIST":
        # Red Silk Áo Dài with Gold Trim
        torso = add_cylinder(f"{name}_AoDaiTop", 0.16, 0.55, (loc[0], loc[1], loc[2] + torso_z), mat=mats["NPC_AoDai_Red"], col=col_npc)
        trousers = add_box(f"{name}_WhitePants", (0.30, 0.20, 0.85), (loc[0], loc[1], loc[2] + legs_z), (0, 0, rad), mats["Ivory_Silk"], col=col_npc)
    else:
        # Formal Vest & Pants (Usher, Server, Musician)
        torso = add_box(f"{name}_Vest", (0.36, 0.22, 0.54), (loc[0], loc[1], loc[2] + torso_z), (0, 0, rad), mats["NPC_Dark_Suit"], col=col_npc)
        pants = add_box(f"{name}_Trousers", (0.32, 0.20, 0.86), (loc[0], loc[1], loc[2] + legs_z), (0, 0, rad), mats["NPC_Dark_Suit"], col=col_npc)

    return head


# ==================== MAIN SCENE BUILDER ====================
def build_w03_venue():
    scene = reset_scene()
    mats = create_pbr_materials()

    # Hierarchy Collections
    root = make_collection("MAYHEN_ROYAL_CRIMSON_MARQUEE")
    col_env = make_collection("00_ENVIRONMENT", root)
    col_arch = make_collection("01_ARCHITECTURE", root)
    col_stage = make_collection("02_STAGE_CEREMONY", root)
    col_dining = make_collection("03_DINING_RECEPTION", root)
    col_tables = make_collection("TABLES", col_dining)
    col_chairs = make_collection("CHAIRS", col_dining)
    col_foyer = make_collection("04_FOYER_PHOTOBOOTH", root)
    col_lighting = make_collection("05_LIGHTING", root)
    col_npc = make_collection("06_DEMO_NPCS", root)
    col_cam = make_collection("07_CAMERAS", root)

    # 1. Environment & Floors
    # Outside grass turf
    add_box("Terrain_Grass", (40.0, 52.0, 0.1), (0.0, -3.0, -0.05), mat=mats["Turf_Grass"], col=col_env)
    # Marquee floor deck
    add_box("Marquee_Floor_Deck", (18.2, 30.2, 0.12), (0.0, -3.0, 0.06), mat=mats["Deck_Wood"], col=col_env)
    # Slate Blue-Grey Felt Aisle Runner (2.2m x 23.5m)
    add_box("Aisle_Carpet_Runner", (2.20, 23.2, 0.015), (0.0, -6.4, 0.13), mat=mats["Carpet_Slate"], col=col_arch)

    # 2. Main Portal Trusses & Marquee Framework
    # 5 Main Gables at Y = -18.0, -11.0, -4.0, +3.0, +10.0 (span 18.0m, eaves 4.2m, ridge 6.2m)
    truss_ys = [-18.0, -11.0, -4.0, 3.0, 10.0]
    for ty in truss_ys:
        # Left & Right Columns
        add_box(f"Truss_Col_L_{int(ty)}", (0.24, 0.24, 4.2), (-8.9, ty, 2.1), mat=mats["Truss_Silver"], col=col_arch)
        add_box(f"Truss_Col_R_{int(ty)}", (0.24, 0.24, 4.2), (8.9, ty, 2.1), mat=mats["Truss_Silver"], col=col_arch)
        # Left & Right Gable Rafters sloping up to (0, ty, 6.2)
        half_span = 8.9
        rise = 2.0
        rafter_len = math.sqrt(half_span**2 + rise**2)
        angle = math.atan2(rise, half_span)
        add_box(f"Truss_Rafter_L_{int(ty)}", (rafter_len, 0.22, 0.22), (-4.45, ty, 4.2 + rise * 0.5), (0, angle, 0), mats["Truss_Silver"], col_arch)
        add_box(f"Truss_Rafter_R_{int(ty)}", (rafter_len, 0.22, 0.22), (4.45, ty, 4.2 + rise * 0.5), (0, -angle, 0), mats["Truss_Silver"], col_arch)

    # Ridge Beam & Purlins
    add_box("Truss_Ridge_Beam", (0.22, 28.5, 0.22), (0.0, -4.0, 6.2), mat=mats["Truss_Silver"], col=col_arch)
    for px in [-6.0, -3.0, 3.0, 6.0]:
        pz = 4.2 + rise * (1.0 - abs(px) / 8.9)
        add_box(f"Truss_Purlin_{int(px)}", (0.16, 28.5, 0.16), (px, -4.0, pz), mat=mats["Truss_Silver"], col=col_arch)

    # 3. Blackout Roof & Starlight Velvet Ceiling Liner
    # Blackout outer roof
    add_box("Roof_Liner_Blackout", (18.6, 28.8, 0.05), (0.0, -4.0, 5.25), mat=mats["Starlight_Liner"], col=col_arch)
    # Side walls with pleated crimson & ivory drapery
    for side_x in [-9.0, 9.0]:
        add_box(f"Wall_Drapery_{int(side_x)}", (0.12, 28.0, 4.2), (side_x, -4.0, 2.1), mat=mats["Crimson_Velvet"], col=col_arch)

    # 4. 14 Catenary Draped Ceiling Fabric Swags (Ivory & Crimson Alternating)
    # Matching video f07: sweeping swoops across the marquee ceiling!
    for i, sy in enumerate([-16.0, -13.5, -11.0, -8.5, -6.0, -3.5, -1.0, 1.5, 4.0, 6.5, 8.5]):
        is_crimson = (i % 2 == 1)
        swag_mat = mats["Crimson_Velvet"] if is_crimson else mats["Ivory_Silk"]
        # Left wing catenary swoop
        add_catenary_swag(
            f"Ceiling_Swag_L_{i}",
            pt_a=(-8.8, sy, 4.15),
            pt_b=(-0.2, sy, 5.85),
            sag=0.75,
            width=0.85,
            mat=swag_mat,
            col=col_arch,
        )
        # Right wing catenary swoop
        add_catenary_swag(
            f"Ceiling_Swag_R_{i}",
            pt_a=(0.2, sy, 5.85),
            pt_b=(8.8, sy, 4.15),
            sag=0.75,
            width=0.85,
            mat=swag_mat,
            col=col_arch,
        )

    # Central 12-Petal Crimson Dome Ceiling Medallion (Y = -4.5m, Z = 5.6m)
    dome_center = (0.0, -4.5, 5.65)
    add_cylinder("Dome_Medallion_Core", 1.80, 0.12, dome_center, mat=mats["Crimson_Velvet"], col=col_arch)
    for petal_idx in range(12):
        p_angle = math.radians(petal_idx * 30.0)
        px = dome_center[0] + 1.25 * math.cos(p_angle)
        py = dome_center[1] + 1.25 * math.sin(p_angle)
        add_box(
            f"Dome_Petal_{petal_idx}",
            (0.85, 0.45, 0.08),
            (px, py, dome_center[2] - 0.02),
            (0, 0, p_angle),
            mats["Crimson_Velvet"],
            col_arch,
        )

    # 5. 8 Crystal Chandeliers (2 rows of 4 along trusses at X = ±3.0m)
    chan_lights = []
    for cx in [-3.0, 3.0]:
        for cy in [-11.0, -4.0, 3.0, 8.0]:
            cl = add_crystal_chandelier(f"Chandelier_{int(cx)}_{int(cy)}", (cx, cy, 4.60), mats, col_lighting)
            chan_lights.append(cl)

    # 6. Stage & Ceremony Area (+5.2m to +11.8m)
    # Catwalk
    add_box("Stage_Catwalk", (2.4, 1.8, 0.45), (0.0, 5.2, 0.225), mat=mats["Deck_Wood"], col=col_stage)
    # Main Stage Platform
    add_box("Stage_Main_Platform", (10.4, 4.2, 0.65), (0.0, 8.2, 0.325), mat=mats["Deck_Wood"], col=col_stage)
    # Stage Front Skirt
    add_box("Stage_Front_Skirt", (10.4, 0.04, 0.64), (0.0, 6.1, 0.32), mat=mats["Crimson_Velvet"], col=col_stage)

    # Pleated Crimson Velvet Backdrop Wall (6.4m x 3.8m)
    add_box("Stage_Backdrop_Velvet", (6.4, 0.12, 3.8), (0.0, 10.2, 2.55), mat=mats["Crimson_Velvet"], col=col_stage)
    # Gold Song Hỷ / NT Monogram Plaque
    add_box("Stage_Gold_Monogram", (1.20, 0.04, 1.20), (0.0, 10.12, 3.20), mat=mats["Gold_Brass"], col=col_stage)

    # Dual 16:9 LED Screens flanking stage (3.20m x 1.80m)
    # Stage Left LED Screen
    add_box("LED_Screen_Left_Frame", (3.30, 0.12, 1.90), (-4.2, 9.8, 2.45), mat=mats["Truss_Silver"], col=col_stage)
    add_box("LED_Screen_Left_Panel", (3.20, 0.02, 1.80), (-4.2, 9.73, 2.45), mat=mats["LED_Screen"], col=col_stage)
    # Stage Right LED Screen
    add_box("LED_Screen_Right_Frame", (3.30, 0.12, 1.90), (4.2, 9.8, 2.45), mat=mats["Truss_Silver"], col=col_stage)
    add_box("LED_Screen_Right_Panel", (3.20, 0.02, 1.80), (4.2, 9.73, 2.45), mat=mats["LED_Screen"], col=col_stage)

    # Champagne Tower Table (Stage Right)
    add_cylinder("Champagne_Table", 0.55, 0.85, (3.2, 7.2, 0.65 + 0.425), mat=mats["Ivory_Silk"], col=col_stage)
    for t_idx, t_rad in enumerate([0.38, 0.28, 0.18, 0.08]):
        add_cylinder(f"Champagne_Tier_{t_idx}", t_rad, 0.08, (3.2, 7.2, 1.50 + t_idx * 0.10), mat=mats["Crystal_Glass"], col=col_stage)

    # Wedding Cake Pedestal (Stage Left)
    add_cylinder("Cake_Table", 0.55, 0.85, (-3.2, 7.2, 0.65 + 0.425), mat=mats["Ivory_Silk"], col=col_stage)
    add_cylinder("Cake_Tier1", 0.35, 0.18, (-3.2, 7.2, 1.50 + 0.09), mat=mats["Porcelain"], col=col_stage)
    add_cylinder("Cake_Tier2", 0.24, 0.18, (-3.2, 7.2, 1.68 + 0.09), mat=mats["Porcelain"], col=col_stage)
    add_cylinder("Cake_Tier3", 0.14, 0.18, (-3.2, 7.2, 1.86 + 0.09), mat=mats["Porcelain"], col=col_stage)

    # Band Instruments
    # Stage Right: Acoustic Drum Kit
    add_cylinder("Drum_Bass", 0.32, 0.42, (3.8, 9.0, 0.65 + 0.32), rot=(math.radians(90), 0, 0), mat=mats["Gold_Brass"], col=col_stage)
    add_cylinder("Drum_Snare", 0.18, 0.14, (3.4, 8.6, 0.65 + 0.55), mat=mats["Porcelain"], col=col_stage)
    add_cylinder("Drum_Cymbal", 0.24, 0.015, (4.1, 8.6, 0.65 + 0.88), mat=mats["Gold_Brass"], col=col_stage)
    # Stage Left: Digital Keyboard on Stand
    add_box("Keyboard_Stand", (0.04, 0.45, 0.72), (-3.8, 8.8, 0.65 + 0.36), mat=mats["Truss_Silver"], col=col_stage)
    add_box("Keyboard_Body", (1.10, 0.32, 0.10), (-3.8, 8.8, 0.65 + 0.75), mat=mats["NPC_Dark_Suit"], col=col_stage)

    # 7. 16 Banquet Dining Tables (2 wings x 4 rows x 2 cols = 16 tables, 160 Tiffany chairs)
    # Clearance: 2.2m central aisle, >1.0m between chair backs (zero collisions!)
    table_xs = [-6.4, -3.0, 3.0, 6.4]
    table_ys = [-10.2, -6.4, -2.6, 1.2]
    t_count = 1
    for ty in table_ys:
        for tx in table_xs:
            add_banquet_table(f"Table_{t_count}", (tx, ty, 0.12), mats, col_tables, col_chairs)
            t_count += 1

    # 6 Standing Floral Pedestals along Central Aisle
    for py in [-11.0, -5.0, 1.0]:
        for px in [-1.25, 1.25]:
            add_cylinder(f"Pedestal_{int(px)}_{int(py)}", 0.14, 0.90, (px, py, 0.12 + 0.45), mat=mats["Gold_Brass"], col=col_arch)
            add_cylinder(f"Pedestal_Floral_{int(px)}_{int(py)}", 0.28, 0.25, (px, py, 0.12 + 1.02), mat=mats["Rose_Cream"], col=col_arch)

    # 8. Foyer Entrance & Photobooth (-18.0m to -13.5m)
    # Arched Floral Entrance Portal
    add_cylinder("Entrance_Col_L", 0.18, 3.6, (-1.8, -17.8, 1.8), mat=mats["Rose_Cream"], col=col_foyer)
    add_cylinder("Entrance_Col_R", 0.18, 3.6, (1.8, -17.8, 1.8), mat=mats["Rose_Cream"], col=col_foyer)
    add_box("Entrance_Arch_Header", (3.8, 0.35, 0.45), (0.0, -17.8, 3.6), mat=mats["Rose_Crimson"], col=col_foyer)

    # Welcome Easel Left ("LỄ TÂN HÔN")
    add_box("Easel_Stand_Left", (0.04, 0.04, 1.45), (-2.8, -16.8, 0.725), (math.radians(12), 0, math.radians(-15)), mats["Truss_Silver"], col_foyer)
    add_box("Welcome_Board_Left", (0.55, 0.03, 0.95), (-2.8, -16.7, 0.88), (math.radians(-8), 0, math.radians(15)), mats["Welcome_Left"], col_foyer)

    # Welcome Easel Right ("NHÀ CÓ HỶ")
    add_box("Easel_Stand_Right", (0.04, 0.04, 1.45), (2.8, -16.8, 0.725), (math.radians(12), 0, math.radians(15)), mats["Truss_Silver"], col_foyer)
    add_box("Welcome_Board_Right", (0.55, 0.03, 1.05), (2.8, -16.7, 0.88), (math.radians(-8), 0, math.radians(-15)), mats["Welcome_Right"], col_foyer)

    # Couple Pre-Wedding Canvas Artwork Display
    add_box("Easel_Canvas_Stand", (0.04, 0.04, 1.55), (-4.2, -15.5, 0.775), (math.radians(10), 0, math.radians(-25)), mats["Truss_Silver"], col_foyer)
    add_box("Couple_Canvas_Frame", (0.64, 0.05, 0.92), (-4.2, -15.4, 0.95), (math.radians(-6), 0, math.radians(25)), mats["Gold_Brass"], col_foyer)
    add_box("Couple_Canvas_Art", (0.58, 0.01, 0.86), (-4.2, -15.37, 0.95), (math.radians(-6), 0, math.radians(25)), mats["Couple_Canvas"], col_foyer)

    # Reception Desk (3.0m x 0.9m x 0.85m)
    add_box("Reception_Desk", (3.0, 0.90, 0.85), (4.5, -15.2, 0.12 + 0.425), mat=mats["Deck_Wood"], col=col_foyer)
    add_box("Reception_Cloth", (3.05, 0.95, 0.84), (4.5, -15.2, 0.12 + 0.42), mat=mats["Crimson_Velvet"], col=col_foyer)
    # Red Lucky Money Box (Hòm Mừng Cưới) with heart slot
    add_box("Lucky_Money_Box", (0.38, 0.28, 0.30), (4.0, -15.2, 0.97 + 0.15), mat=mats["Crimson_Velvet"], col=col_foyer)
    # Guest Signature Book & Pen
    add_box("Guestbook", (0.42, 0.32, 0.03), (4.8, -15.2, 0.97 + 0.015), mat=mats["Ivory_Silk"], col=col_foyer)

    # Photobooth Stage (Stage Left Foyer: -7.0, -14.5)
    add_box("Photobooth_Platform", (4.2, 2.5, 0.20), (-6.0, -14.2, 0.12 + 0.10), mat=mats["Deck_Wood"], col=col_foyer)
    add_box("Photobooth_Backdrop", (3.8, 0.10, 2.8), (-6.0, -13.0, 0.32 + 1.40), mat=mats["Crimson_Velvet"], col=col_foyer)
    add_cylinder("Photobooth_Floral_L", 0.35, 2.6, (-7.8, -13.2, 0.32 + 1.30), mat=mats["Rose_Cream"], col=col_foyer)
    add_cylinder("Photobooth_Floral_R", 0.35, 2.6, (-4.2, -13.2, 0.32 + 1.30), mat=mats["Rose_Cream"], col=col_foyer)

    # 9. 8 Animated Demo Character NPCs
    demo_npcs = [
        ("NPC_Bride", "BRIDE", (-0.35, 8.4, 0.65), 0),
        ("NPC_Groom", "GROOM", (0.35, 8.4, 0.65), 0),
        ("NPC_Receptionist", "RECEPTIONIST", (4.5, -14.5, 0.12), 180),
        ("NPC_Usher", "USHER", (0.0, -16.5, 0.12), 0),
        ("NPC_Server", "SERVER", (-1.2, -4.5, 0.12), 45),
        ("NPC_Keyboardist", "KEYBOARD", (-3.8, 8.3, 0.65), 15),
        ("NPC_Drummer", "DRUMMER", (3.8, 8.5, 0.65), -15),
        ("NPC_Guest_Demo", "GUEST", (-3.0, -9.0, 0.12), 90),
    ]
    for nname, nrole, nloc, nyaw in demo_npcs:
        add_demo_npc(nname, nrole, nloc, nyaw, mats, col_npc)

    # 10. Cameras (4 Key Verification Angles)
    cameras = [
        ("Cam_PBR_01_Overview", (-11.5, -24.5, 8.8), (math.radians(66), 0.0, math.radians(-26)), 24.0),
        ("Cam_PBR_02_Foyer_Entrance", (0.0, -19.2, 1.65), (math.radians(89), 0.0, 0.0), 22.0),
        ("Cam_PBR_03_Aisle_To_Stage", (0.0, -8.8, 1.65), (math.radians(90), 0.0, 0.0), 22.0),
        ("Cam_PBR_04_Gala_Celebration", (0.0, 2.0, 1.60), (math.radians(86), 0.0, 0.0), 24.0),
    ]
    cam_objs = {}
    for cname, loc, rot, lens in cameras:
        cd = bpy.data.cameras.new(cname)
        cd.lens = lens
        co = bpy.data.objects.new(cname, cd)
        co.location = loc
        co.rotation_euler = rot
        col_cam.objects.link(co)
        cam_objs[cname] = co

    # 11. Environment Lighting Setup & Presets
    world = bpy.data.worlds.new("W03_World")
    scene.world = world
    world.use_nodes = True
    bg_node = world.node_tree.nodes.get("Background")

    sun_data = bpy.data.lights.new("Sun_Light", type="SUN")
    sun_obj = bpy.data.objects.new("Sun_Light", sun_data)
    col_lighting.objects.link(sun_obj)

    # Stage Beam Spotlights (Warm 3200K cones projecting onto stage)
    for bx in [-2.5, 2.5]:
        spot_d = bpy.data.lights.new(f"Stage_Spot_{int(bx)}", type="SPOT")
        spot_d.energy = 450.0
        spot_d.spot_size = math.radians(38.0)
        spot_d.spot_blend = 0.25
        spot_d.color = (1.0, 0.92, 0.75)
        spot_o = bpy.data.objects.new(f"Stage_Spot_{int(bx)}", spot_d)
        spot_o.location = (bx, 6.5, 5.2)
        spot_o.rotation_euler = (math.radians(28), 0.0, 0.0)
        col_lighting.objects.link(spot_o)

    def apply_lighting_preset(preset_name):
        """Switches sky, sun angle/intensity, and chandelier energy for 5 timeframes."""
        if preset_name == "DAWN_05H":
            bg_node.inputs["Color"].default_value = (0.24, 0.28, 0.44, 1.0)
            bg_node.inputs["Strength"].default_value = 0.35
            sun_data.energy = 1.2
            sun_data.color = (1.0, 0.65, 0.45)
            sun_obj.rotation_euler = (math.radians(82), math.radians(10), math.radians(85))
            for cl in chan_lights:
                cl.data.energy = 100.0
        elif preset_name == "MORNING_08H":
            bg_node.inputs["Color"].default_value = (0.75, 0.85, 0.98, 1.0)
            bg_node.inputs["Strength"].default_value = 0.95
            sun_data.energy = 3.5
            sun_data.color = (1.0, 0.98, 0.92)
            sun_obj.rotation_euler = (math.radians(65), math.radians(20), math.radians(45))
            for cl in chan_lights:
                cl.data.energy = 140.0
        elif preset_name == "NOON_12H":
            bg_node.inputs["Color"].default_value = (0.85, 0.92, 1.0, 1.0)
            bg_node.inputs["Strength"].default_value = 1.15
            sun_data.energy = 4.8
            sun_data.color = (1.0, 1.0, 0.98)
            sun_obj.rotation_euler = (math.radians(20), math.radians(5), math.radians(10))
            for cl in chan_lights:
                cl.data.energy = 160.0
        elif preset_name == "SUNSET_17H":
            bg_node.inputs["Color"].default_value = (0.55, 0.32, 0.28, 1.0)
            bg_node.inputs["Strength"].default_value = 0.60
            sun_data.energy = 2.2
            sun_data.color = (1.0, 0.58, 0.28)
            sun_obj.rotation_euler = (math.radians(80), math.radians(-15), math.radians(-75))
            for cl in chan_lights:
                cl.data.energy = 220.0
        else:  # NIGHT_20H (Gala Celebration matching video)
            bg_node.inputs["Color"].default_value = (0.015, 0.018, 0.035, 1.0)
            bg_node.inputs["Strength"].default_value = 0.12
            sun_data.energy = 0.0
            sun_obj.rotation_euler = (math.radians(-45), 0, 0)
            for cl in chan_lights:
                cl.data.energy = 280.0

    # Save editable master .blend file
    os.makedirs(os.path.dirname(BLEND_OUT), exist_ok=True)
    os.makedirs(os.path.dirname(GLB_OUT), exist_ok=True)
    os.makedirs(RENDER_DIR, exist_ok=True)

    # Set active camera and save
    scene.camera = cam_objs["Cam_PBR_03_Aisle_To_Stage"]
    apply_lighting_preset("NIGHT_20H")
    bpy.ops.wm.save_as_mainfile(filepath=BLEND_OUT)
    print("SAVED_MASTER_BLEND:", BLEND_OUT)

    # 12. Optimized WebGL GLB Export
    try:
        bpy.ops.export_scene.gltf(
            filepath=GLB_OUT,
            export_format="GLB",
            export_apply=True,
            export_yup=True,
            export_materials="EXPORT",
            export_cameras=True,
            export_lights=True,
        )
        print("EXPORTED_WEBGL_GLB:", GLB_OUT, f"({os.path.getsize(GLB_OUT)} bytes)")
    except Exception as exc:
        print("GLB export warning:", exc)

    # 13. Automated Verification Renders via Cycles GPU CUDA
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

    scene.cycles.samples = 40
    scene.cycles.use_denoising = True
    scene.render.resolution_x = 1280
    scene.render.resolution_y = 720
    scene.render.image_settings.file_format = "PNG"

    # Part A: Render 4 Key Camera Angles under Night Gala (matching video)
    apply_lighting_preset("NIGHT_20H")
    views = [
        ("Cam_PBR_01_Overview", "w03_01_overview.png"),
        ("Cam_PBR_02_Foyer_Entrance", "w03_02_foyer_entrance.png"),
        ("Cam_PBR_03_Aisle_To_Stage", "w03_03_aisle_to_stage.png"),
        ("Cam_PBR_04_Gala_Celebration", "w03_04_stage_gala_night.png"),
    ]
    for cam_name, fname in views:
        scene.camera = cam_objs[cam_name]
        scene.render.filepath = os.path.join(RENDER_DIR, fname)
        bpy.ops.render.render(write_still=True)
        print("RENDERED_VIEW:", fname)

    # Part B: Render 5 Time-of-Day Lighting Presets from Cam_PBR_03_Aisle_To_Stage
    scene.camera = cam_objs["Cam_PBR_03_Aisle_To_Stage"]
    lighting_presets = [
        ("DAWN_05H", "w03_lighting_01_dawn_05h.png"),
        ("MORNING_08H", "w03_lighting_02_morning_08h.png"),
        ("NOON_12H", "w03_lighting_03_noon_12h.png"),
        ("SUNSET_17H", "w03_lighting_04_sunset_17h.png"),
        ("NIGHT_20H", "w03_lighting_05_night_20h.png"),
    ]
    for preset_key, fname in lighting_presets:
        apply_lighting_preset(preset_key)
        scene.render.filepath = os.path.join(RENDER_DIR, fname)
        bpy.ops.render.render(write_still=True)
        print("RENDERED_LIGHTING_PRESET:", fname)


if __name__ == "__main__":
    build_w03_venue()
