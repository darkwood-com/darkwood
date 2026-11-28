document.addEventListener('DOMContentLoaded', function() {
    const themeSwitcher = document.getElementById('theme-switcher');
    const themeIcon = themeSwitcher?.querySelector('img');
    const themes = ['light', 'dark', 'sepia'];
    const labels = { light: 'Light', dark: 'Dark', sepia: 'Sepia' };
    let activeTheme;

    function apply(theme) {
        activeTheme = theme;
        document.body.classList.remove(...themes.map(value => 'theme-' + value));
        document.body.classList.add('theme-' + theme);
        document.body.dataset.theme = theme;
        document.body.dataset.bsTheme = theme;
        themeSwitcher?.setAttribute('aria-label', 'Color theme: ' + labels[theme] + '. Activate for next theme.');
        if (themeIcon) {
            themeIcon.src = themeSwitcher.dataset[theme];
        }
    }

    themeSwitcher?.addEventListener('click', () => {
        const next = themes[(themes.indexOf(activeTheme) + 1) % themes.length];
        apply(next);
        try { localStorage.setItem('theme', next); } catch {}
    });

    let savedTheme;
    try { savedTheme = localStorage.getItem('theme'); } catch {}
    apply(themes.includes(savedTheme) ? savedTheme : 'light');
});
