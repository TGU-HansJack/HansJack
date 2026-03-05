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

        $qqLoginUrl = '';
        try {
            if (function_exists('qqOauthEnabled') && qqOauthEnabled($this->options)) {
                $qqLoginUrl = qqOauthActionUrl('login', [
                    'return' => ($loginReferer !== '') ? $loginReferer : (string) $this->options->siteUrl,
                ]);
            }
        } catch (\Throwable $e) {
            $qqLoginUrl = '';
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
                <?php if ($githubLoginUrl !== '' || $qqLoginUrl !== ''): ?>
                    <div class="login-modal-oauth" aria-label="<?php _e('第三方登录'); ?>">
                        <?php if ($githubLoginUrl !== ''): ?>
                            <a class="login-modal-github" href="<?php echo escape($githubLoginUrl); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-github-icon lucide-github" aria-hidden="true"><path d="M15 22v-4a4.8 4.8 0 0 0-1-3.5c3 0 6-2 6-5.5.08-1.25-.27-2.48-1-3.5.28-1.15.28-2.35 0-3.5 0 0-1 0-3 1.5-2.64-.5-5.36-.5-8 0C6 2 5 2 5 2c-.3 1.15-.3 2.35 0 3.5A5.403 5.403 0 0 0 4 9c0 3.5 3 5.5 6 5.5-.39.49-.68 1.05-.85 1.65-.17.6-.22 1.23-.15 1.85v4"/><path d="M9 18c-4.51 2-5-2-7-2"/></svg>
                                <span><?php _e('GitHub 登录'); ?></span>
                            </a>
                        <?php endif; ?>
                        <?php if ($qqLoginUrl !== ''): ?>
                            <a class="login-modal-qq" href="<?php echo escape($qqLoginUrl); ?>">
                                <svg t="1772696218441" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="17514" width="200" height="200" aria-hidden="true"><path d="M511.09761 957.257c-80.159 0-153.737-25.019-201.11-62.386-24.057 6.702-54.831 17.489-74.252 30.864-16.617 11.439-14.546 23.106-11.55 27.816 13.15 20.689 225.583 13.211 286.912 6.767v-3.061z" fill="#FAAD08" p-id="17515"></path><path d="M496.65061 957.257c80.157 0 153.737-25.019 201.11-62.386 24.057 6.702 54.83 17.489 74.253 30.864 16.616 11.439 14.543 23.106 11.55 27.816-13.15 20.689-225.584 13.211-286.914 6.767v-3.061z" fill="#FAAD08" p-id="17516"></path><path d="M497.12861 474.524c131.934-0.876 237.669-25.783 273.497-35.34 8.541-2.28 13.11-6.364 13.11-6.364 0.03-1.172 0.542-20.952 0.542-31.155C784.27761 229.833 701.12561 57.173 496.64061 57.162 292.15661 57.173 209.00061 229.832 209.00061 401.665c0 10.203 0.516 29.983 0.547 31.155 0 0 3.717 3.821 10.529 5.67 33.078 8.98 140.803 35.139 276.08 36.034h0.972z" fill="#000000" p-id="17517"></path><path d="M860.28261 619.782c-8.12-26.086-19.204-56.506-30.427-85.72 0 0-6.456-0.795-9.718 0.148-100.71 29.205-222.773 47.818-315.792 46.695h-0.962C410.88561 582.017 289.65061 563.617 189.27961 534.698 185.44461 533.595 177.87261 534.063 177.87261 534.063 166.64961 563.276 155.56661 593.696 147.44761 619.782 108.72961 744.168 121.27261 795.644 130.82461 796.798c20.496 2.474 79.78-93.637 79.78-93.637 0 97.66 88.324 247.617 290.576 248.996a718.01 718.01 0 0 1 5.367 0C708.80161 950.778 797.12261 800.822 797.12261 703.162c0 0 59.284 96.111 79.783 93.637 9.55-1.154 22.093-52.63-16.623-177.017" fill="#000000" p-id="17518"></path><path d="M434.38261 316.917c-27.9 1.24-51.745-30.106-53.24-69.956-1.518-39.877 19.858-73.207 47.764-74.454 27.875-1.224 51.703 30.109 53.218 69.974 1.527 39.877-19.853 73.2-47.742 74.436m206.67-69.956c-1.494 39.85-25.34 71.194-53.24 69.956-27.888-1.238-49.269-34.559-47.742-74.435 1.513-39.868 25.341-71.201 53.216-69.974 27.909 1.247 49.285 34.576 47.767 74.453" fill="#FFFFFF" p-id="17519"></path><path d="M683.94261 368.627c-7.323-17.609-81.062-37.227-172.353-37.227h-0.98c-91.29 0-165.031 19.618-172.352 37.227a6.244 6.244 0 0 0-0.535 2.505c0 1.269 0.393 2.414 1.006 3.386 6.168 9.765 88.054 58.018 171.882 58.018h0.98c83.827 0 165.71-48.25 171.881-58.016a6.352 6.352 0 0 0 1.002-3.395c0-0.897-0.2-1.736-0.531-2.498" fill="#FAAD08" p-id="17520"></path><path d="M467.63161 256.377c1.26 15.886-7.377 30-19.266 31.542-11.907 1.544-22.569-10.083-23.836-25.978-1.243-15.895 7.381-30.008 19.25-31.538 11.927-1.549 22.607 10.088 23.852 25.974m73.097 7.935c2.533-4.118 19.827-25.77 55.62-17.886 9.401 2.07 13.75 5.116 14.668 6.316 1.355 1.77 1.726 4.29 0.352 7.684-2.722 6.725-8.338 6.542-11.454 5.226-2.01-0.85-26.94-15.889-49.905 6.553-1.579 1.545-4.405 2.074-7.085 0.242-2.678-1.834-3.786-5.553-2.196-8.135" fill="#000000" p-id="17521"></path><path d="M504.33261 584.495h-0.967c-63.568 0.752-140.646-7.504-215.286-21.92-6.391 36.262-10.25 81.838-6.936 136.196 8.37 137.384 91.62 223.736 220.118 224.996H506.48461c128.498-1.26 211.748-87.612 220.12-224.996 3.314-54.362-0.547-99.938-6.94-136.203-74.654 14.423-151.745 22.684-215.332 21.927" fill="#FFFFFF" p-id="17522"></path><path d="M323.27461 577.016v137.468s64.957 12.705 130.031 3.91V591.59c-41.225-2.262-85.688-7.304-130.031-14.574" fill="#EB1C26" p-id="17523"></path><path d="M788.09761 432.536s-121.98 40.387-283.743 41.539h-0.962c-161.497-1.147-283.328-41.401-283.744-41.539l-40.854 106.952c102.186 32.31 228.837 53.135 324.598 51.926l0.96-0.002c95.768 1.216 222.4-19.61 324.6-51.924l-40.855-106.952z" fill="#EB1C26" p-id="17524"></path></svg>
                                <span><?php _e('QQ 登录'); ?></span>
                            </a>
                        <?php endif; ?>
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
            'avatarSize' => 36,
            // Prevent blurry avatars on HiDPI displays by using srcset (2x/3x).
            'avatarHighRes' => true,
            'replyWord' => '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-reply-icon lucide-reply"><path d="M20 18v-2a4 4 0 0 0-4-4H4"/><path d="m9 17-5-5 5-5"/></svg>'
        ]); ?>

        <?php $comments->pageNav(); ?>
    <?php endif; ?>
</section>
