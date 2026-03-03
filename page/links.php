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

if (!function_exists('v3aLinksStr')) {
    /**
     * @param mixed $value
     */
    function v3aLinksStr($value, int $max = 255): string
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

if (!function_exists('linksJsonExit')) {
    /**
     * @param array<string,mixed> $payload
     */
    function linksJsonExit(array $payload, int $statusCode = 200): void
    {
        if (!headers_sent()) {
            http_response_code($statusCode);
            header('Content-Type: application/json; charset=UTF-8');
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        }

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json)) {
            $json = '{"ok":false,"message":"编码失败。"}';
        }
        echo $json;
        exit;
    }
}

if (!function_exists('linksFeedExcerpt')) {
    function linksFeedExcerpt(string $value, int $max = 120): string
    {
        $text = trim($value);
        if ($text === '') {
            return '';
        }

        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim((string) $text);
        if ($text === '') {
            return '';
        }

        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($text, 'UTF-8') > $max) {
                return (string) mb_substr($text, 0, $max, 'UTF-8') . '...';
            }
            return $text;
        }

        if (strlen($text) > $max) {
            return substr($text, 0, $max) . '...';
        }

        return $text;
    }
}

if (!function_exists('linksFeedResolveUrl')) {
    function linksFeedResolveUrl(string $url, string $feedUrl): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }

        if (preg_match('/^https?:\\/\\//i', $url)) {
            return $url;
        }

        if (strpos($url, '//') === 0) {
            $scheme = (string) (parse_url($feedUrl, PHP_URL_SCHEME) ?: 'https');
            return $scheme . ':' . $url;
        }

        if (class_exists('\\Typecho\\Common')) {
            return (string) \Typecho\Common::url($url, $feedUrl);
        }

        return $url;
    }
}

if (!function_exists('linksFeedFetchBody')) {
    /**
     * @return array{ok:bool,body:string,message:string}
     */
    function linksFeedFetchBody(string $url): array
    {
        $url = trim($url);
        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return ['ok' => false, 'body' => '', 'message' => '订阅地址无效。'];
        }

        $scheme = strtolower((string) (parse_url($url, PHP_URL_SCHEME) ?? ''));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return ['ok' => false, 'body' => '', 'message' => '仅支持 http/https 订阅地址。'];
        }

        $ua = 'ThemeFeedPreview/1.0';

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($ch !== false) {
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_USERAGENT, $ua);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Accept: application/rss+xml, application/atom+xml, application/xml, text/xml, */*',
                ]);

                $body = curl_exec($ch);
                $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $err = (string) curl_error($ch);
                curl_close($ch);

                if (is_string($body) && $body !== '' && $code >= 200 && $code < 400) {
                    return ['ok' => true, 'body' => $body, 'message' => ''];
                }

                return ['ok' => false, 'body' => '', 'message' => $err !== '' ? $err : '订阅抓取失败。'];
            }
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 10,
                'follow_location' => 1,
                'max_redirects' => 3,
                'header' => "User-Agent: {$ua}\r\nAccept: application/rss+xml, application/atom+xml, application/xml, text/xml, */*\r\n",
            ],
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        if (is_string($body) && $body !== '') {
            return ['ok' => true, 'body' => $body, 'message' => ''];
        }

        return ['ok' => false, 'body' => '', 'message' => '订阅抓取失败。'];
    }
}

if (!function_exists('linksFeedParseItems')) {
    /**
     * @return array<int,array{title:string,url:string,publishedAt:int,summary:string}>
     */
    function linksFeedParseItems(string $xml, string $feedUrl, int $limit = 8): array
    {
        $items = [];
        if (trim($xml) === '') {
            return $items;
        }
        if (!function_exists('simplexml_load_string')) {
            return $items;
        }

        $old = libxml_use_internal_errors(true);
        try {
            $feed = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NONET);
        } catch (\Throwable $e) {
            $feed = false;
        }

        if (!$feed) {
            libxml_clear_errors();
            libxml_use_internal_errors($old);
            return $items;
        }

        $pushItem = function (string $title, string $url, string $summary, int $publishedAt) use (&$items): void {
            $title = linksFeedExcerpt($title, 120);
            if ($title === '') {
                $title = '未命名文章';
            }

            $summary = linksFeedExcerpt($summary, 150);
            if ($summary === '') {
                $summary = '暂无摘要';
            }

            $items[] = [
                'title' => $title,
                'url' => $url,
                'publishedAt' => max(0, $publishedAt),
                'summary' => $summary,
            ];
        };

        if (isset($feed->channel->item) || isset($feed->item)) {
            $channels = isset($feed->channel) ? [$feed->channel] : [$feed];
            foreach ($channels as $channel) {
                if (!isset($channel->item)) {
                    continue;
                }

                foreach ($channel->item as $item) {
                    $title = trim((string) ($item->title ?? ''));
                    $link = trim((string) ($item->link ?? ''));
                    $summary = trim((string) ($item->description ?? ''));
                    $publishedText = trim((string) ($item->pubDate ?? ''));

                    $ns = $item->getNamespaces(true);
                    if ($summary === '' && isset($ns['content'])) {
                        $contentChild = $item->children($ns['content']);
                        $summary = trim((string) ($contentChild->encoded ?? ''));
                    }
                    if ($publishedText === '' && isset($ns['dc'])) {
                        $dcChild = $item->children($ns['dc']);
                        $publishedText = trim((string) ($dcChild->date ?? ''));
                    }

                    $publishedAt = $publishedText !== '' ? (int) strtotime($publishedText) : 0;
                    $link = linksFeedResolveUrl($link, $feedUrl);
                    if ($link !== '' && filter_var($link, FILTER_VALIDATE_URL) === false) {
                        $link = '';
                    }

                    $pushItem($title, $link, $summary, $publishedAt);
                }
            }
        }

        if (isset($feed->entry)) {
            foreach ($feed->entry as $entry) {
                $title = trim((string) ($entry->title ?? ''));
                $summary = trim((string) ($entry->summary ?? ''));
                if ($summary === '') {
                    $summary = trim((string) ($entry->content ?? ''));
                }

                $publishedText = trim((string) ($entry->updated ?? ''));
                if ($publishedText === '') {
                    $publishedText = trim((string) ($entry->published ?? ''));
                }
                $publishedAt = $publishedText !== '' ? (int) strtotime($publishedText) : 0;

                $link = '';
                if (isset($entry->link)) {
                    foreach ($entry->link as $linkNode) {
                        $attrs = $linkNode->attributes();
                        $href = trim((string) ($attrs['href'] ?? ''));
                        $rel = strtolower(trim((string) ($attrs['rel'] ?? 'alternate')));
                        if ($href !== '' && ($rel === '' || $rel === 'alternate')) {
                            $link = $href;
                            break;
                        }
                        if ($link === '' && $href !== '') {
                            $link = $href;
                        }
                    }
                }

                $link = linksFeedResolveUrl($link, $feedUrl);
                if ($link !== '' && filter_var($link, FILTER_VALIDATE_URL) === false) {
                    $link = '';
                }

                $pushItem($title, $link, $summary, $publishedAt);
            }
        }

        usort($items, function (array $a, array $b): int {
            return ((int) ($b['publishedAt'] ?? 0)) <=> ((int) ($a['publishedAt'] ?? 0));
        });

        if (count($items) > $limit) {
            $items = array_slice($items, 0, $limit);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($old);

        return $items;
    }
}

if (!function_exists('linksHealthCheck')) {
    /**
     * @return array{state:string,httpCode:int,latencyMs:int,message:string}
     */
    function linksHealthCheck(string $url): array
    {
        $url = trim($url);
        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return [
                'state' => 'red',
                'httpCode' => 0,
                'latencyMs' => 0,
                'message' => '链接无效',
            ];
        }

        $scheme = strtolower((string) (parse_url($url, PHP_URL_SCHEME) ?? ''));
        if (!in_array($scheme, ['http', 'https'], true)) {
            return [
                'state' => 'red',
                'httpCode' => 0,
                'latencyMs' => 0,
                'message' => '协议不支持',
            ];
        }

        $httpCode = 0;
        $latencyMs = 0;
        $message = '';

        if (function_exists('curl_init')) {
            $runCurl = static function (string $targetUrl, bool $headOnly) use (&$httpCode, &$latencyMs, &$message): bool {
                $ch = curl_init($targetUrl);
                if ($ch === false) {
                    return false;
                }

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_USERAGENT, 'ThemeLinkHealth/1.0');
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ]);

                if ($headOnly) {
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                } else {
                    curl_setopt($ch, CURLOPT_HTTPGET, true);
                    // Only fetch a tiny payload for health check.
                    curl_setopt($ch, CURLOPT_RANGE, '0-0');
                }

                $ret = curl_exec($ch);
                $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $time = (float) curl_getinfo($ch, CURLINFO_TOTAL_TIME);
                $errno = (int) curl_errno($ch);
                $err = (string) curl_error($ch);
                curl_close($ch);

                $httpCode = $code > 0 ? $code : $httpCode;
                $latencyMs = max(1, (int) round($time * 1000));
                if ($errno !== 0 && $err !== '') {
                    $message = $err;
                }

                if ($errno !== 0) {
                    return false;
                }

                return is_string($ret) || $headOnly;
            };

            $ok = $runCurl($url, true);
            if (!$ok || $httpCode === 0 || $httpCode >= 400) {
                $runCurl($url, false);
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'timeout' => 10,
                    'follow_location' => 1,
                    'max_redirects' => 3,
                    'header' => "User-Agent: ThemeLinkHealth/1.0\r\nRange: bytes=0-0\r\nAccept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n",
                    'ignore_errors' => true,
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                ],
            ]);

            $start = microtime(true);
            @file_get_contents($url, false, $context);
            $latencyMs = max(1, (int) round((microtime(true) - $start) * 1000));

            $headers = [];
            if (isset($http_response_header) && is_array($http_response_header)) {
                $headers = $http_response_header;
            }
            foreach ($headers as $line) {
                if (preg_match('/^HTTP\\/\\d(?:\\.\\d)?\\s+(\\d{3})/i', (string) $line, $m)) {
                    $httpCode = (int) ($m[1] ?? 0);
                }
            }
        }

        $state = 'red';
        if ($httpCode >= 200 && $httpCode < 400) {
            // Slow but reachable links are marked as warning.
            $state = ($latencyMs >= 1800) ? 'yellow' : 'green';
        } elseif (in_array($httpCode, [401, 403, 405, 429], true)) {
            // Restricted but alive endpoints.
            $state = 'yellow';
        }

        return [
            'state' => $state,
            'httpCode' => max(0, $httpCode),
            'latencyMs' => max(0, $latencyMs),
            'message' => $message,
        ];
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

$isFeedPreviewRequest = (string) ($request->get('links_feed_preview') ?? '') === '1';
$feedPreviewLinkId = (int) ($request->get('link_id') ?? 0);
$isHealthCheckRequest = (string) ($request->get('links_health_check') ?? '') === '1';
$healthCheckLinkId = (int) ($request->get('link_id') ?? 0);

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

        if ($isFeedPreviewRequest) {
            if ($feedPreviewLinkId <= 0) {
                linksJsonExit([
                    'ok' => false,
                    'message' => '参数错误。',
                ], 400);
            }

            $stmt = $pdo->prepare('SELECT id,name,feed FROM v3a_friend_link WHERE id = :id AND status = :status LIMIT 1');
            $stmt->execute([
                ':id' => $feedPreviewLinkId,
                ':status' => 1,
            ]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!is_array($row)) {
                linksJsonExit([
                    'ok' => false,
                    'message' => '未找到订阅信息。',
                ], 404);
            }

            $feedUrl = trim((string) ($row['feed'] ?? ''));
            if ($feedUrl === '') {
                linksJsonExit([
                    'ok' => false,
                    'message' => '无订阅信息。',
                ], 404);
            }

            $remote = linksFeedFetchBody($feedUrl);
            if (empty($remote['ok'])) {
                linksJsonExit([
                    'ok' => false,
                    'message' => (string) ($remote['message'] ?? '订阅抓取失败。'),
                ], 502);
            }

            $items = linksFeedParseItems((string) ($remote['body'] ?? ''), $feedUrl, 8);
            linksJsonExit([
                'ok' => true,
                'title' => trim((string) ($row['name'] ?? '')),
                'items' => $items,
            ]);
        }

        if ($isHealthCheckRequest) {
            if ($healthCheckLinkId <= 0) {
                linksJsonExit([
                    'ok' => false,
                    'message' => '参数错误。',
                ], 400);
            }

            $stmt = $pdo->prepare('SELECT id,url FROM v3a_friend_link WHERE id = :id AND status = :status LIMIT 1');
            $stmt->execute([
                ':id' => $healthCheckLinkId,
                ':status' => 1,
            ]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!is_array($row)) {
                linksJsonExit([
                    'ok' => false,
                    'message' => '未找到链接信息。',
                ], 404);
            }

            $urlRaw = trim((string) ($row['url'] ?? ''));
            $health = linksHealthCheck($urlRaw);
            linksJsonExit([
                'ok' => true,
                'linkId' => (int) ($row['id'] ?? 0),
                'state' => (string) ($health['state'] ?? 'red'),
                'httpCode' => (int) ($health['httpCode'] ?? 0),
                'latencyMs' => (int) ($health['latencyMs'] ?? 0),
            ]);
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
                $name = v3aLinksStr($request->get('name', ''), 100);
                $url = v3aLinksStr($request->get('url', ''), 255);
                $feed = v3aLinksStr($request->get('feed', ''), 500);
                $avatar = v3aLinksStr($request->get('avatar', ''), 500);
                $description = v3aLinksStr($request->get('description', ''), 200);
                $email = v3aLinksStr($request->get('email', ''), 190);
                $message = v3aLinksStr($request->get('message', ''), 500);

                $type = (string) ($applySettings['defaultType'] ?? 'friend');
                if (!in_array($type, ['friend', 'collection'], true)) {
                    $type = 'friend';
                }

                if (!empty($applySettings['allowTypeSelect'])) {
                    $t = strtolower(v3aLinksStr($request->get('type', ''), 20));
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
                } elseif ($feed !== '' && filter_var($feed, FILTER_VALIDATE_URL) === false) {
                    $noticeType = 'error';
                    $noticeMessage = '订阅地址格式不正确（需包含 http/https）。';
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
                                'feed' => $feed,
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

        $stmt = $pdo->prepare('SELECT id,name,url,feed,avatar,description,type FROM v3a_friend_link WHERE status = :status ORDER BY created DESC');
        $stmt->execute([':status' => 1]);
        $links = (array) $stmt->fetchAll();
    }
} catch (\Throwable $e) {
    $noticeType = 'error';
    $noticeMessage = $noticeMessage ?: ('加载失败：' . $e->getMessage());
    if ($isFeedPreviewRequest) {
        linksJsonExit([
            'ok' => false,
            'message' => $noticeMessage,
        ], 500);
    }
    if ($isHealthCheckRequest) {
        linksJsonExit([
            'ok' => false,
            'message' => $noticeMessage,
        ], 500);
    }
}

if ($isFeedPreviewRequest) {
    linksJsonExit([
        'ok' => false,
        'message' => $v3aEnabled ? '暂时无法加载订阅信息。' : '未启用 Vue3Admin 插件，无法加载订阅信息。',
    ], 400);
}

if ($isHealthCheckRequest) {
    linksJsonExit([
        'ok' => false,
        'message' => $v3aEnabled ? '暂时无法检查链接状态。' : '未启用 Vue3Admin 插件，无法检查链接状态。',
    ], 400);
}

$siteTitleRaw = trim((string) ($this->options->title ?? ''));
$siteTitleText = $siteTitleRaw !== '' ? $siteTitleRaw : '本站';
$siteTitle = escape($siteTitleText);

$siteInitialRaw = $siteTitleText;
if (function_exists('mb_substr')) {
    $siteInitialRaw = (string) mb_substr($siteTitleText, 0, 1);
} else {
    $siteInitialRaw = substr($siteTitleText, 0, 1);
}
$siteInitial = escape($siteInitialRaw !== '' ? $siteInitialRaw : '站');

ob_start();
$this->options->siteUrl();
$siteUrlRaw = trim((string) ob_get_clean());
$siteUrlHref = $siteUrlRaw !== '' ? escape($siteUrlRaw) : '';
$siteUrlText = $siteUrlRaw !== '' ? escape(rtrim($siteUrlRaw, '/')) : '—';

ob_start();
$this->options->feedUrl();
$siteFeedRaw = trim((string) ob_get_clean());

ob_start();
$this->options->siteUrl('favicon.ico');
$siteFaviconRaw = trim((string) ob_get_clean());
$siteFavicon = $siteFaviconRaw !== '' ? escape($siteFaviconRaw) : '';

$siteDescRaw = trim((string) ($this->options->description ?? ''));
$siteDesc = escape($siteDescRaw !== '' ? $siteDescRaw : '欢迎交换友情链接。');
$siteCopyName = escape($siteTitleText);
$siteCopyDesc = escape($siteDescRaw !== '' ? $siteDescRaw : '欢迎交换友情链接。');
$siteCopyUrl = $siteUrlRaw !== '' ? escape(rtrim($siteUrlRaw, '/')) : '';
$siteCopyFeed = $siteFeedRaw !== '' ? escape($siteFeedRaw) : '';
$siteCopyAvatar = $siteFaviconRaw !== '' ? escape($siteFaviconRaw) : '';

$this->need('header.php');
?>

<main class="main" role="main">
    <article class="page links-page" aria-label="<?php _e('友情链接'); ?>">
        <div class="article-content">
            <?php echoArchiveContent($this); ?>
        </div>

        <section class="links" aria-label="<?php _e('友链'); ?>">
            <?php if (!$v3aEnabled): ?>
                <p class="links-empty"><?php _e('未启用 Vue3Admin 插件，无法加载友链数据。'); ?></p>
            <?php else: ?>
                <?php if (!empty($links)): ?>
                    <ul class="links-list" aria-label="<?php _e('友情链接列表'); ?>">
                        <?php foreach ((array) $links as $link):
                            $rawName = (string) ($link['name'] ?? '');
                            $name = escape($rawName);
                            $linkId = (int) ($link['id'] ?? 0);
                            $url = escape((string) ($link['url'] ?? ''));
                            $feed = trim((string) ($link['feed'] ?? ''));
                            $feed = $feed !== '' ? escape($feed) : '';
                            $desc = escape((string) ($link['description'] ?? ''));

                            $avatar = trim((string) ($link['avatar'] ?? ''));
                            $avatar = $avatar !== '' ? escape($avatar) : '';

                            $type = strtolower(trim((string) ($link['type'] ?? 'friend')));
                            $typeLabel = $type === 'collection' ? '收藏' : '朋友';
                            $typeLabel = escape($typeLabel);

                            $initial = '—';
                            $trimName = trim($rawName);
                            if ($trimName !== '') {
                                if (function_exists('mb_substr')) {
                                    $initial = (string) mb_substr($trimName, 0, 1);
                                } else {
                                    $initial = substr($trimName, 0, 1);
                                }
                            }
                            $initial = escape($initial);
                            $hasFeedInfo = $feed !== '' && $linkId > 0;
                            $avatarLabel = trim($trimName) !== '' ? ('查看 ' . $trimName . ' 的订阅信息') : '查看该站点的订阅信息';
                            $avatarLabel = escape($avatarLabel);
                            $feedTip = escape(_t('订阅信息'));
                            $noFeedTip = escape(_t('无订阅信息'));
                        ?>
                            <li class="links-item" data-link-type="<?php echo $typeLabel; ?>">
                                <?php if ($hasFeedInfo): ?>
                                    <button
                                        class="links-avatar links-avatar-btn"
                                        type="button"
                                        data-feed-link-id="<?php echo $linkId; ?>"
                                        data-feed-link-name="<?php echo $name !== '' ? $name : '—'; ?>"
                                        data-feed-tip="<?php echo $feedTip; ?>"
                                        title="<?php echo $feedTip; ?>"
                                        aria-label="<?php echo $avatarLabel; ?>"
                                        aria-haspopup="dialog"
                                        aria-expanded="false"
                                    >
                                        <?php if ($avatar !== ''): ?>
                                            <img src="<?php echo $avatar; ?>" alt="" width="42" height="42" loading="lazy" decoding="async">
                                        <?php else: ?>
                                            <span><?php echo $initial; ?></span>
                                        <?php endif; ?>
                                    </button>
                                <?php else: ?>
                                    <div class="links-avatar is-no-feed" data-feed-tip="<?php echo $noFeedTip; ?>" title="<?php echo $noFeedTip; ?>" tabindex="0" aria-label="<?php echo $noFeedTip; ?>">
                                        <?php if ($avatar !== ''): ?>
                                            <img src="<?php echo $avatar; ?>" alt="" width="42" height="42" loading="lazy" decoding="async">
                                        <?php else: ?>
                                            <span><?php echo $initial; ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="links-body">
                                    <div class="links-name-row">
                                        <a class="links-name" href="<?php echo $url; ?>" target="_blank" rel="noreferrer"><?php echo $name !== '' ? $name : '—'; ?></a>
                                        <?php if ($feed !== ''): ?>
                                            <a
                                                class="links-feed is-health-pending"
                                                href="<?php echo $feed; ?>"
                                                target="_blank"
                                                rel="noreferrer"
                                                aria-label="订阅链接"
                                                title="订阅链接"
                                                data-feed-tip="订阅链接"
                                                data-link-health-id="<?php echo $linkId; ?>"
                                                data-health-state="pending"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-rss-icon lucide-rss links-feed-icon" aria-hidden="true">
                                                    <path class="links-feed-wave links-feed-wave-1" pathLength="1" d="M4 11a9 9 0 0 1 9 9"/>
                                                    <path class="links-feed-wave links-feed-wave-2" pathLength="1" d="M4 4a16 16 0 0 1 16 16"/>
                                                    <circle class="links-feed-dot" cx="5" cy="19" r="1"/>
                                                </svg>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($desc !== ''): ?>
                                        <div class="links-desc"><?php echo $desc; ?></div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="links-feed-popover" data-links-feed-popover hidden aria-hidden="true" role="dialog" aria-modal="false" aria-label="<?php _e('订阅信息'); ?>">
                        <div class="links-feed-popover-head">
                            <p class="links-feed-popover-title" data-links-feed-popover-title><?php _e('订阅信息'); ?></p>
                            <button class="links-feed-popover-close" type="button" data-links-feed-popover-close aria-label="<?php _e('关闭订阅信息'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M18 6 6 18"></path>
                                    <path d="m6 6 12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="links-feed-popover-body" data-links-feed-popover-body></div>
                    </div>
                <?php else: ?>
                    <p class="links-empty"><?php _e('暂无友链'); ?></p>
                <?php endif; ?>

                <div class="links-apply" id="v3a-apply" aria-label="<?php _e('申请友链'); ?>">
                    <div class="links-apply-layout">
                        <div class="links-apply-main">
                            <?php if ($noticeMessage !== ''): ?>
                                <div class="links-notice <?php echo ($noticeType === 'success') ? 'is-success' : 'is-error'; ?>">
                                    <?php echo escape($noticeMessage); ?>
                                </div>
                            <?php endif; ?>

                            <section class="links-apply-main-inner" aria-label="<?php _e('友链申请'); ?>">
                                <article class="links-step links-step-check" aria-label="<?php _e('确认前置条件'); ?>">
                                    <div class="links-panel-head">
                                        <span class="links-panel-num" aria-hidden="true">1</span>
                                        <h2 class="links-panel-title"><?php _e('确认前置条件'); ?></h2>
                                    </div>
                                    <div class="links-checklist-wrap">
                                        <label class="links-check">
                                            <input class="links-check-input" type="checkbox">
                                            <span class="links-check-text"><?php _e('类型为「个人博客」，已运营时间不少于 60 天'); ?></span>
                                        </label>
                                        <label class="links-check">
                                            <input class="links-check-input" type="checkbox">
                                            <span class="links-check-text"><?php _e('现有文章数量 >= 5 篇，且原创文章占比 >= 90%'); ?></span>
                                        </label>
                                        <label class="links-check">
                                            <input class="links-check-input" type="checkbox">
                                            <span class="links-check-text"><?php _e('网站访问流畅、内容合法合规、无过多商业广告'); ?></span>
                                        </label>
                                        <label class="links-check">
                                            <input class="links-check-input" type="checkbox">
                                            <span class="links-check-text"><?php _e('全站支持 HTTPS 访问'); ?></span>
                                        </label>
                                        <label class="links-check">
                                            <input class="links-check-input" type="checkbox">
                                            <span class="links-check-text"><?php _e('已将本站添加为贵站的友情链接'); ?></span>
                                        </label>
                                    </div>
                                    <div class="links-step-actions">
                                        <button class="links-btn links-confirm-btn links-primary-btn" type="button" data-links-confirm><?php _e('确定'); ?></button>
                                    </div>
                                </article>

                                <section class="links-step links-step-form" aria-label="<?php _e('填写站点信息'); ?>" hidden aria-hidden="true">
                                    <div class="links-panel-head">
                                        <span class="links-panel-num" aria-hidden="true">2</span>
                                        <h2 class="links-panel-title"><?php _e('填写站点信息'); ?></h2>
                                    </div>

                                    <form class="links-apply-form" method="post" action="<?php $this->permalink(); ?>#v3a-apply" autocomplete="on">
                                        <input type="hidden" name="v3a_do" value="apply">
                                        <input type="hidden" name="v3a_hp" value="">
                                        <input type="hidden" name="_" value="<?php echo escape((string) $security->getToken($csrfRef)); ?>">
                                        <input type="hidden" name="type" value="<?php echo escape((string) ($applySettings['defaultType'] ?? 'friend')); ?>">

                                        <div class="links-form-grid">
                                            <label class="links-form-item" for="links-url">
                                                <span class="links-form-label required"><?php _e('站点地址'); ?></span>
                                                <input type="url" id="links-url" name="url" class="text" required maxlength="255" placeholder="https://your.website">
                                            </label>
                                            <label class="links-form-item" for="links-name">
                                                <span class="links-form-label required"><?php _e('站点名称'); ?></span>
                                                <input type="text" id="links-name" name="name" class="text" required maxlength="100" placeholder="<?php _e('站点名称'); ?>">
                                            </label>
                                            <label class="links-form-item" for="links-avatar">
                                                <span class="links-form-label<?php echo !empty($applySettings['required']['avatar']) ? ' required' : ''; ?>"><?php _e('站点图标'); ?></span>
                                                <input type="url" id="links-avatar" name="avatar" class="text" maxlength="500" placeholder="https://your.website/favicon.svg" <?php echo !empty($applySettings['required']['avatar']) ? 'required' : ''; ?>>
                                            </label>
                                            <label class="links-form-item" for="links-feed">
                                                <span class="links-form-label"><?php _e('订阅地址'); ?></span>
                                                <input type="url" id="links-feed" name="feed" class="text" maxlength="500" placeholder="https://your.website/feed.xml">
                                            </label>
                                            <label class="links-form-item" for="links-description">
                                                <span class="links-form-label<?php echo !empty($applySettings['required']['description']) ? ' required' : ''; ?>"><?php _e('站点描述'); ?></span>
                                                <input type="text" id="links-description" name="description" class="text" maxlength="200" placeholder="<?php _e('一句话介绍您的站点'); ?>" <?php echo !empty($applySettings['required']['description']) ? 'required' : ''; ?>>
                                            </label>
                                            <label class="links-form-item" for="links-email">
                                                <span class="links-form-label required"><?php _e('联系邮箱'); ?></span>
                                                <input type="email" id="links-email" name="email" class="text" maxlength="190" placeholder="<?php _e('接收申请结果及重要通知'); ?>" required>
                                            </label>

                                            <?php if (!empty($applySettings['required']['message'])): ?>
                                                <label class="links-form-item links-form-item-wide" for="links-message">
                                                    <span class="links-form-label required"><?php _e('留言'); ?></span>
                                                    <input type="text" id="links-message" name="message" class="text" maxlength="500" placeholder="<?php _e('可简要补充说明'); ?>" required>
                                                </label>
                                            <?php else: ?>
                                                <input type="hidden" name="message" value="">
                                            <?php endif; ?>
                                        </div>

                                        <p class="links-form-note"><?php _e('* 为必填项'); ?></p>

                                        <div class="links-step-actions">
                                            <button type="submit" class="links-btn links-submit-btn links-primary-btn"><?php _e('提交申请'); ?></button>
                                        </div>
                                    </form>
                                </section>
                            </section>
                        </div>

                        <aside class="links-aside" aria-label="<?php _e('本站友链信息'); ?>">
                            <h2 class="links-aside-title"><?php _e('本站友链信息'); ?></h2>

                            <p class="links-aside-step">
                                <span class="links-aside-step-num" aria-hidden="true">1</span>
                                <span class="links-aside-step-text"><?php _e('请先于贵站友链列表中添加本站：'); ?></span>
                            </p>

                            <div class="links-aside-site">
                                <button
                                    type="button"
                                    class="links-aside-favicon links-copy-trigger<?php echo $siteCopyAvatar === '' ? ' is-disabled' : ''; ?>"
                                    data-copy-text="<?php echo $siteCopyAvatar; ?>"
                                    data-copy-tip="点击复制头像链接"
                                    aria-label="<?php _e('复制头像链接'); ?>"
                                    <?php echo $siteCopyAvatar === '' ? 'disabled aria-disabled="true"' : ''; ?>
                                >
                                    <?php if ($siteFavicon !== ''): ?>
                                        <img src="<?php echo $siteFavicon; ?>" alt="" width="46" height="46" loading="lazy" decoding="async">
                                    <?php else: ?>
                                        <span><?php echo $siteInitial; ?></span>
                                    <?php endif; ?>
                                </button>
                                <div class="links-aside-site-main">
                                    <p class="links-aside-site-title">
                                        <button
                                            type="button"
                                            class="links-copy-trigger links-copy-text"
                                            data-copy-text="<?php echo $siteCopyName; ?>"
                                            data-copy-tip="点击复制信息"
                                        ><?php echo $siteTitle; ?></button>
                                        <span class="links-aside-site-actions" aria-label="<?php _e('复制信息'); ?>">
                                            <button
                                                type="button"
                                                class="links-copy-trigger links-aside-icon-btn<?php echo $siteCopyUrl === '' ? ' is-disabled' : ''; ?>"
                                                data-copy-text="<?php echo $siteCopyUrl; ?>"
                                                data-copy-tip="点击复制信息"
                                                aria-label="<?php _e('复制站点链接'); ?>"
                                                <?php echo $siteCopyUrl === '' ? 'disabled aria-disabled="true"' : ''; ?>
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                                </svg>
                                            </button>
                                            <button
                                                type="button"
                                                class="links-copy-trigger links-aside-icon-btn<?php echo $siteCopyFeed === '' ? ' is-disabled' : ''; ?>"
                                                data-copy-text="<?php echo $siteCopyFeed; ?>"
                                                data-copy-tip="点击复制信息"
                                                aria-label="<?php _e('复制 RSS 链接'); ?>"
                                                <?php echo $siteCopyFeed === '' ? 'disabled aria-disabled="true"' : ''; ?>
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M4 11a9 9 0 0 1 9 9"></path>
                                                    <path d="M4 4a16 16 0 0 1 16 16"></path>
                                                    <circle cx="5" cy="19" r="1"></circle>
                                                </svg>
                                            </button>
                                        </span>
                                    </p>
                                    <button
                                        type="button"
                                        class="links-copy-trigger links-copy-text links-aside-site-desc"
                                        data-copy-text="<?php echo $siteCopyDesc; ?>"
                                        data-copy-tip="点击复制信息"
                                    ><?php echo $siteDesc; ?></button>
                                </div>
                            </div>

                            <p class="links-aside-step">
                                <span class="links-aside-step-num" aria-hidden="true">2</span>
                                <span class="links-aside-step-text"><?php _e('完成后左侧进行流程申请友链'); ?></span>
                            </p>
                        </aside>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </article>
</main>

<script>
    (function () {
        var nodes = document.querySelectorAll('.links-copy-trigger[data-copy-text]');
        if (!nodes || nodes.length === 0) {
            return;
        }

        function fallbackCopy(text) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', 'readonly');
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            ta.style.pointerEvents = 'none';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            var ok = false;
            try {
                ok = document.execCommand('copy');
            } catch (e) {
                ok = false;
            }
            document.body.removeChild(ta);
            return ok;
        }

        function copyText(text) {
            if (navigator.clipboard && navigator.clipboard.writeText) {
                return navigator.clipboard.writeText(text).then(function () {
                    return true;
                }).catch(function () {
                    return fallbackCopy(text);
                });
            }
            return Promise.resolve(fallbackCopy(text));
        }

        function setTip(el, copied) {
            var defaultTip = el.getAttribute('data-copy-tip-default') || '点击复制信息';
            el.setAttribute('data-copy-tip', copied ? '复制成功' : defaultTip);
            if (copied) {
                el.classList.add('is-copied');
            } else {
                el.classList.remove('is-copied');
            }
        }

        Array.prototype.slice.call(nodes).forEach(function (el) {
            var resetTimer = 0;
            var defaultTip = el.getAttribute('data-copy-tip') || '点击复制信息';
            el.setAttribute('data-copy-tip-default', defaultTip);

            el.addEventListener('click', function (e) {
                if (e && e.preventDefault) {
                    e.preventDefault();
                }
                if (el.disabled || el.getAttribute('aria-disabled') === 'true') {
                    return;
                }

                var text = String(el.getAttribute('data-copy-text') || '').trim();
                if (text === '') {
                    return;
                }

                copyText(text).then(function (ok) {
                    if (!ok) {
                        return;
                    }

                    setTip(el, true);
                    if (resetTimer) {
                        clearTimeout(resetTimer);
                    }
                    resetTimer = window.setTimeout(function () {
                        setTip(el, false);
                    }, 1500);
                });
            });
        });
    })();
</script>

<script>
    (function () {
        var page = document.querySelector('.links-page');
        if (!page || !window.fetch) {
            return;
        }

        var avatarButtons = Array.prototype.slice.call(
            page.querySelectorAll('.links-avatar-btn[data-feed-link-id]')
        );
        var popover = page.querySelector('[data-links-feed-popover]');
        if (!popover || avatarButtons.length === 0) {
            return;
        }

        var popoverTitle = popover.querySelector('[data-links-feed-popover-title]');
        var popoverBody = popover.querySelector('[data-links-feed-popover-body]');
        var popoverClose = popover.querySelector('[data-links-feed-popover-close]');
        if (!popoverTitle || !popoverBody) {
            return;
        }

        var activeButton = null;
        var cache = Object.create(null);
        var requestToken = 0;

        function setExpanded(btn, expanded) {
            if (!btn || !btn.setAttribute) {
                return;
            }
            btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
        }

        function closePopover() {
            if (activeButton) {
                setExpanded(activeButton, false);
            }
            activeButton = null;
            popover.hidden = true;
            popover.setAttribute('aria-hidden', 'true');
        }

        function formatRelativeTime(seconds) {
            var ts = parseInt(String(seconds || ''), 10);
            if (!isFinite(ts) || ts <= 0) {
                return '未知时间';
            }

            var now = Math.floor(Date.now() / 1000);
            var diff = now - ts;
            if (!isFinite(diff) || diff < 0) {
                diff = 0;
            }

            if (diff < 5) {
                return '刚刚';
            }
            if (diff < 60) {
                return diff + '秒前';
            }

            var minutes = Math.floor(diff / 60);
            if (minutes < 60) {
                return minutes + '分钟前';
            }

            var hours = Math.floor(minutes / 60);
            if (hours < 24) {
                return hours + '小时前';
            }

            var days = Math.floor(hours / 24);
            if (days < 30) {
                return days + '天前';
            }

            var months = Math.floor(days / 30);
            if (months < 12) {
                return months + '月前';
            }

            var years = Math.floor(days / 365);
            return years + '年前';
        }

        function renderMessage(msg, title) {
            popoverTitle.textContent = title || '订阅信息';
            popoverBody.innerHTML = '';
            var p = document.createElement('p');
            p.className = 'links-feed-empty';
            p.textContent = msg || '暂无订阅信息';
            popoverBody.appendChild(p);
        }

        function renderLoading(title) {
            popoverTitle.textContent = title || '订阅信息';
            popoverBody.innerHTML = '';
            var p = document.createElement('p');
            p.className = 'links-feed-loading';
            p.textContent = '正在加载订阅信息...';
            popoverBody.appendChild(p);
        }

        function renderFeed(data, fallbackTitle) {
            var siteName = data && data.title ? String(data.title) : '';
            var heading = siteName ? (siteName + ' · 订阅信息') : (fallbackTitle || '订阅信息');
            popoverTitle.textContent = heading;
            popoverBody.innerHTML = '';

            var items = data && Array.isArray(data.items) ? data.items : [];
            if (items.length === 0) {
                renderMessage('暂无可展示文章', heading);
                return;
            }

            var list = document.createElement('ul');
            list.className = 'links-feed-list';

            items.forEach(function (item) {
                if (!item) {
                    return;
                }

                var row = document.createElement('li');
                row.className = 'links-feed-entry';

                var titleText = item.title ? String(item.title) : '未命名文章';
                var itemUrl = item.url ? String(item.url) : '';

                if (itemUrl) {
                    var a = document.createElement('a');
                    a.className = 'links-feed-entry-title';
                    a.href = itemUrl;
                    a.target = '_blank';
                    a.rel = 'noreferrer';
                    a.textContent = titleText;
                    row.appendChild(a);
                } else {
                    var span = document.createElement('span');
                    span.className = 'links-feed-entry-title';
                    span.textContent = titleText;
                    row.appendChild(span);
                }

                var time = document.createElement('span');
                time.className = 'links-feed-entry-time';
                time.textContent = formatRelativeTime(item.publishedAt);
                row.appendChild(time);

                var summary = document.createElement('p');
                summary.className = 'links-feed-entry-summary';
                summary.textContent = item.summary ? String(item.summary) : '暂无摘要';
                row.appendChild(summary);

                list.appendChild(row);
            });

            if (list.children.length === 0) {
                renderMessage('暂无可展示文章', heading);
                return;
            }

            popoverBody.appendChild(list);
        }

        function positionPopover() {
            if (!activeButton || popover.hidden) {
                return;
            }

            var viewportW = window.innerWidth || document.documentElement.clientWidth || 0;
            var viewportH = window.innerHeight || document.documentElement.clientHeight || 0;
            var gap = 8;
            var minBelow = 180;
            var rect = activeButton.getBoundingClientRect();
            var below = viewportH - rect.bottom - gap;

            if (below < minBelow && window.scrollBy) {
                var doc = document.documentElement;
                var maxScroll = Math.max(0, (doc.scrollHeight || 0) - ((window.scrollY || 0) + viewportH));
                var delta = Math.min(maxScroll, minBelow - below);
                if (delta > 0) {
                    window.scrollBy(0, delta);
                    rect = activeButton.getBoundingClientRect();
                }
            }

            var top = rect.bottom + gap;
            var availableHeight = viewportH - top - gap;
            if (availableHeight < 120) {
                top = Math.max(gap, viewportH - 140);
                availableHeight = viewportH - top - gap;
            }
            if (availableHeight < 72) {
                availableHeight = 72;
            }

            var headHeight = 44;
            var head = popover.querySelector('.links-feed-popover-head');
            if (head) {
                headHeight = Math.max(30, head.getBoundingClientRect().height || 44);
            }
            var bodyHeight = Math.max(72, availableHeight - headHeight);
            popoverBody.style.maxHeight = Math.round(bodyHeight) + 'px';

            var popRect = popover.getBoundingClientRect();
            var width = popRect.width;
            var left = rect.left + (rect.width / 2) - (width / 2);
            if (left < gap) {
                left = gap;
            }
            if (left + width > viewportW - gap) {
                left = viewportW - width - gap;
            }

            popover.style.left = Math.round(left) + 'px';
            popover.style.top = Math.round(top) + 'px';
        }

        function buildRequestUrl(linkId) {
            var base = window.location.href.split('#')[0];
            var join = base.indexOf('?') === -1 ? '?' : '&';
            return base + join + 'links_feed_preview=1&link_id=' + encodeURIComponent(linkId);
        }

        function loadFeed(button) {
            var linkId = String(button.getAttribute('data-feed-link-id') || '').trim();
            var fallbackName = String(button.getAttribute('data-feed-link-name') || '').trim();
            var fallbackTitle = fallbackName ? (fallbackName + ' · 订阅信息') : '订阅信息';
            if (linkId === '') {
                renderMessage('订阅信息不存在', fallbackTitle);
                return;
            }

            if (cache[linkId]) {
                renderFeed(cache[linkId], fallbackTitle);
                positionPopover();
                return;
            }

            renderLoading(fallbackTitle);
            var token = ++requestToken;

            window.fetch(buildRequestUrl(linkId), {
                method: 'GET',
                cache: 'no-store',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) {
                    if (!response || !response.ok) {
                        throw new Error('请求失败');
                    }
                    return response.json();
                })
                .then(function (data) {
                    if (token !== requestToken) {
                        return;
                    }
                    if (!data || !data.ok) {
                        throw new Error(data && data.message ? String(data.message) : '读取失败');
                    }

                    cache[linkId] = data;
                    if (activeButton === button && !popover.hidden) {
                        renderFeed(data, fallbackTitle);
                        positionPopover();
                    }
                })
                .catch(function (error) {
                    if (token !== requestToken) {
                        return;
                    }
                    if (activeButton === button && !popover.hidden) {
                        var msg = error && error.message ? String(error.message) : '暂时无法加载订阅信息';
                        renderMessage(msg, fallbackTitle);
                        positionPopover();
                    }
                });
        }

        function openPopover(button) {
            if (activeButton === button && !popover.hidden) {
                closePopover();
                return;
            }

            if (activeButton && activeButton !== button) {
                setExpanded(activeButton, false);
            }

            activeButton = button;
            setExpanded(button, true);
            popover.hidden = false;
            popover.setAttribute('aria-hidden', 'false');
            positionPopover();
            loadFeed(button);
        }

        avatarButtons.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                if (e && e.preventDefault) {
                    e.preventDefault();
                }
                openPopover(btn);
            });
        });

        if (popoverClose) {
            popoverClose.addEventListener('click', function (e) {
                if (e && e.preventDefault) {
                    e.preventDefault();
                }
                closePopover();
            });
        }

        document.addEventListener('click', function (e) {
            if (!activeButton || popover.hidden) {
                return;
            }
            var target = e && e.target ? e.target : null;
            if (!target) {
                closePopover();
                return;
            }
            if (popover.contains(target) || activeButton.contains(target)) {
                return;
            }
            closePopover();
        });

        document.addEventListener('keydown', function (e) {
            var key = e && e.key ? String(e.key) : '';
            if (key === 'Escape' || key === 'Esc') {
                closePopover();
            }
        });

        window.addEventListener('resize', function () {
            if (!popover.hidden) {
                positionPopover();
            }
        });

        window.addEventListener('scroll', function () {
            if (!popover.hidden) {
                positionPopover();
            }
        }, true);
    })();
</script>

<script>
    (function () {
        var page = document.querySelector('.links-page');
        if (!page || !window.fetch) {
            return;
        }

        var targets = Array.prototype.slice.call(
            page.querySelectorAll('.links-feed[data-link-health-id]')
        );
        if (!targets || targets.length === 0) {
            return;
        }

        function buildUrl(linkId) {
            var base = window.location.href.split('#')[0];
            var join = base.indexOf('?') === -1 ? '?' : '&';
            return base + join + 'links_health_check=1&link_id=' + encodeURIComponent(linkId);
        }

        function setState(node, state) {
            var next = String(state || 'red').toLowerCase();
            if (next !== 'green' && next !== 'yellow' && next !== 'red' && next !== 'pending') {
                next = 'red';
            }

            node.classList.remove('is-health-pending', 'is-health-green', 'is-health-yellow', 'is-health-red');
            node.classList.add('is-health-' + next);
            node.setAttribute('data-health-state', next);
        }

        function fetchStatus(node) {
            var idText = String(node.getAttribute('data-link-health-id') || '').trim();
            var linkId = parseInt(idText, 10);
            if (!isFinite(linkId) || linkId <= 0) {
                setState(node, 'red');
                return Promise.resolve();
            }

            return window.fetch(buildUrl(linkId), {
                method: 'GET',
                cache: 'no-store',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) {
                    if (!response || !response.ok) {
                        throw new Error('request_failed');
                    }
                    return response.json();
                })
                .then(function (payload) {
                    if (!payload || payload.ok !== true) {
                        throw new Error('invalid_payload');
                    }
                    setState(node, payload.state || 'red');
                })
                .catch(function () {
                    setState(node, 'red');
                });
        }

        targets.forEach(function (node) {
            setState(node, 'pending');
        });

        var index = 0;
        function runQueue() {
            if (index >= targets.length) {
                return;
            }
            var current = targets[index];
            index += 1;

            fetchStatus(current).then(function () {
                window.setTimeout(runQueue, 280);
            });
        }

        // Delay first check to avoid competing with initial render.
        window.setTimeout(runQueue, 450);
    })();
</script>

<?php $this->need('footer.php'); ?>
