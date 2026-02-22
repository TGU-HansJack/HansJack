<?php
/**
 * 回忆页（/memory）
 *
 * 说明：该模板会在你创建一个独立页面，且 slug 为 `memory` 时自动生效。
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');

// Helpers (local to this template to avoid polluting global scope).
$hjMemoryDateRange = static function (int $days): array {
    $days = max(1, (int) $days);
    $end = strtotime('today 23:59:59');
    $start = strtotime('-' . ($days - 1) . ' days', strtotime('today 00:00:00'));

    $labels = [];
    for ($i = 0; $i < $days; $i++) {
        $labels[] = date('Y-m-d', strtotime('+' . $i . ' days', $start));
    }

    return [$start, $end, $labels];
};

$hjMemoryCollectDescendants = static function (array $categories, int $rootMid): array {
    if ($rootMid <= 0) {
        return [];
    }

    $childrenMap = [];
    foreach ($categories as $cat) {
        $mid = (int) ($cat['mid'] ?? 0);
        $parent = (int) ($cat['parent'] ?? 0);
        if ($mid <= 0) {
            continue;
        }
        $childrenMap[$parent][] = $mid;
    }

    $out = [];
    $queue = [$rootMid];
    while (!empty($queue)) {
        $cur = array_shift($queue);
        if (isset($out[$cur])) {
            continue;
        }
        $out[$cur] = true;
        foreach (($childrenMap[$cur] ?? []) as $child) {
            $queue[] = (int) $child;
        }
    }

    return array_keys($out);
};

$hjMemoryCountContentsByDay = static function (array $mids, int $startTs, int $endTs): array {
    $mids = array_values(array_filter(array_map('intval', $mids), static fn($v) => $v > 0));
    if (empty($mids)) {
        return [];
    }

    $rows = [];
    try {
        $db = \Typecho\Db::get();
        $query = $db->select('table.contents.cid', 'table.contents.created')
            ->from('table.contents')
            ->join('table.relationships', 'table.contents.cid = table.relationships.cid')
            ->where('table.relationships.mid IN ?', $mids)
            ->where('table.contents.type = ?', 'post')
            ->where('table.contents.status = ?', 'publish')
            ->where('table.contents.created >= ?', $startTs)
            ->where('table.contents.created <= ?', $endTs);
        $rows = $db->fetchAll($query);
    } catch (\Throwable $e) {
        $rows = [];
    }

    // Deduplicate by cid (avoid double-counting when a post has multiple relationships within the set).
    $seen = [];
    foreach ($rows as $row) {
        $cid = (int) ($row['cid'] ?? 0);
        $created = (int) ($row['created'] ?? 0);
        if ($cid <= 0 || $created <= 0) {
            continue;
        }
        $seen[$cid] = $created;
    }

    $counts = [];
    foreach ($seen as $created) {
        $day = date('Y-m-d', (int) $created);
        $counts[$day] = (int) ($counts[$day] ?? 0) + 1;
    }

    return $counts;
};

$hjMemoryCountCommentsByDay = static function (int $startTs, int $endTs): array {
    $rows = [];
    try {
        $db = \Typecho\Db::get();
        $query = $db->select('table.comments.coid', 'table.comments.created')
            ->from('table.comments')
            ->where('table.comments.status = ?', 'approved')
            ->where('table.comments.type = ?', 'comment')
            ->where('table.comments.created >= ?', $startTs)
            ->where('table.comments.created <= ?', $endTs);
        $rows = $db->fetchAll($query);
    } catch (\Throwable $e) {
        $rows = [];
    }

    $counts = [];
    foreach ($rows as $row) {
        $created = (int) ($row['created'] ?? 0);
        if ($created <= 0) {
            continue;
        }
        $day = date('Y-m-d', $created);
        $counts[$day] = (int) ($counts[$day] ?? 0) + 1;
    }

    return $counts;
};

// Collect categories/tags.
$categories = [];
$postsRootMid = 0;
$notesRootMid = 0;
$postsChildren = [];
$tagsData = [];

try {
    $this->widget('Widget_Metas_Category_List')->to($hjCategoryList);
    if ($hjCategoryList && $hjCategoryList->have()) {
        while ($hjCategoryList->next()) {
            $categories[] = [
                'mid' => (int) ($hjCategoryList->mid ?? 0),
                'parent' => (int) ($hjCategoryList->parent ?? 0),
                'slug' => (string) ($hjCategoryList->slug ?? ''),
                'name' => (string) ($hjCategoryList->name ?? ''),
                'count' => (int) ($hjCategoryList->count ?? 0),
                'url' => (string) ($hjCategoryList->permalink ?? ''),
            ];
        }
    }
} catch (\Throwable $e) {
    $categories = [];
}

foreach ($categories as $cat) {
    if ($cat['slug'] === 'posts') {
        $postsRootMid = (int) $cat['mid'];
    }
    if ($cat['slug'] === 'notes') {
        $notesRootMid = (int) $cat['mid'];
    }
}

if ($postsRootMid > 0) {
    foreach ($categories as $cat) {
        if ((int) $cat['parent'] !== $postsRootMid) {
            continue;
        }
        $name = (string) ($cat['name'] ?? '');
        if ($name === '') {
            continue;
        }
        $postsChildren[] = [
            'name' => $name,
            'value' => max(0, (int) ($cat['count'] ?? 0)),
        ];
    }
}

try {
    $this->widget('Widget_Metas_Tag_Cloud', 'ignoreZeroCount=1&limit=9999')->to($hjTags);
    if ($hjTags && $hjTags->have()) {
        while ($hjTags->next()) {
            $name = (string) ($hjTags->name ?? '');
            $count = (int) ($hjTags->count ?? 0);
            if ($name === '' || $count <= 0) {
                continue;
            }
            $tagsData[] = ['name' => $name, 'value' => $count];
        }
    }
} catch (\Throwable $e) {
    $tagsData = [];
}

// Build time-series data.
[$publishStart, $publishEnd, $publishLabels] = $hjMemoryDateRange(30);
[$commentStart, $commentEnd, $commentLabels] = $hjMemoryDateRange(7);

$postsMids = $hjMemoryCollectDescendants($categories, $postsRootMid);
$notesMids = $hjMemoryCollectDescendants($categories, $notesRootMid);

$postsByDay = $hjMemoryCountContentsByDay($postsMids, $publishStart, $publishEnd);
$notesByDay = $hjMemoryCountContentsByDay($notesMids, $publishStart, $publishEnd);
$commentsByDay = $hjMemoryCountCommentsByDay($commentStart, $commentEnd);

$publishPostsSeries = [];
$publishNotesSeries = [];
foreach ($publishLabels as $day) {
    $publishPostsSeries[] = (int) ($postsByDay[$day] ?? 0);
    $publishNotesSeries[] = (int) ($notesByDay[$day] ?? 0);
}

$commentsSeries = [];
foreach ($commentLabels as $day) {
    $commentsSeries[] = (int) ($commentsByDay[$day] ?? 0);
}

$hjMemoryPayload = [
    'publish' => [
        'labels' => $publishLabels,
        'posts' => $publishPostsSeries,
        'notes' => $publishNotesSeries,
    ],
    'categories' => [
        'postsChildren' => $postsChildren,
    ],
    'tags' => [
        'items' => $tagsData,
    ],
    'comments' => [
        'labels' => $commentLabels,
        'counts' => $commentsSeries,
    ],
];
?>

<main class="hj-main" role="main">
    <section class="hj-memory" aria-label="<?php _e('回忆'); ?>">
        <header class="hj-memory-header">
            <h1 class="hj-memory-title"><?php _e('回忆'); ?></h1>
        </header>

        <div class="hj-memory-grid" aria-label="<?php _e('数据图表'); ?>">
            <section class="hj-memory-block" aria-label="<?php _e('发布趋势'); ?>">
                <h2 class="hj-memory-block-title"><?php _e('发布趋势'); ?></h2>
                <div class="hj-memory-chart" id="hj-memory-chart-publish"></div>
            </section>

            <section class="hj-memory-block" aria-label="<?php _e('博文子分类占比'); ?>">
                <h2 class="hj-memory-block-title"><?php _e('博文子分类占比'); ?></h2>
                <div class="hj-memory-chart" id="hj-memory-chart-categories"></div>
            </section>

            <section class="hj-memory-block hj-memory-block-wide" aria-label="<?php _e('标签占比'); ?>">
                <h2 class="hj-memory-block-title"><?php _e('标签占比'); ?></h2>
                <div class="hj-memory-chart hj-memory-chart-tall" id="hj-memory-chart-tags"></div>
            </section>

            <section class="hj-memory-block" aria-label="<?php _e('最近 7 天评论'); ?>">
                <h2 class="hj-memory-block-title"><?php _e('最近 7 天评论'); ?></h2>
                <div class="hj-memory-chart" id="hj-memory-chart-comments"></div>
            </section>
        </div>
    </section>
</main>

<script src="<?php $this->options->themeUrl('assets/vendor/echarts/echarts.min.js'); ?>"></script>
<script>
    (function () {
        var payload = <?php echo json_encode($hjMemoryPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;

        function cssVar(name) {
            try {
                return window.getComputedStyle(document.documentElement).getPropertyValue(name).trim();
            } catch (e) {
                return "";
            }
        }

        function isDark() {
            return document.documentElement.classList.contains("hj-theme-dark");
        }

        function getThemeColors() {
            var dark = isDark();
            var text = dark ? cssVar("--hj-night-text") : cssVar("--hj-day-text");
            var muted = dark ? cssVar("--hj-muted-night") : cssVar("--hj-muted-day");
            var line = dark ? cssVar("--hj-line-night") : cssVar("--hj-line-day");
            var primary = cssVar("--hj-nav-block-bg") || (dark ? "#dddddb" : "#2a2a28");
            var secondary = dark ? cssVar("--hj-night-gray") : cssVar("--hj-day-gray");

            return {
                text: text || (dark ? "#dddddb" : "#2a2a28"),
                muted: muted || (dark ? "#a5a5a5" : "#757575"),
                line: line || "rgba(0,0,0,0.2)",
                primary: primary,
                secondary: secondary || (dark ? "#a5a5a5" : "#757575"),
            };
        }

        function initChart(el, buildOption) {
            if (!el) {
                return null;
            }
            if (!window.echarts) {
                el.textContent = "ECharts 未加载";
                return null;
            }

            var chart = window.echarts.init(el);
            function render() {
                var colors = getThemeColors();
                var option = buildOption(colors);
                chart.setOption(option, true);
            }

            render();
            return { chart: chart, render: render };
        }

        function emptyOption(text, colors) {
            return {
                backgroundColor: "transparent",
                textStyle: { color: colors.text, fontFamily: cssVar("--hj-font-ui") || "serif" },
                graphic: [{
                    type: "text",
                    left: "center",
                    top: "middle",
                    style: {
                        text: text,
                        fill: colors.muted,
                        fontSize: 12
                    }
                }]
            };
        }

        var instances = [];

        instances.push(initChart(document.getElementById("hj-memory-chart-publish"), function (colors) {
            var labels = (payload.publish && payload.publish.labels) ? payload.publish.labels : [];
            var posts = (payload.publish && payload.publish.posts) ? payload.publish.posts : [];
            var notes = (payload.publish && payload.publish.notes) ? payload.publish.notes : [];

            if (!labels.length) {
                return emptyOption("暂无数据", colors);
            }

            return {
                backgroundColor: "transparent",
                color: [colors.primary, colors.secondary],
                textStyle: { color: colors.text, fontFamily: cssVar("--hj-font-ui") || "serif" },
                grid: { left: 40, right: 18, top: 26, bottom: 32 },
                tooltip: { trigger: "axis" },
                legend: { top: 0, left: 0, textStyle: { color: colors.muted } },
                xAxis: {
                    type: "category",
                    data: labels,
                    axisLine: { lineStyle: { color: colors.line } },
                    axisTick: { show: false },
                    axisLabel: {
                        color: colors.muted,
                        formatter: function (v) { return String(v).slice(5); }
                    }
                },
                yAxis: {
                    type: "value",
                    axisLine: { show: false },
                    axisTick: { show: false },
                    axisLabel: { color: colors.muted },
                    splitLine: { lineStyle: { color: colors.line } }
                },
                series: [
                    { name: "文章", type: "line", smooth: true, data: posts, symbolSize: 4, lineStyle: { width: 2 } },
                    { name: "手记", type: "line", smooth: true, data: notes, symbolSize: 4, lineStyle: { width: 2 } }
                ]
            };
        }));

        instances.push(initChart(document.getElementById("hj-memory-chart-categories"), function (colors) {
            var items = payload.categories && payload.categories.postsChildren ? payload.categories.postsChildren : [];
            var has = items && items.some(function (it) { return (it && it.value) > 0; });
            if (!items.length || !has) {
                return emptyOption("暂无分类数据", colors);
            }

            return {
                backgroundColor: "transparent",
                color: [colors.primary, colors.secondary, "#888", "#aaa", "#666"],
                textStyle: { color: colors.text, fontFamily: cssVar("--hj-font-ui") || "serif" },
                tooltip: { trigger: "item" },
                legend: { show: false },
                series: [{
                    type: "pie",
                    radius: ["35%", "72%"],
                    center: ["50%", "56%"],
                    avoidLabelOverlap: true,
                    itemStyle: { borderColor: "transparent", borderWidth: 0 },
                    label: { color: colors.text, formatter: "{b}\n{d}%" },
                    labelLine: { lineStyle: { color: colors.line } },
                    data: items
                }]
            };
        }));

        instances.push(initChart(document.getElementById("hj-memory-chart-tags"), function (colors) {
            var items = payload.tags && payload.tags.items ? payload.tags.items : [];
            if (!items.length) {
                return emptyOption("暂无标签数据", colors);
            }

            var min = Infinity;
            var max = 0;
            for (var i = 0; i < items.length; i++) {
                var v = items[i] && items[i].value ? Number(items[i].value) : 0;
                if (!isFinite(v)) {
                    continue;
                }
                if (v < min) {
                    min = v;
                }
                if (v > max) {
                    max = v;
                }
            }
            if (!isFinite(min)) {
                min = 0;
            }
            if (!isFinite(max) || max <= min) {
                max = min + 1;
            }

            var dark = isDark();
            var low = dark ? "rgba(221,221,219,0.06)" : "rgba(42,42,40,0.07)";
            var high = dark ? "rgba(221,221,219,0.24)" : "rgba(42,42,40,0.20)";

            return {
                backgroundColor: "transparent",
                textStyle: { color: colors.text, fontFamily: cssVar("--hj-font-ui") || "serif" },
                visualMap: {
                    show: false,
                    min: min,
                    max: max,
                    inRange: { color: [low, high] }
                },
                tooltip: {
                    formatter: function (info) {
                        var name = (info && info.name) ? info.name : "";
                        var value = (info && info.value) ? info.value : 0;
                        return name + " : " + value;
                    }
                },
                series: [{
                    type: "treemap",
                    roam: false,
                    nodeClick: false,
                    breadcrumb: { show: false },
                    label: { show: true, color: colors.text, fontSize: 12 },
                    upperLabel: { show: false },
                    itemStyle: {
                        borderColor: colors.line,
                        borderWidth: 1,
                        gapWidth: 1
                    },
                    levels: [{
                        itemStyle: { borderColor: colors.line, borderWidth: 1, gapWidth: 1 },
                    }],
                    data: items
                }]
            };
        }));

        instances.push(initChart(document.getElementById("hj-memory-chart-comments"), function (colors) {
            var labels = payload.comments && payload.comments.labels ? payload.comments.labels : [];
            var counts = payload.comments && payload.comments.counts ? payload.comments.counts : [];
            if (!labels.length) {
                return emptyOption("暂无数据", colors);
            }

            return {
                backgroundColor: "transparent",
                color: [colors.primary],
                textStyle: { color: colors.text, fontFamily: cssVar("--hj-font-ui") || "serif" },
                grid: { left: 40, right: 18, top: 18, bottom: 32 },
                tooltip: { trigger: "axis" },
                xAxis: {
                    type: "category",
                    data: labels,
                    axisLine: { lineStyle: { color: colors.line } },
                    axisTick: { show: false },
                    axisLabel: { color: colors.muted, formatter: function (v) { return String(v).slice(5); } }
                },
                yAxis: {
                    type: "value",
                    axisLine: { show: false },
                    axisTick: { show: false },
                    axisLabel: { color: colors.muted },
                    splitLine: { lineStyle: { color: colors.line } }
                },
                series: [{
                    type: "bar",
                    data: counts,
                    barMaxWidth: 18
                }]
            };
        }));

        function safeResize() {
            instances.forEach(function (inst) {
                if (inst && inst.chart) {
                    inst.chart.resize();
                }
            });
        }

        function rerender() {
            instances.forEach(function (inst) {
                if (inst && inst.render) {
                    inst.render();
                }
            });
        }

        window.addEventListener("resize", function () {
            safeResize();
        });

        // Re-render charts when theme class changes.
        try {
            var obs = new MutationObserver(function (muts) {
                for (var i = 0; i < muts.length; i++) {
                    if (muts[i].attributeName === "class") {
                        rerender();
                        safeResize();
                        break;
                    }
                }
            });
            obs.observe(document.documentElement, { attributes: true });
        } catch (e) {
            // Ignore.
        }
    })();
</script>

<?php $this->need('footer.php'); ?>
