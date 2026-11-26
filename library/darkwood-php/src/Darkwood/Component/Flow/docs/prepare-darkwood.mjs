import { copyFile, cp, mkdir } from 'node:fs/promises';
import { resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const here = fileURLToPath(new URL('.', import.meta.url));
const bundle = process.env.DARKWOOD_BUNDLE_DIR ?? resolve(here, '../../../Bundle/DarkwoodBundle');
const site = resolve(here, 'src');

await mkdir(resolve(site, 'data'), { recursive: true });
await copyFile(resolve(bundle, 'data/identity.json'), resolve(site, 'data/darkwood_identity.json'));
await cp(resolve(bundle, 'assets'), resolve(site, 'static/darkwood'), { recursive: true });
