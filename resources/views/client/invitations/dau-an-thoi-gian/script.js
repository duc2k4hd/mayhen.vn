/**
 * DẤU ẤN THỜI GIAN - CELESTIAL ROMANCE WEDDING INVITATION
 * Pure Vanilla JavaScript - 60FPS Galaxy Starlight
 * Differential 3D Parallax & Dynamic Light Corridor (Sun -> Earth)
 */

(() => {
    'use strict';

    /* BẮT BUỘC CUỘN LÊN ĐẦU TRANG KHI LOAD / RELOAD */
    if ('scrollRestoration' in history) {
        history.scrollRestoration = 'manual';
    }
    window.scrollTo(0, 0);
    window.addEventListener('beforeunload', () => window.scrollTo(0, 0));
    window.addEventListener('load', () => window.scrollTo(0, 0));

    /* Prefix helper */
    const P = 'mayhen_invitation_ladoifji894yfv_';
    const $ = (id, root = document) => root.querySelector('.' + P + id) || root.getElementById(P + id);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll('.' + P + sel));

    /* Quản lý mở thiệp */
    const body = document.body;
    const cover = $('cover');
    const openBtn = $('open');
    const on = P + 'on';
    const ready = P + 'ready';
    const lock = P + 'lock';
    const opening = P + 'opening';
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* Nhận biết chế độ nhúng Studio Editor */
    const isEmbed = Boolean(
        window.__MAYHEN_IS_EMBED ||
        (window.MAYHEN_INVITATION_CONFIG && window.MAYHEN_INVITATION_CONFIG.isEmbed) ||
        new URLSearchParams(location.search).get('embed') === '1'
    );

    if (isEmbed) {
        body.classList.remove(lock);
        body.classList.add(ready);
        if (cover) {
            cover.hidden = true;
            cover.style.display = 'none';
        }
    }

    /* =========================================================================
       THREE.JS PHOTOREALISTIC 3D DEEP SPACE: MẶT TRỜI, TRÁI ĐẤT & VŨ TRỤ 60FPS
       ========================================================================= */
    const cv = $('sky');
    if (!cv || typeof THREE === 'undefined') return;

    /* 1. Khởi tạo Renderer, Scene & Camera 3D */
    const renderer = new THREE.WebGLRenderer({
        canvas: cv,
        antialias: true,
        alpha: false,
        powerPreference: 'high-performance'
    });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 0.95;

    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x02030a);

    const camera = new THREE.PerspectiveCamera(45, window.innerWidth / window.innerHeight, 0.1, 2000);
    camera.position.set(0, 0, 56);

    /* 2. Hệ thống Parallax làm mượt quán tính (Smooth Lerp Scroll) */
    let targetScrollY = window.pageYOffset || 0;
    let smoothScrollY = targetScrollY;
    let isWarping = false;
    let warpProgress = 0;

    function onScroll() {
        targetScrollY = window.pageYOffset || 0;
        if (Math.random() < 0.28) {
            triggerMeteor3D();
        }
    }
    window.addEventListener('scroll', onScroll, { passive: true });

    /* =========================================================================
       3. NẠP TEXTURE THIÊN VĂN CHÂN THỰC 100% NASA & KHỞI TẠO SPRITE ÁNH SÁNG
       ========================================================================= */
    const textureLoader = new THREE.TextureLoader();

    /* 3.0 Nạp Texture Thiên Văn NASA từ Base64 cục bộ (0% lỗi CORS) */
    const texSun = (typeof window.TEX_SUN !== 'undefined')
        ? textureLoader.load(window.TEX_SUN)
        : null;
    if (texSun) {
        texSun.wrapS = THREE.RepeatWrapping;
        texSun.wrapT = THREE.ClampToEdgeWrapping;
    }

    const texEarth = (typeof window.TEX_EARTH !== 'undefined')
        ? textureLoader.load(window.TEX_EARTH)
        : null;
    if (texEarth) {
        texEarth.wrapS = THREE.RepeatWrapping;
    }

    const texClouds = (typeof window.TEX_CLOUDS !== 'undefined')
        ? textureLoader.load(window.TEX_CLOUDS)
        : null;
    if (texClouds) {
        texClouds.wrapS = THREE.RepeatWrapping;
    }

    /* 3.1 Sprite Hạt Tròn Phát Sáng (Triệt tiêu 100% hình vuông 8-bit) */
    function createGlowDotTexture() {
        const c = document.createElement('canvas');
        c.width = 64; c.height = 64;
        const ctx = c.getContext('2d');
        const grad = ctx.createRadialGradient(32, 32, 0, 32, 32, 32);
        grad.addColorStop(0, 'rgba(255, 255, 255, 1.0)');
        grad.addColorStop(0.25, 'rgba(255, 225, 140, 0.9)');
        grad.addColorStop(0.55, 'rgba(255, 160, 40, 0.35)');
        grad.addColorStop(1, 'rgba(0, 0, 0, 0)');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, 64, 64);
        return new THREE.CanvasTexture(c);
    }

    /* 3.2 Sprite Ngôi Sao Mềm Lấp Lánh */
    function createStarSpriteTexture() {
        const c = document.createElement('canvas');
        c.width = 32; c.height = 32;
        const ctx = c.getContext('2d');
        const grad = ctx.createRadialGradient(16, 16, 0, 16, 16, 16);
        grad.addColorStop(0, 'rgba(255, 255, 255, 1.0)');
        grad.addColorStop(0.35, 'rgba(255, 255, 255, 0.7)');
        grad.addColorStop(0.7, 'rgba(200, 230, 255, 0.2)');
        grad.addColorStop(1, 'rgba(0, 0, 0, 0)');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, 32, 32);
        return new THREE.CanvasTexture(c);
    }

    /* 3.3 Hào quang Nhật Hoa Corona viền ngoài (Lõi trong suốt để lộ rõ 100% vân lửa Mặt Trời) */
    function createSunCoronaTexture() {
        const c = document.createElement('canvas');
        c.width = 512; c.height = 512;
        const ctx = c.getContext('2d');
        const cx = 256, cy = 256;

        /* Bán kính viền đĩa Mặt Trời tương ứng r ~ 140 trong canvas */
        const grad = ctx.createRadialGradient(cx, cy, 136, cx, cy, 256);
        grad.addColorStop(0, 'rgba(0, 0, 0, 0)');              /* Lõi hoàn toàn trong suốt */
        grad.addColorStop(0.10, 'rgba(255, 230, 100, 0.75)');  /* Vành lửa vàng sáng sát mép */
        grad.addColorStop(0.35, 'rgba(255, 140, 20, 0.40)');   /* Ánh cam ấm dịu */
        grad.addColorStop(0.65, 'rgba(220, 50, 0, 0.12)');     /* Cam đỏ vũ trụ */
        grad.addColorStop(1, 'rgba(0, 0, 0, 0)');              /* Mờ dần vào bóng đêm */
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, 512, 512);

        return new THREE.CanvasTexture(c);
    }

    /* 3.4 Vành khí quyển xanh ngọc thanh thoát của Trái Đất (Lõi trong suốt, viền mỏng thanh tú) */
    function createAtmosphereGlowTexture() {
        const c = document.createElement('canvas');
        c.width = 512; c.height = 512;
        const ctx = c.getContext('2d');
        const cx = 256, cy = 256;

        const grad = ctx.createRadialGradient(cx, cy, 212, cx, cy, 256);
        grad.addColorStop(0, 'rgba(0, 0, 0, 0)');              /* Lõi trong suốt, giữ trọn đại dương */
        grad.addColorStop(0.20, 'rgba(0, 220, 255, 0.70)');   /* Viền xanh ngọc tầng ôzôn */
        grad.addColorStop(0.60, 'rgba(2, 136, 209, 0.20)');   /* Tán xạ lam nhạt */
        grad.addColorStop(1, 'rgba(0, 0, 0, 0)');              /* Hoàn toàn trong suốt */

        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, 512, 512);

        return new THREE.CanvasTexture(c);
    }

    /* =========================================================================
       4. DỰNG MÔ HÌNH THIÊN THỂ 3D VÀ KHÔNG GIAN VŨ TRỤ (CHUẨN ẢNH THAM CHIẾU 2)
       ========================================================================= */

    /* 4.1 MẶT TRỜI 3D (THE 3D SUN) - GÓC TÂY BẮC (LÀM NỀN TRẦM ẤM, NHẸ NHÀNG) */
    const sunGroup = new THREE.Group();
    scene.add(sunGroup);

    const sunGeo = new THREE.SphereGeometry(9.6, 64, 64);
    const sunMat = new THREE.MeshBasicMaterial({
        map: texSun,
        color: 0xcc8838, /* Tông màu hổ phách trầm ấm, dịu mắt, làm nền cho thiệp */
        transparent: true,
        opacity: 0.65    /* Nhạt hơn để thiệp cưới ở trung tâm luôn là tâm điểm số 1 */
    });
    const sunMesh = new THREE.Mesh(sunGeo, sunMat);
    sunGroup.add(sunMesh);

    /* Vành nhật hoa Corona mềm dịu bao quanh viền ngoài Mặt Trời */
    const coronaMat = new THREE.SpriteMaterial({
        map: createSunCoronaTexture(),
        transparent: true,
        blending: THREE.AdditiveBlending,
        opacity: 0.42,   /* Hào quang dịu êm, tinh tế, không lấn át */
        depthWrite: false,
        depthTest: false
    });
    const coronaSprite = new THREE.Sprite(coronaMat);
    coronaSprite.scale.set(30, 30, 1);
    sunGroup.add(coronaSprite);

    /* Nguồn sáng định hướng từ Mặt Trời dịu nhẹ, tạo độ tương phản chân thực */
    const sunDirLight = new THREE.DirectionalLight(0xfffaed, 1.2);
    sunDirLight.position.set(-22, 12, -10);
    scene.add(sunDirLight);

    /* Nguồn sáng điểm nhẹ nhàng quanh Mặt Trời */
    const sunPointLight = new THREE.PointLight(0xff9800, 0.8, 450, 0.6);
    sunGroup.add(sunPointLight);

    /* Ánh sáng môi trường vũ trụ trầm ấm */
    const ambientLight = new THREE.AmbientLight(0x0a1424, 0.18);
    scene.add(ambientLight);

    /* 4.2 TRÁI ĐẤT 3D (THE 3D EARTH) - GÓC ĐÔNG NAM (CHÌM ÊM ĐỀM VÀO NỀN) */
    const earthGroup = new THREE.Group();
    /* Trục nghiêng thiên văn chuẩn 23.44 độ của Trái Đất */
    earthGroup.rotation.z = -23.44 * Math.PI / 180;
    scene.add(earthGroup);

    const earthGeo = new THREE.SphereGeometry(5.4, 64, 64);
    const earthMat = new THREE.MeshStandardMaterial({
        map: texEarth,
        roughness: 0.80,
        metalness: 0.05,
        transparent: true,
        opacity: 0.68 /* Nhạt hơn, hòa nhịp với chiều sâu vũ trụ */
    });
    const earthMesh = new THREE.Mesh(earthGeo, earthMat);
    earthGroup.add(earthMesh);

    /* Lớp mây trôi tự nhiên mỏng nhẹ (không che kín lục địa và đại dương) */
    const cloudGeo = new THREE.SphereGeometry(5.46, 64, 64);
    const cloudMat = new THREE.MeshStandardMaterial({
        map: texClouds,
        transparent: true,
        opacity: 0.24,
        roughness: 0.9
    });
    const cloudMesh = new THREE.Mesh(cloudGeo, cloudMat);
    earthGroup.add(cloudMesh);

    /* Vành khí quyển xanh ngọc thanh tú (Không che sao phía sau) */
    const atmoMat = new THREE.SpriteMaterial({
        map: createAtmosphereGlowTexture(),
        transparent: true,
        blending: THREE.AdditiveBlending,
        opacity: 0.35, /* Rất mỏng và thanh tú */
        depthWrite: false,
        depthTest: false
    });
    const atmoSprite = new THREE.Sprite(atmoMat);
    atmoSprite.scale.set(11.6, 11.6, 1);
    earthGroup.add(atmoSprite);

    /* Căn chỉnh vị trí không gian 3D ban đầu (Lùi xa ra 2 góc để thiệp cưới có không gian thở thoáng đãng) */
    let baseSunPos = { x: -27, y: 13, z: -16 };
    let baseEarthPos = { x: 26, y: -12, z: -8 };

    function updateLayoutPositions() {
        const isPortrait = window.innerHeight > window.innerWidth;
        if (!isPortrait) {
            /* Desktop: Lùi xa vào 2 góc màn hình */
            baseSunPos = { x: -27, y: 13, z: -16 };
            baseEarthPos = { x: 26, y: -12, z: -8 };
            sunGroup.scale.set(0.76, 0.76, 0.76);
            earthGroup.scale.set(0.76, 0.76, 0.76);
        } else {
            /* Mobile */
            baseSunPos = { x: -14, y: 16, z: -12 };
            baseEarthPos = { x: 12, y: -15, z: -7 };
            sunGroup.scale.set(0.60, 0.60, 0.60);
            earthGroup.scale.set(0.60, 0.60, 0.60);
        }
        sunGroup.position.set(baseSunPos.x, baseSunPos.y, baseSunPos.z);
        earthGroup.position.set(baseEarthPos.x, baseEarthPos.y, baseEarthPos.z);
        sunDirLight.target = earthGroup;
    }
    updateLayoutPositions();

    /* 4.3 TRƯỜNG SAO 3D BẠT NGÀN PHỦ KHẮP VŨ TRỤ (KHÔNG BỊ CHE KHUẤT) */
    const starCount = 3200;
    const starGeo = new THREE.BufferGeometry();
    const starPos = new Float32Array(starCount * 3);
    const starColors = new Float32Array(starCount * 3);

    for (let i = 0; i < starCount; i++) {
        starPos[i * 3]     = (Math.random() - 0.5) * 440;
        starPos[i * 3 + 1] = (Math.random() - 0.5) * 320;
        starPos[i * 3 + 2] = - 10 - Math.random() * 300;

        const r = Math.random();
        if (r < 0.30) {
            starColors[i * 3] = 1.0; starColors[i * 3 + 1] = 0.90; starColors[i * 3 + 2] = 0.65; /* Vàng ấm */
        } else if (r < 0.60) {
            starColors[i * 3] = 0.65; starColors[i * 3 + 1] = 0.88; starColors[i * 3 + 2] = 1.0; /* Lam ngọc */
        } else {
            starColors[i * 3] = 1.0; starColors[i * 3 + 1] = 1.0; starColors[i * 3 + 2] = 1.0;  /* Kim cương */
        }
    }

    starGeo.setAttribute('position', new THREE.BufferAttribute(starPos, 3));
    starGeo.setAttribute('color', new THREE.BufferAttribute(starColors, 3));

    const starMat = new THREE.PointsMaterial({
        size: 1.8,
        map: createStarSpriteTexture(),
        vertexColors: true,
        transparent: true,
        opacity: 0.50, /* Giảm độ sáng của sao để thiệp cưới nổi bật nhất */
        blending: THREE.AdditiveBlending,
        depthWrite: false,
        sizeAttenuation: true
    });
    const starField = new THREE.Points(starGeo, starMat);
    scene.add(starField);

    /* 4.4 BỤI SAO VŨ TRỤ NHẸ NHÀNG TRONG VÙNG ÁNH SÁNG MẶT TRỜI (GENTLE AMBIENT STARDUST) */
    const dustCount = 80;
    const dustGeo = new THREE.BufferGeometry();
    const dustPos = new Float32Array(dustCount * 3);
    const dustData = [];

    for (let i = 0; i < dustCount; i++) {
        const rx = -28 + Math.random() * 56;
        const ry = -18 + Math.random() * 36;
        const rz = -20 + Math.random() * 25;

        dustPos[i * 3]     = rx;
        dustPos[i * 3 + 1] = ry;
        dustPos[i * 3 + 2] = rz;

        dustData.push({
            vx: (Math.random() - 0.5) * 0.008,
            vy: (Math.random() - 0.5) * 0.008,
            vz: (Math.random() - 0.5) * 0.005
        });
    }

    dustGeo.setAttribute('position', new THREE.BufferAttribute(dustPos, 3));
    const dustMat = new THREE.PointsMaterial({
        size: 2.0,
        map: createGlowDotTexture(),
        color: 0xffe082,
        transparent: true,
        opacity: 0.28, /* Rất dịu */
        blending: THREE.AdditiveBlending,
        depthWrite: false
    });
    const stardustPoints = new THREE.Points(dustGeo, dustMat);
    scene.add(stardustPoints);

    /* 4.5 SAO BĂNG 3D (3D METEORS) */
    const meteors3D = [];
    function triggerMeteor3D() {
        if (meteors3D.length > 5) return;
        const lineGeo = new THREE.BufferGeometry();
        const startX = -30 + Math.random() * 40;
        const startY = 25 + Math.random() * 10;
        const startZ = -10 - Math.random() * 20;

        const positions = new Float32Array([
            startX, startY, startZ,
            startX + 10, startY - 14, startZ
        ]);
        lineGeo.setAttribute('position', new THREE.BufferAttribute(positions, 3));

        const lineMat = new THREE.LineBasicMaterial({
            color: Math.random() < 0.5 ? 0xffe082 : 0x80d8ff,
            transparent: true,
            opacity: 1.0,
            blending: THREE.AdditiveBlending
        });
        const line = new THREE.Line(lineGeo, lineMat);
        scene.add(line);
        meteors3D.push({ line, life: 1.0, vx: 0.9, vy: -1.25 });
    }

    setInterval(() => {
        if (!isWarping) triggerMeteor3D();
    }, 3800);

    /* 5. CẬP NHẬT KÍCH THƯỚC KHI RESIZE */
    function onWindowResize() {
        const w = window.innerWidth;
        const h = window.innerHeight;
        camera.aspect = w / h;
        camera.updateProjectionMatrix();
        renderer.setSize(w, h);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
        updateLayoutPositions();
    }
    window.addEventListener('resize', onWindowResize);

    /* 6. HOẠT CẢNH MỞ THIỆP (WARP DRIVE HYPERSPACE) */
    function triggerOpeningCinematics() {
        if (isWarping) return;
        isWarping = true;
        warpProgress = 0;

        if (cover) {
            cover.classList.add(opening);
        }

        setTimeout(() => {
            body.classList.remove(lock);
            body.classList.add(ready);
            if (cover) {
                cover.style.display = 'none';
            }
        }, 1100);

        setTimeout(() => {
            isWarping = false;
        }, 1600);
    }

    if (openBtn) {
        openBtn.addEventListener('click', triggerOpeningCinematics);
    }

    /* 7. VÒNG LẶP RENDER 60FPS VỚI CHUYỂN ĐỘNG PARALLAX 3D THỰC SỰ */
    let clock = new THREE.Clock();

    function animate3D() {
        requestAnimationFrame(animate3D);
        const delta = clock.getDelta();
        const time = clock.getElapsedTime();

        /* Thuật toán làm mượt quán tính khi cuộn (Smooth Lerp Scroll) */
        smoothScrollY += (targetScrollY - smoothScrollY) * 0.065;

        /* Parallax 3D: Vị trí Mặt Trời và Trái Đất dịch chuyển vi sai theo chiều sâu */
        sunGroup.position.y = baseSunPos.y + (smoothScrollY * 0.007);
        sunGroup.position.x = baseSunPos.x + Math.sin(smoothScrollY * 0.0006) * 1.5;

        earthGroup.position.y = baseEarthPos.y - (smoothScrollY * 0.015);
        earthGroup.position.x = baseEarthPos.x + Math.cos(smoothScrollY * 0.0008) * 1.8;

        /* Camera phối cảnh 3D di chuyển êm đềm */
        camera.position.x = Math.sin(smoothScrollY * 0.0005) * 2.8;
        camera.position.y = - (smoothScrollY * 0.010);
        camera.lookAt(0, 0, -18);

        /* Hiệu ứng Warp Drive khi mở thiệp */
        if (isWarping) {
            camera.position.z -= 0.6;
            camera.fov = 45 + Math.sin(time * 8) * 4;
            camera.updateProjectionMatrix();
        } else if (camera.position.z < 56) {
            camera.position.z += 0.3;
            camera.fov = 45;
            camera.updateProjectionMatrix();
        }

        /* Thiên thể tự xoay quanh trục tự nhiên */
        sunMesh.rotation.y += 0.0016;
        earthMesh.rotation.y += 0.0035;
        cloudMesh.rotation.y += 0.0052;

        /* Nhịp thở hào quang Corona dịu dàng */
        const coronaPulse = 1.0 + 0.03 * Math.sin(time * 2.0);
        coronaSprite.scale.set(30 * coronaPulse, 30 * coronaPulse, 1);

        /* Nguồn sáng luôn phát ra từ Mặt Trời */
        sunDirLight.position.copy(sunGroup.position);

        /* Cập nhật bụi sao vũ trụ bềnh bồng nhẹ nhàng (không chạy thành dòng máu) */
        const dPositions = dustGeo.attributes.position.array;
        for (let i = 0; i < dustCount; i++) {
            const dt = dustData[i];
            dPositions[i * 3]     += dt.vx;
            dPositions[i * 3 + 1] += dt.vy;
            dPositions[i * 3 + 2] += dt.vz;

            /* Giữ bụi sao lơ lửng trong không gian vũ trụ */
            if (dPositions[i * 3] > 32) dPositions[i * 3] = -32;
            if (dPositions[i * 3] < -32) dPositions[i * 3] = 32;
            if (dPositions[i * 3 + 1] > 22) dPositions[i * 3 + 1] = -22;
            if (dPositions[i * 3 + 1] < -22) dPositions[i * 3 + 1] = 22;
        }
        dustGeo.attributes.position.needsUpdate = true;

        /* Cập nhật sao băng 3D */
        for (let j = meteors3D.length - 1; j >= 0; j--) {
            const m = meteors3D[j];
            m.line.position.x += m.vx;
            m.line.position.y += m.vy;
            m.life -= 0.024;
            m.line.material.opacity = m.life;
            if (m.life <= 0) {
                scene.remove(m.line);
                m.line.geometry.dispose();
                m.line.material.dispose();
                meteors3D.splice(j, 1);
            }
        }

        renderer.render(scene, camera);
    }
    animate3D();

    /* =========================================================================
       LOGIC TƯƠNG TÁC THIỆP CƯỚI (CALENDAR, COUNTDOWN, TIMELINE, RSVP, ALBUM)
       ========================================================================= */

    /* Scroll reveal */
    const rvEls = $$('rv');
    if ('IntersectionObserver' in window) {
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add(P + 'in');
                }
            });
        }, { threshold: 0.1 });
        rvEls.forEach(el => obs.observe(el));
    } else {
        rvEls.forEach(el => el.classList.add(P + 'in'));
    }

    /* Lịch cưới Tháng 12/2026 */
    (() => {
        const grid = $('cal_days');
        if (!grid) return;
        const totalDays = 31;
        const startDayOfWeek = 2;

        for (let i = 1; i < startDayOfWeek; i++) {
            const empty = document.createElement('span');
            empty.className = P + 'cal_empty';
            grid.appendChild(empty);
        }

        for (let d = 1; d <= totalDays; d++) {
            const dayEl = document.createElement('span');
            dayEl.textContent = d;
            if (d === 20) {
                dayEl.classList.add(P + 'cal_target');
            }
            grid.appendChild(dayEl);
        }
    })();

    /* Đồng hồ đếm ngược */
    (() => {
        const countBox = $('count');
        if (!countBox) return;
        const target = new Date(countBox.dataset.end || '2026-12-20T18:00:00').getTime();

        function updateCountdown() {
            const now = Date.now();
            const diff = Math.max(0, target - now);
            const sec = Math.floor(diff / 1000);

            const d = Math.floor(sec / 86400);
            const h = Math.floor((sec % 86400) / 3600);
            const m = Math.floor((sec % 3600) / 60);
            const s = sec % 60;

            const elD = countBox.querySelector('[data-u="d"]');
            const elH = countBox.querySelector('[data-u="h"]');
            const elM = countBox.querySelector('[data-u="m"]');
            const elS = countBox.querySelector('[data-u="s"]');

            if (elD) elD.textContent = d;
            if (elH) elH.textContent = String(h).padStart(2, '0');
            if (elM) elM.textContent = String(m).padStart(2, '0');
            if (elS) elS.textContent = String(s).padStart(2, '0');
        }
        updateCountdown();
        setInterval(updateCountdown, 1000);
    })();

    /* Cửa mở bản đồ */
    const doorBtn = $('door_btn');
    if (doorBtn) {
        doorBtn.addEventListener('click', () => {
            const mapEl = $('map');
            if (mapEl) mapEl.classList.add(P + 'open_door');
            if (typeof triggerMeteor3D === 'function') triggerMeteor3D();
        });
    }

    /* Album Coverflow 3D ảnh cưới & Lightbox */
    (() => {
        const box = $('flow');
        if (!box) return;
        const items = $$('flow_item', box);
        const n = items.length;
        if (!n) return;
        let cur = Math.floor(n / 2);

        // Danh sách ảnh
        const photos = items.map(it => {
            if (it.dataset.full) return it.dataset.full;
            const bg = it.style.backgroundImage || window.getComputedStyle(it).backgroundImage;
            const match = bg.match(/url\(['"]?(.*?)['"]?\)/);
            return match ? match[1] : '';
        });

        const draw = () => items.forEach((it, i) => {
            const d = i - cur, a = Math.abs(d);
            const isMobile = window.innerWidth < 600;
            const stepX = isMobile ? 48 : 58;
            it.style.transform = `translateX(-50%) translateX(${d * stepX}%) translateZ(${-a * (isMobile ? 80 : 120)}px) rotateY(${-Math.sign(d) * (isMobile ? 32 : 38) * (a ? 1 : 0)}deg) scale(${a ? 0.85 : 1})`;
            it.style.zIndex = 100 - a;
            it.style.opacity = a > 2 ? 0 : 1;
            it.style.filter = a ? 'brightness(.72)' : 'none';
            it.style.pointerEvents = a > 2 ? 'none' : 'auto';
            it.style.cursor = (i === cur) ? 'zoom-in' : 'pointer';
        });

        const go = k => { cur = (cur + k + n) % n; draw(); };
        const prev = $('prev', box), next = $('next', box);
        if (prev) prev.onclick = (e) => { e.stopPropagation(); go(-1); };
        if (next) next.onclick = (e) => { e.stopPropagation(); go(1); };

        /* Lightbox logic */
        const lb = $('lightbox');
        const lbImg = $('lightbox_img');
        const lbCounter = $('lightbox_counter');
        const lbClose = $('lightbox_close');
        const lbPrev = $('lightbox_prev');
        const lbNext = $('lightbox_next');
        const lbBackdrop = $('lightbox_backdrop');
        let lbIndex = cur;

        const updateLb = (idx) => {
            lbIndex = (idx + n) % n;
            if (lbImg && photos[lbIndex]) lbImg.src = photos[lbIndex];
            if (lbCounter) lbCounter.textContent = `${lbIndex + 1} / ${n}`;
        };

        const openLb = (idx) => {
            if (!lb) return;
            updateLb(idx);
            lb.hidden = false;
            document.body.style.overflow = 'hidden';
        };

        const closeLb = () => {
            if (!lb) return;
            lb.hidden = true;
            document.body.style.overflow = '';
        };

        if (lbClose) lbClose.onclick = closeLb;
        if (lbBackdrop) lbBackdrop.onclick = closeLb;
        if (lbPrev) lbPrev.onclick = (e) => { e.stopPropagation(); updateLb(lbIndex - 1); };
        if (lbNext) lbNext.onclick = (e) => { e.stopPropagation(); updateLb(lbIndex + 1); };

        window.addEventListener('keydown', e => {
            if (lb && !lb.hidden) {
                if (e.key === 'Escape') closeLb();
                else if (e.key === 'ArrowLeft') updateLb(lbIndex - 1);
                else if (e.key === 'ArrowRight') updateLb(lbIndex + 1);
            }
        });

        items.forEach((it, i) => it.onclick = () => {
            if (cur === i) {
                openLb(i);
            } else {
                cur = i;
                draw();
            }
        });

        let x0 = null;
        box.addEventListener('touchstart', e => { x0 = e.touches[0].clientX; }, { passive: true });
        box.addEventListener('touchend', e => {
            if (x0 === null) return;
            const dx = e.changedTouches[0].clientX - x0;
            x0 = null;
            if (Math.abs(dx) > 35) go(dx < 0 ? 1 : -1);
        });
        draw();
    })();

    /* Toast */
    const toast = $('toast');
    let toastTimer;
    const showToast = msg => {
        if (!toast) return;
        toast.textContent = msg;
        toast.classList.add(P + 'show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => toast.classList.remove(P + 'show'), 3600);
    };

    /* Form RSVP */
    $$('opts').forEach(group => group.addEventListener('click', e => {
        const btn = e.target.closest('.' + P + 'opt');
        if (!btn) return;
        $$('opt', group).forEach(o => o.classList.toggle(on, o === btn));
        if (group.classList.contains(P + 'attend')) {
            const guestsEl = $('guests');
            if (guestsEl) guestsEl.hidden = !btn.hasAttribute('data-yes');
        }
    }));

    const counterEl = $('counter');
    if (counterEl) {
        const out = counterEl.querySelector('output');
        counterEl.addEventListener('click', e => {
            const b = e.target.closest('button');
            if (b && out) out.textContent = Math.min(20, Math.max(1, +out.textContent + +b.dataset.n));
        });
    }

    $$('form').forEach(form => form.addEventListener('submit', async e => {
        e.preventDefault();
        const csrfToken = window.__MAYHEN_CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        if (form.classList.contains(P + 'wish')) {
            const nm = $('wish_name', form), tx = $('wish_text', form), list = $('list', form);
            const nameVal = nm ? nm.value.trim() : '';
            const textVal = tx ? tx.value.trim() : '';
            if (!nameVal || !textVal) {
                showToast('Vui lòng nhập tên và lời chúc của bạn');
                return;
            }

            const wishUrl = window.__MAYHEN_WISH_SUBMIT_URL || (window.MAYHEN_INVITATION_CONFIG && window.MAYHEN_INVITATION_CONFIG.wishSubmitUrl);
            if (wishUrl) {
                try {
                    const res = await fetch(wishUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ guest_name: nameVal, message: textVal })
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        showToast(data.message || 'Không thể gửi lời chúc, vui lòng thử lại');
                        return;
                    }
                } catch (err) {
                    // Fallback
                }
            }

            if (list) {
                const it = document.createElement('div');
                it.className = P + 'item';
                it.innerHTML = '<b></b><p></p>';
                it.firstChild.textContent = nameVal;
                it.lastChild.textContent  = textVal;
                const em = $('empty', form);
                if (em) em.remove();
                list.prepend(it);
            }
            if (nm) nm.value = '';
            if (tx) tx.value = '';
            showToast('Cảm ơn bạn đã gửi lời chúc tốt đẹp!');
            if (typeof triggerMeteor3D === 'function') triggerMeteor3D();
            return;
        }

        // Form RSVP:
        const nameInput = form.querySelector('input[type="text"]');
        const nameVal = nameInput ? nameInput.value.trim() : '';
        if (!nameVal) {
            showToast('Vui lòng nhập họ và tên của bạn');
            return;
        }

        const attendBtn = form.querySelector('.' + P + 'attend .' + on);
        const attending = attendBtn ? attendBtn.hasAttribute('data-yes') : true;

        const sideBtn = form.querySelector('.' + P + 'side .' + on);
        const sideVal = sideBtn ? sideBtn.textContent.trim() : '';

        const guestsOut = form.querySelector('.' + P + 'counter output');
        const guestCount = guestsOut ? parseInt(guestsOut.textContent) || 1 : 1;

        const rsvpUrl = window.__MAYHEN_RSVP_SUBMIT_URL || (window.MAYHEN_INVITATION_CONFIG && window.MAYHEN_INVITATION_CONFIG.rsvpSubmitUrl);
        if (rsvpUrl) {
            try {
                const res = await fetch(rsvpUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        guest_name: nameVal,
                        attending: attending,
                        guest_count: guestCount,
                        guest_side: sideVal
                    })
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) {
                    showToast(data.message || 'Không thể gửi phản hồi, vui lòng thử lại');
                    return;
                }
            } catch (err) {
                // Fallback
            }
        }

        showToast(form.dataset.msg || 'Cảm ơn bạn đã xác nhận tham dự!');
        if (typeof triggerMeteor3D === 'function') triggerMeteor3D();
    }));

    /* Hộp Mừng Cưới */
    const boxBtn = $('box');
    const hintBtn = $('hint');
    const giftEl = $('gift');
    const openGift = () => {
        const lid = $('box_lid');
        if (lid) {
            lid.style.transform = 'translate(-40px,-120px) rotate(-35deg)';
            lid.style.opacity = '0';
        }
        if (typeof triggerMeteor3D === 'function') triggerMeteor3D();
        setTimeout(() => { if (giftEl) giftEl.classList.add(on); }, reduce ? 0 : 500);
    };
    if (boxBtn) boxBtn.addEventListener('click', openGift);
    if (hintBtn) hintBtn.addEventListener('click', openGift);
    if (isEmbed && giftEl) {
        giftEl.classList.add(on);
    }

})();