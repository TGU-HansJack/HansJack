/* Theme PJAX Lite (list pages only) */
(function () {
    if (typeof window === "undefined" || typeof document === "undefined") {
        return;
    }
    if (!window.fetch || !window.history || !window.history.pushState || typeof window.DOMParser !== "function") {
        return;
    }

    var MAIN_SELECTOR = "main.main";
    var LIST_SELECTOR = ".posts-list";
    var CACHE_TTL = 45000;
    var CACHE_LIMIT = 16;
    var pageCache = new Map();
    var pendingPrefetch = new Set();
    var activeController = null;
    var navToken = 0;

    function isListPageDoc(doc) {
        return !!(doc && doc.querySelector && doc.querySelector(LIST_SELECTOR));
    }

    function isCurrentListPage() {
        return !!document.querySelector(LIST_SELECTOR);
    }

    function parseUrl(raw) {
        try {
            return new URL(String(raw || ""), window.location.href);
        } catch (e) {
            return null;
        }
    }

    function normalizeFetchUrl(urlObj) {
        if (!urlObj) {
            return "";
        }
        var clone = new URL(urlObj.toString());
        clone.hash = "";
        return clone.toString();
    }

    function cacheGet(key) {
        if (!key || !pageCache.has(key)) {
            return "";
        }
        var row = pageCache.get(key);
        if (!row || !row.html || !row.ts) {
            pageCache.delete(key);
            return "";
        }
        if (Date.now() - row.ts > CACHE_TTL) {
            pageCache.delete(key);
            return "";
        }
        return String(row.html);
    }

    function cacheSet(key, html) {
        if (!key || !html) {
            return;
        }
        pageCache.set(key, { html: String(html), ts: Date.now() });
        while (pageCache.size > CACHE_LIMIT) {
            var first = pageCache.keys().next();
            if (!first || first.done) {
                break;
            }
            pageCache.delete(first.value);
        }
    }

    function dispatchPjaxEvent(name, detail) {
        var payload = detail && typeof detail === "object" ? detail : {};
        try {
            var evt = new CustomEvent(name, { detail: payload });
            window.dispatchEvent(evt);
        } catch (e) {}
        try {
            var docEvt = new CustomEvent(name, { detail: payload });
            document.dispatchEvent(docEvt);
        } catch (e) {}
    }

    function syncBodyClass(nextDoc) {
        if (!nextDoc || !nextDoc.body || !document.body) {
            return;
        }
        try {
            document.body.className = nextDoc.body.className || "";
        } catch (e) {}
    }

    function syncDocumentTitle(nextDoc) {
        if (!nextDoc || !nextDoc.querySelector) {
            return;
        }
        var titleEl = nextDoc.querySelector("title");
        if (!titleEl) {
            return;
        }
        try {
            var text = String(titleEl.textContent || "").trim();
            if (text) {
                document.title = text;
            }
        } catch (e) {}
    }

    function syncCanonical(nextDoc, pageUrl) {
        if (!nextDoc || !nextDoc.querySelector || !document.head) {
            return;
        }
        var nextCanonical = nextDoc.querySelector('link[rel="canonical"]');
        if (!nextCanonical) {
            return;
        }

        var href = String(nextCanonical.getAttribute("href") || "").trim();
        if (!href) {
            return;
        }

        var resolved = "";
        try {
            resolved = new URL(href, pageUrl || window.location.href).toString();
        } catch (e) {
            resolved = href;
        }

        var currentCanonical = document.head.querySelector('link[rel="canonical"]');
        if (currentCanonical) {
            currentCanonical.setAttribute("href", resolved);
            return;
        }

        try {
            var node = document.createElement("link");
            node.setAttribute("rel", "canonical");
            node.setAttribute("href", resolved);
            document.head.appendChild(node);
        } catch (e) {}
    }

    function syncDesktopNav(nextDoc) {
        if (!nextDoc || !nextDoc.querySelectorAll) {
            return;
        }

        var currentLinks = Array.prototype.slice.call(document.querySelectorAll(".nav-desktop .nav-link[data-nav-key]"));
        if (!currentLinks || currentLinks.length === 0) {
            return;
        }

        var stateByKey = {};
        var nextLinks = Array.prototype.slice.call(nextDoc.querySelectorAll(".nav-desktop .nav-link[data-nav-key]"));
        nextLinks.forEach(function (link) {
            if (!link || !link.getAttribute || !link.classList) {
                return;
            }
            var key = String(link.getAttribute("data-nav-key") || "").trim();
            if (!key) {
                return;
            }
            stateByKey[key] = link.classList.contains("is-active");
        });

        currentLinks.forEach(function (link) {
            if (!link || !link.getAttribute || !link.classList) {
                return;
            }
            var key = String(link.getAttribute("data-nav-key") || "").trim();
            if (!key) {
                return;
            }
            link.classList.toggle("is-active", !!stateByKey[key]);
        });
    }

    function syncMobileNav(nextDoc, pageUrl) {
        if (!nextDoc || !nextDoc.querySelectorAll) {
            return;
        }

        var activeHrefMap = {};
        var nextLinks = Array.prototype.slice.call(nextDoc.querySelectorAll(".mobile-nav .user-dropdown-item.is-active[href]"));
        nextLinks.forEach(function (link) {
            if (!link || !link.getAttribute) {
                return;
            }
            var raw = String(link.getAttribute("href") || "").trim();
            if (!raw) {
                return;
            }
            try {
                var abs = new URL(raw, pageUrl || window.location.href).toString();
                activeHrefMap[abs] = true;
            } catch (e) {}
        });

        var currentLinks = Array.prototype.slice.call(document.querySelectorAll(".mobile-nav .user-dropdown-item[href]"));
        currentLinks.forEach(function (link) {
            if (!link || !link.getAttribute || !link.classList) {
                return;
            }
            var raw = String(link.getAttribute("href") || "").trim();
            if (!raw) {
                link.classList.remove("is-active");
                return;
            }
            var absHref = "";
            try {
                absHref = new URL(raw, window.location.href).toString();
            } catch (e) {
                absHref = "";
            }
            link.classList.toggle("is-active", !!(absHref && activeHrefMap[absHref]));
        });
    }

    function updateHistoryStateScroll() {
        var state = window.history.state;
        if (!state || state.hansjackPjax !== true) {
            return;
        }
        try {
            var top = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0;
            var next = {
                hansjackPjax: true,
                url: String(state.url || window.location.href),
                scrollY: Math.max(0, Math.floor(top || 0))
            };
            window.history.replaceState(next, "", window.location.href);
        } catch (e) {}
    }

    function scrollForNavigation(urlObj, popStateY) {
        var targetY = parseInt(popStateY || "0", 10);
        if (isFinite(targetY) && targetY > 0) {
            try {
                window.scrollTo({ top: targetY, behavior: "auto" });
                return;
            } catch (e) {
                window.scrollTo(0, targetY);
                return;
            }
        }

        var hash = urlObj && urlObj.hash ? String(urlObj.hash) : "";
        if (hash && hash.length > 1) {
            var id = hash.slice(1);
            var target = null;
            try {
                target = document.getElementById(id);
            } catch (e) {
                target = null;
            }
            if (!target && document.querySelector) {
                try {
                    target = document.querySelector(hash);
                } catch (e) {
                    target = null;
                }
            }
            if (target && target.scrollIntoView) {
                try {
                    target.scrollIntoView({ block: "start", behavior: "auto" });
                    return;
                } catch (e) {
                    target.scrollIntoView();
                    return;
                }
            }
        }

        try {
            window.scrollTo({ top: 0, behavior: "auto" });
        } catch (e) {
            window.scrollTo(0, 0);
        }
    }

    function hardNavigate(url) {
        if (!url) {
            return;
        }
        try {
            window.location.href = url;
        } catch (e) {
            window.location.assign(url);
        }
    }

    function fetchPageHtml(fetchUrl, token) {
        var cached = cacheGet(fetchUrl);
        if (cached) {
            return Promise.resolve(cached);
        }

        if (activeController) {
            try {
                activeController.abort();
            } catch (e) {}
        }

        var controller = null;
        if (typeof window.AbortController === "function") {
            controller = new window.AbortController();
            activeController = controller;
        } else {
            activeController = null;
        }

        var options = {
            method: "GET",
            credentials: "same-origin",
            cache: "no-store",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "text/html"
            }
        };
        if (controller) {
            options.signal = controller.signal;
        }

        return window.fetch(fetchUrl, options).then(function (response) {
            if (!response || !response.ok) {
                throw new Error("pjax fetch failed");
            }
            return response.text();
        }).then(function (html) {
            if (token !== navToken) {
                return "";
            }
            cacheSet(fetchUrl, html);
            return html;
        });
    }

    function parseHtml(html) {
        var parser = new window.DOMParser();
        return parser.parseFromString(String(html || ""), "text/html");
    }

    function renderPage(nextDoc, targetUrlObj, mode, stateScrollY) {
        var currentMain = document.querySelector(MAIN_SELECTOR);
        var nextMain = nextDoc && nextDoc.querySelector ? nextDoc.querySelector(MAIN_SELECTOR) : null;
        if (!currentMain || !nextMain) {
            return false;
        }
        if (!isListPageDoc(nextDoc)) {
            return false;
        }

        dispatchPjaxEvent("hansjack:pjax:before", {
            url: targetUrlObj ? targetUrlObj.toString() : window.location.href,
            listPage: true
        });

        try {
            currentMain.replaceWith(nextMain);
        } catch (e) {
            return false;
        }

        syncDocumentTitle(nextDoc);
        syncBodyClass(nextDoc);
        syncCanonical(nextDoc, targetUrlObj ? targetUrlObj.toString() : window.location.href);
        syncDesktopNav(nextDoc);
        syncMobileNav(nextDoc, targetUrlObj ? targetUrlObj.toString() : window.location.href);

        var pageUrl = targetUrlObj ? targetUrlObj.toString() : window.location.href;
        if (mode === "push") {
            window.history.pushState({ hansjackPjax: true, url: pageUrl, scrollY: 0 }, "", pageUrl);
        } else if (mode === "replace") {
            window.history.replaceState({ hansjackPjax: true, url: pageUrl, scrollY: 0 }, "", pageUrl);
        }

        scrollForNavigation(targetUrlObj, stateScrollY);

        dispatchPjaxEvent("hansjack:pjax:after", {
            url: pageUrl,
            listPage: true,
            mode: mode
        });

        return true;
    }

    function navigate(rawUrl, mode, userInitiated, stateScrollY) {
        var targetUrlObj = parseUrl(rawUrl);
        if (!targetUrlObj) {
            return;
        }

        var fullTarget = targetUrlObj.toString();
        var fetchUrl = normalizeFetchUrl(targetUrlObj);
        if (!fetchUrl) {
            hardNavigate(fullTarget);
            return;
        }

        navToken += 1;
        var token = navToken;

        fetchPageHtml(fetchUrl, token).then(function (html) {
            if (!html || token !== navToken) {
                return;
            }
            var nextDoc = parseHtml(html);
            if (!renderPage(nextDoc, targetUrlObj, mode, stateScrollY)) {
                if (userInitiated) {
                    hardNavigate(fullTarget);
                }
            }
        }).catch(function () {
            if (token !== navToken) {
                return;
            }
            if (userInitiated) {
                hardNavigate(fullTarget);
            }
        });
    }

    function shouldIgnoreLink(link, event) {
        if (!link || !link.getAttribute) {
            return true;
        }
        if (!isCurrentListPage()) {
            return true;
        }
        if (event && (event.defaultPrevented || event.button !== 0)) {
            return true;
        }
        if (event && (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey)) {
            return true;
        }

        var target = String(link.getAttribute("target") || "").trim();
        if (target && target !== "_self") {
            return true;
        }
        if (link.hasAttribute("download")) {
            return true;
        }
        if (link.closest("[data-no-pjax]")) {
            return true;
        }
        if (link.hasAttribute("data-no-pjax")) {
            return true;
        }

        var href = String(link.getAttribute("href") || "").trim();
        if (!href) {
            return true;
        }
        var lower = href.toLowerCase();
        if (lower.indexOf("javascript:") === 0 || lower.indexOf("mailto:") === 0 || lower.indexOf("tel:") === 0) {
            return true;
        }

        var urlObj = parseUrl(href);
        if (!urlObj) {
            return true;
        }
        if (urlObj.origin !== window.location.origin) {
            return true;
        }

        var current = parseUrl(window.location.href);
        if (!current) {
            return true;
        }

        // Only enable PJAX for list-navigation links to avoid delaying post/detail jumps.
        var isListNavLink = false;
        if (link.classList && link.classList.contains("posts-title")) {
            return true;
        }
        if (link.closest(".posts-pager, .page-navigator")) {
            isListNavLink = true;
        } else if (
            link.classList &&
            (
                link.classList.contains("posts-tag") ||
                link.classList.contains("posts-tag-pill") ||
                link.classList.contains("posts-link")
            )
        ) {
            isListNavLink = true;
        } else {
            var navKey = String(link.getAttribute("data-nav-key") || "").trim().toLowerCase();
            if (navKey === "blog" || navKey === "memo") {
                isListNavLink = true;
            }
            if (!isListNavLink && link.closest(".nav-dropdown") && link.classList && link.classList.contains("user-dropdown-item")) {
                isListNavLink = true;
            }
        }
        if (!isListNavLink) {
            return true;
        }

        var samePathAndQuery =
            urlObj.pathname === current.pathname &&
            urlObj.search === current.search;

        if (samePathAndQuery) {
            // Keep normal browser behavior for hash-only jumps.
            return true;
        }

        return false;
    }

    function prefetch(rawUrl) {
        var urlObj = parseUrl(rawUrl);
        if (!urlObj || urlObj.origin !== window.location.origin) {
            return;
        }
        if (!isCurrentListPage()) {
            return;
        }
        var fetchUrl = normalizeFetchUrl(urlObj);
        if (!fetchUrl || cacheGet(fetchUrl) || pendingPrefetch.has(fetchUrl)) {
            return;
        }

        pendingPrefetch.add(fetchUrl);
        window.fetch(fetchUrl, {
            method: "GET",
            credentials: "same-origin",
            cache: "no-store",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "text/html"
            }
        }).then(function (response) {
            if (!response || !response.ok) {
                throw new Error("prefetch failed");
            }
            return response.text();
        }).then(function (html) {
            cacheSet(fetchUrl, html);
        }).catch(function () {
            // Ignore prefetch errors.
        }).finally(function () {
            pendingPrefetch.delete(fetchUrl);
        });
    }

    document.addEventListener("click", function (e) {
        var target = e && e.target ? e.target : null;
        if (!target || !target.closest) {
            return;
        }

        var link = target.closest("a[href]");
        if (!link || shouldIgnoreLink(link, e)) {
            return;
        }

        e.preventDefault();
        updateHistoryStateScroll();
        navigate(link.getAttribute("href") || "", "push", true, 0);
    });

    document.addEventListener("mouseover", function (e) {
        var target = e && e.target ? e.target : null;
        if (!target || !target.closest) {
            return;
        }
        var link = target.closest("a[href]");
        if (!link || shouldIgnoreLink(link, null)) {
            return;
        }
        prefetch(link.getAttribute("href") || "");
    });

    document.addEventListener("touchstart", function (e) {
        var target = e && e.target ? e.target : null;
        if (!target || !target.closest) {
            return;
        }
        var link = target.closest("a[href]");
        if (!link || shouldIgnoreLink(link, null)) {
            return;
        }
        prefetch(link.getAttribute("href") || "");
    }, { passive: true });

    window.addEventListener("popstate", function (e) {
        var state = e && e.state ? e.state : null;
        if (!state || state.hansjackPjax !== true || !state.url) {
            return;
        }
        if (!isCurrentListPage()) {
            hardNavigate(String(state.url || window.location.href));
            return;
        }
        navigate(String(state.url || ""), "pop", true, state.scrollY || 0);
    });

    if (isCurrentListPage()) {
        try {
            window.history.replaceState({
                hansjackPjax: true,
                url: window.location.href,
                scrollY: Math.max(0, Math.floor(window.pageYOffset || document.documentElement.scrollTop || 0))
            }, "", window.location.href);
        } catch (e) {}
    }
})();
