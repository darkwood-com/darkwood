const assert=require('node:assert/strict');
const {chromium}=require('playwright');
const {paginateStatic,showStaticPage}=require('../../bin/browser/static-pagination.cjs');
(async()=>{
 const browser=await chromium.launch({executablePath:process.env.CHROME||'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'});
 try{
 const page=await browser.newPage({viewport:{width:1440,height:810}});
 await page.setContent(`<style>*{box-sizing:border-box}body{margin:0}.sw-slide{height:810px}.sw-canvas{padding:40px 90px 110px;height:810px;display:grid;grid-template-rows:auto minmax(0,1fr)}.sw-body{overflow:auto}.sw-body-inner{font:36px/1.2 Arial}p{margin:20px 0}[data-board]{width:100px;height:100px;border:2.5px solid blue}</style><article class="sw-slide"><section class="sw-canvas"><header><h1>Canonical heading</h1></header><div class="sw-body"><div class="sw-body-inner"><div data-board aria-label="Board A">A</div><div data-board aria-label="Board B">B</div><pre data-slidewire-diagram><svg xmlns="http://www.w3.org/2000/svg" width="120" height="80"><rect width="120" height="80" fill="blue"/></svg></pre><pre data-slidewire-diagram data-slidewire-diagram-src="sequenceDiagram&#10; participant Loop as EventLoop"><svg width="120" height="80"><text class="error-text">Syntax error</text></svg></pre>${Array.from({length:8},(_,i)=>`<p>Paragraph ${i}: ${'Complete content retained in order. '.repeat(8)}</p>`).join('')}<ol start="4"><li>Ordered item four</li><li>Ordered item five</li></ol></div></div></section></article>`);
 await page.evaluate(()=>{window.diagramRebuilds=0;new MutationObserver(()=>{for(const node of document.querySelectorAll('[data-slidewire-diagram]'))if(!node.querySelector('svg')){window.diagramRebuilds++;node.textContent='Unexpected diagram rebuild';}}).observe(document.body,{childList:true,subtree:true});});
 const frame=page.locator('.sw-slide');
 const plan=await paginateStatic(frame,{slide:7,route:'/slides/example',interactiveUrl:'https://slides.darkwood.com/slides/example',interactivePending:true});
 await page.waitForTimeout(150);
 assert.equal(await frame.locator('[data-slidewire-diagram]').count(),0);
 assert.equal(await page.evaluate(()=>window.diagramRebuilds),0);
 assert(plan.source_text.includes('participant Loop as EventLoop'));
 assert(!plan.source_text.includes('Syntax error'));
 assert(plan.pages>1);
 assert.equal(plan.snapshots.length,2);
 assert.equal(await frame.locator('[data-board]').count(),0);
 const text=[];
 for(let i=0;i<plan.pages;i++){
  await showStaticPage(frame,i,7);
  text.push(await frame.locator('.sw-body-inner').textContent());
  assert.equal(await frame.evaluate(f=>f.querySelector('.sw-body-inner').scrollHeight<=f.querySelector('.sw-body').clientHeight),true);
 }
 assert.equal(text.join('').replace(/\s+/g,' ').trim(),plan.source_text);
 assert((await frame.locator('.sw-export-link').textContent()).includes('mise en ligne à venir'));
 console.log(JSON.stringify({pages:plan.pages,complete_text:true,order:true,bounds:true}));
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1});
