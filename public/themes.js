(() => {
  const themes = ['light', 'dark', 'sepia'];
  const labels = { light: 'Clair', dark: 'Sombre', sepia: 'Sépia' };
  function applyTheme(theme, persist = true) {
    if (!themes.includes(theme)) theme = 'light';
    document.body.classList.remove(...themes.map(t => 'theme-' + t));
    document.body.classList.add('theme-' + theme);
    document.querySelectorAll('[data-theme]').forEach(button => {
      button.setAttribute('aria-pressed', String(button.dataset.theme === theme));
    });
    document.getElementById('theme-switcher')?.setAttribute('aria-label', 'Thème ' + labels[theme] + ' : changer de thème');
    if (persist) {
      try { localStorage.setItem('theme', theme); }
      catch { document.getElementById('theme-status').textContent = 'Préférence appliquée pour cette page ; stockage indisponible.'; }
    }
  }
  applyTheme(document.body.className.match(/theme-(light|dark|sepia)/)?.[1] || 'light', false);
  document.querySelectorAll('[data-theme]').forEach(button => button.addEventListener('click', () => applyTheme(button.dataset.theme)));
  document.getElementById('theme-switcher')?.addEventListener('click', () => {
    const current = document.body.className.match(/theme-(light|dark|sepia)/)?.[1] || 'light';
    applyTheme(themes[(themes.indexOf(current) + 1) % themes.length]);
  });
  window.addEventListener('pageshow', () => { try { applyTheme(localStorage.getItem('theme') || 'light', false); } catch {} });
  const toggle = document.getElementById('menu-toggle'), sidebar = document.getElementById('sidebar');
  const page = document.querySelector('.page-column');
  function closeMenu() { document.body.classList.remove('menu-open'); toggle?.setAttribute('aria-expanded', 'false'); if (page) page.inert = false; }
  toggle?.addEventListener('click', () => {
    const open = document.body.classList.toggle('menu-open'); toggle.setAttribute('aria-expanded', String(open));
    if (page) page.inert = open;
    if (open) sidebar.querySelector('a')?.focus();
  });
  document.getElementById('menu-backdrop')?.addEventListener('click', closeMenu);
  document.addEventListener('keydown', e => {
    if (!document.body.classList.contains('menu-open')) return;
    if (e.key === 'Escape') { closeMenu(); toggle.focus(); }
    if (e.key === 'Tab') {
      const controls = [...sidebar.querySelectorAll('a, button')], first = controls[0], last = controls.at(-1);
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });
  matchMedia('(min-width:576px)').addEventListener('change', e => { if (e.matches) closeMenu(); });
  sidebar?.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));
})();
