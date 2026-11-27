// Run inside nix develop. Requires Playwright and an already running local server.
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs');
const path = require('node:path');
const assert = require('node:assert/strict');
const { auditDarkwoodPalette } = require('../../bin/browser/palette.cjs');

const base = process.env.SLIDEWIRE_URL || 'http://127.0.0.1:8017';
const output = path.resolve(process.env.OUTPUT_DIR || 'storage/app/design-system/examples');
const specification = JSON.parse(fs.readFileSync('tests/fixtures/design-system-metrics.json', 'utf8'));
const roles = JSON.parse(fs.readFileSync('tests/fixtures/symfony-ai-headings.json', 'utf8'));
const spacing = JSON.parse(fs.readFileSync('tests/fixtures/darkwood-heading-spacing.json', 'utf8'));

(async () => {
    fs.mkdirSync(output, { recursive: true });
    const browser = await chromium.launch({
        executablePath: process.env.CHROME || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        headless: true,
        args: ['--force-device-scale-factor=2'],
    });
    try {
        const page = await browser.newPage({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 2 });
        const errors = [];
        const audits = [];
        const audit = async label => {
            const result = await page.locator('.slidewire-shell').evaluate(auditDarkwoodPalette);
            assert.deepEqual(result.violations, [], `${label}: palette violations`);
            audits.push({ label, ...result });
        };
        page.on('pageerror', error => errors.push(error.message));
        page.on('response', response => {
            if (response.status() >= 400) errors.push(`${response.status()} ${response.url()}`);
        });
        await page.goto(`${base}/slides/symfony-ai-in-action`);
        const frames = page.locator('.slidewire-frame');
        assert.equal(await frames.count(), 20);
        assert.equal(await page.locator('.slidewire-controls span, .slidewire-controls output, .sw-pagination').count(), 0);
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
                const canvas = frame.querySelector('.sw-canvas');
                const footer = frame.querySelector('footer');
                const heading = frame.querySelector('h1');
                const subtitle = frame.querySelector('h2');
                const overline = frame.querySelector('p.dw-overline');
                const typography = node => node ? { tag: node.tagName, text: node.textContent.trim(), size: parseFloat(style(node).fontSize), lineHeight: parseFloat(style(node).lineHeight), tracking: parseFloat(style(node).letterSpacing) || 0, weight: style(node).fontWeight, family: style(node).fontFamily, top: node.getBoundingClientRect().top, bottom: node.getBoundingClientRect().bottom, color: style(node).color } : null;
                const body = frame.querySelector('.sw-body');
                const inner = frame.querySelector('.sw-body-inner');
                const bar = document.querySelector('.slidewire-progress-bar');
                const track = document.querySelector('.slidewire-progress');
                const boundary = footer.getBoundingClientRect().top - 20;
                const overlaps = [...canvas.children].filter(node => node !== footer && node.getBoundingClientRect().bottom > boundary).map(node => node.className);
                const clipped = [...frame.querySelectorAll('h1,h2,h3,p,li,pre,.sw-process-step')].filter(node => node.scrollWidth > node.clientWidth + 1).map(node => node.className || node.tagName);
                return {
                    label: frame.querySelector('.sw-footer-label').textContent,
                    progress: { width: bar.getBoundingClientRect().width, color: style(bar).backgroundColor, image: style(bar).backgroundImage, opacity: style(bar).opacity, height: track.getBoundingClientRect().height, track: style(track).backgroundColor },
                    logo: [...frame.querySelectorAll('img')].map(img => ({ src: new URL(img.src).pathname, loaded: img.complete && img.naturalWidth === (img.matches('.sw-author-portrait') ? 640 : 512) && img.naturalHeight === (img.matches('.sw-author-portrait') ? 640 : 512), width: img.width, height: img.height })),
                    heading: heading ? { content: heading.classList.contains('sw-heading'), cover: heading.classList.contains('sw-title'), conclusion: heading.classList.contains('sw-conclusion-title'), color: style(heading).color, align: style(heading).textAlign, top: heading.getBoundingClientRect().top, bottom: heading.getBoundingClientRect().bottom, center: heading.getBoundingClientRect().x + heading.getBoundingClientRect().width / 2, size: parseFloat(style(heading).fontSize), lineHeight: parseFloat(style(heading).lineHeight), weight: style(heading).fontWeight, tracking: parseFloat(style(heading).letterSpacing), family: style(heading).fontFamily, text: heading.textContent.trim(), count: frame.querySelectorAll('h1').length } : null,
                    subtitle: typography(subtitle),
                    overline: typography(overline),
                    headerOrder: [...frame.querySelector('.sw-header').children].map(node => node.tagName),
                    body: body ? { center: (body.getBoundingClientRect().top + body.getBoundingClientRect().bottom) / 2, innerCenter: (inner.getBoundingClientRect().top + inner.getBoundingClientRect().bottom) / 2, scrolls: body.scrollHeight > body.clientHeight + 1 } : null,
                    code: [...frame.querySelectorAll('pre.phiki')].map(pre => ({ theme: pre.className, background: style(pre).backgroundColor, colors: [...new Set([...pre.querySelectorAll('.token')].map(token => style(token).color))] })),
                    nav: (() => { const nav = document.querySelector('.slidewire-controls'); return { background: style(nav).backgroundColor, border: style(nav).borderTopWidth, shadow: style(nav).boxShadow, backdrop: style(nav).backdropFilter, buttons: [...nav.querySelectorAll('button')].map(button => ({ background: style(button).backgroundColor, color: style(button).color, opacity: style(button).opacity })) }; })(),
                    font: document.fonts.check('16px Jost'),
                    overflow: frame.scrollHeight > frame.clientHeight + 1 || frame.scrollWidth > frame.clientWidth + 1,
                    overlaps, clipped,
                };
            });
            if (i > 0) assert.match(metrics.label, /^\d{2} \| /);
            assert.ok(Math.abs(metrics.progress.width - (i + 1) * 72) < 1);
            assert.equal(metrics.progress.color, 'rgb(0, 123, 255)');
            assert.equal(metrics.progress.image, 'none');
            assert.equal(metrics.progress.opacity, '1');
            assert.equal(metrics.progress.height, 6);
            assert.equal(metrics.progress.track, 'rgb(255, 255, 255)');
            assert.ok(metrics.logo.every(logo => logo.loaded && logo.width === logo.height));
            if (metrics.heading) {
                const source = specification[metrics.heading.cover ? 'cover' : metrics.heading.content ? 'content' : 'section'];
                const scale = 1440 / specification.canvas.widthPt;
                assert.equal(metrics.heading.count, 1);
                assert.equal(metrics.heading.text, roles[i].title);
                assert.equal(!!metrics.subtitle, roles[i].subtitle);
                assert.equal(metrics.overline?.text, roles[i].overline);
                assert.deepEqual(metrics.headerOrder, [...(roles[i].overline ? ['P'] : []), 'H1', ...(roles[i].subtitle ? ['H2'] : [])]);
                assert.ok(Math.abs(metrics.heading.size - source.fontSizePt * scale) < 0.01);
                assert.ok(Math.abs(metrics.heading.lineHeight - source.lineHeightPt * scale) < 0.01);
                assert.ok(Math.abs(metrics.heading.tracking - source.letterSpacingPt * scale) < 0.01);
                assert.equal(metrics.heading.weight, String(specification.fontWeight));
                assert.ok(metrics.heading.family.startsWith(specification.fontFamily));
                assert.equal(metrics.heading.color, metrics.heading.content || metrics.heading.conclusion ? 'rgb(0, 0, 0)' : 'rgb(255, 255, 255)');
                assert.equal(metrics.heading.align, metrics.heading.conclusion ? 'left' : 'center');
                if (metrics.heading.content) {
                    const contextHeight = roles[i].overline ? specification.overline.lineHeightPt * scale + spacing.overlineGapPx : 0;
                    assert.ok(Math.abs(metrics.heading.top - (source.boxTopPt + source.textInsetPt) * scale - contextHeight) < 0.1);
                }
                if (!metrics.heading.conclusion) assert.ok(Math.abs(metrics.heading.center - 720) < 1, `Slide ${i + 1}: ${JSON.stringify(metrics.heading)}`);
                for (const role of ['subtitle', 'overline']) if (metrics[role]) {
                    for (const [actual, expected] of [['size', 'fontSizePt'], ['lineHeight', 'lineHeightPt'], ['tracking', 'letterSpacingPt']]) {
                        assert.ok(Math.abs(metrics[role][actual] - specification[role][expected] * scale) < 0.01, `Slide ${i + 1}: ${role} ${actual}`);
                    }
                    assert.equal(metrics[role].color, metrics.heading.color);
                    assert.equal(metrics[role].tag, role === 'subtitle' ? 'H2' : 'P');
                    assert.equal(metrics[role].weight, '400');
                    assert.ok(metrics[role].family.startsWith(specification.fontFamily));
                }
                if (metrics.overline) assert.ok(Math.abs(metrics.heading.top - metrics.overline.bottom - spacing.overlineGapPx) < 0.1);
                if (metrics.subtitle) {
                    const gap = metrics.heading.cover ? spacing.coverSubtitleGapPt * scale : metrics.heading.content || metrics.heading.conclusion ? spacing.subtitleGapPx : spacing.sectionSubtitleGapPt * scale;
                    assert.ok(Math.abs(metrics.subtitle.top - metrics.heading.bottom - gap) < 0.1, `Slide ${i + 1}: subtitle gap`);
                }
            }
            if (metrics.body) {
                assert.equal(metrics.body.scrolls, false, `Slide ${i + 1} needs scrolling`);
                assert.ok(Math.abs(metrics.body.center - metrics.body.innerCenter) < 1, `Slide ${i + 1}: body not centered`);
            }
            assert.equal(metrics.nav.background, 'rgba(0, 0, 0, 0)');
            assert.equal(metrics.nav.border, '0px');
            assert.equal(metrics.nav.shadow, 'none');
            assert.equal(metrics.nav.backdrop, 'none');
            for (const button of metrics.nav.buttons) {
                assert.equal(button.background, 'rgba(0, 0, 0, 0)');
                assert.equal(button.opacity, '1');
                assert.equal(button.color, [0, 3, 7, 17].includes(i) ? 'rgb(255, 255, 255)' : 'rgb(0, 123, 255)');
            }
            for (const code of metrics.code) {
                assert.ok(code.theme.includes('dark-plus'));
                assert.equal(code.background, 'rgb(30, 30, 30)');
                assert.ok(code.colors.length > 3);
            }
            assert.ok(metrics.font);
            assert.equal(metrics.overflow, false, `Slide ${i + 1} scrolls`);
            assert.deepEqual(metrics.overlaps, [], `Slide ${i + 1} overlaps the footer`);
            assert.deepEqual(metrics.clipped, [], `Slide ${i + 1} clips content`);
            await audit(`slide-${i + 1}`);
            await page.screenshot({ scale: 'css', path: path.join(output, `slide-${String(i + 1).padStart(2, '0')}.png`) });
            report.push({ slide: i + 1, ...metrics });
        }
        assert.equal(report.flatMap(slide => slide.code).length, 3);
        await page.keyboard.press('ArrowLeft');
        await page.waitForFunction(() => document.querySelectorAll('.slidewire-frame')[18].classList.contains('is-active'));
        await page.keyboard.press('ArrowRight');
        await page.waitForFunction(() => document.querySelectorAll('.slidewire-frame')[19].classList.contains('is-active'));

        // Real interaction states, including both end-of-deck disabled arrows.
        await audit('last-slide-disabled-next');
        const next = page.getByRole('button', { name: 'Next slide', exact: true });
        assert.ok(await next.isDisabled());
        assert.equal(await next.evaluate(node => getComputedStyle(node).borderTopStyle), 'dashed');
        await next.hover();
        await audit('navigation-disabled-hover');
        await page.locator('.slidewire-controls').screenshot({ scale: 'css', path: path.join(output, 'navigation-disabled.png') });
        const previous = page.getByRole('button', { name: 'Previous slide' });
        await previous.hover();
        await audit('navigation-hover');
        assert.equal(await previous.evaluate(node => getComputedStyle(node).backgroundColor), 'rgba(0, 0, 0, 0)');
        await page.screenshot({ scale: 'css', path: path.join(output, 'navigation-hover.png') });
        await page.mouse.down();
        await audit('navigation-active');
        assert.equal(await previous.evaluate(node => getComputedStyle(node).backgroundColor), 'rgba(0, 0, 0, 0)');
        await page.screenshot({ scale: 'css', path: path.join(output, 'navigation-active.png') });
        await page.mouse.move(10, 450);
        await page.mouse.up();
        await previous.focus();
        await audit('navigation-focus');
        await page.goto(`${base}/slides/symfony-ai-in-action#/slide/1`);
        await page.waitForFunction(() => document.querySelectorAll('.slidewire-frame')[0].classList.contains('is-active'));
        await audit('first-slide-disabled-previous');
        await page.locator('.slidewire-controls').screenshot({ scale: 'css', path: path.join(output, 'navigation-blue.png') });
        await frames.nth(0).evaluate(frame => {
            frame.classList.replace('sw-slide--cover', 'sw-slide--cover-black');
            const logo = frame.querySelector('footer img');
            logo.src = logo.src.replace('uni-blue.png', 'uni-light.png');
            logo.dataset.background = 'black';
        });
        await audit('navigation-black');
        assert.equal(await previous.evaluate(node => getComputedStyle(node).color), 'rgb(255, 255, 255)');
        assert.equal(await previous.evaluate(node => getComputedStyle(node).backgroundColor), 'rgba(0, 0, 0, 0)');
        await page.screenshot({ scale: 'css', path: path.join(output, 'navigation-black.png') });
        await page.goto(`${base}/slides/symfony-ai-in-action#/slide/13`);
        await page.waitForFunction(() => document.querySelectorAll('.slidewire-frame')[12].classList.contains('is-active'));
        const body = page.locator('.slidewire-frame.is-active .sw-body');
        await body.evaluate(node => {
            const inner = node.querySelector('.sw-body-inner');
            const extra = document.createElement('div'); extra.id = 'dense-probe';
            for (let i = 0; i < 25; i++) { const line = document.createElement('p'); line.textContent = 'Contenu dense — ligne ' + (i + 1); line.style.marginBlock = '1rem'; extra.append(line); }
            inner.append(extra);
        });
        const dense = await body.evaluate(node => ({ scrolls: node.scrollHeight > node.clientHeight, firstAccessible: node.querySelector('.sw-body-inner').getBoundingClientRect().top >= node.getBoundingClientRect().top, frameScrolls: node.closest('.slidewire-frame').scrollHeight > 900 }));
        assert.deepEqual(dense, { scrolls: true, firstAccessible: true, frameScrolls: false });
        await body.evaluate(node => node.scrollTo(0, node.scrollHeight));
        await audit('dense-content');
        await page.screenshot({ scale: 'css', path: path.join(output, 'dense-content.png') });
        await page.locator('#dense-probe').evaluate(node => node.remove());

        // Prove that the audit catches inline SVG paint and generated content.
        await page.locator('.slidewire-frame.is-active .sw-canvas').evaluate(canvas => {
            const probe = document.createElement('div');
            probe.id = 'palette-probe';
            probe.innerHTML = '<svg width="20" height="20"><path d="M0 0 L20 20" style="stroke:rgb(255,0,0)"/></svg><style>#palette-probe::after {content:"probe";color:rgb(123,45,67)}</style>';
            canvas.append(probe);
        });
        const detected = await page.locator('.slidewire-shell').evaluate(auditDarkwoodPalette);
        assert.ok(detected.violations.some(v => v.property === 'stroke'));
        assert.ok(detected.violations.some(v => v.pseudo === '::after'));
        // Moving the same foreign color into a CODE token is the sole exception.
        await page.locator('#palette-probe').evaluate(node => { node.innerHTML = '<pre class="phiki"><code><span style="color:rgb(123,45,67)">syntax</span></code></pre>'; });
        assert.deepEqual((await page.locator('.slidewire-shell').evaluate(auditDarkwoodPalette)).violations, []);
        await page.locator('#palette-probe').evaluate(node => node.remove());

        // Exercise all Blade layout variants, including the black cover.
        const fixture = execFileSync('php', ['tests/fixtures/render-design-system.php'], { encoding: 'utf8', env: { ...process.env, SLIDEWIRE_URL: base } });
        await page.route(`${base}/__theme-check`, route => route.fulfill({ contentType: 'text/html; charset=utf-8', body: `<meta charset="utf-8">${fixture}` }));
        await page.goto(`${base}/__theme-check`);
        const manifest = JSON.parse(fs.readFileSync('public/build/manifest.json', 'utf8'));
        await page.addStyleTag({ url: `${base}/build/${manifest['resources/css/app.css'].file}` });
        await page.evaluate(() => document.fonts.ready);
        await page.evaluate(async () => {
            await Promise.all([...document.images].map(img => img.decode()));
        });
        const variants = page.locator('.sw-slide');
        const logos = ['blue', 'light', 'dark'];
        for (let i = 0; i < 3; i++) {
            const logo = variants.nth(i).locator('footer img');
            assert.ok((await logo.getAttribute('src')).endsWith(`dw512x512-uni-${logos[i]}.png`));
            assert.ok(await logo.evaluate(img => img.complete && img.naturalWidth === 512 && img.naturalHeight === 512));
            const palette = await variants.nth(i).evaluate(auditDarkwoodPalette);
            assert.deepEqual(palette.violations, [], `Fixture ${i}: palette violations`);
            audits.push({ label: `fixture-${i}`, ...palette });
            // Pixel audit of the DISPLAYED raster logo, after its SVG filter.
            const logoPng = await logo.screenshot();
            const pixels = await page.evaluate(async data => {
                const image = new Image(); image.src = `data:image/png;base64,${data}`; await image.decode();
                const canvas = document.createElement('canvas'); canvas.width = image.width; canvas.height = image.height;
                const ctx = canvas.getContext('2d'); ctx.drawImage(image, 0, 0);
                const rgba = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
                const found = new Set();
                for (let offset = 0; offset < rgba.length; offset += 4) found.add([...rgba.slice(offset, offset + 4)].join(','));
                return [...found];
            }, logoPng.toString('base64'));
            assert.deepEqual(pixels.filter(color => !['0,0,0,255', '255,255,255,255', '0,123,255,255'].includes(color)), [], `Logo ${i}: out-of-palette pixels`);
            await variants.nth(i).screenshot({ scale: 'css', path: path.join(output, `check-${['blue', 'black', 'lists'][i]}.png`) });
        }
        const lists = await page.locator('.sw-slide--content ul').evaluateAll(nodes => nodes.map(node => getComputedStyle(node).listStyleType));
        assert.deepEqual(lists, ['disc', 'circle', 'circle', 'disc', 'circle', 'circle']);
        // Actual shared Blade headers: every combination, including absent
        // elements and a wrapped title. Panel padding is outside the header.
        await page.addStyleTag({ content: '#dw-spacing-cases {padding:24px;background:#fff;color:#000;display:grid;gap:24px} #dw-spacing-cases > section {padding:24px;border:var(--dw-border-width) solid var(--dw-blue)} #dw-spacing-cases > section::before {content:attr(data-spacing-case);display:block;margin-bottom:16px;font-size:16px;line-height:1}' });
        const spacingCases = await page.locator('[data-spacing-case]').evaluateAll(nodes => nodes.map(panel => {
            const header = panel.querySelector('.sw-header');
            const children = [...header.children];
            const rect = header.getBoundingClientRect();
            return {
                name: panel.dataset.spacingCase,
                height: rect.height,
                gap: getComputedStyle(header).rowGap,
                padding: [getComputedStyle(header).paddingTop, getComputedStyle(header).paddingBottom],
                children: children.map(node => { const r = node.getBoundingClientRect(); const s = getComputedStyle(node); return { tag: node.tagName, top: r.top - rect.top, bottom: r.bottom - rect.top, height: r.height, lineHeight: parseFloat(s.lineHeight), marginBottom: s.marginBottom }; }),
            };
        }));
        assert.equal(spacingCases.length, 5);
        for (const item of spacingCases) {
            assert.equal(item.gap, '0px');
            assert.deepEqual(item.padding, ['0px', '0px']);
            assert.ok(Math.abs(item.children[0].top) < 0.1, `${item.name}: empty leading space`);
            assert.ok(Math.abs(item.children.at(-1).bottom - item.height) < 0.1, `${item.name}: empty trailing space`);
            let total = 0;
            for (let i = 0; i < item.children.length; i++) {
                const child = item.children[i];
                assert.equal(child.marginBottom, '0px');
                total += child.height;
                if (i > 0) {
                    const gap = child.tag === 'H1' ? spacing.overlineGapPx : spacing.subtitleGapPx;
                    assert.ok(Math.abs(child.top - item.children[i - 1].bottom - gap) < 0.1, `${item.name}: ${child.tag} gap`);
                    total += gap;
                }
            }
            assert.ok(Math.abs(item.height - total) < 0.1, `${item.name}: cumulative spacing`);
            if (item.name === 'two-lines') {
                const title = item.children.find(node => node.tag === 'H1');
                assert.ok(Math.abs(title.height - 2 * title.lineHeight) < 0.1);
            }
        }
        assert.deepEqual((await page.locator('#dw-spacing-cases').evaluate(auditDarkwoodPalette)).violations, []);
        await page.locator('#dw-spacing-cases').screenshot({ scale: 'css', path: path.join(output, 'heading-cases.png') });
        // Exercise a real link and its pseudo states without changing the deck.
        const fixtureContent = variants.nth(2);
        await fixtureContent.evaluate(node => {
            const link = document.createElement('a'); link.href = '#theme-check'; link.textContent = 'Darkwood'; link.className = 'palette-link';
            node.querySelector('.sw-content').append(link);
        });
        const link = page.locator('.palette-link');
        await link.hover();
        assert.deepEqual((await fixtureContent.evaluate(auditDarkwoodPalette)).violations, []);
        await page.mouse.down();
        assert.deepEqual((await fixtureContent.evaluate(auditDarkwoodPalette)).violations, []);
        await page.mouse.move(10, 450); await page.mouse.up(); await link.focus();
        assert.deepEqual((await fixtureContent.evaluate(auditDarkwoodPalette)).violations, []);
        await link.evaluate(node => {
            const range = document.createRange(); range.selectNodeContents(node);
            window.getSelection().removeAllRanges(); window.getSelection().addRange(range);
        });
        assert.deepEqual((await fixtureContent.evaluate(auditDarkwoodPalette)).violations, []);
        const codeCases = await page.locator('#native-code-cases [data-code-surface]').evaluateAll(nodes => nodes.map(node => ({
            background: getComputedStyle(node).backgroundColor,
            toolbar: getComputedStyle(node.querySelector('.sw-code-toolbar')).backgroundColor,
            content: getComputedStyle(node.querySelector('pre')).backgroundColor,
            theme: node.querySelector('pre').className,
            font: getComputedStyle(node.querySelector('pre')).fontFamily,
            text: node.querySelector('pre').textContent,
        })));
        assert.equal(codeCases.length, 2);
        for (const item of codeCases) {
            assert.equal(item.background, 'rgb(30, 30, 30)');
            assert.equal(item.toolbar, item.background);
            assert.equal(item.content, item.background);
            assert.ok(item.theme.includes('dark-plus'));
            assert.ok(item.font.startsWith('"JetBrains Mono"'));
        }
        assert.ok(codeCases[1].text.includes('$ php artisan test'));
        await page.locator('#native-code-cases').screenshot({path:path.join(output, 'code-terminal.png'),scale:'css'});
        const lines = await page.locator('#native-line-cases').evaluate(node => ({
            pseudo: getComputedStyle(node, '::after').borderTopWidth,
            svg: [...node.querySelectorAll('line,rect,circle')].map(shape => ({
                width: getComputedStyle(shape).strokeWidth,
                effect: getComputedStyle(shape).vectorEffect,
            })),
        }));
        assert.equal(lines.pseudo, '2.5px');
        assert.ok(lines.svg.every(line => line.width === '2.5px' && line.effect === 'non-scaling-stroke'));
        await page.locator('#native-line-cases').screenshot({path:path.join(output, 'graphic-lines.png'),scale:'css'});
        assert.deepEqual(errors, []);
        fs.writeFileSync(path.join(output, 'validation.json'), JSON.stringify({ viewport: [1440, 900], specification, spacing, spacingCases, dense, errors, lists, lines, codeCases, palette: audits, slides: report }, null, 2));
        console.log(`Validated and captured ${report.length} slides + 3 theme checks: ${output}`);
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
