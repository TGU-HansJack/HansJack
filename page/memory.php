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

$hjMemoryPayload = [
    'categories' => [
        'postsChildren' => $postsChildren,
    ],
    'tags' => [
        'items' => $tagsData,
    ],
];
?>

<main class="hj-main" role="main">
    <section class="hj-memory" aria-label="<?php _e('回忆'); ?>">
        <div class="hj-memory-grid" aria-label="<?php _e('数据图表'); ?>">
            <section class="hj-memory-block" aria-label="<?php _e('博文子分类占比'); ?>">
                <h2 class="hj-memory-block-title"><?php _e('博文子分类占比'); ?></h2>
                <div class="hj-memory-chart" id="hj-memory-chart-categories"></div>
            </section>

            <section class="hj-memory-block" aria-label="<?php _e('标签占比'); ?>">
                <h2 class="hj-memory-block-title"><?php _e('标签占比'); ?></h2>
                <div class="hj-memory-chart" id="hj-memory-chart-tags"></div>
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
