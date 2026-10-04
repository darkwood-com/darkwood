import gsap from 'gsap';
import seed from './seed-20020.json';
import { reduced } from '../motion/reduced';
import { staggerCells, stepTimeline, tweenColors } from '../motion/timeline';
import {
    appearance,
    cellOrder,
    cellSum,
    channels,
    classify,
    counts,
    crop,
    mountBoard,
    mountGrid,
    paintBoard,
    pickCore,
    pickExit,
    reachable,
    route,
    settle,
} from './board';

const A = ['life', 'order', 'life', 'void', 'order'];
const B = ['void', 'void', 'order', 'void', 'life'];

function duration(seconds, animate) {
    return !animate || reduced() ? 0 : seconds;
}

function listsFrom(cells) {
    const lists = [];

    for (let y = 0; y < 9; y += 1) {
        for (let x = 0; x < 7; x += 1) {
            lists.push(channels(cells, x, y));
        }
    }

    return lists;
}

function moveNodes(nodes, lists, mode, mix, animate) {
    const time = duration(0.7, animate);

    nodes.forEach((node, i) => {
        const values = lists[i];
        const look = appearance(values, mode, mode === 'noise' ? 0 : mix);
        const sum = values.reduce((total, value) => total + value, 0);
        node.classList.toggle('is-void', mode !== 'noise' && sum <= 340);
        node.querySelectorAll('i').forEach((quadrant, q) => {
            gsap.to(quadrant, {
                backgroundColor: look.quadrants[q],
                opacity: look.quadrantOpacity,
                duration: time,
                ease: 'power1.inOut',
                overwrite: 'auto',
            });
        });
        tweenColors(node, {
            backgroundColor: mode === 'noise' ? '#17181f' : look.fill,
            duration: time,
            overwrite: 'auto',
        });
    });
}

function runSequence(initial, names) {
    const frames = [{ cells: initial, mode: 'noise', mix: 0 }];
    let cells = initial;

    names.forEach((name, index) => {
        cells = settle(cells, name);
        const last = index === names.length - 1;
        frames.push({
            cells,
            mode: last ? 'terrain' : 'mix',
            mix: last ? 1 : [0.46, 0.62, 0.78, 0.9][index] ?? 0.9,
            condition: name,
        });
    });

    return frames;
}

function finalA() {
    return runSequence(seed.initial, A).at(-1);
}

function stageBoard(host, cell) {
    const nodes = mountBoard(host, { cell });
    paintBoard(nodes, seed.initial, 'noise');

    return nodes;
}

function setText(slide, selector, value) {
    const node = slide.querySelector(selector);

    if (node) {
        node.textContent = value;
    }
}

export const motions = {
    title(slide) {
        const nodes = stageBoard(slide.querySelector('[data-board]'), 22);
        const copy = slide.querySelectorAll('.dw20-copy > *');
        const order = staggerCells();
        gsap.set(nodes, { opacity: 0, scale: 0.94 });
        gsap.set(copy, { opacity: 0, y: 16 });
        let played = false;

        return {
            show(_step, animate) {
                if (played) {
                    return;
                }

                played = true;
                gsap.to(nodes, {
                    opacity: 0.78,
                    scale: 1,
                    duration: duration(0.4, animate),
                    ease: 'power2.out',
                    stagger: (index) => order.index(index) * (reduced() ? 0 : 0.012),
                });
                gsap.to(copy, {
                    opacity: 1,
                    y: 0,
                    duration: duration(0.5, animate),
                    ease: 'power2.out',
                    stagger: reduced() ? 0 : 0.08,
                    delay: duration(0.25, animate),
                });
            },
        };
    },

    facts(slide) {
        const nodes = stageBoard(slide.querySelector('[data-board]'), 34);
        const facts = slide.querySelectorAll('[data-fact]');
        gsap.set(facts, { opacity: 0, y: 12 });
        let played = false;

        return {
            show(_step, animate) {
                if (played) {
                    return;
                }

                played = true;
                const order = cellOrder();
                gsap.fromTo(nodes, { opacity: 0.2 }, {
                    opacity: 1,
                    duration: duration(0.35, animate),
                    stagger: (index) => order.indexOf(index) * (reduced() ? 0 : 0.008),
                    ease: 'power2.out',
                });
                gsap.to(facts, {
                    opacity: 1,
                    y: 0,
                    duration: duration(0.4, animate),
                    stagger: reduced() ? 0 : 0.1,
                    delay: duration(0.2, animate),
                    ease: 'power2.out',
                });
            },
        };
    },

    determinism(slide) {
        const boards = [...slide.querySelectorAll('[data-board]')].map((host) => stageBoard(host, 18));

        return {
            show(step, animate) {
                boards.forEach((nodes) => moveNodes(nodes, listsFrom(seed.initial), 'noise', 0, false));
                const right = slide.querySelector('[data-side="b"]');
                const time = duration(0.45, animate);
                gsap.to(right, {
                    x: step >= 2 ? 10 : 0,
                    duration: time,
                    ease: 'power2.out',
                    overwrite: 'auto',
                });
                right.classList.toggle('is-armed', step >= 2);
            },
        };
    },

    local(slide) {
        const center = slide.querySelector('[data-role="c"]');
        const neighbors = slide.querySelectorAll('[data-flow]');
        const token = slide.querySelector('[data-token]');
        const rungs = slide.querySelectorAll('[data-rung]');
        const timeline = stepTimeline((tl) => {
            tl.addLabel('step0');
            tl.to(neighbors, { x: (_index, target) => Number(target.dataset.dx), y: (_index, target) => Number(target.dataset.dy), opacity: 0.35, duration: 0.6, ease: 'power1.inOut' }, 'step0+=0.02');
            tl.to(center, { scale: 1.06, duration: 0.6, ease: 'power1.inOut' }, '<');
            tl.addLabel('step1', '>');
            tl.to(token, { left: '62.5%', duration: 0.55, ease: 'power2.inOut' }, 'step1');
            tl.to(rungs[2], { scale: 1.08, duration: 0.3, ease: 'power2.out' }, '<');
            tl.addLabel('step2', '>');
        });

        return {
            show(step, animate) {
                const values = ['140', '134', '170'];
                center.textContent = values[Math.min(step, 2)];
                timeline.go(Math.min(step, 2), animate);
            },
        };
    },

    regions(slide) {
        const nodes = mountGrid(slide.querySelector('[data-board]'), 4, 4, 42);
        const x0 = 2;
        const y0 = 3;
        const once = settle(seed.initial, 'base');
        const twice = settle(once, 'base');
        const frames = [
            { cells: seed.initial, mode: 'noise', mix: 0 },
            { cells: once, mode: 'mix', mix: 0.72 },
            { cells: twice, mode: 'terrain', mix: 1 },
        ];

        return {
            show(step, animate) {
                const frame = frames[Math.min(step, frames.length - 1)];
                moveNodes(nodes, crop(frame.cells, x0, y0, 4, 4), frame.mode, frame.mix, animate);
            },
        };
    },

    constraints(slide) {
        const patches = [...slide.querySelectorAll('[data-patch]')];
        const grids = patches.map((host) => mountGrid(host, 3, 3, 36));
        const window = [3, 4];
        const results = {
            life: settle(seed.initial, 'life'),
            order: settle(seed.initial, 'order'),
            void: settle(seed.initial, 'void'),
        };
        return {
            show(_step, animate) {
                grids.forEach((nodes, index) => {
                    const name = patches[index].dataset.patch;
                    moveNodes(nodes, crop(seed.initial, window[0], window[1], 3, 3), 'noise', 0, false);
                    moveNodes(nodes, crop(results[name], window[0], window[1], 3, 3), 'mix', 0.85, animate);
                });
            },
        };
    },

    decisions(slide) {
        const beats = slide.querySelectorAll('[data-beat]');
        const line = slide.querySelector('[data-line]');
        gsap.set(beats, { opacity: 0.28 });
        gsap.set(line, { opacity: 0, y: 10 });

        return {
            show(step, animate) {
                const time = duration(0.35, animate);
                beats.forEach((beat, index) => {
                    gsap.to(beat, { opacity: index < step ? 1 : 0.28, duration: time, ease: 'power2.out', overwrite: 'auto' });
                });
                gsap.to(line, {
                    opacity: step >= beats.length ? 1 : 0,
                    y: step >= beats.length ? 0 : 10,
                    duration: duration(0.45, animate),
                    ease: 'power2.out',
                    overwrite: 'auto',
                });
            },
        };
    },

    morph(slide) {
        const nodes = stageBoard(slide.querySelector('[data-board]'), 32);
        const frames = runSequence(seed.initial, A);
        const picks = [0, 2, 4, 5];

        return {
            show(step, animate) {
                const frame = frames[picks[Math.min(step, picks.length - 1)]];
                moveNodes(nodes, listsFrom(frame.cells), frame.mode, frame.mix, animate);
            },
        };
    },

    thresholds(slide) {
        const done = finalA();
        const terrain = classify(done.cells);
        const examples = [];

        for (let y = 0; y < 9 && examples.length < 3; y += 1) {
            for (let x = 0; x < 7; x += 1) {
                const kind = terrain[y * 7 + x];

                if (examples.some((item) => item.kind === kind)) {
                    continue;
                }

                examples.push({ kind, sum: cellSum(done.cells, x, y), x, y });

                if (examples.length === 3) {
                    break;
                }
            }
        }

        examples.sort((a, b) => a.kind - b.kind);
        const pips = [...slide.querySelectorAll('[data-pip]')];
        const labels = ['VOID', 'GROUND', 'ENERGY'];

        return {
            show(step, animate) {
                pips.forEach((pip, index) => {
                    const example = examples[index];
                    const place = step < 1 ? 0 : (example.sum / 1020) * 100;
                    pip.dataset.kind = labels[example.kind].toLowerCase();
                    pip.style.setProperty('--sum', String(example.sum));
                    gsap.to(pip, {
                        left: `${place}%`,
                        duration: duration(0.7, animate),
                        ease: 'power2.inOut',
                        overwrite: 'auto',
                    });
                    setText(pip, 'b', step >= 2 ? `${labels[example.kind]} ${example.sum}` : String(example.sum));
                });
            },
        };
    },

    objective(slide) {
        const nodes = stageBoard(slide.querySelector('[data-board]'), 30);
        const done = finalA();
        const terrain = classify(done.cells);
        const core = pickCore(done.cells, terrain);
        const exit = pickExit(terrain, core);
        const reach = reachable(terrain, core);

        return {
            show(step, animate) {
                moveNodes(nodes, listsFrom(done.cells), 'terrain', 1, animate && step === 0);
                nodes.forEach((node, index) => {
                    const x = index % 7;
                    const y = Math.floor(index / 7);
                    node.classList.toggle('is-eligible', step >= 1 && terrain[index] !== 0);
                    node.classList.toggle('is-core', step >= 2 && core && core[0] === x && core[1] === y);
                    node.classList.toggle('is-reach', step >= 3 && reach.has(`${x},${y}`));
                    node.classList.toggle('is-exit', step >= 4 && exit && exit[0] === x && exit[1] === y);
                });
            },
        };
    },

    walk(slide) {
        const nodes = stageBoard(slide.querySelector('[data-board]'), 32);
        const done = finalA();
        const terrain = classify(done.cells);
        const core = pickCore(done.cells, terrain);
        const exit = pickExit(terrain, core);
        const path = [core, ...route(terrain, core, exit)];
        moveNodes(nodes, listsFrom(done.cells), 'terrain', 1, false);
        let played = false;

        function place(point) {
            nodes.forEach((node, index) => {
                const x = index % 7;
                const y = Math.floor(index / 7);
                node.classList.toggle('is-player', point && point[0] === x && point[1] === y);
                node.classList.toggle('is-core', core && core[0] === x && core[1] === y);
                node.classList.toggle('is-exit', exit && exit[0] === x && exit[1] === y);
                node.classList.toggle('is-energy', terrain[index] === 2);
            });
        }

        return {
            show(_step, animate) {
                if (played) {
                    return;
                }

                played = true;
                place(core);

                if (!animate || reduced()) {
                    place(exit);

                    return;
                }

                path.forEach((point, index) => {
                    gsap.delayedCall(0.12 * index, () => place(point));
                });
            },
        };
    },

    sequenceA(slide) {
        const nodes = stageBoard(slide.querySelector('[data-board]'), 28);
        const frames = runSequence(seed.initial, A);
        const done = frames.at(-1);
        const terrain = classify(done.cells);
        const core = pickCore(done.cells, terrain);
        const exit = pickExit(terrain, core);
        const path = route(terrain, core, exit);
        const tally = counts(terrain);
        let pathTl = null;
        setText(slide, '[data-void]', String(tally.void));
        setText(slide, '[data-ground]', String(tally.ground));
        setText(slide, '[data-energy]', String(tally.energy));
        setText(slide, '[data-core]', `(${core[0]}, ${core[1]})`);
        setText(slide, '[data-exit]', `(${exit[0]}, ${exit[1]})`);

        return {
            show(step, animate) {
                pathTl?.kill();
                const frame = frames[Math.min(step, frames.length - 1)];
                moveNodes(nodes, listsFrom(frame.cells), frame.mode, frame.mix, animate);
                const locked = step >= frames.length - 1;

                const place = (point) => {
                    nodes.forEach((node, index) => {
                        const x = index % 7;
                        const y = Math.floor(index / 7);
                        node.classList.toggle('is-core', locked && core[0] === x && core[1] === y);
                        node.classList.toggle('is-exit', locked && exit[0] === x && exit[1] === y);
                        node.classList.toggle('is-player', !!point && point[0] === x && point[1] === y);
                    });
                };

                if (!locked) {
                    place(null);

                    return;
                }

                if (!animate || reduced()) {
                    place(path.at(-1));

                    return;
                }

                place(null);
                pathTl = gsap.timeline();
                path.forEach((point, index) => {
                    pathTl.call(() => place(point), null, 0.65 + index * 0.08);
                });
            },
        };
    },

    sequenceB(slide) {
        const nodes = stageBoard(slide.querySelector('[data-board]'), 28);
        const frames = runSequence(seed.initial, B);

        return {
            show(step, animate) {
                const frame = frames[Math.min(step, frames.length - 1)];
                moveNodes(nodes, listsFrom(frame.cells), frame.mode, frame.mix, animate);
                const tally = counts(classify(frame.cells));
                setText(slide, '[data-void]', String(tally.void));
            },
        };
    },

    compare(slide) {
        const left = stageBoard(slide.querySelector('[data-side="a"]'), 22);
        const right = stageBoard(slide.querySelector('[data-side="b"]'), 22);
        const a = runSequence(seed.initial, A).at(-1);
        const b = runSequence(seed.initial, B).at(-1);
        const equation = slide.querySelector('[data-equation]');
        gsap.set(equation, { opacity: 0, y: 8 });
        let played = false;

        return {
            show(_step, animate) {
                if (played) {
                    return;
                }

                played = true;
                moveNodes(left, listsFrom(seed.initial), 'noise', 0, false);
                moveNodes(right, listsFrom(seed.initial), 'noise', 0, false);
                gsap.delayedCall(duration(0.25, animate), () => {
                    moveNodes(left, listsFrom(a.cells), 'terrain', 1, animate);
                    moveNodes(right, listsFrom(b.cells), 'terrain', 1, animate);
                });
                gsap.to(equation, {
                    opacity: 1,
                    y: 0,
                    duration: duration(0.45, animate),
                    delay: duration(0.85, animate),
                    ease: 'power2.out',
                });
            },
        };
    },

funnel(slide) {
        const rows = [...slide.querySelectorAll('[data-row]')];

        return {
            show(step, animate) {
                const time = duration(0.45, animate);
                rows.forEach((row, index) => {
                    const hide = index < rows.length - 1 && step > index;
                    gsap.to(row, {
                        opacity: hide ? 0.14 : 1,
                        scale: hide ? 0.97 : 1,
                        duration: time,
                        ease: 'power1.inOut',
                        overwrite: 'auto',
                    });
                });
            },
        };
    },

    close(slide) {
        const lines = slide.querySelectorAll('[data-line]');
        gsap.set(lines, { opacity: 0, y: 14 });
        let played = false;

        return {
            show(_step, animate) {
                if (played) {
                    return;
                }

                played = true;
                gsap.to(lines, {
                    opacity: 1,
                    y: 0,
                    duration: duration(0.45, animate),
                    stagger: reduced() ? 0 : 0.12,
                    ease: 'power2.out',
                });
            },
        };
    },
};
