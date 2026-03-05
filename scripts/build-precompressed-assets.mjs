import fs from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";
import { gzipSync, brotliCompressSync, constants as zlibConstants } from "node:zlib";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const themeRoot = path.resolve(__dirname, "..");

const targets = [
  "style.css",
  "style.min.css",
  "assets/js/content-static.js",
  "assets/js/content-static.min.js",
  "assets/js/footer-global-pre.js",
  "assets/js/footer-global-pre.min.js",
  "assets/js/footer-global-tail.js",
  "assets/js/footer-global-tail.min.js",
  "assets/js/pjax-lite.js",
  "assets/js/pjax-lite.min.js"
];

async function exists(filePath) {
  try {
    const stat = await fs.stat(filePath);
    return stat.isFile();
  } catch {
    return false;
  }
}

async function writeCompressed(fullPath) {
  const input = await fs.readFile(fullPath);

  const gzipBuffer = gzipSync(input, {
    level: 9
  });
  await fs.writeFile(fullPath + ".gz", gzipBuffer);

  const brBuffer = brotliCompressSync(input, {
    params: {
      [zlibConstants.BROTLI_PARAM_MODE]: zlibConstants.BROTLI_MODE_TEXT,
      [zlibConstants.BROTLI_PARAM_QUALITY]: 11,
      [zlibConstants.BROTLI_PARAM_SIZE_HINT]: input.length
    }
  });
  await fs.writeFile(fullPath + ".br", brBuffer);
}

async function run() {
  for (const rel of targets) {
    const full = path.join(themeRoot, rel);
    if (!(await exists(full))) {
      console.log(`[compress] skip missing: ${rel}`);
      continue;
    }

    await writeCompressed(full);
    console.log(`[compress] ok: ${rel} -> .gz/.br`);
  }
  console.log("[compress] done");
}

run().catch((err) => {
  console.error("[compress] failed", err);
  process.exitCode = 1;
});
