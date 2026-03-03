<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
?>

<?php
$commentsOrder = 'asc';
try {
    $commentsOrder = strtoupper((string) ($this->options->commentsOrder ?? 'ASC'));
} catch (\Throwable $e) {
    $commentsOrder = 'ASC';
}
$commentsOrder = ($commentsOrder === 'DESC') ? 'desc' : 'asc';

$userLoggedIn = false;
try {
    $userLoggedIn = (bool) ($this->user && $this->user->hasLogin());
} catch (\Throwable $e) {
    $userLoggedIn = false;
}

$requireMail = false;
try {
    $requireMail = (bool) ($this->options->commentsRequireMail ?? false);
} catch (\Throwable $e) {
    $requireMail = false;
}

$requireUrl = false;
try {
    $requireUrl = (bool) ($this->options->commentsRequireUrl ?? false);
} catch (\Throwable $e) {
    $requireUrl = false;
}
?>

<section id="comments" class="comments" aria-label="<?php _e('评论'); ?>"
         data-comments-order="<?php echo escape($commentsOrder); ?>"
         data-user-logged="<?php echo $userLoggedIn ? '1' : '0'; ?>"
         data-comments-require-mail="<?php echo $requireMail ? '1' : '0'; ?>"
         data-comments-require-url="<?php echo $requireUrl ? '1' : '0'; ?>">
    <?php $this->comments()->to($comments); ?>

    <?php if ($this->allow('comment')): ?>
        <?php
        $loginReferer = '';
        try {
            $loginReferer = (string) $this->request->getRequestUrl();
        } catch (\Throwable $e) {
            $loginReferer = '';
        }

        $commentToken = '';
        try {
            if ($loginReferer !== '') {
                $commentToken = (string) $this->security->getToken($loginReferer);
            }
        } catch (\Throwable $e) {
            $commentToken = '';
        }

        $githubLoginUrl = '';
        try {
            if (function_exists('githubOauthEnabled') && githubOauthEnabled($this->options)) {
                $githubLoginUrl = githubOauthActionUrl('login', [
                    'return' => ($loginReferer !== '') ? $loginReferer : (string) $this->options->siteUrl,
                ]);
            }
        } catch (\Throwable $e) {
            $githubLoginUrl = '';
        }
        ?>
        <form method="post" action="<?php $this->commentUrl(); ?>" id="comment-form-top" class="comment-form comment-composer-form" role="form"
              data-comment-form data-comment-role="top" data-user-logged="<?php echo $userLoggedIn ? '1' : '0'; ?>">
            <div class="comment-box" data-comment-box>
                <textarea rows="6" cols="50" name="text" id="comment-textarea-top" class="comment-textarea" required></textarea>
                <input type="hidden" name="author" value="<?php $this->remember('author'); ?>">
                <input type="hidden" name="url" value="<?php $this->remember('url'); ?>">
                <input type="hidden" name="mail" value="<?php $this->remember('mail'); ?>">
                <div class="comment-composer-actions" aria-label="<?php _e('评论操作'); ?>">
                    <div class="comment-actions-left" aria-label="<?php _e('工具'); ?>">
                        <button class="comment-icon-btn comment-emoji" type="button" aria-label="<?php _e('表情'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-smile-icon lucide-smile" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/></svg>
                        </button>
                        <button class="comment-icon-btn comment-attach" type="button" aria-label="<?php _e('附件'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-paperclip-icon lucide-paperclip" aria-hidden="true"><path d="m16 6-8.414 8.586a2 2 0 0 0 2.829 2.829l8.414-8.586a4 4 0 1 0-5.657-5.657l-8.379 8.551a6 6 0 1 0 8.485 8.485l8.379-8.551"/></svg>
                        </button>
                        <button class="comment-icon-btn comment-private" type="button" aria-label="<?php _e('私信'); ?>" aria-pressed="false" data-comment-private-toggle>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-line-dot-right-horizontal-icon lucide-line-dot-right-horizontal" aria-hidden="true"><path class="private-line" d="M 3 12 L 15 12"/><circle class="private-dot" cx="18" cy="12" r="3"/></svg>
                        </button>
                        <button class="comment-icon-btn comment-fullscreen-toggle" type="button" aria-label="<?php _e('展开全屏'); ?>" aria-pressed="false" data-comment-fullscreen-toggle>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-maximize-icon lucide-maximize comment-fullscreen-icon comment-fullscreen-icon-max" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minimize-icon lucide-minimize comment-fullscreen-icon comment-fullscreen-icon-min" aria-hidden="true"><path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></svg>
                        </button>
                    </div>
                    <div class="comment-actions-right" aria-label="<?php _e('提交'); ?>">
                        <button class="comment-icon-btn comment-login" type="button" aria-label="<?php _e('登录'); ?>" data-open-login>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-icon lucide-user-round" aria-hidden="true"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                        </button>
                        <button class="comment-icon-btn comment-send" type="submit" aria-label="<?php _e('提交评论'); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send-icon lucide-send" aria-hidden="true"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <?php if ($commentToken !== ''): ?>
                <input type="hidden" name="_" value="<?php echo escape($commentToken); ?>">
            <?php endif; ?>
        </form>

        <div id="<?php $this->respondId(); ?>" class="respond respond" data-comment-respond data-user-logged="<?php echo $userLoggedIn ? '1' : '0'; ?>">
            <form method="post" action="<?php $this->commentUrl(); ?>" id="comment-form" class="comment-form comment-composer-form" role="form"
                  data-comment-form data-comment-role="reply" data-user-logged="<?php echo $userLoggedIn ? '1' : '0'; ?>">
                <div class="comment-box" data-comment-box>
                    <textarea rows="6" cols="50" name="text" id="comment-textarea-reply" class="comment-textarea" required></textarea>
                    <input type="hidden" name="author" value="<?php $this->remember('author'); ?>">
                    <input type="hidden" name="url" value="<?php $this->remember('url'); ?>">
                    <input type="hidden" name="mail" value="<?php $this->remember('mail'); ?>">
                    <div class="comment-composer-actions" aria-label="<?php _e('评论操作'); ?>">
                        <div class="comment-actions-left" aria-label="<?php _e('工具'); ?>">
                            <button class="comment-icon-btn comment-emoji" type="button" aria-label="<?php _e('表情'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-smile-icon lucide-smile" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/></svg>
                            </button>
                            <button class="comment-icon-btn comment-attach" type="button" aria-label="<?php _e('附件'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-paperclip-icon lucide-paperclip" aria-hidden="true"><path d="m16 6-8.414 8.586a2 2 0 0 0 2.829 2.829l8.414-8.586a4 4 0 1 0-5.657-5.657l-8.379 8.551a6 6 0 1 0 8.485 8.485l8.379-8.551"/></svg>
                            </button>
                            <button class="comment-icon-btn comment-private" type="button" aria-label="<?php _e('私信'); ?>" aria-pressed="false" data-comment-private-toggle>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-line-dot-right-horizontal-icon lucide-line-dot-right-horizontal" aria-hidden="true"><path class="private-line" d="M 3 12 L 15 12"/><circle class="private-dot" cx="18" cy="12" r="3"/></svg>
                            </button>
                            <button class="comment-icon-btn comment-fullscreen-toggle" type="button" aria-label="<?php _e('展开全屏'); ?>" aria-pressed="false" data-comment-fullscreen-toggle>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-maximize-icon lucide-maximize comment-fullscreen-icon comment-fullscreen-icon-max" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-minimize-icon lucide-minimize comment-fullscreen-icon comment-fullscreen-icon-min" aria-hidden="true"><path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></svg>
                            </button>
                        </div>
                        <div class="comment-actions-right" aria-label="<?php _e('提交'); ?>">
                            <button class="comment-icon-btn comment-login" type="button" aria-label="<?php _e('登录'); ?>" data-open-login>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round-icon lucide-user-round" aria-hidden="true"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                            </button>
                            <button class="comment-icon-btn comment-send" type="submit" aria-label="<?php _e('提交评论'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send-icon lucide-send" aria-hidden="true"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="login-modal" data-login-modal aria-hidden="true">
            <div class="login-modal-backdrop" data-login-backdrop aria-hidden="true"></div>
            <div class="login-modal-panel" role="dialog" aria-modal="true" aria-label="<?php _e('登录'); ?>" tabindex="-1" data-login-panel>
                <div class="login-modal-title" aria-hidden="true"><?php _e('游客/登录'); ?></div>
                <form class="login-modal-form comment-form" method="post" action="<?php $this->options->loginAction(); ?>" autocomplete="on">
                    <p class="comment-field">
                        <label for="login-name" class="required"><?php _e('用户名/昵称'); ?></label>
                        <input type="text" id="login-name" name="name" class="text" autocomplete="username" value="<?php $this->remember('author'); ?>" required>
                    </p>
                    <p class="comment-field">
                        <label for="login-url"<?php echo ($requireUrl && !$userLoggedIn) ? ' class="required"' : ''; ?>><?php _e('网站'); ?></label>
                        <input type="url" id="login-url" name="url" class="text" autocomplete="url" placeholder="http://" value="<?php $this->remember('url'); ?>">
                    </p>
                    <p class="comment-field">
                        <label for="login-mail"<?php echo ($requireMail && !$userLoggedIn) ? ' class="required"' : ''; ?>><?php _e('邮箱'); ?></label>
                        <input type="email" id="login-mail" name="mail" class="text" autocomplete="email" value="<?php $this->remember('mail'); ?>">
                    </p>
                    <p class="comment-field">
                        <label for="login-pass"><?php _e('账号密码'); ?></label>
                        <input type="password" id="login-pass" name="password" class="text" autocomplete="current-password">
                    </p>
                    <?php if ($loginReferer !== ''): ?>
                        <input type="hidden" name="referer" value="<?php echo escape($loginReferer); ?>">
                    <?php endif; ?>
                    <p class="comment-actions">
                        <button type="submit" class="login-modal-submit"><?php _e('保存'); ?></button>
                    </p>
                </form>
                <?php if ($githubLoginUrl !== ''): ?>
                    <div class="login-modal-oauth" aria-label="<?php _e('第三方登录'); ?>">
                        <a class="login-modal-github" href="<?php echo escape($githubLoginUrl); ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-github-icon lucide-github" aria-hidden="true"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/><path d="M9 18c-4.51 2-5-2-7-2"/></svg>
                            <span><?php _e('GitHub 登录'); ?></span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <h2 class="comments-closed"><?php _e('评论已关闭'); ?></h2>
    <?php endif; ?>

    <?php if ($comments->have()): ?> 
        <?php
        $sortTarget = ($commentsOrder === 'desc') ? 'asc' : 'desc';
        $sortLabel = ($sortTarget === 'asc') ? _t('切换为时间升序') : _t('切换为时间降序');
        ?>
        <div class="comments-head" aria-label="<?php _e('评论'); ?>">
            <div class="comments-head-title">
                <span class="comments-head-title-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-messages-square-icon lucide-messages-square"><path d="M16 10a2 2 0 0 1-2 2H6.828a2 2 0 0 0-1.414.586l-2.202 2.202A.71.71 0 0 1 2 14.286V4a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/><path d="M20 9a2 2 0 0 1 2 2v10.286a.71.71 0 0 1-1.212.502l-2.202-2.202A2 2 0 0 0 17.172 19H10a2 2 0 0 1-2-2v-1"/></svg>
                </span>
                <span class="comments-head-title-text"><?php _e('评论'); ?> <span aria-hidden="true">·</span> <?php $this->commentsNum('%d'); ?></span>
            </div>
            <div class="comments-head-actions" aria-label="<?php _e('操作'); ?>">
                <button class="comments-head-btn comments-refresh-btn" type="button" aria-label="<?php _e('刷新评论'); ?>" title="<?php _e('刷新评论'); ?>" data-comments-refresh>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw-icon lucide-refresh-cw" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                </button>
                <button class="comments-head-btn comments-sort-btn" type="button" aria-label="<?php echo escape($sortLabel); ?>" title="<?php echo escape($sortLabel); ?>" data-comments-sort-toggle>
                    <?php if ($sortTarget === 'asc'): ?>
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
