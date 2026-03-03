<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');
?>

<main class="main" role="main">
    <article class="article" data-post-cid="<?php echo (int) ($this->cid ?? 0); ?>">
        <header class="article-header">
            <h1 class="article-title"><?php $this->title(); ?></h1>
            <?php
            // Word count (approx): strip HTML and whitespace, then count UTF-8 characters.
            $wordCount = 0;
            $tokenWordCount = 0;
            $charCount = 0;
            $readMinutes = 1;
            $wordCountTip = '';
            try {
                $contentHtml = (string) ($this->content ?? '');
                $plain = html_entity_decode(strip_tags($contentHtml), ENT_QUOTES, 'UTF-8');
                $plainNoSpace = (string) preg_replace('/\\s+/u', '', $plain);
                $charCount = function_exists('mb_strlen') ? mb_strlen($plainNoSpace, 'UTF-8') : strlen($plainNoSpace);
                $wordCount = (int) $charCount;

                $wordMatches = [];
                $matched = preg_match_all('/[\x{4E00}-\x{9FFF}]|[A-Za-z0-9]+(?:[\'’\\-][A-Za-z0-9]+)*/u', $plain, $wordMatches);
                $tokenWordCount = $matched === false ? 0 : (int) $matched;
                if ($tokenWordCount <= 0 && $charCount > 0) {
                    $tokenWordCount = (int) $charCount;
                }

                $readByWord = $tokenWordCount > 0 ? ((float) $tokenWordCount / 220.0) : 0.0;
                $readByChar = $charCount > 0 ? ((float) $charCount / 300.0) : 0.0;
                $readMinutes = max(1, (int) ceil(max($readByWord, $readByChar)));
                $wordCountTip = sprintf(
                    _t('%1$d个词 %2$d个字符 阅读预计 %3$d分钟'),
                    (int) $tokenWordCount,
                    (int) $charCount,
                    (int) $readMinutes
                );
            } catch (\Throwable $e) {
                $wordCount = 0;
                $tokenWordCount = 0;
                $charCount = 0;
                $readMinutes = 1;
                $wordCountTip = sprintf(_t('%1$d个词 %2$d个字符 阅读预计 %3$d分钟'), 0, 0, 1);
            }

            $tags = [];
            try {
                $tags = is_array($this->tags) ? $this->tags : [];
            } catch (\Throwable $e) {
                $tags = [];
            }

            // Only show the immediate subcategory under the "posts" or "notes" root categories.
            $childCategory = null;
            $postCategories = [];
            try {
                $postCategories = is_array($this->categories) ? $this->categories : [];
            } catch (\Throwable $e) {
                $postCategories = [];
            }

            $postsRootMid = 0;
            $notesRootMid = 0;
            $categoryByMid = [];
            try {
                $this->widget('Widget_Metas_Category_List')->to($categoryList);
                if ($categoryList && $categoryList->have()) {
                    while ($categoryList->next()) {
                        $mid = (int) ($categoryList->mid ?? 0);
                        if ($mid <= 0) {
                            continue;
                        }
                        $parent = (int) ($categoryList->parent ?? 0);
                        $slug = (string) ($categoryList->slug ?? '');
                        $categoryByMid[$mid] = [
                            'mid' => $mid,
                            'parent' => $parent,
                            'slug' => $slug,
                            'name' => (string) ($categoryList->name ?? ''),
                            'url' => (string) ($categoryList->permalink ?? ''),
                        ];

                        if ($slug === 'posts') {
                            $postsRootMid = $mid;
                        } elseif ($slug === 'notes') {
                            $notesRootMid = $mid;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $postsRootMid = 0;
                $notesRootMid = 0;
                $categoryByMid = [];
            }

            if (!empty($postCategories) && !empty($categoryByMid) && ($postsRootMid || $notesRootMid)) {
                foreach ($postCategories as $cat) {
                    $mid = (int) ($cat['mid'] ?? 0);
                    if ($mid <= 0 || !isset($categoryByMid[$mid])) {
                        continue;
                    }
                    $info = $categoryByMid[$mid];
                    $parent = (int) ($info['parent'] ?? 0);
                    if (
                        ($postsRootMid && $parent === $postsRootMid) ||
                        ($notesRootMid && $parent === $notesRootMid)
                    ) {
                        if ($info['name'] !== '' && $info['url'] !== '') {
                            $childCategory = $info;
                            break;
                        }
                    }
                }
            }

            $guessPosts = [];
            $normalizeKey = static function ($value): string {
                $text = trim((string) $value);
                if ($text === '') {
                    return '';
                }

                if (function_exists('mb_strtolower')) {
                    return mb_strtolower($text, 'UTF-8');
                }

                return strtolower($text);
            };

            $currentCategoryKeys = [];
            $currentRootCategoryKeys = [];
            foreach ($postCategories as $cat) {
                $mid = (int) ($cat['mid'] ?? 0);
                $slug = (string) ($cat['slug'] ?? '');
                if ($mid > 0 && isset($categoryByMid[$mid])) {
                    $mappedSlug = trim((string) ($categoryByMid[$mid]['slug'] ?? ''));
                    if ($mappedSlug !== '') {
                        $slug = $mappedSlug;
                    }
                }

                $slugKey = $normalizeKey($slug);
                $isRootCategory = (
                    ($postsRootMid > 0 && $mid === $postsRootMid) ||
                    ($notesRootMid > 0 && $mid === $notesRootMid) ||
                    $slugKey === 'posts' ||
                    $slugKey === 'notes'
                );
                if ($isRootCategory) {
                    if ($mid > 0) {
                        $currentRootCategoryKeys['mid:' . $mid] = true;
                    }
                    if ($slugKey !== '') {
                        $currentRootCategoryKeys['slug:' . $slugKey] = true;
                    }
                    continue;
                }

                if ($mid > 0) {
                    $currentCategoryKeys['mid:' . $mid] = true;
                }
                if ($slugKey !== '') {
                    $currentCategoryKeys['slug:' . $slugKey] = true;
                }
            }

            $guessUseRootCategoryFallback = false;
            if (empty($currentCategoryKeys) && !empty($currentRootCategoryKeys)) {
                $currentCategoryKeys = $currentRootCategoryKeys;
                $guessUseRootCategoryFallback = true;
            }

            $currentTagKeys = [];
            foreach ($tags as $tag) {
                $mid = (int) ($tag['mid'] ?? 0);
                $slugKey = $normalizeKey((string) ($tag['slug'] ?? ''));
                $nameKey = $normalizeKey((string) ($tag['name'] ?? ''));

                if ($mid > 0) {
                    $currentTagKeys['mid:' . $mid] = true;
                }
                if ($slugKey !== '') {
                    $currentTagKeys['slug:' . $slugKey] = true;
                }
                if ($nameKey !== '') {
                    $currentTagKeys['name:' . $nameKey] = true;
                }
            }

            if (!empty($currentCategoryKeys) && !empty($currentTagKeys)) {
                $guessSource = null;
                try {
                    $this->widget('Widget_Contents_Post_Recent@post_guess', 'pageSize=9999', null, false)->to($guessSource);
                } catch (\Throwable $e) {
                    $guessSource = null;
                }

                if ($guessSource && $guessSource->have()) {
                    $currentCid = (int) ($this->cid ?? 0);

                    while ($guessSource->next()) {
                        if (count($guessPosts) >= 3) {
                            break;
                        }

                        $guessCid = (int) ($guessSource->cid ?? 0);
                        if ($guessCid <= 0 || ($currentCid > 0 && $guessCid === $currentCid)) {
                            continue;
                        }

                        $guessCategories = [];
                        try {
                            $guessCategories = is_array($guessSource->categories) ? $guessSource->categories : [];
                        } catch (\Throwable $e) {
                            $guessCategories = [];
                        }

                        $guessCategoryMatched = false;
                        foreach ($guessCategories as $cat) {
                            $catMid = (int) ($cat['mid'] ?? 0);
                            $catSlug = (string) ($cat['slug'] ?? '');
                            if ($catMid > 0 && isset($categoryByMid[$catMid])) {
                                $mappedSlug = trim((string) ($categoryByMid[$catMid]['slug'] ?? ''));
                                if ($mappedSlug !== '') {
                                    $catSlug = $mappedSlug;
                                }
                            }

                            $catSlugKey = $normalizeKey($catSlug);
                            $catIsRoot = (
                                ($postsRootMid > 0 && $catMid === $postsRootMid) ||
                                ($notesRootMid > 0 && $catMid === $notesRootMid) ||
                                $catSlugKey === 'posts' ||
                                $catSlugKey === 'notes'
                            );
                            if ($catIsRoot && !$guessUseRootCategoryFallback) {
                                continue;
                            }

                            if (
                                ($catMid > 0 && isset($currentCategoryKeys['mid:' . $catMid])) ||
                                ($catSlugKey !== '' && isset($currentCategoryKeys['slug:' . $catSlugKey]))
                            ) {
                                $guessCategoryMatched = true;
                                break;
                            }
                        }

                        if (!$guessCategoryMatched) {
                            continue;
                        }

                        $guessTags = [];
                        try {
                            $guessTags = is_array($guessSource->tags) ? $guessSource->tags : [];
                        } catch (\Throwable $e) {
                            $guessTags = [];
                        }

                        $guessTagMatched = false;
                        foreach ($guessTags as $tag) {
                            $tagMid = (int) ($tag['mid'] ?? 0);
                            $tagSlugKey = $normalizeKey((string) ($tag['slug'] ?? ''));
                            $tagNameKey = $normalizeKey((string) ($tag['name'] ?? ''));
                            if (
                                ($tagMid > 0 && isset($currentTagKeys['mid:' . $tagMid])) ||
                                ($tagSlugKey !== '' && isset($currentTagKeys['slug:' . $tagSlugKey])) ||
                                ($tagNameKey !== '' && isset($currentTagKeys['name:' . $tagNameKey]))
                            ) {
                                $guessTagMatched = true;
                                break;
                            }
                        }

                        if (!$guessTagMatched) {
                            continue;
                        }

                        $guessTitle = trim((string) ($guessSource->title ?? ''));
                        if ($guessTitle === '') {
                            $guessTitle = _t('无标题');
                        }

                        $guessUrl = trim((string) ($guessSource->permalink ?? ''));
                        if ($guessUrl === '') {
                            continue;
                        }

                        $guessExcerpt = '';
                        ob_start();
                        try {
                            $guessSource->excerpt(100, '...');
                        } catch (\Throwable $e) {
                            // Ignore.
                        }
                        $guessExcerpt = (string) ob_get_clean();
                        $guessExcerpt = trim((string) preg_replace('/\\s+/u', ' ', $guessExcerpt));

                        $guessRenderTags = [];
                        $guessTagSeen = [];
                        foreach ($guessTags as $tag) {
                            if (count($guessRenderTags) >= 3) {
                                break;
                            }

                            $tagName = trim((string) ($tag['name'] ?? ''));
                            $tagUrl = trim((string) ($tag['permalink'] ?? ''));
                            if ($tagName === '' || $tagUrl === '') {
                                continue;
                            }

                            $tagKey = $normalizeKey((string) ($tag['slug'] ?? ''));
                            if ($tagKey === '') {
                                $tagKey = $normalizeKey($tagName);
                            }
                            if ($tagKey !== '' && isset($guessTagSeen[$tagKey])) {
                                continue;
                            }

                            if ($tagKey !== '') {
                                $guessTagSeen[$tagKey] = true;
                            }
                            $guessRenderTags[] = [
                                'name' => $tagName,
                                'url' => $tagUrl,
                            ];
                        }

                        $guessCreated = (int) ($guessSource->created ?? 0);
                        $guessModified = (int) ($guessSource->modified ?? 0);
                        $guessPosts[] = [
                            'title' => $guessTitle,
                            'url' => $guessUrl,
                            'created' => $guessCreated,
                            'modified' => $guessModified,
                            'dateTime' => $guessCreated > 0 ? date('c', $guessCreated) : '',
                            'dateLabel' => $guessCreated > 0 ? date('Y/m/d-H:i:s', $guessCreated) : '',
                            'excerpt' => $guessExcerpt,
                            'tags' => $guessRenderTags,
                            'originalIndex' => count($guessPosts),
                        ];
                    }
                }
            }
            ?>
            <p class="article-meta">
                <span class="article-meta-item">
                    <span class="article-meta-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                    </span>
                    <time datetime="<?php $this->date('c'); ?>"><?php $this->date('Y-m-d'); ?></time>
                </span>
                <?php if (!empty($childCategory)): ?>
                    <span class="article-meta-item article-meta-category">
                        <span class="article-meta-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83z"/><path d="M2 12a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 12"/><path d="M2 17a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 17"/></svg>
                        </span>
                        <a class="article-category" href="<?php echo escape((string) $childCategory['url']); ?>"><?php echo escape((string) $childCategory['name']); ?></a>
                    </span>
                <?php endif; ?>
                <span class="article-meta-item article-meta-tags">
                    <span class="article-meta-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="9" y2="9"/><line x1="4" x2="20" y1="15" y2="15"/><line x1="10" x2="8" y1="3" y2="21"/><line x1="16" x2="14" y1="3" y2="21"/></svg>
                    </span>
                    <?php if (!empty($tags)): ?>
                        <span class="article-meta-tags-list">
                            <?php $tagIndex = 0; ?>
                            <?php foreach ($tags as $tag): ?>
                                <?php
                                $name = (string) ($tag['name'] ?? '');
                                $url = (string) ($tag['permalink'] ?? '');
                                if ($name === '' || $url === '') {
                                    continue;
                                }
                                if ($tagIndex > 0) {
                                    echo '<span class="article-tag-sep" aria-hidden="true">; </span>';
                                }
                                $tagIndex += 1;
                                ?>
                                <a class="article-tag" href="<?php echo escape($url); ?>"><?php echo escape($name); ?></a>
                            <?php endforeach; ?>
                        </span>
                    <?php else: ?>
                        <span class="article-meta-empty"><?php _e('无标签'); ?></span>
                    <?php endif; ?>
                </span>
                <span
                    class="article-meta-item article-meta-wordcount"
                    data-meta-tip="<?php echo escape($wordCountTip); ?>"
                    title="<?php echo escape($wordCountTip); ?>"
                    tabindex="0"
                    aria-label="<?php echo escape($wordCountTip); ?>"
                >
                    <span class="article-meta-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 4v16"/><path d="M17 4v16"/><path d="M19 4H9.5a4.5 4.5 0 0 0 0 9H13"/></svg>
                    </span>
                    <span class="article-meta-text"><?php echo (int) $wordCount; ?> <?php _e('字'); ?></span>
                </span>
            </p>
        </header>
        <div class="article-layout" data-article-layout>
            <div class="article-content">
                <?php echoArchiveContent($this); ?>
            </div>
            <aside class="article-aside" aria-label="<?php _e('目录'); ?>">
                <div class="article-toc">
                    <div class="article-toc-header">
                        <h2 class="article-toc-title"><?php _e('目录'); ?></h2>
                        <button class="article-toc-close" type="button" aria-label="<?php _e('关闭目录'); ?>" title="<?php _e('关闭目录'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                    <nav class="article-toc-nav" aria-label="<?php _e('文章目录'); ?>"></nav>
                </div>
            </aside>
        </div>

        <script>
            (function () {
                var layout = document.querySelector("[data-article-layout]");
                if (!layout) {
                    return;
                }

                var content = layout.querySelector(".article-content");
                var tocNav = layout.querySelector(".article-toc-nav");
                if (!content || !tocNav) {
                    return;
                }

                var headings = Array.prototype.slice
                    .call(content.querySelectorAll("h1, h2, h3, h4, h5, h6"))
                    .filter(function (node) {
                        return ((node && node.textContent) || "").trim().length > 0;
                    });

                if (headings.length === 0) {
                    return;
                }

                function headingLevel(node) {
                    var tag = (node && node.tagName) ? String(node.tagName).toUpperCase() : "";
                    var n = parseInt(tag.replace("H", ""), 10);
                    return Number.isFinite(n) ? n : 2;
                }

                var levels = headings.map(headingLevel);
                var minLevel = Math.min.apply(null, levels);
                if (!Number.isFinite(minLevel)) {
                    minLevel = 2;
                }

                function slugify(text) {
                    var s = String(text || "").trim().toLowerCase();
                    // Replace whitespace & common punctuation with hyphens for readable, non-English ids.
                    s = s.replace(/[\s\u3000]+/g, "-");
                    s = s.replace(/[·•・。．，,、:：;；!！?？()（）【】\[\]{}“”‘’'"\/\\|]+/g, "-");
                    s = s.replace(/-+/g, "-");
                    // Keep ascii letters/digits, hyphen/underscore, and CJK Unified Ideographs.
                    s = s.replace(/[^0-9a-z_\-\u4e00-\u9fff]+/g, "");
                    s = s.replace(/-+/g, "-").replace(/^-+|-+$/g, "");
                    return s;
                }

                var used = Object.create(null);
                function isIdTaken(id, node) {
                    var existing = document.getElementById(id);
                    return !!(existing && existing !== node);
                }
                function makeUniqueId(base, node) {
                    var id = base;
                    var i = 2;
                    while (!id || used[id] || isIdTaken(id, node)) {
                        id = base + "-" + i;
                        i += 1;
                    }
                    used[id] = true;
                    return id;
                }

                headings.forEach(function (h, idx) {
                    var raw = (h.getAttribute("id") || "").trim();
                    var base = slugify(h.textContent || "") || ("标题-" + (idx + 1));
                    var unique = makeUniqueId(base, h);
                    if (unique !== raw) {
                        h.setAttribute("id", unique);
                    }
                });

                var rootList = document.createElement("ol");
                rootList.className = "article-toc-list";

                var stack = [{ level: 1, list: rootList }];

                function currentFrame() {
                    return stack[stack.length - 1];
                }

                headings.forEach(function (h) {
                    var rawLevel = headingLevel(h);
                    var level = rawLevel - minLevel + 1;
                    if (level < 1) {
                        level = 1;
                    }

                    var cur = currentFrame();
                    if (level > cur.level + 1) {
                        level = cur.level + 1;
                    }

                    while (level > currentFrame().level) {
                        var parentList = currentFrame().list;
                        var parentLi = parentList.lastElementChild;
                        if (!parentLi) {
                            break;
                        }
                        var sub = document.createElement("ol");
                        sub.className = "article-toc-sublist";
                        parentLi.appendChild(sub);
                        stack.push({ level: currentFrame().level + 1, list: sub });
                    }

                    while (level < currentFrame().level) {
                        stack.pop();
                    }

                    var li = document.createElement("li");
                    var a = document.createElement("a");
                    li.className = "article-toc-item";
                    a.className = "article-toc-link";
                    a.href = "#" + h.id;
                    a.textContent = (h.textContent || "").trim();
                    li.appendChild(a);
                    currentFrame().list.appendChild(li);
                });

                tocNav.textContent = "";
                tocNav.appendChild(rootList);
                layout.classList.add("is-has-toc");

                // Scrollspy: only bold the current section in TOC while scrolling.
                var tocLinks = Array.prototype.slice.call(tocNav.querySelectorAll("a.article-toc-link"));
                var tocLinkById = Object.create(null);
                tocLinks.forEach(function (a) {
                    var href = (a.getAttribute("href") || "").trim();
                    if (href.charAt(0) !== "#") {
                        return;
                    }
                    var id = href.slice(1);
                    if (!id) {
                        return;
                    }
                    tocLinkById[id] = a;
                });

                var activeLink = null;
                function setActiveTocId(id) {
                    var next = id ? tocLinkById[id] : null;
                    if (!next || next === activeLink) {
                        return;
                    }

                    if (activeLink) {
                        activeLink.classList.remove("is-active");
                        activeLink.removeAttribute("aria-current");
                    }

                    activeLink = next;
                    activeLink.classList.add("is-active");
                    activeLink.setAttribute("aria-current", "location");
                }

                function computeActiveHeadingId() {
                    var boundary = 24; // px from top of viewport
                    var activeId = headings[0] ? headings[0].id : "";

                    for (var i = 0; i < headings.length; i += 1) {
                        var h = headings[i];
                        if (!h || !h.id) {
                            continue;
                        }
                        var rect = h.getBoundingClientRect();
                        if (rect.top <= boundary) {
                            activeId = h.id;
                            continue;
                        }
                        break;
                    }

                    return activeId;
                }

                var rafPending = false;
                function updateTocActive() {
                    rafPending = false;
                    setActiveTocId(computeActiveHeadingId());
                }

                function requestUpdateTocActive() {
                    if (rafPending) {
                        return;
                    }
                    rafPending = true;
                    window.requestAnimationFrame(updateTocActive);
                }

                window.addEventListener("scroll", requestUpdateTocActive, { passive: true });
                window.addEventListener("resize", requestUpdateTocActive);
                tocNav.addEventListener("click", function (e) {
                    var target = e.target;
                    if (!target || !target.matches) {
                        return;
                    }
                    if (!target.matches("a.article-toc-link")) {
                        return;
                    }
                    var href = (target.getAttribute("href") || "").trim();
                    if (href.charAt(0) !== "#") {
                        return;
                    }
                    setActiveTocId(href.slice(1));
                });

                requestUpdateTocActive();
                window.setTimeout(requestUpdateTocActive, 0);
                window.setTimeout(requestUpdateTocActive, 300);
            })();
        </script>

        <?php if (!empty($guessPosts)): ?>
            <section class="article-guess" aria-label="<?php _e('猜你想看'); ?>">
                <hr class="article-guess-divider" aria-hidden="true">
                <div class="article-guess-inner">
                    <h2 class="article-guess-title">
                        <span class="article-guess-title-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-route-icon lucide-route"><circle cx="6" cy="19" r="3"/><path d="M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15"/><circle cx="18" cy="5" r="3"/></svg>
                        </span>
                        <span class="article-guess-title-text"><?php _e('猜你想看'); ?></span>
                    </h2>
                    <ul class="article-guess-list" aria-label="<?php _e('猜你喜欢文章'); ?>">
                        <?php foreach ($guessPosts as $guess): ?>
                            <?php
                            $guessTitle = trim((string) ($guess['title'] ?? ''));
                            if ($guessTitle === '') {
                                $guessTitle = _t('无标题');
                            }
                            $guessUrl = trim((string) ($guess['url'] ?? ''));
                            if ($guessUrl === '') {
                                continue;
                            }
                            $guessCreated = (int) ($guess['created'] ?? 0);
                            $guessModified = (int) ($guess['modified'] ?? 0);
                            $guessDateTime = trim((string) ($guess['dateTime'] ?? ''));
                            $guessDateLabel = trim((string) ($guess['dateLabel'] ?? ''));
                            $guessExcerpt = trim((string) ($guess['excerpt'] ?? ''));
                            $guessTags = is_array($guess['tags'] ?? null) ? $guess['tags'] : [];
                            $guessOriginalIndex = (int) ($guess['originalIndex'] ?? 0);
                            ?>
                            <li class="posts-item"
                                data-post-created="<?php echo (int) $guessCreated; ?>"
                                data-post-modified="<?php echo (int) $guessModified; ?>"
                                data-post-excerpt="<?php echo escape($guessExcerpt); ?>"
                                data-post-original-index="<?php echo (int) $guessOriginalIndex; ?>">
                                <div class="posts-item-left">
                                    <a class="posts-title" href="<?php echo escape($guessUrl); ?>">
                                        <?php echo escape($guessTitle); ?>
                                    </a>
                                    <time class="posts-date"<?php if ($guessDateTime !== ''): ?> datetime="<?php echo escape($guessDateTime); ?>"<?php endif; ?>>
                                        <?php echo escape($guessDateLabel); ?>
                                    </time>
                                </div>

                                <div class="posts-item-right" aria-label="<?php _e('标签'); ?>">
                                    <?php foreach ($guessTags as $tag): ?>
                                        <?php
                                        $tagName = trim((string) ($tag['name'] ?? ''));
                                        $tagUrl = trim((string) ($tag['url'] ?? ''));
                                        if ($tagName === '' || $tagUrl === '') {
                                            continue;
                                        }
                                        ?>
                                        <a class="posts-tag" href="<?php echo escape($tagUrl); ?>">#<?php echo escape($tagName); ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </section>
        <?php endif; ?>

        <?php $this->need('comments.php'); ?>
    </article>
</main>

<?php $this->need('footer.php'); ?>
