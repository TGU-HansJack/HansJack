/* Theme Content Static Bundle */

/* block 17 */
(function () {
            var contents = Array.prototype.slice.call(document.querySelectorAll(".article-content, .comment-content"));
            if (!contents || contents.length === 0) {
                return;
            }

            function hasOnlyImageParagraph(p, carrier) {
                if (!p) {
                    return false;
                }

                var nodes = p.childNodes || [];
                for (var i = 0; i < nodes.length; i++) {
                    var n = nodes[i];
                    if (!n) {
                        continue;
                    }
                    if (n.nodeType === 3) {
                        if (String(n.textContent || "").trim() !== "") {
                            return false;
                        }
                        continue;
                    }
                    if (n.nodeType === 8) {
                        continue;
                    }
                    if (n.nodeType === 1) {
                        if (n !== carrier) {
                            return false;
                        }
                    }
                }

                return true;
            }

            function bindLazyBlurUp(img) {
                if (!img || !img.classList) {
                    return;
                }

                if (!img.hasAttribute("loading")) {
                    img.setAttribute("loading", "lazy");
                }

                if (img.dataset && img.dataset.blurupBound === "1") {
                    if (img.complete && img.naturalWidth > 0) {
                        img.classList.add("is-loaded");
                    }
                    return;
                }

                if (img.dataset) {
                    img.dataset.blurupBound = "1";
                }

                img.classList.add("lazy-blurup");

                function markLoaded() {
                    img.classList.add("is-loaded");
                }

                function markError() {
                    img.classList.remove("lazy-blurup");
                    img.classList.remove("is-loaded");
                }

                if (img.complete) {
                    if (img.naturalWidth > 0) {
                        markLoaded();
                    } else {
                        markError();
                    }
                    return;
                }

                img.addEventListener("load", markLoaded, { once: true });
                img.addEventListener("error", markError, { once: true });
            }

            for (var c = 0; c < contents.length; c++) {
                var content = contents[c];
                if (!content || !content.querySelectorAll) {
                    continue;
                }

                var imgs = Array.prototype.slice.call(content.querySelectorAll("img"));
                imgs.forEach(function (img) {
                    if (!img || !img.parentNode) {
                        return;
                    }

                    bindLazyBlurUp(img);

                    if (img.closest && img.closest("figure")) {
                        return;
                    }

                    var carrier = img;
                    var p = img.parentNode;
                    if (p && p.tagName === "A") {
                        carrier = p;
                        p = p.parentNode;
                    }

                    if (!p || p.tagName !== "P") {
                        return;
                    }
                    if (!hasOnlyImageParagraph(p, carrier)) {
                        return;
                    }

                    var caption = String(img.getAttribute("title") || "").trim();
                    if (!caption) {
                        caption = String(img.getAttribute("alt") || "").trim();
                    }

                    img.setAttribute("tabindex", "0");
                    if (img.hasAttribute("title")) {
                        img.removeAttribute("title");
                    }

                    if (carrier && carrier.tagName === "A") {
                        carrier.setAttribute("target", "_blank");
                        carrier.setAttribute("rel", "noopener noreferrer");
                    }

                    var figure = document.createElement("figure");
                    figure.appendChild(carrier);

                    if (caption) {
                        var figcaption = document.createElement("figcaption");
                        figcaption.textContent = caption;
                        figure.appendChild(figcaption);
                    }

                    try {
                        p.parentNode.replaceChild(figure, p);
                    } catch (e) {}
                });
            }
        })();

/* block 18 */
(function () {
            var contents = Array.prototype.slice.call(document.querySelectorAll(".article-content, .comment-content"));
            if (!contents || contents.length === 0) {
                return;
            }

            contents.forEach(function (content) {
                if (!content || !content.querySelectorAll) {
                    return;
                }

            function isBlockedParent(node) {
                var p = node && node.parentNode ? node.parentNode : null;
                while (p && p !== content) {
                    if (p.nodeType !== 1) {
                        p = p.parentNode;
                        continue;
                    }

                    var tag = (p.tagName || "").toUpperCase();
                    if (
                        tag === "CODE" ||
                        tag === "PRE" ||
                        tag === "A" ||
                        tag === "SCRIPT" ||
                        tag === "STYLE" ||
                        tag === "TEXTAREA" ||
                        tag === "RUBY" ||
                        tag === "RT" ||
                        tag === "INS" ||
                        tag === "MARK"
                    ) {
                        return true;
                    }
                    p = p.parentNode;
                }
                return false;
            }

            function buildRuby(baseText, annotations) {
                var ruby = document.createElement("ruby");
                ruby.appendChild(document.createTextNode(baseText));
                for (var i = 0; i < annotations.length; i++) {
                    var rt = document.createElement("rt");
                    rt.textContent = annotations[i];
                    ruby.appendChild(rt);
                }

                return ruby;
            }

            function buildSpoiler(text) {
                var span = document.createElement("span");
                span.className = "term term-spoiler spoiler";
                span.setAttribute("tabindex", "0");
                span.textContent = text;
                return span;
            }

            function buildIns(text) {
                var ins = document.createElement("ins");
                ins.textContent = text;
                return ins;
            }

            function buildMark(text) {
                var mark = document.createElement("mark");
                mark.textContent = text;
                return mark;
            }

            function parseRubyPayload(raw) {
                var inner = String(raw || "").trim();
                if (!inner) {
                    return null;
                }

                var splitAt = inner.indexOf(":");
                if (splitAt <= 0 || splitAt >= inner.length - 1) {
                    return null;
                }

                var base = inner.slice(0, splitAt).trim();
                var right = inner.slice(splitAt + 1).trim();
                if (!base || !right) {
                    return null;
                }

                var annotations = right
                    .split("|")
                    .map(function (item) {
                        return String(item || "").trim();
                    })
                    .filter(function (item) {
                        return item !== "";
                    });

                if (annotations.length === 0) {
                    return null;
                }

                return { base: base, annotations: annotations };
            }

            function hasSpoilerMarker(text) {
                var s = String(text || "");
                var first = s.indexOf("!!");
                if (first === -1) {
                    return false;
                }
                return s.indexOf("!!", first + 2) !== -1;
            }

            function hasInsMarker(text) {
                var s = String(text || "");
                var first = s.indexOf("++");
                if (first === -1) {
                    return false;
                }
                return s.indexOf("++", first + 2) !== -1;
            }

            function hasMarkMarker(text) {
                var s = String(text || "");
                var first = s.indexOf("==");
                if (first === -1) {
                    return false;
                }
                return s.indexOf("==", first + 2) !== -1;
            }

            function hasRubyMarker(text) {
                var s = String(text || "");
                var open = s.indexOf("{");
                while (open !== -1) {
                    var close = s.indexOf("}", open + 1);
                    if (close === -1) {
                        open = s.indexOf("{", open + 1);
                        continue;
                    }
                    var colon = s.indexOf(":", open + 1);
                    if (colon !== -1 && colon < close) {
                        return true;
                    }
                    open = s.indexOf("{", open + 1);
                }
                return false;
            }

            var ALERT_TYPES = {
                note: {
                    label: "Note",
                    path: "M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8Zm8-6.5a6.5 6.5 0 1 0 0 13 6.5 6.5 0 0 0 0-13ZM6.5 7.75A.75.75 0 0 1 7.25 7h1a.75.75 0 0 1 .75.75v2.75h.25a.75.75 0 0 1 0 1.5h-2a.75.75 0 0 1 0-1.5h.25v-2h-.25a.75.75 0 0 1-.75-.75ZM8 6a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"
                },
                tip: {
                    label: "Tip",
                    path: "M8 1.5c-2.363 0-4 1.69-4 3.75 0 .984.424 1.625.984 2.304l.214.253c.223.264.47.556.673.848.284.411.537.896.621 1.49a.75.75 0 0 1-1.484.211c-.04-.282-.163-.547-.37-.847a8.456 8.456 0 0 0-.542-.68c-.084-.1-.173-.205-.268-.32C3.201 7.75 2.5 6.766 2.5 5.25 2.5 2.31 4.863 0 8 0s5.5 2.31 5.5 5.25c0 1.516-.701 2.5-1.328 3.259-.095.115-.184.22-.268.319-.207.245-.383.453-.541.681-.208.3-.33.565-.37.847a.751.751 0 0 1-1.485-.212c.084-.593.337-1.078.621-1.489.203-.292.45-.584.673-.848.075-.088.147-.173.213-.253.561-.679.985-1.32.985-2.304 0-2.06-1.637-3.75-4-3.75ZM5.75 12h4.5a.75.75 0 0 1 0 1.5h-4.5a.75.75 0 0 1 0-1.5ZM6 15.25a.75.75 0 0 1 .75-.75h2.5a.75.75 0 0 1 0 1.5h-2.5a.75.75 0 0 1-.75-.75Z"
                },
                important: {
                    label: "Important",
                    path: "M0 1.75C0 .784.784 0 1.75 0h12.5C15.216 0 16 .784 16 1.75v9.5A1.75 1.75 0 0 1 14.25 13H8.06l-2.573 2.573A1.458 1.458 0 0 1 3 14.543V13H1.75A1.75 1.75 0 0 1 0 11.25Zm1.75-.25a.25.25 0 0 0-.25.25v9.5c0 .138.112.25.25.25h2a.75.75 0 0 1 .75.75v2.19l2.72-2.72a.749.749 0 0 1 .53-.22h6.5a.25.25 0 0 0 .25-.25v-9.5a.25.25 0 0 0-.25-.25Zm7 2.25v2.5a.75.75 0 0 1-1.5 0v-2.5a.75.75 0 0 1 1.5 0ZM9 9a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z"
                },
                warning: {
                    label: "Warning",
                    path: "M6.457 1.047c.659-1.234 2.427-1.234 3.086 0l6.082 11.378A1.75 1.75 0 0 1 14.082 15H1.918a1.75 1.75 0 0 1-1.543-2.575Zm1.763.707a.25.25 0 0 0-.44 0L1.698 13.132a.25.25 0 0 0 .22.368h12.164a.25.25 0 0 0 .22-.368Zm.53 3.996v2.5a.75.75 0 0 1-1.5 0v-2.5a.75.75 0 0 1 1.5 0ZM9 11a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z"
                },
                caution: {
                    label: "Caution",
                    path: "M4.47.22A.749.749 0 0 1 5 0h6c.199 0 .389.079.53.22l4.25 4.25c.141.14.22.331.22.53v6a.749.749 0 0 1-.22.53l-4.25 4.25A.749.749 0 0 1 11 16H5a.749.749 0 0 1-.53-.22L.22 11.53A.749.749 0 0 1 0 11V5c0-.199.079-.389.22-.53Zm.84 1.28L1.5 5.31v5.38l3.81 3.81h5.38l3.81-3.81V5.31L10.69 1.5ZM8 4a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 1 0-2 1 1 0 0 1 0 2Z"
                }
            };

            function parseAlertMarker(text) {
                var raw = String(text || "");
                var match = raw.match(/^\s*\[!([A-Za-z]+)\](?:[ \t]+([^\r\n]+))?(?:\r?\n|$)/);
                if (!match) {
                    return null;
                }

                var type = String(match[1] || "").toLowerCase();
                if (!ALERT_TYPES[type]) {
                    return null;
                }

                return {
                    type: type,
                    title: String(match[2] || "").trim(),
                    markerLength: String(match[0] || "").length
                };
            }

            function consumeLeadingText(root, count) {
                var remaining = Math.max(0, count | 0);
                if (!root || remaining <= 0) {
                    return;
                }

                function walk(node) {
                    if (!node || remaining <= 0) {
                        return;
                    }
                    if (node.nodeType === 3) {
                        var value = String(node.nodeValue || "");
                        if (value.length <= remaining) {
                            remaining -= value.length;
                            node.nodeValue = "";
                        } else {
                            node.nodeValue = value.slice(remaining);
                            remaining = 0;
                        }
                        return;
                    }

                    var child = node.firstChild;
                    while (child && remaining > 0) {
                        var next = child.nextSibling;
                        walk(child);
                        child = next;
                    }
                }

                walk(root);
            }

            function hasVisibleContent(node) {
                if (!node) {
                    return false;
                }
                if (node.nodeType === 3) {
                    return String(node.nodeValue || "").trim() !== "";
                }
                if (node.nodeType !== 1) {
                    return false;
                }

                var text = String(node.textContent || "").trim();
                if (text !== "") {
                    return true;
                }

                return !!(node.querySelector && node.querySelector("*"));
            }

            function createAlertIcon(pathData) {
                var ns = "http://www.w3.org/2000/svg";
                var svg = document.createElementNS(ns, "svg");
                svg.setAttribute("xmlns", ns);
                svg.setAttribute("viewBox", "0 0 16 16");
                svg.setAttribute("width", "16");
                svg.setAttribute("height", "16");
                svg.setAttribute("aria-hidden", "true");

                var path = document.createElementNS(ns, "path");
                path.setAttribute("d", pathData);
                svg.appendChild(path);
                return svg;
            }

            function findFirstNonEmptyTextNode(root) {
                if (!root) {
                    return null;
                }

                var hit = null;

                function walk(node) {
                    if (!node || hit) {
                        return;
                    }
                    if (node.nodeType === 3) {
                        if (String(node.nodeValue || "").trim() !== "") {
                            hit = node;
                        }
                        return;
                    }

                    var child = node.firstChild;
                    while (child && !hit) {
                        walk(child);
                        child = child.nextSibling;
                    }
                }

                walk(root);
                return hit;
            }

            function trimLeadingAlertArtifacts(root) {
                if (!root) {
                    return;
                }

                while (root.firstChild) {
                    var first = root.firstChild;
                    if (first.nodeType === 3 && String(first.nodeValue || "").trim() === "") {
                        root.removeChild(first);
                        continue;
                    }
                    if (first.nodeType === 1 && String(first.tagName || "").toUpperCase() === "BR") {
                        root.removeChild(first);
                        continue;
                    }
                    if (first.nodeType === 1 && String(first.tagName || "").toUpperCase() === "P") {
                        while (first.firstChild) {
                            var pFirst = first.firstChild;
                            if (pFirst.nodeType === 3 && String(pFirst.nodeValue || "").trim() === "") {
                                first.removeChild(pFirst);
                                continue;
                            }
                            if (pFirst.nodeType === 1 && String(pFirst.tagName || "").toUpperCase() === "BR") {
                                first.removeChild(pFirst);
                                continue;
                            }
                            break;
                        }
                        if (!hasVisibleContent(first)) {
                            root.removeChild(first);
                            continue;
                        }
                    }
                    break;
                }
            }

            function parseAlertFromBlock(block) {
                if (!block) {
                    return null;
                }

                var markerTextNode = findFirstNonEmptyTextNode(block);
                if (!markerTextNode) {
                    return null;
                }

                var marker = parseAlertMarker(markerTextNode.nodeValue || "");
                if (!marker) {
                    return null;
                }

                markerTextNode.nodeValue = String(markerTextNode.nodeValue || "").slice(marker.markerLength);
                trimLeadingAlertArtifacts(block);

                return {
                    type: marker.type,
                    title: marker.title,
                    hasBody: hasVisibleContent(block)
                };
            }

            function convertMarkdownAlerts() {
                var quotes = content.querySelectorAll("blockquote");
                if (!quotes || quotes.length === 0) {
                    return;
                }

                for (var i = 0; i < quotes.length; i++) {
                    var quote = quotes[i];
                    if (!quote || !quote.parentNode) {
                        continue;
                    }
                    if (quote.classList && quote.classList.contains("markdown-alert")) {
                        continue;
                    }

                    var quoteClone = quote.cloneNode(true);
                    var blocks = [];
                    var textBuffer = "";

                    function flushTextBuffer() {
                        var text = String(textBuffer || "");
                        textBuffer = "";
                        if (text.trim() === "") {
                            return;
                        }
                        var p = document.createElement("p");
                        p.textContent = text.trim();
                        blocks.push(p);
                    }

                    var rawChildren = Array.prototype.slice.call(quoteClone.childNodes || []);
                    for (var c = 0; c < rawChildren.length; c++) {
                        var rawChild = rawChildren[c];
                        if (!rawChild) {
                            continue;
                        }

                        if (rawChild.nodeType === 3) {
                            textBuffer += String(rawChild.nodeValue || "");
                            continue;
                        }

                        if (rawChild.nodeType === 1 && String(rawChild.tagName || "").toUpperCase() === "BR") {
                            flushTextBuffer();
                            continue;
                        }

                        flushTextBuffer();
                        blocks.push(rawChild);
                    }
                    flushTextBuffer();

                    if (blocks.length === 0) {
                        continue;
                    }

                    var segments = [];
                    var currentAlert = null;
                    var pendingQuoteNodes = [];
                    var hasAlert = false;

                    function pushPendingQuote() {
                        if (!pendingQuoteNodes || pendingQuoteNodes.length === 0) {
                            return;
                        }
                        segments.push({
                            kind: "quote",
                            nodes: pendingQuoteNodes
                        });
                        pendingQuoteNodes = [];
                    }

                    function pushCurrentAlert() {
                        if (!currentAlert) {
                            return;
                        }
                        segments.push(currentAlert);
                        currentAlert = null;
                    }

                    for (var b = 0; b < blocks.length; b++) {
                        var block = blocks[b];
                        if (!block) {
                            continue;
                        }

                        var parsed = parseAlertFromBlock(block);
                        if (parsed) {
                            hasAlert = true;
                            if (currentAlert) {
                                pushCurrentAlert();
                            }
                            if (pendingQuoteNodes.length > 0) {
                                pushPendingQuote();
                            }
                            currentAlert = {
                                kind: "alert",
                                type: parsed.type,
                                title: parsed.title,
                                nodes: []
                            };
                            if (parsed.hasBody) {
                                currentAlert.nodes.push(block);
                            }
                            continue;
                        }

                        if (!hasVisibleContent(block)) {
                            continue;
                        }

                        if (currentAlert) {
                            currentAlert.nodes.push(block);
                        } else {
                            pendingQuoteNodes.push(block);
                        }
                    }

                    if (currentAlert) {
                        pushCurrentAlert();
                    }
                    if (pendingQuoteNodes.length > 0) {
                        pushPendingQuote();
                    }

                    if (!hasAlert || segments.length === 0) {
                        continue;
                    }

                    var frag = document.createDocumentFragment();

                    for (var g = 0; g < segments.length; g++) {
                        var segment = segments[g];
                        if (!segment) {
                            continue;
                        }

                        if (segment.kind === "quote") {
                            var quoteOut = document.createElement("blockquote");
                            var quoteNodes = segment.nodes || [];
                            for (var qn = 0; qn < quoteNodes.length; qn++) {
                                var qNode = quoteNodes[qn];
                                if (!qNode || !hasVisibleContent(qNode)) {
                                    continue;
                                }
                                quoteOut.appendChild(qNode);
                            }
                            if (hasVisibleContent(quoteOut)) {
                                frag.appendChild(quoteOut);
                            }
                            continue;
                        }

                        var group = segment;
                        var meta = ALERT_TYPES[group.type];
                        if (!meta) {
                            continue;
                        }

                        var alert = document.createElement("div");
                        alert.className = "markdown-alert markdown-alert-" + group.type;

                        var title = document.createElement("p");
                        title.className = "markdown-alert-title";
                        title.appendChild(createAlertIcon(meta.path));

                        var strong = document.createElement("strong");
                        strong.textContent = group.title || meta.label;
                        title.appendChild(strong);
                        alert.appendChild(title);

                        for (var n = 0; n < group.nodes.length; n++) {
                            var node = group.nodes[n];
                            if (!node || !hasVisibleContent(node)) {
                                continue;
                            }
                            alert.appendChild(node);
                        }

                        var alertChildren = Array.prototype.slice.call(alert.children || []);
                        for (var ac = 0; ac < alertChildren.length; ac++) {
                            var childEl = alertChildren[ac];
                            if (!childEl) {
                                continue;
                            }
                            if (childEl.classList && childEl.classList.contains("markdown-alert-title")) {
                                continue;
                            }
                            if (!hasVisibleContent(childEl)) {
                                try {
                                    alert.removeChild(childEl);
                                } catch (e) {}
                            }
                        }

                        if (!alert.querySelector(":scope > :not(.markdown-alert-title)")) {
                            continue;
                        }

                        frag.appendChild(alert);
                    }

                    if (!frag.firstChild) {
                        continue;
                    }

                    try {
                        quote.parentNode.replaceChild(frag, quote);
                    } catch (e) {}
                }
            }

            function parseTextNode(node) {
                var text = node && node.nodeValue ? String(node.nodeValue) : "";
                if (!text || (!hasRubyMarker(text) && !hasSpoilerMarker(text) && !hasInsMarker(text) && !hasMarkMarker(text))) {
                    return;
                }

                var frag = document.createDocumentFragment();
                var pos = 0;

                while (pos < text.length) {
                    var spoilerOpen = text.indexOf("!!", pos);
                    var insOpen = text.indexOf("++", pos);
                    var markOpen = text.indexOf("==", pos);
                    var rubyOpen = text.indexOf("{", pos);
                    var nextType = "";
                    var nextPos = -1;

                    if (spoilerOpen !== -1) {
                        nextType = "spoiler";
                        nextPos = spoilerOpen;
                    }
                    if (insOpen !== -1 && (nextPos === -1 || insOpen < nextPos)) {
                        nextType = "ins";
                        nextPos = insOpen;
                    }
                    if (markOpen !== -1 && (nextPos === -1 || markOpen < nextPos)) {
                        nextType = "mark";
                        nextPos = markOpen;
                    }
                    if (rubyOpen !== -1 && (nextPos === -1 || rubyOpen < nextPos)) {
                        nextType = "ruby";
                        nextPos = rubyOpen;
                    }

                    if (nextPos === -1) {
                        frag.appendChild(document.createTextNode(text.slice(pos)));
                        break;
                    }

                    if (nextPos > pos) {
                        frag.appendChild(document.createTextNode(text.slice(pos, nextPos)));
                    }

                    if (nextType === "spoiler") {
                        var spoilerClose = text.indexOf("!!", nextPos + 2);
                        if (spoilerClose === -1) {
                            frag.appendChild(document.createTextNode(text.slice(nextPos)));
                            break;
                        }

                        var spoilerText = text.slice(nextPos + 2, spoilerClose);
                        var spoilerValid =
                            spoilerText !== "" &&
                            spoilerText.trim() === spoilerText &&
                            spoilerText.indexOf("\n") === -1 &&
                            spoilerText.indexOf("\r") === -1;

                        if (!spoilerValid) {
                            frag.appendChild(document.createTextNode("!!"));
                            pos = nextPos + 2;
                            continue;
                        }

                        frag.appendChild(buildSpoiler(spoilerText));
                        pos = spoilerClose + 2;
                        continue;
                    }

                    if (nextType === "ins") {
                        var insClose = text.indexOf("++", nextPos + 2);
                        if (insClose === -1) {
                            frag.appendChild(document.createTextNode(text.slice(nextPos)));
                            break;
                        }

                        var insText = text.slice(nextPos + 2, insClose);
                        var insValid =
                            insText !== "" &&
                            insText.trim() === insText &&
                            insText.indexOf("\n") === -1 &&
                            insText.indexOf("\r") === -1;

                        if (!insValid) {
                            frag.appendChild(document.createTextNode("++"));
                            pos = nextPos + 2;
                            continue;
                        }

                        frag.appendChild(buildIns(insText));
                        pos = insClose + 2;
                        continue;
                    }

                    if (nextType === "mark") {
                        var markClose = text.indexOf("==", nextPos + 2);
                        if (markClose === -1) {
                            frag.appendChild(document.createTextNode(text.slice(nextPos)));
                            break;
                        }

                        var markText = text.slice(nextPos + 2, markClose);
                        var markValid =
                            markText !== "" &&
                            markText.trim() === markText &&
                            markText.indexOf("\n") === -1 &&
                            markText.indexOf("\r") === -1;

                        if (!markValid) {
                            frag.appendChild(document.createTextNode("=="));
                            pos = nextPos + 2;
                            continue;
                        }

                        frag.appendChild(buildMark(markText));
                        pos = markClose + 2;
                        continue;
                    }

                    var rubyClose = text.indexOf("}", nextPos + 1);
                    if (rubyClose === -1) {
                        frag.appendChild(document.createTextNode(text.slice(nextPos)));
                        break;
                    }

                    var rubyRaw = text.slice(nextPos + 1, rubyClose);
                    var rubyParsed = parseRubyPayload(rubyRaw);
                    if (!rubyParsed) {
                        frag.appendChild(document.createTextNode("{"));
                        pos = nextPos + 1;
                        continue;
                    }

                    frag.appendChild(buildRuby(rubyParsed.base, rubyParsed.annotations));
                    pos = rubyClose + 1;
                }

                try {
                    node.parentNode.replaceChild(frag, node);
                } catch (e) {}
            }

            convertMarkdownAlerts();

            if (!document.createTreeWalker) {
                return;
            }

            var walker = document.createTreeWalker(
                content,
                NodeFilter.SHOW_TEXT,
                {
                    acceptNode: function (node) {
                        try {
                            if (!node || !node.nodeValue) {
                                return NodeFilter.FILTER_REJECT;
                            }
                            var nodeText = String(node.nodeValue);
                            if (!hasRubyMarker(nodeText) && !hasSpoilerMarker(nodeText) && !hasInsMarker(nodeText) && !hasMarkMarker(nodeText)) {
                                return NodeFilter.FILTER_REJECT;
                            }
                            if (isBlockedParent(node)) {
                                return NodeFilter.FILTER_REJECT;
                            }
                            return NodeFilter.FILTER_ACCEPT;
                        } catch (e) {
                            return NodeFilter.FILTER_REJECT;
                        }
                    }
                },
                false
            );

            var nodes = [];
            while (walker.nextNode()) {
                nodes.push(walker.currentNode);
            }
            for (var i = 0; i < nodes.length; i++) {
                parseTextNode(nodes[i]);
            }

            // Link tooltip syntax: [text](url "tooltip text")
            // Convert anchor title to our custom tooltip payload.
            var titleLinks = content.querySelectorAll("a[title]");
            for (var i = 0; i < titleLinks.length; i++) {
                var a = titleLinks[i];
                if (!a || !a.getAttribute || !a.classList) {
                    continue;
                }
                var tip = String(a.getAttribute("title") || "").trim();
                if (!tip) {
                    continue;
                }
                try {
                    a.classList.add("term-tooltip");
                    a.setAttribute("data-term", tip);
                    a.removeAttribute("title");
                } catch (e) {}
            }

            function findFootnoteTargetById(id) {
                var node = null;
                try {
                    node = document.getElementById(id);
                } catch (e) {
                    node = null;
                }
                if (node) {
                    return node;
                }
                try {
                    if (window.CSS && CSS.escape) {
                        node = content.querySelector("#" + CSS.escape(id));
                    }
                } catch (e) {
                    node = null;
                }
                return node;
            }

            var footnoteLinks = content.querySelectorAll('sup[id^="fnref-"] > a.footnote-ref[href^="#fn-"]');
            for (var i = 0; i < footnoteLinks.length; i++) {
                var footnoteLink = footnoteLinks[i];
                if (!footnoteLink || !footnoteLink.getAttribute) {
                    continue;
                }

                var href = String(footnoteLink.getAttribute("href") || "").trim();
                if (href.indexOf("#") !== 0 || href.length <= 1) {
                    continue;
                }

                var targetId = href.slice(1).trim();
                if (!targetId) {
                    continue;
                }

                var footnoteItem = findFootnoteTargetById(targetId);
                if (!footnoteItem) {
                    continue;
                }

                var footnoteClone = null;
                try {
                    footnoteClone = footnoteItem.cloneNode(true);
                } catch (e) {
                    footnoteClone = null;
                }
                if (!footnoteClone) {
                    continue;
                }

                var backrefs = footnoteClone.querySelectorAll('a.footnote-backref, a[href^="#fnref-"]');
                for (var j = 0; j < backrefs.length; j++) {
                    var backref = backrefs[j];
                    if (!backref || !backref.parentNode) {
                        continue;
                    }
                    try {
                        backref.parentNode.removeChild(backref);
                    } catch (e) {}
                }

                var dupIdNodes = footnoteClone.querySelectorAll("[id]");
                for (var k = 0; k < dupIdNodes.length; k++) {
                    try {
                        dupIdNodes[k].removeAttribute("id");
                    } catch (e) {}
                }

                var footnoteTipHtml = String(footnoteClone.innerHTML || "").trim();
                if (!footnoteTipHtml) {
                    continue;
                }

                var footnoteSup = footnoteLink.parentNode;
                if (!footnoteSup || String(footnoteSup.tagName || "").toUpperCase() !== "SUP" || !footnoteSup.classList) {
                    continue;
                }

                var oldTipNodes = footnoteSup.querySelectorAll(".footnote-tip");
                for (var m = 0; m < oldTipNodes.length; m++) {
                    var oldTipNode = oldTipNodes[m];
                    if (!oldTipNode || !oldTipNode.parentNode) {
                        continue;
                    }
                    try {
                        oldTipNode.parentNode.removeChild(oldTipNode);
                    } catch (e) {}
                }

                var tipNode = document.createElement("span");
                tipNode.className = "footnote-tip";
                tipNode.setAttribute("role", "tooltip");
                tipNode.innerHTML = footnoteTipHtml;

                try {
                    footnoteSup.classList.add("footnote-tooltip");
                    footnoteSup.setAttribute("data-footnote-tip", "1");
                    footnoteSup.appendChild(tipNode);
                } catch (e) {}
            }

            // Tooltip/Spoiler interactions on touch devices: click to toggle, click outside to close.
            var useClick = false;
            try {
                useClick = !!(window.matchMedia && window.matchMedia("(hover: none) and (pointer: coarse)").matches);
            } catch (e) {
                useClick = false;
            }
            if (!useClick) {
                return;
            }

            var toggles = [];
            var tooltips = content.querySelectorAll(".term-tooltip[data-term]");
            var spoilers = content.querySelectorAll(".term-spoiler");
            var footnoteTips = content.querySelectorAll("sup.footnote-tooltip[data-footnote-tip]");
            for (var i = 0; i < tooltips.length; i++) {
                toggles.push(tooltips[i]);
            }
            for (var j = 0; j < spoilers.length; j++) {
                toggles.push(spoilers[j]);
            }
            for (var k = 0; k < footnoteTips.length; k++) {
                toggles.push(footnoteTips[k]);
            }
            if (toggles.length === 0) {
                return;
            }

            function closeAll() {
                for (var i = 0; i < toggles.length; i++) {
                    try {
                        toggles[i].classList.remove("is-open");
                    } catch (e) {}
                }
            }

            for (var i = 0; i < toggles.length; i++) {
                (function (el) {
                    if (!el || !el.addEventListener) {
                        return;
                    }
                    el.addEventListener("click", function (e) {
                        var isOpen = false;
                        try {
                            isOpen = el.classList.contains("is-open");
                        } catch (err) {
                            isOpen = false;
                        }

                        var isFootnoteToggle = false;
                        try {
                            isFootnoteToggle = el.classList.contains("footnote-tooltip");
                        } catch (err) {
                            isFootnoteToggle = false;
                        }

                        if (isFootnoteToggle) {
                            var clickedFootnoteLink = false;
                            var clickedTipLink = false;
                            try {
                                var rawTarget = e && e.target ? e.target : null;
                                clickedFootnoteLink = !!(
                                    rawTarget &&
                                    rawTarget.closest &&
                                    rawTarget.closest('a.footnote-ref[href^="#fn-"]')
                                );
                                clickedTipLink = !!(
                                    rawTarget &&
                                    rawTarget.closest &&
                                    rawTarget.closest("span.footnote-tip a[href]")
                                );
                            } catch (err) {
                                clickedFootnoteLink = false;
                                clickedTipLink = false;
                            }

                            if (!isOpen) {
                                if (e && e.preventDefault) {
                                    e.preventDefault();
                                }
                                if (e && e.stopPropagation) {
                                    e.stopPropagation();
                                }
                                closeAll();
                                try {
                                    el.classList.add("is-open");
                                } catch (err) {}
                                return;
                            }

                            closeAll();
                            if (clickedFootnoteLink || clickedTipLink) {
                                return;
                            }
                            if (e && e.preventDefault) {
                                e.preventDefault();
                            }
                            if (e && e.stopPropagation) {
                                e.stopPropagation();
                            }
                            return;
                        }

                        var isLinkToggle = false;
                        try {
                            isLinkToggle =
                                (String(el.tagName || "").toUpperCase() === "A") &&
                                !!String(el.getAttribute("href") || "").trim();
                        } catch (err) {
                            isLinkToggle = false;
                        }

                        if (isLinkToggle) {
                            if (isOpen) {
                                closeAll();
                                return;
                            }
                            if (e && e.preventDefault) {
                                e.preventDefault();
                            }
                            if (e && e.stopPropagation) {
                                e.stopPropagation();
                            }
                            closeAll();
                            try {
                                el.classList.add("is-open");
                            } catch (err) {}
                            return;
                        }

                        if (e && e.preventDefault) {
                            e.preventDefault();
                        }
                        if (e && e.stopPropagation) {
                            e.stopPropagation();
                        }

                        closeAll();
                        if (!isOpen) {
                            try {
                                el.classList.add("is-open");
                            } catch (err) {}
                        }
                    });
                })(toggles[i]);
            }

            document.addEventListener("click", function () {
                closeAll();
            });

            document.addEventListener("keydown", function (e) {
                var key = e && e.key ? String(e.key) : "";
                if (key === "Escape" || key === "Esc") {
                    closeAll();
                }
            });
            });
        })();

/* block 19 */
(function () {
            var contents = Array.prototype.slice.call(document.querySelectorAll(".article-content, .comment-content"));
            if (!contents || contents.length === 0) {
                return;
            }

            function fallbackCopy(text) {
                var ok = false;
                var ta = document.createElement("textarea");
                ta.value = text;
                ta.setAttribute("readonly", "");
                ta.style.position = "absolute";
                ta.style.left = "-9999px";
                ta.style.top = "0";
                document.body.appendChild(ta);
                ta.select();
                try {
                    ta.setSelectionRange(0, ta.value.length);
                } catch (e) {}
                try {
                    ok = document.execCommand("copy");
                } catch (e) {
                    ok = false;
                }
                document.body.removeChild(ta);
                return ok;
            }

            function copyText(text) {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    return navigator.clipboard.writeText(text).then(
                        function () {
                            return true;
                        },
                        function () {
                            return fallbackCopy(text);
                        }
                    );
                }
                return Promise.resolve(fallbackCopy(text));
            }

            function setTip(btn, msg) {
                if (!btn || !btn.classList) {
                    return;
                }
                try {
                    btn.classList.add("is-copied");
                    btn.setAttribute("data-code-tip", msg);
                } catch (e) {}

                if (btn._copyTimer) {
                    clearTimeout(btn._copyTimer);
                }
                btn._copyTimer = setTimeout(function () {
                    try {
                        btn.classList.remove("is-copied");
                        btn.setAttribute("data-code-tip", "复制");
                    } catch (e) {}
                }, 1200);
            }

            var maxLines = 10;

            function countLines(text) {
                var t = String(text || "");
                t = t.replace(/\r\n/g, "\n").replace(/\r/g, "\n");
                while (t.length > 0 && t.charAt(t.length - 1) === "\n") {
                    t = t.slice(0, -1);
                }
                if (!t) {
                    return 0;
                }
                return t.split("\n").length;
            }

            function isAtBottom(el) {
                if (!el) {
                    return true;
                }

                var threshold = 2;
                var maxScrollTop = el.scrollHeight - el.clientHeight;
                if (maxScrollTop <= threshold) {
                    return true;
                }
                return el.scrollTop + el.clientHeight >= el.scrollHeight - threshold;
            }

            function syncFoldState(preEl, codeEl) {
                if (!preEl || !codeEl || !preEl.classList) {
                    return;
                }

                if (!preEl.classList.contains("code-collapsed")) {
                    try {
                        preEl.classList.remove("code-at-bottom");
                    } catch (e) {}
                    return;
                }

                var atBottom = isAtBottom(codeEl);
                try {
                    if (atBottom) {
                        preEl.classList.add("code-at-bottom");
                    } else {
                        preEl.classList.remove("code-at-bottom");
                    }
                } catch (e) {}
            }

            for (var c = 0; c < contents.length; c++) {
                var content = contents[c];
                if (!content || !content.querySelectorAll) {
                    continue;
                }

                var blocks = content.querySelectorAll("pre");
                if (!blocks || blocks.length === 0) {
                    continue;
                }

                for (var i = 0; i < blocks.length; i++) {
                    var pre = blocks[i];
                    if (!pre || !pre.querySelector) {
                        continue;
                    }

                    var code = pre.querySelector("code");
                    if (!code) {
                        continue;
                    }

                    var btn = pre.querySelector(".code-copy-btn");
                    if (!btn) {
                        btn = document.createElement("button");
                        btn.type = "button";
                        btn.className = "code-copy-btn";
                        btn.setAttribute("aria-label", "复制");
                        btn.setAttribute("data-code-tip", "复制");
                        btn.innerHTML =
                            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-copy-icon lucide-copy" aria-hidden="true"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>';
                        btn.addEventListener("click", function (e) {
                            if (e && e.preventDefault) {
                                e.preventDefault();
                            }
                            if (e && e.stopPropagation) {
                                e.stopPropagation();
                            }

                            var button = e && e.currentTarget ? e.currentTarget : null;
                            if (!button || !button.parentNode) {
                                return;
                            }
                            var preEl = button.parentNode;
                            if (!preEl || !preEl.querySelector) {
                                return;
                            }
                            var codeEl = preEl.querySelector("code");
                            var text = codeEl ? String(codeEl.textContent || "") : "";
                            if (!text) {
                                setTip(button, "无内容");
                                return;
                            }

                            copyText(text).then(function (ok) {
                                setTip(button, ok ? "已复制" : "复制失败");
                            });
                        });

                        pre.appendChild(btn);
                    }

                    var lineCount = countLines(code.textContent || "");
                    if (lineCount > maxLines) {
                        try {
                            pre.classList.add("code-collapsed");
                        } catch (e) {}

                        if (!pre.querySelector(".code-fold")) {
                            var fold = document.createElement("div");
                            fold.className = "code-fold";

                            var expand = document.createElement("button");
                            expand.type = "button";
                            expand.className = "code-expand-btn";
                            expand.setAttribute("aria-label", "展开");
                            expand.innerHTML =
                                '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down-to-line-icon lucide-arrow-down-to-line" aria-hidden="true"><path d="M12 17V3"/><path d="m6 11 6 6 6-6"/><path d="M19 21H5"/></svg><span>展开</span>';
                            expand.addEventListener("click", function (e) {
                                if (e && e.preventDefault) {
                                    e.preventDefault();
                                }
                                if (e && e.stopPropagation) {
                                    e.stopPropagation();
                                }

                                var button = e && e.currentTarget ? e.currentTarget : null;
                                var foldEl = button && button.parentNode ? button.parentNode : null;
                                var preEl = foldEl && foldEl.parentNode ? foldEl.parentNode : null;
                            if (!preEl || !preEl.classList) {
                                return;
                            }
                            try {
                                preEl.classList.remove("code-collapsed");
                                preEl.classList.remove("code-at-bottom");
                            } catch (err) {}
                            try {
                                if (foldEl && foldEl.remove) {
                                    foldEl.remove();
                                    } else if (foldEl) {
                                        foldEl.hidden = true;
                                    }
                                } catch (err) {}
                            });

                            fold.appendChild(expand);
                            pre.appendChild(fold);
                        }

                        if (!pre._foldScrollBound && code.addEventListener) {
                            pre._foldScrollBound = true;
                            var handler = (function (preEl, codeEl) {
                                return function () {
                                    syncFoldState(preEl, codeEl);
                                };
                            })(pre, code);
                            try {
                                code.addEventListener("scroll", handler, { passive: true });
                            } catch (err) {
                                code.addEventListener("scroll", handler);
                            }
                        }

                        syncFoldState(pre, code);
                    }
                }
            }
        })();
