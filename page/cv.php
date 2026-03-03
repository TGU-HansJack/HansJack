<?php
/**
 * CV 页（/cv）
 *
 * 说明：该模板会在你创建一个独立页面，且 slug 为 `cv` 时自动生效。
 *
 * 自定义字段（页面编辑 -> 自定义字段）建议使用以下 key：
 * - avatar: 头像图片 URL
 * - avatar_link: 点击头像跳转链接（可留空）
 * - full_resume: “完整简历”按钮链接（可留空）
 * - name: 姓名
 * - email: 邮箱
 * - github: GitHub（URL 或用户名）
 * - political: 政治面貌
 * - native_place: 籍贯
 * - nation: 民族
 * - age: 年龄
 * - address: 地址
 * - education: 学历
 * - blog: 博客（URL）
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

if (!function_exists('cvField')) {
    /**
     * @param mixed $archive
     */
    function cvField($archive, string $key, int $max = 0): string
    {
        if (!$archive) {
            return '';
        }

        $value = '';
        try {
            $value = (string) ($archive->fields->{$key} ?? '');
        } catch (\Throwable $e) {
            $value = '';
        }

        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if ($max > 0) {
            if (function_exists('mb_substr')) {
                $value = (string) mb_substr($value, 0, $max, 'UTF-8');
            } else {
                $value = substr($value, 0, $max);
            }
        }

        return $value;
    }
}

if (!function_exists('cvFirstField')) {
    /**
     * @param mixed $archive
     * @param string[] $keys
     */
    function cvFirstField($archive, array $keys, int $max = 0): string
    {
        foreach ($keys as $key) {
            $v = cvField($archive, (string) $key, $max);
            if ($v !== '') {
                return $v;
            }
        }
        return '';
    }
}

if (!function_exists('cvGitHubUrl')) {
    function cvGitHubUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^https?:\\/\\//i', $value)) {
            return $value;
        }

        $value = ltrim($value, '@');

        if (stripos($value, 'github.com') !== false) {
            return 'https://' . $value;
        }

        return 'https://github.com/' . $value;
    }
}

if (!function_exists('cvSafeUrl')) {
    function cvSafeUrl(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^https?:\\/\\//i', $value)) {
            return $value;
        }

        if (preg_match('/^\\//', $value)) {
            return $value;
        }

        return '';
    }
}

$this->need('header.php');

$avatarUrl = cvField($this, 'avatar', 800);
if ($avatarUrl === '') {
    ob_start();
    $this->options->siteUrl('favicon.ico');
    $avatarUrl = trim((string) ob_get_clean());
}

$avatarLink = cvFirstField($this, ['avatar_link', 'avatarLink'], 800);
$avatarLinkHref = cvSafeUrl($avatarLink);

$fullResumeUrl = cvFirstField(
    $this,
    ['full_resume', 'full_resume_url', 'full_cv', 'full_cv_url', 'resume', 'resume_url'],
    800
);
$fullResumeHref = cvSafeUrl($fullResumeUrl);

$cvName = cvFirstField($this, ['name', 'full_name', 'fullname'], 120);
$cvEmail = cvFirstField($this, ['email', 'mail'], 190);
$cvGitHubRaw = cvField($this, 'github', 200);
$cvGitHubHref = $cvGitHubRaw !== '' ? cvGitHubUrl($cvGitHubRaw) : '';
$cvPolitical = cvField($this, 'political', 120);
$cvNativePlace = cvFirstField($this, ['native_place', 'nativePlace'], 120);
$cvNation = cvFirstField($this, ['nation', 'ethnicity'], 120);
$cvAge = cvField($this, 'age', 40);
$cvAddress = cvField($this, 'address', 200);
$cvEducation = cvFirstField($this, ['education', 'degree'], 200);
$cvBlog = cvFirstField($this, ['blog', 'blog_url', 'blogUrl'], 300);
$cvBlogHref = cvSafeUrl($cvBlog);

$metaRows = [
    ['label' => '邮箱', 'value' => $cvEmail, 'type' => 'email'],
    ['label' => 'GitHub', 'value' => $cvGitHubRaw, 'type' => 'url', 'href' => $cvGitHubHref],
    ['label' => '博客', 'value' => $cvBlog, 'type' => $cvBlogHref !== '' ? 'url' : 'text', 'href' => $cvBlogHref],
    ['label' => '政治面貌', 'value' => $cvPolitical, 'type' => 'text'],
    ['label' => '籍贯', 'value' => $cvNativePlace, 'type' => 'text'],
    ['label' => '民族', 'value' => $cvNation, 'type' => 'text'],
    ['label' => '年龄', 'value' => $cvAge, 'type' => 'text'],
    ['label' => '地址', 'value' => $cvAddress, 'type' => 'text'],
    ['label' => '学历', 'value' => $cvEducation, 'type' => 'text'],
];

?>

<main class="main" role="main">
    <section class="cv" aria-label="<?php _e('CV'); ?>">
        <div class="cv-head">
            <div class="cv-avatar" aria-label="<?php _e('头像'); ?>">
                <?php if ($avatarLinkHref !== ''): ?>
                    <a href="<?php echo escape($avatarLinkHref); ?>" target="_blank" rel="noreferrer">
                        <img src="<?php echo escape($avatarUrl); ?>" alt="" width="112" height="112" loading="eager" decoding="async" fetchpriority="high">
                    </a>
                <?php else: ?>
                    <img src="<?php echo escape($avatarUrl); ?>" alt="" width="112" height="112" loading="eager" decoding="async" fetchpriority="high">
                <?php endif; ?>
            </div>

            <div class="cv-info">
                <?php if ($cvName !== ''): ?>
                    <h1 class="cv-name"><?php echo escape($cvName); ?></h1>
                <?php endif; ?>

                <dl class="cv-meta">
                    <?php foreach ($metaRows as $row): ?>
                        <?php
                        $label = (string) ($row['label'] ?? '');
                        $value = (string) ($row['value'] ?? '');
                        $type = (string) ($row['type'] ?? 'text');
                        $href = (string) ($row['href'] ?? '');

                        if ($label === '' || $value === '') {
                            continue;
                        }
                        ?>
                        <div class="cv-meta-item">
                            <dt><?php echo escape($label); ?></dt>
                            <dd>
                                <?php if ($type === 'email' && strpos($value, '@') !== false): ?>
                                    <a class="cv-link" href="mailto:<?php echo escape($value); ?>"><?php echo escape($value); ?></a>
                                <?php elseif ($type === 'url'): ?>
                                    <?php
                                    $url = $href !== '' ? $href : $value;
                                    $safe = cvSafeUrl($url);
                                    ?>
                                    <?php if ($safe !== ''): ?>
                                        <a class="cv-link" href="<?php echo escape($safe); ?>" target="_blank" rel="noreferrer"><?php echo escape($value); ?></a>
                                    <?php else: ?>
                                        <?php echo escape($value); ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php echo escape($value); ?>
                                <?php endif; ?>
                            </dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
            </div>

            <?php if ($fullResumeHref !== ''): ?>
                <div class="cv-actions">
                    <a class="cv-full-btn" href="<?php echo escape($fullResumeHref); ?>" target="_blank" rel="noreferrer"><?php _e('完整简历'); ?></a>
                </div>
            <?php endif; ?>
        </div>

        <div class="article-content cv-content">
            <?php echoArchiveContent($this); ?>
        </div>
    </section>
</main>

<?php $this->need('footer.php'); ?>
