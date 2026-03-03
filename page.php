<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');
?>

<main class="main" role="main">
    <article class="page">
        <header class="article-header">
            <h1 class="article-title"><?php $this->title(); ?></h1>
        </header>
        <div class="article-content">
            <?php echoArchiveContent($this); ?>
        </div>
    </article>
</main>

<?php $this->need('footer.php'); ?>
