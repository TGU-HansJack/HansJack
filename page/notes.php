<?php
/**
 * 手记列表页（/notes）
 *
 * 说明：该模板会在你创建一个独立页面，且 slug 为 `notes` 时自动生效。
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');

$notesCategorySlug = 'notes';
$posts = null;
$categories = null;
$tags = null;
$postsPageSize = 10;
$hjListPage = 1;

try {
    $hjListPage = max(1, (int) $this->request->filter('int')->get('page', 1));
} catch (\Throwable $e) {
    $hjListPage = 1;
}

try {
    $this->widget(
        'Widget_Archive@hj_notes',
        'pageSize=' . $postsPageSize . '&type=category',
        'slug=' . urlencode($notesCategorySlug) . '&page=' . $hjListPage,
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

$hjAllCategories = [];
$hjCategoryByMid = [];
$hjNotesRootMid = 0;
$hjNotesSeriesLinks = [];

try {
    if ($categories && $categories->have()) {
        while ($categories->next()) {
            $mid = (int) ($categories->mid ?? 0);
            if ($mid <= 0) {
                continue;
            }
            $info = [
                'mid' => $mid,
                'parent' => (int) ($categories->parent ?? 0),
                'slug' => (string) ($categories->slug ?? ''),
                'name' => (string) ($categories->name ?? ''),
                'url' => (string) ($categories->permalink ?? ''),
            ];
            $hjAllCategories[] = $info;
            $hjCategoryByMid[$mid] = $info;
        }
    }
} catch (\Throwable $e) {
    $hjAllCategories = [];
    $hjCategoryByMid = [];
}

foreach ($hjAllCategories as $cat) {
    if ($cat['slug'] === $notesCategorySlug) {
        $hjNotesRootMid = (int) $cat['mid'];
        break;
    }
}

if ($hjNotesRootMid) {
    foreach ($hjAllCategories as $cat) {
        if ((int) $cat['parent'] !== $hjNotesRootMid) {
            continue;
        }
        if ($cat['name'] === '' || $cat['url'] === '') {
            continue;
        }
        $hjNotesSeriesLinks[] = $cat;
    }
}
?>

<main class="hj-main" role="main">
    <section class="hj-posts" aria-label="<?php _e('手记列表'); ?>">
        <div class="hj-posts-layout">
            <div class="hj-posts-main">
                <ul class="hj-posts-list" aria-label="<?php _e('手记'); ?>">
                    <?php if ($posts && $posts->have()): ?>
                        <?php while ($posts->next()): ?>
                            <li class="hj-posts-item">
                                <div class="hj-posts-item-left">
                                    <?php
                                    $hjColumnName = '';
                                    $hjPostCategories = [];
                                    try {
                                        $hjPostCategories = is_array($posts->categories) ? $posts->categories : [];
                                    } catch (\Throwable $e) {
                                        $hjPostCategories = [];
                                    }

                                    if ($hjNotesRootMid && !empty($hjPostCategories)) {
                                        foreach ($hjPostCategories as $cat) {
                                            $mid = (int) ($cat['mid'] ?? 0);
                                            if ($mid > 0 && isset($hjCategoryByMid[$mid])) {
                                                $info = $hjCategoryByMid[$mid];
                                                if ((int) ($info['parent'] ?? 0) === $hjNotesRootMid) {
                                                    $hjColumnName = (string) ($info['name'] ?? '');
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="hj-posts-title-row">
                                        <?php if ($hjColumnName !== ''): ?>
                                            <span class="hj-posts-title-prefix"><?php echo hansJackEscape($hjColumnName); ?></span>
                                            <span class="hj-posts-title-sep" aria-hidden="true"> | </span>
                                        <?php endif; ?>
                                        <a class="hj-posts-title" href="<?php echo hansJackEscape($posts->permalink); ?>"><?php echo hansJackEscape($posts->title); ?></a>
                                    </div>
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
                        <li class="hj-posts-empty"><?php _e('暂无手记'); ?></li>
                    <?php endif; ?>
                </ul>
                <?php if ($posts): ?>
                    <?php hansJackRenderPager($posts, $hjPagerPrevIcon, $hjPagerNextIcon, 2, '...', $notesCategorySlug); ?>
                <?php endif; ?>
            </div>

            <aside class="hj-posts-aside" aria-label="<?php _e('侧栏'); ?>">
                <div class="hj-posts-block" aria-label="<?php _e('专栏'); ?>">
                    <h2 class="hj-posts-block-title"><?php _e('专栏'); ?></h2>
                    <div class="hj-posts-links">
                        <?php
                        if (!empty($hjNotesSeriesLinks)) {
                            foreach ($hjNotesSeriesLinks as $cat) {
                                echo '<a class="hj-posts-link" href="' . hansJackEscape($cat['url']) . '">' . hansJackEscape($cat['name']) . '</a>';
                            }
                        } else {
                            echo '<span class="hj-posts-empty">' . _t('暂无专栏') . '</span>';
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
