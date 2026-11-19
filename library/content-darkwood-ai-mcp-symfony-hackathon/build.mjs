import { copyFile, lstat, mkdir, readdir, writeFile, readFile } from 'node:fs/promises';
import { resolve, dirname } from 'node:path';

const card = import.meta.dirname;
const output = resolve(card, 'dist');
const assets = ['style.css', 'themes.js', 'logo-darkwood.png', 'fonts/Jost-400-Book.otf', 'fonts/Jost-600-Semi.otf', 'fonts/OFL.txt', 'hackathon/experience-edit.json', 'hackathon/experience-poster.jpg', 'hackathon/darkwood-game-proof.json', 'hackathon/mcp-proof.json', 'hackathon/design-proof.json', 'hackathon/level-preview.png'];
const allowed = new Set(['index.html', '404.html', 'CNAME', '.nojekyll', ...assets]);

// Refuse unexpected output or symlinks rather than deleting user files.
async function inspect(directory, prefix = '') {
  let entries;
  try { entries = await readdir(directory, { withFileTypes: true }); }
  catch (error) { if (error.code === 'ENOENT') return; throw error; }
  for (const entry of entries) {
    const name = prefix + entry.name;
    if (entry.isSymbolicLink()) throw new Error(`Symlink refused: ${name}`);
    if (entry.isDirectory()) await inspect(resolve(directory, entry.name), `${name}/`);
    else if (!entry.isFile() || !allowed.has(name)) throw new Error(`Unexpected output: ${name}`);
  }
}
try { if ((await lstat(output)).isSymbolicLink()) throw new Error('dist/ must not be a symlink'); }
catch (error) { if (error.code !== 'ENOENT') throw error; }
await inspect(output);
for (const [source, target] of [['src/index.html', 'index.html'], ['src/404.html', '404.html'], ['CNAME', 'CNAME'], ...assets.map(asset => [`public/${asset}`, asset])]) {
  // Check every source component, including its parent directories.
  let component = card;
  for (const part of source.split('/')) {
    component = resolve(component, part);
    if ((await lstat(component)).isSymbolicLink()) throw new Error(`Symlink refused: ${source}`);
  }
  const destination = resolve(output, target);
  await mkdir(dirname(destination), { recursive: true });
  if (source === 'src/index.html') {
    const helper = await readFile(resolve(card, '../darkwood-design/youtube.js'), 'utf8');
    const html = await readFile(resolve(card, source), 'utf8');
    if (!html.includes('<!-- darkwood-youtube-helper -->')) throw new Error('Missing shared player marker');
    await writeFile(destination, html.replace('<!-- darkwood-youtube-helper -->', `<script>${helper}</script>`));
  } else await copyFile(resolve(card, source), destination);
}
await writeFile(resolve(output, '.nojekyll'), '');
console.log('Exported workshop from this card only: dist/ (2 pages, 12 assets, CNAME, .nojekyll).');
