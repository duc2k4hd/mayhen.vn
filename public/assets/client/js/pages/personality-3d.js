/**
 * Mây Hẹn – The Art of Personality: Interactive 3D Museum Sculpture Engine (Vanilla Canvas 3D)
 * - Museum-grade Obsidian & Gold Spotlight rendering (60fps, zero external CDN).
 * - Supports 360° drag-to-orbit, zoom, reset, AND live switching between all 16 personality sculptures on one stage.
 */

const clamp = (v, min, max) => Math.max(min, Math.min(max, v));

const rotatePoint = (p, rx, ry) => {
    const cosY = Math.cos(ry);
    const sinY = Math.sin(ry);
    const x1 = p[0] * cosY - p[2] * sinY;
    const z1 = p[0] * sinY + p[2] * cosY;

    const cosX = Math.cos(rx);
    const sinX = Math.sin(rx);
    const y2 = p[1] * cosX - z1 * sinX;
    const z2 = p[1] * sinX + z1 * cosX;

    return [x1, y2, z2];
};

const createPrismGeometry = (sides, radiusTop, radiusBot, height, yOffset = 0, color = '#1E4D3B') => {
    const verts = [];
    const faces = [];
    const halfH = height / 2;

    for (let i = 0; i < sides; i++) {
        const a = (i / sides) * Math.PI * 2;
        verts.push([Math.cos(a) * radiusTop, -halfH + yOffset, Math.sin(a) * radiusTop]);
        verts.push([Math.cos(a) * radiusBot, halfH + yOffset, Math.sin(a) * radiusBot]);
    }
    const topCenterIdx = verts.length;
    verts.push([0, -halfH + yOffset, 0]);
    const botCenterIdx = verts.length;
    verts.push([0, halfH + yOffset, 0]);

    for (let i = 0; i < sides; i++) {
        const next = (i + 1) % sides;
        const t1 = i * 2;
        const b1 = i * 2 + 1;
        const t2 = next * 2;
        const b2 = next * 2 + 1;
        faces.push({ idx: [t1, t2, b2, b1], color });
        if (radiusTop > 0.01) faces.push({ idx: [topCenterIdx, t2, t1], color });
        if (radiusBot > 0.01) faces.push({ idx: [botCenterIdx, b1, b2], color });
    }
    return { verts, faces };
};

const createRingGeometry = (radius, tube, segs = 22, tiltX = 0, tiltZ = 0, color = '#CFA860') => {
    const verts = [];
    const faces = [];
    const ringSegs = 6;

    for (let i = 0; i < segs; i++) {
        const theta = (i / segs) * Math.PI * 2;
        const cosT = Math.cos(theta);
        const sinT = Math.sin(theta);

        for (let j = 0; j < ringSegs; j++) {
            const phi = (j / ringSegs) * Math.PI * 2;
            const r = radius + tube * Math.cos(phi);
            const x = r * cosT;
            const y = tube * Math.sin(phi);
            const z = r * sinT;

            const y1 = y * Math.cos(tiltX) - z * Math.sin(tiltX);
            const z1 = y * Math.sin(tiltX) + z * Math.cos(tiltX);
            const x2 = x * Math.cos(tiltZ) - y1 * Math.sin(tiltZ);
            const y2 = x * Math.sin(tiltZ) + y1 * Math.cos(tiltZ);
            verts.push([x2, y2, z1]);
        }
    }

    for (let i = 0; i < segs; i++) {
        const nextI = (i + 1) % segs;
        for (let j = 0; j < ringSegs; j++) {
            const nextJ = (j + 1) % ringSegs;
            faces.push({
                idx: [i * ringSegs + j, nextI * ringSegs + j, nextI * ringSegs + nextJ, i * ringSegs + nextJ],
                color,
            });
        }
    }
    return { verts, faces };
};

const mergeMeshes = (meshes) => {
    const verts = [];
    const faces = [];
    for (const m of meshes) {
        const offset = verts.length;
        for (const v of m.verts) verts.push(v);
        for (const f of m.faces) {
            faces.push({ idx: f.idx.map((i) => i + offset), color: f.color });
        }
    }
    return { verts, faces };
};

const buildSculptureMesh = (key, primary, accent) => {
    const pedestal = createPrismGeometry(16, 0.92, 1.02, 0.14, 1.08, '#3B3530');
    const goldRim = createRingGeometry(0.95, 0.025, 24, 0, 0, accent);

    const core = (() => {
        switch (key) {
            case 'architect_monolith':
                return mergeMeshes([
                    createPrismGeometry(4, 0.42, 0.5, 1.75, 0.08, primary),
                    createPrismGeometry(4, 0.25, 0.25, 1.15, -0.22, '#E7DEC8'),
                    createRingGeometry(0.96, 0.045, 24, 0.92, 0.35, accent),
                ]);
            case 'prism_crystal':
                return mergeMeshes([
                    createPrismGeometry(3, 0.02, 0.82, 1.25, -0.25, primary),
                    createPrismGeometry(3, 0.82, 0.02, 0.82, 0.78, accent),
                    createRingGeometry(1.04, 0.038, 24, 1.18, -0.32, '#E7DEC8'),
                ]);
            case 'brass_compass':
                return mergeMeshes([
                    createRingGeometry(0.94, 0.075, 26, 1.57, 0, primary),
                    createRingGeometry(0.72, 0.045, 22, 1.57, 0, accent),
                    createPrismGeometry(4, 0.02, 0.24, 0.92, -0.42, accent),
                    createPrismGeometry(4, 0.24, 0.02, 0.92, 0.5, '#E7DEC8'),
                ]);
            case 'kinetic_sculpture':
                return mergeMeshes([
                    createRingGeometry(1.02, 0.055, 26, 0.65, 0.32, primary),
                    createRingGeometry(0.76, 0.055, 24, -0.82, 0.92, accent),
                    createPrismGeometry(8, 0.34, 0.34, 0.46, 0, '#E7DEC8'),
                ]);
            case 'lotus_halo':
                return mergeMeshes([
                    createPrismGeometry(6, 0.05, 0.66, 1.08, -0.1, primary),
                    createPrismGeometry(6, 0.8, 0.38, 0.46, 0.5, accent),
                    createRingGeometry(1.06, 0.038, 26, 0.28, 0.14, '#E7DEC8'),
                ]);
            case 'book_petals':
                return mergeMeshes([
                    createPrismGeometry(4, 0.86, 0.92, 0.24, 0.68, primary),
                    createPrismGeometry(5, 0.04, 0.58, 0.96, -0.02, accent),
                    createRingGeometry(0.9, 0.038, 24, 1.2, 0.48, '#E7DEC8'),
                ]);
            case 'interlocking_rings':
                return mergeMeshes([
                    createRingGeometry(0.84, 0.075, 26, 0.62, 0.62, primary),
                    createRingGeometry(0.84, 0.075, 26, -0.62, -0.62, accent),
                    createRingGeometry(0.44, 0.05, 20, 1.57, 0, '#E7DEC8'),
                ]);
            case 'blooming_petals':
                return mergeMeshes([
                    createPrismGeometry(8, 0.74, 0.26, 0.84, 0.12, primary),
                    createPrismGeometry(8, 0.3, 0.04, 0.64, -0.42, accent),
                    createRingGeometry(0.98, 0.042, 24, 0.35, 0.22, '#E7DEC8'),
                ]);
            case 'stone_chronometer':
                return mergeMeshes([
                    createPrismGeometry(12, 0.68, 0.68, 0.44, 0.02, primary),
                    createRingGeometry(0.88, 0.062, 26, 1.57, 0, accent),
                    createPrismGeometry(4, 0.16, 0.16, 1.18, 0.02, '#E7DEC8'),
                ]);
            case 'keepsake_vessel':
                return mergeMeshes([
                    createPrismGeometry(8, 0.74, 0.62, 0.9, 0.22, primary),
                    createPrismGeometry(8, 0.28, 0.78, 0.44, -0.45, accent),
                    createRingGeometry(0.86, 0.042, 24, 0.18, 0, '#E7DEC8'),
                ]);
            case 'classical_arch':
                return mergeMeshes([
                    createPrismGeometry(6, 0.46, 0.54, 1.5, 0.04, primary),
                    createPrismGeometry(6, 0.78, 0.78, 0.2, -0.78, accent),
                    createPrismGeometry(6, 0.82, 0.82, 0.2, 0.82, '#E7DEC8'),
                ]);
            case 'botanical_wreath':
                return mergeMeshes([
                    createRingGeometry(0.88, 0.115, 26, 1.57, 0, primary),
                    createRingGeometry(0.68, 0.052, 22, 1.57, 0.32, accent),
                ]);
            case 'precision_gear':
                return mergeMeshes([
                    createPrismGeometry(10, 0.76, 0.76, 0.36, 0.04, primary),
                    createRingGeometry(0.96, 0.06, 22, 0.5, 0, accent),
                    createPrismGeometry(6, 0.28, 0.28, 0.92, 0.04, '#E7DEC8'),
                ]);
            case 'silk_ribbon':
                return mergeMeshes([
                    createRingGeometry(0.94, 0.068, 28, 0.95, 0.75, primary),
                    createRingGeometry(0.66, 0.052, 24, -0.75, 0.45, accent),
                ]);
            case 'dynamic_arc':
                return mergeMeshes([
                    createPrismGeometry(3, 0.04, 0.56, 1.6, 0.02, primary),
                    createRingGeometry(0.96, 0.058, 24, 1.1, -0.65, accent),
                ]);
            default:
                return mergeMeshes([
                    createPrismGeometry(8, 0.04, 0.64, 0.98, -0.46, primary),
                    createPrismGeometry(8, 0.64, 0.04, 0.98, 0.52, accent),
                    createRingGeometry(1.02, 0.045, 24, 0.5, 0.5, '#E7DEC8'),
                ]);
        }
    })();

    return mergeMeshes([pedestal, goldRim, core]);
};

const hexToRgb = (hex) => {
    const clean = (hex || '#1E4D3B').replace('#', '');
    const num = parseInt(clean.length === 3 ? clean.split('').map((c) => c + c).join('') : clean, 16);
    return [(num >> 16) & 255, (num >> 8) & 255, num & 255];
};

const shadeColor = (hex, intensity, spec = 0) => {
    const [r, g, b] = hexToRgb(hex);
    const k = clamp(intensity, 0.26, 1.3);
    const s = Math.round(clamp(spec, 0, 1) * 58);
    return `rgb(${Math.round(clamp(r * k + s, 0, 255))},${Math.round(clamp(g * k + s, 0, 255))},${Math.round(clamp(b * k + s, 0, 255))})`;
};

export const initPersonality3DStages = () => {
    const stages = Array.from(document.querySelectorAll('[data-sculpture-3d]'));
    if (stages.length === 0) return;

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    stages.forEach((stage) => {
        const canvas = stage.querySelector('canvas');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        let mesh = buildSculptureMesh(
            stage.dataset.sculptureKey || 'architect_monolith',
            stage.dataset.primary || '#1E4D3B',
            stage.dataset.accent || '#CFA860'
        );

        let rx = 0.36;
        let ry = 0.55;
        let zoom = 1.02;
        let dragging = false;
        let lastX = 0;
        let lastY = 0;
        let active = true;
        let rafId = 0;

        const L = [0.44, -0.68, -0.58];
        const lLen = Math.hypot(L[0], L[1], L[2]);
        L[0] /= lLen; L[1] /= lLen; L[2] /= lLen;

        const render = () => {
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            const rect = canvas.getBoundingClientRect();
            const w = Math.max(180, Math.round(rect.width || 280));
            const h = Math.max(180, Math.round(rect.height || 260));
            if (canvas.width !== w * dpr || canvas.height !== h * dpr) {
                canvas.width = w * dpr;
                canvas.height = h * dpr;
            }

            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            ctx.clearRect(0, 0, w, h);

            // Ambient museum spotlight glow
            const spot = ctx.createRadialGradient(w * 0.5, h * 0.38, 12, w * 0.5, h * 0.48, w * 0.58);
            spot.addColorStop(0, 'rgba(207, 168, 96, 0.22)');
            spot.addColorStop(0.55, 'rgba(207, 168, 96, 0.06)');
            spot.addColorStop(1, 'rgba(28, 25, 23, 0)');
            ctx.fillStyle = spot;
            ctx.fillRect(0, 0, w, h);

            const transformed = mesh.verts.map((v) => rotatePoint(v, rx, ry));
            const scale = Math.min(w, h) * 0.33 * zoom;
            const camDist = 4.35;

            const projected = transformed.map(([x, y, z]) => {
                const factor = camDist / (camDist + z);
                return [w / 2 + x * scale * factor, h / 2 + y * scale * factor, z];
            });

            const drawable = [];
            for (const f of mesh.faces) {
                const p0 = transformed[f.idx[0]];
                const p1 = transformed[f.idx[1]];
                const p2 = transformed[f.idx[2]];

                const ux = p1[0] - p0[0], uy = p1[1] - p0[1], uz = p1[2] - p0[2];
                const vx = p2[0] - p0[0], vy = p2[1] - p0[1], vz = p2[2] - p0[2];
                const nx = uy * vz - uz * vy;
                const ny = uz * vx - ux * vz;
                const nz = ux * vy - uy * vx;
                const nLen = Math.hypot(nx, ny, nz) || 1;
                const dot = (nx / nLen) * L[0] + (ny / nLen) * L[1] + (nz / nLen) * L[2];
                const spec = Math.pow(Math.max(0, dot), 6);

                let avgZ = 0;
                for (const i of f.idx) avgZ += transformed[i][2];
                avgZ /= f.idx.length;

                drawable.push({
                    pts: f.idx.map((i) => projected[i]),
                    avgZ,
                    fill: shadeColor(f.color, 0.45 + Math.max(0, dot) * 0.78, spec),
                    stroke: shadeColor(f.color, 0.32),
                });
            }

            drawable.sort((a, b) => b.avgZ - a.avgZ);

            for (const d of drawable) {
                ctx.beginPath();
                ctx.moveTo(d.pts[0][0], d.pts[0][1]);
                for (let i = 1; i < d.pts.length; i++) {
                    ctx.lineTo(d.pts[i][0], d.pts[i][1]);
                }
                ctx.closePath();
                ctx.fillStyle = d.fill;
                ctx.fill();
                ctx.strokeStyle = d.stroke;
                ctx.lineWidth = 0.55;
                ctx.stroke();
            }
        };

        // Allow live switching of sculpture mesh on the same 3D showroom stage
        stage.addEventListener('personality:switch-sculpture', (e) => {
            const detail = e.detail || {};
            mesh = buildSculptureMesh(
                detail.sculptureKey || 'architect_monolith',
                detail.primary || '#1E4D3B',
                detail.accent || '#CFA860'
            );
            ry += 0.45;
            render();
        });

        const tick = () => {
            if (!active) return;
            if (!dragging && !reducedMotion) {
                ry += 0.0068;
            }
            render();
            if (!reducedMotion) {
                rafId = requestAnimationFrame(tick);
            }
        };

        canvas.addEventListener('pointerdown', (e) => {
            dragging = true;
            lastX = e.clientX;
            lastY = e.clientY;
            canvas.style.cursor = 'grabbing';
            canvas.setPointerCapture?.(e.pointerId);
        });
        canvas.addEventListener('pointermove', (e) => {
            if (!dragging) return;
            const dx = e.clientX - lastX;
            const dy = e.clientY - lastY;
            lastX = e.clientX;
            lastY = e.clientY;
            ry += dx * 0.012;
            rx = clamp(rx + dy * 0.012, -1.25, 1.25);
            if (reducedMotion) render();
        });
        const endDrag = () => {
            dragging = false;
            canvas.style.cursor = 'grab';
        };
        canvas.addEventListener('pointerup', endDrag);
        canvas.addEventListener('pointercancel', endDrag);

        stage.querySelector('[data-3d-zoom-in]')?.addEventListener('click', () => {
            zoom = clamp(zoom + 0.14, 0.78, 1.48);
            render();
        });
        stage.querySelector('[data-3d-zoom-out]')?.addEventListener('click', () => {
            zoom = clamp(zoom - 0.14, 0.78, 1.48);
            render();
        });
        stage.querySelector('[data-3d-reset]')?.addEventListener('click', () => {
            rx = 0.36;
            ry = 0.55;
            zoom = 1.02;
            render();
        });

        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => {
                const visible = entries[0]?.isIntersecting ?? true;
                active = visible;
                if (visible) {
                    cancelAnimationFrame(rafId);
                    tick();
                } else {
                    cancelAnimationFrame(rafId);
                }
            }, { threshold: 0.05 });
            io.observe(stage);
        } else {
            tick();
        }
    });
};
