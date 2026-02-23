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
$blogSlug = 'posts';
$memoSlug = 'notes';
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
                </div>
                <div class="hj-landing-right">
                    <div class="hj-landing-avatar" aria-hidden="true">
                        <img src="<?php echo hansJackEscape($faviconUrl); ?>" alt="">
                    </div>
                </div>
            </div>

            <div class="hj-landing-bottom" role="group" aria-label="<?php _e('名言'); ?>">
                <p class="hj-hitokoto-text" aria-live="polite"><?php _e('正在获取名言...'); ?></p>
                <button class="hj-scroll-down" type="button" aria-label="<?php _e('向下滚动'); ?>" title="<?php _e('向下滚动'); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down-icon lucide-chevron-down" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </button>
            </div>
        </section>

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
                                <li class="hj-posts-item">
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
