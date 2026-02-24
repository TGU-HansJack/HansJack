<?php if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
} ?>
</div>

<footer class="hj-footer" id="hj-footer">
    <div class="hj-shell">
        <div class="hj-footer-row">
            <p class="hj-footer-left">
                &copy; <?php echo date('Y'); ?>
                <a href="<?php $this->options->siteUrl(); ?>"><?php $this->options->title(); ?></a>
                <?php
                $hjIcpBeian = trim((string) $this->options->hjIcpBeian);
                $hjMpsBeian = trim((string) $this->options->hjMpsBeian);
                $hjFooterCustomCode = trim((string) $this->options->hjFooterCustomCode);
                ?>
                <?php if ($hjIcpBeian !== ''): ?>
                    <span class="hj-footer-sep" aria-hidden="true">·</span>
                    <a class="hj-footer-beian" href="https://beian.miit.gov.cn/" target="_blank" rel="noreferrer"><?php echo hansJackEscape($hjIcpBeian); ?></a>
                <?php endif; ?>
                <?php if ($hjMpsBeian !== ''): ?>
                    <span class="hj-footer-sep" aria-hidden="true">·</span>
                    <a class="hj-footer-beian" href="<?php echo hansJackEscape(hansJackBuildMpsBeianUrl($hjMpsBeian)); ?>" target="_blank" rel="noreferrer"><?php echo hansJackEscape($hjMpsBeian); ?></a>
                <?php endif; ?>
                <?php if ($hjFooterCustomCode !== ''): ?>
                    <span class="hj-footer-sep" aria-hidden="true">·</span>
                    <?php echo $hjFooterCustomCode; ?>
                <?php endif; ?>
            </p>
            <p class="hj-footer-right">
                <code class="hj-footer-power">
                    <span>Powered by</span>
                    <a href="https://typecho.org/" aria-label="Typecho" target="_blank" rel="noreferrer" class="hj-footer-icon-link">
                        <svg class="hj-footer-icon hj-footer-icon-typecho" viewBox="0 0 1024 1024" aria-hidden="true" focusable="false">
                            <path d="M512 1024C132.647385 1024 0 891.313231 0 512S132.647385 0 512 0s512 132.686769 512 512-132.647385 512-512 512zM236.307692 354.461538h551.384616V275.692308H236.307692v78.76923z m0 196.923077h393.846154v-78.76923H236.307692v78.76923z m0 196.923077h472.615385v-78.76923H236.307692v78.76923z" fill="currentColor"></path>
                        </svg>
                    </a>
                    <span class="hj-footer-sep" aria-hidden="true">·</span>
                    <a href="https://github.com/tuyuritio/astro-theme-thought-lite" aria-label="移植+二改主题: ThoughtLite" target="_blank" rel="noreferrer" class="hj-footer-icon-link hj-footer-theme-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hj-footer-icon hj-footer-icon-github" aria-hidden="true" focusable="false"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/><path d="M9 18c-4.51 2-5-2-7-2"/></svg>
                        <span class="hj-footer-icon-tip" aria-hidden="true">模范主题: ThoughtLite</span>
                    </a>
                </code>
            </p>
        </div>
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
    <button class="hj-fab-btn hj-fab-settings" type="button" aria-label="<?php _e('设置'); ?>" title="<?php _e('设置'); ?>" aria-haspopup="true" aria-expanded="false">
        <span class="hj-fab-ring" aria-hidden="true">
            <svg class="hj-fab-ring-svg" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                <circle class="hj-fab-ring-bg" cx="20" cy="20" r="18"></circle>
                <circle class="hj-fab-ring-fg" cx="20" cy="20" r="18"></circle>
            </svg>
        </span>
        <span class="hj-fab-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sliders-vertical-icon lucide-sliders-vertical" aria-hidden="true"><path d="M10 8h4"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M17 16h4"/><path d="M19 12V3"/><path d="M19 21v-5"/><path d="M3 14h4"/><path d="M5 10V3"/><path d="M5 21v-7"/></svg>
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

<div class="hj-fab-settings-popover" data-hj-posts-settings-popover aria-hidden="true" hidden>
    <div class="hj-fab-settings-panel" role="dialog" aria-modal="true" aria-label="<?php _e('设置'); ?>" data-hj-posts-settings-panel>
        <div class="hj-fab-settings-row">
            <span class="hj-fab-settings-label"><?php _e('排序模式'); ?></span>
            <select class="hj-fab-settings-select" data-hj-posts-setting-sort-mode>
                <option value="default"><?php _e('默认'); ?></option>
                <option value="published"><?php _e('按照发布时间'); ?></option>
                <option value="updated"><?php _e('按照更新时间'); ?></option>
            </select>
        </div>
        <div class="hj-fab-settings-row">
            <span class="hj-fab-settings-label"><?php _e('顺序'); ?></span>
            <select class="hj-fab-settings-select" data-hj-posts-setting-order>
                <option value="desc"><?php _e('降序'); ?></option>
                <option value="asc"><?php _e('升序'); ?></option>
            </select>
        </div>
        <div class="hj-fab-settings-row">
            <span class="hj-fab-settings-label"><?php _e('列表模式'); ?></span>
            <select class="hj-fab-settings-select" data-hj-posts-setting-list-mode>
                <option value="compact"><?php _e('紧凑模式'); ?></option>
                <option value="preview"><?php _e('预览模式'); ?></option>
            </select>
        </div>
    </div>
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
        var settingsBtn = fab.querySelector(".hj-fab-settings");
        var commentBtn = fab.querySelector(".hj-fab-comment");
        var tocBtn = fab.querySelector(".hj-fab-toc");
        var settingsPopover = document.querySelector("[data-hj-posts-settings-popover]");
        var settingsPanel = settingsPopover ? settingsPopover.querySelector("[data-hj-posts-settings-panel]") : null;
        var settingsSortModeSelect = settingsPopover ? settingsPopover.querySelector("[data-hj-posts-setting-sort-mode]") : null;
        var settingsOrderSelect = settingsPopover ? settingsPopover.querySelector("[data-hj-posts-setting-order]") : null;
        var settingsListModeSelect = settingsPopover ? settingsPopover.querySelector("[data-hj-posts-setting-list-mode]") : null;
        var tocBackdrop = document.querySelector("[data-hj-mobile-toc-backdrop]");
        var tocCloseBtn = document.querySelector(".hj-article-toc-close");
        var tocOpenClass = "hj-mobile-toc-open";
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
            return document.querySelector(".hj-article-layout.is-has-toc");
        }

        function findCommentTarget() {
            return (
                // Prefer the always-visible top composer; the built-in respond box is hidden
                // until it is moved into a comment body for replying.
                document.querySelector("[data-hj-comment-role=\"top\"]") ||
                document.querySelector("#comments") ||
                document.querySelector("[data-hj-comment-respond]") ||
                document.querySelector("#respond") ||
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

        var postsSettingsStorageKey = "hj_posts_list_settings_v1";
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

        function syncPostsSettingsUI() {
            if (!postsSettings) {
                return;
            }
            if (settingsSortModeSelect) {
                settingsSortModeSelect.value = postsSettings.sortMode;
            }
            if (settingsOrderSelect) {
                settingsOrderSelect.value = postsSettings.order;
            }
            if (settingsListModeSelect) {
                settingsListModeSelect.value = postsSettings.listMode;
            }
        }

        function findPostsLists() {
            return Array.prototype.slice.call(document.querySelectorAll(".hj-posts-list"));
        }

        function ensureOriginalOrder(items) {
            if (!items || items.length === 0) {
                return;
            }
            items.forEach(function (item, idx) {
                if (!item || !item.setAttribute) {
                    return;
                }
                if (!item.hasAttribute("data-hj-post-original-index")) {
                    item.setAttribute("data-hj-post-original-index", String(idx));
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

            var items = Array.prototype.slice.call(list.querySelectorAll(".hj-posts-item"));
            if (!items || items.length === 0) {
                return;
            }

            ensureOriginalOrder(items);

            var sorted = items.slice();
            var mode = postsSettings.sortMode;
            var asc = postsSettings.order === "asc";

            sorted.sort(function (a, b) {
                var aIdx = readIntAttr(a, "data-hj-post-original-index");
                var bIdx = readIntAttr(b, "data-hj-post-original-index");

                if (mode === "default") {
                    // Preserve server order; "asc" just reverses the original list.
                    return asc ? bIdx - aIdx : aIdx - bIdx;
                }

                var aVal = 0;
                var bVal = 0;
                if (mode === "published") {
                    aVal = readIntAttr(a, "data-hj-post-created");
                    bVal = readIntAttr(b, "data-hj-post-created");
                } else if (mode === "updated") {
                    aVal = readIntAttr(a, "data-hj-post-modified") || readIntAttr(a, "data-hj-post-created");
                    bVal = readIntAttr(b, "data-hj-post-modified") || readIntAttr(b, "data-hj-post-created");
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
                text = String(item.getAttribute("data-hj-post-excerpt") || "");
            } catch (e) {
                text = "";
            }
            text = text.trim();
            if (!text) {
                return;
            }

            var existing = item.querySelector("[data-hj-posts-excerpt]");
            if (existing) {
                existing.textContent = text;
                return;
            }

            var node = document.createElement("div");
            node.className = "hj-posts-excerpt";
            node.setAttribute("data-hj-posts-excerpt", "true");
            node.textContent = text;

            var left = item.querySelector(".hj-posts-item-left");
            if (!left) {
                item.appendChild(node);
                return;
            }

            var time = left.querySelector(".hj-posts-date");
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
            var nodes = item.querySelectorAll("[data-hj-posts-excerpt]");
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
            var items = Array.prototype.slice.call(list.querySelectorAll(".hj-posts-item"));
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
                root.classList.toggle("hj-posts-preview-mode", postsSettings.listMode === "preview");
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
            var isPostsPage = !!(body && body.classList && body.classList.contains("hj-page-posts"));
            if (isPostsPage) {
                return true;
            }
            if (!isNarrowScreen()) {
                return false;
            }
            return !!document.querySelector(".hj-posts-list");
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

        postsSettings = loadPostsSettings();
        syncPostsSettingsUI();
        applyPostsSettingsToAllLists();
        updateSettingsFabVisibility();

        if (settingsSortModeSelect) {
            settingsSortModeSelect.addEventListener("change", function () {
                savePostsSettings({
                    sortMode: settingsSortModeSelect.value,
                    order: settingsOrderSelect ? settingsOrderSelect.value : postsSettings.order,
                    listMode: settingsListModeSelect ? settingsListModeSelect.value : postsSettings.listMode
                });
                applyPostsSettingsToAllLists();
            });
        }

        if (settingsOrderSelect) {
            settingsOrderSelect.addEventListener("change", function () {
                savePostsSettings({
                    sortMode: settingsSortModeSelect ? settingsSortModeSelect.value : postsSettings.sortMode,
                    order: settingsOrderSelect.value,
                    listMode: settingsListModeSelect ? settingsListModeSelect.value : postsSettings.listMode
                });
                applyPostsSettingsToAllLists();
            });
        }

        if (settingsListModeSelect) {
            settingsListModeSelect.addEventListener("change", function () {
                savePostsSettings({
                    sortMode: settingsSortModeSelect ? settingsSortModeSelect.value : postsSettings.sortMode,
                    order: settingsOrderSelect ? settingsOrderSelect.value : postsSettings.order,
                    listMode: settingsListModeSelect.value
                });
                applyPostsSettingsToAllLists();
                // Ensure the popover doesn't drift after layout changes (excerpt insertion).
                if (isSettingsPopoverOpen()) {
                    window.setTimeout(positionSettingsPopover, 0);
                }
            });
        }

        if (settingsBtn) {
            settingsBtn.addEventListener("click", function () {
                toggleSettingsPopover();
            });
        }

        document.addEventListener("mousedown", function (e) {
            if (!isSettingsPopoverOpen()) {
                return;
            }
            var t = e ? e.target : null;
            if (!t || !t.closest) {
                return;
            }
            if (t.closest(".hj-fab-settings-popover") || t.closest(".hj-fab-settings")) {
                return;
            }
            closeSettingsPopover();
        }, true);

        window.addEventListener("keydown", function (e) {
            if (!isSettingsPopoverOpen()) {
                return;
            }
            var key = e && (e.key || e.code);
            if (key === "Escape" || key === "Esc") {
                closeSettingsPopover();
            }
        });

        window.addEventListener("resize", function () {
            updateSettingsFabVisibility();
            if (isSettingsPopoverOpen()) {
                positionSettingsPopover();
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

        (function setupCommentsToolbar() {
            var refreshBtn = comments.querySelector("[data-hj-comments-refresh]");
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

            var sortBtn = comments.querySelector("[data-hj-comments-sort-toggle]");
            if (!sortBtn) {
                return;
            }

            function getOrder() {
                var v = (comments.getAttribute("data-hj-comments-order") || "asc").toLowerCase();
                return v === "desc" ? "desc" : "asc";
            }

            function setOrder(v) {
                comments.setAttribute("data-hj-comments-order", v === "desc" ? "desc" : "asc");
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

            function cleanText(text) {
                return String(text || "").replace(/\s+/g, " ").trim();
            }

            function shorten(text, maxLen) {
                text = cleanText(text);
                maxLen = parseInt(maxLen || "0", 10);
                if (!isFinite(maxLen) || maxLen <= 0) {
                    return text;
                }
                if (text.length <= maxLen) {
                    return text;
                }
                if (maxLen <= 3) {
                    return text.slice(0, maxLen);
                }
                return text.slice(0, maxLen - 3) + "...";
            }

            function rebuildPreview(detailsEl) {
                if (!detailsEl) {
                    return;
                }
                var preview = detailsEl.querySelector(".hj-comment-children-preview");
                var list = detailsEl.querySelector(".hj-comment-children-full > .comment-list");
                if (!preview || !list) {
                    return;
                }

                var items = Array.prototype.slice.call(list.children || []).filter(function (n) {
                    return n && n.classList && n.classList.contains("comment-body");
                });

                // Keep the same 5-item cap as the server-side render.
                var maxItems = 5;
                items = items.slice(0, maxItems);

                while (preview.firstChild) {
                    preview.removeChild(preview.firstChild);
                }

                items.forEach(function (item) {
                    var authorEl = item.querySelector(".comment-author .fn");
                    var author = cleanText(authorEl ? authorEl.textContent : "");
                    if (!author) {
                        author = "匿名";
                    }

                    var text = "";
                    if (item.classList.contains("is-private-hidden")) {
                        text = "私信内容";
                    } else {
                        var contentEl = item.querySelector(".comment-content");
                        text = cleanText(contentEl ? contentEl.textContent : "");
                        if (!text) {
                            text = "（无内容）";
                        } else {
                            text = shorten(text, 72);
                        }
                    }

                    var row = document.createElement("div");
                    row.className = "hj-comment-children-preview-item";

                    var a = document.createElement("span");
                    a.className = "hj-comment-children-preview-author";
                    a.textContent = author;

                    var sep = document.createElement("span");
                    sep.className = "hj-comment-children-preview-sep";
                    sep.textContent = "：";

                    var t = document.createElement("span");
                    t.className = "hj-comment-children-preview-text";
                    t.textContent = text;

                    row.appendChild(a);
                    row.appendChild(sep);
                    row.appendChild(t);
                    preview.appendChild(row);
                });
            }

            function rebuildAllPreviews() {
                var blocks = Array.prototype.slice.call(comments.querySelectorAll("details.hj-comment-children"));
                blocks.forEach(rebuildPreview);
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
                rebuildAllPreviews();
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

        (function setupCommentChildrenToggleGuard() {
            // Only the "共x条回复 / 收起回复" text can toggle <details>.
            comments.addEventListener("click", function (e) {
                var t = e && e.target;
                if (!t || !t.closest) {
                    return;
                }

                var summary = t.closest("summary.hj-comment-children-summary");
                if (!summary) {
                    return;
                }

                var toggle = t.closest(".hj-comment-children-toggle");
                if (toggle) {
                    return;
                }

                if (e && typeof e.preventDefault === "function") {
                    e.preventDefault();
                }
                if (e && typeof e.stopPropagation === "function") {
                    e.stopPropagation();
                }
            }, true);
        })();

        (function setupCommentAvatarSizing() {
            // Make the avatar diameter match the author-meta two-line height.
            // We also update --hj-comment-avatar-size so all left indents stay aligned.
            var raf = window.requestAnimationFrame || function (fn) { return window.setTimeout(fn, 16); };
            var ticking = false;

            function update() {
                var items = Array.prototype.slice.call(comments.querySelectorAll(".comment-body"));
                if (!items || items.length === 0) {
                    return;
                }

                items.forEach(function (item) {
                    var meta = item.querySelector(".hj-comment-author-meta");
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

                    // Clamp to a reasonable minimum so it doesn't collapse on edge cases.
                    var h = Math.max(24, rect.height);
                    item.style.setProperty("--hj-comment-avatar-size", h.toFixed(2) + "px");
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

            window.addEventListener("resize", requestUpdate);

            // Recompute after expanding/collapsing reply blocks.
            comments.addEventListener("click", function (e) {
                var t = e && e.target;
                if (!t || !t.closest) {
                    return;
                }
                if (!t.closest(".hj-comment-children-toggle")) {
                    return;
                }
                window.setTimeout(requestUpdate, 0);
            }, true);

            requestUpdate();
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

        var commentsUserLogged = comments.getAttribute("data-hj-user-logged") === "1";
        var commentsRequireMail = comments.getAttribute("data-hj-comments-require-mail") === "1";
        var commentsRequireUrl = comments.getAttribute("data-hj-comments-require-url") === "1";

        var identityKey = "hj_comment_identity_" + (location && location.pathname ? location.pathname : "page");
        var loginFocusTarget = null;

        var privateMarker = "<!--hj-private-->";
        var fullscreenRootClass = "hj-comment-fullscreen-open";
        var activeEmojiClose = null;

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
            var nameInput = modal.querySelector("#hj-login-name");
            var urlInput = modal.querySelector("#hj-login-url");
            var mailInput = modal.querySelector("#hj-login-mail");
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
            var triggers = document.querySelectorAll("[data-hj-open-login-modal]");
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

            var nameInput = modal.querySelector("#hj-login-name");
            var urlInput = modal.querySelector("#hj-login-url");
            var mailInput = modal.querySelector("#hj-login-mail");
            var passInput = modal.querySelector("#hj-login-pass");
            var submitBtn = form.querySelector(".hj-login-modal-submit");

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
            var box = form.querySelector("[data-hj-comment-box]");
            if (!textarea || !box) {
                return;
            }

            var role = form.getAttribute("data-hj-comment-role") || "default";
            var isTop = role === "top";
            var isUserLogged = form.getAttribute("data-hj-user-logged") === "1";

            var authorInput = form.querySelector("input[name=\"author\"]");
            var urlInput = form.querySelector("input[name=\"url\"]");
            var mailInput = form.querySelector("input[name=\"mail\"]");

            var privateBtn = form.querySelector("[data-hj-comment-private-toggle]");
            var fullscreenBtn = form.querySelector("[data-hj-comment-fullscreen-toggle]");
            var loginBtn = form.querySelector("[data-hj-open-login]");
            var emojiBtn = form.querySelector(".hj-comment-emoji");

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
                picker.className = "hj-emoji-picker";
                picker.hidden = true;
                picker.setAttribute("role", "dialog");
                picker.setAttribute("aria-label", "表情");
                picker.setAttribute("data-hj-emoji-picker", "");

                var grid = document.createElement("div");
                grid.className = "hj-emoji-picker-grid";
                grid.setAttribute("role", "listbox");
                grid.setAttribute("aria-label", "表情列表");

                var html = "";
                for (var i = 0; i < EMOJIS.length; i++) {
                    var emo = EMOJIS[i];
                    html += "<button type=\"button\" class=\"hj-emoji-picker-btn\" data-hj-emoji=\"" + emo + "\" aria-label=\"" + emo + "\">" + emo + "</button>";
                }
                grid.innerHTML = html;
                picker.appendChild(grid);

                picker.addEventListener("click", function (e) {
                    var t = e && e.target;
                    if (!t || !t.closest) {
                        return;
                    }
                    var btn = t.closest("[data-hj-emoji]");
                    if (!btn) {
                        return;
                    }
                    if (e && e.preventDefault) {
                        e.preventDefault();
                    }
                    var emo = btn.getAttribute("data-hj-emoji") || "";
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
</script>

<script>
    (function () {
        var blocks = document.querySelectorAll(".hj-links-apply");
        if (!blocks || blocks.length === 0) {
            return;
        }

        function setup(block) {
            if (!block || !block.querySelector) {
                return;
            }

            var stepCheck = block.querySelector(".hj-links-step-check");
            var stepForm = block.querySelector(".hj-links-step-form");
            if (!stepCheck || !stepForm) {
                return;
            }

            var cbs = Array.prototype.slice.call(stepCheck.querySelectorAll(".hj-links-check-input"));
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

            var btn = stepCheck.querySelector("[data-hj-links-confirm]");
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
</script>

<?php if ($this->is('post') || $this->is('page')): ?>
    <script src="<?php $this->options->themeUrl('assets/vendor/highlight/highlight.min.js'); ?>"></script>
    <script>
        (function () {
            var content = document.querySelector(".hj-article-content");
            if (!content) {
                return;
            }

            var blocks = content.querySelectorAll("pre code");
            if (!blocks || blocks.length === 0) {
                return;
            }

            if (typeof window.hljs === "undefined" || !window.hljs) {
                return;
            }

            try {
                window.hljs.configure({ ignoreUnescapedHTML: true });
            } catch (e) {}

            function normalizeLang(code) {
                if (!code || !code.classList) {
                    return;
                }

                var raw = (code.getAttribute("class") || "").trim();
                if (!raw) {
                    return;
                }

                var parts = raw.split(/\s+/);
                for (var i = 0; i < parts.length; i++) {
                    var cls = parts[i];
                    if (!cls || cls.indexOf("lang-") !== 0) {
                        continue;
                    }
                    var lang = cls.slice(5);
                    if (!lang) {
                        continue;
                    }
                    try {
                        code.classList.add("language-" + lang);
                    } catch (e) {}
                }
            }

            for (var i = 0; i < blocks.length; i++) {
                var code = blocks[i];
                if (!code) {
                    continue;
                }
                if (code.dataset && code.dataset.highlighted) {
                    continue;
                }
                normalizeLang(code);
                try {
                    window.hljs.highlightElement(code);
                } catch (e) {}
            }
        })();
    </script>
    <script>
        (function () {
            var content = document.querySelector(".hj-article-content");
            if (!content) {
                return;
            }

            function hasOnlyImageParagraph(p, carrier) {
                if (!p) {
                    return false;
                }

                var nodes = p.childNodes || [];
                for (var i = 0; i < nodes.length; i++) {
                    var n = nodes[i];
                    if (!n) {
                        continue;
                    }
                    if (n.nodeType === 3) {
                        if (String(n.textContent || "").trim() !== "") {
                            return false;
                        }
                        continue;
                    }
                    if (n.nodeType === 8) {
                        continue;
                    }
                    if (n.nodeType === 1) {
                        if (n !== carrier) {
                            return false;
                        }
                    }
                }

                return true;
            }

            var imgs = Array.prototype.slice.call(content.querySelectorAll("img"));
            imgs.forEach(function (img) {
                if (!img || !img.parentNode) {
                    return;
                }
                if (img.closest && img.closest("figure")) {
                    return;
                }

                var carrier = img;
                var p = img.parentNode;
                if (p && p.tagName === "A") {
                    carrier = p;
                    p = p.parentNode;
                }

                if (!p || p.tagName !== "P") {
                    return;
                }
                if (!hasOnlyImageParagraph(p, carrier)) {
                    return;
                }

                var caption = String(img.getAttribute("alt") || "").trim();
                if (!caption) {
                    caption = String(img.getAttribute("title") || "").trim();
                }

                var figure = document.createElement("figure");
                figure.className = "hj-figure";
                figure.appendChild(carrier);

                if (caption) {
                    var figcaption = document.createElement("figcaption");
                    figcaption.textContent = caption;
                    figure.appendChild(figcaption);
                }

                try {
                    p.parentNode.replaceChild(figure, p);
                } catch (e) {}
            });
        })();
    </script>
    <script>
        (function () {
            var content = document.querySelector(".hj-article-content");
            if (!content) {
                return;
            }

            function isBlockedParent(node) {
                var p = node && node.parentNode ? node.parentNode : null;
                while (p && p !== content) {
                    if (p.nodeType !== 1) {
                        p = p.parentNode;
                        continue;
                    }

                    var tag = (p.tagName || "").toUpperCase();
                    if (
                        tag === "CODE" ||
                        tag === "PRE" ||
                        tag === "A" ||
                        tag === "SCRIPT" ||
                        tag === "STYLE" ||
                        tag === "TEXTAREA" ||
                        tag === "RUBY" ||
                        tag === "RT"
                    ) {
                        return true;
                    }
                    p = p.parentNode;
                }
                return false;
            }

            function buildRuby(baseText, annotation) {
                var ruby = document.createElement("ruby");
                ruby.className = "hj-term hj-term-ruby";
                ruby.appendChild(document.createTextNode(baseText));

                var rt = document.createElement("rt");
                rt.textContent = annotation;
                ruby.appendChild(rt);

                return ruby;
            }

            function buildTooltip(baseText, annotation) {
                var span = document.createElement("span");
                span.className = "hj-term hj-term-tooltip";
                span.setAttribute("data-hj-term", annotation);
                span.setAttribute("tabindex", "0");
                span.textContent = baseText;
                return span;
            }

            function buildUnder(baseText, annotation) {
                var wrap = document.createElement("span");
                wrap.className = "hj-term hj-term-under";

                var base = document.createElement("span");
                base.className = "hj-term-under-base";
                base.textContent = baseText;

                var under = document.createElement("span");
                under.className = "hj-term-under-anno";
                under.textContent = annotation;

                wrap.appendChild(base);
                wrap.appendChild(under);
                return wrap;
            }

            function buildSpoiler(text) {
                var span = document.createElement("span");
                span.className = "hj-term hj-term-spoiler spoiler";
                span.setAttribute("tabindex", "0");
                span.textContent = text;
                return span;
            }

            function appendTextWithSpoiler(target, rawText) {
                var text = String(rawText || "");
                if (!text) {
                    return;
                }
                if (text.indexOf("!") === -1) {
                    target.appendChild(document.createTextNode(text));
                    return;
                }

                var pos = 0;
                while (pos < text.length) {
                    var open = text.indexOf("!", pos);
                    if (open === -1) {
                        target.appendChild(document.createTextNode(text.slice(pos)));
                        break;
                    }

                    var close = text.indexOf("!", open + 1);
                    if (close === -1) {
                        target.appendChild(document.createTextNode(text.slice(pos)));
                        break;
                    }

                    var spoilerText = text.slice(open + 1, close);
                    var isValid =
                        spoilerText !== "" &&
                        spoilerText.trim() === spoilerText &&
                        spoilerText.indexOf("\n") === -1 &&
                        spoilerText.indexOf("\r") === -1;

                    if (!isValid) {
                        target.appendChild(document.createTextNode(text.slice(pos, open + 1)));
                        pos = open + 1;
                        continue;
                    }

                    if (open > pos) {
                        target.appendChild(document.createTextNode(text.slice(pos, open)));
                    }
                    target.appendChild(buildSpoiler(spoilerText));
                    pos = close + 1;
                }
            }

            function splitAnnotation(raw) {
                var inner = String(raw || "").trim();
                if (!inner) {
                    return null;
                }

                var mode = "ruby";
                var annotation = inner;

                var pipe = inner.lastIndexOf("|");
                if (pipe !== -1) {
                    var left = inner.slice(0, pipe).trim();
                    var right = inner.slice(pipe + 1).trim().toLowerCase();
                    if (left) {
                        annotation = left;
                    }
                    if (right === "ruby" || right === "tooltip" || right === "under") {
                        mode = right;
                    }
                }

                if (!annotation) {
                    return null;
                }

                return { annotation: annotation, mode: mode };
            }

            function hasSpoilerMarker(text) {
                var s = String(text || "");
                var first = s.indexOf("!");
                if (first === -1) {
                    return false;
                }
                return s.indexOf("!", first + 1) !== -1;
            }

            function findBaseToken(text, pos, caret) {
                try {
                    var before = String(text || "").slice(pos, caret);
                    if (!before) {
                        return null;
                    }

                    // Match the last "token-like" run before ^(.
                    // Prefer Unicode property escapes (modern browsers), fallback to a conservative range set.
                    var m = null;
                    try {
                        m = before.match(/([\p{L}\p{N}_\-+#]+)$/u);
                    } catch (e) {
                        m = before.match(/([0-9A-Za-z\u00C0-\u024F\u0400-\u04FF\u3400-\u4DBF\u4E00-\u9FFF_\-+#]+)$/);
                    }
                    if (!m || !m[1]) {
                        return null;
                    }

                    var baseText = m[1];
                    var start = caret - baseText.length;
                    if (start < pos || start >= caret) {
                        return null;
                    }

                    return { start: start, baseText: baseText };
                } catch (e) {
                    return null;
                }
            }

            function parseTextNode(node) {
                var text = node && node.nodeValue ? String(node.nodeValue) : "";
                if (!text || (text.indexOf("^(") === -1 && !hasSpoilerMarker(text))) {
                    return;
                }

                var frag = document.createDocumentFragment();
                var pos = 0;

                while (pos < text.length) {
                    var caret = text.indexOf("^(", pos);
                    if (caret === -1) {
                        appendTextWithSpoiler(frag, text.slice(pos));
                        break;
                    }

                    // BaseText: take the token-like run immediately before ^(.
                    var base = findBaseToken(text, pos, caret);
                    if (!base) {
                        // No base text found, keep scanning.
                        appendTextWithSpoiler(frag, text.slice(pos, caret + 2));
                        pos = caret + 2;
                        continue;
                    }

                    // Find closing ')'
                    var close = text.indexOf(")", caret + 2);
                    if (close === -1) {
                        appendTextWithSpoiler(frag, text.slice(pos));
                        break;
                    }

                    var start = base.start;
                    var baseText = base.baseText;
                    var inner = text.slice(caret + 2, close);
                    var parsed = splitAnnotation(inner);

                    if (!parsed) {
                        appendTextWithSpoiler(frag, text.slice(pos, close + 1));
                        pos = close + 1;
                        continue;
                    }

                    appendTextWithSpoiler(frag, text.slice(pos, start));

                    if (parsed.mode === "tooltip") {
                        frag.appendChild(buildTooltip(baseText, parsed.annotation));
                    } else if (parsed.mode === "under") {
                        frag.appendChild(buildUnder(baseText, parsed.annotation));
                    } else {
                        frag.appendChild(buildRuby(baseText, parsed.annotation));
                    }

                    pos = close + 1;
                }

                try {
                    node.parentNode.replaceChild(frag, node);
                } catch (e) {}
            }

            if (!document.createTreeWalker) {
                return;
            }

            var walker = document.createTreeWalker(
                content,
                NodeFilter.SHOW_TEXT,
                {
                    acceptNode: function (node) {
                        try {
                            if (!node || !node.nodeValue) {
                                return NodeFilter.FILTER_REJECT;
                            }
                            var nodeText = String(node.nodeValue);
                            if (nodeText.indexOf("^(") === -1 && !hasSpoilerMarker(nodeText)) {
                                return NodeFilter.FILTER_REJECT;
                            }
                            if (isBlockedParent(node)) {
                                return NodeFilter.FILTER_REJECT;
                            }
                            return NodeFilter.FILTER_ACCEPT;
                        } catch (e) {
                            return NodeFilter.FILTER_REJECT;
                        }
                    }
                },
                false
            );

            var nodes = [];
            while (walker.nextNode()) {
                nodes.push(walker.currentNode);
            }
            for (var i = 0; i < nodes.length; i++) {
                parseTextNode(nodes[i]);
            }

            // Tooltip/Spoiler interactions on touch devices: click to toggle, click outside to close.
            var useClick = false;
            try {
                useClick = !!(window.matchMedia && window.matchMedia("(hover: none) and (pointer: coarse)").matches);
            } catch (e) {
                useClick = false;
            }
            if (!useClick) {
                return;
            }

            var toggles = [];
            var tooltips = content.querySelectorAll(".hj-term-tooltip[data-hj-term]");
            var spoilers = content.querySelectorAll(".hj-term-spoiler");
            for (var i = 0; i < tooltips.length; i++) {
                toggles.push(tooltips[i]);
            }
            for (var j = 0; j < spoilers.length; j++) {
                toggles.push(spoilers[j]);
            }
            if (toggles.length === 0) {
                return;
            }

            function closeAll() {
                for (var i = 0; i < toggles.length; i++) {
                    try {
                        toggles[i].classList.remove("is-open");
                    } catch (e) {}
                }
            }

            for (var i = 0; i < toggles.length; i++) {
                (function (el) {
                    if (!el || !el.addEventListener) {
                        return;
                    }
                    el.addEventListener("click", function (e) {
                        if (e && e.preventDefault) {
                            e.preventDefault();
                        }
                        if (e && e.stopPropagation) {
                            e.stopPropagation();
                        }
                        var isOpen = false;
                        try {
                            isOpen = el.classList.contains("is-open");
                        } catch (err) {
                            isOpen = false;
                        }
                        closeAll();
                        if (!isOpen) {
                            try {
                                el.classList.add("is-open");
                            } catch (err) {}
                        }
                    });
                })(toggles[i]);
            }

            document.addEventListener("click", function () {
                closeAll();
            });

            document.addEventListener("keydown", function (e) {
                var key = e && e.key ? String(e.key) : "";
                if (key === "Escape" || key === "Esc") {
                    closeAll();
                }
            });
        })();
    </script>
    <script>
        (function () {
            var content = document.querySelector(".hj-article-content");
            if (!content) {
                return;
            }

            var blocks = content.querySelectorAll("pre");
            if (!blocks || blocks.length === 0) {
                return;
            }

            function fallbackCopy(text) {
                var ok = false;
                var ta = document.createElement("textarea");
                ta.value = text;
                ta.setAttribute("readonly", "");
                ta.style.position = "absolute";
                ta.style.left = "-9999px";
                ta.style.top = "0";
                document.body.appendChild(ta);
                ta.select();
                try {
                    ta.setSelectionRange(0, ta.value.length);
                } catch (e) {}
                try {
                    ok = document.execCommand("copy");
                } catch (e) {
                    ok = false;
                }
                document.body.removeChild(ta);
                return ok;
            }

            function copyText(text) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    return navigator.clipboard.writeText(text).then(
                        function () {
                            return true;
                        },
                        function () {
                            return fallbackCopy(text);
                        }
                    );
                }
                return Promise.resolve(fallbackCopy(text));
            }

            function setTip(btn, msg) {
                if (!btn || !btn.classList) {
                    return;
                }
                try {
                    btn.classList.add("is-copied");
                    btn.setAttribute("data-hj-code-tip", msg);
                } catch (e) {}

                if (btn._hjCopyTimer) {
                    clearTimeout(btn._hjCopyTimer);
                }
                btn._hjCopyTimer = setTimeout(function () {
                    try {
                        btn.classList.remove("is-copied");
                        btn.setAttribute("data-hj-code-tip", "复制");
                    } catch (e) {}
                }, 1200);
            }

            var maxLines = 10;

            function countLines(text) {
                var t = String(text || "");
                t = t.replace(/\r\n/g, "\n").replace(/\r/g, "\n");
                while (t.length > 0 && t.charAt(t.length - 1) === "\n") {
                    t = t.slice(0, -1);
                }
                if (!t) {
                    return 0;
                }
                return t.split("\n").length;
            }

            function isAtBottom(el) {
                if (!el) {
                    return true;
                }

                var threshold = 2;
                var maxScrollTop = el.scrollHeight - el.clientHeight;
                if (maxScrollTop <= threshold) {
                    return true;
                }
                return el.scrollTop + el.clientHeight >= el.scrollHeight - threshold;
            }

            function syncFoldState(preEl, codeEl) {
                if (!preEl || !codeEl || !preEl.classList) {
                    return;
                }

                if (!preEl.classList.contains("hj-code-collapsed")) {
                    try {
                        preEl.classList.remove("hj-code-at-bottom");
                    } catch (e) {}
                    return;
                }

                var atBottom = isAtBottom(codeEl);
                try {
                    if (atBottom) {
                        preEl.classList.add("hj-code-at-bottom");
                    } else {
                        preEl.classList.remove("hj-code-at-bottom");
                    }
                } catch (e) {}
            }

            for (var i = 0; i < blocks.length; i++) {
                var pre = blocks[i];
                if (!pre || !pre.querySelector) {
                    continue;
                }

                var code = pre.querySelector("code");
                if (!code) {
                    continue;
                }

                var btn = pre.querySelector(".hj-code-copy-btn");
                if (!btn) {
                    btn = document.createElement("button");
                    btn.type = "button";
                    btn.className = "hj-code-copy-btn";
                    btn.setAttribute("aria-label", "复制");
                    btn.setAttribute("data-hj-code-tip", "复制");
                    btn.innerHTML =
                        '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy-icon lucide-copy" aria-hidden="true"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>';
                    btn.addEventListener("click", function (e) {
                        if (e && e.preventDefault) {
                            e.preventDefault();
                        }
                        if (e && e.stopPropagation) {
                            e.stopPropagation();
                        }

                        var button = e && e.currentTarget ? e.currentTarget : null;
                        if (!button || !button.parentNode) {
                            return;
                        }
                        var preEl = button.parentNode;
                        if (!preEl || !preEl.querySelector) {
                            return;
                        }
                        var codeEl = preEl.querySelector("code");
                        var text = codeEl ? String(codeEl.textContent || "") : "";
                        if (!text) {
                            setTip(button, "无内容");
                            return;
                        }

                        copyText(text).then(function (ok) {
                            setTip(button, ok ? "已复制" : "复制失败");
                        });
                    });

                    pre.appendChild(btn);
                }

                var lineCount = countLines(code.textContent || "");
                if (lineCount > maxLines) {
                    try {
                        pre.classList.add("hj-code-collapsed");
                    } catch (e) {}

                    if (!pre.querySelector(".hj-code-fold")) {
                        var fold = document.createElement("div");
                        fold.className = "hj-code-fold";

                        var expand = document.createElement("button");
                        expand.type = "button";
                        expand.className = "hj-code-expand-btn";
                        expand.setAttribute("aria-label", "展开");
                        expand.innerHTML =
                            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down-to-line-icon lucide-arrow-down-to-line" aria-hidden="true"><path d="M12 17V3"/><path d="m6 11 6 6 6-6"/><path d="M19 21H5"/></svg><span>展开</span>';
                        expand.addEventListener("click", function (e) {
                            if (e && e.preventDefault) {
                                e.preventDefault();
                            }
                            if (e && e.stopPropagation) {
                                e.stopPropagation();
                            }

                            var button = e && e.currentTarget ? e.currentTarget : null;
                            var foldEl = button && button.parentNode ? button.parentNode : null;
                            var preEl = foldEl && foldEl.parentNode ? foldEl.parentNode : null;
                        if (!preEl || !preEl.classList) {
                            return;
                        }
                        try {
                            preEl.classList.remove("hj-code-collapsed");
                            preEl.classList.remove("hj-code-at-bottom");
                        } catch (err) {}
                        try {
                            if (foldEl && foldEl.remove) {
                                foldEl.remove();
                                } else if (foldEl) {
                                    foldEl.hidden = true;
                                }
                            } catch (err) {}
                        });

                        fold.appendChild(expand);
                        pre.appendChild(fold);
                    }

                    if (!pre._hjFoldScrollBound && code.addEventListener) {
                        pre._hjFoldScrollBound = true;
                        var handler = (function (preEl, codeEl) {
                            return function () {
                                syncFoldState(preEl, codeEl);
                            };
                        })(pre, code);
                        try {
                            code.addEventListener("scroll", handler, { passive: true });
                        } catch (err) {
                            code.addEventListener("scroll", handler);
                        }
                    }

                    syncFoldState(pre, code);
                }
            }
        })();
    </script>
<?php endif; ?>

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

        function markLinkKind(a, href) {
            if (!a || !a.classList) {
                return;
            }
            var raw = (href || a.getAttribute("href") || "") + "";
            if (!raw) {
                return;
            }

            var lower = raw.toLowerCase();
            if (lower.indexOf("mailto:") === 0 || lower.indexOf("tel:") === 0 || lower.indexOf("javascript:") === 0) {
                return;
            }

            var url;
            try {
                url = new URL(raw, window.location.href);
            } catch (e) {
                return;
            }
            if (!url || !url.host) {
                return;
            }

            var isInternal = false;
            try {
                isInternal = url.host === window.location.host;
            } catch (e) {
                isInternal = false;
            }

            try {
                a.classList.toggle("hj-link-internal", isInternal);
                a.classList.toggle("hj-link-external", !isInternal);
            } catch (e) {}
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

            // Mark internal/external link kinds for icon rendering (CSS).
            markLinkKind(a, href);

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
