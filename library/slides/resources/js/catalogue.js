// Home-page shell only. Deck scripts execute inside their own iframe.
const body = document.body;
const menu = document.getElementById('library-menu');
const sidebar = document.getElementById('library-sidebar');
const main = document.getElementById('presentation');
const frame = document.getElementById('deck-player');
const links = [...sidebar.querySelectorAll('a[data-deck-url]')];
const mobile = matchMedia('(max-width:575px)');
function setMenu(open) {
    body.classList.toggle('menu-is-open', open);
    menu.setAttribute('aria-expanded', String(open));
    menu.setAttribute('aria-label', open ? 'Fermer les présentations' : 'Ouvrir les présentations');
    main.inert = open;
    if (open) sidebar.querySelector('[aria-current="page"]')?.scrollIntoView({block:"nearest"});
}
menu.addEventListener('click', () => setMenu(menu.getAttribute('aria-expanded') !== 'true'));
mobile.addEventListener('change', () => setMenu(false));
document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && body.classList.contains('menu-is-open')) { setMenu(false); menu.focus(); }
    if (e.key === 'Tab' && body.classList.contains('menu-is-open')) {
        const items = [menu, ...links], first = items[0], last = items.at(-1);
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
});
function select(link, push = false) {
    for (const a of links) { a.toggleAttribute('aria-current', a === link); if (a === link) a.setAttribute('aria-current','page'); }
    frame.src = link.dataset.deckUrl;
    frame.title = link.dataset.title + ' — présentation Slides';
    document.getElementById('deck-title').textContent = link.dataset.title;
    document.getElementById('deck-direct').href = link.dataset.deckUrl;
    document.title = link.dataset.title + ' — Slides';
    document.getElementById('library-announcement').textContent = 'Présentation sélectionnée : ' + link.dataset.title;
    if (push) history.pushState(null, '', link.href);
    setMenu(false);
}
for (const link of links) link.addEventListener('click', e => {
    if (e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    e.preventDefault(); const wasMobile = mobile.matches; select(link, true); if (wasMobile) main.focus();
});
window.addEventListener('popstate', () => select(links.find(a => new URL(a.href).search === location.search) || links[0]));
if (!mobile.matches) sidebar.querySelector('[aria-current="page"]')?.scrollIntoView({block:'nearest'});
const icons = {
 light:'<circle cx="12" cy="12" r="5"/><path d="M12 1v2m0 18v2M1 12h2m18 0h2M4.22 4.22l1.42 1.42m12.72 12.72 1.42 1.42M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>',
 dark:'<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
 sepia:'<path d="M18 8h1a4 4 0 0 1 0 8h-1M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8zM6 1v3m4-3v3m4-3v3"/>'
};
let theme = 'light';
try { const saved = localStorage.getItem('theme'); if (Object.hasOwn(icons, saved)) theme = saved; } catch {}
function applyTheme() {
    body.classList.remove('theme-light','theme-dark','theme-sepia'); body.classList.add('theme-'+theme);
    document.getElementById('theme-icon').innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${icons[theme]}</svg>`;
    const label = 'Thème ' + ({light:'clair',dark:'sombre',sepia:'sépia'})[theme] + ' — changer de thème';
    document.getElementById('theme-switcher').setAttribute('aria-label',label);
    document.getElementById('theme-switcher').title = label;
}
applyTheme();
document.getElementById('theme-switcher').addEventListener('click', () => {
    const themes = ['light','dark','sepia']; theme = themes[(themes.indexOf(theme)+1)%themes.length]; applyTheme();
    try { localStorage.setItem('theme',theme); } catch {}
});
