// Export-only projection. Source Blade, assets and the interactive reader are never edited.
const fs=require('node:fs');
const path=require('node:path');
async function paginateStatic(frame, {slide, route, interactiveUrl, interactivePending, snapshotFile}) {
    // The reader observes DOM changes and would otherwise replace frozen SVG images.
    await frame.evaluate(f=>f.querySelectorAll('[data-slidewire-diagram]').forEach(e=>{
        const source=e.getAttribute('data-slidewire-diagram-src');
        if(source&&e.querySelector('.error-icon,.error-text')){
            const group=document.createElement('div'),label=document.createElement('p'),pre=document.createElement('pre'),code=document.createElement('code');
            label.textContent='Diagramme : notation source conservée (rendu graphique indisponible).';
            pre.style.cssText='background:#1e1e1e;color:#d4d4d4;padding:24px;font-family:Consolas,monospace;white-space:pre-wrap';
            code.textContent=source;pre.append(code);group.append(label,pre);e.replaceWith(group);
        }else{e.removeAttribute('data-slidewire-diagram');e.removeAttribute('data-slidewire-diagram-src');}
    }));
    const cache=snapshotFile&&fs.existsSync(snapshotFile)?JSON.parse(fs.readFileSync(snapshotFile)): {boards:[],svgs:[]};
    // Freeze game boards and media at their observed initial state, retaining their links.
    await frame.evaluate(f=>{for(const e of f.querySelectorAll('.sw-body div,.sw-body section')){const css=getComputedStyle(e),box=e.getBoundingClientRect();if(css.aspectRatio!=='auto'&&box.width>100&&box.height>100&&e.textContent.trim().length<1000)e.setAttribute('data-export-visual','');}});
    const selector = '[data-board],[data-pg-board],[data-pg-compass],[data-pg-faces],[data-pg-mini],[data-export-visual],div[aria-label],section[aria-label],.sw-board-shell,.sr-stage,.sw-timeline,.sw-stream-tree,.sw-process,.sw-flow,table,[role="table"],canvas,video,iframe';
    const candidates = frame.locator(`.sw-body ${selector.split(',').join(', .sw-body ')}`);
    const items=await candidates.elementHandles();
    const snapshots = [];
    for (let i = 0; i < items.length; i++) {
        const item = items[i];
        if (!await item.isVisible()) continue;
        const meta = await item.evaluate((e,selector) => ({tag:e.tagName,src:e.getAttribute('src'),label:e.getAttribute('aria-label')||e.getAttribute('title')||(e.matches('table,[role="table"],.sw-timeline,.sw-stream-tree,.sw-process,.sw-flow')?'Schéma ou tableau':'Démonstration interactive'),legend:e.matches('[data-board],[data-pg-board],.sw-board-shell,canvas,video,iframe')?'':e.innerText.replace(/\s+/g,' ').trim(),nested:!!e.parentElement.closest(selector)}),selector);
        if (meta.nested) continue;
        let image = null;
        if (meta.tag !== 'IFRAME') image = cache.boards[i] || (await item.screenshot({animations:'disabled'})).toString('base64');
        cache.boards[i]=image;
        await item.evaluate((e,{image,meta}) => {
            const figure=document.createElement('figure');figure.style.margin='16px 0';figure.setAttribute('data-export-atomic-group','');
            if(image){const img=document.createElement('img');img.src='data:image/png;base64,'+image;img.alt=meta.label;img.setAttribute('data-export-atomic','');figure.append(img);}
            const label=document.createElement('figcaption');label.textContent=meta.tag==='IFRAME'?'Démonstration externe : '+meta.label+' (consulter la version interactive).':'Capture statique : '+meta.label;figure.append(label);
            if(meta.src && /^https:\/\//.test(meta.src)){const link=document.createElement('a');link.href=meta.src;link.textContent=meta.src;figure.append(link);}
            e.replaceWith(figure);
            if(meta.legend){const legend=document.createElement('p');legend.textContent='Lecture du schéma : '+meta.legend;figure.after(legend);}
        },{image,meta});
        snapshots.push(meta);
    }
    // Use self-contained SVG images, avoiding duplicate IDs across range fragments.
    cache.svgs=await frame.evaluate((f,cached)=>{
        const urls=[];
        for(const svg of f.querySelectorAll('.sw-body svg')){
            const clone=svg.cloneNode(true), originals=[svg,...svg.querySelectorAll('*')], copies=[clone,...clone.querySelectorAll('*')];
            originals.forEach((node,i)=>{const css=getComputedStyle(node);for(const key of ['fill','stroke','stroke-width','font-family','font-size','font-weight','color','background-color'])copies[i].style.setProperty(key,css.getPropertyValue(key));});
            clone.setAttribute('xmlns','http://www.w3.org/2000/svg');
            const box=svg.getBoundingClientRect();clone.setAttribute('width',String(Math.max(1,box.width)));clone.setAttribute('height',String(Math.max(1,box.height)));
            const img=document.createElement('img');img.src=cached[urls.length]||'data:image/svg+xml;charset=utf-8,'+encodeURIComponent(new XMLSerializer().serializeToString(clone));urls.push(img.src);img.alt=svg.textContent.trim();img.setAttribute('data-export-atomic','');svg.replaceWith(img);
        }
        f.querySelectorAll('.sw-body img').forEach(img=>img.setAttribute('data-export-atomic',''));
        return urls;
    },cache.svgs);
    const result = await frame.evaluate((f,{slide,route,interactiveUrl,interactivePending})=>{
        const body=f.querySelector('.sw-body'), target=body?.querySelector('.sw-body-inner');
        if(!body||!target)throw Error('Static projection requires a canonical body');
        f.classList.add('sw-export-static');
        const style=document.createElement('style');style.textContent=`
        .sw-export-static .sw-canvas{height:810px!important;min-height:0!important;display:grid!important;grid-template-rows:auto minmax(0,1fr)!important;align-items:stretch!important;justify-content:stretch!important}
        .sw-export-static .sw-header h1{font-size:var(--darkwood-heading)!important}
        .sw-export-static .sw-body-inner [data-pg-promo-cta]{display:block!important;opacity:1!important;transform:none!important}
        .sw-export-static .sw-header{grid-row:1!important}
        .sw-export-static .sw-body{grid-row:2!important;height:auto!important;overflow:hidden!important;display:block!important;padding-top:16px!important}
        .sw-export-static .sw-body-inner{margin:0!important;height:auto!important;min-height:0!important}
        .sw-export-static .sw-body-inner *:not(svg):not(svg *){max-height:none!important;min-height:0!important;min-width:0!important;max-width:100%!important;overflow-wrap:anywhere!important;overflow:visible!important;animation:none!important;transition:none!important;box-sizing:border-box!important;transform:none!important;translate:none!important;rotate:none!important;scale:none!important}
        .sw-export-static .sw-body-inner :is(div,section,article,aside,main,ul,ol,li,p,pre,figure){height:auto!important;position:static!important;inset:auto!important}
        .sw-export-static .sw-body-inner :is(div,section,article,aside,main,ul,ol){display:block!important;width:100%!important;max-width:100%!important;grid-template-columns:none!important;gap:16px!important;margin-inline:0!important}
        .sw-export-static .sw-body-inner :is(p,li,h2,h3,h4,pre,figure){width:auto!important;max-width:100%!important}
        .sw-export-static .sw-body-inner :is(pre,code){white-space:pre-wrap!important;overflow-wrap:anywhere!important;word-break:break-word!important}
        .sw-export-static .sw-body-inner img[data-export-atomic]{max-width:100%!important;max-height:var(--export-atomic-height)!important;width:auto!important;height:auto!important;object-fit:contain!important;position:static!important}
        .sw-export-static .sw-body-inner :is([data-sw-reveal],[data-dw-motion]){opacity:1!important;transform:none!important}
        .sw-export-source{display:none!important}
        .sw-export-label{position:absolute;right:32px;top:8px;font:14px Verdana,sans-serif;color:inherit}
        .sw-export-link{position:absolute;left:38px;bottom:4px;font:14px Verdana,sans-serif;color:inherit;max-width:1300px}
        `;document.head.append(style);
        for(const s of target.querySelectorAll('style'))document.head.append(s.cloneNode(true));
        const source=document.createElement('div');source.className='sw-export-source';source.innerHTML=target.innerHTML;source.querySelectorAll('style,script').forEach(e=>e.remove());
        for(const e of source.querySelectorAll('*'))if(e.textContent.trim()||e.querySelector('img'))e.setAttribute('data-export-has-content','');
        for(const ol of source.querySelectorAll('ol'))[...ol.children].forEach((li,i)=>li.setAttribute('data-export-list-index',String(Number(ol.start||1)+i)));
        const note=document.createElement('div');note.className='sw-export-link';note.textContent=interactivePending?'Version interactive (mise en ligne à venir) : ':'Version interactive : ';const a=document.createElement('a');a.href=interactiveUrl||route;a.textContent=interactiveUrl||'Slidewire '+route;note.append(a);f.append(note);
        f.append(source);target.replaceChildren();
        const available=body.clientHeight-20;if(available<80)throw Error('Header leaves insufficient space for a readable static page '+JSON.stringify({height:body.clientHeight,header:f.querySelector('.sw-header')?.getBoundingClientRect().height}));
        f.style.setProperty('--export-atomic-height',Math.max(50,available-140)+'px');
        const points=[{node:source,offset:0}];
        function walk(node){
            if(node.nodeType===3){const text=node.textContent;for(const match of text.matchAll(/\S+\s*/g))points.push({node,offset:match.index+match[0].length});return;}
            if(node.nodeType!==1)return;
            if(node.matches('img,br,hr,input,[data-export-atomic-group]')){points.push({node:node.parentNode,offset:[...node.parentNode.childNodes].indexOf(node)+1});return;}
            for(const child of node.childNodes)walk(child);
        }
        for(const child of source.childNodes)walk(child);
        points.push({node:source,offset:source.childNodes.length});
        function show(start,end){
            const r=document.createRange();r.setStart(points[start].node,points[start].offset);r.setEnd(points[end].node,points[end].offset);target.replaceChildren(r.cloneContents());
            body.scrollTop=0;body.scrollLeft=0;
            for(const e of [...target.querySelectorAll('[data-export-has-content]')].reverse())if(!e.textContent.trim()&&!e.querySelector('img')&&!e.matches('img'))e.remove();
            for(const ol of target.querySelectorAll('ol')){const first=ol.querySelector('[data-export-list-index]');if(first)ol.start=Number(first.getAttribute('data-export-list-index'));}
        }
        function fits(){
            if(target.scrollHeight>available||target.scrollWidth>body.clientWidth+1)return false;
            const box=body.getBoundingClientRect();
            for(const e of target.querySelectorAll('p,li,pre,img,td,th,h1,h2,h3,h4')){if(!e.checkVisibility())continue;const b=e.getBoundingClientRect();if(b.bottom>box.top+available+1||b.right>box.right+1||b.left<box.left-1)return false;}
            return true;
        }
        const ranges=[];let start=0;
        while(start<points.length-1){
            let low=start+1,high=points.length-1,best=start;
            while(low<=high){const mid=Math.floor((low+high)/2);show(start,mid);if(fits()){best=mid;low=mid+1;}else high=mid-1;}
            if(best===start)throw Error('Unbreakable static content exceeds page '+(ranges.length+1)+' '+JSON.stringify({available,bodyWidth:body.clientWidth,height:target.scrollHeight,width:target.scrollWidth,first:target.innerHTML.slice(0,240)}));
            show(start,best);if(!target.textContent.trim()&&!target.querySelector('img')){start=best;continue;}
            ranges.push([start,best]);start=best;
            if(ranges.length>100)throw Error('Excessive pagination; review composition');
        }
        const label=document.createElement('span');label.className='sw-export-label';f.append(label);
        // Retain source nodes and ranges only in the isolated export page.
        f._staticProjection={points,ranges,source,target,label,show};
        show(...ranges[0]);label.textContent='Slide '+slide+' · 1/'+ranges.length+' · représentation statique';
        return {pages:ranges.length,source_text:source.textContent.replace(/\s+/g,' ').trim(),route,interactive_url:interactiveUrl||null};
    },{slide,route,interactiveUrl,interactivePending});
    await frame.locator('.sw-body img').evaluateAll(imgs=>Promise.all(imgs.map(img=>img.decode())));
    if(snapshotFile&&!fs.existsSync(snapshotFile)){fs.mkdirSync(path.dirname(snapshotFile),{recursive:true});fs.writeFileSync(snapshotFile,JSON.stringify(cache));}
    return {...result,snapshots};
}
async function showStaticPage(frame,index,slide){
    await frame.evaluate((f,{index,slide})=>{const p=f._staticProjection;p.show(...p.ranges[index]);p.label.textContent='Slide '+slide+' · '+(index+1)+'/'+p.ranges.length+' · représentation statique';},{index,slide});
    await frame.locator('.sw-body img').evaluateAll(imgs=>Promise.all(imgs.map(img=>img.decode())));
}
module.exports={paginateStatic,showStaticPage};
