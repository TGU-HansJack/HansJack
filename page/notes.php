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
$postsPageSize = 15;
$listPage = 1;

try {
    $listPage = max(1, (int) $this->request->filter('int')->get('page', 1));
} catch (\Throwable $e) {
    $listPage = 1;
}

try {
    $this->widget(
        'Widget_Archive@notes',
        'pageSize=' . $postsPageSize . '&type=category',
        'slug=' . urlencode($notesCategorySlug) . '&page=' . $listPage,
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

$allCategories = [];
$categoryByMid = [];
$notesRootMid = 0;
$notesSeriesLinks = [];

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
            $allCategories[] = $info;
            $categoryByMid[$mid] = $info;
        }
    }
} catch (\Throwable $e) {
    $allCategories = [];
    $categoryByMid = [];
}

foreach ($allCategories as $cat) {
    if ($cat['slug'] === $notesCategorySlug) {
        $notesRootMid = (int) $cat['mid'];
        break;
    }
}

if ($notesRootMid) {
    foreach ($allCategories as $cat) {
        if ((int) $cat['parent'] !== $notesRootMid) {
            continue;
        }
        if ($cat['name'] === '' || $cat['url'] === '') {
            continue;
        }
        $notesSeriesLinks[] = $cat;
    }
}
?>

<main class="main" role="main">
    <section class="posts" aria-label="<?php _e('手记列表'); ?>">
        <div class="posts-layout">
            <div class="posts-main">
                    <ul class="posts-list" aria-label="<?php _e('手记'); ?>">
                    <?php if ($posts && $posts->have()): ?>
                        <?php while ($posts->next()): ?>
                            <?php
                            $postCreated = 0;
                            $postModified = 0;
                            try {
                                $postCreated = (int) ($posts->created ?? 0);
                            } catch (\Throwable $e) {
                                $postCreated = 0;
                            }
                            try {
                                $postModified = (int) ($posts->modified ?? 0);
                            } catch (\Throwable $e) {
                                $postModified = 0;
                            }

                            $postExcerpt = '';
                            ob_start();
                            try {
                                $posts->excerpt(100, '...');
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
                                    <?php
                                    $columnName = '';
                                    $postCategories = [];
                                    try {
                                        $postCategories = is_array($posts->categories) ? $posts->categories : [];
                                    } catch (\Throwable $e) {
                                        $postCategories = [];
                                    }

                                    if ($notesRootMid && !empty($postCategories)) {
                                        foreach ($postCategories as $cat) {
                                            $mid = (int) ($cat['mid'] ?? 0);
                                            if ($mid > 0 && isset($categoryByMid[$mid])) {
                                                $info = $categoryByMid[$mid];
                                                if ((int) ($info['parent'] ?? 0) === $notesRootMid) {
                                                    $columnName = (string) ($info['name'] ?? '');
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                    <div class="posts-title-row">
                                        <?php if ($columnName !== ''): ?>
                                            <span class="posts-title-prefix"><?php echo escape($columnName); ?></span>
                                            <span class="posts-title-sep" aria-hidden="true"> | </span>
                                        <?php endif; ?>
                                        <a class="posts-title" href="<?php echo escape($posts->permalink); ?>"><?php echo escape($posts->title); ?></a>
                                    </div>
                                    <time class="posts-date" datetime="<?php $posts->date('c'); ?>">
                                        <?php $posts->date('Y/m/d-H:i:s'); ?>
                                    </time>
                                </div>

                                <div class="posts-item-right" aria-label="<?php _e('标签'); ?>">
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
                                            echo '<a class="posts-tag" href="' . escape($url) . '">#' . escape($name) . '</a>';
                                        }
                                    }
                                    ?>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="posts-empty"><?php _e('暂无手记'); ?></li>
                    <?php endif; ?>
                </ul>
                <?php if ($posts): ?>
                    <?php renderPager($posts, $pagerPrevIcon, $pagerNextIcon, 2, '...', $notesCategorySlug); ?>
                <?php endif; ?>
            </div>

            <aside class="posts-aside" aria-label="<?php _e('侧栏'); ?>">
                <div class="posts-block" aria-label="<?php _e('专栏'); ?>">
                    <h2 class="posts-block-title"><?php _e('专栏'); ?></h2>
                    <div class="posts-links">
                        <?php
                        if (!empty($notesSeriesLinks)) {
                            foreach ($notesSeriesLinks as $cat) {
                                echo '<a class="posts-link" href="' . escape($cat['url']) . '">' . escape($cat['name']) . '</a>';
                            }
                        } else {
                            echo '<span class="posts-empty">' . _t('暂无专栏') . '</span>';
                        }
                        ?>
                    </div>
                </div>

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
            </aside>
        </div>
    </section>
</main>

<?php $this->need('footer.php'); ?>
