// Validate the complete measured browser run, including every saved PNG.
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict');
const root=path.resolve('storage/app/design-system');
const reference=JSON.parse(fs.readFileSync('tests/fixtures/presentation-content.json')).decks;
const measured=JSON.parse(fs.readFileSync(path.join(root,'validation.json')));
const bounds=JSON.parse(fs.readFileSync(path.join(root,'content-bounds.json')));
const summary={decks:0,slides:0,dense:0,codeBlocks:0,embeddedDependencies:[],presentations:[]};
for(const [slug,expected]of Object.entries(reference)){
 const deck=measured[slug];
 assert.ok(deck,slug+' missing');
 assert.equal(deck.slides.length,expected.count,slug+' count');
 assert.equal(bounds[slug]?.length,expected.count,slug+' content bounds coverage');
 assert.deepEqual(deck.errors,[],slug+' JavaScript errors');
 const source=fs.readFileSync(`resources/views/pages/slides/${slug}.blade.php`,'utf8');
 const embeds=new Set([...source.matchAll(/<iframe\b[^>]*\bsrc="([^"]+)"/g)].map(match=>match[1].replaceAll('&amp;','&')));
 const dependencyFailures=deck.assets.filter(asset=>embeds.has(asset.url));
 assert.deepEqual(deck.assets.filter(asset=>!embeds.has(asset.url)),[],slug+' failed asset requests');
 summary.embeddedDependencies.push(...dependencyFailures.map(asset=>({slug,...asset})));
 for(const row of deck.slides){
  const label=slug+' slide '+row.slide;
  assert.deepEqual(row.palette.violations,[],label+' palette');
  assert.deepEqual(row.design.arrowFailures,[],label+' arrow colours');
  assert.deepEqual(row.design.typography,[],label+' 36px typography');
  assert.deepEqual(row.design.borders,[],label+' 2.5px borders');
  assert.deepEqual(row.design.svgLabels||[],[],label+' SVG label bounds');
  assert.deepEqual(row.design.svgStrokes||[],[],label+' SVG stroke widths');
  assert.deepEqual(row.design.clipping||[],[],label+' concealed content');
  assert.deepEqual(bounds[slug][row.slide-1].clipping,[],label+' internal clipping');
  assert.deepEqual(bounds[slug][row.slide-1].svgLabels,[],label+' measured SVG bounds');
  assert.deepEqual(row.design.codeFailures,[],label+' code surface');
  assert.equal(row.design.blue.toLowerCase(),'#007bff',label+' native blue');
  assert.equal(row.frameOverflow,false,label+' frame overflow');
  assert.notEqual(row.horizontal,true,label+' horizontal overflow');
  assert.deepEqual(row.missingImages,[],label+' missing images');
  assert.ok(row.h1<=1,label+' title hierarchy');
  assert.ok(row.footer,label+' footer');
  if(!row.bodyScroll&&row.center!==null)assert.ok(Math.abs(row.center)<1,label+' body centering');
  const png=fs.readFileSync(path.join(root,'renders',slug,`slide-${String(row.slide).padStart(2,'0')}.png`));
  assert.equal(png.readUInt32BE(16),1440,label+' PNG width');
  assert.equal(png.readUInt32BE(20),900,label+' PNG height');
  summary.codeBlocks+=row.design.code.length;
 }
 const dense=deck.slides.filter(row=>row.bodyScroll).map(row=>row.slide);
 summary.presentations.push({slug,count:deck.slides.length,dense,contactSheet:`storage/app/design-system/renders/${slug}/contact-sheet.png`});
 summary.decks++;summary.slides+=deck.slides.length;summary.dense+=dense.length;
}
assert.equal(summary.decks,28);assert.equal(summary.slides,487);
fs.writeFileSync(path.join(root,'summary.json'),JSON.stringify(summary,null,2));
console.log(`${summary.decks} decks / ${summary.slides} slides: palette, typography, borders, code, assets, frame bounds and centering passed; ${summary.dense} dense compositions require editorial review.`);
