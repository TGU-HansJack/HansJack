<?php if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
} ?>
</div>

<footer class="hj-footer" id="hj-footer">
    <div class="hj-shell">
        <p>
            &copy; <?php echo date('Y'); ?>
            <a href="<?php $this->options->siteUrl(); ?>"><?php $this->options->title(); ?></a>
            · Theme HansJack
        </p>
    </div>
</footer>

<div class="hj-fab" aria-label="<?php _e('快捷操作'); ?>">
    <button class="hj-fab-btn hj-fab-top" type="button" aria-label="<?php _e('返回顶部'); ?>" title="<?php _e('返回顶部'); ?>">
        <span class="hj-fab-ring" aria-hidden="true">
            <svg class="hj-fab-ring-svg" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                <circle class="hj-fab-ring-bg" cx="20" cy="20" r="18"></circle>
                <circle class="hj-fab-ring-fg hj-fab-top-ring-fg" cx="20" cy="20" r="18" stroke-dasharray="113.10 113.10" stroke-dashoffset="113.10"></circle>
            </svg>
        </span>
        <span class="hj-fab-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-to-line-icon lucide-arrow-up-to-line" aria-hidden="true"><path d="M5 3h14"/><path d="m18 13-6-6-6 6"/><path d="M12 7v14"/></svg>
        </span>
    </button>
    <button class="hj-fab-btn hj-fab-comment" type="button" aria-label="<?php _e('评论'); ?>" title="<?php _e('评论'); ?>">
        <span class="hj-fab-ring" aria-hidden="true">
            <svg class="hj-fab-ring-svg" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                <circle class="hj-fab-ring-bg" cx="20" cy="20" r="18"></circle>
                <circle class="hj-fab-ring-fg" cx="20" cy="20" r="18"></circle>
            </svg>
        </span>
        <span class="hj-fab-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square-icon lucide-message-square" aria-hidden="true"><path d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"/></svg>
        </span>
    </button>
    <button class="hj-fab-btn hj-fab-toc" type="button" aria-label="<?php _e('目录'); ?>" title="<?php _e('目录'); ?>" aria-haspopup="true" aria-expanded="false">
        <span class="hj-fab-ring" aria-hidden="true">
            <svg class="hj-fab-ring-svg" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                <circle class="hj-fab-ring-bg" cx="20" cy="20" r="18"></circle>
                <circle class="hj-fab-ring-fg" cx="20" cy="20" r="18"></circle>
            </svg>
        </span>
        <span class="hj-fab-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-table-of-contents-icon lucide-table-of-contents" aria-hidden="true"><path d="M16 5H3"/><path d="M16 12H3"/><path d="M16 19H3"/><path d="M21 5h.01"/><path d="M21 12h.01"/><path d="M21 19h.01"/></svg>
        </span>
    </button>
</div>

<div class="hj-mobile-toc-backdrop" data-hj-mobile-toc-backdrop aria-hidden="true"></div>

<div class="hj-theme-curtain" aria-hidden="true"></div>

<script>
    (function () {
        var root = document.documentElement;
        if (!root) {
            return;
        }

        // Enable hover transitions after the initial paint to avoid replaying the
        // "expand" animation when navigating while the cursor is still hovering.
        var raf = window.requestAnimationFrame
            ? window.requestAnimationFrame.bind(window)
            : function (callback) {
                return window.setTimeout(callback, 16);
            };

        raf(function () {
            raf(function () {
                root.classList.add("hj-motion-ready");
            });
        });
    })();
</script>

<script>
    (function () {
        var typingNode = document.querySelector(".hj-landing-typing-text[data-text]");
        if (typingNode) {
            var fullText = typingNode.getAttribute("data-text") || "";
            var prefersReduced = false;
            try {
                prefersReduced = !!(window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches);
            } catch (e) {
                prefersReduced = false;
            }

            if (prefersReduced) {
                typingNode.textContent = fullText;
            } else {
                typingNode.textContent = "";
                var index = 0;
                var delay = 30;
                function tick() {
                    if (index >= fullText.length) {
                        return;
                    }
                    typingNode.textContent += fullText.charAt(index);
                    index += 1;
                    window.setTimeout(tick, delay);
                }
                window.setTimeout(tick, 240);
            }
        }

        var quoteNode = document.querySelector(".hj-hitokoto-text");
        if (quoteNode && window.fetch) {
            var endpoint = "https://hitokoto.mayx.eu.org/";
            var controller = null;
            var timeout = null;
            if (window.AbortController) {
                controller = new AbortController();
                timeout = window.setTimeout(function () {
                    try {
                        controller.abort();
                    } catch (e) {}
                }, 4500);
            }

            window.fetch(endpoint, {
                method: "GET",
                cache: "no-store",
                signal: controller ? controller.signal : undefined
            })
                .then(function (response) {
                    if (!response || !response.ok) {
                        throw new Error("bad response");
                    }
                    return response.json();
                })
                .then(function (data) {
                    var text = data && data.hitokoto ? String(data.hitokoto) : "";
                    quoteNode.textContent = text || "\u2026";
                })
                .catch(function () {
                    quoteNode.textContent = "\u2026";
                })
                .finally(function () {
                    if (timeout) {
                        window.clearTimeout(timeout);
                    }
                });
        }

        var scrollBtn = document.querySelector(".hj-scroll-down");
        if (scrollBtn) {
            scrollBtn.addEventListener("click", function () {
                var target =
                    document.querySelector("#hj-recent") ||
                    document.querySelector(".hj-recent") ||
                    document.querySelector("#hj-footer") ||
                    document.querySelector(".hj-footer");
                if (target && target.scrollIntoView) {
                    target.scrollIntoView({ behavior: "smooth", block: "start" });
                }
            });
        }
    })();
</script>

<script>
    (function () {
        var fab = document.querySelector(".hj-fab");
        if (!fab) {
            return;
        }

        var topBtn = fab.querySelector(".hj-fab-top");
        var commentBtn = fab.querySelector(".hj-fab-comment");
        var tocBtn = fab.querySelector(".hj-fab-toc");
        var tocBackdrop = document.querySelector("[data-hj-mobile-toc-backdrop]");
        var tocCloseBtn = document.querySelector(".hj-article-toc-close");
        var tocOpenClass = "hj-mobile-toc-open";
        var wideMql = null;
        try {
            wideMql = window.matchMedia ? window.matchMedia("(min-width: 1100px)") : null;
        } catch (e) {
            wideMql = null;
        }
        var raf = window.requestAnimationFrame
            ? window.requestAnimationFrame.bind(window)
            : function (callback) {
                return window.setTimeout(callback, 16);
            };

        function isWideScreen() {
            return !!(wideMql && wideMql.matches);
        }

        function findTocLayout() {
            return document.querySelector(".hj-article-layout.is-has-toc");
        }

        function findCommentTarget() {
            return (
                document.querySelector("[data-hj-comment-respond]") ||
                document.querySelector("#respond") ||
                document.querySelector("#comments") ||
                document.querySelector(".respond") ||
                document.querySelector(".comment-list")
            );
        }

        function readScrollState() {
            var doc = document.documentElement;
            var body = document.body;
            var top = 0;
            var height = 0;
            var view = 0;

            if (doc) {
                top = doc.scrollTop || 0;
                height = doc.scrollHeight || 0;
                view = doc.clientHeight || 0;
            }
            if (!height && body) {
                height = body.scrollHeight || 0;
            }
            if (!top && body) {
                top = body.scrollTop || 0;
            }
            if (!view) {
                view = window.innerHeight || 0;
            }

            var max = Math.max(1, height - view);
            var p = top / max;
            if (p < 0) p = 0;
            if (p > 1) p = 1;

            return { top: top, progress: p };
        }

        function updateTopProgress() {
            if (!topBtn) {
                return;
            }
            var state = readScrollState();
            var ring = topBtn.querySelector(".hj-fab-top-ring-fg");
            if (!ring) {
                return;
            }
            var progress = state.progress;
            var r = parseFloat(ring.getAttribute("r") || "0");
            if (!isFinite(r) || r <= 0) {
                r = 18.5;
            }
            var c = Math.PI * 2 * r;
            ring.style.strokeDasharray = c.toFixed(2) + " " + c.toFixed(2);
                ring.style.strokeDashoffset = (c * (1 - progress)).toFixed(2);
        }

        function isMobileTocOpen() {
            var root = document.documentElement;
            return !!(root && root.classList.contains(tocOpenClass));
        }

        function closeMobileToc() {
            var root = document.documentElement;
            if (root) {
                root.classList.remove(tocOpenClass);
            }
            if (tocBtn) {
                tocBtn.setAttribute("aria-expanded", "false");
            }
            if (tocBackdrop) {
                tocBackdrop.setAttribute("aria-hidden", "true");
            }
        }

        if (tocCloseBtn) {
            tocCloseBtn.addEventListener("click", function (e) {
                if (e && e.preventDefault) {
                    e.preventDefault();
                }
                closeMobileToc();
            });
        }

        function openMobileToc() {
            if (!tocBtn || !findTocLayout() || isWideScreen()) {
                return;
            }
            var root = document.documentElement;
            if (root) {
                root.classList.add(tocOpenClass);
            }
            tocBtn.setAttribute("aria-expanded", "true");
            if (tocBackdrop) {
                tocBackdrop.setAttribute("aria-hidden", "false");
            }
        }

        function toggleMobileToc() {
            if (isMobileTocOpen()) {
                closeMobileToc();
            } else {
                openMobileToc();
            }
        }

        function updateMobileTocAvailability() {
            if (!tocBtn) {
                return;
            }
            var layout = findTocLayout();
            var show = !!layout && !isWideScreen();
            if (show) {
                fab.classList.add("is-has-mobile-toc");
            } else {
                fab.classList.remove("is-has-mobile-toc");
                closeMobileToc();
            }
        }

        if (commentBtn) {
            var target = findCommentTarget();
            if (!target) {
                commentBtn.style.display = "none";
            } else {
                commentBtn.addEventListener("click", function () {
                    var t = findCommentTarget();
                    if (t && t.scrollIntoView) {
                        t.scrollIntoView({ behavior: "smooth", block: "start" });
                    }
                });
            }
        }

        if (topBtn) {
            topBtn.addEventListener("click", function () {
                if (window.scrollTo) {
                    window.scrollTo({ top: 0, behavior: "smooth" });
                } else {
                    document.documentElement.scrollTop = 0;
                    document.body.scrollTop = 0;
                }
            });
        }

        if (tocBtn) {
            updateMobileTocAvailability();

            tocBtn.addEventListener("click", function () {
                updateMobileTocAvailability();
                toggleMobileToc();
            });

            if (tocBackdrop) {
                tocBackdrop.addEventListener("click", function () {
                    closeMobileToc();
                });
            }

            window.addEventListener("keydown", function (e) {
                if (!isMobileTocOpen()) {
                    return;
                }
                var key = e && (e.key || e.code);
                if (key === "Escape" || key === "Esc") {
                    closeMobileToc();
                }
            });

            document.addEventListener("click", function (e) {
                if (!isMobileTocOpen()) {
                    return;
                }
                var t = e ? e.target : null;
                if (!t || !t.closest) {
                    return;
                }
                var link = t.closest("a.hj-article-toc-link");
                if (link) {
                    closeMobileToc();
                }
            });

            window.addEventListener("resize", function () {
                updateMobileTocAvailability();
            });

            if (wideMql && wideMql.addEventListener) {
                wideMql.addEventListener("change", updateMobileTocAvailability);
            } else if (wideMql && wideMql.addListener) {
                wideMql.addListener(updateMobileTocAvailability);
            }
        }

        var ticking = false;
        function requestTick() {
            if (ticking) {
                return;
            }
            ticking = true;
            raf(function () {
                ticking = false;
                updateTopProgress();
            });
        }

        updateTopProgress();
        try {
            window.addEventListener("scroll", requestTick, { passive: true });
        } catch (e) {
            window.addEventListener("scroll", requestTick);
        }
        window.addEventListener("resize", requestTick);
    })();
</script>

<script>
    (function () {
        var nav = document.querySelector(".hj-nav-desktop");
        if (!nav) {
            return;
        }

        var floatIcon = nav.querySelector(".hj-nav-float-icon");
        var links = Array.prototype.slice.call(nav.querySelectorAll(".hj-nav-link[data-nav-key]"));
        if (!floatIcon || links.length === 0) {
            return;
        }

        var storageKey = "hansjack_nav_last_key";
        var activeLink = nav.querySelector(".hj-nav-link.is-active") || links[0];
        var storageAvailable = false;
        try {
            storageAvailable = typeof window.localStorage !== "undefined";
        } catch (e) {
            storageAvailable = false;
        }

        function getIconTemplate(link) {
            return link ? link.querySelector(".hj-nav-icon-template") : null;
        }

        function setFloatIcon(link) {
            var template = getIconTemplate(link);
            if (!template || !template.innerHTML.trim()) {
                floatIcon.innerHTML = "";
                floatIcon.style.opacity = "0";
                return false;
            }

            floatIcon.innerHTML = template.innerHTML;
            floatIcon.style.opacity = "1";
            return true;
        }

        function placeFloatIcon(link) {
            if (!link || !setFloatIcon(link)) {
                return;
            }

            var navRect = nav.getBoundingClientRect();
            var linkRect = link.getBoundingClientRect();
            var textNode = link.querySelector(".hj-nav-link-text");
            var textRect = textNode ? textNode.getBoundingClientRect() : null;
            var iconSize = floatIcon.offsetWidth || 16;
            var iconGap = 3;
            var left = linkRect.left - navRect.left + 7;
            if (textRect) {
                left = textRect.left - navRect.left - iconSize - iconGap;
            }
            var top = linkRect.top - navRect.top + (linkRect.height - iconSize) / 2;

            floatIcon.style.transform = "translate(" + Math.round(left) + "px," + Math.round(top) + "px)";
        }

        var currentKey = activeLink.getAttribute("data-nav-key") || "";
        placeFloatIcon(activeLink);

        if (storageAvailable && currentKey) {
            window.localStorage.setItem(storageKey, currentKey);
        }

        links.forEach(function (link) {
            link.addEventListener("click", function () {
                var key = link.getAttribute("data-nav-key");
                if (storageAvailable && key) {
                    window.localStorage.setItem(storageKey, key);
                }
            });

            link.addEventListener("mouseenter", function () {
                placeFloatIcon(link);
            });

            link.addEventListener("focus", function () {
                placeFloatIcon(link);
            });
        });

        function updateGlowPosition(event) {
            if (!event) {
                return;
            }

            var rect = nav.getBoundingClientRect();
            var x = event.clientX - rect.left;
            var y = event.clientY - rect.top;
            nav.style.setProperty("--hj-nav-glow-x", Math.round(x) + "px");
            nav.style.setProperty("--hj-nav-glow-y", Math.round(y) + "px");
        }

        nav.addEventListener("mouseenter", function (event) {
            nav.classList.add("is-hovering");
            updateGlowPosition(event);
        });

        nav.addEventListener("mousemove", function (event) {
            updateGlowPosition(event);
        });

        nav.addEventListener("mouseleave", function () {
            nav.classList.remove("is-hovering");
            nav.style.removeProperty("--hj-nav-glow-x");
            nav.style.removeProperty("--hj-nav-glow-y");
            placeFloatIcon(activeLink);
        });

        nav.addEventListener("focusout", function (event) {
            var nextTarget = event ? event.relatedTarget : null;
            if (!nextTarget || !nav.contains(nextTarget)) {
                placeFloatIcon(activeLink);
            }
        });

        var resizeTimer = null;
        window.addEventListener("resize", function () {
            if (resizeTimer) {
                window.clearTimeout(resizeTimer);
            }

            resizeTimer = window.setTimeout(function () {
                placeFloatIcon(activeLink);
            }, 80);
        });
    })();
</script>

<script>
    (function () {
        var root = document.documentElement;
        var toggle = document.querySelector(".hj-nav-toggle");
        var mobileNav = document.querySelector(".hj-mobile-nav");
        if (!root || !toggle || !mobileNav) {
            return;
        }

        var panel = mobileNav.querySelector(".hj-mobile-nav-panel");
        var backdrop = mobileNav.querySelector(".hj-mobile-nav-backdrop");
        if (!panel || !backdrop) {
            return;
        }

        var openClass = "is-open";
        var rootOpenClass = "hj-mobile-nav-open";

        function closeOtherHeaderMenus() {
            var pagesMenu = document.querySelector(".hj-pages-menu");
            var userMenu = document.querySelector(".hj-user-menu");

            if (pagesMenu) {
                pagesMenu.classList.remove("is-open");
                var pagesTrigger = pagesMenu.querySelector(".hj-pages-trigger");
                if (pagesTrigger) {
                    pagesTrigger.setAttribute("aria-expanded", "false");
                }
            }

            if (userMenu) {
                userMenu.classList.remove("is-open");
                var userTrigger = userMenu.querySelector(".hj-user-trigger");
                if (userTrigger) {
                    userTrigger.setAttribute("aria-expanded", "false");
                }
            }

            root.classList.remove("hj-header-dropdown-open");
        }

        function openMenu() {
            closeOtherHeaderMenus();
            mobileNav.classList.add(openClass);
            toggle.setAttribute("aria-expanded", "true");
            mobileNav.setAttribute("aria-hidden", "false");
            root.classList.add(rootOpenClass);
        }

        function closeMenu() {
            mobileNav.classList.remove(openClass);
            toggle.setAttribute("aria-expanded", "false");
            mobileNav.setAttribute("aria-hidden", "true");
            root.classList.remove(rootOpenClass);
        }

        toggle.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (mobileNav.classList.contains(openClass)) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        backdrop.addEventListener("click", function () {
            closeMenu();
        });

        document.addEventListener("keydown", function (event) {
            if (!event) {
                return;
            }
            if (event.key === "Escape" && mobileNav.classList.contains(openClass)) {
                closeMenu();
            }
        });

        panel.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                closeMenu();
            });
        });

        var themeBtn = panel.querySelector("[data-hj-theme-toggle]");
        if (themeBtn) {
            themeBtn.addEventListener("click", function (event) {
                event.preventDefault();
                var themeToggle = document.querySelector(".hj-theme-toggle");
                if (themeToggle) {
                    themeToggle.click();
                }
            });
        }
    })();
</script>

<script>
    (function () {
        var menu = document.querySelector(".hj-pages-menu");
        if (!menu) {
            return;
        }

        var trigger = menu.querySelector(".hj-pages-trigger");
        var dropdown = menu.querySelector(".hj-pages-dropdown");
        if (!trigger || !dropdown) {
            return;
        }

        var openClass = "is-open";
        var margin = 8;  // px from viewport edges
        var raf = window.requestAnimationFrame
            ? window.requestAnimationFrame.bind(window)
            : function (callback) {
                return window.setTimeout(callback, 16);
            };
        function syncHeaderDropdownLock() {
            var pagesMenu = document.querySelector(".hj-pages-menu");
            var userMenu = document.querySelector(".hj-user-menu");
            var hasOpen = false;

            if (pagesMenu && pagesMenu.classList.contains("is-open")) {
                hasOpen = true;
            }
            if (userMenu && userMenu.classList.contains("is-open")) {
                hasOpen = true;
            }

            document.documentElement.classList.toggle("hj-header-dropdown-open", hasOpen);
        }

        function getGapPx() {
            try {
                var raw = window
                    .getComputedStyle(document.documentElement)
                    .getPropertyValue("--hj-dropdown-gap");
                var px = parseFloat(raw) || 0;
                if (px > 0) {
                    return Math.max(4, Math.min(28, Math.round(px)));
                }
            } catch (e) {
                // Ignore.
            }

            // Fallback: roughly one-character height.
            var fontSize = 0;
            try {
                fontSize = parseFloat(window.getComputedStyle(trigger).fontSize) || 0;
            } catch (e) {
                fontSize = 0;
            }

            var gap = Math.round(fontSize || 14);
            return Math.max(8, Math.min(28, gap));
        }

        function positionDropdown() {
            var triggerRect = trigger.getBoundingClientRect();
            if (!triggerRect) {
                return;
            }

            var dropdownRect = dropdown.getBoundingClientRect();
            var width = dropdownRect.width || dropdown.offsetWidth || 0;
            var viewportWidth = document.documentElement.clientWidth || window.innerWidth || 0;
            if (viewportWidth <= 0) {
                return;
            }

            var centerX = triggerRect.left + triggerRect.width / 2;
            var left = centerX;
            if (width > 0) {
                var half = width / 2;
                if (left - half < margin) {
                    left = margin + half;
                } else if (left + half > viewportWidth - margin) {
                    left = viewportWidth - margin - half;
                }
            }

            dropdown.style.position = "fixed";
            dropdown.style.left = Math.round(left) + "px";
            dropdown.style.top = Math.round(triggerRect.bottom + getGapPx()) + "px";
            dropdown.style.right = "auto";
        }

        function openMenu() {
            // Keep only one dropdown open at a time.
            var userMenu = document.querySelector(".hj-user-menu");
            if (userMenu) {
                userMenu.classList.remove("is-open");
                var userTrigger = userMenu.querySelector(".hj-user-trigger");
                if (userTrigger) {
                    userTrigger.setAttribute("aria-expanded", "false");
                }
            }

            menu.classList.add(openClass);
            trigger.setAttribute("aria-expanded", "true");
            syncHeaderDropdownLock();
            positionDropdown();
        }

        function closeMenu() {
            menu.classList.remove(openClass);
            trigger.setAttribute("aria-expanded", "false");
            syncHeaderDropdownLock();
        }

        syncHeaderDropdownLock();

        trigger.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (menu.classList.contains(openClass)) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        menu.addEventListener("mouseenter", function () {
            raf(positionDropdown);
        });

        menu.addEventListener("focusin", function () {
            raf(positionDropdown);
        });

        document.addEventListener("click", function (event) {
            var target = event ? event.target : null;
            if (target && menu.contains(target)) {
                return;
            }
            closeMenu();
        });

        document.addEventListener("keydown", function (event) {
            if (!event) {
                return;
            }
            if (event.key === "Escape") {
                closeMenu();
            }
        });

        dropdown.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                closeMenu();
            });
        });

        var resizeTimer = null;
        function scheduleReposition() {
            var shouldReposition = menu.classList.contains(openClass);
            try {
                shouldReposition = shouldReposition || menu.matches(":hover") || menu.matches(":focus-within");
            } catch (e) {
                // Ignore selector support issues.
            }

            if (!shouldReposition) {
                return;
            }

            if (resizeTimer) {
                window.clearTimeout(resizeTimer);
            }
            resizeTimer = window.setTimeout(function () {
                positionDropdown();
            }, 60);
        }

        window.addEventListener("resize", scheduleReposition);
        window.addEventListener("scroll", scheduleReposition, true);
    })();
</script>

<script>
    (function () {
        var menu = document.querySelector(".hj-user-menu");
        if (!menu) {
            return;
        }

        var trigger = menu.querySelector(".hj-user-trigger");
        var dropdown = menu.querySelector(".hj-user-dropdown");
        if (!trigger || !dropdown) {
            return;
        }

        var openClass = "is-open";
        var margin = 8;  // px from viewport edges
        var raf = window.requestAnimationFrame
            ? window.requestAnimationFrame.bind(window)
            : function (callback) {
                return window.setTimeout(callback, 16);
            };
        function syncHeaderDropdownLock() {
            var pagesMenu = document.querySelector(".hj-pages-menu");
            var userMenu = document.querySelector(".hj-user-menu");
            var hasOpen = false;

            if (pagesMenu && pagesMenu.classList.contains("is-open")) {
                hasOpen = true;
            }
            if (userMenu && userMenu.classList.contains("is-open")) {
                hasOpen = true;
            }

            document.documentElement.classList.toggle("hj-header-dropdown-open", hasOpen);
        }

        function getGapPx() {
            try {
                var raw = window
                    .getComputedStyle(document.documentElement)
                    .getPropertyValue("--hj-dropdown-gap");
                var px = parseFloat(raw) || 0;
                if (px > 0) {
                    return Math.max(4, Math.min(28, Math.round(px)));
                }
            } catch (e) {
                // Ignore.
            }

            // Fallback: roughly one-character height.
            var fontSize = 0;
            try {
                fontSize = parseFloat(window.getComputedStyle(trigger).fontSize) || 0;
            } catch (e) {
                fontSize = 0;
            }

            var gap = Math.round(fontSize || 14);
            return Math.max(8, Math.min(28, gap));
        }

        function positionDropdown() {
            var triggerRect = trigger.getBoundingClientRect();
            if (!triggerRect) {
                return;
            }

            var dropdownRect = dropdown.getBoundingClientRect();
            var width = dropdownRect.width || dropdown.offsetWidth || 0;
            var viewportWidth = document.documentElement.clientWidth || window.innerWidth || 0;
            if (viewportWidth <= 0) {
                return;
            }

            var centerX = triggerRect.left + triggerRect.width / 2;
            var left = centerX;
            if (width > 0) {
                var half = width / 2;
                if (left - half < margin) {
                    left = margin + half;
                } else if (left + half > viewportWidth - margin) {
                    left = viewportWidth - margin - half;
                }
            }

            // Fixed positioning avoids being clipped by narrow layout containers.
            dropdown.style.position = "fixed";
            dropdown.style.left = Math.round(left) + "px";
            dropdown.style.top = Math.round(triggerRect.bottom + getGapPx()) + "px";
            dropdown.style.right = "auto";
        }

        function openMenu() {
            // Keep only one dropdown open at a time.
            var pagesMenu = document.querySelector(".hj-pages-menu");
            if (pagesMenu) {
                pagesMenu.classList.remove("is-open");
                var pagesTrigger = pagesMenu.querySelector(".hj-pages-trigger");
                if (pagesTrigger) {
                    pagesTrigger.setAttribute("aria-expanded", "false");
                }
            }

            menu.classList.add(openClass);
            trigger.setAttribute("aria-expanded", "true");
            syncHeaderDropdownLock();
            positionDropdown();
        }

        function closeMenu() {
            menu.classList.remove(openClass);
            trigger.setAttribute("aria-expanded", "false");
            syncHeaderDropdownLock();
        }

        syncHeaderDropdownLock();

        trigger.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (menu.classList.contains(openClass)) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        menu.addEventListener("mouseenter", function () {
            raf(positionDropdown);
        });

        menu.addEventListener("focusin", function () {
            raf(positionDropdown);
        });

        document.addEventListener("click", function (event) {
            var target = event ? event.target : null;
            if (target && menu.contains(target)) {
                return;
            }
            closeMenu();
        });

        document.addEventListener("keydown", function (event) {
            if (!event) {
                return;
            }
            if (event.key === "Escape") {
                closeMenu();
            }
        });

        dropdown.querySelectorAll("a").forEach(function (link) {
            link.addEventListener("click", function () {
                closeMenu();
            });
        });

        var resizeTimer = null;
        function scheduleReposition() {
            var shouldReposition = menu.classList.contains(openClass);
            try {
                shouldReposition = shouldReposition || menu.matches(":hover") || menu.matches(":focus-within");
            } catch (e) {
                // Ignore selector support issues.
            }

            if (!shouldReposition) {
                return;
            }

            if (resizeTimer) {
                window.clearTimeout(resizeTimer);
            }
            resizeTimer = window.setTimeout(function () {
                positionDropdown();
            }, 60);
        }

        window.addEventListener("resize", scheduleReposition);
        window.addEventListener("scroll", scheduleReposition, true);
    })();
</script>

<script>
    (function () {
        var root = document.documentElement;
        var toggle = document.querySelector(".hj-theme-toggle");
        var curtain = document.querySelector(".hj-theme-curtain");
        if (!root || !toggle) {
            return;
        }

        var storageKey = "hansjack_theme_mode";
        var storageAvailable = false;
        try {
            storageAvailable = typeof window.localStorage !== "undefined";
        } catch (e) {
            storageAvailable = false;
        }

        var media = window.matchMedia ? window.matchMedia("(prefers-color-scheme: dark)") : null;
        var raf = window.requestAnimationFrame
            ? window.requestAnimationFrame.bind(window)
            : function (callback) {
                return window.setTimeout(callback, 16);
            };
        var transitionDuration = 520;
        var isSwitching = false;

        function prefersReducedMotion() {
            if (!window.matchMedia) {
                return false;
            }

            return window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        }

        function getStoredTheme() {
            if (!storageAvailable) {
                return "";
            }

            var saved = window.localStorage.getItem(storageKey);
            return saved === "light" || saved === "dark" ? saved : "";
        }

        function setToggleText(mode) {
            var label = mode === "dark" ? "切换到日间模式" : "切换到夜间模式";
            toggle.setAttribute("aria-label", label);
            toggle.setAttribute("title", label);
            toggle.setAttribute("aria-pressed", mode === "dark" ? "true" : "false");
        }

        function writeCookie(value) {
            try {
                var maxAge = 60 * 60 * 24 * 365;
                document.cookie =
                    storageKey +
                    "=" +
                    encodeURIComponent(value) +
                    "; path=/; max-age=" +
                    maxAge +
                    "; samesite=lax";
            } catch (e) {}
        }

        function applyTheme(mode, persist) {
            root.classList.remove("hj-theme-light", "hj-theme-dark");
            if (mode === "dark") {
                root.classList.add("hj-theme-dark");
            } else {
                root.classList.add("hj-theme-light");
            }

            setToggleText(mode);

            if (persist) {
                writeCookie(mode);
                if (storageAvailable) {
                    window.localStorage.setItem(storageKey, mode);
                }
            }
        }

        function readThemeVisual(mode) {
            var styles = window.getComputedStyle(root);
            var colorVar = mode === "dark" ? "--hj-night-bg" : "--hj-day-bg";
            var imageVar = mode === "dark" ? "--hj-night-bg-image" : "--hj-day-bg-image";
            var color = (styles.getPropertyValue(colorVar) || "").trim();
            var image = (styles.getPropertyValue(imageVar) || "").trim();

            return {
                color: color || (mode === "dark" ? "#111111" : "#ffffff"),
                image: image || "none"
            };
        }

        function setCurtainSplit(fromMode, toMode) {
            if (!curtain) {
                return;
            }

            var fromVisual = readThemeVisual(fromMode);
            var toVisual = readThemeVisual(toMode);
            var topVisual = toMode === "dark" ? toVisual : fromVisual;
            var bottomVisual = toMode === "dark" ? fromVisual : toVisual;

            curtain.classList.remove("is-dark", "is-light");
            curtain.classList.add(toMode === "dark" ? "is-dark" : "is-light");
            curtain.style.setProperty("--hj-curtain-top-color", topVisual.color);
            curtain.style.setProperty("--hj-curtain-top-image", topVisual.image);
            curtain.style.setProperty("--hj-curtain-bottom-color", bottomVisual.color);
            curtain.style.setProperty("--hj-curtain-bottom-image", bottomVisual.image);
        }

        function runCurtainTransition(fromMode, toMode, onCovered, onFinished) {
            if (!curtain || prefersReducedMotion()) {
                onCovered();
                if (typeof onFinished === "function") {
                    onFinished();
                }
                return;
            }

            var enterFrom = toMode === "dark" ? -105 : 105;
            var leaveTo = toMode === "dark" ? 105 : -105;

            setCurtainSplit(fromMode, toMode);
            curtain.style.transition = "none";
            curtain.style.opacity = "1";
            curtain.style.transform = "translate3d(0, " + enterFrom + "%, 0)";
            curtain.getBoundingClientRect();

            raf(function () {
                curtain.style.transition = "transform " + transitionDuration + "ms cubic-bezier(0.22, 1, 0.36, 1)";
                curtain.style.transform = "translate3d(0, 0, 0)";

                window.setTimeout(function () {
                    onCovered();

                    curtain.style.transition = "transform " + transitionDuration + "ms cubic-bezier(0.65, 0, 0.35, 1)";
                    curtain.style.transform = "translate3d(0, " + leaveTo + "%, 0)";

                    window.setTimeout(function () {
                        curtain.style.opacity = "0";
                        curtain.style.transition = "none";

                        if (typeof onFinished === "function") {
                            onFinished();
                        }
                    }, transitionDuration);
                }, transitionDuration);
            });
        }

        function runViewTransition(toMode, onFinished) {
            if (prefersReducedMotion() || typeof document.startViewTransition !== "function") {
                return false;
            }

            root.style.setProperty("--hj-vt-duration", transitionDuration + "ms");
            if (toMode === "dark") {
                root.style.setProperty("--hj-vt-inset-from", "0 0 100% 0");
            } else {
                root.style.setProperty("--hj-vt-inset-from", "100% 0 0 0");
            }
            root.style.setProperty("--hj-vt-inset-to", "0 0 0 0");

            var transition = document.startViewTransition(function () {
                applyTheme(toMode, true);
            });

            transition.finished
                .catch(function () {})
                .finally(function () {
                    root.style.removeProperty("--hj-vt-duration");
                    root.style.removeProperty("--hj-vt-inset-from");
                    root.style.removeProperty("--hj-vt-inset-to");

                    if (typeof onFinished === "function") {
                        onFinished();
                    }
                });

            return true;
        }

        var mode = getStoredTheme();
        if (!mode) {
            mode = media && media.matches ? "dark" : "light";
        }
        applyTheme(mode, false);

        toggle.addEventListener("click", function () {
            if (isSwitching) {
                return;
            }

            var fromMode = root.classList.contains("hj-theme-dark") ? "dark" : "light";
            mode = fromMode === "dark" ? "light" : "dark";
            isSwitching = true;

            if (runViewTransition(mode, function () {
                isSwitching = false;
            })) {
                return;
            }

            runCurtainTransition(fromMode, mode, function () {
                applyTheme(mode, true);
            }, function () {
                isSwitching = false;
            });
        });

        if (media) {
            var handleSystemThemeChange = function (event) {
                if (getStoredTheme() || isSwitching) {
                    return;
                }

                applyTheme(event.matches ? "dark" : "light", false);
            };

            if (typeof media.addEventListener === "function") {
                media.addEventListener("change", handleSystemThemeChange);
            } else if (typeof media.addListener === "function") {
                media.addListener(handleSystemThemeChange);
            }
        }
    })();
</script>

<script>
    (function () {
        var body = document.body;
        if (!body) {
            return;
        }

        var isPosts = body.classList.contains("hj-page-posts");
        var isNotes = body.classList.contains("hj-page-notes");
        if (!isPosts && !isNotes) {
            return;
        }

        var pager = body.querySelector(".hj-posts-pager.page-navigator");
        if (!pager) {
            return;
        }

        var host = null;
        if (typeof pager.closest === "function") {
            host = pager.closest(".hj-posts-main");
        }
        if (!host) {
            host = pager.parentElement || body;
        }

        var spacer = document.createElement("div");
        spacer.className = "hj-posts-pager-spacer";
        spacer.style.height = "0px";
        spacer.setAttribute("aria-hidden", "true");
        if (!pager.parentNode) {
            return;
        }
        pager.parentNode.insertBefore(spacer, pager);

        var affixClass = "is-affixed";
        var naturalMarginTop = 0;
        var naturalMarginBottom = 0;
        var raf = window.requestAnimationFrame
            ? window.requestAnimationFrame.bind(window)
            : function (callback) {
                return window.setTimeout(callback, 16);
            };

        function readNaturalMargins() {
            var styles = null;
            try {
                styles = window.getComputedStyle(pager);
            } catch (e) {
                styles = null;
            }

            if (!styles) {
                naturalMarginTop = 0;
                naturalMarginBottom = 0;
                return;
            }

            naturalMarginTop = parseFloat(styles.marginTop) || 0;
            naturalMarginBottom = parseFloat(styles.marginBottom) || 0;
        }

        readNaturalMargins();

        function setSpacerHeight(px) {
            var h = Math.max(0, Math.ceil(px || 0));
            spacer.style.height = h ? h + "px" : "0px";
        }

        function applyFixedSizing() {
            var rect = null;
            try {
                rect = host.getBoundingClientRect();
            } catch (e) {
                rect = null;
            }

            if (!rect || !rect.width) {
                pager.style.left = "0px";
                pager.style.width = "100%";
                return;
            }

            pager.style.left = Math.round(rect.left) + "px";
            pager.style.width = Math.round(rect.width) + "px";
        }

        function clearFixedSizing() {
            pager.style.left = "";
            pager.style.width = "";
        }

        function update() {
            var viewportH = window.innerHeight || document.documentElement.clientHeight || 0;
            if (!viewportH) {
                return;
            }

            var pagerRect = pager.getBoundingClientRect();
            var pagerH = pagerRect ? pagerRect.height : 0;
            if (!pagerH || pagerH <= 0) {
                pagerH = pager.offsetHeight || 0;
            }

            var spacerRect = spacer.getBoundingClientRect();
            var threshold = viewportH - pagerH;
            var shouldAffix = spacerRect && typeof spacerRect.top === "number"
                ? spacerRect.top + naturalMarginTop > threshold
                : false;

            var isAffixed = pager.classList.contains(affixClass);
            if (shouldAffix) {
                if (!isAffixed) {
                    pager.classList.add(affixClass);
                }

                // Height can change after affixing (safe-area padding), so re-measure.
                pagerRect = pager.getBoundingClientRect();
                pagerH = pagerRect ? pagerRect.height : (pager.offsetHeight || pagerH);

                setSpacerHeight(pagerH + naturalMarginTop + naturalMarginBottom);
                applyFixedSizing();
            } else {
                if (isAffixed) {
                    pager.classList.remove(affixClass);
                    clearFixedSizing();
                }
                setSpacerHeight(0);
            }
        }

        var ticking = false;
        function requestUpdate() {
            if (ticking) {
                return;
            }
            ticking = true;
            raf(function () {
                ticking = false;
                update();
            });
        }

        window.addEventListener("scroll", requestUpdate, { passive: true });
        window.addEventListener("resize", function () {
            readNaturalMargins();
            requestUpdate();
        });

        requestUpdate();
    })();
</script>

<script> 
    (function () { 
        var comments = document.querySelector(".hj-comments"); 
        if (!comments) { 
            return; 
        } 
 
        function copyText(text) { 
            if (!text) { 
                return Promise.reject(new Error("no text")); 
            } 
 
            if (navigator && navigator.clipboard && window && window.isSecureContext) { 
                return navigator.clipboard.writeText(text); 
            } 
 
            return new Promise(function (resolve, reject) { 
                try { 
                    var ta = document.createElement("textarea"); 
                    ta.value = text; 
                    ta.setAttribute("readonly", ""); 
                    ta.style.position = "fixed"; 
                    ta.style.top = "-9999px"; 
                    ta.style.left = "-9999px"; 
                    document.body.appendChild(ta); 
                    ta.focus(); 
                    ta.select(); 
                    ta.setSelectionRange(0, ta.value.length); 
                    var ok = false; 
                    try { 
                        ok = document.execCommand("copy"); 
                    } catch (e) {} 
                    document.body.removeChild(ta); 
                    if (ok) { 
                        resolve(); 
                    } else { 
                        reject(new Error("copy failed")); 
                    } 
                } catch (e) { 
                    reject(e); 
                } 
            }); 
        } 
 
        (function setupCommentShare() { 
            var buttons = Array.prototype.slice.call(comments.querySelectorAll("[data-hj-comment-share]"));  
            if (!buttons || buttons.length === 0) {  
                return;  
            }  
 
            buttons.forEach(function (btn) { 
                btn.addEventListener("click", function (e) { 
                    if (e && e.preventDefault) { 
                        e.preventDefault(); 
                    } 
 
                    var url = btn.getAttribute("data-hj-comment-share") || ""; 
                    if (!url) { 
                        return; 
                    } 
 
                    if (navigator && typeof navigator.share === "function") { 
                        navigator.share({ url: url }).catch(function () {}); 
                        return; 
                    } 
 
                    copyText(url).then(function () { 
                        var prevTitle = btn.getAttribute("title") || ""; 
                        btn.setAttribute("title", "已复制"); 
                        window.setTimeout(function () { 
                            btn.setAttribute("title", prevTitle || "分享"); 
                        }, 1200); 
                    }).catch(function () { 
                        try { 
                            window.prompt("复制链接", url); 
                        } catch (err) {} 
                    }); 
                }); 
            });  
        })();  
 
        (function setupCommentThreadCollapse() { 
            // Collapse very deep threads to keep the tree readable. 
            // Levels: 0 = top-level comment, 1 = reply, ... 
            var collapseFromLevel = 3; 
            var items = Array.prototype.slice.call(comments.querySelectorAll(".comment-body[data-hj-comment-level]")); 
            if (!items || items.length === 0) { 
                return; 
            } 
 
            function findDirectChildrenContainer(item) { 
                if (!item || !item.children) { 
                    return null; 
                } 
                for (var i = 0; i < item.children.length; i++) { 
                    var el = item.children[i]; 
                    if (el && el.classList && el.classList.contains("comment-children")) { 
                        return el; 
                    } 
                } 
                return null; 
            } 
 
            items.forEach(function (item) { 
                var level = parseInt(item.getAttribute("data-hj-comment-level") || "0", 10); 
                if (!isFinite(level) || level < collapseFromLevel) { 
                    return; 
                } 
 
                var children = findDirectChildrenContainer(item); 
                if (!children) { 
                    return; 
                } 
 
                if (item.querySelector("[data-hj-comment-more-toggle]")) { 
                    return; 
                } 
 
                children.hidden = true; 
 
                var btn = document.createElement("button"); 
                btn.type = "button"; 
                btn.className = "hj-comment-more-toggle"; 
                btn.setAttribute("data-hj-comment-more-toggle", ""); 
                btn.setAttribute("aria-expanded", "false"); 
                btn.textContent = "展开更多回复"; 
 
                btn.addEventListener("click", function () { 
                    var expanded = btn.getAttribute("aria-expanded") === "true"; 
                    expanded = !expanded; 
                    btn.setAttribute("aria-expanded", expanded ? "true" : "false"); 
                    children.hidden = !expanded; 
                    btn.textContent = expanded ? "收起回复" : "展开更多回复"; 
                }); 
 
                item.insertBefore(btn, children); 
            }); 
        })(); 
 
        // Typecho's built-in reply script toggles #cancel-comment-reply-link.
        // We removed the button, so make the helper tolerant of null.
        (function patchTypechoCommentVisiable() {
            if (!window || !window.TypechoComment) {
                return;
            }
            var tc = window.TypechoComment;
            if (!tc || typeof tc.visiable !== "function") {
                return;
            }
            var orig = tc.visiable;
            if (orig && orig.__hj_safe_null) {
                return;
            }
            tc.visiable = function (el, show) {
                if (!el) {
                    return;
                }
                try {
                    return orig.call(tc, el, show);
                } catch (e) {}
            };
            tc.visiable.__hj_safe_null = true;
        })();

        var forms = Array.prototype.slice.call(comments.querySelectorAll("[data-hj-comment-form]"));
        if (!forms || forms.length === 0) {
            return;
        }

        var root = document.documentElement;
        var modal = document.querySelector("[data-hj-login-modal]");
        var backdrop = modal ? modal.querySelector("[data-hj-login-backdrop]") : null;
        var panel = modal ? modal.querySelector("[data-hj-login-panel]") : null;
        var loginOpenClass = "hj-login-modal-open";

        var privateMarker = "<!--hj-private-->";
        var fullscreenRootClass = "hj-comment-fullscreen-open";

        function openLogin(saveDraftFn) {
            if (!modal || !root) {
                return;
            }
            if (typeof saveDraftFn === "function") {
                saveDraftFn();
            }
            root.classList.add(loginOpenClass);
            modal.setAttribute("aria-hidden", "false");

            var nameInput = modal.querySelector("#hj-login-name");
            if (nameInput) {
                window.setTimeout(function () {
                    try {
                        nameInput.focus();
                    } catch (e) {}
                }, 0);
            } else if (panel) {
                try {
                    panel.focus();
                } catch (e) {}
            }
        }

        function closeLogin() {
            if (!modal || !root) {
                return;
            }
            root.classList.remove(loginOpenClass);
            modal.setAttribute("aria-hidden", "true");
        }

        if (backdrop) {
            backdrop.addEventListener("click", function () {
                closeLogin();
            });
        }

        window.addEventListener("keydown", function (e) {
            if (!root || !root.classList.contains(loginOpenClass)) {
                return;
            }
            var key = e && (e.key || e.code);
            if (key === "Escape" || key === "Esc") {
                closeLogin();
            }
        });

        function cancelReplyIfAny() {
            if (!window || !window.TypechoComment || typeof window.TypechoComment.cancelReply !== "function") {
                return;
            }
            try {
                window.TypechoComment.cancelReply();
            } catch (e) {}
        }

        function setupComposer(form) {
            if (!form) {
                return;
            }

            var textarea = form.querySelector("textarea[name=\"text\"]");
            var box = form.querySelector("[data-hj-comment-box]");
            if (!textarea || !box) {
                return;
            }

            var role = form.getAttribute("data-hj-comment-role") || "default";
            var isTop = role === "top";
            var requireLogin = form.getAttribute("data-hj-require-login") === "1";

            var privateBtn = form.querySelector("[data-hj-comment-private-toggle]");
            var fullscreenBtn = form.querySelector("[data-hj-comment-fullscreen-toggle]");
            var loginBtn = form.querySelector("[data-hj-open-login]");

            // Auto-grow the composer textarea with content (no manual resize).
            // Reply form is initially hidden; compute min height lazily on first focus.
            var minTextareaH = 0;
            function ensureMinTextareaH() {
                if (minTextareaH) {
                    return;
                }
                var h = textarea.offsetHeight || 0;
                if (h) {
                    minTextareaH = h;
                }
            }

            function autoGrowTextarea() {
                try {
                    if (box && box.classList && box.classList.contains("is-fullscreen")) {
                        return;
                    }
                    ensureMinTextareaH();
                    if (minTextareaH) {
                        textarea.style.height = minTextareaH + "px";
                    } else {
                        textarea.style.height = "auto";
                    }

                    var next = textarea.scrollHeight || 0;
                    if (minTextareaH && next < minTextareaH) {
                        next = minTextareaH;
                    }

                    // Only add a tiny buffer when we actually need to grow, to prevent clipping.
                    if (minTextareaH && next > minTextareaH) {
                        next += 2;
                    }

                    if (next) {
                        textarea.style.height = next + "px";
                    }
                } catch (e) {}
            }

            var draftKey = "hj_comment_draft_" + role + "_" + (location && location.pathname ? location.pathname : "page");
            var privateKey = draftKey + "_private";

            function saveDraft() {
                try {
                    sessionStorage.setItem(draftKey, textarea.value || "");
                } catch (e) {}

                if (privateBtn) {
                    try {
                        sessionStorage.setItem(privateKey, privateBtn.getAttribute("aria-pressed") === "true" ? "1" : "0");
                    } catch (e) {}
                }
            }

            function setPrivateState(isOn) {
                if (!privateBtn) {
                    return;
                }
                privateBtn.setAttribute("aria-pressed", isOn ? "true" : "false");
                box.classList.toggle("is-private", !!isOn);
                try {
                    sessionStorage.setItem(privateKey, isOn ? "1" : "0");
                } catch (e) {}
            }

            function restoreDraft() {
                try {
                    var saved = sessionStorage.getItem(draftKey);
                    if (saved && !textarea.value) {
                        textarea.value = saved;
                    }
                } catch (e) {}

                if (privateBtn) {
                    try {
                        var savedPrivate = sessionStorage.getItem(privateKey);
                        if (savedPrivate === "1" || savedPrivate === "0") {
                            setPrivateState(savedPrivate === "1");
                        }
                    } catch (e) {}
                }
            }

            function clearDraft() {
                try {
                    sessionStorage.removeItem(draftKey);
                } catch (e) {}
                try {
                    sessionStorage.removeItem(privateKey);
                } catch (e) {}
            }

            if (loginBtn) {
                loginBtn.addEventListener("click", function (e) {
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }
                    openLogin(saveDraft);
                });
            }

            if (privateBtn) {
                privateBtn.addEventListener("click", function () {
                    var isOn = privateBtn.getAttribute("aria-pressed") === "true";
                    setPrivateState(!isOn);
                });
            }

            function setFullscreenState(isOn) {
                if (!fullscreenBtn || !box || !root) {
                    return;
                }
                fullscreenBtn.setAttribute("aria-pressed", isOn ? "true" : "false");
                fullscreenBtn.setAttribute("aria-label", isOn ? "收起全屏" : "展开全屏");
                fullscreenBtn.setAttribute("title", isOn ? "收起全屏" : "展开全屏");

                box.classList.toggle("is-fullscreen", !!isOn);
                root.classList.toggle(fullscreenRootClass, !!isOn);

                if (isOn) {
                    textarea.style.height = "";
                    try {
                        textarea.focus();
                    } catch (e) {}
                } else {
                    autoGrowTextarea();
                }
            }

            if (fullscreenBtn) {
                fullscreenBtn.addEventListener("click", function (e) {
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }
                    var isOn = fullscreenBtn.getAttribute("aria-pressed") === "true";
                    setFullscreenState(!isOn);
                });
            }

            restoreDraft();

            textarea.addEventListener("focus", function () {
                // Reply form might be hidden on load; re-measure once visible.
                ensureMinTextareaH();
                autoGrowTextarea();

                if (isTop) {
                    cancelReplyIfAny();
                }
            });

            textarea.addEventListener("input", function () {
                autoGrowTextarea();
                saveDraft();
            });

            autoGrowTextarea();

            form.addEventListener("submit", function (e) {
                var isPrivate = privateBtn && privateBtn.getAttribute("aria-pressed") === "true";

                if (requireLogin) {
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }
                    openLogin(saveDraft);
                    return;
                }

                if (isPrivate) {
                    var value = textarea.value || "";
                    var trimmed = value.replace(/^\\s+/, "");
                    if (trimmed.indexOf(privateMarker) !== 0) {
                        textarea.value = privateMarker + "\n" + value;
                    }
                }

                clearDraft();
            });
        }

        forms.forEach(function (f) {
            setupComposer(f);
        });
    })();
</script>

<script>
    (function () {
        var content = document.querySelector(".hj-article-content");
        if (!content) {
            return;
        }

        var links = Array.prototype.slice.call(content.querySelectorAll("a[href]"));
        if (links.length === 0) {
            return;
        }

        var cache = Object.create(null);

        function loadFavicon(host, callback) {
            var entry = cache[host];
            if (entry) {
                if (entry.state === "ok") {
                    callback(entry.url);
                    return;
                }
                if (entry.state === "bad") {
                    callback("");
                    return;
                }
                entry.cbs.push(callback);
                return;
            }

            var url = "//" + host + "/favicon.ico";
            cache[host] = { state: "pending", url: url, cbs: [callback] };

            var img = new Image();
            img.onload = function () {
                var e = cache[host];
                if (!e) {
                    return;
                }
                e.state = "ok";
                var cbs = e.cbs.slice();
                e.cbs.length = 0;
                for (var i = 0; i < cbs.length; i++) {
                    try {
                        cbs[i](e.url);
                    } catch (err) {}
                }
            };
            img.onerror = function () {
                var e = cache[host];
                if (!e) {
                    return;
                }
                e.state = "bad";
                var cbs = e.cbs.slice();
                e.cbs.length = 0;
                for (var i = 0; i < cbs.length; i++) {
                    try {
                        cbs[i]("");
                    } catch (err) {}
                }
            };
            img.src = url;
        }

        function shouldSkipHref(href) {
            if (!href) {
                return true;
            }

            var lower = href.toLowerCase();
            if (lower.indexOf("mailto:") === 0 || lower.indexOf("tel:") === 0 || lower.indexOf("javascript:") === 0) {
                return true;
            }
            if (href.charAt(0) === "#") {
                return true;
            }
            if (!(lower.indexOf("http://") === 0 || lower.indexOf("https://") === 0)) {
                return true;
            }
            return false;
        }

        links.forEach(function (a) {
            if (!a || (a.classList && a.classList.contains("hj-link-has-favicon"))) {
                return;
            }

            var text = ((a.textContent || "") + "").trim();
            if (!text) {
                return;
            }

            var href = a.getAttribute("href") || "";
            if (shouldSkipHref(href)) {
                return;
            }

            var url;
            try {
                url = new URL(href);
            } catch (e) {
                return;
            }
            if (!url || !url.host) {
                return;
            }

            loadFavicon(url.host, function (faviconUrl) {
                if (!faviconUrl) {
                    return;
                }

                a.style.setProperty("--hj-link-favicon", "url(\"" + faviconUrl.replace(/\"/g, "\\\"") + "\")");
                a.classList.add("hj-link-has-favicon");
            });
        });
    })();
</script>

<?php $this->footer(); ?>
</body>
</html>
