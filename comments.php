<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
?>

<?php
$hjCommentsOrder = 'asc';
try {
    $hjCommentsOrder = strtoupper((string) ($this->options->commentsOrder ?? 'ASC'));
} catch (\Throwable $e) {
    $hjCommentsOrder = 'ASC';
}
$hjCommentsOrder = ($hjCommentsOrder === 'DESC') ? 'desc' : 'asc';

$hjUserLoggedIn = false;
try {
    $hjUserLoggedIn = (bool) ($this->user && $this->user->hasLogin());
} catch (\Throwable $e) {
    $hjUserLoggedIn = false;
}

$hjRequireMail = false;
try {
    $hjRequireMail = (bool) ($this->options->commentsRequireMail ?? false);
} catch (\Throwable $e) {
    $hjRequireMail = false;
}

$hjRequireUrl = false;
try {
    $hjRequireUrl = (bool) ($this->options->commentsRequireUrl ?? false);
} catch (\Throwable $e) {
    $hjRequireUrl = false;
}
?>

<section id="comments" class="hj-comments" aria-label="<?php _e('评论'); ?>"
         data-hj-comments-order="<?php echo hansJackEscape($hjCommentsOrder); ?>"
         data-hj-user-logged="<?php echo $hjUserLoggedIn ? '1' : '0'; ?>"
         data-hj-comments-require-mail="<?php echo $hjRequireMail ? '1' : '0'; ?>"
         data-hj-comments-require-url="<?php echo $hjRequireUrl ? '1' : '0'; ?>">
    <?php $this->comments()->to($comments); ?>

    <?php if ($this->allow('comment')): ?>
        <?php
        $hjLoginReferer = '';
        try {
            $hjLoginReferer = (string) $this->request->getRequestUrl();
        } catch (\Throwable $e) {
            $hjLoginReferer = '';
        }

        $hjCommentToken = '';
        try {
            if ($hjLoginReferer !== '') {
                $hjCommentToken = (string) $this->security->getToken($hjLoginReferer);
            }
        } catch (\Throwable $e) {
            $hjCommentToken = '';
        }

        $hjGithubLoginUrl = '';
        try {
            if (function_exists('hansJackGithubOauthEnabled') && hansJackGithubOauthEnabled($this->options)) {
                $hjGithubLoginUrl = hansJackGithubOauthActionUrl('login', [
                    'return' => ($hjLoginReferer !== '') ? $hjLoginReferer : (string) $this->options->siteUrl,
                ]);
            }
        } catch (\Throwable $e) {
            $hjGithubLoginUrl = '';
        }
        ?>
        <form method="post" action="<?php $this->commentUrl(); ?>" id="comment-form-top" class="hj-comment-form hj-comment-composer-form" role="form"
              data-hj-comment-form data-hj-comment-role="top" data-hj-user-logged="<?php echo $hjUserLoggedIn ? '1' : '0'; ?>">
            <div class="hj-comment-box" data-hj-comment-box>
                <textarea rows="6" cols="50" name="text" id="hj-comment-textarea-top" class="hj-comment-textarea" required></textarea>
                <input type="hidden" name="author" value="<?php $this->remember('author'); ?>">
                <input type="hidden" name="url" value="<?php $this->remember('url'); ?>">
                <input type="hidden" name="mail" value="<?php $this->remember('mail'); ?>">
                <div class="hj-comment-composer-actions" aria-label="<?php _e('评论操作'); ?>">
                    <div class="hj-comment-actions-left" aria-label="<?php _e('工具'); ?>">
                        <button class="hj-comment-icon-btn hj-comment-emoji" type="button" aria-label="<?php _e('表情'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-smile-icon lucide-smile" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/></svg>
                        </button>
                        <button class="hj-comment-icon-btn hj-comment-attach" type="button" aria-label="<?php _e('附件'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-paperclip-icon lucide-paperclip" aria-hidden="true"><path d="m16 6-8.414 8.586a2 2 0 0 0 2.829 2.829l8.414-8.586a4 4 0 1 0-5.657-5.657l-8.379 8.551a6 6 0 1 0 8.485 8.485l8.379-8.551"/></svg>
                        </button>
                        <button class="hj-comment-icon-btn hj-comment-private" type="button" aria-label="<?php _e('私信'); ?>" aria-pressed="false" data-hj-comment-private-toggle>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-line-dot-right-horizontal-icon lucide-line-dot-right-horizontal" aria-hidden="true"><path class="hj-private-line" d="M 3 12 L 15 12"/><circle class="hj-private-dot" cx="18" cy="12" r="3"/></svg>
                        </button>
                        <button class="hj-comment-icon-btn hj-comment-fullscreen-toggle" type="button" aria-label="<?php _e('展开全屏'); ?>" aria-pressed="false" data-hj-comment-fullscreen-toggle>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-maximize-icon lucide-maximize hj-comment-fullscreen-icon hj-comment-fullscreen-icon-max" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minimize-icon lucide-minimize hj-comment-fullscreen-icon hj-comment-fullscreen-icon-min" aria-hidden="true"><path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></svg>
                        </button>
                    </div>
                    <div class="hj-comment-actions-right" aria-label="<?php _e('提交'); ?>">
                        <button class="hj-comment-icon-btn hj-comment-login" type="button" aria-label="<?php _e('登录'); ?>" data-hj-open-login>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-icon lucide-user-round" aria-hidden="true"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                        </button>
                        <button class="hj-comment-icon-btn hj-comment-send" type="submit" aria-label="<?php _e('提交评论'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send-icon lucide-send" aria-hidden="true"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <?php if ($hjCommentToken !== ''): ?>
                <input type="hidden" name="_" value="<?php echo hansJackEscape($hjCommentToken); ?>">
            <?php endif; ?>
        </form>

        <div id="<?php $this->respondId(); ?>" class="respond hj-respond" data-hj-comment-respond data-hj-user-logged="<?php echo $hjUserLoggedIn ? '1' : '0'; ?>">
            <form method="post" action="<?php $this->commentUrl(); ?>" id="comment-form" class="hj-comment-form hj-comment-composer-form" role="form"
                  data-hj-comment-form data-hj-comment-role="reply" data-hj-user-logged="<?php echo $hjUserLoggedIn ? '1' : '0'; ?>">
                <div class="hj-comment-box" data-hj-comment-box>
                    <textarea rows="6" cols="50" name="text" id="hj-comment-textarea-reply" class="hj-comment-textarea" required></textarea>
                    <input type="hidden" name="author" value="<?php $this->remember('author'); ?>">
                    <input type="hidden" name="url" value="<?php $this->remember('url'); ?>">
                    <input type="hidden" name="mail" value="<?php $this->remember('mail'); ?>">
                    <div class="hj-comment-composer-actions" aria-label="<?php _e('评论操作'); ?>">
                        <div class="hj-comment-actions-left" aria-label="<?php _e('工具'); ?>">
                            <button class="hj-comment-icon-btn hj-comment-emoji" type="button" aria-label="<?php _e('表情'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-smile-icon lucide-smile" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/></svg>
                            </button>
                            <button class="hj-comment-icon-btn hj-comment-attach" type="button" aria-label="<?php _e('附件'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-paperclip-icon lucide-paperclip" aria-hidden="true"><path d="m16 6-8.414 8.586a2 2 0 0 0 2.829 2.829l8.414-8.586a4 4 0 1 0-5.657-5.657l-8.379 8.551a6 6 0 1 0 8.485 8.485l8.379-8.551"/></svg>
                            </button>
                            <button class="hj-comment-icon-btn hj-comment-private" type="button" aria-label="<?php _e('私信'); ?>" aria-pressed="false" data-hj-comment-private-toggle>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-line-dot-right-horizontal-icon lucide-line-dot-right-horizontal" aria-hidden="true"><path class="hj-private-line" d="M 3 12 L 15 12"/><circle class="hj-private-dot" cx="18" cy="12" r="3"/></svg>
                            </button>
                            <button class="hj-comment-icon-btn hj-comment-fullscreen-toggle" type="button" aria-label="<?php _e('展开全屏'); ?>" aria-pressed="false" data-hj-comment-fullscreen-toggle>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-maximize-icon lucide-maximize hj-comment-fullscreen-icon hj-comment-fullscreen-icon-max" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minimize-icon lucide-minimize hj-comment-fullscreen-icon hj-comment-fullscreen-icon-min" aria-hidden="true"><path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></svg>
                            </button>
                        </div>
                        <div class="hj-comment-actions-right" aria-label="<?php _e('提交'); ?>">
                            <button class="hj-comment-icon-btn hj-comment-login" type="button" aria-label="<?php _e('登录'); ?>" data-hj-open-login>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-icon lucide-user-round" aria-hidden="true"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                            </button>
                            <button class="hj-comment-icon-btn hj-comment-send" type="submit" aria-label="<?php _e('提交评论'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send-icon lucide-send" aria-hidden="true"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="hj-login-modal" data-hj-login-modal aria-hidden="true">
            <div class="hj-login-modal-backdrop" data-hj-login-backdrop aria-hidden="true"></div>
            <div class="hj-login-modal-panel" role="dialog" aria-modal="true" aria-label="<?php _e('登录'); ?>" tabindex="-1" data-hj-login-panel>
                <div class="hj-login-modal-title" aria-hidden="true"><?php _e('游客/登录'); ?></div>
                <form class="hj-login-modal-form hj-comment-form" method="post" action="<?php $this->options->loginAction(); ?>" autocomplete="on">
                    <p class="hj-comment-field">
                        <label for="hj-login-name" class="required"><?php _e('用户名/昵称'); ?></label>
                        <input type="text" id="hj-login-name" name="name" class="text" autocomplete="username" value="<?php $this->remember('author'); ?>" required>
                    </p>
                    <p class="hj-comment-field">
                        <label for="hj-login-url"<?php echo ($hjRequireUrl && !$hjUserLoggedIn) ? ' class="required"' : ''; ?>><?php _e('网站'); ?></label>
                        <input type="url" id="hj-login-url" name="url" class="text" autocomplete="url" placeholder="http://" value="<?php $this->remember('url'); ?>">
                    </p>
                    <p class="hj-comment-field">
                        <label for="hj-login-mail"<?php echo ($hjRequireMail && !$hjUserLoggedIn) ? ' class="required"' : ''; ?>><?php _e('邮箱'); ?></label>
                        <input type="email" id="hj-login-mail" name="mail" class="text" autocomplete="email" value="<?php $this->remember('mail'); ?>">
                    </p>
                    <p class="hj-comment-field">
                        <label for="hj-login-pass"><?php _e('账号密码'); ?></label>
                        <input type="password" id="hj-login-pass" name="password" class="text" autocomplete="current-password">
                    </p>
                    <?php if ($hjLoginReferer !== ''): ?>
                        <input type="hidden" name="referer" value="<?php echo hansJackEscape($hjLoginReferer); ?>">
                    <?php endif; ?>
                    <p class="hj-comment-actions">
                        <button type="submit" class="hj-login-modal-submit"><?php _e('保存'); ?></button>
                    </p>
                </form>
                <?php if ($hjGithubLoginUrl !== ''): ?>
                    <div class="hj-login-modal-oauth" aria-label="<?php _e('第三方登录'); ?>">
                        <a class="hj-login-modal-github" href="<?php echo hansJackEscape($hjGithubLoginUrl); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-github-icon lucide-github" aria-hidden="true"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/><path d="M9 18c-4.51 2-5-2-7-2"/></svg>
                            <span><?php _e('GitHub 登录'); ?></span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <h2 class="hj-comments-closed"><?php _e('评论已关闭'); ?></h2>
    <?php endif; ?>

    <?php if ($comments->have()): ?> 
        <?php
        $hjSortTarget = ($hjCommentsOrder === 'desc') ? 'asc' : 'desc';
        $hjSortLabel = ($hjSortTarget === 'asc') ? _t('切换为时间升序') : _t('切换为时间降序');
        ?>
        <div class="hj-comments-head" aria-label="<?php _e('评论'); ?>">
            <div class="hj-comments-head-title"><?php _e('评论'); ?> <span aria-hidden="true">·</span> <?php $this->commentsNum('%d'); ?><?php _e('条'); ?></div>
            <div class="hj-comments-head-actions" aria-label="<?php _e('操作'); ?>">
                <button class="hj-comments-head-btn hj-comments-refresh-btn" type="button" aria-label="<?php _e('刷新评论'); ?>" title="<?php _e('刷新评论'); ?>" data-hj-comments-refresh>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw-icon lucide-refresh-cw" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                </button>
                <button class="hj-comments-head-btn hj-comments-sort-btn" type="button" aria-label="<?php echo hansJackEscape($hjSortLabel); ?>" title="<?php echo hansJackEscape($hjSortLabel); ?>" data-hj-comments-sort-toggle>
                    <?php if ($hjSortTarget === 'asc'): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock-arrow-up-icon lucide-clock-arrow-up" aria-hidden="true"><path d="M12 6v6l1.56.78"/><path d="M13.227 21.925a10 10 0 1 1 8.767-9.588"/><path d="m14 18 4-4 4 4"/><path d="M18 22v-8"/></svg>
                    <?php else: ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock-arrow-down-icon lucide-clock-arrow-down" aria-hidden="true"><path d="M12 6v6l2 1"/><path d="M12.337 21.994a10 10 0 1 1 9.588-8.767"/><path d="m14 18 4 4 4-4"/><path d="M18 14v8"/></svg>
                    <?php endif; ?>
                </button>
            </div>
        </div>
        <?php $comments->listComments([
            'callback' => 'threadedComments',
            'avatarSize' => 32,
            // Prevent blurry avatars on HiDPI displays by using srcset (2x/3x).
            'avatarHighRes' => true,
            'replyWord' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-reply-icon lucide-reply"><path d="M20 18v-2a4 4 0 0 0-4-4H4"/><path d="m9 17-5-5 5-5"/></svg>'
        ]); ?>

        <?php $comments->pageNav(); ?>
    <?php endif; ?>
</section>
