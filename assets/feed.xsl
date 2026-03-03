<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:rss1="http://purl.org/rss/1.0/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    exclude-result-prefixes="atom rdf rss1 dc">

    <xsl:output method="html" indent="yes" encoding="UTF-8"/>
    <xsl:strip-space elements="*"/>

    <xsl:template match="/">
        <xsl:variable name="siteTitle">
            <xsl:choose>
                <xsl:when test="/rss/channel/title"><xsl:value-of select="/rss/channel/title"/></xsl:when>
                <xsl:when test="/atom:feed/atom:title"><xsl:value-of select="/atom:feed/atom:title"/></xsl:when>
                <xsl:when test="/rdf:RDF/rss1:channel/rss1:title"><xsl:value-of select="/rdf:RDF/rss1:channel/rss1:title"/></xsl:when>
                <xsl:otherwise>我的订阅</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <xsl:variable name="siteLink">
            <xsl:choose>
                <xsl:when test="/rss/channel/link"><xsl:value-of select="/rss/channel/link"/></xsl:when>
                <xsl:when test="/atom:feed/atom:link[@rel='alternate'][1]/@href"><xsl:value-of select="/atom:feed/atom:link[@rel='alternate'][1]/@href"/></xsl:when>
                <xsl:when test="/atom:feed/atom:link[1]/@href"><xsl:value-of select="/atom:feed/atom:link[1]/@href"/></xsl:when>
                <xsl:when test="/rdf:RDF/rss1:channel/rss1:link"><xsl:value-of select="/rdf:RDF/rss1:channel/rss1:link"/></xsl:when>
                <xsl:otherwise>#</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <xsl:variable name="siteDesc">
            <xsl:choose>
                <xsl:when test="/rss/channel/description"><xsl:value-of select="/rss/channel/description"/></xsl:when>
                <xsl:when test="/atom:feed/atom:subtitle"><xsl:value-of select="/atom:feed/atom:subtitle"/></xsl:when>
                <xsl:when test="/rdf:RDF/rss1:channel/rss1:description"><xsl:value-of select="/rdf:RDF/rss1:channel/rss1:description"/></xsl:when>
                <xsl:otherwise>订阅源页面</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <xsl:variable name="feedUrl">
            <xsl:choose>
                <xsl:when test="/rss/channel/atom:link[@rel='self'][1]/@href"><xsl:value-of select="/rss/channel/atom:link[@rel='self'][1]/@href"/></xsl:when>
                <xsl:when test="/atom:feed/atom:link[@rel='self'][1]/@href"><xsl:value-of select="/atom:feed/atom:link[@rel='self'][1]/@href"/></xsl:when>
                <xsl:when test="/atom:feed/atom:id"><xsl:value-of select="/atom:feed/atom:id"/></xsl:when>
                <xsl:when test="/rdf:RDF/rss1:channel/rss1:link"><xsl:value-of select="/rdf:RDF/rss1:channel/rss1:link"/></xsl:when>
                <xsl:when test="/rss/channel/link"><xsl:value-of select="/rss/channel/link"/></xsl:when>
                <xsl:otherwise></xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <xsl:variable name="lastUpdate">
            <xsl:choose>
                <xsl:when test="/rss/channel/lastBuildDate"><xsl:value-of select="/rss/channel/lastBuildDate"/></xsl:when>
                <xsl:when test="/rss/channel/pubDate"><xsl:value-of select="/rss/channel/pubDate"/></xsl:when>
                <xsl:when test="/atom:feed/atom:updated"><xsl:value-of select="/atom:feed/atom:updated"/></xsl:when>
                <xsl:when test="/rdf:RDF/rss1:channel/dc:date"><xsl:value-of select="/rdf:RDF/rss1:channel/dc:date"/></xsl:when>
                <xsl:otherwise>未知</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>

        <html lang="zh-CN">
            <head>
                <meta charset="UTF-8"/>
                <meta name="viewport" content="width=device-width, initial-scale=1"/>
                <title>
                    <xsl:value-of select="$siteTitle"/> · 订阅源
                </title>
                <style type="text/css"><![CDATA[
                    :root {
                        --fg: #2a2a28;
                        --muted: #757575;
                        --bg: #fffffd;
                        --line: rgba(117, 117, 117, 0.28);
                        --chip: rgba(42, 42, 40, 0.06);
                    }
                    * { box-sizing: border-box; }
                    html, body { margin: 0; padding: 0; }
                    body {
                        color: var(--fg);
                        background: var(--bg);
                        font-family: "Source Han Serif CN VF", "Source Han Serif SC", "Songti SC", "SimSun", Georgia, serif;
                        line-height: 1.7;
                    }
                    .feed-wrap {
                        width: min(860px, calc(100vw - 1.6rem));
                        margin: 1.2rem auto 2rem;
                    }
                    header h1 {
                        margin: 0 0 0.25rem;
                        font-size: clamp(1.4rem, 2.6vw, 2rem);
                        line-height: 1.25;
                    }
                    header h1 a {
                        color: inherit;
                        text-decoration: none;
                        border-bottom: 1px solid transparent;
                    }
                    header h1 a:hover,
                    header h1 a:focus-visible {
                        border-bottom-color: currentColor;
                    }
                    header p {
                        margin: 0;
                        color: var(--muted);
                        font-size: 0.95rem;
                    }
                    blockquote {
                        margin: 0.95rem 0 1.1rem;
                        padding: 0.75rem 0.8rem;
                        border-left: 3px solid var(--fg);
                        background: var(--chip);
                    }
                    blockquote p {
                        margin: 0.28rem 0;
                        font-size: 0.9rem;
                    }
                    code {
                        padding: 0.08rem 0.26rem;
                        border: 1px dashed var(--line);
                        background: #fff;
                        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
                        font-size: 0.82rem;
                        line-height: 1.3;
                        word-break: break-all;
                    }
                    .copy-btn {
                        margin-left: 0.35rem;
                        border: 1px solid var(--line);
                        background: #fff;
                        color: inherit;
                        font-family: inherit;
                        font-size: 0.78rem;
                        line-height: 1;
                        padding: 0.25rem 0.5rem;
                        cursor: pointer;
                    }
                    .copy-btn:hover,
                    .copy-btn:focus-visible {
                        border-color: var(--fg);
                    }
                    main {
                        display: flex;
                        flex-direction: column;
                        gap: 0.55rem;
                    }
                    section {
                        padding: 0.6rem 0;
                        border-bottom: 1px dashed var(--line);
                    }
                    article {
                        display: flex;
                        align-items: baseline;
                        justify-content: space-between;
                        gap: 0.65rem;
                        min-width: 0;
                    }
                    article a {
                        min-width: 0;
                        color: inherit;
                        text-decoration: none;
                        white-space: nowrap;
                        overflow: hidden;
                        text-overflow: ellipsis;
                        font-size: 0.98rem;
                        line-height: 1.35;
                    }
                    article a:hover,
                    article a:focus-visible {
                        text-decoration: underline;
                    }
                    article time {
                        flex: 0 0 auto;
                        color: var(--muted);
                        font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace;
                        font-size: 0.74rem;
                    }
                    aside {
                        margin-top: 0.3rem;
                        display: flex;
                        flex-wrap: wrap;
                        gap: 0.3rem;
                    }
                    aside span {
                        display: inline-block;
                        padding: 0.05rem 0.33rem;
                        border: 1px solid var(--line);
                        font-size: 0.72rem;
                        line-height: 1.15;
                    }
                    .feed-empty {
                        margin: 0;
                        color: var(--muted);
                        font-size: 0.9rem;
                    }
                    footer {
                        margin-top: 1.2rem;
                        padding-top: 0.8rem;
                        border-top: 2px solid var(--line);
                    }
                    footer p {
                        margin: 0.2rem 0;
                        font-size: 0.84rem;
                        color: var(--muted);
                    }
                    footer a {
                        color: inherit;
                    }
                    @media (max-width: 740px) {
                        .feed-wrap {
                            width: calc(100vw - 1rem);
                            margin-top: 0.8rem;
                        }
                        article {
                            flex-direction: column;
                            gap: 0.15rem;
                        }
                        article time {
                            font-size: 0.7rem;
                        }
                    }
                    @media (prefers-color-scheme: dark) {
                        :root {
                            --fg: #dddddb;
                            --muted: #a5a5a5;
                            --bg: #0e0e0c;
                            --line: rgba(165, 165, 165, 0.35);
                            --chip: rgba(255, 255, 255, 0.03);
                        }
                        code, .copy-btn {
                            background: rgba(255, 255, 255, 0.02);
                        }
                    }
                ]]></style>
            </head>
            <body>
                <div class="feed-wrap">
                    <header>
                        <h1><a href="{normalize-space($siteLink)}"><xsl:value-of select="$siteTitle"/></a></h1>
                        <p><xsl:value-of select="$siteDesc"/></p>
                    </header>

                    <blockquote>
                        <p>本页面是内容订阅源。</p>
                        <p>您可以在任何支持的阅读器中添加当前地址来订阅此内容，以便及时获取最新更新。</p>
                        <p>
                            订阅地址:
                            <code id="feed-url"><xsl:value-of select="$feedUrl"/></code>
                            <button type="button" class="copy-btn" onclick="copyFeedUrl()">复制</button>
                        </p>
                    </blockquote>

                    <main>
                        <xsl:choose>
                            <xsl:when test="/rss/channel/item">
                                <xsl:for-each select="/rss/channel/item">
                                    <section>
                                        <article>
                                            <a href="{link}"><xsl:value-of select="title"/></a>
                                            <time><xsl:value-of select="pubDate"/></time>
                                        </article>
                                        <aside>
                                            <xsl:for-each select="category">
                                                <span>#<xsl:value-of select="."/></span>
                                            </xsl:for-each>
                                        </aside>
                                    </section>
                                </xsl:for-each>
                            </xsl:when>
                            <xsl:when test="/atom:feed/atom:entry">
                                <xsl:for-each select="/atom:feed/atom:entry">
                                    <section>
                                        <article>
                                            <a href="{atom:link[@rel='alternate'][1]/@href}">
                                                <xsl:value-of select="atom:title"/>
                                            </a>
                                            <time>
                                                <xsl:choose>
                                                    <xsl:when test="atom:published"><xsl:value-of select="atom:published"/></xsl:when>
                                                    <xsl:otherwise><xsl:value-of select="atom:updated"/></xsl:otherwise>
                                                </xsl:choose>
                                            </time>
                                        </article>
                                        <aside>
                                            <xsl:for-each select="atom:category">
                                                <span>#<xsl:value-of select="@term"/></span>
                                            </xsl:for-each>
                                        </aside>
                                    </section>
                                </xsl:for-each>
                            </xsl:when>
                            <xsl:when test="/rdf:RDF/rss1:item">
                                <xsl:for-each select="/rdf:RDF/rss1:item">
                                    <section>
                                        <article>
                                            <a href="{rss1:link}"><xsl:value-of select="rss1:title"/></a>
                                            <time><xsl:value-of select="dc:date"/></time>
                                        </article>
                                    </section>
                                </xsl:for-each>
                            </xsl:when>
                            <xsl:otherwise>
                                <p class="feed-empty">当前订阅源暂无可展示内容。</p>
                            </xsl:otherwise>
                        </xsl:choose>
                    </main>

                    <footer>
                        <p>
                            这是订阅源页面。访问
                            <a href="{normalize-space($siteLink)}"><xsl:value-of select="$siteTitle"/></a>
                            以获得完整的网站体验。
                        </p>
                        <p>最后更新: <xsl:value-of select="$lastUpdate"/></p>
                    </footer>
                </div>

                <script type="text/javascript"><![CDATA[
                    function copyFeedUrl() {
                        var node = document.getElementById("feed-url");
                        var text = node ? (node.textContent || "") : "";
                        if (!text) {
                            alert("未获取到订阅地址");
                            return;
                        }
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(text).then(function () {
                                alert("订阅地址已复制到剪贴板！");
                            }).catch(function () {
                                alert("订阅地址已复制到剪贴板！");
                            });
                        } else {
                            alert("订阅地址已复制到剪贴板！");
                        }
                    }
                ]]></script>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
