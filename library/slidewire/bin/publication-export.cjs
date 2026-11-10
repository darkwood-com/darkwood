// Local canonical renderer. Browser automation is limited to the local Slidewire app.
const fs = require('node:fs');
const path = require('node:path');
const {chromium} = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const {auditDarkwoodPalette} = require('./browser/palette.cjs');
const {auditDesignSystem} = require('./browser/design.cjs');
const {paginateStatic,showStaticPage} = require('./browser/static-pagination.cjs');

(async () => {
    const [manifestFile, directory, base] = process.argv.slice(2);
    if (!/^http:\/\/127\.0\.0\.1:\d+$/.test(base)) throw Error('Only the local loopback renderer is supported.');
    const manifest = JSON.parse(fs.readFileSync(manifestFile));
    const browser = await chromium.launch({executablePath: process.env.CHROME || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome', args: ['--force-device-scale-factor=2']});
    const report = [];
    const pageMap = [];
    try {
        const page = await browser.newPage({viewport: {width: 1440, height: 810}, deviceScaleFactor: 2});
        const errors = [];
        page.on('pageerror', e => errors.push(e.message));
        page.on('response', r => {if (r.status() >= 400 && (!manifest.static_projection || r.request().resourceType() !== 'document' || r.url().startsWith(base+'/slides/'))) errors.push(`${r.status()} ${r.url()}`);});
        await page.goto(`${base}/slides/${manifest.slug}`, {waitUntil: 'networkidle'});
        if (await page.locator('.slidewire-frame').count() !== manifest.slides.length) throw Error('Compiled/browser slide count mismatch.');
        await page.emulateMedia({media: 'screen'});
        await page.addStyleTag({content: '.slidewire-controls,.mermaidTooltip{display:none!important}'});
        for (let i = 0; i < manifest.slides.length; i++) {
            const fragments = manifest.slides[i].fragments;
            await page.goto(`${base}/slides/${manifest.slug}#/slide/${i+1}${fragments ? '/f/'+(fragments-1) : ''}`);
            await page.waitForFunction(i => document.querySelectorAll('.slidewire-frame')[i]?.classList.contains('is-active'), i);
            await page.evaluate(() => document.fonts.ready);
            await page.waitForFunction(i => [...document.querySelectorAll('.slidewire-frame')[i].querySelectorAll('[data-slidewire-diagram]')].every(node => node.hasAttribute('data-processed') && node.querySelector('svg')), i);
            const frame = page.locator('.slidewire-frame').nth(i);
            await frame.evaluate(async f => Promise.all([...f.querySelectorAll('img')].map(img => img.decode())));
            if (fragments) await page.waitForFunction(i => [...document.querySelectorAll('.slidewire-frame')[i].querySelectorAll('[data-fragment]')].every(e => e.classList.contains('slidewire-fragment-visible')), i);
            await page.waitForTimeout(1500);
            const audit = await frame.evaluate(f => {
                const body = f.querySelector('.sw-body');
                return {missingImages: [...f.querySelectorAll('img')].filter(e => !e.complete || !e.naturalWidth).map(e => e.src),
                    scroll: !!body && (body.scrollHeight > body.clientHeight+1 || body.scrollWidth > body.clientWidth+1),
                    overflow: f.scrollHeight > f.clientHeight+1 || f.scrollWidth > f.clientWidth+1,
                    embeds: f.querySelectorAll('iframe,video,canvas').length,
                    documentOverflow: document.documentElement.scrollHeight > innerHeight+1 || document.documentElement.scrollWidth > innerWidth+1,
                    fonts: document.fonts.check('16px Jost')};
            });
            const palette = await page.locator('.slidewire-shell').evaluate(auditDarkwoodPalette);
            const design = await frame.evaluate(auditDesignSystem);
            const designFailures = {arrows:design.arrowFailures, typography:design.typography, borders:design.borders, svgLabels:design.svgLabels, svgStrokes:design.svgStrokes, clipping:design.clipping, code:design.codeFailures};
            const staticSurface = manifest.static_projection && await frame.locator('.sw-body svg,.sw-body canvas,.sw-body video,.sw-body iframe,.sw-body [data-board],.sw-body [data-pg-board],.sw-body div[aria-label],.sw-body .sw-timeline,.sw-body .sw-stream-tree,.sw-body .sw-process,.sw-body table').count()>0;
            const needsProjection = staticSurface || Object.values(designFailures).some(items => items.length) || audit.scroll || audit.overflow || audit.documentOverflow || audit.embeds;
            if (!audit.fonts || audit.missingImages.length || palette.violations.length) throw Error(`Slide ${i+1} has missing assets or invalid palette: ${JSON.stringify({...audit,palette:palette.violations})}`);
            if (needsProjection && !manifest.static_projection) throw Error(`Slide ${i+1} requires review: ${JSON.stringify({...audit,designFailures})}`);
            const projection=needsProjection?await paginateStatic(frame,{slide:i+1,route:'/slides/'+manifest.slug,interactiveUrl:manifest.interactive_base?manifest.interactive_base+'/slides/'+manifest.slug:null,interactivePending:manifest.interactive_pending,snapshotFile:path.join(directory,'..','static-snapshots',manifest.revision,`slide-${i+1}.json`)}):null;
            const pages=projection?.pages||1;
            for(let part=0;part<pages;part++){
                if(projection)await showStaticPage(frame,part,i+1);
                const number=pageMap.length+1;
                await page.screenshot({path: path.join(directory, `slide-${String(number).padStart(3,'0')}.png`), scale: 'css'});
                await page.pdf({path: path.join(directory, `page-${String(number).padStart(3,'0')}.pdf`), width: '1440px', height: '810px', printBackground: true, margin: {top:0,right:0,bottom:0,left:0}});
                pageMap.push({page:number,slide:i+1,slide_id:manifest.slides[i].id,part:part+1,parts:pages,static_projection:!!projection});
            }
            report.push({slide: i+1, ...audit, projection});
        }
        if (errors.length) throw Error(errors.join('\n'));
        fs.writeFileSync(path.join(directory, 'render-validation.json'), JSON.stringify(report, null, 2));
        fs.writeFileSync(path.join(directory, 'page-map.json'), JSON.stringify(pageMap,null,2));
    } finally {await browser.close();}
})().catch(e => {console.error(e.message); process.exitCode = 1;});
