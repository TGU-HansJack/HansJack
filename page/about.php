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

if (!function_exists('aboutDecodeJson')) {
    /**
     * @return mixed
     */
    function aboutDecodeJson(string $json)
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

if (!function_exists('aboutTimelineValues')) {
    /**
     * @param mixed $value
     * @return string[]
     */
    function aboutTimelineValues($value): array
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

        $decoded = aboutDecodeJson($raw);
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

if (!function_exists('aboutTimelineRows')) {
    /**
     * @param mixed $archive
     * @return array<int, array{time:string, values:array<int, string>}>
     */
    function aboutTimelineRows($archive): array
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

        $decoded = aboutDecodeJson($raw);
        if (is_string($decoded)) {
            $decodedSecond = aboutDecodeJson($decoded);
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

            $values = aboutTimelineValues($item['value'] ?? '');
            $rows[] = [
                'time' => $time,
                'values' => $values,
            ];
        }

        // Time line is rendered from newest to oldest.
        return array_reverse($rows);
    }
}

$timelineRows = aboutTimelineRows($this);
$this->need('header.php');
?>

<main class="main" role="main">
    <section class="about" aria-label="<?php _e('关于'); ?>">
        <div class="about-layout">
            <article class="about-article">
                <div class="article-content about-content">
                    <?php echoArchiveContent($this); ?>
                </div>
            </article>

            <aside class="about-aside" aria-label="<?php _e('时间线'); ?>">
                <div class="about-timeline">
                    <?php if (!empty($timelineRows)): ?>
                        <ol class="about-timeline-list">
                            <?php foreach ($timelineRows as $row): ?>
                                <?php
                                $time = (string) ($row['time'] ?? '');
                                $values = is_array($row['values'] ?? null) ? $row['values'] : [];
                                if ($time === '') {
                                    continue;
                                }
                                ?>
                                <li class="about-timeline-item">
                                    <div class="about-timeline-body">
                                        <time class="about-timeline-time"><?php echo escape($time); ?></time>
                                        <?php if (!empty($values)): ?>
                                            <ul class="about-timeline-values">
                                                <?php foreach ($values as $value): ?>
                                                    <?php $valueText = trim((string) $value); ?>
                                                    <?php if ($valueText === ''): ?>
                                                        <?php continue; ?>
                                                    <?php endif; ?>
                                                    <li><?php echo escape($valueText); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php else: ?>
                        <p class="about-timeline-empty"><?php _e('暂无时间线，请在自定义字段 timeline 中填写 JSON。'); ?></p>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </section>
</main>

<?php $this->need('footer.php'); ?>
