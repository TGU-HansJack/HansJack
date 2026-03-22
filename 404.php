<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');
?>

<main class="main page-404-main" role="main">
    <div class="links-apply-main page-404-wrap">
        <section class="links-apply-main-inner" aria-label="404 页面">
            <article class="links-step page-404-panel" aria-label="404 Page Not Found">
                <div class="page-404-content">
                    <h1 class="page-404-title">404 页面不存在</h1>
                    <p class="page-404-title-en">404 Page Not Found</p>
                    <p class="page-404-desc">你访问的页面可能已删除、改名或暂时不可用。</p>
                    <p class="page-404-desc-en">The page you requested may have been removed, renamed, or is temporarily unavailable.</p>
                    <div class="links-step-actions page-404-actions">
                        <a class="links-btn links-submit-btn links-primary-btn" href="<?php $this->options->siteUrl(); ?>">返回首页 / Back Home</a>
                    </div>
                </div>
            </article>
        </section>
    </div>
</main>

<?php $this->need('footer.php'); ?>
