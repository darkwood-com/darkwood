const fs=require('node:fs'),path=require('node:path'),{execFileSync}=require('node:child_process');
const root=path.resolve('storage/app/darkwood-migration'),pilot=path.resolve('storage/app/slide-renders/symfony-ai-in-action/revision-6');const ref=JSON.parse(fs.readFileSync('tests/fixtures/darkwood-migration-baseline.json')).decks;
const magick=process.env.MAGICK||'/nix/store/fa1y609caifi3sc1g7zd3n8w01nbyy6j-imagemagick-7.1.2-34/bin/magick',font=path.resolve('public/darkwood/fonts/Jost/Jost-400-Book.otf');
function sheet(name,tiles,columns,width){const args=['montage','-font',font,'-pointsize','18','-background','white','-fill','black'];for(const [label,file]of tiles)args.push('-label',label,file);args.push('-thumbnail',`${width}x${Math.round(width*900/1440)}`,'-geometry','+8+8','-tile',`${columns}x`,path.join(root,name));execFileSync(magick,args);}
const slide=(slug,n)=>path.join(root,'renders',slug,`slide-${String(n).padStart(2,'0')}.png`);
sheet('overview.png',Object.keys(ref).map(s=>[s.replaceAll('-',' ').replace(/(.{1,32})(\s|$)/g,'$1\n').trim(),slide(s,1)]),4,360);
sheet('reference-comparisons.png',[
 ['Historique — Hero',path.join(pilot,'historical-cover.png')],['Migration — Hero Flow Tokens',slide('flow-pipe',1)],
 ['Historique — titre et colonnes',path.join(pilot,'historical-columns.png')],['Migration — contenu Laravel',slide('laravel-pipeline-flow',9)],
 ['Historique — titre de section',path.join(pilot,'historical-section.png')],['Migration — couverture standard',slide('from-nolife-testing-to-behavioral-testing',1)],
 ['Pilote R6 — code',path.join(pilot,'slide-06.png')],['Migration — code PHP',slide('laravel-pipeline-flow',4)],
],2,720);
sheet('before-after.png', ['flow-pipe','darkwaar27-pigment','holding-the-core-security-engineering-through-darkwaar40-breach','strata-relay'].flatMap(s=>[[s+' — avant',path.join(root,'baseline',s+'.png')],[s+' — après',slide(s,1)]]),2,720);
console.log('Overview, historical comparisons and before/after generated');
