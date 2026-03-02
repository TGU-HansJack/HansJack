/* HansJack Image Viewer */
(function () {
    if (window.HansJackImageViewerLoaded) {
        return;
    }
    window.HansJackImageViewerLoaded = true;

    var roots = Array.prototype.slice.call(
        document.querySelectorAll(".hj-article-content, .hj-comment-content")
    );
    if (!roots || roots.length === 0) {
        return;
    }

    var htmlRoot = document.documentElement;
    var openClass = "hj-image-viewer-open";
    var viewer = null;
    var viewerImage = null;
    var closeButton = null;
    var lastActiveElement = null;

    function isImageUrl(url) {
        var text = String(url || "").trim();
        if (!text) {
            return false;
        }
        if (/^data:image\//i.test(text)) {
            return true;
        }
        return /\.(?:avif|bmp|gif|ico|jpe?g|png|svg|webp)(?:[?#].*)?$/i.test(text);
    }

    function getPreviewSource(img) {
        if (!img) {
            return "";
        }

        var src = "";
        try {
            src = String(img.currentSrc || "").trim();
        } catch (e) {
            src = "";
        }

        if (!src) {
            try {
                src = String(img.getAttribute("src") || "").trim();
            } catch (e) {
                src = "";
            }
        }

        var parentLink = img.closest ? img.closest("a[href]") : null;
        if (parentLink) {
            var href = "";
            try {
                href = String(parentLink.getAttribute("href") || "").trim();
            } catch (e) {
                href = "";
            }
            if (isImageUrl(href)) {
                return href;
            }
        }

        return src;
    }

    function isPreviewableImage(img) {
        if (!img || !img.tagName || String(img.tagName).toUpperCase() !== "IMG") {
            return false;
        }
        if (img.closest && img.closest(".hj-excalidraw-block")) {
            return false;
        }
        if (img.closest && img.closest(".hj-img-viewer")) {
            return false;
        }
        return getPreviewSource(img) !== "";
    }

    function closeViewer() {
        if (!viewer || !viewer.classList) {
            return;
        }
        try {
            viewer.classList.remove("is-open");
            viewer.setAttribute("aria-hidden", "true");
        } catch (e) {}

        if (htmlRoot && htmlRoot.classList) {
            try {
                htmlRoot.classList.remove(openClass);
            } catch (e) {}
        }

        if (viewerImage) {
            try {
                viewerImage.removeAttribute("src");
                viewerImage.removeAttribute("srcset");
                viewerImage.alt = "";
            } catch (e) {}
        }

        var restoreTarget = lastActiveElement;
        lastActiveElement = null;
        if (restoreTarget && typeof restoreTarget.focus === "function") {
            try {
                restoreTarget.focus({ preventScroll: true });
            } catch (e) {
                try {
                    restoreTarget.focus();
                } catch (e2) {}
            }
        }
    }

    function ensureViewer() {
        if (viewer) {
            return;
        }

        viewer = document.createElement("div");
        viewer.className = "hj-img-viewer";
        viewer.setAttribute("aria-hidden", "true");

        var shell = document.createElement("div");
        shell.className = "hj-img-viewer-shell";

        var frame = document.createElement("div");
        frame.className = "hj-img-viewer-frame";

        closeButton = document.createElement("button");
        closeButton.type = "button";
        closeButton.className = "hj-img-viewer-close";
        closeButton.setAttribute("aria-label", "关闭预览");
        closeButton.innerHTML =
            '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';
        closeButton.addEventListener("click", function (e) {
            if (e && e.preventDefault) {
                e.preventDefault();
            }
            if (e && e.stopPropagation) {
                e.stopPropagation();
            }
            closeViewer();
        });

        viewerImage = document.createElement("img");
        viewerImage.className = "hj-img-viewer-image";
        viewerImage.decoding = "async";
        viewerImage.loading = "eager";

        frame.appendChild(viewerImage);
        shell.appendChild(frame);
        shell.appendChild(closeButton);
        viewer.appendChild(shell);

        viewer.addEventListener("click", function (e) {
            if (e && e.target === viewer) {
                closeViewer();
            }
        });

        document.addEventListener("keydown", function (e) {
            if (!viewer || !viewer.classList || !viewer.classList.contains("is-open")) {
                return;
            }
            var key = e && e.key ? String(e.key) : "";
            if (key === "Escape" || key === "Esc") {
                if (e && e.preventDefault) {
                    e.preventDefault();
                }
                closeViewer();
            }
        });

        document.body.appendChild(viewer);
    }

    function openViewer(img) {
        var src = getPreviewSource(img);
        if (!src) {
            return;
        }

        ensureViewer();
        if (!viewer || !viewerImage) {
            return;
        }

        lastActiveElement = img || null;

        var alt = "";
        try {
            alt = String(img.getAttribute("alt") || "").trim();
        } catch (e) {
            alt = "";
        }

        try {
            viewerImage.alt = alt;
            viewerImage.src = src;
            viewer.classList.add("is-open");
            viewer.setAttribute("aria-hidden", "false");
        } catch (e) {}

        if (htmlRoot && htmlRoot.classList) {
            try {
                htmlRoot.classList.add(openClass);
            } catch (e) {}
        }

        if (closeButton && typeof closeButton.focus === "function") {
            try {
                closeButton.focus({ preventScroll: true });
            } catch (e) {
                try {
                    closeButton.focus();
                } catch (e2) {}
            }
        }
    }

    function markImageTarget(img) {
        if (!img || !img.classList || !isPreviewableImage(img)) {
            return;
        }
        if (img.dataset && img.dataset.hjImageViewerBound === "1") {
            return;
        }

        if (img.dataset) {
            img.dataset.hjImageViewerBound = "1";
        }
        img.classList.add("hj-image-viewer-target");
        if (!img.hasAttribute("tabindex")) {
            img.setAttribute("tabindex", "0");
        }
        if (!img.hasAttribute("role")) {
            img.setAttribute("role", "button");
        }
        if (!img.hasAttribute("aria-label")) {
            img.setAttribute("aria-label", "查看大图");
        }

        img.addEventListener("keydown", function (e) {
            var key = e && e.key ? String(e.key) : "";
            if (key !== "Enter" && key !== " " && key !== "Spacebar") {
                return;
            }
            if (e && e.preventDefault) {
                e.preventDefault();
            }
            openViewer(img);
        });
    }

    function markImageTargets(root) {
        if (!root) {
            return;
        }

        if (root.nodeType === 1 && String(root.tagName || "").toUpperCase() === "IMG") {
            markImageTarget(root);
            return;
        }

        if (!root.querySelectorAll) {
            return;
        }

        var imgs = root.querySelectorAll("img");
        for (var i = 0; i < imgs.length; i++) {
            markImageTarget(imgs[i]);
        }
    }

    for (var r = 0; r < roots.length; r++) {
        markImageTargets(roots[r]);
    }

    if (typeof MutationObserver !== "undefined") {
        for (var m = 0; m < roots.length; m++) {
            (function (root) {
                try {
                    var observer = new MutationObserver(function (records) {
                        for (var i = 0; i < records.length; i++) {
                            var record = records[i];
                            var added = record && record.addedNodes ? record.addedNodes : [];
                            for (var j = 0; j < added.length; j++) {
                                var node = added[j];
                                if (!node || node.nodeType !== 1) {
                                    continue;
                                }
                                markImageTargets(node);
                            }
                        }
                    });
                    observer.observe(root, { childList: true, subtree: true });
                } catch (e) {}
            })(roots[m]);
        }
    }

    document.addEventListener(
        "click",
        function (e) {
            if (!e || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
                return;
            }
            var target = e.target;
            if (!target || !target.closest) {
                return;
            }
            var img = target.closest(".hj-article-content img, .hj-comment-content img");
            if (!isPreviewableImage(img)) {
                return;
            }

            var parentLink = img.closest ? img.closest("a[href]") : null;
            if (parentLink && parentLink.contains(img) && e.preventDefault) {
                e.preventDefault();
            }

            openViewer(img);
        },
        true
    );
})();
