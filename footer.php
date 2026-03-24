<?php if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
} ?>
<?php
$needsKatexAssets = false;
try {
    $needsKatexAssets = shouldLoadKatexAssets($this);
} catch (\Throwable $e) {
    $needsKatexAssets = false;
}

$customJavaScript = trim((string) ($this->options->customJavaScript ?? ''));
if ($customJavaScript !== '') {
    $customJavaScript = str_ireplace('</script>', '<\/script>', $customJavaScript);
}

$isAdminViewer = currentUserIsAdmin();
$highLoadDegradeEnabled = hansjackHighLoadDegradeEnabled($this->options);
$visitorLivePollingEnabled = hansjackVisitorLivePollingEnabled($this->options);
$liveReloadEnabledForCurrent = $isAdminViewer
    ? true
    : ($visitorLivePollingEnabled && !$highLoadDegradeEnabled);

$internalLinkMetaJson = '{}';
$allowInternalLinkMeta = !$highLoadDegradeEnabled || $isAdminViewer;
if (($this->is('post') || $this->is('page')) && $allowInternalLinkMeta) {
    $internalLinkMeta = [];
    $normalizePath = static function (string $path): string {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        $path = preg_replace('#/+#', '/', $path);
        if (!is_string($path)) {
            return '';
        }

        if (!startsWith($path, '/')) {
            $path = '/' . ltrim($path, '/');
        }

        $path = rtrim($path, '/');
        return $path === '' ? '/' : $path;
    };

    $internalMetaPosts = null;
    try {
        $this->widget('Widget_Contents_Post_Recent@internal_link_meta', 'pageSize=2000', null, false)->to($internalMetaPosts);
    } catch (\Throwable $e) {
        $internalMetaPosts = null;
    }

    if ($internalMetaPosts && $internalMetaPosts->have()) {
        while ($internalMetaPosts->next()) {
            $postUrl = trim((string) ($internalMetaPosts->permalink ?? ''));
            if ($postUrl === '') {
                continue;
            }

            $pathRaw = trim((string) (parse_url($postUrl, PHP_URL_PATH) ?? ''));
            $pathKey = $normalizePath($pathRaw);
            if ($pathKey === '') {
                continue;
            }

            $postTitle = trim((string) ($internalMetaPosts->title ?? ''));
            if ($postTitle === '') {
                $postTitle = _t('无标题');
            }

            $postCreated = (int) ($internalMetaPosts->created ?? 0);
            $postModified = (int) ($internalMetaPosts->modified ?? 0);

            $payload = [
                'title' => $postTitle,
                'created' => max(0, $postCreated),
                'modified' => max(0, $postModified),
            ];

            $internalLinkMeta[$pathKey] = $payload;

            $decodedPath = $normalizePath(rawurldecode($pathKey));
            if ($decodedPath !== '' && $decodedPath !== $pathKey) {
                $internalLinkMeta[$decodedPath] = $payload;
            }
        }
    }

    $encodedInternalLinkMeta = json_encode(
        $internalLinkMeta,
        JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_HEX_TAG
            | JSON_HEX_AMP
            | JSON_HEX_APOS
            | JSON_HEX_QUOT
    );
    if (is_string($encodedInternalLinkMeta) && $encodedInternalLinkMeta !== '') {
        $internalLinkMetaJson = $encodedInternalLinkMeta;
    }
}
?>
</div>

<footer class="footer" id="footer">
    <div class="shell">
        <div class="footer-row">
            <p class="footer-left">
                <span class="footer-copy-mark" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copyright-icon lucide-copyright"><circle cx="12" cy="12" r="10"/><path d="M14.83 14.83a4 4 0 1 1 0-5.66"/></svg>
                </span>
                <?php echo date('Y'); ?>
                <a href="<?php $this->options->siteUrl(); ?>"><?php $this->options->title(); ?></a>
                <?php
                $icpBeian = trim((string) $this->options->icpBeian);
                $mpsBeian = trim((string) $this->options->mpsBeian);
                $footerCustomCode = trim((string) $this->options->footerCustomCode);
                $rewardImageUrl = normalizeAssetUrl($this->options, (string) ($this->options->rewardImageUrl ?? ''));
                $afdianImageUrl = normalizeAssetUrl($this->options, (string) ($this->options->afdianImageUrl ?? ''));
                $afdianPageUrl = normalizeAssetUrl($this->options, (string) ($this->options->afdianPageUrl ?? ''));
                $rewardMethods = [];
                if ($rewardImageUrl !== '') {
                    $rewardMethods[] = [
                        'key' => 'reward',
                        'label' => _t('赞赏码'),
                        'image' => $rewardImageUrl,
                        'alt' => _t('赞赏码'),
                    ];
                }
                if ($afdianImageUrl !== '') {
                    $rewardMethods[] = [
                        'key' => 'afdian',
                        'label' => _t('爱发电'),
                        'image' => $afdianImageUrl,
                        'alt' => _t('爱发电'),
                        'link' => $afdianPageUrl,
                        'linkLabel' => _t('跳转爱发电页面'),
                    ];
                }
                $showRewardFab = $this->is('post') && !empty($rewardMethods);
                ?>
                <?php if ($icpBeian !== ''): ?>
                    <span class="footer-sep" aria-hidden="true">·</span>
                    <a class="footer-beian" href="https://beian.miit.gov.cn/" target="_blank" rel="noreferrer"><?php echo escape($icpBeian); ?></a>
                <?php endif; ?>
                <?php if ($mpsBeian !== ''): ?>
                    <span class="footer-sep" aria-hidden="true">·</span>
                    <a class="footer-beian" href="<?php echo escape(buildMpsBeianUrl($mpsBeian)); ?>" target="_blank" rel="noreferrer"><?php echo escape($mpsBeian); ?></a>
                <?php endif; ?>
                <?php if ($footerCustomCode !== ''): ?>
                    <span class="footer-sep" aria-hidden="true">·</span>
                    <?php echo $footerCustomCode; ?>
                <?php endif; ?>
            </p>
            <p class="footer-right">
                <code class="footer-power">
                    <span>Powered by</span>
                    <a href="https://typecho.org/" aria-label="Typecho" target="_blank" rel="noreferrer" class="footer-icon-link">
                        <svg class="footer-icon footer-icon-typecho" viewBox="0 0 1024 1024" aria-hidden="true" focusable="false">
                            <path d="M512 1024C132.647385 1024 0 891.313231 0 512S132.647385 0 512 0s512 132.686769 512 512-132.647385 512-512 512zM236.307692 354.461538h551.384616V275.692308H236.307692v78.76923z m0 196.923077h393.846154v-78.76923H236.307692v78.76923z m0 196.923077h472.615385v-78.76923H236.307692v78.76923z" fill="currentColor"></path>
                        </svg>
                    </a>
                    <span class="footer-sep" aria-hidden="true">·</span>
                    <a href="https://github.com/tuyuritio/astro-theme-thought-lite" aria-label="移植+二改主题: ThoughtLite" target="_blank" rel="noreferrer" class="footer-icon-link footer-theme-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="footer-icon footer-icon-github" aria-hidden="true" focusable="false"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/><path d="M9 18c-4.51 2-5-2-7-2"/></svg>
                        <span class="footer-icon-tip" aria-hidden="true">模范主题: ThoughtLite</span>
                    </a>
                </code>
            </p>
        </div>
    </div>
</footer>

<div class="fab" aria-label="<?php _e('快捷操作'); ?>">
    <button class="fab-btn fab-top" type="button" aria-label="<?php _e('返回顶部'); ?>">
        <span class="fab-ring" aria-hidden="true">
            <svg class="fab-ring-svg" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                <circle class="fab-ring-bg" cx="20" cy="20" r="18"></circle>
                <circle class="fab-ring-fg fab-top-ring-fg" cx="20" cy="20" r="18" stroke-dasharray="113.10 113.10" stroke-dashoffset="113.10"></circle>
            </svg>
        </span>
        <span class="fab-icon fab-top-progress" aria-hidden="true">
            <span class="fab-top-progress-value">0</span>
        </span>
        <span class="fab-icon fab-top-symbol" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-up-to-line-icon lucide-arrow-up-to-line" aria-hidden="true"><path d="M5 3h14"/><path d="m18 13-6-6-6 6"/><path d="M12 7v14"/></svg>
        </span>
        <span class="fab-tip" aria-hidden="true"><?php _e('返回顶部'); ?></span>
    </button>
    <button class="fab-btn fab-settings" type="button" aria-label="<?php _e('设置'); ?>" aria-haspopup="true" aria-expanded="false">
        <span class="fab-ring" aria-hidden="true">
            <svg class="fab-ring-svg" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                <circle class="fab-ring-bg" cx="20" cy="20" r="18"></circle>
                <circle class="fab-ring-fg" cx="20" cy="20" r="18"></circle>
            </svg>
        </span>
        <span class="fab-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sliders-vertical-icon lucide-sliders-vertical" aria-hidden="true"><path d="M10 8h4"/><path d="M12 21v-9"/><path d="M12 8V3"/><path d="M17 16h4"/><path d="M19 12V3"/><path d="M19 21v-5"/><path d="M3 14h4"/><path d="M5 10V3"/><path d="M5 21v-7"/></svg>
        </span>
        <span class="fab-tip" aria-hidden="true"><?php _e('设置'); ?></span>
    </button>
    <?php if ($showRewardFab): ?>
        <button class="fab-btn fab-reward" type="button" aria-label="<?php _e('赞赏'); ?>" aria-haspopup="true" aria-expanded="false">
            <span class="fab-ring" aria-hidden="true">
                <svg class="fab-ring-svg" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                    <circle class="fab-ring-bg" cx="20" cy="20" r="18"></circle>
                    <circle class="fab-ring-fg" cx="20" cy="20" r="18"></circle>
                </svg>
            </span>
            <span class="fab-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-hand-heart-icon lucide-hand-heart" aria-hidden="true"><path d="M11 14h2a2 2 0 0 0 0-4h-3c-.6 0-1.1.2-1.4.6L3 16"/><path d="m14.45 13.39 5.05-4.694C20.196 8 21 6.85 21 5.75a2.75 2.75 0 0 0-4.797-1.837.276.276 0 0 1-.406 0A2.75 2.75 0 0 0 11 5.75c0 1.2.802 2.248 1.5 2.946L16 11.95"/><path d="m2 15 6 6"/><path d="m7 20 1.6-1.4c.3-.4.8-.6 1.4-.6h4c1.1 0 2.1-.4 2.8-1.2l4.6-4.4a1 1 0 0 0-2.75-2.91"/></svg>
            </span>
            <span class="fab-tip" aria-hidden="true"><?php _e('赞赏'); ?></span>
        </button>
    <?php endif; ?>
    <?php if ($this->is('post') || $this->is('page')): ?>
        <button class="fab-btn fab-comment" type="button" aria-label="<?php _e('评论'); ?>">
            <span class="fab-ring" aria-hidden="true">
                <svg class="fab-ring-svg" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                    <circle class="fab-ring-bg" cx="20" cy="20" r="18"></circle>
                    <circle class="fab-ring-fg" cx="20" cy="20" r="18"></circle>
                </svg>
            </span>
            <span class="fab-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square-icon lucide-message-square" aria-hidden="true"><path d="M22 17a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 21.286V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2z"/></svg>
            </span>
            <span class="fab-tip" aria-hidden="true"><?php _e('评论'); ?></span>
        </button>
    <?php endif; ?>
    <button class="fab-btn fab-toc" type="button" aria-label="<?php _e('目录'); ?>" aria-haspopup="true" aria-expanded="false">
        <span class="fab-ring" aria-hidden="true">
            <svg class="fab-ring-svg" viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                <circle class="fab-ring-bg" cx="20" cy="20" r="18"></circle>
                <circle class="fab-ring-fg" cx="20" cy="20" r="18"></circle>
            </svg>
        </span>
        <span class="fab-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-table-of-contents-icon lucide-table-of-contents" aria-hidden="true"><path d="M16 5H3"/><path d="M16 12H3"/><path d="M16 19H3"/><path d="M21 5h.01"/><path d="M21 12h.01"/><path d="M21 19h.01"/></svg>
        </span>
        <span class="fab-tip" aria-hidden="true"><?php _e('目录'); ?></span>
    </button>
</div>

<div class="fab-settings-popover" data-posts-settings-popover aria-hidden="true" hidden>
    <div class="fab-settings-panel" role="dialog" aria-modal="true" aria-label="<?php _e('设置'); ?>" data-posts-settings-panel>
        <div class="fab-settings-row">
            <span class="fab-settings-label"><?php _e('排序模式'); ?></span>
            <div class="fab-settings-segmented" role="radiogroup" aria-label="<?php _e('排序模式'); ?>" data-posts-setting-sort-mode>
                <span class="fab-settings-segmented-thumb" aria-hidden="true"></span>
                <button class="fab-settings-segmented-option is-active" type="button" role="radio" aria-checked="true" data-value="default"><?php _e('默认'); ?></button>
                <button class="fab-settings-segmented-option" type="button" role="radio" aria-checked="false" data-value="published"><?php _e('发布'); ?></button>
                <button class="fab-settings-segmented-option" type="button" role="radio" aria-checked="false" data-value="updated"><?php _e('更新'); ?></button>
            </div>
        </div>
        <div class="fab-settings-row">
            <span class="fab-settings-label"><?php _e('顺序'); ?></span>
            <div class="fab-settings-segmented" role="radiogroup" aria-label="<?php _e('顺序'); ?>" data-posts-setting-order>
                <span class="fab-settings-segmented-thumb" aria-hidden="true"></span>
                <button class="fab-settings-segmented-option is-active" type="button" role="radio" aria-checked="true" data-value="desc"><?php _e('降序'); ?></button>
                <button class="fab-settings-segmented-option" type="button" role="radio" aria-checked="false" data-value="asc"><?php _e('升序'); ?></button>
            </div>
        </div>
        <div class="fab-settings-row">
            <span class="fab-settings-label"><?php _e('列表模式'); ?></span>
            <div class="fab-settings-segmented" role="radiogroup" aria-label="<?php _e('列表模式'); ?>" data-posts-setting-list-mode>
                <span class="fab-settings-segmented-thumb" aria-hidden="true"></span>
                <button class="fab-settings-segmented-option is-active" type="button" role="radio" aria-checked="true" data-value="compact"><?php _e('紧凑'); ?></button>
                <button class="fab-settings-segmented-option" type="button" role="radio" aria-checked="false" data-value="preview"><?php _e('预览'); ?></button>
            </div>
        </div>
    </div>
</div>
<?php if ($showRewardFab): ?>
    <?php $hasRewardTabs = count($rewardMethods) > 1; ?>
    <div class="fab-reward-backdrop" data-reward-backdrop aria-hidden="true" hidden></div>
    <div class="fab-reward-popover" data-reward-popover aria-hidden="true" hidden>
        <div class="fab-reward-panel" role="dialog" aria-modal="true" aria-label="<?php _e('赞赏码'); ?>" data-reward-panel>
            <div class="fab-reward-head">
                <?php if ($hasRewardTabs): ?>
                    <div class="fab-reward-tabs" role="tablist" aria-label="<?php _e('赞赏方式'); ?>">
                        <?php foreach ($rewardMethods as $idx => $method): ?>
                            <?php
                            $tabKey = (string) ($method['key'] ?? '');
                            $tabLabel = (string) ($method['label'] ?? '');
                            $tabId = 'reward-tab-' . $tabKey;
                            $paneId = 'reward-pane-' . $tabKey;
                            $isActiveTab = ($idx === 0);
                            ?>
                            <button
                                class="fab-reward-tab<?php echo $isActiveTab ? ' is-active' : ''; ?>"
                                type="button"
                                role="tab"
                                id="<?php echo escape($tabId); ?>"
                                aria-controls="<?php echo escape($paneId); ?>"
                                aria-selected="<?php echo $isActiveTab ? 'true' : 'false'; ?>"
                                tabindex="<?php echo $isActiveTab ? '0' : '-1'; ?>"
                                data-reward-tab="<?php echo escape($tabKey); ?>"
                            ><?php echo escape($tabLabel); ?></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <button class="fab-reward-close" type="button" aria-label="<?php _e('关闭'); ?>" data-reward-close>
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>
            <div class="fab-reward-panes">
                <?php foreach ($rewardMethods as $idx => $method): ?>
                    <?php
                    $paneKey = (string) ($method['key'] ?? '');
                    $paneAlt = (string) ($method['alt'] ?? '');
                    $paneImage = (string) ($method['image'] ?? '');
                    $paneLink = (string) ($method['link'] ?? '');
                    $paneLinkLabel = (string) ($method['linkLabel'] ?? '');
                    $tabId = 'reward-tab-' . $paneKey;
                    $paneId = 'reward-pane-' . $paneKey;
                    $isActivePane = ($idx === 0);
                    ?>
                    <div
                        class="fab-reward-pane<?php echo $isActivePane ? ' is-active' : ''; ?>"
                        role="tabpanel"
                        id="<?php echo escape($paneId); ?>"
                        <?php if ($hasRewardTabs): ?>
                            aria-labelledby="<?php echo escape($tabId); ?>"
                        <?php else: ?>
                            aria-label="<?php echo escape((string) ($method['label'] ?? '')); ?>"
                        <?php endif; ?>
                        data-reward-pane="<?php echo escape($paneKey); ?>"
                        aria-hidden="<?php echo $isActivePane ? 'false' : 'true'; ?>"
                    >
                        <div class="fab-reward-media">
                            <img src="<?php echo escape($paneImage); ?>" alt="<?php echo escape($paneAlt); ?>" loading="lazy" decoding="async">
                            <?php if ($paneLink !== '' && $paneLinkLabel !== ''): ?>
                                <a class="fab-reward-link" href="<?php echo escape($paneLink); ?>" target="_blank" rel="noopener noreferrer"><?php echo escape($paneLinkLabel); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="mobile-toc-backdrop" data-mobile-toc-backdrop aria-hidden="true"></div>

<div class="theme-curtain" aria-hidden="true"></div>

<script src="<?php echo escape(assetUrlSmart($this->options, 'assets/js/footer-global-pre.js')); ?>"></script>
<script src="<?php echo escape(assetUrlSmart($this->options, 'assets/js/landing-seasonal-fx.js')); ?>"></script>

<?php if ($this->is('post') || $this->is('page') || $this->is('index')): ?>
    <script>
        (function () {
            var contents = Array.prototype.slice.call(document.querySelectorAll(".article-content, .comment-content"));
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
                var isDark = root.classList.contains("theme-dark") && !root.classList.contains("theme-light");
                var links = document.querySelectorAll("link[data-hljs-theme]");
                for (var i = 0; i < links.length; i++) {
                    var link = links[i];
                    if (!link) {
                        continue;
                    }
                    var theme = link.getAttribute("data-hljs-theme") || "";
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
                if (root.dataset.hljsThemeObserver === "1") {
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
                    root.dataset.hljsThemeObserver = "1";
                } catch (e) {}
            }

            function appendHighlightThemeLink(theme, href) {
                if (!theme || !href) {
                    return;
                }
                var existing = document.querySelector('link[data-hljs-theme="' + theme + '"]');
                if (existing) {
                    return;
                }

                try {
                    var link = document.createElement("link");
                    link.rel = "stylesheet";
                    link.href = href;
                    link.disabled = true;
                    link.setAttribute("data-hljs-theme", theme);
                    document.head.appendChild(link);
                } catch (e) {}
            }

            function ensureHighlightCss() {
                appendHighlightThemeLink("light", "<?php echo escape(assetUrl($this->options, 'assets/vendor/highlight/github.min.css')); ?>");
                appendHighlightThemeLink("dark", "<?php echo escape(assetUrl($this->options, 'assets/vendor/highlight/github-dark.min.css')); ?>");
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
                var selector = 'script[data-hljs-js="' + key + '"]';
                var existing = document.querySelector(selector);
                if (existing) {
                    if (existing.getAttribute("data-hljs-loaded") === "1") {
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
                script.setAttribute("data-hljs-js", key);
                script.onload = function () {
                    script.setAttribute("data-hljs-loaded", "1");
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
            loadScriptOnce("<?php echo escape(assetUrl($this->options, 'assets/vendor/highlight/highlight.min.js')); ?>", runHighlight);
        })();
    </script>
    <script>
        (function () {
            var contents = Array.prototype.slice.call(document.querySelectorAll(".article-content, .comment-content"));
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
                    targetPre.classList.add("excalidraw-pending");
                    targetPre.setAttribute("data-excalidraw-pending", "1");
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
                    preEl.classList.remove("excalidraw-pending");
                    preEl.removeAttribute("data-excalidraw-pending");
                    preEl.classList.add("excalidraw-unavailable");
                    if (reason) {
                        preEl.setAttribute("data-excalidraw-error", reason);
                    }
                } catch (e) {}
            }

            function appendRuntimeCss(href) {
                if (!href) {
                    return;
                }
                var existing = document.querySelector("link[data-excalidraw-css]");
                if (existing) {
                    return;
                }

                try {
                    var link = document.createElement("link");
                    link.rel = "stylesheet";
                    link.href = href;
                    link.setAttribute("data-excalidraw-css", "1");
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
                var selector = 'script[data-excalidraw-js="' + key + '"][data-excalidraw-mode="' + scriptMode + '"]';
                var existing = document.querySelector(selector);
                if (existing) {
                    if (existing.getAttribute("data-excalidraw-loaded") === "1") {
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
                script.setAttribute("data-excalidraw-js", key);
                script.setAttribute("data-excalidraw-mode", scriptMode);
                script.onload = function () {
                    script.setAttribute("data-excalidraw-loaded", "1");
                    loadDone();
                };
                script.onerror = loadDone;
                document.head.appendChild(script);
            }

            function ensureRuntimeAssets(done) {
                if (
                    typeof window.ExcalidrawRuntime !== "undefined" &&
                    window.ExcalidrawRuntime &&
                    typeof window.ExcalidrawRuntime.mount === "function"
                ) {
                    done();
                    return;
                }

                if (!window.EXCALIDRAW_ASSET_PATH) {
                    window.EXCALIDRAW_ASSET_PATH = "<?php echo escape(assetUrl($this->options, 'assets/vendor/excalidraw/prod/index.js')); ?>".replace(/index\.js(?:\?.*)?$/, "");
                }
                appendRuntimeCss("<?php echo escape(assetUrl($this->options, 'assets/vendor/excalidraw/excalidraw-runtime.css')); ?>");
                loadScriptOnce("<?php echo escape(assetUrl($this->options, 'assets/vendor/excalidraw/excalidraw-runtime.mod.r2.js')); ?>", done, "module");
            }

            function resolveTheme() {
                var root = document.documentElement;
                if (!root || !root.classList) {
                    return "light";
                }
                var isDark = root.classList.contains("theme-dark") && !root.classList.contains("theme-light");
                return isDark ? "dark" : "light";
            }

            function buildEditorFigure() {
                var figure = document.createElement("figure");
                figure.className = "excalidraw-block";
                figure.setAttribute("data-excalidraw", "1");
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
                stage.className = "excalidraw-stage";
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
                editorRoot.className = "excalidraw-editor";
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
                if (preEl.getAttribute("data-excalidraw-rendered") === "1") {
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
                preEl.setAttribute("data-excalidraw-rendered", "1");

                try {
                    preEl.parentNode.replaceChild(figure, preEl);
                } catch (e) {
                    markUnavailable(preEl, "replace");
                    return;
                }

                try {
                    var runtime = window.ExcalidrawRuntime;
                    if (!runtime || typeof runtime.mount !== "function") {
                        throw new Error("runtime");
                    }
                    var mountedEditor = runtime.mount(editorRoot, normalized, { theme: resolveTheme() });
                    if (mountedEditor && typeof mountedEditor.unmount === "function") {
                        figure._excalidrawUnmount = mountedEditor.unmount;
                    }
                } catch (e) {
                    markUnavailable(figure, "mount");
                }
            }

            ensureRuntimeAssets(function () {
                var runtime = window.ExcalidrawRuntime;
                if (!runtime || typeof runtime.mount !== "function") {
                    for (var i = 0; i < targets.length; i++) {
                        var codeEl = targets[i];
                        if (!codeEl || !codeEl.closest) {
                            continue;
                        }
                        markUnavailable(codeEl.closest("pre"), "runtime");
                    }
                    if (window.console && typeof window.console.warn === "function") {
                        window.console.warn("[Theme] Excalidraw runtime unavailable.");
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
    <script>
        (function () {
            var contents = Array.prototype.slice.call(document.querySelectorAll(".article-content, .comment-content"));
            if (!contents || contents.length === 0) {
                return;
            }

            function isVegaLiteCodeBlock(codeEl) {
                if (!codeEl || !codeEl.getAttribute) {
                    return false;
                }

                var className = String(codeEl.getAttribute("class") || "").toLowerCase();
                if (
                    className.indexOf("language-vega-lite") !== -1 ||
                    className.indexOf("lang-vega-lite") !== -1 ||
                    className.indexOf("language-vl") !== -1 ||
                    className.indexOf("lang-vl") !== -1
                ) {
                    return true;
                }

                var language = String(
                    codeEl.getAttribute("data-language") ||
                    codeEl.getAttribute("data-lang") ||
                    ""
                ).toLowerCase();

                return language === "vega-lite" || language === "vl";
            }

            function isLocalVegaSrc(path) {
                if (!path || typeof path !== "string") {
                    return false;
                }
                if (path.charAt(0) !== "/" || path.indexOf("//") === 0) {
                    return false;
                }
                if (path.indexOf("..") !== -1 || path.indexOf("\\") !== -1) {
                    return false;
                }
                if (/\s/.test(path)) {
                    return false;
                }
                if (/[\x00-\x1F\x7F]/.test(path)) {
                    return false;
                }
                return true;
            }

            function findFirstUnsafeDataUrl(spec) {
                var visited = [];

                function walk(node) {
                    if (!node || typeof node !== "object") {
                        return "";
                    }

                    if (visited.indexOf(node) !== -1) {
                        return "";
                    }
                    visited.push(node);

                    if (Array.isArray(node)) {
                        for (var i = 0; i < node.length; i++) {
                            var badInArray = walk(node[i]);
                            if (badInArray) {
                                return badInArray;
                            }
                        }
                        return "";
                    }

                    if (Object.prototype.hasOwnProperty.call(node, "data")) {
                        var dataNode = node.data;
                        if (dataNode && typeof dataNode === "object" && !Array.isArray(dataNode)) {
                            if (Object.prototype.hasOwnProperty.call(dataNode, "url")) {
                                var urlValue = dataNode.url;
                                if (typeof urlValue !== "string") {
                                    return "[non-string]";
                                }

                                var cleanUrl = String(urlValue || "").trim();
                                if (!isLocalVegaSrc(cleanUrl)) {
                                    return cleanUrl || "[empty]";
                                }
                            }
                        }
                    }

                    for (var key in node) {
                        if (!Object.prototype.hasOwnProperty.call(node, key)) {
                            continue;
                        }

                        var badInObject = walk(node[key]);
                        if (badInObject) {
                            return badInObject;
                        }
                    }

                    return "";
                }

                return walk(spec);
            }

            var shortcodeTargets = [];
            var codeTargets = [];
            for (var c = 0; c < contents.length; c++) {
                var content = contents[c];
                if (!content || !content.querySelectorAll) {
                    continue;
                }

                var embeds = content.querySelectorAll('[data-embed-type="vega"]');
                for (var e = 0; e < embeds.length; e++) {
                    if (embeds[e]) {
                        shortcodeTargets.push(embeds[e]);
                    }
                }

                var blocks = content.querySelectorAll("pre code");
                for (var i = 0; i < blocks.length; i++) {
                    var codeEl = blocks[i];
                    if (!codeEl || !isVegaLiteCodeBlock(codeEl)) {
                        continue;
                    }
                    codeTargets.push(codeEl);
                }
            }

            if (shortcodeTargets.length === 0 && codeTargets.length === 0) {
                return;
            }

            function normalizeRenderer(raw) {
                var value = String(raw || "").trim().toLowerCase();
                return value === "canvas" ? "canvas" : "svg";
            }

            function normalizeBoolean(raw, fallback) {
                var value = String(raw || "").trim().toLowerCase();
                if (!value) {
                    return !!fallback;
                }
                if (value === "1" || value === "true" || value === "yes" || value === "on") {
                    return true;
                }
                if (value === "0" || value === "false" || value === "no" || value === "off") {
                    return false;
                }
                return !!fallback;
            }

            function normalizeHeight(raw) {
                var value = parseInt(String(raw || "").trim(), 10);
                if (!isFinite(value) || value <= 0) {
                    return 0;
                }
                if (value < 120) {
                    value = 120;
                } else if (value > 2000) {
                    value = 2000;
                }
                return value;
            }

            function parseStrictJsonSpec(rawText) {
                var text = String(rawText || "").replace(/^\uFEFF/, "").trim();
                if (!text) {
                    return null;
                }

                try {
                    var parsed = JSON.parse(text);
                    if (!parsed || typeof parsed !== "object" || Array.isArray(parsed)) {
                        return null;
                    }
                    return parsed;
                } catch (e) {
                    return null;
                }
            }

            function cloneSpec(spec) {
                try {
                    return JSON.parse(JSON.stringify(spec));
                } catch (e) {
                    return spec;
                }
            }

            function isFiniteNumber(value) {
                return typeof value === "number" && isFinite(value);
            }

            function hasCustomAutosize(spec) {
                if (!spec || typeof spec !== "object" || Array.isArray(spec)) {
                    return false;
                }
                return spec.autosize !== undefined && spec.autosize !== null && spec.autosize !== "";
            }

            function shouldInjectFitAutosize(spec) {
                if (!spec || typeof spec !== "object" || Array.isArray(spec)) {
                    return false;
                }
                if (hasCustomAutosize(spec)) {
                    return false;
                }
                return isFiniteNumber(spec.width) || isFiniteNumber(spec.height);
            }

            function applyDefaultFitAutosize(spec) {
                if (!shouldInjectFitAutosize(spec)) {
                    return {
                        spec: spec,
                        injected: false
                    };
                }

                var cloned = cloneSpec(spec);
                if (!cloned || typeof cloned !== "object" || Array.isArray(cloned) || cloned === spec) {
                    return {
                        spec: spec,
                        injected: false
                    };
                }

                cloned.autosize = {
                    type: "fit",
                    contains: "padding"
                };
                return {
                    spec: cloned,
                    injected: true
                };
            }

            function clearStage(stageEl) {
                if (!stageEl) {
                    return;
                }
                while (stageEl.firstChild) {
                    stageEl.removeChild(stageEl.firstChild);
                }
            }

            function setStageMinHeight(stageEl, height) {
                if (!stageEl || !stageEl.style) {
                    return;
                }
                if (height > 0) {
                    stageEl.style.minHeight = height + "px";
                    return;
                }
                stageEl.style.minHeight = "0";
            }

            var activeVegaOverlay = null;
            var expandSyncTimer = 0;

            function isNarrowVegaViewport() {
                var docEl = document.documentElement;
                var width = window.innerWidth || (docEl && docEl.clientWidth) || 0;
                if (width > 0 && width <= 980) {
                    return true;
                }
                if (!window.matchMedia) {
                    return false;
                }
                try {
                    return window.matchMedia("(max-width: 980px)").matches;
                } catch (e) {
                    return false;
                }
            }

            function readChartIntrinsicWidth(stageEl) {
                if (!stageEl || !stageEl.querySelector) {
                    return 0;
                }

                var svgEl = stageEl.querySelector("svg");
                if (svgEl) {
                    var svgWidth = parseFloat(String(svgEl.getAttribute("width") || "").trim());
                    if (isFiniteNumber(svgWidth) && svgWidth > 0) {
                        return svgWidth;
                    }

                    var viewBox = String(svgEl.getAttribute("viewBox") || "").trim();
                    if (viewBox) {
                        var parts = viewBox.split(/[\s,]+/);
                        if (parts.length === 4) {
                            var viewWidth = parseFloat(parts[2]);
                            if (isFiniteNumber(viewWidth) && viewWidth > 0) {
                                return viewWidth;
                            }
                        }
                    }
                }

                var canvasEl = stageEl.querySelector("canvas");
                if (canvasEl && isFiniteNumber(canvasEl.width) && canvasEl.width > 0) {
                    return canvasEl.width;
                }

                return stageEl.scrollWidth || 0;
            }

            function findPrimaryChartGraphic(stageEl) {
                if (!stageEl || !stageEl.querySelector) {
                    return null;
                }
                return stageEl.querySelector(".vega-embed > svg, .vega-embed > canvas, svg, canvas");
            }

            function measureChartGraphicSize(graphicEl) {
                if (!graphicEl) {
                    return {
                        width: 0,
                        height: 0
                    };
                }

                var tagName = String(graphicEl.tagName || "").toLowerCase();
                if (tagName === "svg") {
                    var svgWidth = parseFloat(String(graphicEl.getAttribute("width") || "").trim());
                    var svgHeight = parseFloat(String(graphicEl.getAttribute("height") || "").trim());
                    if (isFiniteNumber(svgWidth) && svgWidth > 0 && isFiniteNumber(svgHeight) && svgHeight > 0) {
                        return {
                            width: svgWidth,
                            height: svgHeight
                        };
                    }

                    var viewBox = String(graphicEl.getAttribute("viewBox") || "").trim();
                    if (viewBox) {
                        var vbParts = viewBox.split(/[\s,]+/);
                        if (vbParts.length === 4) {
                            var vbWidth = parseFloat(vbParts[2]);
                            var vbHeight = parseFloat(vbParts[3]);
                            if (isFiniteNumber(vbWidth) && vbWidth > 0 && isFiniteNumber(vbHeight) && vbHeight > 0) {
                                return {
                                    width: vbWidth,
                                    height: vbHeight
                                };
                            }
                        }
                    }
                }

                if (tagName === "canvas") {
                    var canvasWidth = parseFloat(String(graphicEl.width || 0));
                    var canvasHeight = parseFloat(String(graphicEl.height || 0));
                    if (isFiniteNumber(canvasWidth) && canvasWidth > 0 && isFiniteNumber(canvasHeight) && canvasHeight > 0) {
                        return {
                            width: canvasWidth,
                            height: canvasHeight
                        };
                    }
                }

                var rect = null;
                try {
                    rect = graphicEl.getBoundingClientRect();
                } catch (e) {}
                var rectWidth = rect && isFiniteNumber(rect.width) ? rect.width : 0;
                var rectHeight = rect && isFiniteNumber(rect.height) ? rect.height : 0;
                return {
                    width: rectWidth > 0 ? rectWidth : 0,
                    height: rectHeight > 0 ? rectHeight : 0
                };
            }

            function restoreOverlayGraphicSize(state) {
                if (!state || !state.graphicEl || !state.graphicEl.style) {
                    return;
                }
                state.graphicEl.style.width = state.graphicInlineWidth || "";
                state.graphicEl.style.height = state.graphicInlineHeight || "";
            }

            function applyOverlayMinimumFill(state) {
                if (!state || !state.stageWrap || !state.stageEl) {
                    return;
                }

                var graphicEl = findPrimaryChartGraphic(state.stageEl);
                if (!graphicEl || !graphicEl.style) {
                    return;
                }

                if (state.graphicEl !== graphicEl) {
                    restoreOverlayGraphicSize(state);
                    state.graphicEl = graphicEl;
                    state.graphicInlineWidth = graphicEl.style.width || "";
                    state.graphicInlineHeight = graphicEl.style.height || "";
                }

                var size = measureChartGraphicSize(graphicEl);
                var wrapRect = null;
                try {
                    wrapRect = state.stageWrap.getBoundingClientRect();
                } catch (e) {}

                var availWidth = wrapRect && isFiniteNumber(wrapRect.width) ? wrapRect.width : 0;
                var availHeight = wrapRect && isFiniteNumber(wrapRect.height) ? wrapRect.height : 0;
                if (!(isFiniteNumber(size.width) && size.width > 0 && isFiniteNumber(size.height) && size.height > 0 && isFiniteNumber(availWidth) && availWidth > 0 && isFiniteNumber(availHeight) && availHeight > 0)) {
                    return;
                }

                // Guarantee that at least one dimension fills the fullscreen viewport.
                if (size.width >= availWidth || size.height >= availHeight) {
                    restoreOverlayGraphicSize(state);
                    return;
                }

                var scale = Math.max(availWidth / size.width, availHeight / size.height);
                if (!isFiniteNumber(scale) || scale <= 1) {
                    restoreOverlayGraphicSize(state);
                    return;
                }

                graphicEl.style.width = Math.ceil(size.width * scale) + "px";
                graphicEl.style.height = Math.ceil(size.height * scale) + "px";
            }

            function syncExpandability(hostEl, stageEl) {
                if (!hostEl || !stageEl || !hostEl.setAttribute || !hostEl.removeAttribute) {
                    return;
                }

                var canExpand = false;
                var chartWidth = readChartIntrinsicWidth(stageEl);
                if (chartWidth > 0) {
                    canExpand = true;
                }

                try {
                    if (canExpand) {
                        hostEl.setAttribute("data-vega-expandable", "1");
                    } else {
                        hostEl.removeAttribute("data-vega-expandable");
                    }
                } catch (e) {}
            }

            function currentFullscreenElement() {
                return document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement || null;
            }

            function requestElementFullscreen(el) {
                if (!el) {
                    return;
                }
                var requestFn = el.requestFullscreen || el.webkitRequestFullscreen || el.msRequestFullscreen;
                if (typeof requestFn !== "function") {
                    return;
                }
                try {
                    var result = requestFn.call(el);
                    if (result && typeof result.catch === "function") {
                        result.catch(function () {});
                    }
                } catch (e) {}
            }

            function exitAnyFullscreen() {
                var exitFn = document.exitFullscreen || document.webkitExitFullscreen || document.msExitFullscreen;
                if (typeof exitFn !== "function") {
                    return;
                }
                try {
                    var result = exitFn.call(document);
                    if (result && typeof result.catch === "function") {
                        result.catch(function () {});
                    }
                } catch (e) {}
            }

            function requestLandscapeOrientationLock() {
                try {
                    if (!window.screen || !window.screen.orientation || typeof window.screen.orientation.lock !== "function") {
                        return;
                    }
                    var lockResult = window.screen.orientation.lock("landscape");
                    if (lockResult && typeof lockResult.catch === "function") {
                        lockResult.catch(function () {});
                    }
                } catch (e) {}
            }

            function releaseOrientationLock() {
                try {
                    if (!window.screen || !window.screen.orientation || typeof window.screen.orientation.unlock !== "function") {
                        return;
                    }
                    window.screen.orientation.unlock();
                } catch (e) {}
            }

            function closeVegaOverlay(skipExitFullscreen) {
                if (!activeVegaOverlay) {
                    return;
                }

                var state = activeVegaOverlay;
                activeVegaOverlay = null;

                try {
                    document.removeEventListener("fullscreenchange", state.onFullscreenChange);
                    document.removeEventListener("webkitfullscreenchange", state.onFullscreenChange);
                    document.removeEventListener("MSFullscreenChange", state.onFullscreenChange);
                    document.removeEventListener("keydown", state.onKeydown, true);
                    window.removeEventListener("resize", state.onWindowResize);
                } catch (e) {}

                if (!skipExitFullscreen && currentFullscreenElement()) {
                    exitAnyFullscreen();
                }

                releaseOrientationLock();
                restoreOverlayGraphicSize(state);

                try {
                    if (state.placeholder && state.placeholder.parentNode) {
                        state.placeholder.parentNode.insertBefore(state.stageEl, state.placeholder);
                        state.placeholder.parentNode.removeChild(state.placeholder);
                    } else if (state.stageParent && state.stageParent.appendChild) {
                        state.stageParent.appendChild(state.stageEl);
                    }
                } catch (e) {}

                try {
                    if (state.overlay && state.overlay.parentNode) {
                        state.overlay.parentNode.removeChild(state.overlay);
                    }
                } catch (e) {}

                try {
                    if (state.hostEl && state.hostEl.removeAttribute) {
                        state.hostEl.removeAttribute("data-vega-overlay-open");
                    }
                    document.documentElement.classList.remove("vega-overlay-open");
                } catch (e) {}

                try {
                    if (state.focusEl && typeof state.focusEl.focus === "function") {
                        state.focusEl.focus();
                    }
                } catch (e) {}

                syncExpandability(state.hostEl, state.stageEl);
            }

            function openVegaOverlay(hostEl, stageEl) {
                if (!hostEl || !stageEl || activeVegaOverlay) {
                    return;
                }
                if (String(hostEl.getAttribute("data-vega-expandable") || "") !== "1") {
                    return;
                }

                var stageParent = stageEl.parentNode;
                if (!stageParent) {
                    return;
                }

                var placeholder = document.createComment("vega-stage-placeholder");
                try {
                    stageParent.insertBefore(placeholder, stageEl);
                } catch (e) {
                    return;
                }

                var overlay = document.createElement("div");
                overlay.className = "vega-embed-overlay";

                var frame = document.createElement("div");
                frame.className = "vega-embed-overlay-frame";

                var closeBtn = document.createElement("button");
                closeBtn.type = "button";
                closeBtn.className = "vega-embed-overlay-close";
                closeBtn.setAttribute("aria-label", "关闭放大图表");
                closeBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>';

                var stageWrap = document.createElement("div");
                stageWrap.className = "vega-embed-overlay-stage";

                try {
                    stageWrap.appendChild(stageEl);
                } catch (e) {
                    return;
                }

                frame.appendChild(closeBtn);
                frame.appendChild(stageWrap);
                overlay.appendChild(frame);

                var focusEl = document.activeElement;
                document.body.appendChild(overlay);

                try {
                    hostEl.setAttribute("data-vega-overlay-open", "1");
                    document.documentElement.classList.add("vega-overlay-open");
                } catch (e) {}

                function onKeydown(event) {
                    if (!event) {
                        return;
                    }
                    if (event.key === "Escape" || event.key === "Esc") {
                        event.preventDefault();
                        closeVegaOverlay(false);
                    }
                }

                function onFullscreenChange() {
                    if (!activeVegaOverlay) {
                        return;
                    }
                    var fullscreenEl = currentFullscreenElement();
                    if (fullscreenEl === activeVegaOverlay.frame) {
                        activeVegaOverlay.wasFullscreen = true;
                        applyOverlayMinimumFill(activeVegaOverlay);
                        return;
                    }
                    if (activeVegaOverlay.wasFullscreen) {
                        closeVegaOverlay(true);
                    }
                }

                function onWindowResize() {
                    if (!activeVegaOverlay) {
                        return;
                    }
                    window.requestAnimationFrame(function () {
                        if (!activeVegaOverlay) {
                            return;
                        }
                        applyOverlayMinimumFill(activeVegaOverlay);
                    });
                }

                closeBtn.addEventListener("click", function (event) {
                    if (event) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    closeVegaOverlay(false);
                });

                activeVegaOverlay = {
                    hostEl: hostEl,
                    stageEl: stageEl,
                    stageParent: stageParent,
                    placeholder: placeholder,
                    overlay: overlay,
                    frame: frame,
                    focusEl: focusEl,
                    onKeydown: onKeydown,
                    onFullscreenChange: onFullscreenChange,
                    onWindowResize: onWindowResize,
                    wasFullscreen: false
                };

                document.addEventListener("keydown", onKeydown, true);
                document.addEventListener("fullscreenchange", onFullscreenChange);
                document.addEventListener("webkitfullscreenchange", onFullscreenChange);
                document.addEventListener("MSFullscreenChange", onFullscreenChange);
                window.addEventListener("resize", onWindowResize);

                if (isNarrowVegaViewport()) {
                    requestElementFullscreen(frame);
                    window.setTimeout(requestLandscapeOrientationLock, 80);
                }
                window.setTimeout(function () {
                    if (!activeVegaOverlay) {
                        return;
                    }
                    applyOverlayMinimumFill(activeVegaOverlay);
                }, 60);
                window.setTimeout(function () {
                    if (!activeVegaOverlay) {
                        return;
                    }
                    applyOverlayMinimumFill(activeVegaOverlay);
                }, 240);

                try {
                    closeBtn.focus();
                } catch (e) {}
            }

            function ensureStageClickToExpand(hostEl, stageEl) {
                if (!hostEl || !stageEl || !stageEl.addEventListener) {
                    return;
                }
                if (hostEl.getAttribute("data-vega-expand-click-init") === "1") {
                    return;
                }

                stageEl.addEventListener("click", function (event) {
                    if (!event) {
                        return;
                    }
                    if (event.defaultPrevented) {
                        return;
                    }
                    if (activeVegaOverlay) {
                        return;
                    }
                    openVegaOverlay(hostEl, stageEl);
                });

                hostEl.setAttribute("data-vega-expand-click-init", "1");
            }

            function syncAllRenderedVegaExpandability() {
                var allCharts = document.querySelectorAll(".vega-embed-shortcode");
                for (var i = 0; i < allCharts.length; i++) {
                    var hostEl = allCharts[i];
                    if (!hostEl || !hostEl.querySelector) {
                        continue;
                    }
                    var stageEl = hostEl.querySelector("[data-vega-stage]");
                    if (!stageEl) {
                        continue;
                    }
                    syncExpandability(hostEl, stageEl);
                }
            }

            function bindVegaExpandabilityResizeSync() {
                if (window.__hansjackVegaResizeSyncBound) {
                    return;
                }
                window.__hansjackVegaResizeSyncBound = true;

                window.addEventListener("resize", function () {
                    if (expandSyncTimer) {
                        window.clearTimeout(expandSyncTimer);
                    }
                    expandSyncTimer = window.setTimeout(function () {
                        syncAllRenderedVegaExpandability();
                    }, 120);
                });
            }

            function ensureStageNode(hostEl) {
                if (!hostEl || !hostEl.querySelector) {
                    return null;
                }

                var stageEl = hostEl.querySelector("[data-vega-stage]");
                if (stageEl) {
                    return stageEl;
                }

                try {
                    stageEl = document.createElement("div");
                    stageEl.className = "vega-embed-stage";
                    stageEl.setAttribute("data-vega-stage", "1");
                    stageEl.setAttribute("aria-label", "Vega-Lite chart");
                    hostEl.appendChild(stageEl);
                    return stageEl;
                } catch (e) {
                    return null;
                }
            }

            function clearError(hostEl) {
                if (!hostEl || !hostEl.querySelector) {
                    return;
                }
                var existing = hostEl.querySelector(".vega-embed-error");
                if (existing && existing.parentNode) {
                    try {
                        existing.parentNode.removeChild(existing);
                    } catch (e) {}
                }
                try {
                    hostEl.removeAttribute("data-vega-status");
                    hostEl.classList.remove("vega-embed-unavailable");
                    hostEl.removeAttribute("data-vega-error");
                } catch (e) {}
            }

            function appendError(hostEl, message) {
                if (!hostEl || !hostEl.querySelector || !message) {
                    return;
                }

                var text = String(message);
                var errorNode = hostEl.querySelector(".vega-embed-error");
                if (!errorNode) {
                    try {
                        errorNode = document.createElement("p");
                        errorNode.className = "vega-embed-error";
                        hostEl.appendChild(errorNode);
                    } catch (e) {
                        return;
                    }
                }

                try {
                    errorNode.textContent = text;
                    hostEl.setAttribute("data-vega-status", "error");
                } catch (e) {}
            }

            function markUnavailable(nodeEl, reason) {
                if (!nodeEl || !nodeEl.classList) {
                    return;
                }
                try {
                    nodeEl.classList.add("vega-embed-unavailable");
                    if (reason) {
                        nodeEl.setAttribute("data-vega-error", reason);
                    }
                } catch (e) {}
            }

            function loadScriptOnce(src, done) {
                var doneFn = typeof done === "function" ? done : function () {};
                if (!src) {
                    doneFn();
                    return;
                }

                var key = src.replace(/[^a-z0-9]/gi, "_");
                var selector = 'script[data-vega-js="' + key + '"]';
                var existing = document.querySelector(selector);
                if (existing) {
                    if (existing.getAttribute("data-vega-loaded") === "1") {
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
                script.setAttribute("data-vega-js", key);
                script.onload = function () {
                    script.setAttribute("data-vega-loaded", "1");
                    doneFn();
                };
                script.onerror = doneFn;
                document.head.appendChild(script);
            }

            function ensureVegaAssets(done) {
                if (
                    typeof window.vega !== "undefined" &&
                    typeof window.vegaLite !== "undefined" &&
                    typeof window.vegaEmbed === "function"
                ) {
                    done();
                    return;
                }

                loadScriptOnce("<?php echo escape(assetUrl($this->options, 'assets/vendor/vega/vega.min.js')); ?>", function () {
                    loadScriptOnce("<?php echo escape(assetUrl($this->options, 'assets/vendor/vega/vega-lite.min.js')); ?>", function () {
                        loadScriptOnce("<?php echo escape(assetUrl($this->options, 'assets/vendor/vega/vega-embed.min.js')); ?>", done);
                    });
                });
            }

            var specCache = {};
            function fetchSpecBySrc(src) {
                if (specCache[src]) {
                    return specCache[src];
                }

                specCache[src] = fetch(src, {
                    credentials: "same-origin",
                    cache: "default"
                }).then(function (resp) {
                    if (!resp || !resp.ok) {
                        throw new Error("http");
                    }
                    return resp.text();
                }).then(function (text) {
                    var spec = parseStrictJsonSpec(text);
                    if (!spec) {
                        throw new Error("json");
                    }
                    return spec;
                });

                return specCache[src];
            }

            function renderIntoStage(hostEl, stageEl, spec, options) {
                if (!hostEl || !stageEl || !spec || typeof window.vegaEmbed !== "function") {
                    return;
                }

                clearError(hostEl);
                ensureStageClickToExpand(hostEl, stageEl);

                var unsafeDataUrl = findFirstUnsafeDataUrl(spec);
                if (unsafeDataUrl) {
                    markUnavailable(hostEl, "data-url");
                    appendError(hostEl, "仅允许站内数据源");
                    return;
                }

                var nextSpec = spec;
                var opts = options || {};
                var height = normalizeHeight(opts.height);
                setStageMinHeight(stageEl, height);
                if (height > 0) {
                    if (typeof spec.height === "undefined" || spec.height === null || spec.height === "") {
                        nextSpec = cloneSpec(spec);
                        if (nextSpec && typeof nextSpec === "object") {
                            nextSpec.height = height;
                        }
                    }
                }

                var baseRenderSpec = nextSpec;
                var fitPrepared = applyDefaultFitAutosize(baseRenderSpec);
                var renderSpec = fitPrepared.spec;
                var embedOptions = {
                    mode: "vega-lite",
                    renderer: normalizeRenderer(opts.renderer),
                    actions: normalizeBoolean(opts.actions, false)
                };

                function markRendered() {
                    try {
                        hostEl.setAttribute("data-vega-rendered", "1");
                        hostEl.removeAttribute("data-vega-status");
                    } catch (e) {}
                    syncExpandability(hostEl, stageEl);
                }

                function markRenderError() {
                    markUnavailable(hostEl, "render");
                    appendError(hostEl, "Vega-Lite 图表渲染失败。");
                    try {
                        hostEl.removeAttribute("data-vega-expandable");
                    } catch (e) {}
                }

                function embedWith(renderTargetSpec) {
                    clearStage(stageEl);
                    return Promise.resolve(window.vegaEmbed(stageEl, renderTargetSpec, embedOptions));
                }

                embedWith(renderSpec).then(markRendered).catch(function () {
                    if (!fitPrepared.injected) {
                        markRenderError();
                        return;
                    }

                    embedWith(baseRenderSpec).then(markRendered).catch(markRenderError);
                });
            }

            function renderShortcodeTarget(target) {
                if (!target || target.getAttribute("data-vega-inited") === "1") {
                    return;
                }
                target.setAttribute("data-vega-inited", "1");

                var src = String(target.getAttribute("data-vega-src") || "").trim();
                if (!isLocalVegaSrc(src)) {
                    markUnavailable(target, "src");
                    appendError(target, "Vega 图表 src 必须为站内路径。");
                    return;
                }

                var stageEl = ensureStageNode(target);
                if (!stageEl) {
                    markUnavailable(target, "stage");
                    appendError(target, "Vega 图表容器不可用。");
                    return;
                }

                var height = normalizeHeight(
                    target.getAttribute("data-vega-height") ||
                    stageEl.getAttribute("data-vega-height") ||
                    ""
                );
                setStageMinHeight(stageEl, height);

                fetchSpecBySrc(src).then(function (spec) {
                    renderIntoStage(target, stageEl, spec, {
                        renderer: target.getAttribute("data-vega-renderer"),
                        actions: target.getAttribute("data-vega-actions"),
                        height: height
                    });
                }).catch(function () {
                    markUnavailable(target, "fetch");
                    appendError(target, "Vega 源文件无法加载或 JSON 不合法。");
                });
            }

            function renderCodeTarget(codeEl) {
                if (!codeEl || !codeEl.closest) {
                    return;
                }

                var preEl = codeEl.closest("pre");
                if (!preEl || !preEl.parentNode || preEl.getAttribute("data-vega-inited") === "1") {
                    return;
                }
                preEl.setAttribute("data-vega-inited", "1");

                var spec = parseStrictJsonSpec(codeEl.textContent || "");
                if (!spec) {
                    markUnavailable(preEl, "json");
                    return;
                }

                var wrapper = document.createElement("section");
                wrapper.className = "vega-embed-shortcode vega-embed-from-code";
                wrapper.setAttribute("data-embed-type", "vega");
                wrapper.setAttribute("data-vega-from", "code");
                wrapper.style.margin = "1rem 0";

                var stageEl = document.createElement("div");
                stageEl.className = "vega-embed-stage";
                stageEl.setAttribute("data-vega-stage", "1");
                stageEl.setAttribute("aria-label", "Vega-Lite chart");
                wrapper.appendChild(stageEl);

                try {
                    preEl.parentNode.replaceChild(wrapper, preEl);
                } catch (e) {
                    markUnavailable(preEl, "replace");
                    return;
                }

                renderIntoStage(wrapper, stageEl, spec, {
                    renderer: "svg",
                    actions: false,
                    height: 0
                });
            }

            bindVegaExpandabilityResizeSync();

            ensureVegaAssets(function () {
                if (
                    typeof window.vega === "undefined" ||
                    typeof window.vegaLite === "undefined" ||
                    typeof window.vegaEmbed !== "function"
                ) {
                    for (var s = 0; s < shortcodeTargets.length; s++) {
                        markUnavailable(shortcodeTargets[s], "runtime");
                        appendError(shortcodeTargets[s], "Vega 运行时加载失败。");
                    }
                    for (var k = 0; k < codeTargets.length; k++) {
                        var pre = codeTargets[k] && codeTargets[k].closest ? codeTargets[k].closest("pre") : null;
                        markUnavailable(pre, "runtime");
                    }
                    if (window.console && typeof window.console.warn === "function") {
                        window.console.warn("[Theme] Vega runtime unavailable.");
                    }
                    return;
                }

                for (var i = 0; i < shortcodeTargets.length; i++) {
                    renderShortcodeTarget(shortcodeTargets[i]);
                }
                for (var j = 0; j < codeTargets.length; j++) {
                    renderCodeTarget(codeTargets[j]);
                }

                window.setTimeout(syncAllRenderedVegaExpandability, 80);
            });
        })();
    </script>
    <script src="<?php echo escape(assetUrl($this->options, 'assets/vendor/medium-zoom/medium-zoom.min.js')); ?>"></script>
    <script src="<?php echo escape(assetUrlSmart($this->options, 'assets/js/content-static.js')); ?>"></script>
    <script>
        (function () {
            var contents = Array.prototype.slice.call(document.querySelectorAll(".article-content, .comment-content"));
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
                    if (content.dataset && content.dataset.katexRendered === "1") {
                        continue;
                    }

                    try {
                        window.renderMathInElement(content, options);
                        if (content.dataset) {
                            content.dataset.katexRendered = "1";
                        }
                    } catch (e) {}
                }
            }

            function appendKatexCss(href) {
                if (!href) {
                    return;
                }
                var cssNode = document.querySelector("link[data-katex-css]");
                if (cssNode) {
                    return;
                }

                try {
                    var link = document.createElement("link");
                    link.rel = "stylesheet";
                    link.href = href;
                    link.setAttribute("data-katex-css", "1");
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
                var selector = 'script[data-katex-js="' + key + '"]';
                var existing = document.querySelector(selector);
                if (existing) {
                    if (existing.getAttribute("data-katex-loaded") === "1") {
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
                script.setAttribute("data-katex-js", key);
                script.onload = function () {
                    script.setAttribute("data-katex-loaded", "1");
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

                appendKatexCss("<?php echo escape(assetUrl($this->options, 'assets/vendor/katex/katex.min.css')); ?>");
                loadScriptOnce("<?php echo escape(assetUrl($this->options, 'assets/vendor/katex/katex.min.js')); ?>", function () {
                    loadScriptOnce("<?php echo escape(assetUrl($this->options, 'assets/vendor/katex/contrib/mhchem.min.js')); ?>", function () {
                        loadScriptOnce("<?php echo escape(assetUrl($this->options, 'assets/vendor/katex/contrib/auto-render.min.js')); ?>", done);
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

<script>
window.__hansjackInternalLinkMeta = <?php echo $internalLinkMetaJson; ?>;
window.__hansjackLiveReloadEnabled = <?php echo $liveReloadEnabledForCurrent ? 'true' : 'false'; ?>;
window.__hansjackHighLoadDegradeEnabled = <?php echo $highLoadDegradeEnabled ? 'true' : 'false'; ?>;
</script>
<script src="<?php echo escape(assetUrlSmart($this->options, 'assets/js/footer-global-tail.js')); ?>"></script>
<script src="<?php echo escape(assetUrlSmart($this->options, 'assets/js/pjax-lite.js')); ?>"></script>

<?php $this->footer(); ?>
<?php if ($customJavaScript !== ''): ?>
<script id="custom-javascript">
<?php echo $customJavaScript; ?>
</script>
<?php endif; ?>
</body>
</html>
