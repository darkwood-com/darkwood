function activatePlayers() {
    document.querySelectorAll('[data-darkwood-embed]').forEach(card => {
        const button = card.querySelector('[data-open-player]');
        const player = card.querySelector('.dw-embed-player');
        const source = card.querySelector('template');
        if (!button || !player || !source) return;
        const openLabel = button.textContent;
        button.hidden = false;
        button.addEventListener('click', () => {
            const open = player.hidden;
            player.replaceChildren(...(open ? [source.content.cloneNode(true)] : []));
            player.hidden = !open;
            button.textContent = open ? button.dataset.closeLabel : openLabel;
            button.setAttribute('aria-expanded', String(open));
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', activatePlayers, { once: true });
} else {
    activatePlayers();
}
