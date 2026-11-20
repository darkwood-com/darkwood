// Presentation only: no storage, account, recording or provider action.
const themes=['light','dark','sepia'];
const button=document.createElement('button');button.type='button';button.className='dw-theme-switcher';button.id='dw-theme-switcher';
const host=document.querySelector('header,.hero,.run-stream__controls')||document.querySelector('main')||document.body;host.prepend(button);
function apply(theme){document.documentElement.dataset.theme=theme;button.textContent={light:'Light',dark:'Dark',sepia:'Sepia'}[theme];button.setAttribute('aria-label','Colour theme: '+theme+'. Activate for next theme.');}
apply(document.documentElement.dataset.theme||'dark');button.addEventListener('click',()=>apply(themes[(themes.indexOf(document.documentElement.dataset.theme)+1)%themes.length]));
