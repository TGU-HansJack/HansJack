<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');
?>

<main class="main" role="main">
    <section class="card">
        <h1 class="card-title">404</h1>
        <p class="card-summary"><?php _e('你访问的页面不存在。'); ?></p>
        <a class="btn btn-primary" href="<?php $this->options->siteUrl(); ?>"><?php _e('返回首页'); ?></a>
    </section>
</main>

<?php $this->need('footer.php'); ?>
