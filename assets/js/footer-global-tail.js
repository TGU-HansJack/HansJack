/* Theme Footer Global Tail Bundle */

/* block 22 */
(function () {
        var contents = Array.prototype.slice.call(document.querySelectorAll(".article-content, .comment-content"));
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

                if (table.closest && table.closest(".table-split")) {
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
                splitRoot.className = "table-split";
                splitRoot.setAttribute("data-table-split", "1");

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
                    item.className = "table-split-item";

                    var label = document.createElement("p");
                    label.className = "table-split-label";
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
        var contents = Array.prototype.slice.call(document.querySelectorAll(".article-content, .comment-content"));
        if (!contents || contents.length === 0) {
            return;
        }

        var internalLinkMeta = {};
        try {
            if (window.__hansjackInternalLinkMeta && typeof window.__hansjackInternalLinkMeta === "object") {
                internalLinkMeta = window.__hansjackInternalLinkMeta;
            }
        } catch (e) {
            internalLinkMeta = {};
        }

        var internalTipMap = typeof WeakMap === "function" ? new WeakMap() : null;
        var internalTipFallback = [];
        var internalTipBound = typeof WeakSet === "function" ? new WeakSet() : null;
        var tooltipEl = null;
        var activeAnchor = null;
        var repositionRaf = 0;

        function normalizePathname(pathname) {
            var path = String(pathname || "").trim();
            if (!path) {
                return "";
            }

            path = path.replace(/\/{2,}/g, "/");
            if (path.charAt(0) !== "/") {
                path = "/" + path.replace(/^\/+/, "");
            }
            if (path.length > 1) {
                path = path.replace(/\/+$/, "");
            }
            return path || "/";
        }

        function setTipForAnchor(a, text) {
            if (!a) {
                return;
            }

            var normalized = String(text || "").trim();
            if (internalTipMap) {
                if (normalized) {
                    internalTipMap.set(a, normalized);
                } else {
                    internalTipMap.delete(a);
                }
                return;
            }

            for (var i = 0; i < internalTipFallback.length; i++) {
                var item = internalTipFallback[i];
                if (!item || item.anchor !== a) {
                    continue;
                }
                if (normalized) {
                    item.text = normalized;
                } else {
                    internalTipFallback.splice(i, 1);
                }
                return;
            }

            if (normalized) {
                internalTipFallback.push({
                    anchor: a,
                    text: normalized
                });
            }
        }

        function clearTipForAnchor(a) {
            setTipForAnchor(a, "");
        }

        function getTipForAnchor(a) {
            if (!a) {
                return "";
            }

            if (internalTipMap) {
                return String(internalTipMap.get(a) || "").trim();
            }

            for (var i = 0; i < internalTipFallback.length; i++) {
                var item = internalTipFallback[i];
                if (item && item.anchor === a) {
                    return String(item.text || "").trim();
                }
            }

            return "";
        }

        function ensureTooltip() {
            if (tooltipEl && tooltipEl.parentNode) {
                return tooltipEl;
            }

            try {
                var existing = document.querySelector(".link-internal-tooltip[data-link-internal-tooltip='1']");
                if (existing) {
                    tooltipEl = existing;
                    return tooltipEl;
                }
            } catch (e) {}

            try {
                tooltipEl = document.createElement("span");
                tooltipEl.className = "link-internal-tooltip";
                tooltipEl.setAttribute("role", "tooltip");
                tooltipEl.setAttribute("aria-hidden", "true");
                tooltipEl.setAttribute("data-link-internal-tooltip", "1");
                document.body.appendChild(tooltipEl);
                return tooltipEl;
            } catch (e) {
                tooltipEl = null;
                return null;
            }
        }

        function formatTipDate(unixSeconds) {
            var ts = parseInt(unixSeconds || "0", 10);
            if (!Number.isFinite(ts) || ts <= 0) {
                return "";
            }

            var d = new Date(ts * 1000);
            if (!d || Number.isNaN(d.getTime())) {
                return "";
            }

            function pad(value) {
                var n = parseInt(value || "0", 10);
                if (!Number.isFinite(n)) {
                    n = 0;
                }
                return n < 10 ? "0" + n : String(n);
            }

            return (
                String(d.getFullYear()) +
                "-" +
                pad(d.getMonth() + 1) +
                "-" +
                pad(d.getDate()) +
                " " +
                pad(d.getHours()) +
                ":" +
                pad(d.getMinutes())
            );
        }

        function readInternalMeta(urlObj) {
            if (!urlObj || !urlObj.pathname) {
                return null;
            }

            var key = normalizePathname(urlObj.pathname);
            if (!key) {
                return null;
            }

            var meta = internalLinkMeta[key];
            if (!meta && key !== "/") {
                meta = internalLinkMeta[key + "/"];
            }
            if (!meta || typeof meta !== "object") {
                return null;
            }

            var title = String(meta.title || "").trim();
            var created = parseInt(meta.created || "0", 10);
            var modified = parseInt(meta.modified || "0", 10);

            if (!Number.isFinite(created) || created < 0) {
                created = 0;
            }
            if (!Number.isFinite(modified) || modified < 0) {
                modified = 0;
            }

            return {
                title: title,
                created: created,
                modified: modified
            };
        }

        function buildInternalTipText(meta, fallbackTitle) {
            if (!meta || typeof meta !== "object") {
                return "";
            }

            var title = String(meta.title || fallbackTitle || "").trim();
            var created = parseInt(meta.created || "0", 10);
            var modified = parseInt(meta.modified || "0", 10);
            if (!Number.isFinite(created) || created < 0) {
                created = 0;
            }
            if (!Number.isFinite(modified) || modified < 0) {
                modified = 0;
            }
            if (modified <= 0) {
                modified = created;
            }

            var lines = [];
            if (title) {
                lines.push(title);
            }
            if (modified > 0) {
                lines.push("最后修改于 " + formatTipDate(modified));
            }
            if (created > 0) {
                lines.push("创建于 " + formatTipDate(created));
            }

            return lines.join("\n").trim();
        }

        function resolveInternalTipText(a, rawHref, urlObj) {
            if (!a || !a.classList || !urlObj) {
                return "";
            }
            if (a.classList.contains("footnote-ref") || a.classList.contains("footnote-backref")) {
                return "";
            }

            var rawText = String(rawHref || "").trim();
            if (!rawText || rawText.charAt(0) === "#") {
                return "";
            }

            var currentPath = normalizePathname(window.location.pathname || "");
            var targetPath = normalizePathname((urlObj && urlObj.pathname) || "");
            if (targetPath && currentPath && targetPath === currentPath) {
                var hashText = "";
                try {
                    hashText = String((urlObj && urlObj.hash) || "").trim();
                } catch (e) {
                    hashText = "";
                }
                if (hashText !== "") {
                    return "";
                }
            }

            var meta = readInternalMeta(urlObj);
            if (!meta) {
                return "";
            }

            var fallbackTitle = String(a.textContent || "").trim();

            var tipText = buildInternalTipText(meta, fallbackTitle);
            if (!tipText) {
                return "";
            }

            return tipText;
        }

        function hideTooltip() {
            activeAnchor = null;
            if (!tooltipEl) {
                return;
            }

            try {
                tooltipEl.classList.remove("is-visible");
                tooltipEl.classList.remove("is-measuring");
                tooltipEl.setAttribute("aria-hidden", "true");
            } catch (e) {}
        }

        function readTopGap(anchor, rect) {
            var lineHeight = 0;
            var fontSize = 0;
            try {
                var style = window.getComputedStyle ? window.getComputedStyle(anchor) : null;
                var rawLineHeight = style ? String(style.lineHeight || "").trim() : "";
                var rawFontSize = style ? String(style.fontSize || "").trim() : "";

                if (rawLineHeight && rawLineHeight !== "normal" && rawLineHeight.indexOf("px") !== -1) {
                    var parsedLineHeight = parseFloat(rawLineHeight);
                    if (Number.isFinite(parsedLineHeight) && parsedLineHeight > 0) {
                        lineHeight = parsedLineHeight;
                    }
                }

                if (rawFontSize && rawFontSize.indexOf("px") !== -1) {
                    var parsedFontSize = parseFloat(rawFontSize);
                    if (Number.isFinite(parsedFontSize) && parsedFontSize > 0) {
                        fontSize = parsedFontSize;
                    }
                }
            } catch (e) {
                lineHeight = 0;
                fontSize = 0;
            }

            var lineSpacing = 0;
            if (lineHeight > 0 && fontSize > 0) {
                lineSpacing = lineHeight - fontSize;
            }

            if (!Number.isFinite(lineSpacing) || lineSpacing <= 0) {
                if (lineHeight > 0) {
                    lineSpacing = lineHeight * 0.25;
                } else if (fontSize > 0) {
                    lineSpacing = fontSize * 0.25;
                } else {
                    var rectHeight = rect && Number.isFinite(rect.height) ? rect.height : 0;
                    lineSpacing = rectHeight > 0 ? rectHeight * 0.2 : 6;
                }
            }

            if (!Number.isFinite(lineSpacing) || lineSpacing <= 0) {
                lineSpacing = 6;
            }

            return Math.max(4, Math.min(14, Math.round(lineSpacing)));
        }

        function positionTooltip(anchor) {
            if (!anchor || !anchor.getBoundingClientRect) {
                hideTooltip();
                return;
            }

            var tip = ensureTooltip();
            if (!tip) {
                return;
            }

            var rect = anchor.getBoundingClientRect();
            if (
                !rect ||
                !Number.isFinite(rect.left) ||
                !Number.isFinite(rect.top) ||
                (!Number.isFinite(rect.width) && !Number.isFinite(rect.height))
            ) {
                hideTooltip();
                return;
            }

            var scrollX = window.pageXOffset || document.documentElement.scrollLeft || document.body.scrollLeft || 0;
            var scrollY = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
            var viewportW = Math.max(window.innerWidth || 0, (document.documentElement && document.documentElement.clientWidth) || 0);
            var viewportH = Math.max(window.innerHeight || 0, (document.documentElement && document.documentElement.clientHeight) || 0);
            if (viewportW <= 0 || viewportH <= 0) {
                hideTooltip();
                return;
            }

            var anchorX = rect.left + rect.width / 2;
            var anchorTop = rect.top;
            var anchorBottom = rect.bottom;
            var viewportPad = 8;
            var gapTop = readTopGap(anchor, rect);
            var gapBottom = 10;

            tip.style.left = "0px";
            tip.style.top = "0px";
            tip.setAttribute("data-placement", "top");
            tip.classList.add("is-measuring");
            tip.classList.add("is-visible");
            tip.setAttribute("aria-hidden", "false");

            var tipRect = tip.getBoundingClientRect();
            var tipW = tipRect && Number.isFinite(tipRect.width) ? tipRect.width : 0;
            var tipH = tipRect && Number.isFinite(tipRect.height) ? tipRect.height : 0;
            if (tipW <= 0 || tipH <= 0) {
                tip.classList.remove("is-measuring");
                return;
            }

            var leftViewport = anchorX - tipW / 2;
            var minLeft = viewportPad;
            var maxLeft = Math.max(viewportPad, viewportW - viewportPad - tipW);
            if (leftViewport < minLeft) {
                leftViewport = minLeft;
            } else if (leftViewport > maxLeft) {
                leftViewport = maxLeft;
            }

            var placement = "top";
            var topViewport = anchorTop - gapTop - tipH;
            var maxTop = Math.max(viewportPad, viewportH - viewportPad - tipH);
            if (topViewport < viewportPad) {
                placement = "bottom";
                topViewport = anchorBottom + gapBottom;
                if (topViewport > maxTop) {
                    topViewport = maxTop;
                }
            } else if (topViewport > maxTop) {
                topViewport = maxTop;
            }

            var docLeft = leftViewport + scrollX;
            var docTop = topViewport + scrollY;
            tip.style.left = Math.round(docLeft) + "px";
            tip.style.top = Math.round(docTop) + "px";
            tip.setAttribute("data-placement", placement);

            var arrowX = anchorX - leftViewport;
            var arrowPad = 11;
            if (tipW > arrowPad * 2) {
                if (arrowX < arrowPad) {
                    arrowX = arrowPad;
                } else if (arrowX > tipW - arrowPad) {
                    arrowX = tipW - arrowPad;
                }
            } else {
                arrowX = tipW / 2;
            }
            tip.style.setProperty("--internal-link-tooltip-arrow-x", Math.round(arrowX) + "px");
            tip.classList.remove("is-measuring");
        }

        function showTooltip(anchor, text) {
            var normalizedText = String(text || "").trim();
            if (!anchor || !normalizedText) {
                hideTooltip();
                return;
            }

            var tip = ensureTooltip();
            if (!tip) {
                return;
            }

            try {
                tip.textContent = normalizedText;
            } catch (e) {
                tip.textContent = "";
            }

            activeAnchor = anchor;
            tip.classList.add("is-visible");
            tip.setAttribute("aria-hidden", "false");
            positionTooltip(anchor);
        }

        function scheduleReposition() {
            if (!activeAnchor) {
                return;
            }
            if (repositionRaf) {
                return;
            }
            repositionRaf = window.requestAnimationFrame(function () {
                repositionRaf = 0;
                if (!activeAnchor || !document.contains(activeAnchor)) {
                    hideTooltip();
                    return;
                }
                positionTooltip(activeAnchor);
            });
        }

        function bindTooltipEvents(a) {
            if (!a || !a.addEventListener) {
                return;
            }
            if (internalTipBound && internalTipBound.has(a)) {
                return;
            }
            if (internalTipBound) {
                internalTipBound.add(a);
            }

            a.addEventListener("mouseenter", function () {
                var text = getTipForAnchor(a);
                if (!text) {
                    if (activeAnchor === a) {
                        hideTooltip();
                    }
                    return;
                }
                showTooltip(a, text);
            });

            a.addEventListener("mouseleave", function () {
                if (activeAnchor === a) {
                    hideTooltip();
                }
            });

            a.addEventListener("focus", function () {
                var text = getTipForAnchor(a);
                if (!text) {
                    if (activeAnchor === a) {
                        hideTooltip();
                    }
                    return;
                }
                showTooltip(a, text);
            });

            a.addEventListener("blur", function () {
                if (activeAnchor === a) {
                    hideTooltip();
                }
            });

            a.addEventListener("click", function () {
                if (activeAnchor === a) {
                    hideTooltip();
                }
            });
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
                    a.classList.toggle("link-mail", kind === "mail");
                    a.classList.toggle("link-internal", kind === "internal");
                    a.classList.toggle("link-external", kind === "external");
                } catch (e) {}
                if (kind !== "internal") {
                    clearTipForAnchor(a);
                    if (activeAnchor === a) {
                        hideTooltip();
                    }
                }
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

            var kind = isInternal ? "internal" : "external";
            setLinkKind(kind);
            if (kind === "internal") {
                var tipText = resolveInternalTipText(a, raw, url);
                setTipForAnchor(a, tipText);
                if (!tipText && activeAnchor === a) {
                    hideTooltip();
                }
            }
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
                bindTooltipEvents(a);
            });
        }

        window.addEventListener("scroll", scheduleReposition, { passive: true });
        window.addEventListener("resize", scheduleReposition);

        document.addEventListener("keydown", function (ev) {
            var key = ev && ev.key ? String(ev.key) : "";
            if (key === "Escape" || key === "Esc") {
                hideTooltip();
            }
        });

        document.addEventListener("visibilitychange", function () {
            if (document.visibilityState && document.visibilityState !== "visible") {
                hideTooltip();
            }
        });

        document.addEventListener("pointerdown", function (ev) {
            if (!activeAnchor) {
                return;
            }
            var target = ev && ev.target ? ev.target : null;
            if (target && target.closest) {
                var active = target.closest("a[href]");
                if (active === activeAnchor) {
                    return;
                }
            }
            hideTooltip();
        }, true);

        window.addEventListener("hansjack:pjax:after", function () {
            hideTooltip();
        });
    })();

/* block 24 */
(function () {
        var body = document.body;
        if (!body || !window.fetch || typeof window.URL !== "function") {
            return;
        }
        try {
            if (window.__hansjackLiveReloadEnabled === false) {
                return;
            }
        } catch (e) {}

        var isPostPage = !!(body.classList && body.classList.contains("page-post"));
        var isListPage = !isPostPage && !!document.querySelector(".posts-list");
        if (!isPostPage && !isListPage) {
            return;
        }

        var postCid = 0;
        if (isPostPage) {
            var postNode = document.querySelector("[data-post-cid]");
            if (!postNode) {
                return;
            }

            postCid = parseInt(postNode.getAttribute("data-post-cid") || "0", 10);
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
        var toastStorageKey = "live_reload_toast";
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
                url.searchParams.set("live_version", "1");
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
                toastRoot.className = "live-toast-area";
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
            var links = Array.prototype.slice.call(document.querySelectorAll(".posts-list .posts-title[href]"));
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
            toast.className = "live-toast";

            var icon = document.createElement("span");
            icon.className = "live-toast-icon";
            icon.setAttribute("aria-hidden", "true");
            icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-fading-arrow-up-icon lucide-circle-fading-arrow-up"><path d="M12 2a10 10 0 0 1 7.38 16.75"></path><path d="m16 12-4-4-4 4"></path><path d="M12 16V8"></path><path d="M2.5 8.875a10 10 0 0 0-.5 3"></path><path d="M2.83 16a10 10 0 0 0 2.43 3.4"></path><path d="M4.636 5.235a10 10 0 0 1 .891-.857"></path><path d="M8.644 21.42a10 10 0 0 0 7.631-.38"></path></svg>';
            toast.appendChild(icon);

            var msg = document.createElement("p");
            msg.className = "live-toast-text";
            if (data.kind === "list" && String(data.title || "").trim() !== "") {
                var title = String(data.title || "").trim();
                msg.appendChild(document.createTextNode("发布新文章："));
                var a = document.createElement("a");
                a.className = "live-toast-link";
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

        window.addEventListener("hansjack:pjax:after", function (e) {
            var detail = e && e.detail ? e.detail : null;
            if (!detail || detail.listPage !== true) {
                return;
            }
            // New list page after PJAX should establish a fresh polling baseline.
            hasUpdate = false;
            stopped = false;
            inFlight = false;
            baselineVersion = "";
            nextInterval = baseInterval;
            clearTimer();
            scheduleNext(1200);
        });

        showQueuedToast();
        scheduleNext(1800);
    })();
