<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');

$contentTypeRaw = '';
try {
    $contentTypeRaw = trim((string) ($this->fields->content_type ?? ''));
} catch (\Throwable $e) {
    $contentTypeRaw = '';
}

$contentTypeKey = function_exists('mb_strtolower')
    ? mb_strtolower($contentTypeRaw, 'UTF-8')
    : strtolower($contentTypeRaw);

$contentWarningMap = [
    'warning' => [
        'icon' => '🚨',
        'title' => _t('内容警告'),
        'lines' => [
            _t('本文含有暴力血腥等强刺激画面与描述内容等'),
            _t('若您近期状态较脆弱请谨慎继续阅读本文内容'),
        ],
        'button' => _t('我已知晓，继续阅读'),
        'tone' => 'warning',
    ],
    'nsfw' => [
        'icon' => '🔞',
        'title' => _t('成人内容提示'),
        'lines' => [
            _t('本文含成人向与不宜公开场合浏览的内容提示'),
            _t('请确认您已满十八周岁并在合适环境阅读本文'),
        ],
        'button' => _t('我已年满18岁，继续阅读'),
        'tone' => 'nsfw',
    ],
    'sensitive' => [
        'icon' => '🧠',
        'title' => _t('敏感内容提示'),
        'lines' => [
            _t('本文涉及心理情绪敏感议题请先评估当前状态'),
            _t('如您感到不适或压力升高建议先暂停阅读文章'),
        ],
        'button' => _t('我已了解，继续阅读'),
        'tone' => 'sensitive',
    ],
];

$contentWarningConfig = $contentWarningMap[$contentTypeKey] ?? null;
$contentWarningEnabled = is_array($contentWarningConfig);
$contentWarningBackUrl = '';
if ($contentWarningEnabled) {
    $contentWarningBackUrl = trim((string) ($this->options->siteUrl ?? ''));
    if ($contentWarningBackUrl !== '') {
        $contentWarningBackUrl = rtrim($contentWarningBackUrl, '/') . '/';
    } else {
        $contentWarningBackUrl = '/';
    }
}
?>

<main class="main" role="main">
    <article class="page<?php echo $contentWarningEnabled ? ' is-content-warning-active' : ''; ?>" data-content-warning-active="<?php echo $contentWarningEnabled ? '1' : '0'; ?>">
        <header class="article-header">
            <h1 class="article-title"><?php $this->title(); ?></h1>
        </header>
        <?php if ($contentWarningEnabled): ?>
            <section class="article-content-warning is-type-<?php echo escape((string) ($contentWarningConfig['tone'] ?? 'warning')); ?>" data-content-warning-shell>
                <div class="article-content-warning-panel">
                    <h2 class="article-content-warning-title">
                        <span class="article-content-warning-title-icon" aria-hidden="true"><?php echo escape((string) ($contentWarningConfig['icon'] ?? '⚠️')); ?></span>
                        <span class="article-content-warning-title-text"><?php echo escape((string) ($contentWarningConfig['title'] ?? _t('内容提示'))); ?></span>
                    </h2>
                    <div class="article-content-warning-copy">
                        <?php
                        $contentWarningLines = is_array($contentWarningConfig['lines'] ?? null) ? $contentWarningConfig['lines'] : [];
                        ?>
                        <?php foreach ($contentWarningLines as $line): ?>
                            <p><?php echo escape((string) $line); ?></p>
                        <?php endforeach; ?>
                    </div>
                    <div class="article-content-warning-actions">
                        <button class="links-btn links-submit-btn links-primary-btn article-content-warning-btn article-content-warning-continue" type="button" data-content-warning-continue>
                            <?php echo escape((string) ($contentWarningConfig['button'] ?? _t('继续阅读'))); ?>
                        </button>
                        <a href="<?php echo escape($contentWarningBackUrl); ?>" class="links-btn article-content-warning-btn article-content-warning-back">
                            <?php _e('返回列表'); ?>
                        </a>
                    </div>
                </div>
            </section>
            <script>
                (function () {
                    function initContentWarning() {
                        var warningShell = document.querySelector("[data-content-warning-shell]");
                        var warningTarget = document.querySelector("[data-content-warning-target]");
                    if (!warningShell || !warningTarget) {
                        return;
                    }
                        function revealContent() {
                            warningTarget.hidden = false;
                            warningTarget.removeAttribute("aria-hidden");
                            warningShell.hidden = true;
                            warningShell.setAttribute("aria-hidden", "true");

                            var article = warningShell.closest(".article, .page");
                            if (article) {
                                article.classList.remove("is-content-warning-active");
                                article.setAttribute("data-content-warning-active", "0");
                            }
                        }

                        var continueBtn = warningShell.querySelector("[data-content-warning-continue]");
                        if (!continueBtn || continueBtn.getAttribute("data-warning-bound") === "1") {
                            return;
                        }
                        continueBtn.setAttribute("data-warning-bound", "1");

                        continueBtn.addEventListener("click", function () {
                            revealContent();
                        });
                    }

                    if (document.readyState === "loading") {
                        document.addEventListener("DOMContentLoaded", initContentWarning);
                    }

                    window.setTimeout(initContentWarning, 0);
                })();
            </script>
        <?php endif; ?>

        <div class="article-content-warning-target"<?php if ($contentWarningEnabled): ?> data-content-warning-target hidden aria-hidden="true"<?php endif; ?>>
            <div class="article-content">
                <?php echoArchiveContent($this); ?>
            </div>
        </div>
    </article>
</main>

<?php $this->need('footer.php'); ?>
