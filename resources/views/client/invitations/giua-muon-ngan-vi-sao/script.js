/* ==========================================================================
   HIỆU ỨNG WEBGL "MÀN SAO RƠI" (MONOCHROME BLACK & WHITE)
   WebGL1 thuần, không thư viện ngoài.
   ========================================================================== */
(function () {
  'use strict';

  var ROOM = { ceil: 1.0, floor: -1.25, zNear: 0.9, zCeilFar: 4.8, zFloorFar: 7.8 };
  var POOL = { x: 0, z: 4.2, r: 1.2 };

  function focalY(aspect) {
    return 1.75 * Math.min(1.45, Math.max(1, Math.pow(1.778 / aspect, 0.3)));
  }

  var DROP_DURATION = 1.45;
  var MAX_PIXELS = 2400000;

  var BEAMS = [
    [-1.15, 2.8, 0.45, 0.25],
    [-0.85, 3.8, 0.30, 0.30],
    [-0.60, 4.9, 0.22, 0.25],
    [0.60, 4.6, 0.22, 0.25],
    [0.85, 3.6, 0.30, 0.30],
    [1.15, 2.6, 0.45, 0.25]
  ];

  var PROJECT =
    'uniform vec2 uFocal;\n' +
    'uniform vec3 uCam;\n' +
    'vec4 project(vec3 p) { vec3 q = p - uCam; return vec4(q.x * uFocal.x, q.y * uFocal.y, q.z - 0.3, q.z); }\n';

  var BG_VS =
    'attribute vec2 aPos;\n' +
    'varying vec2 vP;\n' +
    'void main() { vP = aPos; gl_Position = vec4(aPos, 0.0, 1.0); }\n';

  var BG_FS =
    'precision mediump float;\n' +
    'uniform vec2 uRes;\n' +
    'uniform float uTime;\n' +
    'uniform float uFade;\n' +
    'varying vec2 vP;\n' +
    'float hash(vec2 p) { return fract(sin(dot(p, vec2(12.9898, 78.233))) * 43758.5453); }\n' +
    'float noise(vec2 p) {\n' +
    '  vec2 i = floor(p);\n' +
    '  vec2 f = fract(p);\n' +
    '  f = f * f * (3.0 - 2.0 * f);\n' +
    '  float a = hash(i);\n' +
    '  float b = hash(i + vec2(1.0, 0.0));\n' +
    '  float c = hash(i + vec2(0.0, 1.0));\n' +
    '  float d = hash(i + vec2(1.0, 1.0));\n' +
    '  return mix(mix(a, b, f.x), mix(c, d, f.x), f.y);\n' +
    '}\n' +
    'float fbm(vec2 p) {\n' +
    '  float v = 0.0;\n' +
    '  v += 0.500 * noise(p); p *= 2.02;\n' +
    '  v += 0.250 * noise(p); p *= 2.03;\n' +
    '  v += 0.125 * noise(p);\n' +
    '  return v;\n' +
    '}\n' +
    'void main() {\n' +
    '  float asp = uRes.x / uRes.y;\n' +
    '  vec2 uv = vec2(vP.x * min(asp, 1.6), vP.y);\n' +
    '  float ang = 0.62;\n' +
    '  mat2 rot = mat2(cos(ang), sin(ang), -sin(ang), cos(ang));\n' +
    '  vec2 gp = rot * uv;\n' +
    '  float meander = sin(gp.y * 1.2 + uTime * 0.02) * 0.12 + cos(gp.y * 2.4) * 0.05;\n' +
    '  float dCenter = abs(gp.x - meander);\n' +
    '  float cloud1 = fbm(vec2(gp.x * 2.2, gp.y * 1.1 - uTime * 0.012));\n' +
    '  float cloud2 = fbm(vec2(gp.x * 4.8 + 1.8, gp.y * 2.8 + uTime * 0.008));\n' +
    '  float cloud = cloud1 * 0.6 + cloud2 * 0.4;\n' +
    '  float dust = 1.0 - 0.50 * exp(-pow((gp.x - meander * 1.1 + 0.03) / 0.08, 2.0)) * smoothstep(0.2, 0.65, cloud1);\n' +
    '  float band = exp(-dCenter * dCenter * 3.8) * (0.35 + 0.65 * cloud) * dust;\n' +
    '  vec2 corePos = vec2(gp.x - meander, gp.y - 0.05);\n' +
    '  float coreDist = length(vec2(corePos.x * 1.5, corePos.y * 0.8));\n' +
    '  float coreGlow = exp(-coreDist * coreDist * 3.2) * (0.45 + 0.25 * cloud2);\n' +
    '  vec3 deepSpace = mix(vec3(0.008, 0.012, 0.028), vec3(0.002, 0.004, 0.012), length(vP * vec2(0.65, 0.95)) * 0.85);\n' +
    '  vec3 nebulaViolet = vec3(0.09, 0.04, 0.14);\n' +
    '  vec3 cosmicCyan = vec3(0.03, 0.08, 0.15);\n' +
    '  vec3 stardustGold = vec3(0.14, 0.11, 0.06);\n' +
    '  vec3 stardustCore = vec3(0.18, 0.15, 0.10);\n' +
    '  vec3 col = deepSpace;\n' +
    '  col += nebulaViolet * band * 0.75;\n' +
    '  col += cosmicCyan * pow(band, 1.4) * 0.65;\n' +
    '  col += stardustGold * pow(band, 1.8) * 0.85;\n' +
    '  col += stardustCore * coreGlow * 0.95;\n' +
    '  float down = 1.0 - (vP.y * 0.5 + 0.5);\n' +
    '  float cone = exp(-pow(uv.x / (0.5 + 0.6 * down), 2.0)) * (0.35 + 0.25 * sin(uTime * 0.12));\n' +
    '  col += vec3(0.02, 0.035, 0.065) * cone * (0.3 + 0.7 * down);\n' +
    '  col *= uFade;\n' +
    '  col += (hash(gl_FragCoord.xy + fract(uTime) * 61.0) - 0.5) / 255.0;\n' +
    '  gl_FragColor = vec4(col, 1.0);\n' +
    '}\n';

  var PLANE_VS =
    'precision highp float;\n' + PROJECT +
    'attribute vec2 aPos;\n' +
    'uniform vec3 uCenter;\n' +
    'uniform vec2 uHalf;\n' +
    'varying vec2 vXZ;\n' +
    'void main() {\n' +
    '  vec3 p = vec3(uCenter.x + aPos.x * uHalf.x, uCenter.y, uCenter.z + aPos.y * uHalf.y);\n' +
    '  vXZ = p.xz;\n' +
    '  gl_Position = project(p);\n' +
    '}\n';

  var PLANE_FS =
    'precision highp float;\n' +
    'uniform float uMode;\n' +
    'uniform vec3 uPool;\n' +
    'uniform float uRipple;\n' +
    'uniform float uGlow;\n' +
    'uniform float uFade;\n' +
    'varying vec2 vXZ;\n' +
    'void main() {\n' +
    '  float a;\n' +
    '  vec3 col;\n' +
    '  if (uMode < 0.5) {\n' +
    '    float d = length((vXZ - uPool.xy) * vec2(1.0, 1.35));\n' +
    '    float r2 = uPool.z * uPool.z;\n' +
    '    a = exp(-d * d / (r2 * 0.9)) * 0.45 * uGlow + exp(-d * d / (r2 * 7.0)) * 0.14;\n' +
    '    if (uRipple >= 0.0) {\n' +
    '      float ring = exp(-pow((d - uRipple * 1.9) / 0.32, 2.0)) * exp(-uRipple * 0.8);\n' +
    '      a += ring * 0.35;\n' +
    '    }\n' +
    '    col = mix(vec3(0.22, 0.38, 0.58), vec3(0.50, 0.42, 0.28), 0.45 + 0.35 * sin(d * 2.2));\n' +
    '    if (uRipple >= 0.0) {\n' +
    '      col = mix(col, vec3(0.78, 0.72, 0.58), exp(-uRipple * 0.65));\n' +
    '    }\n' +
    '  } else {\n' +
    '    float dz = (vXZ.y - 4.3) / 1.1;\n' +
    '    a = exp(-dz * dz) * exp(-vXZ.x * vXZ.x / 40.0) * 0.08;\n' +
    '    col = vec3(0.20, 0.28, 0.45);\n' +
    '  }\n' +
    '  gl_FragColor = vec4(col * a * uFade, 1.0);\n' +
    '}\n';

  var BEAM_VS =
    'precision highp float;\n' + PROJECT +
    'attribute vec2 aCorner;\n' +
    'attribute vec4 aBeam;\n' +
    'uniform float uTime;\n' +
    'uniform float uCeil;\n' +
    'uniform float uFloor;\n' +
    'varying vec2 vUV;\n' +
    'varying float vSeed;\n' +
    'varying float vStrength;\n' +
    'varying float vDrift;\n' +
    'varying float vFade;\n' +
    'void main() {\n' +
    '  vSeed = fract(aBeam.x * 0.731 + aBeam.y * 0.417);\n' +
    '  float breathe = 0.55 + 0.45 * sin(uTime * (0.16 + 0.22 * fract(vSeed * 3.1)) + vSeed * 40.0);\n' +
    '  vStrength = aBeam.w * breathe;\n' +
    '  vDrift = mod(uTime * (0.7 + fract(vSeed * 5.7)), 6.2831853);\n' +
    '  float sway = sin(uTime * 0.045 + vSeed * 20.0) * 0.06;\n' +
    '  vec3 p = vec3(aBeam.x + sway + aCorner.x * aBeam.z, mix(uFloor, uCeil + 0.3, aCorner.y), aBeam.y);\n' +
    '  vUV = aCorner;\n' +
    '  vFade = smoothstep(11.0, 2.8, p.z - uCam.z);\n' +
    '  gl_Position = project(p);\n' +
    '}\n';

  var BEAM_FS =
    'precision mediump float;\n' +
    'uniform float uBeam;\n' +
    'varying vec2 vUV;\n' +
    'varying float vSeed;\n' +
    'varying float vStrength;\n' +
    'varying float vDrift;\n' +
    'varying float vFade;\n' +
    'void main() {\n' +
    '  float u = vUV.x;\n' +
    '  float v = vUV.y;\n' +
    '  float g = exp(-u * u * 4.5);\n' +
    '  float core = exp(-u * u * 45.0);\n' +
    '  float prof = 0.25 + 0.75 * smoothstep(0.2, 0.95, v) + 0.25 * exp(-v * v * 40.0);\n' +
    '  prof *= smoothstep(0.0, 0.06, v) * (1.0 - smoothstep(0.84, 1.0, v));\n' +
    '  float streak = 0.75 + 0.25 * sin(u * 17.0 + vSeed * 31.0) * sin(u * 5.3 + vSeed * 7.0);\n' +
    '  float drift = 0.88 + 0.12 * sin(v * 24.0 - vDrift + vSeed * 9.0);\n' +
    '  float a = (g * 0.45 + core * 0.25) * prof * streak * drift * vFade * uBeam * vStrength;\n' +
    '  vec3 col = mix(vec3(0.35, 0.55, 0.85), vec3(0.70, 0.62, 0.45), 0.42 + 0.35 * sin(vSeed * 17.0));\n' +
    '  gl_FragColor = vec4(col * a, 1.0);\n' +
    '}\n';

  var STAR_VS =
    'precision highp float;\n' + PROJECT +
    'attribute vec3 aPos;\n' +
    'attribute vec4 aData;\n' +
    'uniform float uTime;\n' +
    'uniform float uHalfH;\n' +
    'uniform float uCeil;\n' +
    'uniform float uFloor;\n' +
    'uniform vec3 uPool;\n' +
    'uniform float uRipple;\n' +
    'uniform float uGlow;\n' +
    'uniform float uForm;\n' +
    'uniform float uFade;\n' +
    'varying float vBright;\n' +
    'varying vec3 vColor;\n' +
    'void main() {\n' +
    '  vec3 p = aPos;\n' +
    '  float bri = aData.y;\n' +
    '  float s = aData.z;\n' +
    '  float kind = aData.w;\n' +
    '  float t = uTime;\n' +
    '  if (kind < 1.5) {\n' +
    '    if (kind < 0.5) {\n' +
    '      p.x += sin(t * 0.06 + s * 40.0) * 0.06;\n' +
    '      p.y += sin(t * 0.21 + s * 90.0) * 0.025;\n' +
    '      p.z += cos(t * 0.05 + s * 70.0) * 0.05;\n' +
    '    } else {\n' +
    '      p.x += sin(t * 0.05 + s * 50.0) * 0.04;\n' +
    '      p.y += sin(t * 0.33 + s * 80.0) * 0.012;\n' +
    '      float d = length((p.xz - uPool.xy) * vec2(1.0, 1.35));\n' +
    '      bri *= 0.55 + 1.5 * exp(-d * d / (uPool.z * uPool.z)) * uGlow;\n' +
    '      if (uRipple >= 0.0) {\n' +
    '        float ring = exp(-pow((d - uRipple * 1.9) / 0.28, 2.0)) * exp(-uRipple * 0.75);\n' +
    '        bri *= 1.0 + 3.2 * ring;\n' +
    '        p.y += ring * 0.07;\n' +
    '      }\n' +
    '    }\n' +
    '    float k = clamp(uForm * 1.7 - fract(s * 2.93) * 0.7, 0.0, 1.0);\n' +
    '    k = k * k * (3.0 - 2.0 * k);\n' +
    '    vec3 start = vec3(p.x * 0.7 + sin(s * 211.0) * 0.8, mix(uFloor, uCeil, fract(s * 17.17)), p.z);\n' +
    '    p = mix(start, p, k);\n' +
    '    bri *= 0.25 + 0.75 * k;\n' +
    '  } else {\n' +
    '    float span = uCeil - uFloor;\n' +
    '    float speed = 0.035 + 0.11 * fract(s * 7.31);\n' +
    '    float y = uCeil - mod(t * speed + s * 97.0, span);\n' +
    '    p.y = y;\n' +
    '    p.x += sin(t * 0.37 + s * 60.0) * 0.07;\n' +
    '    p.z += cos(t * 0.29 + s * 30.0) * 0.05;\n' +
    '    bri *= smoothstep(uCeil + 0.02, uCeil - 0.3, y) * smoothstep(uFloor - 0.02, uFloor + 0.4, y);\n' +
    '  }\n' +
    '  float tw = 0.5 + 0.5 * sin(t * (0.6 + 2.8 * fract(s * 13.7)) + s * 311.0);\n' +
    '  bri *= mix(0.45 + 0.55 * tw, 0.12 + 2.6 * pow(tw, 8.0), step(0.88, fract(s * 5.23)));\n' +
    '  vec4 clip = project(p);\n' +
    '  float z = max(clip.w, 0.15);\n' +
    '  float px = aData.x * uFocal.y * uHalfH / z;\n' +
    '  float minPx = 1.5;\n' +
    '  bri *= px < minPx ? px / minPx : clamp(10.0 / px, 0.4, 1.0);\n' +
    '  bri *= smoothstep(9.5, 3.8, z) * smoothstep(0.3, 1.1, z) * uFade;\n' +
    '  gl_PointSize = max(px, minPx);\n' +
    '  gl_Position = clip;\n' +
    '  vBright = bri;\n' +
    '  float starHue = fract(s * 7.31);\n' +
    '  vec3 starCol = mix(vec3(0.72, 0.86, 1.0), vec3(1.0, 0.94, 0.76), step(0.35, starHue));\n' +
    '  starCol = mix(starCol, vec3(1.0, 1.0, 1.0), step(0.72, starHue));\n' +
    '  vColor = starCol;\n' +
    '}\n';

  var STAR_FS =
    'precision mediump float;\n' +
    'varying float vBright;\n' +
    'varying vec3 vColor;\n' +
    'void main() {\n' +
    '  vec2 c = gl_PointCoord * 2.0 - 1.0;\n' +
    '  float r2 = dot(c, c);\n' +
    '  if (r2 >= 1.0) discard;\n' +
    '  float a = exp(-r2 * 4.6) * 1.15 + (1.0 - r2) * 0.16;\n' +
    '  gl_FragColor = vec4(vColor * a * vBright, 1.0);\n' +
    '}\n';

  var ORB_VS =
    'precision highp float;\n' + PROJECT +
    'attribute float aIdx;\n' +
    'uniform float uDropT;\n' +
    'uniform float uDur;\n' +
    'uniform vec3 uFrom;\n' +
    'uniform vec3 uTo;\n' +
    'uniform float uRest;\n' +
    'uniform float uAlpha;\n' +
    'uniform float uHalfH;\n' +
    'uniform float uTrail;\n' +
    'varying float vBright;\n' +
    'void main() {\n' +
    '  float tt = uDropT - aIdx * 0.006;\n' +
    '  float x = clamp(tt / uDur, 0.0, 1.0);\n' +
    '  vec3 p = mix(uFrom, uTo, x * x);\n' +
    '  float k = aIdx / uTrail;\n' +
    '  float bri = (1.0 - k) * (1.0 - k);\n' +
    '  if (uRest < 0.5) {\n' +
    '    bri *= step(0.0, tt) * (1.0 - smoothstep(uDur, uDur + 0.2, uDropT));\n' +
    '  }\n' +
    '  vec4 clip = project(p);\n' +
    '  float size = aIdx < 0.5 ? 0.22 : 0.07 * (1.0 - k) + 0.02;\n' +
    '  gl_PointSize = max(size * uFocal.y * uHalfH / max(clip.w, 0.15), 1.5);\n' +
    '  gl_Position = clip;\n' +
    '  vBright = bri * uAlpha;\n' +
    '}\n';

  var ORB_FS =
    'precision mediump float;\n' +
    'varying float vBright;\n' +
    'void main() {\n' +
    '  vec2 c = gl_PointCoord * 2.0 - 1.0;\n' +
    '  float r2 = dot(c, c);\n' +
    '  if (r2 >= 1.0) discard;\n' +
    '  float a = exp(-r2 * 28.0) * 1.8 + exp(-r2 * 6.0) * 0.45 + (1.0 - r2) * 0.08;\n' +
    '  vec3 col = vec3(1.0, 0.98, 0.92);\n' +
    '  gl_FragColor = vec4(col * a * vBright, 1.0);\n' +
    '}\n';

  function mulberry32(seed) {
    return function () {
      seed |= 0;
      seed = (seed + 0x6D2B79F5) | 0;
      var t = Math.imul(seed ^ (seed >>> 15), 1 | seed);
      t = (t + Math.imul(t ^ (t >>> 7), 61 | t)) ^ t;
      return ((t ^ (t >>> 14)) >>> 0) / 4294967296;
    };
  }

  function makeNoise(rand) {
    var SIZE = 64;
    var grid = new Float32Array(SIZE * SIZE);
    for (var i = 0; i < grid.length; i++) grid[i] = rand();
    function at(x, y) { return grid[((y % SIZE + SIZE) % SIZE) * SIZE + ((x % SIZE + SIZE) % SIZE)]; }
    return function (x, y) {
      var xi = Math.floor(x), yi = Math.floor(y);
      var fx = x - xi, fy = y - yi;
      fx = fx * fx * (3 - 2 * fx);
      fy = fy * fy * (3 - 2 * fy);
      var a = at(xi, yi) + (at(xi + 1, yi) - at(xi, yi)) * fx;
      var b = at(xi, yi + 1) + (at(xi + 1, yi + 1) - at(xi, yi + 1)) * fx;
      return a + (b - a) * fy;
    };
  }

  function compile(gl, type, src) {
    var sh = gl.createShader(type);
    gl.shaderSource(sh, src);
    gl.compileShader(sh);
    if (!gl.getShaderParameter(sh, gl.COMPILE_STATUS)) {
      throw new Error(gl.getShaderInfoLog(sh) || 'shader compile failed');
    }
    return sh;
  }

  function program(gl, vs, fs) {
    var p = gl.createProgram();
    gl.attachShader(p, compile(gl, gl.VERTEX_SHADER, vs));
    gl.attachShader(p, compile(gl, gl.FRAGMENT_SHADER, fs));
    gl.linkProgram(p);
    if (!gl.getProgramParameter(p, gl.LINK_STATUS)) {
      throw new Error(gl.getProgramInfoLog(p) || 'program link failed');
    }
    var info = { program: p, attr: {}, uni: {} };
    var n = gl.getProgramParameter(p, gl.ACTIVE_ATTRIBUTES);
    for (var i = 0; i < n; i++) {
      var a = gl.getActiveAttrib(p, i);
      info.attr[a.name] = gl.getAttribLocation(p, a.name);
    }
    n = gl.getProgramParameter(p, gl.ACTIVE_UNIFORMS);
    for (i = 0; i < n; i++) {
      var u = gl.getActiveUniform(p, i);
      info.uni[u.name] = gl.getUniformLocation(p, u.name);
    }
    return info;
  }

  function buildStars(aspect, density) {
    var rand = mulberry32(2712);
    var noise = makeNoise(rand);
    var fx = focalY(aspect) / aspect;
    var areaScale = Math.min(Math.max(aspect / 1.778, 0.55), 2.4);
    var nCeil = Math.round(42000 * density * areaScale);
    var nFloor = Math.round(62000 * density * areaScale);
    var nFall = Math.round(3200 * density * areaScale);
    var data = new Float32Array((nCeil + nFloor + nFall) * 7);
    var o = 0;

    function push(x, y, z, size, bri, kind) {
      data[o++] = x; data[o++] = y; data[o++] = z;
      data[o++] = size; data[o++] = bri; data[o++] = rand(); data[o++] = kind;
    }
    function starSize() {
      var r = rand();
      return 0.005 + 0.012 * r * r * r + (rand() < 0.04 ? 0.014 : 0);
    }
    function starBright() {
      var r = rand();
      return 0.45 + 0.9 * r * r + (rand() < 0.05 ? 1.0 : 0);
    }
    function halfWidth(z) { return z / fx * 1.12 + 0.35; }
    function expo(mean) { return -Math.log(1 - rand() * 0.999) * mean; }

    var i = 0;
    var guard = 0;
    while (i < nCeil && guard++ < nCeil * 6) {
      var z = ROOM.zNear + (ROOM.zCeilFar + 0.6 - ROOM.zNear) * Math.pow(rand(), 0.42);
      var x = (rand() * 2 - 1) * halfWidth(z);
      if (rand() > 0.3 + 0.7 * noise(x * 0.9 + 11, z * 1.7)) continue;
      var y = ROOM.ceil + expo(0.09) * (rand() < 0.85 ? 1 : 3.5);
      if (rand() < 0.12) y = ROOM.ceil - expo(0.06);
      push(x, y, z, starSize(), starBright() * (z > ROOM.zCeilFar ? 0.6 : 1), 0);
      i++;
    }
    nCeil = i;

    i = 0;
    guard = 0;
    while (i < nFloor && guard++ < nFloor * 6) {
      var fz, fxw;
      if (rand() < 0.28) {
        fz = POOL.z + (rand() + rand() + rand() - 1.5) * 2.1;
        fxw = POOL.x + (rand() + rand() + rand() - 1.5) * 3.4;
      } else {
        fz = ROOM.zNear + (ROOM.zFloorFar - ROOM.zNear) * Math.pow(rand(), 0.8);
        fxw = (rand() * 2 - 1) * halfWidth(fz);
      }
      if (fz < 0.6) continue;
      if (rand() > 0.35 + 0.65 * noise(fxw * 1.1 + 40, fz * 1.4 + 7)) continue;
      var fy = ROOM.floor + expo(0.045) * (rand() < 0.9 ? 1 : 4);
      push(fxw, fy, fz, starSize() * (fz < 2.4 ? 1.5 : 1.1), starBright(), 1);
      i++;
    }
    nFloor = i;

    for (i = 0; i < nFall; i++) {
      var sz, sx;
      if (rand() < 0.5) {
        var b = BEAMS[Math.floor(rand() * BEAMS.length)];
        sz = b[1] + (rand() - 0.5) * 0.4;
        sx = b[0] * b[1] / fx + (rand() + rand() - 1) * b[2] * 0.8;
      } else {
        sz = 1.4 + 5.2 * rand();
        sx = (rand() * 2 - 1) * halfWidth(sz);
      }
      push(sx, 0, sz, 0.004 + 0.006 * rand() * rand(), 0.35 + 0.65 * rand(), 2);
    }

    return { data: data.subarray(0, o), groups: [[0, nCeil], [nCeil, nFloor], [nCeil + nFloor, nFall]] };
  }

  function buildBeams(aspect) {
    var fx = focalY(aspect) / aspect;
    var corners = [[-1, 0], [1, 0], [1, 1], [-1, 0], [1, 1], [-1, 1]];
    var data = new Float32Array(BEAMS.length * 6 * 6);
    var o = 0;
    for (var i = 0; i < BEAMS.length; i++) {
      var b = BEAMS[i];
      var worldX = b[0] * b[1] / fx;
      for (var c = 0; c < 6; c++) {
        data[o++] = corners[c][0];
        data[o++] = corners[c][1];
        data[o++] = worldX;
        data[o++] = b[1];
        data[o++] = b[2];
        data[o++] = b[3];
      }
    }
    return data;
  }

  function createEffect(canvas, options) {
    options = options || {};
    var density = options.density || 1;
    var manual = !!options.manual;
    var gl = canvas.getContext('webgl', { alpha: false, antialias: false, depth: false, stencil: false, powerPreference: 'high-performance' }) ||
      canvas.getContext('experimental-webgl', { alpha: false, antialias: false, depth: false });
    if (!gl) return null;

    var reduceMotion = !manual && window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var progBg, progPlane, progBeam, progStar, progOrb;
    try {
      progBg = program(gl, BG_VS, BG_FS);
      progPlane = program(gl, PLANE_VS, PLANE_FS);
      progBeam = program(gl, BEAM_VS, BEAM_FS);
      progStar = program(gl, STAR_VS, STAR_FS);
      progOrb = program(gl, ORB_VS, ORB_FS);
    } catch (err) {
      if (window.console) console.warn('[sao-roi]', err);
      return null;
    }

    var bufTri = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, bufTri);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), gl.STATIC_DRAW);

    var bufQuad = gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER, bufQuad);
    gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 1, -1, 1, 1, -1, -1, 1, 1, -1, 1]), gl.STATIC_DRAW);

    var ORB_TRAIL = 70;
    var bufOrb = gl.createBuffer();
    var orbIdx = new Float32Array(ORB_TRAIL + 1);
    for (var oi = 0; oi <= ORB_TRAIL; oi++) orbIdx[oi] = oi;
    gl.bindBuffer(gl.ARRAY_BUFFER, bufOrb);
    gl.bufferData(gl.ARRAY_BUFFER, orbIdx, gl.STATIC_DRAW);

    var bufStars = gl.createBuffer();
    var bufBeams = gl.createBuffer();
    var starGroups = [];
    var builtAspect = 0;

    var width = 0, height = 0, scale = Math.min(window.devicePixelRatio || 1, 1.5);
    var detail = 1;
    var frameTimes = [];
    var start = performance.now();
    var lastFrame = start;
    var raf = 0;
    var dropAt = null;
    var restFrom = null;

    function rebuild(aspect) {
      var stars = buildStars(aspect, density);
      starGroups = stars.groups;
      gl.bindBuffer(gl.ARRAY_BUFFER, bufStars);
      gl.bufferData(gl.ARRAY_BUFFER, stars.data, gl.STATIC_DRAW);
      gl.bindBuffer(gl.ARRAY_BUFFER, bufBeams);
      gl.bufferData(gl.ARRAY_BUFFER, buildBeams(aspect), gl.STATIC_DRAW);
      builtAspect = aspect;
    }

    function resize() {
      var cssW = manual ? options.width : (canvas.clientWidth || window.innerWidth);
      var cssH = manual ? options.height : (canvas.clientHeight || window.innerHeight);
      var s = manual ? 1 : scale;
      if (!manual && cssW * cssH * s * s > MAX_PIXELS) s = Math.sqrt(MAX_PIXELS / (cssW * cssH));
      var w = Math.max(1, Math.round(cssW * s));
      var h = Math.max(1, Math.round(cssH * s));
      if (w !== width || h !== height) {
        width = canvas.width = w;
        height = canvas.height = h;
      }
      var aspect = cssW / cssH;
      if (!builtAspect || Math.abs(aspect / builtAspect - 1) > 0.08) rebuild(aspect);
    }

    function useProgram(info, t, aspect) {
      gl.useProgram(info.program);
      var u = info.uni;
      if (u.uFocal) gl.uniform2f(u.uFocal, focalY(builtAspect) / aspect, focalY(builtAspect));
      if (u.uCam) {
        gl.uniform3f(u.uCam, Math.sin(t * 0.031) * 0.14 * Math.min(1, aspect * aspect), Math.sin(t * 0.023) * 0.035, Math.sin(t * 0.017) * 0.18);
      }
      if (u.uTime) gl.uniform1f(u.uTime, t);
      if (u.uHalfH) gl.uniform1f(u.uHalfH, height / 2);
      if (u.uCeil) gl.uniform1f(u.uCeil, ROOM.ceil);
      if (u.uFloor) gl.uniform1f(u.uFloor, ROOM.floor);
      if (u.uPool) gl.uniform3f(u.uPool, POOL.x, POOL.z, POOL.r);
      return u;
    }

    function attrib(info, name, size, stride, offset) {
      var loc = info.attr[name];
      if (loc === undefined || loc < 0) return;
      gl.enableVertexAttribArray(loc);
      gl.vertexAttribPointer(loc, size, gl.FLOAT, false, stride, offset);
    }

    function disableAttribs(info) {
      for (var k in info.attr) gl.disableVertexAttribArray(info.attr[k]);
    }

    function frame(now) {
      raf = requestAnimationFrame(frame);
      var dtMs = now - lastFrame;
      lastFrame = now;
      adaptQuality(dtMs);
      draw((now - start) / 1000);
    }

    function draw(t) {
      resize();

      var speed = reduceMotion ? 0.4 : 1;
      var at = t * speed + 40;
      var aspect = manual ? width / height : (canvas.clientWidth || width) / (canvas.clientHeight || height);
      var fade = Math.min(1, t / 2.2);
      var form = reduceMotion ? 1 : Math.min(1, t / 4.2);

      var sinceDrop = dropAt === null ? -1 : t - dropAt;
      var ripple = sinceDrop >= DROP_DURATION ? sinceDrop - DROP_DURATION : -1;
      if (ripple > 6) ripple = -1;
      var glow = (0.85 + 0.15 * Math.sin(at * 0.4)) * (1 + (ripple >= 0 ? 0.9 * Math.exp(-ripple * 1.4) : 0));

      gl.viewport(0, 0, width, height);
      gl.disable(gl.BLEND);

      var u = useProgram(progBg, at, aspect);
      gl.uniform2f(u.uRes, width, height);
      gl.uniform1f(u.uFade, fade);
      gl.bindBuffer(gl.ARRAY_BUFFER, bufTri);
      attrib(progBg, 'aPos', 2, 0, 0);
      gl.drawArrays(gl.TRIANGLES, 0, 3);
      disableAttribs(progBg);

      gl.enable(gl.BLEND);
      gl.blendFunc(gl.ONE, gl.ONE);

      u = useProgram(progPlane, at, aspect);
      gl.uniform1f(u.uFade, fade);
      gl.uniform1f(u.uRipple, ripple);
      gl.uniform1f(u.uGlow, glow * form);
      gl.bindBuffer(gl.ARRAY_BUFFER, bufQuad);
      attrib(progPlane, 'aPos', 2, 0, 0);
      gl.uniform1f(u.uMode, 0);
      gl.uniform3f(u.uCenter, POOL.x, ROOM.floor, POOL.z);
      gl.uniform2f(u.uHalf, 9, 4.5);
      gl.drawArrays(gl.TRIANGLES, 0, 6);
      gl.uniform1f(u.uMode, 1);
      gl.uniform3f(u.uCenter, 0, ROOM.ceil + 0.02, 4.3);
      gl.uniform2f(u.uHalf, 10, 3.2);
      gl.drawArrays(gl.TRIANGLES, 0, 6);
      disableAttribs(progPlane);

      u = useProgram(progBeam, at, aspect);
      gl.uniform1f(u.uBeam, 0.20 * fade);
      gl.bindBuffer(gl.ARRAY_BUFFER, bufBeams);
      attrib(progBeam, 'aCorner', 2, 24, 0);
      attrib(progBeam, 'aBeam', 4, 24, 8);
      gl.drawArrays(gl.TRIANGLES, 0, BEAMS.length * 6);
      disableAttribs(progBeam);

      u = useProgram(progStar, at, aspect);
      gl.uniform1f(u.uRipple, ripple);
      gl.uniform1f(u.uGlow, glow);
      gl.uniform1f(u.uForm, form);
      gl.uniform1f(u.uFade, fade);
      gl.bindBuffer(gl.ARRAY_BUFFER, bufStars);
      attrib(progStar, 'aPos', 3, 28, 0);
      attrib(progStar, 'aData', 4, 28, 12);
      for (var g = 0; g < starGroups.length; g++) {
        gl.drawArrays(gl.POINTS, starGroups[g][0], Math.round(starGroups[g][1] * detail));
      }
      disableAttribs(progStar);

      drawOrb(t, at, aspect, sinceDrop);

      gl.disable(gl.BLEND);
    }

    function drawOrb(t, at, aspect, sinceDrop) {
      var falling = sinceDrop >= 0 && sinceDrop < DROP_DURATION + 0.3;
      var restA = 0;
      if (sinceDrop >= DROP_DURATION) restA = Math.min(1, (sinceDrop - DROP_DURATION) / 0.25);
      if (restFrom !== null) {
        var sinceRest = t - restFrom;
        restA = Math.max(restA, sinceRest >= 0 ? 1 - Math.min(1, sinceRest / 0.5) : 0);
      }
      if (!falling && restA <= 0) return;

      var u = useProgram(progOrb, at, aspect);
      gl.uniform1f(u.uDur, DROP_DURATION);
      gl.uniform3f(u.uFrom, POOL.x, ROOM.ceil + 0.1, POOL.z);
      gl.uniform3f(u.uTo, POOL.x, ROOM.floor + 0.06, POOL.z);
      gl.uniform1f(u.uTrail, ORB_TRAIL);
      gl.bindBuffer(gl.ARRAY_BUFFER, bufOrb);
      attrib(progOrb, 'aIdx', 1, 0, 0);
      if (falling) {
        gl.uniform1f(u.uDropT, sinceDrop);
        gl.uniform1f(u.uRest, 0);
        gl.uniform1f(u.uAlpha, 1);
        gl.drawArrays(gl.POINTS, 0, ORB_TRAIL + 1);
      }
      if (restA > 0) {
        gl.uniform1f(u.uDropT, 1000);
        gl.uniform1f(u.uRest, 1);
        gl.uniform1f(u.uAlpha, restA * (0.55 + 0.12 * Math.sin(at * 2.1)));
        gl.drawArrays(gl.POINTS, 0, 1);
      }
      disableAttribs(progOrb);
    }

    function adaptQuality(dtMs) {
      if (document.hidden || dtMs > 250) return;
      frameTimes.push(dtMs);
      if (frameTimes.length < 90) return;
      var sum = 0;
      for (var i = 0; i < frameTimes.length; i++) sum += frameTimes[i];
      frameTimes.length = 0;
      if (sum / 90 <= 24) return;
      if (detail > 0.5) detail *= 0.7;
      else if (scale > 0.5) scale = Math.max(0.5, scale * 0.8);
    }

    function drop(atTime) {
      var t = typeof atTime === 'number' ? atTime : (performance.now() - start) / 1000;
      if (dropAt !== null && t - dropAt >= DROP_DURATION) restFrom = t;
      dropAt = t;
      return DROP_DURATION * 1000;
    }

    function onContextLost(e) {
      e.preventDefault();
      cancelAnimationFrame(raf);
    }
    canvas.addEventListener('webglcontextlost', onContextLost);

    resize();
    if (!manual) raf = requestAnimationFrame(frame);

    return {
      drop: drop,
      dropDuration: DROP_DURATION * 1000,
      render: draw,
      pause: function () {
        cancelAnimationFrame(raf);
        raf = 0;
      },
      resume: function () {
        if (manual || raf) return;
        lastFrame = performance.now();
        raf = requestAnimationFrame(frame);
      },
      destroy: function () {
        cancelAnimationFrame(raf);
        canvas.removeEventListener('webglcontextlost', onContextLost);
      }
    };
  }

  window.SlideTemplates = window.SlideTemplates || {};
  window.SlideTemplates['sao-roi'] = { createEffect: createEffect };
})();
/* ==========================================================================
   DẤU ẤN THỜI GIAN - LOGIC TƯƠNG TÁC & HOẠT ẢNH
   ========================================================================== */
(function () {
    'use strict';

    var P = 'mayhen_invitation_ladoifji894yfv_';
    var giam = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    var cua = document.querySelector('.' + P + 'sr_cua');
    var nutMo = document.querySelector('.' + P + 'sr_nut_mo');
    var nen = null;
    var hieuUngBat = true;
    var lanRoi = 0;

    /* 1. Khởi tạo nền WebGL Màn Sao Rơi */
    function batNen() {
        if (nen) return;
        var cv = document.querySelector('.' + P + 'sr_nen');
        var mau = window.SlideTemplates && window.SlideTemplates['sao-roi'];
        try {
            nen = mau && cv ? mau.createEffect(cv, { density: window.innerWidth < 600 ? 0.6 : 0.85 }) : null;
        } catch (e) {
            nen = null;
        }
        if (!nen) document.documentElement.classList.add(P + 'sr_khong_gl');
    }

    document.addEventListener('visibilitychange', function () {
        if (!nen) return;
        if (document.hidden) nen.pause();
        else if (hieuUngBat) nen.resume();
    });

    function thaSao() {
        if (!nen || !hieuUngBat) return;
        var bay = Date.now();
        if (bay - lanRoi < 2600) return;
        lanRoi = bay;
        nen.drop();
    }

    /* 2. Hiệu ứng tan thành hạt sáng khi bấm Mở Thiệp */
    function tanHat() {
        var man = document.querySelector('.' + P + 'sr_man');
        var cv = document.querySelector('.' + P + 'sr_hat');
        if (!man || !cv || giam) return;
        var W = window.innerWidth, H = window.innerHeight, ctx;
        cv.width = W;
        cv.height = H;
        try {
            ctx = cv.getContext('2d', { willReadFrequently: true });
        } catch (e) {
            ctx = null;
        }
        if (!ctx) return;
        ctx.clearRect(0, 0, W, H);
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        var sel = '.' + P + 'ladi_headline, .' + P + 'sr_va_cua, .' + P + 'sr_nut_mo, .' + P + 'sr_orn';
        var ds = man.querySelectorAll(sel);
        Array.prototype.forEach.call(ds, function (el) {
            var r = el.getBoundingClientRect();
            if (!r.width || r.bottom < 0 || r.top > H) return;
            var cs = getComputedStyle(el);
            if (el.classList.contains(P + 'sr_orn')) {
                ctx.fillStyle = '#fff';
                ctx.fillRect(r.left, r.top + r.height / 2 - 0.5, r.width, 1.2);
                return;
            }
            var chuoi = (el.innerText || el.textContent || '').trim();
            if (cs.textTransform === 'uppercase') chuoi = chuoi.toUpperCase();
            ctx.font = cs.fontStyle + ' ' + cs.fontWeight + ' ' + cs.fontSize + ' ' + cs.fontFamily;
            try {
                ctx.letterSpacing = cs.letterSpacing === 'normal' ? '0px' : cs.letterSpacing;
            } catch (e) {}
            ctx.fillStyle = '#fff';
            if (el.classList.contains(P + 'sr_nut_mo')) {
                ctx.strokeStyle = '#fff';
                ctx.lineWidth = 1.2;
                var rr = r.height / 2;
                ctx.beginPath();
                ctx.moveTo(r.left + rr, r.top);
                ctx.arcTo(r.right, r.top, r.right, r.bottom, rr);
                ctx.arcTo(r.right, r.bottom, r.left, r.bottom, rr);
                ctx.arcTo(r.left, r.bottom, r.left, r.top, rr);
                ctx.arcTo(r.left, r.top, r.right, r.top, rr);
                ctx.stroke();
            }
            ctx.fillText(chuoi, r.left + r.width / 2, r.top + r.height / 2);
        });

        var d = ctx.getImageData(0, 0, W, H).data, hat = [], buoc = 3;
        for (var y = 0; y < H; y += buoc) {
            for (var x = 0; x < W; x += buoc) {
                if (d[(y * W + x) * 4 + 3] > 120) {
                    hat.push({
                        x: x,
                        y: y,
                        vx: (Math.random() - 0.3) * 0.9,
                        vy: -Math.random() * 1.6 - 0.2,
                        tre: (x / W) * 520 + Math.random() * 260,
                        co: Math.random() * 1.4 + 0.7
                    });
                }
            }
        }
        if (hat.length > 9000) {
            var step = Math.ceil(hat.length / 9000);
            hat = hat.filter(function (_, i) { return i % step === 0; });
        }
        man.classList.add(P + 'tan');
        cv.style.display = 'block';
        var t0 = performance.now(), DAI = 1500;
        (function ve(bay) {
            var t = bay - t0;
            ctx.clearRect(0, 0, W, H);
            ctx.globalCompositeOperation = 'lighter';
            var con = 0;
            for (var i = 0; i < hat.length; i++) {
                var h = hat[i], s = t - h.tre;
                var a = s < 0 ? 1 : 1 - s / DAI;
                if (a <= 0) continue;
                con++;
                var k = Math.max(0, s) / 16;
                var px = h.x + h.vx * k + Math.sin((h.y + k * 3) * 0.05) * Math.min(k, 30) * 0.25;
                var py = h.y + h.vy * k - 0.004 * k * k;
                var shade = (215 + 40 * a) | 0;
                ctx.fillStyle = s < 0 ? 'rgba(255,255,255,.95)' : 'rgba(' + shade + ',' + shade + ',' + shade + ',' + a.toFixed(3) + ')';
                ctx.fillRect(px, py, h.co, h.co);
            }
            ctx.globalCompositeOperation = 'source-over';
            if (con && t < 4000) requestAnimationFrame(ve);
            else {
                ctx.clearRect(0, 0, W, H);
                cv.style.display = 'none';
            }
        })(t0);
    }

    function moCua() {
        if (!cua || cua.classList.contains(P + 'dang_mo')) return;
        tanHat();
        lanRoi = 0;
        thaSao();
        phatNhac();
        cua.classList.add(P + 'dang_mo');
        window.scrollTo(0, 0);
        setTimeout(function () {
            document.body.classList.remove(P + 'sr_khoa');
            vuaMotDong();
        }, giam ? 0 : 1300);
        setTimeout(function () {
            cua.classList.add(P + 'da_mo');
        }, giam ? 50 : (+cua.getAttribute('data-thoi-gian') || 2200));
    }

    /* 3. Hiện dần khi cuộn + thả sao mỗi khi qua phần mới */
    function ganHieuUng() {
        if (giam) return;
        document.querySelectorAll('.' + P + 'sr_the.' + P + 'sr_hien').forEach(function (the) {
            var i = 0;
            the.querySelectorAll('.' + P + 'ClientEditText, .' + P + 'sr_ngay_moi, .' + P + 'bieu_tuong, .' + P + 'edit_RPSV, .' + P + 'edit_sendMessage, .' + P + 'MessageBox').forEach(function (el) {
                if (el.closest('.' + P + 'sr_con') || (el.closest('.' + P + 'sr_ngay_moi') !== null && !el.classList.contains(P + 'sr_ngay_moi'))) return;
                if (el.closest('.' + P + 'sr_the') !== the) return;
                el.classList.add(P + 'sr_con');
                el.style.setProperty('--i', i++);
            });
        });

        var io = 'IntersectionObserver' in window ? new IntersectionObserver(function (es) {
            es.forEach(function (e) {
                if (!e.isIntersecting) return;
                var a = e.target;
                io.unobserve(a);
                setTimeout(function () {
                    a.classList.remove(P + 'sr_anh_cho');
                    a.classList.add(P + 'sr_anh_mo');
                }, 250);
            });
        }, { threshold: 0.25 }) : null;

        if (io) {
            document.querySelectorAll('.' + P + 'ClientEditImage:not(.' + P + 'anh_qr)').forEach(function (a) {
                if (a.closest('.' + P + 'edit_galleryV2')) return;
                a.classList.add(P + 'sr_anh_cho');
                io.observe(a);
            });
        }
    }

    function batHienDan() {
        var ds = document.querySelectorAll('.' + P + 'sr_hien');
        if (!('IntersectionObserver' in window)) {
            ds.forEach(function (el) { el.classList.add(P + 'da_hien'); });
            return;
        }
        var io = new IntersectionObserver(function (es) {
            es.forEach(function (e) {
                if (!e.isIntersecting) return;
                var el = e.target;
                el.style.transitionDelay = ((Array.prototype.indexOf.call(el.parentNode.children, el) % 4) * 0.12) + 's';
                el.classList.add(P + 'da_hien');
                io.unobserve(el);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -6% 0px' });
        ds.forEach(function (el) { io.observe(el); });

        var ioPhan = new IntersectionObserver(function (es) {
            es.forEach(function (e) {
                if (e.isIntersecting && !document.body.classList.contains(P + 'sr_khoa')) thaSao();
            });
        }, { threshold: 0.35 });
        document.querySelectorAll('.' + P + 'sr_phan').forEach(function (s) { ioPhan.observe(s); });
    }

    /* 4. Co chữ tự động vừa 1 dòng */
    function vuaMotDong() {
        document.querySelectorAll('.' + P + 'sr_tieu_de, .' + P + 'sr_doi .' + P + 'ten_ben').forEach(function (o) {
            var h = o.querySelector('.' + P + 'ladi_headline');
            if (!h) return;
            h.style.fontSize = '';
            var goc = parseFloat(getComputedStyle(h).fontSize) || 40;
            var rong = o.clientWidth;
            if (!rong) return;
            var can = h.scrollWidth;
            if (can > rong) {
                h.style.fontSize = Math.max(goc * 0.6, goc * rong / can * 0.98).toFixed(1) + 'px';
            }
        });
    }

    window.addEventListener('resize', function () {
        clearTimeout(vuaMotDong.t);
        vuaMotDong.t = setTimeout(vuaMotDong, 150);
    });
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(vuaMotDong);
    }

    /* 5. Đếm ngược tới ngày cưới (20/12/2026 18:00:00) */
    function batDemNguoc() {
        var target = new Date('2026-12-20T18:00:00+07:00').getTime();
        var elDay = document.querySelector('.' + P + 'cd_day');
        var elHour = document.querySelector('.' + P + 'cd_hour');
        var elMin = document.querySelector('.' + P + 'cd_min');
        var elSec = document.querySelector('.' + P + 'cd_sec');
        if (!elDay || !elHour || !elMin || !elSec) return;

        function capNhat() {
            var now = Date.now();
            var diff = Math.max(0, Math.floor((target - now) / 1000));
            var d = Math.floor(diff / 86400);
            var h = Math.floor((diff % 86400) / 3600);
            var m = Math.floor((diff % 3600) / 60);
            var s = diff % 60;
            elDay.textContent = d;
            elHour.textContent = h;
            elMin.textContent = m;
            elSec.textContent = s;
        }
        capNhat();
        setInterval(capNhat, 1000);
    }

    /* 6. Cổng sao Bản Đồ 3D */
    function cuaBanDo() {
        var c = document.querySelector('.' + P + 'cua_bd');
        if (!c) return;
        var btn = c.querySelector('.' + P + 'nut_mo_bd');
        if (!btn) return;
        btn.addEventListener('click', function () {
            if (c.classList.contains(P + 'mo')) return;
            c.classList.add(P + 'mo');
            thaSao();
            var k = document.querySelector('.' + P + 'sr_khung_bd');
            if (k) k.classList.add(P + 'bd_da_mo');
            setTimeout(function () { c.classList.add(P + 'xong'); }, 2400);
        });
    }

    /* 7. Album Ảnh 3D Coverflow */
    function batAlbumCoverflow() {
        var cf = document.querySelector('.' + P + 'wb_cf');
        if (!cf) return;
        var items = Array.prototype.slice.call(cf.querySelectorAll('.' + P + 'wb_cf_it'));
        var dots = Array.prototype.slice.call(cf.querySelectorAll('.' + P + 'wb_cf_dots button'));
        var btnPrev = cf.querySelector('.' + P + 'wb_cf_nut.' + P + 'truoc');
        var btnNext = cf.querySelector('.' + P + 'wb_cf_nut.' + P + 'sau');
        if (!items.length) return;

        var cur = 7; // Mặc định ảnh số 8 ở giữa giống mẫu gốc
        var n = items.length;
        var timer = null;

        function render(idx) {
            cur = ((idx % n) + n) % n;
            items.forEach(function (it, i) {
                var d = i - cur;
                var abs = Math.abs(d);
                var tx = d * 58;
                var tz = -abs * 120;
                var ry = d === 0 ? 0 : (d < 0 ? 38 : -38);
                var sc = d === 0 ? 1 : 0.84;
                var z = 100 - abs;
                var op = abs <= 3 ? 1 : 0;

                it.style.setProperty('transform', 'translateX(-50%) translateX(' + tx + '%) translateZ(' + tz + 'px) rotateY(' + ry + 'deg) scale(' + sc + ')', 'important');
                it.style.setProperty('z-index', String(z), 'important');
                it.style.setProperty('opacity', String(op), 'important');
                it.style.setProperty('pointer-events', abs <= 3 ? 'auto' : 'none', 'important');
                it.classList.toggle(P + 'wb_cf_ben', d !== 0);
            });
            dots.forEach(function (dot, i) {
                dot.classList.toggle(P + 'on', i === cur);
            });
        }

        function startAuto() {
            stopAuto();
            timer = setInterval(function () {
                render(cur + 1);
            }, 3500);
        }

        function stopAuto() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        if (btnPrev) btnPrev.addEventListener('click', function (e) {
            e.stopPropagation();
            render(cur - 1);
            startAuto();
        });
        if (btnNext) btnNext.addEventListener('click', function (e) {
            e.stopPropagation();
            render(cur + 1);
            startAuto();
        });
        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function (e) {
                e.stopPropagation();
                render(i);
                startAuto();
            });
        });

        var startX = null;
        cf.addEventListener('pointerdown', function (e) {
            startX = e.clientX;
            stopAuto();
        });
        cf.addEventListener('pointerup', function (e) {
            if (startX === null) return;
            var dx = e.clientX - startX;
            startX = null;
            if (Math.abs(dx) > 36) {
                render(cur + (dx < 0 ? 1 : -1));
            }
            startAuto();
        });

        render(cur);
        startAuto();
    }

    /* 8. Hộp Quà Mừng Cưới 3D */
    function hopQua() {
        var hop = document.querySelector('.' + P + 'sr_hop_qua');
        var q = document.querySelector('.' + P + 'sr_qr');
        var lop = document.querySelector('.' + P + 'sr_hop_lop');
        if (!hop || !q || !lop) return;

        hop.querySelectorAll('.' + P + 'hq_tia i').forEach(function (i, k) {
            var g = (k / 22) * Math.PI * 2 + Math.random() * 0.3;
            var r = 90 + Math.random() * 90;
            i.style.setProperty('--x', (Math.cos(g) * r).toFixed(0) + 'px');
            i.style.setProperty('--y', (Math.sin(g) * r * 0.8 - 50).toFixed(0) + 'px');
        });

        function mo() {
            if (hop.classList.contains(P + 'rung') || hop.classList.contains(P + 'mo')) return;
            hop.classList.add(P + 'rung');
            setTimeout(function () {
                hop.classList.add(P + 'mo');
                thaSao();
                var hr = hop.getBoundingClientRect();
                var hx = hr.left + hr.width / 2;
                var hy = hr.top + hr.height * 0.55;
                q.querySelectorAll('.' + P + 'sr_the_qr').forEach(function (t) {
                    var r = t.getBoundingClientRect();
                    t.style.setProperty('--tu-x', (hx - (r.left + r.width / 2)).toFixed(0) + 'px');
                    t.style.setProperty('--tu-y', (hy - (r.top + r.height / 2)).toFixed(0) + 'px');
                });
                setTimeout(function () {
                    q.classList.add(P + 'mo');
                    lop.classList.add(P + 'tat');
                }, 420);
                setTimeout(function () {
                    lop.classList.add(P + 'xong');
                }, 1700);
            }, giam ? 0 : 480);
        }

        hop.addEventListener('click', mo);
        hop.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                mo();
            }
        });
    }

    /* 9. Ký Tên Chúc Phúc (Vẽ chữ ký Canvas + Kéo thả vị trí + Lưu) */
    function batKyTen() {
        var sigWrap = document.querySelector('.' + P + 'edit_signature');
        var btnOpen = document.querySelector('.' + P + 'wb_sig_open_btn');
        var btnRow = document.querySelector('.' + P + 'wb_sig_btn_row');
        var posBtns = document.querySelector('.' + P + 'wb_sig_pos_btns');
        var btnBack = document.querySelector('.' + P + 'wb_sig_btn_back');
        var btnSave = document.querySelector('.' + P + 'wb_sig_btn_save');
        var pad = document.querySelector('.' + P + 'wb_sig_pad');
        var canvas = document.querySelector('.' + P + 'wb_sig_canvas');
        var btnClear = document.querySelector('.' + P + 'wb_sig_btn_clear');
        var btnPos = document.querySelector('.' + P + 'wb_sig_btn_pos');
        var btnCancel = document.querySelector('.' + P + 'wb_sig_btn_cancel');
        var displayInner = document.querySelector('.' + P + 'wb_sig_display_inner');
        if (!canvas || !pad || !btnOpen || !displayInner) return;

        var ctx = canvas.getContext('2d');
        var drawing = false;
        var hasDrawn = false;
        var tempSigEl = null;

        function resizeCanvas() {
            var r = canvas.getBoundingClientRect();
            if (!r.width) return;
            var dpr = window.devicePixelRatio || 1;
            canvas.width = r.width * dpr;
            canvas.height = r.height * dpr;
            ctx.scale(dpr, dpr);
            ctx.strokeStyle = '#FFFFFF';
            ctx.lineWidth = 2.6;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }

        function pos(e) {
            var r = canvas.getBoundingClientRect();
            var t = e.touches && e.touches[0] ? e.touches[0] : e;
            return { x: t.clientX - r.left, y: t.clientY - r.top };
        }

        canvas.addEventListener('pointerdown', function (e) {
            drawing = true;
            hasDrawn = true;
            var p = pos(e);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
        });
        canvas.addEventListener('pointermove', function (e) {
            if (!drawing) return;
            var p = pos(e);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
        });
        window.addEventListener('pointerup', function () {
            drawing = false;
        });

        btnOpen.addEventListener('click', function () {
            pad.classList.add(P + 'open');
            btnRow.style.display = 'none';
            hasDrawn = false;
            setTimeout(resizeCanvas, 30);
        });

        btnClear.addEventListener('click', function () {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasDrawn = false;
        });

        btnCancel.addEventListener('click', function () {
            pad.classList.remove(P + 'open');
            btnRow.style.display = 'flex';
        });

        btnPos.addEventListener('click', function () {
            if (!hasDrawn) {
                hienPopup('Chưa có chữ ký', 'Bạn hãy vẽ chữ ký lên khung trước khi chỉnh vị trí nhé!', 'Đã hiểu ✦');
                return;
            }
            var dataUrl = canvas.toDataURL('image/png');
            pad.classList.remove(P + 'open');
            posBtns.style.display = 'flex';

            if (tempSigEl) tempSigEl.remove();
            tempSigEl = document.createElement('div');
            tempSigEl.className = P + 'wb_sig_item ' + P + 'dragging';
            tempSigEl.style.cssText = 'width: 21%; left: 39%; top: 38%; opacity: 1; filter: drop-shadow(rgba(0,0,0,0.4) 0px 2px 8px);';
            tempSigEl.innerHTML = '<img src="' + dataUrl + '" alt="Chữ ký của bạn">';
            displayInner.appendChild(tempSigEl);

            makeDraggable(tempSigEl, displayInner);
        });

        btnBack.addEventListener('click', function () {
            if (tempSigEl) {
                tempSigEl.remove();
                tempSigEl = null;
            }
            posBtns.style.display = 'none';
            pad.classList.add(P + 'open');
            setTimeout(resizeCanvas, 30);
        });

        btnSave.addEventListener('click', function () {
            if (tempSigEl) {
                tempSigEl.classList.remove(P + 'dragging');
                tempSigEl.classList.add(P + 'wb_sig_idle_pop');
                tempSigEl = null;
            }
            posBtns.style.display = 'none';
            btnRow.style.display = 'flex';
            thaSao();
            hienPopup('Đã lưu chữ ký', 'Cảm ơn bạn đã để lại dấu ấn kỷ niệm cùng chúng mình!', 'Tuyệt vời ✦');
        });

        function makeDraggable(el, container) {
            var sx = 0, sy = 0, sl = 0, st = 0, draggingEl = false;
            el.addEventListener('pointerdown', function (e) {
                if (!el.classList.contains(P + 'dragging')) return;
                e.preventDefault();
                draggingEl = true;
                sx = e.clientX;
                sy = e.clientY;
                sl = parseFloat(el.style.left) || 39;
                st = parseFloat(el.style.top) || 38;
                el.setPointerCapture(e.pointerId);
            });
            el.addEventListener('pointermove', function (e) {
                if (!draggingEl) return;
                var cr = container.getBoundingClientRect();
                var dx = ((e.clientX - sx) / cr.width) * 100;
                var dy = ((e.clientY - sy) / cr.height) * 100;
                el.style.left = Math.max(2, Math.min(76, sl + dx)).toFixed(1) + '%';
                el.style.top = Math.max(2, Math.min(84, st + dy)).toFixed(1) + '%';
            });
            el.addEventListener('pointerup', function () {
                draggingEl = false;
            });
        }
    }

    /* 10. Form Xác Nhận Tham Dự (RSVP) & Gửi Lời Chúc */
    function batForms() {
        document.querySelectorAll('.' + P + 'wb_opt_group').forEach(function (grp) {
            grp.addEventListener('click', function (e) {
                var lbl = e.target.closest('.' + P + 'wb_opt_label');
                if (!lbl) return;
                grp.querySelectorAll('.' + P + 'wb_opt_label').forEach(function (l) {
                    l.classList.remove(P + 'active');
                });
                lbl.classList.add(P + 'active');
                if (grp.getAttribute('data-name') === 'attendance') {
                    var cntField = document.querySelector('.' + P + 'wb_guest_count_field');
                    if (cntField) {
                        cntField.style.display = lbl.getAttribute('data-val') === 'yes' ? 'block' : 'none';
                    }
                }
            });
        });

        var decBtn = document.querySelector('.' + P + 'wb_dec');
        var incBtn = document.querySelector('.' + P + 'wb_inc');
        var cntVal = document.querySelector('.' + P + 'wb_counter_val');
        if (decBtn && incBtn && cntVal) {
            decBtn.addEventListener('click', function () {
                var v = parseInt(cntVal.value, 10) || 1;
                cntVal.value = Math.max(1, v - 1);
            });
            incBtn.addEventListener('click', function () {
                var v = parseInt(cntVal.value, 10) || 1;
                cntVal.value = Math.min(20, v + 1);
            });
        }

        var rsvpForm = document.querySelector('.' + P + 'wb_rsvp_form');
        if (rsvpForm) {
            var rsvpBtn = rsvpForm.querySelector('.' + P + 'wb_submit_btn');
            var rsvpFb = rsvpForm.querySelector('.' + P + 'wb_feedback');
            rsvpBtn.addEventListener('click', function () {
                var nameInput = rsvpForm.querySelector('input[name="name"]');
                var name = nameInput ? nameInput.value.trim() : '';
                if (!name) {
                    if (rsvpFb) {
                        rsvpFb.textContent = 'Vui lòng nhập tên của bạn.';
                        rsvpFb.className = P + 'wb_feedback ' + P + 'err';
                    }
                    return;
                }
                if (rsvpFb) rsvpFb.textContent = '';

                var optGuest = rsvpForm.querySelector('.' + P + 'wb_opt_group[data-name="guestOf"] .' + P + 'active');
                var guestOf = optGuest ? optGuest.getAttribute('data-val') : 'bride';
                var optAttend = rsvpForm.querySelector('.' + P + 'wb_opt_group[data-name="attendance"] .' + P + 'active');
                var isAttending = optAttend ? (optAttend.getAttribute('data-val') === 'yes') : true;
                var guestCountInput = rsvpForm.querySelector('.' + P + 'wb_counter_val');
                var guestCount = guestCountInput ? (parseInt(guestCountInput.value, 10) || 1) : 1;

                // Gửi AJAX lên hệ thống Mây Hẹn nếu có URL
                if (window.__MAYHEN_RSVP_SUBMIT_URL) {
                    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    fetch(window.__MAYHEN_RSVP_SUBMIT_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            guest_name: name,
                            guest_of: guestOf,
                            is_attending: isAttending,
                            guest_count: isAttending ? guestCount : 0
                        })
                    }).catch(function () {});
                }

                nameInput.value = '';
                thaSao();
                hienPopup('Đã nhận lời hẹn', 'Cảm ơn Quý khách đã xác nhận.<br>Hẹn gặp nhau dưới màn sao ngày vui!', 'Hẹn gặp bạn ✦');
            });
        }

        var msgForm = document.querySelector('.' + P + 'wb_msg_form');
        var msgBox = document.querySelector('.' + P + 'MessageBox');
        if (msgForm) {
            var ta = msgForm.querySelector('textarea[name="message"]');
            var charNum = msgForm.querySelector('.' + P + 'wb_char_num');
            if (ta && charNum) {
                ta.addEventListener('input', function () {
                    charNum.textContent = ta.value.length;
                });
            }
            var msgBtn = msgForm.querySelector('.' + P + 'wb_submit_btn');
            var msgFb = msgForm.querySelector('.' + P + 'wb_feedback');
            msgBtn.addEventListener('click', function () {
                var nameInput = msgForm.querySelector('input[name="name"]');
                var name = nameInput ? nameInput.value.trim() : '';
                var msg = ta ? ta.value.trim() : '';
                if (!name || !msg) {
                    if (msgFb) {
                        msgFb.textContent = 'Vui lòng nhập đầy đủ tên và lời chúc của bạn.';
                        msgFb.className = P + 'wb_feedback ' + P + 'err';
                    }
                    return;
                }
                if (msgFb) msgFb.textContent = '';

                // Gửi AJAX Lời chúc lên hệ thống Mây Hẹn
                if (window.__MAYHEN_WISH_SUBMIT_URL) {
                    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    fetch(window.__MAYHEN_WISH_SUBMIT_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            guest_name: name,
                            message: msg
                        })
                    }).catch(function () {});
                }

                if (msgBox) {
                    var item = document.createElement('div');
                    item.className = P + 'MessageBox_item ' + P + 'wb_msg_enter';
                    var h2 = document.createElement('h2');
                    h2.className = P + 'MessageBox_item_name';
                    h2.textContent = name;
                    var p = document.createElement('p');
                    p.className = P + 'MessageBox_item_message';
                    p.textContent = msg;
                    item.appendChild(h2);
                    item.appendChild(p);
                    msgBox.insertBefore(item, msgBox.firstChild);
                }
                nameInput.value = '';
                ta.value = '';
                if (charNum) charNum.textContent = '0';
                thaSao();
                hienPopup('Lời chúc đã gửi', 'Như một vì sao băng, lời chúc của bạn<br>đã bay tới cô dâu, chú rể.', 'Cảm ơn bạn ✦');
            });
        }
    }

    /* 11. Hộp Thông Báo Sao Băng (Popup) */
    function hienPopup(title, descHtml, btnLabel) {
        var ov = document.querySelector('.' + P + 'wb_popup_overlay');
        if (!ov) return;
        var tEl = ov.querySelector('.' + P + 'wb_popup_title');
        var dEl = ov.querySelector('.' + P + 'wb_popup_desc');
        var bEl = ov.querySelector('.' + P + 'wb_popup_btn');
        if (tEl) tEl.textContent = title;
        if (dEl) dEl.innerHTML = descHtml;
        if (bEl) bEl.textContent = btnLabel || 'Đóng ✦';
        ov.classList.add(P + 'show');
    }

    function batPopup() {
        var ov = document.querySelector('.' + P + 'wb_popup_overlay');
        if (!ov) return;
        var bEl = ov.querySelector('.' + P + 'wb_popup_btn');
        if (bEl) {
            bEl.addEventListener('click', function () {
                ov.classList.remove(P + 'show');
            });
        }
        ov.addEventListener('click', function (e) {
            if (e.target === ov) ov.classList.remove(P + 'show');
        });
    }

    /* 12. Trình Xem Ảnh & Phóng To QR (Lightbox) */
    var xem, xemAnh, xemDs, xemSo, xemChu, dsAnh = [], viTri = 0;

    function urlNen(el) {
        var bg = el && el.querySelector('.' + P + 'ladi_image_background');
        if (!bg) return '';
        var m = /url\(["']?([^"')]+)["']?\)/.exec(getComputedStyle(bg).backgroundImage || '');
        if (!m) return '';
        return m[1];
    }

    function dungXem() {
        if (xem) return;
        xem = document.querySelector('.' + P + 'tmx_xem');
        if (!xem) return;
        xemAnh = xem.querySelector('img');
        xemDs = xem.querySelector('.' + P + 'tmx_ds');
        xemSo = xem.querySelector('.' + P + 'tmx_so');
        xemChu = xem.querySelector('.' + P + 'tmx_chu_qr');
        xem.querySelector('.' + P + 'tmx_dong').onclick = dongXem;
        xem.querySelector('.' + P + 'tmx_mui.' + P + 'trai').onclick = function () { den(viTri - 1); };
        xem.querySelector('.' + P + 'tmx_mui.' + P + 'phai').onclick = function () { den(viTri + 1); };
        xem.addEventListener('click', function (e) {
            if (e.target === xem || e.target.classList.contains(P + 'tmx_khung')) dongXem();
        });
        document.addEventListener('keydown', function (e) {
            if (!xem.classList.contains(P + 'mo')) return;
            if (e.key === 'Escape') dongXem();
            if (e.key === 'ArrowLeft') den(viTri - 1);
            if (e.key === 'ArrowRight') den(viTri + 1);
        });
    }

    function den(i) {
        if (!dsAnh.length) return;
        viTri = (i + dsAnh.length) % dsAnh.length;
        xemAnh.style.opacity = '0';
        setTimeout(function () {
            xemAnh.src = dsAnh[viTri];
            xemAnh.style.opacity = '1';
        }, 120);
        xemSo.textContent = (viTri + 1) + ' / ' + dsAnh.length;
        Array.prototype.forEach.call(xemDs.children, function (b, k) {
            b.classList.toggle(P + 'dang', k === viTri);
        });
    }

    function moXem(list, i, chuQr) {
        dungXem();
        if (!xem) return;
        dsAnh = list;
        xem.classList.toggle(P + 'chi_mot', list.length < 2);
        xem.classList.toggle(P + 'la_qr', !!chuQr);
        xemChu.textContent = chuQr || '';
        xemChu.style.display = chuQr ? 'block' : 'none';
        xemDs.innerHTML = '';
        list.forEach(function (u, k) {
            var b = document.createElement('button');
            b.type = 'button';
            b.style.backgroundImage = 'url("' + u + '")';
            b.onclick = function () { den(k); };
            xemDs.appendChild(b);
        });
        xem.classList.add(P + 'mo');
        document.documentElement.style.overflow = 'hidden';
        den(i);
    }

    function dongXem() {
        if (!xem) return;
        xem.classList.remove(P + 'mo');
        document.documentElement.style.overflow = '';
    }

    function batXemAnh() {
        var px = 0, py = 0;
        document.addEventListener('pointerdown', function (e) {
            px = e.clientX;
            py = e.clientY;
        }, true);

        document.addEventListener('click', function (e) {
            if (Math.abs(e.clientX - px) > 8 || Math.abs(e.clientY - py) > 8) return;
            var el = e.target.closest('.' + P + 'wb_cf_it, .' + P + 'anh_qr');
            if (!el) return;
            var u = urlNen(el);
            if (!u) return;
            var qrCard = el.closest('.' + P + 'tmx_qr');
            if (qrCard) {
                var vai = qrCard.querySelector('.' + P + 'tmx_vai_qr');
                var tk = qrCard.querySelector('.' + P + 'tmx_chu_tk');
                var chu = ((vai ? vai.innerText.trim() + '\n' : '') + (tk ? tk.innerText.trim() : '')).trim();
                moXem([u], 0, chu || ' ');
                return;
            }
            var albumUrls = [];
            document.querySelectorAll('.' + P + 'wb_cf_it').forEach(function (it) {
                var iu = urlNen(it);
                if (iu && albumUrls.indexOf(iu) < 0) albumUrls.push(iu);
            });
            var idx = albumUrls.indexOf(u);
            moXem(albumUrls.length ? albumUrls : [u], Math.max(0, idx));
        });
    }

    /* 13. Phát Nhạc Nền */
    var audio = document.querySelector('.' + P + 'audio_music');
    var musicBtn = document.querySelector('.' + P + 'wb_music_btn');

    function phatNhac() {
        if (!audio) {
            audio = document.querySelector('.' + P + 'audio_music');
        }
        if (!audio) return;
        audio.play().then(function () {
            if (musicBtn) musicBtn.classList.add(P + 'playing');
        }).catch(function () {});
    }

    // Live Sync: Đổi bài hát nền thời gian thực từ Studio Editor
    window.updateMayhenMusic = function(newUrl) {
        if (!newUrl) return;
        if (!audio) {
            audio = document.querySelector('.' + P + 'audio_music');
        }
        if (audio) {
            var wasPlaying = !audio.paused;
            audio.pause();
            audio.src = newUrl;
            audio.load();
            if (wasPlaying || window.__MAYHEN_IS_EMBED) {
                phatNhac();
            }
        }
    };

    if (musicBtn) {
        musicBtn.addEventListener('click', function () {
            if (!audio) {
                audio = document.querySelector('.' + P + 'audio_music');
            }
            if (audio) {
                if (audio.paused) {
                    phatNhac();
                } else {
                    audio.pause();
                    musicBtn.classList.remove(P + 'playing');
                }
            }
        });
    }

    /* Khởi chạy toàn bộ khi DOM sẵn sàng */
    if (nutMo) nutMo.addEventListener('click', moCua);

    // Phòng vệ nhúng Studio Editor
    function kiemTraEmbed() {
        if (window.__MAYHEN_IS_EMBED) {
            document.body.classList.remove(P + 'sr_khoa');
            document.documentElement.classList.add(P + 'embed_mode');
            document.body.classList.add(P + 'embed_mode');
            if (cua) {
                cua.style.display = 'none';
                cua.classList.add(P + 'da_mo');
            }
            // Kích hoạt hiển thị toàn bộ phần tử
            document.querySelectorAll('.' + P + 'sr_hien').forEach(function (el) {
                el.style.opacity = '1';
                el.style.transform = 'none';
                el.style.visibility = 'visible';
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        kiemTraEmbed();
        batNen();
        ganHieuUng();
        batHienDan();
        vuaMotDong();
        batDemNguoc();
        cuaBanDo();
        batAlbumCoverflow();
        hopQua();
        batKyTen();
        batForms();
        batPopup();
        batXemAnh();
    });

    window.addEventListener('load', function () {
        setTimeout(vuaMotDong, 400);
    });
})();
