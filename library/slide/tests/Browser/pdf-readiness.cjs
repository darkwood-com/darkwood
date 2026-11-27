// Read-only layout preflight at the exact canonical PDF viewport. No upload.
const {chromium}=require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const fs=require('node:fs'),assert=require('node:assert/strict');
const {auditDesignSystem}=require('../../bin/browser/design.cjs');
const catalog=JSON.parse(fs.readFileSync('resources/presentations.json'));
const reference=JSON.parse(fs.readFileSync('tests/fixtures/presentation-content.json')).decks;
const base=process.env.SLIDEWIRE_URL || 'http://127.0.0.1:8017';
const requested=process.env.DECKS?.split(',');
const output='storage/app/design-system/pdf-readiness.json';
(async()=>{
 const browser=await chromium.launch({executablePath:process.env.CHROME || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',args:['--force-device-scale-factor=2']});
 const report=requested&&fs.existsSync(output)?JSON.parse(fs.readFileSync(output)):{};
 try{for(const deck of catalog.filter(deck=>!requested||requested.includes(deck.source_slug))){
  const page=await browser.newPage({viewport:{width:1440,height:810},deviceScaleFactor:2});
  const slides=process.env.SLIDE_NUMBERS?report[deck.source_slug].slides:[];
  await page.goto(`${base}/slides/${deck.source_slug}`,{waitUntil:'domcontentloaded'});
  const indices=process.env.SLIDE_NUMBERS?process.env.SLIDE_NUMBERS.split(',').map(n=>Number(n)-1):Array.from({length:reference[deck.source_slug].count},(_,i)=>i);
  for(const i of indices){
   const fragment=reference[deck.source_slug].slides[i].fragments-1;
   await page.goto(`${base}/slides/${deck.source_slug}#/slide/${i+1}${fragment>=0?'/f/'+fragment:''}`);
   await page.waitForFunction(i=>document.querySelectorAll('.slidewire-frame')[i]?.classList.contains('is-active'),i);
   await page.evaluate(()=>document.fonts.ready);
   await page.waitForFunction(i=>[...document.querySelectorAll('.slidewire-frame')[i].querySelectorAll('[data-slidewire-diagram]')].every(e=>e.hasAttribute('data-processed')&&e.querySelector('svg')),i);
   if(fragment>=0)await page.waitForFunction(i=>[...document.querySelectorAll('.slidewire-frame')[i].querySelectorAll('[data-fragment]')].every(e=>e.classList.contains('slidewire-fragment-visible')),i);
   const frame=page.locator('.slidewire-frame').nth(i);
   await frame.evaluate(async f=>Promise.all([...f.querySelectorAll('img')].map(img=>img.decode().catch(()=>{}))));
   await page.waitForTimeout(await frame.locator('[data-dw-motion]').count()?1200:150);
   slides[i]=await frame.evaluate((f,n)=>{
    const body=f.querySelector('.sw-body');
    return {slide:n,scroll:!!body&&(body.scrollHeight>body.clientHeight+1||body.scrollWidth>body.clientWidth+1),overflow:f.scrollHeight>f.clientHeight+1||f.scrollWidth>f.clientWidth+1,embeds:f.querySelectorAll('iframe,video,canvas').length,missingImages:[...f.querySelectorAll('img')].filter(e=>!e.complete||!e.naturalWidth).map(e=>e.src)};
   },i+1);
   const design=await frame.evaluate(auditDesignSystem);
   slides[i].clipping=design.clipping;
   slides[i].svgLabels=design.svgLabels;
  }
  assert.equal(slides.length,reference[deck.source_slug].count);
  const blocked=slides.filter(s=>s.scroll||s.overflow||s.embeds||s.missingImages.length||s.clipping?.length||s.svgLabels?.length);
  report[deck.source_slug]={id:deck.id,count:slides.length,ready:blocked.length===0,blocked,slides};
  fs.writeFileSync(output,JSON.stringify(report,null,2));
  console.log(deck.source_slug,blocked.length?'review '+blocked.map(s=>s.slide).join(','):'ready');
  await page.close();
 }}finally{await browser.close();}
})().catch(error=>{console.error(error);process.exitCode=1});
