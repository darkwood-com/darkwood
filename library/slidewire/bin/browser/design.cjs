function auditDesignSystem(frame) {
    const size = node => parseFloat(getComputedStyle(node).fontSize);
    const editable = node => !node.closest('pre,[data-code-surface],svg');
    const content = [...frame.querySelectorAll('.sw-body p,.sw-body li,.sw-body blockquote,.sw-body figcaption,.sw-body td,.sw-body th,.sw-content p,.sw-content li')].filter(editable);
    const typography = content.filter(node => Math.abs(size(node)-36)>0.01).map(node=>({tag:node.tagName,class:node.className,size:size(node),text:node.textContent.trim().slice(0,100)}));
    const borders = [];
    for (const node of frame.querySelectorAll('*')) {
        if (!node.checkVisibility({opacityProperty:true,visibilityProperty:true}) || node.closest('svg')) continue;
        const css=getComputedStyle(node);
        for(const edge of ['Top','Right','Bottom','Left']) {
            const width=parseFloat(css[`border${edge}Width`]);
            if(width>0 && Math.abs(width-2.5)>0.01) borders.push({tag:node.tagName,class:node.className,edge,width});
        }
    }
    const code=[...frame.querySelectorAll('[data-code-surface]')].map(surface=>{
        const pre=surface.querySelector('pre'),toolbar=surface.querySelector('.sw-code-toolbar');
        return {background:getComputedStyle(surface).backgroundColor,content:pre?getComputedStyle(pre).backgroundColor:null,toolbar:toolbar?getComputedStyle(toolbar).backgroundColor:null,theme:pre?.className,font:pre?getComputedStyle(pre).fontFamily:null};
    });
    const codeFailures=code.filter(row=>row.background!=='rgb(30, 30, 30)'||row.content!==row.background||(row.toolbar&&row.toolbar!==row.background)||!row.theme.includes('dark-plus'));
    const svgLabels=[];
    for(const node of frame.querySelectorAll('foreignObject')) {
        const box=(node.closest('.node,.edgeLabel,.cluster-label')||node).getBoundingClientRect();
        if(!box.width||!box.height||!node.textContent.trim())continue;
        const range=document.createRange();range.selectNodeContents(node);
        const text=range.getBoundingClientRect();
        if(text.left<box.left-2||text.top<box.top-2||text.right>box.right+2||text.bottom>box.bottom+2)svgLabels.push({text:node.textContent,width:box.width,height:box.height,textWidth:text.width,textHeight:text.height});
    }
    const svgStrokes=[...frame.querySelectorAll('svg :is(path,line,rect,circle,polygon,polyline,ellipse)')].filter(node=>node.checkVisibility({opacityProperty:true,visibilityProperty:true})&&getComputedStyle(node).stroke!=='none'&&Math.abs(parseFloat(getComputedStyle(node).strokeWidth)-2.5)>.01).map(node=>({tag:node.tagName,width:getComputedStyle(node).strokeWidth}));
    const clipping=[];
    for(const node of frame.querySelectorAll('.sw-body p,.sw-body li,.sw-body pre,.sw-body .slidewire-diagram svg')) {
        if(node.closest('svg')&&!node.matches('svg')||!node.textContent.trim()||!node.checkVisibility({opacityProperty:true,visibilityProperty:true}))continue;
        const rect=node.getBoundingClientRect();
        const own=getComputedStyle(node);
        if(['hidden','clip','auto','scroll'].includes(own.overflowY)&&node.scrollHeight>node.clientHeight+2||['hidden','clip','auto','scroll'].includes(own.overflowX)&&node.scrollWidth>node.clientWidth+2)clipping.push({tag:node.tagName,text:node.textContent.trim().slice(0,80),container:'self'});
        for(let parent=node.parentElement;parent&&!parent.classList.contains('sw-body');parent=parent.parentElement){
            const css=getComputedStyle(parent),box=parent.getBoundingClientRect();
            if(['hidden','clip','auto','scroll'].includes(css.overflowY)&&(rect.top<box.top-2||rect.bottom>box.bottom+2)||['hidden','clip','auto','scroll'].includes(css.overflowX)&&(rect.left<box.left-2||rect.right>box.right+2))clipping.push({tag:node.tagName,text:node.textContent.trim().slice(0,80),container:parent.className});
        }
    }
    const arrowSelectors='.dw-arrow,.sw-process-arrow,.sr-arrow,.sw-arrow,.hc-auth-arrow,.hc-big-arrow,.hc-loop-arrow,.hc-map-arrow,.hc-state-arrow,.pg-pipe > i';
    const arrowColor=frame.matches('.sw-slide--cover,.sw-slide--section')?'rgb(255, 255, 255)':'rgb(0, 123, 255)';
    const arrows=[...frame.querySelectorAll(arrowSelectors)].filter(node=>node.checkVisibility({opacityProperty:true,visibilityProperty:true})&&!node.closest('pre,code')).map(node=>({class:node.className,text:node.textContent.trim(),color:getComputedStyle(node).color}));
    const arrowFailures=arrows.filter(node=>node.color!==arrowColor);
    for(const node of frame.querySelectorAll('.hc-thesis-node,.sw-check')) {
        const css=getComputedStyle(node,'::after');
        if(css.display!=='none'&&css.content!=='none'&&css.color!==arrowColor)arrowFailures.push({class:node.className,pseudo:'::after',color:css.color});
    }
    const heading=frame.querySelector('h1');
    return {arrows,arrowFailures,typography,borders,svgLabels,svgStrokes,clipping,code,codeFailures,titleCount:frame.querySelectorAll('h1').length,title:heading?.textContent.trim(),blue:getComputedStyle(document.documentElement).getPropertyValue('--dw-blue').trim()};
}
module.exports={auditDesignSystem};
