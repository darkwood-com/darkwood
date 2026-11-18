import assert from "node:assert/strict";
import { readFile, stat } from "node:fs/promises";
import { resolve } from "node:path";

const root = resolve(import.meta.dirname, "../..");
const output = resolve(root, process.argv[2] ?? "library/pages/dist");
const domain = (await readFile(resolve(root, "library/pages/CNAME"), "utf8")).trim();
const base = `https://${domain}/`;
const catalog = JSON.parse(await readFile(resolve(root, "tags.json"), "utf8"));
const published = JSON.parse(await readFile(resolve(output, "data/cards.json"), "utf8"));
assert.equal((await readFile(resolve(output, "CNAME"), "utf8")).trim(), domain);
assert.deepEqual(published.map(({ id }) => id).sort(), catalog.projects.map(({ id }) => id).sort());

const sitemap = await readFile(resolve(output, "sitemap.xml"), "utf8");
const expectedUrls = [base, ...published.map(({ id }) => `${base}cards/${encodeURIComponent(id)}/`)];
assert.deepEqual([...sitemap.matchAll(/<loc>(.*?)<\/loc>/g)].map((match) => match[1]).sort(), expectedUrls.sort());
assert.equal(await readFile(resolve(output, "robots.txt"), "utf8"), `User-agent: *\nAllow: /\nSitemap: ${base}sitemap.xml\n`);

const resourcePaths = new Set();
for (const url of expectedUrls) {
  const path = new URL(url).pathname;
  const html = await readFile(resolve(output, `.${path}`, "index.html"), "utf8");
  assert.ok(html.includes(`<link rel="canonical" href="${url}">`), `Canonical: ${url}`);
  assert.ok(html.includes(`<meta property="og:url" content="${url}">`), `Open Graph: ${url}`);
  assert.ok(!html.includes("https://darkwood-com.github.io/darkwood/"), `Old base: ${url}`);
  for (const [, raw] of html.matchAll(/(?:href|src)="([^"]+)"/g)) {
    if (raw.startsWith("#")) continue;
    const link = new URL(raw.replaceAll("&amp;", "&"), url);
    assert.notEqual(link.protocol, "http:", `Insecure link: ${raw}`);
    if (link.origin !== new URL(base).origin) continue;
    assert.ok(!link.pathname.startsWith("/darkwood/"), `Old prefix: ${raw}`);
    resourcePaths.add(link.pathname);
  }
}
for (const path of resourcePaths) {
  const target = resolve(output, `.${decodeURIComponent(path)}`, ...(path.endsWith("/") ? ["index.html"] : []));
  assert.ok((await stat(target)).isFile(), `Missing internal target: ${path}`);
}
const css = await readFile(resolve(output, "assets/catalog.css"), "utf8");
for (const [, font] of css.matchAll(/url\(["']?([^)'"\s]+)["']?\)/g)) {
  const url = new URL(font, `${base}assets/catalog.css`);
  assert.equal(url.origin, new URL(base).origin);
  assert.ok((await stat(resolve(output, `.${url.pathname}`))).isFile(), `Missing CSS asset: ${font}`);
}
console.log(`Validated ${published.length} cards, canonical/Open Graph URLs, sitemap, robots and ${resourcePaths.size} internal targets.`);
