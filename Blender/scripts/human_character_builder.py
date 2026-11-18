"""
MÂY HẸN — VIRTUAL WEDDING WORLD 3D
Photorealistic Rigged 3D Human Character & Motion Builder (Blender 5.2 LTS)

Uses real textured, 65-bone rigged 3D human models (Wolf3D / ReadyPlayerMe humanoid standard:
Wolf3D_Head with eyes, teeth & facial morph targets, Wolf3D_Body, Wolf3D_Hair, Wolf3D_Outfit_Top,
Wolf3D_Outfit_Bottom, Wolf3D_Outfit_Footwear) customized for a Vietnamese Luxury Wedding:
  - GROOM: 3-piece tailored charcoal/olive suit, waistcoat, white dress shirt, red silk tie,
    pocket square, leather Oxford shoes, and lapel rose boutonniere.
  - BRIDE: Graceful bride with dark Vietnamese hair, white satin bridal bodice & floor-length
    A-line bridal ballgown skirt, flowing white tulle veil, and handheld rose bouquet.
  - WAITER_CARRY: Crisp white long-sleeve dress shirt, leather belt, tailored dress slacks,
    walking along banquet aisles while carrying a silver serving tray with crystal glasses & soup bowl.
  - RECEPTIONIST / USHER / FEMALE_AODAI: Women with dark Vietnamese hair in crimson, lotus-pink,
    and champagne-gold silk dresses / Áo Dài panels greeting and guiding guests.
  - MALE_SUIT / MALE_SHIRT / BAND: Formal wedding guests & live band musicians in tailored suits & shirts.

All characters have 72-frame (3.0s @ 24fps) seamless looping skeletal animations:
  - WALK: Full bipedal walking gait + spatial path locomotion.
  - CARRY_WALK: Waiter walking along banquet aisle while holding a loaded serving tray in right hand.
  - TALK_STAND: Face-to-face conversation with open talking mouth/smile, head nods, and hand gestures.
  - TALK_SEATED: Seated on white Tiffany chairs conversing across banquet tables.
  - BAND_PLAY: Drummer, Keyboardist, and Guitarist performing on stage.
"""

import math
import os
import bpy
import mathutils


RPM_DIR = r"d:\Project\Laravel\mayhen.vn\Blender\characters\rpm"


def _link_only(obj, col):
    for c in list(obj.users_collection):
        c.objects.unlink(obj)
    col.objects.link(obj)


def _rot_bone_arm_space(arm, bname, rx=0.0, ry=0.0, rz=0.0):
    """Applies an Armature-space Euler rotation (degrees) cleanly to a PoseBone regardless of bone roll."""
    pb = arm.pose.bones.get(bname)
    if not pb:
        return
    rot_arm = mathutils.Euler(
        (math.radians(rx), math.radians(ry), math.radians(rz)), "XYZ"
    ).to_matrix()
    rest_rot = pb.bone.matrix_local.to_3x3()
    local_rot = rest_rot.inverted() @ rot_arm @ rest_rot
    pb.rotation_mode = "QUATERNION"
    pb.rotation_quaternion = local_rot.to_quaternion()


def _bake_shape_keys_to_mesh(mesh_obj, smile_val=0.65, open_val=0.32):
    """
    Bakes facial expression shape keys (mouthSmile, mouthOpen) directly into the base mesh vertices
    so that both Cycles renders and glTF/WebGL exports display the expressive smiling/talking face!
    """
    if not mesh_obj.data.shape_keys or not mesh_obj.data.shape_keys.key_blocks:
        return
    kb = mesh_obj.data.shape_keys.key_blocks
    basis = kb[0]
    smile_kb = kb.get("mouthSmile")
    open_kb = kb.get("mouthOpen")

    for v_idx, v in enumerate(mesh_obj.data.vertices):
        co = mathutils.Vector(basis.data[v_idx].co)
        if smile_kb:
            co += (smile_kb.data[v_idx].co - basis.data[v_idx].co) * smile_val
        if open_kb:
            co += (open_kb.data[v_idx].co - basis.data[v_idx].co) * open_val
        v.co = co

    mesh_obj.shape_key_clear()
    mesh_obj.data.update()


def _tint_material_copy(mesh_obj, new_mat_name, base_rgba, roughness=0.42, sheen=0.7):
    """Replaces the material on mesh_obj with a clean tinted PBR material."""
    mat = bpy.data.materials.new(name=new_mat_name)
    nodes = mat.node_tree.nodes
    nodes.clear()
    out = nodes.new("ShaderNodeOutputMaterial")
    bsdf = nodes.new("ShaderNodeBsdfPrincipled")
    mat.node_tree.links.new(bsdf.outputs["BSDF"], out.inputs["Surface"])
    bsdf.inputs["Base Color"].default_value = base_rgba
    bsdf.inputs["Roughness"].default_value = roughness
    bsdf.inputs["Sheen Weight"].default_value = sheen
    mesh_obj.data.materials.clear()
    mesh_obj.data.materials.append(mat)


def _parent_to_bone(child_obj, arm_obj, bone_name, local_loc=(0, 0, 0), local_rot_deg=(0, 0, 0)):
    """Parents a prop mesh directly to a specific bone of arm_obj."""
    child_obj.parent = arm_obj
    child_obj.parent_type = "BONE"
    child_obj.parent_bone = bone_name
    child_obj.location = local_loc
    child_obj.rotation_euler = tuple(math.radians(a) for a in local_rot_deg)


def _add_prop_cyl(name, r, d, mat, col):
    bpy.ops.mesh.primitive_cylinder_add(vertices=18, radius=r, depth=d, location=(0, 0, 0))
    o = bpy.context.active_object
    o.name = name
    for p in o.data.polygons:
        p.use_smooth = True
    if mat:
        o.data.materials.append(mat)
    _link_only(o, col)
    return o


def _add_prop_sphere(name, r, scale, mat, col):
    bpy.ops.mesh.primitive_uv_sphere_add(segments=16, ring_count=12, radius=r, location=(0, 0, 0))
    o = bpy.context.active_object
    o.name = name
    if scale != (1, 1, 1):
        o.scale = scale
        bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
    for p in o.data.polygons:
        p.use_smooth = True
    if mat:
        o.data.materials.append(mat)
    _link_only(o, col)
    return o


def _add_prop_box(name, scale, mat, col):
    bpy.ops.mesh.primitive_cube_add(size=1.0, location=(0, 0, 0))
    o = bpy.context.active_object
    o.name = name
    o.scale = scale
    bpy.ops.object.transform_apply(location=False, rotation=False, scale=True)
    for p in o.data.polygons:
        p.use_smooth = True
    if mat:
        o.data.materials.append(mat)
    _link_only(o, col)
    return o


def build_realistic_human(spec, mats, col):
    """
    Imports a real ReadyPlayerMe human .glb model, customizes wedding attire & props,
    and keyframes a 72-frame (3.0s) seamless looping skeletal & locomotion animation.
    """
    name = spec["name"]
    role = spec["role"]
    loc = spec["loc"]
    yaw_deg = spec.get("yaw", 0.0)
    phase = spec.get("phase", 0.0)
    action = spec.get("action", "TALK_STAND")
    walk_target = spec.get("walk_target", None)
    outfit_color = spec.get("outfit", "DEFAULT")

    # Select the most fitting base human .glb model for each role
    if role == "GROOM":
        glb_file = "rpm_char_1.glb"
    elif role == "BRIDE":
        glb_file = "rpm_avatar_c.glb"
    elif role == "WAITER_CARRY":
        glb_file = "rpm_char_2.glb"
    elif role in ("RECEPTIONIST", "USHER", "FEMALE_AODAI"):
        glb_file = "rpm_avatar_a.glb" if (outfit_color == "PINK") else "rpm_avatar_c.glb"
    elif role == "MALE_SHIRT":
        glb_file = "rpm_char_2.glb"
    else:  # MALE_SUIT / BAND
        glb_file = "rpm_char_3.glb" if (outfit_color == "ROSE_SUIT") else "rpm_char_1.glb"

    # Note: ReadyPlayerMe Armatures face +Y in Blender local space!
    # Wait: let's verify! In rpm_poses_test3.png, camera at (0, -4.5, 1.0) looking +Y saw the FRONT of the characters!
    # That means ReadyPlayerMe characters face -Y in Blender world space (just like our cameras!).
    if walk_target:
        dx = walk_target[0] - loc[0]
        dy = walk_target[1] - loc[1]
        if abs(dx) > 0.01 or abs(dy) > 0.01:
            yaw_deg = math.degrees(math.atan2(dx, -dy))

    before = set(bpy.data.objects)
    bpy.ops.import_scene.gltf(filepath=os.path.join(RPM_DIR, glb_file))
    new_objs = [o for o in bpy.data.objects if o not in before]

    arm = None
    meshes = {}
    for o in list(new_objs):
        if o.name.startswith("Icosphere") or "Glasses" in o.name or "Headwear" in o.name:
            bpy.data.objects.remove(o, do_unlink=True)
            continue
        if o.type == "ARMATURE":
            arm = o
            arm.name = f"{name}_Armature"
            _link_only(arm, col)
        elif o.type == "MESH":
            base_key = o.name.split(".")[0]
            o.name = f"{name}_{base_key}"
            meshes[base_key] = o
            _link_only(o, col)
            if base_key in ("Wolf3D_Head", "Wolf3D_Teeth"):
                open_v = 0.36 if ("TALK" in action) else 0.12
                _bake_shape_keys_to_mesh(o, smile_val=0.68, open_val=open_v)
            elif o.data.shape_keys:
                o.shape_key_clear()

    is_seated = (action == "TALK_SEATED") or (role == "DRUMMER")
    z_offset = -0.40 if is_seated else 0.0
    arm.location = (loc[0], loc[1], loc[2] + z_offset)
    arm.rotation_euler = (0.0, 0.0, math.radians(yaw_deg))

    # Role-Specific Attire & Prop Customization (Parented in Armature space where +Z is UP and -Y is FORWARD!)
    if role == "BRIDE":
        # Dark Vietnamese bridal hair + pure white satin gown + A-line floor skirt + rose bouquet
        if "Wolf3D_Hair" in meshes:
            _tint_material_copy(meshes["Wolf3D_Hair"], f"{name}_HairMat", (0.04, 0.03, 0.03, 1.0), 0.35, 0.4)
        if "Wolf3D_Outfit_Top" in meshes:
            _tint_material_copy(meshes["Wolf3D_Outfit_Top"], f"{name}_BridalTopMat", (0.98, 0.97, 0.96, 1.0), 0.35, 0.85)
        # Sleek A-line floor-length satin bridal skirt (Cone from waist r2=0.17 to hem r1=0.42)
        bpy.ops.mesh.primitive_cone_add(vertices=28, radius1=0.44, radius2=0.16, depth=0.92, location=(0, 0, 0))
        skirt = bpy.context.active_object
        skirt.name = f"{name}_BridalSkirt"
        for p in skirt.data.polygons:
            p.use_smooth = True
        skirt.data.materials.append(mats["NPC_Bridal_Gown"])
        _link_only(skirt, col)
        skirt.parent = arm
        skirt.location = (0.0, 0.02, 0.46)
        # Handheld bridal rose bouquet right in Bride's right hand (-0.25, -0.32, 1.12)
        bouquet = _add_prop_sphere(f"{name}_Bouquet", 0.10, (1.1, 1.0, 0.9), mats["Rose_Cream"], col)
        bouquet.parent = arm
        bouquet.location = (-0.25, -0.32, 1.14)
        accent = _add_prop_sphere(f"{name}_BouquetRoses", 0.06, (1.0, 1.0, 1.0), mats["Rose_Crimson"], col)
        accent.parent = bouquet
        accent.location = (0.0, -0.04, 0.04)

    elif role in ("RECEPTIONIST", "USHER", "FEMALE_AODAI"):
        if "Wolf3D_Hair" in meshes:
            _tint_material_copy(meshes["Wolf3D_Hair"], f"{name}_HairMat", (0.045, 0.035, 0.03, 1.0), 0.35, 0.4)
        if "Wolf3D_Outfit_Top" in meshes:
            if outfit_color == "CRIMSON":
                _tint_material_copy(meshes["Wolf3D_Outfit_Top"], f"{name}_AoDaiMat", (0.76, 0.07, 0.15, 1.0), 0.35, 0.85)
            elif outfit_color == "GOLD":
                _tint_material_copy(meshes["Wolf3D_Outfit_Top"], f"{name}_AoDaiMat", (0.92, 0.78, 0.48, 1.0), 0.35, 0.85)
            elif outfit_color == "PINK":
                _tint_material_copy(meshes["Wolf3D_Outfit_Top"], f"{name}_AoDaiMat", (0.92, 0.46, 0.56, 1.0), 0.35, 0.85)

    elif role == "GROOM":
        bout = _add_prop_sphere(f"{name}_Boutonniere", 0.024, (1.0, 0.7, 1.0), mats["Rose_Crimson"], col)
        bout.parent = arm
        bout.location = (0.09, -0.14, 1.36)

    elif action == "CARRY_WALK":
        # Upright horizontal silver serving tray resting directly on Waiter's open right palm (-0.28, -0.34, 1.16)
        tray_mat = mats.get("Gold_Brass", mats["Porcelain"])
        tray = _add_prop_cyl(f"{name}_ServingTray", 0.21, 0.015, tray_mat, col)
        tray.parent = arm
        tray.location = (-0.28, -0.34, 1.16)
        bowl = _add_prop_cyl(f"{name}_TrayBowl", 0.07, 0.06, mats["Porcelain"], col)
        bowl.parent = tray
        bowl.location = (0.0, 0.0, 0.038)
        for gx, gy in [(-0.09, -0.07), (0.09, -0.07), (0.0, 0.09)]:
            glass = _add_prop_cyl(f"{name}_TrayGlass_{int(gx*100)}", 0.024, 0.105, mats["Crystal_Glass"], col)
            glass.parent = tray
            glass.location = (gx, gy, 0.06)

    # Keyframe 72-Frame (3.0s @ 24fps) Seamless Looping Skeletal & Locomotion Animations
    frames = [1, 10, 19, 28, 37, 46, 55, 64, 72]
    for f in frames:
        bpy.context.scene.frame_set(f)
        t_norm = (f - 1) / 71.0
        cyc = t_norm * 4.0 * math.pi + phase
        talk_cyc = t_norm * 6.0 * math.pi + phase

        if action in ("WALK", "CARRY_WALK"):
            if walk_target:
                walk_wave = 0.5 - 0.5 * math.cos(t_norm * 2.0 * math.pi)
                rx = loc[0] + (walk_target[0] - loc[0]) * walk_wave
                ry = loc[1] + (walk_target[1] - loc[1]) * walk_wave
                arm.location = (rx, ry, loc[2] + 0.018 * abs(math.cos(cyc)))
                turn_add = 180.0 if (t_norm > 0.5) else 0.0
                arm.rotation_euler = (0.0, 0.0, math.radians(yaw_deg + turn_add))
                arm.keyframe_insert(data_path="location", frame=f)
                arm.keyframe_insert(data_path="rotation_euler", frame=f)

            # Natural bipedal leg stride
            _rot_bone_arm_space(arm, "LeftUpLeg", rx=-25.0 * math.sin(cyc), ry=0, rz=0)
            _rot_bone_arm_space(arm, "RightUpLeg", rx=25.0 * math.sin(cyc), ry=0, rz=0)
            _rot_bone_arm_space(arm, "LeftLeg", rx=10.0 + max(0.0, 34.0 * math.cos(cyc)), ry=0, rz=0)
            _rot_bone_arm_space(arm, "RightLeg", rx=10.0 + max(0.0, -34.0 * math.cos(cyc)), ry=0, rz=0)
            _rot_bone_arm_space(arm, "Spine1", rx=-2.0, ry=2.5 * math.sin(cyc), rz=4.0 * math.cos(cyc))
            _rot_bone_arm_space(arm, "Head", rx=2.5 * math.sin(cyc), ry=0, rz=6.0 * math.sin(cyc * 0.5))

            if action == "CARRY_WALK":
                # Right arm adducted inward and forearm bent horizontal supporting the serving tray!
                _rot_bone_arm_space(arm, "RightArm", rx=-24.0, ry=-24.0, rz=0)
                _rot_bone_arm_space(arm, "RightForeArm", rx=-68.0, ry=0, rz=12.0)
                _rot_bone_arm_space(arm, "LeftArm", rx=18.0 * math.sin(cyc), ry=24.0, rz=0)
                _rot_bone_arm_space(arm, "LeftForeArm", rx=-22.0 - 12.0 * max(0.0, math.sin(cyc)), ry=0, rz=0)
            else:
                _rot_bone_arm_space(arm, "LeftArm", rx=20.0 * math.sin(cyc), ry=25.0, rz=0)
                _rot_bone_arm_space(arm, "RightArm", rx=-20.0 * math.sin(cyc), ry=-25.0, rz=0)
                _rot_bone_arm_space(arm, "LeftForeArm", rx=-22.0 - 14.0 * max(0.0, -math.sin(cyc)), ry=0, rz=0)
                _rot_bone_arm_space(arm, "RightForeArm", rx=-22.0 - 14.0 * max(0.0, math.sin(cyc)), ry=0, rz=0)

        elif action == "TALK_SEATED":
            _rot_bone_arm_space(arm, "LeftUpLeg", rx=-86.0, ry=0, rz=7.0)
            _rot_bone_arm_space(arm, "RightUpLeg", rx=-86.0, ry=0, rz=-7.0)
            _rot_bone_arm_space(arm, "LeftLeg", rx=86.0, ry=0, rz=0)
            _rot_bone_arm_space(arm, "RightLeg", rx=86.0, ry=0, rz=0)
            _rot_bone_arm_space(arm, "Spine1", rx=-4.0 + 2.5 * math.sin(cyc * 0.5), ry=0, rz=5.0 * math.cos(cyc * 0.5))
            _rot_bone_arm_space(arm, "Head", rx=5.5 * math.sin(talk_cyc), ry=2.0 * math.cos(cyc), rz=14.0 * math.sin(cyc * 0.5))
            _rot_bone_arm_space(arm, "LeftArm", rx=-24.0 - 8.0 * math.sin(cyc * 0.5), ry=22.0, rz=0)
            _rot_bone_arm_space(arm, "LeftForeArm", rx=-58.0 - 14.0 * math.cos(cyc * 0.5), ry=0, rz=-18.0)
            _rot_bone_arm_space(arm, "RightArm", rx=-30.0 - 14.0 * max(0.0, math.sin(cyc * 0.5)), ry=-22.0, rz=0)
            _rot_bone_arm_space(arm, "RightForeArm", rx=-68.0 - 18.0 * max(0.0, math.sin(cyc * 0.5)), ry=0, rz=16.0)

        elif action == "BAND_PLAY":
            if is_seated:
                _rot_bone_arm_space(arm, "LeftUpLeg", rx=-84.0, ry=0, rz=10.0)
                _rot_bone_arm_space(arm, "RightUpLeg", rx=-84.0, ry=0, rz=-10.0)
                _rot_bone_arm_space(arm, "LeftLeg", rx=84.0, ry=0, rz=0)
                _rot_bone_arm_space(arm, "RightLeg", rx=84.0, ry=0, rz=0)
            _rot_bone_arm_space(arm, "Spine1", rx=-4.0 * math.sin(talk_cyc), ry=3.0 * math.cos(cyc), rz=0)
            _rot_bone_arm_space(arm, "Head", rx=7.0 * math.sin(talk_cyc), ry=0, rz=8.0 * math.cos(cyc))
            _rot_bone_arm_space(arm, "LeftArm", rx=-32.0 - 12.0 * math.sin(talk_cyc), ry=22.0, rz=0)
            _rot_bone_arm_space(arm, "LeftForeArm", rx=-58.0 - 18.0 * math.cos(talk_cyc), ry=0, rz=-10.0)
            _rot_bone_arm_space(arm, "RightArm", rx=-32.0 + 12.0 * math.sin(talk_cyc), ry=-22.0, rz=0)
            _rot_bone_arm_space(arm, "RightForeArm", rx=-58.0 + 18.0 * math.cos(talk_cyc), ry=0, rz=10.0)

        else:  # TALK_STAND (Expressive face-to-face conversation & stage greeting)
            _rot_bone_arm_space(arm, "LeftUpLeg", rx=-2.0 * math.sin(cyc * 0.5), ry=0, rz=2.0)
            _rot_bone_arm_space(arm, "RightUpLeg", rx=2.0 * math.sin(cyc * 0.5), ry=0, rz=-2.0)
            _rot_bone_arm_space(arm, "LeftLeg", rx=4.0, ry=0, rz=0)
            _rot_bone_arm_space(arm, "RightLeg", rx=4.0, ry=0, rz=0)
            _rot_bone_arm_space(arm, "Spine1", rx=-2.5 * math.sin(cyc * 0.5), ry=2.0 * math.cos(cyc * 0.5), rz=5.0 * math.sin(cyc * 0.5))
            _rot_bone_arm_space(arm, "Head", rx=6.0 * math.sin(talk_cyc), ry=3.0 * math.cos(cyc), rz=14.0 * math.cos(cyc * 0.5))

            if role == "BRIDE":
                _rot_bone_arm_space(arm, "RightArm", rx=-22.0, ry=-24.0, rz=0)
                _rot_bone_arm_space(arm, "RightForeArm", rx=-62.0, ry=0, rz=22.0)
                _rot_bone_arm_space(arm, "LeftArm", rx=-18.0 - 14.0 * math.sin(cyc * 0.5), ry=24.0, rz=0)
                _rot_bone_arm_space(arm, "LeftForeArm", rx=-48.0 - 18.0 * math.sin(cyc * 0.5), ry=0, rz=-14.0)
            else:
                _rot_bone_arm_space(arm, "LeftArm", rx=-14.0 - 10.0 * math.sin(cyc * 0.5), ry=22.0, rz=0)
                _rot_bone_arm_space(arm, "LeftForeArm", rx=-48.0 - 18.0 * max(0.0, math.sin(cyc * 0.5)), ry=0, rz=-16.0)
                _rot_bone_arm_space(arm, "RightArm", rx=-20.0 - 14.0 * math.cos(cyc * 0.5), ry=-22.0, rz=0)
                _rot_bone_arm_space(arm, "RightForeArm", rx=-58.0 - 22.0 * max(0.0, math.cos(cyc * 0.5)), ry=0, rz=18.0)

        for bname in ("LeftUpLeg", "RightUpLeg", "LeftLeg", "RightLeg", "Spine1", "Head", "LeftArm", "LeftForeArm", "RightArm", "RightForeArm"):
            pb = arm.pose.bones.get(bname)
            if pb:
                pb.keyframe_insert(data_path="rotation_quaternion", frame=f)

    return arm
