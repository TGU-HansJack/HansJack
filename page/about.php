<?php
/**
 * About 页（/about）
 *
 * 说明：该模板会在你创建一个独立页面，且 slug 为 `about` 时自动生效。
 *
 * 自定义字段（页面编辑 -> 自定义字段）：
 * - timeline: JSON 字符串，数组项包含 `time` 与 `value`
 *
 * 示例：
 * [
 *   {"time":"2025/01/07","value":["更新至 Svelte 5","更新至 SvelteKit 2.12"]},
 *   {"time":"2024/08/20","value":"站点命名为「记绪漂流」"}
 * ]
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

if (!function_exists('hansJackAboutDecodeJson')) {
    /**
     * @return mixed
     */
    function hansJackAboutDecodeJson(string $json)
    {
        $json = trim($json);
        if ($json === '') {
            return null;
        }

        // Remove UTF-8 BOM if present.
        if (strncmp($json, "\xEF\xBB\xBF", 3) === 0) {
            $json = substr($json, 3);
        }

        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $decoded;
    }
}

if (!function_exists('hansJackAboutTimelineValues')) {
    /**
     * @param mixed $value
     * @return string[]
     */
    function hansJackAboutTimelineValues($value): array
    {
        $rows = [];

        $pushValue = static function ($item) use (&$rows): void {
            $text = trim((string) $item);
            if ($text !== '') {
                $rows[] = $text;
            }
        };

        if (is_array($value)) {
            foreach ($value as $item) {
                $pushValue($item);
            }
            return $rows;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return $rows;
        }

        $decoded = hansJackAboutDecodeJson($raw);
        if (is_array($decoded)) {
            foreach ($decoded as $item) {
                $pushValue($item);
            }
            if (!empty($rows)) {
                return $rows;
            }
        }

        $lines = preg_split('/\r\n|\r|\n/u', $raw);
        if (is_array($lines)) {
            foreach ($lines as $line) {
                $pushValue($line);
            }
        }

        return $rows;
    }
}

if (!function_exists('hansJackAboutTimelineRows')) {
    /**
     * @param mixed $archive
     * @return array<int, array{time:string, values:array<int, string>}>
     */
    function hansJackAboutTimelineRows($archive): array
    {
        if (!$archive) {
            return [];
        }

        $raw = '';
        try {
            $raw = (string) ($archive->fields->timeline ?? '');
        } catch (\Throwable $e) {
            $raw = '';
        }

        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }

        $decoded = hansJackAboutDecodeJson($raw);
        if (is_string($decoded)) {
            $decodedSecond = hansJackAboutDecodeJson($decoded);
            if (is_array($decodedSecond)) {
                $decoded = $decodedSecond;
            }
        }

        if (!is_array($decoded)) {
            return [];
        }

        $rows = [];
        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }

            $time = trim((string) ($item['time'] ?? ''));
            if ($time === '') {
                continue;
            }

            $values = hansJackAboutTimelineValues($item['value'] ?? '');
            $rows[] = [
                'time' => $time,
                'values' => $values,
            ];
        }

        // Time line is rendered from newest to oldest.
        return array_reverse($rows);
    }
}

$hjTimelineRows = hansJackAboutTimelineRows($this);
$this->need('header.php');
?>

<main class="hj-main" role="main">
    <section class="hj-about" aria-label="<?php _e('关于'); ?>">
        <div class="hj-about-layout">
            <article class="hj-about-article">
                <div class="hj-article-content hj-about-content">
                    <?php hansJackEchoArchiveContent($this); ?>
                </div>
            </article>

            <aside class="hj-about-aside" aria-label="<?php _e('时间线'); ?>">
                <div class="hj-about-timeline">
                    <?php if (!empty($hjTimelineRows)): ?>
                        <ol class="hj-about-timeline-list">
                            <?php foreach ($hjTimelineRows as $row): ?>
                                <?php
                                $time = (string) ($row['time'] ?? '');
                                $values = is_array($row['values'] ?? null) ? $row['values'] : [];
                                if ($time === '') {
                                    continue;
                                }
                                ?>
                                <li class="hj-about-timeline-item">
                                    <div class="hj-about-timeline-body">
                                        <time class="hj-about-timeline-time"><?php echo hansJackEscape($time); ?></time>
                                        <?php if (!empty($values)): ?>
                                            <ul class="hj-about-timeline-values">
                                                <?php foreach ($values as $value): ?>
                                                    <?php $valueText = trim((string) $value); ?>
                                                    <?php if ($valueText === ''): ?>
                                                        <?php continue; ?>
                                                    <?php endif; ?>
                                                    <li><?php echo hansJackEscape($valueText); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php else: ?>
                        <p class="hj-about-timeline-empty"><?php _e('暂无时间线，请在自定义字段 timeline 中填写 JSON。'); ?></p>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </section>
</main>

<?php $this->need('footer.php'); ?>
