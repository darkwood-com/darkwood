const fs = require('node:fs');
const http = require('node:http');
const path = require('node:path');
const assert = require('node:assert/strict');
const { chromium } = require('playwright');
const root = path.resolve(__dirname, '../..');
const template = fs.readFileSync(path.join(root, 'templates/admin/base.html.twig'), 'utf8');
const assets = [...template.matchAll(/<(?:link|script)[^>]+(?:href|src)="\{\{ asset\('([^']+)'\) \}\}"[^>]*(?:><\/script>|\/?>)/g)].map(m => m[0].replace(/\{\{ asset\('([^']+)'\) \}\}/g, '$1'));
const fixture = `<!doctype html><html><head><meta charset="utf-8">${assets.join('\n')}</head><body><div id="page-container"><div class="panel-body"><form id="sample"><textarea class="ckeditor" id="article" name="article[body]"><p>Texte <strong>initial</strong></p></textarea><input class="date" id="date"><select id="select" class="selectpicker"><option value="a">A</option><option value="b">B</option></select><select id="multi" class="multiselect" multiple><option value="a">A</option><option value="b">B</option></select><input type="checkbox" class="switch-button" id="switch"><input id="tags" value="initial"><a data-toggle="dropdown" href="#" class="dropdown-toggle">Menu</a><ul class="dropdown-menu"><li>Item</li></ul></form></div></div><script>$(function(){App.init();$('#tags').tagit();});</script></body></html>`;
const mime = {'.js':'text/javascript','.css':'text/css','.svg':'image/svg+xml','.png':'image/png','.woff2':'font/woff2','.woff':'font/woff','.html':'text/html'};
const failures = [], errors = [];
const server = http.createServer((req,res) => {
  if(req.url === '/'){res.setHeader('Content-Type','text/html');return res.end(fixture);}
  const requested=decodeURIComponent(req.url.split('?')[0]);
  const file=path.resolve(root,'public','.'+requested);
  if(!file.startsWith(path.join(root,'public')+path.sep)||!fs.existsSync(file)||!fs.statSync(file).isFile()){res.statusCode=404;return res.end();}
  res.setHeader('Content-Type',mime[path.extname(file)]||'application/octet-stream');fs.createReadStream(file).pipe(res);
});
(async()=>{
 await new Promise(r=>server.listen(0,'127.0.0.1',r));
 const executablePath = process.env.ADMIN_BROWSER_PATH || ['/Applications/Google Chrome.app/Contents/MacOS/Google Chrome', '/usr/bin/chromium', '/usr/bin/chromium-browser'].find(file => fs.existsSync(file));
 const browser=await chromium.launch({executablePath,headless:true});
 try {
  const page=await browser.newPage({viewport:{width:1280,height:900}});
  await page.route('**/*',route => new URL(route.request().url()).hostname==='127.0.0.1'?route.continue():route.abort());
  page.on('pageerror',e=>errors.push(e.message));
  page.on('response',r=>{if(r.status()>=400&&!r.url().endsWith('favicon.ico'))failures.push(r.url());});
  await page.goto(`http://127.0.0.1:${server.address().port}/`,{waitUntil:'networkidle'});
  await page.waitForSelector('.trumbowyg-editor');
  assert.equal(await page.evaluate(()=>jQuery.fn.jquery),'3.7.1');
  assert.equal(await page.evaluate(()=>jQuery.fn.tooltip.Constructor.VERSION),'3.4.1');
  await page.locator('.trumbowyg-editor').fill('Nouveau texte français');
  await page.locator('.trumbowyg-editor').press('Tab');
  assert.match(await page.locator('#article').inputValue(),/Nouveau texte français/);
  assert.match(await page.locator('.editor-count').textContent(),/mots.*caractères/);
  await page.locator('.trumbowyg-viewHTML-button').click();
  await page.waitForSelector('.trumbowyg-editor-hidden');
  await page.locator('#article').fill('<p>Source <strong>conservée</strong></p>');
  const html=await page.evaluate(()=>{document.querySelector('#sample').addEventListener('submit',e=>e.preventDefault());document.querySelector('#sample').requestSubmit();return new FormData(document.querySelector('#sample')).get('article[body]');});
  assert.match(html,/<strong>conservée<\/strong>/);
  await page.evaluate(()=>{jQuery('#multi').multiselect('select',['a','b']);jQuery('#switch').bootstrapSwitch('state',true);jQuery('#date').datepicker('setDate',new Date(2026,10,18));jQuery('#select').selectpicker('val','b');jQuery('#tags').tagit('createTag','nouveau');});
  assert.deepEqual(await page.locator('#multi').evaluate(el=>[...el.selectedOptions].map(o=>o.value)),['a','b']);
  assert.equal(await page.locator('#switch').isChecked(),true);
  assert.equal(await page.locator('#date').inputValue(),'18/11/2026');
  assert.equal(await page.locator('#select').inputValue(),'b');
  assert.match(await page.locator('#tags').inputValue(),/nouveau/);
  await page.locator('a[data-toggle="dropdown"]').click();
  assert.equal(await page.locator('a[data-toggle="dropdown"] + .dropdown-menu').isVisible(),true);
  await page.waitForTimeout(300);
  if (process.env.ADMIN_SCREENSHOT) await page.screenshot({path:process.env.ADMIN_SCREENSHOT,fullPage:true});
  assert.deepEqual(failures,[]);assert.deepEqual(errors,[]);
  console.log(JSON.stringify({status:'ok',asset_tags:assets.length,checks:['jquery3','bootstrap3','rich-text','visual-textarea-sync','html-source-submit','word-count','date-fr','multiselect','selectpicker','switch','tags','dropdown'],page_errors:errors,missing_assets:failures}));
 } finally {await browser.close();server.close();}
})().catch(e=>{console.error(e);console.error({errors,failures});server.close();process.exitCode=1;});
