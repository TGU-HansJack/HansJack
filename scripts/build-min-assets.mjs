import path from "node:path";
import { fileURLToPath } from "node:url";
import { build } from "esbuild";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const themeRoot = path.resolve(__dirname, "..");

const jsEntries = [
  "assets/js/content-static.js",
  "assets/js/footer-global-pre.js",
  "assets/js/footer-global-tail.js",
  "assets/js/pjax-lite.js"
];

const cssEntries = ["style.css"];

async function minifyJs() {
  for (const rel of jsEntries) {
    const src = path.join(themeRoot, rel);
    const out = path.join(
      themeRoot,
      rel.replace(/\.js$/i, ".min.js")
    );

    await build({
      entryPoints: [src],
      outfile: out,
      bundle: false,
      minify: true,
      format: "iife",
      target: ["es2018"],
      legalComments: "none"
    });
    console.log(`[minify] JS: ${rel} -> ${path.relative(themeRoot, out)}`);
  }
}

async function minifyCss() {
  for (const rel of cssEntries) {
    const src = path.join(themeRoot, rel);
    const out = path.join(
      themeRoot,
      rel.replace(/\.css$/i, ".min.css")
    );

    await build({
      entryPoints: [src],
      outfile: out,
      bundle: false,
      minify: true,
      legalComments: "none",
      loader: { ".css": "css" },
      target: ["es2018"]
    });
    console.log(`[minify] CSS: ${rel} -> ${path.relative(themeRoot, out)}`);
  }
}

async function run() {
  await minifyJs();
  await minifyCss();
  console.log("[minify] done");
}

run().catch((err) => {
  console.error("[minify] failed", err);
  process.exitCode = 1;
});
