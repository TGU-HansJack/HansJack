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
$landingAvatarAlt = '站点头像';
$blogUrl = '';
$memoUrl = '';
$blogSlug = 'posts';
$memoSlug = 'notes';
$landingHeatmapDays = 140;
$landingHeatmapSeries = [];
$landingHeatmapColumns = 20;
$landingLatestContent = null;
$landingLetters = [];
$landingMemories = [];
$landingSeasonalTimeline = [];
$landingSeasonalTimelineMobile = [];
$landingSeasonalTotalCount = 0;
$landingSiteWordCount = 0;
$landingSiteActiveDays = 0;
$landingSitePostsCount = 0;
$landingSiteCommentsCount = 0;
$landingSiteStatsLabel = '0 字 0 天 0 篇 0评论';
$landingMemosPageUrl = '';
$landingHitokotoEnabled = true;
$landingPresenceEnabled = false;
$landingPresenceEndpoint = '';
$landingPresenceState = 'offline';
$landingPresenceTitle = _t('实时活动图标：实现了实时的系统进程和媒体信息上报。');
$landingPresenceIcon = '';
if ($this->is('index')) {
    $landingCountChars = static function (string $text): int {
        $plain = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $plainNoSpace = (string) preg_replace('/\\s+/u', '', $plain);
        if ($plainNoSpace === '') {
            return 0;
        }
        return function_exists('mb_strlen')
            ? (int) mb_strlen($plainNoSpace, 'UTF-8')
            : (int) strlen($plainNoSpace);
    };

    $brandName = trim((string) ($themeConfig['brandName'] ?? ''));
    if ($brandName === '') {
        ob_start();
        $this->options->title();
        $brandName = trim((string) ob_get_clean());
    }

    $welcomeText = trim((string) ($themeConfig['welcomeText'] ?? ''));
    if ($welcomeText === '') {
        $welcomeText = trim('欢迎来到 ' . $brandName);
    }

    $landingAvatarUrl = assetUrl($this->options, 'logo.avif');
    if ($landingAvatarUrl === '') {
        ob_start();
        $this->options->siteUrl('favicon.ico');
        $landingAvatarUrl = trim((string) ob_get_clean());
    }
    $landingAvatarAlt = trim($brandName) !== '' ? ($brandName . '头像') : '站点头像';

    $blogUrl = (string) (($themeConfig['links']['blog'] ?? '') ?: '');
    $memoUrl = (string) (($themeConfig['links']['memo'] ?? '') ?: '');
    $landingHitokotoEnabled = (bool) ($themeConfig['landingHitokotoEnabled'] ?? true);
    $landingPresenceEnabled = (bool) ($themeConfig['presenceStatusEnabled'] ?? false);

    if ($landingPresenceEnabled) {
        $landingPresencePayload = hansjackPresenceStatusPublicPayload($this->options);
        $landingPresenceEndpoint = trim((string) ($landingPresencePayload['endpoint'] ?? ($themeConfig['presenceStatusEndpoint'] ?? '')));
        $landingPresenceState = trim((string) ($landingPresencePayload['state'] ?? 'offline'));
        if ($landingPresenceState === '') {
            $landingPresenceState = 'offline';
        }
        $landingPresenceTitle = trim((string) ($landingPresencePayload['title'] ?? ''));
        if ($landingPresenceTitle === '') {
            $landingPresenceTitle = _t('实时活动图标：实现了实时的系统进程和媒体信息上报。');
        }
        $landingPresenceIcon = trim((string) ($landingPresencePayload['icon'] ?? ''));
    }

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

    $landingLettersLimit = 5;
    $landingMemoriesLimit = 5;
    $landingCommentsPageSize = 180;
    $landingMemosPageCid = 0;

    try {
        $this->widget('Widget_Contents_Page_List@landing_pages')->to($landingPages);
        if ($landingPages && $landingPages->have()) {
            while ($landingPages->next()) {
                $landingPageSlug = trim((string) ($landingPages->slug ?? ''));
                $landingPageSlugLower = function_exists('mb_strtolower')
                    ? mb_strtolower($landingPageSlug, 'UTF-8')
                    : strtolower($landingPageSlug);
                if ($landingPageSlugLower !== 'memos') {
                    continue;
                }

                $landingMemosPageCid = (int) ($landingPages->cid ?? 0);
                $landingMemosPageUrl = trim((string) ($landingPages->permalink ?? ''));
                break;
            }
        }
    } catch (\Throwable $e) {
        $landingMemosPageCid = 0;
        $landingMemosPageUrl = '';
    }

    $landingMemoryDateLabel = static function (int $timestamp): string {
        if ($timestamp <= 0) {
            return '';
        }

        $weekdayMap = ['日', '一', '二', '三', '四', '五', '六'];
        $weekday = $weekdayMap[(int) date('w', $timestamp)] ?? '';
        $base = date('Y年n月j日', $timestamp);
        if ($weekday === '') {
            return $base;
        }
        return $base . '星期' . $weekday;
    };

    $landingStripCommentImages = static function (string $html): array {
        $value = trim($html);
        if ($value === '') {
            return [
                'html' => '',
                'hasImage' => false,
            ];
        }

        $hasImage = false;
        if (
            preg_match('/<img\b/i', $value) === 1
            || preg_match('/<picture\b/i', $value) === 1
            || preg_match('/!\[[^\]]*\]\([^)]+\)/u', $value) === 1
        ) {
            $hasImage = true;
        }

        $withoutFigures = preg_replace('/<figure\b[^>]*>.*?<\/figure>/isu', ' ', $value);
        if ($withoutFigures !== null) {
            $value = $withoutFigures;
        }

        $withoutPictures = preg_replace('/<picture\b[^>]*>.*?<\/picture>/isu', ' ', $value);
        if ($withoutPictures !== null) {
            $value = $withoutPictures;
        }

        $withoutImages = preg_replace('/<img\b[^>]*>/iu', ' ', $value);
        if ($withoutImages !== null) {
            $value = $withoutImages;
        }

        $withoutMarkdownImages = preg_replace('/!\[[^\]]*\]\([^)]+\)/u', ' ', $value);
        if ($withoutMarkdownImages !== null) {
            $value = $withoutMarkdownImages;
        }

        $withoutEmptyParagraphs = preg_replace('/<p>\s*(?:&nbsp;|\x{00A0}|\s|<br\s*\/?>)*<\/p>/iu', '', $value);
        if ($withoutEmptyParagraphs !== null) {
            $value = $withoutEmptyParagraphs;
        }

        $value = trim($value);
        if ($hasImage) {
            $hasText = trim(strip_tags($value)) !== '';
            if (!$hasText) {
                $value = '<p><span class="landing-image-note">（有图）</span></p>';
            } elseif (preg_match('/<\/p>\s*$/iu', $value) === 1) {
                $value = (string) preg_replace('/<\/p>\s*$/iu', '<span class="landing-image-note">（有图）</span></p>', $value, 1);
            } else {
                $value .= '<p><span class="landing-image-note">（有图）</span></p>';
            }
        }

        return [
            'html' => $value,
            'hasImage' => $hasImage,
        ];
    };

    $landingRecentComments = null;
    try {
        $this->widget('Widget_Comments_Recent@landing_comments', 'pageSize=' . $landingCommentsPageSize, null, false)->to($landingRecentComments);
    } catch (\Throwable $e) {
        $landingRecentComments = null;
    }

    if ($landingRecentComments && $landingRecentComments->have()) {
        $landingMemosBaseUrl = '';
        if ($landingMemosPageUrl !== '') {
            $landingMemosBaseUrl = (string) preg_replace('/[#?].*$/', '', $landingMemosPageUrl);
            $landingMemosBaseUrl = rtrim($landingMemosBaseUrl, '/');
        }

        while ($landingRecentComments->next()) {
            $commentTextRaw = (string) ($landingRecentComments->text ?? '');
            $commentIsPrivate = function_exists('isPrivateCommentText')
                ? isPrivateCommentText($commentTextRaw)
                : false;
            if ($commentIsPrivate) {
                continue;
            }

            $commentPlainText = trim(strip_tags($commentTextRaw));
            $commentPlainText = (string) preg_replace('/\s+/u', ' ', $commentPlainText);

            $commentContentHtml = '';
            if (function_exists('renderCommentContent')) {
                $commentContentHtml = trim((string) renderCommentContent($landingRecentComments));
            }

            if ($commentContentHtml === '' && $commentPlainText !== '') {
                $commentContentHtml = '<p>' . nl2br(escape($commentPlainText)) . '</p>';
            }

            $commentContentPrepared = $landingStripCommentImages($commentContentHtml);
            $commentContentHtml = trim((string) ($commentContentPrepared['html'] ?? ''));

            if ($commentContentHtml === '') {
                continue;
            }

            $commentCreated = (int) ($landingRecentComments->created ?? 0);
            $commentAuthor = trim((string) ($landingRecentComments->author ?? ''));
            if ($commentAuthor === '') {
                $commentAuthor = _t('匿名');
            }
            $commentAuthorUrl = trim((string) ($landingRecentComments->url ?? ''));
            $commentMail = strtolower(trim((string) ($landingRecentComments->mail ?? '')));
            $commentAvatarUrl = 'https://cdn.sep.cc/avatar/' . md5($commentMail) . '?s=64&d=mp&r=g';
            $commentCoid = (int) ($landingRecentComments->coid ?? 0);
            $commentTimeWord = trim((string) ($landingRecentComments->dateWord ?? ''));
            $commentTargetTitle = trim((string) ($landingRecentComments->title ?? ''));
            if ($commentTargetTitle === '') {
                $commentTargetTitle = _t('无标题');
            }
            $commentTargetUrl = trim((string) ($landingRecentComments->permalink ?? ''));

            if (count($landingLetters) < $landingLettersLimit) {
                $landingLetters[] = [
                    'id' => $commentCoid,
                    'contentHtml' => $commentContentHtml,
                    'author' => $commentAuthor,
                    'authorUrl' => $commentAuthorUrl,
                    'avatar' => $commentAvatarUrl,
                    'dateIso' => $commentCreated > 0 ? date('c', $commentCreated) : '',
                    'dateTime' => $commentCreated > 0 ? date('Y/m/d H:i:s', $commentCreated) : '',
                    'timeWord' => $commentTimeWord,
                    'postTitle' => $commentTargetTitle,
                    'postUrl' => $commentTargetUrl,
                ];
            }

            $isMemoryComment = false;
            $commentCid = (int) ($landingRecentComments->cid ?? 0);
            if ($landingMemosPageCid > 0 && $commentCid > 0) {
                $isMemoryComment = ($landingMemosPageCid === $commentCid);
            } elseif ($landingMemosBaseUrl !== '' && $commentTargetUrl !== '') {
                $commentBaseUrl = (string) preg_replace('/[#?].*$/', '', $commentTargetUrl);
                $commentBaseUrl = rtrim($commentBaseUrl, '/');
                $isMemoryComment = (
                    $commentBaseUrl === $landingMemosBaseUrl
                    || strpos($commentBaseUrl, $landingMemosBaseUrl . '/') === 0
                );
            }

            if ($isMemoryComment && count($landingMemories) < $landingMemoriesLimit) {
                $landingMemories[] = [
                    'id' => $commentCoid,
                    'contentHtml' => $commentContentHtml,
                    'author' => $commentAuthor,
                    'authorUrl' => $commentAuthorUrl,
                    'avatar' => $commentAvatarUrl,
                    'dateIso' => $commentCreated > 0 ? date('c', $commentCreated) : '',
                    'dateTime' => $commentCreated > 0 ? date('Y/m/d H:i:s', $commentCreated) : '',
                    'dateLabel' => $landingMemoryDateLabel($commentCreated),
                    'timeWord' => $commentTimeWord,
                    'postTitle' => $commentTargetTitle,
                    'postUrl' => $commentTargetUrl,
                ];
            }

            if (count($landingLetters) >= $landingLettersLimit && count($landingMemories) >= $landingMemoriesLimit) {
                break;
            }
        }
    }

    $landingSiteStartTs = 0;
    $landingPrivateMarker = function_exists('privateCommentMarker') ? (string) privateCommentMarker() : '<!--private-->';

    try {
        $db = \Typecho\Db::get();

        $postRows = $db->fetchAll(
            $db->select('created', 'text')
                ->from('table.contents')
                ->where('type = ?', 'post')
                ->where('status = ?', 'publish')
        );

        if (is_array($postRows)) {
            foreach ($postRows as $row) {
                $rowCreated = 0;
                $rowText = '';

                if (is_object($row)) {
                    $rowCreated = (int) ($row->created ?? 0);
                    $rowText = (string) ($row->text ?? '');
                } elseif (is_array($row)) {
                    $rowCreated = (int) ($row['created'] ?? 0);
                    $rowText = (string) ($row['text'] ?? '');
                }

                $landingSitePostsCount += 1;
                if ($rowCreated > 0 && ($landingSiteStartTs <= 0 || $rowCreated < $landingSiteStartTs)) {
                    $landingSiteStartTs = $rowCreated;
                }
                if ($rowText !== '') {
                    $landingSiteWordCount += $landingCountChars((string) strip_tags($rowText));
                }
            }
        }

        $commentCountRow = $db->fetchObject(
            $db->select('COUNT(coid) AS total')
                ->from('table.comments')
                ->where('status = ?', 'approved')
                ->where('text NOT LIKE ?', '%' . $landingPrivateMarker . '%')
                ->limit(1)
        );
        if (is_object($commentCountRow)) {
            $landingSiteCommentsCount = (int) ($commentCountRow->total ?? 0);
        }

        $firstCommentRow = $db->fetchObject(
            $db->select('created')
                ->from('table.comments')
                ->where('status = ?', 'approved')
                ->where('text NOT LIKE ?', '%' . $landingPrivateMarker . '%')
                ->order('created', \Typecho\Db::SORT_ASC)
                ->limit(1)
        );
        if (is_object($firstCommentRow)) {
            $firstCommentCreated = (int) ($firstCommentRow->created ?? 0);
            if ($firstCommentCreated > 0 && ($landingSiteStartTs <= 0 || $firstCommentCreated < $landingSiteStartTs)) {
                $landingSiteStartTs = $firstCommentCreated;
            }
        }
    } catch (\Throwable $e) {
        // Keep zero-values as fallback.
    }

    if ($landingSiteStartTs > 0) {
        $landingSiteActiveDays = max(1, (int) floor((time() - $landingSiteStartTs) / 86400) + 1);
    } else {
        $landingSiteActiveDays = 0;
    }

    $landingSiteStatsLabel = number_format((int) $landingSiteWordCount)
        . ' 字 '
        . number_format((int) $landingSiteActiveDays)
        . ' 天 '
        . number_format((int) $landingSitePostsCount)
        . ' 篇 '
        . number_format((int) $landingSiteCommentsCount)
        . '评论';

    $landingSeasonalFetchLimit = 300;
    $landingSeasonalPerBucketLimit = 10;
    $landingTimelineCandidates = [];
    $landingTimelineSeen = [];

    $landingAppendTimelineItems = static function ($archiveWidget) use (&$landingTimelineCandidates, &$landingTimelineSeen): void {
        if (!$archiveWidget || !$archiveWidget->have()) {
            return;
        }

        while ($archiveWidget->next()) {
            $itemUrl = trim((string) ($archiveWidget->permalink ?? ''));
            if ($itemUrl === '') {
                continue;
            }
            $itemUrlKey = strtolower($itemUrl);
            if (isset($landingTimelineSeen[$itemUrlKey])) {
                continue;
            }

            $itemCreated = (int) ($archiveWidget->created ?? 0);
            if ($itemCreated <= 0) {
                continue;
            }

            $itemTitle = trim((string) ($archiveWidget->title ?? ''));
            if ($itemTitle === '') {
                $itemTitle = _t('无标题');
            }

            $landingTimelineSeen[$itemUrlKey] = true;
            $landingTimelineCandidates[] = [
                'created' => $itemCreated,
                'title' => $itemTitle,
                'url' => $itemUrl,
            ];
        }
    };

    $landingTimelineBlog = null;
    try {
        $this->widget(
            'Widget_Archive@landing_timeline_blog',
            'pageSize=' . $landingSeasonalFetchLimit . '&type=category',
            'slug=' . urlencode($blogSlug),
            false
        )->to($landingTimelineBlog);
    } catch (\Throwable $e) {
        $landingTimelineBlog = null;
    }
    $landingAppendTimelineItems($landingTimelineBlog);

    $landingTimelineMemo = null;
    try {
        $this->widget(
            'Widget_Archive@landing_timeline_memo',
            'pageSize=' . $landingSeasonalFetchLimit . '&type=category',
            'slug=' . urlencode($memoSlug),
            false
        )->to($landingTimelineMemo);
    } catch (\Throwable $e) {
        $landingTimelineMemo = null;
    }
    $landingAppendTimelineItems($landingTimelineMemo);

    usort($landingTimelineCandidates, static function (array $a, array $b): int {
        return ((int) ($b['created'] ?? 0)) <=> ((int) ($a['created'] ?? 0));
    });

    $landingSeasonNames = ['春', '夏', '秋', '冬'];
    $landingMonthNames = [
        1 => '一月',
        2 => '二月',
        3 => '三月',
        4 => '四月',
        5 => '五月',
        6 => '六月',
        7 => '七月',
        8 => '八月',
        9 => '九月',
        10 => '十月',
        11 => '十一月',
        12 => '十二月',
    ];

    $landingReadSeasonMeta = static function (int $timestamp) use ($landingSeasonNames, $landingMonthNames): array {
        if ($timestamp <= 0) {
            return [
                'order' => 0,
                'idx' => 0,
                'name' => $landingSeasonNames[0],
                'monthLabel' => '',
            ];
        }

        $year = (int) date('Y', $timestamp);
        $month = (int) date('n', $timestamp);
        $seasonIdx = 0;
        $seasonYear = $year;

        if ($month >= 3 && $month <= 5) {
            $seasonIdx = 0;
            $seasonYear = $year;
        } elseif ($month >= 6 && $month <= 8) {
            $seasonIdx = 1;
            $seasonYear = $year;
        } elseif ($month >= 9 && $month <= 11) {
            $seasonIdx = 2;
            $seasonYear = $year;
        } else {
            $seasonIdx = 3;
            $seasonYear = ($month === 12) ? ($year + 1) : $year;
        }

        return [
            'order' => ($seasonYear * 4) + $seasonIdx,
            'idx' => $seasonIdx,
            'name' => $landingSeasonNames[$seasonIdx] ?? $landingSeasonNames[0],
            'monthLabel' => $landingMonthNames[$month] ?? ((string) $month . '月'),
        ];
    };

    $landingCurrentSeasonMeta = $landingReadSeasonMeta(time());
    $landingCurrentSeasonOrder = (int) ($landingCurrentSeasonMeta['order'] ?? 0);
    $landingToneByDistance = [
        0 => 'is-tone-10',
        1 => 'is-tone-10',
        2 => 'is-tone-8',
        3 => 'is-tone-6',
        4 => 'is-tone-5',
    ];
    $landingSeasonalTimelineByOrder = [];

    for ($distance = 3; $distance >= 0; $distance--) {
        $seasonOrder = $landingCurrentSeasonOrder - $distance;
        $seasonIdx = $seasonOrder % 4;
        if ($seasonIdx < 0) {
            $seasonIdx += 4;
        }
        $seasonName = $landingSeasonNames[$seasonIdx] ?? $landingSeasonNames[0];
        $isCurrentSeason = ($distance === 0);

        $landingSeasonalTimelineByOrder[$seasonOrder] = [
            'title' => $isCurrentSeason ? ($seasonName . ' · 今') : $seasonName,
            'tone' => $landingToneByDistance[$distance] ?? 'is-tone-10',
            'isCurrent' => $isCurrentSeason,
            'items' => [],
        ];
    }

    foreach ($landingTimelineCandidates as $timelineItem) {
        $timelineCreated = (int) ($timelineItem['created'] ?? 0);
        if ($timelineCreated <= 0) {
            continue;
        }

        $timelineMeta = $landingReadSeasonMeta($timelineCreated);
        $seasonOrder = (int) ($timelineMeta['order'] ?? 0);
        if (!isset($landingSeasonalTimelineByOrder[$seasonOrder])) {
            continue;
        }

        if (count($landingSeasonalTimelineByOrder[$seasonOrder]['items']) >= $landingSeasonalPerBucketLimit) {
            continue;
        }

        $timelineTitle = trim((string) ($timelineItem['title'] ?? ''));
        $timelineUrl = trim((string) ($timelineItem['url'] ?? ''));
        if ($timelineTitle === '' || $timelineUrl === '') {
            continue;
        }

        $landingSeasonalTimelineByOrder[$seasonOrder]['items'][] = [
            'title' => $timelineTitle,
            'month' => (string) ($timelineMeta['monthLabel'] ?? ''),
            'url' => $timelineUrl,
        ];
    }

    $landingSeasonalTimeline = array_values($landingSeasonalTimelineByOrder);
    $landingSeasonalTimelineMobile = array_reverse($landingSeasonalTimeline);
    $landingSeasonalTotalCount = 0;
    foreach ($landingSeasonalTimeline as $seasonBlock) {
        $seasonItems = is_array($seasonBlock['items'] ?? null) ? $seasonBlock['items'] : [];
        $landingSeasonalTotalCount += count($seasonItems);
    }
}
?>

<main class="main" role="main">
    <?php if ($this->is('index')): ?>
        <section class="landing" aria-label="<?php _e('欢迎'); ?>">
            <div class="landing-main">
                <div class="landing-left">
                    <article class="landing-welcome" role="region" aria-label="<?php _e('欢迎词'); ?>"><?php echo escape($welcomeText); ?></article>
                </div>
                <div class="landing-right">
                    <div class="landing-avatar" aria-hidden="true">
                        <img
                            src="<?php echo escape($landingAvatarUrl); ?>"
                            alt="<?php echo escape($landingAvatarAlt); ?>"
                            width="256"
                            height="256"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                        >
                        <?php if ($landingPresenceEnabled): ?>
                            <span
                                class="landing-activity-badge"
                                data-presence-enabled="1"
                                data-presence-endpoint="<?php echo escape($landingPresenceEndpoint); ?>"
                                data-state="<?php echo escape($landingPresenceState); ?>"
                                data-icon="<?php echo escape($landingPresenceIcon); ?>"
                                data-has-icon="<?php echo $landingPresenceIcon !== '' ? '1' : '0'; ?>"
                                title="<?php echo escape($landingPresenceTitle); ?>"
                            >
                                <img
                                    class="landing-activity-icon"
                                    <?php if ($landingPresenceIcon !== ''): ?>
                                        src="<?php echo escape($landingPresenceIcon); ?>"
                                    <?php endif; ?>
                                    alt="<?php _e('应用图标'); ?>"
                                    loading="lazy"
                                    decoding="async"
                                >
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M3 12h4l2.2-4.2L13 18l2.8-6H21"></path>
                                </svg>
                            </span>
                        <?php endif; ?>
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

        <section class="landing-letter-memory" role="region" aria-label="<?php _e('来信与回忆'); ?>">
            <div class="landing-letter-memory-top">
                <div class="landing-letter-memory-col">
                    <h2 class="landing-letter-memory-title"><?php _e('来信'); ?></h2>
                    <div class="landing-letter-list">
                    <?php if (!empty($landingLetters)): ?>
                        <?php foreach ($landingLetters as $letterItem): ?>
                            <?php
                            $letterContentHtml = trim((string) ($letterItem['contentHtml'] ?? ''));
                            $letterAuthor = trim((string) ($letterItem['author'] ?? ''));
                            $letterTimeWord = trim((string) ($letterItem['timeWord'] ?? ''));
                            $letterDateTime = trim((string) ($letterItem['dateTime'] ?? ''));
                            if ($letterTimeWord === '' && $letterDateTime !== '') {
                                $letterTimeWord = $letterDateTime;
                            }
                            $letterPostTitle = trim((string) ($letterItem['postTitle'] ?? ''));
                            $letterPostUrl = trim((string) ($letterItem['postUrl'] ?? ''));
                            ?>
                            <article class="landing-letter-card landing-memory-card">
                                <div class="landing-memory-content comment-content" itemprop="commentText">
                                    <?php if ($letterContentHtml !== ''): ?>
                                        <?php echo $letterContentHtml; ?>
                                    <?php else: ?>
                                        <p><?php _e('暂无内容'); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="landing-letter-meta">
                                    <?php if ($letterPostUrl !== '' && $letterPostTitle !== ''): ?>
                                        <a class="landing-letter-post" href="<?php echo escape($letterPostUrl); ?>"><?php echo escape($letterPostTitle); ?></a>
                                    <?php elseif ($letterPostTitle !== ''): ?>
                                        <span class="landing-letter-post"><?php echo escape($letterPostTitle); ?></span>
                                    <?php endif; ?>
                                    <span class="landing-letter-author">
                                        — <?php echo escape($letterAuthor !== '' ? $letterAuthor : _t('匿名')); ?>
                                        <?php if ($letterTimeWord !== ''): ?> · <?php echo escape($letterTimeWord); ?><?php endif; ?>
                                    </span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="landing-letter-empty"><?php _e('暂无来信'); ?></p>
                    <?php endif; ?>
                    </div>
                </div>

                <div class="landing-letter-memory-col">
                    <h2 class="landing-letter-memory-title"><?php _e('回忆'); ?></h2>
                    <div class="landing-memory-list">
                    <?php if (!empty($landingMemories)): ?>
                        <?php foreach ($landingMemories as $memoryItem): ?>
                            <?php
                            $memoryContentHtml = trim((string) ($memoryItem['contentHtml'] ?? ''));
                            $memoryAuthor = trim((string) ($memoryItem['author'] ?? ''));
                            $memoryTimeWord = trim((string) ($memoryItem['timeWord'] ?? ''));
                            $memoryDateLabel = trim((string) ($memoryItem['dateLabel'] ?? ''));
                            if ($memoryTimeWord === '' && $memoryDateLabel !== '') {
                                $memoryTimeWord = $memoryDateLabel;
                            }
                            $memoryPostTitle = trim((string) ($memoryItem['postTitle'] ?? ''));
                            $memoryPostUrl = trim((string) ($memoryItem['postUrl'] ?? ''));
                            ?>
                            <article class="landing-letter-card landing-memory-card">
                                <div class="landing-memory-content comment-content" itemprop="commentText">
                                    <?php if ($memoryContentHtml !== ''): ?>
                                        <?php echo $memoryContentHtml; ?>
                                    <?php else: ?>
                                        <p><?php _e('暂无内容'); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="landing-letter-meta">
                                    <?php if ($memoryPostUrl !== '' && $memoryPostTitle !== ''): ?>
                                        <a class="landing-letter-post" href="<?php echo escape($memoryPostUrl); ?>"><?php echo escape($memoryPostTitle); ?></a>
                                    <?php elseif ($memoryPostTitle !== ''): ?>
                                        <span class="landing-letter-post"><?php echo escape($memoryPostTitle); ?></span>
                                    <?php endif; ?>
                                    <span class="landing-letter-author">
                                        — <?php echo escape($memoryAuthor !== '' ? $memoryAuthor : _t('匿名')); ?>
                                        <?php if ($memoryTimeWord !== ''): ?> · <?php echo escape($memoryTimeWord); ?><?php endif; ?>
                                    </span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="landing-memory-empty"><?php _e('回忆页还没有评论'); ?></p>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <section class="landing-seasonal" role="region" aria-label="<?php _e('笔耕不辍'); ?>">
            <div class="landing-seasonal-main">
                <h2 class="landing-letter-memory-title"><?php _e('笔耕不辍'); ?></h2>

                <div class="tl-scroll-desktop" aria-label="<?php _e('年度分季归档'); ?>">
                    <?php foreach ($landingSeasonalTimeline as $seasonIndex => $season): ?>
                        <?php
                        $seasonTitle = trim((string) ($season['title'] ?? ''));
                        $seasonToneClass = trim((string) ($season['tone'] ?? 'is-tone-10'));
                        $seasonIsCurrent = (bool) ($season['isCurrent'] ?? false);
                        $seasonItems = is_array($season['items'] ?? null) ? $season['items'] : [];
                        ?>
                        <?php if ($seasonIndex > 0): ?>
                            <div class="tl-scroll-divider" aria-hidden="true"></div>
                        <?php endif; ?>
                        <div class="tl-scroll-col <?php echo escape($seasonToneClass); ?>">
                            <div class="tl-scroll-season<?php echo $seasonIsCurrent ? ' is-current' : ''; ?>">
                                <div class="tl-scroll-season-title"><?php echo escape($seasonTitle); ?></div>
                                <div class="tl-scroll-links">
                                    <?php foreach ($seasonItems as $seasonItem): ?>
                                        <?php
                                        $seasonItemTitle = trim((string) ($seasonItem['title'] ?? ''));
                                        $seasonItemMonth = trim((string) ($seasonItem['month'] ?? ''));
                                        $seasonItemUrl = trim((string) ($seasonItem['url'] ?? ''));
                                        if ($seasonItemTitle === '' || $seasonItemUrl === '') {
                                            continue;
                                        }
                                        ?>
                                        <a class="tl-scroll-link" href="<?php echo escape($seasonItemUrl); ?>">
                                            <span class="tl-scroll-link-title"><?php echo escape($seasonItemTitle); ?></span>
                                            <?php if ($seasonItemMonth !== ''): ?>
                                                <span class="tl-scroll-link-month"><?php echo escape($seasonItemMonth); ?></span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="tl-scroll-mobile" aria-label="<?php _e('年度分季归档（移动端）'); ?>">
                    <?php foreach ($landingSeasonalTimelineMobile as $season): ?>
                        <?php
                        $seasonTitle = trim((string) ($season['title'] ?? ''));
                        $seasonToneClass = trim((string) ($season['tone'] ?? 'is-tone-10'));
                        $seasonIsCurrent = (bool) ($season['isCurrent'] ?? false);
                        $seasonItems = is_array($season['items'] ?? null) ? $season['items'] : [];
                        ?>
                        <div class="tl-scroll-mobile-col <?php echo escape($seasonToneClass); ?>">
                            <div class="tl-scroll-season<?php echo $seasonIsCurrent ? ' is-current' : ''; ?>">
                                <div class="tl-scroll-season-title"><?php echo escape($seasonTitle); ?></div>
                                <div class="tl-scroll-links">
                                    <?php foreach ($seasonItems as $seasonItem): ?>
                                        <?php
                                        $seasonItemTitle = trim((string) ($seasonItem['title'] ?? ''));
                                        $seasonItemMonth = trim((string) ($seasonItem['month'] ?? ''));
                                        $seasonItemUrl = trim((string) ($seasonItem['url'] ?? ''));
                                        if ($seasonItemTitle === '' || $seasonItemUrl === '') {
                                            continue;
                                        }
                                        ?>
                                        <a class="tl-scroll-link" href="<?php echo escape($seasonItemUrl); ?>">
                                            <span class="tl-scroll-link-title"><?php echo escape($seasonItemTitle); ?></span>
                                            <?php if ($seasonItemMonth !== ''): ?>
                                                <span class="tl-scroll-link-month"><?php echo escape($seasonItemMonth); ?></span>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="tl-scroll-footer">
                    <span class="tl-scroll-footer-left"><?php echo escape($landingSiteStatsLabel); ?></span>
                    <span class="tl-scroll-footer-right"><?php echo escape('本年 ' . (int) $landingSeasonalTotalCount . ' 篇'); ?></span>
                </div>
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
                                                    $activityAvatarAlt = $commentAuthor !== '' ? ($commentAuthor . '头像') : '评论者头像';
                                                    ?>
                                                    <?php if ($avatarUrl !== ''): ?>
                                                        <img class="activity-avatar" loading="lazy" src="<?php echo escape($avatarUrl); ?>" alt="<?php echo escape($activityAvatarAlt); ?>" width="16" height="16">
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
        $listHeading = hansjackArchiveRawTitle($this);
        if ($listHeading === '') {
            $listHeading = _t('文章列表');
        }
        ?>
        <section class="posts" aria-label="<?php _e('文章列表'); ?>">
            <div class="posts-layout">
                <div class="posts-main">
                    <h1 class="section-title"><?php echo escape($listHeading); ?></h1>
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
