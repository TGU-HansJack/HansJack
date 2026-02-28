<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');
?>

<main class="hj-main" role="main">
    <article class="hj-page">
        <header class="hj-article-header">
            <h1 class="hj-article-title"><?php $this->title(); ?></h1>
        </header>
        <div class="hj-article-content">
            <?php hansJackEchoArchiveContent($this); ?>
        </div>
    </article>
</main>

<?php $this->need('footer.php'); ?>
