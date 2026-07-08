<?php
/**
 * 复古物语指令图鉴（/cnb）
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

$cnbData = [
    'meta' => [
        'gameVersion' => 'fallback',
        'generatedAt' => gmdate('c'),
        'commandCount' => 3
    ],
    'commands' => [
        ['syntax' => '.chb', 'summary' => '打开游戏内命令手册', 'summaryZh' => '打开游戏内命令手册', 'scope' => 'client', 'role' => 'local', 'category' => '帮助', 'examples' => ['.chb']],
        ['syntax' => '/help [commandname] [subcommand] [subsubcommand]', 'summary' => '显示服务器命令帮助', 'summaryZh' => '显示服务器命令帮助', 'scope' => 'server', 'role' => 'player', 'category' => '帮助', 'examples' => ['/help /land']],
        ['syntax' => '.help [commandname] [subcommand] [subsubcommand]', 'summary' => '显示客户端命令帮助', 'summaryZh' => '显示客户端命令帮助', 'scope' => 'client', 'role' => 'local', 'category' => '帮助', 'examples' => ['.help .cam']]
    ]
];

$cnbDataFiles = [
    dirname(__DIR__) . '/cache/cnb_commands_latest.json',
    dirname(__DIR__) . '/cache/cnb_commands.json'
];

foreach ($cnbDataFiles as $cnbDataFile) {
    if (!is_file($cnbDataFile)) {
        continue;
    }
    $raw = @file_get_contents($cnbDataFile);
    if (!is_string($raw) || $raw === '') {
        continue;
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded) || !isset($decoded['commands']) || !is_array($decoded['commands'])) {
        continue;
    }
    $cnbData = $decoded;
    break;
}

$cnbEssentialCommands = [
    ['syntax' => '.chb', 'summary' => '打开游戏内命令手册', 'summaryZh' => '打开游戏内命令手册', 'scope' => 'client', 'role' => 'local', 'category' => '帮助', 'examples' => ['.chb']],
    ['syntax' => '/help [commandname] [subcommand] [subsubcommand]', 'summary' => '显示服务器命令帮助', 'summaryZh' => '显示服务器命令帮助', 'scope' => 'server', 'role' => 'player', 'category' => '帮助', 'examples' => ['/help /land']],
    ['syntax' => '.help [commandname] [subcommand] [subsubcommand]', 'summary' => '显示客户端命令帮助', 'summaryZh' => '显示客户端命令帮助', 'scope' => 'client', 'role' => 'local', 'category' => '帮助', 'examples' => ['.help .cam']]
];

$cnbLegacyRemovedCommands = [
    [
        'syntax' => '.moon',
        'summary' => 'Removed legacy client command',
        'summaryZh' => '旧版命令（已移除）：曾用于显示月相',
        'scope' => 'client',
        'role' => 'local',
        'category' => '旧版命令（已移除）',
        'legacyRemoved' => true,
        'removedSince' => '1.22.0-rc.7',
        'replacement' => '.chb',
        'additionalInfo' => '最新版反编译中未检出同名命令，建议在游戏内使用 .chb 查询当前替代写法。',
        'additionalInfoZh' => '最新版反编译中未检出同名命令，建议在游戏内使用 .chb 查询当前替代写法。',
        'examples' => ['.moon']
    ],
    [
        'syntax' => '.serverinfo',
        'summary' => 'Removed legacy client command',
        'summaryZh' => '旧版命令（已移除）：曾用于显示服务器信息',
        'scope' => 'client',
        'role' => 'local',
        'category' => '旧版命令（已移除）',
        'legacyRemoved' => true,
        'removedSince' => '1.22.0-rc.7',
        'replacement' => '/info',
        'additionalInfo' => '最新版反编译中未检出同名客户端命令；可尝试服务器端 /info，或在 .chb 内检索最新命令。',
        'additionalInfoZh' => '最新版反编译中未检出同名客户端命令；可尝试服务器端 /info，或在 .chb 内检索最新命令。',
        'examples' => ['.serverinfo']
    ],
    [
        'syntax' => '/pm <playername> <message>',
        'summary' => 'Removed legacy server command',
        'summaryZh' => '旧版命令（已移除）：曾用于私聊',
        'scope' => 'server',
        'role' => 'player',
        'category' => '旧版命令（已移除）',
        'legacyRemoved' => true,
        'removedSince' => '1.22.0-rc.7',
        'replacement' => '/group',
        'additionalInfo' => '最新版反编译中未检出 /pm；建议使用 /group 体系或 .chb 查询当前私聊方案。',
        'additionalInfoZh' => '最新版反编译中未检出 /pm；建议使用 /group 体系或 .chb 查询当前私聊方案。',
        'examples' => ['/pm playername hello']
    ]
];
if (!isset($cnbData['commands']) || !is_array($cnbData['commands'])) {
    $cnbData['commands'] = [];
}
$cnbExistingSyntax = [];
foreach ($cnbData['commands'] as $entry) {
    if (!is_array($entry)) {
        continue;
    }
    $syntax = strtolower(trim((string)($entry['syntax'] ?? '')));
    if ($syntax !== '') {
        $cnbExistingSyntax[$syntax] = true;
    }
}
foreach ($cnbEssentialCommands as $entry) {
    $syntax = strtolower(trim((string)($entry['syntax'] ?? '')));
    if ($syntax === '' || isset($cnbExistingSyntax[$syntax])) {
        continue;
    }
    $cnbData['commands'][] = $entry;
    $cnbExistingSyntax[$syntax] = true;
}
foreach ($cnbLegacyRemovedCommands as $entry) {
    $syntax = strtolower(trim((string)($entry['syntax'] ?? '')));
    if ($syntax === '' || isset($cnbExistingSyntax[$syntax])) {
        continue;
    }
    $cnbData['commands'][] = $entry;
    $cnbExistingSyntax[$syntax] = true;
}
if (!isset($cnbData['meta']) || !is_array($cnbData['meta'])) {
    $cnbData['meta'] = [];
}
$cnbData['meta']['commandCount'] = count($cnbData['commands']);

$cnbCommandsJson = json_encode($cnbData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (!is_string($cnbCommandsJson)) {
    $cnbCommandsJson = '{"meta":{"gameVersion":"fallback","commandCount":0},"commands":[]}';
}

$cnbMeta = (isset($cnbData['meta']) && is_array($cnbData['meta'])) ? $cnbData['meta'] : [];
$cnbGameVersion = htmlspecialchars((string)($cnbMeta['gameVersion'] ?? '未知版本'), ENT_QUOTES, 'UTF-8');
$cnbGeneratedAt = htmlspecialchars((string)($cnbMeta['generatedAt'] ?? ''), ENT_QUOTES, 'UTF-8');
$cnbCommandCount = (int)($cnbMeta['commandCount'] ?? (is_array($cnbData['commands']) ? count($cnbData['commands']) : 0));
$cnbGeneratedText = $cnbGeneratedAt !== '' ? $cnbGeneratedAt : '未知时间';

$this->need('header.php');
?>

<main class="main" role="main">
    <article class="page cnb-page" data-cnb-root aria-label="复古物语指令图鉴">
        <header class="posts-page-head cnb-page-head">
            <h1 class="posts-page-title"><?php $this->title(); ?></h1>
            <p class="cnb-subtitle">复古物语中文社区制作：指令图鉴 · <a href="https://vintagestory.top/" target="_blank" rel="noopener noreferrer">复古物语社区</a></p>
        </header>

        <div class="article-content">
            <?php echoArchiveContent($this); ?>
        </div>

        <section class="links-step cnb-note">
            <p>当前数据来自本机游戏目录自动提取：版本 <code><?php echo $cnbGameVersion; ?></code>，命令 <code><?php echo (string)$cnbCommandCount; ?></code> 条。</p>
            <p>生成时间：<code><?php echo $cnbGeneratedText; ?></code>。如命令行为与页面不一致，请在游戏内输入 <code>.chb</code> 查看实时手册。</p>
            <p>服务器命令以 <code>/</code> 开头，客户端命令以 <code>.</code> 开头；不要输入 <code>[ ]</code>；修改世界/服务器配置后通常需要重启。</p>
        </section>

        <section class="links-step cnb-workbench" aria-label="命令编辑器">
            <div class="cnb-workbench-head">
                <p class="cnb-status is-idle" data-cnb-status>等待输入命令</p>
                <span class="cnb-status-note" data-cnb-status-note>输入 / 或 . 开始；Tab 补全；Enter 采用建议。</span>
            </div>
            <div class="cnb-input-row">
                <input type="text" class="cnb-input" data-cnb-input spellcheck="false" autocomplete="off" placeholder="/land claim grant playername all">
                <button type="button" class="cnb-copy" data-cnb-copy>复制命令</button>
            </div>
            <ul class="cnb-suggest" data-cnb-suggest hidden></ul>
            <div class="cnb-hints" data-cnb-hints></div>
        </section>

        <section class="cnb-filters" aria-label="筛选">
            <div class="cnb-filter-row">
                <span>范围</span>
                <button class="is-active" type="button" data-cnb-filter="scope" data-cnb-value="all">全部</button>
                <button type="button" data-cnb-filter="scope" data-cnb-value="server">服务器</button>
                <button type="button" data-cnb-filter="scope" data-cnb-value="client">客户端</button>
            </div>
            <div class="cnb-filter-row">
                <span>权限</span>
                <button class="is-active" type="button" data-cnb-filter="role" data-cnb-value="all">全部</button>
                <button type="button" data-cnb-filter="role" data-cnb-value="player">玩家</button>
                <button type="button" data-cnb-filter="role" data-cnb-value="admin">管理员</button>
                <button type="button" data-cnb-filter="role" data-cnb-value="local">本地客户端</button>
            </div>
            <p class="cnb-count" data-cnb-count></p>
        </section>

        <section class="cnb-grid-wrap">
            <div class="cnb-grid" data-cnb-grid></div>
            <p class="landing-study-empty" data-cnb-empty hidden>未找到匹配命令</p>
        </section>
    </article>
</main>

<div class="study-word-modal cnb-modal" data-cnb-modal hidden aria-hidden="true" role="dialog" aria-modal="true" aria-label="命令详情">
    <button class="study-word-modal-backdrop" type="button" data-cnb-modal-close aria-label="关闭"></button>
    <div class="study-word-modal-panel">
        <div class="study-word-modal-head">
            <h3 class="study-word-modal-title" data-cnb-modal-title>命令详情</h3>
            <button class="study-word-modal-close" type="button" data-cnb-modal-close aria-label="关闭">×</button>
        </div>
        <div class="study-word-modal-body" data-cnb-modal-body></div>
    </div>
</div>

<style>
.cnb-page { padding-bottom: 2rem; }
.cnb-subtitle { margin: .3rem 0 0; color: var(--muted-day); font-family: var(--font-ui); font-size: .84rem; }
.cnb-subtitle a {
    color: inherit;
    text-decoration: underline;
    text-decoration-thickness: 1px;
    text-underline-offset: .12em;
}
.cnb-subtitle a:hover,
.cnb-subtitle a:focus-visible {
    text-decoration-thickness: 2px;
}
.cnb-note {
    margin-top: .9rem;
    border-style: solid;
    border-width: 1px;
    border-radius: 4px;
    border-color: rgba(180, 40, 40, .38);
    background: rgba(180, 40, 40, .08);
}
.cnb-note p { margin: .3rem 0 0; font-family: var(--font-ui); font-size: .8rem; line-height: 1.5; color: rgba(70, 18, 18, .96); }
.cnb-note p:first-child { margin-top: 0; }
.cnb-note code {
    display: inline-flex;
    align-items: center;
    border: 1px solid rgba(180, 40, 40, .35);
    border-radius: 3px;
    background: rgba(255, 255, 255, .72);
    color: #8a1f1f;
    padding: .03rem .32rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    font-size: .74rem;
    line-height: 1.2;
}
.cnb-workbench {
    --cnb-stick-top: .7rem;
    --cnb-workbench-max-height: calc(100vh - var(--cnb-stick-top) - .7rem);
    --cnb-suggest-max-height: 12rem;
    margin-top: .8rem;
    position: sticky;
    top: var(--cnb-stick-top);
    z-index: 50;
    border-style: solid;
    border-width: 1px;
    border-radius: 4px;
    background: var(--day-bg);
    max-height: var(--cnb-workbench-max-height);
    overflow: auto;
    overscroll-behavior: contain;
    scrollbar-gutter: stable both-edges;
    scroll-margin-top: calc(var(--cnb-stick-top) + .35rem);
}
.cnb-workbench[data-cnb-sticky-disabled="1"] {
    position: relative;
    top: auto;
    max-height: none;
    overflow: visible;
}
.cnb-workbench-head { display: flex; align-items: center; justify-content: space-between; gap: .6rem; margin-bottom: .5rem; }
.cnb-status { margin: 0; display: inline-flex; align-items: center; padding: .08rem .45rem; border: 1px dashed var(--line-day); border-radius: 999px; font-family: var(--font-ui); font-size: .74rem; }
.cnb-status.is-success { border-color: rgba(31,132,90,.5); background: rgba(31,132,90,.12); color: #0f5132; }
.cnb-status.is-warning { border-color: rgba(189,130,30,.54); background: rgba(189,130,30,.13); color: #7a4a00; }
.cnb-status.is-error { border-color: rgba(180,40,40,.5); background: rgba(180,40,40,.14); color: #8a1f1f; }
.cnb-status-note { color: var(--muted-day); font-family: var(--font-ui); font-size: .74rem; }
.cnb-input-row { display: grid; grid-template-columns: minmax(0,1fr) auto; gap: .48rem; }
.cnb-input { width: 100%; border: 2px solid var(--day-black); border-radius: 4px; background: transparent; color: inherit; min-height: 2.1rem; padding: .52rem .58rem; font-family: ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace; font-size: .84rem; }
.cnb-copy { border: 2px solid var(--day-black); border-radius: 4px; background: transparent; color: inherit; padding: .45rem .65rem; font-family: var(--font-ui); font-size: .76rem; cursor: pointer; }
.cnb-copy:hover { background: rgba(17,17,17,.05); }
.cnb-suggest { list-style: none; margin: .52rem 0 0; padding: 0; border: 1px solid var(--line-day); border-radius: 4px; background: var(--day-bg); max-height: var(--cnb-suggest-max-height); overflow: auto; }
.cnb-suggest li { margin: 0; border-bottom: 1px solid var(--line-day); }
.cnb-suggest li:last-child { border-bottom: 0; }
.cnb-suggest button { width: 100%; border: 0; background: transparent; color: inherit; text-align: left; padding: .42rem .55rem; cursor: pointer; display: flex; align-items: flex-start; justify-content: space-between; gap: .56rem; }
.cnb-suggest li.is-active button, .cnb-suggest button:hover { background: rgba(17,17,17,.05); }
.cnb-suggest-main { display: block; flex: 1 1 auto; min-width: 0; font-family: ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace; font-size: .78rem; word-break: break-word; }
.cnb-suggest-meta { display: inline-flex; align-items: center; justify-content: center; margin-top: .03rem; padding: .05rem .4rem; border: 1px solid var(--line-day); border-radius: 999px; color: var(--muted-day); background: rgba(17,17,17,.02); font-family: var(--font-ui); font-size: .68rem; line-height: 1.2; white-space: nowrap; flex: 0 0 auto; }
.cnb-suggest-meta.is-root { border-color: rgba(37, 99, 235, .45); background: rgba(37, 99, 235, .12); color: #1e3a8a; }
.cnb-suggest-meta.is-literal { border-color: rgba(31, 132, 90, .45); background: rgba(31, 132, 90, .12); color: #0f5132; }
.cnb-suggest-meta.is-option { border-color: rgba(189, 130, 30, .5); background: rgba(189, 130, 30, .14); color: #7a4a00; }
.cnb-suggest-meta.is-error { border-color: rgba(180, 40, 40, .5); background: rgba(180, 40, 40, .16); color: #8a1f1f; }
.cnb-suggest-meta.is-info { border-color: rgba(17, 17, 17, .24); background: rgba(17,17,17,.06); color: inherit; }
.cnb-hints { margin-top: .5rem; min-height: 1.2rem; display: flex; flex-wrap: wrap; gap: .34rem; }
.cnb-hints .cnb-hint-chip { display: inline-flex; align-items: center; padding: .07rem .42rem; border: 1px dashed var(--line-day); border-radius: 999px; background: rgba(17,17,17,.02); font-family: var(--font-ui); font-size: .72rem; line-height: 1.2; }
.cnb-hints .cnb-hint-chip.is-success { border-color: rgba(31,132,90,.45); background: rgba(31,132,90,.12); color: #0f5132; }
.cnb-hints .cnb-hint-chip.is-warning { border-color: rgba(189,130,30,.5); background: rgba(189,130,30,.14); color: #7a4a00; }
.cnb-hints .cnb-hint-chip.is-error { border-color: rgba(180,40,40,.5); background: rgba(180,40,40,.16); color: #8a1f1f; }
.cnb-hints .cnb-hint-chip.is-info { border-color: rgba(17,17,17,.24); background: rgba(17,17,17,.06); color: inherit; }
.cnb-filters { margin-top: .8rem; }
.cnb-filter-row { display: flex; align-items: center; flex-wrap: wrap; gap: .4rem; margin-top: .32rem; }
.cnb-filter-row:first-child { margin-top: 0; }
.cnb-filter-row span { color: var(--muted-day); font-family: var(--font-ui); font-size: .76rem; min-width: 2.4rem; }
.cnb-filter-row button { border: 1px solid var(--line-day); border-radius: 999px; background: transparent; color: inherit; font-family: var(--font-ui); font-size: .73rem; padding: .16rem .5rem; cursor: pointer; }
.cnb-filter-row button.is-active { border-color: var(--day-black); }
.cnb-count { margin: .46rem 0 0; color: var(--muted-day); font-family: var(--font-ui); font-size: .76rem; }
.cnb-grid-wrap { margin-top: .7rem; }
.cnb-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(248px,1fr)); gap: .6rem; }
.cnb-card { border-radius: 4px; border: 1px solid var(--line-day); background: rgba(17,17,17,.02); padding: .62rem .66rem; display: flex; flex-direction: column; gap: .33rem; min-height: 10.4rem; cursor: pointer; transition: background-color .15s ease; }
.cnb-card:hover { background: rgba(17,17,17,.05); }
.cnb-card.is-legacy { border-color: rgba(180, 40, 40, .48); background: rgba(180, 40, 40, .08); }
.cnb-card.is-legacy:hover { background: rgba(180, 40, 40, .13); }
.cnb-card-head { display: flex; align-items: baseline; justify-content: space-between; gap: .4rem; }
.cnb-card-order { color: var(--muted-day); font-family: var(--font-ui); font-size: .72rem; }
.cnb-card-tags { display: inline-flex; align-items: center; flex-wrap: wrap; gap: .28rem; }
.cnb-card-tags .cnb-tag { display: inline-flex; padding: .05rem .35rem; border: 1px dashed var(--line-day); border-radius: 999px; font-family: var(--font-ui); font-size: .68rem; line-height: 1.2; }
.cnb-card-tags .cnb-tag.cnb-tag-scope-server { border-color: rgba(37, 99, 235, .45); background: rgba(37, 99, 235, .12); color: #1e3a8a; }
.cnb-card-tags .cnb-tag.cnb-tag-scope-client { border-color: rgba(107, 33, 168, .42); background: rgba(107, 33, 168, .12); color: #5b21b6; }
.cnb-card-tags .cnb-tag.cnb-tag-role-admin { border-color: rgba(180, 40, 40, .5); background: rgba(180, 40, 40, .14); color: #8a1f1f; }
.cnb-card-tags .cnb-tag.cnb-tag-role-player { border-color: rgba(31, 132, 90, .45); background: rgba(31,132,90,.12); color: #0f5132; }
.cnb-card-tags .cnb-tag.cnb-tag-role-local { border-color: rgba(189,130,30,.5); background: rgba(189,130,30,.14); color: #7a4a00; }
.cnb-card-tags .cnb-tag.cnb-tag-legacy { border-color: rgba(180, 40, 40, .5); background: rgba(180, 40, 40, .18); color: #8a1f1f; }
.cnb-card-syntax { margin: .32rem 0 0; font-family: ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace; font-size: .78rem; line-height: 1.4; word-break: break-word; }
.cnb-card-summary { margin: .08rem 0 0; color: var(--muted-day); font-family: var(--font-ui); font-size: .74rem; line-height: 1.42; }
.cnb-card-actions { margin-top: auto; display: inline-flex; flex-wrap: wrap; gap: .34rem; }
.cnb-card-actions button { border: 1px solid var(--line-day); border-radius: 3px; background: transparent; color: inherit; font-family: var(--font-ui); font-size: .71rem; padding: .14rem .42rem; cursor: pointer; }
.cnb-card-actions button:hover { background: rgba(17,17,17,.05); }
.cnb-modal .study-word-modal-body { white-space: normal; }
.cnb-modal-summary { margin: 0; font-family: var(--font-ui); font-size: .84rem; line-height: 1.5; }
.cnb-modal-summary-sub { margin: .14rem 0 0; color: var(--muted-day); font-family: var(--font-ui); font-size: .74rem; line-height: 1.5; }
.cnb-modal-meta { margin: .16rem 0 0; color: var(--muted-day); font-family: var(--font-ui); font-size: .72rem; line-height: 1.45; }
.cnb-modal-block { margin-top: .6rem; }
.cnb-modal-block-title { margin: 0; color: var(--muted-day); font-family: var(--font-ui); font-size: .75rem; }
.cnb-modal-code { margin: .24rem 0 0; display: block; border: 1px solid var(--line-day); border-radius: 4px; padding: .42rem .48rem; background: rgba(17,17,17,.02); font-family: ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace; font-size: .78rem; line-height: 1.4; }
.cnb-modal-args-wrap { margin-top: .3rem; overflow: auto; }
.cnb-modal-args { width: 100%; border-collapse: collapse; font-family: var(--font-ui); font-size: .72rem; line-height: 1.45; }
.cnb-modal-args th,
.cnb-modal-args td { border: 1px solid var(--line-day); padding: .26rem .34rem; text-align: left; vertical-align: top; }
.cnb-modal-args th { background: rgba(17,17,17,.05); font-weight: 600; }
.cnb-modal-actions { margin-top: .72rem; display: inline-flex; gap: .35rem; flex-wrap: wrap; }
.cnb-modal-actions button { border: 1px solid var(--line-day); border-radius: 4px; background: transparent; color: inherit; font-family: var(--font-ui); font-size: .74rem; padding: .24rem .54rem; cursor: pointer; }
.cnb-modal-actions button:hover { background: rgba(17,17,17,.05); }
.cnb-modal-legacy { margin-top: .4rem; border: 1px solid rgba(180,40,40,.45); border-radius: 4px; background: rgba(180,40,40,.12); color: #8a1f1f; font-family: var(--font-ui); font-size: .75rem; line-height: 1.45; padding: .32rem .4rem; }
@media (max-width: 840px) {
    .cnb-workbench-head { flex-direction: column; align-items: flex-start; }
    .cnb-input-row { grid-template-columns: 1fr; }
    .cnb-copy { width: 100%; }
    .cnb-workbench {
        --cnb-stick-top: .58rem;
    }
}
@media (max-width: 560px) { .cnb-grid { grid-template-columns: 1fr; } }
html:not(.theme-light) .cnb-subtitle,
html:not(.theme-light) .cnb-status-note,
html:not(.theme-light) .cnb-suggest-meta,
html:not(.theme-light) .cnb-filter-row span,
html:not(.theme-light) .cnb-count,
html:not(.theme-light) .cnb-card-order,
html:not(.theme-light) .cnb-card-summary,
html:not(.theme-light) .cnb-modal-block-title,
html:not(.theme-light) .cnb-modal-summary-sub,
html:not(.theme-light) .cnb-modal-meta { color: var(--muted-night); }
html:not(.theme-light) .cnb-workbench,
html:not(.theme-light) .cnb-note,
html:not(.theme-light) .cnb-suggest,
html:not(.theme-light) .cnb-suggest li,
html:not(.theme-light) .cnb-filter-row button,
html:not(.theme-light) .cnb-card,
html:not(.theme-light) .cnb-card-actions button,
html:not(.theme-light) .cnb-modal-code,
html:not(.theme-light) .cnb-modal-args th,
html:not(.theme-light) .cnb-modal-args td,
html:not(.theme-light) .cnb-modal-actions button,
html:not(.theme-light) .cnb-hints .cnb-hint-chip,
html:not(.theme-light) .cnb-card-tags .cnb-tag,
html:not(.theme-light) .cnb-status { border-color: var(--line-night); }
html:not(.theme-light) .cnb-input,
html:not(.theme-light) .cnb-copy { border-color: var(--night-white); }
html:not(.theme-light) .cnb-workbench,
html:not(.theme-light) .cnb-suggest { background: var(--night-bg); }
html:not(.theme-light) .cnb-note {
    border-color: rgba(239, 68, 68, .44);
    background: rgba(127, 29, 29, .34);
}
html:not(.theme-light) .cnb-note p { color: #fecaca; }
html:not(.theme-light) .cnb-note code {
    border-color: rgba(248, 113, 113, .42);
    background: rgba(30, 9, 9, .52);
    color: #fecaca;
}
html:not(.theme-light) .cnb-card,
html:not(.theme-light) .cnb-modal-code,
html:not(.theme-light) .cnb-modal-args th { background: rgba(255,255,255,.03); }
html:not(.theme-light) .cnb-copy:hover,
html:not(.theme-light) .cnb-suggest li.is-active button,
html:not(.theme-light) .cnb-suggest button:hover,
html:not(.theme-light) .cnb-card:hover,
html:not(.theme-light) .cnb-card-actions button:hover,
html:not(.theme-light) .cnb-modal-actions button:hover { background: rgba(255,255,255,.06); }
html:not(.theme-light) .cnb-status.is-success { color: #8ee4bd; background: rgba(31,132,90,.2); }
html:not(.theme-light) .cnb-status.is-warning { color: #f1cd80; background: rgba(189,130,30,.22); }
html:not(.theme-light) .cnb-status.is-error { color: #f2a3a3; background: rgba(180,40,40,.25); }
html:not(.theme-light) .cnb-suggest-meta.is-root { color: #93c5fd; background: rgba(37,99,235,.25); border-color: rgba(37,99,235,.45); }
html:not(.theme-light) .cnb-suggest-meta.is-literal { color: #8ee4bd; background: rgba(31,132,90,.22); border-color: rgba(31,132,90,.45); }
html:not(.theme-light) .cnb-suggest-meta.is-option { color: #f1cd80; background: rgba(189,130,30,.25); border-color: rgba(189,130,30,.5); }
html:not(.theme-light) .cnb-suggest-meta.is-error { color: #f2a3a3; background: rgba(180,40,40,.28); border-color: rgba(180,40,40,.5); }
html:not(.theme-light) .cnb-suggest-meta.is-info { color: var(--muted-night); background: rgba(255,255,255,.08); border-color: var(--line-night); }
html:not(.theme-light) .cnb-hints .cnb-hint-chip.is-success { color: #8ee4bd; background: rgba(31,132,90,.22); border-color: rgba(31,132,90,.45); }
html:not(.theme-light) .cnb-hints .cnb-hint-chip.is-warning { color: #f1cd80; background: rgba(189,130,30,.25); border-color: rgba(189,130,30,.5); }
html:not(.theme-light) .cnb-hints .cnb-hint-chip.is-error { color: #f2a3a3; background: rgba(180,40,40,.28); border-color: rgba(180,40,40,.5); }
html:not(.theme-light) .cnb-hints .cnb-hint-chip.is-info { color: var(--muted-night); background: rgba(255,255,255,.08); border-color: var(--line-night); }
html:not(.theme-light) .cnb-card-tags .cnb-tag.cnb-tag-scope-server { color: #93c5fd; background: rgba(37,99,235,.25); border-color: rgba(37,99,235,.45); }
html:not(.theme-light) .cnb-card-tags .cnb-tag.cnb-tag-scope-client { color: #c4b5fd; background: rgba(107,33,168,.24); border-color: rgba(107,33,168,.45); }
html:not(.theme-light) .cnb-card-tags .cnb-tag.cnb-tag-role-admin { color: #f2a3a3; background: rgba(180,40,40,.28); border-color: rgba(180,40,40,.5); }
html:not(.theme-light) .cnb-card-tags .cnb-tag.cnb-tag-role-player { color: #8ee4bd; background: rgba(31,132,90,.22); border-color: rgba(31,132,90,.45); }
html:not(.theme-light) .cnb-card-tags .cnb-tag.cnb-tag-role-local { color: #f1cd80; background: rgba(189,130,30,.25); border-color: rgba(189,130,30,.5); }
html:not(.theme-light) .cnb-card-tags .cnb-tag.cnb-tag-legacy { color: #f2a3a3; background: rgba(180,40,40,.28); border-color: rgba(180,40,40,.5); }
html:not(.theme-light) .cnb-card.is-legacy { border-color: rgba(248,113,113,.44); background: rgba(127,29,29,.24); }
html:not(.theme-light) .cnb-card.is-legacy:hover { background: rgba(127,29,29,.34); }
html:not(.theme-light) .cnb-modal-legacy { color: #fecaca; border-color: rgba(248,113,113,.42); background: rgba(127,29,29,.34); }
</style>

<script id="cnb-command-data" type="application/json"><?php echo $cnbCommandsJson; ?></script>
<script>
(function () {
    var controlKey = '__HansJackCnbControl';
    var old = window[controlKey];
    if (old && typeof old.teardown === 'function') {
        try { old.teardown(); } catch (e) {}
    }

    var root = document.querySelector('[data-cnb-root]');
    if (!root) {
        window[controlKey] = { teardown: function () {}, refresh: function () {} };
        return;
    }

    var dataNode = document.getElementById('cnb-command-data');
    var payload = { meta: {}, commands: [] };
    try {
        payload = JSON.parse(dataNode ? (dataNode.textContent || '{}') : '{}');
    } catch (e) {
        payload = { meta: {}, commands: [] };
    }
    var data = Array.isArray(payload) ? payload : (Array.isArray(payload.commands) ? payload.commands : []);
    var meta = payload && payload.meta && typeof payload.meta === 'object' ? payload.meta : {};

    var grid = root.querySelector('[data-cnb-grid]');
    var empty = root.querySelector('[data-cnb-empty]');
    var countNode = root.querySelector('[data-cnb-count]');
    var workbench = root.querySelector('.cnb-workbench');
    var input = root.querySelector('[data-cnb-input]');
    var copyBtn = root.querySelector('[data-cnb-copy]');
    var statusNode = root.querySelector('[data-cnb-status]');
    var statusNote = root.querySelector('[data-cnb-status-note]');
    var suggest = root.querySelector('[data-cnb-suggest]');
    var hints = root.querySelector('[data-cnb-hints]');
    var filterButtons = Array.prototype.slice.call(root.querySelectorAll('[data-cnb-filter][data-cnb-value]'));

    var modal = document.querySelector('[data-cnb-modal]');
    var modalTitle = modal ? modal.querySelector('[data-cnb-modal-title]') : null;
    var modalBody = modal ? modal.querySelector('[data-cnb-modal-body]') : null;
    var modalClose = modal ? Array.prototype.slice.call(modal.querySelectorAll('[data-cnb-modal-close]')) : [];

    if (!grid || !empty || !countNode || !workbench || !input || !copyBtn || !statusNode || !statusNote || !suggest || !hints || !modal || !modalTitle || !modalBody) {
        window[controlKey] = { teardown: function () {}, refresh: function () {} };
        return;
    }

    var listeners = [];
    function on(el, evt, fn, opts) {
        if (!el || !el.addEventListener) return;
        el.addEventListener(evt, fn, opts || false);
        listeners.push(function () { el.removeEventListener(evt, fn, opts || false); });
    }

    function n(v) { return String(v || '').trim().toLowerCase(); }
    function toInt(v, fallback) {
        var num = parseInt(String(v || ''), 10);
        return isNaN(num) ? fallback : num;
    }
    function esc(v) {
        return String(v == null ? '' : v)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function collectFixedTopInset() {
        var selectors = [
            '.header',
            '.site-header',
            '.main-header',
            '.top-header',
            '.topbar',
            '.navbar',
            '.nav',
            'header'
        ];
        var maxBottom = 0;
        selectors.forEach(function (selector) {
            var nodes = Array.prototype.slice.call(document.querySelectorAll(selector));
            nodes.forEach(function (node) {
                if (!node || node === workbench || workbench.contains(node)) {
                    return;
                }
                var style = window.getComputedStyle(node);
                var pos = String(style.position || '').toLowerCase();
                if (pos !== 'fixed' && pos !== 'sticky') {
                    return;
                }
                var rect = node.getBoundingClientRect();
                if (rect.bottom <= 0 || rect.height <= 0) {
                    return;
                }
                if (rect.top > 4) {
                    return;
                }
                if (rect.bottom > maxBottom) {
                    maxBottom = rect.bottom;
                }
            });
        });
        return Math.max(0, Math.floor(maxBottom));
    }

    function syncWorkbenchLayout() {
        if (!workbench) {
            return;
        }
        var viewport = Math.max(0, window.innerHeight || document.documentElement.clientHeight || 0);
        var topInset = collectFixedTopInset();
        var stickTop = Math.max(8, topInset + 8);
        var available = Math.max(0, viewport - stickTop - 8);

        workbench.style.setProperty('--cnb-stick-top', stickTop + 'px');
        workbench.style.setProperty('--cnb-workbench-max-height', Math.max(180, available) + 'px');

        var headHeight = workbench.querySelector('.cnb-workbench-head');
        var inputHeight = workbench.querySelector('.cnb-input-row');
        var hintsHeight = workbench.querySelector('.cnb-hints');

        var fixedPart = 26;
        if (headHeight) {
            fixedPart += headHeight.getBoundingClientRect().height;
        }
        if (inputHeight) {
            fixedPart += inputHeight.getBoundingClientRect().height;
        }
        if (hintsHeight) {
            fixedPart += hintsHeight.getBoundingClientRect().height;
        }
        fixedPart += 24;

        var suggestMax = Math.max(120, Math.min(340, Math.floor(available - fixedPart)));
        workbench.style.setProperty('--cnb-suggest-max-height', suggestMax + 'px');

        if (available < 240) {
            workbench.setAttribute('data-cnb-sticky-disabled', '1');
        } else {
            workbench.removeAttribute('data-cnb-sticky-disabled');
        }
    }

    function ensureWorkbenchVisible() {
        if (!workbench || workbench.getAttribute('data-cnb-sticky-disabled') === '1') {
            return;
        }
        var viewport = Math.max(0, window.innerHeight || document.documentElement.clientHeight || 0);
        var computed = window.getComputedStyle(workbench);
        var stickTop = toInt(computed.getPropertyValue('--cnb-stick-top'), 8);
        var bottomInset = 8;
        var rect = workbench.getBoundingClientRect();
        var allowed = viewport - stickTop - bottomInset;
        if (allowed <= 0 || rect.height > allowed + 1) {
            return;
        }

        var delta = 0;
        if (rect.top < stickTop) {
            delta = rect.top - stickTop;
        } else if (rect.bottom > viewport - bottomInset) {
            delta = rect.bottom - (viewport - bottomInset);
        }
        if (Math.abs(delta) > 2) {
            window.scrollBy({ top: delta, left: 0, behavior: 'smooth' });
        }
    }

    function splitInput(v) {
        var m = String(v || '').trim().match(/\S+/g);
        return m ? m : [];
    }

    function splitSyntax(v) {
        var m = String(v || '').trim().match(/\[[^\]]+\]|<[^>]+>|[^\s]+/g);
        return m ? m : [];
    }

    function parseToken(raw) {
        var t = String(raw || '').trim();
        var isOptional = t.length >= 2 && t.charAt(0) === '[' && t.charAt(t.length - 1) === ']';
        var isRequired = t.length >= 2 && t.charAt(0) === '<' && t.charAt(t.length - 1) === '>';
        if (isOptional || isRequired) {
            var inner = t.slice(1, -1).trim();
            var optsRaw = [];
            var opts = [];
            if (inner.indexOf('|') >= 0) {
                inner.split('|').forEach(function (x) {
                    var p = String(x || '').trim();
                    if (!p) return;
                    optsRaw.push(p);
                    opts.push(n(p));
                });
            }
            var r = inner.match(/^(-?\d+)\.\.(-?\d+)$/);
            return {
                type: 'ph',
                raw: t,
                inner: inner,
                optsRaw: optsRaw,
                opts: opts,
                range: r ? { min: parseInt(r[1], 10), max: parseInt(r[2], 10) } : null,
                required: isRequired
            };
        }
        return { type: 'lit', raw: t, val: n(t), required: true };
    }

    function isVariadic(p) {
        if (!p || p.type !== 'ph' || (p.opts && p.opts.length) || p.range) return false;
        return /message|reason|description|title|value|coords|starts with name/.test(n(p.inner));
    }
    function tokenMatch(spec, actual, prefix, argMeta) {
        var a = n(actual);
        if (spec.type === 'lit') return prefix ? spec.val.indexOf(a) === 0 : spec.val === a;
        var optNorm = spec.opts && spec.opts.length ? spec.opts.slice() : [];
        if (!optNorm.length && argMeta && Array.isArray(argMeta.options) && argMeta.options.length) {
            optNorm = argMeta.options.map(function (x) { return n(x); }).filter(Boolean);
        }
        if (optNorm.length) {
            if (prefix) return optNorm.some(function (o) { return o.indexOf(a) === 0; });
            return optNorm.indexOf(a) >= 0;
        }
        if (spec.range) {
            if (prefix && (a === '' || a === '-' || /^-?\d+$/.test(a))) return true;
            if (!/^-?\d+$/.test(a)) return false;
            var num = parseInt(a, 10);
            return num >= spec.range.min && num <= spec.range.max;
        }
        return a !== '';
    }

    var entries = data.map(function (item, idx) {
        var syntax = String(item.syntax || '').trim();
        var pattern = splitSyntax(syntax).map(parseToken);
        var requiredMin = 0;
        for (var i = 0; i < pattern.length; i++) {
            if (pattern[i].required) requiredMin++;
        }
        var argsRaw = Array.isArray(item.args) ? item.args.map(function (arg) {
            var a = arg && typeof arg === 'object' ? arg : {};
            return {
                name: String(a.name || '').trim(),
                nameZh: String(a.nameZh || '').trim(),
                parser: String(a.parser || '').trim(),
                parserZh: String(a.parserZh || '').trim(),
                optional: !!a.optional,
                options: Array.isArray(a.options) ? a.options.map(function (x) { return String(x || '').trim(); }).filter(Boolean) : [],
                optionsZh: a.optionsZh && typeof a.optionsZh === 'object' ? a.optionsZh : {},
                explanation: String(a.explanation || '').trim(),
                explanationZh: String(a.explanationZh || '').trim()
            };
        }) : [];
        var tokenArgs = [];
        var argCursor = 0;
        for (var j = 0; j < pattern.length; j++) {
            if (pattern[j].type !== 'lit') {
                tokenArgs[j] = argsRaw[argCursor] || null;
                argCursor++;
            } else {
                tokenArgs[j] = null;
            }
        }
        return {
            id: 'cnb-' + idx,
            idx: idx,
            syntax: syntax,
            syntaxN: n(syntax),
            summary: String(item.summary || '').trim(),
            summaryZh: String(item.summaryZh || '').trim(),
            scope: n(item.scope || 'server'),
            role: n(item.role || 'player'),
            category: String(item.category || '').trim(),
            examples: Array.isArray(item.examples) ? item.examples.map(function (x) { return String(x || '').trim(); }).filter(Boolean) : [],
            args: argsRaw,
            tokenArgs: tokenArgs,
            additionalInfo: String(item.additionalInfo || '').trim(),
            additionalInfoZh: String(item.additionalInfoZh || '').trim(),
            legacyRemoved: !!item.legacyRemoved,
            removedSince: String(item.removedSince || '').trim(),
            replacement: String(item.replacement || '').trim(),
            privilege: String(item.privilege || '').trim(),
            source: Array.isArray(item.source) ? item.source.map(function (x) { return String(x || '').trim(); }).filter(Boolean) : [],
            optionExplainMap: item.optionExplainMap && typeof item.optionExplainMap === 'object' ? item.optionExplainMap : {},
            pattern: pattern,
            first: pattern[0] && pattern[0].type === 'lit' ? pattern[0].val : '',
            firstRaw: pattern[0] && pattern[0].type === 'lit' ? pattern[0].raw : '',
            requiredMin: requiredMin,
            variadic: isVariadic(pattern[pattern.length - 1])
        };
    }).filter(function (e) { return e.syntax && e.pattern.length; });

    entries.forEach(function (e) {
        e.template = e.examples[0] || e.syntax.replace(/\[([^\]]+)\]|<([^>]+)>/g, function (_, a, b) {
            var s = String(a || b || '').trim();
            if (s.indexOf('|') >= 0) return s.split('|')[0].trim();
            if (/^-?\d+\.\.-?\d+$/.test(s)) return s.split('..')[0];
            return '<' + s.replace(/\s+/g, '_') + '>';
        });
        e.search = n([e.syntax, e.summary, e.summaryZh, e.category, e.scope, e.role, e.privilege, e.additionalInfo, e.additionalInfoZh, e.removedSince, e.replacement, e.examples.join(' '), e.args.map(function (a) { return [a.name, a.nameZh, a.explanation, a.explanationZh].join(' '); }).join(' ')].join(' '));
    });

    var entryMap = Object.create(null);
    var roots = [];
    entries.forEach(function (e) {
        entryMap[e.id] = e;
        if (e.firstRaw && roots.indexOf(e.firstRaw) < 0) roots.push(e.firstRaw);
    });

    var state = {
        scope: 'all',
        role: 'all',
        suggestions: [],
        suggestIndex: -1,
        modalEntry: null,
        modalOpen: false,
        lastFocus: null,
        noteTimer: 0
    };
    var defaultStatusNote = '输入 / 或 . 开始；Tab 补全；Enter 采用建议。';
    if (meta && meta.gameVersion) {
        defaultStatusNote += ' 当前库：' + String(meta.gameVersion);
    }

    function roleLabel(r) { return r === 'admin' ? '管理员' : (r === 'local' ? '本地' : '玩家'); }
    function scopeLabel(s) { return s === 'client' ? '客户端' : '服务器'; }
    function toneClass(v) {
        var t = n(v || 'info').replace(/[^a-z-]/g, '');
        return t || 'info';
    }
    var baseOptionExplain = {
        on: '开启',
        off: '关闭',
        true: '是',
        false: '否',
        day: '白天',
        night: '夜晚',
        survival: '生存',
        creative: '创造',
        spectator: '旁观',
        guest: '访客',
        still: '无风',
        lightbreeze: '轻风',
        mediumbreeze: '中风',
        strongbreeze: '强风',
        storm: '暴风',
        add: '添加',
        remove: '移除',
        all: '全部',
        use: '交互',
        traverse: '通行'
    };
    function optionExplain(token, entry, argMeta) {
        var key = n(token);
        if (argMeta && argMeta.optionsZh && typeof argMeta.optionsZh === 'object') {
            if (argMeta.optionsZh[token]) return String(argMeta.optionsZh[token]);
            var lowerHit = Object.keys(argMeta.optionsZh).find(function (k) { return n(k) === key; });
            if (lowerHit) return String(argMeta.optionsZh[lowerHit]);
        }
        if (entry && entry.optionExplainMap && entry.optionExplainMap[key]) {
            return String(entry.optionExplainMap[key]);
        }
        return baseOptionExplain[key] || '';
    }
    function optionLabel(token, entry, argMeta) {
        var desc = optionExplain(token, entry, argMeta);
        if (!desc) return token;
        return token + '（' + desc + '）';
    }

    function validate(entry, tokens) {
        var pat = entry.pattern;
        var bad = -1;
        var msg = '';
        var lit = 0;

        for (var i = 0; i < tokens.length; i++) {
            var spec = i < pat.length ? pat[i] : null;
            if (!spec) {
                bad = i;
                msg = '参数过多';
                break;
            }
            var actual = tokens[i];
            if (entry.variadic && i === pat.length - 1 && tokens.length > pat.length) actual = tokens.slice(i).join(' ');
            var curArgMeta = entry.tokenArgs[i] || null;
            if (!tokenMatch(spec, actual, false, curArgMeta)) {
                bad = i;
                if (spec.type === 'lit') msg = '应输入：' + spec.raw;
                else {
                    var argMeta = entry.tokenArgs[i] || null;
                    var optList = (spec.optsRaw && spec.optsRaw.length) ? spec.optsRaw.slice() : ((argMeta && Array.isArray(argMeta.options)) ? argMeta.options.slice() : []);
                    if (optList.length) {
                        msg = '可选：' + optList.slice(0, 8).map(function (opt) {
                            return optionLabel(opt, entry, argMeta);
                        }).join(' | ');
                    } else if (spec.range) {
                        msg = '范围：' + spec.range.min + '..' + spec.range.max;
                    } else {
                        msg = '参数不合法：' + spec.raw;
                    }
                }
                break;
            }
            if (spec.type === 'lit') lit++;
            if (entry.variadic && i === pat.length - 1 && tokens.length > pat.length) break;
        }

        var ok = bad === -1;
        var next = ok && tokens.length < pat.length ? pat[tokens.length] : null;
        var missingRequired = false;
        if (ok) {
            for (var j = tokens.length; j < pat.length; j++) {
                if (pat[j] && pat[j].required) {
                    missingRequired = true;
                    break;
                }
            }
        }
        var done = ok && tokens.length >= entry.requiredMin && !missingRequired;

        return { ok: ok, done: done, bad: bad, msg: msg, next: next, lit: lit };
    }

    function analyze(raw) {
        var txt = String(raw || '');
        var t = txt.trim();
        if (!t) return { cls: 'is-idle', title: '等待输入命令', note: defaultStatusNote, chips: [] };
        if (t.charAt(0) !== '/' && t.charAt(0) !== '.') return { cls: 'is-error', title: '前缀错误', note: '服务器命令用 /，客户端命令用 .', chips: ['/', '.'] };

        var tokens = splitInput(t);
        var first = n(tokens[0] || '');
        var pool = entries.filter(function (e) { return e.first === first; });

        if (!pool.length) {
            var similar = roots
                .map(function (x) { return { x: x, s: n(x).indexOf(first) === 0 ? 10 : (n(x).indexOf(first) >= 0 ? 5 : 0) }; })
                .filter(function (x) { return x.s > 0; })
                .sort(function (a, b) { return b.s - a.s; })
                .slice(0, 4)
                .map(function (x) { return x.x; });
            return { cls: 'is-error', title: '未知命令主词', note: similar.length ? ('你可能要输入：' + similar.join(' / ')) : '未找到该命令', chips: similar };
        }

        var validPool = [];
        var badPool = [];
        pool.forEach(function (e) {
            var r = validate(e, tokens);
            (r.ok ? validPool : badPool).push({ e: e, r: r });
        });

        if (validPool.length) {
            validPool.sort(function (a, b) {
                if (b.r.lit !== a.r.lit) return b.r.lit - a.r.lit;
                return b.e.pattern.length - a.e.pattern.length;
            });
            var top = validPool[0];
            var isLegacy = !!top.e.legacyRemoved;
            var chips = [];
            if (top.r.next) {
                if (top.r.next.type === 'lit') chips.push(top.r.next.raw);
                else {
                    var nextArgMeta = top.e.tokenArgs[tokens.length] || null;
                    var nextOptions = (top.r.next.optsRaw && top.r.next.optsRaw.length) ? top.r.next.optsRaw.slice() : ((nextArgMeta && Array.isArray(nextArgMeta.options)) ? nextArgMeta.options.slice() : []);
                    if (nextOptions.length) {
                        chips = nextOptions.slice(0, 6).map(function (opt) {
                            return optionLabel(opt, top.e, nextArgMeta);
                        });
                    } else chips.push(top.r.next.raw);
                }
            }
            if (isLegacy && top.e.replacement) chips.unshift('替代：' + top.e.replacement);
            return {
                cls: isLegacy ? 'is-warning' : (top.r.done ? 'is-success' : 'is-warning'),
                title: isLegacy ? '旧版命令（已移除）' : (top.r.done ? '语法通过' : '语法有效，可继续补全'),
                note: isLegacy ? (top.e.additionalInfoZh || top.e.additionalInfo || top.e.summaryZh || top.e.summary || top.e.syntax) : (top.e.summaryZh || top.e.summary || top.e.syntax),
                chips: chips,
                active: top.e
            };
        }

        badPool.sort(function (a, b) {
            if (b.r.bad !== a.r.bad) return b.r.bad - a.r.bad;
            return b.r.lit - a.r.lit;
        });
        var badTop = badPool[0];
        return { cls: 'is-error', title: '语法不通过', note: badTop.r.msg || '参数错误', chips: [], active: badTop.e };
    }

    function collectSuggestions(raw) {
        var inputText = String(raw || '');
        var trimmed = inputText.trim();
        if (!trimmed) return [];
        if (trimmed.charAt(0) !== '/' && trimmed.charAt(0) !== '.') return [];

        var endSpace = /\s$/.test(inputText);
        var tokens = splitInput(inputText);
        var activeToken = endSpace ? '' : (tokens[tokens.length - 1] || '');
        var fixed = endSpace ? tokens.slice() : tokens.slice(0, -1);
        var idx = fixed.length;
        var map = Object.create(null);

        function add(value, meta, score, tone) {
            var key = n(value);
            if (!key) return;
            if (!map[key] || score > map[key].score) map[key] = { value: value, meta: meta || '', score: score || 0, tone: tone || 'info' };
        }

        entries.forEach(function (e) {
            var ok = true;
            for (var i = 0; i < fixed.length; i++) {
                var spec = i < e.pattern.length ? e.pattern[i] : null;
                if (!spec) { ok = false; break; }
                var partText = fixed[i];
                if (e.variadic && i === e.pattern.length - 1 && fixed.length > e.pattern.length) partText = fixed.slice(i).join(' ');
                if (!tokenMatch(spec, partText, false, e.tokenArgs[i] || null)) { ok = false; break; }
                if (e.variadic && i === e.pattern.length - 1 && fixed.length > e.pattern.length) break;
            }
            if (!ok) return;
            var expect = idx < e.pattern.length ? e.pattern[idx] : (e.variadic ? e.pattern[e.pattern.length - 1] : null);
            if (!expect) return;
            if (expect.type === 'lit') add(expect.raw, '子命令', 84, 'literal');
            else if (expect.optsRaw && expect.optsRaw.length) {
                var argMeta = e.tokenArgs[idx] || null;
                expect.optsRaw.forEach(function (x) {
                    var desc = optionExplain(x, e, argMeta);
                    var argName = argMeta ? (argMeta.nameZh || argMeta.name || argMeta.parserZh || '') : '';
                    var meta = desc ? ('参数选项 · ' + desc) : (argName ? ('参数选项 · ' + argName) : '参数选项');
                    add(x, meta, 72, 'option');
                });
            } else {
                var argMetaFallback = e.tokenArgs[idx] || null;
                if (argMetaFallback && Array.isArray(argMetaFallback.options) && argMetaFallback.options.length) {
                    argMetaFallback.options.forEach(function (x) {
                        var desc = optionExplain(x, e, argMetaFallback);
                        var argName = argMetaFallback.nameZh || argMetaFallback.name || argMetaFallback.parserZh || '';
                        var meta = desc ? ('参数选项 · ' + desc) : (argName ? ('参数选项 · ' + argName) : '参数选项');
                        add(x, meta, 70, 'option');
                    }).join(' | ');
                }
            }
        });

        if (idx === 0) {
            var lead = trimmed.charAt(0);
            roots.forEach(function (x) {
                if (lead && x.charAt(0) !== lead) return;
                add(x, lead === '/' ? '服务器主词' : '客户端主词', 96, 'root');
            });
        }

        var q = n(activeToken);
        var list = Object.keys(map).map(function (k) { return map[k]; });
        list = list.filter(function (item) {
            if (!q) return true;
            var v = n(item.value);
            if (v === q) { item.score += 42; return true; }
            if (v.indexOf(q) === 0) { item.score += 26; return true; }
            if (v.indexOf(q) >= 0) { item.score += 10; return true; }
            return false;
        });
        list.sort(function (a, b) {
            if (b.score !== a.score) return b.score - a.score;
            return String(a.value || '').length - String(b.value || '').length;
        });
        return list.slice(0, 10);
    }

    function score(entry, query) {
        if (!query) return 1000 - entry.idx;
        var s = 0;
        if (entry.syntaxN === query) s += 340;
        if (entry.syntaxN.indexOf(query) === 0) s += 240;
        if (entry.syntaxN.indexOf(query) >= 0) s += 150;
        if (entry.search.indexOf(query) >= 0) s += 90;
        return s;
    }

    function renderFilters() {
        filterButtons.forEach(function (btn) {
            var f = String(btn.getAttribute('data-cnb-filter') || '');
            var v = String(btn.getAttribute('data-cnb-value') || '');
            var active = (f === 'scope' ? state.scope === v : state.role === v);
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    function renderGrid(list) {
        grid.innerHTML = '';
        if (!list.length) {
            empty.hidden = false;
            return;
        }
        empty.hidden = true;
        var frag = document.createDocumentFragment();
        list.forEach(function (e, idx) {
            var card = document.createElement('article');
            card.className = 'cnb-card' + (e.legacyRemoved ? ' is-legacy' : '');
            card.setAttribute('data-cnb-id', e.id);
            card.setAttribute('tabindex', '0');
            card.setAttribute('role', 'button');

            card.innerHTML = ''
                + '<div class="cnb-card-head">'
                + '<span class="cnb-card-order">#' + (idx + 1) + '</span>'
                + '<span class="cnb-card-tags">'
                + '<span class="cnb-tag cnb-tag-scope-' + esc(e.scope === 'client' ? 'client' : 'server') + '">' + esc(scopeLabel(e.scope)) + '</span>'
                + '<span class="cnb-tag cnb-tag-role-' + esc(e.role === 'admin' ? 'admin' : (e.role === 'local' ? 'local' : 'player')) + '">' + esc(roleLabel(e.role)) + '</span>'
                + (e.legacyRemoved ? '<span class="cnb-tag cnb-tag-legacy">旧版已移除</span>' : '')
                + (e.category ? ('<span class="cnb-tag cnb-tag-category">' + esc(e.category) + '</span>') : '')
                + '</span>'
                + '</div>'
                + '<p class="cnb-card-syntax">' + esc(e.syntax) + '</p>'
                + '<p class="cnb-card-summary">' + esc(e.summaryZh || e.summary || '点击查看详情') + '</p>'
                + '<div class="cnb-card-actions">'
                + '<button type="button" data-cnb-action="fill" data-cnb-id="' + esc(e.id) + '">填入</button>'
                + '<button type="button" data-cnb-action="copy" data-cnb-id="' + esc(e.id) + '">复制</button>'
                + '<button type="button" data-cnb-action="detail" data-cnb-id="' + esc(e.id) + '">详情</button>'
                + '</div>';

            frag.appendChild(card);
        });
        grid.appendChild(frag);
    }

    function renderSuggest() {
        if (!state.suggestions.length) {
            suggest.hidden = true;
            suggest.innerHTML = '';
            state.suggestIndex = -1;
            return;
        }
        suggest.hidden = false;
        suggest.innerHTML = state.suggestions.map(function (s, idx) {
            var tone = toneClass(s.tone);
            return '<li class="' + (idx === state.suggestIndex ? 'is-active' : '') + '" data-cnb-suggest-index="' + idx + '">'
                + '<button type="button" data-cnb-suggest-index="' + idx + '">'
                + '<span class="cnb-suggest-main">' + esc(s.value) + '</span>'
                + '<span class="cnb-suggest-meta is-' + tone + '">' + esc(s.meta || '补全建议') + '</span>'
                + '</button>'
                + '</li>';
        }).join('');
        ensureWorkbenchVisible();
    }

    function renderHints(analysis) {
        var list = [];
        if (analysis.active) list.push({ text: '匹配：' + analysis.active.syntax, tone: 'success' });
        (analysis.chips || []).forEach(function (x) {
            list.push({ text: '下一步：' + x, tone: analysis.cls === 'is-error' ? 'error' : 'warning' });
        });
        if (!list.length && analysis.note) {
            list.push({ text: analysis.note, tone: analysis.cls === 'is-error' ? 'error' : (analysis.cls === 'is-warning' ? 'warning' : 'info') });
        }
        hints.innerHTML = list.map(function (item) {
            return '<span class="cnb-hint-chip is-' + toneClass(item.tone) + '">' + esc(item.text) + '</span>';
        }).join('');
    }

    function setNote(text) {
        statusNote.textContent = text;
        if (state.noteTimer) {
            clearTimeout(state.noteTimer);
            state.noteTimer = 0;
        }
        state.noteTimer = setTimeout(function () {
            statusNote.textContent = defaultStatusNote;
            state.noteTimer = 0;
        }, 1600);
    }

    function copyText(text) {
        function fallback(v) {
            var ta = document.createElement('textarea');
            ta.value = v;
            ta.setAttribute('readonly', 'readonly');
            ta.style.position = 'fixed';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            var ok = false;
            try { ok = document.execCommand('copy'); } catch (e) { ok = false; }
            document.body.removeChild(ta);
            return ok;
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
            return navigator.clipboard.writeText(text).then(function () { return true; }).catch(function () { return fallback(text); });
        }
        return Promise.resolve(fallback(text));
    }

    function fill(entry) {
        input.value = entry.template || entry.syntax;
        input.focus();
        refresh();
    }

    function applySuggestion(idx) {
        var item = state.suggestions[idx];
        if (!item) return;
        var raw = String(input.value || '');
        var endSpace = /\s$/.test(raw);
        var tokens = splitInput(raw);
        if (endSpace || !tokens.length) tokens.push(item.value);
        else tokens[tokens.length - 1] = item.value;
        input.value = tokens.join(' ') + ' ';
        input.focus();
        refresh();
    }

    function openModal(entry, source) {
        state.modalOpen = true;
        state.modalEntry = entry;
        state.lastFocus = source || document.activeElement || null;

        var ex = entry.examples.length ? entry.examples : [entry.template || entry.syntax];
        var summaryMain = entry.summaryZh || entry.summary || '命令详情';
        var summaryEn = entry.summaryZh && entry.summary ? entry.summary : '';
        var isLegacy = !!entry.legacyRemoved;
        var argsHtml = '';
        if (entry.args && entry.args.length) {
            argsHtml = '<div class="cnb-modal-block"><p class="cnb-modal-block-title">参数说明</p>'
                + '<div class="cnb-modal-args-wrap"><table class="cnb-modal-args"><thead><tr><th>参数</th><th>类型</th><th>必填</th><th>说明</th></tr></thead><tbody>'
                + entry.args.map(function (arg) {
                    var name = arg.nameZh ? (arg.nameZh + (arg.name ? (' (' + arg.name + ')') : '')) : (arg.name || '-');
                    var type = arg.parserZh || arg.parser || '-';
                    var required = arg.optional ? '可选' : '必填';
                    var opts = Array.isArray(arg.options) && arg.options.length ? arg.options.map(function (opt) {
                        return optionLabel(opt, entry, arg);
                    }).join('、') : '';
                    var explain = arg.explanationZh || arg.explanation || '';
                    var desc = [explain, opts ? ('可选值：' + opts) : ''].filter(Boolean).join('；');
                    if (!desc) desc = arg.nameZh || arg.name || '-';
                    return '<tr><td>' + esc(name) + '</td><td>' + esc(type) + '</td><td>' + esc(required) + '</td><td>' + esc(desc) + '</td></tr>';
                }).join('')
                + '</tbody></table></div></div>';
        }
        var metaLine = [scopeLabel(entry.scope), roleLabel(entry.role)];
        if (isLegacy) {
            metaLine.unshift('状态: 旧版命令（已移除）');
        }
        if (entry.removedSince) metaLine.push('参考版本: ' + entry.removedSince);
        if (entry.replacement) metaLine.push('建议替代: ' + entry.replacement);
        if (entry.privilege) metaLine.push('权限: ' + entry.privilege);
        if (entry.source && entry.source.length) metaLine.push('来源: ' + entry.source[0]);
        var extraText = entry.additionalInfoZh || entry.additionalInfo || '';
        var extra = extraText ? ('<div class="cnb-modal-block"><p class="cnb-modal-block-title">补充说明</p><p>' + esc(extraText) + '</p></div>') : '';
        var legacyBlock = isLegacy ? ('<p class="cnb-modal-legacy">该命令已在新版本中移除，仅保留用于历史检索与兼容提示。</p>') : '';

        modalTitle.textContent = entry.syntax;
        modalBody.innerHTML = ''
            + legacyBlock
            + '<p class="cnb-modal-summary">' + esc(summaryMain) + '</p>'
            + (summaryEn ? ('<p class="cnb-modal-summary-sub">' + esc(summaryEn) + '</p>') : '')
            + '<p class="cnb-modal-meta">' + esc(metaLine.join(' · ')) + '</p>'
            + '<div class="cnb-modal-block"><p class="cnb-modal-block-title">语法</p><code class="cnb-modal-code">' + esc(entry.syntax) + '</code></div>'
            + '<div class="cnb-modal-block"><p class="cnb-modal-block-title">示例</p><ol>' + ex.map(function (x) { return '<li><code>' + esc(x) + '</code></li>'; }).join('') + '</ol></div>'
            + argsHtml
            + extra
            + '<div class="cnb-modal-actions">'
            + '<button type="button" data-cnb-modal-action="fill">填入输入框</button>'
            + '<button type="button" data-cnb-modal-action="copy">复制命令</button>'
            + '</div>';

        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.documentElement.classList.add('study-word-modal-open');
    }

    function closeModal(restore) {
        if (!state.modalOpen) return;
        state.modalOpen = false;
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.documentElement.classList.remove('study-word-modal-open');
        modalBody.innerHTML = '';
        if (restore && state.lastFocus && typeof state.lastFocus.focus === 'function') {
            setTimeout(function () {
                try { state.lastFocus.focus(); } catch (e) {}
            }, 0);
        }
        state.lastFocus = null;
    }

    function refresh() {
        syncWorkbenchLayout();
        renderFilters();
        var raw = String(input.value || '');
        var a = analyze(raw);
        statusNode.className = 'cnb-status ' + a.cls;
        statusNode.textContent = a.title;
        statusNote.textContent = a.note || defaultStatusNote;
        renderHints(a);

        state.suggestions = collectSuggestions(raw);
        if (state.suggestIndex >= state.suggestions.length) state.suggestIndex = state.suggestions.length - 1;
        renderSuggest();

        var q = n(raw);
        var list = entries
            .filter(function (e) { return (state.scope === 'all' || e.scope === state.scope) && (state.role === 'all' || e.role === state.role); })
            .map(function (e) { return { e: e, s: score(e, q) }; })
            .filter(function (x) { return !q || x.s > 0; })
            .sort(function (a, b) { return b.s - a.s; })
            .map(function (x) { return x.e; });

        renderGrid(list);
        countNode.textContent = '显示 ' + list.length + ' / ' + entries.length + ' 条命令';
    }

    on(input, 'input', function () { refresh(); });
    on(input, 'focus', function () {
        syncWorkbenchLayout();
        ensureWorkbenchVisible();
    });
    on(window, 'resize', function () {
        syncWorkbenchLayout();
    }, { passive: true });
    on(window, 'orientationchange', function () {
        syncWorkbenchLayout();
    }, { passive: true });

    on(input, 'keydown', function (ev) {
        var k = ev.key || ev.code;
        if (k === 'ArrowDown') {
            if (state.suggestions.length) {
                ev.preventDefault();
                state.suggestIndex = state.suggestIndex < 0 ? 0 : ((state.suggestIndex + 1) % state.suggestions.length);
                renderSuggest();
            }
            return;
        }
        if (k === 'ArrowUp') {
            if (state.suggestions.length) {
                ev.preventDefault();
                state.suggestIndex = state.suggestIndex < 0 ? state.suggestions.length - 1 : ((state.suggestIndex - 1 + state.suggestions.length) % state.suggestions.length);
                renderSuggest();
            }
            return;
        }
        if (k === 'Tab') {
            if (state.suggestions.length) {
                ev.preventDefault();
                applySuggestion(state.suggestIndex >= 0 ? state.suggestIndex : 0);
            }
            return;
        }
        if (k === 'Enter') {
            if (state.suggestions.length && state.suggestIndex >= 0) {
                ev.preventDefault();
                applySuggestion(state.suggestIndex);
            }
            return;
        }
        if (k === 'Escape' || k === 'Esc') {
            if (state.suggestions.length) {
                ev.preventDefault();
                state.suggestions = [];
                state.suggestIndex = -1;
                renderSuggest();
            }
        }
    });

    on(copyBtn, 'click', function (ev) {
        ev.preventDefault();
        var text = String(input.value || '').trim();
        if (!text && state.modalEntry) text = state.modalEntry.template || state.modalEntry.syntax;
        if (!text) {
            setNote('当前没有可复制命令');
            return;
        }
        copyText(text).then(function (ok) {
            setNote(ok ? '复制成功' : '复制失败');
        });
    });

    on(suggest, 'click', function (ev) {
        var btn = ev.target && ev.target.closest ? ev.target.closest('[data-cnb-suggest-index]') : null;
        if (!btn) return;
        ev.preventDefault();
        var idx = parseInt(btn.getAttribute('data-cnb-suggest-index') || '-1', 10);
        if (isNaN(idx) || idx < 0) return;
        applySuggestion(idx);
    });

    on(grid, 'click', function (ev) {
        var act = ev.target && ev.target.closest ? ev.target.closest('[data-cnb-action][data-cnb-id]') : null;
        if (act) {
            ev.preventDefault();
            var id = String(act.getAttribute('data-cnb-id') || '');
            var action = String(act.getAttribute('data-cnb-action') || '');
            var entry = entryMap[id];
            if (!entry) return;
            if (action === 'fill') { fill(entry); setNote('已填入输入框'); return; }
            if (action === 'copy') { copyText(entry.template || entry.syntax).then(function (ok) { setNote(ok ? '命令已复制' : '复制失败'); }); return; }
            if (action === 'detail') { openModal(entry, act); return; }
        }

        var card = ev.target && ev.target.closest ? ev.target.closest('[data-cnb-id]') : null;
        if (!card) return;
        var cid = String(card.getAttribute('data-cnb-id') || '');
        var e = entryMap[cid];
        if (!e) return;
        openModal(e, card);
    });

    on(grid, 'keydown', function (ev) {
        var key = ev.key || ev.code;
        if (key !== 'Enter' && key !== ' ' && key !== 'Spacebar') return;
        var card = ev.target && ev.target.closest ? ev.target.closest('[data-cnb-id]') : null;
        if (!card) return;
        ev.preventDefault();
        var id = String(card.getAttribute('data-cnb-id') || '');
        var entry = entryMap[id];
        if (entry) openModal(entry, card);
    });

    filterButtons.forEach(function (btn) {
        on(btn, 'click', function (ev) {
            ev.preventDefault();
            var f = String(btn.getAttribute('data-cnb-filter') || '');
            var v = String(btn.getAttribute('data-cnb-value') || '');
            if (f === 'scope') state.scope = v;
            else if (f === 'role') state.role = v;
            refresh();
        });
    });

    modalClose.forEach(function (node) {
        on(node, 'click', function (ev) {
            ev.preventDefault();
            closeModal(true);
        });
    });

    on(modal, 'click', function (ev) {
        var btn = ev.target && ev.target.closest ? ev.target.closest('[data-cnb-modal-action]') : null;
        if (!btn || !state.modalEntry) return;
        ev.preventDefault();
        var action = String(btn.getAttribute('data-cnb-modal-action') || '');
        if (action === 'fill') {
            fill(state.modalEntry);
            closeModal(true);
            setNote('已填入输入框');
            return;
        }
        if (action === 'copy') {
            copyText(state.modalEntry.template || state.modalEntry.syntax).then(function (ok) {
                setNote(ok ? '命令已复制' : '复制失败');
            });
        }
    });

    on(window, 'keydown', function (ev) {
        if (!state.modalOpen) return;
        var key = ev.key || ev.code;
        if (key === 'Escape' || key === 'Esc') {
            ev.preventDefault();
            closeModal(true);
        }
    }, true);

    function teardown() {
        closeModal(false);
        if (state.noteTimer) {
            clearTimeout(state.noteTimer);
            state.noteTimer = 0;
        }
        while (listeners.length) {
            var off = listeners.pop();
            try { off(); } catch (e) {}
        }
    }

    window[controlKey] = { teardown: teardown, refresh: refresh };
    refresh();
})();
</script>

<?php $this->need('footer.php'); ?>
