/* HansJack Image Viewer Loader */
(function () {
    if (window.HansJackImageViewerLoaderLoaded) {
        return;
    }
    window.HansJackImageViewerLoaderLoaded = true;

    var currentScript = document.currentScript;
    if (!currentScript) {
        currentScript = document.querySelector("script[data-hj-image-viewer-loader]");
    }
    if (!currentScript) {
        return;
    }

    var cssUrl = String(currentScript.getAttribute("data-hj-image-viewer-css") || "").trim();
    var jsUrl = String(currentScript.getAttribute("data-hj-image-viewer-js") || "").trim();
    if (!cssUrl || !jsUrl) {
        return;
    }

    var contents = Array.prototype.slice.call(
        document.querySelectorAll(".hj-article-content, .hj-comment-content")
    );
    if (!contents || contents.length === 0) {
        return;
    }

    function appendCss(href) {
        if (!href) {
            return;
        }
        if (document.querySelector("link[data-hj-image-viewer-css]")) {
            return;
        }
        try {
            var link = document.createElement("link");
            link.rel = "stylesheet";
            link.href = href;
            link.setAttribute("data-hj-image-viewer-css", "1");
            document.head.appendChild(link);
        } catch (e) {}
    }

    function appendJs(src) {
        if (!src) {
            return;
        }
        var key = src.replace(/[^a-z0-9]/gi, "_");
        var selector = 'script[data-hj-image-viewer-js="' + key + '"]';
        if (document.querySelector(selector)) {
            return;
        }
        try {
            var script = document.createElement("script");
            script.src = src;
            script.defer = true;
            script.setAttribute("data-hj-image-viewer-js", key);
            document.head.appendChild(script);
        } catch (e) {}
    }

    function loadAssets() {
        appendCss(cssUrl);
        appendJs(jsUrl);
    }

    function hasAnyImage(nodes) {
        for (var i = 0; i < nodes.length; i++) {
            var content = nodes[i];
            if (content && content.querySelector && content.querySelector("img")) {
                return true;
            }
        }
        return false;
    }

    if (hasAnyImage(contents)) {
        loadAssets();
        return;
    }

    if (typeof MutationObserver === "undefined") {
        return;
    }

    var loaded = false;
    var observers = [];

    function safeLoadOnce() {
        if (loaded) {
            return;
        }
        loaded = true;
        for (var i = 0; i < observers.length; i++) {
            try {
                observers[i].disconnect();
            } catch (e) {}
        }
        observers = [];
        loadAssets();
    }

    for (var c = 0; c < contents.length; c++) {
        (function (content) {
            if (!content) {
                return;
            }
            try {
                var observer = new MutationObserver(function (records) {
                    for (var r = 0; r < records.length; r++) {
                        var record = records[r];
                        var added = record && record.addedNodes ? record.addedNodes : [];
                        for (var n = 0; n < added.length; n++) {
                            var node = added[n];
                            if (!node || node.nodeType !== 1) {
                                continue;
                            }
                            if (
                                (node.tagName && String(node.tagName).toUpperCase() === "IMG") ||
                                (node.querySelector && node.querySelector("img"))
                            ) {
                                safeLoadOnce();
                                return;
                            }
                        }
                    }
                });
                observer.observe(content, { childList: true, subtree: true });
                observers.push(observer);
            } catch (e) {}
        })(contents[c]);
    }
})();
