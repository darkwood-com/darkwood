import gsap from 'gsap';
import { mountBoard, paintBoard } from './widgets';
import {
    GREEN,
    GREEN_PATH,
    NAMES,
    replay,
    startLevel,
} from './rules';

const WHITE_PATH = ['E', 'E', 'E', 'S', 'W', 'N'];
const ORANGE_PATH = ['S', 'E', 'E', 'S', 'W', 'N'];

function sleep(ms, animate) {
    if (!animate || ms <= 0) {
        return Promise.resolve();
    }

    return new Promise((resolve) => {
        gsap.delayedCall(ms / 1000, resolve);
    });
}

async function tipTo(nodes, fromFrame, toFrame, action, animate) {
    paintBoard(nodes, fromFrame.state);
    const die = nodes.find((node) => node.classList.contains('is-die'))?.querySelector('.pg-die-token');
    if (die && animate) {
        const map = { E: '90deg', W: '-90deg', N: '0deg', S: '0deg' };
        const axis = action === 'E' || action === 'W' ? 'Y' : 'X';
        const angle = map[action] || '90deg';
        await new Promise((resolve) => {
            gsap.fromTo(
                die,
                { rotateX: 0, rotateY: 0, scale: 1 },
                {
                    [`rotate${axis}`]: angle,
                    scale: 1.08,
                    duration: 0.38,
                    ease: 'power2.inOut',
                    onComplete: resolve,
                },
            );
        });
    }
    paintBoard(nodes, toFrame.state);
    const landed = nodes.find((node) => node.classList.contains('is-die'));
    if (landed && animate) {
        gsap.fromTo(landed, { scale: 0.92 }, { scale: 1, duration: 0.22, ease: 'power2.out' });
    }
}

/**
 * Full-bleed X promo storyboard (~28s) using real Pigment rules.
 */
export function bindPromoX(slide) {
    const boardHost = slide.querySelector('[data-pg-board="promo"]');
    const title = slide.querySelector('[data-pg-promo-title]');
    const subtitle = slide.querySelector('[data-pg-promo-sub]');
    const overlay = slide.querySelector('[data-pg-promo-overlay]');
    const cta = slide.querySelector('[data-pg-promo-cta]');
    const nodes = mountBoard(boardHost, { cell: 92 });
    let running = false;
    let generation = 0;

    const setOverlay = (text, animate = true) => {
        if (!overlay) {
            return;
        }
        overlay.textContent = text || '';
        overlay.style.opacity = text ? '1' : '0';
        if (text && animate) {
            gsap.fromTo(overlay, { y: 18, opacity: 0 }, { y: 0, opacity: 1, duration: 0.35, ease: 'power2.out' });
        }
    };

    const showTitle = (on, animate = true) => {
        const targets = [title, subtitle].filter(Boolean);
        if (!targets.length) {
            return;
        }
        gsap.to(targets, {
            opacity: on ? 1 : 0,
            y: on ? 0 : -10,
            duration: animate ? 0.4 : 0,
            stagger: 0.06,
            ease: 'power2.out',
        });
    };

    const showCta = (on, animate = true) => {
        if (!cta) {
            return;
        }
        gsap.to(cta, { opacity: on ? 1 : 0, y: on ? 0 : 16, duration: animate ? 0.45 : 0, ease: 'power2.out' });
    };

    const run = async (animate) => {
        const token = ++generation;
        running = true;
        showCta(false, false);
        showTitle(true, animate);
        setOverlay('', false);
        paintBoard(nodes, startLevel(0));
        await sleep(3200, animate);
        if (token !== generation) {
            return;
        }

        // Scene 2 — first roll
        showTitle(false, animate);
        const first = replay(0, ['E']);
        await tipTo(nodes, first[0], first[1], 'E', animate);
        setOverlay('EVERY ROLL LEAVES A COLOR', animate);
        await sleep(2800, animate);
        if (token !== generation) {
            return;
        }

        // Scene 3 — Return / mix to green
        setOverlay('', false);
        const green = replay(1, GREEN_PATH);
        paintBoard(nodes, green[0].state);
        await sleep(700, animate);
        for (let i = 0; i < GREEN_PATH.length; i += 1) {
            if (token !== generation) {
                return;
            }
            await tipTo(nodes, green[i], green[i + 1], GREEN_PATH[i], animate);
            const after = green[i + 1].events?.after;
            if (i === GREEN_PATH.length - 1 && after === GREEN) {
                setOverlay('YELLOW + BLUE = GREEN', animate);
                const cell = nodes[1];
                if (cell && animate) {
                    gsap.fromTo(
                        cell,
                        { boxShadow: '0 0 0 0 rgba(62,154,90,0)' },
                        {
                            boxShadow: '0 0 0 10px rgba(62,154,90,0.55)',
                            duration: 0.55,
                            yoyo: true,
                            repeat: 1,
                            ease: 'power1.inOut',
                        },
                    );
                }
            } else if (green[i + 1].events) {
                const ev = green[i + 1].events;
                setOverlay(`${ev.action} · ${NAMES[ev.face]} → ${NAMES[ev.after]}`, animate);
            }
            await sleep(i === GREEN_PATH.length - 1 ? 2200 : 1400, animate);
        }
        if (token !== generation) {
            return;
        }

        // Scene 4 — montage
        setOverlay('OTHER PUZZLES', animate);
        const leave = replay(2, GREEN_PATH);
        paintBoard(nodes, leave.at(-1).state);
        await sleep(1100, animate);
        if (token !== generation) {
            return;
        }

        const orange = replay(3, ORANGE_PATH);
        paintBoard(nodes, orange.at(-1).state);
        setOverlay('ORANGE TARGET', animate);
        await sleep(1100, animate);
        if (token !== generation) {
            return;
        }

        const white = replay(4, WHITE_PATH);
        paintBoard(nodes, white[white.length - 2].state);
        setOverlay('WHITE CLEARS A SEAL', animate);
        await sleep(600, animate);
        await tipTo(nodes, white[white.length - 2], white[white.length - 1], 'N', animate);
        await sleep(1400, animate);
        if (token !== generation) {
            return;
        }

        // Scene 5 — CTA
        setOverlay('', false);
        paintBoard(nodes, green.at(-1).state);
        showCta(true, animate);
        await sleep(5200, animate);
        running = false;
        slide.dataset.pgPromoDone = '1';
    };

    gsap.set([title, subtitle], { opacity: 0, y: 12 });
    gsap.set(cta, { opacity: 0, y: 16 });
    gsap.set(overlay, { opacity: 0 });
    paintBoard(nodes, startLevel(0));

    return {
        show(_step, animate) {
            if (running) {
                return;
            }
            void run(!!animate);
        },
        destroy() {
            generation += 1;
            running = false;
        },
    };
}
