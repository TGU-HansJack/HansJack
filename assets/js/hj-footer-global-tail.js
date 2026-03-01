/* HansJack Footer Global Tail Bundle */

/* block 22 */
(function () {
        var contents = Array.prototype.slice.call(document.querySelectorAll(".hj-article-content, .hj-comment-content"));
        if (!contents || contents.length === 0) {
            return;
        }

        var targetCharsPerPartDesktop = 46;
        var targetCharsPerPartMobile = 34;

        function copyAttrs(from, to) {
            if (!from || !to || !from.attributes) {
                return;
            }

            var attrs = from.attributes;
            for (var i = 0; i < attrs.length; i++) {
                var attr = attrs[i];
                if (!attr || !attr.name) {
                    continue;
                }
                if (String(attr.name).toLowerCase() === "id") {
                    continue;
                }
                to.setAttribute(attr.name, attr.value);
            }
        }

        function stripIds(node) {
            if (!node || node.nodeType !== 1) {
                return;
            }

            if (node.removeAttribute) {
                node.removeAttribute("id");
            }
            if (!node.querySelectorAll) {
                return;
            }

            var idNodes = node.querySelectorAll("[id]");
            for (var i = 0; i < idNodes.length; i++) {
                if (idNodes[i] && idNodes[i].removeAttribute) {
                    idNodes[i].removeAttribute("id");
                }
            }
        }

        function hasComplexSpan(table) {
            if (!table || !table.querySelectorAll) {
                return true;
            }

            var spanCells = table.querySelectorAll("th[colspan], td[colspan], th[rowspan], td[rowspan]");
            for (var i = 0; i < spanCells.length; i++) {
                var cell = spanCells[i];
                if (!cell || !cell.getAttribute) {
                    continue;
                }

                var colspan = parseInt(cell.getAttribute("colspan") || "1", 10);
                var rowspan = parseInt(cell.getAttribute("rowspan") || "1", 10);
                if ((Number.isFinite(colspan) && colspan > 1) || (Number.isFinite(rowspan) && rowspan > 1)) {
                    return true;
                }
            }

            return false;
        }

        function getColumnCount(table) {
            if (!table) {
                return 0;
            }

            var firstRow = table.querySelector("tr");
            if (!firstRow || !firstRow.cells) {
                return 0;
            }

            return firstRow.cells.length;
        }

        function normalizeCellText(text) {
            return String(text || "").replace(/\s+/g, " ").trim();
        }

        function estimateCellWeight(cell, isHeader) {
            if (!cell) {
                return 0;
            }

            var text = normalizeCellText(cell.textContent || "");
            var len = text.length;
            if (len <= 0) {
                if (cell.querySelector && cell.querySelector("img,svg,video,canvas,iframe")) {
                    len = 12;
                } else {
                    len = 4;
                }
            }

            var weight = Math.min(48, len);
            if (isHeader) {
                weight = Math.max(weight, Math.min(28, Math.round(weight * 1.15)));
            }

            return Math.max(4, weight);
        }

        function estimateColumnWeights(table, columnCount) {
            var weights = [];
            for (var c = 0; c < columnCount; c++) {
                weights.push(4);
            }
            if (!table || !table.rows || table.rows.length === 0) {
                return weights;
            }

            var rows = table.rows;
            var rowLimit = Math.min(rows.length, 48);
            for (var r = 0; r < rowLimit; r++) {
                var row = rows[r];
                if (!row || !row.cells || row.cells.length === 0) {
                    continue;
                }

                var tag = row.parentNode && row.parentNode.tagName
                    ? String(row.parentNode.tagName).toLowerCase()
                    : "";
                var isHeader = tag === "thead";

                for (var i = 0; i < columnCount && i < row.cells.length; i++) {
                    var weight = estimateCellWeight(row.cells[i], isHeader);
                    if (weight > weights[i]) {
                        weights[i] = weight;
                    }
                }
            }

            return weights;
        }

        function compressSingleColumnGroups(groups) {
            if (!groups || groups.length <= 1) {
                return groups || [];
            }

            for (var i = 0; i < groups.length; i++) {
                var group = groups[i];
                if (!group) {
                    continue;
                }
                var size = group.end - group.start;
                if (size > 1) {
                    continue;
                }

                var prev = i > 0 ? groups[i - 1] : null;
                var next = i + 1 < groups.length ? groups[i + 1] : null;
                var prevSize = prev ? (prev.end - prev.start) : 0;
                var nextSize = next ? (next.end - next.start) : 0;

                if (next && nextSize > 1) {
                    group.end += 1;
                    next.start += 1;
                    continue;
                }

                if (prev && prevSize > 1) {
                    group.start -= 1;
                    prev.end -= 1;
                    continue;
                }

                if (prev) {
                    prev.end = group.end;
                    groups.splice(i, 1);
                    i -= 1;
                    continue;
                }

                if (next) {
                    next.start = group.start;
                    groups.splice(i, 1);
                    i -= 1;
                }
            }

            return groups;
        }

        function buildColumnGroupsByWeight(columnWeights) {
            if (!columnWeights || columnWeights.length <= 3) {
                return [];
            }

            var viewportWidth = 0;
            try {
                viewportWidth = Math.max(
                    window.innerWidth || 0,
                    document.documentElement ? (document.documentElement.clientWidth || 0) : 0
                );
            } catch (e) {
                viewportWidth = 0;
            }
            var targetCharsPerPart = viewportWidth > 0 && viewportWidth <= 720
                ? targetCharsPerPartMobile
                : targetCharsPerPartDesktop;
            var splitTriggerChars = targetCharsPerPart + 8;

            var totalWeight = 0;
            for (var i = 0; i < columnWeights.length; i++) {
                totalWeight += Math.max(4, parseInt(columnWeights[i] || 0, 10));
            }

            if (!Number.isFinite(totalWeight) || totalWeight <= splitTriggerChars) {
                return [];
            }

            var target = Math.max(28, targetCharsPerPart);
            var groups = [];
            var start = 0;
            var sum = 0;

            for (var c = 0; c < columnWeights.length; c++) {
                var weight = Math.max(4, parseInt(columnWeights[c] || 0, 10));
                if (c > start && sum + weight > target) {
                    groups.push({ start: start, end: c });
                    start = c;
                    sum = 0;
                }
                sum += weight;
            }
            groups.push({ start: start, end: columnWeights.length });

            groups = compressSingleColumnGroups(groups);
            if (!groups || groups.length < 2) {
                return [];
            }

            var normalized = [];
            for (var g = 0; g < groups.length; g++) {
                var item = groups[g];
                if (!item || !Number.isFinite(item.start) || !Number.isFinite(item.end) || item.end <= item.start) {
                    continue;
                }
                normalized.push({
                    start: Math.max(0, item.start),
                    end: Math.max(0, item.end)
                });
            }

            if (normalized.length < 2) {
                return [];
            }

            return normalized;
        }

        function cloneRows(rows, startCol, endCol) {
            var list = [];
            if (!rows || rows.length === 0) {
                return list;
            }

            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                if (!row || !row.cells || row.cells.length === 0) {
                    continue;
                }

                var clonedRow = row.cloneNode(false);
                stripIds(clonedRow);

                for (var c = startCol; c < endCol && c < row.cells.length; c++) {
                    var cell = row.cells[c];
                    if (!cell) {
                        continue;
                    }
                    var clonedCell = cell.cloneNode(true);
                    stripIds(clonedCell);
                    clonedRow.appendChild(clonedCell);
                }

                if (clonedRow.cells && clonedRow.cells.length > 0) {
                    list.push(clonedRow);
                }
            }

            return list;
        }

        function appendRowsToSection(targetTable, tagName, sectionNode, startCol, endCol) {
            if (!targetTable || !sectionNode || !sectionNode.rows || sectionNode.rows.length === 0) {
                return false;
            }

            var section = document.createElement(tagName);
            copyAttrs(sectionNode, section);
            var rows = cloneRows(Array.prototype.slice.call(sectionNode.rows), startCol, endCol);
            for (var i = 0; i < rows.length; i++) {
                section.appendChild(rows[i]);
            }

            if (!section.rows || section.rows.length === 0) {
                return false;
            }

            targetTable.appendChild(section);
            return true;
        }

        function buildSplitTable(sourceTable, startCol, endCol) {
            if (!sourceTable) {
                return null;
            }

            var table = document.createElement("table");
            copyAttrs(sourceTable, table);

            var appended = false;
            if (sourceTable.tHead && sourceTable.tHead.rows && sourceTable.tHead.rows.length > 0) {
                appended = appendRowsToSection(table, "thead", sourceTable.tHead, startCol, endCol) || appended;
            }

            if (sourceTable.tBodies && sourceTable.tBodies.length > 0) {
                for (var b = 0; b < sourceTable.tBodies.length; b++) {
                    var tbody = sourceTable.tBodies[b];
                    appended = appendRowsToSection(table, "tbody", tbody, startCol, endCol) || appended;
                }
            }

            if (sourceTable.tFoot && sourceTable.tFoot.rows && sourceTable.tFoot.rows.length > 0) {
                appended = appendRowsToSection(table, "tfoot", sourceTable.tFoot, startCol, endCol) || appended;
            }

            if (!appended && sourceTable.rows && sourceTable.rows.length > 0) {
                var body = document.createElement("tbody");
                var plainRows = cloneRows(Array.prototype.slice.call(sourceTable.rows), startCol, endCol);
                for (var r = 0; r < plainRows.length; r++) {
                    body.appendChild(plainRows[r]);
                }
                if (body.rows && body.rows.length > 0) {
                    table.appendChild(body);
                    appended = true;
                }
            }

            if (!appended || !table.rows || table.rows.length === 0) {
                return null;
            }

            return table;
        }

        function buildPartLabel(baseCaption, partIndex, partTotal, startCol, endCol) {
            var colRange = "列" + (startCol + 1) + "-" + endCol;
            if (baseCaption) {
                return baseCaption + "（" + (partIndex + 1) + "/" + partTotal + "，" + colRange + "）";
            }
            return "表" + (partIndex + 1) + "（" + colRange + "）";
        }

        for (var c = 0; c < contents.length; c++) {
            var content = contents[c];
            if (!content || !content.querySelectorAll) {
                continue;
            }

            var tables = Array.prototype.slice.call(content.querySelectorAll("table"));
            if (!tables || tables.length === 0) {
                continue;
            }

            for (var i = 0; i < tables.length; i++) {
                var table = tables[i];
                if (!table || !table.parentNode) {
                    continue;
                }

                if (table.closest && table.closest(".hj-table-split")) {
                    continue;
                }
                if (hasComplexSpan(table)) {
                    continue;
                }

                var columnCount = getColumnCount(table);
                if (!Number.isFinite(columnCount) || columnCount <= 3) {
                    continue;
                }

                var columnWeights = estimateColumnWeights(table, columnCount);
                var columnGroups = buildColumnGroupsByWeight(columnWeights);
                var partTotal = columnGroups.length;
                if (!Number.isFinite(partTotal) || partTotal < 2) {
                    continue;
                }

                var splitRoot = document.createElement("div");
                splitRoot.className = "hj-table-split";
                splitRoot.setAttribute("data-hj-table-split", "1");

                var baseCaption = "";
                if (table.caption) {
                    baseCaption = String(table.caption.textContent || "").trim();
                }

                for (var part = 0; part < partTotal; part++) {
                    var group = columnGroups[part];
                    if (!group || !Number.isFinite(group.start) || !Number.isFinite(group.end) || group.end <= group.start) {
                        continue;
                    }

                    var startCol = group.start;
                    var endCol = group.end;
                    var splitTable = buildSplitTable(table, startCol, endCol);
                    if (!splitTable) {
                        continue;
                    }

                    var item = document.createElement("section");
                    item.className = "hj-table-split-item";

                    var label = document.createElement("p");
                    label.className = "hj-table-split-label";
                    label.textContent = buildPartLabel(baseCaption, part, partTotal, startCol, endCol);

                    item.appendChild(label);
                    item.appendChild(splitTable);
                    splitRoot.appendChild(item);
                }

                if (!splitRoot.children || splitRoot.children.length === 0) {
                    continue;
                }

                try {
                    table.parentNode.insertBefore(splitRoot, table);
                    table.parentNode.removeChild(table);
                } catch (e) {}
            }
        }
    })();

/* block 23 */
(function () {
        var contents = Array.prototype.slice.call(document.querySelectorAll(".hj-article-content, .hj-comment-content"));
        if (!contents || contents.length === 0) {
            return;
        }

        function markLinkKind(a, href) {
            if (!a || !a.classList) {
                return;
            }
            var raw = (href || a.getAttribute("href") || "") + "";
            if (!raw) {
                return;
            }

            function setLinkKind(kind) {
                try {
                    a.classList.toggle("hj-link-mail", kind === "mail");
                    a.classList.toggle("hj-link-internal", kind === "internal");
                    a.classList.toggle("hj-link-external", kind === "external");
                } catch (e) {}
            }

            var lower = raw.toLowerCase();
            if (lower.indexOf("mailto:") === 0) {
                setLinkKind("mail");
                return;
            }
            if (lower.indexOf("tel:") === 0 || lower.indexOf("javascript:") === 0) {
                setLinkKind("external");
                return;
            }

            var url;
            try {
                url = new URL(raw, window.location.href);
            } catch (e) {
                setLinkKind("external");
                return;
            }
            if (!url || !url.host) {
                setLinkKind("external");
                return;
            }

            var isInternal = false;
            try {
                isInternal = url.host === window.location.host;
            } catch (e) {
                isInternal = false;
            }

            setLinkKind(isInternal ? "internal" : "external");
        }

        for (var c = 0; c < contents.length; c++) {
            var content = contents[c];
            if (!content || !content.querySelectorAll) {
                continue;
            }
            var links = Array.prototype.slice.call(content.querySelectorAll("a[href]"));
            if (links.length === 0) {
                continue;
            }

            links.forEach(function (a) {
                if (!a) {
                    return;
                }

                var text = ((a.textContent || "") + "").trim();
                if (!text) {
                    return;
                }

                var href = a.getAttribute("href") || "";
                markLinkKind(a, href);
            });
        }
    })();

/* block 24 */
(function () {
        var body = document.body;
        if (!body || !window.fetch || typeof window.URL !== "function") {
            return;
        }

        var isPostPage = !!(body.classList && body.classList.contains("hj-page-post"));
        var isListPage = !isPostPage && !!document.querySelector(".hj-posts-list");
        if (!isPostPage && !isListPage) {
            return;
        }

        var postCid = 0;
        if (isPostPage) {
            var postNode = document.querySelector("[data-hj-post-cid]");
            if (!postNode) {
                return;
            }

            postCid = parseInt(postNode.getAttribute("data-hj-post-cid") || "0", 10);
            if (!Number.isFinite(postCid) || postCid <= 0) {
                return;
            }
        }

        var baseInterval = 5000;
        var maxInterval = 60000;
        var nextInterval = baseInterval;
        var timerId = 0;
        var inFlight = false;
        var stopped = false;
        var hasUpdate = false;
        var baselineVersion = "";
        var toastRoot = null;
        var toastDuration = 4000;
        var toastStorageKey = "hj_live_reload_toast";
        var toastHideTimer = 0;
        var requestTimeout = 4500;

        function clearTimer() {
            if (timerId) {
                window.clearTimeout(timerId);
                timerId = 0;
            }
        }

        function scheduleNext(delay) {
            if (stopped) {
                return;
            }
            clearTimer();
            timerId = window.setTimeout(checkVersion, Math.max(1200, delay || baseInterval));
        }

        function buildVersionUrl() {
            var url;
            try {
                url = new URL(window.location.href);
            } catch (e) {
                return "";
            }

            try {
                url.hash = "";
                url.searchParams.set("hj_live_version", "1");
                url.searchParams.set("scope", isPostPage ? "post" : "list");
                if (isPostPage) {
                    url.searchParams.set("cid", String(postCid));
                } else {
                    url.searchParams.delete("cid");
                }
                url.searchParams.set("_ts", String(Date.now()));
            } catch (e) {
                return "";
            }

            return url.toString();
        }

        function ensureToastRoot() {
            if (toastRoot && toastRoot.parentNode) {
                return toastRoot;
            }

            try {
                toastRoot = document.createElement("div");
                toastRoot.className = "hj-live-toast-area";
                toastRoot.setAttribute("aria-live", "polite");
                toastRoot.setAttribute("aria-atomic", "true");
                body.appendChild(toastRoot);
                return toastRoot;
            } catch (e) {
                toastRoot = null;
                return null;
            }
        }

        function clearToastHideTimer() {
            if (toastHideTimer) {
                window.clearTimeout(toastHideTimer);
                toastHideTimer = 0;
            }
        }

        function hideToast() {
            clearToastHideTimer();
            var root = ensureToastRoot();
            if (!root) {
                return;
            }
            root.innerHTML = "";
        }

        function saveToastForNextLoad(payload) {
            if (!payload || typeof payload !== "object") {
                return false;
            }
            try {
                var next = {
                    kind: String(payload.kind || ""),
                    message: String(payload.message || ""),
                    title: String(payload.title || ""),
                    at: Date.now()
                };
                window.sessionStorage.setItem(toastStorageKey, JSON.stringify(next));
                return true;
            } catch (e) {
                return false;
            }
        }

        function showQueuedToast() {
            var raw = "";
            try {
                raw = String(window.sessionStorage.getItem(toastStorageKey) || "");
                window.sessionStorage.removeItem(toastStorageKey);
            } catch (e) {
                raw = "";
            }
            if (!raw) {
                return;
            }

            var payload = null;
            try {
                payload = JSON.parse(raw);
            } catch (e) {
                payload = null;
            }

            var at = payload && payload.at ? parseInt(payload.at, 10) : 0;
            if (!payload || typeof payload !== "object") {
                return;
            }
            if (Number.isFinite(at) && at > 0 && Math.abs(Date.now() - at) > 20000) {
                return;
            }

            showUpdateToast(payload);
        }

        function resolveLatestPostHref(title) {
            var links = Array.prototype.slice.call(document.querySelectorAll(".hj-posts-list .hj-posts-title[href]"));
            if (!links || links.length === 0) {
                return "";
            }

            var wanted = String(title || "").trim();
            var firstHref = "";

            for (var i = 0; i < links.length; i++) {
                var link = links[i];
                if (!link) {
                    continue;
                }
                var href = String(link.getAttribute("href") || "").trim();
                if (firstHref === "" && href !== "") {
                    firstHref = href;
                }
                var text = String((link.textContent || "")).trim();
                if (wanted !== "" && text === wanted && href !== "") {
                    return href;
                }
            }

            return firstHref;
        }

        function armToastAutoHide(toast) {
            if (!toast) {
                return;
            }

            function schedule(delay) {
                clearToastHideTimer();
                toastHideTimer = window.setTimeout(function tryHide() {
                    if (!toast || !toast.parentNode) {
                        clearToastHideTimer();
                        return;
                    }
                    if (toast.matches && toast.matches(":hover")) {
                        schedule(260);
                        return;
                    }
                    hideToast();
                }, delay);
            }

            toast.addEventListener("mouseenter", clearToastHideTimer);
            toast.addEventListener("mouseleave", function () {
                schedule(900);
            });
            schedule(toastDuration);
        }

        function reloadPage() {
            if (stopped) {
                return;
            }
            stopped = true;
            clearTimer();
            try {
                window.location.reload();
            } catch (e) {}
        }

        function showUpdateToast(payload) {
            var root = ensureToastRoot();
            if (!root) {
                return;
            }

            root.innerHTML = "";

            var data = payload && typeof payload === "object"
                ? payload
                : { message: String(payload || ""), kind: "text" };

            var toast = document.createElement("div");
            toast.className = "hj-live-toast";

            var icon = document.createElement("span");
            icon.className = "hj-live-toast-icon";
            icon.setAttribute("aria-hidden", "true");
            icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-fading-arrow-up-icon lucide-circle-fading-arrow-up"><path d="M12 2a10 10 0 0 1 7.38 16.75"></path><path d="m16 12-4-4-4 4"></path><path d="M12 16V8"></path><path d="M2.5 8.875a10 10 0 0 0-.5 3"></path><path d="M2.83 16a10 10 0 0 0 2.43 3.4"></path><path d="M4.636 5.235a10 10 0 0 1 .891-.857"></path><path d="M8.644 21.42a10 10 0 0 0 7.631-.38"></path></svg>';
            toast.appendChild(icon);

            var msg = document.createElement("p");
            msg.className = "hj-live-toast-text";
            if (data.kind === "list" && String(data.title || "").trim() !== "") {
                var title = String(data.title || "").trim();
                msg.appendChild(document.createTextNode("发布新文章："));
                var a = document.createElement("a");
                a.className = "hj-live-toast-link";
                a.textContent = title;
                var href = String(data.href || "").trim();
                if (!href) {
                    href = resolveLatestPostHref(title);
                }
                a.href = href || window.location.href;
                msg.appendChild(a);
            } else {
                msg.textContent = String(data.message || "");
            }
            toast.appendChild(msg);
            root.appendChild(toast);
            armToastAutoHide(toast);
        }

        function buildListToastPayload(payload) {
            var title = payload && payload.latestTitle ? String(payload.latestTitle).trim() : "";
            if (!title) {
                title = "xxxxx";
            }
            return {
                kind: "list",
                title: title,
                message: "发布新文章：" + title
            };
        }

        function handleVersionChanged(payload) {
            if (hasUpdate) {
                return;
            }
            hasUpdate = true;

            var toastPayload = isPostPage
                ? {
                    kind: "post",
                    message: "作者更新内容，文章页已自动刷新"
                }
                : buildListToastPayload(payload);

            if (!saveToastForNextLoad(toastPayload)) {
                showUpdateToast(toastPayload);
                window.setTimeout(reloadPage, 60);
                return;
            }

            reloadPage();
        }

        function onPayload(payload) {
            if (!payload || payload.ok !== true || typeof payload.version !== "string" || payload.version === "") {
                nextInterval = baseInterval;
                scheduleNext(nextInterval);
                return;
            }

            var version = payload.version;
            if (!baselineVersion) {
                baselineVersion = version;
                nextInterval = baseInterval;
                scheduleNext(nextInterval);
                return;
            }

            if (version !== baselineVersion) {
                handleVersionChanged(payload);
                return;
            }

            nextInterval = baseInterval;
            scheduleNext(nextInterval);
        }

        function onError() {
            nextInterval = Math.min(maxInterval, Math.max(baseInterval, nextInterval * 2));
            scheduleNext(nextInterval);
        }

        function checkVersion() {
            if (stopped || hasUpdate) {
                return;
            }

            if (document.visibilityState && document.visibilityState !== "visible") {
                scheduleNext(baseInterval);
                return;
            }

            if (inFlight) {
                scheduleNext(baseInterval);
                return;
            }

            var url = buildVersionUrl();
            if (!url) {
                onError();
                return;
            }

            inFlight = true;

            var abortController = null;
            var abortTimer = 0;
            if (typeof window.AbortController === "function") {
                abortController = new window.AbortController();
                abortTimer = window.setTimeout(function () {
                    try {
                        abortController.abort();
                    } catch (e) {}
                }, requestTimeout);
            }

            var fetchOptions = {
                method: "GET",
                credentials: "same-origin",
                cache: "no-store",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "Accept": "application/json"
                }
            };
            if (abortController) {
                fetchOptions.signal = abortController.signal;
            }

            window.fetch(url, fetchOptions).then(function (response) {
                if (!response || !response.ok) {
                    throw new Error("version check failed");
                }
                return response.json();
            }).then(function (payload) {
                if (abortTimer) {
                    window.clearTimeout(abortTimer);
                    abortTimer = 0;
                }
                inFlight = false;
                onPayload(payload);
            }).catch(function () {
                if (abortTimer) {
                    window.clearTimeout(abortTimer);
                    abortTimer = 0;
                }
                inFlight = false;
                onError();
            });
        }

        document.addEventListener("visibilitychange", function () {
            if (stopped || hasUpdate) {
                return;
            }
            if (document.visibilityState === "visible") {
                scheduleNext(1200);
            }
        });

        window.addEventListener("pagehide", function () {
            stopped = true;
            clearTimer();
        });

        showQueuedToast();
        scheduleNext(1800);
    })();
