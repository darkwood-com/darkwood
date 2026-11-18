import assert from 'node:assert/strict';
import { readFile, stat, readdir } from 'node:fs/promises';
import { resolve } from 'node:path';

const output = resolve(import.meta.dirname, 'dist');
const domain = (await readFile(resolve(import.meta.dirname, 'CNAME'), 'utf8')).trim();
const base = `https://${domain}/`;
const html = await readFile(resolve(output, 'index.html'), 'utf8');
assert.equal((await readFile(resolve(output, 'CNAME'), 'utf8')).trim(), domain);
assert.ok(html.includes('<h1>Darkwood Symfony AI MCP Hackathon</h1>'));
assert.ok(html.includes(`<link rel="canonical" href="${base}">`));
assert.ok(html.includes(`<meta property="og:url" content="${base}">`));
assert.ok(html.includes('library/content-darkwood-ai-mcp-symfony-hackathon'));
assert.ok(!html.includes('content-darkwood-intent'));
const selection = html.split('id="selection"')[1].split('</section>')[0];
assert.equal([...selection.matchAll(/<article\b/g)].length, 6);
for (const [, raw] of html.matchAll(/(?:href|src)="([^"]+)"/g)) {
  if (raw.startsWith('#')) {
    assert.ok(html.includes(`id="${raw.slice(1)}"`), `Missing anchor ${raw}`);
    continue;
  }
  const url = new URL(raw.replaceAll('&amp;', '&'), base);
  assert.notEqual(url.protocol, 'http:', `Insecure URL ${raw}`);
  if (url.origin !== new URL(base).origin) continue;
  const path = url.pathname === '/' ? '/index.html' : url.pathname;
  assert.ok((await stat(resolve(output, `.${decodeURIComponent(path)}`))).isFile(), `Missing resource ${path}`);
}
const css = await readFile(resolve(output, 'style.css'), 'utf8');
for (const [, raw] of css.matchAll(/url\(['"]?([^)'"\s]+)['"]?\)/g)) {
  assert.ok((await stat(resolve(output, raw))).isFile(), `Missing CSS asset ${raw}`);
}
const expected = ['.nojekyll', 'CNAME', 'fonts/Jost-400-Book.otf', 'fonts/Jost-600-Semi.otf', 'fonts/OFL.txt', 'hackathon/mcp-proof.json', 'index.html', 'logo-darkwood.png', 'style.css', 'themes.js'];
async function files(directory, prefix = '') {
  const result = [];
  for (const entry of await readdir(directory, { withFileTypes: true })) {
    assert.ok(!entry.isSymbolicLink(), `Symlink ${prefix + entry.name}`);
    if (entry.isDirectory()) result.push(...await files(resolve(directory, entry.name), `${prefix}${entry.name}/`));
    else result.push(prefix + entry.name);
  }
  return result;
}
assert.deepEqual((await files(output)).sort(), expected.sort());
const proof = JSON.parse(await readFile(resolve(output, 'hackathon/mcp-proof.json'), 'utf8'));
assert.equal(proof[3].result.structuredContent.mode, 'real_ai');
assert.equal(proof[3].result.isError, false);
console.log('Validated workshop: 6 qualified cards, anchors, assets, canonical/Open Graph, real AI evidence and public-only file whitelist.');
