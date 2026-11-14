(() => {
  const themes = ['light', 'dark', 'sepia'];
  const labels = { light: 'Clair', dark: 'Sombre', sepia: 'Sépia' };
  const button = document.getElementById('theme-switcher');
  const icon = document.getElementById('theme-icon');
  const iconTemplates = document.getElementById('theme-icons');
  function applyTheme(theme, persist = true) {
    if (!themes.includes(theme)) theme = 'light';
    document.body.classList.remove(...themes.map(t => 'theme-' + t));
    document.body.classList.add('theme-' + theme);
    const next = themes[(themes.indexOf(theme) + 1) % themes.length];
    const label = 'Thème ' + labels[theme] + ' : passer au thème ' + labels[next];
    button?.setAttribute('aria-label', label); button?.setAttribute('title', label);
    const nextIcon = iconTemplates?.content.querySelector('[data-theme-icon="' + theme + '"] svg');
    if (icon && nextIcon) icon.replaceChildren(nextIcon.cloneNode(true));
    if (persist) {
      try { localStorage.setItem('theme', theme); }
      catch { document.getElementById('theme-status').textContent = 'Préférence appliquée pour cette page ; stockage indisponible.'; }
    }
  }
  applyTheme(document.body.className.match(/theme-(light|dark|sepia)/)?.[1] || 'light', false);
  button?.addEventListener('click', () => {
    const current = document.body.className.match(/theme-(light|dark|sepia)/)?.[1] || 'light';
    applyTheme(themes[(themes.indexOf(current) + 1) % themes.length]);
  });
  window.addEventListener('pageshow', () => { try { applyTheme(localStorage.getItem('theme') || 'light', false); } catch {} closeMenu(); });
  const toggle = document.getElementById('menu-toggle'), sidebar = document.getElementById('sidebar');
  const close = document.getElementById('menu-close'), page = document.querySelector('.page-column');
  const mobile = matchMedia('(max-width:575px)');
  function setMenu(open, restoreFocus = false) {
    document.body.classList.toggle('menu-open', open);
    toggle?.setAttribute('aria-expanded', String(open));
    toggle?.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
    toggle?.setAttribute('title', open ? 'Fermer le menu' : 'Ouvrir le menu');
    if (page) page.inert = open;
    // Hidden mobile navigation is also removed from keyboard navigation.
    if (sidebar) sidebar.inert = mobile.matches && !open;
    if (open) close?.focus();
    else if (restoreFocus) toggle?.focus();
  }
  function closeMenu(restoreFocus = false) { setMenu(false, restoreFocus); }
  toggle?.addEventListener('click', () => setMenu(!document.body.classList.contains('menu-open')));
  close?.addEventListener('click', () => closeMenu(true));
  document.addEventListener('keydown', e => {
    if (!document.body.classList.contains('menu-open')) return;
    if (e.key === 'Escape') { e.preventDefault(); closeMenu(true); }
    if (e.key === 'Tab') {
      const controls = [toggle, ...sidebar.querySelectorAll('a, button')].filter(Boolean);
      const first = controls[0], last = controls.at(-1);
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });
  mobile.addEventListener('change', () => closeMenu());
  sidebar?.querySelectorAll('a').forEach(a => a.addEventListener('click', () => closeMenu()));
  closeMenu();
})();
