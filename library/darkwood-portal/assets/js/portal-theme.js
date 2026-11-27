import palette from '../../vendor/darkwood/darkwood-bundle/assets/images/themes/original.svg';
import sun from '../../vendor/darkwood/darkwood-bundle/assets/images/themes/light.svg';
import moon from '../../vendor/darkwood/darkwood-bundle/assets/images/themes/dark.svg';
import cup from '../../vendor/darkwood/darkwood-bundle/assets/images/themes/sepia.svg';

const body = document.body;
const portal = body.matches('.layout-darkwood, .layout-paper, .layout-sky, .layout-photos, .layout-hello, .layout-api, .layout-podcasts');
const icons = { original: palette, light: sun, dark: moon, sepia: cup };
const copy = {
    fr: ['Changer de thème', 'Original', 'Clair', 'Sombre', 'Sépia'],
    en: ['Change theme', 'Original', 'Light', 'Dark', 'Sepia'],
    de: ['Design wechseln', 'Original', 'Hell', 'Dunkel', 'Sepia'],
};

if (portal) {
    const [label, ...options] = copy[document.documentElement.lang.split('-')[0]] ?? copy.en;
    const themes = ['original', 'light', 'dark', 'sepia'];
    const preferenceKey = 'darkwood-portal-palette';
    const picker = document.createElement('div');
    picker.className = 'portal-theme-picker dropdown';

    function makeIcon(theme) {
        const icon = document.createElement('span');
        icon.className = 'portal-theme-picker__icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.style.setProperty('--portal-theme-icon', `url("${icons[theme]}")`);
        return icon;
    }

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'portal-theme-picker__toggle';
    toggle.dataset.bsToggle = 'dropdown';
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-controls', 'portal-theme-menu');

    const menu = document.createElement('ul');
    menu.id = 'portal-theme-menu';
    menu.className = 'dropdown-menu dropdown-menu-end';
    const choices = themes.map((theme, index) => {
        const item = document.createElement('li');
        const choice = document.createElement('button');
        choice.type = 'button';
        choice.className = 'dropdown-item';
        choice.dataset.palette = theme;
        choice.append(makeIcon(theme), options[index]);
        item.append(choice);
        menu.append(item);
        return choice;
    });
    picker.append(toggle, menu);

    const actions = document.querySelector('.theme-nav__actions, .main-nav > .container');
    if (actions) {
        const locales = actions.querySelector('.theme-nav__locales');
        if (locales) {
            locales.before(picker);
        } else {
            actions.append(picker);
        }
    } else {
        const toolbar = document.createElement('div');
        toolbar.className = 'portal-theme-toolbar';
        toolbar.append(picker);
        (document.querySelector('.theme > .theme-header') ?? body.firstElementChild)?.before(toolbar);
    }

    const apply = (theme) => {
        const activeLabel = options[themes.indexOf(theme)];
        toggle.replaceChildren(makeIcon(theme));
        toggle.setAttribute('aria-label', `${label} · ${activeLabel}`);
        toggle.title = activeLabel;
        for (const choice of choices) {
            const active = choice.dataset.palette === theme;
            choice.classList.toggle('active', active);
            choice.setAttribute('aria-pressed', String(active));
        }
        const root = document.documentElement;
        if (theme === 'original') {
            delete root.dataset.theme;
            delete root.dataset.bsTheme;
        } else {
            root.dataset.theme = theme;
            root.dataset.bsTheme = theme;
        }
    };

    let saved;
    try { saved = localStorage.getItem(preferenceKey); } catch {}
    apply(themes.includes(saved) ? saved : 'original');
    for (const choice of choices) {
        choice.addEventListener('click', () => {
            const theme = choice.dataset.palette;
            apply(theme);
            try { localStorage.setItem(preferenceKey, theme); } catch {}
            toggle.focus();
        });
    }
}
