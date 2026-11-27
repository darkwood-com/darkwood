const {chromium}=require('playwright'),assert=require('node:assert/strict'),fs=require('node:fs'),path=require('node:path');
const {auditDarkwoodPalette}=require('../../bin/browser/palette.cjs');
const base=process.env.SLIDEWIRE_URL||'http://127.0.0.1:8017',out=path.resolve('storage/app/catalogue');
const registry=JSON.parse(fs.readFileSync('resources/presentations.json')),reference=JSON.parse(fs.readFileSync('tests/fixtures/presentation-content.json')).decks;
(async()=>{fs.mkdirSync(path.join(out,'endings'),{recursive:true});const browser=await chromium.launch({executablePath:process.env.CHROME||'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',args:['--force-device-scale-factor=2']});const errors=[],report={presentations:[],navigation:[],homepage:[]};
try{const page=await browser.newPage({viewport:{width:1440,height:900},deviceScaleFactor:2});page.on('pageerror',e=>errors.push(e.message));
for(const viewport of [{width:1440,height:900},{width:390,height:844}]){
 await page.setViewportSize(viewport);const response=await page.goto(base+'/?presentation=memory');assert.equal(response.status(),200);await page.evaluate(()=>document.fonts.ready);
 const links=await page.locator('li[data-presentation-id] a').evaluateAll(nodes=>nodes.map(n=>({id:n.parentElement.dataset.presentationId,url:new URL(n.href).searchParams.get('presentation'),title:n.querySelector('span').textContent.trim()})));
 assert.equal(links.length,registry.length);assert.equal(new Set(links.map(l=>l.id)).size,registry.length);
 for(const row of registry){const link=links.find(l=>l.id===row.id);assert.equal(link.url,row.source_slug);assert.equal(link.title,row.title);}
 assert.equal(await page.locator('#deck-player').count(),1);
 assert.match(await page.locator('#deck-player').getAttribute('src'),/\/slides\/memory$/);
 const rect=await page.locator('#deck-player').boundingBox();assert.ok(Math.abs(rect.width/rect.height-16/9)<.01);
 assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false);
 const deck=page.frameLocator('#deck-player');await deck.locator('.slidewire-frame.is-active').waitFor();
 assert.equal(await deck.locator('.slidewire-frame').count(),22);
 await deck.locator('[aria-label="Next slide"]').click();await deck.locator('.slidewire-frame').nth(1).waitFor({state:'visible'});
 await page.locator('#deck-player').focus();await page.keyboard.press('ArrowRight');
 await page.waitForFunction(()=>document.querySelector('#deck-player').contentDocument.querySelectorAll('.slidewire-frame')[2].classList.contains('is-active'));
 for(const theme of ['theme-dark','theme-sepia','theme-light']){await page.locator('#theme-switcher').click();assert.ok(await page.locator('body').evaluate((n,c)=>n.classList.contains(c),theme));if(viewport.width===1440){await page.waitForTimeout(500);await page.screenshot({path:path.join(out,'homepage-'+theme+'.png'),scale:'css'});}}
 if(viewport.width<576){await page.locator('#library-menu').click();assert.equal(await page.locator('#library-menu').getAttribute('aria-expanded'),'true');const menu=await page.locator('#library-sidebar').boundingBox();assert.equal(menu.width,viewport.width);await page.keyboard.press('Escape');assert.equal(await page.locator('#library-menu').getAttribute('aria-expanded'),'false');await page.locator('#library-menu').click();}
 const first=page.locator('li[data-presentation-id] a').first();await first.click();assert.ok(new URL(page.url()).searchParams.has('presentation'));assert.equal(await page.locator('#library-menu').getAttribute('aria-expanded'),'false');
 await page.reload();assert.equal(await page.locator('a[aria-current="page"]').getAttribute('data-title'),registry[0].title);
 await page.goBack();await page.waitForFunction(()=>document.querySelector('#deck-player').src.endsWith('/slides/memory'));
 if(viewport.width<576){await page.locator('#library-menu').click();await page.screenshot({path:path.join(out,'homepage-mobile-menu.png'),scale:'css'});await page.setViewportSize({width:1000,height:800});assert.equal(await page.locator('#library-menu').getAttribute('aria-expanded'),'false');await page.setViewportSize(viewport);}
 await page.frameLocator('#deck-player').locator('.slidewire-frame.is-active').waitFor();await page.waitForTimeout(1200);
 await page.screenshot({path:path.join(out,viewport.width===1440?'homepage-desktop.png':'homepage-mobile.png'),scale:'css'});
 report.homepage.push({viewport,links:links.length,iframe:'16:9',themes:3,history:true});
}
await page.setViewportSize({width:1440,height:900});
for(const row of registry){const ref=reference[row.source_slug],last=ref.count,fragment=ref.slides[last-1].fragments-1;
 const response=await page.goto(`${base}/slides/${row.source_slug}?catalogue=1#/slide/${last}${fragment>=0?'/f/'+fragment:''}`);assert.equal(response.status(),200);
 await page.waitForFunction(n=>document.querySelectorAll('.slidewire-frame')[n-1]?.classList.contains('is-active'),last);await page.evaluate(()=>document.fonts.ready);await page.waitForTimeout(1400);
 const frame=page.locator('.slidewire-frame.is-active');await frame.evaluate(async f=>Promise.all([...f.querySelectorAll('img')].map(i=>i.decode())));
 const result=await frame.evaluate(f=>{const rect=n=>{const r=n.getBoundingClientRect();return {width:r.width,height:r.height}};return {portraits:[...f.querySelectorAll('.sw-author-portrait')].map(n=>({author:n.dataset.authorId,src:new URL(n.src).pathname,natural:[n.naturalWidth,n.naturalHeight],...rect(n),fit:getComputedStyle(n).objectFit,filter:getComputedStyle(n).filter})),footerLogos:f.querySelectorAll('footer .sw-logo').length,otherLogos:f.querySelectorAll('img.sw-logo:not(footer img)').length,frameOverflow:f.scrollHeight>f.clientHeight+1,bodyScroll:(()=>{const b=f.querySelector('.sw-body');return b?b.scrollHeight>b.clientHeight+1:false})()}});
 const replaced=['symfony-ai-in-action','holding-the-core-security-engineering-through-darkwaar40-breach'].includes(row.source_slug);assert.equal(result.portraits.length,replaced?1:0);assert.equal(result.footerLogos,1);assert.equal(result.frameOverflow,false);
 if(replaced){const img=result.portraits[0];assert.equal(img.author,'matyo91');assert.equal(img.src,'/images/authors/matyo91.jpg');assert.deepEqual(img.natural,[640,640]);assert.ok(Math.abs(img.width-img.height)<.01);assert.equal(img.fit,'cover');assert.equal(img.filter,'none');assert.equal(result.otherLogos,0);assert.equal(result.bodyScroll,false);}
 await page.screenshot({path:path.join(out,'endings',row.source_slug+'.png'),scale:'css'});report.presentations.push({...row,count:last,replaced,...result});
}
for(const [name,num,color]of [['white',2,'rgb(0, 123, 255)'],['blue',1,'rgb(255, 255, 255)'],['black',1,'rgb(255, 255, 255)']]){
 await page.goto(`${base}/slides/symfony-ai-in-action#/slide/${num}`);await page.waitForFunction(n=>document.querySelectorAll('.slidewire-frame')[n-1]?.classList.contains('is-active'),num);await page.waitForTimeout(150);
 if(name==='black')await page.locator('.slidewire-frame.is-active').evaluate(f=>{f.classList.replace('sw-slide--cover','sw-slide--cover-black');const img=f.querySelector('footer img');img.src=img.src.replace('uni-blue.png','uni-light.png');img.dataset.background='black'});
 const nav=page.locator('.slidewire-controls'),next=nav.locator('[aria-label="Next slide"]');await next.hover();await next.focus();
 const state=await nav.evaluate(n=>({background:getComputedStyle(n).backgroundColor,shadow:getComputedStyle(n).boxShadow,buttons:[...n.querySelectorAll('.slidewire-control-arrow')].map(b=>({color:getComputedStyle(b).color,fill:getComputedStyle(b.querySelector('path')).fill,disabled:b.disabled,opacity:getComputedStyle(b).opacity,border:getComputedStyle(b).borderStyle}))}));
 assert.equal(state.background,'rgba(0, 0, 0, 0)');assert.equal(state.shadow,'none');for(const b of state.buttons){assert.equal(b.color,color);assert.equal(b.fill,color);assert.equal(b.opacity,'1');if(b.disabled)assert.equal(b.border,'dashed');}
 await page.screenshot({path:path.join(out,'arrows-'+name+'.png'),scale:'css'});report.navigation.push({name,...state});
 if(name==='white'){await next.click();await page.waitForFunction(()=>document.querySelectorAll('.slidewire-frame')[2].classList.contains('is-active'));await page.keyboard.press('ArrowLeft');await page.waitForFunction(()=>document.querySelectorAll('.slidewire-frame')[1].classList.contains('is-active'));}
}
// PDF proofs are excerpts of the two actual closing frames, not publication artifacts.
await page.setViewportSize({width:1440,height:810});
for(const row of report.presentations.filter(r=>r.replaced)){
 await page.goto(`${base}${'/slides/'+row.source_slug}#/slide/${row.count}`);await page.waitForFunction(n=>document.querySelectorAll('.slidewire-frame')[n-1]?.classList.contains('is-active'),row.count);await page.evaluate(()=>document.fonts.ready);await page.locator('.slidewire-frame.is-active').evaluate(async f=>Promise.all([...f.querySelectorAll('img')].map(i=>i.decode())));await page.waitForTimeout(500);await page.addStyleTag({content:'.slidewire-controls,.mermaidTooltip{display:none!important}'});
 await page.pdf({path:path.join(out,'ending-'+row.source_slug+'.pdf'),width:'1440px',height:'810px',printBackground:true,margin:{top:0,right:0,bottom:0,left:0}});
}
assert.deepEqual(errors,[]);report.errors=errors;fs.writeFileSync(path.join(out,'validation.json'),JSON.stringify(report,null,2));console.log('Catalogue desktop/mobile, 29 endings, two portraits, navigation states and two PDF excerpts passed');
}finally{await browser.close()}})().catch(e=>{console.error(e);process.exitCode=1});
