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
const expected = ['.nojekyll', 'CNAME', 'fonts/Jost-400-Book.otf', 'fonts/Jost-600-Semi.otf', 'fonts/OFL.txt', 'hackathon/mcp-proof.json', 'hackathon/design-proof.json', 'hackathon/level-preview.png', 'hackathon/actual-demo.mp4', 'hackathon/workshop-fr.mp4', 'index.html', 'logo-darkwood.png', 'style.css', 'themes.js'];
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

const design = JSON.parse(await readFile(resolve(output, 'hackathon/design-proof.json'), 'utf8'));
assert.equal(design.execution.mode, 'real_ai');
assert.equal(design.execution.agent, 'level_designer');
assert.equal(design.execution.tool_calls.length, 1);
assert.equal(design.execution.tool_calls[0].tool, 'generate_original_level');
assert.equal(design.execution.tool_calls[0].result.mode, 'real_ai');
assert.ok(design.execution.tool_calls[0].arguments.prompt.length <= 2000);
assert.match(design.public_source_commit, /^[0-9a-f]{40}$/);
assert.equal(design.versions['mcp/sdk'].replace(/^v/, ''), '0.10.0');
assert.ok(html.includes(design.execution.tool_calls[0].result.level.name));
console.log('Validated current designer → official SDK MCP proof, source commit and model result.');
