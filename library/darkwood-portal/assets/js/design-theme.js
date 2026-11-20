const themes = ['light', 'dark', 'sepia'];
const labels = {
    fr: { light: 'Clair', dark: 'Sombre', sepia: 'Sépia' },
    en: { light: 'Light', dark: 'Dark', sepia: 'Sepia' },
    de: { light: 'Hell', dark: 'Dunkel', sepia: 'Sepia' },
};
const icons = { light: '☼', dark: '☾', sepia: '◐' };
const root = document.documentElement;
const host = document.querySelector('.layout-darkwood .theme-nav__actions, .layout-paper .theme-nav__actions');

if (host) {
    const language = root.lang.split('-')[0];
    const button = document.createElement('button');
    button.type = 'button';
    button.id = 'dw-theme-switcher';
    button.className = 'dw-theme-switcher';
    const icon = document.createElement('span');
    icon.setAttribute('aria-hidden', 'true');
    const label = document.createElement('span');
    button.append(icon, label);
    host.prepend(button);

    function apply(theme) {
        root.dataset.theme = theme;
        root.dataset.bsTheme = theme;
        icon.textContent = icons[theme];
        label.textContent = (labels[language] ?? labels.en)[theme];
        button.title = Object.values(labels[language] ?? labels.en).join(' / ');
    }

    let saved;
    try { saved = localStorage.getItem('darkwood-theme'); } catch {}
    apply(themes.includes(saved) ? saved : document.body.classList.contains('layout-darkwood') ? 'dark' : 'light');
    button.addEventListener('click', () => {
        const next = themes[(themes.indexOf(root.dataset.theme) + 1) % themes.length];
        apply(next);
        try { localStorage.setItem('darkwood-theme', next); } catch {}
    });
}
