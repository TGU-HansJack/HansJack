<?php

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

use Typecho\Common;
use Typecho\Cookie;
use Typecho\Db;
use Widget\Archive;
use Widget\Options;

/**
 * 主题设置项
 */
function themeConfig($form)
{
    $options = Options::alloc();

    $cvUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'hjCvUrl',
        null,
        '',
        _t('CV 链接'),
        _t('留空则不显示；示例：https://example.com/cv 或 /cv。')
    );
    $form->addInput($cvUrl);

    $githubUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'hjGithubUrl',
        null,
        '',
        _t('GitHub 链接'),
        _t('留空则不显示；示例：https://github.com/username。')
    );
    $form->addInput($githubUrl);

    $creativeUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'hjCreativeUrl',
        null,
        '',
        _t('创意链接'),
        _t('留空则不显示；示例：https://example.com/creative。')
    );
    $form->addInput($creativeUrl);

    $rewardImageUrl = new \Typecho\Widget\Helper\Form\Element\Text(
        'hjRewardImageUrl',
        null,
        '',
        _t('赞赏码图片链接'),
        _t('用于文章页 FAB 赞赏弹窗；支持完整 URL 或站内相对路径（如 /usr/uploads/reward.png），留空则不显示。')
    );
    $form->addInput($rewardImageUrl);

    $githubOauthEnabled = new \Typecho\Widget\Helper\Form\Element\Radio(
        'hjGithubOauthEnabled',
        [
            '0' => _t('关闭'),
            '1' => _t('开启'),
        ],
        '0',
        _t('GitHub OAuth2 登录'),
        _t('开启后，可在评论登录弹窗中使用 GitHub 登录。')
    );
    $form->addInput($githubOauthEnabled);

    $githubOauthClientId = new \Typecho\Widget\Helper\Form\Element\Text(
        'hjGithubOauthClientId',
        null,
        '',
        _t('GitHub Client ID'),
        _t('GitHub OAuth App 的 Client ID。')
    );
    $form->addInput($githubOauthClientId);

    $githubOauthClientSecret = new \Typecho\Widget\Helper\Form\Element\Password(
        'hjGithubOauthClientSecret',
        null,
        '',
        _t('GitHub Client Secret'),
        _t('GitHub OAuth App 的 Client Secret。')
    );
    $form->addInput($githubOauthClientSecret);

    $githubOauthScope = new \Typecho\Widget\Helper\Form\Element\Text(
        'hjGithubOauthScope',
        null,
        'read:user user:email',
        _t('GitHub OAuth Scope'),
        _t('默认：read:user user:email。')
    );
    $form->addInput($githubOauthScope);

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Hidden(
        'hjGithubBindUid',
        null,
        trim((string) ($options->hjGithubBindUid ?? ''))
    ));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Hidden(
        'hjGithubBindId',
        null,
        trim((string) ($options->hjGithubBindId ?? ''))
    ));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Hidden(
        'hjGithubBindLogin',
        null,
        trim((string) ($options->hjGithubBindLogin ?? ''))
    ));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Hidden(
        'hjGithubBindAvatar',
        null,
        trim((string) ($options->hjGithubBindAvatar ?? ''))
    ));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Hidden(
        'hjGithubBindEmail',
        null,
        trim((string) ($options->hjGithubBindEmail ?? ''))
    ));
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Hidden(
        'hjGithubBindAt',
        null,
        trim((string) ($options->hjGithubBindAt ?? ''))
    ));

    $bindPanel = new \Typecho\Widget\Helper\Form\Element\Fake('hjGithubBindPanel', '');
    $bindPanel->label(_t('GitHub账号绑定'));
    $bindPanel->description(hansJackGithubBindingPanelHtml($options));
    $form->addInput($bindPanel);

    $icpBeian = new \Typecho\Widget\Helper\Form\Element\Text(
        'hjIcpBeian',
        null,
        '',
        _t('ICP备案号'),
        _t('留空则不显示；示例：京ICP备12345678号-1。链接会跳转到工信部备案查询。')
    );
    $form->addInput($icpBeian);

    $mpsBeian = new \Typecho\Widget\Helper\Form\Element\Text(
        'hjMpsBeian',
        null,
        '',
        _t('公安备案号'),
        _t('留空则不显示；示例：京公网安备 11000002000001号。链接会跳转到公安备案查询。')
    );
    $form->addInput($mpsBeian);

    $footerCustomCode = new \Typecho\Widget\Helper\Form\Element\Textarea(
        'hjFooterCustomCode',
        null,
        '',
        _t('底部自定义代码'),
        _t('将输出到页脚左侧。支持 HTML（请自行确保代码安全）。')
    );
    $form->addInput($footerCustomCode);
}

/**
 * Theme entry hook.
 * Keep root "posts"/"notes" archives at a fixed 15 items per page.
 */
function themeInit(Archive $archive)
{
    hansJackHandleCommentUploadRequest($archive);
    hansJackHandleMemoryReactionRequest($archive);
    hansJackHandleGithubOauthRequest($archive);
    hansJackEnableFeedStylesheet($archive);

    if ($archive->is('category', 'posts') || $archive->is('category', 'notes')) {
        $archive->parameter->pageSize = 15;
    }
}

function hansJackCommentUploadJson(array $payload, int $status = 200): void
{
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('X-Robots-Tag: noindex');
    }

    if (function_exists('http_response_code')) {
        http_response_code($status);
    }

    $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($json)) {
        $json = '{"ok":false,"message":"json_encode_failed"}';
    }

    echo $json;
    exit;
}

function hansJackHandleCommentUploadRequest(Archive $archive): void
{
    $exists = false;
    $uploadFlag = '';
    try {
        $uploadFlag = trim((string) $archive->request->get('hj_comment_upload', '', $exists));
    } catch (\Throwable $e) {
        $exists = false;
        $uploadFlag = '';
    }

    if (!$exists) {
        return;
    }

    if ($uploadFlag === '' || $uploadFlag === '0' || strtolower($uploadFlag) === 'false') {
        return;
    }

    if (!$archive->request->isPost()) {
        hansJackCommentUploadJson([
            'ok' => false,
            'message' => _t('请求方式不支持'),
        ], 405);
    }

    $user = null;
    try {
        $user = \Typecho\Widget::widget('Widget_User');
    } catch (\Throwable $e) {
        $user = null;
    }

    $isAdmin = false;
    if ($user) {
        try {
            $isAdmin = $user->hasLogin() && $user->pass('administrator', true);
        } catch (\Throwable $e) {
            $isAdmin = false;
        }
    }

    if (!$isAdmin) {
        hansJackCommentUploadJson([
            'ok' => false,
            'message' => _t('仅管理员可以上传附件'),
        ], 403);
    }

    $token = '';
    $referer = '';
    try {
        $token = trim((string) $archive->request->get('_', ''));
    } catch (\Throwable $e) {
        $token = '';
    }
    try {
        $referer = trim((string) $archive->request->getReferer());
    } catch (\Throwable $e) {
        $referer = '';
    }

    $security = null;
    try {
        $security = \Typecho\Widget::widget('Widget_Security');
    } catch (\Throwable $e) {
        $security = null;
    }

    $expectedToken = '';
    if ($security && method_exists($security, 'getToken') && $referer !== '') {
        try {
            $expectedToken = (string) $security->getToken($referer);
        } catch (\Throwable $e) {
            $expectedToken = '';
        }
    }

    if ($token === '' || $expectedToken === '' || !hash_equals($expectedToken, $token)) {
        hansJackCommentUploadJson([
            'ok' => false,
            'message' => _t('安全校验失败，请刷新后重试'),
        ], 403);
    }

    $file = $_FILES['file'] ?? null;
    if (!is_array($file)) {
        hansJackCommentUploadJson([
            'ok' => false,
            'message' => _t('未检测到上传文件'),
        ], 400);
    }

    $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($errorCode !== UPLOAD_ERR_OK) {
        $errorMap = [
            UPLOAD_ERR_INI_SIZE => _t('文件超出服务器上传限制'),
            UPLOAD_ERR_FORM_SIZE => _t('文件超出表单上传限制'),
            UPLOAD_ERR_PARTIAL => _t('文件上传不完整，请重试'),
            UPLOAD_ERR_NO_FILE => _t('请选择要上传的文件'),
            UPLOAD_ERR_NO_TMP_DIR => _t('服务器临时目录不可用'),
            UPLOAD_ERR_CANT_WRITE => _t('服务器无法写入上传文件'),
            UPLOAD_ERR_EXTENSION => _t('上传被服务器扩展中断'),
        ];
        hansJackCommentUploadJson([
            'ok' => false,
            'message' => $errorMap[$errorCode] ?? _t('文件上传失败'),
        ], 400);
    }

    $tmpPath = trim((string) ($file['tmp_name'] ?? ''));
    if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
        hansJackCommentUploadJson([
            'ok' => false,
            'message' => _t('上传文件无效'),
        ], 400);
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0) {
        hansJackCommentUploadJson([
            'ok' => false,
            'message' => _t('上传文件为空'),
        ], 400);
    }

    $maxSize = 20 * 1024 * 1024;
    if ($size > $maxSize) {
        hansJackCommentUploadJson([
            'ok' => false,
            'message' => _t('文件大小不能超过 20MB'),
        ], 400);
    }

    $originalName = trim((string) ($file['name'] ?? ''));
    $originalName = str_replace(["\r", "\n"], ' ', $originalName);
    $originalName = trim((string) preg_replace('/\s+/u', ' ', $originalName));
    $originalName = basename($originalName);
    if ($originalName === '') {
        $originalName = 'attachment';
    }

    $ext = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
    $allowedExts = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'svg',
        'pdf', 'txt', 'md',
        'zip', 'rar', '7z', 'tar', 'gz',
        'mp3', 'wav', 'ogg', 'mp4', 'webm', 'mov',
    ];
    if ($ext === '' || !in_array($ext, $allowedExts, true)) {
        hansJackCommentUploadJson([
            'ok' => false,
            'message' => _t('该文件类型不允许上传'),
        ], 400);
    }

    $mime = '';
    if (function_exists('finfo_open')) {
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $detected = @finfo_file($finfo, $tmpPath);
            if (is_string($detected)) {
                $mime = strtolower(trim($detected));
            }
            @finfo_close($finfo);
        }
    }

    if ($mime === '') {
        $mime = strtolower(trim((string) ($file['type'] ?? '')));
    }
    if ($mime === '') {
        $mime = 'application/octet-stream';
    }

    $blockedMimes = [
        'application/x-php',
        'text/x-php',
        'text/php',
        'application/x-httpd-php',
    ];
    if (in_array($mime, $blockedMimes, true)) {
        hansJackCommentUploadJson([
            'ok' => false,
            'message' => _t('不允许上传此文件'),
        ], 400);
    }

    $relativeDir = 'usr/uploads/comment/' . date('Y/m');
    $saveDir = rtrim((string) __TYPECHO_ROOT_DIR__, '/\\') . DIRECTORY_SEPARATOR
        . str_replace('/', DIRECTORY_SEPARATOR, $relativeDir);
    if (!is_dir($saveDir) && !@mkdir($saveDir, 0755, true)) {
        hansJackCommentUploadJson([
            'ok' => false,
            'message' => _t('服务器无法创建上传目录'),
        ], 500);
    }

    $random = '';
    try {
        $random = bin2hex(random_bytes(8));
    } catch (\Throwable $e) {
        $random = substr(md5(uniqid((string) mt_rand(), true)), 0, 16);
    }

    $saveName = date('YmdHis') . '-' . $random . '.' . $ext;
    $savePath = $saveDir . DIRECTORY_SEPARATOR . $saveName;
    if (!@move_uploaded_file($tmpPath, $savePath)) {
        hansJackCommentUploadJson([
            'ok' => false,
            'message' => _t('文件保存失败，请重试'),
        ], 500);
    }
    @chmod($savePath, 0644);

    $options = Options::alloc();
    $relativeFile = $relativeDir . '/' . $saveName;
    $fileUrl = Common::url($relativeFile, (string) $options->siteUrl);

    $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'svg'];
    $isImage = in_array($ext, $imageExts, true) || strpos($mime, 'image/') === 0;

    hansJackCommentUploadJson([
        'ok' => true,
        'url' => $fileUrl,
        'name' => $originalName,
        'mime' => $mime,
        'isImage' => $isImage,
        'size' => $size,
    ]);
}

function hansJackMemoryReactionAllowedEmojis(): array
{
    return ['👍', '❤️', '😂', '😮', '😢', '😡', '🎉', '👏', '🔥', '🤔', '👀', '🙏', '💯', '🚀'];
}

function hansJackMemoryReactionCacheFilePath(): string
{
    $base = rtrim((string) __TYPECHO_ROOT_DIR__, '/\\');
    return $base
        . DIRECTORY_SEPARATOR . 'usr'
        . DIRECTORY_SEPARATOR . 'themes'
        . DIRECTORY_SEPARATOR . 'HansJack'
        . DIRECTORY_SEPARATOR . 'cache'
        . DIRECTORY_SEPARATOR . 'memory-reactions.json';
}

function hansJackMemoryReactionDefaultStore(): array
{
    return [
        'version' => 1,
        'updated' => 0,
        'comments' => [],
    ];
}

function hansJackMemoryReactionNormalizeStore($store): array
{
    $normalized = hansJackMemoryReactionDefaultStore();
    if (!is_array($store)) {
        return $normalized;
    }

    $commentsRaw = $store['comments'] ?? [];
    if (!is_array($commentsRaw)) {
        $commentsRaw = [];
    }

    $comments = [];
    foreach ($commentsRaw as $coidRaw => $reactionRows) {
        $coid = (int) $coidRaw;
        if ($coid <= 0 || !is_array($reactionRows)) {
            continue;
        }

        $bucket = [];
        foreach ($reactionRows as $ipHashRaw => $reaction) {
            $ipHash = trim((string) $ipHashRaw);
            if ($ipHash === '' || !is_array($reaction)) {
                continue;
            }

            $emoji = trim((string) ($reaction['emoji'] ?? ''));
            if ($emoji === '') {
                continue;
            }

            $bucket[$ipHash] = [
                'emoji' => $emoji,
                'updated' => (int) ($reaction['updated'] ?? 0),
            ];
        }

        if (!empty($bucket)) {
            $comments[(string) $coid] = $bucket;
        }
    }

    $normalized['comments'] = $comments;
    $normalized['updated'] = (int) ($store['updated'] ?? 0);

    return $normalized;
}

function hansJackMemoryReactionEnsureCacheDir(): bool
{
    $dir = dirname(hansJackMemoryReactionCacheFilePath());
    if (is_dir($dir)) {
        return true;
    }

    return @mkdir($dir, 0755, true);
}

function hansJackMemoryReactionReadStore(): array
{
    $path = hansJackMemoryReactionCacheFilePath();
    if (!is_file($path)) {
        return hansJackMemoryReactionDefaultStore();
    }

    $fp = @fopen($path, 'rb');
    if (!is_resource($fp)) {
        return hansJackMemoryReactionDefaultStore();
    }

    $raw = '';
    if (@flock($fp, LOCK_SH)) {
        $content = stream_get_contents($fp);
        if (is_string($content)) {
            $raw = $content;
        }
        @flock($fp, LOCK_UN);
    } else {
        $content = stream_get_contents($fp);
        if (is_string($content)) {
            $raw = $content;
        }
    }
    @fclose($fp);

    if ($raw === '') {
        return hansJackMemoryReactionDefaultStore();
    }

    $decoded = json_decode($raw, true);
    return hansJackMemoryReactionNormalizeStore($decoded);
}

function hansJackMemoryReactionParseCoids($raw): array
{
    $parts = [];
    if (is_array($raw)) {
        foreach ($raw as $item) {
            $parts[] = (string) $item;
        }
    } else {
        $rawText = trim((string) $raw);
        if ($rawText !== '') {
            $parts = preg_split('/[\s,;|]+/u', $rawText) ?: [];
        }
    }

    $map = [];
    foreach ($parts as $part) {
        $id = (int) $part;
        if ($id <= 0) {
            continue;
        }
        $map[$id] = $id;
        if (count($map) >= 200) {
            break;
        }
    }

    return array_values($map);
}

function hansJackMemoryReactionCommentCid(int $coid): int
{
    if ($coid <= 0) {
        return 0;
    }

    $db = hansJackGithubDb();
    if (!is_object($db)) {
        return 0;
    }

    try {
        $row = $db->fetchRow(
            $db->select('cid')
                ->from('table.comments')
                ->where('coid = ?', $coid)
                ->limit(1)
        );
    } catch (\Throwable $e) {
        return 0;
    }

    if (is_array($row)) {
        return (int) ($row['cid'] ?? 0);
    }
    if (is_object($row)) {
        return (int) ($row->cid ?? 0);
    }

    return 0;
}

function hansJackMemoryReactionIsMemoryComment(int $coid): bool
{
    static $cache = [];

    if ($coid <= 0) {
        return false;
    }
    if (array_key_exists($coid, $cache)) {
        return (bool) $cache[$coid];
    }

    $cid = hansJackMemoryReactionCommentCid($coid);
    if ($cid <= 0) {
        $cache[$coid] = false;
        return false;
    }

    $db = hansJackGithubDb();
    if (!is_object($db)) {
        $cache[$coid] = false;
        return false;
    }

    try {
        $row = $db->fetchRow(
            $db->select('slug', 'type')
                ->from('table.contents')
                ->where('cid = ?', $cid)
                ->limit(1)
        );
    } catch (\Throwable $e) {
        $cache[$coid] = false;
        return false;
    }

    $slug = '';
    $type = '';
    if (is_array($row)) {
        $slug = trim((string) ($row['slug'] ?? ''));
        $type = trim((string) ($row['type'] ?? ''));
    } elseif (is_object($row)) {
        $slug = trim((string) ($row->slug ?? ''));
        $type = trim((string) ($row->type ?? ''));
    }

    $ok = (strtolower($slug) === 'memos' && strtolower($type) === 'page');
    $cache[$coid] = $ok;
    return $ok;
}

function hansJackMemoryReactionClientIp(): string
{
    $keys = [
        'HTTP_CF_CONNECTING_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR',
    ];

    foreach ($keys as $key) {
        $value = trim((string) ($_SERVER[$key] ?? ''));
        if ($value === '') {
            continue;
        }

        $candidates = [$value];
        if ($key === 'HTTP_X_FORWARDED_FOR') {
            $candidates = preg_split('/\s*,\s*/', $value) ?: [$value];
        }

        foreach ($candidates as $candidate) {
            $candidate = trim((string) $candidate);
            if ($candidate === '') {
                continue;
            }
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return $candidate;
            }
        }
    }

    return '';
}

function hansJackMemoryReactionClientHash(): string
{
    $ip = hansJackMemoryReactionClientIp();
    if ($ip === '') {
        $ip = trim((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown-client'));
    }

    return hash('sha256', $ip . '|' . (string) __TYPECHO_ROOT_DIR__);
}

function hansJackMemoryReactionBuildPayloads(array $store, array $coids, string $ipHash, array $allowedEmojis): array
{
    $allowedMap = [];
    foreach ($allowedEmojis as $emoji) {
        $emojiText = trim((string) $emoji);
        if ($emojiText === '') {
            continue;
        }
        $allowedMap[$emojiText] = true;
    }

    $payload = [];
    foreach ($coids as $coidRaw) {
        $coid = (int) $coidRaw;
        if ($coid <= 0) {
            continue;
        }

        $bucket = $store['comments'][(string) $coid] ?? [];
        if (!is_array($bucket)) {
            $bucket = [];
        }

        $counts = [];
        $selected = '';
        foreach ($bucket as $storedHash => $reaction) {
            if (!is_array($reaction)) {
                continue;
            }
            $emoji = trim((string) ($reaction['emoji'] ?? ''));
            if ($emoji === '' || !isset($allowedMap[$emoji])) {
                continue;
            }

            $counts[$emoji] = (int) ($counts[$emoji] ?? 0) + 1;
            if ((string) $storedHash === $ipHash) {
                $selected = $emoji;
            }
        }

        if ($selected !== '' && !isset($allowedMap[$selected])) {
            $selected = '';
        }

        $orderedCounts = [];
        $total = 0;
        foreach ($allowedEmojis as $emoji) {
            $emojiText = trim((string) $emoji);
            if ($emojiText === '') {
                continue;
            }
            $count = (int) ($counts[$emojiText] ?? 0);
            if ($count <= 0) {
                continue;
            }
            $orderedCounts[$emojiText] = $count;
            $total += $count;
        }

        $payload[(string) $coid] = [
            'selected' => $selected,
            'counts' => $orderedCounts,
            'total' => $total,
        ];
    }

    return $payload;
}

function hansJackHandleMemoryReactionRequest(Archive $archive): void
{
    $exists = false;
    $flag = '';
    try {
        $flag = trim((string) $archive->request->get('hj_memory_reaction', '', $exists));
    } catch (\Throwable $e) {
        $exists = false;
        $flag = '';
    }

    if (!$exists) {
        return;
    }
    if ($flag === '' || $flag === '0' || strtolower($flag) === 'false') {
        return;
    }

    $allowedEmojis = hansJackMemoryReactionAllowedEmojis();
    $allowedMap = [];
    foreach ($allowedEmojis as $emoji) {
        $key = trim((string) $emoji);
        if ($key === '') {
            continue;
        }
        $allowedMap[$key] = true;
    }

    $clientHash = hansJackMemoryReactionClientHash();
    if ($clientHash === '') {
        hansJackCommentUploadJson([
            'ok' => false,
            'message' => _t('无法识别客户端'),
        ], 400);
    }

    if ($archive->request->isPost()) {
        $action = '';
        try {
            $action = strtolower(trim((string) $archive->request->get('action', 'set')));
        } catch (\Throwable $e) {
            $action = 'set';
        }

        if ($action === '' || $action === 'set') {
            $coid = 0;
            $emoji = '';
            try {
                $coid = (int) $archive->request->get('coid', 0);
            } catch (\Throwable $e) {
                $coid = 0;
            }
            try {
                $emoji = trim((string) $archive->request->get('emoji', ''));
            } catch (\Throwable $e) {
                $emoji = '';
            }

            if ($coid <= 0 || !hansJackMemoryReactionIsMemoryComment($coid)) {
                hansJackCommentUploadJson([
                    'ok' => false,
                    'message' => _t('评论不存在或不支持互动'),
                ], 400);
            }
            if ($emoji === '' || !isset($allowedMap[$emoji])) {
                hansJackCommentUploadJson([
                    'ok' => false,
                    'message' => _t('互动表情不合法'),
                ], 400);
            }

            if (!hansJackMemoryReactionEnsureCacheDir()) {
                hansJackCommentUploadJson([
                    'ok' => false,
                    'message' => _t('缓存目录不可写'),
                ], 500);
            }

            $path = hansJackMemoryReactionCacheFilePath();
            $fp = @fopen($path, 'c+');
            if (!is_resource($fp)) {
                hansJackCommentUploadJson([
                    'ok' => false,
                    'message' => _t('缓存文件打开失败'),
                ], 500);
            }

            $store = hansJackMemoryReactionDefaultStore();
            $writeOk = false;

            if (@flock($fp, LOCK_EX)) {
                rewind($fp);
                $raw = stream_get_contents($fp);
                if (!is_string($raw)) {
                    $raw = '';
                }

                if ($raw !== '') {
                    $decoded = json_decode($raw, true);
                    $store = hansJackMemoryReactionNormalizeStore($decoded);
                }

                $commentKey = (string) $coid;
                if (!isset($store['comments'][$commentKey]) || !is_array($store['comments'][$commentKey])) {
                    $store['comments'][$commentKey] = [];
                }
                $store['comments'][$commentKey][$clientHash] = [
                    'emoji' => $emoji,
                    'updated' => time(),
                ];
                $store['updated'] = time();

                $json = json_encode($store, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if (is_string($json)) {
                    rewind($fp);
                    if (@ftruncate($fp, 0)) {
                        $written = @fwrite($fp, $json);
                        @fflush($fp);
                        $writeOk = ($written !== false);
                    }
                }

                @flock($fp, LOCK_UN);
            }

            @fclose($fp);

            if (!$writeOk) {
                hansJackCommentUploadJson([
                    'ok' => false,
                    'message' => _t('互动写入失败，请稍后重试'),
                ], 500);
            }

            @chmod($path, 0644);
            $comments = hansJackMemoryReactionBuildPayloads($store, [$coid], $clientHash, $allowedEmojis);
            hansJackCommentUploadJson([
                'ok' => true,
                'comments' => $comments,
                'coids' => [(int) $coid],
                'emojis' => $allowedEmojis,
            ]);
        }
    }

    $coids = [];
    try {
        $coids = hansJackMemoryReactionParseCoids($archive->request->get('coids', ''));
    } catch (\Throwable $e) {
        $coids = [];
    }

    if (empty($coids)) {
        $single = 0;
        try {
            $single = (int) $archive->request->get('coid', 0);
        } catch (\Throwable $e) {
            $single = 0;
        }
        if ($single > 0) {
            $coids = [$single];
        }
    }

    $validCoids = [];
    foreach ($coids as $coidRaw) {
        $coid = (int) $coidRaw;
        if ($coid <= 0) {
            continue;
        }
        if (!hansJackMemoryReactionIsMemoryComment($coid)) {
            continue;
        }
        $validCoids[] = $coid;
    }

    $store = hansJackMemoryReactionReadStore();
    $comments = hansJackMemoryReactionBuildPayloads($store, $validCoids, $clientHash, $allowedEmojis);
    hansJackCommentUploadJson([
        'ok' => true,
        'comments' => $comments,
        'coids' => $validCoids,
        'emojis' => $allowedEmojis,
    ]);
}

/**
 * Feed output strategy:
 * - Browser requests (Accept: text/html): render a readable HTML feed page.
 * - Feed reader requests: keep raw XML output (no browser-side XSLT dependency).
 */
function hansJackEnableFeedStylesheet(Archive $archive): void
{
    static $registered = false;
    if ($registered) {
        return;
    }

    $isFeed = false;
    try {
        $isFeed = !empty($archive->parameter->isFeed);
    } catch (\Throwable $e) {
        $isFeed = false;
    }

    if (!$isFeed) {
        return;
    }

    $renderHtml = hansJackShouldRenderFeedAsHtml();

    $registered = true;
    ob_start(function ($buffer) use ($renderHtml) {
        return hansJackHandleFeedOutput((string) $buffer, $renderHtml);
    });
}

function hansJackShouldRenderFeedAsHtml(): bool
{
    $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($method !== 'GET') {
        return false;
    }

    $format = strtolower(trim((string) ($_GET['format'] ?? '')));
    if ($format === 'xml' || $format === 'raw') {
        return false;
    }
    if ($format === 'html') {
        return true;
    }

    $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
    if ($accept === '') {
        return false;
    }

    return strpos($accept, 'text/html') !== false;
}

function hansJackHandleFeedOutput(string $buffer, bool $renderHtml): string
{
    if ($buffer === '' || strpos($buffer, '<?xml') === false) {
        return $buffer;
    }

    if ($renderHtml) {
        $html = hansJackRenderFeedHtmlFromXml($buffer);
        if ($html !== '') {
            if (!headers_sent()) {
                header('Content-Type: text/html; charset=UTF-8');
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            }
            return $html;
        }
    }

    return $buffer;
}

function hansJackFeedHtmlEscape(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function hansJackFeedSafeUrl(string $url, string $fallback = '#'): string
{
    $url = trim($url);
    if ($url === '') {
        return $fallback;
    }

    if (strpos($url, '//') === 0) {
        return 'https:' . $url;
    }

    if (preg_match('/^https?:\\/\\//i', $url)) {
        return $url;
    }

    return $fallback;
}

function hansJackFeedXPathString(\DOMXPath $xp, string $expr, ?\DOMNode $ctx = null): string
{
    $query = 'string(' . $expr . ')';
    $raw = $ctx ? $xp->evaluate($query, $ctx) : $xp->evaluate($query);
    return trim((string) $raw);
}

/**
 * Parse RSS/Atom XML string and render readable HTML (no browser XSLT dependency).
 */
function hansJackRenderFeedHtmlFromXml(string $xml): string
{
    if (!class_exists('\\DOMDocument') || !class_exists('\\DOMXPath')) {
        return '';
    }

    $doc = new \DOMDocument();
    $previousUseErrors = libxml_use_internal_errors(true);
    $loaded = false;
    try {
        $loaded = $doc->loadXML($xml, LIBXML_NOCDATA | LIBXML_NONET);
    } catch (\Throwable $e) {
        $loaded = false;
    }
    libxml_clear_errors();
    libxml_use_internal_errors($previousUseErrors);

    if (!$loaded || !$doc->documentElement) {
        return '';
    }

    $xp = new \DOMXPath($doc);
    $xp->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
    $xp->registerNamespace('rss1', 'http://purl.org/rss/1.0/');
    $xp->registerNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
    $xp->registerNamespace('dc', 'http://purl.org/dc/elements/1.1/');

    $root = $doc->documentElement;
    $rootName = strtolower((string) $root->localName);
    $rootNs = strtolower((string) ($root->namespaceURI ?? ''));

    $siteTitle = '订阅源';
    $siteLink = '#';
    $siteDesc = '订阅源页面';
    $feedUrl = '';
    $lastUpdate = '未知';
    $items = [];

    if ($rootName === 'rss') {
        $siteTitle = hansJackFeedXPathString($xp, '/rss/channel/title');
        $siteLink = hansJackFeedXPathString($xp, '/rss/channel/link');
        $siteDesc = hansJackFeedXPathString($xp, '/rss/channel/description');
        $feedUrl = hansJackFeedXPathString($xp, "/rss/channel/atom:link[@rel='self'][1]/@href");
        if ($feedUrl === '') {
            $feedUrl = $siteLink;
        }
        $lastUpdate = hansJackFeedXPathString($xp, '/rss/channel/lastBuildDate');
        if ($lastUpdate === '') {
            $lastUpdate = hansJackFeedXPathString($xp, '/rss/channel/pubDate');
        }

        $nodes = $xp->query('/rss/channel/item');
        if ($nodes) {
            foreach ($nodes as $node) {
                $title = hansJackFeedXPathString($xp, 'title', $node);
                $link = hansJackFeedXPathString($xp, 'link', $node);
                $time = hansJackFeedXPathString($xp, 'pubDate', $node);

                $tags = [];
                $tagNodes = $xp->query('category', $node);
                if ($tagNodes) {
                    foreach ($tagNodes as $tagNode) {
                        $tag = trim((string) $tagNode->textContent);
                        if ($tag !== '') {
                            $tags[] = $tag;
                        }
                    }
                }

                $items[] = [
                    'title' => $title !== '' ? $title : '未命名文章',
                    'url' => hansJackFeedSafeUrl($link),
                    'time' => $time !== '' ? $time : '未知时间',
                    'tags' => $tags,
                ];
            }
        }
    } elseif ($rootName === 'feed' && $rootNs === 'http://www.w3.org/2005/atom') {
        $siteTitle = hansJackFeedXPathString($xp, '/atom:feed/atom:title');
        $siteLink = hansJackFeedXPathString($xp, "/atom:feed/atom:link[@rel='alternate'][1]/@href");
        if ($siteLink === '') {
            $siteLink = hansJackFeedXPathString($xp, '/atom:feed/atom:link[1]/@href');
        }
        $siteDesc = hansJackFeedXPathString($xp, '/atom:feed/atom:subtitle');
        $feedUrl = hansJackFeedXPathString($xp, "/atom:feed/atom:link[@rel='self'][1]/@href");
        if ($feedUrl === '') {
            $feedUrl = hansJackFeedXPathString($xp, '/atom:feed/atom:id');
        }
        if ($feedUrl === '') {
            $feedUrl = $siteLink;
        }
        $lastUpdate = hansJackFeedXPathString($xp, '/atom:feed/atom:updated');

        $nodes = $xp->query('/atom:feed/atom:entry');
        if ($nodes) {
            foreach ($nodes as $node) {
                $title = hansJackFeedXPathString($xp, 'atom:title', $node);
                $link = hansJackFeedXPathString($xp, "atom:link[@rel='alternate'][1]/@href", $node);
                if ($link === '') {
                    $link = hansJackFeedXPathString($xp, 'atom:link[1]/@href', $node);
                }
                $time = hansJackFeedXPathString($xp, 'atom:published', $node);
                if ($time === '') {
                    $time = hansJackFeedXPathString($xp, 'atom:updated', $node);
                }

                $tags = [];
                $tagNodes = $xp->query('atom:category', $node);
                if ($tagNodes) {
                    foreach ($tagNodes as $tagNode) {
                        $tag = '';
                        if ($tagNode instanceof \DOMElement && $tagNode->hasAttribute('term')) {
                            $tag = trim((string) $tagNode->getAttribute('term'));
                        }
                        if ($tag !== '') {
                            $tags[] = $tag;
                        }
                    }
                }

                $items[] = [
                    'title' => $title !== '' ? $title : '未命名文章',
                    'url' => hansJackFeedSafeUrl($link),
                    'time' => $time !== '' ? $time : '未知时间',
                    'tags' => $tags,
                ];
            }
        }
    } elseif ($rootName === 'rdf') {
        $siteTitle = hansJackFeedXPathString($xp, '/rdf:RDF/rss1:channel/rss1:title');
        $siteLink = hansJackFeedXPathString($xp, '/rdf:RDF/rss1:channel/rss1:link');
        $siteDesc = hansJackFeedXPathString($xp, '/rdf:RDF/rss1:channel/rss1:description');
        $feedUrl = $siteLink;
        $lastUpdate = hansJackFeedXPathString($xp, '/rdf:RDF/rss1:channel/dc:date');

        $nodes = $xp->query('/rdf:RDF/rss1:item');
        if ($nodes) {
            foreach ($nodes as $node) {
                $title = hansJackFeedXPathString($xp, 'rss1:title', $node);
                $link = hansJackFeedXPathString($xp, 'rss1:link', $node);
                $time = hansJackFeedXPathString($xp, 'dc:date', $node);

                $items[] = [
                    'title' => $title !== '' ? $title : '未命名文章',
                    'url' => hansJackFeedSafeUrl($link),
                    'time' => $time !== '' ? $time : '未知时间',
                    'tags' => [],
                ];
            }
        }
    } else {
        return '';
    }

    if ($siteTitle === '') {
        $siteTitle = '订阅源';
    }
    if ($siteDesc === '') {
        $siteDesc = '订阅源页面';
    }
    if ($lastUpdate === '') {
        $lastUpdate = '未知';
    }

    $siteLinkSafe = hansJackFeedSafeUrl($siteLink);
    $feedUrlSafe = hansJackFeedSafeUrl($feedUrl, $siteLinkSafe);

    $titleEsc = hansJackFeedHtmlEscape($siteTitle);
    $siteDescEsc = hansJackFeedHtmlEscape($siteDesc);
    $siteLinkEsc = hansJackFeedHtmlEscape($siteLinkSafe);
    $feedUrlEsc = hansJackFeedHtmlEscape($feedUrlSafe);
    $lastUpdateEsc = hansJackFeedHtmlEscape($lastUpdate);

    $itemHtml = '';
    foreach ($items as $index => $item) {
        $titleRaw = trim((string) ($item['title'] ?? ''));
        if ($titleRaw === '') {
            $titleRaw = '未命名文章';
        }
        $itemTitle = hansJackFeedHtmlEscape($titleRaw);
        $itemUrl = hansJackFeedHtmlEscape(hansJackFeedSafeUrl((string) ($item['url'] ?? ''), $siteLinkSafe));

        $itemTimeRaw = trim((string) ($item['time'] ?? ''));
        $itemTimestamp = 0;
        if ($itemTimeRaw !== '') {
            $parsed = strtotime($itemTimeRaw);
            if ($parsed !== false && $parsed > 0) {
                $itemTimestamp = (int) $parsed;
            }
        }

        $displayTime = $itemTimeRaw !== '' ? $itemTimeRaw : '未知时间';
        $datetimeAttr = '';
        $createdAttr = '';
        if ($itemTimestamp > 0) {
            $displayTime = date('Y/m/d-H:i:s', $itemTimestamp);
            $datetimeAttr = ' datetime="' . hansJackFeedHtmlEscape(date(DATE_ATOM, $itemTimestamp)) . '"';
            $createdAttr = ' data-hj-post-created="' . $itemTimestamp . '" data-hj-post-modified="' . $itemTimestamp . '"';
        }
        $itemTime = hansJackFeedHtmlEscape($displayTime);

        $tagsHtml = '';
        $tags = is_array($item['tags'] ?? null) ? $item['tags'] : [];
        foreach ($tags as $tag) {
            $tagText = trim((string) $tag);
            if ($tagText === '') {
                continue;
            }
            $tagsHtml .= '<span class="hj-posts-tag">#' . hansJackFeedHtmlEscape($tagText) . '</span>';
        }

        $itemHtml .= '<li class="hj-posts-item"'
            . ' data-hj-post-original-index="' . (int) $index . '"'
            . $createdAttr . '>';
        $itemHtml .= '<div class="hj-posts-item-left">';
        $itemHtml .= '<a class="hj-posts-title" href="' . $itemUrl . '">' . $itemTitle . '</a>';
        $itemHtml .= '<time class="hj-posts-date"' . $datetimeAttr . '>' . $itemTime . '</time>';
        $itemHtml .= '</div>';
        $itemHtml .= '<div class="hj-posts-item-right" aria-label="标签">' . $tagsHtml . '</div>';
        $itemHtml .= '</li>';
    }

    if ($itemHtml === '') {
        $itemHtml = '<li class="hj-posts-empty">当前订阅源暂无可展示内容。</li>';
    }

    $options = Options::alloc();
    $theme = trim((string) ($options->theme ?? ''));
    $themeStyleHref = '';
    if ($theme !== '') {
        $themeStyleHref = (string) $options->themeUrl('style.css', $theme);
    }
    $themeStyleTag = '';
    if ($themeStyleHref !== '') {
        $themeStyleTag = '<link rel="stylesheet" href="' . hansJackFeedHtmlEscape($themeStyleHref) . '">';
    }

    return '<!DOCTYPE html><html lang="zh-CN"><head>'
        . '<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">'
        . '<title>' . $titleEsc . ' · 订阅源</title>'
        . $themeStyleTag
        . '<style>body{margin:0}.hj-feed-page{padding:1.1rem 0 1.8rem}.hj-feed-head{margin-bottom:.75rem}.hj-feed-head h1{margin:0 0 .3rem;font-size:clamp(1.38rem,2.7vw,2rem);line-height:1.25}.hj-feed-head p{margin:0;color:var(--hj-muted-day)}.hj-feed-note{margin-bottom:1rem}.copy-btn{position:relative;margin-left:.35rem;border:1px solid #2a2a28;border-radius:4px;background:#2a2a28;color:#fffffd;font-family:var(--hj-font-ui);font-size:.78rem;line-height:1;padding:.25rem .5rem;cursor:pointer}.copy-btn:hover,.copy-btn:focus-visible{border-color:#1f1f1d;background:#1f1f1d}.copy-btn::after{content:attr(data-copy-tip);position:absolute;left:50%;bottom:calc(100% + 6px);transform:translate(-50%,4px);padding:.14rem .42rem;border-radius:4px;background:var(--hj-nav-block-bg);color:var(--hj-nav-block-fg);font-family:var(--hj-font-ui);font-size:.72rem;line-height:1.2;white-space:nowrap;opacity:0;visibility:hidden;pointer-events:none;transition:opacity .14s ease,transform .14s ease,visibility .14s ease}.copy-btn.is-tip::after{opacity:1;visibility:visible;transform:translate(-50%,0)}.hj-feed-foot{margin-top:1.1rem;font-family:var(--hj-font-ui);font-size:.84rem;color:var(--hj-muted-day)}.hj-feed-foot p{margin:.22rem 0}@media (max-width:980px){.hj-feed-page{padding:1rem 0 1.35rem}}</style>'
        . '</head><body class="hj-page-posts"><div class="hj-shell">'
        . '<main class="hj-main hj-feed-page" role="main"><section class="hj-posts" aria-label="订阅文章列表">'
        . '<div class="hj-feed-head"><h1><a href="' . $siteLinkEsc . '">' . $titleEsc . '</a></h1><p>' . $siteDescEsc . '</p></div>'
        . '<div class="hj-posts-main"><div class="hj-article-content hj-feed-note"><blockquote><p>本页面是内容订阅源。</p><p>您可以在任何支持的阅读器中添加当前地址来订阅此内容，以便及时获取最新更新。</p><p>订阅地址: <code id="feed-url">'
        . $feedUrlEsc . '</code><button type="button" class="copy-btn" data-copy-tip="" onclick="copyFeedUrl()">复制</button></p></blockquote></div>'
        . '<ul class="hj-posts-list" aria-label="文章">' . $itemHtml . '</ul>'
        . '<footer class="hj-feed-foot"><p>这是订阅源页面。访问 <a href="' . $siteLinkEsc . '">' . $titleEsc
        . '</a> 以获得完整的网站体验。</p><p>最后更新: ' . $lastUpdateEsc
        . '</p></footer></div></section></main></div><script>function showCopyTip(message){var btn=document.querySelector(".copy-btn");if(!btn){return;}btn.setAttribute("data-copy-tip",message||"已复制");btn.classList.add("is-tip");var timer=Number(btn.getAttribute("data-copy-tip-timer")||"0");if(timer){window.clearTimeout(timer);}var next=window.setTimeout(function(){btn.classList.remove("is-tip");btn.setAttribute("data-copy-tip","");btn.removeAttribute("data-copy-tip-timer");},1400);btn.setAttribute("data-copy-tip-timer",String(next));}function fallbackCopyText(text){var ok=false;var el=document.createElement("textarea");el.value=text;el.setAttribute("readonly","readonly");el.style.position="fixed";el.style.opacity="0";el.style.pointerEvents="none";document.body.appendChild(el);el.focus();el.select();try{ok=document.execCommand("copy");}catch(e){ok=false;}document.body.removeChild(el);return ok;}function copyFeedUrl(){var node=document.getElementById("feed-url");var text=node?(node.textContent||""):"";if(!text){showCopyTip("未获取到订阅地址");return;}if(navigator.clipboard&&navigator.clipboard.writeText){navigator.clipboard.writeText(text).then(function(){showCopyTip("已复制");}).catch(function(){showCopyTip(fallbackCopyText(text)?"已复制":"复制失败");});return;}showCopyTip(fallbackCopyText(text)?"已复制":"复制失败");}</script></body></html>';
}

function hansJackGithubBindingPanelHtml(Options $options): string
{
    $enabled = hansJackGithubOauthEnabled($options);
    $clientId = trim((string) ($options->hjGithubOauthClientId ?? ''));
    $clientSecret = trim((string) ($options->hjGithubOauthClientSecret ?? ''));

    $bindLogin = trim((string) ($options->hjGithubBindLogin ?? ''));
    $bindId = trim((string) ($options->hjGithubBindId ?? ''));
    $bindAt = trim((string) ($options->hjGithubBindAt ?? ''));

    $bindStatus = _t('未绑定');
    if ($bindLogin !== '') {
        $bindStatus = _t('已绑定：%s', hansJackEscape($bindLogin));
        if ($bindId !== '') {
            $bindStatus .= ' · ID ' . hansJackEscape($bindId);
        }
        if ($bindAt !== '') {
            $bindStatus .= ' · ' . hansJackEscape($bindAt);
        }
    }

    $adminUid = 0;
    $isAdmin = hansJackGithubCurrentAdminUid($adminUid);
    $adminReturn = Common::url('options-theme.php', (string) $options->adminUrl);
    $bindUrl = hansJackGithubOauthActionUrl('bind', ['return' => $adminReturn]);
    $unbindUrl = hansJackGithubOauthActionUrl('unbind', ['return' => $adminReturn]);

    $notes = [];
    if (!$enabled) {
        $notes[] = _t('请先开启 GitHub OAuth2 登录。');
    }
    if ($clientId === '' || $clientSecret === '') {
        $notes[] = _t('请先填写 Client ID / Client Secret。');
    }
    if (!$isAdmin) {
        $notes[] = _t('请先用管理员账号登录后台后再绑定。');
    }

    $html = '<div class="hj-github-bind-panel">';
    $html .= '<div>' . _t('绑定状态：') . $bindStatus . '</div>';

    if (!empty($notes)) {
        $html .= '<div style="margin-top:6px;color:#b46a00;">' . implode('<br>', array_map('hansJackEscape', $notes)) . '</div>';
    }

    if ($isAdmin) {
        $html .= '<div style="margin-top:8px;display:flex;gap:10px;flex-wrap:wrap;">';
        $html .= '<a href="' . hansJackEscape($bindUrl) . '">' . _t('GitHub账号绑定') . '</a>';
        if ($bindLogin !== '' || $bindId !== '') {
            $html .= '<a href="' . hansJackEscape($unbindUrl) . '">' . _t('解除绑定') . '</a>';
        }
        $html .= '</div>';
    }

    $html .= '</div>';
    return $html;
}

function hansJackGithubOauthEnabled(Options $options): bool
{
    $raw = strtolower(trim((string) ($options->hjGithubOauthEnabled ?? '0')));
    return in_array($raw, ['1', 'on', 'true', 'yes'], true);
}

function hansJackGithubCurrentAdminUid(&$uid = 0): bool
{
    $uid = 0;

    try {
        $user = \Typecho\Widget::widget('Widget_User');
    } catch (\Throwable $e) {
        return false;
    }

    if (!$user) {
        return false;
    }

    try {
        if (!$user->hasLogin()) {
            return false;
        }
    } catch (\Throwable $e) {
        return false;
    }

    try {
        if (!$user->pass('administrator', true)) {
            return false;
        }
    } catch (\Throwable $e) {
        return false;
    }

    try {
        $uid = (int) ($user->uid ?? 0);
    } catch (\Throwable $e) {
        $uid = 0;
    }

    return $uid > 0;
}

function hansJackGithubOauthActionUrl(string $action, array $params = []): string
{
    $options = Options::alloc();
    $base = (string) $options->index;

    $query = ['hj_github_oauth' => trim($action)];
    foreach ($params as $key => $value) {
        if ($key === '') {
            continue;
        }
        if ($value === null) {
            continue;
        }

        $text = trim((string) $value);
        if ($text === '') {
            continue;
        }
        $query[$key] = $text;
    }

    $qs = http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    if ($qs === '') {
        return $base;
    }

    $sep = (strpos($base, '?') === false) ? '?' : '&';
    return $base . $sep . $qs;
}

function hansJackGithubNormalizeReturnUrl(string $returnUrl, Options $options): string
{
    $fallback = (string) $options->siteUrl;
    $returnUrl = trim($returnUrl);

    if ($returnUrl === '' || preg_match('/[\r\n]/', $returnUrl)) {
        return $fallback;
    }

    if (hansJackStartsWith($returnUrl, '/')) {
        return Common::url(ltrim($returnUrl, '/'), (string) $options->siteUrl);
    }

    if (!preg_match('/^https?:\\/\\//i', $returnUrl)) {
        return Common::url(ltrim($returnUrl, '/'), (string) $options->siteUrl);
    }

    $targetHost = strtolower((string) (parse_url($returnUrl, PHP_URL_HOST) ?? ''));
    $siteHost = strtolower((string) (parse_url((string) $options->siteUrl, PHP_URL_HOST) ?? ''));
    if ($targetHost === '' || $siteHost === '' || $targetHost !== $siteHost) {
        return $fallback;
    }

    return $returnUrl;
}

function hansJackGithubDb()
{
    try {
        if (class_exists('\\Typecho\\Db')) {
            return Db::get();
        }

        if (class_exists('Typecho_Db')) {
            return \Typecho_Db::get();
        }
    } catch (\Throwable $e) {
        return null;
    }

    return null;
}

function hansJackThemeOptionStorageName(Options $options): string
{
    $theme = trim((string) ($options->theme ?? ''));
    if ($theme === '') {
        $theme = 'HansJack';
    }

    return 'theme:' . $theme;
}

function hansJackThemeOptionLoad(Options $options): array
{
    $db = hansJackGithubDb();
    if (!is_object($db)) {
        return [];
    }

    $name = hansJackThemeOptionStorageName($options);
    if ($name === '') {
        return [];
    }

    try {
        $row = $db->fetchRow(
            $db->select('value')
                ->from('table.options')
                ->where('name = ? AND user = ?', $name, 0)
                ->limit(1)
        );
    } catch (\Throwable $e) {
        return [];
    }

    $raw = '';
    if (is_array($row)) {
        $raw = (string) ($row['value'] ?? '');
    } elseif (is_object($row)) {
        $raw = (string) ($row->value ?? '');
    }

    if ($raw === '') {
        return [];
    }

    $json = json_decode($raw, true);
    if (is_array($json)) {
        return $json;
    }

    $legacy = @unserialize($raw);
    return is_array($legacy) ? $legacy : [];
}

function hansJackThemeOptionSave(Options $options, array $payload): bool
{
    $db = hansJackGithubDb();
    if (!is_object($db)) {
        return false;
    }

    $name = hansJackThemeOptionStorageName($options);
    if ($name === '') {
        return false;
    }

    $value = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($value)) {
        return false;
    }

    try {
        $exists = $db->fetchObject(
            $db->select('name')
                ->from('table.options')
                ->where('name = ? AND user = ?', $name, 0)
                ->limit(1)
        );

        if (is_object($exists)) {
            $db->query(
                $db->update('table.options')
                    ->rows(['value' => $value])
                    ->where('name = ? AND user = ?', $name, 0)
            );
            return true;
        }

        $db->query(
            $db->insert('table.options')
                ->rows([
                    'name' => $name,
                    'value' => $value,
                    'user' => 0,
                ])
        );
        return true;
    } catch (\Throwable $e) {
        return false;
    }
}

function hansJackSaveGithubBinding(Options $options, array $binding): bool
{
    $settings = hansJackThemeOptionLoad($options);

    foreach ($binding as $key => $value) {
        $settings[(string) $key] = trim((string) $value);
    }

    return hansJackThemeOptionSave($options, $settings);
}

function hansJackGithubOauthStateSet(string $state, string $mode, string $returnUrl): void
{
    $expire = 600;
    Cookie::set('__hj_github_oauth_state', $state, $expire);
    Cookie::set('__hj_github_oauth_mode', $mode, $expire);
    Cookie::set('__hj_github_oauth_return', $returnUrl, $expire);
}

function hansJackGithubOauthStateRead(): array
{
    return [
        'state' => trim((string) Cookie::get('__hj_github_oauth_state', '')),
        'mode' => trim((string) Cookie::get('__hj_github_oauth_mode', '')),
        'return' => trim((string) Cookie::get('__hj_github_oauth_return', '')),
    ];
}

function hansJackGithubOauthStateClear(): void
{
    Cookie::delete('__hj_github_oauth_state');
    Cookie::delete('__hj_github_oauth_mode');
    Cookie::delete('__hj_github_oauth_return');
}

function hansJackGithubOauthRandomState(): string
{
    try {
        return bin2hex(random_bytes(16));
    } catch (\Throwable $e) {
        return md5(uniqid((string) mt_rand(), true));
    }
}

function hansJackGithubHttpRequest(string $method, string $url, array $headers = [], string $body = ''): array
{
    $method = strtoupper(trim($method));
    if ($method === '') {
        $method = 'GET';
    }

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        if ($ch !== false) {
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

            if (!empty($headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            }

            if ($method !== 'GET' && $body !== '') {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            $resp = curl_exec($ch);
            $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            return [
                'status' => $status,
                'body' => is_string($resp) ? $resp : '',
            ];
        }
    }

    $headerLines = '';
    if (!empty($headers)) {
        $headerLines = implode("\r\n", $headers) . "\r\n";
    }

    $ctx = stream_context_create([
        'http' => [
            'method' => $method,
            'header' => $headerLines,
            'content' => ($method === 'GET') ? '' : $body,
            'timeout' => 12,
            'ignore_errors' => true,
        ],
    ]);

    $resp = @file_get_contents($url, false, $ctx);
    $status = 0;
    if (!empty($http_response_header[0]) && preg_match('/\\s(\\d{3})\\s/', (string) $http_response_header[0], $m)) {
        $status = (int) $m[1];
    }

    return [
        'status' => $status,
        'body' => is_string($resp) ? $resp : '',
    ];
}

function hansJackGithubExchangeToken(Options $options, string $code, string $state): string
{
    $clientId = trim((string) ($options->hjGithubOauthClientId ?? ''));
    $clientSecret = trim((string) ($options->hjGithubOauthClientSecret ?? ''));
    if ($clientId === '' || $clientSecret === '' || $code === '') {
        return '';
    }

    $payload = http_build_query([
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'code' => $code,
        'state' => $state,
        'redirect_uri' => hansJackGithubOauthActionUrl('callback'),
    ], '', '&', PHP_QUERY_RFC3986);

    $resp = hansJackGithubHttpRequest(
        'POST',
        'https://github.com/login/oauth/access_token',
        [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: HansJack-Typecho-OAuth',
        ],
        $payload
    );

    if ((int) ($resp['status'] ?? 0) < 200 || (int) ($resp['status'] ?? 0) >= 300) {
        return '';
    }

    $data = json_decode((string) ($resp['body'] ?? ''), true);
    if (!is_array($data)) {
        return '';
    }

    return trim((string) ($data['access_token'] ?? ''));
}

function hansJackGithubFetchUser(string $accessToken): array
{
    if ($accessToken === '') {
        return [];
    }

    $resp = hansJackGithubHttpRequest(
        'GET',
        'https://api.github.com/user',
        [
            'Accept: application/vnd.github+json',
            'Authorization: Bearer ' . $accessToken,
            'User-Agent: HansJack-Typecho-OAuth',
            'X-GitHub-Api-Version: 2022-11-28',
        ]
    );

    if ((int) ($resp['status'] ?? 0) < 200 || (int) ($resp['status'] ?? 0) >= 300) {
        return [];
    }

    $data = json_decode((string) ($resp['body'] ?? ''), true);
    return is_array($data) ? $data : [];
}

function hansJackGithubFetchEmails(string $accessToken): array
{
    if ($accessToken === '') {
        return [];
    }

    $resp = hansJackGithubHttpRequest(
        'GET',
        'https://api.github.com/user/emails',
        [
            'Accept: application/vnd.github+json',
            'Authorization: Bearer ' . $accessToken,
            'User-Agent: HansJack-Typecho-OAuth',
            'X-GitHub-Api-Version: 2022-11-28',
        ]
    );

    if ((int) ($resp['status'] ?? 0) < 200 || (int) ($resp['status'] ?? 0) >= 300) {
        return [];
    }

    $data = json_decode((string) ($resp['body'] ?? ''), true);
    return is_array($data) ? $data : [];
}

function hansJackGithubResolveEmail(array $user, array $emails): string
{
    $userEmail = trim((string) ($user['email'] ?? ''));
    if ($userEmail !== '') {
        return $userEmail;
    }

    foreach ($emails as $row) {
        if (!is_array($row)) {
            continue;
        }
        $mail = trim((string) ($row['email'] ?? ''));
        $verified = !empty($row['verified']);
        $primary = !empty($row['primary']);
        if ($mail !== '' && $verified && $primary) {
            return $mail;
        }
    }

    foreach ($emails as $row) {
        if (!is_array($row)) {
            continue;
        }
        $mail = trim((string) ($row['email'] ?? ''));
        $verified = !empty($row['verified']);
        if ($mail !== '' && $verified) {
            return $mail;
        }
    }

    foreach ($emails as $row) {
        if (!is_array($row)) {
            continue;
        }
        $mail = trim((string) ($row['email'] ?? ''));
        if ($mail !== '') {
            return $mail;
        }
    }

    $login = trim((string) ($user['login'] ?? ''));
    if ($login !== '') {
        return $login . '@users.noreply.github.com';
    }

    return '';
}

function hansJackHandleGithubOauthRequest(Archive $archive): void
{
    $action = '';
    try {
        $action = strtolower(trim((string) $archive->request->get('hj_github_oauth', '')));
    } catch (\Throwable $e) {
        $action = '';
    }

    if ($action === '') {
        return;
    }

    if (!in_array($action, ['login', 'bind', 'callback', 'unbind'], true)) {
        return;
    }

    $options = Options::alloc();

    $returnRaw = '';
    try {
        $returnRaw = trim((string) $archive->request->get('return', ''));
    } catch (\Throwable $e) {
        $returnRaw = '';
    }
    $returnUrl = hansJackGithubNormalizeReturnUrl($returnRaw, $options);

    if ($action === 'login' || $action === 'bind') {
        if (!hansJackGithubOauthEnabled($options)) {
            $archive->response->redirect($returnUrl);
            return;
        }

        if ($action === 'bind') {
            $adminUid = 0;
            if (!hansJackGithubCurrentAdminUid($adminUid)) {
                $archive->response->redirect($returnUrl);
                return;
            }
        }

        $clientId = trim((string) ($options->hjGithubOauthClientId ?? ''));
        if ($clientId === '') {
            $archive->response->redirect($returnUrl);
            return;
        }

        $scope = trim((string) ($options->hjGithubOauthScope ?? ''));
        if ($scope === '') {
            $scope = 'read:user user:email';
        }

        $state = hansJackGithubOauthRandomState();
        hansJackGithubOauthStateSet($state, $action, $returnUrl);

        $authUrl = 'https://github.com/login/oauth/authorize?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => hansJackGithubOauthActionUrl('callback'),
            'scope' => $scope,
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);

        $archive->response->redirect($authUrl);
        return;
    }

    if ($action === 'unbind') {
        $adminUid = 0;
        if (!hansJackGithubCurrentAdminUid($adminUid)) {
            $archive->response->redirect($returnUrl);
            return;
        }

        hansJackSaveGithubBinding($options, [
            'hjGithubBindUid' => '',
            'hjGithubBindId' => '',
            'hjGithubBindLogin' => '',
            'hjGithubBindAvatar' => '',
            'hjGithubBindEmail' => '',
            'hjGithubBindAt' => '',
        ]);
        $archive->response->redirect($returnUrl);
        return;
    }

    $stateData = hansJackGithubOauthStateRead();
    hansJackGithubOauthStateClear();

    $expectedState = trim((string) ($stateData['state'] ?? ''));
    $mode = strtolower(trim((string) ($stateData['mode'] ?? '')));
    $stateReturn = hansJackGithubNormalizeReturnUrl((string) ($stateData['return'] ?? ''), $options);
    if ($stateReturn === '') {
        $stateReturn = $returnUrl;
    }

    $recvState = '';
    $code = '';
    try {
        $recvState = trim((string) $archive->request->get('state', ''));
        $code = trim((string) $archive->request->get('code', ''));
    } catch (\Throwable $e) {
        $recvState = '';
        $code = '';
    }

    if ($expectedState === '' || $recvState === '' || !hash_equals($expectedState, $recvState) || $code === '') {
        $archive->response->redirect($stateReturn);
        return;
    }

    $accessToken = hansJackGithubExchangeToken($options, $code, $recvState);
    if ($accessToken === '') {
        $archive->response->redirect($stateReturn);
        return;
    }

    $githubUser = hansJackGithubFetchUser($accessToken);
    if (empty($githubUser)) {
        $archive->response->redirect($stateReturn);
        return;
    }

    $githubEmails = hansJackGithubFetchEmails($accessToken);
    $login = trim((string) ($githubUser['login'] ?? ''));
    $githubId = trim((string) ($githubUser['id'] ?? ''));
    $avatar = trim((string) ($githubUser['avatar_url'] ?? ''));
    $email = hansJackGithubResolveEmail($githubUser, $githubEmails);

    if ($mode === 'bind') {
        $adminUid = 0;
        if (!hansJackGithubCurrentAdminUid($adminUid)) {
            $archive->response->redirect($stateReturn);
            return;
        }

        hansJackSaveGithubBinding($options, [
            'hjGithubBindUid' => (string) $adminUid,
            'hjGithubBindId' => $githubId,
            'hjGithubBindLogin' => $login,
            'hjGithubBindAvatar' => $avatar,
            'hjGithubBindEmail' => $email,
            'hjGithubBindAt' => date('Y-m-d H:i:s'),
        ]);

        $archive->response->redirect($stateReturn);
        return;
    }

    if ($mode !== 'login') {
        $archive->response->redirect($stateReturn);
        return;
    }

    // If this GitHub account is bound to a local admin account,
    // perform a real Typecho login instead of guest identity fill.
    $boundUid = (int) trim((string) ($options->hjGithubBindUid ?? '0'));
    $boundGithubId = trim((string) ($options->hjGithubBindId ?? ''));
    $boundGithubLogin = strtolower(trim((string) ($options->hjGithubBindLogin ?? '')));

    $isBoundMatch = false;
    if ($boundUid > 0) {
        if ($boundGithubId !== '' && $githubId !== '' && hash_equals($boundGithubId, $githubId)) {
            $isBoundMatch = true;
        } elseif (
            $boundGithubId === '' &&
            $boundGithubLogin !== '' &&
            $login !== '' &&
            hash_equals($boundGithubLogin, strtolower($login))
        ) {
            $isBoundMatch = true;
        }
    }

    if ($isBoundMatch) {
        try {
            $userWidget = \Typecho\Widget::widget('Widget_User');
            if ($userWidget && method_exists($userWidget, 'simpleLogin') && $userWidget->simpleLogin($boundUid, false)) {
                $archive->response->redirect($stateReturn);
                return;
            }
        } catch (\Throwable $e) {
            // Fallback to guest identity cookies below.
        }
    }

    $author = ($login !== '') ? $login : _t('GitHub用户');
    $profileUrl = ($login !== '') ? ('https://github.com/' . $login) : 'https://github.com/';
    $expire = 30 * 24 * 3600;

    Cookie::set('__typecho_remember_author', $author, $expire);
    Cookie::set('__typecho_remember_mail', $email, $expire);
    Cookie::set('__typecho_remember_url', $profileUrl, $expire);

    $archive->response->redirect($stateReturn);
}

function hansJackRenderPager(
    Archive $archive,
    string $prevHtml,
    string $nextHtml,
    int $splitPage = 2,
    string $splitWord = '...',
    ?string $basePath = null
): void
{
    $totalPage = 0;
    try {
        $totalPage = (int) $archive->getTotalPage();
    } catch (\Throwable $e) {
        $totalPage = 0;
    }

    if ($totalPage <= 1) {
        return;
    }

    $basePath = is_string($basePath) ? trim($basePath) : null;
    if ($basePath !== null) {
        $basePath = trim($basePath, '/');
        if ($basePath === '') {
            $basePath = null;
        }
    }

    // Theme-level list pages (e.g. /posts, /notes): use a fixed base path for pagination
    // instead of Typecho's archive routes (category_page, tag_page, ...).
    if ($basePath !== null) {
        $currentPage = 1;
        try {
            $currentPage = max(1, (int) $archive->getCurrentPage());
        } catch (\Throwable $e) {
            $currentPage = 1;
        }

        $options = Options::alloc();
        $baseUrl = Common::url($basePath, (string) $options->index);
        $buildUrl = static function (string $baseUrl, int $page): string {
            if ($page <= 1) {
                return $baseUrl;
            }
            $sep = (strpos($baseUrl, '?') === false) ? '?' : '&';
            return $baseUrl . $sep . 'page=' . $page;
        };

        $from = max(1, $currentPage - $splitPage);
        $to = min($totalPage, $currentPage + $splitPage);

        echo '<ol class="page-navigator hj-posts-pager">';

        // Prev (always present; disabled on first page)
        if ($currentPage > 1) {
            echo '<li class="prev"><a href="'
                . hansJackEscape($buildUrl($baseUrl, $currentPage - 1))
                . '">' . $prevHtml . '</a></li>';
        } else {
            echo '<li class="prev is-disabled"><span aria-disabled="true" tabindex="-1">' . $prevHtml . '</span></li>';
        }

        // First page + leading gap
        if ($from > 1) {
            echo '<li><a href="'
                . hansJackEscape($buildUrl($baseUrl, 1))
                . '">1</a></li>';
            if ($from > 2) {
                echo '<li class="hj-posts-pager-gap"><span>' . hansJackEscape($splitWord) . '</span></li>';
            }
        }

        // Middle pages
        for ($i = $from; $i <= $to; $i++) {
            $cls = ($i === $currentPage) ? ' class="current"' : '';
            echo '<li' . $cls . '><a href="'
                . hansJackEscape($buildUrl($baseUrl, $i))
                . '">' . (int) $i . '</a></li>';
        }

        // Trailing gap + last page
        if ($to < $totalPage) {
            if ($to < $totalPage - 1) {
                echo '<li class="hj-posts-pager-gap"><span>' . hansJackEscape($splitWord) . '</span></li>';
            }
            echo '<li><a href="'
                . hansJackEscape($buildUrl($baseUrl, $totalPage))
                . '">' . (int) $totalPage . '</a></li>';
        }

        // Next (always present; disabled on last page)
        if ($currentPage < $totalPage) {
            echo '<li class="next"><a href="'
                . hansJackEscape($buildUrl($baseUrl, $currentPage + 1))
                . '">' . $nextHtml . '</a></li>';
        } else {
            echo '<li class="next is-disabled"><span aria-disabled="true" tabindex="-1">' . $nextHtml . '</span></li>';
        }

        echo '</ol>';
        return;
    }

    $template = [
        'wrapTag' => 'ol',
        'wrapClass' => 'page-navigator hj-posts-pager',
        'itemTag' => 'li',
        'textTag' => 'span',
        'currentClass' => 'current',
        'prevClass' => 'prev',
        'nextClass' => 'next',
    ];

    ob_start();
    $archive->pageNav($prevHtml, $nextHtml, $splitPage, $splitWord, $template);
    $html = (string) ob_get_clean();
    $html = trim($html);
    if ($html === '') {
        return;
    }

    if (!preg_match('/<li[^>]*class=\"[^\"]*\\bprev\\b/', $html)) {
        $disabledPrev = '<li class="prev is-disabled"><span aria-disabled="true" tabindex="-1">' . $prevHtml . '</span></li>';
        $html = (string) preg_replace('/(<ol\\b[^>]*>)/', '$1' . $disabledPrev, $html, 1);
    }

    if (!preg_match('/<li[^>]*class=\"[^\"]*\\bnext\\b/', $html)) {
        $disabledNext = '<li class="next is-disabled"><span aria-disabled="true" tabindex="-1">' . $nextHtml . '</span></li>';
        $html = (string) preg_replace('/<\\/ol>\\s*$/', $disabledNext . '</ol>', $html, 1);
    }

    echo $html;
}

/**
 * 汇总主题配置（模板层调用）
 */
function hansJackBuildThemeConfig(Options $options): array
{
    $brandName = hansJackText((string) $options->title, 'HansJack');

    $links = [
        'home'   => (string) $options->siteUrl,
        // Blog points to a dedicated post list page (slug: "posts").
        // Uses options->index so it works both with and without rewrite (index.php).
        'blog'   => Common::url('posts', (string) $options->index),
        // Memo points to a dedicated notes list page (slug: "notes").
        'memo'   => Common::url('notes', (string) $options->index),
        // Memory points to a dedicated stats page (slug: "memos").
        'memory' => Common::url('memos', (string) $options->index),

        // Landing social links.
        'cv'     => trim((string) $options->hjCvUrl),
        'github' => trim((string) $options->hjGithubUrl),
        'creative' => trim((string) $options->hjCreativeUrl),
    ];

    return [
        'brandName' => $brandName,
        'links' => $links,
        'navItems' => [
            ['key' => 'home', 'label' => '首页', 'url' => $links['home']],
            ['key' => 'blog', 'label' => '博文', 'url' => $links['blog']],
            ['key' => 'memo', 'label' => '手记', 'url' => $links['memo']],
            ['key' => 'memory', 'label' => '回忆', 'url' => $links['memory']]
        ]
    ];
}

function hansJackText(string $value, string $fallback = ''): string
{
    $trimmed = trim($value);
    return $trimmed === '' ? $fallback : $trimmed;
}

function hansJackPercent(string $value, int $fallback = 86): int
{
    $num = (int) trim($value);
    if ($num < 60 || $num > 100) {
        return $fallback;
    }
    return $num;
}

function hansJackColor(string $value, string $fallback): string
{
    $trimmed = trim($value);
    if (preg_match('/^#([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $trimmed)) {
        return strtolower($trimmed);
    }
    return $fallback;
}

function hansJackStartsWith(string $haystack, string $needle): bool
{
    if (function_exists('str_starts_with')) {
        return str_starts_with($haystack, $needle);
    }

    if ($needle === '') {
        return true;
    }

    return substr($haystack, 0, strlen($needle)) === $needle;
}

function hansJackResolveLink(Options $options, string $value, string $fallbackPath): string
{
    $value = trim($value);
    if ($value === '') {
        return Common::url($fallbackPath, $options->siteUrl);
    }

    if (preg_match('/^(https?:)?\\/\\//i', $value) || hansJackStartsWith($value, '#') || hansJackStartsWith($value, 'mailto:') || hansJackStartsWith($value, 'tel:')) {
        return $value;
    }

    return Common::url(ltrim($value, '/'), $options->siteUrl);
}

function hansJackNormalizeAssetUrl(Options $options, string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    $value = str_replace(["\r", "\n", '"', '\'', '(', ')'], '', $value);
    if (preg_match('/^(https?:)?\\/\\//i', $value) || hansJackStartsWith($value, '/')) {
        return $value;
    }

    return Common::url(ltrim($value, '/'), $options->siteUrl);
}

function hansJackCssBackground(string $url): string
{
    if ($url === '') {
        return 'none';
    }

    $safe = str_replace(['\\', '\''], ['\\\\', '\\\''], $url);
    return "url('{$safe}')";
}

function hansJackEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function hansJackBuildMpsBeianUrl(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    // Allow users to paste the full URL if they want.
    if (preg_match('/^(https?:)?\\/\\//i', $value)) {
        return $value;
    }

    $code = '';
    if (preg_match('/(\\d{10,})/', $value, $match)) {
        $code = (string) $match[1];
    }

    if ($code === '') {
        return 'https://beian.mps.gov.cn/#/query/webSearch';
    }

    return 'https://beian.mps.gov.cn/#/query/webSearch?code=' . rawurlencode($code);
}

function hansJackAsciiBanner(string $raw, int $maxLineChars = 0, int $weight = 2): string
{
    $text = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $raw));
    $text = trim($text);
    if ($text === '') {
        $text = 'HANSJACK';
    }

    $maxLineChars = (int) $maxLineChars;
    if ($maxLineChars <= 0) {
        // "0" means: do not split across multiple words/lines.
        $maxLineChars = strlen($text);
    }

    $weight = (int) $weight;
    if ($weight < 1) {
        $weight = 1;
    }
    if ($weight > 3) {
        $weight = 3;
    }

    static $glyphs = null;
    if ($glyphs === null) {
        // 5x7 bitmap font, each row is a 5-bit mask (MSB on the left).
        $glyphs = [
            'A' => [14, 17, 17, 31, 17, 17, 17],
            'B' => [30, 17, 17, 30, 17, 17, 30],
            'C' => [14, 17, 16, 16, 16, 17, 14],
            'D' => [30, 17, 17, 17, 17, 17, 30],
            'E' => [31, 16, 16, 30, 16, 16, 31],
            'F' => [31, 16, 16, 30, 16, 16, 16],
            'G' => [14, 17, 16, 23, 17, 17, 14],
            'H' => [17, 17, 17, 31, 17, 17, 17],
            'I' => [31, 4, 4, 4, 4, 4, 31],
            'J' => [15, 1, 1, 1, 17, 17, 14],
            'K' => [17, 18, 20, 24, 20, 18, 17],
            'L' => [16, 16, 16, 16, 16, 16, 31],
            'M' => [17, 27, 21, 17, 17, 17, 17],
            'N' => [17, 25, 21, 19, 17, 17, 17],
            'O' => [14, 17, 17, 17, 17, 17, 14],
            'P' => [30, 17, 17, 30, 16, 16, 16],
            'Q' => [14, 17, 17, 17, 21, 18, 13],
            'R' => [30, 17, 17, 30, 20, 18, 17],
            'S' => [15, 16, 16, 14, 1, 1, 30],
            'T' => [31, 4, 4, 4, 4, 4, 4],
            'U' => [17, 17, 17, 17, 17, 17, 14],
            'V' => [17, 17, 17, 17, 17, 10, 4],
            'W' => [17, 17, 17, 17, 21, 27, 17],
            'X' => [17, 10, 4, 4, 4, 10, 17],
            'Y' => [17, 10, 4, 4, 4, 4, 4],
            'Z' => [31, 1, 2, 4, 8, 16, 31],
            '0' => [14, 17, 19, 21, 25, 17, 14],
            '1' => [4, 12, 4, 4, 4, 4, 14],
            '2' => [14, 17, 1, 2, 4, 8, 31],
            '3' => [30, 1, 1, 6, 1, 1, 30],
            '4' => [2, 6, 10, 18, 31, 2, 2],
            '5' => [31, 16, 16, 30, 1, 1, 30],
            '6' => [6, 8, 16, 30, 17, 17, 14],
            '7' => [31, 1, 2, 4, 8, 8, 8],
            '8' => [14, 17, 17, 14, 17, 17, 14],
            '9' => [14, 17, 17, 15, 1, 2, 28],
            '?' => [14, 17, 1, 2, 4, 0, 4],
        ];
    }

    $chunks = str_split($text, $maxLineChars);

    $height = 7;
    $out = [];
    $chunkTotal = count($chunks);
    foreach ($chunks as $chunkIndex => $chunk) {
        $rows = array_fill(0, $height, '');
        $chars = str_split($chunk);
        $count = count($chars);
        foreach ($chars as $ci => $ch) {
            $pattern = $glyphs[$ch] ?? $glyphs['?'];
            for ($r = 0; $r < $height; $r++) {
                $mask = (int) ($pattern[$r] ?? 0);
                if ($weight > 1) {
                    // Bold by dilating the 5-bit mask within the glyph box.
                    // Keeps the banner compact while increasing stroke density.
                    $bold = $mask;
                    for ($w = 1; $w < $weight; $w++) {
                        $bold = ($bold | (($bold << 1) & 31) | ($bold >> 1)) & 31;
                    }
                    $mask = $bold;
                }
                for ($bit = 4; $bit >= 0; $bit--) {
                    $rows[$r] .= ($mask & (1 << $bit)) ? $ch : ' ';
                }
                if ($ci !== $count - 1) {
                    $rows[$r] .= ' ';
                }
            }
        }

        for ($r = 0; $r < $height; $r++) {
            $rows[$r] = rtrim($rows[$r]);
        }

        $out = array_merge($out, $rows);
        if ($chunkIndex !== $chunkTotal - 1) {
            $out[] = '';
        }
    }

    return implode("\n", $out);
}

function hansJackAvatarFallback(Options $options): string
{
    $title = trim((string) $options->title);
    if ($title === '') {
        return 'HJ';
    }

    if (function_exists('mb_substr')) {
        return mb_substr($title, 0, 1, 'UTF-8');
    }

    return strtoupper(substr($title, 0, 1));
}

function hansJackIsActiveNav(Archive $archive, string $targetUrl): bool
{
    $targetRaw = (string) (parse_url($targetUrl, PHP_URL_PATH) ?? '');
    if ($targetRaw === '') {
        return false;
    }

    $currentUri = (string) ($archive->request->getRequestUri() ?? '/');
    $currentRaw = (string) (parse_url($currentUri, PHP_URL_PATH) ?? '/');

    $targetPath = hansJackNormalizePath($targetRaw);
    $currentPath = hansJackNormalizePath($currentRaw);

    return $currentPath === $targetPath;
}

function hansJackNormalizePath(string $path): string
{
    $normalized = '/' . ltrim(trim($path), '/');
    $normalized = (string) preg_replace('#/+#', '/', $normalized);
    $normalized = (string) preg_replace('#^/index\\.php#i', '', $normalized);
    $normalized = $normalized === '' ? '/' : $normalized;
    $normalized = rtrim($normalized, '/');
    return $normalized === '' ? '/' : $normalized;
}

function hansJackNavIconSvg(string $key): string
{
    switch ($key) {
        case 'home':
            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.5 21 14 3"/><path d="M20.5 21 10 3"/><path d="M15.5 21 12 15l-3.5 6"/><path d="M2 21h20"/></svg>';
        case 'blog':
            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 12h-5"/><path d="M15 8h-5"/><path d="M19 17V5a2 2 0 0 0-2-2H4"/><path d="M8 21h12a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1H11a1 1 0 0 0-1 1v1a2 2 0 1 1-4 0V5a2 2 0 1 0-4 0v2a1 1 0 0 0 1 1h3"/></svg>';
        case 'memo':
            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.67 19a2 2 0 0 0 1.416-.588l6.154-6.172a6 6 0 0 0-8.49-8.49L5.586 9.914A2 2 0 0 0 5 11.328V18a1 1 0 0 0 1 1z"/><path d="M16 8 2 22"/><path d="M17.5 15H9"/></svg>';
        case 'memory':
            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path class="hj-memory-hour" d="M12 8v4"/><path class="hj-memory-minute" d="M12 12l3 1.6"/></svg>';
        default:
            return '';
    }
}

function hansJackAvatarRating(Options $options): string
{
    $rating = strtoupper(trim((string) ($options->commentsAvatarRating ?? 'G')));
    return in_array($rating, ['G', 'PG', 'R', 'X'], true) ? $rating : 'G';
}

function hansJackGithubLoginFromUrl(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }

    $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));
    if ($host === 'www.github.com') {
        $host = 'github.com';
    }
    if ($host !== 'github.com') {
        return '';
    }

    $path = trim((string) (parse_url($url, PHP_URL_PATH) ?? ''), '/');
    if ($path === '') {
        return '';
    }

    $parts = explode('/', $path);
    $login = trim((string) ($parts[0] ?? ''));
    if ($login === '') {
        return '';
    }

    if (!preg_match('/^[A-Za-z0-9](?:[A-Za-z0-9-]{0,38})$/', $login)) {
        return '';
    }

    return $login;
}

function hansJackPrivateCommentMarker(): string
{
    // Stored in comment text so the theme can render "private" comments without extra DB fields.
    return '<!--hj-private-->';
}

function hansJackIsPrivateCommentText(string $text): bool
{
    $marker = hansJackPrivateCommentMarker();
    return hansJackStartsWith(ltrim($text), $marker);
}

function hansJackStripPrivateCommentMarker(string $text): string
{
    $marker = hansJackPrivateCommentMarker();
    $trimmed = ltrim($text);
    if (!hansJackStartsWith($trimmed, $marker)) {
        return $text;
    }

    $pos = strpos($text, $marker);
    if ($pos === false) {
        return $text;
    }

    $rest = (string) substr($text, $pos + strlen($marker));
    return ltrim($rest, "\r\n\t ");
}

function hansJackCanViewPrivateComment(int $ownerId, int $authorId): bool
{
    $ownerId = (int) $ownerId;
    $authorId = (int) $authorId;

    try {
        $user = \Typecho\Widget::widget('Widget_User');
    } catch (\Throwable $e) {
        $user = null;
    }

    if (!$user) {
        return false;
    }

    try {
        if (!$user->hasLogin()) {
            return false;
        }
    } catch (\Throwable $e) {
        return false;
    }

    $uid = 0;
    try {
        $uid = (int) ($user->uid ?? 0);
    } catch (\Throwable $e) {
        $uid = 0;
    }

    if ($uid > 0 && ($uid === $ownerId || $uid === $authorId)) {
        return true;
    }

    try {
        if ($user->pass('administrator', true) || $user->pass('editor', true)) {
            return true;
        }
    } catch (\Throwable $e) {
        // Ignore.
    }

    return false;
}

function hansJackThreadedCommentsMap($comments): array
{
    if (!is_object($comments)) {
        return [];
    }

    static $cache = [];
    $key = '';
    try {
        $key = function_exists('spl_object_id') ? (string) spl_object_id($comments) : spl_object_hash($comments);
    } catch (\Throwable $e) {
        $key = '';
    }

    if ($key !== '' && isset($cache[$key]) && is_array($cache[$key])) {
        return $cache[$key];
    }

    $map = [];
    try {
        $ref = new \ReflectionObject($comments);
        while ($ref && !$ref->hasProperty('threadedComments')) {
            $ref = $ref->getParentClass();
        }

        if ($ref && $ref->hasProperty('threadedComments')) {
            $prop = $ref->getProperty('threadedComments');
            $prop->setAccessible(true);
            $value = $prop->getValue($comments);
            if (is_array($value)) {
                $map = $value;
            }
        }
    } catch (\Throwable $e) {
        $map = [];
    }

    if ($key !== '') {
        $cache[$key] = $map;
    }

    return $map;
}

function hansJackCountCommentDescendantsByMap(array $map, int $coid, array &$memo = []): int
{
    $coid = (int) $coid;
    if ($coid <= 0) {
        return 0;
    }

    if (isset($memo[$coid]) && is_int($memo[$coid])) {
        return $memo[$coid];
    }

    if (empty($map[$coid]) || !is_array($map[$coid])) {
        $memo[$coid] = 0;
        return 0;
    }

    $children = $map[$coid];
    $count = 0;

    foreach ($children as $child) {
        $count++;
        $childId = 0;
        if (is_array($child)) {
            try {
                $childId = (int) ($child['coid'] ?? 0);
            } catch (\Throwable $e) {
                $childId = 0;
            }
        } elseif (is_object($child)) {
            try {
                $childId = (int) ($child->coid ?? 0);
            } catch (\Throwable $e) {
                $childId = 0;
            }
        }

        if ($childId > 0) {
            $count += hansJackCountCommentDescendantsByMap($map, $childId, $memo);
        }
    }

    $memo[$coid] = $count;
    return $count;
}

function hansJackCountCommentDescendants($comments): int
{
    if (!is_object($comments)) {
        return 0;
    }

    static $memoByWidget = [];

    $widgetKey = '';
    try {
        $widgetKey = function_exists('spl_object_id') ? (string) spl_object_id($comments) : spl_object_hash($comments);
    } catch (\Throwable $e) {
        $widgetKey = '';
    }

    $coid = 0;
    try {
        $coid = (int) ($comments->coid ?? 0);
    } catch (\Throwable $e) {
        $coid = 0;
    }

    if ($coid <= 0) {
        try {
            $children = $comments->children;
            return is_array($children) ? count($children) : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    $map = hansJackThreadedCommentsMap($comments);
    if (empty($map)) {
        try {
            $children = $comments->children;
            return is_array($children) ? count($children) : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    if ($widgetKey === '') {
        $tmpMemo = [];
        return hansJackCountCommentDescendantsByMap($map, $coid, $tmpMemo);
    }

    if (!isset($memoByWidget[$widgetKey]) || !is_array($memoByWidget[$widgetKey])) {
        $memoByWidget[$widgetKey] = [];
    }

    return hansJackCountCommentDescendantsByMap($map, $coid, $memoByWidget[$widgetKey]);
}

function threadedComments($comments, $singleCommentOptions): void
{
    if (!$comments) {
        return;
    }

    $rawText = '';
    try {
        $rawText = (string) ($comments->text ?? '');
    } catch (\Throwable $e) {
        $rawText = '';
    }

    $isPrivate = hansJackIsPrivateCommentText($rawText);
    $canViewPrivate = true;
    if ($isPrivate) {
        $ownerId = 0;
        $authorId = 0;
        try {
            $ownerId = (int) ($comments->ownerId ?? 0);
        } catch (\Throwable $e) {
            $ownerId = 0;
        }
        try {
            $authorId = (int) ($comments->authorId ?? 0);
        } catch (\Throwable $e) {
            $authorId = 0;
        }
        $canViewPrivate = hansJackCanViewPrivateComment($ownerId, $authorId);
    }

    $commentClass = '';
    if (!empty($comments->authorId)) {
        if ((int) $comments->authorId === (int) $comments->ownerId) {
            $commentClass .= ' comment-by-author';
        } else {
            $commentClass .= ' comment-by-user';
        }
    }

    if ($isPrivate) {
        $commentClass .= ' hj-comment-private';
        if (!$canViewPrivate) {
            $commentClass .= ' is-private-hidden';
        }
    }

    $hjHasChildren = false;
    try {
        $hjHasChildren = !empty($comments->children);
    } catch (\Throwable $e) {
        $hjHasChildren = false;
    }
    if ($hjHasChildren) {
        $commentClass .= ' hj-comment-has-children';
    }

    $hjAvatarSize = max(1, (int) ($singleCommentOptions->avatarSize ?? 32));
    $hjAvatarDefault = '';
    try {
        $hjAvatarDefault = trim((string) ($singleCommentOptions->defaultAvatar ?? ''));
    } catch (\Throwable $e) {
        $hjAvatarDefault = '';
    }
    if ($hjAvatarDefault === '') {
        $hjAvatarDefault = 'mp';
    }

    $hjAvatarEmail = '';
    try {
        $hjAvatarEmail = strtolower(trim((string) ($comments->mail ?? '')));
    } catch (\Throwable $e) {
        $hjAvatarEmail = '';
    }

    $hjCommentUrl = '';
    try {
        $hjCommentUrl = trim((string) ($comments->url ?? ''));
    } catch (\Throwable $e) {
        $hjCommentUrl = '';
    }

    $hjAvatarSrcset = '';
    $hjGithubLogin = hansJackGithubLoginFromUrl($hjCommentUrl);
    if ($hjGithubLogin !== '') {
        $hjAvatarBase = 'https://github.com/' . rawurlencode($hjGithubLogin) . '.png';
        $hjAvatarUrl = $hjAvatarBase . '?size=' . $hjAvatarSize;
        if (!empty($singleCommentOptions->avatarHighRes)) {
            $hjAvatarSrcset = $hjAvatarBase . '?size=' . ($hjAvatarSize * 2) . ' 2x, '
                . $hjAvatarBase . '?size=' . ($hjAvatarSize * 3) . ' 3x';
        }
    } else {
        // Use Sep CDN as the default avatar endpoint with Gravatar-compatible parameters.
        $hjAvatarHash = md5($hjAvatarEmail);
        $hjAvatarBase = 'https://cdn.sep.cc/avatar/' . $hjAvatarHash;
        $hjAvatarQuery = '&d=' . rawurlencode($hjAvatarDefault) . '&r=g';
        $hjAvatarUrl = $hjAvatarBase . '?s=' . $hjAvatarSize . $hjAvatarQuery;
        if (!empty($singleCommentOptions->avatarHighRes)) {
            $hjAvatarSrcset = $hjAvatarBase . '?s=' . ($hjAvatarSize * 2) . $hjAvatarQuery . ' 2x, '
                . $hjAvatarBase . '?s=' . ($hjAvatarSize * 3) . $hjAvatarQuery . ' 3x';
        }
    }
    ?>
    <li itemscope itemtype="http://schema.org/UserComments" id="<?php $comments->theId(); ?>" class="comment-body<?php 
    if ($comments->levels > 0) { 
        echo ' comment-child'; 
        $comments->levelsAlt(' comment-level-odd', ' comment-level-even'); 
    } else { 
        echo ' comment-parent';
    }
    $comments->alt(' comment-odd', ' comment-even');
    echo $commentClass; 
    ?>" data-hj-comment-level="<?php echo (int) $comments->levels; ?>"> 
        <div class="comment-author" itemprop="creator" itemscope itemtype="http://schema.org/Person">  
            <span itemprop="image">  
                <img
                    class="avatar"
                    src="<?php echo hansJackEscape($hjAvatarUrl); ?>"
                    <?php if ($hjAvatarSrcset !== ''): ?>srcset="<?php echo hansJackEscape($hjAvatarSrcset); ?>"<?php endif; ?>
                    alt=""
                    width="<?php echo (int) $hjAvatarSize; ?>"
                    height="<?php echo (int) $hjAvatarSize; ?>"
                    loading="lazy"
                    decoding="async"
                    referrerpolicy="no-referrer"
                >
            </span>
            <div class="hj-comment-author-meta">
                <cite class="fn" itemprop="name"><?php $singleCommentOptions->beforeAuthor();
                    $comments->author();
                    $singleCommentOptions->afterAuthor(); ?></cite>
                <div class="comment-meta"> 
                    <time itemprop="commentTime" datetime="<?php $comments->date('c'); ?>"><?php 
                        $singleCommentOptions->beforeDate(); 
                        $comments->date($singleCommentOptions->dateFormat); 
                        $singleCommentOptions->afterDate(); 
                    ?></time> 
                    <?php if ('approved' !== $comments->status) { ?> 
                        <em class="comment-awaiting-moderation"><?php $singleCommentOptions->commentStatus(); ?></em> 
                    <?php } ?> 
                </div> 
            </div>
        </div> 
        <div class="comment-content hj-comment-content<?php echo $isPrivate ? ' is-private' : ''; ?><?php echo ($isPrivate && !$canViewPrivate) ? ' is-private-hidden' : ''; ?>" itemprop="commentText">
            <?php if ($isPrivate && !$canViewPrivate): ?>
                <div class="hj-private-mask" aria-hidden="true"></div>
            <?php else: ?>
                <?php $comments->content(); ?>
            <?php endif; ?>
        </div> 
        <?php if ($comments->children) { ?>
            <?php
            $hjChildren = $comments->children;
            $hjChildrenDirectCount = is_array($hjChildren) ? count($hjChildren) : 0;
            $hjChildrenCount = hansJackCountCommentDescendants($comments);
            $hjChildrenPreview = [];
            if ($hjChildrenDirectCount > 0) {
                $hjChildrenPreview = array_slice($hjChildren, 0, 5);
            }
            ?>
            <details class="comment-children hj-comment-children" itemprop="discusses" data-hj-comment-children data-hj-comment-children-count="<?php echo (int) $hjChildrenCount; ?>">
                <summary class="hj-comment-children-summary">
                    <div class="hj-comment-children-preview" aria-label="<?php _e('回复预览'); ?>">
                        <?php foreach ($hjChildrenPreview as $hjChild): ?>
                            <?php
                            $hjChildAuthor = '';
                            try {
                                $hjChildAuthor = (string) ($hjChild['author'] ?? '');
                            } catch (\Throwable $e) {
                                $hjChildAuthor = '';
                            }

                            $hjChildRaw = '';
                            try {
                                $hjChildRaw = (string) ($hjChild['text'] ?? '');
                            } catch (\Throwable $e) {
                                $hjChildRaw = '';
                            }

                            $hjChildIsPrivate = hansJackIsPrivateCommentText($hjChildRaw);
                            $hjChildCanView = true;
                            if ($hjChildIsPrivate) {
                                $hjChildOwnerId = 0;
                                $hjChildAuthorId = 0;
                                try {
                                    $hjChildOwnerId = (int) ($hjChild['ownerId'] ?? 0);
                                } catch (\Throwable $e) {
                                    $hjChildOwnerId = 0;
                                }
                                try {
                                    $hjChildAuthorId = (int) ($hjChild['authorId'] ?? 0);
                                } catch (\Throwable $e) {
                                    $hjChildAuthorId = 0;
                                }
                                $hjChildCanView = hansJackCanViewPrivateComment($hjChildOwnerId, $hjChildAuthorId);
                            }

                            if ($hjChildIsPrivate && !$hjChildCanView) {
                                $hjChildPreviewText = _t('私信内容');
                            } else {
                                $hjChildPreviewText = hansJackStripPrivateCommentMarker($hjChildRaw);
                                $hjChildPreviewText = strip_tags($hjChildPreviewText);
                                $hjChildPreviewText = (string) preg_replace('/\\s+/u', ' ', $hjChildPreviewText);
                                $hjChildPreviewText = trim($hjChildPreviewText);
                                if ($hjChildPreviewText === '') {
                                    $hjChildPreviewText = _t('（无内容）');
                                } else {
                                    $hjChildPreviewText = Common::subStr($hjChildPreviewText, 0, 72, '...');
                                }
                            }
                            ?>
                            <div class="hj-comment-children-preview-item">
                                <span class="hj-comment-children-preview-author"><?php echo hansJackEscape($hjChildAuthor); ?></span><span class="hj-comment-children-preview-sep">：</span><span class="hj-comment-children-preview-text"><?php echo hansJackEscape($hjChildPreviewText); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <span class="hj-comment-children-toggle hj-comment-children-toggle-closed"><?php echo _t('共') . (int) $hjChildrenCount . _t('条回复'); ?></span>
                    <span class="hj-comment-children-toggle hj-comment-children-toggle-open"><?php _e('收起回复'); ?></span>
                </summary>
                <div class="hj-comment-children-full">
                    <?php $comments->threadedComments(); ?>
                </div>
            </details>
        <?php } ?>
        <div class="comment-reply"> 
            <?php $comments->reply($singleCommentOptions->replyWord); ?> 
            <button class="hj-comment-share-btn" type="button" aria-label="<?php _e('分享'); ?>" title="<?php _e('分享'); ?>" data-hj-comment-share="<?php $comments->permalink(); ?>"> 
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-share2-icon lucide-share-2" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/></svg> 
            </button> 
        </div> 
    </li>
    <?php
}
