// Historical footer and 16:9 composition regression checks; local app only.
const {chromium}=require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert=require('node:assert/strict');
const fs=require('node:fs');
(async()=>{const browser=await chromium.launch({executablePath:process.env.CHROME || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',args:['--force-device-scale-factor=2']});try{
const page=await browser.newPage({viewport:{width:1440,height:810},deviceScaleFactor:2});const rows=[];
for(let i=0;i<20;i++){
 await page.goto(`http://127.0.0.1:8017/slides/symfony-ai-in-action#/slide/${i+1}`);
 await page.waitForFunction(i=>document.querySelectorAll('.slidewire-frame')[i]?.classList.contains('is-active'),i);
 await page.evaluate(()=>document.fonts.ready);
 const result=await page.locator('.slidewire-frame.is-active').evaluate(frame=>{
  const footer=frame.querySelector('footer'),label=footer.querySelector('.sw-footer-label'),author=footer.querySelector('.sw-footer-brand > span'),logo=footer.querySelector('img'),body=frame.querySelector('.sw-body'),inner=frame.querySelector('.sw-body-inner');
  const type=n=>{const s=getComputedStyle(n);return [s.fontFamily,s.fontSize,s.fontWeight,s.lineHeight,s.letterSpacing]};
  const rect=logo.getBoundingClientRect(),style=getComputedStyle(logo);
  return {left:type(label),right:type(author),logo:{width:rect.width,height:rect.height,radius:style.borderTopLeftRadius,border:style.borderTopWidth},label:label.textContent,bodyScroll:body?body.scrollHeight>body.clientHeight+1:false,overflow:frame.scrollHeight>frame.clientHeight+1,center:body&&inner?(inner.getBoundingClientRect().top+inner.getBoundingClientRect().bottom-body.getBoundingClientRect().top-body.getBoundingClientRect().bottom)/2:0,overlap:body?inner.getBoundingClientRect().bottom>footer.getBoundingClientRect().top:false};
 });
 assert.deepEqual(result.left,result.right,`slide ${i+1} footer typography`);assert.ok(result.left[0].startsWith('Verdana'));assert.equal(result.left[1],'25.5px');assert.equal(result.left[2],'400');assert.equal(result.logo.width,60);assert.equal(result.logo.height,60);assert.ok(Math.abs(parseFloat(result.logo.radius)-10)<0.01);assert.ok(Math.abs(parseFloat(result.logo.border)-2.5)<0.01);assert.equal(result.bodyScroll,false,`slide ${i+1} scroll`);assert.equal(result.overflow,false);assert.equal(result.overlap,false);assert.ok(Math.abs(result.center)<1);rows.push(result);
}
await page.goto('http://127.0.0.1:8017/slides/flowvox-update-2026-05-17',{waitUntil:'networkidle'});
await page.waitForSelector('.mermaidTooltip',{state:'attached'});
assert.ok(await page.locator('.mermaidTooltip').evaluate(node=>node.textContent===''&&getComputedStyle(node).display==='none'));
assert.equal(await page.evaluate(()=>document.documentElement.scrollHeight),810,'empty diagram tooltip adds no print page');
fs.mkdirSync('storage/app/publications',{recursive:true});fs.writeFileSync('storage/app/publications/historical-format-validation.json',JSON.stringify(rows,null,2));console.log('20 slides: Verdana footer, bordered logo, no overflow at 1440×810; empty Mermaid tooltip adds no page');
}finally{await browser.close()}})().catch(e=>{console.error(e);process.exitCode=1});
