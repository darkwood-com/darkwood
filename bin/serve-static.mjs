import { createServer } from 'node:http';
import { readFile, realpath, stat } from 'node:fs/promises';
import { resolve, extname, sep } from 'node:path';

const root = await realpath(new URL('../dist', import.meta.url));
const prefix = (process.argv[2] || '').replace(/\/$/, '');
if (prefix && !/^\/[\w-]+(?:\/[\w-]+)*$/.test(prefix)) throw new Error('Invalid URL prefix');
const port = Number(process.argv[3] || 8098);
const types = { '.html': 'text/html; charset=utf-8', '.css': 'text/css', '.js': 'text/javascript', '.png': 'image/png', '.otf': 'font/otf', '.txt': 'text/plain' };
createServer(async (req, res) => {
  try {
    const url = new URL(req.url, 'http://127.0.0.1');
    if (prefix && url.pathname !== prefix && !url.pathname.startsWith(prefix + '/')) throw new Error('Not found');
    const path = decodeURIComponent(url.pathname.slice(prefix.length)) || '/';
    let file = resolve(root, '.' + path);
    if (file !== root && !file.startsWith(root + sep)) throw new Error('Not found');
    if ((await stat(file)).isDirectory()) {
      if (!url.pathname.endsWith('/')) { res.writeHead(301, { Location: url.pathname + '/' + url.search }); res.end(); return; }
      file = resolve(file, 'index.html');
    }
    file = await realpath(file);
    if (!file.startsWith(root + sep)) throw new Error('Not found');
    const body = await readFile(file);
    res.writeHead(200, { 'Content-Type': types[extname(file)] || 'application/octet-stream', 'X-Content-Type-Options': 'nosniff', 'Cache-Control': 'no-store' });
    res.end(req.method === 'HEAD' ? undefined : body);
  } catch { res.writeHead(404); res.end('Not found'); }
}).listen(port, '127.0.0.1', () => console.log(`Static preview: http://127.0.0.1:${port}${prefix}/`));
