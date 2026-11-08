const {chromium}=require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
const {auditDarkwoodPalette}=require('./browser/palette.cjs');
const {auditDesignSystem}=require('./browser/design.cjs');
const base=process.env.SLIDEWIRE_URL || 'http://127.0.0.1:8017';
const root=path.resolve('storage/app/design-system');
const reference=JSON.parse(fs.readFileSync('tests/fixtures/presentation-content.json')).decks;
const reportFile=path.join(root,process.env.REPORT_NAME || 'validation.json');
const requested=process.env.DECKS?.split(',') || Object.keys(reference);
(async()=>{const b=await chromium.launch({executablePath:process.env.CHROME || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',args:['--force-device-scale-factor=2']});const report=fs.existsSync(reportFile)?JSON.parse(fs.readFileSync(reportFile)):{};
try {for(const slug of requested){const p=await b.newPage({viewport:{width:Number(process.env.VIEWPORT_WIDTH || 1440),height:Number(process.env.VIEWPORT_HEIGHT || 900)},deviceScaleFactor:2});const errors=[],assets=[];p.on('pageerror',e=>errors.push(e.message));p.on('response',r=>{if(r.status()>=400) assets.push({status:r.status(),url:r.url()})});
const out=path.join(root,'renders',slug);fs.mkdirSync(out,{recursive:true});await p.goto(`${base}/slides/${slug}`,{waitUntil:'domcontentloaded'});await p.waitForFunction(()=>document.querySelector('.slidewire-frame.is-active'));const frames=p.locator('.slidewire-frame');const rows=process.env.SLIDE_NUMBERS?(report[slug]?.slides||[]):[];
if(await frames.count()!==reference[slug].count)throw Error(slug+' count changed');
const indices=process.env.SLIDE_NUMBERS?process.env.SLIDE_NUMBERS.split(',').map(n=>Number(n)-1):Array.from({length:reference[slug].count},(_,i)=>i);
for(const i of indices){
const fragment=reference[slug].slides[i].fragments-1;
await p.goto(`${base}/slides/${slug}#/slide/${i+1}${fragment>=0?'/f/'+fragment:''}`);
await p.waitForFunction(i=>document.querySelectorAll('.slidewire-frame')[i]?.classList.contains('is-active'),i);
await p.evaluate(()=>document.fonts.ready);
await p.waitForFunction(i=>[...document.querySelectorAll('.slidewire-frame')[i].querySelectorAll('[data-slidewire-diagram]')].every(e=>e.hasAttribute('data-processed')&&e.querySelector('svg')),i);
if(fragment>=0) await p.waitForFunction(i=>{const f=document.querySelectorAll('.slidewire-frame')[i];return [...f.querySelectorAll('[data-fragment]')].every(e=>e.classList.contains('slidewire-fragment-visible'));},i);
await p.waitForTimeout(await frames.nth(i).locator('[data-dw-motion]').count()?1200:650);
await frames.nth(i).evaluate(async f=>{await Promise.all([...f.querySelectorAll('img')].map(img=>img.decode().catch(()=>{})));});
if(!(await frames.nth(i).evaluate(f=>f.classList.contains('is-active')))) throw Error(slug+' fragment navigation escaped slide '+(i+1));
if(process.env.EXTRA_SETTLE) await p.waitForTimeout(Number(process.env.EXTRA_SETTLE));
const frame=frames.nth(i);let palette=await p.locator('.slidewire-shell').evaluate(auditDarkwoodPalette);
// Native diagram fades can outlive the slide transition. Observe their final paint.
for(let attempt=0;attempt<12&&palette.violations.some(v=>v.property==='opacity');attempt++){await p.waitForTimeout(150);palette=await p.locator('.slidewire-shell').evaluate(auditDarkwoodPalette);}
const metrics=await frame.evaluate(frame=>{const body=frame.querySelector('.sw-body'),inner=frame.querySelector('.sw-body-inner'),head=frame.querySelector('.sw-header');const r=e=>e.getBoundingClientRect();return {theme:frame.className,h1:frame.querySelectorAll('h1').length,header:head?{height:r(head).height,top:r(head).top,bottom:r(head).bottom,text:head.textContent.trim().replace(/\s+/g,' ')}:null,frameOverflow:frame.scrollHeight>frame.clientHeight+1,bodyScroll:body&&body.scrollHeight>body.clientHeight+1,horizontal:body&&body.scrollWidth>body.clientWidth+1,center:body&&inner?(r(inner).top+r(inner).bottom-r(body).top-r(body).bottom)/2:null,footer:!!frame.querySelector('.sw-footer'),missingImages:[...frame.querySelectorAll('img')].filter(e=>e.complete&&e.naturalWidth===0).map(e=>e.src),code:[...frame.querySelectorAll('pre.phiki')].map(e=>e.className),fragments:frame.querySelectorAll('[data-fragment]').length,boards:frame.querySelectorAll('.pg-cell,.dw20-cell').length}});
const design=await frame.evaluate(auditDesignSystem);
await p.screenshot({path:path.join(out,`slide-${String(i+1).padStart(2,'0')}.png`),scale:'css'});rows[i]={slide:i+1,...metrics,palette,design};}
// Exercise native keyboard navigation after the last frame, including fragments.
if(rows.length>1){await p.goto(`${base}/slides/${slug}#/slide/1`);await p.waitForTimeout(100);await p.keyboard.press('ArrowRight');await p.waitForTimeout(100);}
report[slug]={count:rows.length,errors,assets,slides:rows};fs.writeFileSync(reportFile,JSON.stringify(report,null,2));console.log(slug,rows.length,'palette',rows.reduce((n,s)=>n+s.palette.violations.length,0),'horizontal',rows.filter(s=>s.horizontal).length,'frameScroll',rows.filter(s=>s.frameOverflow).length);await p.close();
if((process.env.ENFORCE ?? '1')==='1') {
assert.deepEqual(errors,[],slug+' runtime errors');
for(const row of rows){assert.deepEqual(row.palette.violations,[],slug+' palette slide '+row.slide);assert.deepEqual(row.missingImages,[],slug+' images slide '+row.slide);assert.equal(row.frameOverflow,false,slug+' frame overflow '+row.slide);assert.notEqual(row.horizontal,true,slug+' horizontal overflow '+row.slide);assert.deepEqual(row.design.typography,[],slug+' content typography '+row.slide);assert.deepEqual(row.design.borders,[],slug+' border widths '+row.slide);assert.deepEqual(row.design.arrowFailures,[],slug+' arrows slide '+row.slide);assert.deepEqual(row.design.codeFailures,[],slug+' code surface '+row.slide);assert.deepEqual(row.design.svgLabels||[],[],slug+' SVG labels '+row.slide);assert.deepEqual(row.design.svgStrokes||[],[],slug+' SVG strokes '+row.slide);assert.deepEqual(row.design.clipping||[],[],slug+' internal clipping '+row.slide);assert.equal(row.design.blue.toLowerCase(),'#007bff');assert.ok(row.h1<=1,slug+' competing titles '+row.slide);assert.ok(row.footer,slug+' footer '+row.slide);if(!row.bodyScroll&&row.center!==null)assert.ok(Math.abs(row.center)<1,slug+' body centering '+row.slide);}
}}

}finally{await b.close()}})().catch(e=>{console.error(e);process.exit(1)});
