import gsap from 'gsap';
import { reduced } from '../motion/reduced';

function duration(seconds, animate) {
    return !animate || reduced() ? 0 : seconds;
}

function revealOnce(slide, selector = '[data-sw-reveal]') {
    const nodes = slide.querySelectorAll(selector);
    gsap.set(nodes, { opacity: 0, y: 14 });
    let played = false;

    return (animate) => {
        if (played) return;
        played = true;
        gsap.to(nodes, {
            opacity: 1,
            y: 0,
            duration: duration(0.42, animate),
            stagger: reduced() ? 0 : 0.07,
            ease: 'power2.out',
            overwrite: 'auto',
        });
    };
}

function staged(slide, selector) {
    const nodes = [...slide.querySelectorAll(selector)];
    gsap.set(nodes, { opacity: 0.22, y: 8 });

    return (step, animate) => {
        const active = Math.max(0, Number(step) || 0);
        nodes.forEach((node, index) => {
            const on = index <= active;
            gsap.to(node, {
                opacity: on ? 1 : 0.22,
                y: on ? 0 : 8,
                duration: duration(0.3, animate),
                ease: 'power2.out',
                overwrite: 'auto',
            });
        });
    };
}

export const motions = {
    'sw-title'(slide) {
        const reveal = revealOnce(slide);
        const paths = slide.querySelectorAll('[data-sw-path]');
        gsap.set(paths, { strokeDasharray: 440, strokeDashoffset: 440 });

        return {
            show(_step, animate) {
                reveal(animate);
                gsap.to(paths, {
                    strokeDashoffset: 0,
                    duration: duration(1.15, animate),
                    stagger: reduced() ? 0 : 0.18,
                    ease: 'power2.inOut',
                    overwrite: 'auto',
                });
            },
        };
    },

    'sw-question'(slide) {
        const reveal = revealOnce(slide);
        const stage = staged(slide, '[data-sw-model]');
        return { show(step, animate) { reveal(animate); stage(step, animate); } };
    },

    'sw-seed'(slide) {
        const reveal = revealOnce(slide, '[data-sw-reveal]');
        const boards = [...slide.querySelectorAll('[data-sw-seeded-board]')];
        gsap.set(boards, { opacity: 0.18, scale: 0.96 });
        return {
            show(step, animate) {
                reveal(animate);
                boards.forEach((board, index) => {
                    const on = index <= (Number(step) || 0);
                    gsap.to(board, {
                        opacity: on ? 1 : 0.18,
                        scale: on ? 1 : 0.96,
                        duration: duration(0.42, animate),
                        ease: 'power2.out',
                        overwrite: 'auto',
                    });
                });
            },
        };
    },

    'sw-diverge'(slide) {
        const reveal = revealOnce(slide);
        const paths = [...slide.querySelectorAll('[data-sw-route]')];
        const split = slide.querySelector('[data-sw-split]');
        gsap.set(paths, { strokeDasharray: 560, strokeDashoffset: 560 });
        gsap.set(split, { opacity: 0, scale: 0.5 });
        return {
            show(step, animate) {
                reveal(animate);
                const n = Number(step) || 0;
                paths.forEach((path, index) => gsap.to(path, {
                    strokeDashoffset: index <= n ? 0 : 560,
                    duration: duration(0.8, animate),
                    ease: 'power2.inOut',
                    overwrite: 'auto',
                }));
                gsap.to(split, {
                    opacity: n >= 1 ? 1 : 0,
                    scale: n >= 1 ? 1 : 0.5,
                    duration: duration(0.35, animate),
                    ease: 'back.out(1.7)',
                    overwrite: 'auto',
                });
            },
        };
    },

    'sw-state'(slide) {
        const reveal = revealOnce(slide);
        const stage = staged(slide, '[data-sw-stage]');
        return { show(step, animate) { reveal(animate); stage(step, animate); } };
    },

    'sw-streams'(slide) {
        const reveal = revealOnce(slide);
        const stage = staged(slide, '.sw-floor-row');
        return { show(step, animate) { reveal(animate); stage(step, animate); } };
    },

    'sw-arch'(slide) {
        const reveal = revealOnce(slide);
        return { show(_step, animate) { reveal(animate); } };
    },

    'sw-replay'(slide) {
        const reveal = revealOnce(slide);
        const actions = [...slide.querySelectorAll('[data-sw-action]')];
        gsap.set(actions, { opacity: 0.22, y: 8 });
        const result = slide.querySelector('[data-sw-result]');
        gsap.set(result, { opacity: 0.25 });
        return {
            show(step, animate) {
                reveal(animate);
                const active = Number(step) || 0;
                actions.forEach((node, index) => gsap.to(node, {
                    opacity: index < (active + 1) * 2 ? 1 : 0.22,
                    y: index < (active + 1) * 2 ? 0 : 8,
                    duration: duration(0.3, animate),
                    overwrite: 'auto',
                }));
                gsap.to(result, {
                    opacity: active >= 2 ? 1 : 0.25,
                    duration: duration(0.3, animate),
                    overwrite: 'auto',
                });
            },
        };
    },

    'sw-code'(slide) {
        const reveal = revealOnce(slide);
        return { show(_step, animate) { reveal(animate); } };
    },

    'sw-validate'(slide) {
        const reveal = revealOnce(slide);
        const checks = [...slide.querySelectorAll('[data-sw-check]')];
        gsap.set(checks, { opacity: 0.22, y: 8 });
        return {
            show(step, animate) {
                reveal(animate);
                const active = (Number(step) || 0) + 1;
                checks.forEach((node, index) => gsap.to(node, {
                    opacity: index <= active ? 1 : 0.22,
                    y: index <= active ? 0 : 8,
                    duration: duration(0.3, animate),
                    overwrite: 'auto',
                }));
            },
        };
    },

    'sw-ghost'(slide) {
        const reveal = revealOnce(slide);
        const callouts = slide.querySelectorAll('[data-sw-callout]');
        gsap.set(callouts, { opacity: 0, x: 12 });
        return {
            show(_step, animate) {
                reveal(animate);
                gsap.to(callouts, {
                    opacity: 1,
                    x: 0,
                    duration: duration(0.4, animate),
                    stagger: reduced() ? 0 : 0.1,
                    ease: 'power2.out',
                    overwrite: 'auto',
                });
            },
        };
    },

    'sw-timeline'(slide) {
        const reveal = revealOnce(slide);
        const split = slide.querySelectorAll('[data-sw-timeline-split]');
        gsap.set(split, { opacity: 0, scale: 0.7 });
        return {
            show(step, animate) {
                reveal(animate);
                gsap.to(split, {
                    opacity: (Number(step) || 0) >= 1 ? 1 : 0,
                    scale: (Number(step) || 0) >= 1 ? 1 : 0.7,
                    duration: duration(0.35, animate),
                    ease: 'back.out(1.7)',
                    overwrite: 'auto',
                });
            },
        };
    },

    'sw-backend'(slide) {
        const reveal = revealOnce(slide);
        return { show(_step, animate) { reveal(animate); } };
    },

    'sw-close'(slide) {
        const reveal = revealOnce(slide);
        return { show(_step, animate) { reveal(animate); } };
    },
};
