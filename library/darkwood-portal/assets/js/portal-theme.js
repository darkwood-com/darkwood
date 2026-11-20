import sun from '../../../darkwood-design/icons/light.svg';
import moon from '../../../darkwood-design/icons/dark.svg';
import cup from '../../../darkwood-design/icons/sepia.svg';

const body = document.body;
const portal = body.matches('.layout-darkwood, .layout-paper, .layout-sky, .layout-photos, .layout-hello, .layout-api, .layout-podcasts');
const icons = { light: sun, dark: moon, sepia: cup };
const copy = {
    fr: ['Thème', 'Original', 'Clair', 'Sombre', 'Sépia'],
    en: ['Theme', 'Original', 'Light', 'Dark', 'Sepia'],
    de: ['Design', 'Original', 'Hell', 'Dunkel', 'Sepia'],
};

if (portal) {
    const [label, ...options] = copy[document.documentElement.lang.split('-')[0]] ?? copy.en;
    const themes = ['original', 'light', 'dark', 'sepia'];
    const preferenceKey = 'darkwood-portal-palette';
    const picker = document.createElement('label');
    picker.className = 'portal-theme-picker';
    const icon = document.createElement('span');
    icon.className = 'portal-theme-picker__icon';
    icon.setAttribute('aria-hidden', 'true');
    const select = document.createElement('select');
    select.setAttribute('aria-label', label);
    themes.forEach((theme, index) => select.add(new Option(options[index], theme)));
    picker.append(icon, select);

    const actions = document.querySelector('.theme-nav__actions, .main-nav > .container');
    if (actions) {
        actions.append(picker);
    } else {
        const toolbar = document.createElement('div');
        toolbar.className = 'portal-theme-toolbar';
        toolbar.append(picker);
        (document.querySelector('.theme > .theme-header') ?? body.firstElementChild)?.before(toolbar);
    }

    const apply = (theme) => {
        select.value = theme;
        const root = document.documentElement;
        if (theme === 'original') {
            delete root.dataset.theme;
            delete root.dataset.bsTheme;
        } else {
            root.dataset.theme = theme;
            root.dataset.bsTheme = theme;
        }
        icon.hidden = theme === 'original';
        if (theme === 'original') {
            icon.style.removeProperty('--portal-theme-icon');
        } else {
            icon.style.setProperty('--portal-theme-icon', `url("${icons[theme]}")`);
        }
    };

    let saved;
    try { saved = localStorage.getItem(preferenceKey); } catch {}
    apply(themes.includes(saved) ? saved : 'original');
    select.addEventListener('change', () => {
        apply(select.value);
        try { localStorage.setItem(preferenceKey, select.value); } catch {}
    });
}
