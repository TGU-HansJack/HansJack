<?php
/**
 * 寒士杰克主题
 *
 * @package 寒士杰克
 * @author 寒士杰克
 * @version 0.1.0
 * @link https://example.com
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}


$this->need('header.php');
$themeConfig = buildThemeConfig($this->options);

$brandName = '';
$welcomeText = '';
$landingAvatarUrl = '';
$blogUrl = '';
$memoUrl = '';
$blogSlug = 'posts';
$memoSlug = 'notes';
$landingHeatmapDays = 140;
$landingHeatmapSeries = [];
$landingHeatmapColumns = 20;
$landingLatestContent = null;
$landingHitokotoEnabled = true;
if ($this->is('index')) {
    $brandName = trim((string) ($themeConfig['brandName'] ?? ''));
    if ($brandName === '') {
        ob_start();
        $this->options->title();
        $brandName = trim((string) ob_get_clean());
    }

    $welcomeText = trim((string) ($themeConfig['welcomeTitle'] ?? ''));
    if ($welcomeText === '') {
        $welcomeText = trim('欢迎来到 ' . $brandName);
    }

    $landingAvatarUrl = assetUrl($this->options, 'logo.avif');
    if ($landingAvatarUrl === '') {
        ob_start();
        $this->options->siteUrl('favicon.ico');
        $landingAvatarUrl = trim((string) ob_get_clean());
    }

    $blogUrl = (string) (($themeConfig['links']['blog'] ?? '') ?: '');
    $memoUrl = (string) (($themeConfig['links']['memo'] ?? '') ?: '');
    $landingHitokotoEnabled = (bool) ($themeConfig['landingHitokotoEnabled'] ?? true);

    $heatmapDayCount = max(1, (int) $landingHeatmapDays);
    $todayStartTs = strtotime(date('Y-m-d 00:00:00'));
    if ($todayStartTs === false) {
        $todayStartTs = time();
    }
    $heatmapStartTs = $todayStartTs - (($heatmapDayCount - 1) * 86400);
    $heatmapEndTs = $todayStartTs + 86399;

    for ($i = 0; $i < $heatmapDayCount; $i++) {
        $dayTs = $heatmapStartTs + ($i * 86400);
        $dayKey = date('Y-m-d', $dayTs);
        $landingHeatmapSeries[$dayKey] = [
            'dateLabel' => date('Y年n月j日', $dayTs),
            'notes' => [],
            'memos' => [],
            'others' => [],
            'total' => 0,
        ];
    }

    $landingCategoryByMid = [];
    $landingPostsRootMid = 0;
    $landingMemosRootMid = 0;
    try {
        $this->widget('Widget_Metas_Category_List@landing_categories')->to($landingCategories);
        if ($landingCategories && $landingCategories->have()) {
            while ($landingCategories->next()) {
                $mid = (int) ($landingCategories->mid ?? 0);
                if ($mid <= 0) {
                    continue;
                }
                $slug = (string) ($landingCategories->slug ?? '');
                $landingCategoryByMid[$mid] = [
                    'mid' => $mid,
                    'parent' => (int) ($landingCategories->parent ?? 0),
                    'slug' => $slug,
                ];
                if ($slug === $blogSlug || $slug === 'posts') {
                    $landingPostsRootMid = $mid;
                } elseif ($slug === $memoSlug || $slug === 'notes') {
                    $landingMemosRootMid = $mid;
                }
            }
        }
    } catch (\Throwable $e) {
        $landingCategoryByMid = [];
        $landingPostsRootMid = 0;
        $landingMemosRootMid = 0;
    }

    $landingPosts = null;
    try {
        $this->widget('Widget_Contents_Post_Recent@landing_posts', 'pageSize=9999', null, false)->to($landingPosts);
    } catch (\Throwable $e) {
        $landingPosts = null;
    }

    if ($landingPosts && $landingPosts->have()) {
        while ($landingPosts->next()) {
            $created = (int) ($landingPosts->created ?? 0);
            if ($created <= 0) {
                continue;
            }

            if ($landingLatestContent === null || $created > (int) ($landingLatestContent['created'] ?? 0)) {
                $latestTitle = trim((string) ($landingPosts->title ?? ''));
                if ($latestTitle === '') {
                    $latestTitle = _t('无标题');
                }

                $latestTags = [];
                $latestTagKeys = [];
                $postTags = [];
                try {
                    $postTags = is_array($landingPosts->tags) ? $landingPosts->tags : [];
                } catch (\Throwable $e) {
                    $postTags = [];
                }

                foreach ($postTags as $tag) {
                    if (count($latestTags) >= 3) {
                        break;
                    }
                    $tagName = trim((string) ($tag['name'] ?? ''));
                    $tagUrl = trim((string) ($tag['permalink'] ?? ''));
                    if ($tagName === '' || $tagUrl === '') {
                        continue;
                    }
                    $tagKey = function_exists('mb_strtolower')
                        ? mb_strtolower($tagName, 'UTF-8')
                        : strtolower($tagName);
                    if (isset($latestTagKeys[$tagKey])) {
                        continue;
                    }
                    $latestTagKeys[$tagKey] = true;
                    $latestTags[] = [
                        'name' => $tagName,
                        'url' => $tagUrl,
                    ];
                }

                if (count($latestTags) < 3) {
                    $postCategoriesForTags = [];
                    try {
                        $postCategoriesForTags = is_array($landingPosts->categories) ? $landingPosts->categories : [];
                    } catch (\Throwable $e) {
                        $postCategoriesForTags = [];
                    }

                    foreach ($postCategoriesForTags as $cat) {
                        if (count($latestTags) >= 3) {
                            break;
                        }

                        $catName = trim((string) ($cat['name'] ?? ''));
                        $catUrl = trim((string) ($cat['permalink'] ?? ''));
                        if ($catName === '' || $catUrl === '') {
                            continue;
                        }

                        $catMid = (int) ($cat['mid'] ?? 0);
                        $catSlug = trim((string) ($cat['slug'] ?? ''));
                        if ($catMid > 0 && isset($landingCategoryByMid[$catMid])) {
                            $catInfo = $landingCategoryByMid[$catMid];
                            $catSlug = trim((string) ($catInfo['slug'] ?? $catSlug));
                        }

                        $isRootCategory = (
                            ($landingPostsRootMid > 0 && $catMid === $landingPostsRootMid) ||
                            ($landingMemosRootMid > 0 && $catMid === $landingMemosRootMid) ||
                            $catSlug === $blogSlug ||
                            $catSlug === $memoSlug ||
                            $catSlug === 'posts' ||
                            $catSlug === 'notes'
                        );
                        if ($isRootCategory) {
                            continue;
                        }

                        $catKey = function_exists('mb_strtolower')
                            ? mb_strtolower($catName, 'UTF-8')
                            : strtolower($catName);
                        if (isset($latestTagKeys[$catKey])) {
                            continue;
                        }
                        $latestTagKeys[$catKey] = true;

                        $latestTags[] = [
                            'name' => $catName,
                            'url' => $catUrl,
                        ];
                    }
                }

                $landingLatestContent = [
                    'created' => $created,
                    'title' => $latestTitle,
                    'url' => (string) ($landingPosts->permalink ?? ''),
                    'datetime' => date('c', $created),
                    'timeLabel' => date('Y/m/d-H:i:s', $created),
                    'timeTitle' => date('Y年n月j日 H:i:s', $created),
                    'tags' => $latestTags,
                ];
            }

            if ($created < $heatmapStartTs || $created > $heatmapEndTs) {
                continue;
            }

            $dayKey = date('Y-m-d', $created);
            if (!isset($landingHeatmapSeries[$dayKey])) {
                continue;
            }

            $itemTitle = trim((string) ($landingPosts->title ?? ''));
            if ($itemTitle === '') {
                $itemTitle = _t('无标题');
            }
            $itemUrl = trim((string) ($landingPosts->permalink ?? ''));
            if ($itemUrl === '') {
                continue;
            }

            $bucket = 'others';
            $postCategories = [];
            try {
                $postCategories = is_array($landingPosts->categories) ? $landingPosts->categories : [];
            } catch (\Throwable $e) {
                $postCategories = [];
            }

            foreach ($postCategories as $cat) {
                $mid = (int) ($cat['mid'] ?? 0);
                if ($mid <= 0 || !isset($landingCategoryByMid[$mid])) {
                    continue;
                }

                $catInfo = $landingCategoryByMid[$mid];
                $catMid = (int) ($catInfo['mid'] ?? 0);
                $catParent = (int) ($catInfo['parent'] ?? 0);
                $catSlug = (string) ($catInfo['slug'] ?? '');

                if (
                    ($landingMemosRootMid > 0 && ($catMid === $landingMemosRootMid || $catParent === $landingMemosRootMid))
                    || $catSlug === $memoSlug
                    || $catSlug === 'notes'
                ) {
                    $bucket = 'memos';
                    break;
                }

                if (
                    ($landingPostsRootMid > 0 && ($catMid === $landingPostsRootMid || $catParent === $landingPostsRootMid))
                    || $catSlug === $blogSlug
                    || $catSlug === 'posts'
                ) {
                    $bucket = 'notes';
                }
            }

            $landingHeatmapSeries[$dayKey][$bucket][] = [
                'title' => $itemTitle,
                'url' => $itemUrl,
            ];
            $landingHeatmapSeries[$dayKey]['total'] += 1;
        }
    }

    $landingHeatmapSeries = array_values($landingHeatmapSeries);
    $landingHeatmapColumns = (int) ceil(max(1, count($landingHeatmapSeries)) / 7);
    if ($landingHeatmapColumns < 1) {
        $landingHeatmapColumns = 1;
    }
}
?>

<main class="main" role="main">
    <?php if ($this->is('index')): ?>
        <section class="landing" aria-label="<?php _e('欢迎'); ?>">
            <div class="landing-main">
                <div class="landing-left">
                    <div class="landing-terminal" role="region" aria-label="<?php _e('欢迎词'); ?>">
                        <div class="landing-prompt" aria-hidden="true">site@typecho:~$</div>
                        <div class="landing-typing" aria-label="<?php echo escape($welcomeText); ?>">
                            <span class="landing-typing-text" data-text="<?php echo escape($welcomeText); ?>"></span>
                            <span class="landing-cursor" aria-hidden="true"></span>
                        </div>
                    </div>
                </div>
                <div class="landing-right">
                    <div class="landing-avatar" aria-hidden="true">
                        <img
                            src="<?php echo escape($landingAvatarUrl); ?>"
                            alt=""
                            width="256"
                            height="256"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                        >
                    </div>
                </div>
            </div>

            <div class="landing-insights" role="region" aria-label="<?php _e('热力图与最新内容'); ?>">
                <footer class="landing-insights-footer" style="--landing-heatmap-cols: <?php echo (int) $landingHeatmapColumns; ?>;">
                    <section class="landing-heatmap-grid" aria-label="<?php echo escape(sprintf(_t('最近 %d 天内容热力图'), (int) $landingHeatmapDays)); ?>">
                        <?php foreach ($landingHeatmapSeries as $day): ?>
                            <?php
                            $dayTotal = (int) ($day['total'] ?? 0);
                            $dotClass = 'is-empty';
                            if ($dayTotal >= 3) {
                                $dotClass = 'is-level-3';
                            } elseif ($dayTotal === 2) {
                                $dotClass = 'is-level-2';
                            } elseif ($dayTotal === 1) {
                                $dotClass = 'is-level-1';
                            }

                            $dayNotes = is_array($day['notes'] ?? null) ? $day['notes'] : [];
                            $dayMemos = is_array($day['memos'] ?? null) ? $day['memos'] : [];
                            $dayOthers = is_array($day['others'] ?? null) ? $day['others'] : [];
                            $previewLimit = 3;
                            ?>
                            <figure class="landing-heatmap-item">
                                <i class="landing-heatmap-dot <?php echo escape($dotClass); ?>" aria-hidden="true"></i>
                                <figcaption class="landing-heatmap-pop">
                                    <time class="landing-heatmap-date"><?php echo escape((string) ($day['dateLabel'] ?? '')); ?></time>
                                    <?php if ($dayTotal <= 0): ?>
                                        <p class="landing-heatmap-empty"><?php _e('无字'); ?></p>
                                    <?php else: ?>
                                        <?php if (!empty($dayNotes)): ?>
                                            <p class="landing-heatmap-kind"><?php echo escape(sprintf(_t('博文 %d 篇：'), count($dayNotes))); ?></p>
                                            <ul class="landing-heatmap-list">
                                                <?php foreach (array_slice($dayNotes, 0, $previewLimit) as $item): ?>
                                                    <li><a class="landing-heatmap-link" href="<?php echo escape((string) ($item['url'] ?? '')); ?>"><?php echo escape((string) ($item['title'] ?? '')); ?></a></li>
                                                <?php endforeach; ?>
                                                <?php if (count($dayNotes) > $previewLimit): ?>
                                                    <li class="landing-heatmap-more"><?php echo escape(sprintf(_t('另有 %d 篇'), count($dayNotes) - $previewLimit)); ?></li>
                                                <?php endif; ?>
                                            </ul>
                                        <?php endif; ?>

                                        <?php if (!empty($dayMemos)): ?>
                                            <p class="landing-heatmap-kind"><?php echo escape(sprintf(_t('手记 %d 则：'), count($dayMemos))); ?></p>
                                            <ul class="landing-heatmap-list">
                                                <?php foreach (array_slice($dayMemos, 0, $previewLimit) as $item): ?>
                                                    <li><a class="landing-heatmap-link" href="<?php echo escape((string) ($item['url'] ?? '')); ?>"><?php echo escape((string) ($item['title'] ?? '')); ?></a></li>
                                                <?php endforeach; ?>
                                                <?php if (count($dayMemos) > $previewLimit): ?>
                                                    <li class="landing-heatmap-more"><?php echo escape(sprintf(_t('另有 %d 则'), count($dayMemos) - $previewLimit)); ?></li>
                                                <?php endif; ?>
                                            </ul>
                                        <?php endif; ?>

                                        <?php if (!empty($dayOthers)): ?>
                                            <p class="landing-heatmap-kind"><?php echo escape(sprintf(_t('内容 %d 条：'), count($dayOthers))); ?></p>
                                            <ul class="landing-heatmap-list">
                                                <?php foreach (array_slice($dayOthers, 0, $previewLimit) as $item): ?>
                                                    <li><a class="landing-heatmap-link" href="<?php echo escape((string) ($item['url'] ?? '')); ?>"><?php echo escape((string) ($item['title'] ?? '')); ?></a></li>
                                                <?php endforeach; ?>
                                                <?php if (count($dayOthers) > $previewLimit): ?>
                                                    <li class="landing-heatmap-more"><?php echo escape(sprintf(_t('另有 %d 条'), count($dayOthers) - $previewLimit)); ?></li>
                                                <?php endif; ?>
                                            </ul>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </section>

                    <blockquote class="landing-latest">
                        <h1><?php _e('最新内容'); ?></h1>
                        <?php if ($landingLatestContent !== null): ?>
                            <?php
                            $latestTitle = (string) ($landingLatestContent['title'] ?? '');
                            $latestUrl = trim((string) ($landingLatestContent['url'] ?? ''));
                            $latestTimeLabel = (string) ($landingLatestContent['timeLabel'] ?? '');
                            $latestTimeTitle = (string) ($landingLatestContent['timeTitle'] ?? '');
                            $latestDatetime = (string) ($landingLatestContent['datetime'] ?? '');
                            $latestTags = is_array($landingLatestContent['tags'] ?? null) ? $landingLatestContent['tags'] : [];
                            ?>
                            <div class="landing-latest-main">
                                <div class="landing-latest-head">
                                    <?php if ($latestUrl !== ''): ?>
                                        <a href="<?php echo escape($latestUrl); ?>" class="landing-latest-link"><?php echo escape($latestTitle); ?></a>
                                    <?php else: ?>
                                        <span class="landing-latest-link"><?php echo escape($latestTitle); ?></span>
                                    <?php endif; ?>

                                    <?php if (!empty($latestTags)): ?>
                                        <div class="landing-latest-tags">
                                            <?php foreach ($latestTags as $tag): ?>
                                                <?php
                                                $tagName = trim((string) ($tag['name'] ?? ''));
                                                $tagUrl = trim((string) ($tag['url'] ?? ''));
                                                if ($tagName === '' || $tagUrl === '') {
                                                    continue;
                                                }
                                                ?>
                                                <a href="<?php echo escape($tagUrl); ?>" class="landing-latest-tag">#<?php echo escape($tagName); ?></a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($latestTimeLabel !== ''): ?>
                                    <time datetime="<?php echo escape($latestDatetime); ?>" class="landing-latest-time" title="<?php echo escape($latestTimeTitle); ?>"><?php echo escape($latestTimeLabel); ?></time>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="landing-latest-empty"><?php _e('暂无内容'); ?></p>
                        <?php endif; ?>
                    </blockquote>
                </footer>
            </div>

            <div class="landing-bottom" role="group" aria-label="<?php _e('名言'); ?>">
                <?php if ($landingHitokotoEnabled): ?>
                    <p class="hitokoto-text" aria-live="polite"><?php _e('既然选择了远方，便只顾风雨兼程。'); ?></p>
                <?php endif; ?>
                <button class="scroll-down" type="button" aria-label="<?php _e('向下滚动'); ?>" title="<?php _e('向下滚动'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down-icon lucide-chevron-down" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </button>
            </div>
        </section>

        <?php if (false): ?>
        <section class="recent" id="recent" aria-label="<?php _e('最近内容'); ?>">
            <?php
            $recentBlog = null;
            $recentMemo = null;
            $recentComments = null;
            $activityPosts = null;
            $recentListLimit = 5;
            $activityPageSize = 9999;

            try {
                $this->widget(
                    'Widget_Archive@recent_posts',
                    'pageSize=' . $recentListLimit . '&type=category',
                    'slug=' . urlencode($blogSlug),
                    false
                )->to($recentBlog);
            } catch (\Throwable $e) {
                $recentBlog = null;
            }

            try {
                $this->widget(
                    'Widget_Archive@recent_memo',
                    'pageSize=' . $recentListLimit . '&type=category',
                    'slug=' . urlencode($memoSlug),
                    false
                )->to($recentMemo);
            } catch (\Throwable $e) {
                $recentMemo = null;
            }

            try {
                $this->widget('Widget_Comments_Recent', 'pageSize=' . $activityPageSize, null, false)->to($recentComments);
            } catch (\Throwable $e) {
                $recentComments = null;
            }

            try {
                $this->widget('Widget_Contents_Post_Recent@activity_posts', 'pageSize=' . $activityPageSize, null, false)->to($activityPosts);
            } catch (\Throwable $e) {
                $activityPosts = null;
            }

            $activities = [];
            $activityCutoff = strtotime('-2 months');
            if ($activityCutoff === false) {
                $activityCutoff = 0;
            }

            if ($activityPosts && $activityPosts->have()) {
                while ($activityPosts->next()) {
                    $activityCreated = (int) ($activityPosts->created ?? 0);
                    if ($activityCreated < $activityCutoff) {
                        continue;
                    }

                    $activityAuthor = '';
                    try {
                        $activityAuthor = trim((string) ($activityPosts->author->screenName ?? ''));
                    } catch (\Throwable $e) {
                        $activityAuthor = '';
                    }
                    if ($activityAuthor === '') {
                        $activityAuthor = $brandName;
                    }

                    $activities[] = [
                        'type' => 'publish',
                        'created' => $activityCreated,
                        'timeWord' => (string) ($activityPosts->dateWord ?? ''),
                        'author' => $activityAuthor,
                        'title' => (string) ($activityPosts->title ?? ''),
                        'url' => (string) ($activityPosts->permalink ?? ''),
                    ];
                }
            }

            if ($recentComments && $recentComments->have()) {
                while ($recentComments->next()) {
                    $activityCreated = (int) ($recentComments->created ?? 0);
                    if ($activityCreated < $activityCutoff) {
                        continue;
                    }

                    $activities[] = [
                        'type' => ((int) ($recentComments->parent ?? 0) > 0) ? 'reply' : 'comment',
                        'created' => $activityCreated,
                        'timeWord' => (string) ($recentComments->dateWord ?? ''),
                        'author' => (string) ($recentComments->author ?? ''),
                        'mail' => (string) ($recentComments->mail ?? ''),
                        'authorId' => (int) ($recentComments->authorId ?? 0),
                        'ownerId' => (int) ($recentComments->ownerId ?? 0),
                        'title' => (string) ($recentComments->title ?? ''),
                        'url' => (string) ($recentComments->permalink ?? ''),
                        'text' => (string) ($recentComments->text ?? ''),
                    ];
                }
            }

            usort($activities, function ($a, $b) {
                return ((int) ($b['created'] ?? 0)) <=> ((int) ($a['created'] ?? 0));
            });
            ?>

            <div class="recent-grid">
                <div class="recent-left">
                    <div class="recent-panel" aria-label="<?php _e('最近更新的文章'); ?>">
                        <h2 class="recent-title"><?php _e('最近更新的文章'); ?></h2>
                        <ul class="recent-list">
                            <?php if ($recentBlog && $recentBlog->have()): ?>
                                <?php $recentBlogCount = 0; ?>
                                <?php while ($recentBlog->next()): ?>
                                    <?php if ($recentBlogCount >= $recentListLimit) { break; } ?>
                                    <li class="recent-item">
                                        <a class="recent-link" href="<?php echo escape($recentBlog->permalink); ?>">
                                            <span class="recent-link-text"><?php echo escape($recentBlog->title); ?></span>
                                        </a>
                                        <span class="recent-time"><?php echo escape($recentBlog->dateWord); ?></span>
                                    </li>
                                    <?php $recentBlogCount++; ?>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li class="recent-empty"><?php _e('暂无内容'); ?></li>
                            <?php endif; ?>
                        </ul>
                        <?php if ($blogUrl !== ''): ?>
                            <a class="recent-more" href="<?php echo escape($blogUrl); ?>">
                                <?php _e('还有更多'); ?>
                                <span class="recent-more-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-arrow-right-icon lucide-circle-arrow-right"><circle cx="12" cy="12" r="10"/><path d="m12 16 4-4-4-4"/><path d="M8 12h8"/></svg>
                                </span>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="recent-panel" aria-label="<?php _e('最近更新的手记'); ?>">
                        <h2 class="recent-title"><?php _e('最近更新的手记'); ?></h2>
                        <ul class="recent-list">
                            <?php if ($recentMemo && $recentMemo->have()): ?>
                                <?php $recentMemoCount = 0; ?>
                                <?php while ($recentMemo->next()): ?>
                                    <?php if ($recentMemoCount >= $recentListLimit) { break; } ?>
                                    <li class="recent-item">
                                        <a class="recent-link" href="<?php echo escape($recentMemo->permalink); ?>">
                                            <span class="recent-link-text"><?php echo escape($recentMemo->title); ?></span>
                                        </a>
                                        <span class="recent-time"><?php echo escape($recentMemo->dateWord); ?></span>
                                    </li>
                                    <?php $recentMemoCount++; ?>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li class="recent-empty"><?php _e('暂无内容'); ?></li>
                            <?php endif; ?>
                        </ul>
                        <?php if ($memoUrl !== ''): ?>
                            <a class="recent-more" href="<?php echo escape($memoUrl); ?>">
                                <?php _e('还有更多'); ?>
                                <span class="recent-more-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-arrow-right-icon lucide-circle-arrow-right"><circle cx="12" cy="12" r="10"/><path d="m12 16 4-4-4-4"/><path d="M8 12h8"/></svg>
                                </span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="recent-right">
                    <div class="activity" aria-label="<?php _e('最近发生的事'); ?>">
                        <h2 class="recent-title"><?php _e('最近发生的事'); ?></h2>
                        <div class="activity-scroll">
                            <ul class="activity-list">
                            <?php if (!empty($activities)): ?>
                                <?php foreach ($activities as $activity): ?>
                                    <?php
                                    $activityType = (string) ($activity['type'] ?? '');
                                    $isCommentActivity = in_array($activityType, ['comment', 'reply'], true);
                                    $activityTitle = (string) ($activity['title'] ?? '');
                                    $activityUrl = (string) ($activity['url'] ?? '');
                                    $activityTimeWord = (string) ($activity['timeWord'] ?? '');
                                    $activityItemClass = 'activity-item';
                                    if ($isCommentActivity) {
                                        $activityItemClass .= ' is-comment';
                                    } elseif ($activityType === 'publish') {
                                        $activityItemClass .= ' is-publish';
                                    }
                                    ?>
                                    <li class="<?php echo escape($activityItemClass); ?>">
                                        <?php if ($isCommentActivity || $activityType === 'publish'): ?>
                                            <span class="activity-type" aria-hidden="true"></span>
                                        <?php endif; ?>
                                        <div class="activity-row">
                                            <div class="activity-left">
                                                <?php if ($isCommentActivity): ?>
                                                    <?php
                                                    $commentAuthor = trim((string) ($activity['author'] ?? ''));
                                                    $commentMail = trim((string) ($activity['mail'] ?? ''));
                                                    $commentTextRaw = (string) ($activity['text'] ?? '');
                                                    $commentAuthorId = (int) ($activity['authorId'] ?? 0);
                                                    $commentOwnerId = (int) ($activity['ownerId'] ?? 0);

                                                    $commentIsPrivate = isPrivateCommentText($commentTextRaw);
                                                    $commentCanViewPrivate = true;
                                                    if ($commentIsPrivate) {
                                                        $commentCanViewPrivate = canViewPrivateComment($commentOwnerId, $commentAuthorId);
                                                    }

                                                    $commentTextSource = $commentIsPrivate ? stripPrivateCommentMarker($commentTextRaw) : $commentTextRaw;

                                                    $commentText = trim(strip_tags($commentTextSource));
                                                    $commentText = (string) preg_replace('/\\s+/u', ' ', $commentText);

                                                    $commentBubbleClass = 'activity-bubble';
                                                    if ($commentIsPrivate) {
                                                        $commentBubbleClass .= ' is-private';
                                                        if (!$commentCanViewPrivate) {
                                                            $commentBubbleClass .= ' is-private-hidden';
                                                        }
                                                    }

                                                    $avatarHash = $commentMail !== '' ? md5(strtolower($commentMail)) : '';
                                                    $avatarUrl = $avatarHash !== '' ? ('http://www.gravatar.com/avatar/' . $avatarHash . '?s=32&d=retro') : '';
                                                    ?>
                                                    <?php if ($avatarUrl !== ''): ?>
                                                        <img class="activity-avatar" loading="lazy" src="<?php echo escape($avatarUrl); ?>" alt="" width="16" height="16">
                                                    <?php endif; ?>
                                                    <?php if ($commentAuthor !== ''): ?>
                                                        <span class="activity-name"><?php echo escape($commentAuthor); ?></span>
                                                    <?php endif; ?>
                                                    <small class="activity-small"><?php _e('在'); ?></small>
                                                    <a class="activity-post" href="<?php echo escape($activityUrl); ?>">
                                                        <span class="activity-post-text"><b><?php echo escape($activityTitle); ?></b></span>
                                                    </a>
                                                    <small class="activity-small"><?php _e('说：'); ?></small>
                                                <?php else: ?>
                                                    <span class="activity-muted"><?php _e('发布了'); ?></span>
                                                    <a class="activity-post" href="<?php echo escape($activityUrl); ?>">
                                                        <span class="activity-post-text"><?php echo escape($activityTitle); ?></span>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <span class="activity-time"><?php echo escape($activityTimeWord); ?></span>
                                         </div>
                                        <?php if ($isCommentActivity && ($commentText !== '' || $commentIsPrivate)): ?>
                                            <div class="<?php echo escape($commentBubbleClass); ?>"><?php
                                                if (!$commentIsPrivate || $commentCanViewPrivate) {
                                                    echo escape($commentText);
                                                }
                                            ?></div>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="activity-empty"><?php _e('暂无动态'); ?></li>
                            <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php endif; ?>
    <?php else: ?>
        <?php
        $categories = null;
        $tags = null;
        $postsCategorySlug = 'posts';

        try {
            $this->widget('Widget_Metas_Category_List')->to($categories);
        } catch (\Throwable $e) {
            $categories = null;
        }

        try {
            $this->widget('Widget_Metas_Tag_Cloud', 'ignoreZeroCount=1&limit=60')->to($tags);
        } catch (\Throwable $e) {
            $tags = null;
        }

        $pagerPrevIcon = <<<'HTML'
<span class="posts-pager-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg></span>
HTML;
        $pagerNextIcon = <<<'HTML'
<span class="posts-pager-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></span>
HTML;
        $pagerTemplate = [
            'wrapTag' => 'ol',
            'wrapClass' => 'page-navigator posts-pager',
            'itemTag' => 'li',
            'textTag' => 'span',
            'currentClass' => 'current',
            'prevClass' => 'prev',
            'nextClass' => 'next',
        ];
        ?>
        <section class="posts" aria-label="<?php _e('文章列表'); ?>">
            <div class="posts-layout">
                <div class="posts-main">
                    <?php if (!$this->is('index') && !$this->is('post') && !$this->is('category') && !$this->is('tag')): ?>
                        <h2 class="section-title"><?php $this->archiveTitle([
                                'category' => _t('分类 %s'),
                                'search'   => _t('搜索 %s'),
                                'tag'      => _t('标签 %s'),
                                'author'   => _t('%s 的文章')
                            ], '', ''); ?></h2>
                    <?php endif; ?>

                    <?php if ($this->have()): ?>
                        <ul class="posts-list" aria-label="<?php _e('文章'); ?>">
                            <?php while ($this->next()): ?>
                                <?php
                                $postCreated = 0;
                                $postModified = 0;
                                try {
                                    $postCreated = (int) ($this->created ?? 0);
                                } catch (\Throwable $e) {
                                    $postCreated = 0;
                                }
                                try {
                                    $postModified = (int) ($this->modified ?? 0);
                                } catch (\Throwable $e) {
                                    $postModified = 0;
                                }

                                $postExcerpt = '';
                                ob_start();
                                try {
                                    $this->excerpt(100, '...');
                                } catch (\Throwable $e) {
                                    // Ignore.
                                }
                                $postExcerpt = (string) ob_get_clean();
                                $postExcerpt = trim((string) preg_replace('/\\s+/u', ' ', $postExcerpt));
                                ?>
                                <li class="posts-item"
                                    data-post-created="<?php echo (int) $postCreated; ?>"
                                    data-post-modified="<?php echo (int) $postModified; ?>"
                                    data-post-excerpt="<?php echo escape($postExcerpt); ?>">
                                    <div class="posts-item-left">
                                        <a class="posts-title" href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a>
                                        <time class="posts-date" datetime="<?php $this->date('c'); ?>"><?php $this->date('Y/m/d-H:i:s'); ?></time>
                                    </div>

                                    <div class="posts-item-right" aria-label="<?php _e('标签'); ?>">
                                        <?php
                                        $postTags = [];
                                        try {
                                            $postTags = is_array($this->tags) ? $this->tags : [];
                                        } catch (\Throwable $e) {
                                            $postTags = [];
                                        }

                                        if (!empty($postTags)) {
                                            $max = 3;
                                            $i = 0;
                                            foreach ($postTags as $tag) {
                                                if ($i >= $max) {
                                                    break;
                                                }
                                                $name = (string) ($tag['name'] ?? '');
                                                $url = (string) ($tag['permalink'] ?? '');
                                                if ($name === '' || $url === '') {
                                                    continue;
                                                }
                                                $i += 1;
                                                echo '<a class="posts-tag" href="' . escape($url) . '">#' . escape($name) . '</a>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <ul class="posts-list" aria-label="<?php _e('文章'); ?>">
                            <li class="posts-empty"><?php _e('暂无内容'); ?></li>
                        </ul>
                    <?php endif; ?>

                    <?php renderPager($this, $pagerPrevIcon, $pagerNextIcon, 2, '...'); ?>
                </div>

                <aside class="posts-aside" aria-label="<?php _e('侧栏'); ?>">
                    <?php if (!$this->is('category')): ?>
                        <div class="posts-block" aria-label="<?php _e('分类'); ?>">
                            <h2 class="posts-block-title"><?php _e('分类'); ?></h2>
                            <div class="posts-links">
                                <?php
                                $seriesLinks = [];
                                if ($categories && $categories->have()) {
                                    $allCategories = [];
                                    $rootMid = null;
                                    while ($categories->next()) {
                                        $allCategories[] = [
                                            'mid' => (int) ($categories->mid ?? 0),
                                            'parent' => (int) ($categories->parent ?? 0),
                                            'slug' => (string) ($categories->slug ?? ''),
                                            'name' => (string) ($categories->name ?? ''),
                                            'url' => (string) ($categories->permalink ?? ''),
                                        ];
                                    }

                                    foreach ($allCategories as $cat) {
                                        if ($cat['slug'] === $postsCategorySlug) {
                                            $rootMid = (int) $cat['mid'];
                                            break;
                                        }
                                    }

                                    if ($rootMid) {
                                        foreach ($allCategories as $cat) {
                                            if ((int) $cat['parent'] !== $rootMid) {
                                                continue;
                                            }
                                            if ($cat['name'] === '' || $cat['url'] === '') {
                                                continue;
                                            }
                                            $seriesLinks[] = $cat;
                                        }
                                    }
                                }

                                if (!empty($seriesLinks)) {
                                    foreach ($seriesLinks as $cat) {
                                        echo '<a class="posts-link" href="' . escape($cat['url']) . '">' . escape($cat['name']) . '</a>';
                                    }
                                } else {
                                    echo '<span class="posts-empty">' . _t('暂无分类') . '</span>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!$this->is('tag')): ?>
                        <div class="posts-block" aria-label="<?php _e('标签'); ?>">
                            <h2 class="posts-block-title"><?php _e('标签'); ?></h2>
                            <div class="posts-tags">
                                <?php
                                if ($tags && $tags->have()) {
                                    while ($tags->next()) {
                                        $name = (string) ($tags->name ?? '');
                                        $url = (string) ($tags->permalink ?? '');
                                        if ($name === '' || $url === '') {
                                            continue;
                                        }
                                        echo '<a class="posts-tag-pill" href="' . escape($url) . '">' . escape($name) . '</a>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </aside>
            </div>
        </section>
    <?php endif; ?>
</main>

<?php $this->need('footer.php'); ?>
