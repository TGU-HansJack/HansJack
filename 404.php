<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');
?>

<main class="hj-main" role="main">
    <section class="hj-card">
        <h1 class="hj-card-title">404</h1>
        <p class="hj-card-summary"><?php _e('你访问的页面不存在。'); ?></p>
        <a class="hj-btn hj-btn-primary" href="<?php $this->options->siteUrl(); ?>"><?php _e('返回首页'); ?></a>
    </section>
</main>

<?php $this->need('footer.php'); ?>
