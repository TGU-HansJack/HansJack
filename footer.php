<?php if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
} ?>
<?php
$hjNeedsKatexAssets = false;
try {
    $hjNeedsKatexAssets = hansJackShouldLoadKatexAssets($this);
} catch (\Throwable $e) {
    $hjNeedsKatexAssets = false;
}

$hjCustomJavaScript = trim((string) ($this->options->hjCustomJavaScript ?? ''));
if ($hjCustomJavaScript !== '') {
    $hjCustomJavaScript = str_ireplace('</script>', '<\/script>', $hjCustomJavaScript);
}
?>
</div>

<footer class="hj-footer" id="hj-footer">
    <div class="hj-shell">
        <div class="hj-footer-row">
            <p class="hj-footer-left">
                <span class="hj-footer-copy-mark" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copyright-icon lucide-copyright"><circle cx="12" cy="12" r="10"/><path d="M14.83 14.83a4 4 0 1 1 0-5.66"/></svg>
                </span>
                <?php echo date('Y'); ?>
                <a href="<?php $this->options->siteUrl(); ?>"><?php $this->options->title(); ?></a>
                <?php
                $hjIcpBeian = trim((string) $this->options->hjIcpBeian);
                $hjMpsBeian = trim((string) $this->options->hjMpsBeian);
                $hjFooterCustomCode = trim((string) $this->options->hjFooterCustomCode);
                $hjRewardImageUrl = hansJackNormalizeAssetUrl($this->options, (string) ($this->options->hjRewardImageUrl ?? ''));
                $hjAfdianImageUrl = hansJackNormalizeAssetUrl($this->options, (string) ($this->options->hjAfdianImageUrl ?? ''));
                $hjAfdianPageUrl = hansJackNormalizeAssetUrl($this->options, (string) ($this->options->hjAfdianPageUrl ?? ''));
                $hjRewardMethods = [];
                if ($hjRewardImageUrl !== '') {
                    $hjRewardMethods[] = [
                        'key' => 'reward',
                        'label' => _t('赞赏码'),
                        'image' => $hjRewardImageUrl,
                        'alt' => _t('赞赏码'),
                    ];
                }
                if ($hjAfdianImageUrl !== '') {
                    $hjRewardMethods[] = [
                        'key' => 'afdian',
                        'label' => _t('爱发电'),
                        'image' => $hjAfdianImageUrl,
                        'alt' => _t('爱发电'),
                        'link' => $hjAfdianPageUrl,
                        'linkLabel' => _t('跳转爱发电页面'),
                    ];
                }
                $hjShowRewardFab = $this->is('post') && !empty($hjRewardMethods);
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
    <button class="hj-fab-btn hj-fab-top" type="button" aria-label="<?php _e('返回顶部'); ?>">
        <span class="hj-fab-ring" aria-hidden="true">
            <svg class="hj-fab-ring-svg" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                <circle class="hj-fab-ring-bg" cx="20" cy="20" r="18"></circle>
                <circle class="hj-fab-ring-fg hj-fab-top-ring-fg" cx="20" cy="20" r="18" stroke-dasharray="113.10 113.10" stroke-dashoffset="113.10"></circle>
            </svg>
        </span>
        <span class="hj-fab-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-to-line-icon lucide-arrow-up-to-line" aria-hidden="true"><path d="M5 3h14"/><path d="m18 13-6-6-6 6"/><path d="M12 7v14"/></svg>
        </span>
        <span class="hj-fab-tip" aria-hidden="true"><?php _e('返回顶部'); ?></span>
    </button>
    <button class="hj-fab-btn hj-fab-settings" type="button" aria-label="<?php _e('设置'); ?>" aria-haspopup="true" aria-expanded="false">
        <span class="hj-fab-ring" aria-hidden="true">
            <svg class="hj-fab-ring-svg" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                <circle class="hj-fab-ring-bg" cx="20" cy="20" r="18"></circle>
                <circle class="hj-fab-ring-fg" cx="20" cy="20" r="18"></circle>
            </svg>
        </span>
        <span class="hj-fab-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sliders-vertical-icon lucide-sliders-vertical" aria-hidden="true"><path d="M10 8h4"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M17 16h4"/><path d="M19 12V3"/><path d="M19 21v-5"/><path d="M3 14h4"/><path d="M5 10V3"/><path d="M5 21v-7"/></svg>
        </span>
        <span class="hj-fab-tip" aria-hidden="true"><?php _e('设置'); ?></span>
    </button>
    <?php if ($hjShowRewardFab): ?>
        <button class="hj-fab-btn hj-fab-reward" type="button" aria-label="<?php _e('赞赏'); ?>" aria-haspopup="true" aria-expanded="false">
            <span class="hj-fab-ring" aria-hidden="true">
                <svg class="hj-fab-ring-svg" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                    <circle class="hj-fab-ring-bg" cx="20" cy="20" r="18"></circle>
                    <circle class="hj-fab-ring-fg" cx="20" cy="20" r="18"></circle>
                </svg>
            </span>
            <span class="hj-fab-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-hand-heart-icon lucide-hand-heart" aria-hidden="true"><path d="M11 14h2a2 2 0 0 0 0-4h-3c-.6 0-1.1.2-1.4.6L3 16"/><path d="m14.45 13.39 5.05-4.694C20.196 8 21 6.85 21 5.75a2.75 2.75 0 0 0-4.797-1.837.276.276 0 0 1-.406 0A2.75 2.75 0 0 0 11 5.75c0 1.2.802 2.248 1.5 2.946L16 11.95"/><path d="m2 15 6 6"/><path d="m7 20 1.6-1.4c.3-.4.8-.6 1.4-.6h4c1.1 0 2.1-.4 2.8-1.2l4.6-4.4a1 1 0 0 0-2.75-2.91"/></svg>
            </span>
            <span class="hj-fab-tip" aria-hidden="true"><?php _e('赞赏'); ?></span>
        </button>
    <?php endif; ?>
    <button class="hj-fab-btn hj-fab-comment" type="button" aria-label="<?php _e('评论'); ?>">
        <span class="hj-fab-ring" aria-hidden="true">
            <svg class="hj-fab-ring-svg" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                <circle class="hj-fab-ring-bg" cx="20" cy="20" r="18"></circle>
                <circle class="hj-fab-ring-fg" cx="20" cy="20" r="18"></circle>
            </svg>
        </span>
        <span class="hj-fab-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square-icon lucide-message-square" aria-hidden="true"><path d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"/></svg>
        </span>
        <span class="hj-fab-tip" aria-hidden="true"><?php _e('评论'); ?></span>
    </button>
    <button class="hj-fab-btn hj-fab-toc" type="button" aria-label="<?php _e('目录'); ?>" aria-haspopup="true" aria-expanded="false">
        <span class="hj-fab-ring" aria-hidden="true">
            <svg class="hj-fab-ring-svg" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                <circle class="hj-fab-ring-bg" cx="20" cy="20" r="18"></circle>
                <circle class="hj-fab-ring-fg" cx="20" cy="20" r="18"></circle>
            </svg>
        </span>
        <span class="hj-fab-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-table-of-contents-icon lucide-table-of-contents" aria-hidden="true"><path d="M16 5H3"/><path d="M16 12H3"/><path d="M16 19H3"/><path d="M21 5h.01"/><path d="M21 12h.01"/><path d="M21 19h.01"/></svg>
        </span>
        <span class="hj-fab-tip" aria-hidden="true"><?php _e('目录'); ?></span>
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
<?php if ($hjShowRewardFab): ?>
    <?php $hjHasRewardTabs = count($hjRewardMethods) > 1; ?>
    <div class="hj-fab-reward-backdrop" data-hj-reward-backdrop aria-hidden="true" hidden></div>
    <div class="hj-fab-reward-popover" data-hj-reward-popover aria-hidden="true" hidden>
        <div class="hj-fab-reward-panel" role="dialog" aria-modal="true" aria-label="<?php _e('赞赏码'); ?>" data-hj-reward-panel>
            <div class="hj-fab-reward-head">
                <?php if ($hjHasRewardTabs): ?>
                    <div class="hj-fab-reward-tabs" role="tablist" aria-label="<?php _e('赞赏方式'); ?>">
                        <?php foreach ($hjRewardMethods as $idx => $hjMethod): ?>
                            <?php
                            $hjTabKey = (string) ($hjMethod['key'] ?? '');
                            $hjTabLabel = (string) ($hjMethod['label'] ?? '');
                            $hjTabId = 'hj-reward-tab-' . $hjTabKey;
                            $hjPaneId = 'hj-reward-pane-' . $hjTabKey;
                            $hjIsActiveTab = ($idx === 0);
                            ?>
                            <button
                                class="hj-fab-reward-tab<?php echo $hjIsActiveTab ? ' is-active' : ''; ?>"
                                type="button"
                                role="tab"
                                id="<?php echo hansJackEscape($hjTabId); ?>"
                                aria-controls="<?php echo hansJackEscape($hjPaneId); ?>"
                                aria-selected="<?php echo $hjIsActiveTab ? 'true' : 'false'; ?>"
                                tabindex="<?php echo $hjIsActiveTab ? '0' : '-1'; ?>"
                                data-hj-reward-tab="<?php echo hansJackEscape($hjTabKey); ?>"
                            ><?php echo hansJackEscape($hjTabLabel); ?></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <button class="hj-fab-reward-close" type="button" aria-label="<?php _e('关闭'); ?>" data-hj-reward-close>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
            <div class="hj-fab-reward-panes">
                <?php foreach ($hjRewardMethods as $idx => $hjMethod): ?>
                    <?php
                    $hjPaneKey = (string) ($hjMethod['key'] ?? '');
                    $hjPaneAlt = (string) ($hjMethod['alt'] ?? '');
                    $hjPaneImage = (string) ($hjMethod['image'] ?? '');
                    $hjPaneLink = (string) ($hjMethod['link'] ?? '');
                    $hjPaneLinkLabel = (string) ($hjMethod['linkLabel'] ?? '');
                    $hjTabId = 'hj-reward-tab-' . $hjPaneKey;
                    $hjPaneId = 'hj-reward-pane-' . $hjPaneKey;
                    $hjIsActivePane = ($idx === 0);
                    ?>
                    <div
                        class="hj-fab-reward-pane<?php echo $hjIsActivePane ? ' is-active' : ''; ?>"
                        role="tabpanel"
                        id="<?php echo hansJackEscape($hjPaneId); ?>"
                        <?php if ($hjHasRewardTabs): ?>
                            aria-labelledby="<?php echo hansJackEscape($hjTabId); ?>"
                        <?php else: ?>
                            aria-label="<?php echo hansJackEscape((string) ($hjMethod['label'] ?? '')); ?>"
                        <?php endif; ?>
                        data-hj-reward-pane="<?php echo hansJackEscape($hjPaneKey); ?>"
                        aria-hidden="<?php echo $hjIsActivePane ? 'false' : 'true'; ?>"
                    >
                        <div class="hj-fab-reward-media">
                            <img src="<?php echo hansJackEscape($hjPaneImage); ?>" alt="<?php echo hansJackEscape($hjPaneAlt); ?>" loading="lazy" decoding="async">
                            <?php if ($hjPaneLink !== '' && $hjPaneLinkLabel !== ''): ?>
                                <a class="hj-fab-reward-link" href="<?php echo hansJackEscape($hjPaneLink); ?>" target="_blank" rel="noopener noreferrer"><?php echo hansJackEscape($hjPaneLinkLabel); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="hj-mobile-toc-backdrop" data-hj-mobile-toc-backdrop aria-hidden="true"></div>

<div class="hj-theme-curtain" aria-hidden="true"></div>

<script src="<?php echo hansJackEscape(hansJackAssetUrl($this->options, 'assets/js/hj-footer-global-pre.js')); ?>"></script>

<?php if ($this->is('post') || $this->is('page')): ?>
    <script>
        (function () {
            var contents = Array.prototype.slice.call(document.querySelectorAll(".hj-article-content, .hj-comment-content"));
            if (!contents || contents.length === 0) {
                return;
            }

            var codeBlocks = [];
            for (var c = 0; c < contents.length; c++) {
                var content = contents[c];
                if (!content || !content.querySelectorAll) {
                    continue;
                }
                var blocks = content.querySelectorAll("pre code");
                if (!blocks || blocks.length === 0) {
                    continue;
                }
                for (var i = 0; i < blocks.length; i++) {
                    if (blocks[i]) {
                        codeBlocks.push(blocks[i]);
                    }
                }
            }

            if (codeBlocks.length === 0) {
                return;
            }

            function applyHighlightTheme() {
                var root = document.documentElement;
                if (!root || !root.classList) {
                    return;
                }
                var isDark = root.classList.contains("hj-theme-dark") && !root.classList.contains("hj-theme-light");
                var links = document.querySelectorAll("link[data-hj-hljs-theme]");
                for (var i = 0; i < links.length; i++) {
                    var link = links[i];
                    if (!link) {
                        continue;
                    }
                    var theme = link.getAttribute("data-hj-hljs-theme") || "";
                    link.disabled = isDark ? theme !== "dark" : theme !== "light";
                }
            }

            function ensureHighlightThemeObserver() {
                if (!window.MutationObserver) {
                    return;
                }

                var root = document.documentElement;
                if (!root || !root.dataset) {
                    return;
                }
                if (root.dataset.hjHljsThemeObserver === "1") {
                    return;
                }

                try {
                    var obs = new MutationObserver(function (records) {
                        for (var i = 0; i < records.length; i++) {
                            if (records[i] && records[i].attributeName === "class") {
                                applyHighlightTheme();
                                break;
                            }
                        }
                    });
                    obs.observe(root, { attributes: true, attributeFilter: ["class"] });
                    root.dataset.hjHljsThemeObserver = "1";
                } catch (e) {}
            }

            function appendHighlightThemeLink(theme, href) {
                if (!theme || !href) {
                    return;
                }
                var existing = document.querySelector('link[data-hj-hljs-theme="' + theme + '"]');
                if (existing) {
                    return;
                }

                try {
                    var link = document.createElement("link");
                    link.rel = "stylesheet";
                    link.href = href;
                    link.disabled = true;
                    link.setAttribute("data-hj-hljs-theme", theme);
                    document.head.appendChild(link);
                } catch (e) {}
            }

            function ensureHighlightCss() {
                appendHighlightThemeLink("light", "<?php echo hansJackEscape(hansJackAssetUrl($this->options, 'assets/vendor/highlight/github.min.css')); ?>");
                appendHighlightThemeLink("dark", "<?php echo hansJackEscape(hansJackAssetUrl($this->options, 'assets/vendor/highlight/github-dark.min.css')); ?>");
                applyHighlightTheme();
                ensureHighlightThemeObserver();
            }

            function loadScriptOnce(src, done) {
                var doneFn = typeof done === "function" ? done : function () {};
                if (!src) {
                    doneFn();
                    return;
                }

                var key = src.replace(/[^a-z0-9]/gi, "_");
                var selector = 'script[data-hj-hljs-js="' + key + '"]';
                var existing = document.querySelector(selector);
                if (existing) {
                    if (existing.getAttribute("data-hj-hljs-loaded") === "1") {
                        doneFn();
                        return;
                    }

                    existing.addEventListener("load", doneFn, { once: true });
                    existing.addEventListener("error", doneFn, { once: true });
                    return;
                }

                var script = document.createElement("script");
                script.src = src;
                script.async = true;
                script.setAttribute("data-hj-hljs-js", key);
                script.onload = function () {
                    script.setAttribute("data-hj-hljs-loaded", "1");
                    doneFn();
                };
                script.onerror = doneFn;
                document.head.appendChild(script);
            }

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

            function runHighlight() {
                if (typeof window.hljs === "undefined" || !window.hljs) {
                    return;
                }

                try {
                    window.hljs.configure({ ignoreUnescapedHTML: true });
                } catch (e) {}

                for (var i = 0; i < codeBlocks.length; i++) {
                    var code = codeBlocks[i];
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
            }

            ensureHighlightCss();
            loadScriptOnce("<?php echo hansJackEscape(hansJackAssetUrl($this->options, 'assets/vendor/highlight/highlight.min.js')); ?>", runHighlight);
        })();
    </script>
    <script>
        (function () {
            var contents = Array.prototype.slice.call(document.querySelectorAll(".hj-article-content, .hj-comment-content"));
            if (!contents || contents.length === 0) {
                return;
            }

            function isExcalidrawCodeBlock(codeEl) {
                if (!codeEl || !codeEl.getAttribute) {
                    return false;
                }

                var className = String(codeEl.getAttribute("class") || "").toLowerCase();
                if (className.indexOf("language-excalidraw") !== -1 || className.indexOf("lang-excalidraw") !== -1) {
                    return true;
                }

                var language = String(
                    codeEl.getAttribute("data-language") ||
                    codeEl.getAttribute("data-lang") ||
                    ""
                ).toLowerCase();

                return language === "excalidraw";
            }

            var targets = [];
            for (var c = 0; c < contents.length; c++) {
                var content = contents[c];
                if (!content || !content.querySelectorAll) {
                    continue;
                }

                var blocks = content.querySelectorAll("pre code");
                if (!blocks || blocks.length === 0) {
                    continue;
                }

                for (var i = 0; i < blocks.length; i++) {
                    var code = blocks[i];
                    if (!code || !isExcalidrawCodeBlock(code)) {
                        continue;
                    }
                    targets.push(code);
                }
            }

            if (targets.length === 0) {
                return;
            }

            for (var t = 0; t < targets.length; t++) {
                var targetCode = targets[t];
                if (!targetCode || !targetCode.closest) {
                    continue;
                }
                var targetPre = targetCode.closest("pre");
                if (!targetPre || !targetPre.classList) {
                    continue;
                }
                try {
                    targetPre.classList.add("hj-excalidraw-pending");
                    targetPre.setAttribute("data-hj-excalidraw-pending", "1");
                } catch (e) {}
            }

            function parseScene(rawText) {
                var text = String(rawText || "").replace(/^\uFEFF/, "").trim();
                if (!text) {
                    return null;
                }

                try {
                    var scene = JSON.parse(text);
                    if (!scene || typeof scene !== "object") {
                        return null;
                    }
                    return scene;
                } catch (e) {
                    return null;
                }
            }

            function normalizeScene(scene) {
                if (!scene || typeof scene !== "object") {
                    return null;
                }

                var normalized = {
                    scrollToContent: true,
                    elements: Array.isArray(scene.elements) ? scene.elements : [],
                    appState: {},
                    files: scene.files && typeof scene.files === "object" ? scene.files : {}
                };

                var sourceAppState = scene.appState && typeof scene.appState === "object" ? scene.appState : {};
                for (var key in sourceAppState) {
                    if (Object.prototype.hasOwnProperty.call(sourceAppState, key)) {
                        normalized.appState[key] = sourceAppState[key];
                    }
                }
                normalized.appState.viewModeEnabled = true;
                normalized.appState.zenModeEnabled = false;
                normalized.appState.viewBackgroundColor = "transparent";

                return normalized;
            }

            function markUnavailable(preEl, reason) {
                if (!preEl || !preEl.classList) {
                    return;
                }
                try {
                    preEl.classList.remove("hj-excalidraw-pending");
                    preEl.removeAttribute("data-hj-excalidraw-pending");
                    preEl.classList.add("hj-excalidraw-unavailable");
                    if (reason) {
                        preEl.setAttribute("data-hj-excalidraw-error", reason);
                    }
                } catch (e) {}
            }

            function appendRuntimeCss(href) {
                if (!href) {
                    return;
                }
                var existing = document.querySelector("link[data-hj-excalidraw-css]");
                if (existing) {
                    return;
                }

                try {
                    var link = document.createElement("link");
                    link.rel = "stylesheet";
                    link.href = href;
                    link.setAttribute("data-hj-excalidraw-css", "1");
                    document.head.appendChild(link);
                } catch (e) {}
            }

            function loadScriptOnce(src, done, mode) {
                if (!src) {
                    done();
                    return;
                }

                var loadDone = typeof done === "function" ? done : function () {};
                var scriptMode = mode === "module" ? "module" : "classic";
                var key = src.replace(/[^a-z0-9]/gi, "_");
                var selector = 'script[data-hj-excalidraw-js="' + key + '"][data-hj-excalidraw-mode="' + scriptMode + '"]';
                var existing = document.querySelector(selector);
                if (existing) {
                    if (existing.getAttribute("data-hj-excalidraw-loaded") === "1") {
                        loadDone();
                        return;
                    }

                    existing.addEventListener("load", loadDone, { once: true });
                    existing.addEventListener("error", loadDone, { once: true });
                    return;
                }

                var script = document.createElement("script");
                script.src = src;
                script.type = scriptMode === "module" ? "module" : "text/javascript";
                script.async = true;
                script.setAttribute("data-hj-excalidraw-js", key);
                script.setAttribute("data-hj-excalidraw-mode", scriptMode);
                script.onload = function () {
                    script.setAttribute("data-hj-excalidraw-loaded", "1");
                    loadDone();
                };
                script.onerror = loadDone;
                document.head.appendChild(script);
            }

            function ensureRuntimeAssets(done) {
                if (
                    typeof window.HansJackExcalidraw !== "undefined" &&
                    window.HansJackExcalidraw &&
                    typeof window.HansJackExcalidraw.mount === "function"
                ) {
                    done();
                    return;
                }

                if (!window.EXCALIDRAW_ASSET_PATH) {
                    window.EXCALIDRAW_ASSET_PATH = "<?php echo hansJackEscape(hansJackAssetUrl($this->options, 'assets/vendor/excalidraw/prod/index.js')); ?>".replace(/index\.js(?:\?.*)?$/, "");
                }
                appendRuntimeCss("<?php echo hansJackEscape(hansJackAssetUrl($this->options, 'assets/vendor/excalidraw/hj-excalidraw-runtime.css')); ?>");
                loadScriptOnce("<?php echo hansJackEscape(hansJackAssetUrl($this->options, 'assets/vendor/excalidraw/hj-excalidraw-runtime.mod.r2.js')); ?>", done, "module");
            }

            function resolveTheme() {
                var root = document.documentElement;
                if (!root || !root.classList) {
                    return "light";
                }
                var isDark = root.classList.contains("hj-theme-dark") && !root.classList.contains("hj-theme-light");
                return isDark ? "dark" : "light";
            }

            function buildEditorFigure() {
                var figure = document.createElement("figure");
                figure.className = "hj-excalidraw-block";
                figure.setAttribute("data-hj-excalidraw", "1");
                figure.style.display = "block";
                figure.style.margin = "1rem 0";
                figure.style.width = "100%";
                figure.style.maxWidth = "100%";
                figure.style.boxSizing = "border-box";
                figure.style.border = "1px solid #d9d9d9";
                figure.style.borderRadius = "4px";
                figure.style.background = "transparent";
                figure.style.overflow = "hidden";

                var stage = document.createElement("div");
                stage.className = "hj-excalidraw-stage";
                stage.style.position = "relative";
                stage.style.width = "100%";
                var isNarrowViewport = false;
                try {
                    isNarrowViewport = !!(window.matchMedia && window.matchMedia("(max-width: 768px)").matches);
                } catch (e) {}
                stage.style.minHeight = isNarrowViewport ? "380px" : "520px";
                stage.style.maxHeight = isNarrowViewport ? "min(68vh, 560px)" : "760px";
                stage.style.overflow = "hidden";
                stage.style.background = "transparent";

                var editorRoot = document.createElement("div");
                editorRoot.className = "hj-excalidraw-editor";
                editorRoot.style.position = "absolute";
                editorRoot.style.top = "0";
                editorRoot.style.right = "0";
                editorRoot.style.bottom = "0";
                editorRoot.style.left = "0";
                editorRoot.style.width = "100%";
                editorRoot.style.height = "100%";
                stage.appendChild(editorRoot);
                figure.appendChild(stage);
                return {
                    figure: figure,
                    editorRoot: editorRoot
                };
            }

            function renderBlock(codeEl) {
                if (!codeEl) {
                    return;
                }

                var preEl = codeEl.closest ? codeEl.closest("pre") : null;
                if (!preEl || !preEl.parentNode) {
                    return;
                }
                if (preEl.getAttribute("data-hj-excalidraw-rendered") === "1") {
                    return;
                }

                var scene = parseScene(codeEl.textContent || "");
                if (!scene) {
                    markUnavailable(preEl, "json");
                    return;
                }

                var normalized = normalizeScene(scene);
                if (!normalized) {
                    markUnavailable(preEl, "scene");
                    return;
                }

                var mounted = buildEditorFigure();
                var figure = mounted.figure;
                var editorRoot = mounted.editorRoot;
                preEl.setAttribute("data-hj-excalidraw-rendered", "1");

                try {
                    preEl.parentNode.replaceChild(figure, preEl);
                } catch (e) {
                    markUnavailable(preEl, "replace");
                    return;
                }

                try {
                    var runtime = window.HansJackExcalidraw;
                    if (!runtime || typeof runtime.mount !== "function") {
                        throw new Error("runtime");
                    }
                    var mountedEditor = runtime.mount(editorRoot, normalized, { theme: resolveTheme() });
                    if (mountedEditor && typeof mountedEditor.unmount === "function") {
                        figure._hjExcalidrawUnmount = mountedEditor.unmount;
                    }
                } catch (e) {
                    markUnavailable(figure, "mount");
                }
            }

            ensureRuntimeAssets(function () {
                var runtime = window.HansJackExcalidraw;
                if (!runtime || typeof runtime.mount !== "function") {
                    for (var i = 0; i < targets.length; i++) {
                        var codeEl = targets[i];
                        if (!codeEl || !codeEl.closest) {
                            continue;
                        }
                        markUnavailable(codeEl.closest("pre"), "runtime");
                    }
                    if (window.console && typeof window.console.warn === "function") {
                        window.console.warn("[HansJack] Excalidraw runtime unavailable.");
                    }
                    return;
                }

                for (var i = 0; i < targets.length; i++) {
                    var codeEl = targets[i];
                    renderBlock(codeEl);
                }
            });
        })();
    </script>
    <script src="<?php echo hansJackEscape(hansJackAssetUrl($this->options, 'assets/js/hj-content-static.js')); ?>"></script>
    <script>
        (function () {
            var contents = Array.prototype.slice.call(document.querySelectorAll(".hj-article-content, .hj-comment-content"));
            if (!contents || contents.length === 0) {
                return;
            }

            function hasKatexSyntax(text) {
                if (!text) {
                    return false;
                }

                if (text.indexOf("$$") !== -1 && /\$\$[\s\S]+?\$\$/.test(text)) {
                    return true;
                }

                if (text.indexOf("\\(") !== -1 && /\\\([\s\S]+?\\\)/.test(text)) {
                    return true;
                }

                if (text.indexOf("\\[") !== -1 && /\\\[[\s\S]+?\\\]/.test(text)) {
                    return true;
                }

                if (text.indexOf("\\begin{") !== -1 && /\\begin\{(?:equation|align|alignat|gather|CD)\}/.test(text)) {
                    return true;
                }

                if (text.indexOf("$") !== -1 && /(^|[^\\])\$(?![\s$])(?:[^$\\\r\n]|\\.)+?\$(?!\$)/.test(text)) {
                    return true;
                }

                return false;
            }

            function shouldRenderKatex(nodes) {
                function readNodeTextWithoutCode(node) {
                    if (!node) {
                        return "";
                    }

                    var sourceNode = node;
                    if (node.cloneNode) {
                        try {
                            sourceNode = node.cloneNode(true);
                        } catch (e) {
                            sourceNode = node;
                        }
                    }

                    if (sourceNode && sourceNode.querySelectorAll) {
                        var ignored = sourceNode.querySelectorAll("pre, code");
                        for (var i = 0; i < ignored.length; i++) {
                            var ignoredNode = ignored[i];
                            if (!ignoredNode || !ignoredNode.parentNode) {
                                continue;
                            }
                            try {
                                ignoredNode.parentNode.removeChild(ignoredNode);
                            } catch (e) {}
                        }
                    }

                    try {
                        return String((sourceNode && sourceNode.textContent) || "");
                    } catch (e) {
                        return "";
                    }
                }

                for (var i = 0; i < nodes.length; i++) {
                    var node = nodes[i];
                    if (!node) {
                        continue;
                    }
                    var source = readNodeTextWithoutCode(node);
                    if (hasKatexSyntax(source)) {
                        return true;
                    }
                }
                return false;
            }

            function renderAllKatex() {
                if (typeof window.renderMathInElement !== "function" || typeof window.katex === "undefined") {
                    return;
                }

                var options = {
                    delimiters: [
                        { left: "$$", right: "$$", display: true },
                        { left: "\\[", right: "\\]", display: true },
                        { left: "\\(", right: "\\)", display: false },
                        { left: "$", right: "$", display: false }
                    ],
                    throwOnError: false,
                    strict: "ignore",
                    ignoredTags: ["script", "noscript", "style", "textarea", "pre", "code", "option"],
                    ignoredClasses: ["katex", "no-katex", "hljs"]
                };

                for (var i = 0; i < contents.length; i++) {
                    var content = contents[i];
                    if (!content || !content.querySelectorAll) {
                        continue;
                    }
                    if (content.dataset && content.dataset.hjKatexRendered === "1") {
                        continue;
                    }

                    try {
                        window.renderMathInElement(content, options);
                        if (content.dataset) {
                            content.dataset.hjKatexRendered = "1";
                        }
                    } catch (e) {}
                }
            }

            function appendKatexCss(href) {
                if (!href) {
                    return;
                }
                var cssNode = document.querySelector("link[data-hj-katex-css]");
                if (cssNode) {
                    return;
                }

                try {
                    var link = document.createElement("link");
                    link.rel = "stylesheet";
                    link.href = href;
                    link.setAttribute("data-hj-katex-css", "1");
                    document.head.appendChild(link);
                } catch (e) {}
            }

            function loadScriptOnce(src, done) {
                var doneFn = typeof done === "function" ? done : function () {};
                if (!src) {
                    doneFn();
                    return;
                }

                var key = src.replace(/[^a-z0-9]/gi, "_");
                var selector = 'script[data-hj-katex-js="' + key + '"]';
                var existing = document.querySelector(selector);
                if (existing) {
                    if (existing.getAttribute("data-hj-katex-loaded") === "1") {
                        doneFn();
                        return;
                    }

                    existing.addEventListener("load", doneFn, { once: true });
                    existing.addEventListener("error", doneFn, { once: true });
                    return;
                }

                var script = document.createElement("script");
                script.src = src;
                script.async = true;
                script.setAttribute("data-hj-katex-js", key);
                script.onload = function () {
                    script.setAttribute("data-hj-katex-loaded", "1");
                    doneFn();
                };
                script.onerror = doneFn;
                document.head.appendChild(script);
            }

            function ensureKatexAssets(done) {
                if (typeof window.renderMathInElement === "function" && typeof window.katex !== "undefined") {
                    done();
                    return;
                }

                appendKatexCss("<?php echo hansJackEscape(hansJackAssetUrl($this->options, 'assets/vendor/katex/katex.min.css')); ?>");
                loadScriptOnce("<?php echo hansJackEscape(hansJackAssetUrl($this->options, 'assets/vendor/katex/katex.min.js')); ?>", function () {
                    loadScriptOnce("<?php echo hansJackEscape(hansJackAssetUrl($this->options, 'assets/vendor/katex/contrib/mhchem.min.js')); ?>", function () {
                        loadScriptOnce("<?php echo hansJackEscape(hansJackAssetUrl($this->options, 'assets/vendor/katex/contrib/auto-render.min.js')); ?>", done);
                    });
                });
            }

            if (!shouldRenderKatex(contents)) {
                return;
            }

            ensureKatexAssets(renderAllKatex);
        })();
    </script>
<?php endif; ?>

<script src="<?php echo hansJackEscape(hansJackAssetUrl($this->options, 'assets/js/hj-footer-global-tail.js')); ?>"></script>

<?php $this->footer(); ?>
<?php if ($hjCustomJavaScript !== ''): ?>
<script id="hj-custom-javascript">
<?php echo $hjCustomJavaScript; ?>
</script>
<?php endif; ?>
</body>
</html>
