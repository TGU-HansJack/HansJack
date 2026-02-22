<?php

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

use Typecho\Common;
use Widget\Archive;
use Widget\Options;

/**
 * 主题设置项
 */
function themeConfig($form)
{
    // Intentionally left blank: this theme keeps its UI stable and does not
    // expose per-site visual tweaks in the admin panel.
}

/**
 * Theme entry hook.
 * Keep root "posts"/"notes" archives at a fixed 15 items per page.
 */
function themeInit(Archive $archive)
{
    if ($archive->is('category', 'posts') || $archive->is('category', 'notes')) {
        $archive->parameter->pageSize = 15;
    }
}

function hansJackRenderPager(
    Archive $archive,
    string $prevHtml,
    string $nextHtml,
    int $splitPage = 2,
    string $splitWord = '...',
    ?string $basePath = null
): void
{
    $totalPage = 0;
    try {
        $totalPage = (int) $archive->getTotalPage();
    } catch (\Throwable $e) {
        $totalPage = 0;
    }

    if ($totalPage <= 1) {
        return;
    }

    $basePath = is_string($basePath) ? trim($basePath) : null;
    if ($basePath !== null) {
        $basePath = trim($basePath, '/');
        if ($basePath === '') {
            $basePath = null;
        }
    }

    // Theme-level list pages (e.g. /posts, /notes): use a fixed base path for pagination
    // instead of Typecho's archive routes (category_page, tag_page, ...).
    if ($basePath !== null) {
        $currentPage = 1;
        try {
            $currentPage = max(1, (int) $archive->getCurrentPage());
        } catch (\Throwable $e) {
            $currentPage = 1;
        }

        $options = Options::alloc();
        $baseUrl = Common::url($basePath, (string) $options->index);
        $buildUrl = static function (string $baseUrl, int $page): string {
            if ($page <= 1) {
                return $baseUrl;
            }
            $sep = (strpos($baseUrl, '?') === false) ? '?' : '&';
            return $baseUrl . $sep . 'page=' . $page;
        };

        $from = max(1, $currentPage - $splitPage);
        $to = min($totalPage, $currentPage + $splitPage);

        echo '<ol class="page-navigator hj-posts-pager">';

        // Prev (always present; disabled on first page)
        if ($currentPage > 1) {
            echo '<li class="prev"><a href="'
                . hansJackEscape($buildUrl($baseUrl, $currentPage - 1))
                . '">' . $prevHtml . '</a></li>';
        } else {
            echo '<li class="prev is-disabled"><span aria-disabled="true" tabindex="-1">' . $prevHtml . '</span></li>';
        }

        // First page + leading gap
        if ($from > 1) {
            echo '<li><a href="'
                . hansJackEscape($buildUrl($baseUrl, 1))
                . '">1</a></li>';
            if ($from > 2) {
                echo '<li class="hj-posts-pager-gap"><span>' . hansJackEscape($splitWord) . '</span></li>';
            }
        }

        // Middle pages
        for ($i = $from; $i <= $to; $i++) {
            $cls = ($i === $currentPage) ? ' class="current"' : '';
            echo '<li' . $cls . '><a href="'
                . hansJackEscape($buildUrl($baseUrl, $i))
                . '">' . (int) $i . '</a></li>';
        }

        // Trailing gap + last page
        if ($to < $totalPage) {
            if ($to < $totalPage - 1) {
                echo '<li class="hj-posts-pager-gap"><span>' . hansJackEscape($splitWord) . '</span></li>';
            }
            echo '<li><a href="'
                . hansJackEscape($buildUrl($baseUrl, $totalPage))
                . '">' . (int) $totalPage . '</a></li>';
        }

        // Next (always present; disabled on last page)
        if ($currentPage < $totalPage) {
            echo '<li class="next"><a href="'
                . hansJackEscape($buildUrl($baseUrl, $currentPage + 1))
                . '">' . $nextHtml . '</a></li>';
        } else {
            echo '<li class="next is-disabled"><span aria-disabled="true" tabindex="-1">' . $nextHtml . '</span></li>';
        }

        echo '</ol>';
        return;
    }

    $template = [
        'wrapTag' => 'ol',
        'wrapClass' => 'page-navigator hj-posts-pager',
        'itemTag' => 'li',
        'textTag' => 'span',
        'currentClass' => 'current',
        'prevClass' => 'prev',
        'nextClass' => 'next',
    ];

    ob_start();
    $archive->pageNav($prevHtml, $nextHtml, $splitPage, $splitWord, $template);
    $html = (string) ob_get_clean();
    $html = trim($html);
    if ($html === '') {
        return;
    }

    if (!preg_match('/<li[^>]*class=\"[^\"]*\\bprev\\b/', $html)) {
        $disabledPrev = '<li class="prev is-disabled"><span aria-disabled="true" tabindex="-1">' . $prevHtml . '</span></li>';
        $html = (string) preg_replace('/(<ol\\b[^>]*>)/', '$1' . $disabledPrev, $html, 1);
    }

    if (!preg_match('/<li[^>]*class=\"[^\"]*\\bnext\\b/', $html)) {
        $disabledNext = '<li class="next is-disabled"><span aria-disabled="true" tabindex="-1">' . $nextHtml . '</span></li>';
        $html = (string) preg_replace('/<\\/ol>\\s*$/', $disabledNext . '</ol>', $html, 1);
    }

    echo $html;
}

/**
 * 汇总主题配置（模板层调用）
 */
function hansJackBuildThemeConfig(Options $options): array
{
    $brandName = hansJackText((string) $options->title, 'HansJack');

    $links = [
        'home'   => (string) $options->siteUrl,
        // Blog points to a dedicated post list page (slug: "posts").
        // Uses options->index so it works both with and without rewrite (index.php).
        'blog'   => Common::url('posts', (string) $options->index),
        // Memo points to a dedicated notes list page (slug: "notes").
        'memo'   => Common::url('notes', (string) $options->index),
        // Memory points to a dedicated stats page (slug: "memory").
        'memory' => Common::url('memory', (string) $options->index),
    ];

    return [
        'brandName' => $brandName,
        'links' => $links,
        'navItems' => [
            ['key' => 'home', 'label' => '首页', 'url' => $links['home']],
            ['key' => 'blog', 'label' => '博文', 'url' => $links['blog']],
            ['key' => 'memo', 'label' => '手记', 'url' => $links['memo']],
            ['key' => 'memory', 'label' => '回忆', 'url' => $links['memory']]
        ]
    ];
}

function hansJackText(string $value, string $fallback = ''): string
{
    $trimmed = trim($value);
    return $trimmed === '' ? $fallback : $trimmed;
}

function hansJackPercent(string $value, int $fallback = 86): int
{
    $num = (int) trim($value);
    if ($num < 60 || $num > 100) {
        return $fallback;
    }
    return $num;
}

function hansJackColor(string $value, string $fallback): string
{
    $trimmed = trim($value);
    if (preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $trimmed)) {
        return strtolower($trimmed);
    }
    return $fallback;
}

function hansJackStartsWith(string $haystack, string $needle): bool
{
    if (function_exists('str_starts_with')) {
        return str_starts_with($haystack, $needle);
    }

    if ($needle === '') {
        return true;
    }

    return substr($haystack, 0, strlen($needle)) === $needle;
}

function hansJackResolveLink(Options $options, string $value, string $fallbackPath): string
{
    $value = trim($value);
    if ($value === '') {
        return Common::url($fallbackPath, $options->siteUrl);
    }

    if (preg_match('/^(https?:)?\\/\\//i', $value) || hansJackStartsWith($value, '#') || hansJackStartsWith($value, 'mailto:') || hansJackStartsWith($value, 'tel:')) {
        return $value;
    }

    return Common::url(ltrim($value, '/'), $options->siteUrl);
}

function hansJackNormalizeAssetUrl(Options $options, string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $value = str_replace(["\r", "\n", '"', '\'', '(', ')'], '', $value);
    if (preg_match('/^(https?:)?\\/\\//i', $value) || hansJackStartsWith($value, '/')) {
        return $value;
    }

    return Common::url(ltrim($value, '/'), $options->siteUrl);
}

function hansJackCssBackground(string $url): string
{
    if ($url === '') {
        return 'none';
    }

    $safe = str_replace(['\\', '\''], ['\\\\', '\\\''], $url);
    return "url('{$safe}')";
}

function hansJackEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function hansJackAsciiBanner(string $raw, int $maxLineChars = 0, int $weight = 2): string
{
    $text = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $raw));
    $text = trim($text);
    if ($text === '') {
        $text = 'HANSJACK';
    }

    $maxLineChars = (int) $maxLineChars;
    if ($maxLineChars <= 0) {
        // "0" means: do not split across multiple words/lines.
        $maxLineChars = strlen($text);
    }

    $weight = (int) $weight;
    if ($weight < 1) {
        $weight = 1;
    }
    if ($weight > 3) {
        $weight = 3;
    }

    static $glyphs = null;
    if ($glyphs === null) {
        // 5x7 bitmap font, each row is a 5-bit mask (MSB on the left).
        $glyphs = [
            'A' => [14, 17, 17, 31, 17, 17, 17],
            'B' => [30, 17, 17, 30, 17, 17, 30],
            'C' => [14, 17, 16, 16, 16, 17, 14],
            'D' => [30, 17, 17, 17, 17, 17, 30],
            'E' => [31, 16, 16, 30, 16, 16, 31],
            'F' => [31, 16, 16, 30, 16, 16, 16],
            'G' => [14, 17, 16, 23, 17, 17, 14],
            'H' => [17, 17, 17, 31, 17, 17, 17],
            'I' => [31, 4, 4, 4, 4, 4, 31],
            'J' => [15, 1, 1, 1, 17, 17, 14],
            'K' => [17, 18, 20, 24, 20, 18, 17],
            'L' => [16, 16, 16, 16, 16, 16, 31],
            'M' => [17, 27, 21, 17, 17, 17, 17],
            'N' => [17, 25, 21, 19, 17, 17, 17],
            'O' => [14, 17, 17, 17, 17, 17, 14],
            'P' => [30, 17, 17, 30, 16, 16, 16],
            'Q' => [14, 17, 17, 17, 21, 18, 13],
            'R' => [30, 17, 17, 30, 20, 18, 17],
            'S' => [15, 16, 16, 14, 1, 1, 30],
            'T' => [31, 4, 4, 4, 4, 4, 4],
            'U' => [17, 17, 17, 17, 17, 17, 14],
            'V' => [17, 17, 17, 17, 17, 10, 4],
            'W' => [17, 17, 17, 17, 21, 27, 17],
            'X' => [17, 10, 4, 4, 4, 10, 17],
            'Y' => [17, 10, 4, 4, 4, 4, 4],
            'Z' => [31, 1, 2, 4, 8, 16, 31],
            '0' => [14, 17, 19, 21, 25, 17, 14],
            '1' => [4, 12, 4, 4, 4, 4, 14],
            '2' => [14, 17, 1, 2, 4, 8, 31],
            '3' => [30, 1, 1, 6, 1, 1, 30],
            '4' => [2, 6, 10, 18, 31, 2, 2],
            '5' => [31, 16, 16, 30, 1, 1, 30],
            '6' => [6, 8, 16, 30, 17, 17, 14],
            '7' => [31, 1, 2, 4, 8, 8, 8],
            '8' => [14, 17, 17, 14, 17, 17, 14],
            '9' => [14, 17, 17, 15, 1, 2, 28],
            '?' => [14, 17, 1, 2, 4, 0, 4],
        ];
    }

    $chunks = str_split($text, $maxLineChars);

    $height = 7;
    $out = [];
    $chunkTotal = count($chunks);
    foreach ($chunks as $chunkIndex => $chunk) {
        $rows = array_fill(0, $height, '');
        $chars = str_split($chunk);
        $count = count($chars);
        foreach ($chars as $ci => $ch) {
            $pattern = $glyphs[$ch] ?? $glyphs['?'];
            for ($r = 0; $r < $height; $r++) {
                $mask = (int) ($pattern[$r] ?? 0);
                if ($weight > 1) {
                    // Bold by dilating the 5-bit mask within the glyph box.
                    // Keeps the banner compact while increasing stroke density.
                    $bold = $mask;
                    for ($w = 1; $w < $weight; $w++) {
                        $bold = ($bold | (($bold << 1) & 31) | ($bold >> 1)) & 31;
                    }
                    $mask = $bold;
                }
                for ($bit = 4; $bit >= 0; $bit--) {
                    $rows[$r] .= ($mask & (1 << $bit)) ? $ch : ' ';
                }
                if ($ci !== $count - 1) {
                    $rows[$r] .= ' ';
                }
            }
        }

        for ($r = 0; $r < $height; $r++) {
            $rows[$r] = rtrim($rows[$r]);
        }

        $out = array_merge($out, $rows);
        if ($chunkIndex !== $chunkTotal - 1) {
            $out[] = '';
        }
    }

    return implode("\n", $out);
}

function hansJackAvatarFallback(Options $options): string
{
    $title = trim((string) $options->title);
    if ($title === '') {
        return 'HJ';
    }

    if (function_exists('mb_substr')) {
        return mb_substr($title, 0, 1, 'UTF-8');
    }

    return strtoupper(substr($title, 0, 1));
}

function hansJackIsActiveNav(Archive $archive, string $targetUrl): bool
{
    $targetRaw = (string) (parse_url($targetUrl, PHP_URL_PATH) ?? '');
    if ($targetRaw === '') {
        return false;
    }

    $currentUri = (string) ($archive->request->getRequestUri() ?? '/');
    $currentRaw = (string) (parse_url($currentUri, PHP_URL_PATH) ?? '/');

    $targetPath = hansJackNormalizePath($targetRaw);
    $currentPath = hansJackNormalizePath($currentRaw);

    return $currentPath === $targetPath;
}

function hansJackNormalizePath(string $path): string
{
    $normalized = '/' . ltrim(trim($path), '/');
    $normalized = (string) preg_replace('#/+#', '/', $normalized);
    $normalized = (string) preg_replace('#^/index\\.php#i', '', $normalized);
    $normalized = $normalized === '' ? '/' : $normalized;
    $normalized = rtrim($normalized, '/');
    return $normalized === '' ? '/' : $normalized;
}

function hansJackNavIconSvg(string $key): string
{
    switch ($key) {
        case 'home':
            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 21 14 3"/><path d="M20.5 21 10 3"/><path d="M15.5 21 12 15l-3.5 6"/><path d="M2 21h20"/></svg>';
        case 'blog':
            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 12h-5"/><path d="M15 8h-5"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/></svg>';
        case 'memo':
            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.67 19a2 2 0 0 0 1.416-.588l6.154-6.172a6 6 0 0 0-8.49-8.49L5.586 9.914A2 2 0 0 0 5 11.328V18a1 1 0 0 0 1 1z"/><path d="M16 8 2 22"/><path d="M17.5 15H9"/></svg>';
        case 'memory':
            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path class="hj-memory-hour" d="M12 8v4"/><path class="hj-memory-minute" d="M12 12l3 1.6"/></svg>';
        default:
            return '';
    }
}

function hansJackAvatarRating(Options $options): string
{
    $rating = strtoupper(trim((string) ($options->commentsAvatarRating ?? 'G')));
    return in_array($rating, ['G', 'PG', 'R', 'X'], true) ? $rating : 'G';
}
