// Detect clipping within internal containers, rather than only the full frame.
const {chromium}=require(process.env.PLAYWRIGHT_MODULE||'playwright');
const {auditDesignSystem}=require('../../bin/browser/design.cjs');
const fs=require('node:fs');
const decks=JSON.parse(fs.readFileSync('tests/fixtures/presentation-content.json')).decks;
(async()=>{const browser=await chromium.launch({executablePath:process.env.CHROME||'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',args:['--force-device-scale-factor=2']});const report={};
try{for(const[slug,deck]of Object.entries(decks)){
const page=await browser.newPage({viewport:{width:1440,height:900},deviceScaleFactor:2}),rows=[];
await page.goto((process.env.SLIDEWIRE_URL||'http://127.0.0.1:8017')+'/slides/'+slug,{waitUntil:'domcontentloaded'});
for(let i=0;i<deck.count;i++){
 const fragment=deck.slides[i].fragments-1;
 await page.goto(`${process.env.SLIDEWIRE_URL||'http://127.0.0.1:8017'}/slides/${slug}#/slide/${i+1}${fragment>=0?'/f/'+fragment:''}`);
 await page.waitForFunction(i=>document.querySelectorAll('.slidewire-frame')[i]?.classList.contains('is-active'),i);
 await page.evaluate(()=>document.fonts.ready);
 await page.waitForFunction(i=>[...document.querySelectorAll('.slidewire-frame')[i].querySelectorAll('[data-slidewire-diagram]')].every(node=>node.hasAttribute('data-processed')&&node.querySelector('svg')),i);
 if(fragment>=0)await page.waitForFunction(i=>[...document.querySelectorAll('.slidewire-frame')[i].querySelectorAll('[data-fragment]')].every(node=>node.classList.contains('slidewire-fragment-visible')),i);
 const frame=page.locator('.slidewire-frame').nth(i);
 await page.waitForTimeout(await frame.locator('[data-dw-motion]').count()?1200:150);
 const design=await frame.evaluate(auditDesignSystem);rows.push({slide:i+1,clipping:design.clipping,svgLabels:design.svgLabels});
}
report[slug]=rows;fs.writeFileSync('storage/app/design-system/content-bounds.json',JSON.stringify(report,null,2));
console.log(slug,rows.filter(row=>row.clipping.length||row.svgLabels.length).map(row=>row.slide));await page.close();
}}finally{await browser.close()}})().catch(error=>{console.error(error);process.exitCode=1});
