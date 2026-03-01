<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');
?>

<main class="hj-main" role="main">
    <article class="hj-article" data-hj-post-cid="<?php echo (int) ($this->cid ?? 0); ?>">
        <header class="hj-article-header">
            <h1 class="hj-article-title"><?php $this->title(); ?></h1>
            <?php
            // Word count (approx): strip HTML and whitespace, then count UTF-8 characters.
            $hjWordCount = 0;
            $hjTokenWordCount = 0;
            $hjCharCount = 0;
            $hjReadMinutes = 1;
            $hjWordCountTip = '';
            try {
                $hjContentHtml = (string) ($this->content ?? '');
                $hjPlain = html_entity_decode(strip_tags($hjContentHtml), ENT_QUOTES, 'UTF-8');
                $hjPlainNoSpace = (string) preg_replace('/\\s+/u', '', $hjPlain);
                $hjCharCount = function_exists('mb_strlen') ? mb_strlen($hjPlainNoSpace, 'UTF-8') : strlen($hjPlainNoSpace);
                $hjWordCount = (int) $hjCharCount;

                $hjWordMatches = [];
                $hjMatched = preg_match_all('/[\x{4E00}-\x{9FFF}]|[A-Za-z0-9]+(?:[\'’\\-][A-Za-z0-9]+)*/u', $hjPlain, $hjWordMatches);
                $hjTokenWordCount = $hjMatched === false ? 0 : (int) $hjMatched;
                if ($hjTokenWordCount <= 0 && $hjCharCount > 0) {
                    $hjTokenWordCount = (int) $hjCharCount;
                }

                $hjReadByWord = $hjTokenWordCount > 0 ? ((float) $hjTokenWordCount / 220.0) : 0.0;
                $hjReadByChar = $hjCharCount > 0 ? ((float) $hjCharCount / 300.0) : 0.0;
                $hjReadMinutes = max(1, (int) ceil(max($hjReadByWord, $hjReadByChar)));
                $hjWordCountTip = sprintf(
                    _t('%1$d个词 %2$d个字符 阅读预计 %3$d分钟'),
                    (int) $hjTokenWordCount,
                    (int) $hjCharCount,
                    (int) $hjReadMinutes
                );
            } catch (\Throwable $e) {
                $hjWordCount = 0;
                $hjTokenWordCount = 0;
                $hjCharCount = 0;
                $hjReadMinutes = 1;
                $hjWordCountTip = sprintf(_t('%1$d个词 %2$d个字符 阅读预计 %3$d分钟'), 0, 0, 1);
            }

            $hjTags = [];
            try {
                $hjTags = is_array($this->tags) ? $this->tags : [];
            } catch (\Throwable $e) {
                $hjTags = [];
            }

            // Only show the immediate subcategory under the "posts" or "notes" root categories.
            $hjChildCategory = null;
            $hjPostCategories = [];
            try {
                $hjPostCategories = is_array($this->categories) ? $this->categories : [];
            } catch (\Throwable $e) {
                $hjPostCategories = [];
            }

            $hjPostsRootMid = 0;
            $hjNotesRootMid = 0;
            $hjCategoryByMid = [];
            try {
                $this->widget('Widget_Metas_Category_List')->to($hjCategoryList);
                if ($hjCategoryList && $hjCategoryList->have()) {
                    while ($hjCategoryList->next()) {
                        $mid = (int) ($hjCategoryList->mid ?? 0);
                        if ($mid <= 0) {
                            continue;
                        }
                        $parent = (int) ($hjCategoryList->parent ?? 0);
                        $slug = (string) ($hjCategoryList->slug ?? '');
                        $hjCategoryByMid[$mid] = [
                            'mid' => $mid,
                            'parent' => $parent,
                            'slug' => $slug,
                            'name' => (string) ($hjCategoryList->name ?? ''),
                            'url' => (string) ($hjCategoryList->permalink ?? ''),
                        ];

                        if ($slug === 'posts') {
                            $hjPostsRootMid = $mid;
                        } elseif ($slug === 'notes') {
                            $hjNotesRootMid = $mid;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $hjPostsRootMid = 0;
                $hjNotesRootMid = 0;
                $hjCategoryByMid = [];
            }

            if (!empty($hjPostCategories) && !empty($hjCategoryByMid) && ($hjPostsRootMid || $hjNotesRootMid)) {
                foreach ($hjPostCategories as $cat) {
                    $mid = (int) ($cat['mid'] ?? 0);
                    if ($mid <= 0 || !isset($hjCategoryByMid[$mid])) {
                        continue;
                    }
                    $info = $hjCategoryByMid[$mid];
                    $parent = (int) ($info['parent'] ?? 0);
                    if (
                        ($hjPostsRootMid && $parent === $hjPostsRootMid) ||
                        ($hjNotesRootMid && $parent === $hjNotesRootMid)
                    ) {
                        if ($info['name'] !== '' && $info['url'] !== '') {
                            $hjChildCategory = $info;
                            break;
                        }
                    }
                }
            }
            ?>
            <p class="hj-article-meta">
                <span class="hj-article-meta-item">
                    <span class="hj-article-meta-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                    </span>
                    <time datetime="<?php $this->date('c'); ?>"><?php $this->date('Y-m-d'); ?></time>
                </span>
                <?php if (!empty($hjChildCategory)): ?>
                    <span class="hj-article-meta-item hj-article-meta-category">
                        <span class="hj-article-meta-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83z"/><path d="M2 12a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 12"/><path d="M2 17a1 1 0 0 0 .58.91l8.6 3.91a2 2 0 0 0 1.65 0l8.58-3.9A1 1 0 0 0 22 17"/></svg>
                        </span>
                        <a class="hj-article-category" href="<?php echo hansJackEscape((string) $hjChildCategory['url']); ?>"><?php echo hansJackEscape((string) $hjChildCategory['name']); ?></a>
                    </span>
                <?php endif; ?>
                <span class="hj-article-meta-item hj-article-meta-tags">
                    <span class="hj-article-meta-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="9" y2="9"/><line x1="4" x2="20" y1="15" y2="15"/><line x1="10" x2="8" y1="3" y2="21"/><line x1="16" x2="14" y1="3" y2="21"/></svg>
                    </span>
                    <?php if (!empty($hjTags)): ?>
                        <span class="hj-article-meta-tags-list">
                            <?php $hjTagIndex = 0; ?>
                            <?php foreach ($hjTags as $tag): ?>
                                <?php
                                $name = (string) ($tag['name'] ?? '');
                                $url = (string) ($tag['permalink'] ?? '');
                                if ($name === '' || $url === '') {
                                    continue;
                                }
                                if ($hjTagIndex > 0) {
                                    echo '<span class="hj-article-tag-sep" aria-hidden="true">; </span>';
                                }
                                $hjTagIndex += 1;
                                ?>
                                <a class="hj-article-tag" href="<?php echo hansJackEscape($url); ?>"><?php echo hansJackEscape($name); ?></a>
                            <?php endforeach; ?>
                        </span>
                    <?php else: ?>
                        <span class="hj-article-meta-empty"><?php _e('无标签'); ?></span>
                    <?php endif; ?>
                </span>
                <span
                    class="hj-article-meta-item hj-article-meta-wordcount"
                    data-hj-meta-tip="<?php echo hansJackEscape($hjWordCountTip); ?>"
                    title="<?php echo hansJackEscape($hjWordCountTip); ?>"
                    tabindex="0"
                    aria-label="<?php echo hansJackEscape($hjWordCountTip); ?>"
                >
                    <span class="hj-article-meta-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 4v16"/><path d="M17 4v16"/><path d="M19 4H9.5a4.5 4.5 0 0 0 0 9H13"/></svg>
                    </span>
                    <span class="hj-article-meta-text"><?php echo (int) $hjWordCount; ?> <?php _e('字'); ?></span>
                </span>
            </p>
        </header>
        <div class="hj-article-layout" data-hj-article-layout>
            <div class="hj-article-content">
                <?php hansJackEchoArchiveContent($this); ?>
            </div>
            <aside class="hj-article-aside" aria-label="<?php _e('目录'); ?>">
                <div class="hj-article-toc">
                    <div class="hj-article-toc-header">
                        <h2 class="hj-article-toc-title"><?php _e('目录'); ?></h2>
                        <button class="hj-article-toc-close" type="button" aria-label="<?php _e('关闭目录'); ?>" title="<?php _e('关闭目录'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                    <nav class="hj-article-toc-nav" aria-label="<?php _e('文章目录'); ?>"></nav>
                </div>
            </aside>
        </div>

        <script>
            (function () {
                var layout = document.querySelector("[data-hj-article-layout]");
                if (!layout) {
                    return;
                }

                var content = layout.querySelector(".hj-article-content");
                var tocNav = layout.querySelector(".hj-article-toc-nav");
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
                rootList.className = "hj-article-toc-list";

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
                        sub.className = "hj-article-toc-sublist";
                        parentLi.appendChild(sub);
                        stack.push({ level: currentFrame().level + 1, list: sub });
                    }

                    while (level < currentFrame().level) {
                        stack.pop();
                    }

                    var li = document.createElement("li");
                    var a = document.createElement("a");
                    li.className = "hj-article-toc-item";
                    a.className = "hj-article-toc-link";
                    a.href = "#" + h.id;
                    a.textContent = (h.textContent || "").trim();
                    li.appendChild(a);
                    currentFrame().list.appendChild(li);
                });

                tocNav.textContent = "";
                tocNav.appendChild(rootList);
                layout.classList.add("is-has-toc");

                // Scrollspy: only bold the current section in TOC while scrolling.
                var tocLinks = Array.prototype.slice.call(tocNav.querySelectorAll("a.hj-article-toc-link"));
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
                    if (!target.matches("a.hj-article-toc-link")) {
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

        <?php $this->need('comments.php'); ?>
    </article>
</main>

<?php $this->need('footer.php'); ?>
