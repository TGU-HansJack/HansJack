<?php
/**
 * 回忆页（/memos）
 *
 * 说明：该模板会在你创建一个独立页面，且 slug 为 `memos` 时自动生效。
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$this->need('header.php');

$hjUserLoggedIn = false;
$hjUserIsAdmin = false;
try {
    $hjUserLoggedIn = (bool) ($this->user && $this->user->hasLogin());
    $hjUserIsAdmin = $hjUserLoggedIn && (bool) $this->user->pass('administrator', true);
} catch (\Throwable $e) {
    $hjUserLoggedIn = false;
    $hjUserIsAdmin = false;
}

$hjAllowComment = false;
try {
    $hjAllowComment = (bool) $this->allow('comment');
} catch (\Throwable $e) {
    $hjAllowComment = false;
}
$hjCanPostMemo = $hjAllowComment && $hjUserIsAdmin;

$hjCommentToken = '';
if ($hjCanPostMemo) {
    $hjCommentReferer = '';
    try {
        $hjCommentReferer = (string) $this->request->getRequestUrl();
    } catch (\Throwable $e) {
        $hjCommentReferer = '';
    }
    try {
        $hjTokenTarget = ($hjCommentReferer !== '') ? $hjCommentReferer : (string) ($this->permalink ?? '');
        $hjCommentToken = ($hjTokenTarget !== '') ? (string) $this->security->getToken($hjTokenTarget) : '';
    } catch (\Throwable $e) {
        $hjCommentToken = '';
    }
}

$hjTagPattern = '/#([\p{L}\p{N}_\-]{1,40})/u';
$hjReactionEmojis = function_exists('hansJackMemoryReactionAllowedEmojis')
    ? hansJackMemoryReactionAllowedEmojis()
    : ['👍', '❤️', '😂', '😮', '😢', '😡', '🎉', '👏', '🔥', '🤔', '👀', '🙏', '💯', '🚀'];
$hjCommentsData = [];
$hjMonthCounts = [];
$hjTagCounts = [];
$hjLatestCreated = 0;

$hjNormalizeTag = static function ($value): string {
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    if (function_exists('mb_strtolower')) {
        return (string) mb_strtolower($value, 'UTF-8');
    }
    return strtolower($value);
};

$hjFormatDate = static function ($timestamp, $format): string {
    $time = (int) $timestamp;
    $fmt = trim((string) $format);
    if ($time <= 0 || $fmt === '') {
        return '';
    }

    try {
        $date = new \Typecho\Date($time);
        return (string) $date->format($fmt);
    } catch (\Throwable $e) {
        return date($fmt, $time);
    }
};

$this->comments()->to($hjComments);
if ($hjComments && $hjComments->have()) {
    while ($hjComments->next()) {
        $created = 0;
        try {
            $created = (int) ($hjComments->created ?? 0);
        } catch (\Throwable $e) {
            $created = 0;
        }
        if ($created > $hjLatestCreated) {
            $hjLatestCreated = $created;
        }

        $month = $hjFormatDate($created, 'Y-m');
        if ($month !== '') {
            $hjMonthCounts[$month] = (int) ($hjMonthCounts[$month] ?? 0) + 1;
        }

        $author = '';
        try {
            $author = trim((string) ($hjComments->author ?? ''));
        } catch (\Throwable $e) {
            $author = '';
        }
        if ($author === '') {
            $author = _t('匿名');
        }

        $mail = '';
        try {
            $mail = strtolower(trim((string) ($hjComments->mail ?? '')));
        } catch (\Throwable $e) {
            $mail = '';
        }
        $avatar = 'https://cdn.sep.cc/avatar/' . md5($mail) . '?s=64&d=mp&r=g';

        $rawSourceText = '';
        try {
            $rawSourceText = (string) ($hjComments->text ?? '');
        } catch (\Throwable $e) {
            $rawSourceText = '';
        }
        $isPrivateComment = function_exists('hansJackIsPrivateCommentText')
            ? hansJackIsPrivateCommentText($rawSourceText)
            : false;
        $editText = function_exists('hansJackStripPrivateCommentMarker')
            ? hansJackStripPrivateCommentMarker($rawSourceText)
            : $rawSourceText;
        $editText = str_replace(["\r\n", "\r"], "\n", (string) $editText);
        $rawText = trim($editText);

        $renderedContent = '';
        try {
            if (function_exists('hansJackRenderCommentContent')) {
                $renderedContent = (string) hansJackRenderCommentContent($hjComments);
            }
        } catch (\Throwable $e) {
            $renderedContent = '';
        }
        if ($renderedContent === '') {
            try {
                $renderedContent = (string) ($hjComments->content ?? '');
            } catch (\Throwable $e) {
                $renderedContent = '';
            }
        }

        if ($rawText === '') {
            $rawText = _t('（无内容）');
        }

        $renderedTextOnly = trim((string) preg_replace('/\s+/u', '', strip_tags($renderedContent)));
        $renderedHasMedia = preg_match('/<(img|video|audio|iframe|object|embed|svg)\b/i', $renderedContent) === 1;
        if ($renderedTextOnly === '' && !$renderedHasMedia) {
            $renderedContent = '<p>' . hansJackEscape($rawText) . '</p>';
        }

        $commentTagsMap = [];
        if (preg_match_all($hjTagPattern, $rawText, $matches) && !empty($matches[1])) {
            foreach ($matches[1] as $tagRaw) {
                $tagName = trim((string) $tagRaw);
                if ($tagName === '') {
                    continue;
                }

                $tagKey = $hjNormalizeTag($tagName);
                if ($tagKey === '') {
                    continue;
                }
                if (isset($commentTagsMap[$tagKey])) {
                    continue;
                }

                $commentTagsMap[$tagKey] = $tagName;
                if (!isset($hjTagCounts[$tagKey])) {
                    $hjTagCounts[$tagKey] = [
                        'key' => $tagKey,
                        'name' => $tagName,
                        'count' => 0,
                    ];
                }
                $hjTagCounts[$tagKey]['count'] = (int) ($hjTagCounts[$tagKey]['count'] ?? 0) + 1;
            }
        }

        $commentTags = [];
        foreach ($commentTagsMap as $tagKey => $tagName) {
            $commentTags[] = [
                'key' => $tagKey,
                'name' => $tagName,
            ];
        }

        $coid = 0;
        try {
            $coid = (int) ($hjComments->coid ?? 0);
        } catch (\Throwable $e) {
            $coid = 0;
        }
        if ($coid <= 0) {
            $coid = count($hjCommentsData) + 1;
        }

        $status = '';
        try {
            $status = (string) ($hjComments->status ?? '');
        } catch (\Throwable $e) {
            $status = '';
        }

        $hjCommentsData[] = [
            'id' => $coid,
            'author' => $author,
            'avatar' => $avatar,
            'created' => $created,
            'date' => $hjFormatDate($created, 'Y/m/d H:i:s'),
            'dateIso' => $hjFormatDate($created, 'c'),
            'month' => $month,
            'text' => $rawText,
            'editText' => $editText,
            'isPrivate' => $isPrivateComment ? 1 : 0,
            'content' => $renderedContent,
            'tags' => $commentTags,
            'tagKeys' => array_keys($commentTagsMap),
            'status' => $status,
        ];
    }
}

if (!empty($hjCommentsData)) {
    usort($hjCommentsData, static function (array $a, array $b): int {
        return (int) ($b['created'] ?? 0) <=> (int) ($a['created'] ?? 0);
    });
}

if (!empty($hjMonthCounts)) {
    krsort($hjMonthCounts, SORT_STRING);
}

$hjMonthRows = [];
foreach ($hjMonthCounts as $month => $count) {
    $label = $month;
    if (preg_match('/^(\d{4})-(\d{2})$/', (string) $month, $m)) {
        $label = $m[1] . '年' . $m[2] . '月';
    }
    $hjMonthRows[] = [
        'value' => (string) $month,
        'label' => $label,
        'count' => (int) $count,
    ];
}

$hjTagRows = array_values($hjTagCounts);
if (!empty($hjTagRows)) {
    usort($hjTagRows, static function (array $a, array $b): int {
        $aCount = (int) ($a['count'] ?? 0);
        $bCount = (int) ($b['count'] ?? 0);
        if ($aCount !== $bCount) {
            return $bCount <=> $aCount;
        }
        return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
    });
}

$hjTotalComments = count($hjCommentsData);
$hjLatestText = $hjFormatDate($hjLatestCreated, 'Y/m/d H:i:s');
if ($hjLatestText === '') {
    $hjLatestText = _t('暂无');
}
$hjPagePermalink = '';
try {
    $hjPagePermalink = trim((string) ($this->permalink ?? ''));
} catch (\Throwable $e) {
    $hjPagePermalink = '';
}
if ($hjPagePermalink === '') {
    try {
        $hjPagePermalink = trim((string) $this->options->siteUrl);
    } catch (\Throwable $e) {
        $hjPagePermalink = '';
    }
}
?>

<main class="hj-main" role="main" data-hj-memory-root>
    <section class="hj-memory" aria-label="<?php _e('回忆'); ?>">
        <div class="hj-posts-layout hj-memory-layout">
            <div class="hj-memory-main">
                <section id="comments" class="hj-comments hj-memory-comments-shell" aria-label="<?php _e('评论'); ?>">
                    <?php if ($hjCanPostMemo): ?>
                        <form method="post" action="<?php $this->commentUrl(); ?>" class="hj-comment-form hj-comment-composer-form hj-memory-form" id="hj-memory-form"
                              data-hj-comment-form data-hj-comment-role="top" data-hj-user-logged="<?php echo $hjUserLoggedIn ? '1' : '0'; ?>">
                            <div class="hj-comment-box" data-hj-comment-box>
                                <textarea
                                    rows="6"
                                    cols="50"
                                    name="text"
                                    id="hj-memory-text"
                                    class="hj-comment-textarea hj-memory-textarea"
                                    required></textarea>
                                <input type="hidden" name="author" value="<?php $this->remember('author'); ?>">
                                <input type="hidden" name="url" value="<?php $this->remember('url'); ?>">
                                <input type="hidden" name="mail" value="<?php $this->remember('mail'); ?>">
                                <?php if ($hjCommentToken !== ''): ?>
                                    <input type="hidden" name="_" value="<?php echo hansJackEscape($hjCommentToken); ?>">
                                <?php endif; ?>
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
                                        <button class="hj-comment-icon-btn hj-comment-send" type="submit" aria-label="<?php _e('提交评论'); ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send-icon lucide-send" aria-hidden="true"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div id="hj-memory-respond" class="respond hj-respond" data-hj-comment-respond data-hj-user-logged="<?php echo $hjUserLoggedIn ? '1' : '0'; ?>">
                            <form method="post" action="<?php $this->commentUrl(); ?>" class="hj-comment-form hj-comment-composer-form hj-memory-form hj-memory-reply-form" id="hj-memory-form-reply"
                                  data-hj-comment-form data-hj-comment-role="reply" data-hj-user-logged="<?php echo $hjUserLoggedIn ? '1' : '0'; ?>">
                                <div class="hj-comment-box" data-hj-comment-box>
                                    <textarea
                                        rows="6"
                                        cols="50"
                                        name="text"
                                        id="hj-memory-text-reply"
                                        class="hj-comment-textarea hj-memory-textarea"
                                        required></textarea>
                                    <input type="hidden" name="author" value="<?php $this->remember('author'); ?>">
                                    <input type="hidden" name="url" value="<?php $this->remember('url'); ?>">
                                    <input type="hidden" name="mail" value="<?php $this->remember('mail'); ?>">
                                    <?php if ($hjCommentToken !== ''): ?>
                                        <input type="hidden" name="_" value="<?php echo hansJackEscape($hjCommentToken); ?>">
                                    <?php endif; ?>
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
                                            <button class="hj-comment-icon-btn hj-comment-send" type="submit" aria-label="<?php _e('提交评论'); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-send-icon lucide-send" aria-hidden="true"><path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <?php elseif (!$hjAllowComment): ?>
                        <h2 class="hj-comments-closed"><?php _e('当前页面评论已关闭'); ?></h2>
                    <?php endif; ?>

                    <div class="hj-comments-head" aria-label="<?php _e('评论'); ?>">
                        <div class="hj-comments-head-title"><?php _e('评论'); ?></div>
                        <div class="hj-comments-head-actions" aria-label="<?php _e('操作'); ?>">
                            <button class="hj-comments-head-btn hj-comments-refresh-btn" type="button" aria-label="<?php _e('刷新评论'); ?>" title="<?php _e('刷新评论'); ?>" data-hj-comments-refresh>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw-icon lucide-refresh-cw" aria-hidden="true"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                            </button>
                            <button class="hj-comments-head-btn hj-comments-sort-btn" type="button" aria-label="<?php _e('切换为时间降序'); ?>" title="<?php _e('切换为时间降序'); ?>" data-hj-comments-sort-toggle>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clock-arrow-down-icon lucide-clock-arrow-down" aria-hidden="true"><path d="M12 6v6l2 1"/><path d="M12.337 21.994a10 10 0 1 1 9.588-8.767"/><path d="m14 18 4 4 4-4"/><path d="M18 14v8"/></svg>
                            </button>
                        </div>
                    </div>
                    <?php if (!empty($hjCommentsData)): ?>
                        <ol class="comment-list" data-hj-memory-comments>
                            <?php foreach ($hjCommentsData as $item): ?>
                                <?php
                                $tagKeys = isset($item['tagKeys']) && is_array($item['tagKeys']) ? $item['tagKeys'] : [];
                                $tagKeysAttr = implode(',', array_map('strval', $tagKeys));
                                $status = (string) ($item['status'] ?? '');
                                $commentId = (int) ($item['id'] ?? 0);
                                $commentShareUrl = $hjPagePermalink;
                                if ($commentId > 0) {
                                    $commentShareUrl .= '#comment-' . $commentId;
                                }
                                ?>
                                <li
                                    id="comment-<?php echo $commentId; ?>"
                                    class="comment-body comment-parent"
                                    data-hj-memory-item
                                    data-hj-month="<?php echo hansJackEscape((string) ($item['month'] ?? '')); ?>"
                                    data-hj-tags="<?php echo hansJackEscape($tagKeysAttr); ?>">
                                    <div class="comment-author" itemprop="creator" itemscope itemtype="http://schema.org/Person">
                                        <span itemprop="image">
                                            <img class="avatar" src="<?php echo hansJackEscape((string) ($item['avatar'] ?? '')); ?>" alt="" width="32" height="32" loading="lazy" decoding="async" referrerpolicy="no-referrer">
                                        </span>
                                        <div class="hj-comment-author-meta">
                                            <cite class="fn" itemprop="name"><?php echo hansJackEscape((string) ($item['author'] ?? '')); ?></cite>
                                            <div class="comment-meta">
                                                <time itemprop="commentTime" datetime="<?php echo hansJackEscape((string) ($item['dateIso'] ?? '')); ?>">
                                                    <?php echo hansJackEscape((string) ($item['date'] ?? '')); ?>
                                                </time>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="comment-content hj-comment-content" itemprop="commentText">
                                        <?php echo (string) ($item['content'] ?? ''); ?>
                                        <?php if ($status !== '' && $status !== 'approved'): ?>
                                            <p class="comment-awaiting-moderation"><?php _e('审核中'); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($item['tags']) && is_array($item['tags'])): ?>
                                        <div class="hj-memory-comment-tags">
                                            <?php foreach ($item['tags'] as $tag): ?>
                                                <button
                                                    type="button"
                                                    class="hj-memory-tag-chip"
                                                    data-hj-memory-tag-key="<?php echo hansJackEscape((string) ($tag['key'] ?? '')); ?>">
                                                    #<?php echo hansJackEscape((string) ($tag['name'] ?? '')); ?>
                                                </button>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="comment-reply hj-memory-comment-reply" data-hj-memory-action-row data-hj-memory-coid="<?php echo $commentId; ?>">
                                        <span class="hj-memory-reaction-wrap" data-hj-memory-reactor data-hj-memory-coid="<?php echo $commentId; ?>">
                                            <button class="hj-comment-memory-react-btn" type="button" aria-label="<?php _e('互动'); ?>" title="<?php _e('互动'); ?>" data-hj-memory-react-toggle aria-haspopup="dialog" aria-expanded="false">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-smile-icon lucide-smile" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" x2="9.01" y1="9" y2="9"/><line x1="15" x2="15.01" y1="9" y2="9"/></svg>
                                            </button>
                                            <div class="hj-emoji-picker hj-memory-emoji-picker" role="dialog" aria-label="<?php _e('互动表情'); ?>" data-hj-memory-emoji-picker hidden>
                                                <div class="hj-emoji-picker-grid hj-memory-emoji-picker-grid" role="listbox" aria-label="<?php _e('互动表情列表'); ?>">
                                                    <?php foreach ($hjReactionEmojis as $emoji): ?>
                                                        <button type="button" class="hj-emoji-picker-btn hj-memory-emoji-btn" data-hj-memory-emoji="<?php echo hansJackEscape((string) $emoji); ?>" aria-label="<?php echo hansJackEscape((string) $emoji); ?>"><?php echo hansJackEscape((string) $emoji); ?></button>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        </span>
                                        <button class="hj-comment-share-btn" type="button" aria-label="<?php _e('分享'); ?>" title="<?php _e('分享'); ?>" data-hj-comment-share="<?php echo hansJackEscape($commentShareUrl); ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-share2-icon lucide-share-2" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/></svg>
                                        </button>
                                        <?php if ($hjUserIsAdmin && $commentId > 0): ?>
                                            <button
                                                class="hj-comment-edit-btn"
                                                type="button"
                                                aria-label="<?php _e('编辑'); ?>"
                                                title="<?php _e('编辑'); ?>"
                                                data-hj-comment-edit
                                                data-hj-comment-coid="<?php echo $commentId; ?>"
                                                data-hj-comment-edit-private="<?php echo !empty($item['isPrivate']) ? '1' : '0'; ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-line-icon lucide-pencil-line" aria-hidden="true"><path d="M13 21h8"/><path d="m15 5 4 4"/><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/></svg>
                                            </button>
                                            <textarea class="hj-comment-edit-source" data-hj-comment-edit-source hidden><?php echo hansJackEscape((string) ($item['editText'] ?? '')); ?></textarea>
                                        <?php endif; ?>
                                        <span class="hj-memory-reaction-list" data-hj-memory-reactions hidden></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                        <p class="hj-memory-empty" data-hj-memory-empty hidden><?php _e('当前筛选条件下暂无评论'); ?></p>
                    <?php else: ?>
                        <p class="hj-memory-empty"><?php _e('暂无评论'); ?></p>
                    <?php endif; ?>
                </section>
            </div>

            <aside class="hj-posts-aside hj-memory-aside" aria-label="<?php _e('筛选与统计'); ?>">
                <div class="hj-posts-block" aria-label="<?php _e('时间表'); ?>">
                    <h2 class="hj-posts-block-title"><?php _e('时间表'); ?></h2>
                    <div class="hj-posts-links" data-hj-memory-month-panel>
                        <a class="hj-posts-link hj-memory-aside-link is-active" href="#comments" data-hj-memory-month="">
                            <?php _e('全部'); ?>
                            <span class="hj-memory-filter-count"><?php echo (int) $hjTotalComments; ?></span>
                        </a>
                        <?php foreach ($hjMonthRows as $row): ?>
                            <a class="hj-posts-link hj-memory-aside-link" href="#comments" data-hj-memory-month="<?php echo hansJackEscape((string) ($row['value'] ?? '')); ?>">
                                <?php echo hansJackEscape((string) ($row['label'] ?? '')); ?>
                                <span class="hj-memory-filter-count"><?php echo (int) ($row['count'] ?? 0); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="hj-posts-block" aria-label="<?php _e('标签表'); ?>">
                    <h2 class="hj-posts-block-title"><?php _e('标签表'); ?></h2>
                    <div class="hj-posts-tags" data-hj-memory-tag-panel>
                        <a class="hj-posts-tag-pill hj-memory-aside-tag is-active" href="#comments" data-hj-memory-tag-filter="">
                            <?php _e('全部'); ?>
                            <span class="hj-memory-filter-count"><?php echo (int) $hjTotalComments; ?></span>
                        </a>
                        <?php foreach ($hjTagRows as $tag): ?>
                            <a class="hj-posts-tag-pill hj-memory-aside-tag" href="#comments" data-hj-memory-tag-filter="<?php echo hansJackEscape((string) ($tag['key'] ?? '')); ?>">
                                #<?php echo hansJackEscape((string) ($tag['name'] ?? '')); ?>
                                <span class="hj-memory-filter-count"><?php echo (int) ($tag['count'] ?? 0); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="hj-posts-block" aria-label="<?php _e('统计'); ?>">
                    <h2 class="hj-posts-block-title"><?php _e('统计'); ?></h2>
                    <dl class="hj-memory-stats">
                        <div class="hj-memory-stats-row">
                            <dt><?php _e('评论'); ?></dt>
                            <dd><?php echo (int) $hjTotalComments; ?></dd>
                        </div>
                        <div class="hj-memory-stats-row">
                            <dt><?php _e('月份'); ?></dt>
                            <dd><?php echo (int) count($hjMonthRows); ?></dd>
                        </div>
                        <div class="hj-memory-stats-row">
                            <dt><?php _e('标签'); ?></dt>
                            <dd><?php echo (int) count($hjTagRows); ?></dd>
                        </div>
                        <div class="hj-memory-stats-row">
                            <dt><?php _e('最新'); ?></dt>
                            <dd><?php echo hansJackEscape($hjLatestText); ?></dd>
                        </div>
                    </dl>
                </div>
            </aside>
        </div>
    </section>
</main>

<script>
    (function () {
        var root = document.querySelector("[data-hj-memory-root]");
        if (!root) {
            return;
        }

        var items = Array.prototype.slice.call(root.querySelectorAll("[data-hj-memory-item]"));
        var monthPanel = root.querySelector("[data-hj-memory-month-panel]");
        var tagPanel = root.querySelector("[data-hj-memory-tag-panel]");
        var monthButtons = monthPanel ? Array.prototype.slice.call(monthPanel.querySelectorAll("[data-hj-memory-month]")) : [];
        var tagButtons = tagPanel ? Array.prototype.slice.call(tagPanel.querySelectorAll("[data-hj-memory-tag-filter]")) : [];
        var inlineTagButtons = Array.prototype.slice.call(root.querySelectorAll("[data-hj-memory-tag-key]"));
        var countNode = root.querySelector("[data-hj-memory-visible-count]");
        var emptyNode = root.querySelector("[data-hj-memory-empty]");

        var state = {
            month: "",
            tag: ""
        };

        function updateButtonStates() {
            monthButtons.forEach(function (btn) {
                var value = (btn.getAttribute("data-hj-memory-month") || "").trim();
                btn.classList.toggle("is-active", value === state.month);
            });

            tagButtons.forEach(function (btn) {
                var value = (btn.getAttribute("data-hj-memory-tag-filter") || "").trim();
                btn.classList.toggle("is-active", value === state.tag);
            });
        }

        function applyFilters() {
            var visible = 0;
            var total = items.length;

            items.forEach(function (item) {
                var month = (item.getAttribute("data-hj-month") || "").trim();
                var tagsRaw = (item.getAttribute("data-hj-tags") || "").trim();
                var tags = tagsRaw === "" ? [] : tagsRaw.split(",");
                var matchMonth = !state.month || month === state.month;
                var matchTag = !state.tag || tags.indexOf(state.tag) !== -1;
                var show = matchMonth && matchTag;
                item.hidden = !show;
                if (show) {
                    visible += 1;
                }
            });

            if (countNode) {
                countNode.textContent = "显示 " + visible + " / " + total + " 条";
            }
            if (emptyNode) {
                emptyNode.hidden = visible > 0;
            }
            updateButtonStates();
        }

        if (monthPanel) {
            monthPanel.addEventListener("click", function (event) {
                var target = event.target && event.target.closest ? event.target.closest("[data-hj-memory-month]") : null;
                if (!target) {
                    return;
                }
                if (event && typeof event.preventDefault === "function") {
                    event.preventDefault();
                }
                state.month = (target.getAttribute("data-hj-memory-month") || "").trim();
                applyFilters();
            });
        }

        if (tagPanel) {
            tagPanel.addEventListener("click", function (event) {
                var target = event.target && event.target.closest ? event.target.closest("[data-hj-memory-tag-filter]") : null;
                if (!target) {
                    return;
                }
                if (event && typeof event.preventDefault === "function") {
                    event.preventDefault();
                }
                state.tag = (target.getAttribute("data-hj-memory-tag-filter") || "").trim();
                applyFilters();
            });
        }

        inlineTagButtons.forEach(function (btn) {
            btn.addEventListener("click", function () {
                state.tag = (btn.getAttribute("data-hj-memory-tag-key") || "").trim();
                applyFilters();
            });
        });

        applyFilters();
    })();
</script>

<script>
    (function () {
        var root = document.querySelector("[data-hj-memory-root]");
        if (!root || !window.fetch || !window.FormData) {
            return;
        }

        var reactors = Array.prototype.slice.call(root.querySelectorAll("[data-hj-memory-reactor][data-hj-memory-coid]"));
        if (!reactors || reactors.length === 0) {
            return;
        }

        var entries = {};
        reactors.forEach(function (reactor) {
            var coid = parseInt(reactor.getAttribute("data-hj-memory-coid") || "0", 10);
            if (!isFinite(coid) || coid <= 0) {
                return;
            }

            var row = reactor.closest("[data-hj-memory-action-row]");
            var entry = {
                coid: coid,
                reactor: reactor,
                row: row,
                summary: row ? row.querySelector("[data-hj-memory-reactions]") : null,
                toggle: reactor.querySelector("[data-hj-memory-react-toggle]"),
                picker: reactor.querySelector("[data-hj-memory-emoji-picker]"),
                busy: false
            };
            entries[String(coid)] = entry;
        });

        var coids = Object.keys(entries);
        if (!coids.length) {
            return;
        }

        var emojiOrder = [];
        coids.forEach(function (coidKey) {
            var entry = entries[coidKey];
            if (!entry || !entry.picker) {
                return;
            }
            var buttons = Array.prototype.slice.call(entry.picker.querySelectorAll("[data-hj-memory-emoji]"));
            buttons.forEach(function (btn) {
                var emoji = (btn.getAttribute("data-hj-memory-emoji") || "").trim();
                if (!emoji) {
                    return;
                }
                if (emojiOrder.indexOf(emoji) === -1) {
                    emojiOrder.push(emoji);
                }
            });
        });
        if (!emojiOrder.length) {
            emojiOrder = ["👍", "❤️", "😂", "😮", "😢", "😡", "🎉", "👏", "🔥", "🤔", "👀", "🙏", "💯", "🚀"];
        }

        var openEntry = null;

        function parseJSON(text) {
            try {
                return JSON.parse(text);
            } catch (e) {
                return null;
            }
        }

        function buildEndpoint(extraQuery) {
            var raw = "";
            try {
                raw = String(window.location.href || "");
            } catch (e) {
                raw = "";
            }

            try {
                var url = new URL(raw);
                url.hash = "";
                url.searchParams.set("hj_memory_reaction", "1");
                if (extraQuery && typeof extraQuery === "object") {
                    Object.keys(extraQuery).forEach(function (key) {
                        var value = String(extraQuery[key] || "").trim();
                        if (!value) {
                            return;
                        }
                        url.searchParams.set(key, value);
                    });
                }
                return url.toString();
            } catch (e) {
                var clean = raw ? raw.split("#")[0] : "";
                if (!clean) {
                    clean = "?";
                }
                var sep = clean.indexOf("?") === -1 ? "?" : "&";
                var built = clean + sep + "hj_memory_reaction=1";
                if (extraQuery && typeof extraQuery === "object") {
                    Object.keys(extraQuery).forEach(function (key) {
                        var value = String(extraQuery[key] || "").trim();
                        if (!value) {
                            return;
                        }
                        built += "&" + encodeURIComponent(key) + "=" + encodeURIComponent(value);
                    });
                }
                return built;
            }
        }

        function setPickerOpen(entry, isOpen) {
            if (!entry || !entry.picker || !entry.toggle) {
                return;
            }

            if (isOpen && openEntry && openEntry !== entry) {
                setPickerOpen(openEntry, false);
            }

            entry.reactor.classList.toggle("is-open", !!isOpen);
            entry.picker.hidden = !isOpen;
            entry.toggle.setAttribute("aria-expanded", isOpen ? "true" : "false");

            if (isOpen) {
                openEntry = entry;
            } else if (openEntry === entry) {
                openEntry = null;
            }
        }

        function closeOpenPicker() {
            if (!openEntry) {
                return;
            }
            setPickerOpen(openEntry, false);
        }

        function renderEntry(entry, payload) {
            if (!entry) {
                return;
            }

            var selected = payload && typeof payload.selected === "string" ? payload.selected : "";
            var counts = payload && payload.counts && typeof payload.counts === "object" ? payload.counts : {};

            if (entry.toggle) {
                entry.toggle.classList.toggle("is-reacted", !!selected);
                entry.toggle.setAttribute("title", selected ? ("已选择 " + selected) : "互动");
            }

            if (!entry.summary) {
                return;
            }

            while (entry.summary.firstChild) {
                entry.summary.removeChild(entry.summary.firstChild);
            }

            var hasAny = false;
            emojiOrder.forEach(function (emoji) {
                var count = Number(counts[emoji] || 0);
                if (!isFinite(count) || count <= 0) {
                    return;
                }
                hasAny = true;

                var chip = document.createElement("span");
                chip.className = "hj-memory-reaction-chip";
                if (selected === emoji) {
                    chip.classList.add("is-active");
                }
                var emojiNode = document.createElement("span");
                emojiNode.className = "hj-memory-reaction-emoji";
                emojiNode.textContent = emoji;
                var countNode = document.createElement("span");
                countNode.className = "hj-memory-reaction-count";
                countNode.textContent = String(count);
                chip.appendChild(emojiNode);
                chip.appendChild(countNode);
                entry.summary.appendChild(chip);
            });

            entry.summary.hidden = !hasAny;
        }

        function applyPayload(comments) {
            var map = comments && typeof comments === "object" ? comments : {};
            coids.forEach(function (coidKey) {
                renderEntry(entries[coidKey], map[coidKey] || null);
            });
        }

        function requestInitialState() {
            var endpoint = buildEndpoint({ coids: coids.join(",") });
            window.fetch(endpoint, {
                method: "GET",
                credentials: "same-origin",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            }).then(function (response) {
                return response.text().then(function (text) {
                    var payload = parseJSON(text);
                    if (!response.ok || !payload || !payload.ok) {
                        throw new Error("load_failed");
                    }
                    return payload;
                });
            }).then(function (payload) {
                if (payload && payload.emojis && payload.emojis.length) {
                    var nextOrder = [];
                    payload.emojis.forEach(function (emoji) {
                        var e = String(emoji || "").trim();
                        if (!e || nextOrder.indexOf(e) !== -1) {
                            return;
                        }
                        nextOrder.push(e);
                    });
                    if (nextOrder.length) {
                        emojiOrder = nextOrder;
                    }
                }
                applyPayload(payload.comments || {});
            }).catch(function () {
                applyPayload({});
            });
        }

        function submitReaction(entry, emoji) {
            if (!entry || !emoji || entry.busy) {
                return;
            }

            entry.busy = true;
            entry.reactor.classList.add("is-busy");

            var formData = new FormData();
            formData.append("action", "set");
            formData.append("coid", String(entry.coid));
            formData.append("emoji", emoji);

            window.fetch(buildEndpoint(), {
                method: "POST",
                body: formData,
                credentials: "same-origin",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            }).then(function (response) {
                return response.text().then(function (text) {
                    var payload = parseJSON(text);
                    if (!response.ok || !payload || !payload.ok) {
                        var message = payload && payload.message ? String(payload.message) : "互动失败，请稍后重试";
                        throw new Error(message);
                    }
                    return payload;
                });
            }).then(function (payload) {
                applyPayload(payload.comments || {});
            }).catch(function (err) {
                var msg = err && err.message ? String(err.message) : "互动失败，请稍后重试";
                try {
                    window.alert(msg);
                } catch (e) {}
            }).finally(function () {
                entry.busy = false;
                entry.reactor.classList.remove("is-busy");
                setPickerOpen(entry, false);
            });
        }

        coids.forEach(function (coidKey) {
            var entry = entries[coidKey];
            if (!entry || !entry.toggle || !entry.picker) {
                return;
            }

            entry.toggle.addEventListener("click", function (event) {
                if (event && event.preventDefault) {
                    event.preventDefault();
                }
                var isOpen = entry.reactor.classList.contains("is-open");
                setPickerOpen(entry, !isOpen);
            });

            entry.picker.addEventListener("click", function (event) {
                var target = event && event.target && event.target.closest
                    ? event.target.closest("[data-hj-memory-emoji]")
                    : null;
                if (!target) {
                    return;
                }
                if (event && event.preventDefault) {
                    event.preventDefault();
                }
                var emoji = (target.getAttribute("data-hj-memory-emoji") || "").trim();
                if (!emoji) {
                    return;
                }
                submitReaction(entry, emoji);
            });
        });

        document.addEventListener("mousedown", function (event) {
            if (!openEntry || !openEntry.reactor) {
                return;
            }
            var target = event && event.target ? event.target : null;
            if (!target) {
                return;
            }
            if (openEntry.reactor.contains(target)) {
                return;
            }
            closeOpenPicker();
        }, true);

        window.addEventListener("keydown", function (event) {
            var key = event && (event.key || event.code);
            if (key === "Escape" || key === "Esc") {
                closeOpenPicker();
            }
        }, true);

        applyPayload({});
        requestInitialState();
    })();
</script>

<?php $this->need('footer.php'); ?>
