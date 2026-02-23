<?php
/**
 * 友情链接（Vue3Admin 面板）
 *
 * 说明：该模板会在你创建一个独立页面，且 slug 为 `links` 时自动生效。
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$noticeType = '';
$noticeMessage = '';

$export = \Typecho\Plugin::export();
$v3aEnabled = isset($export['activated']['Vue3Admin']);

$request = $this->request;
$security = \Helper::security();
$csrfRef = (string) $request->getRequestUrl();

if (!function_exists('hansJackV3aLinksStr')) {
    /**
     * @param mixed $value
     */
    function hansJackV3aLinksStr($value, int $max = 255): string
    {
        $s = trim((string) $value);
        if ($s === '') {
            return '';
        }

        if (function_exists('mb_substr')) {
            return (string) mb_substr($s, 0, $max);
        }

        return substr($s, 0, $max);
    }
}

$applySettings = [
    'allowTypeSelect' => 0,
    'defaultType' => 'friend',
    'allowedTypes' => [
        'friend' => 1,
        'collection' => 0,
    ],
    'required' => [
        'email' => 0,
        'avatar' => 0,
        'description' => 0,
        'message' => 0,
    ],
];

try {
    $raw = (string) ($this->options->v3a_friend_apply_settings ?? '');
    if (trim($raw) !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $applySettings['allowTypeSelect'] = !empty($decoded['allowTypeSelect']) ? 1 : 0;

            $allowed = is_array($decoded['allowedTypes'] ?? null) ? $decoded['allowedTypes'] : [];
            $applySettings['allowedTypes']['friend'] = !empty($allowed['friend']) ? 1 : 0;
            $applySettings['allowedTypes']['collection'] = !empty($allowed['collection']) ? 1 : 0;
            if (empty($applySettings['allowedTypes']['friend']) && empty($applySettings['allowedTypes']['collection'])) {
                $applySettings['allowedTypes']['friend'] = 1;
            }

            $dt = strtolower(trim((string) ($decoded['defaultType'] ?? 'friend')));
            if (!in_array($dt, ['friend', 'collection'], true)) {
                $dt = 'friend';
            }
            if (empty($applySettings['allowedTypes'][$dt])) {
                $dt = !empty($applySettings['allowedTypes']['friend']) ? 'friend' : 'collection';
            }
            $applySettings['defaultType'] = $dt;

            $req = is_array($decoded['required'] ?? null) ? $decoded['required'] : [];
            $applySettings['required']['email'] = !empty($req['email']) ? 1 : 0;
            $applySettings['required']['avatar'] = !empty($req['avatar']) ? 1 : 0;
            $applySettings['required']['description'] = !empty($req['description']) ? 1 : 0;
            $applySettings['required']['message'] = !empty($req['message']) ? 1 : 0;
        }
    }
} catch (\Throwable $e) {
}

$links = [];

try {
    if ($v3aEnabled) {
        $pdo = null;
        try {
            if (class_exists('\\TypechoPlugin\\Vue3Admin\\LocalStorage')) {
                $pdo = \TypechoPlugin\Vue3Admin\LocalStorage::pdo();
            }
        } catch (\Throwable $e) {
            $pdo = null;
        }

        if (!$pdo) {
            throw new \RuntimeException('Local storage unavailable: please enable PHP extension pdo_sqlite.');
        }

        if (
            isset($_SERVER['REQUEST_METHOD'])
            && strtoupper((string) $_SERVER['REQUEST_METHOD']) === 'POST'
            && (string) ($request->get('v3a_do') ?? '') === 'apply'
        ) {
            $token = (string) ($request->get('_') ?? '');
            $expected = (string) $security->getToken($csrfRef);
            if ($token === '' || !hash_equals($expected, $token)) {
                $noticeType = 'error';
                $noticeMessage = '请求已过期，请刷新页面后重试。';
            } elseif (trim((string) ($request->get('v3a_hp') ?? '')) !== '') {
                $noticeType = 'error';
                $noticeMessage = '提交失败。';
            } else {
                $name = hansJackV3aLinksStr($request->get('name', ''), 100);
                $url = hansJackV3aLinksStr($request->get('url', ''), 255);
                $avatar = hansJackV3aLinksStr($request->get('avatar', ''), 500);
                $description = hansJackV3aLinksStr($request->get('description', ''), 200);
                $email = hansJackV3aLinksStr($request->get('email', ''), 190);
                $message = hansJackV3aLinksStr($request->get('message', ''), 500);

                $type = (string) ($applySettings['defaultType'] ?? 'friend');
                if (!in_array($type, ['friend', 'collection'], true)) {
                    $type = 'friend';
                }

                if (!empty($applySettings['allowTypeSelect'])) {
                    $t = strtolower(hansJackV3aLinksStr($request->get('type', ''), 20));
                    if (in_array($t, ['friend', 'collection'], true) && !empty($applySettings['allowedTypes'][$t])) {
                        $type = $t;
                    }
                }

                if (empty($applySettings['allowedTypes'][$type])) {
                    $type = !empty($applySettings['allowedTypes']['friend']) ? 'friend' : 'collection';
                }

                if ($name === '') {
                    $noticeType = 'error';
                    $noticeMessage = '请填写名称。';
                } elseif ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
                    $noticeType = 'error';
                    $noticeMessage = '请填写正确的网址（需包含 http/https）。';
                } elseif (!empty($applySettings['required']['email']) && $email === '') {
                    $noticeType = 'error';
                    $noticeMessage = '请填写邮箱。';
                } elseif ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                    $noticeType = 'error';
                    $noticeMessage = '邮箱格式不正确。';
                } elseif (!empty($applySettings['required']['avatar']) && $avatar === '') {
                    $noticeType = 'error';
                    $noticeMessage = '请填写头像。';
                } elseif ($avatar !== '' && stripos($avatar, 'data:image/') !== 0 && filter_var($avatar, FILTER_VALIDATE_URL) === false) {
                    $noticeType = 'error';
                    $noticeMessage = '头像格式不正确（需填写图片地址或 data:image/...）。';
                } elseif (!empty($applySettings['required']['description']) && $description === '') {
                    $noticeType = 'error';
                    $noticeMessage = '请填写描述。';
                } elseif (!empty($applySettings['required']['message']) && $message === '') {
                    $noticeType = 'error';
                    $noticeMessage = '请填写留言。';
                } else {
                    $dup = 0;
                    try {
                        $stmt = $pdo->prepare('SELECT COUNT(id) FROM v3a_friend_link WHERE url = :url LIMIT 1');
                        $stmt->execute([':url' => $url]);
                        $dup = (int) ($stmt->fetchColumn() ?: 0);
                    } catch (\Throwable $e) {
                        $dup = 0;
                    }
                    if ($dup > 0) {
                        $noticeType = 'error';
                        $noticeMessage = '该网址已存在于友链列表中。';
                    } else {
                        $pending = 0;
                        try {
                            $stmt = $pdo->prepare(
                                'SELECT COUNT(id) FROM v3a_friend_link_apply WHERE url = :url AND status = :status LIMIT 1'
                            );
                            $stmt->execute([':url' => $url, ':status' => 0]);
                            $pending = (int) ($stmt->fetchColumn() ?: 0);
                        } catch (\Throwable $e) {
                            $pending = 0;
                        }

                        if ($pending > 0) {
                            $noticeType = 'error';
                            $noticeMessage = '该网址已提交过申请，请等待审核。';
                        } else {
                            $rows = [
                                'name' => $name,
                                'url' => $url,
                                'avatar' => $avatar,
                                'description' => $description,
                                'type' => $type,
                                'email' => $email,
                                'message' => $message,
                                'status' => 0,
                                'created' => time(),
                            ];

                            $cols = array_keys($rows);
                            $placeholders = array_map(function ($c) {
                                return ':' . $c;
                            }, $cols);
                            $stmt = $pdo->prepare(
                                'INSERT INTO v3a_friend_link_apply ('
                                    . implode(',', $cols)
                                    . ') VALUES ('
                                    . implode(',', $placeholders)
                                    . ')'
                            );
                            $params = [];
                            foreach ($rows as $k => $v) {
                                $params[':' . $k] = $v;
                            }
                            $stmt->execute($params);

                            try {
                                if (class_exists('\\TypechoPlugin\\Vue3Admin\\Plugin')) {
                                    \TypechoPlugin\Vue3Admin\Plugin::notifyFriendLinkApply($rows);
                                }
                            } catch (\Throwable $e) {
                            }

                            $noticeType = 'success';
                            $noticeMessage = '已提交申请，请等待审核。';
                        }
                    }
                }
            }
        }

        $stmt = $pdo->prepare('SELECT id,name,url,avatar,description,type FROM v3a_friend_link WHERE status = :status ORDER BY created DESC');
        $stmt->execute([':status' => 1]);
        $links = (array) $stmt->fetchAll();
    }
} catch (\Throwable $e) {
    $noticeType = 'error';
    $noticeMessage = $noticeMessage ?: ('加载失败：' . $e->getMessage());
}

$this->need('header.php');
?>

<main class="hj-main" role="main">
    <article class="hj-page hj-links-page" aria-label="<?php _e('友情链接'); ?>">
        <div class="hj-article-content">
            <?php $this->content(); ?>
        </div>

        <section class="hj-links" aria-label="<?php _e('友链'); ?>">
            <?php if (!$v3aEnabled): ?>
                <p class="hj-links-empty"><?php _e('未启用 Vue3Admin 插件，无法加载友链数据。'); ?></p>
            <?php else: ?>
                <?php if (!empty($links)): ?>
                    <ul class="hj-links-list" aria-label="<?php _e('友情链接列表'); ?>">
                        <?php foreach ((array) $links as $link):
                            $rawName = (string) ($link['name'] ?? '');
                            $name = hansJackEscape($rawName);
                            $url = hansJackEscape((string) ($link['url'] ?? ''));
                            $desc = hansJackEscape((string) ($link['description'] ?? ''));

                            $avatar = trim((string) ($link['avatar'] ?? ''));
                            $avatar = $avatar !== '' ? hansJackEscape($avatar) : '';

                            $type = strtolower(trim((string) ($link['type'] ?? 'friend')));
                            $typeLabel = $type === 'collection' ? '收藏' : '朋友';
                            $typeLabel = hansJackEscape($typeLabel);

                            $initial = '—';
                            $trimName = trim($rawName);
                            if ($trimName !== '') {
                                if (function_exists('mb_substr')) {
                                    $initial = (string) mb_substr($trimName, 0, 1);
                                } else {
                                    $initial = substr($trimName, 0, 1);
                                }
                            }
                            $initial = hansJackEscape($initial);
                        ?>
                            <li class="hj-links-item" data-hj-link-type="<?php echo $typeLabel; ?>">
                                <div class="hj-links-avatar" aria-hidden="true">
                                    <?php if ($avatar !== ''): ?>
                                        <img src="<?php echo $avatar; ?>" alt="" loading="lazy">
                                    <?php else: ?>
                                        <span><?php echo $initial; ?></span>
                                    <?php endif; ?>
                                </div>

                                <div class="hj-links-body">
                                    <div class="hj-links-name-row">
                                        <a class="hj-links-name" href="<?php echo $url; ?>" target="_blank" rel="noreferrer"><?php echo $name !== '' ? $name : '—'; ?></a>
                                        <span class="hj-links-type" aria-hidden="true"><?php echo $typeLabel; ?></span>
                                    </div>
                                    <?php if ($desc !== ''): ?>
                                        <div class="hj-links-desc"><?php echo $desc; ?></div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="hj-links-empty"><?php _e('暂无友链'); ?></p>
                <?php endif; ?>

                <div class="hj-links-apply" id="v3a-apply" aria-label="<?php _e('申请友链'); ?>">
                    <?php if ($noticeMessage !== ''): ?>
                        <div class="hj-links-notice <?php echo ($noticeType === 'success') ? 'is-success' : 'is-error'; ?>">
                            <?php echo hansJackEscape($noticeMessage); ?>
                        </div>
                    <?php endif; ?>

                    <section class="hj-links-step hj-links-step-check" aria-label="<?php _e('确认前置条件'); ?>">
                        <div class="hj-links-step-head">
                            <span class="hj-links-step-num" aria-hidden="true">1</span>
                            <h2 class="hj-links-step-title"><?php _e('确认前置条件'); ?></h2>
                        </div>
                        <div class="hj-links-step-divider" aria-hidden="true"></div>
                        <ul class="hj-links-checklist" aria-label="<?php _e('前置条件'); ?>">
                            <li class="hj-links-checklist-item">
                                <label class="hj-links-check">
                                    <input class="hj-links-check-input" type="checkbox">
                                    <span class="hj-links-check-text"><?php _e('类型为「个人博客」，已运营时间不少于 60 天'); ?></span>
                                </label>
                            </li>
                            <li class="hj-links-checklist-item">
                                <label class="hj-links-check">
                                    <input class="hj-links-check-input" type="checkbox">
                                    <span class="hj-links-check-text"><?php _e('现有文章数量 >= 5 篇，且原创文章占比 >= 90%'); ?></span>
                                </label>
                            </li>
                            <li class="hj-links-checklist-item">
                                <label class="hj-links-check">
                                    <input class="hj-links-check-input" type="checkbox">
                                    <span class="hj-links-check-text"><?php _e('网站访问流畅，内容合法合规，无过多商业广告'); ?></span>
                                </label>
                            </li>
                            <li class="hj-links-checklist-item">
                                <label class="hj-links-check">
                                    <input class="hj-links-check-input" type="checkbox">
                                    <span class="hj-links-check-text"><?php _e('全站支持 HTTPS 访问'); ?></span>
                                </label>
                            </li>
                            <li class="hj-links-checklist-item">
                                <label class="hj-links-check">
                                    <input class="hj-links-check-input" type="checkbox">
                                    <span class="hj-links-check-text"><?php _e('已将本站添加为贵站的友情链接'); ?></span>
                                </label>
                            </li>
                        </ul>
                        <div class="hj-links-step-actions">
                            <button class="hj-links-btn hj-links-confirm-btn" type="button" data-hj-links-confirm><?php _e('确定'); ?></button>
                        </div>
                    </section>

                    <section class="hj-links-step hj-links-step-form" aria-label="<?php _e('填写站点信息'); ?>" hidden aria-hidden="true">
                        <div class="hj-links-step-head">
                            <span class="hj-links-step-num" aria-hidden="true">2</span>
                            <h2 class="hj-links-step-title"><?php _e('填写站点信息'); ?></h2>
                        </div>
                        <div class="hj-links-step-divider" aria-hidden="true"></div>

                        <form class="hj-comment-form hj-links-apply-form" method="post" action="<?php $this->permalink(); ?>#v3a-apply" autocomplete="on">
                            <input type="hidden" name="v3a_do" value="apply">
                            <input type="hidden" name="v3a_hp" value="">
                            <input type="hidden" name="_" value="<?php echo hansJackEscape((string) $security->getToken($csrfRef)); ?>">

                            <div class="hj-links-apply-grid">
                                <p class="hj-comment-field">
                                    <label for="hj-links-url" class="required"><?php _e('站点地址'); ?></label>
                                    <input type="url" id="hj-links-url" name="url" class="text" required maxlength="255" placeholder="https://example.com">
                                </p>
                                <p class="hj-comment-field">
                                    <label for="hj-links-name" class="required"><?php _e('站点名称'); ?></label>
                                    <input type="text" id="hj-links-name" name="name" class="text" required maxlength="100" placeholder="<?php _e('站点名称'); ?>">
                                </p>
                                <p class="hj-comment-field">
                                    <label for="hj-links-email"<?php echo !empty($applySettings['required']['email']) ? ' class="required"' : ''; ?>><?php _e('邮箱'); ?></label>
                                    <input type="email" id="hj-links-email" name="email" class="text" maxlength="190" placeholder="<?php _e('可留空'); ?>" <?php echo !empty($applySettings['required']['email']) ? 'required' : ''; ?>>
                                </p>
                                <p class="hj-comment-field">
                                    <label for="hj-links-avatar"<?php echo !empty($applySettings['required']['avatar']) ? ' class="required"' : ''; ?>><?php _e('头像'); ?></label>
                                    <input type="url" id="hj-links-avatar" name="avatar" class="text" maxlength="500" placeholder="https://..." <?php echo !empty($applySettings['required']['avatar']) ? 'required' : ''; ?>>
                                </p>
                                <p class="hj-comment-field hj-links-field-wide">
                                    <label for="hj-links-description"<?php echo !empty($applySettings['required']['description']) ? ' class="required"' : ''; ?>><?php _e('描述'); ?></label>
                                    <input type="text" id="hj-links-description" name="description" class="text" maxlength="200" placeholder="<?php _e('一句话介绍'); ?>" <?php echo !empty($applySettings['required']['description']) ? 'required' : ''; ?>>
                                </p>

                                <?php if (!empty($applySettings['allowTypeSelect'])): ?>
                                    <p class="hj-comment-field">
                                        <label for="hj-links-type"><?php _e('类型'); ?></label>
                                        <select id="hj-links-type" class="hj-links-select" name="type">
                                            <?php if (!empty($applySettings['allowedTypes']['friend'])): ?>
                                                <option value="friend" <?php echo ($applySettings['defaultType'] ?? 'friend') === 'friend' ? 'selected' : ''; ?>><?php _e('朋友'); ?></option>
                                            <?php endif; ?>
                                            <?php if (!empty($applySettings['allowedTypes']['collection'])): ?>
                                                <option value="collection" <?php echo ($applySettings['defaultType'] ?? 'friend') === 'collection' ? 'selected' : ''; ?>><?php _e('收藏'); ?></option>
                                            <?php endif; ?>
                                        </select>
                                    </p>
                                <?php else: ?>
                                    <input type="hidden" name="type" value="<?php echo hansJackEscape((string) ($applySettings['defaultType'] ?? 'friend')); ?>">
                                <?php endif; ?>
                            </div>

                            <p class="hj-comment-field">
                                <label for="hj-links-message"<?php echo !empty($applySettings['required']['message']) ? ' class="required"' : ''; ?>><?php _e('留言'); ?></label>
                                <textarea id="hj-links-message" rows="4" name="message" class="textarea" maxlength="500" placeholder="<?php _e('可留空'); ?>" <?php echo !empty($applySettings['required']['message']) ? 'required' : ''; ?>></textarea>
                            </p>

                            <p class="hj-comment-actions">
                                <button type="submit" class="hj-links-btn hj-links-submit-btn"><?php _e('提交申请'); ?></button>
                            </p>
                        </form>
                    </section>
                </div>
            <?php endif; ?>
        </section>
    </article>
</main>

<?php $this->need('footer.php'); ?>
