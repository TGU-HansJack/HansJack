/* Theme Footer Global Preload Bundle */

/* block 1 */
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
                root.classList.add("motion-ready");
            });
        });
    })();

/* block 2 */
(function () {
        var welcomeNode = document.querySelector(".landing-welcome");
        if (welcomeNode && welcomeNode.classList) {
            var welcomeText = String(welcomeNode.textContent || "").trim();
            var hasLatin = /[A-Za-z]/.test(welcomeText);
            var hasCjk = /[\u3400-\u9FFF\uF900-\uFAFF]/.test(welcomeText);
            if (hasLatin && !hasCjk) {
                welcomeNode.classList.add("is-english");
            } else {
                welcomeNode.classList.remove("is-english");
            }
        }

        var quoteNode = document.querySelector(".hitokoto-text");
        if (quoteNode && window.fetch) {
            var endpoint = "https://hitokoto.mayx.eu.org/";
            var fallbackQuote = (quoteNode.textContent || "").trim();
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
                    quoteNode.textContent = text || fallbackQuote || "\u2026";
                })
                .catch(function () {
                    quoteNode.textContent = fallbackQuote || "\u2026";
                })
                .finally(function () {
                    if (timeout) {
                        window.clearTimeout(timeout);
                    }
                });
        }

        function readScrollTop() {
            var doc = document.documentElement;
            var body = document.body;
            if (typeof window.pageYOffset === "number") {
                return window.pageYOffset || 0;
            }
            if (doc && typeof doc.scrollTop === "number" && doc.scrollTop) {
                return doc.scrollTop;
            }
            if (body && typeof body.scrollTop === "number" && body.scrollTop) {
                return body.scrollTop;
            }
            return 0;
        }

        function readScrollBehavior() {
            try {
                if (window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
                    return "auto";
                }
            } catch (e) {}
            return "smooth";
        }

        var scrollBtn = document.querySelector(".scroll-down");
        if (scrollBtn) {
            scrollBtn.addEventListener("click", function (e) {
                if (e && e.preventDefault) {
                    e.preventDefault();
                }

                var currentTop = readScrollTop();
                var behavior = readScrollBehavior();
                var recentTarget = document.querySelector("#recent") || document.querySelector(".recent");
                if (recentTarget && recentTarget.getBoundingClientRect) {
                    var recentTop = currentTop;
                    try {
                        recentTop = currentTop + (recentTarget.getBoundingClientRect().top || 0);
                    } catch (err) {
                        recentTop = currentTop;
                    }

                    if (window.scrollTo) {
                        try {
                            window.scrollTo({ top: Math.max(0, Math.round(recentTop)), behavior: behavior });
                            return;
                        } catch (err) {}
                    }
                    if (recentTarget.scrollIntoView) {
                        recentTarget.scrollIntoView({ behavior: behavior, block: "start" });
                    }
                    return;
                }

                // The `recent` section may be disabled; in that case, scroll down about one viewport
                // instead of jumping straight to footer to avoid a noticeable first-frame hitch.
                var footerTarget = document.querySelector("#footer") || document.querySelector(".footer");
                var viewportH = window.innerHeight || (document.documentElement ? document.documentElement.clientHeight : 0) || 0;
                var nextTop = currentTop + Math.max(220, Math.round(viewportH * 0.92));
                if (footerTarget && footerTarget.getBoundingClientRect) {
                    try {
                        var footerTop = currentTop + (footerTarget.getBoundingClientRect().top || 0);
                        if (isFinite(footerTop)) {
                            nextTop = Math.min(nextTop, footerTop);
                        }
                    } catch (err) {}
                }
                nextTop = Math.max(0, Math.round(nextTop));

                if (window.scrollTo) {
                    try {
                        window.scrollTo({ top: nextTop, behavior: behavior });
                        return;
                    } catch (err) {}
                }
                window.scrollTo(0, nextTop);
            });
        }
    })();

/* block 3 */
(function () {
        var fab = document.querySelector(".fab");
        if (!fab) {
            return;
        }

        var topBtn = fab.querySelector(".fab-top");
        var settingsBtn = fab.querySelector(".fab-settings");
        var rewardBtn = fab.querySelector(".fab-reward");
        var commentBtn = fab.querySelector(".fab-comment");
        var tocBtn = fab.querySelector(".fab-toc");
        var settingsPopover = document.querySelector("[data-posts-settings-popover]");
        var settingsPanel = settingsPopover ? settingsPopover.querySelector("[data-posts-settings-panel]") : null;
        var settingsSortModeControl = settingsPopover ? settingsPopover.querySelector("[data-posts-setting-sort-mode]") : null;
        var settingsOrderControl = settingsPopover ? settingsPopover.querySelector("[data-posts-setting-order]") : null;
        var settingsListModeControl = settingsPopover ? settingsPopover.querySelector("[data-posts-setting-list-mode]") : null;
        var rewardBackdrop = document.querySelector("[data-reward-backdrop]");
        var rewardPopover = document.querySelector("[data-reward-popover]");
        var rewardPanel = rewardPopover ? rewardPopover.querySelector("[data-reward-panel]") : null;
        var rewardCloseBtn = rewardPopover ? rewardPopover.querySelector("[data-reward-close]") : null;
        var rewardTabButtons = rewardPopover ? Array.prototype.slice.call(rewardPopover.querySelectorAll("[data-reward-tab]")) : [];
        var rewardPanes = rewardPopover ? Array.prototype.slice.call(rewardPopover.querySelectorAll("[data-reward-pane]")) : [];
        var rewardImages = rewardPopover ? Array.prototype.slice.call(rewardPopover.querySelectorAll("[data-reward-pane] img")) : [];
        var tocBackdrop = document.querySelector("[data-mobile-toc-backdrop]");
        var tocCloseBtn = document.querySelector(".article-toc-close");
        var tocOpenClass = "mobile-toc-open";
        var wideMql = null;
        var narrowMql = null;
        try {
            wideMql = window.matchMedia ? window.matchMedia("(min-width: 1100px)") : null;
            narrowMql = window.matchMedia ? window.matchMedia("(max-width: 980px)") : null;
        } catch (e) {
            wideMql = null;
            narrowMql = null;
        }
        var raf = window.requestAnimationFrame
            ? window.requestAnimationFrame.bind(window)
            : function (callback) {
                return window.setTimeout(callback, 16);
            };

        function isWideScreen() {
            return !!(wideMql && wideMql.matches);
        }

        function isNarrowScreen() {
            if (narrowMql) {
                return !!narrowMql.matches;
            }
            return (window.innerWidth || 0) <= 980;
        }

        function findTocLayout() {
            return document.querySelector(".article-layout.is-has-toc");
        }

        function findCommentTarget() {
            return (
                // Prefer the always-visible top composer; the built-in respond box is hidden
                // until it is moved into a comment body for replying.
                document.querySelector("[data-comment-role=\"top\"]") ||
                document.querySelector("#comments") ||
                document.querySelector("[data-comment-respond]") ||
                document.querySelector("#respond") ||
                document.querySelector(".respond") ||
                document.querySelector(".comment-list")
            );
        }

        var topRing = topBtn ? topBtn.querySelector(".fab-top-ring-fg") : null;
        var topRingCirc = 0;
        var topRingOffsetLast = "";
        var cachedScrollMax = 1;

        if (topRing) {
            var ringRadius = parseFloat(topRing.getAttribute("r") || "0");
            if (!isFinite(ringRadius) || ringRadius <= 0) {
                ringRadius = 18.5;
            }
            topRingCirc = Math.PI * 2 * ringRadius;
            topRing.style.strokeDasharray = topRingCirc.toFixed(2) + " " + topRingCirc.toFixed(2);
        }

        function readScrollTop() {
            var doc = document.documentElement;
            var body = document.body;
            if (typeof window.pageYOffset === "number") {
                return window.pageYOffset || 0;
            }
            if (doc && typeof doc.scrollTop === "number" && doc.scrollTop) {
                return doc.scrollTop;
            }
            if (body && typeof body.scrollTop === "number" && body.scrollTop) {
                return body.scrollTop;
            }
            return 0;
        }

        function recomputeScrollMetrics() {
            var doc = document.documentElement;
            var body = document.body;
            var height = 0;
            var view = 0;

            if (doc) {
                height = doc.scrollHeight || 0;
                view = doc.clientHeight || 0;
            }
            if (!height && body) {
                height = body.scrollHeight || 0;
            }
            if (!view) {
                view = window.innerHeight || 0;
            }

            cachedScrollMax = Math.max(1, height - view);
        }

        function readScrollState() {
            var top = readScrollTop();
            var p = top / cachedScrollMax;
            if (p < 0) p = 0;
            if (p > 1) p = 1;
            return { top: top, progress: p };
        }

        function updateTopProgress() {
            if (!topRing || topRingCirc <= 0) {
                return;
            }
            var state = readScrollState();
            var offset = (topRingCirc * (1 - state.progress)).toFixed(2);
            if (offset === topRingOffsetLast) {
                return;
            }
            topRingOffsetLast = offset;
            topRing.style.strokeDashoffset = offset;
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

        var postsSettingsStorageKey = "posts_list_settings_v1";
        var postsSettingsDefaults = { sortMode: "default", order: "desc", listMode: "compact" };
        var postsSettings = null;

        function normalizePostsSettings(next) {
            var s = next && typeof next === "object" ? next : {};

            var sortMode = String(s.sortMode || postsSettingsDefaults.sortMode);
            if (sortMode !== "default" && sortMode !== "published" && sortMode !== "updated") {
                sortMode = postsSettingsDefaults.sortMode;
            }

            var order = String(s.order || postsSettingsDefaults.order);
            if (order !== "asc" && order !== "desc") {
                order = postsSettingsDefaults.order;
            }

            var listMode = String(s.listMode || postsSettingsDefaults.listMode);
            if (listMode !== "compact" && listMode !== "preview") {
                listMode = postsSettingsDefaults.listMode;
            }

            return { sortMode: sortMode, order: order, listMode: listMode };
        }

        function loadPostsSettings() {
            var raw = "";
            try {
                raw = window.localStorage ? String(localStorage.getItem(postsSettingsStorageKey) || "") : "";
            } catch (e) {
                raw = "";
            }
            if (!raw) {
                return normalizePostsSettings(postsSettingsDefaults);
            }
            try {
                return normalizePostsSettings(JSON.parse(raw));
            } catch (e) {
                return normalizePostsSettings(postsSettingsDefaults);
            }
        }

        function savePostsSettings(next) {
            postsSettings = normalizePostsSettings(next);
            try {
                if (window.localStorage) {
                    localStorage.setItem(postsSettingsStorageKey, JSON.stringify(postsSettings));
                }
            } catch (e) {}
        }

        function getSettingsSegmentedOptions(group) {
            if (!group || !group.querySelectorAll) {
                return [];
            }
            return Array.prototype.slice.call(group.querySelectorAll(".fab-settings-segmented-option[data-value]"));
        }

        function getSettingsSegmentedValue(group) {
            var options = getSettingsSegmentedOptions(group);
            if (!options || options.length === 0) {
                return "";
            }
            var active = null;
            options.some(function (opt) {
                if (opt.getAttribute("aria-checked") === "true" || (opt.classList && opt.classList.contains("is-active"))) {
                    active = opt;
                    return true;
                }
                return false;
            });
            if (!active) {
                active = options[0];
            }
            return String(active.getAttribute("data-value") || "");
        }

        function setSettingsSegmentedValue(group, value) {
            var options = getSettingsSegmentedOptions(group);
            if (!group || !options || options.length === 0) {
                return;
            }

            var targetValue = String(value || "");
            var activeIndex = -1;

            options.forEach(function (opt, idx) {
                var optionValue = String(opt.getAttribute("data-value") || "");
                var isActive = optionValue === targetValue;
                if (isActive) {
                    activeIndex = idx;
                }
                opt.classList.toggle("is-active", isActive);
                opt.setAttribute("aria-checked", isActive ? "true" : "false");
                opt.setAttribute("tabindex", isActive ? "0" : "-1");
            });

            if (activeIndex < 0) {
                activeIndex = 0;
                options.forEach(function (opt, idx) {
                    var isActive = idx === 0;
                    opt.classList.toggle("is-active", isActive);
                    opt.setAttribute("aria-checked", isActive ? "true" : "false");
                    opt.setAttribute("tabindex", isActive ? "0" : "-1");
                });
            }

            var activeOption = options[activeIndex];
            var thumbLeft = 0;
            var thumbWidth = 0;
            if (activeOption && group.getBoundingClientRect && activeOption.getBoundingClientRect) {
                var groupRect = group.getBoundingClientRect();
                var optionRect = activeOption.getBoundingClientRect();
                thumbLeft = optionRect.left - groupRect.left;
                thumbWidth = optionRect.width;
            }
            if (!isFinite(thumbLeft) || thumbLeft < 0) {
                thumbLeft = 0;
            }
            if (!isFinite(thumbWidth) || thumbWidth < 0) {
                thumbWidth = 0;
            }

            group.style.setProperty("--fab-settings-segment-left", thumbLeft.toFixed(2) + "px");
            group.style.setProperty("--fab-settings-segment-width", thumbWidth.toFixed(2) + "px");
            group.setAttribute("data-active-index", String(activeIndex));
            group.setAttribute("data-active-value", String(options[activeIndex].getAttribute("data-value") || ""));
        }

        function bindSettingsSegmentedControl(group, onChange) {
            if (!group || !group.addEventListener || typeof onChange !== "function") {
                return;
            }
            group.addEventListener("click", function (e) {
                var target = e ? e.target : null;
                if (!target || !target.closest) {
                    return;
                }
                var option = target.closest(".fab-settings-segmented-option[data-value]");
                if (!option || !group.contains(option)) {
                    return;
                }
                var value = String(option.getAttribute("data-value") || "");
                if (!value) {
                    return;
                }
                var prev = getSettingsSegmentedValue(group);
                setSettingsSegmentedValue(group, value);
                if (value !== prev) {
                    onChange(value);
                }
            });
        }

        function syncPostsSettingsUI() {
            if (!postsSettings) {
                return;
            }
            if (settingsSortModeControl) {
                setSettingsSegmentedValue(settingsSortModeControl, postsSettings.sortMode);
            }
            if (settingsOrderControl) {
                setSettingsSegmentedValue(settingsOrderControl, postsSettings.order);
            }
            if (settingsListModeControl) {
                setSettingsSegmentedValue(settingsListModeControl, postsSettings.listMode);
            }
        }

        function findPostsLists() {
            return Array.prototype.slice.call(document.querySelectorAll(".posts-list"));
        }

        function ensureOriginalOrder(items) {
            if (!items || items.length === 0) {
                return;
            }
            items.forEach(function (item, idx) {
                if (!item || !item.setAttribute) {
                    return;
                }
                if (!item.hasAttribute("data-post-original-index")) {
                    item.setAttribute("data-post-original-index", String(idx));
                }
            });
        }

        function readIntAttr(el, name) {
            if (!el || !el.getAttribute) {
                return 0;
            }
            var v = parseInt(el.getAttribute(name) || "0", 10);
            return isFinite(v) ? v : 0;
        }

        function applyPostsListSort(list) {
            if (!postsSettings || !list) {
                return;
            }

            var items = Array.prototype.slice.call(list.querySelectorAll(".posts-item"));
            if (!items || items.length === 0) {
                return;
            }

            ensureOriginalOrder(items);

            var sorted = items.slice();
            var mode = postsSettings.sortMode;
            var asc = postsSettings.order === "asc";

            sorted.sort(function (a, b) {
                var aIdx = readIntAttr(a, "data-post-original-index");
                var bIdx = readIntAttr(b, "data-post-original-index");

                if (mode === "default") {
                    // Preserve server order; "asc" just reverses the original list.
                    return asc ? bIdx - aIdx : aIdx - bIdx;
                }

                var aVal = 0;
                var bVal = 0;
                if (mode === "published") {
                    aVal = readIntAttr(a, "data-post-created");
                    bVal = readIntAttr(b, "data-post-created");
                } else if (mode === "updated") {
                    aVal = readIntAttr(a, "data-post-modified") || readIntAttr(a, "data-post-created");
                    bVal = readIntAttr(b, "data-post-modified") || readIntAttr(b, "data-post-created");
                }

                if (aVal === bVal) {
                    return aIdx - bIdx;
                }

                return asc ? aVal - bVal : bVal - aVal;
            });

            var frag = document.createDocumentFragment();
            sorted.forEach(function (item) {
                frag.appendChild(item);
            });
            list.appendChild(frag);
        }

        function ensurePostExcerpt(item) {
            if (!item || !item.querySelector) {
                return;
            }
            var text = "";
            try {
                text = String(item.getAttribute("data-post-excerpt") || "");
            } catch (e) {
                text = "";
            }
            text = text.trim();
            if (!text) {
                return;
            }

            var existing = item.querySelector("[data-posts-excerpt]");
            if (existing) {
                existing.textContent = text;
                return;
            }

            var node = document.createElement("div");
            node.className = "posts-excerpt";
            node.setAttribute("data-posts-excerpt", "true");
            node.textContent = text;

            var left = item.querySelector(".posts-item-left");
            if (!left) {
                item.appendChild(node);
                return;
            }

            var time = left.querySelector(".posts-date");
            if (time && time.parentNode === left) {
                left.insertBefore(node, time);
            } else {
                left.appendChild(node);
            }
        }

        function removePostExcerpt(item) {
            if (!item || !item.querySelectorAll) {
                return;
            }
            var nodes = item.querySelectorAll("[data-posts-excerpt]");
            if (!nodes || nodes.length === 0) {
                return;
            }
            Array.prototype.slice.call(nodes).forEach(function (n) {
                if (n && n.parentNode) {
                    n.parentNode.removeChild(n);
                }
            });
        }

        function applyPostsListMode(list) {
            if (!postsSettings || !list) {
                return;
            }
            var items = Array.prototype.slice.call(list.querySelectorAll(".posts-item"));
            if (!items || items.length === 0) {
                return;
            }

            var isPreview = postsSettings.listMode === "preview";
            items.forEach(function (item) {
                if (isPreview) {
                    ensurePostExcerpt(item);
                } else {
                    removePostExcerpt(item);
                }
            });
        }

        function applyPostsSettingsToAllLists() {
            if (!postsSettings) {
                return;
            }

            var root = document.documentElement;
            if (root && root.classList) {
                root.classList.toggle("posts-preview-mode", postsSettings.listMode === "preview");
            }

            var lists = findPostsLists();
            if (!lists || lists.length === 0) {
                return;
            }

            lists.forEach(function (list) {
                applyPostsListSort(list);
                applyPostsListMode(list);
            });
        }

        function shouldShowSettingsFab() {
            if (!settingsBtn) {
                return false;
            }
            var body = document.body;
            var isPostsPage = !!(body && body.classList && body.classList.contains("page-posts"));
            if (isPostsPage) {
                return true;
            }
            if (!isNarrowScreen()) {
                return false;
            }
            return !!document.querySelector(".posts-list");
        }

        function positionSettingsPopover() {
            if (!settingsPopover || !settingsPanel || !settingsBtn) {
                return;
            }

            var btnRect = settingsBtn.getBoundingClientRect();
            var panelRect = settingsPanel.getBoundingClientRect();
            var gap = 10;
            var viewportPad = 8;

            var left = btnRect.left - gap - panelRect.width;
            if (left < viewportPad) {
                left = viewportPad;
            }

            var top = btnRect.top + btnRect.height / 2 - panelRect.height / 2;
            var maxTop = Math.max(viewportPad, window.innerHeight - panelRect.height - viewportPad);
            if (top < viewportPad) {
                top = viewportPad;
            } else if (top > maxTop) {
                top = maxTop;
            }

            settingsPopover.style.left = left.toFixed(0) + "px";
            settingsPopover.style.top = top.toFixed(0) + "px";
        }

        function isSettingsPopoverOpen() {
            return !!(settingsPopover && settingsPopover.classList && settingsPopover.classList.contains("is-open"));
        }

        var settingsPopoverHideTimer = null;

        function closeSettingsPopover() {
            if (!settingsPopover) {
                return;
            }
            if (settingsPopoverHideTimer) {
                window.clearTimeout(settingsPopoverHideTimer);
                settingsPopoverHideTimer = null;
            }
            settingsPopover.classList.remove("is-open");
            settingsPopover.setAttribute("aria-hidden", "true");
            if (settingsBtn) {
                settingsBtn.setAttribute("aria-expanded", "false");
            }
            settingsPopoverHideTimer = window.setTimeout(function () {
                settingsPopoverHideTimer = null;
                settingsPopover.hidden = true;
                settingsPopover.style.left = "";
                settingsPopover.style.top = "";
            }, 180);
        }

        function openSettingsPopover() {
            if (!settingsPopover || !settingsBtn) {
                return;
            }
            if (settingsPopoverHideTimer) {
                window.clearTimeout(settingsPopoverHideTimer);
                settingsPopoverHideTimer = null;
            }
            settingsPopover.hidden = false;
            settingsPopover.setAttribute("aria-hidden", "false");
            settingsBtn.setAttribute("aria-expanded", "true");
            syncPostsSettingsUI();
            positionSettingsPopover();
            window.setTimeout(function () {
                settingsPopover.classList.add("is-open");
            }, 0);
        }

        function toggleSettingsPopover() {
            if (isSettingsPopoverOpen()) {
                closeSettingsPopover();
            } else {
                openSettingsPopover();
            }
        }

        function updateSettingsFabVisibility() {
            if (!settingsBtn) {
                return;
            }
            var show = shouldShowSettingsFab();
            settingsBtn.style.display = show ? "inline-flex" : "none";
            if (!show) {
                closeSettingsPopover();
            }
        }

        function positionRewardPopover() {
            if (!rewardPopover || !rewardPanel) {
                return;
            }

            var panelRect = rewardPanel.getBoundingClientRect();
            var viewportPad = 8;
            var left = (window.innerWidth - panelRect.width) / 2;
            var top = (window.innerHeight - panelRect.height) / 2;
            var maxLeft = Math.max(viewportPad, window.innerWidth - panelRect.width - viewportPad);
            var maxTop = Math.max(viewportPad, window.innerHeight - panelRect.height - viewportPad);
            if (left < viewportPad) {
                left = viewportPad;
            } else if (left > maxLeft) {
                left = maxLeft;
            }
            if (top < viewportPad) {
                top = viewportPad;
            } else if (top > maxTop) {
                top = maxTop;
            }

            rewardPopover.style.left = left.toFixed(0) + "px";
            rewardPopover.style.top = top.toFixed(0) + "px";
        }

        function isRewardPopoverOpen() {
            return !!(rewardPopover && rewardPopover.classList && rewardPopover.classList.contains("is-open"));
        }

        function getRewardActiveKey() {
            for (var i = 0; i < rewardTabButtons.length; i++) {
                var btn = rewardTabButtons[i];
                if (!btn) {
                    continue;
                }
                var selected = (btn.getAttribute("aria-selected") || "").toLowerCase() === "true";
                if (selected || (btn.classList && btn.classList.contains("is-active"))) {
                    return String(btn.getAttribute("data-reward-tab") || "");
                }
            }

            for (var j = 0; j < rewardPanes.length; j++) {
                var pane = rewardPanes[j];
                if (!pane || !pane.classList) {
                    continue;
                }
                if (pane.classList.contains("is-active")) {
                    return String(pane.getAttribute("data-reward-pane") || "");
                }
            }

            if (rewardPanes.length > 0) {
                return String((rewardPanes[0] && rewardPanes[0].getAttribute("data-reward-pane")) || "");
            }
            return "";
        }

        function setRewardActiveKey(nextKey) {
            var target = String(nextKey || "");
            var availableKeys = [];
            for (var i = 0; i < rewardPanes.length; i++) {
                var pane = rewardPanes[i];
                if (!pane) {
                    continue;
                }
                var key = String(pane.getAttribute("data-reward-pane") || "");
                if (key !== "") {
                    availableKeys.push(key);
                }
            }

            if (availableKeys.length === 0) {
                return;
            }
            if (target === "" || availableKeys.indexOf(target) === -1) {
                target = availableKeys[0];
            }

            rewardTabButtons.forEach(function (btn) {
                if (!btn) {
                    return;
                }
                var key = String(btn.getAttribute("data-reward-tab") || "");
                var active = key === target;
                if (btn.classList) {
                    btn.classList.toggle("is-active", active);
                }
                btn.setAttribute("aria-selected", active ? "true" : "false");
                btn.setAttribute("tabindex", active ? "0" : "-1");
            });

            rewardPanes.forEach(function (pane) {
                if (!pane) {
                    return;
                }
                var key = String(pane.getAttribute("data-reward-pane") || "");
                var active = key === target;
                if (pane.classList) {
                    pane.classList.toggle("is-active", active);
                }
                pane.setAttribute("aria-hidden", active ? "false" : "true");
            });
        }

        var rewardPopoverHideTimer = null;

        function clearRewardPopoverHideTimer() {
            if (rewardPopoverHideTimer) {
                window.clearTimeout(rewardPopoverHideTimer);
                rewardPopoverHideTimer = null;
            }
        }

        function closeRewardPopover(immediate) {
            if (!rewardPopover) {
                return;
            }
            clearRewardPopoverHideTimer();
            rewardPopover.classList.remove("is-open");
            rewardPopover.setAttribute("aria-hidden", "true");
            if (rewardBackdrop) {
                rewardBackdrop.classList.remove("is-open");
                rewardBackdrop.setAttribute("aria-hidden", "true");
            }
            if (rewardBtn) {
                rewardBtn.setAttribute("aria-expanded", "false");
            }

            var hideNow = function () {
                rewardPopover.hidden = true;
                rewardPopover.style.left = "";
                rewardPopover.style.top = "";
                if (rewardBackdrop) {
                    rewardBackdrop.hidden = true;
                }
            };

            if (immediate) {
                hideNow();
                return;
            }

            rewardPopoverHideTimer = window.setTimeout(function () {
                rewardPopoverHideTimer = null;
                hideNow();
            }, 180);
        }

        function openRewardPopover() {
            if (!rewardPopover || !rewardPanel || !rewardBtn) {
                return;
            }
            clearRewardPopoverHideTimer();
            setRewardActiveKey(getRewardActiveKey());
            if (rewardBackdrop) {
                rewardBackdrop.hidden = false;
                rewardBackdrop.setAttribute("aria-hidden", "false");
            }
            rewardPopover.hidden = false;
            rewardPopover.setAttribute("aria-hidden", "false");
            rewardBtn.setAttribute("aria-expanded", "true");
            positionRewardPopover();
            window.setTimeout(function () {
                if (rewardBackdrop) {
                    rewardBackdrop.classList.add("is-open");
                }
                rewardPopover.classList.add("is-open");
            }, 0);
        }

        postsSettings = loadPostsSettings();
        syncPostsSettingsUI();
        applyPostsSettingsToAllLists();
        updateSettingsFabVisibility();

        bindSettingsSegmentedControl(settingsSortModeControl, function (value) {
            savePostsSettings({
                sortMode: value,
                order: getSettingsSegmentedValue(settingsOrderControl) || postsSettings.order,
                listMode: getSettingsSegmentedValue(settingsListModeControl) || postsSettings.listMode
            });
            applyPostsSettingsToAllLists();
        });

        bindSettingsSegmentedControl(settingsOrderControl, function (value) {
            savePostsSettings({
                sortMode: getSettingsSegmentedValue(settingsSortModeControl) || postsSettings.sortMode,
                order: value,
                listMode: getSettingsSegmentedValue(settingsListModeControl) || postsSettings.listMode
            });
            applyPostsSettingsToAllLists();
        });

        bindSettingsSegmentedControl(settingsListModeControl, function (value) {
            savePostsSettings({
                sortMode: getSettingsSegmentedValue(settingsSortModeControl) || postsSettings.sortMode,
                order: getSettingsSegmentedValue(settingsOrderControl) || postsSettings.order,
                listMode: value
            });
            applyPostsSettingsToAllLists();
            // Ensure the popover doesn't drift after layout changes (excerpt insertion).
            if (isSettingsPopoverOpen()) {
                window.setTimeout(positionSettingsPopover, 0);
            }
        });

        if (settingsBtn) {
            settingsBtn.addEventListener("click", function () {
                toggleSettingsPopover();
            });
        }

        if (rewardBtn && rewardPopover && rewardPanel) {
            rewardBtn.addEventListener("click", function (e) {
                if (e && e.preventDefault) {
                    e.preventDefault();
                }
                openRewardPopover();
            });

            rewardBtn.addEventListener("focus", function () {
                openRewardPopover();
            });

            if (rewardTabButtons.length > 0) {
                rewardTabButtons.forEach(function (btn) {
                    if (!btn || !btn.addEventListener) {
                        return;
                    }
                    btn.addEventListener("click", function (e) {
                        if (e && e.preventDefault) {
                            e.preventDefault();
                        }
                        var key = String(btn.getAttribute("data-reward-tab") || "");
                        setRewardActiveKey(key);
                        if (isRewardPopoverOpen()) {
                            positionRewardPopover();
                        }
                    });
                });
            }

            if (rewardImages.length > 0) {
                rewardImages.forEach(function (img) {
                    if (!img || !img.addEventListener) {
                        return;
                    }
                    img.addEventListener("load", function () {
                        if (isRewardPopoverOpen()) {
                            positionRewardPopover();
                        }
                    });
                });
            }
        }

        if (rewardCloseBtn) {
            rewardCloseBtn.addEventListener("click", function (e) {
                if (e && e.preventDefault) {
                    e.preventDefault();
                }
                closeRewardPopover();
            });
        }

        document.addEventListener("mousedown", function (e) {
            var t = e ? e.target : null;
            if (!t || !t.closest) {
                return;
            }

            if (isSettingsPopoverOpen()) {
                if (!t.closest(".fab-settings-popover") && !t.closest(".fab-settings")) {
                    closeSettingsPopover();
                }
            }

            if (isRewardPopoverOpen()) {
                if (!t.closest(".fab-reward-popover") && !t.closest(".fab-reward")) {
                    closeRewardPopover();
                }
            }
        }, true);

        window.addEventListener("keydown", function (e) {
            var key = e && (e.key || e.code);
            if (key === "Escape" || key === "Esc") {
                if (isSettingsPopoverOpen()) {
                    closeSettingsPopover();
                }
                if (isRewardPopoverOpen()) {
                    closeRewardPopover();
                }
            }
        });

        window.addEventListener("resize", function () {
            updateSettingsFabVisibility();
            if (isSettingsPopoverOpen()) {
                syncPostsSettingsUI();
                positionSettingsPopover();
            }
            if (isRewardPopoverOpen()) {
                positionRewardPopover();
            }
        });

        window.addEventListener("hansjack:pjax:after", function () {
            // Re-run list-state sync after PJAX swapped `.main`.
            syncPostsSettingsUI();
            applyPostsSettingsToAllLists();
            updateSettingsFabVisibility();
            recomputeScrollMetrics();
            updateTopProgress();
            if (isSettingsPopoverOpen()) {
                closeSettingsPopover();
            }
            if (isRewardPopoverOpen()) {
                closeRewardPopover(true);
            }
        });

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
                var link = t.closest("a.article-toc-link");
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

        recomputeScrollMetrics();
        updateTopProgress();
        try {
            window.addEventListener("scroll", requestTick, { passive: true });
        } catch (e) {
            window.addEventListener("scroll", requestTick);
        }
        window.addEventListener("resize", function () {
            recomputeScrollMetrics();
            requestTick();
        });

        window.addEventListener("load", function () {
            recomputeScrollMetrics();
            requestTick();
        });

        // Async landing quote/content changes can shift page height shortly after paint.
        window.setTimeout(function () {
            recomputeScrollMetrics();
            requestTick();
        }, 180);
        window.setTimeout(function () {
            recomputeScrollMetrics();
            requestTick();
        }, 560);
    })();

/* block 4 */
(function () {
        var nav = document.querySelector(".nav-desktop");
        if (!nav) {
            return;
        }

        var floatIcon = nav.querySelector(".nav-float-icon");
        var links = Array.prototype.slice.call(nav.querySelectorAll(".nav-link[data-nav-key]"));
        if (!floatIcon || links.length === 0) {
            return;
        }

        var storageKey = "nav_last_key";
        var activeLink = nav.querySelector(".nav-link.is-active") || links[0];
        var storageAvailable = false;
        try {
            storageAvailable = typeof window.localStorage !== "undefined";
        } catch (e) {
            storageAvailable = false;
        }

        function getIconTemplate(link) {
            return link ? link.querySelector(".nav-icon-template") : null;
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
            var textNode = link.querySelector(".nav-link-text");
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
            nav.style.setProperty("--nav-glow-x", Math.round(x) + "px");
            nav.style.setProperty("--nav-glow-y", Math.round(y) + "px");
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
            nav.style.removeProperty("--nav-glow-x");
            nav.style.removeProperty("--nav-glow-y");
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

/* block 5 */
(function () {
        var root = document.documentElement;
        var toggle = document.querySelector(".nav-toggle");
        var mobileNav = document.querySelector(".mobile-nav");
        if (!root || !toggle || !mobileNav) {
            return;
        }

        var panel = mobileNav.querySelector(".mobile-nav-panel");
        var backdrop = mobileNav.querySelector(".mobile-nav-backdrop");
        if (!panel || !backdrop) {
            return;
        }

        var openClass = "is-open";
        var rootOpenClass = "mobile-nav-open";

        function closeOtherHeaderMenus() {
            var pagesMenu = document.querySelector(".pages-menu");
            var userMenu = document.querySelector(".user-menu");

            if (pagesMenu) {
                pagesMenu.classList.remove("is-open");
                var pagesTrigger = pagesMenu.querySelector(".pages-trigger");
                if (pagesTrigger) {
                    pagesTrigger.setAttribute("aria-expanded", "false");
                }
            }

            if (userMenu) {
                userMenu.classList.remove("is-open");
                var userTrigger = userMenu.querySelector(".user-trigger");
                if (userTrigger) {
                    userTrigger.setAttribute("aria-expanded", "false");
                }
            }

            root.classList.remove("header-dropdown-open");
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

        var themeBtn = panel.querySelector("[data-theme-toggle]");
        if (themeBtn) {
            themeBtn.addEventListener("click", function (event) {
                event.preventDefault();
                var themeToggle = document.querySelector(".theme-toggle");
                if (themeToggle) {
                    themeToggle.click();
                }
            });
        }
    })();

/* block 6 */
(function () {
        var menu = document.querySelector(".pages-menu");
        if (!menu) {
            return;
        }

        var trigger = menu.querySelector(".pages-trigger");
        var dropdown = menu.querySelector(".pages-dropdown");
        if (!trigger || !dropdown) {
            return;
        }

        var openClass = "is-open";
        var margin = 8;  // px from viewport edges
        var hoverCapable = false;
        try {
            hoverCapable = !!(window.matchMedia && window.matchMedia("(hover: hover) and (pointer: fine)").matches);
        } catch (e) {
            hoverCapable = false;
        }
        var raf = window.requestAnimationFrame
            ? window.requestAnimationFrame.bind(window)
            : function (callback) {
                return window.setTimeout(callback, 16);
            };
        function syncHeaderDropdownLock() {
            var pagesMenu = document.querySelector(".pages-menu");
            var userMenu = document.querySelector(".user-menu");
            var hasOpen = false;

            if (pagesMenu && pagesMenu.classList.contains("is-open")) {
                hasOpen = true;
            }
            if (userMenu && userMenu.classList.contains("is-open")) {
                hasOpen = true;
            }

            document.documentElement.classList.toggle("header-dropdown-open", hasOpen);
        }

        function getGapPx() {
            try {
                var raw = window
                    .getComputedStyle(document.documentElement)
                    .getPropertyValue("--dropdown-gap");
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
            var userMenu = document.querySelector(".user-menu");
            if (userMenu) {
                userMenu.classList.remove("is-open");
                var userTrigger = userMenu.querySelector(".user-trigger");
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

            if (hoverCapable) {
                openMenu();
                return;
            }

            if (menu.classList.contains(openClass)) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        if (hoverCapable) {
            var closeTimer = null;
            function cancelClose() {
                if (!closeTimer) {
                    return;
                }
                window.clearTimeout(closeTimer);
                closeTimer = null;
            }
            function scheduleClose(event) {
                var next = event ? event.relatedTarget : null;
                if (next && menu.contains(next)) {
                    return;
                }

                cancelClose();
                closeTimer = window.setTimeout(function () {
                    closeTimer = null;
                    closeMenu();
                }, 240);
            }

            menu.addEventListener("mouseenter", function () {
                cancelClose();
                openMenu();
            });
            menu.addEventListener("mouseleave", scheduleClose);
            dropdown.addEventListener("mouseenter", cancelClose);
            dropdown.addEventListener("mouseleave", scheduleClose);
            menu.addEventListener("focusin", function () {
                cancelClose();
                openMenu();
            });
            menu.addEventListener("focusout", function (event) {
                var next = event ? event.relatedTarget : null;
                if (next && menu.contains(next)) {
                    return;
                }
                cancelClose();
                closeMenu();
            });
        } else {
            menu.addEventListener("mouseenter", function () {
                raf(positionDropdown);
            });

            menu.addEventListener("focusin", function () {
                raf(positionDropdown);
            });
        }

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

/* block 7 */
(function () {
        var menu = document.querySelector(".user-menu");
        if (!menu) {
            return;
        }

        var trigger = menu.querySelector(".user-trigger");
        var dropdown = menu.querySelector(".user-dropdown");
        if (!trigger || !dropdown) {
            return;
        }

        var openClass = "is-open";
        var margin = 8;  // px from viewport edges
        var hoverCapable = false;
        try {
            hoverCapable = !!(window.matchMedia && window.matchMedia("(hover: hover) and (pointer: fine)").matches);
        } catch (e) {
            hoverCapable = false;
        }
        var raf = window.requestAnimationFrame
            ? window.requestAnimationFrame.bind(window)
            : function (callback) {
                return window.setTimeout(callback, 16);
            };
        function syncHeaderDropdownLock() {
            var pagesMenu = document.querySelector(".pages-menu");
            var userMenu = document.querySelector(".user-menu");
            var hasOpen = false;

            if (pagesMenu && pagesMenu.classList.contains("is-open")) {
                hasOpen = true;
            }
            if (userMenu && userMenu.classList.contains("is-open")) {
                hasOpen = true;
            }

            document.documentElement.classList.toggle("header-dropdown-open", hasOpen);
        }

        function getGapPx() {
            try {
                var raw = window
                    .getComputedStyle(document.documentElement)
                    .getPropertyValue("--dropdown-gap");
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
            var pagesMenu = document.querySelector(".pages-menu");
            if (pagesMenu) {
                pagesMenu.classList.remove("is-open");
                var pagesTrigger = pagesMenu.querySelector(".pages-trigger");
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

            if (hoverCapable) {
                openMenu();
                return;
            }

            if (menu.classList.contains(openClass)) {
                closeMenu();
            } else {
                openMenu();
            }
        });

        if (hoverCapable) {
            var closeTimer = null;
            function cancelClose() {
                if (!closeTimer) {
                    return;
                }
                window.clearTimeout(closeTimer);
                closeTimer = null;
            }
            function scheduleClose(event) {
                var next = event ? event.relatedTarget : null;
                if (next && menu.contains(next)) {
                    return;
                }

                cancelClose();
                closeTimer = window.setTimeout(function () {
                    closeTimer = null;
                    closeMenu();
                }, 240);
            }

            menu.addEventListener("mouseenter", function () {
                cancelClose();
                openMenu();
            });
            menu.addEventListener("mouseleave", scheduleClose);
            dropdown.addEventListener("mouseenter", cancelClose);
            dropdown.addEventListener("mouseleave", scheduleClose);
            menu.addEventListener("focusin", function () {
                cancelClose();
                openMenu();
            });
            menu.addEventListener("focusout", function (event) {
                var next = event ? event.relatedTarget : null;
                if (next && menu.contains(next)) {
                    return;
                }
                cancelClose();
                closeMenu();
            });
        } else {
            menu.addEventListener("mouseenter", function () {
                raf(positionDropdown);
            });

            menu.addEventListener("focusin", function () {
                raf(positionDropdown);
            });
        }

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

/* block 8 */
(function () {
        var root = document.documentElement;
        var toggle = document.querySelector(".theme-toggle");
        var curtain = document.querySelector(".theme-curtain");
        if (!root || !toggle) {
            return;
        }

        var storageKey = "theme_mode";
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
            root.classList.remove("theme-light", "theme-dark");
            if (mode === "dark") {
                root.classList.add("theme-dark");
            } else {
                root.classList.add("theme-light");
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
            var colorVar = mode === "dark" ? "--night-bg" : "--day-bg";
            var imageVar = mode === "dark" ? "--night-bg-image" : "--day-bg-image";
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
            curtain.style.setProperty("--curtain-top-color", topVisual.color);
            curtain.style.setProperty("--curtain-top-image", topVisual.image);
            curtain.style.setProperty("--curtain-bottom-color", bottomVisual.color);
            curtain.style.setProperty("--curtain-bottom-image", bottomVisual.image);
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

            root.style.setProperty("--vt-duration", transitionDuration + "ms");
            if (toMode === "dark") {
                root.style.setProperty("--vt-inset-from", "0 0 100% 0");
            } else {
                root.style.setProperty("--vt-inset-from", "100% 0 0 0");
            }
            root.style.setProperty("--vt-inset-to", "0 0 0 0");

            var transition = document.startViewTransition(function () {
                applyTheme(toMode, true);
            });

            transition.finished
                .catch(function () {})
                .finally(function () {
                    root.style.removeProperty("--vt-duration");
                    root.style.removeProperty("--vt-inset-from");
                    root.style.removeProperty("--vt-inset-to");

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

            var fromMode = root.classList.contains("theme-dark") ? "dark" : "light";
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

/* block 9 */
(function () {
        var body = document.body;
        if (!body) {
            return;
        }

        var isPosts = body.classList.contains("page-posts");
        var isNotes = body.classList.contains("page-notes");
        if (!isPosts && !isNotes) {
            return;
        }

        var pager = body.querySelector(".posts-pager.page-navigator");
        if (!pager) {
            return;
        }

        var host = null;
        if (typeof pager.closest === "function") {
            host = pager.closest(".posts-main");
        }
        if (!host) {
            host = pager.parentElement || body;
        }

        var spacer = document.createElement("div");
        spacer.className = "posts-pager-spacer";
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

/* block 10 */
(function () { 
        var comments = document.querySelector(".comments"); 
        if (!comments) { 
            return; 
        } 

        (function setupCommentsToolbar() {
            var refreshBtn = comments.querySelector("[data-comments-refresh]");
            if (refreshBtn) {
                refreshBtn.addEventListener("click", function (e) {
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }
                    try {
                        if (window && window.location && window.location.hash !== "#comments") {
                            window.location.hash = "comments";
                        }
                    } catch (err) {}
                    try {
                        window.location.reload();
                    } catch (err2) {}
                });
            }

            var sortBtn = comments.querySelector("[data-comments-sort-toggle]");
            if (!sortBtn) {
                return;
            }

            function getOrder() {
                var v = (comments.getAttribute("data-comments-order") || "asc").toLowerCase();
                return v === "desc" ? "desc" : "asc";
            }

            function setOrder(v) {
                comments.setAttribute("data-comments-order", v === "desc" ? "desc" : "asc");
            }

            function updateSortLabel() {
                var order = getOrder();
                var target = order === "desc" ? "asc" : "desc";
                var label = target === "asc" ? "切换为时间升序" : "切换为时间降序";
                sortBtn.setAttribute("aria-label", label);
                sortBtn.setAttribute("title", label);
            }

            var iconAsc = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock-arrow-up-icon lucide-clock-arrow-up" aria-hidden="true"><path d="M12 6v6l1.56.78"/><path d="M13.227 21.925a10 10 0 1 1 8.767-9.588"/><path d="m14 18 4-4 4 4"/><path d="M18 22v-8"/></svg>';
            var iconDesc = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock-arrow-down-icon lucide-clock-arrow-down" aria-hidden="true"><path d="M12 6v6l2 1"/><path d="M12.337 21.994a10 10 0 1 1 9.588-8.767"/><path d="m14 18 4 4 4-4"/><path d="M18 14v8"/></svg>';

            function renderSortIcon() {
                var order = getOrder();
                var target = order === "desc" ? "asc" : "desc";
                sortBtn.innerHTML = target === "asc" ? iconAsc : iconDesc;
            }

            function reverseList(list) {
                if (!list) {
                    return;
                }
                var nodes = Array.prototype.slice.call(list.children || []);
                for (var i = nodes.length - 1; i >= 0; i--) {
                    list.appendChild(nodes[i]);
                }
            }

            sortBtn.addEventListener("click", function (e) {
                if (e && e.preventDefault) {
                    e.preventDefault();
                }

                // Flip current order.
                var current = getOrder();
                var next = current === "asc" ? "desc" : "asc";

                // Reverse every comment list (top-level + threaded lists) to match Typecho's backend behavior.
                var lists = Array.prototype.slice.call(comments.querySelectorAll(".comment-list"));
                lists.forEach(reverseList);

                setOrder(next);
                updateSortLabel();
                renderSortIcon();
                // Thread order changed, recompute avatar sizing/connector lengths.
                try {
                    window.dispatchEvent(new Event("resize"));
                } catch (err) {}
            });

            updateSortLabel();
            renderSortIcon();
        })();
 
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
            var buttons = Array.prototype.slice.call(comments.querySelectorAll("[data-comment-share]"));  
            if (!buttons || buttons.length === 0) {  
                return;  
            }  
 
            buttons.forEach(function (btn) { 
                btn.addEventListener("click", function (e) { 
                    if (e && e.preventDefault) { 
                        e.preventDefault(); 
                    } 
 
                    var url = btn.getAttribute("data-comment-share") || ""; 
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

        (function setupCommentChildrenToggle() {
            var buttons = Array.prototype.slice.call(comments.querySelectorAll("[data-comment-children-toggle]"));
            if (!buttons || buttons.length === 0) {
                return;
            }
            var activeHashTarget = null;
            var activeHashTargetTimer = 0;

            function findDirectChildrenWrap(row) {
                if (!row) {
                    return null;
                }
                var nodes = row.children || [];
                for (var i = 0; i < nodes.length; i++) {
                    var node = nodes[i];
                    if (node && node.classList && node.classList.contains("comment-children")) {
                        return node;
                    }
                }
                if (row.querySelector) {
                    return row.querySelector(".comment-children");
                }
                return null;
            }

            function syncToggleState(row, button) {
                if (!row || !button) {
                    return;
                }

                var wrap = findDirectChildrenWrap(row);
                if (!wrap) {
                    button.setAttribute("hidden", "hidden");
                    return;
                }

                var collapsed = row.classList.contains("comment-children-collapsed");
                var label = collapsed ? "展开子评论" : "收起子评论";
                button.setAttribute("aria-label", label);
                button.setAttribute("title", label);
                button.setAttribute("aria-expanded", collapsed ? "false" : "true");

                if (collapsed) {
                    wrap.setAttribute("hidden", "hidden");
                } else {
                    wrap.removeAttribute("hidden");
                }
            }

            function expandCollapsedRow(row) {
                if (!row || !row.classList || !row.classList.contains("comment-children-collapsed")) {
                    return false;
                }
                row.classList.remove("comment-children-collapsed");
                var button = row.querySelector ? row.querySelector("[data-comment-children-toggle]") : null;
                if (button) {
                    syncToggleState(row, button);
                } else {
                    var wrap = findDirectChildrenWrap(row);
                    if (wrap) {
                        wrap.removeAttribute("hidden");
                    }
                }
                return true;
            }

            function findCommentTargetFromHash(rawHash) {
                var hash = String(rawHash || "");
                if (!hash) {
                    return null;
                }
                if (hash.charAt(0) === "#") {
                    hash = hash.slice(1);
                }
                try {
                    hash = decodeURIComponent(hash);
                } catch (e) {}
                hash = String(hash || "").trim();
                if (!hash || hash.indexOf("comment-") !== 0) {
                    return null;
                }
                var target = document.getElementById(hash);
                if (!target || !comments.contains(target)) {
                    return null;
                }
                return target;
            }

            function revealHashComment(rawHash, smoothScroll) {
                var target = findCommentTargetFromHash(rawHash);
                if (!target) {
                    return;
                }

                var changed = false;
                var cursor = target;
                while (cursor) {
                    var ancestor = cursor.parentElement && cursor.parentElement.closest
                        ? cursor.parentElement.closest(".comment-body.comment-children-collapsed")
                        : null;
                    if (!ancestor) {
                        break;
                    }
                    if (expandCollapsedRow(ancestor)) {
                        changed = true;
                    }
                    cursor = ancestor;
                }

                if (changed) {
                    try {
                        window.dispatchEvent(new Event("resize"));
                    } catch (err) {}
                }

                window.setTimeout(function () {
                    if (!target || !target.scrollIntoView) {
                        return;
                    }
                    try {
                        target.scrollIntoView({
                            behavior: smoothScroll ? "smooth" : "auto",
                            block: "center"
                        });
                    } catch (e) {}
                    if (activeHashTargetTimer) {
                        window.clearTimeout(activeHashTargetTimer);
                        activeHashTargetTimer = 0;
                    }
                    if (activeHashTarget && activeHashTarget !== target && activeHashTarget.classList) {
                        activeHashTarget.classList.remove("is-comment-targeted");
                    }
                    if (target.classList) {
                        target.classList.remove("is-comment-targeted");
                        void target.offsetWidth;
                        target.classList.add("is-comment-targeted");
                    }
                    activeHashTarget = target;
                    activeHashTargetTimer = window.setTimeout(function () {
                        if (activeHashTarget && activeHashTarget.classList) {
                            activeHashTarget.classList.remove("is-comment-targeted");
                        }
                        activeHashTarget = null;
                        activeHashTargetTimer = 0;
                    }, 1800);
                }, changed ? 36 : 0);
            }

            buttons.forEach(function (button) {
                var row = button.closest ? button.closest(".comment-body") : null;
                if (!row) {
                    return;
                }

                syncToggleState(row, button);
                button.addEventListener("click", function (e) {
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }

                    var wrap = findDirectChildrenWrap(row);
                    if (!wrap) {
                        return;
                    }

                    row.classList.toggle("comment-children-collapsed");
                    syncToggleState(row, button);
                    try {
                        window.dispatchEvent(new Event("resize"));
                    } catch (err) {}
                });
            });

            window.setTimeout(function () {
                revealHashComment(window.location ? window.location.hash : "", false);
            }, 0);
            window.setTimeout(function () {
                revealHashComment(window.location ? window.location.hash : "", false);
            }, 120);
            window.addEventListener("hashchange", function () {
                revealHashComment(window.location ? window.location.hash : "", true);
            });
        })();

        (function setupCommentReplyTrace() {
            var bubbles = Array.prototype.slice.call(comments.querySelectorAll(".comment-body.comment-child > .comment-content"));
            if (!bubbles || bubbles.length === 0) {
                return;
            }

            var svgNS = "http://www.w3.org/2000/svg";
            var overlay = null;
            var active = null;
            var raf = window.requestAnimationFrame || function (fn) { return window.setTimeout(fn, 16); };
            var scheduled = false;

            function ensureOverlay() {
                if (overlay && overlay.parentNode) {
                    return overlay;
                }
                overlay = document.createElementNS(svgNS, "svg");
                overlay.setAttribute("data-comment-reply-trace", "");
                overlay.setAttribute("aria-hidden", "true");
                overlay.style.position = "fixed";
                overlay.style.left = "0";
                overlay.style.top = "0";
                overlay.style.width = "100vw";
                overlay.style.height = "100vh";
                overlay.style.pointerEvents = "none";
                overlay.style.overflow = "visible";
                overlay.style.zIndex = "95";
                overlay.style.display = "none";
                document.body.appendChild(overlay);
                return overlay;
            }

            function clearOverlay() {
                if (!overlay) {
                    return;
                }
                overlay.innerHTML = "";
                overlay.style.display = "none";
            }

            function findParentComment(item) {
                if (!item || !item.parentElement || !item.parentElement.closest) {
                    return null;
                }
                return item.parentElement.closest(".comment-body");
            }

            function findDirectCommentContent(row) {
                if (!row) {
                    return null;
                }
                var nodes = row.children || [];
                for (var i = 0; i < nodes.length; i++) {
                    var node = nodes[i];
                    if (node && node.classList && node.classList.contains("comment-content")) {
                        return node;
                    }
                }
                if (row.querySelector) {
                    return row.querySelector(".comment-content");
                }
                return null;
            }

            function requestDraw() {
                if (scheduled) {
                    return;
                }
                scheduled = true;
                raf(function () {
                    scheduled = false;
                    drawTrace();
                });
            }

            function clearActive() {
                active = null;
                clearOverlay();
            }

            function drawTrace() {
                if (!active || !active.item || !active.content) {
                    clearOverlay();
                    return;
                }

                var item = active.item;
                var content = active.content;
                var parent = findParentComment(item);
                if (!parent) {
                    clearActive();
                    return;
                }

                var avatar = parent.querySelector(".comment-author span[itemprop=\"image\"]");
                if (!avatar) {
                    clearActive();
                    return;
                }
                var parentContent = findDirectCommentContent(parent);
                if (!parentContent) {
                    clearActive();
                    return;
                }

                var contentRect = null;
                var avatarRect = null;
                var parentContentRect = null;
                try {
                    contentRect = content.getBoundingClientRect();
                    avatarRect = avatar.getBoundingClientRect();
                    parentContentRect = parentContent.getBoundingClientRect();
                } catch (e) {
                    contentRect = null;
                    avatarRect = null;
                    parentContentRect = null;
                }
                if (!contentRect || !avatarRect || !parentContentRect || contentRect.width <= 0 || contentRect.height <= 0 || parentContentRect.width <= 0 || parentContentRect.height <= 0) {
                    clearOverlay();
                    return;
                }

                var sx = contentRect.left + 1;
                var sy = contentRect.top + contentRect.height * 0.5;
                var exBase = avatarRect.left + avatarRect.width * 0.5;
                var ex = Math.max(parentContentRect.left + 8, Math.min(parentContentRect.right - 8, exBase));
                var ey = parentContentRect.bottom - 1;

                var commentsStyle = window.getComputedStyle ? window.getComputedStyle(comments) : null;
                var stroke = commentsStyle ? String(commentsStyle.getPropertyValue("--comment-reply-trace-color") || "").trim() : "";
                if (!stroke) {
                    stroke = "rgba(146, 146, 146, 0.9)";
                }
                var strokeWidth = 1.5;
                var dx = Math.abs(sx - ex);
                var dy = Math.abs(sy - ey);
                var c1x = sx - Math.max(18, Math.min(42, dx * 0.35));
                var c1y = sy;
                var c2x = ex + Math.max(14, Math.min(34, dx * 0.3));
                var c2y = ey + Math.max(10, Math.min(24, dy * 0.22));

                var svg = ensureOverlay();
                svg.innerHTML = "";
                svg.style.display = "block";

                var path = document.createElementNS(svgNS, "path");
                path.setAttribute(
                    "d",
                    "M " + sx.toFixed(2) + " " + sy.toFixed(2) +
                    " C " + c1x.toFixed(2) + " " + c1y.toFixed(2) +
                    ", " + c2x.toFixed(2) + " " + c2y.toFixed(2) +
                    ", " + ex.toFixed(2) + " " + ey.toFixed(2)
                );
                path.setAttribute("fill", "none");
                path.setAttribute("stroke", stroke);
                path.setAttribute("stroke-width", String(strokeWidth));
                path.setAttribute("stroke-linecap", "round");
                path.setAttribute("stroke-dasharray", "4 4");
                path.setAttribute("vector-effect", "non-scaling-stroke");
                svg.appendChild(path);

                var angle = Math.atan2(ey - c2y, ex - c2x);
                var arrowLen = 7;
                var wing = Math.PI / 6;
                var ax1 = ex - arrowLen * Math.cos(angle - wing);
                var ay1 = ey - arrowLen * Math.sin(angle - wing);
                var ax2 = ex - arrowLen * Math.cos(angle + wing);
                var ay2 = ey - arrowLen * Math.sin(angle + wing);

                var head = document.createElementNS(svgNS, "path");
                head.setAttribute(
                    "d",
                    "M " + ax1.toFixed(2) + " " + ay1.toFixed(2) +
                    " L " + ex.toFixed(2) + " " + ey.toFixed(2) +
                    " L " + ax2.toFixed(2) + " " + ay2.toFixed(2)
                );
                head.setAttribute("fill", "none");
                head.setAttribute("stroke", stroke);
                head.setAttribute("stroke-width", String(strokeWidth));
                head.setAttribute("stroke-linecap", "round");
                head.setAttribute("stroke-linejoin", "round");
                head.setAttribute("vector-effect", "non-scaling-stroke");
                svg.appendChild(head);
            }

            bubbles.forEach(function (content) {
                var item = content.closest ? content.closest(".comment-body") : null;
                if (!item) {
                    return;
                }
                content.addEventListener("mouseenter", function () {
                    active = { item: item, content: content };
                    requestDraw();
                });
                content.addEventListener("mouseleave", function () {
                    clearActive();
                });
            });

            window.addEventListener("scroll", function () {
                if (active) {
                    requestDraw();
                }
            }, { passive: true });
            window.addEventListener("resize", function () {
                if (active) {
                    requestDraw();
                }
            });
            comments.addEventListener("click", function () {
                if (active) {
                    requestDraw();
                }
            }, true);
        })();

        (function setupCommentAvatarSizing() {
            // Make the avatar diameter match the author-meta two-line height.
            // We also update --comment-avatar-size and the connector line height.
            var raf = window.requestAnimationFrame || function (fn) { return window.setTimeout(fn, 16); };
            var ticking = false;

            function update() {
                var items = Array.prototype.slice.call(comments.querySelectorAll(".comment-body"));
                if (!items || items.length === 0) {
                    return;
                }

                items.forEach(function (item) {
                    var meta = item.querySelector(".comment-author-meta");
                    if (!meta) {
                        return;
                    }

                    var rect;
                    try {
                        rect = meta.getBoundingClientRect();
                    } catch (e) {
                        rect = null;
                    }
                    if (!rect || !isFinite(rect.height) || rect.height <= 0) {
                        return;
                    }

                    // Keep avatar compact while still bounded for layout stability.
                    var h = Math.max(24, Math.min(36, rect.height * 1.05));
                    item.style.setProperty("--comment-avatar-size", h.toFixed(2) + "px");
                });

                var commentsStyle = window.getComputedStyle ? window.getComputedStyle(comments) : null;
                var lineStartGap = commentsStyle ? parseFloat(commentsStyle.getPropertyValue("--comment-avatar-line-gap-start")) : NaN;
                var lineEndGap = commentsStyle ? parseFloat(commentsStyle.getPropertyValue("--comment-avatar-line-gap-end")) : NaN;
                var lineFadeLength = commentsStyle ? parseFloat(commentsStyle.getPropertyValue("--comment-avatar-line-fade-length")) : NaN;
                if (!isFinite(lineStartGap)) {
                    lineStartGap = 6;
                }
                if (!isFinite(lineEndGap)) {
                    lineEndGap = 4;
                }
                if (!isFinite(lineFadeLength) || lineFadeLength < 0) {
                    lineFadeLength = 0;
                }

                function getCommentLevel(node, fallbackLevel) {
                    var level = parseInt(node && node.getAttribute ? node.getAttribute("data-comment-level") || "" : "", 10);
                    if (!isFinite(level)) {
                        level = isFinite(fallbackLevel) ? fallbackLevel : 0;
                    }
                    return level;
                }

                function findBoundaryComment(index, currentLevel) {
                    var i = index + 1;
                    while (i < items.length) {
                        var candidate = items[i];
                        if (!candidate) {
                            i += 1;
                            continue;
                        }
                        // Boundary rule: first row whose level is <= current level.
                        // - Same level: stop above same-level next comment.
                        // - Upper level: stop above parent-level (or higher) next comment.
                        var candidateLevel = getCommentLevel(candidate, currentLevel);
                        if (candidateLevel <= currentLevel) {
                            return candidate;
                        }
                        i += 1;
                    }
                    return null;
                }

                function getThreadRootComment(item) {
                    var root = item;
                    while (root && root.parentElement && root.parentElement.closest) {
                        var parentComment = root.parentElement.closest(".comment-body");
                        if (!parentComment) {
                            break;
                        }
                        root = parentComment;
                    }
                    return root || item;
                }

                items.forEach(function (item, index) {
                    var hasChildren = item.classList && item.classList.contains("comment-has-children");
                    var isChildrenCollapsed = item.classList && item.classList.contains("comment-children-collapsed");
                    if (!hasChildren || isChildrenCollapsed) {
                        item.classList.add("is-avatar-line-hidden");
                        item.style.setProperty("--comment-avatar-line-height", "0px");
                        return;
                    }

                    var avatarWrap = item.querySelector('.comment-author span[itemprop="image"]');
                    if (!avatarWrap) {
                        item.classList.add("is-avatar-line-hidden");
                        item.style.setProperty("--comment-avatar-line-height", "0px");
                        return;
                    }

                    var avatarRect;
                    var itemRect;
                    try {
                        avatarRect = avatarWrap.getBoundingClientRect();
                        itemRect = item.getBoundingClientRect();
                    } catch (e) {
                        avatarRect = null;
                        itemRect = null;
                    }
                    if (!avatarRect || !itemRect) {
                        item.classList.add("is-avatar-line-hidden");
                        item.style.setProperty("--comment-avatar-line-height", "0px");
                        return;
                    }

                    var startY = avatarRect.bottom + lineStartGap;
                    var endY = 0;
                    var currentLevel = getCommentLevel(item, 0);
                    var target = findBoundaryComment(index, currentLevel);

                    var targetY = NaN;
                    var hasBoundary = false;
                    if (target) {
                        var targetRect;
                        try {
                            targetRect = target.getBoundingClientRect();
                        } catch (e) {
                            targetRect = null;
                        }
                        if (targetRect && isFinite(targetRect.top)) {
                            hasBoundary = true;
                            targetY = targetRect.top - lineEndGap;
                        }
                    }

                    if (!hasBoundary || !isFinite(targetY)) {
                        var rootComment = getThreadRootComment(item);
                        var rootRect = null;
                        try {
                            rootRect = rootComment ? rootComment.getBoundingClientRect() : null;
                        } catch (e) {
                            rootRect = null;
                        }
                        if (rootRect && isFinite(rootRect.bottom)) {
                            targetY = rootRect.bottom - lineEndGap;
                        } else {
                            targetY = itemRect.bottom - lineEndGap;
                        }
                    }
                    endY = targetY + lineFadeLength;

                    var lineHeight = endY - startY;
                    if (!isFinite(lineHeight) || lineHeight < 6) {
                        item.classList.add("is-avatar-line-hidden");
                        item.style.setProperty("--comment-avatar-line-height", "0px");
                    } else {
                        item.classList.remove("is-avatar-line-hidden");
                        item.style.setProperty("--comment-avatar-line-height", lineHeight.toFixed(2) + "px");
                    }
                });
            }

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

            function requestUpdateBurst() {
                requestUpdate();
                window.setTimeout(requestUpdate, 40);
                window.setTimeout(requestUpdate, 140);
                window.setTimeout(requestUpdate, 280);
            }

            window.addEventListener("resize", requestUpdate);

            // Reply form insertion/removal and threaded list updates should recompute
            // connector start/end positions immediately.
            if (window.MutationObserver) {
                try {
                    var mo = new MutationObserver(function () {
                        requestUpdate();
                    });
                    mo.observe(comments, {
                        childList: true,
                        subtree: true
                    });
                } catch (e) {}
            }

            if (window.ResizeObserver) {
                try {
                    var ro = new ResizeObserver(function () {
                        requestUpdate();
                    });
                    ro.observe(comments);
                } catch (e) {}
            }

            comments.addEventListener("click", function (e) {
                var target = e && e.target && e.target.closest ? e.target.closest("a,button") : null;
                if (!target) {
                    return;
                }
                var isReplyAction = false;
                if (target.id === "cancel-comment-reply-link") {
                    isReplyAction = true;
                } else if (target.classList && target.classList.contains("comment-reply-link")) {
                    isReplyAction = true;
                } else {
                    var onclickText = target.getAttribute ? String(target.getAttribute("onclick") || "") : "";
                    if (onclickText.indexOf("TypechoComment.reply(") !== -1) {
                        isReplyAction = true;
                    }
                }
                if (isReplyAction) {
                    requestUpdateBurst();
                }
            }, true);

            requestUpdateBurst();
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
            if (orig && orig.__safe_null) {
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
            tc.visiable.__safe_null = true;
        })();

        var forms = Array.prototype.slice.call(comments.querySelectorAll("[data-comment-form]"));
        if (!forms || forms.length === 0) {
            return;
        }

        var root = document.documentElement;
        var modal = document.querySelector("[data-login-modal]");
        var backdrop = modal ? modal.querySelector("[data-login-backdrop]") : null;
        var panel = modal ? modal.querySelector("[data-login-panel]") : null;
        var loginOpenClass = "login-modal-open";

        var commentsUserLogged = comments.getAttribute("data-user-logged") === "1";
        var commentsRequireMail = comments.getAttribute("data-comments-require-mail") === "1";
        var commentsRequireUrl = comments.getAttribute("data-comments-require-url") === "1";
        var commentEditBusy = false;
        var isMemoryComments = comments.classList.contains("memory-comments-shell");
        var topCommentForm = comments.querySelector("[data-comment-form][data-comment-role=\"top\"]");
        var topCommentFormHomeParent = topCommentForm ? topCommentForm.parentNode : null;
        var topCommentFormHomeNext = topCommentForm ? topCommentForm.nextSibling : null;

        function getCommentEditForm() {
            var reply = comments.querySelector("[data-comment-form][data-comment-role=\"reply\"]");
            if (reply) {
                return reply;
            }
            if (isMemoryComments) {
                return null;
            }
            var top = comments.querySelector("[data-comment-form][data-comment-role=\"top\"]");
            if (top) {
                return top;
            }
            return forms[0] || null;
        }

        function getCommentEditToken(form) {
            var tokenInput = form ? form.querySelector("input[name=\"_\"]") : null;
            if (!tokenInput && comments && comments.querySelector) {
                tokenInput = comments.querySelector("input[name=\"_\"]");
            }
            return tokenInput && tokenInput.value ? String(tokenInput.value).trim() : "";
        }

        function getCommentEditEndpoint() {
            var raw = "";
            try {
                raw = (window && window.location && window.location.href) ? String(window.location.href) : "";
            } catch (e) {
                raw = "";
            }

            if (!raw) {
                return "?comment_edit=1";
            }

            try {
                var u = new URL(raw);
                u.searchParams.set("comment_edit", "1");
                return u.toString();
            } catch (e) {
                var clean = raw;
                var hashPos = clean.indexOf("#");
                if (hashPos >= 0) {
                    clean = clean.slice(0, hashPos);
                }
                return clean + (clean.indexOf("?") === -1 ? "?" : "&") + "comment_edit=1";
            }
        }

        function parseCommentEditJson(text) {
            try {
                return JSON.parse(String(text || ""));
            } catch (e) {
                return null;
            }
        }

        function extractCommentEditErrorMessage(raw, response) {
            var text = String(raw || "").trim();
            if (text) {
                var jsonMsg = text.match(/"message"\s*:\s*"([^"]+)"/i);
                if (jsonMsg && jsonMsg[1]) {
                    return jsonMsg[1];
                }
                var title = text.match(/<title[^>]*>([\s\S]*?)<\/title>/i);
                if (title && title[1]) {
                    var titleText = String(title[1]).replace(/\s+/g, " ").trim();
                    if (titleText) {
                        return titleText;
                    }
                }
            }
            if (response) {
                var status = parseInt(response.status || "0", 10);
                if (status === 401 || status === 403) {
                    return "权限或安全校验失败，请重新登录管理员并刷新后重试";
                }
                if (status === 404) {
                    return "评论编辑接口不可用（404）";
                }
                if (status >= 500) {
                    return "服务器错误（" + String(status) + "），请稍后重试";
                }
            }
            return "评论编辑失败，请重试";
        }

        function clearCommentEditState() {
            forms.forEach(function (f) {
                if (!f) {
                    return;
                }
                f.removeAttribute("data-comment-edit-coid");
                f.removeAttribute("data-comment-editing");
                var box = f.querySelector("[data-comment-box]");
                if (box && box.classList) {
                    box.classList.remove("is-editing");
                }
                if (f.classList) {
                    f.classList.remove("comment-edit-inline");
                }
            });

            if (isMemoryComments && topCommentForm && topCommentFormHomeParent) {
                if (topCommentForm.parentNode !== topCommentFormHomeParent) {
                    if (topCommentFormHomeNext && topCommentFormHomeNext.parentNode === topCommentFormHomeParent) {
                        topCommentFormHomeParent.insertBefore(topCommentForm, topCommentFormHomeNext);
                    } else {
                        topCommentFormHomeParent.appendChild(topCommentForm);
                    }
                }
            }
        }

        function setCommentPrivateState(form, isPrivate) {
            if (!form) {
                return;
            }
            var privateBtn = form.querySelector("[data-comment-private-toggle]");
            var box = form.querySelector("[data-comment-box]");
            if (privateBtn) {
                privateBtn.setAttribute("aria-pressed", isPrivate ? "true" : "false");
            }
            if (box && box.classList) {
                box.classList.toggle("is-private", !!isPrivate);
            }
        }

        function findDirectChildByClass(parent, className) {
            if (!parent || !className) {
                return null;
            }
            var children = parent.children || [];
            for (var i = 0; i < children.length; i++) {
                var node = children[i];
                if (node && node.classList && node.classList.contains(className)) {
                    return node;
                }
            }
            return null;
        }

        function placeEditBlockUnderReply(item, block) {
            if (!item || !block) {
                return;
            }

            var replyRow = findDirectChildByClass(item, "comment-reply");
            var childrenWrap = findDirectChildByClass(item, "comment-children");
            if (!replyRow) {
                if (childrenWrap) {
                    item.insertBefore(block, childrenWrap);
                } else {
                    item.appendChild(block);
                }
                return;
            }

            var next = replyRow.nextSibling;
            if (next === block) {
                return;
            }
            if (childrenWrap) {
                item.insertBefore(block, childrenWrap);
                return;
            }
            if (next) {
                item.insertBefore(block, next);
            } else {
                item.appendChild(block);
            }
        }

        function openCommentEdit(btn) {
            if (!btn || !btn.closest) {
                return;
            }

            var coid = parseInt(btn.getAttribute("data-comment-coid") || "0", 10);
            if (!isFinite(coid) || coid <= 0) {
                return;
            }

            var row = btn.closest(".comment-reply");
            var source = row ? row.querySelector("[data-comment-edit-source]") : null;
            var text = "";
            if (source) {
                if (typeof source.value === "string") {
                    text = source.value;
                } else {
                    text = source.textContent || "";
                }
            }
            text = String(text || "").replace(/\r\n?/g, "\n");
            var isPrivate = btn.getAttribute("data-comment-edit-private") === "1";

            var form = getCommentEditForm();
            if (!form || !form.querySelector) {
                return;
            }

            var textarea = form.querySelector("textarea[name=\"text\"]");
            if (!textarea) {
                return;
            }

            clearCommentEditState();

            var role = form.getAttribute("data-comment-role") || "";
            var item = btn.closest(".comment-body");
            if (item) {
                if (role === "reply") {
                    var respondWrap = form.closest("[data-comment-respond]") || form.closest(".respond") || form.closest("#respond");
                    if (respondWrap) {
                        placeEditBlockUnderReply(item, respondWrap);
                    }
                } else if (isMemoryComments) {
                    placeEditBlockUnderReply(item, form);
                    if (form.classList) {
                        form.classList.add("comment-edit-inline");
                    }
                }
            }

            form.setAttribute("data-comment-edit-coid", String(coid));
            form.setAttribute("data-comment-editing", "1");
            var box = form.querySelector("[data-comment-box]");
            if (box && box.classList) {
                box.classList.add("is-editing");
            }

            setCommentPrivateState(form, isPrivate);

            textarea.value = text;
            try {
                textarea.dispatchEvent(new Event("input", { bubbles: true }));
            } catch (e) {}

            if (role !== "reply" && window && window.TypechoComment && typeof window.TypechoComment.cancelReply === "function") {
                try {
                    window.TypechoComment.cancelReply();
                } catch (e) {}
            }

            try {
                form.scrollIntoView({ behavior: "smooth", block: "center" });
            } catch (e) {}
            window.setTimeout(function () {
                try {
                    textarea.focus();
                    var len = (textarea.value || "").length;
                    textarea.setSelectionRange(len, len);
                } catch (e) {}
            }, 80);
        }

        function submitCommentEdit(form, coid, text, done) {
            if (commentEditBusy) {
                return;
            }
            var token = getCommentEditToken(form);
            if (!token) {
                try {
                    window.alert("安全令牌缺失，请刷新页面后再试");
                } catch (e) {}
                return;
            }

            var endpoint = getCommentEditEndpoint();
            var formData = new FormData();
            formData.append("coid", String(coid));
            formData.append("text", String(text || ""));
            formData.append("_", token);

            commentEditBusy = true;

            window.fetch(endpoint, {
                method: "POST",
                body: formData,
                credentials: "same-origin",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            }).then(function (response) {
                return response.text().then(function (raw) {
                    var payload = parseCommentEditJson(raw);
                    if (!response.ok || !payload || !payload.ok) {
                        var msg = payload && payload.message ? String(payload.message) : extractCommentEditErrorMessage(raw, response);
                        throw new Error(msg);
                    }
                    return payload;
                });
            }).then(function () {
                clearCommentEditState();
                try {
                    window.location.hash = "comment-" + String(coid);
                } catch (e) {}
                try {
                    window.location.reload();
                } catch (e) {}
            }).catch(function (err) {
                var msg = err && err.message ? String(err.message) : "评论编辑失败，请重试";
                try {
                    window.alert(msg);
                } catch (e) {}
            }).finally(function () {
                commentEditBusy = false;
                if (typeof done === "function") {
                    done();
                }
            });
        }

        var identityKey = "comment_identity_" + (location && location.pathname ? location.pathname : "page");
        var loginFocusTarget = null;

        var privateMarker = "<!--private-->";
        var fullscreenRootClass = "comment-fullscreen-open";
        var activeEmojiClose = null;

        (function setupCommentEditButtons() {
            var buttons = Array.prototype.slice.call(comments.querySelectorAll("[data-comment-edit][data-comment-coid]"));
            if (!buttons || buttons.length === 0) {
                return;
            }

            buttons.forEach(function (btn) {
                btn.addEventListener("click", function (e) {
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }
                    openCommentEdit(btn);
                });
            });

            comments.addEventListener("click", function (e) {
                var t = e && e.target;
                if (!t || !t.closest) {
                    return;
                }
                var replyLink = t.closest(".comment-reply a");
                if (!replyLink) {
                    return;
                }
                window.setTimeout(function () {
                    clearCommentEditState();
                }, 0);
            });
        })();

        var EMOJIS = [
            "😀", "😃", "😄", "😁", "😆", "😅", "🤣", "😂", "🙂", "🙃", "😉", "😊", "😇", "🥰", "😍", "🤩", "😘", "😗", "😚", "😙", "😋", "😛", "😜", "🤪", "😝", "🤑",
            "🤗", "🤭", "🤫", "🤔", "🤐", "🤨", "😐", "😑", "😶", "😏", "😒", "🙄", "😬", "🤥", "😌", "😔", "😪", "🤤", "😴", "😷", "🤒", "🤕", "🤢", "🤮", "🤧",
            "🥵", "🥶", "🥴", "😵", "🤯", "🤠", "🥳", "😎", "🤓", "🧐", "😕", "😟", "🙁", "☹️", "😮", "😯", "😲", "😳", "🥺", "😦", "😧", "😨", "😰", "😥",
            "😢", "😭", "😱", "😖", "😣", "😞", "😓", "😩", "😫", "🥱", "😤", "😡", "😠", "🤬", "😈", "👿", "💀", "☠️", "💩", "🤡", "👻", "👽", "🤖",

            "👍", "👎", "👌", "🤌", "🤏", "✌️", "🤞", "🤟", "🤘", "🤙", "👈", "👉", "👆", "👇", "☝️", "✋", "🤚", "🖐️", "🖖", "👋", "🤝", "🙏", "👏", "🙌", "💪", "🫶", "🫡",

            "❤️", "🧡", "💛", "💚", "💙", "💜", "🤎", "🖤", "🤍", "💔", "💕", "💞", "💓", "💗", "💖", "💘", "💝", "💟", "✨", "🔥", "🌟", "💫", "🎉", "🎊", "💯", "✅", "❌", "⚠️", "❗", "❓", "💡",

            "🐶", "🐱", "🐭", "🐹", "🐰", "🦊", "🐻", "🐼", "🐨", "🐯", "🦁", "🐮", "🐷", "🐸", "🐵", "🐔", "🐧", "🐦", "🐤", "🦆", "🦉", "🦇", "🐺", "🦄",
            "🐝", "🐛", "🦋", "🐌", "🐞", "🐢", "🐍", "🦖", "🦕", "🐙", "🦑", "🦀", "🐡", "🐠", "🐟", "🐬", "🦈", "🐳", "🐋",

            "🍎", "🍐", "🍊", "🍋", "🍌", "🍉", "🍇", "🍓", "🫐", "🍒", "🥝", "🍑", "🥭", "🍍", "🥥", "🥑", "🍅", "🥕", "🌽", "🥔", "🍠", "🍞", "🥐", "🥯", "🥖", "🧀",
            "🥚", "🍳", "🥞", "🧇", "🥓", "🍗", "🍖", "🌭", "🍔", "🍟", "🍕", "🥪", "🥙", "🌮", "🌯", "🍣", "🍱", "🍜", "🍝", "🍛", "🍙", "🍚", "🍘", "🍥", "🥟", "🍤",
            "🍦", "🍨", "🍰", "🎂", "🍪", "🍩", "🍫", "🍬", "🍭", "☕", "🫖", "🍵", "🥤", "🧋", "🍺", "🍻",

            "⚽", "🏀", "🏈", "⚾", "🎾", "🏐", "🏉", "🥏", "🎱", "🏓", "🏸", "🥊", "🏆", "🥇", "🥈", "🥉",

            "📌", "📍", "🧭", "🗺️", "🚗", "🚕", "🚌", "🚇", "✈️", "🛫", "🛬", "🚀", "🛰️", "🌍", "🌙", "☀️", "⛅", "🌧️", "❄️",

            "📝", "✏️", "🖊️", "📷", "📸", "🎥", "📺", "📱", "💻", "⌨️", "🖥️", "🖨️", "🔋", "🔌", "💾", "💿", "📀", "🎧", "🎵", "🎶",

            "🏠", "🏡", "🏢", "🏪", "🏫", "⛪", "🕌", "🛕", "🕍", "⛩️", "🏯", "🏰"
        ];

        function trimText(value) {
            return String(value || "").replace(/\s+/g, " ").trim();
        }

        function readInputValue(input) {
            if (!input) {
                return "";
            }
            return trimText(input.value || "");
        }

        function loadIdentity() {
            try {
                var raw = sessionStorage.getItem(identityKey);
                if (!raw) {
                    return null;
                }
                var parsed = JSON.parse(raw);
                if (!parsed || typeof parsed !== "object") {
                    return null;
                }
                return {
                    author: trimText(parsed.author || ""),
                    url: trimText(parsed.url || ""),
                    mail: trimText(parsed.mail || "")
                };
            } catch (e) {
                return null;
            }
        }

        function saveIdentity(id) {
            try {
                sessionStorage.setItem(identityKey, JSON.stringify({
                    author: trimText(id && id.author ? id.author : ""),
                    url: trimText(id && id.url ? id.url : ""),
                    mail: trimText(id && id.mail ? id.mail : "")
                }));
            } catch (e) {}
        }

        function readIdentityFromForms() {
            if (!forms || forms.length === 0) {
                return null;
            }
            var f = forms[0];
            if (!f || !f.querySelector) {
                return null;
            }
            var author = readInputValue(f.querySelector("input[name=\"author\"]"));
            var url = readInputValue(f.querySelector("input[name=\"url\"]"));
            var mail = readInputValue(f.querySelector("input[name=\"mail\"]"));
            if (!author && !url && !mail) {
                return null;
            }
            return { author: author, url: url, mail: mail };
        }

        function applyIdentityToForm(form, id) {
            if (!form || !id) {
                return;
            }
            var author = form.querySelector("input[name=\"author\"]");
            var url = form.querySelector("input[name=\"url\"]");
            var mail = form.querySelector("input[name=\"mail\"]");

            if (author) author.value = id.author || "";
            if (url) url.value = id.url || "";
            if (mail) mail.value = id.mail || "";
        }

        function applyIdentityToAllForms(id) {
            if (!forms || forms.length === 0 || !id) {
                return;
            }
            forms.forEach(function (f) {
                applyIdentityToForm(f, id);
            });
        }

        function syncModalInputs(id) {
            if (!modal || !id) {
                return;
            }
            var nameInput = modal.querySelector("#login-name");
            var urlInput = modal.querySelector("#login-url");
            var mailInput = modal.querySelector("#login-mail");
            if (nameInput) nameInput.value = id.author || "";
            if (urlInput) urlInput.value = id.url || "";
            if (mailInput) mailInput.value = id.mail || "";
        }

        // Restore identity into hidden comment fields (so guest comments can submit without extra fields).
        (function restoreIdentityToForms() {
            var id = loadIdentity();
            if (!id) {
                id = readIdentityFromForms();
            }
            if (!id) {
                return;
            }
            applyIdentityToAllForms(id);
        })();

        function openLogin(saveDraftFn, focusTarget) {
            if (!modal || !root) {
                return;
            }
            if (typeof saveDraftFn === "function") {
                saveDraftFn();
            }
            loginFocusTarget = focusTarget || null;

            // Ensure modal inputs reflect the last saved identity.
            var id = loadIdentity();
            if (!id) {
                id = readIdentityFromForms();
            }
            if (id) {
                syncModalInputs(id);
            }

            root.classList.add(loginOpenClass);
            modal.setAttribute("aria-hidden", "false");

            var nameInput = modal.querySelector("#login-name");
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

            if (loginFocusTarget) {
                var target = loginFocusTarget;
                loginFocusTarget = null;
                window.setTimeout(function () {
                    try {
                        target.focus();
                    } catch (e) {}
                }, 0);
            }
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

        // Header/mobile "login" links: open the same modal instead of navigating to /admin/login.php.
        (function setupHeaderLoginModalTriggers() {
            var triggers = document.querySelectorAll("[data-open-login-modal]");
            if (!triggers || triggers.length === 0) {
                return;
            }
            triggers.forEach(function (el) {
                el.addEventListener("click", function (e) {
                    if (!modal) {
                        return;
                    }
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }
                    openLogin(null, el);
                });
            });
        })();

        // The "login" modal doubles as the guest identity editor:
        // - If password is empty: save author/url/mail and close.
        // - If password is filled: submit to Typecho login action as usual.
        (function setupLoginIdentityForm() {
            if (!modal) {
                return;
            }
            var form = modal.querySelector("form");
            if (!form) {
                return;
            }

            var nameInput = modal.querySelector("#login-name");
            var urlInput = modal.querySelector("#login-url");
            var mailInput = modal.querySelector("#login-mail");
            var passInput = modal.querySelector("#login-pass");
            var submitBtn = form.querySelector(".login-modal-submit");

            function updateSubmitLabel() {
                if (!submitBtn) {
                    return;
                }
                var password = readInputValue(passInput);
                var next = password ? "登录" : "保存";
                // Avoid touching the DOM if nothing changes.
                if (submitBtn.textContent !== next) {
                    submitBtn.textContent = next;
                }
                submitBtn.setAttribute("aria-label", next);
                submitBtn.setAttribute("title", next);
            }

            if (passInput) {
                passInput.addEventListener("input", updateSubmitLabel);
                passInput.addEventListener("change", updateSubmitLabel);
            }
            // Browser autofill may not trigger input events; set initial state.
            updateSubmitLabel();

            function focusFirstMissing(id) {
                if (!id.author && nameInput) {
                    try { nameInput.focus(); } catch (e) {}
                    return true;
                }
                if (!commentsUserLogged && commentsRequireUrl && !id.url && urlInput) {
                    try { urlInput.focus(); } catch (e) {}
                    return true;
                }
                if (!commentsUserLogged && commentsRequireMail && !id.mail && mailInput) {
                    try { mailInput.focus(); } catch (e) {}
                    return true;
                }
                return false;
            }

            form.addEventListener("submit", function (e) {
                var password = readInputValue(passInput);
                if (password) {
                    return;
                }

                if (e && e.preventDefault) {
                    e.preventDefault();
                }

                var id = {
                    author: readInputValue(nameInput),
                    url: readInputValue(urlInput),
                    mail: readInputValue(mailInput)
                };

                if (focusFirstMissing(id)) {
                    return;
                }

                saveIdentity(id);
                applyIdentityToAllForms(id);
                if (passInput) {
                    passInput.value = "";
                }
                updateSubmitLabel();
                closeLogin();
            });
        })();

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
            var box = form.querySelector("[data-comment-box]");
            if (!textarea || !box) {
                return;
            }

            var role = form.getAttribute("data-comment-role") || "default";
            var isTop = role === "top";
            var isUserLogged = form.getAttribute("data-user-logged") === "1";

            var authorInput = form.querySelector("input[name=\"author\"]");
            var urlInput = form.querySelector("input[name=\"url\"]");
            var mailInput = form.querySelector("input[name=\"mail\"]");

            var privateBtn = form.querySelector("[data-comment-private-toggle]");
            var fullscreenBtn = form.querySelector("[data-comment-fullscreen-toggle]");
            var loginBtn = form.querySelector("[data-open-login]");
            var emojiBtn = form.querySelector(".comment-emoji");
            var attachBtn = form.querySelector(".comment-attach");

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

            var draftKey = "comment_draft_" + role + "_" + (location && location.pathname ? location.pathname : "page");
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

            var emojiPicker = null;
            var emojiDocHandlersOn = false;

            function ensureEmojiPicker() {
                if (emojiPicker) {
                    return emojiPicker;
                }
                if (!document || !box) {
                    return null;
                }

                var picker = document.createElement("div");
                picker.className = "emoji-picker";
                picker.hidden = true;
                picker.setAttribute("role", "dialog");
                picker.setAttribute("aria-label", "表情");
                picker.setAttribute("data-emoji-picker", "");

                var grid = document.createElement("div");
                grid.className = "emoji-picker-grid";
                grid.setAttribute("role", "listbox");
                grid.setAttribute("aria-label", "表情列表");

                var html = "";
                for (var i = 0; i < EMOJIS.length; i++) {
                    var emo = EMOJIS[i];
                    html += "<button type=\"button\" class=\"emoji-picker-btn\" data-emoji=\"" + emo + "\" aria-label=\"" + emo + "\">" + emo + "</button>";
                }
                grid.innerHTML = html;
                picker.appendChild(grid);

                picker.addEventListener("click", function (e) {
                    var t = e && e.target;
                    if (!t || !t.closest) {
                        return;
                    }
                    var btn = t.closest("[data-emoji]");
                    if (!btn) {
                        return;
                    }
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }
                    var emo = btn.getAttribute("data-emoji") || "";
                    insertEmoji(emo);
                    closeEmojiPicker();
                });

                box.appendChild(picker);
                emojiPicker = picker;
                return emojiPicker;
            }

            function insertEmoji(emoji) {
                if (!emoji) {
                    return;
                }
                try {
                    textarea.focus();
                } catch (e) {}

                var start;
                var end;
                try {
                    start = typeof textarea.selectionStart === "number" ? textarea.selectionStart : (textarea.value || "").length;
                    end = typeof textarea.selectionEnd === "number" ? textarea.selectionEnd : (textarea.value || "").length;
                } catch (e) {
                    start = (textarea.value || "").length;
                    end = start;
                }

                try {
                    if (typeof textarea.setRangeText === "function" && typeof start === "number" && typeof end === "number") {
                        textarea.setRangeText(emoji, start, end, "end");
                    } else {
                        var v = textarea.value || "";
                        var before = v.slice(0, start);
                        var after = v.slice(end);
                        textarea.value = before + emoji + after;
                        var pos = before.length + emoji.length;
                        textarea.selectionStart = pos;
                        textarea.selectionEnd = pos;
                    }
                } catch (e) {
                    textarea.value = (textarea.value || "") + emoji;
                }

                autoGrowTextarea();
                saveDraft();
            }

            var attachInput = null;
            var attachPreview = null;
            var attachSnippet = "";
            var attachUploading = false;

            function getCommentUploadEndpoint() {
                var raw = "";
                try {
                    raw = (window && window.location && window.location.href) ? String(window.location.href) : "";
                } catch (e) {
                    raw = "";
                }

                if (!raw) {
                    return "?comment_upload=1";
                }

                try {
                    var u = new URL(raw);
                    u.searchParams.set("comment_upload", "1");
                    return u.toString();
                } catch (e) {
                    var clean = raw;
                    var hashPos = clean.indexOf("#");
                    if (hashPos >= 0) {
                        clean = clean.slice(0, hashPos);
                    }
                    return clean + (clean.indexOf("?") === -1 ? "?" : "&") + "comment_upload=1";
                }
            }

            function getCommentUploadToken() {
                var tokenInput = form.querySelector("input[name=\"_\"]");
                if (!tokenInput && comments && comments.querySelector) {
                    tokenInput = comments.querySelector("input[name=\"_\"]");
                }
                var token = tokenInput && tokenInput.value ? String(tokenInput.value).trim() : "";
                return token;
            }

            function setAttachBusy(isBusy) {
                attachUploading = !!isBusy;
                if (!attachBtn) {
                    return;
                }
                attachBtn.disabled = attachUploading;
                attachBtn.setAttribute("aria-busy", attachUploading ? "true" : "false");
            }

            function normalizeAttachmentName(name) {
                var safe = String(name || "附件")
                    .replace(/[\r\n]+/g, " ")
                    .replace(/[\[\]\(\)]+/g, " ")
                    .replace(/\s+/g, " ")
                    .trim();
                return safe || "附件";
            }

            function buildAttachmentSnippet(data) {
                var url = data && data.url ? String(data.url).trim() : "";
                if (!url) {
                    return "";
                }
                var name = normalizeAttachmentName(data && data.name ? data.name : "附件");
                return data && data.isImage ? ("![" + name + "](" + url + ")") : ("[" + name + "](" + url + ")");
            }

            function insertAttachmentSnippet(snippet) {
                if (!snippet) {
                    return "";
                }

                var value = textarea.value || "";
                var start;
                var end;
                try {
                    start = typeof textarea.selectionStart === "number" ? textarea.selectionStart : value.length;
                    end = typeof textarea.selectionEnd === "number" ? textarea.selectionEnd : value.length;
                } catch (e) {
                    start = value.length;
                    end = value.length;
                }

                var beforeChar = start > 0 ? value.charAt(start - 1) : "";
                var afterChar = end < value.length ? value.charAt(end) : "";
                var insertText = snippet;
                if (beforeChar && beforeChar !== "\n") {
                    insertText = "\n" + insertText;
                }
                if (afterChar && afterChar !== "\n") {
                    insertText += "\n";
                }

                try {
                    textarea.focus();
                } catch (e) {}

                try {
                    if (typeof textarea.setRangeText === "function" && typeof start === "number" && typeof end === "number") {
                        textarea.setRangeText(insertText, start, end, "end");
                    } else {
                        var before = value.slice(0, start);
                        var after = value.slice(end);
                        textarea.value = before + insertText + after;
                        var pos = before.length + insertText.length;
                        textarea.selectionStart = pos;
                        textarea.selectionEnd = pos;
                    }
                } catch (e) {
                    textarea.value = value + insertText;
                }

                autoGrowTextarea();
                saveDraft();
                return insertText;
            }

            function removeAttachmentSnippetIfAny() {
                if (!attachSnippet) {
                    return;
                }

                var value = textarea.value || "";
                var target = attachSnippet;
                var idx = value.lastIndexOf(target);

                if (idx === -1) {
                    var trimmed = target.replace(/^\n+|\n+$/g, "");
                    if (trimmed) {
                        idx = value.lastIndexOf(trimmed);
                        target = trimmed;
                    }
                }

                if (idx !== -1) {
                    textarea.value = value.slice(0, idx) + value.slice(idx + target.length);
                    textarea.value = (textarea.value || "").replace(/\n{3,}/g, "\n\n");
                    autoGrowTextarea();
                    saveDraft();
                }

                attachSnippet = "";
            }

            function clearAttachmentPreview(removeSnippet) {
                if (removeSnippet) {
                    removeAttachmentSnippetIfAny();
                }

                if (attachPreview && attachPreview.parentNode) {
                    attachPreview.parentNode.removeChild(attachPreview);
                }
                attachPreview = null;
                box.classList.remove("has-attachment-preview");
            }

            function getAttachmentFileLabel(data) {
                var name = data && data.name ? String(data.name) : "";
                var m = name.match(/\.([A-Za-z0-9]{1,8})$/);
                if (m && m[1]) {
                    return m[1].toUpperCase();
                }
                return data && data.isImage ? "IMG" : "FILE";
            }

            function renderAttachmentPreview(data) {
                clearAttachmentPreview(false);

                if (!document || !box || !data || !data.url) {
                    return;
                }

                var preview = document.createElement("div");
                preview.className = "comment-attachment-preview";
                preview.setAttribute("data-comment-attachment-preview", "");

                var link = document.createElement("a");
                link.className = "comment-attachment-preview-link";
                link.href = String(data.url);
                link.target = "_blank";
                link.rel = "noopener noreferrer";
                link.setAttribute("aria-label", normalizeAttachmentName(data.name || "附件"));

                if (data.isImage) {
                    var img = document.createElement("img");
                    img.className = "comment-attachment-preview-img";
                    img.src = String(data.url);
                    img.alt = normalizeAttachmentName(data.name || "附件");
                    img.loading = "lazy";
                    link.appendChild(img);
                } else {
                    var fileTag = document.createElement("span");
                    fileTag.className = "comment-attachment-preview-file";
                    fileTag.textContent = getAttachmentFileLabel(data);
                    link.appendChild(fileTag);
                }

                var removeBtn = document.createElement("button");
                removeBtn.type = "button";
                removeBtn.className = "comment-attachment-preview-remove";
                removeBtn.setAttribute("aria-label", "删除附件");
                removeBtn.textContent = "×";
                removeBtn.addEventListener("click", function (e) {
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }
                    if (e && e.stopPropagation) {
                        e.stopPropagation();
                    }
                    clearAttachmentPreview(true);
                });

                preview.appendChild(link);
                preview.appendChild(removeBtn);
                box.appendChild(preview);

                attachPreview = preview;
                box.classList.add("has-attachment-preview");
            }

            function ensureAttachInput() {
                if (attachInput) {
                    return attachInput;
                }
                if (!document || !form) {
                    return null;
                }

                var input = document.createElement("input");
                input.type = "file";
                input.hidden = true;
                input.tabIndex = -1;
                input.setAttribute("aria-hidden", "true");
                input.accept = "image/*,.pdf,.txt,.md,.zip,.rar,.7z,.tar,.gz,.mp3,.wav,.ogg,.mp4,.webm,.mov";
                input.addEventListener("change", function () {
                    var file = input.files && input.files[0] ? input.files[0] : null;
                    if (!file) {
                        return;
                    }
                    uploadAttachment(file);
                });

                form.appendChild(input);
                attachInput = input;
                return attachInput;
            }

            function parseUploadJson(text) {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    return null;
                }
            }

            function uploadAttachment(file) {
                if (!file || attachUploading || !window.fetch || !window.FormData) {
                    return;
                }

                var token = getCommentUploadToken();
                if (!token) {
                    try {
                        window.alert("安全令牌缺失，请刷新页面后再试");
                    } catch (e) {}
                    return;
                }

                var endpoint = getCommentUploadEndpoint();
                var formData = new FormData();
                formData.append("file", file);
                formData.append("_", token);

                setAttachBusy(true);

                window.fetch(endpoint, {
                    method: "POST",
                    body: formData,
                    credentials: "same-origin",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                }).then(function (response) {
                    return response.text().then(function (text) {
                        var payload = parseUploadJson(text);
                        if (!response.ok || !payload || !payload.ok) {
                            var errMsg = payload && payload.message ? String(payload.message) : "附件上传失败，请重试";
                            throw new Error(errMsg);
                        }
                        return payload;
                    });
                }).then(function (payload) {
                    clearAttachmentPreview(true);
                    var snippet = buildAttachmentSnippet(payload);
                    if (!snippet) {
                        throw new Error("附件地址无效");
                    }
                    attachSnippet = insertAttachmentSnippet(snippet);
                    renderAttachmentPreview(payload);
                }).catch(function (err) {
                    var msg = err && err.message ? String(err.message) : "附件上传失败，请重试";
                    try {
                        window.alert(msg);
                    } catch (e) {}
                }).finally(function () {
                    if (attachInput) {
                        attachInput.value = "";
                    }
                    setAttachBusy(false);
                });
            }

            function onEmojiDocMouseDown(e) {
                var t = e && e.target;
                if (!t) {
                    return;
                }
                if (emojiBtn && (t === emojiBtn || (emojiBtn.contains && emojiBtn.contains(t)))) {
                    return;
                }
                if (emojiPicker && (t === emojiPicker || (emojiPicker.contains && emojiPicker.contains(t)))) {
                    return;
                }
                closeEmojiPicker();
            }

            function onEmojiDocKeyDown(e) {
                var key = e && (e.key || e.code);
                if (key === "Escape" || key === "Esc") {
                    closeEmojiPicker();
                }
            }

            function setEmojiPickerOpen(isOn) {
                if (!emojiBtn) {
                    return;
                }
                var picker = ensureEmojiPicker();
                if (!picker) {
                    return;
                }

                var currentlyOpen = !picker.hidden;
                if (!!isOn === currentlyOpen) {
                    return;
                }

                if (isOn && typeof activeEmojiClose === "function" && activeEmojiClose !== closeEmojiPicker) {
                    try {
                        activeEmojiClose();
                    } catch (e) {}
                }

                picker.hidden = !isOn;
                box.classList.toggle("is-emoji-open", !!isOn);
                emojiBtn.setAttribute("aria-expanded", isOn ? "true" : "false");

                if (isOn) {
                    activeEmojiClose = closeEmojiPicker;
                    if (!emojiDocHandlersOn) {
                        emojiDocHandlersOn = true;
                        document.addEventListener("mousedown", onEmojiDocMouseDown, true);
                        window.addEventListener("keydown", onEmojiDocKeyDown, true);
                    }
                } else {
                    if (activeEmojiClose === closeEmojiPicker) {
                        activeEmojiClose = null;
                    }
                    if (emojiDocHandlersOn) {
                        emojiDocHandlersOn = false;
                        document.removeEventListener("mousedown", onEmojiDocMouseDown, true);
                        window.removeEventListener("keydown", onEmojiDocKeyDown, true);
                    }
                }
            }

            function closeEmojiPicker() {
                setEmojiPickerOpen(false);
            }

            if (loginBtn) {
                loginBtn.addEventListener("click", function (e) {
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }
                    openLogin(saveDraft, textarea);
                });
            }

            if (emojiBtn) {
                emojiBtn.setAttribute("aria-haspopup", "dialog");
                emojiBtn.setAttribute("aria-expanded", "false");
                emojiBtn.addEventListener("click", function (e) {
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }
                    var picker = ensureEmojiPicker();
                    var isOn = picker && !picker.hidden;
                    setEmojiPickerOpen(!isOn);
                });
            }

            if (attachBtn) {
                attachBtn.addEventListener("click", function (e) {
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }
                    if (!window.fetch || !window.FormData) {
                        try {
                            window.alert("当前浏览器不支持附件上传");
                        } catch (err) {}
                        return;
                    }
                    var input = ensureAttachInput();
                    if (!input) {
                        return;
                    }
                    try {
                        input.click();
                    } catch (err) {}
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
                var editCoid = parseInt(form.getAttribute("data-comment-edit-coid") || "0", 10);
                if (isFinite(editCoid) && editCoid > 0) {
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }
                    var editText = textarea.value || "";
                    if (isPrivate) {
                        var editTrimmed = editText.replace(/^\s+/, "");
                        if (editTrimmed.indexOf(privateMarker) !== 0) {
                            editText = privateMarker + "\n" + editText;
                        }
                    }
                    clearDraft();
                    submitCommentEdit(form, editCoid, editText);
                    return;
                }

                if (!isUserLogged) {
                    var author = readInputValue(authorInput);
                    var url = readInputValue(urlInput);
                    var mail = readInputValue(mailInput);
                    if (!author || (commentsRequireUrl && !url) || (commentsRequireMail && !mail)) {
                        if (e && e.preventDefault) {
                            e.preventDefault();
                        }
                        openLogin(saveDraft, textarea);
                        return;
                    }
                }

                if (isPrivate) {
                    var value = textarea.value || "";
                    var trimmed = value.replace(/^\s+/, "");
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

/* block 11 */
(function () {
        var blocks = document.querySelectorAll(".links-apply");
        if (!blocks || blocks.length === 0) {
            return;
        }

        function setup(block) {
            if (!block || !block.querySelector) {
                return;
            }

            var stepCheck = block.querySelector(".links-step-check");
            var stepForm = block.querySelector(".links-step-form");
            if (!stepCheck || !stepForm) {
                return;
            }

            var cbs = Array.prototype.slice.call(stepCheck.querySelectorAll(".links-check-input"));
            if (!cbs || cbs.length === 0) {
                return;
            }

            function allChecked() {
                for (var i = 0; i < cbs.length; i++) {
                    if (!cbs[i] || !cbs[i].checked) {
                        return false;
                    }
                }
                return true;
            }

            function setFormEnabled(enabled) {
                var form = stepForm.querySelector("form");
                if (!form) {
                    return;
                }
                var controls = form.querySelectorAll("input, select, textarea, button");
                for (var i = 0; i < controls.length; i++) {
                    var el = controls[i];
                    if (!el) {
                        continue;
                    }
                    if (el.tagName === "INPUT" && String(el.type || "").toLowerCase() === "hidden") {
                        continue;
                    }
                    try {
                        el.disabled = !enabled;
                    } catch (e) {}
                }
            }

            function sync() {
                var ok = allChecked();
                stepForm.hidden = !ok;
                stepForm.setAttribute("aria-hidden", ok ? "false" : "true");
                if (btn) {
                    try {
                        btn.disabled = !!ok;
                        btn.setAttribute("aria-disabled", ok ? "true" : "false");
                    } catch (e) {}
                }
                setFormEnabled(ok);
            }

            var btn = stepCheck.querySelector("[data-links-confirm]");
            if (btn && btn.addEventListener) {
                btn.addEventListener("click", function (e) {
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }
                    for (var i = 0; i < cbs.length; i++) {
                        try {
                            cbs[i].checked = true;
                        } catch (err) {}
                    }
                    sync();
                });
            }

            cbs.forEach(function (cb) {
                if (!cb || !cb.addEventListener) {
                    return;
                }
                cb.addEventListener("change", sync);
            });

            sync();
        }

        Array.prototype.slice.call(blocks).forEach(setup);
    })();
