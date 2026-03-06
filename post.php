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
            $currentCid = (int) ($this->cid ?? 0);
            $guessCachePayload = hansjackLoadPostGuessCache($currentCid);
            $guessCacheUpdated = (int) ($guessCachePayload['updated'] ?? 0);
            $guessPosts = hansjackNormalizePostGuessItems($guessCachePayload['items'] ?? []);
            $guessCacheFresh = (
                $guessCacheUpdated > 0 &&
                (time() - $guessCacheUpdated) <= hansjackPostGuessCacheTtl()
            );
            $guessCanRefreshByAdmin = currentUserIsAdmin() && !hansjackHighLoadDegradeEnabled($this->options);
            $guessRefreshRequested = false;
            if ($guessCanRefreshByAdmin) {
                try {
                    $guessRefreshRequested = trim((string) $this->request->get('refresh_guess_cache', '')) === '1';
                } catch (\Throwable $e) {
                    $guessRefreshRequested = false;
                }
            }
            $guessShouldRebuild = $guessCanRefreshByAdmin && (!$guessCacheFresh || empty($guessPosts) || $guessRefreshRequested);
            $guessRebuilt = false;
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

            if ($guessShouldRebuild && !empty($currentCategoryKeys) && !empty($currentTagKeys)) {
                $guessSource = null;
                try {
                    $this->widget('Widget_Contents_Post_Recent@post_guess', 'pageSize=400', null, false)->to($guessSource);
                } catch (\Throwable $e) {
                    $guessSource = null;
                }

                if ($guessSource) {
                    $guessBuiltPosts = [];
                    $guessRebuilt = true;

                    if ($guessSource->have()) {
                        while ($guessSource->next()) {
                            if (count($guessBuiltPosts) >= 3) {
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
                            $guessBuiltPosts[] = [
                                'title' => $guessTitle,
                                'url' => $guessUrl,
                                'created' => $guessCreated,
                                'modified' => $guessModified,
                                'dateTime' => $guessCreated > 0 ? date('c', $guessCreated) : '',
                                'dateLabel' => $guessCreated > 0 ? date('Y/m/d-H:i:s', $guessCreated) : '',
                                'excerpt' => $guessExcerpt,
                                'tags' => $guessRenderTags,
                                'originalIndex' => count($guessBuiltPosts),
                            ];
                        }
                    }

                    $guessPosts = $guessBuiltPosts;
                }
            }
            if ($guessRebuilt && $currentCid > 0) {
                hansjackSavePostGuessCache($currentCid, $guessPosts);
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
            <?php
            $seriesItems = [];
            try {
                $seriesItems = hansjackSeriesItemsByCid((int) ($this->cid ?? 0));
            } catch (\Throwable $e) {
                $seriesItems = [];
            }
            $seriesVisible = count($seriesItems) >= 2;
            $seriesItemsJson = json_encode(
                $seriesItems,
                JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_HEX_TAG
                    | JSON_HEX_AMP
                    | JSON_HEX_APOS
                    | JSON_HEX_QUOT
            );
            if (!is_string($seriesItemsJson) || $seriesItemsJson === '') {
                $seriesItemsJson = '[]';
            }
            ?>
        </header>
        <div class="article-layout" data-article-layout>
            <div class="article-content">
                <?php echoArchiveContent($this); ?>
            </div>
            <aside class="article-aside" aria-label="<?php _e('右侧栏'); ?>">
                <div class="article-toc">
                    <div class="article-toc-header">
                        <h2 class="article-toc-title"><?php _e('目录'); ?></h2>
                        <button class="article-toc-close" type="button" aria-label="<?php _e('关闭目录'); ?>" title="<?php _e('关闭目录'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                    <nav class="article-toc-nav" aria-label="<?php _e('文章目录'); ?>"></nav>
                </div>
                <?php if ($seriesVisible): ?>
                <section
                    class="article-series"
                    aria-label="<?php _e('系列'); ?>"
                    data-article-series
                    data-series-cid="<?php echo (int) ($this->cid ?? 0); ?>"
                    data-series-order="desc"
                    data-series-endpoint="<?php echo escape($this->permalink); ?>"
                >
                    <div class="article-series-header">
                        <h2 class="article-series-title"><?php _e('系列'); ?></h2>
                        <div class="article-series-actions" aria-label="<?php _e('操作'); ?>">
                            <button class="article-series-btn article-series-refresh-btn" type="button" aria-label="<?php _e('刷新系列'); ?>" title="<?php _e('刷新系列'); ?>" data-series-refresh>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw-icon lucide-refresh-cw" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                            </button>
                            <button class="article-series-btn article-series-sort-btn" type="button" aria-label="<?php _e('切换为时间升序'); ?>" title="<?php _e('切换为时间升序'); ?>" data-series-sort-toggle>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock-arrow-up-icon lucide-clock-arrow-up" aria-hidden="true"><path d="M12 6v6l1.56.78"/><path d="M13.227 21.925a10 10 0 1 1 8.767-9.588"/><path d="m14 18 4-4 4 4"/><path d="M18 22v-8"/></svg>
                            </button>
                        </div>
                    </div>
                    <ul class="article-series-list article-toc-list" aria-label="<?php _e('系列文章'); ?>" data-series-list>
                        <?php if (!empty($seriesItems)): ?>
                            <?php foreach ($seriesItems as $seriesItem): ?>
                                <?php
                                $seriesCid = (int) ($seriesItem['cid'] ?? 0);
                                $seriesTitle = trim((string) ($seriesItem['title'] ?? ''));
                                if ($seriesTitle === '') {
                                    $seriesTitle = _t('无标题');
                                }
                                $seriesUrl = trim((string) ($seriesItem['url'] ?? ''));
                                if ($seriesUrl === '') {
                                    continue;
                                }
                                $seriesCreated = (int) ($seriesItem['created'] ?? 0);
                                $seriesDateTime = trim((string) ($seriesItem['dateTime'] ?? ''));
                                $seriesDateLabel = trim((string) ($seriesItem['dateLabel'] ?? ''));
                                $seriesCurrent = !empty($seriesItem['isCurrent']);
                                ?>
                                <li class="article-series-item article-toc-item" data-series-created="<?php echo (int) $seriesCreated; ?>" data-series-cid="<?php echo (int) $seriesCid; ?>">
                                    <a class="article-series-link article-toc-link<?php echo $seriesCurrent ? ' is-active' : ''; ?>" href="<?php echo escape($seriesUrl); ?>" title="<?php echo escape($seriesTitle); ?>"<?php if ($seriesCurrent): ?> aria-current="location"<?php endif; ?>>
                                        <span class="article-series-link-title"><?php echo escape($seriesTitle); ?></span>
                                        <time class="article-series-date"<?php if ($seriesDateTime !== ''): ?> datetime="<?php echo escape($seriesDateTime); ?>"<?php endif; ?>><?php echo escape($seriesDateLabel); ?></time>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="article-series-empty"><?php _e('暂无系列文章'); ?></li>
                        <?php endif; ?>
                    </ul>
                </section>
                <?php endif; ?>
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

                var tocItems = [];
                var lastLevel = 1;
                headings.forEach(function (h) {
                    var rawLevel = headingLevel(h);
                    var level = rawLevel - minLevel + 1;
                    if (level < 1) {
                        level = 1;
                    }
                    if (level > lastLevel + 1) {
                        level = lastLevel + 1;
                    }
                    lastLevel = level;
                    tocItems.push({
                        heading: h,
                        level: level,
                        depth: level - 1
                    });
                });

                var depths = tocItems.map(function (item) {
                    return item.depth;
                });

                function hasMoreForColumn(index, columnDepth) {
                    for (var i = index + 1; i < depths.length; i += 1) {
                        // Keep ancestor stem only when a later item exists on the same ancestor depth.
                        if (depths[i] === columnDepth) {
                            return true;
                        }
                        if (depths[i] < columnDepth) {
                            return false;
                        }
                    }
                    return false;
                }

                function hasNextSibling(index, depth) {
                    for (var i = index + 1; i < depths.length; i += 1) {
                        if (depths[i] < depth) {
                            return false;
                        }
                        if (depths[i] === depth) {
                            return true;
                        }
                    }
                    return false;
                }

                var rootList = document.createElement("ul");
                rootList.className = "article-toc-list";

                tocItems.forEach(function (item, index) {
                    var depth = item.depth;
                    var title = (item.heading.textContent || "").trim();

                    var li = document.createElement("li");
                    li.className = "article-toc-item";
                    li.setAttribute("data-toc-depth", String(depth));

                    var indicator = document.createElement("span");
                    indicator.className = "article-toc-indicator";
                    indicator.setAttribute("aria-hidden", "true");

                    for (var col = 0; col <= depth; col += 1) {
                        var marker = document.createElement("span");
                        marker.className = "article-toc-indicator-col";

                        if (col < depth) {
                            if (hasMoreForColumn(index, col)) {
                                marker.classList.add("is-stem");
                            }
                        } else {
                            marker.classList.add("is-node");
                            marker.classList.add(hasNextSibling(index, depth) ? "is-continue" : "is-end");
                        }

                        indicator.appendChild(marker);
                    }

                    var a = document.createElement("a");
                    a.className = "article-toc-link";
                    a.href = "#" + item.heading.id;
                    a.title = title;
                    a.textContent = title;

                    li.appendChild(indicator);
                    li.appendChild(a);
                    rootList.appendChild(li);
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
        <script>
            (function () {
                var layout = document.querySelector("[data-article-layout]");
                if (!layout) {
                    return;
                }

                var seriesRoot = layout.querySelector("[data-article-series]");
                if (!seriesRoot) {
                    return;
                }

                var seriesList = seriesRoot.querySelector("[data-series-list]");
                if (!seriesList) {
                    return;
                }

                var refreshBtn = seriesRoot.querySelector("[data-series-refresh]");
                var sortBtn = seriesRoot.querySelector("[data-series-sort-toggle]");
                var tocNav = layout.querySelector(".article-toc-nav");
                var tocBlock = layout.querySelector(".article-toc");

                var currentCid = parseInt(seriesRoot.getAttribute("data-series-cid") || "0", 10);
                if (!Number.isFinite(currentCid)) {
                    currentCid = 0;
                }

                var initialItems = <?php echo $seriesItemsJson; ?>;
                if (!Array.isArray(initialItems)) {
                    initialItems = [];
                }

                var state = {
                    items: initialItems.slice(),
                    order: "desc",
                    loading: false
                };

                var orderRaw = (seriesRoot.getAttribute("data-series-order") || "").toLowerCase();
                if (orderRaw === "asc" || orderRaw === "desc") {
                    state.order = orderRaw;
                }

                var iconAsc = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock-arrow-up-icon lucide-clock-arrow-up" aria-hidden="true"><path d="M12 6v6l1.56.78"/><path d="M13.227 21.925a10 10 0 1 1 8.767-9.588"/><path d="m14 18 4-4 4 4"/><path d="M18 22v-8"/></svg>';
                var iconDesc = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock-arrow-down-icon lucide-clock-arrow-down" aria-hidden="true"><path d="M12 6v6l2 1"/><path d="M12.337 21.994a10 10 0 1 1 9.588-8.767"/><path d="m14 18 4 4 4-4"/><path d="M18 14v8"/></svg>';
                var iconRefresh = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw-icon lucide-refresh-cw" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>';

                function normalizeItems(items) {
                    if (!Array.isArray(items)) {
                        return [];
                    }

                    var result = [];
                    var seen = Object.create(null);
                    for (var i = 0; i < items.length; i += 1) {
                        var item = items[i];
                        if (!item || typeof item !== "object") {
                            continue;
                        }

                        var cid = parseInt(item.cid || "0", 10);
                        if (!Number.isFinite(cid) || cid <= 0) {
                            continue;
                        }
                        if (seen[cid]) {
                            continue;
                        }
                        seen[cid] = true;

                        var title = String(item.title || "").trim();
                        if (!title) {
                            title = "无标题";
                        }
                        var url = String(item.url || "").trim();
                        if (!url) {
                            continue;
                        }

                        var created = parseInt(item.created || "0", 10);
                        if (!Number.isFinite(created) || created < 0) {
                            created = 0;
                        }

                        var dateTime = String(item.dateTime || "").trim();
                        var dateLabel = String(item.dateLabel || "").trim();
                        var isCurrent = !!item.isCurrent || (currentCid > 0 && cid === currentCid);

                        result.push({
                            cid: cid,
                            title: title,
                            url: url,
                            created: created,
                            dateTime: dateTime,
                            dateLabel: dateLabel,
                            isCurrent: isCurrent
                        });
                    }

                    return result;
                }

                function sortedItems(items, order) {
                    var list = normalizeItems(items).slice();
                    list.sort(function (a, b) {
                        var av = parseInt(a.created || "0", 10);
                        var bv = parseInt(b.created || "0", 10);
                        if (av !== bv) {
                            return order === "asc" ? av - bv : bv - av;
                        }
                        return order === "asc" ? a.cid - b.cid : b.cid - a.cid;
                    });
                    return list;
                }

                function updateSortButton() {
                    if (!sortBtn) {
                        return;
                    }
                    var target = state.order === "desc" ? "asc" : "desc";
                    var label = target === "asc" ? "切换为时间升序" : "切换为时间降序";
                    sortBtn.setAttribute("aria-label", label);
                    sortBtn.setAttribute("title", label);
                    sortBtn.innerHTML = target === "asc" ? iconAsc : iconDesc;
                }

                function updateAsideVisibility() {
                    var hasToc = !!(tocNav && tocNav.querySelector(".article-toc-item"));
                    var seriesCount = seriesList ? seriesList.querySelectorAll(".article-series-item").length : 0;
                    var hasSeries = !seriesRoot.hidden && seriesCount >= 2;
                    if (tocBlock) {
                        tocBlock.hidden = !hasToc;
                        tocBlock.setAttribute("aria-hidden", hasToc ? "false" : "true");
                    }
                    if (hasToc || hasSeries) {
                        layout.classList.add("is-has-toc");
                    } else {
                        layout.classList.remove("is-has-toc");
                    }
                }

                function buildSeriesItemNode(item) {
                    var li = document.createElement("li");
                    li.className = "article-series-item article-toc-item";
                    li.setAttribute("data-series-created", String(item.created || 0));
                    li.setAttribute("data-series-cid", String(item.cid || 0));

                    var a = document.createElement("a");
                    a.className = "article-series-link article-toc-link";
                    if (item.isCurrent) {
                        a.classList.add("is-active");
                        a.setAttribute("aria-current", "location");
                    }
                    a.href = item.url;
                    a.title = item.title;

                    var titleSpan = document.createElement("span");
                    titleSpan.className = "article-series-link-title";
                    titleSpan.textContent = item.title;

                    var dateNode = document.createElement("time");
                    dateNode.className = "article-series-date";
                    if (item.dateTime) {
                        dateNode.setAttribute("datetime", item.dateTime);
                    }
                    dateNode.textContent = item.dateLabel || "";

                    a.appendChild(titleSpan);
                    a.appendChild(dateNode);
                    li.appendChild(a);
                    return li;
                }

                function emitSeriesRendered() {
                    var ev = null;
                    try {
                        ev = new CustomEvent("hansjack:series:rendered", {
                            detail: {
                                root: seriesRoot
                            }
                        });
                    } catch (e) {
                        ev = null;
                    }
                    if (!ev) {
                        try {
                            ev = document.createEvent("CustomEvent");
                            ev.initCustomEvent("hansjack:series:rendered", true, false, {
                                root: seriesRoot
                            });
                        } catch (e) {
                            ev = null;
                        }
                    }
                    if (!ev) {
                        return;
                    }
                    try {
                        window.dispatchEvent(ev);
                    } catch (e) {}
                }

                function renderSeriesList() {
                    var items = sortedItems(state.items, state.order);
                    seriesRoot.setAttribute("data-series-order", state.order);
                    seriesList.textContent = "";

                    if (items.length < 2) {
                        seriesRoot.hidden = true;
                        seriesRoot.setAttribute("aria-hidden", "true");
                        updateAsideVisibility();
                        emitSeriesRendered();
                        return;
                    }

                    var frag = document.createDocumentFragment();
                    for (var i = 0; i < items.length; i += 1) {
                        frag.appendChild(buildSeriesItemNode(items[i]));
                    }
                    seriesList.appendChild(frag);
                    seriesRoot.hidden = false;
                    seriesRoot.setAttribute("aria-hidden", "false");
                    updateAsideVisibility();
                    emitSeriesRendered();
                }

                function setLoading(on) {
                    state.loading = !!on;
                    if (!refreshBtn) {
                        return;
                    }
                    refreshBtn.disabled = state.loading;
                    refreshBtn.setAttribute("aria-busy", state.loading ? "true" : "false");
                    refreshBtn.innerHTML = iconRefresh;
                }

                function buildSeriesEndpoint(forceRefresh) {
                    var endpoint = String(seriesRoot.getAttribute("data-series-endpoint") || "").trim();
                    if (!endpoint) {
                        endpoint = window.location.href;
                    }
                    var url;
                    try {
                        url = new URL(endpoint, window.location.href);
                    } catch (e) {
                        return "";
                    }

                    url.hash = "";
                    url.searchParams.set("series_list", "1");
                    if (currentCid > 0) {
                        url.searchParams.set("cid", String(currentCid));
                    }
                    if (forceRefresh) {
                        url.searchParams.set("force", "1");
                    } else {
                        url.searchParams.delete("force");
                    }
                    url.searchParams.set("_ts", String(Date.now()));
                    return url.toString();
                }

                function refreshSeries() {
                    if (state.loading) {
                        return;
                    }
                    var endpoint = buildSeriesEndpoint(true);
                    if (!endpoint) {
                        return;
                    }

                    setLoading(true);
                    window.fetch(endpoint, {
                        method: "GET",
                        credentials: "same-origin",
                        cache: "no-store",
                        headers: {
                            "X-Requested-With": "XMLHttpRequest",
                            "Accept": "application/json"
                        }
                    }).then(function (res) {
                        if (!res || !res.ok) {
                            throw new Error("series request failed");
                        }
                        return res.json();
                    }).then(function (json) {
                        if (!json || json.ok !== true) {
                            throw new Error("series payload invalid");
                        }
                        state.items = normalizeItems(json.items || []);
                        renderSeriesList();
                    }).catch(function () {
                    }).finally(function () {
                        setLoading(false);
                    });
                }

                if (sortBtn) {
                    sortBtn.addEventListener("click", function (e) {
                        if (e && e.preventDefault) {
                            e.preventDefault();
                        }
                        state.order = state.order === "asc" ? "desc" : "asc";
                        updateSortButton();
                        renderSeriesList();
                    });
                }

                if (refreshBtn) {
                    refreshBtn.addEventListener("click", function (e) {
                        if (e && e.preventDefault) {
                            e.preventDefault();
                        }
                        refreshSeries();
                    });
                }

                state.items = normalizeItems(state.items);
                updateSortButton();
                renderSeriesList();
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
