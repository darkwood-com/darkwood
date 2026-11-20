document.addEventListener('DOMContentLoaded', function() {
    const themeSwitcher = document.getElementById('theme-switcher');
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
        updateThemeIcon(theme);
    }

    themeSwitcher?.addEventListener('click', () => {
        const next = themes[(themes.indexOf(activeTheme) + 1) % themes.length];
        apply(next);
        try { localStorage.setItem('theme', next); } catch {}
    });

    function updateThemeIcon(theme) {
        const iconContainer = document.getElementById('theme-icon');
        if (!iconContainer) return;

        let iconHTML = '';
        switch(theme) {
            case 'light':
                iconHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline--fa fa-w-16">
                    <circle cx="12" cy="12" r="5" />
                    <line x1="12" y1="1" x2="12" y2="3" />
                    <line x1="12" y1="21" x2="12" y2="23" />
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                    <line x1="1" y1="12" x2="3" y2="12" />
                    <line x1="21" y1="12" x2="23" y2="12" />
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                </svg>`;
                break;
            case 'dark':
                iconHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline--fa fa-w-16">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" />
                </svg>`;
                break;
            case 'sepia':
                iconHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="svg-inline--fa fa-w-16">
                    <path d="M18 8h1a4 4 0 0 1 0 8h-1" />
                    <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" />
                    <line x1="6" y1="1" x2="6" y2="4" />
                    <line x1="10" y1="1" x2="10" y2="4" />
                    <line x1="14" y1="1" x2="14" y2="4" />
                </svg>`;
                break;
        }
        iconContainer.innerHTML = iconHTML;
    }

    let savedTheme;
    try { savedTheme = localStorage.getItem('theme'); } catch {}
    apply(themes.includes(savedTheme) ? savedTheme : 'light');
});
