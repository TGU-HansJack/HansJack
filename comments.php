<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
?>

<section id="comments" class="hj-comments" aria-label="<?php _e('评论'); ?>">
    <?php $this->comments()->to($comments); ?>

    <?php if ($comments->have()): ?>
        <h2 class="hj-comments-title">
            <?php $this->commentsNum(_t('暂无评论'), _t('仅有一条评论'), _t('已有 %d 条评论')); ?>
        </h2>

        <?php $comments->listComments(); ?>

        <?php $comments->pageNav(); ?>
    <?php else: ?>
        <h2 class="hj-comments-title"><?php _e('暂无评论'); ?></h2>
    <?php endif; ?>

    <?php if ($this->allow('comment')): ?>
        <div id="<?php $this->respondId(); ?>" class="respond hj-respond" data-hj-comment-respond>
            <div class="cancel-comment-reply">
                <?php $comments->cancelReply(); ?>
            </div>

            <h3 id="response" class="hj-respond-title"><?php _e('添加新评论'); ?></h3>
            <form method="post" action="<?php $this->commentUrl(); ?>" id="comment-form" class="hj-comment-form" role="form">
                <?php if ($this->user->hasLogin()): ?>
                    <p class="hj-respond-login">
                        <?php _e('登录身份'); ?>:
                        <a href="<?php $this->options->profileUrl(); ?>"><?php $this->user->screenName(); ?></a>.
                        <a href="<?php $this->options->logoutUrl(); ?>" title="Logout"><?php _e('退出'); ?> &raquo;</a>
                    </p>
                <?php else: ?>
                    <p class="hj-comment-field">
                        <label for="author" class="required"><?php _e('称呼'); ?></label>
                        <input type="text" name="author" id="author" class="text" value="<?php $this->remember('author'); ?>" required />
                    </p>
                    <p class="hj-comment-field">
                        <label for="mail"<?php if ($this->options->commentsRequireMail): ?> class="required"<?php endif; ?>><?php _e('Email'); ?></label>
                        <input type="email" name="mail" id="mail" class="text" value="<?php $this->remember('mail'); ?>"<?php if ($this->options->commentsRequireMail): ?> required<?php endif; ?> />
                    </p>
                    <p class="hj-comment-field">
                        <label for="url"<?php if ($this->options->commentsRequireUrl): ?> class="required"<?php endif; ?>><?php _e('网站'); ?></label>
                        <input type="url" name="url" id="url" class="text" placeholder="<?php _e('http://'); ?>" value="<?php $this->remember('url'); ?>"<?php if ($this->options->commentsRequireUrl): ?> required<?php endif; ?> />
                    </p>
                <?php endif; ?>

                <p class="hj-comment-field">
                    <label for="textarea" class="required"><?php _e('内容'); ?></label>
                    <textarea rows="8" cols="50" name="text" id="textarea" class="textarea" required><?php $this->remember('text'); ?></textarea>
                </p>

                <p class="hj-comment-actions">
                    <button type="submit" class="submit hj-comment-submit"><?php _e('提交评论'); ?></button>
                </p>
            </form>
        </div>
    <?php else: ?>
        <h2 class="hj-comments-closed"><?php _e('评论已关闭'); ?></h2>
    <?php endif; ?>
</section>

