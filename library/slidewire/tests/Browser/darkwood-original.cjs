// Run inside nix develop. Requires Playwright and an already running local server.
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');

const base = process.env.SLIDEWIRE_URL || 'http://127.0.0.1:8017';
const output = path.resolve('storage/app/slide-renders/symfony-ai-in-action/revision-2');

(async () => {
    fs.mkdirSync(output, { recursive: true });
    const browser = await chromium.launch({
        executablePath: process.env.CHROME || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        headless: true,
    });
    try {
        const page = await browser.newPage({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 });
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        page.on('response', response => {
            if (response.status() >= 400) errors.push(`${response.status()} ${response.url()}`);
        });
        await page.goto(`${base}/slides/symfony-ai-in-action`);
        const frames = page.locator('.slidewire-frame');
        assert.equal(await frames.count(), 20);
        const report = [];
        for (let i = 0; i < 20; i++) {
            await page.goto(`${base}/slides/symfony-ai-in-action#/slide/${i + 1}`);
            await page.waitForFunction(index => document.querySelectorAll('.slidewire-frame')[index]?.classList.contains('is-active'), i);
            await page.evaluate(async () => {
                await document.fonts.ready;
                await Promise.all([...document.images].map(img => img.decode().catch(() => {})));
            });
            await page.waitForTimeout(250);
            const metrics = await frames.nth(i).evaluate(frame => {
                const style = node => getComputedStyle(node);
                const canvas = frame.querySelector('.dw-original-canvas');
                const footer = frame.querySelector('footer');
                const heading = frame.querySelector('.dw-original-heading');
                const bar = document.querySelector('.slidewire-progress-bar');
                const track = document.querySelector('.slidewire-progress');
                const boundary = footer.getBoundingClientRect().top - 20;
                const overlaps = [...canvas.children].filter(node => node !== footer && node.getBoundingClientRect().bottom > boundary).map(node => node.className);
                const clipped = [...frame.querySelectorAll('h1,h2,h3,p,li,pre,.dw-original-process-step')].filter(node => node.scrollWidth > node.clientWidth + 1).map(node => node.className || node.tagName);
                return {
                    pagination: frame.querySelector('.dw-original-pagination').textContent,
                    progress: { width: bar.getBoundingClientRect().width, color: style(bar).backgroundColor, image: style(bar).backgroundImage, opacity: style(bar).opacity, height: track.getBoundingClientRect().height, track: style(track).backgroundColor },
                    logo: [...frame.querySelectorAll('img')].map(img => ({ src: new URL(img.src).pathname, loaded: img.complete && img.naturalWidth === 512 && img.naturalHeight === 512, width: img.width, height: img.height })),
                    heading: heading ? { color: style(heading).color, align: style(heading).textAlign, top: heading.getBoundingClientRect().top, center: heading.getBoundingClientRect().x + heading.getBoundingClientRect().width / 2 } : null,
                    font: document.fonts.check('16px Jost'),
                    overflow: frame.scrollHeight > frame.clientHeight + 1 || frame.scrollWidth > frame.clientWidth + 1,
                    overlaps, clipped,
                };
            });
            assert.equal(metrics.pagination, `${String(i + 1).padStart(2, '0')} | 20`);
            assert.ok(Math.abs(metrics.progress.width - (i + 1) * 72) < 1);
            assert.equal(metrics.progress.color, 'rgb(53, 121, 246)');
            assert.equal(metrics.progress.image, 'none');
            assert.equal(metrics.progress.opacity, '1');
            assert.equal(metrics.progress.height, 6);
            assert.equal(metrics.progress.track, 'rgb(255, 255, 255)');
            assert.ok(metrics.logo.every(logo => logo.loaded && logo.width === logo.height));
            if (metrics.heading) {
                assert.equal(metrics.heading.color, 'rgb(17, 17, 17)');
                assert.equal(metrics.heading.align, 'center');
                assert.ok(metrics.heading.top < 120);
                assert.ok(Math.abs(metrics.heading.center - 720) < 1);
            }
            assert.ok(metrics.font);
            assert.equal(metrics.overflow, false, `Slide ${i + 1} scrolls`);
            assert.deepEqual(metrics.overlaps, [], `Slide ${i + 1} overlaps the footer`);
            assert.deepEqual(metrics.clipped, [], `Slide ${i + 1} clips content`);
            await page.screenshot({ path: path.join(output, `slide-${String(i + 1).padStart(2, '0')}.png`) });
            report.push({ slide: i + 1, ...metrics });
        }
        await page.keyboard.press('ArrowLeft');
        await page.waitForFunction(() => document.querySelectorAll('.slidewire-frame')[18].classList.contains('is-active'));
        await page.keyboard.press('ArrowRight');
        await page.waitForFunction(() => document.querySelectorAll('.slidewire-frame')[19].classList.contains('is-active'));

        // Render actual Blade components independently of the pilot: black is an
        // available historical layout, not an extra slide in the presentation.
        const fixture = execFileSync('php', ['tests/fixtures/render-darkwood-original.php'], { encoding: 'utf8', env: { ...process.env, SLIDEWIRE_URL: base } });
        await page.route(`${base}/__theme-check`, route => route.fulfill({ contentType: 'text/html; charset=utf-8', body: `<meta charset="utf-8">${fixture}` }));
        await page.goto(`${base}/__theme-check`);
        const manifest = JSON.parse(fs.readFileSync('public/build/manifest.json', 'utf8'));
        await page.addStyleTag({ url: `${base}/build/${manifest['resources/css/app.css'].file}` });
        await page.evaluate(() => document.fonts.ready);
        await page.evaluate(async () => {
            await Promise.all([...document.images].map(img => img.decode()));
        });
        const variants = page.locator('.dw-original-slide');
        const logos = ['blue', 'light', 'dark'];
        for (let i = 0; i < 3; i++) {
            const logo = variants.nth(i).locator('footer img');
            assert.ok((await logo.getAttribute('src')).endsWith(`dw512x512-uni-${logos[i]}.png`));
            assert.ok(await logo.evaluate(img => img.complete && img.naturalWidth === 512 && img.naturalHeight === 512));
            await variants.nth(i).screenshot({ path: path.join(output, `check-${['blue', 'black', 'lists'][i]}.png`) });
        }
        const lists = await page.locator('.dw-original-slide--content ul').evaluateAll(nodes => nodes.map(node => getComputedStyle(node).listStyleType));
        assert.deepEqual(lists, ['disc', 'circle', 'circle', 'disc', 'circle', 'circle']);
        assert.deepEqual(errors, []);
        fs.writeFileSync(path.join(output, 'validation.json'), JSON.stringify({ viewport: [1440, 900], errors, lists, slides: report }, null, 2));
        console.log(`Validated and captured ${report.length} slides + 3 theme checks: ${output}`);
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
