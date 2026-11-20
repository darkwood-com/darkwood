// Public presentation only. No authentication, cookies or account state is changed.
if(document.body.matches('.layout-darkwood,.layout-paper')){
 const themes=['light','dark','sepia'];const host=document.querySelector('.theme-nav__actions');
 if(host){const button=document.createElement('button');button.type='button';button.id='dw-theme-switcher';button.className='dw-theme-switcher';host.prepend(button);
 const apply=theme=>{document.documentElement.dataset.theme=theme;button.textContent={light:'Clair',dark:'Sombre',sepia:'Sépia'}[theme];button.setAttribute('aria-label','Thème : '+button.textContent+'. Activer pour changer.');};
 apply(document.body.classList.contains('layout-darkwood')?'dark':'light');button.addEventListener('click',()=>apply(themes[(themes.indexOf(document.documentElement.dataset.theme)+1)%3]));}
}
