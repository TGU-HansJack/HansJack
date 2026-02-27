<?php
/**
 * 寒士杰克主题
 *
 * @package 寒士杰克
 * @author 寒士杰克
 * @version 0.1.0
 * @link https://www.hansjack.com
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}


$this->need('header.php');
$themeConfig = hansJackBuildThemeConfig($this->options);

$brandName = '';
$welcomeText = '';
$faviconUrl = '';
$blogUrl = '';
$memoUrl = '';
$cvUrl = '';
$githubUrl = '';
$creativeUrl = '';
$blogSlug = 'posts';
$memoSlug = 'notes';
$landingHeatmapDays = 140;
$landingHeatmapSeries = [];
$landingLatestContent = null;
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

    ob_start();
    $this->options->siteUrl('favicon.ico');
    $faviconUrl = trim((string) ob_get_clean());

    $blogUrl = (string) (($themeConfig['links']['blog'] ?? '') ?: '');
    $memoUrl = (string) (($themeConfig['links']['memo'] ?? '') ?: '');
    $cvUrl = trim((string) (($themeConfig['links']['cv'] ?? '') ?: ''));
    $githubUrl = trim((string) (($themeConfig['links']['github'] ?? '') ?: ''));
    $creativeUrl = trim((string) (($themeConfig['links']['creative'] ?? '') ?: ''));

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
        $this->widget('Widget_Metas_Category_List@hj_landing_categories')->to($landingCategories);
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
        $this->widget('Widget_Contents_Post_Recent@hj_landing_posts', 'pageSize=9999', null, false)->to($landingPosts);
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
}
?>

<main class="hj-main" role="main">
    <?php if ($this->is('index')): ?>
        <section class="hj-landing" aria-label="<?php _e('欢迎'); ?>">
            <div class="hj-landing-main">
                <div class="hj-landing-left">
                    <div class="hj-landing-terminal" role="region" aria-label="<?php _e('欢迎词'); ?>">
                        <div class="hj-landing-prompt" aria-hidden="true">site@typecho:~$</div>
                        <div class="hj-landing-typing" aria-label="<?php echo hansJackEscape($welcomeText); ?>">
                            <span class="hj-landing-typing-text" data-text="<?php echo hansJackEscape($welcomeText); ?>"></span>
                            <span class="hj-landing-cursor" aria-hidden="true"></span>
                        </div>
                    </div>
                    <?php if ($cvUrl !== '' || $githubUrl !== '' || $creativeUrl !== ''): ?>
                        <div class="hj-landing-actions" role="group" aria-label="<?php _e('链接'); ?>">
                            <?php if ($cvUrl !== ''): ?>
                                <a class="hj-landing-icon-btn" href="<?php echo hansJackEscape($cvUrl); ?>" target="_blank" rel="noreferrer" aria-label="CV" title="CV">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-user-icon lucide-file-user" aria-hidden="true"><path d="M6 22a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h8a2.4 2.4 0 0 1 1.704.706l3.588 3.588A2.4 2.4 0 0 1 20 8v12a2 2 0 0 1-2 2z"/><path d="M14 2v5a1 1 0 0 0 1 1h5"/><path d="M16 22a4 4 0 0 0-8 0"/><circle cx="12" cy="15" r="3"/></svg>
                                </a>
                            <?php endif; ?>
                            <?php if ($githubUrl !== ''): ?>
                                <a class="hj-landing-icon-btn" href="<?php echo hansJackEscape($githubUrl); ?>" target="_blank" rel="noreferrer" aria-label="GitHub" title="GitHub">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-github-icon lucide-github" aria-hidden="true"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/><path d="M9 18c-4.51 2-5-2-7-2"/></svg>
                                </a>
                            <?php endif; ?>
                            <?php if ($creativeUrl !== ''): ?>
                                <a class="hj-landing-icon-btn" href="<?php echo hansJackEscape($creativeUrl); ?>" target="_blank" rel="noreferrer" aria-label="<?php _e('创意'); ?>" title="<?php _e('创意'); ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pickaxe-icon lucide-pickaxe" aria-hidden="true"><path d="m14 13-8.381 8.38a1 1 0 0 1-3.001-3L11 9.999"/><path d="M15.973 4.027A13 13 0 0 0 5.902 2.373c-1.398.342-1.092 2.158.277 2.601a19.9 19.9 0 0 1 5.822 3.024"/><path d="M16.001 11.999a19.9 19.9 0 0 1 3.024 5.824c.444 1.369 2.26 1.676 2.603.278A13 13 0 0 0 20 8.069"/><path d="M18.352 3.352a1.205 1.205 0 0 0-1.704 0l-5.296 5.296a1.205 1.205 0 0 0 0 1.704l2.296 2.296a1.205 1.205 0 0 0 1.704 0l5.296-5.296a1.205 1.205 0 0 0 0-1.704z"/></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="hj-landing-right">
                    <div class="hj-landing-avatar" aria-hidden="true">
                        <img src="<?php echo hansJackEscape($faviconUrl); ?>" alt="">
                    </div>
                </div>
            </div>

            <div class="hj-landing-insights" role="region" aria-label="<?php _e('热力图与最新内容'); ?>">
                <footer class="hj-landing-insights-footer">
                    <section class="hj-landing-heatmap-grid" aria-label="<?php echo hansJackEscape(sprintf(_t('最近 %d 天内容热力图'), (int) $landingHeatmapDays)); ?>">
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
                            <figure class="hj-landing-heatmap-item">
                                <i class="hj-landing-heatmap-dot <?php echo hansJackEscape($dotClass); ?>" aria-hidden="true"></i>
                                <figcaption class="hj-landing-heatmap-pop">
                                    <time class="hj-landing-heatmap-date"><?php echo hansJackEscape((string) ($day['dateLabel'] ?? '')); ?></time>
                                    <?php if ($dayTotal <= 0): ?>
                                        <p class="hj-landing-heatmap-empty"><?php _e('无字'); ?></p>
                                    <?php else: ?>
                                        <?php if (!empty($dayNotes)): ?>
                                            <p class="hj-landing-heatmap-kind"><?php echo hansJackEscape(sprintf(_t('博文 %d 篇：'), count($dayNotes))); ?></p>
                                            <ul class="hj-landing-heatmap-list">
                                                <?php foreach (array_slice($dayNotes, 0, $previewLimit) as $item): ?>
                                                    <li><a class="hj-landing-heatmap-link" href="<?php echo hansJackEscape((string) ($item['url'] ?? '')); ?>"><?php echo hansJackEscape((string) ($item['title'] ?? '')); ?></a></li>
                                                <?php endforeach; ?>
                                                <?php if (count($dayNotes) > $previewLimit): ?>
                                                    <li class="hj-landing-heatmap-more"><?php echo hansJackEscape(sprintf(_t('另有 %d 篇'), count($dayNotes) - $previewLimit)); ?></li>
                                                <?php endif; ?>
                                            </ul>
                                        <?php endif; ?>

                                        <?php if (!empty($dayMemos)): ?>
                                            <p class="hj-landing-heatmap-kind"><?php echo hansJackEscape(sprintf(_t('手记 %d 则：'), count($dayMemos))); ?></p>
                                            <ul class="hj-landing-heatmap-list">
                                                <?php foreach (array_slice($dayMemos, 0, $previewLimit) as $item): ?>
                                                    <li><a class="hj-landing-heatmap-link" href="<?php echo hansJackEscape((string) ($item['url'] ?? '')); ?>"><?php echo hansJackEscape((string) ($item['title'] ?? '')); ?></a></li>
                                                <?php endforeach; ?>
                                                <?php if (count($dayMemos) > $previewLimit): ?>
                                                    <li class="hj-landing-heatmap-more"><?php echo hansJackEscape(sprintf(_t('另有 %d 则'), count($dayMemos) - $previewLimit)); ?></li>
                                                <?php endif; ?>
                                            </ul>
                                        <?php endif; ?>

                                        <?php if (!empty($dayOthers)): ?>
                                            <p class="hj-landing-heatmap-kind"><?php echo hansJackEscape(sprintf(_t('内容 %d 条：'), count($dayOthers))); ?></p>
                                            <ul class="hj-landing-heatmap-list">
                                                <?php foreach (array_slice($dayOthers, 0, $previewLimit) as $item): ?>
                                                    <li><a class="hj-landing-heatmap-link" href="<?php echo hansJackEscape((string) ($item['url'] ?? '')); ?>"><?php echo hansJackEscape((string) ($item['title'] ?? '')); ?></a></li>
                                                <?php endforeach; ?>
                                                <?php if (count($dayOthers) > $previewLimit): ?>
                                                    <li class="hj-landing-heatmap-more"><?php echo hansJackEscape(sprintf(_t('另有 %d 条'), count($dayOthers) - $previewLimit)); ?></li>
                                                <?php endif; ?>
                                            </ul>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </section>

                    <blockquote class="hj-landing-latest">
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
                            <div class="hj-landing-latest-main">
                                <div class="hj-landing-latest-head">
                                    <?php if ($latestUrl !== ''): ?>
                                        <a href="<?php echo hansJackEscape($latestUrl); ?>" class="hj-landing-latest-link"><?php echo hansJackEscape($latestTitle); ?></a>
                                    <?php else: ?>
                                        <span class="hj-landing-latest-link"><?php echo hansJackEscape($latestTitle); ?></span>
                                    <?php endif; ?>

                                    <?php if (!empty($latestTags)): ?>
                                        <div class="hj-landing-latest-tags">
                                            <?php foreach ($latestTags as $tag): ?>
                                                <?php
                                                $tagName = trim((string) ($tag['name'] ?? ''));
                                                $tagUrl = trim((string) ($tag['url'] ?? ''));
                                                if ($tagName === '' || $tagUrl === '') {
                                                    continue;
                                                }
                                                ?>
                                                <a href="<?php echo hansJackEscape($tagUrl); ?>" class="hj-landing-latest-tag">#<?php echo hansJackEscape($tagName); ?></a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($latestTimeLabel !== ''): ?>
                                    <time datetime="<?php echo hansJackEscape($latestDatetime); ?>" class="hj-landing-latest-time" title="<?php echo hansJackEscape($latestTimeTitle); ?>"><?php echo hansJackEscape($latestTimeLabel); ?></time>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="hj-landing-latest-empty"><?php _e('暂无内容'); ?></p>
                        <?php endif; ?>
                    </blockquote>
                </footer>
            </div>

            <div class="hj-landing-bottom" role="group" aria-label="<?php _e('名言'); ?>">
                <p class="hj-hitokoto-text" aria-live="polite"><?php _e('正在获取名言...'); ?></p>
                <button class="hj-scroll-down" type="button" aria-label="<?php _e('向下滚动'); ?>" title="<?php _e('向下滚动'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down-icon lucide-chevron-down" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </button>
            </div>
        </section>

        <?php if (false): ?>
        <section class="hj-recent" id="hj-recent" aria-label="<?php _e('最近内容'); ?>">
            <?php
            $recentBlog = null;
            $recentMemo = null;
            $recentComments = null;
            $activityPosts = null;
            $recentListLimit = 5;
            $activityPageSize = 9999;

            try {
                $this->widget(
                    'Widget_Archive@hj_recent_posts',
                    'pageSize=' . $recentListLimit . '&type=category',
                    'slug=' . urlencode($blogSlug),
                    false
                )->to($recentBlog);
            } catch (\Throwable $e) {
                $recentBlog = null;
            }

            try {
                $this->widget(
                    'Widget_Archive@hj_recent_memo',
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
                $this->widget('Widget_Contents_Post_Recent@hj_activity_posts', 'pageSize=' . $activityPageSize, null, false)->to($activityPosts);
            } catch (\Throwable $e) {
                $activityPosts = null;
            }

            $hjActivities = [];
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

                    $hjActivities[] = [
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

                    $hjActivities[] = [
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

            usort($hjActivities, function ($a, $b) {
                return ((int) ($b['created'] ?? 0)) <=> ((int) ($a['created'] ?? 0));
            });
            ?>

            <div class="hj-recent-grid">
                <div class="hj-recent-left">
                    <div class="hj-recent-panel" aria-label="<?php _e('最近更新的文章'); ?>">
                        <h2 class="hj-recent-title"><?php _e('最近更新的文章'); ?></h2>
                        <ul class="hj-recent-list">
                            <?php if ($recentBlog && $recentBlog->have()): ?>
                                <?php $recentBlogCount = 0; ?>
                                <?php while ($recentBlog->next()): ?>
                                    <?php if ($recentBlogCount >= $recentListLimit) { break; } ?>
                                    <li class="hj-recent-item">
                                        <a class="hj-recent-link" href="<?php echo hansJackEscape($recentBlog->permalink); ?>">
                                            <span class="hj-recent-link-text"><?php echo hansJackEscape($recentBlog->title); ?></span>
                                        </a>
                                        <span class="hj-recent-time"><?php echo hansJackEscape($recentBlog->dateWord); ?></span>
                                    </li>
                                    <?php $recentBlogCount++; ?>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li class="hj-recent-empty"><?php _e('暂无内容'); ?></li>
                            <?php endif; ?>
                        </ul>
                        <?php if ($blogUrl !== ''): ?>
                            <a class="hj-recent-more" href="<?php echo hansJackEscape($blogUrl); ?>">
                                <?php _e('还有更多'); ?>
                                <span class="hj-recent-more-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-arrow-right-icon lucide-circle-arrow-right"><circle cx="12" cy="12" r="10"/><path d="m12 16 4-4-4-4"/><path d="M8 12h8"/></svg>
                                </span>
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="hj-recent-panel" aria-label="<?php _e('最近更新的手记'); ?>">
                        <h2 class="hj-recent-title"><?php _e('最近更新的手记'); ?></h2>
                        <ul class="hj-recent-list">
                            <?php if ($recentMemo && $recentMemo->have()): ?>
                                <?php $recentMemoCount = 0; ?>
                                <?php while ($recentMemo->next()): ?>
                                    <?php if ($recentMemoCount >= $recentListLimit) { break; } ?>
                                    <li class="hj-recent-item">
                                        <a class="hj-recent-link" href="<?php echo hansJackEscape($recentMemo->permalink); ?>">
                                            <span class="hj-recent-link-text"><?php echo hansJackEscape($recentMemo->title); ?></span>
                                        </a>
                                        <span class="hj-recent-time"><?php echo hansJackEscape($recentMemo->dateWord); ?></span>
                                    </li>
                                    <?php $recentMemoCount++; ?>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li class="hj-recent-empty"><?php _e('暂无内容'); ?></li>
                            <?php endif; ?>
                        </ul>
                        <?php if ($memoUrl !== ''): ?>
                            <a class="hj-recent-more" href="<?php echo hansJackEscape($memoUrl); ?>">
                                <?php _e('还有更多'); ?>
                                <span class="hj-recent-more-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-arrow-right-icon lucide-circle-arrow-right"><circle cx="12" cy="12" r="10"/><path d="m12 16 4-4-4-4"/><path d="M8 12h8"/></svg>
                                </span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="hj-recent-right">
                    <div class="hj-activity" aria-label="<?php _e('最近发生的事'); ?>">
                        <h2 class="hj-recent-title"><?php _e('最近发生的事'); ?></h2>
                        <div class="hj-activity-scroll">
                            <ul class="hj-activity-list">
                            <?php if (!empty($hjActivities)): ?>
                                <?php foreach ($hjActivities as $activity): ?>
                                    <?php
                                    $activityType = (string) ($activity['type'] ?? '');
                                    $isCommentActivity = in_array($activityType, ['comment', 'reply'], true);
                                    $activityTitle = (string) ($activity['title'] ?? '');
                                    $activityUrl = (string) ($activity['url'] ?? '');
                                    $activityTimeWord = (string) ($activity['timeWord'] ?? '');
                                    $activityItemClass = 'hj-activity-item';
                                    if ($isCommentActivity) {
                                        $activityItemClass .= ' is-comment';
                                    } elseif ($activityType === 'publish') {
                                        $activityItemClass .= ' is-publish';
                                    }
                                    ?>
                                    <li class="<?php echo hansJackEscape($activityItemClass); ?>">
                                        <?php if ($isCommentActivity || $activityType === 'publish'): ?>
                                            <span class="hj-activity-type" aria-hidden="true"></span>
                                        <?php endif; ?>
                                        <div class="hj-activity-row">
                                            <div class="hj-activity-left">
                                                <?php if ($isCommentActivity): ?>
                                                    <?php
                                                    $commentAuthor = trim((string) ($activity['author'] ?? ''));
                                                    $commentMail = trim((string) ($activity['mail'] ?? ''));
                                                    $commentTextRaw = (string) ($activity['text'] ?? '');
                                                    $commentAuthorId = (int) ($activity['authorId'] ?? 0);
                                                    $commentOwnerId = (int) ($activity['ownerId'] ?? 0);

                                                    $commentIsPrivate = hansJackIsPrivateCommentText($commentTextRaw);
                                                    $commentCanViewPrivate = true;
                                                    if ($commentIsPrivate) {
                                                        $commentCanViewPrivate = hansJackCanViewPrivateComment($commentOwnerId, $commentAuthorId);
                                                    }

                                                    $commentTextSource = $commentIsPrivate ? hansJackStripPrivateCommentMarker($commentTextRaw) : $commentTextRaw;

                                                    $commentText = trim(strip_tags($commentTextSource));
                                                    $commentText = (string) preg_replace('/\\s+/u', ' ', $commentText);

                                                    $commentBubbleClass = 'hj-activity-bubble';
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
                                                        <img class="hj-activity-avatar" loading="lazy" src="<?php echo hansJackEscape($avatarUrl); ?>" alt="" width="16" height="16">
                                                    <?php endif; ?>
                                                    <?php if ($commentAuthor !== ''): ?>
                                                        <span class="hj-activity-name"><?php echo hansJackEscape($commentAuthor); ?></span>
                                                    <?php endif; ?>
                                                    <small class="hj-activity-small"><?php _e('在'); ?></small>
                                                    <a class="hj-activity-post" href="<?php echo hansJackEscape($activityUrl); ?>">
                                                        <span class="hj-activity-post-text"><b><?php echo hansJackEscape($activityTitle); ?></b></span>
                                                    </a>
                                                    <small class="hj-activity-small"><?php _e('说：'); ?></small>
                                                <?php else: ?>
                                                    <span class="hj-activity-muted"><?php _e('发布了'); ?></span>
                                                    <a class="hj-activity-post" href="<?php echo hansJackEscape($activityUrl); ?>">
                                                        <span class="hj-activity-post-text"><?php echo hansJackEscape($activityTitle); ?></span>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                            <span class="hj-activity-time"><?php echo hansJackEscape($activityTimeWord); ?></span>
                                         </div>
                                        <?php if ($isCommentActivity && ($commentText !== '' || $commentIsPrivate)): ?>
                                            <div class="<?php echo hansJackEscape($commentBubbleClass); ?>"><?php
                                                if (!$commentIsPrivate || $commentCanViewPrivate) {
                                                    echo hansJackEscape($commentText);
                                                }
                                            ?></div>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="hj-activity-empty"><?php _e('暂无动态'); ?></li>
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

        $hjPagerPrevIcon = <<<'HTML'
<span class="hj-posts-pager-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg></span>
HTML;
        $hjPagerNextIcon = <<<'HTML'
<span class="hj-posts-pager-icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg></span>
HTML;
        $hjPagerTemplate = [
            'wrapTag' => 'ol',
            'wrapClass' => 'page-navigator hj-posts-pager',
            'itemTag' => 'li',
            'textTag' => 'span',
            'currentClass' => 'current',
            'prevClass' => 'prev',
            'nextClass' => 'next',
        ];
        ?>
        <section class="hj-posts" aria-label="<?php _e('文章列表'); ?>">
            <div class="hj-posts-layout">
                <div class="hj-posts-main">
                    <?php if (!$this->is('index') && !$this->is('post') && !$this->is('category') && !$this->is('tag')): ?>
                        <h2 class="hj-section-title"><?php $this->archiveTitle([
                                'category' => _t('分类 %s'),
                                'search'   => _t('搜索 %s'),
                                'tag'      => _t('标签 %s'),
                                'author'   => _t('%s 的文章')
                            ], '', ''); ?></h2>
                    <?php endif; ?>

                    <?php if ($this->have()): ?>
                        <ul class="hj-posts-list" aria-label="<?php _e('文章'); ?>">
                            <?php while ($this->next()): ?>
                                <?php
                                $hjPostCreated = 0;
                                $hjPostModified = 0;
                                try {
                                    $hjPostCreated = (int) ($this->created ?? 0);
                                } catch (\Throwable $e) {
                                    $hjPostCreated = 0;
                                }
                                try {
                                    $hjPostModified = (int) ($this->modified ?? 0);
                                } catch (\Throwable $e) {
                                    $hjPostModified = 0;
                                }

                                $hjPostExcerpt = '';
                                ob_start();
                                try {
                                    $this->excerpt(100, '...');
                                } catch (\Throwable $e) {
                                    // Ignore.
                                }
                                $hjPostExcerpt = (string) ob_get_clean();
                                $hjPostExcerpt = trim((string) preg_replace('/\\s+/u', ' ', $hjPostExcerpt));
                                ?>
                                <li class="hj-posts-item"
                                    data-hj-post-created="<?php echo (int) $hjPostCreated; ?>"
                                    data-hj-post-modified="<?php echo (int) $hjPostModified; ?>"
                                    data-hj-post-excerpt="<?php echo hansJackEscape($hjPostExcerpt); ?>">
                                    <div class="hj-posts-item-left">
                                        <a class="hj-posts-title" href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a>
                                        <time class="hj-posts-date" datetime="<?php $this->date('c'); ?>"><?php $this->date('Y/m/d-H:i:s'); ?></time>
                                    </div>

                                    <div class="hj-posts-item-right" aria-label="<?php _e('标签'); ?>">
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
                                                echo '<a class="hj-posts-tag" href="' . hansJackEscape($url) . '">#' . hansJackEscape($name) . '</a>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <ul class="hj-posts-list" aria-label="<?php _e('文章'); ?>">
                            <li class="hj-posts-empty"><?php _e('暂无内容'); ?></li>
                        </ul>
                    <?php endif; ?>

                    <?php hansJackRenderPager($this, $hjPagerPrevIcon, $hjPagerNextIcon, 2, '...'); ?>
                </div>

                <aside class="hj-posts-aside" aria-label="<?php _e('侧栏'); ?>">
                    <?php if (!$this->is('category')): ?>
                        <div class="hj-posts-block" aria-label="<?php _e('分类'); ?>">
                            <h2 class="hj-posts-block-title"><?php _e('分类'); ?></h2>
                            <div class="hj-posts-links">
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
                                        echo '<a class="hj-posts-link" href="' . hansJackEscape($cat['url']) . '">' . hansJackEscape($cat['name']) . '</a>';
                                    }
                                } else {
                                    echo '<span class="hj-posts-empty">' . _t('暂无分类') . '</span>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!$this->is('tag')): ?>
                        <div class="hj-posts-block" aria-label="<?php _e('标签'); ?>">
                            <h2 class="hj-posts-block-title"><?php _e('标签'); ?></h2>
                            <div class="hj-posts-tags">
                                <?php
                                if ($tags && $tags->have()) {
                                    while ($tags->next()) {
                                        $name = (string) ($tags->name ?? '');
                                        $url = (string) ($tags->permalink ?? '');
                                        if ($name === '' || $url === '') {
                                            continue;
                                        }
                                        echo '<a class="hj-posts-tag-pill" href="' . hansJackEscape($url) . '">' . hansJackEscape($name) . '</a>';
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
