import gsap from 'gsap';
import { reduced } from '../motion/reduced';
import {
    bindDieOrientation,
    bindGreenReplay,
    bindLeaveCompare,
    bindMixer,
    bindTipDemo,
    bindUndoDemo,
    mountBoard,
    paintBoard,
    paintMiniLevels,
} from './widgets';
import { GREEN_PATH, replay, startLevel } from './rules';

function duration(seconds, animate) {
    return !animate || reduced() ? 0 : seconds;
}

function fadeIn(nodes, animate, stagger = 0.06) {
    gsap.fromTo(
        nodes,
        { opacity: 0, y: 14 },
        {
            opacity: 1,
            y: 0,
            duration: duration(0.45, animate),
            stagger: reduced() ? 0 : stagger,
            ease: 'power2.out',
            overwrite: 'auto',
        },
    );
}

export const motions = {
    'pg-title'(slide) {
        const boardHost = slide.querySelector('[data-pg-board="title"]');
        const nodes = boardHost ? mountBoard(boardHost, { cell: 26 }) : [];
        const frames = replay(0, ['E']);
        if (nodes.length) {
            paintBoard(nodes, frames[0].state);
        }
        const copy = slide.querySelectorAll('[data-pg-copy] > *');
        gsap.set(copy, { opacity: 0, y: 18 });
        let played = false;

        return {
            show(_step, animate) {
                if (nodes.length) {
                    paintBoard(nodes, frames[0].state);
                    if (animate && !reduced()) {
                        gsap.delayedCall(0.55, () => paintBoard(nodes, frames[1].state));
                    } else {
                        paintBoard(nodes, frames[1].state);
                    }
                }
                if (played) {
                    return;
                }
                played = true;
                fadeIn(copy, animate, 0.09);
            },
            destroy() {},
        };
    },

    'pg-tip'(slide) {
        const demo = bindTipDemo(slide);
        const copy = slide.querySelectorAll('[data-pg-fade]');
        gsap.set(copy, { opacity: 0, y: 12 });
        let played = false;

        return {
            show(step, animate) {
                demo.setStep(typeof step === 'number' ? step : 0);
                if (!played) {
                    played = true;
                    fadeIn(copy, animate);
                }
            },
            destroy() {
                demo.destroy();
            },
        };
    },

    'pg-die'(slide) {
        const demo = bindDieOrientation(slide);

        return {
            show(step) {
                demo.setStep(typeof step === 'number' ? step : 0);
            },
            destroy() {
                demo.destroy();
            },
        };
    },

    'pg-perm'(slide) {
        const rows = slide.querySelectorAll('[data-pg-fade]');
        gsap.set(rows, { opacity: 0, y: 10 });
        let played = false;

        return {
            show(_step, animate) {
                if (played) {
                    return;
                }
                played = true;
                fadeIn(rows, animate, 0.08);
            },
            destroy() {},
        };
    },

    'pg-mix'(slide) {
        const demo = bindMixer(slide);
        const pot = slide.querySelector('[data-pg-mix-result]');
        gsap.set(pot, { scale: 0.86, opacity: 0.5 });

        return {
            show(step, animate) {
                demo.setStep(typeof step === 'number' ? step : 0);
                gsap.to(pot, {
                    scale: 1,
                    opacity: 1,
                    duration: duration(0.4, animate),
                    ease: 'back.out(1.6)',
                    overwrite: 'auto',
                });
            },
            destroy() {
                demo.destroy();
            },
        };
    },

    'pg-pipeline'(slide) {
        const steps = slide.querySelectorAll('[data-pg-pipe]');
        gsap.set(steps, { opacity: 0.28, y: 8 });

        return {
            show(step, animate) {
                const time = duration(0.35, animate);
                const active = typeof step === 'number' ? step : 0;
                steps.forEach((node, index) => {
                    const on = active === 0 ? index === 0 : index < active;
                    gsap.to(node, {
                        opacity: on ? 1 : 0.28,
                        y: 0,
                        duration: time,
                        ease: 'power2.out',
                        overwrite: 'auto',
                        delay: reduced() ? 0 : index * 0.04,
                    });
                });
            },
            destroy() {},
        };
    },

    'pg-state'(slide) {
        const boards = [...slide.querySelectorAll('[data-pg-board="state"]')];
        const nodesA = mountBoard(boards[0], { cell: 30 });
        const nodesB = mountBoard(boards[1], { cell: 30 });
        const pathA = replay(1, GREEN_PATH);
        const pathB = replay(1, ['E', 'S', 'E', 'N', 'W']);
        const midA = pathA[1].state;
        const midB = pathB[1].state;

        return {
            show(step, animate) {
                const frameA = step >= 1 ? pathA.at(-1).state : midA;
                const frameB = step >= 1 ? pathB.at(-1).state : midB;
                paintBoard(nodesA, frameA);
                paintBoard(nodesB, frameB);
                if (animate && !reduced()) {
                    gsap.fromTo(
                        [...nodesA, ...nodesB],
                        { opacity: 0.4 },
                        { opacity: 1, duration: 0.35, stagger: 0.004, overwrite: 'auto' },
                    );
                }
            },
            destroy() {},
        };
    },

    'pg-green'(slide) {
        const demo = bindGreenReplay(slide);

        return {
            show(step) {
                if (typeof step === 'number' && step > 0) {
                    demo.show(Math.min(step, 5));
                } else {
                    demo.show();
                }
            },
            destroy() {
                demo.destroy();
            },
        };
    },

    'pg-leave'(slide) {
        const demo = bindLeaveCompare(slide);
        const labels = slide.querySelectorAll('[data-pg-fade]');
        gsap.set(labels, { opacity: 0, y: 10 });
        let played = false;

        return {
            show(_step, animate) {
                demo.show();
                if (!played) {
                    played = true;
                    fadeIn(labels, animate);
                }
            },
            destroy() {
                demo.destroy();
            },
        };
    },

    'pg-levels'(slide) {
        const demo = paintMiniLevels(slide);
        const cards = slide.querySelectorAll('[data-pg-level]');
        gsap.set(cards, { opacity: 0, y: 12 });
        let played = false;

        return {
            show(_step, animate) {
                demo.show();
                if (!played) {
                    played = true;
                    fadeIn(cards, animate, 0.07);
                }
            },
            destroy() {
                demo.destroy();
            },
        };
    },

    'pg-arch'(slide) {
        const cards = slide.querySelectorAll('[data-pg-fade]');
        gsap.set(cards, { opacity: 0, y: 14 });
        let played = false;

        return {
            show(_step, animate) {
                if (!played) {
                    played = true;
                    fadeIn(cards, animate, 0.1);
                }
            },
            destroy() {},
        };
    },

    'pg-render'(slide) {
        const host = slide.querySelector('[data-pg-board="render"]');
        const nodes = mountBoard(host, { cell: 34 });
        const frames = replay(1, GREEN_PATH);
        paintBoard(nodes, frames.at(-1).state);
        const items = slide.querySelectorAll('[data-pg-fade]');
        gsap.set(items, { opacity: 0, y: 10 });
        let played = false;

        return {
            show(_step, animate) {
                paintBoard(nodes, frames.at(-1).state);
                if (!played) {
                    played = true;
                    fadeIn(items, animate);
                }
            },
            destroy() {},
        };
    },

    'pg-undo'(slide) {
        const demo = bindUndoDemo(slide);

        return {
            show(step) {
                demo.setStep(typeof step === 'number' ? step : 0);
            },
            destroy() {
                demo.destroy();
            },
        };
    },

    'pg-lessons'(slide) {
        const items = slide.querySelectorAll('[data-pg-lesson]');
        gsap.set(items, { opacity: 0.25, x: -8 });

        return {
            show(step, animate) {
                const time = duration(0.35, animate);
                const active = typeof step === 'number' ? step : 0;
                items.forEach((item, index) => {
                    const on = active === 0 ? false : index < active;
                    gsap.to(item, {
                        opacity: on ? 1 : 0.25,
                        x: 0,
                        duration: time,
                        ease: 'power2.out',
                        overwrite: 'auto',
                    });
                });
            },
            destroy() {},
        };
    },

    'pg-close'(slide) {
        const host = slide.querySelector('[data-pg-board="close"]');
        const nodes = mountBoard(host, { cell: 28 });
        paintBoard(nodes, startLevel(0));
        const copy = slide.querySelectorAll('[data-pg-fade]');
        gsap.set(copy, { opacity: 0, y: 16 });
        let played = false;

        return {
            show(_step, animate) {
                paintBoard(nodes, startLevel(0));
                if (!played) {
                    played = true;
                    fadeIn(copy, animate, 0.1);
                    if (animate && !reduced()) {
                        const frames = replay(0, ['E']);
                        gsap.delayedCall(0.6, () => paintBoard(nodes, frames[1].state));
                    }
                }
            },
            destroy() {},
        };
    },
};
