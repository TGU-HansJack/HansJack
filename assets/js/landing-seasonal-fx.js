/* Theme Landing Seasonal Effects */
(function () {
    if (typeof window === "undefined" || typeof document === "undefined") {
        return;
    }

    var globalKey = "__themekitSeasonalFx";
    var previous = window[globalKey];
    if (previous && typeof previous.teardown === "function") {
        try {
            previous.teardown();
        } catch (e) {}
    }

    var runtime = null;
    var resizeTimer = 0;
    var themeObserver = null;
    var reducedMotionMedia = null;

    var raf = window.requestAnimationFrame
        ? function (callback) {
            return window.requestAnimationFrame(callback);
        }
        : function (callback) {
            return window.setTimeout(function () {
                callback(Date.now());
            }, 16);
        };

    var caf = window.cancelAnimationFrame
        ? function (id) {
            window.cancelAnimationFrame(id);
        }
        : function (id) {
            window.clearTimeout(id);
        };

    try {
        if (window.matchMedia) {
            reducedMotionMedia = window.matchMedia("(prefers-reduced-motion: reduce)");
        }
    } catch (e) {
        reducedMotionMedia = null;
    }

    function randomBetween(min, max) {
        return min + Math.random() * (max - min);
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function isIndexPage() {
        var body = document.body;
        return !!(body && body.classList && body.classList.contains("page-index"));
    }

    function resolveHost() {
        if (!isIndexPage()) {
            return null;
        }
        return document.body || null;
    }

    function isReducedMotion() {
        try {
            return !!(reducedMotionMedia && reducedMotionMedia.matches);
        } catch (e) {
            return false;
        }
    }

    function resolveMode() {
        var root = document.documentElement;
        if (!root || !root.classList) {
            return "sakura";
        }

        var isDark = root.classList.contains("theme-dark") && !root.classList.contains("theme-light");
        return isDark ? "snow" : "sakura";
    }

    function ensureThemeObserver() {
        if (themeObserver || !window.MutationObserver) {
            return;
        }

        var root = document.documentElement;
        if (!root) {
            return;
        }

        try {
            themeObserver = new MutationObserver(function (records) {
                for (var i = 0; i < records.length; i++) {
                    var record = records[i];
                    if (!record || record.attributeName !== "class") {
                        continue;
                    }
                    syncMode();
                    break;
                }
            });
            themeObserver.observe(root, { attributes: true, attributeFilter: ["class"] });
        } catch (e) {
            themeObserver = null;
        }
    }

    function disconnectThemeObserver() {
        if (!themeObserver) {
            return;
        }
        try {
            themeObserver.disconnect();
        } catch (e) {}
        themeObserver = null;
    }

    function targetParticleCount(width, height, mode) {
        var area = Math.max(1, width * height);
        var base = mode === "snow" ? area / 17500 : area / 21500;
        var viewportScale = 1;
        if (width <= 1200) {
            viewportScale *= 0.88;
        }
        if (width <= 900) {
            viewportScale *= 0.84;
        }
        if (width <= 680) {
            viewportScale *= 0.76;
        }
        base = base * viewportScale;

        var min = mode === "snow" ? 34 : 24;
        var max = mode === "snow" ? 150 : 112;
        return Math.max(min, Math.min(max, Math.round(base)));
    }

    function setupParticle(particle, mode, width, height, fromTop) {
        var isSnow = mode === "snow";
        var depth = randomBetween(0.68, 1.22);
        var spawnRangeX = width * 0.18;

        particle.mode = mode;
        particle.depth = depth;
        particle.size = isSnow
            ? randomBetween(1.8, 4.6) * depth
            : randomBetween(6.4, 12.6) * depth;

        particle.alpha = isSnow
            ? randomBetween(0.32, 0.86)
            : randomBetween(0.28, 0.74);

        particle.phase = randomBetween(0, Math.PI * 2);
        particle.swayAmp = isSnow
            ? randomBetween(0.03, 0.12) * depth
            : randomBetween(0.05, 0.22) * depth;
        particle.swayFreq = isSnow
            ? randomBetween(0.35, 1.0)
            : randomBetween(0.22, 0.78);

        if (isSnow) {
            particle.vx = randomBetween(-0.22, 0.18);
            particle.vy = randomBetween(0.5, 1.35);
            particle.rot = randomBetween(0, Math.PI * 2);
            particle.rotSpeed = randomBetween(-0.2, 0.2);
            particle.stretch = 1;
        } else {
            particle.vx = randomBetween(0.08, 0.42);
            particle.vy = randomBetween(0.36, 0.88);
            particle.rot = randomBetween(0, Math.PI * 2);
            particle.rotSpeed = randomBetween(-0.42, 0.42);
            particle.stretch = randomBetween(0.74, 1.16);
        }

        particle.x = randomBetween(-spawnRangeX, width + spawnRangeX);
        if (fromTop) {
            particle.y = randomBetween(-height * 0.2, -8);
        } else {
            particle.y = randomBetween(-height * 0.1, height);
        }
    }

    function ensureParticlePool(forceReset) {
        if (!runtime) {
            return;
        }
        var width = runtime.width;
        var height = runtime.height;
        if (!(width > 0 && height > 0)) {
            runtime.particles = [];
            return;
        }

        var mode = runtime.mode;
        var targetCount = targetParticleCount(width, height, mode);
        runtime.targetCount = targetCount;

        if (!Array.isArray(runtime.particles)) {
            runtime.particles = [];
        }

        if (forceReset) {
            for (var r = 0; r < runtime.particles.length; r++) {
                setupParticle(runtime.particles[r], mode, width, height, false);
            }
        }

        while (runtime.particles.length < targetCount) {
            var particle = {};
            setupParticle(particle, mode, width, height, false);
            runtime.particles.push(particle);
        }

        if (runtime.particles.length > targetCount) {
            runtime.particles.length = targetCount;
        }
    }

    function syncCanvasSize(forceRefreshParticles) {
        if (!runtime || !runtime.canvas || !runtime.host) {
            return false;
        }

        var host = runtime.host;
        var docEl = document.documentElement;
        var rect = host.getBoundingClientRect ? host.getBoundingClientRect() : { width: 0, height: 0 };
        var cssWidth = Math.max(
            1,
            Math.floor(
                Math.max(
                    rect.width || 0,
                    host.clientWidth || 0,
                    host.scrollWidth || 0,
                    (docEl && docEl.clientWidth) || 0
                )
            )
        );
        var cssHeight = Math.max(
            1,
            Math.floor(
                Math.max(
                    rect.height || 0,
                    host.clientHeight || 0,
                    host.scrollHeight || 0,
                    (docEl && docEl.clientHeight) || 0,
                    (docEl && docEl.scrollHeight) || 0
                )
            )
        );
        var dpr = clamp(window.devicePixelRatio || 1, 1, 2);
        var pixelWidth = Math.max(1, Math.round(cssWidth * dpr));
        var pixelHeight = Math.max(1, Math.round(cssHeight * dpr));
        var changed = false;

        runtime.canvas.style.width = cssWidth + "px";
        runtime.canvas.style.height = cssHeight + "px";

        if (runtime.canvas.width !== pixelWidth || runtime.canvas.height !== pixelHeight) {
            runtime.canvas.width = pixelWidth;
            runtime.canvas.height = pixelHeight;
            changed = true;
        }

        if (runtime.width !== cssWidth || runtime.height !== cssHeight || runtime.dpr !== dpr) {
            runtime.width = cssWidth;
            runtime.height = cssHeight;
            runtime.dpr = dpr;
            changed = true;
        }

        if (changed || forceRefreshParticles) {
            ensureParticlePool(changed || !!forceRefreshParticles);
        }

        return changed;
    }

    function drawSakura(ctx, particle) {
        var size = particle.size;
        var alpha = clamp(particle.alpha, 0.08, 1);

        ctx.save();
        ctx.translate(particle.x, particle.y);
        ctx.rotate(particle.rot);
        ctx.scale(1, particle.stretch);

        ctx.beginPath();
        ctx.moveTo(0, -size * 0.7);
        ctx.bezierCurveTo(size * 0.85, -size * 0.36, size * 0.88, size * 0.38, 0, size * 0.72);
        ctx.bezierCurveTo(-size * 0.88, size * 0.38, -size * 0.85, -size * 0.36, 0, -size * 0.7);
        ctx.closePath();
        ctx.fillStyle = "rgba(255, 186, 204, " + (alpha * 0.9).toFixed(3) + ")";
        ctx.fill();

        ctx.strokeStyle = "rgba(255, 241, 246, " + (alpha * 0.6).toFixed(3) + ")";
        ctx.lineWidth = Math.max(0.6, size * 0.08);
        ctx.stroke();

        ctx.beginPath();
        ctx.moveTo(0, -size * 0.56);
        ctx.lineTo(0, size * 0.52);
        ctx.strokeStyle = "rgba(255, 255, 255, " + (alpha * 0.3).toFixed(3) + ")";
        ctx.lineWidth = Math.max(0.5, size * 0.05);
        ctx.stroke();

        ctx.restore();
    }

    function drawSnow(ctx, particle) {
        var size = particle.size;
        var alpha = clamp(particle.alpha, 0.1, 1);
        var branchRatio = 0.6;

        ctx.save();
        ctx.translate(particle.x, particle.y);
        ctx.rotate(particle.rot);
        ctx.lineCap = "round";
        ctx.lineWidth = Math.max(0.7, size * 0.12);
        ctx.strokeStyle = "rgba(242, 248, 255, " + (alpha * 0.92).toFixed(3) + ")";

        ctx.beginPath();
        for (var i = 0; i < 6; i++) {
            var angle = (Math.PI / 3) * i;
            var armX = Math.cos(angle) * size;
            var armY = Math.sin(angle) * size;
            ctx.moveTo(0, 0);
            ctx.lineTo(armX, armY);

            var midX = armX * branchRatio;
            var midY = armY * branchRatio;
            var branchA = angle + 0.55;
            var branchB = angle - 0.55;
            var branchLength = size * 0.34;
            ctx.moveTo(midX, midY);
            ctx.lineTo(
                midX + Math.cos(branchA) * branchLength,
                midY + Math.sin(branchA) * branchLength
            );
            ctx.moveTo(midX, midY);
            ctx.lineTo(
                midX + Math.cos(branchB) * branchLength,
                midY + Math.sin(branchB) * branchLength
            );
        }
        ctx.stroke();

        ctx.beginPath();
        ctx.arc(0, 0, Math.max(0.45, size * 0.12), 0, Math.PI * 2);
        ctx.fillStyle = "rgba(255, 255, 255, " + (alpha * 0.82).toFixed(3) + ")";
        ctx.fill();

        ctx.restore();
    }

    function stepFrame(ts) {
        if (!runtime || !runtime.ctx) {
            return;
        }

        if (!runtime.lastTs) {
            runtime.lastTs = ts || Date.now();
        }

        var nowTs = ts || Date.now();
        var dt = (nowTs - runtime.lastTs) / 1000;
        runtime.lastTs = nowTs;
        if (!isFinite(dt) || dt <= 0) {
            dt = 1 / 60;
        }
        if (dt > 0.05) {
            dt = 0.05;
        }
        runtime.time += dt;

        var ctx = runtime.ctx;
        var width = runtime.width;
        var height = runtime.height;
        if (!(width > 0 && height > 0)) {
            runtime.rafId = raf(stepFrame);
            return;
        }

        ctx.save();
        ctx.setTransform(runtime.dpr, 0, 0, runtime.dpr, 0, 0);
        ctx.clearRect(0, 0, width, height);

        var mode = runtime.mode;
        var particles = runtime.particles || [];
        var dtUnit = dt * 60;
        var baseWind = mode === "snow" ? -0.08 : 0.21;
        var gust = Math.sin(runtime.time * (mode === "snow" ? 0.52 : 0.34)) * (mode === "snow" ? 0.12 : 0.18);
        var wind = baseWind + gust;

        for (var i = 0; i < particles.length; i++) {
            var p = particles[i];
            if (!p) {
                continue;
            }
            if (p.mode !== mode) {
                setupParticle(p, mode, width, height, false);
            }

            var sway = Math.sin(runtime.time * p.swayFreq + p.phase) * p.swayAmp;
            var vx = (p.vx + wind * (0.68 + p.depth * 0.34) + sway) * dtUnit;
            var vy = (p.vy * (0.82 + p.depth * 0.26)) * dtUnit;

            p.x += vx;
            p.y += vy;
            p.rot += p.rotSpeed * dtUnit * 0.05;

            var outBottom = p.y > height + 28;
            var outLeft = p.x < -width * 0.2 - 42;
            var outRight = p.x > width * 1.2 + 42;
            if (outBottom || outLeft || outRight) {
                setupParticle(p, mode, width, height, true);
            }

            if (mode === "snow") {
                drawSnow(ctx, p);
            } else {
                drawSakura(ctx, p);
            }
        }

        ctx.restore();
        runtime.rafId = raf(stepFrame);
    }

    function startLoop() {
        if (!runtime || runtime.rafId) {
            return;
        }
        runtime.lastTs = 0;
        runtime.rafId = raf(stepFrame);
    }

    function stopLoop() {
        if (!runtime || !runtime.rafId) {
            return;
        }
        caf(runtime.rafId);
        runtime.rafId = 0;
    }

    function applyHostLayerStyles(state) {
        if (!state || !state.host || !state.canvas) {
            return;
        }

        var host = state.host;
        var canvas = state.canvas;

        canvas.style.position = "absolute";
        canvas.style.left = "0";
        canvas.style.top = "0";
        canvas.style.width = "100%";
        canvas.style.height = "100%";
        canvas.style.display = "block";
        canvas.style.pointerEvents = "none";
        canvas.style.zIndex = "-1";

        if (!state.hostStyleCaptured) {
            state.hostInlinePosition = host.style.position || "";
            state.hostInlineIsolation = host.style.isolation || "";
            state.hostInlineZIndex = host.style.zIndex || "";
            state.hostStyleCaptured = true;
        }

        var hostComputed = "static";
        try {
            hostComputed = window.getComputedStyle(host).position || "static";
        } catch (e) {
            hostComputed = "static";
        }

        if (hostComputed === "static") {
            host.style.position = "relative";
        }
        host.style.isolation = "isolate";
        host.style.zIndex = "0";
    }

    function restoreHostLayerStyles(state) {
        if (!state) {
            return;
        }

        if (state.host && state.hostStyleCaptured) {
            try {
                state.host.style.position = state.hostInlinePosition;
                state.host.style.isolation = state.hostInlineIsolation;
                state.host.style.zIndex = state.hostInlineZIndex;
            } catch (e) {}
        }
    }

    function destroyRuntime() {
        stopLoop();
        if (!runtime) {
            return;
        }
        restoreHostLayerStyles(runtime);
        if (runtime.canvas && runtime.canvas.parentNode) {
            try {
                runtime.canvas.parentNode.removeChild(runtime.canvas);
            } catch (e) {}
        }
        runtime = null;
    }

    function syncMode() {
        if (!runtime) {
            return;
        }
        var nextMode = resolveMode();
        if (runtime.mode === nextMode) {
            return;
        }
        runtime.mode = nextMode;
        if (runtime.canvas && runtime.canvas.style) {
            runtime.canvas.style.opacity = nextMode === "snow" ? "0.94" : "0.88";
        }
        ensureParticlePool(true);
    }

    function ensureRuntime() {
        var host = resolveHost();
        if (!host || isReducedMotion()) {
            destroyRuntime();
            return;
        }

        if (!runtime) {
            var canvas = document.createElement("canvas");
            canvas.className = "landing-seasonal-fx-canvas";
            canvas.setAttribute("aria-hidden", "true");
            canvas.setAttribute("data-landing-seasonal-fx", "1");
            canvas.style.opacity = resolveMode() === "snow" ? "0.92" : "0.88";

            var ctx = null;
            try {
                ctx = canvas.getContext("2d", { alpha: true });
            } catch (e) {
                ctx = null;
            }
            if (!ctx) {
                return;
            }

            host.appendChild(canvas);
            runtime = {
                canvas: canvas,
                host: host,
                ctx: ctx,
                dpr: 1,
                width: 0,
                height: 0,
                particles: [],
                targetCount: 0,
                mode: resolveMode(),
                time: 0,
                lastTs: 0,
                rafId: 0,
                hostStyleCaptured: false,
                hostInlinePosition: "",
                hostInlineIsolation: "",
                hostInlineZIndex: ""
            };
        } else {
            if (runtime.host !== host) {
                restoreHostLayerStyles(runtime);
                runtime.host = host;
                runtime.hostStyleCaptured = false;
            }
            if (runtime.canvas.parentNode !== host) {
                host.appendChild(runtime.canvas);
            }
        }

        applyHostLayerStyles(runtime);
        runtime.canvas.style.opacity = runtime.mode === "snow" ? "0.94" : "0.88";
        syncCanvasSize(false);
        ensureThemeObserver();
        startLoop();
    }

    function handleResize() {
        if (resizeTimer) {
            window.clearTimeout(resizeTimer);
        }
        resizeTimer = window.setTimeout(function () {
            resizeTimer = 0;
            if (!runtime) {
                ensureRuntime();
                return;
            }
            if (syncCanvasSize(false)) {
                syncMode();
            }
        }, 90);
    }

    function handleVisibility() {
        if (document.visibilityState === "hidden") {
            stopLoop();
            return;
        }
        ensureRuntime();
    }

    function handlePjaxAfter() {
        ensureRuntime();
    }

    function handleReducedMotionChange() {
        ensureRuntime();
    }

    function handlePageHide() {
        stopLoop();
    }

    function handlePageShow() {
        ensureRuntime();
    }

    function bindReducedMotionListener() {
        if (!reducedMotionMedia) {
            return;
        }
        if (typeof reducedMotionMedia.addEventListener === "function") {
            reducedMotionMedia.addEventListener("change", handleReducedMotionChange);
        } else if (typeof reducedMotionMedia.addListener === "function") {
            reducedMotionMedia.addListener(handleReducedMotionChange);
        }
    }

    function unbindReducedMotionListener() {
        if (!reducedMotionMedia) {
            return;
        }
        if (typeof reducedMotionMedia.removeEventListener === "function") {
            reducedMotionMedia.removeEventListener("change", handleReducedMotionChange);
        } else if (typeof reducedMotionMedia.removeListener === "function") {
            reducedMotionMedia.removeListener(handleReducedMotionChange);
        }
    }

    function teardownAll() {
        if (resizeTimer) {
            window.clearTimeout(resizeTimer);
            resizeTimer = 0;
        }
        disconnectThemeObserver();
        unbindReducedMotionListener();
        window.removeEventListener("resize", handleResize);
        window.removeEventListener("orientationchange", handleResize);
        window.removeEventListener("themekit:pjax:after", handlePjaxAfter);
        window.removeEventListener("pagehide", handlePageHide);
        window.removeEventListener("pageshow", handlePageShow);
        document.removeEventListener("visibilitychange", handleVisibility);
        destroyRuntime();
    }

    window.addEventListener("resize", handleResize);
    window.addEventListener("orientationchange", handleResize);
    window.addEventListener("themekit:pjax:after", handlePjaxAfter);
    window.addEventListener("pagehide", handlePageHide);
    window.addEventListener("pageshow", handlePageShow);
    document.addEventListener("visibilitychange", handleVisibility);
    bindReducedMotionListener();

    window[globalKey] = {
        refresh: ensureRuntime,
        teardown: teardownAll
    };

    ensureRuntime();
})();

