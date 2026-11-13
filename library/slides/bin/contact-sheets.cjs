const fs=require('node:fs'),path=require('node:path'),{execFileSync}=require('node:child_process');
const root=path.resolve('storage/app/design-system');
const decks=JSON.parse(fs.readFileSync('tests/fixtures/presentation-content.json')).decks;
const metrics=JSON.parse(fs.readFileSync(path.join(root,'validation.json')));
const magick=process.env.MAGICK||'/nix/store/fa1y609caifi3sc1g7zd3n8w01nbyy6j-imagemagick-7.1.2-34/bin/magick';
const font=path.resolve('public/darkwood/fonts/Jost/Jost-400-Book.otf');
const tokens=fs.readFileSync('resources/css/themes/darkwood.css','utf8').match(/:root\s*\{[^}]+\}/)[0];
const escape=s=>String(s).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('"','&quot;');
let cards='';const overview=[];
for(const [slug,deck]of Object.entries(decks)){
 const directory=path.join(root,'renders',slug),files=Array.from({length:deck.count},(_,i)=>path.join(directory,`slide-${String(i+1).padStart(2,'0')}.png`));
 if(!files.every(file=>fs.existsSync(file)))throw Error('Incomplete capture '+slug);
 const args=['montage','-font',font,'-pointsize','16','-background','white','-fill','black'];
 files.forEach((file,i)=>args.push('-label',String(i+1).padStart(2,'0'),file));
 args.push('-thumbnail','360x225','-geometry','+5+5','-tile','4x',path.join(directory,'contact-sheet.png'));execFileSync(magick,args);
 overview.push('-label',slug,files[0]);
 const dense=(metrics[slug]?.slides||[]).filter(row=>row.bodyScroll).map(row=>row.slide);
 const reading=(metrics[slug]?.slides||[]).filter(row=>row.detailCaptures?.length).map(row=>`<li>Slide ${row.slide} : ${row.detailCaptures.map((file,i)=>`<a href="renders/${slug}/${file}">Vue ${i+1}</a>`).join(' · ')}</li>`).join('');
 cards+=`<article><h2>${escape(slug)}</h2><p>${deck.count} slides · <a href="renders/${slug}/contact-sheet.png">Planche contact</a></p><a href="renders/${slug}/contact-sheet.png"><img loading="lazy" src="renders/${slug}/contact-sheet.png" alt="${escape(slug)}"></a>${dense.length?`<p>Composition dense à examiner : ${dense.join(', ')}</p>`:''}${reading?`<details><summary>Lecture intégrale des compositions denses</summary><ul>${reading}</ul></details>`:''}</article>`;
}
execFileSync(magick,['montage','-font',font,'-pointsize','14','-background','white','-fill','black',...overview,'-thumbnail','360x225','-geometry','+5+5','-tile','4x',path.join(root,'overview.png')]);
fs.writeFileSync(path.join(root,'index.html'),`<!doctype html><html lang="fr"><meta charset="utf-8"><title>Slidewire — validation du design system</title><style>${tokens}body{font:18px Verdana,sans-serif;background:white;color:black;margin:32px}a{color:var(--dw-blue)}article{border-top:var(--dw-border-width) solid var(--dw-blue);padding:20px 0}img{max-width:100%;height:auto}</style><h1>Slidewire — design system</h1><p>${Object.keys(decks).length} présentations · 487 slides · captures 1440 × 900. Les compositions denses restent à valider avant export.</p><p><a href="overview.png">Vue d'ensemble</a> · <a href="validation.json">Contrôles calculés</a> · <a href="interactions.json">Interactions</a></p>${cards}</html>`);
console.log(`${Object.keys(decks).length} contact sheets and local review gallery generated`);
