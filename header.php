<?php if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
} ?>
<?php
$themeConfig = hansJackBuildThemeConfig($this->options);

// Read persisted theme choice early (cookie), so the initial HTML can render without a light flash in dark mode.
$hjThemeCookie = '';
try {
    $hjThemeCookie = isset($_COOKIE['hansjack_theme_mode']) ? (string) $_COOKIE['hansjack_theme_mode'] : '';
} catch (\Throwable $e) {
    $hjThemeCookie = '';
}
$hjThemeCookie = strtolower(trim($hjThemeCookie));
$hjHtmlThemeClass = '';
$hjHtmlThemeStyle = '';
if ($hjThemeCookie === 'dark') {
    $hjHtmlThemeClass = 'hj-theme-dark';
    $hjHtmlThemeStyle = 'background-color:#0e0e0c;color-scheme:dark;';
} elseif ($hjThemeCookie === 'light') {
    $hjHtmlThemeClass = 'hj-theme-light';
    $hjHtmlThemeStyle = 'background-color:#fffffd;color-scheme:light;';
}

$hjNeedsContentEnhance = false;
try {
    $hjNeedsContentEnhance = $this->is('post') || $this->is('page');
} catch (\Throwable $e) {
    $hjNeedsContentEnhance = false;
}

if ($hjNeedsContentEnhance) {
    $hjHtmlThemeClass = trim($hjHtmlThemeClass . ' hj-content-enhance-pending');
}

$hjNeedsKatexAssets = false;
if ($hjNeedsContentEnhance) {
    try {
        $hjNeedsKatexAssets = hansJackShouldLoadKatexAssets($this);
    } catch (\Throwable $e) {
        $hjNeedsKatexAssets = false;
    }
}

$hjCustomCss = trim((string) ($this->options->hjCustomCss ?? ''));
if ($hjCustomCss !== '') {
    $hjCustomCss = str_ireplace('</style>', '<\/style>', $hjCustomCss);
}
?>
<!DOCTYPE html>
<html lang="zh-CN"<?php if ($hjHtmlThemeClass !== '') {
    echo ' class="' . htmlspecialchars($hjHtmlThemeClass, ENT_QUOTES, 'UTF-8') . '"';
} ?><?php if ($hjHtmlThemeStyle !== '') {
    echo ' style="' . htmlspecialchars($hjHtmlThemeStyle, ENT_QUOTES, 'UTF-8') . '"';
} ?>>
<head>
    <meta charset="<?php $this->options->charset(); ?>">
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="color-scheme" content="light dark">
    <meta name="supported-color-schemes" content="light dark">
    <meta name="theme-color" content="#fffffd" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0e0e0c" media="(prefers-color-scheme: dark)">
    <title><?php $this->archiveTitle([
            'category' => _t('分类 %s 下的文章'),
            'search'   => _t('包含关键字 %s 的文章'),
            'tag'      => _t('标签 %s 下的文章'),
            'author'   => _t('%s 发布的文章')
        ], '', ' - '); ?><?php $this->options->title(); ?></title>
    <script>
        (function () {
            var root = document.documentElement;
            if (!root) {
                return;
            }

            var storageKey = "hansjack_theme_mode";
            var mode = "";
            var hasExplicit = false;

            function readCookie(key) {
                var value = "";
                try {
                    var cookies = document.cookie ? document.cookie.split(";") : [];
                    for (var i = 0; i < cookies.length; i++) {
                        var part = cookies[i].trim();
                        if (!part) {
                            continue;
                        }
                        if (part.indexOf(key + "=") === 0) {
                            value = decodeURIComponent(part.slice(key.length + 1));
                            break;
                        }
                    }
                } catch (e) {
                    value = "";
                }
                return value;
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

            if (root.classList.contains("hj-theme-dark")) {
                mode = "dark";
                hasExplicit = true;
            } else if (root.classList.contains("hj-theme-light")) {
                mode = "light";
                hasExplicit = true;
            }

            if (!mode) {
                var cookie = readCookie(storageKey);
                if (cookie === "dark" || cookie === "light") {
                    mode = cookie;
                    hasExplicit = true;
                    root.classList.add(mode === "dark" ? "hj-theme-dark" : "hj-theme-light");
                }
            }

            try {
                if (!mode && window.localStorage) {
                    mode = window.localStorage.getItem(storageKey) || "";
                    if (mode === "dark" || mode === "light") {
                        hasExplicit = true;
                        root.classList.add(mode === "dark" ? "hj-theme-dark" : "hj-theme-light");
                        writeCookie(mode);
                    } else {
                        mode = "";
                    }
                }
            } catch (e) {
                mode = "";
            }

            if (mode !== "dark" && mode !== "light") {
                try {
                    mode = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
                } catch (e) {
                    mode = "light";
                }
            }

            if (mode === "dark") {
                root.classList.remove("hj-theme-light");
                root.classList.add("hj-theme-dark");
            } else {
                root.classList.remove("hj-theme-dark");
                root.classList.add("hj-theme-light");
            }

            // Avoid a white flash before the main stylesheet loads.
            root.style.backgroundColor = mode === "dark" ? "#0e0e0c" : "#fffffd";
            root.style.colorScheme = mode;

            // Keep localStorage and cookie in sync for explicit choices only (don't lock "auto" mode).
            if (hasExplicit) {
                writeCookie(mode);
                try {
                    if (window.localStorage) {
                        window.localStorage.setItem(storageKey, mode);
                    }
                } catch (e) {}
            }
        })();
    </script>
    <style>
        html,
        body {
            background-color: #fffffd;
            color: #2a2a28;
        }

        @media (prefers-color-scheme: dark) {
            html:not(.hj-theme-light),
            html:not(.hj-theme-light) body {
                background-color: #0e0e0c;
                color: #dddddb;
                color-scheme: dark;
            }
        }

        html.hj-theme-dark,
        html.hj-theme-dark body {
            background-color: #0e0e0c;
            color: #dddddb;
            color-scheme: dark;
        }

        html.hj-theme-light,
        html.hj-theme-light body {
            background-color: #fffffd;
            color: #2a2a28;
            color-scheme: light;
        }

        html.hj-content-enhance-pending :is(.hj-article-content, .hj-comment-content) {
            visibility: hidden;
        }
    </style>
    <?php if ($hjNeedsContentEnhance): ?>
        <noscript>
            <style>
                html.hj-content-enhance-pending :is(.hj-article-content, .hj-comment-content) {
                    visibility: visible !important;
                }
            </style>
        </noscript>
    <?php endif; ?>
    <?php if ($hjNeedsKatexAssets): ?>
        <link rel="stylesheet" href="<?php $this->options->themeUrl('assets/vendor/katex/katex.min.css'); ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php $this->options->themeUrl('assets/fonts/SourceHanSerifCN/result.css'); ?>">
    <link rel="stylesheet" href="<?php $this->options->themeUrl('style.css'); ?>">
    <?php $this->header(); ?>
    <?php if ($hjCustomCss !== ''): ?>
    <style id="hj-custom-css">
<?php echo $hjCustomCss; ?>
    </style>
    <?php endif; ?>
</head>
<?php
$hjBodyClasses = [];
$hjBodyClasses[] = $this->is('index') ? 'hj-page-index' : 'hj-page-inner';
if ($this->is('page', 'posts')) {
    $hjBodyClasses[] = 'hj-page-posts';
}
if ($this->is('page', 'notes')) {
    $hjBodyClasses[] = 'hj-page-notes';
}
if ($this->is('page', 'memos')) {
    $hjBodyClasses[] = 'hj-page-memory';
}
if ($this->is('category')) {
    $hjBodyClasses[] = 'hj-page-category';
}
if ($this->is('tag')) {
    $hjBodyClasses[] = 'hj-page-tag';
}
?>
<body class="<?php echo implode(' ', $hjBodyClasses); ?>">
<header class="hj-header" role="banner">
    <div class="hj-shell">
            <div class="hj-header-inner">
                <div class="hj-header-left">
                    <a class="hj-brand" href="<?php $this->options->siteUrl(); ?>" aria-label="<?php echo hansJackEscape($themeConfig['brandName']); ?>" title="<?php echo hansJackEscape($themeConfig['brandName']); ?>">
                        <span class="hj-brand-text"><?php echo hansJackEscape($themeConfig['brandName']); ?></span>
                    </a>
                </div>

            <div class="hj-header-center">
                <?php
                $hjNavCategories = [];
                try {
                    $this->widget('Widget_Metas_Category_List')->to($hjNavCategoryList);
                    $hjAllCategories = [];
                    $postsRootMid = null;

                    if ($hjNavCategoryList && $hjNavCategoryList->have()) {
                        while ($hjNavCategoryList->next()) {
                            $hjAllCategories[] = [
                                'mid' => (int) ($hjNavCategoryList->mid ?? 0),
                                'parent' => (int) ($hjNavCategoryList->parent ?? 0),
                                'slug' => (string) ($hjNavCategoryList->slug ?? ''),
                                'name' => (string) ($hjNavCategoryList->name ?? ''),
                                'url' => (string) ($hjNavCategoryList->permalink ?? ''),
                            ];
                        }
                    }

                    foreach ($hjAllCategories as $cat) {
                        if ($cat['slug'] === 'posts') {
                            $postsRootMid = (int) $cat['mid'];
                            break;
                        }
                    }

                    if ($postsRootMid) {
                        foreach ($hjAllCategories as $cat) {
                            if ((int) $cat['parent'] !== $postsRootMid) {
                                continue;
                            }
                            if ($cat['name'] === '' || $cat['url'] === '') {
                                continue;
                            }
                            $hjNavCategories[] = [
                                'name' => $cat['name'],
                                'url' => $cat['url'],
                            ];
                        }
                    }
                } catch (\Throwable $e) {
                    $hjNavCategories = [];
                }
                ?>
                <nav class="hj-nav hj-nav-desktop" aria-label="<?php _e('主导航'); ?>">
                    <?php foreach ($themeConfig['navItems'] as $item): ?>
                        <?php $navIcon = hansJackNavIconSvg($item['key']); ?>
                        <?php if ($item['key'] === 'blog'): ?>
                            <div class="hj-nav-item hj-nav-item-blog">
                                <a class="hj-nav-link<?php echo hansJackIsActiveNav($this, $item['url']) ? ' is-active' : ''; ?><?php echo $navIcon !== '' ? ' has-icon' : ''; ?>"
                                   data-nav-key="<?php echo hansJackEscape($item['key']); ?>"
                                   href="<?php echo hansJackEscape($item['url']); ?>">
                                    <span class="hj-nav-link-text"><?php echo hansJackEscape($item['label']); ?></span>
                                    <?php if ($navIcon !== ''): ?>
                                        <span class="hj-nav-icon-template" aria-hidden="true"><?php echo $navIcon; ?></span>
                                    <?php endif; ?>
                                </a>
                                <div class="hj-nav-dropdown" role="menu" aria-label="<?php _e('博文分类'); ?>">
                                    <?php if (!empty($hjNavCategories)): ?>
                                        <?php foreach ($hjNavCategories as $cat): ?>
                                            <a class="hj-user-dropdown-item" role="menuitem" href="<?php echo hansJackEscape($cat['url']); ?>">
                                                <span class="hj-user-dropdown-text"><?php echo hansJackEscape($cat['name']); ?></span>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="hj-user-dropdown-item" role="none">
                                            <span class="hj-user-dropdown-text"><?php _e('暂无分类'); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <a class="hj-nav-link<?php echo hansJackIsActiveNav($this, $item['url']) ? ' is-active' : ''; ?><?php echo $navIcon !== '' ? ' has-icon' : ''; ?>"
                               data-nav-key="<?php echo hansJackEscape($item['key']); ?>"
                               href="<?php echo hansJackEscape($item['url']); ?>">
                                <span class="hj-nav-link-text"><?php echo hansJackEscape($item['label']); ?></span>
                                <?php if ($navIcon !== ''): ?>
                                    <span class="hj-nav-icon-template" aria-hidden="true"><?php echo $navIcon; ?></span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <span class="hj-nav-float-icon" aria-hidden="true"></span>
                </nav>
            </div>

            <div class="hj-header-right">
                <button class="hj-theme-toggle" type="button" aria-label="<?php _e('切换主题'); ?>" title="<?php _e('切换主题'); ?>">
                    <span class="hj-theme-icon hj-theme-icon-sun" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sun-icon lucide-sun"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                    </span>
                    <span class="hj-theme-icon hj-theme-icon-moon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-moon-icon lucide-moon"><path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"/></svg>
                    </span>
                </button>
                <a class="hj-rss-btn" href="<?php $this->options->feedUrl(); ?>" aria-label="<?php _e('RSS'); ?>" title="<?php _e('RSS'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-rss-icon lucide-rss"><path d="M4 11a9 9 0 0 1 9 9"/><path d="M4 4a16 16 0 0 1 16 16"/><circle cx="5" cy="19" r="1"/></svg>
                </a>
                <button class="hj-rss-btn hj-nav-toggle" type="button" aria-label="<?php _e('打开菜单'); ?>" aria-haspopup="true" aria-expanded="false" title="<?php _e('菜单'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-menu-icon lucide-menu"><path d="M4 5h16"/><path d="M4 12h16"/><path d="M4 19h16"/></svg>
                </button>
                <div class="hj-pages-menu">
                    <button class="hj-pages-trigger" type="button" aria-haspopup="true" aria-expanded="false" aria-label="<?php _e('前往'); ?>" title="<?php _e('前往'); ?>">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-rocket-icon lucide-rocket" aria-hidden="true"><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09"/><path d="M9 12a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.4 22.4 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 .05 5 .05"/></svg>
                    </button>
                    <div class="hj-pages-dropdown" role="menu" aria-label="<?php _e('独立页面'); ?>">
                        <?php
                        $this->widget('Widget_Contents_Page_List')->to($pages);
                        $hasPageLink = false;
                        while ($pages->next()):
                            $slug = isset($pages->slug) ? (string) $pages->slug : '';
                            if ($slug === 'posts' || $slug === 'notes' || $slug === 'memos') {
                                continue;
                            }
                            $isLinksPage = ($slug === 'links');
                            $isAboutPage = ($slug === 'about');
                            $hasPageLink = true;
                        ?>
                            <a class="hj-user-dropdown-item" role="menuitem" href="<?php $pages->permalink(); ?>">
                                <span class="hj-user-dropdown-icon" aria-hidden="true">
                                    <?php if ($isLinksPage): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-link-icon lucide-link"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                    <?php elseif ($isAboutPage): ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-at-sign-icon lucide-at-sign"><circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-4 8"/></svg>
                                    <?php else: ?>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layers2-icon lucide-layers-2"><path d="M13 13.74a2 2 0 0 1-2 0L2.5 8.87a1 1 0 0 1 0-1.74L11 2.26a2 2 0 0 1 2 0l8.5 4.87a1 1 0 0 1 0 1.74z"/><path d="m20 14.285 1.5.845a1 1 0 0 1 0 1.74L13 21.74a2 2 0 0 1-2 0l-8.5-4.87a1 1 0 0 1 0-1.74l1.5-.845"/></svg>
                                    <?php endif; ?>
                                </span>
                                <span class="hj-user-dropdown-text"><?php $pages->title(); ?></span>
                            </a>
                        <?php endwhile; ?>
                        <?php if (!$hasPageLink): ?>
                            <div class="hj-user-dropdown-item" role="none">
                                <span class="hj-user-dropdown-text"><?php _e('暂无页面'); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if ($this->user->hasLogin()): ?>
                    <div class="hj-user-menu">
                        <button class="hj-user-trigger" type="button" aria-haspopup="true" aria-expanded="false" aria-label="<?php $this->user->screenName(); ?>" title="<?php $this->user->screenName(); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-flame-kindling-icon lucide-flame-kindling" aria-hidden="true"><path d="M12 2c1 3 2.5 3.5 3.5 4.5A5 5 0 0 1 17 10a5 5 0 1 1-10 0c0-.3 0-.6.1-.9a2 2 0 1 0 3.3-2C8 4.5 11 2 12 2Z"/><path d="m5 22 14-4"/><path d="m5 18 14 4"/></svg>
                        </button>
                        <div class="hj-user-dropdown" role="menu" aria-label="<?php _e('用户菜单'); ?>">
                            <a class="hj-user-dropdown-item" role="menuitem" href="<?php $this->options->adminUrl(); ?>">
                                <span class="hj-user-dropdown-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-square-terminal-icon lucide-square-terminal"><path d="m7 11 2-2-2-2"/><path d="M11 13h4"/><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/></svg>
                                </span>
                                <span class="hj-user-dropdown-text"><?php _e('控制台'); ?></span>
                            </a>
                            <a class="hj-user-dropdown-item" role="menuitem" href="<?php $this->options->logoutUrl(); ?>">
                                <span class="hj-user-dropdown-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-door-open-icon lucide-door-open"><path d="M11 20H2"/><path d="M11 4.562v16.157a1 1 0 0 0 1.242.97L19 20V5.562a2 2 0 0 0-1.515-1.94l-4-1A2 2 0 0 0 11 4.561z"/><path d="M11 4H8a2 2 0 0 0-2 2v14"/><path d="M14 12h.01"/><path d="M22 20h-3"/></svg>
                                </span>
                                <span class="hj-user-dropdown-text"><?php _e('退出'); ?></span>
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <a class="hj-login-btn" href="<?php $this->options->adminUrl('login.php'); ?>" aria-label="<?php _e('登录'); ?>" title="<?php _e('登录'); ?>" data-hj-open-login-modal>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-icon lucide-user-round" aria-hidden="true"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="hj-mobile-nav" aria-hidden="true">
        <div class="hj-mobile-nav-backdrop" aria-hidden="true"></div>
        <aside class="hj-mobile-nav-panel" role="dialog" aria-label="<?php _e('菜单'); ?>" aria-modal="true" tabindex="-1">
            <nav class="hj-mobile-nav-section" aria-label="<?php _e('主导航'); ?>">
                <?php foreach ($themeConfig['navItems'] as $item): ?>
                    <?php $navIcon = hansJackNavIconSvg($item['key']); ?>
                    <a class="hj-user-dropdown-item<?php echo hansJackIsActiveNav($this, $item['url']) ? ' is-active' : ''; ?>"
                       role="menuitem"
                       href="<?php echo hansJackEscape($item['url']); ?>">
                        <?php if ($navIcon !== ''): ?>
                            <span class="hj-user-dropdown-icon" aria-hidden="true"><?php echo $navIcon; ?></span>
                        <?php endif; ?>
                        <span class="hj-user-dropdown-text"><?php echo hansJackEscape($item['label']); ?></span>
                    </a>
                <?php endforeach; ?>
                <?php if (!empty($hjNavCategories)): ?>
                    <div class="hj-mobile-nav-divider" role="separator" aria-hidden="true"></div>
                    <div class="hj-mobile-nav-section-title"><?php _e('博文分类'); ?></div>
                    <?php foreach ($hjNavCategories as $cat): ?>
                        <a class="hj-user-dropdown-item hj-mobile-nav-subitem"
                           role="menuitem"
                           href="<?php echo hansJackEscape($cat['url']); ?>">
                            <span class="hj-user-dropdown-text"><?php echo hansJackEscape($cat['name']); ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </nav>

            <div class="hj-mobile-nav-divider" role="separator" aria-hidden="true"></div>
            <div class="hj-mobile-nav-section-title"><?php _e('功能'); ?></div>
            <div class="hj-mobile-nav-section" aria-label="<?php _e('功能'); ?>">
                <button class="hj-user-dropdown-item hj-mobile-nav-action" type="button" data-hj-theme-toggle="true">
                    <span class="hj-user-dropdown-icon" aria-hidden="true">
                        <span class="hj-theme-icon hj-theme-icon-sun">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                        </span>
                        <span class="hj-theme-icon hj-theme-icon-moon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.985 12.486a9 9 0 1 1-9.473-9.472c.405-.022.617.46.402.803a6 6 0 0 0 8.268 8.268c.344-.215.825-.004.803.401"/></svg>
                        </span>
                    </span>
                    <span class="hj-user-dropdown-text"><?php _e('切换主题'); ?></span>
                </button>
                <a class="hj-user-dropdown-item" role="menuitem" href="<?php $this->options->feedUrl(); ?>">
                    <span class="hj-user-dropdown-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11a9 9 0 0 1 9 9"/><path d="M4 4a16 16 0 0 1 16 16"/><circle cx="5" cy="19" r="1"/></svg>
                    </span>
                    <span class="hj-user-dropdown-text"><?php _e('RSS'); ?></span>
                </a>
            </div>

            <div class="hj-mobile-nav-divider" role="separator" aria-hidden="true"></div>
            <div class="hj-mobile-nav-section-title"><?php _e('前往'); ?></div>
            <nav class="hj-mobile-nav-section" aria-label="<?php _e('独立页面'); ?>">
                <?php
                $this->widget('Widget_Contents_Page_List')->to($hjMobilePages);
                $hjHasMobilePage = false;
                while ($hjMobilePages->next()):
                    $slug = isset($hjMobilePages->slug) ? (string) $hjMobilePages->slug : '';
                    if ($slug === 'posts' || $slug === 'notes' || $slug === 'memos') {
                        continue;
                    }
                    $hjHasMobilePage = true;
                ?>
                    <a class="hj-user-dropdown-item" role="menuitem" href="<?php $hjMobilePages->permalink(); ?>">
                        <span class="hj-user-dropdown-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09"/><path d="M9 12a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.4 22.4 0 0 1-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 .05 5 .05"/></svg>
                        </span>
                        <span class="hj-user-dropdown-text"><?php $hjMobilePages->title(); ?></span>
                    </a>
                <?php endwhile; ?>
                <?php if (!$hjHasMobilePage): ?>
                    <div class="hj-user-dropdown-item" role="none">
                        <span class="hj-user-dropdown-text"><?php _e('暂无页面'); ?></span>
                    </div>
                <?php endif; ?>
            </nav>

            <div class="hj-mobile-nav-divider" role="separator" aria-hidden="true"></div>
            <div class="hj-mobile-nav-section-title"><?php _e('用户'); ?></div>
            <nav class="hj-mobile-nav-section" aria-label="<?php _e('用户'); ?>">
                <?php if ($this->user->hasLogin()): ?>
                    <a class="hj-user-dropdown-item" role="menuitem" href="<?php $this->options->adminUrl(); ?>">
                        <span class="hj-user-dropdown-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 11 2-2-2-2"/><path d="M11 13h4"/><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/></svg>
                        </span>
                        <span class="hj-user-dropdown-text"><?php _e('控制台'); ?></span>
                    </a>
                    <a class="hj-user-dropdown-item" role="menuitem" href="<?php $this->options->logoutUrl(); ?>">
                        <span class="hj-user-dropdown-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20H2"/><path d="M11 4.562v16.157a1 1 0 0 0 1.242.97L19 20V5.562a2 2 0 0 0-1.515-1.94l-4-1A2 2 0 0 0 11 4.561z"/><path d="M11 4H8a2 2 0 0 0-2 2v14"/><path d="M14 12h.01"/><path d="M22 20h-3"/></svg>
                        </span>
                        <span class="hj-user-dropdown-text"><?php _e('退出'); ?></span>
                    </a>
                <?php else: ?>
                    <a class="hj-user-dropdown-item" role="menuitem" href="<?php $this->options->adminUrl('login.php'); ?>" data-hj-open-login-modal>
                        <span class="hj-user-dropdown-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                        </span>
                        <span class="hj-user-dropdown-text"><?php _e('登录'); ?></span>
                    </a>
                <?php endif; ?>
            </nav>
        </aside>
    </div>
</header>
<div class="hj-shell hj-main-shell">
