(() => {
    // Slidewire renders separate player and overview copies of the cover.
    const [picker, ...copies] = document.querySelectorAll('.update-theme');
    if (!picker || picker.parentElement === document.body) return;

    copies.forEach(copy => copy.remove());
    document.body.append(picker);
    document.documentElement.lang = picker.lang;

    const options = [...picker.querySelectorAll('input[type="radio"]')];
    const storageKey = 'theme';

    function selectTheme(option) {
        option.checked = true;
        document.documentElement.dataset.updateTheme = option.value;
    }

    let savedTheme;
    try {
        savedTheme = localStorage.getItem(storageKey);
    } catch {
        // The deck remains usable with browser storage disabled.
    }
    selectTheme(options.find(option => option.value === savedTheme) ?? options[0]);

    picker.addEventListener('change', ({ target }) => {
        if (!options.includes(target)) return;
        selectTheme(target);
        try {
            localStorage.setItem(storageKey, target.value);
        } catch {
            // Keep the current document's preference.
        }
    });
})();
