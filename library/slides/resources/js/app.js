import { bindDeck } from './motion/deck';

function start(tries = 0) {
    const state = bindDeck();

    if (state === 'wait' && tries < 40) {
        requestAnimationFrame(() => start(tries + 1));
    }
}

document.addEventListener('alpine:initialized', () => start());
start();
