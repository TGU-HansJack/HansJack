<?php
/**
 * 文章列表页（/post）
 *
 * 说明：该模板会在你创建一个独立页面，且 slug 为 `post` 时自动生效。
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');

$posts = null;
$categories = null;
$tags = null;
$postsCategorySlug = 'posts';
$postsPageSize = 10;
$hjListPage = 1;

try {
    $hjListPage = max(1, (int) $this->request->filter('int')->get('page', 1));
} catch (\Throwable $e) {
    $hjListPage = 1;
}

try {
    $this->widget(
        'Widget_Archive@hj_posts',
        'pageSize=' . $postsPageSize . '&type=category',
        'slug=' . urlencode($postsCategorySlug) . '&page=' . $hjListPage,
        false
    )->to($posts);
} catch (\Throwable $e) {
    $posts = null;
}

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

<main class="hj-main" role="main">
    <section class="hj-posts" aria-label="<?php _e('文章列表'); ?>">
        <div class="hj-posts-layout">
            <div class="hj-posts-main">
                <ul class="hj-posts-list" aria-label="<?php _e('文章'); ?>">
                    <?php if ($posts && $posts->have()): ?>
                        <?php while ($posts->next()): ?>
                            <li class="hj-posts-item">
                                <div class="hj-posts-item-left">
                                    <a class="hj-posts-title" href="<?php echo hansJackEscape($posts->permalink); ?>">
                                        <?php echo hansJackEscape($posts->title); ?>
                                    </a>
                                    <time class="hj-posts-date" datetime="<?php $posts->date('c'); ?>">
                                        <?php $posts->date('Y/m/d-H:i:s'); ?>
                                    </time>
                                </div>

                                <div class="hj-posts-item-right" aria-label="<?php _e('标签'); ?>">
                                    <?php
                                    $postTags = [];
                                    try {
                                        $postTags = is_array($posts->tags) ? $posts->tags : [];
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
                    <?php else: ?>
                        <li class="hj-posts-empty"><?php _e('暂无文章'); ?></li>
                    <?php endif; ?>
                </ul>
                <?php if ($posts): ?>
                    <?php hansJackRenderPager($posts, $hjPagerPrevIcon, $hjPagerNextIcon, 2, '...', $postsCategorySlug); ?>
                <?php endif; ?>
            </div>

            <aside class="hj-posts-aside" aria-label="<?php _e('侧栏'); ?>">
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
            </aside>
        </div>
    </section>
</main>

<?php $this->need('footer.php'); ?>
