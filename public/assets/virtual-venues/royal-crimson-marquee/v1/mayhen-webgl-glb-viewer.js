/**
 * MÂY HẸN — VIRTUAL WEDDING WORLD 3D
 * Pure Vanilla WebGL2 glTF 2.0 (.glb) Engine with 72-Bone GPU Skeletal Skinning
 * Zero external dependencies, zero CDN — 100% Native ES Module.
 */

const VERT_SRC = `#version 300 es
precision highp float;

layout(location = 0) in vec3 a_position;
layout(location = 1) in vec3 a_normal;
layout(location = 2) in vec2 a_uv;
layout(location = 3) in vec4 a_joints;
layout(location = 4) in vec4 a_weights;

uniform mat4 u_viewProj;
uniform mat4 u_model;
uniform mat3 u_normalMat;
uniform int u_isSkinned;
uniform mat4 u_jointMats[72];

out vec3 v_worldPos;
out vec3 v_normal;
out vec2 v_uv;

void main() {
    vec4 worldPos;
    if (u_isSkinned == 1) {
        mat4 skinMat =
            a_weights.x * u_jointMats[int(a_joints.x)] +
            a_weights.y * u_jointMats[int(a_joints.y)] +
            a_weights.z * u_jointMats[int(a_joints.z)] +
            a_weights.w * u_jointMats[int(a_joints.w)];
        worldPos = skinMat * vec4(a_position, 1.0);
        v_normal = normalize(mat3(skinMat) * a_normal);
    } else {
        worldPos = u_model * vec4(a_position, 1.0);
        v_normal = normalize(u_normalMat * a_normal);
    }
    v_worldPos = worldPos.xyz;
    v_uv = a_uv;
    gl_Position = u_viewProj * worldPos;
}
`;

const FRAG_SRC = `#version 300 es
precision highp float;

in vec3 v_worldPos;
in vec3 v_normal;
in vec2 v_uv;

uniform vec4 u_baseColorFactor;
uniform vec3 u_emissiveFactor;
uniform float u_metallic;
uniform float u_roughness;
uniform int u_hasBaseTex;
uniform int u_hasEmissiveTex;
uniform sampler2D u_baseTex;
uniform sampler2D u_emissiveTex;

uniform vec3 u_cameraPos;
uniform vec3 u_ambientColor;
uniform vec3 u_sunDir;
uniform vec3 u_sunColor;
uniform float u_chandelierBoost;

out vec4 outColor;

void main() {
    vec4 baseSample = (u_hasBaseTex == 1) ? texture(u_baseTex, v_uv) : vec4(1.0);
    vec3 albedo = baseSample.rgb * u_baseColorFactor.rgb;
    float alpha = baseSample.a * u_baseColorFactor.a;
    if (alpha < 0.35) {
        discard;
    }

    vec3 N = normalize(v_normal);
    if (!gl_FrontFacing) {
        N = -N;
    }
    vec3 V = normalize(u_cameraPos - v_worldPos);

    // Directional Sun + Hemisphere Sky Fill
    vec3 L = normalize(u_sunDir);
    float ndotl = max(dot(N, L), 0.0);
    float hemi = 0.5 + 0.5 * N.y;
    vec3 lighting = u_ambientColor * (0.58 + 0.42 * hemi) + u_sunColor * ndotl;

    // Exterior Gate, QR Reception, Interior Chandelier & Stage Warm Point Lights (glTF Y-up: X, Y=height, Z=-blender_Y)
    vec3 ptPositions[6];
    ptPositions[0] = vec3(0.0, 4.15, 8.2);
    ptPositions[1] = vec3(0.0, 4.15, -0.5);
    ptPositions[2] = vec3(0.0, 4.35, -9.2);
    ptPositions[3] = vec3(0.0, 3.95, 16.5);
    ptPositions[4] = vec3(-4.8, 3.65, 22.5);
    ptPositions[5] = vec3(4.8, 3.65, 22.5);

    vec3 pointAccum = vec3(0.0);
    for (int i = 0; i < 6; i++) {
        vec3 toLight = ptPositions[i] - v_worldPos;
        float dist = length(toLight);
        vec3 pL = toLight / max(dist, 0.001);
        float atten = 1.0 / (1.0 + 0.042 * dist + 0.0065 * dist * dist);
        float pNdotL = max(dot(N, pL), 0.0);
        vec3 H = normalize(pL + V);
        float spec = pow(max(dot(N, H), 0.0), mix(14.0, 72.0, 1.0 - u_roughness));
        pointAccum += vec3(1.0, 0.92, 0.78) * atten * (pNdotL + spec * (0.28 + 0.72 * u_metallic));
    }

    // Royal Velvet & Ivory Porcelain Rim Sheen
    float fresnel = pow(1.0 - max(dot(N, V), 0.0), 2.6);
    vec3 sheen = albedo * fresnel * 0.22;

    vec3 color = albedo * (lighting + pointAccum * u_chandelierBoost) + sheen;

    // Emissive contribution (Starlight ceiling, LED screens, NT monogram, chandelier bulbs, QR standee, photo lights)
    vec3 emSample = (u_hasEmissiveTex == 1) ? texture(u_emissiveTex, v_uv).rgb : vec3(1.0);
    color += emSample * u_emissiveFactor;

    // Subtle ACES-style tone mapping & gamma
    color = color / (color + vec3(0.82));
    color = pow(max(color, vec3(0.0)), vec3(1.0 / 2.1));

    outColor = vec4(color, 1.0);
}
`;

// ==================== MATRIX MATH HELPERS (Column-Major Float32Array) ====================
function mat4Identity() {
    const m = new Float32Array(16);
    m[0] = 1; m[5] = 1; m[10] = 1; m[15] = 1;
    return m;
}

function mat4Multiply(out, a, b) {
    const a00 = a[0], a01 = a[1], a02 = a[2], a03 = a[3];
    const a10 = a[4], a11 = a[5], a12 = a[6], a13 = a[7];
    const a20 = a[8], a21 = a[9], a22 = a[10], a23 = a[11];
    const a30 = a[12], a31 = a[13], a32 = a[14], a33 = a[15];
    for (let i = 0; i < 4; i++) {
        const b0 = b[i * 4], b1 = b[i * 4 + 1], b2 = b[i * 4 + 2], b3 = b[i * 4 + 3];
        out[i * 4]     = b0 * a00 + b1 * a10 + b2 * a20 + b3 * a30;
        out[i * 4 + 1] = b0 * a01 + b1 * a11 + b2 * a21 + b3 * a31;
        out[i * 4 + 2] = b0 * a02 + b1 * a12 + b2 * a22 + b3 * a32;
        out[i * 4 + 3] = b0 * a03 + b1 * a13 + b2 * a23 + b3 * a33;
    }
    return out;
}

function mat4FromTRS(out, t, q, s) {
    const x = q[0], y = q[1], z = q[2], w = q[3];
    const x2 = x + x, y2 = y + y, z2 = z + z;
    const xx = x * x2, xy = x * y2, xz = x * z2;
    const yy = y * y2, yz = y * z2, zz = z * z2;
    const wx = w * x2, wy = w * y2, wz = w * z2;
    const sx = s[0], sy = s[1], sz = s[2];

    out[0] = (1 - (yy + zz)) * sx;
    out[1] = (xy + wz) * sx;
    out[2] = (xz - wy) * sx;
    out[3] = 0;
    out[4] = (xy - wz) * sy;
    out[5] = (1 - (xx + zz)) * sy;
    out[6] = (yz + wx) * sy;
    out[7] = 0;
    out[8] = (xz + wy) * sz;
    out[9] = (yz - wx) * sz;
    out[10] = (1 - (xx + yy)) * sz;
    out[11] = 0;
    out[12] = t[0];
    out[13] = t[1];
    out[14] = t[2];
    out[15] = 1;
    return out;
}

function mat4Perspective(out, fovyRad, aspect, near, far) {
    const f = 1.0 / Math.tan(fovyRad * 0.5);
    out.fill(0);
    out[0] = f / aspect;
    out[5] = f;
    out[10] = (far + near) / (near - far);
    out[11] = -1;
    out[14] = (2 * far * near) / (near - far);
    return out;
}

function mat4LookAt(out, eye, center, up) {
    let zx = eye[0] - center[0], zy = eye[1] - center[1], zz = eye[2] - center[2];
    let len = Math.hypot(zx, zy, zz) || 1;
    zx /= len; zy /= len; zz /= len;

    let xx = up[1] * zz - up[2] * zy;
    let xy = up[2] * zx - up[0] * zz;
    let xz = up[0] * zy - up[1] * zx;
    len = Math.hypot(xx, xy, xz) || 1;
    xx /= len; xy /= len; xz /= len;

    const yx = zy * xz - zz * xy;
    const yy = zz * xx - zx * xz;
    const yz = zx * xy - zy * xx;

    out[0] = xx; out[1] = yx; out[2] = zx; out[3] = 0;
    out[4] = xy; out[5] = yy; out[6] = zy; out[7] = 0;
    out[8] = xz; out[9] = yz; out[10] = zz; out[11] = 0;
    out[12] = -(xx * eye[0] + xy * eye[1] + xz * eye[2]);
    out[13] = -(yx * eye[0] + yy * eye[1] + yz * eye[2]);
    out[14] = -(zx * eye[0] + zy * eye[1] + zz * eye[2]);
    out[15] = 1;
    return out;
}

function mat3FromMat4(out, m) {
    out[0] = m[0]; out[1] = m[1]; out[2] = m[2];
    out[3] = m[4]; out[4] = m[5]; out[5] = m[6];
    out[6] = m[8]; out[7] = m[9]; out[8] = m[10];
    return out;
}

// ==================== GLB PARSER & WEBGL2 ENGINE ====================
export class MayHenVirtualVenueViewer {
    constructor(canvas, options = {}) {
        this.canvas = canvas;
        this.glbUrl = options.glbUrl || './mayhen_royal_crimson_marquee_v1.glb';
        this.onStatus = options.onStatus || (() => {});
        this.onMetrics = options.onMetrics || (() => {});

        const gl = canvas.getContext('webgl2', { antialias: true, alpha: false, powerPreference: 'high-performance' });
        if (!gl) {
            throw new Error('WEBGL2_NOT_SUPPORTED');
        }
        this.gl = gl;

        this.camera = {
            pos: [0.0, 1.82, 23.0],
            yaw: 0.0,
            pitch: 0.01,
            fovDeg: 58,
        };
        this.keys = new Set();
        this.moveInput = { forward: 0, right: 0 };
        this.qualityTier = 'Balanced';
        this.dprCap = 1.25;
        this.timePreset = 'NIGHT_20H';
        this.liveStreamActive = false;

        this.nodes = [];
        this.meshes = [];
        this.skins = [];
        this.materials = [];
        this.textures = [];
        this.animations = [];
        this.ledMaterialIndices = [];

        this.metrics = {
            fps: 60,
            frameTimeMs: 16.6,
            drawCalls: 0,
            triangles: 0,
            vertices: 0,
            meshCount: 0,
            nodeCount: 0,
            glbSizeBytes: 0,
            loadTimeMs: 0,
            qualityTier: 'Balanced',
            timePreset: 'NIGHT_20H',
            liveStreamActive: false,
        };

        this._initShaders();
        this._initLiveStreamCanvas();
        this._bindInputs();
    }

    _initShaders() {
        const gl = this.gl;
        const compile = (type, src) => {
            const s = gl.createShader(type);
            gl.shaderSource(s, src);
            gl.compileShader(s);
            if (!gl.getShaderParameter(s, gl.COMPILE_STATUS)) {
                throw new Error(gl.getShaderInfoLog(s));
            }
            return s;
        };
        const vs = compile(gl.VERTEX_SHADER, VERT_SRC);
        const fs = compile(gl.FRAGMENT_SHADER, FRAG_SRC);
        const prog = gl.createProgram();
        gl.attachShader(prog, vs);
        gl.attachShader(prog, fs);
        gl.linkProgram(prog);
        if (!gl.getProgramParameter(prog, gl.LINK_STATUS)) {
            throw new Error(gl.getProgramInfoLog(prog));
        }
        this.program = prog;
        this.uniforms = {
            u_viewProj: gl.getUniformLocation(prog, 'u_viewProj'),
            u_model: gl.getUniformLocation(prog, 'u_model'),
            u_normalMat: gl.getUniformLocation(prog, 'u_normalMat'),
            u_isSkinned: gl.getUniformLocation(prog, 'u_isSkinned'),
            u_jointMats: gl.getUniformLocation(prog, 'u_jointMats[0]'),
            u_baseColorFactor: gl.getUniformLocation(prog, 'u_baseColorFactor'),
            u_emissiveFactor: gl.getUniformLocation(prog, 'u_emissiveFactor'),
            u_metallic: gl.getUniformLocation(prog, 'u_metallic'),
            u_roughness: gl.getUniformLocation(prog, 'u_roughness'),
            u_hasBaseTex: gl.getUniformLocation(prog, 'u_hasBaseTex'),
            u_hasEmissiveTex: gl.getUniformLocation(prog, 'u_hasEmissiveTex'),
            u_baseTex: gl.getUniformLocation(prog, 'u_baseTex'),
            u_emissiveTex: gl.getUniformLocation(prog, 'u_emissiveTex'),
            u_cameraPos: gl.getUniformLocation(prog, 'u_cameraPos'),
            u_ambientColor: gl.getUniformLocation(prog, 'u_ambientColor'),
            u_sunDir: gl.getUniformLocation(prog, 'u_sunDir'),
            u_sunColor: gl.getUniformLocation(prog, 'u_sunColor'),
            u_chandelierBoost: gl.getUniformLocation(prog, 'u_chandelierBoost'),
        };
    }

    _initLiveStreamCanvas() {
        const c = document.createElement('canvas');
        c.width = 640;
        c.height = 360;
        this.liveCanvas = c;
        this.liveCtx = c.getContext('2d');
        const gl = this.gl;
        this.liveTex = gl.createTexture();
        gl.bindTexture(gl.TEXTURE_2D, this.liveTex);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
        gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
    }

    _updateLiveStreamTexture(nowSec) {
        if (!this.liveStreamActive) return;
        const ctx = this.liveCtx;
        const w = 640, h = 360;
        const grad = ctx.createLinearGradient(0, 0, w, h);
        grad.addColorStop(0, '#3D0A14');
        grad.addColorStop(0.5, '#6B1324');
        grad.addColorStop(1, '#24080E');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, w, h);

        ctx.fillStyle = 'rgba(232, 198, 128, 0.35)';
        for (let i = 0; i < 16; i++) {
            const px = ((i * 97 + nowSec * 42) % w);
            const py = ((i * 53 + Math.sin(nowSec * 1.5 + i) * 40 + h) % h);
            ctx.beginPath();
            ctx.arc(px, py, 3 + (i % 4), 0, Math.PI * 2);
            ctx.fill();
        }

        ctx.strokeStyle = '#D4AF37';
        ctx.lineWidth = 3;
        ctx.strokeRect(16, 16, w - 32, h - 32);

        ctx.fillStyle = '#C91D2E';
        ctx.fillRect(32, 30, 88, 28);
        ctx.fillStyle = '#FFFFFF';
        ctx.font = 'bold 14px sans-serif';
        ctx.fillText('● TRỰC TIẾP', 40, 49);

        ctx.fillStyle = '#F7EFE6';
        ctx.font = 'bold 26px serif';
        ctx.textAlign = 'center';
        ctx.fillText('LỄ TÂN HÔN — CAO NHÂN & TRẦN THẢO', w / 2, 145);
        ctx.fillStyle = '#E8C680';
        ctx.font = '18px sans-serif';
        ctx.fillText('Đang truyền tín hiệu trực tiếp lên màn hình LED 3D', w / 2, 190);
        ctx.fillStyle = '#CFC4B4';
        ctx.font = '14px monospace';
        ctx.fillText(`Timecode: ${nowSec.toFixed(1)}s | 16:9 WebGL Video Texture`, w / 2, 245);
        ctx.textAlign = 'start';

        const gl = this.gl;
        gl.bindTexture(gl.TEXTURE_2D, this.liveTex);
        gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, this.liveCanvas);
    }

    async loadGLB() {
        const t0 = performance.now();
        this.onStatus('Đang tải mô hình rạp cưới 3D & nhân vật (.glb)...');
        const resp = await fetch(this.glbUrl);
        if (!resp.ok) {
            throw new Error(`HTTP_${resp.status}`);
        }
        const buffer = await resp.arrayBuffer();
        this.metrics.glbSizeBytes = buffer.byteLength;

        const dv = new DataView(buffer);
        const magic = dv.getUint32(0, true);
        if (magic !== 0x46546C67) {
            throw new Error('INVALID_GLB_HEADER');
        }
        const jsonChunkLen = dv.getUint32(12, true);
        const jsonBytes = new Uint8Array(buffer, 20, jsonChunkLen);
        const gltf = JSON.parse(new TextDecoder('utf-8').decode(jsonBytes));

        const binChunkOffset = 20 + jsonChunkLen + 8;
        const binChunkLen = dv.getUint32(20 + jsonChunkLen, true);
        const binBuffer = buffer.slice(binChunkOffset, binChunkOffset + binChunkLen);

        this.onStatus('Đang giải mã texture PBR, khung xương nhân vật & bộ đệm GPU...');
        await this._buildTextures(gltf, binBuffer);
        this._buildMaterials(gltf);
        this._buildMeshes(gltf, binBuffer);
        this._buildNodes(gltf);
        this._buildSkins(gltf, binBuffer);
        this._buildAnimations(gltf, binBuffer);

        this.metrics.loadTimeMs = Math.round(performance.now() - t0);
        this.onStatus('Đã sẵn sàng — Di chuyển bằng W/A/S/D hoặc chọn Góc nhìn nhanh');
        this.resize();
        this.startLoop();
    }

    async _buildTextures(gltf, binBuffer) {
        const gl = this.gl;
        const images = gltf.images || [];
        const textures = gltf.textures || [];
        const bufferViews = gltf.bufferViews || [];

        const decodedImages = await Promise.all(images.map(async (imgDef) => {
            if (imgDef.bufferView !== undefined) {
                const bv = bufferViews[imgDef.bufferView];
                const slice = new Uint8Array(binBuffer, bv.byteOffset || 0, bv.byteLength);
                const blob = new Blob([slice], { type: imgDef.mimeType || 'image/png' });
                return await createImageBitmap(blob, { imageOrientation: 'none' });
            }
            return null;
        }));

        this.textures = textures.map((texDef) => {
            const srcIdx = texDef.source ?? texDef.extensions?.EXT_texture_webp?.source;
            const bmp = srcIdx !== undefined ? decodedImages[srcIdx] : null;
            const tex = gl.createTexture();
            gl.bindTexture(gl.TEXTURE_2D, tex);
            if (bmp) {
                gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, bmp);
                gl.generateMipmap(gl.TEXTURE_2D);
                gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR_MIPMAP_LINEAR);
                gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
                gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.REPEAT);
                gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.REPEAT);
            }
            return tex;
        });
    }

    _buildMaterials(gltf) {
        this.materials = (gltf.materials || []).map((m, idx) => {
            const pbr = m.pbrMetallicRoughness || {};
            const baseColorFactor = pbr.baseColorFactor || [0.9, 0.9, 0.9, 1.0];
            const metallic = pbr.metallicFactor !== undefined ? pbr.metallicFactor : 0.0;
            const roughness = pbr.roughnessFactor !== undefined ? pbr.roughnessFactor : 0.75;
            const emissiveFactor = m.emissiveFactor || [0.0, 0.0, 0.0];
            const baseTexIdx = pbr.baseColorTexture ? pbr.baseColorTexture.index : -1;
            const emTexIdx = m.emissiveTexture ? m.emissiveTexture.index : -1;

            if (m.name && m.name.includes('Mat_LED_Screen')) {
                this.ledMaterialIndices.push(idx);
            }

            return {
                name: m.name || `Mat_${idx}`,
                baseColorFactor: new Float32Array(baseColorFactor),
                emissiveFactor: new Float32Array(emissiveFactor),
                metallic,
                roughness,
                baseTex: baseTexIdx >= 0 ? this.textures[baseTexIdx] : null,
                emissiveTex: emTexIdx >= 0 ? this.textures[emTexIdx] : null,
            };
        });

        this.defaultMaterial = {
            name: 'Default',
            baseColorFactor: new Float32Array([0.88, 0.86, 0.82, 1.0]),
            emissiveFactor: new Float32Array([0, 0, 0]),
            metallic: 0.0,
            roughness: 0.7,
            baseTex: null,
            emissiveTex: null,
        };
    }

    _getAccessorData(gltf, binBuffer, accessorIdx) {
        const acc = gltf.accessors[accessorIdx];
        const bv = gltf.bufferViews[acc.bufferView];
        const byteOffset = (bv.byteOffset || 0) + (acc.byteOffset || 0);
        const numComps = { SCALAR: 1, VEC2: 2, VEC3: 3, VEC4: 4, MAT4: 16 }[acc.type] || 1;
        const count = acc.count * numComps;

        let TypedArr = Float32Array;
        if (acc.componentType === 5123) TypedArr = Uint16Array;
        else if (acc.componentType === 5125) TypedArr = Uint32Array;
        else if (acc.componentType === 5121) TypedArr = Uint8Array;

        return {
            array: new TypedArr(binBuffer, byteOffset, count),
            count: acc.count,
            componentType: acc.componentType,
            numComps,
            byteStride: bv.byteStride || 0,
        };
    }

    _buildMeshes(gltf, binBuffer) {
        const gl = this.gl;
        let totalVerts = 0;
        let totalTris = 0;

        this.meshes = (gltf.meshes || []).map((meshDef) => {
            const prims = (meshDef.primitives || []).map((prim) => {
                const vao = gl.createVertexArray();
                gl.bindVertexArray(vao);

                const posAcc = this._getAccessorData(gltf, binBuffer, prim.attributes.POSITION);
                totalVerts += posAcc.count;
                const posBuf = gl.createBuffer();
                gl.bindBuffer(gl.ARRAY_BUFFER, posBuf);
                gl.bufferData(gl.ARRAY_BUFFER, posAcc.array, gl.STATIC_DRAW);
                gl.enableVertexAttribArray(0);
                gl.vertexAttribPointer(0, 3, gl.FLOAT, false, 0, 0);

                if (prim.attributes.NORMAL !== undefined) {
                    const normAcc = this._getAccessorData(gltf, binBuffer, prim.attributes.NORMAL);
                    const normBuf = gl.createBuffer();
                    gl.bindBuffer(gl.ARRAY_BUFFER, normBuf);
                    gl.bufferData(gl.ARRAY_BUFFER, normAcc.array, gl.STATIC_DRAW);
                    gl.enableVertexAttribArray(1);
                    gl.vertexAttribPointer(1, 3, gl.FLOAT, false, 0, 0);
                } else {
                    gl.disableVertexAttribArray(1);
                    gl.vertexAttrib3f(1, 0, 1, 0);
                }

                if (prim.attributes.TEXCOORD_0 !== undefined) {
                    const uvAcc = this._getAccessorData(gltf, binBuffer, prim.attributes.TEXCOORD_0);
                    const uvBuf = gl.createBuffer();
                    gl.bindBuffer(gl.ARRAY_BUFFER, uvBuf);
                    gl.bufferData(gl.ARRAY_BUFFER, uvAcc.array, gl.STATIC_DRAW);
                    gl.enableVertexAttribArray(2);
                    gl.vertexAttribPointer(2, 2, gl.FLOAT, false, 0, 0);
                } else {
                    gl.disableVertexAttribArray(2);
                    gl.vertexAttrib2f(2, 0, 0);
                }

                const hasSkinAttrs = (prim.attributes.JOINTS_0 !== undefined && prim.attributes.WEIGHTS_0 !== undefined);
                if (hasSkinAttrs) {
                    const jAcc = this._getAccessorData(gltf, binBuffer, prim.attributes.JOINTS_0);
                    const jFloat = new Float32Array(jAcc.array);
                    const jBuf = gl.createBuffer();
                    gl.bindBuffer(gl.ARRAY_BUFFER, jBuf);
                    gl.bufferData(gl.ARRAY_BUFFER, jFloat, gl.STATIC_DRAW);
                    gl.enableVertexAttribArray(3);
                    gl.vertexAttribPointer(3, 4, gl.FLOAT, false, 0, 0);

                    const wAcc = this._getAccessorData(gltf, binBuffer, prim.attributes.WEIGHTS_0);
                    const wFloat = (wAcc.componentType === 5126) ? wAcc.array : new Float32Array(wAcc.array);
                    const wBuf = gl.createBuffer();
                    gl.bindBuffer(gl.ARRAY_BUFFER, wBuf);
                    gl.bufferData(gl.ARRAY_BUFFER, wFloat, gl.STATIC_DRAW);
                    gl.enableVertexAttribArray(4);
                    gl.vertexAttribPointer(4, 4, gl.FLOAT, false, 0, 0);
                } else {
                    gl.disableVertexAttribArray(3);
                    gl.disableVertexAttribArray(4);
                }

                let indexCount = 0;
                let indexType = gl.UNSIGNED_SHORT;
                if (prim.indices !== undefined) {
                    const idxAcc = this._getAccessorData(gltf, binBuffer, prim.indices);
                    const idxBuf = gl.createBuffer();
                    gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, idxBuf);
                    gl.bufferData(gl.ELEMENT_ARRAY_BUFFER, idxAcc.array, gl.STATIC_DRAW);
                    indexCount = idxAcc.count;
                    indexType = idxAcc.componentType;
                    totalTris += Math.floor(indexCount / 3);
                } else {
                    indexCount = posAcc.count;
                    totalTris += Math.floor(indexCount / 3);
                }

                gl.bindVertexArray(null);
                return {
                    vao,
                    indexCount,
                    indexType,
                    hasIndices: prim.indices !== undefined,
                    hasSkinAttrs,
                    materialIdx: prim.material !== undefined ? prim.material : -1,
                };
            });
            return { name: meshDef.name || '', primitives: prims };
        });

        this.metrics.vertices = totalVerts;
        this.metrics.triangles = totalTris;
        this.metrics.meshCount = this.meshes.length;
    }

    _buildNodes(gltf) {
        this.nodes = (gltf.nodes || []).map((n, idx) => ({
            index: idx,
            name: n.name || `Node_${idx}`,
            meshIdx: n.mesh !== undefined ? n.mesh : -1,
            skinIdx: n.skin !== undefined ? n.skin : -1,
            children: n.children || [],
            translation: n.translation ? new Float32Array(n.translation) : new Float32Array([0, 0, 0]),
            rotation: n.rotation ? new Float32Array(n.rotation) : new Float32Array([0, 0, 0, 1]),
            scale: n.scale ? new Float32Array(n.scale) : new Float32Array([1, 1, 1]),
            matrix: n.matrix ? new Float32Array(n.matrix) : null,
            localMat: mat4Identity(),
            worldMat: mat4Identity(),
            normalMat: new Float32Array(9),
            hidden: n.name && (n.name.startsWith('NavBounds') || n.name.startsWith('Zone_') || n.name.startsWith('Stage_BeamCone')),
        }));

        const hasParent = new Set();
        this.nodes.forEach((n) => n.children.forEach((c) => hasParent.add(c)));
        this.rootNodeIndices = this.nodes.map((_, i) => i).filter((i) => !hasParent.has(i));
        this.metrics.nodeCount = this.nodes.length;
        this._updateWorldMatrices();
    }

    _buildSkins(gltf, binBuffer) {
        this.skins = (gltf.skins || []).map((skinDef) => {
            const joints = skinDef.joints || [];
            const ibmAcc = skinDef.inverseBindMatrices !== undefined
                ? this._getAccessorData(gltf, binBuffer, skinDef.inverseBindMatrices).array
                : null;
            const maxJoints = Math.min(joints.length, 72);
            return {
                joints: joints.slice(0, maxJoints),
                inverseBindMatrices: ibmAcc,
                jointMatrices: new Float32Array(72 * 16),
            };
        });
    }

    _updateSkins() {
        const tmpMat = new Float32Array(16);
        for (const skin of this.skins) {
            const numJ = skin.joints.length;
            for (let j = 0; j < numJ; j++) {
                const jointNode = this.nodes[skin.joints[j]];
                if (!jointNode) continue;
                if (skin.inverseBindMatrices) {
                    const ibm = skin.inverseBindMatrices.subarray(j * 16, j * 16 + 16);
                    mat4Multiply(tmpMat, jointNode.worldMat, ibm);
                    skin.jointMatrices.set(tmpMat, j * 16);
                } else {
                    skin.jointMatrices.set(jointNode.worldMat, j * 16);
                }
            }
        }
    }

    _buildAnimations(gltf, binBuffer) {
        this.animations = (gltf.animations || []).map((anim) => {
            const samplers = (anim.samplers || []).map((s) => ({
                input: this._getAccessorData(gltf, binBuffer, s.input).array,
                output: this._getAccessorData(gltf, binBuffer, s.output).array,
                interpolation: s.interpolation || 'LINEAR',
            }));
            const channels = (anim.channels || []).map((ch) => ({
                sampler: samplers[ch.sampler],
                targetNode: ch.target.node,
                path: ch.target.path,
            }));
            return { name: anim.name || 'Anim', channels };
        });
    }

    _evaluateAnimations(timeSec) {
        if (!this.animations.length) return;
        const loopT = timeSec % 3.0;
        for (const anim of this.animations) {
            for (const ch of anim.channels) {
                const node = this.nodes[ch.targetNode];
                if (!node || !ch.sampler) continue;
                const times = ch.sampler.input;
                const vals = ch.sampler.output;
                if (!times.length) continue;

                let idx = 0;
                while (idx < times.length - 2 && loopT > times[idx + 1]) {
                    idx++;
                }
                const t0 = times[idx];
                const t1 = times[Math.min(idx + 1, times.length - 1)];
                const alpha = t1 > t0 ? Math.max(0, Math.min(1, (loopT - t0) / (t1 - t0))) : 0;

                if (ch.path === 'rotation') {
                    const o0 = idx * 4;
                    const o1 = Math.min(idx + 1, times.length - 1) * 4;
                    for (let k = 0; k < 4; k++) {
                        node.rotation[k] = vals[o0 + k] * (1 - alpha) + vals[o1 + k] * alpha;
                    }
                    const len = Math.hypot(node.rotation[0], node.rotation[1], node.rotation[2], node.rotation[3]) || 1;
                    for (let k = 0; k < 4; k++) node.rotation[k] /= len;
                } else if (ch.path === 'translation') {
                    const o0 = idx * 3;
                    const o1 = Math.min(idx + 1, times.length - 1) * 3;
                    for (let k = 0; k < 3; k++) {
                        node.translation[k] = vals[o0 + k] * (1 - alpha) + vals[o1 + k] * alpha;
                    }
                }
            }
        }
    }

    _updateWorldMatrices() {
        const visit = (nodeIdx, parentWorld) => {
            const node = this.nodes[nodeIdx];
            if (!node) return;
            if (node.matrix) {
                node.localMat.set(node.matrix);
            } else {
                mat4FromTRS(node.localMat, node.translation, node.rotation, node.scale);
            }
            if (parentWorld) {
                mat4Multiply(node.worldMat, parentWorld, node.localMat);
            } else {
                node.worldMat.set(node.localMat);
            }
            mat3FromMat4(node.normalMat, node.worldMat);
            for (const childIdx of node.children) {
                visit(childIdx, node.worldMat);
            }
        };
        for (const rIdx of this.rootNodeIndices) {
            visit(rIdx, null);
        }
    }

    setQualityTier(tier) {
        this.qualityTier = tier;
        this.metrics.qualityTier = tier;
        if (tier === 'High') this.dprCap = Math.min(window.devicePixelRatio || 1, 2.0);
        else if (tier === 'Lite') this.dprCap = 0.85;
        else this.dprCap = Math.min(window.devicePixelRatio || 1, 1.25);
        this.resize();
    }

    setTimePreset(preset) {
        this.timePreset = preset;
        this.metrics.timePreset = preset;
    }

    setLiveStreamActive(active) {
        this.liveStreamActive = Boolean(active);
        this.metrics.liveStreamActive = this.liveStreamActive;
    }

    goToPresetCamera(presetId) {
        const presets = {
            foyer_entrance: { pos: [0.0, 1.82, 23.0], yaw: 0.0, pitch: 0.01 },
            reception_table: { pos: [0.4, 1.72, 28.4], yaw: 0.84, pitch: -0.02 },
            photobooth_nt: { pos: [-0.4, 1.72, 28.4], yaw: -0.84, pitch: -0.02 },
            central_aisle: { pos: [0.0, 1.60, 8.8], yaw: 0.0, pitch: 0.01 },
            banquet_table: { pos: [-1.4, 1.50, 8.2], yaw: 0.62, pitch: -0.08 },
            ceremony_stage: { pos: [0.0, 1.65, -2.2], yaw: 0.0, pitch: 0.03 },
            overview_cutaway: { pos: [-12.5, 9.5, 29.0], yaw: -0.45, pitch: -0.28 },
        };
        const p = presets[presetId] || presets.foyer_entrance;
        this.camera.pos = [...p.pos];
        this.camera.yaw = p.yaw;
        this.camera.pitch = p.pitch;
    }

    resize() {
        const rect = this.canvas.getBoundingClientRect();
        const w = Math.max(300, Math.round(rect.width * this.dprCap));
        const h = Math.max(200, Math.round(rect.height * this.dprCap));
        if (this.canvas.width !== w || this.canvas.height !== h) {
            this.canvas.width = w;
            this.canvas.height = h;
        }
        this.gl.viewport(0, 0, this.canvas.width, this.canvas.height);
    }

    _bindInputs() {
        window.addEventListener('resize', () => this.resize());
        window.addEventListener('keydown', (e) => {
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement?.tagName)) return;
            this.keys.add(e.code);
        });
        window.addEventListener('keyup', (e) => this.keys.delete(e.code));

        let dragging = false;
        let lastX = 0, lastY = 0;
        this.canvas.addEventListener('pointerdown', (e) => {
            dragging = true;
            lastX = e.clientX;
            lastY = e.clientY;
            this.canvas.setPointerCapture?.(e.pointerId);
        });
        this.canvas.addEventListener('pointermove', (e) => {
            if (!dragging) return;
            const dx = e.clientX - lastX;
            const dy = e.clientY - lastY;
            lastX = e.clientX;
            lastY = e.clientY;
            this.camera.yaw -= dx * 0.0042;
            this.camera.pitch = Math.max(-0.65, Math.min(0.65, this.camera.pitch - dy * 0.0035));
        });
        const stopDrag = () => { dragging = false; };
        this.canvas.addEventListener('pointerup', stopDrag);
        this.canvas.addEventListener('pointercancel', stopDrag);
    }

    _stepMovement(dt) {
        let fwd = this.moveInput.forward;
        let right = this.moveInput.right;
        if (this.keys.has('KeyW') || this.keys.has('ArrowUp')) fwd += 1;
        if (this.keys.has('KeyS') || this.keys.has('ArrowDown')) fwd -= 1;
        if (this.keys.has('KeyD') || this.keys.has('ArrowRight')) right += 1;
        if (this.keys.has('KeyA') || this.keys.has('ArrowLeft')) right -= 1;

        if (fwd === 0 && right === 0) return;
        const speed = 3.6 * dt;
        const sinY = Math.sin(this.camera.yaw);
        const cosY = Math.cos(this.camera.yaw);

        const nx = this.camera.pos[0] + (-sinY * fwd + cosY * right) * speed;
        const nz = this.camera.pos[2] + (-cosY * fwd - sinY * right) * speed;

        this.camera.pos[0] = Math.max(-8.5, Math.min(8.5, nx));
        this.camera.pos[2] = Math.max(-10.5, Math.min(31.5, nz));

        if (this.camera.pos[2] < -8.2 && Math.abs(this.camera.pos[0]) < 5.0) {
            this.camera.pos[1] = 1.60 + 0.65;
        } else if (this.camera.pos[2] < -5.0 && Math.abs(this.camera.pos[0]) < 1.4) {
            this.camera.pos[1] = 1.60 + 0.45;
        } else if (this.camera.pos[1] < 3.0) {
            this.camera.pos[1] = 1.60;
        }
    }

    _getLightingParams() {
        switch (this.timePreset) {
            case 'DAWN_05H':
                return {
                    sky: [0.08, 0.12, 0.26],
                    ambient: [0.34, 0.42, 0.68],
                    sunDir: [0.6, 0.25, 0.5],
                    sunColor: [0.55, 0.42, 0.58],
                    chandelierBoost: 0.85,
                };
            case 'MORNING_08H':
                return {
                    sky: [0.52, 0.72, 0.94],
                    ambient: [0.68, 0.72, 0.78],
                    sunDir: [0.5, 0.65, 0.4],
                    sunColor: [0.95, 0.88, 0.74],
                    chandelierBoost: 0.75,
                };
            case 'NOON_12H':
                return {
                    sky: [0.75, 0.88, 0.98],
                    ambient: [0.85, 0.88, 0.92],
                    sunDir: [0.15, 0.96, 0.2],
                    sunColor: [1.05, 1.02, 0.98],
                    chandelierBoost: 0.70,
                };
            case 'SUNSET_17H':
                return {
                    sky: [0.58, 0.24, 0.16],
                    ambient: [0.68, 0.42, 0.34],
                    sunDir: [-0.65, 0.28, 0.45],
                    sunColor: [1.10, 0.48, 0.22],
                    chandelierBoost: 1.05,
                };
            default:
                return {
                    sky: [0.02, 0.025, 0.05],
                    ambient: [0.38, 0.35, 0.42],
                    sunDir: [0.0, 0.8, 0.4],
                    sunColor: [0.22, 0.24, 0.35],
                    chandelierBoost: 1.35,
                };
        }
    }

    startLoop() {
        const gl = this.gl;
        const projMat = mat4Identity();
        const viewMat = mat4Identity();
        const viewProjMat = mat4Identity();
        let lastTime = performance.now();
        let frameCounter = 0;
        let fpsAccum = 0;

        const renderFrame = (now) => {
            const dt = Math.min(0.1, (now - lastTime) / 1000);
            const frameMs = now - lastTime;
            lastTime = now;
            frameCounter++;
            fpsAccum += dt;

            this._stepMovement(dt);
            this._evaluateAnimations(now / 1000);
            this._updateWorldMatrices();
            this._updateSkins();
            this._updateLiveStreamTexture(now / 1000);

            const lp = this._getLightingParams();
            gl.clearColor(lp.sky[0], lp.sky[1], lp.sky[2], 1.0);
            gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
            gl.enable(gl.DEPTH_TEST);
            gl.disable(gl.CULL_FACE);

            const aspect = this.canvas.width / Math.max(1, this.canvas.height);
            mat4Perspective(projMat, (this.camera.fovDeg * Math.PI) / 180, aspect, 0.15, 95.0);

            const cosP = Math.cos(this.camera.pitch);
            const sinP = Math.sin(this.camera.pitch);
            const sinY = Math.sin(this.camera.yaw);
            const cosY = Math.cos(this.camera.yaw);
            const target = [
                this.camera.pos[0] - sinY * cosP,
                this.camera.pos[1] + sinP,
                this.camera.pos[2] - cosY * cosP,
            ];
            mat4LookAt(viewMat, this.camera.pos, target, [0, 1, 0]);
            mat4Multiply(viewProjMat, projMat, viewMat);

            gl.useProgram(this.program);
            gl.uniformMatrix4fv(this.uniforms.u_viewProj, false, viewProjMat);
            gl.uniform3fv(this.uniforms.u_cameraPos, this.camera.pos);
            gl.uniform3fv(this.uniforms.u_ambientColor, lp.ambient);
            gl.uniform3fv(this.uniforms.u_sunDir, lp.sunDir);
            gl.uniform3fv(this.uniforms.u_sunColor, lp.sunColor);
            gl.uniform1f(this.uniforms.u_chandelierBoost, lp.chandelierBoost);

            const cutawayOverview = this.camera.pos[1] > 5.0;
            let drawCalls = 0;

            for (const node of this.nodes) {
                if (node.meshIdx < 0 || node.hidden) continue;
                if (cutawayOverview && (node.name.startsWith('Roof_Starlight') || node.name.startsWith('Ceiling_'))) {
                    continue;
                }
                const mesh = this.meshes[node.meshIdx];
                if (!mesh) continue;

                const skin = node.skinIdx >= 0 ? this.skins[node.skinIdx] : null;
                if (skin) {
                    gl.uniform1i(this.uniforms.u_isSkinned, 1);
                    gl.uniformMatrix4fv(this.uniforms.u_jointMats, false, skin.jointMatrices);
                } else {
                    gl.uniform1i(this.uniforms.u_isSkinned, 0);
                    gl.uniformMatrix4fv(this.uniforms.u_model, false, node.worldMat);
                    gl.uniformMatrix3fv(this.uniforms.u_normalMat, false, node.normalMat);
                }

                for (const prim of mesh.primitives) {
                    const mat = this.materials[prim.materialIdx] || this.defaultMaterial;
                    const isLedMat = this.liveStreamActive && this.ledMaterialIndices.includes(prim.materialIdx);

                    gl.uniform4fv(this.uniforms.u_baseColorFactor, mat.baseColorFactor);
                    gl.uniform3fv(this.uniforms.u_emissiveFactor, isLedMat ? [1.2, 1.2, 1.2] : mat.emissiveFactor);
                    gl.uniform1f(this.uniforms.u_metallic, mat.metallic);
                    gl.uniform1f(this.uniforms.u_roughness, mat.roughness);

                    const activeBaseTex = isLedMat ? this.liveTex : mat.baseTex;
                    if (activeBaseTex) {
                        gl.activeTexture(gl.TEXTURE0);
                        gl.bindTexture(gl.TEXTURE_2D, activeBaseTex);
                        gl.uniform1i(this.uniforms.u_baseTex, 0);
                        gl.uniform1i(this.uniforms.u_hasBaseTex, 1);
                    } else {
                        gl.uniform1i(this.uniforms.u_hasBaseTex, 0);
                    }

                    const activeEmTex = isLedMat ? this.liveTex : mat.emissiveTex;
                    if (activeEmTex) {
                        gl.activeTexture(gl.TEXTURE1);
                        gl.bindTexture(gl.TEXTURE_2D, activeEmTex);
                        gl.uniform1i(this.uniforms.u_emissiveTex, 1);
                        gl.uniform1i(this.uniforms.u_hasEmissiveTex, 1);
                    } else {
                        gl.uniform1i(this.uniforms.u_hasEmissiveTex, 0);
                    }

                    gl.bindVertexArray(prim.vao);
                    if (prim.hasIndices) {
                        gl.drawElements(gl.TRIANGLES, prim.indexCount, prim.indexType, 0);
                    } else {
                        gl.drawArrays(gl.TRIANGLES, 0, prim.indexCount);
                    }
                    drawCalls++;
                }
            }

            this.metrics.drawCalls = drawCalls;
            if (fpsAccum >= 0.35) {
                this.metrics.fps = Math.min(120, Math.round(frameCounter / fpsAccum));
                this.metrics.frameTimeMs = Number((frameMs).toFixed(1));
                frameCounter = 0;
                fpsAccum = 0;
                window.__MAYHEN_3D_METRICS__ = { ...this.metrics };
                this.onMetrics(this.metrics);
            }

            requestAnimationFrame(renderFrame);
        };

        requestAnimationFrame(renderFrame);
    }
}
